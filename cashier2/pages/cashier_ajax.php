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
            $basePrice = floatval($row['unit_price']);
            if($row['sold_by_feet'] == '1'){
                $basePrice = $basePrice / floatval($row['length'] ?? 1);
            }

            $panelType = '';
            $soldByFeet = $row['sold_by_feet'];
            $bends = 0;
            $hems = 0;
        
            $unitPrice = calculateUnitPrice(
                            $basePrice, 
                            $estimate_length, 
                            $estimate_length_inch, 
                            $panelType, 
                            $soldByFeet, 
                            $bends, 
                            $hems
                        );
        
            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => getProductName($row['product_id']),
                'supplier_id' => $row['supplier_id'],
                'unit_price' => $unitPrice,
                'line' => $newLine,
                'quantity_ttl' => $totalStock,
                'quantity_in_stock' => $quantityInStock,
                'quantity_cart' => $item_quantity,
                'estimate_width' => $row['width'],
                'estimate_length' => $estimate_length,
                'estimate_length_inch' => $estimate_length_inch,
                'usage' => 0,
                'custom_color' => $row['color'],
                'weight' => $weight,
                'custom_grade' => intval($row['grade'])
            );
        
            $_SESSION["cart"][] = $item_array;
        }        
    } elseif ($key !== false) {
        if (isset($_POST['setquantity'])) {
            $requestedQuantity = max($qty, 1);
            $_SESSION["cart"][$key]['quantity_cart'] = $requestedQuantity;
            echo $_SESSION["cart"][$key]['quantity_cart'];
        } elseif (isset($_POST['addquantity'])) {
            $newQuantity = $_SESSION["cart"][$key]['quantity_cart'] + $qty;
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
            $basePrice = floatval($row['unit_price']);
            if($row['sold_by_feet'] == '1'){
                $basePrice = $basePrice / floatval($row['length'] ?? 1);
            }
            $panelType = '';
            $soldByFeet = $row['sold_by_feet'];
            $bends = 0;
            $hems = 0;

            $unitPrice = calculateUnitPrice($basePrice, $estimate_length, $estimate_length_inch, $panelType, $soldByFeet, $bends, $hems);

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => getProductName($row['product_id']),
                'unit_price' => $unitPrice,
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
                'supplier_id' => $row['supplier_id'],
                'custom_grade' => intval($row['grade'])
            );

            $_SESSION["cart"][] = $item_array;
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
    $grade = isset($_REQUEST['grade']) ? mysqli_real_escape_string($conn, $_REQUEST['grade']) : '';
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
            COALESCE(SUM(i.quantity_ttl), 0) AS total_quantity,
            pt.profile_type as profile_type_name,
            pg.product_grade as product_grade_name
        FROM 
            product AS p
        LEFT JOIN 
            inventory AS i ON p.product_id = i.product_id
        LEFT JOIN 
            profile_type AS pt ON p.profile = pt.profile_type_id
        LEFT JOIN 
            product_grade AS pg ON p.grade = pg.product_grade_id
        WHERE 
            p.hidden = '0' and p.status = '1'
    ";

    if (!empty($searchQuery)) {
        $query_product .= " AND (p.product_item LIKE '%$searchQuery%' OR p.description LIKE '%$searchQuery%')";
    }

    if (!empty($color_id)) {
        $query_product .= " AND i.color_id = '$color_id'";
    }

    if (!empty($grade)) {
        $query_product .= " AND pg.product_grade = '$grade'";
    }

    if (!empty($gauge_id)) {
        $query_product .= " AND p.gauge = '$gauge_id'";
    }

    if (!empty($type_id)) {
        $query_product .= " AND p.product_type = '$type_id'";
    }

    if (!empty($profile_id)) {
        $query_product .= " AND pt.profile_type = '$profile_id'";
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

            $qty_input = !$is_panel  && !$is_custom_truss && !$is_special_trim && !$is_trim && !$is_custom_length
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

if (isset($_POST['set_estimate_data'])) {
    $estimateId = intval($_POST['editestimate']);
    $estimate_details = getEstimateDetails($estimateId);

    $_SESSION['customer_id'] = $estimate_details['customerid'];

    unset($_SESSION['cart']);
    $_SESSION['cart'] = [];

    $query = "SELECT * FROM estimate_prod WHERE estimateid = '$estimateId'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $item_array = array(
                'estimate_prod_id' => $row['id'],
                'estimateid' => $row['estimateid'],
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'supplier_id' => 0,
                'unit_price' => floatval($row['actual_price']),
                'line' => 1,
                'quantity_ttl' => 0,
                'quantity_in_stock' => 0,
                'quantity_cart' => floatval($row['quantity']),
                'estimate_width' => $row['custom_width'],
                'estimate_length' => $row['custom_length'],
                'estimate_length_inch' => $row['custom_length2'],
                'usage' => $row['usageid'],
                'custom_color' => $row['custom_color'],
                'panel_type' => $row['panel_type'],
                'weight' => 0,
                'custom_grade' => $row['custom_grade'],
                'custom_gauge' => 0,
                'stiff_board_batten' => $row['stiff_board_batten'],
                'stiff_stand_seam' => $row['stiff_stand_seam'],
                'is_pre_order' => 0
            );

            $_SESSION['cart'][] = $item_array;

            echo $estimateId;
        }
    }
}

