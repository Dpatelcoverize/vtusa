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

if ($dealerID != "" && $userType == "dealer"){

    /* get Pdf link */
		$dealerInfoQuery = "SELECT * FROM Acct WHERE Acct_ID=" . $dealerID .";";
        $dealerInfo = $link->query($dealerInfoQuery);

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

        //$dealerQuote = "SELECT Path_to_File,File_Asset_Type_ID FROM File_Assets WHERE Acct_ID=" . $dealerID ." AND File_Asset_Type_ID = 6";
        $dealerQuote = "SELECT fa.Path_to_File,fa.File_Asset_Type_ID, cd.Cstmr_Nme, c.Qte_Dt FROM
                         File_Assets fa, Cntrct c, Cntrct_Dim cd WHERE
                         fa.Acct_ID=" . $dealerID ." AND fa.File_Asset_Type_ID = 6 AND
                         fa.Dealer_Cntrct_ID=c.Cntrct_ID AND
                         c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID";
        $dealerQuote = $link->query($dealerQuote);

        //$dealerWarranty = "SELECT Path_to_File,File_Asset_Type_ID FROM File_Assets WHERE Acct_ID=" . $dealerID ." AND File_Asset_Type_ID = 7";
        $dealerWarranty = "SELECT fa.Path_to_File,fa.File_Asset_Type_ID, cd.Cstmr_Nme, c.Qte_Dt FROM
                         File_Assets fa, Cntrct c, Cntrct_Dim cd WHERE
                         fa.Acct_ID=" . $dealerID ." AND fa.File_Asset_Type_ID = 7 AND
                         fa.Dealer_Cntrct_ID=c.Cntrct_ID AND
                         c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID";
        $dealerWarranty = $link->query($dealerWarranty);


		// Get acct info
		$dealerInfoRow = $dealerInfo->fetch_assoc();
		$accountName = $dealerInfoRow["Acct_Nm"];
		$executedPDF = $dealerInfoRow["Dlr_Agrmnt_Exctd_PDF"];

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
                                <h4 class="card-title">Generated PDF Files</h4>
                            </div>
                            <div class="card-body">
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
                                    <a href="<?php echo $pdf['Path_to_File']; ?>" target="_blank"><?php echo $pdf['Cstmr_Nme'];?> (<?php echo $pdf['Qte_Dt'];?>)</a>
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
                                    <a href="<?php echo $pdf['Path_to_File']; ?>" target="_blank"><?php echo $pdf['Cstmr_Nme'];?> (<?php echo $pdf['Qte_Dt'];?>)</a>
                                    </div>
                                    <?php } ?>
                                </div>
                                <?php
                             }
                            ?>


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
                                    <a href="<?php echo $pdf['Path_to_File']; ?>" target="_blank">View Retailer Signed File for <?php echo $accountName;?></a>
                                    </div>
                                    <?php } ?>
                                <?php
								if($executedPDF!=""){
								?>
                                    <div class="col-md-3">
                                    <img style="height: 30px;" src="images/pdf.png" alt="">
                                    <a href="<?php echo $executedPDF; ?>" target="_blank">View Executed File for <?php echo $accountName;?></a>
                                    </div>
								<?php
								}
								?>
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
                                    <a href="<?php echo $pdf['Path_to_File']; ?>" target="_blank">View File</a>
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
                                    <a href="<?php echo $pdf['Path_to_File']; ?>" target="_blank">View File</a>
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
                                    <a href="<?php echo $pdf['Path_to_File']; ?>" target="_blank">View File</a>
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
                                    <a href="<?php echo $pdf['Path_to_File']; ?>" target="_blank">View File</a>
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