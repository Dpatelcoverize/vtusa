<?php
//
// File: my_dealers.php
// Author: Charles Parry
// Date: 8/3/2022
//
//
//
// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "My Dealers";
$pageTitle = "My Dealers";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";


// Variables.
$userID = "";
$roleID = "";
$dealerID = "";
$agreementDate = "";
$dealerName = "";
$dealerAddress1 = "";
$dealerAddress2 = "";
$dealerCity = "";
$dealerState = "";
$dealerZip = "";
$dealerLocationID = "";
$Dlr_Loc_Dim_ID = "";

$personFirstName = "";
$personLastName = "";
$personEmail = "";
$personPhone = "";

$notesField = "";

$form_err = "";


if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


// Security
$security_01_all_dealers = $_SESSION["security_01_all_dealers"];
$security_02_dealership_customers = $_SESSION["security_02_dealership_customers"];
$security_03_dealership_agreement_info = $_SESSION["security_03_dealership_agreement_info"];
$security_04_dealership_agents_customers = $_SESSION["security_04_dealership_agents_customers"];
$security_05_all_quotes = $_SESSION["security_05_all_quotes"];
$security_06_dealers_quotes = $_SESSION["security_06_dealers_quotes"];
$security_07_customers_quotes = $_SESSION["security_07_customers_quotes"];
$security_08_all_warranties = $_SESSION["security_08_all_warranties"];
$security_09_dealers_warranties = $_SESSION["security_09_dealers_warranties"];
$security_10_customers_warranties = $_SESSION["security_10_customers_warranties"];



// Get the adminID from session, or fail.
if (!(isset($_SESSION["admin_id"]))) {
    header("location: index.php");
    exit;
} else {
    $adminID = $_SESSION["admin_id"];
}

// Get the adminID from session, or fail.
if (!(isset($_SESSION["userID"]))) {
    header("location: index.php");
    exit;
} else {
    $userID = $_SESSION["userID"];
}




// At this time, only allow VT admins to use this page.
//  So, get the Role_ID from session, and only allow roleID=1 (admin).
if (!(isset($_SESSION["role_ID"]))) {
    header("location: index.php");
    exit;
} else {
    $roleID = $_SESSION["role_ID"];
	if($roleID!=1){
		/*************** not enforced currently
	    header("location: index.php");
	    exit;
		*************/
	}
}


// Log in as a dealer
if(isset($_GET["acct_ID"])){

	$localAcctID = $_GET["acct_ID"];
	// Primary Contact Query
/*
	$persQuery = "SELECT * FROM Acct a, Pers p, Email e WHERE p.Acct_ID=".$localAcctID." AND
				  p.Cntct_Prsn_For_Acct_Flg='Y' AND
				  p.Pers_ID = e.Pers_ID AND
				  p.Acct_ID = a.Acct_ID";
*/
	$persQuery = "SELECT * FROM Usr_Loc ul, Acct a, Pers p, Email e WHERE ul.Dlr_Acct_ID=".$localAcctID." AND
	              ul.Pers_ID = p.Pers_ID AND
				  p.Cntct_Prsn_For_Acct_Flg='Y' AND
				  p.Pers_ID = e.Pers_ID AND
				  p.Acct_ID = a.Acct_ID";

	$persResult = $link->query($persQuery);
	if (mysqli_num_rows($persResult) > 0) {
		$persRow = mysqli_fetch_assoc($persResult);
		$personFirstName = $persRow["Pers_Frst_Nm"];
		$persID = $persRow["Pers_ID"];
		$personLastName = $persRow["Pers_Last_Nm"];
		$personEmail = $persRow["Email_URL_Desc"];
		$multipleLocations = $persRow["Multiple_Locations"];

		// Set session values that will allow the currently logged in ADMIN
		//  to simulate the session of a logged in DEALER.
		$_SESSION["loggedin"] = true;
		$_SESSION["persID"] = $persID;
		//$_SESSION["isContactPerson"] = "Y";
		$_SESSION["id"] = $localAcctID;
		$_SESSION["username"] = $personEmail;
		$_SESSION["userType"] = "dealer";
		$_SESSION["dealer_multiple_locations"] = $multipleLocations;

	}

	header("location: index.php");
	exit;


}

