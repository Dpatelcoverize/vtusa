<?php

//
// File: create_warranty.php (v4 testing)
// Author: Charles Parry
// Date: 5/20/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Create Warranty";
$pageTitle = "Create Warranty";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');


// Variables.
$dealerID = "";
$Acct_ID = "";  // For location
$dealerAgentID = ""; // For Dealer sales agent
$warrantyID = "";
$warrantyStatus = "";
$agreementDate = "";
$customerName = "";
$customerEmail = "";
$customerAddress = "";
$customerCity = "";
$customerState = "";
$customerZip = "";
$customerPhone = "";
$customerSalesChannel = "Outside sales";
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
$isQuote = "N";
$EVBC_Flg = "";
$EEC_Flg = "";

$smallGoodsPackage = "";

$form_err = "";
$ECM_Reading_Km = "";
$Odometer_Reading_KM = "";


// Menu Controls
$navSection="warranty";
$navItem = "createWarranty";



if (session_status() === PHP_SESSION_NONE) {
        session_start();
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


// Are we making a new quote?
if (isset($_GET["isQuote"])) {
	$isQuote = $_GET["isQuote"];
}


// Are we loading a saved Warranty record, to continue editing?
if (isset($_GET["warrantyID"])) {
	$warrantyID = $_GET["warrantyID"];

	// Quick sanity check of incoming ID value being numeric.
	if (!is_numeric($warrantyID)) {
		header("location: index.php");
		exit;
	}

	// Security: make sure authenticated dealer has rights to this warrantyID
	/*** IMPORTANT ***/


	//$sql = "SELECT * FROM New_Warranty_Temp WHERE New_Warranty_Temp_ID=" . $_GET["warrantyID"];
	$sql = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Cntrct_ID=" . $warrantyID . " AND
	        c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
	        c.Veh_ID = v.Veh_ID";

	$result = $link->query($sql);
	$row = $result->fetch_assoc();

	$customerName = $row["Cstmr_Nme"];
	$customerEmail = $row["Cstmr_Eml"];
	$customerAddress = $row["Cstmr_Addrs"];
	$customerCity = $row["Cstmr_Cty"];
	$customerState = $row["Cstmr_Ste"];
	$customerZip = $row["Cstmr_Pstl"];
	$customerPhone = $row["Cstmr_Phn"];

	$customerSalesChannel = $row["Cntrct_Sales_Chnl"];
	$agreementDate = $row["Contract_Date"];
	$Vehicle_Gross_Weight = $row["Veh_Gross_Wgt_Cnt"];
	$Vehicle_Type = $row["Veh_Type_Nbr"];
	$Vehicle_Vin_Number = $row["Veh_Id_Nbr"];
	$Vehicle_Year = $row["Veh_Model_Yr_Cd"];
	$Vehicle_Make = $row["Veh_Mk_Cd"];
	$Vehicle_Model = $row["Veh_Model_Cd"];

	$Engine_Make = $row["Veh_Eng_Mk_Cd"];
	$Engine_Model = $row["Veh_Eng_Model_Cd"];
	$Engine_Serial = $row["Veh_Eng_Ser_nbr"];

	$Transmission_Make = $row["Veh_Trnsmsn_Mk_Cd"];
	$Transmission_Model = $row["Veh_Trnsmsn_Model_Cd"];
	$Transmission_Serial = $row["Veh_Trnsmsn_Ser_nbr"];

	$Odometer_Reading_Miles = $row["OdoMtr_Read_Miles_Cnt"];
	$Odometer_Reading_Km = $row["OdoMtr_Read_Kms_Cnt"];
	if ($Odometer_Reading_Miles != 0) {
		$Odometer_Miles_Or_KM = "Miles";
	} else {
		$Odometer_Miles_Or_KM = "KM";
	}

	$ECM_Reading_Miles = $row["ECM_Read_Miles_Cnt"];
	$ECM_Reading_Km = $row["ECM_Read_Kms_Cnt"];
	if ($ECM_Reading_Miles != 0) {
		$ECM_Miles_Or_KM = "Miles";
	} else {
		$ECM_Miles_Or_KM = "KM";
	}

	$APU_Engine_Make = $row["Veh_APU_Eng_Mk_Cd"];
	$APU_Engine_Model = $row["Veh_APU_Eng_Model_Cd"];
	$APU_Engine_Year = $row["Veh_APU_Eng_Yr_Cd"];
	$APU_Engine_Serial = $row["Veh_APU_Eng_Ser_nbr"];
	$APU_Flg = $row["APU_Flg"];

	$Vehicle_New_Flag = $row["Veh_New_Flg"];  // check value on this, coming back as 'k'?
	$Vehicle_Description = $row["Veh_Desc"];
	$Tier_Type = $row["Cntrct_Lvl_Cd"];
	$Tier_Type_Desc = $row["Cntrct_Lvl_Desc"];

	$Apparatus_Equipment_Package = $row["AEP_Flg"];
	$Aerial_Package = $row["Aerial_Flg"];
	$Coverage_Term = $row["Cntrct_Term_Mnths_Nbr"];
	$smallGoodsPackage = $row["Small_Goods_Pkg_Flg"];
	$Srvc_Veh_Flg = $row["Srvc_Veh_Flg"];

	$Supply_Packet_To_Be_Shipped = $row["Sply_Pkt_To_Be_Shipd_Flg"];
	$Supply_Packet_Left = $row["Sply_Pkt_Left_Flg"];
	$Supply_Packet_Shipped_Date = $row["Sply_Pkt_Shipd_Dte"];

	$Lien_Holder_Name = $row["Lien_Nme"];
	$Lien_Holder_Email = $row["Lien_Eml"];
	$Lien_Holder_Address = $row["Lien_Addrs"];
	$Lien_Holder_City = $row["Lien_Cty"];
	$Lien_Holder_State_Province = $row["Lien_Ste"];
	$Lien_Holder_Postal_Code = $row["Lien_Pstl"];
	$Lien_Holder_Phone_Number = $row["Lien_Phn"];

	$EVBC_Flg = $row["EVBC_Flg"];
	$EEC_Flg = $row["EEC_Flg"];
	//$warrantyStatus = $row["Warranty_Status"];



	$_SESSION["warrantyID"] = $warrantyID;

}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

	// Get form fields
	if (!empty(trim($_POST["warrantyID"]))) {
		if (is_numeric($_POST["warrantyID"])) {
			$warrantyID = trim($_POST["warrantyID"]);
		} else {
			$warrantyID = "";
		}
	}

	if (!empty(trim($_POST["Acct_ID"]))) {
		$Acct_ID = trim($_POST["Acct_ID"]);
	}

	if (isset($_POST["dealerAgentID"]) && !empty(trim($_POST["dealerAgentID"]))) {
		$dealerAgentID = trim($_POST["dealerAgentID"]);
	} else {
		$_SESSION["errorMessage"] = "ERROR: No dealer agent found, required to create a warranty.";
		header("location: create_warranty.php");
		die();
	}

	if (!empty(trim($_POST["agreementDate"]))) {
		$agreementDate = trim($_POST["agreementDate"]);
		$date = DateTime::createFromFormat('Y-m-d', $agreementDate);
		$agreementDate = $date->format('m-d-Y');
	}

	if (!empty(trim($_POST["customerName"]))) {
		$customerName = trim($_POST["customerName"]);
		$customerName = ucwords($customerName);
	}

	if (!empty(trim($_POST["customerEmail"]))) {
		$customerEmail = trim($_POST["customerEmail"]);
	}

	if (!empty(trim($_POST["customerAddress"]))) {
		$customerAddress = trim($_POST["customerAddress"]);
		$customerAddress = ucwords($customerAddress);
	}

	if (!empty(trim($_POST["customerCity"]))) {
		$customerCity = trim($_POST["customerCity"]);
		$customerCity = ucwords($customerCity);
	}

	if (!empty(trim($_POST["customerState"]))) {
		$customerState = trim($_POST["customerState"]);
		$customerState = ucwords($customerState);
	}

	if (!empty(trim($_POST["customerZip"]))) {
		$customerZip = trim($_POST["customerZip"]);
	}

	if (!empty(trim($_POST["customerPhone"]))) {
		$customerPhone = trim($_POST["customerPhone"]);
	}

	if (!empty(trim($_POST["customerSalesChannel"]))) {
		$customerSalesChannel = trim($_POST["customerSalesChannel"]);
	}

	if (isset($_POST["smallGoodsPackage"]) && !empty(trim($_POST["smallGoodsPackage"]))) {
		$smallGoodsPackage = trim($_POST["smallGoodsPackage"]);
	}else{
		$smallGoodsPackage = "N";
	}

	if (!empty(trim($_POST["Srvc_Veh_Flg"]))) {
		$Srvc_Veh_Flg = trim($_POST["Srvc_Veh_Flg"]);
	}

	/*
    if(!empty(trim($_POST["vehicleManufacturerName"]))){
        $Vehicle_Manufacturer_Name = trim($_POST["vehicleManufacturerName"]);
    }
	 */

	if (!empty(trim($_POST["vehicleGrossWeight"]))) {
		$Vehicle_Gross_Weight = trim($_POST["vehicleGrossWeight"]);
	}

	// NOTE: May want to save these values differently
	if (!empty(trim($_POST["vehicleGrossWeight"]))) {
		$Vehicle_Gross_Weight = trim($_POST["vehicleGrossWeight"]);
		if ($Vehicle_Gross_Weight == "type 1") {
			$Vehicle_Type = 1;
		} else if ($Vehicle_Gross_Weight == "type 2") {
			$Vehicle_Type = 2;
		} else if ($Vehicle_Gross_Weight == "type 3") {
			$Vehicle_Type = 3;
		} else {
			// NOTE: what to do in case of default?
			$Vehicle_Type = 1;
		}
	}


	if (!empty(trim($_POST["vehicleVIN"]))) {
		$Vehicle_Vin_Number = trim($_POST["vehicleVIN"]);
	}

	if (!empty(trim($_POST["vehicleYear"]))) {
		$Vehicle_Year = trim($_POST["vehicleYear"]);
	}

	if (!empty(trim($_POST["vehicleMake"]))) {
		$Vehicle_Make = trim($_POST["vehicleMake"]);
	}

	if (!empty(trim($_POST["vehicleModel"]))) {
		$Vehicle_Model = trim($_POST["vehicleModel"]);
	}

	if (!empty(trim($_POST["engineMake"]))) {
		$Engine_Make = trim($_POST["engineMake"]);
	}

	if (!empty(trim($_POST["engineModel"]))) {
		$Engine_Model = trim($_POST["engineModel"]);
	}

	if (!empty(trim($_POST["engineSerialNumber"]))) {
		$Engine_Serial = trim($_POST["engineSerialNumber"]);
	}

	if (!empty(trim($_POST["transmissionMake"]))) {
		$Transmission_Make = trim($_POST["transmissionMake"]);
	}

	if (!empty(trim($_POST["transmissionModel"]))) {
		$Transmission_Model = trim($_POST["transmissionModel"]);
	}

	if (!empty(trim($_POST["transmissionSerialNumber"]))) {
		$Transmission_Serial = trim($_POST["transmissionSerialNumber"]);
	}

	if (!empty(trim($_POST["odometerReading"]))) {
		$Odometer_Reading = trim($_POST["odometerReading"]);
	}

	if (isset($_POST["milesOrKM"]) && !empty(trim($_POST["milesOrKM"]))) {
		$Odometer_Miles_Or_KM = trim($_POST["milesOrKM"]);
	}

	if (!empty(trim($_POST["ecmReading"]))) {
		$ECM_Reading = trim($_POST["ecmReading"]);
	}

	if (isset($_POST["ecmMilesOrKM"]) && !empty(trim($_POST["ecmMilesOrKM"]))) {
		$ECM_Miles_Or_KM = trim($_POST["ecmMilesOrKM"]);
	}

	if (isset($_POST["isAPU"]) && !empty(trim($_POST["isAPU"]))) {
		$APU_Flg = trim($_POST["isAPU"]);
	}else{
		$APU_Flg = "N";
	}

	if (!empty(trim($_POST["apuMake"]))) {
		$APU_Engine_Make = trim($_POST["apuMake"]);
	}

	if (!empty(trim($_POST["apuModel"]))) {
		$APU_Engine_Model = trim($_POST["apuModel"]);
	}

	if (!empty(trim($_POST["apuYear"]))) {
		$APU_Engine_Year = trim($_POST["apuYear"]);
	}

	if (!empty(trim($_POST["apuSerialNumber"]))) {
		$APU_Engine_Serial = trim($_POST["apuSerialNumber"]);
	}

	if (isset($_POST["isVehicleNew"]) && !empty(trim($_POST["isVehicleNew"]))) {
		$Vehicle_New_Flag = trim($_POST["isVehicleNew"]);
	}

	if (!empty(trim($_POST["vehicleDescription"]))) {
		$Vehicle_Description = trim($_POST["vehicleDescription"]);
	}

	if (!empty(trim($_POST["vehicleTierType"]))) {
		$Tier_Type = trim($_POST["vehicleTierType"]);
	}

	if (isset($_POST["boltOnPackage"]) && !empty(trim($_POST["boltOnPackage"]))) {
		$Apparatus_Equipment_Package = trim($_POST["boltOnPackage"]);
	}else{
		$Apparatus_Equipment_Package = "N";
	}

	if (isset($_POST["aerialPackage"]) && !empty(trim($_POST["aerialPackage"]))) {
		$Aerial_Package = trim($_POST["aerialPackage"]);
	}else{
		$Aerial_Package = "N";
	}

	if (!empty(trim($_POST["coverageTerm"]))) {
		$Coverage_Term = trim($_POST["coverageTerm"]);
	}

	if (isset($_POST["supplyPacketToBeShipped"]) && !empty(trim($_POST["supplyPacketToBeShipped"]))) {
		$Supply_Packet_To_Be_Shipped = trim($_POST["supplyPacketToBeShipped"]);
	}

	if (isset($_POST["supplyPacketLeft"]) && !empty(trim($_POST["supplyPacketLeft"]))) {
		$Supply_Packet_Left = trim($_POST["supplyPacketLeft"]);
	}

	if (!empty(trim($_POST["supplyPacketShippedDate"]))) {
		$Supply_Packet_Shipped_Date = trim($_POST["supplyPacketShippedDate"]);
	}

	if (!empty(trim($_POST["lienHolderName"]))) {
		$Lien_Holder_Name = trim($_POST["lienHolderName"]);
		$Lien_Holder_Name = ucwords($Lien_Holder_Name);
	}

	if (!empty(trim($_POST["lienHolderEmail"]))) {
		$Lien_Holder_Email = trim($_POST["lienHolderEmail"]);
	}

	if (!empty(trim($_POST["lienHolderAddress"]))) {
		$Lien_Holder_Address = trim($_POST["lienHolderAddress"]);
		$Lien_Holder_Address = ucwords($Lien_Holder_Address);
	}

	if (!empty(trim($_POST["lienHolderCity"]))) {
		$Lien_Holder_City = trim($_POST["lienHolderCity"]);
		$Lien_Holder_City = ucwords($Lien_Holder_City);
	}

	if (!empty(trim($_POST["lienHolderState"]))) {
		$Lien_Holder_State_Province = trim($_POST["lienHolderState"]);
		$Lien_Holder_State_Province = ucwords($Lien_Holder_State_Province);
	}

	if (!empty(trim($_POST["lienHolderZip"]))) {
		$Lien_Holder_Postal_Code = trim($_POST["lienHolderZip"]);
	}

	if (!empty(trim($_POST["lienHolderPhone"]))) {
		$Lien_Holder_Phone_Number = trim($_POST["lienHolderPhone"]);
	}

	if (!empty(trim($_POST["isQuote"]))) {
		$isQuote = trim($_POST["isQuote"]);
	}

	if (isset($_POST["isEVBC"]) && !empty(trim($_POST["isEVBC"]))) {
		$EVBC_Flg = trim($_POST["isEVBC"]);
	}else{
		$EVBC_Flg = "N";
	}
	
	if (isset($_POST["isEEC"]) && !empty(trim($_POST["isEEC"]))) {
		$EEC_Flg = trim($_POST["isEEC"]);
	}else{
		$EEC_Flg = "N";
	}

    /*
    $Dealer_Signature = "";
    $Dealer_Signature_Name = "";
    $Dealer_Signature_Date = "";
    $Customer_Signature = "";
    $Customer_Signature_Name = "";
    $Customer_Signature_Date = "";
	 */





	// If we got a warrantyID from the form, we are updating, otherwise create new.
	if ($warrantyID != "") {
		/******** UPDATE WARRANTY DETAILS IN PROPER TABLES ********/

		$contract_ID = $warrantyID;

		/* Prepare an UPDATE statement to update a Warranty entry */
		//$sqlString = "UPDATE New_Warranty_Temp SET Customer_Name=?,Customer_Email=? WHERE Acct_ID=? AND New_Warranty_Temp_ID=?";
		//$stmt = mysqli_prepare($link, $sqlString);
		/* Bind variables to parameters */
		//$val1 = $customerName;
		//$val2 = $customerEmail;
		//$val3 = $dealerID;
		//$val4 = $warrantyID;
		//mysqli_stmt_bind_param($stmt, "ssii", $val1, $val2, $val3, $val4);
		/* Execute the statement */
		//$result = mysqli_stmt_execute($stmt);


		// Get the Cntrct_Dim_ID and Veh_ID from the Cntrct table
		$query = "SELECT Cntrct_Dim_ID, Veh_ID FROM Cntrct WHERE Cntrct_ID=" . $warrantyID . ";";
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$Cntrct_Dim_ID = $row["Cntrct_Dim_ID"];
		$Veh_ID = $row["Veh_ID"];



		/* Prepare an UPDATE statement to update a Cntrct_Dim entry for this Warranty */
		$stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim SET Cstmr_Nme=?,Contract_Date=?,Sply_Pkt_To_Be_Shipd_Flg=?,
		                               Sply_Pkt_Left_Flg=?,Cntrct_Lvl_Cd=?,Cntrct_Lvl_Desc=?,
									   AEP_Flg=?,Aerial_Flg=?,APU_Flg=?,Small_Goods_Pkg_Flg=?,Cntrct_Term_Mnths_Nbr=?,
									   Cstmr_Eml=?,Cstmr_Addrs=?,Cstmr_Cty=?,Cstmr_Ste=?,Cstmr_Pstl=?,Cstmr_Phn=?,
									   Lien_Nme=?,Lien_Eml=?,Lien_Addrs=?,Lien_Cty=?,Lien_Ste=?,Lien_Pstl=?,Lien_Phn=?,
									   Srvc_Veh_Flg=? WHERE
									   Cntrct_Dim_ID=?");

		// Data processing
		if ($Tier_Type == "S") {
			$Tier_Type_Desc = "Squad";
		} else if ($Tier_Type == "B") {
			$Tier_Type_Desc = "Battalion";
		} else {
			$Tier_Type_Desc = "ERROR";
		}

		$val1 = $customerName;
		$val2 = date('Y-m-d', strtotime($agreementDate));
		$val3 = $Supply_Packet_To_Be_Shipped;
		$val4 = $Supply_Packet_Left;
		$val5 = $Tier_Type;
		$val6 = $Tier_Type_Desc;
		$val7 = $Apparatus_Equipment_Package;
		$val8 = $Aerial_Package;
		$val9 = $APU_Flg;
		$val10 = $smallGoodsPackage;
		$val11 = $Coverage_Term;
		$val12 = $customerEmail;
		$val13 = $customerAddress;
		$val14 = $customerCity;
		$val15 = $customerState;
		$val16 = $customerZip;
		$val17 = $customerPhone;
		$val18 = $Lien_Holder_Name;
		$val19 = $Lien_Holder_Email;
		$val20 = $Lien_Holder_Address;
		$val21 = $Lien_Holder_City;
		$val22 = $Lien_Holder_State_Province;
		$val23 = $Lien_Holder_Postal_Code;
		$val24 = $Lien_Holder_Phone_Number;
		$val25 = $Srvc_Veh_Flg;
		$val26 = $Cntrct_Dim_ID;

		mysqli_stmt_bind_param($stmt, "ssssssssssisssissssssisssi", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22, $val23, $val24, $val25,$val26);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


		/* Prepare an UPDATE statement to update a Veh entry for this Warranty */
		$stmt = mysqli_prepare($link, "UPDATE Veh SET Veh_Mk_Cd=?,Veh_Model_Cd=?,Veh_Model_Yr_Cd=?,
									   Veh_Eng_Mk_CD=?,veh_Eng_Model_Cd=?,Veh_Eng_Ser_Nbr=?,
									   Veh_Gross_Wgt_Cnt=?,Veh_Type_Nbr=?,Veh_New_Flg=?,
									   Veh_Trnsmsn_Ser_nbr=?,Veh_Trnsmsn_Mk_Cd=?,Veh_Trnsmsn_Model_Cd=?,
									   Veh_APU_Eng_Ser_nbr=?,Veh_APU_Eng_Mk_Cd=?,Veh_APU_Eng_Model_Cd=?,Veh_APU_Eng_Yr_Cd=?,
									   OdoMtr_Read_Miles_Cnt=?,OdoMtr_Read_Kms_Cnt=?,ECM_Read_Miles_Cnt=?,ECM_Read_Kms_Cnt=?,Veh_Desc=?,
									   Veh_Id_Nbr=? WHERE Veh_ID=?");

		// Data Prep
		if ($Odometer_Miles_Or_KM == "km") {
			$OdoMtr_Read_Miles_Cnt = 0;
			$OdoMtr_Read_Kms_Cnt = $Odometer_Reading;
		} else {
			$OdoMtr_Read_Miles_Cnt = $Odometer_Reading;
			$OdoMtr_Read_Kms_Cnt = 0;
		}

		if ($ECM_Miles_Or_KM == "km") {
			$ECM_Read_Miles_Cnt = 0;
			$ECM_Read_Kms_Cnt = $ECM_Reading;
		} else {
			$ECM_Read_Miles_Cnt = $ECM_Reading;
			$ECM_Read_Kms_Cnt = 0;
		}


		// Data processing

		$val1 = $Vehicle_Make;
		$val2 = $Vehicle_Model;
		$val3 = $Vehicle_Year;
		$val4 = $Engine_Make;
		$val5 = $Engine_Model;
		$val6 = $Engine_Serial;
		$val7 = $Vehicle_Gross_Weight;
		$val8 = $Vehicle_Type;
		$val9 = $Vehicle_New_Flag;
		$val10 = $Transmission_Serial;
		$val11 = $Transmission_Make;
		$val12 = $Transmission_Model;
		$val13 = $APU_Engine_Serial;
		$val14 = $APU_Engine_Make;
		$val15 = $APU_Engine_Model;
		$val16 = $APU_Engine_Year;
		$val17 = $OdoMtr_Read_Miles_Cnt;
		$val18 = $OdoMtr_Read_Kms_Cnt;
		$val19 = $ECM_Read_Miles_Cnt;
		$val20 = $ECM_Read_Kms_Cnt;
		$val21 = $Vehicle_Description;
		$val22 = $Vehicle_Vin_Number;
		$val23 = $Veh_ID;

		mysqli_stmt_bind_param($stmt, "sssssssissssssssssssssi", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22, $val23);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


		/* Prepare an UPDATE statement to update a Cntrct entry for this Warranty */
		$stmt = mysqli_prepare($link, "UPDATE Cntrct SET Cntrct_Sales_Chnl=?,Sply_Pkt_Shipd_Dte=? WHERE Cntrct_ID=?");

		$val1 = $customerSalesChannel;
		$val2 = $Supply_Packet_Shipped_Date;
		$val3 = $warrantyID;

		mysqli_stmt_bind_param($stmt, "ssi", $val1, $val2, $val3);

		/*
		echo "customerSalesChannel=".$customerSalesChannel;
		echo "<br />Supply_Packet_Shipped_Date=".$Supply_Packet_Shipped_Date;
		echo "<br />warrantyID=".$warrantyID;
		die();
		*/

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);



	 } else {

		//die($_POST["customerEmail"]);
		/* Prepare an insert statement to create a Warranty entry */
		$sqlString = "INSERT INTO New_Warranty_Temp (Acct_ID,Customer_Name,Customer_Email,Customer_Address,";
		$sqlString .= "Customer_City,Customer_State,Customer_Zip,Customer_Phone,Customer_Sales_Channel,Contract_Number,";
		$sqlString .= "Agreement_Date,Vehicle_Manufacturer_Name,Vehicle_Gross_Weight,Vehicle_Type,Vehicle_Vin_Number,Vehicle_Year,";
		$sqlString .= "Vehicle_Make,Vehicle_Model,Engine_Make,Engine_Model,Engine_Serial,Transmission_Make,";
		$sqlString .= "Transmission_Model,Transmission_Serial,Odometer_Reading,Odometer_Miles_Or_KM,ECM_Reading,";
		$sqlString .= "ECM_Miles_Or_KM,APU_Engine_Make,APU_Engine_Model,APU_Engine_Year,APU_Engine_Serial,";
		$sqlString .= "Vehicle_New_Flag,Vehicle_Description,Tier_Type,Apparatus_Equipment_Package,Aerial_Package,Electric_Vehicle_Battery_Coverage,Enhanced_Engine_Coverage,";
		$sqlString .= "Coverage_Term,Small_Goods_Package,Supply_Packet_To_Be_Shipped,Supply_Packet_Left,";
		$sqlString .= "Supply_Packet_Shipped_Date,Lien_Holder_Name,Lien_Holder_Email,Lien_Holder_Address,";
		$sqlString .= "Lien_Holder_City,Lien_Holder_State_Province,Lien_Holder_Postal_Code,Lien_Holder_Phone_Number,";
		$sqlString .= "Dealer_Signature,Dealer_Signature_Name,Dealer_Signature_Date,Customer_Signature,";
		$sqlString .= "Customer_Signature_Name,Customer_Signature_Date,";
		$sqlString .= "Warranty_Status,Created_Date) values ";
		$sqlString .= "(?,?,?,?,?,?,?,?,?,'0',"; // up to Contract_Number
		$sqlString .= "?,?,?,?,?,?,?,?,?,?,?,?,"; // up to Transmission_Make
		$sqlString .= "?,?,?,?,?,?,?,?,?,?,"; // up to APU_Engine_Serial
		$sqlString .= "?,?,?,?,?,?,?,?,?,?,?,"; // up to Supply_Packet_Left
		$sqlString .= "?,?,?,?,?,?,?,?,"; // up to Lien_Holder_Phone_Number
		$sqlString .= "?,?,?,?,?,?,"; // up to Customer_Signature
		$sqlString .= "'draft',NOW())";

		$stmt = mysqli_prepare($link, $sqlString);

		/* Bind variables to parameters */
		$val1 = $dealerID;
		$val2 = $customerName;
		$val3 = $customerEmail;
		$val4 = $customerAddress;
		$val5 = $customerCity;
		$val6 = $customerState;
		$val7 = $customerZip;
		$val8 = $customerPhone;
		$val9 = $customerSalesChannel;
		$val10 = $agreementDate;
		$val11 = $Vehicle_Manufacturer_Name;
		$val12 = $Vehicle_Gross_Weight;
		$val13 = $Vehicle_Type;
		$val14 = $Vehicle_Vin_Number;
		$val15 = $Vehicle_Year;  //int
		$val16 = $Vehicle_Make;
		$val17 = $Vehicle_Model;
		$val18 = $Engine_Make;
		$val19 = $Engine_Model;
		$val20 = $Engine_Serial;
		$val21 = $Transmission_Make;
		$val22 = $Transmission_Model;
		$val23 = $Transmission_Serial;
		$val24 = $Odometer_Reading;  //int
		$val25 = $Odometer_Miles_Or_KM;
		$val26 = $ECM_Reading;  //int
		$val27 = $ECM_Miles_Or_KM;
		$val28 = $APU_Engine_Make;
		$val29 = $APU_Engine_Model;
		$val30 = $APU_Engine_Year;  //int
		$val31 = $APU_Engine_Serial;
		$val32 = $Vehicle_New_Flag;
		$val33 = $Vehicle_Description;
		$val34 = $Tier_Type;
		$val35 = $Apparatus_Equipment_Package;
		$val36 = $Aerial_Package;
		$val37 = $Coverage_Term;  //int
		$val38 = $smallGoodsPackage;
		$val39 = $Supply_Packet_To_Be_Shipped;
		$val40 = $Supply_Packet_Left;
		$val41 = $Supply_Packet_Shipped_Date;
		$val42 = $Lien_Holder_Name;
		$val43 = $Lien_Holder_Email;
		$val44 = $Lien_Holder_Address;
		$val45 = $Lien_Holder_City;
		$val46 = $Lien_Holder_State_Province;
		$val47 = $Lien_Holder_Postal_Code;
		$val48 = $Lien_Holder_Phone_Number;
		$val49 = $Dealer_Signature;
		$val50 = $Dealer_Signature_Name;
		$val51 = $Dealer_Signature_Date;
		$val52 = $Customer_Signature;
		$val53 = $Customer_Signature_Name;
		$val54 = $Customer_Signature_Date;
		$val55 = $EVBC_Flg;
		$val56 = $EEC_Flg;
        //15,24,26,30,37,
        /*
        Vehicle_Manufacturer_Name
        Vehicle_Gross_Weight
        Vehicle_Type
        Vehicle_Vin_Number
        Vehicle_Year
        Vehicle_Make
        Vehicle_Model
        Engine_Make
        Engine_Model
        Engine_Serial
        Transmission_Make
        Transmission_Model
        Transmission_Serial
        Odometer_Reading
        Odometer_Miles_Or_KM
        ECM_Reading
        ECM_Miles_Or_KM
        APU_Engine_Make
        APU_Engine_Model
        APU_Engine_Year
        APU_Engine_Serial
        Vehicle_New_Flag
        Vehicle_Description
        Tier_Type
        Apparatus_Equipment_Package
        Aerial_Package
        Coverage_Term
        Small_Goods_Package
        Supply_Packet_To_Be_Shipped
        Supply_Packet_Left
        Supply_Packet_Shipped_Date
        Lien_Holder_Name
        Lien_Holder_Email
        Lien_Holder_Address
        Lien_Holder_City
        Lien_Holder_State_Province
        Lien_Holder_Postal_Code
        Lien_Holder_Phone_Number
        Dealer_Signature
        Dealer_Signature_Name
        Dealer_Signature_Date
        Customer_Signature
        Customer_Signature_Name
        Customer_Signature_Date


		 */


		mysqli_stmt_bind_param($stmt, "isssssssssssssissssssssisisssissssssssisssssssssssssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22, $val23, $val24, $val25, $val26, $val27, $val28, $val29, $val30, $val31, $val32, $val33, $val34, $val35, $val36, $val55, $val56, $val37, $val38, $val39, $val40, $val41, $val42, $val43, $val44, $val45, $val46, $val47, $val48, $val49, $val50, $val51, $val52, $val53, $val54);


		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the newly inserted PK ID
		if ($result) {
			$last_id = mysqli_insert_id($link);
		}



		/******** INSERT WARRANTY DETAILS INTO PROPER TABLES ********/

		/* Prepare an insert statement to create a Cntrct_Dim entry for this new Warranty */
		$stmt = mysqli_prepare($link, "INSERT INTO Cntrct_Dim (Cntrct_type_cd,Cntrct_type_desc,Qte_Flg,Cstmr_Nme,
									   Contract_Date,Sply_Pkt_To_Be_Shipd_Flg,Sply_Pkt_Left_Flg,Cntrct_Lvl_Cd,Cntrct_Lvl_Desc,
									   AEP_Flg,Aerial_Flg,APU_Flg,Small_Goods_Pkg_Flg,Cntrct_Term_Mnths_Nbr,
									   Cstmr_Eml,Cstmr_Addrs,Cstmr_Cty,Cstmr_Ste,Cstmr_Pstl,Cstmr_Phn,
									   Lien_Nme,Lien_Eml,Lien_Addrs,Lien_Cty,Lien_Ste,Lien_Pstl,Lien_Phn,Srvc_Veh_Flg,
									   Created_Date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");

		// Data processing
		if ($Tier_Type == "S") {
			$Tier_Type_Desc = "Squad";
		} else if ($Tier_Type == "B") {
			$Tier_Type_Desc = "Battalion";
		} else {
			$Tier_Type_Desc = "ERROR";
		}

		// Set quote or draft flags accordingly
		if ($isQuote == "Y") {
			$val1 = "WQ";
			$val2 = "Warranty Quote";
			$val3 = "Y";
			$val4 = $customerName;
			$val5 = date('Y-m-d', strtotime($agreementDate));
			$val6 = $Supply_Packet_To_Be_Shipped;
			$val7 = $Supply_Packet_Left;
			$val8 = $Tier_Type;
			$val9 = $Tier_Type_Desc;
			$val10 = $Apparatus_Equipment_Package;
			$val11 = $Aerial_Package;
			$val12 = $APU_Flg;
			$val13 = $smallGoodsPackage;
			$val14 = $Coverage_Term;
		} else {
			$val1 = "WD";
			$val2 = "Warranty";
			$val3 = "N";
			$val4 = $customerName;
			$val5 = date('Y-m-d', strtotime($agreementDate));
			$val6 = $Supply_Packet_To_Be_Shipped;
			$val7 = $Supply_Packet_Left;
			$val8 = $Tier_Type;
			$val9 = $Tier_Type_Desc;
			$val10 = $Apparatus_Equipment_Package;
			$val11 = $Aerial_Package;
			$val12 = $APU_Flg;
			$val13 = $smallGoodsPackage;
			$val14 = $Coverage_Term;
		}

		$val15 = $customerEmail;
		$val16 = $customerAddress;
		$val17 = $customerCity;
		$val18 = $customerState;
		$val19 = $customerZip;
		$val20 = $customerPhone;
		$val21 = $Lien_Holder_Name;
		$val22 = $Lien_Holder_Email;
		$val23 = $Lien_Holder_Address;
		$val24 = $Lien_Holder_City;
		$val25 = $Lien_Holder_State_Province;
		$val26 = $Lien_Holder_Postal_Code;
		$val27 = $Lien_Holder_Phone_Number;
		$val28 = $Srvc_Veh_Flg;

		mysqli_stmt_bind_param($stmt, "sssssssssssssisssissssssisss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22, $val23, $val24, $val25, $val26, $val27, $val28);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the Contract_Dim_ID of the new contract dim entry.
		$contract_dim_ID = mysqli_insert_id($link);


		/* Prepare an insert statement to create a Veh entry for this new Warranty */
		$stmt = mysqli_prepare($link, "INSERT INTO Veh (Veh_Mk_Cd,Veh_Model_Cd,Veh_Model_Yr_Cd,
									   Veh_Eng_Mk_CD,veh_Eng_Model_Cd,Veh_Eng_Ser_Nbr,
									   Veh_Gross_Wgt_Cnt,Veh_Type_Nbr,Veh_New_Flg,
									   Veh_Trnsmsn_Ser_nbr,Veh_Trnsmsn_Mk_Cd,Veh_Trnsmsn_Model_Cd,
									   Veh_APU_Eng_Ser_nbr,Veh_APU_Eng_Mk_Cd,Veh_APU_Eng_Model_Cd,Veh_APU_Eng_Yr_Cd,
									   OdoMtr_Read_Miles_Cnt,OdoMtr_Read_Kms_Cnt,ECM_Read_Miles_Cnt,ECM_Read_Kms_Cnt,Veh_Desc,
									   Veh_Id_Nbr)
									   VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

		// Data Prep
		if ($Odometer_Miles_Or_KM == "km") {
			$OdoMtr_Read_Miles_Cnt = 0;
			$OdoMtr_Read_Kms_Cnt = $Odometer_Reading;
		} else {
			$OdoMtr_Read_Miles_Cnt = $Odometer_Reading;
			$OdoMtr_Read_Kms_Cnt = 0;
		}

		if ($ECM_Miles_Or_KM == "km") {
			$ECM_Read_Miles_Cnt = 0;
			$ECM_Read_Kms_Cnt = $ECM_Reading;
		} else {
			$ECM_Read_Miles_Cnt = $ECM_Reading;
			$ECM_Read_Kms_Cnt = 0;
		}


		// Data processing

		$val1 = $Vehicle_Make;
		$val2 = $Vehicle_Model;
		$val3 = $Vehicle_Year;
		$val4 = $Engine_Make;
		$val5 = $Engine_Model;
		$val6 = $Engine_Serial;
		$val7 = $Vehicle_Gross_Weight;
		$val8 = $Vehicle_Type;
		$val9 = $Vehicle_New_Flag;
		$val10 = $Transmission_Serial;
		$val11 = $Transmission_Make;
		$val12 = $Transmission_Model;
		$val13 = $APU_Engine_Serial;
		$val14 = $APU_Engine_Make;
		$val15 = $APU_Engine_Model;
		$val16 = $APU_Engine_Year;
		$val17 = $OdoMtr_Read_Miles_Cnt;
		$val18 = $OdoMtr_Read_Kms_Cnt;
		$val19 = $ECM_Read_Miles_Cnt;
		$val20 = $ECM_Read_Kms_Cnt;
		$val21 = $Vehicle_Description;
		$val22 = $Vehicle_Vin_Number;

		mysqli_stmt_bind_param($stmt, "sssssssissssssssssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the Contract_ID of the new contract dim entry.
		$veh_ID = mysqli_insert_id($link);

		/* Prepare an insert statement to create a Cntrct entry for this new Warranty */
		$stmt = mysqli_prepare($link, "INSERT INTO Cntrct (Cntrct_Nbr,Cntrct_Sales_Chnl,Sply_Pkt_Shipd_Dte,
									   Cntrct_Dim_ID,Veh_ID,Mfr_Acct_ID,Created_Date) VALUES (?,?,?,?,?,?,NOW())");

		// Data processing
		// Set quote or draft flags accordingly
		if ($isQuote == "Y") {
			$val1 = "";
			$val2 = $customerSalesChannel;
			$val3 = $Supply_Packet_Shipped_Date;
			$val4 = $contract_dim_ID;
			$val5 = $veh_ID;
			$val6 = $Acct_ID;
		} else {
			$val1 = "";
			$val2 = $customerSalesChannel;
			$val3 = $Supply_Packet_Shipped_Date;
			$val4 = $contract_dim_ID;
			$val5 = $veh_ID;
			$val6 = $Acct_ID;
		}

		mysqli_stmt_bind_param($stmt, "sssiii", $val1, $val2, $val3, $val4, $val5, $val6);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the Contract_ID of the new contract dim entry.
		$contract_ID = mysqli_insert_id($link);

		// Put the contractID into session that we just created, which is how
		//  the Small Goods worksheet will function.
		$_SESSION["warrantyID"] = $contract_ID;


	} // if($warranty!="") //


	/**** BUSINESS LOGIC CALCULATIONS ****/

	// Look up the base values from Wrnty_Std_Prcg based on term, type and tier
	$warrantyBasePricingResult = selectWarrantyBasePricing($link, $Coverage_Term, $Vehicle_Type, $Tier_Type);
	$row = mysqli_fetch_assoc($warrantyBasePricingResult);
	$Sales_Agt_Cost_Amt = $row["Sales_Agt_Cost_Amt"];
	$Sales_Agt_Commission_Amt = $row["Sales_Agt_Commission_Amt"];
	$Dlr_Cost_Amt = $row["Dlr_Cost_Amt"];
	$Dlr_Mrkp_Max_Amt = $row["Dlr_Mrkp_Max_Amt"];
	$Dlr_Mrkp_Actl_Amt = $row["Dlr_Mrkp_Max_Amt"];
	$MSRP_Amt = $row["MSRP_Amt"];

	// Update the contract with these values
	$stmt = mysqli_prepare($link, "UPDATE Cntrct SET Sales_Agt_Cost_Amt=?, Sales_Agt_Commission_Amt=?,
	                               Dlr_Cost_Amt=?, Dlr_Mrkp_Max_Amt=?, Dlr_Mrkp_Actl_Amt=?, MSRP_Amt=? WHERE Cntrct_ID=?");

	$val1 = $Sales_Agt_Cost_Amt;
	$val2 = $Sales_Agt_Commission_Amt;
	$val3 = $Dlr_Cost_Amt;
	$val4 = $Dlr_Mrkp_Max_Amt;
	$val5 = $Dlr_Mrkp_Actl_Amt;
	$val6 = $MSRP_Amt;
	$val7 = $contract_ID;

	mysqli_stmt_bind_param($stmt, "iiiiiii", $val1, $val2, $val3, $val4, $val5, $val6, $val7);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);


	// Look up the Additional Standard Pricing values
	$isAEP = "N";
	$isAPU = "N";
	$isAER = "N";
	$isOLD = "N";
	$isEVBC = "N";
	$isEEC = "N";

	if ($APU_Flg == "Y") {
		$isAPU = "Y";
	}
	if ($Apparatus_Equipment_Package == "Y") {
		$isAEP = "Y";
	}
	if ($Aerial_Package == "Y") {
		$isAER = "Y";
	}

	// If the vehicle is over 15 years old, consider it OLD
	if (is_numeric($Vehicle_Year)) {
		if ((date("Y") - $Vehicle_Year) > 15) {
			$isOLD = "Y";
		}
	} else {
		$isOLD = "N";
	}

	if ($EVBC_Flg == "Y") {
		$isEVBC = "Y";
	}
	
	if ($EEC_Flg == "Y") {
		$isEEC = "Y";
	}

	$addlStdPrcgResult = selectAddlStdPrcgSum($link, $isAEP, $isAPU, $isAER, $isOLD, $isEVBC, $isEEC);
	if($addlStdPrcgResult)
	{
		$row = mysqli_fetch_assoc($addlStdPrcgResult);
		$Sales_Agt_Cost_Amt = $row["Addl_Sales_Agt_Cost_Amt"];
		$Sales_Agt_Commission_Amt = $row["Addl_Sales_Agt_Commission_Amt"];
		$Dlr_Cost_Amt = $row["Addl_Dlr_Cost_Amt"];
		$Dlr_Mrkp_Max_Amt = $row["Addl_Dlr_Mrkp_Max_Amt"];
		$Dlr_Mrkp_Actl_Amt = $row["Addl_Dlr_Mrkp_Max_Amt"];
		$MSRP_Amt = $row["Addl_MSRP_Amt"];
	
		// Update the contract with these values
		$stmt = mysqli_prepare($link, "UPDATE Cntrct SET Addl_Sales_Agt_Cost_Amt=?, Addl_Sales_Agt_Commission_Amt=?,
									   Addl_Dlr_Cost_Amt=?, Addl_Dlr_Mrkp_Max_Amt=?, Addl_Dlr_Mrkp_Actl_Amt=?,
									   Addl_MSRP_Amt=? WHERE Cntrct_ID=?");
	
		$val1 = $Sales_Agt_Cost_Amt;
		$val2 = $Sales_Agt_Commission_Amt;
		$val3 = $Dlr_Cost_Amt;
		$val4 = $Dlr_Mrkp_Max_Amt;
		$val5 = $Dlr_Mrkp_Actl_Amt;
		$val6 = $MSRP_Amt;
		$val7 = $contract_ID;
	
		mysqli_stmt_bind_param($stmt, "iiiiiii", $val1, $val2, $val3, $val4, $val5, $val6, $val7);
	
		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);
	
		// Small goods values will be populated into the contract after
		//  the Small Goods process is complete, in the next step below
	
	
		// Call our function to updated the TOTALS columns in the Cntrct table, which is the sum
		//  of base + add-on + small goods.  Need to refresh these totals whenever changes are made
		$totalUpdateResult = updateWarrantyTotals($link,$contract_ID);

	}

 // Genertae PDF	// Start PDF Code here....
	$generatePdf = true;


	// Get the dealer info
	$query = "SELECT * FROM Acct WHERE Acct_ID=" . $dealerID . ";";
	$result = $link->query($query);
	if ($result) {
		$row = $result->fetch_assoc();
		$dealerName = $row["Acct_Nm"];
	} else {
		$dealerName = "";
		$generatePdf = false;
	}


	// Get the dealer address info
	$query = "SELECT * FROM Addr WHERE Acct_ID=" . $dealerID . " AND Addr_Type_Cd='Work';";
	$result = $link->query($query);
	if ($result) {
		$row = $result->fetch_assoc();

		$dealerAddress1 = $row["St_Addr_1_Desc"];
		$dealerAddress2 = $row["St_Addr_2_Desc"];
		$dealerCity = $row["City_Nm"];
		$dealerState = $row["St_Prov_ID"];
		$dealerZip = $row["Pstl_Cd"];
	} else {
		$dealerAddress1 = "";
		$dealerAddress2 = "";
		$dealerCity = "";
		$dealerState = "";
		$dealerZip = "";
		$generatePdf = false;
	}
	$customerStateResult = selectStates($link);
	$lienStateResult = selectStates($link);


	// Get the dealer phone
	$phoneResult = selectTelByAcct($link, $dealerID, "Y", "Work");
	if ($phoneResult) {
		$row = $phoneResult->fetch_assoc();
		$dealerPhone = $row["Tel_Nbr"];
	} else {
		$dealerPhone = "";
		$generatePdf = false;
	}

	if ($generatePdf) {

		// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	  // set document information
		$pdf->setCreator(PDF_CREATOR);
		$pdf->setAuthor('W9');
		$pdf->setTitle('Vital Trends - Quote');
		$pdf->setSubject('Vital Trends - Quote');
		$pdf->setKeywords('W9, Vital, Data, Set, Guide, Quote');

	  // set default header data
	 //$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
		$pdf->setPrintHeader(false);
   //$pdf->setFooterData(array(0,64,0), array(0,64,128));

  // set header and footer fonts
		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

 // set default monospaced font
		$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

 // set margins
		$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP - 15, PDF_MARGIN_RIGHT);
		$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

 // set auto page breaks
		$pdf->setAutoPageBreak(true, PDF_MARGIN_BOTTOM);

 // set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

 // set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
			require_once(dirname(__FILE__) . '/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
 // ---------------------------------------------------------

 // set default font subsetting mode
		$pdf->setFontSubsetting(true);

	// Set font
	// dejavusans is a UTF-8 Unicode font, if you only need to
	// print standard ASCII chars, you can use core fonts like
	// helvetica or times to reduce file size.
	//$pdf->setFont('dejavusans', '', 14, '', true);
		$cambriabF = TCPDF_FONTS::addTTFfont('tcpdf/fonts/cambria/Cambria Math.ttf', 'TrueTypeUnicode', '', 32);
		$pdf->setFont($cambriabF, '', 14, '', true);
	// Add a page
	// This method has several options, check the source code documentation for more information.
		$pdf->AddPage();

  // set text shadow effect
		$pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(0, 0, 0), 'opacity' => 0, 'blend_mode' => 'Normal'));
		$agreeDate = '<u>' . date('d-m-Y', strtotime($agreementDate)) . '</u>';
		$assignDate = date('d-m-Y', strtotime($agreementDate));
		$type =  strtoupper($_POST['vehicleType']);
		$pdf_Tier_Type = '';
		$pdf_Apparatus_Equipment_Package = '';
		$pdf_Aerial_Package = '';
		$apuEnginHtml = '';
		$breakPage = '';
		if($Tier_Type == 'S')
		{
            $pdf_Tier_Type = 'Squad';
		}
		elseif($Tier_Type == 'B')
		{
			$pdf_Tier_Type = 'Battalion';
		}
		if($Apparatus_Equipment_Package == 'Y')
		{
			$pdf_Apparatus_Equipment_Package = 'Yes';
		}
		elseif($Apparatus_Equipment_Package == 'N')
		{
			$pdf_Apparatus_Equipment_Package = 'No';
		}
		if($Aerial_Package == 'Y')
		{
			$pdf_Aerial_Package = 'Yes';
		}
		elseif($Aerial_Package == 'N')
		{
			$pdf_Aerial_Package = 'No';
		}

		if($_POST['vehicleType'] == 'type 1'){
			$componentCoverageHtml =
			'<tr>
			  <td>
				<table class="inner-full-width"  cellpadding="2" cellspacing="2" style="margin:0;">
					<tr>
						<th style="padding:7px 10px; font-size:13px;"><b>III.</b> <b>COMPONENT COVERAGE:</b> <span style="font-weight: 300;">See page 2 section A. for details.</span></th>
					</tr>
					<tr>
						<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
							<label><input type="checkbox" name=""> TIER TYPE: ' . $pdf_Tier_Type . '</label>
						</td>
					</tr>
				</table>
			   </td>
		   </tr>
		   <br pagebreak="true" />';
		}
		elseif($_POST['vehicleType'] == 'type 2'){
			$componentCoverageHtml =
			'<tr>
			  <td>
				<table class="inner-full-width"  cellpadding="2" cellspacing="2" style="margin:0;">
					<tr>
						<th style="padding:7px 10px; font-size:13px;"><b>III.</b> <b>COMPONENT COVERAGE:</b> <span style="font-weight: 300;">See page 2 section A. for details.</span></th>
					</tr>
					<tr>
						<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
							<label><input type="checkbox" name=""> TIER TYPE: ' . $pdf_Tier_Type . '</label>
						</td>
					</tr>
					<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> APPRATUS EQUIPMENT PACKAGE: ' .$pdf_Apparatus_Equipment_Package . '</label>
					</td>
				   </tr>
				</table>
			   </td>
		   </tr>';
		  
		}
		elseif($_POST['vehicleType'] == 'type 3'){
			$componentCoverageHtml =
			'<tr>
			  <td>
				<table class="inner-full-width"  cellpadding="2" cellspacing="2" style="margin:0;">
					<tr>
						<th style="padding:7px 10px; font-size:13px;"><b>III.</b> <b>COMPONENT COVERAGE:</b> <span style="font-weight: 300;">See page 2 section A. for details.</span></th>
					</tr>

					<tr>
						<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
							<label><input type="checkbox" name=""> TIER TYPE: ' . $pdf_Tier_Type . '</label>
						</td>
					</tr>

					<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> APU PACKAGE INCLUDED</label>
					</td>
				   </tr>

					<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> APPRATUS EQUIPMENT PACKAGE: ' .$pdf_Apparatus_Equipment_Package . '</label>
					</td>
				   </tr>

				   <tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> AERIAL PACKAGE: ' .$pdf_Aerial_Package . '</label>
					</td>
				   </tr>

				</table>
			   </td>
		   </tr>
		   ';

		   $apuEnginHtml =
		   '<tr>
		   <td style="padding:7px 10px; font-size:13px;"><b>APU ENGINE:</b></td>
		   <td style="padding:7px 10px; font-size:13px;"><b>MAKE: '.$APU_Engine_Make.'</b></td>
		   <td style="padding:7px 10px; font-size:13px;">MODEL: '.$APU_Engine_Model.'</td>
		   <td style="padding:7px 10px; font-size:13px;">Year: '.$APU_Engine_Year.'</td>
		   <td style="padding:7px 10px; font-size:13px;">SERIAL: '.$APU_Engine_Serial.'</td>
	      </tr>';
		  $breakPage = '<br pagebreak="true" />';
		}

		$html = '
		<html lang="en">
		<style>
		@page {
		  margin:0;
		}
		table.main-table {
			text-align: center;
			padding: 30px 0 30px 0;
			margin:auto;
		}
		.padding-left{
			padding-left: 20px;
		}
		tr.row-2 td {
			font-weight: 900;
		}
		table.inner-full-width {
			width: 100%;
			text-align: left;
			border: 1px solid #000;
			border-bottom: 5px solid #000;
			padding-bottom: 0;
		}
		tr.row-2 td {
			font-weight: 900;
			font-size: 24px;
			font-family: Arial;
			padding-top: 30px;
		}
		tr td {
			font-size: 14px;
			font-family: Arial;
			line-height: 20px;
		}
		table.inner-full-width td, table.inner-full-width th {
			padding: 7px 12px;
			border-bottom: 1px solid #000;
		}
		.head-text-lg {
			font-weight: 600;
			font-size: 20px;
		}
		table.inner-full-width .border-bottom-none {
			border-bottom: none;
		}
		span.border-bottom.full-width {
			display: inline-block;
			width: 100%;
			border-bottom: 1px solid #000;
			margin-bottom: 10px;
			padding-top: 12px;
		}
		label.text-right {
			float: right;
		}
		.cst-sign.full-width {
			display: inline-block;
			width: 100%;
		}
		.sign-box.last {
			margin-bottom: 20px;
		}
		.color-blue {
			color: #201f58;
		}
		</style>
		<body>


		<div class="main">
			<div class="container">
				<table class="main-table" style="width: 600px; margin: auto;">
					<tr>
						<td>
							<table class="full-width" cellpadding="2" cellspacing="2" style="margin:0;">
								<tr>
									<td width="200" align="left"><img src="images/TM2.png" /></td>
									<td width="50" class="border-between"></td>
									<td width="350" align="left" class="padding-left">
										<table class="full-width" cellpadding="0" cellspacing="0">
											<tr>
												<td class="head-text-lg" align="center" style="font-size: 18px;">'.$type.' EMERGENCY VEHICLE COMPONENT BREAKDOWN LIMITED WARRANTY AGREEMENT</td>
											</tr>
											<tr>
												<td class="head-text" align="center" style="font-size: 13px;">Please call our claims hotline @ 800-903-7489 ext. 820, immediately upone noticing any unusual mechanical issues concerning the vehicle listed below.
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="3" align="center"><b>SUBMIT THIS WARRANTY IMMEDIATELY VIA: <span class="color-blue">TRÜNORTH GLOBAL DEALER PORTAL OR WARRANTY.MYTRUNORTH.COM</span></b></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
								<tr>
									<th colspan="4" style="padding:7px 10px; font-size:13px;"><b>I.</b> <b>CUSTOMER INFORMATION:</b></th>
									<th colspan="2" style="padding:7px 10px; font-size:13px;"><b>AGREEMENT DATE: '.$agreeDate.' </b></th>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;"><b>NAME:</b></td>
									<td style="padding:7px 10px; font-size:13px;">'.$customerName.'</td>
									<td style="padding:7px 10px; font-size:13px;"><b></b></td>
									<td style="padding:7px 10px; font-size:13px;"></td>
									<td style="padding:7px 10px; font-size:13px;"><b></b></td>
									<td style="padding:7px 10px; font-size:13px;"></td>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;"><b>EMAIL:</b></td>
									<td style="padding:7px 10px; font-size:13px;">'.$customerEmail.'</td>
									<td style="padding:7px 10px; font-size:13px;"><b></b></td>
									<td style="padding:7px 10px; font-size:13px;"></td>
									<td style="padding:7px 10px; font-size:13px;"><b>PH#:</b></td>
									<td style="padding:7px 10px; font-size:13px;">'.$customerPhone.'</td>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;"><b>ADDRESS:</b></td>
									<td style="padding:7px 10px; font-size:13px;">'.$customerAddress.'</td>
									<td style="padding:7px 10px; font-size:13px;"><b></b></td>
									<td style="padding:7px 10px; font-size:13px;"></td>
									<td style="padding:7px 10px; font-size:13px;"><b></b></td>
									<td style="padding:7px 10px; font-size:13px;"></td>
								</tr>
								<tr>
									<td class="border-bottom-none" style="padding:7px 10px; font-size:13px;"><b>City:</b></td>
									<td class="border-bottom-none" style="padding:7px 10px; font-size:13px;">'.$customerCity.'</td>
									<td class="border-bottom-none" style="padding:7px 10px; font-size:13px;"><b>State/Province:</b></td>
									<td class="border-bottom-none" style="padding:7px 10px; font-size:13px;">'. $customerState.'</td>
									<td class="border-bottom-none" style="padding:7px 10px; font-size:13px;"><b>Zip/Postal Code:</b></td>
									<td class="border-bottom-none" style="padding:7px 10px; font-size:13px;">'.$customerZip.'</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table class="inner-full-width"  cellpadding="2" cellspacing="2" style="margin:0;">
								<tr>
									<th colspan="4" style="padding:7px 10px; font-size:13px;"><b>II.</b> <b>VEHICLE INFORMATION:</b></th>
									<th colspan="2" style="padding:7px 10px; font-size:13px;"></th>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;"><b>FULL VIN: '.$Vehicle_Vin_Number.'</b></td>
									<td style="padding:7px 10px; font-size:13px;"></td>
									<td style="padding:7px 10px; font-size:13px;"><b></b></td>
									<td style="padding:7px 10px; font-size:13px;"></td>
									<td style="padding:7px 10px; font-size:13px;"><b></b></td>
									<td style="padding:7px 10px; font-size:13px;"></td>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;"><b>VECHILE:</b></td>
									<td style="padding:7px 10px; font-size:13px;">Year: '.$Vehicle_Year.'</td>
									<td style="padding:7px 10px; font-size:13px;"><b>MAKE: '.$Vehicle_Make.'</b></td>
									<td style="padding:7px 10px; font-size:13px;"></td>
									<td><b>MODEL:</b></td>
									<td>'.$Vehicle_Model.'</td>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;"><b>ENGINE:</b></td>
									<td style="padding:7px 10px; font-size:13px;">MAKE '.$Engine_Make.'</td>
									<td style="padding:7px 10px; font-size:13px;"><b>MODEL:</b></td>
									<td style="padding:7px 10px; font-size:13px;">'.$Engine_Model.'</td>
									<td style="padding:7px 10px; font-size:13px;"><b>SERIAL#:</b></td>
									<td style="padding:7px 10px; font-size:13px;">'.$Engine_Serial.'</td>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;"><b>TRANSMISSION:</b></td>
									<td style="padding:7px 10px; font-size:13px;">MAKE '. $Transmission_Make .'</td>
									<td style="padding:7px 10px; font-size:13px;"><b>MODEL: </b></td>
									<td style="padding:7px 10px; font-size:13px;">'. $Transmission_Model .'</td>
									<td style="padding:7px 10px; font-size:13px;"><b>SERIAL#: </b></td>
									<td style="padding:7px 10px; font-size:13px;">'. $Transmission_Serial .'</td>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>ODO. READING:</b></td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $Odometer_Reading . ' ' . $Odometer_Miles_Or_KM . '</td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b></b></td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>ECM Reading:</b></td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>' . $ECM_Reading . ' ' . $ECM_Miles_Or_KM . '</b></td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"></td>
								</tr>
								'.$apuEnginHtml.'
							</table>
						</td>
					</tr>
					'.$componentCoverageHtml.'
					<tr>
						<td>
							<table class="inner-full-width"  cellpadding="2" cellspacing="2" style="margin:0;">
								<tr>
									<th style="padding:7px 10px; font-size:13px;"><b>VI.</b> <b>COVERAGE TIME:</b> <span style="font-weight: 300;">The warranty period begins on the Agreement Date Listed above and expires when either the time selected has ended or the unaltered ECM/ECU reaches the mileage/km/hours term limit, whichever occurs first.</span></th>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;" align="center"><b>Type 1 Emergency Vehicles</b></td>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none" align="center">
									  <table cellpadding="2" cellspacing="2" style="margin:0; width:100%;">
										<tr>
										  <td align="center">
											<label><input type="checkbox" name=""> ' . $Coverage_Term . ' Years</label>
										  </td>
										</tr>
									  </table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
								<tr>
									<th style="padding:7px 10px; font-size:13px;" colspan="2"><b>V.</b> <b>RETAILER INFORMATION:</b></th>
									<th style="padding:7px 10px; font-size:13px;"><b>AR#:</b></th>
									<th style="padding:7px 10px; font-size:13px;"></th>
									<th style="padding:7px 10px; font-size:13px;">P0#:</th>
									<th style="padding:7px 10px; font-size:13px;"></th>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;"><b>RETAILER NAME:</b></td>
									<td style="padding:7px 10px; font-size:13px;">' . $dealerName . '</td>
									<td style="padding:7px 10px; font-size:13px;"><b></b></td>
									<td style="padding:7px 10px; font-size:13px;"></td>
									<td style="padding:7px 10px; font-size:13px;"><b>PH#:</b></td>
									<td style="padding:7px 10px; font-size:13px;">' . $dealerPhone . '</td>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;"><b>STREET ADDRESS: </b></td>
									<td style="padding:7px 10px; font-size:13px;" colspan="5">' . $dealerAddress1 . '</td>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>City:</b></td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">'.$dealerCity.'</td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>State/Province:</b></td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">'.$dealerState.'</td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>Zip/Postal Code:</b></td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">'.$dealerZip.'</td>
								</tr>
							</table>
						</td>
					</tr>

					<tr>
						<td>
							<table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
								<tr>
									<th style="padding:7px 10px; font-size:13px;" colspan="2"><b>VI.</b> <b>LIEN HOLDER INFORMATION (If applicable)</b></th>
									<th style="padding:7px 10px; font-size:13px;"><b></b></th>
									<th style="padding:7px 10px; font-size:13px;"></th>
									<th style="padding:7px 10px; font-size:13px;"></th>
									<th style="padding:7px 10px; font-size:13px;"></th>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;"><b>LIEN HOLDER NAME:</b></td>
									<td style="padding:7px 10px; font-size:13px;">' . $Lien_Holder_Name . '</td>
									<td style="padding:7px 10px; font-size:13px;"><b></b></td>
									<td style="padding:7px 10px; font-size:13px;"></td>
									<td style="padding:7px 10px; font-size:13px;"><b>PH#:</b></td>
									<td style="padding:7px 10px; font-size:13px;">' . $Lien_Holder_Phone_Number . '</td>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;"><b>STREET ADDRESS: </b></td>
									<td style="padding:7px 10px; font-size:13px;" colspan="5">' . $Lien_Holder_Address . '</td>
								</tr>
								<tr>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>City:</b></td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $Lien_Holder_City . '</td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>State/Province:</b></td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $Lien_Holder_State_Province . '</td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>Zip/Postal Code:</b></td>
									<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $Lien_Holder_Postal_Code . '</td>
								</tr>
							</table>
						</td>
					</tr>
					'.$breakPage.'
					<tr>
						<td>
							<table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
								<tr>
									<th style="padding:7px 10px; font-size:13px;" colspan="3"><b>VII.</b> <b>I UNDERSTAND:</b> <span style="font-weight: 300;">The warranty period begins on the Agreement Date Listed above and expires when either the time selected has ended or the unaltered ECM/ECU reaches the mileage/km/hours term limit, whichever occurs first.</span></th>
								</tr>

								<tr>
									<td style="padding:7px 10px; font-size:13px;">
										<table cellpadding="2" cellspacing="2" width="550" align="center" style="margin: auto;">
											<tr>
												<td width="270">
													<table style="width: 100%;" cellpadding="2" cellspacing="2">
														<tr>
															<td colspan="2" style="border-bottom: 1px solid #000;">&nbsp;</td>
														</tr>
														<tr>
															<td>CUSTOMER SIGNATURE</td>
															<td align="right" style="text-align: right;">DATE</td>
														</tr>
													</table>
												</td>
												<td width="20"></td>
												<td width="270">
													<table style="width: 100%;" cellpadding="2" cellspacing="2">
														<tr>
															<td colspan="2" style="border-bottom: 1px solid #000;">&nbsp;</td>
														</tr>
														<tr>
															<td>CUSTOMER SIGNATURE</td>
															<td align="right" style="text-align: right;">DATE</td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td width="270">
													<table style="width: 100%;" cellpadding="2" cellspacing="2">
														<tr>
															<td colspan="2" style="border-bottom: 1px solid #000;">&nbsp;</td>
														</tr>
														<tr>
															<td>AUTHORIZED RETAILER SIGNATURE</td>
															<td align="right" style="text-align: right;">DATE</td>
														</tr>
													</table>
												</td>
												<td width="20"></td>
												<td width="270">
													<table style="width: 100%;" cellpadding="2" cellspacing="2">
														<tr>
															<td colspan="2" style="border-bottom: 1px solid #000;">&nbsp;</td>
														</tr>
														<tr>
															<td>AUTHORIZED RETAILER NAME (Printed)</td>
															<td align="right" style="text-align: right;">DATE</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</div>




		</body>
		</html>
 ';

 //<p class="fontClass">TrüNorth Global™ Signature:__________________________________________________________________Date:'.$agreeDate.'_____________________</p>
 // Print text using writeHTMLCell()
		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

 // ---------------------------------------------------------

 // Close and output PDF document
 // This method has several options, check the source code documentation for more information.
        
		$pdfFileName = str_replace(" ", "_", $customerName) . '_' . str_replace(" ", "_", $customerPhone) . '_' . time() . '.pdf';
		$pdf->Output($pdfFileName, 'D');
        die("generate PDF");
		// if ($isQuote == "Y") {
		// 	$pdf->Output(__DIR__ . '/uploads/quote_pdf/' . $pdfFileName, 'F');
		// } else {
		// 	$pdf->Output(__DIR__ . '/uploads/warranty_pdf/' . $pdfFileName, 'F');
		// }

 // Save Pddf into database
		// $query = "SELECT Pers_ID FROM Pers WHERE Acct_ID=" . $dealerID . ";";
		// $result = $link->query($query);
		// $row = $result->fetch_assoc();

		// $primary_Contact_Person_id = $row['Pers_ID'];

 // Get the contract info
		// $query = "SELECT cd.Cntrct_Dim_ID FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
		// $result = $link->query($query);
		// $row = $result->fetch_assoc();

		// $contract_dim_ID = $row["Cntrct_Dim_ID"];

 // Add this file to our File_Assets tracking table
 //  Set type=2 for 'dealer W9'.
		// if ($isQuote == "Y") {
		// 	$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
		// 				   Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,6,'Quote',NOW())");
		// } else {
		// 	$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
		// 				   Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,7,'Warranty',NOW())");
		// }



 /* Bind variables to parameters */
		// $val1 = $dealerID;
		// $val2 = $primary_Contact_Person_id;
		// $val3 = $adminID;
 //$val4 = $contract_dim_ID;
		// $val4 = $contract_ID; // PARRY: I changed this to $warrantyID which is the Cntrct_ID not Cntrct_Dim_ID.
		// if ($isQuote == "Y") {
		// 	$val5 = '/uploads/quote_pdf/' . $pdfFileName;
		// } else {
		// 	$val5 = '/uploads/warranty_pdf/' . $pdfFileName;

		// }
		// mysqli_stmt_bind_param($stmt, "iiiis", $val1, $val2, $val3, $val4, $val5);


 /* Execute the statement */
		// $result = mysqli_stmt_execute($stmt);

	//============================================================+
	// END OF FILE
  //============================================================+

 // End PDF Code here

	}



	// If Small Goods was selected, then forward to the worksheet
	// if ($smallGoodsPackage == "Y") {

	// 	// Redirect to next form

	// 	if ($isQuote == "Y") {
	// 		header("location: small_goods_summary_worksheet.php");
	// 		exit;
	// 	} else {
	// 		header("location: small_goods_worksheet.php");
	// 		exit;
	// 	}


	//}


	// Redirect to next form
	//header("location: warranty_pending.php");
	//exit;
	//die();

    echo "Success";

}


?>