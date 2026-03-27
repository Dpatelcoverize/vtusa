<?php

// Connect to DB
require_once "../../includes/dbConnect.php";

// DB Library
require_once "../../lib/dblib.php";

    //Warranty count
    $year =  date("Y");;

    $warrantyCount_data = [
        ['Months', 'Warranties Written', 'Warranties Paid', 'Total Warranties'],
        ['Jan', 0 , 0, 0],
        ['Feb', 0 , 0, 0],
        ['Mar', 0 , 0, 0],
        ['Apr', 0 , 0, 0],
        ['May', 0 , 0, 0],
        ['Jun', 0 , 0, 0],
        ['Jul', 0 , 0, 0],
        ['Aug', 0 , 0, 0],
        ['Sep', 0 , 0, 0],
        ['Oct', 0 , 0, 0],
        ['Nov', 0 , 0, 0],
        ['Dec', 0 , 0, 0],
    ];

    $query = "SELECT month(Cntrct.Created_Date) AS month, count(Cntrct.Cntrct_ID) AS warranties_written FROM Cntrct, Cntrct_Dim WHERE Cntrct.Cntrct_Dim_ID = Cntrct_Dim.Cntrct_Dim_ID AND YEAR(Cntrct.Created_Date) = '$year' GROUP By month(Cntrct.Created_Date)";
    $res = $link->query($query);

    while( $row = mysqli_fetch_assoc($res))
   {
        $month =  $row["month"];
        $warranties_written = intval($row["warranties_written"]);
        $warrantyCount_data[$month][1] = $warranties_written;
        $warrantyCount_data[$month][3] = $warranties_written;
   }


   //Commissions
//   $commissions =  [
//                     ['Months', 'Commission Sent', 'Commission Received', 'Total Commission'],
//                     ['Jan', 60000, 30000, 90000],
//                     ['Feb', 90000, 45000, 135000],
//                     ['Mar', 120000, 60000, 180000],
//                     ['Apr', 150000, 75000, 225000],
//                     ['May', 180000, 90000, 270000],
//                     ['Jun', 180000, 90000, 270000],
//                     ['Jul', 150000, 75000, 225000],
//                     ['Aug', 240000, 120000, 360000],
//                     ['Sep', 210000, 105000, 315000],
//                     ['Oct', 270000, 135000, 405000],
//                     ['Nov', 240000, 120000, 360000],
//                     ['Dec', 300000, 150000, 450000],
//                 ];


// Affiliate Fees
// $affiiate_fees = [
//                     ['Months', 'Affiliate Fees Paid', 'Outstanding Affiliate Fees', 'Total Affiliate Fees'],
//                     ['Jan', 2000, 2000, 4000],
//                     ['Feb', 3200, 2800, 6000],
//                     ['Mar', 4000, 4000, 8000],
//                     ['Apr', 5200, 4800, 10000],
//                     ['May', 6000, 6000, 12000],
//                     ['Jun', 6000, 6000, 12000],
//                     ['Jul', 5200, 4800, 10000],
//                     ['Aug', 8000, 8000, 16000],
//                     ['Sep', 7200, 6800, 14000],
//                     ['Oct', 9200, 8800, 18000],
//                     ['Nov', 8000, 8000, 16000],
//                     ['Dec', 10000, 10000, 20000],
// ];

   header('Content-Type: application/json');
   print json_encode($warrantyCount_data);
?>