<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_modal") {
        $Inventory_id = mysqli_real_escape_string($conn, $_POST['id']);

        $checkQuery = "SELECT * FROM inventory WHERE Inventory_id = '$Inventory_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            function getValueLabel($table, $idField, $valueField, $idValue) {
                global $conn;
                $query = "SELECT $valueField FROM $table WHERE $idField = '$idValue' LIMIT 1";
                $result = mysqli_query($conn, $query);
                $data = mysqli_fetch_assoc($result);
                return $data[$valueField] ?? '-';
            }

            $productName = getProductName($row['Product_id']);
            $colorName = $row['color_id'];
            $supplierName = getSupplierName($row['supplier_id']);
            $warehouseName = getWarehouseName($row['Warehouse_id']);
            $shelfCode = getWarehouseShelfName($row['Shelves_id']);
            $binCode = getWarehouseBinName($row['Bin_id']);
            $rowID = getWarehouseRowName($row['Row_id']) ?? '-';

            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title">Inventory Details</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body">
                                <div class="row pt-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Product</label>
                                        <div class="form-control"><?= $productName ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Color</label>
                                        <div class="form-control"><?= $colorName ?></div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Supplier</label>
                                        <div class="form-control"><?= $supplierName ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Warehouse</label>
                                        <div class="form-control"><?= $warehouseName ?></div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Shelf</label>
                                        <div class="form-control"><?= $shelfCode ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bin</label>
                                        <div class="form-control"><?= $binCode ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Row</label>
                                        <div class="form-control"><?= $rowID ?></div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Quantity</label>
                                        <div class="form-control"><?= $row['quantity'] ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Pack</label>
                                        <div class="form-control"><?= $row['pack'] ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Total Quantity</label>
                                        <div class="form-control"><?= $row['quantity_ttl'] ?></div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Date</label>
                                        <div class="form-control"><?= $row['Date'] ?></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    
    mysqli_close($conn);
}
?>
