<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "register_account") {
        $firstName = $conn->real_escape_string($_POST['customer_first_name'] ?? '');
        $lastName = $conn->real_escape_string($_POST['customer_last_name'] ?? '');
        $email = $conn->real_escape_string($_POST['contact_email'] ?? '');
        $username = $conn->real_escape_string($_POST['username'] ?? '');
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);

        $status = 0;
        $is_aproved = 0;
    
        $sql = "INSERT INTO customer (
            customer_first_name,
            customer_last_name,
            contact_email,
            status,
            is_approved,
            username,
            password
        ) VALUES (
            '$firstName',
            '$lastName',
            '$email',
            '$status',
            '$is_aproved',
            '$username',
            '$password'
        )";
    
        if ($conn->query($sql) === TRUE) {
            echo "success";
        } else {
            echo "Error: " . $conn->error;
        }
    }

    if ($action == "google_login") {
        $firstName = $payload['given_name'];
        $lastName = $payload['family_name'];
        $email = $payload['email'];
        $status = 0;                  
        $is_approved = 1;
        $googleToken = $id_token; 

        $sql = "INSERT INTO customer (
                    customer_first_name,
                    customer_last_name,
                    contact_email,
                    status,
                    is_approved,
                    google_token
                ) VALUES (
                    '$firstName',
                    '$lastName',
                    '$email',
                    '$status',
                    '$is_approved',
                    '$googleToken'
                )";

        if ($mysqli->query($sql) === TRUE) {
            echo "success";
        } else {
            echo "Error: " . $mysqli->error;
        }
    }
}
mysqli_close($conn);