<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

$result = $conn->query("SELECT orderid FROM orders");

while ($row = $result->fetch_assoc()) {
    $orderId = $row['orderid'];
    
    $token = bin2hex(random_bytes(8));
    
    $stmt = $conn->prepare("UPDATE orders SET token = ? WHERE orderid = ?");
    $stmt->bind_param("si", $token, $orderId);
    $stmt->execute();
}

echo "Tokens generated for all orders.";
?>
