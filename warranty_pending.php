<?php
//
// File: warranty_pending.php (v4 testing)
// Author: Charles Parry
// Date: 5/20/2022
//
//

// Turn on error reporting
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
//error_reporting(E_ALL);


$pageBreadcrumb = "Warranties Pending";
$pageTitle = "Warranties Pending";


// Connect to DB
require_once "includes/dbConnect.php";

/**For encryption of the data */
require_once 'encrypt.php';

// DB Library
require_once "lib/dblib.php";

require_once 'vendor/autoload.php';

use Classes\GeneratePDF;


// Variables.
$dealerID = "";
$agreementDate = "";
$customerName = "";
$customerEmail = "";
$customerAddress = "";
$customerCity = "";
$customerState = "";
$customerZip = "";
$customerPhone = "";
$customerSalesChannel = "";
$showQuotes = "N";
$edit_action = "";
$form_err    = "";
$Acct_ID = "";  // For location
$dealerAgentID = ""; // For Dealer sales agent
$warrantyID = "";
$warrantyStatus = "";
$agreementDate = "";
$customerEmail = "";
$Vehicle_Manufacturer_Name = "";
$Vehicle_Gross_Weight = "";
$Vehicle_Type = "";
$Vehicle_Vin_Number = "";
$Vehicle_Year = "";
$Vehicle_Make = "";
$Vehicle_Model = "";
$Engine_Make = "";
$Engine_Model = "";
$Engine_Serial = "";
$Engine_Hours = "";
$Transmission_Make = "";
$Transmission_Model = "";
$Transmission_Serial = "";
$Odometer_Reading = "";
$Odometer_Miles_Or_KM = "";
$ECM_Reading = "";
$ECM_Miles_Or_KM = "";
$APU_Flg = "";
$APU_Engine_Make = "";
$APU_Engine_Model = "";
$APU_Engine_Year = "";
$APU_Engine_Serial = "";
$APU_Hours = "";
$Vehicle_New_Flag = "";
$Vehicle_Description = "";
$Tier_Type = "";
$Apparatus_Equipment_Package = "";
$Aerial_Package = "";
$Coverage_Term = "";
$Small_Goods_Package = "";
$Srvc_Veh_Flg = ""; // Customer services own fleet of vehicles
$Supply_Packet_To_Be_Shipped = "";
$Supply_Packet_Left = "";
$Supply_Packet_Shipped_Date = "";
$Lien_Holder_Name = "";
$Lien_Holder_Email = "";
$Lien_Holder_Address = "";
$Lien_Holder_City = "";
$Lien_Holder_State_Province = "";
$Lien_Holder_Postal_Code = "";
$Lien_Holder_Phone_Number = "";
$Dealer_Signature = "";
$Dealer_Signature_Name = "";
$Dealer_Signature_Date = "";
$Customer_Signature = "";
$Customer_Signature_Name = "";
$Customer_Signature_Date = "";
$customerPO = "";
$dealerARNumber = "";
$smallGoodsPackage = "";
$ECM_Reading_Km = "";
$Odometer_Reading_Km = "";

$pers_ID = 0;


if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


// Make sure a dealer is currently logged in, or go back to the Agreement
if(!(isset($_SESSION["userType"])) || !($_SESSION["userType"] == "dealer" || $_SESSION["userType"] == "Agent")){
    header("location: index.php");
    exit;
}


// Get a dealer ID from session.
if(!(isset($_SESSION["id"]))){
    header("location: index.php");
    exit;
}else{
	$dealerID = $_SESSION["id"];
	$adminID = $_SESSION["admin_id"];
}

if(isset($_GET["showQuotes"])){
	$showQuotes = $_GET["showQuotes"];
}


