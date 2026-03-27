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

$pageBreadcrumb = "Email Report";
$pageTitle = "Email Report";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";
// Email function
require_once "lib/emailHelper.php";

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

/* GETTING DETAILS */
if (isset($_POST['type']) && $_POST['type']=='view_details') {
    $id = $_POST['id'];
    $sql = "SELECT * FROM email_tracker WHERE id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $emailDetails = $result->fetch_assoc();
        echo json_encode($emailDetails);
    } else {
        echo json_encode(["error" => "Invalid Log ID"]);
    }

    $stmt->close();
    die;
} 
/* END HERE */

/* GETTING DETAILS */
if (isset($_POST['type']) && $_POST['type']=='resend_mail') {
    $toUser = $_POST['to_user'];
    $mailType = $_POST['mail_type'];
    $userType = $_POST['user_type'];
    $fullname = $_POST['to_fullname'];
    $explodedName=explode(' ',$fullname);
    $firstname=$explodedName[0];
    $lastname=$explodedName[1];
    $sql = "SELECT username FROM Users WHERE username = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("s", $toUser);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $emailRawDetails = $result->fetch_assoc();
       if( $mailType=='Dealer Agreement'){
            $to = $toUser;
            $passwordRandom=generateRandomPassword(8);
            $newPassword = password_hash($passwordRandom, PASSWORD_DEFAULT);  
            $subject = "Welcome - New Vital Trends Account";
            $txt = "You have been signed up for a Vital Trends user account!  Please click here to <a href='https://portal.vitaltrendsusa.com'>log in</a>.\n";
            $txt .= "Your user name is: " . $toUser . "\n";
            $txt .= "Your initial password is: ".$passwordRandom." \n";
            $txt .= "Please note, you will need to change your password upon first login.\n\n";
            $txt .= "Thank you!\nVital Trends team";
           
            $moreInfo['user_type']=$userType;
            $moreInfo['mail_type']= $mailType;
            $moreInfo['email_purpose']='welcome';	
            
            $stmt = mysqli_prepare($link, "UPDATE Users SET password=? WHERE username=?");
            mysqli_stmt_bind_param($stmt, "ss", $newPassword, $toUser);
            $result = mysqli_stmt_execute($stmt);

            $emailResult = sendEmail($to, $firstname, $lastname, $subject, $txt,$moreInfo);
            if( $emailResult){
                echo json_encode(["message" => "Successfully email have been sent"]);
            }else{
                echo json_encode(["error" => "Unable to send mail check email Logs"]);
            }
        }elseif( $mailType=='Dealer Setup'){
            $to = $toUser;
            $passwordRandom=generateRandomPassword(8);
            $newPassword = password_hash($passwordRandom, PASSWORD_DEFAULT);  
            $subject = "Welcome - New Vital Trends Account";
            $txt = "You have been signed up for a Vital Trends user account!  Please click here to <a href='https://portal.vitaltrendsusa.com'>log in</a>.\n";
            $txt .= "Your user name is: " . $toUser . "\n";
            $txt .= "Your initial password is: ".$passwordRandom." \n";
            $txt .= "Please note, you will need to change your password upon first login.\n\n";
            $txt .= "Thank you!\nVital Trends team";
        
            $moreInfo['user_type']='Dealer Agent';
            $moreInfo['mail_type']='Dealer Setup';
            $moreInfo['email_purpose']='welcome';	    
                
            $stmt = mysqli_prepare($link, "UPDATE Users SET password=? WHERE username=?");
            mysqli_stmt_bind_param($stmt, "ss", $newPassword, $toUser);
            $result = mysqli_stmt_execute($stmt);

            $emailResult = sendEmail($to, $firstname, $lastname, $subject, $txt, $moreInfo);
            if( $emailResult){
                echo json_encode(["message" => "Successfully email have been sent"]);
            }else{
                echo json_encode(["error" => "Unable to send mail check email Logs"]);
            }
        } else{
            $to = $toUser;
            $passwordRandom=generateRandomPassword(8);
            $newPassword = password_hash($passwordRandom, PASSWORD_DEFAULT);  
            $subject = "Welcome - New Vital Trends Account";
            $txt = "You have been signed up for a Vital Trends user account!  Please click here to <a href='https://portal.vitaltrendsusa.com'>log in</a>.\n";
            $txt .= "Your user name is: " . $toUser . "\n";
            $txt .= "Your initial password is: ".$passwordRandom." \n";
            $txt .= "Please note, you will need to change your password upon first login.\n\n";
            $txt .= "Thank you!\nVital Trends team";
        
            $moreInfo['user_type']='Admin';
            $moreInfo['mail_type']='Passsword Reset';
            $moreInfo['email_purpose']='New Password';	    
                
            $stmt = mysqli_prepare($link, "UPDATE Users SET password=? WHERE username=?");
            mysqli_stmt_bind_param($stmt, "ss", $newPassword, $toUser);
            $result = mysqli_stmt_execute($stmt);

            $emailResult = sendEmail($to, $firstname, $lastname, $subject, $txt, $moreInfo);
            if( $emailResult){
                echo json_encode(["message" => "Successfully email have been sent"]);
            }else{
                echo json_encode(["error" => "Unable to send mail check email Logs"]);
            }
        }
    } else {
        echo json_encode(["error" => "Invalid Username"]);
    }

    $stmt->close();
    die;
} 

