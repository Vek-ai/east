<?php
session_start();

require '../includes/dbconn.php';
require '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="dark" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.min.js"></script>

  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="../assets/libs/bootstrap/dist/css/bootstrap-multiselect.css">
  <link rel="stylesheet" href="../assets/libs/select2/dist/css/select2.min.css">
  <link rel="stylesheet" href="../assets/libs/owl.carousel/dist/assets/owl.carousel.min.css">
  <link href="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone.css" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/smoothness/jquery-ui.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
  
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="stylesheet" href="css/custom.css" />

  <title>Supplier - East Kentucky Metal</title>

</head>

<body>
  <div class="preloader">
    <img src="../assets/images/logos/logo-icon.svg" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper">
    <div class="page-wrapper">
      <!--  Header Start -->
      <header class="topbar rounded-0 border-0" style="background-color: rgb(0, 51, 160);">
        <div class="with-vertical">
          <nav class="navbar navbar-expand-lg px-lg-0 px-3 py-0">
            <div class="d-none d-lg-block">
              <div class="brand-logo d-flex align-items-center justify-content-between">
                <a href="index.php" class="text-nowrap logo-img d-flex align-items-center gap-2">
                  <b class="logo-icon">
                    <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                    <!-- Dark Logo icon -->
                    
                    <!-- Light Logo icon -->
                    <img src="../assets/images/logo.png" alt="homepage" class="light-logo" />
                    <img src="../assets/images/logo.png" alt="homepage" class="dark-logo" />
                  </b>
                </a>
              </div>


            </div>

           

            <div class="d-block d-lg-none">
              <div class="brand-logo d-flex align-items-center justify-content-between">
                <a href="index.php" class="text-nowrap logo-img d-flex align-items-center gap-2">
                  <b class="logo-icon">
                    <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                    <!-- Dark Logo icon -->
                    <img src="../assets/images/logos/logo-light-icon.svg" alt="homepage" class="dark-logo" />
                    <!-- Light Logo icon -->
                    <img src="../assets/images/logo.png" alt="homepage" class="light-logo" width="60%" />
                  </b>
                </a>
              </div>
            </div>
          </nav>
        </div>
      </header>
      <!--  Header End -->

      <div class="body-wrapper">
        <div class="container-fluid">
          <?php 
            if (empty($_REQUEST['page'])) {include 'pages/home.php';}
          ?>
        </div>
      </div>
      <script>
  function handleColorTheme(e) {
    $("html").attr("data-color-theme", e);
    $(e).prop("checked", !0);
  }
</script>
      

    </div>
    

  </div>
  <div class="dark-transparent sidebartoggler"></div>
  <script src="../assets/js/vendor.min.js"></script>
  <!-- Import Js Files -->
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.min.js"></script>
  <script src="../assets/js/theme/app.dark.init.js"></script>
  <script src="../assets/js/theme/theme.js"></script>
  <script src="../assets/js/theme/app.min.js"></script>
  <script src="../assets/js/theme/feather.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap-multiselect.min.js"></script>

  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script src="../assets/libs/jvectormap/jquery-jvectormap.min.js"></script>
  <script src="../assets/js/extra-libs/jvectormap/jquery-jvectormap-us-aea-en.js"></script>
  <script src="../assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="../assets/libs/inputmask/dist/jquery.inputmask.min.js"></script>
  <script src="../assets/libs/select2/dist/js/select2.min.js"></script>
  <script src="../assets/libs/owl.carousel/dist/owl.carousel.min.js"></script>
  <script src="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone-min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
  
  <script>
  function handleTheme() {
    function setThemeAttributes(theme, darkDisplay, lightDisplay, sunDisplay, moonDisplay) {
      $("html").attr("data-bs-theme", theme);
      const layoutElement = $(`#${theme}-layout`);
      if (layoutElement.length) {
        layoutElement.prop("checked", true);
      }
      $(`.${darkDisplay}`).hide();
      $(`.${lightDisplay}`).css("display", "flex");
      $(`.${sunDisplay}`).hide();
      $(`.${moonDisplay}`).css("display", "flex");
    }

    const currentTheme = $("html").attr("data-bs-theme") || "dark";
    setThemeAttributes(
      currentTheme,
      currentTheme === "dark" ? "dark-logo" : "light-logo",
      currentTheme === "dark" ? "light-logo" : "dark-logo",
      currentTheme === "dark" ? "moon" : "sun",
      currentTheme === "dark" ? "sun" : "moon"
    );

    $(".dark-layout").on("click", function () {
      setThemeAttributes("dark", "dark-logo", "light-logo", "moon", "sun");
    });

    $(".light-layout").on("click", function () {
      setThemeAttributes("light", "light-logo", "dark-logo", "sun", "moon");
    });
  }

  handleTheme();

  $(document).ready(function() {
    $(".phone-inputmask").inputmask("(999) 999-9999");
  });
  </script>
</body>

</html>