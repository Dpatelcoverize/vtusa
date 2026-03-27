<?php
//
// File: dealer_multiple_locations.php
// Author: Charles Parry
// Date: 6/22/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$pageBreadcrumb = "Dealer Locations";
$pageTitle = "Dealer Locations";


// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";


// Variables.
$dealerID = "";
$agreementDate = "";
$dealerName  = "";
$dealerAddress1 = "";
$dealerAddress2 = "";
$dealerCity = "";
$dealerState = "";
$dealerZip = "";

$locationName = "";
$locationAddress1 = "";
$locationAddress2 = "";
$locationCity = "";
$locationState = "";
$locationPostalCode = "";
$locationPhone = "";
$locationFax = "";
$locationPrimaryContactFirstName = "";
$locationPrimaryContactLastName = "";
$locationPrimaryContactTitle = "";
$locationPrimaryContactEmail = "";
$notesField = "";

// If reusing an existing Pers contact entry
$locationPrimaryContactExistingPersID = "";


$form_err    = "";


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
}

// Process form data when form is submitted.
if($_SERVER["REQUEST_METHOD"] == "POST"){

	// Get form fields
    if(!empty(trim($_POST["locationName"]))){
        $locationName = trim($_POST["locationName"]);
    }

    if(!empty(trim($_POST["locationAddress1"]))){
        $locationAddress1 = trim($_POST["locationAddress1"]);
    }

    if(!empty(trim($_POST["locationAddress2"]))){
        $locationAddress2 = trim($_POST["locationAddress2"]);
    }

    if(!empty(trim($_POST["locationCity"]))){
        $locationCity = trim($_POST["locationCity"]);
    }

    if(!empty(trim($_POST["locationState"]))){
        $locationState = trim($_POST["locationState"]);
    }

    if(!empty(trim($_POST["locationPostalCode"]))){
        $locationPostalCode = trim($_POST["locationPostalCode"]);
    }

    if(!empty(trim($_POST["locationPhone"]))){
        $locationPhone = trim($_POST["locationPhone"]);
    }

    if(!empty(trim($_POST["locationFax"]))){
        $locationFax = trim($_POST["locationFax"]);
    }

	// If an existing contact was selected from the dropdown, then we
	//  will associate that Pers_ID with this new Acct entry
	//  Otherwise, we can create a new Pers entry with the provided fields.
	if(isset($_POST["primaryContactExisting"]) && is_numeric($_POST["primaryContactExisting"]) && $_POST["primaryContactExisting"]>0){
		$locationPrimaryContactExistingPersID = $_POST["primaryContactExisting"];
	}else{

		if(!empty(trim($_POST["locationPrimaryContactFirstName"]))){
			$locationPrimaryContactFirstName = trim($_POST["locationPrimaryContactFirstName"]);
		}

		if(!empty(trim($_POST["locationPrimaryContactLastName"]))){
			$locationPrimaryContactLastName = trim($_POST["locationPrimaryContactLastName"]);
		}

		if(!empty(trim($_POST["locationPrimaryContactTitle"]))){
			$locationPrimaryContactTitle = trim($_POST["locationPrimaryContactTitle"]);
		}

		if(!empty(trim($_POST["locationPrimaryContactEmail"]))){
			$locationPrimaryContactEmail = trim($_POST["locationPrimaryContactEmail"]);
		}

		// See if provided email is currently unique in the system.
	    if(!empty(trim($_POST["locationPrimaryContactEmail"]))){
	        $personEmail = trim($_POST["locationPrimaryContactEmail"]);

			// Altering query to only allow an email to exist once in whole system, not constrained by acct_id
			//  since we now can link users to multiple acct locations in Usr_Loc table.
	        $query = "SELECT * FROM Email WHERE Email_URL_Desc='".$personEmail."'"; // AND Acct_ID=".$dealerID.";
		    $result = mysqli_query($link,$query);
	        if (mysqli_num_rows($result)>0){
	            $_SESSION["error_emessage"] = "Person Email Already Exist...";
	        } else {
	            unset($_SESSION["error_emessage"]);
	            $locationPrimaryContactEmail = trim($_POST["locationPrimaryContactEmail"]);
	        }
	    }
	    /*else{
	    	$_SESSION["error_message"] = "No Email found, please try again.";
			header("location: dealer_setup.php");
			exit;
		}*/


	}

    if(!empty(trim($_POST["notesField"]))){
        $notesField = trim($_POST["notesField"]);
    }




    if(isset($_SESSION["error_fmessage"]) != '' || isset($_SESSION["error_lmessage"]) != '' || isset($_SESSION["error_emessage"]) != ''){
        header("location: dealer_multiple_locations.php");
        exit;
    }

	$dealerID = $_SESSION["id"];


	// Update tracker for dealer forms, to indicate the form is signed
	$stmt = mysqli_prepare($link, "UPDATE Dealer_Progress SET Dealer_Multiple_Locations_Complete='Y' WHERE Acct_ID=?");

	/* Bind variables to parameters */
	$val1 = $dealerID;

	mysqli_stmt_bind_param($stmt, "i", $val1);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);



	/* Prepare an insert statement to created an Acct entry */
	$stmt = mysqli_prepare($link, "INSERT INTO Acct (Acct_Nm,Prnt_Acct_ID) VALUES (?,?)");

	/* Bind variables to parameters */
	$val1 = $locationName;
	$val2 = $dealerID;

	mysqli_stmt_bind_param($stmt, "si", $val1,$val2);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);

	$last_id = mysqli_insert_id($link);



	/* Prepare an insert statement to create an Addr entry for the main address */
	$stmt = mysqli_prepare($link, "INSERT INTO Addr (Acct_ID,St_Addr_1_Desc,St_Addr_2_Desc,City_Nm,St_Prov_ID,Pstl_Cd,Addr_Type_Cd,Addr_Type_Desc,Prim_Addr_Flg) VALUES (?,?,?,?,?,?,'Work','Work','Y')");

	/* Bind variables to parameters */
	$val1 = $last_id;
	$val2 = $locationAddress1;
	$val3 = $locationAddress2;
	$val4 = $locationCity;
	$val5 = $locationState;
	$val6 = $locationPostalCode;

	mysqli_stmt_bind_param($stmt, "isssis",$val1,$val2,$val3,$val4,$val5,$val6);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);


	// Insert note entry if provided
	if($notesField!=""){
		/* Prepare an insert statement to create an Note entry for the note field */
		$stmt = mysqli_prepare($link, "INSERT INTO Note (Acct_ID,Note_Desc,Note_Type) VALUES (?,?,'agreement')");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $notesField;

		mysqli_stmt_bind_param($stmt, "is", $val1,$val2);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);
	}


	/* Prepare an insert statement to create a Tel entry for the main phone */
	$stmt = mysqli_prepare($link, "INSERT INTO Tel (Acct_ID,Tel_Nbr,Tel_Type_Cd,Tel_Type_Desc,Prim_Tel_Flg) VALUES (?,?,'Work','Work','Y')");

	/* Bind variables to parameters */
	$val1 = $last_id;
	$val2 = $locationPhone;

	mysqli_stmt_bind_param($stmt, "is", $val1,$val2);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);



	// Insert fax entry if provided
	if($locationFax!=""){
		/* Prepare an insert statement to create a Tel entry for the fax */
		$stmt = mysqli_prepare($link, "INSERT INTO Tel (Acct_ID,Tel_Nbr,Tel_Type_Cd,Tel_Type_Desc,Prim_Tel_Flg) VALUES (?,?,'Fax','Fax','N')");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $locationFax;

		mysqli_stmt_bind_param($stmt, "is", $val1,$val2);
	}


	// Now either create a new primary contact for this Acct, or use an existing
	//  one, which is most likely the primary contact on the parent account
	if($locationPrimaryContactExistingPersID != ""){
		// We have been provided with a Pers_ID from the form.
		$primary_Contact_Person_id = $locationPrimaryContactExistingPersID;

		// Get the userID for this existing Pers
		$query = "SELECT * FROM Users WHERE Pers_ID=".$primary_Contact_Person_id;
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$primary_Contact_User_id = $row["userID"];

	}else{
		// Otherwise create a new account

		/* Prepare an insert statement to create an Pers entry for the primary contact person */
		$stmt = mysqli_prepare($link, "INSERT INTO Pers (Acct_ID,Pers_Full_Nm,Pers_Last_Nm,Pers_Frst_Nm,Pers_Username,Pers_Ttl_Nm,Pswd_Hash_Cd,Cntct_Prsn_For_Acct_Flg) VALUES (?,?,?,?,?,?,?,'Y')");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $locationPrimaryContactFirstName." ".$locationPrimaryContactLastName;
		$val3 = $locationPrimaryContactLastName;
		$val4 = $locationPrimaryContactFirstName;
		$val5 = $locationPrimaryContactEmail;  // username
		$val6 = $locationPrimaryContactTitle;
		$val7 = password_hash("PASSWORD", PASSWORD_DEFAULT);  // password

		mysqli_stmt_bind_param($stmt, "issssss", $val1,$val2,$val3,$val4,$val5,$val6,$val7);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the per Pers_ID of the primary contact person.
		$primary_Contact_Person_id = mysqli_insert_id($link);



		/* Create an entry in Users for this new primary contact. */
		$initialPassword = password_hash("PASSWORD", PASSWORD_DEFAULT);

		$stmt = mysqli_prepare($link, "INSERT INTO Users (Acct_ID,Pers_ID,Role_ID,username,password,mustResetPassword,createdDate) VALUES (?,?,2,?,?,'Y',NOW())");

		/* Bind variables to parameters */
		mysqli_stmt_bind_param($stmt, "iiss", $last_id, $primary_Contact_Person_id,$locationPrimaryContactEmail,$initialPassword);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);

		// Get the per User_ID of the primary contact person.
		$primary_Contact_User_id = mysqli_insert_id($link);


		/* Prepare an insert statement to create an Email entry for the primary contact person email */
		$stmt = mysqli_prepare($link, "INSERT INTO Email (Acct_ID,Pers_ID,Email_URL_Desc,Email_Type_Cd,Email_Type_Desc,Email_Prim_Flg) VALUES (?,?,?,'Work','Work','Y')");

		/* Bind variables to parameters */
		$val1 = $last_id;
		$val2 = $primary_Contact_Person_id;
		$val3 = $locationPrimaryContactEmail;

		mysqli_stmt_bind_param($stmt, "iis", $val1,$val2,$val3);

		/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);


		// Send mail to this new new user
		$to = $locationPrimaryContactEmail;
		$subject = "Welcome - New Vital Trends Account";
		$txt = "You have been signed up for a Vital Trends user account!  Please click here to <a href='https://portal.vitaltrendsusa.com'>log in</a>.\n";
		$txt .= "Your initial password is: PASSWORD \n";
		$txt .= "Please note, you will need to change your password upon first login.\n\n";
		$txt .= "Thank you!\nVital Trends team";
		$headers = "From: admin@vitaltrendsusa.com" . "\r\n" .
		"CC: cparry@gmail.com";

		mail($to,$subject,$txt,$headers);


	}

	// Create a new Dlr_Loc_Dim entry
	$stmt = mysqli_prepare($link, "INSERT INTO Dlr_Loc_Dim (Dlr_Acct_ID,Dlr_Acct_Nbr,Dlr_Loc_Nbr,Dlr_Loc_Nm) VALUES (?,0,0,?)");

	/* Bind variables to parameters */
	$val1 = $last_id;
	$val2 = $locationName;

	mysqli_stmt_bind_param($stmt, "is", $val1,$val2);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);

	// Get the per Dlr_Loc_Dim_ID of the primary contact person.
	$Dlr_Loc_Dim_ID = mysqli_insert_id($link);



	// Create a new Usr_Loc entry
	$stmt = mysqli_prepare($link, "INSERT INTO Usr_Loc (Dlr_Acct_ID,Dlr_Loc_Dim_ID,Usr_ID,Pers_ID) VALUES (?,?,?,?)");

	/* Bind variables to parameters */
	$val1 = $last_id;
	$val2 = $Dlr_Loc_Dim_ID;
	$val3 = $primary_Contact_User_id;
	$val4 = $primary_Contact_Person_id;

	mysqli_stmt_bind_param($stmt, "iiii", $val1,$val2,$val3,$val4);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);



	// Redirect back to this entry form
    header("location: dealer_multiple_locations.php");
    exit;


