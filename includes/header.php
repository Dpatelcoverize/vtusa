<?php
//
// File: includes/header.php
// Author: Charles Parry
// Date: 5/7/2022
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize the session
// session_start();

// Connect to DB
require_once "dbConnect.php";


// Variables
$acct_ID = "";
$role_ID = "";
$userID = "";
$persID = "";
$username = "";
$userType = "";
$adminID = "";
$adminUsername = "";
$adminEmail = "";
$adminTelephone = "";
$adminLoggedIn = "N";

$dealerName = "";
$dealerUserFirstName = "";
$dealerUserLastName = "";
$dealerAgreementComplete = "";
$dealerW9Complete = "";
$dealerMultipleLocations = "";
$dealerMultipleLocationsComplete = "";
$dealerSetupComplete = "";
$dealerAddendumComplete = "";
$dealerFeeFormComplete = "";
$dealerBankingComplete = "";
$dealerARNComplete = "";

$dealerAgreementPDF = "";
$dealerW9PDF = "";
$dealerAddendumPDF = "";
$dealerAffiliateFeePDF = "";
$dealerSetupPDF = "";
$quotePDF = "";
$warrantyPDF = "";

$accountTypeCode = "";
$accountTypeDesc = "";

$isContactPerson = "";

// Navigation hints
if (!isset($navSection)) {
	$navSection = "signup";
}

if (!isset($navItem)) {
	$navItem = "agreement";
}



// Make sure an admin or dealer is authenticated, or fail back to login via logout.
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] != true && !isset($_GET["FromEmail"])) {
	if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
		header("location: logout.php");
	}
}

// Make sure an admin or dealer is authenticated, or fail back to login via logout.
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] != true && isset($_GET["FromEmail"])) {
	if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
		header("location: logout.php?FromEmail=true");
	}
}


// Admin Variables.
if (isset($_SESSION["admin_id"])) {
	$adminID = $_SESSION["admin_id"];
}

if (isset($_SESSION["admin_username"])) {
	$adminUsername = $_SESSION["admin_username"];
}

if (isset($_SESSION["role_ID"])) {
	$role_ID = $_SESSION["role_ID"];
}

if (isset($_SESSION["persID"])) {
	$persID = $_SESSION["persID"];
}

if (isset($_SESSION["admin_loggedin"])) {
	$adminLoggedIn = $_SESSION["admin_loggedin"];
}



// User Variables.
if (isset($_SESSION["id"])) {
	$acct_ID = $_SESSION["id"];
}

if (isset($_SESSION["userID"])) {
	$userID = $_SESSION["userID"];
}

if (isset($_SESSION["username"])) {
	$username = $_SESSION["username"];
}
if (isset($_SESSION["userType"])) {
	$userType = $_SESSION["userType"];
}

// Determine if the logged in person is an agent or a primary
//  This will impact security on what they are shown
//  NB this needs to be determined in a more solid way going forward, but
//   this allows us to work through security issues for now.  cparry 1/31/2023
if (isset($_SESSION["isContactPerson"]) && ($_SESSION["isContactPerson"] != "")) {
	$isContactPerson = $_SESSION["isContactPerson"];
} else {
	if ($userID != "") {

		$contactQuery = "SELECT p.Pers_ID, p.Cntct_Prsn_For_Acct_Flg FROM Users u, Pers p WHERE u.userID=" . $userID . " AND u.Pers_ID=p.Pers_ID";
		$contactResult = $link->query($contactQuery);

		if ($contactResult->num_rows > 0) {
			$contactRow = $contactResult->fetch_assoc();

			$isContactPerson = $contactRow["Cntct_Prsn_For_Acct_Flg"];
			$persID = $contactRow["Pers_ID"];
		} else {
			$isContactPerson = "N";
		}

		$_SESSION["isContactPerson"] = $isContactPerson;
		$_SESSION["persID"] = $persID;
	}
}


if (!isset($pageTitle)) {
	$pageTitle = "default page";
}

if (!isset($pageBreadcrumb)) {
	$pageBreadcrumb = "default breadcrumb";
}

$roleID = "";
$isVTAgent = "N";
$vtAgentPersID = "";
$isAgencyAgent = "N";
$agencyAccountID = "";
$agencyAgentPersID = "";

