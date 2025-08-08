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
        $username = isset($_POST['username']) ? $conn->real_escape_string(trim($_POST['username'])) : '';
        $password = isset($_POST['password']) ? $conn->real_escape_string(trim($_POST['password'])) : '';

        if (!empty($username) && !empty($password)) {
            $update_fields = [];
            $update_fields[] = "username = '$username'";
            $update_fields[] = "password = '" . password_hash($password, PASSWORD_DEFAULT) . "'";

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
            $response['message'] = 'Username and password are required.';
        }

        echo json_encode($response);
        }
    }

?>