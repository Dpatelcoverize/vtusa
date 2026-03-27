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

if (!isset($_GET['id'])) {
    header("location: index.php");
    exit;
}

$id = decryptData($_GET['id']);

$query = "SELECT
          *
          FROM
            Users
          WHERE
            isActive = 'Y' AND userID = " . $id;

$result = $link->query($query);
$user = $result->fetch_assoc();
$_SESSION['passChanged'] = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = '';

    if (!empty(trim($_POST["password"]))) {
        $password = trim($_POST["password"]);
    }

    $password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($link, "UPDATE
                                        Users
                                    SET PASSWORD
                                        = ?
                                    WHERE
                                        userID = ?");

    mysqli_stmt_bind_param($stmt, "si", $password, $id);

    /* Execute the statement */
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $_SESSION['passChanged'] = 'Password changed successfully';
    }
}

require_once("includes/header.php");

?>

<div class="content-body">
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
                    <div class="card-header">
                        <h4 class="card-title">Change Password</h4>
                        <h5>(<a href="manage_users.php">Return to User List</a>)</h5>

                    </div>
                    <div class="card-body">
                        <?php
                        if (isset($_SESSION['passChanged']) && $_SESSION['passChanged'] != '') {
                        ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong></strong> <?= $_SESSION['passChanged']; ?>
                            </div>
                        <?php
                        }
                        ?>
                        <form name="pass_change" id="pass_change" method="POST" enctype='multipart/form-data'>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    User Name
                                    <input type="text" class="form-control" name="username" id="username" value="<?php echo $user['username']; ?>" readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    Password
                                    <input type="password" class="form-control" name="password" id="password" placeholder="Enter Password">
                                    <span style="color: red;display: none;" id="passwordE">Please Enter Password..!</span>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    Conform Password
                                    <input type="password" class="form-control" name="cpassword" id="cpassword" placeholder="Enter Conform Password">
                                    <span style="color: red;display: none;" id="cpasswordE">Please Enter Conform Password..!</span>
                                    <span style="color: red;display: none;" id="passMatch">Password And Conform Password Dose Not Matched..!</span>
                                </div>
                            </div>
                            <div class="form-row">
                                <button type="button" id="pass_change_btn" name="pass_change_btn" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
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


</div>

<!-- Required vendors -->
<script src="./vendor/global/global.min.js"></script>
<script src="./vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

<!-- Dashboard 1 -->
<script src="./js/custom.min.js"></script>
<script src="./js/custom-validation.js"></script>
<script src="./js/deznav-init.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
<script>
    $(document).ready(function() {
        $('#finance_table').DataTable();
    });
</script>