// Process form data when form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	// Get form fields
	if (!empty(trim($_POST["warrantyID"]))) {
		if(is_numeric($_POST["warrantyID"])){
			$warrantyID = trim($_POST["warrantyID"]);
			$encryptedId = encryptData($warrantyID);
		}else{
			if($showQuotes=="Y"){
				header("location: warranty_pending.php?showQuotes=Y");
				exit;
			}else{
				header("location: warranty_pending.php");
				exit;
			}
		}
	}


	if (!empty(trim($_POST["edit_action"]))) {
		$edit_action = trim($_POST["edit_action"]);
	}

	if (!empty(trim($_POST["showQuotes"]))) {
		$showQuotes = trim($_POST["showQuotes"]);
	}


	// Dispatch on the 'edit action' parameter
	if($edit_action=="Edit"){
		if($showQuotes=="Y"){
			header("location: create_warranty.php?isQuote=Y&warrantyID=".urlencode($encryptedId));
			exit;
		}else{
			header("location: create_warranty.php?warrantyID=".urlencode($encryptedId));
			exit;
		}

	}elseif($edit_action=="PriceSummary"){
		if($showQuotes=="Y"){
			header("location: warranty_summary.php?isQuote=Y&warrantyID=".urlencode($encryptedId));
		}else{
			header("location: warranty_summary.php?warrantyID=".urlencode($encryptedId));
			exit;
		}

	}elseif($edit_action=="UploadFiles"){
		if($showQuotes=="Y"){
			header("location: warranty_uploads.php?isQuote=Y&warrantyID=".$warrantyID);
		}else{
			header("location: warranty_uploads.php?warrantyID=".$warrantyID);
			exit;
		}

	}elseif($edit_action=="Print"){

		if($showQuotes=="Y"){
			header("location: warranty_print.php?isQuote=Y&warrantyID=". urlencode($encryptedId));
		}else{
			header("location: warranty_print.php?warrantyID=".urlencode($encryptedId));
			exit;
		}
		exit;

		// Get the PDF name.
		if($showQuotes=="Y"){
			$query = "SELECT * FROM File_Assets WHERE Acct_ID=" . $dealerID . " AND Dealer_Cntrct_ID=".$warrantyID."
					  AND File_Asset_Type_ID=6 ORDER BY createdDate DESC;";
		}else{
			$query = "SELECT * FROM File_Assets WHERE Acct_ID=" . $dealerID . " AND Dealer_Cntrct_ID=".$warrantyID."
					  AND File_Asset_Type_ID=7 ORDER BY createdDate DESC;";
		}
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$pdfPath = $row["Path_to_File"];
		header("location: ".$pdfPath);
		exit;

	}elseif($edit_action=="Delete"){
		// Get the Contract DIM ID.
		$query = "SELECT Cntrct_Dim_ID FROM Cntrct WHERE Cntrct_ID=".$warrantyID.";";
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$Cntrct_Dim_ID = $row["Cntrct_Dim_ID"];

		// Set the logical delted flag
		$query = "UPDATE Cntrct_Dim SET 	Is_Deleted_Flg='Y' WHERE Cntrct_Dim_ID=".$Cntrct_Dim_ID.";";
		$result = $link->query($query);


		if($showQuotes=="Y"){
			header("location: warranty_pending.php?showQuotes=Y");
			exit;
		}else{
			header("location: warranty_pending.php");
			exit;
		}

	}elseif($edit_action=="createWarranty"){

		// Copy the three tables to new versions, and link back to the quote

		// Look up the Cntrct_Dim_ID and Veh_ID from the Cntrct table
		$query = "SELECT * FROM Cntrct WHERE Cntrct_ID=" . $warrantyID.";";
		$result = $link->query($query);
		$row = $result->fetch_assoc();


		$Veh_ID = $row["Veh_ID"];
		$Cntrct_Dim_ID = $row["Cntrct_Dim_ID"];

		$query = "SELECT * FROM Cntrct_Dim WHERE Cntrct_Dim_ID =" . $Cntrct_Dim_ID.";";
		$result = $link->query($query);
		$row = $result->fetch_assoc();
		$is_Wrap= $row["Wrap_Flg"];

		/* Prepare a copy statement to create a new Cntrct_Dim entry for a warranty draft from this quote */
		// Note that we are setting the Cntrct_Dim_ID of the Quote to be the Prnt_Cntrct_Dim_ID of the new Warranty entry.
		$stmt = mysqli_prepare($link, "INSERT INTO `Cntrct_Dim`
		                               (Prnt_Cntrct_Dim_ID,Cntrct_type_cd,Cntrct_type_desc,Qte_Flg,Cstmr_Nme,
									    Contract_Date,Sply_Pkt_To_Be_Shipd_Flg,Sply_Pkt_Left_Flg,Cntrct_Lvl_Cd,Cntrct_Lvl_Desc,
									    AEP_Flg,wearables_flag,Aerial_Flg,APU_Flg,EVBC_Flg,EEC_Flg,Small_Goods_Pkg_Flg,Wrap_Flg,Cntrct_Term_Mnths_Nbr,
									    Cstmr_Eml,Cstmr_Addrs,Cstmr_Cty,Cstmr_Ste,Cstmr_Pstl,Cstmr_Phn,
									    Lien_Nme,Lien_Eml,Lien_Addrs,Lien_Cty,Lien_Ste,Lien_Pstl,Lien_Phn,
									    Created_Date)
		                               SELECT
		                                Cntrct_Dim_ID,'WD','Warranty Draft','N',Cstmr_Nme,
									    Contract_Date,Sply_Pkt_To_Be_Shipd_Flg,Sply_Pkt_Left_Flg,Cntrct_Lvl_Cd,Cntrct_Lvl_Desc,
									    AEP_Flg,wearables_flag,Aerial_Flg,APU_Flg,EVBC_Flg,EEC_Flg,Small_Goods_Pkg_Flg,Wrap_Flg,Cntrct_Term_Mnths_Nbr,
									    Cstmr_Eml,Cstmr_Addrs,Cstmr_Cty,Cstmr_Ste,Cstmr_Pstl,Cstmr_Phn,
									    Lien_Nme,Lien_Eml,Lien_Addrs,Lien_Cty,Lien_Ste,Lien_Pstl,Lien_Phn,
									    NOW()
		                               FROM `Cntrct_Dim` WHERE Cntrct_Dim_ID=".$Cntrct_Dim_ID);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the Contract_Dim_ID of the new contract dim entry.
		$new_contract_dim_ID = mysqli_insert_id($link);


		/* Prepare a copy statement to create a new Veh entry for a warranty draft from this quote */
		$stmt = mysqli_prepare($link, "INSERT INTO `Veh`
		                               (Veh_Mk_Cd,Veh_Model_Cd,Veh_Model_Yr_Cd,
									    Veh_Eng_Mk_CD,veh_Eng_Model_Cd,Veh_Eng_Ser_Nbr,
									    Veh_Gross_Wgt_Cnt,Veh_Type_Nbr,Veh_New_Flg,
									    Veh_Trnsmsn_Ser_nbr,Veh_Trnsmsn_Mk_Cd,Veh_Trnsmsn_Model_Cd,
									    Veh_APU_Eng_Ser_nbr,Veh_APU_Eng_Mk_Cd,Veh_APU_Eng_Model_Cd,Veh_APU_Eng_Yr_Cd,Veh_Eng_Hours,
									    OdoMtr_Read_Miles_Cnt,OdoMtr_Read_Kms_Cnt,ECM_Read_Miles_Cnt,ECM_Read_Kms_Cnt,Veh_Desc,
									    Veh_Id_Nbr)
		                               SELECT
		                                Veh_Mk_Cd,Veh_Model_Cd,Veh_Model_Yr_Cd,
										Veh_Eng_Mk_CD,veh_Eng_Model_Cd,Veh_Eng_Ser_Nbr,
										Veh_Gross_Wgt_Cnt,Veh_Type_Nbr,Veh_New_Flg,
										Veh_Trnsmsn_Ser_nbr,Veh_Trnsmsn_Mk_Cd,Veh_Trnsmsn_Model_Cd,
										Veh_APU_Eng_Ser_nbr,Veh_APU_Eng_Mk_Cd,Veh_APU_Eng_Model_Cd,Veh_APU_Eng_Yr_Cd,Veh_Eng_Hours,
										OdoMtr_Read_Miles_Cnt,OdoMtr_Read_Kms_Cnt,ECM_Read_Miles_Cnt,ECM_Read_Kms_Cnt,Veh_Desc,
									    Veh_Id_Nbr
		                               FROM `Veh` WHERE Veh_ID=".$Veh_ID);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the Veh_ID of the new Veh entry.
		$new_veh_ID = mysqli_insert_id($link);

		$Addl_Dlr_Mrkp_Actl_APU_Amt = 0;
		$Addl_Dlr_Mrkp_Actl_AEP_Amt = 0;
		$Addl_Dlr_Mrkp_Actl_AER_Amt = 0;

		/* Prepare a copy statement to create a new Cntrct entry for a warranty draft from this quote */
		$stmt = mysqli_prepare($link, "INSERT INTO `Cntrct`
		                               (Cntrct_Dim_ID,Veh_ID,Cntrct_Nbr,Cntrct_Sales_Chnl,Sply_Pkt_Shipd_Dte,
									   Mfr_Acct_ID,Dlr_Agt_Prsn_ID,Dlr_Cost_Amt,Sales_Agt_Cost_Amt,Sales_Agt_Commission_Amt,
									   Dlr_Mrkp_Max_Amt,Dlr_Mrkp_Actl_Amt,MSRP_Amt,
									   Sales_Agt_Sml_Goods_Cst_Amt,Sales_Agt_Sml_Goods_Commission_Tot_Amt,
									   Dlr_Sml_Goods_Cst_Tot_Amt,Dlr_Sml_Goods_Max_Mrkp_Tot_Amt,
									   Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt,Sml_Goods_Tot_Amt,
									   Addl_Sales_Agt_Cost_Amt,Addl_Sales_Agt_Commission_Amt,Addl_Dlr_Cost_Amt,
									   Addl_Dlr_Mrkp_Max_Amt,Addl_Dlr_Mrkp_Actl_Amt,Addl_MSRP_Amt,
									   Tot_Sales_Agt_Cost_Amt,Tot_Sales_Agt_Commission_Amt,Tot_Dlr_Cost_Amt,
									   Tot_Dlr_Mrkp_Max_Amt,Tot_Dlr_Mrkp_Act_Amt,Tot_MSRP_Amt,
									   Addl_Dlr_Mrkp_Actl_APU_Amt,Addl_Dlr_Mrkp_Actl_AEP_Amt,
									   Addl_Dlr_Mrkp_Actl_AER_Amt,Addl_Dlr_Mrkp_Actl_EVBC_Amt,Addl_Dlr_Mrkp_Actl_EEC_Amt,Quantity,Qte_Dt,Created_Date)
		                               SELECT
		                                ".$new_contract_dim_ID.",".$new_veh_ID.",Cntrct_Nbr,Cntrct_Sales_Chnl,Sply_Pkt_Shipd_Dte,
									   Mfr_Acct_ID,Dlr_Agt_Prsn_ID,Dlr_Cost_Amt,Sales_Agt_Cost_Amt,Sales_Agt_Commission_Amt,
									   Dlr_Mrkp_Max_Amt,Dlr_Mrkp_Actl_Amt,MSRP_Amt,
									   Sales_Agt_Sml_Goods_Cst_Amt,Sales_Agt_Sml_Goods_Commission_Tot_Amt,
									   Dlr_Sml_Goods_Cst_Tot_Amt,Dlr_Sml_Goods_Max_Mrkp_Tot_Amt,
									   Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt,Sml_Goods_Tot_Amt,
									   Addl_Sales_Agt_Cost_Amt,Addl_Sales_Agt_Commission_Amt,Addl_Dlr_Cost_Amt,
									   Addl_Dlr_Mrkp_Max_Amt,Addl_Dlr_Mrkp_Actl_Amt,Addl_MSRP_Amt,
									   Tot_Sales_Agt_Cost_Amt,Tot_Sales_Agt_Commission_Amt,Tot_Dlr_Cost_Amt,
									   Tot_Dlr_Mrkp_Max_Amt,Tot_Dlr_Mrkp_Act_Amt,Tot_MSRP_Amt,
									   Addl_Dlr_Mrkp_Actl_APU_Amt,Addl_Dlr_Mrkp_Actl_AEP_Amt,
									   Addl_Dlr_Mrkp_Actl_AER_Amt,Addl_Dlr_Mrkp_Actl_EVBC_Amt,Addl_Dlr_Mrkp_Actl_EEC_Amt,Quantity,Qte_Dt,Created_Date
		                               FROM `Cntrct` WHERE Cntrct_ID=".$warrantyID);
		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the Cntrct_ID of the new contract entry.
		$new_Cntrct_ID = mysqli_insert_id($link);

		// Update the quote record to point to the ID of the warranty that was created.
		$query = "UPDATE Cntrct SET Created_Warranty_ID=".$new_Cntrct_ID." WHERE Cntrct_ID=".$warrantyID.";";
		$result = $link->query($query);


       //Rewrite warranty PDF

	   	// Get the Acct_ID
		$query = "SELECT * FROM Cntrct WHERE Cntrct_ID=" . $new_Cntrct_ID. ";";
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$Acct_ID = $row["Mfr_Acct_ID"];
		$dealerID = $Acct_ID;

		// Get the dealer info
		$query = "SELECT * FROM Acct WHERE Acct_ID=" . $dealerID . ";";
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$dealerName = $row["Acct_Nm"];

		// If this is a sub-location, will need to get the proper contact person
		//  from the usr_loc table.  Also works for parent location.
		$query_pers = "SELECT * FROM Usr_Loc WHERE Dlr_Acct_ID=" . $dealerID . ";";
		$result_pers = $link->query($query_pers);
		$row_pers = $result_pers->fetch_assoc();

		$pers_ID = $row_pers["Pers_ID"];


		// Dealer Email Query
		$dealerEmailQuery = "SELECT * FROM Email WHERE Pers_ID=".$pers_ID." AND
		Email_Prim_Flg='Y' AND Email_Type_Cd='Work'";
		$dealerEmailResult = $link->query($dealerEmailQuery);
		if (mysqli_num_rows($dealerEmailResult) > 0) {
			$row = $dealerEmailResult->fetch_assoc();
			$dealerEmail = $row["Email_URL_Desc"];
		}else{
			$dealerEmail = "";
		}

		// Get the dealer address info
		$query = "SELECT * FROM Addr WHERE Acct_ID=" . $dealerID . " AND Addr_Type_Cd='Work';";
		$result = $link->query($query);
		if (mysqli_num_rows($result) > 0) {
			$row = $result->fetch_assoc();

			$dealerAddress1 = $row["St_Addr_1_Desc"];
			$dealerAddress2 = $row["St_Addr_2_Desc"];
			$dealerCity = $row["City_Nm"];
			$dealerState = $row["St_Prov_ID"];
			$dealerZip = $row["Pstl_Cd"];

			$customerStateResult = selectState($link,$dealerState);
		}else{
			$dealerAddress1 = "";
			$dealerAddress2 = "";
			$dealerCity = "";
			$dealerState = "";
			$dealerZip = "";

			$customerStateResult = "";
		}


		// Get AR Number
		$arQuery = "SELECT * FROM `Cntrct` c, Cntrct_Dim cd WHERE c.`Mfr_Acct_ID`=".$dealerID." AND
					cd.Cntrct_Type_Desc is NULL AND
					c.`Cntrct_Dim_ID`=cd.`Cntrct_Dim_ID`;";
		$arResult = $link->query($arQuery);
		if (mysqli_num_rows($arResult) > 0) {
			$arRow = mysqli_fetch_assoc($arResult);
			$dealerARNumber = $arRow["Assign_Rtlr_Nbr"];
		}

		// Get the dealer phone
		$phoneResult = selectTelByAcct($link, $dealerID, "Y", "Work");
		$row = $phoneResult->fetch_assoc();
		$dealerPhone = $row["Tel_Nbr"];

		// Get warranty detail

		$query =  "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Mfr_Acct_ID=".$dealerID." AND c.Cntrct_ID =".$new_Cntrct_ID." AND c.Created_Warranty_ID is NULL AND c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND cd.Cntrct_Type_Cd='WD' AND cd.Is_Deleted_Flg != 'Y' AND c.Veh_ID = v.Veh_ID";
		$result = $link->query($query);
		$warranty = $result->fetch_assoc();

		$agreementDate = $warranty["Created_Date"];

		//customer
		$customerName =  $warranty["Cstmr_Nme"];
		$customerEmail =  $warranty["Cstmr_Eml"];
		$customerPhone =  $warranty["Cstmr_Phn"];
		$customerAddress =  $warranty["Cstmr_Addrs"];
		$customerCity =  $warranty["Cstmr_Cty"];
		$customerState =  $warranty["Cstmr_Ste"];
		$customerZip =  $warranty["Cstmr_Pstl"];
		//Vehicle
		$Vehicle_Gross_Weight = $warranty["Veh_Gross_Wgt_Cnt"];
		$vehGrossWeight = $warranty["Veh_Gross_Wgt_Cnt"];
		$Vehicle_Vin_Number = $warranty["Veh_Id_Nbr"];
		$Vehicle_Make = $warranty["Veh_Mk_Cd"];
		$Vehicle_Model = $warranty["Veh_Model_Cd"];
		$Vehicle_Year = $warranty["Veh_Model_Yr_Cd"];
		$Veh_Type_Nbr = $warranty["Veh_Type_Nbr"];

		//Engine
		$Engine_Make = $warranty["Veh_Eng_Mk_Cd"];
		$Engine_Model = $warranty["Veh_Eng_Model_Cd"];
		$Engine_Year = $warranty["Veh_Eng_Ser_nbr"];
		$Engine_Serial = $warranty["Veh_Eng_Ser_nbr"];
		//Transmission
		$Transmission_Make = $warranty["Veh_Trnsmsn_Mk_Cd"];
		$Transmission_Model = $warranty["Veh_Trnsmsn_Model_Cd"];
		$Transmission_Serial = $warranty["Veh_Trnsmsn_Ser_nbr"];

		$Odometer_Reading_Miles = $warranty["OdoMtr_Read_Miles_Cnt"];
		$Odometer_Reading_Km = $warranty["OdoMtr_Read_Kms_Cnt"];

		if ($Odometer_Reading_Miles != 0) {
			$Odometer_Miles_Or_KM = "Miles";
			$Odometer_Reading = $Odometer_Reading_Miles;
		} else {
			$Odometer_Miles_Or_KM = "KM";
			$Odometer_Reading = $Odometer_Reading_Km;
		}

		$ECM_Reading_Miles = $warranty["ECM_Read_Miles_Cnt"];
		$ECM_Reading_Km = $warranty["ECM_Read_Kms_Cnt"];
		if ($ECM_Reading_Miles != 0) {
			$ECM_Miles_Or_KM = "Miles";
			$ECM_Reading = $ECM_Reading_Miles;
		} else {
			$ECM_Miles_Or_KM = "KM";
			$ECM_Reading = $ECM_Reading_Km;
		}

		//APU
		$APU_Flg = $warranty["APU_Flg"];
		$apuMake = $warranty["Veh_APU_Eng_Mk_Cd"];
		$apuModel = $warranty["Veh_APU_Eng_Model_Cd"];
		$apuYear = $warranty["Veh_APU_Eng_Yr_Cd"];
		$apuSerial = $warranty["Veh_APU_Eng_Ser_nbr"];
		$vehIsNew = $warranty["Veh_New_Flg"];
		$vehDescription = $warranty["Veh_Desc"];
		//Component Coverage
		$Tier_Type = $warranty["Cntrct_Lvl_Desc"];
		$AEP_Flg = $warranty["AEP_Flg"];
		$AER_Flg = $warranty["Aerial_Flg"];
		$smallGoodsPackage = $warranty["Small_Goods_Pkg_Flg"];
			$Coverage_Term = $warranty["Cntrct_Term_Mnths_Nbr"];

		//Lien
		$Lien_Holder_Name = $warranty["Lien_Nme"];
		$Lien_Holder_Email = $warranty["Lien_Eml"];
		$Lien_Holder_Address = $warranty["Lien_Addrs"];
		$Lien_Holder_City = $warranty["Lien_Cty"];
		$lienState = $warranty["Lien_Ste"];
		$Lien_Holder_Postal_Code = $warranty["Lien_Pstl"];
		$Lien_Holder_Phone_Number = $warranty["Lien_Phn"];



		$APU_Engine_Make = $warranty["Veh_APU_Eng_Mk_Cd"];
		$APU_Engine_Model = $warranty["Veh_APU_Eng_Model_Cd"];
		$APU_Engine_Year = $warranty["Veh_APU_Eng_Yr_Cd"];
		$APU_Engine_Serial = $warranty["Veh_APU_Eng_Ser_nbr"];
		$APU_Flg = $warranty["APU_Flg"];
		$wearables_flag = $warranty["wearables_flag"];
		$Vehicle_New_Flag = $warranty["Veh_New_Flg"];  // check value on this, coming back as 'k'?
		$Vehicle_Description = $warranty["Veh_Desc"];
		$Tier_Type = $warranty["Cntrct_Lvl_Cd"];
		$Tier_Type_Desc = $warranty["Cntrct_Lvl_Desc"];

		$Apparatus_Equipment_Package = $warranty["AEP_Flg"];
		$Aerial_Package = $warranty["Aerial_Flg"];
		$Coverage_Term = $warranty["Cntrct_Term_Mnths_Nbr"];
		$smallGoodsPackage = $warranty["Small_Goods_Pkg_Flg"];
		$Srvc_Veh_Flg = $warranty["Srvc_Veh_Flg"];



		$type =  $Veh_Type_Nbr;
		$agreeDate = '<u>' . date('d-m-Y', strtotime($agreementDate)) . '</u>';
		$assignDate = date('m-d-Y', strtotime($agreementDate));

		//Customer State
		if($customerState)
			{
				$customerStatePDF = selectState($link, $customerState);
			}
		else
			{
				$customerStatePDF  = "";
			}


		//Dealer State
		if($dealerState)
			{
			$dealerStatePDF = selectState($link, $dealerState);
			}
		else
			{
				$dealerStatePDF = "";
			}

		//Lien Holder State
		if($lienState)
			{
			$Lien_Holder_State_Province_pdf = selectState($link, $lienState);
			}
		else
			{
				$Lien_Holder_State_Province_pdf = "";
			}

		if($Tier_Type == 'S')
			{
				$Tier_Type = 'SQUAD';
			}
		else
			{
				$Tier_Type = 'BATTALION';
			}

		if ($Coverage_Term == '0.5') {
			// $Coverage_Term = '5 YRS UNL'; // why this change?  cparry 10/24/2022
			$Coverage_Term = '0.5';
		} if ($Coverage_Term == '1') {
			// $Coverage_Term = '5 YRS UNL'; // why this change?  cparry 10/24/2022
			$Coverage_Term = '1';
		}else if($Coverage_Term == '3'){
			// $Coverage_Term = '3 YRS UNL'; // why this change?  cparry 10/24/2022
			$Coverage_Term = '3';
		} else if ($Coverage_Term == '5') {
			// $Coverage_Term = '5 YRS UNL'; // why this change?  cparry 10/24/2022
			$Coverage_Term = '5';
		} else if ($Coverage_Term == '7')
			{
				// $Coverage_Term = '7 YRS UNL'; // why this change?  cparry 10/24/2022
				$Coverage_Term = '7';
			}
		else if($Coverage_Term == '10')
			{
				// $Coverage_Term = '10 YRS UNL'; // why this change?  cparry 10/24/2022
				$Coverage_Term = '10';
			}
		
		if ($Odometer_Miles_Or_KM == 'km')
			{
				$Odometer_Miles_Or_KM = 'KM';
			}
		else
			{
				$Odometer_Miles_Or_KM = 'Miles';
			}
		if ($ECM_Miles_Or_KM == 'km')
			{
				$ECM_Miles_Or_KM = 'KM';
			}
		else
			{
				$ECM_Miles_Or_KM = 'Miles';
			}

		if($Apparatus_Equipment_Package == 'N')
			{
				$Apparatus_Equipment_Package = 'NO';
			}
		else
			{
				$Apparatus_Equipment_Package = 'YES';
			}

		if($Aerial_Package == 'N')
			{
				$Aerial_Package = 'NO';
			}
		else
			{
				$Aerial_Package = 'YES';
			}

		if($APU_Flg == 'N')
			{
				$APU_Flg = 'NO';
			}
		else
			{
				$APU_Flg = 'YES';
			}
		$Mon6_12 = false;
		if ($Coverage_Term == '0.5'
		) {
			$Coverage_Term = $type == "TYPE 1" ? '6MTHS/UNL' : '6MTHS UNL';
			$Mon6_12 = true;
		} else if ($Coverage_Term == '1'
		) {
			$Coverage_Term = $type == "TYPE 1" ? '12MTHS/UNL' : '12mths UNL';
			$Mon6_12 = true;
		}else if ($Coverage_Term == '3') {
			$Coverage_Term = '3 YRS UNL';
			$Mon6_12 = false;
		} else if ($Coverage_Term == '5') {
			$Coverage_Term = '5 YRS UNL';
			$Mon6_12 = false;
		} else if ($Coverage_Term == '7') {
			$Coverage_Term = '7 YRS UNL';
			$Mon6_12 = false;
		} else if ($Coverage_Term == '10') {
			$Coverage_Term = '10 YRS UNL';
			$Mon6_12 = false;
		}

		$data = [
			'AGREEMENT DATE' => $assignDate,
			'CUSTOMER NAME' => $customerName,
			'CUSTOMER EMAIL' =>$customerEmail,
			'CUSTOMER PH#' => $customerPhone,
			'CUSTOMER ADDRESS' => $customerAddress,
			'CUSTOMER CITY' => $customerCity,
			'CUSTOMER STATE/PROVINCE' => $customerStatePDF,
			'CUSTOMER ZIP/POSTAL CODE' => $customerZip,
			'VEHICLE TYPE' => $Veh_Type_Nbr,
			'FULL VIN' => $Vehicle_Vin_Number,
			'TRUCK YEAR' => $Vehicle_Year,
			'TRUCK MAKE' => $Vehicle_Make,
			'TRUCK MODEL' => $Vehicle_Model,
			'ENGINE MAKE' => $Engine_Make,
			'ENGINE MODEL' => $Engine_Model,
			'ENGINE SERIAL #' => $Engine_Serial,
			'TRANSMISSION MAKE' => $Transmission_Make,
			'TRANSMISSION MODEL' => $Transmission_Model,
			'TRANSMISSION SERIAL #' => $Transmission_Serial,
			'RETAILER NAME' => $dealerName,
			'PO#' => $dealerAddress2,
			'AR#' => $dealerARNumber,
			'RETAILER PH#' => $dealerPhone,
			'REATILER STREET ADDRESS' => $dealerAddress1,
			'RETAILER CITY' => $dealerCity,
			'RETAILER STATE/PROVINCE' => $dealerStatePDF,
			'RETAILER ZIP/POSTAL CODE' => $dealerZip,
			'LIEN HOLDER NAME' => $Lien_Holder_Name,
			'LIEN HOLDER PH#' => $Lien_Holder_Phone_Number,
			'LIEN HOLDER STREET ADDRESS' => $Lien_Holder_Address,
			'LIEN HOLDER CITY' => $Lien_Holder_City,
			'LIEN HOLDER STATE/PROVINCE' => $Lien_Holder_State_Province_pdf,
			'LIEN HOLDER ZIP/POSTAL CODE' => $Lien_Holder_Postal_Code,
			'COVERAGE' => $Tier_Type,
			'TERM' => $Coverage_Term,
			'ODO READING' => $Odometer_Reading,
			'ODO MILES KM' => $Odometer_Miles_Or_KM,
			'ECM READING' => $ECM_Reading,
			'ECM MILES KM' => $ECM_Miles_Or_KM,
			'Apparatus' => $Apparatus_Equipment_Package,
			'Aerial' => $Aerial_Package,
			'APU' => $APU_Flg,
			'APU YEAR' => $APU_Engine_Year,
			'APU MAKE' => $APU_Engine_Make,
			'APU MODEL'=> $APU_Engine_Model,
			'APU SERIAL #' => $APU_Engine_Serial,
			'ENGINE HOURS' => $Engine_Hours,
			'APU HOURS' => $APU_Hours,
			'CUSTOMER  NAME printed' => $customerName,
			'AUTHORIZED RETAILER  NAME printed' => $dealerName,
			'CUSTOMER AGREEMENT #' => $Vehicle_Vin_Number
		 ];

			$pdfFileName = str_replace(" ", "_", $customerName) . '_' . str_replace(" ", "_", $customerPhone) . '_' . time() . '.pdf';

			$pdf = new GeneratePDF;
			$pdf->generate($data , $pdfFileName , $type , $showQuotes,$is_Wrap,$Mon6_12);

			// Save Pddf into database
			/*
			$query = "SELECT p.Pers_ID FROM Pers p, Usr_Loc ul WHERE ul.Dlr_Acct_ID=" . $dealerID . " AND ul.Pers_ID = p.Pers_ID;";
			$result = $link->query($query);
			$row = $result->fetch_assoc();
			$primary_Contact_Person_id = $row['Pers_ID'];
			*/

			if($pers_ID==0){
				$primary_Contact_Person_id = 0;
			}else{
				$primary_Contact_Person_id = $pers_ID;
			}


			// Get the contract info
			$query = "SELECT cd.Cntrct_Dim_ID, cd.Assign_Rtlr_Nbr FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
			$result = $link->query($query);
			$row = $result->fetch_assoc();

			$contract_dim_ID = $row["Cntrct_Dim_ID"];
			$dealer_AR_number = $row["Assign_Rtlr_Nbr"];

			// Add this file to our File_Assets tracking table
			$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
							Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,7,'Warranty',NOW())");

			/* Bind variables to parameters */
			$val1 = $dealerID;
			$val2 = $primary_Contact_Person_id;
			$val3 = $adminID;
			//$val4 = $contract_dim_ID;
			$val4 = $new_Cntrct_ID;
			$val5 = '/uploads/warranty_pdf/' . $pdfFileName;
			mysqli_stmt_bind_param($stmt, "iiiis", $val1, $val2, $val3, $val4, $val5);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);


			// Look up the Small_Goods_Pkg_Flg from the Cntrct_Dim table.
			$query = "SELECT * FROM Cntrct_Dim WHERE Cntrct_Dim_ID=" . $new_contract_dim_ID.";";
			$result = $link->query($query);
			$row = $result->fetch_assoc();
			$Small_Goods_Pkg_Flg = $row["Small_Goods_Pkg_Flg"];


			// Now if smallgood flag is set, copy the Small Goods values
			//  from Summary to Detail for the new Warranty Draft
			if($Small_Goods_Pkg_Flg=="Y"){

				$smallGoodsQuery = "SELECT * FROM Sml_Goods_Cvge WHERE Cntrct_ID=".$warrantyID;
				$smallGoodsResult = $link->query($smallGoodsQuery);

				// Loop over the summary level items that we have in small goods.
				while ($smallGoodsRow = mysqli_fetch_assoc($smallGoodsResult)) {
					$Item_Cat_Type_Cd      = $smallGoodsRow["Item_Cat_Type_Cd"];
					$Item_Cat_Type_Desc    = $smallGoodsRow["Item_Cat_Type_Desc"];
					$Gnrc_Blended_Prc_Amt  = $smallGoodsRow["Gnrc_Blended_Prc_Amt"];
					$Gnrc_Lmt_Of_Lblty_Amt = $smallGoodsRow["Gnrc_Lmt_Of_Lblty_Amt"];
					$Gnrc_Item_Cat_Qty_Cnt = $smallGoodsRow["Gnrc_Item_Cat_Qty_Cnt"];  // Use this field to multiply INSERT for detail.
					$Gnrc_Item_Extd_Amt    = $smallGoodsRow["Gnrc_Item_Extd_Amt"];
					$Sml_Goods_Gnrc_Prcg_ID = $smallGoodsRow["Sml_Goods_Gnrc_Prcg_ID"];
					$Actl_Lmt_Of_Liabiltiy_Amt = $smallGoodsRow["Actl_Lmt_Of_Liabiltiy_Amt"];
					$Actl_Prc_Amt          = $smallGoodsRow["Actl_Prc_Amt"];
					$Sales_Agt_Cst_Amt     = $smallGoodsRow["Sales_Agt_Cst_Amt"];
					$Sales_Agt_Comssn_Amt  = $smallGoodsRow["Sales_Agt_Comssn_Amt"];
					$Dlr_Cst_Amt           = $smallGoodsRow["Dlr_Cst_Amt"];
					$Dlr_Mrkp_Max_Amt      = $smallGoodsRow["Dlr_Mrkp_Max_Amt"];

					// If the item has a Count > 1, then we insert that many rows in the new detail view
					//  (which is the same table, but new contractID)
					if($Gnrc_Item_Cat_Qty_Cnt>0){
						for($i=0;$i<$Gnrc_Item_Cat_Qty_Cnt;$i++){
							/* Prepare an INSERT statement to create a new Sml_Goods_Cvge entry */
							// Set the count=1 for each detail row.
							$stmt = mysqli_prepare($link, "INSERT INTO `Sml_Goods_Cvge`
														(Item_Cat_Type_Cd,Item_Cat_Type_Desc,Gnrc_Blended_Prc_Amt,
															Gnrc_Lmt_Of_Lblty_Amt,Gnrc_Item_Cat_Qty_Cnt,Gnrc_Item_Extd_Amt,
															Sml_Goods_Gnrc_Prcg_ID,Actl_Lmt_Of_Liabiltiy_Amt,Actl_Prc_Amt,
															Sales_Agt_Cst_Amt,Sales_Agt_Comssn_Amt,Dlr_Cst_Amt,Dlr_Mrkp_Max_Amt,
															ETL_Create_Dt,Cntrct_ID)
														VALUES (?,?,?,?,1,?,?,?,?,?,?,?,?,NOW(),?)");

							$val1 = $Item_Cat_Type_Cd;
							$val2 = $Item_Cat_Type_Desc;
							$val3 = $Gnrc_Blended_Prc_Amt;
							$val4 = $Gnrc_Lmt_Of_Lblty_Amt;
							$val5 = $Gnrc_Item_Extd_Amt;
							$val6 = $Sml_Goods_Gnrc_Prcg_ID;
							$val7 = $Actl_Lmt_Of_Liabiltiy_Amt;
							$val8 = $Dlr_Cst_Amt+$Dlr_Mrkp_Max_Amt;  //fixing a bug that caused $Actl_Prc_Amt to be multipled by quantity from the summary form and carried forward
							$val9 = $Sales_Agt_Cst_Amt;
							$val10 = $Sales_Agt_Comssn_Amt;
							$val11 = $Dlr_Cst_Amt;
							$val12 = $Dlr_Mrkp_Max_Amt;
							$val13 = $new_Cntrct_ID;

							mysqli_stmt_bind_param($stmt, "ssiiiiiiiiiii", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13);

							/* Execute the statement */
							$result = mysqli_stmt_execute($stmt);

						} // for($i=0;$i<$Gnrc_Item_Cat_Qty_Cnt;$i++) //

					} // if($Gnrc_Item_Cat_Qty_Cnt>0) //

				} // while ($smallGoodsRow = mysqli_fetch_assoc($smallGoodsResult)) //

				// Call our routine to update the Small Goods TOTAL columns in the Cntrct table, based on the changes
				//  to small good here.
				$totalSGUpdateResult = updateWarrantySmallGoodsTotals($link,$warrantyID);

				// Call our function to updated the TOTALS columns in the Cntrct table, which is the sum
				//  of base + add-on + small goods.  Need to refresh these totals whenever changes are made
				$totalUpdateResult = updateWarrantyTotals($link,$warrantyID);

				$query = "SELECT * FROM File_Assets WHERE Warranty_Cntrct_ID=" . $warrantyID." AND File_Asset_Type_ID = 13 ORDER BY File_Asset_ID DESC;";
				$result = $link->query($query);
				$row = $result->fetch_assoc();

				$accID = $row["Acct_ID"];
				$dealerPers = $row["Dealer_Pers_ID"];
				$vtPers = $row["VT_Pers_ID"];
				$dealerCntrct = $row["Dealer_Cntrct_ID"];
				$warrantyContrct = $row["Warranty_Cntrct_ID"] + 1;
				$filePath = $row["Path_to_File"];

				$query = "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,Warranty_Cntrct_ID,
				Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES ($accID,$dealerPers,$vtPers,$dealerCntrct,$warrantyContrct,'$filePath',13,'Small Goods Summary',NOW())";
				$result = $link->query($query);


				//


				//Small goods detail PDF
				// create new PDF document

				// Get small goods detail associated with this warrantyID
				$query  = "SELECT * FROM Sml_Goods_Cvge WHERE Cntrct_ID=".$warrantyID." AND Is_Deleted_Flg!='Y'";
				$smallGoodsDetail = $link->query($query);
				$dataArray = [];
				$smallGoods_count = 0;

				while($row = mysqli_fetch_assoc($smallGoodsDetail))
				{

					$itemQuatinty = $row['Gnrc_Item_Cat_Qty_Cnt'];
					while($itemQuatinty != 0){
						array_push( $dataArray,$row );
						$smallGoods_count++;
						$itemQuatinty--;
					}
				}


				// $smallGoods_count++;
				// array_push( $dataArray,$row );
		      // }

				$loopwillIterate = ceil($smallGoods_count / 30);

				$allSmallGoodsDetailPDF = [];

				$start = 0;

				if($loopwillIterate > 1)
				{
					$end = 30;
				}
				else
				{
					$end = $smallGoods_count;
				}

			for( $i=0 ; $i < $loopwillIterate ; $i++ ) {

				$vin = "";

				$data = [ 'CUSTOMER NAME' => $customerName, 'Submission Date' => $agreementDate, 'TRUCK VIN' => $vin, 'Page #', 'Total Pages',
				 		' Entry 1 Type'  => "" , ' Entry 1 Purchase Date'  => "", 'Entry 1 Make'   => "", 'Entry 1 Model'  => "" , 'Entry 1 Serial #' => "", 'Entry 1 Receipt' => "", 'Entry 1 Approved' => "",
				 		' Entry 2 Type'  => "" , ' Entry 2 Purchase Date'   => "", 'Entry 2 Make'   => "", 'Entry 2 Model'  => "" , 'Entry 2 Serial #' => "", 'Entry 2 Receipt' => "", 'Entry 2 Approved' => "",
						' Entry 3 Type'  => "" , ' Entry 3 Purchase Date'   => "", 'Entry 3 Make'   => "", 'Entry 3 Model'  => "" , 'Entry 3 Serial #' => "", 'Entry 3 Receipt' => "", 'Entry 3 Approved' => "",
						' Entry 4 Type'  => "" , ' Entry 4 Purchase Date'   => "", 'Entry 4 Make'   => "", 'Entry 4 Model'  => "" , 'Entry 4 Serial #' => "", 'Entry 4 Receipt' => "", 'Entry 4 Approved' => "",
						' Entry 5 Type'  => "" , ' Entry 5 Purchase Date'   => "", 'Entry 5 Make'   => "", 'Entry 5 Model'  => "" , 'Entry 5 Serial #' => "", 'Entry 5 Receipt' => "", 'Entry 5 Approved' => "",
						' Entry 6 Type'  => "" , ' Entry 6 Purchase Date'   => "", 'Entry 6 Make'   => "", 'Entry 6 Model'  => "" , 'Entry 6 Serial #' => "", 'Entry 6 Receipt' => "", 'Entry 6 Approved' => "",
						' Entry 7 Type'  => "" , ' Entry 7 Purchase Date'   => "", 'Entry 7 Make'   => "", 'Entry 7 Model'  => "" , 'Entry 7 Serial #' => "", 'Entry 7 Receipt' => "", 'Entry 7 Approved' => "",
						' Entry 8 Type'  => "" , ' Entry 8 Purchase Date'   => "", 'Entry 8 Make'   => "", 'Entry 8 Model'  => "" , 'Entry 8 Serial #' => "", 'Entry 8 Receipt' => "", 'Entry 8 Approved' => "",
						' Entry 9 Type'  => "" , ' Entry 9 Purchase Date'   => "", 'Entry 9 Make'   => "", 'Entry 9 Model'  => "",  'Entry 9 Serial #', 'Entry 9 Receipt' => "", 'Entry 9 Approved' => "",
						' Entry 10 Type' => "" , ' Entry 10 Purchase Date'  => "", 'Entry 10 Make'  => "", 'Entry 10 Model' => "", 'Entry 10 Serial #' => "", 'Entry 10 Receipt' => "", 'Entry 10 Approved' => "",
						' Entry 11 Type' => "" , ' Entry 11 Purchase Date'  => "", 'Entry 11 Make'  => "", 'Entry 11 Model' => "" , 'Entry 11 Serial #' => "", 'Entry 11 Receipt' => "", 'Entry 11 Approved' => "",
						' Entry 12 Type' => "" , ' Entry 12 Purchase Date'  => "", 'Entry 12 Make'  => "", 'Entry 12 Model' => "" , 'Entry 12 Serial #' => "", 'Entry 12 Receipt' => "", 'Entry 12 Approved' => "",
						' Entry 13 Type' => "" , ' Entry 13 Purchase Date'  => "", 'Entry 13 Make'  => "", 'Entry 13 Model' => "" , 'Entry 13 Serial #' => "", 'Entry 13 Receipt' => "", 'Entry 13 Approved' => "",
						' Entry 14 Type' => "" , ' Entry 14 Purchase Date'  => "", 'Entry 14 Make'  => "", 'Entry 14 Model' => "" , 'Entry 14 Serial #' => "", 'Entry 14 Receipt' => "", 'Entry 14 Approved' => "",
						' Entry 15 Type' => "" , ' Entry 15 Purchase Date'  => "", 'Entry 15 Make'  => "", 'Entry 15 Model' => "" , 'Entry 15 Serial #' => "", 'Entry 15 Receipt' => "", 'Entry 15 Approved' => "",
						' Entry 16 Type' => "" , ' Entry 16 Purchase Date'  => "", 'Entry 16 Make'  => "", 'Entry 16 Model' => "" , 'Entry 16 Serial #' => "", 'Entry 16 Receipt' => "", 'Entry 16 Approved' => "",
						' Entry 17 Type' => "" , ' Entry 17 Purchase Date'  => "", 'Entry 17 Make'  => "", 'Entry 17 Model' => "" , 'Entry 17 Serial #' => "", 'Entry 17 Receipt' => "", 'Entry 17 Approved' => "",
						' Entry 18 Type' => "" , ' Entry 18 Purchase Date'  => "", 'Entry 18 Make'  => "", 'Entry 18 Model' => "" , 'Entry 18 Serial #' => "", 'Entry 18 Receipt' => "", 'Entry 18 Approved' => "",
						' Entry 19 Type' => "" , ' Entry 19 Purchase Date'  => "", 'Entry 19 Make'  => "", 'Entry 19 Model' => "" , 'Entry 19 Serial #' => "", 'Entry 19 Receipt' => "", 'Entry 19 Approved' => "",
						' Entry 20 Type' => "" , ' Entry 20 Purchase Date'  => "", 'Entry 20 Make'  => "", 'Entry 20 Model' => "" , 'Entry 20 Serial #' => "", 'Entry 20 Receipt' => "", 'Entry 20 Approved' => "",
						' Entry 21 Type' => "" , ' Entry 21 Purchase Date'  => "", 'Entry 21 Make'  => "", 'Entry 21 Model' => "" , 'Entry 21 Serial #' => "", 'Entry 21 Receipt' => "", 'Entry 21 Approved' => "",
						' Entry 22 Type' => "" , ' Entry 22 Purchase Date'  => "", 'Entry 22 Make'  => "", 'Entry 22 Model' => "" , 'Entry 22 Serial #' => "", 'Entry 22 Receipt' => "", 'Entry 22 Approved' => "",
						' Entry 23 Type' => "" , ' Entry 23 Purchase Date'  => "", 'Entry 23 Make'  => "", 'Entry 23 Model' => "" , 'Entry 23 Serial #' => "", 'Entry 23 Receipt' => "", 'Entry 23 Approved' => "",
						' Entry 24 Type' => "" , ' Entry 24 Purchase Date'  => "", 'Entry 24 Make'  => "", 'Entry 24 Model' => "" , 'Entry 24 Serial #' => "", 'Entry 24 Receipt' => "", 'Entry 24 Approved' => "",
						' Entry 25 Type' => "" , ' Entry 25 Purchase Date'  => "", 'Entry 25 Make'  => "", 'Entry 25 Model' => "" , 'Entry 25 Serial #' => "", 'Entry 25 Receipt' => "", 'Entry 25 Approved' => "",
						' Entry 26 Type' => "" , ' Entry 26 Purchase Date'  => "", 'Entry 26 Make'  => "", 'Entry 26 Model' => "" , 'Entry 26 Serial #' => "", 'Entry 26 Receipt' => "", 'Entry 26 Approved' => "",
						' Entry 27 Type' => "" , ' Entry 27 Purchase Date'  => "", 'Entry 27 Make'  => "", 'Entry 27 Model' => "", 'Entry 27 Serial #' => "", 'Entry 27 Receipt' => "", 'Entry 27 Approved' => "",
						' Entry 28 Type' => "" , ' Entry 28 Purchase Date'  => "", 'Entry 28 Make'  => "", 'Entry 28 Model' => "" , 'Entry 28 Serial #' => "", 'Entry 28 Receipt' => "", 'Entry 28 Approved' => "",
						' Entry 29 Type' => "" , ' Entry 29 Purchase Date'  => "", 'Entry 29 Make'  => "", 'Entry 269Model' => "" , 'Entry 29 Serial #' => "", 'Entry 29 Receipt' => "", 'Entry 29 Approved' => "",
				 		' Entry 30 Type' => "" , ' Entry 30 Purchase Date'  => "", 'Entry 30 Make'  => "", 'Entry 30 Model' => "" , 'Entry 30 Serial #' => "", 'Entry 30 Receipt' => "", 'Entry 30 Approved' => "", ];


			   $counter = 1;

			   for($j =  $start ; $j < $end ; $j++){
				$data['Entry '.$counter.' Type'] = $dataArray[$j]["Item_Cat_Type_Cd"];
				$data['Entry '.$counter.' Purchase Date'] = $dataArray[$j]["Mfrd_Yr_Nbr"];
				$data['Entry '.$counter.' Make'] = $dataArray[$j]["Mk_Nbr"];
				$data['Entry '.$counter.' Model'] = $dataArray[$j]["Model_Nbr"];
				$data['Entry '.$counter.' Serial #'] = $dataArray[$j]["Ser_nbr"];
				$counter++;
				}
				$fileNumber = $i +1;
				$data['Page #'] = $fileNumber;
				$data['Total Pages'] = $loopwillIterate;
				$filename = "smallGoodsDeta".$fileNumber.".pdf";
				$pdf = new GeneratePDF;
				$response = $pdf->generateSgDetail($data , $filename);
				$PdfLocation = 'uploads/small_goods_detail_pdf_for_merging/' . $filename;
				array_push( $allSmallGoodsDetailPDF , $PdfLocation);

				$start+=30;

				$remaining = $smallGoods_count - $end;

				if($remaining > 30)
				{
					$end +=30;
				}
				else
				{
					$end +=  $remaining;

				}

				// die("Start".$start."end".$end);

		    }

			// die($allSmallGoodsDetailPDF[1]);

			// $pdf = new \PDFMerger\PDFMerger;
			$pdfFileName = str_replace(" ", "_", $customerName) . '_' . str_replace(" ", "_", $warrantyID) . '_' . time() . '.pdf';
			$command = "/home/dh_pp7hie/pdftk";
			$outputdir = "/home/dh_pp7hie/portaldev.vitaltrendsusa.com/uploads/small_goods_detail_pdf/".$pdfFileName;
			$pdfFiles = " ";
			for( $i = 0 ; $i<$loopwillIterate ; $i++){

				$fileNumber = $allSmallGoodsDetailPDF[$i];
				$pdfFiles .= $fileNumber;
				$pdfFiles .= " ";
			}
			exec($command.$pdfFiles." cat output ".$outputdir);

				// Save Pddf into database
				/*
				$query = "SELECT p.Pers_ID FROM Pers p, Usr_Loc ul WHERE ul.Dlr_Acct_ID=" . $dealerID . " AND ul.Pers_ID = p.Pers_ID;";
				//$query = "SELECT Pers_ID FROM Pers WHERE Acct_ID=" . $dealerID . ";";
				$result = $link->query($query);
				$row = $result->fetch_assoc();
				$primary_Contact_Person_id = $row['Pers_ID'];
				*/

				if($pers_ID==0){
					$primary_Contact_Person_id = 0;
				}else{
					$primary_Contact_Person_id = $pers_ID;
				}


				// Get the contract info
				$query = "SELECT cd.Cntrct_Dim_ID FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
				$result = $link->query($query);
				$row = $result->fetch_assoc();

				$contract_dim_ID = $row["Cntrct_Dim_ID"];

				// Add this file to our File_Assets tracking table
				//  Set type=2 for 'dealer W9'.
				$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,Warranty_Cntrct_ID,
								Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,?,18,'Small Goods Detail',NOW())");

				/* Bind variables to parameters */
				$val1 = $dealerID;
				$val2 = $primary_Contact_Person_id;
				$val3 = $adminID;
				$val4 = $contract_dim_ID;
				$val5 = '/uploads/small_goods_detail_pdf/' . $pdfFileName;
				// $val6 = $warrantyID;
				$val6 = $new_Cntrct_ID;



				mysqli_stmt_bind_param($stmt, "iiiiis", $val1, $val2, $val3, $val4, $val6, $val5);

				/* Execute the statement */
				$result = mysqli_stmt_execute($stmt);

				// Close and output PDF document
				// This method has several options, check the source code documentation for more information.


				//============================================================+
				// END OF FILE
				//============================================================+

				// End PDF Code here


            }

		header("location: create_warranty.php?warrantyID=".$new_Cntrct_ID);
		exit;

	} // }elseif($edit_action=="createWarranty"){ //

} // if($_SERVER["REQUEST_METHOD"] == "POST") //

