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

$query = "SELECT * FROM Users";

$result = $link->query($query);
$userList = $result->fetch_all(MYSQLI_ASSOC);


require_once("includes/header.php");

?>

<!--**********************************
            Content body start
        ***********************************-->
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
                        <h4 class="card-title">Users</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">

                            <table class="table table-responsive-md" id="manage_user_list">
                                <thead>
                                    <tr>
                                        <th>User Name</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email Address</th>
                                        <th>Account Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (count($userList)) {
                                        foreach ($userList as $key => $value) {
                                            $id = encryptData($value['userID']);
                                    ?>
                                            <tr>
                                                <td><?= $value["username"] ?></td>
                                                <td><?= $value["firstName"] ?></td>
                                                <td><?= $value["lastName"] ?></td>
                                                <td><?= $value["emailAddress"] ?></td>
                                                <td>
                                                    <div class="form-check custom-switch toggle-switch text-start me-5 p-0">
                                                    <label class="form-check-label" for="customSwitch12"> <span class="badge badge-sm <?= $value['isActive'] === 'Y' ? 'badge-success' : 'badge-danger' ?>">
            <?= $value['isActive'] === 'Y' ? 'Active' : 'Inactive' ?>
        </span></label>    
                                                    <input type="checkbox" class="form-check-input user-status-toggle" data-id="<?= $id ?>"
            <?= $value['isActive'] === 'Y' ? 'checked' : '' ?>>
                                                        
                                                    </div>
                                                </td>
                                                <td><a href="change_user_pass.php?id=<?= $id ?>">Change Password</a></td>
                                            </tr>
                                    <?php
                                        }
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

<!-- Dashboard 1 -->
<script src="./js/custom.min.js"></script>
<script src="./js/deznav-init.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#manage_user_list').DataTable();
    });
</script>
<script src="js/demo.js"></script>
<script>
    $(document).ready(function () {

    const table = $('#manage_user_list').DataTable();

    // ✅ Status toggle with SweetAlert
    $(document).on('change', '.user-status-toggle', function () {

        const checkbox = $(this);
        const userId = checkbox.data('id');
        const isActive = checkbox.is(':checked') ? 'Y' : 'N';
        const statusText = isActive === 'Y' ? 'activate' : 'deactivate';

        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to ${statusText} this user?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {

            if (!result.isConfirmed) {
                checkbox.prop('checked', !checkbox.is(':checked'));
                return;
            }

            $.ajax({
                url: 'backend/update_user_status.php',
                type: 'POST',
                data: {
                    id: userId,
                    status: isActive
                },
                dataType: 'json',
                success: function (response) {

                    if (response.success) {

                        const badge = checkbox.closest('td').find('.badge');

                        if (isActive === 'Y') {
                            badge
                                .removeClass('badge-danger')
                                .addClass('badge-success')
                                .text('Active');
                        } else {
                            badge
                                .removeClass('badge-success')
                                .addClass('badge-danger')
                                .text('Inactive');
                        }

                        Swal.fire('Updated!', response.message, 'success');

                    } else {
                        checkbox.prop('checked', !checkbox.is(':checked'));
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function () {
                    checkbox.prop('checked', !checkbox.is(':checked'));
                    Swal.fire('Error', 'Something went wrong.', 'error');
                }
            });
        });
    });

});
</script>
</body>

</html>