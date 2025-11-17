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
    
    // Get all staff members except current user
    $stmt = $conn->prepare("
        SELECT 
            staff_id,
            CONCAT(staff_fname, ' ', staff_lname) as name,
            username
        FROM staff 
        WHERE staff_id != :current_user
        ORDER BY staff_fname, staff_lname
    ");
    $stmt->bindParam(':current_user', $_SESSION['userid']);
    $stmt->execute();
    
    $contacts = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'contacts' => $contacts]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
