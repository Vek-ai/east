<?php
require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    
    if ($action === 'fetch_table') {
        $access_profile_id = mysqli_real_escape_string($conn, $_POST['access_profile_id'] ?? '');

        $query = "SELECT * FROM pages";
        $result = mysqli_query($conn, $query);

        $allAccess = [];
        if (!empty($access_profile_id)) {
            $perm_query = "
                SELECT page_id, permission 
                FROM access_profile_pages 
                WHERE access_profile_id = '$access_profile_id'
            ";
            $perm_result = mysqli_query($conn, $perm_query);
            if ($perm_result && mysqli_num_rows($perm_result) > 0) {
                while ($row_perm = mysqli_fetch_assoc($perm_result)) {
                    $allAccess[$row_perm['page_id']] = $row_perm['permission'];
                }
            }
        }

        $data = [];
        $totalPages = 0;
        $withAccess = 0;
        $viewOnly = 0;
        $canEdit = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            $totalPages++;

            $page_id = $row['id'];
            $page_name = $row['page_name'];
            $url = $row['url'];
            $category_id = $row['category_id'];
            $category_name = getPageCategoryName($category_id);

            $permission = $allAccess[$page_id] ?? null;
            $hasAccess = !empty($permission);

            if ($hasAccess) {
                $withAccess++;
                if ($permission === 'view') {
                    $viewOnly++;
                } elseif ($permission === 'edit') {
                    $canEdit++;
                }
            }

            $accessChecked = $hasAccess ? 'checked' : '';
            $access_html = "
                <div class='form-check form-check-radio'>
                    <input 
                        class='form-check-input access-toggle' 
                        type='checkbox' 
                        data-page-id='$page_id' 
                        id='access_$page_id'
                        $accessChecked
                    >
                    <label class='form-check-label' for='access_$page_id'></label>
                </div>";

            $viewChecked = ($permission === 'view') ? 'checked' : '';
            $editChecked = ($permission === 'edit') ? 'checked' : '';
            $perm_html = "
                <div class='form-check form-check-inline'>
                    <input class='form-check-input permission-radio' type='radio' name='permission_$page_id' value='view' $viewChecked " . (!$hasAccess ? "disabled" : "") . ">
                    <label class='form-check-label'>View</label>
                </div>
                <div class='form-check form-check-inline'>
                    <input class='form-check-input permission-radio' type='radio' name='permission_$page_id' value='edit' $editChecked " . (!$hasAccess ? "disabled" : "") . ">
                    <label class='form-check-label'>Edit</label>
                </div>";

            $status = 'No Access';
            if ($hasAccess) {
                $status = $permission === 'edit' ? 'View and Edit' : 'View Only';
            }

            $data[] = [
                'page' => "<div><strong>{$page_name}</strong><br><small class='text-muted'>{$url}</small></div>",
                'category' => "<span class='badge bg-primary'>{$category_name}</span>",
                'access' => $access_html,
                'permission' => $perm_html,
                'status' => "<span class='fw-semibold'>{$status}</span>"
            ];
        }

        echo json_encode([
            'data' => $data,
            'counts' => [
                'total' => $totalPages,
                'with_access' => $withAccess,
                'view_only' => $viewOnly,
                'can_edit' => $canEdit
            ]
        ]);
        exit;
    }


    if ($action === 'save_changes') {
    $access_profile_id = intval($_POST['access_profile_id'] ?? 0);
    $updates = json_decode($_POST['updates'] ?? '[]', true);

    foreach ($updates as $update) {
        $page_id = intval($update['page_id']);
        $has_access = !empty($update['has_access']) ? 1 : 0;
        $permission = $has_access ? mysqli_real_escape_string($conn, $update['permission']) : null;

        $check_sql = "
            SELECT 1 
            FROM access_profile_pages 
            WHERE access_profile_id = $access_profile_id 
              AND page_id = $page_id
        ";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            if ($has_access) {
                $update_sql = "
                    UPDATE access_profile_pages 
                    SET permission = " . ($permission !== null ? "'$permission'" : "NULL") . " 
                    WHERE access_profile_id = $access_profile_id 
                      AND page_id = $page_id
                ";
                mysqli_query($conn, $update_sql);
            } else {
                $delete_sql = "
                    DELETE FROM access_profile_pages 
                    WHERE access_profile_id = $access_profile_id 
                      AND page_id = $page_id
                ";
                mysqli_query($conn, $delete_sql);
            }
        } else {
            if ($has_access) {
                $insert_sql = "
                    INSERT INTO access_profile_pages (access_profile_id, page_id, permission) 
                    VALUES ($access_profile_id, $page_id, " . ($permission !== null ? "'$permission'" : "NULL") . ")
                ";
                mysqli_query($conn, $insert_sql);
            }
        }
    }

    echo 'success';
    exit;
}
    mysqli_close($conn);
}
?>
