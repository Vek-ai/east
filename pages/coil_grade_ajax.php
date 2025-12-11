<?php
session_start();
require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'coil_grade';
$test_table = 'coil_grade_excel';

$includedColumns = [ 
    'coil_grade_id',
    'coil_grade',
    'grade_abbreviations',
    'notes'
];

$permission = $_SESSION['permission'];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $coil_grade_id = mysqli_real_escape_string($conn, $_POST['coil_grade_id']);
        $coil_grade = mysqli_real_escape_string($conn, $_POST['coil_grade'] ?? '');
        $grade_abbreviations = mysqli_real_escape_string($conn, $_POST['grade_abbreviations'] ?? '');
        $grade_id_no = mysqli_real_escape_string($conn, $_POST['grade_id_no'] ?? '');
        $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        $checkQuery = "SELECT grade_abbreviations FROM coil_grade WHERE coil_grade_id = '$coil_grade_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $old_abbr = $row['grade_abbreviations'];

            $updateQuery = "UPDATE coil_grade 
                            SET coil_grade = '$coil_grade', 
                                grade_abbreviations = '$grade_abbreviations', 
                                grade_id_no = '$grade_id_no', 
                                notes = '$notes', 
                                last_edit = NOW(), 
                                edited_by = '$userid'  
                            WHERE coil_grade_id = '$coil_grade_id'";

            if (mysqli_query($conn, $updateQuery)) {
                echo "update-success";
                if ($old_abbr !== $grade_abbreviations) {
                    regenerateABR('coil_grade', $coil_grade_id);
                }
            } else {
                echo "Error updating product grade: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO coil_grade 
                            (coil_grade, grade_abbreviations, grade_id_no, notes, added_date, added_by) 
                            VALUES ('$coil_grade', '$grade_abbreviations', '$grade_id_no', '$notes', NOW(), '$userid')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "add-success";
            } else {
                echo "Error adding product grade: " . mysqli_error($conn);
            }
        }
    }
    
    if ($action == "change_status") {
        $coil_grade_id = mysqli_real_escape_string($conn, $_POST['coil_grade_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE coil_grade SET status = '$new_status' WHERE coil_grade_id = '$coil_grade_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_coil_grade') {
        $coil_grade_id = mysqli_real_escape_string($conn, $_POST['coil_grade_id']);
        $query = "UPDATE coil_grade SET hidden='1' WHERE coil_grade_id='$coil_grade_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_modal_content') {
        $coil_grade_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM coil_grade WHERE coil_grade_id = '$coil_grade_id'";
        $result = mysqli_query($conn, $query);
        $selected_categories = [];
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
        }
        ?>
            <div class="row pt-3">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Product grade</label>
                        <input type="text" id="coil_grade" name="coil_grade" class="form-control"  value="<?= $row['coil_grade'] ?? '' ?>"/>
                    </div>
                </div>
                <div class="col-md-8"></div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Grade Abreviations</label>
                        <input type="text" id="grade_abbreviations" name="grade_abbreviations" class="form-control" value="<?= $row['grade_abbreviations'] ?? '' ?>" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Grade ID #</label>
                        <input type="text" id="grade_id_no" name="grade_id_no" class="form-control" value="<?= $row['grade_id_no'] ?? '' ?>" />
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="5"><?= $row['notes'] ?? '' ?></textarea>
                    </div>
                </div>
            </div>

            <input type="hidden" id="coil_grade_id" name="coil_grade_id" class="form-control"  value="<?= $coil_grade_id ?>"/>
        <?php
    }

    if ($action == "download_excel") {
        $column_txt = '*';

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

    if ($action == 'fetch_table') {
        $query = "SELECT * FROM coil_grade WHERE hidden = 0";
        $result = mysqli_query($conn, $query);
    
        $data = [];
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            $rowData = [];
    
            $rowData['coil_grade'] = '<span class="product' . $no . ($row['status'] == '0' ? ' emphasize-strike' : '') . '">' . $row['coil_grade'] . '</span>';
            $rowData['grade_abbreviations'] = $row['grade_abbreviations'];
            $rowData['grade_id_no'] = $row['grade_id_no'];

            $rowData['notes'] = strlen($row['notes']) > 30 ? substr($row['notes'], 0, 30) . '...' : $row['notes'];
            
            $last_edit = '';
            if (!empty($row['last_edit'])) {
                $date = new DateTime($row['last_edit']);
                $last_edit = $date->format('m-d-Y');
            }
    
            $user_id = $row['edited_by'] != 0 ? $row['edited_by'] : $row['added_by'];
            $last_user_name = $user_id ? get_name($user_id) : '';
            $rowData['last_edit_by'] = $last_user_name;
            $rowData['last_edit'] = $last_edit;
    
            $status = $row['status'];
            $rowData['status_html'] = '<a href="javascript:void(0)" class="changeStatus" data-no="' . $no . '" data-id="' . $row['coil_grade_id'] . '" data-status="' . $status . '"><div id="status-alert' . $no . '" class="alert ' . ($status == '0' ? 'alert-danger bg-danger' : 'alert-success bg-success') . ' text-white border-0 text-center py-1 px-2 my-0" style="border-radius: 5%;">' . ($status == '0' ? 'Inactive' : 'Active') . '</div></a>';
    
            $rowData['action_html'] = '';
            if ($permission === 'edit') {
                if ($status == '0') {
                    $rowData['action_html'] = '<a href="javascript:void(0)" class="text-decoration-none py-1 text-dark hideProductGrade" data-id="' . $row['coil_grade_id'] . '" data-row="' . $no . '"><i class="text-danger ti ti-trash fs-7"></i></a>';
                } else {
                    $rowData['action_html'] = '<a href="javascript:void(0)" class="text-decoration-none py-1" id="addModalBtn" data-id="' . $row['coil_grade_id'] . '" data-type="edit"><i class="ti ti-pencil fs-7"></i></a>';
                }
            }
    
            $data[] = $rowData;
            $no++;
        }
    
        echo json_encode(['data' => $data]);
        exit;
    }

    mysqli_close($conn);
}
?>
