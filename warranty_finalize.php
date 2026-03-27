<?php
//
// File: warranty_finalize.php
// Author: Charles Parry
// Date: 6/04/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Finalize Warranty";
$pageTitle = "Finalize Warranty";


// Connect to DB
require_once "includes/dbConnect.php";


// Variables.
$dealerID = "";
$acctID = "";

$coverageTerm = "";
$vehicleType = "";
$tierType = "";
$aepFlag = "";
$aerialFlag = "";
$smallGoodsFlag = "";

$Sales_Agt_Cost_Amt = "";
$Sales_Agt_Commission_Amt = "";
$Dlr_Cost_Amt = "";
$Dlr_Mrkp_Max_Amt = "";
$MSRP_Amt = "";


$form_err    = "";


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




// Get the warrantyID from the URL
// Are we loading a saved Warranty record, to continue editing?
if(isset($_GET["warrantyID"])){
	$warrantyID = $_GET["warrantyID"];
	$_SESSION["warrantyID"] = $warrantyID;

	$sql = "SELECT * FROM New_Warranty_Temp WHERE New_Warranty_Temp_ID=".$_GET["warrantyID"];
	$result = $link->query($sql);
	$row = $result->fetch_assoc();

	// Get the fields we need from the warranty to look up pricing information.
	$acctID = $row["Acct_ID"];
	$coverageTerm = $row["Coverage_Term"];
	$vehicleType = $row["Vehicle_Type"];
	$tierType = $row["Tier_Type"];
	$aepFlag = $row["Apparatus_Equipment_Package"];
	$aerialFlag = $row["Aerial_Package"];
	$smallGoodsFlag = $row["Small_Goods_Package"];



	// Now that we have all of these flags, we can look up the warranty pricing information
	$sqlString  = "SELECT Sales_Agt_Cost_Amt,Sales_Agt_Commission_Amt,Dlr_Cost_Amt,Dlr_Mrkp_Max_Amt,MSRP_Amt FROM ";
	$sqlString .= "Wrnty_Std_Prcg WHERE Cvrg_Tern_Yrs_Nbr=".$coverageTerm." AND ";
	$sqlString .= "Veh_Type_Cd='".$vehicleType."' AND ";
	$sqlString .= "Tier_Type_Cd='".$tierType."' AND ";
	$sqlString .= "AEP_Flg='".$aepFlag."' AND ";
	$sqlString .= "Aerl_Flg='".$aerialFlag."'";
	$result = $link->query($sqlString);
	$row = $result->fetch_assoc();

	// Get the fields we need from the warranty to look up pricing information.
	$Sales_Agt_Cost_Amt = $row["Sales_Agt_Cost_Amt"];
	$Sales_Agt_Commission_Amt = $row["Sales_Agt_Commission_Amt"];
	$Dlr_Cost_Amt = $row["Dlr_Cost_Amt"];
	$Dlr_Mrkp_Max_Amt = $row["Dlr_Mrkp_Max_Amt"];
	$MSRP_Amt = $row["MSRP_Amt"];


/*
echo "<br/>sql=".$sqlString;
echo "<br/>coverageTerm=".$coverageTerm;
echo "<br/>vehicleType=".$vehicleType;
echo "<br/>tierType=".$tierType;
echo "<br/>aepFlag=".$aepFlag;
echo "<br/>aerialFlag=".$aerialFlag;
*/

//	$stmt = $link->prepare($sqlString);

	/* Bind variables to parameters */
/*
	$val1 = $coverageTerm;
	$val2 = $vehicleType;
	$val3 = $tierType;
	$val4 = $aepFlag;
	$val5 = $aerialFlag;

	$stmt->bind_param("issss",$val1,$val2,$val3,$val4,$val5);

	$stmt->execute();
	$stmt->bind_result($Sales_Agt_Cost_Amt,$Sales_Agt_Commission_Amt,$Dlr_Cost_Amt,$Dlr_Mrkp_Max_Amt,$MSRP_Amt);
	$stmt->fetch();

echo "<br/>Sales_Agt_Cost_Amt=".$Sales_Agt_Cost_Amt;
echo "<br/>Sales_Agt_Commission_Amt=".$Sales_Agt_Commission_Amt;
echo "<br/>Dlr_Cost_Amt=".$Dlr_Cost_Amt;
echo "<br/>Dlr_Mrkp_Max_Amt=".$Dlr_Mrkp_Max_Amt;
echo "<br/>MSRP_Amt=".$MSRP_Amt;
echo "<br/>acctID=".$acctID;
echo "<br/>warrantyID=".$warrantyID;
*/

	// And now that we have these pricing values, save them back to the fields in the warranty itself.
	$sqlString  = "UPDATE New_Warranty_Temp SET Sales_Agt_Cost_Amt=".$Sales_Agt_Cost_Amt.",";
	$sqlString .= "Sales_Agt_Commission_Amt=".$Sales_Agt_Commission_Amt.",";
	$sqlString .= "Dlr_Cost_Amt=".$Dlr_Cost_Amt.",";
	$sqlString .= "Dlr_Mrkp_Amt=".$Dlr_Mrkp_Max_Amt.",";
	$sqlString .= "MSRP_Amt=".$MSRP_Amt." ";
	$sqlString .= "WHERE Acct_ID=".$acctID." AND New_Warranty_Temp_ID=".$warrantyID;
	$result = $link->query($sqlString);


// Having trouble getting prepared statement to work here, need to revisit.

/*
	$stmt = $link->prepare($sqlString);
*/
/*
echo "sql=".$sqlString;
echo "<br />Sales_Agt_Cost_Amt=".$Sales_Agt_Cost_Amt;
echo "<br />Sales_Agt_Commission_Amt=".$Sales_Agt_Commission_Amt;
echo "<br />Dlr_Cost_Amt=".$Dlr_Cost_Amt;
echo "<br />Dlr_Mrkp_Max_Amt=".$Dlr_Mrkp_Max_Amt;
echo "<br />MSRP_Amt=".$MSRP_Amt;
echo "<br />acctID=".$acctID;
echo "<br />warrantyID=".$warrantyID;
*/
	/* Bind variables to parameters */
/*
	$val1 = $Sales_Agt_Cost_Amt;
	$val2 = $Sales_Agt_Commission_Amt;
	$val3 = $Dlr_Cost_Amt;
	$val4 = $Dlr_Mrkp_Max_Amt;
	$val5 = $MSRP_Amt;
	$val6 = $acctID;
	$val7 = $warrantyID;

	$stmt->bind_param("dddddii", $val1,$val2,$val3,$val4,$val5,$val6,$val7);
	$stmt->execute();

die();
*/

if(false){
	$stmt2 = mysqli_prepare($link, "UPDATE New_Warranty_Temp SET Sales_Agt_Cost_Amt=4 WHERE Acct_ID=1032 AND New_Warranty_Temp_ID=12");

	/* Bind variables to parameters */

	$val1 = $Sales_Agt_Cost_Amt;
	$val2 = $Sales_Agt_Commission_Amt;
	$val3 = $Dlr_Cost_Amt;
	$val4 = $Dlr_Mrkp_Max_Amt;
	$val5 = $MSRP_Amt;
	$val6 = $acctID;
	$val7 = $warrantyID;

//	mysqli_stmt_bind_param($stmt, "iiiiiii", $val1,$val2,$val3,$val4,$val5,$val6,$val7);
	mysqli_stmt_bind_param($stmt2, "ii",$val6,$val7);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt2);

	die();
}


}else{
	// This page must act on a single warranty, so fail out if one was not supplied.
	header("location: create_warranty.php");
	exit;

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

                <!-- row -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Warranty #<?php echo $warrantyID;?></h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
									<div class="watermark">
										<img src="images/logo_large_bg.png" alt="">
									</div>
                                    <table class="table table-responsive-md">
                                        <thead>
                                            <tr>
                                                <th>Sales Agent Cost</th>
                                                <th>Sales Agent Commission</th>
                                                <th>Dealer Cost</th>
                                                <th>Dealer Markup</th>
                                                <th>MSRP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php

// Get people associated with this dealerID
$query = "SELECT * FROM New_Warranty_Temp WHERE New_Warranty_Temp_ID=".$warrantyID;

$warrantyResult = $link->query($query);

if (mysqli_num_rows($warrantyResult) > 0) {
  // output data of each row
  $loopCounter = 0;
  while($row = mysqli_fetch_assoc($warrantyResult)) {
	$loopCounter++;
?>
<tr>
	<td><?php echo $row["Sales_Agt_Cost_Amt"];?></td>
	<td><?php echo $row["Sales_Agt_Commission_Amt"];?></td>
	<td><?php echo $row["Dlr_Cost_Amt"];?></td>
	<td><?php echo $row["Dlr_Mrkp_Amt"];?></td>
	<td><?php echo $row["MSRP_Amt"];?></td>
</tr>

<?php
  }
} else {
?>
<tr>
	<td colspan="5">No warranties found, yet.</td>
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

            <!--**********************************
                dealer_agreement form
            ***********************************-->

<!--            <div class="container-fluid">-->
<!--                <div class="row">-->
<!--                    <div class="col-md-12">-->
<!--                        <div class="card" id="toprint">-->
<!--                            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="table-layout: fixed; width: 100%; border-collapse: collapse;" >-->
<!--                                <tbody>-->
<!--                                    <tr>-->
<!--                                        <td>-->
<!--                                            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="table-layout: fixed; width: 100%; border-collapse: collapse;">-->
<!--                                                <tbody>-->
<!--                                                <tr>-->
<!--                                                    <td>-->
<!--                                                        <a href="index.php">-->
<!--                                                            <img src="images/vt_logo.png" alt="Vital Trends" />-->
<!--                                                        </a>-->
<!--                                                    </td>-->
<!--                                                    <td>-->
<!--                                                        <table  align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" >-->
<!--                                                            <tbody>-->
<!--                                                            <tr>-->
<!--                                                                <td style="text-align: left">-->
<!--                                                                    <h3>VITAL TRENDS DEALER AGREEMENT</h3>-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">SALE AGENT</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">ACCOUNT#</label>-->
<!--                                                                    <input type="number" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            </tbody>-->
<!--                                                        </table>-->
<!---->
<!---->
<!--                                                    </td>-->
<!--                                                </tr>-->
<!--                                                <tr>-->
<!--                                                    <td colspan="2" style="border: 1px solid #000;">-->
<!--                                                        <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="table-layout: fixed; width: 100%; border-collapse: collapse;">-->
<!--                                                            <tbody>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">BUSINESS NAME:</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">DEALER LICENSE#</label>-->
<!--                                                                    <input type="number" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">DBA:</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">CONTACT:</label>-->
<!--                                                                    <input type="phone" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">MAILING ADDRESS:</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">OWNER:</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            </tbody>-->
<!--                                                        </table>-->
<!--                                                    </td>-->
<!--                                                </tr>-->
<!--                                                </tbody>-->
<!--                                            </table>-->
<!--                                        </td>-->
<!--                                    </tr>-->
<!--                                </tbody>-->
<!--                            </table>-->
<!--                            <div class="card-body">-->
<!--                                <div class="basic-form">-->
<!--                                    <div class="body">-->
<!--                                        <div id="dialog" title="Digital Signature">-->
<!--                                            <canvas id="myCanvasSignature" acknowledged-data="false" class="signature-pad" width="400" height="100"></canvas>-->
<!--                                            <input id="UserName" name="UserName" type="hidden" value="My Signature">-->
<!--                                        </div>-->
<!--                                        <div id="change-sig" title="Edit Signature">-->
<!--                                            Please provide your new digital signature representation.<br/><br/>-->
<!--                                            <canvas id="signature-pad" class="signature-pad" width="400" height="100"></canvas>-->
<!--                                        </div>-->
<!--                                        <div class="signature-card">-->
<!--                                            <div class="legal-clause"></div>-->
<!--                                            <div class="sign-area">-->
<!--                                                <div class="sign-block pull-right">-->
<!--                                                    <div>Signature: <i class="fa fa-pencil" aria-hidden="true" onclick="Signature('jm38s4i1');"></i></div>-->
<!--                                                    <canvas id="Sig-jm38s4i1" class="dig-sig " sig-id-data="jm38s4i1" signed-data="false" width="400" height="100"></canvas>-->
<!--                                                </div>-->
<!--                                            </div>-->
<!--                                            <div class="clearfix"></div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="log-file">-->
<!--                                        <p>Log file</p>-->
<!--                                    </div>-->
<!--                                    <form>-->
<!--                                        <button class="btn btn-primary"  onclick="printpart()"/>Sign in</button>-->
<!--                                    </form>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
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