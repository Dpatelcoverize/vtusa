<?php
//
// File: emailHelper.php
// Author: Charles Parry
// Date: 7/12/2022
//


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/vendor/autoload.php';

function sendEmail($toAddress, $firstName, $lastName, $subject, $body,$moreinfo=[])
{
	$to = $toAddress;
	$fullName = $firstName . " " . $lastName;
	$password = randomPassword();
	$hashPassword = password_hash($password, PASSWORD_DEFAULT);
	$moreInfo = array();
	$mail = new PHPMailer(true);
	try {
		//$mail->SMTPDebug = 2;
		$uName = SMTP_USERNAME;
		$pass = SMTP_PASSWORD;
		$mail->isSMTP();
		$mail->Host       = SMTP_HOST;
		$mail->SMTPAuth   = true;
		$mail->Username   = $uName;
		$mail->Password   = $pass;
		$mail->SMTPSecure = SMTP_SECURE;
		$mail->Port       = SMTP_PORT;

		$mail->setFrom(SMTP_SENT_FROM, SMTP_SENT_FROM_NAME);
		$mail->addAddress($to);
		$mail->addBCC('josh@vitaltrendsusa.com');
		$mail->addBCC('cparry@gmail.com');

		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body    = $body;
		$mail->AltBody = 'Body in plain text for non-HTML mail clients';
		if ($mail->send()) {
			$response = [
				"code" => "200",
				"status" => "success",
				"message" => "Email sent successfully."
			];
		} else {
			// If send() method fails but doesn't throw an exception, provide details
			$response = [
				"code" => "500",
				"status" => "failure",
				"message" => "Email sending failed. " . $mail->ErrorInfo
			];
		}
	
		email_tracker($toAddress, $fullName, $subject, $body, $password, $response,$moreinfo);
		return true;
	} catch (Exception $e) {
		$response = [
			"code" => "500",
			"status" => "error",
			"message" => "Email could not be sent. Mailer Error: " . $e->getMessage()
		];
		email_tracker($toAddress,$fullName, $subject, $body, $password, $response,$moreinfo);
		return false;
	}
}


function email_tracker($toAddress, $fullName, $subject, $body, $password, $response,$moreinfo)
{
	$currentDateTime = new DateTime();
	$formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');

	$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

	// Check connection
	if ($link === false) {
		die("ERROR: Could not connect. " . mysqli_connect_error());
	}

	$ipAddress = '';
	// Check if the IP address is passed in the HTTP headers
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		// Check for IP address passed from a shared internet connection
		$ipAddress = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		// Check for IP address passed from a proxy server
		$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		// Default to remote address
		$ipAddress = $_SERVER['REMOTE_ADDR'];
	}
	$stmt = mysqli_prepare($link, "INSERT INTO email_tracker 
(to_fullName,email_from_user, email_to_user, email_purpose, email_status, sent_on, ip_sent_from, mail_type, user_type, code,`description`, created_on)
VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

	// Bind the parameters
	mysqli_stmt_bind_param(
		$stmt,
		"ssssssssssss",
		$fullName,
		$email_from_user,
		$email_to_user,
		$email_purpose,
		$email_status,
		$sent_on,
		$ip_sent_from,
		$mail_type,
		$user_type,
		$code,
		$description,
		$created_on
	);
	
	// Set the values for the parameters
	$email_from_user = SMTP_SENT_FROM;
	$email_to_user = $toAddress;
	$email_purpose = (isset($moreinfo['email_purpose'])) ? $moreinfo['email_purpose'] :'NA';
	$email_status = $response['status'];
	$sent_on = $formattedDateTime;
	$ip_sent_from = $ipAddress;
	$mail_type = (isset($moreinfo['mail_type'])) ? $moreinfo['mail_type'] :'NA';
	$user_type = (isset($moreinfo['user_type'])) ? $moreinfo['user_type'] :'NA';
	$code = $response['code'];
	$description = ($response['message']) ? $response['message'] :'NA';
	$created_on = date('d-m-Y h:i:sa');

	// Execute the statement
	$result = mysqli_stmt_execute($stmt);

	if ($result) {
		return true;
	} else {
		return false;
	}
}
