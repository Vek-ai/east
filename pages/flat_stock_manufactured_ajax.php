<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

$trim_id = 43;
$panel_id = 46;

if (isset($_REQUEST['query'])) {
    $searchQuery = isset($_REQUEST['query']) ? mysqli_real_escape_string($conn, $_REQUEST['query']) : '';

    $query_flat_stock = "SELECT * FROM flat_stock fs left join paint_colors pc on fs.color = pc.color_id WHERE 1";

    if (!empty($searchQuery)) {
        $query_flat_stock .= " AND (
                                color_name LIKE '%$searchQuery%' OR 
                                width LIKE '%$searchQuery%' OR 
                                length LIKE '%$searchQuery%' OR 
                                quantity LIKE '%$searchQuery%' OR 
                                notes LIKE '%$searchQuery%' OR 
                                date_added LIKE '%$searchQuery%'
                                )";
    }

    $result_coil = mysqli_query($conn, $query_flat_stock);

    $tableHTML = "";
    if (mysqli_num_rows($result_coil) > 0) {
        while ($row_flat_stock = mysqli_fetch_array($result_coil)) {

            $tableHTML .= '
            <tr>
                <td>
                    <div class="d-flex mb-0 gap-8">
                        <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:' .getColorHexFromColorID($row_flat_stock['color']) .'"></a> '
                        .getColorName($row_flat_stock['color']) .'
                    </div>
                </td>
                <td class="text-center">
                    '.$row_flat_stock['width'].'
                </td>
                <td class="text-center">
                    '.$row_flat_stock['length'].'
                </td>
                <td class="text-center">
                    '.$row_flat_stock['quantity'].'
                </td>
                <td class="text-center">
                    '.$row_flat_stock['notes'].'
                </td>
                <td>'. date("F d, Y", strtotime($row_flat_stock['date_added'])) . '</td>
            </tr>';
        }
    } else {
        $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    
    
    echo $tableHTML;
}

