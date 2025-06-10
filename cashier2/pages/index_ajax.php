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

$conn->close();
?>