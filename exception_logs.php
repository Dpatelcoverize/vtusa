<?php
//
// File: email_report.php
// Author: Naveender Signh
// Date: 07/25/2022
//Description:To track and resend the emails to dealers and dealer agents
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageBreadcrumb = "Exception Logs Report";
$pageTitle = "Exception Logs Report";


// Connect to DB
require_once "includes/dbConnect.php";


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

$query = "SELECT * FROM Exception_Logs WHERE admin_id = 1 OR user_id != 0 ORDER BY id DESC LIMIT 1000";
$ExceptionLogsResult = $link->query($query);

require_once("includes/header.php");

?>
<!--**********************************
        Content body start
***********************************-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.jqueryui.min.css" integrity="sha512-x2AeaPQ8YOMtmWeicVYULhggwMf73vuodGL7GwzRyrPDjOUSABKU7Rw9c3WNFRua9/BvX/ED1IK3VTSsISF6TQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="vendor/datatables/css/jquery.dataTables.min.css">
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
            </div>
        </div>

        <div class="row">

            <div class="col-lg-12">
                <div class="card">
                    <div class="watermark">
                        <img src="images/logo_large_bg.png" alt="">
                    </div>
                    <div class="card-header">
                        <h4 class="card-title">Exception Logs report</h4>
                    </div>


                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-responsive-md" id="exception_report_table">
                                <thead>
                                    <tr>
                                        <th>Admin Username</th>
                                        <th>Admin Id</th>
                                        <th>User Name</th>
                                        <th>User Id</th>
                                        <th>User type</th>
                                        <th>Line No.</th>
                                        <th>Error Type</th>
                                        <th>Message</th>
                                        <th>File Name</th>
                                        <th>Trace</th>
                                        <th>Date and Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($ExceptionLogsResult != "") {

                                        while ($row = mysqli_fetch_assoc($ExceptionLogsResult)) {
                                    ?>
                                            <tr>
                                                <td><?php echo $row["admin_username"];  ?></td>
                                                <td><?php echo $row["admin_id"]; ?></td>
                                                <td><?php echo $row["user_name"]; ?></td>
                                                <td><?php echo $row["user_id"]; ?></td>
                                                <td><?php echo $row["role"]; ?></td>
                                                <td><?php echo $row["line"]; ?></td>
                                                <td><?php echo $row["error_type"]; ?></td>
                                                <td><?php echo $row["message"]; ?></td>
                                                <td><?php echo $row["file"]; ?></td>
                                                <td><?php echo $row["trace"]; ?></td>
                                                <td><?php echo $row["created_at"]; ?></td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                            <?php

                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- MODAL IS HERE TO SHOW INFORMATION -->
<div class="modal fade" id="emailLogModel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Email Log Details</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body" id="emailLogDetails">

            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
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
        Main wrapper end
***********************************-->


<!--**********************************
        Scripts
    ***********************************-->
<!-- Required vendors -->
<script src="./vendor/global/global.min.js"></script>
<script src="./vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

<!-- Dashboard 1 -->
<script src="./js/custom.min.js"></script>
<script src="./js/deznav-init.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
<script src="js/demo.js"></script>
<script>
    $(document).ready(function() {
        $('#exception_report_table').DataTable();
    });
</script>
</body>

</html>