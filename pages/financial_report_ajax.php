<?php
session_start();
require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'cash_flow';
$test_table = 'cash_flow_excel';

$permission = $_SESSION['permission'];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == 'fetch_table_old') {
        $date = !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');
        $selected_station = !empty($_POST['station']) ? intval($_POST['station']) : 0;

        $station_query = "SELECT station_id, station_name FROM station WHERE hidden = 0 AND status = 1";
        if ($selected_station) {
            $station_query .= " AND station_id = $selected_station";
        }
        $station_result = mysqli_query($conn, $station_query);

        $grand_opening = 0;
        $grand_inflows = 0;
        $grand_outflows = 0;
        $grand_closing = 0;

        ob_start();

        if ($station_result && mysqli_num_rows($station_result) > 0) {
            while ($st = mysqli_fetch_assoc($station_result)) {
                $station_id = $st['station_id'];
                $station_name = $st['station_name'];

                $summary_query = "SELECT * FROM cash_flow_summary WHERE closing_date='$date' AND station_id=$station_id LIMIT 1";
                $summary_res = mysqli_query($conn, $summary_query);

                $has_record = false;
                $opening = $total_inflows = $total_outflows = $closing_balance = 0;
                $inflows = [];
                $outflows = [];

                if ($summary_res && mysqli_num_rows($summary_res) > 0) {
                    $has_record = true;
                    $row = mysqli_fetch_assoc($summary_res);
                    $opening = floatval($row['opening_balance']);
                    $total_inflows = floatval($row['total_inflows']);
                    $total_outflows = floatval($row['total_outflows']);
                    $closing_balance = floatval($row['closing_balance']);

                    $details = json_decode($row['details_json'], true);
                    $inflows = $details['inflows'] ?? [];
                    $outflows = $details['outflows'] ?? [];

                    // Add to grand totals only if it exists
                    $grand_opening += $opening;
                    $grand_inflows += $total_inflows;
                    $grand_outflows += $total_outflows;
                    $grand_closing += $closing_balance;
                }

                ?>
                <div class="datatables mb-4">
                    <h5 class="mb-2">Station: <?= htmlspecialchars($station_name) ?></h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount ($)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Opening Balance</td>
                                    <td>Cash Float</td>
                                    <td class="text-end">
                                        <?= $has_record ? '$'.number_format($opening, 2) : '-' ?>
                                    </td>
                                </tr>

                                <?php if ($has_record && !empty($inflows)) { ?>
                                    <tr><td colspan="3"><strong>Cash Inflows</strong></td></tr>
                                    <?php foreach ($inflows as $desc => $amt) { ?>
                                        <tr>
                                            <td></td>
                                            <td><?= ucwords(str_replace('_',' ',$desc)) ?></td>
                                            <td class="text-end">$<?= number_format($amt, 2) ?></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td></td>
                                        <td><strong>Total Inflows</strong></td>
                                        <td class="text-end"><strong>$<?= number_format($total_inflows, 2) ?></strong></td>
                                    </tr>
                                <?php } elseif (!$has_record) { ?>
                                    <tr><td colspan="3" class="text-center text-muted">No inflows recorded</td></tr>
                                <?php } ?>

                                <?php if ($has_record && !empty($outflows)) { ?>
                                    <tr><td colspan="3"><strong>Cash Outflows</strong></td></tr>
                                    <?php foreach ($outflows as $desc => $amt) { ?>
                                        <tr>
                                            <td></td>
                                            <td><?= ucwords(str_replace('_',' ',$desc)) ?></td>
                                            <td class="text-end">$<?= number_format($amt, 2) ?></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td></td>
                                        <td><strong>Total Outflows</strong></td>
                                        <td class="text-end"><strong>$<?= number_format($total_outflows, 2) ?></strong></td>
                                    </tr>
                                <?php } elseif (!$has_record) { ?>
                                    <tr><td colspan="3" class="text-center text-muted">No outflows recorded</td></tr>
                                <?php } ?>

                                <tr>
                                    <td>Closing Balance</td>
                                    <td>
                                        <?php if ($has_record) { ?>
                                            $<?= number_format($opening, 2) ?> + $<?= number_format($total_inflows, 2) ?> - $<?= number_format($total_outflows, 2) ?> =
                                        <?php } ?>
                                    </td>
                                    <td class="text-end">
                                        <?= $has_record ? '<strong>$'.number_format($closing_balance, 2).'</strong>' : '-' ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }

            if (!$selected_station) {
                ?>
                <div class="datatables">
                    <h5 class="mb-2 text-primary">Grand Total (All Stations)</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Amount ($)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Opening Balance</td>
                                    <td class="text-end"><strong>$<?= number_format($grand_opening, 2) ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Total Inflows</td>
                                    <td class="text-end"><strong>$<?= number_format($grand_inflows, 2) ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Total Outflows</td>
                                    <td class="text-end"><strong>$<?= number_format($grand_outflows, 2) ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Closing Balance</td>
                                    <td class="text-end text-success"><strong>$<?= number_format($grand_closing, 2) ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }

        } else {
            ?>
            <div class="alert alert-warning">No stations found.</div>
            <?php
        }

        $table_html = ob_get_clean();
        echo json_encode(['data' => $table_html]);
        exit;
    }

    if ($action == 'fetch_view') {
        $date = !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');

        $openingQuery = "
            SELECT COALESCE(SUM(amount),0) AS opening_balance
            FROM cash_flow
            WHERE DATE(date) = '$date'
            AND movement_type = 'opening_balance'
        ";
        $openingResult = mysqli_query($conn, $openingQuery);
        $opening = floatval(mysqli_fetch_assoc($openingResult)['opening_balance']);

        $inflowQuery = "
            SELECT payment_method, SUM(amount) AS total
            FROM cash_flow
            WHERE DATE(date) = '$date'
            AND movement_type = 'cash_inflow'
            GROUP BY payment_method
        ";
        $inflowResult = mysqli_query($conn, $inflowQuery);
        $cash_inflows = 0;
        $inflowDetails = [];
        while ($row = mysqli_fetch_assoc($inflowResult)) {
            $method = $row['payment_method'] ?: 'Other';
            $amount = floatval($row['total']);
            $cash_inflows += $amount;
            $inflowDetails[$method] = $amount;
        }

        $outflowQuery = "
            SELECT cash_flow_type, SUM(amount) AS total
            FROM cash_flow
            WHERE DATE(date) = '$date'
            AND movement_type = 'cash_outflow'
            AND cash_flow_type != 'product_return'
            GROUP BY cash_flow_type
        ";
        $outflowResult = mysqli_query($conn, $outflowQuery);
        $cash_outflows = 0;
        $outflowDetails = [];
        while ($row = mysqli_fetch_assoc($outflowResult)) {
            $type = $row['cash_flow_type'] ?: 'Other';
            $amount = floatval($row['total']);
            $cash_outflows += $amount;
            $outflowDetails[$type] = $amount;
        }

        $returnQuery = "
            SELECT payment_method, SUM(amount) AS total
            FROM cash_flow
            WHERE DATE(date) = '$date'
            AND movement_type = 'cash_outflow'
            AND cash_flow_type = 'product_return'
            GROUP BY payment_method
        ";
        $returnResult = mysqli_query($conn, $returnQuery);
        $productReturnTotal = 0;
        $productReturnDetails = [];
        while ($row = mysqli_fetch_assoc($returnResult)) {
            $method = $row['payment_method'] ?: 'Other';
            $amount = floatval($row['total']);
            $productReturnTotal += $amount;
            $productReturnDetails[$method] = $amount;
        }

        $closing_balance = $opening + $cash_inflows - $cash_outflows - $productReturnTotal;

        ob_start();
        ?>
        <style>
        table {
            table-layout: fixed;
            width: 100%;
        }

        table td, table th {
            word-wrap: break-word;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        </style>

        <div class="datatables mb-4">
            <h5 class="text-center">View Daily Summary - <?= date('F, jS Y', strtotime($date)) ?></h5>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-striped text-center">
                    <tbody>
                        <tr>
                            <td class="text-start" colspan="2"><strong>Opening Balance</strong></td>
                            <td class="text-end"><strong>$<?= number_format($opening, 2) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mb-3">
                <table class="table table-bordered table-striped text-center">
                    <tbody>
                        <tr>
                            <th class="text-start" colspan="2"><strong>Total Cash Inflow</strong></th>
                            <th class="text-end"><strong>$<?= number_format($cash_inflows, 2) ?></strong></th>
                        </tr>
                        <?php foreach ($inflowDetails as $method => $amt) { ?>
                            <tr>
                                <td colspan="2"><?= ucwords(str_replace('_', ' ', $method)) ?></td>
                                <td class="text-end">$<?= number_format($amt, 2) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mb-3">
                <table class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th class="text-start" colspan="2"><strong>Total Cash Outflow</strong></th>
                            <th class="text-end"><strong>$<?= number_format($cash_outflows, 2) ?></strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($outflowDetails as $type => $amt) { ?>
                            <tr>
                                <td colspan="2"><?= ucwords(str_replace('_', ' ', $type)) ?></td>
                                <td class="text-end">$<?= number_format($amt, 2) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php if ($productReturnTotal > 0) { ?>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th class="text-start" colspan="2"><strong>Product Returns</strong></th>
                            <th class="text-end"><strong>$<?= number_format($productReturnTotal, 2) ?></strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productReturnDetails as $method => $amt) { ?>
                            <tr>
                                <td colspan="2"><?= ucwords(str_replace('_', ' ', $method)) ?></td>
                                <td class="text-end">$<?= number_format($amt, 2) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php } ?>

            <div class="table-responsive mb-3">
                <table class="table table-bordered table-striped text-center">
                    <tbody>
                        <tr>
                            <td class="text-start" colspan="2"><strong>Closing Balance</strong></td>
                            <td class="text-end"><strong>$<?= number_format($closing_balance, 2) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-start" colspan="2"><strong>Opening Balance</strong></td>
                            <td class="text-end"><strong>$<?= number_format($opening, 2) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-start" colspan="2"><strong>Difference</strong></td>
                            <td class="text-end"><strong>$<?= number_format($closing_balance - $opening, 2) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button id="close-btn" type="button" class="btn btn-danger" data-date="<?= $date ?>">Close</button>
        </div>
        <?php
        echo ob_get_clean();
        exit;
    }

    if ($action == 'close_station') {
        header('Content-Type: application/json');

        $cashier_id = intval($_SESSION['userid']);
        $date = !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');

        $check = mysqli_query(
            $conn,
            "SELECT 1 FROM cash_flow_summary WHERE DATE(closing_date) = '$date' LIMIT 1"
        );
        if ($check && mysqli_num_rows($check) > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Day already closed.'
            ]);
            exit;
        }

        $opening = 0;
        $ob = mysqli_query(
            $conn,
            "SELECT SUM(amount) AS total
            FROM cash_flow
            WHERE movement_type = 'opening_balance'
            AND DATE(date) = '$date'"
        );
        if ($ob) {
            $opening = floatval(mysqli_fetch_assoc($ob)['total']);
        }

        // Cash Inflows (by cash_flow_type)
        $inflows = [];
        $total_inflows = 0;
        $ci = mysqli_query(
            $conn,
            "SELECT cash_flow_type, SUM(amount) AS total
            FROM cash_flow
            WHERE movement_type = 'cash_inflow'
            AND DATE(date) = '$date'
            GROUP BY cash_flow_type"
        );
        while ($row = mysqli_fetch_assoc($ci)) {
            $type = $row['cash_flow_type'] ?: 'other';
            $amt  = floatval($row['total']);
            $inflows[$type] = $amt;
            $total_inflows += $amt;
        }

        // Cash Outflows (includes product_return)
        $outflows = [];
        $total_outflows = 0;
        $co = mysqli_query(
            $conn,
            "SELECT cash_flow_type, SUM(amount) AS total
            FROM cash_flow
            WHERE movement_type = 'cash_outflow'
            AND DATE(date) = '$date'
            GROUP BY cash_flow_type"
        );
        while ($row = mysqli_fetch_assoc($co)) {
            $type = $row['cash_flow_type'] ?: 'other';
            $amt  = floatval($row['total']);
            $outflows[$type] = $amt;
            $total_outflows += $amt;
        }

        $closing_balance = $opening + $total_inflows - $total_outflows;

        $details = [
            'opening_balance' => $opening,
            'inflows'         => $inflows,
            'total_inflows'   => $total_inflows,
            'outflows'        => $outflows,
            'total_outflows'  => $total_outflows,
            'closing_balance' => $closing_balance
        ];

        $details_json = mysqli_real_escape_string(
            $conn,
            json_encode($details)
        );

        $insert = "
            INSERT INTO cash_flow_summary (
                cashier_id,
                closing_date,
                opening_balance,
                total_inflows,
                total_outflows,
                closing_balance,
                details_json,
                created_at
            ) VALUES (
                $cashier_id,
                '$date',
                $opening,
                $total_inflows,
                $total_outflows,
                $closing_balance,
                '$details_json',
                NOW()
            )
        ";

        if (mysqli_query($conn, $insert)) {
            echo json_encode([
                'success' => true,
                'closing_balance' => number_format($closing_balance, 2)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => mysqli_error($conn)
            ]);
        }
        exit;
    }

    if ($action == 'fetch_cash_flow') {
        $selected_date = trim($_POST['date'] ?? '');

        if (!$selected_date) {
            echo '<div class="alert alert-warning text-center">No date specified.</div>';
            exit;
        }

        $date = date('Y-m-d', strtotime($selected_date));

        $query = "
            SELECT 
                cf.orderid,
                cf.amount,
                cf.date,
                cf.cash_flow_type,
                o.pay_type,
                o.orderid,
                o.customerid
            FROM cash_flow cf
            LEFT JOIN orders o ON o.orderid = cf.orderid
            WHERE DATE(cf.date) = '$date'
            AND cf.movement_type IN ('cash_inflow')
            ORDER BY cf.date ASC
        ";

        $result = mysqli_query($conn, $query);

        if (!$result || mysqli_num_rows($result) === 0) {
            echo '<div class="alert alert-info text-center">
                No cash inflow records found for ' . date('M d, Y', strtotime($date)) . '.
            </div>';
            exit;
        }

        $groups = [];
        $grandTotal = 0;

        while ($row = mysqli_fetch_assoc($result)) {

            $payTypeRaw = strtolower($row['pay_type'] ?? '');

            if($row['cash_flow_type'] == 'job_deposit'){
                $payType = 'Account Payments';
            } else if (strpos($payTypeRaw, 'cash') !== false) {
                $payType = 'Cash';
            } elseif (strpos($payTypeRaw, 'card') !== false) {
                $payType = 'Credit/Debit Card';
            } elseif (strpos($payTypeRaw, 'check') !== false || strpos($payTypeRaw, 'cheque') !== false) {
                $payType = 'Check';
            } elseif (strpos($payTypeRaw, 'pickup') !== false) {
                $payType = 'Pay at Pick-Up';
            } elseif (strpos($payTypeRaw, 'delivery') !== false) {
                $payType = 'Pay at Delivery';
            } elseif (strpos($payTypeRaw, 'net30') !== false) {
                $payType = 'Charge Net 30';
            } else {
                $payType = 'Other';
            }

            $groups[$payType]['rows'][] = $row;
            $groups[$payType]['total'] = ($groups[$payType]['total'] ?? 0) + $row['amount'];

            $grandTotal += $row['amount'];
        }

        if (empty($groups)) {
            echo '<div class="alert alert-info text-center">
                No valid cash inflows for ' . date('M d, Y', strtotime($date)) . '.
            </div>';
            exit;
        }

        ob_start();
        ?>

        <?php foreach ($groups as $payType => $group) { ?>
            <div class="mb-4">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="text-center bg-light">
                        <tr>
                            <th>Payment Type</th>
                            <th class="text-center"><?= $payType ?></th>
                        </tr>
                        <tr>
                            <th>Invoice #</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Business Name</th>
                            <th>Farm Name</th>
                            <th>Invoice Type</th>
                            <th>Job Name</th>
                            <th>PO #</th>
                            <th class="text-end" style="width: 200px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($group['rows'] as $row) {

                        $order = getOrderDetails($row['orderid']);
                        $customer = getCustomerDetails($order['customerid'] ?? '');
                    ?>
                        <tr class="text-center">
                            <td><?= $order['orderid'] ?? '-' ?></td>
                            <td><?= $customer['customer_first_name'] ?? '-' ?></td>
                            <td><?= $customer['customer_last_name'] ?? '-' ?></td>
                            <td><?= $customer['customer_business_name'] ?? '-' ?></td>
                            <td><?= $customer['customer_farm_name'] ?? '-' ?></td>
                            <td><?= $payType ?></td>
                            <td><?= $order['job_name'] ?? '-' ?></td>
                            <td><?= $order['job_po'] ?? '-' ?></td>
                            <td class="text-end" style="width: 200px;">$<?= number_format($row['amount'], 2) ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold bg-light">
                            <td colspan="8" class="text-end"><?= $payType ?> Total:</td>
                            <td class="text-end" style="width: 200px;">$<?= number_format($group['total'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php } ?>

        <div class="mb-3">
            <table class="table table-bordered table-sm">
                <tfoot>
                    <tr class="fw-bold text-white">
                        <td class="text-end">TOTAL CASH INFLOW:</td>
                        <td class="text-end" style="width: 200px;">
                            $<?= number_format($grandTotal, 2) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php
        echo ob_get_clean();
        exit;
    }


    if ($action == 'fetch_daily_sales') {
        $date = mysqli_real_escape_string($conn, $_POST['date'] ?? '');
        $response_html = '';

        if (empty($date)) {
            echo '<div class="alert alert-warning text-center">No date provided.</div>';
            exit;
        }

        $date_start = date('Y-m-d 00:00:00', strtotime($date));
        $date_end   = date('Y-m-d 23:59:59', strtotime($date));

        $query = "
            SELECT 
                o.*, 
                c.customer_first_name,
                c.customer_last_name,
                c.customer_business_name,
                c.customer_farm_name,
                c.tax_status
            FROM orders AS o
            LEFT JOIN customer AS c ON c.customer_id = o.customerid
            WHERE o.status != 6
            AND o.order_date BETWEEN '$date_start' AND '$date_end'
            ORDER BY o.order_date DESC
        ";

        $result = mysqli_query($conn, $query);
        $orders_by_tax = [];
        $all_tax_statuses = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $tax_status = $row['tax_status'] ?? 0;
            $orders_by_tax[$tax_status][] = $row;
            $all_tax_statuses[$tax_status] = $tax_status;
        }

        if (empty($all_tax_statuses)) {
            $all_tax_statuses[0] = 0;
        }

        $grand_total_materials = 0;
        $grand_total_delivery  = 0;
        $grand_total_tax       = 0;
        $grand_total_price     = 0;

        foreach ($all_tax_statuses as $tax_status_id) {
            $orders = $orders_by_tax[$tax_status_id] ?? [];
            $tax_status_name = htmlspecialchars(getCustomerTaxName($tax_status_id));
            $tax_rate_percent = floatval(getCustomerTaxById($tax_status_id));

            $response_html .= '
                <table class="table table-sm table-bordered table-striped align-middle mb-4">
                    <colgroup>
                        <col style="width:8%">
                        <col style="width:10%">
                        <col style="width:10%">
                        <col style="width:15%">
                        <col style="width:12%">
                        <col style="width:10%">
                        <col style="width:10%">
                        <col style="width:10%">
                        <col style="width:10%">
                        <col style="width:5%">
                    </colgroup>
                    <thead class="text-center bg-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Business/Customer Name</th>
                            <th>Farm Name</th>
                            <th>Materials Price</th>
                            <th>Delivery</th>
                            <th>Sales Tax</th>
                            <th>Total Price</th>
                            <th>Tax Status</th>
                        </tr>
                    </thead>
                    <tbody>
            ';

            $total_materials = 0;
            $total_delivery  = 0;
            $total_tax       = 0;
            $total_price     = 0;

            if (!empty($orders)) {
                foreach ($orders as $row) {
                    $materials_price = floatval($row['discounted_price'] ?? 0);
                    $delivery_price  = floatval($row['delivery_amt'] ?? 0);
                    $sales_tax       = $materials_price * $tax_rate_percent / 100;
                    $total_order     = $materials_price + $delivery_price + $sales_tax;

                    $total_materials += $materials_price;
                    $total_delivery  += $delivery_price;
                    $total_tax       += $sales_tax;
                    $total_price     += $total_order;

                    $response_html .= '
                        <tr class="text-center">
                            <td>' . htmlspecialchars($row['orderid']) . '</td>
                            <td>' . htmlspecialchars($row['customer_first_name'] ?? '-') . '</td>
                            <td>' . htmlspecialchars($row['customer_last_name'] ?? '-') . '</td>
                            <td>' . htmlspecialchars($row['customer_business_name'] ?? '-') . '</td>
                            <td>' . htmlspecialchars($row['customer_farm_name'] ?? '-') . '</td>
                            <td class="text-end">$' . number_format($materials_price, 2) . '</td>
                            <td class="text-end">$' . number_format($delivery_price, 2) . '</td>
                            <td class="text-end">$' . number_format($sales_tax, 2) .'</td>
                            <td class="text-end">$' . number_format($total_order, 2) . '</td>
                            <td>' . $tax_status_name . '</td>
                        </tr>
                    ';
                }
            } else {
                $response_html .= '
                    <tr>
                        <td colspan="10" class="text-center text-muted">No sales found.</td>
                    </tr>
                ';
            }

            $response_html .= '
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold text-end">
                            <td colspan="4"></td>
                            <td>' . $tax_status_name . ' Total:</td>
                            <td>$' . number_format($total_materials, 2) . '</td>
                            <td>$' . number_format($total_delivery, 2) . '</td>
                            <td>$' . number_format($total_tax, 2) . '</td>
                            <td>$' . number_format($total_price, 2) . '</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            ';

            $grand_total_materials += $total_materials;
            $grand_total_delivery  += $total_delivery;
            $grand_total_tax       += $total_tax;
            $grand_total_price     += $total_price;
        }

        $response_html .= '
            <table class="table table-sm align-middle mb-4">
                <colgroup>
                    <col style="width:8%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:15%">
                    <col style="width:12%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:5%">
                </colgroup>
                <tfoot>
                    <tr class="fw-bold text-end">
                        <td colspan="4"></td>
                        <td>Grand Total:</td>
                        <td>$' . number_format($grand_total_materials, 2) . '</td>
                        <td>$' . number_format($grand_total_delivery, 2) . '</td>
                        <td>$' . number_format($grand_total_tax, 2) . '</td>
                        <td>$' . number_format($grand_total_price, 2) . '</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        ';

        echo $response_html;
        exit;
    }

    if ($action == 'fetch_receivable') {
        $date = mysqli_real_escape_string($conn, $_POST['date'] ?? '');
        $response_html = '';

        if (empty($date)) {
            echo '<div class="alert alert-warning text-center">No date provided.</div>';
            exit;
        }

        $date_end = date('Y-m-d 23:59:59', strtotime($date));

        $sql = "
            SELECT
                c.customer_id,
                c.customer_first_name,
                c.customer_last_name,
                c.customer_business_name,
                c.customer_farm_name,

                COALESCE(sc.total_store_credit, 0) + COALESCE(dp.total_deposits, 0) AS available_balance,
                COALESCE(cr.total_credit, 0) - COALESCE(py.total_paid, 0) AS outstanding_credit,
                cr.first_credit_date,
                py.last_payment_date
            FROM customer c

            LEFT JOIN (
                SELECT customer_id, SUM(credit_amount) AS total_store_credit
                FROM customer_store_credit_history
                WHERE credit_type = 'add' AND credit_amount > 0
                GROUP BY customer_id
            ) sc ON sc.customer_id = c.customer_id

            LEFT JOIN (
                SELECT deposited_by AS customer_id, SUM(deposit_remaining) AS total_deposits
                FROM job_deposits
                WHERE deposit_status = 1 AND deposit_remaining > 0
                GROUP BY deposited_by
            ) dp ON dp.customer_id = c.customer_id

            LEFT JOIN (
                SELECT
                    customer_id,
                    SUM(amount) AS total_credit,
                    MIN(created_at) AS first_credit_date
                FROM job_ledger
                WHERE entry_type = 'credit' AND created_at <= '$date_end'
                GROUP BY customer_id
            ) cr ON cr.customer_id = c.customer_id

            LEFT JOIN (
                SELECT
                    jl.customer_id,
                    SUM(jp.amount) AS total_paid,
                    MAX(jp.created_at) AS last_payment_date
                FROM job_payment jp
                INNER JOIN job_ledger jl ON jl.ledger_id = jp.ledger_id
                WHERE jp.status = 1 AND jp.created_at <= '$date_end'
                GROUP BY jl.customer_id
            ) py ON py.customer_id = c.customer_id

            WHERE c.status = 1
            HAVING available_balance > 0 OR outstanding_credit > 0
            ORDER BY c.customer_last_name, c.customer_first_name
        ";

        $result = $conn->query($sql);

        if (!$result || $result->num_rows === 0) {
            echo '<div class="alert alert-info text-center">No receivable or credit balances found for this date.</div>';
            exit;
        }

        $response_html .= '
            <div class="mb-3 fw-bold text-center fs-5">
                Accounts Receivable / Credit Balances as of ' . date('F d, Y', strtotime($date)) . '
            </div>
            <table class="table table-sm table-bordered table-striped align-middle mb-0">
                <thead class="text-center bg-light">
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Business/Customer Name</th>
                        <th>Farm Name</th>
                        <th>Date Outstanding</th>
                        <th>Last Payment</th>
                        <th class="text-end">Total Credit Available</th>
                        <th class="text-end">Total Balance Due</th>
                    </tr>
                </thead>
                <tbody>
        ';

        $grand_total_available = 0;
        $grand_total_due = 0;

        while ($row = $result->fetch_assoc()) {
            $first_name  = htmlspecialchars($row['customer_first_name'] ?? '-');
            $last_name   = htmlspecialchars($row['customer_last_name'] ?? '-');
            $business    = htmlspecialchars($row['customer_business_name'] ?? '-');
            $farm_name   = htmlspecialchars($row['customer_farm_name'] ?? '-');

            $date_outstanding = $row['first_credit_date'] ? date('F d, Y', strtotime($row['first_credit_date'])) : '-';
            $last_payment     = $row['last_payment_date'] ? date('F d, Y', strtotime($row['last_payment_date'])) : '-';

            $available_balance = floatval($row['available_balance']);
            $outstanding_credit = floatval($row['outstanding_credit']);

            $grand_total_available += $available_balance;
            $grand_total_due       += $outstanding_credit;

            $response_html .= '
                <tr class="text-center">
                    <td>' . $first_name . '</td>
                    <td>' . $last_name . '</td>
                    <td>' . $business . '</td>
                    <td>' . $farm_name . '</td>
                    <td>' . $date_outstanding . '</td>
                    <td>' . $last_payment . '</td>
                    <td class="text-end">$' . number_format($available_balance, 2) . '</td>
                    <td class="text-end">$' . number_format($outstanding_credit, 2) . '</td>
                </tr>
            ';
        }

        $response_html .= '
                </tbody>
                <tfoot>
                    <tr class="fw-bold bg-light text-end">
                        <td colspan="6">Grand Total:</td>
                        <td>$' . number_format($grand_total_available, 2) . '</td>
                        <td>$' . number_format($grand_total_due, 2) . '</td>
                    </tr>
                </tfoot>
            </table>
        ';

        echo $response_html;
        exit;
    }

    if ($action == 'fetch_table') {
        $draw   = intval($_POST['draw'] ?? 0);
        $start  = intval($_POST['start'] ?? 0);
        $length = intval($_POST['length'] ?? 25);
        $searchValue = trim($_POST['search']['value'] ?? '');

        $orderCol = $_POST['order'][0]['column'] ?? 0;
        $orderDir = ($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        $columns = [
            0 => 'tx_date',
            1 => 'tx_date',
            2 => 'total_transactions'
        ];

        $orderBy = $columns[$orderCol] ?? 'tx_date';

        $months = array_filter($_POST['month'] ?? [], fn($v) => $v !== '');
        $years  = array_filter($_POST['year'] ?? [], fn($v) => $v !== '');
        $days   = array_filter($_POST['day'] ?? [], fn($v) => $v !== '');

        $maxDays = $start + $length;
        $baseSql = "
            WITH RECURSIVE seq AS (
                SELECT 0 AS n
                UNION ALL
                SELECT n + 1 FROM seq WHERE n + 1 < $maxDays
            )
            SELECT
                CURDATE() - INTERVAL seq.n DAY AS tx_date,
                COALESCE(cf.total_transactions, 0) AS total_transactions
            FROM seq
            LEFT JOIN (
                SELECT DATE(`date`) AS d, COUNT(*) AS total_transactions
                FROM cash_flow
                GROUP BY DATE(`date`)
            ) cf ON cf.d = CURDATE() - INTERVAL seq.n DAY
            LEFT JOIN cash_flow_summary s 
                ON s.closing_date = CURDATE() - INTERVAL seq.n DAY
            WHERE 1
            AND DAYOFWEEK(CURDATE() - INTERVAL seq.n DAY) BETWEEN 2 AND 6
        ";

        $where = '';
        if (!empty($months)) {
            $months = array_map('intval', $months);
            $where .= " AND MONTH(tx_date) IN (" . implode(',', $months) . ")";
        }
        if (!empty($years)) {
            $years = array_map('intval', $years);
            $where .= " AND YEAR(tx_date) IN (" . implode(',', $years) . ")";
        }
        if (!empty($days)) {
            $days = array_map('intval', $days);
            $where .= " AND DAY(tx_date) IN (" . implode(',', $days) . ")";
        }
        
        if ($searchValue !== '') {
            $searchValue = mysqli_real_escape_string($conn, $searchValue);
            $where .= "
                AND (
                    DATE_FORMAT(tx_date, '%b. %D, %Y') LIKE '%$searchValue%'
                    OR DAYNAME(tx_date) LIKE '%$searchValue%'
                )
            ";
        }

        $countSql = "SELECT COUNT(*) AS total FROM ($baseSql) t WHERE 1 $where";
        $recordsFiltered = intval($conn->query($countSql)->fetch_assoc()['total']);
        $recordsTotal = $recordsFiltered;

        $dataSql = "
            SELECT * FROM ($baseSql) t
            WHERE 1 $where
            ORDER BY $orderBy $orderDir
            LIMIT $start, $length
        ";
        $result = $conn->query($dataSql);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $date = $row['tx_date'];

            $data[] = [
                'formatted_date'      => date('M. jS, Y', strtotime($date)),
                'day_of_week'         => date('l', strtotime($date)),
                'total_transactions' => $row['total_transactions'],
                'action' => '
                    <a href="javascript:void(0)" class="me-1 view_report" data-date="'.$date.'">
                        <iconify-icon icon="solar:eye-outline" width="20"></iconify-icon>
                    </a>
                    <a href="javascript:void(0)" class="me-1 view_cash_flow" data-date="'.$date.'">
                        <iconify-icon icon="solar:wad-of-money-outline" width="20" class="text-warning"></iconify-icon>
                    </a>
                    <a href="javascript:void(0)" class="me-1 view_daily_sales" data-date="'.$date.'">
                        <iconify-icon icon="solar:chart-outline" width="20" class="text-info"></iconify-icon>
                    </a>
                    <a href="javascript:void(0)" class="me-1 view_receivable" data-date="'.$date.'">
                        <iconify-icon icon="solar:clipboard-outline" width="20" class="text-primary"></iconify-icon>
                    </a>
                    <a href="javascript:void(0)" class="me-1 view_print" data-id="'.$date.'">
                        <iconify-icon icon="solar:printer-outline" width="20" class="text-success"></iconify-icon>
                    </a>
                '
            ];
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
        exit;
    }

    mysqli_close($conn);
}
?>
