<?php
session_start();
require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'product_grade';
$test_table = 'product_grade_excel';

$permission = $_SESSION['permission'];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_grade_id = mysqli_real_escape_string($conn, $_POST['product_grade_id']);
        $product_grade = mysqli_real_escape_string($conn, $_POST['product_grade']);
        $grade_abbreviations = mysqli_real_escape_string($conn, $_POST['grade_abbreviations']);

        $product_category = mysqli_real_escape_string($conn, json_encode(array_map('intval', $_POST['product_category'] ?? [])));

        $defect_code = mysqli_real_escape_string($conn, $_POST['defect_code']);
        $defect_description = mysqli_real_escape_string($conn, $_POST['defect_description']);
        $multiplier = floatval(mysqli_real_escape_string($conn, $_POST['multiplier']));
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_grade WHERE product_grade_id = '$product_grade_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE product_grade SET product_grade = '$product_grade', grade_abbreviations = '$grade_abbreviations', product_category = '$product_category', multiplier = '$multiplier', notes = '$notes', defect_code = '$defect_code', defect_description = '$defect_description', last_edit = NOW(), edited_by = '$userid'  WHERE product_grade_id = '$product_grade_id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "update-success";
            } else {
                echo "Error updating product grade: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO product_grade (product_grade, grade_abbreviations, product_category, multiplier, notes, defect_code, defect_description, added_date, added_by) VALUES ('$product_grade', '$grade_abbreviations', '$product_category', '$multiplier', '$notes', '$defect_code', '$defect_description', NOW(), '$userid')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "add-success";
            } else {
                echo "Error adding product grade: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_grade_id = mysqli_real_escape_string($conn, $_POST['product_grade_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_grade SET status = '$new_status' WHERE product_grade_id = '$product_grade_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_grade') {
        $product_grade_id = mysqli_real_escape_string($conn, $_POST['product_grade_id']);
        $query = "UPDATE product_grade SET hidden='1' WHERE product_grade_id='$product_grade_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_modal_content') {
        $product_grade_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM product_grade WHERE product_grade_id = '$product_grade_id'";
        $result = mysqli_query($conn, $query);
        $selected_categories = [];
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);

            $selected_categories = json_decode($row['product_category'], true);
            if (!is_array($selected_categories)) {
                if ($selected_categories !== null) {
                    $selected_categories = [$selected_categories];
                } else {
                    $selected_categories = [];
                }
            }
            $selected_categories = array_map('intval', $selected_categories);
        }
        ?>
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Product grade</label>
                        <input type="text" id="product_grade" name="product_grade" class="form-control"  value="<?= $row['product_grade'] ?? '' ?>"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Product Category</label>
                        <select id="product_category" class="form-control select2" name="product_category[]" multiple required>
                        <?php
                        $query_roles = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                        $result_roles = mysqli_query($conn, $query_roles);
                        while ($row_product_category = mysqli_fetch_array($result_roles)) {
                            $cat_id = intval($row_product_category['product_category_id']);
                            $selected = in_array($cat_id, $selected_categories) ? 'selected' : '';
                            ?>
                            <option value="<?= $cat_id ?>" <?= $selected ?>>
                                <?= htmlspecialchars($row_product_category['product_category']) ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Grade Abreviations</label>
                        <input type="text" id="grade_abbreviations" name="grade_abbreviations" class="form-control" value="<?= $row['grade_abbreviations'] ?? '' ?>" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Multiplier</label>
                        <input type="text" id="multiplier" name="multiplier" class="form-control" value="<?= $row['multiplier'] ?? '' ?>" />
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="5"><?= $row['notes'] ?? '' ?></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Defect Code</label>
                        <input type="text" id="defect_code" name="defect_code" class="form-control" value="<?= $row['defect_code'] ?? '' ?>" />
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Defect Description</label>
                        <textarea class="form-control" id="defect_description" name="defect_description" rows="5"><?= $row['defect_description'] ?? '' ?></textarea>
                    </div>
                </div>
            </div>

            <input type="hidden" id="product_grade_id" name="product_grade_id" class="form-control"  value="<?= $product_grade_id ?>"/>
        <?php
    }

    if ($action == "download_excel") {
        $product_category = mysqli_real_escape_string($conn, $_REQUEST['category'] ?? '');
        $category_name = strtoupper(getProductCategoryName($product_category));

        $includedColumns = array();
        $column_txt = '*';

        $includedColumns = [ 
            'product_grade_id',
            'product_category',
            'product_grade',
            'grade_abbreviations',
            'notes',
            'multiplier',
            'defect_code',
            'defect_description'
        ];

        $column_txt = implode(', ', $includedColumns);

        $sql = "SELECT " . $column_txt . " FROM $table WHERE hidden = '0' AND status = '1'";
        if (!empty($product_category)) {
            $sql .= " AND product_category = '$product_category'";
        }
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

        $filename = "$category_name $name.xlsx";
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
                'product_grade_id',
                'product_category',
                'product_grade',
                'grade_abbreviations',
                'notes',
                'multiplier',
                'defect_code',
                'defect_description'
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
    
    if ($action == "download_classifications") {
        $classification = mysqli_real_escape_string($conn, $_REQUEST['class'] ?? '');

        $classifications = [
            'category' => [
                'columns' => ['product_category_id', 'product_category'],
                'table' => 'product_category',
                'where' => "status = '1'"
            ]
        ];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $selectedClassifications = empty($classification) ? array_keys($classifications) : [$classification];

        foreach ($selectedClassifications as $class) {
            if (!isset($classifications[$class])) {
                continue;
            }

            $includedColumns = $classifications[$class]['columns'];
            $table = $classifications[$class]['table'];
            $where = $classifications[$class]['where'];
            $column_txt = implode(', ', $includedColumns);
            $sql = "SELECT $column_txt FROM $table WHERE $where";
            $result = $conn->query($sql);

            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(ucwords($class));

            $row = 1;
            foreach ($includedColumns as $index => $column) {
                $header = ucwords(str_replace('_', ' ', $column));
                $columnLetter = chr(65 + $index);
                $sheet->setCellValue($columnLetter . $row, $header);
            }

            $row = 2;
            while ($data = $result->fetch_assoc()) {
                foreach ($includedColumns as $index => $column) {
                    $columnLetter = chr(65 + $index);

                    $value = $data[$column] ?? '';
                        
                    $sheet->setCellValue($columnLetter . $row, $value);
                }
                $row++;
            }
        }

        if(empty($classification)){
            $classification = 'Classifications';
        }else{
            $classification = ucwords($classification);
        }

        $filename = "$classification Classifications.xlsx";
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

    if ($action == 'fetch_table') {
        $query = "SELECT * FROM product_grade WHERE hidden = 0";
        $result = mysqli_query($conn, $query);
    
        $data = [];
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            $rowData = [];
    
            $rowData['product_grade'] = '<span class="product' . $no . ($row['status'] == '0' ? ' emphasize-strike' : '') . '">' . $row['product_grade'] . '</span>';
            $rowData['grade_abbreviations'] = $row['grade_abbreviations'];
            $rowData['multiplier'] = $row['multiplier'];

            $category_ids = json_decode($row['product_category'], true);
            $category_ids = is_array($category_ids) ? $category_ids : [];
            $rowData['product_category_name'] = implode(', ', array_unique(array_map('getProductCategoryName', $category_ids)));
            $rowData['notes'] = $row['notes'];
            
            $last_edit = '';
            if (!empty($row['last_edit'])) {
                $date = new DateTime($row['last_edit']);
                $last_edit = $date->format('m-d-Y');
            }
    
            $user_id = $row['edited_by'] != 0 ? $row['edited_by'] : $row['added_by'];
            $last_user_name = $user_id ? get_name($user_id) : '';
            $rowData['details'] = "Last Edited $last_edit by $last_user_name";
    
            $status = $row['status'];
            $rowData['status_html'] = '<a href="javascript:void(0)" class="changeStatus" data-no="' . $no . '" data-id="' . $row['product_grade_id'] . '" data-status="' . $status . '"><div id="status-alert' . $no . '" class="alert ' . ($status == '0' ? 'alert-danger bg-danger' : 'alert-success bg-success') . ' text-white border-0 text-center py-1 px-2 my-0" style="border-radius: 5%;">' . ($status == '0' ? 'Inactive' : 'Active') . '</div></a>';
    
            $rowData['action_html'] = '';
            if ($permission === 'edit') {
                if ($status == '0') {
                    $rowData['action_html'] = '<a href="javascript:void(0)" class="text-decoration-none py-1 text-dark hideProductGrade" data-id="' . $row['product_grade_id'] . '" data-row="' . $no . '"><i class="text-danger ti ti-trash fs-7"></i></a>';
                } else {
                    $rowData['action_html'] = '<a href="javascript:void(0)" class="text-decoration-none py-1" id="addModalBtn" data-id="' . $row['product_grade_id'] . '" data-type="edit"><i class="ti ti-pencil fs-7"></i></a>';
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
