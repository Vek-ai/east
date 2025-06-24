<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/send_email.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_view_modal") {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $query = "
            SELECT 
                j.job_id,
                l.created_at AS date,
                l.description,
                j.job_name,
                l.po_number,
                CASE WHEN l.entry_type = 'usage' THEN l.amount ELSE NULL END AS debit,
                CASE WHEN l.entry_type = 'deposit' THEN l.amount ELSE NULL END AS credit
            FROM jobs j
            INNER JOIN job_ledger l ON l.job_id = j.job_id
            WHERE j.customer_id = '$customer_id'
            ORDER BY l.created_at ASC
        ";

        $result = mysqli_query($conn, $query);
        $balance = 0;

        if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="datatables">
                <div class="product-details table-responsive text-wrap">
                    <h5 class="fw-bold">Ledger Data for <?= get_customer_name($customer_id) ?></h5>
                    <table id="job_details_tbl" class="table table-striped table-md text-wrap">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Job</th>
                                <th>PO Number</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) : 
                                $job_details = getJobDetails($row['job_id']);
                                $debit = $row['debit'] !== null ? floatval($row['debit']) : 0;
                                $credit = $row['credit'] !== null ? floatval($row['credit']) : 0;

                                if ($debit == 0 && $credit == 0) continue;

                                $balance += ($debit - $credit);
                            ?>
                                <tr>
                                    <td><?= date('Y-m-d', strtotime($row['date'])) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td><?= htmlspecialchars($row['job_name']) ?></td>
                                    <td>
                                        <a href="javascript:void(0);" 
                                        class="view-order-details" 
                                        data-job="<?= htmlspecialchars($row['job_name']) ?>" 
                                        data-po="<?= htmlspecialchars($row['po_number']) ?>">
                                            <?= htmlspecialchars($row['po_number']) ?>
                                        </a>
                                    </td>
                                    <td class="text-end"><?= $debit > 0 ? '$' .number_format($debit, 2) : '' ?></td>
                                    <td class="text-end"><?= $credit > 0 ? '$' .number_format($credit, 2) : '' ?></td>
                                    <td class="text-end">$<?= number_format($balance, 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <p class="text-muted">No ledger records found for this customer.</p>
        <?php endif;
    }

    mysqli_close($conn);
}
?>
