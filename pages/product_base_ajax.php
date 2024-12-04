<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
        $base_price = mysqli_real_escape_string($conn, $_POST['base_price']);
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_base WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_product_name = $row['product_name'];
            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($product_name != $current_product_name) {
                $checkProductBase = "SELECT * FROM product_base WHERE product_name = '$product_name'";
                $resultProductBase = mysqli_query($conn, $checkProductBase);
                if (mysqli_num_rows($resultProductBase) > 0) {
                    $duplicates[] = "Product Name";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE product_base SET product_name = '$product_name', base_price = '$base_price', last_edit = NOW(), edited_by = '$userid' WHERE id = '$id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "success_update";
                } else {
                    echo "Error updating product base: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkProductBase = "SELECT * FROM product_base WHERE product_name = '$product_name'";
            $resultProductBase = mysqli_query($conn, $checkProductBase);
            if (mysqli_num_rows($resultProductBase) > 0) {
                $duplicates[] = "Product Base";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO product_base (product_name, base_price, added_date, added_by) VALUES ('$product_name', '$base_price', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "success_add";
                } else {
                    echo "Error adding product base: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_base SET status = '$new_status' WHERE id = '$id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_base') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "UPDATE product_base SET hidden='1' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
