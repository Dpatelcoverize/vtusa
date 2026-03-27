<?php
//

// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";
include 'PDFMerger/PDFMerger.php';

// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');

//
require_once 'vendor/autoload.php';

use Classes\GeneratePDF;


// Variables
$hasWarranty = "N";
$hasInkSignedWarranty = "N";
$hasSmallGoodsSummary = "N";
$hasSmallGoodsDetail = "N";
$hasWarrantyAddendum = "N";
$hasECM = "N";
$hasMaintenance = "N";
$hasInspection = "N";
$hasECM = "N";
$hasPhotos = "N";
$hasVINPhoto = "N";
$hasOdometerPhoto = "N";
$hasEnginePhoto = "N";
$remainingDocs = [];

if($_GET["isQuote"]=="Y"){

     $fileTypeID = 6;
}else{

     $fileTypeID = 7;
}

$warrantyID = $_GET["warrantyID"];

$sql = "SELECT * FROM Cntrct c, Veh v , Cntrct_Dim cd WHERE c.Cntrct_ID=" . $warrantyID . " AND
				c.Veh_ID = v.Veh_ID AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID ORDER BY c.Cntrct_ID DESC";

		$result = $link->query($sql);
		$row = $result->fetch_assoc();

		$isVhicleNew = $row["Veh_New_Flg"];
		$odometerReadingKM = $row["OdoMtr_Read_Kms_Cnt"];
		$odometerReadingMiles = $row["OdoMtr_Read_Miles_Cnt"];
		$warranty_type = $row["Veh_Gross_Wgt_Cnt"];
		$SmallGoods = $row["Small_Goods_Pkg_Flg"];


// Warranty PDF
$warrantyPDF = substr(getFileAssetForWarranty($link,$warrantyID,$fileTypeID), 1);
if($warrantyPDF){
	$hasWarranty = "Y";
}



// //Ink Signed Warranty PDF
// if($_GET["isQuote"]=="Y"){
// 	$inkSignedWarranty = substr(getFileAssetForWarranty($link,$warrantyID,16), 1);
// }else{
// 	$inkSignedWarranty = substr(getFileAssetForWarranty($link,$warrantyID,17),1);
// }

// if($inkSignedWarranty)
// {
// 	$hasInkSignedWarranty = "Y";
// }

if($SmallGoods == "Y")
{
	//Small Goods PDF
	$query = "SELECT Path_to_File FROM File_Assets WHERE Warranty_Cntrct_ID=" .$warrantyID." AND File_Asset_Type_ID = 13 ORDER BY File_Asset_ID DESC";
	$result = $link->query($query);
	$smallGoodsSummary = mysqli_fetch_assoc($result);
	if($smallGoodsSummary){
		$hasSmallGoodsSummary = "Y";
		$smallGoodsPDF = substr($smallGoodsSummary["Path_to_File"], 1);
	}else{
		$smallGoodsPDF = "";
	}


	//Small Goods Detail
	$query = "SELECT Path_to_File FROM File_Assets WHERE Warranty_Cntrct_ID=" .$warrantyID." AND File_Asset_Type_ID = 18 ORDER BY File_Asset_ID DESC";
	$result = $link->query($query);
	$smallGoodsDetail = mysqli_fetch_assoc($result);
	if($smallGoodsDetail){
		$hasSmallGoodsDetail = "Y";
		$smallGoodsDetailPDF = substr($smallGoodsDetail["Path_to_File"], 1);

	}else{
		$smallGoodsDetailPDF = "";
	}
}


//Warranty Addendum
$query = "SELECT Path_to_File FROM File_Assets WHERE Warranty_Cntrct_ID=" .$warrantyID." AND File_Asset_Type_ID = 21 ORDER BY File_Asset_ID DESC";
$result = $link->query($query);
$warrantyAddendumResult = mysqli_fetch_assoc($result);
if($warrantyAddendumResult){
	$hasWarrantyAddendum = "Y";
	$warrantyAddendumPDF = substr($warrantyAddendumResult["Path_to_File"], 1);
}else{
	$warrantyAddendumPDF = "";
}


//Maintenance & Wear Form
$maintenancePDF = substr(getFileAssetForWarranty($link,$warrantyID,15), 1);
if($maintenancePDF)
{
	$hasMaintenance = "Y";
}


//Inspection Form
$inspectionForm = substr(getFileAssetForWarranty($link,$warrantyID,8), 1);
if($inspectionForm)
{
	$hasInspection = "Y";
}


//ECM Fault Report
$ECM = substr(getFileAssetForWarranty($link,$warrantyID,20), 1);
if($ECM){

    $hasECM = "Y";
}


//VIN Placard Photo
$VINPhoto = substr(getFileAssetForWarranty($link,$warrantyID,10), 1);
if($VINPhoto){

	$hasVINPhoto = "Y";
}


