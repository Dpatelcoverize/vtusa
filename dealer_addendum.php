<?php
//
// File: dealer_addendum.php
// Author: Charles Parry
// Date: 5/12/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Dealer Addendum";
$pageTitle = "Dealer Addendum";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');

// Variables.
$dealerID = "";
$agreementDate = "";
$dealerName = "";
$dba = "";
$dealerAddress1 = "";
$dealerAddress2 = "";
$dealerCity = "";
$dealerState = "";
$dealerZip = "";
$dealerPhone = "";
$dealerFax = "";
$primaryContact = "";
$primaryContactPhone = "";
$primaryContactEmail = "";

$form_err = "";


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



// Process form data when form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Save the signature
    // handle signature
    $fileName = "";
    try {
        $fields = (object)$_POST;
        if ($fields->signature_type == 'signature1') {
            $image = base64_decode($fields->signature);
            $imageData = $_POST['signature'];
            $imageDataBase30 = $_POST['base30'];

            list($type, $imageData) = explode(';', $imageData);
            list(, $extension) = explode('/', $type);
            // die($extension);
            list(, $imageData) = explode(',', $imageData);
            // $fileName = uniqid().'.'.$extension;
            $data = explode('+', $extension);
            // die($data[0]);
            $fileName = uniqid() . '.' . $data[0];

            $imageData = base64_decode($imageData);
            $image = 'uploads/' . $fileName;
            file_put_contents($image, $imageData);
        } else if ($fields->signature_type == 'signature2') {
            $fileName = uniqid() . '.svg';
            $filePath = 'uploads/' . $fileName;
            if (!generateSVGFromImage($_FILES["signatureFile"]["tmp_name"], $filePath)) {
                throw new Exception('Something went wrong while converting to svg image');
            }
            $imageDataBase30 = null;
        }

        $my_date = date("Y-m-d H:i:s");
    } catch (Exception $exception) {
        //echo json_encode(['status'=>400,'message'=>$exception->getMessage()]);
    }

    // Update tracker for dealer forms, to indicate the addendum is signed
    $stmt = mysqli_prepare($link, "UPDATE Dealer_Progress SET Addendum_Signature=?,Addendum_Signature_Base30=?,Dealer_Addendum_Complete='Y',Addendum_Signed_Date=NOW() WHERE Acct_ID=?");

    /* Bind variables to parameters */
    $val1 = $fileName;
    $val2 = $imageDataBase30;
    $val3 = $dealerID;

    mysqli_stmt_bind_param($stmt, "ssi", $val1, $val2, $val3);

    /* Execute the statement */
    $result = mysqli_stmt_execute($stmt);

    // Genertae PDF	// Start PDF Code here....
    $generatePdf = true;

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
        $generatePdf = false;
    }
    // Look up the state name
    if ($dealerState > 0) {
        $query = "SELECT * FROM St_Prov WHERE St_Prov_ID=" . $dealerState;
        $result = $link->query($query);
        $row = $result->fetch_assoc();

        $dealerStateName = $row["St_Prov_ISO_2_Cd"];
    } else {
        $dealerStateName = "None Found";
    }

    // Get the dealer info
    $query = "SELECT * FROM Acct WHERE Acct_ID=" . $dealerID;
    $result = $link->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $dealerName = $row["Acct_Nm"];
    } else {
        $dealerName = "";
        $generatePdf = false;
    }


    // Get the dealer dba info
    $query = "SELECT * FROM Altn_Nm WHERE Acct_ID=" . $dealerID;
    $result = $link->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $dba = $row["Altn_Nm"];
    } else {
        $generatePdf = false;
        $dba = "";
    }

    // Get primary contact info
    $query = "SELECT * FROM Pers WHERE Cntct_Prsn_For_Acct_Flg='Y' AND Acct_ID=" . $dealerID;
    $result = $link->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $primaryContact = $row["Pers_Full_Nm"];
    } else {
        $primaryContact = "";
        $generatePdf = false;
    }

    // Get the contract info
    $query = "SELECT cd.Contract_Date FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
    $result = $link->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $agreementDate = $row["Contract_Date"];
    } else {
        $agreementDate = "";
        $generatePdf = false;
    }

    if ($generatePdf) {
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->setCreator(PDF_CREATOR);
        $pdf->setAuthor('ADDENDUM');
        $pdf->setTitle('ADDENDUM');
        $pdf->setSubject('ADDENDUM');
        $pdf->setKeywords('ADDENDUM, Vital, Data, Set, Guide');

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

        $html = '
            <style type="text/css">
                .fontClass{
                    font-size: 13px;
                    font-weight:normal;
                }
                .textJustify{
                    text-align:justify;
                }
            </style>
            <table>
                <tr>
                    <td style="width:20%;"></td>
                    <td style="width:70%;text-align: center;vertical-align: middle;width:60%;"><img src="images/TM2.png" /></td>
                    <td style="width:10%;"></td>
                </tr>
            </table>
            <table>
                <tr>
                    <td style="width:100%;"><p style="font-size:13px;text-align:center;">
                    <span style="text-align:center;margin-top:5%;font-size:18px;font-weight:bold;">TRÜNORTH GLOBAL<sup style="font-weight:normal;">TM</sup> AUTHORIZED RETAILER AGREEMENT</span><br>
                        <span style="font-size:13px;">This Agreement is entered into this date: __' . $agreeDate . '__	, between TrüNorth Global Corporation™, located </span>
                        <br><span style="font-size:13px;">at 16740 Birkdale Commons Parkway, Suite 208, Huntersville, North Carolina, 28078, , referred to as “TrüNorth</span><br>
                        <span style="font-size:13px;">Global™”, and the entity identified in the box below referred to as “Retailer.”</span>
                    </p></td>
                </tr>
            </table><br/><br/>
            <table cellspacing="0" cellpadding="5" border="1" style="border-color:grey;">
                <tr>
                    <td style="width:100%;">
                        <table border="0">
                            <tr>
                               <td class="fontClass" style="width:70%;padding-left:40px;">Dealer Name: ' . $dealerName . '</td>
                               <td class="fontClass" style="width:30%;padding-left:40px;">Agreement Date: ' . $agreeDate . '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width:100%;">
                        <table border="0">
                            <tr>
                                <td class="fontClass" style="width:70%;">Dealership Trading As: ' . $dba . '</td>
                                <td class="fontClass" style="width:30%;"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width:100%;">
                        <table border="0">
                            <tr>
                                <td class="fontClass" style="width:100%;">Dealer Owner / Principal or Representative: ' . $primaryContact . '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width:100%;">
                        <table border="0">
                            <tr>
                                <td class="fontClass" style="width:70%;">Address: ' . $dealerAddress1 . '</td>
                                <td class="fontClass" style="width:30%;">PO Box/Suite: ' . $dealerAddress2 . '</td>
                            </tr>

                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width:100%;">
                        <table border="0">
                            <tr>
                                <td class="fontClass" style="width:40%;margin-left:-15px;">City: ' . $dealerCity . '</td>
                                <td class="fontClass" style="width:30%;border-left:none;">State/Province: ' . $dealerState . '</td>
                                <td class="fontClass" style="width:30%;border-left:none;">Zip/Postal Code: ' . $dealerZip . '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <br/><br/>
            <br/>

            <table cellspacing="0" cellpadding="5" border="1" style="border-color:gray;">
                <tr>
                    <td class="fontClass" style="width:100%;">
                        Dealer Owner/Principal or authorized representative hereby authorizes Vital Trends USA, LLC.,
                        205 Arnold Rd. Burlington Flats, NY 13315, and its representatives to offer training and incentives (financial and other) 
                        to the dealer and dealer personnel as necessary to increase and maintain penetration and performance with regard to all products 
                        and/or systems offered and maintained by Vital Trends USA, LLC. as they deem necessary.
                    </td>
                </tr>
            </table>

            <br/><br/><br/>
            <table cellspacing="0" cellpadding="5" border="1" style="border-color:gray;">
                <tr>
                    <td style="width:100%;">
                        <table border="0">
                            <tr>
                              <td class="fontClass">Retailer Signature: <img src="uploads/' . $fileName . '" style="width:90px;height:60px;" /></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width:100%;">
                        <table border="0">
                            <tr>
                                <td class="fontClass" style="width:60%;margin-left:-15px;">Retailer Name: ' . $dealerName . '</td>
                                <td class="fontClass" style="width:40%;border-left:none;">Date: ' . $assignDate . '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <br/><br/><br/>

            <table cellspacing="0" cellpadding="5" border="1" style="border-color:gray;width:100%;">
                <tr>
                    <td style="width:100%;">
                        <table border="0">
                            <tr>
                              <td class="fontClass">To Be Completed by TrüNorth Global™</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width:100%;">
                        <table border="0">
                            <tr>
                                <td class="fontClass" style="width:70%;">Assigned Retailer #:</td>
                                <td class="fontClass" style="width:30%;">Date: ' . $assignDate . '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="width:100%;">
                        <table border="0">
                            <tr>
                              <td class="fontClass" style="width:100%;">Assigned Program(s)</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <br/><br/><br/>

            <table>
                <tr>
                    <td class="fontClass" style="width:70%;">TrüNorth Global™ Signature:__________________________________________________________</td>
                    <td class="fontClass" style="width:30%;">Date:' . $agreeDate . '__________________</td>
                </tr>
            </table>
        ';

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        $pdfFileName = str_replace(" ", "_", $dealerName) . '_' . str_replace(" ", "_", $dba) . '_' . time() . '.pdf';

        $pdf->Output(__DIR__ . '/uploads/dealer_addendum_pdf/' . $pdfFileName, 'F');

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
        //  Set type=1 for 'dealer agreement'.
        $stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
                                    Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,3,'Dealer Addendum',NOW())");

        /* Bind variables to parameters */
        $val1 = $dealerID;
        $val2 = $primary_Contact_Person_id;
        $val3 = $adminID;
        $val4 = $contract_dim_ID;
        $val5 = '/uploads/dealer_addendum_pdf/' . $pdfFileName;

        mysqli_stmt_bind_param($stmt, "iiiis", $val1, $val2, $val3, $val4, $val5);

        /* Execute the statement */
        $result = mysqli_stmt_execute($stmt);

        // End PDF Code here
        // // API Call to TruNorth
        //     $url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/retailers/create-retailer?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";

        //     $curl = curl_init($url);
        //     curl_setopt($curl, CURLOPT_URL, $url);
        //     curl_setopt($curl, CURLOPT_POST, true);
        //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        //     $headers = array(
        //         "Accept: application/json",
        //         "Content-Type: application/json",
        //     );
        //     curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        //     if (file_exists('uploads/dealer_addendum_pdf/' . $pdfFileName)) {
        //         $b64PDFDoc = base64_encode(file_get_contents('uploads/dealer_addendum_pdf/' . $pdfFileName));
        //     } else {
        //         $b64PDFDoc = base64_encode(file_get_contents("files/TEST_AGREEMENT_PDF.pdf"));
        //     }

        //     // get business email
        //     // Select * from Email where acct_id = 1306 AND Pers_ID=425 AND Email_Prim_Flg = 'Y';

        //     // Select Email for the dealer.
        //     $emailResult = selectEmailByAcct($link, $dealerID, "Y", "Work");
        //     if ($emailResult) {
        //         $row = $emailResult->fetch_assoc();
        //         $dealerEmail = $row["Email_URL_Desc"];
        //     } else {
        //         $dealerEmail = "";
        //     }
        //     // Select Tel for the dealer.
        //     $telResult = selectTelByAcct($link, $dealerID, "Y", "Work");
        //     if ($telResult) {
        //         $row = $telResult->fetch_assoc();
        //         $dealerPhone = $row["Tel_Nbr"];
        //     } else {
        //         $dealerPhone = "";
        //     }


        //     $data = "{
        //     \"retailerName\": \"$dealerName\",
        //     \"retailerEmail\": \"$dealerEmail\",
        //     \"retailerPhone\": \"$dealerPhone\",
        //     \"retailerAddress\": {
        //     \"street\": \"$dealerAddress1\",
        //     \"street2\": \"$dealerAddress2\",
        //     \"city\": \"$dealerCity\",
        //     \"state\": \"$dealerState\",
        //     \"zip\": \"$dealerZip\",
        //     \"country\": \"US\"
        //     },
        //     \"defaultCurrency\": \"USD\",
        //     \"validationMethod\": \"ECA Only\",
        //     \"files\" : [{\"type\" : \"vtAgreement\", \"fileBytes\" : \"$b64PDFDoc\"}]
        //     }";

        //     curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        //     $resp = curl_exec($curl);
        //     curl_close($curl);
        //     //var_dump($resp);

        //     $json = json_decode($resp, true);
        //     // print_r($json);

        //     if (isset($json) && array_key_exists("success", $json)) {
        //         $responseStatus = $json["success"];
        //     } else {
        //         $responseStatus = 0;
        //     }

        //     if ($responseStatus == 1) {
        //         $arNumber = $json["data"]["arNumber"];
        //         $apiMessage = $json["message"];

        //         // Save the returned retailer number to the CNTRCT_DIM table.
        //         $stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim SET Assign_Rtlr_Nbr=? WHERE Cntrct_Dim_ID=?");

        //         /* Bind variables to parameters */
        //         $val1 = $arNumber;
        //         $val2 = $contract_dim_ID;

        //         mysqli_stmt_bind_param($stmt, "si", $val1, $val2);

        //         /* Execute the statement */
        //         $result = mysqli_stmt_execute($stmt);

        //     } else {
        //         $arNumber = "FAILED";
        //         $apiMessage = "NONE";
        //         $responseStatus = 0;
        //     }


        //     // Create a new API_Data entry to track activity
        //     $stmt = mysqli_prepare($link, "INSERT INTO API_Responses (Acct_ID,statusCode, dataReturned, arNumber, messageText, sentJSON, returnedJSON, createdDate) VALUES (?,?,?,?,?,?,?,NOW())");

        //     /* Bind variables to parameters */
        //     $val1 = $dealerID;
        //     $val2 = $responseStatus;
        //     $val3 = $arNumber;
        //     $val4 = $arNumber;
        //     $val5 = $apiMessage;
        //     $val6 = $data;
        //     $val7 = $resp;

        //     mysqli_stmt_bind_param($stmt, "issssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7);

        //     /* Execute the statement */
        //     $result = mysqli_stmt_execute($stmt);


        // // End API section
    }
    // Redirect to next form
    header("location: dealer_affiliate_fee.php");
    exit;

    die();
} else {

    // Get the dealer address info
    $query = "SELECT * FROM Addr WHERE Acct_ID=" . $dealerID . " AND Addr_Type_Cd='Work';";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $dealerAddress1 = $row["St_Addr_1_Desc"];
    $dealerAddress2 = $row["St_Addr_2_Desc"];
    $dealerCity = $row["City_Nm"];
    $dealerState = $row["St_Prov_ID"];
    $dealerZip = $row["Pstl_Cd"];

    // Look up the state name
    if ($dealerState > 0) {
        $query = "SELECT * FROM St_Prov WHERE St_Prov_ID=" . $dealerState;
        $result = $link->query($query);
        $row = $result->fetch_assoc();

        $dealerStateName = $row["St_Prov_ISO_2_Cd"];
    } else {
        $dealerStateName = "None Found";
    }

    // Get the dealer info
    $query = "SELECT * FROM Acct WHERE Acct_ID=" . $dealerID;
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $dealerName = $row["Acct_Nm"];

    // Get the dealer dba info
    $query = "SELECT * FROM Altn_Nm WHERE Acct_ID=" . $dealerID;
    $result = $link->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $dba = $row["Altn_Nm"];
    }


    // Get primary contact info
    $query = "SELECT * FROM Pers WHERE Cntct_Prsn_For_Acct_Flg='Y' AND Acct_ID=" . $dealerID;
    $result = $link->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        $primaryContact = $row["Pers_Full_Nm"];
    }


    // Get the contract info
    $query = "SELECT cd.Contract_Date FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
    $result = $link->query($query);
    $row = $result->fetch_assoc();

    $agreementDate = $row["Contract_Date"];
}



