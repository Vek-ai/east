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

$lumber_id = 1;
$trim_id = 4;
$panel_id = 3;
$custom_truss_id = 47;
$special_trim_id = 66;
$screw_id = 16;

function findCartKey($cart, $product_id, $line) {
    foreach ($cart as $key => $item) {
        if ($item['product_id'] == $product_id && $item['line'] == $line) {
            return $key;
        }
    }
    return false;
}

function getLastValue($value) {
    if (empty($value)) {
        return null;
    }

    $decoded = json_decode($value, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return !empty($decoded) ? end($decoded) : null;
    }

    return $value;
}

if (isset($_POST['modifyquantity']) || isset($_POST['duplicate_product'])) {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $line       = (int)($_POST['line'] ?? 0);
    $qty        = max((int)($_POST['qty'] ?? 1), 1);

    $quantityInStock = getProductStockInStock($product_id);
    $totalStock      = getProductStockTotal($product_id);

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = [];
    }

    $newLine = !empty($_SESSION["cart"]) ? max(array_column($_SESSION["cart"], 'line')) + 1 : 1;

    if (isset($_POST['duplicate_product']) && $line > 0) {
        $key = findCartKey($_SESSION["cart"], $product_id, $line);
        if ($key !== false) {
            $oldItem = $_SESSION['cart'][$key];
            $oldItem['line'] = $newLine;
            $oldItem['quantity_cart'] = $qty;
            $_SESSION['cart'][$newLine] = $oldItem;
            echo "Duplicated line $line as $newLine";
            return;
        }
    }

    $key = findCartKey($_SESSION["cart"], $product_id, $line);

    if ($key !== false) {
        if (isset($_POST['setquantity'])) {
            $_SESSION["cart"][$key]['quantity_cart'] = $qty;
        } elseif (isset($_POST['addquantity'])) {
            $_SESSION["cart"][$key]['quantity_cart'] += $qty;
        } elseif (isset($_POST['deductquantity'])) {
            $_SESSION["cart"][$key]['quantity_cart'] -= 1;
            if ($_SESSION["cart"][$key]['quantity_cart'] <= 0) {
                unset($_SESSION["cart"][$key]);
                echo 'removed';
                return;
            }
        }
        echo $_SESSION["cart"][$key]['quantity_cart'];
        return;
    }

    $query = "SELECT * FROM product WHERE product_id = '$product_id' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $item_quantity = min($qty, $totalStock);

        $weight    = floatval($row['weight']);
        $basePrice = floatval($row['unit_price']);
        if ($row['sold_by_feet'] == '1') {
            $basePrice = $basePrice / max(floatval($row['length']), 1);
        }

        $unitPrice = calculateUnitPrice($basePrice, 0, 0, '', $row['sold_by_feet'], 0, 0);

        $_SESSION["cart"][$newLine] = [
            'product_id'          => $row['product_id'],
            'product_item'        => getProductName($row['product_id']),
            'unit_price'          => $unitPrice,
            'line'                => $newLine,
            'quantity_ttl'        => $totalStock,
            'quantity_in_stock'   => $quantityInStock,
            'quantity_cart'       => $item_quantity,
            'estimate_width'      => $row['width'],
            'estimate_length'     => '',
            'estimate_length_inch'=> '',
            'usage'               => 0,
            'custom_color'        => '',
            'weight'              => $weight,
            'supplier_id'         => $row['supplier_id'],
            'custom_grade'        => (int)$row['grade'],
            'custom_profile'      => (int)$row['profile'],
        ];
    }
}

if (isset($_POST['deleteitem'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id_del']);
    $line       = mysqli_real_escape_string($conn, $_POST['line']);

    $found = false;

    if (!empty($_SESSION["cart"])) {
        foreach ($_SESSION["cart"] as $key => $item) {
            if ($item['product_id'] == $product_id && $item['line'] == $line) {
                unset($_SESSION["cart"][$key]);
                $found = true;
                break;
            }
        }
    }

    if ($found) {
        echo "Removed line $line (Product ID: $product_id)";
    } else {
        echo "Item not found in cart.";
    }
}

if (isset($_POST['deleteproduct'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id_del']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);

    if (!empty($_SESSION["cart"]) && isset($_SESSION["cart"][$line])) {
        unset($_SESSION["cart"][$line]);
    } else {
        echo "No matching item found for Product ID: $product_id and Line: $line.";
    }
}

if (isset($_REQUEST['query'])) {
    $searchQuery = isset($_REQUEST['query']) ? mysqli_real_escape_string($conn, $_REQUEST['query']) : '';
    $color_id   = (int) ($_REQUEST['color_id'] ?? 0);
    $grade      = (int) ($_REQUEST['grade'] ?? 0);
    $gauge_id   = (int) ($_REQUEST['gauge_id'] ?? 0);
    $type_id    = (int) ($_REQUEST['type_id'] ?? 0);
    $profile_id = (int) ($_REQUEST['profile_id'] ?? 0);
    $category_id = (int) ($_REQUEST['category_id'] ?? 0);
    $onlyInStock = isset($_REQUEST['onlyInStock']) ? filter_var($_REQUEST['onlyInStock'], FILTER_VALIDATE_BOOLEAN) : false;
    $onlyPromotions = isset($_REQUEST['onlyPromotions']) ? filter_var($_REQUEST['onlyPromotions'], FILTER_VALIDATE_BOOLEAN) : false;
    $onlyOnSale = isset($_REQUEST['onlyOnSale']) ? filter_var($_REQUEST['onlyOnSale'], FILTER_VALIDATE_BOOLEAN) : false;

    $query_product = "
        SELECT 
            p.*,
            COALESCE(
                CASE 
                    WHEN p.product_category IN (3,4) THEN 1  -- treat as always in stock
                    ELSE SUM(i.quantity_ttl)
                END, 0
            ) AS total_quantity,
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
        $attrs = getProductAttributes($searchQuery);

        if (!empty($attrs) && array_filter($attrs)) {
            if (!empty($attrs['color']))     $color_id    = (int) $attrs['color'];
            if (!empty($attrs['grade']))     $grade       = (int) $attrs['grade'];
            if (!empty($attrs['gauge']))     $gauge_id    = (int) $attrs['gauge'];
            if (!empty($attrs['type']))      $type_id     = (int) $attrs['type'];
            if (!empty($attrs['profile']))   $profile_id  = (int) $attrs['profile'];
            if (!empty($attrs['category']))  $category_id = (int) $attrs['category'];
        } else {
            $query_product .= " AND (p.product_item LIKE '%$searchQuery%' OR p.description LIKE '%$searchQuery%')";
        }
    }

    if (!empty($color_id)) {
        $query_product .= " AND JSON_VALID(p.color_paint) 
                            AND (
                                JSON_CONTAINS(p.color_paint, '\"" . intval($color_id) . "\"') 
                                OR JSON_CONTAINS(p.color_paint, '" . intval($color_id) . "')
                            )";
    }

    if (!empty($grade)) {
        $query_product .= " AND JSON_VALID(p.grade) 
                            AND (
                                JSON_CONTAINS(p.grade, '\"" . intval($grade) . "\"') 
                                OR JSON_CONTAINS(p.grade, '" . intval($grade) . "')
                            )";
    }

    if (!empty($gauge_id)) {
        $query_product .= " AND JSON_VALID(p.gauge) 
                            AND (
                                JSON_CONTAINS(p.gauge, '\"" . intval($gauge_id) . "\"') 
                                OR JSON_CONTAINS(p.gauge, '" . intval($gauge_id) . "')
                            )";
    }

    if (!empty($type_id)) {
        $query_product .= " AND JSON_VALID(p.product_type) 
                            AND (
                                JSON_CONTAINS(p.product_type, '\"" . intval($type_id) . "\"') 
                                OR JSON_CONTAINS(p.product_type, '" . intval($type_id) . "')
                            )";
    }

    if (!empty($profile_id)) {
        $query_product .= " AND JSON_VALID(p.profile) 
                            AND (
                                JSON_CONTAINS(p.profile, '\"" . intval($profile_id) . "\"') 
                                OR JSON_CONTAINS(p.profile, '" . intval($profile_id) . "')
                            )";
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
        $query_product .= " HAVING total_quantity > 0";
    }

    $result_product = mysqli_query($conn, $query_product);

    $tableHTML = "";

    if (mysqli_num_rows($result_product) > 0) {
        while ($row_product = mysqli_fetch_array($result_product)) {

            $id_attrib = [];
            if (!empty($row_product['product_id'])) {
                $id_attrib = getProductAttributes($row_product['product_id']);
            }

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
                    <a href="javascript:void(0);" id="view_in_stock" data-id="' . $row_product['product_id'] . '" class="d-flex justify-content-center align-items-center">
                        <span class="text-bg-success p-1 rounded-circle"></span>
                        <span class="ms-2">In Stock</span>
                    </a>';
            } else {
                $stock_text = '
                    <a href="javascript:void(0);" id="view_out_of_stock" data-id="' . $row_product['product_id'] . '" class="d-flex justify-content-center align-items-center">
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

            $is_lumber = $row_product['product_category'] == $lumber_id ? true : false;
            $is_panel = $row_product['product_category'] == $panel_id ? true : false;

            $is_trim = ($row_product['product_category'] == $trim_id);
            $is_special_trim = ($is_trim && $row_product['is_special_trim'] == 1);

            $is_screw = $row_product['product_category'] == $screw_id ? true : false;
            $is_custom_truss = $row_product['product_id'] == $custom_truss_id ? true : false;
            $is_custom_length = $row_product['is_custom_length'] == 1 ? true : false;

            $qty_input = !$is_panel && !$is_custom_truss && !$is_special_trim && !$is_trim && !$is_custom_length && !$is_screw && !$is_lumber
                ? ' <div class="input-group input-group-sm d-flex justify-content-center">
                        <button class="btn btn-outline-primary btn-minus" type="button" data-id="' . $row_product['product_id'] . '">-</button>
                        <input class="form-control p-1 text-center" type="number" id="qty' . $row_product['product_id'] . '" value="1" min="1" style="max-width:70px;">
                        <button class="btn btn-outline-primary btn-plus" type="button" data-id="' . $row_product['product_id'] . '">+</button>
                    </div>'
                : '';


            if($is_custom_truss){
                $btn_id = 'add-to-cart-custom-truss-btn';
            }else if($is_panel){
                $btn_id = 'add-to-cart-panel-btn';
            }else if ($is_special_trim) {
                $btn_id = 'add-to-cart-special-trim-btn';
            } else if($is_trim){
                $btn_id = 'add-to-cart-trim-btn';
            }else if($is_screw){
                $btn_id = 'add-to-cart-screw-btn';
            }else if($is_lumber){
                $btn_id = 'add-to-cart-lumber-btn';
            }else if($is_custom_length){
                $btn_id = 'add-to-cart-custom-length-btn';
            }else{
                $btn_id = 'add-to-cart-btn';
            }

            if (!empty($color_id)) {
                $color_name = getColorName($color_id);
                $color_html = '
                    <div class="d-flex justify-content-center mb-0 gap-8 text-center">
                        <a href="javascript:void(0)" id="view_available_color" data-id="'.$row_product['product_id'].'">'.$color_name.'</a>
                    </div>
                ';
            }else{
                $color_html = '
                    <div class="d-flex justify-content-center mb-0 gap-8 text-center">
                        <a href="javascript:void(0)" id="view_available_color" data-id="'.$row_product['product_id'].'">See Colors</a>
                    </div>
                ';
            }

            $tableHTML .= '
            <tr>
                <td class="text-start">
                    <a href="javascript:void(0);" data-id="' . $row_product['product_id'] . '" class="d-flex align-items-center view_product_details">
                        <div class="d-flex align-items-center" >
                            <div class="ms-3">
                                <h6 class="fw-semibold mb-0 fs-4">'. getProductName($row_product['product_id']) .' ' .$dimensions .'</h6>
                            </div>
                        </div>
                    </a>
                </td>
                <td>
                    '.$color_html.'
                </td>
                <td class="text-center">
                    <a href="javascript:void(0);" style="text-decoration: none; color: inherit;" class="mb-0 text-center">'
                        . mb_strimwidth(
                            getColumnFromTable(
                                "product_grade",
                                "product_grade",
                                !empty($grade) ? $grade : $row_product['grade']
                            ),
                            0, 30, '...'
                        ) . '
                    </a>
                </td>

                <td class="text-center">
                    <a href="javascript:void(0);" style="text-decoration: none; color: inherit;" class="mb-0 text-center">'
                        . mb_strimwidth(
                            getColumnFromTable(
                                "product_gauge",
                                "product_gauge",
                                !empty($gauge_id) ? $gauge_id : $row_product['gauge']
                            ),
                            0, 30, '...'
                        ) . '
                    </a>
                </td>

                <td class="text-center">
                    <a href="javascript:void(0);" style="text-decoration: none; color: inherit;" class="mb-0 text-center">'
                        . mb_strimwidth(
                            getColumnFromTable(
                                "product_type",
                                "product_type",
                                !empty($type_id) ? $type_id : $row_product['product_type']
                            ),
                            0, 30, '...'
                        ) . '
                    </a>
                </td>

                <td class="text-center">
                    <a href="javascript:void(0);" style="text-decoration: none; color: inherit;" class="mb-0 text-center">'
                        . mb_strimwidth(
                            getColumnFromTable(
                                "profile_type",
                                "profile_type",
                                !empty($profile_id) ? $profile_id : $row_product['profile']
                            ),
                            0, 30, '...'
                        ) . '
                    </a>
                </td>

                <td>
                    <div class="d-flex justify-content-center align-items-center">'.$stock_text.'</div>
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
    //echo $query_product;
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
    $length = parseNumber(mysqli_real_escape_string($conn, $_POST['length']));

    $length = floor($length);

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['estimate_length'] = !empty($length) ? $length : "";
    }

    echo "Length ID: $product_id, Line: $line, Key: $key, Length: $length";
}

if (isset($_POST['set_estimate_length_inch'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $length_inch = parseNumber(mysqli_real_escape_string($conn, $_POST['length_inch']));

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['estimate_length_inch'] = !empty($length_inch) ? $length_inch : "";
    }
    echo "Length-inch ID: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_panel_type'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);

    $key = $line;
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['panel_type'] = !empty($type) ? $type : "";
    }
    echo "ID: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_panel_style'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $style = mysqli_real_escape_string($conn, $_POST['style']);

    $key = $line;
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['panel_style'] = !empty($style) ? $style : "";
    }
    echo "ID: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_color'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);

    $key = $line;
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['custom_color'] = !empty($color_id) ? $color_id : "";
    }
    echo "Color id: $color_id, Prod id: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_pack'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $pack = mysqli_real_escape_string($conn, $_POST['pack']);

    $key = findCartKey($_SESSION["cart"], $id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['pack'] = !empty($pack) ? $pack : "";
    }
    echo "pack: $pack, Prod id: $id, Line: $line, Key: $key";
}

if (isset($_POST['set_grade'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);

    $key = $line;
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['custom_grade'] = !empty($grade) ? $grade : "";
    }
    echo "grade id: $grade, Prod id: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_gauge'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $gauge = mysqli_real_escape_string($conn, $_POST['gauge']);

    $key = $line;
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['custom_gauge'] = !empty($gauge) ? $gauge : "";
    }
    echo "gauge id: $grade, Prod id: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_screw_length'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $screw_length = mysqli_real_escape_string($conn, $_POST['screw_length']);

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['screw_length'] = !empty($screw_length) ? $screw_length : "";
    }
    echo "screw_length id: $screw_length, Prod id: $product_id, Line: $line, Key: $key";
}

