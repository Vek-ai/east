<?php
require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);

        $checkDuplicate = "SELECT * FROM page_categories WHERE category_name = '$category_name' AND id != '$id'";
        $duplicateResult = mysqli_query($conn, $checkDuplicate);

        if (mysqli_num_rows($duplicateResult) > 0) {
            echo "duplicate_category";
            exit;
        }

        $checkQuery = "SELECT * FROM page_categories WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE page_categories SET category_name = '$category_name', description = '$description' WHERE id = '$id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating category: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO page_categories (category_name, description) VALUES ('$category_name', '$description')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding category: " . mysqli_error($conn);
            }
        }
    }


    if ($action == 'fetch_modal_content') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM pages WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        $row = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_array($result) : [];

        ?>
        <div class="row pt-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" id="form_category_id" name="category_id">
                        <option value="" hidden>Select Category</option>
                        <?php
                        $catQuery = "SELECT * FROM page_categories ORDER BY category_name ASC";
                        $catResult = mysqli_query($conn, $catQuery);
                        if ($catResult && mysqli_num_rows($catResult) > 0) {
                            while ($catRow = mysqli_fetch_assoc($catResult)) {
                                $selected = ($catRow['id'] == $row['category_id']) ? 'selected' : '';
                                echo "<option value='{$catRow['id']}' $selected>{$catRow['category_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div id="page_details" class="row pt-3 <?= empty($row['category_id']) ? 'd-none' : '' ?>">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Page Name</label>
                    <input type="text" id="page_name" name="page_name" placeholder="Page Name" class="form-control" value="<?= htmlspecialchars($row['page_name'] ?? '') ?>"/>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">File Name</label>
                    <input type="text" id="file_name" name="file_name" placeholder="File Name" class="form-control" value="<?= htmlspecialchars($row['file_name'] ?? '') ?>"/>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">URL</label>
                    <input type="text" id="url" name="url" placeholder="URL" class="form-control" value="<?= htmlspecialchars($row['url'] ?? '') ?>"/>
                </div>
            </div>
        </div>

        <input type="hidden" id="id" name="id" class="form-control" value="<?= $id ?>"/>
        <?php
    }
    
    if ($action === 'fetch_table') {
        $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id'] ?? '');

        $query = "SELECT * FROM pages";
        $result = mysqli_query($conn, $query);

        $allAccess = getUserAccess($staff_id);

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
        $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id'] ?? '');
        $updates = json_decode($_POST['updates'] ?? '[]', true);

        foreach ($updates as $update) {
            $page_id = mysqli_real_escape_string($conn, $update['page_id']);
            $has_access = $update['has_access'] ? 1 : 0;
            $permission = $has_access ? mysqli_real_escape_string($conn, $update['permission']) : null;

            $check_sql = "SELECT * FROM user_page_access WHERE staff_id = '$staff_id' AND page_id = '$page_id'";
            $check_result = mysqli_query($conn, $check_sql);

            if ($check_result && mysqli_num_rows($check_result) > 0) {
                if ($has_access) {
                    $update_sql = "UPDATE user_page_access SET permission = '$permission' WHERE staff_id = '$staff_id' AND page_id = '$page_id'";
                    mysqli_query($conn, $update_sql);
                } else {
                    $delete_sql = "DELETE FROM user_page_access WHERE staff_id = '$staff_id' AND page_id = '$page_id'";
                    mysqli_query($conn, $delete_sql);
                }
            } else {
                if ($has_access) {
                    $insert_sql = "INSERT INTO user_page_access (staff_id, page_id, permission) VALUES ('$staff_id', '$page_id', '$permission')";
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
