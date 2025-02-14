<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id'] ?? null);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category'] ?? 0);
        $product_system = mysqli_real_escape_string($conn, $_POST['product_system'] ?? 0);
        $product_line = mysqli_real_escape_string($conn, $_POST['product_line'] ?? 0);
        $product_type = mysqli_real_escape_string($conn, $_POST['product_type'] ?? 0);
        $width = mysqli_real_escape_string($conn, $_POST['width'] ?? '');
        $userid = mysqli_real_escape_string($conn, $_POST['userid'] ?? '');
    
        $checkQuery = "SELECT * FROM coil_width WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);
    
        if ($result && mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE flat_sheet_width 
                            SET product_category = '$product_category', 
                                product_system = '$product_system', 
                                product_line = '$product_line', 
                                product_type = '$product_type', 
                                width = '$width',
                                last_edit = NOW(), 
                                edited_by = '$userid' 
                            WHERE id = '$id'";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success_update";
            } else {
                echo "Error updating coil width: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO flat_sheet_width (product_category, product_system, product_line, product_type, width, added_by, last_edit) 
                            VALUES ('$product_category', '$product_system', '$product_line', '$product_type', '$width', '$userid', NOW())";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success_add";
            } else {
                echo "Error adding coil width: " . mysqli_error($conn);
            }
        }
    }    
    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE flat_sheet_width SET status = '$new_status' WHERE id = '$id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_fs_width') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "UPDATE flat_sheet_width SET hidden='1' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
