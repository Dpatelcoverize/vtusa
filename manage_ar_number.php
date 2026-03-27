<?php
//
// File: manage_ar_number.php
// Author: Heli Thakore
// Date: 08/04/2024
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Dealer Addendum";
$pageTitle = "Dealer Addendum";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');

// Variables.
$dealerID = "";
$agreementDate = "";
$dealerName = "";
$dba = "";
$dealerAddress1 = "";
$dealerAddress2 = "";
$dealerCity = "";
$dealerState = "";
$dealerZip = "";
$dealerPhone = "";
$dealerFax = "";
$primaryContact = "";
$primaryContactPhone = "";
$primaryContactEmail = "";

$form_err = "";


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
}



// Process form data when form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['arNumber'])) {
        $arnNumber = $_POST['arNumber'];
    }

    if (isset($_POST['wholesale_flg'])) {
        $wholesale_flg = $_POST['wholesale_flg'];
        $stmtact = mysqli_prepare($link, "UPDATE Acct SET Wholesale_Flg = ? WHERE Acct_ID=?");
        mysqli_stmt_bind_param($stmtact, "si", $wholesale_flg, $dealerID);
        $resultact = mysqli_stmt_execute($stmtact);
    }



    // Update tracker for dealer forms, to indicate the ar number is assigned
    $stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim SET Assign_Rtlr_Nbr = ? WHERE Cntrct_Dim_ID=?");

    // Get the contract id
    $query = "SELECT cd.Cntrct_Dim_ID FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $Cntrct_Dim_ID = $row["Cntrct_Dim_ID"];

    mysqli_stmt_bind_param($stmt, "si", $arnNumber, $Cntrct_Dim_ID);

    $result = mysqli_stmt_execute($stmt);

    // Update tracker for dealer forms, to indicate the ar number is assigned
    $stmt = mysqli_prepare($link, "UPDATE Dealer_Progress SET Dealer_ARN_Complete = 'Y' WHERE Acct_ID=?");

    mysqli_stmt_bind_param($stmt, "i", $dealerID);

    $result = mysqli_stmt_execute($stmt);

    header("location: index.php");
    die();
} else {

    // Get the dealer address info
    $query = "SELECT * FROM Addr WHERE Acct_ID=" . $dealerID . " AND Addr_Type_Cd='Work';";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $dealerAddress1 = $row["St_Addr_1_Desc"];
    $dealerAddress2 = $row["St_Addr_2_Desc"];
    $dealerCity = $row["City_Nm"];
    $dealerState = isset($row["St_Pro_vID"]) ? $row["St_Pro_vID"] : '';
    $dealerZip = $row["Pstl_Cd"];

    // Look up the state name
    if ($dealerState > 0) {
        $query = "SELECT * FROM St_Prov WHERE St_Prov_ID=" . $dealerState;
        $result = $link->query($query);
        $row = $result->fetch_assoc();

        $dealerStateName = $row["St_Prov_ISO_2_Cd"];
    } else {
        $dealerStateName = "None Found";
    }

    // Get the dealer info
    $query = "SELECT * FROM Acct WHERE Acct_ID=" . $dealerID;
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $dealerName = $row["Acct_Nm"];
    $wholesale_flg = $row["Wholesale_Flg"];

    // Get the dealer dba info
    $query = "SELECT * FROM Altn_Nm WHERE Acct_ID=" . $dealerID;
    $result = $link->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $dba = $row["Altn_Nm"];
    }


    // Get primary contact info
    $query = "SELECT * FROM Pers WHERE Cntct_Prsn_For_Acct_Flg='Y' AND Acct_ID=" . $dealerID;
    $result = $link->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $primaryContact = $row["Pers_Full_Nm"];
    }


    // Get the contract info
    $query = "SELECT cd.Contract_Date, cd.Assign_Rtlr_Nbr FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $agreementDate = $row["Contract_Date"];
    $arnNumber = $row["Assign_Rtlr_Nbr"];
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
                        <h4 class="card-title">Manage AR Number</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form dealer-form">
                            <div class="watermark">
                                <img src="images/logo_large_bg.png" alt="">
                            </div>
                            <form name="dealerForm" id="dealer_manage_ar_form" method="POST" action="" enctype='multipart/form-data'>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">Dealer Name</h5>
                                        <h4 class="text-muted mb-0"><?php echo $dealerName; ?></h4>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">Agreement Date</h5>
                                        <h4 class="text-muted mb-0"><?php echo $agreementDate; ?></h4>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">Dealership Trading As</h5>
                                        <h4 class="text-muted mb-0"><?php echo $dba; ?></h4>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">Dealer Owner / Principal or Representative</h5>
                                        <h4 class="text-muted mb-0"><?php echo $primaryContact; ?></h4>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <h5 class="text-primary d-inline">Dealership Address</h5>
                                        <h4 class="text-muted mb-0"><?php echo $dealerAddress1; ?> <?php echo $dealerCity . ", " . $dealerStateName . ". " . $dealerZip; ?></h4>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <hr />
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label class="form-check-label">
                                            AR Number
                                        </label>
                                        <input type="text" class="form-control" id="arNumber" name="arNumber" placeholder="Enter AR Number" value="<?= isset($arnNumber) ? $arnNumber : '' ?>" />
                                        <span style="color: red;display: none;" id="ARNumberE">Please Enter AR Number..!</span>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Apply As Wholesale?</label>
                                        <div class="form-group mb-0">
                                            <label class="radio-inline mr-3">
                                                <input type="radio" value="Y" name="wholesale_flg" <?php if (isset($wholesale_flg) && $wholesale_flg == 'Y') {
                                                                                                        echo "checked";
                                                                                                    } ?>> Yes
                                            </label>
                                            <label class="radio-inline mr-3">
                                                <input type="radio" value="N" name="wholesale_flg" <?php if (isset($wholesale_flg) && $wholesale_flg == 'N') {
                                                                                                        echo "checked";
                                                                                                    } ?>> No
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="dealer_manage_ar_submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--**********************************
                dealer_agreement form
            ***********************************-->

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

<!-- Chart piety plugin files -->
<script src="./vendor/peity/jquery.peity.min.js"></script>

<!-- Dashboard 1 -->
<script src="./js/dashboard/dashboard-1.js"></script>
<script src="./js/custom.min.js"></script>
<script src="./js/deznav-init.js"></script>
<script src="./js/custom-validation.js"></script>
<script src="js/demo.js"></script>

</body>

</html>