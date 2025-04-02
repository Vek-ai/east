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
        $supplier_order_id = mysqli_real_escape_string($conn, $_GET['supplier_order_id']);
    
        $total_query = "SELECT COUNT(*) AS total 
                        FROM supplier_orders_prod sop
                        JOIN product p ON sop.product_id = p.product_id
                        WHERE sop.supplier_order_id = '$supplier_order_id' 
                        AND p.product_item LIKE '%$search%'";
        $total_result = mysqli_query($conn, $total_query);
        $total_row = mysqli_fetch_assoc($total_result);
        $total_records = $total_row['total'];
        $total_pages = ceil($total_records / $limit);
    
        $query = "SELECT sop.*, p.product_item 
                  FROM supplier_orders_prod sop
                  JOIN product p ON sop.product_id = p.product_id
                  WHERE sop.supplier_order_id = '$supplier_order_id' 
                  AND p.product_item LIKE '%$search%' 
                  LIMIT $limit OFFSET $offset";
        $result = mysqli_query($conn, $query);
    
        $data = [];
        $total_quantity = 0;
        $total_amount = 0.00;
    
        while ($row_prod = mysqli_fetch_assoc($result)) {
            $product_details = getProductDetails($row_prod['product_id']);
            $row_prod['product_name'] = $product_details['product_item'];
            $row_prod['category'] = getProductCategoryName($product_details['product_category']);
            $row_prod['image'] = !empty($product_details['main_image']) ? "../" . $product_details['main_image'] : '../images/product/product.jpg';
            $row_prod['color'] = $row_prod['color'];
            $row_prod['color_hex'] = getColorHexFromColorID($row_prod['color']);
            $row_prod['color_name'] = !empty($row_prod['color']) ? getColorName($row_prod['color']) : '';
    
            $row_prod['total_price'] = floatval($row_prod['price']) * intval($row_prod['quantity']);
    
            $total_quantity += intval($row_prod['quantity']);
            $total_amount += $row_prod['total_price'];
    
            $row_prod['total_price'] = number_format($row_prod['total_price'], 2);
    
            $data[] = $row_prod;
        }
    
        echo json_encode([
            'data' => $data,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'total_records' => $total_records,
            'total_quantity' => $total_quantity,
            'total_amount' => number_format($total_amount, 2),
        ]);
    }    

    if ($action == "update_product") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $price = mysqli_real_escape_string($conn, $_POST['price']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);
    
        $query = "UPDATE supplier_orders_prod 
                  SET quantity = '$quantity', price = '$price', color = '$color' 
                  WHERE id = '$id'";
    
        if (mysqli_query($conn, $query)) {
            echo json_encode(["success" => true, "sql" => $query]);
        } else {
            echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
        }
    }    

    if ($action == "update_status") {
        $orderId = mysqli_real_escape_string($conn, $_POST['id']);
        $method = mysqli_real_escape_string($conn, $_POST['method']); 
    
        $response = ['success' => false, 'message' => 'Unknown error'];
    
        if ($method == "submit_for_approval") {
            $newStatus = 2;
        } elseif ($method == "accept_order") {
            $newStatus = 2;
        } elseif ($method == "process_order") {
            $newStatus = 5;
        } elseif ($method == "ship_order") {
            $newStatus = 6;
        } else {
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            exit();
        }
        
        $sql = "UPDATE supplier_orders SET status = $newStatus WHERE supplier_order_id = $orderId";
        
        if (mysqli_query($conn, $sql)) {
            $response['success'] = true;
            $response['message'] = 'Order status updated successfully!';
        } else {
            $response['message'] = 'Error updating order status.';
        }
    
        echo json_encode($response);
    }
    
}
mysqli_close($conn);