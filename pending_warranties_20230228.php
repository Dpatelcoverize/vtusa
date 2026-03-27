<?php
//
// File: pending_agreements.php
// Author: Charles Parry
// Date: 8/10/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Pending Agreements";
$pageTitle = "Pending Agreements";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

include 'PDFMerger/PDFMerger.php';

require_once 'vendor/autoload.php';

// Variables.
$dealerID = "";
$dealerAgentID = "";
$dealerAgentPersID = "";
$dealerAgentName = "";
$dealerAgentFirstName = "";
$dealerAgentLastName = "";
$dealerAgentEmail = "";
$dealerAgentPhone = "";
$dealerAgentSignature = "";
$dealerAgentSignatureBase30 = "";
$dealerAgentTNGNumber = "";
$agreementDate = "";
$dealerName = "";
$dealerAddress1 = "";
$dealerAddress2 = "";
$dealerCity = "";
$dealerState = "";
$dealerZip = "";
$dealerPhone = "";
$dealerFax = "";
$dealerEmail = "";
$dealerWebsite = "";
$dealerDBA = "";
$dealerLocationID = "";
$Dlr_Loc_Dim_ID = "";

$shippingAddress1 = "";
$shippingAddress2 = "";
$shippingCity = "";
$shippingState = "";
$shippingZip = "";

$personFirstName = "";
$personLastName = "";
$personEmail = "";
$personPhone = "";

$primaryPersonPersID = "";
$primaryPersonFirstName = "";
$primaryPersonLastName = "";
$primaryPersonEmail = "";
$primaryPersonPhone = "";
$apPersonFlag = "";
$apPersonPersID = "";
$apPersonFirstName = "";
$apPersonLastName = "";
$apPersonEmail = "";
$apPersonPhone = "";
$multipleLocations = "";
$fedTaxNumber = "";
$EINNumber = "";
$dunsNumber = "";
$individualBilling = "";
$salesAgentID = "";
$affiliatePercentage = "";

$pdfFileName = "";
$signatureFileName = "";
$signerName = "";
$signerTitle = "";

$notesField = "";

$form_err = "";

$errorMessage = "";

session_start();


// Get the adminID from session, or fail.
if (!(isset($_SESSION["admin_id"]))) {
    header("location: index.php");
    exit;
} else {
    $adminID = $_SESSION["admin_id"];
}

//    echo $warranty["Veh_Model_Yr_Cd"];
//    die();

