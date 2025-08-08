<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['save_computation'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $coil_id = mysqli_real_escape_string($conn, $_POST['coil_id']);

    $product_length = 0;
    $query_product = "SELECT * FROM product WHERE product_id = '$product_id'";
    $result_product = mysqli_query($conn, $query_product);
    while ($row_product = mysqli_fetch_array($result_product)) {
        $product_length = $row_product['length'];
    }

    $coil_length = 0;
    $query_coil = "SELECT * FROM coil WHERE coil_id = '$coil_id'";
    $result_coil = mysqli_query($conn, $query_coil);
    while ($row_coil = mysqli_fetch_array($result_coil)) {
        $coil_length = $row_coil['length'];
    }

    if ($product_length > 0) {
        $computed_length = $coil_length / $product_length;
        $whole_number_length = floor($computed_length);
        $decimal_part = $computed_length - $whole_number_length;
        $decimal_part = number_format($decimal_part, 2);
        $sql_insert_manufacture = "INSERT INTO coil_process (coilid, productid, quantity) 
                                   VALUES ('$coil_id', '$product_id', '$whole_number_length')";
        if (mysqli_query($conn, $sql_insert_manufacture)) {
            echo "Coil manufacture inserted successfully.\n";
        } else {
            echo "Error inserting coil manufacture: " . mysqli_error($conn) . "\n";
        }

        $remaining_length = $coil_length - $whole_number_length;

        $sql_insert_transaction = "INSERT INTO coil_transaction (coilid, remaining_length) 
                                   VALUES ('$coil_id', '$remaining_length')";
        if (mysqli_query($conn, $sql_insert_transaction)) {
            echo "Coil transaction inserted successfully.\n";
        } else {
            echo "Error inserting coil transaction: " . mysqli_error($conn) . "\n";
        }

        $sql_update_coil_final = "UPDATE coil SET length = '$remaining_length' WHERE coil_id = '$coil_id'";
        if (mysqli_query($conn, $sql_update_coil_final)) {
            echo "Coil length updated successfully.\n";
        } else {
            echo "Error updating coil length: " . mysqli_error($conn) ;
        }
    
    } else {
        echo "Error: Product length is zero or invalid.";
    }
    
}
