<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'test';
//4 = TRIM
$trim_id = 4;
$category_id = 4;

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    
    if ($action == 'fetch_products') {
        $data = [];
        $query = "
            SELECT 
                unified.source_id AS id,
                unified.source_type,
                unified.orderid,
                unified.productid,
                unified.quantity,
                p.*,
                COALESCE(SUM(i.quantity_ttl), 0) AS inv_quantity,
                i.Warehouse_id AS warehouse
            FROM (
                SELECT 
                    op.id AS source_id,
                    'order' AS source_type,
                    op.orderid,
                    op.productid,
                    op.quantity
                FROM order_product AS op
                WHERE op.status = 2

                UNION ALL

                SELECT 
                    pr.id AS source_id,
                    'return' AS source_type,
                    pr.orderid,
                    pr.productid,
                    pr.quantity
                FROM product_returns AS pr
                WHERE pr.status = 0
            ) AS unified
            LEFT JOIN product AS p ON p.product_id = unified.productid AND p.product_origin = 1
            LEFT JOIN inventory AS i ON i.product_id = unified.productid
            GROUP BY unified.source_id
        ";
        $result = mysqli_query($conn, $query);
        $no = 1;

        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['id'];
            $orderid = $row['orderid'];
            $product_id = $row['product_id'];
            $instock = $row['inv_quantity'] > 0 ? 1 : 0;
            $category_id = $row['product_category'];
            $warehouse = $row['warehouse'];
            $order_qty = $row['quantity'];
            $source_type = $row['source_type'];

            $product_name = getProductName($product_id);

            $status_html = $instock
                ? "<span class='badge bg-success text-white'>In Stock</span>"
                : "<span class='badge bg-danger text-white'>Out of Stock</span>";

            $source_html = $source_type === 'order'
                ? "<span class='badge bg-primary text-white'>New Order</span>"
                : "<span class='badge bg-danger text-white'>Return</span>";

            $picture_path = !empty($row['main_image']) ? $row['main_image'] : "images/product/product.jpg";

            $product_name_html = "
                <a href='?page=product_details&product_id={$product_id}'>
                    <div class='d-flex align-items-center'>
                        <img src='{$picture_path}' class='rounded-circle' width='56' height='56'>
                        <div class='ms-3'>
                            <h6 class='fw-semibold mb-0 fs-4'>{$product_name}</h6>
                        </div>
                    </div>
                </a>";

            $action_html = '';

            if ($source_type === 'return') {
                $action_html .= "
                    <a href='javascript:void(0)' id='transfer_warehouse' data-id='{$id}' title='Return to Warehouse' class='text-success'>
                        <iconify-icon icon='material-symbols:warehouse' class='fs-7'></iconify-icon>
                    </a>";
            }

            $data[] = [
                'orderid'   => $orderid,
                'product_name_html'   => $product_name_html,
                'product_category'    => getProductCategoryName($row['product_category']),
                'product_system'      => getProductSystemName($row['product_system']),
                'product_line'        => getProductLineName($row['product_line']),
                'product_type'        => getProductTypeName($row['product_type']),
                'profile'             => getProfileTypeName($row['profile']),
                'color'               => getColorName($row['color']),
                'grade'               => getGradeName($row['grade']),
                'gauge'               => getGaugeName($row['gauge']),
                'warehouse'           => getWarehouseName($warehouse),
                'type'              => $source_type,
                'order_qty'           => $order_qty,
                'instock'             => $instock,
                'status_html'         => $status_html,
                'source_html'         => $source_html,
                'action_html'         => $action_html
            ];

            $no++;
        }

        echo json_encode(['data' => $data]);
    }

    if ($action == 'transfer_warehouse') {
        $id = intval($_POST['id']);

        $fetch = mysqli_query($conn, "SELECT * FROM product_returns WHERE id = $id AND status = 0");
        if (mysqli_num_rows($fetch) > 0) {
            $row = mysqli_fetch_assoc($fetch);

            $product_id = intval($row['productid']);
            $color_id = isset($row['custom_color']) ? intval($row['custom_color']) : 0;
            $quantity = floatval($row['quantity']);

            $color_condition = ($color_id > 0) 
                ? "AND color_id = $color_id" 
                : "";

            $inv_query = "
                SELECT * FROM inventory 
                WHERE product_id = $product_id 
                $color_condition
                LIMIT 1
            ";
            $check_inv = mysqli_query($conn, $inv_query);

            if (mysqli_num_rows($check_inv) > 0) {
                $update_status = mysqli_query($conn, "UPDATE product_returns SET status = 1 WHERE id = $id");

                $update_inventory = mysqli_query($conn, "
                    UPDATE inventory 
                    SET quantity_ttl = quantity_ttl + $quantity 
                    WHERE product_id = $product_id 
                    $color_condition
                ");

                if ($update_status && $update_inventory) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update inventory or return status.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No matching inventory record found.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Return already processed or invalid.']);
        }
    }
    
    mysqli_close($conn);
}
?>
