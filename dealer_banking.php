<?php
//
// File: dealer_banking.php
// Author: Charles Parry
// Date: 7/05/2022

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Dealer Banking";
$pageTitle = "Dealer Banking";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";


// Variables.
$dealerID = "";
$persID = "";
$Acct_ID = "";
$bankDimID = "";
$agreementDate = "";
$dealerName  = "";
$dealerAddress1 = "";
$dealerAddress2 = "";
$dealerCity = "";
$dealerState = "";
$dealerStateName = "";
$dealerZip = "";
$individualBilling = "";

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
$personBankName = "";
$personBankAccountName = "";
$personBankBillingAddress = "";
$personBankBillingCity = "";
$personBankBillingState = "";
$personBankBillingZip = "";
$personRoutingNumber = "";
$personAccountNumber = "";

$notesField = "";

$dealerLocationID = "";

$errorMessage = "";

$form_err    = "";


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

	if (isset($_POST["Acct_ID"]) && !empty(trim($_POST["Acct_ID"]))) {
		$Acct_ID = trim($_POST["Acct_ID"]);
	}

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
	$val1 = $Acct_ID;
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
	$val1 = $Acct_ID;
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
	$val1 = $Acct_ID;
	$val2 = $notesField;

	mysqli_stmt_bind_param($stmt, "is", $val1, $val2);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);


	// Update tracker for dealer forms, to indicate the form is signed
	$stmt = mysqli_prepare($link, "UPDATE Dealer_Progress SET Dealer_Banking_Complete='Y' WHERE Acct_ID=?");

	/* Bind variables to parameters */
	$val1 = $dealerID;

	mysqli_stmt_bind_param($stmt, "i", $val1);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);


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



	// Redirect back to this page so that more entries can be made.
	header("location: dealer_banking.php");
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
	$individualBilling = $row["Individual_Billing"];

	// Get the contract info
	$query = "SELECT cd.Contract_Date FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
	$result = $link->query($query);
	if ($row = $result->fetch_assoc()) {
		$agreementDate = $row["Contract_Date"];
	} else {
		$agreementDate = "";
	}

	if (false) {
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
}

