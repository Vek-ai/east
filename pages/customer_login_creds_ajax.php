<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "update_credentials") {
        $response = ['success' => false, 'message' => ''];
        $customer_id = intval($_POST['customer_id']);
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $currentPassword = isset($_POST['currentPassword']) ? trim($_POST['currentPassword']) : '';
        $newPassword = isset($_POST['newPassword']) ? trim($_POST['newPassword']) : '';
        $confirmPassword = isset($_POST['confirmPassword']) ? trim($_POST['confirmPassword']) : '';

        if (!empty($username)) {
            $update_fields = [];
            $update_fields[] = "username = '$username'";

            if (!empty($currentPassword) && !empty($newPassword)) {
                if ($newPassword === $confirmPassword) {
                    $stmt_check_password = $conn->query("SELECT password FROM customer WHERE customer_id = $customer_id");
                    $user = $stmt_check_password->fetch_assoc();

                    if (password_verify($currentPassword, $user['password'])) {
                        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                        $update_fields[] = "password = '$newPasswordHash'";
                    } else {
                        $response['success'] = false;
                        $response['message'] = 'Current password is incorrect.';
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = 'New passwords do not match.';
                    echo json_encode($response);
                    exit;
                }
            }

            $update_sql = "UPDATE customer SET " . implode(', ', $update_fields) . " WHERE customer_id = $customer_id";
            if ($conn->query($update_sql) === TRUE) {
                $response['success'] = true;
                $response['message'] = 'Credentials updated successfully.';
            } else {
                $response['success'] = false;
                $response['message'] = 'Error updating credentials: ' . $conn->error;
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'Username is required.';
        }

        echo json_encode($response);
    }
}

?>