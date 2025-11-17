<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

require_once '../config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Get unread message counts for each conversation
    $stmt = $conn->prepare("
        SELECT 
            CASE 
                WHEN c.staff1_id = :current_user THEN c.staff2_id
                ELSE c.staff1_id
            END as staff_id,
            CASE 
                WHEN c.staff1_id = :current_user THEN CONCAT(s2.staff_fname, ' ', s2.staff_lname)
                ELSE CONCAT(s1.staff_fname, ' ', s1.staff_lname)
            END as staff_name,
            COUNT(m.message_id) as unread_count
        FROM conversations c
        JOIN staff s1 ON c.staff1_id = s1.staff_id
        JOIN staff s2 ON c.staff2_id = s2.staff_id
        LEFT JOIN messages m ON m.conversation_id = c.conversation_id 
            AND m.sender_id != :current_user 
            AND m.is_read = 0
        WHERE c.staff1_id = :current_user OR c.staff2_id = :current_user
        GROUP BY c.conversation_id, c.staff1_id, c.staff2_id, s1.staff_fname, s1.staff_lname, s2.staff_fname, s2.staff_lname
        HAVING COUNT(m.message_id) > 0
    ");
    $stmt->bindParam(':current_user', $_SESSION['userid']);
    $stmt->execute();
    
    $unread_counts = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'unread_counts' => $unread_counts]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
