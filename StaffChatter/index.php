<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

$db = new Database();
$conn = $db->connect();

// Get all staff members except current user
$staff_sql = "
    SELECT 
        staff_id, 
        staff_fname, 
        staff_lname, 
        username
    FROM staff
    WHERE staff_id != :current_user
    ORDER BY staff_fname, staff_lname
";

$stmt = $conn->prepare($staff_sql);
$stmt->execute([
    ':current_user' => $_SESSION['userid']
]);
$staff_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get conversations with unread message counts
$conversations_query = "
    SELECT 
        c.conversation_id,
        c.staff1_id,
        c.staff2_id,
        CASE 
            WHEN c.staff1_id = :current_user THEN s2.staff_id
            ELSE s1.staff_id
        END AS other_staff_id,
        CASE 
            WHEN c.staff1_id = :current_user THEN CONCAT(s2.staff_fname, ' ', s2.staff_lname)
            ELSE CONCAT(s1.staff_fname, ' ', s1.staff_lname)
        END AS other_staff_name,
        (
            SELECT COUNT(*) 
            FROM messages m
            WHERE m.conversation_id = c.conversation_id
              AND m.sender_id != :current_user
              AND m.is_read = 0
        ) AS unread_count,
        (
            SELECT m2.message_text
            FROM messages m2
            WHERE m2.conversation_id = c.conversation_id
            ORDER BY m2.created_at DESC
            LIMIT 1
        ) AS last_message,
        (
            SELECT m3.created_at
            FROM messages m3
            WHERE m3.conversation_id = c.conversation_id
            ORDER BY m3.created_at DESC
            LIMIT 1
        ) AS last_message_time
    FROM conversations c
    JOIN staff s1 ON c.staff1_id = s1.staff_id
    JOIN staff s2 ON c.staff2_id = s2.staff_id
    WHERE c.staff1_id = :current_user OR c.staff2_id = :current_user
    ORDER BY COALESCE(last_message_time, '1970-01-01') DESC
";

$stmt = $conn->prepare($conversations_query);
$stmt->execute([
    ':current_user' => $_SESSION['userid']
]);
$recent_conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>