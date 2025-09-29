<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if (isset($_POST['fetch_chat_history'])) {
    $current_user_id = $_SESSION['customer_id'];
    $other_user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    $conn->query("
        UPDATE staff_messages 
        SET is_read = 1 
        WHERE sender_id = $other_user_id 
          AND receiver_id = $current_user_id 
    ");

    $sql = "
        SELECT cm.*, 
            c1.customer_first_name AS sender_fname, 
            c1.customer_last_name AS sender_lname, 
            c2.customer_first_name AS receiver_fname, 
            c2.customer_last_name AS receiver_lname
        FROM customer_messages cm
        LEFT JOIN customer c1 ON cm.sender_id = c1.customer_id
        LEFT JOIN customer c2 ON cm.receiver_id = c2.customer_id
        WHERE (cm.sender_id = $current_user_id AND cm.receiver_id = $other_user_id)
        OR (cm.sender_id = $other_user_id AND cm.receiver_id = $current_user_id)
        ORDER BY cm.sent_at ASC";

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
        $friend_details = getCustomerDetails($other_user_id);
        $friend_name = $friend_details['customer_first_name'] .' ' .$friend_details['customer_last_name'];
    }

    $response = [
        'messages' => $messages,
        'friend_name' => $friend_name
    ];

    $conn->close();

    echo json_encode($response);

}

if (isset($_POST['send_message'])) {
    $sender_id = $_SESSION['customer_id'];
    $message = $_POST['message'];
    $receiver_id = $_POST['user_id'];

    $sender_id = $conn->real_escape_string($sender_id);
    $message = $conn->real_escape_string($message);
    $receiver_id = $conn->real_escape_string($receiver_id);

    $sql = "INSERT INTO customer_messages (sender_id, receiver_id, message, sent_at, is_read) 
            VALUES ('$sender_id', '$receiver_id', '$message', NOW(), 0)";

    if ($conn->query($sql) === TRUE) {
        echo $sql;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error sending message: ' . $conn->error]);
    }
}

if (isset($_POST['search_contact'])) {
    $search = $conn->real_escape_string($_POST['search']);
    
    $sql = "SELECT * FROM customer WHERE CONCAT(customer_first_name, ' ', customer_last_name) LIKE '%$search%' OR contact_email LIKE '%$search%' LIMIT 10";
    $result = $conn->query($sql);

    $image = "../assets/images/profile/user-6.jpg";

    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = [
            'id' => $row['customer_id'],
            'name' => $row['customer_first_name'] .' ' .$row['customer_last_name'],
            'email' => $row['contact_email'],
            'image' => $image,
        ];
    }

    echo json_encode($customers);

}