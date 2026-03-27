<?php
//
// File: dblib.php
// Author: Charles Parry
// Date: 6/29/2022
//
//

require_once 'vendor/autoload.php';
require_once 'pdfHelper.php';

use Classes\GeneratePDF;

// Check user is added or valid for forgot password
function selectUser($link,$username){

	$query = "SELECT * FROM Users u, Usr_Loc ul, Pers p WHERE (u.username = '".$username."' OR u.emailAddress='".$username."') AND
	u.UserID = ul.Usr_ID AND ul.Pers_ID = p.Pers_ID;";
	$result = $link->query($query);

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}

// Generate random password
function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

// Get all columns from Acct for a particular Acct_ID
function selectAcct($link,$Acct_ID){

	// Quick param check
	if(!is_numeric($Acct_ID)){
		return 0;
	}

	$query = "SELECT * FROM Acct WHERE Acct_ID=".$Acct_ID.";";
	$result = $link->query($query);
	//$row = $result->fetch_assoc();

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}


// Get all columns from Addr for a particular Acct_ID
function selectAddrByAcct($link,$Acct_ID,$Addr_Type_Cd){

	// Quick param check
	if(!is_numeric($Acct_ID)){
		return 0;
	}

	if($Addr_Type_Cd!="Work"){
		$Addr_Type_Cd = "Ship";
	}


	$query = "SELECT * FROM Addr a, St_Prov s WHERE a.Acct_ID=".$Acct_ID." AND
	         a.Addr_Type_Cd='".$Addr_Type_Cd."' AND a.St_Prov_ID = s.St_Prov_ID;";

	$result = $link->query($query);

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}


// Get all columns from Addr for a particular Pers_ID
function selectAddrByPers($link,$Pers_ID,$Addr_Type_Cd){

	// Quick param check
	if(!is_numeric($Pers_ID)){
		return 0;
	}

	if($Addr_Type_Cd!="Work"){
		$Addr_Type_Cd = "Ship";
	}

	$query = "SELECT * FROM Addr WHERE Pers_ID=".$Pers_ID." AND
	         Addr_Type_Cd='".$Addr_Type_Cd."';";
	$result = $link->query($query);

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}


// Get all columns from Altn_Nm for a particular Acct_ID
function selectAltnNmByAcct($link,$Acct_ID){

	// Quick param check
	if(!is_numeric($Acct_ID)){
		return 0;
	}

	$query = "SELECT * FROM Altn_Nm WHERE Acct_ID=".$Acct_ID.";";
	$result = $link->query($query);

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}


// Get all columns from Email for a particular Acct_ID
//  Have to support Email_Prim_Flg and Email_Type_Cd
function selectEmailByAcct($link,$Acct_ID,$Email_Prim_Flg,$Email_Type_Cd){

	// Quick param check
	if(!is_numeric($Acct_ID)){
		return 0;
	}

	if($Email_Prim_Flg!="Y"){
		$Email_Prim_Flg="N";
	}

	if($Email_Type_Cd!="Website"){
		$Email_Type_Cd="Work";
	}


	$query = "SELECT * FROM Email WHERE Acct_ID=".$Acct_ID." AND
	          Email_Prim_Flg='".$Email_Prim_Flg."' AND
	          Email_Type_Cd='".$Email_Type_Cd."';";
	$result = $link->query($query);

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}


// Get all columns from Email for a particular Pers_ID
//  Have to support Email_Prim_Flg and Email_Type_Cd
function selectEmailByPers($link,$Pers_ID,$Email_Prim_Flg,$Email_Type_Cd){

	// Quick param check
	if(!is_numeric($Pers_ID)){
		return 0;
	}

	if($Email_Prim_Flg!="Y"){
		$Email_Prim_Flg="N";
	}

	if($Email_Type_Cd!="Website"){
		$Email_Type_Cd="Work";
	}


	$query = "SELECT * FROM Email WHERE Pers_ID=".$Pers_ID." AND
	          Email_Prim_Flg='".$Email_Prim_Flg."' AND
	          Email_Type_Cd='".$Email_Type_Cd."';";
	$result = $link->query($query);

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}


// Get all columns from Tel for a particular Acct_ID
//  Have to support Prim_Tel_Flg and Tel_Type_Cd
function selectTelByAcct($link,$Acct_ID,$Prim_Tel_Flg,$Tel_Type_Cd){

	// Quick param check
	if(!is_numeric($Acct_ID)){
		return 0;
	}

	if($Prim_Tel_Flg!="Y"){
		$Prim_Tel_Flg="N";
	}

	if($Tel_Type_Cd!="Fax"){
		$Tel_Type_Cd="Work";
	}


	$query = "SELECT * FROM Tel WHERE Acct_ID=".$Acct_ID." AND
	          Prim_Tel_Flg='".$Prim_Tel_Flg."' AND
	          Tel_Type_Cd='".$Tel_Type_Cd."' AND Pers_ID is NULL;";

	$result = $link->query($query);

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}


// Get all columns from Tel for a particular Pers_ID
//  Have to support Prim_Tel_Flg and Tel_Type_Cd
function selectTelByPers($link,$Pers_ID,$Prim_Tel_Flg,$Tel_Type_Cd){

	// Quick param check
	if(!is_numeric($Pers_ID)){
		return 0;
	}

	if($Prim_Tel_Flg!="Y"){
		$Prim_Tel_Flg="N";
	}

	if($Tel_Type_Cd!="Fax"){
		$Tel_Type_Cd="Work";
	}


	$query = "SELECT * FROM Tel WHERE Pers_ID=".$Pers_ID." AND
	          Prim_Tel_Flg='".$Prim_Tel_Flg."' AND
	          Tel_Type_Cd='".$Tel_Type_Cd."';";
	$result = $link->query($query);

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}


// Get all States from the state enumeration table
function selectStates($link){

	//$query = "SELECT * FROM St_Prov WHERE Cntry_Nm = 'US' ORDER BY St_Prov_Nm";
	$query = "SELECT * FROM St_Prov ORDER BY Cntry_Nm desc, St_Prov_Nm asc";
	$result = $link->query($query);

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}


// Get States from the state enumeration table by ID
function selectState($link,$stateID,$shortForm="N"){

	// Quick param check
	if(!is_numeric($stateID)){
		return 0;
	}

	$query = "SELECT * FROM St_Prov WHERE St_Prov_ID = ".$stateID.";";
	$result = $link->query($query);

	if ($result->num_rows > 0) {
		$row = mysqli_fetch_assoc($result);
		if($shortForm=="Y"){
			return $row["St_Prov_ISO_2_Cd"];
		}else{
			return $row["St_Prov_Nm"];
		}
	}else{
		return 0;
	}
}


// Get Warranty base pricing for term, type and tier
function selectWarrantyBasePricing($link,$warrantyTerm,$warrantyType,$warrantyTier){

	// Quick param check
	if(!is_numeric($warrantyTerm)){
		return 0;
	}
	
	if(!is_numeric($warrantyType)){
		return 0;
	}

	if($warrantyTier!="S"){
		$warrantyTier = "B";
	}

	$query = "SELECT * FROM Wrnty_Std_Prcg WHERE Cvrg_Term_Yrs_Nbr=".$warrantyTerm." AND
	          Veh_Type_Cd=".$warrantyType." AND Tier_Type_Cd='".$warrantyTier."' AND
	          Base_Price_Table_Type_Code='Standard';";
	$result = $link->query($query);

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}

// Get Warranty base pricing for term, type and tier
function selectwrapWarrantyBasePricing($link,$warrantyTerm,$warrantyType,$warrantyTier){
	// Quick param check
	if(!is_numeric($warrantyTerm)){
		return 0;
	}
	if(!is_numeric($warrantyType)){
		return 0;
	}

	if($warrantyTier!="S"){
		$warrantyTier = "B";
	}


	$query = "SELECT * FROM Wrnty_Std_Prcg WHERE Cvrg_Term_Yrs_Nbr=".$warrantyTerm." AND
	          Veh_Type_Cd=".$warrantyType." AND Tier_Type_Cd='".$warrantyTier."' AND
	          Base_Price_Table_Type_Code='Wrap';";
	$result = $link->query($query);

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}


// Get Addl_Std_Prcg sum depending on selected flags
function selectAddlStdPrcgSum($link,$isAEP,$isAPU,$isAER,$isOld){

	$listOfItems = "";

	if($isAEP=="Y"){
		if($listOfItems!=""){
			$listOfItems.=",";
		}
		$listOfItems.="'AEP'";
	}

	if($isAPU=="Y"){
		if($listOfItems!=""){
			$listOfItems.=",";
		}
		$listOfItems.="'APU'";
	}

	if($isAER=="Y"){
		if($listOfItems!=""){
			$listOfItems.=",";
		}
		$listOfItems.="'AER'";
	}

	if($isOld=="Y"){
		if($listOfItems!=""){
			$listOfItems.=",";
		}
		$listOfItems.="'OLD'";
	}

	if($listOfItems!=""){
		$query = "SELECT sum(Sales_Agt_Cost_Amt) AS Addl_Sales_Agt_Cost_Amt,
		                 sum(Sales_Agt_Commission_Amt) AS Addl_Sales_Agt_Commission_Amt,
		                 sum(Dlr_Cost_Amt) AS Addl_Dlr_Cost_Amt,
		                 sum(Dlr_Mrkp_Max_Amt) AS Addl_Dlr_Mrkp_Max_Amt,
		                 sum(MSRP_Amt) AS Addl_MSRP_Amt FROM Addl_Std_Prcg WHERE Addl_Type_Cd in (".$listOfItems.");";

		$result = $link->query($query);

		if ($result->num_rows > 0) {
			return $result;
		}else{
			return 0;
		}
	}else{
		return 0;
	}

}


