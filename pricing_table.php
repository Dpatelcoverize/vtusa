<?php
//
// File: pricing_table.php
// Author: Hardik Santoki
// Date: 25/06/2025
//

// Turn on error reporting
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
//error_reporting(E_ALL);



// Connect to DB
require_once "includes/dbConnect.php";

/**For encryption of the data */
require_once 'encrypt.php';

// DB Library
require_once "lib/dblib.php";


// // PDF function
// require_once "lib/pdfHelper.php";


// Variables.
$pageAction = "";
$toggleType = "";

$dealerID = "";
$smallGoodsCoverageID = "";
$smallGoodsPricingID = "";
$warrantyID = "";
$terms = "";
$coverageType = "";
$wearablesCoverage = "";
$aerialPackageCoverage = "";
$isAepCoverage = "";
$isEvbcCoverage = "";
$isEecCoverage = "";
$vehicleType = "";
$wrapProgramm = "";
$smallGoodsPackage = "";
$isAjax = false;
$vehicleID = "";
$quantity = 1;
$errorMessage = "";

$itemCategoryCode = "";
$itemCategoryDesc = "";
$quantityCount = "";
$manufacturedYear = "";
$manufacturerName = "";
$modelNumber = "";
$serialNumber = "";
$makeNumber = "";
$limitOfLiabilityAmount = "";
$salesAgentCostAmount = "";
$salesAgentCommissionAmount = "";
$dealerCostAmount = "";
$dealerMarkupAmount = "";
$actualPriceAmount = "";
$haveReceipt = "";

$Dlr_Mrkp_Max_Amt = 0;
$Dlr_Mrkp_Actl_Amt = 0;
$Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt = 0;
$Addl_Dlr_Mrkp_Actl_Amt = 0;
$Dlr_Sml_Goods_Max_Mrkp_Tot_Amt = 0;
$Tot_Dlr_Mrkp_Max_Amt = 0;

$Wrap_Flg = "N";

$Addl_Dlr_Mrkp_Actl_APU_Amt = 0;
$Addl_Dlr_Mrkp_Actl_AEP_Amt = 0;
$Addl_Dlr_Mrkp_Actl_AER_Amt = 0;
$Addl_Dlr_Mrkp_Actl_EVBC_Amt = 0;
$Addl_Dlr_Mrkp_Actl_EEC_Amt = 0;
$Addl_Dlr_Mrkp_Actl_ACP_Amt = 0; 
$Addl_Dlr_Mrkp_Actl_HUDS_Amt = 0;
$Addl_Dlr_Mrkp_Actl_UCP_Amt = 0; 

$wrnty_Stat_Desc = "";

$isQuote = "";

$Sales_Agt_Cost_Amt = 0;


$form_err    = "";

$currentSelectionsArray = array();
$loopCounter = 0;
$oldTag = "OLD";


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role_ID = $_SESSION["role_ID"];


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
}


// See if we are specifying a warrantyID in the URL request.
if (isset($_GET["warrantyID"])) {
    $warrantyID = $_GET["warrantyID"];
    $warrantyID = decryptData($warrantyID);
} else {
    header("location: warranty_pending.php");
    exit;
}

if (isset($_GET["terms"])) {
    $terms = $_GET["terms"];
}

if (isset($_GET["coverageType"])) {
    $coverageType = $_GET["coverageType"] == 'S' ? 'Squad' : 'Battalion';
}

if (isset($_GET["wearables"])) {
    $wearablesCoverage = $_GET["wearables"];
}

if (isset($_GET["aerialPackage"])) {
    $aerialPackageCoverage = $_GET["aerialPackage"];
}

if (isset($_GET["aep"])) {
    $isAepCoverage = $_GET["aep"];
}

if (isset($_GET["evbcCoverage"])) {
    $isEvbcCoverage = $_GET["evbcCoverage"];
}

if (isset($_GET["eecCoverage"])) {
    $isEecCoverage = $_GET["eecCoverage"];
}

if (isset($_GET["acpCoverage"])) {
    $isAcpCoverage = $_GET["acpCoverage"];
}

if (isset($_GET["hudsCoverage"])) {
    $isHudsCoverage = $_GET["hudsCoverage"];
}

if (isset($_GET["ucpCoverage"])) {
    $isUcpCoverage = $_GET["ucpCoverage"];
}

if (isset($_GET["vehicleType"])) {
    $vehicleType = (int)$_GET["vehicleType"];
}

if (isset($_GET["wrapProgramm"])) {
    $wrapProgramm = $_GET["wrapProgramm"];
}

if (isset($_GET["isQuote"])) {
    $isQuote = $_GET["isQuote"];
}

if (isset($_GET["smallGoodsPackage"])) {
    $smallGoodsPackage = $_GET["smallGoodsPackage"];
}

if (isset($_GET["ajax"])) {
    $isAjax = (bool)$_GET["ajax"];
}
if (isset($_GET["oldTag"])) {
    $oldTag = $_GET["oldTag"];
}
if (isset($_GET["wrntyStatDesc"])) {
    $wrnty_Stat_Desc = $_GET["wrntyStatDesc"];
}


// Check page action
if (isset($_GET["pageAction"])) {
    $pageAction = $_GET["pageAction"];
}

if ($pageAction == "priceToggle") {

    if (isset($_GET["toggleType"])) {

        $toggleType = $_GET["toggleType"];

        // Get 'term', 'type' and 'coverage' for this contract
        $query = "SELECT Cntrct_Lvl_Desc,Cntrct_Lvl_Cd,Cntrct_Term_Mnths_Nbr,Veh_Type_Nbr,c.Cntrct_Dim_ID, cd.Wrap_Flg FROM
				  Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Cntrct_ID=" . $warrantyID . " AND
				  c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND v.Veh_ID = c.Veh_ID";
        $cntrctResult = $link->query($query);

        $numRows = mysqli_num_rows($cntrctResult);
        if ($numRows > 0) {
            $row = mysqli_fetch_assoc($cntrctResult);
            $Cntrct_Lvl_Desc = $row["Cntrct_Lvl_Desc"];
            $Cntrct_Lvl_Cd = $row["Cntrct_Lvl_Cd"];
            $Cntrct_Term_Mnths_Nbr = $row["Cntrct_Term_Mnths_Nbr"];
            $Veh_Type_Nbr = $row["Veh_Type_Nbr"];
            $Cntrct_Dim_ID = $row["Cntrct_Dim_ID"];
            $Wrap_Flg = $row["Wrap_Flg"];
        } else {
            // Fail
            header("location: warranty_summary.php?warrantyID=" . encryptData($warrantyID) . "&isQuote=Y");
        }

        $toggleTypeOpposite = "";
        if ($Wrap_Flg == 'N') {
            if ($toggleType == "wholesale") {
                // Get wholesale pricing
                $warrantyBasePricingResult = selectWarrantyBasePricing($link, $Cntrct_Term_Mnths_Nbr, $Veh_Type_Nbr, $Cntrct_Lvl_Cd, "Y");
                $toggleTypeOpposite = "standard";
            } else if ($toggleType == "standard") {
                // Get standard pricing
                $warrantyBasePricingResult = selectWarrantyBasePricing($link, $Cntrct_Term_Mnths_Nbr, $Veh_Type_Nbr, $Cntrct_Lvl_Cd, "N");
                $toggleTypeOpposite = "wholesale";
            }
        } else {
            if ($toggleType == "wholesale") {
                // Get wholesale pricing
                $warrantyBasePricingResult = selectwrapWarrantyBasePricing($link, $Cntrct_Term_Mnths_Nbr, $Veh_Type_Nbr, "B", "Y");
                $toggleTypeOpposite = "standard";
            } else if ($toggleType == "standard") {
                // Get standard pricing
                $warrantyBasePricingResult = selectwrapWarrantyBasePricing($link, $Cntrct_Term_Mnths_Nbr, $Veh_Type_Nbr, "B", "N");
                $toggleTypeOpposite = "wholesale";
            }
        }

        /**update wholesale flag at warranty level */
        $wholesale_flg = $toggleType == 'standard' ? 'N' : ($toggleType == 'wholesale' ? 'Y' : 'N');

        $stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim SET Wholesale_Flg=? WHERE
									   Cntrct_Dim_ID=?");
        mysqli_stmt_bind_param($stmt, "si", $wholesale_flg, $Cntrct_Dim_ID);
        $result = mysqli_stmt_execute($stmt);


        $row = mysqli_fetch_assoc($warrantyBasePricingResult);


        $Sales_Agt_Cost_Amt = $row["Sales_Agt_Cost_Amt"];
        $Sales_Agt_Commission_Amt = $row["Sales_Agt_Commission_Amt"];
        $Dlr_Cost_Amt = $row["Dlr_Cost_Amt"];
        $Dlr_Mrkp_Max_Amt = $row["Dlr_Mrkp_Max_Amt"];
        $Dlr_Mrkp_Actl_Amt = $row["Dlr_Mrkp_Max_Amt"];
        $MSRP_Amt = $row["MSRP_Amt"];
        $Additional_Commission_Amt = $row["Additional_Commission"];

        $stmt = mysqli_prepare($link, "UPDATE Cntrct SET Sales_Agt_Cost_Amt=?, Sales_Agt_Commission_Amt=?,
									   Dlr_Cost_Amt=?, Dlr_Mrkp_Max_Amt=?, Dlr_Mrkp_Actl_Amt=?, Additional_Commission_Amt=?, MSRP_Amt=?,
									   Wrnty_Stat_Desc=? WHERE
									   Cntrct_ID=?");

        $val1 = $Sales_Agt_Cost_Amt;
        $val2 = $Sales_Agt_Commission_Amt;
        $val3 = $Dlr_Cost_Amt;
        $val4 = $Dlr_Mrkp_Max_Amt;
        $val5 = $Dlr_Mrkp_Actl_Amt;
        $val6 = $MSRP_Amt;
        $val7 = $toggleType;
        $val8 = $warrantyID;
        $val9 = $Additional_Commission_Amt;

        mysqli_stmt_bind_param($stmt, "iiiiiiisi", $val1, $val2, $val3, $val4, $val5, $val9, $val6, $val7, $val8);

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
			COALESCE(Addl_Dlr_Cost_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_APU_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AEP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AER_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_WEARABLES_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EVBC_Amt,0) +COALESCE( Addl_Dlr_Mrkp_Actl_EEC_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_ACP_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_HUDS_Amt ,  0 ) + COALESCE(Addl_Dlr_Mrkp_Actl_UCP_Amt,0)
		),
		Addl_Dlr_Mrkp_Actl_Amt =(
			COALESCE(Addl_Dlr_Mrkp_Actl_APU_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AEP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_AER_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_WEARABLES_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EVBC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_EEC_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_ACP_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_HUDS_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_UCP_Amt,0)
		),
		Tot_Dlr_Mrkp_Act_Amt =(
			COALESCE(Dlr_Mrkp_Actl_Amt,0) + COALESCE(Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_APU_Amt,0) + COALESCE( Addl_Dlr_Mrkp_Actl_AEP_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_AER_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_WEARABLES_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_EVBC_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_EEC_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_ACP_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_HUDS_Amt ,  0 ) + COALESCE(Addl_Dlr_Mrkp_Actl_UCP_Amt,0)
		),
		Tot_MSRP_Amt =(
			COALESCE(Dlr_Mrkp_Actl_Amt,0) + COALESCE(Dlr_Cost_Amt,0) + COALESCE(Dlr_Sml_Goods_Cst_Tot_Amt,0) + COALESCE(Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt,0) + COALESCE(Addl_Dlr_Cost_Amt,0) + COALESCE(Addl_Dlr_Mrkp_Actl_APU_Amt,0) +COALESCE( Addl_Dlr_Mrkp_Actl_AEP_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_AER_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_WEARABLES_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_EVBC_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_EEC_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_ACP_Amt ,  0 )+COALESCE ( Addl_Dlr_Mrkp_Actl_HUDS_Amt ,  0 )+COALESCE ( Additional_Commission_Amt ,  0 ) + COALESCE(Addl_Dlr_Mrkp_Actl_UCP_Amt,0)
		)
		WHERE Cntrct_ID=?");

        mysqli_stmt_bind_param($stmt, "i", $warrantyID);

        /* Execute the statement */
        $result = mysqli_stmt_execute($stmt);
    }


    $encryptedId = encryptData($warrantyID);
    if ($isQuote == "Y") {
        header("location: create_warranty.php?warrantyID=" . $encryptedId . "&isQuote=Y");
    } else {
        header("location: create_warranty.php?warrantyID=" . $encryptedId);
    }
}


