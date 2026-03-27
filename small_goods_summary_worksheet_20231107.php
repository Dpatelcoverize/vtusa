<?php
//
// File: small_goods_summary_worksheet.php
// Author: Charles Parry
// Date: 7/20/2022
//
//

// Turn on error reporting
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
//error_reporting(E_ALL);


$pageBreadcrumb = "Small Goods Summary Worksheet";
$pageTitle = "Small Goods Summary Worksheet";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

// DB Library
require_once "lib/dblib.php";

// Include the main TCPDF library (search for installation path).
require_once('tcpdf/examples/tcpdf_include.php');


// PDF function
require_once "lib/pdfHelper.php";


require_once 'vendor/autoload.php';

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
$form_err    = "";

$currentSelectionsArray = array();
$loopCounter=0;

$wrap_Flg = "N";

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
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
if(isset($_GET["warrantyID"]) && isset($_GET["isCopy"])){

	$contractID = $_SESSION["copyQuote"];

} else if(isset($_GET["warrantyID"]) && !(isset($_GET["isCopy"])))
{
	$_SESSION["warrantyID"] = $_GET["warrantyID"];
	$contractID = $_GET["warrantyID"];
}

// Get a Warranty ID from session.

if(!(isset($_SESSION["warrantyID"]))){
    header("location: index.php");
    exit;
}else{
	$warrantyID = $_SESSION["warrantyID"];
	if(!(isset($_GET["isCopy"])))
	{
	   $contractID = $_SESSION["warrantyID"];
	}
}

// See if we have data for this warranty contract already saved
if($warrantyID!=""){

	$sql = "SELECT * FROM Sml_Goods_Cvge WHERE Cntrct_ID=".$contractID;
	$smallGoodsResult = $link->query($sql);

	$numRows = mysqli_num_rows($smallGoodsResult);
	if ($numRows > 0) {
		while($row = mysqli_fetch_assoc($smallGoodsResult)) {
			$loopCounter++;
			$currentSelectionsArray[$row["Item_Cat_Type_Cd"]]=$row["Gnrc_Item_Cat_Qty_Cnt"];
		}
	}

}


