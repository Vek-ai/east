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
            SELECT *
            FROM cash_flow
            WHERE DATE(`date`) = '$date'
            AND movement_type IN ('cash_inflow', 'cash_outflow')
            ORDER BY `date` ASC
        ";
        $result = mysqli_query($conn, $query);

        if (!$result || mysqli_num_rows($result) === 0) {
            echo '<div class="alert alert-info text-center">
                No cash flow records found for ' . date('M d, Y', strtotime($date)) . '.
            </div>';
            exit;
        }

        $inflows = [];
        $outflows = [];
        $total_inflows = 0;
        $total_outflows = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['movement_type'] === 'cash_inflow') {
                $inflows[] = $row;
                $total_inflows += floatval($row['amount']);
            } else {
                $outflows[] = $row;
                $total_outflows += floatval($row['amount']);
            }
        }

        ob_start();
        ?>

        <div class="mb-4">
            <h4 class="fw-bold text-success text-center mb-2">Cash Inflow for <?= date('F d, Y', strtotime($date)) ?></h4>
            <table class="table table-bordered table-striped table-sm align-middle">
                <thead class="text-center">
                    <tr>
                        <th>Salesperson</th>
                        <th>Invoice #</th>
                        <th>Customer Name</th>
                        <th>Cash Flow Type</th>
                        <th>Time</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($inflows) {
                        foreach ($inflows as $row) {
                            $orderid = $row['orderid'];
                            $order = getOrderDetails($orderid);
                            $customer_name = get_customer_name($order['customerid'] ?? '');
                            ?>
                            <tr class="text-center">
                                <td><?= htmlspecialchars(get_staff_name($row['received_by'])) ?></td>
                                <td><?= $orderid ?></td>
                                <td><?= $customer_name ?></td>
                                <td><?= ucwords(str_replace('_', ' ', $row['cash_flow_type'])) ?></td>
                                <td><?= date('h:i A', strtotime($row['date'])) ?></td>
                                <td class="text-end">$<?= number_format($row['amount'], 2) ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No cash inflows</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div>
            <h4 class="fw-bold text-danger text-center mb-2">Cash Outflow <?= date('F d, Y', strtotime($date)) ?></h4>
            <table class="table table-bordered table-striped table-sm align-middle">
                <thead class="text-center">
                    <tr>
                        <th colspan="3">Reason for Outflow</th>
                        <th>Cash Flow Type</th>
                        <th>Time</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($outflows) {
                        foreach ($outflows as $row) {
                            $orderid = $row['orderid'];
                            $order = getOrderDetails($orderid);
                            $customer_name = get_customer_name($order['customerid'] ?? '');
                            ?>
                            <tr class="text-center">
                                <td colspan="3"></td>
                                <td><?= ucwords(str_replace('_', ' ', $row['cash_flow_type'])) ?></td>
                                <td><?= date('h:i A', strtotime($row['date'])) ?></td>
                                <td class="text-end">$<?= number_format($row['amount'], 2) ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No cash outflows</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="mb-4">
            <table class="table table-bordered table-striped table-sm align-middle">
                <tbody>
                    <tfoot>
                        <tr class="fw-bold bg-light">
                            <td colspan="5" class="text-end">Total Inflows</td>
                            <td class="text-end text-success">$<?= number_format($total_inflows, 2) ?></td>
                        </tr>
                        <tr class="fw-bold bg-light">
                            <td colspan="5" class="text-end">Total Outflows</td>
                            <td class="text-end text-danger">$<?= number_format($total_outflows, 2) ?></td>
                        </tr>
                    </tfoot>
                </tbody>
            </table>
        </div>

        <?php
        echo ob_get_clean();
        exit;
    }


    if ($action == 'fetch_daily_sales') {
        $date = mysqli_real_escape_string($conn, $_POST['date']);
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
                CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name
            FROM orders AS o
            LEFT JOIN customer AS c ON c.customer_id = o.originalcustomerid
            WHERE o.status != 6
            AND o.order_date BETWEEN '$date_start' AND '$date_end'
            ORDER BY o.order_date DESC
        ";

        $result = mysqli_query($conn, $query);

        if (!$result || mysqli_num_rows($result) === 0) {
            echo '<div class="alert alert-info text-center">No sales found for this date.</div>';
            exit;
        }

        $response_html .= '
            <div class="mb-3 fw-bold text-center fs-5">
                Daily Sales for ' . date('F d, Y', strtotime($date)) . '
            </div>
            <table class="table table-sm table-bordered table-striped align-middle mb-0">
                <thead class="text-center">
                    <tr>
                        <th>Salesperson</th>
                        <th>Invoice #</th>
                        <th>Customer Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
        ';

        $total_amount = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            $orderid = $row['orderid'];
            $expected_amount = floatval($row['discounted_price']);
            $total_paid = floatval(getOrderTotalPayments($orderid));
            $customer_name = htmlspecialchars($row['customer_name']);
            $salesperson = htmlspecialchars(get_staff_name($row['cashier']));
            $order_date = date('F d, Y', strtotime($row['order_date']));
            $order_time = date('h:i A', strtotime($row['order_date']));

            $pay_cash     = floatval($row['pay_cash']);
            $pay_card     = floatval($row['pay_card']);
            $pay_check    = floatval($row['pay_check']);
            $pay_pickup   = floatval($row['pay_pickup']);
            $pay_delivery = floatval($row['pay_delivery']);
            $pay_net30    = floatval($row['pay_net30']);

            if (($pay_cash + $pay_card + $pay_check + $pay_pickup + $pay_delivery + $pay_net30) <= 0) continue;
            $credit_total = $pay_pickup + $pay_delivery + $pay_net30;

            if ($credit_total > 0) {
                if ($total_paid >= $credit_total) {
                    $credit_status = 'bg-success';
                } elseif ($total_paid > 0) {
                    $credit_status = 'bg-warning text-dark';
                } else {
                    $credit_status = 'bg-danger';
                }
            } else {
                $credit_status = 'bg-success';
            }

            $always_paid = 'bg-success';

            $parts = [
                'Cash'     => [$pay_cash, $always_paid],
                'Card'     => [$pay_card, $always_paid],
                'Check'    => [$pay_check, $always_paid],
                'Pickup'   => [$pay_pickup, $credit_status],
                'Delivery' => [$pay_delivery, $credit_status],
                'Net 30'   => [$pay_net30, $credit_status],
            ];

            foreach ($parts as $label => [$amount, $class]) {
                if ($amount <= 0) continue;

                $badge = '<span class="badge ' . $class . '">' . htmlspecialchars($label) . '</span>';

                $response_html .= '
                    <tr class="text-center">
                        <td>' . $salesperson . '</td>
                        <td>' . htmlspecialchars($orderid) . '</td>
                        <td>' . $customer_name . '</td>
                        <td>' . $order_date . '</td>
                        <td>' . $order_time . '</td>
                        <td>' . $badge . '</td>
                        <td class="text-end">$' . number_format($amount, 2) . '</td>
                    </tr>
                ';

                $total_amount += $amount;
            }
        }

        if ($total_amount == 0) {
            echo '<div class="alert alert-info text-center">No sales found for this date.</div>';
            exit;
        }

        $response_html .= '
                </tbody>
                <tfoot>
                    <tr class="fw-bold bg-light">
                        <td colspan="6" class="text-end">Total:</td>
                        <td class="text-end">$' . number_format($total_amount, 2) . '</td>
                    </tr>
                </tfoot>
            </table>
        ';

        echo $response_html;
        exit;
    }

    if ($action == 'fetch_receivable') {
        $date = mysqli_real_escape_string($conn, $_POST['date']);
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
                CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name
            FROM orders AS o
            LEFT JOIN customer AS c ON c.customer_id = o.originalcustomerid
            WHERE o.status != 6
            AND (
                o.pay_pickup > 0 OR 
                o.pay_delivery > 0 OR 
                o.pay_net30 > 0
            )
            AND o.order_date BETWEEN '$date_start' AND '$date_end'
            ORDER BY o.order_date DESC
        ";

        $result = mysqli_query($conn, $query);

        if (!$result || mysqli_num_rows($result) === 0) {
            echo '<div class="alert alert-info text-center">No receivable orders found for this date.</div>';
            exit;
        }

        $response_html .= '
            <div class="mb-3 fw-bold text-center fs-5">
                Accounts Receivable for ' . date('F d, Y', strtotime($date)) . '
            </div>
            <table class="table table-sm table-bordered table-striped align-middle mb-0">
                <thead class="text-center">
                    <tr>
                        <th>Salesperson</th>
                        <th>Invoice #</th>
                        <th>Customer Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
        ';

        $total_amount = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            $orderid = $row['orderid'];
            $customer_name = htmlspecialchars($row['customer_name']);
            $salesperson = htmlspecialchars(get_staff_name($row['cashier']));
            $order_date = date('F d, Y', strtotime($row['order_date']));
            $order_time = date('h:i A', strtotime($row['order_date']));

            $total_paid = floatval(getOrderTotalPayments($orderid));

            $parts = [
                'Pickup'   => floatval($row['pay_pickup']),
                'Delivery' => floatval($row['pay_delivery']),
                'Net 30'   => floatval($row['pay_net30'])
            ];

            $remaining_paid = $total_paid;

            foreach ($parts as $label => $amount) {
                if ($amount <= 0) continue;

                $allocated = min($remaining_paid, $amount);
                $remaining_paid -= $allocated;

                if ($allocated <= 0) {
                    $status_class = 'bg-danger';
                } elseif ($allocated < $amount) {
                    $status_class = 'bg-warning text-dark';
                } else {
                    $status_class = 'bg-success';
                }

                $badge = '<span class="badge ' . $status_class . '">' . htmlspecialchars($label) . '</span>';

                $response_html .= '
                    <tr class="text-center">
                        <td>' . $salesperson . '</td>
                        <td>' . htmlspecialchars($orderid) . '</td>
                        <td>' . $customer_name . '</td>
                        <td>' . $order_date . '</td>
                        <td>' . $order_time . '</td>
                        <td>' . $badge . '</td>
                        <td class="text-end">$' . number_format($amount, 2) . '</td>
                    </tr>
                ';

                $total_amount += $amount;
            }
        }

        if ($total_amount == 0) {
            echo '<div class="alert alert-info text-center">No receivable orders found for this date.</div>';
            exit;
        }

        $response_html .= '
                </tbody>
                <tfoot>
                    <tr class="fw-bold bg-light">
                        <td colspan="6" class="text-end">Total:</td>
                        <td class="text-end">$' . number_format($total_amount, 2) . '</td>
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
            2 => 'business_status',
            3 => 'total_transactions',
            4 => 'daily_status'
        ];
        $orderBy = $columns[$orderCol] ?? 'tx_date';

        $months = array_filter($_POST['month'] ?? [], fn($v) => $v !== '');
        $years  = array_filter($_POST['year'] ?? [], fn($v) => $v !== '');
        $days   = array_filter($_POST['day'] ?? [], fn($v) => $v !== '');
        $businessStatus = $_POST['business_status'] ?? '';
        $dailyStatus    = $_POST['daily_status'] ?? '';

        $maxDays = $start + $length;
        $baseSql = "
            WITH RECURSIVE seq AS (
                SELECT 0 AS n
                UNION ALL
                SELECT n + 1 FROM seq WHERE n + 1 < $maxDays
            )
            SELECT
                CURDATE() - INTERVAL seq.n DAY AS tx_date,
                COALESCE(cf.total_transactions, 0) AS total_transactions,
                s.closing_date
            FROM seq
            LEFT JOIN (
                SELECT DATE(`date`) AS d, COUNT(*) AS total_transactions
                FROM cash_flow
                GROUP BY DATE(`date`)
            ) cf ON cf.d = CURDATE() - INTERVAL seq.n DAY
            LEFT JOIN cash_flow_summary s ON s.closing_date = CURDATE() - INTERVAL seq.n DAY
            WHERE 1
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
        if ($businessStatus !== '') {
            $where .= $businessStatus === 'Open' 
                ? " AND DAYOFWEEK(tx_date) BETWEEN 2 AND 6"
                : " AND DAYOFWEEK(tx_date) IN (1,7)";
        }
        if ($dailyStatus !== '') {
            if ($dailyStatus === 'Completed') {
                $where .= " AND closing_date IS NOT NULL";
            } elseif ($dailyStatus === 'In Operation') {
                $where .= " AND tx_date = CURDATE() AND closing_date IS NULL";
            }
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
            $dayNum = date('N', strtotime($date));
            $businessStatusRow = ($dayNum <= 5) ? 'Open' : 'Closed';

            if (!empty($row['closing_date'])) {
                $dailyStatusRow = 'Completed';
            } elseif ($date === date('Y-m-d')) {
                $dailyStatusRow = 'In Operation';
            } else {
                $dailyStatusRow = 'Pending Completion';
            }

            $data[] = [
                'formatted_date'      => date('M. jS, Y', strtotime($date)),
                'day_of_week'         => date('l', strtotime($date)),
                'business_status'     => $businessStatusRow,
                'total_transactions' => $row['total_transactions'],
                'daily_status'        => $dailyStatusRow,
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
