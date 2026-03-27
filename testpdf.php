<?php

// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('AGREEMENT');
$pdf->setTitle('AGREEMENT');
$pdf->setSubject('AGREEMENT');
$pdf->setKeywords('AGREEMENT, Vital, Data, Set, Guide');

// set default header data
//$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setPrintHeader(false);
//$pdf->setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP-15, PDF_MARGIN_RIGHT);
$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
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
$cambriabF = TCPDF_FONTS::addTTFfont('tcpdf/fonts/cambria/Cambria.ttf', 'TrueTypeUnicode', '', 32);
$pdf->setFont($cambriabF, '', 14, '', true);
// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// set text shadow effect
$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

$html = '
<style type="text/css">
.fontClass{
    font-size: 12px;
    font-weight:normal;
}
.textJustify{
    text-align:justify;
}
</style>
<table>
    <tr>
        <td style="width:20%;"></td>
        <td style="width:70%;text-align: center;vertical-align: middle;width:60%;"><img src="https://portal.vitaltrendsusa.com/logo.png" /></td>
        <td style="width:10%;"></td>
    </tr>
</table>
<table>
    <tr>
        <td style="width:100%;"><p style="font-size:13px;text-align:center;">
        <span style="text-align:center;margin-top:5%;font-size:18px;font-weight:bold;">TRÜNORTH GLOBAL<sup style="font-weight:normal;">TM</sup> AUTHORIZED RETAILER AGREEMENT</span><br>
            <span style="font-size:10px;">This Agreement is entered into this date: ____________	, between TrüNorth Global Corporation™, located </span>
            <br><span style="font-size:10px;">at 16740 Birkdale Commons Parkway, Suite 208, Huntersville, North Carolina, 28078, , referred to as “TrüNorth</span><br>
            <span style="font-size:10px;">Global™”, and the entity identified in the box below referred to as “Retailer.”</span>
        </p></td>
    </tr>
