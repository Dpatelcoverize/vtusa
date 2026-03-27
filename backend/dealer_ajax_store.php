<?php
try {
    require_once "../includes/dbConnect.php";

    if ($_POST) {
        $fields = (object)$_POST;

        $images = [];
        for ($i = 0; $i < count($fields->image); $i++) {
            $image     =    base64_decode($fields->image[$i]);
            $imageData = $_POST['image'][$i];
            list($type, $imageData) = explode(';', $imageData);
            list(, $extension) = explode('/', $type);
            list(, $imageData)      = explode(',', $imageData);
            $fileName = uniqid() . '.' . $extension;
            $imageData = base64_decode($imageData);
            $image = '/uploads/' . $fileName;
            file_put_contents('..' . $image, $imageData);
            array_push($images, $image);
        }

        // $image     =    base64_decode($fields->image);
        // $imageData = $_POST['image'];
        // list($type, $imageData) = explode(';', $imageData);
        // list(,$extension) = explode('/',$type);
        // list(,$imageData)      = explode(',', $imageData);
        // $fileName = uniqid().'.'.$extension;
        // $imageData = base64_decode($imageData);
        // $image = '/uploads/'.$fileName;
        // file_put_contents('..'.$image, $imageData);
        $image = json_encode($images);
        $my_date = date("Y-m-d H:i:s");

        $sql = "INSERT INTO dealers_agreement VALUES (null,'$fields->agreementDate','$fields->dealerName','$fields->dba','$fields->taxID','$fields->duns','$fields->dealerAddress','$fields->poBox','$fields->dealerCity','$fields->dealerState','$fields->zipCode','$fields->dealerPhone','$fields->dealerFax','$fields->dealerLicense','$fields->businessEmail','$fields->businessWebsite','$fields->primaryContact','$fields->primaryContactPhone','$fields->primaryContactEmail','$fields->accountsPayableContact','$fields->accountsPayableContactPhone','$fields->accountsPayableContactEmail','$fields->shipAddress','$fields->shipCity','$fields->shipState','$fields->shipZip','$fields->retailerName','$fields->retailerTitle','$fields->signedOnDate','$image','$my_date','$my_date' )";
        if (mysqli_query($link, $sql)) {
            echo json_encode(['status' => 200, 'message' => 'Record inserted successfully']);
        } else {
            echo json_encode(['status' => 200, 'message' => 'Something went wrong please try again later']);
        }
    }
} catch (Exception $exception) {
    echo json_encode(['status' => 400, 'message' => $exception->getMessage()]);
}
