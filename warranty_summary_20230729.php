<?php
//
// File: warranty_summary.php
// Author: Charles Parry
// Date: 7/24/2022
//
//

// Turn on error reporting
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
//error_reporting(E_ALL);


$pageBreadcrumb = "Warranty Summary Worksheet";
$pageTitle = "Warranty Summary Worksheet";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";


// PDF function
require_once "lib/pdfHelper.php";


// Variables.
$dealerID = "";
$smallGoodsCoverageID = "";
$smallGoodsPricingID = "";
$warrantyID = "";
$vehicleID = "";
$quantity = 1;
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

$Dlr_Mrkp_Max_Amt = 0;
$Dlr_Mrkp_Actl_Amt = 0;
$Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt = 0;
$Addl_Dlr_Mrkp_Actl_Amt = 0;
$Dlr_Sml_Goods_Max_Mrkp_Tot_Amt = 0;
$Tot_Dlr_Mrkp_Max_Amt = 0;

$Wrap_Flg = "N";

$Addl_Dlr_Mrkp_Actl_APU_Amt = 0;
$Addl_Dlr_Mrkp_Actl_AEP_Amt = 0;
$Addl_Dlr_Mrkp_Actl_AER_Amt = 0;


$isQuote = "";

$Sales_Agt_Cost_Amt = 0;


$form_err    = "";

$currentSelectionsArray = array();
$loopCounter=0;


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
}


// See if we are specifying a warrantyID in the URL request.
if(isset($_GET["warrantyID"])){
	$warrantyID = $_GET["warrantyID"];
}else{
    header("location: warranty_pending.php");
    exit;
}


// SECURITY make sure this dealer may edit this warranty
$securityCheck = dealerOwnsWarranty($link,$dealerID,$warrantyID);
if(!$securityCheck){
    header("location: warranty_pending.php");
    exit;
}


if(isset($_GET["isQuote"])){
	$isQuote = $_GET["isQuote"];
}



// Process form data when form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$_SESSION["errorMessage"]="";
	$newQuantity = 1;

	if(isset($_POST["quantity"]) && is_numeric($_POST["quantity"])){
		$newQuantity = $_POST["quantity"];
	}

	// Get the 'actual' markup values that were just set on the form
	if (isset($_POST["Dlr_Mrkp_Actl_Amt"])) {
		$Dlr_Mrkp_Actl_Amt = trim($_POST["Dlr_Mrkp_Actl_Amt"]);
		if(!is_numeric($Dlr_Mrkp_Actl_Amt)){
			$_SESSION["errorMessage"] = "Supplied value for Base Coverage Markup is not numeric.  Please try again.";
		}
	}

	if (isset($_POST["Addl_Dlr_Mrkp_Actl_APU_Amt"])) {
		$Addl_Dlr_Mrkp_Actl_APU_Amt = trim($_POST["Addl_Dlr_Mrkp_Actl_APU_Amt"]);
		if(!is_numeric($Addl_Dlr_Mrkp_Actl_APU_Amt)){
			$_SESSION["errorMessage"] = "Supplied value for APU Markup is not numeric.  Please try again.";
		}
	}

	if (isset($_POST["Addl_Dlr_Mrkp_Actl_AEP_Amt"])) {
		$Addl_Dlr_Mrkp_Actl_AEP_Amt = trim($_POST["Addl_Dlr_Mrkp_Actl_AEP_Amt"]);
		if(!is_numeric($Addl_Dlr_Mrkp_Actl_AEP_Amt)){
			$_SESSION["errorMessage"] = "Supplied value for AEP Markup is not numeric.  Please try again.";
		}
	}

	if (isset($_POST["Addl_Dlr_Mrkp_Actl_AER_Amt"])) {
		$Addl_Dlr_Mrkp_Actl_AER_Amt = trim($_POST["Addl_Dlr_Mrkp_Actl_AER_Amt"]);
		if(!is_numeric($Addl_Dlr_Mrkp_Actl_AER_Amt)){
			$_SESSION["errorMessage"] = "Supplied value for Aerial Markup is not numeric.  Please try again.";
		}
	}

	if (isset($_POST["Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt"])) {
		$Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt = trim($_POST["Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt"]);
		if(!is_numeric($Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt)){
			$_SESSION["errorMessage"] = "Supplied value for Small Goods Markup is not numeric.  Please try again.";
		}
	}



	if(isset($_POST["isQuote"])){
		$isQuote = $_POST["isQuote"];
	}

/*
print_r($_POST);
echo "warrantyID=".$warrantyID;
echo "isQuote=".$isQuote;
die();
*/

	if($_SESSION["errorMessage"]!=""){
		if($isQuote=="Y"){
			header("location: warranty_summary.php?warrantyID=".$warrantyID."&isQuote=Y");
		}else{
			header("location: warranty_summary.php?warrantyID=".$warrantyID);
		}
		exit;
	}



	// Get our MAX values to be sure we are not overstepping those bounds
	$query = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='AEP'";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$Dlr_Mrkp_Max_AEP_Amt = $row["Dlr_Mrkp_Max_Amt"];

	$query = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='APU'";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$Dlr_Mrkp_Max_APU_Amt = $row["Dlr_Mrkp_Max_Amt"];

	$query = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='AER'";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$Dlr_Mrkp_Max_AER_Amt = $row["Dlr_Mrkp_Max_Amt"];

	$query = "SELECT * FROM Cntrct WHERE Cntrct_ID=".$warrantyID;
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$Dlr_Mrkp_Max_Amt = $row["Dlr_Mrkp_Max_Amt"];
	$Dlr_Sml_Goods_Max_Mrkp_Tot_Amt = $row["Dlr_Sml_Goods_Max_Mrkp_Tot_Amt"];
	$quantity = $row["Quantity"];


