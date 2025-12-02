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

$table = 'flat_sheet_width';
$test_table = 'flat_sheet_width_excel';

$trim_id  = 4;

function clean($v) {
    global $conn;
    return mysqli_real_escape_string($conn, $v ?? '');
}

$includedColumns = [
    'id',
    'product_category',
    'product_line',
    'product_type',
    'trim_id',
    'abbreviation',
    'is_customer_special',
    'customer_id',
    'width',
    'hems',
    'bends'
];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id                 = clean($_POST['id']);
        $product_category   = clean($_POST['product_category']);
        $product_line       = clean($_POST['product_line']);
        $product_type       = clean($_POST['product_type']);
        $trim_id            = clean($_POST['trim_id']);
        $abbreviation       = clean($_POST['abbreviation']);
        $is_customer_special= isset($_POST['is_customer_special']) ? 1 : 0;
        $customer_id        = clean($_POST['customer_id']);
        $width              = clean($_POST['width']);
        $hems               = clean($_POST['hems']);
        $bends              = clean($_POST['bends']);
        $userid             = clean($_POST['userid']);

        $checkQuery = "SELECT id FROM flat_sheet_width WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if ($result && mysqli_num_rows($result) > 0) {

            $updateQuery = "
                UPDATE flat_sheet_width SET
                    product_category    = '$product_category',
                    product_line        = '$product_line',
                    product_type        = '$product_type',
                    trim_id             = '$trim_id',
                    abbreviation        = '$abbreviation',
                    is_customer_special = '$is_customer_special',
                    customer_id         = '$customer_id',
                    width               = '$width',
                    hems                = '$hems',
                    bends               = '$bends',
                    edited_by           = '$userid',
                    last_edit           = NOW()
                WHERE id = '$id'
            ";

            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating: " . mysqli_error($conn);
            }

        } else {
            $insertQuery = "
                INSERT INTO flat_sheet_width 
                    (product_category, product_line, product_type, trim_id, abbreviation, is_customer_special,
                    customer_id, width, hems, bends, added_by, last_edit)
                VALUES
                    ('$product_category', '$product_line', '$product_type', '$trim_id', '$abbreviation', '$is_customer_special',
                    '$customer_id', '$width', '$hems', '$bends', '$userid', NOW())
            ";

            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding: " . mysqli_error($conn);
            }
        }
    }

    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE flat_sheet_width SET status = '$new_status' WHERE id = '$id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_fs_width') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "UPDATE flat_sheet_width SET hidden='1' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    if ($action == 'fetch_modal_content') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM flat_sheet_width WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        $is_customer_special = 0;
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            $is_customer_special = floatval($row['is_customer_special'] ?? 0);
        }
        ?>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Trim Identifiers</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Product Category</label>
                        <div class="mb-3">
                            <h4>Trim</h4>
                            <input type="hidden" name="product_category" id="select-category" value="<?= $trim_id ?>"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Product Line</label>
                        <div class="mb-3">
                            <select class="form-control select2 search-category" id="select-line" name="product_line">
                                <option value="" >All Product Lines</option>
                                <optgroup label="Product Type">
                                    <?php
                                    $query_line = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                                    $result_line = mysqli_query($conn, $query_line);
                                    while ($row_line = mysqli_fetch_array($result_line)) {
                                        $selected = (($row['product_line'] ?? '') == $row_line['product_line_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_line['product_line_id'] ?>" data-category="<?= $row_line['product_category'] ?>" <?= $selected ?>><?= $row_line['product_line'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Product Type</label>
                        <div class="mb-3">
                            <select class="form-control select2 search-category" id="select-type" name="product_type">
                                <option value="" >All Product Types</option>
                                <optgroup label="Product Type">
                                    <?php
                                    $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                                    $result_type = mysqli_query($conn, $query_type);
                                    while ($row_type = mysqli_fetch_array($result_type)) {
                                        $selected = (($row['product_type'] ?? '') == $row_type['product_type_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_type['product_type_id'] ?>" data-category="<?= $row_type['product_category'] ?>" <?= $selected ?>><?= $row_type['product_type'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

         <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Trim Specifications</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-3 pt-3">
                        <label class="form-label">Trim Product</label>
                        <div class="mb-3">
                            <select class="form-control select2" id="select-trim-id" name="trim_id">
                                <option value="" >All Trim Products</option>
                                <optgroup label="Trim Products">
                                    <?php
                                    $query_prod = "SELECT * FROM product WHERE hidden = '0' AND status = '1' AND product_category = '$trim_id' ORDER BY `product_item` ASC";
                                    $result_prod = mysqli_query($conn, $query_prod);
                                    while ($row_prod = mysqli_fetch_array($result_prod)) {
                                        $selected = (($row['trim_id'] ?? '') == $row_prod['product_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_prod['product_id'] ?>" <?= $selected ?>><?= $row_prod['product_item'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 pt-3">
                        <label class="form-label">Abbreviation</label>
                        <div class="mb-3">
                            <input type="text" id="abbreviation" name="abbreviation" class="form-control" value="<?= $row['abbreviation'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-3 mb-3 pt-3 text-center">
                        <label class="form-check-label fw-bold d-block mb-1" for="is_customer_special">
                            Customer SPCL Trim
                        </label>
                        <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input" type="checkbox" id="is_customer_special" name="is_customer_special" <?= ($is_customer_special > 0) ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <div class="col-md-3 pt-3">
                        <label class="form-label">Customer</label>
                        <div class="mb-3">
                            <select class="form-control select2" id="select-customer" name="customer_id">
                                <option value="" >All Customers</option>
                                <optgroup label="Customers">
                                    <?php
                                    $query_cust = "SELECT customer_id FROM customer WHERE hidden = '0' AND status = '1' ORDER BY `customer_first_name` ASC";
                                    $result_cust = mysqli_query($conn, $query_cust);
                                    while ($row_cust = mysqli_fetch_array($result_cust)) {
                                        $customer_name = get_customer_name($row_cust['customer_id']);
                                        $selected = (($row['customer_id'] ?? '') == $row_cust['customer_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_cust['customer_id'] ?>" <?= $selected ?>><?= $customer_name ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 pt-3">
                        <label class="form-label">Flat Sheet Width</label>
                        <div class="mb-3">
                            <input type="number" step="0.000001" id="width" name="width" class="form-control" value="<?= $row['width'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3 pt-3">
                        <label class="form-label">Hems</label>
                        <div class="mb-3">
                            <input type="number" step="1" id="hems" name="hems" class="form-control" value="<?= $row['hems'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3 pt-3">
                        <label class="form-label">Bends</label>
                        <div class="mb-3">
                            <input type="number" step="1" id="bends" name="bends" class="form-control" value="<?= $row['bends'] ?? '' ?>"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="id" name="id" class="form-control"  value="<?= $id ?>"/>
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
        $query = "SELECT * FROM flat_sheet_width WHERE hidden = 0";
        $result = mysqli_query($conn, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $no = $row['id'];
            $product_type_id = $row['product_type']; 
            $trim_type = $row['is_customer_special'];
            $customer_id = $row['customer_id'];

            $product_type = getProductTypeName($product_type_id);
            $trim_name = getProductName($row['trim_id']);
            $customer_name = get_customer_name($customer_id);

            $flat_sheet_width = number_format((float)$row['width'], 2);
            $hems = $row['hems'] ?? '';
            $bends = $row['bends'] ?? '';

            if ($row['edited_by'] != "0") {
                $last_user_name = get_name($row['edited_by']);
            } elseif ($row['added_by'] != "0") {
                $last_user_name = get_name($row['added_by']);
            } else {
                $last_user_name = "";
            }

            $last_edit = !empty($row['last_edit'])
                ? (new DateTime($row['last_edit']))->format('m-d-Y')
                : "";

            $status_html = $row['status'] == '0'
                ? "<a href='javascript:void(0)' class='changeStatus' data-no='$no' data-id='$no' data-status='0'>
                        <div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;'>Inactive</div>
                </a>"
                : "<a href='javascript:void(0)' class='changeStatus' data-no='$no' data-id='$no' data-status='1'>
                        <div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;'>Active</div>
                </a>";

            if ($permission === 'edit') {
                $action_html = $row['status'] == '0'
                    ? "<a href='javascript:void(0)' class='py-1 text-dark hideFSWidth' title='Archive' data-id='$no' data-row='$no'>
                            <i class='text-danger ti ti-trash fs-7'></i>
                    </a>"
                    : "<a href='javascript:void(0)' id='addModalBtn' title='Edit'
                        class='d-flex align-items-center justify-content-center text-decoration-none'
                        data-id='$no' data-type='edit'>
                            <i class='ti ti-pencil fs-7'></i>
                    </a>";
            } else {
                $action_html = '';
            }

            $data[] = [
                'product_type'      => $product_type,
                'trim_name'         => $trim_name,
                'is_special_trim'   => $trim_type,
                'customer_name'     => $customer_name,
                'flat_sheet_width'  => $flat_sheet_width,
                'hems'              => $hems,
                'bends'             => $bends,
                'status_html'       => $status_html,
                'action_html'       => $action_html,

                'product_type_id'   => $product_type_id,
                'trim_type'         => $trim_type,
                'customer_id'       => $customer_id
            ];
        }

        echo json_encode(['data' => $data]);
        exit;
    }

    mysqli_close($conn);
}
?>
