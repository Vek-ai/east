<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $Inventory_id = mysqli_real_escape_string($conn, $_POST['Inventory_id']);
        $product_sku = mysqli_real_escape_string($conn, $_POST['product_sku']);
        $product_item = mysqli_real_escape_string($conn, $_POST['product_item']);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
        $product_line = mysqli_real_escape_string($conn, $_POST['product_line']);
        $product_type = mysqli_real_escape_string($conn, $_POST['product_type']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $stock_type = mysqli_real_escape_string($conn, $_POST['stock_type']);
        $material = mysqli_real_escape_string($conn, $_POST['material']);
        $dimensions = mysqli_real_escape_string($conn, $_POST['dimensions']);
        $thickness = mysqli_real_escape_string($conn, $_POST['thickness']);
        $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);
        $grade = mysqli_real_escape_string($conn, $_POST['grade']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);
        $paint_provider = mysqli_real_escape_string($conn, $_POST['paintProvider']);
        $warranty_type = mysqli_real_escape_string($conn, $_POST['warrantyType']);
        $coating = mysqli_real_escape_string($conn, $_POST['coating']);
        $profile = mysqli_real_escape_string($conn, $_POST['profile']);
        $width = mysqli_real_escape_string($conn, $_POST['width']);
        $length = mysqli_real_escape_string($conn, $_POST['length']);
        $weight = mysqli_real_escape_string($conn, $_POST['weight']);
        $unit_price = mysqli_real_escape_string($conn, $_POST['unitPrice']);
        $upc = mysqli_real_escape_string($conn, $_POST['upc']);
        $unit_of_measure = mysqli_real_escape_string($conn, $_POST['unitofMeasure']);
        $unit_cost = mysqli_real_escape_string($conn, $_POST['unitCost']);
        $unit_gross_margin = mysqli_real_escape_string($conn, $_POST['unitGrossMargin']);
        $product_usage = mysqli_real_escape_string($conn, $_POST['product_usage']);
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);

        $correlatedProducts = $_POST['correlatedProducts'];
    
        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product WHERE Inventory_id = '$Inventory_id'";
        $result = mysqli_query($conn, $checkQuery);
        $isInsert = false;
    
        if (mysqli_num_rows($result) > 0) {
            // Record exists, proceed with update
            $isInsert = false;
            $updateQuery = "UPDATE product SET 
                product_item = '$product_item', 
                product_sku = '$product_sku', 
                product_category = '$product_category', 
                product_line = '$product_line', 
                product_type = '$product_type', 
                description = '$description', 
                stock_type = '$stock_type', 
                material = '$material', 
                dimensions = '$dimensions', 
                thickness = '$thickness', 
                gauge = '$gauge', 
                grade = '$grade', 
                color = '$color', 
                paint_provider = '$paint_provider', 
                warranty_type = '$warranty_type', 
                coating = '$coating', 
                profile = '$profile', 
                width = '$width', 
                length = '$length', 
                weight = '$weight', 
                unit_price = '$unit_price', 
                upc = '$upc', 
                unit_of_measure = '$unit_of_measure', 
                unit_cost = '$unit_cost', 
                unit_gross_margin = '$unit_gross_margin', 
                product_usage = '$product_usage', 
                comment = '$comment' 
            WHERE Inventory_id = '$Inventory_id'";
    
            if (mysqli_query($conn, $updateQuery)) {
                
                $query_delete = "DELETE FROM correlated_product WHERE main_correlated_Inventory_id = '$Inventory_id'";
                if (!mysqli_query($conn, $query_delete)) {
                    echo "Error: " . mysqli_error($conn);
                }else{
                    foreach ($correlatedProducts as $correlated_Inventory_id) {
                        $query_correlated = "INSERT INTO correlated_product (`correlated_id`, `main_correlated_Inventory_id`) VALUES ('$correlated_Inventory_id','$Inventory_id')";
                        if (mysqli_query($conn, $query_correlated)) {
                        } else {
                            echo "Error: " . mysqli_error($conn);
                        }    
                    }
                }

                echo "success";
            } else {
                echo "Error updating product: " . mysqli_error($conn);
            }
    
        } else {
            $upc = generateRandomUPC();
            // Record does not exist, proceed with insert
            $isInsert = true;
            $insertQuery = "INSERT INTO product (
                product_item, 
                product_sku, 
                product_category, 
                product_line, 
                product_type, 
                description, 
                stock_type, 
                material, 
                dimensions, 
                thickness, 
                gauge, 
                grade, 
                color, 
                paint_provider, 
                warranty_type, 
                coating, 
                profile, 
                width, 
                length, 
                weight, 
                quantity_in_stock, 
                quantity_quoted, 
                quantity_committed, 
                quantity_available, 
                quantity_in_transit, 
                unit_price, 
                date_added, 
                date_modified, 
                last_ordered_date, 
                last_sold_date, 
                upc, 
                unit_of_measure, 
                unit_cost, 
                unit_gross_margin, 
                product_usage, 
                comment
            ) VALUES (
                '$product_item', 
                '$product_sku', 
                '$product_category', 
                '$product_line', 
                '$product_type', 
                '$description', 
                '$stock_type', 
                '$material', 
                '$dimensions', 
                '$thickness', 
                '$gauge', 
                '$grade', 
                '$color', 
                '$paint_provider', 
                '$warranty_type', 
                '$coating', 
                '$profile', 
                '$width', 
                '$length', 
                '$weight', 
                '$quantity_in_stock', 
                '$quantity_quoted', 
                '$quantity_committed', 
                '$quantity_available', 
                '$quantity_in_transit', 
                '$unit_price', 
                '$date_added', 
                '$date_modified', 
                '$last_ordered_date', 
                '$last_sold_date', 
                '$upc', 
                '$unit_of_measure', 
                '$unit_cost', 
                '$unit_gross_margin', 
                '$product_usage', 
                '$comment'
            )";
    
            if (mysqli_query($conn, $insertQuery)) {
                $Inventory_id = $conn->insert_id;
                $query_delete = "DELETE FROM correlated_product WHERE main_correlated_Inventory_id = '$Inventory_id'";
                if (!mysqli_query($conn, $query_delete)) {
                    echo "Error: " . mysqli_error($conn);
                }else{
                    foreach ($correlatedProducts as $correlated_Inventory_id) {
                        $query_correlated = "INSERT INTO correlated_product (`correlated_id`, `main_correlated_Inventory_id`) VALUES ('$correlated_Inventory_id','$Inventory_id')";
                        if (mysqli_query($conn, $query_correlated)) {
                        } else {
                            echo "Error: " . mysqli_error($conn);
                        }    
                    }
                }

                echo "success";
            } else {
                echo "Error adding product: " . mysqli_error($conn);
            }
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
                            Add Product
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="add_inventory" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">
                                <input type="hidden" id="Inventory_id" name="Inventory_id" class="form-control"  />

                                <div class="row pt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Product</label>
                                    <select id="inventory_category" class="form-control" name="inventory_category">
                                        <option value="/" >Select Product...</option>
                                        <?php
                                        $query_product = "SELECT * FROM product WHERE hidden = '0'";
                                        $result_product = mysqli_query($conn, $query_product);            
                                        while ($row_product = mysqli_fetch_array($result_product)) {
                                            $selected = ($row['Product_id'] == $row_product['product_id']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_product['Inventory_id'] ?>" <?= $selected ?>><?= $row_product['product_item'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Warehouse</label>
                                    <select id="inventory_line" class="form-control" name="inventory_line">
                                        <option value="/" >Select Warehouse...</option>
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
                                    <div class="mb-3">
                                    <label class="form-label">Shelf</label>
                                    <select id="inventory_category" class="form-control" name="inventory_category">
                                        <option value="/" >Select Shelf...</option>
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
                                    <div class="mb-3">
                                    <label class="form-label">Bin</label>
                                    <select id="inventory_line" class="form-control" name="inventory_line">
                                        <option value="/" >Select Bin...</option>
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
                                    <div class="mb-3">
                                    <label class="form-label">Row</label>
                                    <select id="inventory_type" class="form-control" name="inventory_type">
                                        <option value="/" >Select Row...</option>
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
                                    <div class="col-md-6">
                                        <label class="form-label">Date</label>
                                        <input type="date" id="date" name="date" class="form-control" value="<?= $row['Date'] ?>" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Quantity</label>
                                        <input type="text" id="quantity" name="quantity" class="form-control" value="<?= $row['quantity'] ?>" />
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
    if ($action == 'hide_category') {
        $Inventory_id = mysqli_real_escape_string($conn, $_POST['Inventory_id']);
        $query = "UPDATE product SET hidden='1' WHERE Inventory_id='$Inventory_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    
    
    mysqli_close($conn);
}
?>