/*
echo "Dlr_Mrkp_Max_APU_Amt=".$Dlr_Mrkp_Max_APU_Amt;
echo "<br />Addl_Dlr_Mrkp_Actl_APU_Amt=".$Addl_Dlr_Mrkp_Actl_APU_Amt;
die();
*/

	// Now check our values, that they are not over the MAX
	//  If they are, then return an error.
	if($Dlr_Mrkp_Actl_Amt>$Dlr_Mrkp_Max_Amt){
		$_SESSION["errorMessage"] = "Base Coverage Markup is over Max.  Please adjust below $".number_format($Dlr_Mrkp_Max_Amt,0);
	}
	if($Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt>$Dlr_Sml_Goods_Max_Mrkp_Tot_Amt){
		$_SESSION["errorMessage"] = "Small Goods Markup is over Max.  Please adjust below $".number_format($Dlr_Sml_Goods_Max_Mrkp_Tot_Amt,0);
	}
	if($Addl_Dlr_Mrkp_Actl_AEP_Amt>$Dlr_Mrkp_Max_AEP_Amt){
		$_SESSION["errorMessage"] = "AEP Markup is over Max.  Please adjust below $".number_format($Dlr_Mrkp_Max_AEP_Amt,0);
	}
	if($Addl_Dlr_Mrkp_Actl_APU_Amt>$Dlr_Mrkp_Max_APU_Amt){
		$_SESSION["errorMessage"] = "APU Markup is over Max.  Please adjust below $".number_format($Dlr_Mrkp_Max_APU_Amt,0);
	}
	if($Addl_Dlr_Mrkp_Actl_AER_Amt>$Dlr_Mrkp_Max_AER_Amt){
		$_SESSION["errorMessage"] = "Aerial Markup is over Max.  Please adjust below $".number_format($Dlr_Mrkp_Max_AER_Amt,0);
	}

	if($_SESSION["errorMessage"]!=""){
		if($isQuote=="Y"){
			header("location: warranty_summary.php?warrantyID=".$warrantyID."&isQuote=Y");
		}else{
			header("location: warranty_summary.php?warrantyID=".$warrantyID);
		}
		exit;
	}


	/* Prepare an UPDATE statement to update a Cntrct entry for this Warranty based on these markup values */
	$stmt = mysqli_prepare($link, "UPDATE Cntrct SET Dlr_Mrkp_Actl_Amt=?, Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt=?,
	                               Addl_Dlr_Mrkp_Actl_APU_Amt=?,Addl_Dlr_Mrkp_Actl_AEP_Amt=?,
	                               Addl_Dlr_Mrkp_Actl_AER_Amt=?, Quantity=? WHERE Cntrct_ID=?");

	$val1 = $Dlr_Mrkp_Actl_Amt;
	$val2 = $Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt;
	$val3 = $Addl_Dlr_Mrkp_Actl_APU_Amt;
	$val4 = $Addl_Dlr_Mrkp_Actl_AEP_Amt;
	$val5 = $Addl_Dlr_Mrkp_Actl_AER_Amt;
	$val6 = $newQuantity;
	$val7 = $warrantyID;

//echo "Dlr_Mrkp_Actl_Amt=".$Dlr_Mrkp_Actl_Amt;



	mysqli_stmt_bind_param($stmt, "iiiiiii", $val1, $val2, $val3, $val4, $val5, $val6, $val7);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);


	// Update the totals
	$stmt = mysqli_prepare($link, "UPDATE Cntrct SET MSRP_Amt=(Dlr_Mrkp_Actl_Amt+Dlr_Cost_Amt),
	                               Sml_Goods_Tot_Amt=(Dlr_Sml_Goods_Cst_Tot_Amt+Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt),
	                               Addl_MSRP_Amt=(Addl_Dlr_Cost_Amt+Addl_Dlr_Mrkp_Actl_APU_Amt+Addl_Dlr_Mrkp_Actl_AEP_Amt+Addl_Dlr_Mrkp_Actl_AER_Amt),
	                               Addl_Dlr_Mrkp_Actl_Amt=(Addl_Dlr_Mrkp_Actl_APU_Amt+Addl_Dlr_Mrkp_Actl_AEP_Amt+Addl_Dlr_Mrkp_Actl_AER_Amt),
	                               Tot_Dlr_Mrkp_Act_Amt=(Dlr_Mrkp_Actl_Amt+
	                               Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt+
	                               Addl_Dlr_Mrkp_Actl_APU_Amt+Addl_Dlr_Mrkp_Actl_AEP_Amt+Addl_Dlr_Mrkp_Actl_AER_Amt
	                               ),
	                               Tot_MSRP_Amt=(Dlr_Mrkp_Actl_Amt+Dlr_Cost_Amt+
	                               Dlr_Sml_Goods_Cst_Tot_Amt+Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt+
	                               Addl_Dlr_Cost_Amt+Addl_Dlr_Mrkp_Actl_APU_Amt+Addl_Dlr_Mrkp_Actl_AEP_Amt+Addl_Dlr_Mrkp_Actl_AER_Amt
	                               )
	                               WHERE Cntrct_ID=?");

	mysqli_stmt_bind_param($stmt, "i", $warrantyID);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);


	// Update the Small Goods values.
    //$totalSGUpdateResult = updateWarrantySmallGoodsTotals($link,$warrantyID);


	// Now, check for the value of 'term' and 'coverage' and if they have changed from
	//  the starting values
	$query = "SELECT Cntrct_Lvl_Desc,Cntrct_Term_Mnths_Nbr,Veh_Type_Nbr,c.Cntrct_Dim_ID, cd.Wrap_Flg FROM
			  Cntrct c, Cntrct_Dim cd, Veh v WHERE c.Cntrct_ID=".$warrantyID." AND
	          c.Cntrct_Dim_ID=cd.Cntrct_Dim_ID AND v.Veh_ID = c.Veh_ID";

	$cntrctResult = $link->query($query);

	$numRows = mysqli_num_rows($cntrctResult);
	if ($numRows > 0) {
		$row = mysqli_fetch_assoc($cntrctResult);
		$Cntrct_Lvl_Desc = $row["Cntrct_Lvl_Desc"];
		$Cntrct_Term_Mnths_Nbr = $row["Cntrct_Term_Mnths_Nbr"];
		$Veh_Type_Nbr = $row["Veh_Type_Nbr"];
		$Cntrct_Dim_ID = $row["Cntrct_Dim_ID"];
		$Wrap_Flg = $row["Wrap_Flg"];
	}else{
		// Fail
		header("location: warranty_summary.php?warrantyID=".$warrantyID."&isQuote=Y");
	}


	// Sanitize flag value.
	if($Wrap_Flg!="Y"){
		$Wrap_Flg="N";
	}



	// Get the new term and coverage values from the form
	if (isset($_POST["newTerm"])) {
		$newTerm = trim($_POST["newTerm"]);
		if(!is_numeric($newTerm)){
			$_SESSION["errorMessage"] = "Supplied value for New Term is not numeric.  Please try again.";
			header("location: warranty_summary.php?warrantyID=".$warrantyID."&isQuote=Y");
		}
	}else{
		$newTerm = "";
	}


	// Flag for redirect behavior
	$gotoWarrantyPending="N";

	if (isset($_POST["newCoverage"])) {
		$newCoverage = trim($_POST["newCoverage"]);
		if($newCoverage=="S"){
			$newCoverageExpanded = 'Squad';
		}else if($newCoverage=="B"){
			$newCoverageExpanded = 'Battalion';
		}else{
			$newCoverageExpanded = "";
		}
	}else{
		$newCoverage = "";
		$newCoverageExpanded = "";
	}

	// If term or coverage have changed, then create a copy of the quote
	if(($newTerm!=$Cntrct_Term_Mnths_Nbr) || ($newCoverageExpanded!=$Cntrct_Lvl_Desc)){
//echo "copying quote";
		// Remove this feature for now, cparry 5/15/2023.
		//$copyResult = copyQuote($link,$warrantyID,$newCoverageExpanded,$newTerm);
//echo "<br/>done, result=".$copyResult;
//die();

//echo "found structure difference";
//die();

		$stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim SET Cntrct_Lvl_Cd=?,Cntrct_Lvl_Desc=?,Cntrct_Term_Mnths_Nbr=?
									   WHERE Cntrct_Dim_ID=?");

		$val1 = $newCoverage;
		$val2 = $newCoverageExpanded;
		$val3 = $newTerm;
		$val4 = $Cntrct_Dim_ID;

		mysqli_stmt_bind_param($stmt, "ssii", $val1, $val2, $val3, $val4);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Check if wholesale account
		$query = "SELECT Wholesale_Flg FROM Cntrct c, Acct a WHERE c.Cntrct_ID=" . $warrantyID. " AND
		          c.Mfr_Acct_ID=a.Acct_ID;";
		$result = $link->query($query);
		if ($result) {
			$row = $result->fetch_assoc();
			if($row["Wholesale_Flg"]=="Y"){
				$wholesale_flg = $row["Wholesale_Flg"];
			}else{
				$wholesale_flg = "N";
			}
		} else {
			$wholesale_flg = "N";
		}


		// Look up the base values from Wrnty_Std_Prcg based on term, type and tier
		if($Wrap_Flg== 'N')
		{
			$warrantyBasePricingResult = selectWarrantyBasePricing($link, $newTerm, $Veh_Type_Nbr, $newCoverage, $wholesale_flg);

		} else{
			$warrantyBasePricingResult = selectwrapWarrantyBasePricing($link,$newTerm,$Veh_Type_Nbr, "B", $wholesale_flg);
		}


		//$warrantyBasePricingResult = selectWarrantyBasePricing($link, $newTerm, $Veh_Type_Nbr, $newCoverage);
		$row = mysqli_fetch_assoc($warrantyBasePricingResult);

		$Sales_Agt_Cost_Amt = $row["Sales_Agt_Cost_Amt"];
		$Sales_Agt_Commission_Amt = $row["Sales_Agt_Commission_Amt"];
		$Dlr_Cost_Amt = $row["Dlr_Cost_Amt"];
		$Dlr_Mrkp_Max_Amt = $row["Dlr_Mrkp_Max_Amt"];
		$Dlr_Mrkp_Actl_Amt = $row["Dlr_Mrkp_Max_Amt"];
		$MSRP_Amt = $row["MSRP_Amt"];

		$stmt = mysqli_prepare($link, "UPDATE Cntrct SET Sales_Agt_Cost_Amt=?, Sales_Agt_Commission_Amt=?,
									   Dlr_Cost_Amt=?, Dlr_Mrkp_Max_Amt=?, Dlr_Mrkp_Actl_Amt=?, MSRP_Amt=? WHERE
									   Cntrct_ID=?");

		$val1 = $Sales_Agt_Cost_Amt;
		$val2 = $Sales_Agt_Commission_Amt;
		$val3 = $Dlr_Cost_Amt;
		$val4 = $Dlr_Mrkp_Max_Amt;
		$val5 = $Dlr_Mrkp_Actl_Amt;
		$val6 = $MSRP_Amt;
		$val7 = $warrantyID;

		mysqli_stmt_bind_param($stmt, "iiiiiii", $val1, $val2, $val3, $val4, $val5, $val6, $val7);
		$result = mysqli_stmt_execute($stmt);


		// Update the totals
		$stmt = mysqli_prepare($link, "UPDATE Cntrct SET MSRP_Amt=(Dlr_Mrkp_Actl_Amt+Dlr_Cost_Amt),
									   Sml_Goods_Tot_Amt=(Dlr_Sml_Goods_Cst_Tot_Amt+Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt),
									   Addl_MSRP_Amt=(Addl_Dlr_Cost_Amt+Addl_Dlr_Mrkp_Actl_APU_Amt+Addl_Dlr_Mrkp_Actl_AEP_Amt+Addl_Dlr_Mrkp_Actl_AER_Amt),
									   Addl_Dlr_Mrkp_Actl_Amt=(Addl_Dlr_Mrkp_Actl_APU_Amt+Addl_Dlr_Mrkp_Actl_AEP_Amt+Addl_Dlr_Mrkp_Actl_AER_Amt),
									   Tot_Dlr_Mrkp_Act_Amt=(Dlr_Mrkp_Actl_Amt+
									   Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt+
									   Addl_Dlr_Mrkp_Actl_APU_Amt+Addl_Dlr_Mrkp_Actl_AEP_Amt+Addl_Dlr_Mrkp_Actl_AER_Amt
									   ),
									   Tot_MSRP_Amt=(Dlr_Mrkp_Actl_Amt+Dlr_Cost_Amt+
									   Dlr_Sml_Goods_Cst_Tot_Amt+Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt+
									   Addl_Dlr_Cost_Amt+Addl_Dlr_Mrkp_Actl_APU_Amt+Addl_Dlr_Mrkp_Actl_AEP_Amt+Addl_Dlr_Mrkp_Actl_AER_Amt
									   )
									   WHERE Cntrct_ID=?");

		mysqli_stmt_bind_param($stmt, "i", $warrantyID);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


		//$totalUpdateResult = updateWarrantyTotals($link,$warrantyID);


		$gotoWarrantyPending="Y";

	}


	// Rewrite the PDF - switch on wrap flag
	// cparry 6/8/2023.
	$pdfResult = createWarrantyPDF($link,$warrantyID,$isQuote,$Wrap_Flg);




	// Lastly, see if 'quantity' is > 1, and if so determine the extended prices
	// Save our quantity value received from the page.
	if(isset($_POST["quantity"]) && is_numeric($_POST["quantity"])){
		$newQuantity = $_POST["quantity"];

		// If we got a quantity N that is greater than 1, then we want to create N-1 copies of this quote.
		if($newQuantity>1){
			for($i=1;$i<$newQuantity;$i++){

				// Remove this feature for now, cparry 5/15/2023.
				//$newCntrctID = copyQuote($link,$warrantyID);
//echo "creating new quote, contractID = ".$newCntrctID;
//echo "<br />i = ".$i;
//echo "<br />newQuantity = ".$newQuantity;
			}
		}
//die();

		// Now set newQuantity back to 1, since we do not actually want to increment it in the Cntrct table.
		//$newQuantity = 1;

		$gotoWarrantyPending="Y";

	}


	if($gotoWarrantyPending=="Y"){
		//header("location: warranty_pending.php?showQuotes=Y");
		//die();
	}




	if($isQuote=="Y"){
		header("location: warranty_summary.php?warrantyID=".$warrantyID."&isQuote=Y");
	}else{
		header("location: warranty_summary.php?warrantyID=".$warrantyID);
	}
    exit;

}





// See if we have data for this warranty contract already saved
if($warrantyID!=""){

	$updateResult = updateWarrantyTotals($link,$warrantyID);

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
		$Wrap_Flg= $row["Wrap_Flg"];
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

		$quantity= $row["Quantity"];

	}


}



