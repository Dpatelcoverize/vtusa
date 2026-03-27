<?php
//
// File: dealer_affiliate_fee.php
// Author: Charles Parry
// Date: 5/13/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Dealer Affiliate Fee";
$pageTitle = "Dealer Affiliate Fee";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";


// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');

// Variables.
$dealerID = "";
$persID = "";
$bankDimID = "";
$agreementDate = "";
$dealerName = "";
$dealerAddress1 = "";
$dealerAddress2 = "";
$dealerCity = "";
$dealerState = "";
$dealerStateName = "";
$dealerZip = "";

$businessPersonalSelector = "";

// Vars for Business
$businessBankName = "";
$businessBankAccountName = "";
$businessBankBillingAddress = "";
$businessBankBillingCity = "";
$businessBankBillingState = "";
$businessBankBillingZip = "";
$businessBankRoutingNumber = "";
$businessBankAccountNumber = "";

// Vars for person
$persAcctID = "";
$personFirstName = "";
$personLastName = "";
$personEmail = "";
$personPhone = "";
$personDOB = "";
$personSSN = "";
$personAddress1 = "";
$personAddress2 = "";
$personCity = "";
$personState = "";
$personStateName = "";
$personZip = "";
$personBankName = "";
$personBankAccountName = "";
$personBankBillingAddress = "";
$personBankBillingCity = "";
$personBankBillingState = "";
$personBankBillingZip = "";
$personRoutingNumber = "";
$personAccountNumber = "";

$notesField = "";

$Affl_Fee_Amt = "";

$dealerLocationID = "";

$errorMessage = "";

$form_err = "";


session_start();

// Make sure a dealer is currently logged in, or go back to the Agreement
if (!(isset($_SESSION["userType"])) || !($_SESSION["userType"] == "dealer")) {
	header("location: index.php");
	exit;
}

// Get a dealer ID from session.
if (!(isset($_SESSION["id"]))) {
	header("location: index.php");
	exit;
} else {
	$dealerID = $_SESSION["id"];
	$adminID = $_SESSION["admin_id"];
}


// Get an error message from session if applicable.
if ((isset($_SESSION["errorMessage"]))) {
	$errorMessage = $_SESSION["errorMessage"];
	$_SESSION["errorMessage"] = "";
} else {
	$errorMessage = "";
}