</table><br/><br/>
<table cellspacing="0" cellpadding="5" border="1" style="border-color:grey;">
    <tr>
        <td class="fontClass" style="width:100%;padding-left:40px;">&nbsp;&nbsp;&nbsp;Retailer Business Name:</td>
    </tr>
    <tr>
        <td class="fontClass" style="width:100%;">&nbsp;&nbsp;&nbsp;Doing Business As (if applicable):</td>
    </tr>
    <tr class>
        <td style="width:100%;">
            <table border="0">
                <tr>
                    <td class="fontClass" style="width:70%;">Federal Tax ID #:</td>
                    <td class="fontClass" style="width:30%;">D-U-N-S #:</td>
                </tr>
            
            </table>
        </td> 
   </tr>
   <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                    <td class="fontClass" style="width:70%;">Address:</td>
                    <td class="fontClass" style="width:30%;">PO Box/Suite:</td>
                </tr>
            
            </table>
        </td> 
       
    </tr>
    <tr> 
        <td style="width:100%;">
            <table border="0">
                <tr>
                    <td class="fontClass" style="width:40%;margin-left:-15px;">City:</td>
                    <td class="fontClass" style="width:30%;border-left:none;">State/Province:</td>
                    <td class="fontClass" style="width:30%;border-left:none;">Zip/Postal Code:</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr border="0"> 
        <td class="fontClass" border="0" style="width:40%;border-right:0.1px dashed white;">&nbsp;&nbsp;&nbsp;Phone #:</td>
        <td class="fontClass" border="0" style="width:60%;border-left:0.1px dashed white;">Fax #:</td>
    </tr>
    <tr>
        <td class="fontClass" style="width:100%;">&nbsp;&nbsp;&nbsp;Business Email:</td>
    </tr>
    <tr>
        <td class="fontClass">&nbsp;&nbsp;&nbsp;Business Website:</td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:70%;">Primary Contact:</td>
                <td class="fontClass" style="width:30%;">Title:</td>
                </tr>
            
            </table>
        </td> 
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:70%;">Primary Contact Email:</td>
                <td class="fontClass" style="width:30%;">Direct Phone #:</td>
                </tr>
            
            </table>
        </td> 
    </tr>
    <tr>
        <td class="fontClass" style="width:100%;">&nbsp;&nbsp;&nbsp;Accounts Payable Contact:</td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                    <td class="fontClass" style="width:70%;">Accounts Payable Contact Email:</td>
                    <td class="fontClass" style="width:30%;">Direct Phone #:</td>
                </tr>
            </table>
        </td> 
    </tr>
    <tr>
        <td class="fontClass" style="width:100%;">
        <span style="font-weight:bold;">Do you have multiple locations you would like to sign up? ❏ YES or ❏ NO</span><br>
        <span style="font-style:italic;">If, <b>Yes,</b> upon completion of this agreement, a member of Dealer Services will contact you for</span> <br> <span style="font-style:italic;"> additional information.</span>
        </td>
    </tr> 
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:100%;">TrüNorth Global™ and Retailer each agree as follows:</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:2%;">1.</td>
        <td class="fontClass" style="width:98%;">TrüNorth Global™ will provide marketing and sales brochures, Limited Warranty applications, point-of-sale and other materials to assist Retailer in selling Limited Warranties to purchasers (“Purchasers”), enabling such Purchasers to participate in a TrüNorth Global™ Limited Warranty Program. TrüNorth Global™ may change the terms of any Limited Warranty, Limited Warranty Program, or cancel any Limited Warranty Program at any time upon notice to Retailer.</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:2%;">2.</td>
        <td class="fontClass" style="width:98%;">Retailer shall not alter, modify, waive, or discharge any terms or conditions of any Limited Warranty, Limited Warranty Program or the materials provided by TrüNorth Global™. TrüNorth Global™ shall be responsible for the administration of all Limited Warranty Programs, including registration of all approved applications and determination of claim responsibility.</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:2%;">3.</td>
        <td class="fontClass" style="width:98%;">Retailer shall review each Limited Warranty in detail with each Purchaser and explain the terms, conditions, coverage, and limits of liability, as well as the required maintenance and claims responsibilities of each Limited Warranty. Retailer shall obtain each Purchaser’s signature on the Limited Warranty at the time of sale. Once signed, Retailer shall provide each Purchaser with a copy of their Limited Warranty and shall immediately submit a copy of the signed and completed Limited Warranty to TrüNorth Global™ via email, DocuSign, fax, or TrüNorth Global™ Dealer Portal.</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:2%;">4.</td>
        <td class="fontClass" style="width:98%;">Upon receipt of an invoice from TrüNorth Global™ for payment under any Limited Warranty Program, Retailer shall remit such payment to TrüNorth Global™. Invoices are created from the wholesale prices and any applicable charges for such Limited Warranty Programs specified by TrüNorth Global™’s prevailing rate card(s) provided to Retailer. TrüNorth Global™ has the right to change wholesale prices and charges on such rate card(s) upon 60 days prior notice to Retailer.</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:2%;">5.</td>
        <td class="fontClass" style="width:98%;">Retailer may offer and sell Limited Warranties in accordance with this Agreement at retail prices determined by Retailer and/or TrüNorth Global™’s suggested retail price. Retailer is responsible for collection and payment of all federal, state, and local taxes that may apply to the sale of the Limited Warranties by Retailer under this Agreement.</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:2%;">6.</td>
        <td class="fontClass" style="width:98%;">Claims under any Limited Warranty Program can only be made by the Registered Owner listed under Section I. of the Limited Warranty for such Registered Owner. The Registered Owner is completely responsible for the maintenance, transfers, requested documentation, and other requirements as outlined in the Limited Warranty.</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:2%;">7.</td>
        <td class="fontClass" style="width:98%;">This Agreement shall commence on the date set forth above and continue until terminated by either party with 60 days’ notice prior to the renewal date. Upon the termination of this Agreement, Retailer shall return to TrüNorth Global™ all Limited Warranty Program materials and discontinue use of such materials and the TrüNorth Global™ name.</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:2%;">8.</td>
        <td class="fontClass" style="width:98%;">Retailer acknowledges that the Limited Warranty Programs and the materials delivered by TrüNorth Global™ constitute the proprietary property of TrüNorth Global™. TrüNorth Global™ remains the sole owner of such proprietary property. Nothing in this Agreement shall be construed as a transfer, license, or assignment of TrüNorth Global™’s rights in such proprietary property. Retailer shall use the Limited Warranty Programs, materials, and TrüNorth Global™ name solely during the term of this Agreement for purposes of offering and selling the Limited Warranty Program. Limited Warranty Programs shall be fully administered and underwritten by TrüNorth Global™.</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:2%;">9.</td>
        <td class="fontClass" style="width:98%;">TrüNorth Global™ agrees to indemnify and hold Retailer harmless from and against any and all claims, suits, actions, damages, judgments, settlements, liabilities, losses, costs and expenses including reasonable attorney’s fees (“Loss”) arising from any Limited Warranty Program sold by Retailer in accordance with this Agreement, unless such Loss arises from negligence or misconduct of or failure to comply with the terms of this Agreement by Retailer, its contractors, or their respective officers, employees, and agents.</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:3%;">10.</td>
        <td class="fontClass" style="width:97%;">Retailer agrees to indemnify and hold TrüNorth Global™ harmless from any and all Losses arising from the negligence or misconduct of or failure to comply with the terms of this Agreement by Retailer, its contractors or their respective officers, employees, and agents.</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:3%;">11.</td>
        <td class="fontClass" style="width:97%;">Retailer shall not assign, sell, or transfer this Agreement or any of its rights and obligations hereunder without the prior written consent of TrüNorth Global™. No modification, amendment, or supplement to this Agreement shall be effective or binding unless it is made in writing and duly executed by Retailer and TrüNorth Global™.</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:3%;">12.</td>
        <td class="fontClass" style="width:97%;">Dispute Resolution:</td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:2%;"></td>
                <td class="fontClass" style="width:3%;">(a)</td>
                <td class="fontClass" style="width:95%;">This Agreement shall be governed by and construed in accordance with the laws of the State of North Carolina, without regard to conflict of law principles.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:2%;"></td>
                <td class="fontClass" style="width:3%;">(b)</td>
                <td class="fontClass" style="width:95%;">Arbitration Provision and waiver of jury and class action right:</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:7%;"></td>
                <td class="fontClass" style="width:5%;">(i)</td>
                <td class="fontClass" style="width:88%;">In the event of any dispute between the parties arising out of or related to this agreement in any way, including for breach of this agreement, the dispute shall be settled by arbitration administered by the American Arbitration Association (“AAA”). Arbitration is the sole method of dispute resolution between the parties for arbitrable claims.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:7%;"></td>
                <td class="fontClass" style="width:5%;">(ii)</td>
                <td class="fontClass" style="width:88%;">Arbitration shall be administered in accordance with AAA’s Commercial Arbitration Rules, including, where applicable, AAA’s Expedited Procedures for certain commercial disputes. The arbitration will be heard by a single arbitrator selected by AAA. The arbitrator shall have the power to rule on his or her own jurisdiction, including any objections with respect to the existence, scope, or validity of the arbitration agreement or the arbitrability of any claim our counterclaim.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:7%;"></td>
                <td class="fontClass" style="width:5%;">(iii)</td>
                <td class="fontClass" style="width:88%;">Each of the parties will pay equally all arbitration fees and arbitrator compensation.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:7%;"></td>
                <td class="fontClass" style="width:5%;">(iv)</td>
                <td class="fontClass" style="width:88%;">Unless prohibited by law, either party’s Demand for Arbitration must be submitted within one year of when a dispute arises. An arbitration demand is made by sending the Demand for Arbitration to AAA, with a copy to the other party.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:7%;"></td>
                <td class="fontClass" style="width:5%;">(v)</td>
                <td class="fontClass" style="width:88%;">THE PARTIES WAIVE THEIR RIGHT TO A JURY TRIAL and other rights associated with civil lawsuits.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:7%;"></td>
                <td class="fontClass" style="width:5%;">(vi)</td>
                <td class="fontClass" style="width:88%;">The in-person arbitration hearing will take place only in Mecklenburg County, North Carolina, unless both parties agree in writing to a different hearing location.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:7%;"></td>
                <td class="fontClass" style="width:5%;">(vii)</td>
                <td class="fontClass" style="width:88%;">A decision of the arbitrator will be binding and final.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:7%;"></td>
                <td class="fontClass" style="width:5%;">(viii)</td>
                <td class="fontClass" style="width:88%;">The determination and award of the arbitrator may be filed by the prevailing party in a court of proper jurisdiction and shall thereafter have the full force and effect of a judgment at law.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:7%;"></td>
                <td class="fontClass" style="width:5%;">(ix)</td>
                <td class="fontClass" style="width:88%;">This arbitration provision contains mutual benefits and is binding upon all parties, their successors, and assigns. This arbitration provision will survive bankruptcy and will survive any termination, amendment, expiration, or performance of the Agreement.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:7%;"></td>
                <td class="fontClass" style="width:5%;">(x)</td>
                <td class="fontClass" style="width:88%;">If any portion of this arbitration provision is held invalid, the remainder shall remain in effect.</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                <td class="fontClass" style="width:7%;"></td>
                <td class="fontClass" style="width:5%;">(c)</td>
                <td class="fontClass" style="width:88%;">For disputes not submitted to arbitration, each party hereby consents to the jurisdiction and venue of the state courts of Mecklenburg County, North Carolina, for the resolution of any dispute or controversy arising out of or related this Agreement in any way, including for breach of this agreement.</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:3%;">13.</td>
        <td class="fontClass" style="width:97%;">The information disclosed or made available by or on behalf of TrüNorth Global™ to Retailer, the terms of this Agreement and the terms of any other agreement between the parties are strictly confidential and may not be disclosed by Retailer or used for any purpose other than marketing and selling Limited Warranties to purchasers. This provision will survive any termination, amendment, expiration, or performance of the Agreement.</td>
    </tr>
