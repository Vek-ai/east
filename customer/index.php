<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect_url");
    exit();
}
include_once '../includes/dbconn.php';
include_once '../includes/functions.php';

$customer_id = $_SESSION['customer_id'];
$customer_details = getCustomerDetails($customer_id);


$cartItems = getCartDataByCustomerId($customer_id);

$totalQuantity = 0;
foreach ($cartItems as $item) {
    $totalQuantity += (int)$item['quantity_cart'];
}

$grandTotal = 0.00;
foreach ($cartItems as $item) {
    $grandTotal += (float)$item['unit_price'] * (int)$item['quantity_cart'];
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
  <link rel="stylesheet" href="../assets/libs/select2/dist/css/select2.min.css">
  <link rel="stylesheet" href="../assets/libs/owl.carousel/dist/assets/owl.carousel.min.css">
  <link href="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone.css" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/smoothness/jquery-ui.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
  

  <!-- Core Css -->
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="stylesheet" href="css/customer.css" />

  <title>Customers - East Kentucky Metal</title>

</head>

<body>
  <!-- Preloader -->
  <div class="preloader">
    <img src="../assets/images/logos/logo-icon.svg" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <?php include "aside.php"?>
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
                  <li class="nav-item hover-dd dropdown nav-icon-hover-bg rounded-circle d-lg-block">
                    <a class="nav-link nav-icon-hover waves-effect waves-dark" href="javascript:void(0)" id="view_cart" aria-expanded="false">
                      <iconify-icon icon="ic:round-shopping-cart" class="cart-icon"></iconify-icon>
                      <div class="cart-number">
                        <span id="cartQtyMobile" class="cart-quantity"><?= $totalQuantity; ?></span>
                      </div>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up content-dd p-0" 
                        aria-labelledby="drop2"
                        style="max-width: 100%; width: 100%; max-height: 50vh; overflow-y: auto;">
                      
                      <!-- Header -->
                      <div class="py-3 px-3 bg-primary">
                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between">
                          <div class="fs-6 fw-medium text-white mb-2 mb-sm-0">Cart Contents</div>
                          <div class="d-flex align-items-center justify-content-between">
                            <button type="button" class="btn btn-sm me-2" id="clear_cart" style="background-color: #dc3545; color: white;">
                              <i class="fa fa-trash fs-5 me-1"></i> Clear Cart
                            </button>
                            <span id="cartTotalMobile" class="fs-6 fw-medium text-white cartTotal text-end">
                              <?php
                                echo "$" . number_format($grandTotal, 2);
                              ?>
                            </span>
                          </div>
                        </div>
                      </div>

                      <!-- Column headers -->
                      <div class="row bg-light text-secondary py-2 mx-0 text-center">
                        <div class="col-1 col-3-sm">Image</div>
                        <div class="col-7 col-4-sm">Description</div>
                        <div class="col-2 col-2-sm">Color</div>
                        <div class="col-1">Qty</div>
                        <div class="col-1 col-2-sm"></div>
                      </div>

                      <!-- Cart body -->
                      <div class="cart-body px-2" data-simplebar></div>

                    </div>

                  </li>
                </ul>
                <ul class="navbar-nav gap-2 flex-row ms-auto align-items-center justify-content-center">
                  <li class="nav-item hover-dd dropdown nav-icon-hover-bg rounded-circle d-none d-lg-block">
                    <a class="nav-link nav-icon-hover waves-effect waves-dark" href="javascript:void(0)" id="view_cart" aria-expanded="false">
                      <iconify-icon icon="ic:round-shopping-cart" class="cart-icon"></iconify-icon>
                      <div class="cart-number">
                        <span id="cartQty" class="cart-quantity"><?= $totalQuantity; ?></span>
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
                          <span id="cartTotal" class="mb-0 fs-6 fw-medium text-white cartTotal">
                            <?php
                              echo "$" . number_format($grandTotal, 2);
                            ?>
                          </span>
                        </div>
                      </div>

                      <div class="row bg-light text-secondary py-2 mx-0 text-center">
                        <div class="col-1 col-3-sm">Image</div>
                        <div class="col-7 col-4-sm">Description</div>
                        <div class="col-2 col-2-sm">Color</div>
                        <div class="col-1">Qty</div>
                        <div class="col-1 col-2-sm"></div>
                      </div>
                      
                      <div class="cart-body" data-simplebar></div>
                    </div>
                  </li>

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
                        <a href="pages/customer-profile.php" class="d-flex align-items-center position-relative">
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
                          <a class="fs-3" href="pages/customer-profile.php">User
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
              
            </ul>


            <a class="navbar-toggler nav-icon-hover p-0 border-0 text-white" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
              <span class="p-2">
                <i class="ti ti-dots fs-7"></i>
              </span>
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
              <div class="d-flex align-items-center justify-content-between">
                <ul class="navbar-nav gap-2 flex-row ms-auto align-items-center justify-content-center">
                 
                  
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
            if (empty($_REQUEST['page'])) {include 'pages/customer-dash.php';}
            if ($_REQUEST['page'] == "customer-profile") {include 'pages/customer-profile.php';}
            if ($_REQUEST['page'] == "product") {include 'pages/product.php';}
            if ($_REQUEST['page'] == "estimate") {include 'pages/estimate.php';}
            if ($_REQUEST['page'] == "order") {include 'pages/order.php';}
            if ($_REQUEST['page'] == "browse") {include 'pages/cashier.php';}
            if ($_REQUEST['page'] == "messages") {include 'pages/messages.php';}
            if ($_REQUEST['page'] == "job_details") {include 'pages/job_details.php';}
            if ($_REQUEST['page'] == "statement_of_account") {include 'pages/statement_of_account.php';}
          ?>
        </div>
      </div>
      <?php 
          include 'cart/cart.php';
        ?>
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
                              <div class="col-1 col-3-sm text-center">
                                  <span class="d-flex justify-content-center align-items-center bg-primary-subtle rounded-circle text-primary" 
                                      style="width: 50px; height: 50px;">
                                      <img src="${item.img_src}" alt="Item Image" style="width: 100%; height: 100%; object-fit: cover;">
                                  </span>
                              </div>
                              <div class="col-7 col-4-sm">
                                  <h6 class="mb-1 text-wrap">${item.item_name}</h6>
                              </div>
                              <div class="col-2">
                                  <span class="rounded-circle d-inline-block" style="background-color: ${item.color_hex}; width: 20px; height: 20px;"></span>
                              </div>
                              <div class="col-1">
                                  <h6 class="mb-1">${item.quantity}</h6>
                              </div>
                              <div class="col-1 col-2-sm">
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
  });
  </script>
</body>

</html>