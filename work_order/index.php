<?php
session_start();
if (!isset($_SESSION['work_order_user_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect_url");
    exit();
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
  

  <!-- Core Css -->
  <link rel="stylesheet" href="../assets/css/styles.css" />

  <style>
      .tooltip {
          z-index: 9999999 !important;
      }

      .tooltip-inner {
          background-color: #f8f9fa !important;
          color: #000 !important;
          border: 1px solid #ced4da;
          font-size: 0.875rem;
          padding: 6px 10px;
          border-radius: 0.25rem;
          box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      }

      .tooltip.bs-tooltip-top .tooltip-arrow::before,
      .tooltip.bs-tooltip-bottom .tooltip-arrow::before,
      .tooltip.bs-tooltip-start .tooltip-arrow::before,
      .tooltip.bs-tooltip-end .tooltip-arrow::before {
          background: #f8f9fa !important;
          border-color: transparent !important;
      }

      .select2-container .select2-dropdown .select2-results__options {
          max-height: 760px !important;
      }
  </style>

  <title>Work Order - East Kentucky Metal</title>

</head>

<body>
  <!-- Preloader -->
  <div class="preloader">
    <img src="../assets/images/logos/logo-icon.svg" alt="loader" class="lds-ripple img-fluid" />
  </div>

  <div id="toast-container" class="position-fixed top-0 end-0 p-3 d-flex flex-column gap-2" style="z-index: 1055; width: 300px;">

    <div class="alert customize-alert alert-dismissible text-success alert-light-success bg-success-subtle fade show remove-close-icon small shadow-sm mb-2" role="alert">
      <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
      <div class="d-flex align-items-center">
        <i class="ti ti-circle-check fs-5 me-2 text-success"></i>
        Start production on Order # 123
      </div>
    </div>

    <div class="alert customize-alert alert-dismissible text-secondary alert-light-secondary bg-secondary-subtle fade show remove-close-icon small shadow-sm mb-2" role="alert">
      <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
      <div class="d-flex align-items-center">
        <i class="ti ti-info-square fs-5 me-2 text-secondary"></i>
        Come see Matt on next break
      </div>
    </div>

    <div class="alert customize-alert alert-dismissible text-danger alert-light-danger bg-danger-subtle fade show remove-close-icon small shadow-sm mb-2" role="alert">
      <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
      <div class="d-flex align-items-center">
        <i class="ti ti-alert-triangle fs-5 me-2 text-danger"></i>
        Don't use Coil # 599
      </div>
    </div>

    <div class="alert customize-alert alert-dismissible text-warning alert-light-warning bg-warning-subtle fade show remove-close-icon small shadow-sm mb-2" role="alert">
      <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
      <div class="d-flex align-items-center">
        <i class="ti ti-alert-circle fs-5 me-2 text-warning"></i>
        Stop production on next Break
      </div>
    </div>

  </div>


  <div id="main-wrapper">

    <?php include 'aside.php';?>

    <div class="page-wrapper">
      <!--  Header Start -->
      <header class="topbar rounded-0 border-0" style="background-color: rgb(0, 51, 160);">
        <div class="with-vertical"><!-- ---------------------------------- -->
          <!-- Start Vertical Layout Header -->
          <!-- ---------------------------------- -->
          <nav class="navbar navbar-expand-lg px-lg-0 px-3 py-0">
            <div class="d-none d-lg-block">
              <div class="brand-logo d-flex align-items-center justify-content-between">
                <a href="index.php" class="text-nowrap logo-img d-flex align-items-center gap-2">
                  <b class="logo-icon">
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
                    <img src="../assets/images/logos/logo-light-icon.svg" alt="homepage" class="dark-logo" />
                    <img src="../assets/images/logo.png" alt="homepage" class="light-logo" width="60%" />
                  </b>
                </a>
              </div>


            </div>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
              <div class="d-flex align-items-center justify-content-between py-2 py-lg-0">
                
                <ul class="navbar-nav gap-2 flex-row ms-auto align-items-center justify-content-center">
                
                  <li class="nav-item nav-icon-hover-bg rounded-circle">
                    <a class="nav-link nav-icon-hover moon dark-layout" href="javascript:void(0)">
                      <iconify-icon icon="solar:moon-line-duotone" class="moon"></iconify-icon>
                    </a>
                    <a class="nav-link nav-icon-hover sun light-layout" href="javascript:void(0)">
                      <iconify-icon icon="solar:sun-2-line-duotone" class="sun"></iconify-icon>
                    </a>
                  </li>

                  <li class="nav-item hover-dd dropdown nav-icon-hover-bg rounded-circle d-none d-lg-block">
                    <a class="nav-link nav-icon-hover waves-effect waves-dark" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <iconify-icon icon="solar:bell-bing-line-duotone"></iconify-icon>
                      <div class="notify">
                        <span class="heartbit"></span>
                        <span class="point"></span>
                      </div>
                    </a>
                    <div class="dropdown-menu py-0 content-dd  dropdown-menu-animate-up overflow-hidden dropdown-menu-end" aria-labelledby="drop2">

                      <div class="py-3 px-4 bg-primary">
                        <div class="mb-0 fs-6 fw-medium text-white">Notifications</div>
                        <div class="mb-0 fs-2 fw-medium text-white">You have 1 Notifications</div>
                      </div>
                      <div class="message-body" data-simplebar>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center  dropdown-item gap-3   border-bottom">
                          <span class="flex-shrink-0 bg-primary-subtle rounded-circle round-40 d-flex align-items-center justify-content-center fs-6 text-primary">
                            <iconify-icon icon="solar:widget-3-line-duotone"></iconify-icon>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">New Coils added</h6>
                              <span class="fs-2 d-block text-muted ">9:30 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">New Coils added to inventory</span>
                          </div>
                        </a>
                      </div>
                      <div class="p-3">
                        <a class="d-flex btn btn-primary  align-items-center justify-content-center gap-2" href="javascript:void(0);">
                          <span>Check all Notifications</span>
                          <iconify-icon icon="solar:alt-arrow-right-outline" class="iconify-sm"></iconify-icon>
                        </a>
                      </div>





                    </div>
                  </li>

                </ul>
              </div>
            </div>
          </nav>
          <!-- ---------------------------------- -->
          <!-- End Vertical Layout Header -->
          <!-- ---------------------------------- -->
        </div>
      </header>
      <!--  Header End -->

      <div class="body-wrapper">
        <div class="container-fluid">
          <?php 
            if (empty($_REQUEST['page'])) {include 'pages/work_order.php';}
            if ($_REQUEST['page'] == "messages") {include 'pages/messages.php';}
            if ($_REQUEST['page'] == "inventory") {include 'pages/inventory.php';}

            if ($_REQUEST['page'] == "work_order_pending") {include 'pages/work_order_pending.php';}
            if ($_REQUEST['page'] == "work_order_run") {include 'pages/work_order_run.php';}
            if ($_REQUEST['page'] == "work_order_finish") {include 'pages/work_order_finish.php';}
            if ($_REQUEST['page'] == "work_order_release") {include 'pages/work_order_release.php';}
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
    document.addEventListener('mouseenter', function (e) {
        const el = e.target instanceof Element ? e.target.closest('[title]') : null;
        if (!el) return;

        if (!el._tooltipInstance) {
            el._tooltipInstance = new bootstrap.Tooltip(el, {
                trigger: 'hover',
                placement: 'top'
            });
            el._tooltipInstance.show();
        }
    }, true);

    document.addEventListener('mouseleave', function (e) {
        const el = e.target instanceof Element ? e.target.closest('[title]') : null;
        if (!el) return;

        if (el._tooltipInstance) {
            el._tooltipInstance.dispose();
            el._tooltipInstance = null;
        }
    }, true);
    
    $(".phone-inputmask").inputmask("(999) 999-9999");
  });
  </script>
</body>

</html>