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
            <h4 class="card-title">Marketing Materials</h4>
          </div>
          <div class="card-body">
            <div class="row" style="padding-bottom: 60px">
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/marketing/Flagship Tri-Fold Brochure - FINAL PDF.pdf" target="__blank">FLAGSHIP BROCHURE</a>
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/marketing/Wrap Tri-Fold Brochure - FINAL PDF.pdf" target="__blank">WRAP BROCHURE</a>
              </div>
              <div class="col-md-4 mt-5">
                <img style="height: 30px" src="images/pdf.png" alt="" />
                <a href="../uploads/marketing/VT Desk Mat 16x20_USfinal.pdf" target="__blank">DESKMAT</a>
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
<script src="js/demo.js"></script>