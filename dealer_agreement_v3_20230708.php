<?php
// File: dealer_agreement.php (v4 testing)
// Author: Charles Parry
// Date: 5/14/2022
// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);

$pageBreadcrumb = "Contracts Home";
$pageTitle = "Contracts";

// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

// Email function
require_once "lib/emailHelper.php";

require_once 'vendor/autoload.php';

require_once 'php-svg-0.14.0/autoloader.php';



// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');
require_once('FPDI-master/src/autoload.php');
require_once('fpdf/fpdf.php');

// use setasign\Fpdi\Tcpdf\Fpdi;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;
use SVG\SVG;
use Classes\GeneratePDF;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/vendor/autoload.php';

// Variables.
$acctID = "";
$adminID = "";
$agreementDate = "";
$multipleLocations = "";
$multipleLocations_Long = "";
$individualBilling = "";
$dealerName = "";
$dba = "";
$federalTaxID = "";
$duns = "";
$dealerAddress1 = "";
$dealerAddress2 = "";
$dealerCity = "";
$dealerState = "";
$dealerZip = "";
$dealerPhone = "";
$dealerFax = "";
$dealerLicense = "";
$businessEmail = "";
$businessWebsite = "";
$primaryContact = "";
$primaryContactFirstName = "";
$primaryContactLastName = "";
$primaryContactPhone = "";
$primaryContactEmail = "";
$accountsPayableContact = "";
$accountsPayableContactFirstName = "";
$accountsPayableContactLastName = "";
$accountsPayableContactPhone = "";
$accountsPayableContactEmail = "";
$retailerName = "";
$retailerTitle = "";
$signedOnDate = "";
$shippingAddress1 = "";
$shippingCity = "";
$shippingState = "";
$shippingZip = "";
$notesField = "";
$signatureOption = "online";
$Dlr_Affiliate_Fee_Pct = "";
$form_err = "";

session_start();

// Get the AdminID so we can track sales agent for this dealer agreement.
$adminID = $_SESSION["admin_id"];


// Process form data when form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	// To support the notion of a 'dealer service account' we allow a dealer
	//  to log on with a limited admin account and create this initial agreement document.
	//  However, before processing the document, we want to basically log out that dealer and
	//  log in Josh as the sales agent default.
	$adminUsername = $_SESSION["admin_username"];

	if ($adminUsername == "service") {
		try {

			/* Execute the statement */
			$queryString = "SELECT * FROM Users u, Tel t, Email m WHERE u.username='josh' AND
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
				$_SESSION["admin_username"] = 'josh';
			}
		} catch (Exception $e) {
			echo $e->getMessage();
			die();
		}
	}


	// Get the acctID if we are updating
	if (isset($_POST["acctID"]) && $_POST["acctID"] != "") {
		$acctID = $_POST["acctID"];
	}

    // Check if dealer name is empty
	if (empty(trim($_POST["dealerName"]))) {
		$form_err = "Please enter dealer name.";
	} else {
		$dealerName = ucwords(trim($_POST["dealerName"]));
	}

    // Check if primary contact email is empty
	if (empty(trim($_POST["primaryContactEmail"]))) {
		$form_err = "Please enter primary contact email.";
	} else {
		$primaryContactEmail = trim($_POST["primaryContactEmail"]);
	}

	// Get remaining form fields
	if (!empty(trim($_POST["agreementDate"]))) {
		$agreementDate = trim($_POST["agreementDate"]);
	}

	if (!empty(trim($_POST["multipleLocations"]))) {
		$multipleLocations = trim($_POST["multipleLocations"]);
		if ($multipleLocations == "Y") {
			$multipleLocations_Long = "YES";
		} else {
			$multipleLocations_Long = "NO";
		}
	}

	if (!empty(trim($_POST["individualBilling"]))) {
		$individualBilling = trim($_POST["individualBilling"]);
	}

	if (!empty(trim($_POST["dba"]))) {
		$dba = ucwords(trim($_POST["dba"]));
	}


	if (!empty(trim($_POST["taxID"])) && preg_match("/^(\d{2})[-](\d{7})$/", $_POST["taxID"])) {
		$federalTaxID = trim($_POST["taxID"]);
	} else {
		$form_err = "Please enter correct federal tax ID.";
	}


	if (!empty(trim($_POST["duns"]))) {
		$duns = trim($_POST["duns"]);
	}


	if (!empty(trim($_POST["dealerAddress"]))) {
		$dealerAddress1 = ucwords(trim($_POST["dealerAddress"]));
	}


	if (!empty(trim($_POST["poBox"]))) {
		$dealerAddress2 = ucwords(trim($_POST["poBox"]));
	}


	if (!empty(trim($_POST["dealerCity"]))) {
		$dealerCity = ucwords(trim($_POST["dealerCity"]));
	}


	if (!empty(trim($_POST["dealerState"]))) {
		$dealerState = trim($_POST["dealerState"]);
	}


	if (!empty(trim($_POST["dealerZip"]))) {
		$dealerZip = trim($_POST["dealerZip"]);
	}


	if (!empty(trim($_POST["dealerPhone"]))) {
		$dealerPhone = trim($_POST["dealerPhone"]);
	}


	if (!empty(trim($_POST["dealerFax"]))) {
		$dealerFax = trim($_POST["dealerFax"]);
	}


	if (!empty(trim($_POST["dealerLicense"]))) {
		$dealerLicense = trim($_POST["dealerLicense"]);
	}


	if (!empty(trim($_POST["businessEmail"]))) {
		$businessEmail = trim($_POST["businessEmail"]);
	}


	if (!empty(trim($_POST["businessWebsite"]))) {
		$businessWebsite = trim($_POST["businessWebsite"]);
	}


	if (!empty(trim($_POST["primaryContactFirstName"]))) {
		$primaryContactFirstName = ucwords(trim($_POST["primaryContactFirstName"]));
	}


	if (!empty(trim($_POST["primaryContactLastName"]))) {
		$primaryContactLastName = ucwords(trim($_POST["primaryContactLastName"]));
	}


	if (!empty(trim($_POST["primaryContactPhone"]))) {
		$primaryContactPhone = trim($_POST["primaryContactPhone"]);
	}


	if (!empty(trim($_POST["primaryContactEmail"]))) {
		$primaryContactEmail = trim($_POST["primaryContactEmail"]);
	}


	if (!empty(trim($_POST["accountsPayableContactFirstName"]))) {
		$accountsPayableContactFirstName = trim($_POST["accountsPayableContactFirstName"]);
	}


	if (!empty(trim($_POST["accountsPayableContactLastName"]))) {
		$accountsPayableContactLastName = trim($_POST["accountsPayableContactLastName"]);
	}


	if (!empty(trim($_POST["accountsPayableContactPhone"]))) {
		$accountsPayableContactPhone = trim($_POST["accountsPayableContactPhone"]);
	}


	if (!empty(trim($_POST["accountsPayableContactEmail"]))) {
		$accountsPayableContactEmail = trim($_POST["accountsPayableContactEmail"]);
	}


	if (!empty(trim($_POST["retailerName"]))) {
		$retailerName = ucwords(trim($_POST["retailerName"]));
	}


	if (!empty(trim($_POST["retailerTitle"]))) {
		$retailerTitle = ucwords(trim($_POST["retailerTitle"]));
	}


	if (!empty(trim($_POST["signedOnDate"]))) {
		$signedOnDate = trim($_POST["signedOnDate"]);
	}


	if (!empty(trim($_POST["shipAddress"]))) {
		$shippingAddress1 = ucwords(trim($_POST["shipAddress"]));
	}


	if (!empty(trim($_POST["shipCity"]))) {
		$shippingCity = ucwords(trim($_POST["shipCity"]));
	}


	if (isset($_POST["shipState"]) && !empty(trim($_POST["shipState"]))) {
		$shippingState = trim($_POST["shipState"]);
	} else {
		$shippingState = "";
	}


	if (!empty(trim($_POST["shipZip"]))) {
		$shippingZip = trim($_POST["shipZip"]);
	}


	if (!empty(trim($_POST["notesField"]))) {
		$notesField = trim($_POST["notesField"]);
	}


	if (!empty(trim($_POST["Dlr_Affiliate_Fee_Pct"]))) {
		$Dlr_Affiliate_Fee_Pct = trim($_POST["Dlr_Affiliate_Fee_Pct"]);
	}



/*
	echo "<br /><br />";
	echo "<br /><br />";
echo "dealerphone=".$dealerPhone;
	echo "<br /><br />";
	echo "<br /><br />";
	 */
//die();
/*
	// Simulate connectivity
	echo "got dealer name = ".$dealerName;
	echo "<br /><br />";
	//sleep(30);
	echo "pushing details to truNorth";
	echo "<br /><br />";
	//sleep(30);
	echo "transferring data to CRM database";
	echo "<br /><br />";
	 */

	// Prepare a select statement