if(isset($_REQUEST['fetch_add_warehouse'])){
    $id = isset($_REQUEST['id']) ? mysqli_real_escape_string($conn, $_REQUEST['id']) : '';

    $query_flat_stock_process = "SELECT * FROM coil_process as cp left join product as p on cp.productid = p.product_id WHERE cp.id = '$id'";
    $result_coil_process = mysqli_query($conn, $query_flat_stock_process);
    if (mysqli_num_rows($result_coil_process) > 0) {
        while ($row_coil_process = mysqli_fetch_array($result_coil_process)) {
            $quantity = $row_coil_process['quantity'];
?>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Add to Warehouse
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add_inventory" class="form-horizontal">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body">
                            <input type="hidden" id="Product_id" name="Product_id" value="<?= $row_coil_process['product_id'] ?>" class="form-control"  />
                            <input type="hidden" id="coil_process_id" name="coil_process_id" value="<?= $row_coil_process['id'] ?>" class="form-control"  />
                            <div class="row pt-3">
                            <div class="col-md-12">
                                <label class="form-label">Product</label>
                                <div class="mb-3">
                                <p><?= getProductName($row_coil_process['product_id']) ?></p>
                                </div>
                            </div>

                            </div>
                            <div class="row pt-3">
                            <div class="col-md-6">
                                <label class="form-label">Supplier</label>
                                <div class="mb-3">
                                <select id="supplier_id" class="form-control select2-add" name="supplier_id">
                                    <option value="" >Select Supplier...</option>
                                    <optgroup label="Supplier">
                                        <?php
                                        $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
                                        $result_supplier = mysqli_query($conn, $query_supplier);            
                                        while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                            $selected = ($row_coil_process['supplier_id'] == $row_supplier['supplier_id']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_supplier['supplier_id'] ?>" ><?= $row_supplier['supplier_name'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </optgroup>
                                    
                                </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Warehouse</label>
                                <div class="mb-3">
                                <select id="Warehouse_id" class="form-control select2-add" name="Warehouse_id">
                                    <option value="" >Select Warehouse...</option>
                                    <optgroup label="Warehouse">
                                        <?php
                                        $query_warehouse = "SELECT * FROM warehouses WHERE status = '1'";
                                        $result_warehouse = mysqli_query($conn, $query_warehouse);            
                                        while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                                        ?>
                                            <option value="<?= $row_warehouse['WarehouseID'] ?>" ><?= $row_warehouse['WarehouseName'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </optgroup>
                                    
                                </select>
                                </div>
                            </div>
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-4">
                                <label class="form-label">Shelf</label>
                                <div class="mb-3">
                                <select id="Shelves_id" class="form-control select2-add" name="Shelves_id">
                                    <option value="" >Select Shelf...</option>
                                    <optgroup label="Shelf">
                                        <?php
                                        $query_shelf = "SELECT * FROM shelves";
                                        $result_shelf = mysqli_query($conn, $query_shelf);            
                                        while ($row_shelf = mysqli_fetch_array($result_shelf)) {
                                        ?>
                                            <option value="<?= $row_shelf['ShelfID'] ?>" ><?= $row_shelf['ShelfCode'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </optgroup>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bin</label>
                                <div class="mb-3">
                                <select id="Bin_id" class="form-control select2-add" name="Bin_id">
                                    <option value="" >Select Bin...</option>
                                    <optgroup label="Bin">
                                        <?php
                                        $query_bin = "SELECT * FROM bins";
                                        $result_bin = mysqli_query($conn, $query_bin);            
                                        while ($row_bin = mysqli_fetch_array($result_bin)) {
                                        ?>
                                            <option value="<?= $row_bin['BinID'] ?>" ><?= $row_bin['BinCode'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </optgroup>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Row</label>
                                <div class="mb-3">
                                <select id="Row_id" class="form-control select2-add" name="Row_id">
                                    <option value="" >Select Row...</option>
                                    <optgroup label="Row">
                                        <?php
                                        $query_rows = "SELECT * FROM warehouse_rows";
                                        $result_rows = mysqli_query($conn, $query_rows);            
                                        while ($row_rows = mysqli_fetch_array($result_rows)) {
                                        ?>
                                            <option value="<?= $row_rows['WarehouseRowID'] ?>" ><?= $row_rows['WarehouseRowID'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </optgroup>
                                </select>
                                </div>
                            </div>
                            </div>
                            <div class="row pt-3">
                                <div class="col-md-4">
                                    <label class="form-label">Quantity</label>
                                    <input type="text" id="quantity_add" name="quantity" class="form-control" value="<?= $quantity ?>" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pack</label>
                                    <div class="mb-3">
                                    <select id="pack_add" class="form-control select2-add" name="pack">
                                        <option value="" >Select Pack...</option>
                                        <optgroup label="Pack">
                                            <?php
                                            $query_pack = "SELECT * FROM product_pack WHERE hidden = '0'";
                                            $result_pack = mysqli_query($conn, $query_pack);            
                                            while ($row_pack = mysqli_fetch_array($result_pack)) {
                                            ?>
                                                <option value="<?= $row_pack['id'] ?>" data-count="<?= $row_pack['pieces_count'] ?>" ><?= $row_pack['pack_name'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Total Quantity</label>
                                    <input type="text" id="quantity_ttl_add" name="quantity_ttl" class="form-control" value="<?= $quantity ?>" />
                                </div>
                            </div>  
                            <div class="row pt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Date</label>
                                    <input type="date" id="Date" name="Date" class="form-control"  />
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
}

if(isset($_REQUEST['add_inventory'])){
    $coil_process_id = mysqli_real_escape_string($conn, $_POST['coil_process_id']);

    $Product_id = mysqli_real_escape_string($conn, $_POST['Product_id']);
    $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    $Warehouse_id = mysqli_real_escape_string($conn, $_POST['Warehouse_id']);
    $Shelves_id = mysqli_real_escape_string($conn, $_POST['Shelves_id']);
    $Bin_id = mysqli_real_escape_string($conn, $_POST['Bin_id']);
    $Row_id = mysqli_real_escape_string($conn, $_POST['Row_id']);
    $Date = mysqli_real_escape_string($conn, $_POST['Date']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $quantity_ttl = mysqli_real_escape_string($conn, $_POST['quantity_ttl']);
    $pack = mysqli_real_escape_string($conn, $_POST['pack']);
    $addedby = mysqli_real_escape_string($conn, $_POST['addedby']);

    $addedby = $_SESSION['userid']; 
    // Record does not exist, proceed with insert
    $insertQuery = "INSERT INTO inventory (
        Product_id, 
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
        $updateQuery  = "UPDATE coil_process SET transferred = '1' WHERE id = '$coil_process_id'";
        if (mysqli_query($conn, $updateQuery)) {
            echo "success";
        } else {
            die("Error updating record: " . mysqli_error($conn));
        }
    } else {
        die("Error inserting record: " . mysqli_error($conn));
    }
    
}



