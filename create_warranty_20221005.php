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


// PDF function
require_once "lib/pdfHelper.php";


// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');

require_once 'vendor/autoload.php';

use Classes\GeneratePDF;

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
$isQuote = "N";
$customerPO = "";

$dealerARNumber = "";

$smallGoodsPackage = "";

$form_err = "";
$ECM_Reading_Km = "";
$Odometer_Reading_Km = "";


// Menu Controls
$navSection="warranty";
$navItem = "createWarranty";



session_start();

$_SESSION['redirect'] = false;

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
	$customerPO = $row["PO_Nbr"];

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
	$Engine_Hours = $row["Veh_Eng_Hours"];

	$Transmission_Make = $row["Veh_Trnsmsn_Mk_Cd"];
	$Transmission_Model = $row["Veh_Trnsmsn_Model_Cd"];
	$Transmission_Serial = $row["Veh_Trnsmsn_Ser_nbr"];

	$Odometer_Reading_Miles = $row["OdoMtr_Read_Miles_Cnt"];
	$Odometer_Reading_Km = $row["OdoMtr_Read_Kms_Cnt"];

	$Odometer_Miles_Or_KM = "Miles";
	if ($Odometer_Reading_Miles != 0) {
		$Odometer_Miles_Or_KM = "Miles";
	} else if($Odometer_Reading_Km != 0) {
		$Odometer_Miles_Or_KM = "KM";
	}

	$ECM_Reading_Miles = $row["ECM_Read_Miles_Cnt"];
	$ECM_Reading_Km = $row["ECM_Read_Kms_Cnt"];

	$ECM_Miles_Or_KM = "Miles";
	if ($ECM_Reading_Miles != 0) {
		$ECM_Miles_Or_KM = "Miles";
	} else if($ECM_Reading_Km != 0) {
		$ECM_Miles_Or_KM = "KM";
	}

	$APU_Engine_Make = $row["Veh_APU_Eng_Mk_Cd"];
	$APU_Engine_Model = $row["Veh_APU_Eng_Model_Cd"];
	$APU_Engine_Year = $row["Veh_APU_Eng_Yr_Cd"];
	$APU_Engine_Serial = $row["Veh_APU_Eng_Ser_nbr"];
	$APU_Hours = $row["Veh_APU_Hours"];
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

	$dealerAgentID = $row["Pers_Who_Signd_Cntrct_ID"];

	//$warrantyStatus = $row["Warranty_Status"];



	$_SESSION["warrantyID"] = $warrantyID;

}


