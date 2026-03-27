<?php

// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
if (!(isset($_SESSION["id"]))) {
	header("location: index.php");
	exit;
} else {

	$adminID = $_SESSION["admin_id"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
{
    $isQuote = $_POST["showQuotes"];
    $dealerID = $_POST["dealerID"];
    $warrantyID = $_POST["warrantyID"];
    $filename = $_FILES['warrantyPDF']['name'];
    $ext = pathinfo($filename,PATHINFO_EXTENSION);
    $allowed = array("pdf" => "application/pdf");

    if(!array_key_exists($ext,$allowed))
    {
        $_SESSION['status'] = "The file format is not acceptable";
        if($isQuote == "Y")
        {
            Header("Location: warranty_pending.php?showQuotes=Y");
        }
        else
        {
            Header("Location: warranty_pending.php");
        }

    }


    if($isQuote == 'Y')
    {
         move_uploaded_file($_FILES['warrantyPDF']['tmp_name'],"uploads/quote_pdf/".$filename);
    }
    else
    {
        move_uploaded_file($_FILES['warrantyPDF']['tmp_name'],"uploads/warranty_pdf/".$filename);
    }


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
		if ($isQuote == "Y") {
			$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
						   Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,16,'Quote Ink Signed',NOW())");
		} else {
			$stmt = mysqli_prepare($link, "INSERT INTO File_Assets (Acct_ID,Dealer_Pers_ID,VT_Pers_ID,Dealer_Cntrct_ID,
						   Path_to_File,File_Asset_Type_ID,File_Asset_Desc,createdDate) VALUES (?,?,?,?,?,17,'Warranty Ink Signed',NOW())");
		}

/* Bind variables to parameters */
		$val1 = $dealerID;
		$val2 = $primary_Contact_Person_id;
		$val3 = $adminID;
//$val4 = $contract_dim_ID;
		$val4 = $warrantyID; // PARRY: I changed this to $warrantyID which is the Cntrct_ID not Cntrct_Dim_ID.
		if ($isQuote == "Y") {
			$val5 = '/uploads/quote_pdf/' . $filename;
		} else {
			$val5 = '/uploads/warranty_pdf/' . $filename;

		}
		mysqli_stmt_bind_param($stmt, "iiiis", $val1, $val2, $val3, $val4, $val5);


/* Execute the statement */
		$result = mysqli_stmt_execute($stmt);
        $_SESSION['status'] = "File uploaded successfully";
        if($result)
        {
            if($isQuote == "Y")
            {
                Header("Location: warranty_pending.php?showQuotes=Y");
            }
          else{
                 Header("Location: warranty_pending.php");
          }
        }
    }
}

?>