// Process form data when form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	// Get form fields
	if (isset($_POST["persID"]) && !empty(trim($_POST["persID"]))) {
		$persID = trim($_POST["persID"]);
	}

	if (!empty(trim($_POST["businessPersonalSelector"]))) {
		$businessPersonalSelector = trim($_POST["businessPersonalSelector"]);
	}



	// Branch based on business or personal being selected.
	if ($businessPersonalSelector == "Business") {

		if (!empty(trim($_POST["businessBankName"]))) {
			$businessBankName = trim($_POST["businessBankName"]);
		}

		if (!empty(trim($_POST["businessBankAccountName"]))) {
			$businessBankAccountName = trim($_POST["businessBankAccountName"]);
		}

		if (!empty(trim($_POST["businessBankBillingAddress"]))) {
			$businessBankBillingAddress = trim($_POST["businessBankBillingAddress"]);
		}

		if (!empty(trim($_POST["businessBankBillingCity"]))) {
			$businessBankBillingCity = trim($_POST["businessBankBillingCity"]);
		}

		if (!empty(trim($_POST["businessBankBillingState"]))) {
			$businessBankBillingState = trim($_POST["businessBankBillingState"]);
		}

		if (!empty(trim($_POST["businessBankBillingZip"]))) {
			$businessBankBillingZip = trim($_POST["businessBankBillingZip"]);
		}

		if (!empty(trim($_POST["businessBankRoutingNumber"]))) {
			$businessBankRoutingNumber = trim($_POST["businessBankRoutingNumber"]);
		}

		if (!empty(trim($_POST["businessBankAccountNumber"]))) {
			$businessBankAccountNumber = trim($_POST["businessBankAccountNumber"]);
		}
		/*** Insert Banking Information ***/

		/* Prepare an insert statement to create a BANK_DIM entry for this user */
		$stmt = mysqli_prepare($link, "INSERT INTO Bank_Dim (Acct_ID,Bank_Nm,Bank_Acct_Nm,Bank_Rteg_Nbr,Bank_Acct_Nbr) VALUES (?,?,?,?,?)");

		/* Bind variables to parameters */
		$val1 = $dealerID;
		$val2 = $businessBankName;
		$val3 = $businessBankAccountName;
		$val4 = $businessBankRoutingNumber;
		$val5 = $businessBankAccountNumber;

		mysqli_stmt_bind_param($stmt, "issss", $val1, $val2, $val3, $val4, $val5);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the per bank dim ID just created.
		$bankDimID = mysqli_insert_id($link);


		/* Prepare an insert statement to create an Addr entry for the main address */
		$stmt = mysqli_prepare($link, "INSERT INTO Addr (Acct_ID,Bank_Dim_ID,St_Addr_1_Desc,City_Nm,St_Prov_ID,Pstl_Cd,Addr_Type_Cd,Addr_Type_Desc) VALUES (?,?,?,?,?,?,'Work','Work')");

		/* Bind variables to parameters */
		$val1 = $dealerID;
		$val2 = $bankDimID;
		$val3 = $businessBankBillingAddress;
		$val4 = $businessBankBillingCity;
		$val5 = $businessBankBillingState;
		$val6 = $businessBankBillingZip;

		mysqli_stmt_bind_param($stmt, "iissis", $val1, $val2, $val3, $val4, $val5, $val6);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);



		$stmt = mysqli_prepare($link, "INSERT INTO Note (Acct_ID,Note_Desc,Note_Type) VALUES (?,?,'affiliate')");

		/* Bind variables to parameters */
		$val1 = $dealerID;
		$val2 = $notesField;

		mysqli_stmt_bind_param($stmt, "is", $val1, $val2);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);



	} else {

	/*
		if(!empty(trim($_POST["personFirstName"]))){
			$personFirstName = trim($_POST["personFirstName"]);
		}

		if(!empty(trim($_POST["personLastName"]))){
			$personLastName = trim($_POST["personLastName"]);
		}

		if(!empty(trim($_POST["personEmail"]))){
			$personEmail = trim($_POST["personEmail"]);
		}

		if(!empty(trim($_POST["personPhone"]))){
			$personPhone = trim($_POST["personPhone"]);
		}

		if(!empty(trim($_POST["dealerLocationID"]))){
			$dealerLocationID = trim($_POST["dealerLocationID"]);
		}
		 */
		if (!empty(trim($_POST["personDOB"]))) {
			$personDOB = trim($_POST["personDOB"]);
		}

		if (!empty(trim($_POST["personSSN"]))) {
			$personSSN = trim($_POST["personSSN"]);
		}

		if (!empty(trim($_POST["personAddress1"]))) {
			$personAddress1 = trim($_POST["personAddress1"]);
		}

		if (!empty(trim($_POST["personAddress2"]))) {
			$personAddress2 = trim($_POST["personAddress2"]);
		}

		if (!empty(trim($_POST["personCity"]))) {
			$personCity = trim($_POST["personCity"]);
		}

		if (!empty(trim($_POST["personCity"]))) {
			$personCity = trim($_POST["personCity"]);
		}

		if (!empty(trim($_POST["personState"]))) {
			$personState = trim($_POST["personState"]);
		}

		if (!empty(trim($_POST["personZip"]))) {
			$personZip = trim($_POST["personZip"]);
		}

		if (!empty(trim($_POST["personBankAccountName"]))) {
			$personBankAccountName = trim($_POST["personBankAccountName"]);
		}

		if (!empty(trim($_POST["personBankBillingAddress"]))) {
			$personBankBillingAddress = trim($_POST["personBankBillingAddress"]);
		}

		if (!empty(trim($_POST["personBankBillingCity"]))) {
			$personBankBillingCity = trim($_POST["personBankBillingCity"]);
		}

		if (!empty(trim($_POST["personBankBillingState"]))) {
			$personBankBillingState = trim($_POST["personBankBillingState"]);
		}

		if (!empty(trim($_POST["personBankBillingZip"]))) {
			$personBankBillingZip = trim($_POST["personBankBillingZip"]);
		}

		if (!empty(trim($_POST["personRoutingNumber"]))) {
			$personRoutingNumber = trim($_POST["personRoutingNumber"]);
		}

		if (!empty(trim($_POST["personAccountNumber"]))) {
			$personAccountNumber = trim($_POST["personAccountNumber"]);
		}

		if (!empty(trim($_POST["notesField"]))) {
			$notesField = trim($_POST["notesField"]);
		}

		if (!empty(trim($_POST["Affl_Fee_Amt"]))) {
			$Affl_Fee_Amt = trim($_POST["Affl_Fee_Amt"]);
		}


		if ($persID != "") {

			// Do a little error checking
			// If we have a PersID, make sure it is part of this dealer
			$query = "SELECT * FROM Usr_Loc WHERE Pers_ID=" . $persID . " AND Dlr_Acct_ID in (
			SELECT Acct_ID FROM Acct WHERE Acct_ID=" . $dealerID . " OR Prnt_Acct_ID=" . $dealerID . ");";

			$result = $link->query($query);
			if (!$result) {
//			if(!($result->numRows > 0)){
				$_SESSION["errorMessage"] = "Supplied user ID is not in this dealer account.";
				header("location: dealer_affiliate_fee.php");
				die();
			} else {
				// Get the specific Acct_ID for this Pers, since it may be a location of the parent dealer.
				$row = $result->fetch_assoc();
				$persAcctID = $row["Dlr_Acct_ID"];

			}


			// Add the SSN, DOB and Affiliate Percentage to the existing Pers entry
			$stmt = mysqli_prepare($link, "UPDATE Pers SET Brth_Dt=?,Soc_Secur_Nbr=?,Affl_Fee_Amt=? WHERE Pers_ID=?");

			/* Bind variables to parameters */
			$val1 = date('Y-m-d', strtotime($personDOB));
			$val2 = $personSSN;
			$val3 = $Affl_Fee_Amt;
			$val4 = $persID;
/*
echo "Affl_Fee_Amt=".$Affl_Fee_Amt;
echo "<br />persID=".$persID;
die();
*/
			mysqli_stmt_bind_param($stmt, "ssii", $val1, $val2, $val3, $val4);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);


			// Save the address information for this Pers
			$stmt = mysqli_prepare($link, "INSERT INTO Addr (Pers_ID,St_Addr_1_Desc,St_Addr_2_Desc,City_Nm,St_Prov_ID,Pstl_Cd,Addr_Type_Cd,Addr_Type_Desc,Prim_Addr_Flg) VALUES (?,?,?,?,?,?,'Work','Work','N')");

			/* Bind variables to parameters */
			$val1 = $persID;
			$val2 = $personAddress1;
			$val3 = $personAddress2;
			$val4 = $personCity;
			$val5 = $personState;
			$val6 = $personZip;

			mysqli_stmt_bind_param($stmt, "isssis", $val1, $val2, $val3, $val4, $val5, $val6);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);


			// No longer creating user accounts in this workflow.  Saving code for now.
			if (false) {
				// Do a little error checking
				// If adding new, make sure no other user in this dealer account has the same firstname/lastname combo
				$stmt = mysqli_prepare($link, "SELECT * FROM Pers WHERE Acct_ID=? AND Pers_Last_Nm=? AND Pers_Frst_Nm=?");

				/* Bind variables to parameters */
				$val1 = $dealerID;
				$val2 = $personLastName;
				$val3 = $personFirstName;

				mysqli_stmt_bind_param($stmt, "iss", $val1, $val2, $val3);

				/* Execute the statement */
				if (mysqli_stmt_execute($stmt)) {
					$result = mysqli_stmt_get_result($stmt);
					if (!($result)) {
						$_SESSION["errorMessage"] = "Supplied FirstName and LastName match existing user.";
						header("location: dealer_affiliate_fee.php");
						die();
					}
				}


				/* Prepare an insert statement to create a Pers entry for this user */
				$stmt = mysqli_prepare($link, "INSERT INTO Pers (Acct_ID,Pers_Full_Nm,Pers_Last_Nm,Pers_Frst_Nm,Pers_Username,Soc_Secur_Nbr,Brth_Dt,Pswd_Hash_Cd,Cntct_Prsn_For_Acct_Flg) VALUES (?,?,?,?,?,?,?,?,'N')");

				// Format DOB properly
				$formattedPersonDOB = date_format(date_create($personDOB), "Y-m-d");

				/* Bind variables to parameters */
				$val1 = $dealerID;
				$val2 = $personFirstName . " " . $personLastName;
				$val3 = $personLastName;
				$val4 = $personFirstName;
				$val5 = $personEmail;  // username
				$val6 = $personSSN;
				$val7 = $formattedPersonDOB;
				$val8 = password_hash("PASSWORD", PASSWORD_DEFAULT);  // password

				mysqli_stmt_bind_param($stmt, "isssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8);

				/* Execute the statement */
				$result = mysqli_stmt_execute($stmt);

				// Get the per Pers_ID of the primary contact person.
				$persID = mysqli_insert_id($link);


				/* Prepare an insert statement to create a Tel entry for the primary contact person phone */
				$stmt = mysqli_prepare($link, "INSERT INTO Tel (Acct_ID,Pers_ID,Tel_Nbr,Tel_Type_Cd,Tel_Type_Desc,Prim_Tel_Flg) VALUES (?,?,?,'Work','Work','Y')");

				/* Bind variables to parameters */
				$val1 = $dealerID;
				$val2 = $persID;
				$val3 = $personPhone;

				mysqli_stmt_bind_param($stmt, "iis", $val1, $val2, $val3);

				/* Execute the statement */
				$result = mysqli_stmt_execute($stmt);

				/* Prepare an insert statement to create an Email entry for the primary contact person email */
				$stmt = mysqli_prepare($link, "INSERT INTO Email (Acct_ID,Pers_ID,Email_URL_Desc,Email_Type_Cd,Email_Type_Desc,Email_Prim_Flg) VALUES (?,?,?,'Work','Work','Y')");

				/* Bind variables to parameters */
				$val1 = $dealerID;
				$val2 = $persID;
				$val3 = $personEmail;

				mysqli_stmt_bind_param($stmt, "iis", $val1, $val2, $val3);

				/* Execute the statement */
				$result = mysqli_stmt_execute($stmt);

			} // if(false) //

			/* Prepare an insert statement to create an Note entry for the note field */
			$stmt = mysqli_prepare($link, "INSERT INTO Note (Acct_ID,Pers_ID,Note_Desc,Note_Type) VALUES (?,?,?,'affiliate')");

			/* Bind variables to parameters */
			$val1 = $persAcctID;
			$val2 = $persID;
			$val3 = $notesField;

			mysqli_stmt_bind_param($stmt, "iis", $val1, $val2, $val3);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);



			/*** Insert Banking Information ***/

			/* Prepare an insert statement to create a BANK_DIM entry for this user */
			$stmt = mysqli_prepare($link, "INSERT INTO Bank_Dim (Acct_ID,Pers_ID,Bank_Nm,Bank_Acct_Nm,Bank_Rteg_Nbr,Bank_Acct_Nbr) VALUES (?,?,?,?,?,?)");

			/* Bind variables to parameters */
			$val1 = $persAcctID;
			$val2 = $persID;
			$val3 = $personBankName;
			$val4 = $personBankAccountName;
			$val5 = $personRoutingNumber;
			$val6 = $personAccountNumber;

			mysqli_stmt_bind_param($stmt, "iissss", $val1, $val2, $val3, $val4, $val5, $val6);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);

			// Get the bank dim ID.
			$bankDimID = mysqli_insert_id($link);


			/* Prepare an insert statement to create an Addr entry for the main address */
			$stmt = mysqli_prepare($link, "INSERT INTO Addr (Acct_ID,Bank_Dim_ID,St_Addr_1_Desc,City_Nm,St_Prov_ID,Pstl_Cd,Addr_Type_Cd,Addr_Type_Desc) VALUES (?,?,?,?,?,?,'Work','Work')");

			/* Bind variables to parameters */
			$val1 = $persAcctID;
			$val2 = $bankDimID;
			$val3 = $personBankBillingAddress;
			$val4 = $personBankBillingCity;
			$val5 = $personBankBillingState;
			$val6 = $personBankBillingZip;

			mysqli_stmt_bind_param($stmt, "iissis", $val1, $val2, $val3, $val4, $val5, $val6);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);

			// No bank phone currently being captured.
			if (false) {
				/* Prepare an insert statement to create a Tel entry for the main phone */
				$stmt = mysqli_prepare($link, "INSERT INTO Tel (Acct_ID,Bank_Dim_ID,Tel_Nbr,Tel_Type_Cd,Tel_Type_Desc,Prim_Tel_Flg) VALUES (?,?,'Work','Work','Y')");

				/* Bind variables to parameters */
				$val1 = $dealerID;
				$val2 = $bankDimID;
				$val2 = $personBankPhone;

				mysqli_stmt_bind_param($stmt, "is", $val1, $val2);

				/* Execute the statement */
				$result = mysqli_stmt_execute($stmt);
			}

		} else {


		} // if(false) //


	} // business or personal selector



