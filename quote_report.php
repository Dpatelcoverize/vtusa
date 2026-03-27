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
$errorMessage = "";

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


// Get the adminID from session, or fail.
if (!(isset($_SESSION["admin_id"]))) {
    header("location: index.php");
    exit;
} else {
    $adminID = $_SESSION["admin_id"];
}

// Get the roleID from session, fail if not 1 - insist admin is using this report.
if (!(isset($_SESSION["roleID"]))) {
    header("location: index.php");
    exit;
} else {
    $roleID = $_SESSION["roleID"];

    if ($roleID != 1) {
        header("location: index.php");
        exit;
    }
}

//print_r($_SESSION);
//die();

if (isset($_POST['submit'])) {
    if (isset($_POST['filters'])) {
        $filters_field_value = $_POST['filters'];
        if ($filters_field_value === 'YTD' && isset($_POST['YearFilter']) && $_POST['YearFilter'] !== '') {
            $year = $_POST['YearFilter'];
            $query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v, Acct ac,Pers p  WHERE
                c.Created_Warranty_ID is NULL AND
                c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
                cd.Cntrct_Type_Cd='WQ' AND
                cd.Is_Deleted_Flg != 'Y' AND
                c.Veh_ID = v.Veh_ID AND
                ac.Acct_ID = c.Mfr_Acct_ID AND
                p.Pers_ID = c.Pers_Who_Signd_Cntrct_ID AND
                Year(cd.Contract_Date) = $year ORDER BY cd.Contract_Date DESC, cd.Cstmr_Nme DESC, ac.Acct_Nm DESC";
        } else if ($filters_field_value === 'Month' && isset($_POST['MonthFilter']) && $_POST['MonthFilter'] !== '') {
            // echo($_POST['MonthFilter']);
            $month = $_POST['MonthFilter'];
            $query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v, Acct ac, Pers p WHERE
                c.Created_Warranty_ID is NULL AND
                c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
                cd.Cntrct_Type_Cd='WQ' AND
                cd.Is_Deleted_Flg != 'Y' AND
                c.Veh_ID = v.Veh_ID AND
                ac.Acct_ID = c.Mfr_Acct_ID AND
                p.Pers_ID = c.Pers_Who_Signd_Cntrct_ID AND
                DATE_FORMAT(cd.Contract_Date, '%Y-%m') = '$month' ORDER BY cd.Contract_Date DESC, cd.Cstmr_Nme DESC, ac.Acct_Nm DESC";
        } else if ($filters_field_value === 'Date' && isset($_POST['DateFilter']) && $_POST['DateFilter'] !== '') {
            $date_filter = date('Y-m-d', strtotime($_POST['DateFilter']));
            $date = new DateTime($date_filter);
            $date = $date->format('Y-m-d');
            $query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v, Acct ac,Pers p WHERE
                c.Created_Warranty_ID is NULL AND
                c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
                cd.Cntrct_Type_Cd='WQ' AND
                cd.Is_Deleted_Flg != 'Y' AND
                c.Veh_ID = v.Veh_ID AND
                ac.Acct_ID = c.Mfr_Acct_ID AND
                p.Pers_ID = c.Pers_Who_Signd_Cntrct_ID AND
                DATE(cd.Contract_Date) = '$date' ORDER BY cd.Contract_Date DESC, cd.Cstmr_Nme DESC, ac.Acct_Nm DESC";
        } else {
            if ($filters_field_value === 'YTD') {
                $errorMessage = "Please Select Year Filter..!";
            } else if ($filters_field_value === 'Month') {
                $errorMessage = "Please Select Month Filter..!";
            } else if ($filters_field_value === 'Date') {
                $errorMessage = "Please Select Date Filter..!";
            }
            //  $errorMessage="Please select require field to filter";

            // $query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v, Acct ac WHERE
            // c.Created_Warranty_ID is NULL AND
            // c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
            // cd.Cntrct_Type_Cd='WQ' AND
            // cd.Is_Deleted_Flg != 'Y' AND
            // c.Veh_ID = v.Veh_ID AND
            // ac.Acct_ID = c.Mfr_Acct_ID ORDER BY cd.Contract_Date DESC, cd.Cstmr_Nme DESC, ac.Acct_Nm DESC";
        }
    } else {
        $errorMessage = "Please Select Filter Type..!";
    }
} else {
    $query = "SELECT Pers_Who_Signd_Cntrct_ID,Contract_Date,Cstmr_Nme,Veh_Model_Yr_Cd,
				Veh_Gross_Wgt_Cnt,Veh_New_Flg,Cntrct_Term_Mnths_Nbr,Cntrct_Lvl_Desc,
				Wrap_Flg,Dlr_Cost_Amt,Dlr_Mrkp_Actl_Amt,MSRP_Amt,AEP_Flg,APU_Flg,
				Small_Goods_Pkg_Flg,Aerial_Flg,Acct_Nm,
				Pers_Full_Nm, p.Pers_Nbr, p.Affl_Fee_Amt
			FROM
				Cntrct c, Cntrct_Dim cd, Veh v, Acct ac, Pers p
			WHERE
    c.Created_Warranty_ID is NULL AND
    c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
    cd.Cntrct_Type_Cd='WQ' AND
    cd.Is_Deleted_Flg != 'Y' AND
    c.Veh_ID = v.Veh_ID AND
	p.Pers_ID = c.Pers_Who_Signd_Cntrct_ID AND
    ac.Acct_ID = c.Mfr_Acct_ID ORDER BY cd.Contract_Date DESC, cd.Cstmr_Nme DESC, ac.Acct_Nm DESC";
}