//Filter form is submitted.
// if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_POST["filter"])) {



// }

require_once("includes/header.php");


?>

		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
			<div class="container-fluid">
            <?php require_once("includes/common_page_content.php"); ?>
<!--
                <div class="row">
                    <div class="col-md-6">
                        <div>
                            <img src="images/VTPoweredbyTNG.png" alt="Vital Trends Powered by TruNorth">
                        </div>
                    </div>
                    <div class="col-md-6">
						&nbsp;
                    </div>
                </div>
-->
                <!-- row -->
                <div class="row">
                    <div class="col-lg-12">
						<?php

							if(isset($_SESSION['status']))
								{
									?>
										<div class="alert alert-success alert-dismissible fade show" role="alert">
											<strong></strong> <?= $_SESSION['status']; ?>
										</div>
									<?php
									unset($_SESSION['status']);
								}

								if(isset($_SESSION['error']))
								{
									?>
										<div class="alert alert-danger alert-dismissible fade show" role="alert">
											<strong></strong> <?= $_SESSION['error']; ?>
										</div>
									<?php
									unset($_SESSION['error']);
								}
                         ?>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Pending <?php if($showQuotes=="Y"){echo "Quotes";}else{echo "Warranties";} ?> for Dealer</h4>
                            </div>
							<div class="card-header text-center">
								<h5><?php if($showQuotes=="N"){ echo "(<a href='warranty_pending.php?showQuotes=Y'>Go To Pending Quotes</a>)";} else{echo "(<a href='warranty_pending.php'>Go To Pending Warranties</a>)";} ?></h5>
							</div>
								<!---
							<div class="card-header text-center">
								<form name="searchfields" action="warranty_pending.php" method="get">
									<input type="hidden" name="showQuotes" value="<?php echo $showQuotes; ?>" />
									<input type="hidden" name="filter" value="true">
									Location Filter: <select name="locations" class="edit_action" onchange="this.form.submit();">
										<option value="1">loc 1</option>
										<option value="2">loc 2</option>
									</select>
									|
									Customer Filter: <select name="locations" class="edit_action" onchange="this.form.submit();">
										<option value="1">cust 1</option>
										<option value="2">cust 2</option>
									</select>
									|
									Agent Filter: <select name="locations" class="edit_action" onchange="this.form.submit();">
										<option value="1">agent 1</option>
										<option value="2">agent 2</option>
									</select>
								</form>
							</div>
								--->
                            <div class="card-body">
								<span id="isQuote" class="d-none"><?php echo $showQuotes; ?></span>
                                <div class="table-responsive">
									<div class="watermark">
										<img src="images/logo_large_bg.png" alt="">
									</div>
                                    <table id="warrantyTable" class="table table-responsive-md">
									<div class="table-responsive">
                                    <table class="table table-responsive-md" id="finance_table">
                                        <thead>
                                            <tr>
                                                <th>Customer Name</th>
                                                <th>Vehicle Make</th>
                                                <th>VIN</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Small Goods</th>
                                                <!--<th>Pricing</th>-->
                                                <th>Actions</th>
												<!--
												<th>PDF File</th>
												-->
                                            </tr>
                                        </thead>
                                        <tbody>
