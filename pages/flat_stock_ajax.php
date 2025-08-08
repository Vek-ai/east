<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['save_flat_stock'])) {
    $color = floatval($_POST['color']);
    $width = floatval($_POST['width']);
    $length = floatval($_POST['length']);
    $quantity = floatval($_POST['quantity']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    $insert_query = "
    INSERT INTO flat_stock (
        color,
        width,
        length,
        quantity,
        notes
    ) VALUES (
        '$color',
        '$width',
        '$length',
        '$quantity',
        '$notes'
    )";

    $result_insert = mysqli_query($conn, $insert_query);

    if ($result_insert) {
        echo "success";
    } else {
        echo "Database error: " . mysqli_error($conn);
    }
}
?>
