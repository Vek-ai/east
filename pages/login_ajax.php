<?php
include "../includes/dbconn.php";

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $redirect = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : 'index.php';
    $username = $conn->real_escape_string($username);

    // SQL query to fetch user
    $sql = "SELECT userid, password FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the result
        $row = $result->fetch_assoc();
        $db_password = $row['password'];
        $userid = $row['userid'];

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
