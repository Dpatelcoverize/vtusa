<?php

if($warrantyID == '')
{
    $query = "SELECT Cntrct_ID  FROM Cntrct  WHERE Mfr_Acct_ID=".$dealerID." ORDER By Cntrct_ID DESC";
	$result = $link->query($query);
    $warranty = mysqli_fetch_assoc($result);
    $warrantyID = $warranty["Cntrct_ID"];
}

//if(false){

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('Vital Trends');
$pdf->setTitle('Warranty Pricing Summary');
$pdf->setSubject('Warranty Pricing Summary');

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

$type =  strtoupper($_POST['vehicleType']);

$pdf_Tier_Type = '';
$pdf_Apparatus_Equipment_Package = '';
$pdf_Aerial_Package = '';
$apuEnginHtml = '';
$breakPage = '';
$breakPageType1 = '';
$breakPageType2 = '';
$breakPageType3 = '';
$breakPageType3APU = '';

//include 'warranty_boilerplate.php';
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

if($APU_Flg=="Y"){
    $pdf_APU_Package = "Yes";
}else{
    $pdf_APU_Package = "No";
}


$query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Cntrct_ID=".$contract_ID." AND
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
    
    if($smallGoodsPackage == "Y")
    {
    $Sml_Goods_Tot_Amt = $row["Sml_Goods_Tot_Amt"];
    }
    else
    {
        $Sml_Goods_Tot_Amt = 0;
    }

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

if(!isset($dealer_AR_number)){
	$dealer_AR_number = "";
}