?>
<?php

}else{

	// Get the dealer address info
	$query = "SELECT * FROM Addr WHERE Acct_ID=".$dealerID." AND Addr_Type_Cd='Work';";
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$dealerAddress1 = $row["St_Addr_1_Desc"];
	$dealerAddress2 = $row["St_Addr_2_Desc"];
	$dealerCity = $row["City_Nm"];
	$dealerState = $row["St_Prov_ID"];
	$dealerZip = $row["Pstl_Cd"];

	// Look up the state name
	if($dealerState > 0){
		$query = "SELECT * FROM St_Prov WHERE St_Prov_ID=".$dealerState;
		$result = $link->query($query);
		$row = $result->fetch_assoc();

		$dealerStateName = $row["St_Prov_ISO_2_Cd"];

	}else{
		$dealerStateName = "None Found";
	}


	// Get the dealer info
	$query = "SELECT * FROM Acct WHERE Acct_ID=".$dealerID;
	$result = $link->query($query);
	$row = $result->fetch_assoc();

	$dealerName = $row["Acct_Nm"];

	// Get the contract info
	$query = "SELECT cd.Contract_Date FROM Cntrct c, Cntrct_Dim cd WHERE c.Mfr_Acct_ID=".$dealerID." AND c.Cntrct_Dim_ID = cd.Cntrct_Dim_ID;";
	$result = $link->query($query);
	if($row = $result->fetch_assoc()){
		$agreementDate = $row["Contract_Date"];
	}else{
		$agreementDate = "";
	}

	// Get list of states from the Enumeration table
	//$query = "SELECT * FROM St_Prov WHERE Cntry_Nm = 'US' ORDER BY St_Prov_Nm";
	//$stateResult = $link->query($query);
	//$stateShipResult = $link->query($query);

	$stateResult = selectStates($link);
	$stateShipResult = selectStates($link);

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
                <div class="row" style="margin-top: 2%;">
                    <div class="col-lg-12">
						<div class="form-group col-md-12">
							<a href="dealer_setup.php"><span class="badge badge-rounded badge-warning">Done Adding Locations</span></a>
						</div>
					</div>
				</div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header text-center">
                                <h4 class="card-title">AUTHORIZED RETAILER ADDITIONAL LOCATION INFORMATION</h4>
                            </div>
                            <div class="card-body">
                                <div class="basic-form dealer-form">
                                    <div class="watermark">
                                        <img src="images/logo_large_bg.png" alt="">
                                    </div>
                                    <form name="dealerForm" method="POST" action="">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">Dealer Name</h5>
                                                <h4 class="text-muted mb-0"><?php echo $dealerName;?></h4>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">Agreement Date</h5>
                                                <h4 class="text-muted mb-0"><?php echo $agreementDate;?></h4>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <h5 class="text-primary d-inline">Dealership Address</h5>
                                                <h4 class="text-muted mb-0"><?php echo $dealerAddress1;?> <?php echo $dealerCity.", ".$dealerStateName.". ".$dealerZip;?></h4>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <hr />
                                            </div>
                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">Location Name</h5>
                                                <input type="text" class="form-control" name="locationName" placeholder="" required value="" />
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>
                                            <div class="form-group col-md-6">
												&nbsp;
                                            </div>
                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">Location Address</h5>
                                                <input type="text" class="form-control" name="locationAddress1" placeholder="" required value="" />
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">Location PO Box / Suite</h5>
                                                <input type="text" class="form-control" name="locationAddress2" placeholder="" value="" />
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">Location City</h5>
                                                <input type="text" class="form-control" name="locationCity" placeholder="" required value="" />
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">Location State</h5>
												<select class="form-control default-select" name="locationState" id="locationState">
                                                    <option value="" selected disabled>-- Select Location State --</option>
                                                <?php
                                                if (mysqli_num_rows($stateResult) > 0) {
                                                    // output data of each row
                                                    $loopCounter = 0;
                                                    while($row = mysqli_fetch_assoc($stateResult)) {
                                                        $loopCounter++;
                                                    ?>
                                                        <option value=<?php echo $row["St_Prov_ID"]?>><?php echo $row["St_Prov_Nm"];?></option>
                                                <?php } } ?>
												</select>
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">Location Postal Code</h5>
                                                <input type="text" class="form-control" name="locationPostalCode" placeholder="" required value="" />
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>
                                            <div class="form-group col-md-6">
												&nbsp;
                                            </div>

                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">Location Phone</h5>
                                                <input type="text" class="form-control" name="locationPhone" placeholder="" required value="" />
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <h5 class="text-primary d-inline">Location Fax</h5>
                                                <input type="text" class="form-control" name="locationFax" placeholder="" value="" />
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>

                                            <div class="form-group col-md-6">
<?php
// Get people associated with this dealerID
$query = "SELECT * FROM Usr_Loc ul, Pers p, Email m, Tel t WHERE ul.Dlr_Acct_ID=".$dealerID." AND
ul.Pers_ID = p.Pers_ID AND
t.Pers_ID = p.Pers_ID AND
m.Pers_ID = p.Pers_ID ORDER BY Pers_Last_Nm ASC";
$personResult = $link->query($query);

if (mysqli_num_rows($personResult) > 0) {
?>
                                                <h5 class="text-primary d-inline">Existing Dealer People</h5>
												<select class="form-control default-select" name="primaryContactExisting" id="sel1">\n
													<option value="0">- select -</option>
<?
  // output data of each row
  $primary_Contact_exists = false;
  $loopCounter = 0;
  while($row = mysqli_fetch_assoc($personResult)) {
	$loopCounter++;
	$Cntct_Prsn_For_Acct_Flg = $row["Cntct_Prsn_For_Acct_Flg"];

?>
													<option value="<?php echo $row["Pers_ID"];?>" <?php if($Cntct_Prsn_For_Acct_Flg=="Y"){  $primary_Contact_exists = true;?> selected="selected" <?php } ?>><?php echo $row["Pers_Frst_Nm"]." ".$row["Pers_Last_Nm"];?> (<?php echo $row["Email_URL_Desc"];?>) <?php if($Cntct_Prsn_For_Acct_Flg=="Y"){ ?> (primary contact)<?php } ?></option>\n

<?php
  }
?>
												</select>
<?php
}else{
	echo "No people yet defined for this agreement, somehow!";
}
?>
												</select>
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>
                                            <div class="form-group col-md-6">
												&nbsp;
                                            </div>

                                            <div class="form-group col-md-6 locationPrimaryContactFirstName">
                                                <h5 class="text-primary d-inline">Location Primary Contact First Name</h5>
                                                <input type="text" class="form-control" name="locationPrimaryContactFirstName" placeholder="" value="" <?php if($primary_Contact_exists){ ?>  disabled style="background:#ddddddb0;cursor: not-allowed;"<?php } ?>/>
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>
                                            <div class="form-group col-md-6 locationPrimaryContactLastName">
                                                <h5 class="text-primary d-inline">Location Primary Contact Last Name</h5>
                                                <input type="text" class="form-control" name="locationPrimaryContactLastName" placeholder="" value="" <?php if($primary_Contact_exists){ ?>  disabled style="background:#ddddddb0;cursor: not-allowed;"<?php } ?>/>
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>

                                            <div class="form-group col-md-6 locationPrimaryContactTitle">
                                                <h5 class="text-primary d-inline">Location Primary Contact Title</h5>
                                                <input type="text" class="form-control" name="locationPrimaryContactTitle" placeholder="" value="" <?php if($primary_Contact_exists){ ?>  disabled style="background:#ddddddb0;cursor: not-allowed;" <?php } ?> />
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>
                                            <div class="form-group col-md-6 locationPrimaryContactEmail">
                                                <h5 class="text-primary d-inline">Location Primary Contact Email</h5>
                                                <input type="text" class="form-control" name="locationPrimaryContactEmail" placeholder="" value="" <?php if($primary_Contact_exists){ ?> disabled style="background:#ddddddb0;cursor: not-allowed;"<?php } ?> />
                                                <span style="color:red;<?php if(isset($_SESSION['error_fmessage']) !=''){?>display:block; <?php }else{?>display:none; <?php } ?>"><?php if(isset($_SESSION['error_fmessage']) !=''){ echo $_SESSION['error_fmessage']; } ?></span>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Notes</label>
                                                <textarea class="form-control" name="notesField" rows="5" cols="60"></textarea>
                                            </div>
                                            <div class="form-group col-md-6">
												&nbsp;
                                            </div>
                                            <div class="form-group col-md-6">
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                            <div class="form-group col-md-6">
                                            <a href="dealer_setup.php"><span class="badge badge-rounded badge-warning">Done Adding Locations</span></a>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- row -->

                <div class="row">
                    <div class="col-lg-12">
						<div class="form-group col-md-12">

						</div>
					</div>
				</div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Existing Locations for Dealer</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-responsive-md">
                                        <thead>
                                            <tr>
                                                <th class="width80">#</th>
                                                <th>Location Name</th>
                                                <th>Location Address</th>
                                                <th>Telephone</th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php

$St_Addr_1_Desc = "";
$City_Nm = "";
$locationState = "";
$Pstl_Cd = "";
$Tel_Nbr= "";


// Get locations for the parent dealerID
$query = "SELECT * FROM Acct a, Addr ad, Tel t WHERE (a.Prnt_Acct_ID=".$dealerID." OR a.Acct_ID=".$dealerID.") AND
a.Acct_ID = ad.Acct_ID AND
a.Acct_ID = t.Acct_ID AND
ad.Prim_Addr_Flg='Y' AND
Prim_Tel_Flg='Y'";
$locationResult = $link->query($query);


if (mysqli_num_rows($locationResult) > 0) {

  // output data of each row
  $loopCounter = 0;
  while($row = mysqli_fetch_assoc($locationResult)) {
	$loopCounter++;

	$St_Addr_1_Desc = $row["St_Addr_1_Desc"];
	$City_Nm = $row["City_Nm"];
	$locationState = $row["St_Prov_ID"];
	$Pstl_Cd = $row["Pstl_Cd"];
	$Tel_Nbr= $row["Tel_Nbr"];

	if($locationState > 0){
		$stateQuery = "SELECT * FROM St_Prov WHERE St_Prov_ID=".$locationState;
		$stateResult = $link->query($stateQuery);
		$stateRow = $stateResult->fetch_assoc();

		$locationStateName = $stateRow["St_Prov_ISO_2_Cd"];

	}else{
		$locationStateName = "None Found";
	}

?>

<tr>
	<td><?php echo $loopCounter;?></td>
	<td><?php echo $row["Acct_Nm"];?> <?php if($row["Acct_ID"]==$dealerID){echo "(primary)";} ?></td>
	<td><?php echo $St_Addr_1_Desc;?> <?php echo $City_Nm.", ".$locationStateName.". ".$Pstl_Cd;?></td>
	<td><?php echo $Tel_Nbr;?></td>
</tr>

<?php
  }
} else {
?>
<tr>
	<td colspan="5">No locations found, yet.</td>
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
	<script>
		jQuery('[name=primaryContactExisting]').on('change',function(){
			let PCE = jQuery('[name=primaryContactExisting]').val();

			if(PCE > 0) {
				jQuery('[name=locationPrimaryContactFirstName]').attr('disabled',true);
				jQuery('[name=locationPrimaryContactFirstName]').attr('style',"background:#ddddddb0;cursor: not-allowed;");
				jQuery('[name=locationPrimaryContactLastName]').attr('disabled',true);
				jQuery('[name=locationPrimaryContactLastName]').attr('style',"background:#ddddddb0;cursor: not-allowed;");
				jQuery('[name=locationPrimaryContactTitle]').attr('disabled',true);
				jQuery('[name=locationPrimaryContactTitle]').attr('style',"background:#ddddddb0;cursor: not-allowed;");
				jQuery('[name=locationPrimaryContactEmail]').attr('disabled',true);
				jQuery('[name=locationPrimaryContactEmail]').attr('style',"background:#ddddddb0;cursor: not-allowed;");
			}
			else {
				jQuery('[name=locationPrimaryContactFirstName]').removeAttr('disabled');
				jQuery('[name=locationPrimaryContactFirstName]').attr('style',"");
				jQuery('[name=locationPrimaryContactLastName]').removeAttr('disabled');
				jQuery('[name=locationPrimaryContactLastName]').attr('style',"");
				jQuery('[name=locationPrimaryContactTitle]').removeAttr('disabled');
				jQuery('[name=locationPrimaryContactTitle]').attr('style',"");
				jQuery('[name=locationPrimaryContactEmail]').removeAttr('disabled');
				jQuery('[name=locationPrimaryContactEmail]').attr('style',"");
			}

		});
	</script>
</body>
</html>