if ($errorMessage === "") {
    $quoteResult = $link->query($query);
}


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
            <div class="col-sm-12">
                <form name='filters_form' action="quote_report.php" method="POST">
                    <div class="row">
                        <div class="col-sm-1">
                            <span><label for="cars">Filter By </label></span>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">

                                <span><select class="form-control" name="filters" id="filters">
                                        <option value="" selected="true" disabled>Select...</option>
                                        <option value="YTD">YTD</option>
                                        <option value="Month">Month</option>
                                        <option value="Date">Date</option>
                                    </select></span>
                            </div>
                        </div>
                        <div class="col-sm-1 DateFilter">
                            <label for="filter">Date:</label>
                        </div>
                        <div class="col-sm-1 MonthFilter">
                            <label for="filter">Month:</label>
                        </div>
                        <div class="col-sm-1 YearFilter">
                            <label for="filter">Year:</label>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <span><input type="date" id="DateFilter" class="DateFilter form-control" name="DateFilter"></span>


                                <span><input type="month" id="MonthFilter" class="MonthFilter form-control" name="MonthFilter"></span>


                                <span><select id="YearFilter" class="YearFilter form-control" name="YearFilter">
                                        <option value="" selected="true" disabled>Select...</option>
                                        <?php for ($i = 2050; $i >= 2015; $i--) { ?>
                                            <option value="<?php echo ($i) ?>"><?php echo ($i) ?></option>
                                        <?php } ?>
                                    </select></span>

                            </div>
                        </div>
                        <div class="col-sm-3">
                            <span><input type="submit" name="submit" class="btn btn-primary form-control" id="submit_filter"></span>
                        </div>
                    </div>
                </form>
                <?php if ($errorMessage !== '') { ?>
                    <div class="col-lg-12">
                        <span style="color:red;"><?php echo ($errorMessage) ?></span>
                    </div>
                <?php } ?>

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
                                        <th>Vehicle Year</th>
                                        <th>Type</th>
                                        <th>New / Used</th>
                                        <th>Term (Years)</th>
                                        <th>Tier</th>
                                        <th>Wrap</th>
                                        <th>Total Dealer Cost</th>
                                        <th>Dealer Markup</th>
                                        <th>Total Dealer MSRP</th>
                                        <th>AEP</th>
                                        <th>APU</th>
                                        <th>Small Goods</th>
                                        <th>Aerial</th>
                                        <!-- <th>Old Vehicle Surcharge</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($errorMessage === "") {
                                        /*
echo "test the db output speed";
date_default_timezone_set("America/New_York"); // Set timezone to Eastern Time (ET)
echo date("Y-m-d H:i:s");
echo "<br />-----------------------------";
//print_r($quoteResult);
echo "<br />-----------------------------";
echo date("Y-m-d H:i:s");
*/

                                        while ($row = mysqli_fetch_assoc($quoteResult)) {
                                            // print("<pre>");
                                            // print_r($row);
                                            // print ("</pre>");

                                            //$query = "SELECT Acct_Nm FROM Acct WHERE Acct_ID = ".$row["Mfr_Acct_ID"];
                                            //$queryResult = $link->query($query);
                                            //$dealer = mysqli_fetch_assoc($queryResult);

                                            //Get Dealer Agent Information
                                            /*
                                                $dealerAgent = "";
                                                if($row["Pers_Who_Signd_Cntrct_ID"]){
                                                $query = "SELECT Pers_Full_Nm, Pers_Nbr, Affl_Fee_Amt FROM Pers WHERE Pers_ID = ".$row["Pers_Who_Signd_Cntrct_ID"];
                                                $queryResult = $link->query($query);
                                                $dealerAgent = mysqli_fetch_assoc($queryResult);
                                                }
*/

                                            /*
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
*/
                                    ?>
                                            <tr>
                                                <td><?php if ($adminUsername) {
                                                        echo ($adminUsername);
                                                    } ?></td>
                                                <td><?php
                                                    //if($dealer) { echo $dealer["Acct_Nm"]; }
                                                    echo $row["Acct_Nm"];
                                                    ?></td>
                                                <td><?php if ($row["Pers_Who_Signd_Cntrct_ID"]) {
                                                        echo $row["Pers_Full_Nm"];
                                                    }
                                                    ?></td>
                                                <td><?php echo $row["Contract_Date"]; ?></td>
                                                <td><?php echo $row["Cstmr_Nme"]; ?></td>
                                                <td><?php echo $row["Veh_Model_Yr_Cd"]; ?></td>
                                                <td><?php echo $row["Veh_Gross_Wgt_Cnt"]; ?></td>
                                                <td><?php if ($row["Veh_New_Flg"] == "Y") {
                                                        echo "New";
                                                    } else {
                                                        echo "Used";
                                                    } ?></td>
                                                <td><?php echo $row["Cntrct_Term_Mnths_Nbr"]; ?></td>
                                                <td><?php echo $row["Cntrct_Lvl_Desc"]; ?></td>
                                                <td><?php if ($row["Wrap_Flg"] == "Y") {
                                                        echo "Yes";
                                                    } else {
                                                        echo "No";
                                                    } ?></td>
                                                <td>$<?php echo number_format($row["Dlr_Cost_Amt"], 2); ?></td>
                                                <td>$<?php echo number_format($row["Dlr_Mrkp_Actl_Amt"], 2); ?></td>
                                                <td>$<?php echo number_format($row["MSRP_Amt"], 2); ?></td>
                                                <td><input type="checkbox" <?php if ($row["AEP_Flg"] === "Y") {
                                                                                echo "checked='checked'";
                                                                            } ?> disabled></td>
                                                <td><input type="checkbox" <?php if ($row["APU_Flg"] === "Y") {
                                                                                echo "checked='checked'";
                                                                            } ?> disabled></td>
                                                <td><input type="checkbox" <?php if ($row["Small_Goods_Pkg_Flg"] === "Y") {
                                                                                echo "checked='checked'";
                                                                            } ?> disabled></td>
                                                <td><input type="checkbox" <?php if ($row["Aerial_Flg"] === "Y") {
                                                                                echo "checked='checked'";
                                                                            } ?> disabled></td>
                                                <!-- <td></td> -->


                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                            <?php
                            //echo "<br />-----------------------------";
                            //echo date("Y-m-d H:i:s");
                            //die();
                            ?>
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
<script src="js/demo.js"></script>
<script>
    $(document).ready(function() {
        $('#finance_table').DataTable();
        $(".YearFilter").hide();
        $(".MonthFilter").hide();
        $(".DateFilter").hide();
        $("#submit_filter").hide();
        $("#filters").change(function() {
            // Pure JS
            var selectedVal = this.value;
            if (selectedVal === "YTD") {
                $(".YearFilter").show();
                $(".MonthFilter").hide();
                $(".DateFilter").hide();
                $("#MonthFilter").val('');
                $("#DateFilter").val('');
            } else if (selectedVal === "Month") {
                $(".YearFilter").hide();
                $(".MonthFilter").show();
                $(".DateFilter").hide();
                $("#YearFilter").val('');
                $("#DateFilter").val('');
            } else if (selectedVal === "Date") {
                $(".YearFilter").hide();
                $(".MonthFilter").hide();
                $(".DateFilter").show();
                $("#YearFilter").val('');
                $("#MonthFilter").val('');

            }
            $("#submit_filter").show();
        });
    })
</script>
</body>

</html>