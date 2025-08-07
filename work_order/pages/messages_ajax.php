<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if (isset($_POST['fetch_chat_history'])) {
    $current_user_id = $_SESSION['work_order_user_id'];
    $other_user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    $conn->query("
        UPDATE staff_messages 
        SET is_read = 1 
        WHERE sender_id = $other_user_id 
          AND receiver_id = $current_user_id 
    ");

    $sql = "
        SELECT sm.*, 
            s1.staff_fname AS sender_fname, 
            s1.staff_lname AS sender_lname, 
            s2.staff_fname AS receiver_fname, 
            s2.staff_lname AS receiver_lname
        FROM staff_messages sm
        LEFT JOIN staff s1 ON sm.sender_id = s1.staff_id
        LEFT JOIN staff s2 ON sm.receiver_id = s2.staff_id
        WHERE (sm.sender_id = $current_user_id AND sm.receiver_id = $other_user_id)
        OR (sm.sender_id = $other_user_id AND sm.receiver_id = $current_user_id)
        ORDER BY sm.sent_at ASC";

    $result = $conn->query($sql);

    $messages = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $is_sender = $row['sender_id'] == $current_user_id;
            $sender_name = $is_sender ? $row['receiver_fname'] . ' ' . $row['receiver_lname'] : $row['sender_fname'] . ' ' . $row['sender_lname'];
            $messages[] = [
                'isWithMessage' => true,
                'sender_name' => $sender_name,
                'message' => htmlspecialchars($row['message']),
                'time_sent' => date('g:i A', strtotime($row['sent_at'])),
                'is_sender' => $is_sender
            ];
        }
    } else {
        $messages[] = [
            'isWithMessage' => false,
            'message' => 'No messages found with this user.'
        ];
    }

    

    $other_user_name = '';
    if (!empty($other_user_id)) {
        $friend_details = getStaffDetails($other_user_id);
        $friend_name = $friend_details['staff_fname'] .' ' .$friend_details['staff_lname'];
    }

    $response = [
        'messages' => $messages,
        'friend_name' => $friend_name
    ];

    $conn->close();

    echo json_encode($response);

}

if (isset($_POST['send_message'])) {
    $sender_id = $_SESSION['work_order_user_id'];
    $message = $_POST['message'];
    $receiver_id = $_POST['user_id'];

    $sender_id = $conn->real_escape_string($sender_id);
    $message = $conn->real_escape_string($message);
    $receiver_id = $conn->real_escape_string($receiver_id);

    $sql = "INSERT INTO staff_messages (sender_id, receiver_id, message, sent_at, is_read) 
            VALUES ('$sender_id', '$receiver_id', '$message', NOW(), 0)";

    if ($conn->query($sql) === TRUE) {
        echo 'success';
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error sending message: ' . $conn->error]);
    }
}

if (isset($_POST['search_contact'])) {
    $search = $conn->real_escape_string($_POST['search']);
    
    $sql = "SELECT * FROM staff WHERE CONCAT(staff_fname, ' ', staff_lname) LIKE '%$search%' OR email LIKE '%$search%' LIMIT 10";
    $result = $conn->query($sql);

    $image = "../assets/images/profile/user-6.jpg";

    $staffs = [];
    while ($row = $result->fetch_assoc()) {
        $staffs[] = [
            'id' => $row['staff_id'],
            'name' => $row['staff_fname'] .' ' .$row['staff_lname'],
            'email' => $row['email'],
            'image' => $image,
        ];
    }

    echo json_encode($staffs);

}