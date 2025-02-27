<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $product_category_id = mysqli_real_escape_string($conn, $_POST['product_category']);
        $customer_pricing_id = mysqli_real_escape_string($conn, $_POST['customer_pricing']);
        $percentage = mysqli_real_escape_string($conn, floatval($_POST['percentage'] ?? 0.00));
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        $product_items = isset($_POST['product_items']) ? $_POST['product_items'] : [];
        $product_items_str = implode(',', $product_items);
        $product_items_str = mysqli_real_escape_string($conn, $product_items_str);

        $checkQuery = "SELECT * FROM pricing_category WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $updateQuery = "UPDATE pricing_category SET product_category_id = '$product_category_id', customer_pricing_id = '$customer_pricing_id', percentage = '$percentage', product_items = '$product_items_str', last_edit = NOW(), edited_by = '$userid'  WHERE id = '$id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating category: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO pricing_category (product_category_id, customer_pricing_id, percentage, product_items, added_date, added_by) VALUES ('$product_category_id', '$customer_pricing_id', '$percentage', '$product_items_str', NOW(), '$userid')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding category: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE pricing_category SET status = '$new_status' WHERE id = '$id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_pricing_category') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "UPDATE pricing_category SET hidden='1' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
