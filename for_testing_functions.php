<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

$assignedBy = $_SESSION['userid'] ?? 1;

$productQuery = "SELECT product_id, color FROM product";
$productResult = mysqli_query($conn, $productQuery);

while ($product = mysqli_fetch_assoc($productResult)) {
    $productId = intval($product['product_id']);
    
    $productColors = json_decode($product['color'], true) ?: [];
    $productColors = array_map('intval', $productColors);
    
    $assignedColorsQuery = "SELECT color_id FROM product_color_assign WHERE product_id = $productId";
    $assignedColorsResult = mysqli_query($conn, $assignedColorsQuery);
    
    $assignedColors = [];
    while ($row = mysqli_fetch_assoc($assignedColorsResult)) {
        $assignedColors[] = intval($row['color_id']);
    }
    
    $allColors = array_unique(array_merge($productColors, $assignedColors));
    
    if (!empty($assignedColors)) {
        $toDelete = array_diff($assignedColors, $allColors);
        if (!empty($toDelete)) {
            $idsStr = implode(',', $toDelete);
            $deleteQuery = "DELETE FROM product_color_assign 
                            WHERE product_id = $productId AND color_id IN ($idsStr)";
            mysqli_query($conn, $deleteQuery);
        }
    }

    $toInsert = array_diff($allColors, $assignedColors);
    foreach ($toInsert as $colorId) {
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $colorId = intval($colorId);
        $insertQuery = "INSERT INTO product_color_assign 
                        (product_id, color_id, `date`, `time`, assigned_by)
                        VALUES ($productId, $colorId, '$date', '$time', $assignedBy)";
        mysqli_query($conn, $insertQuery);
    }

    $colorsJson = json_encode($allColors);
    mysqli_query($conn, "UPDATE product SET color = '$colorsJson' WHERE product_id = $productId");
}

echo "Product colors synchronization completed successfully.";

?>
