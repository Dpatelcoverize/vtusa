<?php
//
// File: dealer_affiliate_fee_w9.php
// Author: Charles Parry
// Date: 6/13/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Dealer Affiliate W9";
$pageTitle = "Dealer Affiliate W9";


// Connect to DB
require_once "includes/dbConnect.php";


// Variables.
$dealerID = "";
$persID = "";
$agreementDate = "";
$signedDate = "";
$dealerCompanyType = "";
$dealerCompanyDesc = "";
$exemptionPayeeCode = "";
$exemptionFATCACode = "";
$dealerEIN = "";

$personAddress1 = "";
$personAddress2 = "";
$personCity = "";
$personState = "";
$personStateName = "";
$personZip = "";

$form_err    = "";


if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

// Make sure a dealer is currently logged in, or go back to the Agreement
if(!(isset($_SESSION["userType"])) || !($_SESSION["userType"] == "dealer")){
    header("location: index.php");
    exit;
}

// Get a dealer ID from session.
if(!(isset($_SESSION["id"]))){
    header("location: index.php");
    exit;
}else{
	$dealerID = $_SESSION["id"];
}

$IRS_Stat_Cd='';
$Exempt_Payee_Cd='';
$Exempt_FATCA_Rptg_Cd='';
$EIN_Nbr='';