// Process form data when form is submitted.
if($_SERVER["REQUEST_METHOD"] == "POST"){

	// Get the enumerated list of all small goods from Sml_Goods_Gnrc_Prcg
	$query = "SELECT * FROM Sml_Goods_Gnrc_Prcg ORDER BY Item_Cat_Type_Cd ASC";
	$smallGoodsResult = $link->query($query);

    if (mysqli_num_rows($smallGoodsResult) > 0) {
	  // output data of each row
	  $loopCounter = 0;
		while($row = mysqli_fetch_assoc($smallGoodsResult)) {
			$loopCounter++;

			// Values for this particular Small Good from Sml_Goods_Gnrc_Prcg
			$Gnrc_Blended_Prc_Amt = $row["Gnrc_Blended_Prc_Amt"];
			$Gnrc_Lmt_Of_Lblty_Amt = $row["Gnrc_Lmt_Of_Lblty_Amt"];
			$Agt_Cst_Amt = $row["Agt_Cst_Amt"];
			$Agt_Comssn_Amt = $row["Agt_Comssn_Amt"];
			$Dlr_Cst_Amt = $row["Dlr_Cst_Amt"];
			$Dlr_Mrkp_Amt = $row["Dlr_Mrkp_Amt"];
			$MSRP_Amt = $row["MSRP_Amt"];


			// Check if we have a value for any quantity field in the form.
			if(isset($_POST["quantity_".$row["Sml_Goods_Gnrc_Prcg_ID"]])){
				//echo "<br />".$row["Item_Cat_Type_Cd"]."= ".$_POST["quantity_".$row["Sml_Goods_Gnrc_Prcg_ID"]];
				// Update if the Cd is in the currentSelectionsArray, otherwise add new
				if(isset($currentSelectionsArray[$row["Item_Cat_Type_Cd"]]) && !(isset($_GET["isCopy"]))){
					// Remove the entry if quantity==0
					if($_POST["quantity_".$row["Sml_Goods_Gnrc_Prcg_ID"]]==0){
						$sqlString = "DELETE FROM Sml_Goods_Cvge WHERE Cntrct_ID=? AND Item_Cat_Type_Cd=?";

						$stmt = mysqli_prepare($link, $sqlString);

						$val1 = $warrantyID;
						$val2 = $row["Item_Cat_Type_Cd"];

						mysqli_stmt_bind_param($stmt, "is", $val1,$val2);

						/* Execute the statement */
						$result = mysqli_stmt_execute($stmt);

					}else{
						$sqlString  = "UPDATE Sml_Goods_Cvge SET Gnrc_Item_Cat_Qty_Cnt=?,Sales_Agt_Cst_Amt=?,";
						$sqlString .= "Sales_Agt_Comssn_Amt=?,Dlr_Cst_Amt=?,Dlr_Mrkp_Max_Amt=?,Actl_Prc_Amt=? ";
						$sqlString .= "WHERE Cntrct_ID=? AND Item_Cat_Type_Cd=?";

						$stmt = mysqli_prepare($link, $sqlString);

						/* Bind variables to parameters */
						$val1 = $_POST["quantity_".$row["Sml_Goods_Gnrc_Prcg_ID"]];
						$val2 = $Agt_Cst_Amt;
						$val3 = $Agt_Comssn_Amt;
						$val4 = $Dlr_Cst_Amt;
						$val5 = $Dlr_Mrkp_Amt;
						$val6 = ($Dlr_Cst_Amt+$Dlr_Mrkp_Amt)*$_POST["quantity_".$row["Sml_Goods_Gnrc_Prcg_ID"]];
						$val7 = $warrantyID;
						$val8 = $row["Item_Cat_Type_Cd"];

						mysqli_stmt_bind_param($stmt, "iiiiiiis", $val1,$val2,$val3,$val4,$val5,$val6,$val7,$val8);

						// Call our routine to update the Small Goods TOTAL columns in the Cntrct table, based on the changes
						//  to small good here.
						//  Do not update Actl column
						//$totalSGUpdateResult = updateWarrantySmallGoodsTotals($link,$warrantyID,"N");

						/* Execute the statement */
						$result = mysqli_stmt_execute($stmt);

					}

				}else{
					// If we have a greater than 0 quantity
					if($_POST["quantity_".$row["Sml_Goods_Gnrc_Prcg_ID"]]>0){
						/* Prepare an insert statement to create a Warranty entry */
						$sqlString  = "INSERT INTO Sml_Goods_Cvge (Sml_Goods_Gnrc_Prcg_ID,item_cat_type_cd,item_cat_type_desc,Gnrc_Item_Cat_Qty_Cnt,";
						$sqlString .= "Sales_Agt_Cst_Amt,Sales_Agt_Comssn_Amt,Dlr_Cst_Amt,Dlr_Mrkp_Max_Amt,Actl_Prc_Amt,Cntrct_ID) ";
						$sqlString .= "values (?,?,?,?,?,?,?,?,?,?)";

						$stmt = mysqli_prepare($link, $sqlString);

						/* Bind variables to parameters */
						$val1 = $row["Sml_Goods_Gnrc_Prcg_ID"];
						$val2 = $row["Item_Cat_Type_Cd"];
						$val3 = $row["Item_Cat_Type_Desc"];
						$val4 = $_POST["quantity_".$row["Sml_Goods_Gnrc_Prcg_ID"]];
						$val5 = $Agt_Cst_Amt;
						$val6 = $Agt_Comssn_Amt;
						$val7 = $Dlr_Cst_Amt;
						$val8 = $Dlr_Mrkp_Amt;
						$val9 = ($Dlr_Cst_Amt+$Dlr_Mrkp_Amt)*$_POST["quantity_".$row["Sml_Goods_Gnrc_Prcg_ID"]];
						$val10 = $warrantyID;

						mysqli_stmt_bind_param($stmt, "issiiiiiii", $val1,$val2,$val3,$val4,$val5,$val6,$val7,$val8,$val9,$val10);

						// Call our routine to update the Small Goods TOTAL columns in the Cntrct table, based on the changes
						//  to small good here.
						//  Allow Actl column to be updated with 'Y' flag
						//$totalSGUpdateResult = updateWarrantySmallGoodsTotals($link,$warrantyID,"Y");

						/* Execute the statement */
						$result = mysqli_stmt_execute($stmt);
					}

				}

				/* Execute the statement */
				//$result = mysqli_stmt_execute($stmt);

			}
		}

		// Call our routine to update the Small Goods TOTAL columns in the Cntrct table, based on the changes
		//  to small good here.
		//  Allow Actl column to be updated with 'Y' flag
		$totalSGUpdateResult = updateWarrantySmallGoodsTotals($link,$warrantyID,"Y");

		// Call our function to updated the TOTALS columns in the Cntrct table, which is the sum
		//  of base + add-on + small goods.  Need to refresh these totals whenever changes are made
		$totalUpdateResult = updateWarrantyTotals($link,$warrantyID,"Y");


		//Get customer and vehicle info
		$query = "SELECT * FROM Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Cntrct_ID=".$warrantyID." AND
		c.Veh_ID = v.Veh_ID AND
		c.Created_Warranty_ID is NULL AND
		c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND
		cd.Cntrct_Type_Cd='WQ' AND
		cd.Is_Deleted_Flg != 'Y' ";

		$result = $link->query($query);

		$result = mysqli_fetch_assoc($result);
		$customerName = $result["Cstmr_Nme"];
		$agreementDate = $result["Contract_Date"];
		$vin = $result["Veh_Id_Nbr"];
		$wrap_Flg = $result["Wrap_Flg"];


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
		$totalQuantitySubmitted = 0;

		$agreementStartDate = strtotime($agreementDate);
		$endDate = date('Y-m-d', strtotime('+'.$coverage_terms.' years', $agreementStartDate));
		$endDate = date('Y-m-d', strtotime('-1 day', strtotime($endDate)));

		// create new PDF document

		// Close and output PDF document
		// This method has several options, check the source code documentation for more information.

		$pdfFileName = str_replace(" ", "_", $customerName) . '_' . str_replace(" ", "_", $warrantyID) . '_' . time() . '.pdf';

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

				foreach($smallGoodsResult as $item){
					if($_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']] > 0)
					{
						if($item["Item_Cat_Type_Cd"] == 'A')
						{
							$data['QUANTITY SUBMITTED A'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//$data['QUANTITY APPROVED A'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'B')
						{
							$data['QUANTITY SUBMITTED B'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//$data['QUANTITY APPROVED B'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'C')
						{
							$data['QUANTITY SUBMITTED C'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							// $data['QUANTITY APPROVED C'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'D')
						{
							$data['QUANTITY SUBMITTED D'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//$data['QUANTITY APPROVED D'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'E')
						{
							$data['QUANTITY SUBMITTED E'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//$data['QUANTITY APPROVED E'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'F')
						{
							$data['QUANTITY SUBMITTED F'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//$data['QUANTITY APPROVED F'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
										else if($item["Item_Cat_Type_Cd"] == 'G')
						{
							$data['QUANTITY SUBMITTED G'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//$data['QUANTITY APPROVED G'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
										else if($item["Item_Cat_Type_Cd"] == 'H')
						{
							$data['QUANTITY SUBMITTED I'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//$data['QUANTITY APPROVED I'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'I')
						{
							$data['QUANTITY SUBMITTED J'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							///$data['QUANTITY APPROVED J'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}else if($item["Item_Cat_Type_Cd"] == 'J')
						{
							$data['QUANTITY SUBMITTED K'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//$data['QUANTITY APPROVED K'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'K')
						{
							$data['QUANTITY SUBMITTED L'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							// $data['QUANTITY APPROVED L'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'L')
						{
							$data['QUANTITY SUBMITTED M'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//$data['QUANTITY APPROVED M'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'M')
						{
							$data['QUANTITY SUBMITTED N'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							// $data['QUANTITY APPROVED N'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'N')
						{
							$data['QUANTITY SUBMITTED O'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//$data['QUANTITY APPROVED O'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'O')
						{
							$data['QUANTITY SUBMITTED P'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							// $data['QUANTITY APPROVED P'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'P')
						{
							$data['QUANTITY SUBMITTED Q'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							// $data['QUANTITY APPROVED Q'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'Q')
						{
							$data['QUANTITY SUBMITTED R'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//$data['QUANTITY APPROVED R'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'R')
						{
							$data['QUANTITY SUBMITTED S'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							// $data['QUANTITY APPROVED S'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'S')
						{
							$data['QUANTITY SUBMITTED T'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							// $data['QUANTITY APPROVED T'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'T')
						{
							$data['QUANTITY SUBMITTED U'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//  $data['QUANTITY APPROVED U'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'U')
						{
							$data['QUANTITY SUBMITTED V'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							// $data['QUANTITY APPROVED V'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'V')
						{
							$data['QUANTITY SUBMITTED W'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//$data['QUANTITY APPROVED W'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'W')
						{
							$data['QUANTITY SUBMITTED X'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							//  $data['QUANTITY APPROVED X'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}else if($item["Item_Cat_Type_Cd"] == 'X')
						{
							$data['QUANTITY SUBMITTED Y'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							// $data['QUANTITY APPROVED Y'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'Y')
						{
							$data['QUANTITY SUBMITTED Z'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							// $data['QUANTITY APPROVED Z'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						else if($item["Item_Cat_Type_Cd"] == 'Z')
						{
							$data['QUANTITY SUBMITTED H'] = $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
							// $data['QUANTITY APPROVED H'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
						}
						$totalAmount += $item['Dlr_Cst_Amt'] * $_POST['quantity_'.$item['Sml_Goods_Gnrc_Prcg_ID']];
					}
				}
						$data['Estimated Items Cost'] = '$'.$totalAmount;
						$data['Estimated Items Submitted'] = $totalQuantitySubmitted;

				$pdf = new GeneratePDF;
				$pdf->generateSgSummary($data,  $pdfFileName);



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

		// End PDF Code here


			// Rewrite the PDF
			$pdfResult = createWarrantyPDF($link,$warrantyID,"Y",$wrap_Flg);

    }


	// Redirect to next form
    header("location: warranty_pending.php?showQuotes=Y");
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
                                <h4 class="card-title">Small Goods Summary Worksheet</h4>
                                <?php
									if($errorMessage!=""){
										echo "<h5>Notice: ".$errorMessage."</h5>";
									}
                                ?>
                            </div>
                            <div class="card-header text-center">
                                <h5>(<a href="warranty_pending.php?showQuotes=Y">Return To Pending Quote List</a>)</h5>
							</div>
                            <div class="card-body">
                                <div class="basic-form dealer-form">
                                    <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>
                                    <form name="smallGoodsForm" method="POST" action="">
                                    	<input type="hidden" name="warrantyID" value="<?php echo $warrantyID;?>"/>

                                        <div class="form-row">
                                            <div class="form-group col-md-12">
                                                <label>Enter quantities of each item: </label>
												<style>
													.alnright { text-align: right; }
													table td {
													  padding: 3px;
													}
												</style>
												<table border="1" cellpadding="5" cellspacing="5"  class='alnright'>
													<tr style="background-color:#201F58;color:#FFFFFF;font-weight:bold;text-align:center;">
														<td>Item Type</td>
														<td>Tools &amp; Small Goods Items</td>
														<td>Quantity Submitted</td>
														<td>Cost Per Item</td>
														<td>Total Cost</td>
													</tr>
													<?php

													// Look up the available small goods items
													$query = "SELECT * FROM Sml_Goods_Gnrc_Prcg ORDER BY Item_Cat_Type_Cd ASC";
													$smallGoodsResult = $link->query($query);

													$addendumTotal = 0;
													$extendedTotal = 0;
													if (mysqli_num_rows($smallGoodsResult) > 0) {
													  // output data of each row
													  $loopCounter = 0;
													  while($row = mysqli_fetch_assoc($smallGoodsResult)) {
														$loopCounter++;

														if(isset($currentSelectionsArray[$row["Item_Cat_Type_Cd"]])){
															$valuePlaceholder = $currentSelectionsArray[$row["Item_Cat_Type_Cd"]];
														}else{
															$valuePlaceholder = "0";
														}
														?>
														<tr>
															<td><?php echo $row["Item_Cat_Type_Cd"]; ?></td>
															<td><?php echo $row["Item_Cat_Type_Desc"]; ?></td>
															<td>
																<select style="width:150px;" name="quantity_<?php echo $row["Sml_Goods_Gnrc_Prcg_ID"]; ?>">
																	<?php
																	for($i=0;$i<=30;$i++){
																	?>
																		<option value="<?php echo $i;?>" <?php if($i==$valuePlaceholder){echo "selected";}?>><?php echo $i;?></option>
																	<?php
																	}
																	?>
																</select>
																<!---
																<input type="text" value="<?php echo $valuePlaceholder;?>" name="quantity_<?php echo $row["Sml_Goods_Gnrc_Prcg_ID"]; ?>"/>
																--->
															</td>
															<td>$<?php echo $row["MSRP_Amt"]; ?></td>
															<td>$<?php echo ($row["MSRP_Amt"] * $valuePlaceholder); ?></td>
														</tr>

														<?php

														$addendumTotal += $row["MSRP_Amt"];
														$extendedTotal += ($row["MSRP_Amt"] * $valuePlaceholder);

													  }
													} else {
													?>
													<tr>
														<td colspan="4">
															<p>No Small Goods Found</p>
														</td>
													</tr>
													<?php
													}
													?>
													<tr style="background-color:#201F58;color:#FFFFFF;font-weight:bold;">
														<td>Totals</td>
														<td>&nbsp;</td>
														<td>&nbsp;</td>
														<td>&nbsp;</td>
														<td>$<?php echo $extendedTotal; ?></td>
													</tr>
												</table>
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

<script src="./js/jSignature/jSignature.min.js"></script>
<script src="./js/jSignature/jSignInit.js"></script>

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

</body>
</html>