<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'supplier_type';
$test_table = 'supplier_type_excel';
$main_primary_key = getPrimaryKey($table);

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $supplier_type_id = mysqli_real_escape_string($conn, $_POST['supplier_type_id']);
        $supplier_type = mysqli_real_escape_string($conn, $_POST['supplier_type']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM supplier_type WHERE supplier_type_id = '$supplier_type_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_supplier_type = $row['supplier_type'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($supplier_type != $current_supplier_type) {
                $checkCategory = "SELECT * FROM supplier_type WHERE supplier_type = '$current_supplier_type'";
                $resultCategory = mysqli_query($conn, $checkCategory);
                if (mysqli_num_rows($resultCategory) > 0) {
                    $duplicates[] = "Supplier Type";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE supplier_type SET supplier_type = '$supplier_type', description = '$description', last_edit = NOW(), edited_by = '$userid'  WHERE supplier_type_id = '$supplier_type_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Supplier type updated successfully.";
                } else {
                    echo "Error updating supplier type: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkCategory = "SELECT * FROM supplier_type WHERE supplier_type = '$supplier_type'";
            $resultCategory = mysqli_query($conn, $checkCategory);
            if (mysqli_num_rows($resultCategory) > 0) {
                $duplicates[] = "Supplier Type";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO supplier_type (supplier_type, description, added_date, added_by) VALUES ('$supplier_type', '$description', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "New supplier type added successfully.";
                } else {
                    echo "Error adding supplier type: " . mysqli_error($conn);
                }
            }
        }
    } 

    if ($action == 'fetch_modal_content') {
        $id = '';
        $supplier_type = '';
        $description = '';
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM $table WHERE $main_primary_key = '$id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            $supplier_type_id = $row['supplier_type_id'];
            $supplier_type = $row['supplier_type'];
            $description = $row['description'];
        }

        ?>
            <div class="row pt-3">
                <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Supplier Type</label>
                    <input type="text" id="supplier_type" name="supplier_type" class="form-control"  value="<?= $supplier_type ?>"/>
                </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Role Description</label>
                <textarea class="form-control" id="description" name="description" rows="5"><?= $description ?></textarea>
            </div>

            <input type="hidden" id="supplier_type_id" name="supplier_type_id" class="form-control"  value="<?= $id ?>"/>
        <?php
    }

    if ($action == "fetch_data") {
        header('Content-Type: application/json');

        $query = "SELECT color_group, gauge, profile, width, multiplier FROM panel_multiplier";
        $result = mysqli_query($conn, $query);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                'color_group' => $row['color_group'],
                'gauge' => $row['gauge'],
                'profile' => $row['profile'],
                'width' => $row['width'],
                'multiplier' => $row['multiplier']
            ];
        }
        echo json_encode($data);
    }

    if ($action === 'save_multiplier') {
        $cg = mysqli_real_escape_string($conn, $_POST['color_group']);
        $g = (int) $_POST['gauge'];
        $p = mysqli_real_escape_string($conn, $_POST['profile']);
        $w = (float) $_POST['width'];
        $m = (float) $_POST['multiplier'];

        $update = "UPDATE panel_multiplier 
                SET multiplier = $m 
                WHERE color_group = '$cg' AND gauge = $g AND profile = '$p' AND width = $w";
        mysqli_query($conn, $update);

        if (mysqli_affected_rows($conn) === 0) {
            $insert = "INSERT INTO panel_multiplier (color_group, gauge, profile, width, multiplier) 
                    VALUES ('$cg', $g, '$p', $w, $m)";
            mysqli_query($conn, $insert);
        }

        echo "Successfully saved";
        exit;
    }


    mysqli_close($conn);
}
?>
