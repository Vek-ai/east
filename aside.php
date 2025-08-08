  <aside class="left-sidebar with-vertical">
      <div><!-- ---------------------------------- -->
        <!-- Start Vertical Layout Sidebar -->
        <!-- ---------------------------------- -->
        <!-- Sidebar scroll-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
          <!-----------Profile------------------>
          <div class="user-profile position-relative" style="background: url(assets/images/backgrounds/user-info.jpg) no-repeat;">
            <!-- User profile image -->
            <div class="profile-img">
              <img src="assets/images/profile/user-1.jpg" alt="user" class="w-100 rounded-circle overflow-hidden" />
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
        <!-- Sales -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Sales</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="/cashier2/">
            <iconify-icon icon="solar:pen-new-round-linear" class="aside-icon"></iconify-icon>
            <span class="hide-menu">New Sale</span>
          </a>
        </li>
        
        <?php
        $sql = "SELECT url, menu_icon, menu_name 
                FROM pages 
                WHERE visibility = 1 
                  AND menu_category = 'Sales'
                  AND category_id = '1'
                ORDER BY id ASC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $page_url = !empty($row['url']) 
                            ? '?page=' . htmlspecialchars($row['url']) 
                            : 'javascript:void(0);';

                echo '<li class="sidebar-item">
                        <a class="sidebar-link" href="' . $page_url . '">
                            <iconify-icon icon="' . htmlspecialchars($row['menu_icon']) . '" class="aside-icon"></iconify-icon>
                            <span class="hide-menu">' . htmlspecialchars($row['menu_name']) . '</span>
                        </a>
                      </li>';
            }
        }
        ?>



        <!-- ---------------------------------- -->
        <!-- Products -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Products</span>
        </li>
        <?php
        $sql = "SELECT url, menu_icon, menu_name 
                FROM pages 
                WHERE visibility = 1 
                  AND menu_category = 'Products'
                  AND category_id = '1'
                ORDER BY id ASC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $page_url = !empty($row['url']) 
                            ? '?page=' . htmlspecialchars($row['url']) 
                            : 'javascript:void(0);';

                echo '<li class="sidebar-item">
                        <a class="sidebar-link" href="' . $page_url . '">
                            <iconify-icon icon="' . htmlspecialchars($row['menu_icon']) . '" class="aside-icon"></iconify-icon>
                            <span class="hide-menu">' . htmlspecialchars($row['menu_name']) . '</span>
                        </a>
                      </li>';
            }
        }
        ?>

        <!-- Products -->
        <!-- ---------------------------------- -->
        
       <!-- ---------------------------------- -->
        <!-- Customers -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:add-square-outline" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Customers</span>
        </li>
        <?php
        $sql = "SELECT url, menu_icon, menu_name 
                FROM pages 
                WHERE visibility = 1 
                  AND menu_category = 'Customers'
                  AND category_id = '1'
                ORDER BY id ASC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $page_url = !empty($row['url']) 
                            ? '?page=' . htmlspecialchars($row['url']) 
                            : 'javascript:void(0);';

                echo '<li class="sidebar-item">
                        <a class="sidebar-link" href="' . $page_url . '">
                            <iconify-icon icon="' . htmlspecialchars($row['menu_icon']) . '" class="aside-icon"></iconify-icon>
                            <span class="hide-menu">' . htmlspecialchars($row['menu_name']) . '</span>
                        </a>
                      </li>';
            }
        }
        ?>

        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">EKM Tools</span>
        </li>
        <?php
        $sql = "SELECT url, menu_icon, menu_name 
                FROM pages 
                WHERE visibility = 1 
                  AND menu_category = 'EKM Tools'
                  AND category_id = '1'
                ORDER BY id ASC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $url = trim($row['url']);
        
                if (!empty($url)) {
                    if (preg_match('/^https?:\/\//i', $url)) {
                        $page_url = htmlspecialchars($url);
                        $target   = ' target="_blank"';
                    } else {
                        $page_url = '?page=' . htmlspecialchars($url);
                        $target   = '';
                    }
                } else {
                    $page_url = 'javascript:void(0);';
                    $target   = '';
                }

                echo '<li class="sidebar-item">
                        <a class="sidebar-link" href="' . $page_url . '">
                            <iconify-icon icon="' . htmlspecialchars($row['menu_icon']) . '" class="aside-icon"></iconify-icon>
                            <span class="hide-menu">' . htmlspecialchars($row['menu_name']) . '</span>
                        </a>
                      </li>';
            }
        }
        ?>

        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Supplier</span>
        </li>
        <?php
        $sql = "SELECT url, menu_icon, menu_name 
                FROM pages 
                WHERE visibility = 1 
                  AND menu_category = 'Supplier'
                  AND category_id = '1'
                ORDER BY id ASC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $page_url = !empty($row['url']) 
                            ? '?page=' . htmlspecialchars($row['url']) 
                            : 'javascript:void(0);';

                echo '<li class="sidebar-item">
                        <a class="sidebar-link" href="' . $page_url . '">
                            <iconify-icon icon="' . htmlspecialchars($row['menu_icon']) . '" class="aside-icon"></iconify-icon>
                            <span class="hide-menu">' . htmlspecialchars($row['menu_name']) . '</span>
                        </a>
                      </li>';
            }
        }
        ?>

        <!-- ---------------------------------- -->
        <!-- Reports -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:presentation-graph-outline" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Reports</span>
        </li>
        <?php
        $sql = "SELECT url, menu_icon, menu_name 
                FROM pages 
                WHERE visibility = 1 
                  AND menu_category = 'Reports'
                  AND category_id = '1'
                ORDER BY id ASC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $page_url = !empty($row['url']) 
                            ? '?page=' . htmlspecialchars($row['url']) 
                            : 'javascript:void(0);';

                echo '<li class="sidebar-item">
                        <a class="sidebar-link" href="' . $page_url . '">
                            <iconify-icon icon="' . htmlspecialchars($row['menu_icon']) . '" class="aside-icon"></iconify-icon>
                            <span class="hide-menu">' . htmlspecialchars($row['menu_name']) . '</span>
                        </a>
                      </li>';
            }
        }
        ?>

        <!-- ---------------------------------- -->
        <!-- Employees -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Employees</span>
        </li>
        <?php
        $sql = "SELECT url, menu_icon, menu_name 
                FROM pages 
                WHERE visibility = 1 
                  AND menu_category = 'Employees'
                  AND category_id = '1'
                ORDER BY id ASC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $page_url = !empty($row['url']) 
                            ? '?page=' . htmlspecialchars($row['url']) 
                            : 'javascript:void(0);';

                echo '<li class="sidebar-item">
                        <a class="sidebar-link" href="' . $page_url . '">
                            <iconify-icon icon="' . htmlspecialchars($row['menu_icon']) . '" class="aside-icon"></iconify-icon>
                            <span class="hide-menu">' . htmlspecialchars($row['menu_name']) . '</span>
                        </a>
                      </li>';
            }
        }
        ?>
        <!-- Products -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Warehouse</span>
        </li>
        <?php
        $sql = "SELECT url, menu_icon, menu_name 
                FROM pages 
                WHERE visibility = 1 
                  AND menu_category = 'Warehouse'
                  AND category_id = '1'
                ORDER BY id ASC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $page_url = !empty($row['url']) 
                            ? '?page=' . htmlspecialchars($row['url']) 
                            : 'javascript:void(0);';

                echo '<li class="sidebar-item">
                        <a class="sidebar-link" href="' . $page_url . '">
                            <iconify-icon icon="' . htmlspecialchars($row['menu_icon']) . '" class="aside-icon"></iconify-icon>
                            <span class="hide-menu">' . htmlspecialchars($row['menu_name']) . '</span>
                        </a>
                      </li>';
            }
        }
        ?>

        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Products Properties</span>
        </li>
        <?php
        $sql = "SELECT url, menu_icon, menu_name 
                FROM pages 
                WHERE visibility = 1 
                  AND menu_category = 'Products Properties'
                  AND category_id = '1'
                ORDER BY id ASC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $page_url = !empty($row['url']) 
                            ? '?page=' . htmlspecialchars($row['url']) 
                            : 'javascript:void(0);';

                echo '<li class="sidebar-item">
                        <a class="sidebar-link" href="' . $page_url . '">
                            <iconify-icon icon="' . htmlspecialchars($row['menu_icon']) . '" class="aside-icon"></iconify-icon>
                            <span class="hide-menu">' . htmlspecialchars($row['menu_name']) . '</span>
                        </a>
                      </li>';
            }
        }
        ?>
    
        <!-- ---------------------------------- -->
        <!-- Settings -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Settings</span>
        </li>
        <?php
        $sql = "SELECT url, menu_icon, menu_name 
                FROM pages 
                WHERE visibility = 1 
                  AND menu_category = 'Settings'
                  AND category_id = '1'
                ORDER BY id ASC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $page_url = !empty($row['url']) 
                            ? '?page=' . htmlspecialchars($row['url']) 
                            : 'javascript:void(0);';

                echo '<li class="sidebar-item">
                        <a class="sidebar-link" href="' . $page_url . '">
                            <iconify-icon icon="' . htmlspecialchars($row['menu_icon']) . '" class="aside-icon"></iconify-icon>
                            <span class="hide-menu">' . htmlspecialchars($row['menu_name']) . '</span>
                        </a>
                      </li>';
            }
        }
        ?>

        <!-- ---------------------------------- -->
        <!-- Help & Support -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Help & Support</span>
        </li>
        <?php
        $sql = "SELECT url, menu_icon, menu_name 
                FROM pages 
                WHERE visibility = 1 
                  AND menu_category = 'Help & Support'
                  AND category_id = '1'
                ORDER BY id ASC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $page_url = !empty($row['url']) 
                            ? '?page=' . htmlspecialchars($row['url']) 
                            : 'javascript:void(0);';

                echo '<li class="sidebar-item">
                        <a class="sidebar-link" href="' . $page_url . '">
                            <iconify-icon icon="' . htmlspecialchars($row['menu_icon']) . '" class="aside-icon"></iconify-icon>
                            <span class="hide-menu">' . htmlspecialchars($row['menu_name']) . '</span>
                        </a>
                      </li>';
            }
        }
        ?>
      </ul>
        </nav>

        <!-- End Sidebar scroll-->
      </div>
    </aside>