<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_type_id = mysqli_real_escape_string($conn, $_POST['product_type_id']);
        $product_type = mysqli_real_escape_string($conn, $_POST['product_type']);
        $type_abreviations = mysqli_real_escape_string($conn, $_POST['type_abreviations']);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        $multiplier = mysqli_real_escape_string($conn, floatval($_POST['multiplier'] ?? 0.00));
        $special = isset($_POST['special']) ? intval($_POST['special']) : 0;
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_type WHERE product_type_id = '$product_type_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE product_type SET product_type = '$product_type', type_abreviations = '$type_abreviations', product_category = '$product_category', notes = '$notes', multiplier = '$multiplier', special = '$special', last_edit = NOW(), edited_by = '$userid'  WHERE product_type_id = '$product_type_id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "Product type updated successfully.";
            } else {
                echo "Error updating product type: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO product_type (product_type, type_abreviations, product_category, notes, multiplier, special, added_date, added_by) VALUES ('$product_type', '$type_abreviations', '$product_category', '$notes', '$multiplier', '$special', NOW(), '$userid')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "New product type added successfully.";
            } else {
                echo "Error adding product type: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_type_id = mysqli_real_escape_string($conn, $_POST['product_type_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_type SET status = '$new_status' WHERE product_type_id = '$product_type_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_type') {
        $product_type_id = mysqli_real_escape_string($conn, $_POST['product_type_id']);
        $query = "UPDATE product_type SET hidden='1' WHERE product_type_id='$product_type_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