if(isset($_GET["delete"])){
	$id = $_GET["delete"];
	$stmt = mysqli_prepare($link, "UPDATE Users SET isDeleted = 'Y'  WHERE Acct_ID = ?");
    
	mysqli_stmt_bind_param($stmt, "s", $id);
	$result = mysqli_stmt_execute($stmt);

	if($result){
		header("location: my_dealers.php");
	}
}
if(isset($_GET["active"])){
	$id = $_GET["active"];
	$stmt = mysqli_prepare($link, "UPDATE Users SET isDeleted = 'N'  WHERE Acct_ID = ?");

    mysqli_stmt_bind_param($stmt, "s", $id);
	$result = mysqli_stmt_execute($stmt);
	if($result){
		header("location: my_dealers.php");
	}
}


require_once("includes/header.php");

if($adminID==1){
//  $userID = 400;
//  $roleID = 3;
//  $security_01_all_dealers = "N";
}

?>

		<!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <!-- row -->
			<div class="container-fluid">
            <?php require_once("includes/common_page_content.php"); ?>
                <div class="row" style="margin-top: 2%;">
                    <div class="col-lg-12">
						<div class="form-group col-md-12">

						</div>
					</div>
				</div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Dealers I have signed up</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-responsive-md" id="finance_table">
                                        <thead>
                                            <tr>
                                                <th>Dealer Name</th>
                                                <th>AR #</th>
                                                <th>Primary Contact Name</th>
                                                <th>Primary Contact Email</th>
												<th>Location</th>
												<th>Is Deleted</th>
												<th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php

// Since we are only allowing VT admins (roleID=1) into this page, and we
//  want VT admins to see all Dealers, we will remove the Sls_Agnt_ID check here.

//  Can be restored later, or on a different page, when we want to filter
//  the list of dealers down to only the ones that pertain to the logged in agent.

// UPDATE: we actually still want all agents to be able to get in, so
//  if the logged in user is a VT admin, do not check Sls_Agnt_ID, otherwise do so.



// if($roleID == 1){
// 	$query = "SELECT * FROM Acct ORDER BY Acct_Nm ASC";
// }else{
// 	$query = "SELECT * FROM Acct WHERE Sls_Agnt_ID = ".$adminID;
// }




