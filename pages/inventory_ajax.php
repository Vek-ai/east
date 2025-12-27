<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';
require '../includes/phpmailer/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Picqer\Barcode\BarcodeGeneratorPNG;

$table = 'inventory';
$test_table = 'inventory_excel';

$includedColumns = [
    'i.Inventory_id'        => 'Product Entry #',
    'prod_abbrev'           => 'Product ID #',
    'p.product_category'    => 'Product Category',
    'i.product_line'        => 'Product Line',
    'i.product_type'        => 'Product Type',
    'i.grade'               => 'Product Grade',
    'i.gauge'               => 'Product Gauge',
    'i.dimension_id'        => 'Length',
    'c.color_group'         => 'Color Group',
    'i.color_id'          => 'Color Name',
    'i.Product_id'          => 'Description',
    'p.abbreviation'        => 'Abbreviation',
    'i.quantity_ttl'        => 'Qty on Hand',
    'i.reorder_level'       => 'Reorder Qty',
    'w.WarehouseName'       => 'Warehouse',
    'w.Location'            => 'Warehouse Location',
    'wr.rack'               => 'Rack',
    'ws.slot'               => 'Slot',
    's.ShelfCode'           => 'Shelf',
    'r.RowCode'             => 'Row',
    'b.BinCode'             => 'Bin',
];

function wrapTextGD($text, $maxCharsPerLine) {
    $words = explode(' ', $text);
    $lines = [];
    $current = '';

    foreach ($words as $word) {
        if (strlen($current . ' ' . $word) <= $maxCharsPerLine) {
            $current = trim($current . ' ' . $word);
        } else {
            $lines[] = $current;
            $current = $word;
        }
    }

    if ($current !== '') {
        $lines[] = $current;
    }

    return $lines;
}

function cachedColorName($id) {
    static $cache = [];
    if (!isset($cache[$id])) {
        $cache[$id] = getColorName($id);
    }
    return $cache[$id];
}

function cachedGradeName($id) {
    static $cache = [];
    if (!isset($cache[$id])) {
        $cache[$id] = getGradeName($id);
    }
    return $cache[$id];
}

function cachedGaugeName($id) {
    static $cache = [];
    if (!isset($cache[$id])) {
        $cache[$id] = getGaugeName($id);
    }
    return $cache[$id];
}

