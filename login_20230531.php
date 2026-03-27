<?php
//
// File: login.php
// Author: Charles Parry
// Date: 5/07/2022
//

// Turn on error reporting
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
//error_reporting(E_ALL);

//echo password_hash("123", PASSWORD_DEFAULT);
//die();

// Clear our session
session_destroy();

// Initialize the session
session_start();

// Is user already logged in?
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
	header("location: index.php");
	exit;
}


// Connect to DB
require_once "includes/dbConnect.php";

require_once "lib/dblib.php";

// Variables.
$username = "";
$password = "";
$Acct_ID = ""; // If the user is associated with more than 1 dealer acct, we will have to determine which
$username_err = "";
$password_err = "";
$login_err = "";
$Sls_Agnt_ID = "";


// Process form data when form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {


    // Check if username is empty
	if (empty(trim($_POST["username"]))) {
		$username_err = "Please enter username.";
	} else {
		$username = trim($_POST["username"]);
	}

    // Check if password is empty
	if (empty(trim($_POST["password"]))) {
		$password_err = "Please enter your password.";
	} else {
		$password = trim($_POST["password"]);
	}

    // Check if we got a dealerID
	if (isset($_POST["dealerID"])) {
		$Acct_ID = $_POST["dealerID"];
		$username_err = "";
		$password_err = "";
		$username = $_SESSION["login_username"];
		$password = $_SESSION["login_password"];
//		$_SESSION["login_username"] = "";
//		$_SESSION["login_password"] = "";
	/*
	echo "password=".$password;
	echo "<br />username=".$username;
	echo "<br />Acct_ID=".$Acct_ID;
	echo "<br />password_err=".$password_err;
	die();
		*/
	}



	if (($password_err != "") || ($username_err != "")) {
		// Redirect user to main page
		header("location: login.php");
	}

 /*
	// Check password against a hard coded value for now, since this
	//  is intended just to be a stop-spam login not DB driven in particular.
	if($password == "test123"){
		// Store data in session variables
		$_SESSION["loggedin"] = true;
		$_SESSION["id"] = "1";
		$_SESSION["username"] = "admin";
		$_SESSION["userType"] = "admin";

		// Redirect user to main page
		header("location: index.php");
	}else{
		// Redirect user to main page
		header("location: login.php");
	}
	die();
	 */

    // Validate credentials
	if (empty($username_err) && empty($password_err)) {

		// If we do not have a Acct_ID defined, check if this user has multiple dealer associations
		if ($Acct_ID == "") {
			$stmt = mysqli_prepare($link, "SELECT u.userID, u.username, u.password, u.mustResetPassword, u.Role_ID,
			                               a.Acct_Nm, a.Acct_ID FROM Users u, Acct a, Usr_Loc ul WHERE u.username = ? AND
										   u.userID = ul.Usr_ID AND ul.Dlr_Acct_ID = a.Acct_ID ORDER BY a.Acct_Nm");
			/* Bind variables to parameters */
			$val1 = $username;
			mysqli_stmt_bind_param($stmt, "s", $val1);
			if (mysqli_stmt_execute($stmt)) {
				mysqli_stmt_store_result($stmt);
				$numRows = mysqli_stmt_num_rows($stmt);
	//echo "numRows here=".$numRows;
	//$numRows = 2;
	//die();
				// If numRows > 1, then we have to go to a multi-plex page and determine which dealer they want to use.
				if ($numRows > 1) {
					// Save the username and password that they entered, so we can look up the multi-dealer-selection.
					$_SESSION['login_username'] = $username;
					$_SESSION['login_password'] = $password;

					// Forward to the select dealer page.
					header("location: selectDealer.php");
					die();
				}
			}
		} // if($dealerID=="") //


		if ($Acct_ID != "") {
			$stmt = mysqli_prepare($link, "SELECT u.userID, u.username, u.password, u.mustResetPassword, u.Role_ID, u.Agent_ID, a.Acct_Nm,
			                               a.Acct_ID, a.Sls_Agnt_ID FROM Users u, Acct a, Usr_Loc ul WHERE u.username = ? AND
										   ul.Dlr_Acct_ID=? AND u.userID = ul.Usr_ID AND ul.Dlr_Acct_ID = a.Acct_ID ORDER BY a.Acct_Nm");

			/* Bind variables to parameters */
			$val1 = $username;
			$val2 = $Acct_ID;

			mysqli_stmt_bind_param($stmt, "si", $val1, $val2);
		} else {
			/* Prepare a select statement to check credentials for this user. */
			//$stmt = mysqli_prepare($link, "SELECT userID, username, password, mustResetPassword FROM Users WHERE Role_ID=1 AND username = ?");
			//$stmt = mysqli_prepare($link, "SELECT u.userID, u.username, u.password, u.mustResetPassword, u.Role_ID, a.Acct_Nm, a.Acct_ID FROM Users u LEFT JOIN Acct a ON u.Acct_ID = a.Acct_ID WHERE u.username = ? ORDER BY a.Acct_Nm");
			$stmt = mysqli_prepare($link, "SELECT u.userID, u.username, u.password, u.mustResetPassword, u.Role_ID, u.Agent_ID, a.Acct_Nm,
			                               a.Acct_ID, a.Sls_Agnt_ID FROM Users u, Acct a, Usr_Loc ul WHERE u.username = ? AND
										   u.userID = ul.Usr_ID AND ul.Dlr_Acct_ID = a.Acct_ID ORDER BY a.Acct_Nm");

	// SELECT * FROM Usr_Loc ul, `Users` u, Acct a WHERE u.`username` = 'josh.s.kantor@gmail.com' AND ul.Usr_ID = u.UserID AND ul.Dlr_Acct_ID = a.Acct_ID;

			/* Bind variables to parameters */
			$val1 = $username;

			mysqli_stmt_bind_param($stmt, "s", $val1);
		}


		/* Execute the statement */
		//$result = mysqli_stmt_execute($stmt);


		// Attempt to execute the prepared statement
		if (mysqli_stmt_execute($stmt)) {

	//			mysqli_stmt_store_result($stmt);
	//			$numRows = mysqli_stmt_num_rows($stmt);

			$result = mysqli_stmt_get_result($stmt);
			$row = mysqli_fetch_assoc($result);

	//print_r($row);
	//die();

			// WORKS
			/*
			$numRows = 0;
			while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
				foreach ($row as $r) {
					print "$r ";
				}
				print "\n";
				$numRows++;
			}

echo "username=".$username;
echo "<br />";
echo "Acct_ID=".$Acct_ID;
die();
			 */

			mysqli_stmt_bind_result($stmt, $userID , $username, $storedPassword, $mustResetPassword, $roleID,  $Agent_ID,  $Acct_Nm, $Acct_ID, $Sls_Agnt_ID);

			$rowCount = 0;
			/*
			while (mysqli_stmt_fetch($stmt))
			{
				printf("%d, %s, %s, %s, %s, %d \n", $userID, $username, $storedPassword, $mustResetPassword, $Acct_Nm, $Acct_ID);
				echo "<br />";
				$rowCount++;
			}
			 */
	//			echo "<br /><br/>row count=".$rowCount;
	//			die();
			$userID = $row["userID"];
			$roleID = $row["Role_ID"];
			$Acct_ID = $row["Acct_ID"];
			$Agent_ID = $row["Agent_ID"];
			$storedPassword = $row["password"];
			$mustResetPassword = $row["mustResetPassword"];
			$Sls_Agnt_ID = $row["Sls_Agnt_ID"];


	/*
echo "here4";
echo "<br />";
echo "Sls_Agnt_ID=".$Sls_Agnt_ID;
die();


	echo "<br />username= ".$username;
	echo "<br />num rows = ".$numRows;
	echo "<br /><br />userID= ".$userID;
	echo "<br /><br />storedPassword = ".$storedPassword;
	echo "<br /><br />mustResetPassword = ".$mustResetPassword;
	die();
				*/
	/*



   die();


    echo "password= ".$password;
	echo "<br />storedPassword= ".$storedPassword;
	die();

				*/


	//			if (true) {

			if (password_verify($password, $storedPassword)) {
				// echo "password checks out";
				// echo "<br />roleID=".$roleID;
				// die();
	/*
	echo "<br />id=".$Acct_ID;
	echo "<br />userID=".$userID;
	echo "<br />username=".$username;
	echo "<br />type="."dealer";
	echo "<br />roleID=".$roleID;
	die();
				 */
				// If Role_ID == 1, this is an admin.
				//  If Role_ID == 2, this is a dealer account.
				//  If Role_ID == 3, this is a Vital Trends Sales Agent Account.
				//  If Role_ID == 4, this is a Agency Sales Agent Account.
				//  If Role_ID == 5, this is a Agency Account.
				//  If Role_ID == 6, this is a Dealer Agent Account.
				if (($roleID == 1)) {
					// Store data in session variables
					$_SESSION["admin_loggedin"] = true;
					$_SESSION["admin_id"] = $userID;
					$_SESSION["admin_username"] = $username;
					$_SESSION["role_ID"] = $roleID;

					// Clear out any old dealer related session vars when admin re-logs in
					$_SESSION["loggedin"] = "";
					$_SESSION["id"] = "";
					$_SESSION["userID"] = "";
					$_SESSION["username"] = "";
					$_SESSION["userType"] = "";
					unset($_SESSION["agentID"]);
				} else if($roleID == 2){
					$_SESSION["loggedin"] = true;
					$_SESSION["id"] = $Acct_ID;
					$_SESSION["agentID"] = $Agent_ID;
					$_SESSION["userID"] = $userID;
					$_SESSION["username"] = $username;
					$_SESSION["userType"] = "dealer";
					$_SESSION["role_ID"] = $roleID;
					$_SESSION["admin_id"] = $Sls_Agnt_ID; // This allows us to display the sales agent who created this dealer.
				} else if(($roleID == 3) || ($roleID == 4) || ($roleID == 5) || ($roleID == 6)){

					// Do not start the session with a dealer selected, for Agencies or Agency Agents
					if($roleID==4 || $roleID==5){
						$_SESSION["id"] = "";
						$_SESSION["admin_id"] = $userID; // This allows us to display the sales agent who created this dealer.
						$_SESSION["username"] = "";
						$_SESSION["userType"] = "Agent";
					}else if($roleID==6){
						// For Dealer Agents
						$_SESSION["id"] = $Acct_ID;
						$_SESSION["admin_id"] = $Sls_Agnt_ID; // This allows us to display the sales agent who created this dealer.
						$_SESSION["userType"] = "dealer";
						$_SESSION["username"] = $username;
					}else{
						$_SESSION["id"] = $Acct_ID;
						$_SESSION["admin_id"] = $Sls_Agnt_ID; // This allows us to display the sales agent who created this dealer.
						$_SESSION["username"] = $username;
						$_SESSION["userType"] = "Agent";
					}

					$_SESSION["loggedin"] = true;
					$_SESSION["agentID"] = $Agent_ID;
					$_SESSION["userID"] = $userID;
					$_SESSION["role_ID"] = $roleID;


					// Clear out any old dealer related session vars when admin re-logs in
					$_SESSION["admin_loggedin"] = false;
					$_SESSION["admin_username"] = "";
				}


				///////////////////////////////////////////////////////////////////////////////////////////////
				// SECURITY!
				// cparry@gmail.com
				// 3/8/2023
				// Base this off of our current security matrix
				//  Define a flag for each security parameter, then use those flags
				//  throughout the application to customize behavior based on settings saved here to session.

				$_SESSION["security_01_all_dealers"] = "N";
				$_SESSION["security_02_dealership_customers"] = "N";
				$_SESSION["security_03_dealership_agreement_info"] = "N";
				$_SESSION["security_04_dealership_agents_customers"] = "N";
				$_SESSION["security_05_all_quotes"] = "N";
				$_SESSION["security_06_dealers_quotes"] = "N";
				$_SESSION["security_07_customers_quotes"] = "N";
				$_SESSION["security_08_all_warranties"] = "N";
				$_SESSION["security_09_dealers_warranties"] = "N";
				$_SESSION["security_10_customers_warranties"] = "N";

				if($roleID==1){
					$_SESSION["security_01_all_dealers"] = "Y";
					$_SESSION["security_02_dealership_customers"] = "Y";
					$_SESSION["security_03_dealership_agreement_info"] = "Y";
					$_SESSION["security_04_dealership_agents_customers"] = "Y";
					$_SESSION["security_05_all_quotes"] = "Y";
					$_SESSION["security_06_dealers_quotes"] = "Y";
					$_SESSION["security_07_customers_quotes"] = "Y";
					$_SESSION["security_08_all_warranties"] = "Y";
					$_SESSION["security_09_dealers_warranties"] = "Y";
					$_SESSION["security_10_customers_warranties"] = "Y";
				}else if($roleID==2){
					$_SESSION["security_01_all_dealers"] = "N";
					$_SESSION["security_02_dealership_customers"] = "Y";
					$_SESSION["security_03_dealership_agreement_info"] = "Y";
					$_SESSION["security_04_dealership_agents_customers"] = "Y";
					$_SESSION["security_05_all_quotes"] = "N";
					$_SESSION["security_06_dealers_quotes"] = "Y";
					$_SESSION["security_07_customers_quotes"] = "Y";
					$_SESSION["security_08_all_warranties"] = "N";
					$_SESSION["security_09_dealers_warranties"] = "Y";
					$_SESSION["security_10_customers_warranties"] = "Y";
				}else if($roleID==3){
					$_SESSION["security_01_all_dealers"] = "N";
					$_SESSION["security_02_dealership_customers"] = "Y";
					$_SESSION["security_03_dealership_agreement_info"] = "Y";
					$_SESSION["security_04_dealership_agents_customers"] = "Y";
					$_SESSION["security_05_all_quotes"] = "Y";
					$_SESSION["security_06_dealers_quotes"] = "Y";
					$_SESSION["security_07_customers_quotes"] = "Y";
					$_SESSION["security_08_all_warranties"] = "Y";
					$_SESSION["security_09_dealers_warranties"] = "Y";
					$_SESSION["security_10_customers_warranties"] = "Y";
				}else if($roleID==4){
					$_SESSION["security_01_all_dealers"] = "N";
					$_SESSION["security_02_dealership_customers"] = "Y";
					$_SESSION["security_03_dealership_agreement_info"] = "Y";
					$_SESSION["security_04_dealership_agents_customers"] = "Y";
					$_SESSION["security_05_all_quotes"] = "Y";
					$_SESSION["security_06_dealers_quotes"] = "Y";
					$_SESSION["security_07_customers_quotes"] = "Y";
					$_SESSION["security_08_all_warranties"] = "Y";
					$_SESSION["security_09_dealers_warranties"] = "Y";
					$_SESSION["security_10_customers_warranties"] = "Y";
				}else if($roleID==5){
					$_SESSION["security_01_all_dealers"] = "N";
					$_SESSION["security_02_dealership_customers"] = "Y";
					$_SESSION["security_03_dealership_agreement_info"] = "Y";
					$_SESSION["security_04_dealership_agents_customers"] = "Y";
					$_SESSION["security_05_all_quotes"] = "Y";
					$_SESSION["security_06_dealers_quotes"] = "Y";
					$_SESSION["security_07_customers_quotes"] = "Y";
					$_SESSION["security_08_all_warranties"] = "Y";
					$_SESSION["security_09_dealers_warranties"] = "Y";
					$_SESSION["security_10_customers_warranties"] = "Y";
				}else if($roleID==6){
					$_SESSION["security_01_all_dealers"] = "N";
					$_SESSION["security_02_dealership_customers"] = "Y";
					$_SESSION["security_03_dealership_agreement_info"] = "N";
					$_SESSION["security_04_dealership_agents_customers"] = "N";
					$_SESSION["security_05_all_quotes"] = "N";
					$_SESSION["security_06_dealers_quotes"] = "N";
					$_SESSION["security_07_customers_quotes"] = "Y";
					$_SESSION["security_08_all_warranties"] = "N";
					$_SESSION["security_09_dealers_warranties"] = "N";
					$_SESSION["security_10_customers_warranties"] = "N";
				}else{
					// If we ended up here, go back to login.
					header("location: login.php");
					die();
				}


	/*
	echo "username=".$username;
	echo "<Br />roleID=".$roleID;
	echo "<Br />session[admin]=".$_SESSION["admin_id"];
	echo "<Br />mustResetPassword=".$mustResetPassword;
	die();
				 */
				// If this is a new user login, then they must change their password
				if ($mustResetPassword == "Y") {
					if(isset($_GET["FromEmail"]))
					{
						// Redirect user to password reset page
						header("location: resetPassword.php?FromEmail=true");
					}
					else
					{
						// Redirect user to password reset page
						header("location: resetPassword.php");
					}

					die();
				} else {

					if(isset($_SESSION["agentID"]) && $_SESSION["agentID"] > 0)
					{
						$sql = "SELECT w9_signature FROM Pers WHERE Pers_ID=".$_SESSION["agentID"];
						$result = mysqli_query($link, $sql);
						$row = mysqli_fetch_assoc($result);

						if($row["w9_signature"])
						{
							header("location: index.php");
							die();
						}else{
							header("location: stand_alone_signature.php?agentID=".base64_encode($_SESSION["agentID"]));
							die();
						}
					}

					// Redirect user to main page
					header("location: index.php");
					die();
				}

			} else {
				// Password is not valid, display a generic error message
				$login_err = "Invalid username or password.";
			}


			// If we ended up here, go back to login.
			header("location: login.php");
			die();




			// Store result
			mysqli_stmt_store_result($stmt);

	/*

	echo "<br /><br />username=".$username;
	echo "<br /><br />password=".$password;
	echo "<br /><br />storedPassword=".$storedPassword;
	echo "<br /><br />hashed_password=".$hashed_password;
	echo "<br /><br />mustResetPassword=".$mustResetPassword;
	die();

	$result = mysqli_stmt_get_result($stmt);
	$row = mysqli_fetch_assoc($result);
	print_r($row);
	die();


				$getResult = mysqli_stmt_get_result($stmt);
				$row = mysqli_fetch_assoc($getResult);
				$mustResetPassword = $row["mustResetPassword"];
	echo "SELECT userID, username, password FROM Users WHERE Role_ID=1 AND username = ".$username;
	echo "<br /><br />mustResetPassword=".$mustResetPassword;
	die();

	$result = mysqli_stmt_get_result($stmt);
	$row = mysqli_fetch_assoc($result);
	$mustResetPassword=$row["mustResetPassword"];
	echo "<br /><br />username=".$username;
	echo "<br /><br />mustResetPassword=".$mustResetPassword;
	die();

			 */

			// Check if username exists, if yes then verify password
			if (mysqli_stmt_num_rows($stmt) == 1) {

				$hashed_password = password_hash($password, PASSWORD_DEFAULT);

				// Bind result variables
				mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);

				if (mysqli_stmt_fetch($stmt)) {
					if (password_verify($password, $hashed_password)) {
						// Password is correct, so start a new session
						session_start();

						// Store data in session variables
						$_SESSION["admin_loggedin"] = true;
						$_SESSION["admin_id"] = $id;
						$_SESSION["admin_username"] = $username;

						// If this is a new user login, then they must change their password
						if ($mustResetPassword == "Y") {
							// Redirect user to password reset page
							if(isset($_GET["FromEmail"]))
							{
								// Redirect user to password reset page
								header("location: resetPassword.php?FromEmail=true");
							}
							else
							{
								// Redirect user to password reset page
								header("location: resetPassword.php");
							}
						} else {
							// Redirect user to main page
							header("location: index.php");
						}

					} else {
						// Password is not valid, display a generic error message
						$login_err = "Invalid username or password.";
					}
				}
			}
		}


  /*
        if($stmt = mysqli_prepare($link, $sql)){

            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){
					$hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);

                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
	echo "correct password!";
	die();
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            // Redirect user to main page
                            header("location: index.php");
                        } else{
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    // Username does not exist, display a generic error message
                    $login_err = "Invalid username or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }

		 */

	}

    // Close connection
	mysqli_close($link);
}



?>
<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Vital Trends Portal</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="./images/favicon.ico">
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
                                    <h4 class="text-center mb-4 text-white">Sign in to the Vital Trends Portal</h4>
	<?php
if (!empty($login_err)) {
	echo '<div class="alert alert-danger">' . $login_err . '</div>';
}
?>
								    <form action="" method="POST">
                                        <div class="form-group">
                                            <label class="mb-1 text-white"><strong>Username</strong></label>
                                            <input type="text" name="username" class="form-control" value="">
                                        </div>
                                        <div class="form-group">
                                            <label class="mb-1 text-white"><strong>Password</strong></label>
                                            <input type="password" name="password" class="form-control" value="">
                                        </div>
                                        <div class="form-row d-flex justify-content-between mt-4 mb-2">
<!---
                                            <div class="form-group">
                                               <div class="custom-control custom-checkbox ml-1 text-white">
													<input type="checkbox" class="custom-control-input" id="basic_checkbox_1">
													<label class="custom-control-label" for="basic_checkbox_1">Remember my preference</label>
												</div>
                                            </div>
--->

                                            <!-- <div class="form-group">
                                                <a class="text-white" href="page-forgot-password.html">Forgot Password?</a>
                                            </div> -->

											<div class="form-group">
                                                <a class="text-white" href="forgot-password.php">Forgot Password?</a>
                                            </div>

                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn bg-white text-primary btn-block">Sign Me In</button>
                                        </div>
                                    </form>
<!---
                                    <div class="new-account mt-3">
                                        <p class="text-white">Don't have an account? <a class="text-white" href="./page-register.html">Sign up</a></p>
                                    </div>
--->
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