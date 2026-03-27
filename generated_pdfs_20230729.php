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
$pageTitle = "Contracts";


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
	$adminID = $_SESSION["admin_id"];
    $userType = $_SESSION["userType"];
}


// Get an error message from session if applicable.
if ((isset($_SESSION["errorMessage"]))) {
	$errorMessage = $_SESSION["errorMessage"];
	$_SESSION["errorMessage"] = "";
} else {
	$errorMessage = "";
}

require_once("includes/header.php");


if ($dealerID != "" && $userType == "dealer"){

    /* get Pdf link */
		$dealerAgreement = "SELECT Path_to_File,File_Asset_Type_ID FROM File_Assets WHERE Acct_ID=" . $dealerID ." AND File_Asset_Type_ID = 1";
        $dealerAgreement = $link->query($dealerAgreement);

        $dealerw9 = "SELECT Path_to_File,File_Asset_Type_ID FROM File_Assets WHERE Acct_ID=" . $dealerID ." AND File_Asset_Type_ID = 2";
        $dealerw9 = $link->query($dealerw9);

        $dealerAddendum = "SELECT Path_to_File,File_Asset_Type_ID FROM File_Assets WHERE Acct_ID=" . $dealerID ." AND File_Asset_Type_ID = 3";
        $dealerAddendum = $link->query($dealerAddendum);

        $dealerAffiliate = "SELECT Path_to_File,File_Asset_Type_ID FROM File_Assets WHERE Acct_ID=" . $dealerID ." AND File_Asset_Type_ID = 4";
        $dealerAffiliate = $link->query($dealerAffiliate);

        $dealerSetup = "SELECT Path_to_File,File_Asset_Type_ID FROM File_Assets WHERE Acct_ID=" . $dealerID ." AND File_Asset_Type_ID = 5";
        $dealerSetup = $link->query($dealerSetup);

		// If the current user is a dealer agent, or an agency agent, then they should only see
		//  the PDFs that they wrote themselves.

/*
echo "dealerID=".$dealerID;
echo "<br />";
echo "userID=".$userID;
echo "<br />";
echo "role_ID=".$role_ID;
echo "<br />";
echo "adminID=".$adminID;
echo "<br />";
die();
*/
		if(($role_ID == 4) || ($role_ID == 6)){
	        $dealerQuote = "SELECT Path_to_File,File_Asset_Type_ID FROM File_Assets f WHERE
	                         Acct_ID=" . $dealerID ." AND
	                         File_Asset_Type_ID = 6 AND
	                         f.VT_Pers_ID=(SELECT Pers_ID FROM Users WHERE userID='.$userID.')";
		}else{
	        $dealerQuote = "SELECT Path_to_File,File_Asset_Type_ID FROM File_Assets WHERE Acct_ID=" . $dealerID ." AND File_Asset_Type_ID = 6";
		}
        $dealerQuote = $link->query($dealerQuote);

        $dealerWarranty = "SELECT Path_to_File,File_Asset_Type_ID FROM File_Assets WHERE Acct_ID=" . $dealerID ." AND File_Asset_Type_ID = 7";
        $dealerWarranty = $link->query($dealerWarranty);

}


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
                                <h4 class="card-title">Generated PDF Files</h4>
                            </div>
                            <div class="card-body">
                                <?php
                                if($dealerAgreement){
                                ?>
                                <h5>Dealer Agreement PDFs</h5>
                                <hr>
                                <div class="row text-center" style="padding-bottom: 60px">
                                <?php
                                foreach($dealerAgreement as $pdf)
                                {
                                ?>
                                    <div class="col-md-3">
                                    <img style="height: 30px;" src="images/pdf.png" alt="">
                                    <a href="<?php echo $pdf['Path_to_File']; ?>">View File</a>
                                    </div>
                                    <?php } ?>
                                </div>
                                <?php
                               }
                            ?>

                            <?php
                            if($dealerw9)
                            { ?>
                                <h5>Dealer W-9 PDFs</h5>
                                <hr>
                                <div class="row text-center" style="padding-bottom: 60px">
                                <?php
                                foreach($dealerw9 as $pdf)
                                { ?>
                                    <div class="col-md-3">
                                    <img style="height: 30px;" src="images/pdf.png" alt="">
                                    <a href="<?php echo $pdf['Path_to_File']; ?>">View File</a>
                                    </div>
                                    <?php } ?>
                                </div>
                                <?php
                             }
                            ?>

                          <?php
                            if($dealerSetup)
                            { ?>
                               <h5>Dealer Setup PDFs</h5>
                               <hr>
                                <div class="row text-center" style="padding-bottom: 60px">
                                <?php
                                foreach($dealerSetup as $pdf)
                                { ?>
                                    <div class="col-md-3">
                                    <img style="height: 30px;" src="images/pdf.png" alt="">
                                    <a href="<?php echo $pdf['Path_to_File']; ?>">View File</a>
                                    </div>
                                    <?php } ?>
                                </div>
                                <?php
                             }
                            ?>

                         <?php
                            if($dealerAddendum)
                            { ?>
                               <h5>Dealer Addendum PDFs</h5>
                               <hr>
                                <div class="row text-center" style="padding-bottom: 60px">
                                <?php
                                foreach($dealerAddendum as $pdf)
                                { ?>
                                    <div class="col-md-3">
                                    <img style="height: 30px;" src="images/pdf.png" alt="">
                                    <a href="<?php echo $pdf['Path_to_File']; ?>">View File</a>
                                    </div>
                                    <?php } ?>
                                </div>
                                <?php
                             }
                            ?>

                         <?php
                            if($dealerAffiliate)
                            { ?>
                               <h5>Dealer Affiliate PDFs</h5>
                               <hr>
                                <div class="row text-center" style="padding-bottom: 60px">
                                <?php
                                foreach($dealerAffiliate as $pdf)
                                { ?>
                                    <div class="col-md-3">
                                    <img style="height: 30px;" src="images/pdf.png" alt="">
                                    <a href="<?php echo $pdf['Path_to_File']; ?>">View File</a>
                                    </div>
                                    <?php } ?>
                                </div>
                                <?php
                             }
                            ?>

                          <?php
                            if($dealerQuote)
                            { ?>
                               <h5>Dealer Quotes PDFs</h5>
                               <hr>
                                <div class="row text-center" style="padding-bottom: 60px">
                                <?php
                                foreach($dealerQuote as $pdf)
                                { ?>
                                    <div class="col-md-3">
                                    <img style="height: 30px;" src="images/pdf.png" alt="">
                                    <a href="<?php echo $pdf['Path_to_File']; ?>">View File</a>
                                    </div>
                                    <?php } ?>
                                </div>
                                <?php
                             }
                            ?>

                           <?php
                            if($dealerWarranty)
                            { ?>
                              <h5>Dealer Warranty PDFs</h5>
                              <hr>
                                <div class="row text-center" style="padding-bottom: 60px">
                                <?php
                                foreach($dealerWarranty as $pdf)
                                { ?>
                                    <div class="col-md-3">
                                    <img style="height: 30px;" src="images/pdf.png" alt="">
                                    <a href="<?php echo $pdf['Path_to_File']; ?>">View File</a>
                                    </div>
                                    <?php } ?>
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