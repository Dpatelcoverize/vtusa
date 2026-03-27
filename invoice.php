<?php
require_once __DIR__ . '/tcpdf/tcpdf.php';
// Connect to DB
require_once "includes/dbConnect.php";
// DB Library
require_once "lib/dblib.php";

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
class MYPDF extends TCPDF
{

    // Top blue bar
    public function Header()
    {
        $this->SetFillColor(30, 29, 87); // #1e1d57
        $this->Rect(0, 0, $this->getPageWidth(), 0.2, 'F'); // 0.6 inch height
    }

    // Bottom blue bar
    public function Footer()
    {
        $this->SetFillColor(30, 29, 87); // #1e1d57
        $this->Rect(0, $this->getPageHeight() - 0.2, $this->getPageWidth(), 0.6, 'F');
    }
}

/* PHP FUNCTIONAL CODE */
// Get a dealer ID from session.
if (!(isset($_SESSION["id"])) || ($_SESSION["id"] == "")) {
	header("location: index.php");
	exit;
} else {
	$dealerID = $_SESSION["id"];
	$adminID = $_SESSION["admin_id"];
}

if (isset($_GET["warrantyID"])) {
	$warrantyID = $_GET["warrantyID"];
	//$warrantyID = decryptData($warrantyID);

	$securityCheck = dealerOwnsWarranty($link, $dealerID, $warrantyID);

	if (!$securityCheck) {
		if ($isQuote == 'Y') {
			$url = "warranty_pending.php?showQuotes=Y";
		} else {
			$url = "warranty_pending.php";
		}
		header("location: $url");
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

	$Mfr_Acct_ID = $row["Mfr_Acct_ID"];

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
	} else if ($Odometer_Reading_Km != 0) {
		$Odometer_Miles_Or_KM = "KM";
	}

	$ECM_Reading_Miles = $row["ECM_Read_Miles_Cnt"];
	$ECM_Reading_Km = $row["ECM_Read_Kms_Cnt"];

	$ECM_Miles_Or_KM = "Miles";
	if ($ECM_Reading_Miles != 0) {
		$ECM_Miles_Or_KM = "Miles";
	} else if ($ECM_Reading_Km != 0) {
		$ECM_Miles_Or_KM = "KM";
	}

	$APU_Engine_Make = $row["Veh_APU_Eng_Mk_Cd"];
	$APU_Engine_Model = $row["Veh_APU_Eng_Model_Cd"];
	$APU_Engine_Year = $row["Veh_APU_Eng_Yr_Cd"];
	$APU_Engine_Serial = $row["Veh_APU_Eng_Ser_nbr"];
	$APU_Hours = $row["Veh_APU_Hours"];
	$APU_Flg = $row["APU_Flg"];
	$wearables_flag = $row["wearables_flag"];

	$Vehicle_New_Flag = $row["Veh_New_Flg"];  // check value on this, coming back as 'k'?
	$Vehicle_Description = $row["Veh_Desc"];
	$Tier_Type = $row["Cntrct_Lvl_Cd"];
	// var_dump($Tier_Type);die;
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

	$wrap_program = $row["Wrap_Flg"];
	$wrap_Program_Term = $row["Cntrct_Term_Mnths_Nbr"];

	$EVBC_Flg = $row["EVBC_Flg"];
	$EEC_Flg = $row["EEC_Flg"];


	//$warrantyStatus = $row["Warranty_Status"];

	$_SESSION["warrantyID"] = $warrantyID;
}
/* FUNCTIONALITY ENDS HERE */

// Create PDF
$pdf = new MYPDF('P', 'in', 'LETTER', true, 'UTF-8', false);


// Document settings
$pdf->SetCreator('TCPDF');
$pdf->SetAuthor('Banner Fire Equipment');
$pdf->SetTitle('Invoice');
// $pdf->SetMargins(0.5, 0.8, 0.5);   // Left, Top, Right
// $pdf->SetHeaderMargin(0);
// $pdf->SetFooterMargin(0);
// $pdf->SetAutoPageBreak(true, 0.8); // Leave space for footer bar
$pdf->SetMargins(0.5, 0.5, 0.5);   // left, top, right
$pdf->SetAutoPageBreak(true, 1.0); // bottom space
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
$pdf->SetFont('glacialindifference', '', 12);

// Add page
$pdf->AddPage();
// $pdf->Ln(0.4);

$pdf->SetY(0.4); // Push content below top blue bar

// ===== WATERMARK IMAGE =====
$pdf->SetAlpha(0.15);
$pdf->Image(
    __DIR__ . '/images/bg-img.png',
    0.5,  // X position (inches)
    1.5,  // Y position (inches)
    7.5,  // Width
    '',
    '',
    '',
    false,
    300
);
$pdf->SetAlpha(1);

// ===== HTML CONTENT =====
$html = <<<HTML
<style>
body {
    font-family: glacialindifference;
    font-size: 12px;
}

/* table {
    border-collapse: collapse;
} */
/* th, td {
    padding: 5px;
} */
.InvoiceItemTable {
    border: 2px solid #1e1d57;
    font-size: 12px;
    font-family: glacialindifference;
    text-align: center;
}
</style>


<table width="100%">
    <tr>
        <td align="center">
            <img src="images/full-logo.jpg" height="1.52in" width="5.52in   ">
        </td>
    </tr>
