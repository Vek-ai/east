<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['search_customer'])){
    $search = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $query = "
        SELECT 
            CONCAT(customer_first_name, ' ', customer_last_name) AS customer_name, customer_id
        FROM 
            customer
        WHERE 
            (customer_first_name LIKE '%$search%' 
            OR 
            customer_last_name LIKE '%$search%')
            AND status != '3'
        LIMIT 15
    ";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            ?>
            <div class="message-body" data-simplebar>
                <a href="?page=customer-dashboard&id=<?= $row['customer_id'] ?>" class="p-3 d-flex align-items-center dropdown-item gap-3  border-bottom">
                    <span class="flex-shrink-0 bg-secondary-subtle rounded-circle round-40 d-flex align-items-center justify-content-center fs-6 text-secondary">
                        <iconify-icon icon="ic:round-account-circle"></iconify-icon>
                    </span>
                    <div class="w-80">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-1"><?= $row['customer_name'] ?></h6>
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

if(isset($_POST['fetch_order_product_details'])){
    $supplier_temp_order_id = mysqli_real_escape_string($conn, $_POST['orderid']);
    ?>
    <style>
        .tooltip-inner {
            background-color: white !important;
            color: black !important;
            font-size: calc(0.875rem + 2px) !important;
        }
    </style>
    <div class="card-body datatables">
        <div class="product-details table-responsive text-nowrap">
            <table id="order_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Color</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 0;
                    $query = "SELECT * FROM supplier_temp_prod_orders WHERE supplier_temp_order_id='$supplier_temp_order_id'";
                    $result = mysqli_query($conn, $query);
                    $totalquantity = $total_price = 0;
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $product_id = $row['product_id'];
                            $price = number_format(floatval($row['price']) * floatval($row['quantity']),2);
                            ?>
                            <tr>
                                <td class="text-wrap"> 
                                    <?php echo getProductName($product_id) ?>
                                </td>
                                <td>
                                <div class="d-flex mb-0 gap-8">
                                    <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromColorID($row['color'])?>"></a>
                                    <?= getColorName($row['color']); ?>
                                </div>
                                </td>
                                <td class="text-center"><?= floatval($row['quantity']) ?></td>
                                <td class="text-end">$ <?= $price ?></td>
                            </tr>

                            <?php
                            $totalquantity += $row['quantity'] ;
                            $total_price += $price;
                            
                        }
                    }
                    ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end">Total</td>
                        <td class="text-center"><?= $totalquantity ?></td>
                        <td class="text-end">$ <?= number_format($total_price,2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>   
    <div class="modal-footer">
        <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">
            <i class="fas fa-times me-2"></i> Close
        </button>
    </div>
    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip(); 
        });
    </script>
    <?php
}

if (isset($_POST['approve_customer'])) {
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $query = "SELECT * FROM customer WHERE customer_id='$customer_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $query = "UPDATE customer SET is_approved = 1, status = 1 WHERE customer_id='$customer_id'";
        $result = mysqli_query($conn, $query);

        if ($result) {
            echo "success";
        } else {
            echo "MySQL error: " . mysqli_error($conn);
        }
    }
}

if (isset($_POST['reject_customer'])) {
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $query = "SELECT * FROM customer WHERE customer_id='$customer_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $query = "UPDATE customer SET is_approved = 0, status = 0, hidden = 1 WHERE customer_id='$customer_id'";
        $result = mysqli_query($conn, $query);

        if ($result) {
            echo "success";
        } else {
            echo "MySQL error: " . mysqli_error($conn);
        }
    }
}
?>