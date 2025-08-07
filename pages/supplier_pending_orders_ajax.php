<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_view_modal") {
        $supplier_id = intval($_REQUEST['supplier_id']);
        $supplier_name = getSupplierName($supplier_id);

        $query_orders = "
            SELECT 
                sop.product_id, 
                sop.quantity, 
                sop.price, 
                sop.color, 
                so.order_date,
                sop.id AS prod_order_id
            FROM supplier_orders_prod sop
            JOIN supplier_orders so ON so.supplier_order_id = sop.supplier_order_id
            WHERE so.supplier_id = '$supplier_id'
            ORDER BY so.order_date DESC
        ";

        $result_orders = mysqli_query($conn, $query_orders);
        ?>
        <div class="row pt-3">
            <div class="col-md-12">
                <h5 class="mb-3">Ordered Products for <?= $supplier_name ?></h5>

                <?php if (mysqli_num_rows($result_orders) > 0) { ?>
                    <div class="datatables">
                        <div class="table-responsive">
                            <table id="supplier_order_products" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Color</th>
                                        <th>Order Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($prod = mysqli_fetch_assoc($result_orders)) {
                                        $product_id = $prod['product_id'];
                                        $product_details = getProductDetails($product_id);
                                        $product_name = $product_details['product_item'];
                                        $picture_path = !empty($product_details['main_image']) ? $product_details['main_image'] : "images/product/product.jpg";
                                        ?>
                                        <tr>
                                            <td>
                                                <div class='d-flex align-items-center'>
                                                    <img src='<?= $picture_path ?>' class='rounded-circle' width='56' height='56'>
                                                    <div class='ms-3'>
                                                        <h6 class='fw-semibold mb-0 fs-4'><?= $product_name ?></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= $prod['quantity'] ?></td>
                                            <td>$<?= number_format($prod['price'], 2) ?></td>
                                            <td><?= getColorName($prod['color']) ?></td>
                                            <td><?= date("M d, Y", strtotime($prod['order_date'])) ?></td>
                                            <td class="text-center">
                                                <a href="javascript:void(0)" class="delete-product-btn" data-id="<?= $prod['prod_order_id'] ?>" title="Remove Product from Order">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-3">
                        <a href="?page=supplier_edit_orders&supplier_id=<?= $supplier_id ?>" target="_blank" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Order
                        </a>
                        
                        <div>
                            <button class="btn btn-success me-2" id="approve_supplier_order" data-supplier-id="<?= $supplier_id ?>">
                                <i class="fas fa-check"></i> Place Order
                            </button>
                            <button class="btn btn-danger" id="remove_supplier_order" data-supplier-id="<?= $supplier_id ?>">
                                <i class="fas fa-trash-alt"></i> Remove Order
                            </button>
                        </div>
                    </div>

                <?php } else { ?>
                    <p class="text-muted">No orders found for this supplier.</p>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    if ($action == 'fetch_suppliers_w_order') {
        $data = [];

        $query = "
            SELECT 
                supplier_id,
                MAX(cashier) AS latest_cashier,
                SUM(total_price) AS total_amount,
                MAX(order_date) AS latest_order_date
            FROM supplier_orders
            WHERE status = 1
            GROUP BY supplier_id
            ORDER BY latest_order_date DESC
        ";
        
        $result = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $supplier_id = $row['supplier_id'];
            $supplier_name = getSupplierName($supplier_id);

            $cashier_id = $row['latest_cashier'];
            $cashier_name = get_staff_name($cashier_id);

            $total_price = number_format($row['total_amount'], 2);
            $order_date_raw = strtotime($row['latest_order_date']);
            $order_date = date("M d, Y", $order_date_raw);
            $month = date("m", $order_date_raw);
            $year = date("Y", $order_date_raw);

            $action_html = "
                <div class='action-btn text-center'>
                    <a href='javascript:void(0)' title='View' class='text-primary view_order_btn'
                        data-supplier='{$supplier_id}'>
                        <i class='ti ti-eye fs-7'></i>
                    </a>
                </div>";

            $data[] = [
                'supplier_name' => $supplier_name,
                'supplier_id'   => $supplier_id,
                'cashier_name'  => $cashier_name,
                'cashier_id'    => $cashier_id,
                'total_price'   => "$" . $total_price,
                'order_date'    => $order_date,
                'month'         => $month,
                'year'          => $year,
                'action_html'   => $action_html
            ];
        }

        echo json_encode(['data' => $data]);
    }

    if ($action == "approve_supplier_order") {
        $supplier_id = intval($_POST['supplier_id']);
        $update = "
            UPDATE supplier_orders 
            SET status = 2 
            WHERE supplier_id = '$supplier_id' AND status = 1
        ";

        if (mysqli_query($conn, $update)) {
            echo json_encode(['success' => true, 'message' => 'Order approved.']);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }

        exit;
    }

    if ($action == "reject_supplier_order") {
        $supplier_id = intval($_POST['supplier_id']);
        $update = "
            UPDATE supplier_orders 
            SET status = 3 
            WHERE supplier_id = '$supplier_id' AND status = 1
        ";

        if (mysqli_query($conn, $update)) {
            echo json_encode(['success' => true, 'message' => 'Order rejected.']);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }

        exit;
    }

    if ($action === "delete_product") {
        $prod_order_id = intval($_POST['prod_order_id']);

        $query = "SELECT supplier_order_id FROM supplier_orders_prod WHERE id = '$prod_order_id' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $supplier_order_id = intval($row['supplier_order_id']);

            $delete = "DELETE FROM supplier_orders_prod WHERE id = '$prod_order_id' LIMIT 1";
            if (mysqli_query($conn, $delete)) {
                $total_query = "
                    SELECT SUM(price * quantity) AS total 
                    FROM supplier_orders_prod 
                    WHERE supplier_order_id = '$supplier_order_id'
                ";
                $total_result = mysqli_query($conn, $total_query);
                $total_row = mysqli_fetch_assoc($total_result);
                $new_total = floatval($total_row['total'] ?? 0);

                $update_total = "
                    UPDATE supplier_orders 
                    SET total_price = '$new_total', is_edited = 1 
                    WHERE supplier_order_id = '$supplier_order_id'
                ";
                mysqli_query($conn, $update_total);

                echo "success";
            } else {
                echo "failed";
            }
        } else {
            echo "failed";
        }

        exit;
    }


    mysqli_close($conn);
}
?>
