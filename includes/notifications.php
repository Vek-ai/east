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
        $audienceFilter = "AND (n.audience_scope = $audience)";
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

function getNotifDetails($type) {
    $actions = [
        'coil_defective' => [
            'title' => 'Coil Tagged as Defective',
            'icon' => 'solar:danger-triangle-line-duotone',
            'iconColor' => '#dc3545',
            'iconBg' => '#f8d7da',
        ],
        'new_work_order' => [
            'title' => 'New Work Order',
            'icon' => 'solar:document-add-line-duotone',
            'iconColor' => '#198754',
            'iconBg' => '#d1e7dd',
        ],
        'review_coil' => [
            'title' => 'Coil Under Review',
            'icon' => 'solar:archive-check-line-duotone',
            'iconColor' => '#ffc107',
            'iconBg' => '#fff3cd',
        ],
        'approve_coil' => [
            'title' => 'Coil Approved for Use',
            'icon' => 'solar:check-circle-line-duotone',
            'iconColor' => '#0dcaf0',
            'iconBg' => '#cff4fc',
        ],
        'work_order_done' => [
            'title' => 'Work Order Completed',
            'icon' => 'solar:clipboard-check-line-duotone',
            'iconColor' => '#6f42c1',
            'iconBg' => '#e2d9f3',
        ],

        'approval_granted' => [
            'title' => 'Approval Granted',
            'icon' => 'solar:shield-check-line-duotone',
            'iconColor' => '#28a745',
            'iconBg' => '#d4edda',
        ],
        'request_approval' => [
            'title' => 'Approval Requested',
            'icon' => 'solar:hand-money-line-duotone',
            'iconColor' => '#fd7e14',
            'iconBg' => '#ffe5d0',
        ],
        'pre_order' => [
            'title' => 'Pre-Order Created',
            'icon' => 'solar:cart-large-line-duotone',
            'iconColor' => '#0d6efd',
            'iconBg' => '#d0e2ff',
        ],
        'no_stock_order' => [
            'title' => 'Out-of-Stock Order',
            'icon' => 'solar:box-minimalistic-line-duotone',
            'iconColor' => '#dc3545',
            'iconBg' => '#f8d7da',
        ],
        'estimate_request' => [
            'title' => 'Estimate Request Submitted',
            'icon' => 'solar:document-line-duotone',
            'iconColor' => '#6610f2',
            'iconBg' => '#e6e0f8',
        ],
        'deposit_approval' => [
            'title' => 'Job Deposit Approval',
            'icon' => 'solar:wallet-line-duotone',
            'iconColor' => '#20c997',
            'iconBg' => '#d2f4ea',
        ],
    ];

    return $actions[$type] ?? [
        'title' => 'New Activity',
        'icon' => 'solar:bell-line-duotone',
        'iconColor' => '#0d6efd',
        'iconBg' => '#cfe2ff',
    ];
}

function getCustomerNotifDetails($type) {
    $actions = [
        'return_approved' => [
            'title' => 'Return Approved',
            'icon' => 'solar:check-circle-line-duotone',
            'iconColor' => '#198754',
            'iconBg' => '#d1e7dd',
        ],
        'return_rejected' => [
            'title' => 'Return Rejected',
            'icon' => 'solar:close-circle-line-duotone',
            'iconColor' => '#dc3545',
            'iconBg' => '#f8d7da',
        ],
    ];

    return $actions[$type] ?? [
        'title' => 'New Notification',
        'icon' => 'solar:bell-line-duotone',
        'iconColor' => '#0d6efd',
        'iconBg' => '#d0e2ff',
    ];
}

function createCustomerNotification($actorId, $actionType, $targetId, $targetType, $message, $recipientIds = [], $url = null) {
    $pdo = getPDO();

    try {
        $pdo->beginTransaction();

        $audienceScope = null;

        if (is_string($recipientIds)) {
            switch (strtolower($recipientIds)) {
                case 'all':
                    $audienceScope = 0;
                    break;
                default:
                    $audienceScope = null;
            }
            $recipientIds = [];
        }

        if (is_numeric($recipientIds)) {
            $recipientIds = [$recipientIds];
        }

        $stmt = $pdo->prepare("
            INSERT INTO customer_notifications (
                actor_id, action_type, target_id, target_type, message, url, audience_scope, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$actorId, $actionType, $targetId, $targetType, $message, $url, $audienceScope]);

        $notificationId = $pdo->lastInsertId();

        if (!empty($recipientIds)) {
            $stmt = $pdo->prepare("
                INSERT INTO customer_notification_recipients (notification_id, recipient_id, is_read)
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
        error_log("Customer notification failed: " . $e->getMessage());
        return false;
    }
}

function getCustomerNotifications($customerId, $audience = null) {
    global $conn;

    $customerId = intval($customerId);
    $audienceFilter = '';

    if ($audience !== null) {
        $audience = intval($audience);
        $audienceFilter = "AND (n.audience_scope = $audience)";
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
        FROM customer_notification_recipients r
        JOIN customer_notifications n ON r.notification_id = n.id
        WHERE r.recipient_id = $customerId
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

?>