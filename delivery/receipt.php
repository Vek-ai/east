<?php
// Include the phpqrcode library
include('qrlib.php');

// URL to be encoded in the QR code
$website = "https://metal.ilearnwebtech.com/print_receipt.php?token=".$_GET['prod'];

// Message to display when the QR code is scanned
$message = "Your Everyday Inspiration";

// You can format the data to include both message and URL
$data = json_encode([
    'message' => $message,
    'website' => $website
]);

// Output file path
$file = 'receiptqr/receiptqr'.$_GET['prod'].'.png';

// Set the size/scale of the QR code
$size = 15; // Adjust the size if necessary

// Generate the QR code and save it as a PNG file
QRcode::png($website, $file, QR_ECLEVEL_L, $size);

// Output the QR code directly to the browser
header('Content-Type: image/png');
QRcode::png($website, false, QR_ECLEVEL_L, $size);
?>
