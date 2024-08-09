<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/phpmailer/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $subject = mysqli_real_escape_string($conn, $_POST['subject']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);

        $insertQuery = "INSERT INTO contact_form (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";
        if (mysqli_query($conn, $insertQuery)) {
            // Send email

            $mail = new PHPMailer(true);

            $owner_email = "kurumitaku555@gmail.com";
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
                        .label {
                            font-weight: bold;
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>New Contact Form Submission</h2>
                        <p class='label'>Name:</p>
                        <p>$name</p>
                        <p class='label'>Email:</p>
                        <p>$email</p>
                        <p class='label'>Subject:</p>
                        <p>$subject</p>
                        <p class='label'>Message:</p>
                        <p>$message</p>
                    </div>
                </body>
                </html>
                ";

            try {
                //Server settings
                $mail->SMTPDebug = 0; // Enable verbose debug output
                $mail->isSMTP(); // Set mailer to use SMTP
                $mail->Host       = 'smtp.sendgrid.net'; // Specify main and backup SMTP servers
                $mail->SMTPAuth   = true; // Enable SMTP authentication
                $mail->Username   = 'apikey';       // SMTP username
                $mail->Password   = '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'; // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable SSL encryption, 'tls' also accepted
                $mail->Port       = 465; // TCP port to connect to, use 587 if using 'tls'
            
                //Recipients
                $mail->setFrom('claims@mymotorclaim.com.au', 'My Motor Claim');
                $mail->addAddress($owner_email, "Safesky"); // Add a recipient 
                // Content
                $mail->isHTML(true); // Set email format to HTML
                $mail->Subject = 'New Contact Form Submission - '.$name;
                $mail->Body    = $message;
                $mail->AltBody = $message;
            
                $mail->send();
            
                echo 'add-success';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error adding product gauge: " . mysqli_error($conn);
        }
    }
    mysqli_close($conn);
}
?>
