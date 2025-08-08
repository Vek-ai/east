<?php
require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'product_line';
$test_table = 'product_line_excel';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
        $product_line = mysqli_real_escape_string($conn, $_POST['product_line']);
        $line_abreviations = mysqli_real_escape_string($conn, $_POST['line_abreviations']);
        
        $product_category_array = $_POST['product_category'] ?? [];
        $product_category = mysqli_real_escape_string($conn, json_encode($product_category_array));

        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        $multiplier = mysqli_real_escape_string($conn, floatval($_POST['multiplier'] ?? 0.00));

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_line WHERE product_line_id = '$product_line_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE product_line SET product_line = '$product_line', line_abreviations = '$line_abreviations', product_category = '$product_category', notes = '$notes', multiplier = '$multiplier', last_edit = NOW(), edited_by = '$userid'  WHERE product_line_id = '$product_line_id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "Product line updated successfully.";
            } else {
                echo "Error updating product line: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO product_line (product_line, line_abreviations, product_category, notes, multiplier, added_date, added_by) VALUES ('$product_line', '$line_abreviations', '$product_category', '$notes', '$multiplier', NOW(), '$userid')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "New product line added successfully.";
            } else {
                echo "Error adding product line: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_line SET status = '$new_status' WHERE product_line_id = '$product_line_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_line') {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
        $query = "UPDATE product_line SET hidden='1' WHERE product_line_id='$product_line_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_modal_content') {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM product_line WHERE product_line_id = '$product_line_id'";
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
                <label class="form-label">Product line</label>
                <input type="text" id="product_line" name="product_line" class="form-control"  value="<?= $row['product_line'] ?? '' ?>"/>
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
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Line Abreviations</label>
                <input type="text" id="line_abreviations" name="line_abreviations" class="form-control" value="<?= $row['line_abreviations'] ?? '' ?>" />
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Multiplier</label>
                <input type="text" id="multiplier" name="multiplier" class="form-control" value="<?= $row['multiplier'] ?? '' ?>" />
            </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="5"><?= $row['notes'] ?? '' ?></textarea>
        </div>

            <input type="hidden" id="product_line_id" name="product_line_id" class="form-control"  value="<?= $product_line_id ?>"/>
        <?php
    }

    if ($action == "download_excel") {
        $product_category = mysqli_real_escape_string($conn, $_REQUEST['category'] ?? '');
        $category_name = strtoupper(getProductCategoryName($product_category));

        $includedColumns = array();
        $column_txt = '*';

        $includedColumns = [ 
            'product_line_id',
            'product_category',
            'product_line',
            'line_abreviations',
            'notes',
            'multiplier'
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
            echo $sql;
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
                'product_line_id',
                'product_category',
                'product_line',
                'line_abreviations',
                'notes',
                'multiplier'
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
    
    if ($action === 'fetch_table') {
        $query = "SELECT * FROM product_line WHERE hidden = 0";
        $result = mysqli_query($conn, $query);
    
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $no = $row['product_line_id'];
            $product_line = $row['product_line'];
            $line_abreviations = $row['line_abreviations'];
            
            $category_ids = json_decode($row['product_category'] ?? '', true);
            $category_ids = is_array($category_ids) ? $category_ids : [];
            $category_name_str = implode(', ', array_unique(array_map('getProductCategoryName', $category_ids)));

            $multiplier = $row['multiplier'];
            $notes = $row['notes'];
    
            $last_edit = !empty($row['last_edit']) ? (new DateTime($row['last_edit']))->format('m-d-Y') : '';
    
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
    
            $action_html = $row['status'] == '0'
                ? "<a href='javascript:void(0)' class='py-1 text-dark hideLine' title='Archive' data-id='$no' data-row='$no' style='border-radius: 10%;'>
                        <i class='text-danger ti ti-trash fs-7'></i>
                   </a>"
                : "<a href='javascript:void(0)' id='addModalBtn' title='Edit' class='d-flex align-items-center justify-content-center text-decoration-none' data-id='$no' data-type='edit'>
                        <i class='ti ti-pencil fs-7'></i>
                   </a>";
    
            $data[] = [
                'product_line' => $product_line,
                'line_abreviations' => $line_abreviations,
                'product_category_name' => $category_name_str,
                'multiplier' => $multiplier,
                'notes' => $notes,
                'last_edit' => "Last Edited $last_edit by $last_user_name",
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
