<?php

class PagesModel
{

    /** Create a new page */
    public function create($pagename)
    {
        $pagedir = getcwd() . "/pages/";
        touch($pagedir . $pagename . ".html");
    }

    /** Load the contents of a page */
    public function delete($pagename)
    {
        $pagedir = getcwd() . "/pages/";
        $file = $pagedir . $pagename;
        if (!file_exists($file)) {
            $file .= ".html";
        }
        if (!file_exists($file)) {
            return false;
        }
        unlink($file);
    }

    /** Get all available pages */
    public function fetchAll()
    {
        $pagedir = getcwd() . "/pages/";
        $pages = array();
        if ($handle = opendir($pagedir)) {
            while (false !== ($file = readdir($handle))) {
                if (!preg_match('![.]html!sim', $file)) continue;
                $title = ucfirst(str_ireplace("_", " ", str_ireplace(".html", "", $file)));
                $pages[$title] = $file;
            }
            closedir($handle);
        }
        return $pages;
    }

    /** Load the contents of a page */
    public function fetch($pagename)
    {
        $f3 = Base::instance();

        //Gets all the pages stored on the server
        $allPages = array_values($this->fetchAll());

        //Filters out the file extensions on the pahes
        foreach ($allPages as $key => $page) {
            $allPages[$key] = preg_replace('/\\.[^.\\s]{3,4}$/', '', $page);
        }

        //Only return a page that's in the set of pages, otherwise 404 it
        if (in_array($pagename, $allPages)) {
            $pagedirectory = getcwd() . "/pages/";
            $file = $pagedirectory . $pagename;
            if (!file_exists($file)) {
                $file .= ".html";
            }
            if (!file_exists($file)) {
                return false;
            }
            return file_get_contents($file);
        } else return $f3->error(404);

    }

    /** Save contents of the page based on title and content field to file */
    public function save()
    {
        $pagedir = getcwd() . "/pages/";
        $file = $pagedir . $this->title;
        if (!file_exists($file)) {
            $file .= ".html";
        }
        if (!file_exists($file)) {
            return false;
        }
        if (!isset($this->content)) {
            return false;
        }
        return file_put_contents($file, $this->content);
    }

}

?>