<?php

//  SECURITY
//   if we have an agent logged in, who is not the primary, then limit their view to only their items
if($userType=="dealer" && $isContactPerson!="Y" && $persID!="" && !$adminLoggedIn){
	$limitView = "Y";
}else{
	$limitView = "N";
}

/*
if(true || $adminID==1 || $roleID==3){
echo "-----------DEBUG INFO---------------<br />";
echo "dealerID=".$dealerID;
echo "<Br />roleID=".$roleID;
echo "<Br />persID=".$persID;
echo "<Br />limitView=".$limitView;
echo "<br />-----------DEBUG INFO---------------";
//print_r($_SESSION);
//die();
}
*/

if($roleID==3){
	$limitView = "N";
}

if($dealerID==1348 && $persID=504){
	$limitView = "N";
	$roleID = 3;
}


// If we have an agency logged in, then we need to show quotes/warranties that were either
//  created by the agency itself
//  or created by an agent of the agency.
//  To do this, we are calling getAcctIDForAgency($link,$agencyPrimaryUserID,$agencyAcctID)
//    this gives a comma-delimited list of Acct_ID values (dealers) that meet that criteria.
//  Then we use that list to look up warranties accordingly.

$acctIDList=$dealerID;

/*
echo "roleID=".$roleID;
echo "<br />";
echo "agencyAccountID=".$_SESSION["agencyAccountID"];
echo "<br />";
echo "userID=".$userID;
echo "<br />";
*/