if ($adminID == "") {
	try {

		/* Execute the statement */
		$queryString = "SELECT * FROM Users u, Tel t, Email m WHERE u.username='Josh' AND
						u.Pers_ID = t.Pers_ID AND
						m.Pers_ID = u.Pers_ID";

		$result = $link->query($queryString);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();

			$adminID = $row["userID"];
			$adminUsername = $row["firstName"] . " " . $row["lastName"];
			$adminTelephone = $row["Tel_Nbr"];
			$adminEmail = $row["Email_URL_Desc"];

			$_SESSION["admin_id"] = $adminID;
		}
	} catch (Exception $e) {
		echo $e->getMessage();
		die();
	}
} else {
	try {

		/* Execute the statement */
		$queryString = "SELECT * FROM Users u, Tel t, Email m WHERE u.userID=" . $adminID . " AND
		u.Pers_ID = t.Pers_ID AND
		m.Pers_ID = u.Pers_ID";

		$result = $link->query($queryString);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();

			$adminUsername = $row["firstName"] . " " . $row["lastName"];
			$adminTelephone = $row["Tel_Nbr"];
			$adminEmail = $row["Email_URL_Desc"];
		}
	} catch (Exception $e) {
		echo $e->getMessage();
		die();
	}


	/* Check the type of person logged in.  A VT Sales Agent or an Agency person */
	$queryString = "SELECT * FROM Users u, Acct a, Pers p, Usr_Loc ul WHERE u.userID=" . $adminID . " AND
	                u.userID=ul.usr_ID AND ul.pers_ID = p.Pers_ID AND ul.Dlr_Acct_ID = a.Acct_ID";

	$result = $link->query($queryString);

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();

		$roleID = $row["Role_ID"];
		$local_acct_ID = $row["Acct_ID"];
		$pers_ID = $row["Pers_ID"];

		// If this user is tied to the VT Dealer account, they are a VT sales agent
		if ($acct_ID == 1) {
			$isVTAgent = "Y";
			$vtAgentPersID = $pers_ID;
		}

		$accountTypeCode = $row["Acct_Type_Cd"];
		$accountTypeDesc = $row["Acct_Type_Desc"];

		if ($accountTypeCode == "A") {
			$isAgencyAgent = "Y";
			$agencyAccountID = $local_acct_ID;
			$agencyAgentPersID = $pers_ID;
		}
	}

	$_SESSION["roleID"] = $roleID;
	$_SESSION["isVTAgent"] = $isVTAgent;
	$_SESSION["vtAgentPersID"] = $vtAgentPersID;
	$_SESSION["isAgencyAgent"] = $isAgencyAgent;
	$_SESSION["agencyAccountID"] = $agencyAccountID;
	$_SESSION["agencyAgentPersID"] = $agencyAgentPersID;
}


