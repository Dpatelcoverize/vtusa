<?php
//
// File: small_goods_worksheet.php
// Author: Charles Parry
// Date: 5/21/2022
//
//

// Turn on error reporting
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
//error_reporting(E_ALL);


$pageBreadcrumb = "Small Goods Worksheet";
$pageTitle = "Small Goods Worksheet";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');

// PDF function
require_once "lib/pdfHelper.php";

require_once 'vendor/autoload.php';

include 'PDFMerger/PDFMerger.php';

use Classes\GeneratePDF;

// Variables.
$dealerID = "";
$smallGoodsCoverageID = "";
$smallGoodsPricingID = "";
$warrantyID = "";
$vehicleID = "";
$errorMessage = "";

$itemCategoryCode = "";
$itemCategoryDesc = "";
$quantityCount = "";
$manufacturedYear = "";
$manufacturerName = "";
$modelNumber = "";
$serialNumber = "";
$makeNumber = "";
$limitOfLiabilityAmount = "";
$salesAgentCostAmount = "";
$salesAgentCommissionAmount = "";
$dealerCostAmount = "";
$dealerMarkupAmount = "";
$actualPriceAmount = "";
$haveReceipt = "";

$insertingNew = "N";

$form_err    = "";

session_start();


// Make sure a dealer is currently logged in, or go back to the Agreement
if(!(isset($_SESSION["userType"])) || !($_SESSION["userType"] == "dealer")){
    header("location: index.php");
    exit;
}

// Get a dealer ID from session.
if(!(isset($_SESSION["id"]))){
    header("location: index.php");
    exit;
}else{
	$dealerID = $_SESSION["id"];
    $adminID = $_SESSION["admin_id"];
}


// See if we are specifying a warrantyID in the URL request.
if(isset($_GET["warrantyID"])){
	$_SESSION["warrantyID"] = $_GET["warrantyID"];
}


// Get a Warranty ID from session.
if(!(isset($_SESSION["warrantyID"]))){
    header("location: index.php");
    exit;
}else{
	$warrantyID = $_SESSION["warrantyID"];
}


// Are we deleting a row?
if(isset($_GET["pageAction"])){

		$pageAction = $_GET["pageAction"];

		if($pageAction=="delete"){

			if(isset($_GET["smallGoodsCoverageID"]) && is_numeric($_GET["smallGoodsCoverageID"])){
				$smallGoodsCoverageID = $_GET["smallGoodsCoverageID"];

				if(isset($_GET["warrantyID"]) && is_numeric($_GET["warrantyID"])){

					$warrantyID = $_GET["warrantyID"];

					// Set the logical delted flag
					$query = "UPDATE Sml_Goods_Cvge SET Is_Deleted_Flg='Y' WHERE
							   Sml_Goods_Cvge_ID=".$smallGoodsCoverageID.";";
					$result = $link->query($query);


					// Update values, set the 'actual' markup to the 'max' markup.
					$totalSGUpdateResult = updateWarrantySmallGoodsTotals($link,$warrantyID,"Y");

					// Call our function to updated the TOTALS columns in the Cntrct table, which is the sum
					//  of base + add-on + small goods.  Need to refresh these totals whenever changes are made
					// Include Actuals
					$totalUpdateResult = updateWarrantyTotals($link,$warrantyID,"Y");

					header("location: small_goods_worksheet.php?warrantyID=".$warrantyID);
					exit;

				}else{

					header("location: warranty_pending.php");
					exit;

				}


			}else{
				header("location: warranty_pending.php");
				exit;
			}



		}

}



// Are we loading a saved Small Goods Coverage record, to continue editing?
if(isset($_GET["smallGoodsCoverageID"])){
	$smallGoodsCoverageID = $_GET["smallGoodsCoverageID"];

	$sql = "SELECT * FROM Sml_Goods_Cvge WHERE Sml_Goods_Cvge_ID=".$smallGoodsCoverageID;
	$result = $link->query($sql);
	$row = $result->fetch_assoc();

	$itemCategoryCode = $row["Item_Cat_Type_Cd"];
	$itemCategoryDesc = $row["Item_Cat_Type_Desc"];
	$manufacturedYear = $row["Mfrd_Yr_Nbr"];
	$manufacturerName = $row["Mfr_Nm"];
	$modelNumber = $row["Model_Nbr"];
	$serialNumber = $row["Ser_nbr"];
	$makeNumber = $row["Mk_Nbr"];
	$limitOfLiabilityAmount = $row["Actl_Lmt_Of_Liabiltiy_Amt"];
	$smallGoodsPricingID = $row["Sml_Goods_Gnrc_Prcg_ID"];
	$haveReceipt = $row["sml_goods_rcpt_flg"];
}