require_once("includes/header.php");


if(isset($_SESSION["errorMessage"]) && ($_SESSION["errorMessage"]!="")){
	$errorMessage = $_SESSION["errorMessage"];
	$_SESSION["errorMessage"]="";
}else{
	$errorMessage = "";
}


// Additional Standard Pricing default values
$aepQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='AEP'";

$aepResult = $link->query($aepQuery);

$aepRows = mysqli_num_rows($aepResult);
if ($aepRows==1) {
	$aepRow = mysqli_fetch_assoc($aepResult);
	$aep_Dlr_Cost_Amt = $aepRow["Dlr_Cost_Amt"];
	$aep_Dlr_Mrkp_Max_Amt = $aepRow["Dlr_Mrkp_Max_Amt"];
	$aep_MSRP_Amt = $aepRow["MSRP_Amt"];
}else{
	$aep_Dlr_Cost_Amt = 0;
	$aep_Dlr_Mrkp_Max_Amt = 0;
	$aep_MSRP_Amt = 0;
}


$apuQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='APU'";

$apuResult = $link->query($apuQuery);

$apuRows = mysqli_num_rows($apuResult);
if ($apuRows > 0) {
	$apuRow = mysqli_fetch_assoc($apuResult);
	$apu_Dlr_Cost_Amt = $apuRow["Dlr_Cost_Amt"];
	$apu_Dlr_Mrkp_Max_Amt = $apuRow["Dlr_Mrkp_Max_Amt"];
	$apu_MSRP_Amt = $apuRow["MSRP_Amt"];
}else{
	$apu_Dlr_Cost_Amt = 0;
	$apu_Dlr_Mrkp_Max_Amt = 0;
	$apu_MSRP_Amt = 0;
}


$aerQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='AER'";

$aerResult = $link->query($aerQuery);

$aerRows = mysqli_num_rows($aerResult);
if ($aerRows > 0) {
	$aerRow = mysqli_fetch_assoc($aerResult);
	$aer_Dlr_Cost_Amt = $aerRow["Dlr_Cost_Amt"];
	$aer_Dlr_Mrkp_Max_Amt = $aerRow["Dlr_Mrkp_Max_Amt"];
	$aer_MSRP_Amt = $aerRow["MSRP_Amt"];
}else{
	$aer_Dlr_Cost_Amt = 0;
	$aer_Dlr_Mrkp_Max_Amt = 0;
	$aer_MSRP_Amt = 0;
}


$oldQuery = "SELECT * FROM Addl_Std_Prcg WHERE Addl_Type_Cd='OLD'";

$oldResult = $link->query($oldQuery);

$oldRows = mysqli_num_rows($oldResult);
if ($oldRows > 0) {
	$oldRow = mysqli_fetch_assoc($oldResult);
	$old_Dlr_Cost_Amt = $oldRow["Dlr_Cost_Amt"];
	$old_Dlr_Mrkp_Max_Amt = $oldRow["Dlr_Mrkp_Max_Amt"];
	$old_MSRP_Amt = $oldRow["MSRP_Amt"];
}else{
	$old_Dlr_Cost_Amt = 0;
	$old_Dlr_Mrkp_Max_Amt = 0;
	$old_MSRP_Amt = 0;
}




