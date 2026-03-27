<?php
//
// File: dealer_logout.php
// Author: Charles Parry
// Date: 5/24/2022
//


// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

// Clear the dealer session values
//$_SESSION["loggedin"] = false;
$_SESSION["id"] = "";
$_SESSION["username"] = "";

if($_SESSION["role_ID"] == 4 || $_SESSION["role_ID"]==5){
	$_SESSION["userType"] = "Agent";
}else{
	$_SESSION["userType"] = "";
}

// Redirect to portal index page
header("location: index.php");
exit;

?>