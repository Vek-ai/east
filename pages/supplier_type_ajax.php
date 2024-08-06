<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $supplier_type_id = mysqli_real_escape_string($conn, $_POST['supplier_type_id']);
        $supplier_type = mysqli_real_escape_string($conn, $_POST['supplier_type']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM supplier_type WHERE supplier_type_id = '$supplier_type_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_supplier_type = $row['supplier_type'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($supplier_type != $current_supplier_type) {
                $checkCategory = "SELECT * FROM supplier_type WHERE supplier_type = '$current_supplier_type'";
                $resultCategory = mysqli_query($conn, $checkCategory);
                if (mysqli_num_rows($resultCategory) > 0) {
                    $duplicates[] = "Supplier Type";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE supplier_type SET supplier_type = '$supplier_type', description = '$description', last_edit = NOW(), edited_by = '$userid'  WHERE supplier_type_id = '$supplier_type_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Supplier type updated successfully.";
                } else {
                    echo "Error updating supplier type: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkCategory = "SELECT * FROM supplier_type WHERE supplier_type = '$supplier_type'";
            $resultCategory = mysqli_query($conn, $checkCategory);
            if (mysqli_num_rows($resultCategory) > 0) {
                $duplicates[] = "Supplier Type";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO supplier_type (supplier_type, description, added_date, added_by) VALUES ('$supplier_type', '$description', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "New supplier type added successfully.";
                } else {
                    echo "Error adding supplier type: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $supplier_type_id = mysqli_real_escape_string($conn, $_POST['supplier_type_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE supplier_type SET status = '$new_status' WHERE supplier_type_id = '$supplier_type_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == 'hide_supplier_type') {
        $supplier_type_id = mysqli_real_escape_string($conn, $_POST['supplier_type_id']);
        $query = "UPDATE supplier_type SET hidden='1' WHERE supplier_type_id='$supplier_type_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
