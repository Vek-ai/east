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

    if ($action == 'fetch_table') {
        $station_filter = !empty($_POST['station']) ? "AND station_id=" . intval($_POST['station']) : "";
        $date_filter = !empty($_POST['date']) ? "AND DATE(date)='" . $_POST['date'] . "'" : "AND DATE(date)='" . date('Y-m-d') . "'";

        // Opening balance
        $opening = 0;
        $ob_query = "SELECT amount FROM cash_flow WHERE movement_type='opening_balance' $date_filter $station_filter LIMIT 1";
        $ob = mysqli_query($conn, $ob_query);
        if ($ob && mysqli_num_rows($ob)) {
            $row = mysqli_fetch_assoc($ob);
            $opening = floatval($row['amount']);
        }

        // Cash inflows
        $inflows = [];
        $total_inflows = 0;
        $ci_query = "SELECT cash_flow_type, SUM(amount) as total FROM cash_flow WHERE movement_type='cash_inflow' $date_filter $station_filter GROUP BY cash_flow_type";
        $ci = mysqli_query($conn, $ci_query);
        while ($row = mysqli_fetch_assoc($ci)) {
            $inflows[$row['cash_flow_type']] = floatval($row['total']);
            $total_inflows += floatval($row['total']);
        }

        // Cash outflows
        $outflows = [];
        $total_outflows = 0;
        $co_query = "SELECT cash_flow_type, SUM(amount) as total FROM cash_flow WHERE movement_type='cash_outflow' $date_filter $station_filter GROUP BY cash_flow_type";
        $co = mysqli_query($conn, $co_query);
        while ($row = mysqli_fetch_assoc($co)) {
            $outflows[$row['cash_flow_type']] = floatval($row['total']);
            $total_outflows += floatval($row['total']);
        }

        $closing_balance = $opening + $total_inflows - $total_outflows;

        ob_start();
        ?>
        <div class="datatables">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount ($)</th>
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
                                    <td><?= ucwords(str_replace('_',' ',$desc)) ?></td>
                                    <td class="text-end">$<?= number_format($amt, 2) ?></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td></td>
                                <td><strong>Total Inflows</strong></td>
                                <td class="text-end"><strong>$<?= number_format($total_inflows, 2) ?></strong></td>
                            </tr>
                        <?php } ?>

                        <?php if (!empty($outflows)) { ?>
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
                        <?php } ?>

                        <tr>
                            <td>Closing Balance</td>
                            <td>$<?= number_format($opening, 2) ?> + $<?= number_format($total_inflows, 2) ?> - $<?= number_format($total_outflows, 2) ?> =</td>
                            <td class="text-end"><strong>$<?= number_format($closing_balance, 2) ?></strong></td>
                        </tr>
                    </tbody>
                </table>

                <div class="text-end mt-3">
                    <button class="btn btn-danger">Confirm Closing</button>
                </div>
            </div>
        </div>
        <?php
        $table_html = ob_get_clean();

        echo json_encode(['data' => $table_html]);
        exit;
    }

    mysqli_close($conn);
}
?>