//Dashboard Odometer Photo
$odometerPhoto = substr(getFileAssetForWarranty($link,$warrantyID, 11), 1);
if($odometerPhoto)
{
    $hasOdometerPhoto = "Y";
}


//Engine Placard Photo
$enginePhoto = substr(getFileAssetForWarranty($link,$warrantyID, 12), 1);
if($enginePhoto)
{
    $hasEnginePhoto = "Y";
}


// Get small goods detail associated with this warrantyID
$query  = "SELECT * FROM Sml_Goods_Cvge sgc, Sml_Goods_Gnrc_Prcg sggp WHERE sgc.Cntrct_ID=".$warrantyID." AND ";
$query .= "sgc.Sml_Goods_Gnrc_Prcg_ID=sggp.Sml_Goods_Gnrc_Prcg_ID AND sgc.Is_Deleted_Flg!='Y'";
$smallGoodsResult = $link->query($query);


$recieptPath = [];
$SmallgoodsRemainingFiles = [];
if (mysqli_num_rows($smallGoodsResult) > 0) {
	// output data of each row
	$loopCounter = 0;
	while($row = mysqli_fetch_assoc($smallGoodsResult)) {
	  $loopCounter++;
	  $countOfSmallGoods++;
	  $purchaseDate = $row["Mfrd_Yr_Nbr"];
	  $make = $row["Mk_Nbr"];
	  $model = $row["Model_Nbr"];
	  $serial = $row["Ser_nbr"];

	  if($row["sml_goods_rcpt_flg"]=="Y"){
		  $filePath = substr(getFileAssetForSmallGood($link,$row["Sml_Goods_Cvge_ID"],14), 1);
		  array_push($recieptPath, $filePath );
		  $countWithReceipts++;
	  }else{
		array_push($SmallgoodsRemainingFiles,"Receipt for Small Good “".$row["Item_Cat_Type_Desc"]."” not yet uploaded");

		if($purchaseDate == ""){
	    array_push($SmallgoodsRemainingFiles,"Purchase date for Small Good “".$row["Item_Cat_Type_Desc"]."” is required");
		}
		if($make == ""){
			array_push($SmallgoodsRemainingFiles,"Make number for Small Good “".$row["Item_Cat_Type_Desc"]."” is required");
		}
		if($make == ""){
			array_push($SmallgoodsRemainingFiles,"Model number for Small Good “".$row["Item_Cat_Type_Desc"]."” is required");
		}
		if($make == ""){
			array_push($SmallgoodsRemainingFiles,"Serial number for Small Good “".$row["Item_Cat_Type_Desc"]."” is required");
		}
		array_push($SmallgoodsRemainingFiles,"<br>");
	  }
	}
}



//Merge all PDFs
$pdf = new \PDFMerger\PDFMerger;



if($hasWarranty=="Y"){
	$pdfFile1 =  strval($warrantyPDF);
	$pdf->addPDF($pdfFile1 , 'all', 'P');
	
}



if($hasInkSignedWarranty == "Y"){
	$pdfFile11 =  strval($inkSignedWarranty);
	$pdf->addPDF($pdfFile11 , 'all', 'P');
	
}



// if($hasSmallGoodsSummary == "Y"){
// 	$pdfFile2 =  strval($smallGoodsPDF);
// 	$pdf->addPDF($pdfFile2 , 'all', 'P');
	
// }


// if($hasSmallGoodsDetail=="Y"){
// 	$pdfFile3 =  strval($smallGoodsDetailPDF);
// 	$pdf->addPDF($pdfFile3 , 'all', 'P');
// }



if($hasWarrantyAddendum=="Y"){
	$pdfFile4 =  strval($warrantyAddendumPDF);
	$pdf->addPDF($pdfFile4 , 'all', 'P');
	
}
else
{
	array_push($remainingDocs,"Warranty Addendum not yet uploaded");
}




//if vehicle new
if($isVhicleNew == "Y")
{
	if($hasMaintenance == "Y")
	{
		$pdfFile5 =  strval($maintenancePDF);

		$pdf->addPDF($pdfFile5 , 'all', 'P');
	}
	else
	{
		$pdf->addPDF('uploads/fillable_documents/VT TNG EMV MWR v0622 F Fillable.pdf' , 'all', 'P');
		array_push($remainingDocs,"Maintenance & Wear Form not yet uploaded");
	}
}



