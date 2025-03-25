<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

$supplier_id = '';

if(!empty($_REQUEST['id'])){
    $supplier_id = $_REQUEST['id'];
    $supplier_details = getSupplierDetails($supplier_id);
}

?>
<style>
    .dz-preview {
        position: relative;
    }

    .dz-remove {
        position: absolute;
        top: 10px;
        right: 10px;
        background: red;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        font-weight: bold;
        font-size: 12px;
        z-index: 9999;
        cursor: pointer;
    }

    #productList_filter {
        display: none !important;
    }

    #productTable th:nth-child(1), 
    #productTable td:nth-child(1) {
        width: 25% !important; /* Products */
    }

    .readonly {
        pointer-events: none;
        background-color: #f8f9fa;
        color: #6c757d;
        border: 0;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .readonly select,
    .readonly option {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
    }

    .readonly input {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
    }

    .cart-icon {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    .cart-badge {
        position: absolute;
        top: -16px;
        right: -16px; /* Slightly outside the icon */
        background-color: red;
        color: white;
        font-size: 14px;
        font-weight: bold;
        min-width: 20px;
        min-height: 20px;
        line-height: 20px;
        text-align: center;
        border-radius: 50%;
        padding: 2px 6px;
        white-space: nowrap;
        display: none;
    }

    /* Adjust width dynamically based on number size */
    .cart-badge[data-count="10"],
    .cart-badge[data-count="99"],
    .cart-badge[data-count="100+"] {
        min-width: auto;
        padding: 2px 8px;
    }
    
    /* Show badge only when count is greater than 0 */
    .cart-badge:not(:empty):not(:contains("0")) {
        display: inline-block;
    }


</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"><?= getSupplierName($supplier_id) ?> Orders</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page"><?= getSupplierName($supplier_id) ?> Orders</li>
            </ol>
            </nav>
        </div>
        <div>
            <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
            
            </div>
        </div>
        </div>
    </div>
    </div>

    <div class="widget-content searchable-container list">

    <div class="modal fade" id="response-modal" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div id="responseHeaderContainer" class="modal-header align-items-center modal-colored-header">
                <h4 id="responseHeader" class="m-0"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <p id="responseMsg"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
                Close
                </button>
            </div>
            </div>
        </div>
    </div>

    <?php
    $sql_orders="SELECT supplier_id, supplier_temp_order_id, 
                        SUM(price * quantity) AS total_price,
                        SUM(quantity) AS ttl_qty
                FROM supplier_temp_prod_orders
                WHERE supplier_id = '$supplier_id'
                GROUP BY supplier_temp_order_id";

    $result_orders = $conn->query($sql_orders);

    if ($result_orders->num_rows > 0) {
        while ($row_order = $result_orders->fetch_assoc()) {
            $supplier_temp_order_id = $row_order['supplier_temp_order_id'];
            $order_dtls = getSupplierTempOrderDetails($supplier_temp_order_id);
    ?>
            <div class="card card-body">
                <div class="row">
                    <div class="col-12">
                    <h3 class="card-title mb-2 d-flex justify-content-between flex-wrap">
                        <span>Staff: <strong><?= get_staff_name($order_dtls['cashier']) ?></strong></span>
                        <span>Total Amount: <strong>$<?= number_format($row_order['total_price'], 2) ?></strong></span>
                        <span>Total Qty: <strong><?= $row_order['ttl_qty'] ?></strong></span>
                    </h3>
                        <?php
                        $sql_products = "SELECT * FROM supplier_temp_prod_orders WHERE supplier_temp_order_id = '$supplier_temp_order_id' AND supplier_id = '$supplier_id'";
                        $result_products = $conn->query($sql_products);
                        ?>

                        <div class="datatables">
                            <div class="table-responsive">
                                <table id="productList" class="table table-sm search-table align-middle text-wrap">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Color</th>
                                            <th>Quantity</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row_product = $result_products->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?= getProductName($row_product['product_id']); ?></td>
                                                <td>
                                                    <span class="d-flex align-items-center small">
                                                        <span class="rounded-circle d-block p-1 me-2" 
                                                            style="background-color: <?= getColorHexFromColorID($row_product['color']); ?>; 
                                                                    width: 25px; height: 25px;">
                                                        </span>
                                                        <?= !empty($row_product['color']) ? getColorName($row_product['color']) : ''; ?>
                                                    </span>
                                                </td>
                                                <td><?= $row_product['quantity']; ?></td>
                                                <td class="text-right">$<?= number_format($row_product['price'], 2); ?></td>
                                                <td class="text-right">$<?= number_format($row_product['price'] * $row_product['quantity'], 2); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="#" class="btn btn-sm" style="background-color: #28a745; color: #fff; border: none;">
                                <i class="fas fa-shopping-cart me-1"></i> Order
                            </a>
                            <a href="#" class="btn btn-sm" style="background-color: #ffc107; color: #fff; border: none;">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
    <?php
        }
    } else {
        echo "<p>No orders found for this supplier.</p>";
    }
    ?>


    </div>
</div>




