<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (isset($_POST['action'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

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
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="dark" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
  <!-- Required meta tags -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Favicon icon-->
  <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />

  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <title>Contact Form</title>

</head>

<body>
  <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
      <div class="d-flex justify-content-between align-items-center">
        <div><br>
          <h4 class="font-weight-medium fs-14 mb-0">Contact Form</h4>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
              </li>
              <li class="breadcrumb-item text-muted" aria-current="page">Contact Form</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12">
    <!-- start Default Form Elements -->
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

      <form id="contactForm" class="form-horizontal" method="post">
        <div class="row pt-3">
          <div class="col-md-12">
            <div class="mb-3">
              <label c  lass="form-label">Name</label>
              <input type="text" id="name" name="name" class="form-control"/>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-12">
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" id="email" name="email" class="form-control"/>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-12">
            <div class="mb-3">
              <label class="form-label">Subject</label>
              <input type="text" id="subject" name="subject" class="form-control"/>
            </div>
          </div>
        </div>

        
        <div class="row pt-3">
          <div class="col-md-12">
            <div class="mb-3">
              <label class="form-label">Message</label>
              <textarea class="form-control" id="message" name="message" rows="5"></textarea>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <div class="card-body border-top ">
            <div class="row">
              
              <div class="col-6 text-start">
              
              </div>
              <div class="col-6 text-end">
                <button type="submit" class="btn btn-primary" style="border-radius: 10%;">Send Message</button>
              </div>
            </div>
            
          </div>
        </div>

      </form>
    </div>
    <!-- end Default Form Elements -->
  </div>

</body>