<aside class="left-sidebar with-vertical">
   <div>
      <!-- ---------------------------------- -->
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
                  " id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">Markarn Doe</a>
               <div class="dropdown-menu animated flipInY" aria-labelledby="dropdownMenuLink">
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item d-flex gap-2" href="../dark/page-account-settings.html">
                  <i data-feather="settings" class="feather-sm text-warning "></i>
                  Account Setting
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item d-flex gap-2" href="logout.php">
                  <i data-feather="log-out" class="feather-sm text-danger "></i>
                  Logout
                  </a>
                  <div class="dropdown-divider"></div>
                  <div class="px-3 py-2">
                     <a href="../dark/page-user-profile.html" class="btn d-block w-100 btn-info rounded-pill">View Profile</a>
                  </div>
               </div>
            </div>
         </div>
         <!-----------Profile End------------------>
         <ul id="sidebarnav">
            <!-- ---------------------------------- -->
            <!-- Cashier -->
            <!-- ---------------------------------- -->
            <li class="nav-small-cap">
               <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
               <span class="hide-menu">Work Orders</span>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link d-flex align-items-center gap-2" href="?page=">
                  <iconify-icon icon="mdi:clipboard-text-outline" class="fs-7 aside-icon"></iconify-icon>
                  <span class="hide-menu">Work Orders</span>
               </a>
            </li>

            <li class="sidebar-item">
               <a class="sidebar-link d-flex align-items-center gap-2" href="?page=inventory">
                  <iconify-icon icon="mdi:warehouse" class="fs-7 aside-icon"></iconify-icon>
                  <span class="hide-menu">Inventory</span>
               </a>
            </li>

            <li class="sidebar-item">
               <a class="sidebar-link d-flex align-items-center gap-2" href="?page=messages">
                  <iconify-icon icon="mdi:email-outline" class="fs-7 aside-icon"></iconify-icon>
                  <span class="hide-menu">Messages</span>
               </a>
            </li>

         </ul>
      </nav>
      <!-- End Sidebar scroll-->
   </div>
</aside>