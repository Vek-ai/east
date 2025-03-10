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

function indexToColumnLetter($index) {
    $letter = '';
    
    while ($index >= 0) {
        $letter = chr($index % 26 + 65) . $letter;
        $index = floor($index / 26) - 1;
    }
    
    return $letter;
}

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $color_name = mysqli_real_escape_string($conn, $_POST['color_name']);
        $color_code = mysqli_real_escape_string($conn, $_POST['color_code']);
        $color_group = mysqli_real_escape_string($conn, $_POST['color_group']);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
        $provider_id = mysqli_real_escape_string($conn, $_POST['provider']);
        $ekm_color_code = mysqli_real_escape_string($conn, $_POST['ekm_color_code']);
        $ekm_color_no = mysqli_real_escape_string($conn, $_POST['ekm_color_no']);
        $ekm_paint_code = mysqli_real_escape_string($conn, $_POST['ekm_paint_code'] ?? '');
        $color_abbreviation = mysqli_real_escape_string($conn, $_POST['color_abbreviation']);
        $stock_availability = mysqli_real_escape_string($conn, $_POST['stock_availability']);
        $multiplier_category = mysqli_real_escape_string($conn, $_POST['multiplier_category'] ?? '');

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM paint_colors WHERE color_id = '$color_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE paint_colors SET color_name = '$color_name', color_code = '$color_code', ekm_color_no = '$ekm_color_no', ekm_paint_code = '$ekm_paint_code', color_group = '$color_group', product_category = '$product_category', provider_id = '$provider_id', last_edit = NOW(), edited_by = '$userid', ekm_color_code = '$ekm_color_code', color_abbreviation = '$color_abbreviation', stock_availability = '$stock_availability', multiplier_category = '$multiplier_category'  WHERE color_id = '$color_id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "Paint color updated successfully.";
            } else {
                echo "Error updating paint color: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO paint_colors (color_name, color_code, ekm_color_no, ekm_paint_code, color_group, product_category, provider_id, added_date, added_by, ekm_color_code, color_abbreviation, stock_availability, multiplier_category) VALUES ('$color_name', '$color_code', '$ekm_color_no', '$ekm_paint_code', '$color_group', '$product_category', '$provider_id', NOW(), '$userid', '$ekm_color_code', '$color_abbreviation', '$stock_availability', '$multiplier_category')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "New paint color added successfully.";
            } else {
                echo "Error adding paint color: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE paint_colors SET color_status = '$new_status' WHERE color_id = '$color_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_paint_color') {
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $query = "UPDATE paint_colors SET hidden='1' WHERE color_id='$color_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == "fetch_uploaded_modal") {
        $table = "test_color";
        
        $sql = "SELECT * FROM $table";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $columns = array_keys($row);
            $result->data_seek(0);

            $includedColumns = [ 
                'color_id',
                'color_name',
                'color_code',
                'ekm_color_code',
                'ekm_color_no',
                'product_category',
                'color_group',
                'provider_id',
                'color_abbreviation',
                'stock_availability'
            ];

            $columns = array_filter($columns, fn($col) => in_array($col, $includedColumns));

            

            $columnsWithData = [];
            while ($row = $result->fetch_assoc()) {
                foreach ($columns as $column) {
                    if (isset($row[$column]) && trim($row[$column]) !== '') {
                        $columnsWithData[$column] = true;
                    }
                }
            }

            $result->data_seek(0);
            ?>
            
            <div class="card card-body shadow">
                <form id="tableForm">
                    <div style="overflow-x: auto; overflow-y: auto; max-height: 80vh; max-width: 100%;">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <?php
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $formattedColumn = ucwords(str_replace('_', ' ', $column));
                                            echo "<th class='fs-4'>" . $formattedColumn . "</th>";
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                while ($row = $result->fetch_assoc()) {
                                    $color_id = $row['color_id'];
                                    echo '<tr>';
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $value = $row[$column] ?? '';
                                            echo "<td contenteditable='true' class='table_data' data-header-name='".$column."' data-id='".$color_id."'>$value</td>";
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

    if ($action == "upload_excel") {
        if (isset($_FILES['excel_file'])) {
            $fileTmpPath = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $table_test = 'test_color';

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
                if($col == '$ Per square inch'){
                    $dbColumn = 'cost_per_sq_in';
                }else{
                    $dbColumn = strtolower(str_replace(' ', '_', $col));
                }
                $dbColumns[] = $dbColumn;
                $columnMapping[$dbColumn] = $col;
            }

            $truncateSql = "TRUNCATE TABLE $table_test";
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

                $sql = "INSERT INTO $table_test ($columnNames) VALUES ('$columnValues')";
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

    if ($action == "save_table") {
        $table = "paint_colors";
        
        $selectSql = "SELECT * FROM test_color";
        $result = $conn->query($selectSql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $color_id = trim($row['color_id'] ?? ''); 

                unset($row['id']);

                if (!empty($color_id)) {
                    $checkSql = "SELECT COUNT(*) as count FROM $table WHERE color_id = '$color_id'";
                    $checkResult = $conn->query($checkSql);
                    $exists = $checkResult->fetch_assoc()['count'] > 0;

                    if ($exists) {
                        $updateFields = [];
                        foreach ($row as $column => $value) {
                            if ($column !== 'color_id') {
                                $updateFields[] = "$column = '$value'";
                            }
                        }
                        $updateSql = "UPDATE $table SET " . implode(", ", $updateFields) . " WHERE color_id = '$color_id'";
                        $conn->query($updateSql);
                        continue;
                    }
                }

                $columns = implode(", ", array_keys($row));
                $values = implode("', '", array_values($row));
                $insertSql = "INSERT INTO $table ($columns) VALUES ('$values')";
                $conn->query($insertSql);
            }

            echo "Data has been successfully saved";

            $truncateSql = "TRUNCATE TABLE test_color";
            if ($conn->query($truncateSql) !== TRUE) {
                echo " but failed to clear test color table: " . $conn->error;
            }
        } else {
            echo "No data found in test color table.";
        }
    }

    if ($action == "download_excel") {
        $product_category = mysqli_real_escape_string($conn, $_REQUEST['category'] ?? '');
        $category_name = strtoupper(getProductCategoryName($product_category));

        $includedColumns = array();
        $column_txt = '*';

        $includedColumns = [ 
            'color_id',
            'color_name',
            'color_code',
            'ekm_color_code',
            'ekm_color_no',
            'product_category',
            'color_group',
            'provider_id',
            'color_abbreviation',
            'stock_availability'
        ];

        $column_txt = implode(', ', $includedColumns);

        $sql = "SELECT " . $column_txt . " FROM paint_colors WHERE hidden = '0' AND color_status = '1'";
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

        $filename = "$category_name COLORS.xlsx";
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

    if ($action == "download_classifications") {
        $classification = mysqli_real_escape_string($conn, $_REQUEST['class'] ?? '');

        $classifications = [
            'category' => [
                'columns' => ['product_category_id', 'product_category'],
                'table' => 'product_category',
                'where' => "status = '1'"
            ],
            'color_group' => [
                'columns' => ['color_group_name_id', 'color_group_name'],
                'table' => 'color_group_name',
                'where' => "status = '1'"
            ],
            'paint_providers' => [
                'columns' => ['provider_id', 'provider_name'],
                'table' => 'paint_providers',
                'where' => "provider_status = '1'"
            ],
            'availability' => [
                'columns' => ['product_availability_id', 'product_availability'],
                'table' => 'product_availability',
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
            $classification = 'Color Classifications';
        }else{
            $classification = ucwords($classification);
        }

        $filename = "$classification Color Classifications.xlsx";
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

    if ($action == "update_color_data") {
        $column_name = $_POST['header_name'];
        $new_value = $_POST['new_value'];
        $color_id = $_POST['id'];
        
        if (empty($column_name) || empty($color_id)) {
            exit;
        }
        
        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $color_id = mysqli_real_escape_string($conn, $color_id);
        
        $sql = "UPDATE test_color SET `$column_name` = '$new_value' WHERE color_id = '$color_id'";

        if ($conn->query($sql) === TRUE) {
            echo 'Success';
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
    }

    mysqli_close($conn);
}
?>
