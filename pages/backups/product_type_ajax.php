<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action']) && $_REQUEST['action'] == "add_update"){
    $product_type_id = mysqli_real_escape_string($conn, $_POST['product_type_id']);
    $product_type = mysqli_real_escape_string($conn, $_POST['product_type']);
    $type_abreviations = mysqli_real_escape_string($conn, $_POST['type_abreviations']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    // SQL query to check if the record exists
    $checkQuery = "SELECT * FROM product_type WHERE product_type_id = '$product_type_id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Record exists, update it
        $updateQuery = "UPDATE product_type SET product_type = '$product_type', type_abreviations = '$type_abreviations', notes = '$notes' WHERE product_type_id = '$product_type_id'";
        if (mysqli_query($conn, $updateQuery)) {
            echo "Product type updated successfully.";
        } else {
            echo "Error updating type: " . mysqli_error($conn);
        }
    } else {
        // Record does not exist, insert it
        $insertQuery = "INSERT INTO product_type (product_type, type_abreviations, notes) VALUES ('$product_type', '$type_abreviations', '$notes')";
        if (mysqli_query($conn, $insertQuery)) {
            echo "New product type added successfully.";
        } else {
            echo "Error adding type: " . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == "change_status"){
    $product_type_id = mysqli_real_escape_string($conn, $_POST['product_type_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $new_status = '';
    if($status == '0'){
        $new_status = '1';
    }else{
        $new_status = '0';
    }
    $delete_query = "UPDATE product_type SET status = '$new_status' WHERE product_type_id = '$product_type_id'";
    $result_delete = mysqli_query($conn, $delete_query);

    if ($result_delete) {
        echo "success";
    } else {
        echo "Error deleting type or type not found: " . mysqli_error($conn);
    }
}


?>
