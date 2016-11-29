<?php

class User extends Controller
{

    public function view($f3)
    {
        $userid = $f3->get('PARAMS.3');
        if (empty($userid)) { //Parameter protection
            return $f3->reroute('/');
        }
        $u = $this->Model->Users->fetch($userid);
        if (empty($u['username'])) { //Parameter protection
            return $f3->reroute('/');
        }

        $articles = $this->Model->Posts->fetchAll(array('user_id' => $userid));
        $comments = $this->Model->Comments->fetchAll(array('user_id' => $userid));

        $f3->set('u', $u);
        $f3->set('articles', $articles);
        $f3->set('comments', $comments);
    }

    public function add($f3)
    {
        if ($this->request->is('post')) {
            extract($this->request->data);

            $check = $this->Model->Users->fetch(array('username' => h($username))); //XSS protection
            $email = filter_var($email, FILTER_SANITIZE_EMAIL); //Remove disallowed characters from email address
            $pwvalid = $this->checkPasswordValid($password, $pwerrorlist); //Checks that the apssword si logner than 8 characters, and msut contain both letters and numbers
            if (!empty($check)) {
                StatusMessage::add('User already exists', 'danger');
            } else if ($password != $password2) {
                StatusMessage::add('Passwords must match', 'danger');
            } else if (!(isAlphanumericOnly($username))) { //Enforces alphanumeric only usernames
                StatusMessage::add('Username can only contain alphanumeric characters', 'danger');
            } else if (!(isAlphanumericOnly($displayname))) { //Enforces alphanumeric only display names
                StatusMessage::add('Displayname can only contain alphanumeric characters', 'danger');
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { //Checks that is a valid email address format
                StatusMessage::add('Email invalid', 'danger');
            } else if (!$pwvalid) {
                foreach ($pwerrorlist as $errorstring) {
                    StatusMessage::add($errorstring, 'danger');
                }
            } else {
                $user = $this->Model->Users;
                $user->copyfrom('POST', function ($arr) {    //Parameter protection, match be in this set
                    return array_intersect_key($arr, array_flip(array('username', 'displayname', 'email', 'password', 'password2')));
                });
                $user->created = mydate();
                $user->bio = '';
                $user->level = 1;
                $user->setPassword($password);
                if (empty($displayname)) {
                    $user->displayname = $user->username;
                }

                $user->username = h($username); //XSS protection
                $user->displayname = h($user->displayname); ////XSS protection

                //Set the users password
                $user->setPassword($user->password);

                $user->save();
                StatusMessage::add('Registration complete', 'success');
                return $f3->reroute('/user/login');
            }
        }
    }

    public function login($f3)
    {
        /** YOU MAY NOT CHANGE THIS FUNCTION - Make any changes in Auth->checkLogin, Auth->login and afterLogin() */
        if ($this->request->is('post')) {

            //Check for debug mode
            $settings = $this->Model->Settings;
            $debug = $settings->getSetting('debug');

            //Either allow log in with checked and approved login, or debug mode login
            list($username, $password) = array($this->request->data['username'], $this->request->data['password']);
            if (
                ($this->Auth->checkLogin($username, $password, $this->request, $debug) && ($this->Auth->login($username, $password))) ||
                ($debug && $this->Auth->debugLogin($username))
            ) {

                $this->afterLogin($f3);

            } else {
                StatusMessage::add('Invalid username or password', 'danger');
            }
        }
    }

    /* Handle after logging in */
    private function afterLogin($f3)
    {
        StatusMessage::add('Logged in succesfully', 'success');

        //Redirect to where they came from
        if (isset($_GET['from']) && substr($_GET['from'], 0, 1) == '/') {    //Block rerouting to outside of the domain
            $f3->reroute($_GET['from']);
        } else {
            $f3->reroute('/');
        }
    }

    public function logout($f3)
    {
        $this->Auth->logout();
        StatusMessage::add('Logged out succesfully', 'success');
        $f3->reroute('/');
    }


    public function profile($f3)
    {
        $id = $this->Auth->user('id');
        extract($this->request->data);
        $u = $this->Model->Users->fetch($id);
        $oldpass = $u->password;
        if ($this->request->is('post')) {
            $u->copyfrom('POST');
            if (empty($u->password)) {
                $u->password = $oldpass;
            }

            //Handle avatar upload
            if (isset($_FILES['avatar']) && isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name'])) {
                $url = File::Upload($_FILES['avatar']);
                $u->avatar = $url;
            } else if (isset($reset)) {
                $u->avatar = '';
            }

            $u->save();
            \StatusMessage::add('Profile updated succesfully', 'success');
            return $f3->reroute('/user/profile');
        }
        $_POST = $u->cast();
        $f3->set('u', $u);
    }


    //Only admins would need to do this, blocked authorisation bypass
    /*    public function promote($f3)
        {
            $id = $this->Auth->user('id');
            $u = $this->Model->Users->fetch($id);
            $u->level = 2;
            $u->save();
            return $f3->reroute('/');
        }*/

    public function checkPasswordValid($pwd, &$errors)
    {
        $errors_init = $errors;

        if (strlen($pwd) < 8) {
            $errors[] = "Password too short!";
        }

        if (!preg_match("#[0-9]+#", $pwd)) {
            $errors[] = "Password must include at least one number!";
        }

        if (!preg_match("#[a-zA-Z]+#", $pwd)) {
            $errors[] = "Password must include at least one letter!";
        }

        return ($errors == $errors_init);
    }

}

?>
