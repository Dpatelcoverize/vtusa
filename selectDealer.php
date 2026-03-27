<?php
//
// File: selectDealer.php
// Author: Charles Parry
// Date: 7/11/2022
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


// Variables.
$Acct_ID = "";
$dealerName = "";
$username = "";
$login_err = "";

if(!isset($_SESSION["login_username"])){
	// Redirect user to login again
	header("location: login.php");
	die();
}else{
	$username = $_SESSION["login_username"];
}

if(!isset($_SESSION["login_password"])){
	// Redirect user to login again
	header("location: login.php");
	die();
}



?>
<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Vital Trends Portal - Select Dealer</title>
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
                                    <h4 class="text-center mb-4 text-white">Select dealer to which you wish to authenticate</h4>

                                    <table class="table table-responsive-md">
                                        <thead>
                                            <tr>
                                                <th>Dealer</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

<?php
if(!empty($login_err)){
	echo '<div class="alert alert-danger">' . $login_err . '</div>';
}
?>

<?php
// Get list of dealers associated with this username.
$query = "SELECT * FROM Usr_Loc ul, `Users` u, Acct a WHERE u.`username` = ? AND ul.Usr_ID = u.UserID AND ul.Dlr_Acct_ID = a.Acct_ID";
$stmt = mysqli_prepare($link, $query);

$val1 = $username;
mysqli_stmt_bind_param($stmt, "s", $val1);
if(mysqli_stmt_execute($stmt)){
	$result = mysqli_stmt_get_result($stmt);
//	$row = mysqli_fetch_assoc($result);

	while ($row = mysqli_fetch_assoc($result)) {

?>
<tr>
	<td><?php echo $row["Acct_Nm"]?></td>
	<td><form action="login.php" method="POST">
			<input type="hidden" name="dealerID" value="<?php echo $row["Acct_ID"]; ?>"/>
			<input type="submit" value="Select" />
		</form>
	</td>
</tr>

<?php
	}


}

?>
										</tbody>
									</table>
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