if (isset($_POST['set_screw_type'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $screw_type = mysqli_real_escape_string($conn, $_POST['screw_type']);

    $key = findCartKey($_SESSION["cart"], $product_id, $line);
    if ($key !== false && isset($_SESSION["cart"][$key])) {
        $_SESSION["cart"][$key]['screw_type'] = !empty($screw_type) ? $screw_type : "";
    }
    echo "screw_type id: $screw_type, Prod id: $product_id, Line: $line, Key: $key";
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
                'custom_profile' => $row['custom_profile'],
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
            $custom_profile = intval($item['custom_profile']);
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
                        custom_profile = '$custom_profile',
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
                        estimateid, product_id, product_item, custom_color, custom_grade, custom_profile,
                        custom_width, custom_bend, custom_hem, custom_length, custom_length2,
                        quantity, actual_price, discounted_price, usageid,
                        panel_type, stiff_stand_seam, stiff_board_batten
                    ) VALUES (
                        '$estimate_id', '$product_id', '$product_item', '$custom_color', '$custom_grade', '$custom_profile',
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
    header('Content-Type: application/json');
    $response = [];

    if (!isset($_SESSION['customer_id']) || empty($_SESSION['cart'])) {
        $response['error'] = "Customer ID or cart is not set.";
        echo json_encode($response);
        exit;
    }

    $credit_amt     = floatval($_POST['credit_amt'] ?? 0);
    $cash_amt       = floatval($_POST['cash_amt'] ?? 0);
    $job_id         = intval($_POST['job_id'] ?? 0);
    $job_name       = mysqli_real_escape_string($conn, $_POST['job_name'] ?? '');
    $job_po         = mysqli_real_escape_string($conn, $_POST['job_po'] ?? '');
    $deliver_method = mysqli_real_escape_string($conn, $_POST['deliver_method'] ?? 'pickup');
    $deliver_address= mysqli_real_escape_string($conn, $_POST['deliver_address'] ?? '');
    $deliver_city   = mysqli_real_escape_string($conn, $_POST['deliver_city'] ?? '');
    $deliver_state  = mysqli_real_escape_string($conn, $_POST['deliver_state'] ?? '');
    $deliver_zip    = mysqli_real_escape_string($conn, $_POST['deliver_zip'] ?? '');
    $delivery_amt   = floatval($_POST['delivery_amt'] ?? 0);
    $deliver_fname  = mysqli_real_escape_string($conn, $_POST['deliver_fname'] ?? '');
    $deliver_lname  = mysqli_real_escape_string($conn, $_POST['deliver_lname'] ?? '');
    $pay_type       = mysqli_real_escape_string($conn, $_POST['payment_method'] ?? 'cash');
    $customer_tax   = mysqli_real_escape_string($conn, $_POST['customer_tax'] ?? '');
    $contractor_id  = mysqli_real_escape_string($conn, $_POST['contractor_id'] ?? '');
    $truck          = intval($_POST['truck'] ?? 0);

    $applyStoreCredit = floatval($_POST['applyStoreCredit'] ?? 0);
    $applyJobDeposit  = floatval($_POST['applyJobDeposit'] ?? 0);

    $estimateid  = intval($_SESSION['estimateid'] ?? 0);
    $customerid  = intval($_SESSION['customer_id']);
    $cashierid   = intval($_SESSION['userid']);
    $cart        = $_SESSION['cart'];
    $order_date  = date('Y-m-d H:i:s');

    $customer_details   = getCustomerDetails($customerid);
    $discount_default   = floatval(getCustomerDiscount($customerid)) / 100;
    $store_credit       = floatval($customer_details['store_credit']);
    $charge_net_30      = floatval($customer_details['charge_net_30']);
    $credit_limit       = floatval($customer_details['credit_limit'] ?? 0);
    $credit_total       = getCustomerCreditTotal($customerid);
    $tax_exempt_number  = $customer_details['tax_exempt_number'] ?? '';

    if (!empty($customer_tax)) {
        $tax_rate   = floatval(getCustomerTaxById($customer_tax)) / 100;
        $tax_status = $customer_tax;
    } else {
        $tax_rate   = floatval(getCustomerTaxById($customer_details['tax_status'])) / 100;
        $tax_status = $customer_details['tax_status'];
    }

    $total_price = 0;
    $total_discounted_price = 0;

    foreach ($cart as $item) {
        $calculated = calculateCartItem($item);
        $actual_price = $calculated['product_price'];
        $customer_price = $calculated['customer_price'];
        $discounted_price = $customer_price * (1 + $tax_rate);
        $total_price += $actual_price;
        $total_discounted_price += $discounted_price;
    }

    $token = bin2hex(random_bytes(8));

    $query = "INSERT INTO estimates (
        status, total_price, discounted_price, discount_percent, cashier, cash_amt, credit_amt, 
        estimated_date, order_date, customerid, originalcustomerid, job_name, job_po, 
        deliver_address, deliver_city, deliver_state, deliver_zip, delivery_amt, deliver_method, 
        deliver_fname, deliver_lname, pay_type, tax_status, tax_exempt_number, truck, contractor_id, token
    ) VALUES (
        1, '$total_price', '$total_discounted_price', '".($discount_default * 100)."', '$cashierid',
        '$cash_amt', '$credit_amt', '$order_date', '$order_date', '$customerid', '$customerid',
        '$job_name', '$job_po', '$deliver_address', '$deliver_city', '$deliver_state', '$deliver_zip',
        '$delivery_amt', '$deliver_method', '$deliver_fname', '$deliver_lname', '$pay_type',
        '$tax_status', '$tax_exempt_number', '$truck', '$contractor_id', '$token'
    )";

    if ($conn->query($query) === TRUE) {
        $estimateid = $conn->insert_id;

        foreach ($cart as $item) {
            $calc = calculateCartItem($item);

            $product_id        = $calc['data_id'];
            $product_item      = $item['product_item'] ?? '';
            $quantity          = $calc['quantity'];
            $total_length      = $calc['total_length'];
            $product_price     = $calc['product_price'];
            $customer_price    = $calc['customer_price'];
            $category_id       = $calc['category_id'];
            $color_id          = $calc['color_id'];
            $grade             = $calc['grade'];
            $gauge             = $calc['gauge'];
            $profile           = $calc['profile'];
            $curr_discount     = intval(getCustomerDiscountProfile($customerid));
            $loyalty_discount  = intval(getCustomerDiscountLoyalty($customerid));
            $used_discount     = $item['used_discount'] ?? getCustomerDiscount($customerid);
            $stiff_stand_seam  = $item['stiff_stand_seam'] ?? '0';
            $stiff_board_batten= $item['stiff_board_batten'] ?? '0';
            $panel_type        = $item['panel_type'] ?? '';
            $panel_style       = $item['panel_style'] ?? '';
            $custom_img_src    = $item['custom_trim_src'] ?? '';
            $bundle_id         = $item['bundle_name'] ?? '';
            $note              = $item['note'] ?? '';

            $custom_width      = $item['estimate_width'] ?? $calc['product']['width'];
            $custom_bend       = $item['estimate_bend'] ?? '';
            $custom_hem        = $item['estimate_hem'] ?? '';
            $custom_length     = $item['estimate_length'] ?? 0;
            $custom_length2    = $item['estimate_length_inch'] ?? 0;

            $insert = "INSERT INTO estimate_prod (
                estimateid, product_id, product_item, quantity, custom_width, custom_bend, custom_hem,
                custom_length, custom_length2, actual_price, discounted_price, product_category,
                custom_color, custom_grade, custom_gauge, custom_profile, current_customer_discount, 
                current_loyalty_discount, used_discount, stiff_stand_seam, stiff_board_batten, 
                panel_type, panel_style, custom_img_src, bundle_id, note
            ) VALUES (
                '$estimateid', '$product_id', '$product_item', '$quantity', '$custom_width', '$custom_bend', '$custom_hem',
                '$custom_length', '$custom_length2', '$product_price', '$customer_price', '$category_id',
                '$color_id', '$grade', '$gauge', '$profile', '$curr_discount', '$loyalty_discount', '$used_discount',
                '$stiff_stand_seam', '$stiff_board_batten', '$panel_type', '$panel_style', '$custom_img_src', 
                '$bundle_id', '$note'
            )";

            if (!$conn->query($insert)) {
                $response['error'] = "Error inserting product: " . $conn->error;
                echo json_encode($response);
                exit;
            }
        }

        unset($_SESSION['customer_id']);
        unset($_SESSION['cart']);

        $response['success'] = true;
        $response['estimate_id'] = $estimateid;

    } else {
        $response['error'] = "Error inserting estimate: " . $conn->error;
    }

    echo json_encode($response);
    exit;
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

    $discount = floatval($_POST['discount'] ?? 0);
    $posted_credit_amt = isset($_POST['credit_amt']) ? floatval($_POST['credit_amt']) : null;
    $posted_cash_amt   = isset($_POST['cash_amt']) ? floatval($_POST['cash_amt']) : null;

    $job_id = intval($_POST['job_id'] ?? 0);
    $job_name = mysqli_real_escape_string($conn, $_POST['job_name'] ?? '');
    $job_po = mysqli_real_escape_string($conn, $_POST['job_po'] ?? '');
    $deliver_method = mysqli_real_escape_string($conn, $_POST['deliver_method'] ?? 'pickup');
    $deliver_address = mysqli_real_escape_string($conn, $_POST['deliver_address'] ?? '');
    $deliver_city = mysqli_real_escape_string($conn, $_POST['deliver_city'] ?? '');
    $deliver_state = mysqli_real_escape_string($conn, $_POST['deliver_state'] ?? '');
    $deliver_zip = mysqli_real_escape_string($conn, $_POST['deliver_zip'] ?? '');
    $delivery_amt = floatval($_POST['delivery_amt'] ?? 0);

    $contractor_id = mysqli_real_escape_string($conn, $_POST['contractor_id'] ?? '');
    $truck = intval($_POST['truck'] ?? 0);

    $deliver_fname = mysqli_real_escape_string($conn, $_POST['deliver_fname'] ?? '');
    $deliver_lname = mysqli_real_escape_string($conn, $_POST['deliver_lname'] ?? '');
    $deliver_phone = mysqli_real_escape_string($conn, $_POST['deliver_phone'] ?? '');
    $deliver_email = mysqli_real_escape_string($conn, $_POST['deliver_email'] ?? '');
    $customer_tax = mysqli_real_escape_string($conn, $_POST['customer_tax'] ?? '');
    $tax_exempt_number = mysqli_real_escape_string($conn, $_POST['tax_exempt_number'] ?? '');
    $isAddingCustomer = intval($_POST['isAddingCustomer'] ?? 0);

    $scheduled_date = mysqli_real_escape_string($conn, $_POST['scheduled_date'] ?? '');
    $scheduled_time = mysqli_real_escape_string($conn, $_POST['scheduled_time'] ?? '');

    $applyStoreCredit = floatval($_POST['applyStoreCredit'] ?? 0);
    $applyJobDeposit = floatval($_POST['applyJobDeposit'] ?? 0);

    $pay_cash     = floatval($_POST['pay_cash'] ?? 0);
    $pay_card     = floatval($_POST['pay_card'] ?? 0);
    $pay_check    = floatval($_POST['pay_check'] ?? 0);
    $pay_pickup   = floatval($_POST['pay_pickup'] ?? 0);
    $pay_delivery = floatval($_POST['pay_delivery'] ?? 0);
    $pay_net30    = floatval($_POST['pay_net30'] ?? 0);

    $pay_types = [];
    if ($pay_cash > 0)   $pay_types[] = 'cash';
    if ($pay_card > 0)   $pay_types[] = 'card';
    if ($pay_check > 0)  $pay_types[] = 'check';
    if ($pay_pickup > 0) $pay_types[] = 'pickup';
    if ($pay_delivery > 0) $pay_types[] = 'delivery';
    if ($pay_net30 > 0)  $pay_types[] = 'net30';
    $pay_type_label = implode(',', $pay_types);

    $payments_cash_like   = $pay_cash + $pay_card + $pay_check;
    $payments_credit_like = $pay_pickup + $pay_delivery + $pay_net30;

    if (empty($scheduled_date) && empty($scheduled_time)) {
        $scheduled_datetime = '';
    } else {
        if (empty($scheduled_date) && !empty($scheduled_time)) $scheduled_date = date('Y-m-d');
        if (!empty($scheduled_date) && empty($scheduled_time)) $scheduled_time = '06:00';
        $scheduled_datetime = date('Y-m-d H:i:s', strtotime("$scheduled_date $scheduled_time"));
    }

    $customer_id = $_SESSION['customer_id'] ?? null;
    if (empty($customer_id)) {
        if ($isAddingCustomer == 1) {
            $sql = "INSERT INTO customer (
                        customer_first_name, customer_last_name, contact_email, contact_phone, tax_status, tax_exempt_number, created_at
                    ) VALUES (
                        '$deliver_fname', '$deliver_lname', '$deliver_email', '$deliver_phone', '$customer_tax', '$tax_exempt_number', NOW()
                    )";
            if (mysqli_query($conn, $sql)) {
                $customer_id = mysqli_insert_id($conn);
                $_SESSION['customer_id'] = $customer_id;
            } else {
                $response['error'] = "Failed to add new customer: " . mysqli_error($conn);
                echo json_encode($response);
                exit;
            }
        } else {
            $response['error'] = "Customer ID is not set and not adding new customer.";
            echo json_encode($response);
            exit;
        }
    }

    if (!isset($customer_id) || empty($_SESSION['cart'])) {
        $response['error'] = "Customer ID or cart is not set.";
        echo json_encode($response);
        exit;
    }

    $estimateid = intval($_SESSION['estimateid'] ?? 0);
    $customerid = intval($_SESSION['customer_id']);
    $cashierid = intval($_SESSION['userid']);
    $station_id = intval($_SESSION['station'] ?? 0);
    $cart = $_SESSION['cart'];
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

    $tax_exempt_number = $customer_details['tax_exempt_number'];
    $credit_limit = $customer_details['credit_limit'] ?? 0;
    $credit_total = getCustomerCreditTotal($customerid);

    $total_price = 0;
    $total_discounted_price = 0;
    $pre_orders = [];
    foreach ($cart as $item) {
        $calculated = calculateCartItem($item);
        $actual_price = $calculated['product_price'];
        $customer_price = $calculated['customer_price'];
        $discounted_price = $customer_price * (1 + $tax_rate);
        $total_price += $actual_price;
        $total_discounted_price += $discounted_price;
    }

    $original_cash_amt = $payments_cash_like;
    $original_credit_amt = $payments_credit_like;

    $store_credit = floatval($customer_details['store_credit']);
    $charge_net_30 = floatval($customer_details['charge_net_30']);

    // ---- Net30 approval (if net30 was used) ----
    if ($pay_net30 > 0 && $charge_net_30 < $total_discounted_price) {
        $result = createNet30Approval(
            $customerid,
            $cashierid,
            'net30',
            $charge_net_30,
            ['job_po' => $job_po, 'job_name' => $job_name],
            [
                'deliver_address' => $deliver_address,
                'deliver_city'    => $deliver_city,
                'deliver_state'   => $deliver_state,
                'deliver_zip'     => $deliver_zip,
                'delivery_amt'    => $delivery_amt,
                'deliver_fname'   => $deliver_fname,
                'deliver_lname'   => $deliver_lname
            ],
            $tax_rate,
            $discount_default
        );
        if (!$result['success']) {
            echo json_encode($result);
            exit;
        }
    }

    $credit_to_apply = 0;
    $job_deposit_applied = 0;
    $new_store_credit = $store_credit;

    if ($applyStoreCredit && $store_credit > 0) {
        $credit_to_apply = min($store_credit, $original_cash_amt);
        $new_store_credit = $store_credit - $credit_to_apply;
        $collected_cash_after_store = $original_cash_amt - $credit_to_apply;
    } else {
        $collected_cash_after_store = $original_cash_amt;
    }

    $job_balance = getJobDepositTotal($job_id);
    if ($applyJobDeposit && $job_balance > 0) {
        $job_deposit_applied = min($job_balance, $collected_cash_after_store);
        $collected_cash_after_store -= $job_deposit_applied;
    }

    $cash_amt   = max(0, $collected_cash_after_store);
    $credit_amt = $original_credit_amt;

    if ($cash_amt == 0 && $credit_amt == 0 && $applyJobDeposit) {
        $pay_type_label = 'job_deposit';
    } else {
        $pay_type_label = $pay_type_label;
    }

    $check_no = mysqli_real_escape_string($conn, $_POST['check_no'] ?? '');
    $check_number = (!empty($check_no) && $pay_check > 0) ? "'" . mysqli_real_escape_string($conn, $check_no) . "'" : "NULL";

    $token = bin2hex(random_bytes(8));

    $discount_percent = ($discount * 100);
    $sql_insert = "
        INSERT INTO orders (
            estimateid, cashier, station, total_price, discounted_price, discount_percent,
            order_date, scheduled_date, customerid, originalcustomerid,
            cash_amt, credit_amt, job_name, job_po,
            deliver_address, deliver_city, deliver_state, deliver_zip,
            delivery_amt, deliver_method, deliver_fname, deliver_lname,
            pay_type, pay_cash, pay_card, pay_check, pay_pickup, pay_delivery, pay_net30,
            tax_status, tax_exempt_number, truck, contractor_id, token
        ) VALUES (
            '$estimateid', '$cashierid', '$station_id', '$total_price', '$total_discounted_price', '$discount_percent',
            '$order_date', '$scheduled_datetime', '$customerid', '$customerid',
            '$cash_amt', '$credit_amt', '$job_name', '$job_po',
            '$deliver_address', '$deliver_city', '$deliver_state', '$deliver_zip',
            '$delivery_amt', '$deliver_method', '$deliver_fname', '$deliver_lname',
            '".mysqli_real_escape_string($conn,$pay_type_label)."',
            '$pay_cash', '$pay_card', '$pay_check', '$pay_pickup', '$pay_delivery', '$pay_net30',
            '$tax_status', '$tax_exempt_number', '$truck', '$contractor_id', '$token'
        )
    ";

    if ($conn->query($sql_insert) === TRUE) {
        $orderid = $conn->insert_id;

        $baseUrl = "https://delivery.ilearnsda.com/receipt.php";
        $url = $baseUrl . "?prod=" . urlencode($token);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_exec($ch);
        curl_close($ch);

        addPoints($customerid, $orderid);

        $po_number = mysqli_real_escape_string($conn, $job_po);
        $created_by = mysqli_real_escape_string($conn, $cashierid);
        $reference_no = mysqli_real_escape_string($conn, $orderid);
        $description = 'Materials Purchased';

        if ($pay_net30 > 0) {
            $net30_applied = min($charge_net_30, $pay_net30);
            if ($net30_applied > 0) {
                $new_charge_net_30 = $charge_net_30 - $net30_applied;
                $update_sql = "UPDATE customer SET charge_net_30 = $new_charge_net_30 WHERE customer_id = $customerid";
                if (!mysqli_query($conn, $update_sql)) {
                    $response['error'] = 'Update Error: ' . mysqli_error($conn);
                }

                $insert_sql = "
                    INSERT INTO customer_net30_history (customer_id, credit_amount, credit_type, reference_type, reference_id, created_at)
                    VALUES ($customerid, $net30_applied, 'use', 'order', $orderid, NOW())
                ";
                if (!mysqli_query($conn, $insert_sql)) {
                    $response['error'] = 'Ledger Insert Error: ' . mysqli_error($conn);
                }
            }
        }

        if ($applyStoreCredit && $credit_to_apply > 0) {
            $update_sql = "UPDATE customer SET store_credit = $new_store_credit WHERE customer_id = $customerid";
            if (!mysqli_query($conn, $update_sql)) {
                $response['error'] = 'Update Error: ' . mysqli_error($conn);
            }

            $insert_sql = "
                INSERT INTO customer_store_credit_history (customer_id, credit_amount, credit_type, reference_type, reference_id, created_at)
                VALUES ($customerid, $credit_to_apply, 'use', 'order', $orderid, NOW())
            ";
            if (!mysqli_query($conn, $insert_sql)) {
                $response['error'] = 'Ledger Insert Error: ' . mysqli_error($conn);
            }
        }

        if ($applyJobDeposit && $job_deposit_applied > 0) {
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
                    if ($remaining_to_apply <= 0) break;
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

        if ($pay_cash > 0) {
            $amt = number_format($pay_cash, 2, '.', '');
            $sql = "
                INSERT INTO job_ledger (job_id, customer_id, entry_type, amount, po_number, reference_no, description, check_number, created_by, created_at, payment_method)
                VALUES ('$job_id', '$customerid', 'usage', '$amt', '$po_number', '$reference_no', '$description', NULL, '$created_by', NOW(), 'cash')
            ";
            mysqli_query($conn, $sql);
            recordCashInflow('cash', 'sales_payment', $pay_cash);
        }
        if ($pay_card > 0) {
            $amt = number_format($pay_card, 2, '.', '');
            $sql = "
                INSERT INTO job_ledger (job_id, customer_id, entry_type, amount, po_number, reference_no, description, check_number, created_by, created_at, payment_method)
                VALUES ('$job_id', '$customerid', 'usage', '$amt', '$po_number', '$reference_no', '$description', NULL, '$created_by', NOW(), 'card')
            ";
            mysqli_query($conn, $sql);
            recordCashInflow('card', 'sales_payment', $pay_card);
        }
        if ($pay_check > 0) {
            $amt = number_format($pay_check, 2, '.', '');
            $sql = "
                INSERT INTO job_ledger (job_id, customer_id, entry_type, amount, po_number, reference_no, description, check_number, created_by, created_at, payment_method)
                VALUES ('$job_id', '$customerid', 'usage', '$amt', '$po_number', '$reference_no', '$description', $check_number, '$created_by', NOW(), 'check')
            ";
            mysqli_query($conn, $sql);
            recordCashInflow('check', 'sales_payment', $pay_check);
        }

        if ($pay_pickup > 0) {
            $amt = number_format($pay_pickup, 2, '.', '');
            $sql = "
                INSERT INTO job_ledger (job_id, customer_id, entry_type, amount, po_number, reference_no, description, check_number, created_by, created_at, payment_method)
                VALUES ('$job_id', '$customerid', 'credit', '$amt', '$po_number', '$reference_no', 'Pickup Order', NULL, '$created_by', NOW(), 'pickup')
            ";
            mysqli_query($conn, $sql);
        }
        if ($pay_delivery > 0) {
            $amt = number_format($pay_delivery, 2, '.', '');
            $sql = "
                INSERT INTO job_ledger (job_id, customer_id, entry_type, amount, po_number, reference_no, description, check_number, created_by, created_at, payment_method)
                VALUES ('$job_id', '$customerid', 'credit', '$amt', '$po_number', '$reference_no', 'Delivery Order', NULL, '$created_by', NOW(), 'delivery')
            ";
            mysqli_query($conn, $sql);
        }
        if ($pay_net30 > 0) {
            $amt = number_format($pay_net30, 2, '.', '');
            $sql = "
                INSERT INTO job_ledger (job_id, customer_id, entry_type, amount, po_number, reference_no, description, check_number, created_by, created_at, payment_method)
                VALUES ('$job_id', '$customerid', 'credit', '$amt', '$po_number', '$reference_no', 'Net30 Charge', NULL, '$created_by', NOW(), 'net30')
            ";
            mysqli_query($conn, $sql);
        }

        foreach ($cart as $item) {
            $calc = calculateCartItem($item);

            $product_id   = $calc['data_id'];
            $product_item = $item['product_item'] ?? '';
            $quantity     = $calc['quantity'];
            $total_length = $calc['total_length'];
            $unit_price   = $calc['unit_price'];
            $product_price   = $calc['product_price'];
            $customer_price  = $calc['customer_price'];
            $amount_discount = $calc['amount_discount'];
            $category_id     = $calc['category_id'];
            $color_id        = $calc['color_id'];
            $grade           = $calc['grade'];
            $gauge           = $calc['gauge'];
            $profile         = $calc['profile'];
            $discount_item        = $calc['discount'];

            $stiff_stand_seam  = $item['stiff_stand_seam'] ?? '0';
            $stiff_board_batten = $item['stiff_board_batten'] ?? '0';
            $panel_type        = $item['panel_type'] ?? '';
            $panel_style       = $item['panel_style'] ?? '';
            $custom_img_src    = $item['custom_trim_src'] ?? '';
            $bundle_id         = $item['bundle_name'] ?? '';
            $note              = $item['note'] ?? '';
            $screw_length      = $item['screw_length'] ?? '';

            $curr_discount    = intval(getCustomerDiscountProfile($customerid));
            $loyalty_discount = intval(getCustomerDiscountLoyalty($customerid));
            $used_discount    = $item['used_discount'] ?? getCustomerDiscount($customerid);

            $product_id_abbrev = $calc['unique_prod_id'];

            $query = "INSERT INTO order_product (
                orderid, productid, product_item, quantity, custom_width, custom_bend, custom_hem,
                custom_length, custom_length2, actual_price, discounted_price, product_category,
                custom_color, custom_grade, custom_gauge, custom_profile, current_customer_discount, current_loyalty_discount,
                used_discount, stiff_stand_seam, stiff_board_batten, panel_type, panel_style, custom_img_src, bundle_id, note,
                product_id_abbrev, screw_length
            ) VALUES (
                '$orderid', '$product_id', '$product_item', '$quantity', '" . ($item['estimate_width'] ?? $calc['product']['width']) . "',
                '" . ($item['estimate_bend'] ?? '') . "', '" . ($item['estimate_hem'] ?? '') . "', '$total_length', '" . ($item['estimate_length_inch'] ?? 0) . "',
                '$product_price', '$customer_price', '$category_id',
                '$color_id', '$grade', '$gauge', '$profile', '$curr_discount', '$loyalty_discount',
                '$used_discount', '$stiff_stand_seam', '$stiff_board_batten', '$panel_type', '$panel_style', '$custom_img_src', '$bundle_id', '$note',
                '$product_id_abbrev', '$screw_length'
            )";

            if ($conn->query($query) !== TRUE) {
                die("Error: " . $conn->error);
            }

            $order_prod_id = $conn->insert_id;

            if ($calc['product']['product_origin'] == 2) {
                $query = "INSERT INTO work_order (
                    work_order_id, work_order_product_id, productid, product_item,
                    quantity, custom_width, custom_bend, custom_hem, custom_length, custom_length2,
                    actual_price, discounted_price, product_category, custom_color, custom_grade, custom_gauge, custom_profile,
                    current_customer_discount, current_loyalty_discount, used_discount, stiff_stand_seam, stiff_board_batten, panel_type,
                    panel_style, custom_img_src, user_id
                ) VALUES (
                    '$orderid', '$order_prod_id', '$product_id', '$product_item',
                    '$quantity', '" . ($item['estimate_width'] ?? $calc['product']['width']) . "', '" . ($item['estimate_bend'] ?? '') . "', '" . ($item['estimate_hem'] ?? '') . "',
                    '$total_length', '" . ($item['estimate_length_inch'] ?? 0) . "',
                    '$product_price', '$customer_price', '$category_id', '$color_id', '$grade', '$gauge', '$profile',
                    '$curr_discount', '$loyalty_discount', '$used_discount', '$stiff_stand_seam', '$stiff_board_batten', '$panel_type',
                    '$panel_style', '$custom_img_src', '$cashierid'
                )";

                if ($conn->query($query) !== TRUE) {
                    die("Error: " . $conn->error);
                }
            }

            if (($item['is_pre_order'] ?? '0') == '1') {
                $insert_query = "INSERT INTO product_preorder (
                    product_id, product_category, color, grade, gauge
                ) VALUES (
                    '$product_id', '$category_id', '$color_id', '$grade', '$gauge'
                )";
                mysqli_query($conn, $insert_query);
                $pre_orders[] = $product_id;
            }

            $current_stock = getProductStockTotal($product_id);
            if ($current_stock < 1) {
                $list_items = '<ul style="list-style-type: none; padding-left: 0;">';
                foreach ([
                    'product_item' => $product_item,
                    'product_category' => ucwords(getProductCategoryName($category_id)),
                    'color' => getColorName($color_id),
                    'grade' => getGradeName($grade),
                    'gauge' => getGaugeName($gauge)
                ] as $key => $value) {
                    $label = ucfirst(str_replace('_', ' ', $key));
                    $list_items .= "<li style='margin-bottom: 8px;'><span style='display: inline-block; min-width: 140px; font-weight: bold; color: #333;'>$label:</span><span style='color: #555;'>".htmlspecialchars($value)."</span></li>";
                }
                $list_items .= '</ul>';

                $subject = "Out of stock Product has been Ordered!";
                $response_email = $emailSender->sendOutOfStockEmail($admin_email, $subject, $list_items);

                $actorId = $cashierid;
                $actionType = 'no_stock_order';
                $targetId = $orderid;
                $targetType = "No Stock Order";
                $message = "Order #$orderid has out-of-stock items ordered";
                $url = '?page=order_list';
                createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);
            }
        }

        if (!empty($pre_orders)) {
            $list_items = '<ul style="list-style-type: none; padding-left: 0;">';
            foreach ($pre_orders as $prod) {
                $list_items .= "<li style='margin-bottom: 8px;'><span style='display: inline-block; min-width: 140px; font-weight: bold; color: #333;'>Product ID:</span><span style='color: #555;'>".htmlspecialchars($prod)."</span></li>";
            }
            $list_items .= '</ul>';

            $subject ="Out of Stock items has been preordered";
            $resp = $emailSender->sendPreOrderEmail($admin_email, $subject, $list_items);
            if (!$resp['success']) {
                $response['error'] = "Failed to send mail. " . ($resp['error'] ?? '');
            }

            $actorId = $cashierid;
            $actionType = 'pre_order';
            $targetId = $orderid;
            $targetType = "Pre-Order";
            $message = "Order #$orderid has items pre-ordered";
            $url = '?page=order_list';
            $recipientIds = getAdminIDs();
            createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);
        }

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
            $response['customer_id'] = $_SESSION['customer_id'];

            unset($_SESSION['cart']);
        } else {
            $response['message'] = "Error inserting order estimate records: " . $conn->error;
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
            'custom_profile' => isset($item['custom_profile']) ? intval($item['custom_profile']) : 'NULL',
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
            'panel_type' => intval($item['panel_type'] ?? 0),
            'panel_style' => intval($item['panel_style'] ?? 0)
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
                {$p['custom_color']}, {$p['custom_grade']}, {$p['custom_profile']}, '{$p['custom_width']}', {$p['custom_height']}, {$p['custom_bend']}, {$p['custom_hem']},
                {$p['custom_length']}, {$p['custom_length2']}, '{$p['actual_price']}', '{$p['discounted_price']}', '{$p['product_category']}',
                '{$p['usageid']}', {$p['current_customer_discount']}, {$p['current_loyalty_discount']}, {$p['used_discount']},
                '{$p['stiff_stand_seam']}', '{$p['stiff_board_batten']}', '{$p['panel_type']}', '{$p['panel_style']}'
            )";
        }

        $insert_products = "INSERT INTO approval_product (
            approval_id, productid, product_item, status, quantity, custom_color, custom_grade, custom_profile, custom_width,
            custom_height, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price,
            product_category, usageid, current_customer_discount, current_loyalty_discount,
            used_discount, stiff_stand_seam, stiff_board_batten, panel_type, panel_style
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

    $actorId = $cashierid;
    $actor_name = get_staff_name($actorId);
    $actionType = 'request_approval';
    $targetId = $approval_id;
    $targetType = "Request Approval(Discount)";
    $message = "Approval #$targetId requested for Discount Purposes";
    $url = '?page=approval_list';
    $recipientIds = getAdminIDs();
    createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);

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
    $id           = mysqli_real_escape_string($conn, $_POST['id']);
    $price        = floatval($_POST['price']);
    $drawing_data = mysqli_real_escape_string($conn, $_POST['drawing_data'] ?? '');
    $img_src      = mysqli_real_escape_string($conn, $_POST['img_src'] ?? '');
    $is_pre_order = (int)($_POST['is_pre_order'] ?? 0);
    $is_custom    = (int)($_POST['is_custom'] ?? 0);

    $color          = trim($_POST['color'] ?? '');
    $grade          = trim($_POST['grade'] ?? '');
    $gauge          = trim($_POST['gauge'] ?? '');
    $profile        = trim($_POST['profile'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $trim_no        = trim($_POST['trim_no'] ?? '');

    $width      = floatval($_POST['width'] ?? '');
    $hem        = floatval($_POST['hem'] ?? '');
    $bend       = floatval($_POST['bend'] ?? '');

    $quantities = $_POST['quantity'] ?? [];
    $lengths    = $_POST['length'] ?? [];
    $length_ids = $_POST['dimension_ids'] ?? [];
    $notes      = $_POST['notes'] ?? [];

    if (is_string($length_ids)) {
        $length_ids = json_decode($length_ids, true);
    }

    if (!isset($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
        $_SESSION["cart"] = [];
    }

    $result = mysqli_query($conn, "SELECT * FROM product WHERE product_id = '$id'");
    if (!$result || mysqli_num_rows($result) == 0) {
        echo json_encode(['error' => "Trim Product not available"]);
        exit;
    }
    $row = mysqli_fetch_assoc($result);

    foreach ($quantities as $i => $quantity) {
        $quantity  = floatval($quantity);
        $length    = floatval($lengths[$i] ?? 0);
        $length_id = intval($length_ids[$i] ?? 0);
        $note      = trim($notes[$i] ?? '');

        if(!empty($description)){
            $note = $description .', ' .$note;
        }

        if ($quantity <= 0) continue;

        $feet   = intval(floor($length));
        $inches = intval(round(($length - $feet) * 12));
        if ($inches >= 12) { $feet++; $inches = 0; }

        $foundKey = null;
        foreach ($_SESSION["cart"] as $key => $item) {
            if (
                intval($item['product_id']) == intval($row['product_id']) &&
                trim($item['custom_color'] ?? '') === $color &&
                trim($item['custom_grade'] ?? '') === $grade &&
                trim($item['custom_gauge'] ?? '') === $gauge &&
                trim($item['width'] ?? '') === $width &&
                trim($item['hem'] ?? '') === $hem &&
                trim($item['bend'] ?? '') === $bend &&
                trim($item['note'] ?? '') === $note &&
                intval($item['estimate_length']) === $feet &&
                intval($item['estimate_length_inch']) === $inches
            ) {
                $foundKey = $key;
                break;
            }
        }

        if ($foundKey !== null) {
            $_SESSION["cart"][$foundKey]['quantity_cart'] += $quantity;
        } else {
            $newLine = empty($_SESSION['cart']) ? 1 : (max(array_keys($_SESSION['cart'])) + 1);
            $_SESSION["cart"][$newLine] = [
                'product_id'            => $row['product_id'],
                'product_item'          => $row['product_item'],
                'unit_price'            => $price,
                'line'                  => $newLine,
                'quantity_cart'         => $quantity,
                'quantity_in_stock'     => getProductStockTotal($row['product_id']),
                'estimate_length'       => $feet,
                'estimate_length_inch'  => $inches,
                'length_id'             => $length_id,
                'custom_color'          => $color,
                'custom_grade'          => $grade,
                'custom_gauge'          => $gauge,
                'custom_profile'        => $profile ?: getLastValue($row['profile']),
                'width'                 => $width,
                'hem'                   => $hem,
                'bend'                  => $bend,
                'note'                  => $note,
                'weight'                => $row['weight'],
                'usage'                 => 0,
                'supplier_id'           => '',
                'estimate_width'        => 0,
                'is_pre_order'          => $is_pre_order,
                'is_custom'             => $is_custom,
                'custom_trim_src'       => $img_src,
                'trim_no'               => $trim_no,
                'drawing_data'          => $drawing_data
            ];
        }
    }

    echo json_encode(['success' => true]);
}

if (isset($_POST['save_custom_length'])) {
    $id   = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line'] ?? 1);
    $profile = mysqli_real_escape_string($conn, $_POST['profile'] ?? 0);

    $quantities  = $_POST['quantity'] ?? [];
    $feet_list   = $_POST['length_feet'] ?? [];
    $inch_list   = $_POST['length_inch'] ?? [];
    $prices      = $_POST['price'] ?? [];
    $color_id    = $_POST['color_id'] ?? [];
    $dimension_id    = $_POST['dimension_id'] ?? [];
    $pack_arr    = $_POST['pack'] ?? [];
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
            $custom_color       = intval($color_id);
            $dimension          = intval($dimension_id[$idx] ?? 0);
            $pack          = $pack_arr[$idx] ?? 0;
            $note               = $notes[$idx] ?? '';

            $dimension_value = '';
            if(!empty($dimension)){
                $dimension_details = getDimensionDetails($dimension);
                $dimension_value = $dimension_details['dimension'];
            }
            
            if ($quantity <= 0) continue;

            $found = false;
            foreach ($_SESSION["cart"] as &$item) {
                if (
                    $item['product_id'] == $id &&
                    $item['estimate_length'] == $estimate_length &&
                    $item['estimate_length_inch'] == $estimate_length_in &&
                    $item['custom_profile'] == $profile &&
                    $item['custom_color'] == $custom_color &&
                    $item['dimension'] == $dimension_value &&
                    $item['pack'] == $pack
                ) {
                    $item['quantity_cart'] += $quantity;
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
                    'screw_length'        => $dimension_value,
                    'screw_type'          => 'SD',
                    'weight'              => 0,
                    'supplier_id'         => '',
                    'custom_grade'        => '',
                    'custom_profile'      => !empty($profile) ? $profile : getLastValue($row['profile']),
                    'custom_gauge'        => '',
                    'note'                => $note,
                    'pack'                => $pack
                );

                $_SESSION["cart"][] = $item_array;
            }
        }
    } else {
        echo json_encode(['error' => "Trim Product not available"]);
        exit;
    }

    echo json_encode(['success' => print_r($_SESSION["cart"])]);
}

