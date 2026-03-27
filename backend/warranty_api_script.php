<?php
require_once("lib/functions.php");


	 // Send to test or prod endpoint
	 if(isset($sendType)){
		if($sendType=="prod"){
			 $url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/warranties/upsert-record?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";
			 //$url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/retailers/create-retailer?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";

			 //echo "WARNING: Attempting to push to PROD.  Disabled for now during development.";
			 //die();
		}else{
			 $url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/warranties/upsert-record?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";
			 //$url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/subprod/retailers/create-retailer?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";
			 $dealerARNumber = "PA135"; // Hard code this for testing purposes, based on TNG spec.
			 //$dealerAgentEmail = "it+vt-testing@trunorthwarranty.com"; // Hard code this for testing purposes, based on TNG spec.
		}
	 }else{
			 $url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/warranties/upsert-record?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";
			 $dealerARNumber = "PA135"; // Hard code this for testing purposes, based on TNG spec.
			 //$dealerAgentEmail = "it+vt-testing@trunorthwarranty.com"; // Hard code this for testing purposes, based on TNG spec.
	 }

	 $warrantyAPIURL = $url;

	 $curl = curl_init($url);
	 curl_setopt($curl, CURLOPT_URL, $url);
	 curl_setopt($curl, CURLOPT_POST, true);
	 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	 $headers = array(
	 	"Accept: application/json",
	 	"Content-Type: application/json",
	 );
	 curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	 $Warranty_PDF = ltrim($Warranty_PDF, '/');
	 if (file_exists($Warranty_PDF)) {
	 	$b64WarrantyDoc = base64_encode(file_get_contents($Warranty_PDF));
	 } else {
	 	$b64WarrantyDoc = "";
	 }

	 $Inspection_Report = ltrim($Inspection_Report, '/');
	 if (file_exists($Inspection_Report)) {
	 	$b64InspectionDoc = base64_encode(file_get_contents($Inspection_Report));
	 } else {
	 	$b64InspectionDoc = "";
	 }

	 $ECA_Report = ltrim($ECA_Report, '/');
	 if (file_exists($ECA_Report)) {
	 	$b64ECADoc = base64_encode(file_get_contents($ECA_Report));
	 } else {
	 	$b64ECADoc = "";
	 }

	 $VIN_Photo = ltrim($VIN_Photo, '/');
	 if (file_exists($VIN_Photo)) {
	 	$b64VIN_Image = base64_encode(file_get_contents($VIN_Photo));
	 	$b64VIN_ImageName = substr($VIN_Photo, strrpos($VIN_Photo, '/') + 1);
	 } else {
	 	$b64VIN_Image = "";
	 	$b64VIN_ImageName = "";
	 }

	 $Dashboard_Photo = ltrim($Dashboard_Photo, '/');
	 if (file_exists($Dashboard_Photo)) {
	 	$b64Dashboard_Image = base64_encode(file_get_contents($Dashboard_Photo));
	 	$b64Dashboard_ImageName = substr($Dashboard_Photo, strrpos($Dashboard_Photo, '/') + 1);
	 } else {
	 	$b64Dashboard_Image = "";
	 	$b64Dashboard_ImageName = "";
	 }

	 $Engine_Photo = ltrim($Engine_Photo, '/');
	 if (file_exists($Engine_Photo)) {
	 	$b64Engine_Image = base64_encode(file_get_contents($Engine_Photo));
	 	$b64Engine_ImageName = substr($Engine_Photo, strrpos($Engine_Photo, '/') + 1);
	 } else {
	 	$b64Engine_Image = "";
	 	$b64Engine_ImageName = "";
	 }

	 $Maintenance_Form = ltrim($Maintenance_Form, '/');
	 if (file_exists($Maintenance_Form)) {
	 	$b64MaintenanceDoc = base64_encode(file_get_contents(ltrim($Maintenance_Form, '/')));
	 } else {
	 	$b64MaintenanceDoc = "";
	 }


	if(is_numeric($customerState)){
		$customerState = selectState($link,$customerState);
	}

	if(is_numeric($lienState)){
		$lienState = selectState($link,$lienState);
	}

	if($isAPU=="Y"){
		$APU_Coverage = "true";
	}else{
		$APU_Coverage = "false";
	}

	if($AEP=="Y"){
		$AEP_Coverage = "true";
	}else{
		$AEP_Coverage = "false";
	}

	if($aerialPackage=="Y"){
		$Aerial_Coverage = "true";
	}else{
		$Aerial_Coverage = "false";
	}

	// Sanity check some data
	$vehYearForAPI =  $vehYear;
	if($vehYearForAPI==""){
		$vehYearForAPI = "null";
	}

	$odometerreadingForAPI =  $odometerreading;
	if($odometerreadingForAPI==""){
		$odometerreadingForAPI = "0";
	}

	$ecmreadingForAPI =  $ecmreading;
	if($ecmreadingForAPI==""){
		$ecmreadingForAPI = "0";
	}

	$apuYearForAPI =  $apuYear;
	if($apuYearForAPI==""){
		$apuYearForAPI = "0";
	}

	if(($vehIsNew=="Y") && ($odometerMilesOrKM=="Miles" && $odometerreadingMiles > 500) && ((date("Y")-$vehYear)<3)){
		$validationType = "ECA";
	}else{
		$validationType = "inspectionECM";
	}

	//echo "validationType=".$validationType;
	//die();

	// Only send EITHER an inspection form, OR the ECA form + 3 photos
	if($validationType=="ECA"){
		$b64InspectionDoc = "null";
	}

