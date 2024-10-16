<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

session_start();

function getProductStockInStock($product_id) {
    global $conn;
    $product_id = mysqli_real_escape_string($conn, $product_id);
    $query = "SELECT quantity_in_stock
              FROM product
              WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['quantity_in_stock'];
}

function getProductStockTotal($product_id) {
    global $conn;
    $product_id = mysqli_real_escape_string($conn, $product_id);
    $query = "SELECT COALESCE(SUM(quantity_ttl), 0) as total_quantity
              FROM inventory
              WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total_quantity'];
}

if (isset($_POST['modifyquantity'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

    $quantityInStock = getProductStockInStock($product_id);
    $totalQuantity = getProductStockTotal($product_id);
    $totalStock = $quantityInStock + $totalQuantity;

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = array();
    }

    $key = array_search($product_id, array_column($_SESSION["cart"], 'product_id'));

    if ($key !== false) {
        if (isset($_POST['setquantity'])) {
            $requestedQuantity = $qty;
            if ($requestedQuantity < 1) $requestedQuantity = 1;
            $_SESSION["cart"][$key]['quantity_cart'] = min($requestedQuantity, $totalStock);
            echo $_SESSION["cart"][$key]['quantity_cart'];

        } elseif (isset($_POST['addquantity'])) {
            $newQuantity = $_SESSION["cart"][$key]['quantity_cart'] + 1;
            $_SESSION["cart"][$key]['quantity_cart'] = ($newQuantity > $totalStock) ? $totalStock : $newQuantity;
            echo $_SESSION["cart"][$key]['quantity_cart'];

        } elseif (isset($_POST['deductquantity'])) {
            $currentQuantity = $_SESSION["cart"][$key]['quantity_cart'];
            if ($currentQuantity <= 1) {
                array_splice($_SESSION["cart"], $key, 1);
                echo 'removed';
            } else {
                $_SESSION["cart"][$key]['quantity_cart'] = $currentQuantity - 1;
                echo $_SESSION["cart"][$key]['quantity_cart'];
            }
        }
    } else {
        $query = "SELECT 
                    product_id,
                    product_item,
                    unit_price
                  FROM product
                  WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $item_quantity = min($qty, $totalStock);

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'unit_price' => $row['unit_price'],
                'quantity_ttl' => $totalStock,
                'quantity_in_stock' => $quantityInStock,
                'quantity_cart' => $item_quantity
            );

            $_SESSION["cart"][] = $item_array;
            echo $item_quantity;
        }
    }
}

if (isset($_REQUEST['query'])) {
    $searchQuery = isset($_REQUEST['query']) ? mysqli_real_escape_string($conn, $_REQUEST['query']) : '';
    $color_id = isset($_REQUEST['color_id']) ? mysqli_real_escape_string($conn, $_REQUEST['color_id']) : '';
    $grade_id = isset($_REQUEST['grade_id']) ? mysqli_real_escape_string($conn, $_REQUEST['grade_id']) : '';
    $profile_id = isset($_REQUEST['profile_id']) ? mysqli_real_escape_string($conn, $_REQUEST['profile_id']) : '';
    $onlyInStock = isset($_REQUEST['onlyInStock']) ? filter_var($_REQUEST['onlyInStock'], FILTER_VALIDATE_BOOLEAN) : false;
    

    $query_product = "
        SELECT 
            p.*,
            COALESCE(SUM(i.quantity_ttl), 0) AS total_quantity
        FROM 
            product AS p
        LEFT JOIN 
            inventory AS i ON p.product_id = i.product_id
        WHERE 
            p.hidden = '0'
    ";

    if (!empty($searchQuery)) {
        $query_product .= " AND (p.product_item LIKE '%$searchQuery%' OR p.description LIKE '%$searchQuery%')";
    }

    if (!empty($color_id)) {
        $query_product .= " AND p.color = '$color_id'";
    }

    if (!empty($grade_id)) {
        $query_product .= " AND p.grade = '$grade_id'";
    }

    if (!empty($profile_id)) {
        $query_product .= " AND p.profile = '$profile_id'";
    }

    $query_product .= " GROUP BY p.product_id";

    if ($onlyInStock) {
        $query_product .= " HAVING total_quantity > 1";
    }

    $result_product = mysqli_query($conn, $query_product);

    $tableHTML = "";

    if (mysqli_num_rows($result_product) > 0) {
        while ($row_product = mysqli_fetch_array($result_product)) {
            $data_id = $row_product["product_id"];

            if ($row_product['total_quantity'] > 0) {
                $stock_text = '
                    <a href="javascript:void(0);" id="view_product_details" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                        <span class="text-bg-success p-1 rounded-circle"></span>
                        <span class="ms-2">In Stock</span>
                    </a>';
            } else {
                $stock_text = '
                    <div class="d-flex align-items-center">
                        <span class="text-bg-danger p-1 rounded-circle"></span>
                        <span class="ms-2">Out of Stock</span>
                    </div>';
            }            
            

            $default_image = '../images/product/product.jpg';

            $picture_path = !empty($row_product['main_image']) && file_exists($row_product['main_image'])
            ? $row_product['main_image']
            : $default_image;

            $tableHTML .= '
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="' . htmlspecialchars($picture_path, ENT_QUOTES, 'UTF-8') . '" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                        <div class="ms-3">
                            <h6 class="fw-semibold mb-0 fs-4">' . htmlspecialchars($row_product['product_item'], ENT_QUOTES, 'UTF-8') . '</h6>
                        </div>
                    </div>
                </td>
                <td><p class="mb-0">' . htmlspecialchars(getColorName($row_product['color']), ENT_QUOTES, 'UTF-8') . '</p></td>
                <td><p class="mb-0">' . htmlspecialchars(getGradeName($row_product['grade']), ENT_QUOTES, 'UTF-8') . '</p></td>
                <td><p class="mb-0">' . htmlspecialchars(getProfileTypeName($row_product['profile']), ENT_QUOTES, 'UTF-8') . '</p></td>
                <td>
                    <div class="input-group">
                        <span class="input-group-btn">
                            <button class="btn btn-primary btn-icon" type="button" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" onClick="deductquantity(this)">
                                <i class="fa fa-minus"></i>
                            </button>
                        </span>
                        <input class="form-control" type="text" size="5" value="' . htmlspecialchars($values["quantity_cart"], ENT_QUOTES, 'UTF-8') . '" style="color:#ffffff;" onchange="updatequantity(this)" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" id="item_quantity' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '">
                        <span class="input-group-btn">
                            <button class="btn btn-primary btn-icon" type="button" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" onClick="addquantity(this)">
                                <i class="fa fa-plus"></i>
                            </button>
                        </span>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">' . $stock_text . '</div>
                </td>
                <td><h6 class="mb-0 fs-4">$' . htmlspecialchars($row_product['unit_cost'], ENT_QUOTES, 'UTF-8') . '</h6></td>
            </tr>';

        }
    } else {
        $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    
    echo $tableHTML;
    //echo $query_product;
}

if(isset($_POST['fetch_view_modal'])){
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);

    $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
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