// Security check that the dealerID in session owns the warrantyID being edited
function dealerOwnsWarranty($link,$dealerID,$warrantyID){

	// Quick param check
	if(!is_numeric($dealerID)){
		return 0;
	}

	if(!is_numeric($warrantyID)){
		return 0;
	}


	// Updating to take into account sub locations.  cparry 1/3/2023.
	//$query = "SELECT * FROM Cntrct WHERE Cntrct_ID=".$warrantyID." AND
	//          Mfr_Acct_ID=".$dealerID.";";
	$query = "SELECT * FROM Cntrct c, Acct a WHERE c.Cntrct_ID=".$warrantyID." AND
                  c.Mfr_Acct_ID = a.Acct_ID AND
                  (a.Acct_ID = ".$dealerID." OR a.prnt_Acct_ID = ".$dealerID.");";


	$result = $link->query($query);

	if ($result->num_rows > 0) {
		return $result;
	}else{
		return 0;
	}
}


// Check for a warranty file asset of a particular type
function getFileAssetForWarranty($link,$warrantyID,$fileType){

	// Quick param check
	if(!is_numeric($warrantyID)){
		return 0;
	}

	if(!is_numeric($fileType)){
		return 0;
	}


	$query = "SELECT * FROM File_Assets WHERE Dealer_Cntrct_ID=".$warrantyID." AND
	          File_Asset_Type_ID=".$fileType." ORDER BY createdDate DESC;";

	$result = $link->query($query);

	if ($result->num_rows > 0) {
		$row = mysqli_fetch_assoc($result);
		if($row["Path_to_File"]!=""){
			return $row["Path_to_File"];
		}else{
			return 0;
		}
	}else{
		return 0;
	}
}



// Get Acct_ID list for Agency
function getAcctIDForAgency($link,$agencyPrimaryUserID,$agencyAcctID){

	// Quick param check
	if(!is_numeric($agencyPrimaryUserID)){
		return 0;
	}

	if(!is_numeric($agencyAcctID)){
		return 0;
	}


	// Variables
	$returnList = "";


	$query = "SELECT GROUP_CONCAT(a.Acct_ID) as acctIDList FROM Acct a WHERE
			a.Acct_ID in (select Acct_ID FROM Acct WHERE Sls_Agnt_ID=".$agencyPrimaryUserID.") OR
			a.Acct_ID in (
			SELECT a.Acct_ID FROM Acct a WHERE Sls_Agnt_ID in (
				SELECT userID FROM Users WHERE Acct_ID = ".$agencyAcctID."))";

// 1318
// 2090

	$result = $link->query($query);

	if ($result->num_rows > 0) {
		$row = mysqli_fetch_assoc($result);
		if($row["acctIDList"]!=""){
			$returnList = $row["acctIDList"];
			return $returnList;
		}else{
			return 0;
		}
	}else{
		return 0;
	}
}



// Check for a small goods file asset (receipt) by ID
function getFileAssetForSmallGood($link,$smallGoodID,$fileType){

	// Quick param check
	if(!is_numeric($smallGoodID)){
		return 0;
	}

	if(!is_numeric($fileType)){
		return 0;
	}


	$query = "SELECT * FROM File_Assets WHERE Sml_Goods_Cvge_ID=".$smallGoodID." AND
	          File_Asset_Type_ID=".$fileType." ORDER BY createdDate DESC;";

	$result = $link->query($query);

	if ($result->num_rows > 0) {
		$row = mysqli_fetch_assoc($result);
		if($row["Path_to_File"]!=""){
			return $row["Path_to_File"];
		}else{
			return 0;
		}
	}else{
		return 0;
	}
}


// Load the Cntrct Small Goods Totals columns with sums from Sml_Goods_Cvge
function updateWarrantySmallGoodsTotals($link,$warrantyID,$includeActual="N"){
	// Quick param check
	if(!is_numeric($warrantyID)){
		return 0;
	}

	// Variables
	$Sml_Goods_Sales_Agt_Cost_Amt = 0;
	$Sml_Goods_Sales_Agt_Commission_Amt = 0;
	$Sml_Goods_Dlr_Cost_Amt = 0;
	$Sml_Goods_Dlr_Mrkp_Max_Amt = 0;
	$Sml_Goods_Actl_Prc_Amt = 0;

	/*
		$query = "SELECT sum(Sales_Agt_Cst_Amt) AS Sml_Goods_Sales_Agt_Cost_Amt,
						sum(Sales_Agt_Comssn_Amt) AS Sml_Goods_Sales_Agt_Commission_Amt,
						sum(Dlr_Cst_Amt) AS Sml_Goods_Dlr_Cost_Amt,
						sum(Dlr_Mrkp_Max_Amt) AS Sml_Goods_Dlr_Mrkp_Max_Amt,
						sum(Actl_Prc_Amt) AS Sml_Goods_Actl_Prc_Amt FROM Sml_Goods_Cvge WHERE Cntrct_ID=".$warrantyID.";";
	*/

	$query = "SELECT Cntrct_ID , Sales_Agt_Cst_Amt, Gnrc_Item_Cat_Qty_Cnt, (sum(Sales_Agt_Cst_Amt*Gnrc_Item_Cat_Qty_Cnt)) AS Sml_Goods_Sales_Agt_Cost_Amt,
					 (sum(Sales_Agt_Comssn_Amt*Gnrc_Item_Cat_Qty_Cnt)) AS Sml_Goods_Sales_Agt_Commission_Amt,
					 (sum(Dlr_Cst_Amt*Gnrc_Item_Cat_Qty_Cnt)) AS Sml_Goods_Dlr_Cost_Amt,
					 (sum(Dlr_Mrkp_Max_Amt*Gnrc_Item_Cat_Qty_Cnt)) AS Sml_Goods_Dlr_Mrkp_Max_Amt,
					 sum(Actl_Prc_Amt) AS Sml_Goods_Actl_Prc_Amt FROM Sml_Goods_Cvge WHERE
					 Is_Deleted_Flg!='Y' AND Cntrct_ID=".$warrantyID.";";

	$result = $link->query($query);
	$row = mysqli_fetch_assoc($result);
	if ($row["Cntrct_ID"] != "") {
		/*
		echo "query=".$query;
		echo "<br />";
		print_r($row);
		echo "<br /><br />";
		*/
		$Sml_Goods_Sales_Agt_Cost_Amt = $row["Sml_Goods_Sales_Agt_Cost_Amt"];
		$Sml_Goods_Sales_Agt_Commission_Amt = $row["Sml_Goods_Sales_Agt_Commission_Amt"];
		$Sml_Goods_Dlr_Cost_Amt = $row["Sml_Goods_Dlr_Cost_Amt"];
		$Sml_Goods_Dlr_Mrkp_Max_Amt = $row["Sml_Goods_Dlr_Mrkp_Max_Amt"];
		$Sml_Goods_Actl_Prc_Amt = $row["Sml_Goods_Actl_Prc_Amt"];

		}else{

			$Sml_Goods_Sales_Agt_Cost_Amt = 0;
			$Sml_Goods_Sales_Agt_Commission_Amt = 0;
			$Sml_Goods_Dlr_Cost_Amt = 0;
			$Sml_Goods_Dlr_Mrkp_Max_Amt = 0;
			$Sml_Goods_Actl_Prc_Amt = 0;
		}

			// Now update the 'small goods totals' columns in the Cntrct table
			$query = "UPDATE Cntrct SET Sales_Agt_Sml_Goods_Cst_Amt=".$Sml_Goods_Sales_Agt_Cost_Amt.",
					Sales_Agt_Sml_Goods_Commission_Tot_Amt=".$Sml_Goods_Sales_Agt_Commission_Amt.",
					Dlr_Sml_Goods_Cst_Tot_Amt=".$Sml_Goods_Dlr_Cost_Amt.",
					Dlr_Sml_Goods_Max_Mrkp_Tot_Amt=".$Sml_Goods_Dlr_Mrkp_Max_Amt.",";
			if($includeActual=="Y"){
				$query .= "Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt=".$Sml_Goods_Dlr_Mrkp_Max_Amt.",";
			}

			$query .= "Sml_Goods_Tot_Amt=".$Sml_Goods_Actl_Prc_Amt." WHERE Cntrct_ID=".$warrantyID.";";
		//echo "<br />query=".$query;
		//die();

			$result = $link->query($query);

			return 1;

}


// Load the Cntrct Totals columns with sums from base, add-on and small goods.
function updateWarrantyTotals($link,$warrantyID,$includeActual="N"){

	// Quick param check
	if(!is_numeric($warrantyID)){
		return 0;
	}

	// Update the 'totals' columns in the Cntrct table
	$query = "UPDATE Cntrct SET
	          Tot_Sales_Agt_Cost_Amt=(Sales_Agt_Cost_Amt+Sales_Agt_Sml_Goods_Cst_Amt+Addl_Sales_Agt_Cost_Amt),
			  Tot_Sales_Agt_Commission_Amt=(Sales_Agt_Commission_Amt+Sales_Agt_Sml_Goods_Commission_Tot_Amt+Addl_Sales_Agt_Commission_Amt),
			  Tot_Dlr_Cost_Amt=(Dlr_Cost_Amt+Dlr_Sml_Goods_Cst_Tot_Amt+Addl_Dlr_Cost_Amt),
			  Tot_Dlr_Mrkp_Max_Amt=(Dlr_Mrkp_Max_Amt+Dlr_Sml_Goods_Max_Mrkp_Tot_Amt+Addl_Dlr_Mrkp_Max_Amt),";

	if($includeActual=="Y"){
		$query .= "Tot_Dlr_Mrkp_Act_Amt=(Dlr_Mrkp_Actl_Amt+Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt+Addl_Dlr_Mrkp_Actl_Amt),";
	}

	$query .= "Tot_MSRP_Amt=(MSRP_Amt+Sml_Goods_Tot_Amt+Addl_MSRP_Amt) WHERE Cntrct_ID=".$warrantyID.";";

	$result = $link->query($query);

	return 1;

}



