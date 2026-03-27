<?php

// Connect to DB
require_once "includes/dbConnect.php";
/**For encryption of the data */
require_once 'encrypt.php';
// DB Library
require_once "lib/dblib.php";

use Classes\GeneratePDF;

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

/**Get csrf token */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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


if (!isset($_GET["id"]) || !isset($_GET['loc'])) {
    header("location: index.php");
    exit;
}

$per_id = $_GET["id"];
$per_id = decryptData($per_id);
$loc_id = $_GET["loc"];
$loc_id = decryptData($loc_id);

$query = "SELECT
          *,
          (
              CASE WHEN Cntct_Prsn_For_Acct_Flg = 'Y' THEN 0 ELSE 1
          END
          ) AS Cntct_Prsn_For_Acct_Flg_Order
          FROM
              `Usr_Loc` u,
              `Acct` a,
              Pers p,
              Email m,
              Tel t
          WHERE
              u.Usr_Loc_ID = $loc_id AND p.Pers_ID = $per_id AND a.Acct_ID = u.`Dlr_Acct_ID` AND u.Pers_ID = p.Pers_ID AND p.Pers_ID = t.Pers_ID AND m.Pers_ID = p.Pers_ID
          ORDER BY
              Cntct_Prsn_For_Acct_Flg_Order,
              Acct_Nm,
              Pers_Last_Nm ASC;";

