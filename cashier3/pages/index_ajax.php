<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_REQUEST['search_customer'])){
    $search = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $query = "
        SELECT 
            CONCAT(customer_first_name, ' ', customer_last_name) AS customer_name, customer_id, contact_email, contact_phone
        FROM 
            customer
        WHERE 
            (customer_first_name LIKE '%$search%' 
            OR 
            customer_last_name LIKE '%$search%'
            OR 
            customer_business_name LIKE '%$search%'
            )
            AND status != '3'
        LIMIT 15
    ";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            ?>
            <div class="message-body" data-simplebar>
                <a href="?page=customer-dashboard&id=<?= $row['customer_id'] ?>" target="_blank" class="p-3 d-flex align-items-center dropdown-item gap-3  border-bottom">
                    <span class="flex-shrink-0 bg-secondary-subtle rounded-circle round-40 d-flex align-items-center justify-content-center fs-6 text-secondary">
                        <iconify-icon icon="ic:round-account-circle"></iconify-icon>
                    </span>
                    <div class="w-80">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-1 text-wrap">
                                <?= get_customer_name($row['customer_id']) ?>
                                <?php
                                $contact_info_to_display = '';

                                if (!empty($row['contact_phone'])) {
                                    $contact_info_to_display = $row['contact_phone'];
                                } elseif (!empty($row['contact_email'])) {
                                    $contact_info_to_display = $row['contact_email'];
                                }

                                if (!empty($contact_info_to_display)):
                                ?>
                                    (<?= $contact_info_to_display ?>)
                                <?php endif; ?>
                            </h6>
                        </div>
                    </div>
                </a>
            </div>
            <?php
        }
    } else {
        ?>
        <div class="message-body" data-simplebar>
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="mb-1 p-3 ">No customer found</h6>
            </div>
        </div>
        <?php
    }
}

if (isset($_GET['fetch_cart'])) {
    header('Content-Type: application/json');
    
    $cartItems = [];

    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $product_details = getProductDetails($item['product_id']);
            $default_image = '../images/product/product.jpg';
            $picture_path = isset($product_details['main_image']) && !empty($product_details['main_image'])
                ? "../" . $product_details['main_image']
                : $default_image;

            $cartItems[] = [
                'img_src' => $picture_path,
                'item_name' => $item['product_item'],
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
    $userId = $_SESSION['userid'];
    $html = '';
    $count = 0;

    assignMissingNotif($userId, '1');
    $notifications = getUserNotifications($userId, '1');

    if (!empty($notifications)) {
        foreach ($notifications as $row) {
            $count++;

            $msg = htmlspecialchars($row['message']);
            $time = date("h:i A", strtotime($row['created_at']));
            $url = htmlspecialchars($row['url'] ?? 'javascript:void(0)');
            $type = $row['action_type'] ?? '';

            $details = getNotifDetails($type);
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
    $userId = $_SESSION['userid'];

    $sql = "UPDATE notification_recipients SET is_read = 1, read_at = NOW() 
            WHERE notification_id = $id AND recipient_id = $userId";

    mysqli_query($conn, $sql);
}

if (isset($_POST['fetch_opening_bal'])) {
    $today = date('Y-m-d');
    $station_id = intval($_SESSION['station'] ?? 0);

    $sql = "SELECT amount 
            FROM cash_flow 
            WHERE movement_type = 'opening_balance' 
            AND DATE(`date`) = '$today'
            AND station_id = '$station_id'
            LIMIT 1";

    $res = mysqli_query($conn, $sql);

    $opening_balance = 0;
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $opening_balance = floatval($row['amount']);
    }

    echo json_encode([
        'status' => 'success',
        'opening_balance' => $opening_balance
    ]);
    exit;
}

if (isset($_POST['save_opening_bal'])) {
    $opening_balance = floatval($_POST['opening_balance']);
    $today = date('Y-m-d H:i:s');

    $received_by = intval($_SESSION['userid'] ?? 0);
    $station_id = intval($_SESSION['station'] ?? 0);

    $check = mysqli_query($conn, "
        SELECT id 
        FROM cash_flow 
        WHERE movement_type = 'opening_balance' 
        AND DATE(`date`) = CURDATE() 
        AND station_id = '$station_id'
        LIMIT 1
    ");

    if ($check && mysqli_num_rows($check) > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Opening balance already set for this station today.'
        ]);
        exit;
    }

    $sql = "
        INSERT INTO cash_flow 
            (movement_type, payment_method, date, received_by, station_id, cash_flow_type, amount)
        VALUES 
            ('opening_balance', '', '$today', '$received_by', '$station_id', 'opening_balance', '$opening_balance')
    ";

    $res = mysqli_query($conn, $sql);

    if ($res) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Opening balance saved successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to save opening balance.'
        ]);
    }

    exit;
}

if (isset($_POST['record_cash_outflow'])) {
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $amount      = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

    if ($description === '' || $amount <= 0) {
        $response['message'] = 'Please select a description and enter a valid amount.';
        echo json_encode($response);
        exit;
    }

    $description_normalized = strtolower($description);
    $description_normalized = preg_replace('/\s+/', '_', $description_normalized);

    $saved = recordCashOutflow('', $description_normalized, $amount);

    if ($saved) {
        $response['success'] = true;
    } else {
        $response['message'] = 'Failed to save cash outflow.';
    }

    echo json_encode($response);
    exit;
}

$conn->close();
?>