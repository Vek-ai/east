<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/phpmailer/vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

function generateRandomUPC() {
  $upc_code = '';
  
  // Generate the first 11 digits randomly
  for ($i = 0; $i < 11; $i++) {
      $upc_code .= mt_rand(0, 9);
  }

  // Calculate the 12th digit (check digit)
  $odd_sum = 0;
  $even_sum = 0;

  for ($i = 0; $i < 11; $i++) {
      if ($i % 2 === 0) {
          $odd_sum += $upc_code[$i];
      } else {
          $even_sum += $upc_code[$i];
      }
  }

  $total_sum = (3 * $odd_sum) + $even_sum;
  $check_digit = (10 - ($total_sum % 10)) % 10;

  $upc_code .= $check_digit;

  return $upc_code;
}

$generator = new BarcodeGeneratorPNG();
$upc_code = generateRandomUPC();

// Generate the barcode
$barcode = $generator->getBarcode($upc_code, $generator::TYPE_UPC_A);

// Define the file path where the barcode will be saved
$file_path = "images/barcode/$upc_code.png";

// Save the barcode to the file
file_put_contents("../$file_path", $barcode);

// Return the path to the generated image and the UPC code
echo json_encode(['file_path' => $file_path, 'upc_code' => $upc_code]);
?>