if (isset($_POST['edit_estimate'])) {
    $estimate_id = intval($_POST['edit_estimate']);

    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $estimate_prod_id = isset($item['estimate_prod_id']) ? intval($item['estimate_prod_id']) : 0;

            $product_id = intval($item['product_id']);
            $product_item = mysqli_real_escape_string($conn, $item['product_item']);
            $custom_color = intval($item['custom_color']);
            $custom_grade = intval($item['custom_grade']);
            $custom_gauge = intval($item['custom_gauge']);
            $quantity = intval($item['quantity_cart']);
            $custom_width = mysqli_real_escape_string($conn, $item['estimate_width']);
            $custom_length = mysqli_real_escape_string($conn, $item['estimate_length']);
            $custom_length2 = mysqli_real_escape_string($conn, $item['estimate_length_inch']);
            $custom_bend = isset($item['estimate_bend']) ? mysqli_real_escape_string($conn, $item['estimate_bend']) : '';
            $custom_hem = isset($item['estimate_hem']) ? mysqli_real_escape_string($conn, $item['estimate_hem']) : '';
            $usageid = intval($item['usage']);
            $panel_type = intval($item['panel_type']);
            $stiff_stand_seam = intval($item['stiff_stand_seam']);
            $stiff_board_batten = intval($item['stiff_board_batten']);
            $actual_price = floatval($item['unit_price']);
            $discounted_price = isset($item['discounted_price']) ? floatval($item['discounted_price']) : $actual_price;

            if ($estimate_prod_id > 0) {
                $query = "
                    UPDATE estimate_prod SET 
                        custom_color = '$custom_color',
                        custom_grade = '$custom_grade',
                        custom_width = '$custom_width',
                        custom_bend = '$custom_bend',
                        custom_hem = '$custom_hem',
                        custom_length = '$custom_length',
                        custom_length2 = '$custom_length2',
                        quantity = '$quantity',
                        actual_price = '$actual_price',
                        discounted_price = '$discounted_price',
                        usageid = '$usageid',
                        panel_type = '$panel_type',
                        stiff_stand_seam = '$stiff_stand_seam',
                        stiff_board_batten = '$stiff_board_batten'
                    WHERE id = $estimate_prod_id
                ";
            } else {
                $query = "
                    INSERT INTO estimate_prod (
                        estimateid, product_id, product_item, custom_color, custom_grade, 
                        custom_width, custom_bend, custom_hem, custom_length, custom_length2,
                        quantity, actual_price, discounted_price, usageid,
                        panel_type, stiff_stand_seam, stiff_board_batten
                    ) VALUES (
                        '$estimate_id', '$product_id', '$product_item', '$custom_color', '$custom_grade',
                        '$custom_width', '$custom_bend', '$custom_hem', '$custom_length', '$custom_length2',
                        '$quantity', '$actual_price', '$discounted_price', '$usageid',
                        '$panel_type', '$stiff_stand_seam', '$stiff_board_batten'
                    )
                ";
            }

            mysqli_query($conn, $query);
        }

        unset($_SESSION["cart"]);
        unset($_SESSION["customer_id"]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => "Estimate id: " .$estimate_id]);
    }
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

    $cashierid = intval($_SESSION['userid']);

    $customerid = intval($_SESSION['customer_id']);
    $cart = $_SESSION['cart'];
    $estimated_date = date('Y-m-d H:i:s');
    $discount_default = floatval(getCustomerDiscount($customerid)) / 100;

    $customer_details = getCustomerDetails($customerid);
    $credit_limit = number_format($customer_details['credit_limit'] ?? 0,2);
    $credit_total = number_format(getCustomerCreditTotal($customerid),2);

    $total_actual_price = 0;
    $total_discounted_price = 0;
    $pre_orders = array();
    foreach ($cart as $item) {
        $discount = 0;
        if (isset($item['used_discount'])) {
            $discount = $item['used_discount'] / 100;
        } else {
            $discount = $discount_default;
        }

        $product_id = intval($item['product_id']);
        $product_details = getProductDetails($product_id);
        $customer_pricing = getPricingCategory($product_details['product_category'], $customer_details['customer_pricing']) / 100;

        $unit_price = floatval($item['unit_price']);
        $quantity_cart = intval($item['quantity_cart']);
        $estimate_length = floatval($item['estimate_length']);
        $estimate_length_inch = floatval($item['estimate_length_inch']);
        $amount_discount = !empty($item["amount_discount"]) ? floatval($item["amount_discount"]) : 0;

        $total_length = $estimate_length + ($estimate_length_inch / 12);

        $actual_price = $unit_price * $total_length;
        $discounted_price = ($actual_price * (1 - $discount) * (1 - $customer_pricing)) - $amount_discount;

        $total_actual_price += $actual_price;
        $total_discounted_price += $discounted_price;
    }

    $query = "INSERT INTO estimates (total_price, cashier, discounted_price, discount_percent, estimated_date, customerid, originalcustomerid, cash_amt, credit_amt, job_name, job_po, deliver_address,  deliver_city,  deliver_state,  deliver_zip, delivery_amt, deliver_fname, deliver_lname) 
              VALUES ('$total_actual_price', '$cashierid', '$total_discounted_price', '".($discount * 100)."', '$estimated_date', '$customerid', '$customerid', '$cash_amt', '$credit_amt' , '$job_name' , '$job_po' , '$deliver_address', '$deliver_city', '$deliver_state', '$deliver_zip' , '$delivery_amt' , '$deliver_fname' , '$deliver_lname')";

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
            
            $custom_color = $item['custom_color'];
            $custom_grade = $item['custom_grade'];
            $custom_gauge = $item['custom_gauge'];
            $is_pre_order = $item['is_pre_order'];
            $amount_discount = !empty($item["amount_discount"]) ? $item["amount_discount"] : 0;

            $estimate_length = floatval($item['estimate_length']);
            $estimate_length_inch = floatval($item['estimate_length_inch']);
            $total_length = $estimate_length + ($estimate_length_inch / 12);

            $actual_price = $unit_price * $total_length;
            $discounted_price = ($actual_price * (1 - $discount) * (1 - $customer_pricing)) - $amount_discount;

            $curr_discount = intval(getCustomerDiscountProfile($customerid));
            $loyalty_discount = intval(getCustomerDiscountLoyalty($customerid));
            $used_discount = !empty($item['used_discount']) ? $item['used_discount'] : getCustomerDiscount($customerid);

            $stiff_stand_seam = !empty($item['stiff_stand_seam']) ? $item['stiff_stand_seam'] : '0';
            $stiff_board_batten = !empty($item['stiff_board_batten']) ? $item['stiff_board_batten'] : '0';
            $panel_type = !empty($item['panel_type']) ? $item['panel_type'] : '0';
            $custom_img_src = $item['custom_trim_src'];

            $values[] = "('$estimateid', '$product_id', '$product_item', '$quantity_cart', '$estimate_width', '$estimate_bend', '$estimate_hem', '$estimate_length', '$estimate_length_inch', '$actual_price', '$discounted_price', '$custom_color', '$custom_grade', '$curr_discount', '$loyalty_discount', '$used_discount', '$stiff_stand_seam', '$stiff_board_batten', '$panel_type', '$custom_img_src')";

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

        $query = "INSERT INTO estimate_prod (estimateid, product_id, product_item, quantity, custom_width, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price, custom_color, custom_grade, current_customer_discount, current_loyalty_discount, used_discount, stiff_stand_seam, stiff_board_batten, panel_type, custom_img_src) VALUES ";
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

        unset($_SESSION['customer_id']);
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
                    'product_item' => getProductName($product_details['product_id']),
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
    $pay_type = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? '');
    $customer_tax = mysqli_real_escape_string($conn, $_POST['customer_tax'] ?? '');

    if (!isset($_SESSION['customer_id']) || empty($_SESSION['cart'])) {
        $response['error'] = "Customer ID or cart is not set.";
        echo json_encode($response);
        exit;
    }

    $applyStoreCredit = floatval($_POST['applyStoreCredit']);
    $applyJobDeposit = floatval($_POST['applyJobDeposit']);

    $estimateid = intval($_SESSION['estimateid']);
    $customerid = intval($_SESSION['customer_id']);
    $cashierid = intval($_SESSION['userid']);
    $cart = $_SESSION['cart'];
    $order_date = date('Y-m-d H:i:s');
    $discount_default = floatval(getCustomerDiscount($customerid)) / 100;

    $customer_details = getCustomerDetails($customerid);

    if(!empty($customer_tax)){
        $discount_default = floatval(getCustomerTaxById($customer_tax)) / 100;
        $tax_status = $customer_tax;
    }else{
        $discount_default = floatval(getCustomerDiscount($customerid)) / 100;
        $tax_status = $customer_details['tax_status'];
    }

    $tax_exempt_number = $customer_details['tax_exempt_number'];
    $credit_limit = number_format($customer_details['credit_limit'] ?? 0,2);
    $credit_total = number_format(getCustomerCreditTotal($customerid),2);

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
        $amount_discount = !empty($item["amount_discount"]) ? floatval($item["amount_discount"]) : 0;

        $total_length = ($estimate_length + ($estimate_length_inch / 12));
        $actual_price = $unit_price * $quantity_cart * $total_length;

        $discounted_price = ($actual_price * (1 - $discount) * (1 - $customer_pricing)) - $amount_discount;
        $discounted_price = max(0, $discounted_price);

        $total_price += $actual_price;
        $total_discounted_price += $discounted_price;

        $approval_products[] = [
            'productid' => $product_id,
            'product_item' => $item['product_item'],
            'quantity' => $quantity_cart,
            'custom_color' => $item['custom_color'] ?? null,
            'custom_grade' => $item['custom_grade'] ?? null,
            'custom_width' => $item['custom_width'],
            'custom_height' => $item['custom_height'] ?? null,
            'custom_bend' => $item['custom_bend'] ?? null,
            'custom_hem' => $item['custom_hem'] ?? null,
            'custom_length' => $item['custom_length'] ?? null,
            'custom_length2' => $item['custom_length2'] ?? null,
            'actual_price' => $actual_price,
            'discounted_price' => $discounted_price,
            'product_category' => $product_details['product_category'],
            'usageid' => $item['usageid'] ?? 0,
            'current_customer_discount' => $item['current_customer_discount'] ?? 0,
            'current_loyalty_discount' => $item['current_loyalty_discount'] ?? 0,
            'used_discount' => $item['used_discount'] ?? 0,
            'stiff_stand_seam' => $item['stiff_stand_seam'],
            'stiff_board_batten' => $item['stiff_board_batten'],
            'panel_type' => $item['panel_type'],
        ];
    }

    $original_cash_amt = floatval($cash_amt);
    $store_credit = floatval($customer_details['store_credit']);
    $charge_net_30 = floatval($customer_details['charge_net_30']);

    if ($pay_type == 'net30' && $charge_net_30 < $total_discounted_price) {
        $job_po = mysqli_real_escape_string($conn, $job_po ?? '');
        $job_name = mysqli_real_escape_string($conn, $job_name ?? '');
        $deliver_address = mysqli_real_escape_string($conn, $deliver_address ?? '');
        $deliver_city = mysqli_real_escape_string($conn, $deliver_city ?? '');
        $deliver_state = mysqli_real_escape_string($conn, $deliver_state ?? '');
        $deliver_zip = mysqli_real_escape_string($conn, $deliver_zip ?? '');
        $delivery_amt = mysqli_real_escape_string($conn, $delivery_amt ?? '');
        $deliver_fname = mysqli_real_escape_string($conn, $deliver_fname ?? '');
        $deliver_lname = mysqli_real_escape_string($conn, $deliver_lname ?? '');

        $insert_approval = "
            INSERT INTO approval (
                status, cashier, total_price, discounted_price, discount_percent,
                cash_amt, disc_amount, submitted_date, customerid, originalcustomerid,
                job_name, job_po, deliver_address, deliver_city, deliver_state,
                deliver_zip, delivery_amt, deliver_fname, deliver_lname, type_approval, pay_type
            ) VALUES (
                1, '$cashierid', '$total_price', '$total_discounted_price', '$discount_percent',
                '$cash_amt', '$amount_discount', NOW(), '$customerid', '$customerid',
                '$job_name', '$job_po', '$deliver_address', '$deliver_city', '$deliver_state',
                '$deliver_zip', '$delivery_amt', '$deliver_fname', '$deliver_lname', 2 , '$pay_type'
            )
        ";

        if (!mysqli_query($conn, $insert_approval)) {
            die(json_encode(['success' => false, 'message' => 'Approval insert failed: ' . mysqli_error($conn)]));
        }

        $approval_id = mysqli_insert_id($conn);

        foreach ($approval_products as $p) {
            $sql = "
                INSERT INTO approval_product (
                    approval_id, productid, product_item, status, quantity, custom_color,
                    custom_grade, custom_width, custom_height, custom_bend, custom_hem,
                    custom_length, custom_length2, actual_price, discounted_price,
                    product_category, usageid, current_customer_discount, current_loyalty_discount,
                    used_discount, stiff_stand_seam, stiff_board_batten, panel_type
                ) VALUES (
                    '$approval_id', '{$p['productid']}', '" . mysqli_real_escape_string($conn, $p['product_item']) . "', 0, '{$p['quantity']}',
                    " . ($p['custom_color'] ?? 'NULL') . ", " . ($p['custom_grade'] ?? 'NULL') . ",
                    '" . mysqli_real_escape_string($conn, $p['custom_width']) . "', " . ($p['custom_height'] ? "'" . mysqli_real_escape_string($conn, $p['custom_height']) . "'" : "NULL") . ",
                    " . ($p['custom_bend'] ? "'" . mysqli_real_escape_string($conn, $p['custom_bend']) . "'" : "NULL") . ",
                    " . ($p['custom_hem'] ? "'" . mysqli_real_escape_string($conn, $p['custom_hem']) . "'" : "NULL") . ",
                    " . ($p['custom_length'] ? "'" . mysqli_real_escape_string($conn, $p['custom_length']) . "'" : "NULL") . ",
                    " . ($p['custom_length2'] ? "'" . mysqli_real_escape_string($conn, $p['custom_length2']) . "'" : "NULL") . ",
                    '{$p['actual_price']}', '{$p['discounted_price']}', '{$p['product_category']}', '{$p['usageid']}',
                    '{$p['current_customer_discount']}', '{$p['current_loyalty_discount']}', '{$p['used_discount']}',
                    '{$p['stiff_stand_seam']}', '{$p['stiff_board_batten']}', '{$p['panel_type']}'
                )
            ";
            mysqli_query($conn, $sql);
        }

        unset($_SESSION['cart']);

        echo json_encode([
            'success' => false,
            'error' => 'Approval request created due to insufficient Net balance.'
        ]);
        exit;
    }

    $job_balance = getJobDepositTotal($job_id);

    $credit_to_apply = 0;
    $job_deposit_applied = 0;
    $new_store_credit = $store_credit;

    if ($applyStoreCredit && $store_credit > 0) {
        $credit_to_apply = min($store_credit, $original_cash_amt);
        $new_store_credit = $store_credit - $credit_to_apply;
        $cash_amt = $original_cash_amt - $credit_to_apply;
    } else {
        $cash_amt = $original_cash_amt;
    }

    if ($pay_type == 'net30' && $charge_net_30 > 0) {
        $net30_applied = min($charge_net_30, $cash_amt);
        $new_charge_net_30 = $charge_net_30 - $net30_applied;
        $cash_amt = $cash_amt - $net30_applied;
    }

    if ($applyJobDeposit && $job_balance > 0) {
        $job_deposit_applied = min($job_balance, $cash_amt);
        $cash_amt -= $job_deposit_applied;
    }

    if ($pay_type == 'delivery' || $pay_type == 'pickup') {
        $credit_amt = $cash_amt;
        $cash_amt = 0;
    }

    if ($cash_amt == 0 && $credit_amt == 0 && $applyJobDeposit) {
        $pay_type = 'job_deposit';
    }

    /* 
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
    */

    $query = "INSERT INTO orders (estimateid, cashier, total_price, discounted_price, discount_percent, order_date, customerid, originalcustomerid, cash_amt, credit_amt, job_name, job_po, deliver_address,  deliver_city,  deliver_state,  deliver_zip, delivery_amt, deliver_method, deliver_fname, deliver_lname, pay_type, tax_status, tax_exempt_number) 
              VALUES ('$estimateid', '$cashierid', '$total_price', '$total_discounted_price', '".($discount * 100)."', '$order_date', '$customerid', '$customerid', '$cash_amt', '$credit_amt' , '$job_name' , '$job_po' , '$deliver_address', '$deliver_city', '$deliver_state', '$deliver_zip' , '$delivery_amt', '$deliver_method' , '$deliver_fname' , '$deliver_lname', '$pay_type', '$tax_status', '$tax_exempt_number')";

    if ($conn->query($query) === TRUE) {
        $orderid = $conn->insert_id;

        $job_id = intval($job_id);
        $po_number = mysqli_real_escape_string($conn, $job_po);
        $created_by = mysqli_real_escape_string($conn, $cashierid);
        $reference_no = mysqli_real_escape_string($conn, $orderid);
        $description = 'Materials Purchased';
        $check_number = ($payment_method === 'check' && !empty($check_no)) ? "'".mysqli_real_escape_string($conn, $check_no)."'" : "NULL";

        if ($pay_type == 'net30') {
            $update_sql = "UPDATE customer SET charge_net_30 = $new_charge_net_30 WHERE customer_id = $customerid";
            if (!mysqli_query($conn, $update_sql)) {
                $response['error'] = 'Update Error: ' . mysqli_error($conn);
            }

            $insert_sql = "
                INSERT INTO customer_net30_history (customer_id, credit_amount, credit_type, reference_type, reference_id, created_at)
                VALUES (
                    $customerid,
                    $net30_applied,
                    'use',
                    'order',
                    $orderid,
                    NOW()
                )
            ";
            if (!mysqli_query($conn, $insert_sql)) {
                $response['error'] = 'Ledger Insert Error: ' . mysqli_error($conn);
            }

            $sql = "
                INSERT INTO job_ledger (
                    job_id, customer_id, entry_type, amount, po_number, reference_no, description, 
                    check_number, created_by, created_at, payment_method
                ) VALUES (
                    '$job_id', '$customerid', 'credit', '$net30_applied', '$po_number', '$reference_no', '$description',
                    NULL, '$created_by', NOW(), '$pay_type'
                )
            ";
            if (!mysqli_query($conn, $sql)) {
                $response['error'] = 'Ledger Insert Error: ' . mysqli_error($conn);
            }
        }

        if ($applyStoreCredit) {
            $update_sql = "UPDATE customer SET store_credit = $new_store_credit WHERE customer_id = $customerid";
            if (!mysqli_query($conn, $update_sql)) {
                $response['error'] = 'Update Error: ' . mysqli_error($conn);
            }

            $insert_sql = "
                INSERT INTO customer_store_credit_history (customer_id, credit_amount, credit_type, reference_type, reference_id, created_at)
                VALUES (
                    $customerid,
                    $credit_to_apply,
                    'use',
                    'order',
                    $orderid,
                    NOW()
                )
            ";
            if (!mysqli_query($conn, $insert_sql)) {
                $response['error'] = 'Ledger Insert Error: ' . mysqli_error($conn);
            }
        }

        if (!empty($job_id)) {
            if ($applyJobDeposit && $job_balance > 0 && $job_deposit_applied > 0) {
                $amount = number_format($job_deposit_applied, 2, '.', '');
                $sql = "
                    INSERT INTO job_ledger (
                        job_id, customer_id, entry_type, amount, po_number, reference_no, description, check_number, created_by, created_at, payment_method
                    ) VALUES (
                        '$job_id', '$customerid', 'usage', '$amount', '$po_number', '$reference_no', '$description', $check_number, '$created_by', NOW(), 'job_deposit'
                    )
                ";
                if (!mysqli_query($conn, $sql)) {
                    $response['error'] = 'Ledger Insert Error (usage): ' . mysqli_error($conn);
                } else {
                    $remaining_to_apply = $job_deposit_applied;

                    $query_deposits = "
                        SELECT deposit_id, deposit_remaining 
                        FROM job_deposits 
                        WHERE job_id = '$job_id' AND deposit_status = 1 AND deposit_remaining > 0 
                        ORDER BY created_at ASC
                    ";
                    $result_deposits = mysqli_query($conn, $query_deposits);

                    while ($row = mysqli_fetch_assoc($result_deposits)) {
                        $deposit_id = $row['deposit_id'];
                        $remaining = floatval($row['deposit_remaining']);

                        if ($remaining_to_apply <= 0) {
                            break;
                        }

                        $used = min($remaining, $remaining_to_apply);
                        $new_remaining = $remaining - $used;
                        $new_status = ($new_remaining <= 0) ? 2 : 1;

                        $update_deposit = "
                            UPDATE job_deposits
                            SET deposit_remaining = $new_remaining,
                                deposit_status = $new_status
                            WHERE deposit_id = $deposit_id
                        ";
                        mysqli_query($conn, $update_deposit);

                        $remaining_to_apply -= $used;
                    }
                }
            }
        }
        
        $entry_type = ($pay_type == 'delivery' || $pay_type == 'pickup' || $pay_type == 'net30') ? 'credit' : 'usage';
        $cash_amt = floatval($cash_amt);
        $credit_amt = floatval($credit_amt);
        $amount = $cash_amt != 0 ? $cash_amt : $credit_amt;

        if ($amount > 0) {
            $amount = number_format($amount, 2, '.', '');
            $sql = "
                INSERT INTO job_ledger (
                    job_id, customer_id, entry_type, amount, po_number, reference_no, description, 
                    check_number, created_by, created_at, payment_method
                ) VALUES (
                    '$job_id', '$customerid', '$entry_type', '$amount', '$po_number', '$reference_no', '$description',
                    NULL, '$created_by', NOW(), '$pay_type'
                )
            ";
            if (!mysqli_query($conn, $sql)) {
                $response['error'] = 'Ledger Insert Error ($entry_type): ' . mysqli_error($conn);
            }
        }

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

            $total_length = $estimate_length + ($estimate_length_inch / 12);

            $amount_discount = !empty($item["amount_discount"]) ? $item["amount_discount"] : 0;
            $product_category = intval($product_details['product_category']);
            $actual_price = $unit_price * $quantity_cart * $total_length;
            $discounted_price = ($actual_price * (1 - $discount) * (1 - $customer_pricing)) - $amount_discount;
            $discounted_price = max(0, $discounted_price);

            $curr_discount = intval(getCustomerDiscountProfile($customerid));
            $loyalty_discount = intval(getCustomerDiscountLoyalty($customerid));
            $used_discount = !empty($item['used_discount']) ? $item['used_discount'] : getCustomerDiscount($customerid);

            $stiff_stand_seam = !empty($item['stiff_stand_seam']) ? $item['stiff_stand_seam'] : '0';
            $stiff_board_batten = !empty($item['stiff_board_batten']) ? $item['stiff_board_batten'] : '0';
            $panel_type = !empty($item['panel_type']) ? $item['panel_type'] : '0';
            $custom_img_src = $item['custom_trim_src'];

            $values[] = "('$orderid', '$product_id', '$product_item', '$quantity_cart', '$estimate_width', '$estimate_bend', '$estimate_hem', '$estimate_length', '$estimate_length_inch', '$actual_price', '$discounted_price', '$product_category', '$custom_color' , '$custom_grade', '$curr_discount', '$loyalty_discount', '$used_discount', '$stiff_stand_seam', '$stiff_board_batten', '$panel_type', '$custom_img_src')";
            
            

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
                            padding-left: 0;
                        }
                        li {
                            margin-bottom: 5px;
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>$subject</h2>
                        $list_items
                    </div>
                </body>
                </html>";

                $response = sendEmail($admin_email, 'EKM', $subject, $message);
                if ($response['success'] == true) {
                } else {
                    $response['error'] = "Failed to send Mail" . $conn->error;
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

        $query = "INSERT INTO order_product (orderid, productid, product_item, quantity, custom_width, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price, product_category, custom_color, custom_grade, current_customer_discount, current_loyalty_discount, used_discount, stiff_stand_seam, stiff_board_batten, panel_type, custom_img_src) VALUES ";
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

                unset($_SESSION['cart']);

                if($applyStoreCredit){

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

        unset($_SESSION['customer_id']);
    } else {
        $response['error'] = "Error inserting order: " . $conn->error;
    }

    $conn->close();
    echo json_encode($response);
}

if (isset($_POST['save_approval'])) {
    header('Content-Type: application/json');
    $response = [];

    if (!isset($_SESSION['customer_id']) || empty($_SESSION['cart'])) {
        $response['error'] = "Customer ID or cart is not set.";
        echo json_encode($response);
        exit;
    }

    $customerid = intval($_SESSION['customer_id']);
    $cashierid = intval($_SESSION['userid']);
    $cart = $_SESSION['cart'];
    $submitted_date = date('Y-m-d H:i:s');

    $customer_details = getCustomerDetails($customerid);
    $discount_default = floatval(getCustomerDiscount($customerid)) / 100;

    $total_price = 0;
    $total_discounted_price = 0;
    $approval_products = [];

    foreach ($cart as $item) {
        $discount = isset($item['used_discount']) && is_numeric($item['used_discount']) ? floatval($item['used_discount']) / 100 : $discount_default;

        $product_id = intval($item['product_id']);
        $product_details = getProductDetails($product_id);
        $customer_pricing = getPricingCategory($product_details['product_category'], $customer_details['customer_pricing']) / 100;

        $quantity_cart = intval($item['quantity_cart']);
        $unit_price = floatval($item['unit_price']);
        $estimate_length = floatval($item['estimate_length']);
        $estimate_length_inch = floatval($item['estimate_length_inch']);
        $amount_discount = !empty($item['amount_discount']) ? floatval($item['amount_discount']) : 0;

        $total_length = $estimate_length + ($estimate_length_inch / 12);
        $actual_price = $unit_price * $quantity_cart * $total_length;
        $discounted_price = ($actual_price * (1 - $discount) * (1 - $customer_pricing)) - $amount_discount;
        $discounted_price = max(0, $discounted_price);

        $total_price += $actual_price;
        $total_discounted_price += $discounted_price;

        $approval_products[] = [
            'productid' => $product_id,
            'product_item' => mysqli_real_escape_string($conn, $item['product_item']),
            'quantity' => $quantity_cart,
            'custom_color' => isset($item['custom_color']) ? intval($item['custom_color']) : 'NULL',
            'custom_grade' => isset($item['custom_grade']) ? intval($item['custom_grade']) : 'NULL',
            'custom_width' => mysqli_real_escape_string($conn, $item['custom_width']),
            'custom_height' => isset($item['custom_height']) ? "'" . mysqli_real_escape_string($conn, $item['custom_height']) . "'" : 'NULL',
            'custom_bend' => isset($item['custom_bend']) ? "'" . mysqli_real_escape_string($conn, $item['custom_bend']) . "'" : 'NULL',
            'custom_hem' => isset($item['custom_hem']) ? "'" . mysqli_real_escape_string($conn, $item['custom_hem']) . "'" : 'NULL',
            'custom_length' => isset($item['custom_length']) ? "'" . mysqli_real_escape_string($conn, $item['custom_length']) . "'" : 'NULL',
            'custom_length2' => isset($item['custom_length2']) ? "'" . mysqli_real_escape_string($conn, $item['custom_length2']) . "'" : 'NULL',
            'actual_price' => $actual_price,
            'discounted_price' => $discounted_price,
            'product_category' => $product_details['product_category'],
            'usageid' => $item['usageid'] ?? 0,
            'current_customer_discount' => $item['current_customer_discount'] ?? 'NULL',
            'current_loyalty_discount' => $item['current_loyalty_discount'] ?? 'NULL',
            'used_discount' => $item['used_discount'] ?? 'NULL',
            'stiff_stand_seam' => intval($item['stiff_stand_seam'] ?? 0),
            'stiff_board_batten' => intval($item['stiff_board_batten'] ?? 0),
            'panel_type' => intval($item['panel_type'] ?? 0)
        ];
    }

    $discount_percent = $discount_default * 100;

    $query = "INSERT INTO approval (
        status, cashier, total_price, discounted_price, discount_percent,
        submitted_date, customerid, originalcustomerid, type_approval
    ) VALUES (
        1, '$cashierid', '$total_price', '$total_discounted_price', '$discount_percent',
        '$submitted_date', '$customerid', '$customerid', 1
    )";

    if ($conn->query($query)) {
        $approval_id = $conn->insert_id;

        $values = [];
        foreach ($approval_products as $p) {
            $values[] = "(
                '$approval_id', '{$p['productid']}', '{$p['product_item']}', 0, '{$p['quantity']}',
                {$p['custom_color']}, {$p['custom_grade']}, '{$p['custom_width']}', {$p['custom_height']}, {$p['custom_bend']}, {$p['custom_hem']},
                {$p['custom_length']}, {$p['custom_length2']}, '{$p['actual_price']}', '{$p['discounted_price']}', '{$p['product_category']}',
                '{$p['usageid']}', {$p['current_customer_discount']}, {$p['current_loyalty_discount']}, {$p['used_discount']},
                '{$p['stiff_stand_seam']}', '{$p['stiff_board_batten']}', '{$p['panel_type']}'
            )";
        }

        $insert_products = "INSERT INTO approval_product (
            approval_id, productid, product_item, status, quantity, custom_color, custom_grade, custom_width,
            custom_height, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price,
            product_category, usageid, current_customer_discount, current_loyalty_discount,
            used_discount, stiff_stand_seam, stiff_board_batten, panel_type
        ) VALUES " . implode(', ', $values);

        if ($conn->query($insert_products)) {
            unset($_SESSION['cart'], $_SESSION['customer_id']);
            $response['success'] = true;
            $response['approval_id'] = $approval_id;
        } else {
            $response['error'] = "Error inserting approval products: " . $conn->error;
        }
    } else {
        $response['error'] = "Error inserting approval: " . $conn->error;
    }

    unset($_SESSION['cart']);

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
            customer_id, 
            customer_first_name, 
            customer_last_name, 
            customer_business_name,
            contact_phone
        FROM 
            customer
        WHERE 
            (
                customer_first_name LIKE '%$search%' 
                OR customer_last_name LIKE '%$search%'
                OR customer_business_name LIKE '%$search%'
            )
            AND status NOT IN ('0', '3')
        LIMIT 15
    ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $response = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $fullName = $row['customer_first_name'] . ' ' . $row['customer_last_name'];
            $label = get_customer_name($row['customer_id']);

            if (!empty($row['contact_phone'])) {
                $label .= ' (' . $row['contact_phone'] . ')';
            }

            $response[] = [
                'value' => $row['customer_id'],
                'label' => $label
            ];
        }
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Query failed']);
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

