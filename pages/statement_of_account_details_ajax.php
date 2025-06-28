<?php
session_start();
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
        $orderid = intval($_POST['orderid']);

        $query = "
            SELECT * FROM order_product WHERE orderid = '$orderid' 
        ";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $first_row = mysqli_fetch_assoc($result);
            $orderid = $first_row['orderid'];
            mysqli_data_seek($result, 0);
            $order_details = getOrderDetails($orderid);

            $totalquantity = $total_actual_price = $total_disc_price = $total_amount = 0;

            $tracking_number = $order_details['tracking_number'] ?? '';
            $shipping_comp_details = getShippingCompanyDetails($order_details['shipping_company']);
            $shipping_company = $shipping_comp_details['shipping_company'] ?? '';

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
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                $status_prod_labels = [
                                    0 => ['label' => 'New', 'class' => 'badge bg-primary'],
                                    1 => ['label' => 'Processing', 'class' => 'badge bg-success'],
                                    2 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-warning'],
                                    3 => ['label' => 'In Transit', 'class' => 'badge bg-secondary'],
                                    4 => ['label' => 'Delivered', 'class' => 'badge bg-success']
                                ];

                                while ($row = mysqli_fetch_assoc($result)) {
                                    $product_details = getProductDetails($row['productid']);
                                    $product_name = !empty($row['product_item']) 
                                        ? $row['product_item'] 
                                        : getProductName($row['productid']);

                                    $status_info = $status_prod_labels[$row['status']] ?? ['label' => 'Unknown', 'class' => 'badge bg-dark'];

                                    $actual_price = floatval(str_replace(',', '', $row['actual_price']));
                                    $discounted_price = floatval(str_replace(',', '', $row['discounted_price']));
                                    $quantity = intval($row['quantity']);

                                    $totalquantity += $quantity;
                                    $total_actual_price += $actual_price;
                                    $total_disc_price += $discounted_price;
                                    $total_amount += $line_total;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($product_name) ?></td>
                                    <td>
                                        <div class="d-flex mb-0 gap-8">
                                            <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_details['color']) ?>"></a>
                                            <?= htmlspecialchars(getColorFromID($product_details['color'])) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars(getGradeName($product_details['grade'])) ?></td>
                                    <td><?= htmlspecialchars(getProfileTypeName($product_details['profile'])) ?></td>
                                    <td class="text-center"><?= $quantity ?></td>
                                    <td class="text-center">
                                        <span class="<?= $status_info['class'] ?> fw-bond"><?= $status_info['label'] ?></span>
                                    </td>
                                    <td class="text-end">$<?= number_format($actual_price, 2) ?></td>
                                    <td class="text-end">$<?= number_format($discounted_price, 2) ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total</td>
                                    <td class="text-center fw-bold"><?= $totalquantity ?></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-end fw-bold">$<?= number_format($total_disc_price, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <script>
                $(document).ready(function () {
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
        } else {
            echo '<p class="text-muted">No order data found for the specified job and PO number.</p>';
        }
    }

    if ($action == "payment_receivable") {
        $ledger_id = intval($_POST['ledger_id'] ?? 0);
        $payment_amount = floatval($_POST['payment_amount'] ?? 0);
        $paid_by = trim($_POST['paid_by'] ?? '');
        $reference_no = trim($_POST['reference_no'] ?? '');
        $payment_method = $_POST['type'] ?? 'cash';
        $check_no = $_POST['check_no'] ?? null;
        $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');

        $cashier = $_SESSION['userid'];

        $check_query = "SELECT * FROM job_ledger WHERE ledger_id = '$ledger_id'";
        $check_result = mysqli_query($conn, $check_query);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $check_no_sql = $payment_method === 'check' ? "'" . mysqli_real_escape_string($conn, $check_no) . "'" : "NULL";

            $insert = "
                INSERT INTO job_payment (ledger_id, amount, payment_method, check_number, reference_no, description, created_by, cashier)
                VALUES (
                    '$ledger_id',
                    '$payment_amount',
                    '$payment_method',
                    $check_no_sql,
                    '" . mysqli_real_escape_string($conn, $reference_no) . "',
                    '$description',
                    '" . mysqli_real_escape_string($conn, $paid_by) . "',
                    '$cashier'
                )
            ";

            if (mysqli_query($conn, $insert)) {
                echo 'success';
            } else {
                echo 'error_insert';
            }
        } else {
            echo 'job_not_found';
        }
    }

    if ($action == "payment_history") {
        $ledger_id = intval($_POST['ledger_id'] ?? 0);

        ?>
        <div class="datatables">
            <div class="table-responsive">
                <table id="payment_history_tbl" class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Cashier</th>
                        <th>Reference No</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                            $total_paid = 0;
                            $query = "SELECT * FROM job_payment WHERE ledger_id = '$ledger_id' ORDER BY created_at DESC";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                $cashier = get_staff_name($row['cashier']);
                                $payment_method = ucfirst($row['payment_method']);
                                $amount = number_format($row['amount'], 2);
                                $reference_no = $row['reference_no'];
                                $description = $row['description'];
                                $date = date('F d,Y', strtotime($row['created_at']));

                                $total_paid += $row['amount'];
                                echo "<tr>
                                        <td>$date</td>
                                        <td>$payment_method</td>
                                        <td>$cashier</td>
                                        <td>$reference_no</td>
                                        <td>$description</td>
                                        <td class='text-end'>$$amount</td>
                                    </tr>";
                            }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Total</th>
                            <th class="text-end">$<?= number_format($total_paid, 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>
        </div>

        <?php
    }


    mysqli_close($conn);
}
?>
