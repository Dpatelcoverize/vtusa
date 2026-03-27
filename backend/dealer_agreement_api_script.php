<?php

require_once("lib/functions.php");


	 // Send to test or prod endpoint
	 if(isset($sendType)){
		if($sendType=="prod"){
			 $url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/retailers/create-retailer?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";

			 //echo "WARNING: Attempting to push to PROD.  Disabled for now during development.";
			 //die();
		}else{
			 $url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/subprod/retailers/create-retailer?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";
		}
	 }else{
		 $url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/subprod/retailers/create-retailer?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";
	 }


	 $curl = curl_init($url);
	 curl_setopt($curl, CURLOPT_URL, $url);
	 curl_setopt($curl, CURLOPT_POST, true);
	 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	 $headers = array(
	 	"Accept: application/json",
	 	"Content-Type: application/json",
	 );
	 curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	 if (file_exists('uploads/dealer_agreement_pdf/' . $pdfFileName)) {
	 	$b64PDFDoc = base64_encode(file_get_contents('uploads/dealer_agreement_pdf/' . $pdfFileName));
	 } else {
	 	$b64PDFDoc = base64_encode(file_get_contents("files/TEST_AGREEMENT_PDF.pdf"));
	 }

	 $data = "{
	  \"retailerName\": \"$dealerName\",
	  \"retailerEmail\": \"$businessEmail\",
	  \"retailerPhone\": \"$dealerPhone\",
	  \"retailerAddress\": {
	  \"street\": \"$dealerAddress1\",
	  \"street2\": \"$dealerAddress2\",
	  \"city\": \"$dealerCity\",
	  \"state\": \"$dealerState\",
	  \"zip\": \"$dealerZip\",
	  \"country\": \"US\"
	  },
	  \"defaultCurrency\": \"USD\",
	  \"validationMethod\": \"ECA Only\",
	  \"files\" : [{\"type\" : \"vtAgreement\", \"fileBytes\" : \"$b64PDFDoc\"}]
	 }";


	 // echo "data=".$data;
	 // die();

	 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);


	 $resp = curl_exec($curl);

	 curl_close($curl);
	 //var_dump($resp);
	 $json = json_decode($resp, true);
	 // print_r($json);

	 if (isset($json) && array_key_exists("success", $json)) {
	 	$responseStatus = $json["success"];
	 } else {
	 	$responseStatus = 0;
	 }

	 if ($responseStatus == 1) {
	 	$arNumber = $json["data"]["arNumber"];
	 	$apiMessage = $json["message"];

	 	// Save the returned retailer number to the CNTRCT_DIM table.
		if($sendType=="prod"){
		 	$stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim SET Assign_Rtlr_Nbr=?,Sent_To_TNG_Prod_Flg='Y' WHERE
		 	                               Cntrct_Dim_ID=?");
		}else{
		 	$stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim SET Assign_Rtlr_Nbr_Test=?,Sent_To_TNG_Test_Flg='Y' WHERE
		 	                               Cntrct_Dim_ID=?");
		}


	 	/* Bind variables to parameters */
	 	$val1 = $arNumber;
	 	$val2 = $contract_dim_ID;

	 	mysqli_stmt_bind_param($stmt, "si", $val1, $val2);

	 	/* Execute the statement */
	 	$result = mysqli_stmt_execute($stmt);

	 } else {
	 	$arNumber = "FAILED";
	 	$apiMessage = "NONE";
	 	$responseStatus = 0;
	 }


	 // to avoid bloating the database, save the API message data to the file system, and save the
	 //  path to that file in the database.

	 // Folder will be /api/logs/
	 //  subfolder will be current date YYYYMMDD
	 $currentDate = date('Ymd');
	 $filename = "api/logs/".$currentDate."/".random_string(25).".txt";
     $dirname = dirname($filename);
	 if (!is_dir($dirname)) {
	   mkdir($dirname, 0755, true);
	 }

/*
echo "filename=".$filename;
echo "<br />dirname=".$dirname;
echo "<br />currentDate=".$currentDate;
die();
*/
	 // Open this file, and write our 'data' into it which includes the large PDF.
	 //  Then close file.
     $myfile = fopen($filename, "w") or die("Unable to open file!");
	 fwrite($myfile, $data);
	 fclose($myfile);


	 // Create a new API_Data entry to track activity
	 $stmt = mysqli_prepare($link, "INSERT INTO API_Responses (Acct_ID,Endpoint_Used,statusCode, dataReturned, arNumber, messageText, sentJSON, returnedJSON, createdDate) VALUES (?,?,?,?,?,?,?,?,NOW())");

	 /* Bind variables to parameters */
	 $val1 = $last_id;
	 $val2 = $url;
	 $val3 = $responseStatus;
	 $val4 = $arNumber;
	 $val5 = $arNumber;
	 $val6 = $apiMessage;
	 $val7 = $filename; // this used to save the large $data value, but now points to the filesystem.
	 $val8 = $resp;

	 mysqli_stmt_bind_param($stmt, "isssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7,$val8);

	 /* Execute the statement */
	 $result = mysqli_stmt_execute($stmt);
?>