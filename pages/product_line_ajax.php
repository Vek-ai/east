<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
        $product_line = mysqli_real_escape_string($conn, $_POST['product_line']);
        $line_abreviations = mysqli_real_escape_string($conn, $_POST['line_abreviations']);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        $multiplier = mysqli_real_escape_string($conn, floatval($_POST['multiplier'] ?? 0.00));

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_line WHERE product_line_id = '$product_line_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE product_line SET product_line = '$product_line', line_abreviations = '$line_abreviations', product_category = '$product_category', notes = '$notes', multiplier = '$multiplier', last_edit = NOW(), edited_by = '$userid'  WHERE product_line_id = '$product_line_id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "Product line updated successfully.";
            } else {
                echo "Error updating product line: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO product_line (product_line, line_abreviations, product_category, notes, multiplier, added_date, added_by) VALUES ('$product_line', '$line_abreviations', '$product_category', '$notes', '$multiplier', NOW(), '$userid')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "New product line added successfully.";
            } else {
                echo "Error adding product line: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_line SET status = '$new_status' WHERE product_line_id = '$product_line_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_line') {
        $product_line_id = mysqli_real_escape_string($conn, $_POST['product_line_id']);
        $query = "UPDATE product_line SET hidden='1' WHERE product_line_id='$product_line_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