if(!is_numeric($quantity) || $quantity<1){
	$quantity=1;
}


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
                                <h4 class="card-title"><?php if($isQuote=="Y"){ echo "Quote";}else{ echo "Warranty";}?> Summary</h4>
                                <?php
									if($errorMessage!=""){
										echo "<h5>Notice: ".$errorMessage."</h5>";
									}
                                ?>
                            </div>
                            <div class="card-header text-center">
								<?php if($isQuote=="Y"){ ?>
	                                <h5>(<a href="warranty_pending.php?showQuotes=Y">Return to Quote List</a>)</h5>
								<?php }else{ ?>
	                                <h5>(<a href="warranty_pending.php">Return to Warranty List</a>)</h5>
								<?php } ?>
							</div>
							<?php
							if($errorMessage!=""){
							?>
								<div class="card-header text-center">
									<span style="color:red;">ERROR: <?php echo $errorMessage;?></span>
								</div>
							<?php
							}
							?>
                            <div class="card-body">
                                <div class="basic-form dealer-form">
                                    <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>

									<div class="form-row">
										<div class="form-group col-md-12">
											<style>
												.alnright { text-align: right; }
												table td {
												  padding: 3px;
												}
											</style>
											<?php
												$altColor = "lightgrey";
												$nextColor = "white";
											?>
                                    <form name="markupForm" id="markupForm" method="POST" action="">
											<input type="hidden" id="warrantyID" value="<?php echo $warrantyID; ?>">
											<input type="hidden" id="isQuote" name="isQuote" value="<?php echo $isQuote; ?>">
											<button type="submit" style="align:right;" id="markupFormSubmit" class="btn btn-primary">Update</button>
											<table border="1" cellpadding="5" cellspacing="5" class='alnright'>
												<tr style="background-color:#201F58;color:#FFFFFF; font-weight:bold;text-align:center;">
													<td>VIN</td>
													<td>Vehicle Type</td>
													<td>Term (Years)</td>
													<td>Coverage</td>
													<td>Dealer Cost</td>
													<td>Dealer Markup</td>
													<td>Dealer Max Markup</td>
													<td>MSRP</td>
													<td>Quantity</td>
													<td>Extended MSRP</td>
												</tr>
												<tr style="background-color:<?php echo $nextColor;?>">
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<script language="javascript">
														var pricingArr = new Array(); // Dimensions: [type][tier][term][Dlr_Cost_Amt|Dlr_Mrkp_Max_Amt|MSRP_Amt

pricingArr[2] = new Array();
pricingArr[2]['S'] = 123;
pricingArr[2]['B'] = 456;
//alert(pricingArr[2]['B']);
													<?php
														// Get the standard pricing for all combos to support the dynamic fields
														$pricingQuery = "SELECT * FROM Wrnty_Std_Prcg WHERE Veh_Type_Cd=".$Veh_Type_Nbr;

														$pricingResult = $link->query($pricingQuery);

														$pricingRows = mysqli_num_rows($pricingResult);
														if ($pricingRows > 0) {
															while($pricingRow = mysqli_fetch_assoc($pricingResult)){
																$Tier_Type_Cd_local = $pricingRow["Tier_Type_Cd"]; // B or S
																$Cvrg_Term_Yrs_Nbr_local = $pricingRow["Cvrg_Term_Yrs_Nbr"];  // 5, 7, 10
																$Dlr_Cost_Amt_local = $pricingRow["Dlr_Cost_Amt"];
																$Dlr_Mrkp_Max_Amt_local = $pricingRow["Dlr_Mrkp_Max_Amt"];
																$MSRP_Amt_local = $pricingRow["MSRP_Amt"];

																if($Tier_Type_Cd_local == "S"){
																	$Tier_Type_Cd_Int_local = 1;
																}else{
																	$Tier_Type_Cd_Int_local = 2;
																}

																// Build a javascript array
																?>
//														pricingArr[<?php echo $Veh_Type_Nbr;?>][<?php echo $Tier_Type_Cd_Int_local;?>][<?php echo $Cvrg_Term_Yrs_Nbr_local;?>][1] = <?php echo $Dlr_Cost_Amt_local;?>;
//														pricingArr[<?php echo $Veh_Type_Nbr;?>][<?php echo $Tier_Type_Cd_Int_local;?>][<?php echo $Cvrg_Term_Yrs_Nbr_local;?>][2] = <?php echo $Dlr_Mrkp_Max_Amt_local;?>;
//														pricingArr[<?php echo $Veh_Type_Nbr;?>][<?php echo $Tier_Type_Cd_Int_local;?>][<?php echo $Cvrg_Term_Yrs_Nbr_local;?>][3] = <?php echo $MSRP_Amt_local;?>;
																<?php

															}
														}

													?>