/*
	$sql = "SELECT userID, username, password FROM Users WHERE username = 'cparry'";
	$result = $link->query($sql);
	while ($row = $result->fetch_row()) {
	    print_r($row);
	}
	 */

	if ($acctID != "") {
		// Update
		/* Prepare an update statement to created an Acct entry */
		$stmt = mysqli_prepare($link, "UPDATE Acct SET Acct_Nm=?,Fed_Tax_Number=?,Duns_Number=? WHERE acct_id=?");

		/* Bind variables to parameters */
		$val1 = $dealerName;
		$val2 = $federalTaxID;
		$val3 = $duns;
		$val4 = $acctID;

		mysqli_stmt_bind_param($stmt, "sssi", $val1, $val2, $val3, $val4);

		/* Execute the statement */
		//$result = mysqli_stmt_execute($stmt);

		echo "updated";
		die();


	} else {
		/* Prepare an insert statement to created an Acct entry */
		// Saving Federal Tax ID to both Fed_Tax and EIN_Nbr for future internationalization options.
		$stmt = mysqli_prepare($link, "INSERT INTO Acct (Acct_Nm,Fed_Tax_Number,EIN_Nbr,Duns_Number,Multiple_Locations,Individual_Billing,Sls_Agnt_ID,Dlr_Affiliate_Fee_Pct,Acct_Type_Cd,Acct_Type_Desc) VALUES (?,?,?,?,?,?,?,?,?,?)");

		/* Bind variables to parameters */
		$val1 = $dealerName;
		$val2 = $federalTaxID;
		$val3 = $federalTaxID;
		$val4 = $duns;
		$val5 = $multipleLocations;
		$val6 = $individualBilling;
		$val7 = $adminID;
		$val8 = $Dlr_Affiliate_Fee_Pct;
		$val9 = "D";
		$val10 = "Dealer";

		mysqli_stmt_bind_param($stmt, "ssssssiiss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

	}


	if ($result) {
		$last_id = mysqli_insert_id($link);
		//echo "<br /><br />New record created successfully. Last inserted ID is: " . $last_id;

		// Now that we have the newly created dealerID, we need to do several things:
		//   - add the ID to a session var
		//   - insert dealer email, phone and address to respective tables using this ID
		//   - update the dealer_agreement_tracking table with forms so far completed (only Dealer Agreement so far)
		//   - redirect back to next form in the series.
		//


		/* Prepare an insert statement to create an Addr entry for the main address */
		$stmt = mysqli_prepare($link, "INSERT INTO Addr (Acct_ID,St_Addr_1_Desc,St_Addr_2_Desc,City_Nm,St_Prov_ID,Pstl_Cd,Addr_Type_Cd,Addr_Type_Desc,Prim_Addr_Flg) VALUES (?,?,?,?,?,?,'Work','Work','Y')");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $dealerAddress1;
		$val3 = $dealerAddress2;
		$val4 = $dealerCity;
		$val5 = $dealerState;
		$val6 = $dealerZip;

		mysqli_stmt_bind_param($stmt, "isssis", $val1, $val2, $val3, $val4, $val5, $val6);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);




		// Enter shipping address if it is not blank
		if ($shippingAddress1 != "") {
			/* Prepare an insert statement to create an Addr entry for the shipping address */
			$stmt = mysqli_prepare($link, "INSERT INTO Addr (Acct_ID,St_Addr_1_Desc,City_Nm,St_Prov_ID,Pstl_Cd,Addr_Type_Cd,Addr_Type_Desc) VALUES (?,?,?,?,?,'Ship','Shipping')");

			/* Bind variables to parameters */
			$val1 = $last_id;
			$val2 = $shippingAddress1;
			$val3 = $shippingCity;
			$val4 = $shippingState;
			$val5 = $shippingZip;

			mysqli_stmt_bind_param($stmt, "issis", $val1, $val2, $val3, $val4, $val5);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);
		}


		// Enter note if it is not blank
		if ($notesField != "") {
			/* Prepare an insert statement to create an Note entry for the note field */
			$stmt = mysqli_prepare($link, "INSERT INTO Note (Acct_ID,Note_Desc,Note_Type) VALUES (?,?,'agreement')");

			/* Bind variables to parameters */
			$val1 = $last_id;
			$val2 = $notesField;

			mysqli_stmt_bind_param($stmt, "is", $val1, $val2);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);
		}


		/* Prepare an insert statement to create a Tel entry for the main phone */
		$stmt = mysqli_prepare($link, "INSERT INTO Tel (Acct_ID,Tel_Nbr,Tel_Type_Cd,Tel_Type_Desc,Prim_Tel_Flg) VALUES (?,?,'Work','Work','Y')");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $dealerPhone;

		mysqli_stmt_bind_param($stmt, "is", $val1, $val2);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);



		// Enter fax number if it is not blank
		if ($dealerFax == "") {
			/* Prepare an insert statement to create a Tel entry for the fax */
			$stmt = mysqli_prepare($link, "INSERT INTO Tel (Acct_ID,Tel_Nbr,Tel_Type_Cd,Tel_Type_Desc,Prim_Tel_Flg) VALUES (?,?,'Fax','Fax','N')");

			/* Bind variables to parameters */
			$val1 = $last_id;
			$val2 = $dealerFax;

			mysqli_stmt_bind_param($stmt, "is", $val1, $val2);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);
		}



		/* Prepare an insert statement to create an Email entry for the main email */
		$stmt = mysqli_prepare($link, "INSERT INTO Email (Acct_ID,Email_URL_Desc,Email_Type_Cd,Email_Type_Desc,Email_Prim_Flg) VALUES (?,?,'Work','Work','Y')");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $businessEmail;

		mysqli_stmt_bind_param($stmt, "is", $val1, $val2);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);






		/* Prepare an insert statement to create an Email entry for the main website URL */
		$stmt = mysqli_prepare($link, "INSERT INTO Email (Acct_ID,Email_URL_Desc,Email_Type_Cd,Email_Type_Desc,Email_Prim_Flg) VALUES (?,?,'Website','Website','N')");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $businessWebsite;

		mysqli_stmt_bind_param($stmt, "is", $val1, $val2);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


		// There are three cases we have to handle, regarding the 'primary' and the 'accounts payable' contact people
		// 1) if the AP data shows the same email as primary, then set a flag in the primary that they are also AP
		// 2) if the AP data is blank, then set a flag in the primary that they are also AP
		// 3) if the AP data is different than the primary, then create new.
		if (($accountsPayableContactEmail == $primaryContactEmail) || ($accountsPayableContactEmail == "")) {
			$usePrimaryAsAP = "Y";
		} else {
			$usePrimaryAsAP = "N";
		}



		/* Prepare an insert statement to create an Pers entry for the primary contact person */
		$stmt = mysqli_prepare($link, "INSERT INTO Pers (Acct_ID,Pers_Full_Nm,Pers_Last_Nm,Pers_Frst_Nm,Pers_Username,Pswd_Hash_Cd,Cntct_Prsn_For_Acct_Flg,AP_Prsn_Flg,Prsn_Type_Cd,Prsn_Type_Desc) VALUES (?,?,?,?,?,?,'Y','" . $usePrimaryAsAP . "','DSA','Dealer Sales Agent')");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $primaryContactFirstName . " " . $primaryContactLastName;
		$val3 = $primaryContactLastName;
		$val4 = $primaryContactFirstName;
		$val5 = $primaryContactEmail;  // username
		$val6 = password_hash("PASSWORD", PASSWORD_DEFAULT);  // password

		mysqli_stmt_bind_param($stmt, "isssss", $val1, $val2, $val3, $val4, $val5, $val6);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the per Pers_ID of the primary contact person.
		$primary_Contact_Person_id = mysqli_insert_id($link);



		/* Create an entry in Users for this new Dealer. */
		$initialPassword = password_hash("PASSWORD", PASSWORD_DEFAULT);

		$stmt = mysqli_prepare($link, "INSERT INTO Users (Acct_ID,Pers_ID,Role_ID,username,password,mustResetPassword,createdDate) VALUES (?,?,2,?,?,'Y',NOW())");

		/* Bind variables to parameters */
		$val1 = $dealerName;
		mysqli_stmt_bind_param($stmt, "iiss", $last_id, $primary_Contact_Person_id, $primaryContactEmail, $initialPassword);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the per User_ID of the primary contact person.
		$primary_Contact_User_id = mysqli_insert_id($link);


		/* Prepare an insert statement to create a Tel entry for the primary contact person phone */
		$stmt = mysqli_prepare($link, "INSERT INTO Tel (Acct_ID,Pers_ID,Tel_Nbr,Tel_Type_Cd,Tel_Type_Desc,Prim_Tel_Flg) VALUES (?,?,?,'Work','Work','N')");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $primary_Contact_Person_id;
		$val3 = $primaryContactPhone;

		mysqli_stmt_bind_param($stmt, "iis", $val1, $val2, $val3);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);



		/* Prepare an insert statement to create an Email entry for the primary contact person email */
		$stmt = mysqli_prepare($link, "INSERT INTO Email (Acct_ID,Pers_ID,Email_URL_Desc,Email_Type_Cd,Email_Type_Desc,Email_Prim_Flg) VALUES (?,?,?,'Work','Work','Y')");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $primary_Contact_Person_id;
		$val3 = $primaryContactEmail;

		mysqli_stmt_bind_param($stmt, "iis", $val1, $val2, $val3);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);



		// Create a new Dlr_Loc_Dim entry
		$stmt = mysqli_prepare($link, "INSERT INTO Dlr_Loc_Dim (Dlr_Acct_ID,Dlr_Acct_Nbr,Dlr_Loc_Nbr,Dlr_Loc_Nm) VALUES (?,0,0,?)");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $dealerName;

		mysqli_stmt_bind_param($stmt, "is", $val1, $val2);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the per Dlr_Loc_Dim_ID of the primary contact person.
		$Dlr_Loc_Dim_ID = mysqli_insert_id($link);



		// Create a new Usr_Loc entry
		$stmt = mysqli_prepare($link, "INSERT INTO Usr_Loc (Dlr_Acct_ID,Dlr_Loc_Dim_ID,Usr_ID,Pers_ID) VALUES (?,?,?,?)");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $Dlr_Loc_Dim_ID;
		$val3 = $primary_Contact_User_id;
		$val4 = $primary_Contact_Person_id;

		mysqli_stmt_bind_param($stmt, "iiii", $val1, $val2, $val3, $val4);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


		// See if we should be adding an accounts payable person
		if ($usePrimaryAsAP == "N" && $accountsPayableContactFirstName != "" && $accountsPayableContactLastName != "") {
			/* Prepare an insert statement to create an Pers entry for the Accounts Payable contact person */
			$stmt = mysqli_prepare($link, "INSERT INTO Pers (Acct_ID,Pers_Full_Nm,Pers_Last_Nm,Pers_Frst_Nm,Pers_Ttl_Nm,AP_Prsn_Flg,Prsn_Type_Cd,Prsn_Type_Desc) VALUES (?,?,?,?,'Accounts Payable Contact','Y','DSA','Dealer Sales Agent')");

			/* Bind variables to parameters */
			$val1 = $last_id;
			$val2 = $accountsPayableContactFirstName . " " . $accountsPayableContactLastName;
			$val3 = $accountsPayableContactLastName;
			$val4 = $accountsPayableContactFirstName;

			mysqli_stmt_bind_param($stmt, "isss", $val1, $val2, $val3, $val4);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);

			// Get the per Pers_ID of the primary contact person.
			$accounts_payable_Person_id = mysqli_insert_id($link);


			/* Prepare an insert statement to create a Tel entry for the accounts payable person phone */
			$stmt = mysqli_prepare($link, "INSERT INTO Tel (Acct_ID,Pers_ID,Tel_Nbr,Tel_Type_Cd,Tel_Type_Desc,Prim_Tel_Flg) VALUES (?,?,?,'Work','Work','Y')");

			/* Bind variables to parameters */
			$val1 = $last_id;
			$val2 = $accounts_payable_Person_id;
			$val3 = $accountsPayableContactPhone;

			mysqli_stmt_bind_param($stmt, "iis", $val1, $val2, $val3);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);


			/* Prepare an insert statement to create an Email entry for the accounts payable person email */
			$stmt = mysqli_prepare($link, "INSERT INTO Email (Acct_ID,Pers_ID,Email_URL_Desc,Email_Type_Cd,Email_Type_Desc,Email_Prim_Flg) VALUES (?,?,?,'Work','Work','Y')");

			/* Bind variables to parameters */
			$val1 = $last_id;
			$val2 = $accounts_payable_Person_id;
			$val3 = $accountsPayableContactEmail;

			mysqli_stmt_bind_param($stmt, "iis", $val1, $val2, $val3);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);


			/* Create an entry in Users for this new Dealer. */
			$initialPassword = password_hash("PASSWORD", PASSWORD_DEFAULT);

			$stmt = mysqli_prepare($link, "INSERT INTO Users (Acct_ID,Pers_ID,Role_ID,username,password,mustResetPassword,createdDate) VALUES (?,?,2,?,?,'Y',NOW())");

			/* Bind variables to parameters */
			$val1 = $dealerName;
			mysqli_stmt_bind_param($stmt, "iiss", $last_id, $accounts_payable_Person_id, $accountsPayableContactEmail, $initialPassword);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);

			// Get the per User_ID of the primary contact person.
			$ap_Contact_User_id = mysqli_insert_id($link);


			// Create a new Usr_Loc entry
			$stmt = mysqli_prepare($link, "INSERT INTO Usr_Loc (Dlr_Acct_ID,Dlr_Loc_Dim_ID,Usr_ID,Pers_ID) VALUES (?,?,?,?)");

			/* Bind variables to parameters */
			$val1 = $last_id;
			$val2 = $Dlr_Loc_Dim_ID;
			$val3 = $ap_Contact_User_id;
			$val4 = $accounts_payable_Person_id;

			mysqli_stmt_bind_param($stmt, "iiii", $val1, $val2, $val3, $val4);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);


		}




		/* Prepare an insert statement to create an Altn_Nm entry for the DBA info */
		$stmt = mysqli_prepare($link, "INSERT INTO Altn_Nm (Acct_ID,Altn_Nm,Altn_Nm_Type_Cd,Altn_Nm_Type_Desc) VALUES (?,?,'DBA','Doing Business As')");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $dba;

		mysqli_stmt_bind_param($stmt, "is", $val1, $val2);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// handle signature
		$fileName = "";
		$extension = "jpg+xml";
			try {
				$fields = (object)$_POST;
				$image = base64_decode($fields->signature);
				$imageData = $_POST['signature'];
				$imageDataBase30 = $_POST['base30'];

				list($type, $imageData) = explode(';', $imageData);
				list(, $extension) = explode('/', $type);
				// die($extension);
				list(, $imageData) = explode(',', $imageData);
				// $fileName = uniqid().'.'.$extension;
				$data = explode('+', $extension);
				// die($data[0]);
				$fileName = uniqid() . '.' . $data[0];

				$imageData = base64_decode($imageData);
				$image = 'uploads/' . $fileName;
				file_put_contents($image, $imageData);
				$my_date = date("Y-m-d H:i:s");


				/*
				$sql = "INSERT INTO dealers_agreement VALUES (null,'$fields->agreementDate','$fields->dealerName','$fields->dba','$fields->taxID','$fields->duns','$fields->dealerAddress','$fields->poBox','$fields->dealerCity','$fields->dealerState','$fields->zipCode','$fields->dealerPhone','$fields->dealerFax','$fields->dealerLicense','$fields->businessEmail','$fields->businessWebsite','$fields->primaryContact','$fields->primaryContactPhone','$fields->primaryContactEmail','$fields->accountsPayableContact','$fields->accountsPayableContactPhone','$fields->accountsPayableContactEmail','$fields->shipAddress','$fields->shipCity','$fields->shipState','$fields->shipZip','$fields->retailerName','$fields->retailerTitle','$fields->signedOnDate','$image','$my_date','$my_date' )";
				if (mysqli_query($link, $sql)) {
					echo json_encode(['status' => 200, 'message' => 'Record inserted successfully']);
				}else{
					echo json_encode(['status'=>200,'message'=>'Something went wrong please try again later']);
				}
				*/
			} catch (Exception $exception) {
				//echo json_encode(['status'=>400,'message'=>$exception->getMessage()]);
			}

				/* Prepare an insert statement to create a Cntrct_Dim entry for this Acct_ID */
			$stmt = mysqli_prepare($link, "INSERT INTO Cntrct_Dim (Cntrct_Signer_Nm,Cntrct_Signer_Ttl,Cntrct_Signature,Cntrct_Signature_Base30,Contract_Date,Created_Date) VALUES (?,?,?,?,?,NOW())");

			/* Bind variables to parameters */
			$val1 = $retailerName;
			$val2 = $retailerTitle;
			$val3 = $fileName;
			$val4 = $imageDataBase30;
			$val5 = date('Y-m-d', strtotime($agreementDate));

			mysqli_stmt_bind_param($stmt, "sssss", $val1, $val2, $val3, $val4, $val5);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);

			// Get the Contract_Dim_ID of the new contract dim entry.
			$contract_dim_ID = mysqli_insert_id($link);


			/* Prepare an insert statement to create a Cntrct entry for this Acct_ID */
			$stmt = mysqli_prepare($link, "INSERT INTO Cntrct (Cntrct_Dim_ID,Mfr_Acct_ID,Created_Date) VALUES (?,?,NOW())");

			/* Bind variables to parameters */
			$val1 = $contract_dim_ID;
			$val2 = $last_id;

			mysqli_stmt_bind_param($stmt, "ii", $val1, $val2);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);


			// Send mail to this new user
			$to = $primaryContactEmail;
			$subject = "Welcome - New Vital Trends Account";
			$txt = "You have been signed up for a Vital Trends user account!  Please click here to <a href='https://portal.vitaltrendsusa.com'>log in</a>.\n";
			$txt .= "Your user name is: " . $primaryContactEmail . "\n";
			$txt .= "Your initial password is: PASSWORD \n";
			$txt .= "Please note, you will need to change your password upon first login.\n\n";
			$txt .= "Thank you!\nVital Trends team";
			$headers = "From: admin@vitaltrendsusa.com" . "\r\n" .
				"BCC: cparry@gmail.com";

			//mail($to,$subject,$txt,$headers);

			$emailResult = sendEmail($to, $primaryContactFirstName, $primaryContactLastName, $subject, $txt);


			// If the Accounts Payable contact is different than the Primary Contact, send them email also
			if ($usePrimaryAsAP == "N" && $accountsPayableContactEmail != "") {
				$to = $accountsPayableContactEmail;
				$subject = "Welcome - New Vital Trends Account";
				$txt = "You have been signed up for a Vital Trends user account!  Please click here to <a href='https://portal.vitaltrendsusa.com'>log in</a>.\n";
				$txt .= "Your user name is: " . $accountsPayableContactEmail . "\n";
				$txt .= "Your initial password is: PASSWORD \n";
				$txt .= "Please note, you will need to change your password upon first login.\n\n";
				$txt .= "Thank you!\nVital Trends team";
				$headers = "From: admin@vitaltrendsusa.com" . "\r\n" .
					"BCC: cparry@gmail.com";

				//mail($to,$subject,$txt,$headers);

				$emailResult = sendEmail($to, $accountsPayableContactFirstName, $accountsPayableContactLastName, $subject, $txt);

			}
		/* Prepare an insert statement to create a Dealer_Progress entry for this Acct_ID */
		$stmt = mysqli_prepare($link, "INSERT INTO Dealer_Progress (Acct_ID,Dealer_Agreement_Complete,Created_Date) VALUES (?,'Y',NOW())");

		/* Bind variables to parameters */
		$val1 = $last_id;

		mysqli_stmt_bind_param($stmt, "i", $val1);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


		// Now, create a local session to authenticate this newly created Dealer user.
		// Store data in session variables
		$_SESSION["loggedin"] = true;
		$_SESSION["id"] = $last_id;
		$_SESSION["username"] = $primaryContactEmail;
		$_SESSION["userType"] = "dealer";
		$_SESSION["dealer_multiple_locations"] = $multipleLocations;

