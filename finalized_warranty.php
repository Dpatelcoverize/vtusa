<?php
//
// File: finalized_warranty.php (v4 testing)
// Author: Hardik Santoki
// Date: 4/24/2025
//
// Turn on error reporting
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
// error_reporting(E_ALL);
$pageBreadcrumb = "Finalized Warranties";
$pageTitle = "Finalized Warranties";

// Connect to DB
require_once "includes/dbConnect.php";

/**For encryption of the data */
require_once 'encrypt.php';

// DB Library
require_once "lib/dblib.php";

require_once 'vendor/autoload.php';
if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

// Make sure a dealer is currently logged in, or go back to the Agreement
if (!(isset($_SESSION["userType"])) || !($_SESSION["userType"] == "dealer" || $_SESSION["userType"] == "Agent")) {
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
require_once("includes/header.php");

?>
<!--**********************************
            Content body start
        ***********************************-->
<div class="content-body">
	<!-- row -->
	<div class="container-fluid">
		<?php require_once("includes/common_page_content.php"); ?>

		<!-- row -->
		<div class="row">
			<div class="col-lg-12">
				<?php

				if (isset($_SESSION['status'])) {
				?>
					<div class="alert alert-success alert-dismissible fade show" role="alert">
						<strong></strong> <?= $_SESSION['status']; ?>
					</div>
				<?php
					unset($_SESSION['status']);
				}

				if (isset($_SESSION['error'])) {
				?>
					<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<strong></strong> <?= $_SESSION['error']; ?>
					</div>
				<?php
					unset($_SESSION['error']);
				}
				?>
				<div class="card">
					<div class="card-header">
						<h4 class="card-title">Finalized Warranties</h4>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<div class="watermark">
								<img src="images/logo_large_bg.png" alt="">
							</div>
							<div class="table-responsive">
                            <table class="table table-responsive-md" id="finance_table">

								<thead>
									<tr>
										<th>Customer Name</th>
										<th>Vehicle Make</th>
										<th>VIN</th>
										<th>Description</th>

										<th>Actions</th>

									</tr>
								</thead>
								<tbody>
									<?php
									//  SECURITY
									//   if we have an agent logged in, who is not the primary, then limit their view to only their items
									if ($userType == "dealer" && $isContactPerson != "Y" && $persID != "" && !$adminLoggedIn) {
										$limitView = "Y";
									} else {
										$limitView = "N";
									}

									if ($roleID == 3) {
										$limitView = "N";
									}

									if ($dealerID == 1348 && $persID = 504) {
										$limitView = "N";
										$roleID = 3;
									}

									$acctIDList = $dealerID;

									if ($roleID == 5) {
										// For Agency
										if (isset($_SESSION["agencyAccountID"]) && $_SESSION["agencyAccountID"] != "") {
											$acctIDList = getAcctIDForAgency($link, $userID, $_SESSION["agencyAccountID"]);
										}
									}

									if ($roleID == 2) {
										// For Dealer Primary
										$acctIDList = getAcctIDForAgency($link, $userID, $acct_ID);
									}


									$query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Mfr_Acct_ID in (SELECT Acct_ID FROM Acct WHERE Acct_ID in (" . $acctIDList . ") OR Prnt_Acct_ID in (" . $acctIDList . ")) AND c.Created_Warranty_ID is NULL AND c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND cd.Cntrct_Type_Cd='WD' AND cd.Is_Deleted_Flg != 'Y' AND c.Veh_ID = v.Veh_ID AND c.Finalized_Warranty_Flg = 'Y'";

									if ($limitView == "Y") {
										$query .= " AND Dlr_Agt_Prsn_ID=" . $persID . ";";
									}

									$warrantyResult = $link->query($query);

									if (mysqli_num_rows($warrantyResult) > 0) {
										// output data of each row
										$loopCounter = 0;
										while ($row = mysqli_fetch_assoc($warrantyResult)) {
											$loopCounter++;
									?>
											<tr>
												<td><?php echo $row["Cstmr_Nme"]; ?></td>
												<td><?php echo $row["Veh_Mk_Cd"]; ?></td>
												<td><?php echo $row["Veh_Id_Nbr"]; ?></td>
												<td><?php echo $row["Veh_Desc"]; ?></td>

												<?php
												// Remove Addendum link from here for now.
												if (false) {
													if ($showQuotes != "Y") {
														$query = "SELECT Path_to_File FROM File_Assets WHERE Warranty_Cntrct_ID=" . $row["Cntrct_ID"] . " AND File_Asset_Type_ID = 21 ORDER BY File_Asset_ID DESC";
														$result = $link->query($query);
														$addendumPDF = mysqli_fetch_assoc($result);
														if ($addendumPDF) {
												?>
															<td><a href="<?php echo $addendumPDF["Path_to_File"] ?>" target="__blank">Print</td>
														<?php
														} else {
														?>
															<td></td>
												<?php
														}
													}
												}
												?>
												<td style="white-space: nowrap;">

													<?php
													$filePathResult = getFileAssetForWarranty($link, $row["Cntrct_ID"], 7);
													?>
													<a href="<?php echo $filePathResult; ?>" target="_blank" class="btn btn-primary btn-md">Print Warranty</a>
													<!-- <a href="print_all_docs.php?warrantyID=<?php echo encryptData($row["Cntrct_ID"]) ?>&isQuote=N&salt=<?php echo $RandSaltString; ?>" class="btn btn-primary btn-md" target="_blank">Print All Documents</a> -->

												</td>
												<?php

												if (false) {

													// Check if we have uploaded an ink signed warranty.
													if ($showQuotes == "Y") {
														$query = "SELECT * FROM File_Assets WHERE Acct_ID=" . $dealerID . " AND Dealer_Cntrct_ID=" . $row["Cntrct_ID"] . " AND File_Asset_Type_ID = 16 ORDER BY createdDate DESC;";
													} else {
														$query = "SELECT * FROM File_Assets WHERE Acct_ID=" . $dealerID . " AND Dealer_Cntrct_ID=" . $row["Cntrct_ID"] . " AND File_Asset_Type_ID = 17 ORDER BY createdDate DESC;";
													}

													$file = $link->query($query);

													if (mysqli_num_rows($file) > 0) {
														$row = $file->fetch_assoc();
												?>
														<td> <span class="badge badge-success">Uploaded</span></td>
														<td><a href="#" class="changePDF">Change PDF</a></td>
														<td class="d-none fileID"><?php echo  $row["File_Asset_ID"]; ?></td>
														<td class="d-none oldFile"><?php echo  $row["Path_to_File"]; ?></td>
													<?php
													} else {
													?>
														<td> <span class="badge badge-danger">Pending</span></td>
														<td><a class="upload" href="#">Upload PDF</a></td>
														<td class="d-none dealerID"><?php echo  $dealerID; ?></td>
														<td class="d-none warrantyID"><?php echo  $row["Cntrct_ID"]; ?></td>
												<?php
													}
												}
												?>
											</tr>
										<?php
										}
									} else {
										?>
										<tr>
											<td colspan="5">No items found, yet.</td>
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

		<!-- Modal -->
		<div class="modal fade" id="uploadPDF" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Upload Scanned Signature PDF File</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form action="uploadScannedPDF.php" method="POST" id="warrantyForm" enctype="multipart/form-data">
							<input type="hidden" name="showQuotes" value="<?php echo $showQuotes; ?>" />
							<input type="hidden" name="dealerID" id="dealerID" name="dealerID">
							<input type="hidden" name="warrantyID" id="warrantyID" name="warrantyID">
							<div class="form-group">
								<input name="warrantyPDF" id="warrantyPDF" type="file"><br>
								<span class="text-danger" id="warrantyPDFE" style="font-size:12px"></span>
							</div>
							<div class="form-group mt-5">
								<button type="button" name="uploadPDF" id="upload" class="btn btn-md btn-primary float-right">Upload</button>
								<button type="button" class="btn btn-md btn-secondary float-right mr-2" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>


		<!-- Modal -->
		<div class="modal fade" id="changePDF" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Change Scanned Signature PDF File</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form action="changeScannedPDF.php" method="POST" id="changeWarrantyForm" enctype="multipart/form-data">
							<input type="hidden" name="showQuotes" value="<?php echo $showQuotes; ?>" />
							<input type="hidden" name="dealerID" id="changePDFdealerID">
							<input type="hidden" name="fileID" id="fileID">
							<input type="hidden" name="oldFile" id="oldFile">
							<div class="form-group">
								<input name="warrantyPDF" id="changeWarrantyPDF" type="file"><br>
								<span class="text-danger" id="changeWarrantyPDFE" style="font-size:12px"></span>
							</div>
							<div class="form-group mt-5">
								<button type="button" name="changePDF" id="change" class="btn btn-md btn-primary float-right">Upload</button>
								<button type="button" class="btn btn-md btn-secondary float-right mr-2" data-dismiss="modal">Close</button>
							</div>
						</form>
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
</div>

<script src="./vendor/global/global.min.js"></script>
<script src="./vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="./vendor/chart.js/Chart.bundle.min.js"></script>
<script src="./vendor/owl-carousel/owl.carousel.js"></script>

<!-- Chart piety plugin files -->
<script src="./vendor/peity/jquery.peity.min.js"></script>

<!-- Apex Chart -->
<script src="./vendor/apexchart/apexchart.js"></script>

<!-- Dashboard 1 -->
<script src="./js/dashboard/dashboard-1.js"></script>
<script src="./js/custom.min.js"></script>
<script src="./js/deznav-init.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="./js/toastr.js"></script>

<script src="./js/jSignature/jSignature.min.js"></script>
<script src="./js/jSignature/jSignInit.js"></script>

<script>
	$(document).ready(function() {
		$('#preloader').fadeOut(1500);
	});
</script>
   <script src="./vendor/global/global.min.js"></script>
	<script src="./vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

	<!-- Dashboard 1 -->
    <script src="./js/custom.min.js"></script>
	<script src="./js/deznav-init.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
	<script src="js/demo.js"></script>
<script>
        $(document).ready( function () {
          $('#finance_table').DataTable();
        } );
    </script>