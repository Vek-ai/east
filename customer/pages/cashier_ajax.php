<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';
require '../../includes/send_email.php';

$admin_email = getSetting('admin_email');

$trim_id = 4;
$panel_id = 3;
$custom_truss_id = 47;
$special_trim_id = 66;

function cartItemExists($product_id, $line) {
    global $conn;
    $product_id = mysqli_real_escape_string($conn, $product_id);
    $line = (int)$line;
    $customer_id = (int)$_SESSION['customer_id'];

    $query = "SELECT 1 FROM customer_cart WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line LIMIT 1";
    $result = mysqli_query($conn, $query);

    return mysqli_num_rows($result) > 0;
}

if (isset($_POST['modifyquantity']) || isset($_POST['duplicate_product'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $line = isset($_POST['line']) ? (int)$_POST['line'] : 1;
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
    $customer_id = (int)$_SESSION['customer_id'];

    $quantityInStock = getProductStockInStock($product_id);
    $totalQuantity = getProductStockTotal($product_id);
    $totalStock = $totalQuantity;

    if (isset($_POST['duplicate_product'])) {
        $newLine = $line + 1;
        while (cartItemExists($product_id, $newLine)) {
            $newLine++;
        }

        $result = mysqli_query($conn, "SELECT * FROM product WHERE product_id = '$product_id'");
        if ($row = mysqli_fetch_assoc($result)) {
            $length = floatval(preg_replace('/[^0-9.]/', '', $row['length']));
            $estimate_length = floor($length);
            $estimate_length_inch = $length - $estimate_length;

            $weight = floatval($row['weight']);
            $basePrice = floatval($row['unit_price']);
            if ($row['sold_by_feet'] == '1') {
                $basePrice = $basePrice / ($length ?: 1);
            }

            $unitPrice = calculateUnitPrice($basePrice, $estimate_length, $estimate_length_inch, '', $row['sold_by_feet'], 0, 0);
            $item_quantity = min($qty, $totalStock);

            $product_item = mysqli_real_escape_string($conn, getProductName($row['product_id']));
            $supplier_id = (int)$row['supplier_id'];
            $estimate_width = (float)$row['width'];
            $custom_color = mysqli_real_escape_string($conn, $row['color']);
            $custom_grade = (int)$row['grade'];
            $usage = 0;

            $sql = "
                INSERT INTO customer_cart (
                    customer_id, product_id, product_item, supplier_id, unit_price, line,
                    quantity_ttl, quantity_in_stock, quantity_cart,
                    estimate_width, estimate_length, estimate_length_inch,
                    prod_usage, custom_color, weight, custom_grade, created_at
                ) VALUES (
                    $customer_id, '$product_id', '$product_item', $supplier_id, $unitPrice, $newLine,
                    $totalStock, $quantityInStock, $item_quantity,
                    $estimate_width, $estimate_length, $estimate_length_inch,
                    $usage, '$custom_color', $weight, $custom_grade, NOW()
                )
            ";
            mysqli_query($conn, $sql);
        }

    } elseif (cartItemExists($product_id, $line)) {
        if (isset($_POST['setquantity'])) {
            $qty = max($qty, 1);
            mysqli_query($conn, "UPDATE customer_cart SET quantity_cart = $qty, updated_at = NOW() WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
            echo $qty;

        } elseif (isset($_POST['addquantity'])) {
            mysqli_query($conn, "UPDATE customer_cart SET quantity_cart = quantity_cart + $qty, updated_at = NOW() WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
            $res = mysqli_query($conn, "SELECT quantity_cart FROM customer_cart WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
            echo mysqli_fetch_assoc($res)['quantity_cart'];

        } elseif (isset($_POST['deductquantity'])) {
            $res = mysqli_query($conn, "SELECT quantity_cart FROM customer_cart WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
            $row = mysqli_fetch_assoc($res);
            if ((int)$row['quantity_cart'] <= 1) {
                mysqli_query($conn, "DELETE FROM customer_cart WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
                echo 'removed';
            } else {
                mysqli_query($conn, "UPDATE customer_cart SET quantity_cart = quantity_cart - 1, updated_at = NOW() WHERE customer_id = $customer_id AND product_id = '$product_id' AND line = $line");
                echo ((int)$row['quantity_cart'] - 1);
            }
        }

    } else {
        $result = mysqli_query($conn, "SELECT * FROM product WHERE product_id = '$product_id'");
        if ($row = mysqli_fetch_assoc($result)) {
            $length = floatval(preg_replace('/[^0-9.]/', '', $row['length']));
            $estimate_length = floor($length);
            $estimate_length_inch = $length - $estimate_length;

            $weight = floatval($row['weight']);
            $basePrice = floatval($row['unit_price']);
            if ($row['sold_by_feet'] == '1') {
                $basePrice = $basePrice / ($length ?: 1);
            }

            $unitPrice = calculateUnitPrice($basePrice, $estimate_length, $estimate_length_inch, '', $row['sold_by_feet'], 0, 0);
            $item_quantity = min($qty, $totalStock);

            $product_item = mysqli_real_escape_string($conn, getProductName($row['product_id']));
            $supplier_id = (int)$row['supplier_id'];
            $estimate_width = (float)$row['width'];
            $custom_color = mysqli_real_escape_string($conn, $row['color']);
            $custom_grade = (int)$row['grade'];
            $usage = 0;

            $sql = "
                INSERT INTO customer_cart (
                    customer_id, product_id, product_item, supplier_id, unit_price, line,
                    quantity_ttl, quantity_in_stock, quantity_cart,
                    estimate_width, estimate_length, estimate_length_inch,
                    prod_usage, custom_color, weight, custom_grade, created_at
                ) VALUES (
                    $customer_id, '$product_id', '$product_item', $supplier_id, $unitPrice, $line,
                    $totalStock, $quantityInStock, $item_quantity,
                    $estimate_width, $estimate_length, $estimate_length_inch,
                    $usage, '$custom_color', $weight, $custom_grade, NOW()
                )
            ";
            mysqli_query($conn, $sql);
        }
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
        $query_product .= " AND p.on_sale = '1'";
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
            $is_custom_truss = $row_product['product_id'] == $custom_truss_id ? true : false;
            $is_special_trim = $row_product['product_id'] == $special_trim_id ? true : false;
            $is_custom_length = $row_product['is_custom_length'] == 1 ? true : false;

            $qty_input = !$is_panel  && !$is_custom_truss && !$is_special_trim && !$is_trim  && !$is_custom_length
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

    if (!isset($_SESSION['customer_id'])) {
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
    $customerid = intval($_SESSION['customer_id']);

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
        
            $message = "
                <html>
                <head>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            line-height: 1.6;
                            color: #333;
                        }
                        .container {
                            padding: 20px;
                            border: 1px solid #e0e0e0;
                            background-color: #f9f9f9;
                            width: 80%;
                            margin: 0 auto;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        }
                        h2 {
                            color: #0056b3;
                            margin-bottom: 20px;
                        }
                        p {
                            margin: 5px 0;
                        }
                        ul {
                            padding-left: 20px;
                        }
                        li {
                            margin-bottom: 5px;
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>$subject</h2>
                        <ul>
                            $list_items
                        </ul>
                    </div>
                </body>
                </html>
            ";

            $response = sendEmail($admin_email, 'EKM', $subject, $message);
            if ($response['success'] == true) {
            } else {
                $response['error'] = "Failed to send Mail" . $conn->error;
            }
        }

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

    if (!isset($_SESSION['customer_id'])) {
        $response['error'] = "Customer ID or cart is not set.";
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

    $estimateid = intval($_SESSION['estimateid']);
    $customerid = intval($_SESSION['customer_id']);
    $cashierid = 0;
    
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
    $pre_orders = array();
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
        $unit_price = floatval($item['unit_price']);
        $estimate_length = floatval($item['estimate_length']);
        $estimate_length_inch = floatval($item['estimate_length_inch']);
        $amount_discount = !empty($item["amount_discount"]) ? $item["amount_discount"] : 0;

        $actual_price = $unit_price * $quantity_cart;
        $discounted_price = ($actual_price * (1 - $discount) * (1 - $customer_pricing)) - $amount_discount;

        $total_price += $actual_price;
        $total_discounted_price += $discounted_price;
    }

    $query = "INSERT INTO orders (estimateid, cashier, total_price, discounted_price, discount_percent, order_date, customerid, originalcustomerid, cash_amt, credit_amt, job_name, job_po, deliver_address,  deliver_city,  deliver_state,  deliver_zip, delivery_amt, deliver_fname, deliver_lname) 
              VALUES ('$estimateid', '$cashierid', '$total_price', '$total_discounted_price', '".($discount * 100)."', '$order_date', '$customerid', '$customerid', '$cash_amt', '$credit_amt' , '$job_name' , '$job_po' , '$deliver_address', '$deliver_city', '$deliver_state', '$deliver_zip' , '$delivery_amt' , '$deliver_fname' , '$deliver_lname')";

    if ($conn->query($query) === TRUE) {
        $orderid = $conn->insert_id;

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
            $unit_price = floatval($item['unit_price']);
            $estimate_width = !empty($item['estimate_width']) ? floatval($item['estimate_width']) : floatval($product_details['width']);
            $estimate_bend = floatval($item['estimate_bend']);
            $estimate_hem = floatval($item['estimate_hem']);
            $estimate_length = floatval($item['estimate_length']);
            $estimate_length_inch = floatval($item['estimate_length_inch']);
            $custom_color = $item['custom_color'];
            $custom_grade = $item['custom_grade'];
            $custom_gauge = $item['custom_gauge'];

            $amount_discount = !empty($item["amount_discount"]) ? $item["amount_discount"] : 0;

            $actual_price = $unit_price;
            $discounted_price = ($actual_price * (1 - $discount) * (1 - $customer_pricing)) - $amount_discount;
            $product_category = intval($product_details['product_category']);

            $curr_discount = intval(getCustomerDiscountProfile($customerid));
            $loyalty_discount = intval(getCustomerDiscountLoyalty($customerid));
            $used_discount = !empty($item['used_discount']) ? $item['used_discount'] : getCustomerDiscount($customerid);

            $stiff_stand_seam = !empty($item['stiff_stand_seam']) ? $item['stiff_stand_seam'] : '0';
            $stiff_board_batten = !empty($item['stiff_board_batten']) ? $item['stiff_board_batten'] : '0';
            $panel_type = !empty($item['panel_type']) ? $item['panel_type'] : '0';
            $custom_img_src = $item['custom_trim_src'];

            $values[] = "('$orderid', '$product_id', '$product_item', '$quantity_cart', '$estimate_width', '$estimate_bend', '$estimate_hem', '$estimate_length', '$estimate_length_inch', '$actual_price', '$discounted_price', '$product_category', '$custom_color' , '$custom_grade', '$curr_discount', '$loyalty_discount', '$used_discount', '$stiff_stand_seam', '$stiff_board_batten', '$panel_type')";
            
            $upd_inventory = "UPDATE inventory 
                            SET quantity = quantity - $quantity_cart, quantity_ttl = quantity_ttl - $quantity_cart 
                            WHERE Product_id = '$product_id' AND color_id = '$custom_color' 
                            LIMIT 1";

            if (!mysqli_query($conn, $upd_inventory)) {
                echo "Error: " . mysqli_error($conn);
            }

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
                            '$orderid', 
                            '2', 
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

            $is_pre_order = $item['is_pre_order'];

            if($is_pre_order == '1'){
                $pre_orders = [
                    'product_item' => $product_item,
                    'product_category' => ucwords(getProductCategoryName($product_details['product_category'])),
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
        
            $message = "
                <html>
                <head>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            line-height: 1.6;
                            color: #333;
                        }
                        .container {
                            padding: 20px;
                            border: 1px solid #e0e0e0;
                            background-color: #f9f9f9;
                            width: 80%;
                            margin: 0 auto;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        }
                        h2 {
                            color: #0056b3;
                            margin-bottom: 20px;
                        }
                        p {
                            margin: 5px 0;
                        }
                        ul {
                            padding-left: 20px;
                        }
                        li {
                            margin-bottom: 5px;
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>$subject</h2>
                        <ul>
                            $list_items
                        </ul>
                    </div>
                </body>
                </html>
            ";

            $response = sendEmail($admin_email, 'EKM', $subject, $message);
            if ($response['success'] == true) {
            } else {
                $response['error'] = "Failed to send Mail" . $conn->error;
            }
        }

        $query = "INSERT INTO order_product (orderid, productid, product_item, quantity, custom_width, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price, product_category, custom_color, custom_grade, current_customer_discount, current_loyalty_discount, used_discount, stiff_stand_seam, stiff_board_batten, panel_type) VALUES ";
        $query .= implode(', ', $values);

        if ($conn->query($query) === TRUE) {
            $query = "INSERT INTO order_estimate (order_estimate_id, type) VALUES ('$orderid','2')";
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

                $response['success'] = true;
                $response['order_id'] = $orderid;

                deleteCustomerCart();

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
    $customer_id = mysqli_real_escape_string($conn, $_SESSION['customer_id']);
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $quantity = floatval(mysqli_real_escape_string($conn, $_POST['quantity']));
    $length = floatval(mysqli_real_escape_string($conn, $_POST['length']));
    $feet = floor($length);
    $decimalFeet = $length - $feet;
    $inches = $decimalFeet * 12;
    $price = floatval(mysqli_real_escape_string($conn, $_POST['price']));
    $drawing_data = mysqli_real_escape_string($conn, $_POST['drawing_data']);
    $img_src = mysqli_real_escape_string($conn, $_POST['img_src']);
    $is_pre_order = mysqli_real_escape_string($conn, $_POST['is_pre_order'] ?? 0);
    $is_custom = mysqli_real_escape_string($conn, $_POST['is_custom'] ?? 0);

    $color = mysqli_real_escape_string($conn, $_POST['color'] ?? '');
    $grade = mysqli_real_escape_string($conn, $_POST['grade'] ?? '');
    $gauge = mysqli_real_escape_string($conn, $_POST['gauge'] ?? '');

    $query = "SELECT * FROM customer_cart WHERE customer_id = '$customer_id' AND product_id = '$id' AND line = '$line'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $update_query = "UPDATE customer_cart SET 
            quantity_cart = '$quantity', 
            estimate_length = '$feet', 
            estimate_length_inch = '$inches', 
            unit_price = '$price', 
            custom_img_src = '$img_src', 
            drawing_data = '$drawing_data', 
            custom_color = '$color', 
            custom_grade = '$grade', 
            custom_gauge = '$gauge', 
            is_pre_order = '$is_pre_order',
            is_custom = '$is_custom',
            WHERE customer_id = '$customer_id' AND product_id = '$id' AND line = '$line'";

        $update_result = mysqli_query($conn, $update_query);
    } else {
        $query = "SELECT * FROM product WHERE product_id = '$id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            $line_to_use = is_numeric($line) ? intval($line) : 1;

            $line_query = "SELECT MAX(line) AS max_line FROM customer_cart WHERE customer_id = '$customer_id' AND product_id = '$id'";
            $line_result = mysqli_query($conn, $line_query);
            $line_row = mysqli_fetch_assoc($line_result);
            if ($line_row['max_line'] >= $line_to_use) {
                $line_to_use = $line_row['max_line'] + 1;
            }

            $insert_query = "INSERT INTO customer_cart (
                customer_id, 
                product_id, 
                product_item, 
                unit_price, 
                line, 
                quantity_cart, 
                estimate_width, 
                estimate_length, 
                estimate_length_inch, 
                prod_usage, 
                custom_color, 
                weight, 
                supplier_id, 
                custom_grade, 
                custom_img_src,
                drawing_data,
                is_pre_order,
                is_custom
            ) VALUES (
                '$customer_id', 
                '$id', 
                '{$row['product_item']}', 
                '$price', 
                '$line_to_use', 
                '$quantity', 
                0, 
                '$feet', 
                '$inches', 
                0, 
                '$color', 
                0, 
                '{$row['supplier_id']}',
                '$grade', 
                '$img_src',
                '$drawing_data',
                '$is_pre_order',
                '$is_custom'
            )";            

            $insert_result = mysqli_query($conn, $insert_query);
        } else {
            echo json_encode(['error' => "Trim Product not available"]);
            exit;
        }
    }

    echo json_encode(['success' => true]);
}

if (isset($_POST['save_custom_length'])) {
    $customer_id = mysqli_real_escape_string($conn, $_SESSION['customer_id']);
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line'] ?? 1);
    $quantity = floatval(mysqli_real_escape_string($conn, $_POST['quantity']));
    $estimate_length = floatval(mysqli_real_escape_string($conn, $_POST['custom_length_feet']));
    $estimate_length_inch = floatval(mysqli_real_escape_string($conn, $_POST['custom_length_inch']));
    $price = floatval(mysqli_real_escape_string($conn, $_POST['price']));

    $query = "SELECT * FROM product WHERE product_id = '$id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $line_to_use = is_numeric($line) ? intval($line) : 1;
        $line_query = "SELECT MAX(line) as max_line FROM customer_cart 
                       WHERE customer_id = '$customer_id' AND product_id = '$id'";
        $line_result = mysqli_query($conn, $line_query);
        if ($line_result && $line_data = mysqli_fetch_assoc($line_result)) {
            if ($line_data['max_line'] !== null) {
                $line_to_use = $line_data['max_line'] + 1;
            }
        }

        $insert_query = "INSERT INTO customer_cart (
                customer_id, 
                product_id, 
                product_item, 
                unit_price, 
                line, 
                quantity_cart, 
                estimate_width, 
                estimate_length, 
                estimate_length_inch, 
                prod_usage, 
                custom_color, 
                weight, 
                supplier_id, 
                custom_grade, 
                custom_img_src,
                drawing_data
            ) VALUES (
                '$customer_id', 
                '$id', 
                '" . mysqli_real_escape_string($conn, $row['product_item']) . "', 
                '$price', 
                '$line_to_use', 
                '$quantity', 
                0, 
                '$estimate_length', 
                '$estimate_length_inch', 
                0, 
                '', 
                0, 
                '',
                '', 
                '',
                ''
            )";

        if (mysqli_query($conn, $insert_query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Insert failed: ' . mysqli_error($conn)]);
        }

    } else {
        echo json_encode(['error' => "Trim Product not available"]);
    }
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
    $customer_id = $_SESSION['customer_id'];
    $quantity = isset($_POST['quantity_product']) ? $_POST['quantity_product'] : [];
    $quantity = array_map(fn($qty) => empty($qty) ? 0 : $qty, $quantity);
    $lengthFeet = $_POST['length_feet'] ?? [];
    $lengthInch = $_POST['length_inch'] ?? [];
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $is_pre_order = mysqli_real_escape_string($conn, $_POST['is_pre_order'] ?? 0);
    $panel_type = mysqli_real_escape_string($conn, $_POST['panel_type']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);
    $panel_drip_stop = mysqli_real_escape_string($conn, $_POST['panel_drip_stop']);
    $stiff_board_batten = isset($_POST['stiff_board_batten']) ? mysqli_real_escape_string($conn, $_POST['stiff_board_batten']) : '';
    $stiff_stand_seam = isset($_POST['stiff_stand_seam']) ? mysqli_real_escape_string($conn, $_POST['stiff_stand_seam']) : '';
    $bend_product = floatval($_POST['bend_product'] ?? 0);
    $hem_product = floatval($_POST['hem_product'] ?? 0);
    $line = 1;

    foreach ($quantity as $index => $qty) {
        $length_feet = intval($lengthFeet[$index] ?? 0);
        $length_inch = intval($lengthInch[$index] ?? 0);

        $quantityInStock = getProductStockInStock($product_id);
        $totalStock = getProductStockTotal($product_id);

        $query = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $requestedQuantity = max($qty, 1);

            $check_sql = "SELECT * FROM customer_cart 
                          WHERE customer_id = '$customer_id' 
                            AND product_id = '$product_id' 
                            AND line = '$line'";
            $check_result = mysqli_query($conn, $check_sql);

            $basePrice = floatval($row['unit_price']);
            if ($row['sold_by_feet'] == '1') {
                $basePrice = $basePrice / floatval($row['length'] ?? 1);
            }

            $unit_price = calculateUnitPrice(
                $basePrice,
                $length_feet,
                $length_inch,
                $panel_type,
                $row['sold_by_feet'],
                $bend_product,
                $hem_product
            );

            if (mysqli_num_rows($check_result) > 0) {
                $existing = mysqli_fetch_assoc($check_result);
                $new_quantity = $existing['quantity_cart'] + min($requestedQuantity, $totalStock);

                $update_sql = "UPDATE customer_cart SET 
                                quantity_cart = '$new_quantity'
                               WHERE customer_id = '$customer_id'
                                 AND product_id = '$product_id'
                                 AND line = '$line'";
                mysqli_query($conn, $update_sql);
            } else {
                $product_name = getProductName($product_id);
                $weight = floatval($row['weight']);
                $width = floatval($row['width']);

                $insert_sql = "INSERT INTO customer_cart (
                    customer_id, product_id, product_item, supplier_id,
                    unit_price, line, quantity_ttl, quantity_in_stock, quantity_cart,
                    estimate_width, estimate_length, estimate_length_inch, usage,
                    custom_color, panel_type, weight, custom_grade, custom_gauge,
                    stiff_board_batten, stiff_stand_seam, is_pre_order
                ) VALUES (
                    '$customer_id', '$product_id', '".mysqli_real_escape_string($conn, $product_name)."', '".$row['supplier_id']."',
                    '$unit_price', '$line', '$totalStock', '$quantityInStock', '$requestedQuantity',
                    '$width', '$length_feet', '$length_inch', 0,
                    '".(!empty($color) ? $color : $row['color'])."', '$panel_type', '$weight', 
                    '".(!empty($grade) ? $grade : $row['grade'])."', '".(!empty($gauge) ? $gauge : $row['gauge'])."',
                    '$stiff_board_batten', '$stiff_stand_seam', '$is_pre_order'
                )";
                mysqli_query($conn, $insert_sql);
            }
        }

        $line++;
    }

    echo 'success';
    exit;
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
        $insert_query = "INSERT INTO job_names (
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

?>

