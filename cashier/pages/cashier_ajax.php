<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../../includes/dbconn.php';
require '../../includes/functions.php';

if (isset($_REQUEST['barcode'])) {
    $item_quantity = 1;
    $upc = mysqli_real_escape_string($conn, $_REQUEST['barcode']);
    $query_p = "SELECT * FROM product WHERE upc = '" . $upc . "'";
    $result_p = mysqli_query($conn, $query_p);
    $row_p = mysqli_fetch_array($result_p);
    $product_id = $row_p['product_id'];

    $cart_array = array(
        'vatexempt' => '0',
        'cash_amount' => '0',
        'creditcalculate' => '0',
        'creditcash_amount' => '0',
        'credit_amount' => '0',
        'discount' => '0'
    );
    $_SESSION["cart_data"][0] = $cart_array;

    if (isset($_REQUEST['qty']) && $_REQUEST['qty'] != '') {
        $item_quantity = (int)$_REQUEST['qty'];
    }

    $query = "
        SELECT 
            p.product_id,
            p.product_item,
            p.unit_price,
            COALESCE(SUM(i.quantity_ttl), 0) as quantity_ttl,
            p.quantity_in_stock
        FROM 
            product p
        LEFT JOIN 
            inventory i
        ON 
            p.product_id = i.product_id
        WHERE 
            p.upc = '$upc'
        GROUP BY 
            p.product_id, p.product_item, p.unit_price";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 0) {
        echo "wrong";
    } else {
        while ($row = mysqli_fetch_array($result)) {
            $available_quantity = $row['quantity_ttl'] + $row['quantity_in_stock'];

            if ($item_quantity > $available_quantity) {
                $item_quantity = $available_quantity;
                echo "0";
            }

            if (isset($_SESSION["cart"])) {
                $item_array_id = array_column($_SESSION["cart"], "product_id");

                if (!in_array($row['product_id'], $item_array_id)) {
                    $item_array = array(
                        'product_id' => $row['product_id'],
                        'product_item' => $row['product_item'],
                        'unit_price' => $row['unit_price'],
                        'quantity_ttl' => $row['quantity_ttl'],
                        'quantity_in_stock' => $row['quantity_in_stock'],
                        'quantity_cart' => $item_quantity
                    );
                    array_unshift($_SESSION["cart"], $item_array);
                } else {
                    $key = array_search($row['product_id'], array_column($_SESSION["cart"], 'product_id'));

                    if (isset($_REQUEST['qty']) && $_REQUEST['qty'] != '') {
                        if (($_SESSION["cart"][$key]['quantity_ttl'] + $item_quantity) > $available_quantity) {
                            $_SESSION["cart"][$key]['quantity_ttl'] = $available_quantity;
                            echo "0";
                        } else {
                            $_SESSION["cart"][$key]['quantity_ttl'] += $item_quantity;
                        }
                    } else {
                        if (($_SESSION["cart"][$key]['quantity_ttl'] + 1) > $available_quantity) {
                            $_SESSION["cart"][$key]['quantity_ttl'] = $available_quantity;
                            echo "0";
                        } else {
                            $_SESSION["cart"][$key]['quantity_ttl'] += 1;
                        }
                    }
                }
            } else {
                $item_array = array(
                    'product_id' => $row['product_id'],
                    'product_item' => $row['product_item'],
                    'unit_price' => $row['unit_price'],
                    'quantity_ttl' => $row['quantity_ttl'],
                    'quantity_in_stock' => $row['quantity_in_stock'],
                    'quantity_cart' => $item_quantity
                );
                $_SESSION["cart"] = array($item_array);
            }
        }
    }
}

if (isset($_REQUEST['product_id'])) {
    $product_id = mysqli_real_escape_string($conn, $_REQUEST['product_id']);
    
    $cart_array = array(
        'vatexempt' => '0',
        'cash_amount' => '0',
        'creditcalculate' => '0',
        'creditcash_amount' => '0',
        'credit_amount' => '0',
        'discount' => '0'
    );
    $_SESSION["cart_data"][0] = $cart_array;

    $item_quantity = isset($_REQUEST['qty']) && $_REQUEST['qty'] != '' ? (int)$_REQUEST['qty'] : 1;

    $query = "SELECT 
            p.product_id,
            p.product_item,
            p.unit_price,
            COALESCE(SUM(i.quantity_ttl), 0) as quantity_ttl,
            p.quantity_in_stock
        FROM 
            product p
        LEFT JOIN 
            inventory i
        ON 
            p.product_id = i.product_id
        WHERE 
            p.product_id = '$product_id'
        GROUP BY 
            p.product_id, p.product_item, p.unit_price";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $total_stock = $row['quantity_ttl'];
        
        if ($item_quantity > $total_stock) {
            $item_quantity = $total_stock;
        }

        if (isset($_SESSION["cart"])) {
            $item_array_id = array_column($_SESSION["cart"], "product_id");

            if (!in_array($product_id, $item_array_id)) {
                $item_array = array(
                    'product_id' => $row['product_id'],
                    'product_item' => $row['product_item'],
                    'unit_price' => $row['unit_price'],
                    'quantity_ttl' => $row['quantity_ttl'],
                    'quantity_in_stock' => $row['quantity_in_stock'],
                    'quantity_cart' => $item_quantity
                );
                array_unshift($_SESSION["cart"], $item_array);
            } else {
                $key = array_search($product_id, array_column($_SESSION["cart"], 'product_id'));

                $current_quantity = $_SESSION["cart"][$key]['quantity_ttl'];
                $new_quantity = $item_quantity + $current_quantity;

                if ($new_quantity > $total_stock) {
                    $_SESSION["cart"][$key]['quantity_ttl'] = $total_stock;
                    echo "0"; // Quantity adjusted to the maximum available stock
                } else {
                    $_SESSION["cart"][$key]['quantity_ttl'] = $new_quantity;
                }
            }
        } else {
            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'unit_price' => $row['unit_price'],
                'quantity_ttl' => $row['quantity_ttl'],
                'quantity_in_stock' => $row['quantity_in_stock'],
                'quantity_cart' => $item_quantity
            );
            $_SESSION["cart"] = array($item_array);
        }
    } else {
        //echo "Product not found";
    }
}