require_once("includes/header.php");

?>

<!--**********************************
            Content body start
        ***********************************-->
<div class="content-body">
    <!-- row -->
    <div class="container-fluid">
        <?php require_once("includes/common_page_content.php"); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h4 class="card-title">Dealer Addendum</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form dealer-form">
                            <div class="watermark">
                                <img src="images/logo_large_bg.png" alt="">
                            </div>
                            <form name="dealerForm" id="dealer_addendum_form" method="POST" action="" enctype='multipart/form-data'>
                                <div class="form-row row">
                                    <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">Dealer Name</h5>
                                        <h4 class="text-muted mb-0"><?php echo $dealerName; ?></h4>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">Agreement Date</h5>
                                        <h4 class="text-muted mb-0"><?php echo $agreementDate; ?></h4>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">Dealership Trading As</h5>
                                        <h4 class="text-muted mb-0"><?php echo $dba; ?></h4>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <h5 class="text-primary d-inline">Dealer Owner / Principal or Representative</h5>
                                        <h4 class="text-muted mb-0"><?php echo $primaryContact; ?></h4>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <h5 class="text-primary d-inline">Dealership Address</h5>
                                        <h4 class="text-muted mb-0"><?php echo $dealerAddress1; ?> <?php echo $dealerCity . ", " . $dealerStateName . ". " . $dealerZip; ?></h4>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <hr />
                                    </div>
                                    <div class="form-group col-md-12 terms-text">
                                        <ul>
                                            <li>
                                                Dealer Owner/Principal or authorized representative hereby authorizes Vital Trends USA, LLC., 205
                                                Arnold Rd. Burlington Flats, NY 13315, and its representatives to offer training and incentives (financial
                                                and other) to the dealer and dealer personnel as necessary to increase and maintain penetration and
                                                performance with regard to all products and/or systems offered and maintained by Vital Trends USA,
                                                LLC. as they deem necessary.
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <hr />
                                    </div>

                                    <!-- <div class="form-group col-md-12 row">
                                        <div class="form-group col-md-6">
                                            <h5 class="font-weight-normal">Sign here</h5>
                                            <div class="signature"></div>
                                            <span style="color: red;display: none;" id="signatureE">Please Enter Signature Data..!</span>
                                        </div>
                                    </div> -->

                                    <div class="form-group col-md-12 row">
                                        <div class="form-group col-md-12">
                                            <label class="radio-inline mr-3">
                                                <input type="radio" name="signature_type" value="signature1" checked>
                                                Sign here
                                            </label>
                                            <label class="radio-inline mr-3">
                                                <input type="radio" name="signature_type" value="signature2">
                                                or Upload Signature
                                            </label>
                                        </div>
                                        <div class="form-group col-md-12 signature1Content signatureContent">
                                            <div class="signature"></div>
                                        </div>
                                        <div class="form-group col-md-12 signature2Content signatureContent" style="display: none;">
                                            <input type="file" class="form-control" id="signatureFile" name="signatureFile" />
                                        </div>
                                        <span style="color: red;display: none;" id="signatureE">Please Enter Signature Data..!</span>
                                        <span style="color: red;display: none;" id="signatureEType">Error: Only JPEG and PNG files are allowed.!</span>
                                    </div>
                                </div>
                                <button type="button" id="dealer_addendum_submit" class="btn btn-primary">Submit</button>
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