//alert(pricingArr[2][1][5][1]);

														function updatePricing() {
														  var newTermValue = document.getElementById("newTerm").value;
														  var newCoverageValue = document.getElementById("newCoverage").value;

														  var dealerCostTemp = pricingArr[<?php echo $Veh_Type_Nbr;?>]["newCoverageValue"][newTermValue]["Dlr_Cost_Amt"];
														  dealerCostField.innerHTML = "$"+(dealerCostTemp).toLocaleString();


														}


														function updateTextField() {
														  var select = document.getElementById("quantityDrop");
														  var msrpField = document.getElementById("msrpField");
														  <?php if($AEP_Flg=="Y"){ ?>
															  var aepField = document.getElementById("aepField");
														  <?php } ?>
														  <?php if($APU_Flg=="Y"){ ?>
															  var apuField = document.getElementById("apuField");
														  <?php } ?>
														  <?php if($Aerial_Flg=="Y"){ ?>
															  var aerField = document.getElementById("aerField");
														  <?php } ?>
														  <?php if($Small_Goods_Pkg_Flg=="Y"){ ?>
															  var smallGoodsField = document.getElementById("smallGoodsField");
														  <?php } ?>
														  <?php if($Old_Flg=="Y"){ ?>
															  var oldField = document.getElementById("oldField");
														  <?php } ?>
														  var totalMSRPField = document.getElementById("totalMSRPField");

														  // Check if dropdown value is numeric
														  if (!isNaN(parseFloat(select.value)) && isFinite(select.value)) {

															  msrpField.innerHTML = "$"+(<?php echo $MSRP_Amt;?>*select.value).toLocaleString();
															  <?php if($AEP_Flg=="Y"){ ?>
																  aepField.innerHTML = "$"+(<?php echo $aep_Dlr_Cost_Amt+$Addl_Dlr_Mrkp_Actl_AEP_Amt;?>*select.value).toLocaleString();
															  <?php } ?>
															  <?php if($APU_Flg=="Y"){ ?>
																  apuField.innerHTML = "$"+(<?php echo $apu_Dlr_Cost_Amt+$Addl_Dlr_Mrkp_Actl_APU_Amt;?>*select.value).toLocaleString();
															  <?php } ?>
															  <?php if($Aerial_Flg=="Y"){ ?>
																  aerField.innerHTML = "$"+(<?php echo $aer_Dlr_Cost_Amt+$Addl_Dlr_Mrkp_Actl_AER_Amt;?>*select.value).toLocaleString();
															  <?php } ?>
															  <?php if($Small_Goods_Pkg_Flg=="Y"){ ?>
																  smallGoodsField.innerHTML = "$"+(<?php echo $Sml_Goods_Tot_Amt;?>*select.value).toLocaleString();
															  <?php } ?>
															  <?php if($Old_Flg=="Y"){ ?>
																  oldField.innerHTML = "$"+(<?php echo $old_MSRP_Amt;?>*select.value).toLocaleString();
															  <?php } ?>
															  totalMSRPField.innerHTML = "$"+(<?php echo $Tot_MSRP_Amt;?>*select.value).toLocaleString();

														  }
														}

														updateTextField();
													</script>

												</tr>
												<?php
													if($nextColor=="white"){$nextColor=$altColor;}else{$nextColor="white";}
												?>
												<tr style="background-color:<?php echo $nextColor;?>">
													<td style="text-align: center;"><?php echo $Veh_Id_Nbr;?></td>
													<td style="text-align: center;"><?php echo $Veh_Type_Nbr;?>
													<?php
													if($Wrap_Flg=="Y"){
													?>
														WRAP
													<?php
													}
													?>
													</td>
													<td style="text-align: center;">
													<?php
													if($Wrap_Flg=="Y"){
													?>
														<select id="newTerm" name="newTerm" onchange="updatePricing()">
															<option value="2" <?php if($Cntrct_Term_Mnths_Nbr==2){echo "selected";}?>>2</option>
															<option value="3" <?php if($Cntrct_Term_Mnths_Nbr==3){echo "selected";}?>>3</option>
															<option value="5" <?php if($Cntrct_Term_Mnths_Nbr==5){echo "selected";}?>>5</option>
														</select>
													<?php
													}else{
													?>
														<select id="newTerm" name="newTerm" onchange="updatePricing()">
															<option value="5" <?php if($Cntrct_Term_Mnths_Nbr==5){echo "selected";}?>>5</option>
															<option value="7" <?php if($Cntrct_Term_Mnths_Nbr==7){echo "selected";}?>>7</option>
															<option value="10" <?php if($Cntrct_Term_Mnths_Nbr==10){echo "selected";}?>>10</option>
														</select>
													<?php
													}
													?>
													</td>
													<td style="text-align: left;">
													<?php
													if($Wrap_Flg=="Y"){
													?>
														Battalion
														<input type="hidden" name="newCoverage" value="B"/>
													<?php
													}else{
													?>
														<select id="newCoverage" name="newCoverage" onchange="updatePricing()">
															<option value="S" <?php if($Cntrct_Lvl_Desc=='Squad'){echo "selected";}?>>Squad</option>
															<option value="B" <?php if($Cntrct_Lvl_Desc=='Battalion'){echo "selected";}?>>Battalion</option>
														</select>
													<?php
													}
													?>
													</td>
													<td><span id="dealerCostField"><?php echo "$".number_format($Dlr_Cost_Amt,0); ?></span></td>
													<td style="text-align:right;">
														<input type="text" size="10" style="text-align:right;" name="Dlr_Mrkp_Actl_Amt" value="<?php echo $Dlr_Mrkp_Actl_Amt;?>"/>
														<!-- Tier Type (Squad,Battalion) -->
														<!---
														<span class="Tier_Type_Span" style="cursor:pointer;"><?php echo "$".number_format($Dlr_Mrkp_Actl_Amt,0); ?></span>
														<input type="hidden" id="Tier_Type_Dlr_Mrkp_Actl_Amt" name="Tier_Type_Dlr_Mrkp_Actl_Amt" value="<?php echo $Dlr_Mrkp_Actl_Amt; ?>">
														--->
													</td>
													<td>
														<span class="Tier_Type_Span" style="cursor:pointer;"><?php echo "$".number_format($Dlr_Mrkp_Max_Amt,0); ?></span>
													</td>
													<td><span class="Tier_Type_MSRP_Amt_Span"><?php echo "$".number_format($MSRP_Amt,0); ?></span></td>
													<td>
														<select name="quantity" id="quantityDrop" onchange="updateTextField()">
														<?php
															for($i=1;$i<50;$i++){
															?>
															<option value="<?php echo $i;?>" <?php if($quantity==$i){echo "selected";}?>><?php echo $i;?></option>
															<?php
															}
														?>
														</select>
													</td>
													<td><span id="msrpField"><?php echo "$".number_format($quantity * $MSRP_Amt,0); ?></span></td>
												</tr>
												<?php if($AEP_Flg=="Y"){
													if($nextColor=="white"){$nextColor=$altColor;}else{$nextColor="white";}

												?>
												<tr style="background-color:<?php echo $nextColor;?>">
													<td></td>
													<td></td>
													<td></td>
													<td style="text-align: left;">AEP</td>
													<td><?php echo "$".number_format($aep_Dlr_Cost_Amt,0); ?></td>
													<td>
														<input type="text" size="10" style="text-align:right;" name="Addl_Dlr_Mrkp_Actl_AEP_Amt" value="<?php echo $Addl_Dlr_Mrkp_Actl_AEP_Amt;?>"/>
													</td>
													<td><?php echo "$".number_format($aep_Dlr_Mrkp_Max_Amt,0); ?></td>
													<td><?php echo "$".number_format(($aep_Dlr_Cost_Amt+$Addl_Dlr_Mrkp_Actl_AEP_Amt),0); ?></td>
													<td></td>
													<td><span id="aepField"><?php echo "$".number_format($quantity*($aep_Dlr_Cost_Amt+$Addl_Dlr_Mrkp_Actl_AEP_Amt),0); ?></span></td>
												</tr>
												<?php } ?>
												<?php if($APU_Flg=="Y"){
													if($nextColor=="white"){$nextColor=$altColor;}else{$nextColor="white";}

												?>
												<tr style="background-color:<?php echo $nextColor;?>">
													<td></td>
													<td></td>
													<td></td>
													<td style="text-align: left;">APU</td>
													<td><?php echo "$".number_format($apu_Dlr_Cost_Amt,0); ?></td>
													<td>
														<input type="text" size="10" style="text-align:right;" name="Addl_Dlr_Mrkp_Actl_APU_Amt" value="<?php echo $Addl_Dlr_Mrkp_Actl_APU_Amt;?>"/>
													</td>
													<td><?php echo "$".number_format($apu_Dlr_Mrkp_Max_Amt,0); ?></td>
													<td><?php echo "$".number_format(($apu_Dlr_Cost_Amt+$Addl_Dlr_Mrkp_Actl_APU_Amt),0); ?></td>
													<td></td>
													<td><span id="apuField"><?php echo "$".number_format($quantity*($apu_Dlr_Cost_Amt+$Addl_Dlr_Mrkp_Actl_APU_Amt),0); ?></span></td>
												</tr>
												<?php } ?>
												<?php if($Aerial_Flg=="Y"){
													if($nextColor=="white"){$nextColor=$altColor;}else{$nextColor="white";}

												?>
												<tr style="background-color:<?php echo $nextColor;?>">
													<td></td>
													<td></td>
													<td></td>
													<td style="text-align: left;">Aerial</td>
													<td><?php echo "$".number_format($aer_Dlr_Cost_Amt,0); ?></td>
													<td>
														<input type="text" size="10" style="text-align:right;" name="Addl_Dlr_Mrkp_Actl_AER_Amt" value="<?php echo $Addl_Dlr_Mrkp_Actl_AER_Amt;?>"/>
													</td>
													<td><?php echo "$".number_format($aer_Dlr_Mrkp_Max_Amt,0); ?></td>
													<td><?php echo "$".number_format(($aer_Dlr_Cost_Amt+$Addl_Dlr_Mrkp_Actl_AER_Amt),0); ?></td>
													<td></td>
													<td><span id="aerField"><?php echo "$".number_format($quantity*($aer_Dlr_Cost_Amt+$Addl_Dlr_Mrkp_Actl_AER_Amt),0); ?></span></td>
												</tr>
												<?php } ?>
												<?php if($Old_Flg=="Y"){
													if($nextColor=="white"){$nextColor=$altColor;}else{$nextColor="white";}

												?>
												<tr style="background-color:<?php echo $nextColor;?>">
													<td></td>
													<td></td>
													<td></td>
													<td style="text-align: left;">Aged Vehicle Surcharge</td>
													<td><?php echo "$".number_format($old_Dlr_Cost_Amt,0); ?></td>
													<td><?php echo "$".number_format($old_Dlr_Mrkp_Max_Amt,0); ?></td>
													<td><?php echo "$".number_format($old_Dlr_Mrkp_Max_Amt,0); ?></td>
													<td><?php echo "$".number_format($old_MSRP_Amt,0); ?></td>
													<td></td>
													<td><span id="oldField"><?php echo "$".number_format($quantity*$old_MSRP_Amt,0); ?></span></td>
												</tr>
												<?php } ?>
												<?php if($Small_Goods_Pkg_Flg=="Y"){
													if($nextColor=="white"){$nextColor=$altColor;}else{$nextColor="white";}

												?>
												<tr style="background-color:<?php echo $nextColor;?>">
													<td></td>
													<td></td>
													<td></td>
													<td style="text-align: left;">Small Goods</td>
													<td><?php echo "$".number_format($Dlr_Sml_Goods_Cst_Tot_Amt,0); ?></td>
													<td>
														<input type="text" size="10" style="text-align:right;" name="Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt" value="<?php echo $Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt;?>"/>
													</td>
													<td><?php echo "$".number_format($Dlr_Sml_Goods_Max_Mrkp_Tot_Amt,0); ?></td>
													<td><?php echo "$".number_format($Sml_Goods_Tot_Amt,0); ?></td>
													<td></td>
													<td><span id="smallGoodsField"><?php echo "$".number_format($quantity*$Sml_Goods_Tot_Amt,0); ?></span></td>
												</tr>
												<?php } ?>
												<tr style="background-color:#201F58;color:#FFFFFF; font-weight:bold;">
													<td>Total Cost</td>
													<td></td>
													<td></td>
													<td></td>
													<td><?php echo "$".number_format($Tot_Dlr_Cost_Amt,0); ?></td>
													<td>
														<?php echo "$".number_format($Tot_Dlr_Mrkp_Act_Amt,0); ?>
														<!---
														<input type="text" size="10" style="text-align:right;" name="Dlr_Mrkp_Act_Amt" value="<?php echo "";?>"/>
														--->
													</td>
													<td>
														<?php echo "$".number_format($Tot_Dlr_Mrkp_Max_Amt,0); ?>
														<!---
														<span class="Tot_Dlr_Mrkp_Act_Amt_Span" style="cursor:pointer;"><?php echo "$".number_format($Tot_Dlr_Mrkp_Act_Amt,0); ?></span>
														<input type="hidden" id="Tot_Dlr_Mrkp_Act_Amt" name="Tot_Dlr_Mrkp_Act_Amt" value="<?php echo $Tot_Dlr_Mrkp_Act_Amt; ?>">
														--->
													</td>
													<td><span class="Tot_MSRP_Amt_Span"><?php echo "$".number_format($Tot_MSRP_Amt,0); ?></span></td>
													<td></td>
													<td><span id="totalMSRPField" class="Tot_MSRP_Amt_Span"><?php echo "$".number_format($quantity*$Tot_MSRP_Amt,0); ?></span></td>
												</tr>
											</table>
											<br /><Br />
