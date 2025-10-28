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

$gauge_29_id = 1;
$gauge_26_id = 2;

$table = 'coil_product';
$test_table = 'coil_product_excel';

$includedColumns = [
    'coil_id',
    'entry_no',
    'date',
    'date_inventory',
    'weight',
    'thickness',
    'width',
    'round_width',
    'coil_class',
    'stated_length',
    'actual_start_length',
    'coating',
    'grade',
    'gauge',
    'main_image',
    'color_sold_as',
    'actual_color',
    'color_close',
    'color_group',
    'paint_supplier',
    'invoice_price',
    'price_per_ft',
    'price_per_in',
    'sq_in_price',
    'allowed_price',
    'floor_price',
    'supplier',
    'stock_availability',
    'warehouse',
    'notes'
];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $coil_id = mysqli_real_escape_string($conn, $_POST['coil_id'] ?? '');
        $entry_no = mysqli_real_escape_string($conn, $_POST['entry_no'] ?? '');
        $warehouse = mysqli_real_escape_string($conn, $_POST['warehouse'] ?? '');
        $coil_class = mysqli_real_escape_string($conn, $_POST['coil_class'] ?? '');
        $coating = mysqli_real_escape_string($conn, $_POST['coating'] ?? '');
        $grade = mysqli_real_escape_string($conn, $_POST['grade'] ?? '');
        $gauge = mysqli_real_escape_string($conn, $_POST['gauge'] ?? '');
        $date = mysqli_real_escape_string($conn, $_POST['date'] ?? '');
        $date_inventory = mysqli_real_escape_string($conn, $_POST['date_inventory'] ?? '');
        $year = !empty($date) ? date('Y', strtotime($date)) : '';
        $month = !empty($date) ? date('m', strtotime($date)) : '';
        $weight = floatval($_POST['weight'] ?? 0);
        $thickness = floatval($_POST['thickness'] ?? 0);
        $width = floatval($_POST['width'] ?? 0);
        $round_width = floatval($_POST['round_width'] ?? 0);
        $stated_length = floatval($_POST['stated_length'] ?? 0);
        $actual_start_length = floatval($_POST['actual_start_length'] ?? 0);
        $color_sold_as = mysqli_real_escape_string($conn, $_POST['color_sold_as'] ?? '');
        $actual_color = mysqli_real_escape_string($conn, $_POST['actual_color'] ?? '');
        $color_close = mysqli_real_escape_string($conn, $_POST['color_close'] ?? '');
        $paint_supplier = mysqli_real_escape_string($conn, $_POST['paint_supplier'] ?? '');
        $color_group = $_POST['color_group'] ?? [];
        $color_group_json = mysqli_real_escape_string($conn, json_encode($color_group));
        $invoice_price = floatval($_POST['invoice_price'] ?? 0);
        $price_per_ft = floatval($_POST['price_per_ft'] ?? 0);
        $price_per_in = floatval($_POST['price_per_in'] ?? 0);
        $sq_in_price = floatval($_POST['sq_in_price'] ?? 0);
        $allowed_price = floatval($_POST['allowed_price'] ?? 0);
        $floor_price = floatval($_POST['floor_price'] ?? 0);
        $supplier = mysqli_real_escape_string($conn, $_POST['supplier'] ?? '');
        $stock_availability = mysqli_real_escape_string($conn, $_POST['stock_availability'] ?? '');
        $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
        $lb_per_ft = ($actual_start_length != 0) ? ($weight / $actual_start_length) : 0;
        $remaining_feet = floatval($_POST['remaining_feet'] ?? 0);
        $user_id = intval($_SESSION['userid'] ?? 0);

        $checkQuery = "SELECT * FROM coil_product WHERE coil_id = '$coil_id'";
        $result = mysqli_query($conn, $checkQuery);
        $isInsert = (mysqli_num_rows($result) == 0);

        if ($isInsert) {
            $remaining_feet = $actual_start_length;
        }

        $main_image_sql = '';
        if (!empty($_FILES['picture_path']['name'][0])) {
            $fileNames = (array) $_FILES['picture_path']['name'];
            $uploadFileDir = '../images/coils/';
            for ($i = 0; $i < count($fileNames); $i++) {
                $fileTmpPath = $_FILES['picture_path']['tmp_name'][$i];
                $fileName = $fileNames[$i];
                if (empty($fileName)) continue;
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    if ($i == 0) {
                        $main_image_sql = ", main_image='images/coils/$newFileName'";
                    }
                }
            }
        }

        if (!$isInsert) {
            $updateQuery = "
                UPDATE coil_product SET
                    entry_no = '$entry_no',
                    warehouse = '$warehouse',
                    coil_class = '$coil_class',
                    coating = '$coating',
                    grade = '$grade',
                    gauge = '$gauge',
                    date = '$date',
                    date_inventory = '$date_inventory',
                    year = '$year',
                    month = '$month',
                    weight = '$weight',
                    thickness = '$thickness',
                    width = '$width',
                    round_width = '$round_width',
                    stated_length = '$stated_length',
                    actual_start_length = '$actual_start_length',
                    color_sold_as = '$color_sold_as',
                    actual_color = '$actual_color',
                    color_close = '$color_close',
                    color_group = '$color_group_json',
                    paint_supplier = '$paint_supplier',
                    invoice_price = '$invoice_price',
                    price_per_ft = '$price_per_ft',
                    price_per_in = '$price_per_in',
                    sq_in_price = '$sq_in_price',
                    allowed_price = '$allowed_price',
                    floor_price = '$floor_price',
                    supplier = '$supplier',
                    stock_availability = '$stock_availability',
                    notes = '$notes',
                    remaining_feet = '$remaining_feet',
                    lb_per_ft = '$lb_per_ft',
                    current_weight = '" . ($lb_per_ft * $remaining_feet) . "'
                    $main_image_sql,
                    last_edit = NOW(),
                    edited_by = '$user_id'
                WHERE coil_id = '$coil_id'
            ";

            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating record: " . mysqli_error($conn);
            }
        } else {
            $main_image = 'images/coils/product.jpg';
            if (!empty($main_image_sql)) {
                $main_image = str_replace(", main_image='", "", $main_image_sql);
                $main_image = rtrim($main_image, "'");
            }
            $insertQuery = "
                INSERT INTO coil_product (
                    coil_id, entry_no, warehouse, coil_class, coating, grade, gauge,
                    date, date_inventory, year, month, weight, thickness, width, round_width,
                    stated_length, actual_start_length, color_sold_as, actual_color, color_close,
                    color_group, paint_supplier, invoice_price, price_per_ft, price_per_in,
                    sq_in_price, allowed_price, floor_price, supplier, stock_availability,
                    notes, remaining_feet, lb_per_ft, current_weight, main_image,
                    added_date, added_by
                ) VALUES (
                    '$coil_id', '$entry_no', '$warehouse', '$coil_class', '$coating', '$grade', '$gauge',
                    '$date', '$date_inventory', '$year', '$month', '$weight', '$thickness', '$width', '$round_width',
                    '$stated_length', '$actual_start_length', '$color_sold_as', '$actual_color', '$color_close',
                    '$color_group_json', '$paint_supplier', '$invoice_price', '$price_per_ft', '$price_per_in',
                    '$sq_in_price', '$allowed_price', '$floor_price', '$supplier', '$stock_availability',
                    '$notes', '$remaining_feet', '$lb_per_ft', '" . ($lb_per_ft * $remaining_feet) . "', '$main_image',
                    NOW(), '$user_id'
                )
            ";

            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error inserting record: " . mysqli_error($conn);
            }
        }
    }


    if ($action == "fetch_modal") {
        $coil_id = mysqli_real_escape_string($conn, $_POST['id']);
        $checkQuery = "SELECT * FROM coil_product WHERE coil_id = '$coil_id'";
        $result = mysqli_query($conn, $checkQuery);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        }
        ?>
        <input type="hidden" id="coil_id_edit" name="coil_id" class="form-control" value="<?= $row['coil_id'] ?? '' ?>"/>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Coil Identifiers</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Coil #</label>
                            <input type="text" id="entry_no" name="entry_no" class="form-control" value="<?= $row['entry_no'] ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Purchase Date</label>
                            <input type="date" id="date" name="date" class="form-control" value="<?= $row['date'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Date Added to Inventory</label>
                            <input type="date" id="date_inventory" name="date_inventory" class="form-control" value="<?= $row['date_inventory'] ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Weight</label>
                            <input type="text" id="weight_edit" name="weight" class="form-control" value="<?= $row['weight'] ?? '' ?>"/>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Thickness</label>
                            <input type="text" id="thickness_edit" name="thickness" class="form-control" value="<?= $row['thickness'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Width</label>
                            <input type="text" id="width" name="width" class="form-control" value="<?= $row['width'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Round Width</label>
                            <input type="text" id="round_width" name="round_width" class="form-control" value="<?= $row['round_width'] ?? '' ?>"/>
                        </div>
                    </div> 
                    <div class="col-md-3">
                        <label class="form-label">Coil Class</label>
                        <div class="mb-3">
                            <input type="text" id="coil_class" name="coil_class" class="form-control" value="<?= $row['coil_class'] ?? '' ?>"/>
                        </div>
                    </div>   

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Stated Length</label>
                            <input type="number" step="0.001" id="stated_length" name="stated_length" class="form-control" value="<?= $row['stated_length'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Actual Starting Length</label>
                            <input type="number" step="0.001" id="actual_start_length" name="actual_start_length" class="form-control"  value="<?= $row['actual_start_length'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Coating</label>
                            <input type="text" id="coating_edit" name="coating" class="form-control" value="<?= $row['coating'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Grade</label>
                        <div class="mb-3">
                            <select id="grade_edit" class="form-control select2-edit" name="grade">
                                <option value="" >Select Grade...</option>
                                <?php
                                $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                $result_grade = mysqli_query($conn, $query_grade);            
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                    $selected = (($row['grade'] ?? '') == $row_grade['product_grade_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_grade['product_grade_id'] ?>" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Gauge</label>
                        <div class="mb-3">
                            <select id="gauge_edit" class="form-control select2-edit" name="gauge">
                                <option value="" >Select Gauge...</option>
                                <?php
                                $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' GROUP BY product_gauge ORDER BY `product_gauge` ASC";
                                $result_gauge = mysqli_query($conn, $query_gauge);            
                                while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                    $selected = (($row['gauge'] ?? '') == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_gauge['product_gauge_id'] ?>" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Coil Images</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="card-body p-0">
                        <p action="#" id="myUpdateDropzone" class="dropzone">
                            <div class="fallback">
                            <input type="file" id="picture_path_edit" name="picture_path" class="form-control" style="display: none"/>
                            </div>
                        </p>
                    </div>
                </div>

                <?php
                            
                if (!empty($row['main_image'])) {
                ?>
                <div class="card card-body m-2">
                    <h5>Current Images</h5>
                    <div class="row pt-3">
                        <div class="col-md-2 position-relative">
                            <div class="mb-3">
                                <img src="<?= $row['main_image'] ?? '' ?>" class="img-fluid" alt="Product Image" />
                                <button class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn" data-image-id="<?= $row['coil_id'] ?? '' ?>">X</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                }
                ?>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold ">Coil Color Mapping</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Sold As</label>
                        <div class="mb-3">
                            <select id="color_sold_as" class="form-control select2-edit" name="color_sold_as">
                                <option value="">Select Color...</option>
                                <?php
                                $query_paint_colors = "
                                    SELECT color_id, color_name 
                                    FROM paint_colors 
                                    WHERE hidden = '0' AND color_status = '1'
                                    ORDER BY color_name ASC
                                ";
                                $result_paint_colors = mysqli_query($conn, $query_paint_colors);

                                $unique_colors = [];
                                while ($row_paint_colors = mysqli_fetch_assoc($result_paint_colors)) {
                                    $name = trim(strtolower($row_paint_colors['color_name']));
                                    if (!isset($unique_colors[$name])) {
                                        $unique_colors[$name] = $row_paint_colors;
                                    }
                                }

                                foreach ($unique_colors as $color) {
                                    $selected = (($row['color_sold_as'] ?? '') == $color['color_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $color['color_id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($color['color_name']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Actual Color</label>
                        <div class="mb-3">
                            <select id="actual_color" class="form-control select2-edit colors-edit" name="actual_color">
                                <option value="">Select Color...</option>
                                <?php
                                foreach ($unique_colors as $color) {
                                    $selected = (($row['actual_color'] ?? '') == $color['color_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $color['color_id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($color['color_name']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Close EKM Color</label>
                        <div class="mb-3">
                            <select id="color_close" class="form-control select2-edit colors-edit" name="color_close">
                                <option value="">Select Color...</option>
                                <?php
                                foreach ($unique_colors as $color) {
                                    $selected = (($row['color_close'] ?? '') == $color['color_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $color['color_id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($color['color_name']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Available Color Groups</label>
                            </div>
                            <select id="color" class="form-control calculate select2-edit" name="color_group">
                                <option value="">Select Color Group...</option>
                                <?php
                                $query_groups = "SELECT * FROM product_color ORDER BY color_name ASC";
                                $result_groups = mysqli_query($conn, $query_groups);
                                while ($row_group = mysqli_fetch_assoc($result_groups)) {
                                    $selected = (($row['color_group'] ?? '') == $row_group['id']) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $row_group['id'] ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($row_group['color_name']) ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Paint Supplier</label>
                        <div class="mb-3">
                            <select id="paint_supplier" class="form-control select2-edit" name="paint_supplier">
                                <option value="" >Select One...</option>
                                <?php
                                $query_paint = "SELECT * FROM paint_providers";
                                $result_paint = mysqli_query($conn, $query_paint);            
                                while ($row_paint = mysqli_fetch_array($result_paint)) {
                                $selected = (($row['paint_supplier'] ?? '') == $row_paint['provider_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_paint['provider_id'] ?>" <?= $selected ?> ><?= $row_paint['provider_name'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold ">Coil Pricing</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Invoice Price ($)</label>
                            <input type="text" id="invoice_price" name="invoice_price" class="form-control" value="<?= $row['invoice_price'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Price per Ft ($)</label>
                            <input type="text" id="price_per_ft" name="price_per_ft" class="form-control" value="<?= $row['price_per_ft'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Price per In ($)</label>
                            <input type="text" id="price_per_in" name="price_per_in" class="form-control" value="<?= $row['price_per_in'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Sq/in Price ($)</label>
                            <input type="text" id="sq_in_price" name="sq_in_price" class="form-control" value="<?= $row['sq_in_price'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Price per In ($)</label>
                            <input type="text" id="price_per_in" name="price_per_in" class="form-control" value="<?= $row['price_per_in'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Max Allowable Price per Ft w/o Approval</label>
                            <input type="text" id="allowed_price" name="allowed_price" class="form-control" value="<?= $row['allowed_price'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Floor Price</label>
                            <input type="text" id="floor_price" name="floor_price" class="form-control" value="<?= $row['floor_price'] ?? '' ?>"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold ">Inventory Tracking</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <div class="mb-3">
                            <select id="supplier" class="form-control select2-edit" name="supplier">
                                <option value="">Select Supplier...</option>
                                <?php
                                $query_supplier = "SELECT * FROM supplier WHERE status = '1'";
                                $result_supplier = mysqli_query($conn, $query_supplier);            
                                while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                    $selected = (($row['supplier'] ?? '') == $row_supplier['supplier_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_supplier['supplier_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_name'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stock Availability</label>
                        <div class="mb-3">
                            <select id="stock_availability" class="form-control select2-edit" name="stock_availability">
                                <option value="" >Select Availability...</option>
                                <?php
                                $query_availability = "SELECT * FROM product_availability";
                                $result_availability = mysqli_query($conn, $query_availability);            
                                while ($row_availability = mysqli_fetch_array($result_availability)) {
                                $selected = (($row['stock_availability'] ?? '') == $row_availability['product_availability_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_availability['product_availability_id'] ?>" <?= $selected ?> ><?= $row_availability['product_availability'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Warehouse</label>
                        <div class="mb-3">
                            <select id="warehouse_edit" class="form-control select2-edit" name="warehouse">
                                <option value="" >Select One...</option>
                                <?php
                                $query_warehouses = "SELECT * FROM warehouses WHERE status = 1 ORDER BY `WarehouseName` ASC";
                                $result_warehouses = mysqli_query($conn, $query_warehouses);            
                                while ($row_warehouses = mysqli_fetch_array($result_warehouses)) {
                                    $selected = (($row['warehouse'] ?? '') == $row_warehouses['WarehouseID']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_warehouses['WarehouseID'] ?>" <?= $selected ?>><?= $row_warehouses['WarehouseName'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold ">Coil Notes</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <textarea 
                                id="notes" 
                                name="notes" 
                                class="form-control" 
                                rows="5"
                            ><?= htmlspecialchars($row['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <div class="form-actions">
                <div class="card-body">
                    <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                    <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $(".select2-edit").each(function () {
                    $(this).select2({
                        width: '100%',
                        placeholder: "Select One...",
                        allowClear: true,
                        dropdownParent: $(this).parent()
                    });
                });

                let uploadedUpdateFiles = [];

                $('#myUpdateDropzone').dropzone({
                    addRemoveLinks: true,
                    dictRemoveFile: "X",
                    init: function() {
                        this.on("addedfile", function(file) {
                            uploadedUpdateFiles.push(file);
                            updateFileInput2();
                            displayFileNames2()
                        });

                        this.on("removedfile", function(file) {
                            uploadedUpdateFiles = uploadedUpdateFiles.filter(f => f.name !== file.name);
                            updateFileInput2();
                            displayFileNames2()
                        });
                    }
                });

                function updateFileInput2() {
                    const fileInput = document.getElementById('picture_path_edit');
                    const dataTransfer = new DataTransfer();

                    uploadedUpdateFiles.forEach(file => {
                        const fileBlob = new Blob([file], { type: file.type });
                        dataTransfer.items.add(new File([fileBlob], file.name, { type: file.type }));
                    });

                    fileInput.files = dataTransfer.files;
                }

                function displayFileNames2() {
                    let files = document.getElementById('picture_path_edit').files;
                    let fileNames = '';

                    if (files.length > 0) {
                        for (let i = 0; i < files.length; i++) {
                            let file = files[i];
                            fileNames += `<p>${file.name}</p>`;
                        }
                    } else {
                        fileNames = '<p>No files selected</p>';
                    }

                    console.log(fileNames);
                }

                $('#product_category_update').on('change', function() {
                    var product_category_id = $(this).val();
                    $.ajax({
                        url: 'pages/product_ajax.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            product_category_id: product_category_id,
                            action: "fetch_product_fields"
                        },
                        success: function(response) {
                            $('.opt_field_update').hide();

                            if (response.length > 0) {

                                response.forEach(function(field) {
                                    var fieldParts = field.fields.split(',');
                                    fieldParts.forEach(function(part) {
                                        $('.opt_field_update[data-id="' + part + '"]').show();
                                    });
                                });
                            } else {
                                $('.opt_field_update').show();
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            alert('Error: ' + textStatus + ' - ' + errorThrown);
                        }
                    });
                });
                
            });

        </script>
        <?php

    } 

    if ($action == "fetch_width_details") {
        $id = intval($_POST['width_id']);

        $query = "SELECT * FROM coil_width WHERE id = $id AND hidden = 0";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            echo json_encode([
                "success" => true,
                "classification" => $row['classification']
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "No width details found."]);
        }
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

    if ($action == "change_status") {
        $coil_id = mysqli_real_escape_string($conn, $_POST['coil_id']);

        $hideQuery = "UPDATE coil_product SET hidden = '1' WHERE coil_id = '$coil_id'";
        if (mysqli_query($conn, $hideQuery)) {
            echo "success";
        } else {
            echo "Error updating hidden flag: " . mysqli_error($conn);
        }
    }


    if ($action == "download_excel") {
        $column_txt = implode(', ', $includedColumns);

        $sql = "SELECT $column_txt FROM $table WHERE hidden = '0' AND status != '4'";
        $result = $conn->query($sql);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;
        foreach ($includedColumns as $index => $column) {
            $header = ucwords(str_replace('_', ' ', $column));
            $columnLetter = ($index >= 26) ? indexToColumnLetter($index) : chr(65 + $index);
            $sheet->setCellValue($columnLetter . $row, $header);
        }

        $row = 2;
        while ($data = $result->fetch_assoc()) {
            foreach ($includedColumns as $index => $column) {
                $columnLetter = ($index >= 26) ? indexToColumnLetter($index) : chr(65 + $index);
                $sheet->setCellValue($columnLetter . $row, $data[$column] ?? '');
            }
            $row++;
        }

        $filename = "COIL_PRODUCT.xlsx";
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
            if (!$conn->query($truncateSql)) {
                echo "Error truncating table: " . $conn->error;
                exit;
            }

            foreach ($rows as $index => $row) {
                if ($index == 0) continue;

                $data = array_combine($dbColumns, $row);
                $columnNames = implode(", ", array_keys($data));
                $columnValues = implode("', '", array_map(function($value) { return $value ?? ''; }, array_values($data)));

                $sql = "INSERT INTO $test_table ($columnNames) VALUES ('$columnValues')";
                if (!$conn->query($sql)) {
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

        if (empty($column_name) || empty($id)) exit;

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

            $conn->query("TRUNCATE TABLE $test_table");
        } else {
            echo "No data found in test table.";
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

            <div class="card card-body shadow" data-table="<?= $table ?>">
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
