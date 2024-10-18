<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['merge'])) {
    $customer_original = floatval($_POST['customer_original']);
    $customer_merge = floatval($_POST['customer_merge']);

    $query = "UPDATE customer SET status = 3, merge_from = '$customer_original' WHERE customer_id = '$customer_merge'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $query = "UPDATE orders SET customerid = '$customer_original' WHERE customerid = '$customer_merge'";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            echo "Error updating orders: " . mysqli_error($conn);
            exit;
        }

        $query = "UPDATE estimates SET customerid = '$customer_original' WHERE customerid = '$customer_merge'";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            echo "Error updating estimates: " . mysqli_error($conn);
            exit;
        }
        
        echo "success";
    } else {
        echo "Database error: " . mysqli_error($conn);
    }
}
?>
