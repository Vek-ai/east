<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

$trim_id = 4;
$panel_id = 3;

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
    $totalStock = $totalQuantity;

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = array();
    }

    $key = findCartKey($_SESSION["cart"], $product_id, $line);

    if (isset($_POST['duplicate_product'])) {
        $newLine = $line + 1;
        while (findCartKey($_SESSION["cart"], $product_id, $newLine) !== false) {
            $newLine++;
        }

        $query = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $item_quantity = min($qty, $totalStock);

            $length = $row['length'];
            $length_clean = preg_replace('/[^0-9.]/', '', $length);
            $length_float = floatval($length_clean);
            $estimate_length = floor($length_float);
            $estimate_length_inch = $length_float - $estimate_length;

            $weight = floatval($row['weight']);

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'unit_price' => $row['unit_price'],
                'line' => $newLine,
                'quantity_ttl' => $totalStock,
                'quantity_in_stock' => $quantityInStock,
                'quantity_cart' => $item_quantity,
                'estimate_width' => $row['width'],
                'estimate_length' => '',
                'estimate_length_inch' => '',
                'usage' => 0,
                'custom_color' => $row['color'],
                'weight' => $weight,
                'custom_grade' => intval($row['grade'])
            );

            $_SESSION["cart"][] = $item_array;
            echo $item_quantity;
        }
    } elseif ($key !== false) {
        if (isset($_POST['setquantity'])) {
            $requestedQuantity = max($qty, 1);
            $_SESSION["cart"][$key]['quantity_cart'] = $requestedQuantity;
            echo $_SESSION["cart"][$key]['quantity_cart'];
        } elseif (isset($_POST['addquantity'])) {
            $newQuantity = $_SESSION["cart"][$key]['quantity_cart'] + 1;
            $_SESSION["cart"][$key]['quantity_cart'] = $newQuantity;
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
        $query = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $item_quantity = min($qty, $totalStock);

            $weight = floatval($row['weight']);

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'unit_price' => $row['unit_price'],
                'line' => 1,
                'quantity_ttl' => $totalStock,
                'quantity_in_stock' => $quantityInStock,
                'quantity_cart' => $item_quantity,
                'estimate_width' => $row['width'],
                'estimate_length' => '',
                'estimate_length_inch' => '',
                'usage' => 0,
                'custom_color' => $row['color'],
                'weight' => $weight,
                'custom_grade' => intval($row['grade'])
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
    $color_id = isset($_REQUEST['color_id']) ? mysqli_real_escape_string($conn, $_REQUEST['color_id']) : '';
    $grade_id = isset($_REQUEST['grade_id']) ? mysqli_real_escape_string($conn, $_REQUEST['grade_id']) : '';
    $gauge_id = isset($_REQUEST['gauge_id']) ? mysqli_real_escape_string($conn, $_REQUEST['gauge_id']) : '';
    $type_id = isset($_REQUEST['type_id']) ? mysqli_real_escape_string($conn, $_REQUEST['type_id']) : '';
    $profile_id = isset($_REQUEST['profile_id']) ? mysqli_real_escape_string($conn, $_REQUEST['profile_id']) : '';
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

    if (!empty($grade_id)) {
        $query_product .= " AND p.grade = '$grade_id'";
    }

    if (!empty($gauge_id)) {
        $query_product .= " AND p.gauge = '$gauge_id'";
    }

    if (!empty($type_id)) {
        $query_product .= " AND p.product_type = '$type_id'";
    }

    if (!empty($profile_id)) {
        $query_product .= " AND p.profile = '$profile_id'";
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
                    $sql = "SELECT COUNT(*) AS count FROM coil WHERE color = '$product_color'";
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
                     
            $default_image = '../images/product/product.jpg';

            $picture_path = !empty($row_product['main_image'])
            ? "../" .$row_product['main_image']
            : $default_image;

            $tableHTML .= '
            <tr>
                <td>
                    <a href="javascript:void(0);" id="view_product_details" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center">
                        <div class="d-flex align-items-center" >
                            <img src="'.$picture_path.'" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                            <div class="ms-3">
                                <h6 class="fw-semibold mb-0 fs-4">'. $row_product['product_item'] .' ' .$dimensions .'</h6>
                            </div>
                        </div>
                    </a>
                </td>
                <td>
                    <div class="d-flex mb-0 gap-8">
                        <a href="javascript:void(0)" id="view_available_color" data-id="'.$row_product['product_id'].'">See Colors</a>
                    </div>
                </td>
                <td><p class="mb-0">'. getGradeName($row_product['grade']) .'</p></td>
                <td><p class="mb-0">'. getGaugeName($row_product['gauge']) .'</p></td>
                <td><p class="mb-0">'. getProductTypeName($row_product['product_type']) .'</p></td>
                <td><p class="mb-0">'. getProfileTypeName($row_product['profile']) .'</p></td>
                <td><p class="mb-0">'. getProductCategoryName($row_product['product_category']) .'</p></td>
                <td>
                    <div class="d-flex align-items-center">'.$stock_text.'</div>
                </td>
                
                <td>
                    <button class="btn btn-sm btn-primary btn-add-to-cart" type="button" data-id="'.$row_product['product_id'].'" id="add-to-cart-btn">Add to Cart</button>
                </td>
            </tr>';
        }
    } else {
        $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    
    //echo $tableHTML;
    echo $tableHTML;
}

if (isset($_POST['set_usage'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $usage = mysqli_real_escape_string($conn, $_POST['usage']);

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['usage'] = !empty($usage) ? $usage : "";
    }
    echo "usage ID: $usage, Line: $line, Key: $key";
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

if (isset($_POST['set_estimate_length_inch'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $length_inch = mysqli_real_escape_string($conn, $_POST['length_inch']);

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['estimate_length_inch'] = !empty($length_inch) ? $length_inch : "";
    }
    echo "Length-inch ID: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_color'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['custom_color'] = !empty($color_id) ? $color_id : "";
    }
    echo "Color id: $color_id, Prod id: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_grade'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['custom_grade'] = !empty($grade) ? $grade : "";
    }
    echo "grade id: $grade, Prod id: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['save_estimate'])) {
    $response = [
        'success' => false,
        'message' => '',
        'estimate_id' => null
    ];

    $credit_amt = floatval($_POST['credit_amt']);
    $cash_amt = floatval($_POST['cash_amt']);
    $job_name = mysqli_real_escape_string($conn, $_POST['job_name'] ?? '');
    $job_po = mysqli_real_escape_string($conn, $_POST['job_po'] ?? '');
    $deliver_address = mysqli_real_escape_string($conn, $_POST['deliver_address'] ?? '');
    $deliver_city = mysqli_real_escape_string($conn, $_POST['deliver_city'] ?? '');
    $deliver_state = mysqli_real_escape_string($conn, $_POST['deliver_state'] ?? '');
    $deliver_zip = mysqli_real_escape_string($conn, $_POST['deliver_zip'] ?? '');
    $delivery_amt = mysqli_real_escape_string($conn, $_POST['delivery_amt'] ?? '');
    $deliver_fname = mysqli_real_escape_string($conn, $_POST['deliver_fname'] ?? '');
    $deliver_lname = mysqli_real_escape_string($conn, $_POST['deliver_lname'] ?? '');

    if (!isset($_SESSION['customer_id']) || empty($_SESSION['cart'])) {
        $response['message'] = "Customer ID or cart is not set.";
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $customerid = intval($_SESSION['customer_id']);
    $cart = $_SESSION['cart'];
    $estimated_date = date('Y-m-d H:i:s');
    $discount_default = floatval(getCustomerDiscount($customerid)) / 100;

    $customer_details = getCustomerDetails($customerid);
    $credit_limit = number_format($customer_details['credit_limit'] ?? 0,2);
    $credit_total = number_format(getCustomerCreditTotal($customerid),2);

    if($credit_amt > 0){
        $credit_available = $credit_limit - $credit_total;
        if($credit_available <= 0){
            $response['error'] = "Cannot pay via Credit! The Customer’s credit limit has been reached";
            echo json_encode($response);
            exit;
        }
        
        if($credit_amt > $credit_limit){
            $response['error'] = "Credit amount cannot exceed the customer's credit limit";
            echo json_encode($response);
            exit;
        }
    }

    $total_actual_price = 0;
    $total_discounted_price = 0;
    foreach ($cart as $item) {
        $discount = 0;
        if(isset($item['used_discount'])){
            $discount = $item['used_discount'] / 100;
        }else{
            $discount = $discount_default;
        }
        $unit_price = floatval($item['unit_price']);
        $quantity_cart = intval($item['quantity_cart']);
        $product_details = getProductDetails($item['product_id']);
        $is_sold_by_feet = intval($product_details['sold_by_feet']);
        $estimate_length = floatval($item['estimate_length']);
        $estimate_length_inch = floatval($item['estimate_length_inch']);

        $total_length = !empty($is_sold_by_feet) ? ($estimate_length + ($estimate_length_inch / 12)) : 1;

        $actual_price = $unit_price * $total_length * $quantity_cart;
        $discounted_price = $actual_price * (1 - $discount);

        $total_actual_price += $actual_price;
        $total_discounted_price += $discounted_price;
    }

    $query = "INSERT INTO estimates (total_price, discounted_price, discount_percent, estimated_date, customerid, originalcustomerid, cash_amt, credit_amt, job_name, job_po, deliver_address,  deliver_city,  deliver_state,  deliver_zip, delivery_amt, deliver_fname, deliver_lname) 
              VALUES ('$total_actual_price', '$total_discounted_price', '".($discount * 100)."', '$estimated_date', '$customerid', '$customerid', '$cash_amt', '$credit_amt' , '$job_name' , '$job_po' , '$deliver_address', '$deliver_city', '$deliver_state', '$deliver_zip' , '$delivery_amt' , '$deliver_fname' , '$deliver_lname')";

    if ($conn->query($query) === TRUE) {
        $estimateid = $conn->insert_id;

        $values = [];
        foreach ($cart as $item) {
            $discount = 0;
            if(isset($item['used_discount'])){
                $discount = $item['used_discount'] / 100;
            }else{
                $discount = $discount_default;
            }
            $product_id = intval($item['product_id']);
            $product_details = getProductDetails($product_id);
            $quantity_cart = intval($item['quantity_cart']);
            $unit_price = floatval($item['unit_price']);
            $estimate_width = !empty($item['estimate_width']) ? floatval($item['estimate_width']) : $product_details['width'];
            $estimate_bend = floatval($item['estimate_bend']);
            $estimate_hem = floatval($item['estimate_hem']);
            $estimate_length = floatval($item['estimate_length']);
            $estimate_length_inch = floatval($item['estimate_length_inch']);
            $custom_color = $item['custom_color'];
            $custom_grade = $item['custom_grade'];
            $is_sold_by_feet = intval($product_details['sold_by_feet']);
            
            $total_length = !empty($is_sold_by_feet) ? ($estimate_length + ($estimate_length_inch / 12)) : 1;

            $actual_price = $unit_price * $total_length;
            $discounted_price = $actual_price * (1 - $discount);

            $curr_discount = intval(getCustomerDiscountProfile($customerid));
            $loyalty_discount = intval(getCustomerDiscountLoyalty($customerid));
            $used_discount = !empty($item['used_discount']) ? $item['used_discount'] : getCustomerDiscount($customerid);

            $values[] = "('$estimateid', '$product_id', '$quantity_cart', '$estimate_width', '$estimate_bend', '$estimate_hem', '$estimate_length', '$estimate_length_inch', '$actual_price', '$discounted_price', '$custom_color', '$custom_grade', '$curr_discount', '$loyalty_discount', '$used_discount')";

        }

        $query = "INSERT INTO estimate_prod (estimateid, product_id, quantity, custom_width, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price, custom_color, custom_grade, current_customer_discount, current_loyalty_discount, used_discount) VALUES ";
        $query .= implode(', ', $values);

        if ($conn->query($query) === TRUE) {
            $query = "INSERT INTO order_estimate (order_estimate_id, type) VALUES ('$estimateid','1')";
            if ($conn->query($query) === TRUE) {
                unset($_SESSION['cart']);
                $response['success'] = true;
                $response['message'] = "Estimate and products successfully saved.";
                $response['estimate_id'] = $estimateid;
            }else{
                $response['message'] = "Error inserting order estimate records: " . $conn->error;
            }
        } else {
            $response['message'] = "Error inserting estimate products: " . $conn->error;
        }
    } else {
        $response['message'] = "Error inserting estimate: " . $conn->error;
    }

    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
}

if (isset($_POST['load_estimate'])) {
    $estimateid = intval($_POST['id']);

    $_SESSION['cart'] = [];
    
    $response = [
        'success' => false,
        'message' => '',
        'estimate' => null
    ];

    $query = "SELECT * FROM estimates WHERE estimateid = '$estimateid'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $estimate = $result->fetch_assoc();
        $_SESSION['customer_id'] = $estimate['customerid'];
        $_SESSION['estimateid'] = $estimateid;

        $query = "SELECT * FROM estimate_prod WHERE estimateid = '$estimateid'";
        $result_products = $conn->query($query);

        if ($result_products && $result_products->num_rows > 0) {
            $cart = [];
            $line = 1;

            while ($row = $result_products->fetch_assoc()) {
                $product_details = getProductDetails($row['product_id']);

                $quantityInStock = getProductStockInStock($row['product_id']);
                $totalQuantity = getProductStockTotal($row['product_id']);
                $totalStock = $totalQuantity;

                $cart[] = [
                    'line' => $line,
                    'product_id' => $row['product_id'],
                    'product_item' => $product_details['product_item'],
                    'quantity_cart' => $row['quantity'],
                    'quantity_ttl' => $totalStock,
                    'quantity_in_stock' => $quantityInStock,
                    'unit_price' => $row['actual_price'],
                    'estimate_width' => $row['custom_width'],
                    'estimate_bend' => $row['custom_bend'],
                    'estimate_hem' => $row['custom_hem'],
                    'estimate_length' => $row['custom_length'],
                    'estimate_length_inch' => $row['custom_length2'],
                    'custom_color' => $row['custom_color'],
                    'usageid' => $row['usageid']
                ];

                $line++;
            }
            $_SESSION['cart'] = $cart;

            $response['success'] = true;
            $response['message'] = "Estimate successfully loaded into session.";
        } else {
            $response['message'] = "No products found for the estimate.";
        }
    } else {
        $response['message'] = "Estimate not found.";
    }

    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
}

if (isset($_POST['save_order'])) {
    header('Content-Type: application/json');
    $response = [];

    $credit_amt = floatval($_POST['credit_amt']);
    $cash_amt = floatval($_POST['cash_amt']);
    $job_name = mysqli_real_escape_string($conn, $_POST['job_name'] ?? '');
    $job_po = mysqli_real_escape_string($conn, $_POST['job_po'] ?? '');
    $deliver_address = mysqli_real_escape_string($conn, $_POST['deliver_address'] ?? '');
    $deliver_city = mysqli_real_escape_string($conn, $_POST['deliver_city'] ?? '');
    $deliver_state = mysqli_real_escape_string($conn, $_POST['deliver_state'] ?? '');
    $deliver_zip = mysqli_real_escape_string($conn, $_POST['deliver_zip'] ?? '');
    $delivery_amt = mysqli_real_escape_string($conn, $_POST['delivery_amt'] ?? '');
    $deliver_fname = mysqli_real_escape_string($conn, $_POST['deliver_fname'] ?? '');
    $deliver_lname = mysqli_real_escape_string($conn, $_POST['deliver_lname'] ?? '');
    if (!isset($_SESSION['customer_id']) || empty($_SESSION['cart'])) {
        $response['error'] = "Customer ID or cart is not set.";
        echo json_encode($response);
        exit;
    }

    $estimateid = intval($_SESSION['estimateid']);
    $customerid = intval($_SESSION['customer_id']);
    $cashierid = intval($_SESSION['userid']);
    $cart = $_SESSION['cart'];
    $order_date = date('Y-m-d H:i:s');
    $discount_default = floatval(getCustomerDiscount($customerid)) / 100;

    $customer_details = getCustomerDetails($customerid);
    $credit_limit = number_format($customer_details['credit_limit'] ?? 0,2);
    $credit_total = number_format(getCustomerCreditTotal($customerid),2);

    if($credit_amt > 0){
        $credit_available = $credit_limit - $credit_total;
        if($credit_available <= 0){
            $response['error'] = "Cannot pay via Credit! The Customer’s credit limit has been reached";
            echo json_encode($response);
            exit;
        }
        
        if($credit_amt > $credit_limit){
            $response['error'] = "Credit amount cannot exceed the customer's credit limit";
            echo json_encode($response);
            exit;
        }
    }

    $total_price = 0;
    $total_discounted_price = 0;

    foreach ($cart as $item) {
        $discount = 0;
        if(isset($item['used_discount'])){
            $discount = $item['used_discount'] / 100;
        }else{
            $discount = $discount_default;
        }
        $product_id = intval($item['product_id']);
        $product_details = getProductDetails($product_id);
        $quantity_cart = intval($item['quantity_cart']);
        $unit_price = floatval($item['unit_price']);
        $estimate_length = floatval($item['estimate_length']);
        $estimate_length_inch = floatval($item['estimate_length_inch']);
        $is_sold_by_feet = intval($product_details['sold_by_feet']);

        $total_length = !empty($is_sold_by_feet) ? ($estimate_length + ($estimate_length_inch / 12)) : 1;
        $actual_price = $unit_price * $total_length * $quantity_cart;
        $discounted_price = $actual_price * (1 - $discount);

        $total_price += $actual_price;
        $total_discounted_price += $discounted_price;
    }

    $query = "INSERT INTO orders (estimateid, cashier, total_price, discounted_price, discount_percent, order_date, customerid, originalcustomerid, cash_amt, credit_amt, job_name, job_po, deliver_address,  deliver_city,  deliver_state,  deliver_zip, delivery_amt, deliver_fname, deliver_lname) 
              VALUES ('$estimateid', '$cashierid', '$total_price', '$total_discounted_price', '".($discount * 100)."', '$order_date', '$customerid', '$customerid', '$cash_amt', '$credit_amt' , '$job_name' , '$job_po' , '$deliver_address', '$deliver_city', '$deliver_state', '$deliver_zip' , '$delivery_amt' , '$deliver_fname' , '$deliver_lname')";

    if ($conn->query($query) === TRUE) {
        $orderid = $conn->insert_id;

        $values = [];
        foreach ($cart as $item) {
            $product_id = intval($item['product_id']);
            $product_details = getProductDetails($product_id);
            $quantity_cart = intval($item['quantity_cart']);
            $unit_price = floatval($item['unit_price']);
            $estimate_width = !empty($item['estimate_width']) ? floatval($item['estimate_width']) : floatval($product_details['width']);
            $estimate_bend = floatval($item['estimate_bend']);
            $estimate_hem = floatval($item['estimate_hem']);
            $estimate_length = floatval($item['estimate_length']);
            $estimate_length_inch = floatval($item['estimate_length_inch']);
            $custom_color = $item['custom_color'];
            $custom_grade = $item['custom_grade'];
            $is_sold_by_feet = intval($product_details['sold_by_feet']);

            $total_length = !empty($is_sold_by_feet) ? ($estimate_length + ($estimate_length_inch / 12)) : 1;

            $actual_price = $unit_price * $total_length;
            $discounted_price = $actual_price * (1 - $discount);
            $product_category = intval($product_details['product_category']);

            $curr_discount = intval(getCustomerDiscountProfile($customerid));
            $loyalty_discount = intval(getCustomerDiscountLoyalty($customerid));
            $used_discount = !empty($item['used_discount']) ? $item['used_discount'] : getCustomerDiscount($customerid);

            $values[] = "('$orderid', '$product_id', '$quantity_cart', '$estimate_width', '$estimate_bend', '$estimate_hem', '$estimate_length', '$estimate_length_inch', '$actual_price', '$discounted_price', '$product_category', '$custom_color' , '$custom_grade', '$curr_discount', '$loyalty_discount', '$used_discount')";
        }

        $query = "INSERT INTO order_product (orderid, productid, quantity, custom_width, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price, product_category, custom_color, custom_grade, current_customer_discount, current_loyalty_discount, used_discount) VALUES ";
        $query .= implode(', ', $values);

        if ($conn->query($query) === TRUE) {
            $query = "INSERT INTO order_estimate (order_estimate_id, type) VALUES ('$orderid','2')";
            if ($conn->query($query) === TRUE) {
                $response['success'] = true;
                $response['order_id'] = $orderid;

                unset($_SESSION['cart']);

                $customer_total_orders = getCustomerOrderTotal($customerid);
                $customer_details = getCustomerDetails($customerid);
                $isLoyalty = $customer_details['loyalty'];

                if (!$isLoyalty) {
                    $query_loyalty = "SELECT * FROM loyalty_program WHERE date_from <= CURDATE() AND date_to >= CURDATE()";
                    $result_loyalty = $conn->query($query_loyalty);

                    if ($result_loyalty && $result_loyalty->num_rows > 0) {
                        while ($row_loyalty = $result_loyalty->fetch_assoc()) {
                            $accumulated_loyalty_required = $row_loyalty['accumulated_total_orders'];

                            if ($customer_total_orders >= $accumulated_loyalty_required) {
                                $query_update_loyalty = "UPDATE customer SET loyalty = 1 WHERE customer_id = $customerid";
                                if ($conn->query($query_update_loyalty) === TRUE) {
                                    $response['message'] = 'Added Customer to Loyalty Program';
                                }
                            }
                        }
                    }
                }
            }else{
                $response['message'] = "Error inserting order estimate records: " . $conn->error;
            }
        } else {
            $response['error'] = "Error inserting order products: " . $conn->error;
        }
    } else {
        $response['error'] = "Error inserting order: " . $conn->error;
    }

    $conn->close();
    echo json_encode($response);
}

if (isset($_POST['clear_cart'])) {
    unset($_SESSION['cart']);
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
            (customer_first_name LIKE '%$search%' 
            OR 
            customer_last_name LIKE '%$search%')
            AND status != '3'
            AND status != '0'
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

if (isset($input['save_drawing'])) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = mysqli_real_escape_string($conn, $input['id']);
    $line = mysqli_real_escape_string($conn, $input['line']);

    if (isset($input['image_data'])) {
        $key = findCartKey($_SESSION["cart"], $id, $line);
        if ($key !== false && isset($_SESSION["cart"][$key])) {
            $image_data = $input['image_data'];
            $image_data = str_replace('data:image/png;base64,', '', $image_data);
            $image_data = str_replace(' ', '+', $image_data);
            $decodedData = base64_decode($image_data);
            $images_directory = "../../images/drawing/";
            $filename = uniqid() . '.png';
            
            if (file_put_contents($images_directory . $filename, $decodedData)) {
                $_SESSION["cart"][$key]['custom_trim_src'] = $filename;
                echo json_encode(['filename' => $filename]);
            } else {
                echo json_encode(['error' => 'Failed to save image']);
            }
        } else {
            echo json_encode(['error' => "Product not found in cart: ID: $id, Line: $line"]);
        }  
    } else {
        echo json_encode(['error' => 'No image data provided']);
    }
    
}

if (isset($_POST['return_product'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);

    $query = "SELECT * FROM order_product WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $order = mysqli_fetch_assoc($result);
        $available_quantity = $order['quantity'];

        if ($quantity > $available_quantity) {
            echo "Quantity entered exceeds the purchased count!";
        } else {
            $insert_query = "INSERT INTO product_returns 
                             (orderid, productid, status, quantity, custom_color, custom_width, custom_height, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price, product_category, usageid)
                             VALUES 
                             ('{$order['orderid']}', '{$order['productid']}', 4, '$quantity', '{$order['custom_color']}', '{$order['custom_width']}', '{$order['custom_height']}', 
                              '{$order['custom_bend']}', '{$order['custom_hem']}', '{$order['custom_length']}', '{$order['custom_length2']}', '{$order['actual_price']}', 
                              '{$order['discounted_price']}', '{$order['product_category']}', '{$order['usageid']}')";
            
            if (mysqli_query($conn, $insert_query)) {
                $new_quantity = $available_quantity - $quantity;
                $update_query = "UPDATE order_product SET quantity = '$new_quantity' WHERE id = '$id'";
                if (mysqli_query($conn, $update_query)) {

                    setOrderTotals($order['orderid']);
                    echo "success";
                } else {
                    echo "Error updating order quantity.";
                }
            } else {
                echo "Error inserting into product_returns.";
            }
        }
    } else {
        echo "Error: Order not found.";
    }
}

if(isset($_POST['fetch_change_color_modal'])){
    if (!empty($_SESSION["cart"])) {
        $cart_colors = array();
        $in_stock_colors = array();
        $category_ids = array();
        foreach ($_SESSION["cart"] as $keys => $values) {
            $cart_colors[] = $values["custom_color"];

            $product_details = getProductDetails($values["product_id"]);
            $category_ids[] = $product_details["product_category"];

            $query_colors = "SELECT color_id FROM inventory WHERE Product_id = '".$values["product_id"]."'";
            $result_colors = mysqli_query($conn, $query_colors);            
            while ($row_colors = mysqli_fetch_array($result_colors)) {
                $in_stock_colors[] = $row_colors['color_id'];
            }

            if ($product_details["product_category"] == $trim_id || $product_details["product_category"] == $panel_id) {
                $sql = "SELECT COUNT(*) AS count FROM coil WHERE color = '".$values["custom_color"]."'";
                $result = mysqli_query($conn, $sql);

                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    if ($row['count'] > 0) {
                        $in_stock_colors[] = $row['color'];
                    }
                }
            }
        }
        $cart_colors = array_unique($cart_colors);
        $in_stock_colors = array_unique($in_stock_colors);
        $category_ids = array_unique($category_ids);
    }
    ?>
        <div id="change_color_container" class="d-flex align-items-center justify-content-between w-100">
            <div class="col-md-5">
                <label class="form-label" for="orig-colors" style="display:block; width: 100%;">Change Color From:</label>
                <select id="orig-colors" class="form-select">
                    <option value="">Select Original Color</option>
                    <?php
                    foreach ($cart_colors as $color_id) {
                        echo '<option value="' . $color_id . '" data-color="' . getColorHexFromColorID($color_id) . '">' . getColorName($color_id) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <span class="px-2"> TO </span>
            <div class="col-md-5">
                <label class="form-label" for="in-stock-colors" style="display:block; width: 100%;">New Color:</label>
                <select id="in-stock-colors" class="form-select">
                    <option value="">Select Available Color</option>
                    <?php
                    foreach ($in_stock_colors as $color_id) {
                        echo '<option value="' . $color_id . '" data-color="' . getColorHexFromColorID($color_id) . '">' . getColorName($color_id) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div id="change_category_container" class="mt-4">
            <label class="form-label" for="category_id_color" style="display:block; width: 100%;">Only for this category (Optional)</label>
            <select id="category_id_color" class="form-select">
                <option value="">Select Available Category</option>
                <?php
                foreach ($category_ids as $category_id) {
                    echo '<option value="' . $category_id . '">' . getProductCategoryName($category_id) . '</option>';
                }
                ?>
            </select>
        </div>
        <script>
            $(document).ready(function() {
                $("#category_id_color").select2({
                    width: '300px',
                    placeholder: "Select Category",
                    dropdownAutoWidth: true,
                    dropdownParent: $('#change_category_container'),
                    templateResult: formatOption,
                    templateSelection: formatOption
                });

                $("#orig-colors").select2({
                    width: '300px',
                    placeholder: "Select Color...",
                    dropdownAutoWidth: true,
                    dropdownParent: $('#change_color_container'),
                    templateResult: formatOption,
                    templateSelection: formatOption
                });

                $("#in-stock-colors").select2({
                    width: '300px',
                    placeholder: "Select Color...",
                    dropdownAutoWidth: true,
                    dropdownParent: $('#change_color_container'),
                    templateResult: formatOption,
                    templateSelection: formatOption
                });
            });
        </script>

    <?php
}

if(isset($_POST['fetch_change_price_modal'])){
    ?>
        <div id="change_price_container" class="d-flex align-items-center justify-content-between w-100">
            <div class="col-md-12">
                <label class="form-label" for="price_group_select" style="display:block; width: 100%;">Discount Category</label>
                <select id="price_group_select" class="form-select custom-select text-start">
                    <option value="">Select Discount</option>
                    <?php
                        $query = "SELECT * FROM customer_types WHERE status = 1";
                        $result = mysqli_query($conn, $query);
                        if ($result) {
                            while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                                <option value="<?= $row['customer_price_cat'] ?>"><?= $row['customer_type_name'] ?></option>
                            <?php
                            }
                        }
                    ?>
                </select>
            </div>
        </div>
        <div id="change_price_container" class="d-flex align-items-center justify-content-between w-100 mt-4">
            <div class="col-md-12">
                <label class="form-label" for="product_select" style="display:block; width: 100%;">Products Affected</label>
                <select id="product_select" class="form-select custom-select text-start" multiple="multiple">
                    <?php
                        foreach ($_SESSION["cart"] as $keys => $values) {
                            ?>
                                <option value="<?= $values['product_id'] ?>"><?= $values['product_item'] ?></option>
                            <?php
                        }
                    ?>
                </select>
            </div>
        </div>
        
        <script>
            $(document).ready(function() {
                $("#price_group_select").select2({
                    width: '100%',
                    placeholder: "Select Discount",
                    dropdownAutoWidth: true,
                    dropdownParent: $('#change_price_container')
                });

                $('#product_select').multiselect({
                    includeSelectAllOption: true,
                    nonSelectedText: 'Select Products',
                    buttonWidth: '100%',
                    templates: {
                        button: '<button type="button" class="multiselect dropdown-toggle text-start" data-bs-toggle="dropdown" aria-expanded="false"><span class="multiselect-selected-text"></span></button>',
                        ul: '<ul class="multiselect-container dropdown-menu dropdown-menu-start px-0"></ul>',
                    },
                });
            });
        </script>

    <?php
}

if(isset($_POST['fetch_change_grade_modal'])){
    if (!empty($_SESSION["cart"])) {
        $cart_grade = array();
        $in_stock_grade = array();
        $category_ids = array();
        foreach ($_SESSION["cart"] as $keys => $values) {
            $cart_grade[] = $values["custom_grade"];

            $product_details = getProductDetails($values["product_id"]);
            $category_ids[] = intval($product_details["product_category"]);

            $in_stock_grade[] = $product_details['grade'];

            if ($product_details["product_category"] == $trim_id || $product_details["product_category"] == $panel_id) {
                $sql = "SELECT grade FROM coil";
                $result = mysqli_query($conn, $sql);
            
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $in_stock_grade[] = $row['grade'];
                    }
                }
            }
        }
        $cart_grade = array_filter(array_unique($cart_grade), function($value) {
            return !empty($value);
        });
        
        $in_stock_grade = array_filter(array_unique($in_stock_grade), function($value) {
            return !empty($value);
        });
        
        $category_ids = array_filter(array_unique($category_ids), function($value) {
            return !empty($value);
        });
    }
    ?>
        <div id="change_grade_container" class="d-flex align-items-center justify-content-between w-100">
            <div class="col-md-5">
                <label class="form-label" for="orig-grade" style="display:block; width: 100%;">Change Grade From:</label>
                <select id="orig-grade" class="form-select">
                    <option value="">Select Original Grade</option>
                    <?php
                    foreach ($cart_grade as $grade_id) {
                        echo '<option value="' . $grade_id . '">' . getGradeName($grade_id) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <span class="px-2"> TO </span>
            <div class="col-md-5">
                <label class="form-label" for="in-stock-grade" style="display:block; width: 100%;">New Grade:</label>
                <select id="in-stock-grade" class="form-select">
                    <option value="">Select Available Grade</option>
                    <?php
                    foreach ($in_stock_grade as $stock_grade_id) {
                        echo '<option value="' . $stock_grade_id . '">' . getGradeName($stock_grade_id) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <div id="change_category_container" class="mt-4">
            <label class="form-label" for="category_id" style="display:block; width: 100%;">Only for this category (Optional)</label>
            <select id="category_id" class="form-select">
                <option value="">Select Available Category</option>
                <?php
                foreach ($category_ids as $category_id) {
                    echo '<option value="' . $category_id . '">' . getProductCategoryName($category_id) . '</option>';
                }
                ?>
            </select>
        </div>
    
        
        <script>
            $(document).ready(function() {
                $("#category_id").select2({
                    width: '300px',
                    placeholder: "Select Category",
                    dropdownAutoWidth: true,
                    dropdownParent: $('#change_category_container'),
                    templateResult: formatOption,
                    templateSelection: formatOption
                });

                $("#orig-grade").select2({
                    width: '300px',
                    placeholder: "Select Grade...",
                    dropdownAutoWidth: true,
                    dropdownParent: $('#change_grade_container'),
                    templateResult: formatOption,
                    templateSelection: formatOption
                });

                $("#in-stock-grade").select2({
                    width: '300px',
                    placeholder: "Select Grade...",
                    dropdownAutoWidth: true,
                    dropdownParent: $('#change_grade_container'),
                    templateResult: formatOption,
                    templateSelection: formatOption
                });
            });
        </script>

    <?php
}

if (isset($_POST['change_color'])) {
    $orig_color = $_POST['orig_color'];
    $in_stock_color = $_POST['in_stock_color'];
    $selected_category_id = $_POST['category_id'];

    foreach ($_SESSION['cart'] as $key => &$item) {
        if ($item['custom_color'] == $orig_color) {
            if (!empty($selected_category_id)) {
                $product_details = getProductDetails($item['product_id']);
                if ($product_details['product_category'] == $selected_category_id) {
                    $item['custom_color'] = $in_stock_color;
                }
            } else {
                $item['custom_color'] = $in_stock_color;
            }
        }
    }
    unset($item);

    echo "success";
}

if (isset($_POST['change_grade'])) {
    $orig_grade = $_POST['orig_grade'];
    $in_stock_grade = $_POST['in_stock_grade'];
    $selected_category_id = $_POST['category_id'];

    foreach ($_SESSION['cart'] as $key => &$item) {
        if ($item['custom_grade'] == $orig_grade) {
            if (!empty($selected_category_id)) {
                $product_details = getProductDetails($item['product_id']);
                if ($product_details['product_category'] == $selected_category_id) {
                    $item['custom_grade'] = $in_stock_grade;
                }
            } else {
                $item['custom_grade'] = $in_stock_grade;
            }
        }
    }
    unset($item);

    echo "success";
}

if (isset($_POST['change_price'])) {
    $price_group_select = $_POST['price_group_select'];
    $product_select = $_POST['product_select'];

    foreach ($_SESSION['cart'] as $key => &$item) {
        if (in_array($item['product_id'], $product_select)) {
            $item['used_discount'] = $price_group_select;
        }
    }
    unset($item);

    echo "success";
}

if (isset($_POST['add_to_cart'])) {
    $qty = isset($_POST['quantity_product']) ? intval($_POST['quantity_product']) : 0;
    $lengthFeet = isset($_POST['length_feet']) ? intval($_POST['length_feet']) : 0;
    $lengthInch = isset($_POST['length_inch']) ? intval($_POST['length_inch']) : 0;
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $panel_type = mysqli_real_escape_string($conn, $_POST['panel_type']);
    $stiff_board_batten = isset($_POST['stiff_board_batten']) ? mysqli_real_escape_string($conn, $_POST['stiff_board_batten']) : '';
    $stiff_stand_seam = isset($_POST['stiff_stand_seam']) ? mysqli_real_escape_string($conn, $_POST['stiff_stand_seam']) : '';

    $line = 1;

    $quantityInStock = getProductStockInStock($product_id);
    $totalQuantity = getProductStockTotal($product_id);
    $totalStock = $totalQuantity;

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = array();
    }

    $key = findCartKey($_SESSION["cart"], $product_id, $line);

    if ($key !== false) {
        $requestedQuantity = max($qty, 1);
        $_SESSION["cart"][$key]['quantity_cart'] += min($requestedQuantity, $totalStock);
    } else {
        $query = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $item_quantity = $qty;

            $weight = floatval($row['weight']);

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'unit_price' => $row['unit_price'],
                'line' => 1,
                'quantity_ttl' => $totalStock,
                'quantity_in_stock' => $quantityInStock,
                'quantity_cart' => $item_quantity,
                'estimate_width' => $row['width'],
                'estimate_length' => $lengthFeet,
                'estimate_length_inch' => $lengthInch,
                'usage' => 0,
                'custom_color' => $row['color'],
                'panel_type' => $panel_type,
                'weight' => $weight,
                'custom_grade' => intval($row['grade']),
                'stiff_board_batten' => $stiff_board_batten,
                'stiff_stand_seam' => $stiff_stand_seam
            );

            $_SESSION["cart"][] = $item_array;

            $backer_rod_3_8 = 45;
            $backer_rod_1_2 = 46;
            $stiffening_rib_id = 7;

            $backer_rod_details = array();
            if($row['product_category'] == $stiffening_rib_id && $stiff_board_batten == 'flat'){
                $quantityInStock = getProductStockInStock($stiff_board_batten);
                $totalQuantity = getProductStockTotal($stiff_board_batten);
                $totalStock = $totalQuantity;
                $backer_rod_details = getProductDetails($backer_rod_3_8);

                $item_array = array(
                    'product_id' => $backer_rod_details['product_id'],
                    'product_item' => $backer_rod_details['product_item'],
                    'unit_price' => $backer_rod_details['unit_price'],
                    'line' => 1,
                    'quantity_ttl' => $totalStock,
                    'quantity_in_stock' => $quantityInStock,
                    'quantity_cart' => 1,
                    'estimate_width' => $backer_rod_details['width'],
                    'estimate_length' => 0,
                    'estimate_length_inch' => 0,
                    'usage' => 0,
                    'custom_color' => $backer_rod_details['color'],
                    'panel_type' => '',
                    'weight' => floatval($backer_rod_details['weight']),
                    'custom_grade' => floatval($backer_rod_details['custom_grade'])
                );
    
                $_SESSION["cart"][] = $item_array;
            }else if($row['product_category'] == $stiffening_rib_id && $stiff_stand_seam == 'flat'){
                $quantityInStock = getProductStockInStock($stiffening_rib_id);
                $totalQuantity = getProductStockTotal($stiffening_rib_id);
                $totalStock = $totalQuantity;
                $backer_rod_details = getProductDetails($backer_rod_1_2);

                $item_array = array(
                    'product_id' => $backer_rod_details['product_id'],
                    'product_item' => $backer_rod_details['product_item'],
                    'unit_price' => $backer_rod_details['unit_price'],
                    'line' => 1,
                    'quantity_ttl' => $totalStock,
                    'quantity_in_stock' => $quantityInStock,
                    'quantity_cart' => 1,
                    'estimate_width' => $backer_rod_details['width'],
                    'estimate_length' => 0,
                    'estimate_length_inch' => 0,
                    'usage' => 0,
                    'custom_color' => $backer_rod_details['color'],
                    'panel_type' => '',
                    'weight' => floatval($backer_rod_details['weight']),
                    'custom_grade' => floatval($backer_rod_details['custom_grade'])
                );
    
                $_SESSION["cart"][] = $item_array;
            }
        }
    }

    echo 'success';
}


?>
