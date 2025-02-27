<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    
    if ($action == "fetch_info") {
        $warehouse_id = mysqli_real_escape_string($conn, $_REQUEST['warehouse_id']);

        $checkQuery = "SELECT * FROM warehouses WHERE WarehouseID = '$warehouse_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
        }

        
    }

    if ($action == "add_update_bin") {
        $BinID = mysqli_real_escape_string($conn, $_POST['BinID']);
        $WarehouseID = mysqli_real_escape_string($conn, $_POST['WarehouseID']);
        $BinCode = mysqli_real_escape_string($conn, $_POST['BinCode']);
        $Description = mysqli_real_escape_string($conn, $_POST['Description']);
    
        $checkQuery = "SELECT * FROM bins WHERE BinID = '$BinID'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE bins 
                SET 
                    BinCode = '$BinCode', 
                    Description = '$Description'
                WHERE BinID = '$BinID'
            ";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "$updateQuery";
            } else {
                echo "Error updating warehouse: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "
                INSERT INTO bins (
                    BinCode,
                    WarehouseID,
                    Description
                ) VALUES (
                    '$BinCode', 
                    '$WarehouseID', 
                    '$Description'
                )
            ";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success";
            } else {
                echo "Error adding warehouse: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "add_update_shelf") {
        $ShelfID = mysqli_real_escape_string($conn, $_POST['ShelfID']);
        $WarehouseRowID = mysqli_real_escape_string($conn, $_POST['WarehouseRowID']);
        $ShelfCode = mysqli_real_escape_string($conn, $_POST['ShelfCode']);
        $Description = mysqli_real_escape_string($conn, $_POST['Description']);
    
        $checkQuery = "SELECT * FROM shelves WHERE ShelfID = '$ShelfID'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE shelves 
                SET 
                    WarehouseRowID = '$WarehouseRowID', 
                    ShelfCode = '$ShelfCode', 
                    Description = '$Description'
                WHERE WarehouseRowID = '$WarehouseRowID'
            ";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success";
            } else {
                echo "Error updating warehouse: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "
                INSERT INTO shelves (
                    WarehouseRowID,
                    ShelfCode,
                    Description
                ) VALUES (
                    '$WarehouseRowID', 
                    '$ShelfCode', 
                    '$Description'
                )
            ";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success";
            } else {
                echo "Error adding warehouse: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "row_delete") {
        $row_id = mysqli_real_escape_string($conn, $_POST['row_id']);
        $updateQuery = "UPDATE warehouse_rows SET hidden = '1' WHERE WarehouseRowID = '$row_id'";
        if (mysqli_query($conn, $updateQuery)) {
            echo "success";
        } else {
            echo "Error updating warehouse: " . mysqli_error($conn);
        }
    }
    if ($action == "shelf_delete") {
        $shelf_id = mysqli_real_escape_string($conn, $_POST['shelf_id']);
        $updateQuery = "UPDATE shelves SET hidden = '1' WHERE ShelfID  = '$shelf_id'";
        if (mysqli_query($conn, $updateQuery)) {
            echo "success";
        } else {
            echo "Error updating warehouse: " . mysqli_error($conn);
        }
    }
    if ($action == "bin_delete") {
        $bin_id = mysqli_real_escape_string($conn, $_POST['bin_id']);
        $updateQuery = "UPDATE bins SET hidden = '1' WHERE BinID  = '$bin_id'";
        if (mysqli_query($conn, $updateQuery)) {
            echo "success";
        } else {
            echo "Error updating warehouse: " . mysqli_error($conn);
        }
    }

    if ($action == "add_update_row") {
        $WarehouseRowID = mysqli_real_escape_string($conn, $_POST['WarehouseRowID']);
        $WarehouseID = mysqli_real_escape_string($conn, $_POST['WarehouseID']);
        $RowCode = mysqli_real_escape_string($conn, $_POST['RowCode']);
        $Description = mysqli_real_escape_string($conn, $_POST['Description']);
    
        $checkQuery = "SELECT * FROM warehouse_rows WHERE WarehouseRowID = '$WarehouseRowID'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE warehouse_rows 
                SET 
                    RowCode = '$RowCode', 
                    Description = '$Description'
                WHERE WarehouseRowID = '$WarehouseRowID'
            ";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success";
            } else {
                echo "Error updating warehouse: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "
                INSERT INTO warehouse_rows (
                    RowCode,
                    WarehouseID,
                    Description
                ) VALUES (
                    '$RowCode', 
                    '$WarehouseID', 
                    '$Description'
                )
            ";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success";
            } else {
                echo "Error adding warehouse: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "add_edit_row") {
        $row_id = mysqli_real_escape_string($conn, $_POST['row_id']);
        $warehouse_id = mysqli_real_escape_string($conn, $_POST['warehouse_id']);

        $checkQuery = "SELECT * FROM warehouse_rows WHERE WarehouseRowID = '$row_id'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        }
        ?>
        <div class="card">
            <div class="card-body">
                <input type="hidden" id="WarehouseRowID" name="WarehouseRowID" class="form-control" value="<?= $row['WarehouseRowID'] ?? $row_id ?>"/>
                <input type="hidden" id="WarehouseID" name="WarehouseID" class="form-control" value="<?= $row['WarehouseID'] ?? $warehouse_id ?>"/>

                <div class="row pt-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Row Code</label>
                            <input type="text" id="RowCode" name="RowCode" class="form-control" value="<?= $row['RowCode'] ?? '' ?>"/>
                        </div>
                    </div>
                </div>

                <div class="row pt-3">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="Description" name="Description" rows="5"><?= $row['Description'] ?? '' ?></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }

    if ($action == "add_edit_shelf") {
        $shelf_id = mysqli_real_escape_string($conn, $_POST['shelf_id']);
        $warehouse_id = mysqli_real_escape_string($conn, $_POST['warehouse_id']);

        $checkQuery = "SELECT * FROM shelves WHERE ShelfID = '$shelf_id'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        }
        ?>
        <div class="card">
            <div class="card-body">
                <input type="hidden" id="ShelfID" name="ShelfID" class="form-control" value="<?= $row['ShelfID'] ?? $shelf_id ?>"/>
                <input type="hidden" id="WarehouseID" name="WarehouseID" class="form-control" value="<?= $row['WarehouseID'] ?? $warehouse_id ?>"/>

                <div class="row pt-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Shelf Code</label>
                            <input type="text" id="ShelfCode" name="ShelfCode" class="form-control" value="<?= $row['ShelfCode'] ?? '' ?>" required/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Row Code</label>
                            <select id="WarehouseRowID" class="form-control" name="WarehouseRowID" required>
                                <option value="" >Select One...</option>
                                <?php
                                $query_rows = "SELECT * FROM warehouse_rows WHERE WarehouseID = '" .$warehouse_id ."'";
                                $result_rows = mysqli_query($conn, $query_rows);            
                                while ($row_rows = mysqli_fetch_array($result_rows)) {
                                    $selected = (($row['WarehouseRowID'] ?? '') == $row_rows['WarehouseRowID']) ? 'selected' : '';
                                    
                                ?>
                                    <option value="<?= $row_rows['WarehouseRowID'] ?>" <?= $selected ?>><?= $row_rows['RowCode'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="Description" name="Description" rows="5"><?= $row['Description'] ?? '' ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    if ($action == "add_edit_bin") {
        $bin_id = mysqli_real_escape_string($conn, $_POST['bin_id']);
        $warehouse_id = mysqli_real_escape_string($conn, $_POST['warehouse_id']);

        $checkQuery = "SELECT * FROM bins WHERE BinID = '$bin_id'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
        }
        ?>
        <div class="card">
            <div class="card-body">
                <input type="hidden" id="BinID" name="BinID" class="form-control" value="<?= $row['BinID'] ?? $bin_id ?>"/>
                <input type="hidden" id="WarehouseID" name="WarehouseID" class="form-control" value="<?= $row['WarehouseID'] ?? $warehouse_id ?>"/>

                <div class="row pt-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Row Code</label>
                            <input type="text" id="BinCode" name="BinCode" class="form-control" value="<?= $row['BinCode'] ?? '' ?>"/>
                        </div>
                    </div>
                </div>

                <div class="row pt-3">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="Description" name="Description" rows="5"><?= $row['Description'] ?? '' ?></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }

    if ($action == "fetch_modal_bin") {
        $BinID = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM bins WHERE BinID  = '$BinID'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">Products on Bin <?= $row['BinCode'] ?></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="update_warehouse" class="form-horizontal">
                        <div class="modal-body">
                            <div class="datatables card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="tbl-bin-products" class="table search-table align-middle text-nowrap">
                                            <thead class="header-item">
                                            <th>Product Item</th>
                                            <th>Quantity</th>
                                            <th>Action</th>
                                            </thead>
                                            <tbody>
                                            <?php
                                                $no = 1;
                                                $query_warehouse = "SELECT *
                                                                    FROM inventory AS i
                                                                    LEFT JOIN product AS p ON i.product_id = p.product_id
                                                                    WHERE i.Bin_id = '$BinID'
                                                                    ;";
                                                $result_bin = mysqli_query($conn, $query_warehouse);            
                                                while ($row_bin = mysqli_fetch_array($result_bin)) {
                                                    $Inventory_id = $row_bin['Inventory_id'];
                                                    $product_item = $row_bin['product_item'];
                                                    $quantity = $row_bin['quantity'];
                                                ?>
                                                    <!-- start row -->
                                                    <tr class="search-items">
                                                        <td><?= $product_item ?></td>
                                                        <td><?= $quantity ?></td>
                                                        <td>
                                                            <div class="action-btn text-center">
                                                                <a href="#" id="view_warehouse_btn" class="text-primary transferInventory" data-id="<?= $row_bin['Inventory_id'] ?>">
                                                                    <i class="ti ti-truck fs-7"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php 
                                                $no++;
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <div class="form-actions">
                                <div class="card-body">
                                    <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                    <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <script>
                $(".phone-inputmask").inputmask("(999) 999-9999");
            </script>
            <?php
        }
    } 

    if ($action == "fetch_modal_row") {
        $WarehouseRowID = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM warehouse_rows WHERE WarehouseRowID  = '$WarehouseRowID'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">Products on Row <?= $row['RowCode'] ?></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="update_warehouse" class="form-horizontal">
                        <div class="modal-body">
                            <div class="datatables card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="tbl-row-products" class="table search-table align-middle text-nowrap">
                                            <thead class="header-item">
                                            <th>Product Item</th>
                                            <th>Quantity</th>
                                            <th>Action</th>
                                            </thead>
                                            <tbody>
                                            <?php
                                                $no = 1;
                                                $query_warehouse = "SELECT *
                                                                    FROM inventory AS i
                                                                    LEFT JOIN product AS p ON i.product_id = p.product_id
                                                                    WHERE i.Row_id = '$WarehouseRowID'
                                                                    ;";
                                                $result_row = mysqli_query($conn, $query_warehouse);            
                                                while ($row_row = mysqli_fetch_array($result_row)) {
                                                    $Inventory_id = $row_row['Inventory_id'];
                                                    $product_item = $row_row['product_item'];
                                                    $quantity = $row_row['quantity'];
                                                ?>
                                                    <!-- start row -->
                                                    <tr class="search-items">
                                                        <td><?= $product_item ?></td>
                                                        <td><?= $quantity ?></td>
                                                        <td>
                                                            <div class="action-btn text-center">
                                                                <a href="#" id="view_warehouse_btn" class="text-primary transferInventory" data-id="<?= $row_row['Inventory_id'] ?>">
                                                                    <i class="ti ti-truck fs-7"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php 
                                                $no++;
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <div class="form-actions">
                                <div class="card-body">
                                    <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                    <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <script>
                $(".phone-inputmask").inputmask("(999) 999-9999");
            </script>
            <?php
        }
    } 

    if ($action == "fetch_modal_shelf") {
        $ShelfID = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM shelves WHERE ShelfID  = '$ShelfID'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">Products on Shelf <?= $row['ShelfCode'] ?></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="update_warehouse" class="form-horizontal">
                        <div class="modal-body">
                            <div class="datatables card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="tbl-shelf-products" class="table search-table align-middle text-nowrap">
                                            <thead class="header-item">
                                            <th>Product Item</th>
                                            <th>Quantity</th>
                                            <th>Action</th>
                                            </thead>
                                            <tbody>
                                            <?php
                                                $no = 1;
                                                $query_warehouse = "SELECT *
                                                                    FROM inventory AS i
                                                                    LEFT JOIN product AS p ON i.product_id = p.product_id
                                                                    WHERE i.Shelves_id = '$ShelfID'
                                                                    ;";
                                                $result_shelf = mysqli_query($conn, $query_warehouse);            
                                                while ($row_shelf = mysqli_fetch_array($result_shelf)) {
                                                    $Inventory_id = $row_shelf['Inventory_id'];
                                                    $product_item = $row_shelf['product_item'];
                                                    $quantity = $row_shelf['quantity'];
                                                ?>
                                                    <!-- start row -->
                                                    <tr class="search-items">
                                                        <td><?= $product_item ?></td>
                                                        <td><?= $quantity ?></td>
                                                        <td>
                                                            <div class="action-btn text-center">
                                                                <a href="#" id="view_warehouse_btn" class="text-primary transferInventory" data-id="<?= $row_shelf['Inventory_id'] ?>">
                                                                    <i class="ti ti-truck fs-7"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php 
                                                $no++;
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <div class="form-actions">
                                <div class="card-body">
                                    <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                    <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <script>
                $(".phone-inputmask").inputmask("(999) 999-9999");
            </script>
            <?php
        }
    } 

    if ($action == "fetch_modal_transfer") {
        $Inventory_id = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM inventory WHERE Inventory_id  = '$Inventory_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);

            $WarehouseID = $row['Warehouse_id'];
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            Transfer <?= getProductName($row['Product_id']) ?> To
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="col-4">
                        <button type="button" id="btn-reopen-modal" class="btn btn-primary px-3 py-2">Back</button>                            
                    </div>
                    <form id="transfer_inventory" class="form-horizontal" >
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">
                                
                                <input type="hidden" id="Inventory_id" name="Inventory_id" class="form-control" value="<?= $Inventory_id ?>" />

                                <div class="row pt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">Warehouse</label>
                                    <select id="Warehouse_id" class="form-control select2-add" name="Warehouse_id">
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
                                    <select id="Shelves_id" class="form-control select2-add" name="Shelves_id">
                                        <option value="/" >Select Shelf...</option>
                                        <?php
                                        $query_shelf = "SELECT * 
                                                        FROM shelves s
                                                        INNER JOIN warehouse_rows wr ON s.WarehouseRowID = wr.WarehouseRowID
                                                        WHERE wr.WarehouseID = '$WarehouseID'";
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
                                    <select id="Bin_id" class="form-control select2-add" name="Bin_id">
                                        <option value="/" >Select Bin...</option>
                                        <?php
                                        $query_bin = "SELECT * FROM bins WHERE WarehouseID = '$WarehouseID'";
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
                                    <select id="Row_id" class="form-control select2-add" name="Row_id">
                                        <option value="/" >Select Row...</option>
                                        <?php
                                        $query_rows = "SELECT * FROM warehouse_rows WHERE WarehouseID = '$WarehouseID'";
                                        $result_rows = mysqli_query($conn, $query_rows);            
                                        while ($row_rows = mysqli_fetch_array($result_rows)) {
                                            $selected = ($row['Row_id'] == $row_rows['WarehouseRowID']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_rows['WarehouseRowID'] ?>" <?= $selected ?>><?= $row_rows['RowCode'] ?></option>
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
                                        <input type="date" id="Date" name="Date" class="form-control" value="<?= $row['Date'] ?>" />
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

    if ($action == "transfer_product") {
        $Inventory_id = mysqli_real_escape_string($conn, $_POST['Inventory_id']);
        $Warehouse_id = mysqli_real_escape_string($conn, $_POST['Warehouse_id']);
        $Shelves_id = mysqli_real_escape_string($conn, $_POST['Shelves_id']);
        $Bin_id = mysqli_real_escape_string($conn, $_POST['Bin_id']);
        $Row_id = mysqli_real_escape_string($conn, $_POST['Row_id']);
        $Date = mysqli_real_escape_string($conn, $_POST['Date']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    
        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM inventory WHERE Inventory_id = '$Inventory_id'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (!$result) {
            die("Error executing query: " . mysqli_error($conn));
        }
    
        if (mysqli_num_rows($result) > 0) {
            $status = '1';
            
            // Record exists, proceed with update
            $updateQuery = "UPDATE inventory SET 
                Warehouse_id = '$Warehouse_id',
                Shelves_id = '$Shelves_id',
                Bin_id = '$Bin_id',
                Row_id = '$Row_id',
                Date = '$Date',
                quantity = '$quantity',
                status = '$status'
                WHERE Inventory_id = '$Inventory_id'";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success";
            } else {
                die("Error updating record: " . mysqli_error($conn));
            }
        }
    }

    mysqli_close($conn);
}
?>