// Make a copy of the specified quote.  Allow for 'quantity', 'terms' and 'coverage' to be updated.
function copyQuote($link,$warrantyID,$newCoverage="",$newTerm="",$newQuantity=""){
		// Quick param check
		if(!is_numeric($warrantyID)){
			return 0;
		}

	    /*
		$query = "insert into Cntrct (`Mfr_Acct_ID`,`Dlr_Agt_Prsn_ID`,
			`VT_Sales_Agt_Prsn_ID`,`Agency_Acct_ID`,`Agency_Sales_Agt_Prsn_Id`,
			`Mncplty_Acct_ID`,`Prnt_Cntrct_ID`,`Cls_ID`,`Cntrct_Origtn_Dt_ID`,
			`Cntrct_Nbr`,`Dlr_Cost_Amt`,`Fin_And_Ins_Markup_Amt`,`Rtl_Cost_Amt`,
			`Affl_Fee_Amt`,`ComMissiion_Amt`,`Tot_Amt`,`Veh_ID`,`Cntrct_Dim_ID`,
			`Cntrct_Signd_Dt_ID`,`Pers_Who_Signd_Cntrct_ID`,`Cntrct_Img`,
			`Sply_Pkt_Shipd_Dt_ID`,`Sply_Pkt_Shipd_Dte`,`Adminr_Acct_ID`,
			`Affl_Refl_Fee_Amt`,`Bolt_On_Amt`,`Aerl_Amt`,`Sml_Goods_Amt`,
			`Qte_Dt_ID`,`Qte_Dt`,`Created_Warranty_ID`,`Cntrct_Sales_Chnl_ID`,
			`Cntrct_Sales_Chnl`,`Sales_Agt_Cost_Amt`,`Sales_Agt_Commission_Amt`,
			`Dlr_Mrkp_Max_Amt`,`Dlr_Mrkp_Actl_Amt`,`MSRP_Amt`,`Sales_Agt_Sml_Goods_Cst_Amt`,
			`Sales_Agt_Sml_Goods_Commission_Tot_Amt`,`Dlr_Sml_Goods_Cst_Tot_Amt`,
			`Dlr_Sml_Goods_Max_Mrkp_Tot_Amt`,`Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt`,
			`Sml_Goods_Tot_Amt`,`Addl_Sales_Agt_Cost_Amt`,`Addl_Sales_Agt_Commission_Amt`,
			`Addl_Dlr_Cost_Amt`,`Addl_Dlr_Mrkp_Max_Amt`,`Addl_Dlr_Mrkp_Actl_Amt`,
			`Addl_Dlr_Mrkp_Actl_APU_Amt`,`Addl_Dlr_Mrkp_Actl_AEP_Amt`,`Addl_Dlr_Mrkp_Actl_AER_Amt`,
			`Addl_MSRP_Amt`,`Tot_Sales_Agt_Cost_Amt`,`Tot_Sales_Agt_Commission_Amt`,
			`Tot_Dlr_Cost_Amt`,`Tot_Dlr_Mrkp_Max_Amt`,`Tot_Dlr_Mrkp_Act_Amt`,`Tot_MSRP_Amt`,
			`Created_Date`,`Wrnty_Stat_Desc`,`Paymnt_Rcvd_Frm_TNG_Dt`,`TNG_Paymnt_Amt`,
			`Amt_Diff_Reason_Desc`,`Afflt_Fee_Payee_Nm`,`Afflt_Fee_Payee_Title_Nm`,`Afflt_Fee_Pct`,
			`Afflt_Fee_Payee_Amt`,`Afflt_Fee_Pd_Dt`,`Afflt_Fee_ACH_Nbr`)
			SELECT
			`Mfr_Acct_ID`,`Dlr_Agt_Prsn_ID`,
			`VT_Sales_Agt_Prsn_ID`,`Agency_Acct_ID`,`Agency_Sales_Agt_Prsn_Id`,
			`Mncplty_Acct_ID`,`Prnt_Cntrct_ID`,`Cls_ID`,`Cntrct_Origtn_Dt_ID`,
			`Cntrct_Nbr`,`Dlr_Cost_Amt`,`Fin_And_Ins_Markup_Amt`,`Rtl_Cost_Amt`,
			`Affl_Fee_Amt`,`ComMissiion_Amt`,`Tot_Amt`,`Veh_ID`,`Cntrct_Dim_ID`,
			`Cntrct_Signd_Dt_ID`,`Pers_Who_Signd_Cntrct_ID`,`Cntrct_Img`,
			`Sply_Pkt_Shipd_Dt_ID`,`Sply_Pkt_Shipd_Dte`,`Adminr_Acct_ID`,
			`Affl_Refl_Fee_Amt`,`Bolt_On_Amt`,`Aerl_Amt`,`Sml_Goods_Amt`,
			`Qte_Dt_ID`,`Qte_Dt`,`Created_Warranty_ID`,`Cntrct_Sales_Chnl_ID`,
			`Cntrct_Sales_Chnl`,`Sales_Agt_Cost_Amt`,`Sales_Agt_Commission_Amt`,
			`Dlr_Mrkp_Max_Amt`,`Dlr_Mrkp_Actl_Amt`,`MSRP_Amt`,`Sales_Agt_Sml_Goods_Cst_Amt`,
			`Sales_Agt_Sml_Goods_Commission_Tot_Amt`,`Dlr_Sml_Goods_Cst_Tot_Amt`,
			`Dlr_Sml_Goods_Max_Mrkp_Tot_Amt`,`Dlr_Sml_Goods_Actl_Mrkp_Tot_Amt`,
			`Sml_Goods_Tot_Amt`,`Addl_Sales_Agt_Cost_Amt`,`Addl_Sales_Agt_Commission_Amt`,
			`Addl_Dlr_Cost_Amt`,`Addl_Dlr_Mrkp_Max_Amt`,`Addl_Dlr_Mrkp_Actl_Amt`,
			`Addl_Dlr_Mrkp_Actl_APU_Amt`,`Addl_Dlr_Mrkp_Actl_AEP_Amt`,`Addl_Dlr_Mrkp_Actl_AER_Amt`,
			`Addl_MSRP_Amt`,`Tot_Sales_Agt_Cost_Amt`,`Tot_Sales_Agt_Commission_Amt`,
			`Tot_Dlr_Cost_Amt`,`Tot_Dlr_Mrkp_Max_Amt`,`Tot_Dlr_Mrkp_Act_Amt`,`Tot_MSRP_Amt`,
			`Created_Date`,`Wrnty_Stat_Desc`,`Paymnt_Rcvd_Frm_TNG_Dt`,`TNG_Paymnt_Amt`,
			`Amt_Diff_Reason_Desc`,`Afflt_Fee_Payee_Nm`,`Afflt_Fee_Payee_Title_Nm`,`Afflt_Fee_Pct`,
			`Afflt_Fee_Payee_Amt`,`Afflt_Fee_Pd_Dt`,`Afflt_Fee_ACH_Nbr`
			from Cntrct where Cntrct_id = ".$warrantyID.";";
	    */
		$query = "CREATE TABLE tmptable_1 SELECT * FROM Cntrct WHERE Cntrct_ID = ".$warrantyID.";";
		$result = $link->query($query);

		$query = "UPDATE tmptable_1 SET Cntrct_ID = NULL;";
		$result = $link->query($query);

		$query = "INSERT INTO Cntrct SELECT * FROM tmptable_1;";
		$result = $link->query($query);

		$new_cntrct_ID = mysqli_insert_id($link);

		$query = "DROP TABLE IF EXISTS tmptable_1;";
		$result = $link->query($query);

		//echo "created new Cntrct entry from old Cntrct_ID = ".$warrantyID." and with new Cntrct_ID = ".$new_cntrct_ID;
		//echo "<br /><br />";

		// Get the Cntrct_Dim_id and veh_id
		$query = "SELECT Cntrct_Dim_ID, Veh_ID, Cntrct_ID FROM Cntrct WHERE Cntrct_ID=".$new_cntrct_ID.";";
		$result = $link->query($query);
		$row = mysqli_fetch_assoc($result);
		if ($row["Cntrct_Dim_ID"] != "") {
			$cntrct_dim_id = $row["Cntrct_Dim_ID"];
			$veh_id = $row["Veh_ID"];
			$cntrct_ID = $row["Cntrct_ID"];
	    	$_SESSION["warrantyID"] = $cntrct_ID;
		}else{
			return 0;
		}


		// Now create the small goods copy
		$query = "CREATE TABLE tmptable_1 SELECT * FROM Sml_Goods_Cvge WHERE Cntrct_ID = ".$warrantyID.";";
		$result = $link->query($query);

		$query = "UPDATE tmptable_1 SET Sml_Goods_Cvge_ID = NULL, Cntrct_ID=".$new_cntrct_ID.";";
		$result = $link->query($query);

		$query = "INSERT INTO Sml_Goods_Cvge SELECT * FROM tmptable_1;";
		$result = $link->query($query);

		$query = "DROP TABLE IF EXISTS tmptable_1;";
		$result = $link->query($query);




		// Now create the Cntrct_Dim copy
		$query = "CREATE TABLE tmptable_1 SELECT * FROM Cntrct_Dim WHERE Cntrct_Dim_ID = ".$cntrct_dim_id.";";
		$result = $link->query($query);

		$query = "UPDATE tmptable_1 SET Cntrct_Dim_ID = NULL;";
		$result = $link->query($query);

		$query = "INSERT INTO Cntrct_Dim SELECT * FROM tmptable_1;";
		$result = $link->query($query);

		// Get the Cntrct_dim_id of the new contract dim entry.
		$new_cntrct_dim_ID = mysqli_insert_id($link);

		$query = "DROP TABLE IF EXISTS tmptable_1;";
		$result = $link->query($query);


		// Update the Cntrct_Dim_ID in the newly created Cntrct to point to the new Veh entry
		if($new_cntrct_dim_ID != 0){
			$query = "UPDATE Cntrct SET Cntrct_Dim_ID=".$new_cntrct_dim_ID." WHERE Cntrct_ID=".$new_cntrct_ID.";";
			$result = $link->query($query);
		}else{
			return 0;
		}


		//echo "created new Cntrct_Dim entry from old cntrct_dim_id = ".$cntrct_dim_id." and with new new_cntrct_dim_ID = ".$new_cntrct_dim_ID;
		//echo "<br /><br />";

		// Update the term and coverage if specified.
		if($newTerm!="" && is_numeric($newTerm)){
			$query = "UPDATE Cntrct_Dim SET Cntrct_Term_Mnths_Nbr=".$newTerm." WHERE Cntrct_Dim_ID=".$new_cntrct_dim_ID.";";
			$result = $link->query($query);
			//echo "updating term: ".$query;
			//echo "<br /><br />";
		}

		if($newCoverage!="" && ($newCoverage=="Squad" || $newCoverage=="Battalion")){
			if($newCoverage=="Squad"){
				$cntrct_lvl_cd = 'S';
			}else{
				$cntrct_lvl_cd = 'B';
			}
			$query = "UPDATE Cntrct_Dim SET Cntrct_Lvl_Desc='".$newCoverage."', Cntrct_Lvl_Cd='".$cntrct_lvl_cd."'
			          WHERE Cntrct_Dim_ID=".$new_cntrct_dim_ID.";";
			$result = $link->query($query);
		//echo "updating coverage: ".$query;
		//echo "<br /><br />";
		}


		// Now create the Veh copy
		$query = "CREATE TABLE tmptable_1 SELECT * FROM Veh WHERE Veh_ID = ".$veh_id.";";
		$result = $link->query($query);

		$query = "UPDATE tmptable_1 SET Veh_ID = NULL;";
		$result = $link->query($query);

		$query = "INSERT INTO Veh SELECT * FROM tmptable_1;";
		$result = $link->query($query);

		// Get the Veh_id of the new Veh entry.
		$new_veh_ID = mysqli_insert_id($link);

		$query = "DROP TABLE IF EXISTS tmptable_1;";
		$result = $link->query($query);

		//echo "created new Veh entry from old veh_id = ".$veh_id." and with new new_veh_ID = ".$new_veh_ID;
		//echo "<br /><br />";

		// Update the Veh_ID in the newly created Cntrct to point to the new Veh entry
		if($new_veh_ID!=0){
			$query = "UPDATE Cntrct SET Veh_ID=".$new_veh_ID." WHERE Cntrct_ID=".$new_cntrct_ID.";";
			$result = $link->query($query);
		}else{
			return 0;
		}

		//Generate Quote PDF
		createWarrantyPDF($link, $cntrct_ID , "Y");



        ///////Check small goods
		$query = "SELECT * FROM Sml_Goods_Cvge WHERE Cntrct_ID = ".$warrantyID.";";
		$result = $link->query($query);
        $smallgoods =  mysqli_num_rows($result);

		if ($smallgoods > 0) {
		//Copy small goods
		copySmallGoods($link, $_GET["warrantyID"]);
		}

}


