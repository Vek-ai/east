<?php
require '../includes/phpmailer/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($email, $name, $subject, $message) {
    /* $mail_host = 'smtp.sendgrid.net';
    $api_user = 'apikey';
    $api_pass = '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';

    //credentials
    $from_email = 'vekka@adiqted.com';
    $from_name = 'East Kentucky Metal';
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = $mail_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $api_user;
        $mail->Password   = $api_pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);
        $mail->send();
        
        return [
            'success' => true,
            'message' => "Successfully sent email to $name."
        ];
    } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Email could not be sent to $name.",
                'error' => addslashes($mail->ErrorInfo)
            ];
    } */

    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: vekka@ilearnsda.com" . "\r\n";

    if (mail($email, $subject, $message, $headers)) {
        return [
            'success' => true,
            'message' => "Successfully sent email to $name."
        ];
    } else {
        $error = error_get_last();
        return [
            'success' => false,
            'message' => "Email could not be sent to $name.",
            'error' => isset($error['message']) ? addslashes($error['message']) : 'Unknown error'
        ];
    }

}
?>