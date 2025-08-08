<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $staff_product_access_id = mysqli_real_escape_string($conn, $_POST['staff_product_access_id']);
        $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);
        $product_ids = $_POST['product_ids'];
        $escaped_product_ids = array_map(function($id) use ($conn) {
            return mysqli_real_escape_string($conn, $id);
        }, $product_ids);
        $product_id = implode(',', $escaped_product_ids);
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM staff_product_access WHERE staff_product_access_id = '$staff_product_access_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_staff_id = $row['staff_id'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($staff_id != $current_staff_id) {
                $check_staff_id = "SELECT * FROM staff_product_access WHERE staff_id = '$staff_id'";
                $result_staff_id = mysqli_query($conn, $check_staff_id);
                if (mysqli_num_rows($result_staff_id) > 0) {
                    $duplicates[] = "Staff";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg record already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE staff_product_access SET staff_id = '$staff_id', product_id = '$product_id', last_edit = NOW(), edited_by = '$userid'  WHERE staff_product_access_id = '$staff_product_access_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "success-update";
                } else {
                    echo "Error updating staff product access: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $check_staff_id = "SELECT * FROM staff_product_access WHERE staff_id = '$staff_id'";
            $result_staff_id = mysqli_query($conn, $check_staff_id);
            if (mysqli_num_rows($result_staff_id) > 0) {
                $duplicates[] = "Staff";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO staff_product_access (staff_id, product_id, added_date, added_by) VALUES ('$staff_id', '$product_id', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "success-add";
                } else {
                    echo "Error adding staff product access: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $staff_product_access_id = mysqli_real_escape_string($conn, $_POST['staff_product_access_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE staff_product_access SET status = '$new_status' WHERE staff_product_access_id = '$staff_product_access_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_staff_product_access') {
        $staff_product_access_id = mysqli_real_escape_string($conn, $_POST['staff_product_access_id']);
        $query = "UPDATE staff_product_access SET hidden='1' WHERE staff_product_access_id='$staff_product_access_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
