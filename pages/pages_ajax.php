<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $page_name = mysqli_real_escape_string($conn, $_POST['page_name']);
        $file_name = mysqli_real_escape_string($conn, $_POST['file_name']);
        $url = mysqli_real_escape_string($conn, $_POST['url']);
        $category_id = !empty($_POST['category_id']) ? mysqli_real_escape_string($conn, $_POST['category_id']) : 'NULL';

        $checkDuplicate = "SELECT * FROM pages WHERE (page_name = '$page_name' OR file_name = '$file_name') AND id != '$id'";
        $duplicateResult = mysqli_query($conn, $checkDuplicate);

        if (mysqli_num_rows($duplicateResult) > 0) {
            echo "duplicate_page";
            exit;
        }

        $checkQuery = "SELECT * FROM pages WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE pages SET 
                                page_name = '$page_name', 
                                file_name = '$file_name', 
                                url = '$url', 
                                category_id = $category_id 
                            WHERE id = '$id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating page: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO pages (page_name, file_name, url, category_id) 
                            VALUES ('$page_name', '$file_name', '$url', $category_id)";
            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding page: " . mysqli_error($conn);
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
        $category_id = mysqli_real_escape_string($conn, $_POST['category_id'] ?? '');

        $query = "SELECT * FROM pages";
        if (!empty($category_id) && $category_id !== 'all') {
            $query .= " WHERE category_id = '$category_id'";
        }

        $result = mysqli_query($conn, $query);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['id'];
            $page_name = $row['page_name'];
            $file_name = $row['file_name'];
            $url = $row['url'];
            $category_id = $row['category_id'];

            $action_html = "<a href='javascript:void(0)' id='addModalBtn' title='Edit' class='d-flex align-items-center justify-content-center text-decoration-none' data-id='$id' data-type='edit'>
                                <i class='ti ti-pencil fs-7'></i>
                            </a>";

            $data[] = [
                'page_name' => $page_name,
                'file_name' => $file_name,
                'url' => $url,
                'category_id' => $category_id,
                'category' => getPageCategoryName($category_id),
                'action_html' => $action_html,
            ];
        }

        echo json_encode(['data' => $data]);
        exit;
    }

    mysqli_close($conn);
}
?>
