  <aside class="left-sidebar with-vertical">
      <div><!-- ---------------------------------- -->
        <!-- Start Vertical Layout Sidebar -->
        <!-- ---------------------------------- -->
        <!-- Sidebar scroll-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
          <!-----------Profile------------------>
          <div class="user-profile position-relative" style="background: url(../../assets/images/backgrounds/user-info.jpg) no-repeat;">
            <!-- User profile image -->
            <div class="profile-img">
              <img src="../../assets/images/profile/user-1.jpg" alt="user" class="w-100 rounded-circle overflow-hidden" />
            </div>
            <!-- User profile text-->
            <div class="profile-text hide-menu pt-1 dropdown">
              <a href="javascript:void(0)" class="dropdown-toggle u-dropdown w-100 text-white
                  d-block
                  position-relative
                " id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false"><?= $customer_details['customer_first_name'] .' ' .$customer_details['customer_last_name'] ?></a>
              <div class="dropdown-menu animated flipInY" aria-labelledby="dropdownMenuLink">
               
               
               
                <div class="dropdown-divider"></div>
                <a class="dropdown-item d-flex gap-2" href="../dark/page-account-settings.html">
                  <i data-feather="settings" class="feather-sm text-warning "></i>
                  Account Setting
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item d-flex gap-2" href="#">
                  <i data-feather="log-out" class="feather-sm text-danger "></i>
                  Logout
                </a>
                <div class="dropdown-divider"></div>
                <div class="px-3 py-2">
                  <a href="pages/customer-profile.php" class="btn d-block w-100 btn-info rounded-pill">View Profile</a>
                </div>
              </div>
            </div>
          </div>
          <!-----------Profile End------------------>

          <ul id="sidebarnav">
        <!-- ---------------------------------- -->
        <!-- Dashboard -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Dashboard</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=customer-profile">
            <i class="fa fa-user-circle aside-icon"></i>
            <span class="hide-menu">Profile</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="index.php">
            <i class="fa fa-tachometer-alt aside-icon"></i>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=product">
            <i class="fa fa-list-alt aside-icon"></i>
            <span class="hide-menu">View Products</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=browse">
            <i class="fa fa-shopping-cart aside-icon"></i>
            <span class="hide-menu">Order/Estimate</span>
          </a>
        </li>

      </ul>
        </nav>

        <!-- End Sidebar scroll-->
      </div>
    </aside>