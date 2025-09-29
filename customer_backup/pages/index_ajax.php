<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if (isset($_GET['fetch_cart'])) {
    header('Content-Type: application/json');

    $cartItems = [];

    if (isset($_SESSION['customer_id'])) {
        $customer_id = $_SESSION['customer_id'];
        $dbCartItems = getCartDataByCustomerId($customer_id);

        foreach ($dbCartItems as $item) {
            $product_details = getProductDetails($item['product_id']);
            $default_image = '../images/product/product.jpg';

            $picture_path = isset($product_details['main_image']) && !empty($product_details['main_image'])
                ? "../" . $product_details['main_image']
                : $default_image;

            $cartItems[] = [
                'img_src' => $picture_path,
                'item_name' => $product_details['product_item'],
                'color_hex' => getColorHexFromColorID($item['custom_color']),
                'quantity' => $item['quantity_cart'],
                'product_id' => $item['product_id'],
                'line' => $item['line']
            ];
        }
    }

    echo json_encode(['cart_items' => $cartItems]);
}

if (isset($_POST['fetch_notifications'])) {
    $customer_id = $_SESSION['customer_id'];
    $html = '';
    $count = 0;

    $notifications = getCustomerNotifications($customer_id);

    if (!empty($notifications)) {
        foreach ($notifications as $row) {
            $count++;

            $msg = htmlspecialchars($row['message']);
            $time = date("h:i A", strtotime($row['created_at']));
            $url = htmlspecialchars($row['url'] ?? 'javascript:void(0)');
            $type = $row['action_type'] ?? '';

            $details = getCustomerNotifDetails($type);
            $title = $details['title'];
            $icon = $details['icon'];
            $iconColor = $details['iconColor'];
            $iconBg = $details['iconBg'];

            $isRead = (int)$row['is_read'] === 1;
            $textClass = $isRead ? 'text-muted fw-normal' : 'fw-bold';
            $textStyle = $isRead ? '' : 'style="color:#ffffff !important"';

            $html .= "
            <a href='javascript:void(0)'
                class='notification-link p-3 d-flex align-items-center dropdown-item gap-3 border-bottom'
                data-id='{$row['id']}'
                data-url='{$url}'>
                <span class='flex-shrink-0 rounded-circle round-40 d-flex align-items-center justify-content-center fs-6'
                    style='background-color: {$iconBg}; color: {$iconColor};'>
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
    $customer_id = $_SESSION['customer_id'];

    $sql = "UPDATE customer_notification_recipients 
            SET is_read = 1, read_at = NOW() 
            WHERE notification_id = $id AND recipient_id = $customer_id";

    if (!mysqli_query($conn, $sql)) {
        error_log("Failed to update customer notification read status: " . mysqli_error($conn));
    }
}


$conn->close();
?>