<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = "special_trim";
$product_excel = 'special_trim_excel';
//4 = TRIM
$trim_id = 4;
$category_id = 4;

$includedColumns = [
    'special_trim_id'   => 'Entry ID',
    'product_id'        => 'Product ID',
    'customer_id'       => 'Customer',
    'spec_trim_desc'    => 'Special Trim Description',
    'spec_trim_no'      => 'Special Trim #',
    'flat_sheet_width'  => 'Flat Sheed Width',
    'bends'             => 'Total Bends',
    'hems'              => 'Total Hems',
    'last_order'        => 'Last Ordered'
];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $special_trim_id = mysqli_real_escape_string($conn, $_POST['special_trim_id'] ?? '');
        $product_id      = mysqli_real_escape_string($conn, $_POST['product_id'] ?? '');
        $customer_id     = mysqli_real_escape_string($conn, $_POST['customer_id'] ?? '');
        $spec_trim_desc  = mysqli_real_escape_string($conn, $_POST['spec_trim_desc'] ?? '');
        $spec_trim_no    = mysqli_real_escape_string($conn, $_POST['spec_trim_no'] ?? '');
        $flat_sheet_width= mysqli_real_escape_string($conn, $_POST['flat_sheet_width'] ?? '');
        $hems            = mysqli_real_escape_string($conn, $_POST['hems'] ?? '');
        $bends           = mysqli_real_escape_string($conn, $_POST['bends'] ?? '');
        $userid          = intval($_POST['userid'] ?? 0);

        $exists = false;

        if (!empty($special_trim_id)) {
            $checkQuery = "SELECT special_trim_id FROM special_trim WHERE special_trim_id = '$special_trim_id' LIMIT 1";
            $res = mysqli_query($conn, $checkQuery);
            if ($res && mysqli_num_rows($res) > 0) {
                $exists = true;
            }
        }

        if ($exists) {
            $updateQuery = "
                UPDATE special_trim SET
                    customer_id       = '$customer_id',
                    product_id        = '$product_id',
                    spec_trim_desc    = '$spec_trim_desc',
                    spec_trim_no      = '$spec_trim_no',
                    flat_sheet_width  = '$flat_sheet_width',
                    hems              = '$hems',
                    bends             = '$bends',
                    edited_by         = $userid,
                    last_edit         = NOW()
                WHERE special_trim_id = '$special_trim_id'
                LIMIT 1
            ";
            if (!mysqli_query($conn, $updateQuery)) {
                echo 'Error updating product: ' . mysqli_error($conn);
                exit;
            }
            echo "success_update";
        } else {
            $insertQuery = "
                INSERT INTO special_trim (
                    customer_id, product_id, spec_trim_desc, spec_trim_no, flat_sheet_width, hems, bends,
                    added_by, edited_by, added_date, last_edit
                ) VALUES (
                    '$customer_id', '$product_id', '$spec_trim_desc', '$spec_trim_no', '$flat_sheet_width', '$hems', '$bends',
                    $userid, $userid, NOW(), NOW()
                )
            ";
            if (!mysqli_query($conn, $insertQuery)) {
                echo 'Error inserting product: ' . mysqli_error($conn);
                exit;
            }
            echo "success_add";
        }
    }

    if ($action == "fetch_modal") {
        $special_trim_id = mysqli_real_escape_string($conn, $_POST['id']);

        $checkQuery = "SELECT * FROM special_trim WHERE special_trim_id = '$special_trim_id'";
        $result = mysqli_query($conn, $checkQuery);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            //$product_id = $row['product_id'];

            $product = getProductDetails($product_id);
        }

        //lock to special trim product, 318 id
        $product_id = 318;
        ?>
        <input type="hidden" id="special_trim_id" name="special_trim_id" value="<?= $row['special_trim_id'] ?>" />
        <input type="hidden" id="product_id_fixed" name="product_id" value="<?= $product_id ?>" />

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <div class="mb-3">
                                <p id="product"><?= getColumnFromTable("product","product_item",$product_id) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Identifier</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Product Category</label>
                        <div class="mb-3">
                            <p id="product_category"><?= getColumnFromTable("product_category","product_category",$product['product_category']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Product Line</label>
                        <div class="mb-3">
                            <p id="product_line"><?= getColumnFromTable("product_line","product_line",$product['product_line']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Product Type</label>
                        <div class="mb-3">
                            <p id="product_type"><?= getColumnFromTable("product_type","product_type",$product['product_type']); ?></p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Product Profile</label>
                        <div class="mb-3">
                            <p id="profile_type"><?= getColumnFromTable("profile_type","profile_type",$product['profile']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Product Grade</label>
                        <div class="mb-3">
                            <p id="product_grade"><?= getColumnFromTable("product_grade","product_grade",$product['grade']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Product Gauge</label>
                        <div class="mb-3">
                            <p id="product_gauge"><?= getColumnFromTable("product_gauge","product_gauge",$product['gauge']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Color Mapping</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Available Color Groups</label>
                        <div class="mb-3">
                            <p id="color_group"><?= getColumnFromTable("product_color","color_name",$product['color_group']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Available Colors</label>
                        <div class="mb-3">
                            <p id="color_name"><?= getColumnFromTable("paint_colors","color_name",$product['color']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold" id="trim_spec_title">Special Trim Specs</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row" id="special_trim_container">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Customer</label>
                                <a href="?page=customer" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <select id="customer_id" class="form-control select2" name="customer_id">
                                <option value="" >Select Customer...</option>
                                <?php
                                $query_customer = "SELECT * FROM customer WHERE hidden = '0' AND status = '1' ORDER BY `customer_first_name` ASC";
                                $result_customer = mysqli_query($conn, $query_customer);            
                                while ($row_customer = mysqli_fetch_array($result_customer)) {
                                    $selected = ($row_customer['customer_id'] == $row['customer_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_customer['customer_id'] ?>" <?= $selected ?>><?= get_customer_name($row_customer['customer_id']) ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8"></div>

                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Special Trim Description</label>
                            <input type="text" id="spec_trim_desc" name="spec_trim_desc" class="form-control" value="<?= $row['spec_trim_desc']?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Special Trim #</label>
                            <input type="text" id="spec_trim_no" name="spec_trim_no" class="form-control" value="<?= $row['spec_trim_no']?>" />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Flat Sheet Width</label>
                            <input type="text" id="flat_sheet_width" name="flat_sheet_width" class="form-control" value="<?= $row['flat_sheet_width'] ?>"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Total Hems</label>
                            <input type="text" id="hems" name="hems" class="form-control" value="<?= $row['hems'] ?>" placeholder="Enter Hems"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Total Bends</label>
                            <input type="text" id="bends" name="bends" class="form-control" value="<?= $row['bends']?>" placeholder="Enter Bends"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Information</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label">Product Description</label>
                        <p id="product_description"><?= $product['product_item'] ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Abbreviation</label>
                        <p id="abbreviation"><?= $product['abbreviation'] ?></p>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Manufactured or Purchased</label>
                        <p id="product_origin"><?= $product['product_origin'] == '1' ? "Purchased" : ($product['product_origin']=='2' ? "Manufactured" : "") ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unit of Measure</label>
                        <p id="unit_of_measure"><?= $product['unit_of_measure'] == 'ft' ? "Ft" : ($product['unit_of_measure']=='each' ? "Each" : "") ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Approx Weight per Ft</label>
                        <p id="weight"><?= $product['weight'] ?></p>
                    </div>
                    
                    <?php
                    $query_img = "SELECT * FROM product_images WHERE productid = '$product_id'";
                    $result_img = mysqli_query($conn, $query_img);
                    if (mysqli_num_rows($result_img) > 0) { ?>
                        <div class="col-md-12">
                            <h5>Current Images</h5>
                            <div class="row pt-3">
                                <?php while ($row_img = mysqli_fetch_array($result_img)) { 
                                    $image_id = $row_img['prodimgid'];
                                ?>
                                    <div class="col-md-2 position-relative">
                                        <img src="<?= $row_img['image_url'] ?>" class="img-fluid" alt="Product Image" />
                                        <button class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn" data-image-id="<?= $image_id ?>">X</button>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Pricing</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Available Lengths</label>
                        <p id="available_lengths"><?= getColumnFromTable("dimensions","dimension",$product['available_lengths']); ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Base Price per Ft</label>
                        <p id="unit_price"><?= floatval($product['unit_price']) ?? 0 ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Floor Price per Ft</label>
                        <p id="floor_price"><?= floatval($product['floor_price']) ?? 0 ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Notes</h5>
            </div>
            <div class="card-body border rounded p-3">
                <p id="product_comment"><?= $product['comment'] ?></p>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
            <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
        </div>
    <?php 
    } 

    if ($action == 'fetch_product_details') {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        
        $product = getProductDetails($product_id);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }

        $product['product_category_text'] = getColumnFromTable("product_category","product_category",$product['product_category']);
        $product['product_line_text'] = getColumnFromTable("product_line","product_line",$product['product_line']);
        $product['product_type_text'] = getColumnFromTable("product_type","product_type",$product['product_type']);
        $product['profile_text'] = getColumnFromTable("profile_type","profile_type",$product['profile']);
        $product['grade_text'] = getColumnFromTable("product_grade","product_grade",$product['grade']);
        $product['gauge_text'] = getColumnFromTable("product_gauge","product_gauge",$product['gauge']);
        $product['color_group_text'] = getColumnFromTable("product_color","color_name",$product['color_group']);
        $product['color_name_text'] = getColumnFromTable("paint_colors","color_name",$product['color']);
        $product['available_lengths_text'] = getColumnFromTable("dimensions","dimension",$product['available_lengths']);
        $product['product_origin_text'] = $product['product_origin'] == 1 ? 'Purchased' : ($product['product_origin'] == 2 ? 'Manufactured' : '');
        $product['unit_of_measure_text'] = $product['unit_of_measure'] == 'ft' ? 'Ft' : ($product['unit_of_measure'] == 'each' ? 'Each' : '');

        echo json_encode(['success' => true, 'data' => $product]);
        exit;
    }



    if ($action == "remove_image") {
        $image_id = $_POST['image_id'];
    
        $delete_query = "DELETE FROM product_images WHERE prodimgid = '$image_id'";
        if (mysqli_query($conn, $delete_query)) {
            /* if (file_exists($image_url)) {
                unlink($image_url);
            } */
            echo 'success';
        } else {
            echo "Error removing image: " . mysqli_error($conn);
        }
    }

    if ($action == "fetch_uploaded_modal") {
        $sql = "SELECT * FROM `$product_excel`";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $columnsWithData = [];
            while ($row = $result->fetch_assoc()) {
                foreach (array_keys($includedColumns) as $col) {
                    if (!empty(trim($row[$col] ?? ''))) {
                        $columnsWithData[$col] = true;
                    }
                }
            }

            $result->data_seek(0);
            ?>
            
            <div class="card card-body shadow">
                <form id="tableForm">
                    <div style="overflow-x: auto; overflow-y: auto; max-height: 90vh; max-width: 100%;">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <?php
                                    foreach ($includedColumns as $dbCol => $label) {
                                        if (isset($columnsWithData[$dbCol])) {
                                            echo "<th class='fs-4'>" . htmlspecialchars($label) . "</th>";
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                $special_trim_id = htmlspecialchars($row['special_trim_id'] ?? '');
                                echo '<tr>';
                                foreach ($includedColumns as $dbCol => $label) {
                                    if (isset($columnsWithData[$dbCol])) {
                                        $value = htmlspecialchars($row[$dbCol] ?? '');
                                        echo "<td contenteditable='true' class='table_data' data-header-name='" . htmlspecialchars($dbCol) . "' data-id='" . $special_trim_id . "'>$value</td>";
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
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, ["xlsx", "xls"])) {
                echo "Please upload a valid Excel file.";
                exit;
            }

            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $dbColumns = array_keys($includedColumns);
            $numColumns = count($dbColumns);

            if (!$conn->query("TRUNCATE TABLE `$product_excel`")) {
                echo "Error truncating table: " . $conn->error;
                exit;
            }

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                $row = array_pad($row, $numColumns, '');
                $row = array_slice($row, 0, $numColumns);

                $data = array_combine($dbColumns, $row);

                $escapedValues = array_map(fn($v) => mysqli_real_escape_string($conn, $v ?? ''), array_values($data));
                $columnNames = implode(", ", array_map(fn($col) => "`$col`", array_keys($data)));
                $columnValues = implode("', '", $escapedValues);

                $sql = "INSERT INTO `$product_excel` ($columnNames) VALUES ('$columnValues')";
                if (!$conn->query($sql)) {
                    echo "Error inserting row {$i}: " . $conn->error;
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

        $selectSql = "SELECT * FROM $product_excel";
        $result = $conn->query($selectSql);
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                unset($row['id']);
    
                $special_trim_id = trim($row['special_trim_id'] ?? '');
    
                $conditions = [];
                foreach ($row as $column => $value) {
                    $conditions[] = "$column = '" . $conn->real_escape_string($value) . "'";
                }
    
                $checkSql = "SELECT COUNT(*) as count FROM $table WHERE " . implode(" AND ", $conditions);
                $checkResult = $conn->query($checkSql);
                $exists = $checkResult->fetch_assoc()['count'] > 0;
    
                if ($exists) {
                    continue;
                }
    
                if (!empty($special_trim_id)) {
                    $idCheckSql = "SELECT COUNT(*) as count FROM $table WHERE special_trim_id = '$special_trim_id'";
                    $idCheckResult = $conn->query($idCheckSql);
                    $idExists = $idCheckResult->fetch_assoc()['count'] > 0;
    
                    if ($idExists) {
                        $updateFields = [];
                        foreach ($row as $column => $value) {
                            if ($column !== 'special_trim_id') {
                                $updateFields[] = "$column = '" . $conn->real_escape_string($value) . "'";
                            }
                        }
                        $updateSql = "UPDATE $table SET " . implode(", ", $updateFields) . " WHERE special_trim_id = '$special_trim_id'";
                        $conn->query($updateSql);
                        continue;
                    }
                }
    
                $columns = implode(", ", array_keys($row));
                $values = "'" . implode("', '", array_map([$conn, 'real_escape_string'], array_values($row))) . "'";
                $insertSql = "INSERT INTO $table ($columns) VALUES ($values)";
                $conn->query($insertSql);
            }
    
            echo "Data has been successfully saved";
    
            $truncateSql = "TRUNCATE TABLE $product_excel";
            if ($conn->query($truncateSql) !== TRUE) {
                echo " but failed to clear $product_excel table: " . $conn->error;
            }
        } else {
            echo "No data found in $product_excel table.";
        }
    }    

    if ($action == "download_excel") {
        $columnNames = array_keys($includedColumns);
        $column_txt = implode(', ', $columnNames);

        $sql = "SELECT $column_txt FROM special_trim WHERE hidden = '0' AND status = '1'";
        $result = $conn->query($sql);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;

        foreach ($includedColumns as $column => $header) {
            $index = array_search($column, $columnNames);
            $columnLetter = indexToColumnLetter($index);
            $sheet->setCellValue($columnLetter . $row, $header);
        }

        $row = 2;
        while ($data = $result->fetch_assoc()) {
            foreach ($includedColumns as $column => $header) {
                $index = array_search($column, $columnNames);
                $columnLetter = indexToColumnLetter($index);
                $sheet->setCellValue($columnLetter . $row, $data[$column] ?? '');
            }
            $row++;
        }

        $filename = "Special Trim.xlsx";
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
            'type' => [
                'columns' => ['product_type_id', 'product_type'],
                'table' => 'product_type',
                'where' => "status = '1'"
            ],
            'grade' => [
                'columns' => ['product_grade_id', 'product_grade'],
                'table' => 'product_grade',
                'where' => "status = '1'"
            ],
            'color' => [
                'columns' => ['color_id', 'product_category', 'color_name'],
                'table' => 'paint_colors',
                'where' => "color_status = '1'"
            ],
            'profile' => [
                'columns' => ['profile_type_id', 'profile_type'],
                'table' => 'profile_type',
                'where' => "status = '1'"
            ],
            'flat_sheet_width' => [
                'columns' => ['id', 'product_system', 'product_category', 'product_line', 'product_type', 'width'],
                'table' => 'flat_sheet_width',
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
    
                    if ($column == 'product_category' && $class == 'color') {
                        $value = getProductCategoryName($data[$column] ?? '');
                    }

                    if ($column == 'product_system' && $class == 'flat_sheet_width') {
                        $value = getProductSystemName($data[$column] ?? '');
                    }

                    if ($column == 'product_category' && $class == 'flat_sheet_width') {
                        $value = getProductCategoryName($data[$column] ?? '');
                    }

                    if ($column == 'product_line' && $class == 'flat_sheet_width') {
                        $value = getProductLineName($data[$column] ?? '');
                    }

                    if ($column == 'product_type' && $class == 'flat_sheet_width') {
                        $value = getProductTypeName($data[$column] ?? '');
                    }
                        
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
    
        $filename = "$classification.xlsx";
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

    if ($action == "update_product_data") {
        $column_name = $_POST['header_name'];
        $new_value = $_POST['new_value'];
        $special_trim_id = $_POST['id'];
        
        if (empty($column_name) || empty($special_trim_id)) {
            exit;
        }
        
        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $special_trim_id = mysqli_real_escape_string($conn, $special_trim_id);
        
        $sql = "UPDATE $product_excel SET `$column_name` = '$new_value' WHERE special_trim_id = '$special_trim_id'";

        if ($conn->query($sql) === TRUE) {
            echo 'Success';
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
    }
    
    if ($action == 'fetch_products') {
        $permission = $_SESSION['permission'];
        $data = [];
        
        $query = "
            SELECT
                s.special_trim_id,
                s.customer_id,
                s.spec_trim_desc,
                s.spec_trim_no,
                s.flat_sheet_width,
                s.hems,
                s.bends,
                s.last_order,
                s.status AS trim_status,
                s.hidden AS trim_hidden,
                p.color,
                p.product_id,
                p.product_item,
                p.grade,
                p.gauge
            FROM
                special_trim s
            LEFT JOIN
                product p
            ON s.product_id = p.product_id
            WHERE
                s.status = 1
                AND s.hidden = 0
            ORDER BY
                p.product_item ASC
        ";
        
        $result = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $customer_id = $row['customer_id'];
            $customer = get_customer_name($customer_id);

            $action_html = '';
            if ($permission === 'edit') {
                $action_html = "
                    <div class='action-btn text-center'>
                        <a href='javascript:void(0)' 
                        id='addProductModalBtn' 
                        title='Edit' 
                        class='text-warning edit'
                        data-id='{$row['special_trim_id']}'>
                        <i class='ti ti-pencil fs-7'></i>
                        </a>
                    </div>";
            }

            $data[] = [
                'customer'      => $customer,
                'color'         => getColorName($row['color']),
                'product_item'  => $row['product_item'],
                'description'   => $row['spec_trim_desc'],
                'trim_no'       => $row['spec_trim_no'],
                'last_order'    => $row['last_order'],
                'customer_id'   => $customer_id,
                'grade'         => $row['grade'],
                'gauge'         => $row['gauge'],
                'action_html'   => $action_html
            ];
        }

        echo json_encode(['data' => $data]);
    }

    
    mysqli_close($conn);
}
?>
