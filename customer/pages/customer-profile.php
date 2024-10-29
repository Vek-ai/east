<?php
session_start();
include '../../includes/dbconn.php';

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

global $currentUser;

$userId = $_SESSION['userid'];

$sql = "SELECT * FROM customer WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $currentUser = $result->fetch_assoc();
} else {
    $currentUser = null;
}

$stmt->close();
$conn->close();

$firstName = isset($currentUser['customer_first_name']) ? htmlspecialchars($currentUser['customer_first_name']) : 'First Name';
$lastName = isset($currentUser['customer_last_name']) ? htmlspecialchars($currentUser['customer_last_name']) : 'Last Name';
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

  <title>MaterialPro Template by WrapPixel</title>
</head>

<body>
  <!-- Preloader -->
  <div class="preloader">
    <img src="../../assets/images/logos/logo-icon.svg" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper">
    <!-- Sidebar Start -->
    <?php include "../aside.php"?>
    <!--  Sidebar End -->
    <div class="page-wrapper">
      <!--  Header Start -->
      <header class="topbar rounded-0 border-0 bg-primary">
        <div class="with-vertical"><!-- ---------------------------------- -->
          <!-- Start Vertical Layout Header -->
          <!-- ---------------------------------- -->
          <nav class="navbar navbar-expand-lg px-lg-0 px-3 py-0">
            <div class="d-none d-lg-block">
              <div class="brand-logo d-flex align-items-center justify-content-between">
                <a href="../../dark/index.html" class="text-nowrap logo-img d-flex align-items-center gap-2">
                  <b class="logo-icon">
                    <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                    <!-- Dark Logo icon -->
                    <img src="../../assets/images/logos/logo-light-icon.svg" alt="homepage" class="dark-logo" />
                    <!-- Light Logo icon -->
                    <img src="../../assets/images/logos/logo-light-icon.svg" alt="homepage" class="light-logo" />
                  </b>
                  <!--End Logo icon -->
                  <!-- Logo text -->
                  <span class="logo-text">
                    <!-- dark Logo text -->
                    <img src="../../assets/images/logos/logo-light-text.svg" alt="homepage" class="dark-logo ps-2" />
                    <!-- Light Logo text -->
                    <img src="../../assets/images/logos/logo-light-text.svg" class="light-logo ps-2" alt="homepage" />
                  </span>
                </a>
              </div>


            </div>

            <ul class="navbar-nav gap-2">

              <li class="nav-item nav-icon-hover-bg rounded-circle">
                <a class="nav-link nav-icon-hover sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                  <iconify-icon icon="solar:list-bold"></iconify-icon>
                </a>
              </li>
              <!-- ------------------------------- -->
              <!-- start notification Dropdown -->
              <!-- ------------------------------- -->
              <li class="nav-item d-none d-lg-block search-box nav-icon-hover-bg rounded-circle">
                <a class="nav-link nav-icon-hover d-none d-md-flex waves-effect waves-dark" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#exampleModal">
                  <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                </a>
              </li>


              <li class="nav-item d-none d-lg-block dropdown nav-icon-hover-bg rounded-circle">
                <a class="nav-link nav-icon-hover" id="drop2" href="javascript:void(0)" aria-haspopup="true" aria-expanded="false">
                  <iconify-icon icon="solar:widget-3-line-duotone"></iconify-icon>
                </a>
                <div class="dropdown-menu dropdown-menu-nav dropdown-menu-animate-up py-0 overflow-hidden" aria-labelledby="drop2">
                  <div class="row align-items-center">
                    <div class="col-8">
                      <div class="ps-3 pt-3">
                        <div class="border-bottom">
                          <div class="row">
                            <div class="col-6">
                              <div class="position-relative">
                                <a href="../../dark/app-chat.html" class="d-flex align-items-center pb-9 position-relative ">
                                  <div class="bg-primary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:chat-line-linear" class="text-primary fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block ">
                                    <h6 class="mb-0 bg-hover-primary">Chat Application</h6>
                                    <span class="fs-3 d-block text-muted">New messages arrived</span>
                                  </div>
                                </a>
                                <a href="../../dark/app-invoice.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-secondary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:bill-list-linear" class="text-secondary fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Invoice App</h6>
                                    <span class="fs-3 d-block text-muted">Get latest invoice</span>
                                  </div>
                                </a>
                                <a href="../../dark/app-contact2.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-success-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:bedside-table-2-linear" class="text-success fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Contact Application</h6>
                                    <span class="fs-3 d-block text-muted">2 Unsaved Contacts</span>
                                  </div>
                                </a>
                                <a href="../../dark/app-email.html" class="d-flex align-items-center pb-9 position-relative">
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
                                <a href="../../dark/page-user-profile.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-danger-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:cart-large-2-linear" class="text-danger fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">User Profile</h6>
                                    <span class="fs-3 d-block text-muted">learn more information</span>
                                  </div>
                                </a>
                                <a href="../../dark/app-calendar.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-primary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:calendar-linear" class="text-primary fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Calendar App</h6>
                                    <span class="fs-3 d-block text-muted">Get dates</span>
                                  </div>
                                </a>
                                <a href="../../dark/app-contact.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-secondary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:bedside-table-linear" class="text-secondary fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Contact List Table</h6>
                                    <span class="fs-3 d-block text-muted">Add new contact</span>
                                  </div>
                                </a>
                                <a href="../../dark/app-notes.html" class="d-flex align-items-center pb-9 position-relative">
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
                            <a class="text-dark d-flex align-items-center lh-1 fs-3 bg-hover-primary" href="../../dark/page-faq.html">
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
                            <a class="text-dark bg-hover-primary" href="../../dark/page-pricing.html">Pricing Page</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="../../dark/authentication-login.html">Authentication Design</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="../../dark/authentication-register.html">Register Now</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="../../dark/authentication-error.html">404 Error Page</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="../../dark/app-notes.html">Notes App</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="../../dark/page-user-profile.html">User Application</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="../../dark/page-account-settings.html">Account Settings</a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </li>


            </ul>

            <div class="d-block d-lg-none">
              <div class="brand-logo d-flex align-items-center justify-content-between">
                <a href="../../dark/index.html" class="text-nowrap logo-img d-flex align-items-center gap-2">
                  <b class="logo-icon">
                    <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                    <!-- Dark Logo icon -->
                    <img src="../../assets/images/logos/logo-light-icon.svg" alt="homepage" class="dark-logo" />
                    <!-- Light Logo icon -->
                    <img src="../../assets/images/logos/logo-light-icon.svg" alt="homepage" class="light-logo" />
                  </b>
                  <!--End Logo icon -->
                  <!-- Logo text -->
                  <span class="logo-text">
                    <!-- dark Logo text -->
                    <img src="../../assets/images/logos/logo-light-text.svg" alt="homepage" class="dark-logo ps-2" />
                    <!-- Light Logo text -->
                    <img src="../../assets/images/logos/logo-light-text.svg" class="light-logo ps-2" alt="homepage" />
                  </span>
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
                            <img src="../../assets/images/profile/user-2.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-3.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-4.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-5.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-6.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-7.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                  <!-- ------------------------------- -->
                  <!-- start language Dropdown -->
                  <!-- ------------------------------- -->

                  <li class="nav-item hover-dd dropdown nav-icon-hover-bg rounded-circle">
                    <a class="nav-link" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <img src="../../assets/images/svgs/icon-flag-en.svg" alt="" width="20px" height="20px" class="round-20" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                      <div class="message-body">
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 py-2 px-4 dropdown-item">
                          <div class="position-relative">
                            <img src="../../assets/images/svgs/icon-flag-en.svg" alt="" width="20px" height="20px" class="round-20" />
                          </div>
                          <p class="mb-0 fs-3">English</p>
                        </a>
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 py-2 px-4 dropdown-item">
                          <div class="position-relative">
                            <img src="../../assets/images/svgs/icon-flag-cn.svg" alt="" width="20px" height="20px" class="round-20" />
                          </div>
                          <p class="mb-0 fs-3">Chinese</p>
                        </a>
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 py-2 px-4 dropdown-item">
                          <div class="position-relative">
                            <img src="../../assets/images/svgs/icon-flag-fr.svg" alt="" width="20px" height="20px" class="round-20" />
                          </div>
                          <p class="mb-0 fs-3">French</p>
                        </a>
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 py-2 px-4 dropdown-item">
                          <div class="position-relative">
                            <img src="../../assets/images/svgs/icon-flag-sa.svg" alt="" width="20px" height="20px" class="round-20" />
                          </div>
                          <p class="mb-0 fs-3">Arabic</p>
                        </a>
                      </div>
                    </div>
                  </li>


                  <!-- ------------------------------- -->
                  <!-- end language Dropdown -->
                  <!-- ------------------------------- -->

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
                            <img src="../../assets/images/profile/user-2.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-3.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-4.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-5.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-6.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-7.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                  <!-- end notification Dropdown -->
                  <!-- ------------------------------- -->

                  <!-- ------------------------------- -->
                  <!-- start profile Dropdown -->
                  <!-- ------------------------------- -->
                  <li class="nav-item hover-dd dropdown">
                    <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <img src="../../assets/images/profile/user-2.jpg" alt="user" class="profile-pic rounded-circle round-30" />
                    </a>
                    <div class="dropdown-menu pt-0 content-dd overflow-hidden pt-0 dropdown-menu-end user-dd" aria-labelledby="drop2">
                      <div class="profile-dropdown position-relative" data-simplebar>
                        <div class=" py-3 border-bottom">
                          <div class="d-flex align-items-center px-3">
                            <img src="../../assets/images/profile/user-2.jpg" class="rounded-circle round-50" alt="" />
                            <div class="ms-3">
                              <h5 class="mb-1 fs-4"><?php echo $firstName . ' ' . $lastName; ?></h5>
                              <p class="mb-0 fs-2 d-flex align-items-center text-muted">
                                markrarn@wrappixel.com
                              </p>
                            </div>
                          </div>
                        </div>
                        <div class="message-body pb-3">
                          <div class="px-3 pt-3">
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="../../dark/page-user-profile.html" class=" d-flex  align-items-center ">
                                My Profile
                              </a>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="javascript:void(0)" class=" d-flex  align-items-center ">
                                My Projects
                              </a>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="../../dark/app-email.html" class=" d-flex  align-items-center ">
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
                              <a href="../../dark/page-account-settings.html" class=" d-flex  align-items-center  ">
                                Account Settings
                              </a>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="../../dark/authentication-login.html" class=" d-flex  align-items-center ">
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
                <a href="../../dark/index.html" class="text-nowrap logo-img d-block">
                  <b class="logo-icon">
                    <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                    <!-- Dark Logo icon -->
                    <img src="../../assets/images/logos/logo-icon.svg" alt="homepage">
                  </b>
                  <!--End Logo icon -->
                  <!-- Logo text -->
                  <span class="logo-text">
                    <!-- dark Logo text -->
                    <img src="../../assets/images/logos/logo-text.svg" alt="homepage" class="dark-logo ps-2">
                    <!-- Light Logo text -->
                    <img src="../../assets/images/logos/logo-light-text.svg" class="light-logo ps-2" alt="homepage">
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
                        <a href="../../dark/app-chat.html" class="d-flex align-items-center position-relative ">
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
                        <a href="../../dark/app-invoice.html" class="d-flex align-items-center position-relative">
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
                        <a href="../../dark/app-contact2.html" class="d-flex align-items-center position-relative">
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
                        <a href="../../dark/app-email.html" class="d-flex align-items-center position-relative">
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
                        <a href="../../dark/page-user-profile.html" class="d-flex align-items-center position-relative">
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
                        <a href="../../dark/app-calendar.html" class="d-flex align-items-center position-relative">
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
                        <a href="../../dark/app-contact.html" class="d-flex align-items-center position-relative">
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
                        <a href="../../dark/app-notes.html" class="d-flex align-items-center position-relative">
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
                          <a class="fs-3" href="../../dark/page-pricing.html">Pricing
                            Page</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="../../dark/authentication-login.html">Authentication Design</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="../../dark/authentication-register.html">Register Now</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="../../dark/authentication-error.html">404
                            Error Page</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="../../dark/app-notes.html">Notes App</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="../../dark/page-user-profile.html">User
                            Application</a>
                        </li>
                        <li class="sidebar-item py-2">
                          <a class="fs-3" href="../../dark/page-account-settings.html">Account Settings</a>
                        </li>
                      </ul>
                    </ul>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link px-1" href="../../dark/app-chat.html" aria-expanded="false">
                      <span class="d-flex">
                        <iconify-icon icon="solar:chat-unread-outline" class="fs-6"></iconify-icon>
                      </span>
                      <span class="hide-menu">Chat</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link px-1" href="../../dark/app-calendar.html" aria-expanded="false">
                      <span class="d-flex">
                        <iconify-icon icon="solar:calendar-minimalistic-outline" class="fs-6"></iconify-icon>
                      </span>
                      <span class="hide-menu">Calendar</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link px-1" href="../../dark/app-email.html" aria-expanded="false">
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
                  <a href="../../dark/index.html" class="text-nowrap logo-img d-flex align-items-center gap-2">
                    <b class="logo-icon">
                      <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                      <!-- Dark Logo icon -->
                      <img src="../../assets/images/logos/logo-light-icon.svg" alt="homepage" class="dark-logo" />
                      <!-- Light Logo icon -->
                      <img src="../../assets/images/logos/logo-light-icon.svg" alt="homepage" class="light-logo" />
                    </b>
                    <!--End Logo icon -->
                    <!-- Logo text -->
                    <span class="logo-text">
                      <!-- dark Logo text -->
                      <img src="../../assets/images/logos/logo-light-text.svg" alt="homepage" class="dark-logo ps-2" />
                      <!-- Light Logo text -->
                      <img src="../../assets/images/logos/logo-light-text.svg" class="light-logo ps-2" alt="homepage" />
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
                                <a href="../../dark/app-chat.html" class="d-flex align-items-center pb-9 position-relative ">
                                  <div class="bg-primary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:chat-line-linear" class="text-primary fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block ">
                                    <h6 class="mb-0 bg-hover-primary">Chat Application</h6>
                                    <span class="fs-3 d-block text-muted">New messages arrived</span>
                                  </div>
                                </a>
                                <a href="../../dark/app-invoice.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-secondary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:bill-list-linear" class="text-secondary fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Invoice App</h6>
                                    <span class="fs-3 d-block text-muted">Get latest invoice</span>
                                  </div>
                                </a>
                                <a href="../../dark/app-contact2.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-success-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:bedside-table-2-linear" class="text-success fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Contact Application</h6>
                                    <span class="fs-3 d-block text-muted">2 Unsaved Contacts</span>
                                  </div>
                                </a>
                                <a href="../../dark/app-email.html" class="d-flex align-items-center pb-9 position-relative">
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
                                <a href="../../dark/page-user-profile.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-danger-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:cart-large-2-linear" class="text-danger fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">User Profile</h6>
                                    <span class="fs-3 d-block text-muted">learn more information</span>
                                  </div>
                                </a>
                                <a href="../../dark/app-calendar.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-primary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:calendar-linear" class="text-primary fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Calendar App</h6>
                                    <span class="fs-3 d-block text-muted">Get dates</span>
                                  </div>
                                </a>
                                <a href="../../dark/app-contact.html" class="d-flex align-items-center pb-9 position-relative">
                                  <div class="bg-secondary-subtle rounded-circle round-40 me-3 p-6 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:bedside-table-linear" class="text-secondary fs-5"></iconify-icon>
                                  </div>
                                  <div class="d-inline-block">
                                    <h6 class="mb-0 bg-hover-primary">Contact List Table</h6>
                                    <span class="fs-3 d-block text-muted">Add new contact</span>
                                  </div>
                                </a>
                                <a href="../../dark/app-notes.html" class="d-flex align-items-center pb-9 position-relative">
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
                            <a class="text-dark d-flex align-items-center lh-1 fs-3 bg-hover-primary" href="../../dark/page-faq.html">
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
                            <a class="text-dark bg-hover-primary" href="../../dark/page-pricing.html">Pricing Page</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="../../dark/authentication-login.html">Authentication Design</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="../../dark/authentication-register.html">Register Now</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="../../dark/authentication-error.html">404 Error Page</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="../../dark/app-notes.html">Notes App</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="../../dark/page-user-profile.html">User Application</a>
                          </li>
                          <li class="mb-3">
                            <a class="text-dark bg-hover-primary" href="../../dark/page-account-settings.html">Account Settings</a>
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
                  <!-- ------------------------------- -->
                  <!-- start language Dropdown -->
                  <!-- ------------------------------- -->
                  <li class="nav-item hover-dd dropdown nav-icon-hover-bg rounded-circle">
                    <a class="nav-link" href="javascript:void(0)" id="drop2" aria-expanded="false">
                      <img src="../../assets/images/svgs/icon-flag-en.svg" alt="" width="20px" height="20px" class="round-20" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                      <div class="message-body">
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 py-2 px-4 dropdown-item">
                          <div class="position-relative">
                            <img src="../../assets/images/svgs/icon-flag-en.svg" alt="" width="20px" height="20px" class="round-20" />
                          </div>
                          <p class="mb-0 fs-3">English</p>
                        </a>
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 py-2 px-4 dropdown-item">
                          <div class="position-relative">
                            <img src="../../assets/images/svgs/icon-flag-cn.svg" alt="" width="20px" height="20px" class="round-20" />
                          </div>
                          <p class="mb-0 fs-3">Chinese</p>
                        </a>
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 py-2 px-4 dropdown-item">
                          <div class="position-relative">
                            <img src="../../assets/images/svgs/icon-flag-fr.svg" alt="" width="20px" height="20px" class="round-20" />
                          </div>
                          <p class="mb-0 fs-3">French</p>
                        </a>
                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 py-2 px-4 dropdown-item">
                          <div class="position-relative">
                            <img src="../../assets/images/svgs/icon-flag-sa.svg" alt="" width="20px" height="20px" class="round-20" />
                          </div>
                          <p class="mb-0 fs-3">Arabic</p>
                        </a>
                      </div>
                    </div>
                  </li>
                  <!-- ------------------------------- -->
                  <!-- end language Dropdown -->
                  <!-- ------------------------------- -->
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
                            <img src="../../assets/images/profile/user-2.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-3.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-4.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-5.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-6.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                            <img src="../../assets/images/profile/user-7.jpg" alt="user" class="rounded-circle w-100 round-40" />
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
                      <img src="../../assets/images/profile/user-2.jpg" alt="user" width="30" class="profile-pic rounded-circle" />
                    </a>
                    <div class="dropdown-menu pt-0 content-dd overflow-hidden pt-0 dropdown-menu-end user-dd" aria-labelledby="drop2">
                      <div class="profile-dropdown position-relative" data-simplebar>
                        <div class=" py-3 border-bottom">
                          <div class="d-flex align-items-center px-3">
                            <img src="../../assets/images/profile/user-2.jpg" class="rounded-circle round-50" alt="" />
                            <div class="ms-3">
                              <h5 class="mb-1 fs-4"><h5 class="mb-1 fs-4"><?php echo $firstName . ' ' . $lastName; ?></h5></h5>
                              <p class="mb-0 fs-2 d-flex align-items-center text-muted">
                                markrarn@wrappixel.com
                              </p>
                            </div>
                          </div>
                        </div>
                        <div class="message-body pb-3">
                          <div class="px-3 pt-3">
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="../../dark/page-user-profile.html" class=" d-flex  align-items-center ">
                                My Profile
                              </a>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="javascript:void(0)" class=" d-flex  align-items-center ">
                                My Projects
                              </a>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="../../dark/app-email.html" class=" d-flex  align-items-center ">
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
                              <a href="../../dark/page-account-settings.html" class=" d-flex  align-items-center  ">
                                Account Settings
                              </a>
                            </div>
                            <div class="h6 mb-0 dropdown-item py-8 px-3 rounded-2 link">
                              <a href="../../dark/authentication-login.html" class=" d-flex  align-items-center ">
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

      <aside class="left-sidebar with-horizontal">
        <!-- Sidebar scroll-->
        <div>
          <!-- Sidebar navigation-->
          <nav id="sidebarnavh" class="sidebar-nav scroll-sidebar container-fluid">
            <ul id="sidebarnav">
              <!-- ============================= -->
              <!-- Home -->
              <!-- ============================= -->
              <li class="nav-small-cap">
                <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon"></iconify-icon>
                <span class="hide-menu">Home</span>
              </li>
              <!-- =================== -->
              <!-- Dashboard -->
              <!-- =================== -->
              <li class="sidebar-item">
                <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                  <iconify-icon icon="solar:screencast-2-linear" class="aside-icon"></iconify-icon>
                  <span class="hide-menu">Dashboard</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                  <li class="sidebar-item">
                    <a href="../../dark/index.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Modern Dashboard</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/index2.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Awesome Dashboard</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/index3.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Classy Dashboard</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/index4.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Analytical Dashboard</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/index5.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Minimal Dashboard</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/index6.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">General Dashboard</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!-- ============================= -->
              <!-- Apps -->
              <!-- ============================= -->
              <li class="nav-small-cap">
                <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon"></iconify-icon>
                <span class="hide-menu">Apps</span>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link two-column has-arrow" href="javascript:void(0)" aria-expanded="false">
                  <iconify-icon icon="solar:archive-linear" class="aside-icon"></iconify-icon>
                  <span class="hide-menu">Apps</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                  <li class="sidebar-item">
                    <a href="../../dark/app-calendar.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Calendar</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/app-kanban.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Kanban</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/app-chat.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Chat</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a class="sidebar-link" href="../../dark/app-email.html" aria-expanded="false">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Email</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/app-contact.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Contact Table</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/app-contact2.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Contact List</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/app-notes.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Notes</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/app-invoice.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Invoice</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/page-user-profile.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">User Profile</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/blog-posts.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Posts</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/blog-detail.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Detail</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/eco-shop.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Shop</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/eco-shop-detail.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Shop Detail</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/eco-product-list.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">List</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/eco-checkout.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Checkout</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!-- ============================= -->
              <!-- PAGES -->
              <!-- ============================= -->
              <li class="nav-small-cap">
                <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon"></iconify-icon>
                <span class="hide-menu">PAGES</span>
              </li>
              <li class="sidebar-item">
                <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                  <iconify-icon icon="solar:programming-linear" class="aside-icon"></iconify-icon>
                  <span class="hide-menu">Pages</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                  <li class="sidebar-item">
                    <a href="../../dark/page-faq.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">FAQ</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/page-account-settings.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Account Setting</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/page-pricing.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Pricing</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/widgets-cards.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Card</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/widgets-banners.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Banner</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/widgets-charts.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Charts</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../landingpage/index.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Landing Page</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!-- ============================= -->
              <!-- UI -->
              <!-- ============================= -->
              <li class="nav-small-cap">
                <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon"></iconify-icon>
                <span class="hide-menu">UI</span>
              </li>
              <!-- =================== -->
              <!-- UI Elements -->
              <!-- =================== -->
              <li class="sidebar-item mega-dropdown">
                <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                  <iconify-icon icon="solar:text-underline-cross-linear" class="aside-icon"></iconify-icon>
                  <span class="hide-menu">UI</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                  <li class="sidebar-item">
                    <a href="../../dark/ui-accordian.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Accordian</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-badge.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Badge</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-buttons.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Buttons</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-dropdowns.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Dropdowns</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-modals.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Modals</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-tab.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Tab</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-tooltip-popover.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Tooltip & Popover</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-notification.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Alerts</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-progressbar.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Progressbar</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-pagination.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Pagination</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-typography.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Typography</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-bootstrap-ui.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Bootstrap UI</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-breadcrumb.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Breadcrumb</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-offcanvas.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Offcanvas</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-lists.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Lists</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-grid.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Grid</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-carousel.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Carousel</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-scrollspy.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Scrollspy</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-spinner.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Spinner</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/ui-link.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Link</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!-- ============================= -->
              <!-- Forms -->
              <!-- ============================= -->
              <li class="nav-small-cap">
                <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon"></iconify-icon>
                <span class="hide-menu">Forms</span>
              </li>
              <!-- =================== -->
              <!-- Forms -->
              <!-- =================== -->
              <li class="sidebar-item">
                <a class="sidebar-link two-column has-arrow" href="javascript:void(0)" aria-expanded="false">
                  <iconify-icon icon="solar:book-2-linear" class="aside-icon"></iconify-icon>
                  <span class="hide-menu">Forms</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                  <!-- form elements -->
                  <li class="sidebar-item">
                    <a href="../../dark/form-inputs.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Forms Input</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/form-input-groups.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Input Groups</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/form-input-grid.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Input Grid</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/form-checkbox-radio.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Checkbox & Radios</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/form-bootstrap-switch.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Bootstrap Switch</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/form-select2.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Select2</span>
                    </a>
                  </li>
                  <!-- form inputs -->
                  <li class="sidebar-item">
                    <a href="../../dark/form-basic.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Basic Form</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/form-vertical.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Form Vertical</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/form-horizontal.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Form Horizontal</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/form-actions.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Form Actions</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/form-row-separator.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Row Separator</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/form-bordered.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Form Bordered</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/form-detail.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Form Detail</span>
                    </a>
                  </li>
                  <!-- form wizard -->
                  <li class="sidebar-item">
                    <a href="../../dark/form-wizard.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Form Wizard</span>
                    </a>
                  </li>
                  <!-- Quill Editor -->
                  <li class="sidebar-item">
                    <a href="../../dark/form-editor-quill.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Quill Editor</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!-- ============================= -->
              <!-- Tables -->
              <!-- ============================= -->
              <li class="nav-small-cap">
                <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon"></iconify-icon>
                <span class="hide-menu">Tables</span>
              </li>
              <!-- =================== -->
              <!-- Bootstrap Table -->
              <!-- =================== -->
              <li class="sidebar-item">
                <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                  <iconify-icon icon="solar:sidebar-minimalistic-linear" class="aside-icon"></iconify-icon>
                  <span class="hide-menu">Tables</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                  <li class="sidebar-item">
                    <a href="../../dark/table-basic.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Basic Table</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/table-dark-basic.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Dark Basic Table</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/table-sizing.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Sizing Table</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/table-layout-coloured.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Coloured Table</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/table-datatable-basic.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Basic Initialisation</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/table-datatable-api.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">API</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/table-datatable-advanced.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Advanced Initialisation</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!-- ============================= -->
              <!-- Charts -->
              <!-- ============================= -->
              <li class="nav-small-cap">
                <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon"></iconify-icon>
                <span class="hide-menu">Charts</span>
              </li>
              <!-- =================== -->
              <!-- Apex Chart -->
              <!-- =================== -->
              <li class="sidebar-item">
                <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                  <iconify-icon icon="solar:pie-chart-3-linear" class="aside-icon"></iconify-icon>
                  <span class="hide-menu">Charts</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                  <li class="sidebar-item">
                    <a href="../../dark/chart-apex-line.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Line Chart</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/chart-apex-area.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Area Chart</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/chart-apex-bar.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Bar Chart</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/chart-apex-pie.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Pie Chart</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/chart-apex-radial.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Radial Chart</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/chart-apex-radar.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Radar Chart</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!-- ============================= -->
              <!-- Icons -->
              <!-- ============================= -->
              <li class="nav-small-cap">
                <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon"></iconify-icon>
                <span class="hide-menu">Icons</span>
              </li>
              <!-- =================== -->
              <!-- Tabler Icon -->
              <!-- =================== -->
              <li class="sidebar-item">
                <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                  <iconify-icon icon="solar:pie-chart-3-linear" class="aside-icon"></iconify-icon>
                  <span class="hide-menu">Icon</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                  <li class="sidebar-item">
                    <a href="../../dark/icon-tabler.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Tabler Icon</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="../../dark/icon-solar.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Solar Icon</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!-- multi level -->
              <li class="sidebar-item">
                <a class="sidebar-link has-arrow" href="javascript:void(0)" aria-expanded="false">
                  <iconify-icon icon="solar:layers-minimalistic-linear" class="aside-icon"></iconify-icon>
                  <span class="hide-menu">Multi DD</span>
                </a>
                <ul aria-expanded="false" class="collapse first-level">
                  <li class="sidebar-item">
                    <a href="../../docs/index.html" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Documentation</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="javascript:void(0)" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Page 1</span>
                    </a>
                  </li>
                  <li class="sidebar-item">
                    <a href="javascript:void(0)" class="sidebar-link has-arrow">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Page 2</span>
                    </a>
                    <ul aria-expanded="false" class="collapse second-level">
                      <li class="sidebar-item">
                        <a href="javascript:void(0)" class="sidebar-link">
                          <i class="ti ti-circle"></i>
                          <span class="hide-menu">Page 2.1</span>
                        </a>
                      </li>
                      <li class="sidebar-item">
                        <a href="javascript:void(0)" class="sidebar-link">
                          <i class="ti ti-circle"></i>
                          <span class="hide-menu">Page 2.2</span>
                        </a>
                      </li>
                      <li class="sidebar-item">
                        <a href="javascript:void(0)" class="sidebar-link">
                          <i class="ti ti-circle"></i>
                          <span class="hide-menu">Page 2.3</span>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="sidebar-item">
                    <a href="javascript:void(0)" class="sidebar-link">
                      <i class="ti ti-circle"></i>
                      <span class="hide-menu">Page 3</span>
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </nav>
          <!-- End Sidebar navigation -->
        </div>
        <!-- End Sidebar scroll-->
      </aside>

      <div class="body-wrapper">
        <div class="container-fluid">
          <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
            <div class="card-body px-0">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h4 class="font-weight-medium fs-14 mb-0">User Profile</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="">Home
                        </a>
                      </li>
                      <li class="breadcrumb-item text-muted" aria-current="page">User Profile</li>
                    </ol>
                  </nav>
                </div>
                <div>
                  <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
                    <div class="d-flex gap-2">
                      <div class="">
                        <small>This Month</small>
                        <h4 class="text-primary mb-0 ">$58,256</h4>
                      </div>
                      <div class="">
                        <div class="breadbar"></div>
                      </div>
                    </div>
                    <div class="d-flex gap-2">
                      <div class="">
                        <small>Last Month</small>
                        <h4 class="text-secondary mb-0 ">$58,256</h4>
                      </div>
                      <div class="">
                        <div class="breadbar2"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card overflow-hidden">
            <div class="card-body p-0">
              <img src="../../assets/images/backgrounds/profilebg.jpg" alt="materialpro-img" class="img-fluid">
              <div class="row align-items-center">
                <div class="col-lg-4 order-lg-1 order-2">
                  <div class="d-flex align-items-center justify-content-around m-4">
                    <div class="text-center">
                      <i class="ti ti-file-description fs-6 d-block mb-2"></i>
                      <h4 class="mb-0 fw-semibold lh-1">938</h4>
                      <p class="mb-0 ">Posts</p>
                    </div>
                    <div class="text-center">
                      <i class="ti ti-user-circle fs-6 d-block mb-2"></i>
                      <h4 class="mb-0 fw-semibold lh-1">3,586</h4>
                      <p class="mb-0 ">Followers</p>
                    </div>
                    <div class="text-center">
                      <i class="ti ti-user-check fs-6 d-block mb-2"></i>
                      <h4 class="mb-0 fw-semibold lh-1">2,659</h4>
                      <p class="mb-0 ">Following</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 mt-n3 order-lg-2 order-1">
                  <div class="mt-n5">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                      <div class="d-flex align-items-center justify-content-center round-110">
                        <div class="border border-4 border-white d-flex align-items-center justify-content-center rounded-circle overflow-hidden round-100">
                          <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="w-100 h-100">
                        </div>
                      </div>
                    </div>
                    <div class="text-center">
                      <h5 class="mb-0"><h5 class="mb-1 fs-4"><?php echo $firstName . ' ' . $lastName; ?></h5></h5>
                      <p class="mb-0">Designer</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 order-last">
                  <ul class="list-unstyled d-flex align-items-center justify-content-center justify-content-lg-end my-3 mx-4 pe-4 gap-3">
                    <li>
                      <a class="d-flex align-items-center justify-content-center btn btn-primary p-2 fs-4 rounded-circle" href="javascript:void(0)" width="30" height="30">
                        <i class="ti ti-brand-facebook"></i>
                      </a>
                    </li>
                    <li>
                      <a class="btn btn-secondary d-flex align-items-center justify-content-center p-2 fs-4 rounded-circle" href="javascript:void(0)">
                        <i class="ti ti-brand-dribbble"></i>
                      </a>
                    </li>
                    <li>
                      <a class="btn btn-danger d-flex align-items-center justify-content-center p-2 fs-4 rounded-circle" href="javascript:void(0)">
                        <i class="ti ti-brand-youtube"></i>
                      </a>
                    </li>
                    <li>
                      <button class="btn btn-primary text-nowrap">Add To Story</button>
                    </li>
                  </ul>
                </div>
              </div>
              <ul class="nav nav-pills user-profile-tab justify-content-end mt-2 bg-primary-subtle rounded-2 rounded-top-0" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active hstack gap-2 rounded-0 fs-12 py-6" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="true">
                    <i class="ti ti-user-circle fs-5"></i>
                    <span class="d-none d-md-block">Profile</span>
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link hstack gap-2 rounded-0 fs-12 py-6" id="pills-followers-tab" data-bs-toggle="pill" data-bs-target="#pills-followers" type="button" role="tab" aria-controls="pills-followers" aria-selected="false">
                    <i class="ti ti-heart fs-5"></i>
                    <span class="d-none d-md-block">Followers</span>
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link hstack gap-2 rounded-0 fs-12 py-6" id="pills-friends-tab" data-bs-toggle="pill" data-bs-target="#pills-friends" type="button" role="tab" aria-controls="pills-friends" aria-selected="false">
                    <i class="ti ti-user-circle fs-5"></i>
                    <span class="d-none d-md-block">Friends</span>
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link hstack gap-2 rounded-0 fs-12 py-6" id="pills-gallery-tab" data-bs-toggle="pill" data-bs-target="#pills-gallery" type="button" role="tab" aria-controls="pills-gallery" aria-selected="false">
                    <i class="ti ti-photo-plus fs-5"></i>
                    <span class="d-none d-md-block">Gallery</span>
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">
              <div class="row">
                <div class="col-lg-4">
                  <div class="card shadow-none border">
                    <div class="card-body">
                      <h4 class="mb-3">Introduction</h4>
                      <p class="card-subtitle">Hello, I am <h5 class="mb-1 fs-4"><?php echo $firstName . ' ' . $lastName; ?></h5>. I love making websites and graphics. Lorem
                        ipsum dolor sit amet,
                        consectetur adipiscing elit.</p>
                      <div class="vstack gap-3 mt-4">
                        <div class="hstack gap-6">
                          <i class="ti ti-briefcase text-dark fs-6"></i>
                          <h6 class=" mb-0">Sir, P P Institute Of Science</h6>
                        </div>
                        <div class="hstack gap-6">
                          <i class="ti ti-mail text-dark fs-6"></i>
                          <h6 class=" mb-0">markrarn@wrappixel.com</h6>
                        </div>
                        <div class="hstack gap-6">
                          <i class="ti ti-device-desktop text-dark fs-6"></i>
                          <h6 class=" mb-0">www.xyz.com</h6>
                        </div>
                        <div class="hstack gap-6">
                          <i class="ti ti-map-pin text-dark fs-6"></i>
                          <h6 class=" mb-0">Newyork, USA - 100001</h6>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="card shadow-none border">
                    <div class="card-body">
                      <h4 class="fw-semibold mb-3">Photos</h4>
                      <div class="row">
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-9">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-9">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-9">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-4.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-9">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-5.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-9">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-6.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-9">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-7.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-6">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-6">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-6">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-8">
                  <div class="card shadow-none border">
                    <div class="card-body">
                      <div class="form-floating mb-3">
                        <textarea class="form-control h-140" placeholder="Leave a comment here" id="floatingTextarea2"></textarea>
                        <label for="floatingTextarea2">Share your thoughts</label>
                      </div>
                      <div class="d-flex align-items-center gap-6 flex-wrap">
                        <a class="d-flex align-items-center round-32 justify-content-center btn btn-primary rounded-circle p-0" href="javascript:void(0)">
                          <i class="ti ti-photo"></i>
                        </a>
                        <a href="javascript:void(0)" class="text-dark link-primary pe-3 py-2">Photo / Video</a>

                        <a class="d-flex align-items-center round-32 justify-content-center btn btn-secondary rounded-circle p-0" href="javascript:void(0)">
                          <i class="ti ti-notebook"></i>
                        </a>
                        <a href="javascript:void(0)" class="text-dark link-secondary pe-3 py-2">Article</a>


                        <button class="btn btn-primary ms-auto">Post</button>
                      </div>
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-body border-bottom">
                      <div class="d-flex align-items-center gap-6 flex-wrap">
                        <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                        <h6 class="mb-0"><h5 class="mb-1 fs-4"><?php echo $firstName . ' ' . $lastName; ?></h5></h6>
                        <span class="fs-2 hstack gap-2">
                          <span class="round-10 text-bg-light rounded-circle d-inline-block"></span> 15 min
                          ago
                        </span>
                      </div>
                      <p class="text-dark my-3">
                        Nu kek vuzkibsu mooruno ejepogojo uzjon gag fa ezik disan he nah. Wij wo pevhij tumbug rohsa
                        ahpi ujisapse lo vap labkez eddu suk.
                      </p>
                      <img src="../../assets/images/products/s1.jpg" alt="materialpro-img" height="360" class="rounded-4 w-100 object-fit-cover">
                      <div class="d-flex align-items-center my-3">
                        <div class="d-flex align-items-center gap-2">
                          <a class="round-32 rounded-circle btn btn-primary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                            <i class="ti ti-thumb-up"></i>
                          </a>
                          <span class="text-dark fw-semibold">67</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 ms-4">
                          <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Comment">
                            <i class="ti ti-message-2"></i>
                          </a>
                          <span class="text-dark fw-semibold">2</span>
                        </div>
                        <a class="text-dark ms-auto d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Share">
                          <i class="ti ti-share"></i>
                        </a>
                      </div>
                      <div class="position-relative">
                        <div class="p-4 rounded-2 text-bg-light mb-3">
                          <div class="d-flex align-items-center gap-6 flex-wrap">
                            <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                            <h6 class="mb-0">Deran Mac</h6>
                            <span class="fs-2">
                              <span class="p-1 text-bg-muted rounded-circle d-inline-block"></span> 8 min ago
                            </span>
                          </div>
                          <p class="my-3">Lufo zizrap iwofapsuk pusar luc jodawbac zi op uvezojroj duwage vuhzoc ja
                            vawdud le furhez siva
                            fikavu ineloh. Zot afokoge si mucuve hoikpaf adzuk zileuda falohfek zoije fuka udune lub
                            annajor gazo
                            conis sufur gu.
                          </p>
                          <div class="d-flex align-items-center">
                            <div class="d-flex align-items-center gap-2">
                              <a class="round-32 rounded-circle btn btn-primary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                                <i class="ti ti-thumb-up"></i>
                              </a>
                              <span class="text-dark fw-semibold">55</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 ms-4">
                              <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Reply">
                                <i class="ti ti-arrow-back-up"></i>
                              </a>
                              <span class="text-dark fw-semibold">0</span>
                            </div>
                          </div>
                        </div>
                        <div class="p-4 rounded-2 text-bg-light mb-3">
                          <div class="d-flex align-items-center gap-6 flex-wrap">
                            <img src="../../assets/images/profile/user-4.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                            <h6 class="mb-0">Daisy Wilson</h6>
                            <span class="fs-2">
                              <span class="p-1 text-bg-muted rounded-circle d-inline-block"></span> 5
                              min
                              ago
                            </span>
                          </div>
                          <p class="my-3">
                            Zumankeg ba lah lew ipep tino tugjekoj hosih fazjid wotmila durmuri buf hi sigapolu joit
                            ebmi joge vo.
                            Horemo vogo hat na ejednu sarta afaamraz zi cunidce peroido suvan podene igneve.
                          </p>
                          <div class="d-flex align-items-center">
                            <div class="d-flex align-items-center gap-2">
                              <a class="round-32 rounded-circle btn btn-primary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                                <i class="ti ti-thumb-up"></i>
                              </a>
                              <span class="text-dark fw-semibold">68</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 ms-4">
                              <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Reply">
                                <i class="ti ti-arrow-back-up"></i>
                              </a>
                              <span class="text-dark fw-semibold">1</span>
                            </div>
                          </div>
                        </div>
                        <div class="p-4 rounded-2 text-bg-light ms-7">
                          <div class="d-flex align-items-center gap-6 flex-wrap">
                            <img src="../../assets/images/profile/user-5.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                            <h6 class="mb-0">Carry minati</h6>
                            <span class="fs-2">
                              <span class="p-1 text-bg-muted rounded-circle d-inline-block"></span>
                              just
                              now
                            </span>
                          </div>
                          <p class="my-3">
                            Olte ni somvukab ugura ovaobeco hakgoc miha peztajo tawosu udbacas kismakin hi. Dej
                            zetfamu cevufi sokbid bud mun soimeuha pokahram vehurpar keecris pepab voegmud
                            zundafhef hej pe.
                          </p>
                        </div>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-6 flex-wrap p-3 flex-lg-nowrap">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                      <input type="text" class="form-control py-8" id="exampleInputtext" aria-describedby="textHelp" placeholder="Comment">
                      <button class="btn btn-primary">Comment</button>
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-body border-bottom">
                      <div class="d-flex align-items-center gap-6 flex-wrap">
                        <img src="../../assets/images/profile/user-5.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                        <h6 class="mb-0">Carry Minati</h6>
                        <span class="fs-2">
                          <span class="p-1 text-bg-light rounded-circle d-inline-block"></span>
                          now
                        </span>
                      </div>
                      <p class="text-dark my-3">
                        Pucnus taw set babu lasufot lawdebuw nem ig bopnub notavfe pe ranlu dijsan liwfekaj lo az. Dom
                        giat gu
                        sehiosi bikelu lo eb uwrerej bih woppoawi wijdiola iknem hih suzega gojmev kir rigoj.
                      </p>
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center gap-2">
                          <a class="round-32 rounded-circle btn btn-primary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                            <i class="ti ti-thumb-up"></i>
                          </a>
                          <span class="text-dark fw-semibold">1</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 ms-4">
                          <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Comment">
                            <i class="ti ti-message-2"></i>
                          </a>
                          <span class="text-dark fw-semibold">0</span>
                        </div>
                        <a class="text-dark ms-auto d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Share">
                          <i class="ti ti-share"></i>
                        </a>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-6 flex-wrap p-3 flex-lg-nowrap">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                      <input type="text" class="form-control py-8" id="exampleInputtext1" aria-describedby="textHelp" placeholder="Comment">
                      <button class="btn btn-primary">Comment</button>
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-body border-bottom">
                      <div class="d-flex align-items-center gap-6 flex-wrap">
                        <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                        <h6 class="mb-0">Genelia Desouza</h6>
                        <span class="fs-2">
                          <span class="p-1 text-bg-light rounded-circle d-inline-block"></span> 15 min
                          ago
                        </span>
                      </div>
                      <p class="text-dark my-3">
                        Faco kiswuoti mucurvi juokomo fobgi aze huweik zazjofefa kuujer talmoc li niczot lohejbo vozev
                        zi huto. Ju
                        tupma uwujate bevolkoh hob munuap lirec zak ja li hotlanu pigtunu.
                      </p>
                      <div class="row">
                        <div class="col-sm-6">
                          <img src="../../assets/images/products/s2.jpg" alt="materialpro-img" class="img-fluid rounded-4 mb-3 mb-sm-0">
                        </div>
                        <div class="col-sm-6">
                          <img src="../../assets/images/products/s4.jpg" alt="materialpro-img" class="img-fluid rounded-4">
                        </div>
                      </div>
                      <div class="d-flex align-items-center my-3">
                        <div class="d-flex align-items-center gap-2">
                          <a class="text-dark d-flex align-items-center justify-content-center bg-light p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                            <i class="ti ti-thumb-up"></i>
                          </a>
                          <span class="text-dark fw-semibold">320</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 ms-4">
                          <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Comment">
                            <i class="ti ti-message-2"></i>
                          </a>
                          <span class="text-dark fw-semibold">1</span>
                        </div>
                        <a class="text-dark ms-auto d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Share">
                          <i class="ti ti-share"></i>
                        </a>
                      </div>
                      <div class="p-4 rounded-2 text-bg-light">
                        <div class="d-flex align-items-center gap-6 flex-wrap">
                          <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                          <h6 class="mb-0">Ritesh Deshmukh</h6>
                          <span class="fs-2">
                            <span class="p-1 text-bg-muted rounded-circle d-inline-block"></span> 15
                            min
                            ago
                          </span>
                        </div>
                        <p class="my-3">
                          Hintib cojno riv ze heb cipcep fij wo tufinpu bephekdab infule pajnaji. Jiran goetimip muovo
                          go en
                          gaga zeljomim hozlu lezuvi ehkapod dec bifoom hag dootasac odo luvgit ti ella.
                        </p>
                        <div class="d-flex align-items-center">
                          <div class="d-flex align-items-center gap-2">
                            <a class="round-32 rounded-circle btn btn-primary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                              <i class="ti ti-thumb-up"></i>
                            </a>
                            <span class="text-dark fw-semibold">65</span>
                          </div>
                          <div class="d-flex align-items-center gap-2 ms-4">
                            <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Reply">
                              <i class="ti ti-arrow-back-up"></i>
                            </a>
                            <span class="text-dark fw-semibold">0</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-6 flex-wrap p-3 flex-lg-nowrap">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                      <input type="text" class="form-control py-8" id="exampleInputtext2" aria-describedby="textHelp" placeholder="Comment">
                      <button class="btn btn-primary">Comment</button>
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-body border-bottom">
                      <div class="d-flex align-items-center gap-6 flex-wrap">
                        <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                        <h6 class="mb-0"><h5 class="mb-1 fs-4"><?php echo $firstName . ' ' . $lastName; ?></h5></h6>
                        <span class="fs-2">
                          <span class="p-1 text-bg-light rounded-circle d-inline-block"></span> 15 min
                          ago
                        </span>
                      </div>
                      <p class="text-dark my-3">
                        Faco kiswuoti mucurvi juokomo fobgi aze huweik zazjofefa kuujer talmoc li niczot lohejbo vozev
                        zi huto. Ju
                        tupma uwujate bevolkoh hob munuap lirec zak ja li hotlanu pigtunu.
                      </p>
                      <iframe class="rounded-4 border border-2 mb-3 h-300" src="https://www.youtube.com/embed/d1-FRj20WBE" frameborder="0" width="100%"></iframe>
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center gap-2">
                          <a class="round-32 rounded-circle btn btn-primary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                            <i class="ti ti-thumb-up"></i>
                          </a>
                          <span class="text-dark fw-semibold">129</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 ms-4">
                          <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Comment">
                            <i class="ti ti-message-2"></i>
                          </a>
                          <span class="text-dark fw-semibold">0</span>
                        </div>
                        <a class="text-dark ms-auto d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Share">
                          <i class="ti ti-share"></i>
                        </a>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-6 flex-wrap p-3 flex-lg-nowrap">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                      <input type="text" class="form-control py-8" id="exampleInputtext3" aria-describedby="textHelp" placeholder="Comment">
                      <button class="btn btn-primary">Comment</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="pills-followers" role="tabpanel" aria-labelledby="pills-followers-tab" tabindex="0">
              <div class="d-sm-flex align-items-center justify-content-between mt-3 mb-4">
                <h3 class="mb-3 mb-sm-0 fw-semibold d-flex align-items-center">Followers <span class="badge text-bg-secondary fs-2 rounded-4 py-1 px-2 ms-2">20</span>
                </h3>
                <form class="position-relative">
                  <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Followers">
                  <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y text-dark ms-3"></i>
                </form>
              </div>
              <div class="row">
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Betty Adams</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Sint Maarten
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Virginia Wong</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Tunisia
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Birdie</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Algeria
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-4.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Steven</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Malaysia
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-5.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Hannah</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Grenada
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-6.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Effie Gross</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Azerbaijan
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-7.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Barton</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>French Souther
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Carolyn</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Nauru
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-9.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Elizabeth</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Djibouti
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-10.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Jon Cohen</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>United States
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-12.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Hernandez</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Equatorial Guinea
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Willie</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Solomon Islands
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Harvey</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Uruguay
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-4.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Alice George</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Madagascar
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-12.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Simpson</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Bahrain
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-6.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Francis Barber</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Colombia
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-7.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Christian</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Maldives
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Laura Nelson</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>St. Helena
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-9.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Blanche</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>South Africa
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-10.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Adam</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Suriname
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="pills-friends" role="tabpanel" aria-labelledby="pills-friends-tab" tabindex="0">
              <div class="d-sm-flex align-items-center justify-content-between mt-3 mb-4">
                <h3 class="mb-3 mb-sm-0 fw-semibold d-flex align-items-center">Friends <span class="badge text-bg-secondary fs-2 rounded-4 py-1 px-2 ms-2">20</span>
                </h3>
                <form class="position-relative">
                  <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Friends">
                  <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y text-dark ms-3"></i>
                </form>
              </div>
              <div class="row">
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Betty Adams</h5>
                      <span class="text-dark fs-2">Medical Secretary</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Inez Lyons</h5>
                      <span class="text-dark fs-2">Medical Technician</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Lydia Bryan</h5>
                      <span class="text-dark fs-2">Preschool Teacher</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-4.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Carolyn Bryant</h5>
                      <span class="text-dark fs-2">Legal Secretary</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Paul Benson</h5>
                      <span class="text-dark fs-2">Safety Engineer</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-6.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Robert Francis</h5>
                      <span class="text-dark fs-2">Nursing Administrator</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-7.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Billy Rogers</h5>
                      <span class="text-dark fs-2">Legal Secretary</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Rosetta Brewer</h5>
                      <span class="text-dark fs-2">Comptroller</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-9.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Patrick Knight</h5>
                      <span class="text-dark fs-2">Retail Store Manager</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-10.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Francis Sutton</h5>
                      <span class="text-dark fs-2">Astronomer</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-11.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Bernice Henry</h5>
                      <span class="text-dark fs-2">Security Consultant</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Estella Garcia</h5>
                      <span class="text-dark fs-2">Lead Software Test Engineer</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Norman Moran</h5>
                      <span class="text-dark fs-2">Engineer Technician</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-5.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Jessie Matthews</h5>
                      <span class="text-dark fs-2">Lead Software Engineer</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-5.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Elijah Perez</h5>
                      <span class="text-dark fs-2">Special Education Teacher</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-6.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Robert Martin</h5>
                      <span class="text-dark fs-2">Transportation Manager</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-7.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Elva Wong</h5>
                      <span class="text-dark fs-2">Logistics Manager</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Edith Taylor</h5>
                      <span class="text-dark fs-2">Union Representative</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-9.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Violet Jackson</h5>
                      <span class="text-dark fs-2">Agricultural Inspector</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-10.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Phoebe Owens</h5>
                      <span class="text-dark fs-2">Safety Engineer</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="pills-gallery" role="tabpanel" aria-labelledby="pills-gallery-tab" tabindex="0">
              <div class="d-sm-flex align-items-center justify-content-between mt-3 mb-4">
                <h3 class="mb-3 mb-sm-0 fw-semibold d-flex align-items-center">Gallery <span class="badge text-bg-secondary fs-2 rounded-4 py-1 px-2 ms-2">12</span>
                </h3>
                <form class="position-relative">
                  <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Friends">
                  <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y text-dark ms-3"></i>
                </form>
              </div>
              <div class="row">
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s1.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Isuava wakceajo fe.jpg</h6>
                          <span class="text-dark fs-2">Wed, Dec 14, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Isuava wakceajo fe.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s2.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Ip docmowe vemremrif.jpg</h6>
                          <span class="text-dark fs-2">Wed, Dec 14, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Ip docmowe vemremrif.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s3.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Duan cosudos utaku.jpg</h6>
                          <span class="text-dark fs-2">Wed, Dec 14, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Duan cosudos utaku.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s4.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Fu netbuv oggu.jpg</h6>
                          <span class="text-dark fs-2">Wed, Dec 14, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Fu netbuv oggu.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s5.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Di sekog do.jpg</h6>
                          <span class="text-dark fs-2">Wed, Dec 14, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Di sekog do.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s6.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Lo jogu camhiisi.jpg</h6>
                          <span class="text-dark fs-2">Thu, Dec 15, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Lo jogu camhiisi.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s7.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Orewac huosazud robuf.jpg</h6>
                          <span class="text-dark fs-2">Fri, Dec 16, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Orewac huosazud robuf.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s8.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Nira biolaizo tuzi.jpg</h6>
                          <span class="text-dark fs-2">Sat, Dec 17, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Nira biolaizo tuzi.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s9.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Peri metu ejvu.jpg</h6>
                          <span class="text-dark fs-2">Sun, Dec 18, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Peri metu ejvu.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s10.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Vurnohot tajraje isusufuj.jpg</h6>
                          <span class="text-dark fs-2">Mon, Dec 19, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Vurnohot tajraje isusufuj.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s11.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Juc oz ma.jpg</h6>
                          <span class="text-dark fs-2">Tue, Dec 20, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Juc oz ma.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s12.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Povipvez marjelliz zuuva.jpg</h6>
                          <span class="text-dark fs-2">Wed, Dec 21, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Povipvez marjelliz zuuva.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script>
  function handleColorTheme(e) {
    $("html").attr("data-color-theme", e);
    $(e).prop("checked", !0);
  }
