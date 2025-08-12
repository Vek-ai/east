<?php
$staff_id = intval($_SESSION['userid']);

$allowed_categories = ['Sales', 'Products', 'Customers', 'Reports', 'Inventory', 'Settings'];

$sql = "SELECT p.url, p.menu_icon, p.menu_name, p.menu_category
        FROM pages AS p
        INNER JOIN user_page_access AS upa 
            ON p.id = upa.page_id
        WHERE p.visibility = 1
          AND upa.staff_id = $staff_id
          AND p.menu_category IN ('" . implode("','", $allowed_categories) . "')
        ORDER BY p.menu_category, p.id ASC";

$result = mysqli_query($conn, $sql);

$menu_items = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $menu_items[$row['menu_category']][] = $row;
    }
}
?>
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
        <?php
        foreach ($allowed_categories as $category) {
            if (!empty($menu_items[$category])) {
                echo '<li class="nav-small-cap"><span class="hide-menu">' . htmlspecialchars($category) . '</span></li>';
                
                foreach ($menu_items[$category] as $page) {
                    $url = trim($page['url']);

                    if ($url === '') {
                        $href = 'javascript:void(0)';
                        $target = '';
                    }
                    elseif (preg_match('/^https?:\/\//i', $url)) {
                        $href = $url;
                        $target = ' target="_blank" rel="noopener noreferrer"';
                    }
                    elseif (strpos($url, '/') === 0) {
                        $href = $url;
                        $target = ' target="_blank" rel="noopener noreferrer"';
                    }
                    else {
                        $href = $url;
                        $target = '';
                    }

                    echo '<li class="sidebar-item">
                            <a class="sidebar-link" href="' . htmlspecialchars($href) . '"' . $target . '>
                                <iconify-icon icon="' . htmlspecialchars($page['menu_icon']) . '" class="aside-icon"></iconify-icon>
                                <span class="hide-menu">' . htmlspecialchars($page['menu_name']) . '</span>
                            </a>
                          </li>';
                }
            }
        }

        ?>
      </ul>
        </nav>

        <!-- End Sidebar scroll-->
      </div>
    </aside>