if($roleID == 5){
	// For Agency
	if(isset($_SESSION["agencyAccountID"]) && $_SESSION["agencyAccountID"]!=""){
		$acctIDList = getAcctIDForAgency($link,$userID,$_SESSION["agencyAccountID"]);
//echo "acctIDList=".$acctIDList;
//echo "<br />";
	}
}

if($roleID == 2){
	// For Dealer Primary
	$acctIDList = getAcctIDForAgency($link,$userID,$acct_ID);
}



// Existing warranty or quote drafts
if($showQuotes=="Y"){
	// $query = "SELECT * FROM New_Warranty_Temp WHERE Acct_ID=".$dealerID." ORDER BY Created_Date ASC";
/*
	$query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Mfr_Acct_ID=".$dealerID." AND
			  c.Created_Warranty_ID is NULL AND
	          c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
	          cd.Cntrct_Type_Cd='WQ' AND
	          cd.Is_Deleted_Flg != 'Y' AND
	          c.Veh_ID = v.Veh_ID";
*/

	$query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Mfr_Acct_ID in (
				SELECT Acct_ID FROM Acct WHERE Acct_ID in (".$acctIDList.") OR Prnt_Acct_ID in (".$acctIDList.")
				)
				AND
			  c.Created_Warranty_ID is NULL AND
	          c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
	          cd.Cntrct_Type_Cd='WQ' AND
	          cd.Is_Deleted_Flg != 'Y' AND
	          c.Veh_ID = v.Veh_ID";

	if($limitView=="Y"){
		$query .= " AND Dlr_Agt_Prsn_ID=".$persID.";";
	}

//echo "query=".$query;
//die();
}else{
	// Exclude quotes that have been made into warranties, Created_Warranty_ID is not null.
/*
	$query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Mfr_Acct_ID=".$dealerID." AND
			  c.Created_Warranty_ID is NULL AND
			  c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
	          cd.Cntrct_Type_Cd='WD' AND
	          cd.Is_Deleted_Flg != 'Y' AND
	          c.Veh_ID = v.Veh_ID";
*/

	$query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Mfr_Acct_ID in (
				SELECT Acct_ID FROM Acct WHERE Acct_ID in (".$acctIDList.") OR Prnt_Acct_ID in (".$acctIDList.")
				)
				AND
		      c.Created_Warranty_ID is NULL AND
			  c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
	          cd.Cntrct_Type_Cd='WD' AND
	          cd.Is_Deleted_Flg != 'Y' AND
	          c.Veh_ID = v.Veh_ID AND
			  c.Finalized_Warranty_Flg = 'N'";

	if($limitView=="Y"){
		$query .= " AND Dlr_Agt_Prsn_ID=".$persID.";";
	}

if($persID==681){
//	echo "query=".$query;
//	die();
}
}

