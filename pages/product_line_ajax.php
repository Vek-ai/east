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
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        $multiplier = mysqli_real_escape_string($conn, floatval($_POST['multiplier'] ?? 0.00));

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_line WHERE product_line_id = '$product_line_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_product_line = $row['product_line'];
            $current_line_abreviations = $row['line_abreviations'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($product_line != $current_product_line) {
                $checkProductLine = "SELECT * FROM product_line WHERE product_line = '$product_line'";
                $resultProductLine = mysqli_query($conn, $checkProductLine);
                if (mysqli_num_rows($resultProductLine) > 0) {
                    $duplicates[] = "Product line";
                }
            }

            if ($line_abreviations != $current_line_abreviations) {
                $checkAbreviations = "SELECT * FROM product_line WHERE line_abreviations = '$line_abreviations'";
                $resultAbreviations = mysqli_query($conn, $checkAbreviations);
                if (mysqli_num_rows($resultAbreviations) > 0) {
                    $duplicates[] = "Line Abbreviations";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE product_line SET product_line = '$product_line', line_abreviations = '$line_abreviations', notes = '$notes', multiplier = '$multiplier', last_edit = NOW(), edited_by = '$userid'  WHERE product_line_id = '$product_line_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Product line updated successfully.";
                } else {
                    echo "Error updating product line: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkProductLine = "SELECT * FROM product_line WHERE product_line = '$product_line'";
            $resultProductLine = mysqli_query($conn, $checkProductLine);
            if (mysqli_num_rows($resultProductLine) > 0) {
                $duplicates[] = "Product Line";
            }

            $checkAbreviations = "SELECT * FROM product_line WHERE line_abreviations = '$line_abreviations'";
            $resultAbreviations = mysqli_query($conn, $checkAbreviations);
            if (mysqli_num_rows($resultAbreviations) > 0) {
                $duplicates[] = "Line Abbreviations";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO product_line (product_line, line_abreviations, notes, multiplier, added_date, added_by) VALUES ('$product_line', '$line_abreviations', '$notes', '$multiplier', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "New product line added successfully.";
                } else {
                    echo "Error adding product line: " . mysqli_error($conn);
                }
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
