<?php
//
// File: warranty_print.php
// Author: Charles Parry
// Date: 8/05/2022
//
//

// Turn on error reporting
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
//error_reporting(E_ALL);


$pageBreadcrumb = "Warranty Print";
$pageTitle = "Warranty Print";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

/**For encryption of the data */
require_once 'encrypt.php';

include 'PDFMerger/PDFMerger.php';


// Variables.
$dealerID = "";
$warrantyID = "";
$errorMessage = "";
$fileType = "";
$filename = "";
$ext = "";

$isQuote = "";

$loopCounter = 0;

$errorString = "";
$isWarrantyFinalized = "";

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


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
}

if (isset($_SESSION["admin_id"])) {
	$adminID = $_SESSION["admin_id"];
}

if (isset($_GET["isQuote"])) {
	$isQuote = $_GET["isQuote"];
}


// Process form data when form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
} else {
	// See if we are specifying a warrantyID in the URL request.
	if (isset($_GET["warrantyID"])) {
		$warrantyID = $_GET["warrantyID"];
		$warrantyID = decryptData($warrantyID);

		// SECURITY make sure this dealer may edit this warranty
		$securityCheck = dealerOwnsWarranty($link, $dealerID, $warrantyID);
		if (!$securityCheck) {
			if ($isQuote == "Y") {
				header("location: warranty_pending.php?showQuotes=Y");
			} else {
				header("location: warranty_pending.php");
			}
			exit;
		}


		$sql = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Cntrct_ID=" . $warrantyID . " AND
				c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
				c.Veh_ID = v.Veh_ID";

		$result = $link->query($sql);
		$row = $result->fetch_assoc();

		$customerName = $row["Cstmr_Nme"];
		$customerEmail = $row["Cstmr_Eml"];
		$customerAddress = $row["Cstmr_Addrs"];
		$customerCity = $row["Cstmr_Cty"];
		$customerState = $row["Cstmr_Ste"];
		$customerZip = $row["Cstmr_Pstl"];
		$customerPhone = $row["Cstmr_Phn"];

		$customerSalesChannel = $row["Cntrct_Sales_Chnl"];
		$agreementDate = $row["Contract_Date"];
		$Vehicle_Gross_Weight = $row["Veh_Gross_Wgt_Cnt"];
		$Vehicle_Type = $row["Veh_Type_Nbr"];
		$Vehicle_Vin_Number = $row["Veh_Id_Nbr"];
		$Vehicle_Year = $row["Veh_Model_Yr_Cd"];
		$Vehicle_Make = $row["Veh_Mk_Cd"];
		$Vehicle_Model = $row["Veh_Model_Cd"];

		$Engine_Make = $row["Veh_Eng_Mk_Cd"];
		$Engine_Model = $row["Veh_Eng_Model_Cd"];
		$Engine_Serial = $row["Veh_Eng_Ser_nbr"];

		$Transmission_Make = $row["Veh_Trnsmsn_Mk_Cd"];
		$Transmission_Model = $row["Veh_Trnsmsn_Model_Cd"];
		$Transmission_Serial = $row["Veh_Trnsmsn_Ser_nbr"];

		$Odometer_Reading_Miles = $row["OdoMtr_Read_Miles_Cnt"];
		$Odometer_Reading_Km = $row["OdoMtr_Read_Kms_Cnt"];
		if ($Odometer_Reading_Miles != 0) {
			$Odometer_Miles_Or_KM = "Miles";
		} else {
			$Odometer_Miles_Or_KM = "KM";
		}

		$ECM_Reading_Miles = $row["ECM_Read_Miles_Cnt"];
		$ECM_Reading_Km = $row["ECM_Read_Kms_Cnt"];
		if ($ECM_Reading_Miles != 0) {
			$ECM_Miles_Or_KM = "Miles";
		} else {
			$ECM_Miles_Or_KM = "KM";
		}

		$APU_Engine_Make = $row["Veh_APU_Eng_Mk_Cd"];
		$APU_Engine_Model = $row["Veh_APU_Eng_Model_Cd"];
		$APU_Engine_Year = $row["Veh_APU_Eng_Yr_Cd"];
		$APU_Engine_Serial = $row["Veh_APU_Eng_Ser_nbr"];
		$APU_Flg = $row["APU_Flg"];

		$Vehicle_New_Flag = $row["Veh_New_Flg"];  // check value on this, coming back as 'k'?
		$Vehicle_Description = $row["Veh_Desc"];
		$Tier_Type = $row["Cntrct_Lvl_Cd"];
		$Tier_Type_Desc = $row["Cntrct_Lvl_Desc"];

		$Apparatus_Equipment_Package = $row["AEP_Flg"];
		$Aerial_Package = $row["Aerial_Flg"];
		$Coverage_Term = $row["Cntrct_Term_Mnths_Nbr"];
		$smallGoodsPackage = $row["Small_Goods_Pkg_Flg"];
		$Srvc_Veh_Flg = $row["Srvc_Veh_Flg"];

		$Supply_Packet_To_Be_Shipped = $row["Sply_Pkt_To_Be_Shipd_Flg"];
		$Supply_Packet_Left = $row["Sply_Pkt_Left_Flg"];
		$Supply_Packet_Shipped_Date = $row["Sply_Pkt_Shipd_Dte"];

		$Lien_Holder_Name = $row["Lien_Nme"];
		$Lien_Holder_Email = $row["Lien_Eml"];
		$Lien_Holder_Address = $row["Lien_Addrs"];
		$Lien_Holder_City = $row["Lien_Cty"];
		$Lien_Holder_State_Province = $row["Lien_Ste"];
		$Lien_Holder_Postal_Code = $row["Lien_Pstl"];
		$Lien_Holder_Phone_Number = $row["Lien_Phn"];

		$wearable_flag = $row['wearables_flag'];
		$isWarrantyFinalized = $row["Finalized_Warranty_Flg"];
	} else {
		if ($isQuote == "Y") {
			header("location: warranty_pending.php?showQuotes=Y");
		} else {
			header("location: warranty_pending.php");
		}
		exit;
	}
}