if (($acct_ID != "" && $userType == "dealer") || ($acct_ID != "" && $userType == "Agent")) {

	try {

		/* Check on the dealer progress for the existing forms. */
		$queryString = "SELECT * FROM Dealer_Progress dp, Acct a WHERE dp.Acct_ID=" . $acct_ID . " AND dp.Acct_ID = a.Acct_ID";
		//$result = mysqli_query($link,"SELECT * FROM Dealer_Progress WHERE Acct_ID=".$userID);

		$result = $link->query($queryString);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$dealerAgreementComplete = $row["Dealer_Agreement_Complete"];
			$dealerW9Complete = $row["Dealer_W9_Complete"];
			$dealerBankingComplete = $row["Dealer_Banking_Complete"];
			$dealerSetupComplete = $row["Dealer_Setup_Complete"];
			$dealerAddendumComplete = $row["Dealer_Addendum_Complete"];
			$dealerFeeFormComplete = $row["Dealer_Fee_Form_Complete"];
			$dealerMultipleLocations = $row["Multiple_Locations"];
			$dealerMultipleLocationsComplete = $row["Dealer_Multiple_Locations_Complete"];
			$dealerARNComplete = $row["Dealer_ARN_Complete"];
			$dealerAgreementPDF = $row["Dlr_Agrmnt_PDF"];
		}

		/* get Pdf link */
		$queryString = "SELECT Path_to_File,File_Asset_Type_ID FROM File_Assets WHERE Acct_ID=" . $acct_ID;

		$result = $link->query($queryString);



		if ($result->num_rows > 0) {
			$allPDFs = $result;
			while ($row = $result->fetch_assoc()) {
				if ($row['File_Asset_Type_ID'] == 2)
					$dealerW9PDF = $row["Path_to_File"];
				else if ($row['File_Asset_Type_ID'] == 3)
					$dealerAddendumPDF = $row["Path_to_File"];
				else if ($row['File_Asset_Type_ID'] == 4)
					$dealerAffiliateFeePDF = $row["Path_to_File"];
				else if ($row['File_Asset_Type_ID'] == 5)
					$dealerSetupPDF = $row["Path_to_File"];
				else if ($row['File_Asset_Type_ID'] == 6)
					$quotePDF = $row["Path_to_File"];
				else if ($row['File_Asset_Type_ID'] == 7)
					$warrantyPDF = $row["Path_to_File"];
			}
		}
	} catch (Exception $e) {
		echo $e->getMessage();
		die();
	}

	// Get the dealer info to display in the header
	if ($acct_ID != "") {
		try {
			// If we have a userID, then look up the name associated with that account
			//  so that we have a consistent display in the header.
			if ($userID != "") {
				$queryString = "SELECT * FROM Usr_Loc ul, Acct a, Pers p WHERE
								ul.Usr_ID=" . $userID . " AND
								ul.Pers_ID = p.Pers_ID AND
								ul.Dlr_Acct_ID = a.Acct_ID;";
			} else {
				$queryString = "SELECT * FROM Usr_Loc ul, Acct a, Pers p WHERE
								ul.Dlr_Acct_ID=" . $acct_ID . " AND
								ul.Pers_ID = p.Pers_ID AND
								p.Cntct_Prsn_For_Acct_Flg='Y' AND
								p.Acct_ID = a.Acct_ID;";
			}
			//echo "queryString=".$queryString;
			//die();



			$result = $link->query($queryString);

			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();

				$dealerName = $row["Acct_Nm"];
				$dealerUserFirstName = $row["Pers_Frst_Nm"];
				$dealerUserLastName = $row["Pers_Last_Nm"];

				$accountTypeCode = $row["Acct_Type_Cd"];
				$accountTypeDesc = $row["Acct_Type_Desc"];
			}
		} catch (Exception $e) {
			echo $e->getMessage();
			die();
		}
	}
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Vital Trends - Portal - Dealer Agreement</title>
	<!-- Favicon icon -->
	<link rel="icon" type="image/png" sizes="16x16" href="./images/favicon.ico">
	<link href="./vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="./vendor/chartist/css/chartist.min.css">
	<link href="./vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
	<link href="./vendor/owl-carousel/owl.carousel.css" rel="stylesheet">
	<link href="./css/style.css" rel="stylesheet">
	<link href="./css/yearpicker.css" rel="stylesheet">
	<link
		href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&display=swap"
		rel="stylesheet">
	<style>
		.logo {
			width: 500px;
			height: 154px;
			margin: 0 auto;
			display: flex;
			align-items: center;
		}

		.logo img {
			max-width: 100%;
			height: auto;
		}

		.dealer-form {
			position: relative;
			background-position: center;
			background-repeat: no-repeat;
			background-size: contain;
			z-index: 1;
		}

		.dealer-form .form-control {
			background: transparent;
			color: #000000;
			border: 1px solid rgb(199, 200, 201);
		}

		.dealer-form .form-group label {
			color: #3d4465;
		}

		.swal2-actions {
			column-gap: 20px;
		}

		.watermark {
			top: 60%;
			left: 60%;
			transform: translate(-50%, -50%);
			opacity: 0.1;
			z-index: -1;
			position: fixed;
			max-width: 450px;
		}

		.watermark img {
			max-width: 100%;
			height: auto;
		}

		.terms-text {
			border: 1px solid rgb(199, 200, 201);
			border-radius: 4px;
			padding: 10px 20px !important;
			margin: 5px 5px 15px;
		}

		.brand-logo img {
			max-width: 100%;
			height: 85px;
		}

		.terms-text ol li {
			list-style: auto;
			padding: 0 10px;
			margin: 0 0 0 15px;
		}

		.terms-text ol li ol li {
			list-style: lower-alpha;
		}

		.terms-text ol li ol li ol li {
			list-style: lower-roman;
		}

		button:disabled {
			cursor: not-allowed;
			pointer-events: all !important;
		}
	</style>
</head>

