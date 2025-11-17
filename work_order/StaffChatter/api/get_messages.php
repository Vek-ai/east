<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['work_order_user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

require_once '../config/database.php';

$current_user_id = (int)($_SESSION['work_order_user_id']);
$other_staff_id  = (int)($_GET['other_staff_id'] ?? 0);
$last_message_id = (int)($_GET['last_message_id'] ?? 0);

if (!$other_staff_id) {
    echo json_encode(['success' => false, 'error' => 'Missing staff_id']);
    exit();
}

try {
    $db   = new Database();
    $conn = $db->connect();

    // 1️⃣ Find the conversation between these two users
    $stmt = $conn->prepare("
        SELECT conversation_id
        FROM conversations
        WHERE (staff1_id = :current_user1 AND staff2_id = :other_user1)
           OR (staff1_id = :other_user2 AND staff2_id = :current_user2)
        LIMIT 1
    ");
    $stmt->bindValue(':current_user1', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':other_user1',  $other_staff_id,  PDO::PARAM_INT);
    $stmt->bindValue(':other_user2',  $other_staff_id,  PDO::PARAM_INT);
    $stmt->bindValue(':current_user2', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();

    $conversation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$conversation) {
        // No conversation yet → no messages
        echo json_encode(['success' => true, 'messages' => []]);
        exit();
    }

    $conversation_id = (int)$conversation['conversation_id'];

    // 2️⃣ Load messages for this conversation
    if ($last_message_id > 0) {
        // Only load new messages
        $stmt = $conn->prepare("
            SELECT 
                message_id,
                conversation_id,
                sender_id,
                message_text,
                file_name,
                file_path,
                file_size,
                file_type,
                is_read,
                created_at
            FROM messages
            WHERE conversation_id = :conversation_id
              AND message_id > :last_message_id
            ORDER BY message_id ASC
        ");
        $stmt->bindValue(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->bindValue(':last_message_id', $last_message_id, PDO::PARAM_INT);
    } else {
        // First load: get full history
        $stmt = $conn->prepare("
            SELECT 
                message_id,
                conversation_id,
                sender_id,
                message_text,
                file_name,
                file_path,
                file_size,
                file_type,
                is_read,
                created_at
            FROM messages
            WHERE conversation_id = :conversation_id
            ORDER BY message_id ASC
        ");
        $stmt->bindValue(':conversation_id', $conversation_id, PDO::PARAM_INT);
    }

    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3️⃣ Mark messages from the *other* user as read
    if (!empty($messages)) {
        $stmt = $conn->prepare("
            UPDATE messages
            SET is_read = 1
            WHERE conversation_id = :conversation_id
              AND sender_id = :other_user
              AND is_read = 0
        ");
        $stmt->bindValue(':conversation_id', $conversation_id, PDO::PARAM_INT);
        $stmt->bindValue(':other_user', $other_staff_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    echo json_encode([
        'success'  => true,
        'messages' => $messages
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
