<?php
require 'phpmailer/vendor/autoload.php';
require_once 'dbconn.php';

function sendEmail($email, $name, $subject, $message) {
    global $enable_email;

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: East Kentucky Metal <vekka@ilearnsda.com>" . "\r\n";
    
    if($enable_email){
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
    }else{
        return [
            'success' => false,
            'message' => "Failed to send. Sending email is disabled.",
            'error' => "Sending email set to false"
        ];
    }
}

function sendPhoneMessage($phone, $name, $subject, $message) {
    global $enable_phone_message;

    if($enable_phone_message){
        $is_success = true;
        if ($is_success) {
            return [
                'success' => true,
                'message' => "Successfully sent message to $name."
            ];
        } else {
            return [
                'success' => false,
                'message' => "Message could not be sent to $name.",
                'error' => 'error'
            ];
        }
    }else{
        return [
            'success' => false,
            'message' => "Failed to send. Sending message is disabled.",
            'error' => "Sending phone message set to false"
        ];
    }
    
}

?>