</div>
<!--**********************************
            Content body end
        ***********************************-->

<?php

require_once("includes/footer.php");

?>

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

<!-- Chart piety plugin files -->
<script src="./vendor/peity/jquery.peity.min.js"></script>

<!-- Dashboard 1 -->
<script src="./js/dashboard/dashboard-1.js"></script>
<script src="./js/custom.min.js"></script>
<script src="./js/deznav-init.js"></script>
<script src="./js/jSignature/jSignature.min.js"></script>
<script src="./js/jSignature/jSignInit.js"></script>
<script src="./js/custom-validation.js"></script>
<script src="js/demo.js"></script>
<script>
    jQuery('input').on('keypress', function(e) {
        if (e.which === 13) {
            var flag1 = 0;

            if ($(".jSignature").jSignature("getData", "native").length == 0 && !$('#signatureFile')[0].files[0]) {
                $("#signatureE").css("display", "block");
            } else {
                $("#signatureE").css("display", "none");
                flag1 = 1;
            }
        }
    });
</script>
<script>
    function carouselReview() {
        /*  testimonial one function by = owl.carousel.js */
        function checkDirection() {
            var htmlClassName = document.getElementsByTagName('html')[0].getAttribute('class');
            if (htmlClassName == 'rtl') {
                return true;
            } else {
                return false;

            }
        }

        jQuery('.testimonial-one').owlCarousel({
            loop: true,
            autoplay: true,
            margin: 30,
            nav: false,
            dots: false,
            rtl: checkDirection(),
            left: true,
            navText: ['', ''],
            responsive: {
                0: {
                    items: 1
                },
                1200: {
                    items: 2
                },
                1600: {
                    items: 3
                }
            }
        })
    }
    jQuery(window).on('load', function() {
        setTimeout(function() {
            carouselReview();
        }, 1000);
    });
