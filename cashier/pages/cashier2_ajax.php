<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

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

if(isset($_REQUEST['deleteitem'])){
    $key = array_search($_REQUEST['product_id_del'], array_column($_SESSION["cart"], 'product_id'));
    array_splice($_SESSION["cart"], $key, 1);
}

if (isset($_REQUEST['query'])) {
    $searchQuery = isset($_REQUEST['query']) ? mysqli_real_escape_string($conn, $_REQUEST['query']) : '';
    $type_id = isset($_REQUEST['type_id']) ? mysqli_real_escape_string($conn, $_REQUEST['type_id']) : '';
    $line_id = isset($_REQUEST['line_id']) ? mysqli_real_escape_string($conn, $_REQUEST['line_id']) : '';
    $category_id = isset($_REQUEST['category_id']) ? mysqli_real_escape_string($conn, $_REQUEST['category_id']) : '';
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

    if (!empty($type_id)) {
        $query_product .= " AND p.product_type = '$type_id'";
    }

    if (!empty($line_id)) {
        $query_product .= " AND p.product_line = '$line_id'";
    }

    if (!empty($category_id)) {
        $query_product .= " AND p.product_category = '$category_id'";
    }

    $query_product .= " GROUP BY p.product_id";

    if ($onlyInStock) {
        $query_product .= " HAVING total_quantity > 1";
    }

    $result_product = mysqli_query($conn, $query_product);

    $tableHTML = "";

    if (mysqli_num_rows($result_product) > 0) {
        while ($row_product = mysqli_fetch_array($result_product)) {

            if($row_product['total_quantity'] > 0){
                $stock_text = '<span class="text-bg-success p-1 rounded-circle"></span><p class="mb-0 ms-2">InStock</p>';
            }else{
                $stock_text = '<span class="text-bg-danger p-1 rounded-circle"></span><p class="mb-0 ms-2">OutOfStock</p>';
            }

            if(!empty($row_product['main_image'])){
                $picture_path = $row_product['main_image'];
            }else{
                $picture_path = "images/product/product.jpg";
            }

            $tableHTML .= '
            <tr>
                <td>
                    <a href="/?page=product_details&product_id='.$row_product['product_id'].'">
                        <div class="d-flex align-items-center">
                            <img src="'.$picture_path.'" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                            <div class="ms-3">
                                <h6 class="fw-semibold mb-0 fs-4">'. $row_product['product_item'] .'</h6>
                            </div>
                        </div>
                    </a>
                </td>
                <td><p class="mb-0">'. getProductTypeName($row_product['product_type']) .'</p></td>
                <td><p class="mb-0">'. getProductLineName($row_product['product_line']) .'</p></td>
                <td><p class="mb-0">'. getProductCategoryName($row_product['product_category']) .'</p></td>
                <td>
                    <div class="d-flex align-items-center">'.$stock_text.'</div>
                </td>
                <td><h6 class="mb-0 fs-4">$'. $row_product['unit_cost'] .'</h6></td>
                <td>
                    <button class="btn btn-primary btn-add-to-cart" type="button" data-id="'.$row_product['product_id'].'" onClick="addtocart(this)">Add to Cart</button>
                </td>
            </tr>';
        }
    } else {
        $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    
    //echo $tableHTML;
    echo $tableHTML;
}

if(isset($_POST['fetch_cart'])){
    ?>
    <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Cart Contents</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="demo">
                        <div class="card-body">
                            <div class="product-details table-responsive text-nowrap">
                                <table id="productTable" class="table table-hover mb-0 text-md-nowrap">
                                    <thead>
                                        <tr>
                                            <th width="20%">Description</th>
                                            <th width="13%" class="text-center">Color</th>
                                            <th width="13%" class="text-center">Grade</th>
                                            <th width="13%" class="text-center">Profile</th>
                                            <th width="20%" class="text-center pl-3">Quantity</th>
                                            <th width="5%" class="text-center">Stock</th>
                                            <th width="10%" class="text-center">Price</i></th>
                                            <th width="6%" class="text-center">Action</i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total = 0;
                                        $totalquantity = 0;
                                        if (!empty($_SESSION["cart"])) {
                                            foreach ($_SESSION["cart"] as $keys => $values) {
                                                $data_id = $values["product_id"];

                                                $totalstockquantity = $values["quantity_ttl"] + $values["quantity_in_stock"];

                                                if ($totalstockquantity > 0) {
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
                                            ?>
                                                <tr>
                                                    <td>
                                                        <?php echo $values["product_item"]; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo getColorFromID($data_id); ?>
                                                        
                                                    </td>
                                                    <td>
                                                        <?php echo getGradeFromID($data_id); ?>
                                                        
                                                    </td>
                                                    <td>
                                                        <?php echo getProfileFromID($data_id); ?>
                                                        
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <span class="input-group-btn">
                                                                <button class="btn btn-primary btn-icon p-1 mr-1" type="button" data-id="<?php echo $data_id; ?>" onClick="deductquantity(this)">
                                                                    <i class="fa fa-minus"></i>
                                                                </button>
                                                            </span> 
                                                            <input class="form-control" type="text" size="5" value="<?php echo $values["quantity_cart"]; ?>" style="color:#ffffff;" onchange="updatequantity(this)" data-id="<?php echo $data_id; ?>" id="item_quantity<?php echo $data_id;?>">
                                                            <span class="input-group-btn">
                                                                <button class="btn btn-primary btn-icon p-1 ml-1" type="button" data-id="<?php echo $data_id; ?>" onClick="addquantity(this)">
                                                                    <i class="fa fa-plus"></i>
                                                                </button>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td><?= $stock_text ?></td>
                                                    <td class="text-end pl-3">$
                                                        <?php
                                                        $subtotal = ($values["quantity_cart"] * $values["unit_price"]);
                                                        echo number_format($subtotal, 2);
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-danger-gradient btn-sm" type="button" data-id="<?php echo $data_id; ?>" onClick="delete_item(this)"><i class="fa fa-trash"></i></button>
                                                        <input type="hidden" class="form-control" data-id="<?php echo $data_id; ?>" id="item_id<?php echo $data_id; ?>" value="<?php echo $values["product_id"]; ?>">
                                                        <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_ttl"];?>" id="warehouse_stock<?php echo $data_id;?>">
                                                        <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_in_stock"];?>" id="store_stock<?php echo $data_id;?>">
                                                    </td>
                                                </tr>
                                        <?php
                                                $totalquantity += $values["quantity_cart"];
                                                $total += $subtotal;
                                            }
                                            
                                        }
                                        $_SESSION["total_quantity"] = $totalquantity;
                                        $_SESSION["grandtotal"] = $total;
                                        ?>
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <td colspan="2"></td>
                                            <td colspan="1" class="text-end">Total Quantity:</td>
                                            <td colspan="1" class=""><span id="qty_ttl"><?= $totalquantity ?></span></td>
                                            <td colspan="1" class="text-end">Amount Due:</td>
                                            <td colspan="1" class="text-end"><span id="ammount_due"><?= $total ?> $</span></td>
                                            <td colspan="1"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    <?php
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



