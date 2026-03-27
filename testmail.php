<?php
/**PHP mailer */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
  
require 'PHPMailer/vendor/autoload.php';
  
$mail = new PHPMailer(true);
  
try {
    //$mail->SMTPDebug = 2;                                       
    $mail->isSMTP();                                            
    $mail->Host       = 'smtp.dreamhost.com';                    
    $mail->SMTPAuth   = true;                             
    $mail->Username   = 'info@vitaltrendsusa.com';                 
    $mail->Password   = 'z]p3RjC$HnPTd3+TLKnM';                        
    $mail->SMTPSecure = 'tls';                              
    $mail->Port       = 587;  
  
    $mail->setFrom('info@vitaltrendsusa.com', 'info');           
    $mail->addAddress('vikas.malaviya.2991@gmail.com');
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