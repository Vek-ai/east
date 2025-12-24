<?php
require_once '../includes/dbconn.php';
require_once '../includes/functions.php';

$order_estimate_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order_estimate = getOrderEstimateDetails($order_estimate_id);
$invoice_no = $order_estimate['order_estimate_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $latitude       = $_POST['latitude'] ?? null;
    $longitude      = $_POST['longitude'] ?? null;
    $photoAddress   = $_POST['photo_address'] ?? '';
    $amount         = $_POST['amount'] ?? 0;
    $amount         = ($amount === '' || $amount === null) ? 0 : floatval($amount);
    $datetime       = date('Y-m-d H:i:s');

    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {

        $uploadDir = 'deliverypictures/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileTmpPath  = $_FILES['picture']['tmp_name'];
        $fileExt      = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
        $newFileName  = 'order_' . $order_estimate_id . '.' . $fileExt;
        $uploadPath   = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $uploadPath)) {

            $sql = "
                UPDATE order_estimate
                SET status = 3,
                    image_url = ?,
                    latitude = ?,
                    longitude = ?,
                    datetime = ?,
                    photo_address = ?,
                    amount = ?
                WHERE id = ?
            ";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $newFileName, $latitude, $longitude, $datetime, $photoAddress, $amount, $order_estimate_id);

            if ($stmt->execute()) {

                $sqlOrderProduct = "
                    UPDATE order_product
                    SET status = 4
                    WHERE orderid = ?
                ";

                $stmtOP = $conn->prepare($sqlOrderProduct);
                $stmtOP->bind_param("i", $invoice_no);
                $stmtOP->execute();
                $stmtOP->close();

                $received_by     = '';
                $station_id      = '';
                $movement_type   = 'cash_inflow';
                $payment_method  = 'cash';
                $cash_flow_type  = 'delivery_payment';
                $orderid         = $invoice_no;
                $date            = date('Y-m-d H:i:s');

                $sqlCash = "
                    INSERT INTO cash_flow
                    (orderid, movement_type, payment_method, date, received_by, station_id, cash_flow_type, amount)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ";

                $stmtCash = $conn->prepare($sqlCash);
                $stmtCash->bind_param("issssssd", $orderid, $movement_type, $payment_method, $date, $received_by, $station_id, $cash_flow_type, $amount);
                $stmtCash->execute();
                $stmtCash->close();

                $successMessage = "Delivery recorded successfully!";
            } else {
                $errorMessage = "DB Update Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $errorMessage = "File upload failed.";
        }
    } else {
        $errorMessage = "No picture uploaded.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Upload Delivery Proof</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body, html { height: 100%; margin: 0; padding: 0; }
.upload-card { width: 100%; max-width: 500px; }
@media (max-width: 576px) {
    .upload-card { width: 100vw; height: 100vh; border-radius: 0; margin: 0; }
    .card-body { overflow-y: auto; height: calc(100vh - 60px); }
    .card-header h5 { font-size: 1rem; }
    .btn { font-size: 0.95rem; }
}
</style>
</head>
<body>
<div class="container-fluid d-flex justify-content-center align-items-center min-vh-100">
    <div class="col-12 col-sm-10 col-md-6 col-lg-4 upload-card">
        <div class="card shadow-lg h-100">
            <div class="card-header bg-primary text-white text-center">
                <h5>Delivery Proof Upload</h5>
                <small>Order #<?= htmlspecialchars($invoice_no) ?></small>
            </div>
            <div class="card-body">
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success"><?= $successMessage ?></div>
                <?php endif; ?>
                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger"><?= $errorMessage ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" onsubmit="return getLocation(event)">
                    <div class="mb-3">
                        <label class="form-label">Delivery Amount</label>
                        <input type="number" step="0.001" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Picture</label>
                        <input type="file" name="picture" class="form-control" accept="image/*" capture="camera" required>
                    </div>
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="photo_address" id="photo_address">
                    <button class="btn btn-success w-100" type="submit">Submit Delivery</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
var latitude;
var longitude;

function getLocation() {
    if (!navigator.geolocation) {
        alert("Geolocation not supported");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        pos => {
            latitude = pos.coords.latitude;
            longitude = pos.coords.longitude;

            console.log(latitude);
            console.log(longitude);

            document.getElementById('latitude').value = latitude;
            document.getElementById('longitude').value = longitude;

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('photo_address').value = data.display_name || '';
                    document.querySelector('form').submit();
                })
                .catch(() => {
                    document.querySelector('form').submit();
                });
        }
    );
}

getLocation();
</script>
</body>
</html>