//echo "session username is now = ".$_SESSION["username"];


	} else {
//	  echo "<br /><br />Error: " . $sql . "<br>" . mysqli_error($link);
	}



	//Code for the Fillable PDF form is over here.
   $dealerSignatureImage = __DIR__.'/uploads/'. $fileName;
   $saveTo = __DIR__.'/uploads/Signatures';
//    $dealerSignatureImage = 'uploads/'. $fileName;
//    $pic = __DIR__.'/uploads/home/dh_pp7hie/portaldev.vitaltrendsusa.com/uploads/6347cfed66ab1.jpg';


   $dealerStatePDF = selectState($link, $dealerState);


   $Data = ['Date' => $agreementDate,'RETAILER BUSINESS NAME' => $dealerName ,'DOING BUSINESS AS' => $dba,'FEDERAL TAX ID'=> $federalTaxID,'DUNS#' => $duns,
   'ADDRESS'=> $dealerAddress1 ,'PO BOX/SUITE' => $dealerAddress2 , 'CITY' => $dealerCity  , 'STATE/PROVINCE' => $dealerStatePDF  , 'ZIP/POSTAL CODE' => $dealerZip,
   'PHONE#' => $dealerPhone, 'FAX#' => $dealerFax, 'BUSINESS EMAIL' => $businessEmail , 'BUSINESS WEBSITE' => $businessWebsite ,
   'PRIMARY CONTACT NAME _1' =>  $primaryContactFirstName . ' ' . $primaryContactLastName,'PRIMARY CONTACT TITLE_1' => $retailerTitle,
   'PRIMARY CONTACT EMAIL_1' => $primaryContactEmail  , 'PRIMARY CONTACT PH#_1' => $primaryContactPhone , 'ACCOUNTS PAYABLE CONTACT_1' => $accountsPayableContactFirstName . ' ' . $accountsPayableContactLastName ,
   'ACCOUNTS PAYABLE CONTACT EMAIL_1'=> $accountsPayableContactEmail ,'AP CONTACT PHONE_1'=> $accountsPayableContactPhone , 'MULTIPLE LOCATIONS?' => $multipleLocations_Long  , 'RETAILER SIGNATURE' => $fileName , 'RETAILER NAME'=> $retailerName  , 'RETAILER TITLE' => $retailerTitle ,
   'RETAILER SIGNED DATE' => $agreementDate ,'ASSIGNED RETAILER#' => ""  , 'TN DATE' => $agreementDate  , 'ASSIGNED PROGRAMS' => ""  , 'TN SIGNATURE' => ""  , 'TN SIGNATURE DATE' =>  $agreementDate
   ];


   	// $pdfFileName = "portaldev.vitaltrendsusa.com/uploads/fillable_documents/VT TNG GARA v0622 F Fillable.pdf";

	$pdfFileName = str_replace(" ", "_", $primaryContactFirstName) . '_' . str_replace(" ", "_", $primaryContactLastName) . '_' . time() . '.pdf';

	$pdf = new GeneratePDF;

	$response = $pdf->generateSgDetailDealer($Data , $pdfFileName);

	// $pdf = new \PDFMerger\PDFMerger;
	// $pdfFileName = str_replace(" ", "_", $customerName) . '_' . str_replace(" ", "_", $warrantyID) . '_' . time() . '.pdf';

	$command = "/home/dh_pp7hie/pdftk";
	$outputdir = "/home/dh_pp7hie/portaldev.vitaltrendsusa.com/uploads/dealer_agreement_pdf/".$pdfFileName;
	exec($command." cat output ".$outputdir);

	$Dir = "uploads/dealer_agreement_pdf/".$pdfFileName;