//	$_SESSION["errorMessage"] = "error test";
//	header("location: dealer_affiliate_fee.php");
//	die();


/*
	$query = "SELECT * FROM PersID WHERE Acct_ID=".$dealerID." AND Pers_Last_Nm='".$personLastName."' AND Pers_First_Nm='".$personFirstName."';";
	$result = $link->query($query);
	if(!($result->numRows > 0)){
		$_SESSION["errorMessage"] = "Supplied user ID is not in this dealer account.";
		header("location: dealer_affiliate_fee.php");
		die();
	}
	 */


// Update tracker for dealer forms, to indicate the addendum is signed
	$stmt = mysqli_prepare($link, "UPDATE Dealer_Progress SET Dealer_Fee_Form_Complete='Y' WHERE Acct_ID=?");

/* Bind variables to parameters */
	$val1 = $dealerID;

	mysqli_stmt_bind_param($stmt, "i", $val1);

/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);



	if (false) {

	/* Prepare an insert statement to create a Dealer_Affiliate_Fee_Temp entry for this Acct_ID */
		$sqlString = "INSERT INTO Dealer_Affiliate_Fee_Temp (Acct_ID,Type_Select,Bank_Name,Bank_Address,Bank_City,";
		$sqlString .= "Bank_State,Bank_Zip,Bank_Account_Number,Bank_Routing_Number,";
		$sqlString .= "Personal_First_Name,Personal_Last_Name,Personal_Email,Personal_Phone,Personal_DOB,";
		$sqlString .= "Personal_SSN,Personal_Bank_Name,Personal_Bank_Address,Personal_Bank_City,Personal_Bank_State,";
		$sqlString .= "Personal_Bank_Zipcode,Personal_Bank_Account_Number,Personal_Bank_Routing_Number,createdDate) VALUES ";
		$sqlString .= "(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())";
		$stmt = mysqli_prepare($link, $sqlString);

	/* Bind variables to parameters */
		$val1 = $dealerID;
		$val2 = $businessPersonalSelector;
		$val3 = $bankName;
		$val4 = $bankBillingAddress;
		$val5 = $bankBillingCity;
		$val6 = $bankBillingState;
		$val7 = $bankBillingZip;
		$val8 = $bankAccountNumber;
		$val9 = $bankRoutingNumber;
		$val10 = $personFirstName;
		$val11 = $personLastName;
		$val12 = $personEmail;
		$val13 = $personPhone;
		$val14 = $personDOB;
		$val15 = $personSSN;
		$val16 = $personBankName;
		$val17 = $personBankBillingAddress;
		$val18 = $personBankBillingCity;
		$val19 = $personBankBillingState;
		$val20 = $personBankBillingZip;
		$val21 = $personAccountNumber;
		$val22 = $personRoutingNumber;


		mysqli_stmt_bind_param($stmt, "isssssssssssssssssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22);

	/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


	// Update tracker for dealer forms, to indicate the addendum is signed
		$stmt = mysqli_prepare($link, "UPDATE Dealer_Progress SET Dealer_Fee_Form_Complete='Y' WHERE Acct_ID=?");

	/* Bind variables to parameters */
		$val1 = $dealerID;

		mysqli_stmt_bind_param($stmt, "i", $val1);

	/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

	}

//

// Genertae PDF	// Start PDF Code here....
	$generatePdf = true;

	// Get the dealer address info
	$query = "SELECT * FROM Addr WHERE Acct_ID=" . $dealerID . " AND Addr_Type_Cd='Work';";
	$result = $link->query($query);
	if ($result) {
		$row = $result->fetch_assoc();

		$dealerAddress1 = $row["St_Addr_1_Desc"];
		$dealerAddress2 = $row["St_Addr_2_Desc"];
		$dealerCity = $row["City_Nm"];
		$dealerState = $row["St_Prov_ID"];
		$dealerZip = $row["Pstl_Cd"];
	} else {
		$generatePdf = false;
	}
	// Look up the state name
	if ($dealerState > 0) {
		$query = "SELECT * FROM St_Prov WHERE St_Prov_ID=" . $dealerState;
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$dealerStateName = $row["St_Prov_ISO_2_Cd"];

	} else {
		$dealerStateName = "None Found";
	}


	// Get the dealer info
	$query = "SELECT * FROM Acct WHERE Acct_ID=" . $dealerID . ";";
	$result = $link->query($query);
	if ($result) {
		$row = $result->fetch_assoc();
		$dealerName = $row["Acct_Nm"];
	} else {
		$dealerName = "";
		$generatePdf = false;
	}

	// Get the contract info
	$query = "SELECT cd.Contract_Date FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
	$result = $link->query($query);

	if ($row = $result->fetch_assoc()) {
		$agreementDate = $row["Contract_Date"];
	} else {
		$agreementDate = "";
		$generatePdf = false;
	}


	// Check if there is a business bank entry yet
	$stmt = mysqli_prepare($link, "SELECT * FROM Bank_Dim b, Addr a WHERE b.Acct_ID=? AND b.Pers_ID=0 AND b.Bank_Dim_ID=a.Bank_Dim_ID");

	/* Bind variables to parameters */
	$val1 = $dealerID;

	mysqli_stmt_bind_param($stmt, "i", $val1);

	/* Execute the statement */
	if (mysqli_stmt_execute($stmt)) {
		$result = mysqli_stmt_get_result($stmt);
		if (($result)) {
			$num_rows = mysqli_num_rows($result);
			if ($num_rows > 0) {
				$row = mysqli_fetch_assoc($result);
				$businessBankName = $row["Bank_Nm"];
				$businessBankAccountName = $row["Bank_Nm"];
				$businessBankBillingAddress = $row["St_Addr_1_Desc"];
				$businessBankBillingCity = $row["City_Nm"];
				$businessBankBillingState = $row["St_Prov_ID"];
				$businessBankBillingZip = $row["Pstl_Cd"];
				$businessBankRoutingNumber = $row["Bank_Rteg_Nbr"];
				$businessBankAccountNumber = $row["Bank_Acct_Nbr"];
			}
		} else {
			$generatePdf = false;
		}
	} else {
		$generatePdf = false;
	}

	if ($generatePdf) {
    // create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	   // set document information
		$pdf->setCreator(PDF_CREATOR);
		$pdf->setAuthor('AFFILIATE FEE');
		$pdf->setTitle('AFFILIATE FEE');
		$pdf->setSubject('AFFILIATE FEE');
		$pdf->setKeywords('AFFILIATE FEE, Vital, Data, Set, Guide');

	   // set default header data
	   //$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
		$pdf->setPrintHeader(false);
	   //$pdf->setFooterData(array(0,64,0), array(0,64,128));

	   // set header and footer fonts
		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	   // set default monospaced font
		$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	   // set margins
		$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP - 15, PDF_MARGIN_RIGHT);
		$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

	   // set auto page breaks
		$pdf->setAutoPageBreak(true, PDF_MARGIN_BOTTOM);

	   // set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	   // set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once(dirname(__FILE__) . '/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
	   // ---------------------------------------------------------

	   // set default font subsetting mode
		$pdf->setFontSubsetting(true);

	   // Set font
	   // dejavusans is a UTF-8 Unicode font, if you only need to
	   // print standard ASCII chars, you can use core fonts like
	   // helvetica or times to reduce file size.
	   //$pdf->setFont('dejavusans', '', 14, '', true);
		$cambriabF = TCPDF_FONTS::addTTFfont('tcpdf/fonts/cambria/Cambria Math.ttf', 'TrueTypeUnicode', '', 32);
		$pdf->setFont($cambriabF, '', 14, '', true);
	   // Add a page
	   // This method has several options, check the source code documentation for more information.
		$pdf->AddPage();

	   // set text shadow effect
		$pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(0, 0, 0), 'opacity' => 0, 'blend_mode' => 'Normal'));
		$agreeDate = '<u>' . date('d-m-Y', strtotime($agreementDate)) . '</u>';
		$assignDate = date('d-m-Y', strtotime($agreementDate));

		$html = '
		   <style type="text/css">
			   .fontClass{
				   font-size: 13px;
				   font-weight:normal;
			   }
			   .textJustify{
				   text-align:justify;
			   }
		   </style>
		   <table>
			   <tr>
				   <td style="width:20%;"></td>
				   <td style="width:70%;text-align: center;vertical-align: middle;width:60%;"><img src="images/TM2.png" /></td>
				   <td style="width:10%;"></td>
			   </tr>
		   </table>
		    <table>
			   <tr>
				   <td style="width:100%;"><p style="font-size:13px;text-align:center;">
				   <span style="text-align:center;margin-top:5%;font-size:18px;font-weight:bold;">TRÜNORTH GLOBAL<sup style="font-weight:normal;">TM</sup> AUTHORIZED RETAILER AGREEMENT</span><br>
					   <span style="font-size:13px;">This Agreement is entered into this date: __' . $agreeDate . '__	, between TrüNorth Global Corporation™, located </span>
					   <br><span style="font-size:13px;">at 16740 Birkdale Commons Parkway, Suite 208, Huntersville, North Carolina, 28078, , referred to as “TrüNorth</span><br>
					   <span style="font-size:13px;">Global™”, and the entity identified in the box below referred to as “Retailer.”</span>
				   </p></td>
			   </tr>
			</table><br/><br/>
			<table cellspacing="0" cellpadding="5" border="1" style="border-color:grey;">
				<tr>
					<td style="width:100%;">
						<table border="0">
								<tr>
							      <td class="fontClass" style="width:70%;padding-left:40px;">Dealer Name: ' . $dealerName . '</td>
							      <td class="fontClass" style="width:30%;padding-left:40px;">Agreement Date: ' . $agreeDate . '</td>
								</tr>
						</table>
					</td>			  
				</tr>
				<tr>
					<td style="width:100%;">
						<table border="0">
							<tr>
							  <td class="fontClass" style="width:100%;padding-left:40px;">Dealership Address: ' . $dealerAddress1 . ' ' . $dealerCity . ', ' . $dealerStateName . '. ' . $dealerZip . '</td>
							</tr>
						</table>
					</td>			
				</tr>
			</table>
			<br/><br/>
			<table border="0" style="width:100%;">
				<tr>
					<td class="fontClass" style="width:100%;" style="color:#201f58; font-weight:bolder;">Personal Information </td>
				</tr>
			</table>
			<table cellspacing="0" cellpadding="5" border="1" style="border-color:grey;">
				<tr>
					<td style="width:100%;">
						<table border="0">
							<tr>
								<td class="fontClass" style="width:50%;padding-left:40px;">Date of Birth: ' . $dealerName . '</td>
								<td class="fontClass" style="width:50%;padding-left:40px;">Social Security Number: ' . $personSSN . '</td>
							</tr>
							<tr>
								<td class="fontClass" style="width:50%;padding-left:40px;">Date of Birth: ' . date_format(date_create($personDOB), "Y-m-d") . '</td>
								<td class="fontClass" style="width:50%;padding-left:40px;">Social Security Number: ' . $personSSN . '</td>
							</tr>
							<tr>
								<td class="fontClass" style="width:50%;padding-left:40px;">Address 1: ' . $personAddress1 . '</td>
								<td class="fontClass" style="width:50%;padding-left:40px;">Address 2: ' . $personAddress2 . '</td>
							</tr>
							<tr>
								<td class="fontClass" style="width:35%;padding-left:40px;">City: ' . $personCity . '</td>
								<td class="fontClass" style="width:35%;padding-left:40px;">State: ' . $personStateName . '</td>
								<td class="fontClass" style="width:30%;padding-left:40px;">Postal Code: ' . $personZip . '</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>

		   	<br/><br/>
		    <table border="0" style="width:100%;">
				<tr>
					<td class="fontClass" style="width:100%;" style="color:#201f58; font-weight:bolder;">Bank Information </td>
				</tr>
			</table>
			<table cellspacing="0" cellpadding="5" border="1" style="border-color:grey;">
				<tr>
					<td style="width:100%;">
						<table border="0">
							<tr>
								<td class="fontClass" style="width:50%;padding-left:40px;">Bank Name: ' . $personBankName . '</td>
								<td class="fontClass" style="width:50%;padding-left:40px;">Name on Bank Account: ' . $personBankAccountName . '</td>
							</tr>
							<tr>
								<td class="fontClass" style="width:50%;padding-left:40px;">Bank Account Address: ' . $personBankBillingAddress . '</td>
								<td class="fontClass" style="width:50%;padding-left:40px;">Bank Account City: ' . $personBankBillingCity . '</td>
							</tr>
							<tr>
								<td class="fontClass" style="width:50%;padding-left:40px;">Bank State: ' . $personBankBillingState . '</td>
								<td class="fontClass" style="width:50%;padding-left:40px;">Bank Postal Code: ' . $personBankBillingZip . '</td>
							</tr>
							<tr>
								<td class="fontClass" style="width:50%;padding-left:40px;">Bank Routing Number: ' . $personRoutingNumber . '</td>
								<td class="fontClass" style="width:50%;padding-left:40px;">Bank Account Number: ' . $personAccountNumber . '</td>
							</tr>
						</table>
					</td>
			   	</tr>
			</table>
			<br/><br/>
		    <table border="0" style="width:100%;">
				<tr>
					<td class="fontClass" style="width:100%;" style="color:#201f58; font-weight:bolder;">Notes </td>
				</tr>
			</table>
			<table cellspacing="0" cellpadding="5" border="1" style="border-color:grey;">
				<tr>
					<td style="width:100%;">
						<table border="0">
							   <tr>
									<td class="fontClass" style="width:100%;padding-left:40px;">' . $notesField . '</td>
								</tr>
						</table>
					</td>
				</tr>
			</table>

		   <br/><br/>

		   <table cellspacing="0" cellpadding="5" border="1" style="border-color:gray;">
			   <tr>
					<td style="width:100%;">
							<table border="0">
								<tr>
								 <td class="fontClass">To Be Completed by TrüNorth Global™</td>
								</tr>
							</table>
					</td>
			   </tr>
			   <tr>
				   <td style="width:100%;">
					   <table border="0">
						   <tr>
							   <td class="fontClass" style="width:70%;">Assigned Retailer #:</td>
							   <td class="fontClass" style="width:30%;">Date: ' . $assignDate . '</td>
						   </tr>
					   </table>
				   </td>
			   </tr>
			   <tr>
					<td style="width:100%;">
						<table border="0">
							<tr>
								<td class="fontClass" style="width:100%;">Assigned Program(s)</td>
						    </tr>
						</table>
					</td>
			   </tr>
		   </table>
		   <br/><br/><br/>

		   <table>
			   <tr>
				   <td class="fontClass" style="width:70%;">TrüNorth Global™ Signature:__________________________________________________________</td>
				   <td class="fontClass" style="width:30%;">Date:' . $agreeDate . '__________________</td>
			   </tr>
		   </table>
	   ';

		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

		$pdfFileName = str_replace(" ", "_", $dealerName) . '_' . time() . '.pdf';

		$pdf->Output(__DIR__ . '/uploads/dealer_affiliateFee_pdf/' . $pdfFileName, 'F');

	   // Save Pddf into database
		$query = "SELECT Pers_ID FROM Pers WHERE Acct_ID=" . $dealerID . ";";
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$primary_Contact_Person_id = $row['Pers_ID'];

	   // Get the contract info
		$query = "SELECT cd.Cntrct_Dim_ID FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$contract_dim_ID = $row["Cntrct_Dim_ID"];

	   // Add this file to our File_Assets tracking table
	   //  Set type=1 for 'dealer agreement'.
		$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
	                                  Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,4,'Dealer Affiliate Fee',NOW())");

	   /* Bind variables to parameters */
		$val1 = $dealerID;
		$val2 = $primary_Contact_Person_id;
		$val3 = $adminID;
		$val4 = $contract_dim_ID;
		$val5 = '/uploads/dealer_affiliateFee_pdf/' . $pdfFileName;

		mysqli_stmt_bind_param($stmt, "iiiis", $val1, $val2, $val3, $val4, $val5);

	   /* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

	   //============================================================+
	   // END OF FILE
	   //============================================================+

   	// End PDF Code here
	// // API Call to TruNorth
	// 	$url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/retailers/create-retailer?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";

	// 	$curl = curl_init($url);
	// 	curl_setopt($curl, CURLOPT_URL, $url);
	// 	curl_setopt($curl, CURLOPT_POST, true);
	// 	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	// 	$headers = array(
	// 		"Accept: application/json",
	// 		"Content-Type: application/json",
	// 	);
	// 	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	// 	if (file_exists('uploads/dealer_affiliateFee_pdf/' . $pdfFileName)) {
	// 		$b64PDFDoc = base64_encode(file_get_contents('uploads/dealer_affiliateFee_pdf/' . $pdfFileName));
	// 	} else {
	// 		$b64PDFDoc = base64_encode(file_get_contents("files/TEST_AGREEMENT_PDF.pdf"));
	// 	}

	// 	// get business email
	// 	// Select * from Email where acct_id = 1306 AND Pers_ID=425 AND Email_Prim_Flg = 'Y';

	// 	// Select Email for the dealer.
	// 	$emailResult = selectEmailByAcct($link, $dealerID, "Y", "Work");
	// 	if ($emailResult) {
	// 		$row = $emailResult->fetch_assoc();
	// 		$dealerEmail = $row["Email_URL_Desc"];
	// 	} else {
	// 		$dealerEmail = "";
	// 	}
	// 	// Select Tel for the dealer.
	// 	$telResult = selectTelByAcct($link, $dealerID, "Y", "Work");
	// 	if ($telResult) {
	// 		$row = $telResult->fetch_assoc();
	// 		$dealerPhone = $row["Tel_Nbr"];
	// 	} else {
	// 		$dealerPhone = "";
	// 	}


	// 	$data = "{
	// 	\"retailerName\": \"$dealerName\",
	// 	\"retailerEmail\": \"$dealerEmail\",
	// 	\"retailerPhone\": \"$dealerPhone\",
	// 	\"retailerAddress\": {
	// 	\"street\": \"$dealerAddress1\",
	// 	\"street2\": \"$dealerAddress2\",
	// 	\"city\": \"$dealerCity\",
	// 	\"state\": \"$dealerState\",
	// 	\"zip\": \"$dealerZip\",
	// 	\"country\": \"US\"
	// 	},
	// 	\"defaultCurrency\": \"USD\",
	// 	\"validationMethod\": \"ECA Only\",
	// 	\"files\" : [{\"type\" : \"vtAgreement\", \"fileBytes\" : \"$b64PDFDoc\"}]
	// 	}";

	// 	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

	// 	$resp = curl_exec($curl);
	// 	curl_close($curl);
	// 	//var_dump($resp);

	// 	$json = json_decode($resp, true);
	// 	// print_r($json);

	// 	if (isset($json) && array_key_exists("success", $json)) {
	// 		$responseStatus = $json["success"];
	// 	} else {
	// 		$responseStatus = 0;
	// 	}

	// 	if ($responseStatus == 1) {
	// 		$arNumber = $json["data"]["arNumber"];
	// 		$apiMessage = $json["message"];

	// 		// Save the returned retailer number to the CNTRCT_DIM table.
	// 		$stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim SET Assign_Rtlr_Nbr=? WHERE Cntrct_Dim_ID=?");

	// 		/* Bind variables to parameters */
	// 		$val1 = $arNumber;
	// 		$val2 = $contract_dim_ID;

	// 		mysqli_stmt_bind_param($stmt, "si", $val1, $val2);

	// 		/* Execute the statement */
	// 		$result = mysqli_stmt_execute($stmt);

	// 	} else {
	// 		$arNumber = "FAILED";
	// 		$apiMessage = "NONE";
	// 		$responseStatus = 0;
	// 	}


	// 	// Create a new API_Data entry to track activity
	// 	$stmt = mysqli_prepare($link, "INSERT INTO API_Responses (Acct_ID,statusCode, dataReturned, arNumber, messageText, sentJSON, returnedJSON, createdDate) VALUES (?,?,?,?,?,?,?,NOW())");

	// 	/* Bind variables to parameters */
	// 	$val1 = $dealerID;
	// 	$val2 = $responseStatus;
	// 	$val3 = $arNumber;
	// 	$val4 = $arNumber;
	// 	$val5 = $apiMessage;
	// 	$val6 = $data;
	// 	$val7 = $resp;

	// 	mysqli_stmt_bind_param($stmt, "issssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7);

	// 	/* Execute the statement */
	// 	$result = mysqli_stmt_execute($stmt);


	// // End API section
	}