// Process form data when form is submitted.
if($_SERVER["REQUEST_METHOD"] == "POST"){

	// Get form fields

	// Get PersID that we are updating.
	//  NOTE - should do more checking here to ensure this PersID is valid for this dealer.
    if(isset($_POST["persID"]) && !empty(trim($_POST["persID"]))){
        $persID = trim($_POST["persID"]);
    }else{
		// Must have a PersID at this point, or fail back to parent page.
		header("location: dealer_affiliate_fee.php");
		exit;
    }

    if(!empty(trim($_POST["exemptionPayeeCode"]))){
        $exemptionPayeeCode = trim($_POST["exemptionPayeeCode"]);
    }

    if(!empty(trim($_POST["exemptionFATCACode"]))){
        $exemptionFATCACode = trim($_POST["exemptionFATCACode"]);
    }


	// handle signature
	$fileName = "";
	try{

		$fields = (object)$_POST;
		$image     =    base64_decode($fields->signature);
		$imageData = $_POST['signature'];
		$imageDataBase30 = $_POST['base30'];
		list($type, $imageData) = explode(';', $imageData);
		list(,$extension) = explode('/',$type);
		list(,$imageData)      = explode(',', $imageData);
		// $fileName = uniqid().'.'.$extension;
		$data = explode('+', $extension);
		$fileName = uniqid().'.'.$data[0];
		$imageData = base64_decode($imageData);
		$image = 'uploads/'.$fileName;
		file_put_contents($image, $imageData);
		$my_date = date("Y-m-d H:i:s");

	}catch (Exception $exception){
		//echo json_encode(['status'=>400,'message'=>$exception->getMessage()]);
	}



	// Hard code to this value for this workflow.
	$dealerCompanyDesc = "Individual";


	/* Prepare an insert statement to update the Pers record for this user with W-9 details. */
	$stmt = mysqli_prepare($link, "UPDATE Pers SET Exempt_Payee_Cd=?,Exempt_FATCA_Rptg_Cd=?,
	                               w9_signature=?, W9_Signature_Base30=?, w9_signed_date=NOW() WHERE Pers_ID=? AND Acct_ID=?");

	/* Bind variables to parameters */
	$val1 = $exemptionPayeeCode;
	$val2 = $exemptionFATCACode;
	$val3 = $fileName;
	$val4 = $imageDataBase30;
	$val5 = $persID;
	$val6 = $dealerID;

	mysqli_stmt_bind_param($stmt, "ssssii", $val1,$val2,$val3,$val4,$val5,$val6);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);





	// Redirect to next form
    header("location: dealer_affiliate_fee.php");
    exit;

	die();
}else{

	// Get the Pers info
    if(isset($_GET["persID"]) && !empty(trim($_GET["persID"]))){
        $persID = trim($_GET["persID"]);
    }else{
		// Must have a PersID at this point, or fail back to parent page.
		header("location: dealer_affiliate_fee.php");
		exit;
    }

	$query = "SELECT * FROM Pers WHERE Pers_ID=".$persID.";";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$personFirstName = $row["Pers_Frst_Nm"];
	$personLastName = $row["Pers_Last_Nm"];
	$personSSN = $row["Soc_Secur_Nbr"];



	// Get the dealer info
	$query = "SELECT * FROM Acct WHERE Acct_ID=".$dealerID.";";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$dealerName = $row["Acct_Nm"];
//    $Exempt_Payee_Cd = $row["Exempt_Payee_Cd"];
//    $IRS_Stat_Cd=$row["IRS_Stat_Cd"];
//    $Exempt_FATCA_Rptg_Cd=$row["Exempt_FATCA_Rptg_Cd"];
//    $EIN_Nbr=$row["EIN_Nbr"];


	// Get the dealer address info
	$query = "SELECT * FROM Addr WHERE Acct_ID=".$dealerID." AND Addr_Type_Cd='Work';";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$dealerAddress1 = $row["St_Addr_1_Desc"];
	$dealerAddress2 = $row["St_Addr_2_Desc"];
	$dealerCity = $row["City_Nm"];
	$dealerState = $row["St_Prov_ID"];
	$dealerZip = $row["Pstl_Cd"];

	// Look up the state name
	if($dealerState > 0){
		$query = "SELECT * FROM St_Prov WHERE St_Prov_ID=".$dealerState;
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$dealerStateName = $row["St_Prov_ISO_2_Cd"];

	}else{
		$dealerStateName = "None Found";
	}


	// Get the PERS address info
	$query = "SELECT * FROM Addr WHERE Pers_ID=".$persID.";";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$personAddress1 = $row["St_Addr_1_Desc"];
	$personAddress2 = $row["St_Addr_2_Desc"];
	$personCity = $row["City_Nm"];
	$personState = $row["St_Prov_ID"];
	$personZip = $row["Pstl_Cd"];

	// Look up the state name
	if($personState > 0){
		$query = "SELECT * FROM St_Prov WHERE St_Prov_ID=".$dealerState;
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$personStateName = $row["St_Prov_ISO_2_Cd"];

	}else{
		$personStateName = "None Found";
	}



	// Get the contract info
	$query = "SELECT cd.Contract_Date FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=".$dealerID." AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$agreementDate = $row["Contract_Date"];

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
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header text-center">
                                <h4 class="card-title">Individual W9</h4>
                            </div>
                            <div class="card-body">
                                <div class="basic-form dealer-form">
                                <div class="watermark" style="z-index:-1;">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>
                                    <form name="dealerForm" id="dealer_w9_form" method="POST" action="">
										<input type="hidden" name="persID" value="<?php echo $persID;?>" />
                                        <div class="form-row">
						<div class="form-group col-md-6">
							<h5 class="text-primary d-inline">Dealer Name</h5>
							<h4 class="text-muted mb-0"><?php echo $dealerName;?></h4>
						</div>
						<div class="form-group col-md-6">
							<h5 class="text-primary d-inline">Agreement Date</h5>
							<h4 class="text-muted mb-0"><?php echo $agreementDate;?></h4>
						</div>
						<div class="form-group col-md-12">
							<h5 class="text-primary d-inline">Dealership Address</h5>
							<h4 class="text-muted mb-0"><?php echo $dealerAddress1;?> <?php echo $dealerCity.", ".$dealerStateName.". ".$dealerZip;?></h4>
						</div>
						<div class="form-group col-md-12">
							<hr />
						</div>
						<div class="form-group col-md-12">
							<h5 class="text-primary d-inline">Name</h5>
							<h4 class="text-muted mb-0"><?php echo $personFirstName." ".$personLastName;?></h4>
						</div>
						<div class="form-group col-md-6">
							<h5 class="text-primary d-inline">Company Type</h5>
							<h4 class="text-muted mb-0">Individual</h4>
						</div>
						<div class="form-group col-md-6">
							<h5 class="text-primary d-inline">SSN</h5>
							<h4 class="text-muted mb-0"><?php echo $personSSN;?></h4>
						</div>
						<div class="form-group col-md-6">
							<h5 class="text-primary d-inline">Address 1</h5>
							<h4 class="text-muted mb-0"><?php echo $personAddress1; ?></h4>
						</div>
						<div class="form-group col-md-6">
							<h5 class="text-primary d-inline">Address 2</h5>
							<h4 class="text-muted mb-0"><?php echo $personAddress2; ?></h4>
						</div>
						<div class="form-group col-md-6">
							<h5 class="text-primary d-inline">City</h5>
							<h4 class="text-muted mb-0"><?php echo $personCity; ?></h4>
						</div>
						<div class="form-group col-md-6">
							<h5 class="text-primary d-inline">State</h5>
							<h4 class="text-muted mb-0"><?php echo $personStateName; ?></h4>
						</div>
						<div class="form-group col-md-6">
							<h5 class="text-primary d-inline">Zip / Postal Code</h5>
							<h4 class="text-muted mb-0"><?php echo $personZip; ?></h4>
						</div>

						<div class="form-group col-md-12">
							<hr />
						</div>
						<div class="form-group col-md-12 terms-text">
							<b>Part I Taxpayer Identification Number (TIN)  (AGK - This goes right above
							where you enter the EIN or SSN)</b>
							<br /><br />
							Enter your TIN in the appropriate box. The TIN provided must match the name
							given on line 1 to avoid backup withholding. For individuals, this is generally
							your social security number (SSN). However, for a resident alien, sole proprietor,
							or disregarded entity, see the instructions for Part I, later. For other entities,
							it is your employer identification number (EIN). If you do not have a number, see
							How to get a TIN, later.
						</div>

						<div class="form-group col-md-6">
							Exemption Payee Code
							<input type="text" class="form-control" name="exemptionPayeeCode" id="exemptionPayeeCode" value="<?php  echo $Exempt_Payee_Cd; ?>" placeholder="">
							<span style="color: red;display: none;" id="exemptionPayeeCodeE">Please Enter Exemption Payee Code..!</span>
						</div>
						<div class="form-group col-md-6">
							Exemption From FATCA Reporting Code
							<input type="text" class="form-control" name="exemptionFATCACode" id="exemptionFATCACode" value="<?php  echo $Exempt_FATCA_Rptg_Cd; ?>" placeholder="">
							<span style="color: red;display: none;" id="exemptionFATCACodeE">Please Enter Exemption FATCA Code..!</span>
						</div>

					<!-- <div class="form-group col-md-12">
							<h3>Click to sign</h3>
							<input type="text" id="txt" style="border-radius: 5px;">
						</div> -->

						<div class="form-group col-md-12 terms-text">
							<b>Under penalties of perjury, I certify that:</b>
							<br /><br />
							<ol>
								<li>The number shown on this form is my correct taxpayer identification number (or I am waiting for a number to be issued to me); and</li>
								<li>I am not subject to backup withholding because: (a) I am exempt from backup withholding, or (b) I have not been notified by the Internal Revenue Service (IRS) that I am subject to backup withholding as a result of a failure to report all interest or dividends, or (c) the IRS has notified me that I am no longer subject to backup withholding; and</li>
								<li>I am a U.S. citizen or other U.S. person (defined below); and 4. The FATCA code(s) entered on this form (if any) indicating that I am exempt from FATCA reporting is correct.</li>
							</ol>
							Certification instructions. You must cross out item 2 above if you have been notified by the IRS that you are currently subject to backup withholding because you have failed to report all interest and dividends on your tax return. For real estate transactions, item 2 does not apply. For mortgage interest paid, acquisition or abandonment of secured property, cancellation of debt, contributions to an individual retirement arrangement (IRA), and generally, payments other than interest and dividends, you are not required to sign the certification, but you must provide your correct TIN.
						</div>

                        <div class="form-group col-md-12 row">
                            <div class="form-group col-md-12">
                                <h5 class="font-weight-normal">Sign here</h5>
                                <div class="signature"></div>
                                <span style="color: red;display: none;" id="signatureE">Please Enter Signature Data..!</span>
                            </div>
                        </div>

                                        </div>
                                        <button type="button" id="dealer_w9_submit" name="dealer_w9_submit" class="btn btn-primary">Submit</button>
                                    </form>
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
	<script src="./js/dashboard/dashboard-1.js"></script>
    <script src="./js/custom.min.js"></script>
	<script src="./js/deznav-init.js"></script>
    <script src="./js/jSignature/jSignature.min.js"></script>
    <script src="./js/jSignature/jSignInit.js"></script>
    <script src="./js/custom-validation.js"></script>

</body>
</html>