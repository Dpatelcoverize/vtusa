<?php
//
// File: warranty_uploads.php
// Author: Hardik Santoki
// Date: 4/29/2025
//
//

// Turn on error reporting
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
// error_reporting(E_ALL);

// Connect to DB
require_once "includes/dbConnect.php";

// DB Library
require_once "lib/dblib.php";

/**For encryption of the data */
require_once 'encrypt.php';

// Variables.
$dealerID = "";
$warrantyID = "";
$isQuote = "";
$isWarrantyFinalized = "";

if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

// Make sure a dealer is currently logged in, or go back to the Agreement
if (!(isset($_SESSION["userType"])) || !($_SESSION["userType"] == "dealer")) {
    header("location: index.php");
    exit;
}

// Get a dealer ID from session.
if (!(isset($_SESSION["id"]))) {
    header("location: index.php");
    exit;
} else {
    $dealerID = $_SESSION["id"];
}

if (isset($_SESSION["admin_id"])) {
    $adminID = $_SESSION["admin_id"];
}

if (isset($_GET["isQuote"])) {
    $isQuote = $_GET["isQuote"];
}

// Process form data when form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_SESSION["errorMessage"] = "";

    if (isset($_POST["warrantyID"]) && ($_POST["warrantyID"] != "")) {
        $warrantyID = $_POST["warrantyID"];
        $isWarrantyFinalized = $_POST["isWarrantyFinalized"];
        // SECURITY make sure this dealer may edit this warranty

        $securityCheck = dealerOwnsWarranty($link, $dealerID, $warrantyID);
        if (!$securityCheck) {

            if ($isQuote == "Y") {
                header("location: warranty_pending.php?isQuote=Y");
            } else {
                header("location: warranty_pending.php");
            }
            exit;
        }
        $stmt = mysqli_prepare($link, "UPDATE Cntrct SET Finalized_Warranty_Flg=? WHERE Cntrct_ID=?");

        $val1 = $isWarrantyFinalized;
        $val2 = $warrantyID;

        mysqli_stmt_bind_param($stmt, "si", $val1, $val2);

        /* Execute the statement */
        $result = mysqli_stmt_execute($stmt);
        if ($result) {
            header("location: warranty_print.php?warrantyID=" . encryptData($warrantyID));
        } else {
            $_SESSION["errorMessage"] = "Something Went Wrong";
            header("location: warranty_print.php?warrantyID=" . encryptData($warrantyID));
        }
    } else {
        Header("Location: warranty_pending.php");
        exit;
    }
}
