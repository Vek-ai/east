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
    'coil_id'           => 'Coil ID',
    'entry_no'          => 'Coil #',
    'sleeve_code'       => 'Sleeve #',
    'coil_code'         => 'Coil Code',
    'date'              => 'Purchase Date',
    'date_inventory'    => 'Date Added to Inventory',
    'supplier'          => 'Supplier',
    'purchase_weight'   => 'Purchased Weight (Lbs)',
    'purchase_cwt'      => 'Purchased CWT Units',
    'start_weight'      => 'Starting Weight (Lbs)',
    'start_weight_cwt'  => 'Starting CWT Units',
    'weight'            => 'Current Weight (Lbs)',
    'lb_per_ft'         => 'Lbs per Linear Ft',
    'lb_per_in'         => 'Lbs per Linear In',
    'lb_sq_ft'          => 'Lbs per Sq/Ft',
    'lb_sq_in'          => 'Lbs per Sq/In',
    'purchase_length'   => 'Purchased Length (LF)',
    'starting_length'   => 'Starting Length (LF)',
    'remaining_feet'    => 'Current Length (LF)',
    'thickness'         => 'Thickness',
    'width'             => 'Width',
    'round_width'       => 'Round Width',
    'grade'             => 'Grade',
    'coating'           => 'Coating',
    'total_price'       => 'Total Purchase Price',
    'purchase_price'    => 'Purchase Price (LF)',
    'purchase_price_cwt'=> 'Purchase Price (CWT)',
    'purchase_l_in'     => 'Purchase Price per L in',
    'purchase_sq_ft'    => 'Purchase Price per Sq/Ft',
    'purchase_sq_in'    => 'Purchase Price per Sq/In',
    'paint_supplier'    => 'Paint Supplier',
    'color_sold_as'     => 'Purchased As Color',
    'actual_color'      => 'Actual Color/Sold As',
    'color_close'       => 'Close EKM Color',
    'color_group'       => 'Color Group',
    'grade_sold_as'     => 'Grade Sold As',
    'gauge_sold_as'     => 'Gauge Sold As',
    'notes'             => 'Notes',
    'main_image'        => 'Main Image'
];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $coil_id = mysqli_real_escape_string($conn, $_POST['coil_id'] ?? '');
        
        // --- Basic Coil Info ---
        $entry_no = mysqli_real_escape_string($conn, $_POST['entry_no'] ?? '');
        $coil_no = mysqli_real_escape_string($conn, $_POST['coil_no'] ?? '');
        $warehouse = mysqli_real_escape_string($conn, $_POST['warehouse'] ?? '');
        $coil_class = mysqli_real_escape_string($conn, $_POST['coil_class'] ?? '');
        $coil_code = mysqli_real_escape_string($conn, $_POST['coil_code'] ?? '');
        $sleeve_code = mysqli_real_escape_string($conn, $_POST['sleeve_code'] ?? '');
        $date = mysqli_real_escape_string($conn, $_POST['date'] ?? '');
        $date_inventory = mysqli_real_escape_string($conn, $_POST['date_inventory'] ?? '');
        $year = !empty($date) ? date('Y', strtotime($date)) : '';
        $month = !empty($date) ? date('m', strtotime($date)) : '';

        // --- Specs ---
        $grade = mysqli_real_escape_string($conn, $_POST['grade'] ?? '');
        $grade_sold_as = mysqli_real_escape_string($conn, $_POST['grade_sold_as'] ?? '');
        $gauge = mysqli_real_escape_string($conn, $_POST['gauge'] ?? '');
        $gauge_sold_as = mysqli_real_escape_string($conn, $_POST['gauge_sold_as'] ?? '');
        $coating = mysqli_real_escape_string($conn, $_POST['coating'] ?? '');
        $thickness = floatval($_POST['thickness'] ?? 0);
        $width = floatval($_POST['width'] ?? 0);
        $round_width = floatval($_POST['round_width'] ?? 0);

        // --- Weights ---
        $purchase_weight = floatval($_POST['purchase_weight'] ?? 0);
        $start_weight = floatval($_POST['start_weight'] ?? 0);
        $weight = floatval($_POST['weight'] ?? 0);
        $purchase_cwt = $purchase_weight / 100;
        $start_weight_cwt = $start_weight / 100;

        // --- Lengths ---
        $stated_length = floatval($_POST['stated_length'] ?? 0);
        $starting_length = floatval($_POST['starting_length'] ?? 0);
        $purchase_length = floatval($_POST['purchase_length'] ?? 0);
        $remaining_feet = floatval($_POST['remaining_feet'] ?? $starting_length);

        // --- Calculated Weight Metrics ---
        $lb_per_ft = $starting_length > 0 ? $purchase_weight / $starting_length : 0;
        $lb_per_in = $lb_per_ft / 12;
        $lb_sq_in = $width > 0 ? $lb_per_in / $width : 0;
        $lb_sq_ft = $lb_sq_in * 144;
        $current_weight = $lb_per_ft * $remaining_feet;

        // --- Costs ---
        $total_price = floatval($_POST['total_price'] ?? 0);
        $purchase_price = floatval($_POST['purchase_price'] ?? 0);
        $purchase_price_cwt = $purchase_cwt > 0 ? $total_price / $purchase_cwt : 0;
        $purchase_l_in = $purchase_price / 12;
        $purchase_sq_ft = $width > 0 ? $purchase_l_in / $width * 144 : 0;
        $invoice_price = floatval($_POST['invoice_price'] ?? 0);
        $price_per_ft = floatval($_POST['price_per_ft'] ?? 0);
        $price_per_in = floatval($_POST['price_per_in'] ?? 0);
        $sq_in_price = floatval($_POST['sq_in_price'] ?? 0);
        $allowed_price = floatval($_POST['allowed_price'] ?? 0);
        $floor_price = floatval($_POST['floor_price'] ?? 0);

        // --- Color Info ---
        $color_sold_as = mysqli_real_escape_string($conn, $_POST['color_sold_as'] ?? '');
        $actual_color = mysqli_real_escape_string($conn, $_POST['actual_color'] ?? '');
        $color_close = mysqli_real_escape_string($conn, $_POST['color_close'] ?? '');
        $paint_supplier = mysqli_real_escape_string($conn, $_POST['paint_supplier'] ?? '');
        $supplier = mysqli_real_escape_string($conn, $_POST['supplier'] ?? '');
        $stock_availability = mysqli_real_escape_string($conn, $_POST['stock_availability'] ?? '');
        $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
        $coil_condition = mysqli_real_escape_string($conn, $_POST['coil_condition'] ?? '');
        
        $user_id = intval($_SESSION['userid'] ?? 0);

        // --- Determine Color Group ---
        $color_group = 0;
        if (!empty($color_close)) {
            $res = mysqli_query($conn, "SELECT color_group FROM paint_colors WHERE hidden='0' AND color_id='$color_close' LIMIT 1");
            if ($res && $rg = mysqli_fetch_assoc($res)) $color_group = intval($rg['color_group']);
        }

        // --- Handle Image Upload ---
        $main_image = 'images/coils/product.jpg';
        if (!empty($_FILES['picture_path']['name'][0])) {
            $uploadFileDir = '../images/coils/';
            foreach ($_FILES['picture_path']['name'] as $i => $fileName) {
                if (empty($fileName)) continue;
                $fileTmpPath = $_FILES['picture_path']['tmp_name'][$i] ?? '';
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = md5(time() . $fileName . $i) . '.' . $ext;
                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path) && $i==0) $main_image = "images/coils/$newFileName";
            }
        }

        // --- Insert or Update ---
        $checkQuery = "SELECT coil_id FROM coil_product WHERE coil_id='$coil_id'";
        $isInsert = (mysqli_num_rows(mysqli_query($conn, $checkQuery)) == 0);

        if ($isInsert) {
            $insertQuery = "
                INSERT INTO coil_product (
                    entry_no, coil_no, warehouse, coil_class, coil_code, sleeve_code,
                    date, date_inventory, year, month,
                    grade, grade_sold_as, gauge, gauge_sold_as, coating,
                    thickness, width, round_width,
                    purchase_weight, start_weight, weight,
                    stated_length, starting_length, purchase_length,
                    purchase_cwt, start_weight_cwt,
                    lb_per_ft, lb_per_in, lb_sq_ft, lb_sq_in,
                    color_sold_as, actual_color, color_close, color_group, paint_supplier,
                    total_price, purchase_price, purchase_price_cwt, purchase_l_in, purchase_sq_ft,
                    invoice_price, price_per_ft, price_per_in, sq_in_price,
                    allowed_price, floor_price,
                    supplier, stock_availability, notes,
                    remaining_feet, current_weight, coil_condition,
                    main_image, added_date, added_by
                ) VALUES (
                    '$entry_no', '$coil_no', '$warehouse', '$coil_class', '$coil_code', '$sleeve_code',
                    '$date', '$date_inventory', '$year', '$month',
                    '$grade', '$grade_sold_as', '$gauge', '$gauge_sold_as', '$coating',
                    '$thickness', '$width', '$round_width',
                    '$purchase_weight', '$start_weight', '$weight',
                    '$stated_length', '$starting_length', '$purchase_length',
                    '$purchase_cwt', '$start_weight_cwt',
                    '$lb_per_ft', '$lb_per_in', '$lb_sq_ft', '$lb_sq_in',
                    '$color_sold_as', '$actual_color', '$color_close', '$color_group', '$paint_supplier',
                    '$total_price', '$purchase_price', '$purchase_price_cwt', '$purchase_l_in', '$purchase_sq_ft',
                    '$invoice_price', '$price_per_ft', '$price_per_in', '$sq_in_price',
                    '$allowed_price', '$floor_price',
                    '$supplier', '$stock_availability', '$notes',
                    '$remaining_feet', '$current_weight', '$coil_condition',
                    '$main_image', NOW(), '$user_id'
                )
            ";
            echo mysqli_query($conn, $insertQuery) ? "success_add" : "Error inserting record: " . mysqli_error($conn);
        } else {
            $updateQuery = "
                UPDATE coil_product SET
                    entry_no='$entry_no', coil_no='$coil_no', warehouse='$warehouse', coil_class='$coil_class', coil_code='$coil_code', sleeve_code='$sleeve_code',
                    date='$date', date_inventory='$date_inventory', year='$year', month='$month',
                    grade='$grade', grade_sold_as='$grade_sold_as', gauge='$gauge', gauge_sold_as='$gauge_sold_as', coating='$coating',
                    thickness='$thickness', width='$width', round_width='$round_width',
                    purchase_weight='$purchase_weight', start_weight='$start_weight', weight='$weight',
                    stated_length='$stated_length', starting_length='$starting_length', purchase_length='$purchase_length',
                    purchase_cwt='$purchase_cwt', start_weight_cwt='$start_weight_cwt',
                    lb_per_ft='$lb_per_ft', lb_per_in='$lb_per_in', lb_sq_ft='$lb_sq_ft', lb_sq_in='$lb_sq_in',
                    color_sold_as='$color_sold_as', actual_color='$actual_color', color_close='$color_close', color_group='$color_group', paint_supplier='$paint_supplier',
                    total_price='$total_price', purchase_price='$purchase_price', purchase_price_cwt='$purchase_price_cwt', purchase_l_in='$purchase_l_in', purchase_sq_ft='$purchase_sq_ft',
                    invoice_price='$invoice_price', price_per_ft='$price_per_ft', price_per_in='$price_per_in', sq_in_price='$sq_in_price',
                    allowed_price='$allowed_price', floor_price='$floor_price',
                    supplier='$supplier', stock_availability='$stock_availability', notes='$notes',
                    remaining_feet='$remaining_feet', current_weight='$current_weight', coil_condition='$coil_condition',
                    main_image='$main_image', last_edit=NOW(), edited_by='$user_id'
                WHERE coil_id='$coil_id'
            ";
            echo mysqli_query($conn, $updateQuery) ? "success_update" : "Error updating record: " . mysqli_error($conn);
        }
    }

    if ($action == "inventory_record") {
        $coil_id = intval($_POST['coil_id'] ?? 0);
        $current_inventory = floatval($_POST['current_inventory'] ?? 0);
        $user_id = intval($_SESSION['userid'] ?? 0);

        $warehouse = intval($_POST['Warehouse_id'] ?? 0);
        $rack      = intval($_POST['rack'] ?? 0);
        $slot      = intval($_POST['slot'] ?? 0);
        $shelf     = intval($_POST['Shelves_id'] ?? 0);
        $row       = intval($_POST['Row_id'] ?? 0);
        $bin       = intval($_POST['Bin_id'] ?? 0);

        if ($coil_id <= 0) {
            echo "Invalid coil ID or inventory value.";
            exit;
        }

        $query = "SELECT entry_no, sleeve_code, weight, remaining_feet, coil_condition, warehouse 
                FROM coil_product 
                WHERE coil_id = $coil_id LIMIT 1";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 0) {
            echo "Coil not found.";
            exit;
        }

        $coil = mysqli_fetch_assoc($result);
        mysqli_begin_transaction($conn);

        try {
            $insertQuery = "INSERT INTO coil_inventory_check_history 
                (entry_no, coil_id, sleeve_code, weight, remaining_feet, inventory_check_lf, coil_condition, warehouse, 
                inventory_checked_at, checked_by)
                VALUES (
                    '".mysqli_real_escape_string($conn, $coil['entry_no'])."',
                    $coil_id,
                    '".mysqli_real_escape_string($conn, $coil['sleeve_code'])."',
                    '".floatval($coil['weight'])."',
                    '".floatval($coil['remaining_feet'])."',
                    '$current_inventory',
                    '".intval($coil['coil_condition'])."',
                    '".mysqli_real_escape_string($conn, $warehouse)."',
                    NOW(),
                    $user_id
                )";

            if (!mysqli_query($conn, $insertQuery)) {
                throw new Exception("Error saving inventory: " . mysqli_error($conn));
            }

            $updateQuery = "UPDATE coil_product 
                            SET warehouse = '$warehouse',
                                rack = '$rack',
                                slot = '$slot',
                                Shelves_id = '$shelf',
                                Row_id = '$row',
                                Bin_id = '$bin',
                                last_edit = NOW(),
                                edited_by = $user_id
                            WHERE coil_id = $coil_id";

            if (!mysqli_query($conn, $updateQuery)) {
                throw new Exception("Error updating coil product location: " . mysqli_error($conn));
            }

            mysqli_commit($conn);
            echo "success";

        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo $e->getMessage();
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
                <h5 class="mb-0 fw-bold">Coil #'s</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Coil Entry #</label>
                            <p class="form-control"><?= $row['coil_id'] ?? '' ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Coil #</label>
                            <input type="text" id="entry_no" name="entry_no" class="form-control" value="<?= $row['entry_no'] ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Sleeve #</label>
                            <input type="text" id="sleeve_code" name="sleeve_code" class="form-control" value="<?= $row['sleeve_code'] ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Coil Code</label>
                            <input type="text" id="coil_code" name="coil_code" class="form-control" value="<?= $row['coil_code'] ?? '' ?>" />
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
                <h5 class="mb-0 fw-bold">Coil Weight Info</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Purchased Weight (Lbs)</label>
                            <input type="text" id="purchase_weight" name="purchase_weight" class="form-control" value="<?= $row['purchase_weight'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Purchased CWT Units</label>
                            <input type="text" id="purchase_cwt" name="purchase_cwt" class="form-control" value="<?= $row['purchase_cwt'] ?? ''  ?>" readonly/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Starting Weight (Lbs)</label>
                            <input type="text" id="start_weight" name="start_weight" class="form-control" value="<?= $row['start_weight'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Starting CWT Units</label>
                            <input type="text" id="start_weight_cwt" name="start_weight_cwt" class="form-control" value="<?= $row['start_weight_cwt'] ?? ''  ?>" readonly/>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Current Weight (Lbs)</label>
                            <input type="text" id="weight" name="weight" class="form-control" value="<?= $row['weight'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-9"></div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Lbs per Linear Ft</label>
                            <input type="text" id="lb_per_ft" name="lb_per_ft" class="form-control" value="<?= $row['lb_per_ft'] ?? '' ?>" readonly/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Lbs per Linear In</label>
                            <input type="text" id="lb_per_in" name="lb_per_in" class="form-control" value="<?= $row['lb_per_in'] ?? '' ?>" readonly/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Lbs per Sq/Ft</label>
                            <input type="text" id="lb_sq_ft" name="lb_sq_ft" class="form-control" value="<?= $row['lb_sq_ft'] ?? '' ?>" readonly/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Lbs per Sq/In</label>
                            <input type="text" id="lb_sq_in" name="lb_sq_in" class="form-control" value="<?= $row['lb_sq_in'] ?? '' ?>" readonly/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Coil Length Info</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Purchased Length (LF)</label>
                            <input type="text" id="purchase_length" name="purchase_length" class="form-control" value="<?= $row['purchase_length'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Starting Length (LF)</label>
                            <input type="text" id="starting_length" name="starting_length" class="form-control" value="<?= $row['starting_length'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Current Length (LF)</label>
                            <input type="text" id="remaining_feet" name="remaining_feet" class="form-control" value="<?= number_format(floatval($row['remaining_feet']),2) ?? '' ?>"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Coil Specs</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
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
                    <div class="col-md-3"></div>

                    <div class="col-md-3">
                        <label class="form-label">Grade</label>
                        <div class="mb-3">
                            <select id="grade_edit" class="form-control select2-edit" name="grade">
                                <option value="" >Select Grade...</option>
                                <?php
                                $query_grade = "SELECT * FROM coil_grade WHERE hidden = '0' AND status = '1' ORDER BY `coil_grade` ASC";
                                $result_grade = mysqli_query($conn, $query_grade);            
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                    $selected = (($row['grade'] ?? '') == $row_grade['coil_grade_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_grade['coil_grade_id'] ?>" <?= $selected ?>><?= $row_grade['coil_grade'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Coating</label>
                            <input type="text" id="coating_edit" name="coating" class="form-control" value="<?= $row['coating'] ?? '' ?>"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Coil Cost Info</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Total Purchase Price</label>
                            <input type="number" step="0.0001" id="total_price" name="total_price" class="form-control" value="<?= $row['total_price'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Purchase Price (LF)</label>
                            <input type="number" step="0.0001" id="purchase_price" name="purchase_price" class="form-control" value="<?= $row['purchase_price'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Purchase Price (CWT)</label>
                            <input type="number" step="0.0001" id="purchase_price_cwt" name="purchase_price_cwt" class="form-control" value="<?= $row['purchase_price_cwt'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3"></div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Purchase Price per L in</label>
                            <input type="number" step="0.0001" id="purchase_l_in" name="purchase_l_in" class="form-control" value="<?= $row['purchase_l_in'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Purchase Price per Sq/Ft</label>
                            <input type="number" step="0.0001" id="purchase_sq_ft" name="purchase_sq_ft" class="form-control" value="<?= $row['purchase_sq_ft'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Purchase Price per Sq/In</label>
                            <input type="number" step="0.0001" id="purchase_sq_in" name="purchase_sq_in" class="form-control" value="<?= $row['purchase_sq_in'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Coil Color Info</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
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
                    <div class="col-md-3">
                        <label class="form-label">Purchased As Color</label>
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
                        <label class="form-label">Actual Color/Sold As</label>
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
                    <div class="col-md-3"></div>

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
                                <label class="form-label">Color Groups</label>
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
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold ">Sold As/Coil Condition</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Grade Sold As</label>
                            <a href="?page=coil_grade" target="_blank" class="text-decoration-none">Edit</a>
                        </div>
                        <div class="mb-3">
                            <select id="grade_sold_as" class="form-control select2-edit" name="grade_sold_as">
                                <option value="" >Select Grade...</option>
                                <?php
                                $query_grade = "SELECT * FROM coil_grade WHERE hidden = '0' AND status = '1' ORDER BY `coil_grade` ASC";
                                $result_grade = mysqli_query($conn, $query_grade);            
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                    $selected = (($row['grade_sold_as'] ?? '') == $row_grade['coil_grade_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_grade['coil_grade_id'] ?>" <?= $selected ?>><?= $row_grade['coil_grade'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Gauge Sold As</label>
                            <a href="?page=product_gauge" target="_blank" class="text-decoration-none">Edit</a>
                        </div>
                        <div class="mb-3">
                            <select id="gauge_sold_as" class="form-control select2-edit" name="gauge_sold_as">
                                <option value="" >Select Grade...</option>
                                <?php
                                $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY `product_gauge` ASC";
                                $result_gauge = mysqli_query($conn, $query_gauge);            
                                while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                    $selected = (($row['gauge_sold_as'] ?? '') == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_gauge['product_gauge_id'] ?>" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Coil Condition Rating</label>
                            <a href="?page=coil_condition" target="_blank" class="text-decoration-none">Edit</a>
                        </div>
                        <div class="mb-3">
                            <select id="coil_condition" class="form-control select2-edit" name="coil_condition">
                                <option value="" >Select Condition...</option>
                                <?php
                                $query_condition = "SELECT * FROM coil_condition WHERE hidden = '0' AND status = '1' ORDER BY `coil_condition` ASC";
                                $result_condition = mysqli_query($conn, $query_condition);            
                                while ($row_condition = mysqli_fetch_array($result_condition)) {
                                    $selected = (($row['coil_condition'] ?? '') == $row_condition['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_condition['id'] ?>" <?= $selected ?>><?= $row_condition['coil_condition'] ?></option>
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
            });

        </script>
        <?php

    } 

    if ($action == "fetch_inventory_modal") {
        $coil_id = mysqli_real_escape_string($conn, $_POST['id']);
        $checkQuery = "
            SELECT cp.*, cich.inventory_check_lf AS last_inventory
            FROM coil_product cp
            LEFT JOIN coil_inventory_check_history cich
                ON cich.coil_id = cp.coil_id
            WHERE cp.coil_id = '$coil_id'
            ORDER BY cich.inventory_checked_at DESC
            LIMIT 1
        ";
        $result = mysqli_query($conn, $checkQuery);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        
            ?>
            <input type="hidden" id="coil_id_edit" name="coil_id" class="form-control" value="<?= $row['coil_id'] ?? '' ?>"/>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Coil #'s</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Coil #</label>
                                <p class="form-control"><?= $row['entry_no'] ?? '' ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Sleeve #</label>
                                <p class="form-control"><?= $row['sleeve_code'] ?? '' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Coil Weight Info</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Purchased Weight (Lbs)</label>
                                <p class="form-control"><?= $row['purchase_weight'] ?? '' ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Starting Weight (Lbs)</label>
                                <p class="form-control" ><?= $row['starting_weight'] ?? '' ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Current Weight (Lbs)</label>
                                <p class="form-control"><?= $row['weight'] ?? '' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Coil Length Info</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Purchased Length (LF)</label>
                                <p class="form-control"><?= $row['purchase_length'] ?? '' ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Current Length (LF)</label>
                                <p class="form-control"><?= number_format(floatval($row['remaining_feet']),2) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Inventory Check</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Last Inventory Check (LF)</label>
                                <p class="form-control"><?= number_format(floatval($row['last_inventory'] ?? 0), 2) ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Current Inventory Check (LF)</label>
                                <input type="text" id="current_inventory" name="current_inventory" class="form-control" 
                                    style="border: 2px solid white !important;" 
                                    />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Coil Cost Info</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Total Purchase Price</label>
                                <p class="form-control"><?= $row['total_price'] ?? '' ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Purchase Price (LF)</label>
                                <p class="form-control"><?= $row['purchase_price'] ?? '' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Coil Color Info</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Actual Color/Sold As</label>
                                <p class="form-control"><?= getColorName($row['actual_color'] ?? '') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Sold As/Coil Condition</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Coil Condition Rating</label>
                                <p class="form-control"><?= getCoilConditionName($row['coil_condition'] ?? '') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0 fw-bold">Product Location Management</h5>
                </div>
                <div class="card-body border rounded p-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Warehouse</label>
                                <a href="?page=warehouses" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <div class="mb-2">
                                <select id="Warehouse_id" class="form-control select2" name="Warehouse_id">
                                    <option value="">Select Warehouse...</option>
                                    <optgroup label="Warehouse">
                                        <?php
                                        $query_warehouse = "SELECT * FROM warehouses";
                                        $result_warehouse = mysqli_query($conn, $query_warehouse);
                                        while ($w = mysqli_fetch_assoc($result_warehouse)) {
                                            $selected = ($row['warehouse'] == $w['WarehouseID']) ? 'selected' : '';
                                            $location = htmlspecialchars($w['Location'], ENT_QUOTES);
                                            echo "<option value='{$w['WarehouseID']}' data-location='{$location}' $selected>{$w['WarehouseName']}</option>";
                                        }
                                        ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8 mb-3 text-center">
                            <div class="card text-center p-2">
                                <h5>Warehouse Location</h4>
                                <p class="mb-0 warehouse_location"></p>
                            </div>
                        </div>

                        <div class="col-md-4 text-center">
                            <label class="form-label">Rack</label>
                            <div class="mb-2">
                                <select id="rack" class="form-control select2" name="rack">
                                    <option value="">N/A</option>
                                    <optgroup label="Rack">
                                        <?php
                                        $query_rack = "SELECT * FROM warehouse_rack WHERE hidden = '0'";
                                        $result_rack = mysqli_query($conn, $query_rack);
                                        while ($r = mysqli_fetch_assoc($result_rack)) {
                                            $selected = ($row['rack'] == $r['id']) ? 'selected' : '';
                                            echo "<option value='{$r['id']}' $selected>{$r['rack']}</option>";
                                        }
                                        ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 text-center">
                            <label class="form-label">Slot</label>
                            <div class="mb-2">
                                <select id="slot" class="form-control select2" name="slot">
                                    <option value="">N/A</option>
                                    <optgroup label="Slot">
                                        <?php
                                        $query_slot = "SELECT * FROM warehouse_slot WHERE hidden = '0'";
                                        $result_slot = mysqli_query($conn, $query_slot);
                                        while ($s = mysqli_fetch_assoc($result_slot)) {
                                            $selected = ($row['slot'] == $s['id']) ? 'selected' : '';
                                            echo "<option value='{$s['id']}' $selected>{$s['slot']}</option>";
                                        }
                                        ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4"></div>

                        <div class="col-md-4 text-center">
                            <label class="form-label">Shelf</label>
                            <div class="mb-2">
                                <select id="Shelves_id" class="form-control select2" name="Shelves_id">
                                    <option value="">N/A</option>
                                    <optgroup label="Shelf">
                                        <?php
                                        $query_shelf = "SELECT * FROM shelves";
                                        $result_shelf = mysqli_query($conn, $query_shelf);
                                        while ($s = mysqli_fetch_assoc($result_shelf)) {
                                            $selected = ($row['Shelves_id'] == $s['ShelfID']) ? 'selected' : '';
                                            echo "<option value='{$s['ShelfID']}' $selected>{$s['ShelfCode']}</option>";
                                        }
                                        ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 mb-2 text-center">
                            <label class="form-label">Row</label>
                            <div class="mb-2">
                                <select id="Row_id" class="form-control select2" name="Row_id">
                                    <option value="">N/A</option>
                                    <optgroup label="Row">
                                        <?php
                                        $query_rows = "SELECT * FROM warehouse_rows";
                                        $result_rows = mysqli_query($conn, $query_rows);
                                        while ($r = mysqli_fetch_assoc($result_rows)) {
                                            $selected = ($row['Row_id'] == $r['WarehouseRowID']) ? 'selected' : '';
                                            echo "<option value='{$r['WarehouseRowID']}' $selected>{$r['WarehouseRowID']}</option>";
                                        }
                                        ?>
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 mb-2 text-center">
                            <label class="form-label">Bin</label>
                            <div class="mb-2">
                                <select id="Bin_id" class="form-control select2" name="Bin_id">
                                    <option value="">N/A</option>
                                    <optgroup label="Bin">
                                        <?php
                                        $query_bin = "SELECT * FROM bins";
                                        $result_bin = mysqli_query($conn, $query_bin);
                                        while ($b = mysqli_fetch_assoc($result_bin)) {
                                            $selected = ($row['Bin_id'] == $b['BinID']) ? 'selected' : '';
                                            echo "<option value='{$b['BinID']}' $selected>{$b['BinCode']}</option>";
                                        }
                                        ?>
                                    </optgroup>
                                </select>
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
                function updateWarehouseLocation() {
                    var location = $('#Warehouse_id option:selected').data('location') || '';
                    $('.warehouse_location').text(location);
                }

                $(document).on('change', '#Warehouse_id', updateWarehouseLocation);

                $('#current_inventory').focus().select();

                $(".select2").each(function() {
                    $(this).select2({
                        dropdownParent: $(this).parent()
                    });
                });
            </script>
        
        <?php
        }
    }

    if ($action == "fetch_inventory_check_history") {
        $coil_id = mysqli_real_escape_string($conn, $_POST['id']);
        $sql = "
            SELECT 
                entry_no,
                sleeve_code,
                weight,
                remaining_feet,
                inventory_check_lf,
                coil_condition,
                warehouse,
                inventory_checked_at,
                checked_by
            FROM coil_inventory_check_history
            WHERE coil_id = '$coil_id'
            ORDER BY inventory_checked_at DESC
        ";

        $result = $conn->query($sql);

        $html = '
        <div class="datatables">
            <div class="table-responsive">
                <table id="inventoryCheckTable" class="table table-bordered table-striped w-100">
                    <thead class="table-dark">
                        <tr>
                            <th>Coil #</th>
                            <th>Sleeve #</th>
                            <th>Weight (Lbs)</th>
                            <th>Length (LF)</th>
                            <th>Inventory Check (LF)</th>
                            <th>Condition Rating</th>
                            <th>Warehouse</th>
                            <th>Check Date</th>
                            <th>Check Time</th>
                            <th>Checked By</th>
                        </tr>
                    </thead>
                    <tbody>';
        

        if ($result && $result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {

                $entry_no  = htmlspecialchars($row['entry_no'] ?? '', ENT_QUOTES);
                $sleeve    = htmlspecialchars($row['sleeve_code'] ?? '', ENT_QUOTES);

                $weight    = $row['weight'] !== null ? number_format($row['weight'], 2) : "0.00";
                $remaining = $row['remaining_feet'] !== null ? number_format($row['remaining_feet'], 2) : "0.00";
                $inv_lf    = $row['inventory_check_lf'] !== null ? number_format($row['inventory_check_lf'], 2) : "0.00";

                $condition = getCoilConditionName(htmlspecialchars($row['coil_condition'] ?? '', ENT_QUOTES));
                $warehouse = getWarehouseName(htmlspecialchars($row['warehouse'] ?? '', ENT_QUOTES));

                $date = $row['inventory_checked_at'] ? date("m/d/Y", strtotime($row['inventory_checked_at'])) : '';
                $time = $row['inventory_checked_at'] ? date("h:i A", strtotime($row['inventory_checked_at'])) : '';

                $checked_name = "";
                if (!empty($row['checked_by'])) {
                    $uid = (int)$row['checked_by'];
                    $checked_name = get_staff_name($uid);
                }

                $html .= "<tr>
                            <td>{$entry_no}</td>
                            <td>{$sleeve}</td>
                            <td>{$weight}</td>
                            <td>{$remaining}</td>
                            <td>{$inv_lf}</td>
                            <td>{$condition}</td>
                            <td>{$warehouse}</td>
                            <td>{$date}</td>
                            <td>{$time}</td>
                            <td>{$checked_name}</td>
                        </tr>";
            }

        } else {
            
        }

        $html .= '</tbody></table></div></div>';

        echo $html;
        exit;
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


    if ($_REQUEST['action'] == "download_excel") {
        $column_txt = implode(', ', array_keys($includedColumns));
        $sql = "SELECT $column_txt FROM $table";
        $result = $conn->query($sql);
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $sheets = [];

        while ($data = $result->fetch_assoc()) {
            $sheetName = $data['Product_id'] ? getProductName($data['Product_id']) : 'Uncategorized';
            $sheetName = sanitizeSheetTitle($sheetName);

            if (!isset($sheets[$sheetName])) {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle($sheetName);
                $sheets[$sheetName] = $sheet;

                $colIndex = 0;
                foreach ($includedColumns as $dbColumn => $displayName) {
                    $columnLetter = ($colIndex >= 26) ? indexToColumnLetter($colIndex) : chr(65 + $colIndex);
                    $sheet->setCellValue($columnLetter . '1', $displayName);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                    $colIndex++;
                }

                $headerRange = 'A1:' . $sheet->getHighestColumn() . '1';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9D9D9']
                    ],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]);
            }

            $sheet = $sheets[$sheetName];
            $row = $sheet->getHighestRow() + 1;

            $colIndex = 0;
            foreach ($includedColumns as $dbColumn => $displayName) {
                $columnLetter = ($colIndex >= 26) ? indexToColumnLetter($colIndex) : chr(65 + $colIndex);
                $sheet->setCellValue($columnLetter . $row, $data[$dbColumn] ?? '');
                $colIndex++;
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        $downloadDir = 'downloads';
        if (!is_dir($downloadDir)) mkdir($downloadDir, 0777, true);

        $timestamp = date('Ymd_His');
        $filename = "Coil Product_{$timestamp}.xlsx";
        $filePath = $downloadDir . '/' . $filename;

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=0');
        readfile($filePath);
        unlink($filePath);
        exit;
    }

    if ($action == "upload_excel") {
        if (!isset($_FILES['excel_file'])) {
            echo "No file uploaded.";
            exit;
        }

        $fileTmpPath = $_FILES['excel_file']['tmp_name'];
        $fileExtension = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, ["xlsx", "xls"])) {
            echo "Please upload a valid Excel file.";
            exit;
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileTmpPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            echo "The uploaded file is empty.";
            exit;
        }

        $headers = $rows[0];
        $dbColumns = [];
        foreach ($headers as $header) {
            foreach ($includedColumns as $dbCol => $displayName) {
                if ($displayName === $header) {
                    $dbColumns[] = $dbCol;
                    break;
                }
            }
        }

        if (empty($dbColumns)) {
            echo "No matching columns found in uploaded file.";
            exit;
        }

        if (!$conn->query("TRUNCATE TABLE $test_table")) {
            echo "Error truncating table: " . $conn->error;
            exit;
        }

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            $data = array_combine($dbColumns, $row);

            $allEmpty = true;
            foreach ($data as $v) {
                if (trim($v) !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) continue;

            $columnNames = implode(", ", array_map(fn($col) => "`$col`", array_keys($data)));
            $columnValues = implode("', '", array_map(fn($v) => mysqli_real_escape_string($conn, $v ?? ''), array_values($data)));

            $sql = "INSERT INTO $test_table ($columnNames) VALUES ('$columnValues')";
            if (!$conn->query($sql)) {
                echo "Error inserting data on row " . ($index + 1) . ": " . $conn->error;
                exit;
            }
        }

        echo "success";
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
            $allRows = [];
            while ($row = $result->fetch_assoc()) {
                $allRows[] = $row;
            }

            $columns = array_intersect(array_keys($includedColumns), array_keys($allRows[0] ?? []));

            ?>
            <div class="card card-body shadow" data-table="<?= $table ?>">
                <form id="tableForm">
                    <div style="overflow-x: auto; overflow-y: auto; max-height: 80vh; max-width: 100%;">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                            <tr>
                                <?php
                                foreach ($columns as $column) {
                                    $formattedColumn = $includedColumns[$column];
                                    echo "<th class='fs-4'>$formattedColumn</th>";
                                }
                                ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($allRows as $row) {
                                $primaryValue = $row[$test_primary] ?? '';
                                echo '<tr>';
                                foreach ($columns as $column) {
                                    $value = htmlspecialchars($row[$column] ?? '', ENT_QUOTES, 'UTF-8');
                                    echo "<td contenteditable='true' class='table_data' data-header-name='$column' data-id='$primaryValue'>$value</td>";
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