//
	// Redirect back to this page so that more entries can be made.
	header("location: dealer_affiliate_fee.php");
	exit;


	die();
} else {

	// Get the dealer address info
	$query = "SELECT * FROM Addr WHERE Acct_ID=" . $dealerID . " AND Addr_Type_Cd='Work';";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$dealerAddress1 = $row["St_Addr_1_Desc"];
	$dealerAddress2 = $row["St_Addr_2_Desc"];
	$dealerCity = $row["City_Nm"];
	$dealerState = $row["St_Prov_ID"];
	$dealerZip = $row["Pstl_Cd"];

	// Look up the state name
	if ($dealerState > 0) {
		$query = "SELECT * FROM St_Prov WHERE St_Prov_ID=" . $dealerState;
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$dealerStateName = $row["St_Prov_ISO_2_Cd"];

	} else {
		$dealerStateName = "None Found";
	}


	// Get the dealer info
	$query = "SELECT * FROM Acct WHERE Acct_ID=" . $dealerID . ";";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$dealerName = $row["Acct_Nm"];

	// Get the contract info
	$query = "SELECT cd.Contract_Date FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
	$result = $link->query($query);
	if ($row = $result->fetch_assoc()) {
		$agreementDate = $row["Contract_Date"];
	} else {
		$agreementDate = "";
	}


	// Check if there is a business bank entry yet
	$stmt = mysqli_prepare($link, "SELECT * FROM Bank_Dim b, Addr a WHERE b.Acct_ID=? AND b.Pers_ID=0 AND b.Bank_Dim_ID=a.Bank_Dim_ID");

	/* Bind variables to parameters */
	$val1 = $dealerID;

	mysqli_stmt_bind_param($stmt, "i", $val1);

	/* Execute the statement */
	if (mysqli_stmt_execute($stmt)) {
		$result = mysqli_stmt_get_result($stmt);
		if (($result)) {
			$num_rows = mysqli_num_rows($result);
			if ($num_rows > 0) {
				$row = mysqli_fetch_assoc($result);
				$businessBankName = $row["Bank_Nm"];
				$businessBankAccountName = $row["Bank_Nm"];
				$businessBankBillingAddress = $row["St_Addr_1_Desc"];
				$businessBankBillingCity = $row["City_Nm"];
				$businessBankBillingState = $row["St_Prov_ID"];
				$businessBankBillingZip = $row["Pstl_Cd"];
				$businessBankRoutingNumber = $row["Bank_Rteg_Nbr"];
				$businessBankAccountNumber = $row["Bank_Acct_Nbr"];
			}
		}
	}




}


