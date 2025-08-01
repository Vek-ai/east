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
            p.hidden = '0' and p.status = '1'
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

            $is_panel = $row_product['product_category'] == $panel_id ? true : false;
            $qty_input = !$is_panel 
                ? ' <div class="input-group input-group-sm">
                        <button class="btn btn-outline-primary btn-minus" type="button" data-id="' . $row_product['product_id'] . '">-</button>
                        <input class="form-control p-1 text-center" type="number" id="qty' . $row_product['product_id'] . '" value="1" min="1">
                        <button class="btn btn-outline-primary btn-plus" type="button" data-id="' . $row_product['product_id'] . '">+</button>
                    </div>'
                : '';

            $btn_id = $is_panel ? 'add-to-cart-btn' : 'add-to-cart-non-panel';

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
    
    //echo $tableHTML;
    echo $tableHTML;
}

if (isset($_POST['modifyquantity']) || isset($_POST['duplicate_product'])) {
    $customer_id = $_SESSION['customer_id'];
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $line = isset($_POST['line']) ? (int)$_POST['line'] : 1;
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

    $quantityInStock = getProductStockInStock($product_id);
    $totalQuantity = getProductStockTotal($product_id);
    $totalStock = $totalQuantity;

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
        $basePrice = floatval($row['unit_price']);
        if ($row['sold_by_feet'] == '1') {
            $basePrice = $basePrice / floatval($row['length'] ?? 1);
        }

        $panelType = '';
        $soldByFeet = $row['sold_by_feet'];
        $bends = 0;
        $hems = 0;

        $unitPrice = calculateUnitPrice($basePrice, $estimate_length, $estimate_length_inch, $panelType, $soldByFeet, $bends, $hems);

        $check = mysqli_query($conn, "SELECT * FROM customer_cart WHERE customer_id='$customer_id' AND product_id='$product_id' AND line='$line'");
        if (isset($_POST['duplicate_product'])) {
            $newLine = $line + 1;
            while (mysqli_num_rows(mysqli_query($conn, "SELECT 1 FROM customer_cart WHERE customer_id='$customer_id' AND product_id='$product_id' AND line='$newLine'")) > 0) {
                $newLine++;
            }
            $line = $newLine;
        }

        if (mysqli_num_rows($check) > 0 && !isset($_POST['duplicate_product'])) {
            if (isset($_POST['setquantity'])) {
                $newQty = max($qty, 1);
                mysqli_query($conn, "UPDATE customer_cart SET quantity_cart = '$newQty' WHERE customer_id='$customer_id' AND product_id='$product_id' AND line='$line'");
                echo $newQty;
            } elseif (isset($_POST['addquantity'])) {
                mysqli_query($conn, "UPDATE customer_cart SET quantity_cart = quantity_cart + '$qty' WHERE customer_id='$customer_id' AND product_id='$product_id' AND line='$line'");
                $res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT quantity_cart FROM customer_cart WHERE customer_id='$customer_id' AND product_id='$product_id' AND line='$line'"));
                echo $res['quantity_cart'];
            } elseif (isset($_POST['deductquantity'])) {
                $res = mysqli_fetch_assoc($check);
                if ($res['quantity_cart'] <= 1) {
                    mysqli_query($conn, "DELETE FROM customer_cart WHERE customer_id='$customer_id' AND product_id='$product_id' AND line='$line'");
                    echo 'removed';
                } else {
                    mysqli_query($conn, "UPDATE customer_cart SET quantity_cart = quantity_cart - 1 WHERE customer_id='$customer_id' AND product_id='$product_id' AND line='$line'");
                    echo $res['quantity_cart'] - 1;
                }
            }
        } else {
            mysqli_query($conn, "INSERT INTO customer_cart (
                customer_id, product_id, line, quantity_cart, unit_price,
                prod_usage, custom_color, custom_grade, estimate_width, estimate_length,
                estimate_length_inch, weight, supplier_id
            ) VALUES (
                '$customer_id', '$product_id', '$line', '$item_quantity', '$unitPrice',
                0, '{$row['color']}', '" . intval($row['grade']) . "', '{$row['width']}',
                '$estimate_length', '$estimate_length_inch', '$weight', '{$row['supplier_id']}'
            )");
        }
    }
}

