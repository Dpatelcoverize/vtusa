<?php

// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
$_SESSION['status'];
$_SESSION['error'];
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
    $fileID = $_POST["fileID"];
    $oldFile = ltrim($_POST["oldFile"], $_POST["oldFile"][0]);
    $filename = $_FILES['warrantyPDF']['name'];
    $ext = pathinfo($filename,PATHINFO_EXTENSION);
    $allowed = array("pdf" => "application/pdf");

    if(!array_key_exists($ext,$allowed))
    {
        $_SESSION['error'] = "The file format is not acceptable";
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
        $filePath = '/uploads/quote_pdf/' . $filename;
        $query = "UPDATE File_Assets SET Path_to_File = '". $filePath . "'  WHERE File_Asset_ID = ". $fileID;
        $result = $link->query($query);
    }
    else
    {
        move_uploaded_file($_FILES['warrantyPDF']['tmp_name'],"uploads/warranty_pdf/".$filename);
        $filePath = '/uploads/warranty_pdf/' . $filename;
        $query = "UPDATE `File_Assets` SET `Path_to_File`= '". $filePath ."' WHERE File_Asset_ID=". $fileID;
        $result = $link->query($query);
    }

        $_SESSION['status'] = "File updated successfully";
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