//RandomString generator for URL Salting.

function random($len)
{

	$char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	// ----------------------------------------------
	// Number of possible combinations
	// ----------------------------------------------
	$pos = strlen($char);
	$pos = pow($pos, $len);
	// echo $pos.'<br>';
	// ----------------------------------------------

	$total = strlen($char) - 1;
	$text = "";

	for ($i = 0; $i < $len; $i++) {
		$text = $text . $char[rand(0, $total)];
	}
	return $text;
}

$RandSaltString = random(12);

//



require_once("includes/header.php");


if (isset($_SESSION["errorMessage"]) && ($_SESSION["errorMessage"] != "")) {
	$errorMessage = $_SESSION["errorMessage"];
	$_SESSION["errorMessage"] = "";
} else {
	$errorMessage = "";
}

// Display Flags
$showInspection = "N";
$showECA = "N";
$showPhotoSection = "N";
$showECM = "N";
$errorString = "";

// Unless vehicle is new, need inspection report
if ($Vehicle_New_Flag != "Y") {
	$showInspection = "Y";
}

// If we have a blank vehicle year, set it to 0 to avoid problems
if ($Vehicle_Year == "") {
	$Vehicle_Year = 0;
}

// New vehicle over 500 miles, less than 2 years show ECA
if (($Vehicle_New_Flag == "Y") && ($Odometer_Miles_Or_KM == "Miles" && $Odometer_Reading_Miles > 500) && ((date("Y") - $Vehicle_Year) < 3)) {
	$showECA = "Y";
	$showPhotoSection = "Y";
} else {
	if ((date("Y") - $Vehicle_Year) < 3) {
		$errorString .= "<Br />ERROR: Age of " . date("Y") - $Vehicle_Year . " is out of bounds for new vehicle.";
	}

	if ($Odometer_Miles_Or_KM == "Miles" && $Odometer_Reading_Miles > 500) {
		//		$errorString .= "<Br />ERROR: Mileage of ".$Odometer_Reading_Miles." Miles is out of bounds for new vehicle.";
	}
}

