<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

function findCartKey($cart, $product_id) {
    foreach ($cart as $key => $item) {
        if ($item['product_id'] == $product_id) {
            return $key;
        }
    }
    return false;
}

if (isset($_POST['modifyquantity'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

    $quantityInStock = getProductStockInStock($product_id);

    if (!isset($_SESSION["order_cart"])) {
        $_SESSION["order_cart"] = array();
    }

    $key = findCartKey($_SESSION["order_cart"], $product_id);

    if ($key !== false) {
        if (isset($_POST['setquantity'])) {
            $requestedQuantity = max($qty, 1);
            $_SESSION["order_cart"][$key]['quantity_cart'] = $requestedQuantity;
            echo $_SESSION["order_cart"][$key]['quantity_cart'];
        } elseif (isset($_POST['addquantity'])) {
            $newQuantity = $_SESSION["order_cart"][$key]['quantity_cart'] + $qty;
            $_SESSION["order_cart"][$key]['quantity_cart'] = $newQuantity;
            echo $_SESSION["order_cart"][$key]['quantity_cart'];
        } elseif (isset($_POST['deductquantity'])) {
            $currentQuantity = $_SESSION["order_cart"][$key]['quantity_cart'];
            if ($currentQuantity <= 1) {
                array_splice($_SESSION["order_cart"], $key, 1);
                echo 'removed';
            } else {
                $_SESSION["order_cart"][$key]['quantity_cart'] = $currentQuantity - 1;
                echo $_SESSION["order_cart"][$key]['quantity_cart'];
            }
        }
    } else {
        $query = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            $basePrice = floatval($row['unit_price']);
            if($row['sold_by_feet'] == '1'){
                $basePrice = $basePrice / floatval($row['length'] ?? 1);
            }

            $item_array = array(
                'product_id' => $row['product_id'],
                'product_item' => getProductName($row['product_id']),
                'unit_price' => $basePrice,
                'quantity_cart' => $qty,
                'custom_color' => $row['color']
            );

            $_SESSION["order_cart"][] = $item_array;
        }

    }
}

if (isset($_POST['fetch_cart_count'])) {
    $cart_count = 0;
    if (isset($_SESSION['order_cart']) && is_array($_SESSION['order_cart'])) {
        foreach ($_SESSION['order_cart'] as $item) {
            $cart_count += isset($item['quantity_cart']) ? intval($item['quantity_cart']) : 0;
        }
    }
    echo $cart_count;
}

mysqli_close($conn);
?>