if($security_01_all_dealers == "Y"){
/*
	$query = "SELECT a.Acct_Nm,a.Acct_ID,a.Prnt_Acct_ID, ad.St_Addr_1_Desc,
				ad.City_Nm, Assign_Rtlr_Nbr, p.Pers_Frst_Nm,
				p.Pers_Last_Nm, e.Email_URL_Desc  FROM
				Acct a, Addr ad, Cntrct c, Cntrct_Dim cd, Pers p, Email e WHERE
	          	ad.Addr_Type_Cd='Work' AND ad.Prim_Addr_Flg='Y' AND ad.acct_ID = a.acct_ID AND
	          	c.Mfr_Acct_ID = a.Acct_ID AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID AND
	          	cd.Cntrct_Type_Desc is NULL AND
	          	p.Acct_ID = (SELECT COALESCE(a.Prnt_Acct_ID, a.Acct_ID) as tmpAcctID) AND
	          	p.Cntct_Prsn_For_Acct_Flg='Y' AND p.Pers_ID = e.Pers_ID
	          	ORDER BY Acct_Nm ASC";

*/

	$query = "SELECT a.Acct_Nm,a.Acct_ID,a.Prnt_Acct_ID, ad.St_Addr_1_Desc,
				ad.City_Nm, Assign_Rtlr_Nbr, p.Pers_Frst_Nm,
				p.Pers_Last_Nm, e.Email_URL_Desc, u.isDeleted  FROM
				Acct a, Addr ad, Cntrct c, Cntrct_Dim cd, Pers p, Email e, Usr_Loc ul, Users u WHERE
	          	ad.Addr_Type_Cd='Work' AND ad.Prim_Addr_Flg='Y' AND ad.acct_ID = a.acct_ID AND
	          	c.Mfr_Acct_ID = a.Acct_ID AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID AND
	          	cd.Cntrct_Type_Desc is NULL AND
	          	ul.Dlr_Acct_ID = (SELECT COALESCE(a.Prnt_Acct_ID, a.Acct_ID) as tmpAcctID) AND
	          	ul.Pers_ID = p.Pers_ID AND u.Pers_ID = p.Pers_ID AND
	          	p.Cntct_Prsn_For_Acct_Flg='Y' AND p.Pers_ID = e.Pers_ID AND u.isDeleted = 'N'
	          	ORDER BY Acct_Nm ASC";
}else if($security_02_dealership_customers == "Y"){
	// Show all dealers associated with this agency
//	$query = "SELECT Acct_Nm,Acct_ID,Prnt_Acct_ID FROM Acct WHERE Sls_Agnt_ID = ".$adminID;

	// If an agency, find all dealers created by the primary, or any agents.
	if($roleID==5){
		$query = "SELECT a.Acct_Nm, a.Acct_ID, a.Prnt_Acct_ID FROM Acct a WHERE
				a.Acct_ID in (select Acct_ID FROM Acct WHERE Sls_Agnt_ID=".$userID.") OR
				a.Acct_ID in (
				SELECT a.Acct_ID FROM Acct a WHERE Sls_Agnt_ID in (
					SELECT userID FROM Users WHERE Acct_ID = ".$_SESSION["agencyAccountID"]."))";
		//echo "query=".$query;
		//print_r($_SESSION);
		//die();
	}else if($roleID==4){
		$query = "SELECT a.Acct_Nm, a.Acct_ID, a.Prnt_Acct_ID FROM Acct a WHERE
				a.Acct_ID in (select Acct_ID FROM Acct WHERE Sls_Agnt_ID=".$userID.")";

	}else if($roleID==2){
		// Dealer Primary
		$query = "SELECT a.Acct_Nm, a.Acct_ID, a.Prnt_Acct_ID FROM Acct a WHERE
				a.Acct_ID in (select Acct_ID FROM Acct WHERE Sls_Agnt_ID=".$userID.") OR
				a.Acct_ID in (
				SELECT a.Acct_ID FROM Acct a WHERE Sls_Agnt_ID in (
					SELECT userID FROM Users WHERE Acct_ID = ".$acct_id."))";

	}else if($roleID==6){
		// Dealer Agent
		$query = "SELECT a.Acct_Nm, a.Acct_ID, a.Prnt_Acct_ID FROM Acct a WHERE
				a.Acct_ID in (select Acct_ID FROM Acct WHERE Sls_Agnt_ID=".$userID.")";

	}else{
	/*
		$query = "SELECT a.Acct_Nm,a.Acct_ID,a.Prnt_Acct_ID, ad.St_Addr_1_Desc,
				ad.City_Nm, Assign_Rtlr_Nbr, p.Pers_Frst_Nm,
				p.Pers_Last_Nm, e.Email_URL_Desc  FROM
				Acct a, Addr ad, Cntrct c, Cntrct_Dim cd, Pers p, Email e WHERE
				(a.Acct_ID in (select Acct_ID FROM Acct WHERE Sls_Agnt_ID=".$userID.") OR
				a.Acct_ID in (SELECT a.Acct_ID FROM Acct a, Usr_Loc u WHERE u.Dlr_Acct_ID = a.Acct_ID AND u.Usr_ID = ".$userID.")) AND
	          	ad.Addr_Type_Cd='Work' AND ad.Prim_Addr_Flg='Y' AND ad.acct_ID = a.acct_ID AND
	          	c.Mfr_Acct_ID = a.Acct_ID AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID AND
	          	cd.Cntrct_Type_Desc is NULL AND
	          	p.Acct_ID = (SELECT COALESCE(a.Prnt_Acct_ID, a.Acct_ID) as tmpAcctID) AND
	          	p.Cntct_Prsn_For_Acct_Flg='Y' AND p.Pers_ID = e.Pers_ID
	          	ORDER BY Acct_Nm ASC";
*/

		$query = "SELECT a.Acct_Nm, a.Acct_ID, a.Prnt_Acct_ID,
					'' as St_Addr_1_Desc, '' as City_Nm, '' as Assign_Rtlr_Nbr, '' as Pers_Frst_Nm,
				 	'' as Pers_Last_Nm, '' as Email_URL_Desc
				FROM Acct a WHERE
				a.Acct_ID in (select Acct_ID FROM Acct WHERE Sls_Agnt_ID=".$userID.") OR
				a.Acct_ID in (SELECT a.Acct_ID FROM Acct a, Usr_Loc u WHERE u.Dlr_Acct_ID = a.Acct_ID AND u.Usr_ID = ".$userID.")";
	}

//echo "query=".$query;
//die();

}




