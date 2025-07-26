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

function getUserNotifications($staffId, $audience = null) {
    global $conn;

    $staffId = intval($staffId);
    $audienceFilter = '';

    if ($audience !== null) {
        $audience = intval($audience);
        $audienceFilter = "AND (n.audience_scope = $audience OR n.audience_scope IS NULL OR n.audience_scope = '')";
    }

    $sql = "
        SELECT 
            n.id,
            n.message,
            n.url,
            n.created_at,
            n.action_type,
            r.is_read,
            r.read_at
        FROM notification_recipients r
        JOIN notifications n ON r.notification_id = n.id
        WHERE r.recipient_id = $staffId
        $audienceFilter
        ORDER BY n.created_at DESC
        LIMIT 10
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

//call to add notifications given userid and scope (0=admin/1=cashier/2=work_order)
function assignMissingNotif($staffId, $audienceScope) {
    global $conn;
    $staffId = intval($staffId);
    $audienceScope = intval($audienceScope);

    $sql = "
        SELECT n.id
        FROM notifications n
        LEFT JOIN notification_recipients r
            ON n.id = r.notification_id AND r.recipient_id = $staffId
        WHERE n.audience_scope = '$audienceScope'
          AND r.notification_id IS NULL
    ";

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Query failed: " . mysqli_error($conn));
        return false;
    }

    $notifIds = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifIds[] = (int)$row['id'];
    }

    if (empty($notifIds)) {
        return 0;
    }

    foreach ($notifIds as $notifId) {
        $insertSql = "
            INSERT INTO notification_recipients (notification_id, recipient_id, is_read)
            VALUES ($notifId, $staffId, 0)
        ";
        if (mysqli_query($conn, $insertSql)) {
        } else {
            error_log("Insert failed for notif $notifId: " . mysqli_error($conn));
        }
    }
}

?>