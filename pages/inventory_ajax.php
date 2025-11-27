<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);


require '../includes/dbconn.php';
require '../includes/functions.php';

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
                    (Product_id, product_type, product_line, color_id, grade, gauge, dimension_id, Warehouse_id, Shelves_id, Bin_id, Row_id, Date, quantity_ttl, reorder_level, addedby, last_edit, edited_by)
                VALUES
                    ('$Product_id', '$product_type', '$product_line', '$color_id', '$grade', '$gauge', '$dimension_id', '$Warehouse_id', '$Shelves_id', '$Bin_id', '$Row_id', '$Date', '$quantity_ttl', '$reorder_level', '$addedby', '$now', '$addedby')
            ";
            if (!mysqli_query($conn, $insertQuery)) die("Error inserting inventory: " . mysqli_error($conn));
        }

        echo "success";
    }

    if ($action == "fetch_modal") {
        $Product_id   = (int)($_POST['id'] ?? 0);
        $product_type = (int)($_POST['type'] ?? 0);
        $product_line = (int)($_POST['line'] ?? 0);
        $grade        = (int)($_POST['grade'] ?? 0);
        $gauge        = (int)($_POST['gauge'] ?? 0);
        $color_id     = (int)($_POST['color'] ?? 0);
        $dimension_id = (int)($_POST['dim'] ?? 0);

        $where = [];
        if ($Product_id)   $where[] = "Product_id = '$Product_id'";
        if ($product_type) $where[] = "product_type = '$product_type'";
        if ($product_line) $where[] = "product_line = '$product_line'";
        if ($grade)        $where[] = "grade = '$grade'";
        if ($gauge)        $where[] = "gauge = '$gauge'";
        if ($color_id)     $where[] = "color_id = '$color_id'";
        if ($dimension_id) $where[] = "dimension_id = '$dimension_id'";

        $row = [];
        if (!empty($where)) {
            $query_inventory = "SELECT * FROM inventory WHERE " . implode(" AND ", $where) . " LIMIT 1";
            $res = mysqli_query($conn, $query_inventory);
            if ($res) $row = mysqli_fetch_assoc($res);
        }
        $result_inventory = mysqli_query($conn, $query_inventory);
        if ($result_inventory && mysqli_num_rows($result_inventory) > 0) {
            $row = mysqli_fetch_assoc($result_inventory);
        }

        $product_details = getProductDetails($Product_id);
        $color_details = getColorDetails($color_id);
        ?>
        <input type="hidden" id="Product_id" name="Product_id" value="<?= $Product_id ?>" />
        <input type="hidden" id="product_type" name="product_type" value="<?= $product_type ?>" />
        <input type="hidden" id="product_line" name="product_line" value="<?= $product_line ?>" />
        <input type="hidden" id="grade" name="grade" value="<?= $grade ?>" />
        <input type="hidden" id="gauge" name="gauge" value="<?= $gauge ?>" />
        <input type="hidden" id="color_id" name="color_id" value="<?= $color_id ?>" />
        <input type="hidden" id="dimension_id" name="dimension_id" value="<?= $dimension_id ?>" />

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Inventory Identifier</h5>
            </div>
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
                            <p class="mb-0"><?= getProductCategoryName($product_line) ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Product Type</h4>
                            <p class="mb-0"><?= getProductTypeName($product_type); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Grade</h4>
                            <p class="mb-0"><?= getGradeName($grade); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Gauge</h4>
                            <p class="mb-0"><?= getGaugeName($gauge); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-center p-2">
                            <h5>Length</h4>
                            <p class="mb-0"><?= getDimensionName($dimension_id); ?></p>
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
                            <p class="mb-0"><?= getColorName($color_id) ?></p>
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
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Product Location Management</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4 mb-2 text-center">
                        <label class="form-label">Warehouse</label>
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
                        <label class="form-label">Shelf</label>
                        <div class="mb-2">
                            <select id="Shelves_id" class="form-control select2-inventory" name="Shelves_id">
                                <option value="">Select Shelf...</option>
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
                                <option value="">Select Row...</option>
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
                                <option value="">Select Bin...</option>
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

    if ($_POST['action'] == 'fetch_table') {
        $draw = intval($_POST['draw'] ?? 1);
        $start = intval($_POST['start'] ?? 0);
        $length = intval($_POST['length'] ?? 100);
        $isStock = $_POST['isStock'] ?? 0;

        $filters = ["p.status = 1", "p.hidden = 0"];
        if (!empty($_POST['color'])) $filters[] = "i.color_id IN (" . implode(',', array_map('intval', explode(',', $_POST['color']))) . ")";
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
                    data-id="'.trim($row['Product_id']).'">
                        <i class="ti ti-pencil fs-5"></i>
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

    if ($_POST['action'] == 'fetch_inventory_rows') {
        $productId = $_POST['product_id'] ?? '';
        $colorId = $_POST['color_id'] ?? '';
        $gradeId = $_POST['grade'] ?? '';
        $gaugeId = $_POST['gauge'] ?? '';

        $rows = [];

        if ($productId) {
            $productRes = mysqli_query($conn, "SELECT available_lengths FROM product WHERE product_id='$productId'");
            $productRow = mysqli_fetch_assoc($productRes);
            $available_lengths = [];

            if (!empty($productRow['available_lengths'])) {
                $decoded = json_decode($productRow['available_lengths'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $available_lengths = $decoded;
                } else {
                    $available_lengths = array_filter(explode(',', $productRow['available_lengths']));
                }
            }

            foreach ($available_lengths as $dimension_id) {
                $query = "
                    SELECT Inventory_id, dimension_id, quantity_ttl, reorder_level 
                    FROM inventory 
                    WHERE Product_id='$productId' 
                    AND dimension_id='$dimension_id'
                ";

                if ($colorId) $query .= " AND color_id='$colorId'";
                if ($gradeId) $query .= " AND grade='$gradeId'";
                if ($gaugeId) $query .= " AND gauge='$gaugeId'";

                $result = mysqli_query($conn, $query);
                $r = mysqli_fetch_assoc($result);

                if ($r) {
                    $r['dimension_display'] = getDimensionName($r['dimension_id']);
                } else {
                    $r = [
                        'Inventory_id' => '',
                        'dimension_id' => $dimension_id,
                        'dimension_display' => getDimensionName($dimension_id),
                        'quantity_ttl' => 0,
                        'reorder_level' => 0
                    ];
                }

                $rows[] = $r;
            }
        }

        echo json_encode($rows);
        exit;
    }
    
    mysqli_close($conn);
}
?>