$personResult = $link->query($query);
$businessAddress = "";
$personFirstName = "";
$personLastName = "";
$personEmail = "";
$dealerIsDeleted = "";




if (mysqli_num_rows($personResult) > 0) {
  // output data of each row
    $loopCounter = 0;
    while ($row = mysqli_fetch_assoc($personResult)) {
        $loopCounter++;
		$acct_ID = $row["Acct_ID"];
		$parent_acct_ID = $row["Prnt_Acct_ID"];
		$dealerARNumber = "";
		$personFirstName = "";
		$personLastName = "";
		$personEmail = "";
		$dealerIsDeleted = $row["isDeleted"];

		// $addressQuery = "SELECT * FROM Addr WHERE Acct_ID=".$row["Acct_ID"]." AND Addr_Type_Cd='Work' AND Prim_Addr_Flg='Y'";
		// $addressResult = $link->query($addressQuery);
		// if (mysqli_num_rows($addressResult) > 0) {
		//     $addressRow = mysqli_fetch_assoc($addressResult);
		// 	$businessAddress = $addressRow["St_Addr_1_Desc"].", ".$addressRow["City_Nm"];
		// }

		/*
		$addressQuery = "SELECT St_Addr_1_Desc,City_Nm FROM Addr WHERE Acct_ID=".$row["Acct_ID"]." AND Addr_Type_Cd='Work' AND Prim_Addr_Flg='Y'";
		$addressResult = $link->query($addressQuery);
		if (mysqli_num_rows($addressResult) > 0) {
		    $addressRow = mysqli_fetch_assoc($addressResult);
			$businessAddress = $addressRow["St_Addr_1_Desc"].", ".$addressRow["City_Nm"];
		}
		*/

		$businessAddress = $row["St_Addr_1_Desc"].", ".$row["City_Nm"];




		// Get AR Number
		// $arQuery = "SELECT * FROM `Cntrct` c, Cntrct_Dim cd WHERE c.`Mfr_Acct_ID`=".$acct_ID." AND
		//             cd.Cntrct_Type_Desc is NULL AND
		//             c.`Cntrct_Dim_ID`=cd.`Cntrct_Dim_ID`;";
		// $arResult = $link->query($arQuery);
		// if (mysqli_num_rows($arResult) > 0) {
		//     $arRow = mysqli_fetch_assoc($arResult);
		// 	$dealerARNumber = $arRow["Assign_Rtlr_Nbr"];
		// }

		/*
		$arQuery = "SELECT Assign_Rtlr_Nbr FROM `Cntrct` c, Cntrct_Dim cd WHERE c.`Mfr_Acct_ID`=".$acct_ID." AND
		            cd.Cntrct_Type_Desc is NULL AND
		            c.`Cntrct_Dim_ID`=cd.`Cntrct_Dim_ID`;";
		$arResult = $link->query($arQuery);
		if (mysqli_num_rows($arResult) > 0) {
		    $arRow = mysqli_fetch_assoc($arResult);
			$dealerARNumber = $arRow["Assign_Rtlr_Nbr"];
		}
		*/

		$dealerARNumber = $row["Assign_Rtlr_Nbr"];




		// Primary Contact Query
		if($parent_acct_ID!=""){
			$tmpAcctID = $parent_acct_ID;
		}else{
			$tmpAcctID = $row["Acct_ID"];
		}


		// $persQuery = "SELECT * FROM Pers p, Email e WHERE p.Acct_ID=".$tmpAcctID." AND
		//               p.Cntct_Prsn_For_Acct_Flg='Y' AND
		// 			  p.Pers_ID = e.Pers_ID";

		$persQuery = "SELECT Pers_Frst_Nm,Pers_Last_Nm,Email_URL_Desc  FROM Pers p, Email e WHERE p.Acct_ID=".$tmpAcctID." AND
		              p.Cntct_Prsn_For_Acct_Flg='Y' AND
					  p.Pers_ID = e.Pers_ID";


		/*
		$persResult = $link->query($persQuery);
		if (mysqli_num_rows($persResult) > 0) {
		    $persRow = mysqli_fetch_assoc($persResult);
			$personFirstName = $persRow["Pers_Frst_Nm"];
			$personLastName = $persRow["Pers_Last_Nm"];
			$personEmail = $persRow["Email_URL_Desc"];

			// Blank out the primary contact email and name for Bulldog, for any sub locations
			if(substr($row["Acct_Nm"],0,5)==="Bulld"){
				if($row["Acct_ID"]!=1384){
					$personFirstName = "";
					$personLastName = "";
					$personEmail = "";
				}
			}

		}
		*/
		$personFirstName = $row["Pers_Frst_Nm"];
		$personLastName = $row["Pers_Last_Nm"];
		$personEmail = $row["Email_URL_Desc"];

		// Blank out the primary contact email and name for Bulldog, for any sub locations
		if(substr($row["Acct_Nm"],0,5)==="Bulld"){
			if($row["Acct_ID"]!=1384){
				$personFirstName = "";
				$personLastName = "";
				$personEmail = "";
			}
		}


        ?>
<tr>
	<td><a href="my_dealers.php?acct_ID=<?php echo $row["Acct_ID"]; ?>"><?php echo $row["Acct_Nm"]; ?></a></td>
	<td><?php echo $dealerARNumber;?></td>
	<td><?php echo $personFirstName;?> <?php echo $personLastName;?></td>
	<td><?php echo $personEmail;?></td>
	<td><?php echo $businessAddress;?></td>
	<td><?php echo $dealerIsDeleted;?></td>
	<td><?php if ($dealerIsDeleted =='N'){ ?><a href="my_dealers.php?delete=<?php echo $row["Acct_ID"] ?>"><button type="button" class="btn btn-sm btn-danger" >Archive</button></a><?php }elseif($dealerIsDeleted =='Y') { ?> <a href="my_dealers.php?active=<?php echo $row["Acct_ID"] ?>"><button class="btn btn-success"  type="button" >Active</button></a><?php } ?></td>
</tr>

<?php

	}

} else {
    ?>
<tr>
	<td colspan="5">No dealers found, yet.</td>
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

	<!-- Dashboard 1 -->
    <script src="./js/custom.min.js"></script>
	<script src="./js/deznav-init.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.js"></script>
	<script src="js/demo.js"></script>
    <script>
        $(document).ready( function () {
          $('#finance_table').DataTable();
        } );
    </script>

</body>
</html>