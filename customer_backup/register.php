<?php
session_start();

include "../includes/dbconn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $conn->real_escape_string($_POST['username']);
  $password = $_POST['password'];
  $redirect = (!empty($_REQUEST['redirect']) && $_REQUEST['redirect'] !== 'login.php') ? $_REQUEST['redirect'] : 'index.php';

  $sql = "SELECT customer_id, password FROM customer WHERE username = '$username'";
  $result = $conn->query($sql);

  if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $db_password = $row['password'];
      $customer_id = $row['customer_id'];

      if (password_verify($password, $db_password)) {
          $_SESSION['customer_id'] = $customer_id;
          setcookie("userid", $customer_id, time() + (86400 * 30), "/");

          header("Location: $redirect");
          exit();
      } else {
          $error = 'Invalid username or password.';
      }
  } else {
      $error = 'Invalid username or password.';
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
  <link rel="shortcut icon" type="image/png" href="../../assets/images/logos/favicon.png" />
  <!-- Core Css -->
  <link rel="stylesheet" href="../../assets/css/styles.css" />
  <title>Register</title>

    <script src="https://accounts.google.com/gsi/client" defer></script> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
  <!-- Preloader -->
  <div class="preloader">
    <img src="../assets/images/logo.png" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper" class="auth-customizer-none">
    <div class="position-relative overflow-hidden radial-gradient min-vh-100 w-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3 auth-card">
            <div class="card mb-0">
              <div class="card-body">
                <a href="javascript:void(0)" class="text-nowrap logo-img d-flex align-items-center justify-content-center gap-2 mb-4 w-100">
                  <b class="logo-icon">
                    <img src="../assets/images/logo.png" alt="homepage" class="dark-logo" />
                  </b>
                </a>
                <div id="errorAlert" class="alert alert-danger d-none" role="alert">
                    <h5 id="errorMsg"></h5>
                </div>
                <div class="row text-center">
                    <div class="col-12 mb-2 mb-sm-0">
                        <a id="googleBtn" class="d-flex justify-content-center" href="javascript:void(0)"></a>
                    </div>
                </div>
                <div class="position-relative text-center my-4">
                  <p class="mb-0 fs-4 px-3 d-inline-block bg-white z-index-5 position-relative">or sign Up with</p>
                  <span class="border-top w-100 position-absolute top-50 start-50 translate-middle"></span>
                </div>
                <form action="" id="signupForm" method="post">
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                        <div class="">
                            <label for="customer_first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="customer_first_name" name="customer_first_name" required>
                        </div>
                        <div class="">
                            <label for="customer_last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="customer_last_name" name="customer_last_name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-4">
                        <label for="exampleInputPassword1" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 mb-4">Register</button>
                  <div class="position-relative text-center my-4">
                    <p class="mb-0 fs-4 px-3 d-inline-block bg-white z-index-5 position-relative">Existing Account?</p>
                    <span class="border-top w-100 position-absolute top-50 start-50 translate-middle"></span>
                  </div>
                  <div class="d-flex align-items-center justify-content-center mb-2">
                    <a class="text-primary fw-medium" href="login.php">Log in</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
<div class="modal fade" id="response-modal" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div id="responseHeaderContainer" class="modal-header align-items-center modal-colored-header">
            <h4 id="responseHeader" class="m-0"></h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            
            <p id="responseMsg"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
            Close
            </button>
        </div>
        </div>
    </div>
</div>

<!-- Import Js Files -->
<script src="../../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/libs/simplebar/dist/simplebar.min.js"></script>
<script src="../../assets/js/theme/app.dark.init.js"></script>
<script src="../../assets/js/theme/theme.js"></script>
<script src="../../assets/js/theme/app.min.js"></script>
<script src="../../assets/js/theme/feather.min.js"></script>
<!-- solar icons -->
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

<script>
$(document).ready(function () {
    window.onload = function () {
        if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
            google.accounts.id.initialize({
                client_id: '<?=$google_auth_client_id?>',
                callback: handleGoogleLogin,
                ux_mode: 'popup'
            });

            google.accounts.id.renderButton(
                document.getElementById("googleBtn"),
                { 
                    theme: "standard",
                    size: "large",
                    shape: "pill",
                    width: 200
                }
            );
        }

        function handleGoogleLogin(response) {
            $.ajax({
                url: 'pages/register_ajax.php',
                method: 'POST',
                data: {
                    token: response.credential,
                    action: 'google_login'
                },
                success: function (data) {
                    if (data.trim() === 'success') {
                        $('#responseHeader').text("Customer Application Success!");
                        $('#responseMsg').text("Please wait for admin to approve your application.");
                        $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            window.location.href = 'login.php';
                        });
                    } else {
                        $('#errorMsg').text(data);
                        $('#errorAlert').removeClass('d-none');
                    }
                },
                error: function (xhr) {
                    console.error('Login error:', xhr.responseText);
                    alert('Google Sign-In failed.');
                }
            });
        }
    };

    $('#signupForm').on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'register_account');

        $.ajax({
            url: 'pages/register_ajax.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.trim() === 'success') {
                    $('#responseHeader').text("Customer Application Success!");
                    $('#responseMsg').text("Please wait for admin to approve your application.");
                    $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                    $('#response-modal').modal("show");

                    $('#response-modal').on('hide.bs.modal', function () {
                        window.location.href = 'login.php';
                    });
                } else {
                    $('#errorMsg').text(response);
                    $('#errorAlert').removeClass('d-none');
                }
            },
            error: function (xhr) {
                const errorText = xhr.responseText || 'Error saving customer.';
                $('#errorMsg').text(errorText);
                $('#errorAlert').removeClass('d-none');
            }
        });
    });
});
</script>

</body>
</html>
