<?php
//
// File: small_goods_summary.php
// Author: Charles Parry
// Date: 7/31/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Small Goods Summary";
$pageTitle = "Small Goods Summary";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";


// Variables.
$dealerID = "";
$smallGoodsCoverageID = "";
$smallGoodsPricingID = "";
$warrantyID = "";
$vehicleID = "";
$errorMessage = "";

$itemCategoryCode = "";
$itemCategoryDesc = "";
$quantityCount = "";
$manufacturedYear = "";
$manufacturerName = "";
$modelNumber = "";
$serialNumber = "";
$makeNumber = "";
$limitOfLiabilityAmount = "";
$salesAgentCostAmount = "";
$salesAgentCommissionAmount = "";
$dealerCostAmount = "";
$dealerMarkupAmount = "";
$actualPriceAmount = "";
$haveReceipt = "";

$form_err    = "";

$currentSelectionsArray = array();
$loopCounter=0;


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


// See if we are specifying a warrantyID in the URL request.
if(isset($_GET["warrantyID"])){
	$_SESSION["warrantyID"] = $_GET["warrantyID"];
}


// Get a Warranty ID from session.
if(!(isset($_SESSION["warrantyID"]))){
    header("location: index.php");
    exit;
}else{
	$warrantyID = $_SESSION["warrantyID"];
}


// See if we have data for this warranty contract already saved
if($warrantyID!=""){

	$sql = "SELECT sum(Gnrc_Item_Cat_Qty_Cnt) as sumQty,Item_Cat_Type_Cd,Item_Cat_Type_Desc FROM
	        Sml_Goods_Cvge WHERE Is_Deleted_Flg!='Y' AND Cntrct_ID=".$warrantyID." group by `Sml_Goods_Gnrc_Prcg_ID`";
	$smallGoodsResult = $link->query($sql);

	$numRows = mysqli_num_rows($smallGoodsResult);
	if ($numRows > 0) {
		while($row = mysqli_fetch_assoc($smallGoodsResult)) {
			$loopCounter++;
			$currentSelectionsArray[$row["Item_Cat_Type_Cd"]]=$row["sumQty"];
		}
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
                <div class="row">
                    <div class="col-md-6">
                        <div>
                            <img src="images/VTPoweredbyTNG.png" alt="Vital Trends Powered by TruNorth">
                        </div>
                    </div>
                    <div class="col-md-6">
						&nbsp;
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header text-center">
                                <h4 class="card-title">Small Goods Summary</h4>
                                <?php
									if($errorMessage!=""){
										echo "<h5>Notice: ".$errorMessage."</h5>";
									}
                                ?>
                            </div>
                            <div class="card-header text-center">
                                <h5>(<a href="warranty_pending.php">Return To Pending Warranty List</a>)</h5>
							</div>
                            <div class="card-body">
                                <div class="basic-form dealer-form">
                                    <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>
									<div class="form-row">
										<div class="form-group col-md-12">
											<style>
												.alnright { text-align: right; }
												table td {
												  padding: 3px;
												}
											</style>
											<table border="1" cellpadding="5" cellspacing="5"  class='alnright'>
												<tr style="background-color:#201F58;color:#FFFFFF;font-weight:bold;text-align:center;">
													<td>Item Type</td>
													<td>Tools &amp; Small Goods Items</td>
													<td>Quantity</td>
													<td>Cost Per Item</td>
													<td>Total Cost</td>
												</tr>
												<?php

												// Look up the available small goods items
												$query = "SELECT * FROM Sml_Goods_Gnrc_Prcg ORDER BY Item_Cat_Type_Cd ASC";
												$smallGoodsResult = $link->query($query);

												$addendumTotal = 0;
												$extendedTotal = 0;
												if (mysqli_num_rows($smallGoodsResult) > 0) {
												  // output data of each row
												  $loopCounter = 0;
												  while($row = mysqli_fetch_assoc($smallGoodsResult)) {
													$loopCounter++;

													if(isset($currentSelectionsArray[$row["Item_Cat_Type_Cd"]])){
														$valuePlaceholder = $currentSelectionsArray[$row["Item_Cat_Type_Cd"]];
													?>
														<tr>
															<td><?php echo $row["Item_Cat_Type_Cd"]; ?></td>
															<td><?php echo $row["Item_Cat_Type_Desc"]; ?></td>
															<td><?php echo $valuePlaceholder;?></td>
															<td>$<?php echo number_format($row["MSRP_Amt"],0); ?></td>
															<td>$<?php echo number_format(($row["MSRP_Amt"] * $valuePlaceholder),0); ?></td>
														</tr>
													<?php

													}else{
														$valuePlaceholder = "0";
													}

													$addendumTotal += $row["MSRP_Amt"];
													$extendedTotal += ($row["MSRP_Amt"] * $valuePlaceholder);

												  }
												} else {
												?>
												<tr>
													<td colspan="4">
														<p>No Small Goods Found</p>
													</td>
												</tr>
												<?php
												}
												?>
												<tr style="background-color:#201F58;color:#FFFFFF;font-weight:bold;">
													<td>Totals</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>$<?php echo $extendedTotal; ?></td>
												</tr>
											</table>
										</div>
									</div>
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
<script src="./vendor/chart.js/Chart.bundle.min.js"></script>
<script src="./vendor/owl-carousel/owl.carousel.js"></script>

<!-- Chart piety plugin files -->
<script src="./vendor/peity/jquery.peity.min.js"></script>

<!-- Dashboard 1 -->
<script src="./js/custom.min.js"></script>
<script src="./js/deznav-init.js"></script>

<script src="./js/jSignature/jSignature.min.js"></script>
<script src="./js/jSignature/jSignInit.js"></script>

<script>
    function carouselReview(){
        /*  testimonial one function by = owl.carousel.js */
        function checkDirection() {
            var htmlClassName = document.getElementsByTagName('html')[0].getAttribute('class');
            if(htmlClassName == 'rtl') {
                return true;
            } else {
                return false;

            }
        }

        jQuery('.testimonial-one').owlCarousel({
            loop:true,
            autoplay:true,
            margin:30,
            nav:false,
            dots: false,
            rtl: checkDirection(),
            left:true,
            navText: ['', ''],
            responsive:{
                0:{
                    items:1
                },
                1200:{
                    items:2
                },
                1600:{
                    items:3
                }
            }
        })
    }
    jQuery(window).on('load',function(){
        setTimeout(function(){
            carouselReview();
        }, 1000);
    });
</script>
<script>
    function printpart () {
        var printwin = window.open("");
        printwin.document.write(document.getElementById("toprint").innerHTML);
        printwin.stop();
        printwin.print();
        printwin.close();
    }
</script>

</body>
</html>