// Get list of states from the Enumeration table
$stateResult = selectStates($link);
$stateResultForBank = selectStates($link);


require_once("includes/header.php");

?>

		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
			<div class="container-fluid">
            <?php require_once("includes/common_page_content.php"); ?>
                <div class="row" style="margin-top: 2%;">
                    <div class="col-lg-12">
						<div class="form-group col-md-12">
							<a href="index.php"><span class="badge badge-rounded badge-warning">Done Adding Affiliates - Back to Main</span></a>
						</div>
					</div>
				</div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header text-center">
                                <h4 class="card-title">Dealer Affiliate Fee Setup</h4>
								(Fields with * are required)
                            </div>
							<?php
						if ($errorMessage != "") {
							?>
                                <div class="alert alert-danger alert-dismissible fade show">
									<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
									<strong>Error!</strong> <?php echo $errorMessage; ?>
									<button type="button" class="close h-100" data-dismiss="alert" aria-label="Close"><span><i class="mdi mdi-close"></i></span>
                                    </button>
								</div>
							<?php

					}
					?>
                            <div class="card-body">
                            <div class="basic-form dealer-form">
                                    <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>
                                        <div class="form-row">
											<div class="form-group col-md-6">
												<h5 class="text-primary d-inline">Dealer Name</h5>
												<h4 class="text-muted mb-0"><?php echo $dealerName; ?></h4>
											</div>
											<div class="form-group col-md-6">
												<h5 class="text-primary d-inline">Agreement Date</h5>
												<h4 class="text-muted mb-0"><?php echo $agreementDate; ?></h4>
											</div>
											<div class="form-group col-md-12">
												<h5 class="text-primary d-inline">Dealership Address</h5>
												<h4 class="text-muted mb-0"><?php echo $dealerAddress1; ?> <?php echo $dealerCity . ", " . $dealerStateName . ". " . $dealerZip; ?></h4>
											</div>

											<div class="form-group col-md-12">
												<hr />
											</div>


