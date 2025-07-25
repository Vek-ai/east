<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if (isset($_POST['fetch_notifications'])) {
    $userId = $_SESSION['userid'];
    $html = '';
    $count = 0;

    assignMissingNotif($userId, '2');
    $notifications = getUserNotifications($userId);

    if (!empty($notifications)) {
        foreach ($notifications as $row) {
            $count++;

            $msg = htmlspecialchars($row['message']);
            $time = date("h:i A", strtotime($row['created_at']));
            $url = htmlspecialchars($row['url'] ?? 'javascript:void(0)');
            $type = $row['action_type'] ?? '';

            $title = "New Activity";
            $icon = "solar:bell-line-duotone";
            $iconColor = "text-primary";
            $iconBg = "bg-primary-subtle";

            switch ($type) {
                case 'coil_defective':
                    $title = "Coil Tagged as Defective";
                    $icon = "solar:danger-triangle-line-duotone";
                    $iconColor = "text-danger";
                    $iconBg = "bg-danger-subtle";
                    break;

                case 'new_work_order':
                    $title = "New Work Order";
                    $icon = "solar:document-add-line-duotone";
                    $iconColor = "text-success";
                    $iconBg = "bg-success-subtle";
                    break;

                case 'review_coil':
                    $title = "Coil Under Review";
                    $icon = "solar:archive-check-line-duotone";
                    $iconColor = "text-warning";
                    $iconBg = "bg-warning-subtle";
                    break;

                case 'approve_coil':
                    $title = "Coil Approved for Use";
                    $icon = "solar:check-circle-line-duotone";
                    $iconColor = "text-info";
                    $iconBg = "bg-info-subtle";
                    break;
            }

            $isRead = (int)$row['is_read'] === 1;
            $textClass = $isRead ? 'text-muted fw-normal' : 'fw-bold';
            $textStyle = $isRead ? '' : 'style="color:#ffffff !important"';

            $html .= "
            <a href='javascript:void(0)'
                class='notification-link p-3 d-flex align-items-center dropdown-item gap-3 border-bottom'
                data-id='{$row['id']}'
                data-url='{$url}'>
                <span class='flex-shrink-0 {$iconBg} rounded-circle round-40 d-flex align-items-center justify-content-center fs-6 {$iconColor}'>
                    <iconify-icon icon='{$icon}'></iconify-icon>
                </span>
                <div class='w-80'>
                    <div class='d-flex align-items-center justify-content-between'>
                        <h6 class='mb-1 {$textClass}' {$textStyle}>{$title}</h6>
                        <span class='fs-2 d-block text-muted'>{$time}</span>
                    </div>
                    <span class='fs-2 d-block text-truncate {$textClass}' {$textStyle}>{$msg}</span>
                </div>
            </a>";
        }
    } else {
        $html = "<div class='p-3 text-center text-muted'>No notifications found.</div>";
    }

    echo json_encode([
        'count' => $count,
        'html' => $html
    ]);
}

if (isset($_POST['notification_id'])) {
    $id = intval($_POST['notification_id']);
    $userId = $_SESSION['userid'];

    $sql = "UPDATE notification_recipients SET is_read = 1, read_at = NOW() 
            WHERE notification_id = $id AND recipient_id = $userId";

    mysqli_query($conn, $sql);
}

?>