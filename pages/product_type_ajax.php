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
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_type WHERE product_type_id = '$product_type_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_product_type = $row['product_type'];
            $current_type_abreviations = $row['type_abreviations'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($product_type != $current_product_type) {
                $checkProductType = "SELECT * FROM product_type WHERE product_type = '$product_type'";
                $resultProductType = mysqli_query($conn, $checkProductType);
                if (mysqli_num_rows($resultProductType) > 0) {
                    $duplicates[] = "Product type";
                }
            }

            if ($type_abreviations != $current_type_abreviations) {
                $checkAbreviations = "SELECT * FROM product_type WHERE type_abreviations = '$type_abreviations'";
                $resultAbreviations = mysqli_query($conn, $checkAbreviations);
                if (mysqli_num_rows($resultAbreviations) > 0) {
                    $duplicates[] = "Type Abbreviations";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE product_type SET product_type = '$product_type', type_abreviations = '$type_abreviations', notes = '$notes', last_edit = NOW(), edited_by = '$userid'  WHERE product_type_id = '$product_type_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Product type updated successfully.";
                } else {
                    echo "Error updating product type: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkProductType = "SELECT * FROM product_type WHERE product_type = '$product_type'";
            $resultProductType = mysqli_query($conn, $checkProductType);
            if (mysqli_num_rows($resultProductType) > 0) {
                $duplicates[] = "Product Type";
            }

            $checkAbreviations = "SELECT * FROM product_type WHERE type_abreviations = '$type_abreviations'";
            $resultAbreviations = mysqli_query($conn, $checkAbreviations);
            if (mysqli_num_rows($resultAbreviations) > 0) {
                $duplicates[] = "Type Abbreviations";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO product_type (product_type, type_abreviations, notes, added_date, added_by) VALUES ('$product_type', '$type_abreviations', '$notes', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "New product type added successfully.";
                } else {
                    echo "Error adding product type: " . mysqli_error($conn);
                }
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