<?php if (false) { ?>

											<div class="form-group col-md-6">
												<h5 class="text-primary d-inline">Existing Locations</h5>
												<?php
												// Get locations associated with this dealer.
											$query = "SELECT * FROM `Acct` WHERE Acct_ID = " . $dealerID . " OR `Prnt_Acct_ID`=" . $dealerID . " ORDER BY Prnt_Acct_ID ASC";
											$personResult = $link->query($query);

											if (mysqli_num_rows($personResult) > 0) {
												?>
												<select class="form-control default-select" name="dealerLocationID" id="sel1">\n
													<?
													// output data of each row
												$loopCounter = 0;
												while ($row = mysqli_fetch_assoc($personResult)) {
													$loopCounter++;

													?>
													<option value="<?php echo $row["Acct_ID"]; ?>"><?php echo $row["Acct_Nm"]; ?> <?php if ($row["Prnt_Acct_ID"] == "") { ?> (main location)<?php
																																																																																																																																																			} ?></option>\n

													<?php

											}
											?>
												</select>
												<?php

										} else {
											echo "No locations yet defined for this agreement, somehow!";
										}
										?>
												<span style="color:red;<?php if (isset($_SESSION['error_fmessage']) != '') { ?>display:block; <?php
																																																																																																								} else { ?>display:none; <?php
																																																																																																																															} ?>"><?php if (isset($_SESSION['error_fmessage']) != '') {
																																																																																																																																					echo $_SESSION['error_fmessage'];
																																																																																																																																				} ?></span>
											</div>

											<div class="form-group col-md-12">
												<h5 class="text-primary d-inline">Select Type to Enter</h5>
												<br />
												<label class="form-check-label">
													<?php if ($businessBankName != "") { ?>
														<i>(Business Information Already Entered)</i>
													<?php
											} else { ?>
														<input class="form-check-input" type="radio" name="businessPersonalSelector" value="Business" checked>
														Business
													<?php
											} ?>
												</label>
												<br />
												<label class="form-check-label">
													<input class="form-check-input" type="radio" name="businessPersonalSelector" value="Personal" <?php if ($businessBankName != "") { ?>checked<?php
																																																																																																																																																							} ?>>
													Personal
												</label>
											</div>
											<div class="form-group col-md-12">
												<b>Business</b><br />
											</div>
										<?php if ($businessBankName == "") { ?>

											<div class="form-group col-md-6">
												<input type="text" class="form-control Business" name="businessBankName" placeholder="Bank Name">
											</div>
											<div class="form-group col-md-6">
												<input type="text" class="form-control Business" name="businessBankAccountName" placeholder="Name on Bank Account">
											</div>
											<div class="form-group col-md-6">
												<input type="text" class="form-control Business" name="businessBankBillingAddress" placeholder="Bank Address">
											</div>
											<div class="form-group col-md-6">
												<input type="text" class="form-control Business" name="businessBankBillingCity" placeholder="Bank City">
											</div>
											<div class="form-group col-md-6">
												<input type="text" class="form-control Business" name="businessBankBillingState" placeholder="Bank State">
											</div>
											<div class="form-group col-md-6">
												<input type="text" class="form-control Business" name="businessBankBillingZip" placeholder="Bank Postal Code">
											</div>
											<div class="form-group col-md-6">
												<input type="text" class="form-control Business" name="businessBankRoutingNumber" placeholder="Routing Number">
											</div>
											<div class="form-group col-md-6">
												<input type="text" class="form-control Business" name="businessBankAccountNumber" placeholder="Account Number">
											</div>
											<div class="form-group col-md-12">
												&nbsp;
											</div>

										<?php
								} else { ?>
											<div class="form-group col-md-6">
												<h4 class="text-muted mb-0"><?php echo $businessBankName; ?></h4>
												<h5 class="text-primary d-inline">Business Bank Name</h5>
											</div>
											<div class="form-group col-md-6">
												<h4 class="text-muted mb-0"><?php echo $businessBankAccountName; ?></h4>
												<h5 class="text-primary d-inline">Business Bank Account Name</h5>
											</div>
											<div class="form-group col-md-6">
												<input type="text" class="form-control Business" name="businessBankBillingAddress" placeholder="Bank Address">
											</div>
											<div class="form-group col-md-6">
												<input type="text" class="form-control Business" name="businessBankBillingCity" placeholder="Bank City">
											</div>
											<div class="form-group col-md-6">
												<input type="text" class="form-control Business" name="businessBankBillingState" placeholder="Bank State">
											</div>
											<div class="form-group col-md-6">
												<input type="text" class="form-control Business" name="businessBankBillingZip" placeholder="Bank Postal Code">
											</div>
											<div class="form-group col-md-6">
												<h4 class="text-muted mb-0"><?php echo $businessBankRoutingNumber; ?></h4>
												<h5 class="text-primary d-inline">Business Bank Routing Number</h5>
											</div>
											<div class="form-group col-md-6">
												<h4 class="text-muted mb-0"><?php echo $businessBankAccountNumber; ?></h4>
												<h5 class="text-primary d-inline">Business Bank Account Number</h5>
											</div>

										<?php
								} ?>
										<div class="form-group col-md-12">
											<button type="submit" class="btn btn-primary">Submit Business</button>
										</div>
										<div class="form-group col-md-12">
											<hr />
										</div>
