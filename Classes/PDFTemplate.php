<?php

namespace Classes;

class PDFTemplate
{
    private $data;
    private $link;

    private $breakPageType1;

    private $breakPageType2;

    private $breakPageType3;

    public function __construct($link,$data)
    {
        $this->data = $data;
        $this->link = $link;
    }

    public function getQuoteTPL()
    {
        // echo '<pre>';
        // print_r($this->data);die();
       // Additional Standard Pricing default values
            $aepQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='AEP'";

            $aepResult = $this->link->query($aepQuery);

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

            $apuResult = $this->link->query($apuQuery);

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

            $aerResult = $this->link->query($aerQuery);

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

            $aepResult = $this->link->query($aepQuery);

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

            
            $evbcQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='EVBC'";

            $evbcResult = $this->link->query($evbcQuery);

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

            $eecResult = $this->link->query($eecQuery);

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

            $acpResult = $this->link->query($acpQuery);

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

            $hudsResult = $this->link->query($hudsQuery);

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

            $ucpResult = $this->link->query($ucpQuery);

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

            /* Starts -	Aged Vehicle Surcharge */
            $oldTag = "OLD";
            if (date("Y") - $this->data->Vehicle_Year > 20) {
                $oldTag = "OLD2";
            } else if (date("Y") - $this->data->Vehicle_Year > 14) {
                $oldTag = "OLD";
            }


            $oldQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='" . $oldTag . "'";

            $oldResult = $this->link->query($oldQuery);

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
            /* Ends - Aged Vehicle Surcharge   */

        $addonPricing='';
        if($this->data->APU_Flg == "Y" || $this->data->AEP_Flg == "Y" || $this->data->AER_Flg == "Y" || $this->data->wearable == "Y" || $this->data->EVBC_Flg == "Y" || $this->data->EEC_Flg == "Y" || $this->data->ACP_Flg == "Y" || $this->data->HUDS_Flg == "Y" || $this->data->UCP_Flg == "Y" || $this->data->Old_Flg == "Y"){
            
            $addonPricing.='<tr>
                        <td colspan="2" style="padding:7px 10px; font-size:13px;"><b>Add-Ons: </b></td>    
                        <td colspan="4" style="padding:0;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">';
                            if($this->data->AEP_Flg == "Y"){
                                $addonPricing.='<tr>
                                    <td colspan="3">
                                        Fire Apparatus Pump & Equipment Package
                                    </td>
                                    <td colspan="1" style="padding:7px 0px; font-size:13px; text-align:right;">
                                        $' . number_format($this->data->quantity * ($aep_Dlr_Cost_Amt + $this->data->Addl_Dlr_Mrkp_Actl_AEP_Amt), 0) . '
                                    </td>
                                </tr>';
                            }

                            if($this->data->APU_Flg == "Y"){
                                $addonPricing.='<tr>
                                    <td colspan="3">
                                        Addl_Dlr_Mrkp_Actl_APU_Amt
                                    </td>
                                    <td colspan="1" style="padding:7px 0px; font-size:13px; text-align:right;">
                                        $' . number_format($this->data->quantity * ($apu_Dlr_Cost_Amt + $this->data->Addl_Dlr_Mrkp_Actl_APU_Amt), 0) . '
                                    </td>
                                </tr>';
                            }

                            if($this->data->AER_Flg == "Y"){
                                $addonPricing.='<tr>
                                    <td colspan="3">
                                        Aerial Package
                                    </td>
                                    <td colspan="1" style="padding:7px 0px; font-size:13px; text-align:right;">
                                        $' . number_format($this->data->quantity * ($aer_Dlr_Cost_Amt + $this->data->Addl_Dlr_Mrkp_Actl_AER_Amt), 0) . '
                                    </td>
                                </tr>';
                            }
                            
                            if($this->data->wearable == "Y"){
                                $addonPricing.='<tr>
                                    <td colspan="3">
                                        Wearables Package 
                                    </td>
                                    <td colspan="1" style="padding:7px 0px; font-size:13px; text-align:right;">
                                        $' . number_format($this->data->quantity * ($wearable_Dlr_Cost_Amt + $this->data->Addl_Dlr_Mrkp_Actl_WEARABLES_Amt), 0) . '
                                    </td>
                                </tr>';
                            }

                            if($this->data->EVBC_Flg == "Y"){
                                $addonPricing.='<tr>
                                    <td colspan="3">
                                        Electric Vehicle Battery Package 
                                    </td>
                                    <td colspan="1" style="padding:7px 0px; font-size:13px; text-align:right;">
                                        $' . number_format($this->data->quantity * ($evbc_Dlr_Cost_Amt + $this->data->Addl_Dlr_Mrkp_Actl_EVBC_Amt), 0) . '
                                    </td>
                                </tr>';
                            }

                            if($this->data->EEC_Flg == "Y"){
                                $addonPricing.='<tr>
                                    <td colspan="3">
                                        Enhanced Engine Coverage
                                    </td>
                                    <td colspan="1" style="padding:7px 0px; font-size:13px; text-align:right;">
                                        $' . number_format($this->data->quantity * ($eec_Dlr_Cost_Amt + $this->data->Addl_Dlr_Mrkp_Actl_EEC_Amt), 0) . '
                                    </td>
                                </tr>';
                            }

                            if($this->data->ACP_Flg == "Y"){
                                $addonPricing.='<tr>
                                    <td colspan="3">
                                       Ambulance Conversion Package
                                    </td>
                                    <td colspan="1" style="padding:7px 0px; font-size:13px; text-align:right;">
                                        $' . number_format($this->data->quantity * ($acp_Dlr_Cost_Amt + $this->data->Addl_Dlr_Mrkp_Actl_ACP_Amt), 0) . '
                                    </td>
                                </tr>';
                            }

                            if($this->data->HUDS_Flg == "Y"){
                                $addonPricing.='<tr>
                                    <td colspan="3">
                                        High Use Dept Surcharge
                                    </td>
                                    <td colspan="1" style="padding:7px 0px; font-size:13px; text-align:right;">
                                        $' . number_format($this->data->quantity * ($huds_Dlr_Cost_Amt + $this->data->Addl_Dlr_Mrkp_Actl_HUDS_Amt), 0) . '
                                    </td>
                                </tr>';
                            }

                            if($this->data->UCP_Flg == "Y"){
                                $addonPricing.='<tr>
                                    <td colspan="3">
                                       Upfitter Conversion Package
                                    </td>
                                    <td colspan="1" style="padding:7px 0px; font-size:13px; text-align:right;">
                                        $' . number_format($this->data->quantity * ($ucp_Dlr_Cost_Amt + $this->data->Addl_Dlr_Mrkp_Actl_UCP_Amt), 0) . '
                                    </td>
                                </tr>';
                            }

                             if($this->data->Old_Flg == "Y"){
                                $addonPricing.='<tr>
                                    <td colspan="3">
                                       Aged Vehicle Surcharge
                                    </td>
                                    <td colspan="1" style="padding:7px 0px; font-size:13px; text-align:right;">
                                        $' . number_format($this->data->quantity * $old_Dlr_Cost_Amt, 0) . '
                                    </td>
                                </tr>';
                            }

                            $addonPricing.='<tr style="border-top:2px solid #000;">
                                    <td colspan="3" style="border-top:2px solid #000;">
                                        Total Add-On Pricing
                                    </td>
                                    <td colspan="1" style="padding:7px 0px; font-size:13px; text-align:right;border-top:2px solid #000;">
                                        $' . number_format($this->data->quantity * $this->data->Addl_MSRP_Amt, 0) . '
                                    </td>
                                </tr>';
                             $addonPricing.='</table>
                        </td>
                    </tr>';

        }else{

            $addonPricing.= ' <tr>
                        <td colspan="2" style="padding:7px 10px; font-size:13px;"><b>Add-Ons: </b></td>
                       <td style="padding:7px 10px; font-size:13px;" colspan="4">$0</td>
                    </tr>';
        }
       

        $html = '<html lang="en">
        <style>
            @page {
                margin: 0;
            }
        
            table.main-table {
                text-align: center;
                padding: 17px 0 17px 0;
                margin: auto;
            }
        
            .padding-left {
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
        
            table.inner-full-width td,
            table.inner-full-width th {
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


            .custom_font_boilerplate {
                font-size: 12px;
                line-height: 1.17;
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
                                                    <td class="head-text-lg" align="center" style="font-size: 18px;">' . $this->data->title . '</td>
                                                </tr>
                                                <tr>
                                                    <td class="head-text" align="center" style="font-size: 13px;">Please call our claims hotline @ 800-903-7489 ext. 820, immediately upone noticing any unusual mechanical issues concerning the vehicle listed below.
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    ' . $this->data->titleQuoteString . '
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
                                    <tr>
                                        <th colspan="4" style="padding:7px 10px; font-size:13px;"><b>I.</b> <b>CUSTOMER INFORMATION:</b></th>
                                        <th colspan="2" style="padding:7px 10px; font-size:13px;"><b>AGREEMENT DATE: ' . $this->data->agreeDate . ' </b></th>
                                    </tr>
                                    <tr>
                                        <td style="padding:7px 10px; font-size:13px;"><b>NAME:</b></td>
                                        <td colspan="5" style="padding:7px 10px; font-size:13px;">' . $this->data->customerName . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:7px 10px; font-size:13px;"><b>EMAIL:</b></td>
                                        <td colspan="3" style="padding:7px 10px; font-size:13px;">' . $this->data->customerEmail . '</td>
                                        <td style="padding:7px 10px; font-size:13px;"><b>PH#:</b></td>
                                        <td style="padding:7px 10px; font-size:13px;">' . $this->data->customerPhone . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:7px 10px; font-size:13px;"><b>ADDRESS:</b></td>
                                        <td colspan="5" style="padding:7px 10px; font-size:13px;">' . $this->data->customerAddress . '</td>
                                    </tr>
                                    <tr>
                                        <td class="border-bottom-none" style="padding:7px 10px; font-size:13px;"><b>City:</b></td>
                                        <td class="border-bottom-none" style="padding:7px 10px; font-size:13px;">' . $this->data->customerCity . '</td>
                                        <td class="border-bottom-none" style="padding:7px 10px; font-size:13px;"><b>State/Province:</b></td>
                                        <td class="border-bottom-none" style="padding:7px 10px; font-size:13px;">' . $this->data->customerStatePDF . '</td>
                                        <td class="border-bottom-none" style="padding:7px 10px; font-size:13px;"><b>Zip/Postal Code:</b></td>
                                        <td class="border-bottom-none" style="padding:7px 10px; font-size:13px;">' . $this->data->customerZip . '</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
                                    <tr>
                                        <th colspan="4" style="padding:7px 10px; font-size:13px;"><b>II.</b> <b>VEHICLE INFORMATION:</b></th>
                                        <th colspan="2" style="padding:7px 10px; font-size:13px;"></th>
                                    </tr>
                                    <tr>
                                        <td style="padding:7px 10px; font-size:13px;" colspan="3"><b>FULL VIN: ' . $this->data->Vehicle_Vin_Number . '</b></td>
                                        <td style="padding:7px 10px; font-size:13px;"></td>
                                        <td style="padding:7px 10px; font-size:13px;"><b></b></td>
                                        <td style="padding:7px 10px; font-size:13px;"></td>
                                    </tr>
                                    <tr>
                                        <td style="padding:7px 10px; font-size:13px;"><b>VEHICLE:</b></td>
                                        <td style="padding:7px 10px; font-size:13px;">Year: ' . $this->data->Vehicle_Year . '</td>
                                        <td style="padding:7px 10px; font-size:13px;"><b>MAKE: ' . $this->data->Vehicle_Make . '</b></td>
                                        <td style="padding:7px 10px; font-size:13px;"></td>
                                        <td><b>MODEL:</b></td>
                                        <td>' . $this->data->Vehicle_Model . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:7px 10px; font-size:13px;"><b>ENGINE:</b></td>
                                        <td style="padding:7px 10px; font-size:13px;">MAKE: ' . $this->data->Engine_Make . '</td>
                                        <td style="padding:7px 10px; font-size:13px;"><b>MODEL:</b></td>
                                        <td style="padding:7px 10px; font-size:13px;">' . $this->data->Engine_Model . '</td>
                                        <td style="padding:7px 10px; font-size:13px;"><b>SERIAL#:</b></td>
                                        <td style="padding:7px 10px; font-size:13px;">' . $this->data->Engine_Serial . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:7px 10px; font-size:13px;"><b>TRANSMISSION:</b></td>
                                        <td style="padding:7px 10px; font-size:13px;">MAKE: ' . $this->data->Transmission_Make . '</td>
                                        <td style="padding:7px 10px; font-size:13px;"><b>MODEL: </b></td>
                                        <td style="padding:7px 10px; font-size:13px;">' . $this->data->Transmission_Model . '</td>
                                        <td style="padding:7px 10px; font-size:13px;"><b>SERIAL#: </b></td>
                                        <td style="padding:7px 10px; font-size:13px;">' . $this->data->Transmission_Serial . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>ODO. READING:</b></td>
                                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $this->data->Odometer_Reading . ' ' . $this->data->Odometer_Miles_Or_KM . '</td>
                                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b></b></td>
                                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>ECM Reading:</b></td>
                                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>' . $this->data->ECM_Reading . ' ' . $this->data->ECM_Miles_Or_KM . '</b></td>
                                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"></td>
                                    </tr>';
        if ($this->data->APU_Flg == "Y") {
            $html .= '<tr>
                            <td style="padding:7px 10px; font-size:13px;"><b>APU ENGINE:</b></td>
                            <td style="padding:7px 10px; font-size:13px;"><b>MAKE: ' . $this->data->APU_Engine_Make . '</b></td>
                            <td style="padding:7px 10px; font-size:13px;">MODEL: ' . $this->data->APU_Engine_Model . '</td>
                            <td style="padding:7px 10px; font-size:13px;">Year: ' . $this->data->APU_Engine_Year . '</td>
                            <td style="padding:7px 10px; font-size:13px;">SERIAL: ' . $this->data->APU_Engine_Serial . '</td>
                        </tr>';
        }
        $html .= ' </table>
        </td>
    </tr>';
        /**
         * For vehicle type 
         */

        if ($this->data->type == 'TYPE 1') {
            $html .= '<tr>
            <td>
              <table
                class="inner-full-width"
                cellpadding="2"
                cellspacing="2"
                style="margin: 0"
              >
                <tr>
                  <th style="padding: 7px 10px; font-size: 13px">
                    <b>III.</b> <b>COMPONENT COVERAGE:</b>
                    <span style="font-weight: 300"
                      >See page 2 section A. for details.</span
                    >
                  </th>
                </tr>
                <tr>
                  <td
                    style="padding: 7px 10px; font-size: 13px"
                    class="border-bottom-none"
                  >
                    <label
                      ><input type="checkbox" name="" /> VEHICLE TYPE: ' . $this->data->type .
                '</label
                    >
                  </td>
                </tr>
                <tr>
                  <td
                    style="padding: 7px 10px; font-size: 13px"
                    class="border-bottom-none"
                  >
                    <label
                      ><input type="checkbox" name="" /> TIER TYPE: ' . $this->data->pdf_Tier_Type .
                '</label
                    >
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          ';

            $this->breakPageType1 . '<br pagebreak="true" />';
        } elseif ($this->data->type == 'TYPE 2') {
            $html .= '<tr>
            <td>
                <table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
                    <tr>
                        <th style="padding:7px 10px; font-size:13px;"><b>III.</b> <b>COMPONENT COVERAGE:</b> <span style="font-weight: 300;">See page 2 section A. for details.</span></th>
                        <th style="padding:7px 10px; font-size:13px;">&nbsp;</th>
                    </tr>
                    <tr>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
                            <label><input type="checkbox" name=""> VEHICLE TYPE: ' . $this->data->type . '</label>
                        </td>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
                            <?php if ($isQuote == "Y") { ?>
                                <label><input type="checkbox" name="">UNLIMITED MILEAGE</label>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
                            <label><input type="checkbox" name=""> TIER TYPE: ' . $this->data->pdf_Tier_Type . '</label>
                        </td>
                        <td style="padding:27px 10px; font-size:13px;" class="border-bottom-none">
                            <?php if ($isQuote == "Y") { ?>
                                <label><input type="checkbox" name="">UNLIMITED HOURS</label>
                            <?php } ?>
                        </td>
                    </tr>';

            if ($this->data->AEP_Flg == "Y") {
                $html .= '<tr>
                <td style="padding: 7px 10px; font-size: 13px" class="border-bottom-none">
                  <label
                    ><input type="checkbox" name="" /> APPRATUS EQUIPMENT PACKAGE: Yes
                  </label>
                </td>
              </tr>
              ';
            }

            $html .= '</table>
            </td>
            </tr>';

            // $this->breakPageType2 .= '<br pagebreak="true" />';
        } elseif ($this->data->type == 'TYPE 3') {
            $html .= '<tr>
            <td>
                <table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
                    <tr>
                        <th style="padding:7px 10px; font-size:13px;"><b>III.</b> <b>COMPONENT COVERAGE:</b> <span style="font-weight: 300;">See page 2 section A. for details.</span></th>
                        <th style="padding:7px 10px; font-size:13px;">&nbsp;</th>
                    </tr>
        
                    <tr>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
                            <label><input type="checkbox" name=""> VEHICLE TYPE: ' . $this->data->type . '</label>
                        </td>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
                            <?php if ($isQuote == "Y") { ?>
                                <label><input type="checkbox" name="">UNLIMITED MILEAGE</label>
                            <?php } ?>
                        </td>
                    </tr>
        
                    <tr>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
                            <label><input type="checkbox" name=""> TIER TYPE: ' . $this->data->pdf_Tier_Type . '</label>
                        </td>
                        <td style="padding:27px 10px; font-size:13px;" class="border-bottom-none">
                            <?php if ($isQuote == "Y") { ?>
                                <label><input type="checkbox" name="">UNLIMITED HOURS</label>
                            <?php } ?>
                        </td>
                    </tr>';

            if ($this->data->APU_Flg == "Y") {
                $html .= '<tr>
                <td style="padding: 7px 10px; font-size: 13px" class="border-bottom-none">
                  <label><input type="checkbox" name="" /> APU PACKAGE: Yes</label>
                </td>
              </tr>
              ';
                // $this->breakPageType3 = '<br pagebreak="true" />';
            }

            if ($this->data->AEP_Flg == "Y") {
                $html .= '<tr>
                <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
                    <label><input type="checkbox" name=""> APPRATUS EQUIPMENT PACKAGE: Yes </label>
                </td>
            </tr>';
            }

            if ($this->data->AER_Flg == "Y") {
                $html .= ' <tr>
                <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">
                    <label><input type="checkbox" name=""> AERIAL PACKAGE: Yes</label>
                </td>
            </tr>';
            }

            $html .= '</table>
            </td>
            </tr>';
        }
        
        $html .= '<tr>
            <td>
                <table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
                    <tr>
                        <th style="padding:7px 10px; font-size:13px;"><b>IV.</b> <b>COVERAGE TIME:</b> <span style="font-weight: 300;">The warranty period begins on the Agreement Date Listed above and expires when either the time selected has ended or the unaltered ECM/ECU reaches the mileage/km/hours term limit, whichever occurs first.</span></th>
                    </tr>
                    <tr>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none" align="center">
                            <table cellpadding="2" cellspacing="2" style="margin:0; width:100%;">
                                <tr>
                                    <td align="center">
                                        <label><input type="checkbox" name=""> ' . $this->data->Coverage_Term . '</label>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr><br pagebreak="true" />
        ' . $this->breakPageType1 . '
        ' . $this->breakPageType2 . '
        ' . $this->breakPageType3 . '
        <tr>
            <td>
                <table class="inner-full-width" cellpadding="2" cellspacing="2" style="margin:0;">
                    <tr>
                        <th style="padding:7px 10px; font-size:13px;" colspan="2"><b>V.</b> <b>RETAILER INFORMATION:</b></th>
                        <th style="padding:7px 10px; font-size:13px;"><b>AR#:</b></th>
                        <th style="padding:7px 10px; font-size:13px;">' . $this->data->dealerARNumber . '</th>
                        <th style="padding:7px 10px; font-size:13px;">P0#:</th>
                        <th style="padding:7px 10px; font-size:13px;">' . $this->data->dealerAddress2 . '</th>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:7px 10px; font-size:13px;"><b>RETAILER NAME:</b></td>
                        <td colspan="2" style="padding:7px 10px; font-size:13px;">' . $this->data->dealerName . '</td>
                        <td style="padding:7px 10px; font-size:13px;"><b>PH#:</b></td>
                        <td style="padding:7px 10px; font-size:13px;">' . $this->data->dealerPhone . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:7px 10px; font-size:13px;"><b>STREET ADDRESS: </b></td>
                        <td style="padding:7px 10px; font-size:13px;" colspan="4">' . $this->data->dealerAddress1 . '</td>
                    </tr>
                    <tr>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>City:</b></td>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $this->data->dealerCity . '</td>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>State/Province:</b></td>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $this->data->dealerStatePDF . '</td>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>Zip/Postal Code:</b></td>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $this->data->dealerZip . '</td>
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
                        <td colspan="2" style="padding:7px 10px; font-size:13px;">' . $this->data->Lien_Holder_Name . '</td>
                        <td style="padding:7px 10px; font-size:13px;"><b>PH#:</b></td>
                        <td style="padding:7px 10px; font-size:13px;">' . $this->data->Lien_Holder_Phone_Number . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:7px 10px; font-size:13px;"><b>STREET ADDRESS: </b></td>
                        <td style="padding:7px 10px; font-size:13px;" colspan="4">' . $this->data->Lien_Holder_Address . '</td>
                    </tr>
                    <tr>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>City:</b></td>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $this->data->Lien_Holder_City . '</td>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>State/Province:</b></td>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $this->data->Lien_Holder_State_Province_pdf . '</td>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none"><b>Zip/Postal Code:</b></td>
                        <td style="padding:7px 10px; font-size:13px;" class="border-bottom-none">' . $this->data->Lien_Holder_Postal_Code . '</td>
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
                        <td colspan="2" style="padding:7px 10px; font-size:13px;"><b>Quantity:</b></td>
                        <td colspan="2" style="padding:7px 10px; font-size:13px;">' . $this->data->quantity . '</td>
                        <td style="padding:7px 10px; font-size:13px;"></td>
                        <td style="padding:7px 10px; font-size:13px;"></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:7px 10px; font-size:13px;"><b>MSRP:</b></td>
                        <td colspan="2" style="padding:7px 10px; font-size:13px;">$' . number_format($this->data->quantity * $this->data->MSRP_Amt, 0) . '</td>
                        <td style="padding:7px 10px; font-size:13px;"></td>
                        <td style="padding:7px 10px; font-size:13px;"></td>
                    </tr>
                    ' . $addonPricing . '
                    <tr>
                        <td colspan="2" style="padding:7px 10px; font-size:13px;"><b>SMALL GOODS: </b></td>
                        <td style="padding:7px 10px; font-size:13px;" colspan="4">$' . number_format($this->data->quantity * $this->data->Sml_Goods_Tot_Amt, 0) . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:7px 10px; font-size:13px;"><b>TOTAL MSRP: </b></td>
                        <td style="padding:7px 10px; font-size:13px;" colspan="4">$' . number_format($this->data->quantity * $this->data->Tot_MSRP_Amt, 0) . '</td>
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
                                                <td style="width: 40%; text-align: right; margin-right: 20px" align="right">DATE</td>
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
        ' . $this->data->boilerplate . '
        </div>
        </div>
        
        </body>
        
        </html>';
        return $html;
    }
}
