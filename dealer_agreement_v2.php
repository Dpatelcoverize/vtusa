<?php
//
// File: dealer_agreement.php
// Author: Charles Parry
// Date: 5/7/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Contracts Home";
$pageTitle = "Contracts";


// Connect to DB
require_once "includes/dbConnect.php";


// Variables.
$agreementDate = "";
$dealerName  = "";
$dba = "";
$federalTaxID = "";
$duns = "";
$dealerAddress1 = "";
$dealerAddress2 = "";
$dealerCity = "";
$dealerState = "";
$dealerZip = "";
$dealerPhone = "";
$dealerFax = "";
$businessEmail = "";
$businessWebsite = "";
$primaryContact = "";
$primaryContactPhone = "";
$primaryContactEmail = "";
$accountsPayableContact = "";
$accountsPayableContactPhone = "";
$accountsPayableContactEmail = "";
$retailerName = "";
$retailerTitle = "";
$signedOnDate = "";
$shippingAddress1 = "";
$shippingCity = "";
$shippingState = "";
$shippingZip = "";


$form_err    = "";


if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

// Process form data when form is submitted.
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if dealer name is empty
    if(empty(trim($_POST["dealerName"]))){
        $form_err = "Please enter dealer name.";
    } else{
        $dealerName = trim($_POST["dealerName"]);
    }

    // Check if primary contact email is empty
    if(empty(trim($_POST["primaryContactEmail"]))){
        $form_err = "Please enter primary contact email.";
    } else{
        $primaryContactEmail = trim($_POST["primaryContactEmail"]);
    }

    // Get remaining form fields
    if(!empty(trim($_POST["agreementDate"]))){
        $agreementDate = trim($_POST["agreementDate"]);
    }

    if(!empty(trim($_POST["dba"]))){
        $dba = trim($_POST["dba"]);
    }

    if(!empty(trim($_POST["taxID"]))){
        $federalTaxID = trim($_POST["taxID"]);
    }

    if(!empty(trim($_POST["duns"]))){
        $duns = trim($_POST["duns"]);
    }

    if(!empty(trim($_POST["dealerAddress"]))){
        $dealerAddress1 = trim($_POST["dealerAddress"]);
    }

    if(!empty(trim($_POST["poBox"]))){
        $dealerAddress2 = trim($_POST["poBox"]);
    }

    if(!empty(trim($_POST["dealerCity"]))){
        $dealerCity = trim($_POST["dealerCity"]);
    }

    if(!empty(trim($_POST["dealerState"]))){
        $dealerState = trim($_POST["dealerState"]);
    }

    if(!empty(trim($_POST["zipCode"]))){
        $dealerZip = trim($_POST["zipCode"]);
    }

    if(!empty(trim($_POST["dealerPhone"]))){
        $dealerPhone = trim($_POST["dealerPhone"]);
    }


    echo "<br /><br />";
    echo "<br /><br />";
    echo "dealerphone=".$dealerPhone;
    echo "<br /><br />";
    echo "<br /><br />";

//die();

    // Simulate connectivity
    echo "got dealer name = ".$dealerName;
    echo "<br /><br />";
    //sleep(30);
    echo "pushing details to truNorth";
    echo "<br /><br />";
    //sleep(30);
    echo "transferring data to CRM database";
    echo "<br /><br />";


    // Prepare a select statement
    $sql = "SELECT userID, username, password FROM Users WHERE username = 'cparry'";
    $result = $link->query($sql);
    while ($row = $result->fetch_row()) {
        print_r($row);
    }


    /* Prepare an insert statement to created an Acct entry */
    $stmt = mysqli_prepare($link, "INSERT INTO Acct (Acct_Nm) VALUES (?)");

    /* Bind variables to parameters */
    $val1 = $dealerName;
    mysqli_stmt_bind_param($stmt, "s", $val1);

    /* Execute the statement */
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $last_id = mysqli_insert_id($link);
        echo "<br /><br />New record created successfully. Last inserted ID is: " . $last_id;

        // Now that we have the newly created dealerID, we need to do several things:
        //   - add the ID to a session var
        //   - insert dealer email, phone and address to respective tables using this ID
        //   - update the dealer_agreement_tracking table with forms so far completed (only Dealer Agreement so far)
        //   - redirect back to next form in the series.
        //

        /* Create an entry in Users for this new Dealer. */
        $stmt = mysqli_prepare($link, "INSERT INTO Users (Acct_ID,Role_ID,username,password,createdDate) VALUES (?,2,?,'PASSWORD',NOW())");

        /* Bind variables to parameters */
        $val1 = $dealerName;
        mysqli_stmt_bind_param($stmt, "is", $last_id, $primaryContactEmail);

        /* Execute the statement */
        $result = mysqli_stmt_execute($stmt);


        // Now, create a local session to authenticate this newly created Dealer user.
        // Store data in session variables
        $_SESSION["loggedin"] = true;
        $_SESSION["id"] = $last_id;
        $_SESSION["username"] = $primaryContactEmail;
        $_SESSION["userType"] = "dealer";

        echo "session username is now = ".$_SESSION["username"];

    } else {
        echo "<br /><br />Error: " . $sql . "<br>" . mysqli_error($link);
    }

    echo "<br /><br />";

    ?>
    <a href="dealer_agreement_v2.php">Return to Dealer Agreement</a>
    <?php

    die();
}


