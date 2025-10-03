<?php
session_start();
if (!isset($_SESSION['userid'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect_url");
    exit();
}
define('APP_SECURE', true);
include_once '../includes/dbconn.php';


$user_id = $_SESSION['userid'];
$page_key = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 'cashier';
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
  <link rel="stylesheet" href="css/cashier.css" />

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
  </style>

  <title>East Kentucky Metal</title>

</head>

<body>
  <!-- Preloader -->
  <div class="preloader">
    <img src="../assets/images/logos/logo-icon.svg" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper">
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
                    <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                    <!-- Dark Logo icon -->
                    
                    <!-- Light Logo icon -->
                    <img src="../assets/images/logo.png" alt="homepage" class="light-logo" />
                    <img src="../assets/images/logo.png" alt="homepage" class="dark-logo" />
                  </b>
                  <!--End Logo icon -->
                  <!-- Logo text -->
                 
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
                  <!--End Logo icon -->
                  <!-- Logo text -->
                 
                </a>
              </div>


            </div>
            <ul class="navbar-nav flex-row  gap-2 align-items-center justify-content-center d-flex d-lg-none">
              <li class="nav-item dropdown nav-icon-hover-bg rounded-circle">
                <a class="navbar-toggler nav-link text-white nav-icon-hover border-0" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="">
                    <i class="ti ti-dots fs-7"></i>
                  </span>
                </a>
              </li>
            </ul>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
              <div class="d-flex align-items-center justify-content-between py-2 py-lg-0">
                <ul class="navbar-nav flex-row  align-items-center justify-content-center d-flex d-lg-none">
                  <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="nav-link d-flex d-lg-none align-items-center justify-content-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobilenavbar" aria-controls="offcanvasWithBothOptions">
                      <iconify-icon icon="solar:menu-dots-circle-linear"></iconify-icon>
                    </a>
                  </li>
                  <li class="nav-item hover-dd dropdown nav-icon-hover-bg rounded-circle">
                    <a class="nav-link nav-icon-hover waves-effect waves-dark" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <iconify-icon icon="solar:bell-bing-line-duotone"></iconify-icon>
                      <div class="notify">
                        <span class="heartbit"></span>
                        <span class="point"></span>
                      </div>
                    </a>
                    <div class="dropdown-menu py-0 content-dd  dropdown-menu-animate-up overflow-hidden" aria-labelledby="drop2">

                      <div class="py-3 px-4 bg-primary">
                        <div class="mb-0 fs-6 fw-medium text-white">Notifications</div>
                        <div class="mb-0 fs-2 fw-medium text-white">You have 4 Notifications</div>
                      </div>
                      <div class="message-body" data-simplebar>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center  dropdown-item gap-3   border-bottom">
                          <span class="flex-shrink-0 bg-primary-subtle rounded-circle round-40 d-flex align-items-center justify-content-center fs-6 text-primary">
                            <iconify-icon icon="solar:widget-3-line-duotone"></iconify-icon>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Launch Admin</h6>
                              <span class="fs-2 d-block text-muted ">9:30 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just see the my new admin!</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3  border-bottom">
                          <span class="flex-shrink-0 bg-secondary-subtle rounded-circle round-40 d-flex align-items-center justify-content-center fs-6 text-secondary">
                            <iconify-icon icon="solar:calendar-mark-line-duotone"></iconify-icon>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Event today</h6>
                              <span class="fs-2 d-block text-muted ">9:10 AM</span>
                            </div>

                            <span class="fs-2 d-block text-truncate text-muted">Just a reminder that you have event</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3  border-bottom">
                          <span class="flex-shrink-0 bg-danger-subtle rounded-circle round-40 d-flex align-items-center justify-content-center fs-6 text-danger">
                            <iconify-icon icon="solar:settings-minimalistic-line-duotone"></iconify-icon>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Settings</h6>
                              <span class="fs-2 d-block text-muted ">9:08 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">You can customize this template as you want</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3  border-bottom">
                          <span class="flex-shrink-0 bg-warning-subtle rounded-circle round-40 d-flex align-items-center justify-content-center fs-6 text-warning">
                            <iconify-icon icon="solar:link-circle-line-duotone"></iconify-icon>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Luanch Admin</h6>
                              <span class="fs-2 d-block text-muted ">9:30 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just see the my new admin!</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3  border-bottom">
                          <span class="flex-shrink-0 bg-success-subtle rounded-circle round-40 d-flex align-items-center justify-content-center">
                            <i data-feather="calendar" class="feather-sm fill-white text-success"></i>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Event today</h6>
                              <span class="fs-2 d-block text-muted ">9:10 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just a reminder that you have event</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3  border-bottom">
                          <span class="flex-shrink-0 bg-info-subtle rounded-circle round-40 d-flex align-items-center justify-content-center">
                            <i data-feather="settings" class="feather-sm fill-white text-info"></i>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Settings</h6>
                              <span class="fs-2 d-block text-muted ">9:08 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">You can customize this template as you want</span>
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
                  <li class="nav-item hover-dd dropdown nav-icon-hover-bg rounded-circle">
                    <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <iconify-icon icon="solar:inbox-line-line-duotone"></iconify-icon>
                      <div class="notify">
                        <span class="heartbit"></span>
                        <span class="point"></span>
                      </div>
                    </a>
                    <div class="dropdown-menu py-0 content-dd dropdown-menu-animate-up overflow-hidden" aria-labelledby="drop2">

                      <div class="py-3 px-4 bg-secondary">
                        <div class="mb-0 fs-6 fw-medium text-white">Messages</div>
                        <div class="mb-0 fs-2 fw-medium text-white">You have 5 new messages</div>
                      </div>
                      <div class="message-body" data-simplebar>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-2.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Mathew Anderson</h6>
                              <span class="fs-2 d-block text-muted">9:30 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just see the my new admin!</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-3.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Bianca Anderson</h6>
                              <span class="fs-2 d-block text-muted">9:10 AM</span>
                            </div>

                            <span class="fs-2 d-block text-truncate text-muted">Just a reminder that you have event</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-4.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Andrew Johnson</h6>
                              <span class="fs-2 d-block text-muted">9:08 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">You can customize this template as you want</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-5.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Miyra Strokes</h6>
                              <span class="fs-2 d-block text-muted">9:30 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just see the my new admin!</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-6.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Mark, Stoinus & Rishvi..</h6>
                              <span class="fs-2 d-block text-muted">9:10 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just a reminder that you have event</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-7.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Eliga Rush</h6>
                              <span class="fs-2 d-block text-muted">9:08 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">You can customize this template as you want</span>
                          </div>
                        </a>
                      </div>
                      <div class="p-3">
                        <a class="d-flex btn btn-secondary  align-items-center justify-content-center gap-2" href="javascript:void(0);">
                          <span>Check all Messages</span>
                          <iconify-icon icon="solar:alt-arrow-right-outline" class="iconify-sm"></iconify-icon>
                        </a>
                      </div>

                    </div>
                  </li>
                </ul>
                <ul class="navbar-nav gap-2 flex-row ms-auto align-items-center justify-content-center">

                  <li class="nav-item hover-dd dropdown nav-icon-hover-bg rounded-circle d-none d-lg-block">
                    <a class="nav-link nav-icon-hover waves-effect waves-dark" href="/" aria-expanded="false">
                      <iconify-icon icon="ic:round-home" class="home-icon"></iconify-icon>
                    </a>
                  </li>
                
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

                  <li class="nav-item hover-dd dropdown  nav-icon-hover-bg rounded-circle d-none d-lg-block">
                    <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <iconify-icon icon="solar:inbox-line-line-duotone"></iconify-icon>
                      <div class="notify">
                        <span class="heartbit"></span>
                        <span class="point"></span>
                      </div>
                    </a>
                    <div class="dropdown-menu py-0 content-dd dropdown-menu-animate-up dropdown-menu-end overflow-hidden" aria-labelledby="drop2">

                      <div class="py-3 px-4 bg-secondary">
                        <div class="mb-0 fs-6 fw-medium text-white">Messages</div>
                        <div class="mb-0 fs-2 fw-medium text-white">You have 5 new messages</div>
                      </div>
                      <div class="message-body" data-simplebar>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-2.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Mathew Anderson</h6>
                              <span class="fs-2 d-block text-muted">9:30 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just see the my new admin!</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-3.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Bianca Anderson</h6>
                              <span class="fs-2 d-block text-muted">9:10 AM</span>
                            </div>

                            <span class="fs-2 d-block text-truncate text-muted">Just a reminder that you have event</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-4.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Andrew Johnson</h6>
                              <span class="fs-2 d-block text-muted">9:08 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">You can customize this template as you want</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-5.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Miyra Strokes</h6>
                              <span class="fs-2 d-block text-muted">9:30 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just see the my new admin!</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-6.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Mark, Stoinus & Rishvi..</h6>
                              <span class="fs-2 d-block text-muted">9:10 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just a reminder that you have event</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-7.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Eliga Rush</h6>
                              <span class="fs-2 d-block text-muted">9:08 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">You can customize this template as you want</span>
                          </div>
                        </a>
                      </div>
                      <div class="p-3">
                        <a class="d-flex btn btn-secondary  align-items-center justify-content-center gap-2" href="javascript:void(0);">
                          <span>Check all Messages</span>
                          <iconify-icon icon="solar:alt-arrow-right-outline" class="iconify-sm"></iconify-icon>
                        </a>
                      </div>

                    </div>
                  </li>

                  <li class="nav-item nav-icon-hover-bg d-lg-block rounded-circle">
                    <a class="nav-link nav-icon-hover d-none d-md-flex waves-effect waves-dark" 
                        href="?page=approved_list"
                        data-bs-toggle="tooltip" 
                        data-bs-placement="bottom" 
                        data-bs-html="true"
                        title="<h6 class='mb-0'>Approved List</h6>">
                        <i class="fa fa-check" aria-hidden="true"></i>
                    </a>
                  </li>

                  <li class="nav-item hover-dd dropdown nav-icon-hover-bg rounded-circle d-none d-lg-block">
                    <a class="nav-link nav-icon-hover waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                      <iconify-icon icon="ic:round-search" class="search-icon"></iconify-icon>
                    </a>
                    <div class="dropdown-menu py-0 content-dd  dropdown-menu-animate-up overflow-hidden dropdown-menu-end" aria-labelledby="drop2">

                      <div class="py-3 px-4 bg-primary">
                        <div class="mb-0 fs-6 fw-medium text-white">Search Customers</div>
                      </div>
                      <div class="p-3 d-flex align-items-center border-bottom">
                        <div class="w-100">
                          <div class="d-flex align-items-center justify-content-between">
                            <input type="text" id="customer-search-input" class="form-control" placeholder="Search Customer Name">
                          </div>
                        </div>
                      </div>
                      <div id="customer-search-list"></div>
                    </div>
                  </li>

                  <li class="nav-item nav-icon-hover-bg d-lg-block rounded-circle d-none">
                    <a class="nav-link nav-icon-hover d-none d-md-flex waves-effect waves-dark order-icon" 
                        id="order_product" 
                        href="#" 
                        data-bs-toggle="tooltip" 
                        data-bs-placement="bottom" 
                        data-bs-html="true"
                        title="<h6 class='mb-0'>Order Products from Supplier</h6>">
                      <i class="fas fa-boxes-stacked"></i>
                    </a>
                  </li>

                  <li class="nav-item hover-dd dropdown nav-icon-hover-bg rounded-circle d-none d-lg-block">
                      <a class="nav-link nav-icon-hover waves-effect waves-dark" href="javascript:void(0)" id="view_cart" aria-expanded="false">
                        <iconify-icon icon="ic:round-shopping-cart" class="cart-icon"></iconify-icon>
                        <div class="cart-number">
                          <span id="cartQty" class="cart-quantity">
                            <?php
                              $totalQuantity = 0;
                              if (!empty($_SESSION["cart"])) {
                                foreach ($_SESSION["cart"] as $item) {
                                  $totalQuantity += $item["quantity_cart"];
                                }
                              }
                              echo $totalQuantity;
                            ?>
                          </span>
                        </div>
                      </a>

                      <div class="dropdown-menu py-0 content-dd dropdown-menu-animate-up dropdown-menu-end" 
                          aria-labelledby="drop2" style="width: 40vw; max-height: 50vh; overflow-y: auto; padding: 0; margin: 0;">
                          <div class="py-3 px-4 bg-primary">
                            <div class="d-flex align-items-center justify-content-between">
                              <div class="mb-0 fs-6 fw-medium text-white">Cart Contents</div>
                              <button type="button" class="btn btn-sm mb-2 me-2 " id="clear_cart" style="background-color: #dc3545; color: white;">
                                  <i class="fa fa-trash fs-4 me-2"></i>
                                  Clear Cart
                              </button>
                              <span id="cartTotal" class="mb-0 fs-6 fw-medium text-white"><?= "$" . number_format($_SESSION["grandtotal"] ?? 0, 2); ?></span>
                            </div>
                          </div>
                          <div class="row bg-light text-secondary py-2 mx-0 text-center">
                              <div class="col-1">Image</div>
                              <div class="col-7">Description</div>
                              <div class="col-2">Color</div>
                              <div class="col-1">Qty</div>
                              <div class="col-1"></div>
                          </div>
                          <div class="cart-body" data-simplebar>
                              
                          </div>
                      </div>
                  </li>

                  <!-- ------------------------------- -->
                  <!-- end notification Dropdown -->
                  <!-- ------------------------------- -->

                  <!-- ------------------------------- -->
                  <!-- start profile Dropdown -->
                  <!-- ------------------------------- -->
                  <li class="nav-item hover-dd dropdown">
                    <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <img src="../assets/images/profile/user-1.jpg" alt="user" class="profile-pic rounded-circle round-30" />
                    </a>
                    <div class="dropdown-menu pt-0 content-dd overflow-hidden pt-0 dropdown-menu-end user-dd" aria-labelledby="drop2">
                      <div class="profile-dropdown position-relative" data-simplebar>
                        <div class=" py-3 border-bottom">
                          <div class="d-flex align-items-center px-3">
                            <img src="../assets/images/profile/user-1.jpg" class="rounded-circle round-50" alt="" />
                            <div class="ms-3">
                              <h5 class="mb-1 fs-4">Markarn Doe</h5>
                              <p class="mb-0 fs-2 d-flex align-items-center text-muted">
                                markrarn@wrappixel.com
                              </p>
                            </div>
                          </div>
                        </div>
                        <div class="message-body pb-3">
                          <div class="px-3 pt-3">
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="dark/page-user-profile.html" class=" d-flex  align-items-center ">
                                My Profile
                              </a>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="javascript:void(0)" class=" d-flex  align-items-center ">
                                My Projects
                              </a>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="dark/app-email.html" class=" d-flex  align-items-center ">
                                Inbox
                              </a>
                            </div>
                          </div>
                          <hr>
                          <div class="px-3">
                            <div class="py-8 px-3 d-flex justify-content-between dropdown-item align-items-center h6 mb-0  rounded-2 link">
                              <a href="javascript:void(0)" class="">
                                Mode
                              </a>
                              <div>
                                <a class="moon dark-layout" href="javascript:void(0)">
                                  <iconify-icon icon="solar:moon-line-duotone" class="moon"></iconify-icon>
                                </a>
                                <a class="sun light-layout" href="javascript:void(0)">
                                  <iconify-icon icon="solar:sun-2-line-duotone" class="sun"></iconify-icon>
                                </a>
                              </div>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="dark/page-account-settings.html" class=" d-flex  align-items-center  ">
                                Account Settings
                              </a>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="logout.php" class=" d-flex  align-items-center ">
                                Sign Out
                              </a>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </li>


                  <!-- ------------------------------- -->
                  <!-- end profile Dropdown -->
                  <!-- ------------------------------- -->
                </ul>
              </div>
            </div>
          </nav>
          <!-- ---------------------------------- -->
          <!-- End Vertical Layout Header -->
          <!-- ---------------------------------- -->

          <!-- ------------------------------- -->
          <!-- apps Dropdown in Small screen -->
          <!-- ------------------------------- -->
          <!--  Mobilenavbar -->
          <div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="mobilenavbar" aria-labelledby="offcanvasWithBothOptionsLabel">
            <nav class="sidebar-nav scroll-sidebar">
              <div class="offcanvas-header justify-content-between">
                <a href="index.php" class="text-nowrap logo-img d-block">
                  <b class="logo-icon">
                    <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                    <!-- Dark Logo icon -->
                    <img src="../assets/images/logos/logo-icon.svg" alt="homepage">
                  </b>
                  <!--End Logo icon -->
                  <!-- Logo text -->
                  <span class="logo-text">
                    <!-- dark Logo text -->
                    <img src="../assets/images/logos/logo-text.svg" alt="homepage" class="dark-logo ps-2">
                    <!-- Light Logo text -->
                    <img src="../assets/images/logos/logo-light-text.svg" class="light-logo ps-2" alt="homepage">
                  </span>
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
              </div>
              <div class="offcanvas-body h-n80" data-simplebar>
                <ul id="sidebarnav">
                  <li class="sidebar-item">
                    <a class="sidebar-link has-arrow px-1" href="javascript:void(0)" aria-expanded="false">
                      <span class="d-flex">
                        <iconify-icon icon="solar:shield-plus-outline" class="fs-6"></iconify-icon>
                      </span>
                      <span class="hide-menu">Apps</span>
                    </a>
                    <ul aria-expanded="false" class="collapse first-level my-3">
                      <li class="sidebar-item py-2">
                        <a href="dark/app-chat.html" class="d-flex align-items-center position-relative ">
                          <div class="bg-primary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:chat-line-linear" class="text-primary fs-5"></iconify-icon>
                          </div>
                          <div class="d-inline-block ">
                            <h6 class="mb-0 ">Chat Application</h6>
                            <span class="fs-3 d-block text-muted">New messages arrived</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="dark/app-invoice.html" class="d-flex align-items-center position-relative">
                          <div class="bg-secondary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:bill-list-linear" class="text-secondary fs-5"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0">Invoice App</h6>
                            <span class="fs-3 d-block text-muted">Get latest invoice</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="dark/app-contact2.html" class="d-flex align-items-center position-relative">
                          <div class="bg-success-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:bedside-table-2-linear" class="text-success fs-5"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0">Contact Application</h6>
                            <span class="fs-3 d-block text-muted">2 Unsaved Contacts</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="dark/app-email.html" class="d-flex align-items-center position-relative">
                          <div class="bg-warning-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:letter-unread-linear" class="text-warning fs-5"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0">Email App</h6>
                            <span class="fs-3 d-block text-muted">Get new emails</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="dark/page-user-profile.html" class="d-flex align-items-center position-relative">
                          <div class="bg-danger-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:cart-large-2-linear" class="text-danger fs-5"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0">User Profile</h6>
                            <span class="fs-3 d-block text-muted">learn more information</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="dark/app-calendar.html" class="d-flex align-items-center position-relative">
                          <div class="bg-primary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:calendar-linear" class="text-primary fs-5"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0">Calendar App</h6>
                            <span class="fs-3 d-block text-muted">Get dates</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="dark/app-contact.html" class="d-flex align-items-center position-relative">
                          <div class="bg-secondary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:bedside-table-linear" class="text-secondary fs-5"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0">Contact List Table</h6>
                            <span class="fs-3 d-block text-muted">Add new contact</span>
                          </div>
                        </a>
                      </li>
                      <li class="sidebar-item py-2">
                        <a href="dark/app-notes.html" class="d-flex align-items-center position-relative">
                          <div class="bg-success-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="solar:palette-linear" class="text-success fs-5"></iconify-icon>
                          </div>
                          <div class="d-inline-block">
                            <h6 class="mb-0">Notes Application</h6>
                            <span class="fs-3 d-block text-muted">To-do and Daily tasks</span>
                          </div>
                        </a>
                      </li>
                      <ul class="px-8 mt-7 mb-4">
                        <li class="sidebar-item mb-3">
                          <h5 class="fs-5 fw-semibold">Quick Links</h5>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="dark/page-pricing.html">Pricing
                            Page</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="dark/authentication-login.html">Authentication Design</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="dark/authentication-register.html">Register Now</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="dark/authentication-error.html">404
                            Error Page</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="dark/app-notes.html">Notes App</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="dark/page-user-profile.html">User
                            Application</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="dark/page-account-settings.html">Account Settings</a>
                        </li>
                      </ul>
                    </ul>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link px-1" href="dark/app-chat.html" aria-expanded="false">
                      <span class="d-flex">
                        <iconify-icon icon="solar:chat-unread-outline" class="fs-6"></iconify-icon>
                      </span>
                      <span class="hide-menu">Chat</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link px-1" href="dark/app-calendar.html" aria-expanded="false">
                      <span class="d-flex">
                        <iconify-icon icon="solar:calendar-minimalistic-outline" class="fs-6"></iconify-icon>
                      </span>
                      <span class="hide-menu">Calendar</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link px-1" href="dark/app-email.html" aria-expanded="false">
                      <span class="d-flex">
                        <iconify-icon icon="solar:inbox-unread-outline" class="fs-6"></iconify-icon>
                      </span>
                      <span class="hide-menu">Email</span>
                    </a>
                  </li>
                </ul>
              </div>
            </nav>
          </div>
        </div>
        <div class="app-header with-horizontal">
          <nav class="navbar navbar-expand-xl container-fluid">
            <ul class="navbar-nav gap-2 align-items-center">
              <li class="nav-item d-block d-xl-none">
                <a class="nav-link sidebartoggler ms-n3" id="sidebarCollapse" href="javascript:void(0)">
                  <iconify-icon icon="solar:hamburger-menu-line-duotone"></iconify-icon>
                </a>
              </li>
              <li class="nav-item d-none d-xl-block">
                <div class="brand-logo d-flex align-items-center justify-content-between">
                  <a href="index.php" class="text-nowrap logo-img d-flex align-items-center gap-2">
                    <b class="logo-icon">
                      <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                      <!-- Dark Logo icon -->
                      <img src="../assets/images/logos/logo-light-icon.svg" alt="homepage" class="dark-logo" />
                      <!-- Light Logo icon -->
                      <img src="../assets/images/logos/logo-light-icon.svg" alt="homepage" class="light-logo" />
                    </b>
                    <!--End Logo icon -->
                    <!-- Logo text -->
                    <span class="logo-text">
                      <!-- dark Logo text -->
                      <img src="../assets/images/logos/logo-light-text.svg" alt="homepage" class="dark-logo ps-2" />
                      <!-- Light Logo text -->
                      <img src="../assets/images/logos/logo-light-text.svg" class="light-logo ps-2" alt="homepage" />
                    </span>
                  </a>
                </div>



              </li>

              <li class="nav-item d-none d-lg-block search-box">
                <a class="nav-link nav-icon-hover d-none d-md-flex waves-effect waves-dark" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#exampleModal">
                  <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                </a>
              </li>

              <li class="nav-item hover-dd d-none d-lg-block dropdown">
                <a class="nav-link nav-icon-hover" id="drop2" href="javascript:void(0)" aria-haspopup="true" aria-expanded="false">
                  <iconify-icon icon="solar:widget-3-line-duotone"></iconify-icon>
                </a>
                <div class="dropdown-menu dropdown-menu-nav  dropdown-menu-animate-up py-0" aria-labelledby="drop2">
                  <div class="row align-items-center">
                    <div class="col-8">
                      <div class="ps-3 pt-3">
                        <div class="border-bottom">
                          <div class="row">
                            <div class="col-6">
                              <div class="position-relative">
                                <a href="dark/app-chat.html" class="d-flex align-items-center pb-9 position-relative ">
                                  <div class="bg-primary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:chat-line-linear" class="text-primary fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block ">
                                    <h6 class="mb-0 bg-hover-primary">Chat Application</h6>
                                    <span class="fs-3 d-block text-muted">New messages arrived</span>
                                  </div>
                                </a>
                                <a href="dark/app-invoice.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-secondary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:bill-list-linear" class="text-secondary fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Invoice App</h6>
                                    <span class="fs-3 d-block text-muted">Get latest invoice</span>
                                  </div>
                                </a>
                                <a href="dark/app-contact2.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-success-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:bedside-table-2-linear" class="text-success fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Contact Application</h6>
                                    <span class="fs-3 d-block text-muted">2 Unsaved Contacts</span>
                                  </div>
                                </a>
                                <a href="dark/app-email.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-warning-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:letter-unread-linear" class="text-warning fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Email App</h6>
                                    <span class="fs-3 d-block text-muted">Get new emails</span>
                                  </div>
                                </a>
                              </div>
                            </div>
                            <div class="col-6">
                              <div class="position-relative">
                                <a href="dark/page-user-profile.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-danger-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:cart-large-2-linear" class="text-danger fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">User Profile</h6>
                                    <span class="fs-3 d-block text-muted">learn more information</span>
                                  </div>
                                </a>
                                <a href="dark/app-calendar.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-primary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:calendar-linear" class="text-primary fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Calendar App</h6>
                                    <span class="fs-3 d-block text-muted">Get dates</span>
                                  </div>
                                </a>
                                <a href="dark/app-contact.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-secondary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:bedside-table-linear" class="text-secondary fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Contact List Table</h6>
                                    <span class="fs-3 d-block text-muted">Add new contact</span>
                                  </div>
                                </a>
                                <a href="dark/app-notes.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-success-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:palette-linear" class="text-success fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Notes Application</h6>
                                    <span class="fs-3 d-block text-muted">To-do and Daily tasks</span>
                                  </div>
                                </a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="row align-items-center py-3 pb-1">
                          <div class="col-8">
                            <a class="text-dark d-flex align-items-center lh-1 fs-3 bg-hover-primary" href="dark/page-faq.html">
                              <i class="ti ti-help fs-6 me-2"></i>Frequently Asked Questions
                            </a>
                          </div>
                          <div class="col-4">
                            <div class="d-flex justify-content-end pe-4">
                              <button class="btn btn-primary rounded-pill fs-3">Check</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-4 ms-n7">
                      <div class="position-relative p-3 border-start h-100">
                        <h5 class="fs-5 mb-9 fw-semibold">Quick Links</h5>
                        <ul>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="dark/page-pricing.html">Pricing Page</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="dark/authentication-login.html">Authentication Design</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="dark/authentication-register.html">Register Now</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="dark/authentication-error.html">404 Error Page</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="dark/app-notes.html">Notes App</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="dark/page-user-profile.html">User Application</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="dark/page-account-settings.html">Account Settings</a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            </ul>


            <a class="navbar-toggler nav-icon-hover p-0 border-0 text-white" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <span class="p-2">
                <i class="ti ti-dots fs-7"></i>
              </span>
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
              <div class="d-flex align-items-center justify-content-between">
                <ul class="navbar-nav gap-2 flex-row ms-auto align-items-center justify-content-center">
                 
                  <li class="nav-item nav-icon-hover-bg rounded-circle">
                    <a class="nav-link nav-icon-hover moon dark-layout" href="javascript:void(0)">
                      <iconify-icon icon="solar:moon-line-duotone" class="moon"></iconify-icon>
                    </a>
                    <a class="nav-link nav-icon-hover sun light-layout" href="javascript:void(0)">
                      <iconify-icon icon="solar:sun-2-line-duotone" class="sun"></iconify-icon>
                    </a>
                  </li>



                  <li class="nav-item hover-dd dropdown nav-icon-hover-bg rounded-circle  d-none d-lg-block">
                    <a class="nav-link nav-icon-hover waves-effect waves-dark" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <iconify-icon icon="solar:bell-bing-line-duotone"></iconify-icon>
                      <div class="notify">
                        <span class="heartbit"></span>
                        <span class="point"></span>
                      </div>
                    </a>
                    <div class="dropdown-menu py-0 content-dd  dropdown-menu-animate-up dropdown-menu-end overflow-hidden" aria-labelledby="drop2">

                      <div class="py-3 px-4 bg-primary">
                        <div class="mb-0 fs-6 fw-medium text-white">Notifications</div>
                        <div class="mb-0 fs-2 fw-medium text-white">You have 4 Notifications</div>
                      </div>
                      <div class="message-body" data-simplebar>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center  dropdown-item gap-3   border-bottom">
                          <span class="flex-shrink-0 bg-primary-subtle rounded-circle round-40 d-flex align-items-center justify-content-center fs-6 text-primary">
                            <iconify-icon icon="solar:widget-3-line-duotone"></iconify-icon>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Launch Admin</h6>
                              <span class="fs-2 d-block text-muted ">9:30 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just see the my new admin!</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3  border-bottom">
                          <span class="flex-shrink-0 bg-secondary-subtle rounded-circle round-40 d-flex align-items-center justify-content-center fs-6 text-secondary">
                            <iconify-icon icon="solar:calendar-mark-line-duotone"></iconify-icon>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Event today</h6>
                              <span class="fs-2 d-block text-muted ">9:10 AM</span>
                            </div>

                            <span class="fs-2 d-block text-truncate text-muted">Just a reminder that you have event</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3  border-bottom">
                          <span class="flex-shrink-0 bg-danger-subtle rounded-circle round-40 d-flex align-items-center justify-content-center fs-6 text-danger">
                            <iconify-icon icon="solar:settings-minimalistic-line-duotone"></iconify-icon>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Settings</h6>
                              <span class="fs-2 d-block text-muted ">9:08 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">You can customize this template as you want</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3  border-bottom">
                          <span class="flex-shrink-0 bg-warning-subtle rounded-circle round-40 d-flex align-items-center justify-content-center fs-6 text-warning">
                            <iconify-icon icon="solar:link-circle-line-duotone"></iconify-icon>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Luanch Admin</h6>
                              <span class="fs-2 d-block text-muted ">9:30 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just see the my new admin!</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3  border-bottom">
                          <span class="flex-shrink-0 bg-success-subtle rounded-circle round-40 d-flex align-items-center justify-content-center">
                            <i data-feather="calendar" class="feather-sm fill-white text-success"></i>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Event today</h6>
                              <span class="fs-2 d-block text-muted ">9:10 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just a reminder that you have event</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3  border-bottom">
                          <span class="flex-shrink-0 bg-info-subtle rounded-circle round-40 d-flex align-items-center justify-content-center">
                            <i data-feather="settings" class="feather-sm fill-white text-info"></i>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Settings</h6>
                              <span class="fs-2 d-block text-muted ">9:08 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">You can customize this template as you want</span>
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

                  <li class="nav-item hover-dd dropdown nav-icon-hover-bg rounded-circle d-none d-lg-block">
                    <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <iconify-icon icon="solar:inbox-line-line-duotone"></iconify-icon>
                      <div class="notify">
                        <span class="heartbit"></span>
                        <span class="point"></span>
                      </div>
                    </a>
                    <div class="dropdown-menu py-0 content-dd dropdown-menu-animate-up  dropdown-menu-end overflow-hidden" aria-labelledby="drop2">

                      <div class="py-3 px-4 bg-secondary">
                        <div class="mb-0 fs-6 fw-medium text-white">Messages</div>
                        <div class="mb-0 fs-2 fw-medium text-white">You have 5 new messages</div>
                      </div>
                      <div class="message-body" data-simplebar>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-2.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Mathew Anderson</h6>
                              <span class="fs-2 d-block text-muted">9:30 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just see the my new admin!</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-3.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Bianca Anderson</h6>
                              <span class="fs-2 d-block text-muted">9:10 AM</span>
                            </div>

                            <span class="fs-2 d-block text-truncate text-muted">Just a reminder that you have event</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-4.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Andrew Johnson</h6>
                              <span class="fs-2 d-block text-muted">9:08 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">You can customize this template as you want</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-5.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Miyra Strokes</h6>
                              <span class="fs-2 d-block text-muted">9:30 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just see the my new admin!</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-6.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Mark, Stoinus & Rishvi..</h6>
                              <span class="fs-2 d-block text-muted">9:10 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">Just a reminder that you have event</span>
                          </div>
                        </a>
                        <a href="javascript:void(0)" class="p-3 d-flex align-items-center dropdown-item gap-3 border-bottom">
                          <span class="user-img position-relative d-inline-block">
                            <img src="../assets/images/profile/user-7.jpg" alt="user" class="rounded-circle w-100 round-40" />
                            <span class="profile-status bg-success position-absolute rounded-circle"></span>
                          </span>
                          <div class="w-80">
                            <div class="d-flex align-items-center justify-content-between">
                              <h6 class="mb-1">Eliga Rush</h6>
                              <span class="fs-2 d-block text-muted">9:08 AM</span>
                            </div>
                            <span class="fs-2 d-block text-truncate text-muted">You can customize this template as you want</span>
                          </div>
                        </a>
                      </div>
                      <div class="p-3">
                        <a class="d-flex btn btn-secondary  align-items-center justify-content-center gap-2" href="javascript:void(0);">
                          <span>Check all Messages</span>
                          <iconify-icon icon="solar:alt-arrow-right-outline" class="iconify-sm"></iconify-icon>
                        </a>
                      </div>

                    </div>
                  </li>


                  <!-- ------------------------------- -->
                  <!-- start profile Dropdown -->
                  <!-- ------------------------------- -->
                  <li class="nav-item hover-dd dropdown">
                    <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <img src="../assets/images/profile/user-1.jpg" alt="user" width="30" class="profile-pic rounded-circle" />
                    </a>
                    <div class="dropdown-menu pt-0 content-dd overflow-hidden pt-0 dropdown-menu-end user-dd" aria-labelledby="drop2">
                      <div class="profile-dropdown position-relative" data-simplebar>
                        <div class=" py-3 border-bottom">
                          <div class="d-flex align-items-center px-3">
                            <img src="../assets/images/profile/user-1.jpg" class="rounded-circle round-50" alt="" />
                            <div class="ms-3">
                              <h5 class="mb-1 fs-4">Markarn Doe</h5>
                              <p class="mb-0 fs-2 d-flex align-items-center text-muted">
                                markrarn@wrappixel.com
                              </p>
                            </div>
                          </div>
                        </div>
                        <div class="message-body pb-3">
                          <div class="px-3 pt-3">
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="dark/page-user-profile.html" class=" d-flex  align-items-center ">
                                My Profile
                              </a>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="javascript:void(0)" class=" d-flex  align-items-center ">
                                My Projects
                              </a>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="dark/app-email.html" class=" d-flex  align-items-center ">
                                Inbox
                              </a>
                            </div>
                          </div>
                          <hr>
                          <div class="px-3">
                            <div class="py-8 px-3 d-flex justify-content-between dropdown-item align-items-center h6 mb-0  rounded-2 link">
                              <a href="javascript:void(0)" class="">
                                Mode
                              </a>
                              <div>
                                <a class="moon dark-layout" href="javascript:void(0)">
                                  <iconify-icon icon="solar:moon-line-duotone" class="moon"></iconify-icon>
                                </a>
                                <a class="sun light-layout" href="javascript:void(0)">
                                  <iconify-icon icon="solar:sun-2-line-duotone" class="sun"></iconify-icon>
                                </a>
                              </div>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="dark/page-account-settings.html" class=" d-flex  align-items-center  ">
                                Account Settings
                              </a>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="dark/authentication-login.html" class=" d-flex  align-items-center ">
                                Sign Out
                              </a>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </li>

                  <!-- ------------------------------- -->
                  <!-- end profile Dropdown -->
                  <!-- ------------------------------- -->
                </ul>
              </div>
            </div>
          </nav>
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
                  upa.permission
              FROM pages p
              JOIN user_page_access upa ON upa.page_id = p.id
              WHERE p.url = '$page_key'
              AND upa.staff_id = '$user_id'
              AND upa.permission IN ('view', 'edit')
              AND p.category_id = '2'
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


  function loadCartItemsHeader() {
      $.ajax({
          url: 'pages/index_ajax.php',
          type: 'GET',
          data: { fetch_cart: 'fetch_cart' },
          dataType: 'json',
          success: function(response) {
              if (response.cart_items && Array.isArray(response.cart_items)) {
                  $('.cart-body').empty();
                  response.cart_items.forEach(function(item) {
                      const itemRow = `
                          <div class="row align-items-center text-center py-2 border-bottom mx-0">
                              <div class="col-1 text-center">
                                  <span class="d-flex justify-content-center align-items-center bg-primary-subtle rounded-circle text-primary" 
                                      style="width: 50px; height: 50px;">
                                      <img src="${item.img_src}" alt="Item Image" style="width: 100%; height: 100%; object-fit: cover;">
                                  </span>
                              </div>
                              <div class="col-7">
                                  <h6 class="mb-1">${item.item_name}</h6>
                              </div>
                              <div class="col-2">
                                  <span class="rounded-circle d-inline-block" style="background-color: ${item.color_hex}; width: 20px; height: 20px;"></span>
                              </div>
                              <div class="col-1">
                                  <h6 class="mb-1">${item.quantity}</h6>
                              </div>
                              <div class="col-1">
                                  <button class="btn btn-sm" type="button" data-line="${item.line}" data-id="${item.product_id}" onClick="delete_item(this)">
                                      <i class="fa fa-trash"></i>
                                  </button>
                              </div>
                          </div>
                      `;
                      $('.cart-body').append(itemRow);
                  });
                  $("#cartQty").load(location.href + " #cartQty");
                  $("#cartTotal").load(location.href + " #cartTotal");
              } else {
                  console.error('Invalid data format: cart_items is not an array.');
              }
          },
          error: function(xhr, status, error) {
              console.error('Error fetching cart items:', error);
          }
    });

  }


  $(document).ready(function() {
    $(document).on('mouseenter', '[title]', function () {
        const $el = $(this);

        if ($el.closest('.select2-container').length > 0) return;

        if (!$el.data('bs.tooltip')) {
            $el.tooltip({
                trigger: 'hover',
                placement: 'top'
            }).tooltip('show');
        }
    });

    $(document).on('mouseleave', '[title]', function () {
        const $el = $(this);

        if ($el.closest('.select2-container').length > 0) return;

        if ($el.data('bs.tooltip')) {
            $el.tooltip('dispose');
        }
    });
    
    loadCartItemsHeader();

    $(".phone-inputmask").inputmask("(999) 999-9999");

    $('#customer-search-input').on('input', function() {
        let customerName = $(this).val();
        console.log(customerName);
        if (customerName.length > 0) {
            $.ajax({
                url: 'pages/index_ajax.php',
                type: 'POST',
                data: { 
                  customer_name: customerName,
                  search_customer: 'search_customer'
                },
                success: function(response) {
                    $('#customer-search-list').html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Response Text: ' + jqXHR.responseText);
                    $('#customer-search-list').html('<p class="list-group-item text-danger text-center">Error fetching results</p>');
                }
            });
        } else {
            $('#customer-search-list').empty();
        }
    });

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
</body>

</html>