//  $saveto = "uploads/dealer_agreement_pdf/".$pdfFileName;
//Code to add the Image to the Project.

//
//die($dealerSignatureImage);
//$svg = file_get_contents($dealerSignatureImage);
// $imagick = new Imagick();
// $imagick->readImageBlob($svg);
// $imagick->setImageFormat('jpeg');
// header('Content-Type: image/jpeg');

$image = SVG::fromFile($dealerSignatureImage);
$rasterImage = $image->toRasterImage(200,200,'#FFFFFF');
imagejpeg($rasterImage,$saveTo.'/output.jpg');

$pic = $saveTo.'/output.jpg';

//


//
$pdf = new Fpdi();
// $pdf->AddPage();
$pageCount = $pdf->setSourceFile($Dir);
$page = $pdf->importPage(1);
$pdf->addPage();
$pdf->useTemplate($page,10,10,200);
$page = $pdf->importPage(2);
$pdf->addPage();
$pdf->useTemplate($page,10,10,200);
$page = $pdf->importPage(3);
$pdf->addPage();
$pdf->useTemplate($page,10,10,200);
// $pdf->Image('uploads/sky.jpg',60,110,12,12);
$pdf->Image($pic,60,110,12,12);
// $pdf->Image($pic,0,0);
$pdf->Output('F',$Dir);
// $pdf->Output();





//

		    $stmt = mysqli_prepare($link, "UPDATE Acct SET Dlr_Agrmnt_PDF=? WHERE acct_id=?");

			/* Bind variables to parameters */
			$val1 = $pdfFileName;
			$val2 = $last_id;

			mysqli_stmt_bind_param($stmt, "si", $val1, $val2);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);


			// Add this file to our File_Assets tracking table
			//  Set type=1 for 'dealer agreement'.
			$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
										Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,1,'Dealer Agreement',NOW())");

			/* Bind variables to parameters */
			$val1 = $last_id;
			$val2 = $primary_Contact_Person_id;
			$val3 = $adminID;
			$val4 = $contract_dim_ID;
			$val5 = '/uploads/dealer_agreement_pdf/' . $pdfFileName;

			mysqli_stmt_bind_param($stmt, "iiiis", $val1, $val2, $val3, $val4, $val5);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);


			//$pdf->Output('D:/tester.pdf', 'F');
			//============================================================+
			// END OF FILE
			//============================================================+
			// End PDF Code here


			// Get the dealer state value
			$addrResult = selectAddrByAcct($link, $last_id, "Work");

			if ($addrResult) {
				$row = $addrResult->fetch_assoc();
				$dealerState = $row["St_Prov_ISO_2_Cd"];
			} else {
				$dealerState = "";
			}

			// API Call to TruNorth
			//include('backend/dealer_agreement_api_script.php');
			// End API section


		    //Send Email to Dealer

			$mail = new PHPMailer(true);

			//$mail->SMTPDebug = 2;
			$mail->isSMTP();
			$mail->Host       = 'smtp.dreamhost.com';
			$mail->SMTPAuth   = true;
			$mail->Username   = 'info@vitaltrendsusa.com';
			$mail->Password   = 'z]p3RjC$HnPTd3+TLKnM';
			$mail->SMTPSecure = 'tls';
			$mail->Port       = 587;

			$mail->setFrom('info@vitaltrendsusa.com', 'info');
			$mail->addAddress($primaryContactEmail);
			// $mail->addAddress('receiver2@gfg.com', 'Name');
			$arrayHost = explode('.', $_SERVER['HTTP_HOST']);
			$serverSubdomain = array_shift($arrayHost);
		    $body = 'Dear <b>'.$primaryContactFirstName.' '.$primaryContactLastName.'</b><br><br><p>Vital Trends requires the names of those individuals you have authorized to write warranties. Please follow the link enclosed and send a list of individuals who you have authorized to write warranties. This should consist of Name, Location, Email, and Phone Number.  When done, submit the form and Vital Trends will follow up with the individuals you have entered here and obtain their signatures for verification purposes when a warranty is written.</p><br><a href="https://'.$serverSubdomain.'.vitaltrendsusa.com/dealer_setup.php?FromEmail=true">Click Here</a> to add individuals.<br><br><p>Thank you,</p><br><p><b>Vital Trends</b></p>';
			$mail->isHTML(true);
			$mail->Subject = 'Vital Trends Requests Dealer Agents For Warranties';
			$mail->Body    = $body;
			$mail->AltBody = '';

			// No longer sending this Dealer Agreement Requests Agents email
			// Keeping code just in case.  cparry@gmail.com 1/30/2023.
			//$mail->send();

		    // Redirect to next form
	        header("location: dealer_w9.php");
	        exit;

	        die();


}


