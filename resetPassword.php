<?php
//
// File: resetPassword.php
// Author: Charles Parry
// Date: 6/16/2022
//

// Turn on error reporting
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
//error_reporting(E_ALL);

//echo password_hash("123", PASSWORD_DEFAULT);
//die();

// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


// Connect to DB
require_once "includes/dbConnect.php";
require_once "lib/dblib.php";

// Variables.
$password = "";
$password_confirm = "";
$username_err = "";
$password_err = "";
$login_err    = "";
$crumb = "";

// See if we have the 'crumb' attribute in the URL
if ((isset($_GET["crumb"])) ) {
	$crumb = $_GET["crumb"];
}


// Make sure we have the right info for a user who needs to update their password.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true){
	if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] != true){
		// Redirect user to login again
		header("location: login.php");
		die();
	}
}

if(!isset($_SESSION["id"])){
	if(!isset($_SESSION["admin_id"])){
		// Redirect user to login again
		header("location: login.php");
		die();
	}
}

if(!isset($_SESSION["username"])){
	if(!isset($_SESSION["admin_username"])){
		// Redirect user to login again
		header("location: login.php");
		die();
	}
}

if(!isset($_SESSION["role_ID"])){
	// Redirect user to login again
	header("location: login.php");
	die();
}


if(isset($_SESSION["errorMessage"]) && $_SESSION["errorMessage"]!=""){
	$login_err = $_SESSION["errorMessage"];
	$_SESSION["errorMessage"] = "";
}


// Process form data when form is submitted.
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter password.";
		$_SESSION["errorMessage"] = "Please enter password.";

		// Redirect user back to reset page
		header("location: resetPassword.php");
		die();

    } else{
        $password = trim($_POST["password"]);
    }

    // Check if password_confirm is empty
    if(empty(trim($_POST["password_confirm"]))){
        $password_confirm_err = "Please enter your confirmation password.";
		$_SESSION["errorMessage"] = "Please enter confirmation password.";

		// Redirect user back to reset page
		header("location: resetPassword.php");
		die();

    } else{
        $password_confirm = trim($_POST["password_confirm"]);
    }


    // Check if passwords do not match
    if($password_confirm != $password){
        $password_confirm_err = "Password do not match - please try again.";
		$_SESSION["errorMessage"] = "Password do not match - please try again.";

		// Redirect user back to reset page
		header("location: resetPassword.php");
		die();

    }


	// If we cleared those checks, update user password with this hashed value.
	$hashed_password = password_hash($password, PASSWORD_DEFAULT);

	if(($_SESSION["role_ID"]==1) || ($_SESSION["role_ID"]==3) || ($_SESSION["role_ID"]==4)){
		$stmt = mysqli_prepare($link, "UPDATE Users SET password=?, mustResetPassword='N' WHERE userID=? AND username=?");
	}else{
		$stmt = mysqli_prepare($link, "UPDATE Users SET password=?, mustResetPassword='N' WHERE Acct_ID=? AND username=?");
	}


	/* Bind variables to parameters */
	$val1 = $hashed_password;
	if(($_SESSION["role_ID"]==1) || ($_SESSION["role_ID"]==3) || ($_SESSION["role_ID"]==4)){
		$val2 = $_SESSION["admin_id"];
		$val3 = $_SESSION["admin_username"];
	}else{
		$val2 = $_SESSION["id"];
		$val3 = $_SESSION["username"];
	}

	mysqli_stmt_bind_param($stmt, "sis", $val1,$val2,$val3);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);


	if(isset($_SESSION["agentID"]) && $_SESSION["agentID"] > 0)
	{
		$sql = "SELECT w9_signature FROM Pers WHERE Pers_ID=".$_SESSION["agentID"];
		$result = mysqli_query($link, $sql);
		$row = mysqli_fetch_assoc($result);
		if($row["w9_signature"])
		{  header("location: index.php");
			die();
		}else{
			header("location: stand_alone_signature.php?agentID=".base64_encode($_SESSION["agentID"]));
			die();
		}
	}


	if(isset($_GET["FromEmail"]))
	{
		header("location: dealerAgent.php");
		die();
	}
	else
	{
		if($crumb!=""){
			// Redirect user to different start page
			if($crumb=="dealer_setup"){
				header("location: dealer_setup.php");
				die();
			}
		}else{
			// Continue on to main page
			header("location: index.php");
			die();
		}

	}

}



?>
<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Vital Trends Portal - Password Reset</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="./images/favicon.png">
    <link href="./css/style.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
</head>

<body class="h-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
                    <div class="authincation-content">
                        <div class="row no-gutters">
                            <div class="col-xl-12">
                                <div class="auth-form">
									<div class="text-center mb-3">
										<a href="index.html"><img src="images/" alt=""></a>
									</div>
                                    <h4 class="text-center mb-4 text-white">Reset your Password for Vital Trends Portal</h4>
	<?php
	if(!empty($login_err)){
		echo '<div class="alert alert-danger">' . $login_err . '</div>';
	}
	?>
								    <form action="" method="POST">
                                        <div class="form-group">
                                            <label class="mb-1 text-white"><strong>Password</strong></label>
                                            <input type="password" name="password" class="form-control" value="">
                                        </div>
                                        <div class="form-group">
                                            <label class="mb-1 text-white"><strong>Confirm Password</strong></label>
                                            <input type="password" name="password_confirm" class="form-control" value="">
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn bg-white text-primary btn-block">Reset Password and Sign In</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="./vendor/global/global.min.js"></script>
	<script src="./vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="./js/custom.min.js"></script>
    <script src="./js/deznav-init.js"></script>

</body>

</html>