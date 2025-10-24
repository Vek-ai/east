<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

$emailSender = new EmailTemplates();

if (isset($_POST['search_ledger'])) {
    $coil_id = mysqli_real_escape_string($conn, $_POST['coilid']);

    $sql_tx = "
        SELECT id, coilid, date, remaining_length, length_before_use, used_in_workorders
        FROM coil_transaction
        WHERE coilid = '$coil_id'
    ";
    $res_tx = mysqli_query($conn, $sql_tx);

    $sql_def = "
        SELECT history_id, coil_defective_id, action_type, change_text, note, changed_by, changed_at
        FROM coil_defective_history
        WHERE coil_id = '$coil_id'
    ";
    $res_def = mysqli_query($conn, $sql_def);

    if ((!$res_tx || mysqli_num_rows($res_tx) == 0) && (!$res_def || mysqli_num_rows($res_def) == 0)) {
        echo '<div class="alert alert-warning">No records found for this coil ' . $coil_id . '.</div>';
        exit;
    }

    $timeline = [];

    if ($res_tx) {
        while ($t = mysqli_fetch_assoc($res_tx)) {
            $t['type'] = 'transaction';
            $t['datetime'] = $t['date'];
            $timeline[] = $t;
        }
    }

    if ($res_def) {
        while ($d = mysqli_fetch_assoc($res_def)) {
            $d['type'] = 'defective';
            $d['datetime'] = $d['changed_at'];
            $timeline[] = $d;
        }
    }

    usort($timeline, function ($a, $b) {
        return strtotime($b['datetime']) - strtotime($a['datetime']);
    });
    ?>

    <div class="card card-body">
        <?php foreach ($timeline as $row): ?>
            <?php if ($row['type'] === 'transaction'): ?>
                <?php
                $trans_id = (int)$row['id'];
                $trans_date = date('m/d/Y g:i A', strtotime($row['date']));
                $before_ft = (float)$row['length_before_use'];
                $remain_ft = (float)$row['remaining_length'];
                $used_ft = max(0, $before_ft - $remain_ft);
                $used_wo_list = array_filter(array_map('trim', explode(',', $row['used_in_workorders'])));
                ?>
                <div class="mb-3 border-start border-4 border-success rounded p-2 ps-3" id="transaction-block-<?= $trans_id ?>">
                    <div class="fw-bold mb-1 text-success">Coil Processed — <?= $trans_date ?></div>
                    <table class="table table-bordered table-sm text-center mb-2">
                        <thead>
                            <tr>
                                <th>Initial (Ft)</th>
                                <th>Used (Ft)</th>
                                <th>Remaining (Ft)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= number_format($before_ft, 2) ?></td>
                                <td><?= number_format($used_ft, 2) ?></td>
                                <td><?= number_format($remain_ft, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <?php if (!empty($used_wo_list)): ?>
                        <?php
                        $id_list = implode(',', array_map('intval', $used_wo_list));
                        $sql_wo = "
                            SELECT
                                wo.id AS wo_id,
                                wo.work_order_id AS invoice_no,
                                wo.work_order_product_id AS line_id,
                                wo.quantity AS wo_quantity,
                                wo.custom_length AS wo_length_ft,
                                wo.custom_length2 AS wo_length_in,
                                wo.submitted_date AS wo_date,
                                op.productid AS product_id,
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
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle text-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice #</th>
                                            <th>Line Item</th>
                                            <th>Product ID</th>
                                            <th>Description</th>
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
                                        <?php while ($wo = mysqli_fetch_assoc($res_wo)): ?>
                                            <?php
                                            $qty = (int)$wo['wo_quantity'];
                                            $length_ft = (float)$wo['wo_length_ft'];
                                            $line_total = $qty * $length_ft;
                                            ?>
                                            <tr>
                                                <td><?= date('m/d/Y', strtotime($wo['wo_date'])) ?></td>
                                                <td>
                                                    <a href="javascript:void(0)" class="text-primary view_invoice_details" data-orderid="<?= $wo['invoice_no'] ?>">
                                                        INV#<?= htmlspecialchars($wo['invoice_no']) ?>
                                                    </a>
                                                </td>
                                                <td>L<?= htmlspecialchars($wo['line_id']) ?></td>
                                                <td><?= htmlspecialchars($wo['product_id_abbrev'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($wo['product_item'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars(getColorName($wo['op_custom_color']) ?? '-') ?></td>
                                                <td><?= htmlspecialchars(getGradeName($wo['op_custom_grade']) ?? '-') ?></td>
                                                <td><?= htmlspecialchars(getGaugeName($wo['op_custom_gauge']) ?? '-') ?></td>
                                                <td><?= htmlspecialchars(getProfileTypeName($wo['op_custom_profile']) ?? '-') ?></td>
                                                <td class="text-end"><?= number_format($qty) ?></td>
                                                <td class="text-end"><?= number_format($line_total, 2) ?></td>
                                                <td>
                                                    <a href="javascript:void(0)" class="me-1 text-decoration-none view_invoice_details" data-orderid="<?= $wo['invoice_no'] ?>" title="View Invoice"> 
                                                        <i class="fa fa-eye text-primary"></i> 
                                                    </a>
                                                    <a href="javascript:void(0)" class="me-1 text-decoration-none" title="Print">
                                                        <i class="fa-solid fa-print text-info"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" class="text-decoration-none" title="Download">
                                                        <i class="fa-solid fa-download text-success"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            <?php elseif ($row['type'] === 'defective'): ?>
                <div class="mb-3 border-start border-4 border-danger rounded p-2 ps-3">
                    <div class="fw-bold text-danger mb-1">Defective — <?= date('m/d/Y g:i A', strtotime($row['changed_at'])) ?></div>
                    <table class="table table-bordered table-sm text-center mb-0">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Note</th>
                                <th>Changed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= nl2br(htmlspecialchars($row['change_text'] ?? '-')) ?></td>
                                <td><?= nl2br(htmlspecialchars($row['note'] ?? '-')) ?></td>
                                <td><?= htmlspecialchars(get_staff_name($row['changed_by'])) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php
}
?>







