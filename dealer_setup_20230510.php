<?php
//
// File: dealer_setup.php
// Author: Charles Parry
// Date: 5/13/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Dealer Setup";
$pageTitle = "Dealer Setup";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

// Email function
require_once "lib/emailHelper.php";


// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');

// Variables.
$dealerID = "";
$agreementDate = "";
$dealerName = "";
$dealerAddress1 = "";
$dealerAddress2 = "";
$dealerCity = "";
$dealerState = "";
$dealerZip = "";
$dealerLocationID = "";
$Dlr_Loc_Dim_ID = "";

$personFirstName = "";
$personLastName = "";
$personEmail = "";
$personPhone = "";

$notesField = "";

$form_err = "";

$fromEmail = "N";

session_start();

// See if we have the 'FromEmail' flag in the URL
if ((isset($_GET["FromEmail"])) && ($_GET["FromEmail"] == "true")) {
	$fromEmail = "Y";
}


// Make sure a dealer is currently logged in, or go back to the Agreement
if (!(isset($_SESSION["userType"])) || !($_SESSION["userType"] == "dealer")) {
	// Support the pass through from login back to this page
	if($fromEmail=="Y"){
		header("location: index.php?crumb=dealer_signup");
		exit;
	}else{
		header("location: index.php");
		exit;
	}
}

// Get a dealer ID from session.
if (!(isset($_SESSION["id"]))) {
    header("location: index.php");
    exit;
} else {
    $dealerID = $_SESSION["id"];
    $adminID = $_SESSION["admin_id"];
}



