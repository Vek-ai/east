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
        $stock_type_id = mysqli_real_escape_string($conn, $_POST['stock_type_id']);
        $stock_type = mysqli_real_escape_string($conn, $_POST['stock_type']);
        $stock_abbreviations = mysqli_real_escape_string($conn, $_POST['stock_abbreviations']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM stock_type WHERE stock_type_id = '$stock_type_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_stock_type = $row['stock_type'];
            $current_stock_abbreviations = $row['stock_abbreviations'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($stock_type != $current_stock_type) {
                $checkStock = "SELECT * FROM stock_type WHERE stock_type = '$stock_type'";
                $resultStock = mysqli_query($conn, $checkStock);
                if (mysqli_num_rows($resultStock) > 0) {
                    $duplicates[] = "Stock type";
                }
            }

            if ($stock_abbreviations != $current_stock_abbreviations) {
                $checkAbreviations = "SELECT * FROM stock_type WHERE stock_abbreviations = '$stock_abbreviations'";
                $resultAbreviations = mysqli_query($conn, $checkAbreviations);
                if (mysqli_num_rows($resultAbreviations) > 0) {
                    $duplicates[] = "Stock type Abbreviations";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE stock_type SET stock_type = '$stock_type', stock_abbreviations = '$stock_abbreviations', notes = '$notes', last_edit = NOW(), edited_by = '$userid'  WHERE stock_type_id = '$stock_type_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "update-success";
                } else {
                    echo "Error updating stock type: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkStock = "SELECT * FROM stock_type WHERE stock_type = '$stock_type'";
            $resultStock = mysqli_query($conn, $checkStock);
            if (mysqli_num_rows($resultStock) > 0) {
                $duplicates[] = "Stock type";
            }

            $checkAbreviations = "SELECT * FROM stock_type WHERE stock_abbreviations = '$stock_abbreviations'";
            $resultAbreviations = mysqli_query($conn, $checkAbreviations);
            if (mysqli_num_rows($resultAbreviations) > 0) {
                $duplicates[] = "Stock type Abbreviations";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO stock_type (stock_type, stock_abbreviations, notes, added_date, added_by) VALUES ('$stock_type', '$stock_abbreviations', '$notes', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "add-success";
                } else {
                    echo "Error adding stock type: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $stock_type_id = mysqli_real_escape_string($conn, $_POST['stock_type_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE stock_type SET status = '$new_status' WHERE stock_type_id = '$stock_type_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_stock_type') {
        $stock_type_id = mysqli_real_escape_string($conn, $_POST['stock_type_id']);
        $query = "UPDATE stock_type SET hidden='1' WHERE stock_type_id='$stock_type_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
