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

$table = 'product_screw_type';
$test_table = 'product_screw_type_excel';

$includedColumns = [
    'product_screw_type_id'   => 'ID',
    'product_screw_type'      => 'Screw Type',
    'type_abreviations' => 'Abbreviation',
    'notes'             => 'Notes'
];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_screw_type_id = mysqli_real_escape_string($conn, $_POST['product_screw_type_id']);
        $product_screw_type = mysqli_real_escape_string($conn, $_POST['product_screw_type']);
        $type_abreviations = mysqli_real_escape_string($conn, $_POST['type_abreviations']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        $dimensions = isset($_POST['dimensions']) ? $_POST['dimensions'] : [];
        $dimensions = array_filter($dimensions, function($v) {
            return $v !== "" && is_numeric($v);
        });

        $dimensions = array_map('intval', $dimensions);
        $dimensions_json = json_encode($dimensions, JSON_NUMERIC_CHECK);
        $dimensions_sql = mysqli_real_escape_string($conn, $dimensions_json);

        $checkQuery = "SELECT type_abreviations FROM product_screw_type WHERE product_screw_type_id = '$product_screw_type_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $old_abbr = $row['type_abreviations'];

            $updateQuery = "UPDATE product_screw_type 
                            SET product_screw_type = '$product_screw_type',
                                type_abreviations = '$type_abreviations',
                                notes = '$notes',
                                dimensions = '$dimensions_sql',
                                last_edit = NOW(),
                                edited_by = '$userid'
                            WHERE product_screw_type_id = '$product_screw_type_id'";

            if (mysqli_query($conn, $updateQuery)) {
                echo "update-success";
                if ($old_abbr !== $type_abreviations) {
                    regenerateABR('product_screw_type', $product_screw_type_id);
                }
            } else {
                echo "Error updating product type: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO product_screw_type 
                            (product_screw_type, type_abreviations, notes, dimensions, added_date, added_by) 
                            VALUES 
                            ('$product_screw_type', '$type_abreviations', '$notes', '$dimensions_sql', NOW(), '$userid')";

            if (mysqli_query($conn, $insertQuery)) {
                echo "add-success";
            } else {
                echo "Error adding product type: " . mysqli_error($conn);
            }
        }
    }

    
    if ($action == "change_status") {
        $product_screw_type_id = mysqli_real_escape_string($conn, $_POST['product_screw_type_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_screw_type SET status = '$new_status' WHERE product_screw_type_id = '$product_screw_type_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_screw_type') {
        $product_screw_type_id = mysqli_real_escape_string($conn, $_POST['product_screw_type_id']);
        $query = "UPDATE product_screw_type SET hidden='1' WHERE product_screw_type_id='$product_screw_type_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_modal_content') {
        $product_screw_type_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM product_screw_type WHERE product_screw_type_id = '$product_screw_type_id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
        }

        $lengths = [];
        $lengthQuery = "SELECT * FROM dimensions WHERE dimension_category = 16 ORDER BY dimension ASC";
        $lengthRes = mysqli_query($conn, $lengthQuery);
        if ($lengthRes && mysqli_num_rows($lengthRes) > 0) {
            while ($l = mysqli_fetch_assoc($lengthRes)) {
                $lengths[] = $l;
            }
        }

        $selected_lengths = isset($row['dimensions']) ? json_decode($row['dimensions'], true) : [];
        if (!is_array($selected_lengths)) $selected_lengths = [];
        ?>
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Screw type</label>
                        <input type="text" id="product_screw_type" name="product_screw_type" class="form-control"  value="<?= $row['product_screw_type'] ?? '' ?>"/>
                    </div>
                </div>
                <div class="col-md-6"></div>
            </div>

            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Abbreviation</label>
                        <input type="text" id="type_abreviations" name="type_abreviations" class="form-control" value="<?= $row['type_abreviations'] ?? '' ?>" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label">Screw Length(s)</label>
                        <a href="?page=dimensions" target="_blank" class="text-decoration-none">Edit</a>
                    </div>
                    <div class="mb-3">
                        <select id="dimensions" name="dimensions[]" class="form-control select2_modal" multiple>
                            <?php foreach ($lengths as $l): ?>
                                <?php 
                                    $id = $l['dimension_id']; 
                                    $text = $l['dimension'] . ' ' . ($l['dimension_unit'] ?? '');
                                    $sel = in_array($id, $selected_lengths) ? 'selected' : '';
                                ?>
                                <option value="<?= $id ?>" <?= $sel ?>><?= $text ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="5"><?= $row['notes'] ?? '' ?></textarea>
            </div>

            <input type="hidden" id="product_screw_type_id" name="product_screw_type_id" class="form-control"  value="<?= $product_screw_type_id ?>"/>
        <?php
    }

    if ($action == "download_excel") {
        $column_txt = implode(', ', array_keys($includedColumns));
        $sql = "SELECT $column_txt FROM $table WHERE hidden = '0' AND status = '1'";
        $result = $conn->query($sql);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;

        $index = 0;
        foreach ($includedColumns as $dbColumn => $displayName) {
            $columnLetter = ($index >= 26) ? indexToColumnLetter($index) : chr(65 + $index);
            $sheet->setCellValue($columnLetter . $row, $displayName);
            $index++;
        }

        $row++;

        while ($data = $result->fetch_assoc()) {
            $index = 0;
            foreach ($includedColumns as $dbColumn => $displayName) {
                $columnLetter = ($index >= 26) ? indexToColumnLetter($index) : chr(65 + $index);
                $sheet->setCellValue($columnLetter . $row, $data[$dbColumn] ?? '');
                $index++;
            }
            $row++;
        }

        $name = strtoupper(str_replace('_', ' ', $table));
        $filename = "$name.xlsx";
        $filePath = $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=0');

        readfile($filePath);
        unlink($filePath);
        exit;
    }

    if ($action == "upload_excel") {
        if (isset($_FILES['excel_file'])) {
            $fileTmpPath = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, ["xlsx", "xls"])) {
                echo "Please upload a valid Excel file.";
                exit;
            }

            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $dbColumns = array_keys($includedColumns);

            if (!$conn->query("TRUNCATE TABLE $test_table")) {
                echo "Error truncating table: " . $conn->error;
                exit;
            }

            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex === 0) {
                    continue;
                }

                $data = [];
                $allEmpty = true;
                foreach ($dbColumns as $i => $colName) {
                    $cellValue = isset($row[$i]) ? $row[$i] : '';
                    $cellValue = (string)$cellValue;
                    if ($cellValue !== '') $allEmpty = false;
                    $data[$colName] = mysqli_real_escape_string($conn, $cellValue);
                }

                if ($allEmpty) continue;

                $columnNames = implode(", ", array_keys($data));
                $columnValues = implode("', '", array_values($data));

                $sql = "INSERT INTO $test_table ($columnNames) VALUES ('$columnValues')";
                if (!$conn->query($sql)) {
                    echo "Error inserting data: " . $conn->error;
                    exit;
                }
            }

            echo "success";
        } else {
            echo "No file uploaded.";
            exit;
        }
    }

    if ($action == "fetch_uploaded_modal") {
        $test_primary = getPrimaryKey($test_table);
        $sql = "SELECT * FROM $test_table";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            ?>
            <div class="card card-body shadow" data-table="<?= $table ?>">
                <form id="tableForm">
                    <div style="overflow-x: auto; overflow-y: auto; max-height: 80vh; max-width: 100%;">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <?php
                                    foreach ($includedColumns as $dbColumn => $displayName) {
                                        echo "<th class='fs-4'>" . htmlspecialchars($displayName) . "</th>";
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                $primaryValue = $row[$test_primary] ?? '';
                                echo '<tr>';
                                foreach ($includedColumns as $dbColumn => $displayName) {
                                    $value = htmlspecialchars($row[$dbColumn] ?? '', ENT_QUOTES, 'UTF-8');
                                    echo "<td contenteditable='true' class='table_data' data-header-name='$dbColumn' data-id='$primaryValue'>$value</td>";
                                }
                                echo '</tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" id="saveTable" class="btn btn-primary mt-3">Save</button>
                    </div>
                </form>
            </div>
            <?php
        } else {
            echo "<p>No data found in the table.</p>";
        }
    }

    if ($action == "update_test_data") {
        $column_name = $_POST['header_name'] ?? '';
        $new_value = $_POST['new_value'] ?? '';
        $id = $_POST['id'] ?? '';

        if (empty($column_name) || empty($id)) exit;

        $test_primary = getPrimaryKey($test_table);

        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $id = mysqli_real_escape_string($conn, $id);

        $sql = "UPDATE $test_table SET `$column_name` = '$new_value' WHERE $test_primary = '$id'";
        echo $conn->query($sql) ? 'success' : 'Error updating record: ' . $conn->error;
    }

    if ($action == "save_table") {
        $main_primary = getPrimaryKey($table);
        $test_primary = getPrimaryKey($test_table);

        $result = $conn->query("SELECT * FROM $test_table");

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $main_primary_id = trim($row[$main_primary] ?? '');
                unset($row[$test_primary]);

                $escape = fn($val) => mysqli_real_escape_string($conn, $val === null ? '' : (string)$val);

                if (!empty($main_primary_id)) {
                    $checkSql = "SELECT COUNT(*) as count FROM $table WHERE $main_primary = '" . $escape($main_primary_id) . "'";
                    $checkResult = $conn->query($checkSql);
                    $exists = $checkResult->fetch_assoc()['count'] > 0;

                    if ($exists) {
                        $updateFields = [];
                        foreach ($row as $column => $value) {
                            if ($column !== $main_primary) {
                                $updateFields[] = "$column = '" . $escape($value) . "'";
                            }
                        }

                        if (!empty($updateFields)) {
                            $updateSql = "UPDATE $table SET " . implode(", ", $updateFields) . " WHERE $main_primary = '" . $escape($main_primary_id) . "'";
                            $conn->query($updateSql);
                        }

                        continue;
                    }
                }

                $columns = [];
                $values = [];
                foreach ($row as $column => $value) {
                    $columns[] = $column;
                    $values[] = "'" . $escape($value) . "'";
                }

                if (!empty($columns)) {
                    $insertSql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
                    $conn->query($insertSql);
                }
            }

            echo "Data has been successfully saved";

            $conn->query("TRUNCATE TABLE $test_table");
        } else {
            echo "No data found in test table.";
        }
    }

    if ($action === 'fetch_table') {
        $permission = $_SESSION['permission'];
        $query = "SELECT * FROM product_screw_type WHERE hidden = 0";
        $result = mysqli_query($conn, $query);
    
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $no = $row['product_screw_type_id'];
            $product_screw_type = $row['product_screw_type'];
            $type_abreviations = $row['type_abreviations'];
            $notes = !empty($row['notes']) 
            ? (strlen($row['notes']) > 30 ? substr($row['notes'], 0, 30) . '...' : $row['notes']) 
            : '';
    
            $last_edit = !empty($row['last_edit']) ? (new DateTime($row['last_edit']))->format('m/d/Y') : '';
    
            $added_by = $row['added_by'];
            $edited_by = $row['edited_by'];
    
            if ($edited_by != "0") {
                $last_user_name = get_name($edited_by);
            } elseif ($added_by != "0") {
                $last_user_name = get_name($added_by);
            } else {
                $last_user_name = "";
            }
    
            $status_html = $row['status'] == '0'
                ? "<a href='javascript:void(0)' class='changeStatus' data-no='$no' data-id='$no' data-status='0'>
                        <div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;'>Inactive</div>
                   </a>"
                : "<a href='javascript:void(0)' class='changeStatus' data-no='$no' data-id='$no' data-status='1'>
                        <div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;'>Active</div>
                   </a>";
    
            $action_html = '';
            if ($permission === 'edit') {
                $action_html = $row['status'] == '0'
                    ? "<a href='javascript:void(0)' class='py-1 text-dark hideType' title='Archive' data-id='$no' data-row='$no' style='border-radius: 10%;'>
                            <i class='text-danger ti ti-trash fs-7'></i>
                    </a>"
                    : "<a href='javascript:void(0)' id='addModalBtn' title='Edit' class='d-flex align-items-center justify-content-center text-decoration-none' data-id='$no' data-type='edit'>
                            <i class='ti ti-pencil fs-7'></i>
                    </a>";
            }
    
            $data[] = [
                'product_screw_type' => $product_screw_type,
                'type_abreviations' => $type_abreviations,
                'notes' => $notes,
                'last_edit_by' => $last_user_name,
                'last_edit' => $last_edit,
                'status_html' => $status_html,
                'action_html' => $action_html
            ];
        }
    
        echo json_encode(['data' => $data]);
        exit;
    }

    mysqli_close($conn);
}
?>
