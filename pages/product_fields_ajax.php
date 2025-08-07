<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_fields_id = mysqli_real_escape_string($conn, $_POST['product_fields_id']);
        $product_category_id = mysqli_real_escape_string($conn, $_POST['product_category_id']);
        $field = isset($_POST['fields']) ? $_POST['fields'] : [];
        $fields = implode(',', $field);
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_fields WHERE product_fields_id = '$product_fields_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_category_id = $row['product_category_id'];

            $duplicates = [];

            // Check for duplicates only if the new values are different from the current values
            if ($product_category_id != $current_category_id) {
                $check_category = "SELECT * FROM product_fields WHERE product_category_id = '$product_category_id'";
                $result_category = mysqli_query($conn, $check_category);
                if (mysqli_num_rows($result_category) > 0) {
                    $duplicates[] = "Product Category";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg record already exists! Please change to a unique value.";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE product_fields SET product_category_id = '$product_category_id', fields = '$fields', last_edit = NOW(), edited_by = '$userid' WHERE product_fields_id = '$product_fields_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "success-update";
                } else {
                    echo "Error updating product fields: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $check_category = "SELECT * FROM product_fields WHERE product_category_id = '$product_category_id'";
            $result_category = mysqli_query($conn, $check_category);
            $duplicates = [];

            if (mysqli_num_rows($result_category) > 0) {
                $duplicates[] = "Product Category";
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exists! Please change to a unique value.";
            } else {
                $insertQuery = "INSERT INTO product_fields (product_category_id, fields, added_date, added_by) VALUES ('$product_category_id', '$fields', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "success-add";
                } else {
                    echo "Error adding product fields: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    elseif ($action == "change_status") {
        $product_fields_id = mysqli_real_escape_string($conn, $_POST['product_fields_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_fields SET status = '$new_status' WHERE product_fields_id = '$product_fields_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    elseif ($action == 'hide_field_product') {
        $product_fields_id = mysqli_real_escape_string($conn, $_POST['product_fields_id']);
        $query = "UPDATE product_fields SET hidden='1' WHERE product_fields_id='$product_fields_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'Error hiding product field: ' . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
}
?>