</script>
      <button class="btn btn-primary p-3 rounded-circle d-flex align-items-center justify-content-center customizer-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
        <i class="icon ti ti-settings fs-7 text-white"></i>
      </button>

      <div class="offcanvas customizer offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
          <h4 class="offcanvas-title fw-semibold" id="offcanvasExampleLabel">
            Settings
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body h-n80" data-simplebar>
          <h6 class="fw-semibold fs-4 mb-2">Theme</h6>

          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check light-layout " name="theme-layout" id="light-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="light-layout"> <iconify-icon icon="solar:sun-2-outline" class="icon fs-7 me-2"></iconify-icon>Light</label>
            <input type="radio" class="btn-check dark-layout" name="theme-layout" id="dark-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="dark-layout"><iconify-icon icon="solar:moon-outline" class="icon fs-7 me-2"></iconify-icon>Dark</label>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Theme Direction</h6>
          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check" name="direction-l" id="ltr-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="ltr-layout"><iconify-icon icon="solar:align-left-linear" class="icon fs-7 me-2"></iconify-icon>LTR</label>

            <input type="radio" class="btn-check" name="direction-l" id="rtl-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="rtl-layout">
              <iconify-icon icon="solar:align-right-linear" class="icon fs-7 me-2"></iconify-icon>RTL
            </label>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Theme Colors</h6>

          <div class="d-flex flex-row flex-wrap gap-3 customizer-box color-pallete" role="group">
            <input type="radio" class="btn-check" name="color-theme-layout" id="Blue_Theme" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center rounded" onclick="handleColorTheme('Blue_Theme')" for="Blue_Theme" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="BLUE_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-1">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="Aqua_Theme" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center rounded" onclick="handleColorTheme('Aqua_Theme')" for="Aqua_Theme" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="AQUA_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-2">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="Purple_Theme" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center rounded" onclick="handleColorTheme('Purple_Theme')" for="Purple_Theme" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="PURPLE_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-3">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="green-theme-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center rounded" onclick="handleColorTheme('Green_Theme')" for="green-theme-layout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="GREEN_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-4">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="cyan-theme-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center rounded" onclick="handleColorTheme('Cyan_Theme')" for="cyan-theme-layout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="CYAN_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-5">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="orange-theme-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center rounded" onclick="handleColorTheme('Orange_Theme')" for="orange-theme-layout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="ORANGE_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-6">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Layout Type</h6>
          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <div>
              <input type="radio" class="btn-check" name="page-layout" id="vertical-layout" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary rounded" for="vertical-layout">
                <iconify-icon icon="solar:slider-vertical-minimalistic-linear" class="icon fs-7 me-2"></iconify-icon>Vertical
              </label>
            </div>
            <div>
              <input type="radio" class="btn-check" name="page-layout" id="horizontal-layout" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary rounded" for="horizontal-layout">
                <iconify-icon icon="solar:slider-minimalistic-horizontal-outline" class="icon fs-7 me-2"></iconify-icon>
                Horizontal
              </label>
            </div>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Container Option</h6>

          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check" name="layout" id="boxed-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="boxed-layout">
              <iconify-icon icon="solar:cardholder-linear" class="icon fs-7 me-2"></iconify-icon>
              Boxed
            </label>

            <input type="radio" class="btn-check" name="layout" id="full-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="full-layout">
              <iconify-icon icon="solar:scanner-linear" class="icon fs-7 me-2"></iconify-icon> Full
            </label>
          </div>

          <h6 class="fw-semibold fs-4 mb-2 mt-5">Sidebar Type</h6>
          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <a href="javascript:void(0)" class="fullsidebar">
              <input type="radio" class="btn-check" name="sidebar-type" id="full-sidebar" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary rounded" for="full-sidebar"><iconify-icon icon="solar:sidebar-minimalistic-outline" class="icon fs-7 me-2"></iconify-icon> Full</label>
            </a>
            <div>
              <input type="radio" class="btn-check " name="sidebar-type" id="mini-sidebar" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary rounded" for="mini-sidebar">
                <iconify-icon icon="solar:siderbar-outline" class="icon fs-7 me-2"></iconify-icon>Collapse
              </label>
            </div>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Card With</h6>

          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check" name="card-layout" id="card-with-border" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="card-with-border"><iconify-icon icon="solar:library-broken" class="icon fs-7 me-2"></iconify-icon>Border</label>

            <input type="radio" class="btn-check" name="card-layout" id="card-without-border" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="card-without-border">
              <iconify-icon icon="solar:box-outline " class="icon fs-7 me-2"></iconify-icon>Shadow
            </label>
          </div>
        </div>
      </div>

      <script>
  function handleColorTheme(e) {
    document.documentElement.setAttribute("data-color-theme", e);
  }
