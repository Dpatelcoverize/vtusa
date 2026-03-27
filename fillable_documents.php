<?php
//
// File: dealer_agreement.php (v4 testing)
// Author: Charles Parry
// Date: 5/14/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Contracts Home";
$pageTitle = "Fillable Documents";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

// Email function
require_once "lib/emailHelper.php";

// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');


if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

// Make sure a dealer is currently logged in, or go back to the Agreement
//if (!(isset($_SESSION["userType"])) || !($_SESSION["userType"] == "dealer")) {
//	header("location: index.php");
//	exit;
//}

// Get a dealer ID from session.
//if (!(isset($_SESSION["id"]))) {
//	header("location: index.php");
//	exit;
//} else {
//	$dealerID = $_SESSION["id"];
//	$adminID = $_SESSION["admin_id"];
//    $userType = $_SESSION["userType"];
//}


// Get an error message from session if applicable.
if ((isset($_SESSION["errorMessage"]))) {
  $errorMessage = $_SESSION["errorMessage"];
  $_SESSION["errorMessage"] = "";
} else {
  $errorMessage = "";
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
            <h4 class="card-title">Fillable Documents</h4>
          </div>
          <div class="card-body">
            <div class="row" style="padding-bottom: 60px">
<!---
<div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/fillable_documents/VT TNG GARA v0622 Addtl Locations Fillable.pdf" target="__blank">ADDITIONAL LOCATIONS FILLABLE PDF</a>
              </div>
--->
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/fillable_documents/Vital Trends Agreement v0425.pdf" target="__blank">FILLABLE CONTRACT</a>
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/fillable_documents/VT TNG GARA Addendum v0622 F Fillable.pdf" target="__blank">DEALER ADDENDUM FILLABLE PDF </a>
              </div>
              <?php
                if (false) {
              ?>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/fillable_documents/TNG_EMV_WRAP_WA_ENG_v0523_Fillable.pdf" target="__blank">WRAP PROGRAM WARRANTY FILLABLE PDF </a>
              </div>
              
                <div class="col-md-4 mt-5">
                  <img style="height: 30px" src="images/pdf.png" alt="" />
                  <a href="../uploads/fillable_documents/EMV_TNG_VT_ECA_v0722_Fillable.pdf" target="__blank">EQUIPMENT CONDITION AFFIDAVIT (ECA) FILLABLE PDF</a>
                </div>
              
                <div class="col-md-4 mt-5">
                  <img style="height: 30px" src="images/pdf.png" alt="" />
                  <a href="../uploads/fillable_documents/EMV_VT_Fleet_Maintenance_Repair_Addendum_v0622.pdf" target="__blank">FLEET MAINTENANCE FORM</a>
                </div>
              
                <div class="col-md-4 mt-5">
                  <img style="height: 30px" src="images/pdf.png" alt="" />
                  <a href="../uploads/fillable_documents/TNG EMV Inspection v0622 Fillable.pdf" target="__blank">INSPECTION FORM</a>
                </div>
              
                <div class="col-md-4 mt-5">
                  <img style="height: 30px" src="images/pdf.png" alt="" />
                  <a href="../uploads/fillable_documents/VT TNG EMV MWR v0622 F Fillable.pdf" target="__blank">MAINTENANCE AND WEARABLES FILLABLE PDF</a>
                </div>
              <?php } ?>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/fillable_documents/EMV_TSG_Addendum_v1123_fillables.pdf" target="__blank">SMALL GOODS ADDENDUM FILLABLE PDF </a>
              </div>

              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/fillable_documents/EMV TSG Approval Request v0622 F Fillable.pdf" target="__blank">SMALL GOODS COVERAGE REQUEST FILLABLE PDF </a>
              </div>

              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/fillable_documents/EMV_VT_Transfer_Request_v0622_ECM500.pdf" target="__blank">TRANSFER REQUEST FORM</a>
              </div>
              <?php
                if (false) {
              ?>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/fillable_documents/TNG EMV T1 WA ENG v0622 F Fillable.pdf" target="__blank">TYPE 1 WARRANTY CONTRACT FILLABLE PDF </a>
              </div>

              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/fillable_documents/TNG EMV T2 WA ENG v0622 F Fillable.pdf" target="__blank">TYPE 2 WARRANTY CONTRACT FILLABLE PDF </a>
              </div>

              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/fillable_documents/TNG EMV T3 WA ENG v0622 F Fillable.pdf" target="__blank">TYPE 3 WARRANTY CONTRACT FILLABLE PDF </a>
              </div>
              <?php } ?>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/fillable_documents/VT TNG GARA v0622 F Fillable.pdf" target="__blank">VITAL TRENDS DEALER AGREEMENT FILLABLE PDF</a>
              </div>
              <?php
                if (false) {
              ?>
                <div class="col-md-4 mt-5">
                  <img style="height: 30px" src="images/pdf.png" alt="" />
                  <a href="../uploads/fillable_documents/VITAL TRENDS WEARABLES COVERAGE ADDENDUM v0324 _ Fillable.pdf" target="__blank">VITAL TRENDS WEARABLES COVERAGE ADDENDUM</a>
                </div>
              <?php } ?>
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
<script src="js/demo.js"></script>