$result = $link->query($query);
$per_data = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /**check token */
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("location: index.php");
        exit;
    }

    /**Get post fields */
    if (!empty(trim($_POST["personFirstName"]))) {
        $personFirstName = htmlspecialchars(trim($_POST['personFirstName']), ENT_QUOTES, 'UTF-8');
    }

    if (!empty(trim($_POST["personLastName"]))) {
        $personLastName = htmlspecialchars(trim($_POST['personLastName']), ENT_QUOTES, 'UTF-8');
    }

    if (!empty(trim($_POST["personPhone"]))) {
        $personPhone = htmlspecialchars(trim($_POST['personPhone']), ENT_QUOTES, 'UTF-8');
    }

    // if (!empty(trim($_POST["dealerLocationID"]))) {
    //     $dealerLocationID = htmlspecialchars(trim($_POST['dealerLocationID']), ENT_QUOTES, 'UTF-8');
    // }

    if (!empty(trim($_POST["personEmail"]))) {
        $personEmail = htmlspecialchars(trim($_POST['personEmail']), ENT_QUOTES, 'UTF-8');
    }

    $dealerID = $_SESSION["id"];

    $stmt = mysqli_prepare($link, "UPDATE
                                        Pers
                                    SET
                                        Pers_Full_Nm = ?,
                                        Pers_Last_Nm = ?,
                                        Pers_Frst_Nm = ?
                                    WHERE
                                        Pers_ID = ?");

    /* Bind variables to parameters */
    $val2 = $personFirstName . " " . $personLastName;
    $val3 = $personLastName;
    $val4 = $personFirstName;

    mysqli_stmt_bind_param($stmt, "issi", $val2, $val3, $val4, $per_id);

    /* Execute the statement */
    $result = mysqli_stmt_execute($stmt);

    $stmt = mysqli_prepare($link, "UPDATE Users 
                                    SET  
                                        firstName=?, 
                                        lastName=?, 
                                        emailAddress=?
                                    WHERE 
                                        Pers_ID=?");

    mysqli_stmt_bind_param($stmt, "sssi", $personFirstName, $personLastName, $personEmail, $per_id);


    /* Execute the statement */

    $result = mysqli_stmt_execute($stmt);

    /*$query = "SELECT
                    *
                FROM
                    Dlr_Loc_Dim
                WHERE
                    Dlr_Acct_ID = " . $dealerLocationID . ";";
    $result = $link->query($query);
    $row = $result->fetch_assoc();
    $Dlr_Loc_Dim_ID = $row["Dlr_Loc_Dim_ID"];

    $stmt = mysqli_prepare($link, "UPDATE Usr_Loc 
                                    SET  
                                        Dlr_Loc_Dim_ID=?,
                                    WHERE 
                                        Dlr_Acct_ID=?");

    mysqli_stmt_bind_param($stmt, "ii", $Dlr_Loc_Dim_ID, $dealerLocationID);

    $result = mysqli_stmt_execute($stmt);*/

    $stmt = mysqli_prepare($link, "UPDATE Tel 
                                    SET 
                                        Tel_Nbr=?
                                    WHERE 
                                        Pers_ID=?");

    mysqli_stmt_bind_param($stmt, "si", $personPhone, $per_id);

    $result = mysqli_stmt_execute($stmt);

    $stmt = mysqli_prepare($link, "UPDATE Email 
                                    SET 
                                        Email_URL_Desc=?
                                    WHERE 
                                        Pers_ID=?");

    mysqli_stmt_bind_param($stmt, "si", $personEmail, $per_id);

    /* Execute the statement */
    $result = mysqli_stmt_execute($stmt);

    // header("location: dealer_setup.php");
    // exit;

    if ($per_data['Cntct_Prsn_For_Acct_Flg'] == 'Y') {

        $acctID = $_SESSION['id'];

        // Select data for this acctID.
        $acctResult = selectAcct($link, $acctID);

        if ($acctResult) {
            $row = $acctResult->fetch_assoc();
            $dealerName = $row["Acct_Nm"];
            $federalTaxID = $row["Fed_Tax_Number"];
            $dunsNumber = $row["Duns_Number"];
            $multipleLocations = $row["Multiple_Locations"];
            $individualBilling = $row["Individual_Billing"];
            $wholesale_flg = $row["Wholesale_Flg"];
        } else {
            // Failed to find a result for the provided Acct_ID so drop out to main page.
            header("location: index.php");
            exit;
        }

        // Select Addr for the dealer.
        $addrResult = selectAddrByAcct($link, $acctID, "Work");

        if ($addrResult) {
            $row = $addrResult->fetch_assoc();
            $dealerAddress1 = $row["St_Addr_1_Desc"];
            $dealerAddress2 = $row["St_Addr_2_Desc"];
            $dealerCity = $row["City_Nm"];
            $dealerState = $row["St_Prov_ISO_2_Cd"];
            $dealerPostalCode = $row["Pstl_Cd"];
        } else {
            $dealerAddress1 = "";
            $dealerAddress2 = "";
            $dealerCity = "";
            $dealerState = "";
            $dealerPostalCode = "";
        }

        // Select Tel for the dealer.
        $telResult = selectTelByAcct($link, $acctID, "Y", "Work");
        if ($telResult) {
            $row = $telResult->fetch_assoc();
            $dealerPhone = $row["Tel_Nbr"];
        } else {
            $dealerPhone = "";
        }

        // Select Fax for the dealer.
        $telResult = selectTelByAcct($link, $acctID, "N", "Fax");

        if ($telResult) {
            $row = $telResult->fetch_assoc();
            $dealerFax = $row["Tel_Nbr"];
        } else {
            $dealerFax = "";
        }

        // Select Email for the dealer.
        $emailResult = selectEmailByAcct($link, $acctID, "Y", "Work");
        if ($emailResult) {
            $row = $emailResult->fetch_assoc();
            $dealerEmail = $row["Email_URL_Desc"];
        } else {
            $dealerEmail = "";
        }


        // Select website for the dealer.
        $websiteResult = selectEmailByAcct($link, $acctID, "N", "Website");
        if ($websiteResult) {
            $row = $websiteResult->fetch_assoc();
            $dealerWebsite = $row["Email_URL_Desc"];
        } else {
            $dealerWebsite = "";
        }

        // Get contract and signature info
        $query = "SELECT * FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $acctID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
        $result = $link->query($query);
        $row = $result->fetch_assoc();

        $signatureFilename = $row["Cntrct_Signature"];
        $signatureSigner = $row["Cntrct_Signer_Nm"];
        $signatureTitle = $row["Cntrct_Signer_Ttl"];
        $agreementDate = $row['Created_Date'];
        $contractID = $row['Cntrct_ID'];
        $contractDimID = $row['Cntrct_Dim_ID'];

        /**get Altn_Nm data */
        $query = "SELECT * FROM Altn_Nm WHERE Acct_ID=" . $acctID . ";";
        $result = $link->query($query);
        $row = $result->fetch_assoc();

        $dba = $row['Altn_Nm'];

        /**get account payable details data */
        $query = "SELECT
                        *
                    FROM
                        Pers p,
                        Email e,
                        Tel t
                    WHERE
                        p.Pers_ID = e.Pers_ID AND 
                        p.Pers_ID = t.Pers_ID AND 
                        p.Acct_ID = " . $acctID . " AND 
                        p.AP_Prsn_Flg = 'Y';";

        $result = $link->query($query);
        $row = $result->fetch_assoc();

        $accountsPayableContactFirstName = $row['Pers_Frst_Nm'];
        $accountsPayableContactLastName = $row['Pers_Last_Nm'];
        $accountsPayableContactEmail = $row['Email_URL_Desc'];
        $accountsPayableContactPhone = $row['Tel_Nbr'];

        $Data = [
            'Date' => $agreementDate,
            'RETAILER BUSINESS NAME' => $dealerName,
            'DOING BUSINESS AS' => $dba,
            'FEDERAL TAX ID' => $federalTaxID,
            'DUNS#' => $dunsNumber,
            'ADDRESS' => $dealerAddress1,
            'PO BOX/SUITE' => $dealerAddress2,
            'CITY' => $dealerCity,
            'STATE/PROVINCE' => $dealerState,
            'ZIP/POSTAL CODE' => $dealerPostalCode,
            'PHONE#' => $dealerPhone,
            'FAX#' => $dealerFax,
            'BUSINESS EMAIL' => $dealerEmail,
            'BUSINESS WEBSITE' => $dealerWebsite,
            'PRIMARY CONTACT NAME _1' =>  $personFirstName . ' ' . $personLastName,
            'PRIMARY CONTACT TITLE_1' => $signatureTitle,
            'PRIMARY CONTACT EMAIL_1' => $personEmail,
            'PRIMARY CONTACT PH#_1' => $personPhone,
            'ACCOUNTS PAYABLE CONTACT_1' => $accountsPayableContactFirstName . ' ' . $accountsPayableContactLastName,
            'ACCOUNTS PAYABLE CONTACT EMAIL_1' => $accountsPayableContactEmail,
            'AP CONTACT PHONE_1' => $accountsPayableContactPhone,
            'MULTIPLE LOCATIONS?' => $multipleLocations,
            'RETAILER SIGNATURE' => $signatureFilename,
            'RETAILER NAME' => $signatureSigner,
            'RETAILER TITLE' => $signatureTitle,
            'RETAILER SIGNED DATE' => $agreementDate,
            'ASSIGNED RETAILER#' => "",
            'TN DATE' => $agreementDate,
            'ASSIGNED PROGRAMS' => "",
            'TN SIGNATURE' => "",
            'TN SIGNATURE DATE' =>  $agreementDate
        ];

        //print_r($Data);
        //echo  $contractID .'-'.$contractDimID."</br>";
        //echo  $acctID."</br>";
        //echo $per_id."</br>"; die;
        $sql = "SELECT Path_to_File FROM File_Assets WHERE Acct_ID = ? AND Dealer_Pers_ID = ? AND Dealer_Cntrct_ID = ? AND File_Asset_Type_ID=1";
        $filestmt = mysqli_prepare($link, $sql);

        if ($filestmt) {
            mysqli_stmt_bind_param($filestmt, "iii", $acctID, $per_id, $contractDimID);
            mysqli_stmt_execute($filestmt);
            mysqli_stmt_bind_result($filestmt, $Path_to_File);
            if (mysqli_stmt_fetch($filestmt)) {
                //$fileNameWithoutExtension = pathinfo($Path_to_File, PATHINFO_FILENAME);
                //$pdfFileName= $fileNameWithoutExtension;  //ONLY GIVES NAME
                $pdfFileName= basename($Path_to_File);
                $pdf = new GeneratePDF;
                $response = $pdf->generateSgDetailDealer($Data, $pdfFileName);
                header("location: dealer_setup.php");
            } else {
                echo "No file found with the given IDs.";
            }
            mysqli_stmt_close($filestmt);
        } else {
            echo "Error preparing statement: " . mysqli_error($link);
        }

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
                    <a href="dealer_setup.php"><span class="badge badge-rounded badge-warning">Back to dealer setup</span></a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h4 class="card-title">Edit Person detail</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form dealer-form">
                            <div class="watermark">
                                <img src="images/logo_large_bg.png" alt="">
                            </div>
                            <form name="personForm" id="personForm" method="POST" action="">
                                <?php
                                if (isset($_SESSION['csrf_token'])) {
                                    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
                                } else {
                                    header("Location: page-error-500.php");
                                    exit();
                                }
                                ?>
                                <div class="form-row">
                                    <!-- <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">Existing Locations</h5>
                                        <?php
                                        // Get locations associated with this dealer.
                                        $query = "SELECT * FROM `Acct` WHERE Acct_ID = " . $dealerID . " OR `Prnt_Acct_ID`=" . $dealerID . " ORDER BY Prnt_Acct_ID ASC";
                                        $personResult = $link->query($query);

                                        if (mysqli_num_rows($personResult) > 0) {
                                        ?>
                                            <select class="form-control default-select" name="dealerLocationID" id="sel1">\n
                                                <?
                                                // output data of each row
                                                $loopCounter = 0;
                                                while ($row = mysqli_fetch_assoc($personResult)) {
                                                    $loopCounter++;
                                                ?>
                                                    <option value="<?php echo $row["Acct_ID"]; ?>" <?php echo isset($per_data) && isset($per_data['Dlr_Acct_ID']) && $per_data['Dlr_Acct_ID'] == $row['Acct_ID'] ? 'selected' : ''; ?>><?php echo $row["Acct_Nm"]; ?> <?php if ($row["Prnt_Acct_ID"] == "") { ?> (main location)
                                                        <?php } ?></option>\n
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        <?php
                                        } else {
                                            echo "No locations yet defined for this agreement, somehow!";
                                        }
                                        ?>
                                        <span style="color:red;<?php if (isset($_SESSION['error_fmessage']) != '') { ?>display:block; <?php
                                                                                                                                    } else { ?>display:none; <?php
                                                                                                                                                            } ?>"><?php if (isset($_SESSION['error_fmessage']) != '') {
                                                                                                                                                                        echo $_SESSION['error_fmessage'];
                                                                                                                                                                    } ?></span>
                                    </div> -->

                                    <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">First Name</h5>
                                        <input type="text" class="form-control" name="personFirstName" placeholder="" required value="<?= isset($per_data) && isset($per_data['Pers_Frst_Nm']) ? $per_data['Pers_Frst_Nm'] : '' ?>">
                                        <span style="color:red;<?php if (isset($_SESSION['error_fmessage']) != '') { ?>display:block; <?php
                                                                                                                                    } else { ?>display:none; <?php
                                                                                                                                                            } ?>"><?php if (isset($_SESSION['error_fmessage']) != '') {
                                                                                                                                                                        echo $_SESSION['error_fmessage'];
                                                                                                                                                                    } ?></span>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">Last Name</h5>
                                        <input type="text" class="form-control" name="personLastName" placeholder="" required value="<?= isset($per_data) && isset($per_data['Pers_Last_Nm']) ? $per_data['Pers_Last_Nm'] : '' ?>">
                                        <span style="color:red;<?php if (isset($_SESSION['error_lmessage']) != '') { ?>display:block; <?php
                                                                                                                                    } else { ?>display:none; <?php
                                                                                                                                                            } ?>"><?php if (isset($_SESSION['error_lmessage']) != '') {
                                                                                                                                                                        echo $_SESSION['error_lmessage'];
                                                                                                                                                                    } ?></span>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">Email</h5>
                                        <input type="text" class="form-control" name="personEmail" placeholder="" required value="<?= isset($per_data) && isset($per_data['Email_URL_Desc']) ? $per_data['Email_URL_Desc'] : '' ?>">
                                        <span style="color:red;<?php if (isset($_SESSION['error_emessage']) != '') { ?>display:block; <?php
                                                                                                                                    } else { ?>display:none; <?php
                                                                                                                                                            } ?>"><?php if (isset($_SESSION['error_emessage']) != '') {
                                                                                                                                                                        echo $_SESSION['error_emessage'];
                                                                                                                                                                    } ?></span>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">Phone</h5>
                                        <input type="text" class="form-control" name="personPhone" placeholder="" required value="<?= isset($per_data) && isset($per_data['Tel_Nbr']) ? $per_data['Tel_Nbr'] : '' ?>">
                                    </div>
                                    <div class="form-group col-md-6">
                                        &nbsp;
                                    </div>
                                    <div class="form-group col-md-6">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <a href="dealer_setup.php"><span class="badge badge-rounded badge-warning">Back to dealer setup</span></a>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once("includes/footer.php");
    ?>
</div>

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