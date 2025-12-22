<?php
session_start();
if (!isset($_SESSION['work_order_user_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect_url");
    exit();
}
include_once '../includes/dbconn.php';
define('APP_SECURE', true);

$user_id = $_SESSION['work_order_user_id'];
$page_key = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 'work_order';
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
  <link rel="stylesheet" href="StaffChatter/css/chat-widget.css">

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
                    <a class="nav-link nav-icon-hover waves-effect waves-dark notificationsContainerIcon" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <iconify-icon icon="solar:bell-bing-line-duotone"></iconify-icon>
                      <div class="notify">
                        <span class="heartbit"></span>
                        <span class="point"></span>
                      </div>
                    </a>
                    <div class="dropdown-menu py-0 content-dd  dropdown-menu-animate-up overflow-hidden dropdown-menu-end" aria-labelledby="drop2">

                      <div class="py-3 px-4 bg-primary">
                        <div class="mb-0 fs-6 fw-medium text-white">Notifications</div>
                        <div class="mb-0 fs-2 fw-medium text-white" id="notifCountLabel">You have 4 Notifications</div>
                      </div>
                      <div class="message-body" data-simplebar id="notificationsContainer" style="max-height: 300px; overflow-y: auto;">
                        <!-- Notifications will be injected here -->
                      </div>
                      <div class="p-3">
                        <a class="d-flex btn btn-primary  align-items-center justify-content-center gap-2" href="?page=notifications">
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
          $query = "
              SELECT 
                  p.id, 
                  p.file_name, 
                  CASE
                      WHEN upa.permission IS NOT NULL THEN upa.permission
                      ELSE app.permission
                  END AS permission
              FROM pages p
              LEFT JOIN user_page_access upa
                  ON upa.page_id = p.id
                  AND upa.staff_id = '$user_id'
                  AND upa.permission IN ('view', 'edit')
              LEFT JOIN staff s
                  ON s.staff_id = '$user_id'
              LEFT JOIN access_profile_pages app
                  ON app.page_id = p.id
                  AND app.access_profile_id = s.access_profile_id
                  AND app.permission IN ('view', 'edit')
              WHERE p.url = '$page_key'
                  AND p.category_id = '3'
                  AND (
                      upa.permission IS NOT NULL
                      OR app.permission IS NOT NULL
                  )
              LIMIT 1
          ";
          $result = mysqli_query($conn, $query);
          if ($row = mysqli_fetch_assoc($result)) {
              $_SESSION['permission'] = $row['permission'];
              include "pages/" . $row['file_name'];
          } else {
              include "not_authorized.php";
          }
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

    let notificationCooldown = false;

    function fetchNotifications() {
      if (notificationCooldown) return;

      $.ajax({
        url: 'pages/index_ajax.php',
        type: 'POST',
        data: { 
          fetch_notifications: 'fetch_notifications' 
        },
        success: function (response) {
          try {
            const data = JSON.parse(response);
            $('#notifCountLabel').text(`You have ${data.count} Notifications`);
            $('#notificationsContainer').html(data.html);
            notificationCooldown = true;

            setTimeout(() => {
              notificationCooldown = false;
            }, 60000);
          } catch (e) {
            console.error('Invalid JSON response:', response);
            $('#notificationsContainer').html('<div class="p-3 text-center text-danger">Invalid server response.</div>');
          }
        },
        error: function () {
          $('#notificationsContainer').html('<div class="p-3 text-center text-danger">Error loading notifications.</div>');
        }
      });
    }

    $(document).on('click', '.notificationsContainerIcon', function () {
      fetchNotifications();
    });

    $(document).on('click', '.notification-link', function (e) {
        e.preventDefault();

        const notifId = $(this).data('id');
        const targetUrl = $(this).data('url');

        $.ajax({
            url: 'pages/index_ajax.php',
            method: 'POST',
            data: { 
              notification_id: notifId,
              read_notification: "read_notification"
            },
            success: function () {
                window.location.href = targetUrl;
            },
            error: function (xhr, status, error) {
                alert('An error occurred while marking the notification as read. See console for details.');
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
            }

        });
    });


    fetchNotifications();
  });
  </script>
  <?php if (isset($_SESSION['work_order_user_id']) && isset($_SESSION['fullname'])): ?>
  <script src="StaffChatter/js/chat-widget.js"></script>
  <script>
      const chatWidget = new ChatWidget({
          currentUserId: <?php echo $_SESSION['work_order_user_id']; ?>,
          currentUserName: '<?php echo htmlspecialchars($_SESSION['fullname']); ?>'
      });
  </script>
  <?php endif; ?>
</body>

</html>