<?php
// If we have small goods, display the 'total limit of liability' as a stand-alone line here
if($Small_Goods_Pkg_Flg=="Y"){

	$totalLiabilitySum = 0;
	$query  = "SELECT sum(sggp.Gnrc_Lmt_Of_Lblty_Amt*sgc.Gnrc_Item_Cat_Qty_Cnt) as liability_sum FROM Sml_Goods_Cvge sgc, Sml_Goods_Gnrc_Prcg sggp WHERE sgc.Cntrct_ID=".$warrantyID." AND ";
	$query .= "sgc.Sml_Goods_Gnrc_Prcg_ID=sggp.Sml_Goods_Gnrc_Prcg_ID AND sgc.Is_Deleted_Flg!='Y'";
	$smallGoodsResult = $link->query($query);
	if (mysqli_num_rows($smallGoodsResult) > 0) {
		// output data of each row
		$row = mysqli_fetch_assoc($smallGoodsResult);
		$totalLiabilitySum = $row["liability_sum"];
	}else{
		$totalLiabilitySum = 0;
	}

?>
											<p><b>Small Goods Limit of Liability: $<?php echo number_format($totalLiabilitySum,0);?></b></p>
<?php
}
?>
											<button type="submit" style="align:right;" id="markupFormSubmit" class="btn btn-primary">Update</button>
										</form>
										</div>
									</div>
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
<!---<script src="./js/common.js"></script>--->

<script type="text/javascript">
    window.addEventListener("keydown", (e) => {
        if(e.which === 13 || e.key === 13 )
		{
            e.preventDefault();
        }
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