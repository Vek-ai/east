<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';
require '../../includes/send_email.php';

$admin_email = getSetting('admin_email');
$emailSender = new EmailTemplates();

$trim_id = 4;
$panel_id = 3;
$custom_truss_id = 47;
$special_trim_id = 66;
$screw_id = 16;

function cartItemExists($product_id) {
    global $conn;
    $product_id = mysqli_real_escape_string($conn, $product_id);
    $customer_id = (int)$_SESSION['customer_id'];

    $query = "SELECT 1 FROM customer_cart WHERE customer_id = $customer_id AND product_id = '$product_id' LIMIT 1";
    $result = mysqli_query($conn, $query);

    return mysqli_num_rows($result) > 0;
}

function insertCartRow($cartRow) {
    global $conn;

    $columns = implode(", ", array_keys($cartRow));
    $values  = "'" . implode("','", array_map(function($val) use ($conn) {
        return mysqli_real_escape_string($conn, $val);
    }, array_values($cartRow))) . "'";

    $sql = "INSERT INTO customer_cart ($columns) VALUES ($values)";
    mysqli_query($conn, $sql);

    return mysqli_insert_id($conn);
}

function updateCartRow($cartRow, $where) {
    global $conn;

    $setParts = [];
    foreach ($cartRow as $col => $val) {
        $safeVal = mysqli_real_escape_string($conn, $val);
        $setParts[] = "$col = '$safeVal'";
    }
    $setClause = implode(", ", $setParts);

    $whereParts = [];
    foreach ($where as $col => $val) {
        $safeVal = mysqli_real_escape_string($conn, $val);
        $whereParts[] = "$col = '$safeVal'";
    }
    $whereClause = implode(" AND ", $whereParts);

    $sql = "UPDATE customer_cart SET $setClause WHERE $whereClause";
    return mysqli_query($conn, $sql);
}

function duplicateCartRow($line) {
    global $conn;

    $line = (int)$line;

    $sql = "INSERT INTO customer_cart (
                customer_id, product_id, product_item, supplier_id, unit_price,
                quantity_ttl, quantity_in_stock, quantity_cart,
                estimate_width, estimate_length, estimate_length_inch,
                prod_usage, custom_color, weight, custom_grade, created_at
            )
            SELECT 
                customer_id, product_id, product_item, supplier_id, unit_price,
                quantity_ttl, quantity_in_stock, quantity_cart,
                estimate_width, estimate_length, estimate_length_inch,
                prod_usage, custom_color, weight, custom_grade, NOW()
            FROM customer_cart
            WHERE id = $line
            LIMIT 1";

    mysqli_query($conn, $sql);

    $insert_id = mysqli_insert_id($conn);

    mysqli_query($conn, "UPDATE customer_cart SET line = $insert_id WHERE id = $insert_id");

    return $insert_id;
}

