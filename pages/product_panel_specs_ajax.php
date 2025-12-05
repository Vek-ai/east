<?php
session_start();

$permission = $_SESSION['permission'];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'product_panel_spec';
$test_table = 'product_panel_spec_excel';

$panel_id  = 4;

function clean($v) {
    global $conn;
    return mysqli_real_escape_string($conn, $v ?? '');
}

$includedColumns = [
    'id',
    'product_id',
    'exposed_fastener',
    'concealed_fastener'
];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id                 = clean($_POST['id'] ?? '');
        $product_id         = clean($_POST['product_id'] ?? '');
        $exposed_fastener   = ($_POST['fastener_type'] ?? '') === 'exposed' ? 1 : 0;
        $concealed_fastener = ($_POST['fastener_type'] ?? '') === 'concealed' ? 1 : 0;
        $userid             = $_SESSION['userid'];

        $checkQuery = "SELECT id FROM product_panel_spec WHERE product_id = '$product_id' LIMIT 1";
        $result = mysqli_query($conn, $checkQuery);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $existing_id = $row['id'];

            $updateQuery = "
                UPDATE product_panel_spec SET
                    exposed_fastener    = '$exposed_fastener',
                    concealed_fastener  = '$concealed_fastener',
                    edited_by           = '$userid',
                    last_edit           = NOW()
                WHERE id = '$existing_id'
            ";

            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating: " . mysqli_error($conn);
            }

        } else {
            $insertQuery = "
                INSERT INTO product_panel_spec 
                    (product_id, exposed_fastener, concealed_fastener, added_by, last_edit)
                VALUES
                    ('$product_id', '$exposed_fastener', '$concealed_fastener', '$userid', NOW())
            ";

            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding: " . mysqli_error($conn);
            }
        }
    }

    
    if ($action == 'fetch_modal_content') {

        $product_id = intval($_POST['id']);
        $row = [];

        $query = "SELECT * FROM product_panel_spec WHERE product_id = '$product_id' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        }
    ?>
    <div class="card shadow-sm rounded-3 mb-3">
        <div class="card-header bg-light border-bottom">
            <h5 class="mb-0 fw-bold">Metal Panel Specifications</h5>
        </div>

        <div class="card-body border rounded p-3">
            <div class="row">

                <div class="col-md-4 pt-3">
                    <label class="form-label">Panel Product </label>
                    <div class="mb-3">
                        <select class="form-control select2" id="select-trim-id" name="panel_id">
                            <option value="">All Panel Products</option>

                            <optgroup label="Panel Products">
                                <?php
                                $query_prod = "
                                    SELECT * FROM product 
                                    WHERE hidden = 0 AND status = 1 AND product_category = '$panel_id'
                                    ORDER BY product_item ASC
                                ";
                                $result_prod = mysqli_query($conn, $query_prod);

                                while ($p = mysqli_fetch_assoc($result_prod)) {
                                    $sel = ($p['product_id'] == $product_id) ? 'selected' : '';
                                ?>
                                    <option value="<?= $p['product_id'] ?>" <?= $sel ?>>
                                        <?= $p['product_item'] ?>
                                    </option>
                                <?php } ?>
                            </optgroup>

                        </select>
                    </div>
                </div>

                <div class="row mb-3 pt-3 text-center">

                    <div class="col-6">
                        <label class="form-check-label fw-bold d-block mb-1">Exposed Fastener</label>
                        <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input" type="radio"
                                name="fastener_type"
                                value="exposed"
                                <?= (!empty($row) && $row['exposed_fastener'] == 1) ? 'checked' : '' ?> >
                        </div>
                    </div>

                    <div class="col-6">
                        <label class="form-check-label fw-bold d-block mb-1">Concealed Fastener</label>
                        <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input" type="radio"
                                name="fastener_type"
                                value="concealed"
                                <?= (!empty($row) && $row['concealed_fastener'] == 1) ? 'checked' : '' ?> >
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <input type="hidden" name="product_id" value="<?= $product_id ?>">
    <?php
    }


    
    if ($action == 'fetch_view_content') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        ?>
            <h4 class="card-title d-flex justify-content-center align-items-center">Trim profile details here.</h4>
        <?php
    }

    if ($action == "download_excel") {
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
    
    if ($action == "download_classifications") {
        $classification = mysqli_real_escape_string($conn, $_REQUEST['class'] ?? '');

        $classifications = [
            'category' => [
                'columns' => ['product_category_id', 'product_category'],
                'table' => 'product_category',
                'where' => "status = '1'"
            ],
            'line' => [
                'columns' => ['product_line_id', 'product_line'],
                'table' => 'product_line',
                'where' => "status = '1'"
            ],
            'type' => [
                'columns' => ['product_type_id', 'product_type'],
                'table' => 'product_type',
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
        $query = "  SELECT 
                        p.product_id as product_id,
                        p.product_item,
                        ps.concealed_fastener,
                        ps.exposed_fastener
                    FROM product p
                    LEFT JOIN product_panel_spec ps
                        ON p.product_id = ps.product_id
                    WHERE 
                        p.product_category = '$panel_id' AND
                        p.status = 1 AND 
                        p.hidden = 0
                    ORDER BY product_item ASC
                    ";
        $result = mysqli_query($conn, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $product_id = $row['product_id']; 

            $metal_panel_name = $row['product_item'];

            $concealed_fastener = intval($row['concealed_fastener']) > 0 ? '<i class="fa fa-check text-success fs-8"></i>' : '';
            $exposed_fastener = intval($row['exposed_fastener']) > 0 ? '<i class="fa fa-check text-success fs-8"></i>' : '';
        
            if ($permission === 'edit') {
                $action_html = "<a href='javascript:void(0)' id='addModalBtn' title='Edit'
                                    class='d-flex align-items-center justify-content-center text-decoration-none'
                                    data-id='$product_id' data-type='edit'>
                                        <i class='ti ti-eye fs-7'></i>
                                </a>";
            } else {
                $action_html = '';
            }

            $fastener = '';
            if(intval($row['concealed_fastener'])){
                $fastener = 'concealed';
            }

            if(intval($row['exposed_fastener'])){
                $fastener = 'exposed';
            }

            $data[] = [
                'metal_panel_name'      => $metal_panel_name,
                'exposed_fastener'      => $exposed_fastener,
                'concealed_fastener'    => $concealed_fastener,
                'action_html'           => $action_html,
                'fastener'              => $fastener,
                'is_exposed_fastener'   => intval($row['exposed_fastener'])
            ];
        }

        echo json_encode(['data' => $data]);
        exit;
    }

    mysqli_close($conn);
}
?>
