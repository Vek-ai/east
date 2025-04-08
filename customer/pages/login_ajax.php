<?php
session_start();
error_reporting(E_ALL & ~E_DEPRECATED);

require '../../includes/dbconn.php';
require '../../includes/functions.php';
require '../../includes/vendor/autoload.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "google_login") {
        $id_token = $_POST['token'];
    
        $response = file_get_contents('https://oauth2.googleapis.com/tokeninfo?id_token=' . $id_token);
        $data = json_decode($response, true);
    
        if (isset($data['email'], $data['sub']) && $data['aud'] === $google_auth_client_id) {
            $googleId = $conn->real_escape_string($data['sub']);
            $sql = "SELECT customer_id, is_approved FROM customer WHERE google_token = '$googleId'";
            $result = $conn->query($sql);
    
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $customer_id = $row['customer_id'];
                $is_approved = $row['is_approved'];
    
                if ($is_approved == 1) {
                    $_SESSION['customer_id'] = $customer_id;
                    setcookie("userid", $customer_id, time() + (86400 * 30), "/");
    
                    echo "success";
                } else {
                    echo 'Account not yet approved. Please wait for admin to approve your application.';
                }
            } else {
                echo 'Google Login Failed: No matching user found in DB';
            }
        } else {
            echo "Invalid token.";
        }
    }
    
    
}
mysqli_close($conn);