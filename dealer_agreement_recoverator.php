<?php
// File: dealer_agreement_recoverator.php
// Author: Charles Parry
// Date: 8/26/2023
// Purpose: regenerate a dealer agreement after the record has been created

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
$wholesale_flg = "N";

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

// Get the AdminID so we can track sales agent for this dealer agreement.
$adminID = $_SESSION["admin_id"];



//echo "turned off for now";
//die();



// Go straight into this section, which is the old POST handler.
//  We want to load relevant dealer info from the DB then run the PDF process.
//if ($_SERVER["REQUEST_METHOD"] == "POST") {
if (true) {

	// To support the notion of a 'dealer service account' we allow a dealer
	//  to log on with a limited admin account and create this initial agreement document.
	//  However, before processing the document, we want to basically log out that dealer and
	//  log in Josh as the sales agent default.
	$adminUsername = $_SESSION["admin_username"];

	// What Acct_ID are we regenerating?
	//  This could be a param in the future
	//  Cascade Fire Equipment
	//$acctID = 1429;

	// Axix
	$acctID = 1500;
	$last_id = $acctID;
    $filename = ""; // for existing signature

	// Get data from Acct, Cntrct, Cntrct_Dim
	$query = "SELECT * FROM Acct a, Cntrct c, Cntrct_Dim cd WHERE a.Acct_ID=".$acctID." AND
	           c.Mfr_Acct_ID=a.acct_ID AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
	$result = $link->query($query);
	$acctDataRow = $result->fetch_assoc();

	// From Cntrct_Dim
	$retailerName = $acctDataRow["Cntrct_Signer_Nm"];
	$retailerTitle = $acctDataRow["Cntrct_Signer_Ttl"];
	$fileName = $acctDataRow["Cntrct_Signature"];
	$imageDataBase30 = $acctDataRow["Cntrct_Signature_Base30"];
	$agreementDate = $acctDataRow["Contract_Date"];
	$contract_dim_ID = $acctDataRow["Cntrct_Dim_ID"];

	// From Acct
	$dealerName = $acctDataRow["Acct_Nm"];
	$federalTaxID = $acctDataRow["Fed_Tax_Number"];
	$duns = $acctDataRow["Duns_Number"];
	$multipleLocations = $acctDataRow["Multiple_Locations"];
	$individualBilling = $acctDataRow["Individual_Billing"];
	$adminID = $acctDataRow["Sls_Agnt_ID"];
	$Dlr_Affiliate_Fee_Pct = $acctDataRow["Dlr_Affiliate_Fee_Pct"];
	$adminID = $acctDataRow["Sls_Agnt_ID"];

	if ($multipleLocations == "Y") {
		$multipleLocations_Long = "YES";
	} else {
		$multipleLocations_Long = "NO";
	}


	// Get the DBA name if available
	$query = "SELECT * FROM Altn_Nm WHERE Acct_ID=".$acctID.";";
	$result = $link->query($query);
	$altNameRow = $result->fetch_assoc();
	$dba = $altNameRow["Altn_Nm"];

	// Get Dealer Address
	$query = "SELECT * FROM Addr WHERE Acct_ID=".$acctID." AND Prim_Addr_Flg='Y' AND
	           Addr_Type_Cd='Work' AND Addr_Type_Desc='Work';";
	$result = $link->query($query);
	$dealerAddressRow = $result->fetch_assoc();

	$dealerAddress1 = $dealerAddressRow["St_Addr_1_Desc"];
	$dealerAddress2 = $dealerAddressRow["St_Addr_2_Desc"];
	$dealerCity = $dealerAddressRow["City_Nm"];
	$dealerState = $dealerAddressRow["St_Prov_ID"];
	$dealerZip = $dealerAddressRow["Pstl_Cd"];

	// Primary Contact Info
	$query = "SELECT * FROM Users WHERE Acct_ID=".$acctID." AND Role_ID=2;";
	$result = $link->query($query);
	$primaryContactRow = $result->fetch_assoc();
	$primary_Contact_Person_id = $primaryContactRow["Pers_ID"];
	$primaryContactEmail = $primaryContactRow["username"];

	// Primary Contact Info
	$query = "SELECT * FROM Pers WHERE Acct_ID=".$acctID." AND Cntct_Prsn_For_Acct_Flg='Y' AND
	           Prsn_Type_Cd='DSA' AND Prsn_Type_Desc='Dealer Sales Agent';";
	$result = $link->query($query);
	$persRow = $result->fetch_assoc();
	$primaryContactLastName = $persRow["Pers_Last_Nm"];
	$primaryContactFirstName = $persRow["Pers_Frst_Nm"];
	$primaryContactEmail = $persRow["Pers_Username"];
	$primary_Contact_Person_id = $persRow["Pers_ID"];
	$usePrimaryAsAP = $persRow["AP_Prsn_Flg"];

	// Primary Contact Tel
	$query = "SELECT * FROM Tel WHERE Acct_ID=".$acctID." AND Tel_Type_Cd='Work' AND
	           Tel_Type_Desc='Work' AND Prim_Tel_Flg='N';";
	$result = $link->query($query);
	$persTelRow = $result->fetch_assoc();
	$primaryContactPhone = $persTelRow["Tel_Nbr"];

	// Accounts Payable info
	if($usePrimaryAsAP=="Y"){
		$accountsPayableContactFirstName = $primaryContactFirstName;
		$accountsPayableContactLastName = $primaryContactLastName;
		$accountsPayableContactEmail = $primaryContactEmail;
		$accountsPayableContactPhone = $primaryContactPhone;


	}else{
		// AP Contact name
		$query = "SELECT * FROM Pers WHERE Acct_ID=".$acctID." AND AP_Prsn_Flg='Y' AND
				   Prsn_Type_Cd='DSA' AND Prsn_Type_Desc='Dealer Sales Agent' AND
				   Pers_Ttl_Nm='Accounts Payable Contact';";
		$result = $link->query($query);
		$persRow = $result->fetch_assoc();
		$accountsPayableContactLastName = $persRow["Pers_Last_Nm"];
		$accountsPayableContactFirstName = $persRow["Pers_Frst_Nm"];
		$accountsPayableContactEmail = $persRow["Pers_Username"];

		// AP Contact Tel
		$query = "SELECT * FROM Tel WHERE Acct_ID=".$acctID." AND Tel_Type_Cd='Work' AND
				   Tel_Type_Desc='Work' AND Prim_Tel_Flg='Y';";
		$result = $link->query($query);
		$persTelRow = $result->fetch_assoc();
		$accountsPayableContactPhone = $persTelRow["Tel_Nbr"];

		// AP Contact Email
		$query = "SELECT * FROM Email WHERE Acct_ID=".$acctID." AND Email_Type_Cd='Work' AND
				   Email_Type_Desc='Work' AND Email_Prim_Flg='Y';";
		$result = $link->query($query);
		$persEmailRow = $result->fetch_assoc();
		$accountsPayableContactEmail = $persEmailRow["Email_URL_Desc"];

	}


	// Dealer Phone
	$query = "SELECT * FROM Tel WHERE Acct_ID=".$acctID." AND Tel_Type_Cd='Work' AND
	           Tel_Type_Desc='Work' AND Prim_Tel_Flg='Y';";
	$result = $link->query($query);
	$dealerTelRow = $result->fetch_assoc();
	$dealerPhone = $dealerTelRow["Tel_Nbr"];

	// Dealer Fax
	$query = "SELECT * FROM Tel WHERE Acct_ID=".$acctID." AND Tel_Type_Cd='Fax' AND
	           Tel_Type_Desc='Fax' AND Prim_Tel_Flg='N';";
	$result = $link->query($query);
	$dealerFaxRow = $result->fetch_assoc();
	$dealerFax = $dealerFaxRow["Tel_Nbr"];

	// Business Email
	$query = "SELECT * FROM Email WHERE Acct_ID=".$acctID." AND Email_Type_Cd='Work' AND
	           Email_Type_Desc='Work' AND Email_Prim_Flg='Y';";
	$result = $link->query($query);
	$dealerEmailRow = $result->fetch_assoc();
	$businessEmail = $dealerEmailRow["Email_URL_Desc"];

	// Business website
	$query = "SELECT * FROM Email WHERE Acct_ID=".$acctID." AND Email_Type_Cd='Website' AND
	           Email_Type_Desc='Website' AND Email_Prim_Flg='N';";
	$result = $link->query($query);
	$dealerWebsiteRow = $result->fetch_assoc();
	$businessWebsite = $dealerWebsiteRow["Email_URL_Desc"];









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

	// $command = "/home/dh_pp7hie/pdftk";
	// $outputdir = "/home/dh_pp7hie/portaldev.vitaltrendsusa.com/uploads/dealer_agreement_pdf/".$pdfFileName;
	// exec($command." cat output ".$outputdir);

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


echo "PDF Created!";

die();

}

?>