/*
	if($b64InspectionDoc!=""){
		$b64ECADoc = "null";
		$b64VIN_ImageName = "null";
		$b64Dashboard_ImageName = "null";
		$b64Engine_ImageName = "null";
		$b64VIN_Image = "null";
		$b64Dashboard_Image = "null";
		$b64Engine_Image = "null";
	}
*/

	$vehTypeForAPI = "type".$vehType;

	$vehTierTypeForAPI = strtolower($vehTierType);



	 $data = "{
	  \"truckVIN\": \"$vehIDNumber\",
	  \"customerEmail\": \"$customerEmail\",
	  \"customerPhone\": \"$customerPhone\",
	  \"customerName\": \"$customerName\",
	  \"customerAddress\": {
	  \"street\": \"$customerAddress\",
	  \"street2\": \"\",
	  \"city\": \"$customerCity\",
	  \"state\": \"$customerState\",
	  \"zip\": \"$customerZip\",
	  \"country\": \"US\"
	  },
	  \"companyName\": \"$dealerName\",
	  \"lienHolderName\": \"$lienName\",
	  \"lienHolderPhone\": \"$lienPhone\",";

	if($lienAddress!=""){
		$data .= "
		  \"lienHolderAddress\": {
		  \"street\": \"$lienAddress\",
		  \"street2\": \"\",
		  \"city\": \"$lienCity\",
		  \"state\": \"$lienState\",
		  \"zip\": \"$lienZip\",
		  \"country\": \"US\"
		  },";
	}else{
		$data .= "
		  \"lienHolderAddress\": {
		  \"street\": null,
		  \"street2\": null,
		  \"city\": null,
		  \"state\": null,
		  \"zip\": null,
		  \"country\": null
		  },";
	}

	$data .= "
	  \"vehicleType\": \"$vehTypeForAPI\",
	  \"vehicleMake\": \"$vehMake\",
	  \"vehicleModel\": \"$vehModel\",
	  \"vehicleYear\": $vehYearForAPI,
	  \"vehicleOdometer\": $odometerreadingForAPI,
	  \"vehicleOdometerUnits\": \"$odometerMilesOrKM\",
	  \"vehicleEcm\": $ecmreadingForAPI,
	  \"vehicleEcmUnits\": \"$ecmMilesOrKM\",
	  \"engineHours\": 0,
	  \"engineMake\": \"$engMake\",
	  \"engineModel\": \"$engModel\",
	  \"engineSerial\": \"$engSerial\",
	  \"transmissionMake\": \"$transmissionMake\",
	  \"transmissionModel\": \"$transmissionModel\",
	  \"transmissionSerial\": \"$transmissionSerial\",
	  \"apuMake\": \"$apuMake\",
	  \"apuModel\": \"$apuModel\",";

	if($apuYearForAPI==0){
		$data .= "\"apuYear\": null,";
	}else{
		$data .= "\"apuYear\": ".$apuYearForAPI.",";
	}

	$data .= "
	  \"apuSerial\": \"$apuSerial\",
	  \"dealerID\": \"$dealerAgentEmail\",
	  \"retailerAR\": \"$dealerARNumber\",
	  \"poNumber\": null,
	  \"termLength\": $coverageTerm,
	  \"programPackage\": \"$vehTierTypeForAPI\",
	  \"apuCoverage\": $APU_Coverage,
	  \"apparatusCoverage\": $AEP_Coverage,
	  \"aerialCoverage\": $Aerial_Coverage,
	  \"validationType\": \"$validationType\",
	  \"isPaperWarranty\": true,";

	 $data .= "\"agreementb64File\" : \"$b64WarrantyDoc\",";

 	  if($validationType!="ECA"){
		  $data .= "\"inspectionB64File\" : \"$b64InspectionDoc\"";
	  }else{
		  $data .= "\"ecaB64File\" : \"$b64ECADoc\",
		  \"vinPlacardData\" : {\"fileName\" : \"$b64VIN_ImageName\", \"fileBytes\" : \"$b64VIN_Image\"},
		  \"dashboardPhotoData\" : {\"fileName\" : \"$b64Dashboard_ImageName\", \"fileBytes\" : \"$b64Dashboard_Image\"},
		  \"enginePlacardData\" : {\"fileName\" : \"$b64Engine_ImageName\", \"fileBytes\" : \"$b64Engine_Image\"}
		  ";
	  }

	 $data .= "}";

	 $warrantyData = $data;

	  // echo "data=".$data;
	 // die();

	 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);


	 $resp = curl_exec($curl);
	 curl_close($curl);
	 var_dump($resp);
	 $json = json_decode($resp, true);
	 print_r($json);

	 if (isset($json) && array_key_exists("success", $json)) {
	 	$responseStatus = $json["success"];
	 } else {
	 	$responseStatus = 0;
	 }


	 if ($responseStatus == 1) {
	 	$new_TNG_WarrantyID = $json["data"];
	 	$apiMessage = $json["message"];

	 	// Save the returned retailer number to the CNTRCT_DIM table.
		if($sendType=="prod"){
		 	$stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim SET Assign_Warranty_ID_Prod=?,Sent_To_TNG_Prod_Flg='Y' WHERE
		 	                               Cntrct_Dim_ID=?");
		}else{
		 	$stmt = mysqli_prepare($link, "UPDATE Cntrct_Dim SET Assign_Warranty_ID_Test=?,Sent_To_TNG_Test_Flg='Y' WHERE
		 	                               Cntrct_Dim_ID=?");
		}


	 	/* Bind variables to parameters */
	 	$val1 = $new_TNG_WarrantyID;
	 	$val2 = $warranty_Cntrct_Dim_ID;

	 	mysqli_stmt_bind_param($stmt, "si", $val1, $val2);

	 	/* Execute the statement */
	 	$result = mysqli_stmt_execute($stmt);

	 } else {
	 	$new_TNG_WarrantyID = "FAILED";
	 	$apiMessage = "NONE";
	 	$responseStatus = 0;
	 }


	 // to avoid bloating the database, save the API message data to the file system, and save the
	 //  path to that file in the database.

	 // Folder will be /api/logs/warranties_to_tng/
	 //  subfolder will be current date YYYYMMDD
	 $currentDate = date('Ymd');
	 $filename = "api/logs/warranties_to_tng/".$currentDate."/1_".random_string(25).".txt";
     $dirname = dirname($filename);
	 if (!is_dir($dirname)) {
	   mkdir($dirname, 0755, true);
	 }

	 // Open this file, and write our 'data' into it which includes the large PDF.
	 //  Then close file.
     $myfile = fopen($filename, "w") or die("Unable to open file!");
	 fwrite($myfile, $data);
	 fclose($myfile);

	 // Create a new API_Data entry to track activity
	 $stmt = mysqli_prepare($link, "INSERT INTO API_Responses (Acct_ID,Endpoint_Used,statusCode, dataReturned, arNumber, messageText, sentJSON, returnedJSON, createdDate) VALUES (?,?,?,?,?,?,?,?,NOW())");

	 /* Bind variables to parameters */
	 $val1 = $dealerID;
	 $val2 = $url;
	 $val3 = $responseStatus;
	 $val4 = $new_TNG_WarrantyID;
	 $val5 = $new_TNG_WarrantyID;
	 $val6 = $apiMessage;
	 $val7 = $filename;  // Save path to json file instead of full json in DB
	 $val8 = $resp;

	 mysqli_stmt_bind_param($stmt, "isssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8);

	 /* Execute the statement */
	 $result = mysqli_stmt_execute($stmt);

echo "<br />got here 1";
echo "<br />smallGoodsPackage=".$smallGoodsPackage;

	 // If this warranty has small goods, then we need to send each one to TNG separately
	 if($smallGoodsPackage=="Y"){

		// Hash table of assetTypes for TNG
		$assetTypesLookup["RECIPROCATING SAW"] = "recipSaw";
		$assetTypesLookup["DEMO SAW"] = "demoSaw";
		$assetTypesLookup["CHAIN SAW"] = "chainSaw";
		$assetTypesLookup["CHAINSAW"] = "chainSaw";
		$assetTypesLookup["IMPACT DRIVER"] = "impactDriver";
		$assetTypesLookup["CFM FAN"] = "fan";
		$assetTypesLookup["GENERATOR"] = "generator";
		$assetTypesLookup["TRASH PUMP"] = "trashPump";
		$assetTypesLookup["ELECTRIC VEHICLE BATTERY EXTINGUISHER"] = "batteryExtinguisher";
		$assetTypesLookup["EV BATTERY EXTINGUISHER SYSTEM"] = "batteryExtinguisher";
		$assetTypesLookup["CUTTERS/SPREADERS/HYDRAULIC RAMS"] = "cutters";
		$assetTypesLookup["HANDHELD CAMERA (INFRARED, NIGHT VISION)"] = "camera";
		$assetTypesLookup["FIRE DRONE"] = "fireDrone";
		$assetTypesLookup["GAS/CHEMICAL METERS"] = "chemMeter";
		$assetTypesLookup["COMMUNICATION RADIO"] = "comRadio";
		$assetTypesLookup["PERSONAL RADIO"] = "personalRadio";
		$assetTypesLookup["SCUBA MASK"] = "scubaMask";
		$assetTypesLookup["SCUBA TANK/HARNESS"] = "scubaTank";
		$assetTypesLookup["SCBA MASK"] = "scbaMask";
		$assetTypesLookup["SCBA PASS ALARM"] = "passAlarm";
		$assetTypesLookup["SCBA TANK"] = "scbaTank";
		$assetTypesLookup["NOZZLES (FOAM/WATER)"] = "nozzles";
		$assetTypesLookup["AIR LIFTING BAG"] = "liftBag";
		$assetTypesLookup["JACKS/STRUTS"] = "jack";
		$assetTypesLookup["POWER STRETCHER"] = "stretcher";
		$assetTypesLookup["DEFIBRILLATOR"] = "defib";
		$assetTypesLookup["HEART MONITOR"] = "heartMonitor";
		$assetTypesLookup["AUTOMATED PORTABLE CPR MACHINE"] = "cprMachine";



		$query  = "SELECT * FROM Sml_Goods_Cvge sgc, Sml_Goods_Gnrc_Prcg sggp WHERE sgc.Cntrct_ID=".$warrantyID." AND
		           sgc.Sml_Goods_Gnrc_Prcg_ID=sggp.Sml_Goods_Gnrc_Prcg_ID AND sgc.Is_Deleted_Flg!='Y'";
		$smallGoodsResult = $link->query($query);

		if (mysqli_num_rows($smallGoodsResult) > 0) {
		  while($row = mysqli_fetch_assoc($smallGoodsResult)) {
			$Item_Cat_Type_Desc = $row["Item_Cat_Type_Desc"];
			$Mfr_Nm = $row["Mfr_Nm"];
			$Ser_nbr = $row["Ser_nbr"];
			$Model_Nbr = $row["Model_Nbr"];
			$Ser_nbr = $row["Ser_nbr"];
			$Sml_Goods_Cvge_ID = $row["Sml_Goods_Cvge_ID"];

			// Get the Receipt image
			$receiptQuery = "SELECT * FROM File_Assets WHERE Sml_Goods_Cvge_ID=".$Sml_Goods_Cvge_ID."
			                ORDER BY File_Asset_ID DESC;";
//echo "receiptQuery=".$receiptQuery;
//echo "<br />";
			$receiptResult = $link->query($receiptQuery);
			$b64ReceiptImage = "";
			if (mysqli_num_rows($receiptResult) > 0) {
				$receiptRow = mysqli_fetch_assoc($receiptResult);
				$receiptImage = ltrim($receiptRow["Path_to_File"], '/');
//echo "receiptImage=".$receiptImage;
//echo "<br />";

				 if (file_exists($receiptImage)) {
					$b64ReceiptImage = base64_encode(file_get_contents($receiptImage));
				 }

			}

			// NEED TO ADD SMALL GOODS SUPPORT FOR PURCHASE PRICE AND PURCHASE DATE

			// Try to get the TNG asset code from the associative array.
			$assetType = $assetTypesLookup[$Item_Cat_Type_Desc];

			// API Call to TruNorth
			$url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/warranties/small-goods/create-addon-asset?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";

			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$headers = array(
				"Accept: application/json",
				"Content-Type: application/json",
			);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);


			$data = "{
			  \"warrantyConnectionID\": \"$new_TNG_WarrantyID\",
			  \"assetType\": \"$assetType\",
			  \"serialNumber\": \"$Ser_nbr\",
			  \"assetMake\": \"$Mfr_Nm\",
			  \"assetModel\": \"$Model_Nbr\",
			  \"purchaseDate\": \"09/01/1998\",
			  \"purchasePrice\": 198,
			  \"receiptFile\": \"$b64ReceiptImage\"
			}";

//echo $data;
//die();
			 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);


			 $resp = curl_exec($curl);
			 curl_close($curl);
			 var_dump($resp);
			 $json = json_decode($resp, true);
			 print_r($json);


			 if (isset($json) && array_key_exists("success", $json)) {
				$responseStatus = $json["success"];
			 } else {
				$responseStatus = 0;
			 }


			 if ($responseStatus == 1) {
				$new_TNG_SmallGoodID = $json["data"];
				$apiMessage = $json["message"];

				// Save the returned Small Good number to the Sml_Goods_Cvge table.
				if($sendType=="prod"){
					$stmt = mysqli_prepare($link, "UPDATE Sml_Goods_Cvge SET Sml_Goods_TNG_ID=? WHERE
												   Sml_Goods_Cvge_ID=?");
				}else{
					$stmt = mysqli_prepare($link, "UPDATE Sml_Goods_Cvge SET Sml_Goods_TNG_ID=? WHERE
												   Sml_Goods_Cvge_ID=?");
				}


				/* Bind variables to parameters */
				$val1 = $new_TNG_SmallGoodID;
				$val2 = $Sml_Goods_Cvge_ID;

				mysqli_stmt_bind_param($stmt, "si", $val1, $val2);

				/* Execute the statement */
				$result = mysqli_stmt_execute($stmt);

			 } else {
				$new_TNG_SmallGoodID = "FAILED";
				$apiMessage = "NONE";
				$responseStatus = 0;
			 }


			 // to avoid bloating the database, save the API message data to the file system, and save the
			 //  path to that file in the database.

			 // Folder will be /api/logs/warranties_to_tng/
			 //  subfolder will be current date YYYYMMDD
			 $currentDate = date('Ymd');
			 $filename = "api/logs/warranties_to_tng/".$currentDate."/2_".random_string(25).".txt";
			 $dirname = dirname($filename);
			 if (!is_dir($dirname)) {
			   mkdir($dirname, 0755, true);
			 }

			 // Open this file, and write our 'data' into it which includes the large PDF.
			 //  Then close file.
			 $myfile = fopen($filename, "w") or die("Unable to open file!");
			 fwrite($myfile, $data);
			 fclose($myfile);


			 // Create a new API_Data entry to track activity
			 $stmt = mysqli_prepare($link, "INSERT INTO API_Responses (Acct_ID,Endpoint_Used,statusCode, dataReturned, arNumber, messageText, sentJSON, returnedJSON, createdDate) VALUES (?,?,?,?,?,?,?,?,NOW())");

			 /* Bind variables to parameters */
			 $val1 = $dealerID;
			 $val2 = $url;
			 $val3 = $responseStatus;
			 $val4 = $new_TNG_WarrantyID;
			 $val5 = $new_TNG_WarrantyID;
			 $val6 = $apiMessage;
			 $val7 = $filename; // save path to json instead of full json body in db.
			 $val8 = $resp;

			 mysqli_stmt_bind_param($stmt, "isssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8);

			 /* Execute the statement */
			 $result = mysqli_stmt_execute($stmt);

		  }
		}

		 // to avoid bloating the database, save the API message data to the file system, and save the
		 //  path to that file in the database.

		 // Folder will be /api/logs/warranties_to_tng/
		 //  subfolder will be current date YYYYMMDD
		 $currentDate = date('Ymd');
		 $filename = "api/logs/warranties_to_tng/".$currentDate."/3_".random_string(25).".txt";
		 $dirname = dirname($filename);
		 if (!is_dir($dirname)) {
		   mkdir($dirname, 0755, true);
		 }

		 // Open this file, and write our 'data' into it which includes the large PDF.
		 //  Then close file.
		 $myfile = fopen($filename, "w") or die("Unable to open file!");
		 fwrite($myfile, $data);
		 fclose($myfile);



		if ($responseStatus == 1) {
			$apiMessage = $json["message"];

			 // Create a new API_Data entry to track activity
			 $stmt = mysqli_prepare($link, "INSERT INTO API_Responses (Acct_ID,Endpoint_Used,statusCode, dataReturned, arNumber, messageText, sentJSON, returnedJSON, createdDate) VALUES (?,?,?,?,?,?,?,?,NOW())");

			 /* Bind variables to parameters */
			 $val1 = $dealerID;
			 $val2 = $url;
			 $val3 = $responseStatus;
			 $val4 = $new_TNG_WarrantyID;
			 $val5 = $new_TNG_WarrantyID;
			 $val6 = $apiMessage;
			 $val7 = $filename;  // save the path to the json file instead of the full json body in db.
			 $val8 = $resp;

			 mysqli_stmt_bind_param($stmt, "isssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8);

			 /* Execute the statement */
			 $result = mysqli_stmt_execute($stmt);


		} else {
			$new_TNG_SmallGoodID = "FAILED";
			$apiMessage = "NONE";
			$responseStatus = 0;

			 // to avoid bloating the database, save the API message data to the file system, and save the
			 //  path to that file in the database.

			 // Folder will be /api/logs/warranties_to_tng/
			 //  subfolder will be current date YYYYMMDD
			 $currentDate = date('Ymd');
			 $filename = "api/logs/warranties_to_tng/".$currentDate."/5_".random_string(25).".txt";
			 $dirname = dirname($filename);
			 if (!is_dir($dirname)) {
			   mkdir($dirname, 0755, true);
			 }

			 // Open this file, and write our 'data' into it which includes the large PDF.
			 //  Then close file.
			 $myfile = fopen($filename, "w") or die("Unable to open file!");
			 fwrite($myfile, $data);
			 fclose($myfile);


			 // Create a new API_Data entry to track activity
			 $stmt = mysqli_prepare($link, "INSERT INTO API_Responses (Acct_ID,Endpoint_Used,statusCode, dataReturned, arNumber, messageText, sentJSON, returnedJSON, createdDate) VALUES (?,?,?,?,?,?,?,?,NOW())");

			 /* Bind variables to parameters */
			 $val1 = $dealerID;
			 $val2 = $url;
			 $val3 = $responseStatus;
			 $val4 = $new_TNG_WarrantyID;
			 $val5 = $new_TNG_WarrantyID;
			 $val6 = $apiMessage;
			 $val7 = $filename;  // save the path to the file instead of full json body in db.
			 $val8 = $resp;

			 mysqli_stmt_bind_param($stmt, "isssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8);

			 /* Execute the statement */
			 $result = mysqli_stmt_execute($stmt);

		}


	 }


	// Now complete the warranty API process, by calling 'send-for-signature' and then 'upsert' again
	// API Call to TruNorth
	$url = "https://vital-trends-api-services-2lzg7n0t.uc.gateway.dev/warranties/send-for-signature?key=AIzaSyDd5htzm_7fFhJsY7oxvE6c8f35FtNKkJk";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$headers = array(
		"Accept: application/json",
		"Content-Type: application/json",
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);


	$data = "{
	  \"warrantyConnectionID\": \"$new_TNG_WarrantyID\",
	  \"customerName\": \"$customerName\",
	  \"customerEmail\": \"$customerEmail\",
	  \"customerPhone\": \"$customerPhone\",
	  \"muteMessages\": true
	}";

	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

	$resp = curl_exec($curl);
	curl_close($curl);
	var_dump($resp);
	$json = json_decode($resp, true);
	print_r($json);

	if (isset($json) && array_key_exists("success", $json)) {
		$responseStatus = $json["success"];
		$apiMessage = $json["message"];
	} else {
		$responseStatus = 0;
		$apiMessage = "ERROR";
	}


	 // to avoid bloating the database, save the API message data to the file system, and save the
	 //  path to that file in the database.

	 // Folder will be /api/logs/warranties_to_tng/
	 //  subfolder will be current date YYYYMMDD
	 $currentDate = date('Ymd');
	 $filename = "api/logs/warranties_to_tng/".$currentDate."/6_".random_string(25).".txt";
     $dirname = dirname($filename);
	 if (!is_dir($dirname)) {
	   mkdir($dirname, 0755, true);
	 }

	 // Open this file, and write our 'data' into it which includes the large PDF.
	 //  Then close file.
     $myfile = fopen($filename, "w") or die("Unable to open file!");
	 fwrite($myfile, $data);
	 fclose($myfile);


	 // Create a new API_Data entry to track activity
	 $stmt = mysqli_prepare($link, "INSERT INTO API_Responses (Acct_ID,Endpoint_Used,statusCode, dataReturned, arNumber, messageText, sentJSON, returnedJSON, createdDate) VALUES (?,?,?,?,?,?,?,?,NOW())");

	 /* Bind variables to parameters */
	 $val1 = $dealerID;
	 $val2 = $warrantyAPIURL;
	 $val3 = $responseStatus;
	 $val4 = $new_TNG_WarrantyID;
	 $val5 = $new_TNG_WarrantyID;
	 $val6 = $apiMessage;
	 $val7 = $filename;  // save path to file instead of full json body in db.
	 $val8 = $resp;

	 mysqli_stmt_bind_param($stmt, "isssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8);

	 /* Execute the statement */
	 $result = mysqli_stmt_execute($stmt);



	// Call warranty upsert again

	$curl = curl_init($warrantyAPIURL);
	curl_setopt($curl, CURLOPT_URL, $warrantyAPIURL);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$headers = array(
		"Accept: application/json",
		"Content-Type: application/json",
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	// Concatenate the Warranty and Maintenance Form docs.
	$Warranty_PDF;
	$Maintenance_Form;
	$b64WarrantyDoc = "";

	$pdfFile1 =  strval($Warranty_PDF);
	$pdfFile2 =  strval($Maintenance_Form);

	$command = "/home/dh_pp7hie/pdftk";
    $baseurl = "/home/dh_pp7hie/portaldev.vitaltrendsusa.com/";
	$filename = "merged.pdf";
	$outputdir = "/home/dh_pp7hie/portaldev.vitaltrendsusa.com/backend/".$filename;
	$pdfFile1 = $baseurl . $pdfFile1;
	if($pdfFile2)
	{
		$pdfFile2 = $baseurl . $pdfFile2;
		exec($command." ".$pdfFile1." ".$pdfFile2." cat output ".$outputdir);
	}
	else
	{
		exec($command." ".$pdfFile1." cat output ".$outputdir);
	}

	$mergedPDF = "backend/merged.pdf";

	$b64WarrantyDoc = base64_encode(file_get_contents($mergedPDF));

	 $data = "{
	  \"warrantyConnectionID\": \"$new_TNG_WarrantyID\",
	  \"truckVIN\": \"$vehIDNumber\",
	  \"customerEmail\": \"$customerEmail\",
	  \"customerPhone\": \"$customerPhone\",
	  \"customerName\": \"$customerName\",
	  \"customerAddress\": {
	  \"street\": \"$customerAddress\",
	  \"street2\": \"\",
	  \"city\": \"$customerCity\",
	  \"state\": \"$customerState\",
	  \"zip\": \"$customerZip\",
	  \"country\": \"US\"
	  },
	  \"companyName\": \"$dealerName\",
	  \"lienHolderName\": \"$lienName\",
	  \"lienHolderPhone\": \"$lienPhone\",";

	if($lienAddress!=""){
		$data .= "
		  \"lienHolderAddress\": {
		  \"street\": \"$lienAddress\",
		  \"street2\": \"\",
		  \"city\": \"$lienCity\",
		  \"state\": \"$lienState\",
		  \"zip\": \"$lienZip\",
		  \"country\": \"US\"
		  },";
	}else{
		$data .= "
		  \"lienHolderAddress\": {
		  \"street\": null,
		  \"street2\": null,
		  \"city\": null,
		  \"state\": null,
		  \"zip\": null,
		  \"country\": null
		  },";
	}

	$data .= "
	  \"vehicleType\": \"$vehTypeForAPI\",
	  \"vehicleMake\": \"$vehMake\",
	  \"vehicleModel\": \"$vehModel\",
	  \"vehicleYear\": $vehYearForAPI,
	  \"vehicleOdometer\": $odometerreadingForAPI,
	  \"vehicleOdometerUnits\": \"$odometerMilesOrKM\",
	  \"vehicleEcm\": $ecmreadingForAPI,
	  \"vehicleEcmUnits\": \"$ecmMilesOrKM\",
	  \"engineHours\": 0,
	  \"engineMake\": \"$engMake\",
	  \"engineModel\": \"$engModel\",
	  \"engineSerial\": \"$engSerial\",
	  \"transmissionMake\": \"$transmissionMake\",
	  \"transmissionModel\": \"$transmissionModel\",
	  \"transmissionSerial\": \"$transmissionSerial\",
	  \"apuMake\": \"$apuMake\",
	  \"apuModel\": \"$apuModel\",";

	if($apuYearForAPI==0){
		$data .= "\"apuYear\": null,";
	}else{
		$data .= "\"apuYear\": ".$apuYearForAPI.",";
	}

	$data .= "
	  \"apuSerial\": \"$apuSerial\",
	  \"dealerID\": \"$dealerAgentEmail\",
	  \"retailerAR\": \"$dealerARNumber\",
	  \"poNumber\": null,
	  \"termLength\": $coverageTerm,
	  \"programPackage\": \"$vehTierTypeForAPI\",
	  \"apuCoverage\": $APU_Coverage,
	  \"apparatusCoverage\": $AEP_Coverage,
	  \"aerialCoverage\": $Aerial_Coverage,
	  \"validationType\": \"$validationType\",
	  \"isPaperWarranty\": true,
	  \"agreementb64File\" : \"$b64WarrantyDoc\",";

	  if($validationType!="ECA"){
		  $data .= "\"inspectionB64File\" : \"$b64InspectionDoc\"";
	  }else{
		  $data .= "\"ecaB64File\" : \"$b64ECADoc\",
		  \"vinPlacardData\" : {\"fileName\" : \"$b64VIN_ImageName\", \"fileBytes\" : \"$b64VIN_Image\"},
		  \"dashboardPhotoData\" : {\"fileName\" : \"$b64Dashboard_ImageName\", \"fileBytes\" : \"$b64Dashboard_Image\"},
		  \"enginePlacardData\" : {\"fileName\" : \"$b64Engine_ImageName\", \"fileBytes\" : \"$b64Engine_Image\"}
		  ";
	  }

	 $data .= "}";

	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

	$resp = curl_exec($curl);
	curl_close($curl);
	var_dump($resp);
	$json = json_decode($resp, true);
	print_r($json);

	if (isset($json) && array_key_exists("success", $json)) {
		$responseStatus = $json["success"];
	} else {
		$responseStatus = 0;
	}


	 // to avoid bloating the database, save the API message data to the file system, and save the
	 //  path to that file in the database.

	 // Folder will be /api/logs/warranties_to_tng/
	 //  subfolder will be current date YYYYMMDD
	 $currentDate = date('Ymd');
	 $filename = "api/logs/warranties_to_tng/".$currentDate."/7_".random_string(25).".txt";
     $dirname = dirname($filename);
	 if (!is_dir($dirname)) {
	   mkdir($dirname, 0755, true);
	 }

	 // Open this file, and write our 'data' into it which includes the large PDF.
	 //  Then close file.
     $myfile = fopen($filename, "w") or die("Unable to open file!");
	 fwrite($myfile, $data);
	 fclose($myfile);

	 // Create a new API_Data entry to track activity
	 $stmt = mysqli_prepare($link, "INSERT INTO API_Responses (Acct_ID,Endpoint_Used,statusCode, dataReturned, arNumber, messageText, sentJSON, returnedJSON, createdDate) VALUES (?,?,?,?,?,?,?,?,NOW())");

	 /* Bind variables to parameters */
	 $val1 = $dealerID;
	 $val2 = $warrantyAPIURL;
	 $val3 = $responseStatus;
	 $val4 = $new_TNG_WarrantyID;
	 $val5 = $new_TNG_WarrantyID;
	 $val6 = $apiMessage;
	 $val7 = $filename;  // save path to saved json file instead of huge json payload.
	 $val8 = $resp;

	 mysqli_stmt_bind_param($stmt, "isssssss", $val1, $val2, $val3, $val4, $val5, $val6, $val7, $val8);

	 /* Execute the statement */
	 $result = mysqli_stmt_execute($stmt);




?>