<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

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

            $job_id = $row['job_id'];
            $job_details = getJobDetails($job_id);
            $customer_id = $job_details['customer_id'];
            $customer_name = get_customer_name($customer_id);
            $job_name = $job_details['job_name'];

            ?>
            <div class="container">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Customer</label>
                        <div class="ms-2"><?= $customer_name ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Job Name</label>
                        <div class="ms-2"><?= $job_name ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Initial Deposit</label>
                        <div class="ms-2">$<?= number_format($row['deposit_amount'], 2) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Remaining Amount</label>
                        <div class="ms-2">$<?= number_format($row['deposit_remaining'], 2) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <div class="ms-2"><?= $status_badge ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Payment Type</label>
                        <div class="ms-2"><?= htmlspecialchars($row['type']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Reference No</label>
                        <div class="ms-2"><?= htmlspecialchars($row['reference_no']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Check No</label>
                        <div class="ms-2"><?= htmlspecialchars($check_no) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Deposited By</label>
                        <div class="ms-2"><?= get_staff_name($row['deposited_by']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Created At</label>
                        <div class="ms-2"><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></div>
                    </div>
                </div>
            </div>
            <?php
        } else {
            echo "<div class='text-danger text-center'>Deposit not found.</div>";
        }
        exit;
    }

    if ($action == 'deposit_job') {
        $job_id = intval($_POST['job_id'] ?? 0);
        $deposit_amount = floatval($_POST['deposit_amount'] ?? 0);
        $deposited_by = $_SESSION['userid'];
        $reference_no = trim($_POST['reference_no'] ?? '');
        $payment_method = $_POST['type'] ?? 'cash';
        $check_no = $_POST['check_no'] ?? null;
        $description = mysqli_real_escape_string($conn, $_POST['description'] ?? 'Job deposit');

        $check_query = "SELECT * FROM jobs WHERE job_id = '$job_id'";
        $check_result = mysqli_query($conn, $check_query);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $check_no_sql = $payment_method === 'check' ? "'" . mysqli_real_escape_string($conn, $check_no) . "'" : "NULL";

            $deposit_status = 1;
            if ($payment_method === 'cash' && $deposit_amount > 10000) {
                $deposit_status = 0;
            }

            $insert = "
                INSERT INTO job_ledger (job_id, customer_id, entry_type, amount, payment_method, check_number, reference_no, description, created_by)
                VALUES (
                    '$job_id',
                    '" . mysqli_real_escape_string($conn, $deposited_by) . "',
                    'deposit',
                    '$deposit_amount',
                    '$payment_method',
                    $check_no_sql,
                    '" . mysqli_real_escape_string($conn, $reference_no) . "',
                    '$description',
                    '" . mysqli_real_escape_string($conn, $deposited_by) . "'
                )
            ";

            if (mysqli_query($conn, $insert)) {
                $insert_deposit = "
                    INSERT INTO job_deposits (
                        job_id,
                        deposit_amount,
                        deposit_remaining,
                        deposit_status,
                        deposited_by,
                        reference_no,
                        type,
                        check_no
                    ) VALUES (
                        '$job_id',
                        '$deposit_amount',
                        '$deposit_amount',
                        '$deposit_status',
                        '" . mysqli_real_escape_string($conn, $deposited_by) . "',
                        '" . mysqli_real_escape_string($conn, $reference_no) . "',
                        '$payment_method',
                        $check_no_sql
                    )
                ";
                mysqli_query($conn, $insert_deposit);

                if ($payment_method === 'cash' && $deposit_amount > 10000) {
                    $deposit_id = mysqli_insert_id($conn);

                    $actorId = $_SESSION['userid'];
                    $actor_name = get_staff_name($actorId);
                    $actionType = 'deposit_approval';
                    $targetId = $deposit_id;
                    $targetType = 'Cash Deposit Approval';
                    $message = "$actor_name has requested cash deposit approval";
                    $url = '?page=job_deposit_approval';
                    $recipientIds = getAdminIDs();
                    createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);
                }

                $update = "
                    UPDATE jobs 
                    SET deposit_amount = deposit_amount + $deposit_amount
                    WHERE job_id = '$job_id'
                ";
                $update_result = mysqli_query($conn, $update);

                echo $update_result ? 'success' : 'error_update';
            } else {
                echo 'error_insert';
            }
        } else {
            echo 'job_not_found';
        }
    }

    if($action == 'fetch_deposit_modal'){
        ?>
        <div class="card">
            <div class="card-body">

                <div>
                    <label for="deposited_by" class="form-label">Customer</label>
                    <div class="mb-3">
                        <select class="form-select" id="deposited_by" name="deposited_by" required>
                            <option value="">Select Customer</option>
                            <?php
                            $customers = mysqli_query($conn, "SELECT customer_id, CONCAT(customer_first_name, ' ', customer_last_name) AS name FROM customer WHERE status = 1");
                            while ($row = mysqli_fetch_assoc($customers)) {
                                $customer_id = $row['customer_id'];
                                $customer_name = get_customer_name($row['customer_id']);
                                echo "<option value='$customer_id'>$customer_name</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="d-none job_details">
                    <label for="job_id" class="form-label">Deposit Job</label>
                    <div class="mb-3">
                        <select class="form-select" id="job_id" name="job_id" required>
                            <option value="">Select Job</option>
                            <?php
                            $jobs = mysqli_query($conn, "SELECT job_id, customer_id, job_name FROM jobs WHERE status = 'active'");
                            while ($row = mysqli_fetch_assoc($jobs)) {
                                echo "<option value='{$row['job_id']}' data-customer='{$row['customer_id']}'>{$row['job_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            
                <div class="mb-3 d-none job_details">
                    <label for="type" class="form-label">Deposit Type</label>
                    <select class="form-select" id="deposit_type" name="type" required>
                        <option value="">-- Select Type --</option>
                        <option value="cash">Cash</option>
                        <option value="check">Check</option>
                    </select>
                </div>

                <div id="deposit_details_group" class="d-none">
                    <div class="mb-3">
                        <label for="deposit_amount" class="form-label">Deposit Amount</label>
                        <input type="number" step="0.01" class="form-control" id="deposit_amount" name="deposit_amount" >
                    </div>

                    <div class="mb-3">
                        <label for="reference_no" class="form-label">Reference No</label>
                        <input type="text" class="form-control" id="reference_no" name="reference_no" required>
                    </div>

                    <div class="mb-3 d-none" id="check_no_group">
                        <label for="check_no" class="form-label">Check No</label>
                        <input type="text" class="form-control" id="check_no" name="check_no">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    mysqli_close($conn);
}
?>
