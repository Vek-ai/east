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
                op.*,  
                p.*, 
                COALESCE(SUM(i.quantity_ttl), 0) AS inv_quantity,
                i.Warehouse_id as warehouse
            FROM order_product AS op 
            LEFT JOIN product AS p ON p.product_id = op.productid 
            LEFT JOIN inventory AS i ON i.product_id = op.productid 
            WHERE p.hidden = 0 AND op.status = 0 AND p.product_origin = 1
            GROUP BY p.product_id
        ";
        $result = mysqli_query($conn, $query);
        $no = 1;
    
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['id'];
            $product_id = $row['product_id'];
            $status = $row['status'];
            $instock = $row['inv_quantity'] > 1 ? 1 : 0;
            $category_id = $row['product_category'];
            $warehouse = $row['warehouse'];

            $order_qty = $row['quantity'];

    
            $status_html = $instock == 1
                ? "<span class='badge bg-success text-white'>In Stock</span>"
                : "<span class='badge bg-danger text-white'>Out of Stock</span>";
            $picture_path = !empty($row['main_image']) ? $row['main_image'] : "images/product/product.jpg";
    
            $product_name_html = "
                <a href='?page=product_details&product_id={$product_id}'>
                    <div class='d-flex align-items-center'>
                        <img src='{$picture_path}' class='rounded-circle' width='56' height='56'>
                        <div class='ms-3'>
                            <h6 class='fw-semibold mb-0 fs-4'>{$row['product_item']}</h6>
                        </div>
                    </div>
                </a>";

            $action_html = '';
    
            if ($instock == 1) {
                $action_html .= "
                    <a href='javascript:void(0)' id='release_product' data-id='{$id}' title='Release' class='text-success'>
                        <iconify-icon icon='solar:box-minimalistic-bold' class='fs-7'></iconify-icon>
                    </a>";
            } else {
                $action_html .= "
                    <a href='javascript:void(0)' id='order_product' data-id='{$id}' title='Order' class='text-danger'>
                        <iconify-icon icon='solar:cart-large-bold' class='fs-7'></iconify-icon>
                    </a>";
            }
    
            $data[] = [
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
                'active'              => $status,
                'order_qty'           => $order_qty,
                'instock'             => $instock,
                'status'              => $status,
                'status_html'         => $status_html,
                'action_html'         => $action_html
            ];
    
            $no++;
        }
    
        echo json_encode(['data' => $data]);
    }

    if ($action == 'release_product') {
        $product_id = intval($_POST['product_id']);
        $status = intval($_POST['status']);

        $sql = "UPDATE order_product SET status = $status WHERE id = $product_id";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }

        mysqli_close($conn);
        exit;
    }
    
    mysqli_close($conn);
}
?>
