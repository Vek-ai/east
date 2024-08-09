<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_gauge_id = mysqli_real_escape_string($conn, $_POST['product_gauge_id']);
        $product_gauge = mysqli_real_escape_string($conn, $_POST['product_gauge']);
        $gauge_abbreviations = mysqli_real_escape_string($conn, $_POST['gauge_abbreviations']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product_gauge WHERE product_gauge_id = '$product_gauge_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_product_gauge = $row['product_gauge'];
            $current_gauge_abbreviations = $row['gauge_abbreviations'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($product_gauge != $current_product_gauge) {
                $checkProductGauge = "SELECT * FROM product_gauge WHERE product_gauge = '$product_gauge'";
                $resultProductGauge = mysqli_query($conn, $checkProductGauge);
                if (mysqli_num_rows($resultProductGauge) > 0) {
                    $duplicates[] = "Product gauge";
                }
            }

            if ($gauge_abbreviations != $current_gauge_abbreviations) {
                $checkAbreviations = "SELECT * FROM product_gauge WHERE gauge_abbreviations = '$gauge_abbreviations'";
                $resultAbreviations = mysqli_query($conn, $checkAbreviations);
                if (mysqli_num_rows($resultAbreviations) > 0) {
                    $duplicates[] = "Gauge Abbreviations";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE product_gauge SET product_gauge = '$product_gauge', gauge_abbreviations = '$gauge_abbreviations', notes = '$notes', last_edit = NOW(), edited_by = '$userid'  WHERE product_gauge_id = '$product_gauge_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "update-success";
                } else {
                    echo "Error updating product gauge: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkProductGauge = "SELECT * FROM product_gauge WHERE product_gauge = '$product_gauge'";
            $resultProductGauge = mysqli_query($conn, $checkProductGauge);
            if (mysqli_num_rows($resultProductGauge) > 0) {
                $duplicates[] = "Product Gauge";
            }

            $checkAbreviations = "SELECT * FROM product_gauge WHERE gauge_abbreviations = '$gauge_abbreviations'";
            $resultAbreviations = mysqli_query($conn, $checkAbreviations);
            if (mysqli_num_rows($resultAbreviations) > 0) {
                $duplicates[] = "Gauge Abbreviations";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO product_gauge (product_gauge, gauge_abbreviations, notes, added_date, added_by) VALUES ('$product_gauge', '$gauge_abbreviations', '$notes', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "add-success";
                } else {
                    echo "Error adding product gauge: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $product_gauge_id = mysqli_real_escape_string($conn, $_POST['product_gauge_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product_gauge SET status = '$new_status' WHERE product_gauge_id = '$product_gauge_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_product_gauge') {
        $product_gauge_id = mysqli_real_escape_string($conn, $_POST['product_gauge_id']);
        $query = "UPDATE product_gauge SET hidden='1' WHERE product_gauge_id='$product_gauge_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