if (isset($_POST['save_lumber'])) {
    $id   = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line'] ?? 1);
    $profile = mysqli_real_escape_string($conn, $_POST['profile'] ?? 0);

    $quantities  = $_POST['quantity'] ?? [];
    $prices      = $_POST['price'] ?? [];
    $color_id    = $_POST['color_id'] ?? [];
    $dimension_id    = $_POST['dimension_id'] ?? [];
    $pack_arr    = $_POST['pack'] ?? [];
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
            $price              = floatval($prices[$idx] ?? 0);
            $custom_color       = intval($color_id);
            $dimension          = intval($dimension_id[$idx] ?? 0);
            $pack          = $pack_arr[$idx] ?? 0;
            $note               = $notes[$idx] ?? '';

            $dimension_value = '';
            if(!empty($dimension)){
                $dimension_details = getDimensionDetails($dimension);
                $dimension_value = $dimension_details['dimension'];

                $feet  = floatval($dimension_details['dimension_feet'] ?? 0);
                $inch  = floatval($dimension_details['dimension_inches'] ?? 0);
            }
            
            if ($quantity <= 0) continue;

            $found = false;
            foreach ($_SESSION["cart"] as &$item) {
                if (
                    $item['product_id'] == $id &&
                    $item['estimate_length'] == $feet &&
                    $item['estimate_length_inch'] == $inch &&
                    $item['custom_profile'] == $profile &&
                    $item['custom_color'] == $custom_color &&
                    $item['dimension'] == $dimension_value &&
                    $item['pack'] == $pack
                ) {
                    $item['quantity_cart'] += $quantity;
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
                    'estimate_length'     => $feet,
                    'estimate_length_inch'=> $inch,
                    'usage'               => 0,
                    'custom_color'        => $custom_color,
                    'screw_length'        => $dimension_value,
                    'screw_type'          => 'SD',
                    'weight'              => 0,
                    'supplier_id'         => '',
                    'custom_grade'        => '',
                    'custom_profile'      => !empty($profile) ? $profile : getLastValue($row['profile']),
                    'custom_gauge'        => '',
                    'note'                => $note,
                    'pack'                => $pack
                );

                $_SESSION["cart"][] = $item_array;
            }
        }
    } else {
        echo json_encode(['error' => "Trim Product not available"]);
        exit;
    }

    echo json_encode(['success' => print_r($_SESSION["cart"])]);
}

