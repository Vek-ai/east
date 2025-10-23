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

        $query = "
            SELECT s.station_name, cfs.*
            FROM cash_flow_summary cfs
            LEFT JOIN station s ON s.station_id = cfs.station_id
            WHERE DATE(cfs.closing_date) = '$date'
            ORDER BY s.station_name ASC
        ";
        $result = mysqli_query($conn, $query);

        $grand_opening = 0;
        $grand_inflows = 0;
        $grand_outflows = 0;
        $grand_closing = 0;

        ob_start();

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $station_name = $row['station_name'] ?? 'Unknown Station';
                $opening = floatval($row['opening_balance']);
                $total_inflows = floatval($row['total_inflows']);
                $total_outflows = floatval($row['total_outflows']);
                $closing_balance = floatval($row['closing_balance']);

                $details = json_decode($row['details_json'], true);
                $inflows = $details['inflows'] ?? [];
                $outflows = $details['outflows'] ?? [];

                $grand_opening += $opening;
                $grand_inflows += $total_inflows;
                $grand_outflows += $total_outflows;
                $grand_closing += $closing_balance;
                ?>
                <div class="datatables mb-4">
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
                                    <td class="text-end">$<?= number_format($opening, 2) ?></td>
                                </tr>

                                <?php if (!empty($inflows)) { ?>
                                    <tr><td colspan="3"><strong>Cash Inflows</strong></td></tr>
                                    <?php foreach ($inflows as $desc => $amt) { ?>
                                        <tr>
                                            <td></td>
                                            <td><?= ucwords(str_replace('_', ' ', $desc)) ?></td>
                                            <td class="text-end">$<?= number_format($amt, 2) ?></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td></td>
                                        <td><strong>Total Inflows</strong></td>
                                        <td class="text-end"><strong>$<?= number_format($total_inflows, 2) ?></strong></td>
                                    </tr>
                                <?php } else { ?>
                                    <tr><td colspan="3" class="text-center text-muted">No inflows recorded</td></tr>
                                <?php } ?>

                                <?php if (!empty($outflows)) { ?>
                                    <tr><td colspan="3"><strong>Cash Outflows</strong></td></tr>
                                    <?php foreach ($outflows as $desc => $amt) { ?>
                                        <tr>
                                            <td></td>
                                            <td><?= ucwords(str_replace('_', ' ', $desc)) ?></td>
                                            <td class="text-end">$<?= number_format($amt, 2) ?></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td></td>
                                        <td><strong>Total Outflows</strong></td>
                                        <td class="text-end"><strong>$<?= number_format($total_outflows, 2) ?></strong></td>
                                    </tr>
                                <?php } else { ?>
                                    <tr><td colspan="3" class="text-center text-muted">No outflows recorded</td></tr>
                                <?php } ?>

                                <tr>
                                    <td>Closing Balance</td>
                                    <td>
                                        $<?= number_format($opening, 2) ?> + $<?= number_format($total_inflows, 2) ?> - $<?= number_format($total_outflows, 2) ?> =
                                    </td>
                                    <td class="text-end"><strong>$<?= number_format($closing_balance, 2) ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="alert alert-warning">No cash flow summaries found for <?= htmlspecialchars($date) ?>.</div>
            <?php
        }

        $table_html = ob_get_clean();
        echo $table_html;
        exit;
    }

    if ($action == 'fetch_cash_flow') {
        $selected_date = isset($_POST['date']) ? trim($_POST['date']) : null;

        if (!$selected_date) {
            echo '<div class="alert alert-warning text-center">No date specified.</div>';
            exit;
        }

        $date_formatted = date('Y-m-d', strtotime($selected_date));

        $query = "
            SELECT *
            FROM cash_flow
            WHERE DATE(`date`) = '$date_formatted'
            ORDER BY `date` DESC
        ";

        $result = mysqli_query($conn, $query);

        if (!$result || mysqli_num_rows($result) === 0) {
            echo '<div class="alert alert-info text-center">No cash flow records found for ' . date('M d, Y', strtotime($date_formatted)) . '.</div>';
            exit;
        }

        $html = '
            <div class="mb-3 fw-bold text-center fs-5">
                Cash Flow for ' . date('F d, Y', strtotime($date_formatted)) . '
            </div>
            <table class="table table-bordered table-striped table-sm align-middle mb-0">
                <thead class="text-center">
                    <tr>
                        <th>Cashier</th>
                        <th>Payment Method</th>
                        <th>Movement Type</th>
                        <th>Cash Flow Type</th>
                        <th>Date</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
        ';

        $total_amount = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            $dateObj = new DateTime($row['date']);
            $cashier = htmlspecialchars(get_staff_name($row['received_by']));
            $payment_method = htmlspecialchars(ucwords($row['payment_method']));
            $movement_type = htmlspecialchars(ucwords(str_replace('_', ' ', $row['movement_type'])));
            $cash_flow_type = htmlspecialchars(ucwords(str_replace('_', ' ', $row['cash_flow_type'])));
            $date_display = $dateObj->format('m/d/Y');
            $amount = floatval($row['amount']);
            $total_amount += $amount;

            $html .= "
                <tr class='text-center'>
                    <td>{$cashier}</td>
                    <td>{$payment_method}</td>
                    <td>{$movement_type}</td>
                    <td>{$cash_flow_type}</td>
                    <td>{$date_display}</td>
                    <td class='text-end'>₱" . number_format($amount, 2) . "</td>
                </tr>
            ";
        }

        $html .= '
                </tbody>
            </table>
        ';

        echo $html;
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
            SELECT o.*, 
                CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name
            FROM orders AS o
            LEFT JOIN customer AS c ON c.customer_id = o.originalcustomerid
            WHERE o.status != 6
            AND o.order_date BETWEEN '$date_start' AND '$date_end'
            ORDER BY o.order_date DESC
        ";

        $result = mysqli_query($conn, $query);

        if (!$result || mysqli_num_rows($result) === 0) {
            echo '<div class="alert alert-info text-center">No orders found for this date.</div>';
            exit;
        }

        $response_html .= '
            <table class="table table-sm table-bordered table-striped align-middle mb-0">
                <thead class="text-center">
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Salesperson</th>
                    </tr>
                </thead>
                <tbody>
        ';

        $total_amount = 0;
        $status_labels = [
            'pickup'   => ['label' => 'Pay at Pick-up'],
            'delivery' => ['label' => 'Pay at Delivery'],
            'cash'     => ['label' => 'Cash'],
            'check'    => ['label' => 'Check'],
            'card'     => ['label' => 'Credit/Debit Card'],
            'net30'    => ['label' => 'Charge Net 30'],
        ];

        while ($row = mysqli_fetch_assoc($result)) {
            $orderid = $row['orderid'];
            $pay_type = strtolower(trim($row['pay_type']));
            $label = $status_labels[$pay_type]['label'] ?? ucfirst($pay_type);

            $total_paid = getOrderTotalPayments($orderid);
            $expected_amount = floatval($row['discounted_price']);
            $color = 'secondary';
            $payment_status = 'Not Paid';

            if ($total_paid <= 0) {
                $color = 'danger';
                $payment_status = 'Not Paid';
            } elseif ($total_paid < $expected_amount) {
                $color = 'warning';
                $payment_status = 'Partially Paid';
            } else {
                $color = 'success';
                $payment_status = 'Fully Paid';
            }

            $response_html .= '
                <tr>
                    <td class="text-center">' . htmlspecialchars($orderid) . '</td>
                    <td>' . htmlspecialchars($row['customer_name']) . '</td>
                    <td class="text-end">' . number_format($expected_amount, 2) . '</td>
                    <td>' . date('F d, Y', strtotime($row['order_date'])) . '</td>
                    <td>' . date('h:i A', strtotime($row['order_date'])) . '</td>
                    <td class="text-center"><span class="badge bg-' . $color . '">' . $payment_status . '</span></td>
                    <td>' . htmlspecialchars(get_staff_name($row['cashier'])) . '</td>
                </tr>
            ';

            $total_amount += $expected_amount;
        }

        $response_html .= '
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="2" class="text-end">Total:</td>
                        <td class="text-end">' . number_format($total_amount, 2) . '</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        ';

        echo $response_html;
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
            AND o.pay_type IN ('net30', 'pickup', 'delivery')
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
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Salesperson</th>
                    </tr>
                </thead>
                <tbody>
        ';

        $total_amount = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            $orderid = $row['orderid'];
            $pay_type = strtolower(trim($row['pay_type']));

            $total_paid = floatval(getOrderTotalPayments($orderid));
            $expected_amount = floatval($row['discounted_price']);

            if ($total_paid <= 0) {
                $color = 'danger';
                $payment_status = 'Not Paid';
            } elseif ($total_paid < $expected_amount) {
                $color = 'warning';
                $payment_status = 'Partially Paid';
            } else {
                $color = 'success';
                $payment_status = 'Fully Paid';
            }

            $response_html .= '
                <tr class="text-center">
                    <td class="text-center">' . htmlspecialchars($orderid) . '</td>
                    <td>' . htmlspecialchars($row['customer_name']) . '</td>
                    <td class="text-end">₱' . number_format($expected_amount, 2) . '</td>
                    <td>' . date('F d, Y', strtotime($row['order_date'])) . '</td>
                    <td>' . date('h:i A', strtotime($row['order_date'])) . '</td>
                    <td class="text-center"><span class="badge bg-' . $color . '">' . $payment_status . '</span></td>
                    <td>' . htmlspecialchars(get_staff_name($row['cashier'])) . '</td>
                </tr>
            ';

            $total_amount += $expected_amount;
        }

        if ($total_amount == 0) {
            echo '<div class="alert alert-info text-center">No unpaid receivables for this date.</div>';
            exit;
        }

        $response_html .= '
                </tbody>
                <tfoot>
                    <tr class="fw-bold bg-light">
                        <td colspan="2" class="text-end">Total:</td>
                        <td class="text-end">₱' . number_format($total_amount, 2) . '</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        ';

        echo $response_html;
        exit;
    }




    mysqli_close($conn);
}
?>
