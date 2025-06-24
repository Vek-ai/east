<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/send_email.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_order_details") {
        $job_name = mysqli_real_escape_string($conn, $_POST['job_name']);
        $po_number = mysqli_real_escape_string($conn, $_POST['po_number']);
        $customer_id = intval($_POST['customer_id']);

        $query = "
            SELECT * 
            FROM order_product op 
            LEFT JOIN orders o ON op.orderid = o.orderid  
            WHERE o.job_name = '$job_name' 
            AND o.job_po = '$po_number'
            AND o.customerid = '$customer_id'
        ";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $order_details = getOrderDetails($orderid);
            $totalquantity = $total_actual_price = $total_disc_price = 0;
            $status_code = $order_details['status'];

            $tracking_number = $order_details['tracking_number'];
            $shipping_comp_details = getShippingCompanyDetails($order_details['shipping_company']);
            $shipping_company = $shipping_comp_details['shipping_company'];

            $response = array();
            ?>
            <style>
                #acct_dtls_tbl {
                    width: 100% !important;
                }

                #acct_dtls_tbl td, #acct_dtls_tbl th {
                    white-space: normal !important;
                    word-wrap: break-word;
                }
            </style>
            <div class="card">
                <div class="card-body datatables">
                    <div class="order-details table-responsive text-nowrap">
                        <div class="col-12 col-md-4 col-lg-4 text-md-start mt-3 fs-5" id="shipping-info">
                            <?php if (!empty($shipping_company)) : ?>
                            <div>
                                <strong>Shipping Company:</strong>
                                <span id="shipping-company"><?= htmlspecialchars($shipping_company) ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($tracking_number)) : ?>
                            <div>
                                <strong>Tracking #:</strong>
                                <span id="tracking-number"><?= htmlspecialchars($tracking_number) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <table id="acct_dtls_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Color</th>
                                    <th>Grade</th>
                                    <th>Profile</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Customer Price</th>
                                    <th class="text-center">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $is_processing = false;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $orderid = $row['orderid'];
                                        $product_details = getProductDetails($row['productid']);
                                        
                                        $status_prod_db = $row['status'];

                                        $product_name = '';
                                        if(!empty($row['product_item'])){
                                            $product_name = $row['product_item'];
                                        }else{
                                            $product_name = getProductName($row['product_id']);
                                        }

                                        if($status_prod_db == '1'){
                                            $is_processing = true;
                                        }

                                        $status_prod_labels = [
                                            0 => ['label' => 'New', 'class' => 'badge bg-primary'],
                                            1 => ['label' => 'Processing', 'class' => 'badge bg-success'],
                                            2 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-warning'],
                                            3 => ['label' => 'In Transit', 'class' => 'badge bg-secondary'],
                                            4 => ['label' => 'Delivered', 'class' => 'badge bg-success']
                                        ];

                                        $status_prod = $status_prod_labels[$status_prod_db];
                                    ?> 
                                        <tr> 
                                            <td>
                                                <?= $product_name ?>
                                            </td>
                                            <td>
                                            <div class="d-flex mb-0 gap-8">
                                                <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_details['color'])?>"></a>
                                                <?= getColorFromID($product_details['color']); ?>
                                            </div>
                                            </td>
                                            <td>
                                                <?php echo getGradeName($product_details['grade']); ?>
                                            </td>
                                            <td>
                                                <?php echo getProfileTypeName($product_details['profile']); ?>
                                            </td>
                                            <td><?= $row['quantity'] ?></td>
                                            <td>
                                                <span class="<?= $status_prod['class']; ?> fw-bond"><?= $status_prod['label']; ?></span>
                                            </td>
                                            <td class="text-end">$ <?= number_format($row['actual_price'],2) ?></td>
                                            <td class="text-end">$ <?= number_format($row['discounted_price'],2) ?></td>
                                            <td class="text-end">$ <?= number_format(floatval($row['discounted_price'] * $row['quantity']),2) ?></td>
                                        </tr>
                                <?php
                                        $totalquantity += $row['quantity'] ;
                                        $total_actual_price += $row['actual_price'];
                                        $total_disc_price += $row['discounted_price'];
                                        $total_amount += floatval($row['discounted_price']) * $row['quantity'];
                                    }
                                
                                ?>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end">Total</td>
                                    <td><?= $totalquantity ?></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-end">$ <?= number_format($total_amount,2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
                
            <script>
                $(document).ready(function() {
                    $('#acct_dtls_tbl').DataTable({
                        language: {
                            emptyTable: "Order Details not found"
                        },
                        autoWidth: false,
                        responsive: true,
                        lengthChange: false
                    });
                });
            </script>

            <?php
        }
    }

    mysqli_close($conn);
}
?>