if (isset($_POST['save_trim'])) {
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

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = array();
    }

    $key = findCartKey($_SESSION["cart"], $id, $line);

    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['quantity_cart'] = $quantity;
        $_SESSION["cart"][$key]['estimate_length'] = $feet;
        $_SESSION["cart"][$key]['estimate_length_inch'] = $inches;
        $_SESSION["cart"][$key]['unit_price'] = $price;
        $_SESSION["cart"][$key]['custom_trim_src'] = $img_src;
        $_SESSION["cart"][$key]['drawing_data'] = $drawing_data;
        $_SESSION["cart"][$key]['custom_color'] = $color;
        $_SESSION["cart"][$key]['custom_grade'] = $grade;
        $_SESSION["cart"][$key]['custom_gauge'] = $gauge;
        $_SESSION["cart"][$key]['is_pre_order'] = $is_pre_order;
        $_SESSION["cart"][$key]['is_custom'] = $is_pre_order;
    } else {
        $query = "SELECT * FROM product WHERE product_id = '$id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            $line_to_use = is_numeric($line) ? intval($line) : 1;

            foreach ($_SESSION["cart"] as $item) {
                if ($item['product_id'] == $id && $item['line'] >= $line_to_use) {
                    $line_to_use = $item['line'] + 1;
                }
            }

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => $row['product_item'],
                'unit_price' => $price,
                'line' => $line_to_use,
                'quantity_ttl' => 0,
                'quantity_in_stock' => 0,
                'quantity_cart' => $quantity,
                'estimate_width' => 0,
                'estimate_length' => $feet,
                'estimate_length_inch' => $inches,
                'usage' => 0,
                'custom_color' => $color,
                'weight' => 0,
                'supplier_id' => '',
                'custom_grade' => $grade,
                'custom_gauge' => $gauge,
                'is_pre_order' => $is_pre_order,
                'is_custom' => $is_custom,
                'custom_trim_src' => $img_src,
                'drawing_data' => $drawing_data
            );

            $_SESSION["cart"][] = $item_array;
        } else {
            echo json_encode(['error' => "Trim Product not available"]);
            exit;
        }
    }

    echo json_encode(['success' => true]);
}