$html = '
<style type="text/css">
       .fontClass{
           font-size: 13px;
           font-weight:normal;
       }
       .textJustify{
           text-align:justify;
       }
       td{
        border: none;
       }
       </style>
       <table>
            <tr>
                <td style="width:60%;text-align: center;vertical-align: middle;"><img src="images/TM2.png" /></td>
                <td style="width:40%;">
                    <p style="font-size:13px;text-align:center;">
                        <span style="text-align:center;margin-top:5%;font-size:18px;font-weight:bold;">Warranty Pricing Summary</span>
                    </p>
                </td>
            </tr>
        </table>
        <br><br><br>
        <table cellpadding="5">
            <tr style="font-weight:bold">
                <td class="fontClass" style="border: 1px sold black; background-color:#ccd2e6;" colspan="3">CUSTOMER INFORMATION</td>
            </tr>
            <tr style="font-weight:bold;">
                <td class="fontClass" style="border: 1px sold black;" colspan="3">Customer Name: '.$customerName.'</td>
            </tr>
            <tr style="font-weight:bold;">
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-left: 1px solid black;" colspan="2">EMAIL: '.$customerEmail.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-right: 1px solid black;" colspan="1">PHONE: '.$customerPhone.'</td>
            </tr>
            <tr style="font-weight:bold;">
                <td class="fontClass" style="border: 1px solid black;" colspan="3">ADDRESS: '.$customerAddress.'</td>
            </tr>
            <tr style="font-weight:bold;">
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-left: 1px solid black;">CITY: '.$customerCity.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; text-align:center;">STATE: '.$customerStatePDF.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-right: 1px solid black;">POSTAL CODE: '.$customerZip.'</td>
            </tr>
        </table>
        <br><br><br>
        <table cellpadding="5">
            <tr style="font-weight:bold">
                <td class="fontClass" style="border: 1px sold black; background-color:#ccd2e6;" colspan="4">VEHICLE INFORMATION</td>
            </tr>
            <tr style="font-weight:bold;">
                <td class="fontClass" style="border: 1px sold black;" colspan="4">FULL VIN: '.$Vehicle_Vin_Number.'</td>
            </tr>
            <tr style="font-weight:bold;">
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-left: 1px solid black;">VEHICLE: </td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black;">Year: '.$Vehicle_Year.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black;">MAKE: '.$Vehicle_Make.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-right: 1px solid black;" colspan="1">MODEL: '.$Vehicle_Model.'</td>
            </tr>
            <tr style="font-weight:bold;">
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-left: 1px solid black;" >ENGINE: </td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black;">MAKE: '.$Engine_Make.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black;">MODEL: '.$Engine_Model.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-right: 1px solid black;" colspan="1">SERIAL#: '.$Engine_Serial.'</td>
            </tr>
            <tr style="font-weight:bold;">
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-left: 1px solid black;">TRANSMISSION: </td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black;">MAKE: '.$Transmission_Make.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black;">MODEL: '.$Transmission_Model.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-right: 1px solid black;" colspan="1">SERIAL#: '.$Transmission_Serial.'</td>
            </tr>
            <tr style="font-weight:bold;">
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-left: 1px solid black;" colspan="2">ODO. READING: '.$Odometer_Reading . ' ' . $Odometer_Miles_Or_KM.' </td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-right: 1px solid black;"colspan="2">ECM Reading: '.$ECM_Reading . ' ' . $ECM_Miles_Or_KM.'</td>
            </tr>
        </table>
        <br><br><br>
        <table cellpadding="5">
            <tr style="font-weight:bold">
                <td class="fontClass" style="border: 1px sold black; background-color:#ccd2e6;" colspan="3">RETAILER INFORMATION</td>
            </tr>
            <tr style="font-weight:bold;">
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-left: 1px solid black;" colspan="1">RETAILER NAME: '.$dealerName.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; text-align:center;" colspan="1">RETAILER AR: '.$dealer_AR_number.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-right: 1px solid black;" colspan="1">PHONE: '.$dealerPhone.'</td>
            </tr>
            <tr style="font-weight:bold;">
                <td class="fontClass" style="border: 1px solid black;" colspan="3">ADDRESS: '.$dealerAddress1.'</td>
            </tr>
            <tr style="font-weight:bold;">
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-left: 1px solid black;">CITY: '.$dealerCity.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; text-align:center;">STATE: '.$dealerStatePDF.'</td>
                <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-right: 1px solid black;">POSTAL CODE: '.$dealerZip.'</td>
            </tr>
       </table>
       <br><br><br>
       <table cellpadding="5">
           <tr style="font-weight:bold">
               <td class="fontClass" style="border: 1px sold black; background-color:#ccd2e6;" colspan="3">COMPONENT COVERAGE</td>
           </tr>
           <tr style="font-weight:bold;">
               <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-left: 1px solid black;" colspan="2">VEHICLE TYPE: '.$type.'</td>
               <td class="fontClass" style="border-bottom: 1px solid black; border-top: 1px solid black; border-right: 1px solid black;" colspan="1">TIER TYPE: '.$Tier_Type.'</td>
           </tr>
           <tr style="font-weight:bold;">
               <td class="fontClass" style="border: 1px solid black;text-align:center;" colspan="3">COVERAGE TERM: '.$Coverage_Term.'</td>
           </tr>
      </table>
      <br pagebreak="true" />
       <table cellpadding="5">
           <tr style="font-weight:bold">
               <td class="fontClass" style="border: 1px sold black; background-color:#ccd2e6;" colspan="3">PRICING</td>
           </tr>
           <tr style="font-weight:bold;">
               <td class="fontClass" style="border: 1px solid black;" colspan="3">MSRP: $'.number_format($MSRP_Amt,0).'</td>
           </tr>
           <tr style="font-weight:bold;">
              <td class="fontClass" style="border: 1px solid black;" colspan="3">Add-Ons (Ex. APU, AEP, Aerial): $'.number_format($Addl_MSRP_Amt,0).'</td>
           </tr>
           <tr style="font-weight:bold;">
              <td class="fontClass" style="border: 1px solid black;" colspan="3">SMALL GOODS: $'.number_format($Sml_Goods_Tot_Amt,0).'</td>
           </tr>
            <tr style="font-weight:bold;">
              <td class="fontClass" style="border: 1px solid black;" colspan="3">TOTAL MSRP: $'.number_format($Tot_MSRP_Amt,0).'</td>
            </tr>
      </table>
      <br> <br><br>
        <table cellpadding="5" cellspacing="5" style="margin: auto;">
            <tr>
                <td width="270">
                    <table style="width: 100%;" cellpadding="2" cellspacing="2">
                        <tr>
                            <td colspan="2" style="border-bottom: 1px solid #000; background-color:yellow;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td class="fontClass" style="width: 100%;" colspan="2">CUSTOMER NAME</td>
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
                            <td class="fontClass" style="width: 100%;" colspan="2">CUSTOMER SIGNATURE</td>
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
                            <td class="fontClass">CUSTOMER TITLE</td>
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
                            <td class="fontClass">CUSTOMER DATE</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>`
      <br /><br />
      Pricing Is Subject To Actuarial Review';

    //<p class="fontClass">TrüNorth Global™ Signature:__________________________________________________________________Date:'.$agreeDate.'_____________________</p>
    // Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

    // ---------------------------------------------------------

    // Close and output PDF document
    // This method has several options, check the source code documentation for more information.
// Close and output PDF document
// This method has several options, check the source code documentation for more information.

$pdfFileName = str_replace(" ", "_", $customerName) . '_' . str_replace(" ", "_", $customerPhone) . '_' . time() . '.pdf';

$pdf->Output(__DIR__ . '/uploads/addendum_pdf/' . $pdfFileName, 'F');

// Save Pddf into database
$query = "SELECT Pers_ID FROM Pers WHERE Acct_ID=" . $dealerID . ";";
$result = $link->query($query);
$row = $result->fetch_assoc();

$primary_Contact_Person_id = $row['Pers_ID'];

// Get the contract info
$query = "SELECT cd.Cntrct_Dim_ID FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
$result = $link->query($query);
$row = $result->fetch_assoc();

$contract_dim_ID = $row["Cntrct_Dim_ID"];

// Add this file to our File_Assets tracking table
//  Set type=2 for 'dealer W9'.

    $stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Warranty_Cntrct_ID,
                   Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,21,'Addendum',NOW())");

/* Bind variables to parameters */
$val1 = $dealerID;
$val2 = $primary_Contact_Person_id;
$val3 = $adminID;
//$val4 = $contract_dim_ID;
$val4 = $warrantyID;
$val5 = '/uploads/addendum_pdf/' . $pdfFileName;
mysqli_stmt_bind_param($stmt, "iiiis", $val1, $val2, $val3, $val4, $val5);


/* Execute the statement */
$result = mysqli_stmt_execute($stmt);

?>