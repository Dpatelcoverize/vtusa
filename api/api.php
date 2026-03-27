<?php
//
// File: api.php
// Author: Charles Parry
// Date: 7/12/2022
//
//

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);


$secretApiKey = "kwjejh234hsfkdfj";


$pageBreadcrumb = "API";
$pageTitle = "API";


// Connect to DB
require_once "../includes/dbConnect.php";

// DB Library
//require_once "../lib/dblib.php";


// Variables
$apiEndpoint = "";
$apiKey = "";
$arNumber = "";
$pdfURL = "";



function response($status,$status_message,$data)
{
	header("HTTP/1.1 ".$status);

	$response['status']=$status;
	$response['status_message']=$status_message;
	$response['data']=$data;

	$json_response = json_encode($response);
	echo $json_response;
}



// Get the API Endpoint
if(isset($_GET["endpoint"]) && $_GET["endpoint"]!=""){
	$apiEndpoint = $_GET["endpoint"];
}else{
	// Echo back a success response for the caller
	response(404,"Failure","No Endpoint Found");

	die();

}

if(isset($_GET["key"]) && $_GET["key"]!=""){
	$apiKey = $_GET["key"];
}else{
	// Echo back a success response for the caller
	response(404,"Failure","No API Key Found");

	die();

}

if(false){
?>



<?php

}

// Verify the Key that was used
if($apiKey != $secretApiKey){

	// Echo back a success response for the caller
	response(404,"Failure","Bad API Key Found");

	die();

?>
<!DOCTYPE html>
<html lang="en" class="h-100">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title>Vital Trends Portal - Select Dealer</title>
		<!-- Favicon icon -->
		<link rel="icon" type="image/png" sizes="16x16" href="./images/favicon.png">
		<link href="./css/style.css" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
	</head>

	<body class="h-100">

	<span style="color:red;"><p>bad API Key found.  Halting process.</p></span>
	</body></html>
<?
	die();
}


if($apiEndpoint=="send_pdf"){

	if(isset($_GET["ar"]) && $_GET["ar"]!=""){
		$arNumber = $_GET["ar"];
	}

	if(isset($_GET["path"]) && $_GET["path"]!=""){
		$pdfURL = $_GET["path"];
	}


	// Create a new API_Data entry to track activity
	$stmt = mysqli_prepare($link, "INSERT INTO API_Data (API_Endpoint,AR_Number,PDF_Path,API_Key,Created_Date) VALUES (?,?,?,?,NOW())");

	/* Bind variables to parameters */
	$val1 = $apiEndpoint;
	$val2 = $arNumber;
	$val3 = $pdfURL;
	$val4 = $apiKey;

	mysqli_stmt_bind_param($stmt, "ssss", $val1,$val2,$val3,$val4);

	/* Execute the statement */
	$result = mysqli_stmt_execute($stmt);


	// Echo back a success response for the caller
	response(200,"Success","PDF Received");

	die();
}else{
	// Echo back a success response for the caller
	response(404,"Failure","Bad Endpoint Found");

	die();

}



?>

		<h3>Vital Trends API Endpoint</h3>

		<p>Thanks for using the VT API!</p>
		<table border="1" cellpadding="5">
			<tr>
				<td>API Endpoint=<?php echo $apiEndpoint;?></td>
			</tr>
			<tr>
				<td>AR Number=<?php echo $arNumber;?></td>
			</tr>
			<tr>
				<td>Path to PDF=<?php echo $pdfURL;?></td>
			</tr>
		</table>
		<span style="color:green;"><p>Information has been recorded - thank you</p></span>

	</body>
</html>
