<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_category_id = mysqli_real_escape_string($conn, $_POST['product_category_id']);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
        $category_abreviations = mysqli_real_escape_string($conn, $_POST['category_abreviations']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        $multiplier = mysqli_real_escape_string($conn, floatval($_POST['multiplier'] ?? 0.00));
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_category WHERE product_category_id = '$product_category_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_product_category = $row['product_category'];
            $current_category_abreviations = $row['category_abreviations'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($product_category != $current_product_category) {
                $checkCategory = "SELECT * FROM product_category WHERE product_category = '$product_category'";
                $resultCategory = mysqli_query($conn, $checkCategory);
                if (mysqli_num_rows($resultCategory) > 0) {
                    $duplicates[] = "Product Category";
                }
            }

            if ($category_abreviations != $current_category_abreviations) {
                $checkAbreviations = "SELECT * FROM product_category WHERE category_abreviations = '$category_abreviations'";
                $resultAbreviations = mysqli_query($conn, $checkAbreviations);
                if (mysqli_num_rows($resultAbreviations) > 0) {
                    $duplicates[] = "Category Abbreviations";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE product_category SET product_category = '$product_category', category_abreviations = '$category_abreviations', notes = '$notes', multiplier = '$multiplier', last_edit = NOW(), edited_by = '$userid'  WHERE product_category_id = '$product_category_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Category updated successfully.";
                } else {
                    echo "Error updating category: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkCategory = "SELECT * FROM product_category WHERE product_category = '$product_category'";
            $resultCategory = mysqli_query($conn, $checkCategory);
            if (mysqli_num_rows($resultCategory) > 0) {
                $duplicates[] = "Product Category";
            }

            $checkAbreviations = "SELECT * FROM product_category WHERE category_abreviations = '$category_abreviations'";
            $resultAbreviations = mysqli_query($conn, $checkAbreviations);
            if (mysqli_num_rows($resultAbreviations) > 0) {
                $duplicates[] = "Category Abbreviations";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO product_category (product_category, category_abreviations, notes, multiplier, added_date, added_by) VALUES ('$product_category', '$category_abreviations', '$notes', '$multiplier', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "New category added successfully.";
                } else {
                    echo "Error adding category: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_category_id = mysqli_real_escape_string($conn, $_POST['product_category_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_category SET status = '$new_status' WHERE product_category_id = '$product_category_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_category') {
        $product_category_id = mysqli_real_escape_string($conn, $_POST['product_category_id']);
        $query = "UPDATE product_category SET hidden='1' WHERE product_category_id='$product_category_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