// Process form data when form is submitted.
if($_SERVER["REQUEST_METHOD"] == "POST"){

//	die("here");
	// Get form fields
    if(!empty(trim($_POST["smallGoodsCoverageID"]))){
        $smallGoodsCoverageID = trim($_POST["smallGoodsCoverageID"]);
    }

    if(!empty(trim($_POST["smallGoodsPricingID"]))){
        $smallGoodsPricingID = trim($_POST["smallGoodsPricingID"]);

		$sql = "SELECT * FROM Sml_Goods_Gnrc_Prcg WHERE Sml_Goods_Gnrc_Prcg_ID=".$smallGoodsPricingID;
		$result = $link->query($sql);
		$row = $result->fetch_assoc();

		$itemCategoryCode = $row["Item_Cat_Type_Cd"];
		$itemCategoryDesc = $row["Item_Cat_Type_Desc"];
		$limitOfLiabilityAmount = $row["Gnrc_Lmt_Of_Lblty_Amt"];
		$agentCost = $row["Agt_Cst_Amt"];
		$agentCommission = $row["Agt_Comssn_Amt"];
		$dealerCost = $row["Dlr_Cst_Amt"];
		$dealerMarkup = $row["Dlr_Mrkp_Amt"];
		$msrpTotal = $row["MSRP_Amt"];
    }else{
		header("location: small_goods_worksheet.php");
		exit;
	}

    if(!empty(trim($_POST["warrantyID"]))){
        $warrantyID = trim($_POST["warrantyID"]);
    }

    if(!empty(trim($_POST["manufacturedYear"]))){
        $manufacturedYear = trim($_POST["manufacturedYear"]);
    }

    if(!empty(trim($_POST["manufacturerName"]))){
        $manufacturerName = trim($_POST["manufacturerName"]);
    }

    if(!empty(trim($_POST["modelNumber"]))){
        $modelNumber = trim($_POST["modelNumber"]);
    }

    if(!empty(trim($_POST["makeNumber"]))){
        $makeNumber = trim($_POST["makeNumber"]);
    }

    if(!empty(trim($_POST["serialNumber"]))){
        $serialNumber = trim($_POST["serialNumber"]);
    }



	// If we got a smallGoodsCoverageID from the form, we are updating, otherwise create new.
	if($smallGoodsCoverageID!=""){
		/* Prepare an insert statement to create a Sml_Goods_Cvge entry */
		$sqlString  = "UPDATE Sml_Goods_Cvge SET Sml_Goods_Gnrc_Prcg_ID=?,item_cat_type_cd=?,item_cat_type_desc=?, Gnrc_Item_Cat_Qty_Cnt=1,";
		$sqlString .= "Mfrd_Yr_Nbr=?, Mfr_Nm=?, Model_Nbr=?, Mk_Nbr=?, Ser_nbr=?,Actl_Lmt_Of_Liabiltiy_Amt=?, ";
		$sqlString .= "sml_goods_rcpt_flg=? WHERE Sml_Goods_Cvge_ID=?";

		$stmt = mysqli_prepare($link, $sqlString);

		/* Bind variables to parameters */
		$val1 = $smallGoodsPricingID;
		$val2 = $itemCategoryCode;
		$val3 = $itemCategoryDesc;
		$val4 = $manufacturedYear;
		$val5 = $manufacturerName;
		$val6 = $modelNumber;
		$val7 = $makeNumber;
		$val8 = $serialNumber;
		$val9 = $limitOfLiabilityAmount;
		$val10 = $haveReceipt;
		$val11 = $smallGoodsCoverageID;

		mysqli_stmt_bind_param($stmt, "isssssssssi", $val1,$val2,$val3,$val4,$val5,$val6,$val7,$val8,$val9,$val10,$val11);

	}else{
		/* Prepare an insert statement to create a Sml_Goods_Cvge entry */
		$sqlString  = "INSERT INTO Sml_Goods_Cvge (Sml_Goods_Gnrc_Prcg_ID,item_cat_type_cd,item_cat_type_desc,Gnrc_Item_Cat_Qty_Cnt,";
		$sqlString .= "Mfrd_Yr_Nbr, Mfr_Nm, Model_Nbr, Mk_Nbr, Ser_nbr, Gnrc_Lmt_Of_Lblty_Amt, Actl_Lmt_Of_Liabiltiy_Amt, ";
		$sqlString .= "Sales_Agt_Cst_Amt,Sales_Agt_Comssn_Amt,Dlr_Cst_Amt,Dlr_Mrkp_Max_Amt,Actl_Prc_Amt,sml_goods_rcpt_flg,Cntrct_ID) ";
		$sqlString .= "values (?,?,?,1,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

		$stmt = mysqli_prepare($link, $sqlString);

		/* Bind variables to parameters */
		$val1 = $smallGoodsPricingID;
		$val2 = $itemCategoryCode;
		$val3 = $itemCategoryDesc;
		$val4 = $manufacturedYear;
		$val5 = $manufacturerName;
		$val6 = $modelNumber;
		$val7 = $makeNumber;
		$val8 = $serialNumber;
		$val9 = $limitOfLiabilityAmount;
		$val10 = $limitOfLiabilityAmount;
		$val11 = $agentCost;
		$val12 = $agentCommission;
		$val13 = $dealerCost;
		$val14 = $dealerMarkup;
		$val15 = $msrpTotal;
		$val16 = $haveReceipt;
		$val17 = $warrantyID;

		mysqli_stmt_bind_param($stmt, "isssssssssiiiiisi", $val1,$val2,$val3,$val4,$val5,$val6,$val7,$val8,$val9,$val10,$val11,$val12,$val13,$val14,$val15,$val16,$val17);

		$insertingNew = "Y";

	}

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);

	if ($result) {
		$last_id = mysqli_insert_id($link);
		if($smallGoodsCoverageID==""){
			$smallGoodsCoverageID = $last_id;
		}
		//echo "<br /><br />New record created successfully. Last inserted ID is: " . $last_id;
	} else {
//	  echo "<br /><br />Error: " . $sql . "<br>" . mysqli_error($link);
	}

	// Call our routine to update the Small Goods TOTAL columns in the Cntrct table, based on the changes
	//  to small good here.
	if($insertingNew=="Y"){
		// For initial insert, we want to set the 'actual' markup to the 'max' markup.
		$totalSGUpdateResult = updateWarrantySmallGoodsTotals($link,$warrantyID,"Y");

		// Call our function to updated the TOTALS columns in the Cntrct table, which is the sum
		//  of base + add-on + small goods.  Need to refresh these totals whenever changes are made
		// Include Actuals
		$totalUpdateResult = updateWarrantyTotals($link,$warrantyID,"Y");

	}else{
		$totalSGUpdateResult = updateWarrantySmallGoodsTotals($link,$warrantyID);

		// Call our function to updated the TOTALS columns in the Cntrct table, which is the sum
		//  of base + add-on + small goods.  Need to refresh these totals whenever changes are made
		// DO NOT Include Actuals
		$totalUpdateResult = updateWarrantyTotals($link,$warrantyID);

	}



	/********************
      Handle Receipt
    *********************/

	// Get the file type details
    if(isset($_POST["fileType"]) && ($_POST["fileType"]!="")){
		$fileType = $_POST["fileType"];
    }else{
		$fileType = "";
	}


    if(isset($_FILES['receiptImage']['name']) && ($_FILES['receiptImage']['name']!="")){
		$filename = $_FILES['receiptImage']['name'];
	    $ext = pathinfo($filename,PATHINFO_EXTENSION);

		// Randomize the name a bit, in case the customer uploads a bunch of receipt
		//  files with the same name.
		$filename = substr(md5(rand()), 0, 7).$filename;

        $haveReceipt = "Y";


		/* Allow whatever type of file...

	    $allowed = array("pdf" => "application/pdf","jpg" => "image/jpg","png" => "image/png");
	    if(!array_key_exists(strtolower($ext),$allowed)){
	        $_SESSION['status'] = "The file format is not acceptable";
			header("location: small_goods_worksheet.php?smallGoodsCoverageID=".$smallGoodsCoverageID);
			exit;
	    }
		*/

		// Save the file

		// To stay organized, we will gather our files into a folder named for the warrantyID
		if (!file_exists("uploads/warranty_pdf/".$warrantyID)) {
			mkdir("uploads/warranty_pdf/".$warrantyID, 0777, true);
		}
		$filename = str_replace(" ", "_", $filename) ;
	    move_uploaded_file($_FILES['receiptImage']['tmp_name'],"uploads/warranty_pdf/".$warrantyID."/".$filename);



		// Save the path to the file in File_Assets for this warrantyID

		$query = "SELECT ul.Pers_ID FROM Usr_Loc ul, Pers p WHERE ul.Dlr_Acct_ID=" . $dealerID . " AND
				  ul.Pers_ID = p.Pers_ID AND p.Cntct_Prsn_For_Acct_Flg='Y';";
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$primary_Contact_Person_id = $row['Pers_ID'];


		// Insert file in our tracking table
		$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
										Sml_Goods_Cvge_ID,Path_to_File,File_Asset_Type_ID,createdDate) VALUES (?,?,?,?,?,?,?,NOW())");