// Used Type 1
/*
if(($Vehicle_New_Flag!="Y") &&
   ($Vehicle_Type==1) &&
   (($Odometer_Miles_Or_KM=="Miles" && $Odometer_Reading_Miles < 350000) ||
   ($Odometer_Miles_Or_KM=="KM" && $Odometer_Reading_Km < 563270))){
	$showECM = "Y";
}

// Used Type 2
if(($Vehicle_New_Flag!="Y") &&
   ($Vehicle_Type==2) &&
   (($Odometer_Miles_Or_KM=="Miles" && $Odometer_Reading_Miles < 500000) ||
   ($Odometer_Miles_Or_KM=="KM" && $Odometer_Reading_Km < 804672))){
	$showECM = "Y";
}

// Used Type 3
if(($Vehicle_New_Flag!="Y") &&
   ($Vehicle_Type==3) &&
   (($Odometer_Miles_Or_KM=="Miles" && $Odometer_Reading_Miles < 250000) ||
   ($Odometer_Miles_Or_KM=="KM" && $Odometer_Reading_Km < 402336))){
	$showPhotoSection = "Y";
}else{
	if(($Vehicle_New_Flag!="Y") &&
	   ($Vehicle_Type==3) &&
	   (($Odometer_Miles_Or_KM=="Miles" && $Odometer_Reading_Miles > 250000) ||
	   ($Odometer_Miles_Or_KM=="KM" && $Odometer_Reading_Km > 402336))){
		if($Odometer_Miles_Or_KM=="Miles"){
			$odometer_reading = $Odometer_Reading_Miles;
		}else{
			$odometer_reading = $Odometer_Reading_Km;
		}
		$errorString .= "<Br />ERROR: Mileage of ".$odometer_reading." ".$Odometer_Miles_Or_KM." is out of bounds for type ".$Vehicle_Type." used coverage.";
	}
}

*/


if (($Vehicle_New_Flag != "Y") &&
	($Vehicle_Type == 1)
) {
	$showInspection = "Y";
	$showECM = "Y";
}

if (($Vehicle_New_Flag != "Y") &&
	($Vehicle_Type == 2)
) {
	$showInspection = "Y";
	$showECM = "Y";
}

if (($Vehicle_New_Flag != "Y") &&
	($Vehicle_Type == 3)
) {
	$showInspection = "Y";
	$showECM = "Y";
	$showPhotoSection = "Y";
}



?>

<!--**********************************
            Content body start
        ***********************************-->
