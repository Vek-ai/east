<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if (isset($_GET['fetch_cart'])) {
    header('Content-Type: application/json');

    $cartItems = [];

    if (isset($_SESSION['customer_id'])) {
        $customer_id = $_SESSION['customer_id'];
        $dbCartItems = getCartDataByCustomerId($customer_id);

        foreach ($dbCartItems as $item) {
            $product_details = getProductDetails($item['product_id']);
            $default_image = '../images/product/product.jpg';

            $picture_path = isset($product_details['main_image']) && !empty($product_details['main_image'])
                ? "../" . $product_details['main_image']
                : $default_image;

            $cartItems[] = [
                'img_src' => $picture_path,
                'item_name' => $product_details['product_item'],
                'color_hex' => getColorHexFromColorID($item['custom_color']),
                'quantity' => $item['quantity_cart'],
                'product_id' => $item['product_id'],
                'line' => $item['line']
            ];
        }
    }

    echo json_encode(['cart_items' => $cartItems]);
}

$conn->close();
?>