</script>
    </div>

    <!--  Search Bar -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-1">
          <div class="modal-header border-bottom">
            <input type="search" class="form-control fs-2" placeholder="Search here" id="search" />
            <a href="javascript:void(0)" data-bs-dismiss="modal" class="lh-1">
              <i class="ti ti-x fs-5 ms-3"></i>
            </a>
          </div>
          <div class="modal-body message-body" data-simplebar="">
            <h5 class="mb-0 fs-5 p-1">Quick Page Links</h5>
            <ul class="list mb-0 py-2">
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Modern</span>
                  <span class="fs-2 text-muted d-block">/dashboards/dashboard1</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Dashboard</span>
                  <span class="fs-2 text-muted d-block">/dashboards/dashboard2</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Contacts</span>
                  <span class="fs-2 text-muted d-block">/apps/contacts</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Posts</span>
                  <span class="fs-2 text-muted d-block">/apps/blog/posts</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Detail</span>
                  <span class="fs-2 text-muted d-block">/apps/blog/detail/streaming-video-way-before-it-was-cool-go-dark-tomorrow</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Shop</span>
                  <span class="fs-2 text-muted d-block">/apps/ecommerce/shop</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Modern</span>
                  <span class="fs-2 text-muted d-block">/dashboards/dashboard1</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Dashboard</span>
                  <span class="fs-2 text-muted d-block">/dashboards/dashboard2</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Contacts</span>
                  <span class="fs-2 text-muted d-block">/apps/contacts</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Posts</span>
                  <span class="fs-2 text-muted d-block">/apps/blog/posts</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Detail</span>
                  <span class="fs-2 text-muted d-block">/apps/blog/detail/streaming-video-way-before-it-was-cool-go-dark-tomorrow</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Shop</span>
                  <span class="fs-2 text-muted d-block">/apps/ecommerce/shop</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>


  </div>
  <div class="dark-transparent sidebartoggler"></div>
  <!-- Import Js Files -->
  <script src="../../assets/js/breadcrumb/breadcrumbChart.js"></script>
  <script src="../../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/libs/simplebar/dist/simplebar.min.js"></script>
  <script src="../../assets/js/theme/app.dark.init.js"></script>
  <script src="../../assets/js/theme/theme.js"></script>
  <script src="../../assets/js/theme/app.min.js"></script>
  <script src="../../assets/js/theme/sidebarmenu.js"></script>
  <script src="../../assets/js/theme/feather.min.js"></script>

  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>