if (isset($_POST['deleteitem'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id_del']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $customer_id = $_SESSION['customer_id'];

    $sql = "DELETE FROM customer_cart WHERE customer_id = '$customer_id' AND product_id = '$product_id' AND line = '$line'";
    $result = $conn->query($sql);

    if ($result) {
        echo "Item deleted successfully.";
    } else {
        echo "Failed to delete item.";
    }
}

if (isset($_POST['set_usage'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $usage = mysqli_real_escape_string($conn, $_POST['usage']);
    $customer_id = $_SESSION['customer_id'];

    $sql = "UPDATE customer_cart SET prod_usage = '$usage' 
            WHERE customer_id = '$customer_id' AND product_id = '$product_id' AND line = '$line'";
    $conn->query($sql);
    echo "Updated usage for product $product_id, line $line";
}

if (isset($_POST['set_estimate_hem'])) {
    $hem = mysqli_real_escape_string($conn, $_POST['hem']);
    $sql = "UPDATE customer_cart SET estimate_hem = '$hem' 
            WHERE customer_id = '$customer_id' AND product_id = '$product_id' AND line = '$line'";
    $conn->query($sql);
    echo "Updated hem for product $product_id, line $line";
}

if (isset($_POST['set_estimate_bend'])) {
    $bend = mysqli_real_escape_string($conn, $_POST['bend']);
    $sql = "UPDATE customer_cart SET estimate_bend = '$bend' 
            WHERE customer_id = '$customer_id' AND product_id = '$product_id' AND line = '$line'";
    $conn->query($sql);
    echo "Updated bend for product $product_id, line $line";
}

if (isset($_POST['set_estimate_height'])) {
    $height = mysqli_real_escape_string($conn, $_POST['height']);
    $sql = "UPDATE customer_cart SET estimate_height = '$height' 
            WHERE customer_id = '$customer_id' AND product_id = '$product_id' AND line = '$line'";
    $conn->query($sql);
    echo "Updated height for product $product_id, line $line";
}

if (isset($_POST['set_estimate_width'])) {
    $width = mysqli_real_escape_string($conn, $_POST['width']);
    $sql = "UPDATE customer_cart SET estimate_width = '$width' 
            WHERE customer_id = '$customer_id' AND product_id = '$product_id' AND line = '$line'";
    $conn->query($sql);
    echo "Updated width for product $product_id, line $line";
}

if (isset($_POST['set_estimate_length'])) {
    $length = mysqli_real_escape_string($conn, $_POST['length']);
    $sql = "UPDATE customer_cart SET estimate_length = '$length' 
            WHERE customer_id = '$customer_id' AND product_id = '$product_id' AND line = '$line'";
    $conn->query($sql);
    echo "Updated length for product $product_id, line $line";
}

if (isset($_POST['set_estimate_length_inch'])) {
    $length_inch = mysqli_real_escape_string($conn, $_POST['length_inch']);
    $sql = "UPDATE customer_cart SET estimate_length_inch = '$length_inch' 
            WHERE customer_id = '$customer_id' AND product_id = '$product_id' AND line = '$line'";
    $conn->query($sql);
    echo "Updated length (inch) for product $product_id, line $line";
}

if (isset($_POST['set_color'])) {
    $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
    $sql = "UPDATE customer_cart SET custom_color = '$color_id' 
            WHERE customer_id = '$customer_id' AND product_id = '$product_id' AND line = '$line'";
    $conn->query($sql);
    echo "Updated color for product $product_id, line $line";
}

if (isset($_POST['set_grade'])) {
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $sql = "UPDATE customer_cart SET custom_grade = '$grade' 
            WHERE customer_id = '$customer_id' AND product_id = '$product_id' AND line = '$line'";
    $conn->query($sql);
    echo "Updated grade for product $product_id, line $line";
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

    $customerid = intval($_SESSION['customer_id']);
    
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
    $customer_name = $customer_details['customer_first_name'] .' ' .$customer_details['customer_last_name'];
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
    $order_from = 2; //customer

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

    $query = "INSERT INTO estimates (total_price, discounted_price, discount_percent, estimated_date, customerid, originalcustomerid, cash_amt, credit_amt, job_name, job_po, deliver_address,  deliver_city,  deliver_state,  deliver_zip, delivery_amt, deliver_fname, deliver_lname, order_from) 
              VALUES ('$total_actual_price', '$total_discounted_price', '".($discount * 100)."', '$estimated_date', '$customerid', '$customerid', '$cash_amt', '$credit_amt' , '$job_name' , '$job_po' , '$deliver_address', '$deliver_city', '$deliver_state', '$deliver_zip' , '$delivery_amt' , '$deliver_fname' , '$deliver_lname' , '$order_from')";

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

            $customer_pricing = getPricingCategory($product_details['product_category'], $customer_details['customer_pricing']) / 100;

            $unit_price = floatval($item['unit_price']);
            $estimate_width = !empty($item['estimate_width']) ? floatval($item['estimate_width']) : $product_details['width'];
            $estimate_bend = floatval($item['estimate_bend']);
            $estimate_hem = floatval($item['estimate_hem']);
            $estimate_length = floatval($item['estimate_length']);
            $estimate_length_inch = floatval($item['estimate_length_inch']);
            $custom_color = $item['custom_color'];
            $custom_grade = $item['custom_grade'];
            $amount_discount = !empty($item["amount_discount"]) ? $item["amount_discount"] : 0;

            $actual_price = $unit_price;
            $discounted_price = ($actual_price * (1 - $discount) * (1 - $customer_pricing)) - $amount_discount;

            $curr_discount = intval(getCustomerDiscountProfile($customerid));
            $loyalty_discount = intval(getCustomerDiscountLoyalty($customerid));
            $used_discount = !empty($item['used_discount']) ? $item['used_discount'] : getCustomerDiscount($customerid);

            $stiff_stand_seam = !empty($item['stiff_stand_seam']) ? $item['stiff_stand_seam'] : '0';
            $stiff_board_batten = !empty($item['stiff_board_batten']) ? $item['stiff_board_batten'] : '0';
            $panel_type = !empty($item['panel_type']) ? $item['panel_type'] : '0';

            $values[] = "('$estimateid', '$product_id', '$quantity_cart', '$estimate_width', '$estimate_bend', '$estimate_hem', '$estimate_length', '$estimate_length_inch', '$actual_price', '$discounted_price', '$custom_color', '$custom_grade', '$curr_discount', '$loyalty_discount', '$used_discount', '$stiff_stand_seam', '$stiff_board_batten', '$panel_type')";

            if ($product_details['product_origin'] == 2) {
                $query = "INSERT INTO work_order_product (
                            work_order_id, 
                            type,
                            productid, 
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
                            panel_type
                        ) 
                        VALUES (
                            '$estimateid', 
                            '1',
                            '$product_id', 
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
                            '$panel_type'
                        )";
            
                if ($conn->query($query) === TRUE) {
                } else {
                    die("Error: " . $conn->error);
                }
            }
        }

        $query = "INSERT INTO estimate_prod (estimateid, product_id, quantity, custom_width, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price, custom_color, custom_grade, current_customer_discount, current_loyalty_discount, used_discount, stiff_stand_seam, stiff_board_batten, panel_type) VALUES ";
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

                $response['success'] = true;
                $response['message'] = "Estimate and products successfully saved.";
                $response['estimate_id'] = $estimateid;

                $subject = "$customer_name has sent an Estimate request";
                $emailResult = $emailer->sendEstimateNotif($admin_email, $subject);

                if (!$emailResult['success']) {
                    $response['message'] = $emailResult['error'];
                }
                
                $sql = "DELETE FROM customer_cart WHERE customer_id = '$customerid'";
                if (!$conn->query($sql)) {
                    $response['message'] = "Error clearing cart: " . $conn->error;
                }
                
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

    $estimateid = intval($_SESSION['estimateid']);
    $customerid = intval($_SESSION['customer_id']);
    $cashierid = intval($_SESSION['userid']);

    $cart = getCartDataByCustomerId($customerid);

    if (empty($cart)) {
        $response['message'] = "Cart is empty.";
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $order_date = date('Y-m-d H:i:s');
    $discount_default = floatval(getCustomerDiscount($customerid)) / 100;

    $customer_details = getCustomerDetails($customerid);
    $customer_name = $customer_details['customer_first_name'] .' ' .$customer_details['customer_last_name'];
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
    $order_from = 2; //customer

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

    $query = "INSERT INTO orders (estimateid, cashier, total_price, discounted_price, discount_percent, order_date, customerid, originalcustomerid, cash_amt, credit_amt, job_name, job_po, deliver_address,  deliver_city,  deliver_state,  deliver_zip, delivery_amt, deliver_fname, deliver_lname, order_from) 
              VALUES ('$estimateid', '$cashierid', '$total_price', '$total_discounted_price', '".($discount * 100)."', '$order_date', '$customerid', '$customerid', '$cash_amt', '$credit_amt' , '$job_name' , '$job_po' , '$deliver_address', '$deliver_city', '$deliver_state', '$deliver_zip' , '$delivery_amt' , '$deliver_fname' , '$deliver_lname' , '$order_from')";

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

            $values[] = "('$orderid', '$product_id', '$quantity_cart', '$estimate_width', '$estimate_bend', '$estimate_hem', '$estimate_length', '$estimate_length_inch', '$actual_price', '$discounted_price', '$product_category', '$custom_color' , '$custom_grade', '$curr_discount', '$loyalty_discount', '$used_discount', '$stiff_stand_seam', '$stiff_board_batten', '$panel_type')";
            
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
                            panel_type
                        ) 
                        VALUES (
                            '$orderid', 
                            '2', 
                            '$product_id', 
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
                            '$panel_type'
                        )";
            
                if ($conn->query($query) === TRUE) {
                } else {
                    die("Error: " . $conn->error);
                }
            }
            
        }

        $query = "INSERT INTO order_product (orderid, productid, quantity, custom_width, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price, product_category, custom_color, custom_grade, current_customer_discount, current_loyalty_discount, used_discount, stiff_stand_seam, stiff_board_batten, panel_type) VALUES ";
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
                $response['message'] = "Successfully sent Order request to EKM.";
                $response['order_id'] = $orderid;

                $subject = "$customer_name has sent an Order request";
                $emailResult = $emailer->sendEstimateNotif($admin_email, $subject);
                if (!$emailResult['success']) {
                    $response['message'] = $emailResult['error'];
                }

                $sql = "DELETE FROM customer_cart WHERE customer_id = '$customerid'";
                if (!$conn->query($sql)) {
                    $response['error'] = "Error clearing cart: " . $conn->error;
                }

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
    $customer_id = $_SESSION['customer_id'];

    $sql = "DELETE FROM customer_cart WHERE customer_id = '$customer_id'";
    if ($conn->query($sql)) {
        echo "Cart cleared successfully.";
    } else {
        echo "Error clearing cart: " . $conn->error;
    }
}

