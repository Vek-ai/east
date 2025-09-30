<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/send_email.php';

$emailSender = new EmailTemplates();

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_view_modal") {
        $orderid = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM order_product WHERE orderid = '$orderid'";
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
                #est_dtls_tbl {
                    width: 100% !important;
                }

                #est_dtls_tbl td, #est_dtls_tbl th {
                    white-space: normal !important;
                    word-wrap: break-word;
                }
            </style>
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            View Order
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div id="update_product" class="form-horizontal">
                        <div class="modal-body">
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
                                        <table id="est_dtls_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" id="select_all"></th>
                                                    <th>Description</th>
                                                    <th>Color</th>
                                                    <th>Grade</th>
                                                    <th>Profile</th>
                                                    <th class="text-center">Quantity</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-center">Dimensions</th>
                                                    <th class="text-center">Price</th>
                                                    <th class="text-center">Customer Price</th>
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
                                                            <td class="text-center">
                                                                <input type="checkbox" class="row-checkbox" value="<?= $row['id'] ?>" data-status="">
                                                            </td>
                                                            <td>
                                                                <?= $product_name ?>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex mb-0 gap-8">
                                                                    <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromColorID($row['custom_color'])?>"></a>
                                                                    <?= getColorFromID($row['custom_color']); ?>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <?= getGradeName($row['custom_grade']); ?>
                                                            </td>
                                                            <td>
                                                                <?= getProfileTypeName($row['custom_profile']); ?>
                                                            </td>
                                                            <td><?= $row['quantity'] ?></td>
                                                            <td>
                                                                <span class="<?= $status_prod['class']; ?> fw-bond"><?= $status_prod['label']; ?></span>
                                                            </td>
                                                            <td>
                                                                <?php 
                                                                $width = $row['custom_width'];
                                                                $height = $row['custom_height'];
                                                                
                                                                if (!empty($width) && !empty($height)) {
                                                                    echo htmlspecialchars($width) . " X " . htmlspecialchars($height);
                                                                } elseif (!empty($width)) {
                                                                    echo "Width: " . htmlspecialchars($width);
                                                                } elseif (!empty($height)) {
                                                                    echo "Height: " . htmlspecialchars($height);
                                                                }
                                                                ?>
                                                            </td>
                                                            <td class="text-end">$ <?= number_format($row['actual_price'],2) ?></td>
                                                            <td class="text-end">$ <?= number_format($row['discounted_price'],2) ?></td>
                                                        </tr>
                                                <?php
                                                        $totalquantity += $row['quantity'] ;
                                                        $total_actual_price += $row['actual_price'];
                                                        $total_disc_price += $row['discounted_price'];
                                                        $total_amount += $total_disc_price;
                                                    }
                                                
                                                ?>
                                            </tbody>

                                            <tfoot>
                                                <tr>
                                                    <td colspan="6" class="text-end">Total</td>
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
                        </div>
                    </div>
                </div>
            </div>
            <script>
                $(document).ready(function() {
                    $('#est_dtls_tbl').DataTable({
                        language: {
                            emptyTable: "Order Details not found"
                        },
                        autoWidth: false,
                        responsive: true,
                        lengthChange: false
                    });

                    $('#select_all').on('change', function () {
                        $('.row-checkbox').prop('checked', this.checked);
                    });

                    $(document).on('change', '.row-checkbox', function () {
                        const allChecked = $('.row-checkbox').length === $('.row-checkbox:checked').length;
                        $('#select_all').prop('checked', allChecked);
                    });

                    window.getSelectedIDs = function () {
                        let ids = [];
                        $('.row-checkbox:checked').each(function () {
                            ids.push($(this).val());
                        });
                        console.log("Selected IDs:", ids);
                        return ids;
                    };
                });
            </script>

            <?php
        }
    } 

    if ($action == "download_excel") {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Invoice List.xls");

        $pay_labels = [
            'pickup'   => ['label' => 'Pay at Pick-up'],
            'delivery' => ['label' => 'Pay at Delivery'],
            'cash'     => ['label' => 'Cash'],
            'check'    => ['label' => 'Check'],
            'card'     => ['label' => 'Credit/Debit Card'],
            'net30'    => ['label' => 'Charge Net 30'],
        ];

        echo "<table border='1'>";
        echo "<thead>
                <tr style='font-weight: bold; background-color: #f0f0f0;'>
                    <th>Customer</th>
                    <th>Total Price</th>
                    <th>Order Date</th>
                    <th>Payment Method</th>
                </tr>
            </thead><tbody>";

        $query = "SELECT * FROM orders ORDER BY order_date DESC";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $customer_name = get_customer_name($row["customerid"]);
            $total_price = number_format($row['discounted_price'], 2);

            $date = '';
            if (!empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') {
                $date = date("F d, Y", strtotime($row["order_date"]));
            }

            $pay_type_key = strtolower(trim($row['pay_type']));
            $pay_type = $pay_labels[$pay_type_key]['label'] ?? ucfirst($pay_type_key);

            echo "<tr>
                    <td>" . htmlspecialchars($customer_name) . "</td>
                    <td style='text-align: right'>" . $total_price . "</td>
                    <td style='text-align: center'>" . $date . "</td>
                    <td style='text-align: center'>" . $pay_type . "</td>
                </tr>";
        }

        echo "</tbody></table>";
        exit;
    }

    if ($action == "download_pdf") {
        require '../includes/fpdf/fpdf.php';

        $pay_labels = [
            'pickup'   => ['label' => 'Pay at Pick-up'],
            'delivery' => ['label' => 'Pay at Delivery'],
            'cash'     => ['label' => 'Cash'],
            'check'    => ['label' => 'Check'],
            'card'     => ['label' => 'Credit/Debit Card'],
            'net30'    => ['label' => 'Charge Net 30'],
        ];

        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Invoice List', 0, 1, 'L');

        $usableWidth = $pdf->GetPageWidth() - 20;

        $colWidths = [
            'Customer' => $usableWidth * 0.35,
            'Total'    => $usableWidth * 0.15,
            'Date'     => $usableWidth * 0.20,
            'Payment'  => $usableWidth * 0.30
        ];

        $cellheight = 6;

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($colWidths['Customer'], $cellheight, 'Customer', 1);
        $pdf->Cell($colWidths['Total'], $cellheight, 'Total Price', 1, 0, 'C');
        $pdf->Cell($colWidths['Date'], $cellheight, 'Order Date', 1, 0, 'C');
        $pdf->Cell($colWidths['Payment'], $cellheight, 'Payment Method', 1, 0, 'C');
        $pdf->Ln();

        $query = "SELECT * FROM orders ORDER BY order_date DESC";
        $result = mysqli_query($conn, $query);
        $pdf->SetFont('Arial', '', 10);
        while ($row = mysqli_fetch_assoc($result)) {
            $customer_name = get_customer_name($row["customerid"]);
            $total_price = number_format($row['discounted_price'], 2);

            $date = '';
            if (!empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') {
                $date = date("F d, Y", strtotime($row["order_date"]));
            }

            $pay_type_key = strtolower(trim($row['pay_type']));
            $pay_type = $pay_labels[$pay_type_key]['label'] ?? ucfirst($pay_type_key);

            $pdf->Cell($colWidths['Customer'], $cellheight, $customer_name, 1);
            $pdf->Cell($colWidths['Total'], $cellheight, $total_price, 1, 0, 'R');
            $pdf->Cell($colWidths['Date'], $cellheight, $date, 1, 0, 'C');
            $pdf->Cell($colWidths['Payment'], $cellheight, $pay_type, 1, 0, 'C');
            $pdf->Ln();
        }

        $pdf->Output('I', 'order_list.pdf');
        exit;
    }

    if ($action == "print_result") {
        $pay_labels = [
            'pickup'   => ['label' => 'Pay at Pick-up'],
            'delivery' => ['label' => 'Pay at Delivery'],
            'cash'     => ['label' => 'Cash'],
            'check'    => ['label' => 'Check'],
            'card'     => ['label' => 'Credit/Debit Card'],
            'net30'    => ['label' => 'Charge Net 30'],
        ];

        $query = "SELECT * FROM orders ORDER BY order_date DESC";
        $result = mysqli_query($conn, $query);
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print Orders</title>
            <style>
                table {
                    border-collapse: collapse;
                    width: 100%;
                }
                th, td {
                    border: 1px solid #000;
                    padding: 2px;
                    padding-left: 8px;
                    
                }
                th {
                    background-color: #f0f0f0;
                }
            </style>
        </head>
        <body onload="window.print()">
            <h3>Invoice List</h3>
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th style="text-align: right">Total Price</th>
                        <th style="text-align: center">Order Date</th>
                        <th style="text-align: center">Payment Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        while ($row = mysqli_fetch_assoc($result)){ 
                        $customer_name = get_customer_name($row["customerid"]);
                        $total_price = '$' .number_format(floatval($row['discounted_price']), 2);

                        $date = '';
                        if (!empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') {
                            $date = date("F d, Y", strtotime($row["order_date"]));
                        }

                        $pay_type_key = strtolower(trim($row['pay_type']));
                        $pay_type = $pay_labels[$pay_type_key]['label'] ?? ucfirst($pay_type_key);
                    ?>
                        <tr>
                            <td><?= $customer_name ?></td>
                            <td style="text-align: right"><?= $total_price ?></td>
                            <td style="text-align: center"><?= $date ?></td>
                            <td style="text-align: center"><?= $pay_type ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
        exit;
    }

    if ($action == "send_order") {
        $orderid = mysqli_real_escape_string($conn, $_POST['send_order_id']);
        $customer_id = mysqli_real_escape_string($conn, $_POST['send_customer_id']);
        $customer_details = getCustomerDetails($customer_id);
        $customer_name = get_customer_name($customer_id);
        $customer_email = $customer_details['contact_email'];
        $customer_phone = $customer_details['contact_phone'];

        $send_option = mysqli_real_escape_string($conn, $_POST['send_option']);
        $order_url = "https://metal.ilearnwebtech.com/print_order_product.php?id=" . urlencode($orderid);
        $subject = "Order Invoice";

        $results = [];

        if ($send_option === 'email' || $send_option === 'both') {
            $results['email'] = $emailSender->sendInvoiceToCustomer($customer_email, $subject, $order_url);
        }

        if ($send_option === 'sms' || $send_option === 'both') {
            $sms_message = "Hi $customer_name,\n\nYour order invoice is ready.\nClick this link to view your receipt:\n$order_url";
            $results['sms'] = $emailSender->sendPhoneMessage($customer_phone, $subject, $sms_message);
        }

        $response = [
            'success' => true,
            'message' => "Successfully sent to Customer",
            'results' => $results
        ];

        echo json_encode($response);
    }


    if ($action === 'update_delivery_payment') {
        $order_id = mysqli_real_escape_string($conn, $_POST['orderid'] ?? '');
        $delivery_method = mysqli_real_escape_string($conn, $_POST['delivery_method'] ?? '');
        $payment_option = mysqli_real_escape_string($conn, $_POST['payment_option'] ?? '');

        $current_user = $_SESSION['userid'] ?? 'System';

        if (!empty($order_id)) {
            $old_query = "SELECT deliver_method, pay_type FROM orders WHERE orderid = '$order_id' LIMIT 1";
            $old_result = mysqli_query($conn, $old_query);
            $old_data = mysqli_fetch_assoc($old_result);

            $query = "
                UPDATE orders 
                SET 
                    deliver_method = '$delivery_method', 
                    pay_type = '$payment_option' 
                WHERE orderid = '$order_id'
            ";

            if (mysqli_query($conn, $query)) {
                $new_data = [
                    'deliver_method' => $delivery_method,
                    'pay_type' => $payment_option
                ];

                $changes = [];
                foreach ($new_data as $key => $new_val) {
                    $old_val = $old_data[$key] ?? '';
                    if ((string)$new_val !== (string)$old_val) {
                        $changes[$key] = [
                            'old' => $old_val,
                            'new' => $new_val
                        ];
                    }
                }

                if (!empty($changes)) {
                    $old_json = json_encode(array_map(fn($v) => $v['old'], $changes));
                    $new_json = json_encode(array_map(fn($v) => $v['new'], $changes));

                    $log_sql = "
                        INSERT INTO order_history 
                            (orderid, action_type, old_value, new_value, updated_by) 
                        VALUES 
                            ('$order_id', 'update_order', 
                            '" . mysqli_real_escape_string($conn, $old_json) . "', 
                            '" . mysqli_real_escape_string($conn, $new_json) . "', 
                            '" . mysqli_real_escape_string($conn, $current_user) . "')
                    ";
                    mysqli_query($conn, $log_sql);
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Order updated successfully.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Database error: ' . mysqli_error($conn)
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Missing order ID'
            ]);
        }
    }

    if ($action === 'fetch_order_history') {
        $orderid = intval($_POST['id']);

        $history_query = "SELECT * FROM order_history 
                        WHERE orderid = '$orderid' 
                        ORDER BY created_at DESC";

        $history_result = mysqli_query($conn, $history_query);

        $status_prod_labels = [
            0 => ['label' => 'New', 'class' => 'badge bg-primary'],
            1 => ['label' => 'Processing', 'class' => 'badge bg-success'],
            2 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-warning'],
            3 => ['label' => 'In Transit', 'class' => 'badge bg-secondary'],
            4 => ['label' => 'Delivered', 'class' => 'badge bg-success'],
            5 => ['label' => 'On Hold', 'class' => 'badge bg-danger']
        ];
        ?>
        <div class="modal-header">
            <h5 class="modal-title">Change History</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <?php if (mysqli_num_rows($history_result) > 0): ?>
                <div class="datatables">
                    <div class="table-responsive">
                        <table id="history_tbl" class="table table-sm text-center">
                            <thead>
                                <tr>
                                    <th style="max-width: 20%;">Scope</th>
                                    <th>Action</th>
                                    <th>Updated By</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($history_result)): ?>
                                    <?php
                                        $old = json_decode($row['old_value'], true);
                                        $new = json_decode($row['new_value'], true);

                                        $is_product = !empty($row['order_product_id']);
                                        $scope_label = 'Order Details';

                                        if ($is_product) {
                                            $prod_details = getOrderProdDetails($row['order_product_id']);
                                            $product_id = $prod_details['productid'];
                                            $scope_label = getProductName($product_id);
                                        }

                                        $staff_name = get_staff_name($row['updated_by']);
                                        $datetime = new DateTime($row['created_at']);
                                        $date = $datetime->format('M j, Y');
                                        $time = $datetime->format('h:i A');

                                        foreach ($new as $key => $new_val):
                                            $old_val = $old[$key] ?? '';

                                            switch ($key) {
                                                case 'custom_color':
                                                    $old_val = getColorName($old_val);
                                                    $new_val = getColorName($new_val);
                                                    break;
                                                case 'custom_grade':
                                                    $old_val = getGradeName($old_val);
                                                    $new_val = getGradeName($new_val);
                                                    break;
                                                case 'profile':
                                                    $old_val = getProfileTypeName($old_val);
                                                    $new_val = getProfileTypeName($new_val);
                                                    break;
                                                case 'status':
                                                    $old_badge = $status_prod_labels[$old_val] ?? ['label' => $old_val, 'class' => 'badge bg-secondary'];
                                                    $new_badge = $status_prod_labels[$new_val] ?? ['label' => $new_val, 'class' => 'badge bg-secondary'];
                                                    $old_val = "<span class='{$old_badge['class']}'>" . htmlspecialchars($old_badge['label']) . "</span>";
                                                    $new_val = "<span class='{$new_badge['class']}'>" . htmlspecialchars($new_badge['label']) . "</span>";
                                                    break;
                                            }

                                            if ((string)$new_val !== (string)$old_val):
                                                $field_name = ucwords(str_replace('_', ' ', $key));
                                    ?>
                                        <tr>
                                            <td style="max-width: 20%;"><?= htmlspecialchars($scope_label) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($field_name) ?>:</strong>
                                                <?= $key === 'status' ? "$old_val → $new_val" : "<span class='text-danger'>" . htmlspecialchars($old_val) . "</span> → <span class='text-success'>" . htmlspecialchars($new_val) . "</span>" ?>
                                            </td>
                                            <td><?= htmlspecialchars($staff_name) ?></td>
                                            <td><?= htmlspecialchars($date) ?></td>
                                            <td><?= htmlspecialchars($time) ?></td>
                                        </tr>
                                    <?php
                                            endif;
                                        endforeach;
                                    ?>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-center">No change history found.</p>
            <?php endif; ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
        <?php
    }

    mysqli_close($conn);
}
?>
