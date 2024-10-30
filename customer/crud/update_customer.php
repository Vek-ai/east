<?php
header('Content-Type: application/json');
include '../../includes/dbconn.php'; // Include your database connection file

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from POST request
    $customer_id = $_POST['customer_id'];
    $customer_first_name = $_POST['customer_first_name'];
    $customer_last_name = $_POST['customer_last_name'];
    $contact_email = $_POST['contact_email'];
    $contact_phone = $_POST['contact_phone'];
    $contact_fax = $_POST['contact_fax'];
    $customer_business_name = $_POST['customer_business_name'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $secondary_contact_name = $_POST['secondary_contact_name'];
    $secondary_contact_phone = $_POST['secondary_contact_phone'];
    $ap_contact_name = $_POST['ap_contact_name'];
    $ap_contact_email = $_POST['ap_contact_email'];
    $ap_contact_phone = $_POST['ap_contact_phone'];
    $tax_status = $_POST['tax_status'];
    $tax_exempt_number = $_POST['tax_exempt_number'];
    $customer_notes = $_POST['customer_notes'];
    $call_status = isset($_POST['call_status']) ? 1 : 0;

    // Prepare the SQL statement
    $query = "UPDATE customer SET 
              customer_first_name = ?, customer_last_name = ?, contact_email = ?, contact_phone = ?, contact_fax = ?, 
              customer_business_name = ?, address = ?, city = ?, state = ?, zip = ?, 
              secondary_contact_name = ?, secondary_contact_phone = ?, ap_contact_name = ?, 
              ap_contact_email = ?, ap_contact_phone = ?, tax_status = ?, tax_exempt_number = ?, 
              customer_notes = ?, call_status = ? 
              WHERE customer_id = ?";

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        'sssssssssssssssssisi',
        $customer_first_name, $customer_last_name, $contact_email, $contact_phone, $contact_fax,
        $customer_business_name, $address, $city, $state, $zip,
        $secondary_contact_name, $secondary_contact_phone, $ap_contact_name,
        $ap_contact_email, $ap_contact_phone, $tax_status, $tax_exempt_number,
        $customer_notes, $call_status, $customer_id
    );

    // Execute statement and update response based on result
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Customer updated successfully!';
    } else {
        $response['message'] = 'Failed to update customer.';
    }

    $stmt->close();
}

// Output the JSON response
echo json_encode($response);
?>
