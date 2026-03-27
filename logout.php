<?php
//
// File: logout.php
// Author: Charles Parry
// Date: 6/17/2021
//


// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
header("location: login.php");
exit;

?>