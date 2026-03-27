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
$pageTitle = "Samples";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

// Email function
require_once "lib/emailHelper.php";

// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');


session_start();

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
            <h4 class="card-title">Sample Documents</h4>
          </div>
          <div class="card-body">
            <div class="row" style="padding-bottom: 60px">
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/samples/EMV TNG VT ECA v0722 SAMPLE.pdf"
                  target="__blank"
                  >EQUIPMENT CONDITION AFFIDAVIT (ECA)</a
                >
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/samples/EMV TSG Approval Request v0622 SAMPLE.pdf"
                  target="__blank"
                  >SAMPLE COVERAGE REQUEST SMALL GOODS </a
                >
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/samples/VT TNG GARA Complete v0622 SAMPLE.pdf"
                  target="__blank"
                  >SAMPLE DEALER AGREEMENT</a
                >
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/samples/TNG EMV Inspection v0622 SAMPLE.pdf"
                  target="__blank"
                  >SAMPLE INSPECTION FORM</a
                >
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/samples/VT TNG EMV MWR 0622 SAMPLE.pdf"
                  target="__blank"
                  >SAMPLE MAINTENANCE AND WEARABLES</a
                >
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/samples/EMV VT Retailer Wholesale Rate Card v062822 SAMPLE.pdf"
                  target="__blank"
                  >SAMPLE RATES - DEALER COST</a
                >
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/samples/EMV TSG Addendum v0622 SAMPLE.pdf"
                  target="__blank"
                >
                SAMPLE SMALL GOODS ADDENDUM</a
                >
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/wrap_program/SAMPLE_TNG_EMV_WRAP_WA_ENG_v0323.pdf"
                  target="__blank"
                  >SAMPLE WRAP PROGRAM WARRANTY</a
                >
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/wrap_program/Dealer_Cost_EMV_Wrap_Rate_Card_v0523.pdf"
                  target="__blank"
                  >SAMPLE WRAP RATES - DEALER COST</a
                >
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/wrap_program/MSRP_EMV_Wrap_Rate_Card_v0523.pdf"
                  target="__blank"
                  >SAMPLE WRAP RATES - MSRP</a
                >
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/samples/TNG EMV T1 WA ENG v0622 SAMPLE.pdf"
                  target="__blank"
                  >TYPE 1 SAMPLE CONTRACT</a
                >
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/samples/TNG EMV T2 WA ENG v0622 SAMPLE.pdf"
                  target="__blank"
                  >TYPE 2 SAMPLE CONTRACT</a
                >
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a
                  href="../uploads/samples/TNG EMV T3 WA ENG v0622 SAMPLE.pdf"
                  target="__blank"
                  >TYPE 3 SAMPLE CONTRACT</a
                >
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