</table>
<table width="100%" cellpadding="1">
    <tr>
        <td width="70%"><span style="font-size:32px;font-weight:bold;font-family:glacialindifference">INVOICE</span><br><span style="font-size:18px;font-weight:bold;font-family:glacialindifference">BANNER FIRE EQUIPMENT</span>
        </td>
        <td width="30%">
            <strong style="font-size:18px;font-weight:bold;font-family:glacialindifference">INVOICE #IL-002</strong><br>
            Date: 09/22/2025<br>
            <strong>Due Date: 10/06/2025</strong>
        </td>
    </tr>
</table>

<br>

<table width="100%" cellpadding="1">
<tr><td style="font-size:12px;font-family:glacialindifference"><strong>Customer Name:</strong>$customerName</td></tr>
<tr><td style="font-size:12px;font-family:glacialindifference"><strong>Customer Address:</strong> $customerAddress</td></tr>
<tr><td style="font-size:12px;font-family:glacialindifference"><strong>Customer Phone:</strong> $customerPhone</td></tr>
</table>

<br/>
<br/>

<table width="100%" cellpadding="5" style="align-items: center; text-align: center; border-collapse: collapse;">
    <tr>
        <td class="InvoiceItemTable" width="40%"><strong>ITEM</strong></td>
        <td class="InvoiceItemTable" width="10%"><strong>QTY</strong></td>
        <td class="InvoiceItemTable" width="25%"><strong>DEALER COST</strong></td>
        <td class="InvoiceItemTable" width="25%"><strong>TOTAL DUE</strong></td>
    </tr>
    <tr>
        <td class="InvoiceItemTable">
            <strong>10 Year Battalion Level</strong><br>2026 Freightliner MC 106 Tanker<br>3ALACYFE5TDWH6288
        </td>
        <td class="InvoiceItemTable">1</td>
        <td class="InvoiceItemTable">$44,805.00</td>
        <td class="InvoiceItemTable">$44,805.00</td>
    </tr>
    <tr>
        <td class="InvoiceItemTable" >
            <strong>10 Year Battalion Level</strong><br>2026 Freightliner MC 106 Tanker<br>3ALACYFE5TDWH6288
        </td>
        <td class="InvoiceItemTable">1</td>
        <td class="InvoiceItemTable" >$44,805.00</td>
        <td class="InvoiceItemTable" >$44,805.00</td>
    </tr>
    <tr>
        <td class="InvoiceItemTable">
            <strong>10 Year Battalion Level</strong>
            <br>2026 Freightliner MC 106 Tanker
            <br>3ALACYFE5TDWH6288
        </td>
        <td class="InvoiceItemTable">1</td>
        <td class="InvoiceItemTable">$44,805.00</td>
        <td class="InvoiceItemTable">$44,805.00</td>
    </tr>
</table>

<table width="100%">
    <tr>
        <td align="right" style="font-size:24px;font-weight:bold;color:#1e1d57;">
            TOTAL DUE: $68,505.00
        </td>
    </tr>
</table>

<table>
    <tr>
        <td><span style="font-size:9px; font-family:glacialindifference; text-decoration: underline; font-weight: bold;">Invoice Payment Terms
            </span><br/>
            <span style="font-size:9px; font-family:glacialindifference;">Payment is to be remitted within 15 days of Invoice Due Date. At 30 days past invoice due date, a .5% penalty will be added to the outstanding invoice. At 45 days past invoice due date, an additional .5% penalty will be added to the outstanding invoice. If funds are not received within 60 days of the Invoice due date, the customer agreement could be made inactive or voided for nonpayment in accordance with the “How this agreement is cancelled or voided” section on the respective customer’s agreement. Customer and dealer will be notified of cancellation of contract. Once voided, the rights and privileges of the agreement are forfeited including the validation of any claim and the right to any refund. Contract void and cancellations are at the sole discretion of Vital Trends.
            </span>
            <br/><span style="font-size:9px; font-family:glacialindifference;">New invoice due dates can be issued for specific invoices at the sole discretion of Vital Trends in the event of vehicle delivery delay. New invoice due date requests need to be emailed to accounting@vitaltrendsusa.com and must include original Purchase Order # and corresponding Invoice #. No other forms of contact with regards to re-issuing of invoice due date will be accepted. Until invoice has been updated with new invoice due date, original dates and penalties will apply.
            </span>
        </td>
    </tr>
</table>


<table>
    <tr>
        <td style="width: 80%; font-size:9px;;font-family:glacialindifference"><span style="text-decoration: underline; font-weight: bold; padding: 0; margin: 0;">Acceptable Payment Methods
            </span><br />
            <span>Payment via ACH:
                <br /> If selling dealer has an ACH form that Vital Trends must fill out prior to payment. please forward ACH form to
                <br /> accounting@vitaltrendsusa.com.
            </span>
            <span>Payment via check, please remit to:
                <br />Vital Trends 
                <br />9621 Summit Rd.
                <br />Cassville, NY 13318
            </span>
        </td>
        <td style="width: 20%; height: 100%; align-items: end; text-align: end;">
            <img src="images/QR.jpg" height="80">
        </td>
    </tr>
</table>

HTML;

// Write HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
$pdf->Output('invoice.pdf', 'I');