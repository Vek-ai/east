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

$table = 'loyalty_program';
$test_table = 'loyalty_program_excel';
$main_primary_key = getPrimaryKey($table);

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $loyalty_id = mysqli_real_escape_string($conn, $_POST['loyalty_id']);
        $loyalty_program_name = mysqli_real_escape_string($conn, $_POST['loyalty_program_name']);
        $accumulated_total_orders = mysqli_real_escape_string($conn, $_POST['accumulated_total_orders']);
        $discount = mysqli_real_escape_string($conn, $_POST['discount']);
        $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
        $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        if (!empty($loyalty_id)) {
            $checkQuery = "SELECT * FROM loyalty_program WHERE loyalty_id = '$loyalty_id'";
            $result = mysqli_query($conn, $checkQuery);

            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $current_loyalty_program = $row['loyalty_program_name'];

                if ($loyalty_program_name != $current_loyalty_program) {
                    $checkLoyalty = "SELECT * FROM loyalty_program WHERE loyalty_program_name = '$loyalty_program_name'";
                    $resultLoyalty = mysqli_query($conn, $checkLoyalty);
                    if (mysqli_num_rows($resultLoyalty) > 0) {
                        echo "Loyalty Program Name already exists! Please choose a unique value.";
                        exit;
                    }
                }

                $updateQuery = "
                    UPDATE loyalty_program 
                    SET 
                        loyalty_program_name = '$loyalty_program_name', 
                        accumulated_total_orders = '$accumulated_total_orders', 
                        discount = '$discount', 
                        date_from = '$date_from', 
                        date_to = '$date_to', 
                        last_edit = NOW(), 
                        edited_by = '$userid' 
                    WHERE 
                        loyalty_id = '$loyalty_id'
                ";

                if (mysqli_query($conn, $updateQuery)) {
                    echo "success_update";
                } else {
                    echo "Error updating loyalty program: " . mysqli_error($conn);
                }
            }
        } else {
            $checkLoyalty = "SELECT * FROM loyalty_program WHERE loyalty_program_name = '$loyalty_program_name'";
            $resultLoyalty = mysqli_query($conn, $checkLoyalty);
            if (mysqli_num_rows($resultLoyalty) > 0) {
                echo "Loyalty Program Name already exists! Please choose a unique value.";
                exit;
            }

            $insertQuery = "
                INSERT INTO loyalty_program (
                    loyalty_program_name, 
                    accumulated_total_orders, 
                    discount, 
                    date_from, 
                    date_to, 
                    added_date, 
                    added_by
                ) VALUES (
                    '$loyalty_program_name', 
                    '$accumulated_total_orders', 
                    '$discount', 
                    '$date_from', 
                    '$date_to', 
                    NOW(), 
                    '$userid'
                )
            ";

            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding loyalty program: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "change_status") {
        $loyalty_id = mysqli_real_escape_string($conn, $_POST['loyalty_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE loyalty_program SET status = '$new_status' WHERE loyalty_id = '$loyalty_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == 'hide_loyalty_program') {
        $loyalty_id = mysqli_real_escape_string($conn, $_POST['loyalty_id']);
        $query = "UPDATE loyalty_program SET hidden = '1' WHERE loyalty_id = '$loyalty_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'Error hiding category: ' . mysqli_error($conn);
        }
    }

    if ($action == 'fetch_modal_content') {
        $loyalty_program_name = '';
        $accumulated_total_orders = '';
        $discount = '';
        $date_from = '';
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM $table WHERE $main_primary_key = '$id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            $loyalty_program_name = $row['loyalty_program_name'];
            $accumulated_total_orders = $row['accumulated_total_orders'];
            $discount = $row['discount'];
            $date_from = $row['date_from'];
        }
        ?>
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Loyalty Program</label>
                        <input type="text" id="loyalty_program_name" name="loyalty_program_name" class="form-control"  value="<?= $loyalty_program_name ?>"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Accumulated Total Orders</label>
                        <input type="number" id="accumulated_total_orders" name="accumulated_total_orders" class="form-control" value="<?= $accumulated_total_orders ?>"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Discount (%)</label>
                        <input type="number" id="discount" name="discount" class="form-control" value="<?= $discount ?>"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Validity Date Start</label>
                        <input type="date" id="date_from" name="date_from" class="form-control" value="<?= $date_from ?>"/>
                    </div>
                </div>
            </div>

            <input type="hidden" id="loyalty_id" name="loyalty_id" class="form-control"  value="<?= $id ?>"/>
        <?php
    }

    if ($action == "download_excel") {
        $includedColumns = array();
        $column_txt = '*';

        $includedColumns = [ 
            'loyalty_id',
            'loyalty_program_name',
            'accumulated_total_orders',
            'discount',
            'date_from'
        ];

        $column_txt = implode(', ', $includedColumns);

        $sql = "SELECT " . $column_txt . " FROM $table WHERE hidden = '0' AND status = '1'";
        $result = $conn->query($sql);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [];
        $row = 1;
        
        foreach ($includedColumns as $index => $column) {
            $header = ucwords(str_replace('_', ' ', $column));
        
            if ($index >= 26) {
                $columnLetter = indexToColumnLetter($index);
            } else {
                $columnLetter = chr(65 + $index);
            }
        
            $sheet->setCellValue($columnLetter . $row, $header);
        }        

        $row = 2;
        while ($data = $result->fetch_assoc()) {
            foreach ($includedColumns as $index => $column) {
                if ($index >= 26) {
                    $columnLetter = indexToColumnLetter($index);
                } else {
                    $columnLetter = chr(65 + $index);
                }
                $sheet->setCellValue($columnLetter . $row, $data[$column] ?? '');
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
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            if ($fileExtension != "xlsx" && $fileExtension != "xls") {
                echo "Please upload a valid Excel file.";
                exit;
            }

            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $columns = $rows[0];
            $dbColumns = [];
            $columnMapping = [];

            foreach ($columns as $col) {
                $dbColumn = strtolower(str_replace(' ', '_', $col));

                $dbColumns[] = $dbColumn;
                $columnMapping[$dbColumn] = $col;
            }

            $truncateSql = "TRUNCATE TABLE $test_table";
            $truncateResult = $conn->query($truncateSql);

            if (!$truncateResult) {
                echo "Error truncating table: " . $conn->error;
                exit;
            }

            foreach ($rows as $index => $row) {
                if ($index == 0) {
                    continue;
                }

                $data = array_combine($dbColumns, $row);

                $columnNames = implode(", ", array_keys($data));
                $columnValues = implode("', '", array_map(function($value) { return $value ?? ''; }, array_values($data)));

                $sql = "INSERT INTO $test_table ($columnNames) VALUES ('$columnValues')";
                $result = $conn->query($sql);

                if (!$result) {
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
    
    if ($action == "update_test_data") {
        $column_name = $_POST['header_name'];
        $new_value = $_POST['new_value'];
        $id = $_POST['id'];
        
        if (empty($column_name) || empty($id)) {
            exit;
        }

        $test_primary = getPrimaryKey($test_table);
        
        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $id = mysqli_real_escape_string($conn, $id);
        
        $sql = "UPDATE $test_table SET `$column_name` = '$new_value' WHERE $test_primary = '$id'";

        if ($conn->query($sql) === TRUE) {
            echo 'success';
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
    }

    if ($action == "save_table") {
        $main_primary = getPrimaryKey($table);
        $test_primary = getPrimaryKey($test_table);
        
        $selectSql = "SELECT * FROM $test_table";
        $result = $conn->query($selectSql);
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $main_primary_id = trim($row[$main_primary] ?? ''); 
    
                unset($row[$test_primary]);
    
                if (!empty($main_primary_id)) {
                    $checkSql = "SELECT COUNT(*) as count FROM $table WHERE $main_primary = '$main_primary_id'";
                    $checkResult = $conn->query($checkSql);
                    $exists = $checkResult->fetch_assoc()['count'] > 0;
    
                    if ($exists) {
                        $updateFields = [];
                        foreach ($row as $column => $value) {
                            if ($column !== $main_primary && $value !== null && $value !== '') {
                                $updateFields[] = "$column = '$value'";
                            }
                        }
                        if (!empty($updateFields)) {
                            $updateSql = "UPDATE $table SET " . implode(", ", $updateFields) . " WHERE $main_primary = '$main_primary_id'";
                            $conn->query($updateSql);
                        }
                        continue;
                    }
                }
    
                $columns = [];
                $values = [];
                foreach ($row as $column => $value) {
                    if ($value !== null && $value !== '') {
                        $columns[] = $column;
                        $values[] = "'$value'";
                    }
                }
                if (!empty($columns)) {
                    $insertSql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
                    $conn->query($insertSql);
                }
            }
    
            echo "Data has been successfully saved";
    
            $truncateSql = "TRUNCATE TABLE $test_table";
            if ($conn->query($truncateSql) !== TRUE) {
                echo " but failed to clear test color table: " . $conn->error;
            }
        } else {
            echo "No data found in test color table.";
        }
    }     

    if ($action == "fetch_uploaded_modal") {
        $test_primary = getPrimaryKey($test_table);
        
        $sql = "SELECT * FROM $test_table";
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            $columns = [];
            while ($field = $result->fetch_field()) {
                $columns[] = $field->name;
            }
    
            $includedColumns = [ 
                'loyalty_id',
                'loyalty_program_name',
                'accumulated_total_orders',
                'discount',
                'date_from'
            ];
    
            $columns = array_filter($columns, function ($col) use ($includedColumns) {
                return in_array($col, $includedColumns, true);
            });
    
            $columnsWithData = [];
            while ($row = $result->fetch_assoc()) {
                foreach ($columns as $column) {
                    if (!empty(trim($row[$column] ?? ''))) {
                        $columnsWithData[$column] = true;
                    }
                }
            }
    
            $result->data_seek(0);
            ?>
    
            <div class="card card-body shadow" data-table="<?=$table?>">
                <form id="tableForm">
                    <div style="overflow-x: auto; overflow-y: auto; max-height: 80vh; max-width: 100%;">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <?php
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $formattedColumn = ucwords(str_replace('_', ' ', $column));
                                            echo "<th class='fs-4'>$formattedColumn</th>";
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                while ($row = $result->fetch_assoc()) {
                                    $primaryValue = $row[$test_primary] ?? '';
                                    echo '<tr>';
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $value = htmlspecialchars($row[$column] ?? '', ENT_QUOTES, 'UTF-8');
                                            echo "<td contenteditable='true' class='table_data' data-header-name='$column' data-id='$primaryValue'>$value</td>";
                                        }
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

    mysqli_close($conn);
}
?>
