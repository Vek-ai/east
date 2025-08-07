<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_coating_id = mysqli_real_escape_string($conn, $_POST['product_coating_id']);
        $product_coating = mysqli_real_escape_string($conn, $_POST['product_coating']);
        $coating_abbreviations = mysqli_real_escape_string($conn, $_POST['coating_abbreviations']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_coating WHERE product_coating_id = '$product_coating_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_product_coating = $row['product_coating'];
            $current_system_abreviations = $row['coating_abbreviations'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($product_coating != $current_product_coating) {
                $checkSystem = "SELECT * FROM product_coating WHERE product_coating = '$product_coating'";
                $resultSystem = mysqli_query($conn, $checkSystem);
                if (mysqli_num_rows($resultSystem) > 0) {
                    $duplicates[] = "Product System";
                }
            }

            if ($coating_abbreviations != $current_system_abreviations) {
                $checkAbreviations = "SELECT * FROM product_coating WHERE coating_abbreviations = '$coating_abbreviations'";
                $resultAbreviations = mysqli_query($conn, $checkAbreviations);
                if (mysqli_num_rows($resultAbreviations) > 0) {
                    $duplicates[] = "System Abbreviations";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE product_coating SET product_coating = '$product_coating', coating_abbreviations = '$coating_abbreviations', notes = '$notes', last_edit = NOW(), edited_by = '$userid'  WHERE product_coating_id = '$product_coating_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "success_update";
                } else {
                    echo "Error updating product systems: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkSystem = "SELECT * FROM product_coating WHERE product_coating = '$product_coating'";
            $resultSystem = mysqli_query($conn, $checkSystem);
            if (mysqli_num_rows($resultSystem) > 0) {
                $duplicates[] = "Product System";
            }

            $checkAbreviations = "SELECT * FROM product_coating WHERE coating_abbreviations = '$coating_abbreviations'";
            $resultAbreviations = mysqli_query($conn, $checkAbreviations);
            if (mysqli_num_rows($resultAbreviations) > 0) {
                $duplicates[] = "System Abbreviations";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO product_coating (product_coating, coating_abbreviations, notes, added_date, added_by) VALUES ('$product_coating', '$coating_abbreviations', '$notes', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "success_add";
                } else {
                    echo "Error adding product system: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_coating_id = mysqli_real_escape_string($conn, $_POST['product_coating_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_coating SET status = '$new_status' WHERE product_coating_id = '$product_coating_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_system') {
        $product_coating_id = mysqli_real_escape_string($conn, $_POST['product_coating_id']);
        $query = "UPDATE product_coating SET hidden='1' WHERE product_coating_id='$product_coating_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