/*
echo "dealerID=".$dealerID;
echo "<br />";
echo "primary_Contact_Person_id=".$primary_Contact_Person_id;
echo "<br />";
echo "adminID=".$adminID;
echo "<br />";
echo "warrantyID=".$warrantyID;
echo "<br />";
echo "smallGoodsCoverageID=".$smallGoodsCoverageID;
echo "<br />";
echo "fileType=".$fileType;
echo "<br />";
die();
*/

	/* Bind variables to parameters */
		$val1 = $dealerID;
		$val2 = $primary_Contact_Person_id;
		$val3 = $adminID;
		$val4 = $warrantyID;
		$val5 = $smallGoodsCoverageID;
		$val6 = "/uploads/warranty_pdf/".$warrantyID."/".$filename;
		$val7 = $fileType;

		mysqli_stmt_bind_param($stmt, "iiiiisi", $val1, $val2, $val3, $val4, $val5, $val6, $val7);


	/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


		// Update the small goods record to show that we have a receipt
		$sqlString  = "UPDATE Sml_Goods_Cvge SET sml_goods_rcpt_flg='Y' WHERE Sml_Goods_Cvge_ID=?";

		$stmt = mysqli_prepare($link, $sqlString);

		/* Bind variables to parameters */
		$val1 = $smallGoodsCoverageID;

		mysqli_stmt_bind_param($stmt, "i", $val1);
		$result = mysqli_stmt_execute($stmt);


    }else{
		$filename = "";
	    $ext = "";
        $haveReceipt = "N";
	}


	/********************
      END Handle Receipt
    *********************/






    //Creating PDF File

	//Get customer and vehicle info
	$query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Cntrct_ID=".$warrantyID." AND
	c.Veh_ID = v.Veh_ID AND
	c.Created_Warranty_ID is NULL AND
	c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
	cd.Cntrct_Type_Cd='WD' AND
	cd.Is_Deleted_Flg != 'Y' ";

    $result = $link->query($query);

    $result = mysqli_fetch_assoc($result);
	$customerName = $result["Cstmr_Nme"];
	$agreementDate = $result["Contract_Date"];
	$vin = $result["Veh_Id_Nbr"];
    // Get Dealer info
	$query = "SELECT Pers_ID FROM Pers WHERE Acct_ID=" . $dealerID . ";";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$primary_Contact_Person_id = $row['Pers_ID'];

	// Get the contract info
	$query = "SELECT cd.Cntrct_Dim_ID, cd.Cntrct_Term_Mnths_Nbr FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=" . $dealerID . " AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID ORDER By cd.Cntrct_Dim_ID Desc";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$contract_dim_ID = $row["Cntrct_Dim_ID"];
	$coverage_terms = $row["Cntrct_Term_Mnths_Nbr"];

	$agreementStartDate = strtotime($agreementDate);
	$endDate = date('Y-m-d', strtotime('+'.$coverage_terms.' years', $agreementStartDate));
	$endDate = date('Y-m-d', strtotime('-1 day', strtotime($endDate)));

    //Get SUmmary Goods info
    $currentSelectionsArray = array();
    $loopstart=0;
    $temp="";
    $temp2="";

	$sql = "SELECT sum(Gnrc_Item_Cat_Qty_Cnt) as sumQty,Item_Cat_Type_Cd,Item_Cat_Type_Desc FROM
	        Sml_Goods_Cvge WHERE Is_Deleted_Flg!='Y' AND Cntrct_ID=".$warrantyID." group by `Sml_Goods_Gnrc_Prcg_ID`";
	$smallGoodSummary = $link->query($sql);

	$numRows = mysqli_num_rows($smallGoodSummary);
	if ($numRows > 0) {
		while($row = mysqli_fetch_assoc($smallGoodSummary)) {
			$loopstart++;
			$currentSelectionsArray[$row["Item_Cat_Type_Cd"]]=$row["sumQty"];
		}
	}

    // Look up the available small goods items
    //$query = "SELECT * FROM Sml_Goods_Cvge a , Sml_Goods_Gnrc_Prcg b WHERE a.Sml_Goods_Gnrc_Prcg_ID = b.Sml_Goods_Gnrc_Prcg_ID";
    $query = "SELECT * FROM Sml_Goods_Gnrc_Prcg ORDER BY Item_Cat_Type_Cd ASC";
    $smallGoodSummary = $link->query($query);

    $addendumTotal = 0;
    $extendedTotal = 0;
	$totalQuantitySubmitted = 0;

    $data = [ 'CUSTOMER NAME' => $customerName,
		 'Equipment FULL VIN' => $vin,
		 'TSGA Submit Date' => $agreementDate,
		 'Agreement Date'  => $agreementDate,
		 'WA End Date' =>  $endDate,
		 'QUANTITY SUBMITTED A', 'QUANTITY APPROVED A' ,
		 'QUANTITY SUBMITTED B', 'QUANTITY APPROVED B',
		 'QUANTITY SUBMITTED C', 'QUANTITY APPROVED C',
		 'QUANTITY SUBMITTED D', 'QUANTITY APPROVED D',
		 'QUANTITY SUBMITTED E', 'QUANTITY APPROVED E',
		 'QUANTITY SUBMITTED F', 'QUANTITY APPROVED F',
         'QUANTITY SUBMITTED G', 'QUANTITY APPROVED G',
         'QUANTITY SUBMITTED F', 'QUANTITY APPROVED H',
		 'QUANTITY SUBMITTED I', 'QUANTITY APPROVED I',
		 'QUANTITY SUBMITTED J', 'QUANTITY APPROVED J',
		 'QUANTITY SUBMITTED K', 'QUANTITY APPROVED K',
		 'QUANTITY SUBMITTED L', 'QUANTITY APPROVED L',
		 'QUANTITY SUBMITTED M', 'QUANTITY APPROVED M',
		 'QUANTITY SUBMITTED N', 'QUANTITY APPROVED N',
		 'QUANTITY SUBMITTED O', 'QUANTITY APPROVED O',
		 'QUANTITY SUBMITTED P', 'QUANTITY APPROVED P',
		 'QUANTITY SUBMITTED Q', 'QUANTITY APPROVED Q',
		 'QUANTITY SUBMITTED R', 'QUANTITY APPROVED R',
		 'QUANTITY SUBMITTED S', 'QUANTITY APPROVED S',
		 'QUANTITY SUBMITTED T', 'QUANTITY APPROVED T',
		 'QUANTITY SUBMITTED U', 'QUANTITY APPROVED U',
		 'QUANTITY SUBMITTED V', 'QUANTITY APPROVED V',
		 'QUANTITY SUBMITTED W', 'QUANTITY APPROVED W',
		 'QUANTITY SUBMITTED X', 'QUANTITY APPROVED X',
		 'QUANTITY SUBMITTED Y', 'QUANTITY APPROVED Y',
		 'QUANTITY SUBMITTED Z', 'QUANTITY APPROVED Z',
		 'Estimated Items Cost', 'Estimated Items Submitted',
		 ];
		$totalAmount = 0;
    if (mysqli_num_rows($smallGoodSummary) > 0) {
        // output data of each row
        $loopstart = 0;
        while($row = mysqli_fetch_assoc($smallGoodSummary)) {
          $loopstart++;

          if(isset($currentSelectionsArray[$row["Item_Cat_Type_Cd"]])){
              $valuePlaceholder = $currentSelectionsArray[$row["Item_Cat_Type_Cd"]];
			  if($valuePlaceholder > 0)
			  {
                if($row["Item_Cat_Type_Cd"] == 'A')
				{
					$data['QUANTITY SUBMITTED A'] = $valuePlaceholder;
					//$data['QUANTITY APPROVED A'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					$totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'B')
				{
					$data['QUANTITY SUBMITTED B'] = $valuePlaceholder;
					//$data['QUANTITY APPROVED B'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					$totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'C')
				{
					   $data['QUANTITY SUBMITTED C'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED C'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'D')
				{
					   $data['QUANTITY SUBMITTED D'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED D'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'E')
				{
					   $data['QUANTITY SUBMITTED E'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED E'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'F')
				{
					   $data['QUANTITY SUBMITTED F'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED F'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
                                else if($row["Item_Cat_Type_Cd"] == 'G')
				{
					   $data['QUANTITY SUBMITTED G'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED G'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
                                else if($row["Item_Cat_Type_Cd"] == 'H')
		 		{
					   $data['QUANTITY SUBMITTED I'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED I'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'I')
				{
					   $data['QUANTITY SUBMITTED J'] = $valuePlaceholder;
					  // $data['QUANTITY APPROVED J'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}else if($row["Item_Cat_Type_Cd"] == 'J')
				{
					   $data['QUANTITY SUBMITTED K'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED K'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'K')
				{
					   $data['QUANTITY SUBMITTED L'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED L'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'L')
				{
					   $data['QUANTITY SUBMITTED M'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED M'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'M')
				{
					   $data['QUANTITY SUBMITTED N'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED N'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'N')
				{
					   $data['QUANTITY SUBMITTED O'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED O'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'O')
				{
					   $data['QUANTITY SUBMITTED P'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED P'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'P')
				{
					   $data['QUANTITY SUBMITTED Q'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED Q'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'Q')
				{
					   $data['QUANTITY SUBMITTED R'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED R'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'R')
				{
					   $data['QUANTITY SUBMITTED S'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED S'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'S')
				{
					   $data['QUANTITY SUBMITTED T'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED T'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'T')
				{
					   $data['QUANTITY SUBMITTED U'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED U'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'U')
				{
					   $data['QUANTITY SUBMITTED V'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED V'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'V')
				{
					   $data['QUANTITY SUBMITTED W'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED W'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'W')
				{
				       $data['QUANTITY SUBMITTED X'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED X'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}else if($row["Item_Cat_Type_Cd"] == 'X')
				{
				       $data['QUANTITY SUBMITTED Y'] = $valuePlaceholder;
					   //$data['QUANTITY APPROVED Y'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'Y')
				{
					   $data['QUANTITY SUBMITTED Z'] = $valuePlaceholder;
					  // $data['QUANTITY APPROVED Z'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
				else if($row["Item_Cat_Type_Cd"] == 'Z')
				{
					   $data['QUANTITY SUBMITTED H'] = $valuePlaceholder;
					  // $data['QUANTITY APPROVED H'] = '$'.number_format(($row["MSRP_Amt"] * $valuePlaceholder),0);
					   $totalQuantitySubmitted += $valuePlaceholder;
				}
			  $addendumTotal += $row["Dlr_Cst_Amt"] * $valuePlaceholder;
			  }
            }
          //$extendedTotal += ($row["MSRP_Amt"] * $valuePlaceholder);

        }
		  $data['Estimated Items Submitted'] = $totalQuantitySubmitted;
          $data['Estimated Items Cost'] =  '$'.$addendumTotal;
      }


 $pdfFileName = str_replace(" ", "_", $customerName) . '_' . str_replace(" ", "_", $warrantyID) . '_' . time() . '.pdf';

 $pdf = new GeneratePDF;
 $response = $pdf->generateSgSummary($data,  $pdfFileName);


 // Save Pddf into database

 // Add this file to our File_Assets tracking table
 //  Set type=2 for 'dealer W9'.
 $stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,Warranty_Cntrct_ID,
                             Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,?,13,'Small Goods Summary',NOW())");

 /* Bind variables to parameters */
 $val1 = $dealerID;
 $val2 = $primary_Contact_Person_id;
 $val3 = $adminID;
 $val4 = $contract_dim_ID;
 $val5 = '/uploads/small_goods_summary_pdf/' . $pdfFileName;
 $val6 = $warrantyID;

 mysqli_stmt_bind_param($stmt, "iiiiis", $val1, $val2, $val3, $val4, $val6, $val5);

 /* Execute the statement */
 $result = mysqli_stmt_execute($stmt);

 //============================================================+
 // END OF FILE
 //============================================================+


//Small goods detail PDF
            // create new PDF document

			// Get small goods detail associated with this warrantyID
			$query  = "SELECT * FROM Sml_Goods_Cvge WHERE Cntrct_ID=".$warrantyID." AND Is_Deleted_Flg!='Y'";
			$smallGoodsDetail = $link->query($query);
            $dataArray = [];
			$smallGoods_count = 0;

			while($row = mysqli_fetch_assoc($smallGoodsDetail))
			{
				$smallGoods_count++;
				array_push( $dataArray,$row );
			}



			$loopwillIterate = ceil($smallGoods_count / 30);


			$allSmallGoodsDetailPDF = [];

			$start = 0;

			if($loopwillIterate > 1)
			{
				$end = 30;
			}
			else
			{
				$end = $smallGoods_count;
			}


			for( $i=0 ; $i < $loopwillIterate ; $i++ ) {



				$data = [ 'CUSTOMER NAME' => $customerName, 'Submission Date' => $agreementDate, 'TRUCK VIN' => $vin, 'Page #', 'Total Pages',
				 		' Entry 1 Type'  => "" , ' Entry 1 Purchase Date'  => "", 'Entry 1 Make'   => "", 'Entry 1 Model'  => "" , 'Entry 1 Serial #' => "", 'Entry 1 Receipt' => "", 'Entry 1 Approved' => "",
				 		' Entry 2 Type'  => "" , ' Entry 2 Purchase Date'   => "", 'Entry 2 Make'   => "", 'Entry 2 Model'  => "" , 'Entry 2 Serial #' => "", 'Entry 2 Receipt' => "", 'Entry 2 Approved' => "",
						' Entry 3 Type'  => "" , ' Entry 3 Purchase Date'   => "", 'Entry 3 Make'   => "", 'Entry 3 Model'  => "" , 'Entry 3 Serial #' => "", 'Entry 3 Receipt' => "", 'Entry 3 Approved' => "",
						' Entry 4 Type'  => "" , ' Entry 4 Purchase Date'   => "", 'Entry 4 Make'   => "", 'Entry 4 Model'  => "" , 'Entry 4 Serial #' => "", 'Entry 4 Receipt' => "", 'Entry 4 Approved' => "",
						' Entry 5 Type'  => "" , ' Entry 5 Purchase Date'   => "", 'Entry 5 Make'   => "", 'Entry 5 Model'  => "" , 'Entry 5 Serial #' => "", 'Entry 5 Receipt' => "", 'Entry 5 Approved' => "",
						' Entry 6 Type'  => "" , ' Entry 6 Purchase Date'   => "", 'Entry 6 Make'   => "", 'Entry 6 Model'  => "" , 'Entry 6 Serial #' => "", 'Entry 6 Receipt' => "", 'Entry 6 Approved' => "",
						' Entry 7 Type'  => "" , ' Entry 7 Purchase Date'   => "", 'Entry 7 Make'   => "", 'Entry 7 Model'  => "" , 'Entry 7 Serial #' => "", 'Entry 7 Receipt' => "", 'Entry 7 Approved' => "",
						' Entry 8 Type'  => "" , ' Entry 8 Purchase Date'   => "", 'Entry 8 Make'   => "", 'Entry 8 Model'  => "" , 'Entry 8 Serial #' => "", 'Entry 8 Receipt' => "", 'Entry 8 Approved' => "",
						' Entry 9 Type'  => "" , ' Entry 9 Purchase Date'   => "", 'Entry 9 Make'   => "", 'Entry 9 Model'  => "",  'Entry 9 Serial #', 'Entry 9 Receipt' => "", 'Entry 9 Approved' => "",
						' Entry 10 Type' => "" , ' Entry 10 Purchase Date'  => "", 'Entry 10 Make'  => "", 'Entry 10 Model' => "", 'Entry 10 Serial #' => "", 'Entry 10 Receipt' => "", 'Entry 10 Approved' => "",
						' Entry 11 Type' => "" , ' Entry 11 Purchase Date'  => "", 'Entry 11 Make'  => "", 'Entry 11 Model' => "" , 'Entry 11 Serial #' => "", 'Entry 11 Receipt' => "", 'Entry 11 Approved' => "",
						' Entry 12 Type' => "" , ' Entry 12 Purchase Date'  => "", 'Entry 12 Make'  => "", 'Entry 12 Model' => "" , 'Entry 12 Serial #' => "", 'Entry 12 Receipt' => "", 'Entry 12 Approved' => "",
						' Entry 13 Type' => "" , ' Entry 13 Purchase Date'  => "", 'Entry 13 Make'  => "", 'Entry 13 Model' => "" , 'Entry 13 Serial #' => "", 'Entry 13 Receipt' => "", 'Entry 13 Approved' => "",
						' Entry 14 Type' => "" , ' Entry 14 Purchase Date'  => "", 'Entry 14 Make'  => "", 'Entry 14 Model' => "" , 'Entry 14 Serial #' => "", 'Entry 14 Receipt' => "", 'Entry 14 Approved' => "",
						' Entry 15 Type' => "" , ' Entry 15 Purchase Date'  => "", 'Entry 15 Make'  => "", 'Entry 15 Model' => "" , 'Entry 15 Serial #' => "", 'Entry 15 Receipt' => "", 'Entry 15 Approved' => "",
						' Entry 16 Type' => "" , ' Entry 16 Purchase Date'  => "", 'Entry 16 Make'  => "", 'Entry 16 Model' => "" , 'Entry 16 Serial #' => "", 'Entry 16 Receipt' => "", 'Entry 16 Approved' => "",
						' Entry 17 Type' => "" , ' Entry 17 Purchase Date'  => "", 'Entry 17 Make'  => "", 'Entry 17 Model' => "" , 'Entry 17 Serial #' => "", 'Entry 17 Receipt' => "", 'Entry 17 Approved' => "",
						' Entry 18 Type' => "" , ' Entry 18 Purchase Date'  => "", 'Entry 18 Make'  => "", 'Entry 18 Model' => "" , 'Entry 18 Serial #' => "", 'Entry 18 Receipt' => "", 'Entry 18 Approved' => "",
						' Entry 19 Type' => "" , ' Entry 19 Purchase Date'  => "", 'Entry 19 Make'  => "", 'Entry 19 Model' => "" , 'Entry 19 Serial #' => "", 'Entry 19 Receipt' => "", 'Entry 19 Approved' => "",
						' Entry 20 Type' => "" , ' Entry 20 Purchase Date'  => "", 'Entry 20 Make'  => "", 'Entry 20 Model' => "" , 'Entry 20 Serial #' => "", 'Entry 20 Receipt' => "", 'Entry 20 Approved' => "",
						' Entry 21 Type' => "" , ' Entry 21 Purchase Date'  => "", 'Entry 21 Make'  => "", 'Entry 21 Model' => "" , 'Entry 21 Serial #' => "", 'Entry 21 Receipt' => "", 'Entry 21 Approved' => "",
						' Entry 22 Type' => "" , ' Entry 22 Purchase Date'  => "", 'Entry 22 Make'  => "", 'Entry 22 Model' => "" , 'Entry 22 Serial #' => "", 'Entry 22 Receipt' => "", 'Entry 22 Approved' => "",
						' Entry 23 Type' => "" , ' Entry 23 Purchase Date'  => "", 'Entry 23 Make'  => "", 'Entry 23 Model' => "" , 'Entry 23 Serial #' => "", 'Entry 23 Receipt' => "", 'Entry 23 Approved' => "",
						' Entry 24 Type' => "" , ' Entry 24 Purchase Date'  => "", 'Entry 24 Make'  => "", 'Entry 24 Model' => "" , 'Entry 24 Serial #' => "", 'Entry 24 Receipt' => "", 'Entry 24 Approved' => "",
						' Entry 25 Type' => "" , ' Entry 25 Purchase Date'  => "", 'Entry 25 Make'  => "", 'Entry 25 Model' => "" , 'Entry 25 Serial #' => "", 'Entry 25 Receipt' => "", 'Entry 25 Approved' => "",
						' Entry 26 Type' => "" , ' Entry 26 Purchase Date'  => "", 'Entry 26 Make'  => "", 'Entry 26 Model' => "" , 'Entry 26 Serial #' => "", 'Entry 26 Receipt' => "", 'Entry 26 Approved' => "",
						' Entry 27 Type' => "" , ' Entry 27 Purchase Date'  => "", 'Entry 27 Make'  => "", 'Entry 27 Model' => "", 'Entry 27 Serial #' => "", 'Entry 27 Receipt' => "", 'Entry 27 Approved' => "",
						' Entry 28 Type' => "" , ' Entry 28 Purchase Date'  => "", 'Entry 28 Make'  => "", 'Entry 28 Model' => "" , 'Entry 28 Serial #' => "", 'Entry 28 Receipt' => "", 'Entry 28 Approved' => "",
						' Entry 29 Type' => "" , ' Entry 29 Purchase Date'  => "", 'Entry 29 Make'  => "", 'Entry 269Model' => "" , 'Entry 29 Serial #' => "", 'Entry 29 Receipt' => "", 'Entry 29 Approved' => "",
				 		' Entry 30 Type' => "" , ' Entry 30 Purchase Date'  => "", 'Entry 30 Make'  => "", 'Entry 30 Model' => "" , 'Entry 30 Serial #' => "", 'Entry 30 Receipt' => "", 'Entry 30 Approved' => "", ];


			  $counter = 1;

			   for($j =  $start ; $j < $end ; $j++){
				$data['Entry '.$counter.' Type'] = $dataArray[$j]["Item_Cat_Type_Cd"];
				$data['Entry '.$counter.' Purchase Date'] = $dataArray[$j]["Mfrd_Yr_Nbr"];
				$data['Entry '.$counter.' Make'] = $dataArray[$j]["Mk_Nbr"];
				$data['Entry '.$counter.' Model'] = $dataArray[$j]["Model_Nbr"];
				$data['Entry '.$counter.' Serial #'] = $dataArray[$j]["Ser_nbr"];
				$counter++;
				}
				$fileNumber = $i +1;
				$data['Page #'] = $fileNumber;
				$data['Total Pages'] = $loopwillIterate;
				$filename = "smallGoodsDeta".$fileNumber.".pdf";
				$pdf = new GeneratePDF;
				$response = $pdf->generateSgDetail($data , $filename);
				$PdfLocation = 'uploads/small_goods_detail_pdf_for_merging/' . $filename;
				array_push( $allSmallGoodsDetailPDF , $PdfLocation);



				$start+=30;

				$remaining = $smallGoods_count - $end;

				if($remaining > 30)
				{
					$end +=30;
				}
				else
				{
					$end +=  $remaining;

				}

				// die("Start".$start."end".$end);

		}





			// die($allSmallGoodsDetailPDF[1]);




			// $pdf = new \PDFMerger\PDFMerger;
			$pdfFileName = str_replace(" ", "_", $customerName) . '_' . str_replace(" ", "_", $warrantyID) . '_' . time() . '.pdf';
			$command = "/home/dh_zzb8f9/pdftk";
			$outputdir = "/home/dh_zzb8f9/portal.vitaltrendsusa.com/uploads/small_goods_detail_pdf/".$pdfFileName;
			$pdfFiles = " ";
			for( $i = 0 ; $i<$loopwillIterate ; $i++){

				$fileNumber = $allSmallGoodsDetailPDF[$i];
				$pdfFiles .= $fileNumber;
				$pdfFiles .= " ";
			}
			exec($command.$pdfFiles." cat output ".$outputdir);

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
				$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,Warranty_Cntrct_ID,
								Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,?,18,'Small Goods Detail',NOW())");

				/* Bind variables to parameters */
				$val1 = $dealerID;
				$val2 = $primary_Contact_Person_id;
				$val3 = $adminID;
				$val4 = $contract_dim_ID;
				$val5 = '/uploads/small_goods_detail_pdf/' . $pdfFileName;
				$val6 = $warrantyID;

				mysqli_stmt_bind_param($stmt, "iiiiis", $val1, $val2, $val3, $val4, $val6, $val5);

				/* Execute the statement */
				$result = mysqli_stmt_execute($stmt);

// Close and output PDF document
// This method has several options, check the source code documentation for more information.






//============================================================+
// END OF FILE
//============================================================+

// End PDF Code here

// Redirect to next form
header("location: small_goods_worksheet.php");
exit;
die();

}


require_once("includes/header.php");

?>

		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
			<div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div>
                            <img src="images/VTPoweredbyTNG.png" alt="Vital Trends Powered by TruNorth">
                        </div>
                    </div>
                    <div class="col-md-6">
						&nbsp;
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header text-center">
                                <h4 class="card-title">Small Goods</h4>
                                <?php
									if($errorMessage!=""){
										echo "<h5>Notice: ".$errorMessage."</h5>";
									}
                                ?>
                            </div>
                            <div class="card-header text-center">
                                <h5>(<a href="warranty_pending.php">Return to Pending Warranty List</a>)</h5>
							</div>
                            <div class="card-body">
                                <div class="basic-form dealer-form">
                                    <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>
                                    <form name="dealerForm" id="smallGoodsForm" method="POST" action="" enctype="multipart/form-data">
                                    	<input type="hidden" name="warrantyID" value="<?php echo $warrantyID;?>"/>
                                    	<input type="hidden" name="smallGoodsCoverageID" value="<?php echo $smallGoodsCoverageID;?>"/>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Select Item: </label>
                                                <select name="smallGoodsPricingID">
<?php

// Look up the available small goods items
$query = "SELECT * FROM Sml_Goods_Gnrc_Prcg ORDER BY Item_Cat_Type_Cd ASC";
$personResult = $link->query($query);

if (mysqli_num_rows($personResult) > 0) {
  // output data of each row
  $loopstart = 0;
  while($row = mysqli_fetch_assoc($personResult)) {
	$loopstart++;
?>
	<option value="<?php echo $row["Sml_Goods_Gnrc_Prcg_ID"];?>" <?php if($smallGoodsPricingID == $row["Sml_Goods_Gnrc_Prcg_ID"]){echo "selected=\"selected\"";}?>>(<?php echo $row["Item_Cat_Type_Cd"];?>) <?php echo $row["Item_Cat_Type_Desc"];?></option>\n
<?php
  }
} else {
?>
<tr>
	<option value="">No Small Goods Found</option>
</tr>

<?php
}


?>
											</select>
                                            </div>

                                            <div class="form-group col-md-6">
												Quantity: 1
                                            </div>
                                            <div class="form-group col-md-6">
												<label for="manufacturedYear">Purchase Date <span class="text-danger">*</span></label>
												<input type="date" class="form-control" name="manufacturedYear" id="manufacturedYear" value="<?php echo $manufacturedYear;?>" placeholder="Manufactured Year">
											</div>

                                            <div class="form-group col-md-6">
											<label for="manufacturedYear"></label>
												<input type="text" class="form-control" name="manufacturerName" value="<?php echo $manufacturerName;?>" placeholder="Manufacturer Name">
                                            </div>
                                            <div class="form-group col-md-6">
												<input type="text" class="form-control" name="modelNumber" value="<?php echo $modelNumber;?>" placeholder="Model Number">
                                            </div>

                                            <div class="form-group col-md-6">
												<input type="text" class="form-control" name="makeNumber" value="<?php echo $makeNumber;?>" placeholder="Make Number">
                                            </div>
                                            <div class="form-group col-md-6">
												<input type="text" class="form-control" name="serialNumber" value="<?php echo $serialNumber;?>" placeholder="Serial Number">
                                            </div>

											<div class="form-group col-md-6">
												<label>Receipt Image<span class="text-danger"></span></label>
												<?php
													$fileTypeID = 14;
													$filePathResult = getFileAssetForSmallGood($link,$smallGoodsCoverageID,$fileTypeID);
												?>
												<input type="hidden" value="<?php echo $filePathResult; ?>" id="smallGoodsreciept">
												<?php
													if($filePathResult){
													?>
														<br />
														<img src="images/green_check.png" height="20" width="20" alt="File Uploaded" />
														<span style="color:green;">File Uploaded! <a href="<?php echo $filePathResult;?>" target="_blank">(view)</a></span>
														<br /><br />
													<?php
													}
												?>
													<input type="hidden" name="dealerID" id="dealerID" value="<?php echo $dealerID;?>">
													<input type="hidden" name="warrantyID" id="warrantyID" value="<?php echo $warrantyID;?>">
													<input type="hidden" name="fileType" id="fileType" value="<?php echo $fileTypeID;?>">
													<div class="form-group">
														<input name="receiptImage" id="receiptImage" type="file"><br>
													</div>

											</div>


											<!---
                                            <div class="form-group col-md-12">
												<input type="text" class="form-control" name="limitOfLiabilityAmount" value="<?php echo $limitOfLiabilityAmount;?>" placeholder="Actual Limit of Liability Amount ">
                                            </div>
											--->
                                        </div>
                                        <button type="button" id="smallGoodsFormSubmit" class="btn btn-primary">Submit</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Existing Small Goods Defined for Contract</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-responsive-md">
                                        <thead>
											<tr style="background-color:#201F58;color:#FFFFFF;font-weight:bold;">
												<!---
                                                <th>Code</th>
												--->
                                                <th>Description</th>
                                                <th>Limit of Liability</th>
                                                <th>Dealer Cost</th>
                                                <th>Dealer Markup</th>
                                                <th>MSRP Subtotal</th>
                                                <th>Purchase Date</th>
                                                <th>Make</th>
												<th>Model</th>
												<th>Serial#</th>
                                                <th>Receipt?</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php

// Get small goods detail associated with this warrantyID.
$query  = "SELECT * FROM Sml_Goods_Cvge sgc, Sml_Goods_Gnrc_Prcg sggp WHERE sgc.Cntrct_ID=".$warrantyID." AND ";
$query .= "sgc.Sml_Goods_Gnrc_Prcg_ID=sggp.Sml_Goods_Gnrc_Prcg_ID AND sgc.Is_Deleted_Flg!='Y'";
$smallGoodsResult = $link->query($query);

$totalCostRunningSum = 0;
$totalLiabilitySum = 0;

$total_Gnrc_Lmt_Of_Lblty_Amt = 0;
$total_MSRP_Amt = 0;
$total_Dlr_Cst_Amt = 0;
$total_Dlr_Mrkp_Max_Amt = 0;
$total_Actl_Prc_Amt = 0;

if (mysqli_num_rows($smallGoodsResult) > 0) {
  // output data of each row
  $loopstart = 0;
  while($row = mysqli_fetch_assoc($smallGoodsResult)) {
	$loopstart++;
?>

<tr>
	<!---
	<td><?php echo $row["Item_Cat_Type_Cd"];?></td>
	--->
	<td><?php echo $row["Item_Cat_Type_Desc"];?></td>
	<td>$<?php echo number_format((float)$row["Gnrc_Lmt_Of_Lblty_Amt"], 0, '.', ',');?></td>
	<td>$<?php echo number_format((float)$row["Dlr_Cst_Amt"], 0, '.', ',');?></td>
	<td>$<?php echo number_format((float)$row["Dlr_Mrkp_Max_Amt"], 0, '.', ',');?></td>
	<td>$<?php echo number_format((float)$row["Actl_Prc_Amt"], 0, '.', ',');?></td>
	<td><?php echo $row["Mfrd_Yr_Nbr"];?></td>
	<td><?php echo $row["Mk_Nbr"];?></td>
	<td><?php echo $row["Model_Nbr"];?></td>
	<td><?php echo $row["Ser_nbr"];?></td>
	<td><?php echo $row["sml_goods_rcpt_flg"];?></td>
	<td style="white-space: nowrap;">
		<a href="small_goods_worksheet.php?smallGoodsCoverageID=<?php echo $row["Sml_Goods_Cvge_ID"];?>">Edit</a> |
		<a href="small_goods_worksheet.php?smallGoodsCoverageID=<?php echo $row["Sml_Goods_Cvge_ID"];?>&pageAction=delete&warrantyID=<?php echo $warrantyID;?>">Delete</a>
	</td>
</tr>

<?php
	$totalCostRunningSum += $row["MSRP_Amt"];
	$totalLiabilitySum += $row["Gnrc_Lmt_Of_Lblty_Amt"];

	$total_Gnrc_Lmt_Of_Lblty_Amt += $row["Gnrc_Lmt_Of_Lblty_Amt"];
	//$total_MSRP_Amt += $row["MSRP_Amt"];
	$total_Dlr_Cst_Amt += $row["Dlr_Cst_Amt"];
	$total_Dlr_Mrkp_Max_Amt += $row["Dlr_Mrkp_Max_Amt"];
	$total_Actl_Prc_Amt += $row["Actl_Prc_Amt"];

  } // while //
?>
<tr style="background-color:#201F58;color:#FFFFFF;font-weight:bold;">
	<!---
	<td>&nbsp;</td>
	--->
	<td>Totals</td>
	<td>$<?php echo number_format((float)$total_Gnrc_Lmt_Of_Lblty_Amt, 0, '.', ',');?></td>
	<td>$<?php echo number_format((float)$total_Dlr_Cst_Amt, 0, '.', ',');?></td>
	<td>$<?php echo number_format((float)$total_Dlr_Mrkp_Max_Amt, 0, '.', ',');?></td>
	<td>$<?php echo number_format((float)$total_Actl_Prc_Amt, 0, '.', ',');?></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>

<?

} else {
?>
<tr>
	<td colspan="5">No small goods found, yet.</td>
</tr>

<?php
}

?>
                                     </tbody>
                                 </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>





            </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


<script src="./js/jSignature/jSignature.min.js"></script>
<script src="./js/jSignature/jSignInit.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

$(document).ready(function () {

	$("#smallGoodsFormSubmit").click(function(){
		var flag = 1;
		var Error_message = "";

		var currentYear = new Date().getFullYear();
		var ManufacturedYear = new Date($("#manufacturedYear").val());
		var ManufacturedYear = ManufacturedYear.getFullYear() ;


		if($("#manufacturedYear").val() == "")
		{
			Error_message += "<li>Purchase Date field is required.</li>";
			flag = 0;

		} else if(ManufacturedYear < currentYear && $("#receiptImage").get(0).files.length === 0)
		{
			Error_message += "<li>This small good is missing a receipt and is greater than 4 years old. It cannot be warrantied.</li>";
			flag = 0;


		} else if(ManufacturedYear < currentYear)
		{
			Error_message += "<li>This small good is greater than 4 years old and cannot be warrantied.</li>";
			flag = 0;

		} else if($("#receiptImage").get(0).files.length === 0 && $("#smallGoodsreciept").val() == 0)
		{
			//Error_message += "<li>This small good is missing a receipt and cannot be warrantied.</li>";
			//flag = 0;
		}
		else
		{


			flag = 1;
		}

		if(flag == 1)
		{
			$("#smallGoodsForm").submit();
		}
		else
		{

		Swal.fire({
				position: 'top-center',
				title: 'ERROR',
				html: "<ul style='padding: 0 0 0 38px;text-align: left;color: red;'>"+Error_message+"</ul>",
				showConfirmButton: true,
			});
		}
	});
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