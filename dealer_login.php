<?php
//
// File: dealer_login.php
// Author: Charles Parry
// Date: 5/16/2022
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);

//echo password_hash("123", PASSWORD_DEFAULT);
//die();

// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

// Is user already logged in?
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
	if(isset($_SESSION["userType"]) && $_SESSION["userType"] == "dealer"){

		//header("location: index.php");
		//exit;
	}
}


// Connect to DB
require_once "includes/dbConnect.php";



// Variables.
$username = "";
$password = "";
$hashed_password = "";
$dealerID = "";
$username_err = "";
$password_err = "";
$login_err    = "";



// Process form data when form is submitted.
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if username is empty
    if(empty(trim($_POST["dealer_username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["dealer_username"]);
    }

    // Check if password is empty
    if(empty(trim($_POST["dealer_password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["dealer_password"]);
    }

	try{
		// Look up hashed password for this username
		$query = "SELECT * FROM Pers WHERE Pers_Username='".$username."';";

		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$hashed_password = $row["Pswd_Hash_Cd"];
		$dealerID = $row["Acct_ID"];

		/*
		echo "query=".$query;
		echo "<br />";
		echo "hashed_password=".$hashed_password;
		echo "<br />";
		echo "form password=".$password;
		die();
		*/

	}catch(Exception $e){
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}


	// Verify password
	if(password_verify($password, $hashed_password)) {

		// Store data in session variables
		$_SESSION["loggedin"] = true;
		$_SESSION["id"] = $dealerID;
		$_SESSION["username"] = $username;
		$_SESSION["userType"] = "dealer";

		// Redirect user to main page
		header("location: index.php");
	}

	// Redirect user to main page, either if there was an error, or if they logged in.
	header("location: index.php");

}

?>