<div class="content-body">
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-6">
				<div>
					<img src="images/VTPoweredbyTNG.png" alt="Vital Trends Powered by TruNorth">
				</div>
			</div>
			<div class="col-md-6">
				&nbsp;
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header text-center">
						<h4 class="card-title">Supporting files for <?php if ($isQuote == "Y") {
																		echo " Quote ";
																	} else {
																		echo " Warranty ";
																	} ?></h4>
					</div>
					<?php
					// Disable this error reporting for now.  It should take place on warranty creation form.
					if (false) {
						if ($errorString != "") {
					?>
							<div class="card-header text-center">
								<span style="color:red;font-weight:bold;"><?php echo $errorString; ?></span>
							</div>

					<?php
						}
					}
					?>
					<div class="card-header text-center">
						<?php if ($isQuote == "Y") { ?>
							<h5>(<a href="warranty_pending.php?showQuotes=Y">Return to Quote List</a>)</h5>
						<?php } else { ?>
							<h5>(<a href="warranty_pending.php">Return to Warranty List</a>)</h5>
						<?php } ?>
						<a href="print_all_docs.php?warrantyID=<?php echo encryptData($warrantyID) ?>&isQuote=<?php if ($isQuote == "Y") {
																													echo "Y";
																												} else {
																													echo "N";
																												} ?>&salt=<?php echo $RandSaltString; ?>" class="btn btn-primary btn-md" target="_blank">Print Documents</a>
					</div>
					<?php
					if ($errorMessage != "") {
					?>
						<div class="card-header text-center">
							<span style="color:red;">ERROR: <?php echo $errorMessage; ?></span>
						</div>
					<?php
					}
					?>
					<div class="card-body">
						<div class="basic-form dealer-form">
							<div class="watermark">
								<img src="images/logo_large_bg.png" alt="">
							</div>
					
							<?php 
							if ($isQuote != "Y") {
							?>
							<form action="warranty_finalized_form_submit.php" method="POST" id="finalizedWarrantyForm">
								<label>Would you like to Finalize Warranty?</label>
								<div class="form-group col-md-6">
									<label class="radio-inline mr-3"><input type="radio" value="Y" name="isWarrantyFinalized" <?php if ($isWarrantyFinalized == "Y") {
																																		echo " checked='checked' ";
																																	} ?>> Yes</label>
									<label class="radio-inline mr-3"><input type="radio" value="N" name="isWarrantyFinalized" <?php if ($isWarrantyFinalized == "N") {																											echo " checked='checked' ";
																																	} ?>> No</label>
								    <input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID; ?>">
								</div>
							</form>
							<?php } ?>
							
							<div class="form-row">

								<div class="form-group col-md-12">
									<span style="color:BLUE;">
										PLEASE NOTE: Completed forms in PDF format may be emailed
										to dropbox@vitaltrendsusa.com after signature
									</span>
									<br /><b>Agreement Date: </b> <?php echo $agreementDate; ?>
									<br /><b>Customer Name: </b> <?php echo $customerName; ?>
									<br /><b>VIN: </b> <?php echo $Vehicle_Vin_Number; ?>
									<br /><b>Warranty ID?: </b> <?php echo $warrantyID; ?>

								</div>

								<div class="form-group col-md-6">
									<label>Warranty PDF (blank, to be signed)<span class="text-danger"></span></label>
									<?php
									if ($isQuote == "Y") {
										$fileTypeID = 6;
									} else {
										$fileTypeID = 7;
									}
									$filePathResult = getFileAssetForWarranty($link, $warrantyID, $fileTypeID);

									if ($filePathResult != "0") {
									?>
										<br />
										<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
										<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a></span>
										<br /><br />
									<?php
									}
									?>
								</div>

								<div class="form-group col-md-6">
									<label>Ink Signed Warranty<span class="text-danger"></span></label>
									<?php
									if ($isQuote == "Y") {
										$fileTypeID = 16;
									} else {
										$fileTypeID = 17;
									}
									$filePathResult = getFileAssetForWarranty($link, $warrantyID, $fileTypeID);
									if ($filePathResult != 0) {
									?>
										<br />
										<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
										<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a></span>
										<br /><br />
									<?php
									}
									?>
									<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
										<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID; ?>">
										<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID; ?>">
										<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID; ?>">
										<div class="form-group">
											<input name="uploadedFile" id="warrantyPDF" type="file"><br>
											<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
										</div>
									</form>

								</div>

								<div class="form-group col-md-6">
									<label>Warranty Addendum (Pricing Sheet)<span class="text-danger"></span></label>
									<?php
									$fileTypeID = 21;
									$query = "SELECT Path_to_File FROM File_Assets WHERE Warranty_Cntrct_ID=" . $warrantyID . " AND File_Asset_Type_ID = " . $fileTypeID . " ORDER BY File_Asset_ID DESC";
									$result = $link->query($query);
									$addendumPDF = mysqli_fetch_assoc($result);
									if ($addendumPDF) {
										$filePathResult = $addendumPDF["Path_to_File"];
										if ($filePathResult != 0) {
									?>
											<br />
											<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
											<span style="color:green;">File Available! <a href="<?php echo $filePathResult; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a></span>
											<br /><br />
										<?php
										}
									} else {
										?>
										<span style="color:red;font-weight:bold;">File Not Loaded</span>
										<Br /><Br />
									<?php
									}
									?>
								</div>

								<!-- wearable addendum pdf -->
								<?php
								if ($wearable_flag == 'Y') {
								?>
									<div class="form-group col-md-6">
										<label>Wearable Addendum<span class="text-danger"></span></label>
										<?php
										$fileTypeID = $isQuote == 'Y' ? 24 : 23;
										$query = "SELECT Path_to_File FROM File_Assets WHERE Warranty_Cntrct_ID=" . $warrantyID . " AND File_Asset_Type_ID = " . $fileTypeID . " ORDER BY File_Asset_ID DESC";
										$result = $link->query($query);
										$addendumPDF = mysqli_fetch_assoc($result);
										// print_r($addendumPDF);
										// exit;
										if ($addendumPDF) {
											$filePathResult = $addendumPDF["Path_to_File"];
											if ($filePathResult != 0) {
										?>
												<br />
												<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
												<span style="color:green;">File Available! <a href="<?php echo $filePathResult; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a></span>
												<br /><br />
											<?php
											}
										} else {
											?>
											<span style="color:red;font-weight:bold;">File Not Loaded</span>
											<Br /><Br />
										<?php
										}
										?>
									</div>
								<?php
								}
								?>

								<div class="form-group col-md-6">
									&nbsp;
								</div>

								<div class="form-group col-md-12">
									&nbsp;
								</div>

								<div class="form-group col-md-12">
									<h5>Supporting Documents</h5>
								</div>

								<?php
								// Unless vehicle is new, need inspection report
								if ($showInspection == "Y") {

								?>

									<div class="form-group col-md-12">
										<hr />
									</div>

									<div class="form-group col-md-6">
										<label>Inspection Report<span class="text-danger"></span>
											<a href="../uploads/fillable_documents/TNG EMV Inspection v0622 Fillable.pdf" target="_blank">(download blank)</a>
										</label>
										<?php
										$fileTypeID = 8;
										$filePathResult = getFileAssetForWarranty($link, $warrantyID, $fileTypeID);
										if ($filePathResult != 0) {
										?>
											<br />
											<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
											<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a></span>
											<br /><br />
										<?php
										} else {
										?>
											<br />
											<span style="color:red;font-weight:bold;">File Not Loaded</span>
										<?php
										}
										?>
										<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
											<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID; ?>">
											<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID; ?>">
											<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID; ?>">
											<div class="form-group">
												<input name="uploadedFile" id="warrantyPDF" type="file"><br>
												<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
											</div>
										</form>
									</div>

									<div class="form-group col-md-6">
										&nbsp;
									</div>

								<?php

								}

								?>


								<?php
								// Show ECA form if vehicle is new and over 500 miles
								if ($showECA == "Y" && false) {

								?>


									<div class="form-group col-md-6">
										<label>ECA Report<span class="text-danger"></span>
											<a href="../uploads/fillable_documents/EMV_TNG_VT_ECA_v0722_Fillable.pdf" target="_blank">(download blank)</a>
										</label>
										<?php
										$fileTypeID = 9;
										$filePathResult = getFileAssetForWarranty($link, $warrantyID, $fileTypeID);
										if ($filePathResult != 0) {
										?>
											<br />
											<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
											<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a></span>
											<br /><br />
										<?php
										} else {
										?>
											<br />
											<span style="color:red;font-weight:bold;">File Not Loaded</span>
										<?php
										}
										?>
										<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
											<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID; ?>">
											<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID; ?>">
											<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID; ?>">
											<div class="form-group">
												<input name="uploadedFile" id="warrantyPDF" type="file"><br>
												<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
											</div>
										</form>

									</div>

									<div class="form-group col-md-6">
										&nbsp;
									</div>

								<?php

								}

								?>



								<!-- <div class="form-group col-md-12">
									<hr />
								</div> -->

								<!--
										<div class="form-group col-md-6">
											<label>Inspection Report<span class="text-danger"></span></label>
											<br />
											<img style="height: 30px" src="images/pdf.png" alt="" />
											<a
											  href="../uploads/fillable_documents/TNG EMV Inspection v0622 Fillable.pdf"
											  target="_blank">Inspection Report (download)</a>
										</div>

										<div class="form-group col-md-6">
											<label>Equipment Condition Verification Form<span class="text-danger"></span></label>
											<br />
											<img style="height: 30px" src="images/pdf.png" alt="" />
											<a
											  href="../uploads/fillable_documents/EMV_TNG_VT_ECA_v0722_Fillable.pdf"
											  target="_blank">ECA Form (download)</a>
										</div>

