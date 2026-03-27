<?php
//
// File: includes/header.php
// Author: Charles Parry
// Date: 5/7/2022
//

// Turn on error reporting
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Initialize the session
//session_start();

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
if(!isset($navSection)){
	$navSection="signup";
}

if(!isset($navItem)){
	$navItem="agreement";
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



/*
// SPECIAL CASE
//  In order to support the 'service account' with which a dealer can log in to the
//   portal and create an agreement, without a VT admin being there with them, and without
//   sharing VT admin credentials, there is a 'service' user which comes in with admin rights.
//   HOWEVER we want to strip those admin rights at this time.
if($adminUsername=="service"){
	$_SESSION["admin_id"] = "";
	$_SESSION["admin_username"] = "";
}
*/


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
if (isset($_SESSION["isContactPerson"]) && ($_SESSION["isContactPerson"]!="")) {
	$isContactPerson = $_SESSION["isContactPerson"];

}else{
	if($userID!=""){

		$contactQuery = "SELECT p.Pers_ID, p.Cntct_Prsn_For_Acct_Flg FROM Users u, Pers p WHERE u.userID=".$userID." AND u.Pers_ID=p.Pers_ID";
		$contactResult = $link->query($contactQuery);

	//echo "contactQuery=".$contactQuery;
	//die();

		if ($contactResult->num_rows > 0) {
			$contactRow = $contactResult->fetch_assoc();

			$isContactPerson = $contactRow["Cntct_Prsn_For_Acct_Flg"];
			$persID = $contactRow["Pers_ID"];
		}else{
			$isContactPerson = "N";
		}

		$_SESSION["isContactPerson"] = $isContactPerson;
		$_SESSION["persID"] = $persID;

	}
}
//echo "isContactPerson=".$isContactPerson;


if (!isset($pageTitle)) {
	$pageTitle = "default page";
}

if (!isset($pageBreadcrumb)) {
	$pageBreadcrumb = "default breadcrumb";
}


// Some required variables to support the new vt agent and agency agent concept
$roleID = "";
$isVTAgent = "N";
$vtAgentPersID = "";
$isAgencyAgent = "N";
$agencyAccountID = "";
$agencyAgentPersID = "";

// Set the default sales agent to Josh, if we did not get one upon login.
if ($adminID == "") {
	try {

		/* Execute the statement */
		//$queryString = "SELECT * FROM Users WHERE username='Josh'";
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
	$queryString = "SELECT * FROM Users u, Acct a, Pers p, Usr_Loc ul WHERE u.userID=".$adminID." AND
	                u.userID=ul.usr_ID AND ul.pers_ID = p.Pers_ID AND ul.Dlr_Acct_ID = a.Acct_ID";

	$result = $link->query($queryString);

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();

		$roleID = $row["Role_ID"];
		$local_acct_ID = $row["Acct_ID"];
		$pers_ID = $row["Pers_ID"];

		// If this user is tied to the VT Dealer account, they are a VT sales agent
		if($acct_ID == 1){
			$isVTAgent = "Y";
			$vtAgentPersID = $pers_ID;
		}

		$accountTypeCode = $row["Acct_Type_Cd"];
		$accountTypeDesc = $row["Acct_Type_Desc"];

		if($accountTypeCode=="A"){
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

// Look up some contract progress info if we have an authenticated dealer session.
//echo "acct_id=".$acct_ID;
//die();


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

			/* Execute the statement */

/*
			$queryString = "SELECT * FROM Users u, Acct a, Pers p WHERE a.Acct_ID=" . $acct_ID . " AND a.Acct_ID = u.Acct_ID AND u.Pers_ID = p.Pers_ID";

			if($userID != ""){
				$queryString .= " AND userID=".$userID;
			}
*/

			// If we have a userID, then look up the name associated with that account
			//  so that we have a consistent display in the header.
			if($userID!=""){
				$queryString = "SELECT * FROM Usr_Loc ul, Acct a, Pers p WHERE
								ul.Usr_ID=" . $userID . " AND
								ul.Pers_ID = p.Pers_ID AND
								ul.Dlr_Acct_ID = a.Acct_ID;";
			}else{
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
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
    <style>
        .logo{
            width: 500px;
            height: 154px;
            margin: 0 auto;
            display: flex;
            align-items: center;
        }
        .logo img{
            max-width: 100%;
            height: auto;
        }

        .dealer-form{
            position: relative;
            background-position: center;
            background-repeat: no-repeat;
            background-size: contain;
            z-index: 1;
        }
        .dealer-form .form-control{
            background: transparent;
            color: #3d4465;
            border:1px solid rgb(199, 200, 201);
        }
        .dealer-form .form-group label{
            color: #3d4465;
		}

		.swal2-actions {
    			column-gap: 20px;
				}

        .watermark{
            top: 60%;
            left: 60%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: -1;
            position: fixed;
            max-width: 450px;
        }
        .watermark img{
            max-width: 100%;
            height: auto;
        }

        .terms-text{
            border: 1px solid rgb(199, 200, 201);
            border-radius: 4px;
            padding: 10px 20px !important;
            margin: 5px 5px 15px;
        }
        .brand-logo img{
            max-width: 100%;
            height: auto;
        }
        .terms-text ol li{
            list-style: auto;
            padding: 0 10px;
            margin: 0 0 0 15px;
        }
        .terms-text ol li ol li{
            list-style: lower-alpha;
        }
        .terms-text ol li ol li ol li{
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
				<img src="images/vt_logo.png" alt="Vital Trends" />
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
									<?php if($accountTypeCode=="A"){ ?>
										Sales Agency
									<?php }else{ ?>
										Dealer
									<?php } ?>
								:&nbsp;</strong> <?php echo $dealerName; ?> (Hello <?php echo $dealerUserFirstName; ?>!)

								<?php
									// If a dealer or dealer agent is directly logged in, be sure to use the
									//  more robust logout method to clear the session.
									if($role_ID==2 || $role_ID==6){
								?>
										(<a href="logout.php">logout dealer</a>)
								<?php
									}else{
								?>
										(<a href="dealer_logout.php">logout dealer</a>)
								<?php
									}
								?>

							<?php

					} ?>
                        </div>
                        <ul class="navbar-nav header-right">
<!---
							<li class="nav-item">
								<div class="input-group search-area d-xl-inline-flex d-none">
									<input type="text" class="form-control" placeholder="Search here...">
									<div class="input-group-append">
										<span class="input-group-text"><a href="javascript:void(0)"><i class="flaticon-381-search-2"></i></a></span>
									</div>
								</div>
							</li>
							<li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link  ai-icon" href="javascript:void(0)" role="button" data-toggle="dropdown">
                                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M10.4525 25.6682C11.0606 27.0357 12.4091 28 14.0006 28C15.5922 28 16.9407 27.0357 17.5488 25.6682C16.4266 25.7231 15.2596 25.76 14.0006 25.76C12.7418 25.76 11.5748 25.7231 10.4525 25.6682Z" fill="#3E4954"/>
										<path d="M26.3531 19.74C24.8769 17.8785 22.3995 14.2195 22.3995 10.64C22.3995 7.09073 20.1192 3.89758 16.7995 2.72382C16.7592 1.21406 15.5183 0 14.0006 0C12.4819 0 11.2421 1.21406 11.2017 2.72382C7.88095 3.89758 5.60064 7.09073 5.60064 10.64C5.60064 14.2207 3.12434 17.8785 1.64706 19.74C1.15427 20.3616 1.00191 21.1825 1.24051 21.9363C1.47348 22.6721 2.05361 23.2422 2.79282 23.4595C4.08755 23.8415 6.20991 24.2715 9.44676 24.491C10.8479 24.5851 12.3543 24.64 14.0007 24.64C15.646 24.64 17.1524 24.5851 18.5535 24.491C21.7914 24.2715 23.9127 23.8415 25.2085 23.4595C25.9477 23.2422 26.5268 22.6722 26.7597 21.9363C26.9983 21.1825 26.8448 20.3616 26.3531 19.74Z" fill="#3E4954"/>
									</svg>
									<span class="badge light text-white bg-primary">3</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <div id="DZ_W_Notification1" class="widget-media dz-scroll p-3 height380">
										<ul class="timeline">
											<li>
												<div class="timeline-panel">
													<div class="media mr-2">
														<img alt="image" width="50" src="images/avatar/1.jpg">
													</div>
													<div class="media-body">
														<h6 class="mb-1">Dr sultads Send you Photo</h6>
														<small class="d-block">29 July 2020 - 02:26 PM</small>
													</div>
												</div>
											</li>
											<li>
												<div class="timeline-panel">
													<div class="media mr-2 media-info">
														KG
													</div>
													<div class="media-body">
														<h6 class="mb-1">Resport created successfully</h6>
														<small class="d-block">29 July 2020 - 02:26 PM</small>
													</div>
												</div>
											</li>
											<li>
												<div class="timeline-panel">
													<div class="media mr-2 media-success">
														<i class="fa fa-home"></i>
													</div>
													<div class="media-body">
														<h6 class="mb-1">Reminder : Treatment Time!</h6>
														<small class="d-block">29 July 2020 - 02:26 PM</small>
													</div>
												</div>
											</li>
											 <li>
												<div class="timeline-panel">
													<div class="media mr-2">
														<img alt="image" width="50" src="images/avatar/1.jpg">
													</div>
													<div class="media-body">
														<h6 class="mb-1">Dr sultads Send you Photo</h6>
														<small class="d-block">29 July 2020 - 02:26 PM</small>
													</div>
												</div>
											</li>
											<li>
												<div class="timeline-panel">
													<div class="media mr-2 media-danger">
														KG
													</div>
													<div class="media-body">
														<h6 class="mb-1">Resport created successfully</h6>
														<small class="d-block">29 July 2020 - 02:26 PM</small>
													</div>
												</div>
											</li>
											<li>
												<div class="timeline-panel">
													<div class="media mr-2 media-primary">
														<i class="fa fa-home"></i>
													</div>
													<div class="media-body">
														<h6 class="mb-1">Reminder : Treatment Time!</h6>
														<small class="d-block">29 July 2020 - 02:26 PM</small>
													</div>
												</div>
											</li>
										</ul>
									</div>
                                    <a class="all-notification" href="javascript:void(0)">See all notifications <i class="ti-arrow-right"></i></a>
                                </div>
                            </li>
--->
<!---
							<li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link bell bell-link" href="javascript:void(0)">
                                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M22.4605 3.84888H5.31688C4.64748 3.84961 4.00571 4.11586 3.53237 4.58919C3.05903 5.06253 2.79279 5.7043 2.79205 6.3737V18.1562C2.79279 18.8256 3.05903 19.4674 3.53237 19.9407C4.00571 20.4141 4.64748 20.6803 5.31688 20.6811C5.54005 20.6812 5.75404 20.7699 5.91184 20.9277C6.06964 21.0855 6.15836 21.2995 6.15849 21.5227V23.3168C6.15849 23.6215 6.24118 23.9204 6.39774 24.1818C6.5543 24.4431 6.77886 24.6571 7.04747 24.8009C7.31608 24.9446 7.61867 25.0128 7.92298 24.9981C8.22729 24.9834 8.52189 24.8863 8.77539 24.7173L14.6173 20.8224C14.7554 20.7299 14.918 20.6807 15.0842 20.6811H19.187C19.7383 20.68 20.2743 20.4994 20.7137 20.1664C21.1531 19.8335 21.4721 19.3664 21.6222 18.8359L24.8966 7.05011C24.9999 6.67481 25.0152 6.28074 24.9414 5.89856C24.8675 5.51637 24.7064 5.15639 24.4707 4.84663C24.235 4.53687 23.931 4.28568 23.5823 4.11263C23.2336 3.93957 22.8497 3.84931 22.4605 3.84888ZM23.2733 6.60304L20.0006 18.3847C19.95 18.5614 19.8432 18.7168 19.6964 18.8275C19.5496 18.9381 19.3708 18.9979 19.187 18.9978H15.0842C14.5856 18.9972 14.0981 19.1448 13.6837 19.4219L7.84171 23.3168V21.5227C7.84097 20.8533 7.57473 20.2115 7.10139 19.7382C6.62805 19.2648 5.98628 18.9986 5.31688 18.9978C5.09371 18.9977 4.87972 18.909 4.72192 18.7512C4.56412 18.5934 4.4754 18.3794 4.47527 18.1562V6.3737C4.4754 6.15054 4.56412 5.93655 4.72192 5.77874C4.87972 5.62094 5.09371 5.53223 5.31688 5.5321H22.4605C22.5905 5.53243 22.7188 5.56277 22.8353 5.62076C22.9517 5.67875 23.0532 5.76283 23.1318 5.86646C23.2105 5.97008 23.2642 6.09045 23.2887 6.21821C23.3132 6.34597 23.308 6.47766 23.2733 6.60304Z" fill="#3E4954"/>
										<path d="M7.84173 11.4233H12.0498C12.273 11.4233 12.4871 11.3347 12.6449 11.1768C12.8027 11.019 12.8914 10.8049 12.8914 10.5817C12.8914 10.3585 12.8027 10.1444 12.6449 9.98661C12.4871 9.82878 12.273 9.74011 12.0498 9.74011H7.84173C7.61852 9.74011 7.40446 9.82878 7.24662 9.98661C7.08879 10.1444 7.00012 10.3585 7.00012 10.5817C7.00012 10.8049 7.08879 11.019 7.24662 11.1768C7.40446 11.3347 7.61852 11.4233 7.84173 11.4233Z" fill="#3E4954"/>
										<path d="M15.4162 13.1066H7.84173C7.61852 13.1066 7.40446 13.1952 7.24662 13.3531C7.08879 13.5109 7.00012 13.725 7.00012 13.9482C7.00012 14.1714 7.08879 14.3855 7.24662 14.5433C7.40446 14.7011 7.61852 14.7898 7.84173 14.7898H15.4162C15.6394 14.7898 15.8535 14.7011 16.0113 14.5433C16.1692 14.3855 16.2578 14.1714 16.2578 13.9482C16.2578 13.725 16.1692 13.5109 16.0113 13.3531C15.8535 13.1952 15.6394 13.1066 15.4162 13.1066Z" fill="#3E4954"/>
									</svg>
									<span class="badge light text-white bg-primary">3</span>
                                </a>
							</li>
--->
                            <li class="nav-item dropdown header-profile">
                                <a class="nav-link" href="javascript:void(0)" role="button" data-toggle="dropdown">
									<div class="header-info">
										<span class="text-black"><strong>Sales Agent:</strong> <?php echo $adminUsername; ?> <br /><?php echo $adminTelephone; ?><br /> <?php echo $adminEmail; ?></span>
										<p class="fs-12 mb-0"><!--- Role ---></p>
									</div>
                                    <img src="images/profile/17.jpg" width="20" alt=""/>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="./app-profile.html" class="dropdown-item ai-icon">
                                        <svg id="icon-user1" xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <span class="ml-2">Profile </span>
                                    </a>
                                    <a href="./email-inbox.html" class="dropdown-item ai-icon">
                                        <svg id="icon-inbox" xmlns="http://www.w3.org/2000/svg" class="text-success" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                        <span class="ml-2">Inbox </span>
                                    </a>
                                    <a href="logout.php" class="dropdown-item ai-icon">
                                        <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
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
								<input type="password" name="dealer_password" class="form-control" placeholder="Password">
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
					  if( ($accountTypeCode=="A") || ($userType=="dealer" && $isContactPerson=="Y") || ($adminLoggedIn) ){
/*
echo "accountTypeCode=".$accountTypeCode;
echo "<br />";
echo "userType=".$userType;
echo "<br />";
echo "isContactPerson=".$isContactPerson;
echo "<br />";
if($adminLoggedIn){
echo "adminloggedin = true";
}else{
echo "adminloggedin = false";
}
*/
					?>
                    <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="<?php if($navSection=="signup"){echo "true";}else{echo "false";}?>">
							<i class="flaticon-381-networking"></i>
							<span class="nav-text">Dealer Signup</span>
						</a>
					<ul aria-expanded="false">
						<?php

						// If a Sales Agency is logged in, allow them to create new dealers.
						if($accountTypeCode=="A"){

						?>

						<li>
							<a href="dealer_agreement_v3.php">
								New Dealer Agreement
							</a>
						</li>

						<?php

						}else{

						?>

						<li>
							<a href="dealer_agreement_v3.php?acctID=<?php echo $acct_ID; ?>">
								<?php if ($dealerAgreementComplete == "Y") { ?>
									<img src="images/green_check.png" height="10" width="10" alt="Dealer Agreement Complete"/>
								<?php
								} ?>
								Dealer Agreement
							</a>
							<?php if ($dealerAgreementComplete == "Y") { ?>
								<span style="padding: 0px !important;">
								<?php if($dealerAgreementPDF!=""){
								?>
									<a href="/uploads/dealer_agreement_pdf/<?php echo $dealerAgreementPDF; ?>" target="_blank">PDF</a>
								<?php
								}
								?>
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
								<img src="images/green_check.png" height="10" width="10" alt="Dealer W9 Complete"/>
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
					<?php if ($dealerMultipleLocations == "Y") { ?> <li><a href="dealer_multiple_locations.php"><?php if ($dealerMultipleLocationsComplete == "Y") { ?><img src="images/green_check.png" height="10" width="10" alt="Dealer W9 Complete"/> <?php

																																																																																																																																																																																																																																										} ?>Dealer Locations</a></li><?php

																																																																																																																																																																																																																																																																					} ?>
					<!---<li><?php if ($dealerSetupComplete != "Y") { ?><a href="dealer_setup.php"><?php

																																																																																		} else { ?><img src="images/green_check.png" height="10" width="10" alt="Dealer Setup Complete"/> <?php

																																																																																																																																																																																		} ?>Dealer Setup<?php if ($dealerSetupComplete != "Y") { ?></a><?php

																																																																																																																																																																																																																																															} ?></li>--->
					<li>
						<a href="dealer_setup.php">
							<?php if ($dealerSetupComplete == "Y") { ?>
								<img src="images/green_check.png" height="10" width="10" alt="Dealer Setup Complete"/>
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
					<li><a href="dealer_addendum.php"><?php if ($dealerAddendumComplete == "Y") { ?><img src="images/green_check.png" height="10" width="10" alt="Dealer Addendum Complete"/> <?php

																																																																																																																																																																													} ?>Dealer Addendum</a><?php if ($dealerAddendumComplete == "Y" && !empty($dealerAddendumPDF)) { ?><span style="padding: 0px !important;"><!---<a href="../allPDF.php?fileTypeID=3">PDFs</a>---></span><?php

																																																																																																																																																																																																																																																																																																																																																																																															} ?></li>
					<!---<li><?php if ($dealerFeeFormComplete != "Y") { ?><a href="dealer_affiliate_fee.php"><?php

																																																																																												} else { ?><img src="images/green_check.png" height="10" width="10" alt="Dealer Affiliate Fee Complete"/> <?php

																																																																																																																																																																																																				} ?>Affiliate Fee Form<?php if ($dealerFeeFormComplete != "Y") { ?></a><?php

																																																																																																																																																																																																																																																																									} ?></li>--->
					<li style="margin-left:1.3rem"><?php if ($dealerFeeFormComplete == "Y") { ?><img src="images/green_check.png" height="10" width="10" alt="Dealer Affiliate Fee Complete"/> <?php

																																																																																																																																																			} ?>Affiliate Fee Forms: <?php if ($dealerFeeFormComplete == "Y" && !empty($dealerAffiliateFeePDF)) { ?><span style="padding: 0px !important;"><!---<a href="../allPDF.php?fileTypeID=4">PDFs</a>---></span><?php

																																																																																																																																																																																																																																																																																																																																																																														} ?></li>
					<li><a href="dealer_banking.php"><?php if ($dealerBankingComplete == "Y") { ?><img src="images/green_check.png" height="10" width="10" alt="Dealer Affiliate Fee Complete"/> <?php

																																																																																																																																																																																} ?>Dealer</a></li>
					<li><a href="dealer_affiliate_fee.php"><?php if ($dealerFeeFormComplete == "Y") { ?><img src="images/green_check.png" height="10" width="10" alt="Dealer Affiliate Fee Complete"/> <?php

																																																																																																																																																																																						} ?>Personnel</a></li>
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

                    <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="<?php if($navSection=="warranty"){echo "true";}else{echo "false";}?>">
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
				} ?></li>
							<li><a href="warranty_pending.php?showQuotes=Y">Pending Quotes</a></li>
							<li><a href="create_warranty.php">Create Warranty</a><?php if (!empty($warrantyPDF)) { ?>
							<!--
							<span style="padding: 0px !important;">
								<a href="../warranty_pdf.php">PDFs</a>
								<a href="../allPDF.php?fileTypeID=7">PDFs</a>
							</span>
							-->
						<?php
				} ?></li>
							<li><a href="warranty_pending.php">Pending Warranties</a></li>
						</ul>
                    </li>

					<?php

					// Only show this menu item a VT Admin is logged in, role_ID = 1.
					if ((isset($role_ID) && $role_ID==1)){

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

					}

					?>
                    <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="<?php if($navSection=="docs"){echo "true";}else{echo "false";}?>">
							<i class="flaticon-381-networking"></i>
							<span class="nav-text">Doc Center</span>
						</a>
                        <ul aria-expanded="false">
							<!---
							<li><a href="">Sample Forms</a></li>
							<li><a href="">Completed Dealer Forms</a></li>
							--->
							<li><a href="../generated_pdfs.php">Dealer Doc PDFs</a></li>
                    </li>
					<?php if($acct_ID ==""){
						// I am removing the requirement for a dealer to be logged in for
						//  navigation to the docs pages.  cparry@gmail.com 8/16/2022.
					?>
					<li><a href="../fillable_documents.php">Fillable Documents</a></li>
					<?php } else
					{
					?>
					<li><a href="../fillable_documents.php">
							<span class="nav-text">Fillable Documents</span>
						</a>
                    </li>
					<?php } ?>

					<?php if($acct_ID ==""){
					?>
					<li><a href="../samples_documents.php">Sample Contracts</a></li>
					<?php } else
					{
					?>
					<li><a href="../samples_documents.php">
							<span class="nav-text">Sample Contracts</span>
						</a>
                    </li>
					<?php } ?>

	                <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
							<span class="nav-text">Brochures</span>
						</a>
                        <ul aria-expanded="false">
						<li style="padding-left: 20px;background: #e8e8e8;"><a style="font-size: 14px" href="../uploads/brochures/Wrap_Program Vital_Trends_Trifold_Brochure.pdf" target="__blank">Wrap Program</a></li>
						<li style="padding-left: 20px;background: #e8e8e8;"><a style="font-size: 14px" href="../uploads/brochures/Emergency_Vehicle_Warranty_Submission.pdf" target="__blank">Inspection Requirements</a></li>
						<li style="padding-left: 20px;background: #e8e8e8;"><a style="font-size: 14px" href="../uploads/brochures/VT_Program_Highlights.pdf" target="__blank">Program Highlights</a></li>
						<li style="padding-left: 20px;background: #e8e8e8;"><a style="font-size: 14px" href="../uploads/brochures/VITAL_TRENDS_Booklet.pdf" target="__blank">Vital Trends Booklet</a></li>
						<li style="padding-left: 20px;background: #e8e8e8;"><a style="font-size: 14px" href="../uploads/brochures/Vital_Trends_Trifold_Brochure.pdf" target="__blank">Vital Trends Trifold</a></li>
						<li style="padding-left: 20px;background: #e8e8e8;"><a style="font-size: 14px" href="../uploads/brochures/VT_CC_Sheet_v0822.pdf" target="__blank">Vital Trends Covered Components</a></li>
						</ul>
                    </li>

				</ul>
            </li>
					<?php

					// Only show this menu item if a dealer is NOT logged in, but just a VT admin
					// (isset($_SESSION["userType"]) && $_SESSION["userType"]!="Agent")
				if ((isset($_SESSION["admin_username"]) && $_SESSION["admin_username"]!="service") &&
					    (isset($_SESSION["userType"]) && $_SESSION["userType"]!="dealer")
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
					if ((isset($_SESSION["admin_username"]) && $_SESSION["admin_username"]!="service") &&
					    (isset($_SESSION["userType"]) && $_SESSION["userType"]!="dealer")  &&
					    (isset($_SESSION["userType"]) && $_SESSION["userType"]!="Agent")) {

					?>

                    <li>
                    	<a  class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
							<i class="flaticon-381-networking"></i>
							<span class="nav-text">Reporting</span>
						</a>
						<ul aria-expanded="false">
							<li><a href="reporting-dashboard.php">Finance</a></li>
							<li><a href="#">Claims</a></li>
							<li><a href="#">Vital Trends News</a></li>
							<li><a href="#">Sales & Contracts</a></li>
							<li><a href="quote_report.php">Quote Report</a></li>
						</ul>
                    </li>
					<?php

					}


						// Only show this menu item if a dealer is NOT logged in, but just a VT admin
					if ((isset($_SESSION["admin_username"]) && $_SESSION["admin_username"]!="service") &&
							(isset($_SESSION["userType"]) && $_SESSION["userType"]!="dealer")  &&
					    (isset($_SESSION["userType"]) && $_SESSION["userType"]!="Agent")) {
					?>
					<li>
                    	<a href="dealerAgent.php"><i class="flaticon-381-networking"></i>
							<span class="nav-text">My Agents</span></a>
                    </li>
					<?php

					}

					?>


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

