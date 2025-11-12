<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);


require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $Product_id   = mysqli_real_escape_string($conn, $_POST['Product_id']);
        $color_id     = mysqli_real_escape_string($conn, $_POST['color_id']);
        $supplier_id  = mysqli_real_escape_string($conn, $_POST['supplier_id']);
        $Warehouse_id = mysqli_real_escape_string($conn, $_POST['Warehouse_id']);
        $Shelves_id   = mysqli_real_escape_string($conn, $_POST['Shelves_id']);
        $Bin_id       = mysqli_real_escape_string($conn, $_POST['Bin_id']);
        $Row_id       = mysqli_real_escape_string($conn, $_POST['Row_id']);
        $Date         = mysqli_real_escape_string($conn, $_POST['Date']);
        $pack         = mysqli_real_escape_string($conn, $_POST['pack']);
        $cost         = mysqli_real_escape_string($conn, $_POST['cost'] ?? '0');
        $price        = mysqli_real_escape_string($conn, $_POST['price'] ?? '0');
        $lumber_type  = mysqli_real_escape_string($conn, $_POST['lumber_type']);
        $grade        = mysqli_real_escape_string($conn, $_POST['grade']);
        $gauge        = mysqli_real_escape_string($conn, $_POST['gauge']);
        $addedby      = $_SESSION['userid'];

        $dimension_ids  = $_POST['dimension_id'] ?? [];
        $quantity_ttls  = $_POST['quantity_ttl'] ?? [];
        $reorder_levels = $_POST['reorder_level'] ?? [];

        $total_quantity = array_sum($quantity_ttls);

        $result = mysqli_query($conn, "
            SELECT inventory_id, quantity_ttl
            FROM inventory
            WHERE Product_id='$Product_id'
            AND color_id='$color_id'
            AND grade='$grade'
            AND gauge='$gauge'
            AND lumber_type='$lumber_type'
        ");
        if (!$result) die("Error fetching inventory: " . mysqli_error($conn));

        while ($row = mysqli_fetch_assoc($result)) {
            $inventory_id = $row['inventory_id'];
            $new_total_qty = (int)$row['quantity_ttl'] + $total_quantity;
            $updateQuery = "
                UPDATE inventory SET
                    Warehouse_id='$Warehouse_id',
                    Shelves_id='$Shelves_id',
                    Bin_id='$Bin_id',
                    Row_id='$Row_id',
                    Date='$Date',
                    quantity_ttl='$new_total_qty',
                    pack='$pack',
                    cost='$cost',
                    price='$price',
                    addedby='$addedby',
                    last_edit=NOW(),
                    edited_by='$addedby'
                WHERE inventory_id='$inventory_id'
            ";
            if (!mysqli_query($conn, $updateQuery)) die("Error updating inventory: " . mysqli_error($conn));
        }

        foreach ($dimension_ids as $i => $dimension_id) {
            $dimension_id  = mysqli_real_escape_string($conn, $dimension_id);
            $quantity_ttl  = (int)($quantity_ttls[$i] ?? 0);
            $reorder_level = (int)($reorder_levels[$i] ?? 0);

            if ($quantity_ttl <= 0) continue;

            $checkQuery = "
                SELECT inventory_id
                FROM inventory
                WHERE Product_id='$Product_id'
                AND color_id='$color_id'
                AND grade='$grade'
                AND gauge='$gauge'
                AND lumber_type='$lumber_type'
                AND dimension_id='$dimension_id'
                LIMIT 1
            ";
            $resCheck = mysqli_query($conn, $checkQuery);
            if (!$resCheck) die("Error checking dimension inventory: " . mysqli_error($conn));

            if (mysqli_num_rows($resCheck) > 0) {
                $rowDim = mysqli_fetch_assoc($resCheck);
                $inventory_id = $rowDim['inventory_id'];
                $updateDim = "
                    UPDATE inventory SET
                        Warehouse_id='$Warehouse_id',
                        Shelves_id='$Shelves_id',
                        Bin_id='$Bin_id',
                        Row_id='$Row_id',
                        Date='$Date',
                        quantity_ttl='$quantity_ttl',
                        reorder_level='$reorder_level',
                        pack='$pack',
                        cost='$cost',
                        price='$price',
                        addedby='$addedby',
                        last_edit=NOW(),
                        edited_by='$addedby'
                    WHERE inventory_id='$inventory_id'
                ";
                if (!mysqli_query($conn, $updateDim)) die("Error updating dimension inventory: " . mysqli_error($conn));
            } else {
                $insertDim = "
                    INSERT INTO inventory
                        (Product_id, color_id, grade, gauge, lumber_type, dimension_id, Warehouse_id, Shelves_id, Bin_id, Row_id, Date, quantity_ttl, reorder_level, pack, cost, price, addedby)
                    VALUES
                        ('$Product_id', '$color_id', '$grade', '$gauge', '$lumber_type', '$dimension_id', '$Warehouse_id', '$Shelves_id', '$Bin_id', '$Row_id', '$Date', '$quantity_ttl', '$reorder_level', '$pack', '$cost', '$price', '$addedby')
                ";
                if (!mysqli_query($conn, $insertDim)) die("Error inserting dimension inventory: " . mysqli_error($conn));
                $inventory_id = mysqli_insert_id($conn);
            }

            $date_part = date("mdY", strtotime($Date));
            $batchno   = $Product_id . $supplier_id . $inventory_id . $date_part;

            $insertProductInventory = "
                INSERT INTO product_inventory
                    (productid, inventoryid, supplierid, cost, price, delivery_date, batchno, entered_by, quantity, pack_id, total_quantity)
                VALUES
                    ('$Product_id', '$inventory_id', '$supplier_id', '$cost', '$price', NOW(), '$batchno', '$addedby', '$quantity_ttl', '$pack', '$total_quantity')
            ";
            if (!mysqli_query($conn, $insertProductInventory)) die("Error inserting product_inventory: " . mysqli_error($conn));
        }

        echo "success";
    }

    if ($action == "fetch_modal") {
        $Inventory_id = (int)($_POST['id'] ?? 0);

        $row = []; 

        if ($Inventory_id > 0) {
            $query_inventory = "SELECT * FROM inventory WHERE Inventory_id = '$Inventory_id' LIMIT 1";
            $result_inventory = mysqli_query($conn, $query_inventory);
            if ($result_inventory && mysqli_num_rows($result_inventory) > 0) {
                $row = mysqli_fetch_assoc($result_inventory);
            }
        }
        ?>
        <input type="hidden" id="Inventory_id" name="Inventory_id" value="<?= $row['Inventory_id'] ?>" />

        <div class="row pt-3">
            <div class="col-md-3">
                <label class="form-label">Product</label>
                <div class="mb-3">
                    <select id="product_id" class="form-control select2-inventory" name="Product_id">
                        <option value="" hidden>Select Product...</option>
                        <optgroup label="Product">
                            <?php
                            $query_product = "SELECT * FROM product WHERE hidden = '0'";
                            $result_product = mysqli_query($conn, $query_product);
                            while ($p = mysqli_fetch_assoc($result_product)) {
                                $selected = ($row['Product_id'] == $p['product_id']) ? 'selected' : '';
                                echo "<option value='{$p['product_id']}' data-category='{$p['product_category']}' $selected>{$p['product_item']}</option>";
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Color</label>
                <div class="mb-3">
                    <select id="color" class="form-control color_id select2-inventory" name="color_id">
                        <option value="">Select Color...</option>
                        <?php
                        $query_colors = "
                            SELECT * 
                            FROM paint_colors 
                            WHERE hidden = '0' AND color_status = '1' 
                            GROUP BY BINARY color_name
                            ORDER BY color_name ASC
                        ";
                        $result_colors = mysqli_query($conn, $query_colors);
                        while ($c = mysqli_fetch_assoc($result_colors)) {
                            $selected = ($row['color_id'] == $c['color_id']) ? 'selected' : '';
                            $hex = getColorHexFromColorID($c['color_id']);
                            $hex = $c['color_id'];
                            echo "<option value='{$c['color_id']}' data-color='{$hex}' data-category='{$hex}' $selected>{$c['color_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Grade</label>
                <div class="mb-3">
                    <select id="grade" class="form-control grade-cart select2-inventory" name="grade">
                        <option value="">Select Grade...</option>
                        <?php
                        $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY product_grade ASC";
                        $result_grade = mysqli_query($conn, $query_grade);
                        while ($g = mysqli_fetch_assoc($result_grade)) {
                            $selected = ($row['grade'] == $g['product_grade_id']) ? 'selected' : '';
                            echo "<option value='{$g['product_grade_id']}' $selected>{$g['product_grade']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Gauge</label>
                <div class="mb-3">
                    <select id="gauge" class="form-control gauge-cart select2-inventory" name="gauge">
                        <option value="">Select Gauge...</option>
                        <?php
                        $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY product_gauge ASC";
                        $result_gauge = mysqli_query($conn, $query_gauge);
                        while ($gauge = mysqli_fetch_assoc($result_gauge)) {
                            $selected = ($row['gauge'] == $gauge['product_gauge_id']) ? 'selected' : '';
                            echo "<option value='{$gauge['product_gauge_id']}' $selected>{$gauge['product_gauge']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row pt-3" id="length_rows_container">
            <div class="col-3 text-center"><label class="form-label">Length</label></div>
            <div class="col-3"><label class="form-label">Qty on Hand</label></div>
            <div class="col-3"><label class="form-label">Reorder Qty</label></div>
            <div class="col-3 text-end"></div>
        </div>

        <div id="length_rows_wrapper" class="position-relative">
            <?php
            if (!empty($row['Inventory_id'])) {
                $Inventory_id = $row['Inventory_id'];

                $query_lengths = "
                    SELECT i.inventory_id, i.dimension_id, i.quantity_ttl, i.reorder_level
                    FROM inventory i
                    WHERE i.Product_id = '{$row['Product_id']}'
                    AND i.color_id = '{$row['color_id']}'
                    AND i.grade = '{$row['grade']}'
                    AND i.gauge = '{$row['gauge']}'
                    AND i.lumber_type = '{$row['lumber_type']}'
                ";

                $result_lengths = mysqli_query($conn, $query_lengths);

                if ($result_lengths && mysqli_num_rows($result_lengths) > 0) {
                    while ($length = mysqli_fetch_assoc($result_lengths)) {
                        ?>
                        <div class="row length-row align-items-center mb-2">
                            <div class="col-3">
                                <select class="form-control dimension_id select2-inventory" name="dimension_id[]">
                                    <option value="">Select Length...</option>
                                    <?php
                                    $res_dim = mysqli_query($conn, "SELECT dimension_id, dimension_category FROM dimensions ORDER BY dimension ASC");
                                    while ($d = mysqli_fetch_assoc($res_dim)) {
                                        $dimension_name = getDimensionName($d['dimension_id']);
                                        $selected = ($d['dimension_id'] == $length['dimension_id']) ? 'selected' : '';
                                        echo "<option value='{$d['dimension_id']}' data-category='{$d['dimension_category']}' $selected>{$dimension_name}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-3">
                                <input type="number" class="form-control quantity_ttl" name="quantity_ttl[]" value="<?= $length['quantity_ttl'] ?>" placeholder="Qty" min="0">
                            </div>
                            <div class="col-3">
                                <input type="number" class="form-control reorder_level" name="reorder_level[]" value="<?= $length['reorder_level'] ?>" placeholder="Reorder Level" min="0">
                            </div>
                            <div class="col-3 text-start">
                                <a href="javascript:void(0)" type="button" class="text-decoration-none fs-7 remove_length_row">&times;</a>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
            ?>
        </div>


        <div class="text-end mt-2 row" id="add_length_row_container">
            <div class="col-9 text-end">
                <a href="javascript:void(0)" id="add_length_row" class="text-decoration-none fs-7">+</a>
            </div>
        </div>

        <div id="length_row_template" class="d-none">
            <div class="row length-row align-items-center mb-2">
                <div class="col-3">
                    <select class="form-control dimension_id select2-inventory" name="dimension_id[]">
                        <option value="">Select Length...</option>
                        <?php
                        $query_dim = "SELECT dimension_id, dimension_category FROM dimensions ORDER BY dimension ASC";
                        $res_dim = mysqli_query($conn, $query_dim);
                        while ($d = mysqli_fetch_assoc($res_dim)) {
                            $dimension_name = getDimensionName($d['dimension_id']);
                            echo "<option value='{$d['dimension_id']}' data-category='{$d['dimension_category']}'>{$dimension_name}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-3">
                    <input type="number" class="form-control quantity_ttl" name="quantity_ttl[]" placeholder="Qty" min="0">
                </div>
                <div class="col-3">
                    <input type="number" class="form-control reorder_level" name="reorder_level[]" placeholder="Reorder Level" min="0">
                </div>
                <div class="col-3 text-start">
                    <a href="javascript:void(0)" type="button" class="text-decoration-none fs-7 remove_length_row">&times;</a>
                </div>
            </div>
        </div>

        <div class="row pt-3">
            <div class="col-md-3 mb-2">
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
                                echo "<option value='{$w['WarehouseID']}' $selected>{$w['WarehouseName']}</option>";
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="col-md-9 mb-2"></div>

            <div class="col-md-3">
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

            <div class="col-md-3 mb-2">
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

            <div class="col-md-3 mb-2">
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
        </div>
        
        <div class="row pt-3 d-none">
            <div class="col-md-6">
                <label class="form-label">Supplier</label>
                <select id="supplier_id" class="form-control select2-inventory inventory_supplier" name="supplier_id">
                    <option value="">Select Supplier...</option>
                    <optgroup label="Supplier">
                        <?php
                        $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY supplier_name ASC";
                        $result_supplier = mysqli_query($conn, $query_supplier);
                        while ($sup = mysqli_fetch_assoc($result_supplier)) {
                            $selected = ($row['supplier_id'] == $sup['supplier_id']) ? 'selected' : '';
                            echo "<option value='{$sup['supplier_id']}' $selected>{$sup['supplier_name']}</option>";
                        }
                        ?>
                    </optgroup>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Date</label>
                <input type="date" id="Date" name="Date" class="form-control" value="<?= $row['Date'] ?? date('Y-m-d') ?>">
            </div>
        </div>
        <?php
        
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
