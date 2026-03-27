<?php
//
// File: dblib.php
// Author: Charles Parry
// Date: 6/29/2022
//
//
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
	          Veh_Type_Cd=".$warrantyType." AND Tier_Type_Cd='".$warrantyTier."';";
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
?>