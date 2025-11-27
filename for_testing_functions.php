<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

// Run update if button pressed
if (isset($_POST['update_inventory'])) {

    $productRes = mysqli_query($conn, "
        SELECT 
            product_id, 
            product_line, 
            product_type, 
            grade, 
            gauge, 
            available_lengths, 
            color
        FROM product
        WHERE status = 1 AND hidden = 0
    ");

    $insertCount = 0;

    while ($p = mysqli_fetch_assoc($productRes)) {
        $lines   = json_decode($p['product_line'], true) ?: [$p['product_line']];
        $types   = json_decode($p['product_type'], true) ?: [$p['product_type']];
        $grades  = json_decode($p['grade'], true) ?: [$p['grade']];
        $gauges  = json_decode($p['gauge'], true) ?: [$p['gauge']];
        $lengths = json_decode($p['available_lengths'], true) ?: [$p['available_lengths']];
        $colors  = json_decode($p['color'], true) ?: [$p['color']];

        $combinations = array_combinations([
            'product_line'     => $lines,
            'product_type'     => $types,
            'grade'            => $grades,
            'gauge'            => $gauges,
            'dimension_id'     => $lengths,
            'color_id'         => $colors
        ]);

        foreach ($combinations as $combo) {
            $product_id = intval($p['product_id']);
            $line       = intval($combo['product_line']);
            $type       = intval($combo['product_type']);
            $grade      = intval($combo['grade']);
            $gauge      = intval($combo['gauge']);
            $dim        = intval($combo['dimension_id']);
            $color      = $combo['color_id'] !== null ? intval($combo['color_id']) : "NULL";

            // Check for existing inventory row
            $checkSql = "SELECT 1 FROM inventory 
                         WHERE Product_id = $product_id 
                           AND product_line = $line 
                           AND product_type = $type 
                           AND grade = $grade 
                           AND gauge = $gauge 
                           AND dimension_id = $dim 
                           AND color_id " . ($color === "NULL" ? "IS NULL" : "= $color") . "
                         LIMIT 1";
            $exists = $conn->query($checkSql)->num_rows > 0;

            if (!$exists) {
                $insertSql = "INSERT INTO inventory 
                              (Product_id, product_line, product_type, grade, gauge, color_id, dimension_id) 
                              VALUES ($product_id, $line, $type, $grade, $gauge, $color, $dim)";
                if ($conn->query($insertSql)) $insertCount++;
            }
        }
    }

    echo "<p>Inventory update complete. $insertCount new combinations added.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Inventory Combinations</title>
</head>
<body>
<h2>Update Missing Inventory Combinations</h2>
<form method="post">
    <button type="submit" name="update_inventory">Run Update</button>
</form>
</body>
</html>
