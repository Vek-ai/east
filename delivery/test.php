<?php
// Include the phpqrcode library
include('qrlib.php');

// Get the current domain dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$domain = $protocol . $_SERVER['HTTP_HOST'];

// URL to be encoded in the QR code
$website = $domain . "/updatedelivery.php?id=" . urlencode($_GET['prod']);

// Message to display when the QR code is scanned
$message = "Your Everyday Inspiration";

// You can format the data to include both message and URL
$data = json_encode([
    'message' => $message,
    'website' => $website
]);

$file = __DIR__ . '/deliveryqr/qrcode' . $_GET['prod'] . '.png';
$size = 20;

// Generate the QR code and save it as a PNG file
QRcode::png($website, $file, QR_ECLEVEL_L, $size);

// Output the QR code directly to the browser
header('Content-Type: image/png');
QRcode::png($website, false, QR_ECLEVEL_L, $size);
?>
