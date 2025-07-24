<?php
function getPDO() {
    global $host, $user, $password, $dbname;
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        die("Database connection error.");
    }
}

function createNotification($actorId, $actionType, $targetId, $targetType, $message, $recipientIds = [], $url = null) {
    $pdo = getPDO();

    try {
        $pdo->beginTransaction();

        $audienceScope = null;

        // Check if recipientIds is a string representing a group
        if (is_string($recipientIds)) {
            switch (strtolower($recipientIds)) {
                case 'admin':
                    $audienceScope = 0;
                    break;
                case 'cashier':
                    $audienceScope = 1;
                    break;
                case 'work_order':
                    $audienceScope = 2;
                    break;
                default:
                    $audienceScope = null;
            }
            // No need to insert into notification_recipients — role-based
            $recipientIds = [];
        }

        // Insert notification with audience_scope
        $stmt = $pdo->prepare("
            INSERT INTO notifications (actor_id, action_type, target_id, target_type, message, url, audience_scope, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$actorId, $actionType, $targetId, $targetType, $message, $url, $audienceScope]);

        $notificationId = $pdo->lastInsertId();

        // Add recipients if provided (individual staff IDs)
        if (!empty($recipientIds)) {
            $stmt = $pdo->prepare("
                INSERT INTO notification_recipients (notification_id, recipient_id, is_read)
                VALUES (?, ?, 0)
            ");

            foreach ($recipientIds as $recipientId) {
                $stmt->execute([$notificationId, $recipientId]);
            }
        }

        $pdo->commit();
        return $notificationId;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Failed to create notification: " . $e->getMessage());
        return false;
    }
}

function getUserNotifications($staffId) {
    global $conn;

    $staffId = intval($staffId);

    $sql = "
        SELECT 
            n.id,
            n.message,
            n.url,
            n.created_at,
            r.is_read,
            r.read_at
        FROM notification_recipients r
        JOIN notifications n ON r.notification_id = n.id
        WHERE r.recipient_id = $staffId
        ORDER BY n.created_at DESC
    ";

    $result = mysqli_query($conn, $sql);
    $notifications = [];

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
    }

    return $notifications;
}

function getRoleNotifications($role) {
    global $conn;

    $roleMap = [
        'admin'      => 0,
        'cashier'    => 1,
        'work_order' => 2
    ];

    if (!isset($roleMap[$role])) return [];

    $scope = $roleMap[$role];

    $sql = "
        SELECT 
            id,
            action_type,
            message,
            url,
            created_at
        FROM notifications
        WHERE audience_scope = $scope
        ORDER BY created_at DESC
    ";

    $result = mysqli_query($conn, $sql);
    $notifications = [];

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
    }

    return $notifications;
}

?>