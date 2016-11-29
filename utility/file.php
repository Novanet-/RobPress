<?php

class File
{

    public static function Upload($array, $local = false)
    {
        $f3 = Base::instance();
        extract($array);

        //Only allow this set of file extensions to be uplaoded
        $validExtensions = array(
            'png',
            'jpg',
            'gif',
            'jpeg',
            'bmg'
        );

        //Only allow these content-types to be uploaded
        $validTypes = array(
            'image/png',
            'image/jpg',
            'image/gif',
            'image/jpeg',
            'image/bmg'
        );

        $errors = array('extension' => False, 'type' => False, 'size' => False);

        $ext = end((explode(".", $name)));

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $tmp_name); //Get content-type

        if (!in_array($ext, $validExtensions)) { //check file extension against valid set
            foreach ($errors as $key => $val) {
                $errors['extension'] = true;
            }
        }

        if (!in_array($type, $validTypes)) { //check file content-type against valid set
            foreach ($errors as $key => $val) {
                $errors['type'] = true;
            }
        }

        $size = filesize($tmp_name); //CGet filesize

        if ($size > 1048576) {    //File must be less than 1MB
            foreach ($errors as $key => $val) {
                $errors['size'] = true;
            }
        }

        $valid = true;

        foreach ($errors as $key => $val) {
            if ($val) {
                \StatusMessage::add('Invalid file ' . $key, 'danger');
                $valid = false;
            }
        }

        if (!$valid) {
            return $f3->reroute($_SERVER['REQUEST_URI']);
        }


        $name = uniqid() . '.' . $ext;
        $directory = getcwd() . '/uploads';
        $destination = $directory . '/' . $name;
        $webdest = '/uploads/' . $name;

        //Local files get moved
        if ($local) {
            if (copy($tmp_name, $destination)) {
                chmod($destination, 0666);
                return $webdest;
            } else {
                return false;
            }
            //POSTed files are done with move_uploaded_file
        } else {
            if (move_uploaded_file($tmp_name, $destination)) {
                chmod($destination, 0666);
                return $webdest;
            } else {
                return false;
            }
        }
    }

}

?>
