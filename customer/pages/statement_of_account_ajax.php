<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

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

    if ($action == 'upload_payment_screenshot') {
        $payment_id = intval($_POST['payment_id'] ?? 0);
        $ledger_id = intval($_POST['ledger_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $upload_dir = '../../uploads/payment_proofs/';
        $created_by = $_SESSION['customer_id'] ?? 'guest';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $uploaded_files = [];

        if (!empty($_FILES['screenshots']['name'][0])) {
            foreach ($_FILES['screenshots']['name'] as $key => $name) {
                $tmp_name = $_FILES['screenshots']['tmp_name'][$key];
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $safe_name = uniqid('proof_', true) . '.' . $ext;

                if (move_uploaded_file($tmp_name, $upload_dir . $safe_name)) {
                    $uploaded_files[] = $safe_name;
                }
            }

            if (!empty($uploaded_files)) {
                $screenshots_json = json_encode($uploaded_files);

                if ($payment_id === 0 && $ledger_id !== 0) {
                    $insert_sql = "
                        INSERT INTO job_payment (ledger_id, amount, payment_method, created_by, created_at, status)
                        VALUES ($ledger_id, $amount, 'wire', '$created_by', NOW(), 0)
                    ";

                    if (mysqli_query($conn, $insert_sql)) {
                        $payment_id = mysqli_insert_id($conn);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Failed to create new payment.']);
                        exit;
                    }
                }

                $update_sql = "
                    UPDATE job_payment 
                    SET screenshots = '$screenshots_json'
                    WHERE payment_id = $payment_id
                    LIMIT 1
                ";

                if (mysqli_query($conn, $update_sql)) {
                    echo json_encode(['status' => 'success', 'payment_id' => $payment_id]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update payment record.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Upload failed.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No files received.']);
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
                            <th class="text-center">Status</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Action</th>
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
                            $payment_id = $row['payment_id'];

                            $status = intval($row['status']);
                            $badge = $status === 1
                                ? "<span class='badge bg-success'>Paid</span>"
                                : "<span class='badge bg-warning text-dark'>Pending</span>";

                            $total_paid += $row['amount'];
                            echo "<tr>
                                    <td>$date</td>
                                    <td>$payment_method</td>
                                    <td>$cashier</td>
                                    <td>$reference_no</td>
                                    <td>$description</td>
                                    <td class='text-center'>$badge</td>
                                    <td class='text-end'>$$amount</td>
                                    <td class='text-center'>
                                        <a type='button' class='btnViewProofRow' title='View Proof of Payment' data-payment-id='$payment_id'>
                                            <iconify-icon icon='mdi:eye' class='fs-7 text-primary'></iconify-icon>
                                        </a>
                                        <a type='button' class='btnUploadProofRow' title='Upload Proof of Payment' data-payment-id='$payment_id'>
                                            <iconify-icon icon='mdi:upload' class='fs-7 text-warning'></iconify-icon>
                                        </a>
                                    </td>
                                </tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">Total</th>
                            <th class="text-end">$<?= number_format($total_paid, 2) ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>
        </div>

        <div class="modal-footer px-0">
            <button type="button" class="btn btn-outline-primary btnUploadProof" data-id="<?= $ledger_id ?>">
                Upload Payment Screenshots
            </button>
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
        </div>

        <?php
    }

    if ($action == 'view_payment_proof') {
        $payment_id = intval($_POST['payment_id'] ?? 0);
        $query = "SELECT screenshots FROM job_payment WHERE payment_id = $payment_id LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($row = mysqli_fetch_assoc($result)) {
            $screenshots = json_decode($row['screenshots'] ?? '[]', true);

            if (!empty($screenshots)) {
                ?>
                <div id="proofCarousel" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-inner">
                        <?php foreach ($screenshots as $index => $filename): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img 
                                    src="../uploads/payment_proofs/<?= htmlspecialchars($filename) ?>" 
                                    class="d-block w-100 preview-click" 
                                    style="max-height:500px;object-fit:contain;cursor: zoom-in;" 
                                    data-src="../uploads/payment_proofs/<?= htmlspecialchars($filename) ?>" 
                                    alt="Proof <?= $index + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#proofCarousel" data-bs-slide="prev"
                            style="width: 60px; background: rgba(0,0,0,0.5); border: none;">
                        <span class="carousel-control-prev-icon" style="filter: invert(1); width: 2rem; height: 2rem;"></span>
                        <span class="visually-hidden fw-bold text-white">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#proofCarousel" data-bs-slide="next"
                            style="width: 60px; background: rgba(0,0,0,0.5); border: none;">
                        <span class="carousel-control-next-icon" style="filter: invert(1); width: 2rem; height: 2rem;"></span>
                        <span class="visually-hidden fw-bold text-white">Next</span>
                    </button>
                </div>
                <?php
            } else {
                echo "<p class='text-center'>No screenshots found for this payment.</p>";
            }
        } else {
            echo "<p class='text-danger'>Invalid payment ID.</p>";
        }
        exit;
    }


    

    mysqli_close($conn);
}
?>