// See if we have data for this warranty contract already saved
if ($warrantyID != "") {

    $updateResult = updateWarrantyTotals($link, $warrantyID);

    $query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Cntrct_ID=" . $warrantyID . " AND
	          c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND v.Veh_ID = c.Veh_ID";


    $cntrctResult = $link->query($query);

    $numRows = mysqli_num_rows($cntrctResult);
    if ($numRows > 0) {
        $row = mysqli_fetch_assoc($cntrctResult);
        // Check flags
        $AEP_Flg = $isAepCoverage;
        $APU_Flg = $row["APU_Flg"];
        $Aerial_Flg = $aerialPackageCoverage;
        $Small_Goods_Pkg_Flg = $smallGoodsPackage;
        $Wrap_Flg = $wrapProgramm;
        $wearables_flag = $wearablesCoverage;
        $EVBC_Flg = $isEvbcCoverage;
        $EEC_Flg = $isEecCoverage;
        $ACP_Flg = $isAcpCoverage;
        $HUDS_Flg = $isHudsCoverage;
        $UCP_Flg = $isUcpCoverage;
        // if (is_numeric($row["Veh_Model_Yr_Cd"])) {
        //     if (date("Y") - $row["Veh_Model_Yr_Cd"] > 15) {
        //         $Old_Flg = "Y";
        //     } else {
        //         $Old_Flg = "N";
        //     }
        // } else {
        //     $Old_Flg = "N";
        // }
        if ($oldTag == "OLD" || $oldTag == "OLD2") {
            $Old_Flg = "Y";
        } else {
            $Old_Flg = "N";
        }

        // Vehicle info
        $Veh_Type_Nbr = $vehicleType;
        $Veh_Id_Nbr = $row["Veh_Id_Nbr"];
        $Veh_Model_Yr_Cd = (int)$row["Veh_Model_Yr_Cd"];
        // Pricing
        $Sales_Agt_Cost_Amt = $row["Sales_Agt_Cost_Amt"];
        $Cntrct_Lvl_Desc = $coverageType;
        $Cntrct_Term_Mnths_Nbr = $terms;
        $Dlr_Cost_Amt = $row["Dlr_Cost_Amt"];
        $Dlr_Mrkp_Actl_Amt = $row["Dlr_Mrkp_Actl_Amt"];
        $Dlr_Mrkp_Max_Amt = $row["Dlr_Mrkp_Max_Amt"];
        $MSRP_Amt = $row["MSRP_Amt"];

        $Dlr_Sml_Goods_Cst_Tot_Amt = $row["Dlr_Sml_Goods_Cst_Tot_Amt"];
        $Dlr_Sml_Goods_Max_Mrkp_Tot_Amt = $row["Dlr_Sml_Goods_Max_Mrkp_Tot_Amt"];
        $Sml_Goods_Tot_Amt = !empty($row['Sml_Goods_Tot_Amt']) ? $row['Sml_Goods_Tot_Amt'] : 0;


        $Addl_Dlr_Mrkp_Actl_APU_Amt = $row["Addl_Dlr_Mrkp_Actl_APU_Amt"];
        $Addl_Dlr_Mrkp_Actl_AEP_Amt = $row["Addl_Dlr_Mrkp_Actl_AEP_Amt"];
        // var_dump($Addl_Dlr_Mrkp_Actl_AEP_Amt);die;
        $Addl_Dlr_Mrkp_Actl_AER_Amt = $row["Addl_Dlr_Mrkp_Actl_AER_Amt"];
        $Addl_Dlr_Mrkp_Actl_WEARABLES_Amt  = $row["Addl_Dlr_Mrkp_Actl_WEARABLES_Amt"];
        $Addl_Dlr_Mrkp_Actl_EVBC_Amt  = $row["Addl_Dlr_Mrkp_Actl_EVBC_Amt"];
        $Addl_Dlr_Mrkp_Actl_EEC_Amt  = $row["Addl_Dlr_Mrkp_Actl_EEC_Amt"];
        $Addl_Dlr_Mrkp_Actl_ACP_Amt  = $row["Addl_Dlr_Mrkp_Actl_ACP_Amt"];
        $Addl_Dlr_Mrkp_Actl_HUDS_Amt  = $row["Addl_Dlr_Mrkp_Actl_HUDS_Amt"];
        $Addl_Dlr_Mrkp_Actl_UCP_Amt  = $row["Addl_Dlr_Mrkp_Actl_UCP_Amt"];

        $Tot_Dlr_Cost_Amt = $row["Tot_Dlr_Cost_Amt"];
        $Tot_Dlr_Mrkp_Act_Amt = $row["Tot_Dlr_Mrkp_Act_Amt"];
        $Tot_Dlr_Mrkp_Max_Amt = $row["Tot_Dlr_Mrkp_Max_Amt"];
        $Tot_MSRP_Amt = $row["Tot_MSRP_Amt"];

        // Add on values
        $Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt = $row["Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt"];
        $Addl_Dlr_Mrkp_Actl_Amt = $row["Addl_Dlr_Mrkp_Actl_Amt"];

        $quantity = $row["Quantity"];

        $wrnty_Stat_Desc = isset($row["Wrnty_Stat_Desc"]) ?  $row["Wrnty_Stat_Desc"] : "";
        //echo $wrnty_Stat_Desc;
    }
}
else{
    $quantity = 1;
    $Tot_Dlr_Mrkp_Act_Amt =0;
    $Dlr_Mrkp_Max_Amt = null;
    $Dlr_Mrkp_Actl_Amt = null;
    $Addl_Dlr_Mrkp_Actl_APU_Amt = null;
    $Addl_Dlr_Mrkp_Actl_AEP_Amt = null;
    $Addl_Dlr_Mrkp_Actl_AER_Amt = null;
    $Addl_Dlr_Mrkp_Actl_EVBC_Amt = null;
    $Addl_Dlr_Mrkp_Actl_EEC_Amt = null;
    $Addl_Dlr_Mrkp_Actl_ACP_Amt = null;
    $Addl_Dlr_Mrkp_Actl_HUDS_Amt = 0;
    $Addl_Dlr_Mrkp_Actl_UCP_Amt = null;

    $AEP_Flg = $isAepCoverage;
    $Aerial_Flg = $aerialPackageCoverage;
    $Small_Goods_Pkg_Flg = $smallGoodsPackage;
    $Wrap_Flg = $wrapProgramm;
    $wearables_flag = $wearablesCoverage;
    $EVBC_Flg = $isEvbcCoverage;
    $EEC_Flg = $isEecCoverage;
    $ACP_Flg = $isAcpCoverage;
    $HUDS_Flg = $isHudsCoverage;
    $Veh_Type_Nbr = $vehicleType;
    $Cntrct_Lvl_Desc = $coverageType;
    $Cntrct_Term_Mnths_Nbr = $terms;
    $UCP_Flg = $isUcpCoverage;
    // if (is_numeric($row["Veh_Model_Yr_Cd"])) {
    if ($oldTag == "OLD" || $oldTag == "OLD2") {
        $Old_Flg = "Y";
    } else {
        $Old_Flg = "N";
    }
        // } else {
        //     $Old_Flg = "N";
        // }
    // $oldTag = "OLD";
}



if (isset($_SESSION["errorMessage"]) && ($_SESSION["errorMessage"] != "")) {
    $errorMessage = $_SESSION["errorMessage"];
    $_SESSION["errorMessage"] = "";
} else {
    $errorMessage = "";
}


// Additional Standard Pricing default values
$aepQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='AEP'";

$aepResult = $link->query($aepQuery);

$aepRows = mysqli_num_rows($aepResult);
if ($aepRows == 1) {
    $aepRow = mysqli_fetch_assoc($aepResult);
    $aep_Dlr_Cost_Amt = $aepRow["Dlr_Cost_Amt"];
    $aep_Dlr_Mrkp_Max_Amt = $aepRow["Dlr_Mrkp_Max_Amt"];
    $aep_MSRP_Amt = $aepRow["MSRP_Amt"];
} else {
    $aep_Dlr_Cost_Amt = 0;
    $aep_Dlr_Mrkp_Max_Amt = 0;
    $aep_MSRP_Amt = 0;
}


$apuQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='APU'";

$apuResult = $link->query($apuQuery);

$apuRows = mysqli_num_rows($apuResult);
if ($apuRows > 0) {
    $apuRow = mysqli_fetch_assoc($apuResult);
    $apu_Dlr_Cost_Amt = $apuRow["Dlr_Cost_Amt"];
    $apu_Dlr_Mrkp_Max_Amt = $apuRow["Dlr_Mrkp_Max_Amt"];
    $apu_MSRP_Amt = $apuRow["MSRP_Amt"];
} else {
    $apu_Dlr_Cost_Amt = 0;
    $apu_Dlr_Mrkp_Max_Amt = 0;
    $apu_MSRP_Amt = 0;
}


$aerQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='AER'";

$aerResult = $link->query($aerQuery);

$aerRows = mysqli_num_rows($aerResult);
if ($aerRows > 0) {
    $aerRow = mysqli_fetch_assoc($aerResult);
    $aer_Dlr_Cost_Amt = $aerRow["Dlr_Cost_Amt"];
    $aer_Dlr_Mrkp_Max_Amt = $aerRow["Dlr_Mrkp_Max_Amt"];
    $aer_MSRP_Amt = $aerRow["MSRP_Amt"];
} else {
    $aer_Dlr_Cost_Amt = 0;
    $aer_Dlr_Mrkp_Max_Amt = 0;
    $aer_MSRP_Amt = 0;
}

$aepQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='WEARABLES'";

$aepResult = $link->query($aepQuery);

$wRows = mysqli_num_rows($aepResult);
if ($wRows == 1) {
    $wRows = mysqli_fetch_assoc($aepResult);
    $wearable_Dlr_Cost_Amt = $wRows["Dlr_Cost_Amt"];
    $wearable_Dlr_Mrkp_Max_Amt = $wRows["Dlr_Mrkp_Max_Amt"];
    $wearable_MSRP_Amt = $wRows["MSRP_Amt"];
} else {
    $wearable_Dlr_Cost_Amt = 0;
    $wearable_Dlr_Mrkp_Max_Amt = 0;
    $wearable_MSRP_Amt = 0;
}


// $oldTag = "OLD";

// if (date("Y") - $Veh_Model_Yr_Cd > 20) {
//     $oldTag = "OLD2";
// } else if (date("Y") - $Veh_Model_Yr_Cd > 15) {
//     $oldTag = "OLD";
// }


$oldQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='" . $oldTag . "'";

$oldResult = $link->query($oldQuery);

$oldRows = mysqli_num_rows($oldResult);
if ($oldRows > 0) {
    $oldRow = mysqli_fetch_assoc($oldResult);
    $old_Dlr_Cost_Amt = $oldRow["Dlr_Cost_Amt"];
    $old_Dlr_Mrkp_Max_Amt = $oldRow["Dlr_Mrkp_Max_Amt"];
    $old_MSRP_Amt = $oldRow["MSRP_Amt"];
} else {
    $old_Dlr_Cost_Amt = 0;
    $old_Dlr_Mrkp_Max_Amt = 0;
    $old_MSRP_Amt = 0;
}

$evbcQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='EVBC'";

$evbcResult = $link->query($evbcQuery);

$evbcRows = mysqli_num_rows($evbcResult);
if ($evbcRows > 0) {
    $evbcRow = mysqli_fetch_assoc($evbcResult);
    $evbc_Dlr_Cost_Amt = $evbcRow["Dlr_Cost_Amt"];
    $evbc_Dlr_Mrkp_Max_Amt = $evbcRow["Dlr_Mrkp_Max_Amt"];
    $evbc_MSRP_Amt = $evbcRow["MSRP_Amt"];
} else {
    $evbc_Dlr_Cost_Amt = 0;
    $evbc_Dlr_Mrkp_Max_Amt = 0;
    $evbc_MSRP_Amt = 0;
}

$eecQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='EEC'";

$eecResult = $link->query($eecQuery);

$eecRows = mysqli_num_rows($eecResult);
if ($eecRows > 0) {
    $eecRow = mysqli_fetch_assoc($eecResult);
    $eec_Dlr_Cost_Amt = $eecRow["Dlr_Cost_Amt"];
    $eec_Dlr_Mrkp_Max_Amt = $eecRow["Dlr_Mrkp_Max_Amt"];
    $eec_MSRP_Amt = $eecRow["MSRP_Amt"];
} else {
    $eec_Dlr_Cost_Amt = 0;
    $eec_Dlr_Mrkp_Max_Amt = 0;
    $eec_MSRP_Amt = 0;
}

$acpQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='ACP'";

$acpResult = $link->query($acpQuery);

$acpRows = mysqli_num_rows($acpResult);
if ($acpRows > 0) {
    $acpRow = mysqli_fetch_assoc($acpResult);
    $acp_Dlr_Cost_Amt = $acpRow["Dlr_Cost_Amt"];
    $acp_Dlr_Mrkp_Max_Amt = $acpRow["Dlr_Mrkp_Max_Amt"];
    $acp_MSRP_Amt = $acpRow["MSRP_Amt"];
} else {
    $acp_Dlr_Cost_Amt = 0;
    $acp_Dlr_Mrkp_Max_Amt = 0;
    $acp_MSRP_Amt = 0;
}

$hudsQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='HUDS'";

$hudsResult = $link->query($hudsQuery);

$hudsRows = mysqli_num_rows($hudsResult);
if ($hudsRows > 0) {
    $hudsRow = mysqli_fetch_assoc($hudsResult);
    $huds_Dlr_Cost_Amt = $hudsRow["Dlr_Cost_Amt"];
    $huds_Dlr_Mrkp_Max_Amt = $hudsRow["Dlr_Mrkp_Max_Amt"];
    $huds_MSRP_Amt = $hudsRow["MSRP_Amt"];
} else {
    $huds_Dlr_Cost_Amt = 0;
    $huds_Dlr_Mrkp_Max_Amt = 0;
    $huds_MSRP_Amt = 0;
}

$ucpQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='UCP'";

$ucpResult = $link->query($ucpQuery);

$ucpRows = mysqli_num_rows($ucpResult);
if ($ucpRows > 0) {
    $ucpRow = mysqli_fetch_assoc($ucpResult);
    $ucp_Dlr_Cost_Amt = $ucpRow["Dlr_Cost_Amt"];
    $ucp_Dlr_Mrkp_Max_Amt = $ucpRow["Dlr_Mrkp_Max_Amt"];
    $ucp_MSRP_Amt = $ucpRow["MSRP_Amt"];
} else {
    $ucp_Dlr_Cost_Amt = 0;
    $ucp_Dlr_Mrkp_Max_Amt = 0;
    $ucp_MSRP_Amt = 0;
}


if (!is_numeric($quantity) || $quantity < 1) {
    $quantity = 1;
}


?>

<!--**********************************
            Content body start
        ***********************************-->
