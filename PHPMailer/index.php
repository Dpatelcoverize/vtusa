<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
  
$mail = new PHPMailer(true);
try {
    //$mail->SMTPDebug = 2;                                       
    $mail->isSMTP();                                            
    $mail->Host       = SMTP_HOST;                    
    $mail->SMTPAuth   = true;                             
    $mail->Username   = SMTP_USERNAME;                 
    $mail->Password   = SMTP_PASSWORD;                        
    $mail->SMTPSecure = SMTP_SECURE;                              
    $mail->Port       = SMTP_PORT;  
  
    $mail->setFrom(SMTP_SENT_FROM, SMTP_SENT_FROM_NAME);           
    $mail->addAddress('heli.t@covrize.com');
    // $mail->addAddress('receiver2@gfg.com', 'Name');
       
    $mail->isHTML(true);                                  
    $mail->Subject = 'Subject';
    $mail->Body    = 'HTML message body in <b>bold</b> ';
    $mail->AltBody = 'Body in plain text for non-HTML mail clients';
    $mail->send();
    echo "Mail has been sent successfully!";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
  
?>