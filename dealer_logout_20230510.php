<?php
//
// File: dealer_logout.php
// Author: Charles Parry
// Date: 5/24/2022
//


// Initialize the session
session_start();

// Clear the dealer session values
$_SESSION["loggedin"] = false;
$_SESSION["id"] = "";
$_SESSION["username"] = "";
$_SESSION["userType"] = "";


// Redirect to portal index page
header("location: index.php");
exit;

?>