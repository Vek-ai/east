<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action']) && $_REQUEST['action'] == "add_update"){
    $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
    $product_line = mysqli_real_escape_string($conn, $_POST['product_line']);
    $line_abreviations = mysqli_real_escape_string($conn, $_POST['line_abreviations']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    // SQL query to check if the record exists
    $checkQuery = "SELECT * FROM product_line WHERE product_line_id = '$product_line_id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Record exists, update it
        $updateQuery = "UPDATE product_line SET product_line = '$product_line', line_abreviations = '$line_abreviations', notes = '$notes' WHERE product_line_id = '$product_line_id'";
        if (mysqli_query($conn, $updateQuery)) {
            echo "Product line updated successfully.";
        } else {
            echo "Error updating line: " . mysqli_error($conn);
        }
    } else {
        // Record does not exist, insert it
        $insertQuery = "INSERT INTO product_line (product_line, line_abreviations, notes) VALUES ('$product_line', '$line_abreviations', '$notes')";
        if (mysqli_query($conn, $insertQuery)) {
            echo "New product line added successfully.";
        } else {
            echo "Error adding line: " . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == "change_status"){
    $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $new_status = '';
    if($status == '0'){
        $new_status = '1';
    }else{
        $new_status = '0';
    }
    $delete_query = "UPDATE product_line SET status = '$new_status' WHERE product_line_id = '$product_line_id'";
    $result_delete = mysqli_query($conn, $delete_query);

    if ($result_delete) {
        echo "success";
    } else {
        echo "Error deleting line or line not found: " . mysqli_error($conn);
    }
}


?>
