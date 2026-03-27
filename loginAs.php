<?php
ob_start();
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
if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


// Get the adminID from session, or fail.fin
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

require_once("includes/header.php");
if ($adminID == 1) {

    /* GET USERS LIST */
    // If Role_ID == 1, this is an admin.
    //  If Role_ID == 2, this is a dealer account.
    //  If Role_ID == 3, this is a Vital Trends Sales Agent Account.
    //  If Role_ID == 4, this is a Agency Sales Agent Account.
    //  If Role_ID == 5, this is a Agency Account.
    //  If Role_ID == 6, this is a Dealer Agent Account.
    $rolesList = [
        '2' => 'Dealer Account',
        '3' => 'Vital Trends Sales Agent Account',
        '4' => 'Agency Sales Agent Account',
        '5' => 'Agency Account',
        '6' => 'Dealer Agent Account',
    ];

    if (isset($_POST["usernames"]) && isset($_POST["submitlogin"])) {

        $getUsername = $_POST["usernames"];
        $getUserRole = $_POST["userrole"];
        // Fetch User Information
        $stmt = mysqli_prepare($link, "SELECT u.userID, u.Pers_ID,u.username, u.mustResetPassword, u.Role_ID, u.Agent_ID, a.Acct_Nm,
        a.Acct_ID, a.Sls_Agnt_ID,prsn.Pers_Frst_Nm,prsn.Pers_Last_Nm FROM Users u, Acct a, Usr_Loc ul,Pers prsn WHERE u.username = ? AND u.Role_ID=? AND
        u.userID = ul.Usr_ID AND ul.Dlr_Acct_ID = a.Acct_ID AND u.Pers_ID = prsn.Pers_ID ORDER BY a.Acct_Nm");

        /* Bind variables to parameters */
        $val1 = $getUsername;
        $val2 = $getUserRole;

        mysqli_stmt_bind_param($stmt, "si", $val1, $val2);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);

            $row = mysqli_fetch_assoc($result);
            if (!empty($row)) {
                if (count($row) > 0 &&  $getUserRole == $row['Role_ID']) {
                    $roleID = $row['Role_ID'];

                    if ($roleID == 2) {

                        $_SESSION["loggedin"] = true;
                        $_SESSION["persID"] = $row['Pers_ID'];
                        $_SESSION["id"] = $row['Acct_ID'];
                        $_SESSION["username"] = $row['username'];
                        $_SESSION["userType"] = "dealer";
                        // $_SESSION["dealer_multiple_locations"] = $multipleLocations;
                        header("location: index.php");
                    } else if (count($row) > 0 && ($row['Role_ID'] == 3) || ($row['Role_ID'] == 4) || ($row['Role_ID'] == 5) || ($row['Role_ID'] == 6)) {
                        $roleID = $row['Role_ID'];
                        $_SESSION["loggedin"] = true;
                        $_SESSION["persID"] = $row['Pers_ID'];
                        $_SESSION["id"] = $row['Acct_ID'];
                        $_SESSION["username"] = $row['username'];
                        if ($roleID == 4 || $roleID == 5) {
                            $_SESSION["userType"] = "Agent";
                            //$_SESSION["admin_id"] = $userID; // This allows us to display the sales agent who created this dealer.
                        } else if ($roleID == 6) {
                            // For Dealer Agents
                            // $_SESSION["admin_id"] = $Sls_Agnt_ID; // This allows us to display the sales agent who created this dealer.
                            $_SESSION["userType"] = "dealer";
                        } else {
                            // $_SESSION["admin_id"] = $Sls_Agnt_ID; // This allows us to display the sales agent who created this dealer.
                            // $_SESSION["username"] = $username;
                            $_SESSION["userType"] = "Agent";
                        }
                        header("location: index.php");
                    }
                } 
            }else{
                header("location: loginAs.php");
            }
        }
       
    }
?>


    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="content-body">
        <!-- row -->
        <div class="container-fluid">

            <div class="row" style="margin-top: 2%;">
                <div class="col-lg-12">
                    <div class="form-group col-md-12">

                    </div>
                </div>
            </div>
            <div class="row no-gutters">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title text-center">Login As Another User</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <a href="index.html"><img src="images/" alt=""></a>
                            </div>
                            <h4 class="text-center mb-4 text-black"></h4>
                            <?php
                            if (!empty($login_err)) {
                                echo '<div class="alert alert-danger">' . $login_err . '</div>';
                            }
                            ?>
                            <form action="" method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="mb-1 text-black"><strong>Role</strong></label>
                                            <select class="select2-selector userrole form-control" name="userrole" onchange="getUsers(this.value)" height="2rem" required>
                                                <option value="">--Select An Option--</option>
                                                <?php
                                                foreach ($rolesList as $key => $value) {
                                                ?>
                                                    <option value="<?= $key ?>"><?= $value ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="mb-1 text-black"><strong>User</strong></label>
                                            <select class="select2-selector user form-control" id="usernames" name="usernames">


                                            </select>
                                        </div>
                                    </div>
                                </div>


                                <div class="text-center">
                                    <button type="submit" name="submitlogin" class="btn btn-success  bg-success text-white">Login Now</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php
}
require_once("includes/footer.php");
?>
<script src="./vendor/global/global.min.js"></script>
<script src="./vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

<script src="./js/custom.min.js"></script>
<script src="./js/deznav-init.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/demo.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-selector').select2({
            theme: "classic",
            placeholder: 'Select an option',
            allowClear: true
        });
    });

    function getUsers(role) {

        $.ajax({
            type: 'POST',
            url: "backend/getUsersList.php",
            data: {
                role: role,
                type: 'getUsersList'
            },
            dataType: 'json',
            success: function(data) {
                let AllUsers = data.data;

                // Clear the current options
                $('#usernames').empty();

                if (data.status == 200) {
                    // Populate the usernames dropdown
                    $.each(AllUsers, function(index, user) {
                        $('#usernames').append($('<option>', {
                            value: user, // Assuming user.id is the identifier
                            text: user // Assuming user.name is the display name
                        }));
                    });

                    // Refresh the Select2 dropdown
                    $('#usernames').select2({
                        theme: "classic",
                        placeholder: 'Select User',

                    });
                } else {
                    Swal.fire({
                        position: 'top-center',
                        title: 'Warning',
                        html: "<p class='text-warning'>User Selection Is Required</p>",
                        showConfirmButton: true,
                    });
                }
            }
        })
    }
</script>