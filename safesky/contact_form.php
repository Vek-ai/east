<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['btn-submit'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message_content = htmlspecialchars($_POST['message']);

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
            <p>$message_content</p>
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
        echo "<script>alert('Message has been successfully sent!')</script>";
        $_POST = array();
    } else {
      echo "<script>alert('Failed to send message')</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="dark" data-color-theme="Blue_Theme" data-layout="vertical">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <title>Contact Form</title>

</head>

<style>
  .max-width-1000{
    max-width: 1000px;
  }
</style>

<body>
<div id="main-wrapper">
  <div class="page-wrapper">
    <div class="body-wrapper">
      <div class="container-fluid max-width-1000">
        <div class="col-12 card card-body">
          <img src="safesky.jpg" alt="materialpro-img" class="img-fluid">
          <div class="card card-body">
          
            <div class="row">
              <div class="col-12">
                
              </div>
            </div>

            <div class="row pt-3">
              <div class="col-12">
                <h3>Contact Form</h3>
              </div>
            </div>

            <form id="contactForm" class="form-horizontal" method="post" action="contact_form.php">
              <div class="row pt-3">
                <div class="col-md-12">
                  <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" id="name" name="name" class="form-control" required />
                  </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-12">
                  <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required />
                  </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-12">
                  <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control" required />
                  </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-12">
                  <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                  </div>
                </div>
              </div>

              <div class="form-actions">
                <div class="card-body border-top">
                  <div class="row">
                    <div class="col-6 text-start"></div>
                    <div class="col-6 text-end">
                      <button id="btn-submit" type="submit" name="btn-submit" class="btn btn-primary" style="border-radius: 10%;">Send Message</button>
                    </div>
                  </div>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

</html>
