<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $customer_type_id = mysqli_real_escape_string($conn, $_POST['customer_type_id']);
        $customer_type_name = mysqli_real_escape_string($conn, $_POST['customer_type_name']);
        $customer_type_of_work = mysqli_real_escape_string($conn, $_POST['customer_type_of_work']);
        $customer_work_radius = mysqli_real_escape_string($conn, $_POST['customer_work_radius']);
        $customer_crew_size = mysqli_real_escape_string($conn, $_POST['customer_crew_size']);
        $customer_notes = mysqli_real_escape_string($conn, $_POST['customer_notes']);
        $customer_price_cat = mysqli_real_escape_string($conn, $_POST['customer_price_cat']);
        $cust_price_lvl_date = mysqli_real_escape_string($conn, $_POST['cust_price_lvl_date']);

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM customer_types WHERE customer_type_id = '$customer_type_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
                $current_customer_type_id = $row['customer_type_id'];
                $current_customer_type_name = $row['customer_type_name'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($customer_type_name != $current_customer_type_name) {
                $checkCustomerType = "SELECT * FROM customer_types WHERE customer_type_name = '$customer_type_name'";
                $resultCustomerType = mysqli_query($conn, $checkCustomerType);
                if (mysqli_num_rows($resultCustomerType) > 0) {
                    $duplicates[] = "Customer Type";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE customer_types
                    SET  
                        customer_type_name = '$customer_type_name', 
                        customer_type_of_work = '$customer_type_of_work', 
                        customer_work_radius = '$customer_work_radius', 
                        customer_crew_size = '$customer_crew_size', 
                        last_update_date = NOW(),
                        customer_notes = '$customer_notes', 
                        customer_price_cat = '$customer_price_cat', 
                        cust_price_lvl_date = '$cust_price_lvl_date'
                        
                        WHERE customer_type_id = '$customer_type_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Product line updated successfully.";
                } else {
                    echo "Error updating product line: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkCustomerType = "SELECT * FROM customer_types WHERE customer_type_name = '$customer_type_name'";
            $resultCustomerType = mysqli_query($conn, $checkCustomerType);
            if (mysqli_num_rows($resultCustomerType) > 0) {
                $duplicates[] = "Customer Type";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO customer_types 
                (
                    customer_type_id, 
                    customer_type_name, 
                    customer_type_of_work, 
                    customer_work_radius,
                    customer_crew_size,
                    customer_notes,
                    customer_price_cat,
                    cust_price_lvl_date
                ) 
                VALUES 
                (
                    '$customer_type_id', 
                    '$customer_type_name', 
                    '$customer_type_of_work',
                    '$customer_work_radius',
                    '$customer_crew_size',
                    '$customer_notes',
                    '$customer_price_cat',
                    '$cust_price_lvl_date'
                )";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "New product line added successfully.";
                } else {
                    echo "Error adding product line: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $customer_type_id = mysqli_real_escape_string($conn, $_POST['customer_type_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE customer_types SET status = '$new_status' WHERE customer_type_id = '$customer_type_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_customer') {
        $customer_type_id = mysqli_real_escape_string($conn, $_POST['customer_type_id']);
        $query = "UPDATE customer_types SET hidden='1' WHERE customer_type_id ='$customer_type_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