if (isset($_POST['save_screw'])) {
    $id   = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line'] ?? 1);
    $profile = mysqli_real_escape_string($conn, $_POST['profile'] ?? 0);

    $quantities  = $_POST['quantity'] ?? [];
    $feet_list   = $_POST['length_feet'] ?? [];
    $inch_list   = $_POST['length_inch'] ?? [];
    $color_id    = $_POST['color_id'] ?? [];
    $dimension_id    = $_POST['dimension_id'] ?? [];
    $pack_arr    = $_POST['pack'] ?? [];
    $notes       = $_POST['notes'] ?? [];

    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = array();
    }

    $query = "SELECT * FROM product WHERE product_id = '$id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $bulk_starts = $row['bulk_starts_at'] ?? 1;

        foreach ($quantities as $idx => $qty) {
            $quantity           = floatval($qty);
            $estimate_length    = 1;
            $estimate_length_in = 0;
            $custom_color       = intval($color_id);
            $dimension          = intval($dimension_id[$idx] ?? 0);
            $pack_id            = $pack_arr[$idx] ?? '';
            $note               = $notes[$idx] ?? '';

            $packPieces = getPackPieces($pack_id);
            $pack = ($packPieces < 1) ? 1 : $packPieces;
            $packName = getPackName($pack_id);

            $res = mysqli_query($conn, "SELECT * FROM product_screw_lengths WHERE product_id = '$id' AND dimension_id = '$dimension' LIMIT 1");
            $row_length = mysqli_fetch_assoc($res);

            $unit_price  = floatval($row_length['unit_price'] ?? 0);
            $bulk_price  = floatval($row_length['bulk_price'] ?? 0);

            if ($bulk_price > 0 && $qty >= $bulk_starts) {
                $unit_price = $bulk_price;
            }

            $dimension_value = '';
            if(!empty($dimension)){
                $dimension_details = getDimensionDetails($dimension);
                $dimension_value = $dimension_details['dimension'];
            }
            
            if ($quantity <= 0) continue;

            $found = false;
            foreach ($_SESSION["cart"] as &$item) {
                if (
                    $item['product_id'] == $id &&
                    $item['estimate_length'] == $estimate_length &&
                    $item['estimate_length_inch'] == $estimate_length_in &&
                    $item['custom_profile'] == $profile &&
                    $item['custom_color'] == $custom_color &&
                    $item['dimension'] == $dimension_value &&
                    $item['pack'] == $pack
                ) {
                    $item['quantity_cart'] += $quantity;
                    $found = true;
                    break;
                }
            }
            unset($item);

            if (!$found) {
                $line_to_use = (count($_SESSION["cart"]) > 0) ? max(array_column($_SESSION["cart"], 'line')) + 1 : 1;

                $item_array = array(
                    'product_id'          => $row['product_id'],
                    'product_item'        => $row['product_item'] . (!empty($packName) ? " ($packName)" : ""),
                    'unit_price'          => $unit_price,
                    'line'                => $line_to_use,
                    'quantity_ttl'        => getProductStockTotal($row['product_id']),
                    'quantity_in_stock'   => 0,
                    'quantity_cart'       => $quantity,
                    'estimate_width'      => 0,
                    'estimate_length'     => $estimate_length,
                    'estimate_length_inch'=> $estimate_length_in,
                    'usage'               => 0,
                    'custom_color'        => $custom_color,
                    'screw_length'        => $dimension_value,
                    'screw_type'          => 'SD',
                    'weight'              => 0,
                    'supplier_id'         => '',
                    'custom_grade'        => '',
                    'custom_profile'      => !empty($profile) ? $profile : getLastValue($row['profile']),
                    'custom_gauge'        => '',
                    'note'                => $note,
                    'pack'                => $pack
                );

                $_SESSION["cart"][] = $item_array;
            }
        }
    } else {
        echo json_encode(['error' => "Trim Product not available"]);
        exit;
    }

    echo json_encode(['success' => print_r($_SESSION["cart"])]);
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
    $quantity = floatval($_POST['quantity']);
    $stock_fee_percent = floatval($_POST['stock_fee']) / 100;
    $pay_method = mysqli_real_escape_string($conn, $_POST['pay_method'] ?? '');

    $query = "SELECT op.*, o.order_date, o.customerid, o.originalcustomerid 
              FROM order_product AS op
              LEFT JOIN orders AS o ON o.orderid = op.orderid
              WHERE op.id = '$id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $order = mysqli_fetch_assoc($result);
        $available_quantity = floatval($order['quantity']);
        $productid = $order['productid'];
        $product_details = getProductDetails($productid);
        $product_origin = intval($product_details['product_origin']);
        $orderid = $order['orderid'];
        $customer_id = $order['originalcustomerid'] ?: $order['customerid'];

        if ($quantity > $available_quantity) {
            echo "Quantity entered exceeds the purchased count!";
            exit;
        }

        $status = $product_origin === 1 ? 1 : 0;

        $insert_query = "INSERT INTO product_returns 
                         (orderid, productid, status, quantity, custom_color, custom_width, custom_height, custom_bend, custom_hem, custom_length, custom_length2, actual_price, discounted_price, product_category, usageid, stock_fee)
                         VALUES 
                         (
                            '{$order['orderid']}', 
                            '{$order['productid']}', 
                            '$status', 
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

            $new_quantity = max(0, $available_quantity - $quantity);
            $update_order_product = "UPDATE order_product SET quantity = '$new_quantity' WHERE id = '$id'";
            mysqli_query($conn, $update_order_product);

            $amount = $quantity * floatval($order['discounted_price']);
            $stock_fee = $amount * $stock_fee_percent;
            $amount_returned = $amount - $stock_fee;

            if ($pay_method === 'store_credit') {
                $credit_update = "
                    UPDATE customer 
                    SET store_credit = store_credit + $amount_returned
                    WHERE customer_id = '$customer_id'
                ";
                mysqli_query($conn, $credit_update);

                $credit_history = "
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
                        'Refund via store credit (return)',
                        NOW()
                    )
                ";
                mysqli_query($conn, $credit_history);

            } else {
                recordCashOutflow($pay_method, 'product_return', $amount_returned);
            }

            if ($status === 0) {
                $actorId = $_SESSION['userid'];
                $actor_name = get_staff_name($actorId);
                $actionType = 'return_manufactured';
                $targetId = $return_id;
                $targetType = 'Return Approval';
                $message = "New Manufactured product Return approval Request";
                $url = '?page=returns_list_pending';
                createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);
            }

            echo "success|$return_id";
        } else {
            echo "Error inserting into product_returns.";
        }
    } else {
        echo "Error: Order not found.";
    }
}

