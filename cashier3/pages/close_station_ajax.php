<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if (isset($_POST['close_station'])) {
    header('Content-Type: application/json');

    $station_id = intval($_SESSION['station']);
    $cashier_id = intval($_SESSION['userid']);
    $today = date('Y-m-d');

    $opening = 0;
    $ob = mysqli_query($conn, "SELECT amount FROM cash_flow WHERE movement_type='opening_balance' AND DATE(date)='$today' AND station_id=$station_id LIMIT 1");
    if ($ob && mysqli_num_rows($ob)) {
        $row = mysqli_fetch_assoc($ob);
        $opening = floatval($row['amount']);
    }

    $inflows = [];
    $total_inflows = 0;
    $ci = mysqli_query($conn, "SELECT cash_flow_type, SUM(amount) as total FROM cash_flow WHERE movement_type='cash_inflow' AND DATE(date)='$today' AND station_id=$station_id GROUP BY cash_flow_type");
    while ($row = mysqli_fetch_assoc($ci)) {
        $inflows[$row['cash_flow_type']] = floatval($row['total']);
        $total_inflows += floatval($row['total']);
    }

    $outflows = [];
    $total_outflows = 0;
    $co = mysqli_query($conn, "SELECT cash_flow_type, SUM(amount) as total FROM cash_flow WHERE movement_type='cash_outflow' AND DATE(date)='$today' AND station_id=$station_id GROUP BY cash_flow_type");
    while ($row = mysqli_fetch_assoc($co)) {
        $outflows[$row['cash_flow_type']] = floatval($row['total']);
        $total_outflows += floatval($row['total']);
    }

    $closing_balance = $opening + $total_inflows - $total_outflows;

    $details = [
        'opening' => $opening,
        'inflows' => $inflows,
        'total_inflows' => $total_inflows,
        'outflows' => $outflows,
        'total_outflows' => $total_outflows,
        'closing_balance' => $closing_balance
    ];
    $details_json = mysqli_real_escape_string($conn, json_encode($details));

    $sql = "INSERT INTO cash_flow_summary (station_id, cashier_id, closing_date, opening_balance, total_inflows, total_outflows, closing_balance, details_json) 
            VALUES ($station_id, $cashier_id, '$today', $opening, $total_inflows, $total_outflows, $closing_balance, '$details_json')";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
}