if (isset($_POST['modifyquantity']) || isset($_POST['duplicate_product'])) {
    $product_id  = mysqli_real_escape_string($conn, $_POST['product_id']);
    $line        = isset($_POST['line']) ? (int)$_POST['line'] : 0;
    $qty         = max((int)($_POST['qty'] ?? 1), 1);
    $customer_id = (int)$_SESSION['customer_id'];

    if (isset($_POST['duplicate_product'])) {
        $newId = duplicateCartRow($line); 
        echo $newId; 
        return;
    }

    if ($line > 0) {
        if (isset($_POST['setquantity'])) {
            $qty = max($qty, 1);
            updateCartRow(['quantity_cart' => $qty], ['id' => $line]);
            echo $qty;

        } elseif (isset($_POST['addquantity'])) {
            mysqli_query($conn, "UPDATE customer_cart 
                                 SET quantity_cart = quantity_cart + $qty 
                                 WHERE id = $line");
            $res = mysqli_query($conn, "SELECT quantity_cart FROM customer_cart WHERE id = $line");
            echo mysqli_fetch_assoc($res)['quantity_cart'];

        } elseif (isset($_POST['deductquantity'])) {
            $res = mysqli_query($conn, "SELECT quantity_cart FROM customer_cart WHERE id = $line");
            $row = mysqli_fetch_assoc($res);
            if ((int)$row['quantity_cart'] <= 1) {
                mysqli_query($conn, "DELETE FROM customer_cart WHERE id = $line");
                echo 'removed';
            } else {
                updateCartRow(['quantity_cart' => ((int)$row['quantity_cart'] - 1)], ['id' => $line]);
                echo ((int)$row['quantity_cart'] - 1);
            }
        }
        return;
    }

    $quantityInStock = getProductStockInStock($product_id);
    $totalStock      = getProductStockTotal($product_id);

    $res = mysqli_query($conn, "SELECT * FROM product WHERE product_id = '$product_id' LIMIT 1");
    if ($row = mysqli_fetch_assoc($res)) {
        $length = floatval(preg_replace('/[^0-9.]/', '', $row['length']));
        $estimate_length      = floor($length);
        $estimate_length_inch = $length - $estimate_length;

        $weight    = floatval($row['weight']);
        $basePrice = floatval($row['unit_price']);
        if ($row['sold_by_feet'] == '1') {
            $basePrice = $basePrice / ($length ?: 1);
        }

        $unitPrice     = calculateUnitPrice($basePrice, $estimate_length, $estimate_length_inch, '', $row['sold_by_feet'], 0, 0);
        $item_quantity = min($qty, $totalStock);

        $cartRow = [
            'customer_id'            => $customer_id,
            'product_id'             => $product_id,
            'product_item'           => getProductName($row['product_id']),
            'supplier_id'            => (int)$row['supplier_id'],
            'unit_price'             => $unitPrice,
            'quantity_ttl'           => $totalStock,
            'quantity_in_stock'      => $quantityInStock,
            'quantity_cart'          => $item_quantity,
            'estimate_width'         => (float)$row['width'],
            'estimate_length'        => $estimate_length,
            'estimate_length_inch'   => $estimate_length_inch,
            'prod_usage'             => 0,
            'custom_color'           => $row['color'],
            'weight'                 => $weight,
            'custom_grade'           => (int)$row['grade'],
            'created_at'             => date('Y-m-d H:i:s'),
        ];

        $insert_id = insertCartRow($cartRow);
        updateCartRow(['line' => $insert_id], ['id' => $insert_id]);

        return $insert_id;
    }
}

if (isset($_POST['deleteitem'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id_del']);
    $line = (int)$_POST['line'];
    $customer_id = (int)$_SESSION['customer_id'];

    $query = "DELETE FROM customer_cart WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line";
    mysqli_query($conn, $query);
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
    $onlyPromotions = isset($_REQUEST['onlyPromotions']) ? filter_var($_REQUEST['onlyPromotions'], FILTER_VALIDATE_BOOLEAN) : false;
    $onlyOnSale = isset($_REQUEST['onlyOnSale']) ? filter_var($_REQUEST['onlyOnSale'], FILTER_VALIDATE_BOOLEAN) : false;

    $query_product = "
        SELECT 
            p.*,
            COALESCE(SUM(i.quantity_ttl), 0) AS total_quantity
        FROM 
            product AS p
        LEFT JOIN 
            inventory AS i ON p.product_id = i.product_id
        WHERE 
            p.hidden = '0' and p.status = '1'
    ";

    if (!empty($searchQuery)) {
        $query_product .= " AND (p.product_item LIKE '%$searchQuery%' OR p.description LIKE '%$searchQuery%')";
    }

    if (!empty($color_id)) {
        $query_product .= " AND i.color_id = '$color_id'";
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

    if ($onlyPromotions) {
        $query_product .= " AND p.on_promotion = '1'";
    }

    if ($onlyOnSale) {
        $query_product .= " 
            AND EXISTS (
                SELECT 1
                FROM sales_discounts sd
                WHERE sd.product_id = p.product_id
                AND (
                    sd.date_started = '0000-00-00 00:00:00' OR sd.date_started <= NOW()
                )
                AND (
                    sd.date_finished = '0000-00-00 00:00:00' OR sd.date_finished >= NOW()
                )
            )
        ";
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
                    $sql = "SELECT COUNT(*) AS count FROM coil_product WHERE color_sold_as = '$product_color'";
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

            $is_panel = $row_product['product_category'] == $panel_id ? true : false;
            $is_trim = $row_product['product_category'] == $trim_id ? true : false;
            $is_screw = $row_product['product_category'] == $screw_id ? true : false;
            $is_custom_truss = $row_product['product_id'] == $custom_truss_id ? true : false;
            $is_special_trim = $row_product['product_id'] == $special_trim_id ? true : false;
            $is_custom_length = $row_product['is_custom_length'] == 1 ? true : false;

            $qty_input = !$is_panel  && !$is_custom_truss && !$is_special_trim && !$is_trim && !$is_custom_length && !$is_screw
                ? ' <div class="input-group input-group-sm">
                        <button class="btn btn-outline-primary btn-minus" type="button" data-id="' . $row_product['product_id'] . '">-</button>
                        <input class="form-control p-1 text-center" type="number" id="qty' . $row_product['product_id'] . '" value="1" min="1">
                        <button class="btn btn-outline-primary btn-plus" type="button" data-id="' . $row_product['product_id'] . '">+</button>
                    </div>'
                : '';

            if($is_custom_truss){
                $btn_id = 'add-to-cart-custom-truss-btn';
            }else if($is_special_trim){
                $btn_id = 'add-to-cart-special-trim-btn';
            }else if($is_panel){
                $btn_id = 'add-to-cart-panel-btn';
            }else if($is_trim){
                $btn_id = 'add-to-cart-trim-btn';
            }else if($is_screw){
                $btn_id = 'add-to-cart-custom-length-btn';
            }else if($is_custom_length){
                $btn_id = 'add-to-cart-custom-length-btn';
            }else{
                $btn_id = 'add-to-cart-btn';
            }

            $tableHTML .= '
            <tr>
                <td>
                    <a href="javascript:void(0);" id="view_product_details" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center">
                        <div class="d-flex align-items-center" >
                            <img src="'.$picture_path.'" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                            <div class="ms-3">
                                <h6 class="fw-semibold mb-0 fs-4">'. getProductName($row_product['product_id']) .' ' .$dimensions .'</h6>
                            </div>
                        </div>
                    </a>
                </td>
                <td>
                    <div class="d-flex mb-0 gap-8">
                        <a href="javascript:void(0)" id="view_available_color" data-id="'.$row_product['product_id'].'">See Colors</a>
                    </div>
                </td>
                <td><a href="javascript:void(0);" style="text-decoration: none; color: inherit;" class="mb-0">'. getGradeName($row_product['grade']) .'</a></td>
                <td><a href="javascript:void(0);" style="text-decoration: none; color: inherit;" class="mb-0">'. getGaugeName($row_product['gauge']) .'</a></td>
                <td><a href="javascript:void(0);" style="text-decoration: none; color: inherit;" class="mb-0">'. getProductTypeName($row_product['product_type']) .'</a></td>
                <td><a href="javascript:void(0);" style="text-decoration: none; color: inherit;" class="mb-0">'. getProfileTypeName($row_product['profile']) .'</a></td>
                <td>
                    <div class="d-flex align-items-center">'.$stock_text.'</div>
                </td>
                <td>
                    '.$qty_input.'
                </td>
                <td>
                    <button class="btn btn-sm btn-primary btn-add-to-cart" type="button" data-id="'.$row_product['product_id'].'" id="'.$btn_id.'">Add to Cart</button>
                </td>
            </tr>';
        }
    } else {
        $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    
    echo $tableHTML;
}

if (isset($_POST['set_usage'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = (int)$_POST['line'];
    $usage = mysqli_real_escape_string($conn, $_POST['usage']);
    $customer_id = (int)$_SESSION['customer_id'];

    mysqli_query($conn, "UPDATE customer_cart SET prod_usage = '$usage', updated_at = NOW() WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
}

if (isset($_POST['set_estimate_hem'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = (int)$_POST['line'];
    $hem = mysqli_real_escape_string($conn, $_POST['hem']);
    $customer_id = (int)$_SESSION['customer_id'];

    mysqli_query($conn, "UPDATE customer_cart SET estimate_hem = '$hem', updated_at = NOW() WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
}

if (isset($_POST['set_estimate_bend'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = (int)$_POST['line'];
    $bend = mysqli_real_escape_string($conn, $_POST['bend']);
    $customer_id = (int)$_SESSION['customer_id'];

    mysqli_query($conn, "UPDATE customer_cart SET estimate_bend = '$bend', updated_at = NOW() WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
}

if (isset($_POST['set_estimate_height'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = (int)$_POST['line'];
    $height = mysqli_real_escape_string($conn, $_POST['height']);
    $customer_id = (int)$_SESSION['customer_id'];

    mysqli_query($conn, "UPDATE customer_cart SET estimate_height = '$height', updated_at = NOW() WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
}

if (isset($_POST['set_estimate_width'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = (int)$_POST['line'];
    $width = mysqli_real_escape_string($conn, $_POST['width']);
    $customer_id = (int)$_SESSION['customer_id'];

    mysqli_query($conn, "UPDATE customer_cart SET estimate_width = '$width', updated_at = NOW() WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
}

if (isset($_POST['set_estimate_length'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = (int)$_POST['line'];
    $length = mysqli_real_escape_string($conn, $_POST['length']);
    $customer_id = (int)$_SESSION['customer_id'];

    mysqli_query($conn, "UPDATE customer_cart SET estimate_length = '$length', updated_at = NOW() WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
}

if (isset($_POST['set_estimate_length_inch'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = (int)$_POST['line'];
    $length_inch = mysqli_real_escape_string($conn, $_POST['length_inch']);
    $customer_id = (int)$_SESSION['customer_id'];

    mysqli_query($conn, "UPDATE customer_cart SET estimate_length_inch = '$length_inch', updated_at = NOW() WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
}

if (isset($_POST['set_color'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = (int)$_POST['line'];
    $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
    $customer_id = (int)$_SESSION['customer_id'];

    mysqli_query($conn, "UPDATE customer_cart SET custom_color = '$color_id', updated_at = NOW() WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
}

if (isset($_POST['set_grade'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = (int)$_POST['line'];
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $customer_id = (int)$_SESSION['customer_id'];

    mysqli_query($conn, "UPDATE customer_cart SET custom_grade = '$grade', updated_at = NOW() WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
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

    $customerid = intval($_SESSION['customer_id']);

    if (!isset($customerid)) {
        $response['message'] = "Customer ID or cart is not set.";
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    $cart = getCartDataByCustomerId($customerid);

    if (empty($cart)) {
        $response['message'] = "Cart is empty.";
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    

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
    $pre_orders = array();
    foreach ($cart as $item) {
        $discount = 0;
        if(isset($item['used_discount'])){
            $discount = $item['used_discount'] / 100;
        }else{
            $discount = $discount_default;
        }
        $product_id = intval($item['product_id']);
        $product_details = getProductDetails($product_id);
        $customer_pricing = getPricingCategory($product_details['product_category'], $customer_details['customer_pricing']) / 100;
        $unit_price = floatval($item['unit_price']);
        $quantity_cart = intval($item['quantity_cart']);
        $product_details = getProductDetails($item['product_id']);
        $estimate_length = floatval($item['estimate_length']);
        $estimate_length_inch = floatval($item['estimate_length_inch']);
        $amount_discount = !empty($item["amount_discount"]) ? $item["amount_discount"] : 0;

        $actual_price = $unit_price * $quantity_cart;
        $discounted_price = ($actual_price * (1 - $discount) * (1 - $customer_pricing)) - $amount_discount;

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

            $product_item = $item['product_item'] ?? '';

            $quantity_cart = intval($item['quantity_cart']);

            $customer_pricing = getPricingCategory($product_details['product_category'], $customer_details['customer_pricing']) / 100;

            $unit_price = floatval($item['unit_price']);
            $estimate_width = !empty($item['estimate_width']) ? floatval($item['estimate_width']) : $product_details['width'];
            $estimate_bend = floatval($item['estimate_bend']);
            $estimate_hem = floatval($item['estimate_hem']);
            $estimate_length = floatval($item['estimate_length']);
            $estimate_length_inch = floatval($item['estimate_length_inch']);
            $custom_color = $item['custom_color'];
            $custom_grade = $item['custom_grade'];
            $custom_gauge = $item['custom_gauge'];
            $is_pre_order = $item['is_pre_order'];
            $amount_discount = !empty($item["amount_discount"]) ? $item["amount_discount"] : 0;

            $actual_price = $unit_price;
            $discounted_price = ($actual_price * (1 - $discount) * (1 - $customer_pricing)) - $amount_discount;

            $curr_discount = intval(getCustomerDiscountProfile($customerid));
            $loyalty_discount = intval(getCustomerDiscountLoyalty($customerid));
            $used_discount = !empty($item['used_discount']) ? $item['used_discount'] : getCustomerDiscount($customerid);

            $stiff_stand_seam = !empty($item['stiff_stand_seam']) ? $item['stiff_stand_seam'] : '0';
            $stiff_board_batten = !empty($item['stiff_board_batten']) ? $item['stiff_board_batten'] : '0';
            $panel_type = !empty($item['panel_type']) ? $item['panel_type'] : '0';
            $custom_img_src = $item['custom_trim_src'];

            $values[] = "('$estimateid', '$product_id', '$product_item', '$quantity_cart', '$estimate_width', '$estimate_bend', '$estimate_hem', '$estimate_length', '$estimate_length_inch', '$actual_price', '$discounted_price', '$custom_color', '$custom_grade', '$curr_discount', '$loyalty_discount', '$used_discount', '$stiff_stand_seam', '$stiff_board_batten', '$panel_type')";

            if ($product_details['product_origin'] == 2) {
                $query = "INSERT INTO work_order_product (
                            work_order_id, 
                            type,
                            productid, 
                            product_item,
                            quantity, 
                            custom_width, 
                            custom_bend, 
                            custom_hem, 
                            custom_length, 
                            custom_length2, 
                            actual_price, 
                            discounted_price, 
                            product_category, 
                            custom_color, 
                            custom_grade, 
                            current_customer_discount, 
                            current_loyalty_discount, 
                            used_discount, 
                            stiff_stand_seam, 
                            stiff_board_batten, 
                            panel_type,
                            custom_img_src
                        ) 
                        VALUES (
                            '$estimateid', 
                            '1',
                            '$product_id', 
                            '$product_item', 
                            '$quantity_cart', 
                            '$estimate_width', 
                            '$estimate_bend', 
                            '$estimate_hem', 
                            '$estimate_length', 
                            '$estimate_length_inch', 
                            '$actual_price', 
                            '$discounted_price', 
                            '$product_category', 
                            '$custom_color', 
                            '$custom_grade', 
                            '$curr_discount', 
                            '$loyalty_discount', 
                            '$used_discount', 
                            '$stiff_stand_seam', 
                            '$stiff_board_batten', 
                            '$panel_type',
                            '$custom_img_src'
                        )";
            
                if ($conn->query($query) === TRUE) {
                } else {
                    die("Error: " . $conn->error);
                }
            }

            $product_category = $product_details['product_category'];

            $is_pre_order = $item['is_pre_order'];

            if($is_pre_order == '1'){
                $pre_orders = [
                    'product_item' => $product_item,
                    'product_category' => ucwords(getProductCategoryName($product_category)),
                    'color' => getColorName($custom_color),
                    'grade' => getGradeName($custom_grade),
                    'gauge' => getGaugeName($custom_gauge)
                ];

                $insert_query = "
                    INSERT INTO product_preorder (
                        product_id,
                        product_category,
                        color,
                        grade,
                        gauge
                    ) VALUES (
                        '$product_id',
                        '$product_category',
                        '$color',
                        '$grade',
                        '$gauge'
                    )
                ";

                if (mysqli_query($conn, $insert_query)) {
                } else {
                    $response['error'] = "Insert failed: " . mysqli_error($conn);
                }
            }

            $current_stock = getProductStockTotal($product_id);
            if($current_stock < 1){
                $out_stock_orders = [
                    'product_item' => $product_item,
                    'product_category' => ucwords(getProductCategoryName($product_details['product_category'])),
                    'color' => getColorName($custom_color),
                    'grade' => getGradeName($custom_grade),
                    'gauge' => getGaugeName($custom_gauge)
                ];

                $list_items = '<ul style="list-style-type: none; padding-left: 0;">';
                foreach ($out_stock_orders as $key => $value) {
                    $label = ucfirst(str_replace('_', ' ', $key));
                    $list_items .= "
                        <li style='margin-bottom: 8px;'>
                            <span style='display: inline-block; min-width: 140px; font-weight: bold; color: #333;'>$label:</span>
                            <span style='color: #555;'>" . htmlspecialchars($value) . "</span>
                        </li>";
                }
                $list_items .= '</ul>';

                $subject = "Out of stock Product has been Ordered!";
                $response = $emailSender->sendOutOfStockEmail($admin_email, $subject, $list_items);
                if (!$response['success']) {
                    $response['error'] = "Failed to send mail. " . ($response['error'] ?? '');
                }

                $actorId = $cashierid;
                $actor_name = get_staff_name($actorId);
                $actionType = 'no_stock_order';
                $targetId = $estimateid;
                $targetType = "No Stock Order";
                $message = "Estimate #$estimateid has out-of-stock items ordered";
                $url = '?page=estimate_list';
                $recipientIds = getAdminIDs();
                createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);
                
                
            }
        }

        if (!empty($pre_orders)) {
            $list_items = '<ul style="list-style-type: none; padding-left: 0;">';
            foreach ($pre_orders as $key => $value) {
                $label = ucfirst(str_replace('_', ' ', $key));
                $list_items .= "
                    <li style='margin-bottom: 8px;'>
                        <span style='display: inline-block; min-width: 140px; font-weight: bold; color: #333;'>$label:</span>
                        <span style='color: #555;'>" . htmlspecialchars($value) . "</span>
                    </li>";
            }
            $list_items .= '</ul>';
        
            $subject ="Out of Stock items has been preordered";
            $response = $emailSender->sendPreOrderEmail($admin_email, $subject, $list_items);
            if (!$response['success']) {
                $response['error'] = "Failed to send mail. " . ($response['error'] ?? '');
            }

            $actorId = $cashierid;
            $actor_name = get_staff_name($actorId);
            $actionType = 'pre_order';
            $targetId = $estimateid;
            $targetType = "Pre-Order";
            $message = "Estimate #$estimateid has items preordered";
            $url = '?page=estimate_list';
            $recipientIds = getAdminIDs();
            createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);
        }

        $actorId = $customerid;
        $actor_name = get_staff_name($actorId);
        $actionType = 'estimate_request';
        $targetId = $estimateid;
        $targetType = "Estimate Submission(Customer)";
        $message = "Estimate #$targetId requested by Customer $actor_name";
        $url = '?page=estimate_list';
        $recipientIds = getAdminIDs();
        createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);

        $query = "INSERT INTO estimate_prod (estimateid, product_id, product_item, quantity, custom_width, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price, custom_color, custom_grade, current_customer_discount, current_loyalty_discount, used_discount, stiff_stand_seam, stiff_board_batten, panel_type) VALUES ";
        $query .= implode(', ', $values);

        if ($conn->query($query) === TRUE) {
            $query = "INSERT INTO order_estimate (order_estimate_id, type) VALUES ('$estimateid','1')";
            if ($conn->query($query) === TRUE) {
                $order_estimate_id = $conn->insert_id;
                $baseUrl = "https://delivery.ilearnsda.com/test.php";
                $prodValue = $order_estimate_id;
                $url = $baseUrl . "?prod=" . urlencode($prodValue);
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                curl_exec($ch);
                curl_close($ch);
                
                deleteCustomerCart();
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

if (isset($_POST['save_order'])) {
    header('Content-Type: application/json');
    $response = [];

    // Sanitize inputs
    $credit_amt = floatval($_POST['credit_amt']);
    $cash_amt = floatval($_POST['cash_amt']);
    $job_id = intval($_POST['job_id']);
    $job_name = mysqli_real_escape_string($conn, $_POST['job_name'] ?? '');
    $job_po = mysqli_real_escape_string($conn, $_POST['job_po'] ?? '');
    $deliver_method = mysqli_real_escape_string($conn, $_POST['deliver_method'] ?? 'pickup');
    $deliver_address = mysqli_real_escape_string($conn, $_POST['deliver_address'] ?? '');
    $deliver_city = mysqli_real_escape_string($conn, $_POST['deliver_city'] ?? '');
    $deliver_state = mysqli_real_escape_string($conn, $_POST['deliver_state'] ?? '');
    $deliver_zip = mysqli_real_escape_string($conn, $_POST['deliver_zip'] ?? '');
    $delivery_amt = mysqli_real_escape_string($conn, $_POST['delivery_amt'] ?? '');
    $deliver_fname = mysqli_real_escape_string($conn, $_POST['deliver_fname'] ?? '');
    $deliver_lname = mysqli_real_escape_string($conn, $_POST['deliver_lname'] ?? '');
    $deliver_phone = mysqli_real_escape_string($conn, $_POST['deliver_phone'] ?? '');
    $deliver_email = mysqli_real_escape_string($conn, $_POST['deliver_email'] ?? '');
    $pay_type = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? '');
    $customer_tax = mysqli_real_escape_string($conn, $_POST['customer_tax'] ?? '');
    $tax_exempt_number = mysqli_real_escape_string($conn, $_POST['tax_exempt_number'] ?? '');
    $truck = intval($_POST['truck']);
    $contractor_id = intval($_POST['contractor_id'] ?? 0);

    $estimateid = intval($_SESSION['estimateid']);
    $customerid = intval($_SESSION['customer_id']);
    $cashierid = intval($_SESSION['userid']);

    if (!isset($_SESSION['customer_id'])) {
        $response['error'] = "Customer ID or cart is not set.";
        echo json_encode($response);
        exit;
    }

    $cart = getCartDataByCustomerId($customerid);

    $applyStoreCredit = floatval($_POST['applyStoreCredit']);
    $applyJobDeposit = floatval($_POST['applyJobDeposit']);

    $order_date = date('Y-m-d H:i:s');
    $discount_default = floatval(getCustomerDiscount($customerid)) / 100;

    $customer_details = getCustomerDetails($customerid);

    if (!empty($customer_tax)) {
        $tax_rate = floatval(getCustomerTaxById($customer_tax)) / 100;
        $tax_status = $customer_tax;
    } else {
        $tax_rate = floatval(getCustomerTaxById($customer_details['tax_status'])) / 100;
        $tax_status = $customer_details['tax_status'];
    }

    $total_price = 0;
    $total_discounted_price = 0;
    $approval_products = [];

    foreach ($cart as $item) {
        $discount = isset($item['used_discount']) ? floatval($item['used_discount']) / 100 : $discount_default;
        $product_id = intval($item['product_id']);
        $product_details = getProductDetails($product_id);

        $customer_pricing = getPricingCategory($product_details['product_category'], $customer_details['customer_pricing']) / 100;
        $quantity_cart = intval($item['quantity_cart']);
        $unit_price = floatval($item['unit_price']);
        $estimate_length = floatval($item['estimate_length']);
        $estimate_length_inch = floatval($item['estimate_length_inch']);
        $amount_discount = !empty($item["amount_discount"]) ? floatval($item["amount_discount"]) : 0;

        $total_length = ($estimate_length + ($estimate_length_inch / 12));
        $actual_price = $unit_price * $quantity_cart * $total_length;

        $price_after_discount = ($actual_price * (1 - $discount) * (1 - $customer_pricing)) - $amount_discount;
        $price_after_discount = max(0, $price_after_discount);

        $discounted_price = $price_after_discount * (1 + $tax_rate);

        $total_price += $actual_price;
        $total_discounted_price += $discounted_price;

        $approval_products[] = [
            'productid' => $product_id,
            'product_item' => $item['product_item'],
            'quantity' => $quantity_cart,
            'custom_color' => $item['custom_color'] ?? null,
            'custom_grade' => $item['custom_grade'] ?? null,
            'custom_gauge' => $item['custom_gauge'] ?? 0,
            'custom_profile' => $item['custom_profile'] ?? 0,
            'custom_width' => $item['custom_width'],
            'custom_height' => $item['custom_height'] ?? null,
            'custom_bend' => $item['custom_bend'] ?? null,
            'custom_hem' => $item['custom_hem'] ?? null,
            'custom_length' => $item['custom_length'] ?? null,
            'custom_length2' => $item['custom_length2'] ?? null,
            'actual_price' => $actual_price,
            'discounted_price' => $price_after_discount,
            'product_category' => $product_details['product_category'],
            'usageid' => $item['usageid'] ?? 0,
            'current_customer_discount' => $item['current_customer_discount'] ?? 0,
            'current_loyalty_discount' => $item['current_loyalty_discount'] ?? 0,
            'used_discount' => $item['used_discount'] ?? 0,
            'stiff_stand_seam' => $item['stiff_stand_seam'],
            'stiff_board_batten' => $item['stiff_board_batten'],
            'panel_type' => $item['panel_type'],
            'panel_style' => $item['panel_style'] ?? null,
            'assigned_coils' => $item['assigned_coils'] ?? null,
        ];
    }

    $insert_approval = "
        INSERT INTO approval (
            status, cashier, total_price, discounted_price, discount_percent,
            cash_amt, disc_amount, submitted_date, type_approval, customerid,
            originalcustomerid, job_name, job_po, deliver_address, deliver_city,
            deliver_state, deliver_zip, delivery_amt, deliver_fname, deliver_lname,
            pay_type, tax_status, tax_exempt_number, truck, contractor_id
        ) VALUES (
            1,
            '{$cashierid}',
            '{$total_price}',
            '{$total_discounted_price}',
            '{$discount_default}',
            '{$cash_amt}',
            '{$amount_discount}',
            NOW(),
            3,
            '{$customerid}',
            '{$customerid}',
            '{$job_name}',
            '{$job_po}',
            '{$deliver_address}',
            '{$deliver_city}',
            '{$deliver_state}',
            '{$deliver_zip}',
            '{$delivery_amt}',
            '{$deliver_fname}',
            '{$deliver_lname}',
            '{$pay_type}',
            '{$tax_status}',
            '{$tax_exempt_number}',
            '{$truck}',
            '{$contractor_id}'
        )
    ";

    if (!mysqli_query($conn, $insert_approval)) {
        die(json_encode(['success' => false, 'message' => 'Approval insert failed: ' . mysqli_error($conn)]));
    }

    $approval_id = mysqli_insert_id($conn);

    foreach ($approval_products as $p) {
        $sql = "
            INSERT INTO approval_product (
                approval_id, productid, product_item, status, quantity,
                custom_color, custom_grade, custom_gauge, custom_profile, custom_width,
                custom_height, custom_bend, custom_hem, custom_length, custom_length2,
                actual_price, discounted_price, product_category, usageid, current_customer_discount,
                current_loyalty_discount, used_discount, stiff_stand_seam, stiff_board_batten,
                panel_type, panel_style, assigned_coils
            ) VALUES (
                '$approval_id',
                '{$p['productid']}',
                '" . mysqli_real_escape_string($conn, $p['product_item']) . "',
                0,
                '{$p['quantity']}',
                " . ($p['custom_color'] !== null ? "'" . mysqli_real_escape_string($conn, $p['custom_color']) . "'" : "NULL") . ",
                " . ($p['custom_grade'] !== null ? "'" . mysqli_real_escape_string($conn, $p['custom_grade']) . "'" : "NULL") . ",
                " . ($p['custom_gauge'] ?? 0) . ",
                " . ($p['custom_profile'] ?? 0) . ",
                '" . mysqli_real_escape_string($conn, $p['custom_width']) . "',
                " . (!empty($p['custom_height']) ? "'" . mysqli_real_escape_string($conn, $p['custom_height']) . "'" : "NULL") . ",
                " . (!empty($p['custom_bend']) ? "'" . mysqli_real_escape_string($conn, $p['custom_bend']) . "'" : "NULL") . ",
                " . (!empty($p['custom_hem']) ? "'" . mysqli_real_escape_string($conn, $p['custom_hem']) . "'" : "NULL") . ",
                " . (!empty($p['custom_length']) ? "'" . mysqli_real_escape_string($conn, $p['custom_length']) . "'" : "NULL") . ",
                " . (!empty($p['custom_length2']) ? "'" . mysqli_real_escape_string($conn, $p['custom_length2']) . "'" : "NULL") . ",
                '{$p['actual_price']}',
                '{$p['discounted_price']}',
                '{$p['product_category']}',
                '{$p['usageid']}',
                '{$p['current_customer_discount']}',
                '{$p['current_loyalty_discount']}',
                '{$p['used_discount']}',
                '{$p['stiff_stand_seam']}',
                '{$p['stiff_board_batten']}',
                " . (!empty($p['panel_type']) ? "'" . mysqli_real_escape_string($conn, $p['panel_type']) . "'" : "NULL") . ",
                " . (!empty($p['panel_style']) ? "'" . mysqli_real_escape_string($conn, $p['panel_style']) . "'" : "NULL") . ",
                " . (!empty($p['assigned_coils']) ? "'" . mysqli_real_escape_string($conn, $p['assigned_coils']) . "'" : "NULL") . "
            )
        ";
        mysqli_query($conn, $sql);
    }

    $actorId = $customerid;
    $actor_name = get_customer_name($actorId);
    $actionType = 'request_approval';
    $targetId = $approval_id;
    $targetType = "Request Approval(Customer)";
    $message = "Approval #$targetId requested by $actor_name";
    $url = '?page=approval_list';
    $recipientIds = getAdminIDs();
    createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);

    echo json_encode([
        'success' => false,
        'error' => 'Order submitted for approval'
    ]);

    $conn->close();

    exit;
}

if (isset($_POST['save_approval'])) {
    header('Content-Type: application/json');
    $response = [];
    
    if (!isset($_SESSION['customer_id'])) {
        $response['error'] = "Customer ID or cart is not set.";
        echo json_encode($response);
        exit;
    }

    $customerid = intval($_SESSION['customer_id']);
    $cashierid = 0;

    $cart = getCartDataByCustomerId($customerid);
    if (empty($cart)) {
        $response['message'] = "Cart is empty.";
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    $submitted_date = date('Y-m-d H:i:s');
    $discount_default = floatval(getCustomerDiscount($customerid)) / 100;

    $customer_details = getCustomerDetails($customerid);

    $total_price = 0;
    $total_discounted_price = 0;

    foreach ($cart as $item) {
        $discount = 0;
        if (isset($item['used_discount']) && is_numeric($item['used_discount'])) {
            $discount = floatval($item['used_discount']) / 100;
        } else {
            $discount = isset($discount_default) ? $discount_default : 0.0;
        }
        $product_id = intval($item['product_id']);
        $product_details = getProductDetails($product_id);
        $customer_pricing = getPricingCategory($product_details['product_category'], $customer_details['customer_pricing']) / 100;
        $quantity_cart = intval($item['quantity_cart']);
        $unit_price = $customer_pricing * floatval($item['unit_price']);
        $estimate_length = floatval($item['estimate_length']);
        $estimate_length_inch = floatval($item['estimate_length_inch']);
        $amount_discount = !empty($item["amount_discount"]) ? $item["amount_discount"] : 0;

        $actual_price = $unit_price * $quantity_cart;
        $discounted_price = ($actual_price * (1 - $discount)) - $amount_discount;

        $total_price += $actual_price;
        $total_discounted_price += $discounted_price;
    }

    $query = "INSERT INTO approval (cashier, total_price, discounted_price, discount_percent, submitted_date, customerid, originalcustomerid) 
              VALUES ('$cashierid', '$total_price', '$total_discounted_price', '".($discount * 100)."', '$submitted_date', '$customerid', '$customerid')";

    if ($conn->query($query) === TRUE) {
        $approval_id = $conn->insert_id;

        $values = [];
        foreach ($cart as $item) {
            $discount = 0;
            if (isset($item['used_discount']) && is_numeric($item['used_discount'])) {
                $discount = floatval($item['used_discount']) / 100;
            } else {
                $discount = isset($discount_default) ? $discount_default : 0.0;
            }
            $product_id = intval($item['product_id']);

            $product_item = $item['product_item'] ?? '';

            $product_details = getProductDetails($product_id);
            $customer_pricing = getPricingCategory($product_details['product_category'], $customer_details['customer_pricing']) / 100;
            $quantity_cart = intval($item['quantity_cart']);
            $unit_price = $customer_pricing * floatval($item['unit_price']);
            $estimate_width = !empty($item['estimate_width']) ? floatval($item['estimate_width']) : floatval($product_details['width']);
            $estimate_bend = floatval($item['estimate_bend']);
            $estimate_hem = floatval($item['estimate_hem']);
            $estimate_length = floatval($item['estimate_length']);
            $estimate_length_inch = floatval($item['estimate_length_inch']);
            $custom_color = $item['custom_color'];
            $custom_grade = $item['custom_grade'];
            $amount_discount = !empty($item["amount_discount"]) ? $item["amount_discount"] : 0;
            $actual_price = $unit_price;
            $discounted_price = ($actual_price * (1 - $discount)) - $amount_discount;
            $product_category = intval($product_details['product_category']);
            $curr_discount = intval(getCustomerDiscountProfile($customerid));
            $loyalty_discount = intval(getCustomerDiscountLoyalty($customerid));
            $used_discount = !empty($item['used_discount']) ? $item['used_discount'] : getCustomerDiscount($customerid);
            $stiff_stand_seam = !empty($item['stiff_stand_seam']) ? $item['stiff_stand_seam'] : '0';
            $stiff_board_batten = !empty($item['stiff_board_batten']) ? $item['stiff_board_batten'] : '0';
            $panel_type = !empty($item['panel_type']) ? $item['panel_type'] : '0';

            $values[] = "('$approval_id', '$product_id', '$product_item', '$quantity_cart', '$estimate_width', '$estimate_bend', '$estimate_hem', '$estimate_length', '$estimate_length_inch', '$actual_price', '$discounted_price', '$product_category', '$custom_color' , '$custom_grade', '$curr_discount', '$loyalty_discount', '$used_discount', '$stiff_stand_seam', '$stiff_board_batten', '$panel_type')";
        }

        $query = "INSERT INTO approval_product (approval_id, productid, product_item, quantity, custom_width, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price, product_category, custom_color, custom_grade, current_customer_discount, current_loyalty_discount, used_discount, stiff_stand_seam, stiff_board_batten, panel_type) VALUES ";
        $query .= implode(', ', $values);

        if ($conn->query($query) === TRUE) {

            deleteCustomerCart();

            $response['success'] = true;
            $response['approval_id'] = $approval_id;

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
    deleteCustomerCart();
}

if (isset($_POST['save_trim'])) {
    $customer_id = (int)$_SESSION['customer_id'];
    $id       = mysqli_real_escape_string($conn, $_POST['id']);
    $line     = (int)($_POST['line'] ?? 0);
    $price    = floatval($_POST['price']);
    $drawing_data = mysqli_real_escape_string($conn, $_POST['drawing_data']);
    $img_src  = mysqli_real_escape_string($conn, $_POST['img_src']);
    $is_pre_order = (int)($_POST['is_pre_order'] ?? 0);
    $is_custom    = (int)($_POST['is_custom'] ?? 0);

    $color  = mysqli_real_escape_string($conn, $_POST['color'] ?? '');
    $grade  = mysqli_real_escape_string($conn, $_POST['grade'] ?? '');
    $gauge  = mysqli_real_escape_string($conn, $_POST['gauge'] ?? '');

    $quantities = $_POST['quantity'] ?? [];
    $lengths    = $_POST['length'] ?? [];
    $notes      = $_POST['notes'] ?? [];

    $query = "SELECT * FROM product WHERE product_id = '$id' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) == 0) {
        echo json_encode(['error' => "Trim Product not available"]);
        exit;
    }
    $row = mysqli_fetch_assoc($result);

    foreach ($quantities as $i => $quantity) {
        $quantity = floatval($quantity);
        $length   = floatval($lengths[$i] ?? 0);
        $note     = mysqli_real_escape_string($conn, $notes[$i] ?? '');

        if ($quantity <= 0) continue;

        $feet        = floor($length);
        $decimalFeet = $length - $feet;
        $inches      = $decimalFeet * 12;

        $check = mysqli_query(
            $conn,
            "SELECT id, quantity_cart FROM customer_cart 
             WHERE customer_id = '$customer_id' 
             AND product_id = '$id'
             AND custom_grade = '$grade'
             AND custom_gauge = '$gauge'
             AND custom_color = '$color'
             AND note = '$note'
             LIMIT 1"
        );

        if ($check && mysqli_num_rows($check) > 0) {
            $existing = mysqli_fetch_assoc($check);
            updateCartRow(
                ['quantity_cart' => $existing['quantity_cart'] + $quantity],
                ['id' => $existing['id']]
            );
        } else {
            $cartRow = [
                'customer_id'        => $customer_id,
                'product_id'         => $row['product_id'],
                'product_item'       => $row['product_item'],
                'unit_price'         => $price,
                'quantity_cart'      => $quantity,
                'quantity_ttl'       => 0,
                'quantity_in_stock'  => getProductStockTotal($row['product_id']),
                'estimate_width'     => 0,
                'estimate_length'    => $feet,
                'estimate_length_inch' => $inches,
                'prod_usage'         => 0,
                'custom_color'       => $color,
                'weight'             => 0,
                'supplier_id'        => $row['supplier_id'],
                'custom_grade'       => $grade,
                'custom_profile'     => $row['profile'],
                'custom_gauge'       => $gauge,
                'is_pre_order'       => $is_pre_order,
                'is_custom'          => $is_custom,
                'custom_img_src'     => $img_src,
                'drawing_data'       => $drawing_data,
                'note'               => $note,
                'created_at'         => date('Y-m-d H:i:s')
            ];
            $newId = insertCartRow($cartRow);

            updateCartRow(['line' => $newId], ['id' => $newId]);
        }
    }

    echo json_encode(['success' => true]);
}

if (isset($_POST['save_custom_length'])) {
    $id   = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line'] ?? 1);

    $quantities  = $_POST['quantity'] ?? [];
    $feet_list   = $_POST['length_feet'] ?? [];
    $inch_list   = $_POST['length_inch'] ?? [];
    $prices      = $_POST['price'] ?? [];
    $color_id    = $_POST['color_id'] ?? [];
    $notes       = $_POST['notes'] ?? [];

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = array();
    }

    $query = "SELECT * FROM product WHERE product_id = '$id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        foreach ($quantities as $idx => $qty) {
            $quantity           = floatval($qty);
            $estimate_length    = round(floatval($feet_list[$idx] ?? 0), 2);
            $estimate_length_in = round(floatval($inch_list[$idx] ?? 0), 2);
            $price              = floatval($prices[$idx] ?? 0);
            $custom_color       = intval($color_id[$idx] ?? 0);
            $note               = $notes[$idx] ?? '';

            if ($quantity <= 0) continue;

            $found = false;
            foreach ($_SESSION["cart"] as &$item) {
                if (
                    $item['product_id'] == $id &&
                    $item['estimate_length'] == $estimate_length &&
                    $item['estimate_length_inch'] == $estimate_length_in &&
                    $item['custom_color'] == $custom_color
                ) {
                    // ✅ Update session
                    $item['quantity_cart'] += $quantity;

                    // ✅ Update DB
                    updateCartRow(
                        ['quantity_cart' => $item['quantity_cart']],
                        [
                            'product_id'          => $id,
                            'estimate_length'     => $estimate_length,
                            'estimate_length_inch'=> $estimate_length_in,
                            'custom_color'        => $custom_color
                        ]
                    );

                    $found = true;
                    break;
                }
            }
            unset($item);

            if (!$found) {
                $line_to_use = (count($_SESSION["cart"]) > 0) ? max(array_column($_SESSION["cart"], 'line')) + 1 : 1;

                $item_array = array(
                    'product_id'          => $row['product_id'],
                    'product_item'        => $row['product_item'],
                    'unit_price'          => $price,
                    'line'                => $line_to_use,
                    'quantity_ttl'        => getProductStockTotal($row['product_id']),
                    'quantity_in_stock'   => 0,
                    'quantity_cart'       => $quantity,
                    'estimate_width'      => 0,
                    'estimate_length'     => $estimate_length,
                    'estimate_length_inch'=> $estimate_length_in,
                    'usage'               => 0,
                    'custom_color'        => $custom_color,
                    'weight'              => 0,
                    'supplier_id'         => '',
                    'custom_grade'        => '',
                    'custom_profile'      => 0,
                    'custom_gauge'        => '',
                    'note'                => $note
                );

                // ✅ Insert to session
                $_SESSION["cart"][] = $item_array;

                // ✅ Insert to DB
                insertCartRow($item_array);
            }
        }
    } else {
        echo json_encode(['error' => "Trim Product not available"]);
        exit;
    }

    echo json_encode(['success' => print_r($_SESSION["cart"], true)]);
}


if (isset($_POST['save_drawing'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $quantity = floatval(mysqli_real_escape_string($conn, $_POST['quantity']));
    $length = floatval(mysqli_real_escape_string($conn, $_POST['length']));
    $price = floatval(mysqli_real_escape_string($conn, $_POST['price']));

    if (isset($_POST['image_data'])) {
        $image_data = $_POST['image_data'];
        $image_data = str_replace('data:image/png;base64,', '', $image_data);
        $image_data = str_replace(' ', '+', $image_data);
        $decodedData = base64_decode($image_data);
        $images_directory = "../../images/drawing/";
        $filename = uniqid() . '.png';

        if (file_put_contents($images_directory . $filename, $decodedData)) {
            echo json_encode(['filename' => $filename]);
        } else {
            echo json_encode(['error' => 'Failed to save image']);
        }
    } else {
        echo json_encode(['error' => 'No image data provided']);
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
                $sql = "SELECT COUNT(*) AS count FROM coil_product WHERE color_sold_as = '".$values["custom_color"]."'";
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
        <div id="change_price_container" class="row">
            <div class="col-md-12">
                <label class="form-label" for="price_input">Price</label>
                <input class="form-control" id="price_input" name="price_input" placeholder="Price" />
            </div>
            <div class="col-md-12 mt-4">
                <label class="form-label" for="disc_input">Discount</label>
                <input class="form-control" id="disc_input" name="disc_input" placeholder="Discount ($)" />
            </div>
            <div class="col-md-12 mt-4">
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
            <div class="col-md-12 mt-4">
                <label class="form-label" for="product_select" style="display:block; width: 100%;">Products Affected</label>
                <select id="product_select" class="form-select custom-select text-start" multiple="multiple">
                    <?php
                        foreach ($_SESSION["cart"] as $keys => $values) {
                            ?>
                                <option value="<?= $values['product_id'] ?>"><?= getProductName($values['product_id']) ?></option>
                            <?php
                        }
                    ?>
                </select>
            </div>
            <div class="col-md-12 mt-4">
                <label class="form-label" for="notes_input">Notes</label>
                <textarea class="form-control" id="notes_input" name="notes_input" rows="3" placeholder="Notes here"></textarea>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                let isUpdating = false;

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

                $('#disc_input').on('change', function () {
                    if (isUpdating) return;
                    isUpdating = true;
                    $("#price_group_select").val(null).trigger('change');
                    isUpdating = false;
                });

                $('#price_group_select').on('change', function () {
                    if (isUpdating) return;
                    isUpdating = true;
                    $('#disc_input').val('');
                    isUpdating = false;
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
                $sql = "SELECT grade FROM coil_product";
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
    $orig_color = mysqli_real_escape_string($conn, $_POST['orig_color']);
    $in_stock_color = mysqli_real_escape_string($conn, $_POST['in_stock_color']);
    $selected_category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $customer_id = mysqli_real_escape_string($conn, $_SESSION['customer_id']);

    if (!empty($selected_category_id)) {
        $query = "UPDATE customer_cart AS cc
                  JOIN product AS p ON cc.product_id = p.product_id
                  SET cc.custom_color = '$in_stock_color'
                  WHERE cc.customer_id = '$customer_id' 
                    AND cc.custom_color = '$orig_color'
                    AND p.product_category = '$selected_category_id'";
    } else {
        $query = "UPDATE customer_cart 
                  SET custom_color = '$in_stock_color' 
                  WHERE customer_id = '$customer_id' 
                    AND custom_color = '$orig_color'";
    }
    mysqli_query($conn, $query);
    echo "success";
    exit;
}

if (isset($_POST['change_grade'])) {
    $orig_grade = mysqli_real_escape_string($conn, $_POST['orig_grade']);
    $in_stock_grade = mysqli_real_escape_string($conn, $_POST['in_stock_grade']);
    $selected_category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $customer_id = mysqli_real_escape_string($conn, $_SESSION['customer_id']);

    if (!empty($selected_category_id)) {
        $query = "UPDATE customer_cart AS cc
                  JOIN product AS p ON cc.product_id = p.product_id
                  SET cc.custom_grade = '$in_stock_grade'
                  WHERE cc.customer_id = '$customer_id' 
                    AND cc.custom_grade = '$orig_grade'
                    AND p.product_category = '$selected_category_id'";
    } else {
        $query = "UPDATE customer_cart 
                  SET custom_grade = '$in_stock_grade' 
                  WHERE customer_id = '$customer_id' 
                    AND custom_grade = '$orig_grade'";
    }
    mysqli_query($conn, $query);
    echo "success";
    exit;
}

if (isset($_POST['change_price'])) {
    $price_group_select = mysqli_real_escape_string($conn, $_POST['price_group_select']);
    $product_select = $_POST['product_select'];
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $disc = mysqli_real_escape_string($conn, $_POST['disc']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $customer_id = mysqli_real_escape_string($conn, $_SESSION['customer_id']);

    if (!empty($product_select)) {
        foreach ($product_select as $product_id) {
            $product_id = mysqli_real_escape_string($conn, $product_id);
            $updates = [];

            $updates[] = "used_discount = '$price_group_select'";
            if (!empty($price)) $updates[] = "unit_price = '$price'";
            if (!empty($disc)) $updates[] = "amount_discount = '$disc'";
            if (!empty($notes)) $updates[] = "notes = '$notes'";

            if (!empty($updates)) {
                $update_query = "UPDATE customer_cart 
                                 SET " . implode(", ", $updates) . "
                                 WHERE customer_id = '$customer_id' 
                                   AND product_id = '$product_id'";
                mysqli_query($conn, $update_query);
            }
        }
        echo "success";
        exit;
    }
}

if (isset($_POST['change_discount'])) {
    $discount = floatval($_POST['discount']);
    $customer_id = mysqli_real_escape_string($conn, $_SESSION['customer_id']);

    $query = "UPDATE customer_cart 
              SET used_discount = '$discount' 
              WHERE customer_id = '$customer_id'";
    mysqli_query($conn, $query);
    echo "success";
    exit;
}

if (isset($_POST['add_to_cart'])) {
    $quantity       = $_POST['quantity_product'] ?? [];
    $quantity       = array_map(fn($qty) => empty($qty) ? 0 : $qty, $quantity);

    $lengthFeet         = $_POST['length_feet'] ?? [];
    $lengthInch         = $_POST['length_inch'] ?? [];
    $lengthFraction     = $_POST['length_fraction'] ?? [];
    $panel_types        = $_POST['panel_option'] ?? [];
    $panel_styles       = $_POST['panel_style'] ?? [];
    $panel_drip_stops   = $_POST['panel_drip_stop'] ?? [];
    $bundle_names       = $_POST['bundle_name'] ?? [];
    $notes              = $_POST['notes'] ?? [];
    $customer_id        = $_SESSION['customer_id'] ?? '';
    $product_id    = mysqli_real_escape_string($conn, $_POST['product_id']);
    $is_pre_order  = mysqli_real_escape_string($conn, $_POST['is_pre_order'] ?? 0);
    $color         = mysqli_real_escape_string($conn, $_POST['color'] ?? '');
    $grade         = mysqli_real_escape_string($conn, $_POST['grade'] ?? '');
    $gauge         = mysqli_real_escape_string($conn, $_POST['gauge'] ?? '');
    $profile       = mysqli_real_escape_string($conn, $_POST['profile'] ?? '');
    $stiff_board_batten = $_POST['stiff_board_batten'] ?? '';
    $stiff_stand_seam   = $_POST['stiff_stand_seam'] ?? '';
    $bend_product       = floatval($_POST['bend_product'] ?? 0);
    $hem_product        = floatval($_POST['hem_product'] ?? 0);

    $product_details = getProductDetails($product_id);

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = [];
    }

    foreach ($quantity as $index => $qty) {
        $length_feet = isset($lengthFeet[$index]) ? parseNumber($lengthFeet[$index]) : 0;
        $length_inch = isset($lengthInch[$index]) ? parseNumber($lengthInch[$index]) : 0;
        $note   = $notes[$index] ?? '';

        if ($length_feet == 0 && $length_inch == 0 || ($qty == 0)) {
            continue;
        }
        
        $panel_type_row  = $panel_types[$index]  ?? 'solid';
        $panel_style_row = $panel_styles[$index] ?? 'regular';
        $panel_drip_stop_row = $panel_drip_stops[$index] ?? '';
        $bundle_name_row = $bundle_names[$index] ?? '';

        $quantityInStock = getProductStockInStock($product_id);
        $totalQuantity   = getProductStockTotal($product_id);
        $totalStock      = $totalQuantity;

        $key = false;
        foreach ($_SESSION["cart"] as $cartKey => $item) {
            if (
                $item['product_id'] == $product_id &&
                $item['custom_grade'] == $grade &&
                $item['custom_gauge'] == $gauge &&
                $item['custom_color'] == $color &&
                $item['panel_type'] == $panel_type_row &&
                $item['panel_drip_stop'] == $panel_drip_stop_row &&
                $item['estimate_length'] == $length_feet &&
                $item['estimate_length_inch'] == $length_inch &&
                $item['note'] == $note
            ) {
                $key = $cartKey;
                break;
            }
        }

        if ($key !== false) {
            $requestedQuantity = max($qty, 1);
            $_SESSION["cart"][$key]['quantity_cart'] += min($requestedQuantity, $totalStock);

            updateCartRow(
                ['quantity_cart' => $_SESSION["cart"][$key]['quantity_cart']],
                [
                    'product_id'          => $product_id,
                    'custom_grade'        => $grade,
                    'custom_gauge'        => $gauge,
                    'custom_color'        => $color,
                    'panel_type'          => $panel_type_row,
                    'panel_drip_stop'     => $panel_drip_stop_row,
                    'estimate_length'     => $length_feet,
                    'estimate_length_inch'=> $length_inch,
                    'note'                => $note
                ]
            );

        } else {
            $query  = "SELECT * FROM product WHERE product_id = '$product_id'";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                $row           = mysqli_fetch_assoc($result);
                $item_quantity = $qty;

                $basePrice = floatval($product_details['unit_price'] ?? 0);
                if (!empty($product_details['sold_by_feet']) && $product_details['sold_by_feet'] == '1') {
                    $length = floatval($product_details['length'] ?? 0);
                    if ($length > 0) {
                        $basePrice = $basePrice / $length;
                    }
                }

                $unit_price = calculateUnitPrice(
                    $basePrice,
                    1,
                    0,
                    $panel_type_row,
                    $row['sold_by_feet'],
                    $bend_product,
                    $hem_product
                );

                $weight   = floatval($row['weight']);
                $nextLine = empty($_SESSION['cart']) ? 1 : (max(array_keys($_SESSION['cart'])) + 1);

                $item_array = [
                    'customer_id'         => $customer_id,
                    'product_id'          => $row['product_id'],
                    'product_item'        => getProductName($row['product_id']),
                    'supplier_id'         => $row['supplier_id'],
                    'unit_price'          => $unit_price,
                    'line'                => $nextLine,
                    'quantity_ttl'        => $totalStock,
                    'quantity_in_stock'   => $quantityInStock,
                    'quantity_cart'       => $item_quantity,
                    'estimate_width'      => $row['width'],
                    'estimate_length'     => $length_feet,
                    'estimate_length_inch'=> $length_inch,
                    'prod_usage'          => 0,
                    'custom_color'        => !empty($color) ? $color : $row['color'],
                    'panel_type'          => $panel_type_row,
                    'panel_style'         => $panel_style_row,
                    'panel_drip_stop'     => $panel_drip_stop_row,
                    'weight'              => $weight,
                    'custom_grade'        => !empty($grade) ? $grade : $row['grade'],
                    'custom_gauge'        => !empty($gauge) ? $gauge : $row['gauge'],
                    'custom_profile'      => !empty($profile) ? $profile : $row['profile'],
                    'stiff_board_batten'  => $stiff_board_batten,
                    'stiff_stand_seam'    => $stiff_stand_seam,
                    'is_pre_order'        => $is_pre_order,
                    'bundle_name'         => $bundle_name_row,
                    'note'                => $note
                ];

                $_SESSION['cart'][$nextLine] = $item_array;
                insertCartRow($item_array);
            }
        }
    }

    echo 'success';
}

if (isset($_POST['add_custom_truss_to_cart'])) {
    $customer_id = mysqli_real_escape_string($conn, $_SESSION['customer_id'] ?? '');
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id'] ?? '');
    $truss_type = mysqli_real_escape_string($conn, $_POST['truss_type'] ?? '');
    $truss_material = mysqli_real_escape_string($conn, $_POST['truss_material'] ?? '');
    $size = mysqli_real_escape_string($conn, $_POST['size'] ?? '');
    $truss_pitch = mysqli_real_escape_string($conn, $_POST['truss_pitch'] ?? '');
    $truss_spacing = mysqli_real_escape_string($conn, $_POST['truss_spacing'] ?? '');
    $truss_ceiling_load = mysqli_real_escape_string($conn, $_POST['truss_ceiling_load'] ?? '');
    $truss_left_overhang = mysqli_real_escape_string($conn, $_POST['truss_left_overhang'] ?? '');
    $truss_right_overhang = mysqli_real_escape_string($conn, $_POST['truss_right_overhang'] ?? '');
    $truss_top_pitch = mysqli_real_escape_string($conn, $_POST['truss_top_pitch'] ?? '');
    $truss_bottom_pitch = mysqli_real_escape_string($conn, $_POST['truss_bottom_pitch'] ?? '');
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity_product'][0] ?? '');
    $cost = mysqli_real_escape_string($conn, $_POST['cost'] ?? '');
    $price = mysqli_real_escape_string($conn, $_POST['price'] ?? '');
    $line = 1;

    $material_name = getTrussMaterialName($truss_material);
    $overhang_left_name = getTrussOverhangName($truss_left_overhang);
    $overhang_right_name = getTrussOverhangName($truss_right_overhang);
    $pitch_name = getTrussPitchName($truss_pitch);
    $spacing_name = getTrussSpacingName($truss_spacing);
    $type_name = getTrussTypeName($truss_type);

    $parts = ['(Custom Truss)'];
    if (!empty($size)) $parts[] = $size;
    if (!empty($material_name)) $parts[] = $material_name;
    if (!empty($overhang_left_name)) $parts[] = $overhang_left_name;
    if (!empty($overhang_right_name)) $parts[] = $overhang_right_name;
    if (!empty($pitch_name)) $parts[] = $pitch_name;
    if (!empty($spacing_name)) $parts[] = $spacing_name;
    if (!empty($type_name)) $parts[] = $type_name;

    $product_item = mysqli_real_escape_string($conn, implode(' - ', $parts));

    $query = "SELECT * FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $quantityInStock = getProductStockInStock($product_id);
        $totalQuantity = getProductStockTotal($product_id);

        $weight = floatval($row['weight']);

        $check_sql = "SELECT * FROM customer_cart 
                      WHERE customer_id = '$customer_id' 
                        AND product_id = '$product_id' 
                        AND line = '$line' 
                        AND product_item = '$product_item'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $existing = mysqli_fetch_assoc($check_result);
            $new_quantity = $existing['quantity_cart'] + $quantity;
            $update_sql = "UPDATE customer_cart SET 
                            quantity_cart = '$new_quantity' 
                           WHERE customer_cart_id = '{$existing['customer_cart_id']}'";
            mysqli_query($conn, $update_sql);
        } else {
            $insert_sql = "INSERT INTO customer_cart (
                customer_id, product_id, product_item, supplier_id,
                unit_price, line, quantity_ttl, quantity_in_stock, quantity_cart,
                weight, is_pre_order
            ) VALUES (
                '$customer_id', '$product_id', '$product_item', '".$row['supplier_id']."',
                '$price', '$line', '$totalQuantity', '$quantityInStock', '$quantity',
                '$weight', 1
            )";
            mysqli_query($conn, $insert_sql);
        }
    }

    echo 'success';
    exit;
}

if (isset($_POST['add_job_name'])) {
    $customer_id = isset($_SESSION['customer_id']) ? intval($_SESSION['customer_id']) : 0;
    $job_name = isset($_POST['job_name']) ? mysqli_real_escape_string($conn, $_POST['job_name']) : '';

    if(empty($customer_id)){
        echo "Customer is not set. Please select customer first!";
        exit;
    }

    if(empty($job_name)){
        echo "Job name cannot be empty!";
        exit;
    }

    if (!empty($customer_id) && !empty($job_name)) {
        $insert_query = "INSERT INTO jobs (
                            customer_id, 
                            job_name
                        ) VALUES (
                            '$customer_id', 
                            '$job_name'
                        )";
        
        if (mysqli_query($conn, $insert_query)) {
            echo "success";
        } else {
            echo "Error inserting into product_returns.";
        }
    }
}

if (isset($_POST['filter_category'])) {
    $product_category = isset($_REQUEST['product_category']) ? intval($_REQUEST['product_category']) : '';

    $category_condition = "";
    if (!empty($product_category)) {
        $category_condition = "AND product_category = '$product_category'";
    }
        ?>
        <!-- Profile Type -->
        <div class="position-relative w-100 py-2 px-1">
            <select class="form-control ps-5 select2_filter filter-selection" id="select-profile" data-filter-name="Profile Type">
                <option value="" data-category="">All Profile Types</option>
                <optgroup label="Product Line">
                    <?php
                    $query_profile = "SELECT * FROM profile_type WHERE hidden = '0' $category_condition ORDER BY profile_type ASC";
                    $result_profile = mysqli_query($conn, $query_profile);
                    while ($row_profile = mysqli_fetch_array($result_profile)) {
                    ?>
                        <option value="<?= htmlspecialchars($row_profile['profile_type_id']) ?>" 
                                data-category="<?= htmlspecialchars($row_profile['product_category']) ?>">
                            <?= htmlspecialchars($row_profile['profile_type']) ?>
                        </option>
                    <?php } ?>
                </optgroup>
            </select>
        </div>

        <!-- Product Type -->
        <div class="position-relative w-100 py-2 px-1">
            <select class="form-control search-category ps-5 select2_filter filter-selection" id="select-type" data-filter-name="Product Types">
                <option value="" data-category="">All Product Types</option>
                <optgroup label="Product Type">
                    <?php
                    $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' $category_condition ORDER BY product_type ASC";
                    $result_type = mysqli_query($conn, $query_type);
                    while ($row_type = mysqli_fetch_array($result_type)) {
                    ?>
                        <option value="<?= htmlspecialchars($row_type['product_type_id']) ?>" 
                                data-category="<?= htmlspecialchars($row_type['product_category']) ?>">
                            <?= htmlspecialchars($row_type['product_type']) ?>
                        </option>
                    <?php } ?>
                </optgroup>
            </select>
        </div>

        <!-- Colors -->
        <div class="position-relative w-100 py-2 px-1">
            <select class="form-control search-category ps-5 select2_filter filter-selection" id="select-color" data-filter-name="Color">
                <option value="" data-category="">All Colors</option>
                <optgroup label="Product Colors">
                    <?php
                    $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' $category_condition ORDER BY color_name ASC";
                    $result_color = mysqli_query($conn, $query_color);
                    while ($row_color = mysqli_fetch_array($result_color)) {
                    ?>
                        <option value="<?= htmlspecialchars($row_color['color_id']) ?>" 
                                data-category="<?= htmlspecialchars($row_color['product_category']) ?>">
                            <?= htmlspecialchars($row_color['color_name']) ?>
                        </option>
                    <?php } ?>
                </optgroup>
            </select>
        </div>

        <!-- Grade -->
        <div class="position-relative w-100 py-2 px-1">
            <select class="form-control search-category ps-5 select2_filter filter-selection" id="select-grade" data-filter-name="Grade">
                <option value="" data-category="">All Grades</option>
                <optgroup label="Product Grades">
                    <?php
                    $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' $category_condition ORDER BY product_grade ASC";
                    $result_grade = mysqli_query($conn, $query_grade);
                    while ($row_grade = mysqli_fetch_array($result_grade)) {
                    ?>
                        <option value="<?= htmlspecialchars($row_grade['product_grade_id']) ?>" 
                                data-category="<?= htmlspecialchars($row_grade['product_category']) ?>">
                            <?= htmlspecialchars($row_grade['product_grade']) ?>
                        </option>
                    <?php } ?>
                </optgroup>
            </select>
        </div>

        <!-- Gauge -->
        <div class="position-relative w-100 py-2 px-1">
            <select class="form-control ps-5 select2_filter filter-selection" id="select-gauge" data-filter-name="Gauge">
                <option value="" data-category="">All Gauges</option>
                <optgroup label="Product Gauges">
                    <?php
                    $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY product_gauge ASC";
                    $result_gauge = mysqli_query($conn, $query_gauge);
                    while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                    ?>
                        <option value="<?= htmlspecialchars($row_gauge['product_gauge_id']) ?>" 
                                data-category="gauge">
                            <?= htmlspecialchars($row_gauge['product_gauge']) ?>
                        </option>
                    <?php } ?>
                </optgroup>
            </select>
        </div>
        <?php
}

if (isset($_POST['add_cart_screw'])) {
    global $conn;

    $product_id        = (int)$_POST['product_id'];
    $color_id          = (int)$_POST['color_id'];
    $selected_color_id = (int)($_POST['selected_color_id'] ?? 0);
    $type_to_apply     = $_POST['type_to_apply'] ?? 'panel';

    $panel_id = 3;
    $trim_id  = 4;
    $apply_category_id = ($type_to_apply === 'trim') ? $trim_id : $panel_id;

    $q = "
        SELECT p.product_id, p.product_item, p.unit_price, 
               i.inventory_id AS id, i.pack, i.color_id, p.weight
        FROM product p
        LEFT JOIN inventory i 
            ON i.product_id = p.product_id
        WHERE p.product_id = $product_id
          AND i.color_id = $color_id
        ORDER BY i.pack ASC
    ";
    $r = mysqli_query($conn, $q);
    if (!$r || mysqli_num_rows($r) == 0) {
        exit("Screw product not found for this color: $color_id");
    }

    $packs = [];
    while ($row = mysqli_fetch_assoc($r)) {
        $packs[] = $row;
    }

    $total_inches = 0;
    $cartQ = "
        SELECT c.product_id, c.custom_color, c.quantity_cart,
               c.estimate_length, c.estimate_length_inch
        FROM customer_cart c
        JOIN product p ON c.product_id = p.product_id
        WHERE p.product_category = $apply_category_id
          AND c.custom_color = $selected_color_id
    ";
    $cartR = mysqli_query($conn, $cartQ);
    while ($item = mysqli_fetch_assoc($cartR)) {
        $qty = (int)$item['quantity_cart'];
        $len_feet = (float)$item['estimate_length'];
        $len_inch = (float)$item['estimate_length_inch'];
        $total_len_inch = ($len_feet * 12) + $len_inch;

        if ($total_len_inch > 0) {
            $total_inches += $qty * $total_len_inch;
        }
    }

    $screw_details = getProductDetails($product_id);
    $screw_system  = $screw_details['product_system'];
    $screw_sys     = getProductSystemDetails($screw_system);
    $screw_distance = isset($screw_sys['screw_distance']) && $screw_sys['screw_distance'] > 0 
                        ? (int)$screw_sys['screw_distance'] 
                        : 1;

    $screws_needed = $total_inches > 0 
        ? ceil($total_inches / $screw_distance) 
        : 0;

    $chosen_pack = null;
    $chosen_pack_pieces = PHP_INT_MAX;
    foreach ($packs as $pack) {
        $pack_pieces = getPackPieces($pack['pack']);
        if ($pack_pieces <= 0) continue;

        $packs_needed = ceil($screws_needed / $pack_pieces);
        $total_pcs    = $packs_needed * $pack_pieces;

        if ($total_pcs >= $screws_needed && $pack_pieces < $chosen_pack_pieces) {
            $chosen_pack = $pack;
            $chosen_pack_pieces = $pack_pieces;
        }
    }

    if (!$chosen_pack) {
        exit("No valid screw packs found.");
    }

    $pack_size    = $chosen_pack_pieces;
    $packs_needed = ceil($screws_needed / $pack_size);

    $checkQ = "
        SELECT id, quantity_cart 
        FROM customer_cart 
        WHERE product_id = {$chosen_pack['product_id']} 
          AND custom_color = {$chosen_pack['color_id']}
        LIMIT 1
    ";
    $checkR = mysqli_query($conn, $checkQ);

    if (mysqli_num_rows($checkR) > 0) {
        $row = mysqli_fetch_assoc($checkR);
        $new_qty = $row['quantity_cart'] + $packs_needed;
        $updateQ = "UPDATE customer_cart SET quantity_cart = $new_qty WHERE id = {$row['id']}";
        mysqli_query($conn, $updateQ);
    } else {
        $insertQ = "
            INSERT INTO customer_cart 
            (product_id, product_item, unit_price, line, estimate_length, estimate_length_inch, 
             quantity_cart, custom_color, quantity_ttl, quantity_in_stock, weight, created_at) 
            VALUES (
                {$chosen_pack['product_id']},
                '" . mysqli_real_escape_string($conn, getProductName($chosen_pack['product_id'])) . "',
                {$chosen_pack['unit_price']},
                1,
                $pack_size,
                0,
                $packs_needed,
                {$chosen_pack['color_id']},
                " . getProductStockTotal($chosen_pack['product_id']) . ",
                0,
                {$chosen_pack['weight']},
                NOW()
            )
        ";
        mysqli_query($conn, $insertQ);
    }

    echo "Added $packs_needed pack(s) of screws (Apply Type: $type_to_apply, Apply Color: $selected_color_id, Screw Distance: {$screw_distance}) to cart";
}


?>

