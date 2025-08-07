<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['save_computation'])) {
    $coil_id = mysqli_real_escape_string($conn, $_POST['coil_id']);
    $length = floatval($_POST['length']);
    $quantity = floatval($_POST['quantity']);

    $coil_length = 0;
    $query_coil = "SELECT * FROM coil WHERE coil_id = '$coil_id'";
    $result_coil = mysqli_query($conn, $query_coil);

    if (mysqli_num_rows($result_coil) > 0) {
        $row_coil = mysqli_fetch_assoc($result_coil);
        $coil_length = $row_coil['length'];

        if ($length > 0 && $quantity > 0) {
            $computedLength = $length * $quantity;
            $remainingLength = $coil_length - $computedLength;

            if ($remainingLength < 0) {
                echo "Formula results in a negative remaining length!";
            } else {
                $totalLength = number_format($remainingLength, 2);

                $insert_query = "
                INSERT INTO flat_sheet (
                    grade,
                    Color,
                    Width,
                    Length,
                    thickness,
                    materialgrade,
                    steelcoating,
                    gauge,
                    backercolor,
                    weight,
                    paintcode,
                    supplier,
                    paintdefect,
                    milltag,
                    Invoice,
                    coil_id,
                    quantity_made,
                    quantity_remaining
                ) VALUES (
                    '" . $row_coil['grade'] . "',
                    '" . $row_coil['color'] . "',
                    '" . $row_coil['width'] . "',
                    '" . $length . "',
                    '" . $row_coil['thickness'] . "',
                    '" . $row_coil['material_grade'] . "',
                    '" . $row_coil['steel_coating'] . "',
                    '" . $row_coil['gauge'] . "',
                    '" . $row_coil['backer_color'] . "',
                    '" . $row_coil['weight'] . "',
                    '" . $row_coil['paint_code'] . "',
                    '" . $row_coil['supplier'] . "',
                    '',
                    '',
                    '" . $row_coil['invoice'] . "',
                    '" . $coil_id . "',
                    '" . $quantity . "',
                    '" . $remainingLength . "'
                )";

                $result_insert = mysqli_query($conn, $insert_query);

                if ($result_insert) {
                    echo "success";
                } else {
                    echo "Database error: " . mysqli_error($conn);
                }
            }
        } else {
            echo "Invalid length or quantity.";
        }
    } else {
        echo "Coil not found.";
    }
}
?>