//if vehicle used
if($isVhicleNew == "N")
{

	// Vehicle Type 1
	if($warranty_type == "type 1")
	{

	

		//Maintenece Form
        if($hasMaintenance == "Y")
		{
			$pdfFile5 =  strval($maintenancePDF);
			$pdf->addPDF($pdfFile5 , 'all', 'P');

		}
		else
		{
			$pdf->addPDF('uploads/fillable_documents/VT TNG EMV MWR v0622 F Fillable.pdf' , 'all', 'P');
			array_push($remainingDocs,"Maintenance & Wear Form not yet uploaded");
		}


		//Inspection Form
		if($hasInspection == "Y")
		{


			$pdfFile6 =  strval($inspectionForm);  //uploads/warranty_pdf/RegularPDF/7ad1e99inspection_blank.pdf
			$filenameExt = basename($pdfFile6);  //7ad1e99inspection_blank.pdf

			$RegularPDF = new GeneratePDF;
			$emptyArray = [];
			$pdfFile6 = $RegularPDF->convertToRegularPDF($emptyArray,$pdfFile6,$filenameExt); //method returning the path of regular generated pdf
			
			$pdf->addPDF($pdfFile6 , 'all', 'P');
		
		}
		else
		{
			$pdf->addPDF('uploads/fillable_documents/Inspection_Form.pdf' , 'all', 'P');
			array_push($remainingDocs,"Inspection Form not yet uploaded");


		}

	

		//ECM Fault Report
		if($hasECM == "Y")
		{
			$pdfFile7 =  strval($ECM);
			$pdf->addPDF($pdfFile7 , 'all', 'P');
		}
		else
		{
			$pdf->addPDF('uploads/fillable_documents/ECA.pdf' , 'all', 'P');
			array_push($remainingDocs,"ECM Fault Report not yet uploaded");
		}

		

	}




	// Vehicle Type 2
	else if($warranty_type == "type 2")
	{


		if($hasMaintenance == "Y")
		{
			$pdfFile5 =  strval($maintenancePDF);
			$pdf->addPDF($pdfFile5 , 'all', 'P');
		}
		else
		{
			array_push($remainingDocs,"Maintenance & Wear Form not yet uploaded");
		}

		//Inspection Form
		if($hasInspection == "Y")
		{
			$pdfFile6 =  strval($inspectionForm);  //uploads/warranty_pdf/RegularPDF/7ad1e99inspection_blank.pdf
			$filenameExt = basename($pdfFile6);  //7ad1e99inspection_blank.pdf

			$RegularPDF = new GeneratePDF;
			$emptyArray = [];
			$pdfFile6 = $RegularPDF->convertToRegularPDF($emptyArray,$pdfFile6,$filenameExt); //method returning the path of regular generated pdf.
			$pdf->addPDF($pdfFile6 , 'all', 'P');
		
		}
		else
		{
			$pdf->addPDF('uploads/fillable_documents/Inspection_Form.pdf' , 'all', 'P');
			array_push($remainingDocs,"Inspection Form not yet uploaded");
		}

		//ECM Fault Report
		if($hasECM == "Y")
		{
			$pdfFile7 =  strval($ECM);
			$pdf->addPDF($pdfFile7 , 'all', 'P');

		}
		else
		{
			$pdf->addPDF('uploads/fillable_documents/ECA.pdf' , 'all', 'P');
			array_push($remainingDocs,"ECM Fault Report not yet uploaded");
		}


	}
	

	
	// Vehicle Type 3
	else if($warranty_type == "type 3")
	{


			if($hasMaintenance == "Y")
		{
			$pdfFile5 =  strval($maintenancePDF);
			$pdf->addPDF($pdfFile5 , 'all', 'P');
		}
		else
		{
			array_push($remainingDocs, "Maintenance & Wear Form not yet uploaded");
		}

		//Inspection Form
		if($hasInspection == "Y" && file_exists($inspectionForm))
		{

			$pdfFile6 =  strval($inspectionForm);  //uploads/warranty_pdf/RegularPDF/7ad1e99inspection_blank.pdf
			$filenameExt = basename($pdfFile6);  //7ad1e99inspection_blank.pdf

			$RegularPDF = new GeneratePDF;
			$emptyArray = [];
			$pdfFile6 = $RegularPDF->convertToRegularPDF($emptyArray,$pdfFile6,$filenameExt); //method returning the path of regular generated pdf
			$pdf->addPDF($pdfFile6 , 'all', 'P');
		}
		
		else
		{
			$pdf->addPDF('uploads/fillable_documents/Inspection_Form.pdf' , 'all', 'P');
			array_push($remainingDocs,"Inspection Form not yet uploaded");
		}




		// //VIN Placard Photo
		// if($hasVINPhoto == "Y")
		// {
		// 	$pdfFile7 =  strval($VINPhoto);
		// 	$pdf->addPDF($pdfFile7 , 'all', 'P');
		// }
		// else
		// {
		// 	array_push($remainingDocs,"VIN Placard Photo not yet uploaded");
		// }


		// //Dashboard Odometer Photo
		// if($hasOdometerPhoto == "Y")
		// {
		// 	$pdfFile8 =  strval($odometerPhoto);
		// 	$pdf->addPDF($pdfFile8 , 'all', 'P');
		// }
		// else
		// {
		// 	array_push($remainingDocs,"Dashboard Odometer Photo not yet uploaded");
		// }


		// //Engine Placard Photo
		// if($hasEnginePhoto == "Y")
		// {
		// 	$pdfFile9 =  strval($enginePhoto);
		// 	$pdf->addPDF($pdfFile9 , 'all', 'P');
		// }
		// else
		// {
		// 	array_push($remainingDocs,"Engine Placard Photo not yet uploaded");
		// }
		//VIN Placard Photo



		if($hasVINPhoto == "N")
		{
			array_push($remainingDocs,"VIN Placard Photo not yet uploaded");
		}

		//Dashboard Odometer Photo
		if($hasOdometerPhoto == "N")
		{
			array_push($remainingDocs,"Dashboard Odometer Photo not yet uploaded");
		}

		//Engine Placard Photo
		if($hasEnginePhoto == "N")
		{
			array_push($remainingDocs,"Engine Placard Photo not yet uploaded");
		}
	}
}