<div class="card">
    <div class="card-header text-center">
        <h4 class="card-title"><?php if ($isQuote == "Y") {
                                    echo "Quote";
                                } else {
                                    echo "Warranty";
                                } ?> Summary</h4>
        <?php
        if ($errorMessage != "") {
            echo "<h5>Notice: " . $errorMessage . "</h5>";
        }
        ?>
    </div>
    <?php
    if ($errorMessage != "") {
    ?>
        <div class="card-header text-center">
            <span style="color:red;">ERROR: <?php echo $errorMessage; ?></span>
        </div>
    <?php
    }
    ?>
    <div class="card-body">
        <div class="basic-form dealer-form">

            <div class="form-row">
                <div class="form-group col-md-12">
                    <style>
                        .alnright {
                            text-align: right;
                        }

                        table td {
                            padding: 3px;
                        }
                    </style>
                    <?php
                    $altColor = "lightgrey";
                    $nextColor = "white";
                    ?>
                    <input type="hidden" id="warrantyID" value="<?php echo $warrantyID; ?>">
                    <input type="hidden" id="isQuote" name="isQuote" value="<?php echo $isQuote; ?>">
                    <table border="1" cellpadding="5" cellspacing="5" class='alnright'>
                        <tr style="background-color:#201F58;color:#FFFFFF; font-weight:bold;text-align:center;">
                            <td>VIN</td>
                            <td>Vehicle Type</td>
                            <td>Term (Years)</td>
                            <td>Coverage</td>
                            <td>Dealer Cost</td>
                            <td>Dealer Markup</td>
                            <td>Dealer Max Markup</td>
                            <td>MSRP</td>
                            <td>Quantity</td>
                            <td>Extended MSRP</td>
                        </tr>
                        <tr style="background-color:<?php echo $nextColor; ?>">
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <script language="javascript">
                                <?php
                                $pricingArr = array();
                                $termsFloat = number_format((float)$terms, 1, '.', '');

                                // 1. Build the pricing array in PHP
                                if ($wrnty_Stat_Desc == "wholesale" && $Wrap_Flg == "N") {
                                    $pricingQuery = "SELECT * FROM Wrnty_Std_Prcg WHERE Base_Price_Table_Type_Code='Standard_Wholesale' AND Veh_Type_Cd=" . $Veh_Type_Nbr . " AND Tier_Type_Cd='" . $_GET["coverageType"] . "' AND Cvrg_Term_Yrs_Nbr='" . $termsFloat . "'";
                                } else if ($Wrap_Flg == "N") {
                                
                                    $pricingQuery = "SELECT * FROM Wrnty_Std_Prcg WHERE Base_Price_Table_Type_Code='Standard' AND Veh_Type_Cd=". $Veh_Type_Nbr . " AND Tier_Type_Cd='" . $_GET["coverageType"] . "' AND Cvrg_Term_Yrs_Nbr='" . $termsFloat . "'";
                                   
                                } else if ($wrnty_Stat_Desc == "wholesale" && $Wrap_Flg == "Y") {
                                    $pricingQuery = "SELECT * FROM Wrnty_Std_Prcg WHERE Base_Price_Table_Type_Code='Wrap_Wholesale' AND Veh_Type_Cd=" . $Veh_Type_Nbr . " AND Tier_Type_Cd='" . $_GET["coverageType"] . "' AND Cvrg_Term_Yrs_Nbr='" . $termsFloat . "'";
                                } else if ($Wrap_Flg == "Y") {
                                    $pricingQuery = "SELECT * FROM Wrnty_Std_Prcg WHERE Base_Price_Table_Type_Code='Wrap' AND Veh_Type_Cd=" . $Veh_Type_Nbr . " AND Tier_Type_Cd='" . $_GET["coverageType"] . "' AND Cvrg_Term_Yrs_Nbr='" . $termsFloat . "'";
                                }

                               
                                $pricingResult = $link->query($pricingQuery);
                                $row = mysqli_fetch_assoc($pricingResult);
                                // var_dump($pricingResult);die;


                                $Tier_Type_Cd_local = $row["Tier_Type_Cd"]; // B or S
                                $Cvrg_Term_Yrs_Nbr_local = $row["Cvrg_Term_Yrs_Nbr"];
                                $Dlr_Cost_Amt = $row["Dlr_Cost_Amt"];
                                $Dlr_Mrkp_Max_Amt = $row["Dlr_Mrkp_Max_Amt"];
                                $Additional_Commission_Amt = $row["Additional_Commission"];

                                $MSRP_Amt = ($Dlr_Mrkp_Actl_Amt === null ? $Dlr_Mrkp_Max_Amt : $Dlr_Mrkp_Actl_Amt) + $Dlr_Cost_Amt + $Additional_Commission_Amt;

                                // $Tier_Type_Cd_Int_local = ($Tier_Type_Cd_local == "S") ? 1 : 2;
                                // if (mysqli_num_rows($pricingResult) > 0) {
                                //     while ($pricingRow = mysqli_fetch_assoc($pricingResult)) {

                                //         $pricingArr[$Veh_Type_Nbr][$Tier_Type_Cd_Int_local][$Cvrg_Term_Yrs_Nbr_local] = array(
                                //             "Dlr_Cost_Amt" => $Dlr_Cost_Amt_local,
                                //             "Dlr_Mrkp_Max_Amt" => $Dlr_Mrkp_Max_Amt_local,
                                //             "MSRP_Amt" => $MSRP_Amt_local
                                //         );
                                //     }
                                // }

                                // 2. Now get the needed pricing values
                                $newTermValue = $terms; // passed from earlier PHP code
                                $newCoverageValue = ($_POST['newCoverage'] ?? 'S'); // or however you're getting this input
                                $newCoverageValue = ($newCoverageValue == "S") ? 1 : 2;

                                // Handle missing values gracefully
                                // $dealerCostTemp = $pricingArr[$Veh_Type_Nbr][$newCoverageValue][$newTermValue]["Dlr_Cost_Amt"] ?? 0;
                                // $DlrMrkpMaxAmtTemp = $pricingArr[$Veh_Type_Nbr][$newCoverageValue][$newTermValue]["Dlr_Mrkp_Max_Amt"] ?? 0;
                                // $MSRPAmtTemp = $pricingArr[$Veh_Type_Nbr][$newCoverageValue][$newTermValue]["MSRP_Amt"] ?? 0;

                                // Dealer markup override
                                $dealerMarkupCost = $_POST["DlrMrkpActlAmt"] ?? 0;
                                if ($dealerMarkupCost == 0) {
                                    $dealerMarkupCost = $DlrMrkpMaxAmtTemp;
                                }
                                ?>

                                function updatePricing() {
                                    var pricingArr = [] // Dimensions: [type][tier][term][Dlr_Cost_Amt|Dlr_Mrkp_Max_Amt|MSRP_Amt
                                    <?php
                                    // Get the standard pricing for all combos to support the dynamic fields
                                    // if ($wrnty_Stat_Desc == "wholesale") {
                                    //     $pricingQuery = "SELECT * FROM Wrnty_Std_Prcg WHERE Base_Price_Table_Type_Code='Standard_Wholesale' AND
									// 						                 Veh_Type_Cd=" . $Veh_Type_Nbr;
                                    // } else {
                                    //     $pricingQuery = "SELECT * FROM Wrnty_Std_Prcg WHERE Base_Price_Table_Type_Code='Standard' AND
									// 						                 Veh_Type_Cd=" . $Veh_Type_Nbr;
                                    // }

                                    if ($wrnty_Stat_Desc == "wholesale" && $Wrap_Flg == "N") {
                                        $pricingQuery = "SELECT * FROM Wrnty_Std_Prcg 
                                                        WHERE Base_Price_Table_Type_Code = 'Standard_Wholesale' 
                                                        AND Veh_Type_Cd = " . $Veh_Type_Nbr;
                                    } else if ($Wrap_Flg == "N") {
                                        $pricingQuery = "SELECT * FROM Wrnty_Std_Prcg 
                                                        WHERE Base_Price_Table_Type_Code = 'Standard' 
                                                        AND Veh_Type_Cd = " . $Veh_Type_Nbr;
                                    } else if ($wrnty_Stat_Desc == "wholesale" && $Wrap_Flg == "Y") {
                                        $pricingQuery = "SELECT * FROM Wrnty_Std_Prcg 
                                                        WHERE Base_Price_Table_Type_Code = 'Wrap_Wholesale' 
                                                        AND Veh_Type_Cd = " . $Veh_Type_Nbr;
                                    } else if ($Wrap_Flg == "Y") {
                                        $pricingQuery = "SELECT * FROM Wrnty_Std_Prcg 
                                                        WHERE Base_Price_Table_Type_Code = 'Wrap' 
                                                        AND Veh_Type_Cd = " . $Veh_Type_Nbr;
                                    }


                                    //echo  $pricingQuery;
                                    $pricingResult = $link->query($pricingQuery);

                                    $pricingRows = mysqli_num_rows($pricingResult);
                                    if ($pricingRows > 0) {
                                        while ($pricingRow = mysqli_fetch_assoc($pricingResult)) {
                                            $Tier_Type_Cd_local = $pricingRow["Tier_Type_Cd"]; // B or S
                                            $Cvrg_Term_Yrs_Nbr_local = $pricingRow["Cvrg_Term_Yrs_Nbr"];  // 5, 7, 10
                                            $Dlr_Cost_Amt_local = $pricingRow["Dlr_Cost_Amt"];
                                            $Dlr_Mrkp_Max_Amt_local = $pricingRow["Dlr_Mrkp_Max_Amt"];
                                            $MSRP_Amt_local = $pricingRow["MSRP_Amt"];

                                            if ($Tier_Type_Cd_local == "S") {
                                                $Tier_Type_Cd_Int_local = 1;
                                            } else {
                                                $Tier_Type_Cd_Int_local = 2;
                                            }

                                            // Build a javascript array
                                    ?>
                                            // Adding data to the JavaScript array
                                            pricingArr[<?php echo $Veh_Type_Nbr; ?>] = pricingArr[<?php echo $Veh_Type_Nbr; ?>] || [];
                                            pricingArr[<?php echo $Veh_Type_Nbr; ?>][<?php echo $Tier_Type_Cd_Int_local; ?>] = pricingArr[<?php echo $Veh_Type_Nbr; ?>][<?php echo $Tier_Type_Cd_Int_local; ?>] || [];
                                            pricingArr[<?php echo $Veh_Type_Nbr; ?>][<?php echo $Tier_Type_Cd_Int_local; ?>][<?php echo $Cvrg_Term_Yrs_Nbr_local; ?>] = {
                                                "Dlr_Cost_Amt": <?php echo $Dlr_Cost_Amt_local; ?>,
                                                "Dlr_Mrkp_Max_Amt": <?php echo $Dlr_Mrkp_Max_Amt_local; ?>,
                                                "MSRP_Amt": <?php echo $MSRP_Amt_local; ?>
                                            };
                                    <?php

                                        }
                                    }

                                    ?>
                                    
                                    var newTermValue = document.getElementById("newTerm").value;
                                    var numberOfQuantity = document.getElementById("quantityDrop").value;
                                    var newCoverageValue = document.getElementById("newCoverage").value;
                                    
                                    if (newCoverageValue == "S") {
                                        newCoverageValue = 1;
                                    } else {
                                        newCoverageValue = 2;
                                    }

                                    var vehTypeNbr = <?php echo $Veh_Type_Nbr; ?>;
                                    var pricingEntry = null;
                                    if (pricingArr && pricingArr[vehTypeNbr] && pricingArr[vehTypeNbr][newCoverageValue] && pricingArr[vehTypeNbr][newCoverageValue][newTermValue]) {
                                        pricingEntry = pricingArr[vehTypeNbr][newCoverageValue][newTermValue];
                                    }

                                    // Use existing on-page values as fallbacks if pricing is missing
                                    var dealerCostTemp = pricingEntry ? pricingEntry["Dlr_Cost_Amt"] : (typeof <?php echo $Dlr_Cost_Amt?:0; ?> !== 'undefined' ? <?php echo $Dlr_Cost_Amt?:0; ?> : 0);
                                    var DlrMrkpMaxAmtTemp = pricingEntry ? pricingEntry["Dlr_Mrkp_Max_Amt"] : (typeof <?php echo $Dlr_Mrkp_Max_Amt?:0; ?> !== 'undefined' ? <?php echo $Dlr_Mrkp_Max_Amt?:0; ?> : 0);
                                    var MSRPAmtTemp = pricingEntry ? pricingEntry["MSRP_Amt"] : (typeof <?php echo $MSRP_Amt?:0; ?> !== 'undefined' ? <?php echo $MSRP_Amt?:0; ?> : 0);
                                    
                                   var phpMaxAmt = <?php echo isset($Dlr_Mrkp_Max_Amt) ? (int)$Dlr_Mrkp_Max_Amt : 0; ?>;
                                  
document.getElementById("DlrMrkpActlAmt").value =
    pricingEntry && pricingEntry["Dlr_Mrkp_Max_Amt"] !== undefined
        ? pricingEntry["Dlr_Mrkp_Max_Amt"]
        : phpMaxAmt;
                                    dealerCostField.innerHTML = "$" + (Number(dealerCostTemp) || 0).toLocaleString();
                                    DlrMrkpMaxAmt.innerHTML = "$" + (Number(DlrMrkpMaxAmtTemp) || 0).toLocaleString();
                                    MSRPAmt.innerHTML = "$" + (Number(MSRPAmtTemp) || 0).toLocaleString();
                                    msrpField.innerHTML = "$" + ((Number(MSRPAmtTemp) || 0) * (Number(numberOfQuantity) || 1)).toLocaleString();
                                    updateDealerCost();

                                }

                                function updateTextField() {
                                    var selectElement = document.getElementById("quantityDrop");
                                    if (selectElement) {
                                        var selectedValue = selectElement.value;
                                    }
                                    var msrpField = document.getElementById("msrpField");
                                    <?php if ($AEP_Flg == "Y") { ?>
                                        var aepField = document.getElementById("aepField");
                                    <?php } ?>
                                    <?php if ($APU_Flg == "Y") { ?>
                                        var apuField = document.getElementById("apuField");
                                    <?php } ?>
                                    <?php if ($Aerial_Flg == "Y") { ?>
                                        var aerField = document.getElementById("aerField");
                                    <?php } ?>
                                    <?php if ($Small_Goods_Pkg_Flg == "Y") { ?>
                                        var smallGoodsField = document.getElementById("smallGoodsField");
                                    <?php } ?>
                                    <?php if ($Old_Flg == "Y") { ?>
                                        var oldField = document.getElementById("oldField");
                                    <?php } ?>
                                    <?php if ($wearables_flag == "Y") { ?>
                                        var wearablesField = document.getElementById("wearablesField");
                                    <?php } ?>
                                    <?php if ($EVBC_Flg == "Y") { ?>
                                        var evbcField = document.getElementById("evbcField");
                                    <?php } ?>
                                    <?php if ($EEC_Flg == "Y") { ?>
                                        var eecField = document.getElementById("eecField");
                                    <?php } ?>
                                    <?php if ($ACP_Flg == "Y") { ?>
                                        var acpField = document.getElementById("acpField");
                                    <?php } ?>
                                    <?php if ($HUDS_Flg == "Y") { ?>
                                        var hudsField = document.getElementById("hudsField");
                                    <?php } ?>
                                    var totalMSRPField = document.getElementById("totalMSRPField");

                                    // Check if dropdown value is numeric
                                    if (!isNaN(parseFloat(selectedValue)) && !isNaN(selectedValue)) {
                                        <?php
                                        $totalDlrCost = $Dlr_Cost_Amt ? $Dlr_Cost_Amt : 0;
                                        $totalDlrMrkpMaxAmt = $Dlr_Mrkp_Max_Amt ? $Dlr_Mrkp_Max_Amt : 0;
                                        $totalMSRPAmt = $MSRP_Amt ? $MSRP_Amt : 0;
                                        if ($Dlr_Mrkp_Actl_Amt === 0 || $Dlr_Mrkp_Actl_Amt === '0') {
                                            $totalDlrMrkpActAmt = 0;
                                        } elseif ($Dlr_Mrkp_Actl_Amt === null || $Dlr_Mrkp_Actl_Amt === '') {
                                            $totalDlrMrkpActAmt = $Dlr_Mrkp_Max_Amt;
                                        } else {
                                             $totalDlrMrkpActAmt = $Dlr_Mrkp_Actl_Amt;
                                        }
                                       
                                        // $totalDlrMrkpActAmt = $Dlr_Mrkp_Actl_Amt=== null ? $Dlr_Mrkp_Max_Amt : $Dlr_Mrkp_Actl_Amt;

                                        // $totalDlrCost = 0;    
                                        // $totalDlrCost = 0;    
                                        // $totalDlrCost = 0;    
                                        // $totalDlrCost = 0;    

                                        // var_dump($totalMSRPAmt,$MSRP_Amt);die;

                                        ?>

                                        msrpField.innerHTML = "$" + (<?php echo $MSRP_Amt; ?> * selectedValue).toLocaleString();
                                        <?php if ($AEP_Flg == "Y") {
                                            // var_dump($totalDlrCost,$aep_Dlr_Cost_Amt);die;
                                            $totalDlrCost = (int)$totalDlrCost + (int)$aep_Dlr_Cost_Amt;
                                            $totalDlrMrkpMaxAmt = (int)$totalDlrMrkpMaxAmt + (int)$aep_Dlr_Mrkp_Max_Amt;
                                            $totalMSRPAmt = (int)$totalMSRPAmt + (int)$aep_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_AEP_Amt=== null ? (int)$aep_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_AEP_Amt);
                                            $aepResolved =
    ($Addl_Dlr_Mrkp_Actl_AEP_Amt === 0 || $Addl_Dlr_Mrkp_Actl_AEP_Amt === '0')
        ? 0
        : (($Addl_Dlr_Mrkp_Actl_AEP_Amt === null || $Addl_Dlr_Mrkp_Actl_AEP_Amt === '')
            ? $aep_Dlr_Mrkp_Max_Amt
            : $Addl_Dlr_Mrkp_Actl_AEP_Amt);
            $totalDlrMrkpActAmt += (int)$aepResolved;
                                            //$totalDlrMrkpActAmt = (int)$totalDlrMrkpActAmt + ((int)$Addl_Dlr_Mrkp_Actl_AEP_Amt=== null ? (int)$aep_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_AEP_Amt);
                                        ?>
                                            aepField.innerHTML = "$" + (<?php echo $aep_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_AEP_Amt=== null ? (int)$aep_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_AEP_Amt); ?> * selectedValue).toLocaleString();
                                        <?php } ?>
                                        <?php if ($APU_Flg == "Y") {
                                            $totalDlrCost = (int)$totalDlrCost + (int)$apu_Dlr_Cost_Amt;
                                            $totalDlrMrkpMaxAmt = (int)$totalDlrMrkpMaxAmt + (int)$apu_Dlr_Mrkp_Max_Amt;
                                            $totalMSRPAmt = (int)$totalMSRPAmt + (int)$apu_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_APU_Amt=== null ? (int)$apu_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_APU_Amt);
                                            $apuResolved =
    ($Addl_Dlr_Mrkp_Actl_APU_Amt === 0 || $Addl_Dlr_Mrkp_Actl_APU_Amt === '0')
        ? 0
        : (($Addl_Dlr_Mrkp_Actl_APU_Amt === null || $Addl_Dlr_Mrkp_Actl_APU_Amt === '')
            ? $apu_Dlr_Mrkp_Max_Amt
            : $Addl_Dlr_Mrkp_Actl_APU_Amt);
            $totalDlrMrkpActAmt += (int)$apuResolved;
                                           // $totalDlrMrkpActAmt = (int)$totalDlrMrkpActAmt + ((int)$Addl_Dlr_Mrkp_Actl_APU_Amt=== null ? (int)$apu_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_APU_Amt);
                                        ?>
                                            apuField.innerHTML = "$" + (<?php echo $apu_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_APU_Amt=== null ? (int)$apu_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_APU_Amt); ?> * selectedValue).toLocaleString();
                                        <?php } ?>
                                        <?php if ($Aerial_Flg == "Y") {
                                            $totalDlrCost = (int)$totalDlrCost + (int)$aer_Dlr_Cost_Amt;
                                            $totalDlrMrkpMaxAmt = (int)$totalDlrMrkpMaxAmt + (int)$aer_Dlr_Mrkp_Max_Amt;
                                            $totalMSRPAmt = (int)$totalMSRPAmt + (int)$aer_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_AER_Amt=== null ? (int)$aer_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_AER_Amt);
                                            $aerResolved =
    ($Addl_Dlr_Mrkp_Actl_AER_Amt === 0 || $Addl_Dlr_Mrkp_Actl_AER_Amt === '0')
        ? 0
        : (($Addl_Dlr_Mrkp_Actl_AER_Amt === null || $Addl_Dlr_Mrkp_Actl_AER_Amt === '')
            ? $aer_Dlr_Mrkp_Max_Amt
            : $Addl_Dlr_Mrkp_Actl_AER_Amt);
$totalDlrMrkpActAmt += (int)$aerResolved;
                                           // $totalDlrMrkpActAmt = (int)$totalDlrMrkpActAmt + ((int)$Addl_Dlr_Mrkp_Actl_AER_Amt=== null ? (int)$aer_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_AER_Amt);
                                        ?>
                                            aerField.innerHTML = "$" + (<?php echo $aer_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_AER_Amt=== null ? (int)$aer_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_AER_Amt); ?> * selectedValue).toLocaleString();
                                        <?php } ?>
                                        <?php if ($Small_Goods_Pkg_Flg == "Y") {
                                            $totalDlrCost = (int)$totalDlrCost + (int)$Dlr_Sml_Goods_Cst_Tot_Amt;
                                            $totalDlrMrkpMaxAmt = (int)$totalDlrMrkpMaxAmt + (int)$Dlr_Sml_Goods_Max_Mrkp_Tot_Amt;
                                            $totalMSRPAmt = (int)$totalMSRPAmt + (int)$Sml_Goods_Tot_Amt;
                                            $totalDlrMrkpActAmt = (int)$totalDlrMrkpActAmt + ($Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt=== null ? (int)$Dlr_Sml_Goods_Max_Mrkp_Tot_Amt : (int)$Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt);
                                        ?>
                                            smallGoodsField.innerHTML = "$" + (<?php echo $Sml_Goods_Tot_Amt?? 0; ?> * selectedValue).toLocaleString();
                                        <?php } ?>
                                        <?php if ($Old_Flg == "Y") {
                                            $totalDlrCost = (int)$totalDlrCost + (int)$old_MSRP_Amt;
                                            $totalDlrMrkpMaxAmt = (int)$totalDlrMrkpMaxAmt + (int)$old_Dlr_Mrkp_Max_Amt;
                                            $totalMSRPAmt = (int)$totalMSRPAmt + (int)$old_MSRP_Amt;
                                            $totalDlrMrkpActAmt = (int)$totalDlrMrkpActAmt + (int)$old_Dlr_Mrkp_Max_Amt;
                                        ?>
                                            oldField.innerHTML = "$" + (<?php echo $old_MSRP_Amt; ?> * selectedValue).toLocaleString();
                                        <?php } ?>
                                        <?php if ($wearables_flag == "Y") {
                                            $totalDlrCost = (int)$totalDlrCost + (int)$wearable_Dlr_Cost_Amt;
                                            $totalDlrMrkpMaxAmt = (int)$totalDlrMrkpMaxAmt + (int)$wearable_Dlr_Mrkp_Max_Amt;
                                            $totalMSRPAmt = (int)$totalMSRPAmt + (int)$wearable_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_WEARABLES_Amt=== null ? (int)$wearable_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_WEARABLES_Amt);
