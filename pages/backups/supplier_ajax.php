<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add_update") {
    $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    $supplier_name = mysqli_real_escape_string($conn, $_POST['supplier_name']);
    $supplier_website = mysqli_real_escape_string($conn, $_POST['supplier_website']);
    $contact_name = mysqli_real_escape_string($conn, $_POST['contact_name']);
    $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
    $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
    $contact_fax = mysqli_real_escape_string($conn, $_POST['contact_fax']);
    $secondary_name = mysqli_real_escape_string($conn, $_POST['secondary_name']);
    $secondary_phone = mysqli_real_escape_string($conn, $_POST['secondary_phone']);
    $secondary_email = mysqli_real_escape_string($conn, $_POST['secondary_email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $last_ordered_date = mysqli_real_escape_string($conn, $_POST['last_ordered_date']);
    $products = mysqli_real_escape_string($conn, $_POST['products']);
    $freight_rate = mysqli_real_escape_string($conn, $_POST['freight_rate']);
    $payment_terms = mysqli_real_escape_string($conn, $_POST['payment_terms']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // SQL query to check if the record exists
    $checkQuery = "SELECT * FROM supplier WHERE supplier_id = '$supplier_id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        // Record exists, update it
        $updateQuery = "UPDATE supplier SET 
                            supplier_name = '$supplier_name', 
                            supplier_website = '$supplier_website', 
                            contact_name = '$contact_name', 
                            contact_email = '$contact_email', 
                            contact_phone = '$contact_phone', 
                            contact_fax = '$contact_fax', 
                            secondary_name = '$secondary_name', 
                            secondary_phone = '$secondary_phone', 
                            secondary_email = '$secondary_email', 
                            address = '$address', 
                            last_ordered_date = '$last_ordered_date', 
                            products = '$products', 
                            freight_rate = '$freight_rate', 
                            payment_terms = '$payment_terms', 
                            comment = '$comment'
                        WHERE supplier_id = '$supplier_id'";
        if (mysqli_query($conn, $updateQuery)) {
            echo "Supplier updated successfully.";
        } else {
            echo "Error updating supplier: " . mysqli_error($conn);
        }
    } else {
        // Record does not exist, insert it
        $insertQuery = "INSERT INTO supplier (supplier_name, supplier_website, contact_name, contact_email, contact_phone, contact_fax, secondary_name, secondary_phone, secondary_email, address, last_ordered_date, products, freight_rate, payment_terms, comment) 
                VALUES ('$supplier_name', '$supplier_website', '$contact_name', '$contact_email', '$contact_phone', '$contact_fax', '$secondary_name', '$secondary_phone', '$secondary_email', '$address', '$last_ordered_date', '$products', '$freight_rate', '$payment_terms', '$comment')";

        if (mysqli_query($conn, $insertQuery)) {
            echo "New supplier added successfully.";
        } else {
            echo "Error adding supplier: " . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "change_status") {
    $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $new_status = $status == '0' ? '1' : '0';

    $updateStatusQuery = "UPDATE supplier SET status = '$new_status' WHERE supplier_id = '$supplier_id'";
    $result_update_status = mysqli_query($conn, $updateStatusQuery);

    if ($result_update_status) {
        echo "success";
    } else {
        echo "Error updating status: " . mysqli_error($conn);
    }
}
?>
