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



    mysqli_close($conn);
}
?>
