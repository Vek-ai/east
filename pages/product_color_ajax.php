<?php
require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'product_color';
$test_table = 'product_color_excel';
$main_primary_key = getPrimaryKey($table);

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $table = "product_color";

        $primaryKeyQuery = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'";
        $primaryKeyResult = mysqli_query($conn, $primaryKeyQuery);
        $primaryKeyRow = mysqli_fetch_assoc($primaryKeyResult);
        $primaryKey = $primaryKeyRow['Column_name'];

        if (!$primaryKey) {
            echo "Error: Unable to retrieve primary key for table $table";
            exit;
        }

        $primaryKeyValue = mysqli_real_escape_string($conn, $_POST[$primaryKey]);

        $fields = [];
        foreach ($_POST as $key => $value) {
            if ($key != $primaryKey) {
                if (is_array($value)) {
                    $allNumeric = array_reduce($value, function($carry, $item) {
                        return $carry && is_numeric($item);
                    }, true);

                    if ($allNumeric) {
                        $value = array_map('intval', $value);
                    }
                    $value = json_encode($value);
                }

                $fields[$key] = mysqli_real_escape_string($conn, $value);
            }
        }

        $checkQuery = "SELECT * FROM $table WHERE $primaryKey = '$primaryKeyValue'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE $table SET ";

            foreach ($fields as $column => $value) {
                $columnExists = mysqli_query($conn, "SHOW COLUMNS FROM $table LIKE '$column'");
                if (mysqli_num_rows($columnExists) > 0) {
                    $updateQuery .= "$column = '$value', ";
                }
            }

            $updateQuery = rtrim($updateQuery, ", ");
            $updateQuery .= " WHERE $primaryKey = '$primaryKeyValue'";

            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating product_color: " . mysqli_error($conn);
            }
        } else {
            $columns = [];
            $values = [];

            foreach ($fields as $column => $value) {
                $columnExists = mysqli_query($conn, "SHOW COLUMNS FROM $table LIKE '$column'");
                if (mysqli_num_rows($columnExists) > 0) {
                    $columns[] = $column;
                    $values[] = "'$value'";
                }
            }

            $columnsStr = implode(", ", $columns);
            $valuesStr = implode(", ", $values);

            $insertQuery = "INSERT INTO $table ($primaryKey, $columnsStr) VALUES ('$primaryKeyValue', $valuesStr)";

            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding product_color: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "fetch_modal_edit") {
        if (isset($_POST['id'])) {
            $id = intval($_POST['id']);
            $query = "SELECT * FROM product_color WHERE id = $id";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
            } else {
                $row = [];
            }
        } else {
            $row = [];
        }
        ?>
        <div class="card">
            <div class="card-body">
                <input type="hidden" id="form_id" name="id" class="form-control" value="<?= $row['id'] ?? '' ?>"/>

                <?php
                $selected_categories = !empty($row['product_category']) ? json_decode($row['product_category'], true) : [];
                $selected_grades     = !empty($row['grade']) ? json_decode($row['grade'], true) : [];
                $selected_gauges     = !empty($row['gauge']) ? json_decode($row['gauge'], true) : [];
                $selected_profiles   = !empty($row['profile']) ? json_decode($row['profile'], true) : [];

                $selected_categories = is_array($selected_categories) ? $selected_categories : [];
                $selected_grades     = is_array($selected_grades) ? $selected_grades : [];
                $selected_gauges     = is_array($selected_gauges) ? $selected_gauges : [];
                $selected_profiles   = is_array($selected_profiles) ? $selected_profiles : [];
                ?>
                <div class="card shadow-sm rounded-3 mb-3">
                    <div class="card-header bg-light border-bottom">
                        <h5 class="mb-0 fw-bold">Color Group Identifiers</h5>
                    </div>
                    <div class="card-body border rounded p-3">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Color Group Name</label>
                                <input type="text" class="form-control" name="color_name" id="color_name" value="<?= $row['color_name'] ?? '' ?>">
                            </div>

                            <div class="col-md-4"></div>

                            <div class="col-md-4">
                                <label class="form-label">Product Category</label>
                                <div class="mb-3">
                                    <select id="product_category" class="form-control select2-edit" name="product_category[]" multiple>
                                        <?php
                                        $query_roles = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                        $result_roles = mysqli_query($conn, $query_roles);            
                                        while ($row_product_category = mysqli_fetch_array($result_roles)) {
                                            $selected = in_array($row_product_category['product_category_id'], $selected_categories) ? 'selected' : '';
                                            ?>
                                            <option value="<?= $row_product_category['product_category_id'] ?>"
                                                    data-category="<?= $row_product_category['product_category'] ?>"
                                                    data-filename="<?= $row_product_category['color_group_filename'] ?>"
                                                    <?= $selected ?>>
                                                <?= $row_product_category['product_category'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label">Product Grade</label>
                                    <a href="?page=product_grade" target="_blank" class="text-decoration-none">Edit</a>
                                </div>
                                <div class="mb-3">
                                    <select id="product_grade" class="form-control select2-edit add-category" name="grade[]" multiple>
                                        <?php
                                        $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY product_grade";
                                        $result_grade = mysqli_query($conn, $query_grade);
                                        while ($row_grade = mysqli_fetch_array($result_grade)) {
                                            $selected = in_array($row_grade['product_grade_id'], $selected_grades) ? 'selected' : '';
                                            ?>
                                            <option value="<?= $row_grade['product_grade_id'] ?>" 
                                                    data-category="<?= $row_grade['product_category'] ?>" 
                                                    <?= $selected ?>>
                                                <?= $row_grade['product_grade'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label">Product Gauge</label>
                                        <a href="?page=product_gauge" target="_blank" class="text-decoration-none">Edit</a>
                                    </div>
                                    <select id="gauge" class="form-control select2-edit" name="gauge[]" multiple>
                                        <?php
                                        $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1'";
                                        $result_gauge = mysqli_query($conn, $query_gauge);

                                        $existing_gauges = [];
                                        while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                            if (!in_array($row_gauge['product_gauge'], $existing_gauges)) {
                                                $existing_gauges[] = $row_gauge['product_gauge'];
                                                $selected = in_array($row_gauge['product_gauge_id'], $selected_gauges) ? 'selected' : '';
                                                ?>
                                                <option value="<?= htmlspecialchars($row_gauge['product_gauge_id']) ?>" 
                                                        data-multiplier="<?= htmlspecialchars($row_gauge['multiplier']) ?>" 
                                                        <?= $selected ?>>
                                                    <?= htmlspecialchars($row_gauge['product_gauge']) ?>
                                                </option>
                                            <?php }
                                        } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label">Product Profile</label>
                                        <a href="?page=profile_type" target="_blank" class="text-decoration-none">Edit</a>
                                    </div>
                                    <select id="profile" class="form-control select2-edit add-category" name="profile[]" multiple>
                                        <?php
                                        $query_profile_type = "SELECT * FROM profile_type WHERE hidden = '0' AND status = '1'";
                                        $result_profile_type = mysqli_query($conn, $query_profile_type);            
                                        while ($row_profile_type = mysqli_fetch_array($result_profile_type)) {
                                            $selected = in_array($row_profile_type['profile_type_id'], $selected_profiles) ? 'selected' : '';
                                            ?>
                                            <option value="<?= $row_profile_type['profile_type_id'] ?>" 
                                                    data-category="<?= $row_profile_type['product_category'] ?>"  
                                                    <?= $selected ?>>
                                                <?= $row_profile_type['profile_type'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm rounded-3 mb-3">
                    <div class="card-header bg-light border-bottom">
                        <h5 class="mb-0 fw-bold">Color Group Pricing</h5>
                    </div>
                    <div class="card-body border rounded p-3">
                        <div class="row">
                            <div class="col-md-4 mb-3 panel-fields" data-id="7">
                                <label class="form-label">Multiplier Value</label>
                                <input type="text" class="form-control" name="multiplier" id="multiplier" value="<?= $row['multiplier'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    if ($action == "download_excel") {
        $includedColumns = array();
        $column_txt = '*';

        $includedColumns = [ 
            'id',
            'product_category',
            'profile',
            'grade',
            'gauge',
            'multiplier'
        ];

        $column_txt = implode(', ', $includedColumns);

        $sql = "SELECT " . $column_txt . " FROM $table";
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
                'id',
                'product_category',
                'profile',
                'grade',
                'gauge',
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
            'product_system' => [
                'columns' => ['product_system_id', 'product_system'],
                'table' => 'product_system',
                'where' => "status = '1'"
            ],
            'product_gauge' => [
                'columns' => ['product_gauge_id', 'product_gauge'],
                'table' => 'product_gauge',
                'where' => "status = '1'"
            ],
            'product_coating' => [
                'columns' => ['product_coating_id', 'product_coating'],
                'table' => 'product_coating',
                'where' => "status = '1'"
            ],
            'color_group_name' => [
                'columns' => ['color_group_name_id', 'color_group_name'],
                'table' => 'color_group_name',
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
            $column_txt = implode(', ', array_map(fn($col) => "`$col`", $includedColumns));
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
            $classification = 'All';
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

    if ($action == "copy_color") {
        $id = intval($_POST['id']);
        $table = "product_color";

        $cols = [];
        $res = mysqli_query($conn, "SHOW COLUMNS FROM $table");
        while ($row = mysqli_fetch_assoc($res)) {
            if ($row['Key'] != 'PRI') {
                $cols[] = $row['Field'];
            }
        }
        $columns = implode(", ", $cols);
        $selects = [];
        foreach ($cols as $col) {
            if ($col == 'color_name') {
                $selects[] = "CONCAT('Copy - ', $col)";
            } else {
                $selects[] = $col;
            }
        }
        $select = implode(", ", $selects);

        $sql = "INSERT INTO $table ($columns) SELECT $select FROM $table WHERE id = $id";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            echo "success";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }

    if ($action == "delete_color") {
        $id = intval($_POST['id']);
        
        $table = "product_color";

        $sql = "DELETE FROM $table WHERE id = $id";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            echo "success_delete";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }

    
    mysqli_close($conn);
}
?>