function createWarranty($link, $post){

		// Variables.
	$dealerID = "";
	$Acct_ID = "";  // For location
	$Mfr_Acct_ID = ""; // Saved location in Cntrct
	$dealerAgentID = ""; // For Dealer sales agent
	$persID = ""; // the persID of the logged in person.
	$warrantyID = "";
	$warrantyStatus = "";
	$agreementDate = "";
	$customerName = "";
	$customerEmail = "";
	$customerAddress = "";
	$customerCity = "";
	$customerState = "";
	$customerZip = "";
	$customerPhone = "";
	$customerSalesChannel = "Outside sales";
	$Vehicle_Manufacturer_Name = "";
	$Vehicle_Gross_Weight = "";
	$Vehicle_Type = "";
	$Vehicle_Vin_Number = "";
	$Vehicle_Year = "";
	$Vehicle_Make = "";
	$Vehicle_Model = "";
	$Engine_Make = "";
	$Engine_Model = "";
	$Engine_Serial = "";
	$Engine_Hours = "";
	$Transmission_Make = "";
	$Transmission_Model = "";
	$Transmission_Serial = "";
	$Odometer_Reading = "";
	$Odometer_Miles_Or_KM = "";
	$ECM_Reading = "";
	$ECM_Miles_Or_KM = "";
	$APU_Flg = "";
	$APU_Engine_Make = "";
	$APU_Engine_Model = "";
	$APU_Engine_Year = "";
	$APU_Engine_Serial = "";
	$APU_Hours = "";
	$Vehicle_New_Flag = "";
	$Vehicle_Description = "";
	$Tier_Type = "";
	$Apparatus_Equipment_Package = "";
	$Aerial_Package = "";
	$Coverage_Term = "";
	$Small_Goods_Package = "";
	$Srvc_Veh_Flg = ""; // Customer services own fleet of vehicles
	$Supply_Packet_To_Be_Shipped = "";
	$Supply_Packet_Left = "";
	$Supply_Packet_Shipped_Date = "";
	$Lien_Holder_Name = "";
	$Lien_Holder_Email = "";
	$Lien_Holder_Address = "";
	$Lien_Holder_City = "";
	$Lien_Holder_State_Province = "";
	$Lien_Holder_Postal_Code = "";
	$Lien_Holder_Phone_Number = "";
	$Dealer_Signature = "";
	$Dealer_Signature_Name = "";
	$Dealer_Signature_Date = "";
	$Customer_Signature = "";
	$Customer_Signature_Name = "";
	$Customer_Signature_Date = "";
	$isQuote = "N";
	$customerPO = "";

	$wrap_program = "N";
	$wrap_Program_Term = "";

	$dealerARNumber = "";
	$smallGoodsPackage = "";

	$form_err = "";
	$ECM_Reading_Km = "";
	$Odometer_Reading_Km = "";

	$currentTerm = "";
	$currentTier = "";
	$currentCoverage = "";
	$currentAPU = "";
	$currentAerial = "";
	$currentAEP = "";
	$currentVehicleNewFlag = "";


	if (!empty(trim($post["Acct_ID"]))) {
		$Acct_ID = trim($post["Acct_ID"]);
	}


	if (isset($_POST["dealerAgentID"]) && !empty(trim($_POST["dealerAgentID"]))) {
		$dealerAgentID = trim($_POST["dealerAgentID"]);
	} else {
		if(isset($_SESSION["persID"]) && $_SESSION["persID"]!=""){
			// If we did not get a dealerAgentID from the form, default to the currently authenticated PersID
			$dealerAgentID = $_SESSION["persID"];
		}else{
			$_SESSION["errorMessage"] = "ERROR: No dealer agent found, required to create a warranty.";
			header("location: create_warranty.php");
			die();
		}
	}


	if (!empty(trim($post["agreementDate"]))) {
		$agreementDate = trim($post["agreementDate"]);
		$agreementDateForInsert = trim($post["agreementDate"]);
		$date = DateTime::createFromFormat('Y-m-d', $agreementDate);
		$agreementDate = $date->format('m-d-Y');
	}

	if (!empty(trim($post["customerName"]))) {
		$customerName = trim($post["customerName"]);
		$customerName = ucwords($customerName);
	}

	if (!empty(trim($post["customerEmail"]))) {
		$customerEmail = trim($post["customerEmail"]);
	}

	if (!empty(trim($post["customerAddress"]))) {
		$customerAddress = trim($post["customerAddress"]);
		$customerAddress = ucwords($customerAddress);
	}

	if (!empty(trim($post["customerCity"]))) {
		$customerCity = trim($post["customerCity"]);
		$customerCity = ucwords($customerCity);
	}

	if (!empty(trim($post["customerState"]))) {
		$customerState = trim($post["customerState"]);
		$customerState = ucwords($customerState);
	}

	if (!empty(trim($post["customerPO"]))) {
		$customerPO = trim($post["customerPO"]);
	}

	if (!empty(trim($post["customerZip"]))) {
		$customerZip = trim($post["customerZip"]);
	}

	if (!empty(trim($post["customerPhone"]))) {
		$customerPhone = trim($post["customerPhone"]);
	}

	if (!empty(trim($post["customerSalesChannel"]))) {
		$customerSalesChannel = trim($post["customerSalesChannel"]);
	}

	if (isset($post["smallGoodsPackage"]) && !empty(trim($post["smallGoodsPackage"]))) {
		$smallGoodsPackage = trim($post["smallGoodsPackage"]);
	}else{
		$smallGoodsPackage = "N";
	}

	if (!empty(trim($post["Srvc_Veh_Flg"]))) {
		$Srvc_Veh_Flg = trim($post["Srvc_Veh_Flg"]);
	}


	if (!empty(trim($post["vehicleGrossWeight"]))) {
		$Vehicle_Gross_Weight = trim($post["vehicleGrossWeight"]);
	}

	// NOTE: May want to save these values differently
	if (!empty(trim($post["vehicleGrossWeight"]))) {
		$Vehicle_Gross_Weight = trim($post["vehicleGrossWeight"]);
		if ($Vehicle_Gross_Weight == "type 1") {
			$Vehicle_Type = 1;
		} else if ($Vehicle_Gross_Weight == "type 2") {
			$Vehicle_Type = 2;
		} else if ($Vehicle_Gross_Weight == "type 3") {
			$Vehicle_Type = 3;
		} else {
			// NOTE: what to do in case of default?
			$Vehicle_Type = 1;
		}
	}


	if (!empty(trim($post["vehicleVIN"]))) {
		$Vehicle_Vin_Number = trim($post["vehicleVIN"]);
	}

	if (!empty(trim($post["vehicleYear"]))) {
		$Vehicle_Year = trim($post["vehicleYear"]);
	}

	if (!empty(trim($post["vehicleMake"]))) {
		$Vehicle_Make = trim($post["vehicleMake"]);
	}

	if (!empty(trim($post["vehicleModel"]))) {
		$Vehicle_Model = trim($post["vehicleModel"]);
	}

	if (!empty(trim($post["engineMake"]))) {
		$Engine_Make = trim($post["engineMake"]);
	}

	if (!empty(trim($post["engineModel"]))) {
		$Engine_Model = trim($post["engineModel"]);
	}

	if (!empty(trim($post["engineSerialNumber"]))) {
		$Engine_Serial = trim($post["engineSerialNumber"]);
	}

	if (!empty(trim($post["engineHours"]))) {
		$Engine_Hours = trim($post["engineHours"]);
	}

	if (!empty(trim($post["transmissionMake"]))) {
		$Transmission_Make = trim($post["transmissionMake"]);
	}

	if (!empty(trim($post["transmissionModel"]))) {
		$Transmission_Model = trim($post["transmissionModel"]);
	}

	if (!empty(trim($post["transmissionSerialNumber"]))) {
		$Transmission_Serial = trim($post["transmissionSerialNumber"]);
	}

	if (!empty(trim($post["odometerReading"]))) {
		$Odometer_Reading = trim($post["odometerReading"]);
	}

	if (isset($post["milesOrKM"]) && !empty(trim($post["milesOrKM"]))) {
		$Odometer_Miles_Or_KM = trim($post["milesOrKM"]);
	}

	if (!empty(trim($post["ecmReading"]))) {
		$ECM_Reading = trim($post["ecmReading"]);
	}

	if (isset($post["ecmMilesOrKM"]) && !empty(trim($post["ecmMilesOrKM"]))) {
		$ECM_Miles_Or_KM = trim($post["ecmMilesOrKM"]);
	}

	if (isset($post["isAPU"]) && !empty(trim($post["isAPU"]))) {
		if($Vehicle_Type == 3)
		{
			$APU_Flg = trim($post["isAPU"]);
		}
		else
		{
			$APU_Flg = 'N';
		}
	}else{
		$APU_Flg = "N";
	}

	if (!empty(trim($post["apuMake"]))) {
		$APU_Engine_Make = trim($post["apuMake"]);
	}

	if (!empty(trim($post["apuModel"]))) {
		$APU_Engine_Model = trim($post["apuModel"]);
	}

	if (!empty(trim($post["apuYear"]))) {
		$APU_Engine_Year = trim($post["apuYear"]);
	}

	if (!empty(trim($post["apuSerialNumber"]))) {
		$APU_Engine_Serial = trim($post["apuSerialNumber"]);
	}

	if (!empty(trim($post["apuHours"]))) {
		$APU_Hours = trim($post["apuHours"]);
	}

	if (isset($post["isVehicleNew"]) && !empty(trim($post["isVehicleNew"]))) {
		$Vehicle_New_Flag = trim($post["isVehicleNew"]);
	}

	if (!empty(trim($post["vehicleDescription"]))) {
		$Vehicle_Description = trim($post["vehicleDescription"]);
	}

	if (!empty(trim($post["vehicleTierType"]))) {
		$Tier_Type = trim($post["vehicleTierType"]);
	}

	if (isset($post["boltOnPackage"]) && !empty(trim($post["boltOnPackage"]))) {
		$Apparatus_Equipment_Package = trim($post["boltOnPackage"]);
	}else{
		$Apparatus_Equipment_Package = "N";
	}

	if (isset($post["aerialPackage"]) && !empty(trim($post["aerialPackage"]))) {
		$Aerial_Package = trim($post["aerialPackage"]);
	}else{
		$Aerial_Package = "N";
	}

	if (isset($_POST["wrap_program"]) && !empty(trim($post["wrap_program"]))) {
			if(trim($post["wrap_program"]) == 'Y')
			{
				$wrap_program = 'Y';
				if (!empty(trim($_POST["wrapProgram2"]))) {
					$wrap_Program_Term = trim($_POST["wrapProgram2"]);
				}
			}
			else
			{
				$wrap_program = 'N';

				if (!empty(trim($post["coverageTerm"]))) {
					$Coverage_Term = trim($post["coverageTerm"]);
				}
			}
	}else{
		$wrap_program = "N";
		if (!empty(trim($post["coverageTerm"]))) {
			$Coverage_Term = trim($post["coverageTerm"]);
		}
	}

	if (isset($post["supplyPacketToBeShipped"]) && !empty(trim($post["supplyPacketToBeShipped"]))) {
		$Supply_Packet_To_Be_Shipped = trim($post["supplyPacketToBeShipped"]);
	}

	if (isset($post["supplyPacketLeft"]) && !empty(trim($post["supplyPacketLeft"]))) {
		$Supply_Packet_Left = trim($post["supplyPacketLeft"]);
	}

	if (!empty(trim($post["supplyPacketShippedDate"]))) {
		$Supply_Packet_Shipped_Date = trim($post["supplyPacketShippedDate"]);
	}

	if (!empty(trim($post["lienHolderName"]))) {
		$Lien_Holder_Name = trim($post["lienHolderName"]);
		$Lien_Holder_Name = ucwords($Lien_Holder_Name);
	}

	if (!empty(trim($post["lienHolderEmail"]))) {
		$Lien_Holder_Email = trim($post["lienHolderEmail"]);
	}

	if (!empty(trim($post["lienHolderAddress"]))) {
		$Lien_Holder_Address = trim($post["lienHolderAddress"]);
		$Lien_Holder_Address = ucwords($Lien_Holder_Address);
	}

	if (!empty(trim($post["lienHolderCity"]))) {
		$Lien_Holder_City = trim($post["lienHolderCity"]);
		$Lien_Holder_City = ucwords($Lien_Holder_City);
	}

	if (!empty(trim($post["lienHolderState"]))) {
		$Lien_Holder_State_Province = trim($post["lienHolderState"]);
		$Lien_Holder_State_Province = ucwords($Lien_Holder_State_Province);
	}

	if (!empty(trim($post["lienHolderZip"]))) {
		$Lien_Holder_Postal_Code = trim($post["lienHolderZip"]);
	}

	if (!empty(trim($post["lienHolderPhone"]))) {
		$Lien_Holder_Phone_Number = trim($post["lienHolderPhone"]);
	}

	if (!empty(trim($post["isQuote"]))) {
		$isQuote = trim($post["isQuote"]);
	}


	// Get some other values from session to support Agency concept
	$roleID = $_SESSION["roleID"];
	$isVTAgent = $_SESSION["isVTAgent"];
	$vtAgentPersID = $_SESSION["vtAgentPersID"];
	$isAgencyAgent = $_SESSION["isAgencyAgent"];
	$agencyAccountID = $_SESSION["agencyAccountID"];
	$agencyAgentPersID = $_SESSION["agencyAgentPersID"];

	if(isset($_SESSION["persID"]) && $_SESSION["persID"]!=""){
		$persID = $_SESSION["persID"];
	}else{
		$persID = 0;
	}


		/* Prepare an insert statement to create a Warranty entry */
		$sqlString = "INSERT INTO New_Warranty_Temp (Acct_ID,Customer_Name,Customer_Email,Customer_Address,";
		$sqlString .= "Customer_City,Customer_State,Customer_Zip,Customer_Phone,Customer_Sales_Channel,Contract_Number,";
		$sqlString .= "Agreement_Date,Vehicle_Manufacturer_Name,Vehicle_Gross_Weight,Vehicle_Type,Vehicle_Vin_Number,Vehicle_Year,";
		$sqlString .= "Vehicle_Make,Vehicle_Model,Engine_Make,Engine_Model,Engine_Serial,Transmission_Make,";
		$sqlString .= "Transmission_Model,Transmission_Serial,Odometer_Reading,Odometer_Miles_Or_KM,ECM_Reading,";
		$sqlString .= "ECM_Miles_Or_KM,APU_Engine_Make,APU_Engine_Model,APU_Engine_Year,APU_Engine_Serial,";
		$sqlString .= "Vehicle_New_Flag,Vehicle_Description,Tier_Type,Apparatus_Equipment_Package,Aerial_Package,";
		$sqlString .= "Coverage_Term,Small_Goods_Package,Supply_Packet_To_Be_Shipped,Supply_Packet_Left,";
		$sqlString .= "Supply_Packet_Shipped_Date,Lien_Holder_Name,Lien_Holder_Email,Lien_Holder_Address,";
		$sqlString .= "Lien_Holder_City,Lien_Holder_State_Province,Lien_Holder_Postal_Code,Lien_Holder_Phone_Number,";
		$sqlString .= "Dealer_Signature,Dealer_Signature_Name,Dealer_Signature_Date,Customer_Signature,";
		$sqlString .= "Customer_Signature_Name,Customer_Signature_Date,";
		$sqlString .= "Warranty_Status,Created_Date) values ";
		$sqlString .= "(?,?,?,?,?,?,?,?,?,'0',"; // up to Contract_Number
		$sqlString .= "?,?,?,?,?,?,?,?,?,?,?,?,"; // up to Transmission_Make
		$sqlString .= "?,?,?,?,?,?,?,?,?,?,"; // up to APU_Engine_Serial
		$sqlString .= "?,?,?,?,?,?,?,?,?,"; // up to Supply_Packet_Left
		$sqlString .= "?,?,?,?,?,?,?,?,"; // up to Lien_Holder_Phone_Number
		$sqlString .= "?,?,?,?,?,?,"; // up to Customer_Signature
		$sqlString .= "'draft',NOW())";

		$stmt = mysqli_prepare($link, $sqlString);

		/* Bind variables to parameters */
		$val1 = $dealerID;
		$val2 = $customerName;
		$val3 = $customerEmail;
		$val4 = $customerAddress;
		$val5 = $customerCity;
		$val6 = $customerState;
		$val7 = $customerZip;
		$val8 = $customerPhone;
		$val9 = $customerSalesChannel;
		$val10 = $agreementDate;
		$val11 = $Vehicle_Manufacturer_Name;
		$val12 = $Vehicle_Gross_Weight;
		$val13 = $Vehicle_Type;
		$val14 = $Vehicle_Vin_Number;
		$val15 = $Vehicle_Year;  //int
		$val16 = $Vehicle_Make;
		$val17 = $Vehicle_Model;
		$val18 = $Engine_Make;
		$val19 = $Engine_Model;
		$val20 = $Engine_Serial;
		$val21 = $Transmission_Make;
		$val22 = $Transmission_Model;
		$val23 = $Transmission_Serial;
		$val24 = $Odometer_Reading;  //int
		$val25 = $Odometer_Miles_Or_KM;
		$val26 = $ECM_Reading;  //int
		$val27 = $ECM_Miles_Or_KM;
		$val28 = $APU_Engine_Make;
		$val29 = $APU_Engine_Model;
		$val30 = $APU_Engine_Year;  //int
		$val31 = $APU_Engine_Serial;
		$val32 = $Vehicle_New_Flag;
		$val33 = $Vehicle_Description;
		$val34 = $Tier_Type;
		$val35 = $Apparatus_Equipment_Package;
		$val36 = $Aerial_Package;
		$val37 = $Coverage_Term;  //int
		$val38 = $smallGoodsPackage;
		$val39 = $Supply_Packet_To_Be_Shipped;
		$val40 = $Supply_Packet_Left;
		$val41 = $Supply_Packet_Shipped_Date;
		$val42 = $Lien_Holder_Name;
		$val43 = $Lien_Holder_Email;
		$val44 = $Lien_Holder_Address;
		$val45 = $Lien_Holder_City;
		$val46 = $Lien_Holder_State_Province;
		$val47 = $Lien_Holder_Postal_Code;
		$val48 = $Lien_Holder_Phone_Number;
		$val49 = $Dealer_Signature;
		$val50 = $Dealer_Signature_Name;
		$val51 = $Dealer_Signature_Date;
		$val52 = $Customer_Signature;
		$val53 = $Customer_Signature_Name;
		$val54 = $Customer_Signature_Date;


		mysqli_stmt_bind_param($stmt, "isssssssssssssissssssssisisssissssssisssssssssssssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22, $val23, $val24, $val25, $val26, $val27, $val28, $val29, $val30, $val31, $val32, $val33, $val34, $val35, $val36, $val37, $val38, $val39, $val40, $val41, $val42, $val43, $val44, $val45, $val46, $val47, $val48, $val49, $val50, $val51, $val52, $val53, $val54);


		/* Execute the statement */
		//$result = mysqli_stmt_execute($stmt);

		// Get the newly inserted PK ID
		//if ($result) {
		//	$last_id = mysqli_insert_id($link);
		//}



		/******** INSERT WARRANTY DETAILS INTO PROPER TABLES ********/

		/* Prepare an insert statement to create a Cntrct_Dim entry for this new Warranty */
		$stmt = mysqli_prepare($link, "INSERT INTO Cntrct_Dim (Cntrct_type_cd,Cntrct_type_desc,Qte_Flg,Cstmr_Nme,
									   Contract_Date,Sply_Pkt_To_Be_Shipd_Flg,Sply_Pkt_Left_Flg,Cntrct_Lvl_Cd,Cntrct_Lvl_Desc,
									   AEP_Flg,Aerial_Flg,APU_Flg,Small_Goods_Pkg_Flg,Cntrct_Term_Mnths_Nbr,
									   Cstmr_Eml,Cstmr_Addrs,Cstmr_Cty,Cstmr_Ste,Cstmr_Pstl,Cstmr_Phn,
									   Lien_Nme,Lien_Eml,Lien_Addrs,Lien_Cty,Lien_Ste,Lien_Pstl,Lien_Phn,Srvc_Veh_Flg,PO_Nbr,
									   Wrap_Flg,Created_Date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");

		// Data processing
		if ($Tier_Type == "S") {
			$Tier_Type_Desc = "Squad";
		} else if ($Tier_Type == "B") {
			$Tier_Type_Desc = "Battalion";
		} else {
			$Tier_Type_Desc = "ERROR";
		}

		// If we are doing a 'wrap' type, then use the wrap term,
		//  otherwise use the original coverage term value.
		if($wrap_program=="Y"){
			$termValue = $wrap_Program_Term;
		}else{
			$termValue = $Coverage_Term;
		}

//echo "wrap_program=".$wrap_program;
//echo "<br />";
//echo "wrap_Program_Term=".$wrap_Program_Term;
//die();
		// Set quote or draft flags accordingly
		if ($isQuote == "Y") {
			$val1 = "WQ";
			$val2 = "Warranty Quote";
			$val3 = "Y";
			$val4 = $customerName;
			$val5 = date('Y-m-d', strtotime($agreementDateForInsert));
			$val6 = $Supply_Packet_To_Be_Shipped;
			$val7 = $Supply_Packet_Left;
			$val8 = $Tier_Type;
			$val9 = $Tier_Type_Desc;
			$val10 = $Apparatus_Equipment_Package;
			$val11 = $Aerial_Package;
			$val12 = $APU_Flg;
			$val13 = $smallGoodsPackage;
			$val14 = $termValue;
		} else {
			$val1 = "WD";
			$val2 = "Warranty";
			$val3 = "N";
			$val4 = $customerName;
			$val5 = date('Y-m-d', strtotime($agreementDateForInsert));
			$val6 = $Supply_Packet_To_Be_Shipped;
			$val7 = $Supply_Packet_Left;
			$val8 = $Tier_Type;
			$val9 = $Tier_Type_Desc;
			$val10 = $Apparatus_Equipment_Package;
			$val11 = $Aerial_Package;
			$val12 = $APU_Flg;
			$val13 = $smallGoodsPackage;
			$val14 = $termValue;
		}

		$val15 = $customerEmail;
		$val16 = $customerAddress;
		$val17 = $customerCity;
		$val18 = $customerState;
		$val19 = $customerZip;
		$val20 = $customerPhone;
		$val21 = $Lien_Holder_Name;
		$val22 = $Lien_Holder_Email;
		$val23 = $Lien_Holder_Address;
		$val24 = $Lien_Holder_City;
		$val25 = $Lien_Holder_State_Province;
		$val26 = $Lien_Holder_Postal_Code;
		$val27 = $Lien_Holder_Phone_Number;
		$val28 = $Srvc_Veh_Flg;
		$val29 = $wrap_program;
		$val30 = $customerPO;

		mysqli_stmt_bind_param($stmt, "sssssssssssssisssissssssisssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22, $val23, $val24, $val25, $val26, $val27, $val28, $val29, $val30);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the Contract_Dim_ID of the new contract dim entry.
		$contract_dim_ID = mysqli_insert_id($link);

		$stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim set Wrap_Flg=? WHERE Cntrct_Dim_ID = ?");
		$val1 = $wrap_program;
		$val2 = $contract_dim_ID;
		mysqli_stmt_bind_param($stmt, "si", $val1,$val2);
		$result = mysqli_stmt_execute($stmt);



		/* Prepare an insert statement to create a Veh entry for this new Warranty */
		$stmt = mysqli_prepare($link, "INSERT INTO Veh (Veh_Mk_Cd,Veh_Model_Cd,Veh_Model_Yr_Cd,
									   Veh_Eng_Mk_CD,veh_Eng_Model_Cd,Veh_Eng_Ser_Nbr,
									   Veh_Gross_Wgt_Cnt,Veh_Type_Nbr,Veh_New_Flg,
									   Veh_Trnsmsn_Ser_nbr,Veh_Trnsmsn_Mk_Cd,Veh_Trnsmsn_Model_Cd,
									   Veh_APU_Eng_Ser_nbr,Veh_APU_Eng_Mk_Cd,Veh_APU_Eng_Model_Cd,Veh_APU_Eng_Yr_Cd,
									   OdoMtr_Read_Miles_Cnt,OdoMtr_Read_Kms_Cnt,ECM_Read_Miles_Cnt,ECM_Read_Kms_Cnt,Veh_Desc,
									   Veh_Id_Nbr,Veh_Eng_Hours,Veh_APU_Hours)
									   VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

		// Data Prep
		if ($Odometer_Miles_Or_KM == "km") {
			$OdoMtr_Read_Miles_Cnt = 0;
			$OdoMtr_Read_Kms_Cnt = $Odometer_Reading;
		} else {
			$OdoMtr_Read_Miles_Cnt = $Odometer_Reading;
			$OdoMtr_Read_Kms_Cnt = 0;
		}

		if ($ECM_Miles_Or_KM == "km") {
			$ECM_Read_Miles_Cnt = 0;
			$ECM_Read_Kms_Cnt = $ECM_Reading;
		} else {
			$ECM_Read_Miles_Cnt = $ECM_Reading;
			$ECM_Read_Kms_Cnt = 0;
		}


		// Data processing

		$val1 = $Vehicle_Make;
		$val2 = $Vehicle_Model;
		$val3 = $Vehicle_Year;
		$val4 = $Engine_Make;
		$val5 = $Engine_Model;
		$val6 = $Engine_Serial;
		$val7 = $Vehicle_Gross_Weight;
		$val8 = $Vehicle_Type;
		$val9 = $Vehicle_New_Flag;
		$val10 = $Transmission_Serial;
		$val11 = $Transmission_Make;
		$val12 = $Transmission_Model;
		$val13 = $APU_Engine_Serial;
		$val14 = $APU_Engine_Make;
		$val15 = $APU_Engine_Model;
		$val16 = $APU_Engine_Year;
		$val17 = $OdoMtr_Read_Miles_Cnt;
		$val18 = $OdoMtr_Read_Kms_Cnt;
		$val19 = $ECM_Read_Miles_Cnt;
		$val20 = $ECM_Read_Kms_Cnt;
		$val21 = $Vehicle_Description;
		$val22 = $Vehicle_Vin_Number;
		$val23 = $Engine_Hours;
		$val24 = $APU_Hours;

		mysqli_stmt_bind_param($stmt, "sssssssissssssssssssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10, $val11, $val12, $val13, $val14, $val15, $val16, $val17, $val18, $val19, $val20, $val21, $val22, $val23, $val24);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the Contract_ID of the new contract dim entry.
		$veh_ID = mysqli_insert_id($link);

		/* Prepare an insert statement to create a Cntrct entry for this new Warranty */
		$stmt = mysqli_prepare($link, "INSERT INTO Cntrct (Cntrct_Nbr,Cntrct_Sales_Chnl,Sply_Pkt_Shipd_Dte,
									   Cntrct_Dim_ID,Veh_ID,Mfr_Acct_ID,Pers_Who_Signd_Cntrct_ID,Dlr_Agt_Prsn_ID,
									   Qte_Dt,Created_Date)
									   VALUES (?,?,?,?,?,?,?,?,?,NOW())");

		// Data processing
		// Set quote or draft flags accordingly
		if ($isQuote == "Y") {
			$val1 = "";
			$val2 = $customerSalesChannel;
			$val3 = $Supply_Packet_Shipped_Date;
			$val4 = $contract_dim_ID;
			$val5 = $veh_ID;
			$val6 = $Acct_ID;
			$val7 = $dealerAgentID;
			$val8 = $persID;
			$val9 = $agreementDateForInsert;
		} else {
			$val1 = "";
			$val2 = $customerSalesChannel;
			$val3 = $Supply_Packet_Shipped_Date;
			$val4 = $contract_dim_ID;
			$val5 = $veh_ID;
			$val6 = $Acct_ID;
			$val7 = $dealerAgentID;
			$val8 = $persID;
			$val9 = '0000-00-00 00:00:00';
		}

		mysqli_stmt_bind_param($stmt, "sssiiiiis", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the Contract_ID of the new contract dim entry.
		$contract_ID = mysqli_insert_id($link);


		$_SESSION["warrantyID"] = $contract_ID;

		// If the admin performing this action is an Agency Sales Agent, then we need to update a few things
		if($isAgencyAgent=="Y"){
			$stmt = mysqli_prepare($link, "UPDATE Cntrct set Agency_Acct_ID=?, Agency_Sales_Agt_Prsn_Id=? WHERE
			                               Cntrct_ID = ?");

			$val1 = $agencyAccountID;
			$val2 = $agencyAgentPersID;
			$val3 = $contract_ID;

			mysqli_stmt_bind_param($stmt, "iii", $val1, $val2, $val3);

			$result = mysqli_stmt_execute($stmt);

		}else if($isVTAgent=="Y"){

			$stmt = mysqli_prepare($link, "UPDATE Cntrct set VT_Sales_Agt_Prsn_ID=? WHERE
			                               Cntrct_ID = ?");

			$val1 = $vtAgentPersID;
			$val2 = $contract_ID;

			mysqli_stmt_bind_param($stmt, "ii", $val1, $val2);

			$result = mysqli_stmt_execute($stmt);

		}

		// If a dealer agent is creating this, then we need to populate the Dlr_Agt_Prsn_ID column accordingly
		if($roleID==6){
			$stmt = mysqli_prepare($link, "UPDATE Cntrct set Dlr_Agt_Prsn_ID=? WHERE
			                               Cntrct_ID = ?");

			if(isset($_SESSION["persID"]) && $_SESSION["persID"]!=""){
				$persID = $_SESSION["persID"];
			}else{
				$persID = 0;
			}
			$val1 = $persID;
			$val2 = $contract_ID;

			mysqli_stmt_bind_param($stmt, "ii", $val1, $val2);

			$result = mysqli_stmt_execute($stmt);

		}

		/**** BUSINESS LOGIC CALCULATIONS ****/

		// Look up the base values from Wrnty_Std_Prcg based on term, type and tier
		if($wrap_program == 'N')
		{
			$warrantyBasePricingResult = selectWarrantyBasePricing($link, $Coverage_Term, $Vehicle_Type, $Tier_Type);
				
		} else{
			$warrantyBasePricingResult = selectwrapWarrantyBasePricing($link,$termValue,$Vehicle_Type, $Tier_Type);
		}
	
		$row = mysqli_fetch_assoc($warrantyBasePricingResult);

		$Sales_Agt_Cost_Amt = $row["Sales_Agt_Cost_Amt"];
		$Sales_Agt_Commission_Amt = $row["Sales_Agt_Commission_Amt"];
		$Dlr_Cost_Amt = $row["Dlr_Cost_Amt"];
		$Dlr_Mrkp_Max_Amt = $row["Dlr_Mrkp_Max_Amt"];
		$Dlr_Mrkp_Actl_Amt = $row["Dlr_Mrkp_Max_Amt"];
		$MSRP_Amt = $row["MSRP_Amt"];

		// Update the contract with these values
		$stmt = mysqli_prepare($link, "UPDATE Cntrct SET Sales_Agt_Cost_Amt=?, Sales_Agt_Commission_Amt=?,
		                               Dlr_Cost_Amt=?, Dlr_Mrkp_Max_Amt=?, Dlr_Mrkp_Actl_Amt=?, MSRP_Amt=? WHERE Cntrct_ID=?");

		$val1 = $Sales_Agt_Cost_Amt;
		$val2 = $Sales_Agt_Commission_Amt;
		$val3 = $Dlr_Cost_Amt;
		$val4 = $Dlr_Mrkp_Max_Amt;
		$val5 = $Dlr_Mrkp_Actl_Amt;
		$val6 = $MSRP_Amt;
		$val7 = $contract_ID;

		mysqli_stmt_bind_param($stmt, "iiiiiii", $val1, $val2, $val3, $val4, $val5, $val6, $val7);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


		// Look up the Additional Standard Pricing values
		$isAEP = "N";
		$isAPU = "N";
		$isAER = "N";
		$isOLD = "N";

		if ($APU_Flg == "Y") {
			$isAPU = "Y";
		}
		if ($Apparatus_Equipment_Package == "Y") {
			$isAEP = "Y";
		}
		if ($Aerial_Package == "Y") {
			$isAER = "Y";
		}

		// If the vehicle is over 15 years old, consider it OLD
		if (is_numeric($Vehicle_Year)) {
			if ((date("Y") - $Vehicle_Year) > 14) {
				$isOLD = "Y";
			}
		} else {
			$isOLD = "N";
		}

		// Look up the MAX values for any add-on included, and set the 'ACTL' column in Cntrct accordingly
		$Addl_Dlr_Mrkp_Actl_APU_Amt = 0;
		$Addl_Dlr_Mrkp_Actl_AEP_Amt = 0;
		$Addl_Dlr_Mrkp_Actl_AER_Amt = 0;
		if($isAPU=="Y"){
			$query = "SELECT Dlr_Mrkp_Max_Amt FROM Addl_Std_Prcg WHERE Addl_Type_Cd = 'APU'";
			$result = $link->query($query);
			if ($result->num_rows > 0) {
				$row = mysqli_fetch_assoc($result);
				$Addl_Dlr_Mrkp_Actl_APU_Amt = $row["Dlr_Mrkp_Max_Amt"];
			}
		}

		if($isAEP=="Y"){
			$query = "SELECT Dlr_Mrkp_Max_Amt FROM Addl_Std_Prcg WHERE Addl_Type_Cd = 'AEP'";
			$result = $link->query($query);
			if ($result->num_rows > 0) {
				$row = mysqli_fetch_assoc($result);
				$Addl_Dlr_Mrkp_Actl_AEP_Amt = $row["Dlr_Mrkp_Max_Amt"];
			}
		}

		if($isAER=="Y"){
			$query = "SELECT Dlr_Mrkp_Max_Amt FROM Addl_Std_Prcg WHERE Addl_Type_Cd = 'AER'";
			$result = $link->query($query);
			if ($result->num_rows > 0) {
				$row = mysqli_fetch_assoc($result);
				$Addl_Dlr_Mrkp_Actl_AER_Amt = $row["Dlr_Mrkp_Max_Amt"];
			}
		}


		$addlStdPrcgResult = selectAddlStdPrcgSum($link, $isAEP, $isAPU, $isAER, $isOLD);

		if($addlStdPrcgResult)
		{

			$row = mysqli_fetch_assoc($addlStdPrcgResult);
			$Sales_Agt_Cost_Amt = $row["Addl_Sales_Agt_Cost_Amt"];
			$Sales_Agt_Commission_Amt = $row["Addl_Sales_Agt_Commission_Amt"];
			$Dlr_Cost_Amt = $row["Addl_Dlr_Cost_Amt"];
			$Dlr_Mrkp_Max_Amt = $row["Addl_Dlr_Mrkp_Max_Amt"];
			$Dlr_Mrkp_Actl_Amt = $row["Addl_Dlr_Mrkp_Max_Amt"];
			$MSRP_Amt = $row["Addl_MSRP_Amt"];

			// Update the contract with these values
			$stmt = mysqli_prepare($link, "UPDATE Cntrct SET Addl_Sales_Agt_Cost_Amt=?, Addl_Sales_Agt_Commission_Amt=?,
										   Addl_Dlr_Cost_Amt=?, Addl_Dlr_Mrkp_Max_Amt=?, Addl_Dlr_Mrkp_Actl_Amt=?,
										   Addl_MSRP_Amt=?, Addl_Dlr_Mrkp_Actl_APU_Amt=?, Addl_Dlr_Mrkp_Actl_AEP_Amt=?,
										   Addl_Dlr_Mrkp_Actl_AER_Amt=? WHERE Cntrct_ID=?");

			$val1 = $Sales_Agt_Cost_Amt;
			$val2 = $Sales_Agt_Commission_Amt;
			$val3 = $Dlr_Cost_Amt;
			$val4 = $Dlr_Mrkp_Max_Amt;
			$val5 = $Dlr_Mrkp_Actl_Amt;
			$val6 = $MSRP_Amt;
			$val7 = $Addl_Dlr_Mrkp_Actl_APU_Amt;
			$val8 = $Addl_Dlr_Mrkp_Actl_AEP_Amt;
			$val9 = $Addl_Dlr_Mrkp_Actl_AER_Amt;
			$val10 = $contract_ID;

			mysqli_stmt_bind_param($stmt, "iiiiiiiiii", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8, $val9, $val10);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);

			// Small goods values will be populated into the contract after
			//  the Small Goods process is complete, in the next step below


		} // if($addlStdPrcgResult) //

		// Call our function to updated the TOTALS columns in the Cntrct table, which is the sum
		//  of base + add-on + small goods.  Need to refresh these totals whenever changes are made
		//  Since we are creating the Contract at this time, pass in 'Y' for the 'include actuals'
		//   argument.  This will update the Tot_Dlr_Mrkp_Act_Amt, which we want to avoid updating
		//   in the future so it doesn't reset the custom selection made by a dealer.
		$totalUpdateResult = updateWarrantyTotals($link,$contract_ID,"Y");

		return $contract_ID;
}

function copySmallGoods($link, $warrantyid)
{
	$dealerID = $_SESSION["id"];
	$adminID = $_SESSION["admin_id"];
	$warrantyID = $_SESSION["warrantyID"];
	$loopCounter=0;
	$sql = "SELECT * FROM Sml_Goods_Cvge WHERE Cntrct_ID=".$warrantyid;
	$smallGoodsResult = $link->query($sql);

	$numRows = mysqli_num_rows($smallGoodsResult);
	if ($numRows > 0) {
		while($row = mysqli_fetch_assoc($smallGoodsResult)) {
			$loopCounter++;
			// $currentSelectionsArray[$row["Item_Cat_Type_Cd"]]=$row["Gnrc_Item_Cat_Qty_Cnt"];4


			/* Prepare an insert statement to create a Warranty entry */
			$sqlString  = "INSERT INTO Sml_Goods_Cvge (Sml_Goods_Gnrc_Prcg_ID,item_cat_type_cd,item_cat_type_desc,Gnrc_Item_Cat_Qty_Cnt,";
			$sqlString .= "Sales_Agt_Cst_Amt,Sales_Agt_Comssn_Amt,Dlr_Cst_Amt,Dlr_Mrkp_Max_Amt,Actl_Prc_Amt,Cntrct_ID) ";
			$sqlString .= "values (?,?,?,?,?,?,?,?,?,?)";

			$stmt = mysqli_prepare($link, $sqlString);

			/* Bind variables to parameters */
			$val1 = $row["Sml_Goods_Gnrc_Prcg_ID"];
			$val2 = $row["Item_Cat_Type_Cd"];
			$val3 = $row["Item_Cat_Type_Desc"];
			$val4 = $row["Gnrc_Item_Cat_Qty_Cnt"];
			$val5 = $row["Sales_Agt_Cst_Amt"];
			$val6 = $row["Sales_Agt_Comssn_Amt"];
			$val7 = $row["Dlr_Cst_Amt"];
			$val8 = $row["Dlr_Mrkp_Max_Amt"];
			$val9 = $row["Actl_Prc_Amt"];
			$val10 = $warrantyID;

			mysqli_stmt_bind_param($stmt, "issiiiiiii", $val1,$val2,$val3,$val4,$val5,$val6,$val7,$val8,$val9,$val10);

			/* Execute the statement */
			$result = mysqli_stmt_execute($stmt);
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
					if($item["Gnrc_Item_Cat_Qty_Cnt"] > 0)
					{
						if($item["Item_Cat_Type_Cd"] == 'A')
						{
							$data['QUANTITY SUBMITTED A'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							//$data['QUANTITY APPROVED A'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'B')
						{
							$data['QUANTITY SUBMITTED B'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							//$data['QUANTITY APPROVED B'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'C')
						{
							$data['QUANTITY SUBMITTED C'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							// $data['QUANTITY APPROVED C'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'D')
						{
							$data['QUANTITY SUBMITTED D'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							//$data['QUANTITY APPROVED D'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'E')
						{
							$data['QUANTITY SUBMITTED E'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							//$data['QUANTITY APPROVED E'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted +=$item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'F')
						{
							$data['QUANTITY SUBMITTED F'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							//$data['QUANTITY APPROVED F'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
										else if($item["Item_Cat_Type_Cd"] == 'G')
						{
							$data['QUANTITY SUBMITTED G'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							//$data['QUANTITY APPROVED G'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
										else if($item["Item_Cat_Type_Cd"] == 'H')
						{
							$data['QUANTITY SUBMITTED I'] =$item["Gnrc_Item_Cat_Qty_Cnt"];
							//$data['QUANTITY APPROVED I'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'I')
						{
							$data['QUANTITY SUBMITTED J'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							///$data['QUANTITY APPROVED J'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}else if($item["Item_Cat_Type_Cd"] == 'J')
						{
							$data['QUANTITY SUBMITTED K'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							//$data['QUANTITY APPROVED K'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'K')
						{
							$data['QUANTITY SUBMITTED L'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							// $data['QUANTITY APPROVED L'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'L')
						{
							$data['QUANTITY SUBMITTED M'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							//$data['QUANTITY APPROVED M'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'M')
						{
							$data['QUANTITY SUBMITTED N'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							// $data['QUANTITY APPROVED N'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'N')
						{
							$data['QUANTITY SUBMITTED O'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							//$data['QUANTITY APPROVED O'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'O')
						{
							$data['QUANTITY SUBMITTED P'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							// $data['QUANTITY APPROVED P'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'P')
						{
							$data['QUANTITY SUBMITTED Q'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							// $data['QUANTITY APPROVED Q'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'Q')
						{
							$data['QUANTITY SUBMITTED R'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							//$data['QUANTITY APPROVED R'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'R')
						{
							$data['QUANTITY SUBMITTED S'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							// $data['QUANTITY APPROVED S'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'S')
						{
							$data['QUANTITY SUBMITTED T'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							// $data['QUANTITY APPROVED T'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'T')
						{
							$data['QUANTITY SUBMITTED U'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							//  $data['QUANTITY APPROVED U'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'U')
						{
							$data['QUANTITY SUBMITTED V'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							// $data['QUANTITY APPROVED V'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'V')
						{
							$data['QUANTITY SUBMITTED W'] =$item["Gnrc_Item_Cat_Qty_Cnt"];
							//$data['QUANTITY APPROVED W'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'W')
						{
							$data['QUANTITY SUBMITTED X'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							//  $data['QUANTITY APPROVED X'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}else if($item["Item_Cat_Type_Cd"] == 'X')
						{
							$data['QUANTITY SUBMITTED Y'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							// $data['QUANTITY APPROVED Y'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'Y')
						{
							$data['QUANTITY SUBMITTED Z'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							// $data['QUANTITY APPROVED Z'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						else if($item["Item_Cat_Type_Cd"] == 'Z')
						{
							$data['QUANTITY SUBMITTED H'] = $item["Gnrc_Item_Cat_Qty_Cnt"];
							// $data['QUANTITY APPROVED H'] = '$'.number_format($item["MSRP_Amt"] * $_POST['quantity_'.$item["Sml_Goods_Gnrc_Prcg_ID"]],0);
							$totalQuantitySubmitted += $item["Gnrc_Item_Cat_Qty_Cnt"];
						}
						$totalAmount += $item['Dlr_Cst_Amt'] * $item["Gnrc_Item_Cat_Qty_Cnt"];
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
		$pdfResult = createWarrantyPDF($link,$warrantyID,"Y");

		header("location: warranty_pending.php?showQuotes=Y");
		exit;
	}




	die(print_r($currentSelectionsArray));



}


?>