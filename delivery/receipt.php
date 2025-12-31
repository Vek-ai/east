<?php
// Include the phpqrcode library
include_once('qrlib.php');

// URL to be encoded in the QR code
$website = "https://metal.ilearnwebtech.com/print_receipt.php?token=" . urlencode($_GET['prod']);

// Message to display when the QR code is scanned
$message = "Receipt";

// Optionally include message and URL in JSON (not used in QR content here)
$data = json_encode([
    'message' => $message,
    'website' => $website
]);

$file = __DIR__ . '/receiptqr/receiptqr' . $_GET['prod'] . '.png';

// Set the size/scale of the QR code
$size = 20;

// Generate the QR code and save it as a PNG file
QRcode::png($website, $file, QR_ECLEVEL_L, $size);

// Output the QR code directly to the browser
header('Content-Type: image/png');
QRcode::png($website, false, QR_ECLEVEL_L, $size);
?>
