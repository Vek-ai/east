<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['work_order_user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

require_once '../config/database.php';

try {
    $db   = new Database();
    $conn = $db->connect();

    $current_user_id = (int)$_SESSION['work_order_user_id'];

    // Get unread message counts for each conversation, with distinct parameter names
    $stmt = $conn->prepare("
        SELECT 
            CASE 
                WHEN c.staff1_id = :current_user1 THEN c.staff2_id
                ELSE c.staff1_id
            END AS staff_id,
            CASE 
                WHEN c.staff1_id = :current_user2 THEN CONCAT(s2.staff_fname, ' ', s2.staff_lname)
                ELSE CONCAT(s1.staff_fname, ' ', s1.staff_lname)
            END AS staff_name,
            COUNT(m.message_id) AS unread_count
        FROM conversations c
        JOIN staff s1 ON c.staff1_id = s1.staff_id
        JOIN staff s2 ON c.staff2_id = s2.staff_id
        LEFT JOIN messages m 
            ON m.conversation_id = c.conversation_id 
           AND m.sender_id != :current_user3
           AND m.is_read = 0
        WHERE c.staff1_id = :current_user4 
           OR c.staff2_id = :current_user5
        GROUP BY 
            c.conversation_id, 
            c.staff1_id, 
            c.staff2_id, 
            s1.staff_fname, 
            s1.staff_lname, 
            s2.staff_fname, 
            s2.staff_lname
        HAVING COUNT(m.message_id) > 0
    ");

    $stmt->bindValue(':current_user1', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':current_user2', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':current_user3', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':current_user4', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':current_user5', $current_user_id, PDO::PARAM_INT);

    $stmt->execute();
    
    $unread_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success'       => true, 
        'unread_counts' => $unread_counts
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error'   => $e->getMessage()
    ]);
}
