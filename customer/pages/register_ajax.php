<?php
session_start();
error_reporting(E_ALL & ~E_DEPRECATED);

require '../../includes/dbconn.php';
require '../../includes/functions.php';
require '../../includes/vendor/autoload.php';

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
        $id_token = $_POST['token'];
    
        $response = file_get_contents('https://oauth2.googleapis.com/tokeninfo?id_token=' . $id_token);
        $data = json_decode($response, true);
    
        if (isset($data['email'], $data['sub']) && $data['aud'] === $google_auth_client_id) {
            $firstName = $conn->real_escape_string($data['given_name']);
            $lastName = $conn->real_escape_string($data['family_name']);
            $email = $conn->real_escape_string($data['email']);
            $googleId = $conn->real_escape_string($data['sub']);
    
            $status = 0;
            $is_approved = 0;
    
            $check = "SELECT customer_id FROM customer WHERE contact_email = '$email'";
            $res = $conn->query($check);
    
            if ($res && $res->num_rows > 0) {
                echo "User already exists.";
            } else {
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
                            '$googleId'
                        )";
    
                if ($conn->query($sql) === TRUE) {
                    echo "success";
                } else {
                    echo "Error: " . $conn->error;
                }
            }
        } else {
            echo "Invalid token.";
        }
    }
    
    
}
mysqli_close($conn);