<?php
/********************************************************/
//        FILE: sanitize.php
// DESCRIPTION: Contains functions that handle
//              the preprocessing of the form
//              submitted data so that information
//              may be safely stored in the database.
//
//     HISTORY:
//              04-19-2006
//                  -File created.
/********************************************************/
/*********************************************************
//make the data safe to be inserted in the database.
//this will handle strings or single level arrays
*********************************************************/
function sanitize($obj) {

    if (is_array($obj)) {
        $keys = array_keys($obj);
        foreach ($keys AS $key) $obj[$key] = sanitizeString($obj[$key]);
    }
    else $obj = sanitizeString($obj);

    return $obj;

}
/*********************************************************
//actually perform the sanitation
*********************************************************/
function sanitizeString($str) {

    return db_escape_string(strip_tags($str));

}
/*********************************************************
//cleans sanitize string for display
*********************************************************/
function stripsan($str) {

    return stripslashes(db_unescape_string($str));

}
/*********************************************************
//sanitizes all get,post,request, and cookie variables
*********************************************************/
function sanitizeRequest($es = null) {

    if (!$es) $es = array();

    //the request sg
    $keys = array_keys($_REQUEST);
    foreach ($keys AS $key) {
        //skip if the variable is marked for exemption
        if (in_array($key,$es)) continue;
        $_REQUEST[$key] = sanitize($_REQUEST[$key]);
    }

    //the post sg
    $keys = array_keys($_POST);
    foreach ($keys AS $key) {
        //skip if the variable is marked for exemption
        if (in_array($key,$es)) continue;
        $_POST[$key] = sanitize($_POST[$key]);
    }

    //the get sg
    $keys = array_keys($_GET);
    foreach ($keys AS $key) {
        //skip if the variable is marked for exemption
        if (in_array($key,$es)) continue;
        $_GET[$key] = sanitize($_GET[$key]);

    }

    //the cookie sg
    $keys = array_keys($_COOKIE);
    foreach ($keys AS $key) {

        //skip if the variable is marked for exemption
        if (in_array($key,$es)) continue;
        $_COOKIE[$key] = sanitize($_COOKIE[$key]);

    }

}

