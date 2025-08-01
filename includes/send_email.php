<?php
class EmailTemplates {
    private $headers;
    private $enableEmail;
    private $enablePhoneMessage;

    public function __construct($enableEmail = true, $enablePhoneMessage = true) {
        $this->enableEmail = $enableEmail;
        $this->enablePhoneMessage = $enablePhoneMessage;

        // Set default email headers
        $this->headers = "MIME-Version: 1.0\r\n";
        $this->headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $this->headers .= "From: no-reply@eastkentuckymetal.com\r\n";
    }

    public function sendEmail($email, $subject, $message) {
        if ($this->enableEmail) {
            if (mail($email, $subject, $message, $this->headers)) {
                return [
                    'success' => true,
                    'message' => "Email sent successfully."
                ];
            } else {
                $error = error_get_last();
                return [
                    'success' => false,
                    'message' => "Email could not be sent.",
                    'error' => isset($error['message']) ? addslashes($error['message']) : 'Unknown error'
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => "Email sending is disabled.",
                'error' => "Sending email set to false"
            ];
        }
    }

    public function sendPhoneMessage($phone, $subject, $message) {
        if ($this->enablePhoneMessage) {
            // Simulated success
            return [
                'success' => true,
                'message' => "Message sent successfully."
            ];
        } else {
            return [
                'success' => false,
                'message' => "Message sending is disabled.",
                'error' => "Sending phone message set to false"
            ];
        }
    }

    public function sendOutOfStockEmail($email, $subject, $listHtml) {
        if (empty($listHtml)) {
            return [
                'success' => false,
                'message' => 'No content provided for the email.'
            ];
        }

        $message = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                    }
                    .container {
                        padding: 20px;
                        border: 1px solid #e0e0e0;
                        background-color: #f9f9f9;
                        width: 80%;
                        margin: 0 auto;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    ul {
                        padding-left: 0;
                        list-style: none;
                    }
                    li {
                        margin-bottom: 8px;
                    }
                    span {
                        display: inline-block;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>$subject</h2>
                    $listHtml
                </div>
            </body>
            </html>
        ";

        return $this->sendEmail($email, $subject, $message);
    }

    public function sendPreOrderEmail($email, $subject, $listHtml) {
        if (empty($listHtml)) {
            return [
                'success' => false,
                'message' => 'No content provided for the email.'
            ];
        }

        $message = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                    }
                    .container {
                        padding: 20px;
                        border: 1px solid #e0e0e0;
                        background-color: #f9f9f9;
                        width: 80%;
                        margin: 0 auto;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    ul {
                        padding-left: 0;
                        list-style: none;
                    }
                    li {
                        margin-bottom: 8px;
                    }
                    span {
                        display: inline-block;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>$subject</h2>
                    $listHtml
                </div>
            </body>
            </html>
        ";

        return $this->sendEmail($email, $subject, $message);
    }

    public function sendEstimateNotif($email, $subject, $link = 'https://metal.ilearnwebtech.com/index.php?page=estimate_list') {
        $message = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                    }
                    .container {
                        padding: 20px;
                        border: 1px solid #e0e0e0;
                        background-color: #f9f9f9;
                        width: 80%;
                        margin: 0 auto;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    h2 {
                        color: #0056b3;
                        margin-bottom: 20px;
                    }
                    p {
                        margin: 5px 0;
                    }
                    .link {
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>$subject</h2>
                    <a href='$link' class='link' target='_blank'>To view estimate details, click this link</a>
                </div>
            </body>
            </html>
        ";

        return $this->sendEmail($email, $subject, $message);
    }

    public function sendEstimateToCustomer($email, $subject, $link) {
        $htmlMessage = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                    }
                    .container {
                        padding: 20px;
                        border: 1px solid #e0e0e0;
                        background-color: #f9f9f9;
                        width: 80%;
                        margin: 0 auto;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    h2 {
                        color: #0056b3;
                        margin-bottom: 20px;
                    }
                    p {
                        margin: 5px 0;
                    }
                    .link {
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>$subject</h2>
                    <a href='$link' class='link' target='_blank'>To view your estimate details, click this link</a>
                </div>
            </body>
            </html>
        ";

        return $this->sendEmail($email, $subject, $htmlMessage);
    }

    public function sendEstimateStatusEmail($email, $subject, $link_url, $id, $key, $shipping_url = '') {
        $html_message = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                    }
                    .container {
                        padding: 20px;
                        border: 1px solid #e0e0e0;
                        background-color: #f9f9f9;
                        width: 80%;
                        margin: 0 auto;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    h2 {
                        color: #0056b3;
                        margin-bottom: 20px;
                    }
                    p {
                        margin: 5px 0;
                    }
                    .link {
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>$subject</h2>
                    <a href='$link_url' class='link' target='_blank'>To estimate order details, click this link</a>
                </div>
            </body>
            </html>
        ";

        if (!empty($email)) {
            $result = $this->sendEmail($email, $subject, $html_message);
            return [
                'success' => true,
                'email_success' => $result['success'],
                'message' => $result['success']
                    ? "Successfully updated status and sent email confirmation."
                    : "Successfully updated status, but email could not be sent.",
                'error' => $result['success'] ? null : ($result['error'] ?? 'Unknown email error'),
                'id' => $id,
                'key' => $key,
                'url' => $shipping_url
            ];
        } else {
            return [
                'success' => true,
                'email_success' => false,
                'message' => "Successfully updated status, but email could not be sent (no email).",
                'error' => 'Missing email',
                'id' => $id,
                'key' => $key,
                'url' => $shipping_url
            ];
        }
    }

    public function sendInvoiceToCustomer($email, $subject, $link) {
        $message = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                        background-color: #f4f4f4;
                        padding: 30px;
                    }
                    .container {
                        padding: 20px;
                        border: 1px solid #e0e0e0;
                        background-color: #ffffff;
                        width: 80%;
                        margin: 0 auto;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
                    }
                    h2 {
                        color: #0056b3;
                        margin-bottom: 15px;
                    }
                    .link {
                        display: inline-block;
                        margin-top: 10px;
                        padding: 10px 15px;
                        background-color: #007bff;
                        color: white;
                        text-decoration: none;
                        border-radius: 5px;
                    }
                    .link:hover {
                        background-color: #0056b3;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>$subject</h2>
                    <p>Your Order Invoice is now ready. Click the button below to view your invoice.</p>
                    <a href='$link' class='link' target='_blank'>View Invoice</a>
                </div>
            </body>
            </html>";

        return $this->sendEmail($email, $subject, $message);
    }

    public function sendReturnNoticeToCustomer($email, $subject, $link, $customer_name = '') {
        $html_message = "
        <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                        background-color: #f4f4f4;
                        padding: 30px;
                    }
                    .container {
                        padding: 20px;
                        border: 1px solid #e0e0e0;
                        background-color: #ffffff;
                        width: 80%;
                        margin: 0 auto;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
                    }
                    h2 {
                        color: #0056b3;
                        margin-bottom: 15px;
                    }
                    .link {
                        display: inline-block;
                        margin-top: 10px;
                        padding: 10px 15px;
                        background-color: #007bff;
                        color: white;
                        text-decoration: none;
                        border-radius: 5px;
                    }
                    .link:hover {
                        background-color: #0056b3;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Product Returns</h2>
                    <p>Hi $customer_name,</p>
                    <p>Your product orders have been successfully returned. Click the button below for more details.</p>
                    <a href='$link' class='link' target='_blank'>View Details</a>
                </div>
            </body>
        </html>";

        return $this->sendEmail($email, $subject, $html_message);
    }

    public function sendOrderToCustomer($email, $name, $subject, $link) {
        $html_message = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                    }
                    .container {
                        padding: 20px;
                        border: 1px solid #e0e0e0;
                        background-color: #f9f9f9;
                        width: 80%;
                        margin: 0 auto;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    h2 {
                        color: #0056b3;
                        margin-bottom: 20px;
                    }
                    .link {
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>$subject</h2>
                    <a href='$link' class='link' target='_blank'>To view order details, click this link</a>
                </div>
            </body>
            </html>
        ";

        return $this->sendEmail($email, $subject, $html_message);
    }

    public function sendStatement($email, $subject, $balance, $link) {
        $htmlMessage = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                    }
                    .container {
                        padding: 20px;
                        border: 1px solid #e0e0e0;
                        background-color: #f9f9f9;
                        width: 80%;
                        margin: 0 auto;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    h2 {
                        color: #0056b3;
                        margin-bottom: 20px;
                    }
                    p {
                        margin: 5px 0;
                    }
                    .link {
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>$subject</h2>
                    <p>This is to remind you of your outstanding balance of $balance.</p>
                    <a href='$link' class='link' target='_blank'>Click this link to view additional details</a>
                </div>
            </body>
            </html>
        ";

        return $this->sendEmail($email, $subject, $htmlMessage);
    }

    public function sendSupplierOrder($email, $subject, $link) {
        $htmlMessage = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                    }
                    .container {
                        padding: 20px;
                        border: 1px solid #e0e0e0;
                        background-color: #f9f9f9;
                        width: 80%;
                        margin: 0 auto;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    h2 {
                        color: #0056b3;
                        margin-bottom: 20px;
                    }
                    .link {
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>$subject</h2>
                    <a href='$link' class='link' target='_blank'>To view order details, click this link</a>
                </div>
            </body>
            </html>
        ";

        return $this->sendEmail($email, $subject, $htmlMessage);
    }

    public function sendSupplierNotif($email, $subject, $link) {
        $htmlMessage = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
                    }
                    .container {
                        padding: 20px;
                        border: 1px solid #e0e0e0;
                        background-color: #f9f9f9;
                        width: 80%;
                        margin: 0 auto;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    h2 {
                        color: #0056b3;
                        margin-bottom: 20px;
                    }
                    .link {
                        font-weight: bold;
                        color: #007bff;
                        text-decoration: none;
                    }
                    .link:hover {
                        text-decoration: underline;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>$subject</h2>
                    <a href='$link' class='link' target='_blank'>Click here to view order details</a>
                </div>
            </body>
            </html>
        ";

        return $this->sendEmail($email, $subject, $htmlMessage);
    }


}