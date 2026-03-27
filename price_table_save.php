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

if (isset($_POST['warrantyID'])) {
    $warrantyID = $_POST['warrantyID'];;
    $warrantyID = decryptData($warrantyID);
}
$Addl_Dlr_Mrkp_Actl_APU_Amt = NULL;
$Addl_Dlr_Mrkp_Actl_AEP_Amt = NULL;   
$Addl_Dlr_Mrkp_Actl_AER_Amt = NULL;
$Addl_Dlr_Mrkp_Actl_WEARABLES_Amt = NULL;
$Addl_Dlr_Mrkp_Actl_EVBC_Amt = NULL;
$Addl_Dlr_Mrkp_Actl_ACP_Amt = NULL;
$Addl_Dlr_Mrkp_Actl_HUDS_Amt = 0;
$Addl_Dlr_Mrkp_Actl_EEC_Amt = NULL;
$Addl_Dlr_Mrkp_Actl_UCP_Amt = NULL;


// $warrantyID =  $_POST['warrantyID'];
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($warrantyID)) {
    $_SESSION["errorMessage"] = "";
    $newQuantity = 1;

    if (isset($_POST["quantity"]) && is_numeric($_POST["quantity"])) {
        $newQuantity = $_POST["quantity"];
    }

    // Get the 'actual' markup values that were just set on the form
    if (isset($_POST["Dlr_Mrkp_Actl_Amt"])) {
        $Dlr_Mrkp_Actl_Amt = trim($_POST["Dlr_Mrkp_Actl_Amt"]);
        if (!is_numeric($Dlr_Mrkp_Actl_Amt)) {
            $_SESSION["errorMessage"] = "Supplied value for Base Coverage Markup is not numeric.  Please try again.";
        }
    }
    
    if (isset($_POST["dlr_Markp_Max_Amt"])) {
        $dlr_Markp_Max_Amt = trim($_POST["dlr_Markp_Max_Amt"]);
        if (!is_numeric($dlr_Markp_Max_Amt)) {
            $_SESSION["errorMessage"] = "Supplied value for Base Coverage Markup is not numeric.  Please try again.";
        }
    }


    if (isset($_POST["Addl_Dlr_Mrkp_Actl_APU_Amt"])) {
        $Addl_Dlr_Mrkp_Actl_APU_Amt = trim($_POST["Addl_Dlr_Mrkp_Actl_APU_Amt"]);
        if (!is_numeric($Addl_Dlr_Mrkp_Actl_APU_Amt)) {
            $_SESSION["errorMessage"] = "Supplied value for APU Markup is not numeric.  Please try again.";
        }
    }

    if (isset($_POST["Addl_Dlr_Mrkp_Actl_AEP_Amt"])) {
        $Addl_Dlr_Mrkp_Actl_AEP_Amt = trim($_POST["Addl_Dlr_Mrkp_Actl_AEP_Amt"]);
        if (!is_numeric($Addl_Dlr_Mrkp_Actl_AEP_Amt)) {
            $_SESSION["errorMessage"] = "Supplied value for AEP Markup is not numeric.  Please try again.";
        }
    }

    if (isset($_POST["Addl_Dlr_Mrkp_Actl_AER_Amt"])) {
        $Addl_Dlr_Mrkp_Actl_AER_Amt = trim($_POST["Addl_Dlr_Mrkp_Actl_AER_Amt"]);
        if (!is_numeric($Addl_Dlr_Mrkp_Actl_AER_Amt)) {
            $_SESSION["errorMessage"] = "Supplied value for Aerial Markup is not numeric.  Please try again.";
        }
    }

    if (isset($_POST["Addl_Dlr_Mrkp_Actl_WEARABLES_Amt"])) {
        $Addl_Dlr_Mrkp_Actl_WEARABLES_Amt = trim($_POST["Addl_Dlr_Mrkp_Actl_WEARABLES_Amt"]);
        if (!is_numeric($Addl_Dlr_Mrkp_Actl_WEARABLES_Amt)) {
            $_SESSION["errorMessage"] = "Supplied value for Wearables Markup is not numeric.  Please try again.";
        }
    }

    if (isset($_POST["Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt"])) {
        $Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt = trim($_POST["Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt"]);
        if (!is_numeric($Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt)) {
            $_SESSION["errorMessage"] = "Supplied value for Small Goods Markup is not numeric.  Please try again.";
        }
    }

    if (isset($_POST["Addl_Dlr_Mrkp_Actl_EVBC_Amt"])) {
        $Addl_Dlr_Mrkp_Actl_EVBC_Amt = trim($_POST["Addl_Dlr_Mrkp_Actl_EVBC_Amt"]);
        if (!is_numeric($Addl_Dlr_Mrkp_Actl_EVBC_Amt)) {
            $_SESSION["errorMessage"] = "Supplied value for Electric Vehicle Battery Coverage is not numeric.  Please try again.";
        }
    }

    if (isset($_POST["Addl_Dlr_Mrkp_Actl_EEC_Amt"])) {
        $Addl_Dlr_Mrkp_Actl_EEC_Amt = trim($_POST["Addl_Dlr_Mrkp_Actl_EEC_Amt"]);
        if (!is_numeric($Addl_Dlr_Mrkp_Actl_EEC_Amt)) {
            $_SESSION["errorMessage"] = "Supplied value for Enhanced Engine Coverage is not numeric.  Please try again.";
        }
    }

    if (isset($_POST["Addl_Dlr_Mrkp_Actl_ACP_Amt"])) {
        $Addl_Dlr_Mrkp_Actl_ACP_Amt = trim($_POST["Addl_Dlr_Mrkp_Actl_ACP_Amt"]);
        if (!is_numeric($Addl_Dlr_Mrkp_Actl_ACP_Amt)) {
            $_SESSION["errorMessage"] = "Supplied value for ACP Markup is not numeric.  Please try again.";
        }
    }
    if (isset($_POST["Addl_Dlr_Mrkp_Actl_HUDS_Amt"])) {
        $Addl_Dlr_Mrkp_Actl_HUDS_Amt = trim($_POST["Addl_Dlr_Mrkp_Actl_HUDS_Amt"]);
        if (!is_numeric($Addl_Dlr_Mrkp_Actl_HUDS_Amt)) {
            $_SESSION["errorMessage"] = "Supplied value for HUDS Markup is not numeric.  Please try again.";
        }
    }

     if (isset($_POST["Addl_Dlr_Mrkp_Actl_UCP_Amt"])) {
        $Addl_Dlr_Mrkp_Actl_UCP_Amt = trim($_POST["Addl_Dlr_Mrkp_Actl_UCP_Amt"]);
        if (!is_numeric($Addl_Dlr_Mrkp_Actl_UCP_Amt)) {
            $_SESSION["errorMessage"] = "Supplied value for UCP Markup is not numeric.  Please try again.";
        }
    }



    if (isset($_POST["isQuote"])) {
        $isQuote = $_POST["isQuote"];
    }

    /*
    print_r($_POST);
    echo "warrantyID=".$warrantyID;
    echo "isQuote=".$isQuote;
    die();
    */


    if ($_SESSION["errorMessage"] != "") {
        if ($isQuote == "Y") {
            header("location: create_warranty.php?warrantyID=" . encryptData($warrantyID) . "&isQuote=Y");
        } else {
            header("location: create_warranty.php?warrantyID=" . encryptData($warrantyID));
        }
        exit;
    }


    // Get our MAX values to be sure we are not overstepping those bounds
    $query = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='AEP'";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $Dlr_Mrkp_Max_AEP_Amt = $row["Dlr_Mrkp_Max_Amt"];

    $query = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='APU'";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $Dlr_Mrkp_Max_APU_Amt = $row["Dlr_Mrkp_Max_Amt"];

    $query = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='AER'";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $Dlr_Mrkp_Max_AER_Amt = $row["Dlr_Mrkp_Max_Amt"];

    $query = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='WEARABLES'";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $Dlr_Mrkp_Max_WEARABLES_Amt = $row["Dlr_Mrkp_Max_Amt"];

    $query = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='EVBC'";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $Dlr_Mrkp_Max_EVBC_Amt = $row["Dlr_Mrkp_Max_Amt"];

    $query = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='EEC'";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $Dlr_Mrkp_Max_EEC_Amt = $row["Dlr_Mrkp_Max_Amt"];

    $query = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='ACP'";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $Dlr_Mrkp_Max_ACP_Amt = $row["Dlr_Mrkp_Max_Amt"];

    $query = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='HUDS'";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $Dlr_Mrkp_Max_HUDS_Amt = $row["Dlr_Mrkp_Max_Amt"];

    
    $query = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='UCP'";
    $result = $link->query($query);
    $row = $result->fetch_assoc();
    $Dlr_Mrkp_Max_UCP_Amt = $row["Dlr_Mrkp_Max_Amt"];

    // var_dump($warrantyID);die;
    $query = "SELECT * FROM Cntrct WHERE Cntrct_ID=" . $warrantyID;
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $Dlr_Mrkp_Max_Amt = $row["Dlr_Mrkp_Max_Amt"];
    $Dlr_Sml_Goods_Max_Mrkp_Tot_Amt = $row["Dlr_Sml_Goods_Max_Mrkp_Tot_Amt"];
    $quantity = $row["Quantity"];


    
    // echo "Dlr_Mrkp_Actl_Amt=".$Dlr_Mrkp_Actl_Amt;
    // echo "<br />Dlr_Mrkp_Max_Amt=".$Dlr_Mrkp_Max_Amt.$Dlr_Mrkp_Actl_Amt > $Dlr_Mrkp_Max_Amt;
    // echo "<br />Dlr_Mrkp_Actl_Amt > Dlr_Mrkp_Max_Amt=".($Dlr_Mrkp_Actl_Amt > $Dlr_Mrkp_Max_Amt);
    // die();
    

    // Now check our values, that they are not over the MAX
    //  If they are, then return an error.
    if ($Dlr_Mrkp_Actl_Amt > $dlr_Markp_Max_Amt) {
        $_SESSION["errorMessage"] = "Base Coverage Markup is over Max.  Please adjust below $" . number_format($dlr_Markp_Max_Amt, 0);
    }
    if ($Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt > $Dlr_Sml_Goods_Max_Mrkp_Tot_Amt) {
        $_SESSION["errorMessage"] = "Small Goods Markup is over Max.  Please adjust below $" . number_format($Dlr_Sml_Goods_Max_Mrkp_Tot_Amt, 0);
    }
    if ($Addl_Dlr_Mrkp_Actl_AEP_Amt > $Dlr_Mrkp_Max_AEP_Amt) {
        $_SESSION["errorMessage"] = "AEP Markup is over Max.  Please adjust below $" . number_format($Dlr_Mrkp_Max_AEP_Amt, 0);
    }
    if ($Addl_Dlr_Mrkp_Actl_APU_Amt > $Dlr_Mrkp_Max_APU_Amt) {
        $_SESSION["errorMessage"] = "APU Markup is over Max.  Please adjust below $" . number_format($Dlr_Mrkp_Max_APU_Amt, 0);
    }
    if ($Addl_Dlr_Mrkp_Actl_AER_Amt > $Dlr_Mrkp_Max_AER_Amt) {
        $_SESSION["errorMessage"] = "Aerial Markup is over Max.  Please adjust below $" . number_format($Dlr_Mrkp_Max_AER_Amt, 0);
    }
    if (isset($Addl_Dlr_Mrkp_Actl_WEARABLES_Amt) && $Addl_Dlr_Mrkp_Actl_WEARABLES_Amt > $Dlr_Mrkp_Max_WEARABLES_Amt) {
        $_SESSION["errorMessage"] = "Wearables Markup is over Max.  Please adjust below $" . number_format($Dlr_Mrkp_Max_WEARABLES_Amt, 0);
    }

    if (isset($Addl_Dlr_Mrkp_Actl_EVBC_Amt) && $Addl_Dlr_Mrkp_Actl_EVBC_Amt > $Dlr_Mrkp_Max_EVBC_Amt) {
        $_SESSION["errorMessage"] = "Electric Vehicle Battery Coverage is over Max.  Please adjust below $" . number_format($Dlr_Mrkp_Max_EVBC_Amt, 0);
    }
    if (isset($Addl_Dlr_Mrkp_Actl_EEC_Amt) && $Addl_Dlr_Mrkp_Actl_EEC_Amt > $Dlr_Mrkp_Max_EEC_Amt) {
        $_SESSION["errorMessage"] = "Enhanced Engine Covrage is over Max.  Please adjust below $" . number_format($Dlr_Mrkp_Max_EEC_Amt, 0);
    }
    if (isset($Addl_Dlr_Mrkp_Actl_ACP_Amt) && $Addl_Dlr_Mrkp_Actl_ACP_Amt > $Dlr_Mrkp_Max_ACP_Amt) {
        $_SESSION["errorMessage"] = "Enhanced Engine Covrage is over Max.  Please adjust below $" . number_format($Dlr_Mrkp_Max_ACP_Amt, 0);
    }
    if (isset($Addl_Dlr_Mrkp_Actl_HUDS_Amt) && $Addl_Dlr_Mrkp_Actl_HUDS_Amt > $Dlr_Mrkp_Max_HUDS_Amt) {
        $_SESSION["errorMessage"] = "HUDS Coverage is over Max.  Please adjust below $" . number_format($Dlr_Mrkp_Max_HUDS_Amt, 0);
    }
    if (isset($Addl_Dlr_Mrkp_Actl_UCP_Amt) && $Addl_Dlr_Mrkp_Actl_UCP_Amt > $Dlr_Mrkp_Max_UCP_Amt) {
        $_SESSION["errorMessage"] = "Upfitter Conversion Package is over Max.  Please adjust below $" . number_format($Dlr_Mrkp_Max_UCP_Amt, 0);
    }
        
    if ($_SESSION["errorMessage"] != "") {
        if ($isQuote == "Y") {
            echo json_encode(['message' => $_SESSION["errorMessage"], 'error' => true]);
            // header("location: create_warranty.php?warrantyID=" . encryptData($warrantyID) . "&isQuote=Y#price-table");
        } else {
            echo json_encode(['message' => $_SESSION["errorMessage"], 'error' => true]);
            // header("location: create_warranty.php?warrantyID=" . encryptData($warrantyID) . "#price-table");
        }
        die();
    }


    /* Prepare an UPDATE statement to update a Cntrct entry for this Warranty based on these markup values */
    $stmt = mysqli_prepare($link, "UPDATE Cntrct SET Dlr_Mrkp_Actl_Amt=?, Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt=?,
                                Addl_Dlr_Mrkp_Actl_APU_Amt=?,Addl_Dlr_Mrkp_Actl_AEP_Amt=?,
                                Addl_Dlr_Mrkp_Actl_AER_Amt=?, Quantity=?, Addl_Dlr_Mrkp_Actl_WEARABLES_Amt=?, Addl_Dlr_Mrkp_Actl_EVBC_Amt=?, Addl_Dlr_Mrkp_Actl_EEC_Amt=?, Addl_Dlr_Mrkp_Actl_ACP_Amt=?, Addl_Dlr_Mrkp_Actl_HUDS_Amt=?, Addl_Dlr_Mrkp_Actl_UCP_Amt=? WHERE Cntrct_ID=?");

    $val1 = $Dlr_Mrkp_Actl_Amt;
    $val2 = $Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt;
    $val3 = $Addl_Dlr_Mrkp_Actl_APU_Amt;
    $val4 = $Addl_Dlr_Mrkp_Actl_AEP_Amt;
    $val5 = $Addl_Dlr_Mrkp_Actl_AER_Amt;
    $val8 = $Addl_Dlr_Mrkp_Actl_WEARABLES_Amt;
    $val6 = $newQuantity;
    $val7 = $warrantyID;
    $val9 = $Addl_Dlr_Mrkp_Actl_EVBC_Amt;
    // var_dump($Addl_Dlr_Mrkp_Actl_EEC_Amt);die;
    $val10 = $Addl_Dlr_Mrkp_Actl_EEC_Amt;
    $val11 = $Addl_Dlr_Mrkp_Actl_ACP_Amt;
    $val12 = $Addl_Dlr_Mrkp_Actl_HUDS_Amt;
    $val13 = $Addl_Dlr_Mrkp_Actl_UCP_Amt;

    //echo "Dlr_Mrkp_Actl_Amt=".$Dlr_Mrkp_Actl_Amt;



    mysqli_stmt_bind_param($stmt, "iiiiiiiiiiiii", $val1, $val2, $val3, $val4, $val5, $val6, $val8, $val9, $val10, $val11, $val12, $val13, $val7);

    /* Execute the statement */
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        $_SESSION["errorMessage"] = "Error updating warranty: " . mysqli_error($link);
        echo json_encode(['message' => $_SESSION["errorMessage"], 'error' => true]);
    }

    // Update the totals
    $stmt = mysqli_prepare($link, "UPDATE
    Cntrct
    SET
    MSRP_Amt =(COALESCE(Dlr_Mrkp_Actl_Amt,0) + COALESCE(Dlr_Cost_Amt,0) + COALESCE(Additional_Commission_Amt,0)),
    Sml_Goods_Tot_Amt =(
        COALESCE(Dlr_Sml_Goods_Cst_Tot_Amt,0) + COALESCE(Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt,0)
    ),
    Addl_MSRP_Amt =(
        COALESCE(Addl_Dlr_Cost_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_APU_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AEP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AER_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_WEARABLES_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EVBC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EEC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_ACP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_HUDS_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_UCP_Amt,0)
    ),
    Addl_Dlr_Mrkp_Actl_Amt =(
        COALESCE(Addl_Dlr_Mrkp_Actl_APU_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AEP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AER_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_WEARABLES_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EVBC_Amt,0) +COALESCE(Addl_Dlr_Mrkp_Actl_EEC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_ACP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_HUDS_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_UCP_Amt,0)
    ),
    Tot_Dlr_Mrkp_Act_Amt =(
        COALESCE(Dlr_Mrkp_Actl_Amt,0) + COALESCE(Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_APU_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AEP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AER_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_WEARABLES_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EVBC_Amt,0) +COALESCE(Addl_Dlr_Mrkp_Actl_EEC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_HUDS_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_UCP_Amt,0)
    ),
    Tot_MSRP_Amt =(
        COALESCE(Dlr_Mrkp_Actl_Amt,0) + COALESCE(Dlr_Cost_Amt,0) + COALESCE(Dlr_Sml_Goods_Cst_Tot_Amt,0) + COALESCE(Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt,0) + COALESCE(Addl_Dlr_Cost_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_APU_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AEP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AER_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_WEARABLES_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EVBC_Amt,0) +COALESCE(Addl_Dlr_Mrkp_Actl_EEC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_ACP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_HUDS_Amt,0) +  COALESCE(Additional_Commission_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_UCP_Amt,0)
    )
    WHERE Cntrct_ID=?");

    mysqli_stmt_bind_param($stmt, "i", $warrantyID);

    /* Execute the statement */
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        $_SESSION["errorMessage"] = "Error updating warranty: " . mysqli_error($link);
        echo json_encode(['message' => $_SESSION["errorMessage"], 'error' => true]);
    }
    // Update the Small Goods values.
    //$totalSGUpdateResult = updateWarrantySmallGoodsTotals($link,$warrantyID);


    // Now, check for the value of 'term' and 'coverage' and if they have changed from
    //  the starting values
    $query = "SELECT Cntrct_Lvl_Desc,Cntrct_Term_Mnths_Nbr,Veh_Type_Nbr,c.Cntrct_Dim_ID, cd.Wrap_Flg FROM
            Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Cntrct_ID=" . $warrantyID . " AND
            c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND v.Veh_ID = c.Veh_ID";

    $cntrctResult = $link->query($query);

    $numRows = mysqli_num_rows($cntrctResult);

    if ($numRows > 0) {
        $row = mysqli_fetch_assoc($cntrctResult);
        $Cntrct_Lvl_Desc = $row["Cntrct_Lvl_Desc"];
        $Cntrct_Term_Mnths_Nbr = $row["Cntrct_Term_Mnths_Nbr"];
        $Veh_Type_Nbr = $row["Veh_Type_Nbr"];
        $Cntrct_Dim_ID = $row["Cntrct_Dim_ID"];
        $Wrap_Flg = $row["Wrap_Flg"];
    } else {
        // Fail
        header("location: create_warranty.php?warrantyID=" . encryptData($warrantyID) . "&isQuote=Y");
    }


    // Sanitize flag value.
    if ($Wrap_Flg != "Y") {
        $Wrap_Flg = "N";
    }



    // Get the new term and coverage values from the form
    if (isset($_POST["newTerm"])) {
        $newTerm = trim($_POST["newTerm"]);
        if (!is_numeric($newTerm)) {
            $_SESSION["errorMessage"] = "Supplied value for New Term is not numeric.  Please try again.";
            header("location: warranty_summary.php?warrantyID=" . encryptData($warrantyID) . "&isQuote=Y");
        }
    } else {
        $newTerm = "";
    }


    // Flag for redirect behavior
    $gotoWarrantyPending = "N";

    if (isset($_POST["newCoverage"])) {
        $newCoverage = trim($_POST["newCoverage"]);
        if ($newCoverage == "S") {
            $newCoverageExpanded = 'Squad';
        } else if ($newCoverage == "B") {
            $newCoverageExpanded = 'Battalion';
        } else {
            $newCoverageExpanded = "";
        }
    } else {
        $newCoverage = "";
        $newCoverageExpanded = "";
    }

    // If term or coverage have changed, then create a copy of the quote
    if (($newTerm != $Cntrct_Term_Mnths_Nbr) || ($newCoverageExpanded != $Cntrct_Lvl_Desc)) {
        //echo "copying quote";
        // Remove this feature for now, cparry 5/15/2023.
        //$copyResult = copyQuote($link,$warrantyID,$newCoverageExpanded,$newTerm);
        //echo "<br/>done, result=".$copyResult;
        //die();

        //echo "found structure difference";
        //die();

        $stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim SET Cntrct_Lvl_Cd=?,Cntrct_Lvl_Desc=?,Cntrct_Term_Mnths_Nbr=?
                                    WHERE Cntrct_Dim_ID=?");

        $val1 = $newCoverage;
        $val2 = $newCoverageExpanded;
        $val3 = $newTerm;
        $val4 = $Cntrct_Dim_ID;

        mysqli_stmt_bind_param($stmt, "ssdi", $val1, $val2, $val3, $val4);

        /* Execute the statement */
        $result = mysqli_stmt_execute($stmt);

        // Check if wholesale account
        // $query = "SELECT Wholesale_Flg FROM Cntrct c, Acct a WHERE c.Cntrct_ID=" . $warrantyID . " AND
        //           c.Mfr_Acct_ID=a.Acct_ID;";

        /**maintain warranty level wholesale flag */
        if (!$result) {
            $_SESSION["errorMessage"] = "Error updating warranty: " . mysqli_error($link);
            echo json_encode(['message' => $_SESSION["errorMessage"], 'error' => true]);
        }

        $query = "SELECT Wholesale_Flg FROM Cntrct_Dim WHERE Cntrct_Dim_ID=" . $Cntrct_Dim_ID . ";";
        $result = $link->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row["Wholesale_Flg"] == "Y") {
                $wholesale_flg = $row["Wholesale_Flg"];
            } else {
                $wholesale_flg = "N";
            }
        } else {
            $wholesale_flg = "N";
        }
        // var_dump($Wrap_Flg,$link, $newTerm, $Veh_Type_Nbr, $newCoverage, $wholesale_flg ); die;

        // Look up the base values from Wrnty_Std_Prcg based on term, type and tier
        if ($Wrap_Flg == 'N') {
            $warrantyBasePricingResult = selectWarrantyBasePricing($link, $newTerm, $Veh_Type_Nbr, $newCoverage, $wholesale_flg);
        } else {
            $warrantyBasePricingResult = selectwrapWarrantyBasePricing($link, $newTerm, $Veh_Type_Nbr, "B", $wholesale_flg);
        }


        //$warrantyBasePricingResult = selectWarrantyBasePricing($link, $newTerm, $Veh_Type_Nbr, $newCoverage);
        $row = mysqli_fetch_assoc($warrantyBasePricingResult);

        $Sales_Agt_Cost_Amt = $row["Sales_Agt_Cost_Amt"];
        $Sales_Agt_Commission_Amt = $row["Sales_Agt_Commission_Amt"];
        $Dlr_Cost_Amt = $row["Dlr_Cost_Amt"];
        $Dlr_Mrkp_Max_Amt = $row["Dlr_Mrkp_Max_Amt"];
        //$Dlr_Mrkp_Actl_Amt =  $Dlr_Mrkp_Actl_Amt == 0 ? $row["Dlr_Mrkp_Max_Amt"] : $Dlr_Mrkp_Actl_Amt;
        $Dlr_Mrkp_Actl_Amt =
    ((int)$Dlr_Mrkp_Actl_Amt === 0 || $Dlr_Mrkp_Actl_Amt === '0')
        ? 0
        : (($Dlr_Mrkp_Actl_Amt === null || $Dlr_Mrkp_Actl_Amt === '')
            ? $row["Dlr_Mrkp_Max_Amt"]
            : $Dlr_Mrkp_Actl_Amt);
        $MSRP_Amt = $row["MSRP_Amt"];
        $Additional_Commission_Amt = $row["Additional_Commission"];

        $stmt = mysqli_prepare($link, "UPDATE Cntrct SET Sales_Agt_Cost_Amt=?, Sales_Agt_Commission_Amt=?,
                                    Dlr_Cost_Amt=?, Dlr_Mrkp_Max_Amt=?, Dlr_Mrkp_Actl_Amt=?, Additional_Commission_Amt=?, MSRP_Amt=? WHERE
                                    Cntrct_ID=?");

        $val1 = $Sales_Agt_Cost_Amt;
        $val2 = $Sales_Agt_Commission_Amt;
        $val3 = $Dlr_Cost_Amt;
        $val4 = $Dlr_Mrkp_Max_Amt;
        $val5 = $Dlr_Mrkp_Actl_Amt;
        $val6 = $MSRP_Amt;
        $val7 = $warrantyID;
        $val8 = $Additional_Commission_Amt;

        mysqli_stmt_bind_param($stmt, "iiiiiiii", $val1, $val2, $val3, $val4, $val5, $val8, $val6, $val7);
        $result = mysqli_stmt_execute($stmt);


        // Update the totals
        $stmt = mysqli_prepare($link, "UPDATE
        Cntrct
        SET
        MSRP_Amt =(COALESCE(Dlr_Mrkp_Actl_Amt,0) + COALESCE(Dlr_Cost_Amt,0) + COALESCE(Additional_Commission_Amt,0)),
        Sml_Goods_Tot_Amt =(
            COALESCE(Dlr_Sml_Goods_Cst_Tot_Amt,0) + COALESCE(Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt,0)
        ),
        Addl_MSRP_Amt =(
            COALESCE(Addl_Dlr_Cost_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_APU_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AEP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AER_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_WEARABLES_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EVBC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EEC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_ACP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_HUDS_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_UCP_Amt,0)
        ),
        Addl_Dlr_Mrkp_Actl_Amt =(
            COALESCE(Addl_Dlr_Mrkp_Actl_APU_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AEP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AER_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_WEARABLES_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EVBC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EEC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_ACP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_HUDS_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_UCP_Amt,0)
        ),
        Tot_Dlr_Mrkp_Act_Amt =(
            COALESCE(Dlr_Mrkp_Actl_Amt,0) + COALESCE(Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_APU_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AEP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AER_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_WEARABLES_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EVBC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EEC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_ACP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_HUDS_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_UCP_Amt,0)
        ),
        Tot_MSRP_Amt =(
            COALESCE(Dlr_Mrkp_Actl_Amt,0) + COALESCE(Dlr_Cost_Amt,0) + COALESCE(Dlr_Sml_Goods_Cst_Tot_Amt,0) + COALESCE(Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt,0) + COALESCE(Addl_Dlr_Cost_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_APU_Amt,0) +COALESCE( Addl_Dlr_Mrkp_Actl_AEP_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_AER_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_WEARABLES_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_EVBC_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_EEC_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_ACP_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_HUDS_Amt ,  0 )+COALESCE ( Additional_Commission_Amt ,  0 ) + COALESCE(Addl_Dlr_Mrkp_Actl_UCP_Amt,0)
        )
        WHERE Cntrct_ID=?");


        mysqli_stmt_bind_param($stmt, "i", $warrantyID);

        /* Execute the statement */
        $result = mysqli_stmt_execute($stmt);
        //$totalUpdateResult = updateWarrantyTotals($link,$warrantyID);


        if (!$result) {
            $_SESSION["errorMessage"] = "Error updating warranty: " . mysqli_error($link);
            echo json_encode(['message' => $_SESSION["errorMessage"], 'error' => true]);
        }
        $gotoWarrantyPending = "Y";
    }


    // Rewrite the PDF - switch on wrap flag
    // cparry 6/8/2023.

    //echo "just before pdf update, warrantyID=".$warrantyID;
    //die();
    $pdfResult = createWarrantyPDF($link, $warrantyID, $isQuote, $Wrap_Flg);


    // Lastly, see if 'quantity' is > 1, and if so determine the extended prices
    // Save our quantity value received from the page.
    if (isset($_POST["quantity"]) && is_numeric($_POST["quantity"])) {
        $newQuantity = $_POST["quantity"];

        // If we got a quantity N that is greater than 1, then we want to create N-1 copies of this quote.
        if ($newQuantity > 1) {
            for ($i = 1; $i < $newQuantity; $i++) {

                // Remove this feature for now, cparry 5/15/2023.
                //$newCntrctID = copyQuote($link,$warrantyID);
                //echo "creating new quote, contractID = ".$newCntrctID;
                //echo "<br />i = ".$i;
                //echo "<br />newQuantity = ".$newQuantity;
            }
        }
        //die();

        // Now set newQuantity back to 1, since we do not actually want to increment it in the Cntrct table.
        //$newQuantity = 1;

        $gotoWarrantyPending = "Y";
    }


    if ($gotoWarrantyPending == "Y") {
        //header("location: warranty_pending.php?showQuotes=Y");
        //die();
    }




    // if ($isQuote == "Y") {
    // 	header("location: warranty_summary.php?warrantyID=" . encryptData($warrantyID) . "&isQuote=Y");
    // } else {
    // 	header("location: warranty_summary.php?warrantyID=" . encryptData($warrantyID));
    // }
    // exit;

    if ($_SESSION["errorMessage"] == "") {
        echo json_encode(['message' => "Update successful", 'error' => false]);
    } 
}