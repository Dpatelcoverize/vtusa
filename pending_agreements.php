<?php
//
// File: pending_agreements.php
// Author: Charles Parry
// Date: 8/10/2022
//
//tester

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


// Variables.
$dealerID = "";
$agreementDate = "";
$dealerName = "";
$dealerNameForDisplay = "";
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


if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


// Get the adminID from session, or fail.
if (!(isset($_SESSION["admin_id"]))) {
    header("location: index.php");
    exit;
} else {
    $adminID = $_SESSION["admin_id"];
}





// Look up Dealer Agreement info to display
if(isset($_GET["Acct_ID"])){

	$localAcctID = $_GET["Acct_ID"];

	// Primary Contact Query
	$persQuery = "SELECT * FROM Acct a, Pers p, Email e WHERE p.Acct_ID=".$localAcctID." AND
				  p.Cntct_Prsn_For_Acct_Flg='Y' AND
				  p.Pers_ID = e.Pers_ID AND
				  p.Acct_ID = a.Acct_ID";
	$persResult = $link->query($persQuery);
	if (mysqli_num_rows($persResult) > 0) {
		$persRow = mysqli_fetch_assoc($persResult);
		$primaryPersonPersID = $persRow["Pers_ID"];
		$primaryPersonFirstName = $persRow["Pers_Frst_Nm"];
		$primaryPersonLastName = $persRow["Pers_Last_Nm"];
		$primaryPersonEmail = $persRow["Email_URL_Desc"];
		$multipleLocations = $persRow["Multiple_Locations"];
		$dealerName = $persRow["Acct_Nm"];
		$dealerNameForDisplay = $persRow["Acct_Nm"];
		$fedTaxNumber = $persRow["Fed_Tax_Number"];
		$EINNumber = $persRow["EIN_Nbr"];
		$dunsNumber = $persRow["Duns_Number"];
		$individualBilling = $persRow["Individual_Billing"];
		$salesAgentID = $persRow["Sls_Agnt_ID"];
		$affiliatePercentage = $persRow["Dlr_Affiliate_Fee_Pct"];
		$pdfFileName = $persRow["Dlr_Agrmnt_PDF"];
		$apPersonFlag = $persRow["AP_Prsn_Flg"];
	}


	// Dealer Address Query
	$addrQuery = "SELECT * FROM Addr a WHERE a.Acct_ID=".$localAcctID." AND
	              Addr_Type_Desc='Work' AND Prim_Addr_Flg='Y'";
	$addrResult = $link->query($addrQuery);
	if (mysqli_num_rows($addrResult) > 0) {
		$addrRow = mysqli_fetch_assoc($addrResult);
		$dealerAddress1 = $addrRow["St_Addr_1_Desc"];
		$dealerAddress2 = $addrRow["St_Addr_2_Desc"];
		$dealerCity = $addrRow["City_Nm"];
		$dealerState = $addrRow["St_Prov_ID"];
		$dealerZip = $addrRow["Pstl_Cd"];
	}


	// Dealer Phone Query
	$addrQuery = "SELECT * FROM Tel a WHERE a.Acct_ID=".$localAcctID." AND
	              Tel_Type_Cd='Work' AND Prim_Tel_Flg='Y'";
	$addrResult = $link->query($addrQuery);
	if (mysqli_num_rows($addrResult) > 0) {
		$addrRow = mysqli_fetch_assoc($addrResult);
		$dealerPhone = $addrRow["Tel_Nbr"];
	}


	// Dealer Fax Query
	$addrQuery = "SELECT * FROM Tel a WHERE a.Acct_ID=".$localAcctID." AND
	              Tel_Type_Cd='Fax' AND Prim_Tel_Flg='N'";
	$addrResult = $link->query($addrQuery);
	if (mysqli_num_rows($addrResult) > 0) {
		$addrRow = mysqli_fetch_assoc($addrResult);
		$dealerFax = $addrRow["Tel_Nbr"];
	}


	// Shipping Address Query
	$addrQuery = "SELECT * FROM Addr a WHERE a.Acct_ID=".$localAcctID." AND
	              Addr_Type_Cd='Ship'";
	$addrResult = $link->query($addrQuery);
	if (mysqli_num_rows($addrResult) > 0) {
		$addrRow = mysqli_fetch_assoc($addrResult);
		$shippingAddress1 = $addrRow["St_Addr_1_Desc"];
		$shippingAddress2 = $addrRow["St_Addr_2_Desc"];
		$shippingCity = $addrRow["City_Nm"];
		$shippingState = $addrRow["St_Prov_ID"];
		$shippingZip = $addrRow["Pstl_Cd"];
	}


	// Dealer Notes Query
	$notesQuery = "SELECT * FROM Note WHERE Acct_ID=".$localAcctID." AND
	              Note_Type='agreement'";
	$notesResult = $link->query($notesQuery);
	if (mysqli_num_rows($notesResult) > 0) {
		$notesRow = mysqli_fetch_assoc($notesResult);
		$notesField = $notesRow["Note_Desc"];
	}


	// Dealer Email Query
	$dealerEmailQuery = "SELECT * FROM Email WHERE Acct_ID=".$localAcctID." AND
	                     Email_Prim_Flg='Y' AND Email_Type_Cd='Work'";
	$dealerEmailResult = $link->query($dealerEmailQuery);
	if (mysqli_num_rows($dealerEmailResult) > 0) {
		$dealerEmailRow = mysqli_fetch_assoc($dealerEmailResult);
		$dealerEmail = $dealerEmailRow["Email_URL_Desc"];
	}


	// Dealer Website Query
	$dealerEmailQuery = "SELECT * FROM Email WHERE Acct_ID=".$localAcctID." AND Email_Type_Cd='Website'";
	$dealerEmailResult = $link->query($dealerEmailQuery);
	if (mysqli_num_rows($dealerEmailResult) > 0) {
		$dealerEmailRow = mysqli_fetch_assoc($dealerEmailResult);
		$dealerWebsite = $dealerEmailRow["Email_URL_Desc"];
	}


	// Primary Person Phone
	$primaryPersonPhoneQuery = "SELECT * FROM Tel WHERE Acct_ID=".$localAcctID." AND
	                            Tel_Type_Cd='Work' AND Prim_Tel_Flg='N' AND Pers_ID=".$primaryPersonPersID.";";
	$primaryPersonPhoneResult = $link->query($primaryPersonPhoneQuery);
	if (mysqli_num_rows($primaryPersonPhoneResult) > 0) {
		$primaryPersonPhoneRow = mysqli_fetch_assoc($primaryPersonPhoneResult);
		$primaryPersonPhone = $primaryPersonPhoneRow["Tel_Nbr"];
	}


	// Look up Accounts Payable Contact info.  If the AP_Prsn_Flg is set on the
	//  primary contact person, then their info is to be used.
	if($apPersonFlag=="Y"){
		$apPersonPersID = $primaryPersonPersID;
		$apPersonFirstName = $primaryPersonFirstName;
		$apPersonLastName = $primaryPersonLastName;
		$apPersonEmail = $primaryPersonEmail;
		$apPersonPhone = $primaryPersonPhone;

	}else{
		// Accounts Payable Contact Query
		$persAPQuery = "SELECT * FROM Acct a, Pers p, Email e WHERE p.Acct_ID=".$localAcctID." AND
					  p.Acct_ID = a.Acct_ID AND
					  p.AP_Prsn_Flg='Y' AND
					  p.Pers_Ttl_Nm='Accounts Payable Contact' AND
					  p.Pers_ID = e.Pers_ID";
		$persAPResult = $link->query($persAPQuery);
		if (mysqli_num_rows($persAPResult) > 0) {
			$persAPRow = mysqli_fetch_assoc($persAPResult);
			$apPersonPersID = $persAPRow["Pers_ID"];
			$apPersonFirstName = $persAPRow["Pers_Frst_Nm"];
			$apPersonLastName = $persAPRow["Pers_Last_Nm"];
			$apPersonEmail = $persAPRow["Email_URL_Desc"];
		}

		// AP Person Phone
		$apPersonPhoneQuery = "SELECT * FROM Tel WHERE Acct_ID=".$localAcctID." AND
									Tel_Type_Cd='Work' AND Pers_ID=".$apPersonPersID.";";
		$apPersonPhoneResult = $link->query($apPersonPhoneQuery);
		if (mysqli_num_rows($apPersonPhoneResult) > 0) {
			$apPersonPhoneRow = mysqli_fetch_assoc($apPersonPhoneResult);
			$apPersonPhone = $apPersonPhoneRow["Tel_Nbr"];
		}
	}


	// Dealer DBA
	$dbaQuery = "SELECT * FROM Altn_Nm WHERE Acct_ID=".$localAcctID." AND
	                            Altn_Nm_Type_Cd='DBA';";
	$dbaResult = $link->query($dbaQuery);
	if (mysqli_num_rows($dbaResult) > 0) {
		$dbaRow = mysqli_fetch_assoc($dbaResult);
		$dealerDBA = $dbaRow["Altn_Nm"];
	}


	// Contract and Signature
	$signatureQuery = "SELECT * FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=".$localAcctID." AND
	                            c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID AND cd.Cntrct_Type_Cd is NULL;";
	$signatureResult = $link->query($signatureQuery);
	if (mysqli_num_rows($signatureResult) > 0) {
		$signatureRow = mysqli_fetch_assoc($signatureResult);
		$contract_dim_ID = $signatureRow["Cntrct_Dim_ID"];
		$signatureFileName = $signatureRow["Cntrct_Signature"];
		$signerName = $signatureRow["Cntrct_Signer_Nm"];
		$signerTitle = $signatureRow["Cntrct_Signer_Ttl"];
	}



	// Make the call to the API, either TEST or PROD.
	if(isset($_GET["sendType"])){

		$businessEmail = $dealerEmail;
		$dealerState = selectState($link,$dealerState,"Y");
		$last_id = $localAcctID;

		// What type of API send? Can be test or prod
		if($_GET["sendType"]==""){
			$sendType = "test";
		}else{
			$sendType = $_GET["sendType"];
		}

		// API Call to TruNorth
		include('backend/dealer_agreement_api_script.php');

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
						(<a href="pending_agreements.php">Back to List of Pending Dealer Agreements</a>)
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-lg-12">
					<div class="card">
						<div class="card-header">
							<h4 class="card-title">Dealer Agreement Waiting for Approval</h4>
						</div>
						<div class="card-header">
							Will be sent to TruNorth production API endpoint when approved.
							<a href="pending_agreements.php?Acct_ID=<?php echo $localAcctID; ?>&sendType=test"><button type="button" name="pushToTestButton" class="btn btn-md btn-primary float-right">Push to Test</button></a>
							<a href="pending_agreements.php?Acct_ID=<?php echo $localAcctID; ?>&sendType=prod"><button type="button" name="pushToProdButton" style="background-color:yellow;color:black;" class="btn btn-md btn-primary float-right">Push to PROD</button></a>
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
					<b>Dealer Name:</b> <?php echo $dealerNameForDisplay; ?><br />
					<b>Dealer DBA:</b> <?php echo $dealerDBA; ?><br />
					<b>Dealer Acct ID:</b> <?php echo $localAcctID; ?><br />
					<b>Dealer Email:</b> <?php echo $dealerEmail; ?><br />
					<b>Dealer Phone:</b> <?php echo $dealerPhone; ?><br />
					<b>Dealer Fax:</b> <?php echo $dealerFax; ?><br />
					<b>Dealer Address1:</b> <?php echo $dealerAddress1; ?><br />
					<b>Dealer Address2:</b> <?php echo $dealerAddress2; ?><br />
					<b>Dealer City:</b> <?php echo $dealerCity; ?><br />
					<b>Dealer State:</b> <?php if(is_numeric($dealerState)){echo selectState($link,$dealerState,"Y");}else{echo $dealerState;} ?><br />
					<b>Dealer Zip:</b> <?php echo $dealerZip; ?><br />
					<b>Dealer Website:</b> <?php echo $dealerWebsite; ?><br />
					<b>Multiple Locations:</b> <?php echo $multipleLocations; ?><br />
					<b>Individual Billing:</b> <?php echo $individualBilling; ?><br />
					<br />
					<b>Signer Name:</b> <?php echo $signerName; ?><br />
					<b>Signer Tital:</b> <?php echo $signerTitle; ?><br />
					<img src="uploads/<?php echo $signatureFileName;?>" style="width:90px;height:60px;" /></td>
			</tr>
				</div>

				<div class="col-md-6">
					<b>Primary First Name:</b> <?php echo $primaryPersonFirstName; ?><br />
					<b>Primary Last Name:</b> <?php echo $primaryPersonLastName; ?><br />
					<b>Primary Email:</b> <?php echo $primaryPersonEmail; ?><br />
					<b>Primary Phone:</b> <?php echo $primaryPersonPhone; ?><br />
					<b>Primary Pers_ID:</b> <?php echo $primaryPersonPersID; ?><br />
					<br />
					<b>Accounts Payable First Name:</b> <?php echo $apPersonFirstName; ?><br />
					<b>Accounts Payable Last Name:</b> <?php echo $apPersonLastName; ?><br />
					<b>Accounts Payable Email:</b> <?php echo $apPersonEmail; ?><br />
					<b>Accounts Payable Phone:</b> <?php echo $apPersonPhone; ?><br />
					<b>Accounts Payable Pers_ID:</b> <?php echo $apPersonPersID; ?><br />
					<br />
					<b>Federal Tax ID:</b> <?php echo $fedTaxNumber; ?><br />
					<b>EIN Number:</b> <?php echo $EINNumber; ?><br />
					<b>DUNS Number:</b> <?php echo $dunsNumber; ?><br />
					<b>Sales Agent:</b> <?php echo $salesAgentID; ?><br />
					<b>Affiliate Fee Pct:</b> <?php echo $affiliatePercentage; ?><br />

				</div>


<?php
	//header("location: index.php");
	//exit;

}else{




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
                                <h4 class="card-title">Dealer Agreements Waiting for Approval</h4>
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
<!--
                                                <th>Primary Contact Name</th>
                                                <th>Primary Contact Email</th>
-->
												<th>Location</th>
												<th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php

$query = "SELECT * FROM Acct a, Cntrct c, Cntrct_Dim cd WHERE
          a.Acct_ID = c.Mfr_Acct_ID AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID AND
          cd.Sent_To_TNG_Prod_Flg != 'Y' AND cd.Cntrct_Type_Cd is NULL ORDER BY a.Acct_Nm";

$personResult = $link->query($query);
$businessAddress = "";
$personFirstName = "";
$personLastName = "";
$personEmail = "";

if (mysqli_num_rows($personResult) > 0) {
  // output data of each row
    $loopCounter = 0;
    while ($row = mysqli_fetch_assoc($personResult)) {
        $loopCounter++;

		$addressQuery = "SELECT * FROM Addr WHERE Acct_ID=".$row["Acct_ID"]." AND Addr_Type_Cd='Work' AND Prim_Addr_Flg='Y'";
		$addressResult = $link->query($addressQuery);
		if (mysqli_num_rows($addressResult) > 0) {
		    $addressRow = mysqli_fetch_assoc($addressResult);
			$businessAddress = $addressRow["St_Addr_1_Desc"].", ".$addressRow["City_Nm"];
		}

		// Primary Contact Query
		$persQuery = "SELECT * FROM Pers p, Email e WHERE p.Acct_ID=".$row["Acct_ID"]." AND
		              p.Cntct_Prsn_For_Acct_Flg='Y' AND
		              p.Pers_ID = e.Pers_ID";
		$persResult = $link->query($persQuery);
		if (mysqli_num_rows($persResult) > 0) {
		    $persRow = mysqli_fetch_assoc($persResult);
			$personFirstName = $persRow["Pers_Frst_Nm"];
			$personLastName = $persRow["Pers_Last_Nm"];
			$personEmail = $persRow["Email_URL_Desc"];
		}


        ?>
<tr>
	<td><?php echo $row["Acct_Nm"]; ?></td>
<!--
	<td><?php echo $personFirstName;?> <?php echo $personLastName;?></td>
	<td><?php echo $personEmail;?></td>
-->
	<td><?php echo $businessAddress;?></td>
	<td>[<a href="pending_agreements.php?Acct_ID=<?php echo $row["Acct_ID"]; ?>">Review</a>]</td>
</tr>

<?php

	}

} else {
    ?>
<tr>
	<td colspan="5">No dealers found, yet.</td>
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
	<script src="js/demo.js"></script>
</body>
</html>