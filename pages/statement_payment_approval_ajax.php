<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == 'view_payment_proof') {
        $payment_id = intval($_POST['payment_id'] ?? 0);
        $query = "SELECT screenshots FROM job_payment WHERE payment_id = $payment_id LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($row = mysqli_fetch_assoc($result)) {
            $screenshots = json_decode($row['screenshots'] ?? '[]', true);

            if (!empty($screenshots)) {
                ?>
                <div id="proofCarousel" class="carousel slide no-animation" data-bs-ride="false">
                    <div class="carousel-inner">
                        <?php foreach ($screenshots as $index => $filename): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img 
                                    src="uploads/payment_proofs/<?= htmlspecialchars($filename) ?>" 
                                    class="d-block w-100 preview-click" 
                                    style="max-height:500px;object-fit:contain;cursor: zoom-in;" 
                                    data-src="uploads/payment_proofs/<?= htmlspecialchars($filename) ?>" 
                                    alt="Proof <?= $index + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#proofCarousel" data-bs-slide="prev"
                        style="width: 60px; background-color: rgba(255, 255, 255, 0.2); border: none;">
                        <span class="carousel-control-prev-icon" aria-hidden="true"
                            style="filter: brightness(0) invert(1); width: 2rem; height: 2rem;"></span>
                        <span class="visually-hidden fw-bold text-white">Previous</span>
                    </button>

                    <button class="carousel-control-next" type="button" data-bs-target="#proofCarousel" data-bs-slide="next"
                        style="width: 60px; background-color: rgba(255, 255, 255, 0.2); border: none;">
                        <span class="carousel-control-next-icon" aria-hidden="true"
                            style="filter: brightness(0) invert(1); width: 2rem; height: 2rem;"></span>
                        <span class="visually-hidden fw-bold text-white">Next</span>
                    </button>

                </div>
                <?php
            } else {
                echo "<p class='text-center'>No screenshots found for this payment.</p>";
            }
        } else {
            echo "<p class='text-danger'>Invalid payment ID.</p>";
        }
        exit;
    }

    if ($action == 'approve_payment' || $action == 'reject_payment') {
        $payment_id = intval($_POST['payment_id'] ?? 0);

        if ($payment_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid payment ID']);
            exit;
        }

        $status = $action === 'approve_payment' ? 1 : 2;

        $sql = "UPDATE job_payment SET status = $status WHERE payment_id = $payment_id";
        $res = mysqli_query($conn, $sql);

        if ($res) {
            echo json_encode(['status' => 'success', 'message' => $status === 1 ? 'Payment approved.' : 'Payment rejected.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update status.']);
        }
    }

    mysqli_close($conn);
}
?>
