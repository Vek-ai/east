<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

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
            $to = 'kurumitaku555@gmail.com';
            $email_subject = "New Contact Form Submission: $subject";

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

            // Headers for HTML email
            $headers  = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            // Additional headers
            $headers .= "From: noreply@ilearnwebtech.com" . "\r\n";
            $headers .= "Reply-To: $email" . "\r\n";

            if (mail($to, $email_subject, $message, $headers)) {
                echo "add-success";
            } else {
                echo "Error sending email.";
            }

        } else {
            echo "Error adding product gauge: " . mysqli_error($conn);
        }
    }
    mysqli_close($conn);
}
?>
