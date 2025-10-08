<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'product_type';
$test_table = 'product_type_excel';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $dimension_id       = mysqli_real_escape_string($conn, $_POST['dimension_id']);
        $dimension          = mysqli_real_escape_string($conn, $_POST['dimension']);
        $dimension_unit     = mysqli_real_escape_string($conn, $_POST['dimension_unit']);
        $dimension_category = mysqli_real_escape_string($conn, $_POST['dimension_category']);
        $dimension_abbreviation = mysqli_real_escape_string($conn, $_POST['dimension_abbreviation']);

        $checkQuery = "SELECT * FROM dimensions WHERE dimension_id = '$dimension_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE dimensions 
                            SET dimension = '$dimension',
                                dimension_unit = '$dimension_unit',
                                dimension_category = '$dimension_category',
                                dimension_abbreviation = '$dimension_abbreviation'
                            WHERE dimension_id = '$dimension_id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating dimension: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO dimensions (dimension, dimension_unit, dimension_category, dimension_abbreviation) 
                            VALUES ('$dimension', '$dimension_unit', '$dimension_category', '$dimension_abbreviation')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding dimension: " . mysqli_error($conn);
            }
        }
    }

    if ($action == 'fetch_modal_content') {
        $dimension_id = mysqli_real_escape_string($conn, $_POST['id']);
        $row = [];

        if (!empty($dimension_id)) {
            $query = "SELECT * FROM dimensions WHERE dimension_id = '$dimension_id'";
            $result = mysqli_query($conn, $query);
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_array($result);
            }
        }

        ?>
        <div class="row pt-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select id="dimension_category" class="form-control" name="dimension_category">
                        <option value="">Select One...</option>
                        <?php
                        $query_categories = "SELECT * FROM product_category WHERE hidden = 0 AND status = 1 ORDER BY product_category ASC";
                        $result_categories = mysqli_query($conn, $query_categories);
                        while ($cat = mysqli_fetch_array($result_categories)) {
                            $selected = (($row['dimension_category'] ?? '') == $cat['product_category_id']) ? 'selected' : '';
                            echo "<option value='{$cat['product_category_id']}' {$selected}>{$cat['product_category']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Abbreviation</label>
                    <input type="text" id="dimension_abbreviation" name="dimension_abbreviation" class="form-control" value="<?= $row['dimension_abbreviation'] ?? '' ?>" />
                </div>
            </div>
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Dimension</label>
                    <input type="text" id="dimension" name="dimension" class="form-control" value="<?= $row['dimension'] ?? '' ?>" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Unit</label>
                    <select id="dimension_unit" name="dimension_unit" class="form-control">
                        <?php
                        $units = ['feet' => 'Feet', 'inch' => 'Inches', 'meter' => 'Meters'];
                        $selectedUnit = strtolower($row['dimension_unit'] ?? '');
                        foreach ($units as $value => $label) {
                            $selected = ($selectedUnit === $value) ? 'selected' : '';
                            echo "<option value=\"$value\" $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        

        <input type="hidden" id="dimension_id" name="dimension_id" value="<?= $dimension_id ?>" />
        <?php
    }


    if ($action === 'fetch_table') {
        $permission = $_SESSION['permission'] ?? '';
        $query = "SELECT * FROM dimensions";
        $result = mysqli_query($conn, $query);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $dimension_id       = $row['dimension_id'];
            $dimension_category = $row['dimension_category'];
            $dimension          = $row['dimension'];
            $dimension_unit     = $row['dimension_unit'];
            $dimension_abbreviation     = $row['dimension_abbreviation'];

            $action_html = '';
            if ($permission === 'edit') {
                $action_html = "<a href='javascript:void(0)' id='addModalBtn' title='Edit' class='d-flex align-items-center justify-content-center text-decoration-none' data-id='$dimension_id' data-type='edit'>
                                    <i class='ti ti-pencil fs-7'></i>
                                </a>";
            }

            $data[] = [
                'dimension'             => $dimension . ' ' . $dimension_unit,
                'dimension_category_id' => $dimension_category,
                'dimension_abbreviation' => $dimension_abbreviation,
                'dimension_category'    => getProductCategoryName($dimension_category),
                'action'                => $action_html
            ];
        }

        echo json_encode(['data' => $data]);
        exit;
    }


    mysqli_close($conn);
}
?>