$warrantyResult = $link->query($query);

//echo "num rows=".mysqli_num_rows($warrantyResult);
//die();

if (mysqli_num_rows($warrantyResult) > 0) {
  // output data of each row
  $loopCounter = 0;
  while($row = mysqli_fetch_assoc($warrantyResult)) {
	$loopCounter++;
?>
<tr>
	<td><?php echo $row["Cstmr_Nme"];?></td>
	<td><?php echo $row["Veh_Mk_Cd"];?></td>
	<td><?php echo $row["Veh_Id_Nbr"];?></td>
	<td><?php echo $row["Veh_Desc"];?></td>
	<td><?php if($row["Cntrct_Type_Desc"]=="Warranty Quote"){ echo "Quote";}else{ echo $row["Cntrct_Type_Desc"];} ?></td>
<?php
  // Show the 'edit small goods' option if Small_Goods_Package==Y
  if($row["Small_Goods_Pkg_Flg"]=="Y"){
  	if($showQuotes=="Y"){
?>
	<td>
		<a href="small_goods_summary_worksheet.php?warrantyID=<?php echo $row["Cntrct_ID"];?>">Edit Small Goods</a>
	</td>
<?php
	}else{
		$warranty = $row["Cntrct_ID"];
		$query = "SELECT Path_to_File FROM File_Assets WHERE Warranty_Cntrct_ID=" .$warranty." AND File_Asset_Type_ID = 13 ORDER BY File_Asset_ID DESC";
		$result = $link->query($query);
		$smallGoodsPDF = mysqli_fetch_assoc($result);

		$query = "SELECT Path_to_File FROM File_Assets WHERE Warranty_Cntrct_ID=" .$warranty." AND File_Asset_Type_ID = 18 ORDER BY File_Asset_ID DESC";
		$result = $link->query($query);
		$smallGoodsDetailPDF = mysqli_fetch_assoc($result);
    ?>
	<td>
		<a href="small_goods_worksheet.php?warrantyID=<?php echo isset($row["Cntrct_ID"]) ? $row["Cntrct_ID"] : "";?>">Edit</a> | <a href="<?php echo isset($smallGoodsPDF['Path_to_File']) ? $smallGoodsPDF['Path_to_File'] : ""; ?>" target="__blank">Print</a> | <a href="<?php echo isset($smallGoodsDetailPDF['Path_to_File']) ? $smallGoodsDetailPDF['Path_to_File'] : ""; ?>" target="__blank">Detail</a><br>
		<a href="small_goods_summary.php?warrantyID=<?php echo isset($row["Cntrct_ID"]) ?$row["Cntrct_ID"] : "";?>">Show Summary</a>
	</td>
<?php
	}
  }else{
?>
	<td>&nbsp;</td>
<?php
  }
?>
<?php
// Remove Addendum link from here for now.
if(false){
	if($showQuotes !="Y")
	{
			$query = "SELECT Path_to_File FROM File_Assets WHERE Warranty_Cntrct_ID=" .$row["Cntrct_ID"]." AND File_Asset_Type_ID = 21 ORDER BY File_Asset_ID DESC";
			$result = $link->query($query);
			$addendumPDF = mysqli_fetch_assoc($result);
			if($addendumPDF)
			{
	?>
				<td><a href = "<?php echo $addendumPDF["Path_to_File"] ?>" target="__blank">Print</td>
				<?php
			}
			else
			{
				?>
				<td></td>
				<?php
			}
	}
}
?>
	<td style="white-space: nowrap;">
		<form name="action_form_<?php echo $row["Cntrct_ID"]; ?>" class="action_form" action="warranty_pending.php" method="POST">
			<input type="hidden" name="warrantyID" value="<?php echo $row["Cntrct_ID"]; ?>" />
			<input type="hidden" name="showQuotes" value="<?php echo $showQuotes; ?>" />
			<select name="edit_action" class="edit_action">
				<option value="Edit">Edit</option>
				<option value="PriceSummary">Price Summary</option>
				<option value="Print">Print</option>
				<option value="Generate_Invoice">Generate Invoice</option>
				<option value="Delete">Delete</option>
				<?php if($showQuotes=="Y"){?>
					<option value="createWarranty">Create Warranty</option>
				<?php }else{ ?>
					<option value="signElectronically">Sign On Line</option>
				<?php } ?>
			</select>
			<input type="button" class="action_form_submit" value="Go"/>
		</form>
		<!---
		<a href="create_warranty.php?warrantyID=<?php echo $row["Cntrct_ID"]; ?>">Edit</a>
		<a href="">Print</a>
		<a href="">Delete</a>
		<a href="warranty_finalize.php?warrantyID=<?php echo $row["Cntrct_ID"]; ?>">Create Warranty</a>
		--->
	</td>
	<?php
	// Remove the pdf upload/display from this table, and move to the 'uploads' page.
	if(false){

		// Check if we have uploaded an ink signed warranty.
		if($showQuotes=="Y"){
			$query = "SELECT * FROM File_Assets WHERE Acct_ID=" . $dealerID . " AND Dealer_Cntrct_ID=".$row["Cntrct_ID"]." AND File_Asset_Type_ID = 16 ORDER BY createdDate DESC;";
		}else{
			$query = "SELECT * FROM File_Assets WHERE Acct_ID=" . $dealerID . " AND Dealer_Cntrct_ID=".$row["Cntrct_ID"]." AND File_Asset_Type_ID = 17 ORDER BY createdDate DESC;";
		}

		$file = $link->query($query);

		if (mysqli_num_rows($file) > 0) {
			$row = $file->fetch_assoc();
		?>
		<td> <span class="badge badge-success">Uploaded</span></td>
		<td><a href="#"  class="changePDF">Change PDF</a></td>
		<td class="d-none fileID"><?php echo  $row["File_Asset_ID"]; ?></td>
		<td class="d-none oldFile"><?php echo  $row["Path_to_File"]; ?></td>
		<?php
		} else {
		?>
		<td> <span class="badge badge-danger">Pending</span></td>
		<td><a class="upload" href="#">Upload PDF</a></td>
		<td class="d-none dealerID"><?php echo  $dealerID; ?></td>
		<td class="d-none warrantyID"><?php echo  $row["Cntrct_ID"]; ?></td>
	<?php
		}

	} // if(false) //

	?>

</tr>

<?php
  }
} else {
?>
<tr>
	<td colspan="5">No items found, yet.</td>
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


<!-- Modal -->
<div class="modal fade" id="uploadPDF" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Upload Scanned Signature PDF File</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<form action="uploadScannedPDF.php" method="POST" id="warrantyForm" enctype="multipart/form-data">
		<input type="hidden" name="showQuotes" value="<?php echo $showQuotes; ?>" />
		<input type="hidden" name="dealerID" id="dealerID" name="dealerID">
        <input type="hidden" name="warrantyID" id="warrantyID" name="warrantyID">
		<div class="form-group">
		<input name="warrantyPDF" id="warrantyPDF" type="file"><br>
		<span class="text-danger" id="warrantyPDFE" style="font-size:12px"></span>
		</div>
		<div class="form-group mt-5">
        <button type="button" name="uploadPDF" id="upload" class="btn btn-md btn-primary float-right">Upload</button>
		<button type="button" class="btn btn-md btn-secondary float-right mr-2" data-dismiss="modal">Close</button>
		</div>
		</form>
      </div>
    </div>
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="changePDF" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Change Scanned Signature PDF File</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<form action="changeScannedPDF.php" method="POST" id="changeWarrantyForm" enctype="multipart/form-data">
		<input type="hidden" name="showQuotes" value="<?php echo $showQuotes; ?>" />
		<input type="hidden" name="dealerID" id="changePDFdealerID">
        <input type="hidden" name="fileID" id="fileID">
		<input type="hidden" name="oldFile" id="oldFile">
		<div class="form-group">
		<input name="warrantyPDF" id="changeWarrantyPDF" type="file"><br>
		<span class="text-danger" id="changeWarrantyPDFE" style="font-size:12px"></span>
		</div>
		<div class="form-group mt-5">
        <button type="button" name="changePDF" id="change" class="btn btn-md btn-primary float-right">Upload</button>
		<button type="button" class="btn btn-md btn-secondary float-right mr-2" data-dismiss="modal">Close</button>
		</div>
		</form>
      </div>
    </div>
  </div>
</div>


            <!--**********************************
                dealer_agreement form
            ***********************************-->

<!--            <div class="container-fluid">-->
<!--                <div class="row">-->
<!--                    <div class="col-md-12">-->
<!--                        <div class="card" id="toprint">-->
<!--                            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="table-layout: fixed; width: 100%; border-collapse: collapse;" >-->
<!--                                <tbody>-->
<!--                                    <tr>-->
<!--                                        <td>-->
<!--                                            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="table-layout: fixed; width: 100%; border-collapse: collapse;">-->
<!--                                                <tbody>-->
<!--                                                <tr>-->
<!--                                                    <td>-->
<!--                                                        <a href="index.php">-->
<!--                                                            <img src="images/vt_logo.png" alt="Vital Trends" />-->
<!--                                                        </a>-->
<!--                                                    </td>-->
<!--                                                    <td>-->
<!--                                                        <table  align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" >-->
<!--                                                            <tbody>-->
<!--                                                            <tr>-->
<!--                                                                <td style="text-align: left">-->
<!--                                                                    <h3>VITAL TRENDS DEALER AGREEMENT</h3>-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">SALE AGENT</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">ACCOUNT#</label>-->
<!--                                                                    <input type="number" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            </tbody>-->
<!--                                                        </table>-->
<!---->
<!---->
<!--                                                    </td>-->
<!--                                                </tr>-->
<!--                                                <tr>-->
<!--                                                    <td colspan="2" style="border: 1px solid #000;">-->
<!--                                                        <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="table-layout: fixed; width: 100%; border-collapse: collapse;">-->
<!--                                                            <tbody>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">BUSINESS NAME:</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">DEALER LICENSE#</label>-->
<!--                                                                    <input type="number" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">DBA:</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">CONTACT:</label>-->
<!--                                                                    <input type="phone" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            <tr>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">MAILING ADDRESS:</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                                <td style="padding: 10px 0;">-->
<!--                                                                    <label style="margin: 0; font-weight: bold; color: #000;">OWNER:</label>-->
<!--                                                                    <input type="text" required style="border: 0; border-bottom: 1px solid #000;">-->
<!--                                                                </td>-->
<!--                                                            </tr>-->
<!--                                                            </tbody>-->
<!--                                                        </table>-->
<!--                                                    </td>-->
<!--                                                </tr>-->
<!--                                                </tbody>-->
<!--                                            </table>-->
<!--                                        </td>-->
<!--                                    </tr>-->
<!--                                </tbody>-->
<!--                            </table>-->
<!--                            <div class="card-body">-->
<!--                                <div class="basic-form">-->
<!--                                    <div class="body">-->
<!--                                        <div id="dialog" title="Digital Signature">-->
<!--                                            <canvas id="myCanvasSignature" acknowledged-data="false" class="signature-pad" width="400" height="100"></canvas>-->
<!--                                            <input id="UserName" name="UserName" type="hidden" value="My Signature">-->
<!--                                        </div>-->
<!--                                        <div id="change-sig" title="Edit Signature">-->
<!--                                            Please provide your new digital signature representation.<br/><br/>-->
<!--                                            <canvas id="signature-pad" class="signature-pad" width="400" height="100"></canvas>-->
<!--                                        </div>-->
<!--                                        <div class="signature-card">-->
<!--                                            <div class="legal-clause"></div>-->
<!--                                            <div class="sign-area">-->
<!--                                                <div class="sign-block pull-right">-->
<!--                                                    <div>Signature: <i class="fa fa-pencil" aria-hidden="true" onclick="Signature('jm38s4i1');"></i></div>-->
<!--                                                    <canvas id="Sig-jm38s4i1" class="dig-sig " sig-id-data="jm38s4i1" signed-data="false" width="400" height="100"></canvas>-->
<!--                                                </div>-->
<!--                                            </div>-->
<!--                                            <div class="clearfix"></div>-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <div class="log-file">-->
<!--                                        <p>Log file</p>-->
<!--                                    </div>-->
<!--                                    <form>-->
<!--                                        <button class="btn btn-primary"  onclick="printpart()"/>Sign in</button>-->
<!--                                    </form>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
        </div>
        <!--**********************************
            Content body end
        ***********************************-->

        <!--**********************************
            Footer start
        ***********************************-->
        <div class="footer">
            <div class="copyright">
                <p>Copyright Developed by <a href="http://vitaltrendsusa.com/" target="_blank">Vital Trends</a> 2022</p>
            </div>
        </div>
        <!--**********************************
            Footer end
        ***********************************-->

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
<script src="js/demo.js"></script>
<script>
	$(document).ready(function(){
		$('#preloader').fadeOut(1500);
	});
</script>


<!------------------------- Confirmation Modals --------------------------->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script type="text/javascript">
    $(".action_form_submit").click(function() {
         // get the current row
		var tr = $(this).closest("tr");

		var action = tr.find(".edit_action").val();

		var warrantyType;
		if($("#isQuote").text() == "Y")
		{
			warrantyType = "Quote";
		}
		else
		{
			warrantyType = "Warranty";
		}

		if(action == "Delete")
		{
				swal({
				title: "Are you sure!",
				text: "Do you really want to remove this "+warrantyType+"?",
				icon: "warning",
				buttons: true,
				dangerMode: true,
			}).then((willDelete) => {
				if (willDelete) {
					//$(".edit_action").submit();
					tr.find(".action_form").submit();
					swal("Yaa! "+warrantyType+" successfully deleted!", {
						icon: "success",
					});
				} else {
					swal( warrantyType+" not deleted your "+warrantyType+" is safe!", {
						icon: "error",
					});
				}
			});
		}
		else if(action == "createWarranty"){
			swal({
				title: "Are you sure!",
				text: "Want to turn this Quote into a Warranty?",
				icon: "warning",
				buttons: true,
				dangerMode: true,
			}).then((willDelete) => {
				if (willDelete) {
					tr.find(".action_form").submit();
					swal("Yaa! Quote turned into Warranty successfully!", {
						icon: "success",
					});
				} else {
					swal("Quote not turned into Warranty!", {
						icon: "error",
					});
				}
			});
		}
		else if(action == "Generate_Invoice"){
			window.open('invoice.php?warrantyID='+tr.find("input[name='warrantyID']").val(), '_blank');
		}
		else
		{
			tr.find(".action_form").submit();
		}

    });
</script>
<!------------------------- Confirmation Modals End --------------------------->

<script>
    function carouselReview(){
        /*  testimonial one function by = owl.carousel.js */
        function checkDirection() {
            var htmlClassName = document.getElementsByTagName('html')[0].getAttribute('class');
            if(htmlClassName == 'rtl') {
                return true;
            } else {
                return false;

            }
        }

        jQuery('.testimonial-one').owlCarousel({
            loop:true,
            autoplay:true,
            margin:30,
            nav:false,
            dots: false,
            rtl: checkDirection(),
            left:true,
            navText: ['', ''],
            responsive:{
                0:{
                    items:1
                },
                1200:{
                    items:2
                },
                1600:{
                    items:3
                }
            }
        })
    }
    jQuery(window).on('load',function(){
        setTimeout(function(){
            carouselReview();
        }, 1000);
    });
</script>
<script>
    function printpart () {
        var printwin = window.open("");
        printwin.document.write(document.getElementById("toprint").innerHTML);
        printwin.stop();
        printwin.print();
        printwin.close();
    }
</script>
<script>
	$(document).ready(function(){
		$(".moveToW9").click(function(){
			window.location.href='dealer_w9.php';
		});
	});
</script>
<script>

	//Upload Scanned PDF
	 $("#warrantyTable").on('click', '.upload', function() {

		 // get the current row
		 var tr = $(this).closest("tr");

		var dealerID = tr.find(".dealerID").text();
		var warrantyID = tr.find(".warrantyID").text();

		$("#dealerID").val(dealerID);
		$("#warrantyID").val(warrantyID);

		$('#uploadPDF').modal('show');
	 });

	 $("#upload").click(function() {

		var warrantyID =  $("#warrantyID").val();
		var dealerID =  $("#dealerID").val();
		var flag1 = 0;

		if($("#warrantyPDF")[0].files.length == 0)
		{
			$("#warrantyPDFE").text('Please upload scanned warranty PDF with signature');
		}
		else
		{
			$("#warrantyPDFE").text('');
			flag1 = 1;
		}

		if(flag1 == 1)
		{
			$("#warrantyForm").submit();
		}
	 });



//Change Scanned PDF
$("#warrantyTable").on('click', '.changePDF', function() {

// get the current row
var tr = $(this).closest("tr");

var dealerID = tr.find(".dealerID").text();
var fileID = tr.find(".fileID").text();
var oldFile = tr.find(".oldFile").text();

$("#fileID").val(fileID);
$("#oldFile").val(oldFile);
$("#changePDFwarrantyID").val(warrantyID);

$('#changePDF').modal('show');
});

$("#change").click(function() {

var fileID =  $("#fileID").val();
var oldFile =  $("#oldFile").val();
var dealerID =  $("#changePDFdealerID").val();
var flag1 = 0;

if($("#changeWarrantyPDF")[0].files.length == 0)
{
   $("#changeWarrantyPDFE").text('Please upload scanned warranty PDF with signature');
}
else
{
   $("#changeWarrantyPDFE").text('');
   flag1 = 1;
}

if(flag1 == 1)
{
   $("#changeWarrantyForm").submit();
}
});


</script>
  <script src="./vendor/global/global.min.js"></script>
	<script src="./vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

	<!-- Dashboard 1 -->
    <script src="./js/custom.min.js"></script>
	<script src="./js/deznav-init.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
	<script src="js/demo.js"></script>
<script>
        $(document).ready( function () {
          $('#finance_table').DataTable();
        } );
    </script>
</body>
</html>