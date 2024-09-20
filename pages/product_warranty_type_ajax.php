<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_warranty_type_id = mysqli_real_escape_string($conn, $_POST['product_warranty_type_id']);
        $product_warranty_type = mysqli_real_escape_string($conn, $_POST['product_warranty_type']);
        $warranty_type_abbreviations = mysqli_real_escape_string($conn, $_POST['warranty_type_abbreviations']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_warranty_type WHERE product_warranty_type_id = '$product_warranty_type_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_product_warranty_type = $row['product_warranty_type'];
            $current_warranty_type_abbreviations = $row['warranty_type_abbreviations'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($product_warranty_type != $current_product_warranty_type) {
                $checkProductwarranty_type = "SELECT * FROM product_warranty_type WHERE product_warranty_type = '$product_warranty_type'";
                $resultProductwarranty_type = mysqli_query($conn, $checkProductwarranty_type);
                if (mysqli_num_rows($resultProductwarranty_type) > 0) {
                    $duplicates[] = "Product warranty type";
                }
            }

            if ($warranty_type_abbreviations != $current_warranty_type_abbreviations) {
                $checkAbreviations = "SELECT * FROM product_warranty_type WHERE warranty_type_abbreviations = '$warranty_type_abbreviations'";
                $resultAbreviations = mysqli_query($conn, $checkAbreviations);
                if (mysqli_num_rows($resultAbreviations) > 0) {
                    $duplicates[] = "Warranty type Abbreviations";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE product_warranty_type SET product_warranty_type = '$product_warranty_type', warranty_type_abbreviations = '$warranty_type_abbreviations', notes = '$notes', last_edit = NOW(), edited_by = '$userid'  WHERE product_warranty_type_id = '$product_warranty_type_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "update-success";
                } else {
                    echo "Error updating product warranty type: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkProductwarranty_type = "SELECT * FROM product_warranty_type WHERE product_warranty_type = '$product_warranty_type'";
            $resultProductwarranty_type = mysqli_query($conn, $checkProductwarranty_type);
            if (mysqli_num_rows($resultProductwarranty_type) > 0) {
                $duplicates[] = "Product warranty type";
            }

            $checkAbreviations = "SELECT * FROM product_warranty_type WHERE warranty_type_abbreviations = '$warranty_type_abbreviations'";
            $resultAbreviations = mysqli_query($conn, $checkAbreviations);
            if (mysqli_num_rows($resultAbreviations) > 0) {
                $duplicates[] = "warranty type Abbreviations";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO product_warranty_type (product_warranty_type, warranty_type_abbreviations, notes, added_date, added_by) VALUES ('$product_warranty_type', '$warranty_type_abbreviations', '$notes', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "add-success";
                } else {
                    echo "Error adding product warranty type: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_warranty_type_id = mysqli_real_escape_string($conn, $_POST['product_warranty_type_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_warranty_type SET status = '$new_status' WHERE product_warranty_type_id = '$product_warranty_type_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_warranty_type') {
        $product_warranty_type_id = mysqli_real_escape_string($conn, $_POST['product_warranty_type_id']);
        $query = "UPDATE product_warranty_type SET hidden='1' WHERE product_warranty_type_id='$product_warranty_type_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