// Process form data when form is submitted.
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
		$agreementDateForInsert = trim($_POST["agreementDate"]);
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

	if (!empty(trim($_POST["customerPO"]))) {
		$customerPO = trim($_POST["customerPO"]);
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

	if (!empty(trim($_POST["engineHours"]))) {
		$Engine_Hours = trim($_POST["engineHours"]);
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
		if($Vehicle_Type == 3)
		{
			$APU_Flg = trim($_POST["isAPU"]);
		}
		else
		{
			$APU_Flg = 'N';
		}
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

	if (!empty(trim($_POST["apuHours"]))) {
		$APU_Hours = trim($_POST["apuHours"]);
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
									   Srvc_Veh_Flg=?,PO_Nbr=? WHERE
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
		$val2 = date('Y-m-d', strtotime($agreementDateForInsert));
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
		$val26 = $customerPO;
		$val27 = $Cntrct_Dim_ID;

		mysqli_stmt_bind_param($stmt, "ssssssssssisssissssssissssi", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22, $val23, $val24, $val25,$val26,$val27);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


		/* Prepare an UPDATE statement to update a Veh entry for this Warranty */
		$stmt = mysqli_prepare($link, "UPDATE Veh SET Veh_Mk_Cd=?,Veh_Model_Cd=?,Veh_Model_Yr_Cd=?,
									   Veh_Eng_Mk_CD=?,veh_Eng_Model_Cd=?,Veh_Eng_Ser_Nbr=?,
									   Veh_Gross_Wgt_Cnt=?,Veh_Type_Nbr=?,Veh_New_Flg=?,
									   Veh_Trnsmsn_Ser_nbr=?,Veh_Trnsmsn_Mk_Cd=?,Veh_Trnsmsn_Model_Cd=?,
									   Veh_APU_Eng_Ser_nbr=?,Veh_APU_Eng_Mk_Cd=?,Veh_APU_Eng_Model_Cd=?,Veh_APU_Eng_Yr_Cd=?,
									   OdoMtr_Read_Miles_Cnt=?,OdoMtr_Read_Kms_Cnt=?,ECM_Read_Miles_Cnt=?,ECM_Read_Kms_Cnt=?,Veh_Desc=?,
									   Veh_Id_Nbr=?, Veh_Eng_Hours=?, Veh_APU_Hours=? WHERE Veh_ID=?");

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
		$val23 = $Engine_Hours;
		$val24 = $APU_Hours;
		$val25 = $Veh_ID;

		mysqli_stmt_bind_param($stmt, "sssssssissssssssssssssssi", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22, $val23, $val24, $val25);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


		/* Prepare an UPDATE statement to update a Cntrct entry for this Warranty */
		$stmt = mysqli_prepare($link, "UPDATE Cntrct SET Cntrct_Sales_Chnl=?,Sply_Pkt_Shipd_Dte=?,
		                               Pers_Who_Signd_Cntrct_ID=? WHERE Cntrct_ID=?");

		$val1 = $customerSalesChannel;
		$val2 = $Supply_Packet_Shipped_Date;
		$val3 = $dealerAgentID;
		$val4 = $warrantyID;

		mysqli_stmt_bind_param($stmt, "ssii", $val1, $val2, $val3, $val4);

/*
echo "customerSalesChannel=".$customerSalesChannel;
echo "<br />Supply_Packet_Shipped_Date=".$Supply_Packet_Shipped_Date;
echo "<br />warrantyID=".$warrantyID;
die();
*/

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);



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
		// NB: do not update the Dlr_Mrkp_Actl_Amt column in case edits have been made manually.
		$stmt = mysqli_prepare($link, "UPDATE Cntrct SET Sales_Agt_Cost_Amt=?, Sales_Agt_Commission_Amt=?,
		                               Dlr_Cost_Amt=?, Dlr_Mrkp_Max_Amt=?, MSRP_Amt=? WHERE Cntrct_ID=?");

		$val1 = $Sales_Agt_Cost_Amt;
		$val2 = $Sales_Agt_Commission_Amt;
		$val3 = $Dlr_Cost_Amt;
		$val4 = $Dlr_Mrkp_Max_Amt;
		$val5 = $MSRP_Amt;
		$val6 = $contract_ID;

		mysqli_stmt_bind_param($stmt, "iiiiii", $val1, $val2, $val3, $val4, $val5, $val6);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


		// Update the totals
		$stmt = mysqli_prepare($link, "UPDATE Cntrct SET MSRP_Amt=(Dlr_Mrkp_Actl_Amt+Dlr_Cost_Amt),
									   Sml_Goods_Tot_Amt=(Dlr_Sml_Goods_Cst_Tot_Amt+Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt),
									   Addl_MSRP_Amt=(Addl_Dlr_Cost_Amt+Addl_Dlr_Mrkp_Actl_APU_Amt+Addl_Dlr_Mrkp_Actl_AEP_Amt+Addl_Dlr_Mrkp_Actl_AER_Amt),
									   Addl_Dlr_Mrkp_Actl_Amt=(Addl_Dlr_Mrkp_Actl_APU_Amt+Addl_Dlr_Mrkp_Actl_AEP_Amt+Addl_Dlr_Mrkp_Actl_AER_Amt),
									   Tot_Dlr_Mrkp_Act_Amt=(Dlr_Mrkp_Actl_Amt+
									   Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt+
									   Addl_Dlr_Mrkp_Actl_APU_Amt+Addl_Dlr_Mrkp_Actl_AEP_Amt+Addl_Dlr_Mrkp_Actl_AER_Amt
									   ),
									   Tot_MSRP_Amt=(Dlr_Mrkp_Actl_Amt+Dlr_Cost_Amt+
									   Dlr_Sml_Goods_Cst_Tot_Amt+Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt+
									   Addl_Dlr_Cost_Amt+Addl_Dlr_Mrkp_Actl_APU_Amt+Addl_Dlr_Mrkp_Actl_AEP_Amt+Addl_Dlr_Mrkp_Actl_AER_Amt
									   )
									   WHERE Cntrct_ID=?");

		mysqli_stmt_bind_param($stmt, "i", $contract_ID);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);



		// Call our function to updated the TOTALS columns in the Cntrct table, which is the sum
		//  of base + add-on + small goods.  Need to refresh these totals whenever changes are made
		//  Since we are creating the Contract at this time, pass in 'Y' for the 'include actuals'
		//   argument.  This will update the Tot_Dlr_Mrkp_Act_Amt, which we want to avoid updating
		//   in the future so it doesn't reset the custom selection made by a dealer.
		//$totalUpdateResult = updateWarrantyTotals($link,$contract_ID,"Y");


	} else {

		//die($_POST["customerEmail"]);
		/* Prepare an insert statement to create a Warranty entry */
		$sqlString = "INSERT INTO New_Warranty_Temp (Acct_ID,Customer_Name,Customer_Email,Customer_Address,";
		$sqlString .= "Customer_City,Customer_State,Customer_Zip,Customer_Phone,Customer_Sales_Channel,Contract_Number,";
		$sqlString .= "Agreement_Date,Vehicle_Manufacturer_Name,Vehicle_Gross_Weight,Vehicle_Type,Vehicle_Vin_Number,Vehicle_Year,";
		$sqlString .= "Vehicle_Make,Vehicle_Model,Engine_Make,Engine_Model,Engine_Serial,Transmission_Make,";
		$sqlString .= "Transmission_Model,Transmission_Serial,Odometer_Reading,Odometer_Miles_Or_KM,ECM_Reading,";
		$sqlString .= "ECM_Miles_Or_KM,APU_Engine_Make,APU_Engine_Model,APU_Engine_Year,APU_Engine_Serial,";
		$sqlString .= "Vehicle_New_Flag,Vehicle_Description,Tier_Type,Apparatus_Equipment_Package,Aerial_Package,";
		$sqlString .= "Coverage_Term,Small_Goods_Package,Supply_Packet_To_Be_Shipped,Supply_Packet_Left,";
		$sqlString .= "Supply_Packet_Shipped_Date,Lien_Holder_Name,Lien_Holder_Email,Lien_Holder_Address,";
		$sqlString .= "Lien_Holder_City,Lien_Holder_State_Province,Lien_Holder_Postal_Code,Lien_Holder_Phone_Number,";
		$sqlString .= "Dealer_Signature,Dealer_Signature_Name,Dealer_Signature_Date,Customer_Signature,";
		$sqlString .= "Customer_Signature_Name,Customer_Signature_Date,";
		$sqlString .= "Warranty_Status,Created_Date) values ";
		$sqlString .= "(?,?,?,?,?,?,?,?,?,'0',"; // up to Contract_Number
		$sqlString .= "?,?,?,?,?,?,?,?,?,?,?,?,"; // up to Transmission_Make
		$sqlString .= "?,?,?,?,?,?,?,?,?,?,"; // up to APU_Engine_Serial
		$sqlString .= "?,?,?,?,?,?,?,?,?,"; // up to Supply_Packet_Left
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


		mysqli_stmt_bind_param($stmt, "isssssssssssssissssssssisisssissssssisssssssssssssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22, $val23, $val24, $val25, $val26, $val27, $val28, $val29, $val30, $val31, $val32, $val33, $val34, $val35, $val36, $val37, $val38, $val39, $val40, $val41, $val42, $val43, $val44, $val45, $val46, $val47, $val48, $val49, $val50, $val51, $val52, $val53, $val54);


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
									   Lien_Nme,Lien_Eml,Lien_Addrs,Lien_Cty,Lien_Ste,Lien_Pstl,Lien_Phn,Srvc_Veh_Flg,PO_Nbr,
									   Created_Date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");

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
			$val5 = date('Y-m-d', strtotime($agreementDateForInsert));
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
			$val5 = date('Y-m-d', strtotime($agreementDateForInsert));
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
		$val29 = $customerPO;

		mysqli_stmt_bind_param($stmt, "sssssssssssssisssissssssissss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22, $val23, $val24, $val25, $val26, $val27, $val28, $val29);

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
									   Veh_Id_Nbr,Veh_Eng_Hours,Veh_APU_Hours)
									   VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

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
		$val23 = $Engine_Hours;
		$val24 = $APU_Hours;

		mysqli_stmt_bind_param($stmt, "sssssssissssssssssssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22, $val23, $val24);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the Contract_ID of the new contract dim entry.
		$veh_ID = mysqli_insert_id($link);

		/* Prepare an insert statement to create a Cntrct entry for this new Warranty */
		$stmt = mysqli_prepare($link, "INSERT INTO Cntrct (Cntrct_Nbr,Cntrct_Sales_Chnl,Sply_Pkt_Shipd_Dte,
									   Cntrct_Dim_ID,Veh_ID,Mfr_Acct_ID,Pers_Who_Signd_Cntrct_ID,Created_Date)
									   VALUES (?,?,?,?,?,?,?,NOW())");

		// Data processing
		// Set quote or draft flags accordingly
		if ($isQuote == "Y") {
			$val1 = "";
			$val2 = $customerSalesChannel;
			$val3 = $Supply_Packet_Shipped_Date;
			$val4 = $contract_dim_ID;
			$val5 = $veh_ID;
			$val6 = $Acct_ID;
			$val7 = $dealerAgentID;
		} else {
			$val1 = "";
			$val2 = $customerSalesChannel;
			$val3 = $Supply_Packet_Shipped_Date;
			$val4 = $contract_dim_ID;
			$val5 = $veh_ID;
			$val6 = $Acct_ID;
			$val7 = $dealerAgentID;
		}

		mysqli_stmt_bind_param($stmt, "sssiiii", $val1, $val2, $val3, $val4, $val5, $val6, $val7);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the Contract_ID of the new contract dim entry.
		$contract_ID = mysqli_insert_id($link);

		// Put the contractID into session that we just created, which is how
		//  the Small Goods worksheet will function.
		$_SESSION["warrantyID"] = $contract_ID;



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
			if ((date("Y") - $Vehicle_Year) > 14) {
				$isOLD = "Y";
			}
		} else {
			$isOLD = "N";
		}

		// Look up the MAX values for any add-on included, and set the 'ACTL' column in Cntrct accordingly
		$Addl_Dlr_Mrkp_Actl_APU_Amt = 0;
		$Addl_Dlr_Mrkp_Actl_AEP_Amt = 0;
		$Addl_Dlr_Mrkp_Actl_AER_Amt = 0;
		if($isAPU=="Y"){
			$query = "SELECT Dlr_Mrkp_Max_Amt FROM Addl_Std_Prcg WHERE Addl_Type_Cd = 'APU'";
			$result = $link->query($query);
			if ($result->num_rows > 0) {
				$row = mysqli_fetch_assoc($result);
				$Addl_Dlr_Mrkp_Actl_APU_Amt = $row["Dlr_Mrkp_Max_Amt"];
			}
		}

		if($isAEP=="Y"){
			$query = "SELECT Dlr_Mrkp_Max_Amt FROM Addl_Std_Prcg WHERE Addl_Type_Cd = 'AEP'";
			$result = $link->query($query);
			if ($result->num_rows > 0) {
				$row = mysqli_fetch_assoc($result);
				$Addl_Dlr_Mrkp_Actl_AEP_Amt = $row["Dlr_Mrkp_Max_Amt"];
			}
		}

		if($isAER=="Y"){
			$query = "SELECT Dlr_Mrkp_Max_Amt FROM Addl_Std_Prcg WHERE Addl_Type_Cd = 'AER'";
			$result = $link->query($query);
			if ($result->num_rows > 0) {
				$row = mysqli_fetch_assoc($result);
				$Addl_Dlr_Mrkp_Actl_AER_Amt = $row["Dlr_Mrkp_Max_Amt"];
			}
		}

	/*
	echo "Addl_Dlr_Mrkp_Actl_APU_Amt=".$Addl_Dlr_Mrkp_Actl_APU_Amt;
	echo "<br />Addl_Dlr_Mrkp_Actl_AEP_Amt=".$Addl_Dlr_Mrkp_Actl_AEP_Amt;
	echo "<br />Addl_Dlr_Mrkp_Actl_AER_Amt=".$Addl_Dlr_Mrkp_Actl_AER_Amt;
	*/


		$addlStdPrcgResult = selectAddlStdPrcgSum($link, $isAEP, $isAPU, $isAER, $isOLD);
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
										   Addl_MSRP_Amt=?, Addl_Dlr_Mrkp_Actl_APU_Amt=?, Addl_Dlr_Mrkp_Actl_AEP_Amt=?,
										   Addl_Dlr_Mrkp_Actl_AER_Amt=? WHERE Cntrct_ID=?");

			$val1 = $Sales_Agt_Cost_Amt;
			$val2 = $Sales_Agt_Commission_Amt;
			$val3 = $Dlr_Cost_Amt;
			$val4 = $Dlr_Mrkp_Max_Amt;
			$val5 = $Dlr_Mrkp_Actl_Amt;
			$val6 = $MSRP_Amt;
			$val7 = $Addl_Dlr_Mrkp_Actl_APU_Amt;
			$val8 = $Addl_Dlr_Mrkp_Actl_AEP_Amt;
			$val9 = $Addl_Dlr_Mrkp_Actl_AER_Amt;
			$val10 = $contract_ID;

			mysqli_stmt_bind_param($stmt, "iiiiiiiiii", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);

			// Small goods values will be populated into the contract after
			//  the Small Goods process is complete, in the next step below


			// Call our function to updated the TOTALS columns in the Cntrct table, which is the sum
			//  of base + add-on + small goods.  Need to refresh these totals whenever changes are made
			//  Since we are creating the Contract at this time, pass in 'Y' for the 'include actuals'
			//   argument.  This will update the Tot_Dlr_Mrkp_Act_Amt, which we want to avoid updating
			//   in the future so it doesn't reset the custom selection made by a dealer.
			$totalUpdateResult = updateWarrantyTotals($link,$contract_ID,"Y");



	} // if($warranty!="") //



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
	if ($phoneResult) {
		$row = $phoneResult->fetch_assoc();
		$dealerPhone = $row["Tel_Nbr"];
	} else {
		$dealerPhone = "";
		$generatePdf = false;
	}


	if ($generatePdf) {
		$type =  strtoupper($_POST['vehicleType']);
		$agreeDate = '<u>' . date('d-m-Y', strtotime($agreementDate)) . '</u>';
		$assignDate = date('d-m-Y', strtotime($agreementDate));
        //Customer State
		//$customerStateResult = selectStates($link, $customerState);
	    //$row = $customerStateResult->fetch_assoc();
		//$customerStatePDF  = $row['St_Prov_Nm'];

		$customerStatePDF = selectState($link, $customerState);


		//Dealer State
		//$dealerStateResult = selectStates($link, $dealerState);
	    //$row = $dealerStateResult->fetch_assoc();
		//$dealerStatePDF  = $row['St_Prov_Nm'];

		$dealerStatePDF = selectState($link, $dealerState);


		//Lien Holder State
		//$lienStateResult = selectStates($link, $Lien_Holder_State_Province);
	    //$row = $lienStateResult->fetch_assoc();
		//$Lien_Holder_State_Province_pdf  = $row['St_Prov_Nm'];

		if($Lien_Holder_State_Province!=""){
			$Lien_Holder_State_Province_pdf = selectState($link, $Lien_Holder_State_Province);
			if($Lien_Holder_State_Province_pdf==0){
				$Lien_Holder_State_Province_pdf="";
			}
		}else{
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

		if($Coverage_Term == '5')
		{
			$Coverage_Term = '5 YRS UNL';
		}
		else if($Coverage_Term == '7')
		{
			$Coverage_Term = '7 YRS UNL';
		}
		else if($Coverage_Term == '10')
		{
			$Coverage_Term = '10 YRS UNL';
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


		$Odometer_Reading = $_POST["odometerReading"];
		$ECM_Reading = $_POST["ecmReading"];


		$data = [
			'AGREEMENT DATE' => '           '.$assignDate,
			'CUSTOMER NAME' => $customerName,
			'CUSTOMER EMAIL' =>$customerEmail,
			'CUSTOMER PH#' => $customerPhone,
			'CUSTOMER ADDRESS' => $customerAddress,
			'CUSTOMER CITY' => $customerCity,
			'CUSTOMER STATE/PROVINCE' => $customerStatePDF,
			'CUSTOMER ZIP/POSTAL CODE' => $customerZip,
			'VEHICLE TYPE' => 1,
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
			'APU HOURS' => $APU_Hours
			];


                        $pdfFileName = str_replace(" ", "_", $customerName) . '_' . str_replace(" ", "_", $customerPhone) . '_' . time() . '.pdf';

			$pdf = new GeneratePDF;
			$pdf->generate($data , $pdfFileName , $type , $isQuote);

                // Save Pddf into database
		$query = "SELECT Pers_ID FROM Pers WHERE Acct_ID=" . $dealerID . ";";
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$primary_Contact_Person_id = $row['Pers_ID'];

                // Get the contract info
		$query = "SELECT cd.Cntrct_Dim_ID, cd.Assign_Rtlr_Nbr FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$contract_dim_ID = $row["Cntrct_Dim_ID"];
		$dealer_AR_number = $row["Assign_Rtlr_Nbr"];

                // Add this file to our File_Assets tracking table
                //  Set type=2 for 'dealer W9'.
		if ($isQuote == "Y") {
			$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
						   Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,6,'Quote',NOW())");
		} else {
			$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
						   Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,7,'Warranty',NOW())");
		}



                /* Bind variables to parameters */
		$val1 = $dealerID;
		$val2 = $primary_Contact_Person_id;
		$val3 = $adminID;
		//$val4 = $contract_dim_ID;
		$val4 = $contract_ID; // PARRY: I changed this to $warrantyID which is the Cntrct_ID not Cntrct_Dim_ID.
		if ($isQuote == "Y") {
			$val5 = '/uploads/quote_pdf/' . $pdfFileName;
		} else {
			$val5 = '/uploads/warranty_pdf/' . $pdfFileName;

		}
		mysqli_stmt_bind_param($stmt, "iiiis", $val1, $val2, $val3, $val4, $val5);


                /* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

                if($isQuote == "N")
		{
		   include "create-addendum-pdf.php";
		}

	    }


		// If Small Goods was selected, then forward to the worksheet
		 if ($smallGoodsPackage == "Y") {

		// 	// Redirect to next form

		 	if ($isQuote == "Y") {
		 		header("location: small_goods_summary_worksheet.php");
		 		exit;
		 	} else {
		 		header("location: small_goods_worksheet.php");
		 		exit;
		 	}


	     }


		  // Call our function to updated the TOTALS columns in the Cntrct table, which is the sum
		  //  of base + add-on + small goods.  Need to refresh these totals whenever changes are made
		  $totalUpdateResult = updateWarrantyTotals($link,$contract_ID);

		 // Redirect to next form
         if ($isQuote == "Y") {
			header("location: warranty_pending.php?showQuotes=Y");
			exit;
			die();
		 }else{
			header("location: warranty_pending.php");
			exit;
			die();
		 }

    }

	else  {

	// Get the dealer info
	$query = "SELECT * FROM Acct WHERE Acct_ID=" . $dealerID . ";";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$dealerName = $row["Acct_Nm"];


	// Get the dealer address info
	$query = "SELECT * FROM Addr WHERE Acct_ID=" . $dealerID . " AND Addr_Type_Cd='Work';";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$dealerAddress1 = $row["St_Addr_1_Desc"];
	$dealerAddress2 = $row["St_Addr_2_Desc"];
	$dealerCity = $row["City_Nm"];
	$dealerState = $row["St_Prov_ID"];
	$dealerZip = $row["Pstl_Cd"];

	$customerStateResult = selectStates($link);
	$lienStateResult = selectStates($link);

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

    }


require_once("includes/header.php");

?>

		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
			<div class="container-fluid">
                <!-- <div class="row">
                    <div class="col-md-6">
                        <div class="logo">
                            <img src="images/vt_logo.png" alt="Vital Trends">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="logo">
                            <img src="images/VTPoweredbyTNG.png" alt="Vital Trends">
                        </div>
                    </div>
                </div> -->
                <!-- <div class="row">
                    <div class="col-md-6">
                        <div>
                            <img src="images/VTPoweredbyTNG.png" alt="Vital Trends Powered by TruNorth">
                        </div>
                    </div>
                    <div class="col-md-6">
						&nbsp;
                    </div>
                </div> -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header text-center">
                                <h4 class="card-title">
                                <?php
                                   if ($warrantyID != "") {
                                       echo "Update ";
                                    } else {
                                       echo "New ";
                                    }

                                    if ($isQuote == "Y") {
                                       echo "Quote <span style='font-size: 14px;'>(All fields with <span class='text-danger'>*</span> are required)</span>";
                                    } else {
                                       echo "Warranty <span style='font-size: 14px;'>(All fields with <span class='text-danger'>*</span> are required)</span>";
                                    }
                                ?>
                                </h4>
                                <?php
                                    if ($warrantyStatus != "") {
                                       echo "<h5>Warranty Status: " . $warrantyStatus . "</h5>";
                                    }
                                ?>
                            </div>
                            <div class="card-body">
                                <div class="basic-form dealer-form">
                                    <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>
									<span id="isQuote" class="d-none"><?php echo $isQuote;?></span>
									<h4>Customer Information</h4>
                                    <form name="dealerForm" id="warrantyForm" method="POST" action="">
                                    	<input type="hidden" name="warrantyID" value="<?php echo $warrantyID; ?>"/>
                                    	<input type="hidden" name="isQuote" value="<?php echo $isQuote; ?>"/>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Agreement Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" value="<?php echo $agreementDate; ?>" name="agreementDate" id="agreementDate">
												<span style="color: red;display: none;" id="agreementDateE">Please Select Agreement date..!</span>
                                            </div>

											<?php
											// Get locations associated with this dealer.
										$query = "SELECT a.Acct_ID, a.Acct_Nm, a.Prnt_Acct_ID FROM
											Acct a LEFT JOIN Bank_Dim b ON a.Acct_ID=b.Acct_ID WHERE
											(a.Acct_ID = " . $dealerID . " OR a.Prnt_Acct_ID=" . $dealerID . ")
											ORDER BY a.Prnt_Acct_ID ASC";
										$locationResult = $link->query($query);
										$numRows = mysqli_num_rows($locationResult);
										if ($numRows > 0) {
											?>
											<div class="form-group col-md-6">
												<h5 class="text-primary d-inline">Dealer Locations</h5>
												<?php
											if ($numRows > 1) {
												?>
													<select class="form-control default-select" name="Acct_ID" id="sel1">\n
														<?
														// output data of each row
													$loopCounter = 0;
													while ($row = mysqli_fetch_assoc($locationResult)) {
														$loopCounter++;
														?>
															<option value="<?php echo $row["Acct_ID"]; ?>"><?php echo $row["Acct_Nm"]; ?> <?php if ($row["Prnt_Acct_ID"] == "") { ?> (main location)<?php

                                                        } ?></option>\n
														<?php

												    }
												?>
													</select>
												<?php

										} else {
											$row = mysqli_fetch_assoc($locationResult);
											?>
													<p><?php echo $row["Acct_Nm"]; ?> (Primary)</p>
													<input type="hidden" name="Acct_ID" value="<?php echo $row["Acct_ID"]; ?>" />

												<?php

										}
										?>
												<span style="color:red;<?php if (isset($_SESSION['error_fmessage']) != '') { ?>display:block; <?php

										} else { ?>display:none; <?php

										} ?>"><?php if (isset($_SESSION['error_fmessage']) != '') {
										echo $_SESSION['error_fmessage'];
										} ?></span>
											</div>
											<?php

									} else {
										echo "<br />No locations still need banking information.";
									}
									?>

                                            <div class="form-group col-md-6">
                                                <label>Dealer Sales Agent</label>
												<?php
											$query = "SELECT * FROM Usr_Loc ul, Pers p, Email m, Tel t, Dlr_Loc_Dim dld WHERE ul.Dlr_Acct_ID in (
												SELECT Acct_ID FROM Acct WHERE Acct_ID=" . $dealerID . " OR Prnt_Acct_ID=" . $dealerID . ") AND
												ul.Dlr_Loc_Dim_ID=dld.Dlr_Loc_Dim_ID AND
												ul.Pers_ID = p.Pers_ID AND
												t.Pers_ID = p.Pers_ID AND
												m.Pers_ID = p.Pers_ID
												GROUP BY p.Pers_ID
												ORDER BY Pers_Last_Nm ASC";
											$personResult = $link->query($query);

											if ($personResult && mysqli_num_rows($personResult) > 0) {
												?>
													<select class="form-control default-select" name="dealerAgentID" id="sel1">\n
													<?
													  // output data of each row
												$loopCounter = 0;
												while ($row = mysqli_fetch_assoc($personResult)) {
													$loopCounter++;
													$Cntct_Prsn_For_Acct_Flg = $row["Cntct_Prsn_For_Acct_Flg"];
													?>
														<option value="<?php echo $row["Pers_ID"]; ?>"
														<?php
															if($dealerAgentID == $row["Pers_ID"]){
														?>
																selected="selected"
														<?php
															}else if (($dealerAgentID=="") && $Cntct_Prsn_For_Acct_Flg == "Y") { ?> selected="selected" <?php

													} ?>><?php echo $row["Pers_Frst_Nm"] . " " . $row["Pers_Last_Nm"]; ?> (<?php echo $row["Email_URL_Desc"]; ?>) (<?php echo $row["Dlr_Loc_Nm"]; ?>)<?php if ($Cntct_Prsn_For_Acct_Flg == "Y") { ?> (primary contact)<?php

													} ?></option>\n
													<?php

											}
											?>
													</select>
												<?php

										} else {
											echo "<br />No dealer agents found.";
										}
										?>

                                            </div>

                                            <div class="form-group col-md-6">
												&nbsp;
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Customer Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="customerName" id="customerName" value="<?php echo $customerName; ?>" />
												<span style="color: red;display: none;" id="customerNameE">Please Enter Customer Name..!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Customer Email <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="customerEmail" id="customerEmail" value="<?php echo $customerEmail; ?>" />
												<span style="color: red;display: none;" id="customerEmailE"></span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Customer Street Address <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="customerAddress" id="customerAddress" value="<?php echo $customerAddress; ?>" />
												<span style="color: red;display: none;" id="customerAddressE">Please Enter Customer Street Address..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Customer City <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="customerCity" id="customerCity" value="<?php echo $customerCity; ?>" />
												<span style="color: red;display: none;" id="customerCityE">Please Enter Customer City..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Customer State <span class="text-danger">*</span></label>
												<select class="form-control default-select" name="customerState" id="customerState">
													<option value="" selected>-- Select Customer State --</option>
												<?php
											if (mysqli_num_rows($customerStateResult) > 0) {
													// output data of each row
												$loopCounter = 0;
												while ($row = mysqli_fetch_assoc($customerStateResult)) {
													$loopCounter++;
													?>
														<option value="<?php echo $row["St_Prov_ID"]; ?>" <?php if ($customerState == $row["St_Prov_ID"]) {
														echo " selected ";
														} ?>><?php echo $row["St_Prov_Nm"]; ?></option>
												<?php

										}
									} ?>
												</select>
												<span style="color: red;display: none;" id="customerStateE">Please Select Customer State..!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Customer Zip Code</label>
                                                <input type="text" class="form-control" name="customerZip" id="customerZip" value="<?php echo $customerZip; ?>" />
												<span style="color: red;display: none;" id="customerZipE">Please Enter Customer Zip Code..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Customer Phone</label>
                                                <input type="text" class="form-control" name="customerPhone" id="customerPhone" value="<?php echo $customerPhone; ?>" />
												<span style="color: red;display: none;" id="customerPhoneE">Please Enter Customer Phone..!</span>
											</div>
                                            <div class="form-group col-md-6">
											<label>PO#</label>
                                                <input type="text" class="form-control" name="customerPO" id="customerPO" value="<?php echo $customerPO; ?>" />
												<span style="color: red;display: none;" id="customerPOE">Please Enter Customer PO Number..!</span>
                                            </div>
                                            <div class="form-group col-md-12">
												<h4>Vehicle Information</h4>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Vehicle Gross Weight <span class="text-danger">*</span></label>
												<select class="form-control default-select" id="grossWeight" name="vehicleGrossWeight" onchange="weightChange()">
													<option name="" value="">- Please Select -</option>
													<option name="type1" value="type 1" <?php $tempType = "";
																																															if ($Vehicle_Gross_Weight == "type 1") {
																																																echo " selected ";
																																																$tempType = "type 1";
																																															} ?> >less than 10000 GVW</option>
													<option name="type2" value="type 2" <?php if ($Vehicle_Gross_Weight == "type 2") {
																																																echo " selected ";
																																																$tempType = "type 2";
																																															} ?>>between 10001 and 29000 GVW</option>
													<option name="type3" value="type 3" <?php if ($Vehicle_Gross_Weight == "type 3") {
																																																echo " selected ";
																																																$tempType = "type 3";
																																															} ?>>greater than 29001 GVW</option>
												</select>
												<span style="color: red;display: none;" id="grossWeightE">Please Select Vehicle Gross Weight..!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Vehicle Type <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="vehicleType" name="vehicleType" value="<?php echo $tempType; ?>" readonly>
												<span style="color: red;display: none;" id="vehicleTypeE">Please Enter Vehicle Type..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Vehicle VIN <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
                                                <input type="text" class="form-control" name="vehicleVIN"  id="vehicleVIN" value="<?php echo $Vehicle_Vin_Number; ?>" />
												<span style="color: red;display: none;" id="vehicleVINE">Please Enter Vehicle VIN..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Vehicle Year <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="vehicleYear" id="vehicleYear" value="<?php echo $Vehicle_Year; ?>" />
												<span style="color: red;display: none;" id="vehicleYearE">Please Enter Vehicle Year..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Vehicle Make <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
                                                <input type="text" class="form-control" name="vehicleMake" id="vehicleMake" value="<?php echo $Vehicle_Make; ?>" />
												<span style="color: red;display: none;" id="vehicleMakeE">Please Enter Vehicle Make..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Vehicle Model <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
                                                <input type="text" class="form-control" name="vehicleModel" id="vehicleModel" value="<?php echo $Vehicle_Model; ?>" />
												<span style="color: red;display: none;" id="vehicleModelE">Please Enter Vehicle Model..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Engine Make <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
                                                <input type="text" class="form-control" name="engineMake" id="engineMake" value="<?php echo $Engine_Make; ?>" />
												<span style="color: red;display: none;" id="engineMakeE">Please Enter Engine Make..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Engine Model <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
                                                <input type="text" class="form-control" name="engineModel" id="engineModel" value="<?php echo $Engine_Model; ?>" />
												<span style="color: red;display: none;" id="engineModelE">Please Enter Engine Model..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Engine Serial # <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
                                                <input type="text" class="form-control" name="engineSerialNumber" id="engineSerialNumber" value="<?php echo $Engine_Serial; ?>" />
												<span style="color: red;display: none;" id="engineSerialNumberE">Please Enter Engine Serial #..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Transmission Make <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
                                                <input type="text" class="form-control" name="transmissionMake" id="transmissionMake" value="<?php echo $Transmission_Make; ?>" />
												<span style="color: red;display: none;" id="transmissionMakeE">Please Enter Transmission Make..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Transmission Model <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
                                                <input type="text" class="form-control" name="transmissionModel" id="transmissionModel" value="<?php echo $Transmission_Model; ?>" />
												<span style="color: red;display: none;" id="transmissionModelE">Please Enter Transmission Model..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Transmission Serial # <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
                                                <input type="text" class="form-control" name="transmissionSerialNumber" id="transmissionSerialNumber" value="<?php echo $Transmission_Serial; ?>" />
												<span style="color: red;display: none;" id="transmissionSerialNumberE">Please Enter Transmission Serial #..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Odometer Reading <span class="text-danger">*</span></label>
											<?php
											if ($Odometer_Miles_Or_KM == "Miles") {
												$Odometer_Reading = $Odometer_Reading_Miles;
											} else {
												$Odometer_Reading = $Odometer_Reading_Km;
											}
											?>
                                                <input type="text" class="form-control" name="odometerReading" id="odometerReading" value="<?php echo $Odometer_Reading; ?>" >
												<span style="color: red;display: none;" id="odometerReadingE">Please Enter Odometer Reading..!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Miles or KM? <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
												<div class="form-group mb-0">
													<label class="radio-inline mr-3"><input type="radio" value="miles" name="milesOrKM" <?php if ($Odometer_Miles_Or_KM != "KM") {
													echo " checked='checked' ";
													} ?>> Miles</label>
													<label class="radio-inline mr-3"><input type="radio" value="km" name="milesOrKM" <?php if ($Odometer_Miles_Or_KM == "KM") {
													echo " checked='checked' ";
													} ?>> KM</label>
												</div>
												<span style="color: red;display: none;" id="milesOrKME">Please Select Miles or KM?.!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>ECM Reading <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
												<?php
											if ($ECM_Miles_Or_KM == "Miles") {
												$ECM_Reading = $ECM_Reading_Miles;
											} else {
												$ECM_Reading = $ECM_Reading_Km;
											}
											?>
                                                <input type="text" class="form-control" name="ecmReading" id="ecmReading" value="<?php echo $ECM_Reading; ?>" />
												<span style="color: red;display: none;" id="ecmReadingE">Please Enter ECM Reading..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>ECM Miles or KM? <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
												<div class="form-group mb-0">
													<label class="radio-inline mr-3"><input type="radio" value="miles" name="ecmMilesOrKM" <?php if ($ECM_Miles_Or_KM != "KM") {
																																																																																																				echo " checked='checked' ";
																																																																																																			} ?>> ECM Miles</label>
													<label class="radio-inline mr-3"><input type="radio" value="km" name="ecmMilesOrKM" <?php if ($ECM_Miles_Or_KM == "KM") {
																																																																																																	echo " checked='checked' ";
																																																																																																} ?>> ECM KM</label>
												</div>
												<span style="color: red;display: none;" id="ecmMilesOrKME">Please Select ECM Miles or KM?.!</span>
                                            </div>
											<div class="form-group col-md-6">
                                                <label>Engine Hours <?php if($isQuote == 'N'){?> <span class="text-danger">*</span> <?php } ?></label>
                                                <input type="text" class="form-control" name="engineHours" id="engineHours" value="<?php echo $Engine_Hours; ?>" />
												<span style="color: red;display: none;" id="engineHoursE">Please Enter Engine Hours..!</span>
											</div>
											<div class="form-group col-md-6">
											</div>
                                            <div class="form-group col-md-6">
                                                <label>APU? <span class="text-danger type3Field">*</span></label>
												<div class="form-group mb-0">
													<label class="radio-inline mr-3"><input type="radio" value="Y" name="isAPU" <?php if ($APU_Flg == "Y") {
																																																																																									echo " checked='checked' ";
																																																																																								} ?>> Yes</label>
													<label class="radio-inline mr-3"><input type="radio" value="N" name="isAPU" <?php if ($APU_Flg == "N") {
																																																																																									echo " checked='checked' ";
																																																																																								} ?>> No</label>
												</div>
												<span style="color: red;display: none;" id="isAPUE">Please Select APU..!</span>
                                            </div>
                                            <div class="form-group col-md-6">
												&nbsp;
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>APU Make <span class="text-danger type3Field" ></span></label>
                                                <input type="text" class="form-control" name="apuMake" id="apuMake" value="<?php echo $APU_Engine_Make; ?>" />
												<span style="color: red;display: none;" id="apuMakeE">Please Enter APU Make..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>APU Model <span class="text-danger type3Field" ></span></label>
                                                <input type="text" class="form-control" name="apuModel" id="apuModel" value="<?php echo $APU_Engine_Model; ?>" />
												<span style="color: red;display: none;" id="apuModelE">Please Enter APU Model..!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>APU Year <span class="text-danger type3Field" ></span></label>
                                                <input type="text" class="form-control" name="apuYear" id="apuYear" value="<?php echo $APU_Engine_Year; ?>" />
												<span style="color: red;display: none;" id="apuYearE">Please Enter APU Year..!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>APU Serial # <span class="text-danger type3Field" ></span></span></label>
                                                <input type="text" class="form-control" name="apuSerialNumber" id="apuSerialNumber" value="<?php echo $APU_Engine_Serial; ?>" />
												<span style="color: red;display: none;" id="apuSerialNumberE">Please Enter APU Serial #..!</span>
											</div>
											<div class="form-group col-md-6">
                                                <label>APU Hours <span class="text-danger type3Field" ></span></span></label>
                                                <input type="text" class="form-control" name="apuHours" id="apuHours" value="<?php echo $APU_Hours; ?>" />
												<span style="color: red;display: none;" id="apuHoursE">Please Enter APU Hours..!</span>
											</div>
											<div class="form-group col-md-6">
                                                <label>Vehicle Description</label>
                                                <textarea class="form-control" name="vehicleDescription" id="vehicleDescription"><?php echo $Vehicle_Description; ?></textarea>
												<span style="color: red;display: none;" id="vehicleDescriptionE">Please Enter Vehicle Description..!</span>
											</div>
											<div class="form-group col-md-6">
                                                <label>Is Vehicle New? <span class="text-danger">*</span></label>
												<div class="form-group mb-0">
													<label class="radio-inline mr-3"><input type="radio" value="Y" name="isVehicleNew" <?php if ($Vehicle_New_Flag == "Y") {
																																																																																																				echo " checked='checked' ";
																																																																																																			} ?>> Yes</label>
													<label class="radio-inline mr-3"><input type="radio" value="N" name="isVehicleNew" <?php if ($Vehicle_New_Flag == "N") {
																																																																																																	echo " checked='checked' ";
																																																																																																} ?>> No</label>
												</div>
												<span style="color: red;display: none;" id="isVehicleNewE">Please Select Is Vehicle New?.!</span>
                                            </div>
                                            <div class="form-group col-md-12">
												<h4>Component Coverage</h4>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Tier Type?  <span class="text-danger">*</span></label>
												<div class="form-group mb-0">
													<label class="radio-inline mr-3"><input type="radio" value="S" name="vehicleTierType" <?php if ($Tier_Type == "S") {
																																																																																																			echo " checked='checked' ";
																																																																																																		} ?>> Squad</label>
													<label class="radio-inline mr-3"><input type="radio" value="B" name="vehicleTierType" <?php if ($Tier_Type == "B") {
																																																																																																			echo " checked='checked' ";
																																																																																																		} ?>> Battalion</label>
												</div>
												<span style="color: red;display: none;" id="vehicleTierTypeE">Please Select Tier Type.! <span class="text-danger">*</span></span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Apparatus Equipment Package? <span class="text-danger type2Field"></span></label>
												<div class="form-group mb-0">
													<label class="radio-inline mr-3"><input type="radio" value="Y" name="boltOnPackage" <?php if ($Apparatus_Equipment_Package == "Y") {
																																																																																																	echo " checked='checked' ";
																																																																																																} ?>> Yes</label>
													<label class="radio-inline mr-3"><input type="radio" value="N" name="boltOnPackage" <?php if ($Apparatus_Equipment_Package == "N") {
																																																																																																	echo " checked='checked' ";
																																																																																																} ?>> No</label>
												</div>
												<span style="color: red;display: none;" id="boltOnPackageE">Please Select Apparatus Equipment Package.!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Aerial Package? <span class="text-danger type2Field"></span></label>
												<div class="form-group mb-0">
													<label class="radio-inline mr-3"><input type="radio" value="Y" name="aerialPackage" <?php if ($Aerial_Package == "Y") {
																																																																																																	echo " checked='checked' ";
																																																																																																} ?>> Yes</label>
													<label class="radio-inline mr-3"><input type="radio" value="N" name="aerialPackage" <?php if ($Aerial_Package == "N") {
																																																																																																	echo " checked='checked' ";
																																																																																																} ?>> No</label>
												</div>
												<span style="color: red;display: none;" id="aerialPackageE">Please Select Aerial Package.!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Small Goods Package?</label>
												<div class="form-group mb-0">
													<label class="radio-inline mr-3"><input type="radio" value="Y" name="smallGoodsPackage" <?php if ($smallGoodsPackage == "Y") {
																																																																																																					echo " checked='checked' ";
																																																																																																				} ?>> Yes</label>
													<label class="radio-inline mr-3"><input type="radio" value="N" name="smallGoodsPackage" <?php if ($smallGoodsPackage == "N") {
																																																																																																					echo " checked='checked' ";
																																																																																																				} ?>> No</label>
												</div>
												<span style="color: red;display: none;" id="smallGoodsPackageE">Please Select Small Goods Package.!</span>
                                            </div>
                                            <div class="form-group col-md-12">
												<h4>Coverage Term <span class="text-danger">*</span></h4>
                                            </div>
                                            <div class="form-group col-md-6">
												<div class="form-group mb-0">
													<label class="radio-inline mr-3"><input type="radio" value="5" name="coverageTerm" <?php if ($Coverage_Term == "5") {
																																																																																																echo " checked='checked' ";
																																																																																															} ?>> 5 year</label>
													<label class="radio-inline mr-3"><input type="radio" value="7" name="coverageTerm" <?php if ($Coverage_Term == "7") {
																																																																																																echo " checked='checked' ";
																																																																																															} ?>> 7 year</label>
													<label class="radio-inline mr-3"><input type="radio" value="10" name="coverageTerm" <?php if ($Coverage_Term == "10") {
																																																																																																	echo " checked='checked' ";
																																																																																																} ?>> 10 year</label>
												</div>
												<span style="color: red;display: none;" id="coverageTermE">Please Select coverage Term.!</span>
                                            </div>
                                            <div class="form-group col-md-12">
												&nbsp;
                                            </div>

                                            <div class="form-group col-md-12">
												<h4>Dealer Information</h4>
                                            </div>

											<input type="hidden" name="customerSalesChannel" value="Outside Sales" />
											<?php
												// Remove this form field for now, but maintain a default.
												if(false){
											?>
                                            <div class="form-group col-md-6">

                                                <!-- <input type="text" class="form-control" name="customerSalesChannel" id="customerSalesChannel" value="<?php echo $customerSalesChannel; ?>" />
												<span style="color: red;display: none;" id="customerSalesChannelE">Please Enter Contract Sales Channel..!</span> -->

												<label>Contract Sales Channel</label>
												<select class="form-control default-select" id="customerSalesChannel" name="customerSalesChannel">
													<option value="Outside Sales" <?php if ($customerSalesChannel == "Outside Sales") { echo " selected ";} ?> >Outside Sales</option>
													<option value="Inside Sales" <?php if ($customerSalesChannel == "Inside Sales") { echo " selected ";} ?>>Inside Sales</option>
												</select>
												<span style="color: red;display: none;" id="customerSalesChannelE">Please Enter Contract Sales Channel..!</span>
											</div>
											<?php
												}
											?>
                                            <div class="form-group col-md-6">
                                                <label>Customer Services Own Vehicle Fleet?</label>
												<div class="form-group mb-0">
													<label class="radio-inline mr-3"><input type="radio" value="Y" name="Srvc_Veh_Flg"
													<?php
													if ($Srvc_Veh_Flg == "Y") {
														echo " checked='checked' ";
													} ?>>
														Yes
													</label>
													<label class="radio-inline mr-3"><input type="radio" value="N" name="Srvc_Veh_Flg"
													<?php
													if (($Srvc_Veh_Flg == "N") || ($Srvc_Veh_Flg == "")) {
														echo " checked='checked' ";
													} ?>>
														No
													</label>
												</div>
												<span style="color: red;display: none;" id="supplyPacketToBeShippedE">Please Select Supply Packet to be Shipped.!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Dealer Name</label>
												<p><?php echo $dealerName; ?></p>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Dealer Phone</label>
												<p><?php echo $dealerPhone; ?></p>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Dealer Address</label>
												<p><?php echo $dealerAddress1; ?></p>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Dealer City</label>
												<p><?php echo $dealerCity; ?></p>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Dealer State</label>
                                                <?php
                                                if($dealerState!=""){
													$dealerStateString = selectState($link,$dealerState);
												}
												?>
												<p><?php echo $dealerStateString; ?></p>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Dealer Zipcode</label>
												<p><?php echo $dealerZip; ?></p>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Dealer AR #</label>
												<p><?php echo $dealerARNumber; ?></p>
                                            </div>

                                            <div class="form-group col-md-6">
											<h4>Lien Holder Information</h4>
                                            </div>
											<div class="form-group col-md-6">
                                            </div>

											<input type="hidden" name="supplyPacketToBeShipped" value="N"/>
											<input type="hidden" name="supplyPacketShippedDate" value=""/>
											<input type="hidden" name="supplyPacketLeft" value="N"/>
											<?php
											// Remove all Supply Packet questions.
											if(false){
											?>
                                            <div class="form-group col-md-6">
                                                <label>Supply Packet to be Shipped</label>
												<div class="form-group mb-0">
													<label class="radio-inline mr-3"><input type="radio" value="Y" name="supplyPacketToBeShipped" <?php if ($Supply_Packet_To_Be_Shipped == "Y") {
																																																																																																											echo " checked='checked' ";
																																																																																																										} ?>>Yes</label>
													<label class="radio-inline mr-3"><input type="radio" value="N" name="supplyPacketToBeShipped" <?php if ($Supply_Packet_To_Be_Shipped == "N") {
																																																																																																											echo " checked='checked' ";
																																																																																																										} ?>>No</label>
												</div>
												<span style="color: red;display: none;" id="supplyPacketToBeShippedE">Please Select Supply Packet to be Shipped.!</span>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Supply Packet Left</label>
												<div class="form-group mb-0">
													<label class="radio-inline mr-3"><input type="radio" value="Y" name="supplyPacketLeft" <?php if ($Supply_Packet_Left == "Y") {
																																																																																																				echo " checked='checked' ";
																																																																																																			} ?>>Yes</label>
													<label class="radio-inline mr-3"><input type="radio" value="N" name="supplyPacketLeft" <?php if ($Supply_Packet_Left == "N") {
																																																																																																				echo " checked='checked' ";
																																																																																																			} ?>>No</label>
												</div>
												<span style="color: red;display: none;" id="supplyPacketLeftE">Please Select Supply Packet Left.!</span>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Supply Packet Shipped Date</label>
                                                <input type="date" class="form-control" name="supplyPacketShippedDate" id="supplyPacketShippedDate" value="<?php if($Supply_Packet_Shipped_Date!=""){$explodedDate=explode(" ",$Supply_Packet_Shipped_Date); echo $explodedDate[0];} ?>" >
												<span style="color: red;display: none;" id="supplyPacketShippedDateE">Please Select Supply Packet Shipped Date..!</span>
											</div>
											<?php
											}
											?>

                                            <div class="form-group col-md-6">
                                                <label>Lien Holder Name <?php if($isQuote == 'N'){?> <span class="text-danger"></span> <?php } ?></label>
                                                <input type="text" class="form-control"  value="<?php echo $Lien_Holder_Name; ?>" name="lienHolderName" id="lienHolderName" />
												<span style="color: red;display: none;" id="lienHolderNameE">Please Enter Lien Holder Name..!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Lien Holder Email <?php if($isQuote == 'N'){?> <span class="text-danger"></span> <?php } ?></label>
                                                <input type="email" class="form-control" value="<?php echo $Lien_Holder_Email; ?>" name="lienHolderEmail" id="lienHolderEmail" />
												<span style="color: red;display: none;" id="lienHolderEmailE">Please Enter Lien Holder Email..!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Lien Holder Address <?php if($isQuote == 'N'){?> <span class="text-danger"></span> <?php } ?></label>
                                                <input type="text" class="form-control" value="<?php echo $Lien_Holder_Address; ?>" name="lienHolderAddress" id="lienHolderAddress" />
												<span style="color: red;display: none;" id="lienHolderAddressE">Please Enter Lien Holder Address..!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Lien Holder City <?php if($isQuote == 'N'){?> <span class="text-danger"></span> <?php } ?></label>
                                                <input type="text" class="form-control" value="<?php echo $Lien_Holder_City; ?>" name="lienHolderCity" id="lienHolderCity" />
												<span style="color: red;display: none;" id="lienHolderCityE">Please Enter Lien Holder City..!</span>
											</div>
                                            <div class="form-group col-md-6">
                                                <label>Lien Holder State <?php if($isQuote == 'N'){?> <span class="text-danger"></span> <?php } ?></label>
												<select class="form-control default-select" name="lienHolderState" id="lienHolderState">
													<option value="" selected>-- Select Lien Holder State --</option>
												<?php
											if (mysqli_num_rows($lienStateResult) > 0) {
													// output data of each row
												$loopCounter = 0;
												while ($row = mysqli_fetch_assoc($lienStateResult)) {
													$loopCounter++;
													?>
														<option value=<?php echo $row["St_Prov_ID"] ?> <?php if ($Lien_Holder_State_Province == $row["St_Prov_ID"]) {
																																																													echo " selected ";
																																																												} ?>><?php echo $row["St_Prov_Nm"]; ?></option>
												<?php

										}
									} ?>
												</select>
												<span style="color: red;display: none;" id="lienHolderStateE">Please Select Lien Holder State..!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Lien Holder Zip  <?php if($isQuote == 'N'){?> <span class="text-danger"></span> <?php } ?></label>
                                                <input type="text" class="form-control" value="<?php echo $Lien_Holder_Postal_Code; ?>" name="lienHolderZip" id="lienHolderZip" />
												<span style="color: red;display: none;" id="lienHolderZipE">Please Select Lien Holder Zip Code..!</span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Lien Holder Phone Number  <?php if($isQuote == 'N'){?> <span class="text-danger"></span> <?php } ?></label>
                                                <input type="text" class="form-control" value="<?php echo $Lien_Holder_Phone_Number; ?>" name="lienHolderPhone" id="lienHolderPhone">
												<span style="color: red;display: none;" id="lienHolderPhoneE">Please Select Lien Holder Phone Number..!</span>
                                            </div>

<!---
                                            <div class="form-group col-md-12">
												<a href="small_goods_worksheet.php"><span class="badge badge-rounded badge-warning">Small Goods Worksheet</span></a>
                                            </div>
--->
<!---
                                            <div class="form-group col-md-6">
                                                <h6 class="font-weight-normal">Sign here</h6>
                                                <div class="signature"></div>
                                            </div>
                                            <div class="form-group col-md-6"></div>

                                            <div class="form-group col-md-6">
                                                <label>Retailer Name</label>
                                                <input type="text" class="form-control" name="retailerName">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Retailer Title</label>
                                                <input type="text" class="form-control" name="retailerTitle">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>signedOnDate</label>
                                                <input type="text" class="form-control" name="signedOnDate">
                                            </div>
--->
                                        </div>

                                        <button type="button" id="warrantyFormSubmit" value="submit" class="btn btn-primary">Submit</button>
                                    </form>
                                </div>
                            </div>
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


<!-- Dashboard 1 -->
<script src="./js/custom.min.js"></script>
<script src="./js/deznav-init.js"></script>

<script src="./js/jSignature/jSignature.min.js"></script>
<script src="./js/jSignature/jSignInit.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	$(document).ready(function(){

		$(".moveToW9").click(function(){
			window.location.href='dealer_w9.php';
		});


	//Validation for warranty/quote form submission

$('input').on('keypress', function(e) {
	if(e.which === 13)
	{

		var isQuote = $("#isQuote").text();
		var flag1 = 0;
		var flag2 = 0;
		var flag3 = 0;
		var flag4 = 0;
		var flag5 = 0;
		var flag6 = 0;
		var flag7 = 1; // customerZip
		var flag8 = 1; // customerPhone
		var flag9 = 0;
		var flag10 = 0;
		var flag11 = 1;
		var flag12 = 1;
		var flag13 = 1;
		var flag14 = 1;
		var flag15 = 1;
		var flag16 = 1;
		var flag17 = 1;
		var flag18 = 1;
		var flag19 = 1;
		var flag20 = 1;
		var flag21 = 1;
		var flag22 = 1;
		var flag23 = 1;
		var flag24 = 1;
		var flag25 = 1;
		var flag26 = 1;
		var flag27 = 0;
		var flag28 = 0;
		var flag29 = 0;
		var flag30 = 1; // lienHolderName
		var flag31 = 1; // lienHolderEmail
		var flag32 = 1; // lienHolderAddress
		var flag33 = 1; // lienHolderCity
		var flag34 = 1; // lienHolderState
		var flag35 = 1; // lienholderzip
		var flag36 = 1; // lienholderphone
		var flag37 = 0;
		var flag38 = 0;
		var flag39 = 0;
		var flag40 = 0;
		var flag41 = 0;
		var flag42 = 0;
		var flag43 = 0;
		var flag44 = 0;
		var flag45 = 0;
		var flag46 = 0;
		var flag47 = 0;
		var flag48 = 1;
		var flag49 = 0;
		var flag50 = 0;

		var Error_message = "";

		var customerEmailPattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
		var customerZipPattern = /^\b[9]{5}$|([9]{5}(-[9]{4}))\b$/i;
		var customerPhonePattern = /^\b[0-9]{3}\s[0-9]{3}(-[0-9]{4})\b$/i;
		var vehicleYearPattern = /^\b[0-9]{4}\b$/i;
		var meterReadingPattern = /^\d*[.]?\d*$/i;


		if ($("#agreementDate").val() == "") {
			Error_message += "<li> Agreement date is required</li>";
			$("#agreementDate").focus();
			$("#agreementDateE").css("display", "block");
		} else {
			$("#agreementDateE").css("display", "none");
			flag1 = 1;
		}

		if ($("#customerName").val() == "") {
			Error_message += "<li> Customer name is required</li>";
			$("#customerName").focus();
			$("#customerNameE").css("display", "block");
		} else {
			$("#customerNameE").css("display", "none");
			flag2 = 1;
		}

		if ($("#customerEmail").val() == "") {
			Error_message += "<li> Customer email is required</li>";
			$("#customerEmail").focus();
			$("#customerEmailE").css("display", "block");
			$("#customerEmailE").text('Please Enter Customer Email..!');

		} else if(!customerEmailPattern.test($("#customerEmail").val())){
			Error_message += "<li> Customer valid email is required</li>";
			$("#customerEmail").focus();
			$("#customerEmailE").css("display", "block");
		    $("#customerEmailE").text('Please Enter Valid Email..!');
		} else{
			$("#customerEmailE").css("display", "none");
			flag3 = 1;
		}

		if ($("#customerAddress").val() == "") {
			Error_message += "<li> Customer address is required</li>";
			$("#customerAddress").focus();
			$("#customerAddressE").css("display", "block");
		} else {
			$("#customerAddressE").css("display", "none");
			flag4 = 1;
		}

		if ($("#customerCity").val() == "") {
			Error_message += "<li> Customer city is required</li>";
			$("#customerCity").focus();
			$("#customerCityE").css("display", "block");
		} else {
			$("#customerCityE").css("display", "none");
			flag5 = 1;
		}

		if ($("#customerState").val() == "") {
			Error_message += "<li> Customer state is required</li>";
			$("#customerState").focus();
			$("#customerStateE").css("display", "block");
		} else {
			$("#customerStateE").css("display", "none");
			flag6 = 1;
		}
 /*
		if ($("#customerZip").val() == "") {
		$("#customerZip").focus();
		$("#customerZipE").css("display", "block");
		} else if(!customerZipPattern.test($("#customerZip").val())){
			$("#customerZipE").css("display", "block");
		    $("#customerZipE").text('Please Enter Valid Zip Code..!');
		} else {
		$("#customerZipE").css("display", "none");
		flag7 = 1;
		}

		if ($("#customerPhone").val() == "") {
		$("#customerPhone").focus();
		$("#customerPhoneE").css("display", "block");
		} else if(!customerPhonePattern.test($("#customerPhone").val())){
			$("#customerPhoneE").css("display", "block");
		    $("#customerPhoneE").text('Please Enter Valid Phone Number..!');
		}  else {
		$("#customerPhoneE").css("display", "none");
		flag8 = 1;
		}
 */
		if ($("#grossWeight").val() == "") {
			Error_message += "<li> Gross weight is required</li>";
			$("#grossWeight").focus();
			$("#grossWeightE").css("display", "block");
		} else {
			$("#grossWeightE").css("display", "none");
			flag9 = 1;
		}

		if ($("#vehicleType").val() == "") {
			Error_message += "<li> Vehicle type is required</li>";
			$("#vehicleType").focus();
			$("#vehicleTypeE").css("display", "block");
		} else {
			$("#vehicleTypeE").css("display", "none");
			flag10 = 1;
		}

		if ($("#vehicleVIN").val() == "" && isQuote == 'N') {
			Error_message += "<li> Vehicle VIN is required</li>";
			$("#vehicleVIN").focus();
			$("#vehicleVINE").css("display", "block");
			flag11 = 0;
		} else {
			$("#vehicleVINE").css("display", "none");
			flag11 = 1;
		}

		if ($("#vehicleYear").val() == "") {
			Error_message += "<li> Vehicle year is required</li>";
			$("#vehicleYear").focus();
			$("#vehicleYearE").css("display", "block");
			flag12 = 0;
		} else if($("#vehicleYear").val() != "" && !vehicleYearPattern.test($("#vehicleYear").val())){
			Error_message += "<li> Vehicle valid year is required</li>";
			$("#vehicleYearE").css("display", "block");
		    $("#vehicleYearE").text('Please Enter Valid Vehicle Year..!');
			flag12 = 0;
		}   else {
			$("#vehicleYearE").css("display", "none");
			flag12 = 1;
		}

		if ($("#vehicleMake").val() == "" && isQuote == 'N') {
			Error_message += "<li> Vehicle make is required</li>";
			$("#vehicleMake").focus();
			$("#vehicleMakeE").css("display", "block");
			flag13 = 0;
		} else {
			$("#vehicleMakeE").css("display", "none");
			flag13 = 1;
		}


		if ($("#vehicleModel").val() == "" && isQuote == 'N') {
			Error_message += "<li> Vehicle model is required</li>";
			$("#vehicleModel").focus();
			$("#vehicleModelE").css("display", "block");
			flag14 = 0;
		} else {
			$("#vehicleModelE").css("display", "none");
			flag14 = 1;
		}

		if ($("#engineMake").val() == "" && isQuote == 'N') {
			Error_message += "<li> Engine make is required</li>";
			$("#engineMake").focus();
			$("#engineMakeE").css("display", "block");
			flag15 = 0;
		} else {
			$("#engineMakeE").css("display", "none");
			flag15 = 1;
		}

		if ($("#engineModel").val() == "" && isQuote == 'N') {
			Error_message += "<li> Engine model is required</li>";
			$("#engineModel").focus();
			$("#engineModelE").css("display", "block");
			flag16 = 0;
		} else {
			$("#engineModelE").css("display", "none");
			flag16 = 1;
		}

		if ($("#engineSerialNumber").val() == "" && isQuote == 'N') {
			Error_message += "<li> Engine serial number is required</li>";
			$("#engineSerialNumber").focus();
			$("#engineSerialNumberE").css("display", "block");
			flag17 = 0;
		} else {
			$("#engineSerialNumberE").css("display", "none");
			flag17 = 1;
		}

		if ($("#transmissionMake").val() == ""  && isQuote == 'N') {
			Error_message += "<li> Transmission make is required</li>";
			$("#transmissionMake").focus();
			$("#transmissionMakeE").css("display", "block");
			flag18 = 0;
		} else {
			$("#transmissionMakeE").css("display", "none");
			flag18 = 1;
		}

		if ($("#transmissionModel").val() == ""  && isQuote == 'N') {
			Error_message += "<li> Transmission model is required</li>";
			$("#transmissionModel").focus();
			$("#transmissionModelE").css("display", "block");
			flag19 = 0;
		} else {
			$("#transmissionModelE").css("display", "none");
			flag19 = 1;
		}

		if ($("#transmissionSerialNumber").val() == ""  && isQuote == 'N') {
			Error_message += "<li> Transmission serial number is required</li>";
			$("#transmissionSerialNumber").focus();
			$("#transmissionSerialNumberE").css("display", "block");
			flag20 = 0;
		} else {
			$("#transmissionSerialNumberE").css("display", "none");
			flag20 = 1;
		}
		if ($("#odometerReading").val() == "") {
			Error_message += "<li> Odometer reading is required</li>";
			$("#odometerReading").focus();
			$("#odometerReadingE").css("display", "block");
			flag21 = 0;
		} else if($("#odometerReading").val() != "" && !meterReadingPattern.test($("#odometerReading").val())){
			Error_message += "<li> Odometer valid reading is required</li>";
			$("#odometerReadingE").css("display", "block");
		    $("#odometerReadingE").text('Please Enter Valid Odometer Reading..!');
			flag21 = 0;
		}  else {
			$("#odometerReadingE").css("display", "none");
			flag21 = 1;
		}

		if ($("#ecmReading").val() == "" && isQuote == 'N') {
			Error_message += "<li> ECM reading is required</li>";
			$("#ecmReading").focus();
			$("#ecmReadingE").css("display", "block");
			flag22 = 0;
		} else if($("#ecmReading").val() != "" && !meterReadingPattern.test($("#ecmReading").val())){
			Error_message += "<li> ECM valid reading is required</li>";
			$("#ecmReading").css("display", "block");
		    $("#ecmReading").text('Please Enter Valid ECM Reading..!');
			flag22 = 0;
		}   else {
			$("#ecmReadingE").css("display", "none");
			flag22 = 1;
		}
		if ($("#engineHours").val() == ""  && isQuote == 'N') {
			Error_message += "<li> Engine Hours required</li>";
			$("#engineHours").focus();
			$("#engineHoursE").css("display", "block");
			flag49 = 0;
		} else {
			$("#engineHoursE").css("display", "none");
			flag49 = 1;
		}

		if($("input:radio[name='isAPU']").is(":checked")) {
			$("#isAPUE").css("display", "none");
			flag37 = 1;
        } else {
			Error_message += "<li> APU is required</li>";
		   $("#isAPUE").css("display", "block");
		}

		if ($("#apuMake").val() == "" && $("input[type=radio][name=isAPU]:checked").val() == "Y") {
			Error_message += "<li> APU make is required</li>";
			$("#apuMake").focus();
			$("#apuMakeE").css("display", "block");
			flag23 = 0;
		} else {
			$("#apuMakeE").css("display", "none");
			flag23 = 1;
		}

		if ($("#apuModel").val() == ""  && $("input[type=radio][name=isAPU]:checked").val() == "Y") {
			Error_message += "<li> APU mdel is required</li>";
			$("#apuModel").focus();
			$("#apuModelE").css("display", "block");
			flag24 = 0;
		} else {
			$("#apuModelE").css("display", "none");
			flag24 = 1;
		}

		if ($("#apuYear").val() == ""  &&  $("input[type=radio][name=isAPU]:checked").val() == "Y") {
			Error_message += "<li> APU year is required</li>";
			$("#apuYear").focus();
			$("#apuYearE").css("display", "block");
			flag25 = 0;
		} else {
			$("#apuYearE").css("display", "none");
			flag25 = 1;
		}

		if ($("#apuSerialNumber").val() == "" && $("input[type=radio][name=isAPU]:checked").val() == "Y") {
			Error_message += "<li> APU serial number is required</li>";
			$("#apuSerialNumber").focus();
			$("#apuSerialNumberE").css("display", "block");
			flag26 = 0;
		} else {
			$("#apuSerialNumberE").css("display", "none");
			flag26 = 1;
		}

		if ($("#apuHours").val() == ""  && $("input[type=radio][name=isAPU]:checked").val() == "Y") {
			Error_message += "<li> APU Hours required</li>";
			$("#apuHours").focus();
			$("#apuHoursE").css("display", "block");
			flag50 = 0;
		} else {
			$("#apuHoursE").css("display", "none");
			flag50 = 1;
		}

		if ($("#customerSalesChannel").val() == "" && isQuote == 'N') {
			Error_message += "<li> Customer sales channel is required</li>";
			$("#customerSalesChannel").focus();
			$("#customerSalesChannelE").css("display", "block");
		} else {
			$("#customerSalesChannelE").css("display", "none");
			flag28 = 1;
		}
  /*

		if ($("#lienHolderName").val() == "" && isQuote == 'N') {
		$("#lienHolderName").focus();
		$("#lienHolderNameE").css("display", "block");
		} else {
		$("#lienHolderNameE").css("display", "none");
		flag30 = 1;
		}
 */

		 if ($("#lienHolderName").val() != "") {

			if ($("#lienHolderEmail").val() == "") {
			$("#lienHolderEmail").focus();
			$("#lienHolderEmailE").css("display", "block");
                        Error_message += "<li>Lien Holder Email is required</li>";
                          flag31 = 0;
			} else {
			$("#lienHolderEmailE").css("display", "none");
			flag31 = 1;
			}

			if ($("#lienHolderAddress").val() == "" ) {
			$("#lienHolderAddress").focus();
			$("#lienHolderAddressE").css("display", "block");
                        Error_message += "<li>Lien Holder Address is required</li>";
			flag32 = 0;
			} else {
			$("#lienHolderAddressE").css("display", "none");
			flag32 = 1;
			}

			if ($("#lienHolderCity").val() == "") {
			$("#lienHolderCity").focus();
			$("#lienHolderCityE").css("display", "block");
                        Error_message += "<li>Lien Holder City is required</li>";
			flag33 = 0;
			} else {
			$("#lienHolderCityE").css("display", "none");
			flag33 = 1;
			}

			if ($("#lienHolderState").val() == "") {
			$("#lienHolderState").focus();
			$("#lienHolderStateE").css("display", "block");
                        Error_message += "<li>Lien Holder State is required</li>";
			flag34 = 0;
			} else {
			$("#lienHolderStateE").css("display", "none");
			flag34 = 1;
			}

			if ($("#lienHolderZip").val() == "") {
			$("#lienHolderZip").focus();
			$("#lienHolderZipE").css("display", "block");
                        Error_message += "<li>Lien Holder Zip Code is required</li>";
                           flag35 = 0;
			} else {
			$("#lienHolderZipE").css("display", "none");
			flag35 = 1;
			}

			if ($("#lienHolderPhone").val() == "") {
			$("#lienHolderPhone").focus();
			$("#lienHolderPhoneE").css("display", "block");
                        Error_message += "<li>Lien Holder Phone Number is required</li>";
                            flag36 = 0;
			}  else {
			$("#lienHolderPhoneE").css("display", "none");
			flag36 = 1;
			}

	    }
            else
            {
                      $("#lienHolderEmailE").css("display", "none");
                      $("#lienHolderAddressE").css("display", "none");
                      $("#lienHolderCityE").css("display", "none");
                      $("#lienHolderStateE").css("display", "none");
                      $("#lienHolderZipE").css("display", "none");
                      $("#lienHolderPhoneE").css("display", "none");

            }



		if($("input:radio[name='milesOrKM']").is(":checked") || isQuote == 'Y') {
			$("#milesOrKME").css("display", "none");
			flag38 = 1;
        } else {
			Error_message += "<li> Miles or KME is required</li>";
		   $("#milesOrKME").css("display", "block");
		}

		if($("input:radio[name='ecmMilesOrKM']").is(":checked") || isQuote == 'Y') {
			$("#ecmMilesOrKME").css("display", "none");
			flag39 = 1;
        } else {
			Error_message += "<li> ECM miles or KME is required</li>";
		   $("#ecmMilesOrKME").css("display", "block");
		}
	
		if($("input:radio[name='isVehicleNew']").is(":checked")) {
			$("#isVehicleNewE").css("display", "none");
			flag40 = 1;
        } else {
			Error_message += "<li> Vehicle new is required</li>";
		   $("#isVehicleNewE").css("display", "block");
		}

		if($("input:radio[name='vehicleTierType']").is(":checked")) {
			$("#vehicleTierTypeE").css("display", "none");
			flag41 = 1;
        } else {
			Error_message += "<li> Vehicle tire type is required</li>";
		   $("#vehicleTierTypeE").css("display", "block");
		}

		if($("input:radio[name='boltOnPackage']").is(":checked") && ($("#vehicleType").val() == 'type 3' || $("#vehicleType").val() == 'type 2') || $("#vehicleType").val() == 'type 1') {
			$("#boltOnPackageE").css("display", "none");
			flag42 = 1;
        } else {
			Error_message += "<li> Bolt package is required</li>";
		   $("#boltOnPackageE").css("display", "block");
		}

		if($("input:radio[name='aerialPackage']").is(":checked") && ($("#vehicleType").val() == 'type 3' || $("#vehicleType").val() == 'type 2') || $("#vehicleType").val() == 'type 1') {
			$("#aerialPackageE").css("display", "none");
			flag43 = 1;
        } else {
			Error_message += "<li> Aerial package is required</li>";
		   $("#aerialPackageE").css("display", "block");
		}

		if($("input:radio[name='coverageTerm']").is(":checked")) {
			$("#coverageTermE").css("display", "none");
			flag45 = 1;
        } else {
			Error_message += "<li> Coverage term is required</li>";
		   $("#coverageTermE").css("display", "block");
		}

        //New Warranty Rules validation//

		    var currentYear = new Date().getFullYear();
            var vehicleDate = new Date($("#vehicleYear").val());
            // var day = vehicleDate.getDate();
            // var month = vehicleDate.getMonth() + 2;
            var year = vehicleDate.getFullYear() + 2;
            // if (day < 10) day = "0" + day;
            // if (month < 10) month = "0" + month;
			var NewVehicleDate = vehicleDate.getFullYear() + 2;
			var OldVehicleDate = vehicleDate.getFullYear() + 20;

	    if( $("#odometerReading").val() > 500 && $("input:radio[name='milesOrKM']:checked").val() == "miles" && $("input:radio[name='isVehicleNew']:checked").val() == 'Y' && NewVehicleDate < currentYear)
		{
			Error_message += "<li>Vehicle older than 2 years and greater than 500 miles.  This is a USED vehicle, not a new vehicle.</li>";
			flag48 = 0;

		} else if( $("#odometerReading").val() > 804 && $("input:radio[name='milesOrKM']:checked").val() == "km" && $("input:radio[name='isVehicleNew']:checked").val() == 'Y' && NewVehicleDate < currentYear)
		{
			Error_message += "<li>Vehicle older than 2 years and greater than 500 miles.  This is a USED vehicle, not a new vehicle.</li>";
			flag48 = 0;

		} else if( $("#odometerReading").val() > 500 && $("input:radio[name='milesOrKM']:checked").val() == "miles" && $("input:radio[name='isVehicleNew']:checked").val() == 'Y')
		{
			Error_message += "<li>Vehicle mileage greater than 500 miles.  This is a USED vehicle, not a new vehicle.</li>";
			flag48 = 0;

		} else if( $("#odometerReading").val() > 804 && $("input:radio[name='milesOrKM']:checked").val() == "km" && $("input:radio[name='isVehicleNew']:checked").val() == 'Y' )
		{
			Error_message += "<li>Vehicle mileage greater than 500 miles.  This is a USED vehicle, not a new vehicle.</li>";
			flag48 = 0;

		} else if(NewVehicleDate < currentYear && $("input:radio[name='isVehicleNew']:checked").val() == 'Y')
		{
			Error_message += "<li>Vehicle older than 2 years.  This is a USED vehicle, not a new vehicle.</li>";
			flag48 = 0;

		} else if($("input:radio[name='isVehicleNew']:checked").val() == 'N' && OldVehicleDate < currentYear)
		{
			Error_message += "<li>Vehicle older than 20 years and cannot be warrantied.</li>";
			flag48 = 0;

		} else if( $("input:radio[name='isVehicleNew']:checked").val() == 'N' && $("#grossWeight").val() == 'type 1' && $("#odometerReading").val() > 350000 && $("input:radio[name='milesOrKM']:checked").val() == "miles")
		{
			Error_message += "<li>Vehicle has more than 350,000 miles, and cannot be warrantied.</li>";
			flag48 = 0;

		} else if( $("input:radio[name='isVehicleNew']:checked").val() == 'N' && $("#grossWeight").val() == 'type 1' && $("#odometerReading").val() > 563270 && $("input:radio[name='milesOrKM']:checked").val() == "km")
		{
			Error_message += "<li>Vehicle has more than 563,270 kilometers, and cannot be warrantied.</li>";
			flag48 = 0;

		} else if( $("input:radio[name='isVehicleNew']:checked").val() == 'N' && $("#grossWeight").val() == 'type 2' && $("#odometerReading").val() > 500000 && $("input:radio[name='milesOrKM']:checked").val() == "miles")
		{
			Error_message += "<li>Vehicle has more than 500,000 miles, and cannot be warrantied.</li>";
			flag48 = 0;

		} else if( $("input:radio[name='isVehicleNew']:checked").val() == 'N' && $("#grossWeight").val() == 'type 2' && $("#odometerReading").val() > 804672 && $("input:radio[name='milesOrKM']:checked").val() == "km")
		{
			Error_message += "<li>Vehicle has more than 804,672 kilometers, and cannot be warrantied.</li>";
			flag48 = 0;

		} else if( $("input:radio[name='isVehicleNew']:checked").val() == 'N' && $("#grossWeight").val() == 'type 3' && $("#odometerReading").val() > 250000 && $("input:radio[name='milesOrKM']:checked").val() == "miles")
		{
			Error_message += "<li>Vehicle has more than 250,000 miles, and cannot be warrantied.</li>";
			flag48 = 0;

		} else if( $("input:radio[name='isVehicleNew']:checked").val() == 'N' && $("#grossWeight").val() == 'type 3' && $("#odometerReading").val() > 402336 && $("input:radio[name='milesOrKM']:checked").val() == "km")
		{
			Error_message += "<li>Vehicle has more than 402336 kilometers, and cannot be warrantied.</li>";
			flag48 = 0;

		} else{
			flag48 = 1;
		}
	}

});

	$("#warrantyFormSubmit").click(function () {

		var isQuote = $("#isQuote").text();
		var flag1 = 0;
		var flag2 = 0;
		var flag3 = 0;
		var flag4 = 0;
		var flag5 = 0;
		var flag6 = 0;
		var flag7 = 1; // customerZip
		var flag8 = 1; // customerPhone
		var flag9 = 0;
		var flag10 = 0;
		var flag11 = 1;
		var flag12 = 1;
		var flag13 = 1;
		var flag14 = 1;
		var flag15 = 1;
		var flag16 = 1;
		var flag17 = 1;
		var flag18 = 1;
		var flag19 = 1;
		var flag20 = 1;
		var flag21 = 1;
		var flag22 = 1;
		var flag23 = 1;
		var flag24 = 1;
		var flag25 = 1;
		var flag26 = 1;
		var flag27 = 0;
		var flag28 = 0;
		var flag29 = 0;
		var flag30 = 1; // lienHolderName
		var flag31 = 1; // lienHolderEmail
		var flag32 = 1; // lienHolderAddress
		var flag33 = 1; // lienHolderCity
		var flag34 = 1; // lienHolderState
		var flag35 = 1; // lienholderzip
		var flag36 = 1; // lienholderphone
		var flag37 = 0;
		var flag38 = 0;
		var flag39 = 0;
		var flag40 = 0;
		var flag41 = 0;
		var flag42 = 0;
		var flag43 = 0;
		var flag44 = 0;
		var flag45 = 0;
		var flag46 = 0;
		var flag47 = 0;
		var flag48 = 1;
		var flag49 = 0;
		var flag50 = 0;

		var Error_message = "";

		var customerEmailPattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
		var customerZipPattern = /^\b[9]{5}$|([9]{5}(-[9]{4}))\b$/i;
		var customerPhonePattern = /^\b[0-9]{3}\s[0-9]{3}(-[0-9]{4})\b$/i;
		var vehicleYearPattern = /^\b[0-9]{4}\b$/i;
		var meterReadingPattern = /^\d*[.]?\d*$/i;


		if ($("#agreementDate").val() == "") {
			Error_message += "<li> Agreement date is required</li>";
			$("#agreementDate").focus();
			$("#agreementDateE").css("display", "block");
		} else {
			$("#agreementDateE").css("display", "none");
			flag1 = 1;
		}

		if ($("#customerName").val() == "") {
			Error_message += "<li> Customer name is required</li>";
			$("#customerName").focus();
			$("#customerNameE").css("display", "block");
		} else {
			$("#customerNameE").css("display", "none");
			flag2 = 1;
		}

		if ($("#customerEmail").val() == "") {
			Error_message += "<li> Customer email is required</li>";
			$("#customerEmail").focus();
			$("#customerEmailE").css("display", "block");
			$("#customerEmailE").text('Please Enter Customer Email..!');

		} else if(!customerEmailPattern.test($("#customerEmail").val())){
			Error_message += "<li> Customer valid email is required</li>";
			$("#customerEmail").focus();
			$("#customerEmailE").css("display", "block");
		    $("#customerEmailE").text('Please Enter Valid Email..!');
		} else{
			$("#customerEmailE").css("display", "none");
			flag3 = 1;
		}

		if ($("#customerAddress").val() == "") {
			Error_message += "<li> Customer address is required</li>";
			$("#customerAddress").focus();
			$("#customerAddressE").css("display", "block");
		} else {
			$("#customerAddressE").css("display", "none");
			flag4 = 1;
		}

		if ($("#customerCity").val() == "") {
			Error_message += "<li> Customer city is required</li>";
			$("#customerCity").focus();
			$("#customerCityE").css("display", "block");
		} else {
			$("#customerCityE").css("display", "none");
			flag5 = 1;
		}

		if ($("#customerState").val() == "") {
			Error_message += "<li> Customer state is required</li>";
			$("#customerState").focus();
			$("#customerStateE").css("display", "block");
		} else {
			$("#customerStateE").css("display", "none");
			flag6 = 1;
		}
 /*
		if ($("#customerZip").val() == "") {
		$("#customerZip").focus();
		$("#customerZipE").css("display", "block");
		} else if(!customerZipPattern.test($("#customerZip").val())){
			$("#customerZipE").css("display", "block");
		    $("#customerZipE").text('Please Enter Valid Zip Code..!');
		} else {
		$("#customerZipE").css("display", "none");
		flag7 = 1;
		}

		if ($("#customerPhone").val() == "") {
		$("#customerPhone").focus();
		$("#customerPhoneE").css("display", "block");
		} else if(!customerPhonePattern.test($("#customerPhone").val())){
			$("#customerPhoneE").css("display", "block");
		    $("#customerPhoneE").text('Please Enter Valid Phone Number..!');
		}  else {
		$("#customerPhoneE").css("display", "none");
		flag8 = 1;
		}
 */
		if ($("#grossWeight").val() == "") {
			Error_message += "<li> Gross weight is required</li>";
			$("#grossWeight").focus();
			$("#grossWeightE").css("display", "block");
		} else {
			$("#grossWeightE").css("display", "none");
			flag9 = 1;
		}

		if ($("#vehicleType").val() == "") {
			Error_message += "<li> Vehicle type is required</li>";
			$("#vehicleType").focus();
			$("#vehicleTypeE").css("display", "block");
		} else {
			$("#vehicleTypeE").css("display", "none");
			flag10 = 1;
		}

		if ($("#vehicleVIN").val() == "" && isQuote == 'N') {
			Error_message += "<li> Vehicle VIN is required</li>";
			$("#vehicleVIN").focus();
			$("#vehicleVINE").css("display", "block");
			flag11 = 0;
		} else {
			$("#vehicleVINE").css("display", "none");
			flag11 = 1;
		}

		if ($("#vehicleYear").val() == "") {
			Error_message += "<li> Vehicle year is required</li>";
			$("#vehicleYear").focus();
			$("#vehicleYearE").css("display", "block");
			flag12 = 0;
		} else if($("#vehicleYear").val() != "" && !vehicleYearPattern.test($("#vehicleYear").val())){
			Error_message += "<li> Vehicle valid year is required</li>";
			$("#vehicleYearE").css("display", "block");
		    $("#vehicleYearE").text('Please Enter Valid Vehicle Year..!');
			flag12 = 0;
		}   else {
			$("#vehicleYearE").css("display", "none");
			flag12 = 1;
		}

		if ($("#vehicleMake").val() == "" && isQuote == 'N') {
			Error_message += "<li> Vehicle make is required</li>";
			$("#vehicleMake").focus();
			$("#vehicleMakeE").css("display", "block");
			flag13 = 0;
		} else {
			$("#vehicleMakeE").css("display", "none");
			flag13 = 1;
		}


		if ($("#vehicleModel").val() == "" && isQuote == 'N') {
			Error_message += "<li> Vehicle model is required</li>";
			$("#vehicleModel").focus();
			$("#vehicleModelE").css("display", "block");
			flag14 = 0;
		} else {
			$("#vehicleModelE").css("display", "none");
			flag14 = 1;
		}

		if ($("#engineMake").val() == "" && isQuote == 'N') {
			Error_message += "<li> Engine make is required</li>";
			$("#engineMake").focus();
			$("#engineMakeE").css("display", "block");
			flag15 = 0;
		} else {
			$("#engineMakeE").css("display", "none");
			flag15 = 1;
		}

		if ($("#engineModel").val() == "" && isQuote == 'N') {
			Error_message += "<li> Engine model is required</li>";
			$("#engineModel").focus();
			$("#engineModelE").css("display", "block");
			flag16 = 0;
		} else {
			$("#engineModelE").css("display", "none");
			flag16 = 1;
		}

		if ($("#engineSerialNumber").val() == "" && isQuote == 'N') {
			Error_message += "<li> Engine serial number is required</li>";
			$("#engineSerialNumber").focus();
			$("#engineSerialNumberE").css("display", "block");
			flag17 = 0;
		} else {
			$("#engineSerialNumberE").css("display", "none");
			flag17 = 1;
		}

		if ($("#transmissionMake").val() == ""  && isQuote == 'N') {
			Error_message += "<li> Transmission make is required</li>";
			$("#transmissionMake").focus();
			$("#transmissionMakeE").css("display", "block");
			flag18 = 0;
		} else {
			$("#transmissionMakeE").css("display", "none");
			flag18 = 1;
		}

		if ($("#transmissionModel").val() == ""  && isQuote == 'N') {
			Error_message += "<li> Transmission model is required</li>";
			$("#transmissionModel").focus();
			$("#transmissionModelE").css("display", "block");
			flag19 = 0;
		} else {
			$("#transmissionModelE").css("display", "none");
			flag19 = 1;
		}

		if ($("#transmissionSerialNumber").val() == ""  && isQuote == 'N') {
			Error_message += "<li> Transmission serial number is required</li>";
			$("#transmissionSerialNumber").focus();
			$("#transmissionSerialNumberE").css("display", "block");
			flag20 = 0;
		} else {
			$("#transmissionSerialNumberE").css("display", "none");
			flag20 = 1;
		}
		if ($("#odometerReading").val() == "") {
			Error_message += "<li> Odometer reading is required</li>";
			$("#odometerReading").focus();
			$("#odometerReadingE").css("display", "block");
			flag21 = 0;
		} else if($("#odometerReading").val() != "" && !meterReadingPattern.test($("#odometerReading").val())){
			Error_message += "<li> Odometer valid reading is required</li>";
			$("#odometerReadingE").css("display", "block");
		    $("#odometerReadingE").text('Please Enter Valid Odometer Reading..!');
			flag21 = 0;
		}  else {
			$("#odometerReadingE").css("display", "none");
			flag21 = 1;
		}

		if ($("#ecmReading").val() == "" && isQuote == 'N') {
			Error_message += "<li> ECM reading is required</li>";
			$("#ecmReading").focus();
			$("#ecmReadingE").css("display", "block");
			flag22 = 0;
		} else if($("#ecmReading").val() != "" && !meterReadingPattern.test($("#ecmReading").val())){
			Error_message += "<li> ECM valid reading is required</li>";
			$("#ecmReading").css("display", "block");
		    $("#ecmReading").text('Please Enter Valid ECM Reading..!');
			flag22 = 0;
		}   else {
			$("#ecmReadingE").css("display", "none");
			flag22 = 1;
		}
		if ($("#engineHours").val() == ""  && isQuote == 'N') {
			Error_message += "<li> Engine Hours required</li>";
			$("#engineHours").focus();
			$("#engineHoursE").css("display", "block");
			flag49 = 0;
		} else {
			$("#engineHoursE").css("display", "none");
			flag49 = 1;
		}

		if($("input:radio[name='isAPU']").is(":checked")) {
			$("#isAPUE").css("display", "none");
			flag37 = 1;
        } else {
			Error_message += "<li> APU is required</li>";
		   $("#isAPUE").css("display", "block");
		}

		if ($("#apuMake").val() == "" && $("input[type=radio][name=isAPU]:checked").val() == "Y") {
			Error_message += "<li> APU make is required</li>";
			$("#apuMake").focus();
			$("#apuMakeE").css("display", "block");
			flag23 = 0;
		} else {
			$("#apuMakeE").css("display", "none");
			flag23 = 1;
		}

		if ($("#apuModel").val() == ""  && $("input[type=radio][name=isAPU]:checked").val() == "Y") {
			Error_message += "<li> APU mdel is required</li>";
			$("#apuModel").focus();
			$("#apuModelE").css("display", "block");
			flag24 = 0;
		} else {
			$("#apuModelE").css("display", "none");
			flag24 = 1;
		}

		if ($("#apuYear").val() == ""  &&  $("input[type=radio][name=isAPU]:checked").val() == "Y") {
			Error_message += "<li> APU year is required</li>";
			$("#apuYear").focus();
			$("#apuYearE").css("display", "block");
			flag25 = 0;
		} else {
			$("#apuYearE").css("display", "none");
			flag25 = 1;
		}

		if ($("#apuSerialNumber").val() == "" && $("input[type=radio][name=isAPU]:checked").val() == "Y") {
			Error_message += "<li> APU serial number is required</li>";
			$("#apuSerialNumber").focus();
			$("#apuSerialNumberE").css("display", "block");
			flag26 = 0;
		} else {
			$("#apuSerialNumberE").css("display", "none");
			flag26 = 1;
		}

		if ($("#apuHours").val() == ""  && $("input[type=radio][name=isAPU]:checked").val() == "Y") {
			Error_message += "<li> APU Hours required</li>";
			$("#apuHours").focus();
			$("#apuHoursE").css("display", "block");
			flag50 = 0;
		} else {
			$("#apuHoursE").css("display", "none");
			flag50 = 1;
		}

		if ($("#customerSalesChannel").val() == "" && isQuote == 'N') {
			Error_message += "<li> Customer sales channel is required</li>";
			$("#customerSalesChannel").focus();
			$("#customerSalesChannelE").css("display", "block");
		} else {
			$("#customerSalesChannelE").css("display", "none");
			flag28 = 1;
		}
  /*

		if ($("#lienHolderName").val() == "" && isQuote == 'N') {
		$("#lienHolderName").focus();
		$("#lienHolderNameE").css("display", "block");
		} else {
		$("#lienHolderNameE").css("display", "none");
		flag30 = 1;
		}
 */

		 if ($("#lienHolderName").val() != "") {

			if ($("#lienHolderEmail").val() == "") {
			$("#lienHolderEmail").focus();
			$("#lienHolderEmailE").css("display", "block");
                        Error_message += "<li>Lien Holder Email is required</li>";
                          flag31 = 0;
			} else {
			$("#lienHolderEmailE").css("display", "none");
			flag31 = 1;
			}

			if ($("#lienHolderAddress").val() == "" ) {
			$("#lienHolderAddress").focus();
			$("#lienHolderAddressE").css("display", "block");
                        Error_message += "<li>Lien Holder Address is required</li>";
			flag32 = 0;
			} else {
			$("#lienHolderAddressE").css("display", "none");
			flag32 = 1;
			}

			if ($("#lienHolderCity").val() == "") {
			$("#lienHolderCity").focus();
			$("#lienHolderCityE").css("display", "block");
                        Error_message += "<li>Lien Holder City is required</li>";
			flag33 = 0;
			} else {
			$("#lienHolderCityE").css("display", "none");
			flag33 = 1;
			}

			if ($("#lienHolderState").val() == "") {
			$("#lienHolderState").focus();
			$("#lienHolderStateE").css("display", "block");
                        Error_message += "<li>Lien Holder State is required</li>";
			flag34 = 0;
			} else {
			$("#lienHolderStateE").css("display", "none");
			flag34 = 1;
			}

			if ($("#lienHolderZip").val() == "") {
			$("#lienHolderZip").focus();
			$("#lienHolderZipE").css("display", "block");
                        Error_message += "<li>Lien Holder Zip Code is required</li>";
                           flag35 = 0;
			} else {
			$("#lienHolderZipE").css("display", "none");
			flag35 = 1;
			}

			if ($("#lienHolderPhone").val() == "") {
			$("#lienHolderPhone").focus();
			$("#lienHolderPhoneE").css("display", "block");
                        Error_message += "<li>Lien Holder Phone Number is required</li>";
                            flag36 = 0;
			}  else {
			$("#lienHolderPhoneE").css("display", "none");
			flag36 = 1;
			}

	    }
            else
            {
                      $("#lienHolderEmailE").css("display", "none");
                      $("#lienHolderAddressE").css("display", "none");
                      $("#lienHolderCityE").css("display", "none");
                      $("#lienHolderStateE").css("display", "none");
                      $("#lienHolderZipE").css("display", "none");
                      $("#lienHolderPhoneE").css("display", "none");

            }



		if($("input:radio[name='milesOrKM']").is(":checked") || isQuote == 'Y') {
			$("#milesOrKME").css("display", "none");
			flag38 = 1;
        } else {
			Error_message += "<li> Miles or KME is required</li>";
		   $("#milesOrKME").css("display", "block");
		}

		if($("input:radio[name='ecmMilesOrKM']").is(":checked") || isQuote == 'Y') {
			$("#ecmMilesOrKME").css("display", "none");
			flag39 = 1;
        } else {
			Error_message += "<li> ECM miles or KME is required</li>";
		   $("#ecmMilesOrKME").css("display", "block");
		}
	
		if($("input:radio[name='isVehicleNew']").is(":checked")) {
			$("#isVehicleNewE").css("display", "none");
			flag40 = 1;
        } else {
			Error_message += "<li> Vehicle new is required</li>";
		   $("#isVehicleNewE").css("display", "block");
		}

		if($("input:radio[name='vehicleTierType']").is(":checked")) {
			$("#vehicleTierTypeE").css("display", "none");
			flag41 = 1;
        } else {
			Error_message += "<li> Vehicle tire type is required</li>";
		   $("#vehicleTierTypeE").css("display", "block");
		}

		if($("input:radio[name='boltOnPackage']").is(":checked") && ($("#vehicleType").val() == 'type 3' || $("#vehicleType").val() == 'type 2') || $("#vehicleType").val() == 'type 1') {
			$("#boltOnPackageE").css("display", "none");
			flag42 = 1;
        } else {
			Error_message += "<li> Bolt package is required</li>";
		   $("#boltOnPackageE").css("display", "block");
		}

		if($("input:radio[name='aerialPackage']").is(":checked") && ($("#vehicleType").val() == 'type 3' || $("#vehicleType").val() == 'type 2') || $("#vehicleType").val() == 'type 1') {
			$("#aerialPackageE").css("display", "none");
			flag43 = 1;
        } else {
			Error_message += "<li> Aerial package is required</li>";
		   $("#aerialPackageE").css("display", "block");
		}

		if($("input:radio[name='coverageTerm']").is(":checked")) {
			$("#coverageTermE").css("display", "none");
			flag45 = 1;
        } else {
			Error_message += "<li> Coverage term is required</li>";
		   $("#coverageTermE").css("display", "block");
		}

        //New Warranty Rules validation//

		    var currentYear = new Date().getFullYear();
            var vehicleDate = new Date($("#vehicleYear").val());
            // var day = vehicleDate.getDate();
            // var month = vehicleDate.getMonth() + 2;
            var year = vehicleDate.getFullYear() + 2;
            // if (day < 10) day = "0" + day;
            // if (month < 10) month = "0" + month;
			var NewVehicleDate = vehicleDate.getFullYear() + 2;
			var OldVehicleDate = vehicleDate.getFullYear() + 20;

	    if( $("#odometerReading").val() > 500 && $("input:radio[name='milesOrKM']:checked").val() == "miles" && $("input:radio[name='isVehicleNew']:checked").val() == 'Y' && NewVehicleDate < currentYear)
		{
			Error_message += "<li>Vehicle older than 2 years and greater than 500 miles.  This is a USED vehicle, not a new vehicle.</li>";
			flag48 = 0;

		} else if( $("#odometerReading").val() > 804 && $("input:radio[name='milesOrKM']:checked").val() == "km" && $("input:radio[name='isVehicleNew']:checked").val() == 'Y' && NewVehicleDate < currentYear)
		{
			Error_message += "<li>Vehicle older than 2 years and greater than 500 miles.  This is a USED vehicle, not a new vehicle.</li>";
			flag48 = 0;

		} else if( $("#odometerReading").val() > 500 && $("input:radio[name='milesOrKM']:checked").val() == "miles" && $("input:radio[name='isVehicleNew']:checked").val() == 'Y')
		{
			Error_message += "<li>Vehicle mileage greater than 500 miles.  This is a USED vehicle, not a new vehicle.</li>";
			flag48 = 0;

		} else if( $("#odometerReading").val() > 804 && $("input:radio[name='milesOrKM']:checked").val() == "km" && $("input:radio[name='isVehicleNew']:checked").val() == 'Y' )
		{
			Error_message += "<li>Vehicle mileage greater than 500 miles.  This is a USED vehicle, not a new vehicle.</li>";
			flag48 = 0;

		} else if(NewVehicleDate < currentYear && $("input:radio[name='isVehicleNew']:checked").val() == 'Y')
		{
			Error_message += "<li>Vehicle older than 2 years.  This is a USED vehicle, not a new vehicle.</li>";
			flag48 = 0;

		} else if($("input:radio[name='isVehicleNew']:checked").val() == 'N' && OldVehicleDate < currentYear)
		{
			Error_message += "<li>Vehicle older than 20 years and cannot be warrantied.</li>";
			flag48 = 0;

		} else if( $("input:radio[name='isVehicleNew']:checked").val() == 'N' && $("#grossWeight").val() == 'type 1' && $("#odometerReading").val() > 350000 && $("input:radio[name='milesOrKM']:checked").val() == "miles")
		{
			Error_message += "<li>Vehicle has more than 350,000 miles, and cannot be warrantied.</li>";
			flag48 = 0;

		} else if( $("input:radio[name='isVehicleNew']:checked").val() == 'N' && $("#grossWeight").val() == 'type 1' && $("#odometerReading").val() > 563270 && $("input:radio[name='milesOrKM']:checked").val() == "km")
		{
			Error_message += "<li>Vehicle has more than 563,270 kilometers, and cannot be warrantied.</li>";
			flag48 = 0;

		} else if( $("input:radio[name='isVehicleNew']:checked").val() == 'N' && $("#grossWeight").val() == 'type 2' && $("#odometerReading").val() > 500000 && $("input:radio[name='milesOrKM']:checked").val() == "miles")
		{
			Error_message += "<li>Vehicle has more than 500,000 miles, and cannot be warrantied.</li>";
			flag48 = 0;

		} else if( $("input:radio[name='isVehicleNew']:checked").val() == 'N' && $("#grossWeight").val() == 'type 2' && $("#odometerReading").val() > 804672 && $("input:radio[name='milesOrKM']:checked").val() == "km")
		{
			Error_message += "<li>Vehicle has more than 804,672 kilometers, and cannot be warrantied.</li>";
			flag48 = 0;

		} else if( $("input:radio[name='isVehicleNew']:checked").val() == 'N' && $("#grossWeight").val() == 'type 3' && $("#odometerReading").val() > 250000 && $("input:radio[name='milesOrKM']:checked").val() == "miles")
		{
			Error_message += "<li>Vehicle has more than 250,000 miles, and cannot be warrantied.</li>";
			flag48 = 0;

		} else if( $("input:radio[name='isVehicleNew']:checked").val() == 'N' && $("#grossWeight").val() == 'type 3' && $("#odometerReading").val() > 402336 && $("input:radio[name='milesOrKM']:checked").val() == "km")
		{
			Error_message += "<li>Vehicle has more than 402336 kilometers, and cannot be warrantied.</li>";
			flag48 = 0;

		} else{
			flag48 = 1;
		}

		if (
		   flag1 == 1 &&
		   flag2 == 1 &&
		   flag3 == 1 &&
		   flag4 == 1 &&
		   flag5 == 1 &&
		   flag6 == 1 &&
		   flag7 == 1 &&
		   flag8 == 1 &&
		   flag9 == 1 &&
		   flag10 == 1 &&
		   flag11 == 1 &&
		   flag12 == 1 &&
		   flag13 == 1 &&
		   flag14 == 1 &&
		   flag15 == 1 &&
		   flag16 == 1 &&
		   flag17 == 1 &&
		   flag18 == 1 &&
		   flag19 == 1 &&
		   flag20 == 1 &&
		   flag21 == 1 &&
		   flag22 == 1 &&
		   flag23 == 1 &&
		   flag24 == 1 &&
		   flag25 == 1 &&
		   flag26 == 1 &&
		   flag28 == 1 &&
		   flag30 == 1 &&
		   flag31 == 1 &&
		   flag32 == 1 &&
		   flag33 == 1 &&
		   flag34 == 1 &&
		   flag35 == 1 &&
		   flag36 == 1 &&
		   flag37 == 1 &&
		   flag38 == 1 &&
		   flag39 == 1 &&
		   flag40 == 1 &&
	       flag41 == 1 &&
		   flag42 == 1 &&
		   flag43 == 1 &&
		   flag45 == 1 &&
		   flag48 == 1 &&
		   flag49 == 1 &&
		   flag50 == 1
		) {

			$("#warrantyFormSubmit").prop("disabled", true);
			$("#warrantyForm").submit();
			setTimeout(function() {

			var redirect = '<%=Session["redirect"] %>';

			if(redirect)
			{
				if($("input[type=radio][name=smallGoodsPackage]:checked").val() == "Y")
				{
					if (isQuote == "Y") {
						window.location = 'small_goods_summary_worksheet.php';
					} else {
						window.location = 'small_goods_worksheet.php';
					}
				}
				else
				{
					if(isQuote == "Y")
					{
						window.location = 'warranty_pending.php?showQuotes=Y';
					}
					else
					{
						window.location = 'warranty_pending.php';
					}

				}

			}

         }, 5000);

		}
		else {

			Swal.fire({
				position: 'top-center',
				title: 'Please enter required fields',
				html: "<ul style='padding: 0 0 0 38px;text-align: left;color: red;'>"+Error_message+"</ul>",
				showConfirmButton: true,
				// timer: 1500
			});
		}
    });


	});

</script>

<script>

	function weightChange() {
		//alert(document.getElementById("grossWeight").value);
		document.getElementById("vehicleType").value = document.getElementById("grossWeight").value;
		var isQuote = $("#isQuote").text();
	    if (document.getElementById("grossWeight").value == 'type 3' || document.getElementById("grossWeight").value == 'type 2')
		{
			$(".type2Field").text('*');
		}
		else
		{
			$(".type2Field").text('');
		}

		if(document.getElementById("grossWeight").value == 'type 3' && isQuote == 'N')
		{
			$(".type3Field").text('*');
		}
		else
		{
			$(".type3Field").text('');
		}

		if(document.getElementById("grossWeight").value == 'type 1')
		{
			//APU
			$("input:radio[name='isAPU']").each(function(i) {
               $(this).attr('disabled', true);
			});
			$("input[name=isAPU][value='N']").prop("checked",true);
			    //$("input:radio[name=isAPU]").val('N');
			  	$("#apuMake").css("background", "#e8e8e8");
				$("#apuMake").prop("readonly", true);
				$("#apuModel").css("background", "#e8e8e8");
				$("#apuModel").prop("readonly", true);
				$("#apuYear").css("background", "#e8e8e8");
				$("#apuYear").prop("readonly", true);;
				$("#apuSerialNumber").css("background", "#e8e8e8");
				$("#apuSerialNumber").prop("readonly", true);
				$("#apuHours").css("background", "#e8e8e8");
				$("#apuHours").prop("readonly", true);

			//Aerial Package
			$("input:radio[name='aerialPackage']").each(function(i) {
               $(this).attr('disabled', true);
			});
			$("input[name=aerialPackage][value='N']").prop("checked",true);
            $("input:radio[name=aerialPackage]").val('N');

			//AEP
			$("input:radio[name='boltOnPackage']").each(function(i) {
               $(this).attr('disabled', true);
			});
			$("input[name=boltOnPackage][value='N']").prop("checked",true);
            $("input:radio[name=boltOnPackage]").val('N');


		}
		else if(document.getElementById("grossWeight").value == 'type 2')
		{
			//APU
			$("input:radio[name='isAPU']").each(function(i) {
               $(this).attr('disabled', true);
			});
			$("input[name=isAPU][value='N']").prop("checked",true);
			    //$("input:radio[name=isAPU]").val('N');
				$("#apuMake").css("background", "#e8e8e8");
				$("#apuMake").prop("readonly", true);
				$("#apuModel").css("background", "#e8e8e8");
				$("#apuModel").prop("readonly", true);
				$("#apuYear").css("background", "#e8e8e8");
				$("#apuYear").prop("readonly", true);;
				$("#apuSerialNumber").css("background", "#e8e8e8");
				$("#apuSerialNumber").prop("readonly", true);
				$("#apuHours").css("background", "#e8e8e8");
				$("#apuHours").prop("readonly", true);

			//Aerial Package
			$("input:radio[name='aerialPackage']").each(function(i) {
               $(this).attr('disabled', true);
			});
			$("input[name=aerialPackage][value='N']").prop("checked",true);
            $("input:radio[name=aerialPackage]").val('N');

			//AEP
			$("input:radio[name='boltOnPackage']").each(function(i) {
               $(this).attr('disabled', false);
			});
			$("input[name=boltOnPackage][value='N']").prop("checked",false);


		}
		else
		{
			//APU
			$("input:radio[name='isAPU']").each(function(i) {
               $(this).attr('disabled', false);
			});
			$("input:radio[name=isAPU]").prop("checked",false);
			$("#apuMake").css("background", "none");
			$("#apuMake").prop("readonly", false);
			$("#apuModel").css("background", "none");
			$("#apuModel").prop("readonly", false);
			$("#apuYear").css("background", "none");
			$("#apuYear").prop("readonly", false);
			$("#apuSerialNumber").css("background", "none");
			$("#apuSerialNumber").prop("readonly", false);
			$("#apuHours").css("background", "none");
			$("#apuHours").prop("readonly", false);
			//Aerial Packge
			$("input:radio[name='aerialPackage']").each(function(i) {
               $(this).attr('disabled', false);
			});
			$("input[name=aerialPackage][value='N']").prop("checked",false);

		}


	}

	$('input[type=radio][name=isAPU]').change(function() {

		if (this.value == 'Y') {
			$("#apuMake").css("background", "none");
			$("#apuMake").prop("readonly", false);
			$("#apuModel").css("background", "none");
			$("#apuModel").prop("readonly", false);
			$("#apuYear").css("background", "none");
			$("#apuYear").prop("readonly", false);
			$("#apuSerialNumber").css("background", "none");
			$("#apuSerialNumber").prop("readonly", false);
			$("#apuHours").css("background", "none");
			$("#apuHours").prop("readonly", false);

		}
		else if (this.value == 'N') {
			$("#apuMake").css("background", "#e8e8e8");
			$("#apuMake").prop("readonly", true);
			$("#apuModel").css("background", "#e8e8e8");
			$("#apuModel").prop("readonly", true);
			$("#apuYear").css("background", "#e8e8e8");
			$("#apuYear").prop("readonly", true);;
			$("#apuSerialNumber").css("background", "#e8e8e8");
			$("#apuSerialNumber").prop("readonly", true);
			$("#apuHours").css("background", "#e8e8e8");
			$("#apuHours").prop("readonly", true);

			$("#apuMakeE").css("display", "none");
			$("#apuModelE").css("display", "none");
			$("#apuYearE").css("display", "none");
			$("#apuSerialNumberE").css("display", "none");
			$("#apuHoursE").css("display", "none");
		}
	});


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

</body>
</html>