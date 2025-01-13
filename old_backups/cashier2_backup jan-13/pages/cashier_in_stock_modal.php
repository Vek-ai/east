<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_in_stock_modal'])){
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);

    $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        ?>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Stock Details</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container py-4">
                        <h5 class="mb-3 fs-6 fw-semibold text-center">Inventory</h5>
                        <?php
                        $query_inventory = "
                            SELECT DISTINCT Warehouse_id FROM inventory WHERE Product_id = '$product_id'
                            UNION
                            SELECT 0 AS Warehouse_id";
                        $result_inventory = mysqli_query($conn, $query_inventory);

                        if ($result_inventory && mysqli_num_rows($result_inventory) > 0) {
                            echo '<div class="row">';

                            while ($row_inventory = mysqli_fetch_assoc($result_inventory)) {
                                $WarehouseID = $row_inventory['Warehouse_id'];

                                $query_inventory_details = "
                                    SELECT Bin_id, Row_id, Shelves_id, pack, quantity ,quantity_ttl
                                    FROM inventory 
                                    WHERE Warehouse_id = '$WarehouseID' AND Product_id = '$product_id'";
                                $result_inventory_details = mysqli_query($conn, $query_inventory_details);

                                if ($result_inventory_details && mysqli_num_rows($result_inventory_details) > 0) {
                                    $total_quantity = 0;
                                    $details = [];

                                    $used_bin = false;
                                    $used_row = false;
                                    $used_shelf = false;

                                    while ($inventory = mysqli_fetch_assoc($result_inventory_details)) {
                                        $packs = $inventory['pack'];
                                        $quantity = $inventory['quantity'];
                                        $item_quantity = $inventory['quantity_ttl'];
                                        $total_quantity += $item_quantity;
                                        if ($item_quantity > 0) {
                                            if (!$used_bin && !empty($inventory['Bin_id']) && $inventory['Bin_id'] != '0') {
                                                $details[] = [
                                                    'type' => 'BIN',
                                                    'id' => $inventory['Bin_id'],
                                                    'name' => getWarehouseBinName($inventory['Bin_id']),
                                                    'quantity' => $item_quantity
                                                ];
                                                $used_bin = true;
                                            } elseif (!$used_row && !empty($inventory['Row_id']) && $inventory['Row_id'] != '0') {
                                                $details[] = [
                                                    'type' => 'ROW',
                                                    'id' => $inventory['Row_id'],
                                                    'name' => getWarehouseRowName($inventory['Row_id']),
                                                    'quantity' => $item_quantity
                                                ];
                                                $used_row = true;
                                            } elseif (!$used_shelf && !empty($inventory['Shelves_id']) && $inventory['Shelves_id'] != '0') {
                                                $details[] = [
                                                    'type' => 'SHELF',
                                                    'id' => $inventory['Shelves_id'],
                                                    'name' => getWarehouseShelfName($inventory['Shelves_id']),
                                                    'quantity' => $item_quantity
                                                ];
                                                $used_shelf = true;
                                            }
                                        }
                                    }

                                    $warehouse_name = $WarehouseID == 0 ? 'Unallocated' : htmlspecialchars(getWarehouseName($WarehouseID));
                                    if($total_quantity > 0){
                                        echo "<div class='col-12 mt-3'>
                                            <div class='row p-3 border rounded bg-light'>
                                                <div class='col'>
                                                    <h5 class='mb-0 fs-5 fw-bold'>$warehouse_name</h5>
                                                </div>
                                                <div class='col text-end'>
                                                    <p class='mb-0 fs-3'><span class='badge bg-primary fs-3'>" . htmlspecialchars($total_quantity) . " PCS</span></p>
                                                </div>
                                            </div>
                                        </div>";

                                        foreach ($details as $detail) {
                                            if (!empty($detail['id']) && $detail['id'] != '0') {
                                                echo "<div class='col'>
                                                        <div class='row mb-0 p-2 border rounded bg-light'>
                                                            <h5 class='mb-0 fs-3 fw-bold'>{$detail['type']}: " . htmlspecialchars($detail['name']) . "</h5>
                                                            <p class='mb-0 fs-3'>{$packs} " . getPackName($packs) . " - " . htmlspecialchars($detail['quantity']) . " PCS</p>
                                                        </div>
                                                    </div>";
                                            }
                                        }
                                        unset($details);
                                    }
                                }
                            }
                            echo '</div>';
                        } else {
                            echo '<p class="mb-3 fs-4 fw-semibold text-center">This Product is not listed in the <a href="/?page=inventory">Inventory</a></p>';
                        }
                        ?>

                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
<?php
    }
}