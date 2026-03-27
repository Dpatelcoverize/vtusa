<?php
// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//mysqli_report(MYSQLI_REPORT_ALL);
error_reporting(E_ALL);

// DB Library
require_once "lib/dblib.php";

require 'config.php';
// Email function
require_once "lib/emailHelper.php";

//$accountsPayableContactEmail = "partridge.angela@rescuevehicles.com";
$accountsPayableContactEmail = "cparry@gmail.com";
$accountsPayableFirstName = "Angela";
$accountsPayableLastName = "Partridge";

$to = $accountsPayableContactEmail;
$subject = "Welcome - New Vital Trends Account";
$txt = "You have been signed up for a Vital Trends user account!  Please click here to <a href='https://portal.vitaltrendsusa.com'>log in</a>.\n";
$txt .= "Your user name is: ".$accountsPayableContactEmail."\n";
$txt .= "Your initial password is: PASSWORD \n";
$txt .= "Please note, you will need to change your password upon first login.\n\n";
$txt .= "Thank you!\nVital Trends team";
$headers = "From: admin@vitaltrendsusa.com" . "\r\n" .
"BCC: cparry@gmail.com";

//mail($to,$subject,$txt,$headers);
$moreInfo['user_type']='Admin';
$moreInfo['mail_type']='Dealer';
$moreInfo['email_purpose']='Account Payable Contact Details';	  
$emailResult = sendEmail($to, $accountsPayableFirstName, $accountsPayableLastName, $subject, $txt,$moreInfo);

if($emailResult){
	echo "message sent!";
}else{
	echo "problem sending message";
}

die();


?>