</script>
<script>
    function printpart() {
        var printwin = window.open("");
        printwin.document.write(document.getElementById("toprint").innerHTML);
        printwin.stop();
        printwin.print();
        printwin.close();
    }
</script>

<script>
    //sketch lib
    (function() {
        var __slice = [].slice;

        (function($) {
            var Sketch;
            $.fn.sketch = function() {
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
            Sketch = (function() {

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
                        $('body').delegate("a[href=\"#" + (this.canvas.attr('id')) + "\"]", 'click', function(e) {
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

                Sketch.prototype.download = function(format) {
                    var mime;
                    format || (format = "png");
                    if (format === "jpg") {
                        format = "jpeg";
                    }
                    mime = "image/" + format;
                    return window.open(this.el.toDataURL(mime));
                };

                Sketch.prototype.set = function(key, value) {
                    this[key] = value;
                    return this.canvas.trigger("sketch.change" + key, value);
                };

                Sketch.prototype.startPainting = function() {
                    this.painting = true;
                    return this.action = {
                        tool: this.tool,
                        color: this.color,
                        size: parseFloat(this.size),
                        events: []
                    };
                };


                Sketch.prototype.stopPainting = function() {
                    if (this.action) {
                        this.actions.push(this.action);
                    }
                    this.painting = false;
                    this.action = null;
                    return this.redraw();
                };

                Sketch.prototype.onEvent = function(e) {
                    if (e.originalEvent && e.originalEvent.targetTouches) {
                        e.pageX = e.originalEvent.targetTouches[0].pageX;
                        e.pageY = e.originalEvent.targetTouches[0].pageY;
                    }
                    $.sketch.tools[$(this).data('sketch').tool].onEvent.call($(this).data('sketch'), e);
                    e.preventDefault();
                    return false;
                };

                Sketch.prototype.redraw = function() {
                    var sketch;
                    //this.el.width = this.canvas.width();
                    this.context = this.el.getContext('2d');
                    sketch = this;
                    $.each(this.actions, function() {
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
                onEvent: function(e) {
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
                draw: function(action) {
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
                onEvent: function(e) {
                    return $.sketch.tools.marker.onEvent.call(this, e);
                },
                draw: function(action) {
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


    (function($) {
        $.fn.SignaturePad = function(options) {

            //update the settings
            var settings = $.extend({
                allowToSign: true,
                img64: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
                border: '1px solid #c7c8c9',
                width: '300px',
                height: '150px',
                callback: function() {
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
                big_pad.css('width', '60%');
                big_pad.css('height', '40%');
                big_pad.css('z-index', '999999999');
                big_pad.css('border-radius', '10px');
                big_pad.attr('id', 'signPadBig');
                $('body').append(big_pad);

                var update_canvas_size = function() {
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
                //canvas.css('width', '90%');
                canvas.css('height', 'auto');
                big_pad.append(canvas);

                update_canvas_size();
                $(window).on('resize', function() {
                    update_canvas_size();
                });

                var clearCanvas = function() {
                    canvas.sketch().action = null;
                    canvas.sketch().actions = []; // this line empties the actions.
                    var ctx = canvas[0].getContext("2d");
                    ctx.clearRect(0, 0, canvas[0].width, canvas[0].height);
                    return true
                }

                var _get_base64_value = function() {
                    var text_control = $.data(big_pad[0], 'control'); //settings.control; // $('#' + big_pad.attr('id'));
                    return $(text_control).val();
                }

                var copyCanvas = function() {
                    //get data from bigger pad
                    var sigData = canvas[0].toDataURL("image/png");

                    var _img = new Image;
                    _img.onload = resizeImage;
                    _img.src = sigData;

                    var targetWidth = canvas.width();
                    var targetHeight = canvas.height();

                    function resizeImage() {
                        var imageToDataUri = function(img, width, height) {

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

                        var text_control = $.data(big_pad[0], 'control'); //settings.control; // $('#' + big_pad.attr('id'));
                        if (text_control)
                            $(text_control).val(newDataUri);
                    }
                }

                var buttons = [{
                        title: 'Close',
                        callback: function() {
                            clearCanvas();
                            big_pad.slideToggle(function() {
                                back_drop.hide('fade');
                            });

                        }
                    },
                    {
                        title: 'Clear',
                        callback: function() {
                            clearCanvas();
                            if (settings.callback)
                                settings.callback(_get_base64_value(), 'clear');
                        }
                    },
                    {
                        title: 'Accept',
                        callback: function() {
                            copyCanvas();
                            clearCanvas();
                            big_pad.slideToggle(function() {
                                back_drop.hide('fade', function() {
                                    if (settings.callback)
                                        settings.callback(_get_base64_value(), 'accept');
                                });
                            });
                        }
                    }
                ].forEach(function(e) {
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
                    btn.on('click', function() {
                        e.callback(e.title);
                    })
                    big_pad.append(btn);

                });

            } else {
                canvas = big_pad.find('canvas')[0];
            }

            //init the signpad
            if (canvas) {
                var sign1big = $(canvas).sketch({
                    defaultColor: "#000",
                    defaultSize: 5
                });
            }

            //for each control
            return this.each(function() {

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

                if (typeof(wrapper) == 'object') {
                    wrapper.append(img);
                }




                //init the big sign pad
                if (settings.allowToSign == true) {
                    //click to the pad bigger
                    img.on('click', function() {
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

    $(document).ready(function() {
        var sign = $('#txt').SignaturePad({
            allowToSign: true,
            img64: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            border: '1px solid #c7c8c9',
            width: '300px',
            height: '150px',
            callback: function(data, action) {
                console.log(data);
            }
        });
    })
</script>
<script>
    $(document).ready(function() {
        $(".moveToW9").click(function() {
            window.location.href = 'dealer_w9.php';
        });
    });
</script>
</body>

</html>