if(isset($_REQUEST['deleteitem'])){
    $key = array_search($_REQUEST['product_id_del'], array_column($_SESSION["cart"], 'product_id'));
    array_splice($_SESSION["cart"], $key, 1);
}

if (isset($_POST['update_qty'])) {
    $productId = $_REQUEST['product_id_update'];
    $key = array_search($productId, array_column($_SESSION["cart"], 'product_id'));

    if ($key !== false) {
        $totalStock = $_SESSION["cart"][$key]['quantity_ttl'] + $_SESSION["cart"][$key]['quantity_in_stock'];

        if (isset($_POST['item_quantity'])) {
            $requestedQuantity = $_POST['item_quantity'];

            if ($requestedQuantity > $totalStock) {
                $_SESSION["cart"][$key]['quantity_cart'] = $totalStock;
            } else {
                $_SESSION["cart"][$key]['quantity_cart'] = $requestedQuantity;
            }
        }

        if (isset($_POST['addquantity'])) {
            $newQuantity = $_SESSION["cart"][$key]['quantity_cart'] + 1;

            if ($newQuantity > $totalStock) {
                echo "greater";
            } else {
                $_SESSION["cart"][$key]['quantity_cart'] = $newQuantity;
            }
        }

        if (isset($_POST['deductquantity'])) {
            $currentQuantity = $_SESSION["cart"][$key]['quantity_cart'];

            if ($currentQuantity <= 1) {
                array_splice($_SESSION["cart"], $key, 1);
            } else {
                $_SESSION["cart"][$key]['quantity_cart'] = $currentQuantity - 1;
            }
        }
    }
}

if(isset($_POST['fetch_view_modal'])){
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);

    // SQL query to check if the record exists
    $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Record exists, fetch current values
        $row = mysqli_fetch_assoc($result);
        
        ?>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Product Details</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container py-4">
                        <h5 class="mb-3 fs-6 fw-semibold text-center">Inventory</h5>
                        <?php
                        $query_inventory = "SELECT DISTINCT Warehouse_id FROM inventory WHERE Product_id = '$product_id' AND Warehouse_id != '0'";
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
                                    while ($inventory = mysqli_fetch_assoc($result_inventory_details)) {
                                        $packs = $inventory['pack'];
                                        $quantity = $inventory['quantity'];
                                        $item_quantity = $inventory['quantity_ttl'];
                                        $total_quantity += $inventory['quantity_ttl'];

                                        $details[] = [
                                            'type' => 'BIN',
                                            'id' => $inventory['Bin_id'],
                                            'name' => getWarehouseBinName($inventory['Bin_id']),
                                            'quantity' => $item_quantity
                                        ];
                                        $details[] = [
                                            'type' => 'ROW',
                                            'id' => $inventory['Row_id'],
                                            'name' => getWarehouseRowName($inventory['Row_id']),
                                            'quantity' => $item_quantity
                                        ];
                                        $details[] = [
                                            'type' => 'SHELF',
                                            'id' => $inventory['Shelves_id'],
                                            'name' => getWarehouseShelfName($inventory['Shelves_id']),
                                            'quantity' => $item_quantity
                                        ];
                                    }

                                    echo "<div class='col-12 mt-3'>
                                            <div class='row p-3 border rounded bg-light'>
                                                <div class='col'>
                                                    <h5 class='mb-0 fs-5 fw-bold'>" . htmlspecialchars(getWarehouseName($WarehouseID)) . "</h5>
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

if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search']);

    $query = "
        SELECT product_id AS value, product_item AS label
        FROM product
        WHERE (product_item LIKE '%$search%' OR upc LIKE '%$search%')
    ";

    if (isset($_POST['color_id']) && !empty($_POST['color_id'])) {
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $query .= " AND color = '$color_id'";
    }

    if (isset($_POST['grade_id']) && !empty($_POST['grade_id'])) {
        $grade_id = mysqli_real_escape_string($conn, $_POST['grade_id']);
        $query .= " AND grade = '$grade_id'";
    }

    if (isset($_POST['profile_id']) && !empty($_POST['profile_id'])) {
        $profile_id = mysqli_real_escape_string($conn, $_POST['profile_id']);
        $query .= " AND profile = '$profile_id'";
    }

    $query .= " LIMIT 15";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $response = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }
        echo json_encode($response);
    } else {
        echo json_encode(array('error' => 'Query failed'));
    }
}


if (isset($_POST['search_customer'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search_customer']);

    $query = "
        SELECT 
            customer_id AS value, 
            CONCAT(customer_first_name, ' ', customer_last_name) AS label
        FROM 
            customer
        WHERE 
            customer_first_name LIKE '%$search%' 
            OR customer_last_name LIKE '%$search%'
        LIMIT 15
    ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $response = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }
        echo json_encode($response);
    } else {
        echo json_encode(array('error' => 'Query failed'));
    }
}
?>