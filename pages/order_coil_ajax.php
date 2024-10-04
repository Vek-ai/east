<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

$trim_id = 43;
$panel_id = 46;

function findCartKey($cart, $identifier, $line, $isCoil = false) {
    foreach ($cart as $key => $item) {
        if ($isCoil) {
            // Search by coil_id if $isCoil is true
            if (isset($item['coil_id']) && $item['coil_id'] == $identifier && $item['line'] == $line) {
                return $key;
            }
        } else {
            // Default search by product_id
            if (isset($item['product_id']) && $item['product_id'] == $identifier && $item['line'] == $line) {
                return $key;
            }
        }
    }
    return false;
}

if (isset($_POST['modifyquantity']) || isset($_POST['duplicate_product'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $line = isset($_POST['line']) ? (int)$_POST['line'] : 1;
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

    $quantityInStock = getProductStockInStock($product_id);
    $totalQuantity = getProductStockTotal($product_id);
    $totalStock = $quantityInStock + $totalQuantity;

    if (!isset($_SESSION["orders"])) {
        $_SESSION["orders"] = array();
    }

    if(isset($type) && $type == 'coil'){
        $key = findCartKey($_SESSION["orders"], $product_id, $line, true);
        
    }else{
        $key = findCartKey($_SESSION["orders"], $product_id, $line);
    }
    echo "$type";

    if (isset($_POST['duplicate_product'])) {
        $newLine = $line + 1;
        while (findCartKey($_SESSION["orders"], $product_id, $newLine) !== false) {
            $newLine++;
        }

        $query = "SELECT product_id, product_item, unit_price, width, length FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $item_quantity = min($qty, $totalStock);

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'unit_price' => $row['unit_price'],
                'line' => $newLine,
                'quantity_cart' => $item_quantity
                
            );

            $_SESSION["orders"][] = $item_array;
            echo "duplicated" .$item_quantity;
        }
    } elseif ($key !== false) {
        if (isset($_POST['setquantity'])) {
            $requestedQuantity = max($qty, 1);
            $_SESSION["orders"][$key]['quantity_cart'] = $requestedQuantity;
        } elseif (isset($_POST['addquantity'])) {
            $newQuantity = $_SESSION["orders"][$key]['quantity_cart'] + 1;
            $_SESSION["orders"][$key]['quantity_cart'] = $newQuantity;
        } elseif (isset($_POST['deductquantity'])) {
            $currentQuantity = $_SESSION["orders"][$key]['quantity_cart'];
            if ($currentQuantity <= 1) {
                array_splice($_SESSION["orders"], $key, 1);
            } else {
                $_SESSION["orders"][$key]['quantity_cart'] = $currentQuantity - 1;
            }
        }
    } else {
        // Product does not exist in cart
        $query = "SELECT product_id, product_item, unit_price, width, length FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $item_quantity = min($qty, $totalStock);

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'unit_price' => $row['unit_price'],
                'line' => 1,
                'quantity_cart' => $item_quantity
            );

            $_SESSION["orders"][] = $item_array;
            echo $item_quantity;
        }
    }
}

if (isset($_POST['deleteitem'])) {
    
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id_del']);
        $line = mysqli_real_escape_string($conn, $_POST['line']);
        
        $key = findCartKey($_SESSION["orders"], $product_id, $line);
        
        echo "ID: $product_id, Line: $line, Key: $key";
        
        if ($key !== false) {
            array_splice($_SESSION["orders"], $key, 1);
        } else {
            echo "Item not found in cart.";
        }
    
}