function cachedWarehouseName($id) {
    static $cache = [];
    if (!isset($cache[$id])) {
        $cache[$id] = getWarehouseName($id);
    }
    return $cache[$id];
}

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $Product_id   = mysqli_real_escape_string($conn, $_POST['Product_id']);
        $product_type = mysqli_real_escape_string($conn, $_POST['product_type']);
        $product_line = mysqli_real_escape_string($conn, $_POST['product_line']);
        $color_id     = mysqli_real_escape_string($conn, $_POST['color_id']);
        $Warehouse_id = mysqli_real_escape_string($conn, $_POST['Warehouse_id']);
        $rack         = mysqli_real_escape_string($conn, $_POST['rack']);
        $slot         = mysqli_real_escape_string($conn, $_POST['slot']);
        $Shelves_id   = mysqli_real_escape_string($conn, $_POST['Shelves_id']);
        $Bin_id       = mysqli_real_escape_string($conn, $_POST['Bin_id']);
        $Row_id       = mysqli_real_escape_string($conn, $_POST['Row_id']);
        $Date         = mysqli_real_escape_string($conn, $_POST['Date']);
        $grade        = mysqli_real_escape_string($conn, $_POST['grade']);
        $gauge        = mysqli_real_escape_string($conn, $_POST['gauge']);
        $dimension_id = mysqli_real_escape_string($conn, $_POST['dimension_id']);
        $quantity_ttl = (int)($_POST['quantity_ttl'] ?? 0);
        $reorder_level = (int)($_POST['reorder_level'] ?? 0);
        $addedby      = $_SESSION['userid'];

        if ($quantity_ttl <= 0) die("Quantity must be greater than 0");

        $checkQuery = "
            SELECT inventory_id, quantity_ttl
            FROM inventory
            WHERE Product_id='$Product_id'
            AND product_type='$product_type'
            AND product_line='$product_line'
            AND color_id='$color_id'
            AND grade='$grade'
            AND gauge='$gauge'
            AND dimension_id='$dimension_id'
            LIMIT 1
        ";
        $resCheck = mysqli_query($conn, $checkQuery);
        if (!$resCheck) die("Error checking inventory: " . mysqli_error($conn));

        $now = date("Y-m-d H:i:s");

        if (mysqli_num_rows($resCheck) > 0) {
            $rowInv = mysqli_fetch_assoc($resCheck);
            $inventory_id = $rowInv['inventory_id'];
            $new_qty = (int)$rowInv['quantity_ttl'] + $quantity_ttl;

            $updateQuery = "
                UPDATE inventory SET
                    product_type='$product_type',
                    product_line='$product_line',
                    Warehouse_id='$Warehouse_id',
                    rack='$rack',
                    slot='$slot',
                    Shelves_id='$Shelves_id',
                    Bin_id='$Bin_id',
                    Row_id='$Row_id',
                    Date='$Date',
                    quantity_ttl='$new_qty',
                    reorder_level='$reorder_level',
                    addedby='$addedby',
                    last_edit='$now',
                    edited_by='$addedby'
                WHERE inventory_id='$inventory_id'
            ";
            if (!mysqli_query($conn, $updateQuery)) die("Error updating inventory: " . mysqli_error($conn));
        } else {
            $insertQuery = "
                INSERT INTO inventory
                    (Product_id, product_type, product_line, color_id, grade, gauge, dimension_id, Warehouse_id, rack, slot, Shelves_id, Bin_id, Row_id, Date, quantity_ttl, reorder_level, addedby, last_edit, edited_by)
                VALUES
                    ('$Product_id', '$product_type', '$product_line', '$color_id', '$grade', '$gauge', '$dimension_id', '$Warehouse_id', '$rack', '$slot', '$Shelves_id', '$Bin_id', '$Row_id', '$Date', '$quantity_ttl', '$reorder_level', '$addedby', '$now', '$addedby')
            ";
            if (!mysqli_query($conn, $insertQuery)) die("Error inserting inventory: " . mysqli_error($conn));
        }

        echo "success";
    }

    if ($action == "fetch_modal") {

        $Inventory_id = (int)($_POST['inv'] ?? 0);

        $row = [];
        $Product_id = 0;
        $color_id = 0;
        if (!empty($Inventory_id)) {
            $query_inventory = "SELECT * FROM inventory WHERE Inventory_id = '$Inventory_id' LIMIT 1";
            $res = mysqli_query($conn, $query_inventory);
            if ($res) $row = mysqli_fetch_assoc($res);
        }
        $result_inventory = mysqli_query($conn, $query_inventory);
        if ($result_inventory && mysqli_num_rows($result_inventory) > 0) {
            $row = mysqli_fetch_assoc($result_inventory);
            $Product_id = ($row['Product_id']);
            $color_id = ($row['color_id']);
        }

        $product_details = getProductDetails($Product_id);
        $category_id = $product_details['product_category'];
        $color_details = getColorDetails($color_id);
        ?>
        <input type="hidden" id="Product_id" name="Product_id" value="<?= $Product_id ?>" />
        <input type="hidden" id="product_type" name="product_type" value="<?= $row['product_type'] ?>" />
        <input type="hidden" id="product_line" name="product_line" value="<?= $row['product_line'] ?>" />
        <input type="hidden" id="grade" name="grade" value="<?= $row['grade'] ?>" />
        <input type="hidden" id="gauge" name="gauge" value="<?= $row['gauge'] ?>" />
        <input type="hidden" id="color_id" name="color_id" value="<?= $row['color_id'] ?>" />
        <input type="hidden" id="dimension_id" name="dimension_id" value="<?= $row['dimension_id'] ?>" />

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Inventory Identifier</h5>
            </div>
            <script>console.log(<?= print_r($row) ?>);</script>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Product Category</h4>
                            <p class="mb-0"><?= getProductCategoryName($product_details['product_category']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Product Line</h4>
                            <p class="mb-0"><?= getProductCategoryName($row['product_line'] ?? '') ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Product Type</h4>
                            <p class="mb-0"><?= getProductTypeName($row['product_type'] ?? ''); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Grade</h4>
                            <p class="mb-0"><?= getGradeName($row['grade'] ?? ''); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Gauge</h4>
                            <p class="mb-0"><?= getGaugeName($row['gauge'] ?? ''); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Length</h4>
                            <p class="mb-0"><?= getDimensionName($row['dimension_id'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Inventory Color Mapping</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Color Group</h4>
                            <p class="mb-0"><?= getColorGroupName($color_details['color_group']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Color Name</h4>
                            <p class="mb-0"><?= getColorName($row['color_id'] ?? '') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Inventory Information</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <div class="card text-center p-2">
                            <h5>Product Description</h4>
                            <p class="mb-0"><?= $product_details['product_item'] ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Abbreviation</h4>
                            <p class="mb-0"><?= $product_details['abbreviation'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Inventory Management</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <label for="quantity_ttl" class="form-label fw-bold">Quantity on Hand</label>
                            <input 
                                type="number" 
                                class="form-control" 
                                name="quantity_ttl" 
                                id="quantity_ttl" 
                                value="<?= $row['quantity_ttl'] ?? 0 ?>"
                            >
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <label for="reorder_level" class="form-label fw-bold">Reorder Qty</label>
                            <input 
                                type="number" 
                                class="form-control" 
                                name="reorder_level" 
                                id="reorder_level" 
                                value="<?= $row['reorder_level'] ?? 0 ?>"
                            >
                        </div>
                    </div>

                    <div class="col-md-4 d-flex justify-content-center align-middle">
                        <?php
                        $screw_id = 16;
                        if($category_id == $screw_id){
                            ?>
                            <div class="p-2">
                                <button type="button" class="btn btn-primary updateInventoryPack" data-id="<?= $Inventory_id ?>">Update Pack Qtys</button>
                            </div>
                            <?php

                        }
                        ?>
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
                            <select id="Warehouse_id" class="form-control select2-inventory" name="Warehouse_id">
                                <option value="">Select Warehouse...</option>
                                <optgroup label="Warehouse">
                                    <?php
                                    $query_warehouse = "SELECT * FROM warehouses";
                                    $result_warehouse = mysqli_query($conn, $query_warehouse);
                                    while ($w = mysqli_fetch_assoc($result_warehouse)) {
                                        $selected = ($row['Warehouse_id'] == $w['WarehouseID']) ? 'selected' : '';
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
                            <select id="rack" class="form-control select2-inventory" name="rack">
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
                            <select id="slot" class="form-control select2-inventory" name="slot">
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
                            <select id="Shelves_id" class="form-control select2-inventory" name="Shelves_id">
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
                            <select id="Row_id" class="form-control select2-inventory" name="Row_id">
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
                            <select id="Bin_id" class="form-control select2-inventory" name="Bin_id">
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

        <div class="row pt-3">
            

            
        </div>
        <?php
        
    }

    if ($action == "fetch_pack_modal") {

        $Inventory_id = (int)($_POST['id'] ?? 0);

        $row = [];
        if (!empty($Inventory_id)) {
            $query_inventory = "SELECT * FROM inventory WHERE Inventory_id = '$Inventory_id' LIMIT 1";
            $res = mysqli_query($conn, $query_inventory);
            if ($res) $row = mysqli_fetch_assoc($res);
        }

        $product_details = getProductDetails($row['Product_id']);
        $packs_assigned = $product_details['pack'] ?? [];

        if (is_string($packs_assigned)) {
            $packs_assigned = json_decode($packs_assigned, true);
        }

        if (!is_array($packs_assigned)) {
            $packs_assigned = [];
        }
        ?>

        <style>
        .readonly {
            border: none !important;
            background-color: transparent !important;
            pointer-events: none;
            box-shadow: none;
            color: #495057;
            padding-left: 0;
            padding-right: 0;
        }
        </style>

        <input type="hidden" id="Inventory_id" name="Inventory_id" value="<?= $Inventory_id ?>" />

        <div class="row g-3 mb-3">
            <div class="col-2">
                <label class="form-label">Total Qty on Hand</label>
                <input type="text" class="form-control readonly" id="total_qty_on_hand" value="<?= $row['quantity_ttl'] ?? 0 ?>">
            </div>
            <div class="col-2">
                <label class="form-label">Baseline Reorder Qty</label>
                <input type="text" class="form-control readonly" id="baseline_reorder_qty" value="<?= $row['reorder_level'] ?? 0 ?>">
            </div>
            <div class="col-2">
                <label class="form-label">Qty Left on Hand</label>
                <input type="text" class="form-control readonly" id="qty_left_on_hand" readonly>
            </div>
            <div class="col-2">
                <label class="form-label">Total Qty Needed</label>
                <input type="text" class="form-control readonly" id="total_qty_needed" readonly>
            </div>
            <div class="col-2">
                <label class="form-label">Total Reorder Qty Needed</label>
                <input type="text" class="form-control readonly" id="total_reorder_needed" readonly>
            </div>
        </div>


        <hr>

        <?php
        foreach ($packs_assigned as $pack_id) {

            $pack_details = getSupplierPackDetails($pack_id);
            $pack_name = $pack_details['pack'] ?? '';
            $pack_count = $pack_details['pack_count'] ?? 0;

            $query_pack = "SELECT * FROM inventory_pack WHERE inventory_id = '$Inventory_id' AND pack_id = '$pack_id' LIMIT 1";
            $res_pack = mysqli_query($conn, $query_pack);
            $pack_row = $res_pack && mysqli_num_rows($res_pack) > 0 ? mysqli_fetch_assoc($res_pack) : [];

            $pack_qty_on_hand = $pack_row['qty'] ?? 0;
            $pack_reorder_qty = $pack_row['reorder_qty'] ?? 0;
            $qty_needed = 0;
            $reorder_needed = 0;
            ?>
            <div class="row g-3 mb-2">
                <div class="col-2">
                    <label class="form-label">Pack Name</label>
                    <p class="form-control readonly"><?= htmlspecialchars($pack_name) ?></p>
                </div>
                <div class="col-2">
                    <label class="form-label">Qty in Pack</label>
                    <p class="form-control readonly"><?= number_format($pack_count) ?></p>
                </div>
                <div class="col-2">
                    <label class="form-label">Pack Qty on Hand</label>
                    <input type="text" class="form-control calc-qty" 
                        data-qty-in-pack="<?= $pack_count ?>" 
                        name="pack_qty_on_hand[<?= $pack_id ?>]" 
                        value="<?= $pack_qty_on_hand ?>">
                </div>

                <div class="col-2">
                    <label class="form-label">Pack Reorder Qty</label>
                    <input type="text" class="form-control calc-qty" 
                        data-qty-in-pack="<?= $pack_count ?>" 
                        name="pack_reorder_qty[<?= $pack_id ?>]" 
                        value="<?= $pack_reorder_qty ?>">
                </div>

                <div class="col-2">
                    <label class="form-label">Qty Needed</label>
                    <input type="text" class="form-control qty-needed readonly" readonly>
                </div>

                <div class="col-2">
                    <label class="form-label">Reorder Qty Needed</label>
                    <input type="text" class="form-control reorder-needed readonly" readonly>
                </div>
            </div>
        <?php
        }
    }

    if ($action == "update_pack") {
        $Inventory_id = (int)($_POST['Inventory_id'] ?? 0);
        $packs_json = $_POST['packs'] ?? '[]';
        $packs = json_decode($packs_json, true);

        if (!is_array($packs)) {
            echo "Invalid packs data.";
            exit;
        }

        foreach ($packs as $p) {
            $pack_id = (int)($p['pack_id'] ?? 0);
            $qty = (float)($p['qty'] ?? 0);
            $reorder_qty = (float)($p['reorder_qty'] ?? 0);

            if ($pack_id <= 0) continue;
            $check = mysqli_query($conn, "SELECT id FROM inventory_pack WHERE inventory_id='$Inventory_id' AND pack_id='$pack_id' LIMIT 1");
            if ($check && mysqli_num_rows($check) > 0) {
                $update_sql = "UPDATE inventory_pack 
                            SET qty='$qty', reorder_qty='$reorder_qty' 
                            WHERE inventory_id='$Inventory_id' AND pack_id='$pack_id'";
                mysqli_query($conn, $update_sql);
            } else {
                $insert_sql = "INSERT INTO inventory_pack (inventory_id, pack_id, qty, reorder_qty) 
                            VALUES ('$Inventory_id', '$pack_id', '$qty', '$reorder_qty')";
                mysqli_query($conn, $insert_sql);
            }
        }

        echo "success";
        exit;
    }

    if ($_POST['action'] == 'fetch_table') {
        $draw = intval($_POST['draw'] ?? 1);
        $start = intval($_POST['start'] ?? 0);
        $length = intval($_POST['length'] ?? 100);
        $isStock = $_POST['isStock'] ?? 0;

        $filters = ["p.status = 1", "p.hidden = 0"];
        if (!empty($_POST['category'])) $filters[] = "p.product_category IN (" . implode(',', array_map('intval', explode(',', $_POST['category']))) . ")";
        if (!empty($_POST['line'])) $filters[] = "i.product_line IN (" . implode(',', array_map('intval', explode(',', $_POST['line']))) . ")";
        if (!empty($_POST['type'])) $filters[] = "i.product_type IN (" . implode(',', array_map('intval', explode(',', $_POST['type']))) . ")";
        if (!empty($_POST['color'])) $filters[] = "i.color_id IN (" . implode(',', array_map('intval', explode(',', $_POST['color']))) . ")";
        if (!empty($_POST['grade'])) $filters[] = "i.grade IN (" . implode(',', array_map('intval', explode(',', $_POST['grade']))) . ")";
        if (!empty($_POST['gauge'])) $filters[] = "i.gauge IN (" . implode(',', array_map('intval', explode(',', $_POST['gauge']))) . ")";

        if (!empty($_POST['supplier'])) $filters[] = "i.supplier_id IN (" . implode(',', array_map('intval', explode(',', $_POST['supplier']))) . ")";
        if (!empty($_POST['warehouse'])) $filters[] = "i.Warehouse_id IN (" . implode(',', array_map('intval', explode(',', $_POST['warehouse']))) . ")";
        if (!empty($_POST['shelf'])) $filters[] = "i.Shelves_id IN (" . implode(',', array_map('intval', explode(',', $_POST['shelf']))) . ")";
        if (!empty($_POST['bin'])) $filters[] = "i.Bin_id IN (" . implode(',', array_map('intval', explode(',', $_POST['bin']))) . ")";
        if (!empty($_POST['rowFilter'])) $filters[] = "i.Row_id IN (" . implode(',', array_map('intval', explode(',', $_POST['rowFilter']))) . ")";
        if (!empty($_POST['textSearch'])) {
            $search = $conn->real_escape_string(strtolower($_POST['textSearch']));
            $filters[] = "LOWER(p.product_item) LIKE '%$search%'";
        }
        if ($isStock) $filters[] = "i.quantity_ttl > 0";

        $whereSql = "WHERE " . implode(" AND ", $filters);

        $columns = [
            0 => 'p.product_item',
            1 => 'i.color_id',
            2 => 'i.grade',
            3 => 'i.gauge',
            4 => 'i.dimension_id',
            5 => 'i.Warehouse_id',
            6 => 'i.quantity_ttl',
            7 => 'i.last_edit',
            8 => 'i.edited_by',
            9 => 'i.status'
        ];
        $orderSql = '';
        if (!empty($_POST['order'][0])) {
            $colIndex = intval($_POST['order'][0]['column']);
            $dir = $_POST['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';
            if (isset($columns[$colIndex])) $orderSql = "ORDER BY " . $columns[$colIndex] . " $dir";
        }

        $totalRes = $conn->query("SELECT COUNT(*) as cnt FROM inventory i JOIN product p ON i.Product_id = p.product_id WHERE p.status=1 AND p.hidden=0");
        $recordsTotal = $totalRes->fetch_assoc()['cnt'];

        $filteredRes = $conn->query("SELECT COUNT(*) as cnt FROM inventory i JOIN product p ON i.Product_id = p.product_id $whereSql");
        $recordsFiltered = $filteredRes->fetch_assoc()['cnt'];

        $sql = "
            SELECT i.*, p.product_item, p.product_category
            FROM inventory i
            JOIN product p ON i.Product_id = p.product_id
            $whereSql
            $orderSql
            LIMIT $start, $length
        ";
        $res = $conn->query($sql);

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $prod_abbrev = getProdID([
                'category' => $row['product_category'],
                'type'     => $row['product_type'],
                'line'     => $row['product_line'],
                'grade'    => $row['grade'],
                'gauge'    => $row['gauge'],
                'color'    => $row['color_id']
            ]);

            $lastEdit = $row['last_edit'] ? date('m/d/Y', strtotime($row['last_edit'])) : '';
            $staff = ($row['edited_by'] ?? 0) > 0 ? get_staff_name($row['edited_by']) : '';
            $statusHtml = ($row['status'] ?? 0) == 0
                ? "<span class='alert alert-primary py-1 px-2 my-0'>New</span>"
                : "<span class='alert alert-success py-1 px-2 my-0'>Transferred</span>";

            $data[] = [
                $prod_abbrev,
                $row['product_item'],
                cachedColorName($row['color_id']),
                cachedGradeName($row['grade']),
                cachedGaugeName($row['gauge']),
                cachedWarehouseName($row['Warehouse_id']),
                $row['quantity_ttl'],
                $lastEdit,
                $staff,
                $statusHtml,
                '<a href="#" id="view_inventory_btn" 
                    data-type="'.trim($row['product_type']).'"
                    data-line="'.trim($row['product_line']).'"
                    data-grade="'.trim($row['grade']).'"
                    data-gauge="'.trim($row['gauge']).'"
                    data-color="'.trim($row['color_id']).'"
                    data-dim="'.trim($row['dimension_id']).'"
                    data-id="'.trim($row['Product_id']).'"
                    data-inv="'.trim($row['Inventory_id']).'">
                        <i class="ti ti-pencil fs-5"></i>
                </a>
                <a href="#" id="view_qr_barcode" 
                    data-id="'.intval($row['Product_id']).'">
                        <i class="ti ti-qrcode text-warning fs-5"></i>
                </a>
                <a href="#" id="view_warehouse_qr" 
                    data-id="'.intval($row['Warehouse_id']).'">
                        <i class="ti ti-building-warehouse text-info fs-5"></i>
                </a>'
            ];
        }

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
        exit;
    }

    if ($action == "change_status") {
        $Inventory_id = mysqli_real_escape_string($conn, $_POST['inventory_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE inventory SET status = '$new_status' WHERE Inventory_id = '$Inventory_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == "fetch_supplier_packs") {
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    
        $query_pack = "SELECT * FROM supplier_pack WHERE supplierid = '$supplier_id' AND hidden = '0'";
        $result_pack = mysqli_query($conn, $query_pack);

        $packs = [];
        while ($row_pack = mysqli_fetch_assoc($result_pack)) {
            $packs[] = [
                'id' => $row_pack['id'],
                'pack' => $row_pack['pack'],
                'pack_count' => $row_pack['pack_count']
            ];
        }
        
        echo json_encode($packs);
    }

    if ($action == "fetch_supplier_cases") {
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    
        $query_case = "SELECT * FROM supplier_case WHERE supplierid = '$supplier_id' AND hidden = '0'";
        $result_case = mysqli_query($conn, $query_case);

        $cases = [];
        while ($row_case = mysqli_fetch_assoc($result_case)) {
            $cases[] = [
                'id' => $row_case['id'],
                'case' => $row_case['case'],
                'case_count' => $row_case['case_count']
            ];
        }
        
        echo json_encode($cases);
    }

    if ($_REQUEST['action'] == "download_excel") {
        $selectCols = array_filter(array_keys($includedColumns), fn($k) => strpos($k, '.') !== false);
        $column_txt = implode(', ', $selectCols);

        $sql = "SELECT $column_txt
                FROM $table AS i
                LEFT JOIN product AS p ON i.Product_id = p.product_id
                LEFT JOIN paint_colors AS c ON i.color_id = c.color_id
                LEFT JOIN product_color AS pc ON c.color_group = pc.id
                LEFT JOIN warehouses AS w ON i.Warehouse_id = w.WarehouseID
                LEFT JOIN warehouse_rack AS wr ON i.rack = wr.id
                LEFT JOIN warehouse_slot AS ws ON i.slot = ws.id
                LEFT JOIN shelves AS s ON i.Shelves_id = s.ShelfID
                LEFT JOIN warehouse_rows AS r ON i.Row_id = r.WarehouseRowID
                LEFT JOIN bins AS b ON i.Bin_id = b.BinID
                WHERE 1";
        
        $product_category = $_REQUEST['category'] ?? [];
        if (!empty($product_category)) {
            $escaped = array_map(fn($id) => intval($id), $product_category);
            $sql .= " AND p.product_category IN (" . implode(',', $escaped) . ")";
        }

        $product_id = $_REQUEST['product_id'] ?? [];
        if (!empty($product_id)) {
            $ids = array_map('intval', (array)$product_id);
            $sql .= " AND i.Product_id IN (" . implode(',', $ids) . ")";
        }

        if (empty($product_category) && empty($product_id)) {
            echo "No category or product selected.";
            exit;
        }

        $result = $conn->query($sql);
        $spreadsheet = new Spreadsheet();

        $spreadsheet->removeSheetByIndex(0);

        $sheets = [];

        while ($data = $result->fetch_assoc()) {

            $sheetName = ($data['Product_id'] ? getProductName($data['Product_id']) : 'Uncategorized');
            $sheetName = sanitizeSheetTitle($sheetName);

            if (!isset($sheets[$sheetName])) {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle($sheetName);
                $sheets[$sheetName] = $sheet;

                $colIndex = 0;
                foreach ($includedColumns as $dbColumn => $displayName) {
                    $columnLetter = ($colIndex >= 26) ? indexToColumnLetter($colIndex) : chr(65 + $colIndex);
                    $sheet->setCellValue($columnLetter . '1', $displayName);

                    if ($dbColumn == 'i.Inventory_id') {
                        $sheet->getColumnDimension($columnLetter)->setVisible(false);
                    }
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

                foreach (range('A', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }

            $sheet = $sheets[$sheetName];
            $row = $sheet->getHighestRow() + 1;

            $prod_abbrev = getProdID([
                'category' => $data['product_category'] ?? null,
                'line'     => $data['product_line'] ?? null,
                'type'     => $data['product_type'] ?? null,
                'grade'    => $data['grade'] ?? null,
                'gauge'    => $data['gauge'] ?? null,
                'color'    => $data['color_id'] ?? null,
                'length'   => $data['dimension_id'] ?? null,
            ]);

            $colIndex = 0;
            foreach ($includedColumns as $dbColumn => $displayName) {
                $columnLetter = ($colIndex >= 26) ? indexToColumnLetter($colIndex) : chr(65 + $colIndex);

                if ($dbColumn === 'prod_abbrev') {
                    $value = $prod_abbrev;
                } else {
                    $rawColumn = preg_replace('/^[a-z0-9_]+\./i', '', $dbColumn);
                    $value = $data[$rawColumn] ?? '';

                    switch ($rawColumn) {
                        case 'product_line':     $value = getProductLineName($value); break;
                        case 'product_type':     $value = getProductTypeName($value); break;
                        case 'grade':            $value = getGradeName($value); break;
                        case 'gauge':            $value = getGaugeName($value); break;
                        case 'color_id':         $value = getColorName($value); break;
                        case 'dimension_id':     $value = getDimensionName($value); break;
                        case 'Product_id':       $value = getProductName($value); break;
                        case 'product_category': $value = getProductCategoryName($value); break;
                        case 'color_group':      $value = getColorGroupName($value); break;
                    }
                }

                $sheet->setCellValue($columnLetter . $row, $value);
                $colIndex++;
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        $downloadDir = 'downloads';
        if (!is_dir($downloadDir)) mkdir($downloadDir, 0777, true);

        $timestamp = date('Ymd_His');
        $filename = "inventory_{$timestamp}.xlsx";
        $filePath = $downloadDir . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
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

        $spreadsheet = IOFactory::load($fileTmpPath);
        $inventoryColumns = [
            'Inventory_id',
            'Product_id',
            'product_line',
            'product_type',
            'color_id',
            'grade',
            'gauge',
            'Warehouse_id',
            'rack',
            'slot',
            'Shelves_id',
            'Bin_id',
            'Row_id',
            'dimension_id',
            'quantity_ttl',
            'reorder_level'
        ];

        if (!$conn->query("TRUNCATE TABLE $test_table")) {
            echo "Error truncating table: " . $conn->error;
            exit;
        }
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $rows = $sheet->toArray();
            if (count($rows) < 2) continue;

            $headers = $rows[0];
            $headerMap = [];

            foreach ($headers as $i => $header) {
                foreach ($includedColumns as $col => $displayName) {
                    $plainCol = preg_replace('/^[a-z]+\./i', '', $col);
                    if ($displayName === $header && in_array($plainCol, $inventoryColumns)) {
                        $headerMap[$i] = $plainCol;
                    }
                }
            }

            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex === 0) continue;

                $data = [];
                $allEmpty = true;

                foreach ($headerMap as $colIndex => $dbCol) {
                    $cellValue = isset($row[$colIndex]) ? trim($row[$colIndex]) : '';
                    if ($cellValue !== '') $allEmpty = false;

                    switch ($dbCol) {
                        case 'product_line':
                            $cellValue = getIDByName('product_line', 'product_line', $cellValue);
                            break;
                        case 'product_type':
                            $cellValue = getIDByName('product_type', 'product_type', $cellValue);
                            break;
                        case 'grade':
                            $cellValue = getIDByName('product_grade', 'product_grade', $cellValue);
                            break;
                        case 'gauge':
                            $cellValue = getIDByName('product_gauge', 'product_gauge', $cellValue);
                            break;
                        case 'color_id':
                            $cellValue = getIDByName('paint_colors', 'color_name', $cellValue);
                            break;
                        case 'dimension_id':
                            $cellValue = getIDByName('dimensions', 'dimension', $cellValue);
                            break;
                        case 'Product_id':
                            $cellValue = getIDByName('product', 'product_item', $cellValue);
                            break;
                        case 'Warehouse_id':
                            $cellValue = getIDByName('warehouses', 'WarehouseName', $cellValue);
                            break;
                        case 'rack':
                            $cellValue = getIDByName('warehouse_rack', 'rack', $cellValue);
                            break;
                        case 'slot':
                            $cellValue = getIDByName('warehouse_slot', 'slot', $cellValue);
                            break;
                        case 'Shelves_id':
                            $cellValue = getIDByName('shelves', 'ShelfCode', $cellValue);
                            break;
                        case 'Bin_id':
                            $cellValue = getIDByName('bins', 'BinCode', $cellValue);
                            break;
                        case 'Row_id':
                            $cellValue = getIDByName('warehouse_rows', 'RowCode', $cellValue);
                            break;
                    }

                    $data[$dbCol] = mysqli_real_escape_string($conn, $cellValue);
                }

                if ($allEmpty) continue;

                $columnNames = implode(", ", array_keys($data));
                $columnValues = implode("', '", array_values($data));

                $sql = "INSERT INTO $test_table ($columnNames) VALUES ('$columnValues')";
                if (!$conn->query($sql)) {
                    echo "Error inserting data on sheet '{$sheet->getTitle()}', row " . ($rowIndex + 1) . ": " . $conn->error;
                    exit;
                }
            }
        }

        echo "success";
    }

    if ($action == "fetch_uploaded_modal") {
        $test_primary = getPrimaryKey($test_table);

        $selectCols = [];
        foreach ($includedColumns as $dbColumn => $displayName) {
            if ($dbColumn === 'prod_abbrev') continue;
            $rawColumn = preg_replace('/^[a-z0-9_]+\./i', '', $dbColumn);
            $selectCols[] = "$dbColumn AS `$rawColumn`";
        }
        $column_txt = implode(', ', $selectCols);

        $sql = "SELECT $column_txt
                FROM $test_table AS i
                LEFT JOIN product AS p ON i.Product_id = p.product_id
                LEFT JOIN paint_colors AS c ON i.color_id = c.color_id
                LEFT JOIN product_color AS pc ON c.color_group = pc.id
                LEFT JOIN warehouses AS w ON i.Warehouse_id = w.WarehouseID
                LEFT JOIN warehouse_rack AS wr ON i.rack = wr.id
                LEFT JOIN warehouse_slot AS ws ON i.slot = ws.id
                LEFT JOIN shelves AS s ON i.Shelves_id = s.ShelfID
                LEFT JOIN warehouse_rows AS r ON i.Row_id = r.WarehouseRowID
                LEFT JOIN bins AS b ON i.Bin_id = b.BinID
                WHERE 1";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            ?>
            <style>
                .table-bordered-custom {
                    border-collapse: collapse;
                    width: 100%;
                }
                .table-bordered-custom th,
                .table-bordered-custom td {
                    border: 1px solid #555;
                    padding: 8px 10px;
                    text-align: center;
                }
                .table-bordered-custom th {
                    font-weight: bold;
                    font-size: 1rem;
                }
                .table-bordered-custom td.editable {
                    border: 5px solid #fff;
                    cursor: pointer;
                }
                .table-bordered-custom td.editable:focus {
                    outline: none;
                }
            </style>

            <div class="card card-body shadow" data-table="<?= $table ?>">
                <form id="tableForm">
                    <div style="overflow-x: auto; overflow-y: auto; max-height: 80vh; max-width: 100%;">
                        <table class="table-bordered-custom text-center">
                            <thead>
                                <tr>
                                    <?php
                                    foreach ($includedColumns as $dbColumn => $displayName) {
                                        echo "<th>" . htmlspecialchars($displayName) . "</th>";
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                $primaryValue = $row[$test_primary] ?? '';
                                echo '<tr>';

                                $prod_abbrev = getProdID([
                                    'category' => $row['product_category'] ?? null,
                                    'line'     => $row['product_line'] ?? null,
                                    'type'     => $row['product_type'] ?? null,
                                    'grade'    => $row['grade'] ?? null,
                                    'gauge'    => $row['gauge'] ?? null,
                                    'color'    => $row['color_id'] ?? null,
                                    'length'   => $row['dimension_id'] ?? null,
                                ]);

                                foreach ($includedColumns as $dbColumn => $displayName) {
                                    $rawColumn = preg_replace('/^[a-z0-9_]+\./i', '', $dbColumn);

                                    if ($dbColumn === 'prod_abbrev') {
                                        $value = $prod_abbrev;
                                    } else {
                                        $value = $row[$rawColumn] ?? '';

                                        switch ($rawColumn) {
                                            case 'product_line':     $value = getProductLineName($value); break;
                                            case 'product_type':     $value = getProductTypeName($value); break;
                                            case 'grade':            $value = getGradeName($value); break;
                                            case 'gauge':            $value = getGaugeName($value); break;
                                            case 'color_id':         $value = getColorName($value); break;
                                            case 'color_group':      $value = getColorGroupName($value); break;
                                            case 'dimension_id':     $value = getDimensionName($value); break;
                                            case 'Product_id':       $value = getProductName($value); break;
                                            case 'product_category': $value = getProductCategoryName($value); break;
                                        }
                                    }

                                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

                                    $editable = in_array($rawColumn, ['quantity_ttl', 'reorder_level']);
                                    $contentEditable = $editable ? "contenteditable='true' class='editable'" : '';

                                    echo "<td $contentEditable data-header-name='$rawColumn' data-id='$primaryValue'>$value</td>";
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


    if ($action == "update_test_data") {
        $column_name = $_POST['header_name'] ?? '';
        $new_value = $_POST['new_value'] ?? '';
        $id = $_POST['id'] ?? '';

        if (empty($column_name) || empty($id)) exit;

        $test_primary = getPrimaryKey($test_table);

        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $id = mysqli_real_escape_string($conn, $id);

        $sql = "UPDATE $test_table SET `$column_name` = '$new_value' WHERE $test_primary = '$id'";
        echo $conn->query($sql) ? 'success' : 'Error updating record: ' . $conn->error;
    }

    if ($action == "save_table") {
        $main_primary = getPrimaryKey($table);
        $test_primary = getPrimaryKey($test_table);

        $result = $conn->query("SELECT * FROM $test_table");
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
                            if ($column !== $main_primary) {
                                $updateFields[] = "$column = '" . mysqli_real_escape_string($conn, $value) . "'";
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
                    $columns[] = $column;
                    $values[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
                }
                if (!empty($columns)) {
                    $insertSql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
                    $conn->query($insertSql);
                }
            }

            echo "Data has been successfully saved";

            $conn->query("TRUNCATE TABLE $test_table");
        } else {
            echo "No data found in test color table.";
        }
    }
    
    if ($action == "download_classifications") {
        $classification = mysqli_real_escape_string($conn, $_REQUEST['class'] ?? '');

        $classifications = [
            'line' => [
                'columns' => ['product_line_id', 'product_line'],
                'table' => 'product_line',
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
            'gauge' => [
                'columns' => ['product_gauge_id', 'product_gauge'],
                'table' => 'product_gauge',
                'where' => "status = '1'"
            ],
            'dimension' => [
                'columns' => ['dimension_id', 'dimension'],
                'table' => 'dimensions'
            ],
            'color' => [
                'columns' => ['color_id', 'color_name'],
                'table' => 'paint_colors',
                'where' => "color_status = '1'"
            ],
            'product_id' => [
                'columns' => ['product_id', 'product_item'],
                'table' => 'product',
                'where' => "status = '1'"
            ],
            'warehouse' => [
                'columns' => ['WarehouseID', 'WarehouseName'],
                'table' => 'warehouses',
                'where' => "status = '1'"
            ],
            'rack' => [
                'columns' => ['id', 'rack'],
                'table' => 'warehouse_rack',
                'where' => "hidden = '0'"
            ],
            'slot' => [
                'columns' => ['id', 'slot'],
                'table' => 'warehouse_slot',
                'where' => "hidden = '0'"
            ],
            'shelf' => [
                'columns' => ['ShelfID', 'ShelfCode'],
                'table' => 'shelves',
                'where' => "hidden = '0'"
            ],
            'row' => [
                'columns' => ['WarehouseRowID', 'RowCode'],
                'table' => 'warehouse_rows',
                'where' => "hidden = '0'"
            ],
            'bin' => [
                'columns' => ['BinID', 'BinCode'],
                'table' => 'bins',
                'where' => "hidden = '0'"
            ],
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
            $where = $classifications[$class]['where'] ?? '';

            $column_txt = implode(', ', $includedColumns);

            $sql = "SELECT $column_txt FROM $table";
            if (!empty($where)) {
                $sql .= " WHERE $where";
            }

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

        if (empty($classification)) {
            $classification = 'Classifications';
        } else {
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

    if ($action === "fetch_qr") {
        $Product_id = mysqli_real_escape_string($conn, $_POST['id'] ?? '');
        include_once('../delivery/qrlib.php');

        $product = getProductDetails($Product_id);
        $product_name = trim($product['product_item'] ?? 'Product');

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $domain   = $protocol . $_SERVER['HTTP_HOST'];

        $website = $domain . "/?page=product_details&product_id=" . urlencode($Product_id);

        $dir = "../images/productqr";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $final_path = "$dir/prod{$Product_id}.png";
        $tmp_qr     = "$dir/tmp_qr_{$Product_id}.png";

        if (!file_exists($final_path)) {
            QRcode::png($website, $tmp_qr, QR_ECLEVEL_L, 10, 1);

            $qr_img = imagecreatefrompng($tmp_qr);
            $qr_w = imagesx($qr_img);
            $qr_h = imagesy($qr_img);

            $font_file = __DIR__ . '/../assets/fonts/roboto/Roboto-Bold.ttf'; 
            $font_size = 15;

            $lines = [];
            $words = explode(" ", $product_name);
            $current = "";
            foreach ($words as $word) {
                $test = $current ? "$current $word" : $word;
                $box  = imagettfbbox($font_size, 0, $font_file, $test);
                $w = $box[2] - $box[0];
                if ($w < $qr_w - 20) {
                    $current = $test;
                } else {
                    $lines[] = $current;
                    $current = $word;
                }
            }
            if ($current) $lines[] = $current;

            $line_spacing = 4;
            $line_height = $font_size + $line_spacing;
            $text_height = count($lines) * $line_height;

            $padding = 5;
            $canvas_w = $qr_w + ($padding * 2);
            $canvas_h = $text_height + $qr_h + ($padding * 2);

            $canvas = imagecreatetruecolor($canvas_w, $canvas_h);
            $white  = imagecolorallocate($canvas, 255, 255, 255);
            $black  = imagecolorallocate($canvas, 0, 0, 0);
            imagefill($canvas, 0, 0, $white);

            $y = $padding;
            foreach ($lines as $line) {
                $box = imagettfbbox($font_size, 0, $font_file, $line);
                $text_w = $box[2] - $box[0];
                $x = ($canvas_w - $text_w) / 2;
                imagettftext($canvas, $font_size, 0, $x, $y + $font_size, $black, $font_file, $line);
                $y += $line_height;
            }

            imagecopy($canvas, $qr_img, $padding, $y, 0, 0, $qr_w, $qr_h);

            imagepng($canvas, $final_path);
            imagedestroy($qr_img);
            imagedestroy($canvas);
            unlink($tmp_qr);
        }

        echo ltrim($final_path, '../');
        exit;
    }

    if ($action === "fetch_barcode") {
        $Product_id = mysqli_real_escape_string($conn, $_POST['id'] ?? '');

        $product = getProductDetails($Product_id);
        $product_name = trim($product['product_item'] ?? 'Product');

        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $barcode_value = 'P' . str_pad($Product_id, 11, '0', STR_PAD_LEFT);

        $dir = "../images/barcode";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $final_path = "$dir/{$barcode_value}.png";

        if (!file_exists($final_path)) {
            $width_factor = 2;
            $height = 80;
            $barcode_png = $generator->getBarcode($barcode_value, $generator::TYPE_CODE_128, $width_factor, $height);

            $tmp_barcode = "$dir/tmp_barcode_{$Product_id}.png";
            file_put_contents($tmp_barcode, $barcode_png);

            $barcode_img = imagecreatefrompng($tmp_barcode);
            $bw = imagesx($barcode_img);
            $bh = imagesy($barcode_img);

            $font_file = __DIR__ . '/../assets/fonts/roboto/Roboto-Bold.ttf';
            $font_size = 14;

            $lines = [];
            $words = explode(" ", $product_name);
            $current = "";
            foreach ($words as $word) {
                $test = $current ? "$current $word" : $word;
                $box  = imagettfbbox($font_size, 0, $font_file, $test);
                $w = $box[2] - $box[0];
                if ($w < $bw - 20) {
                    $current = $test;
                } else {
                    $lines[] = $current;
                    $current = $word;
                }
            }
            if ($current) $lines[] = $current;

            $line_spacing = 4;
            $line_height = $font_size + $line_spacing;
            $text_height = count($lines) * $line_height;

            $padding_x = 10;
            $padding_y = 10;

            $canvas_w = $bw + ($padding_x * 2);
            $canvas_h = $bh + $text_height + ($padding_y * 2);

            $canvas = imagecreatetruecolor($canvas_w, $canvas_h);
            $white = imagecolorallocate($canvas, 255, 255, 255);
            $black = imagecolorallocate($canvas, 0, 0, 0);
            imagefill($canvas, 0, 0, $white);

            $y = $padding_y;
            foreach ($lines as $line) {
                $box = imagettfbbox($font_size, 0, $font_file, $line);
                $text_w = $box[2] - $box[0];
                $x = ($canvas_w - $text_w) / 2;
                imagettftext($canvas, $font_size, 0, $x, $y + $font_size, $black, $font_file, $line);
                $y += $line_height;
            }

            imagecopy($canvas, $barcode_img, $padding_x, $text_height + $padding_y, 0, 0, $bw, $bh);

            imagepng($canvas, $final_path);
            imagedestroy($barcode_img);
            imagedestroy($canvas);
            unlink($tmp_barcode);
        }

        echo ltrim($final_path, '../');
        exit;
    }

    if ($action === "fetch_warehouse_qr") {
        $warehouse_id = mysqli_real_escape_string($conn, $_POST['id'] ?? '');
        include_once('../delivery/qrlib.php');

        $warehouse_name = getWarehouseName($warehouse_id);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $domain   = $protocol . $_SERVER['HTTP_HOST'];

        $website = $domain . "/?page=warehouse_details&warehouse_id=" . urlencode($warehouse_id);

        $dir = "../images/warehouseqr";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $final_path = "$dir/warehouse{$warehouse_id}.png";
        $tmp_qr     = "$dir/tmp_qr_{$warehouse_id}.png";

        if (!file_exists($final_path)) {
            QRcode::png($website, $tmp_qr, QR_ECLEVEL_L, 10, 1);

            $qr_img = imagecreatefrompng($tmp_qr);
            $qr_w = imagesx($qr_img);
            $qr_h = imagesy($qr_img);

            $font_file = __DIR__ . '/../assets/fonts/roboto/Roboto-Bold.ttf'; 
            $font_size = 15;

            $lines = [];
            $words = explode(" ", $warehouse_name);
            $current = "";
            foreach ($words as $word) {
                $test = $current ? "$current $word" : $word;
                $box  = imagettfbbox($font_size, 0, $font_file, $test);
                $w = $box[2] - $box[0];
                if ($w < $qr_w - 20) {
                    $current = $test;
                } else {
                    $lines[] = $current;
                    $current = $word;
                }
            }
            if ($current) $lines[] = $current;

            $line_spacing = 4;
            $line_height = $font_size + $line_spacing;
            $text_height = count($lines) * $line_height;

            $padding = 5;
            $canvas_w = $qr_w + ($padding * 2);
            $canvas_h = $text_height + $qr_h + ($padding * 2);

            $canvas = imagecreatetruecolor($canvas_w, $canvas_h);
            $white  = imagecolorallocate($canvas, 255, 255, 255);
            $black  = imagecolorallocate($canvas, 0, 0, 0);
            imagefill($canvas, 0, 0, $white);

            $y = $padding;
            foreach ($lines as $line) {
                $box = imagettfbbox($font_size, 0, $font_file, $line);
                $text_w = $box[2] - $box[0];
                $x = ($canvas_w - $text_w) / 2;
                imagettftext($canvas, $font_size, 0, $x, $y + $font_size, $black, $font_file, $line);
                $y += $line_height;
            }

            imagecopy($canvas, $qr_img, $padding, $y, 0, 0, $qr_w, $qr_h);

            imagepng($canvas, $final_path);
            imagedestroy($qr_img);
            imagedestroy($canvas);
            unlink($tmp_qr);
        }

        echo ltrim($final_path, '../');
        exit;
    }



    mysqli_close($conn);
}
?>
