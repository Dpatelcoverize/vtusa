<?php
//
// File: pdfHelper.php
// Author: Charles Parry
// Date: 8/13/2022
//
//


function createWarrantyPDF($link,$warrantyID,$isQuote){

	// Variables
	$dealerARNumber = "";
	$pers_ID = 0;

	// Include the main TCPDF library (search for installation path).
	require_once('tcpdf/examples/tcpdf_include.php');


	// Quick input validation.
	if(!is_numeric($warrantyID)){
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
	$query = "SELECT * FROM Cntrct WHERE Cntrct_ID=" . $warrantyID. ";";
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

		$customerStateResult = selectState($link,$dealerState);
	}else{
		$dealerAddress1 = "not found";
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
	}else{
		$dealerARNumber = "";
	}

	// Get the dealer phone
	$phoneResult = selectTelByAcct($link, $dealerID, "Y", "Work");
	$row = $phoneResult->fetch_assoc();
	$dealerPhone = $row["Tel_Nbr"];

    // Get warranty detail
	if($isQuote=="Y"){
		$Cntrct_Type_Cd = "WQ";
	}else{
		$Cntrct_Type_Cd = "WD";
	}
   $query =  "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Mfr_Acct_ID=".$dealerID." AND c.Cntrct_ID =".$warrantyID." AND c.Created_Warranty_ID is NULL AND c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND cd.Cntrct_Type_Cd='".$Cntrct_Type_Cd."' AND cd.Is_Deleted_Flg != 'Y' AND c.Veh_ID = v.Veh_ID";

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

	$Vehicle_New_Flag = $warranty["Veh_New_Flg"];  // check value on this, coming back as 'k'?
	$Vehicle_Description = $warranty["Veh_Desc"];
	$Tier_Type = $warranty["Cntrct_Lvl_Cd"];
	$Tier_Type_Desc = $warranty["Cntrct_Lvl_Desc"];

	$Apparatus_Equipment_Package = $warranty["AEP_Flg"];
	$Aerial_Package = $warranty["Aerial_Flg"];
	$Coverage_Term = $warranty["Cntrct_Term_Mnths_Nbr"];
	$smallGoodsPackage = $warranty["Small_Goods_Pkg_Flg"];
	$Srvc_Veh_Flg = $warranty["Srvc_Veh_Flg"];



	// create new PDF document
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

  // set document information
	$pdf->setCreator(PDF_CREATOR);
	$pdf->setAuthor('Vital Trends');
	if($isQuote=="Y"){
		$pdf->setTitle('Vital Trends - Quote');
		$pdf->setSubject('Vital Trends - Quote');
	}else{
		$pdf->setTitle('Vital Trends - Warranty');
		$pdf->setSubject('Vital Trends - Warranty');
	}
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

	if($lienState!=""){
		$Lien_Holder_State_Province_pdf = selectState($link, $lienState);
		if($lienState==0){
			$Lien_Holder_State_Province_pdf="";
		}
	}else{
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
	if($Tier_Type == 'S')
	{
		$pdf_Tier_Type = 'Squad';
	}
	elseif($Tier_Type == 'B')
	{
		$pdf_Tier_Type = 'Battalion';
	}
	if($AEP_Flg == 'Y')
	{
		$pdf_Apparatus_Equipment_Package = 'Yes';
	}
	elseif($AEP_Flg == 'N')
	{
		$pdf_Apparatus_Equipment_Package = 'No';
	}
	if($AER_Flg== 'Y')
	{
		$pdf_Aerial_Package = 'Yes';
	}
	elseif($AER_Flg == 'N')
	{
		$pdf_Aerial_Package = 'No';
	}

	if($APU_Flg=="Y"){
		$pdf_APU_Package = "Yes";
	}else{
		$pdf_APU_Package = "No";
	}

	if($type == 'TYPE 1'){
		$componentCoverageHtml =
		'<tr>
		  <td>
			<table class="inner-full-width"  cellpadding="2" cellspacing="2" style="margin:0;">
				<tr>
					<th style="padding:7px 10px; font-size:13px;"><b>III.</b> <b>COMPONENT COVERAGE:</b> <span style="font-weight: 300;">See page 2 section A. for details.</span></th>
				</tr>
				<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> VEHICLE TYPE: ' . $type . '</label>
					</td>
				</tr>
				<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> TIER TYPE: ' . $pdf_Tier_Type . '</label>
					</td>
				</tr>
			</table>
		   </td>
	   </tr> ';
	   $breakPageType1 = '<br pagebreak="true" />';
	}
	elseif($type == 'TYPE 2'){
		$componentCoverageHtml =
		'<tr>
		  <td>
			<table class="inner-full-width"  cellpadding="2" cellspacing="2" style="margin:0;">
				<tr>
					<th style="padding:7px 10px; font-size:13px;"><b>III.</b> <b>COMPONENT COVERAGE:</b> <span style="font-weight: 300;">See page 2 section A. for details.</span></th>
				</tr>
				<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> VEHICLE TYPE: ' . $type . '</label>
					</td>
				</tr>
				<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> TIER TYPE: ' . $pdf_Tier_Type . '</label>
					</td>
				</tr>';
		if($AEP_Flg == "Y"){
			$pdf_Apparatus_Equipment_Package = "Yes";
			$componentCoverageHtml .= '
					<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> APPRATUS EQUIPMENT PACKAGE: ' .$pdf_Apparatus_Equipment_Package . '</label>
					</td>
				   </tr>';
		}

		$componentCoverageHtml .= '
			</table>
		   </td>
	   </tr>';

	   $breakPageType2 = '<br pagebreak="true" />';

	}
	elseif($type == 'TYPE 3'){
		$componentCoverageHtml =
		'<tr>
		  <td>
			<table class="inner-full-width"  cellpadding="2" cellspacing="2" style="margin:0;">
				<tr>
					<th style="padding:7px 10px; font-size:13px;"><b>III.</b> <b>COMPONENT COVERAGE:</b> <span style="font-weight: 300;">See page 2 section A. for details.</span></th>
				</tr>

				<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> VEHICLE TYPE: ' . $type . '</label>
					</td>
				</tr>

				<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> TIER TYPE: ' . $pdf_Tier_Type . '</label>
					</td>
				</tr>

		';
		if($APU_Flg == "Y"){
			$componentCoverageHtml .= '
				<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> APU PACKAGE: '.$pdf_APU_Package.'</label>
					</td>
				</tr>
				';
				$breakPageType3APU = '<br pagebreak="true" />';
		}
		else
		{
			$breakPageType3 = '<br pagebreak="true" />';
		}

		if($Apparatus_Equipment_Package == "Y"){
			$pdf_Apparatus_Equipment_Package = "Yes";
			$componentCoverageHtml .= '
				<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> APPRATUS EQUIPMENT PACKAGE: ' .$pdf_Apparatus_Equipment_Package . '</label>
					</td>
				</tr>
			';
		}

		if($Aerial_Package == "Y"){
			$componentCoverageHtml .= '
				<tr>
					<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
						<label><input type="checkbox" name=""> AERIAL PACKAGE: ' .$pdf_Aerial_Package . '</label>
					</td>
				</tr>
			';
		}

		$componentCoverageHtml .= '
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

	}

	include "warranty_boilerplate.php";

	// Prepare some variable text for the header.
	if($isQuote=="Y"){
		$titleQuoteString = " QUOTE ";
	}else{
		$titleQuoteString = "";
	}

	if($isQuote=="Y"){
		$titleQuoteString = " QUOTE ";
	}else{
		//$titleQuoteString = '<tr><td colspan="3" align="center"><b>SUBMIT THIS WARRANTY IMMEDIATELY VIA: <span class="color-blue">TRUNORTH GLOBAL DEALER PORTAL OR WARRANTY.MYTRUNORTH.COM</span></b></td></tr>';
		$titleQuoteString = " Warranty ";
	}




	$query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Cntrct_ID=".$warrantyID." AND
			  c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND v.Veh_ID = c.Veh_ID";
	$cntrctResult = $link->query($query);
	$numRows = mysqli_num_rows($cntrctResult);
	if ($numRows > 0) {
		$row = mysqli_fetch_assoc($cntrctResult);

		// Check flags
		$AEP_Flg = $row["AEP_Flg"];
		$APU_Flg = $row["APU_Flg"];
		$Aerial_Flg = $row["Aerial_Flg"];
		$Small_Goods_Pkg_Flg = $row["Small_Goods_Pkg_Flg"];
		if(is_numeric($row["Veh_Model_Yr_Cd"])){
			if(date("Y")-$row["Veh_Model_Yr_Cd"] > 14){
				$Old_Flg = "Y";
			}else{
				$Old_Flg = "N";
			}
		}else{
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
		<tr style="background-color:'.$nextColor.'">
			<td style="text-align: center;">'.$Veh_Id_Nbr.'</td>
			<td style="text-align: center;">'.$Veh_Type_Nbr.'</td>
			<td style="text-align: center;">'.$Cntrct_Term_Mnths_Nbr.'</td>
			<td style="text-align: left;">'.$Cntrct_Lvl_Desc.'</td>
			<td><span class="Tier_Type_MSRP_Amt_Span">$'.number_format($MSRP_Amt,0).'</span></td>
		</tr>
	';

	$priceTableHTML .= '
		<tr style="background-color:'.$nextColor.'">
			<td style="text-align: center;"></td>
			<td style="text-align: center;"></td>
			<td style="text-align: center;"></td>
			<td style="text-align: left;">Add-Ons (Ex. APU, AEP, Aerial)</td>
			<td><span class="Tier_Type_MSRP_Amt_Span">$'.number_format($Addl_MSRP_Amt,0).'</span></td>
		</tr>
	';

	$priceTableHTML .= '
		<tr style="background-color:'.$nextColor.'">
			<td style="text-align: center;"></td>
			<td style="text-align: center;"></td>
			<td style="text-align: center;"></td>
			<td style="text-align: left;">Small Goods</td>
			<td><span class="Tier_Type_MSRP_Amt_Span">$'.number_format($Sml_Goods_Tot_Amt,0).'</span></td>
		</tr>
	';

	$priceTableHTML .= '
		<tr style="background-color:'.$nextColor.'">
			<td style="text-align: center;"></td>
			<td style="text-align: center;"></td>
			<td style="text-align: center;"></td>
			<td style="text-align: left;">Total MSRP</td>
			<td><span class="Tier_Type_MSRP_Amt_Span">$'.number_format($Tot_MSRP_Amt,0).'</span></td>
		</tr>
	';

	$priceTableHTML .= '</table>';
/*
echo "priceTableHTML=".$priceTableHTML;
die();
*/

	$html = '
	<html lang="en">
	<style>
	@page {
	  margin:0;
	}
	table.main-table {
		text-align: center;
		padding: 17px 0 17px 0;
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
											<td class="head-text-lg" align="center" style="font-size: 18px;">'.$type.' EMERGENCY VEHICLE COMPONENT BREAKDOWN LIMITED WARRANTY AGREEMENT '.$titleQuoteString.'</td>
										</tr>
										<tr>
											<td class="head-text" align="center" style="font-size: 13px;">Please call our claims hotline @ 800-903-7489 ext. 820, immediately upone noticing any unusual mechanical issues concerning the vehicle listed below.
											</td>
										</tr>
									</table>
								</td>
							</tr>
							'.$titleQuoteString.'
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
								<td colspan="5" style="padding:7px 10px; font-size:13px;">'.$customerName.'</td>
							</tr>
							<tr>
								<td style="padding:7px 10px; font-size:13px;"><b>EMAIL:</b></td>
								<td colspan="3" style="padding:7px 10px; font-size:13px;">'.$customerEmail.'</td>
								<td style="padding:7px 10px; font-size:13px;"><b>PH#:</b></td>
								<td style="padding:7px 10px; font-size:13px;">'.$customerPhone.'</td>
							</tr>
							<tr>
								<td style="padding:7px 10px; font-size:13px;"><b>ADDRESS:</b></td>
								<td colspan="5" style="padding:7px 10px; font-size:13px;">'.$customerAddress.'</td>
							</tr>
							<tr>
								<td class="border-bottom-none" style="padding:7px 10px; font-size:13px;"><b>City:</b></td>
								<td class="border-bottom-none" style="padding:7px 10px; font-size:13px;">'.$customerCity.'</td>
								<td class="border-bottom-none" style="padding:7px 10px; font-size:13px;"><b>State/Province:</b></td>
								<td class="border-bottom-none" style="padding:7px 10px; font-size:13px;">'. $customerStatePDF.'</td>
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
								<td style="padding:7px 10px; font-size:13px;" colspan="3"><b>FULL VIN: '.$Vehicle_Vin_Number.'</b></td>
								<td style="padding:7px 10px; font-size:13px;"></td>
								<td style="padding:7px 10px; font-size:13px;"><b></b></td>
								<td style="padding:7px 10px; font-size:13px;"></td>
							</tr>
							<tr>
								<td style="padding:7px 10px; font-size:13px;"><b>VEHICLE:</b></td>
								<td style="padding:7px 10px; font-size:13px;">Year: '.$Vehicle_Year.'</td>
								<td style="padding:7px 10px; font-size:13px;"><b>MAKE: '.$Vehicle_Make.'</b></td>
								<td style="padding:7px 10px; font-size:13px;"></td>
								<td><b>MODEL:</b></td>
								<td>'.$Vehicle_Model.'</td>
							</tr>
							<tr>
								<td style="padding:7px 10px; font-size:13px;"><b>ENGINE:</b></td>
								<td style="padding:7px 10px; font-size:13px;">MAKE: '.$Engine_Make.'</td>
								<td style="padding:7px 10px; font-size:13px;"><b>MODEL:</b></td>
								<td style="padding:7px 10px; font-size:13px;">'.$Engine_Model.'</td>
								<td style="padding:7px 10px; font-size:13px;"><b>SERIAL#:</b></td>
								<td style="padding:7px 10px; font-size:13px;">'.$Engine_Serial.'</td>
							</tr>
							<tr>
								<td style="padding:7px 10px; font-size:13px;"><b>TRANSMISSION:</b></td>
								<td style="padding:7px 10px; font-size:13px;">MAKE: '. $Transmission_Make .'</td>
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
							';

							if($APU_Flg=="Y"){
								$html .= $apuEnginHtml;
							}

							$html .= '
						</table>
					</td>
				</tr>
				'.$componentCoverageHtml.'
				'.$breakPageType3APU.'
				<tr>
					<td>
						<table class="inner-full-width"  cellpadding="2" cellspacing="2" style="margin:0;">
							<tr>
								<th style="padding:7px 10px; font-size:13px;"><b>IV.</b> <b>COVERAGE TIME:</b> <span style="font-weight: 300;">The warranty period begins on the Agreement Date Listed above and expires when either the time selected has ended or the unaltered ECM/ECU reaches the mileage/km/hours term limit, whichever occurs first.</span></th>
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
				'.$breakPageType1.'
				'.$breakPageType2.'
				'.$breakPageType3.'
				<tr>
					<td>
						<table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
							<tr>
								<th style="padding:7px 10px; font-size:13px;" colspan="2"><b>V.</b> <b>RETAILER INFORMATION:</b></th>
								<th style="padding:7px 10px; font-size:13px;"><b>AR#:</b></th>
								<th style="padding:7px 10px; font-size:13px;">'.$dealerARNumber.'</th>
								<th style="padding:7px 10px; font-size:13px;">P0#:</th>
								<th style="padding:7px 10px; font-size:13px;">'.$dealerAddress2.'</th>
							</tr>
							<tr>
								<td colspan="2" style="padding:7px 10px; font-size:13px;"><b>RETAILER NAME:</b></td>
								<td colspan="2" style="padding:7px 10px; font-size:13px;">' . $dealerName . '</td>
								<td style="padding:7px 10px; font-size:13px;"><b>PH#:</b></td>
								<td style="padding:7px 10px; font-size:13px;">' . $dealerPhone . '</td>
							</tr>
							<tr>
								<td colspan="2" style="padding:7px 10px; font-size:13px;"><b>STREET ADDRESS: </b></td>
								<td style="padding:7px 10px; font-size:13px;" colspan="4">' . $dealerAddress1 . '</td>
							</tr>
							<tr>
								<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>City:</b></td>
								<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">'.$dealerCity.'</td>
								<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>State/Province:</b></td>
								<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">'.$dealerStatePDF.'</td>
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
								<th style="padding:7px 10px; font-size:13px;" colspan="6"><b>VI.</b> <b>LIEN HOLDER INFORMATION (If applicable)</b></th>
							</tr>
							<tr>
								<td colspan="2" style="padding:7px 10px; font-size:13px;"><b>LIEN HOLDER NAME:</b></td>
								<td colspan="2" style="padding:7px 10px; font-size:13px;">' . $Lien_Holder_Name . '</td>
								<td style="padding:7px 10px; font-size:13px;"><b>PH#:</b></td>
								<td style="padding:7px 10px; font-size:13px;">' . $Lien_Holder_Phone_Number . '</td>
							</tr>
							<tr>
								<td colspan="2" style="padding:7px 10px; font-size:13px;"><b>STREET ADDRESS: </b></td>
								<td style="padding:7px 10px; font-size:13px;" colspan="4">' . $Lien_Holder_Address . '</td>
							</tr>
							<tr>
								<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>City:</b></td>
								<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $Lien_Holder_City . '</td>
								<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>State/Province:</b></td>
								<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $Lien_Holder_State_Province_pdf . '</td>
								<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>Zip/Postal Code:</b></td>
								<td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $Lien_Holder_Postal_Code . '</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
							<tr>
								<th style="padding:7px 10px; font-size:13px;" colspan="2"><b>VII.</b> <b>PRICING:</b></th>
								<th style="padding:7px 10px; font-size:13px;"></th>
								<th style="padding:7px 10px; font-size:13px;"></th>
								<th style="padding:7px 10px; font-size:13px;"></th>
								<th style="padding:7px 10px; font-size:13px;"></th>
							</tr>
							<tr>
								<td colspan="2" style="padding:7px 10px; font-size:13px;"><b>MSRP:</b></td>
								<td colspan="2" style="padding:7px 10px; font-size:13px;">$'.number_format($MSRP_Amt,0).'</td>
								<td style="padding:7px 10px; font-size:13px;"></td>
								<td style="padding:7px 10px; font-size:13px;"></td>
							</tr>
							<tr>
								<td colspan="2" style="padding:7px 10px; font-size:13px;"><b>Add-Ons (Ex. APU, AEP, Aerial): </b></td>
								<td style="padding:7px 10px; font-size:13px;" colspan="4">$'.number_format($Addl_MSRP_Amt,0).'</td>
							</tr>
							<tr>
								<td colspan="2" style="padding:7px 10px; font-size:13px;"><b>SMALL GOODS: </b></td>
								<td style="padding:7px 10px; font-size:13px;" colspan="4">$'.number_format($Sml_Goods_Tot_Amt,0).'</td>
							</tr>
							<tr>
								<td colspan="2" style="padding:7px 10px; font-size:13px;"><b>TOTAL MSRP: </b></td>
								<td style="padding:7px 10px; font-size:13px;" colspan="4">$'.number_format($Tot_MSRP_Amt,0).'</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
							<tr>
								<th style="padding:7px 10px; font-size:13px;" colspan="3"><b>VIII.</b> <b>I UNDERSTAND:</b> <span style="font-weight: 300;">The warranty period begins on the Agreement Date Listed above and expires when either the time selected has ended or the unaltered ECM/ECU reaches the mileage/km/hours term limit, whichever occurs first.</span></th>
							</tr>

							<tr>
								<td style="padding:7px 10px; font-size:13px;">
									<table cellpadding="2" cellspacing="2" width="550" align="center" style="margin: auto;">
										<tr>
											<td width="270">
												<table style="width: 100%;" cellpadding="2" cellspacing="2">
													<tr>
														<td colspan="2" style="border-bottom: 1px solid #000; background-color:yellow;">&nbsp;</td>
													</tr>
													<tr>
														<td style="width: 60%;">CUSTOMER SIGNATURE</td>
														<td style="width: 40%; text-align: right; margin-right: 20px" align="right">DATE</td>
													</tr>
												</table>
											</td>
											<td width="20"></td>
											<td width="270">
												<table style="width: 100%;" cellpadding="2" cellspacing="2">
													<tr>
														<td colspan="2" style="border-bottom: 1px solid #000;background-color:yellow">&nbsp;</td>
													</tr>
													<tr>
														<td style="width: 60%;">AUTHORIZED RETAILER SIGNATURE</td>
														<td style="width: 40%; text-align: right; margin-right: 20px" align="right" >DATE</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td width="270">
												<table style="width: 100%;" cellpadding="2" cellspacing="2">
													<tr>
														<td style="border-bottom: 1px solid #000;background-color:yellow"></td>
													</tr>
													<tr>
														<td>CUSTOMER NAME (Printed)</td>
													</tr>
												</table>
											</td>
											<td width="20"></td>
											<td width="270">
												<table style="width: 100%;" cellpadding="2" cellspacing="2">
													<tr>
														<td style="border-bottom: 1px solid #000;background-color:yellow"></td>
													</tr>
													<tr>
														<td>AUTHORIZED RETAILER NAME (Printed)</td>
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
			<br pagebreak="true" />
			'.$boilerplate.'
		</div>
	</div>

	</body>
	</html>';

		//<p class="fontClass">TrüNorth Global™ Signature:__________________________________________________________________Date:'.$agreeDate.'_____________________</p>
		// Print text using writeHTMLCell()
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
if($pers_ID==0){
	$primary_Contact_Person_id = 0;
}else{
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


	//============================================================+
	// END OF FILE
	//============================================================+

	// End PDF Code here


}


?>