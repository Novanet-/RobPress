<?php

/** Prepare timestamp for MySQL insertion */
function mydate($timestamp = 0)
{
    if (empty($timestamp)) {
        $timestamp = time();
    }
    if (!is_numeric($timestamp)) {
        $timestamp = strtotime($timestamp);
    }
    return date("Y-m-d H:i:s", $timestamp);
}

/** Prepare timestamp for nice display */
function nicedate($timestamp = 0)
{
    if (empty($timestamp)) {
        $timestamp = time();
    }
    if (!is_numeric($timestamp)) {
        $timestamp = strtotime($timestamp);
    }
    return date("l jS \of F Y H:i:s", $timestamp);
}

/** HTML escape content */
function h($text)
{
    return htmlspecialchars($text);
}

/**Like "h" but only removes script tags, for output where rich formatting is still required, such as the body of a blog post
 * @param $text
 * @return string
 */
function s($text)
{
    $dom = new DOMDocument();
    $dom->loadHTML($text);

// Find all the <img> tags
    $scripts = $dom->getElementsByTagName("script");

// And remove them
    $scripts_remove = array();
    foreach ($scripts as $img) {
        $scripts_remove[] = $img;
    }

    foreach ($scripts_remove as $i) {
        $i->parentNode->removeChild($i);
    }
    $output = $dom->saveHTML();
    return $output;
}

/**Predicate to check if given string contains only alphanumeric characters
 * @param $string
 * @return bool
 */
function isAlphanumericOnly($string)
{
    return (preg_match('/[^a-zA-Z0-9_]/', $string) == 0);
}

/** Declare constants */
if (isset($_SERVER['BASE'])) {
    define('BASE', $_SERVER['BASE']);
} else {
    define('BASE', '/');
}

?>
