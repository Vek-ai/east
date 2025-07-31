<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == 'view_deposit') {
        $deposit_id = intval($_POST['id'] ?? 0);

        $query = "SELECT * FROM job_deposits WHERE deposit_id = $deposit_id LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($row = mysqli_fetch_assoc($result)) {
            $status_map = [
                0 => "<span class='badge bg-warning text-dark'>Pending</span>",
                1 => "<span class='badge bg-info'>Available</span>",
                2 => "<span class='badge bg-secondary'>Used</span>"
            ];

            $status_badge = $status_map[intval($row['deposit_status'])] ?? "<span class='badge bg-dark'>Unknown</span>";
            $check_no = $row['type'] === 'check' ? $row['check_no'] : '-';

            ?>
            <div class="container">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Deposit Amount</label>
                        <div>$<?= number_format($row['deposit_amount'], 2) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <div><?= $status_badge ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Payment Type</label>
                        <div class="form-control text-capitalize"><?= htmlspecialchars($row['type']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Reference No</label>
                        <div><?= htmlspecialchars($row['reference_no']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Check No</label>
                        <div><?= htmlspecialchars($check_no) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Deposited By</label>
                        <div><?= get_staff_name($row['deposited_by']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Created At</label>
                        <div><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></div>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a type="button" class="btnApprove" title="Approve Deposit" data-id="<?= $deposit_id ?>">
                        <iconify-icon icon="solar:like-bold" class="fs-7 text-success"></iconify-icon> Approve
                    </a>
                    <a type="button" class="btnReject" title="Reject Deposit" data-id="<?= $deposit_id ?>">
                        <iconify-icon icon="solar:dislike-bold" class="fs-7 text-danger"></iconify-icon> Reject
                    </a>
                </div>
            </div>
            <?php
        } else {
            echo "<div class='text-danger text-center'>Deposit not found.</div>";
        }
        exit;
    }

    if ($action === 'approve_deposit') {
        $sql = "UPDATE job_deposits SET deposit_status = 1 WHERE deposit_id = $deposit_id";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Deposit approved successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to approve deposit.']);
        }
        exit;
    }

    if ($action === 'reject_deposit') {
        $sql = "UPDATE job_deposits SET deposit_status = 3 WHERE deposit_id = $deposit_id";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Deposit rejected successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to reject deposit.']);
        }
        exit;
    }

    mysqli_close($conn);
}
?>
