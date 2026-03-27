<?php

// Connect to DB
require_once "includes/dbConnect.php";

/**For encryption of the data */
require_once 'encrypt.php';

// DB Library
require_once "lib/dblib.php";

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

if (!(isset($_SESSION["roleID"])) && $_SESSION["roleID"] != 1) {
    header("location: index.php");
    exit;
}

$isQuote = '';

if (isset($_GET['isQuote'])) {
    $isQuote = 'Y';
}

$warrantyType = $isQuote == 'Y' ? 'WQ' : 'WD';

$query = "SELECT
          *
          FROM
            Cntrct c,
            Cntrct_Dim cd,
            Veh v
          WHERE
            c.Created_Warranty_ID IS NULL AND 
            c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID AND 
            cd.Cntrct_Type_Cd = '" . $warrantyType . "' AND 
            cd.Is_Deleted_Flg != 'Y' AND 
            c.Veh_ID = v.Veh_ID;";

$result = $link->query($query);
$list = $result->fetch_all(MYSQLI_ASSOC);

// echo "<pre>";
// print_r($list);
// exit;

require_once("includes/header.php");

?>

<div class="content-body">
    <div class="container-fluid">
        <?php require_once("includes/common_page_content.php"); ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><?php if ($isQuote == "Y") {
                                                    echo "Quotes";
                                                } else {
                                                    echo "Warranties";
                                                } ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="watermark">
                                <img src="images/logo_large_bg.png" alt="">
                            </div>
                            <table id="warrantyTable" class="table table-responsive-md">
                                <thead>
                                    <tr>
                                        <th>Customer Name</th>
                                        <th>Sales Agent name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($list as $key => $value) {
                                    ?>
                                        <tr>
                                            <td><?= $value["Cstmr_Nme"] ?></td>
                                            <td><?= $value["Acct_Nm"] ?></td>
                                            <td>Change Sale Agent</td>
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
    </div>
    <div class="footer">
        <div class="copyright">
            <p>Copyright Developed by <a href="http://vitaltrendsusa.com/" target="_blank">Vital Trends</a> 2022</p>
        </div>
    </div>
</div>

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
    $(document).ready(function() {
        $('#preloader').fadeOut(1500);
    });
</script>


<!------------------------- Confirmation Modals --------------------------->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>