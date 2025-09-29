<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "load_est_prod") {
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $offset = ($page - 1) * $limit;
    
        $search = mysqli_real_escape_string($conn, $_GET['search']);
        $estimateid = mysqli_real_escape_string($conn, $_GET['estimateid']);
    
        $total_query = "SELECT COUNT(*) AS total 
                        FROM estimate_prod ep
                        JOIN product p ON ep.product_id = p.product_id
                        WHERE ep.estimateid = '$estimateid' 
                        AND p.product_item LIKE '%$search%'";
        $total_result = mysqli_query($conn, $total_query);
        $total_row = mysqli_fetch_assoc($total_result);
        $total_records = $total_row['total'];
        $total_pages = ceil($total_records / $limit);
    
        $query = "SELECT ep.*, p.product_item, ep.product_item as est_prod_name
                  FROM estimate_prod ep
                  JOIN product p ON ep.product_id = p.product_id
                  WHERE ep.estimateid = '$estimateid' 
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
            $product_details = getProductDetails($row_prod['product_id']);

            $product_name = '';
            if(!empty($row_prod['est_prod_name'])){
                $product_name = $row_prod['est_prod_name'];
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

    if ($action == "update_estimate") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $discounted_price = mysqli_real_escape_string($conn, $_POST['price']);
        $color = mysqli_real_escape_string($conn, $_POST['color']);

        $estimateid = 0;
        $query = "SELECT * FROM estimate_prod WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $estimateid = $row['estimateid'];
        }

        $sql = "UPDATE estimates SET is_edited = '1' WHERE estimateid = $estimateid";
        mysqli_query($conn, $sql);
    
        $query = "UPDATE estimate_prod 
                  SET quantity = '$quantity', discounted_price = '$discounted_price', custom_color = '$color' 
                  WHERE id = '$id'";
    
        if (mysqli_query($conn, $query)) {
            echo json_encode(["success" => true, "sql" => $query]);
        } else {
            echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
        }
    }    

    if ($action == "update_status") {
        $estimateid = mysqli_real_escape_string($conn, $_POST['id']);
        $method = mysqli_real_escape_string($conn, $_POST['method']); 

        $is_edited = '0';

        $query = "SELECT * FROM estimates WHERE estimateid = '$estimateid'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $customerid = $row['customerid'];
            $customer_details = getCustomerDetails($customerid);
            $customer_name = $customer_details['customer_first_name'] .' ' .$customer_details['customer_first_name'];
        
            $response = ['success' => false, 'message' => 'Unknown error'];
        
            if ($method == "submit_for_approval") {
                $newStatus = 3;
            } elseif ($method == "accept_estimate") {
                $newStatus = 4;
            }  else {
                $response['message'] = 'Invalid action';
                echo json_encode($response);
                exit();
            }
            
            $sql = "UPDATE estimates SET status = $newStatus, is_edited = '0' WHERE estimateid = $estimateid";
            
            if (mysqli_query($conn, $sql)) {
                $response['success'] = true;
                $response['message'] = 'Status updated successfully!';
            } else {
                $response['message'] = 'Error updating order status.';
            }
        
            echo json_encode($response);
        }
    }
    
}
mysqli_close($conn);