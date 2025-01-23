<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
header('Content-Type: application/json');
include '../../includes/dbconn.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();

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
    $tax_exempt_number = $_POST['tax_exempt_number'];
    $customer_notes = $_POST['customer_notes'];
    $call_status = isset($_POST['call_status']) ? 1 : 0;
    $currentPassword = isset($_POST['currentPassword']) ? trim($_POST['currentPassword']) : '';
    $newPassword = isset($_POST['newPassword']) ? trim($_POST['newPassword']) : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';

    if (!empty($currentPassword) && !empty($newPassword)) {
        if ($newPassword === $confirmPassword) {
            $stmt_check_password = $conn->query("SELECT password FROM customer WHERE customer_id = $customer_id");
            $user = $stmt_check_password->fetch_assoc();

            if (password_verify($currentPassword, $user['password'])) {
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $update_password_sql = "UPDATE customer SET password = '$newPasswordHash' WHERE customer_id = $customer_id";
                if ($conn->query($update_password_sql) === TRUE) {
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Error updating password: ' . $conn->error;
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Current password is incorrect.';
                echo json_encode($response);
                exit;
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'New Passwords do not match.';
            echo json_encode($response);
            exit;
        }
    }

    $query = "UPDATE customer SET 
              customer_first_name = '$customer_first_name', customer_last_name = '$customer_last_name', contact_email = '$contact_email', contact_phone = '$contact_phone', contact_fax = '$contact_fax', 
              customer_business_name = '$customer_business_name', address = '$address', city = '$city', state = '$state', zip = '$zip', 
              secondary_contact_name = '$secondary_contact_name', secondary_contact_phone = '$secondary_contact_phone', ap_contact_name = '$ap_contact_name', 
              ap_contact_email = '$ap_contact_email', ap_contact_phone = '$ap_contact_phone', tax_status = '$tax_status', tax_exempt_number = '$tax_exempt_number', 
              customer_notes = '$customer_notes', call_status = '$call_status' 
              WHERE customer_id = '$customer_id'";

    if ($conn->query($query) === TRUE) {
        $response['success'] = true;
        $response['message'] = 'Customer updated successfully!';
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to update customer: ' . $conn->error;
    }

    
}

echo json_encode($response);
?>