// Process form data when form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION["personFirstName"] = $_POST["personFirstName"];
    $_SESSION["personLastName"] = $_POST["personLastName"];
    $_SESSION["personEmail"] = $_POST["personEmail"];
    $_SESSION["personPhone"] = $_POST["personPhone"];





	// Get remaining form fields
	// Removing this strict filter on first and last name.  cparry 7/11/2022.
	/*
    if(!empty(trim($_POST["personFirstName"]))){
        $query = "SELECT * FROM Pers WHERE Pers_Frst_Nm='".$_POST["personFirstName"]."' AND Acct_ID=".$dealerID;
	    $result = mysqli_query($link,$query);
        if (mysqli_num_rows($result)>0){
            $_SESSION["error_fmessage"] = "Person First Name Already Exist...dealerid=".$dealerID;
        } else {
            unset($_SESSION["error_fmessage"]);
            $personFirstName = trim($_POST["personFirstName"]);
        }
    }else{
    	$_SESSION["error_message"] = "No First Name found, please try again.";
		header("location: dealer_setup.php");
		exit;
	}*/

	/*
    if(!empty(trim($_POST["personLastName"]))){
        $personLastName = trim($_POST["personLastName"]);
        $query = "SELECT * FROM Pers WHERE Pers_Last_Nm='".$_POST["personLastName"]."' AND Acct_ID=".$dealerID;
	    $result = mysqli_query($link,$query);
        if (mysqli_num_rows($result)>0){
            $_SESSION["error_lmessage"] = "Person Last Name Already Exist...";
        } else {
            unset($_SESSION["error_lmessage"]);
            $personLastName = trim($_POST["personLastName"]);
        }
    }else{
    	$_SESSION["error_message"] = "No Last Name found, please try again.";
		header("location: dealer_setup.php");
		exit;
	}*/

    if (!empty(trim($_POST["personEmail"]))) {
        $personEmail = trim($_POST["personEmail"]);
        $query = "SELECT * FROM Email WHERE Email_URL_Desc='" . $_POST["personEmail"] . "' AND Acct_ID=" . $dealerID;
        $result = mysqli_query($link, $query);
        if (mysqli_num_rows($result) > 0) {
            $_SESSION["error_emessage"] = "Person Email Already Exist...";
        } else {
            unset($_SESSION["error_emessage"]);
            $personEmail = trim($_POST["personEmail"]);
        }
    }
    /*else{
    	$_SESSION["error_message"] = "No Email found, please try again.";
		header("location: dealer_setup.php");
		exit;
	}*/

    if (!empty(trim($_POST["personFirstName"]))) {
        $personFirstName = trim($_POST["personFirstName"]);
    }

    if (!empty(trim($_POST["personLastName"]))) {
        $personLastName = trim($_POST["personLastName"]);
    }

    if (!empty(trim($_POST["personPhone"]))) {
        $personPhone = trim($_POST["personPhone"]);
    }

    if (!empty(trim($_POST["notesField"]))) {
        $notesField = trim($_POST["notesField"]);
    }

    if (!empty(trim($_POST["dealerLocationID"]))) {
        $dealerLocationID = trim($_POST["dealerLocationID"]);
    }


    if (isset($_SESSION["error_fmessage"]) != '' || isset($_SESSION["error_lmessage"]) != '' || isset($_SESSION["error_emessage"]) != '') {
        header("location: dealer_setup.php");
        exit;
    }

    unset($_SESSION["personFirstName"]);
    unset($_SESSION["personLastName"]);
    unset($_SESSION["personEmail"]);
    unset($_SESSION["personPhone"]);
    $dealerID = $_SESSION["id"];


	// Update tracker for dealer forms, to indicate the form is signed
    $stmt = mysqli_prepare($link, "UPDATE Dealer_Progress SET Dealer_Setup_Complete='Y' WHERE Acct_ID=?");

	/* Bind variables to parameters */
    $val1 = $dealerID;

    mysqli_stmt_bind_param($stmt, "i", $val1);

	/* Execute the statement */
    $result = mysqli_stmt_execute($stmt);


	/* Prepare an insert statement to create an Pers entry for the contact person */
    $stmt = mysqli_prepare($link, "INSERT INTO Pers (Acct_ID,Pers_Full_Nm,Pers_Last_Nm,Pers_Frst_Nm) VALUES (?,?,?,?)");

	/* Bind variables to parameters */
    $val1 = $dealerID;
    $val2 = $personFirstName . " " . $personLastName;
    $val3 = $personLastName;
    $val4 = $personFirstName;

    mysqli_stmt_bind_param($stmt, "isss", $val1, $val2, $val3, $val4);

	/* Execute the statement */
    $result = mysqli_stmt_execute($stmt);

	// Get the per Pers_ID of the primary contact person.
    $primary_Contact_Person_id = mysqli_insert_id($link);


	/* Create an entry in Users for this new user. */
    $initialPassword = password_hash("PASSWORD", PASSWORD_DEFAULT);

	$dealerAgentRoleID = 2;
    $stmt = mysqli_prepare($link, "INSERT INTO Users (Acct_ID,Pers_ID,Agent_ID,Role_ID,username,password,mustResetPassword,createdDate) VALUES (?,?,?,$dealerAgentRoleID,?,?,'Y',NOW())");

	/* Bind variables to parameters */
    $val1 = $dealerName;
    mysqli_stmt_bind_param($stmt, "iiiss", $dealerID, $primary_Contact_Person_id, $primary_Contact_Person_id, $personEmail, $initialPassword);

	/* Execute the statement */
    $result = mysqli_stmt_execute($stmt);

	// Get the per User_ID of the primary contact person.
    $primary_Contact_User_id = mysqli_insert_id($link);


	// Look up the Dlr_Loc_Dim_ID
    $query = "SELECT * FROM Dlr_Loc_Dim WHERE Dlr_Acct_ID=" . $dealerLocationID . ";";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $Dlr_Loc_Dim_ID = $row["Dlr_Loc_Dim_ID"];

	// Create a new Usr_Loc entry
    $stmt = mysqli_prepare($link, "INSERT INTO Usr_Loc (Dlr_Acct_ID,Dlr_Loc_Dim_ID,Usr_ID,Pers_ID) VALUES (?,?,?,?)");

	/* Bind variables to parameters */
    $val1 = $dealerLocationID;
    $val2 = $Dlr_Loc_Dim_ID;
    $val3 = $primary_Contact_User_id;
    $val4 = $primary_Contact_Person_id;

    mysqli_stmt_bind_param($stmt, "iiii", $val1, $val2, $val3, $val4);

	/* Execute the statement */
    $result = mysqli_stmt_execute($stmt);




	// Send mail to this new user
    $to = $personEmail;
    $subject = "Welcome - New Vital Trends Account";
    $txt = "You have been signed up for a Vital Trends user account!  Please click here to <a href='https://portal.vitaltrendsusa.com'>log in</a>.\n";
	$txt .= "Your user name is: " . $personEmail . "\n";
    $txt .= "Your initial password is: PASSWORD \n";
    $txt .= "Please note, you will need to change your password upon first login.\n\n";
    $txt .= "Thank you!\nVital Trends team";
    $headers = "From: admin@vitaltrendsusa.com" . "\r\n" .
        "CC: cparry@gmail.com";

    //mail($to, $subject, $txt, $headers);


	$emailResult = sendEmail($to, $personFirstName, $personLastName, $subject, $txt);




	/* Prepare an insert statement to create a Tel entry for the contact person phone */
    $stmt = mysqli_prepare($link, "INSERT INTO Tel (Acct_ID,Pers_ID,Tel_Nbr,Tel_Type_Cd,Tel_Type_Desc,Prim_Tel_Flg) VALUES (?,?,?,'Work','Work','N')");

	/* Bind variables to parameters */
    $val1 = $dealerID;
    $val2 = $primary_Contact_Person_id;
    $val3 = $personPhone;

    mysqli_stmt_bind_param($stmt, "iis", $val1, $val2, $val3);

	/* Execute the statement */
    $result = mysqli_stmt_execute($stmt);


	/* Prepare an insert statement to create an Email entry for the contact person email */
    $stmt = mysqli_prepare($link, "INSERT INTO Email (Acct_ID,Pers_ID,Email_URL_Desc,Email_Type_Cd,Email_Type_Desc,Email_Prim_Flg) VALUES (?,?,?,'Work','Work','N')");

	/* Bind variables to parameters */
    $val1 = $dealerID;
    $val2 = $primary_Contact_Person_id;
    $val3 = $personEmail;

    mysqli_stmt_bind_param($stmt, "iis", $val1, $val2, $val3);

	/* Execute the statement */
    $result = mysqli_stmt_execute($stmt);


	/* Prepare an insert statement to create an Note entry for the note field */
    $stmt = mysqli_prepare($link, "INSERT INTO Note (Acct_ID,Pers_ID,Note_Desc,Note_Type) VALUES (?,?,?,'setup')");

	/* Bind variables to parameters */
    $val1 = $dealerID;
    $val2 = $primary_Contact_Person_id;
    $val3 = $notesField;

    mysqli_stmt_bind_param($stmt, "iis", $val1, $val2, $val3);

	/* Execute the statement */
    $result = mysqli_stmt_execute($stmt);

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
    $query = "SELECT * FROM St_Prov WHERE St_Prov_ID=" . $dealerState;
    $result = $link->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $dealerStateName = $row["St_Prov_ISO_2_Cd"];

    } else {
        $dealerStateName = "";
        $generatePdf = false;
    }

    // Get the dealer info
    $query = "SELECT * FROM Acct WHERE Acct_ID=" . $dealerID;
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
    if ($result) {
        $row = $result->fetch_assoc();
        $agreementDate = $row["Contract_Date"];

    } else {
        $agreementDate = "";
        $generatePdf = false;
    }

    if ($generatePdf) {

         // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

       // set document information
        $pdf->setCreator(PDF_CREATOR);
        $pdf->setAuthor('SETUP');
        $pdf->setTitle('SETUP');
        $pdf->setSubject('SETUP');
        $pdf->setKeywords('SETUP, Vital, Data, Set, Guide');

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
        $dealerAddess = $dealerAddress1 . " " . $dealerCity . ", " . $dealerStateName . ". " . $dealerZip;

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
                        <td class="fontClass" style="width:70%;">Address: ' . $dealerAddess . '</td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

    <br/><br/>

    <table cellspacing="0" cellpadding="5" border="1" style="border-color:grey;">
        <tr>
            <td style="width:100%;">
                <table border="0">
                    <tr>
                      <td class="fontClass" style="width:100%;padding-left:40px;">User Name: ' . $personFirstName . ' ' . $personLastName . '</td>
                    </tr>

                </table>
            </td>
        </tr>

        <tr>
            <td style="width:100%;">
                <table border="0">
                    <tr>
                        <td class="fontClass" style="width:70%;">User Email: ' . $personEmail . '</td>
                        <td class="fontClass" style="width:30%;">User Phone#: ' . $personPhone . '</td>
                    </tr>

                </table>
            </td>
        </tr>
        <tr>
            <td style="width:100%;">
                <table border="0">
                    <tr>
                       <td class="fontClass" style="width:100%;padding-left:40px;">Notes: ' . $notesField . '</td>
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
                        <td class="fontClass" style="width:70%;margin-left:-15px;">Retailer Name: ' . $dealerName . '</td>

                        <td class="fontClass" style="width:30%;border-left:none;">Date: ' . $assignDate . '</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br><br>
    <table cellspacing="0" cellpadding="5" border="1" style="border-color:gray;">
        <tr>
            <td style="width:100%;">
                <table border="0">
                    <tr>
                      <td class="fontClass" style="width:100%;">To Be Completed by TrüNorth Global™</td>
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
    <br/><br/>
    <table>
        <tr>
            <td class="fontClass" style="width:30%;"></td>
            <td class="fontClass" style="width:60%;"></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="fontClass" style="width:70%;">TrüNorth Global™ Signature:__________________________________________________________</td>
            <td class="fontClass" style="width:30%;">Date:' . $agreeDate . '__________________</td>
        </tr>
    </table>
';

//<p class="fontClass">TrüNorth Global™ Signature:__________________________________________________________________Date:'.$agreeDate.'_____________________</p>
// Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.

        $pdfFileName = str_replace(" ", "_", $personFirstName) . '_' . str_replace(" ", "_", $personLastName) . '_' . time() . '.pdf';

        $pdf->Output(__DIR__ . '/uploads/dealer_setup_pdf/' . $pdfFileName, 'F');

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
//  Set type=2 for 'dealer W9'.
        $stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
                            Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,5,'Dealer Setup',NOW())");



