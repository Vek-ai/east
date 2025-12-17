<?php
$staff_id = intval($_SESSION['userid']);
$allowed_categories = [
                      'Sales', 
                      'EKM Tools', 
                      'Products', 
                      'Customers', 
                      'Reports', 
                      'Supplier', 
                      'Employees', 
                      'Warehouse', 
                      'Products Properties', 
                      'Coils',
                      'Settings',
                      'Help & Support',
                    ];

$profileSql = "SELECT access_profile_id FROM staff WHERE staff_id = $staff_id";
$profileRes = mysqli_query($conn, $profileSql);
$access_profile_id = 0;
if ($profileRes && mysqli_num_rows($profileRes) > 0) {
    $access_profile_id = intval(mysqli_fetch_assoc($profileRes)['access_profile_id']);
}

$profilePages = [];
if ($access_profile_id > 0) {
    $ppSql = "SELECT page_id FROM access_profile_pages WHERE access_profile_id = $access_profile_id";
    $ppRes = mysqli_query($conn, $ppSql);
    if ($ppRes && mysqli_num_rows($ppRes) > 0) {
        while ($row = mysqli_fetch_assoc($ppRes)) {
            $profilePages[$row['page_id']] = true;
        }
    }
}

$userPages = [];
$upaSql = "SELECT page_id FROM user_page_access WHERE staff_id = $staff_id";
$upaRes = mysqli_query($conn, $upaSql);
if ($upaRes && mysqli_num_rows($upaRes) > 0) {
    while ($row = mysqli_fetch_assoc($upaRes)) {
        $userPages[$row['page_id']] = true;
    }
}

$pageIds = array_keys($profilePages + $userPages);

$menu_items = [];
if (!empty($pageIds)) {
    $sql = "
        SELECT id, url, menu_icon, menu_name, menu_category, sort_order
        FROM pages
        WHERE visibility = 1
          AND id IN (" . implode(",", array_map('intval', $pageIds)) . ")
          AND menu_category IN ('" . implode("','", $allowed_categories) . "')
          AND category_id = '1'
        ORDER BY 
            CASE 
                WHEN sort_order = 0 THEN 999999
                ELSE sort_order
            END ASC,
            id ASC
    ";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $menu_items[$row['menu_category']][] = $row;
        }
    }
}
?>
<aside class="left-sidebar with-vertical">
    <div>
        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
            <div class="user-profile position-relative" style="background: url(assets/images/backgrounds/user-info.jpg) no-repeat;">
                <div class="profile-img">
                    <img src="assets/images/profile/user-1.jpg" alt="user" class="w-100 rounded-circle overflow-hidden" />
                </div>
                <div class="profile-text hide-menu pt-1 dropdown">
                    <a href="javascript:void(0)" class="dropdown-toggle u-dropdown w-100 text-white d-block position-relative" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">Markarn Doe</a>
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
            <ul id="sidebarnav">
                <?php
                foreach ($allowed_categories as $category) {
                    if (!empty($menu_items[$category])) {
                        echo '<li class="nav-small-cap"><span class="hide-menu">' . htmlspecialchars($category) . '</span></li>';
                        foreach ($menu_items[$category] as $page) {
                            $url    = trim($page['url']);
                            $href   = '';
                            $target = '';
                            $extra  = '';

                            if (strpos($url, '#') === 0) {
                                $href  = '#';
                                $extra = ' data-bs-toggle="modal" data-bs-target="' . htmlspecialchars($url) . '"';
                            } elseif ($url === '') {
                                $href = 'javascript:void(0)';
                            } elseif (preg_match('/^https?:\/\//i', $url)) {
                                $href = $url;
                                $target = ' target="_blank" rel="noopener noreferrer"';
                            } elseif (strpos($url, '/') === 0) {
                                $href = $url;
                                $target = ' target="_blank" rel="noopener noreferrer"';
                            } else {
                                $href = '?page=' . $url;
                            }
                            echo '<li class="sidebar-item">
                                    <a class="sidebar-link nav_' . htmlspecialchars(ltrim($url, '#')) . '"
                                    href="' . htmlspecialchars($href) . '"' . $target . $extra . '>
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
    </div>
</aside>