if (isset($_GET["acctID"]) && $_GET["acctID"] != "") {

	$acctID = $_GET["acctID"];


	// SECURITY check permission of authenticated user to this acctID record.


	// Select data for this acctID.
	$acctResult = selectAcct($link, $acctID);

	if ($acctResult) {
		$row = $acctResult->fetch_assoc();
		$dealerName = $row["Acct_Nm"];
		$federalTaxID = $row["Fed_Tax_Number"];
		$dunsNumber = $row["Duns_Number"];
		$multipleLocations = $row["Multiple_Locations"];
		$individualBilling = $row["Individual_Billing"];

	} else {
		// Failed to find a result for the provided Acct_ID so drop out to main page.
		header("location: index.php");
		exit;
	}


	// Select Addr for the dealer.
	$addrResult = selectAddrByAcct($link, $acctID, "Work");

	if ($addrResult) {
		$row = $addrResult->fetch_assoc();
		$dealerAddress1 = $row["St_Addr_1_Desc"];
		$dealerAddress2 = $row["St_Addr_2_Desc"];
		$dealerCity = $row["City_Nm"];
		$dealerState = $row["St_Prov_ISO_2_Cd"];
		$dealerPostalCode = $row["Pstl_Cd"];
	} else {
		$dealerAddress1 = "";
		$dealerAddress2 = "";
		$dealerCity = "";
		$dealerState = "";
		$dealerPostalCode = "";
	}


	// Select Tel for the dealer.
	$telResult = selectTelByAcct($link, $acctID, "Y", "Work");
	if ($telResult) {
		$row = $telResult->fetch_assoc();
		$dealerPhone = $row["Tel_Nbr"];
	} else {
		$dealerPhone = "";
	}


	// Select Fax for the dealer.
	$telResult = selectTelByAcct($link, $acctID, "N", "Fax");

	if ($telResult) {
		$row = $telResult->fetch_assoc();
		$dealerFax = $row["Tel_Nbr"];
	} else {
		$dealerFax = "";
	}


	// Select Email for the dealer.
	$emailResult = selectEmailByAcct($link, $acctID, "Y", "Work");
	if ($emailResult) {
		$row = $emailResult->fetch_assoc();
		$dealerEmail = $row["Email_URL_Desc"];
	} else {
		$dealerEmail = "";
	}


	// Select website for the dealer.
	$websiteResult = selectEmailByAcct($link, $acctID, "N", "Website");
	if ($websiteResult) {
		$row = $websiteResult->fetch_assoc();
		$dealerWebsite = $row["Email_URL_Desc"];
	} else {
		$dealerWebsite = "";
	}



	// Get contract and signature info
	$query = "SELECT * FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $acctID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$signatureFilename = $row["Cntrct_Signature"];
	$signatureSigner = $row["Cntrct_Signer_Nm"];
	$signatureTitle = $row["Cntrct_Signer_Ttl"];

}

	// Get list of states from the Enumeration table
	//$query = "SELECT * FROM St_Prov WHERE Cntry_Nm = 'US' ORDER BY St_Prov_Nm";
	//$stateResult = $link->query($query);
	//$stateShipResult = $link->query($query);

	$stateResult = selectStates($link);
	$stateShipResult = selectStates($link);


	// Preset some fields.
	$agreementDate = date("m/d/Y");
	//$federalTaxID = "99-9999999";

	require_once("includes/header.php");

?>

		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
			<div class="container-fluid">
                <?php require_once("includes/common_page_content.php"); ?>
