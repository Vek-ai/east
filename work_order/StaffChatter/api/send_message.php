<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['work_order_user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

require_once '../config/database.php';

$current_user_id = (int)($_SESSION['work_order_user_id']);
$other_staff_id  = (int)($_POST['other_staff_id'] ?? 0);
$message_text    = trim($_POST['message_text'] ?? '');
$has_file        = isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK;

if (!$other_staff_id || (!$message_text && !$has_file)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

if ($other_staff_id === $current_user_id) {
    echo json_encode(['success' => false, 'error' => 'Cannot send message to yourself']);
    exit();
}

// Handle file upload
$file_name = null;
$file_path = null;
$file_type = null;
$file_size = null;

if ($has_file) {
    $file      = $_FILES['file'];
    $file_name = basename($file['name']);
    $file_size = (int)$file['size'];
    $file_tmp  = $file['tmp_name'];
    
    // Validate file size (max 10MB)
    if ($file_size > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 10MB']);
        exit();
    }
    
    // Whitelist of allowed file extensions and their corresponding MIME types
    $allowed_types = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'gif'  => ['image/gif'],
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xls'  => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'ppt'  => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
        'txt'  => ['text/plain'],
        'zip'  => ['application/zip', 'application/x-zip-compressed'],
        'rar'  => ['application/x-rar-compressed', 'application/octet-stream'],
        'mp4'  => ['video/mp4'],
        'mp3'  => ['audio/mpeg'],
        'wav'  => ['audio/wav', 'audio/x-wav']
    ];
    
    // Get and validate extension
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    if (!isset($allowed_types[$extension])) {
        echo json_encode([
            'success' => false,
            'error'   => 'File type not allowed. Allowed types: ' . implode(', ', array_keys($allowed_types))
        ]);
        exit();
    }
    
    // Detect actual MIME type using server-side detection (not client-supplied)
    $finfo         = finfo_open(FILEINFO_MIME_TYPE);
    $detected_mime = finfo_file($finfo, $file_tmp);
    finfo_close($finfo);
    
    // Validate that detected MIME matches expected MIME for this extension
    if (!in_array($detected_mime, $allowed_types[$extension], true)) {
        echo json_encode([
            'success' => false, 
            'error'   => 'File type mismatch. The file does not match its extension.'
        ]);
        exit();
    }
    
    // Create unique filename with sanitized extension
    $unique_name = uniqid('', true) . '_' . time() . '.' . $extension;
    $upload_dir  = '../uploads/';
    $file_path   = $unique_name;
    
    if (!move_uploaded_file($file_tmp, $upload_dir . $unique_name)) {
        echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
        exit();
    }
    
    // Re-verify the uploaded file (defense in depth)
    $finfo      = finfo_open(FILEINFO_MIME_TYPE);
    $final_mime = finfo_file($finfo, $upload_dir . $unique_name);
    finfo_close($finfo);
    
    if (!in_array($final_mime, $allowed_types[$extension], true)) {
        @unlink($upload_dir . $unique_name);
        echo json_encode([
            'success' => false,
            'error'   => 'File validation failed after upload'
        ]);
        exit();
    }
    
    // Set file type to the detected MIME for database storage
    $file_type = $detected_mime;
}

try {
    $db   = new Database();
    $conn = $db->connect();
    
    $conn->beginTransaction();
    
    // 🔍 Find or create conversation (fixed params, MySQL-safe)
    $stmt = $conn->prepare("
        SELECT conversation_id 
        FROM conversations 
        WHERE (staff1_id = :current_user1 AND staff2_id = :other_user1)
           OR (staff1_id = :other_user2 AND staff2_id = :current_user2)
    ");
    $stmt->bindValue(':current_user1', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':other_user1',  $other_staff_id,  PDO::PARAM_INT);
    $stmt->bindValue(':other_user2',  $other_staff_id,  PDO::PARAM_INT);
    $stmt->bindValue(':current_user2', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$conversation) {
        // Create new conversation (always put smaller ID first for consistency)
        $staff1 = min($current_user_id, $other_staff_id);
        $staff2 = max($current_user_id, $other_staff_id);
        
        $stmt = $conn->prepare("
            INSERT INTO conversations (staff1_id, staff2_id, updated_at) 
            VALUES (:staff1, :staff2, NOW())
        ");
        $stmt->bindValue(':staff1', $staff1, PDO::PARAM_INT);
        $stmt->bindValue(':staff2', $staff2, PDO::PARAM_INT);
        $stmt->execute();
        
        $conversation_id = (int)$conn->lastInsertId();
    } else {
        $conversation_id = (int)$conversation['conversation_id'];
    }
    
    // 💬 Insert message (no RETURNING, use lastInsertId)
    $stmt = $conn->prepare("
        INSERT INTO messages (
            conversation_id, 
            sender_id, 
            message_text, 
            file_name, 
            file_path, 
            file_type, 
            file_size,
            created_at
        ) 
        VALUES (
            :conversation_id, 
            :sender_id, 
            :message_text, 
            :file_name, 
            :file_path, 
            :file_type, 
            :file_size,
            NOW()
        )
    ");
    $stmt->bindValue(':conversation_id', $conversation_id, PDO::PARAM_INT);
    $stmt->bindValue(':sender_id',      $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':message_text',   $message_text !== '' ? $message_text : null, PDO::PARAM_STR);
    $stmt->bindValue(':file_name',      $file_name);
    $stmt->bindValue(':file_path',      $file_path);
    $stmt->bindValue(':file_type',      $file_type);
    $stmt->bindValue(':file_size',      $file_size);
    $stmt->execute();
    
    $message_id = (int)$conn->lastInsertId();

    // Fetch created_at for this message (optional but nice for the frontend)
    $created_at = null;
    if ($message_id) {
        $stmt = $conn->prepare("
            SELECT created_at 
            FROM messages 
            WHERE message_id = :message_id
        ");
        $stmt->bindValue(':message_id', $message_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $created_at = $row['created_at'];
        }
    }
    
    // 🕒 Update conversation timestamp
    $stmt = $conn->prepare("
        UPDATE conversations 
        SET updated_at = NOW() 
        WHERE conversation_id = :conversation_id
    ");
    $stmt->bindValue(':conversation_id', $conversation_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success'         => true,
        'message_id'      => $message_id,
        'created_at'      => $created_at,
        'conversation_id' => $conversation_id
    ]);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
