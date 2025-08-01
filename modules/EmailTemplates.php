<?php

class EmailTemplates {
    private $headers;

    public function __construct() {
        // Default headers for all emails
        $this->headers = "MIME-Version: 1.0" . "\r\n";
        $this->headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $this->headers .= 'From: no-reply@yourdomain.com' . "\r\n";
    }

    private function sendMail($to, $subject, $message) {
        return mail($to, $subject, $message, $this->headers);
    }

    // Welcome Email
    public function sendWelcomeEmail($to, $userName) {
        $subject = "Welcome to Our Service, $userName!";
        $message = "
            <html>
            <body>
                <h2>Welcome, $userName!</h2>
                <p>Thank you for signing up. We're excited to have you on board.</p>
            </body>
            </html>
        ";
        return $this->sendMail($to, $subject, $message);
    }

    // Password Reset Email
    public function sendPasswordResetEmail($to, $resetLink) {
        $subject = "Reset Your Password";
        $message = "
            <html>
            <body>
                <h2>Password Reset</h2>
                <p>Click the link below to reset your password:</p>
                <a href='$resetLink'>$resetLink</a>
            </body>
            </html>
        ";
        return $this->sendMail($to, $subject, $message);
    }

    // Booking Confirmation Email
    public function sendBookingConfirmation($to, $userName, $bookingDetails) {
        $subject = "Your Booking is Confirmed";
        $message = "
            <html>
            <body>
                <h2>Hi $userName,</h2>
                <p>Your booking has been confirmed with the following details:</p>
                <p>$bookingDetails</p>
            </body>
            </html>
        ";
        return $this->sendMail($to, $subject, $message);
    }

    // General Notification Email
    public function sendNotification($to, $subject, $body) {
        $message = "
            <html>
            <body>
                <p>$body</p>
            </body>
            </html>
        ";
        return $this->sendMail($to, $subject, $message);
    }
}

?>
