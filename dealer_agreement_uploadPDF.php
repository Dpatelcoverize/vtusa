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
$pageTitle = "Quotes PDF";


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

// Get the AdminID so we can track sales agent for this dealer agreement.
$adminID = $_SESSION["admin_id"];

require_once("includes/header.php");
?>

<!--**********************************
            Content body start
        ***********************************-->
<div class="content-body">
<div class="container-fluid">
                <?php require_once("includes/common_page_content.php"); ?>
  <!-- row -->
  <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header text-center">
                                <h4 class="card-title">Dealer Agreement</h4>
								(Step 2)
                            </div>
                            <div class="card-body">
                                <div class="basic-form dealer-form">
                                    <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>
                                    <form action="#">
                                        <div class="row">
                                          <div class="form-group col-md-12">
                                              <input type="file">
                                              <span style="font-size: 14px; color: red;">Please upload downloaded PDF file with Signature</span>
										  </div>
                                          <div class="form-group col-md-12 mt-5">
                                          <button type="button" class="btn btn-primary" name="submit">Submit</button>
                                          </div>
                                        </div>
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
