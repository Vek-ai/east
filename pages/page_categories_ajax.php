<?php
session_start();
require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'page_categories';
$test_table = 'page_categories_excel';

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
        $query = "SELECT * FROM page_categories WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
        }

        ?>
        <div class="row pt-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" id="category_name" name="category_name" placeholder="Category Name" class="form-control"  value="<?= $row['category_name'] ?? '' ?>"/>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5"><?= $row['description'] ?? '' ?></textarea>
        </div>

            <input type="hidden" id="id" name="id" class="form-control"  value="<?= $id ?>"/>
        <?php
    }
    
    if ($action === 'fetch_table') {
        $permission = $_SESSION['permission'];

        $query = "SELECT * FROM page_categories";
        $result = mysqli_query($conn, $query);
    
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['id'];
            $category_name = $row['category_name'];
            $description = $row['description'];
    
            $action_html = '';
            if ($permission === 'edit') {
                $action_html = "<a href='javascript:void(0)' id='addModalBtn' title='Edit' class='d-flex align-items-center justify-content-center text-decoration-none' data-id='$id' data-type='edit'>
                                    <i class='ti ti-pencil fs-7'></i>
                                </a>";
            }
    
            $data[] = [
                'category_name' => $category_name,
                'description' => $description,
                'action_html' => $action_html
            ];
        }
    
        echo json_encode(['data' => $data]);
        exit;
    }

    mysqli_close($conn);
}
?>