if (isset($_POST['save_custom_length'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line'] ?? 1);
    $quantity = floatval(mysqli_real_escape_string($conn, $_POST['quantity']));
    $estimate_length = floatval(mysqli_real_escape_string($conn, $_POST['custom_length_feet']));
    $estimate_length_inch = floatval(mysqli_real_escape_string($conn, $_POST['custom_length_inch']));
    $price = floatval(mysqli_real_escape_string($conn, $_POST['price']));

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = array();
    }

    $query = "SELECT * FROM product WHERE product_id = '$id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $line_to_use = is_numeric($line) ? intval($line) : 1;

        foreach ($_SESSION["cart"] as $item) {
            if ($item['product_id'] == $id && $item['line'] >= $line_to_use) {
                $line_to_use = $item['line'] + 1;
            }
        }

        $item_array = array(
            'product_id' => $row['product_id'],
            'product_item' => $row['product_item'],
            'unit_price' => $price,
            'line' => $line_to_use,
            'quantity_ttl' => 0,
            'quantity_in_stock' => 0,
            'quantity_cart' => $quantity,
            'estimate_width' => 0,
            'estimate_length' => $estimate_length,
            'estimate_length_inch' => $estimate_length_inch,
            'usage' => 0,
            'custom_color' => '',
            'weight' => 0,
            'supplier_id' => '',
            'custom_grade' => '',
            'custom_gauge' => ''
        );

        $_SESSION["cart"][] = $item_array;
    } else {
        echo json_encode(['error' => "Trim Product not available"]);
        exit;
    }

    echo json_encode(['success' =>$_SESSION["cart"]]);
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


if (isset($_POST['return_product'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $stock_fee_percent = floatval($_POST['stock_fee']) / 100;

    $query = "SELECT op.*, o.order_date, o.originalcustomerid 
              FROM order_product AS op
              LEFT JOIN orders AS o ON o.orderid = op.orderid
              WHERE op.id = '$id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $order = mysqli_fetch_assoc($result);
        $available_quantity = $order['quantity'];

        if ($quantity > $available_quantity) {
            echo "Quantity entered exceeds the purchased count!";
            exit;
        }

        $insert_query = "INSERT INTO product_returns 
                         (orderid, productid, status, quantity, custom_color, custom_width, custom_height, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price, product_category, usageid, stock_fee)
                         VALUES 
                         (
                            '{$order['orderid']}', 
                            '{$order['productid']}', 
                            0, 
                            '$quantity', 
                            '{$order['custom_color']}', 
                            '{$order['custom_width']}', 
                            '{$order['custom_height']}', 
                            '{$order['custom_bend']}', 
                            '{$order['custom_hem']}', 
                            '{$order['custom_length']}', 
                            '{$order['custom_length2']}', 
                            '{$order['actual_price']}', 
                            '{$order['discounted_price']}', 
                            '{$order['product_category']}', 
                            '{$order['usageid']}', 
                            '$stock_fee_percent'
                         )";

        if (mysqli_query($conn, $insert_query)) {
            $return_id = mysqli_insert_id($conn);

            $new_quantity = $available_quantity - $quantity;
            $update_query = "UPDATE order_product SET quantity = '$new_quantity' WHERE id = '$id'";
            mysqli_query($conn, $update_query);

            $purchase_date = new DateTime($order['order_date']);
            $today = new DateTime();
            $interval = $purchase_date->diff($today)->days;

            if ($interval > 90) {
                $discounted_price = floatval($order['discounted_price']);
                $amount = $quantity * $discounted_price;
                $stock_fee = $amount * $stock_fee_percent;
                $amount_returned = $amount - $stock_fee;

                $customer_id = $order['originalcustomerid'];

                $update_credit_query = "UPDATE customer SET store_credit = store_credit + $amount_returned WHERE customer_id = '$customer_id'";
                mysqli_query($conn, $update_credit_query);

                $insert_credit_history = "
                    INSERT INTO customer_store_credit_history (
                        customer_id,
                        credit_amount,
                        credit_type,
                        reference_type,
                        reference_id,
                        description,
                        created_at
                    ) VALUES (
                        '$customer_id',
                        $amount_returned,
                        'add',
                        'product_return',
                        $return_id,
                        'Refund (return over 90 days, less stock fee)',
                        NOW()
                    )
                ";
                mysqli_query($conn, $insert_credit_history);
            }

            setOrderTotals($order['orderid']);
            echo "success";
        } else {
            echo "Error inserting into product_returns.";
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
    $price = $_POST['price'];
    $disc = $_POST['disc'];
    $notes = $_POST['notes'];

    if (!empty($product_select)) {
        foreach ($_SESSION['cart'] as $key => &$item) {
            if (in_array($item['product_id'], $product_select)) {
                $item['used_discount'] = $price_group_select;

                if (!empty($price)) {
                    $item['unit_price'] = $price;
                }

                if (!empty($disc)) {
                    $item['amount_discount'] = $disc;
                }

                if (!empty($notes)) {
                    $item['notes'] = $notes;
                }
            }
        }
        unset($item);
        echo "success";
    }
}

if (isset($_POST['change_discount'])) {
    $discount = (float) $_POST['discount'];
    foreach ($_SESSION['cart'] as $key => &$item) {
        $item['used_discount'] = $discount;
    }
    unset($item);
    echo "success";
}

if (isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity_product']) ? $_POST['quantity_product'] : [];
    $quantity = array_map(function($qty) {
        return empty($qty) ? 0 : $qty;
    }, $quantity);
    $lengthFeet = isset($_POST['length_feet']) ? $_POST['length_feet'] : [];
    $lengthInch = isset($_POST['length_inch']) ? $_POST['length_inch'] : [];
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $is_pre_order = mysqli_real_escape_string($conn, $_POST['is_pre_order'] ?? 0);
    $panel_type = mysqli_real_escape_string($conn, $_POST['panel_type']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);
    $panel_drip_stop = mysqli_real_escape_string($conn, $_POST['panel_drip_stop']);
    $stiff_board_batten = isset($_POST['stiff_board_batten']) ? mysqli_real_escape_string($conn, $_POST['stiff_board_batten']) : '';
    $stiff_stand_seam = isset($_POST['stiff_stand_seam']) ? mysqli_real_escape_string($conn, $_POST['stiff_stand_seam']) : '';
    $bend_product = isset($_POST['bend_product']) ? floatval($_POST['bend_product']) : 0;
    $hem_product = isset($_POST['hem_product']) ? floatval($_POST['hem_product']) : 0;
    $line = 1;

    foreach ($quantity as $index => $qty) {
        $length_feet = isset($lengthFeet[$index]) ? intval($lengthFeet[$index]) : 0;
        $length_inch = isset($lengthInch[$index]) ? intval($lengthInch[$index]) : 0;

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

                $basePrice = floatval($row['unit_price']);
                if($row['sold_by_feet'] == '1'){
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
                $weight = floatval($row['weight']);
                $item_array = array(
                    'product_id' => $row['product_id'],
                    'product_item' => getProductName($row['product_id']),
                    'supplier_id' => $row['supplier_id'],
                    'unit_price' => $unit_price,
                    'line' => 1,
                    'quantity_ttl' => $totalStock,
                    'quantity_in_stock' => $quantityInStock,
                    'quantity_cart' => $item_quantity,
                    'estimate_width' => $row['width'],
                    'estimate_length' => $length_feet,
                    'estimate_length_inch' => $length_inch,
                    'usage' => 0,
                    'custom_color' => !empty($color) ? $color : $row['color'],
                    'panel_type' => $panel_type,
                    'weight' => $weight,
                    'custom_grade' => !empty($grade) ? $grade : $row['grade'],
                    'custom_gauge' => !empty($gauge) ? $gauge : $row['gauge'],
                    'stiff_board_batten' => $stiff_board_batten,
                    'stiff_stand_seam' => $stiff_stand_seam,
                    'is_pre_order' => $is_pre_order
                );
    
                $_SESSION["cart"][] = $item_array;
            }
        }
        $line++;
    }

    

    echo 'success';
}

if (isset($_POST['add_custom_truss_to_cart'])) {
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

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = array();
    }

    $material_name = getTrussMaterialName($truss_material);
    $overhang_left_name = getTrussOverhangName($truss_left_overhang);
    $overhang_right_name = getTrussOverhangName($truss_right_overhang);
    $pitch_name = getTrussPitchName($truss_pitch);
    $spacing_name = getTrussSpacingName($truss_spacing);
    $type_name = getTrussTypeName($truss_type);

    $parts = [];

    $parts[] = '(Custom Truss)';

    if (!empty($size)) {
        $parts[] = $size;
    }
    if (!empty($material_name)) {
        $parts[] = $material_name;
    }
    if (!empty($overhang_left_name)) {
        $parts[] = $overhang_left_name;
    }
    if (!empty($overhang_right_name)) {
        $parts[] = $overhang_right_name;
    }
    if (!empty($pitch_name)) {
        $parts[] = $pitch_name;
    }
    if (!empty($spacing_name)) {
        $parts[] = $spacing_name;
    }
    if (!empty($type_name)) {
        $parts[] = $type_name;
    }

    $product_item = implode(' - ', $parts);

    $query = "SELECT * FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $item_quantity = $quantity;

        $quantityInStock = getProductStockInStock($product_id);
        $totalQuantity = getProductStockTotal($product_id);
        $totalStock = $totalQuantity;

        $weight = floatval($row['weight']);
        $item_array = array(
            'product_id' => $row['product_id'],
            'product_item' => $product_item,
            'unit_price' => $price,
            'line' => 1,
            'quantity_ttl' => $totalStock,
            'quantity_in_stock' => $quantityInStock,
            'quantity_cart' => $item_quantity
        );

        $_SESSION["cart"][] = $item_array;
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
                        $query_profile = "
                            SELECT DISTINCT profile_type
                            FROM profile_type 
                            WHERE hidden = '0' $category_condition
                            ORDER BY profile_type ASC";
                        $result_profile = mysqli_query($conn, $query_profile);
                        while ($row_profile = mysqli_fetch_array($result_profile)) {
                        ?>
                            <option value="<?= $row_profile['profile_type'] ?>">
                                <?= $row_profile['profile_type'] ?>
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