-->


								<div class="form-group col-md-6">
									<label>Maintenance and Wear Form<span class="text-danger"></span>
										<a href="../uploads/fillable_documents/VT TNG EMV MWR v0622 F Fillable.pdf" target="_blank">(download)</a>
									</label>
									<?php
									$fileTypeID = 15;
									$filePathResult = getFileAssetForWarranty($link, $warrantyID, $fileTypeID);
									if ($filePathResult != 0) {
									?>
										<br />
										<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
										<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a></span>
										<br /><br />
									<?php
									} else {
									?>
										<br />
										<span style="color:red;font-weight:bold;">File Not Loaded</span>
									<?php
									}
									?>
									<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
										<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID; ?>">
										<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID; ?>">
										<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID; ?>">
										<div class="form-group">
											<input name="uploadedFile" id="warrantyPDF" type="file"><br>
											<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
										</div>
									</form>

								</div>

								<!--
										<div class="form-group col-md-6">
											<label>Maintenance Wear Form<span class="text-danger"></span></label>
											<br />
											<img style="height: 30px" src="images/pdf.png" alt="" />
											<a
											  href="../uploads/fillable_documents/VT TNG EMV MWR v0622 F Fillable.pdf"
											  target="_blank">Maintenance Wear Form (download)</a>
										</div>
