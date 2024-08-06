<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action']) && $_REQUEST['action'] == "add_update"){
    $warehouse_id = mysqli_real_escape_string($conn, $_POST['warehouse_id']);
    $warehouse_name = mysqli_real_escape_string($conn, $_POST['warehouse_name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $warehouse_capacity = mysqli_real_escape_string($conn, $_POST['warehouse_capacity']);
    $warehouse_rows = mysqli_real_escape_string($conn, $_POST['warehouse_rows']);
    $shelf = mysqli_real_escape_string($conn, $_POST['shelf']);
    $bin = mysqli_real_escape_string($conn, $_POST['bin']);
    $bin_capacity = mysqli_real_escape_string($conn, $_POST['bin_capacity']);
    $count_date = mysqli_real_escape_string($conn, $_POST['count_date']);
    $contact_person = mysqli_real_escape_string($conn, $_POST['contact_person']);
    $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
    $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);

    // SQL query to check if the record exists
    $checkQuery = "SELECT * FROM warehouse WHERE warehouse_id = '$warehouse_id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Record exists, update it
        $updateQuery = "UPDATE warehouse SET 
                            warehouse_name = '$warehouse_name', 
                            location = '$location', 
                            warehouse_capacity = '$warehouse_capacity', 
                            warehouse_rows = '$warehouse_rows', 
                            shelf = '$shelf', 
                            bin = '$bin', 
                            bin_capacity = '$bin_capacity', 
                            count_date = '$count_date', 
                            contact_person = '$contact_person', 
                            contact_phone = '$contact_phone', 
                            contact_email = '$contact_email'
                        WHERE warehouse_id = '$warehouse_id'";
        if (mysqli_query($conn, $updateQuery)) {
            echo "Warehouse updated successfully.";
        } else {
            echo "Error updating warehouse: " . mysqli_error($conn);
        }
    } else {
        // Record does not exist, insert it
        $insertQuery = "INSERT INTO warehouse (warehouse_name, location, warehouse_capacity, warehouse_rows, shelf, bin, bin_capacity, count_date, contact_person, contact_phone, contact_email) 
                VALUES ('$warehouse_name', '$location', '$warehouse_capacity', '$warehouse_rows', '$shelf', '$bin', '$bin_capacity', '$count_date', '$contact_person', '$contact_phone', '$contact_email')";

        if (mysqli_query($conn, $insertQuery)) {
            echo "New warehouse added successfully.";
        } else {
            echo "Error adding warehouse: " . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == "change_status"){
    $warehouse_id = mysqli_real_escape_string($conn, $_POST['warehouse_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $new_status = $status == '0' ? '1' : '0';

    $updateStatusQuery = "UPDATE warehouse SET status = '$new_status' WHERE warehouse_id = '$warehouse_id'";
    $result_update_status = mysqli_query($conn, $updateStatusQuery);

    if ($result_update_status) {
        echo "success";
    } else {
        echo "Error updating status: " . mysqli_error($conn);
    }
}
?>