// if($recieptPath != [])
// {
// 	 $counter = 10;
//      foreach($recieptPath as $reciept)
// 	 {
// 		    $pdfFile.$counter =  strval($reciept);
// 			$pdf->addPDF($pdfFile.$counter , 'all', 'P');
// 			$counter++;
// 	 }
// }


if($remainingDocs != [] || $SmallgoodsRemainingFiles != [])
{

	if($remainingDocs != [])
	{
		$remianigDocsHTML .= "<h4>Forms & Photos Not Yet Uploaded:</h4>";
	}
	//Forms & Photos
	$remianigDocsHTML .= "<ul>";
	foreach($remainingDocs as $doc)
	{
		$remianigDocsHTML .= "<li class='fontClass'>".$doc."</li>";
	}

	$remianigDocsHTML .= "</ul>";

	//Small Goods Information & Receipts
	if($SmallgoodsRemainingFiles != [])
	{
		$remianigSmallGoodsHTML .= "<h4>Small Goods Information & Receipts:</h4>";
	}
	$remianigSmallGoodsHTML .= "<ul>";
	foreach($SmallgoodsRemainingFiles as $doc)
	{
		$remianigSmallGoodsHTML .= "<li class='fontClass'>".$doc."</li>";
	}

	$remianigSmallGoodsHTML .= "</ul>";


	$html .= "<h4>Items Still Needed:</h4>";
	$html .= $remianigDocsHTML;
	$html .= $remianigSmallGoodsHTML;


	$newPdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
	// set document information
	$newPdf->setCreator(PDF_CREATOR);
	$newPdf->setAuthor('Vital Trends');
	$newPdf->setTitle('Vital Trends');
	$newPdf->setSubject('Vital Trends');
	$newPdf->setKeywords('Remaining-Docs, Vital, Data, Set, Guide, Quote');
	
	// set default header data
	//$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$newPdf->setPrintHeader(false);
	//$pdf->setFooterData(array(0,64,0), array(0,64,128));
	
	// set header and footer fonts
	$newPdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$newPdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	
	// set default monospaced font
	
	$newPdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	// set margins
	
	$newPdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP - 15, PDF_MARGIN_RIGHT);
	$newPdf->setHeaderMargin(PDF_MARGIN_HEADER);
	$newPdf->setFooterMargin(PDF_MARGIN_FOOTER);
	// set auto page breaks
	
	$newPdf->setAutoPageBreak(true, PDF_MARGIN_BOTTOM);
	
	// set image scale factor
	$newPdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	
	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
	require_once(dirname(__FILE__) . '/lang/eng.php');
		$newPdf->setLanguageArray($l);
	}
		// ---------------------------------------------------------
	
	// set default font subsetting mode
	$newPdf->setFontSubsetting(true);
	
	
	// Set font
	$cambriabF = TCPDF_FONTS::addTTFfont('tcpdf/fonts/cambria/Cambria Math.ttf', 'TrueTypeUnicode', '', 32);
	$newPdf->setFont($cambriabF, '', 14, '', true);
	// Add a page
	// This method has several options, check the source code documentation for more information.
	$newPdf->AddPage();
	
	
	$newPdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
	
	// ---------------------------------------------------------
	
	// Close and output PDF document
	// This method has several options, check the source code documentation for more information.
	// Close and output PDF document
	// This method has several options, check the source code documentation for more information.
	
	$pdfFileName = 'RemainingDocs.pdf';
	$newPdf->Output(__DIR__ . '/uploads/remianing_pdfs/' . $pdfFileName, 'F');
	
	$pdf->addPDF('uploads/remianing_pdfs/RemainingDocs.pdf', 'all', 'P');
	
	// echo "here14";
	// die();
	
}



//echo $_SERVER['DOCUMENT_ROOT'].'/test.pdf';
$pdf->merge();


?>