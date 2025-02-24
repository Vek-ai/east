<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_system_id = mysqli_real_escape_string($conn, $_POST['product_system_id']);
        $product_system = mysqli_real_escape_string($conn, $_POST['product_system']);
        $system_abbreviations = mysqli_real_escape_string($conn, $_POST['system_abbreviations']);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        $multiplier = mysqli_real_escape_string($conn, floatval($_POST['multiplier'] ?? 0.00));

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_system WHERE product_system_id = '$product_system_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE product_system SET product_system = '$product_system', system_abbreviations = '$system_abbreviations', product_category = '$product_category', notes = '$notes', multiplier = '$multiplier', last_edit = NOW(), edited_by = '$userid'  WHERE product_system_id = '$product_system_id'";
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating product systems: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO product_system (product_system, system_abbreviations, product_category, notes, multiplier, added_date, added_by) VALUES ('$product_system', '$system_abbreviations', '$product_category', '$notes', '$multiplier', NOW(), '$userid')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding product system: " . mysqli_error($conn);
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_system_id = mysqli_real_escape_string($conn, $_POST['product_system_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_system SET status = '$new_status' WHERE product_system_id = '$product_system_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_system') {
        $product_system_id = mysqli_real_escape_string($conn, $_POST['product_system_id']);
        $query = "UPDATE product_system SET hidden='1' WHERE product_system_id='$product_system_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
