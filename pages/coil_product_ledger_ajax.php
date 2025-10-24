<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

$emailSender = new EmailTemplates();

if (isset($_POST['search_tx'])) {
    $coil_id = mysqli_real_escape_string($conn, $_POST['coilid']);
    $date_from = !empty($_POST['date_from']) ? $_POST['date_from'] . ' 00:00:00' : null;
    $date_to = !empty($_POST['date_to']) ? $_POST['date_to'] . ' 23:59:59' : null;

    $date_filter = "";
    if ($date_from && $date_to) {
        $date_filter = "AND date BETWEEN '$date_from' AND '$date_to'";
    } elseif ($date_from) {
        $date_filter = "AND date >= '$date_from'";
    } elseif ($date_to) {
        $date_filter = "AND date <= '$date_to'";
    }

    $sql_tx = "
        SELECT id, coilid, date, remaining_length, length_before_use, used_in_workorders
        FROM coil_transaction
        WHERE coilid = '$coil_id' $date_filter
        ORDER BY date DESC
    ";
    $res_tx = mysqli_query($conn, $sql_tx);

    if (!$res_tx || mysqli_num_rows($res_tx) == 0) {
        echo '<div class="alert alert-warning">No coil transactions found for this date range.</div>';
        exit;
    }
    ?>

    <div class="card card-body mb-4">
        <div class="datatables">
            <div class="table-responsive">
                <table id="coilTransactions" class="table align-middle text-center text-wrap">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice</th>
                            <th>Line Item</th>
                            <th>Product ID</th>
                            <th>Description</th>
                            <th>Initial (Ft)</th>
                            <th>Used (Ft)</th>
                            <th>Remaining (Ft)</th>
                            <th>Color</th>
                            <th>Grade</th>
                            <th>Gauge</th>
                            <th>Profile</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Total Ft</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($tx = mysqli_fetch_assoc($res_tx)): ?>
                            <?php
                            $trans_date = date('m/d/Y', strtotime($tx['date']));
                            $before_ft = (float)$tx['length_before_use'];
                            $remain_ft = (float)$tx['remaining_length'];
                            $used_ft = max(0, $before_ft - $remain_ft);
                            $used_wo_list = array_filter(array_map('trim', explode(',', $tx['used_in_workorders'])));
                            if (empty($used_wo_list)) continue;
                            $id_list = implode(',', array_map('intval', $used_wo_list));

                            $sql_wo = "
                                SELECT
                                    wo.id AS wo_id,
                                    wo.work_order_id AS invoice_no,
                                    wo.work_order_product_id AS line_id,
                                    wo.quantity AS wo_quantity,
                                    wo.custom_length AS wo_length_ft,
                                    wo.submitted_date AS wo_date,
                                    op.product_item AS product_item,
                                    op.product_id_abbrev AS product_id_abbrev,
                                    op.custom_color AS op_custom_color,
                                    op.custom_grade AS op_custom_grade,
                                    op.custom_gauge AS op_custom_gauge,
                                    op.custom_profile AS op_custom_profile
                                FROM work_order wo
                                LEFT JOIN order_product op ON op.id = wo.work_order_product_id
                                WHERE wo.id IN ($id_list)
                                ORDER BY op.productid, wo.id
                            ";
                            $res_wo = mysqli_query($conn, $sql_wo);
                            ?>
                            <?php if ($res_wo && mysqli_num_rows($res_wo) > 0): ?>
                                <?php while ($wo = mysqli_fetch_assoc($res_wo)): ?>
                                    <?php
                                    $qty = (int)$wo['wo_quantity'];
                                    $length_ft = (float)$wo['wo_length_ft'];
                                    $line_total = $qty * $length_ft;
                                    ?>
                                    <tr>
                                        <td><?= $trans_date ?></td>
                                        <td>
                                            <a href="javascript:void(0)" class="text-primary view_invoice_details" data-orderid="<?= $wo['invoice_no'] ?>">
                                                INV#<?= htmlspecialchars($wo['invoice_no']) ?>
                                            </a>
                                        </td>
                                        <td>L<?= htmlspecialchars($wo['line_id']) ?></td>
                                        <td><?= htmlspecialchars($wo['product_id_abbrev'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($wo['product_item'] ?? '-') ?></td>
                                        <td><?= number_format($before_ft, 2) ?></td>
                                        <td><?= number_format($used_ft, 2) ?></td>
                                        <td><?= number_format($remain_ft, 2) ?></td>
                                        <td><?= htmlspecialchars(getColorName($wo['op_custom_color']) ?? '-') ?></td>
                                        <td><?= htmlspecialchars(getGradeName($wo['op_custom_grade']) ?? '-') ?></td>
                                        <td><?= htmlspecialchars(getGaugeName($wo['op_custom_gauge']) ?? '-') ?></td>
                                        <td><?= htmlspecialchars(getProfileTypeName($wo['op_custom_profile']) ?? '-') ?></td>
                                        <td class="text-end"><?= number_format($qty) ?></td>
                                        <td class="text-end"><?= number_format($line_total, 2) ?></td>
                                        <td class="text-nowrap">
                                            <a href="javascript:void(0)" class="me-1 text-decoration-none view_invoice_details" title="View" data-orderid="<?= $wo['invoice_no'] ?>">
                                                <i class="fa-solid fa-eye text-primary"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="me-1 text-decoration-none" title="Print" data-wo="<?= (int)$wo['wo_id'] ?>">
                                                <i class="fa-solid fa-print text-info"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="text-decoration-none" title="Download" data-wo="<?= (int)$wo['wo_id'] ?>">
                                                <i class="fa-solid fa-download text-success"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>

    <script>
    $(function() {
        $('#coilTransactions').DataTable({
            searching: false,
            ordering: true,
            order: [],
            pageLength: 100
        });
    });
    </script>

<?php
}

if (isset($_POST['search_defective'])) {
    $coil_id = mysqli_real_escape_string($conn, $_POST['coilid']);
    $date_from = !empty($_POST['date_from']) ? $_POST['date_from'] . ' 00:00:00' : null;
    $date_to = !empty($_POST['date_to']) ? $_POST['date_to'] . ' 23:59:59' : null;

    $date_filter = "";
    if ($date_from && $date_to) {
        $date_filter = "AND changed_at BETWEEN '$date_from' AND '$date_to'";
    } elseif ($date_from) {
        $date_filter = "AND changed_at >= '$date_from'";
    } elseif ($date_to) {
        $date_filter = "AND changed_at <= '$date_to'";
    }

    $sql_def = "
        SELECT history_id, coil_defective_id, action_type, change_text, note, changed_by, changed_at
        FROM coil_defective_history
        WHERE coil_id = '$coil_id' $date_filter
        ORDER BY changed_at DESC
    ";
    $res_def = mysqli_query($conn, $sql_def);

    if (!$res_def || mysqli_num_rows($res_def) == 0) {
        echo '<div class="alert alert-warning">No defective history found for this coil in the selected date range.</div>';
        exit;
    }
    ?>

    <div class="card card-body">
        <div class="datatables">
            <div class="table-responsive">
            <table id="coilDefects" class="table align-middle text-center text-wrap">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Change</th>
                        <th>Note</th>
                        <th>Changed By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($d = mysqli_fetch_assoc($res_def)): ?>
                        <tr>
                            <td><?= date('m/d/Y', strtotime($d['changed_at'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($d['change_text'] ?? '-')) ?></td>
                            <td><?= nl2br(htmlspecialchars($d['note'] ?? '-')) ?></td>
                            <td><?= htmlspecialchars(get_staff_name($d['changed_by'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    $(function() {
        $('#coilDefects').DataTable({
            searching: false,
            ordering: true,
            order: [],
            pageLength: 100
        });
    });
    </script>

<?php
}
?>