if (isset($_REQUEST['query'])) {
    $searchQuery = isset($_REQUEST['query']) ? mysqli_real_escape_string($conn, $_REQUEST['query']) : '';
    $color_id = isset($_REQUEST['type_id']) ? mysqli_real_escape_string($conn, $_REQUEST['color_id']) : '';
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

    if (!empty($color_id)) {
        $query_product .= " AND p.color = '$color_id'";
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

            $product_length = $row_product['length'];
            $product_width = $row_product['width'];
            $product_color = $row_product['color'];

            $dimensions = "";

            if (!empty($product_length) || !empty($product_width)) {
                $dimensions = '';
            
                if (!empty($product_length)) {
                    $dimensions .= $product_length;
                }
            
                if (!empty($product_width)) {
                    if (!empty($dimensions)) {
                        $dimensions .= " X ";
                    }
                    $dimensions .= $product_width;
                }
            
                if (!empty($dimensions)) {
                    $dimensions = " - " . $dimensions;
                }
            }

            if ($row_product['total_quantity'] > 0) {
                $stock_text = '
                    <a href="javascript:void(0);" id="view_in_stock" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center">
                        <span class="text-bg-success p-1 rounded-circle"></span>
                        <span class="ms-2">In Stock</span>
                    </a>';
            } else {
                $stock_text = '
                    <a href="javascript:void(0);" id="view_out_of_stock" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center">
                        <span class="text-bg-danger p-1 rounded-circle"></span>
                        <span class="ms-2">Out of Stock</span>
                    </a>';
            
                if ($row_product['product_category'] == $trim_id || $row_product['product_category'] == $panel_id) {
                    $sql = "SELECT COUNT(*) AS count FROM coil WHERE width = '$product_width' AND color = '$product_color'";
                    $result = mysqli_query($conn, $sql);

                    if ($result) {
                        $row = mysqli_fetch_assoc($result);
                        if ($row['count'] > 0) {
                            $stock_text = '
                            <a href="javascript:void(0);" id="view_available" data-color="' . htmlspecialchars($product_color, ENT_QUOTES) . '" data-width="' . htmlspecialchars($product_width, ENT_QUOTES) . '" class="d-flex align-items-center">
                                <span class="text-bg-warning p-1 rounded-circle"></span>
                                <span class="ms-2">Available</span>
                            </a>';
                        }
                    }
                }
            }
                     
            

            $default_image = 'images/product/product.jpg';

            $picture_path = !empty($row_product['main_image'])
            ? "" .$row_product['main_image']
            : $default_image;

            $tableHTML .= '
            <tr>
                <td style="width: 15% !important;">
                    <a href="javascript:void(0);" id="view_product_details" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center text-left">
                        <div class="d-flex align-items-center">
                            <img src="'.$picture_path.'" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                            <div class="ms-3">
                                <h6 class="fw-semibold mb-0 fs-4">'. $row_product['product_item'] .' ' .$dimensions .'</h6>
                            </div>
                        </div>
                    </a>
                </td>
                <td>
                    <div class="d-flex mb-0 gap-8">
                        <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:' .getColorHexFromColorID($row_product['color']) .'" data-toggle="tooltip" data-placement="top" title="'.getColorName($row_product['color']).'"></a> '
                         .'
                    </div>
                </td>
                <td><p class="mb-0">'. getProductTypeName($row_product['product_type']) .'</p></td>
                <td><p class="mb-0">'. getProductLineName($row_product['product_line']) .'</p></td>
                <td><p class="mb-0">'. getProductCategoryName($row_product['product_category']) .'</p></td>
                <td>
                    <div class="d-flex align-items-center text-center">'.$stock_text.'</div>
                </td>
                <td><h6 class="mb-0 fs-4">$'. $row_product['unit_cost'] .'</h6></td>
                <td>
                    <button class="btn btn-primary btn-add-to-cart px-2 py-1" type="button" data-id="'.$row_product['product_id'].'" onClick="addToOrders(this)">Add to Order</button>
                </td>
            </tr>';
        }
    } else {
        $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    
    //echo $tableHTML;
    echo $tableHTML;
}