<?php
} ?>

									</div>

		                        <form name="dealerAffiliateForm" id="dealerAffiliateForm" method="POST" action="">
									<input type="hidden" name="businessPersonalSelector" value="Personal"/>

                                    <div class="form-row">
										<div class="form-group col-md-12">
											<b>Personal</b><br />
										</div>
                                        <div class="form-group col-md-12">
											<h5 class="text-primary d-inline">Please Select from Dealership Personnel</h5>
<?php
// Get people associated with this dealerID
$query = "SELECT * FROM Usr_Loc ul, Pers p, Email m, Tel t, Dlr_Loc_Dim dld WHERE ul.Dlr_Acct_ID in (
SELECT Acct_ID FROM Acct WHERE Acct_ID=" . $dealerID . " OR Prnt_Acct_ID=" . $dealerID . ") AND
ul.Dlr_Loc_Dim_ID=dld.Dlr_Loc_Dim_ID AND
ul.Pers_ID = p.Pers_ID AND
t.Pers_ID = p.Pers_ID AND
m.Pers_ID = p.Pers_ID AND
p.Soc_Secur_Nbr is NULL
GROUP BY p.Pers_ID
ORDER BY Pers_Last_Nm ASC";
$personResult = $link->query($query);


if ($personResult && mysqli_num_rows($personResult) > 0) {
	?>
												<select class="form-control default-select" name="persID" id="sel1">\n
<?
  // output data of each row
$loopCounter = 0;
while ($row = mysqli_fetch_assoc($personResult)) {
	$loopCounter++;
	$Cntct_Prsn_For_Acct_Flg = $row["Cntct_Prsn_For_Acct_Flg"];

	?>
													<option value="<?php echo $row["Pers_ID"]; ?>" <?php if ($Cntct_Prsn_For_Acct_Flg == "Y") { ?> selected="selected" <?php
																																																																																																																														} ?>><?php echo $row["Pers_Frst_Nm"] . " " . $row["Pers_Last_Nm"]; ?> (<?php echo $row["Email_URL_Desc"]; ?>) (<?php echo $row["Dlr_Loc_Nm"]; ?>)<?php if ($Cntct_Prsn_For_Acct_Flg == "Y") { ?> (primary contact)<?php
																																																																																																																																																																																																																																																																																																																																														} ?></option>\n

<?php

}
?>
												</select>
<?php

} else {
	echo "<br />No people at this dealer need banking info defined.";
}
?>
											<span style="color:red;<?php if (isset($_SESSION['error_fmessage']) != '') { ?>display:block; <?php
																																																																																																							} else { ?>display:none; <?php
																																																																																																																														} ?>"><?php if (isset($_SESSION['error_fmessage']) != '') {
																																																																																																																																				echo $_SESSION['error_fmessage'];
																																																																																																																																			} ?></span>
										</div>


<!---
										<div class="form-group col-md-6">
											<input type="text" class="form-control Personal" name="personFirstName" placeholder="First Name">
										</div>
										<div class="form-group col-md-6">
											<input type="text" class="form-control Personal" name="personLastName" placeholder="Last Name">
										</div>
										<div class="form-group col-md-6">
											<input type="text" class="form-control Personal" name="personEmail" placeholder="Email">
										</div>
										<div class="form-group col-md-6">
											<input type="text" class="form-control Personal" name="personPhone" placeholder="Phone">
										</div>
--->

										<div class="form-group col-md-6">
											<label>Date of Birth *</label>
											<input type="text" class="form-control Personal" name="personDOB" id="personDOB" placeholder="" onfocus="(this.type='date')">
											<span style="color: red;display: none;" id="personDOBE">Please Enter Person Date of Birth!</span>
										</div>
										<div class="form-group col-md-6">
											<label>Social Security Number - (ex. 999-99-9999) *</label>
											<input type="text" class="form-control Personal" name="personSSN" id="personSSN" placeholder="SSN - (999-99-9999)">
											<span style="color: red;display: none;" id="personSSNE">Please Enter Person SSN!</span>
										</div>

										<div class="form-group col-md-6">
											<label>Address 1 </label>
											<input type="text" class="form-control Personal" name="personAddress1" id="personAddress1" placeholder="">
											<span style="color: red;display: none;" id="personAddress1E">Please Enter Person Address!</span>
										</div>
										<div class="form-group col-md-6">
											<label>Address 2 </label>
											<input type="text" class="form-control Personal" name="personAddress2" id="personAddress2" placeholder="">
											<span style="color: red;display: none;" id="personAddress2E">Please Enter Person Address!</span>
										</div>
										<div class="form-group col-md-6">
											<label>City </label>
											<input type="text" class="form-control Personal" name="personCity" id="personCity" placeholder="">
											<span style="color: red;display: none;" id="personCityE">Please Enter Person City</span>
										</div>
										<div class="form-group col-md-6">
											<label>State </label>
											<select class="form-control default-select" name="personState" id="personState">
												<option value="" selected disabled>-- Select State --</option>
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
											<span style="color: red;display: none;" id="personStateE">Please Enter State!</span>
										</div>
										<div class="form-group col-md-6">
											<label>Postal Code </label>
											<input type="text" class="form-control Personal" name="personZip" id="personZip" placeholder="" >
											<span style="color: red;display: none;" id="personZipE">Please Enter Person Postal Code</span>
										</div>
										<div class="form-group col-md-6">
											&nbsp;
										</div>

										<div class="form-group col-md-6">
											<label>Affiliate Fee Percentage Amount</label>
											<input type="text" class="form-control Personal" name="Affl_Fee_Amt" id="personZip" placeholder="" >
											<span style="color: red;display: none;" id="Affl_Fee_AmtE">Please Enter Affiliate Fee Amount</span>
										</div>

										<div class="form-group col-md-6">
											&nbsp;
										</div>

										<div class="form-group col-md-6">
											<label>Bank Name *</label>
											<input type="text" class="form-control Personal" name="personBankName" id="personBankName" placeholder="">
											<span style="color: red;display: none;" id="personBankNameE">Please Enter Bank Name!</span>
										</div>
										<div class="form-group col-md-6">
											<label>Name on Bank Account *</label>
											<input type="text" class="form-control Personal" name="personBankAccountName" id="personBankAccountName" placeholder="">
											<span style="color: red;display: none;" id="personBankAccountNameE">Please Enter Bank Account Name!</span>
										</div>
										<div class="form-group col-md-6">
											<label>Bank Account Address *</label>
											<input type="text" class="form-control Personal" name="personBankBillingAddress" id="personBankBillingAddress" placeholder="">
											<span style="color: red;display: none;" id="personBankBillingAddressE">Please Enter Bank Account Address!</span>
										</div>
										<div class="form-group col-md-6">
											<label>Bank Account City *</label>
											<input type="text" class="form-control Personal" name="personBankBillingCity" id="personBankBillingCity" placeholder="">
											<span style="color: red;display: none;" id="personBankBillingCityE">Please Enter Bank Account City!</span>
										</div>
										<div class="form-group col-md-6">
											<label>Bank State *</label>
											<select class="form-control default-select" name="personBankBillingState" id="personBankBillingState">
												<option value="" selected disabled>-- Select Bank State --</option>
											<?php
										if (mysqli_num_rows($stateResultForBank) > 0) {
												// output data of each row
											$loopCounter = 0;
											while ($row = mysqli_fetch_assoc($stateResultForBank)) {
												$loopCounter++;
												?>
													<option value=<?php echo $row["St_Prov_ID"] ?>><?php echo $row["St_Prov_Nm"]; ?></option>
											<?php
									}
								} ?>
											</select>
											<span style="color: red;display: none;" id="personBankBillingStateE">Please Enter Bank Billing State!</span>
										</div>
										<div class="form-group col-md-6">
											<label>Bank Postal Code *</label>
											<input type="text" class="form-control Personal" name="personBankBillingZip" id="personBankBillingZip" placeholder="">
											<span style="color: red;display: none;" id="personBankBillingZipE">Please Enter Bank Postal Code!</span>
										</div>
										<div class="form-group col-md-6">
											<label>Bank Routing Number *</label>
											<input type="text" class="form-control Personal" name="personRoutingNumber" id="personRoutingNumber" placeholder="">
											<span style="color: red;display: none;" id="personRoutingNumberE">Please Enter Routing Number!</span>
										</div>
										<div class="form-group col-md-6">
											<label>Bank Account Number *</label>
											<input type="text" class="form-control Personal" name="personAccountNumber" id="personAccountNumber" placeholder="">
											<span style="color: red;display: none;" id="personAccountNumberE">Please Enter Account Number!</span>
										</div>
										<div class="form-group col-md-6">
											<label>Notes</label>
											<textarea class="form-control" name="notesField" rows="5" cols="60"></textarea>
										</div>
										<div class="form-group col-md-6">
											&nbsp;
										</div>
										<div class="form-group col-md-6">
											<button type="button" class="btn btn-primary" id="dealAffiliateSubmit" name="dealAffiliateSubmit">Submit</button>
										</div>
										<div class="form-group col-md-6">
											<a href="index.php"><span class="badge badge-rounded badge-warning" style="margin-top: 3%;">Done Adding Affiliates - Back to Main</span></a>
										</div>

                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- row -->

                <!-- <div class="row">
                    <div class="col-lg-12">
						<div class="form-group col-md-12">
							<a href="index.php"><span class="badge badge-rounded badge-warning">Done Adding Affiliates - Back to Main</span></a>
						</div>
					</div>
				</div> -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Existing Affiliates Defined for Dealer</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-responsive-md">
                                        <thead>
                                            <tr>
                                                <th class="width80">#</th>
                                                <th>Personal First Name</th>
                                                <th>Personal Last Name</th>
                                                <th>Location Name</th>
                                                <th>Bank Name</th>
                                                <th>Affiliate Fee %</th>
                                                <th>W-9</th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php