if (isset($_POST['return_approval_product'])) {
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
        $productid = $order['productid'];
        $product_details = getProductDetails($productid);
        $product_origin = $product_details['product_origin'];

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
                            '0', 
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

            if ($product_origin == 1) {
                $actorId = $_SESSION['userid'];
                $actor_name = get_staff_name($actorId);
                $actionType = 'return_stockable';
                $targetId = $return_id;
                $targetType = 'Return Stockable';
                $message = "New Stockable product Return approval Request";
                $url = '?page=returns_list_pending';
                createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);
            }else{
                $actorId = $_SESSION['userid'];
                $actor_name = get_staff_name($actorId);
                $actionType = 'return_manufactured';
                $targetId = $return_id;
                $targetType = 'Return Approval';
                $message = "New Manufactured product Return approval Request";
                $url = '?page=returns_list_pending';
                createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);
            }
            
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
    $quantity       = $_POST['quantity_product'] ?? [];
    $quantity       = array_map(fn($qty) => empty($qty) ? 0 : $qty, $quantity);

    $lengthFeet         = $_POST['length_feet'] ?? [];
    $lengthInch         = $_POST['length_inch'] ?? [];
    $panel_types        = $_POST['panel_option'] ?? [];
    $panel_styles       = $_POST['panel_style'] ?? [];
    $panel_drip_stops   = $_POST['panel_drip_stop'] ?? [];
    $bundle_names       = $_POST['bundle_name'] ?? [];
    $notes              = $_POST['notes'] ?? [];

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

    // Step 1: Merge duplicates within the same POST submission
    $mergedItems = [];

    foreach ($quantity as $index => $qty) {
        $length_feet = isset($lengthFeet[$index]) ? parseNumber($lengthFeet[$index]) : 0;
        $length_inch = isset($lengthInch[$index]) ? parseNumber($lengthInch[$index]) : 0;
        $note   = trim($notes[$index] ?? '');

        if (($length_feet == 0 && $length_inch == 0) || ($qty == 0)) continue;

        $panel_type_row  = $panel_types[$index]  ?? 'solid';
        $panel_style_row = $panel_styles[$index] ?? 'regular';
        $panel_drip_stop_row = $panel_drip_stops[$index] ?? '';
        $bundle_name_row = $bundle_names[$index] ?? '';

        // Build a unique key representing this combination
        $key = md5(json_encode([
            $product_id, $grade, $gauge, $profile, $color,
            $panel_type_row, $panel_style_row, $panel_drip_stop_row,
            $length_feet, $length_inch, $note
        ]));

        if (!isset($mergedItems[$key])) {
            $mergedItems[$key] = [
                'quantity'         => $qty,
                'length_feet'      => $length_feet,
                'length_inch'      => $length_inch,
                'panel_type'       => $panel_type_row,
                'panel_style'      => $panel_style_row,
                'panel_drip_stop'  => $panel_drip_stop_row,
                'bundle_name'      => $bundle_name_row,
                'note'             => $note
            ];
        } else {
            $mergedItems[$key]['quantity'] += $qty;
        }
    }

    // Step 2: Process each merged item
    foreach ($mergedItems as $item) {
        $qty = $item['quantity'];
        $length_feet = $item['length_feet'];
        $length_inch = $item['length_inch'];
        $panel_type_row = $item['panel_type'];
        $panel_style_row = $item['panel_style'];
        $panel_drip_stop_row = $item['panel_drip_stop'];
        $bundle_name_row = $item['bundle_name'];
        $note = $item['note'];

        $quantityInStock = getProductStockInStock($product_id);
        $totalQuantity   = getProductStockTotal($product_id);
        $totalStock      = $totalQuantity;

        // Check existing cart for exact match
        $foundKey = false;
        foreach ($_SESSION["cart"] as $cartKey => $cartItem) {
            if (
                $cartItem['product_id'] == $product_id &&
                $cartItem['custom_grade'] == $grade &&
                $cartItem['custom_gauge'] == $gauge &&
                $cartItem['custom_profile'] == $profile &&
                $cartItem['custom_color'] == $color &&
                $cartItem['panel_type'] == $panel_type_row &&
                $cartItem['panel_style'] == $panel_style_row &&
                $cartItem['panel_drip_stop'] == $panel_drip_stop_row &&
                $cartItem['estimate_length'] == $length_feet &&
                $cartItem['estimate_length_inch'] == $length_inch &&
                $cartItem['note'] == $note
            ) {
                $foundKey = $cartKey;
                break;
            }
        }

        if ($foundKey !== false) {
            $_SESSION["cart"][$foundKey]['quantity_cart'] += min($qty, $totalStock);
        } else {
            $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM product WHERE product_id = '$product_id'"));
            if (!$row) continue;

            $basePrice = floatval($product_details['unit_price'] ?? 0);
            if (!empty($product_details['sold_by_feet']) && $product_details['sold_by_feet'] == '1') {
                $length = floatval($product_details['length'] ?? 0);
                if ($length > 0) $basePrice = $basePrice / $length;
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

            $_SESSION['cart'][$nextLine] = [
                'product_id'          => $row['product_id'],
                'product_item'        => getProductName($row['product_id']),
                'supplier_id'         => $row['supplier_id'],
                'unit_price'          => $unit_price,
                'line'                => $nextLine,
                'quantity_ttl'        => $totalStock,
                'quantity_in_stock'   => $quantityInStock,
                'quantity_cart'       => $qty,
                'estimate_width'      => $row['width'],
                'estimate_length'     => $length_feet,
                'estimate_length_inch'=> $length_inch,
                'usage'               => 0,
                'custom_color'        => $color ?: $row['color'],
                'panel_type'          => $panel_type_row,
                'panel_style'         => $panel_style_row,
                'panel_drip_stop'     => $panel_drip_stop_row,
                'weight'              => $weight,
                'custom_grade'        => $grade ?: '',
                'custom_gauge'        => $gauge ?: '',
                'custom_profile'      => $profile ?: getLastValue($row['profile']),
                'stiff_board_batten'  => $stiff_board_batten,
                'stiff_stand_seam'    => $stiff_stand_seam,
                'is_pre_order'        => $is_pre_order,
                'bundle_name'         => $bundle_name_row,
                'note'                => $note
            ];
        }
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
        $clean_category = intval($product_category);

        $category_condition = "
            AND FIND_IN_SET(
                '$clean_category',
                REPLACE(REPLACE(REPLACE(REPLACE(product_category, '\"', ''), \"'\", ''), '[', ''), ']', '')
            )
        ";
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

if (isset($_POST['add_cart_screw'])) {
    $product_id       = (int)$_POST['product_id'];
    $color_id         = (int)$_POST['color_id'];
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
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $item_details = getProductDetails($item['product_id']);

            if ($item_details['product_category'] != $apply_category_id) continue;
            if ((int)$item['custom_color'] !== $selected_color_id) continue;

            $qty = (int)$item['quantity_cart'];
            $len_feet  = !empty($item['estimate_length']) ? (float)$item['estimate_length'] : 0;
            $len_inch  = !empty($item['estimate_length_inch']) ? (float)$item['estimate_length_inch'] : 0;
            $total_len_inch = ($len_feet * 12) + $len_inch;

            $product_system = $item_details['product_system'];
            $sys = getProductSystemDetails($product_system);
            $distance = isset($sys['screw_distance']) && $sys['screw_distance'] > 0 
                        ? (int)$sys['screw_distance'] 
                        : 1;

            if ($total_len_inch > 0) {
                $total_inches += $qty * $total_len_inch;
            }
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
    $total_pcs    = $pack_size * $packs_needed;

    $found_in_cart = false;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as &$cart_item) {
            if ($cart_item['product_id'] == $chosen_pack['product_id'] &&
                $cart_item['custom_color'] == $chosen_pack['color_id']) {

                $cart_item['quantity_cart'] += $packs_needed;
                $found_in_cart = true;
                break;
            }
        }
        unset($cart_item);
    }

    if (!$found_in_cart) {
        $nextLine = empty($_SESSION['cart']) ? 1 : (max(array_keys($_SESSION['cart'])) + 1);

        $_SESSION['cart'][$nextLine] = [
            "product_id"        => $chosen_pack['product_id'],
            "product_item"      => getProductName($chosen_pack['product_id']),
            "unit_price"        => $chosen_pack['unit_price'],
            "line"              => $nextLine,
            "estimate_width"    => '',
            "estimate_length"   => $pack_size,
            "estimate_length_inch" => '',
            "quantity_cart"     => $packs_needed,
            "custom_color"      => $chosen_pack['color_id'],
            "usage"             => 0,
            'quantity_ttl'      => getProductStockTotal($chosen_pack['product_id']),
            'quantity_in_stock' => '',
            "weight"            => floatval($chosen_pack['weight'])
        ];
    }

    echo "Added $packs_needed pack(s) of screws (Apply Type: $type_to_apply, Apply Color: $selected_color_id, Screw Distance: {$screw_distance}) to cart";
}

if (isset($_POST['set_bundle_name'])) {
    $lines = $_POST['lines'];
    $bundle_name = trim($_POST['bundle_name']);

    if (!empty($lines) && $bundle_name !== "") {
        foreach ($lines as $line) {
            if (isset($_SESSION['cart'][$line])) {
                $_SESSION['cart'][$line]['bundle_name'] = $bundle_name;
            }
        }
    }

    echo json_encode([
        "status" => "ok",
        "bundle_name" => $bundle_name,
        "lines_updated" => $lines
    ]);
    exit;
}

if (isset($_POST['reorder_cart'])) {
    $order = $_POST['order'] ?? [];

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $newCart = [];
    foreach ($order as $row) {
        $line = (int)$row['line'];
        $bundle = trim($row['bundle'] ?? '');

        if (isset($_SESSION['cart'][$line])) {
            $_SESSION['cart'][$line]['bundle_name'] = $bundle;
            $newCart[$line] = $_SESSION['cart'][$line];
        }
    }

    $_SESSION['cart'] = $newCart;

    echo json_encode(['success' => true]);
    exit;
}


if (isset($_POST['set_contractor'])) {
    $_SESSION['contractor_id']   = $_POST['contractor_id'];
}

if (isset($_POST['change_cart_columns'])) {
    $staff_id = $_SESSION['userid'] ?? 0;
    if (!$staff_id) {
        echo "No staff ID found.";
        exit;
    }

    $all_settings = [
        'show_prod_id_abbrev',
        'show_unique_product_id',
        'show_linear_ft',
        'show_per_panel',
        'show_panel_price',
        'show_trim_per_ft',
        'show_trim_per_each',
        'show_trim_price',
        'show_screw_per_each',
        'show_screw_per_pack',
        'show_screw_price',
        'show_each_per_each',
        'show_each_per_pack',
        'show_each_price',
        'show_retail_price',
        'show_profile',
        'show_drag_handle'
    ];

    foreach ($all_settings as $key) {
        $setting_key   = mysqli_real_escape_string($conn, $key);
        $setting_value = isset($_POST[$key]) ? 1 : 0;

        $check = mysqli_query($conn, "
            SELECT id FROM staff_settings 
            WHERE staff_id = '$staff_id' AND setting_key = '$setting_key' 
            LIMIT 1
        ");

        if ($check && mysqli_num_rows($check) > 0) {
            $update = "
                UPDATE staff_settings 
                SET setting_value = '$setting_value', updated_at = NOW()
                WHERE staff_id = '$staff_id' AND setting_key = '$setting_key'
            ";
            mysqli_query($conn, $update);
        } else {
            $insert = "
                INSERT INTO staff_settings (staff_id, setting_key, setting_value, created_at, updated_at)
                VALUES ('$staff_id', '$setting_key', '$setting_value', NOW(), NOW())
            ";
            mysqli_query($conn, $insert);
        }
    }

    echo "success";
    exit;
}

if (isset($_POST['send_order'])) {
    $orderid = mysqli_real_escape_string($conn, $_POST['send_order_id']);
    $customer_id = mysqli_real_escape_string($conn, $_POST['send_order_customer']);

    if (!empty($_POST['order_url'])) {
        $order_url = $_POST['order_url'];
    } else {
        $order_url = "https://metal.ilearnwebtech.com/print_order_product.php?id=" . urlencode($orderid);
    }

    if (empty($customer_id)) {
        echo json_encode(['success' => false, 'message' => 'No customer ID found in session.']);
        exit;
    }

    $customer_details = getCustomerDetails($customer_id);
    $customer_name = get_customer_name($customer_id);
    $customer_email = $customer_details['contact_email'] ?? null;

    if (empty($customer_email)) {
        echo json_encode(['success' => false, 'message' => 'Customer email not found.']);
        exit;
    }

    $send_option = mysqli_real_escape_string($conn, $_POST['send_option']);
    $subject = "Order Invoice";

    $results = [];
    $results['email'] = $emailSender->sendInvoiceToCustomer($customer_email, $subject, $order_url);

    $response = [
        'success' => true,
        'message' => "Successfully sent to Customer",
        'results' => $results
    ];

    echo json_encode($response);
}

if (isset($_POST['toggle_group'])) {
    $group_id = mysqli_real_escape_string($conn, $_POST['group_id']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);

    if (!isset($_SESSION['cart_group_state'])) {
        $_SESSION['cart_group_state'] = [];
    }

    $_SESSION['cart_group_state'][$group_id] = $state;

    echo "Group $group_id set to $state";
    exit;
}

if (isset($_POST['fetch_special_trim_details'])) {

    $special_trim_id = intval($_POST['id']);
    $query = "
        SELECT *
        FROM special_trim
        WHERE special_trim_id = $special_trim_id
        LIMIT 1
    ";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $flat_sheet_width = is_array(json_decode($row['flat_sheet_width'], true)) ? json_decode($row['flat_sheet_width'], true)[0] : $row['flat_sheet_width'];
        $fsw_det = getFlatSheetWidthDetails($flat_sheet_width);
        $width = $fsw_det['width'];

        $response = [
            'success' => true,
            'trim_no' => $row['trim_no'],
            'unit_price' => $row['unit_price'],
            'color' => is_array(json_decode($row['color'], true)) ? json_decode($row['color'], true)[0] : $row['color'],
            'grade' => is_array(json_decode($row['grade'], true)) ? json_decode($row['grade'], true)[0] : $row['grade'],
            'gauge' => is_array(json_decode($row['gauge'], true)) ? json_decode($row['gauge'], true)[0] : $row['gauge'],
            'flat_sheet_width' => $width,
            'hems' => $row['hems'],
            'bends' => $row['bends'],
            'description' => $row['description']
        ];

        echo json_encode($response);

    } else {

        echo json_encode([
            'success' => false,
            'message' => 'Special trim not found.'
        ]);
    }

    exit;
}
?>

