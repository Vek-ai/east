<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

require_once '../config/database.php';

$other_staff_id = (int)($_GET['other_staff_id'] ?? 0);
$last_message_id = (int)($_GET['last_message_id'] ?? 0);

if (!$other_staff_id) {
    echo json_encode(['success' => false, 'error' => 'Missing staff_id']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Find conversation
    $stmt = $conn->prepare("
        SELECT conversation_id FROM conversations 
        WHERE (staff1_id = :current_user AND staff2_id = :other_user)
           OR (staff1_id = :other_user AND staff2_id = :current_user)
    ");
    $stmt->bindParam(':current_user', $_SESSION['userid']);
    $stmt->bindParam(':other_user', $other_staff_id);
    $stmt->execute();
    
    $conversation = $stmt->fetch();
    
    if (!$conversation) {
        echo json_encode(['success' => true, 'messages' => []]);
        exit();
    }
    
    $conversation_id = $conversation['conversation_id'];
    
    // Get messages
    if ($last_message_id > 0) {
        // Get only new messages
        $stmt = $conn->prepare("
            SELECT message_id, sender_id, message_text, file_name, file_path, file_type, file_size, created_at 
            FROM messages 
            WHERE conversation_id = :conversation_id AND message_id > :last_message_id
            ORDER BY created_at ASC
        ");
        $stmt->bindParam(':conversation_id', $conversation_id);
        $stmt->bindParam(':last_message_id', $last_message_id);
    } else {
        // Get all messages
        $stmt = $conn->prepare("
            SELECT message_id, sender_id, message_text, file_name, file_path, file_type, file_size, created_at 
            FROM messages 
            WHERE conversation_id = :conversation_id
            ORDER BY created_at ASC
        ");
        $stmt->bindParam(':conversation_id', $conversation_id);
    }
    
    $stmt->execute();
    $messages = $stmt->fetchAll();
    
    // Mark received messages as read
    if (!empty($messages)) {
        $stmt = $conn->prepare("
            UPDATE messages 
            SET is_read = 1 
            WHERE conversation_id = :conversation_id 
            AND sender_id = :other_user 
            AND is_read = 0
        ");
        $stmt->bindParam(':conversation_id', $conversation_id);
        $stmt->bindParam(':other_user', $other_staff_id);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