/* Bind variables to parameters */
        $val1 = $dealerID;
        $val2 = $primary_Contact_Person_id;
        $val3 = $adminID;
        $val4 = $contract_dim_ID;
        $val5 = '/uploads/dealer_setup_pdf/' . $pdfFileName;

        mysqli_stmt_bind_param($stmt, "iiiis", $val1, $val2, $val3, $val4, $val5);

/* Execute the statement */
        $result = mysqli_stmt_execute($stmt);

//============================================================+
// END OF FILE
//============================================================+

// End PDF Code here

// // API Call to TruNorth
    //     $url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/retailers/create-retailer?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";

    //     $curl = curl_init($url);
    //     curl_setopt($curl, CURLOPT_URL, $url);
    //     curl_setopt($curl, CURLOPT_POST, true);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    //     $headers = array(
    //         "Accept: application/json",
    //         "Content-Type: application/json",
    //     );
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    //     if (file_exists('uploads/dealer_setup_pdf/' . $pdfFileName)) {
    //         $b64PDFDoc = base64_encode(file_get_contents('uploads/dealer_setup_pdf/' . $pdfFileName));
    //     } else {
    //         $b64PDFDoc = base64_encode(file_get_contents("files/TEST_AGREEMENT_PDF.pdf"));
    //     }

    //     // get business email
    //     // Select * from Email where acct_id = 1306 AND Pers_ID=425 AND Email_Prim_Flg = 'Y';

    //     // Select Email for the dealer.
    //     $emailResult = selectEmailByAcct($link, $dealerID, "Y", "Work");
    //     if ($emailResult) {
    //         $row = $emailResult->fetch_assoc();
    //         $dealerEmail = $row["Email_URL_Desc"];
    //     } else {
    //         $dealerEmail = "";
    //     }
    //     // Select Tel for the dealer.
    //     $telResult = selectTelByAcct($link, $dealerID, "Y", "Work");
    //     if ($telResult) {
    //         $row = $telResult->fetch_assoc();
    //         $dealerPhone = $row["Tel_Nbr"];
    //     } else {
    //         $dealerPhone = "";
    //     }


    //     $data = "{
    //     \"retailerName\": \"$dealerName\",
    //     \"retailerEmail\": \"$dealerEmail\",
    //     \"retailerPhone\": \"$dealerPhone\",
    //     \"retailerAddress\": {
    //     \"street\": \"$dealerAddress1\",
    //     \"street2\": \"$dealerAddress2\",
    //     \"city\": \"$dealerCity\",
    //     \"state\": \"$dealerState\",
    //     \"zip\": \"$dealerZip\",
    //     \"country\": \"US\"
    //     },
    //     \"defaultCurrency\": \"USD\",
    //     \"validationMethod\": \"ECA Only\",
    //     \"files\" : [{\"type\" : \"vtAgreement\", \"fileBytes\" : \"$b64PDFDoc\"}]
    //     }";

    //     curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    //     $resp = curl_exec($curl);
    //     curl_close($curl);
    //     //var_dump($resp);

    //     $json = json_decode($resp, true);
    //     // print_r($json);

    //     if (isset($json) && array_key_exists("success", $json)) {
    //         $responseStatus = $json["success"];
    //     } else {
    //         $responseStatus = 0;
    //     }

    //     if ($responseStatus == 1) {
    //         $arNumber = $json["data"]["arNumber"];
    //         $apiMessage = $json["message"];

    //         // Save the returned retailer number to the CNTRCT_DIM table.
    //         $stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim SET Assign_Rtlr_Nbr=? WHERE Cntrct_Dim_ID=?");

    //         /* Bind variables to parameters */
    //         $val1 = $arNumber;
    //         $val2 = $contract_dim_ID;

    //         mysqli_stmt_bind_param($stmt, "si", $val1, $val2);

    //         /* Execute the statement */
    //         $result = mysqli_stmt_execute($stmt);

    //     } else {
    //         $arNumber = "FAILED";
    //         $apiMessage = "NONE";
    //         $responseStatus = 0;
    //     }


    //     // Create a new API_Data entry to track activity
    //     $stmt = mysqli_prepare($link, "INSERT INTO API_Responses (Acct_ID,statusCode, dataReturned, arNumber, messageText, sentJSON, returnedJSON, createdDate) VALUES (?,?,?,?,?,?,?,NOW())");

    //     /* Bind variables to parameters */
    //     $val1 = $dealerID;
    //     $val2 = $responseStatus;
    //     $val3 = $arNumber;
    //     $val4 = $arNumber;
    //     $val5 = $apiMessage;
    //     $val6 = $data;
    //     $val7 = $resp;

    //     mysqli_stmt_bind_param($stmt, "issssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7);

    //     /* Execute the statement */
    //     $result = mysqli_stmt_execute($stmt);


    // // End API section


    }
    // Redirect to next form

    header("location: dealer_setup.php");

    exit;


    ?>