$wearablesResolved =
    ($Addl_Dlr_Mrkp_Actl_WEARABLES_Amt === 0 || $Addl_Dlr_Mrkp_Actl_WEARABLES_Amt === '0')
        ? 0
        : (($Addl_Dlr_Mrkp_Actl_WEARABLES_Amt === null || $Addl_Dlr_Mrkp_Actl_WEARABLES_Amt === '')
            ? $wearable_Dlr_Mrkp_Max_Amt
            : $Addl_Dlr_Mrkp_Actl_WEARABLES_Amt);
                                            $totalDlrMrkpActAmt += (int)$wearablesResolved;
                                            //$totalDlrMrkpActAmt = (int)$totalDlrMrkpActAmt + ((int)$Addl_Dlr_Mrkp_Actl_WEARABLES_Amt=== null ? (int)$wearable_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_WEARABLES_Amt);
                                        ?>
                                            wearablesField.innerHTML = "$" + (<?php echo $wearable_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_WEARABLES_Amt=== null ? (int)$wearable_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_WEARABLES_Amt); ?> * selectedValue).toLocaleString();
                                        <?php } ?>
                                        <?php if ($EVBC_Flg == "Y") {
                                            $totalDlrCost = (int)$totalDlrCost + (int)$evbc_Dlr_Cost_Amt;
                                            $totalDlrMrkpMaxAmt = (int)$totalDlrMrkpMaxAmt + (int)$evbc_Dlr_Mrkp_Max_Amt;
                                            $totalMSRPAmt = (int)$totalMSRPAmt + (int)$evbc_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_EVBC_Amt=== null ? (int)$evbc_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_EVBC_Amt);
                                            $evbcResolved =
    ($Addl_Dlr_Mrkp_Actl_EVBC_Amt === 0 || $Addl_Dlr_Mrkp_Actl_EVBC_Amt === '0')
        ? 0
        : (($Addl_Dlr_Mrkp_Actl_EVBC_Amt === null || $Addl_Dlr_Mrkp_Actl_EVBC_Amt === '')
            ? $evbc_Dlr_Mrkp_Max_Amt
            : $Addl_Dlr_Mrkp_Actl_EVBC_Amt);
            $totalDlrMrkpActAmt += (int)$evbcResolved;
                                            //$totalDlrMrkpActAmt = (int)$totalDlrMrkpActAmt + ((int)$Addl_Dlr_Mrkp_Actl_EVBC_Amt=== null ? (int)$evbc_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_EVBC_Amt);
                                        ?>
                                            evbcField.innerHTML = "$" + (<?php echo $evbc_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_EVBC_Amt=== null ? (int)$evbc_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_EVBC_Amt); ?> * selectedValue).toLocaleString();
                                        <?php } ?>
                                        <?php if ($EEC_Flg == "Y") {
                                            $totalDlrCost = (int)$totalDlrCost + (int)$eec_Dlr_Cost_Amt;
                                            $totalDlrMrkpMaxAmt = (int)$totalDlrMrkpMaxAmt + (int)$eec_Dlr_Mrkp_Max_Amt;
                                            $totalMSRPAmt = (int)$totalMSRPAmt + (int)$eec_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_EEC_Amt=== null ? (int)$eec_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_EEC_Amt);
                                            $eecResolved =($Addl_Dlr_Mrkp_Actl_EEC_Amt === 0 || $Addl_Dlr_Mrkp_Actl_EEC_Amt === '0')
                                            ? 0
                                            : (($Addl_Dlr_Mrkp_Actl_EEC_Amt === null || $Addl_Dlr_Mrkp_Actl_EEC_Amt === '')
                                                ? $eec_Dlr_Mrkp_Max_Amt
                                                : $Addl_Dlr_Mrkp_Actl_EEC_Amt);
                                                $totalDlrMrkpActAmt += (int)$eecResolved;
                                            //$totalDlrMrkpActAmt = (int)$totalDlrMrkpActAmt + ((int)$Addl_Dlr_Mrkp_Actl_EEC_Amt=== null ? (int)$eec_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_EEC_Amt);
                                        ?>
                                            eecField.innerHTML = "$" + (<?php echo $eec_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_EEC_Amt=== null ? (int)$eec_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_EEC_Amt); ?> * selectedValue).toLocaleString();
                                        <?php } ?>
                                        <?php if ($ACP_Flg == "Y") {
                                            $totalDlrCost = (int)$totalDlrCost + (int)$acp_Dlr_Cost_Amt;
                                            $totalDlrMrkpMaxAmt = (int)$totalDlrMrkpMaxAmt + (int)$acp_Dlr_Mrkp_Max_Amt;
                                            $totalMSRPAmt = (int)$totalMSRPAmt + (int)$acp_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_ACP_Amt=== null ? (int)$acp_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_ACP_Amt);
                                            $acpResolved =($Addl_Dlr_Mrkp_Actl_ACP_Amt === 0 || $Addl_Dlr_Mrkp_Actl_ACP_Amt === '0')
                                                ? 0
                                                : (($Addl_Dlr_Mrkp_Actl_ACP_Amt === null || $Addl_Dlr_Mrkp_Actl_ACP_Amt === '')
                                                    ? $acp_Dlr_Mrkp_Max_Amt
                                                    : $Addl_Dlr_Mrkp_Actl_ACP_Amt);
                                            $totalDlrMrkpActAmt += (int)$acpResolved;
                                           // $totalDlrMrkpActAmt = (int)$totalDlrMrkpActAmt + ((int)$Addl_Dlr_Mrkp_Actl_ACP_Amt=== null ? (int)$acp_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_ACP_Amt);
                                              
                                        ?>
                                            acpField.innerHTML = "$" + (<?php echo $acp_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_ACP_Amt=== null ? (int)$acp_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_ACP_Amt); ?> * selectedValue).toLocaleString();
                                        <?php } ?>
                                        <?php if ($HUDS_Flg == "Y") {
                                            $totalDlrCost = (int)$totalDlrCost + (int)$huds_Dlr_Cost_Amt;
                                            $totalDlrMrkpMaxAmt = (int)$totalDlrMrkpMaxAmt + (int)$huds_Dlr_Mrkp_Max_Amt;
                                            $totalMSRPAmt = (int)$totalMSRPAmt + (int)$huds_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_HUDS_Amt=== null ? (int)$huds_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_HUDS_Amt);
                                            $totalDlrMrkpActAmt = (int)$totalDlrMrkpActAmt + ($Addl_Dlr_Mrkp_Actl_HUDS_Amt=== null ? (int)$huds_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_HUDS_Amt);
                                        ?>
                                            hudsField.innerHTML = "$" + (<?php echo $huds_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_HUDS_Amt=== null ? (int)$huds_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_HUDS_Amt); ?> * selectedValue).toLocaleString();
                                        <?php } ?>
                                        <?php if ($UCP_Flg == "Y") {
                                            $totalDlrCost = (int)$totalDlrCost + (int)$ucp_Dlr_Cost_Amt;
                                            $totalDlrMrkpMaxAmt = (int)$totalDlrMrkpMaxAmt + (int)$ucp_Dlr_Mrkp_Max_Amt;
                                            $totalMSRPAmt = (int)$totalMSRPAmt + (int)$ucp_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_UCP_Amt=== null ? (int)$ucp_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_UCP_Amt);
                                            $ucpResolved =($Addl_Dlr_Mrkp_Actl_UCP_Amt === 0 || $Addl_Dlr_Mrkp_Actl_UCP_Amt === '0')
                                                ? 0
                                                : (($Addl_Dlr_Mrkp_Actl_UCP_Amt === null || $Addl_Dlr_Mrkp_Actl_UCP_Amt === '')
                                                    ? $ucp_Dlr_Mrkp_Max_Amt
                                                    : $Addl_Dlr_Mrkp_Actl_UCP_Amt);
                                            $totalDlrMrkpActAmt += (int)$ucpResolved;
                                           // $totalDlrMrkpActAmt = (int)$totalDlrMrkpActAmt + ((int)$Addl_Dlr_Mrkp_Actl_UCP_Amt=== null ? (int)$ucp_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_UCP_Amt);
                                              
                                        ?>
                                            ucpField.innerHTML = "$" + (<?php echo $ucp_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_UCP_Amt=== null ? (int)$ucp_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_UCP_Amt); ?> * selectedValue).toLocaleString();
                                        <?php } ?>
                                        totalDlrCost.innerHTML = "$" + (<?php echo $totalDlrCost; ?>).toLocaleString();
                                        totalDlrMrkpMaxAmt.innerHTML = "$" + (<?php echo $totalDlrMrkpMaxAmt; ?>).toLocaleString();
                                        totalMSRPAmt.innerHTML = "$" + (<?php echo $totalMSRPAmt; ?>).toLocaleString();
                                        totalDlrMrkpActAmt.innerHTML = "$" + (<?php echo $totalDlrMrkpActAmt; ?>).toLocaleString();
                                        totalMSRPField.innerHTML = "$" + (<?php echo $totalMSRPAmt; ?> * selectedValue).toLocaleString();
                                    }
                                }

                                updateTextField();

                                function updateDealerCost() {
                                    var totalDlrMrkpActlAmt = 0;
                                    var totalDlrMaxMrkpActlAmt = 0;
                                    var totalDealerCost = 0;
                                    var totalMSRP = 0;
                                    var totalExtendedMSRP = 0;
                                    var elements = document.getElementsByClassName("DlrMrkpActlAmt");
                                    var elementsDealerCostClass = document.getElementsByClassName("dealerCostClass");
                                    var elementsDealerMAxMArkupClass = document.getElementsByClassName("DealerMaxMarkupClass");
                                    var elementsMSRPClass = document.getElementsByClassName("MSRPClass");
                                    console.log("updateDealerCost called", totalDlrCost);
                                    let quantity = document.getElementById('quantityDrop').value;

                                    var totalDlrMrkpActAmt = document.getElementById("totalDlrMrkpActAmt");
                                    var totalDlrMrkpMaxAmt = document.getElementById("totalDlrMrkpMaxAmt");
                                    var totalMSRPAmt = document.getElementById("totalMSRPAmt");
                                    var totalExtendedMSRPAmt = document.getElementById("totalMSRPField");
                                    if (elements.length > 0) {


                                        for (var i = 0; i < elements.length; i++) {
                                            let value = parseFloat(elements[i].value);
                                            if (!isNaN(value)) {
                                                totalDlrMrkpActlAmt += value;
                                            }
                                        }
                                        if (totalDlrMrkpActAmt) {
                                            totalDlrMrkpActAmt.innerHTML = "$" + totalDlrMrkpActlAmt.toLocaleString();
                                        }
                                    }
                                    if (elementsDealerCostClass.length > 0) {
                                        for (var i = 0; i < elementsDealerCostClass.length; i++) {
                                            let value = parseFloat(elementsDealerCostClass[i].innerHTML.replace(/[$,]/g, ''));
                                            if (!isNaN(value)) {
                                                totalDealerCost += value;
                                            }
                                        }
                                        if (totalDealerCost > 0) {
                                            document.getElementById("totalDlrCost").innerHTML = "$" + totalDealerCost.toLocaleString();
                                        } else {
                                            document.getElementById("totalDlrCost").innerHTML = "$0";
                                        }

                                    }
                                    if (elementsDealerMAxMArkupClass.length > 0) {
                                        for (var i = 0; i < elementsDealerMAxMArkupClass.length; i++) {
                                            let value = parseFloat(elementsDealerMAxMArkupClass[i].innerHTML.replace(/[$,]/g, ''));
                                            if (!isNaN(value)) {
                                                totalDlrMaxMrkpActlAmt += value;
                                            }
                                        }
                                        if (totalDlrMrkpMaxAmt) {
                                            totalDlrMrkpMaxAmt.innerHTML = "$" + totalDlrMaxMrkpActlAmt.toLocaleString();
                                        }

                                    }
                                    if (elementsMSRPClass.length > 0) {
                                        for (var i = 0; i < elementsMSRPClass.length; i++) {
                                            let value = parseFloat(elementsMSRPClass[i].innerHTML.replace(/[$,]/g, ''));
                                            if (!isNaN(value)) {
                                                totalMSRP += (value);
                                            }
                                            if (!isNaN(value)) {
                                                totalExtendedMSRP += (value * parseFloat(quantity));
                                            }
                                        }
                                        if (totalMSRPAmt) {
                                            totalMSRPAmt.innerHTML = "$" + totalMSRP.toLocaleString();
                                        }
                                        if (totalExtendedMSRPAmt) {
                                            totalExtendedMSRPAmt.innerHTML = "$" + totalExtendedMSRP.toLocaleString();
                                        }

                                    }
                                }

                                function validateQuantity() {
                                    var input = document.getElementById('quantityDrop');
                                    var value = parseInt(input.value) || 1;
                                    if (value < 1) value = 1;
                                    if (value > 100) value = 100;
                                    input.value = value;
                                    var extendedMSRP = 0;
                                    var elementsExtendedMSRPClass = document.getElementsByClassName("extendedMSRPClass");
                                    var elementsMSRPClass = document.getElementsByClassName("MSRPClass");
                                    if (elementsMSRPClass.length > 0) {
                                        for (var i = 0; i < elementsMSRPClass.length; i++) {
                                            var MSRPvalue = parseFloat(elementsMSRPClass[i].innerHTML.replace(/[$,]/g, ''));
                                            elementsExtendedMSRPClass[i].innerHTML = "$" + (value * MSRPvalue).toLocaleString();
                                            if (!isNaN(value)) {
                                                extendedMSRP += (MSRPvalue * value);
                                            }

                                        }
                                        if (extendedMSRP > 0) {
                                            document.getElementById("totalMSRPField").innerHTML = "$" + (extendedMSRP).toLocaleString();
                                        } else {
                                            document.getElementById("totalMSRPField").innerHTML = "$0";
                                        }

                                    }
                                }

                                function setCoverage() {
                                    var newCoverage = document.getElementById("newCoverage").value;
                                    if (newCoverage == "S") {
                                        $('input[name="vehicleTierType"][value="S"]').prop('checked', true);
                                    } else {
                                        $('input[name="vehicleTierType"][value="B"]').prop('checked', true);
                                    }
                                }
                                function setWrapTerm() {
                                    var newWrapTerm = document.getElementById("newTerm").value;
                                    if (newWrapTerm) {
                                        $('input[name="wrapProgram2"][value="'+newWrapTerm+'"]').prop('checked', true);
                                    }
                                }

                                function setTerm() {
                                    let term = $('#newTerm').val();
                                    $('#coverageTerm').val(term).selectpicker('refresh');
                                }

                                $('#priceTableSubmit').on('click', function(e) {
                                    e.preventDefault();
                                    
                                    let fields = [
                                        "Dlr_Mrkp_Actl_Amt",
                                        "Addl_Dlr_Mrkp_Actl_APU_Amt",
                                        "Addl_Dlr_Mrkp_Actl_AEP_Amt",
                                        "Addl_Dlr_Mrkp_Actl_AER_Amt",
                                        "Addl_Dlr_Mrkp_Actl_WEARABLES_Amt",
                                        "Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt",
                                        "Addl_Dlr_Mrkp_Actl_EVBC_Amt",
                                        "Addl_Dlr_Mrkp_Actl_EEC_Amt",
                                        "Addl_Dlr_Mrkp_Actl_ACP_Amt",
                                        "Addl_Dlr_Mrkp_Actl_HUDS_Amt",
                                        "Addl_Dlr_Mrkp_Actl_UCP_Amt",
                                        "isQuote",
                                        "newCoverage",
                                        "newTerm",
                                        "quantity",
                                    ];

                                    let data = {warrantyID : '<?php if(isset($_GET['warrantyID'])) echo $_GET['warrantyID']; else echo ''; ?>',
                                        dlr_Markp_Max_Amt : document.getElementById("DlrMrkpMaxAmt").innerText.replace(/[$,]/g, ''),
                                    };

                                    fields.forEach(function(field) {
                                        let input = $(`[name="${field}"]`);
                                        if (input.length && input.val().trim() !== "") {
                                            data[field] = input.val().trim();
                                        }
                                    });
                                    console.log(data);
                                    
                                    $.ajax({
                                        url: 'price_table_save.php',
                                        type: 'POST',
                                        dataType: 'json',
                                        data: data,
                                        success: function(response) {
                                            console.log(response);
                                            if(response.error){
                                                Swal.fire({
                                                    position: 'top-center',
                                                    title: 'Update Failed',
                                                    html: "<ul style='padding: 0 0 0 38px;text-align: left;color: red;'>" + response.message + "</ul>",
                                                    showConfirmButton: true,
                                                });
                                            } else{
                                                Swal.fire({
                                                    position: 'top-center',
                                                    title: 'Update Successful',
                                                    html: "<ul style='padding: 0 0 0 38px;text-align: left;color: green;'>"+ response.message +"</ul>",
                                                    showConfirmButton: true,
                                                    // timer: 1500
                                                });
                                                fetchData() // Refresh data after successful update
                                            }
                                        },
                                        error: function() {
                                            alert('Error fetching data.');
                                        }
                                    });
                                });
                            </script>

                        </tr>
                        <?php
                        if ($nextColor == "white") {
                            $nextColor = $altColor;
                        } else {
                            $nextColor = "white";
                        }
                        ?>
                        <tr style="background-color:<?php echo $nextColor; ?>">
                            <td style="text-align: center;"><?php echo $Veh_Id_Nbr; ?></td>
                            <td style="text-align: center;"><?php echo $Veh_Type_Nbr; ?>
                                <input type="hidden" name='vehicleType' value='<?= $Veh_Type_Nbr ?>' />
                                <?php
                                if ($Wrap_Flg == "Y") {
                                ?>
                                    WRAP
                                <?php
                                }
                                ?>
                            </td>
                            <td style="text-align: center;">
                                <?php
                                if ($Wrap_Flg == "Y") {
                                ?>
                                    <select id="newTerm" name="newTerm" onchange="updatePricing(); setWrapTerm();">
                                        <option value="1" <?php if ($Cntrct_Term_Mnths_Nbr == 1) {
                                                                echo "selected";
                                                            } ?>>1</option>
                                        <option value="2" <?php if ($Cntrct_Term_Mnths_Nbr == 2) {
                                                                echo "selected";
                                                            } ?>>2</option>
                                        <option value="3" <?php if ($Cntrct_Term_Mnths_Nbr == 3) {
                                                                echo "selected";
                                                            } ?>>3</option>
                                        <option value="4" <?php if ($Cntrct_Term_Mnths_Nbr == 4) {
                                                                echo "selected";
                                                            } ?>>4</option>
                                        <option value="5" <?php if ($Cntrct_Term_Mnths_Nbr == 5) {
                                                                echo "selected";
                                                            } ?>>5</option>
                                    </select>
                                <?php
                                } else {
                                ?>
                                    <select id="newTerm" name="newTerm" onchange="updatePricing(); setTerm();">
                                        <option value="0.5" <?php if ($Cntrct_Term_Mnths_Nbr == 0.5) {
                                                                echo "selected";
                                                            } ?>>6 Months</option>
                                        <option value="1" <?php if ($Cntrct_Term_Mnths_Nbr == 1) {
                                                                echo "selected";
                                                            } ?>>1</option>
                                        <option value="2" <?php if ($Cntrct_Term_Mnths_Nbr == 2) {
                                                                echo "selected";
                                                            } ?>>2</option>
                                        <option value="3" <?php if ($Cntrct_Term_Mnths_Nbr == 3) {
                                                                echo "selected";
                                                            } ?>>3</option>
                                        <option value="4" <?php if ($Cntrct_Term_Mnths_Nbr == 4) {
                                                                echo "selected";
                                                            } ?>>4</option>
                                        <option value="5" <?php if ($Cntrct_Term_Mnths_Nbr == 5) {
                                                                echo "selected";
                                                            } ?>>5</option>
                                        <option value="6" <?php if ($Cntrct_Term_Mnths_Nbr == 6) {
                                                                echo "selected";
                                                            } ?>>6</option>
                                        <option value="7" <?php if ($Cntrct_Term_Mnths_Nbr == 7) {
                                                                echo "selected";
                                                            } ?>>7</option>
                                        <option value="8" <?php if ($Cntrct_Term_Mnths_Nbr == 8) {
                                                                echo "selected";
                                                            } ?>>8</option>
                                        <option value="9" <?php if ($Cntrct_Term_Mnths_Nbr == 9) {
                                                                echo "selected";
                                                            } ?>>9</option>
                                        <option value="10" <?php if ($Cntrct_Term_Mnths_Nbr == 10) {
                                                                echo "selected";
                                                            } ?>>10</option>
                                    </select>

                                <?php
                                }
                                ?>
                            </td>
                            <td style="text-align: left;">
                                <?php
                                if ($Wrap_Flg == "Y") {
                                ?>
                                    Battalion
                                    <input type="hidden" id="newCoverage" name="newCoverage" value="B" />
                                <?php
                                } else {
                                ?>
                                    <select id="newCoverage" name="newCoverage" onchange="updatePricing(); setCoverage();">
                                        <option value="S" <?php if ($Cntrct_Lvl_Desc == 'Squad') {
                                                                echo "selected";
                                                            } ?>>Squad</option>
                                        <option value="B" <?php if ($Cntrct_Lvl_Desc == 'Battalion') {
                                                                echo "selected";
                                                            } ?>>Battalion</option>
                                    </select>
                                    <!-- Only one element with id="newCoverage" -->

                                    <input type="hidden" id="newCoverage" name="newCoverage" value="<?php echo ($Cntrct_Lvl_Desc == 'Squad') ? 'S' : 'B'; ?>">
                                <?php
                                }
                                ?>
                            </td>
                            <td><span id="dealerCostField" class="dealerCostClass"><?php echo "$" . number_format($Dlr_Cost_Amt, 0); ?></span></td>
                            <td style="text-align:right;">
                                
                                 <?php
                                $finalMarkupValue = ($Dlr_Mrkp_Actl_Amt === 0 || $Dlr_Mrkp_Actl_Amt === '0')
                                    ? 0
                                    : (($Dlr_Mrkp_Actl_Amt === null || $Dlr_Mrkp_Actl_Amt === '')
                                        ? $Dlr_Mrkp_Max_Amt
                                        : $Dlr_Mrkp_Actl_Amt);
                                ?>

                                <input
                                    type="text"
                                    size="10"
                                    id="DlrMrkpActlAmt"
                                    class="DlrMrkpActlAmt"
                                    onchange="updateDealerCost();"
                                    style="text-align:right;"
                                    name="Dlr_Mrkp_Actl_Amt"
                                    value="<?= htmlspecialchars($finalMarkupValue, ENT_QUOTES, 'UTF-8') ?>"
                                />
                            </td>
                            <td>
                                <span id="DlrMrkpMaxAmt" class="Tier_Type_Span DealerMaxMarkupClass" style="cursor:pointer;"><?php echo "$" . number_format($Dlr_Mrkp_Max_Amt, 0); ?></span>
                            </td>
                            <td><span id="MSRPAmt" class="Tier_Type_MSRP_Amt_Span MSRPClass"><?php echo "$" . number_format($MSRP_Amt, 0); ?></span></td>
                            <td>
                                <input style="max-width: 70px;"
                                    type="text"
                                    name="quantity"
                                    id="quantityDrop"
                                    value="<?php echo isset($quantity) ? $quantity : "1"; ?>"
                                    min="1"
                                    max="100"
                                    onchange="validateQuantity();" />
                            </td>
                            <td><span id="msrpField" class="extendedMSRPClass"><?php echo "$" . number_format($quantity * $MSRP_Amt, 0); ?></span></td>
                        </tr>
                        <?php if ($AEP_Flg == "Y") {
                            if ($nextColor == "white") {
                                $nextColor = $altColor;
                            } else {
                                $nextColor = "white";
                            }

                        ?>
                            <tr style="background-color:<?php echo $nextColor; ?>">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: left;">Fire Apparatus Pump & Equipment Package</td>
                                <td class="dealerCostClass"><?php echo "$" . number_format($aep_Dlr_Cost_Amt, 0); ?></td>
                                <td>
                                    <?php
                                    $aepValue =
                                        ($Addl_Dlr_Mrkp_Actl_AEP_Amt === 0 || $Addl_Dlr_Mrkp_Actl_AEP_Amt === '0')
                                            ? 0
                                            : (($Addl_Dlr_Mrkp_Actl_AEP_Amt === null || $Addl_Dlr_Mrkp_Actl_AEP_Amt === '')
                                                ? $aep_Dlr_Mrkp_Max_Amt
                                                : $Addl_Dlr_Mrkp_Actl_AEP_Amt);
                                    ?>

                                    <input
                                        type="text"
                                        size="10"
                                        class="DlrMrkpActlAmt"
                                        onchange="updateDealerCost();"
                                        style="text-align:right;"
                                        name="Addl_Dlr_Mrkp_Actl_AEP_Amt"
                                        value="<?= htmlspecialchars($aepValue, ENT_QUOTES, 'UTF-8') ?>"
                                    />

                                </td>
                                <td class="DealerMaxMarkupClass"><?php echo "$" . number_format($aep_Dlr_Mrkp_Max_Amt, 0); ?></td>
                                <td class="MSRPClass"><?php echo "$" . number_format(($aep_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_AEP_Amt=== null ? (int)$aep_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_AEP_Amt)), 0); ?></td>
                                <td></td>
                                <td><span id="aepField" class="extendedMSRPClass"><?php echo "$" . number_format($quantity * ($aep_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_AEP_Amt=== null ? (int)$aep_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_AEP_Amt)), 0); ?></span></td>
                            </tr>
                        <?php } ?>
                        <?php if ($APU_Flg == "Y") {
                            if ($nextColor == "white") {
                                $nextColor = $altColor;
                            } else {
                                $nextColor = "white";
                            }

                        ?>
                            <tr style="background-color:<?php echo $nextColor; ?>">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: left;">APU</td>
                                <td class="dealerCostClass"><?php echo "$" . number_format($apu_Dlr_Cost_Amt, 0); ?></td>
                                <td>
                                    <?php
                                    $apuValue =
                                        ($Addl_Dlr_Mrkp_Actl_APU_Amt === 0 || $Addl_Dlr_Mrkp_Actl_APU_Amt === '0')
                                            ? 0
                                            : (($Addl_Dlr_Mrkp_Actl_APU_Amt === null || $Addl_Dlr_Mrkp_Actl_APU_Amt === '')
                                                ? $apu_Dlr_Mrkp_Max_Amt
                                                : $Addl_Dlr_Mrkp_Actl_APU_Amt);
                                    ?>

                                    <input
                                        type="text"
                                        size="10"
                                        class="DlrMrkpActlAmt"
                                        onchange="updateDealerCost();"
                                        style="text-align:right;"
                                        name="Addl_Dlr_Mrkp_Actl_APU_Amt"
                                        value="<?= htmlspecialchars($apuValue, ENT_QUOTES, 'UTF-8') ?>"
                                    />

                                </td>
                                <td class="DealerMaxMarkupClass"><?php echo "$" . number_format($apu_Dlr_Mrkp_Max_Amt, 0); ?></td>
                                <td class="MSRPClass"><?php echo "$" . number_format(($apu_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_APU_Amt=== null ? (int)$apu_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_APU_Amt)), 0); ?></td>
                                <td></td>
                                <td><span id="apuField" class="extendedMSRPClass"><?php echo "$" . number_format($quantity * ($apu_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_APU_Amt=== null ? (int)$apu_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_APU_Amt)), 0); ?></span></td>
                            </tr>
                        <?php } ?>
                        <?php if ($Aerial_Flg == "Y") {
                            if ($nextColor == "white") {
                                $nextColor = $altColor;
                            } else {
                                $nextColor = "white";
                            }

                        ?>
                            <tr style="background-color:<?php echo $nextColor; ?>">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: left;">Aerial Package</td>
                                <td class="dealerCostClass"><?php echo "$" . number_format($aer_Dlr_Cost_Amt, 0); ?></td>
                                <td>
                                   <?php
                                    $aerValue =
                                        ($Addl_Dlr_Mrkp_Actl_AER_Amt === 0 || $Addl_Dlr_Mrkp_Actl_AER_Amt === '0')
                                            ? 0
                                            : (($Addl_Dlr_Mrkp_Actl_AER_Amt === null || $Addl_Dlr_Mrkp_Actl_AER_Amt === '')
                                                ? $aer_Dlr_Mrkp_Max_Amt
                                                : $Addl_Dlr_Mrkp_Actl_AER_Amt);
                                    ?>

                                    <input
                                        type="text"
                                        size="10"
                                        class="DlrMrkpActlAmt"
                                        onchange="updateDealerCost();"
                                        style="text-align:right;"
                                        name="Addl_Dlr_Mrkp_Actl_AER_Amt"
                                        value="<?= htmlspecialchars($aerValue, ENT_QUOTES, 'UTF-8') ?>"
                                    />

                                </td>
                                <td class="DealerMaxMarkupClass"><?php echo "$" . number_format($aer_Dlr_Mrkp_Max_Amt, 0); ?></td>
                                <td class="MSRPClass"><?php echo "$" . number_format(($aer_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_AER_Amt=== null ? (int)$aer_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_AER_Amt)), 0); ?></td>
                                <td></td>
                                <td><span id="aerField" class="extendedMSRPClass"><?php echo "$" . number_format($quantity * ($aer_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_AER_Amt=== null ? (int)$aer_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_AER_Amt)), 0); ?></span></td>
                            </tr>
                        <?php } ?>
                        <?php if ($Old_Flg == "Y") {
                            if ($nextColor == "white") {
                                $nextColor = $altColor;
                            } else {
                                $nextColor = "white";
                            }

                        ?>
                            <tr style="background-color:<?php echo $nextColor; ?>">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: left;">Aged Vehicle Surcharge</td>
                                <td class="dealerCostClass"><?php echo "$" . number_format($old_Dlr_Cost_Amt, 0); ?></td>
                                <td><?php echo "$" . number_format($old_Dlr_Mrkp_Max_Amt, 0); ?></td>
                                <td class="DealerMaxMarkupClass"><?php echo "$" . number_format($old_Dlr_Mrkp_Max_Amt, 0); ?></td>
                                <td class="MSRPClass"><?php echo "$" . number_format($old_MSRP_Amt, 0); ?></td>
                                <td></td>
                                <td><span id="oldField" class="extendedMSRPClass"><?php echo "$" . number_format($quantity * $old_MSRP_Amt, 0); ?></span></td>
                            </tr>
                        <?php } ?>
                        <?php if ($Small_Goods_Pkg_Flg == "Y") {
                            if ($nextColor == "white") {
                                $nextColor = $altColor;
                            } else {
                                $nextColor = "white";
                            }

                        ?>
                            <tr style="background-color:<?php echo $nextColor; ?>">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: left;">Small Goods</td>
                                <td class="dealerCostClass"><?php echo "$" . number_format($Dlr_Sml_Goods_Cst_Tot_Amt, 0); ?></td>
                                <td>
                                    <?php
                                    $smlGoodsValue =
                                        ($Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt === 0 || $Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt === '0')
                                            ? 0
                                            : (($Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt === null || $Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt === '')
                                                ? $Dlr_Sml_Goods_Max_Mrkp_Tot_Amt
                                                : $Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt);
                                    ?>

                                    <input
                                        type="text"
                                        size="10"
                                        class="DlrMrkpActlAmt"
                                        onchange="updateDealerCost();"
                                        style="text-align:right;"
                                        name="Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt"
                                        value="<?= htmlspecialchars($smlGoodsValue, ENT_QUOTES, 'UTF-8') ?>"
                                    />
                                </td>
                                <td class="DealerMaxMarkupClass"><?php echo "$" . number_format($Dlr_Sml_Goods_Max_Mrkp_Tot_Amt, 0); ?></td>
                                <td class="MSRPClass"><?php echo "$" . number_format($Sml_Goods_Tot_Amt, 0); ?></td>
                                <td></td>
                                <td><span id="smallGoodsField" class="extendedMSRPClass"><?php echo "$" . number_format($quantity * $Sml_Goods_Tot_Amt, 0); ?></span></td>
                            </tr>
                        <?php } ?>
                        <?php if ($wearables_flag == "Y") {
                            if ($nextColor == "white") {
                                $nextColor = $altColor;
                            } else {
                                $nextColor = "white";
                            }

                        ?>
                            <tr style="background-color:<?php echo $nextColor; ?>">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: left;">Wearables Package</td>
                                <td class="dealerCostClass"><?php echo "$" . number_format($wearable_Dlr_Cost_Amt, 0); ?></td>
                                <td>
                                    <?php
                                    $wearablesValue =
                                        ($Addl_Dlr_Mrkp_Actl_WEARABLES_Amt === 0 || $Addl_Dlr_Mrkp_Actl_WEARABLES_Amt === '0')
                                            ? 0
                                            : (($Addl_Dlr_Mrkp_Actl_WEARABLES_Amt === null || $Addl_Dlr_Mrkp_Actl_WEARABLES_Amt === '')
                                                ? $wearable_Dlr_Mrkp_Max_Amt
                                                : $Addl_Dlr_Mrkp_Actl_WEARABLES_Amt);
                                    ?>

                                    <input
                                        type="text"
                                        size="10"
                                        class="DlrMrkpActlAmt"
                                        onchange="updateDealerCost();"
                                        style="text-align:right;"
                                        name="Addl_Dlr_Mrkp_Actl_WEARABLES_Amt"
                                        value="<?= htmlspecialchars($wearablesValue, ENT_QUOTES, 'UTF-8') ?>"
                                    />
                                </td>
                                <td class="DealerMaxMarkupClass"><?php echo "$" . number_format($wearable_Dlr_Mrkp_Max_Amt, 0); ?></td>
                                <td class="MSRPClass"><?php echo "$" . number_format(($wearable_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_WEARABLES_Amt=== null ? (int)$wearable_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_WEARABLES_Amt)), 0); ?></td>
                                
                                <td></td>
                                <td><span id="wearablesField" class="extendedMSRPClass"><?php echo "$" . number_format($quantity * ($wearable_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_WEARABLES_Amt=== null ? (int)$wearable_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_WEARABLES_Amt)), 0); ?></span></td>
                            </tr>
                        <?php } ?>
                        <?php if ($EVBC_Flg == "Y") {
                            if ($nextColor == "white") {
                                $nextColor = $altColor;
                            } else {
                                $nextColor = "white";
                            }

                        ?>
                            <tr style="background-color:<?php echo $nextColor; ?>">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: left;">Electric Vehicle Battery Package</td>
                                <td class="dealerCostClass"><?php echo "$" . number_format($evbc_Dlr_Cost_Amt, 0); ?></td>
                                <td>
                                    <?php
                                    $evbcValue =
                                        ($Addl_Dlr_Mrkp_Actl_EVBC_Amt === 0 || $Addl_Dlr_Mrkp_Actl_EVBC_Amt === '0')
                                            ? 0
                                            : (($Addl_Dlr_Mrkp_Actl_EVBC_Amt === null || $Addl_Dlr_Mrkp_Actl_EVBC_Amt === '')
                                                ? $evbc_Dlr_Mrkp_Max_Amt
                                                : $Addl_Dlr_Mrkp_Actl_EVBC_Amt);
                                    ?>

                                    <input
                                        type="text"
                                        size="10"
                                        class="DlrMrkpActlAmt"
                                        onchange="updateDealerCost();"
                                        style="text-align:right;"
                                        name="Addl_Dlr_Mrkp_Actl_EVBC_Amt"
                                        value="<?= htmlspecialchars($evbcValue, ENT_QUOTES, 'UTF-8') ?>"
                                    />
                                </td>
                                <td class="DealerMaxMarkupClass"><?php echo "$" . number_format($evbc_Dlr_Mrkp_Max_Amt, 0); ?></td>
                                <td class="MSRPClass"><?php echo "$" . number_format(($evbc_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_EVBC_Amt=== null ? (int)$evbc_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_EVBC_Amt)), 0); ?></td>
                                <td></td>
                                <td><span id="evbcField" class="extendedMSRPClass"><?php echo "$" . number_format($quantity * ($evbc_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_EVBC_Amt=== null ? (int)$evbc_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_EVBC_Amt)), 0); ?></span></td>
                            </tr>
                        <?php } ?>
                        <?php if ($EEC_Flg == "Y") {
                            if ($nextColor == "white") {
                                $nextColor = $altColor;
                            } else {
                                $nextColor = "white";
                            }

                        ?>
                            <tr style="background-color:<?php echo $nextColor; ?>">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: left;">Enhanced Engine Coverage</td>
                                <td class="dealerCostClass"><?php echo "$" . number_format($eec_Dlr_Cost_Amt, 0); ?></td>
                                <td>
                                   <?php
                                    $eecValue =
                                        ($Addl_Dlr_Mrkp_Actl_EEC_Amt === 0 || $Addl_Dlr_Mrkp_Actl_EEC_Amt === '0')
                                            ? 0
                                            : (($Addl_Dlr_Mrkp_Actl_EEC_Amt === null || $Addl_Dlr_Mrkp_Actl_EEC_Amt === '')
                                                ? $eec_Dlr_Mrkp_Max_Amt
                                                : $Addl_Dlr_Mrkp_Actl_EEC_Amt);
                                    ?>

                                    <input
                                        type="text"
                                        size="10"
                                        class="DlrMrkpActlAmt"
                                        onchange="updateDealerCost();"
                                        style="text-align:right;"
                                        name="Addl_Dlr_Mrkp_Actl_EEC_Amt"
                                        value="<?= htmlspecialchars($eecValue, ENT_QUOTES, 'UTF-8') ?>"
                                    />
                                </td>
                                <td class="DealerMaxMarkupClass"><?php echo "$" . number_format($eec_Dlr_Mrkp_Max_Amt, 0); ?></td>
                                <td class="MSRPClass"><?php echo "$" . number_format(($eec_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_EEC_Amt=== null ? (int)$eec_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_EEC_Amt)), 0); ?></td>
                                <td></td>
                                <td><span id="eecField" class="extendedMSRPClass"><?php echo "$" . number_format($quantity * ($eec_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_EEC_Amt=== null ? (int)$eec_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_EEC_Amt)), 0); ?></span></td>
                            </tr>
                        <?php } ?>
                        <?php if ($ACP_Flg == "Y") {
                            if ($nextColor == "white") {
                                $nextColor = $altColor;
                            } else {
                                $nextColor = "white";
                            }

                        ?>
                            <tr style="background-color:<?php echo $nextColor; ?>">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: left;">Ambulance Conversion Package</td>
                                <td class="dealerCostClass"><?php echo "$" . number_format($acp_Dlr_Cost_Amt, 0); ?></td>
                                <td>
                                   <?php
                                    $acpValue =
                                        ($Addl_Dlr_Mrkp_Actl_ACP_Amt === 0 || $Addl_Dlr_Mrkp_Actl_ACP_Amt === '0')
                                            ? 0
                                            : (($Addl_Dlr_Mrkp_Actl_ACP_Amt === null || $Addl_Dlr_Mrkp_Actl_ACP_Amt === '')
                                                ? $acp_Dlr_Mrkp_Max_Amt
                                                : $Addl_Dlr_Mrkp_Actl_ACP_Amt);
                                    ?>

                                    <input
                                        type="text"
                                        size="10"
                                        class="DlrMrkpActlAmt"
                                        onchange="updateDealerCost();"
                                        style="text-align:right;"
                                        name="Addl_Dlr_Mrkp_Actl_ACP_Amt"
                                        value="<?= htmlspecialchars($acpValue, ENT_QUOTES, 'UTF-8') ?>"
                                    />
                                </td>
                                <td class="DealerMaxMarkupClass"><?php echo "$" . number_format($acp_Dlr_Mrkp_Max_Amt, 0); ?></td>
                                <td class="MSRPClass"><?php echo "$" . number_format(($acp_Dlr_Cost_Amt + ( $Addl_Dlr_Mrkp_Actl_ACP_Amt=== null ? (int)$acp_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_ACP_Amt)), 0); ?></td>
                                <td></td>
                                <td><span id="acpField" class="extendedMSRPClass"><?php echo "$" . number_format($quantity * ($acp_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_ACP_Amt=== null ? (int)$acp_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_ACP_Amt)), 0); ?></span></td>
                            </tr>
                        <?php } ?>
                        <?php if ($HUDS_Flg == "Y") {
                            if ($nextColor == "white") {
                                $nextColor = $altColor;
                            } else {
                                $nextColor = "white";
                            }

                        ?>
                            <tr style="background-color:<?php echo $nextColor; ?>">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: left;">High Use Dept Surcharge</td>
                                <td class="dealerCostClass"><?php echo "$" . number_format($huds_Dlr_Cost_Amt, 0); ?></td>
                                <td>
                                   
                                    <?php
                                    
$hudsValue =($Addl_Dlr_Mrkp_Actl_HUDS_Amt === 0 || $Addl_Dlr_Mrkp_Actl_HUDS_Amt === '0')
        ? 0
        : (($Addl_Dlr_Mrkp_Actl_HUDS_Amt === null || $Addl_Dlr_Mrkp_Actl_HUDS_Amt === '')
            ? $huds_Dlr_Mrkp_Max_Amt
            : $Addl_Dlr_Mrkp_Actl_HUDS_Amt);
?>
<?php if ($_SESSION['role_ID'] == 1) { ?>
    <input
        type="text"
        size="10"
        class="DlrMrkpActlAmt"
        onchange="updateDealerCost();"
        style="text-align:right;"
        name="Addl_Dlr_Mrkp_Actl_HUDS_Amt"
        value="<?= htmlspecialchars($hudsValue, ENT_QUOTES, 'UTF-8') ?>"
    />
<?php } else { ?>
  <?= "$" . number_format($hudsValue, 0); ?>

    <input
        type="hidden"
        class="DlrMrkpActlAmt"
        name="Addl_Dlr_Mrkp_Actl_HUDS_Amt"
        value="<?= htmlspecialchars($hudsValue, ENT_QUOTES, 'UTF-8') ?>"
    />
<?php } ?>

                                </td>
                                <td class="DealerMaxMarkupClass"><?php echo "$" . number_format($huds_Dlr_Mrkp_Max_Amt, 0); ?></td>
                                <td class="MSRPClass"><?php echo "$" . number_format(($huds_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_HUDS_Amt=== null ? 0 : (int)$Addl_Dlr_Mrkp_Actl_HUDS_Amt)), 0); ?></td>
                                <td></td>
                                <td><span id="hudsField" class="extendedMSRPClass"><?php echo "$" . number_format($quantity * ($huds_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_HUDS_Amt=== null ? 0 : (int)$Addl_Dlr_Mrkp_Actl_HUDS_Amt)), 0); ?></span></td>
                            </tr>
                        <?php } ?>

                        <?php if ($UCP_Flg == "Y") {
                            if ($nextColor == "white") {
                                $nextColor = $altColor;
                            } else {
                                $nextColor = "white";
                            }

                        ?>
                            <tr style="background-color:<?php echo $nextColor; ?>">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: left;">Upfitter Conversion Package</td>
                                <td class="dealerCostClass"><?php echo "$" . number_format($ucp_Dlr_Cost_Amt, 0); ?></td>
                                <td>
                                   <?php
                                    $ucpValue =
                                        ($Addl_Dlr_Mrkp_Actl_UCP_Amt === 0 || $Addl_Dlr_Mrkp_Actl_UCP_Amt === '0')
                                            ? 0
                                            : (($Addl_Dlr_Mrkp_Actl_UCP_Amt === null || $Addl_Dlr_Mrkp_Actl_UCP_Amt === '')
                                                ? $ucp_Dlr_Mrkp_Max_Amt
                                                : $Addl_Dlr_Mrkp_Actl_UCP_Amt);
                                    ?>

                                    <input
                                        type="text"
                                        size="10"
                                        class="DlrMrkpActlAmt"
                                        onchange="updateDealerCost();"
                                        style="text-align:right;"
                                        name="Addl_Dlr_Mrkp_Actl_UCP_Amt"
                                        value="<?= htmlspecialchars($ucpValue, ENT_QUOTES, 'UTF-8') ?>"
                                    />
                                </td>
                                <td class="DealerMaxMarkupClass"><?php echo "$" . number_format($ucp_Dlr_Mrkp_Max_Amt, 0); ?></td>
                                <td class="MSRPClass"><?php echo "$" . number_format(($ucp_Dlr_Cost_Amt + ( $Addl_Dlr_Mrkp_Actl_UCP_Amt=== null ? (int)$ucp_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_UCP_Amt)), 0); ?></td>
                                <td></td>
                                <td><span id="ucpField" class="extendedMSRPClass"><?php echo "$" . number_format($quantity * ($ucp_Dlr_Cost_Amt + ($Addl_Dlr_Mrkp_Actl_UCP_Amt=== null ? (int)$ucp_Dlr_Mrkp_Max_Amt : (int)$Addl_Dlr_Mrkp_Actl_UCP_Amt)), 0); ?></span></td>
                            </tr>
                        <?php } ?>
                        <tr style="background-color:#201F58;color:#FFFFFF; font-weight:bold;">
                            <td>Total Cost</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><span id="totalDlrCost"><?php echo "$" . number_format($Tot_Dlr_Cost_Amt, 0); ?></span></td>
                            <td>
                                <span id="totalDlrMrkpActAmt"><?php echo "$" . number_format($Tot_Dlr_Mrkp_Act_Amt, 0); ?></span>
                            </td>
                            <td>
                                <span id="totalDlrMrkpMaxAmt"><?php echo "$" . number_format($Tot_Dlr_Mrkp_Max_Amt, 0); ?></span>
                            </td>
                            <td><span id="totalMSRPAmt" class="Tot_MSRP_Amt_Span"><?php echo "$" . number_format($Tot_MSRP_Amt, 0); ?></span></td>
                            <td></td>
                            <td><span id="totalMSRPField" class="Tot_MSRP_Amt_Span"><?php echo "$" . number_format($quantity * $Tot_MSRP_Amt, 0); ?></span></td>
                        </tr>
                    </table>
                    <br /><Br />

                    <?php
                    // If admin, show the wholesale price toggle
                    if ($role_ID == 1 && $warrantyID != "") {
                        if ($wrnty_Stat_Desc == "wholesale") {
                    ?>
                            <span style="color:red;">Option: <a href="pricing_table.php?pageAction=priceToggle&toggleType=standard&warrantyID=<?php echo encryptData($warrantyID); ?>&isQuote=<?php echo $isQuote; ?>"><b>Use Standard Pricing</b></a></span>
                            <br /><Br />
                        <?php
                        } else {
                        ?>
                            <div style="color:red;">Option: <a href="pricing_table.php?pageAction=priceToggle&toggleType=wholesale&warrantyID=<?php echo encryptData($warrantyID);; ?>&isQuote=<?php echo $isQuote; ?>"><b>Use Wholesale Pricing</b></a></div>
                            <br /><Br />
                    <?php

                        }
                    }

                    ?>

                    <?php
                    // If we have small goods, display the 'total limit of liability'
                    if ($Small_Goods_Pkg_Flg === "Y") {

                        $totalLiabilitySum = 0;

                        $query = "
                            SELECT 
                                SUM(sggp.Gnrc_Lmt_Of_Lblty_Amt * sgc.Gnrc_Item_Cat_Qty_Cnt) AS liability_sum
                            FROM Sml_Goods_Cvge sgc
                            INNER JOIN Sml_Goods_Gnrc_Prcg sggp
                                ON sgc.Sml_Goods_Gnrc_Prcg_ID = sggp.Sml_Goods_Gnrc_Prcg_ID
                            WHERE sgc.Cntrct_ID = ?
                            AND sgc.Is_Deleted_Flg <> 'Y'
                        ";

                        if ($stmt = $link->prepare($query)) {
                            $stmt->bind_param("i", $warrantyID);
                            $stmt->execute();
                            $stmt->bind_result($liabilitySum);
                            $stmt->fetch();
                            $stmt->close();

                            // Handle NULL from SUM()
                            $totalLiabilitySum = $liabilitySum ?? 0;
                        }
                    ?>
                        <p>
                            <b>
                                Small Goods Limit of Liability:
                                $<?php echo number_format($totalLiabilitySum, 0); ?>
                            </b>
                        </p>
                    <?php
                    }
                    ?>
                    <?php
                    if($warrantyID != "") {
                    ?>
                    <button type="button" style="align:right;" id="priceTableSubmit" class="btn btn-primary">Update</button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>