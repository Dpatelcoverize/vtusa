<?php
//
// File: quote_report.php
// Author: Charles Parry
// Date: 11/25/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Quote Report";
$pageTitle = "Quote Report";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";


// Variables.
$dealerID = "";
$agreementDate = "";
$dealerName = "";
$dealerAddress1 = "";
$dealerAddress2 = "";
$dealerCity = "";
$dealerState = "";
$dealerZip = "";
$dealerLocationID = "";
$Dlr_Loc_Dim_ID = "";

$personFirstName = "";
$personLastName = "";
$personEmail = "";
$personPhone = "";

$notesField = "";

$form_err = "";


session_start();


// Get the adminID from session, or fail.
if (!(isset($_SESSION["admin_id"]))) {
    header("location: index.php");
    exit;
} else {
    $adminID = $_SESSION["admin_id"];
}


	// Finance
	$query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE
			  c.Created_Warranty_ID is NULL AND
	          c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
	          cd.Cntrct_Type_Cd='WQ' AND
	          cd.Is_Deleted_Flg != 'Y' AND
	          c.Veh_ID = v.Veh_ID ORDER BY Contract_Date DESC";

//echo "query=".$query;
//die();

	$quoteResult = $link->query($query);


require_once("includes/header.php");

?>

		<!--**********************************
            Content body start
        ***********************************-->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.jqueryui.min.css" integrity="sha512-x2AeaPQ8YOMtmWeicVYULhggwMf73vuodGL7GwzRyrPDjOUSABKU7Rw9c3WNFRua9/BvX/ED1IK3VTSsISF6TQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="./css/datatable.css">
        <div class="content-body">
            <!-- row -->
			<div class="container-fluid">
               <?php require_once("includes/common_page_content.php"); ?>
                <div class="row" style="margin-top: 2%;">
                    <div class="col-lg-12">
                        <div class="form-group col-md-12">

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                            </div>
                            <div class="card-header">
                                <h4 class="card-title">Quote report</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-responsive-md" id="finance_table">
                                        <thead>
                                            <tr>
                                                <th>VT Sales Agent</th>
                                                <th>Dealer Name</th>
                                                <th>Dealer Agent</th>
                                                <th>Quote Date</th>
                                                <th>Customer Name</th>
                                                <th>Vehicle Desc</th>
                                                <th>Vehicle Make</th>
                                                <th>Vehicle Model</th>
                                                <th>Vehicle Year</th>
                                                <th>Type</th>
                                                <th>New / Used</th>
                                                <th>Term (Years)</th>
                                                <th>Tier</th>
                                                <th>Total Dealer Cost</th>
                                                <th>Total Dealer Markup</th>
                                                <th>Total Dealer MSRP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($row = mysqli_fetch_assoc($quoteResult)) {

//print_r($row);
//die();
                                                $query = "SELECT Acct_Nm FROM Acct WHERE Acct_ID = ".$row["Mfr_Acct_ID"];
                                                $queryResult = $link->query($query);
                                                $dealer = mysqli_fetch_assoc($queryResult);

                                                //Get Dealer Agent Information
                                                $dealerAgent = "";
                                                if($row["Pers_Who_Signd_Cntrct_ID"]){
                                                $query = "SELECT Pers_Full_Nm, Pers_Nbr, Affl_Fee_Amt FROM Pers WHERE Pers_ID = ".$row["Pers_Who_Signd_Cntrct_ID"];
                                                $queryResult = $link->query($query);
                                                $dealerAgent = mysqli_fetch_assoc($queryResult);
                                                }

                                                //Get Vehicle Information
                                                $affiliateFee = "";
                                                if($row["Veh_ID"]){
                                                    $query = "SELECT Veh_Type_Nbr FROM Veh WHERE Veh_ID = ".$row["Veh_ID"];
                                                    $queryResult = $link->query($query);
                                                    $veh = mysqli_fetch_assoc($queryResult);
                                                    if($veh['Veh_Type_Nbr'] == '1')
                                                    {
                                                        $affiliateFee = "$200";
                                                    }
                                                   else if($veh['Veh_Type_Nbr'] == '2')
                                                    {
                                                        $affiliateFee = "$400";
                                                    }
                                                    else
                                                    {
                                                        $affiliateFee = "$600";
                                                    }
                                                }
                                                ?>
                                            <tr>
                                                <td><?php echo $row["Cstmr_Nme"]; ?></td>
                                                <td><?php if($dealer) { echo $dealer["Acct_Nm"]; } ?></td>
                                                <td><?php echo "dealer agent"; ?></td>
                                                <td><?php echo $row["Contract_Date"]; ?></td>
                                                <td><?php echo $row["Cstmr_Nme"]; ?></td>
                                                <td><?php echo $row["Veh_Desc"]; ?></td>
                                                <td><?php echo $row["Veh_Mk_Cd"]; ?></td>
                                                <td><?php echo $row["Veh_Model_Cd"]; ?></td>
                                                <td><?php echo $row["Veh_Model_Yr_Cd"]; ?></td>
                                                <td><?php echo $row["Veh_Gross_Wgt_Cnt"]; ?></td>
                                                <td><?php if($row["Veh_New_Flg"]=="Y"){echo "New";}else{echo "Used";} ?></td>
                                                <td><?php echo $row["Cntrct_Term_Mnths_Nbr"]; ?></td>
                                                <td><?php echo $row["Cntrct_Lvl_Desc"]; ?></td>
                                                <td>$<?php echo number_format($row["Dlr_Cost_Amt"],2); ?></td>
                                                <td>$<?php echo number_format($row["Dlr_Mrkp_Actl_Amt"],2); ?></td>
                                                <td>$<?php echo number_format($row["MSRP_Amt"],2); ?></td>
                                            </tr>
                                             <?php } ?>
                                        </tbody>
                                    </table>
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

		<!--**********************************
           Support ticket button start
        ***********************************-->

        <!--**********************************
           Support ticket button end
        ***********************************-->
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
	<script src="./js/dashboard/dashboard-1.js"></script>
    <script src="./js/custom.min.js"></script>
	<script src="./js/deznav-init.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
    <script>
        $(document).ready( function () {
          $('#finance_table').DataTable();
        } );
    </script>
</body>
</html>