<body>
	<!--*******************
        Preloader start
    ********************-->
	<div id="preloader">
		<div class="sk-three-bounce">
			<div class="sk-child sk-bounce1"></div>
			<div class="sk-child sk-bounce2"></div>
			<div class="sk-child sk-bounce3"></div>
		</div>
	</div>
	<!--*******************
        Preloader end
    ********************-->

	<!--**********************************
        Main wrapper start
    ***********************************-->
	<div id="main-wrapper">

		<!--**********************************
            Nav header start
        ***********************************-->
		<div class="nav-header">
			<a href="index.php" class="brand-logo pt-3">
				<img src="images/vt_logo.png" alt="Vital Trends" height="85" />
			</a>
			<div class="nav-control">
				<div class="hamburger">
					<span class="line"></span><span class="line"></span><span class="line"></span>
				</div>
			</div>
		</div>
		<!--**********************************
            Nav header end
        ***********************************-->
		<!--**********************************
            Chat box start
        ***********************************-->
		<div class="chatbox">
			<div class="chatbox-close"></div>
			<div class="custom-tab-1">
				<ul class="nav nav-tabs">
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#notes">Notes</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-toggle="tab" href="#alerts">Alerts</a>
					</li>
					<li class="nav-item">
						<a class="nav-link active" data-toggle="tab" href="#chat">Chat</a>
					</li>
				</ul>
			</div>
		</div>
		<!--**********************************
            Chat box End
        ***********************************-->

		<!--**********************************
            Header start
        ***********************************-->
		<div class="header">
			<div class="header-content">
				<nav class="navbar navbar-expand">
					<div class="collapse navbar-collapse justify-content-between">
						<div class="header-left">
							<div class="dashboard_bar">
								<!---Dashboard--->
							</div>
							<?php if ($username != "") { ?>
							<strong>
								<?php if ($accountTypeCode == "A") { ?>
								Sales Agency
								<?php } else {
										echo ucfirst($_SESSION['userType']);
									?>
								<?php } ?>
								:&nbsp;</strong> <?php echo $dealerName; ?> (Hello <?php echo $dealerUserFirstName; ?>!)

							<?php
								// If a dealer or dealer agent is directly logged in, be sure to use the
								//  more robust logout method to clear the session.
								if ($role_ID == 2 || $role_ID == 6) {
								?>
							(<a href="logout.php" class="text-danger">Logout Dealer</a>)
							<?php
								} else {
								?>
							(<a href="dealer_logout.php" class="text-danger">Logout</a>)
							<?php
								}
								?>

							<?php

							} ?>
						</div>
						<ul class="navbar-nav header-right">

							<li class="nav-item dropdown header-profile">
								<a class="nav-link" href="javascript:void(0)" role="button" data-toggle="dropdown">
									<div class="header-info">
										<span class="text-black"><strong>Sales Agent:</strong>
											<?php echo $adminUsername; ?> <br /><?php echo $adminTelephone; ?><br />
											<?php echo $adminEmail; ?></span>
										<p class="fs-12 mb-0">
											<!--- Role --->
										</p>
									</div>
									<img src="images/profile/17.jpg" width="20" alt="" />
								</a>
								<div class="dropdown-menu dropdown-menu-end" data-bs-popper="static">
									<a href="#" class="dropdown-item ai-icon">
										<svg id="icon-user1" xmlns="http://www.w3.org/2000/svg" class="text-primary"
											width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
											stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
											<circle cx="12" cy="7" r="4"></circle>
										</svg>
										<span class="ml-2">Profile </span>
									</a>
									
									<a href="logout.php" class="dropdown-item ai-icon">
										<svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger"
											width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
											stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
											<polyline points="16 17 21 12 16 7"></polyline>
											<line x1="21" y1="12" x2="9" y2="12"></line>
										</svg>
										<span class="ml-2">Logout</span>
									</a>
								</div>
							</li>
						</ul>
					</div>
				</nav>
			</div>
		</div>
		<!--**********************************
            Header end ti-comment-alt
        ***********************************-->

		<!--**********************************
            Sidebar start
        ***********************************-->
		<div class="deznav">
			<div class="deznav-scroll">
				<?php
				// Show the dealer login form, if there is not a dealer currently authenticated
				if ($userType != "dealer" && false) {
				?>
				<!--- dealer login --->
				<div class="basic-form">
					<br />
					<h4 class="card-title">Dealer Login</h4>
					<form name="dealerLoginForm" action="dealer_login.php" method="POST">
						<div class="form-group row">
							<div class="col-sm-12">
								<input type="email" name="dealer_username" class="form-control" placeholder="Email">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-sm-12">
								<input type="password" name="dealer_password" class="form-control"
									placeholder="Password">
							</div>
						</div>
						<div class="form-group row">
							<div class="col-sm-12">
								<button type="submit" class="btn btn-primary">Sign in</button>
							</div>
						</div>
					</form>
				</div>
				<!--- END dealer login --->
				<?php

				}
				?>

				<ul class="metismenu" id="menu">
					<?php
					// dealer menu security
					if (($accountTypeCode == "A") || ($userType == "dealer" && $isContactPerson == "Y") || ($adminLoggedIn)) {
					?>
					<li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="<?php if ($navSection == "signup") {
																										echo "true";
																									} else {
																										echo "false";
																									} ?>">
							<i class="flaticon-381-networking"></i>
							<span class="nav-text">Dealer Signup</span>
						</a>
						<ul aria-expanded="false">
							<?php

								// If a Sales Agency is logged in, allow them to create new dealers.
								if ($accountTypeCode == "A") {

								?>

							<li>
								<a href="dealer_agreement_v3.php">
									New Dealer Agreement
								</a>
							</li>

							<?php

								} else {

								?>

							<li>
								<a href="dealer_agreement_v3.php?acctID=<?php echo $acct_ID; ?>">
									<?php if ($dealerAgreementComplete == "Y") { ?>
									<img src="images/green_check.png" height="10" width="10"
										alt="Dealer Agreement Complete" />
									<?php
											} ?>
									Dealer Agreement
								</a>
								<?php if ($dealerAgreementComplete == "Y") { ?>
								<span style="padding: 0px !important;">
									<a href="/uploads/dealer_agreement_pdf/<?php echo $dealerAgreementPDF; ?>"
										target="_blank">PDF</a>
								</span>
								<?php
										} ?>
							</li>

							<?php

								} // if($accountTypeCode=="A") //

								?>



							<?php if ($userType == "dealer") { ?>
							<li>
								<a href="dealer_w9.php">
									<?php if ($dealerW9Complete == "Y") { ?>
									<img src="images/green_check.png" height="10" width="10" alt="Dealer W9 Complete" />
									<?php
											} ?>
									Dealer W-9
								</a>
								<?php if ($dealerW9Complete == "Y" && !empty($dealerW9PDF)) { ?>
								<span style="padding: 0px !important;">
									<!-- <a href="<?php echo $dealerW9PDF; ?>" target="_blank">PDF</a> -->
									<!---<a href="../allPDF.php?fileTypeID=2">PDFs</a>--->
								</span>
								<?php
										} ?>
							</li>
							<!---<li><a href="dealer_banking.php"><?php if ($dealerBankingComplete == "Y") { ?><img src="images/green_check.png" height="10" width="10" alt="Dealer Banking Complete"/> <?php

																																																			} ?>Dealer Banking</a></li>--->
							<?php if ($dealerMultipleLocations == "Y") { ?> <li><a
									href="dealer_multiple_locations.php"><?php if ($dealerMultipleLocationsComplete == "Y") { ?><img
										src="images/green_check.png" height="10" width="10" alt="Dealer W9 Complete" /> <?php

																																																																		} ?>Dealer Locations</a></li><?php

																																																																									} ?>
							<!---<li><?php if ($dealerSetupComplete != "Y") { ?><a href="dealer_setup.php"><?php

																												} else { ?><img src="images/green_check.png" height="10" width="10" alt="Dealer Setup Complete"/> <?php

																																																				} ?>Dealer Setup<?php if ($dealerSetupComplete != "Y") { ?></a><?php

																																																																			} ?></li>--->
							<li>
								<a href="dealer_setup.php">
									<?php if ($dealerSetupComplete == "Y") { ?>
									<img src="images/green_check.png" height="10" width="10"
										alt="Dealer Setup Complete" />
									<?php
											} ?>
									Dealer Setup
								</a>
								<?php if ($dealerSetupComplete == "Y" && !empty($dealerSetupPDF)) { ?>
								<span style="padding: 0px !important;">
									<!-- <a href="<?php echo $dealerSetupPDF; ?>" target="_blank">PDF</a> -->
									<!---<a href="../allPDF.php?fileTypeID=5">PDFs</a>--->
								</span>
								<?php
										} ?>
							</li>
							<li><a href="dealer_addendum.php"><?php if ($dealerAddendumComplete == "Y") { ?><img
										src="images/green_check.png" height="10" width="10"
										alt="Dealer Addendum Complete" /> <?php

																																																			} ?>Dealer
									Addendum</a><?php if ($dealerAddendumComplete == "Y" && !empty($dealerAddendumPDF)) { ?><span
									style="padding: 0px !important;">
									<!---<a href="../allPDF.php?fileTypeID=3">PDFs</a>---></span><?php

																																																																																																				} ?></li>
							<li>
								<a href="manage_ar_number.php">
									<?php if ($dealerARNComplete == "Y") {
											?>
									<img src="images/green_check.png" height="10" width="10"
										alt="Dealer Affiliate Fee Complete" />
									<?php } ?>
									Manage AR Number
								</a>
							</li>
							<!---<li><?php if ($dealerFeeFormComplete != "Y") { ?><a href="dealer_affiliate_fee.php"><?php

																															} else { ?><img src="images/green_check.png" height="10" width="10" alt="Dealer Affiliate Fee Complete"/> <?php

																																																									} ?>Affiliate Fee Form<?php if ($dealerFeeFormComplete != "Y") { ?></a><?php

																																																																										} ?></li>--->
							<li style="margin-left:1.3rem;font-weight:bold;"><?php if ($dealerFeeFormComplete == "Y") { ?><img src="images/green_check.png" height="10" width="10"
										alt="Dealer Affiliate Fee Complete" /> <?php

																																																												} ?><strong
									style="padding-left: 3rem;">Affiliate Fee Forms:</strong>
								<?php if ($dealerFeeFormComplete == "Y" && !empty($dealerAffiliateFeePDF)) { ?><span
									style="padding: 0px !important;">
									<!---<a href="../allPDF.php?fileTypeID=4">PDFs</a>---></span><?php

																																																																																																								} ?></li>
							<li><a href="dealer_banking.php"><?php if ($dealerBankingComplete == "Y") { ?><img
										src="images/green_check.png" height="10" width="10"
										alt="Dealer Affiliate Fee Complete" /> <?php

																																																				} ?>Dealer</a></li>
							<li><a href="dealer_affiliate_fee.php"><?php if ($dealerFeeFormComplete == "Y") { ?><img
										src="images/green_check.png" height="10" width="10"
										alt="Dealer Affiliate Fee Complete" /> <?php

																																																					} ?>Personnel</a></li>
					</li>
					<?php

								} else { ?>
					<li><a href="#">Dealer W-9</a></li>
					<li><a href="#">Dealer Setup</a></li>
					<li><a href="#">Dealer Addendum</a></li>
					<li><a href="#">Affiliiate Fee Forms</a></li>
					<?php

								} ?>
				</ul>
				</li>
				<?php

					} // dealer menu security.

			?>

				<?php
			if ((isset($_SESSION["admin_username"]) && $_SESSION["admin_username"] != "service")
				|| ($role_ID == 2)
			) {
			?>

				<li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="<?php if ($navSection == "warranty") {
																								echo "true";
																							} else {
																								echo "false";
																							} ?>">
						<i class="flaticon-381-networking"></i>
						<span class="nav-text">Dealer Warranty</span>
					</a>
					<ul aria-expanded="false">
						<li><a href="create_warranty.php?isQuote=Y">Create Quote</a><?php if (!empty($quotePDF)) { ?>
							<!--
							<span style="padding: 0px !important;">
								<a href="../allPDF.php?fileTypeID=6">PDFs</a>
							</span>
							-->
							<?php
																					} ?>
						</li>
						<li><a href="warranty_pending.php?showQuotes=Y">Pending Quotes</a></li>
						<li><a href="create_warranty.php">Create Warranty</a><?php if (!empty($warrantyPDF)) { ?>
							<!--
							<span style="padding: 0px !important;">
								<a href="../warranty_pdf.php">PDFs</a>
								<a href="../allPDF.php?fileTypeID=7">PDFs</a>
							</span>
							-->
							<?php
																				} ?>
						</li>
						<li><a href="warranty_pending.php">Pending Warranties</a></li>
						<li><a href="finalized_warranty.php">Finalized Warranties</a></li>
					</ul>
				</li>
				<?php
			}
			?>

				<?php

			// Only show this menu item a VT Admin is logged in, role_ID = 1.
			if ((isset($role_ID) && $role_ID == 1) &&
				((isset($_SESSION["admin_username"]) && $_SESSION["admin_username"] != "service"))
			) {

			?>

				<li>
					<a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="true">
						<i class="flaticon-381-networking"></i>
						<span class="nav-text">Pending Items</span>
					</a>
					<ul aria-expanded="false">
						<li><a href="../pending_agreements.php">Dealer Agreements</a></li>
						<li><a href="../pending_warranties.php">Warranty Drafts</a></li>
					</ul>
				</li>
				<?php
				if ((isset($_SESSION["roleID"]) && $_SESSION["roleID"] == 1) && ((isset($_SESSION["admin_username"]) && $_SESSION["admin_username"] != "service") &&
					(isset($_SESSION["userType"]) && $_SESSION["userType"] != "dealer")  &&
					(isset($_SESSION["userType"]) && $_SESSION["userType"] != "Agent")
				)) {
				?>
				<li>
					<a href="manage_users.php"><i class="flaticon-381-networking"></i>
						<span class="nav-text">Manage Users</span></a>
				</li>
				<!-- <li>
						<a href="manage_warranty.php?isQuote=Y"><i class="flaticon-381-networking"></i>
							<span class="nav-text">Manage Quotes</span></a>
					</li>
					<li>
						<a href="manage_warranty.php"><i class="flaticon-381-networking"></i>
							<span class="nav-text">Manage Warranty</span></a>
					</li> -->
				<?php
				}
				?>

				<?php

			}

			?>
				<li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="<?php if ($navSection == "docs") {
																							echo "true";
																						} else {
																							echo "false";
																						} ?>">
						<i class="flaticon-381-networking"></i>
						<span class="nav-text">Doc Center</span>
					</a>
					<ul aria-expanded="false">
						<!---
							<li><a href="">Sample Forms</a></li>
							<li><a href="">Completed Dealer Forms</a></li>
							--->
						<?php if (false) { ?><li><a href="../generated_pdfs.php">Dealer Doc PDFs</a></li><?php } ?>
				</li>
				<?php if ($acct_ID == "") {
				// I am removing the requirement for a dealer to be logged in for
				//  navigation to the docs pages.  cparry@gmail.com 8/16/2022.
			?>
				<li><a href="../fillable_documents.php">Fillable Documents</a></li>
				<?php } else {
			?>
				<li><a href="../fillable_documents.php">
						<span class="nav-text">Fillable Documents</span>
					</a>
				</li>
				<?php } ?>

				<?php if ($acct_ID == "") {
			?>
				<li><a href="../samples_documents.php">Sample Contracts</a></li>
				<li><a href="../marketing_documents.php">
						<span class="nav-text">Marketing Materials</span>
					</a>
				</li>
				<?php } else {
			?>
				<li><a href="../samples_documents.php">
						<span class="nav-text">Sample Contracts</span>
					</a>
				</li>
				<li><a href="../marketing_documents.php">
						<span class="nav-text">Marketing Materials</span>
					</a>
				</li>
				<?php } ?>

				<?php if (false) { ?><li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
						<span class="nav-text">Brochures</span>
					</a>
					<ul aria-expanded="false">
						<li style="padding-left: 20px;background: #e8e8e8;"><a style="font-size: 14px"
								href="../uploads/brochures/Wrap_Program Vital_Trends_Trifold_Brochure.pdf"
								target="__blank">Wrap Program</a></li>
						<?php if (false) { ?><li style="padding-left: 20px;background: #e8e8e8;"><a
								style="font-size: 14px"
								href="../uploads/brochures/Emergency_Vehicle_Warranty_Submission.pdf"
								target="__blank">Inspection Requirements</a></li><?php } ?>
						<li style="padding-left: 20px;background: #e8e8e8;"><a style="font-size: 14px"
								href="../uploads/brochures/VT_Program_Highlights.pdf" target="__blank">Program
								Highlights</a></li>
						<?php if (false) { ?><li style="padding-left: 20px;background: #e8e8e8;"><a
								style="font-size: 14px" href="../uploads/brochures/VITAL_TRENDS_Booklet.pdf"
								target="__blank">Vital Trends Booklet</a></li><?php } ?>
						<li style="padding-left: 20px;background: #e8e8e8;"><a style="font-size: 14px"
								href="../uploads/brochures/Vital_Trends_Trifold_Brochure.pdf" target="__blank">Vital
								Trends Trifold</a></li>
						<li style="padding-left: 20px;background: #e8e8e8;"><a style="font-size: 14px"
								href="../uploads/brochures/VT_CC_Sheet_v0822.pdf" target="__blank">Vital Trends Covered
								Components</a></li>
					</ul>
				</li><?php } ?>

				</ul>
				</li>
				<?php

			// Only show this menu item if a dealer is NOT logged in, but just a VT admin
			// (isset($_SESSION["userType"]) && $_SESSION["userType"]!="Agent")
			if ((isset($_SESSION["admin_username"]) && $_SESSION["admin_username"] != "service") &&
				(isset($_SESSION["userType"]) && $_SESSION["userType"] != "dealer")
			) {

			?>

				<li>
					<a href="my_dealers.php"><i class="flaticon-381-networking"></i>
						<span class="nav-text">My Dealers</span></a>
				</li>
				<!--
                    <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
							<i class="flaticon-381-networking"></i>
							<span class="nav-text">My Dealers</span>
						</a>
                        <ul aria-expanded="false">
							<li><a href="my_dealers.php">See My Dealers</a></li>
						</ul>
                    </li>
                    <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
							<i class="flaticon-381-networking"></i>
							<span class="nav-text">Agent Signup</span>
						</a>
                        <ul aria-expanded="false">
							<li><a href="index.html">Agent Agreement</a></li>
							<li><a href="index2.html">Commission Payment</a></li>
							<li><a href="search-job.html">Agent Info Sheet</a></li>
							<li><a href="application.html">Agent W-9</a></li>
						</ul>
                    </li>
					-->
				<?php

			}
			?>

				<?php

			// Only show this menu item if a dealer is NOT logged in, but just a VT admin
			if ((isset($_SESSION["admin_username"]) && $_SESSION["admin_username"] != "service") &&
				(isset($_SESSION["userType"]) && $_SESSION["userType"] != "dealer")  &&
				(isset($_SESSION["userType"]) && $_SESSION["userType"] != "Agent")
			) {

			?>

				<li>
					<a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
						<i class="flaticon-381-networking"></i>
						<span class="nav-text">Reporting</span>
					</a>
					<ul aria-expanded="false">
						<li><a href="reporting-dashboard.php">Finance</a></li>
						<li><a href="#">Claims</a></li>
						<li><a href="#">Vital Trends News</a></li>
						<li><a href="#">Sales & Contracts</a></li>
						<li><a href="quote_report.php">Quote Report</a></li>
						<li><a href="email_report.php">Email Report</a></li>
						<li><a href="exception_logs.php">Exception Logs</a></li>
					</ul>
				</li>
				<?php

			}


			// Only show this menu item if a dealer is NOT logged in, but just a VT admin
			if ((isset($_SESSION["admin_username"]) && $_SESSION["admin_username"] != "service") &&
				(isset($_SESSION["userType"]) && $_SESSION["userType"] != "dealer")  &&
				(isset($_SESSION["userType"]) && $_SESSION["userType"] != "Agent")
			) {
			?>
				<li>
					<a href="dealerAgent.php"><i class="flaticon-381-networking"></i>
						<span class="nav-text">My Agents</span></a>
				</li>
				<?php

			}

			?>
				<?php
			if ((isset($role_ID) && $role_ID == 1) && (isset($_SESSION["admin_username"]) && $_SESSION["admin_username"] != "service") &&
				(isset($_SESSION["userType"]) && $_SESSION["userType"] != "dealer")  &&
				(isset($_SESSION["userType"]) && $_SESSION["userType"] != "Agent")
			) {
			?>
				<li>
					<a href="loginAs.php"><i class="flaticon-381-networking"></i>
						<span class="nav-text">Login As</span></a>
				</li>


				<?php } ?>

				</ul>

				<div class="copyright">
					<p><strong>Vital Trends Portal Admin Dashboard</strong> &copy; 2022 All Rights Reserved</p>
					<!---<p>Made with <span class="heart"></span> by DexignZone</p>--->
				</div>
			</div>
		</div>
		<!--**********************************
            Sidebar end
        ***********************************-->