// Get list of states from the Enumeration table
//$query = "SELECT * FROM St_Prov WHERE Cntry_Nm = 'US' ORDER BY St_Prov_Nm";
$stateResult = selectStates($link);


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
					<a href="index.php" class="btn btn-sm btn-secondary">
                                           Done Adding Banking <i class="fa fa-angle-double-right"></i> Back To Main
                                        </a>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header text-center">
						<h4 class="card-title">Dealer Banking Setup</h4>
						(Fields with * are required)
					</div>
					<?php
					if ($errorMessage != "") {
					?>
						<div class="alert alert-danger alert-dismissible fade show">
							<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
								<polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
								<line x1="15" y1="9" x2="9" y2="15"></line>
								<line x1="9" y1="9" x2="15" y2="15"></line>
							</svg>
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
							<form name="dealerBankingForm" id="dealerBankingForm" method="POST" action="">
								<input type="hidden" name="businessPersonalSelector" value="Business" />
								<div class="form-row row">
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
										<h5 class="text-primary d-inline">Existing Locations without Banking Information</h5>
										<?php
										// Get locations associated with this dealer.

										// If dealer locations are not individually billed, then only need this
										//  info for the main location
										if ($individualBilling == "N") {
											$query = "SELECT a.Acct_ID, a.Acct_Nm, a.Prnt_Acct_ID FROM Acct a LEFT JOIN Bank_Dim b ON a.Acct_ID=b.Acct_ID WHERE a.Acct_ID = " . $dealerID . " AND b.Bank_Acct_Nm is NULL AND b.Pers_ID is NULL ORDER BY a.Prnt_Acct_ID ASC";
										} else {
											$query = "SELECT a.Acct_ID, a.Acct_Nm, a.Prnt_Acct_ID FROM Acct a LEFT JOIN Bank_Dim b ON a.Acct_ID=b.Acct_ID WHERE (a.Acct_ID = " . $dealerID . " OR a.Prnt_Acct_ID=" . $dealerID . ") AND b.Bank_Acct_Nm is NULL AND b.Pers_ID is NULL ORDER BY a.Prnt_Acct_ID ASC";
										}

										$personResult = $link->query($query);

										if (mysqli_num_rows($personResult) > 0) {
										?>
											<select class="form-control default-select" name="Acct_ID" id="sel1">\n
												<?php
												// output data of each row
												$loopCounter = 0;
												while ($row = mysqli_fetch_assoc($personResult)) {
													$loopCounter++;
												?>
													<option value="<?php echo $row["Acct_ID"]; ?>"><?php echo $row["Acct_Nm"]; ?> <?php if ($row["Prnt_Acct_ID"] == "") { ?> (main location)<?php } ?></option>\n
												<?php
												}
												?>
											</select>
										<?php
										} else {
											echo "<br />No locations still need banking information.";
										}
										?>
										<?php if ($individualBilling == "N") { ?>
											<p>NB: This dealer is centrally billed, so only the main location requires this information</p>
										<?php } ?>
										<span style="color:red;<?php if (isset($_SESSION['error_fmessage']) != '') { ?>display:block; <?php } else { ?>display:none; <?php } ?>"><?php if (isset($_SESSION['error_fmessage']) != '') {
																																												echo $_SESSION['error_fmessage'];
																																											} ?></span>
									</div>

									<div class="form-group col-md-12">
										<b>Business Banking</b><br />
									</div>

									<div class="form-group col-md-6">
										<label>Bank Name *</label>
										<input type="text" class="form-control Business" name="businessBankName" id="businessBankName" placeholder="">
										<span style="color: red;display: none;" id="businessBankNameE">Please Enter Bank Name!</span>
									</div>
									<div class="form-group col-md-6">
										<label>Name on Bank Account *</label>
										<input type="text" class="form-control Business" name="businessBankAccountName" id="businessBankAccountName" placeholder="">
										<span style="color: red;display: none;" id="businessBankAccountNameE">Please Enter Account Name!</span>
									</div>
									<div class="form-group col-md-6">
										<label>Business Billing Address *</label>
										<input type="text" class="form-control Business" name="businessBankBillingAddress" id="businessBankBillingAddress" placeholder="">
										<span style="color: red;display: none;" id="businessBankBillingAddressE">Please Enter Bank Billing Address!</span>
									</div>
									<div class="form-group col-md-6">
										<label>Business Billing City *</label>
										<input type="text" class="form-control Business" name="businessBankBillingCity" id="businessBankBillingCity" placeholder="">
										<span style="color: red;display: none;" id="businessBankBillingCityE">Please Enter Bank Billing City!</span>
									</div>
									<div class="form-group col-md-6">
										<label>Business Billing State *</label>
										<select class="form-control default-select" name="businessBankBillingState" id="businessBankBillingState">
											<option value="" selected disabled>-- Select Business State --</option>
											<?php
											if (mysqli_num_rows($stateResult) > 0) {
												// output data of each row
												$loopCounter = 0;
												while ($row = mysqli_fetch_assoc($stateResult)) {
													$loopCounter++;
											?>
													<option value=<?php echo $row["St_Prov_ID"] ?>><?php echo $row["St_Prov_Nm"]; ?></option>
											<?php }
											} ?>
										</select>
										<span style="color: red;display: none;" id="businessBankBillingStateE">Please Enter Bank Billing State!</span>
									</div>
									<div class="form-group col-md-6">
										<label>Business Billing Postal Code *</label>
										<input type="text" class="form-control Business" name="businessBankBillingZip" id="businessBankBillingZip" placeholder="">
										<span style="color: red;display: none;" id="businessBankBillingZipE">Please Enter Bank Billing Postal Code!</span>
									</div>
									<div class="form-group col-md-6">
										<label>Routing Number *</label>
										<input type="text" class="form-control Business" name="businessBankRoutingNumber" id="businessBankRoutingNumber" placeholder="">
										<span style="color: red;display: none;" id="businessBankRoutingNumberE">Please Enter Routing Number!</span>
									</div>
									<div class="form-group col-md-6">
										<label>Account Number *</label>
										<input type="text" class="form-control Business" name="businessBankAccountNumber" id="businessBankAccountNumber" placeholder="">
										<span style="color: red;display: none;" id="businessBankAccountNumberE">Please Enter Account Number!</span>
									</div>
									<div class="form-group col-md-12">
										&nbsp;
									</div>

									<div class="form-group col-md-12">
										<button type="button" class="btn btn-primary" id="dealBankingSubmit" name="dealBankingSubmit">Submit</button>
										<!---<button type="submit" class="btn btn-primary" id="dealBankingSubmit" name="dealBankingSubmit">Submit Business</button>--->
									</div>
									<div class="form-group col-md-12">
										<hr />
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
						<h4 class="card-title">Existing Banking Information Defined for Dealer</h4>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-responsive-md">
								<thead>
									<tr>
										<th class="width80">#</th>
										<th>Location</th>
										<th>Bank Name</th>
										<th>Account Number</th>
									</tr>
								</thead>
								<tbody>
									<?php

									// Get saved banking information for these dealer locations.
									$query = "SELECT * FROM Acct a LEFT JOIN Bank_Dim b ON a.Acct_ID=b.Acct_ID WHERE (a.Acct_ID = " . $dealerID . " OR a.Prnt_Acct_ID=" . $dealerID . ") AND b.Bank_Acct_Nm is NOT NULL ORDER BY a.Prnt_Acct_ID ASC";
									$bankingResult = $link->query($query);

									if (mysqli_num_rows($bankingResult) > 0) {
										// output data of each row
										$loopCounter = 0;
										while ($row = mysqli_fetch_assoc($bankingResult)) {
											$loopCounter++;
									?>
											<tr>
												<td><?php echo $row["Bank_Dim_ID"] ?></td>
												<td><?php echo $row["Acct_Nm"]; ?></td>
												<td><?php echo $row["Bank_Nm"]; ?></td>
												<td><?php echo $row["Bank_Acct_Nbr"]; ?></td>
											</tr>

										<?php
										}
									} else {
										?>
										<tr>
											<td colspan="5">No banking information found, yet.</td>
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
<script src="js/demo.js"></script>
</body>

</html>