</table>
<br/><br/>
<table>
    <tr>
        <td class="fontClass" style="width:3%;">14.</td>
        <td class="fontClass" style="width:97%;">If any term or provision of this Agreement, or the application thereof to any person or circumstance, shall be declared invalid or unenforceable by any court or governmental agency of competent jurisdiction, the remainder of this Agreement, or the application of such provision to persons or circumstances other than those to which it is invalid or unenforceable, shall not be affected thereby, and each provision of this Agreement shall be valid and enforceable to the fullest extent permitted by law.</td>
    </tr>
</table>
<br/><br/>
<table cellspacing="0" cellpadding="5" border="1" style="border-color:gray;">
    <tr>
        <td class="fontClass">&nbsp;&nbsp;&nbsp;Retailer Signature:</td>
    </tr>
    <tr> 
        <td style="width:100%;">
            <table border="0">
                <tr>
                    <td class="fontClass" style="width:40%;margin-left:-15px;">Retailer Name:</td>
                    <td class="fontClass" style="width:30%;border-left:none;">Title:</td>
                    <td class="fontClass" style="width:30%;border-left:none;">Date:</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br><br>
<table cellspacing="0" cellpadding="5" border="1" style="border-color:gray;">
    <tr>
        <td class="fontClass">&nbsp;&nbsp;&nbsp;To Be Completed by TrüNorth Global™</td>
    </tr>
    <tr>
        <td style="width:100%;">
            <table border="0">
                <tr>
                    <td class="fontClass" style="width:70%;">Assigned Retailer #:</td>
                    <td class="fontClass" style="width:30%;">Date</td>
                </tr>
            </table>
        </td> 
    </tr>
    <tr>
        <td class="fontClass" style="width:100%;">&nbsp;&nbsp;&nbsp;Assigned Program(s)</td>
    </tr>
</table>
<p class="fontClass">TrüNorth Global™ Signature:___________________________________________________________________Date:___________________________________</p>
';

// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('example_001.pdf', 'I');
//$pdf->Output('D:/tester.pdf', 'F');
//============================================================+
// END OF FILE
//============================================================+
