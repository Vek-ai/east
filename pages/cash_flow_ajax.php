<?php
session_start();
require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'cash_flow';
$test_table = 'cash_flow_excel';

$permission = $_SESSION['permission'];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == 'fetch_table') {
        $query = "SELECT * FROM cash_flow ORDER BY date DESC";
        $result = mysqli_query($conn, $query);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $dateObj = new DateTime($row['date']);
            $data[] = [
                'id'             => $row['id'],
                'cashier'        => get_staff_name($row['received_by']),
                'station'        => getStationName($row['station_id']),
                'station_id'        => $row['station_id'],
                'payment_method' => ucwords($row['payment_method']),
                'cash_flow_type' => ucwords(str_replace('_', ' ', $row['cash_flow_type'])),
                'date_display'   => $dateObj->format('m/d/Y'),
                'date'           => $dateObj->format('Y-m-d'),
                'month'          => $dateObj->format('n'),
                'year'           => $dateObj->format('Y')
            ];
        }

        echo json_encode(['data' => $data]);
        exit;
    }

    mysqli_close($conn);
}
?>