function generateRandomPassword($length = 12) {
    // Generate random bytes
    $bytes = random_bytes($length);
    
    // Convert to a hexadecimal representation
    $password = bin2hex($bytes);
    
    // Truncate the password to the desired length
    return substr($password, 0, $length);
}
/* END HERE */



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
    $query = "SELECT * FROM email_tracker et, Users u WHERE et.email_to_user = u.username ORDER BY et.created_on DESC";
}


if ($errorMessage === "") {
    $emailResult = $link->query($query);
}


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
                        <h4 class="card-title">Email report</h4>
                    </div>


                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-responsive-md" id="email_report_table">
                                <thead>
                                    <tr>
                                        <th>To FullName</th>
                                        <th>To</th>
                                        <th>Status</th>
                                        <th>Mail type</th>
                                        <th>User type</th>
                                        <th>Code</th>
                                        <th>Sent On</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($errorMessage === "") {

                                        while ($row = mysqli_fetch_assoc($emailResult)) {
                                    ?>
                                            <tr>

                                                <td><?php echo $row["to_fullName"];  ?></td>
                                                <td><?php echo $row["email_to_user"]; ?></td>
                                                <td><?php echo $row["email_status"]; ?></td>
                                                <td><?php echo $row["mail_type"]; ?></td>
                                                <td><?php echo $row["user_type"]; ?></td>
                                                <td><?php echo $row["code"]; ?></td>
                                                <td><?php echo $row["sent_on"]; ?></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                                            More..
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="javascript:viewEmaillogs('<?php echo $row["id"]; ?>')">See Details</a>
                                                            <a class="dropdown-item" href="javascript:resendMail('<?= $row["email_to_user"] ?>','<?= $row["mail_type"] ?>','<?= $row["user_type"] ?>','<?= $row["to_fullName"] ?>')">Resend Mail</a>
                                                        </div>
                                                    </div>
                                                </td>

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
        $('#email_report_table').DataTable();
    });

    function viewEmaillogs(id) {
        $.ajax({
            url: "email_report.php",
            type: "POST",
            data: {
                id: id,
                type: 'view_details'
            },
            success: function(response) {
                var user = JSON.parse(response);
                if (user.error) {
                    $("#emailLogDetails").html("<p>"+ user.error +"</p>");
                } else {
                    var emailStatusBadge = user.email_status === "success"
                                        ? '<span class="badge badge-success">Success</span>'
                                        : '<span class="badge badge-danger">Failure</span>';
                    var emailDetails = `
                                    <table class="table table-hover ">
                                        <tr><th>Full Name:</th><td>${user.to_fullName}</td></tr>
                                        <tr><th>Email From:</th><td>${user.email_from_user}</td></tr>
                                        <tr><th>Email To:</th><td>${user.email_to_user}</td></tr>
                                        <tr><th>IP Sent From:</th><td>${user.ip_sent_from}</td></tr>
                                        <tr><th>Mail Type:</th><td>${user.mail_type}</td></tr>
                                        <tr><th>User Type:</th><td>${user.user_type}</td></tr>
                                        <tr><th>Email Purpose:</th><td>${user.email_purpose}</td></tr>
                                        <tr><th>Code:</th><td>${user.code}</td></tr>
                                        <tr><th>Email Status:</th><td>${emailStatusBadge}</td></tr>
                                        <tr><th>Description:</th><td>${user.description}</td></tr>
                                        <tr><th>Sent On:</th><td>${user.sent_on}</td></tr>
                                    </table>
                                `;

                        $("#emailLogDetails").html(emailDetails);
                        $("#emailLogModel").modal('show');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error:', textStatus, errorThrown);
                $("#emailLogDetails").html("<p>An error occurred while processing your request.</p>");
            }
        });
    }

    function resendMail(to_user, mail_type,user_type,fullname) {
        $('#preloader').fadeOut(1500);
        $('#main-wrapper').addClass('show');
        var sweet_loader = '<div class="spinner-grow" style="width: 3rem; height: 3rem;" role="status"><span class="sr-only">Loading...</span></div>';
        $.ajax({
            url: "email_report.php",
            type: "POST",
            crossDomain: true,
            data: {
                to_user: to_user,
                mail_type:mail_type,
                user_type:user_type,
                to_fullname:fullname,
                type: 'resend_mail'
            },
            beforeSend: function() {
                swal.fire({
                    html: '<h5>Sending,Please Wait...</h5></br><div class="spinner-grow" style="width: 3rem; height: 3rem;" role="status"><span class="sr-only">Loading...</span></div>',
                    showConfirmButton: false,
                });
            },
            success: function(response) {
               
                var mailerinfo = JSON.parse(response);
                if (mailerinfo.error) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Something went wrong please check the logs!",
                        footer: '<a href="email_report.php">Why do I have this issue?</a>'
                        });
                    
                } else {
                    Swal.fire({
                        title: "Sent",
                        text: mailerinfo.message,
                        icon: "success"
                        });
                    
                  
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error:', textStatus, errorThrown);
                $("#emailLogDetails").html("<p>An error occurred while processing your request.</p>");
            }
        });
    }
</script>
</body>

</html>