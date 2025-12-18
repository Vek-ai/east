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

$product_excel = 'special_trim_excel';
//4 = TRIM
$trim_id = 4;
$category_id = 4;

$includedColumns = [
    'special_trim_id'   => 'ID',
    'product_category'  => 'Product Category',
    'product_type'      => 'Product Type',
    'profile'           => 'Product Profile',
    'grade'             => 'Grade',
    'gauge'             => 'Gauge',
    'color_group'       => 'Color Group',
    'color_paint'       => 'Color',
    'customer_id'       => 'Customer',
    'trim_no'           => 'Special Trim #',
    'description'       => 'Description',
    'warranty_type'     => 'Warranty Type',
    'product_origin'    => 'Manufactured or Purchased',
    'unit_of_measure'   => 'Unit of Measure',
    'weight'            => 'Weight',
    'flat_sheet_width'  => 'Flat Sheed Width',
    'bends'             => 'Total Bends',
    'hems'              => 'Total Hems',
    'available_lengths' => 'Available Lengths',
    'unit_price'        => 'Retail Price',
    'comment'           => 'Notes',
    'last_order'        => 'Last Ordered'
];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);

        $customer          = mysqli_real_escape_string($conn, $_POST['customer'] ?? '');
        $spec_trim_desc    = mysqli_real_escape_string($conn, $_POST['spec_trim_desc'] ?? '');
        $spec_trim_no      = mysqli_real_escape_string($conn, $_POST['spec_trim_no'] ?? '');
        $flat_sheet_width  = mysqli_real_escape_string($conn, $_POST['flat_sheet_width'] ?? '');
        $hems              = mysqli_real_escape_string($conn, $_POST['hems'] ?? '');
        $bends             = mysqli_real_escape_string($conn, $_POST['bends'] ?? '');
        $userid            = mysqli_real_escape_string($conn, $_POST['userid'] ?? '');

        $updateQuery = "
            UPDATE product SET
                customer          = '$customer',
                spec_trim_desc    = '$spec_trim_desc',
                spec_trim_no      = '$spec_trim_no',
                flat_sheet_width  = '$flat_sheet_width',
                hems              = '$hems',
                bends             = '$bends'
            WHERE product_id = '$product_id'
            LIMIT 1
        ";

        if (!mysqli_query($conn, $updateQuery)) {
            echo 'Error updating product: ' . mysqli_error($conn);
            exit;
        }

        echo $updateQuery .'success_update';
    }

    if ($action == "get_product_abr") {
        $category_ids = isset($_POST['category_ids']) ? array_map('intval', $_POST['category_ids']) : [];
        $type_ids     = isset($_POST['type_ids']) ? array_map('intval', $_POST['type_ids']) : [];
        $profile_ids  = isset($_POST['profile_ids']) ? array_map('intval', $_POST['profile_ids']) : [];
        $grade_ids    = isset($_POST['grade_ids']) ? array_map('intval', $_POST['grade_ids']) : [];
        $gauge_ids    = isset($_POST['gauge_ids']) ? array_map('intval', $_POST['gauge_ids']) : [];
        $color_ids    = isset($_POST['color_ids']) ? array_map('intval', $_POST['color_ids']) : [];
        $length       = isset($_POST['length']) ? floatval($_POST['length']) : 0;
        $special_trim_id   = isset($_POST['special_trim_id']) ? $_POST['special_trim_id'] : '';

        $panel_id = 3;
        $trim_id  = 4;

        $product_ids_string = generateProductAbrString(
                                $category_ids,
                                $profile_ids,
                                $grade_ids,
                                $gauge_ids,
                                $type_ids,
                                $color_ids,
                                '',
                                $special_trim_id = null
                            );

        echo $product_ids_string;
    } 

    if ($action == "fetch_modal") {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);

        $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        }
        ?>
        <input type="hidden" id="product_id" name="product_id" value="<?= $product_id ?>" />
        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Identifier</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Product Category</label>
                        <div class="mb-3">
                            <p><?= getColumnFromTable("product_category","product_category",$row['product_category']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Product Line</label>
                        <div class="mb-3">
                            <p><?= getColumnFromTable("product_line","product_line",$row['product_line']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Product Type</label>
                        <div class="mb-3">
                            <p><?= getColumnFromTable("product_type","product_type",$row['product_type']); ?></p>
                        </div>
                    </div>

                    <?php $selected_profile = (array) json_decode($row['profile'] ?? '[]', true); ?>
                    <div class="col-md-4">
                        <label class="form-label">Product Profile</label>
                        <div class="mb-3">
                            <p><?= getColumnFromTable("profile_type","profile_type",$row['profile']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Product Grade</label>
                        <div class="mb-3">
                            <p><?= getColumnFromTable("product_grade","product_grade",$row['grade']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Product Gauge</label>
                        <div class="mb-3">
                            <p><?= getColumnFromTable("product_gauge","product_gauge",$row['gauge']); ?></p>
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
                        <div class="mb-3">
                            <label class="form-label">Available Color Groups</label>
                            <div class="mb-3">
                                <p><?= getColumnFromTable("product_color","color_name",$row['color_group']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Available Colors</label>
                            <div class="mb-3">
                                <p><?= getColumnFromTable("paint_colors","color_name",$row['color']); ?></p>
                            </div>
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
                        <div class="mb-3">
                            <label class="form-label">Product Description</label>
                            <div class="mb-3">
                                <p><?= $row['product_item'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Abbreviation</label>
                            <div class="mb-3">
                                <p><?= $row['abbreviation'] ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Manufactured or Purchased</label>
                            <div class="mb-3">
                                <?= $row['product_origin'] == '1' ? "<p>Purchased</p>" : '' ?>
                                <?= $row['product_origin'] == '2' ? "<p>Manufactured</p>" : '' ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Unit of Measure</label>
                            <div class="mb-3">
                                <?= $row['unit_of_measure'] == 'ft' ? "<p>Ft</p>" : '' ?>
                                <?= $row['unit_of_measure'] == 'each' ? "<p>Each</p>" : '' ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Approx Weight per Ft</label>
                            <div class="mb-3">
                                <p><?= $row['weight'] ?></p>
                            </div>
                        </div>
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
                                        <div class="mb-3">
                                            <img src="<?= $row_img['image_url'] ?>" class="img-fluid" alt="Product Image" />
                                            <button class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn" data-image-id="<?= $image_id ?>">X</button>
                                        </div>
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
                            <select id="customer" class="form-control select2" name="customer">
                                <option value="" >Select Customer...</option>
                                <?php
                                $query_customer = "SELECT * FROM customer WHERE hidden = '0' AND status = '1' ORDER BY `customer_first_name` ASC";
                                $result_customer = mysqli_query($conn, $query_customer);            
                                while ($row_customer = mysqli_fetch_array($result_customer)) {
                                    $selected = ($row_customer['customer_id'] == $row['customer']) ? 'selected' : '';
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
                <h5 class="mb-0 fw-bold">Product Pricing</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Available Lengths</label>
                        <div class="mb-3">
                            <p><?= getColumnFromTable("dimensions","dimension",$row['available_lengths']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?php $unit_price = floatval($row['unit_price']) ?? 0; ?>
                        <label class="form-label">Base Price per Ft</label>
                        <div class="mb-3">
                            <p><?= $unit_price ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?php $floor_price = floatval($row['floor_price']) ?? 0; ?>
                        <label class="form-label">Floor Price per Ft</label>
                        <div class="mb-3">
                            <p><?= $floor_price ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Notes</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <p><?= $row['comment'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <div class="form-actions">
                <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
        <?php
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
        $table = "special_trim";
    
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
                *
            FROM
                product 
            WHERE
                status = 1
                AND hidden = 0
                AND product_category = '4'
                AND is_special_trim = '1'
            ORDER BY
                product_item ASC
        ";
        $result = mysqli_query($conn, $query);
        $no = 1;
    
        while ($row = mysqli_fetch_assoc($result)) {
            $product_id = $row['product_id'];
            $customer_id = $row['customer'];
            $customer = get_customer_name($customer_id);
            $description = $row['spec_trim_desc'];
            $trim_no = $row['spec_trim_no'];
            $last_order = $row['last_order'];
            
            $action_html = '';
            if ($permission === 'edit') {
                $action_html = "
                    <div class='action-btn text-center'>
                        <a href='javascript:void(0)' 
                        id='addProductModalBtn' 
                        title='Edit' 
                        class='text-warning edit'
                        data-id='{$product_id}' 
                        >
                        <i class='ti ti-pencil fs-7'></i>
                        </a>
                    </div>";
            }
    
            $data[] = [
                'customer'          => $customer,
                'color'             => getColorName($row['color']),
                'product_item'      => $row['product_item'],
                'description'       => $description,
                'trim_no'           => $trim_no,
                'last_order'        => $last_order,
                'customer_id'       => $customer_id,
                'grade'             => $row['grade'],
                'gauge'             => $row['gauge'],
                'action_html'       => $action_html
            ];
    
            $no++;
        }
    
        echo json_encode(['data' => $data]);
    }
    
    mysqli_close($conn);
}
?>