if(isset($_POST['fetch_orders'])){
    ?>
    <style>
        .table-fixed {
            table-layout: fixed;
            width: 100%;
        }

        .table-fixed th,
        .table-fixed td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            word-wrap: break-word;
        }

        .table-fixed th:nth-child(1),
        .table-fixed td:nth-child(1) { width: 8%; }
        .table-fixed th:nth-child(2),
        .table-fixed td:nth-child(2) { width: 15%; }
        .table-fixed th:nth-child(3),
        .table-fixed td:nth-child(3) { width: 8%; }
        .table-fixed th:nth-child(4),
        .table-fixed td:nth-child(4) { width: 8%; }
        .table-fixed th:nth-child(5),
        .table-fixed td:nth-child(5) { width: 15%; }
        .table-fixed th:nth-child(6),
        .table-fixed td:nth-child(6) { width: 15%; }
        .table-fixed th:nth-child(7),
        .table-fixed td:nth-child(7) { width: 10%; }
        .table-fixed th:nth-child(8),
        .table-fixed td:nth-child(8) { width: 7%; }
        .table-fixed th:nth-child(9),
        .table-fixed td:nth-child(9) { width: 10%; }
        .table-fixed th:nth-child(10),
        .table-fixed td:nth-child(10) { width: 4%; }

        input[readonly] {
            border: none;               
            background-color: transparent;
            pointer-events: none;
            color: inherit;
        }

        .table-fixed tbody tr:hover input[readonly] {
            background-color: transparent;
        }
    </style>
        <div class="card-body datatables"> 
            <div class="product-details table-responsive text-nowrap">
                <table id="orderTable" class="table table-hover table-fixed mb-0 text-md-nowrap text-center">
                    <thead>
                        <tr>
                            <th width="5%">Image</th>
                            <th width="10%">Description</th>
                            <th width="5%" class="text-center">Color</th>
                            <th width="5%" class="text-center">Grade</th>
                            <th width="5%" class="text-center">Profile</th>
                            <th width="25%" class="text-center pl-3">Quantity</th>
                            <th width="5%" class="text-center">Stock</th>
                            <th width="7%" class="text-center">Price</th>
                            <th width="1%" class="text-center"> </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        $totalquantity = 0;
                        if (!empty($_SESSION["orders"])) {
                            foreach ($_SESSION["orders"] as $keys => $values) {
                                $data_id = $values["product_id"];
                                $product = getProductDetails($data_id);
                                $totalstockquantity = $values["quantity_ttl"] ?? 0 + $values["quantity_in_stock"] ?? 0;
                                $product_name = $values["product_item"];

                                if ($totalstockquantity > 0) {
                                    $stock_text = '
                                        <a href="javascript:void(0);" id="view_in_stock" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                                            <span class="text-bg-success p-1 rounded-circle"></span>
                                            <span class="ms-2">In Stock</span>
                                        </a>';
                                } else {
                                    $stock_text = '
                                        <a href="javascript:void(0);" id="view_out_of_stock" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                                            <span class="text-bg-danger p-1 rounded-circle"></span>
                                            <span class="ms-2">Out of Stock</span>
                                        </a>';
                                }
                                $type = 'product';

                                if(!isset($data_id)){
                                    $data_id = $values["coil_id"];
                                    $product = getCoilDetails($data_id);
                                    $product_name = $values["coil_item"];
                                    $type = 'coil';

                                    $stock_text = 'N/A';
                                }

                                 

                                $default_image = 'images/product/product.jpg';

                                $picture_path = !empty($row_product['main_image'])
                                ? "" .$row_product['main_image']
                                : $default_image;

                                $images_directory = "images/drawing/";
                                ?>
                                <tr>
                                    <td>
                                        <div class="align-items-center text-center w-100">
                                            <img src="<?= $picture_path ?>" class="rounded-circle " alt="materialpro-img" width="56" height="56">
                                        </div>
                                    </td>
                                    <td>
                                        <h6 class="fw-semibold mb-0 fs-4"><?= $product_name ?></h6>
                                    </td>
                                    <td>
                                        <div class="d-flex mb-0 gap-8">
                                            <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromColorID($product['color'])?>" data-toggle="tooltip" data-placement="top" title="<?= getColorName($product['color'])?>"></a>
                                        </div>
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
                                                <button class="btn btn-primary btn-icon p-1 mr-1" type="button" data-line="<?php echo $values["line"]; ?>" data-type="<?= $type ?>" data-id="<?php echo $data_id; ?>" onClick="deductquantity(this)">
                                                    -
                                                </button>
                                            </span> 
                                            <input class="form-control" type="text" size="5" value="<?php echo $values["quantity_cart"]; ?>" style="color:#ffffff;" data-type="<?= $type ?>" onchange="updatequantity(this)" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" id="item_quantity<?php echo $data_id;?>">
                                            <span class="input-group-btn">
                                                <button class="btn btn-primary btn-icon p-1 ml-1" type="button" data-line="<?php echo $values["line"]; ?>" data-type="<?= $type ?>" data-id="<?php echo $data_id; ?>" onClick="addquantity(this)">
                                                    +
                                                </button>
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <td><?= $stock_text ?></td>
                                    <td class="text-end pl-3">$
                                        <?php
                                        $subtotal = $values["quantity_cart"] * $values["unit_price"];
                                        echo number_format($subtotal, 2);
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger-gradient btn-sm" type="button" data-line="<?php echo $values["line"]; ?>" data-id="<?php echo $data_id; ?>" onClick="delete_item(this)"><i class="ti ti-trash"></i></button>
                                        <input type="hidden" class="form-control" data-id="<?php echo $data_id; ?>" id="item_id<?php echo $data_id; ?>" value="<?php echo $values["product_id"]; ?>">
                                        <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_ttl"];?>" id="warehouse_stock<?php echo $data_id;?>">
                                        <input class="form-control" type="hidden" size="5" value="<?php echo $values["line"];?>" id="line<?php echo $data_id;?>">
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
                            <td colspan="1"></td>
                            <td colspan="2" class="text-end">Total Quantity:</td>
                            <td colspan="1" class=""><span id="qty_ttl"><?= $totalquantity ?></span></td>
                            <td colspan="3" class="text-end">Amount Due:</td>
                            <td colspan="1" class="text-end"><span id="ammount_due"><?= $total ?> $</span></td>
                            <td colspan="1"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>           
        </div>
    <?php
}

if (isset($_POST['search_supplier'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search_supplier']);

    $query = "
        SELECT 
            supplier_id AS value, 
            supplier_name AS label
        FROM 
            supplier
        WHERE 
            supplier_name LIKE '%$search%'
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

if (isset($_POST['change_supplier'])) {
    if (isset($_POST['supplier_id'])) {
        $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
        $_SESSION['supplier_id'] = $supplier_id;
        echo 'success';
    } else {
        echo 'Error: Customer ID not provided.';
    }
}

if (isset($_POST['unset_supplier'])) {
    unset($_SESSION['supplier_id']);
    echo "Customer session unset";
}