// Get people associated with this dealerID
$query = "SELECT * FROM Usr_Loc ul, Pers p, Bank_Dim b, Dlr_Loc_Dim dld WHERE ul.Dlr_Acct_ID in (
SELECT Acct_ID FROM Acct WHERE Acct_ID=" . $dealerID . " OR Prnt_Acct_ID=" . $dealerID . ") AND
ul.Dlr_Loc_Dim_ID=dld.Dlr_Loc_Dim_ID AND
ul.Pers_ID = p.Pers_ID AND
p.Pers_ID = b.Pers_ID
GROUP BY p.Pers_ID
ORDER BY Pers_Last_Nm ASC";

//$query = "SELECT * FROM Pers p, Bank_Dim b WHERE p.Acct_ID=".$dealerID." AND p.Pers_ID = b.Pers_ID ORDER BY p.Pers_ID ASC";
$personResult = $link->query($query);

if (mysqli_num_rows($personResult) > 0) {
  // output data of each row
	$loopCounter = 0;
	while ($row = mysqli_fetch_assoc($personResult)) {
		$loopCounter++;
		?>
<tr>
	<td><?php echo $row["Pers_ID"] ?></td>
	<td><?php echo $row["Pers_Frst_Nm"]; ?></td>
	<td><?php echo $row["Pers_Last_Nm"]; ?></td>
	<td><?php echo $row["Dlr_Loc_Nm"]; ?></td>
	<td><?php echo $row["Bank_Nm"]; ?></td>
	<td><?php echo $row["Affl_Fee_Amt"]; ?>%</td>
	<td><?php if ($row["w9_signature"] == "") { ?><a href="dealer_affiliate_fee_w9.php?persID=<?php echo $row["Pers_ID"]; ?>">Sign W-9</a><?php
																																																																																																																																					} else { ?>W9 Is Complete<?php
																																																																																																																																																												} ?></td>
</tr>

<?php

}
} else {
	?>
<tr>
	<td colspan="5">No people found, yet.</td>
</tr>

<?php

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

	<!-- Dashboard 1 -->
    <script src="./js/custom.min.js"></script>
	<script src="./js/deznav-init.js"></script>

	<script src="./js/custom-validation.js"></script>

	<script>
      jQuery('input').on('keypress', function(e) {
        if(e.which === 13)
		{
			var flag1 = 0;
    var flag2 = 0;
    var flag3 = 0;
    var flag4 = 0;
    var flag5 = 0;
    var flag6 = 0;
    var flag7 = 0;
    var flag8 = 0;
    var flag9 = 0;
    var flag10 = 0;

    if ($("#personDOB").val() == "") {
      $("#personDOB").focus();
      $("#personDOBE").css("display", "block");
    } else {
      $("#personDOBE").css("display", "none");
      flag1 = 1;
    }

    if ($("#personSSN").val() == "") {
      $("#personSSN").focus();
      $("#personSSNE").css("display", "block");
    } else {
      $("#personSSNE").css("display", "none");
      flag2 = 1;
    }

    if ($("#personBankName").val() == "") {
      $("#personBankName").focus();
      $("#personBankNameE").css("display", "block");
    } else {
      $("#personBankNameE").css("display", "none");
      flag3 = 1;
    }

    if ($("#personBankAccountName").val() == "") {
      $("#personBankAccountName").focus();
      $("#personBankAccountNameE").css("display", "block");
    } else {
      $("#personBankAccountNameE").css("display", "none");
      flag4 = 1;
    }

    if ($("#personBankBillingAddress").val() == "") {
      $("#personBankBillingAddress").focus();
      $("#personBankBillingAddressE").css("display", "block");
    } else {
      $("#personBankBillingAddressE").css("display", "none");
      flag5 = 1;
    }

    if ($("#personBankBillingCity").val() == "") {
      $("#personBankBillingCity").focus();
      $("#personBankBillingCityE").css("display", "block");
    } else {
      $("#personBankBillingCityE").css("display", "none");
      flag6 = 1;
    }

    if (
      $("#personBankBillingState").val() == "" ||
      $("#personBankBillingState").val() == null
    ) {
      $("#personBankBillingState").focus();
      $("#personBankBillingStateE").css("display", "block");
    } else {
      $("#personBankBillingStateE").css("display", "none");
      flag7 = 1;
    }

    if ($("#personBankBillingZip").val() == "") {
      $("#personBankBillingZip").focus();
      $("#personBankBillingZipE").css("display", "block");
    } else {
      $("#personBankBillingZipE").css("display", "none");
      flag8 = 1;
    }

    if ($("#personRoutingNumber").val() == "") {
      $("#personRoutingNumber").focus();
      $("#personRoutingNumberE").css("display", "block");
    } else {
      $("#personRoutingNumberE").css("display", "none");
      flag9 = 1;
    }

    if ($("#personAccountNumber").val() == "") {
      $("#personAccountNumber").focus();
      $("#personAccountNumberE").css("display", "block");
    } else {
      $("#personAccountNumberE").css("display", "none");
      flag10 = 1;
    }
        }
      });
</script>

</body>
</html>