-->

								<div class="form-group col-md-12">
									<hr />
								</div>

								<?php
								if ($smallGoodsPackage == "Y") {
									$countOfSmallGoods = 0;
									$countWithReceipts = 0;

								?>
									<div class="form-group col-md-6">
										<label>Small Goods Status <span class="text-danger"></span></label>
									</div>

									<div class="form-group col-md-6">
										&nbsp;
									</div>

									<div class="form-group col-md-12">
										<table class="table table-responsive-md">
											<thead>
												<tr style="background-color:#201F58;color:#FFFFFF;font-weight:bold;">
													<th>Description</th>
													<th>Serial Number</th>
													<th>Receipt?</th>
												</tr>
											</thead>
											<tbody>

												<?php
												// Get small goods detail associated with this warrantyID
												$query  = "SELECT * FROM Sml_Goods_Cvge sgc, Sml_Goods_Gnrc_Prcg sggp WHERE sgc.Cntrct_ID=" . $warrantyID . " AND ";
												$query .= "sgc.Sml_Goods_Gnrc_Prcg_ID=sggp.Sml_Goods_Gnrc_Prcg_ID AND sgc.Is_Deleted_Flg!='Y'";
												$smallGoodsResult = $link->query($query);

												$totalCostRunningSum = 0;
												$totalLiabilitySum = 0;

												$total_Gnrc_Lmt_Of_Lblty_Amt = 0;
												$total_MSRP_Amt = 0;
												$total_Dlr_Cst_Amt = 0;
												$total_Dlr_Mrkp_Max_Amt = 0;
												$total_Actl_Prc_Amt = 0;
												$fileTypeID = 14;

												if (mysqli_num_rows($smallGoodsResult) > 0) {
													// output data of each row
													$loopCounter = 0;
													while ($row = mysqli_fetch_assoc($smallGoodsResult)) {
														$loopCounter++;
														$countOfSmallGoods++;

														if ($row["sml_goods_rcpt_flg"] == "Y") {
															$filePathResult = getFileAssetForSmallGood($link, $row["Sml_Goods_Cvge_ID"], $fileTypeID);
															$countWithReceipts++;
														} else {
															$filePathResult = "";
														}


												?>

														<tr>
															<td><?php echo $row["Item_Cat_Type_Desc"]; ?></td>
															<td><?php echo $row["Ser_nbr"]; ?></td>
															<td>
																<?php
																if ($filePathResult != "") {
																?>
																	<a href="<?php echo $filePathResult; ?>" target="_blank">Receipt</a>
																<?php
																} else {
																?>
																	<span style="color:red;">No Receipt</span>
																	(<a href="small_goods_worksheet.php?smallGoodsCoverageID=<?php echo $row["Sml_Goods_Cvge_ID"]; ?>" target="_blank">Add one here</a>)
																<?php
																}

																?>
															</td>
														</tr>

												<?php
													}
												}
												?>
											</tbody>
										</table>

										<label>
											You have <?php echo $countWithReceipts; ?> of <?php echo $countOfSmallGoods; ?>
											small goods with receipt
										</label>

									</div>


									<div class="form-group col-md-6">
										<?php
										$query = "SELECT Path_to_File FROM File_Assets WHERE Warranty_Cntrct_ID=" . $warrantyID . " AND File_Asset_Type_ID = 13 ORDER BY File_Asset_ID DESC";
										$result = $link->query($query);
										$smallGoodsPDF = mysqli_fetch_assoc($result);
										if ($smallGoodsPDF) {
										?>
											<label>Small Goods Summary</label>
											<a href="<?php echo $smallGoodsPDF['Path_to_File']; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a>
										<?php
										}
										?>
									</div>

									<div class="form-group col-md-6">
										<?php
										$query = "SELECT Path_to_File FROM File_Assets WHERE Warranty_Cntrct_ID=" . $warrantyID . " AND File_Asset_Type_ID = 18 ORDER BY File_Asset_ID DESC";
										$result = $link->query($query);
										$smallGoodsDetailPDF = mysqli_fetch_assoc($result);
										if ($smallGoodsDetailPDF) {
										?>
											<label>Small Goods Detail</label>
											<a href="<?php echo $smallGoodsDetailPDF['Path_to_File']; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a>
										<?php
										}
										?>
									</div>

									<div class="form-group col-md-12">
										&nbsp;
									</div>

								<?php
								} // if(has small goods) //
								?>

								<?php
								// Unless vehicle is new, need inspection report
								if ($showPhotoSection == "Y") {

								?>
									<div class="form-group col-md-6">
										<label>VIN Placard Photo<span class="text-danger"></span></label>
										<?php
										$fileTypeID = 10;
										$filePathResult = getFileAssetForWarranty($link, $warrantyID, $fileTypeID);
										if ($filePathResult != 0) {
										?>
											<br />
											<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
											<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a></span>
											<br /><br />
										<?php
										} else {
										?>
											<br />
											<span style="color:red;font-weight:bold;">File Not Loaded</span>
										<?php
										}
										?>
										<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
											<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID; ?>">
											<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID; ?>">
											<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID; ?>">
											<div class="form-group">
												<input name="uploadedFile" id="warrantyPDF" type="file"><br>
												<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
											</div>
										</form>

									</div>
									<!--
													<div class="form-group col-md-6">
														<label>VIN Placard Image<span class="text-danger"></span></label>
													</div>

													<div class="form-group col-md-6">
														<label>Dashboard (showing Odometer) Image<span class="text-danger"></span></label>
													</div>

													<div class="form-group col-md-6">
														<label>Engine Placard Image<span class="text-danger"></span></label>
													</div>

-->
									<div class="form-group col-md-6">
										&nbsp;
									</div>

									<div class="form-group col-md-12">
										<hr />
									</div>

									<div class="form-group col-md-6">
										<label>Engine Placard Photo<span class="text-danger"></span></label>
										<?php
										$fileTypeID = 12;
										$filePathResult = getFileAssetForWarranty($link, $warrantyID, $fileTypeID);
										if ($filePathResult != 0) {
										?>
											<br />
											<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
											<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a></span>
											<br /><br />
										<?php
										} else {
										?>
											<br />
											<span style="color:red;font-weight:bold;">File Not Loaded</span>
										<?php
										}
										?>
										<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
											<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID; ?>">
											<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID; ?>">
											<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID; ?>">
											<div class="form-group">
												<input name="uploadedFile" id="warrantyPDF" type="file"><br>
												<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
											</div>
										</form>

									</div>

									<div class="form-group col-md-6">
										&nbsp;
									</div>

									<div class="form-group col-md-12">
										<hr />
									</div>

									<div class="form-group col-md-6">
										<label>Dashboard Odometer Photo<span class="text-danger"></span></label>
										<?php
										$fileTypeID = 11;
										$filePathResult = getFileAssetForWarranty($link, $warrantyID, $fileTypeID);
										if ($filePathResult != 0) {
										?>
											<br />
											<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
											<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a></span>
											<br /><br />
										<?php
										} else {
										?>
											<br />
											<span style="color:red;font-weight:bold;">File Not Loaded</span>
										<?php
										}
										?>
										<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
											<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID; ?>">
											<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID; ?>">
											<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID; ?>">
											<div class="form-group">
												<input name="uploadedFile" id="warrantyPDF" type="file"><br>
												<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
											</div>
										</form>

									</div>

									<div class="form-group col-md-6">
										&nbsp;
									</div>

									<div class="form-group col-md-12">
										<hr />
									</div>

								<?php

								} // Show Photos //

								?>

								<?php

								if ($showECM == "Y") {

								?>
									<div class="form-group col-md-6">
										<label>ECM Fault Report<span class="text-danger"></span></label>
										<?php
										$fileTypeID = 20;
										$filePathResult = getFileAssetForWarranty($link, $warrantyID, $fileTypeID);
										if ($filePathResult != 0) {
										?>
											<br />
											<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
											<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult; ?>" target="_blank">(<b>VIEW &amp; PRINT</b>)</a></span>
											<br /><br />
										<?php
										} else {
										?>
											<br />
											<span style="color:red;font-weight:bold;">File Not Loaded</span>
										<?php
										}
										?>
										<form action="warranty_uploads.php" method="POST" id="warrantyUploadForm" enctype="multipart/form-data">
											<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID; ?>">
											<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID; ?>">
											<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID; ?>">
											<div class="form-group">
												<input name="uploadedFile" id="warrantyPDF" type="file"><br>
												<button type="submit" name="uploadFile" id="upload" class="btn btn-md btn-primary float-left">Upload</button>
											</div>
										</form>

									</div>

									<div class="form-group col-md-6">
										&nbsp;
									</div>
								<?php
								}
								?>

								<?php
								// If dealer maintains own fleet, need to suggest this form.
								if ($Srvc_Veh_Flg == "Y") {
								?>
									<div class="form-group col-md-6">
										<label>Fleet Maintenance Form<span class="text-danger"></span>
											<a href="" target="_blank">EMV VT Fleet Maintenance Form (download)</a>
										</label>
										<br />
										<img style="height: 30px" src="images/pdf.png" alt="" />
									</div>
								<?php
								}
								?>



							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>





