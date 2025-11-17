<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

require_once '../config/database.php';

$other_staff_id = (int)($_POST['other_staff_id'] ?? 0);
$message_text = trim($_POST['message_text'] ?? '');
$has_file = isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK;

if (!$other_staff_id || (!$message_text && !$has_file)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

if ($other_staff_id == $_SESSION['userid']) {
    echo json_encode(['success' => false, 'error' => 'Cannot send message to yourself']);
    exit();
}

// Handle file upload
$file_name = null;
$file_path = null;
$file_type = null;
$file_size = null;

if ($has_file) {
    $file = $_FILES['file'];
    $file_name = basename($file['name']);
    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    
    // Validate file size (max 10MB)
    if ($file_size > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 10MB']);
        exit();
    }
    
    // Whitelist of allowed file extensions and their corresponding MIME types
    $allowed_types = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
        'txt' => ['text/plain'],
        'zip' => ['application/zip', 'application/x-zip-compressed'],
        'rar' => ['application/x-rar-compressed', 'application/octet-stream'],
        'mp4' => ['video/mp4'],
        'mp3' => ['audio/mpeg'],
        'wav' => ['audio/wav', 'audio/x-wav']
    ];
    
    // Get and validate extension
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    if (!isset($allowed_types[$extension])) {
        echo json_encode(['success' => false, 'error' => 'File type not allowed. Allowed types: ' . implode(', ', array_keys($allowed_types))]);
        exit();
    }
    
    // Detect actual MIME type using server-side detection (not client-supplied)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detected_mime = finfo_file($finfo, $file_tmp);
    finfo_close($finfo);
    
    // Validate that detected MIME matches expected MIME for this extension
    if (!in_array($detected_mime, $allowed_types[$extension])) {
        echo json_encode([
            'success' => false, 
            'error' => 'File type mismatch. The file does not match its extension.'
        ]);
        exit();
    }
    
    // Create unique filename with sanitized extension
    $unique_name = uniqid() . '_' . time() . '.' . $extension;
    $upload_dir = '../uploads/';
    $file_path = $unique_name;
    
    if (!move_uploaded_file($file_tmp, $upload_dir . $unique_name)) {
        echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
        exit();
    }
    
    // Re-verify the uploaded file (defense in depth)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $final_mime = finfo_file($finfo, $upload_dir . $unique_name);
    finfo_close($finfo);
    
    if (!in_array($final_mime, $allowed_types[$extension])) {
        unlink($upload_dir . $unique_name);
        echo json_encode([
            'success' => false,
            'error' => 'File validation failed after upload'
        ]);
        exit();
    }
    
    // Set file type to the detected MIME for database storage
    $file_type = $detected_mime;
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    $conn->beginTransaction();
    
    // Find or create conversation
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
        // Create new conversation (always put smaller ID first for consistency)
        $staff1 = min($_SESSION['userid'], $other_staff_id);
        $staff2 = max($_SESSION['userid'], $other_staff_id);
        
        $stmt = $conn->prepare("
            INSERT INTO conversations (staff1_id, staff2_id) 
            VALUES (:staff1, :staff2) 
            RETURNING conversation_id
        ");
        $stmt->bindParam(':staff1', $staff1);
        $stmt->bindParam(':staff2', $staff2);
        $stmt->execute();
        $conversation = $stmt->fetch();
    }
    
    $conversation_id = $conversation['conversation_id'];
    
    // Insert message
    $stmt = $conn->prepare("
        INSERT INTO messages (conversation_id, sender_id, message_text, file_name, file_path, file_type, file_size) 
        VALUES (:conversation_id, :sender_id, :message_text, :file_name, :file_path, :file_type, :file_size)
        RETURNING message_id, created_at
    ");
    $stmt->bindParam(':conversation_id', $conversation_id);
    $stmt->bindParam(':sender_id', $_SESSION['userid']);
    $stmt->bindParam(':message_text', $message_text);
    $stmt->bindParam(':file_name', $file_name);
    $stmt->bindParam(':file_path', $file_path);
    $stmt->bindParam(':file_type', $file_type);
    $stmt->bindParam(':file_size', $file_size);
    $stmt->execute();
    
    $message = $stmt->fetch();
    
    // Update conversation timestamp
    $stmt = $conn->prepare("UPDATE conversations SET updated_at = CURRENT_TIMESTAMP WHERE conversation_id = :conversation_id");
    $stmt->bindParam(':conversation_id', $conversation_id);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message_id' => $message['message_id'],
        'created_at' => $message['created_at']
    ]);
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
