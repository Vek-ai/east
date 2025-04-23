<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "load_order_prod") {
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $offset = ($page - 1) * $limit;
    
        $search = mysqli_real_escape_string($conn, $_GET['search']);
        $orderid = mysqli_real_escape_string($conn, $_GET['orderid']);
    
        $total_query = "SELECT COUNT(*) AS total 
                        FROM order_product op
                        JOIN product p ON op.productid = p.product_id
                        WHERE op.orderid = '$orderid' 
                        AND p.product_item LIKE '%$search%'";
        $total_result = mysqli_query($conn, $total_query);
        $total_row = mysqli_fetch_assoc($total_result);
        $total_records = $total_row['total'];
        $total_pages = ceil($total_records / $limit);
    
        $query = "SELECT op.*, p.product_item, op.product_item as ord_prod_name
                  FROM order_product op
                  JOIN product p ON op.productid = p.product_id
                  WHERE op.orderid = '$orderid' 
                  AND p.product_item LIKE '%$search%' 
                  LIMIT $limit OFFSET $offset";
        $result = mysqli_query($conn, $query);
    
        $data = [];
        $total_quantity = 0;
        $total_amount = 0.00;
    
        $total_quantity = 0;
        $total_amount = 0;
        $total_disc_amount = 0;
        $data = [];

        while ($row_prod = mysqli_fetch_assoc($result)) {
            $product_details = getProductDetails($row_prod['productid']);

            $product_name = '';
            if(!empty($row_prod['ord_prod_name'])){
                $product_name = $row_prod['ord_prod_name'];
            }else{
                $product_name = getProductName($row_prod['product_id']);
            }
            
            $row_prod['product_name'] = $product_name;
            $row_prod['category'] = getProductCategoryName($product_details['product_category']);
            $row_prod['image'] = !empty($product_details['main_image']) ? "../" . $product_details['main_image'] : '../images/product/product.jpg';
            
            $row_prod['color'] = $row_prod['custom_color'];
            $row_prod['color_hex'] = getColorHexFromColorID($row_prod['custom_color']);
            $row_prod['color_name'] = !empty($row_prod['custom_color']) ? getColorName($row_prod['custom_color']) : '';

            $quantity = intval($row_prod['quantity']);
            $actual_price = floatval($row_prod['actual_price']);
            $discounted_price = floatval($row_prod['discounted_price']);

            $row_prod['total_price'] = $actual_price * $quantity;
            $row_prod['total_disc_price'] = $discounted_price * $quantity;

            $total_quantity += $quantity;
            $total_amount += $row_prod['total_price'];
            $total_disc_amount += $row_prod['total_disc_price'];

            $row_prod['total_price'] = number_format($row_prod['total_price'], 2);
            $row_prod['total_disc_price'] = number_format($row_prod['total_disc_price'], 2);

            $data[] = $row_prod;
        }

    
        echo json_encode([
            'data' => $data,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'total_records' => $total_records,
            'total_quantity' => $total_quantity,
            'total_amount' => number_format($total_amount, 2),
            'total_disc_amount' => number_format($total_disc_amount, 2),
        ]);
    }    
}
mysqli_close($conn);