</div>
</div>
<!--**********************************
            Content body end
        ***********************************-->

<!--**********************************
            Footer start
        ***********************************-->
<div class="footer">
	<div class="copyright">
		<p>Copyright Developed by <a href="http://vitaltrendsusa.com/" target="_blank">Vital Trends</a> 2022</p>
	</div>
</div>
<!--**********************************
            Footer end
        ***********************************-->

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

<!-- Dashboard 1 -->
<script src="./js/custom.min.js"></script>
<script src="./js/deznav-init.js"></script>

<script src="./js/jSignature/jSignature.min.js"></script>
<script src="./js/jSignature/jSignInit.js"></script>
<script src="./js/common.js"></script>
<script src="js/demo.js"></script>
<script>
	$("#printAll").click(function() {


		window.print();

	});
</script>
<script>
	function carouselReview() {
		/*  testimonial one function by = owl.carousel.js */
		function checkDirection() {
			var htmlClassName = document.getElementsByTagName('html')[0].getAttribute('class');
			if (htmlClassName == 'rtl') {
				return true;
			} else {
				return false;

			}
		}

		jQuery('.testimonial-one').owlCarousel({
			loop: true,
			autoplay: true,
			margin: 30,
			nav: false,
			dots: false,
			rtl: checkDirection(),
			left: true,
			navText: ['', ''],
			responsive: {
				0: {
					items: 1
				},
				1200: {
					items: 2
				},
				1600: {
					items: 3
				}
			}
		})
	}
	jQuery(window).on('load', function() {
		setTimeout(function() {
			carouselReview();
		}, 1000);
	});
</script>
<script>
	function printpart() {
		var printwin = window.open("");
		printwin.document.write(document.getElementById("toprint").innerHTML);
		printwin.stop();
		printwin.print();
		printwin.close();
	}
</script>
<script>
	$("input:radio[name='isWarrantyFinalized']").change(function() {
		$("#finalizedWarrantyForm").submit();
	});
</script>

</body>

</html>