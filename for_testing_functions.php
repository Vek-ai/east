<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

$quantity = 1;
$lengthFeet = 10;
$lengthInch = 10;
$panelType = '';
$soldByFeet = 0;
$bends = 10;
$hems = 10;
$basePrice = 1;

$totalPrice = calculateUnitPrice($basePrice, $lengthFeet, $lengthInch, $panelType, $soldByFeet, $bends, $hems);

echo number_format($totalPrice, 2);

?>