<?php

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
    $query = "SELECT * FROM Acct WHERE Acct_ID=" . $dealerID;
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

}



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
							<a href="dealer_addendum.php"><span class="badge badge-rounded badge-warning">Done Adding Users</span></a>
						</div>
					</div>
				</div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header text-center">
                                <h4 class="card-title">Dealer Setup</h4>
                            </div>
                            <div class="card-body">
                                <div class="basic-form dealer-form">
                                    <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>
                                    <form name="dealerForm" id="dealer_setup" method="POST" action="">
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
                                            <div class="form-group col-md-6">
												&nbsp;
                                            </div>
                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">New User First Name</h5>
                                                <input type="text" class="form-control" name="personFirstName" placeholder="" required value="<?php if (isset($_SESSION['personFirstName']) != '') {
                                                                                                                                                    echo $_SESSION['personFirstName'];
                                                                                                                                                } else {
                                                                                                                                                    echo $personFirstName;
                                                                                                                                                } ?>">
                                                <span style="color:red;<?php if (isset($_SESSION['error_fmessage']) != '') { ?>display:block; <?php
                                                                                                                                            } else { ?>display:none; <?php
                                                                                                                                                                } ?>"><?php if (isset($_SESSION['error_fmessage']) != '') {
                                                                                                                                                                            echo $_SESSION['error_fmessage'];
                                                                                                                                                                        } ?></span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">New User Last Name</h5>
                                                <input type="text" class="form-control" name="personLastName" placeholder="" required value="<?php if (isset($_SESSION['personLastName']) != '') {
                                                                                                                                                echo $_SESSION['personLastName'];
                                                                                                                                            } else {
                                                                                                                                                echo $personLastName;
                                                                                                                                            } ?>">
                                                <span style="color:red;<?php if (isset($_SESSION['error_lmessage']) != '') { ?>display:block; <?php
                                                                                                                                            } else { ?>display:none; <?php
                                                                                                                                                                } ?>"><?php if (isset($_SESSION['error_lmessage']) != '') {
                                                                                                                                                                            echo $_SESSION['error_lmessage'];
                                                                                                                                                                        } ?></span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">New User Email</h5>
                                                <input type="text" class="form-control" name="personEmail" placeholder="" required value="<?php if (isset($_SESSION['personEmail']) != '') {
                                                                                                                                                echo $_SESSION['personEmail'];
                                                                                                                                            } else {
                                                                                                                                                echo $personEmail;
                                                                                                                                            } ?>">
                                                <span style="color:red;<?php if (isset($_SESSION['error_emessage']) != '') { ?>display:block; <?php
                                                                                                                                            } else { ?>display:none; <?php
                                                                                                                                                                } ?>"><?php if (isset($_SESSION['error_emessage']) != '') {
                                                                                                                                                                            echo $_SESSION['error_emessage'];
                                                                                                                                                                        } ?></span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">New User Phone</h5>
                                                <input type="text" class="form-control" name="personPhone" placeholder="" required value="<?php if (isset($_SESSION['personPhone']) != '') {
                                                                                                                                                echo $_SESSION['personPhone'];
                                                                                                                                            } else {
                                                                                                                                                echo $personPhone;
                                                                                                                                            } ?>">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Notes</label>
                                                <textarea class="form-control" name="notesField" rows="5" cols="60"></textarea>
                                            </div>
                                            <div class="form-group col-md-6">
												&nbsp;
                                            </div>
                                            <div class="form-group col-md-6">
                                                <button type="button" id="dealer_setup_submit" class="btn btn-primary">Submit</button>
                                            </div>
                                            <div class="form-group col-md-6">
                                            <a href="dealer_addendum.php"><span class="badge badge-rounded badge-warning">Done Adding Users</span></a>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- row -->

                <div class="row">
                    <div class="col-lg-12">
						<div class="form-group col-md-12">

						</div>
					</div>
				</div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Existing Users for Dealer</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-responsive-md">
                                        <thead>
                                            <tr>
                                                <th class="width80">#</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
												<th>Location</th>
                                                <th>Phone</th>
                                                <th>Email</th>
                                                <th>Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php