<?php
if (false) {
	?>
                <div class="row mobile-view">
                	<div class="col-md-12">
						<?php
							// Show the dealer login form, if there is not a dealer currently authenticated
					if ($userType != "dealer") {
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
                	</div>
                </div>
<?php

}
?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header text-center">
                                <h4 class="card-title">Dealer Agreement</h4>
								(Fields with * are required)
                            </div>
                            <div class="card-body">
                                <div class="basic-form dealer-form">
                                    <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>
									<?php
								if ($acctID != "") {
									?>
											<div class="form-group col-md-6">
												<label>Agreement Date: </label>
												<?php echo $dealerName; ?>
											</div>
											<div class="form-group col-md-6">
												<label>Dealer Name: </label>
												<?php echo $dealerName; ?>
											</div>
											<div class="form-group col-md-6">
												<label>DUNS Number: </label>
												<?php echo $dunsNumber; ?>
											</div>
											<div class="form-group col-md-6">
												<label>Federal Tax ID: </label>
												<?php echo $federalTaxID; ?>
											</div>
											<div class="form-group col-md-6">
												<label>Dealer has multiple locations?: </label>
												<?php echo $multipleLocations; ?>
											</div>
											<div class="form-group col-md-6">
												<label>Locations are billed Individually?: </label>
												<?php echo $individualBilling; ?>
											</div>
											<div class="form-group col-md-6">
												<label>Dealer Address: </label><br />
												<?php echo $dealerAddress1; ?><br />
												<?php echo $dealerAddress2; ?><br />
												<?php echo $dealerCity; ?>,
												<?php echo $dealerState; ?>.
												<?php echo $dealerPostalCode; ?>
											</div>
											<div class="form-group col-md-6">
												<label>Dealer Phone: </label>
												<?php echo $dealerPhone; ?>
											</div>
											<div class="form-group col-md-6">
												<label>Dealer Fax: </label>
												<?php echo $dealerFax; ?>
											</div>
											<div class="form-group col-md-6">
												<label>Dealer Email: </label>
												<?php echo $dealerEmail; ?>
											</div>
											<div class="form-group col-md-6">
												<label>Dealer Website: </label>
												<?php echo $dealerWebsite; ?>
											</div>
											<?php
												if ($signatureTitle != '' & file_exists('uploads/'.$signatureFilename)) {
											?>
												<div class="form-group col-md-6">
													<label>Signature: </label>
													<img src="uploads/<?php echo $signatureFilename; ?>" />
												</div>
												<div class="form-group col-md-6">
													<label>Signer Name (Title): </label>
													<?php echo $signatureSigner . " (" . $signatureTitle . ")"; ?>
												</div>
											<?php
												} else {
											?>
													<div class="form-group col-md-6">
														<label>Signature: </label>
														Scanned.
													</div>
											<?php
												}
											?>
									<?php

							} else {
								?>

										<form name="dealerForm" id="dealer_agreement_v3" method="POST" action="">
											<input type="hidden" name="Dlr_Affiliate_Fee_Pct" value=""/>
											<?php
										if ($acctID != "") {
											?>
												<input type="hidden" name="acctID" value="<?php echo $acctID; ?>"/>
											<?php

									}
									?>
											<div class="form-row">
												<div class="form-group col-md-4">
													<label>Agreement Date</label>
													<input type="text" class="form-control" value="<?php echo $agreementDate; ?>" name="agreementDate">
												</div>

												<div class="form-group col-md-4" style="padding-left: 50px;">
													<label>Multiple Locations?</label>
													<div class="form-group mb-0">
														<label class="radio-inline mr-3"><input type="radio" value="Y" name="multipleLocations"> Yes</label>
														<label class="radio-inline mr-3"><input type="radio" value="N" name="multipleLocations" checked> No</label>
													</div>
												</div>
												<div class="form-group col-md-4">
														<label>Do Locations Bill Separately?</label>
														<div class="form-group mb-0">
															<label class="radio-inline mr-3"><input type="radio" value="Y" name="individualBilling"> Yes</label>
															<label class="radio-inline mr-3"><input type="radio" value="N" name="individualBilling" checked> No</label>
														</div>
												</div>
												<div class="form-group col-md-6">
													<label>Dealer Name *</label>
													<?php
												if ($acctID == "") {
													?>
													<input type="text" class="form-control" name="dealerName" id="dealerName" value="<?php echo $dealerName; ?>" required>
													<span style="color: red;display: none;" id="dealerNameE">Please Enter Dealer Name..!</span>
													<?php

											} else {
												?>
														<?php echo $dealerName; ?>
													<?php

											}
											?>
												</div>
												<div class="form-group col-md-6">
													<label>DBA</label>
													<input type="text" class="form-control" name="dba">
												</div>
												<div class="form-group col-md-6">
													<label>Federal Tax ID *</label>
													<input type="text" value="<?php echo $federalTaxID; ?>" class="form-control" name="taxID" id="taxID" placeholder="99-9999999" maxlength="10">
													<span style="color: red;display: none;" id="taxIDE">Please Enter Federal Tax ID..!</span>
												</div>
												<div class="form-group col-md-6">
													<label>D-U-N-S</label>
													<input type="text" class="form-control" name="duns">
												</div>
												<div class="form-group col-md-6">
													<label>Business Street Address *</label>
													<input type="text" class="form-control" name="dealerAddress" id="dealerAddress">
													<span style="color: red;display: none;" id="dealerAddressE">Please Enter Business Street Address..!</span>
												</div>
												<div class="form-group col-md-6">
													<label>PO Box / Suite</label>
													<input type="text" class="form-control" name="poBox">
												</div>
												<div class="form-group col-md-6">
													<label>Business City *</label>
													<input type="text" class="form-control" name="dealerCity" id="dealerCity">
													<span style="color: red;display: none;" id="dealerCityE">Please Enter Business City..!</span>
												</div>
												<div class="form-group col-md-6 businessState">
													<label>Business State *</label>
													<select class="form-control default-select " name="dealerState" id="dealerState">
														<option value="" selected disabled>-- Select Business State --</option>
													<?php
												if (mysqli_num_rows($stateResult) > 0) {
														// output data of each row
													$loopCounter = 0;
													while ($row = mysqli_fetch_assoc($stateResult)) {
														$loopCounter++;
														?>
															<option value=<?php echo $row["St_Prov_ID"] ?>><?php echo $row["St_Prov_Nm"]; ?></option>
													<?php
											}
										} ?>
													</select>
													<span style="color: red;display: none;" id="dealerStateE">Please Select Business State..!</span>
												</div>
												<div class="form-group col-md-6">
													<label>Business Postal Code *</label>
													<input type="text" class="form-control" name="dealerZip" id="dealerZip">
													<span style="color: red;display: none;" id="dealerZipE">Please Enter Business Postal Code..!</span>
												</div>
												<div class="form-group col-md-6">
													<label>Phone Number *</label>
													<input type="phone" class="form-control" name="dealerPhone" id="dealerPhone">
													<span style="color: red;display: none;" id="dealerPhoneE">Please Enter Phone Number..!</span>
												</div>
												<div class="form-group col-md-6">
													<label>Fax</label>
													<input type="text" class="form-control" name="dealerFax">
												</div>
												<div class="form-group col-md-6">
													<label>Dealer License</label>
													<input type="text" class="form-control" name="dealerLicense">
												</div>

												<?php if(false){ ?>

													<div class="form-group col-md-6">
														<label>Dealer Affiliate Fee Percentage</label>
														<input type="text" class="form-control" name="Dlr_Affiliate_Fee_Pct">
													</div>
													<div class="form-group col-md-6">
														&nbsp;
													</div>

												<?php } ?>

												<div class="form-group col-md-6">
													<label>Business Email *</label>
													<input type="email" class="form-control" name="businessEmail" id="businessEmail">
													<span style="color: red;display: none;" id="businessEmailE">Please Enter Business Email..!</span>
												</div>
												<div class="form-group col-md-6">
													<label>Business Website *</label>
													<input type="url" placeholder="https://www.vitaltrendsusa.com" class="form-control businessWebsite" name="businessWebsite">
												</div>
												<div class="form-group col-md-6">
													<label>Primary Contact First Name *</label>
													<input type="text" class="form-control primaryContactData" name="primaryContactFirstName" id="primaryContactFirstName">
													<span style="color: red;display: none;" id="primaryContactFirstNameE">Please Enter Primary Contact First Name..!</span>
												</div>
												<div class="form-group col-md-6">
													<label>Primary Contact Last Name *</label>
													<input type="text" class="form-control primaryContactData" name="primaryContactLastName" id="primaryContactLastName">
													<span style="color: red;display: none;" id="primaryContactLastNameE">Please Enter Primary Contact Last Name..!</span>
												</div>
												<div class="form-group col-md-6">
													<label>Primary Contact Phone *</label>
													<input type="phone" class="form-control primaryContactData" name="primaryContactPhone" id="primaryContactPhone">
													<span style="color: red;display: none;" id="primaryContactPhoneE">Please Enter Primary Contact Phone..!</span>
												</div>
												<div class="form-group col-md-6">
													<label>Primary Contact Email *</label>
													<input type="email" class="form-control primaryContactData" name="primaryContactEmail" id="primaryContactEmail">
													<span style="color: red;display: none;" id="primaryContactEmailE">Please Enter Primary Contact Email..!</span>
												</div>
												<div class="form-group col-md-6">
													<label>Accounts Payable Contact First Name</label>
													<input type="text" class="form-control" name="accountsPayableContactFirstName">
												</div>
												<div class="form-group col-md-6">
													<label>Accounts Payable Contact Last Name</label>
													<input type="text" class="form-control" name="accountsPayableContactLastName">
												</div>
												<div class="form-group col-md-6">
													<label>Accounts Payable Contact Phone</label>
													<input type="phone" class="form-control" name="accountsPayableContactPhone">
												</div>
												<div class="form-group col-md-6">
													<label>Accounts Payable Contact Email</label>
													<input type="email" class="form-control" name="accountsPayableContactEmail">
												</div>
												<div class="form-group col-md-12">
													<div class="custom-control custom-checkbox mb-2">
														<input type="checkbox" class="custom-control-input" id="copyToShipAddress">
														<label class="custom-control-label" for="copyToShipAddress" style="color: black;font-weight: bold;">Shipping address is the same as Business address</label>
													</div>
												</div>
												<div class="form-group col-md-6">
													<label>Shipping Address</label>
													<input type="text" class="form-control" name="shipAddress" id="shipAddress" >
													<span style="color: red;display: none;" id="shipAddressE">Please Enter Shipping Address..!</span>
												</div>
												<div class="form-group col-md-6">
													<label>Shipping City</label>
													<input type="text" class="form-control" name="shipCity" id="shipCity" >
													<span style="color: red;display: none;" id="shipCityE">Please Enter Shipping City..!</span>
												</div>

												<div class="form-group col-md-6">
													<label>Shipping State</label>
													<select class="form-control default-select shipState" name="shipState" id="shipState">
														<option value="" selected disabled>-- Select Shipping State --</option>
														<?php
													if (mysqli_num_rows($stateShipResult) > 0) {
														// output data of each row
														$loopCounter = 0;
														while ($row = mysqli_fetch_assoc($stateShipResult)) {
															$loopCounter++;
															?>
															<option value=<?php echo $row["St_Prov_ID"] ?>><?php echo $row["St_Prov_Nm"]; ?></option>
													<?php
											}
										} ?>
													</select>
													<span style="color: red;display: none;" id="shipStateE">Please Select Shipping State..!</span>
												</div>

												<div class="form-group col-md-6">
													<label>Shipping Postal Code</label>
													<input type="text" class="form-control" name="shipZip" id="shipZip" >
													<span style="color: red;display: none;" id="shipZipE">Please Enter Shipping Postal Code..!</span>
												</div>
												<div class="form-group col-md-12">
													<label>Notes</label>
													<textarea class="form-control" name="notesField" rows="5" cols="60"></textarea>
												</div>
												<div class="form-group col-md-6">
													&nbsp;
												</div>
												<div class="form-group col-md-12 terms-text">
													<p>
														Tr&#252;North Global&#8482; and Retailer each agree as follows:
													</p>
														<ol>
															  <li>
																  Tr&#252;North Global&#8482; will provide marketing and sales brochures, Limited Warranty applications, point-of-sale and other
																  materials to assist Retailer in selling Limited Warranties to purchasers (“Purchasers”), enabling such Purchasers to
																  participate in a Tr&#252;North Global&#8482; Limited Warranty Program. Tr&#252;North Global&#8482; may change the terms of any Limited
																  Warranty, Limited Warranty Program, or cancel any Limited Warranty Program at any time upon notice to Retailer.
															  </li>
															  <li>
																  Retailer shall not alter, modify, waive, or discharge any terms or conditions of any Limited Warranty, Limited
																  Warranty Program or the materials provided by Tr&#252;North Global&#8482;. Tr&#252;North Global&#8482; shall be responsible for the
																  administration of all Limited Warranty Programs, including registration of all approved applications and
																  determination of claim responsibility.
															  </li>
															<li>
																Retailer shall review each Limited Warranty in detail with each Purchaser and explain the terms, conditions, coverage,
																and limits of liability, as well as the required maintenance and claims responsibilities of each Limited Warranty.
																Retailer shall obtain each Purchaser's signature on the Limited Warranty at the time of sale. Once signed, Retailer
																shall provide each Purchaser with a copy of their Limited Warranty and shall immediately submit a copy of the signed
																and completed Limited Warranty to Tr&#252;North Global&#8482; via email, DocuSign, fax, or Tr&#252;North Global&#8482; Dealer Portal.
															</li>
															<li>
																Upon receipt of an invoice from TrüNorth Global™ for payment under any Limited Warranty Program, Retailer shall

																remit such payment to TrüNorth Global™. Invoices are created from the wholesale prices and any applicable charges

																for such Limited Warranty Programs specified by TrüNorth Global™’s prevailing rate card(s) provided to Retailer.

																TrüNorth Global™ has the right to change wholesale prices and charges on such rate card(s) upon 60 days prior notice

																to Retailer.
															</li>
															<li>
																Retailer may offer and sell Limited Warranties in accordance with this Agreement at retail prices determined by

																Retailer and/or TrüNorth Global™’s suggested retail price. Retailer is responsible for collection and payment of all

																federal, state, and local taxes that may apply to the sale of the Limited Warranties by Retailer under this Agreement.
															</li>
															<li>
																Claims under any Limited Warranty Program can only be made by the Registered Owner listed under Section I. of

																the Limited Warranty for such Registered Owner. The Registered Owner is completely responsible for the

																maintenance, transfers, requested documentation, and other requirements as outlined in the Limited Warranty.
															</li>
															<li>
																This Agreement shall commence on the date set forth above and continue until terminated by either party with 60

																days’ notice prior to the renewal date. Upon the termination of this Agreement, Retailer shall return to TrüNorth

																Global™ all Limited Warranty Program materials and discontinue use of such materials and the TrüNorth Global™

																name.
															</li>
															<li>
																Retailer acknowledges that the Limited Warranty Programs and the materials delivered by TrüNorth Global™

																constitute the proprietary property of TrüNorth Global™. TrüNorth Global™ remains the sole owner of such

																proprietary property. Nothing in this Agreement shall be construed as a transfer, license, or assignment of TrüNorth

																Global™’s rights in such proprietary property. Retailer shall use the Limited Warranty Programs, materials, and

																TrüNorth Global™ name solely during the term of this Agreement for purposes of offering and selling the Limited

																Warranty Program. Limited Warranty Programs shall be fully administered and underwritten by TrüNorth Global™.
															</li>
															<li>
																TrüNorth Global™ agrees to indemnify and hold Retailer harmless from and against any and all claims, suits, actions,

																damages, judgments, settlements, liabilities, losses, costs and expenses including reasonable attorney’s fees (“Loss”)

																arising from any Limited Warranty Program sold by Retailer in accordance with this Agreement, unless such Loss

																arises from negligence or misconduct of or failure to comply with the terms of this Agreement by Retailer, its

																contractors, or their respective officers, employees, and agents.
															</li>
															<li>
																Retailer agrees to indemnify and hold TrüNorth Global™ harmless from any and all Losses arising from the

																negligence or misconduct of or failure to comply with the terms of this Agreement by Retailer, its contractors or their

																respective officers, employees, and agents.
															</li>
															<li>
																Retailer shall not assign, sell, or transfer this Agreement or any of its rights and obligations hereunder without the

																prior written consent of TrüNorth Global™. No modification, amendment, or supplement to this Agreement shall be

																effective or binding unless it is made in writing and duly executed by Retailer and TrüNorth Global™.
															</li>
															<li>
																Dispute Resolution:
																<ol type="a">
																	<li>This Agreement shall be governed by and construed in accordance with the laws of the State of North Carolina,
																		without regard to conflict of law principles.</li>
																	<li>
																		Arbitration Provision and waiver of jury and class action right:
																		<ol type="i">
																			<li>In the event of any dispute between the parties arising out of or related to this agreement in any way,

																				including for breach of this agreement, the dispute shall be settled by arbitration administered by the

																				American Arbitration Association (“AAA”). Arbitration is the sole method of dispute resolution

																				between the parties for arbitrable claims.
																			</li>
																			<li>Arbitration shall be administered in accordance with AAA’s Commercial Arbitration Rules, including,

																				where applicable, AAA’s Expedited Procedures for certain commercial disputes. The arbitration will be

																				heard by a single arbitrator selected by AAA. The arbitrator shall have the power to rule on his or her

																				own jurisdiction, including any objections with respect to the existence, scope, or validity of the

																				arbitration agreement or the arbitrability of any claim our counterclaim.</li>
																			<li>
																				Each of the parties will pay equally all arbitration fees and arbitrator compensation.
																			</li>
																		</ol>
																	</li>
																</ol>
															</li>
														</ol>
												</div>
												<div class="form-group col-md-12 row js-online-signature-container">
													<div class="form-group col-md-12 row">
														<div class="form-group col-md-12">
															<h5 class="font-weight-normal">Sign here</h5>
															<div class="signature"></div>
															<span style="color: red;display: none;" id="signatureE">Please Enter Signature Data..!</span>
														</div>
													</div>
													<div class="form-group col-md-6">
														<label>Signature Name *</label>
														<input type="text" class="form-control" name="retailerName" id="retailerName">
														<span style="color: red;display: none;" id="retailerNameE">Please Enter Retailer Name..!</span>
													</div>
													<div class="form-group col-md-6">
														<label>Signature Title *</label>
														<input type="text" class="form-control" name="retailerTitle" id="retailerTitle">
														<span style="color: red;display: none;" id="retailerTitleE">Please Enter Retailer Title..!</span>
													</div>
													<div class="form-group col-md-6">
														<label>Signed On Date</label>
														<input type="text" class="form-control" name="signedOnDate" id="signedOnDate" required value="<?php echo date("m/d/Y"); ?>">
														<span style="color: red;display: none;" id="signedOnDateE">Please Enter Signed On Date..!</span>
														<span style="color: red;display: none;" id="signedOnDateED">Please Enter Valid Date..!</span>
													</div>
												</div>
											</div>
											<button type="button" class="btn btn-primary" id="dealAgreementSubmit" name="dealAgreementSubmit">Submit</button>
										</form>
									<?php

							}
							?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--**********************************
                dealer_agreement form
            ***********************************-->

<!--            <div class="container-fluid">-->
<!--                <div class="row">-->
<!--                    <div class="col-md-12">-->
<!--                        <div class="card" id="toprint">-->
<!--                            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="table-layout: fixed; width: 100%; border-collapse: collapse;" >-->
<!--                                <tbody>-->
<!--                                    <tr>-->
<!--                                        <td>-->
<!--                                            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="table-layout: fixed; width: 100%; border-collapse: collapse;">-->
<!--                                                <tbody>-->
<!--                                                <tr>-->
<!--                                                    <td>-->
<!--                                                        <a href="index.php">-->
<!--                                                            <img src="images/vt_logo.png" alt="Vital Trends" />-->
<!--                                                        </a>-->
<!--                                                    </td>-->
<!--                                                    <td>-->
<!--                                                        <table  align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" >-->
<!--                                                            <tbody>-->
<!--                                                            <tr>-->
<!--                                                                <td style="text-align: left">-->
<!--                                                                    <h3>VITAL TRENDS DEALER AGREEMENT</h3>-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">SALE AGENT</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">ACCOUNT#</label>-->
<!--                                                                    <input type="number" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            </tbody>-->
<!--                                                        </table>-->
<!---->
<!---->
<!--                                                    </td>-->
<!--                                                </tr>-->
<!--                                                <tr>-->
<!--                                                    <td colspan="2" style="border: 1px solid #000;">-->
<!--                                                        <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="table-layout: fixed; width: 100%; border-collapse: collapse;">-->
<!--                                                            <tbody>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">BUSINESS NAME:</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">DEALER LICENSE#</label>-->
<!--                                                                    <input type="number" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">DBA:</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">CONTACT:</label>-->
<!--                                                                    <input type="phone" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">MAILING ADDRESS:</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">OWNER:</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            </tbody>-->
<!--                                                        </table>-->
<!--                                                    </td>-->
<!--                                                </tr>-->
<!--                                                </tbody>-->
<!--                                            </table>-->
<!--                                        </td>-->
<!--                                    </tr>-->
<!--                                </tbody>-->
<!--                            </table>-->
<!--                            <div class="card-body">-->
<!--                                <div class="basic-form">-->
<!--                                    <div class="body">-->
<!--                                        <div id="dialog" title="Digital Signature">-->
<!--                                            <canvas id="myCanvasSignature" acknowledged-data="false" class="signature-pad" width="400" height="100"></canvas>-->
<!--                                            <input id="UserName" name="UserName" type="hidden" value="My Signature">-->
<!--                                        </div>-->
<!--                                        <div id="change-sig" title="Edit Signature">-->
<!--                                            Please provide your new digital signature representation.<br/><br/>-->
<!--                                            <canvas id="signature-pad" class="signature-pad" width="400" height="100"></canvas>-->
<!--                                        </div>-->
<!--                                        <div class="signature-card">-->
<!--                                            <div class="legal-clause"></div>-->
<!--                                            <div class="sign-area">-->
<!--                                                <div class="sign-block pull-right">-->
<!--                                                    <div>Signature: <i class="fa fa-pencil" aria-hidden="true" onclick="Signature('jm38s4i1');"></i></div>-->
<!--                                                    <canvas id="Sig-jm38s4i1" class="dig-sig " sig-id-data="jm38s4i1" signed-data="false" width="400" height="100"></canvas>-->
<!--                                                </div>-->
<!--                                            </div>-->
<!--                                            <div class="clearfix"></div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="log-file">-->
<!--                                        <p>Log file</p>-->
<!--                                    </div>-->
<!--                                    <form>-->
<!--                                        <button class="btn btn-primary"  onclick="printpart()"/>Sign in</button>-->
<!--                                    </form>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
        </div>
        <!--**********************************
            Content body end
        ***********************************-->

<?php
require_once("includes/footer.php");
?>

		<!--**********************************
           Support ticket button start
        ***********************************-->

        <!--**********************************
           Support ticket button end
        ***********************************-->


    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

<!--**********************************
    Scripts
***********************************-->
<!-- Required vendors -->
<script src="./vendor/global/global.min.js"></script>
<script src="./vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="./vendor/chart.js/Chart.bundle.min.js"></script>
<script src="./vendor/owl-carousel/owl.carousel.js"></script>

<!-- Chart piety plugin files -->
<script src="./vendor/peity/jquery.peity.min.js"></script>

<!-- Apex Chart -->

<!-- Dashboard 1 -->
<script src="./js/custom.min.js"></script>
<script src="./js/deznav-init.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script src="./js/jSignature/jSignature.min.js"></script>
<script src="./js/jSignature/jSignInit.js"></script>
<script src="./js/custom-validation.js"></script>
<script>

jQuery('input').on('keypress', function(e) {
        if(e.which === 13)
		{
					var flag1,
			flag2,
			flag3,
			flag4,
			flag5,
			flag6,
			flag7,
			flag8,
			flag9,
			flag10,
			flag11,
			flag12,
			flag13,
			flag14,
			flag15,
			flag16,
			flag17,
			flag18,
			flag19,
			flag20 = 0;
			if ($("#dealerName").val() == "") {
			$("#dealerName").focus();
			$("#dealerNameE").css("display", "block");
			} else {
			$("#dealerNameE").css("display", "none");
			flag1 = 1;
			}

			if (
			$("#taxID").val() == "" ||
			!/^(\d{2})[-](\d{7})$/.test($("#taxID").val())
			) {
			$("#taxID").focus();
			$("#taxIDE").css("display", "block");
			} else {
			$("#taxIDE").css("display", "none");
			flag2 = 1;
			}

			if ($("#dealerAddress").val() == "") {
			$("#dealerAddress").focus();
			$("#dealerAddressE").css("display", "block");
			} else {
			$("#dealerAddressE").css("display", "none");
			flag3 = 1;
			}

			if ($("#dealerCity").val() == "") {
			$("#dealerCity").focus();
			$("#dealerCityE").css("display", "block");
			} else {
			$("#dealerCityE").css("display", "none");
			flag4 = 1;
			}

			if ($("#dealerState").val() == "" || $("#dealerState").val() == null) {
			$("#dealerState").focus();
			$("#dealerStateE").css("display", "block");
			} else {
			$("#dealerStateE").css("display", "none");
			flag5 = 1;
			}

			if ($("#dealerZip").val() == "") {
			$("#dealerZip").focus();
			$("#dealerZipE").css("display", "block");
			} else {
			$("#dealerZipE").css("display", "none");
			flag6 = 1;
			}

			if ($("#dealerPhone").val() == "") {
			$("#dealerPhone").focus();
			$("#dealerPhoneE").css("display", "block");
			} else {
			$("#dealerPhoneE").css("display", "none");
			flag7 = 1;
			}

			if ($("#businessEmail").val() == "") {
			$("#businessEmail").focus();
			$("#businessEmailE").css("display", "block");
			} else {
			$("#businessEmailE").css("display", "none");
			flag8 = 1;
			}

			if ($("#primaryContactFirstName").val() == "") {
			$("#primaryContactFirstName").focus();
			$("#primaryContactFirstNameE").css("display", "block");
			} else {
			$("#primaryContactFirstNameE").css("display", "none");
			flag9 = 1;
			}

			if ($("#primaryContactLastName").val() == "") {
			$("#primaryContactLastName").focus();
			$("#primaryContactLastNameE").css("display", "block");
			} else {
			$("#primaryContactLastNameE").css("display", "none");
			flag10 = 1;
			}

			if ($("#primaryContactPhone").val() == "") {
			$("#primaryContactPhone").focus();
			$("#primaryContactPhoneE").css("display", "block");
			} else {
			$("#primaryContactPhoneE").css("display", "none");
			flag11 = 1;
			}

			if ($("#primaryContactEmail").val() == "") {
			$("#primaryContactEmail").focus();
			$("#primaryContactEmailE").css("display", "block");
			} else {
			$("#primaryContactEmailE").css("display", "none");
			flag12 = 1;
			}

			flag13 = 1;
			flag14 = 1;
			flag15 = 1;
			flag16 = 1;
			/*
				if($("#shipAddress").val() == ''){
					$("#shipAddress").focus();
					$("#shipAddressE").css("display","block");
				} else {
					$("#shipAddressE").css("display","none");
					flag13=1;
				}

				if($("#shipCity").val() == ''){
					$("#shipCity").focus();
					$("#shipCityE").css("display","block");
				} else {
					$("#shipCityE").css("display","none");
					flag14=1;
				}

				if($("#shipState").val() == '' || $("#shipState").val() == null){
					$("#shipState").focus();
					$("#shipStateE").css("display","block");
				} else {
					$("#shipStateE").css("display","none");
					flag15=1;
				}

				if($("#shipZip").val() == ''){
					$("#shipZip").focus();
					$("#shipZipE").css("display","block");
				} else {
					$("#shipZipE").css("display","none");
					flag16=1;
				}
		*/

			if ($(".jSignature").jSignature("getData", "native").length == 0) {
			$("#signatureE").css("display", "block");
			} else {
			$("#signatureE").css("display", "none");
			flag17 = 1;
			}

			if ($("#retailerName").val() == "") {
			$("#retailerName").focus();
			$("#retailerNameE").css("display", "block");
			} else {
			$("#retailerNameE").css("display", "none");
			flag18 = 1;
			}

			if ($("#retailerTitle").val() == "") {
			$("#retailerTitle").focus();
			$("#retailerTitleE").css("display", "block");
			} else {
			$("#retailerTitleE").css("display", "none");
			flag19 = 1;
			}

			if ($("#signedOnDate").val() == "") {
			$("#signedOnDate").focus();
			$("#signedOnDateE").css("display", "block");
			} else {
			$("#signedOnDateE").css("display", "none");
			var signedOnDate1 = $("#signedOnDate").val();
			var validDate = "^(1[0-2]|0[1-9])/(3[01]|[12][0-9]|0[1-9])/[0-9]{4}$";
			if (signedOnDate1.match(validDate)) {
				$("#signedOnDateED").css("display", "none");
				flag20 = 1;
			} else {
				$("#signedOnDateED").css("display", "block");
			}
			}
		}
        });


</script>
<script>
    function carouselReview(){
        /*  testimonial one function by = owl.carousel.js */
        function checkDirection() {
            var htmlClassName = document.getElementsByTagName('html')[0].getAttribute('class');
            if(htmlClassName == 'rtl') {
                return true;
            } else {
                return false;

            }
        }

        jQuery('.testimonial-one').owlCarousel({
            loop:true,
            autoplay:true,
            margin:30,
            nav:false,
            dots: false,
            rtl: checkDirection(),
            left:true,
            navText: ['', ''],
            responsive:{
                0:{
                    items:1
                },
                1200:{
                    items:2
                },
                1600:{
                    items:3
                }
            }
        })
    }
    jQuery(window).on('load',function(){
        setTimeout(function(){
            carouselReview();
        }, 1000);
    });
</script>
<script>
	jQuery('.primaryContactData').on('keyup',function(){
		let PCFN = jQuery('#primaryContactFirstName').val();
		let PCLN = jQuery('#primaryContactLastName').val();
		let PCP = jQuery('#primaryContactPhone').val();
		let PCE = jQuery('#primaryContactEmail').val();
		jQuery('[name=accountsPayableContactFirstName]').val(PCFN);
		jQuery('[name=accountsPayableContactLastName]').val(PCLN);
		jQuery('[name=accountsPayableContactPhone]').val(PCP);
		jQuery('[name=accountsPayableContactEmail]').val(PCE);
	});

	jQuery('#taxID').on('keyup',function(){

		let taxID = jQuery('#taxID').val();
		if(taxID.length == 2) {
			if(/^(\d{2})$/.test(taxID)) {
				taxID +="-";
				jQuery('#taxID').val(taxID);
			}
		}
		else if (/^(\d{2})[-](\d{7})$/.test(taxID))
		{
			jQuery('#taxIDE').hide();
		}
		else {
			jQuery('#taxIDE').show();
			jQuery('#taxIDE').text("Please Enter Correct Federal Tax ID..!");
		}
	});


    function printpart () {
        var printwin = window.open("");
        printwin.document.write(document.getElementById("toprint").innerHTML);
        printwin.stop();
        printwin.print();
        printwin.close();
    }
</script>
</body>
</html>