if (isset($_POST['add_to_cart'])) {
    $customer_id = $_SESSION['customer_id'];
    $quantity = isset($_POST['quantity_product']) ? $_POST['quantity_product'] : [];
    $quantity = array_map(function($qty) {
        return empty($qty) ? 0 : $qty;
    }, $quantity);
    $lengthFeet = $_POST['length_feet'] ?? [];
    $lengthInch = $_POST['length_inch'] ?? [];
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $panel_type = mysqli_real_escape_string($conn, $_POST['panel_type']);
    $panel_drip_stop = mysqli_real_escape_string($conn, $_POST['panel_drip_stop']);
    $stiff_board_batten = $_POST['stiff_board_batten'] ?? '';
    $stiff_stand_seam = $_POST['stiff_stand_seam'] ?? '';
    $bend_product = isset($_POST['bend_product']) ? floatval($_POST['bend_product']) : 0;
    $hem_product = isset($_POST['hem_product']) ? floatval($_POST['hem_product']) : 0;

    $line = 1;
    foreach ($quantity as $index => $qty) {
        $length_feet = intval($lengthFeet[$index] ?? 0);
        $length_inch = intval($lengthInch[$index] ?? 0);
        $quantityInStock = getProductStockInStock($product_id);
        $totalQuantity = getProductStockTotal($product_id);
        $totalStock = $totalQuantity;

        $query = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $item_quantity = max(1, $qty);

            $basePrice = floatval($row['unit_price']);
            if ($row['sold_by_feet'] == '1') {
                $basePrice = $basePrice / floatval($row['length'] ?: 1);
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

            $product_item = getProductName($row['product_id']);
            $supplier_id = $row['supplier_id'];
            $weight = floatval($row['weight']);
            $estimate_width = $row['width'];
            $custom_color = $row['color'];
            $custom_grade = intval($row['grade']);

            $check_sql = "SELECT id, quantity_cart FROM customer_cart 
                          WHERE customer_id = '$customer_id' AND product_id = '$product_id' AND line = '$line'";
            $check_result = mysqli_query($conn, $check_sql);

            if (mysqli_num_rows($check_result) > 0) {
                $existing = mysqli_fetch_assoc($check_result);
                $new_qty = $existing['quantity_cart'] + min($item_quantity, $totalStock);

                $update_sql = "UPDATE customer_cart SET quantity_cart = '$new_qty' 
                               WHERE id = '{$existing['id']}'";
                mysqli_query($conn, $update_sql);
            } else {
                $insert_sql = "INSERT INTO customer_cart (
                    customer_id, product_id, product_item, supplier_id, unit_price,
                    line, quantity_ttl, quantity_in_stock, quantity_cart,
                    estimate_width, estimate_length, estimate_length_inch,
                    prod_usage, custom_color, panel_type, weight, custom_grade,
                    stiff_board_batten, stiff_stand_seam
                ) VALUES (
                    '$customer_id', '$product_id', '".mysqli_real_escape_string($conn, $product_item)."', '$supplier_id', '$unit_price',
                    '$line', '$totalStock', '$quantityInStock', '$item_quantity',
                    '$estimate_width', '$length_feet', '$length_inch',
                    0, '".mysqli_real_escape_string($conn, $custom_color)."', '".mysqli_real_escape_string($conn, $panel_type)."',
                    '$weight', '$custom_grade', 
                    '".mysqli_real_escape_string($conn, $stiff_board_batten)."', 
                    '".mysqli_real_escape_string($conn, $stiff_stand_seam)."'
                )";
                mysqli_query($conn, $insert_sql);
            }
        }
        $line++;
    }
    echo 'success';
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
?>
