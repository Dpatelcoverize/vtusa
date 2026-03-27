<?php
//
// File: pdfHelper.php
// Author: Charles Parry
// Date: 8/13/2022
//
//
require_once 'vendor/autoload.php';

use Classes\PDFTemplate;
use Classes\GeneratePDF;

function createWarrantyPDF($link, $warrantyID, $isQuote, $isWrap = "N")
{
	//  This session stuff was causing huge problems!  Just use the passed-in argument..?!
	//	if(isset($_SESSION["warrantyID"]) && $_SESSION["warrantyID"]!="" && $_SESSION["warrantyID"]!=0){
	//		$warrantyID =  $_SESSION["warrantyID"];
	//	}

	// Sanity check the passed in value
	if (!is_numeric($warrantyID)) {
		return 0;
	}

	// Variables
	$dealerARNumber = "";
	$pers_ID = 0;
	$quantity = 1;

	// Include the main TCPDF library (search for installation path).
	require_once('tcpdf/examples/tcpdf_include.php');


	// Quick input validation.
	if (!is_numeric($warrantyID)) {
		return 0;
	}

	// Get a dealer ID from session.
	if (!(isset($_SESSION["id"]))) {
		return 0;
	} else {
		$dealerID = $_SESSION["id"];
		$adminID = $_SESSION["admin_id"];
	}


	// Get the Acct_ID
	$query = "SELECT * FROM Cntrct WHERE Cntrct_ID=" . $warrantyID . ";";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$Acct_ID = $row["Mfr_Acct_ID"];
	$dealerID = $Acct_ID;
	if (is_numeric($row["Quantity"]) && $row["Quantity"] > 0) {
		$quantity = $row["Quantity"];
	} else {
		$quantity = 1;
	}

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
	$dealerEmailQuery = "SELECT * FROM Email WHERE Pers_ID=" . $pers_ID . " AND
    Email_Prim_Flg='Y' AND Email_Type_Cd='Work'";
	$dealerEmailResult = $link->query($dealerEmailQuery);
	if (mysqli_num_rows($dealerEmailResult) > 0) {
		$row = $dealerEmailResult->fetch_assoc();
		$dealerEmail = $row["Email_URL_Desc"];
	} else {
		$dealerEmail = "not found";
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

		$customerStateResult = selectState($link, $dealerState);
	} else {
		$dealerAddress1 = "not found";
		$dealerAddress2 = "";
		$dealerCity = "";
		$dealerState = "";
		$dealerZip = "";

		$customerStateResult = "";
	}


	// Get AR Number
	$arQuery = "SELECT * FROM `Cntrct` c, Cntrct_Dim cd WHERE c.`Mfr_Acct_ID`=" . $dealerID . " AND
				cd.Cntrct_Type_Desc is NULL AND
				c.`Cntrct_Dim_ID`=cd.`Cntrct_Dim_ID`;";
	$arResult = $link->query($arQuery);
	if (mysqli_num_rows($arResult) > 0) {
		$arRow = mysqli_fetch_assoc($arResult);
		$dealerARNumber = $arRow["Assign_Rtlr_Nbr"];
	} else {
		$dealerARNumber = "";
	}

	// Get the dealer phone
	$phoneResult = selectTelByAcct($link, $dealerID, "Y", "Work");
	$row = $phoneResult->fetch_assoc();
	$dealerPhone = $row["Tel_Nbr"];

	// Get warranty detail
	if ($isQuote == "Y") {
		$Cntrct_Type_Cd = "WQ";
	} else {
		$Cntrct_Type_Cd = "WD";
	}
	$query =  "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_ID =" . $warrantyID . " AND c.Created_Warranty_ID is NULL AND c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND cd.Cntrct_Type_Cd='" . $Cntrct_Type_Cd . "' AND cd.Is_Deleted_Flg != 'Y' AND c.Veh_ID = v.Veh_ID";

	$result = $link->query($query);
	$warranty = $result->fetch_assoc();

	$agreementDate = $warranty["Contract_Date"];

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
	$APU_Hours  = $warranty["Veh_APU_Hours"];

	//Engine
	$Engine_Make = $warranty["Veh_Eng_Mk_Cd"];
	$Engine_Model = $warranty["Veh_Eng_Model_Cd"];
	$Engine_Year = $warranty["Veh_Eng_Ser_nbr"];
	$Engine_Serial = $warranty["Veh_Eng_Ser_nbr"];
	$Engine_Hours = $warranty["Veh_Eng_Hours"];
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
	$EVBC = $warranty["EVBC_Flg"];
	$EEC = $warranty["EEC_Flg"];
	$ACP = $warranty["ACP_Flg"];
	$HUDS = $warranty["HUDS_Flg"];
	$UCP = $warranty["UCP_Flg"];

	//Lien
	$Lien_Holder_Name = $warranty["Lien_Nme"];
	$Lien_Holder_Email = $warranty["Lien_Eml"];
	$Lien_Holder_Address = $warranty["Lien_Addrs"];
	$Lien_Holder_City = $warranty["Lien_Cty"];
	$lienState = $warranty["Lien_Ste"];
	$Lien_Holder_Postal_Code = $warranty["Lien_Pstl"];
	$Lien_Holder_Phone_Number = $warranty["Lien_Phn"];
	$Lien_Holder_State_Province = $lienState;


	$APU_Engine_Make = $warranty["Veh_APU_Eng_Mk_Cd"];
	$APU_Engine_Model = $warranty["Veh_APU_Eng_Model_Cd"];
	$APU_Engine_Year = $warranty["Veh_APU_Eng_Yr_Cd"];
	$APU_Engine_Serial = $warranty["Veh_APU_Eng_Ser_nbr"];
	$APU_Flg = $warranty["APU_Flg"];

	$Vehicle_New_Flag = $warranty["Veh_New_Flg"];  // check value on this, coming back as 'k'?
	$Vehicle_Description = $warranty["Veh_Desc"];
	$Tier_Type = $warranty["Cntrct_Lvl_Cd"];
	$Tier_Type_Desc = $warranty["Cntrct_Lvl_Desc"];

	$Apparatus_Equipment_Package = $warranty["AEP_Flg"];
	$Aerial_Package = $warranty["Aerial_Flg"];
	$Coverage_Term = $warranty["Cntrct_Term_Mnths_Nbr"];
	$smallGoodsPackage = $warranty["Small_Goods_Pkg_Flg"];
	$Srvc_Veh_Flg = $warranty["Srvc_Veh_Flg"];

	$wearable = $warranty['wearables_flag'];

	$agreeDate = '<u>' . date('m-d-Y', strtotime($agreementDate)) . '</u>';
	$assignDate = date('m-d-Y', strtotime($agreementDate));
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

	if ($lienState != "") {
		$Lien_Holder_State_Province_pdf = selectState($link, $lienState);
		if ($lienState == 0) {
			$Lien_Holder_State_Province_pdf = "";
		}
	} else {
		$lienState = "";
	}

	$type =  strtoupper($Vehicle_Gross_Weight);

	$pdf_Tier_Type = '';
	$pdf_Apparatus_Equipment_Package = '';
	$pdf_Aerial_Package = '';
	$apuEnginHtml = '';
	$breakPage = '';
	$breakPageType1 = '';
	$breakPageType2 = '';
	$breakPageType3 = '';
	$breakPageType3APU = '';
	if ($Tier_Type == 'S') {
		$pdf_Tier_Type = 'Squad';
	} elseif ($Tier_Type == 'B') {
		$pdf_Tier_Type = 'Battalion';
	}
	if ($AEP_Flg == 'Y') {
		$pdf_Apparatus_Equipment_Package = 'YES';
	} elseif ($AEP_Flg == 'N') {
		$pdf_Apparatus_Equipment_Package = 'NO';
	}
	if ($AER_Flg == 'Y') {
		$pdf_Aerial_Package = 'YES';
	} elseif ($AER_Flg == 'N') {
		$pdf_Aerial_Package = 'NO';
	}

	if ($APU_Flg == "Y") {
		$pdf_APU_Package = "YES";
	} else {
		$pdf_APU_Package = "NO";
	}


	if ($isQuote == "Y") {
		// create new PDF document

		$titleQuoteString = " QUOTE ";

		if ($isWrap == "N") {
			$title = $type . ' EMERGENCY VEHICLE COMPONENT BREAKDOWN LIMITED WARRANTY AGREEMENT ' . $titleQuoteString;
		} else {
			$title = "TYPE 3 EMERGENCY VEHICLE COMPONENT BREAKDOWN LIMITED WRAP WARRANTY AGREEMENT QUOTE";
		}

		$query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Cntrct_ID=" . $warrantyID . " AND
			c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND v.Veh_ID = c.Veh_ID";
		$cntrctResult = $link->query($query);
		$numRows = mysqli_num_rows($cntrctResult);
		if ($numRows > 0) {
			$row = mysqli_fetch_assoc($cntrctResult);

			// Check flags
			$AEP_Flg = $row["AEP_Flg"];
			$APU_Flg = $row["APU_Flg"];
			$Aerial_Flg = $row["Aerial_Flg"];
			$wearables_flag = $row["wearables_flag"];
			$EVBC_Flg = $row["EVBC_Flg"];
			$EEC_Flg = $row["EEC_Flg"];
			$ACP_Flg = $row["ACP_Flg"];
			$HUDS_Flg = $row["HUDS_Flg"];
			$UCP_Flg = $row["UCP_Flg"];
			$Small_Goods_Pkg_Flg = $row["Small_Goods_Pkg_Flg"];
			if (is_numeric($row["Veh_Model_Yr_Cd"])) {
				if (date("Y") - $row["Veh_Model_Yr_Cd"] > 14) {
					$Old_Flg = "Y";
				} else {
					$Old_Flg = "N";
				}
			} else {
				$Old_Flg = "N";
			}

			// Vehicle info
			$Veh_Type_Nbr = $row["Veh_Type_Nbr"];
			$Veh_Id_Nbr = $row["Veh_Id_Nbr"];

			// Pricing
			$Sales_Agt_Cost_Amt = $row["Sales_Agt_Cost_Amt"];
			$Cntrct_Lvl_Desc = $row["Cntrct_Lvl_Desc"];
			$Cntrct_Term_Mnths_Nbr = $row["Cntrct_Term_Mnths_Nbr"];
			$Dlr_Cost_Amt = $row["Dlr_Cost_Amt"];
			$Dlr_Mrkp_Actl_Amt = $row["Dlr_Mrkp_Actl_Amt"];
			$Dlr_Mrkp_Max_Amt = $row["Dlr_Mrkp_Max_Amt"];
			$MSRP_Amt = $row["MSRP_Amt"];

			$Dlr_Sml_Goods_Cst_Tot_Amt = $row["Dlr_Sml_Goods_Cst_Tot_Amt"];
			$Dlr_Sml_Goods_Max_Mrkp_Tot_Amt = $row["Dlr_Sml_Goods_Max_Mrkp_Tot_Amt"];
			$Sml_Goods_Tot_Amt = $row["Sml_Goods_Tot_Amt"];

			$Addl_Dlr_Mrkp_Actl_APU_Amt = $row["Addl_Dlr_Mrkp_Actl_APU_Amt"];
			$Addl_Dlr_Mrkp_Actl_AEP_Amt = $row["Addl_Dlr_Mrkp_Actl_AEP_Amt"];
			$Addl_Dlr_Mrkp_Actl_AER_Amt = $row["Addl_Dlr_Mrkp_Actl_AER_Amt"];
			$Addl_Dlr_Mrkp_Actl_WEARABLES_Amt = $row["Addl_Dlr_Mrkp_Actl_WEARABLES_Amt"];
			$Addl_Dlr_Mrkp_Actl_EVBC_Amt = $row["Addl_Dlr_Mrkp_Actl_EVBC_Amt"];
			$Addl_Dlr_Mrkp_Actl_EEC_Amt = $row["Addl_Dlr_Mrkp_Actl_EEC_Amt"];
			$Addl_Dlr_Mrkp_Actl_ACP_Amt = $row["Addl_Dlr_Mrkp_Actl_ACP_Amt"];
			$Addl_Dlr_Mrkp_Actl_HUDS_Amt = $row["Addl_Dlr_Mrkp_Actl_HUDS_Amt"];
			$Addl_Dlr_Mrkp_Actl_UCP_Amt = $row["Addl_Dlr_Mrkp_Actl_UCP_Amt"];

			$Tot_Dlr_Cost_Amt = $row["Tot_Dlr_Cost_Amt"];
			$Tot_Dlr_Mrkp_Act_Amt = $row["Tot_Dlr_Mrkp_Act_Amt"];
			$Tot_Dlr_Mrkp_Max_Amt = $row["Tot_Dlr_Mrkp_Max_Amt"];
			$Tot_MSRP_Amt = $row["Tot_MSRP_Amt"];
			//print_r($row);

			// Add on values
			$Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt = $row["Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt"];
			$Addl_Dlr_Mrkp_Actl_Amt = $row["Addl_Dlr_Mrkp_Actl_Amt"];
			$Addl_MSRP_Amt = $row["Addl_MSRP_Amt"];
		}

		if ($Coverage_Term == '0.5') {
			$Coverage_Time = '6 Months';
			$cellL = '6MTH';
		} else if ($Coverage_Term == '1') {
			$Coverage_Time = '1 Year';
			$cellL = '1YR';
		} else if ($Coverage_Term == '5') {
			$Coverage_Time = '5 Years';
		} else if ($Coverage_Term == '7') {
			$Coverage_Time = '7 Years';
		} else if ($Coverage_Term == '10') {
			$Coverage_Time = '10 Years';
		} else if ($Coverage_Term == '2') {
			$Coverage_Time = '2 Years';
		} else if ($Coverage_Term == '3') {
			$Coverage_Time = '3 Years';
		} else if ($Coverage_Term == '4') {
			$Coverage_Time = '4 Years';
		} else if ($Coverage_Term == '6') {
			$Coverage_Time = '6 Years';
		} else if ($Coverage_Term == '8') {
			$Coverage_Time = '8 Years';
		} else if ($Coverage_Term == '9') {
			$Coverage_Time = '9 Years';
		}

		$data = (object) [
			'type' => $type,
			'title' => $title,
			'titleQuoteString' => $titleQuoteString,
			'agreeDate' => $agreeDate,
			'customerName' => $customerName,
			'Vehicle_Vin_Number' => $Vehicle_Vin_Number,
			'customerEmail' => $customerEmail,
			'customerPhone' => $customerPhone,
			'customerAddress' => $customerAddress,
			'customerCity' => $customerCity,
			'customerStatePDF' => $customerStatePDF,
			'customerZip' => $customerZip,
			'Vehicle_Year' => $Vehicle_Year,
			'Vehicle_Make' => $Vehicle_Make,
			'Vehicle_Model' => $Vehicle_Model,
			'Engine_Make' => $Engine_Make,
			'Engine_Model' => $Engine_Model,
			'Engine_Serial' => $Engine_Serial,
			'Transmission_Make' => $Transmission_Make,
			'Transmission_Model' => $Transmission_Model,
			'Transmission_Serial' => $Transmission_Serial,
			'Odometer_Reading' => $Odometer_Reading,
			'Odometer_Miles_Or_KM' => $Odometer_Miles_Or_KM,
			'ECM_Reading' => $ECM_Reading,
			'ECM_Miles_Or_KM' => $ECM_Miles_Or_KM,
			'pdf_Tier_Type' => $pdf_Tier_Type,
			'AEP_Flg' => $AEP_Flg,
			'Addl_Dlr_Mrkp_Actl_AEP_Amt' => $Addl_Dlr_Mrkp_Actl_AEP_Amt,
			'APU_Flg' => $APU_Flg,
			'Addl_Dlr_Mrkp_Actl_APU_Amt' => $Addl_Dlr_Mrkp_Actl_APU_Amt,
			'AER_Flg' => $Aerial_Flg,
			'Addl_Dlr_Mrkp_Actl_AER_Amt' => $Addl_Dlr_Mrkp_Actl_AER_Amt,
			'wearable' => $wearables_flag,
			'Addl_Dlr_Mrkp_Actl_WEARABLES_Amt'=> $Addl_Dlr_Mrkp_Actl_WEARABLES_Amt,
			'EVBC_Flg' => $EVBC_Flg,
			'Addl_Dlr_Mrkp_Actl_EVBC_Amt' => $Addl_Dlr_Mrkp_Actl_EVBC_Amt,
			'EEC_Flg' => $EEC_Flg,
			'Addl_Dlr_Mrkp_Actl_EEC_Amt' => $Addl_Dlr_Mrkp_Actl_EEC_Amt,
			'ACP_Flg' => $ACP_Flg,
			'Addl_Dlr_Mrkp_Actl_ACP_Amt' => $Addl_Dlr_Mrkp_Actl_ACP_Amt,
			'HUDS_Flg' => $HUDS_Flg,
			'Addl_Dlr_Mrkp_Actl_HUDS_Amt' => $Addl_Dlr_Mrkp_Actl_HUDS_Amt,
			'UCP_Flg' => $UCP_Flg,
			'Addl_Dlr_Mrkp_Actl_UCP_Amt' => $Addl_Dlr_Mrkp_Actl_UCP_Amt,
			'Old_Flg' => $Old_Flg,
			'APU_Engine_Make' => $APU_Engine_Make,
			'APU_Engine_Model' => $APU_Engine_Model,
			'APU_Engine_Year' => $APU_Engine_Year,
			'APU_Engine_Serial' => $APU_Engine_Serial,
			'Coverage_Term' => $Coverage_Time,
			'dealerARNumber' => $dealerARNumber,
			'dealerAddress2' => $dealerAddress2,
			'dealerName' => $dealerName,
			'dealerPhone' => $dealerPhone,
			'dealerAddress1' => $dealerAddress1,
			'dealerCity' => $dealerCity,
			'dealerStatePDF' => $dealerStatePDF,
			'dealerZip' => $dealerZip,
			'Lien_Holder_Name' => $Lien_Holder_Name,
			'Lien_Holder_Phone_Number' => $Lien_Holder_Phone_Number,
			'Lien_Holder_Address' => $Lien_Holder_Address,
			'Lien_Holder_City' => $Lien_Holder_City,
			'Lien_Holder_State_Province_pdf' => $Lien_Holder_State_Province_pdf,
			'Lien_Holder_Postal_Code' => $Lien_Holder_Postal_Code,
			'quantity' => $quantity,
			'MSRP_Amt' => $MSRP_Amt,
			'Addl_MSRP_Amt' => $Addl_MSRP_Amt,
			'Sml_Goods_Tot_Amt' => $Sml_Goods_Tot_Amt,
			'Tot_MSRP_Amt' => $Tot_MSRP_Amt,
		];

		if ($Coverage_Term == 0.5 || $Coverage_Term == 1) {

			$footerHTML = (object) [
				'cellL' => $cellL . 'ALL EMV ENG v0425',
				'cellM' => 'This is a copyright protected document. © 2025 Need Help? 888-318-3472'
			];

			// if ($type == 'TYPE 1') {
			// 	include "PDFBoilerplate/6Mos1YrBoilerplateT1.php";
			// } else if ($type == 'TYPE 2') {
			// 	include "PDFBoilerplate/6Mos1YrBoilerplateT2.php";
			// } else if ($type == 'TYPE 3') {
			// 	include "PDFBoilerplate/6Mos1YrBoilerplateT3.php";
			// }
				include "PDFBoilerplate/quoteBoilerplate.php";

			$data->boilerplate = $boilerplate;

			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false, $footerHTML);

			$tpl = new PDFTemplate($link,$data);
			$html = $tpl->getQuoteTPL();
		} else { // Set font
			$footerHTML = (object) [
				'cellL' => 'ALL EMV ENG v0425',
				'cellM' => 'This is a copyright protected document. © 2025 Need Help? 888-318-3472'
			];

			// if ($isWrap == "N") {
			// 	include "warranty_boilerplate.php";
			// } else {
			// 	include "wrapQuote_boilerplate.php";
			// }
			include "PDFBoilerplate/quoteBoilerplate.php";
			$data->boilerplate = $boilerplate;

			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false, $footerHTML);

			$tpl = new PDFTemplate($link,$data);
			$html = $tpl->getQuoteTPL();


			$altColor = "lightgrey";
			$nextColor = "white";


			$priceTableHTML = '
	<table border="1" cellpadding="5" cellspacing="5" class="alnright">
		<tr style="background-color:#17166A; color:white; font-weight:bold;text-align:center;">
			<td>Vehicle ID</td>
			<td>Vehicle Type</td>
			<td>Term (Years)</td>
			<td>Coverage</td>
			<td>MSRP</td>
		</tr>
';

			$priceTableHTML .= '
	<tr style="background-color:' . $nextColor . '">
		<td style="text-align: center;">' . $Veh_Id_Nbr . '</td>
		<td style="text-align: center;">' . $Veh_Type_Nbr . '</td>
		<td style="text-align: center;">' . $Cntrct_Term_Mnths_Nbr . '</td>
		<td style="text-align: left;">' . $Cntrct_Lvl_Desc . '</td>
		<td><span class="Tier_Type_MSRP_Amt_Span">$' . number_format($MSRP_Amt, 0) . '</span></td>
	</tr>
';

			$priceTableHTML .= '
	<tr style="background-color:' . $nextColor . '">
		<td style="text-align: center;"></td>
		<td style="text-align: center;"></td>
		<td style="text-align: center;"></td>
		<td style="text-align: left;">Add-Ons (Ex. APU, AEP, Aerial)</td>
		<td><span class="Tier_Type_MSRP_Amt_Span">$' . number_format($Addl_MSRP_Amt, 0) . '</span></td>
	</tr>
';

			$priceTableHTML .= '
	<tr style="background-color:' . $nextColor . '">
		<td style="text-align: center;"></td>
		<td style="text-align: center;"></td>
		<td style="text-align: center;"></td>
		<td style="text-align: left;">Small Goods</td>
		<td><span class="Tier_Type_MSRP_Amt_Span">$' . number_format($Sml_Goods_Tot_Amt, 0) . '</span></td>
	</tr>
';

			$priceTableHTML .= '
	<tr style="background-color:' . $nextColor . '">
		<td style="text-align: center;"></td>
		<td style="text-align: center;"></td>
		<td style="text-align: center;"></td>
		<td style="text-align: left;">Total MSRP</td>
		<td><span class="Tier_Type_MSRP_Amt_Span">$' . number_format($Tot_MSRP_Amt, 0) . '</span></td>
	</tr>
';

			$priceTableHTML .= '</table>';
		}

		// set document information
		$pdf->setCreator(PDF_CREATOR);
		$pdf->setAuthor('Vital Trends');
		$pdf->setTitle('Vital Trends - Quote');
		$pdf->setSubject('Vital Trends - Quote');
		$pdf->setKeywords('W9, Vital, Data, Set, Guide, Quote');
		$pdf->setPrintHeader(false);

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

		// set header and footer fonts
		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

		// ---------------------------------------------------------

		// Close and output PDF document
		// This method has several options, check the source code documentation for more information.
		// Close and output PDF document
		// This method has several options, check the source code documentation for more information.

		$pdfFileName = str_replace(" ", "_", $customerName) . '_' . str_replace(" ", "_", $customerPhone) . '_' . time() . '.pdf';

		if ($isQuote == "Y") {
			$pdf->Output(__DIR__ . '/../uploads/quote_pdf/' . $pdfFileName, 'F');
		} else {
			$pdf->Output(__DIR__ . '/../uploads/warranty_pdf/' . $pdfFileName, 'F');
		}

		// Save Pddf into database
		if ($pers_ID == 0) {
			$primary_Contact_Person_id = 0;
		} else {
			$primary_Contact_Person_id = $pers_ID;
		}

		/*
$query = "SELECT Pers_ID FROM Pers WHERE Acct_ID=" . $dealerID . ";";
$result = $link->query($query);
if (mysqli_num_rows($result) > 0) {
$row = $result->fetch_assoc();
$primary_Contact_Person_id = $row['Pers_ID'];
}else{
$primary_Contact_Person_id = 0;
}
*/

		// Get the contract info
		$query = "SELECT cd.Cntrct_Dim_ID FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$contract_dim_ID = $row["Cntrct_Dim_ID"];

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
		$val4 = $warrantyID; // PARRY: I changed this to $warrantyID which is the Cntrct_ID not Cntrct_Dim_ID.
		if ($isQuote == "Y") {
			$val5 = '/uploads/quote_pdf/' . $pdfFileName;
		} else {
			$val5 = '/uploads/warranty_pdf/' . $pdfFileName;
		}
		mysqli_stmt_bind_param($stmt, "iiiis", $val1, $val2, $val3, $val4, $val5);


		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);
		if ($wearable == 'Y') {
			include "create_wearable_addendum_pdf.php";
		}
		// include "create_wear_pdf.php";
		//============================================================+
		// END OF FILE
		//============================================================+

		// End PDF Code here

	} else {
		$Mon6_12 = false;
		if ($Tier_Type == 'B') {
			$Tier_Type = 'BATTALION';
		} elseif ($Tier_Type == 'S') {
			$Tier_Type = 'SQUAD';
		}
		if ($Coverage_Term == '0.5') {
			$Coverage_Term = '6 Months';
			$Mon6_12 = true;
		} else if ($Coverage_Term == '1') {
			$Coverage_Term = '1 Year';
			$Mon6_12 = true;
		} else if ($Coverage_Term == '2') {
			$Coverage_Term = '2 Years';
			$Mon6_12 = false;
		} else if ($Coverage_Term == '3') {
			$Coverage_Term = '3 Years';
			$Mon6_12 = false;
		} else if ($Coverage_Term == '4') {
			$Coverage_Term = '4 Years';
			$Mon6_12 = false;
		} else if ($Coverage_Term == '5') {
			$Coverage_Term = '5 Years';
			$Mon6_12 = false;
		} else if ($Coverage_Term == '6') {
			$Coverage_Term = '6 Years';
			$Mon6_12 = false;
		} else if ($Coverage_Term == '7') {
			$Coverage_Term = '7 Years';
			$Mon6_12 = false;
		} else if ($Coverage_Term == '8') {
			$Coverage_Term = '8 Years';
			$Mon6_12 = false;
		} else if ($Coverage_Term == '9') {
			$Coverage_Term = '9 Years';
			$Mon6_12 = false;
		} else if ($Coverage_Term == '10') {
			$Coverage_Term = '10 Years';
			$Mon6_12 = false;
		}

		if ($type == "TYPE 1") {
			$Vehicle_Type = "Type 1";
		} else if ($type == "TYPE 2") {
			$Vehicle_Type = "Type 2";
		} else if ($type == "TYPE 3") {
			$Vehicle_Type = "Type 3";
		}  else if ($type == "TYPE 4") {
			$Vehicle_Type = "Type 4";
		}  else if ($type == "TYPE 5") {
			$Vehicle_Type = "Type 5";
		} else {
			// NOTE: what to do in case of default?
			$Vehicle_Type = "1";
		}

		$data = [
			'AGREEMENT DATE' => $assignDate,
			'CUSTOMER NAME' => $customerName,
			'CUSTOMER EMAIL' => $customerEmail,
			'CUSTOMER PH#' => $customerPhone,
			'CUSTOMER ADDRESS' => $customerAddress,
			'CUSTOMER CITY' => $customerCity,
			'CUSTOMER STATE/PROVINCE' => $customerStatePDF,
			'CUSTOMER ZIP/POSTAL CODE' => $customerZip,
			'VEHICLE TYPE' => $Vehicle_Type,
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
			'Apparatus' => $pdf_Apparatus_Equipment_Package,
			'Aerial' => $pdf_Aerial_Package,
			'APU' => $pdf_APU_Package,
			'APU YEAR' => $APU_Engine_Year,
			'APU MAKE' => $APU_Engine_Make,
			'APU MODEL' => $APU_Engine_Model,
			'APU SERIAL #' => $APU_Engine_Serial,
			'ENGINE HOURS' => $Engine_Hours,
			'APU HOURS' => $APU_Hours,
			'CUSTOMER  NAME printed' => $customerName,
			'AUTHORIZED RETAILER  NAME printed' => $dealerName,
			'CUSTOMER AGREEMENT #' => $Vehicle_Vin_Number,
			'WEARABLES' => $wearable,
			'EVBC' => $EVBC,
			'EEC' => $EEC,
			'ACP' => $ACP,
			'HUDS' => $HUDS,
			'UCP' => $UCP,
			'WRAP' => $isWrap,
		];

		$pdfFileName = str_replace(" ", "_", $customerName) . '_' . str_replace(" ", "_", $customerPhone) . '_' . time() . '.pdf';

		$pdf = new GeneratePDF;
		$pdf->generate($data, $pdfFileName, $Vehicle_Type, $isQuote, $isWrap, $Mon6_12);
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
		$val4 = $warrantyID; // PARRY: I changed this to $warrantyID which is the Cntrct_ID not Cntrct_Dim_ID.
		if ($isQuote == "Y") {
			$val5 = '/uploads/quote_pdf/' . $pdfFileName;
		} else {
			$val5 = '/uploads/warranty_pdf/' . $pdfFileName;
		}
		mysqli_stmt_bind_param($stmt, "iiiis", $val1, $val2, $val3, $val4, $val5);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);
		if ($wearable == 'Y') {
			include "create_wearable_addendum_pdf.php";
		}
		// include "create-addendum-pdf.php";
		// include "create_wear_pdf.php";
	}
}
