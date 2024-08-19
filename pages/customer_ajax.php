<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $customer_first_name = mysqli_real_escape_string($conn, $_POST['customer_first_name']);
        $customer_last_name = mysqli_real_escape_string($conn, $_POST['customer_last_name']);
        $customer_business_name = mysqli_real_escape_string($conn, $_POST['customer_business_name']);
        $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
        $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
        $contact_fax = mysqli_real_escape_string($conn, $_POST['contact_fax']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $city = mysqli_real_escape_string($conn, $_POST['city']);
        $state = mysqli_real_escape_string($conn, $_POST['state']);
        $zip = mysqli_real_escape_string($conn, $_POST['zip']);
        $secondary_contact_name = mysqli_real_escape_string($conn, $_POST['secondary_contact_name']);
        $secondary_contact_phone = mysqli_real_escape_string($conn, $_POST['secondary_contact_phone']);
        $ap_contact_name = mysqli_real_escape_string($conn, $_POST['ap_contact_name']);
        $ap_contact_email = mysqli_real_escape_string($conn, $_POST['ap_contact_email']);
        $ap_contact_phone = mysqli_real_escape_string($conn, $_POST['ap_contact_phone']);
        $tax_status = mysqli_real_escape_string($conn, $_POST['tax_status']);
        $tax_exempt_number = mysqli_real_escape_string($conn, $_POST['tax_exempt_number']);
        $customer_notes = mysqli_real_escape_string($conn, $_POST['customer_notes']);
        $call_status = isset($_POST['call_status']) ? mysqli_real_escape_string($conn, $_POST['call_status']) : '';

        $customer_name = $customer_first_name . "" . $customer_last_name;

        $userid = mysqli_real_escape_string($conn, $_POST['userid']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM customer WHERE customer_id = '$customer_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
                $current_customer_id = $row['customer_id'];
                $current_customer_name = $row['customer_first_name'] . "" . $row['customer_last_name'];

            $duplicates = array();

            // Check for duplicates only if the new values are different from the current values
            if ($customer_name != $current_customer_name) {
                $checkCustomer = "SELECT CONCAT(customer_first_name, ' ', customer_last_name) AS full_name 
                FROM customer 
                WHERE customer_first_name = '$customer_first_name' 
                  AND customer_last_name = '$customer_last_name'";
                $resultCustomer = mysqli_query($conn, $checkCustomer);
                if (mysqli_num_rows($resultCustomer) > 0) {
                    $duplicates[] = "Customer";
                }
            }

            if (!empty($duplicates)) {
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                // No duplicates, proceed with update
                $updateQuery = "UPDATE customer
                    SET  
                        customer_first_name = '$customer_first_name', 
                        customer_last_name = '$customer_last_name', 
                        customer_business_name = '$customer_business_name', 
                        contact_email = '$contact_email', 
                        contact_phone = '$contact_phone', 
                        contact_fax = '$contact_fax', 
                        address = '$address', 
                        city = '$city', 
                        state = '$state',
                        zip = '$zip',
                        secondary_contact_name = '$secondary_contact_name',
                        secondary_contact_phone = '$secondary_contact_phone',
                        ap_contact_name = '$ap_contact_name',
                        ap_contact_email = '$ap_contact_email',
                        ap_contact_phone = '$ap_contact_phone',
                        tax_status = '$tax_status',
                        tax_exempt_number = '$tax_exempt_number',
                        customer_notes = '$customer_notes',
                        call_status = '$call_status'
                        
                        WHERE customer_id = '$customer_id'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "Product line updated successfully.";
                } else {
                    echo "Error updating product line: " . mysqli_error($conn);
                }
            }
        } else {
            // Record does not exist, perform duplicate checks before inserting
            $duplicates = array();
            $checkCustomer = "SELECT CONCAT(customer_first_name, ' ', customer_last_name) AS full_name 
            FROM customer 
            WHERE customer_first_name = '$customer_first_name' 
              AND customer_last_name = '$customer_last_name'";
            $resultCustomer = mysqli_query($conn, $checkCustomer);
            if (mysqli_num_rows($resultCustomer) > 0) {
                $duplicates[] = "Customer";
            }

            if(!empty($duplicates)){
                $msg = implode(", ", $duplicates);
                echo "$msg already exist! Please change to a unique value";
            } else {
                $insertQuery = "INSERT INTO customer (
                    customer_first_name, 
                    customer_last_name, 
                    customer_business_name, 
                    contact_email,
                    contact_phone,
                    contact_fax,
                    address,
                    city,
                    state,
                    zip,
                    secondary_contact_name,
                    secondary_contact_phone,
                    ap_contact_name,
                    ap_contact_email,
                    ap_contact_phone,
                    tax_status,
                    tax_exempt_number,
                    customer_notes,
                    call_status) 
                    VALUES (
                    '$customer_first_name', 
                    '$customer_last_name', 
                    '$customer_business_name',
                    '$contact_email',
                    '$contact_phone',
                    '$contact_fax',
                    '$address',
                    '$city',
                    '$state',
                    '$zip',
                    '$secondary_contact_name',
                    '$secondary_contact_phone',
                    '$ap_contact_name',
                    '$ap_contact_email',
                    '$ap_contact_phone',
                    '$tax_status',
                    '$tax_exempt_number',
                    '$customer_notes',
                    '$call_status')";
                if (mysqli_query($conn, $insertQuery)) {
                    echo "New product line added successfully.";
                } else {
                    echo "Error adding product line: " . mysqli_error($conn);
                }
            }
        }
    } 
    
    if ($action == "change_status") {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE customer SET status = '$new_status' WHERE customer_id = '$customer_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_customer') {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $query = "UPDATE customer SET hidden='1' WHERE customer_id='$customer_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
    mysqli_close($conn);
}
?>