// Get people associated with this dealerID
// $query = "SELECT * FROM Pers p, Email m, Tel t WHERE p.Acct_ID=".$dealerID." AND t.Pers_ID = p.Pers_ID AND
// m.Pers_ID = p.Pers_ID ORDER BY Pers_Last_Nm ASC";

$query = "SELECT *, (CASE WHEN Cntct_Prsn_For_Acct_Flg = 'Y' THEN 0 ELSE 1 END) as Cntct_Prsn_For_Acct_Flg_Order
FROM `Usr_Loc` u, `Acct` a, Pers p, Email m, Tel t WHERE
(a.Acct_ID = " . $dealerID . " OR a.Prnt_Acct_ID=" . $dealerID . ") AND
a.Acct_ID = u.`Dlr_Acct_ID` AND
u.Pers_ID = p.Pers_ID AND
p.Pers_ID = t.Pers_ID AND
m.Pers_ID = p.Pers_ID
ORDER BY Cntct_Prsn_For_Acct_Flg_Order,Acct_Nm, Pers_Last_Nm ASC;";





// $query = "SELECT p.Pers_Frst_Nm,p.Pers_Last_Nm,a.Acct_Nm,t.Tel_Nbr,m.Email_URL_Desc, p.Pers_Ttl_Nm,p.Cntct_Prsn_For_Acct_Flg
// FROM `Usr_Loc` u, `Acct` a, Pers p, Email m, Tel t WHERE (a.Acct_ID = ".$dealerID." OR a.Prnt_Acct_ID=".$dealerID.")
// AND a.Acct_ID = u.`Dlr_Acct_ID` AND u.Pers_ID = p.Pers_ID AND p.Pers_ID = t.Pers_ID AND m.Pers_ID = p.Pers_ID
// UNION SELECT d.Frst_Nm as Pers_Frst_Nm, d.Last_Nm as Pers_Last_Nm, d.Location as Acct_Nm, d.Phone as Tel_Nbr,
// d.Email as Email_URL_Desc, p.Pers_Ttl_Nm,p.Cntct_Prsn_For_Acct_Flg FROM Dealer_Agent as d, Pers p, Users as u
// Where d.Dealer_ID = ".$dealerID." AND u.Agent_ID = d.delaerAgent_ID AND u.Pers_ID = p.Pers_ID";





$personResult = $link->query($query);

if (mysqli_num_rows($personResult) > 0) {
  // output data of each row
    $loopCounter = 0;
    while ($row = mysqli_fetch_assoc($personResult)) {
        $loopCounter++;
        ?>
<tr>
	<td><?php echo $loopCounter; ?></td>
	<td><?php echo $row["Pers_Frst_Nm"]; ?></td>
	<td><?php echo $row["Pers_Last_Nm"]; ?></td>
	<td><?php echo $row["Acct_Nm"]; ?></td>
	<td><?php echo $row["Tel_Nbr"]; ?></td>
	<td><?php echo $row["Email_URL_Desc"]; ?></td>
	<td>
<?php
if ($row["Pers_Ttl_Nm"] == "Accounts Payable Contact") {
    echo "Accounts Payable Contact";
} else if ($row["Cntct_Prsn_For_Acct_Flg"] == "Y") {
    echo "Primary";
} else {
    echo "standard";
}


?>
	</td>
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

</body>
</html>