require_once("includes/header.php");

?>

<!--**********************************
    Content body start
***********************************-->
<div class="content-body" id="dealer-form">
    <!-- row -->
    <div class="container-fluid" >
        <div class="row">
            <div class="col-md-6">
                <div class="logo">
                    <img src="images/vt_logo.png" alt="Vital Trends">
                </div>
            </div>
            <div class="col-md-6">
                <div class="logo">
                    <img src="images/trunorth-logo.png" alt="Vital Trends">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h4 class="card-title" id="dealer_agreement">Dealer Agreement</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form ">
                            <div class="watermark">
                                <img src="images/logo_large_bg.png" alt="">
                            </div>
                            <form name="dealerForm" method="POST" class="ajax-form" action="javascript:void(0)" data-action="backend/dealer_ajax_store.php" >
                                <div class="col-md-12">
                                    <div class="alert alert-success" style="display: none" id="success_box"><span id="success_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="alert alert-danger" style="display: none" id="danger_box"><span id="danger_error"></span>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Agreement Date</label>
                                        <input type="text" class="form-control" name="agreementDate" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Dealer Name</label>
                                        <input type="text" class="form-control" name="dealerName" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>DBA</label>
                                        <input type="text" class="form-control" name="dba" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Federal Tax ID</label>
                                        <input type="text" class="form-control" name="taxID" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>D-U-N-S</label>
                                        <input type="text" class="form-control" name="duns" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Address</label>
                                        <input type="text" class="form-control" name="dealerAddress" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>PO Box / Suite</label>
                                        <input type="text" class="form-control" name="poBox" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>City</label>
                                        <input type="text" class="form-control" name="dealerCity" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>State</label>
                                        <input type="text" class="form-control" name="dealerState" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Zip Code</label>
                                        <input type="text" class="form-control" name="zipCode" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Phone Number</label>
                                        <input type="phone" class="form-control" name="dealerPhone" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Fax</label>
                                        <input type="text" class="form-control" name="dealerFax" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Dealer License</label>
                                        <input type="text" class="form-control" name="dealerLicense" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Business Email</label>
                                        <input type="email" class="form-control" name="businessEmail" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Business Website</label>
                                        <input type="url" class="form-control" name="businessWebsite" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Primary Contact</label>
                                        <input type="text" class="form-control" name="primaryContact" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Primary Contact Phone</label>
                                        <input type="phone" class="form-control" name="primaryContactPhone" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Primary Contact Email</label>
                                        <input type="email" class="form-control" name="primaryContactEmail" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Accounts Payable Contact</label>
                                        <input type="text" class="form-control" name="accountsPayableContact" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Accounts Payable Contact Phone</label>
                                        <input type="phone" class="form-control" name="accountsPayableContactPhone" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Accounts Payable Contact Email</label>
                                        <input type="email" class="form-control" name="accountsPayableContactEmail">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Shipping Address</label>
                                        <input type="text" class="form-control" name="shipAddress" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Shipping City</label>
                                        <input type="text" class="form-control" name="shipCity" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Shipping State</label>
                                        <input type="text" class="form-control" name="shipState" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Shipping Zipcode</label>
                                        <input type="text" class="form-control" name="shipZip" required>
                                    </div>
                                    <div class="form-group col-md-12 terms-text">
                                        <p>
                                            pTrüNorth Global™ and Retailer each agree as follows:
                                            <br>
                                            <br>
                                            1. TrüNorth Global™ will provide marketing and sales brochures, Limited Warranty applications, point-of-sale and other
                                            <br>
                                            materials to assist Retailer in selling Limited Warranties to purchasers (“Purchasers”), enabling such Purchasers to
                                            <br>
                                            participate in a TrüNorth Global™ Limited Warranty Program. TrüNorth Global™ may change the terms of any Limited
                                            <br>
                                            Warranty, Limited Warranty Program, or cancel any Limited Warranty Program at any time upon notice to Retailer.
                                            <br><br>
                                            2. Retailer shall not alter, modify, waive, or discharge any terms or conditions of any Limited Warranty, Limited
                                            <br>
                                            Warranty Program or the materials provided by TrüNorth Global™. TrüNorth Global™ shall be responsible for the
                                            <br>
                                            administration of all Limited Warranty Programs, including registration of all approved applications and
                                            <br>
                                            determination of claim responsibility.
                                            <br>
                                            3. Retailer shall review each Limited Warranty in detail with each Purchaser and explain the terms, conditions, coverage,
                                            <br><br>
                                            and limits of liability, as well as the required maintenance and claims responsibilities of each Limited Warranty.
                                            <br>
                                            Retailer shall obtain each Purchaser’s signature on the Limited Warranty at the time of sale. Once signed, Retailer
                                            <br>
                                            shall provide each Purchaser with a copy of their Limited Warranty and shall immediately submit a copy of the signed
                                            <br>
                                            and completed Limited Warranty to TrüNorth Global™ via email, DocuSign, fax, or TrüNorth Global™ Dealer Portal.
                                            <br><br>
                                            4. Upon receipt of an invoice from TrüNorth Global™ for payment under any Limited Warranty Program, Retailer shall
                                            <br>
                                            remit such payment to TrüNorth Global™. Invoices are created from the wholesale prices and any applicable charges
                                            <br>
                                            for such Limited Warranty Programs specified by TrüNorth Global™’s prevailing rate card(s) provided to Retailer.
                                            <br>
                                            TrüNorth Global™ has the right to change wholesale prices and charges on such rate card(s) upon 60 days prior notice
                                            <br>
                                            to Retailer.
                                            <br><br>
                                            5. Retailer may offer and sell Limited Warranties in accordance with this Agreement at retail prices determined by
                                            <br>
                                            Retailer and/or TrüNorth Global™’s suggested retail price. Retailer is responsible for collection and payment of all
                                            <br>
                                            federal, state, and local taxes that may apply to the sale of the Limited Warranties by Retailer under this Agreement.
                                            <br><br>
                                            6. Claims under any Limited Warranty Program can only be made by the Registered Owner listed under Section I. of
                                            <br>
                                            the Limited Warranty for such Registered Owner. The Registered Owner is completely responsible for the
                                            <br>
                                            maintenance, transfers, requested documentation, and other requirements as outlined in the Limited Warranty.
                                            <br><br>
                                            7. This Agreement shall commence on the date set forth above and continue until terminated by either party with 60
                                            <br>
                                            days’ notice prior to the renewal date. Upon the termination of this Agreement, Retailer shall return to TrüNorth
                                            <br>
                                            Global™ all Limited Warranty Program materials and discontinue use of such materials and the TrüNorth Global™
                                            <br>
                                            name.
                                            <br><br>
                                            8. Retailer acknowledges that the Limited Warranty Programs and the materials delivered by TrüNorth Global™
                                            <br>
                                            constitute the proprietary property of TrüNorth Global™. TrüNorth Global™ remains the sole owner of such
                                            <br>
                                            proprietary property. Nothing in this Agreement shall be construed as a transfer, license, or assignment of TrüNorth
                                            <br>
                                            Global™’s rights in such proprietary property. Retailer shall use the Limited Warranty Programs, materials, and
                                            <br>
                                            TrüNorth Global™ name solely during the term of this Agreement for purposes of offering and selling the Limited
                                            <br>
                                            Warranty Program. Limited Warranty Programs shall be fully administered and underwritten by TrüNorth Global™.
                                            <br><br>
                                            9. TrüNorth Global™ agrees to indemnify and hold Retailer harmless from and against any and all claims, suits, actions,
                                            <br>
                                            damages, judgments, settlements, liabilities, losses, costs and expenses including reasonable attorney’s fees (“Loss”)
                                            <br>
                                            arising from any Limited Warranty Program sold by Retailer in accordance with this Agreement, unless such Loss
                                            <br>
                                            arises from negligence or misconduct of or failure to comply with the terms of this Agreement by Retailer, its
                                            <br>
                                            contractors, or their respective officers, employees, and agents.
                                            <br><br>
                                            10. Retailer agrees to indemnify and hold TrüNorth Global™ harmless from any and all Losses arising from the
                                            <br>
                                            negligence or misconduct of or failure to comply with the terms of this Agreement by Retailer, its contractors or their
                                            <br>
                                            respective officers, employees, and agents.
                                            <br><br>
                                            11. Retailer shall not assign, sell, or transfer this Agreement or any of its rights and obligations hereunder without the
                                            <br>
                                            prior written consent of TrüNorth Global™. No modification, amendment, or supplement to this Agreement shall be
                                            <br>
                                            effective or binding unless it is made in writing and duly executed by Retailer and TrüNorth Global™.
                                            <br><br>
                                            12. Dispute Resolution:
                                            <br><br>
                                            (a) This Agreement shall be governed by and construed in accordance with the laws of the State of North Carolina,
                                            <br>
                                            without regard to conflict of law principles.
                                            <br><br>
                                            (b) Arbitration Provision and waiver of jury and class action right:
                                            <br><br>
                                            (i) In the event of any dispute between the parties arising out of or related to this agreement in any way,
                                            <br>
                                            including for breach of this agreement, the dispute shall be settled by arbitration administered by the
                                            <br>
                                            American Arbitration Association (“AAA”). Arbitration is the sole method of dispute resolution
                                            <br>
                                            between the parties for arbitrable claims.
                                            <br><br>
                                            (ii) Arbitration shall be administered in accordance with AAA’s Commercial Arbitration Rules, including,
                                            <br>
                                            where applicable, AAA’s Expedited Procedures for certain commercial disputes. The arbitration will be
                                            <br>
                                            heard by a single arbitrator selected by AAA. The arbitrator shall have the power to rule on his or her
                                            <br>
                                            own jurisdiction, including any objections with respect to the existence, scope, or validity of the
                                            <br>
                                            arbitration agreement or the arbitrability of any claim our counterclaim.
                                            <br><br>
                                            (iii) Each of the parties will pay equally all arbitration fees and arbitrator compensation.
                                        </p>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <h6 class="font-weight-normal">Click to sign</h6>
                                        <input type="text" id="txt" style="border-radius: 5px;">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Retailer Name</label>
                                        <input type="text" class="form-control" name="retailerName" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Retailer Title</label>
                                        <input type="text" class="form-control" name="retailerTitle">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>signedOnDate</label>
                                        <input type="text" class="form-control" name="signedOnDate">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
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
        <p>Copyright © Designed &amp; Developed by <a href="http://dexignzone.com/" target="_blank">DexignZone</a> 2020</p>
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
    //sketch lib
    (function () {
        var __slice = [].slice;

        (function ($) {
            var Sketch;
            $.fn.sketch = function () {
                var args, key, sketch;
                key = arguments[0], args = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
                if (this.length > 1) {
                    $.error('Sketch.js can only be called on one element at a time.');
                }
                sketch = this.data('sketch');
                if (typeof key === 'string' && sketch) {
                    if (sketch[key]) {
                        if (typeof sketch[key] === 'function') {
                            return sketch[key].apply(sketch, args);
                        } else if (args.length === 0) {
                            return sketch[key];
                        } else if (args.length === 1) {
                            return sketch[key] = args[0];
                        }
                    } else {
                        return $.error('Sketch.js did not recognize the given command.');
                    }
                } else if (sketch) {
                    return sketch;
                } else {
                    this.data('sketch', new Sketch(this.get(0), key));
                    return this;
                }
            };
            Sketch = (function () {

                function Sketch(el, opts) {
                    this.el = el;
                    this.canvas = $(el);
                    this.context = el.getContext('2d');
                    this.options = $.extend({
                        toolLinks: true,
                        defaultTool: 'marker',
                        defaultColor: '#000000',
                        defaultSize: 2
                    }, opts);
                    this.painting = false;
                    this.color = this.options.defaultColor;
                    this.size = this.options.defaultSize;
                    this.tool = this.options.defaultTool;
                    this.actions = [];
                    this.action = [];
                    this.canvas.bind('click mousedown mouseup mousemove mouseleave mouseout touchstart touchmove touchend touchcancel', this.onEvent);
                    if (this.options.toolLinks) {
                        $('body').delegate("a[href=\"#" + (this.canvas.attr('id')) + "\"]", 'click', function (e) {
                            var $canvas, $this, key, sketch, _i, _len, _ref;
                            $this = $(this);
                            $canvas = $($this.attr('href'));
                            sketch = $canvas.data('sketch');
                            _ref = ['color', 'size', 'tool'];
                            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                                key = _ref[_i];
                                if ($this.attr("data-" + key)) {
                                    sketch.set(key, $(this).attr("data-" + key));
                                }
                            }
                            if ($(this).attr('data-download')) {
                                sketch.download($(this).attr('data-download'));
                            }
                            return false;
                        });
                    }
                }

                Sketch.prototype.download = function (format) {
                    var mime;
                    format || (format = "png");
                    if (format === "jpg") {
                        format = "jpeg";
                    }
                    mime = "image/" + format;
                    return window.open(this.el.toDataURL(mime));
                };

                Sketch.prototype.set = function (key, value) {
                    this[key] = value;
                    return this.canvas.trigger("sketch.change" + key, value);
                };

                Sketch.prototype.startPainting = function () {
                    this.painting = true;
                    return this.action = {
                        tool: this.tool,
                        color: this.color,
                        size: parseFloat(this.size),
                        events: []
                    };
                };


                Sketch.prototype.stopPainting = function () {
                    if (this.action) {
                        this.actions.push(this.action);
                    }
                    this.painting = false;
                    this.action = null;
                    return this.redraw();
                };

                Sketch.prototype.onEvent = function (e) {
                    if (e.originalEvent && e.originalEvent.targetTouches) {
                        e.pageX = e.originalEvent.targetTouches[0].pageX;
                        e.pageY = e.originalEvent.targetTouches[0].pageY;
                    }
                    $.sketch.tools[$(this).data('sketch').tool].onEvent.call($(this).data('sketch'), e);
                    e.preventDefault();
                    return false;
                };

                Sketch.prototype.redraw = function () {
                    var sketch;
                    //this.el.width = this.canvas.width();
                    this.context = this.el.getContext('2d');
                    sketch = this;
                    $.each(this.actions, function () {
                        if (this.tool) {
                            return $.sketch.tools[this.tool].draw.call(sketch, this);
                        }
                    });
                    if (this.painting && this.action) {
                        return $.sketch.tools[this.action.tool].draw.call(sketch, this.action);
                    }
                };

                return Sketch;

            })();
            $.sketch = {
                tools: {}
            };
            $.sketch.tools.marker = {
                onEvent: function (e) {
                    switch (e.type) {
                        case 'mousedown':
                        case 'touchstart':
                            if (this.painting) {
                                this.stopPainting();
                            }
                            this.startPainting();
                            break;
                        case 'mouseup':
                        //return this.context.globalCompositeOperation = oldcomposite;
                        case 'mouseout':
                        case 'mouseleave':
                        case 'touchend':
                        //this.stopPainting();
                        case 'touchcancel':
                            this.stopPainting();
                    }
                    if (this.painting) {
                        this.action.events.push({
                            x: e.pageX - this.canvas.offset().left,
                            y: e.pageY - this.canvas.offset().top,
                            event: e.type
                        });
                        return this.redraw();
                    }
                },
                draw: function (action) {
                    var event, previous, _i, _len, _ref;
                    this.context.lineJoin = "round";
                    this.context.lineCap = "round";
                    this.context.beginPath();
                    this.context.moveTo(action.events[0].x, action.events[0].y);
                    _ref = action.events;
                    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                        event = _ref[_i];
                        this.context.lineTo(event.x, event.y);
                        previous = event;
                    }
                    this.context.strokeStyle = action.color;
                    this.context.lineWidth = action.size;
                    return this.context.stroke();
                }
            };
            return $.sketch.tools.eraser = {
                onEvent: function (e) {
                    return $.sketch.tools.marker.onEvent.call(this, e);
                },
                draw: function (action) {
                    var oldcomposite;
                    oldcomposite = this.context.globalCompositeOperation;
                    this.context.globalCompositeOperation = "destination-out";
                    action.color = "rgba(0,0,0,1)";
                    $.sketch.tools.marker.draw.call(this, action);
                    return this.context.globalCompositeOperation = oldcomposite;
                }
            };
        })(jQuery);

    }).call(this);


    (function ($) {
        $.fn.SignaturePad = function (options) {

            //update the settings
            var settings = $.extend({
                allowToSign: true,
                img64: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
                border: '1px solid #c7c8c9',
                width: '300px',
                height: '150px',
                callback: function () {
                    return true;
                }
            }, options);

            //control should be a textbox
            //loop all the controls
            var id = 0;

            //add a very big pad
            var big_pad = $('#signPadBig');
            var back_drop = $('#signPadBigBackDrop');
            var canvas = undefined;
            if (big_pad.length == 0) {

                back_drop = $('<div>')
                back_drop.css('position', 'fixed');
                back_drop.css('top', '0');
                back_drop.css('right', '0');
                back_drop.css('bottom', '0');
                back_drop.css('left', '0');
                back_drop.css('z-index', '1040 !important');
                back_drop.css('background-color', '#000');
                back_drop.css('display', 'none');
                back_drop.css('filter', 'alpha(opacity=50)');
                back_drop.css('opacity', '0.5');
                $('body').append(back_drop);

                big_pad = $('<div>');
                big_pad.css('display', 'none');
                big_pad.css('position', 'fixed');
                big_pad.css('margin', 'auto');
                big_pad.css('top', '0');
                big_pad.css('bottom', '0');
                big_pad.css('right', '0');
                big_pad.css('left', '0');
                big_pad.css('z-index', '1000002 !important');
                big_pad.css('overflow', 'hidden');
                big_pad.css('outline', '0');
                big_pad.css('-webkit-overflow-scrolling', 'touch');

                big_pad.css('right', '0');
                big_pad.css('border', '1px solid #c8c8c8');
                big_pad.css('padding', '15px');
                big_pad.css('background-color', 'white');
                big_pad.css('margin-top', 'auto');
                big_pad.css('width', '50%');
                big_pad.css('height', '40%');
                big_pad.css('z-index', '999999999');
                big_pad.css('border-radius', '10px');
                big_pad.attr('id', 'signPadBig');
                $('body').append(big_pad);

                var update_canvas_size = function () {
                    var w = big_pad.width() //* 0.95;
                    var h = big_pad.height() - 55;

                    canvas.attr('width', w);
                    // canvas.attr('height', h);
                }


                canvas = $('<canvas>');
                canvas.css('display', 'block');
                canvas.css('margin', '0 auto');
                canvas.css('border', '1px solid #c8c8c8');
                canvas.css('border-radius', '10px');
                canvas.css('width', 'auto');
                canvas.css('height', 'auto');
                big_pad.append(canvas);

                update_canvas_size();
                $(window).on('resize', function () {
                    update_canvas_size();
                });

                var clearCanvas = function () {
                    canvas.sketch().action = null;
                    canvas.sketch().actions = [];       // this line empties the actions.
                    var ctx = canvas[0].getContext("2d");
                    ctx.clearRect(0, 0, canvas[0].width, canvas[0].height);
                    return true
                }

                var _get_base64_value = function () {
                    var text_control = $.data(big_pad[0], 'control');  //settings.control; // $('#' + big_pad.attr('id'));
                    return $(text_control).val();
                }

                var copyCanvas = function () {
                    //get data from bigger pad
                    var sigData = canvas[0].toDataURL("image/png");

                    var _img = new Image;
                    _img.onload = resizeImage;
                    _img.src = sigData;

                    var targetWidth = canvas.width();
                    var targetHeight = canvas.height();

                    function resizeImage() {
                        var imageToDataUri = function (img, width, height) {

                            // create an off-screen canvas
                            var canvas = document.createElement('canvas'),
                                ctx = canvas.getContext('2d');

                            // set its dimension to target size
                            canvas.width = width;
                            canvas.height = height;

                            // draw source image into the off-screen canvas:
                            ctx.drawImage(img, 0, 0, width, height);

                            // encode image to data-uri with base64 version of compressed image
                            return canvas.toDataURL();
                        }

                        var newDataUri = imageToDataUri(this, targetWidth, targetHeight);
                        var control_img = $.data(big_pad[0], 'img');
                        if (control_img)
                            $(control_img).attr("src", newDataUri);

                        var text_control = $.data(big_pad[0], 'control');  //settings.control; // $('#' + big_pad.attr('id'));
                        if (text_control)
                            $(text_control).val(newDataUri);
                    }
                }

                var buttons = [
                    {
                        title: 'Close',
                        callback: function () {
                            clearCanvas();
                            big_pad.slideToggle(function () {
                                back_drop.hide('fade');
                            });

                        }
                    },
                    {
                        title: 'Clear',
                        callback: function () {
                            clearCanvas();
                            if (settings.callback)
                                settings.callback(_get_base64_value(), 'clear');
                        }
                    },
                    {
                        title: 'Accept',
                        callback: function () {
                            copyCanvas();
                            clearCanvas();
                            big_pad.slideToggle(function () {
                                back_drop.hide('fade', function () {
                                    if (settings.callback)
                                        settings.callback(_get_base64_value(), 'accept');
                                });
                            });
                        }
                    }].forEach(function (e) {
                    var btn = $('<button>');
                    btn.attr('type', 'button');
                    btn.css('border', '1px solid #c8c8c8');
                    btn.css('background-color', 'white');
                    btn.css('padding', '10px');
                    btn.css('display', 'block');
                    btn.css('margin-top', '15px');
                    btn.css('margin-right', '5px');
                    btn.css('cursor', 'pointer');
                    btn.css('border-radius', '5px');
                    btn.css('float', 'right');
                    btn.css('height', '40px');
                    btn.text(e.title);
                    btn.on('click', function () {
                        e.callback(e.title);
                    })
                    big_pad.append(btn);

                });

            }
            else {
                canvas = big_pad.find('canvas')[0];
            }

            //init the signpad
            if (canvas) {
                var sign1big = $(canvas).sketch({ defaultColor: "#000", defaultSize: 5 });
            }

            //for each control
            return this.each(function () {

                var control = $(this);
                control.hide();

                //get the control parent
                var wrapper = control.parent();
                var img = $('<img>');

                //style it
                img.css("cursor", "pointer");
                img.css("border", settings.border);
                img.css("height", settings.height);
                img.css("width", settings.width);
                img.css('border-radius', '5px')
                img.attr("src", settings.img64);
                img.attr("id", 'signature');

                if (typeof (wrapper) == 'object') {
                    wrapper.append(img);
                }

                //init the big sign pad
                if (settings.allowToSign == true) {
                    //click to the pad bigger
                    img.on('click', function () {
                        //show the pad
                        back_drop.show();
                        big_pad.slideToggle();

                        //save control to use later
                        $.data(big_pad[0], 'img', img);
                        $.data(big_pad[0], 'control', control);

                        //settings.control = control;
                        //settings.img = img;
                    });
                }
            });
        };


    })(jQuery);

    $(document).ready(function () {
        var sign = $('#txt').SignaturePad({
            allowToSign: true,
            img64: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            border: '1px solid #c7c8c9',
            width: '300px',
            height: '150px',
            callback: function (data, action) {
                console.log(data);
            }
        });
    })
</script>

<script>
    $(document).on('submit',".ajax-form",function(e){

        e.preventDefault();
        var form = $(this);
        var image = $('#signature').attr('src');
        var formDataToUpload = new FormData(this);
        formDataToUpload.append("image",image);

        $.ajax({
            type:'POST',
            url:form.attr("data-action"),
            data:formDataToUpload,
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'json',
            success:function (data) {
                if(data.status == 200){
                    $(".ajax-form").trigger('reset');
                    $( "#signature" ).attr('src','data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

                    // toastr.success(data.message)
                    $('#success_error').html(data.message);
                    $('#success_box').show()
                    $('html, body').animate({
                        scrollTop: $("#dealer_agreement").offset().top
                    }, 1000);
                }else{
                    $('#danger_error').html(data.message);
                    $('#danger_box').show()
                    $('html, body').animate({
                        scrollTop: $("#dealer_agreement").offset().top
                    }, 1000);
                    // toastr.error(data.message)
                }
            }
        })
    });

</script>

</body>
</html>