// Look up Warranty info to display
if(isset($_GET["Acct_ID"])){

    $dealerID = $_GET["Acct_ID"];
    $warrantyID = $_GET["Warranty_ID"];

	// Get the dealer info
	//$query = "SELECT * FROM Acct WHERE Acct_ID=" . $dealerID . ";";
	$query = "SELECT * FROM `Acct` a, Cntrct c, Cntrct_Dim cd WHERE
	          a.`Acct_ID`=".$dealerID." AND
	          a.Acct_ID = c.Mfr_Acct_ID AND
	          c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID AND
	          cd.Cntrct_type_Cd is null;";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$dealerName = $row["Acct_Nm"];
	$dealerARNumber = $row["Assign_Rtlr_Nbr"];


    // Dealer Email Query
	$dealerEmailQuery = "SELECT * FROM Email WHERE Acct_ID=".$dealerID." AND
    Email_Prim_Flg='Y' AND Email_Type_Cd='Work'";
    $dealerEmailResult = $link->query($dealerEmailQuery);
    $row = $dealerEmailResult->fetch_assoc();
    $dealerEmail = $row["Email_URL_Desc"];


	// Get the dealer address info
	$query = "SELECT * FROM Addr WHERE Acct_ID=" . $dealerID . " AND Addr_Type_Cd='Work';";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$dealerAddress1 = $row["St_Addr_1_Desc"];
	$dealerAddress2 = $row["St_Addr_2_Desc"];
	$dealerCity = $row["City_Nm"];
	$dealerState = $row["St_Prov_ID"];
	$dealerZip = $row["Pstl_Cd"];

	$customerStateResult = selectStates($link);
	$lienStateResult = selectStates($link);


	// Get the dealer phone
	$phoneResult = selectTelByAcct($link, $dealerID, "Y", "Work");
	$row = $phoneResult->fetch_assoc();
	$dealerPhone = $row["Tel_Nbr"];

    // Get warranty detail

   $query =  "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Mfr_Acct_ID=".$dealerID." AND c.Cntrct_ID =".$warrantyID." AND c.Created_Warranty_ID is NULL AND c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND cd.Cntrct_Type_Cd='WD' AND cd.Is_Deleted_Flg != 'Y' AND c.Veh_ID = v.Veh_ID";
   $result = $link->query($query);
   $warranty = $result->fetch_assoc();

   $agreementDate = $warranty["Created_Date"];
   $dealerAgentID = $warranty["Pers_Who_Signd_Cntrct_ID"];
   $warranty_Cntrct_Dim_ID = $warranty["Cntrct_Dim_ID"];

   //customer
   $customerName =  $warranty["Cstmr_Nme"];
   $customerEmail =  $warranty["Cstmr_Eml"];
   $customerPhone =  $warranty["Cstmr_Phn"];
   $customerAddress =  $warranty["Cstmr_Addrs"];
   $customerCity =  $warranty["Cstmr_Cty"];
   $customerState =  $warranty["Cstmr_Ste"];
   $customerZip =  $warranty["Cstmr_Pstl"];
   //Vehicle
   $vehGrossWeight = $warranty["Veh_Gross_Wgt_Cnt"];
   $vehIDNumber = $warranty["Veh_Id_Nbr"];
   $vehMake = $warranty["Veh_Mk_Cd"];
   $vehModel = $warranty["Veh_Model_Cd"];
   $vehYear = $warranty["Veh_Model_Yr_Cd"];
   $vehType = $warranty["Veh_Type_Nbr"];  // The Type number, 1,2 or 3
   //Engine
   $engMake = $warranty["Veh_Eng_Mk_Cd"];
   $engModel = $warranty["Veh_Eng_Model_Cd"];
   $engYear = $warranty["Veh_Eng_Ser_nbr"];
   $engSerial = $warranty["Veh_Eng_Ser_nbr"];
   //Transmission
   $transmissionMake = $warranty["Veh_Trnsmsn_Mk_Cd"];
   $transmissionModel = $warranty["Veh_Trnsmsn_Model_Cd"];
   $transmissionSerial = $warranty["Veh_Trnsmsn_Ser_nbr"];
   //Meter Readings
   $odometerreadingMiles = $warranty["OdoMtr_Read_Miles_Cnt"];
   $odometerreadingKm = $warranty["OdoMtr_Read_Kms_Cnt"];
   $ecmreadingMiles = $warranty["ECM_Read_Miles_Cnt"];
   $ecmreadingKm = $warranty["ECM_Read_Kms_Cnt"];

   if($odometerreadingMiles!=0){
   	  $odometerreading = $odometerreadingMiles;
   	  $odometerMilesOrKM = "Miles";
   }elseif($odometerreadingKm!=0){
   	  $odometerreading = $odometerreadingKm;
   	  $odometerMilesOrKM = "KM";
   }else{
   	  $odometerreading = 0;
   	  $odometerMilesOrKM = "None";
   }

   if($ecmreadingMiles!=0){
   	  $ecmreading = $ecmreadingMiles;
   	  $ecmMilesOrKM = "Miles";
   }elseif($ecmreadingKm!=0){
   	  $ecmreading = $ecmreadingKm;
   	  $ecmMilesOrKM = "KM";
   }else{
   	  $ecmreading = 0;
   	  $ecmMilesOrKM = "None";
   }


   //APU
   $isAPU = $warranty["APU_Flg"];
   $apuMake = $warranty["Veh_APU_Eng_Mk_Cd"];
   $apuModel = $warranty["Veh_APU_Eng_Model_Cd"];
   $apuYear = $warranty["Veh_APU_Eng_Yr_Cd"];
   $apuSerial = $warranty["Veh_APU_Eng_Ser_nbr"];
   $vehIsNew = $warranty["Veh_New_Flg"];
   $vehDescription = $warranty["Veh_Desc"];
   //Component Coverage
   $vehTierType = $warranty["Cntrct_Lvl_Desc"];
   $AEP = $warranty["AEP_Flg"];
   $aerialPackage = $warranty["Aerial_Flg"];
   $smallGoodsPackage = $warranty["Small_Goods_Pkg_Flg"];
   $coverageTerm = $warranty["Cntrct_Term_Mnths_Nbr"];

   //Lien
   $lienName = $warranty["Lien_Nme"];
   $lienEmail =  $warranty["Lien_Eml"];
   $lienPhone =  $warranty["Lien_Phn"];
   $lienAddress =  $warranty["Lien_Addrs"];
   $lienCity =  $warranty["Lien_Cty"];
   $lienState =  $warranty["Lien_Ste"];
   $lienZip =  $warranty["Lien_Pstl"];


   if($dealerAgentID!="" && is_numeric($dealerAgentID)){
	   // Get the info of the Dealer Agent who is signing this contract.
	   $agentQuery =  "SELECT * FROM Pers p, Email e, Tel t WHERE
					   p.Pers_ID=".$dealerAgentID." AND
					   p.Pers_ID = t.Pers_ID AND
					   p.Pers_ID = e.Pers_ID";
	   $agentResult = $link->query($agentQuery);
	   $agentRow = $agentResult->fetch_assoc();
	   $dealerAgentPersID = $agentRow["Pers_ID"];
	   $dealerAgentName = $agentRow["Pers_Full_Nm"];
	   $dealerAgentFirstName = $agentRow["Pers_Frst_Nm"];
	   $dealerAgentLastName = $agentRow["Pers_Last_Nm"];
	   $dealerAgentSignature = $agentRow["w9_signature"];
	   $dealerAgentSignatureBase30 = $agentRow["W9_Signature_Base30"];
	   $dealerAgentTNGNumber = $agentRow["Pers_Nbr"];
	   $dealerAgentPhone = $agentRow["Tel_Nbr"];
	   $dealerAgentEmail = $agentRow["Email_URL_Desc"];
	}else{
	   // Indicate that we are missing the dealer agent, so do not send yet!
	   $dealerAgentPersID = "";
	   $dealerAgentName = "MISSING";
	   $dealerAgentFirstName = "";
	   $dealerAgentLastName = "";
	   $dealerAgentSignature = "";
	   $dealerAgentSignatureBase30 = "";
	   $dealerAgentTNGNumber = "";
	   $dealerAgentPhone = "";
	   $dealerAgentEmail = "";
	}

   //Get PDF and Image Files
   $query = "SELECT * FROM File_Assets WHERE Acct_ID=" . $dealerID . " AND Dealer_Cntrct_ID=".$warrantyID;"
		          ORDER BY createdDate DESC;";
   $result = $link->query($query);
   $file = $result->fetch_assoc();
   if($file)
   {
    $pdfFileName = ltrim($file["Path_to_File"], $file["Path_to_File"][0]);
   }

   // Warranty PDF
   $Warranty_PDF = getFileAssetForWarranty($link,$warrantyID,7);

   // Warranty PDF SIGNED
   $Warranty_PDF_SIGNED = getFileAssetForWarranty($link,$warrantyID,17);

   // Inspection Report PDF
   $Inspection_Report = getFileAssetForWarranty($link,$warrantyID,8);

   // ECA Report PDF
   $ECA_Report = getFileAssetForWarranty($link,$warrantyID,9);

   // VIN Placard Photo
   $VIN_Photo = getFileAssetForWarranty($link,$warrantyID,10);

   // Dashboard Photo
   $Dashboard_Photo = getFileAssetForWarranty($link,$warrantyID,11);

   // Engine Placard Photo
   $Engine_Photo = getFileAssetForWarranty($link,$warrantyID,12);

   // Maintenance and Wear Form
   $Maintenance_Form = getFileAssetForWarranty($link,$warrantyID,15);

   // NFPA Form
   $NFPA_Form = getFileAssetForWarranty($link,$warrantyID,19);

   // ECM Fault Report Form
   $ECM_Form = getFileAssetForWarranty($link,$warrantyID,20);


// Make the call to the API, either TEST or PROD.
if(isset($_GET["sendType"])){

    // What type of API send? Can be test or prod
    if($_GET["sendType"]==""){
        $sendType = "test";
    }else{
        $sendType = $_GET["sendType"];
    }

	// Make sure we have what we need for the dealer agent
	if($dealerAgentSignature==""){
		$_SESSION["errorMessage"] = "ERROR: No signature saved for Dealer Agent.  Please capture in Personnel Affiliate Fee W9 Form.";
	    header("location: pending_warranties.php?Acct_ID=".$dealerID."&Warranty_ID=".$warrantyID);
	    exit;
	}


	// If the Pers entry already has a Pers_Nbr, then they have already been sent
	//  to the external company and an ID has been sent back.  OK to proceed.
	//  Otherwise, we need to send this user to the underwriter and update that value
	//  before sending the warranty information.
	if($dealerAgentTNGNumber==""){
		// Send the dealer information to TNG
		if($sendType == "test"){
			//$url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/subprod/users/create-dealer?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";
			// Have to send dealer agent to live endpoint for a real test.
			$url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/users/create-dealer?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";

		}else{
			$url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/users/create-dealer?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";
	//echo "oops in prod!";
	//die();
			}

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$headers = array(
		   "Accept: application/json",
		   "Content-Type: application/json",
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		$b64SVGDoc=base64_encode(file_get_contents("uploads/".$dealerAgentSignature));
		$dealerSigNotEncoded=file_get_contents("uploads/".$dealerAgentSignature);


		// Clean some inputs
		if(strpos($dealerAgentSignatureBase30,'image/jsignature;base30,')){
			$dealerAgentSignatureBase30 = str_replace('image/jsignature;base30,','',$dealerAgentSignatureBase30);
		}

		if(strpos($dealerSigNotEncoded,"<svg")){
			$dealerSigNotEncoded = substr($dealerSigNotEncoded, strpos($dealerSigNotEncoded,"<svg"));
			$dealerSigNotEncoded = str_replace("\"","'",$dealerSigNotEncoded);
		} // "


	//echo "dealerSigNotEncoded=".$dealerSigNotEncoded;
	//die();


		$data_direct = "{
		 \"firstName\": \"$dealerAgentFirstName\",
		 \"lastName\": \"$dealerAgentLastName\",
		 \"emailAddress\": \"$dealerAgentEmail\",
		 \"phoneNumber\": \"$dealerAgentPhone\",
		 \"sigBase30\": \"$dealerAgentSignatureBase30\",
		 \"sigSVG\": \"$dealerSigNotEncoded\"
		}";


		curl_setopt($curl, CURLOPT_POSTFIELDS, $data_direct);

		//for debug only!
		//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$resp = curl_exec($curl);
		curl_close($curl);
		//var_dump($resp);
		$json = json_decode($resp, true);

		if (isset($json) && array_key_exists("success", $json)) {
			$responseStatus = $json["success"];
		} else {
			$responseStatus = 0;
		}

		if ($responseStatus == 1) {
			$dealerAgentTNGNumber = $json["data"];
			$apiMessage = $json["message"];

			// Update the Pers record with the returned TNG ID if the call was a success.
			$stmt = mysqli_prepare($link, "UPDATE Pers SET Pers_Nbr=? WHERE Pers_ID=?");


			/* Bind variables to parameters */
			$val1 = $dealerAgentTNGNumber;
			$val2 = $dealerAgentID;

			mysqli_stmt_bind_param($stmt, "si", $val1, $val2);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);

		 } else {
			$dealerAgentTNGNumber = "FAILED";
			$apiMessage = "NONE";
			$responseStatus = 0;
		 }


		//
		// Create a new API_Data entry to track activity
		$stmt = mysqli_prepare($link, "INSERT INTO API_Responses (Acct_ID, Endpoint_Used, statusCode, dataReturned, arNumber, messageText, sentJSON, returnedJSON, createdDate) VALUES (?,?,?,?,?,?,?,?,NOW())");

		/* Bind variables to parameters */
		$val1 = $dealerID;
		$val2 = $url;
		$val3 = $responseStatus;
		$val4 = $dealerAgentTNGNumber;
		$val5 = $dealerAgentTNGNumber;
		$val6 = $apiMessage;
		$val7 = $data_direct;
		$val8 = $resp;

		mysqli_stmt_bind_param($stmt, "isssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);




	}else{
		// The Dealer Agent who is signing this contract already is recorded with TNG, so we
		//  do not need to send them now.
	}

	// Now send the warranty info with an API call to TruNorth.
    include('backend/warranty_api_script.php');

die();

    $businessEmail = $dealerEmail;
    $dealerState = selectState($link,$dealerState,"Y");
    $last_id = $dealerID;

    // What type of API send? Can be test or prod
    if($_GET["sendType"]==""){
        $sendType = "test";
    }else{
        $sendType = $_GET["sendType"];
    }

    // API Call to TruNorth

    if(!isset($arNumber)){
        $arNumber = "Error: None Found";
    }

    if(!isset($apiMessage)){
        $apiMessage = "Error: None Found";
    }

    if(!isset($json)){
        $json = "Error: None Found";
    }

    if(!isset($url)){
        $url = "Error: None Found";
    }

}

	require_once("includes/header.php");

	// Check for error messages
	if(isset($_SESSION["errorMessage"]) && $_SESSION["errorMessage"]!=""){
		$errorMessage = $_SESSION["errorMessage"];
		$_SESSION["errorMessage"] = "";
	}

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
						(<a href="pending_warranties.php">Back to List of Pending warranties</a>)
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-lg-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">Warranty Waiting for Approval</h4>
						</div>
						<?php
						if($errorMessage!=""){
						?>
						<div class="card-header">
							<h4 class="card-title" style="color:red;"><?php echo $errorMessage;?></h4>
						</div>
						<?php
						}
						?>
						<div class="card-header">
							Will be sent to TruNorth production API endpoint when approved.
                            <a href="pending_warranties.php?Acct_ID=<?php echo $dealerID; ?>&Warranty_ID=<?php echo $warrantyID; ?>&sendType=test"><button type="button" name="pushToTestButton" class="btn btn-md btn-primary float-right">Push to Test</button></a>
							<a href="pending_warranties.php?Acct_ID=<?php echo $dealerID; ?>&Warranty_ID=<?php echo $warrantyID; ?>&sendType=prod"><button type="button" name="pushToProdButton" style="background-color:yellow;color:black;" class="btn btn-md btn-primary float-right">Push to PROD</button></a>
                            <!---
							<button value="Approve">Approve!</button>
							--->
						</div>
					</div>
				</div>
				<?php
					if(isset($arNumber) && $arNumber!=""){
				?>
				<div class="col-lg-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">API Result</h4>
						</div>
						<div class="card-header">
							<b>Send Type: </b><?php echo $sendType;?>
							<b>AR Number: </b><?php echo $arNumber;?>
							<b>API Message: </b><?php echo $apiMessage;?>
						</div>
					</div>
				</div>
				<div class="col-lg-12">
					<div class="card">
						<div class="card-header">
							<b>Raw JSON: </b><?php echo print_r($json);?>
						</div>
					</div>
				</div>
				<div class="col-lg-12">
					<div class="card">
						<div class="card-header">
							<b>Endpoint: </b><?php echo $url;?>
						</div>
					</div>
				</div>
				<?php
				}
				?>
            <div class="col-md-6">

                <b>Agreement Date:</b> <?php echo $agreementDate; ?><br />
                <br />
                <b>Dealer Name:</b> <?php echo $dealerName; ?><br />
                <b>Dealer Email:</b> <?php echo $dealerEmail; ?><br />
                <b>Dealer Phone:</b> <?php echo $dealerPhone; ?><br />
                <b>Dealer Address:</b> <?php echo $dealerAddress1; ?><br />
                <b>Dealer City:</b> <?php echo $dealerCity; ?><br />
                <b>Dealer State:</b> <?php if($dealerState!=""){ $dealerState = selectState($link,$dealerState); }?> <?php echo $dealerState; ?><br />
                <b>Dealer Zip</b> <?php echo $dealerZip; ?><br />
                <b>Dealer AR Number</b> <?php echo $dealerARNumber; ?><br />

                <br />
                <b>Dealer Agent Name:</b> <?php echo $dealerAgentName; ?><br />
                <b>Dealer Agent Signature?:</b> <?php if($dealerAgentSignature!=""){echo "Yes";}else{echo "No";} ?><br />
				<?php
					// If we need the dealer agent signature, display a link back to that part of the system
					if($dealerAgentSignature=="" && isset($dealerAgentPersID) && is_numeric($dealerAgentPersID)){
					?>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<a href="dealer_affiliate_fee_w9.php?persID=<?php echo $dealerAgentPersID; ?>" style="color:blue;" target="_blank">Go to Signature Page</a>
						<br />
					<?php
					}

				?>
                <br />
                <b>Customer Name:</b> <?php echo $customerName; ?><br />
                <b>Customer Email:</b> <?php echo $customerEmail; ?><br />
                <b>Customer Phone:</b> <?php echo $customerPhone; ?><br />
                <b>Customer Address:</b> <?php echo $customerAddress; ?><br />
                <b>Customer City:</b> <?php echo $customerCity; ?><br />
                <b>Customer State:</b> <?php if($customerState!=""){ $customerState = selectState($link,$customerState); }?> <?php echo $customerState; ?><br />
                <b>Customer Zip</b> <?php echo $customerZip; ?><br />
                <br />
                <b>Lien Name:</b> <?php echo $lienName; ?><br />
                <b>Lien Phone:</b> <?php echo $lienPhone; ?><br />
                <b>Lien Address:</b> <?php echo $lienAddress; ?><br />
                <b>Lien City:</b> <?php echo $lienCity; ?><br />
                <b>Lien State:</b> <?php if($lienState!=""){ $lienState = selectState($link,$lienState); }?> <?php if($lienState != 0) {echo $lienState; }?><br />
                <b>Lien Zip</b> <?php echo $lienZip; ?><br />

           </div>
           <div class="col-md-6">
           <b>Vehicle Gross Weight:</b> <?php echo $vehGrossWeight; ?><br />
           <b>Vehicle ID:</b> <?php echo $vehIDNumber; ?><br />
           <b>Vehicle Make:</b> <?php echo $vehMake; ?><br />
           <b>Vehicle Model:</b> <?php echo $vehModel; ?><br />
           <b>Vehicle Year:</b> <?php echo $vehYear; ?><br />
           <b>Engine Make:</b> <?php echo $engMake; ?><br />
           <b>Engine Model:</b> <?php echo $engModel; ?><br />
           <b>Engine Year:</b> <?php echo $engYear; ?><br />
           <b>Engine Serial:</b> <?php echo $engSerial; ?><br />
           <b>Transmission Serial:</b> <?php echo $transmissionSerial; ?><br />
           <b>Transmission Make:</b> <?php echo $transmissionMake; ?><br />
           <b>Transmission Model:</b> <?php echo $transmissionModel; ?><br />
           <b>Odometer Reading:</b> <?php echo $odometerreading; ?> (<?php echo $odometerMilesOrKM;?>)<br />
           <b>ECM Reading:</b> <?php echo $ecmreading; ?> (<?php echo $ecmMilesOrKM;?>)<br />
           <?php if($isAPU == "Y") {?>
           <b>APU Make:</b> <?php echo $apuMake; ?><br />
           <b>APU Model:</b> <?php echo $apuModel; ?><br />
           <b>APU Year:</b> <?php echo $apuYear; ?><br />
           <b>APU Serial:</b> <?php echo $apuSerial; ?><br />
           <?php } ?>
           <b>Is Vehicle New?:</b> <?php echo $vehIsNew; ?><br />
           <b>Vehicle Description:</b> <?php echo $vehDescription; ?><br />
           <b>Tier Type:</b> <?php echo $vehTierType; ?><br />
           <b>Apparatus Equipment Package:</b> <?php echo $AEP; ?><br />
           <b>Aerial Package:</b> <?php echo $aerialPackage; ?><br />
           <b>Small Goods:</b> <?php echo $smallGoodsPackage; ?><br />
           <b>Coverage Term:</b> <?php echo $coverageTerm; ?><br />
           </div>

		   <div class="col-md-12">
			&nbsp;
		   </div>

		   <div class="col-md-6">
			<label>SIGNED Warranty</label>
			<?php
			if($Warranty_PDF_SIGNED!=0){
			?>
				<br />
				<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
				<span style="color:green;">File Uploaded! <a href="<?php echo $Warranty_PDF_SIGNED;?>" target="_blank">(view)</a></span>
				<br /><br />
			<?php
			}
			?>

		   </div>

		   <div class="col-md-6">
			&nbsp;
		   </div>

		   <div class="col-md-12">
			&nbsp;
		   </div>

		   <div class="col-md-6">
			<label>Inspection Report</label>
			<?php
			if($Inspection_Report!=0){
			?>
				<br />
				<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
				<span style="color:green;">File Uploaded! <a href="<?php echo $Inspection_Report;?>" target="_blank">(view)</a></span>
				<br /><br />
			<?php
			}
			?>

		   </div>

		   <div class="col-md-6">
			<label>ECA Report</label>
			<?php
			if($ECA_Report!=0){
			?>
				<br />
				<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
				<span style="color:green;">File Uploaded! <a href="<?php echo $ECA_Report;?>" target="_blank">(view)</a></span>
				<br /><br />
			<?php
			}
			?>

		   </div>

		   <div class="col-md-6">
			<label>VIN Placard Photo</label>
			<?php
			if($VIN_Photo!=0){
			?>
				<br />
				<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
				<span style="color:green;">File Uploaded! <a href="<?php echo $VIN_Photo;?>" target="_blank">(view)</a></span>
				<br /><br />
			<?php
			}
			?>

		   </div>


		   <div class="col-md-6">
			<label>Dashboard Photo</label>
			<?php
			if($Dashboard_Photo!=0){
			?>
				<br />
				<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
				<span style="color:green;">File Uploaded! <a href="<?php echo $Dashboard_Photo;?>" target="_blank">(view)</a></span>
				<br /><br />
			<?php
			}
			?>

		   </div>

		   <div class="col-md-6">
			<label>Engine Placard Photo</label>
			<?php
			if($Engine_Photo!=0){
			?>
				<br />
				<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
				<span style="color:green;">File Uploaded! <a href="<?php echo $Engine_Photo;?>" target="_blank">(view)</a></span>
				<br /><br />
			<?php
			}
			?>

		   </div>

		   <div class="col-md-6">
			<label>Maintenance and Wear Form</label>
			<?php
			if($Maintenance_Form!=0){
			?>
				<br />
				<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
				<span style="color:green;">File Uploaded! <a href="<?php echo $Maintenance_Form;?>" target="_blank">(view)</a></span>
				<br /><br />
			<?php
			}
			?>

		   </div>

		   <div class="col-md-6">
			<label>NFPA Inspection Form</label>
			<?php
			if($NFPA_Form!=0){
			?>
				<br />
				<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
				<span style="color:green;">File Uploaded! <a href="<?php echo $NFPA_Form;?>" target="_blank">(view)</a></span>
				<br /><br />
			<?php
			}
			?>

		   </div>

		   <div class="col-md-6">
			<label>ECM Fault Report</label>
			<?php
			if($ECM_Form!=0){
			?>
				<br />
				<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
				<span style="color:green;">File Uploaded! <a href="<?php echo $ECM_Form;?>" target="_blank">(view)</a></span>
				<br /><br />
			<?php
			}
			?>

		   </div>


<?php
}else{

//    $query = "SELECT * FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=".$dealerID." AND"


    $query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Acct ac WHERE
    c.Created_Warranty_ID is NULL AND
    c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
    cd.Cntrct_Type_Cd='WD' AND
    cd.Is_Deleted_Flg != 'Y' AND
    ac.Acct_ID = c.Mfr_Acct_ID";

$warrantyResult = $link->query($query);


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

						</div>
					</div>
				</div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Warranties Waiting for Approval</h4>
                            </div>
                            <div class="card-header">
								Will be sent to TruNorth production API endpoint when approved.
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-responsive-md">
                                        <thead>
                                            <tr>
                                               <th>Dealer Name</th>
                                                <th>Customer Name</th>
												<th>Location</th>
                                                <th>Agreement Date</th>
												<th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while ($row = mysqli_fetch_assoc($warrantyResult)) {
                                            ?>
                                            <tr>
                                                <td><?php echo $row["Acct_Nm"] ?></td>
                                                <td><?php echo $row["Cstmr_Nme"] ?></td>
												<td><?php echo $row["Cstmr_Addrs"] ?></td>
                                                <td><?php echo $row["Created_Date"] ?></td>
                                               <td>[<a href="pending_warranties.php?Acct_ID=<?php echo $row["Acct_ID"];?>&&Warranty_ID=<?php echo $row["Cntrct_ID"];?>">Review</a>]</td>
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
}
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
	<script src="./js/dashboard/dashboard-1.js"></script>
    <script src="./js/custom.min.js"></script>
	<script src="./js/deznav-init.js"></script>

</body>
</html>