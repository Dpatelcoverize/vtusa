<?php
include 'PDFMerger/PDFMerger.php';

$pdf = new \PDFMerger\PDFMerger;
$pdf1 = strval('warranty_type1.pdf');
$pdf2 =strval(warranty_type2.pdf);
echo $pdf1;
die();
$pdf->addPDF($pd1, 'all', 'P');
$pdf->addPDF($pd2, 'all', 'P');
$pdf->merge();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <button>Print</button>
</body>
</html>