<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

function findCartKey($cart, $product_id, $line) {
    foreach ($cart as $key => $item) {
        if ($item['product_id'] == $product_id && $item['line'] == $line) {
            return $key;
        }
    }
    return false;
}

if (isset($_POST['modifyquantity']) || isset($_POST['duplicate_product'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $line = isset($_POST['line']) ? (int)$_POST['line'] : 1;
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

    $quantityInStock = getProductStockInStock($product_id);
    $totalQuantity = getProductStockTotal($product_id);
    $totalStock = $quantityInStock + $totalQuantity;

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = array();
    }

    $key = findCartKey($_SESSION["cart"], $product_id, $line);

    if (isset($_POST['duplicate_product'])) {
        $newLine = $line + 1;
        while (findCartKey($_SESSION["cart"], $product_id, $newLine) !== false) {
            $newLine++;
        }

        $query = "SELECT product_id, product_item, unit_price FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $item_quantity = min($qty, $totalStock);

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'unit_price' => $row['unit_price'],
                'line' => $newLine,
                'quantity_ttl' => $totalStock,
                'quantity_in_stock' => $quantityInStock,
                'quantity_cart' => $item_quantity
            );

            $_SESSION["cart"][] = $item_array;
            echo $item_quantity;
        }
    } elseif ($key !== false) {
        // Handle existing item
        if (isset($_POST['setquantity'])) {
            $requestedQuantity = max($qty, 1);
            echo "ID: $product_id, Line: $line, Key: $key, Quantity: $requestedQuantity";
            $_SESSION["cart"][$key]['quantity_cart'] = min($requestedQuantity, $totalStock);
            echo $_SESSION["cart"][$key]['quantity_cart'];
        } elseif (isset($_POST['addquantity'])) {
            echo "ID: $product_id, Line: $line, Key: $key";
            $newQuantity = $_SESSION["cart"][$key]['quantity_cart'] + 1;
            $_SESSION["cart"][$key]['quantity_cart'] = min($newQuantity, $totalStock);
            echo $_SESSION["cart"][$key]['quantity_cart'];
        } elseif (isset($_POST['deductquantity'])) {
            echo "ID: $product_id, Line: $line, Key: $key";
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
        // Product does not exist in cart
        $query = "SELECT product_id, product_item, unit_price FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $item_quantity = min($qty, $totalStock);

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'unit_price' => $row['unit_price'],
                'line' => 1,
                'quantity_ttl' => $totalStock,
                'quantity_in_stock' => $quantityInStock,
                'quantity_cart' => $item_quantity
            );

            $_SESSION["cart"][] = $item_array;
            echo $item_quantity;
        }
    }
}

if (isset($_POST['deleteitem'])) {
    
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id_del']);
        $line = mysqli_real_escape_string($conn, $_POST['line']);
        
        $key = findCartKey($_SESSION["cart"], $product_id, $line);
        
        echo "ID: $product_id, Line: $line, Key: $key";
        
        if ($key !== false) {
            array_splice($_SESSION["cart"], $key, 1);
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
            }            
            

            $default_image = '../images/product/product.jpg';

            $picture_path = !empty($row_product['main_image'])
            ? "../" .$row_product['main_image']
            : $default_image;

            $tableHTML .= '
            <tr>
                <td>
                    <a href="javascript:void(0);" id="view_product_details" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center">
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
                        <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:' .getColorHexFromColorID($row_product['color']) .'"></a> '
                        .getColorName($row_product['color']) .'
                    </div>
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

if (isset($_POST['set_estimate_hem'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $hem = mysqli_real_escape_string($conn, $_POST['hem']);

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['estimate_hem'] = !empty($hem) ? $hem : "";
    }
    echo "Hem ID: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_estimate_bend'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $bend = mysqli_real_escape_string($conn, $_POST['bend']);

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['estimate_bend'] = !empty($bend) ? $bend : "";
    }
    echo "Bend ID: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_estimate_height'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $height = mysqli_real_escape_string($conn, $_POST['height']);

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['estimate_height'] = !empty($height) ? $height : "";
    }
    echo "Height ID: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_estimate_width'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $width = mysqli_real_escape_string($conn, $_POST['width']);

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['estimate_width'] = !empty($width) ? $width : "";
    }
    echo "Width ID: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_estimate_length'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $length = mysqli_real_escape_string($conn, $_POST['length']);

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['estimate_length'] = !empty($length) ? $length : "";
    }
    echo "Length ID: $product_id, Line: $line, Key: $key";
}


if (isset($_POST['save_estimate'])) {
    $cart = $_SESSION['cart'];

    if (!isset($_SESSION['customer_id'])) {
        echo "Customer ID is not set.";
        exit;
    }

    $customerid = intval($_SESSION['customer_id']);
    $total_price = 0;
    $estimateid = null;

    if (!empty($cart)) {
        $estimated_date = date('Y-m-d H:i:s');

        foreach ($cart as $item) {
            $unit_price = floatval($item['unit_price']);
            $quantity_cart = intval($item['quantity_cart']);
            $total_price += $unit_price * $quantity_cart;
        }

        $discounted_price = number_format($total_price * 0.9, 2);

        $query = "INSERT INTO estimates (total_price, discounted_price, estimated_date, customerid) VALUES ('$total_price', '$discounted_price', '$estimated_date', '$customerid')";
        if ($conn->query($query) === TRUE) {
            $estimateid = $conn->insert_id;
        } else {
            echo "Error inserting estimate: " . $conn->error;
            exit;
        }
    }

    if ($estimateid) {
        $query = "INSERT INTO estimate_prod (estimateid, product_id, quantity, custom_width, custom_height, actual_price, discounted_price) VALUES ";

        $values = [];
        foreach ($cart as $item) {
            $product_id = intval($item['product_id']);
            $quantity_cart = intval($item['quantity_cart']);
            $unit_price = floatval($item['unit_price']);

            $estimate_width = floatval($item['estimate_width']);
            $estimate_height = floatval($item['estimate_height']);

            $discounted_price = number_format($unit_price * 0.9, 2);

            $values[] = "('$estimateid', '$product_id', '$quantity_cart', '$estimate_width', '$estimate_height', '$unit_price', '$discounted_price')";
        }

        $query .= implode(', ', $values);

        if ($conn->query($query) === TRUE) {
            echo "success";

            unset($_SESSION['cart']);
        } else {
            echo "Error inserting estimate products: " . $conn->error;
        }
    }

    $conn->close();
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

if (isset($_POST['change_customer'])) {
    if (isset($_POST['customer_id'])) {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $_SESSION['customer_id'] = $customer_id;
        echo 'success';
    } else {
        echo 'Error: Customer ID not provided.';
    }
}

if (isset($_POST['unset_customer'])) {
    unset($_SESSION['customer_id']);
    echo "Customer session unset";
}





