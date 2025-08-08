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
        $operation = mysqli_real_escape_string($conn, $_POST['operation']);
        $Product_id = mysqli_real_escape_string($conn, $_POST['Product_id']);
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
        $Warehouse_id = mysqli_real_escape_string($conn, $_POST['Warehouse_id']);
        $Shelves_id = mysqli_real_escape_string($conn, $_POST['Shelves_id']);
        $Bin_id = mysqli_real_escape_string($conn, $_POST['Bin_id']);
        $Row_id = mysqli_real_escape_string($conn, $_POST['Row_id']);
        $Date = mysqli_real_escape_string($conn, $_POST['Date']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $quantity_ttl = mysqli_real_escape_string($conn, $_POST['quantity_ttl']);
        $pack = mysqli_real_escape_string($conn, $_POST['pack']);

        $length_value = mysqli_real_escape_string($conn, $_POST['length_value']);
        $length_unit = mysqli_real_escape_string($conn, $_POST['length_unit']);

        $formatted_length = "$length_value $length_unit";

        $addedby = $_SESSION['userid'];
    
        $checkQuery = "SELECT * FROM inventory WHERE Product_id = '$Product_id' AND color_id = '$color_id'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (!$result) {
            die("Error executing query: " . mysqli_error($conn));
        }
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE inventory SET 
                supplier_id = '$supplier_id', 
                Warehouse_id = '$Warehouse_id', 
                Shelves_id = '$Shelves_id', 
                Bin_id = '$Bin_id', 
                Row_id = '$Row_id', 
                Date = '$Date', 
                quantity = '$quantity',
                quantity_ttl = '$quantity_ttl', 
                pack = '$pack',
                addedby = '$addedby'
                WHERE Product_id = '$Product_id' AND color_id = '$color_id'";
    
            if ($operation == 'add') {
                $updateQuery = "UPDATE inventory SET 
                    supplier_id = '$supplier_id', 
                    Warehouse_id = '$Warehouse_id', 
                    Shelves_id = '$Shelves_id', 
                    Bin_id = '$Bin_id', 
                    Row_id = '$Row_id', 
                    Date = '$Date', 
                    quantity = quantity + '$quantity',
                    quantity_ttl = quantity_ttl + '$quantity_ttl', 
                    pack = '$pack',
                    addedby = '$addedby'
                    WHERE Product_id = '$Product_id' AND color_id = '$color_id'";
            }
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success";
            } else {
                die("Error updating record: " . mysqli_error($conn));
            }
        } else {
            $insertQuery = "INSERT INTO inventory (
                Product_id, 
                color_id, 
                supplier_id, 
                Warehouse_id, 
                Shelves_id, 
                Bin_id, 
                Row_id, 
                Date, 
                quantity, 
                pack, 
                quantity_ttl, 
                addedby
            ) VALUES (
                '$Product_id', 
                '$color_id', 
                '$supplier_id', 
                '$Warehouse_id',
                '$Shelves_id', 
                '$Bin_id', 
                '$Row_id', 
                '$Date', 
                '$quantity', 
                '$pack', 
                '$quantity_ttl', 
                '$addedby'
            )";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success";
            } else {
                die("Error inserting record: " . mysqli_error($conn));
            }
        }

        $inv_id_query = mysqli_query($conn, "SELECT inventory_id FROM inventory WHERE Product_id = '$Product_id' AND color_id = '$color_id' LIMIT 1");
        $inventory = mysqli_fetch_assoc($inv_id_query);
        $inventory_id = $inventory['inventory_id'];

        $check_length = mysqli_query($conn, "SELECT variant_id FROM product_variant_length WHERE inventory_id = '$inventory_id'");
        
        if (mysqli_num_rows($check_length) > 0) {
            mysqli_query($conn, "UPDATE product_variant_length SET length = '$formatted_length' WHERE inventory_id = '$inventory_id'");
        } else {
            mysqli_query($conn, "INSERT INTO product_variant_length (inventory_id, length) VALUES ('$inventory_id', '$formatted_length')");
        }
    }
    

    if ($action == "fetch_modal") {
        $Inventory_id = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM inventory WHERE Inventory_id = '$Inventory_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            Update Inventory
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="update_inventory" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">
                                <input type="hidden" id="Inventory_id" name="Inventory_id" class="form-control" value="<?= $row['Inventory_id'] ?>" />
                                <input type="hidden" id="operation" name="operation" value="update" />

                                <div class="row pt-3">
                                <div class="col-md-8">
                                    <label class="form-label">Product</label>
                                    <div class="mb-3">
                                    <select id="inventory_product" class="select2-update form-control" name="Product_id">
                                        <option value="" >Select Product...</option>
                                        <?php
                                        $query_product = "SELECT * FROM product WHERE hidden = '0'";
                                        $result_product = mysqli_query($conn, $query_product);            
                                        while ($row_product = mysqli_fetch_array($result_product)) {
                                            $selected = ($row['Product_id'] == $row_product['product_id']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_product['product_id'] ?>" <?= $selected ?>><?= $row_product['product_item'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Color</label>
                                    <div class="mb-3">
                                        <select id="color<?= $no ?>" class="form-control color-cart select2-update" name="color_id">
                                            <option value="" >Select Color...</option>
                                            <?php
                                            $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                            $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                            while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                                if(empty($row['color_id'])){
                                                    $product_details = getProductDetails($row['Product_id']);
                                                    $color_id = $product_details['color'];
                                                }else{
                                                    $color_id = $row['color_id'];
                                                }

                                                $selected = ($color_id == $row_paint_colors['color_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?> data-color="<?= getColorHexFromColorID($row_paint_colors['color_id']) ?>"><?= $row_paint_colors['color_name'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Supplier</label>
                                    <div class="mb-3">
                                        <select id="inventory_supplier" class="form-control select2-update inventory_supplier" name="supplier_id">
                                            <option value="" >Select Supplier...</option>
                                            <?php
                                            $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
                                            $result_supplier = mysqli_query($conn, $query_supplier);            
                                            while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                                $selected = ($row['supplier_id'] == $row_supplier['supplier_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_supplier['supplier_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_name'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Warehouse</label>
                                    <div class="mb-3">
                                    <select id="inventory_warehouse" class="select2-update form-control" name="Warehouse_id">
                                        <option value="" >Select Warehouse...</option>
                                        <?php
                                        $query_warehouse = "SELECT * FROM warehouses WHERE status = '1'";
                                        $result_warehouse = mysqli_query($conn, $query_warehouse);            
                                        while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                                            $selected = ($row['Warehouse_id'] == $row_warehouse['WarehouseID']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_warehouse['WarehouseID'] ?>" <?= $selected ?>><?= $row_warehouse['WarehouseName'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-4">
                                    <label class="form-label">Shelf</label>
                                    <div class="mb-3">
                                    <select id="inventory_shelf" class="form-control select2-update" name="Shelves_id">
                                        <option value="" >Select Shelf...</option>
                                        <?php
                                        $query_shelf = "SELECT * FROM shelves";
                                        $result_shelf = mysqli_query($conn, $query_shelf);            
                                        while ($row_shelf = mysqli_fetch_array($result_shelf)) {
                                            $selected = ($row['Shelves_id'] == $row_shelf['ShelfID']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_shelf['ShelfID'] ?>" <?= $selected ?>><?= $row_shelf['ShelfCode'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Bin</label>
                                    <div class="mb-3">
                                    <select id="inventory_bin" class="form-control select2-update" name="Bin_id">
                                        <option value="" >Select Bin...</option>
                                        <?php
                                        $query_bin = "SELECT * FROM bins";
                                        $result_bin = mysqli_query($conn, $query_bin);            
                                        while ($row_bin = mysqli_fetch_array($result_bin)) {
                                            $selected = ($row['Bin_id'] == $row_bin['BinID']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_bin['BinID'] ?>" <?= $selected ?>><?= $row_bin['BinCode'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Row</label>
                                    <div class="mb-3">
                                    
                                    <select id="inventory_row" class="form-control select2-update" name="Row_id">
                                        <option value="" >Select Row...</option>
                                        <?php
                                        $query_rows = "SELECT * FROM warehouse_rows";
                                        $result_rows = mysqli_query($conn, $query_rows);            
                                        while ($row_rows = mysqli_fetch_array($result_rows)) {
                                            $selected = ($row['Row_id'] == $row_rows['WarehouseRowID']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_rows['WarehouseRowID'] ?>" <?= $selected ?>><?= $row_rows['WarehouseRowID'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                </div>
                                <div class="row pt-3">
                                <div class="col-md-4">
                                    <label class="form-label">Quantity</label>
                                    <input type="text" id="quantity_update" name="quantity" class="form-control" value="<?= $row['quantity'] ?>" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pack</label>
                                    <div class="mb-3">
                                    <select id="pack_update" class="form-control select2-update pack_select" name="pack">
                                        <option value="">Select Pack...</option>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Total Quantity</label>
                                    <input type="text" id="quantity_ttl_update" name="quantity_ttl" class="form-control" value="<?= $row['quantity_ttl'] ?>" />
                                </div>
                                </div> 
                                <div class="row pt-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Date</label>
                                        <input type="date" id="date" name="Date" class="form-control" value="<?= $row['Date'] ?>" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Length</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="length_value" class="form-control" placeholder="Enter length">
                                            <select name="length_unit" class="form-control">
                                                <option value="inches">Inches</option>
                                                <option value="meter">Meter</option>
                                                <option value="feet">Feet</option>
                                            </select>
                                        </div>
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
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <script>
                /* $("#inventory_product").select2({dropdownParent: $('#updateInventoryModal .modal-content')});
                $("#inventory_warehouse").select2({dropdownParent: $('#updateInventoryModal .modal-content')});
                $("#inventory_row").select2({dropdownParent: $('#updateInventoryModal .modal-content')});
                $("#inventory_bin").select2({dropdownParent: $('#updateInventoryModal .modal-content')});
                $("#inventory_shelf").select2({dropdownParent: $('#updateInventoryModal .modal-content')});
                $("#inventory_supplier").select2({dropdownParent: $('#updateInventoryModal .modal-content')}); */
                $(document).ready(function() {
                    $(".select2-update").select2({
                        dropdownParent: $('#updateInventoryModal .modal-content'),
                        placeholder: "Select One...",
                        allowClear: true,
                        templateResult: formatOption,
                        templateSelection: formatOption
                    });
                }); 
                
            </script>
            <?php
        }
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
    
    mysqli_close($conn);
}
?>
