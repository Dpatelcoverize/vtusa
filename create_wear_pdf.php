<?php

use Classes\GeneratePDF;

$pdfData = [
    'Customer Name' => $customerName,
    "Vehicle FULL VIN" => $Vehicle_Vin_Number,
];

$directory_path = "/uploads/warranty_pdf";
$directory = __DIR__ . $directory_path;

if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

$filename = "/" . str_replace(" ", "_", $customerName) . '_' . time() . '.pdf';
$pdfFileName = $directory . $filename;

$pdf = new GeneratePDF;
$uploaded_file = $pdf->generateWearForm($pdfData, $pdfFileName);

$stmt = mysqli_prepare($link, "INSERT INTO File_Assets(
                                                Acct_ID,
                                                Dealer_Pers_ID,
                                                VT_Pers_ID,
                                                Dealer_Cntrct_ID,
                                                Path_to_File,
                                                File_Asset_Type_ID,
                                                File_Asset_Desc,
                                                createdDate
                                            )
                                VALUES(?, ?, ?, ?, ?, ?, 'Wear form asset', NOW())");

$path = $directory_path . $filename;

$fileType = 15;

mysqli_stmt_bind_param($stmt, "iiiisi", $dealerID, $primary_Contact_Person_id, $adminID, $warrantyID, $path, $fileType);

/* Execute the statement */
$result = mysqli_stmt_execute($stmt);
