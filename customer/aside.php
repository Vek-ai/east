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
            <iconify-icon icon="solar:user-circle-line-duotone" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Profile</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="index.php">
            <iconify-icon icon="solar:home-linear" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=product">
            <iconify-icon icon="solar:list-check-line-duotone" class="aside-icon"></iconify-icon>
            <span class="hide-menu">View Products</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=messages">
            <iconify-icon icon="solar:letter-line-duotone" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Messages</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=browse">
            <iconify-icon icon="solar:cart-large-line-duotone" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Order/Estimate</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="#">
            <iconify-icon icon="solar:fire-line-duotone" class="aside-icon"></iconify-icon>
            <span class="hide-menu">On Sale</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="#">
            <iconify-icon icon="solar:star-line-duotone" class="aside-icon"></iconify-icon>
            <span class="hide-menu">EKM Points</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="#">
            <iconify-icon icon="solar:buildings-line-duotone" class="aside-icon"></iconify-icon>
            <span class="hide-menu text-wrap">Request a Building Quote</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="#">
            <iconify-icon icon="solar:book-line-duotone" class="aside-icon"></iconify-icon>
            <span class="hide-menu">View Product Guides</span>
          </a>
        </li>

        <li class="sidebar-item">
          <a class="sidebar-link" href="#">
            <iconify-icon icon="solar:folder-line-duotone" class="aside-icon"></iconify-icon>
            <span class="hide-menu">My Projects</span>
          </a>
        </li>

        <li class="nav-item dropdown sidebar-item">
          <a class="nav-link dropdown-toggle sidebar-link" href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <iconify-icon icon="solar:settings-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Settings</span>
          </a>
          <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
            <li>
              <a class="dropdown-item sidebar-link sublink d-flex align-items-center" href="#">
                <iconify-icon icon="solar:user-outline" class="me-2"></iconify-icon>
                Account Settings
              </a>
            </li>
            <li>
              <a class="dropdown-item sidebar-link sublink d-flex align-items-center" href="#">
                <iconify-icon icon="solar:question-circle-line-duotone" class="me-2"></iconify-icon>
                FAQ's
              </a>
            </li>
            <li>
              <a class="dropdown-item sidebar-link sublink d-flex align-items-center" href="#">
                <iconify-icon icon="solar:chat-line-outline" class="me-2"></iconify-icon>
                Contact Support
              </a>
            </li>
          </ul>
        </li>


      </ul>
        </nav>

        <!-- End Sidebar scroll-->
      </div>
    </aside>