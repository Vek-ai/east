<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
include "../../includes/dbconn.php";

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $redirect = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : 'index.php';
    $username = $conn->real_escape_string($username);

    // SQL query to fetch user
    $sql = "SELECT * FROM staff WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the result
        $row = $result->fetch_assoc();
        $db_password = $row['password_hash'];
        $userid = $row['staff_id'];

        // Verify the password
        if ($db_password == $password) {
            $_SESSION['userid'] = $userid;
            setcookie("userid", $userid, time() + (86400 * 30), "/");
            
            echo "success|$redirect";
        } else {
            echo 'Invalid username or password.';
        }
    } else {
        echo 'Invalid username or password.';
    }
} else {
    echo 'Invalid request method.';
}
?>
