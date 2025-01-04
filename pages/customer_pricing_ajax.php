<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $pricing_name = mysqli_real_escape_string($conn, $_POST['pricing_name']);
        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM customer_pricing WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            $current_pricing_name = $row['pricing_name'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($pricing_name != $current_pricing_name) {
                $checkCategory = "SELECT * FROM customer_pricing WHERE pricing_name = '$pricing_name'";
                $resultCategory = mysqli_query($conn, $checkCategory);
                if (mysqli_num_rows($resultCategory) > 0) {
                    $duplicates[] = "Pricing Name";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE customer_pricing SET pricing_name = '$pricing_name', last_edit = NOW(), edited_by = '$userid'  WHERE id = '$id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "success_update";
                } else {
                    echo "Error updating category: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkCategory = "SELECT * FROM customer_pricing WHERE pricing_name = '$pricing_name'";
            $resultCategory = mysqli_query($conn, $checkCategory);
            if (mysqli_num_rows($resultCategory) > 0) {
                $duplicates[] = "Pricing Name";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO customer_pricing (pricing_name, added_date, added_by) VALUES ('$pricing_name', NOW(), '$userid')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "success_add";
                } else {
                    echo "Error adding category: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE customer_pricing SET status = '$new_status' WHERE id = '$id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_customer_pricing') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "UPDATE customer_pricing SET hidden='1' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
