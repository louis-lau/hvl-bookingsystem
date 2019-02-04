<?php

/*
* This is the driver you have selected to use
*/
$driver = 'mysqli';

$db = newAdoConnection($driver);

// Store all the secret stuff in secrets.php and add that file to gitignore
require_once __DIR__ . "/secrets.php";
$db->connect("$host", "$user", "$password", "$database_name");

$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
//$db->debug = true;

function sanitize_recursive($s) {
    if (is_array($s)) {
        return(array_map('sanitize_recursive', $s));
    } else {
        return htmlspecialchars($s);
    }
}