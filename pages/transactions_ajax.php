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

$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');
$where      = " AND DATE(date) BETWEEN '$start_date' AND '$end_date'";

function getCashFlowRows() {
    global $conn, $where;

    $query = "
        SELECT *
        FROM cash_flow
        WHERE movement_type IN ('cash_inflow','cash_outflow')
        $where
        ORDER BY date DESC
    ";

    $result = mysqli_query($conn, $query);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $orderid = $row['orderid'];
        $order = getOrderDetails($orderid);
        $customer_id = $order['customerid'] ?? null;

        $rows[] = [
            'orderid'        => $orderid,
            'customer_name'  => get_customer_name($customer_id),
            'date_display'   => date('m/d/Y', strtotime($row['date'])),
            'movement_type'  => ucwords(str_replace('_', ' ', $row['movement_type'])),
            'cash_flow_type' => ucwords(str_replace('_', ' ', $row['cash_flow_type'])),
            'amount'         => floatval($row['amount'])
        ];
    }

    return $rows;
}

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action === 'fetch_table') {
        $rows = getCashFlowRows();

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                'orderid'        => $r['orderid'],
                'customer_name'  => $r['customer_name'],
                'date_display'   => $r['date_display'],
                'movement_type'  => $r['movement_type'],
                'cash_flow_type' => $r['cash_flow_type'],
                'amount_display' => '$' . number_format($r['amount'], 2),
                'amount'         => $r['amount']
            ];
        }

        echo json_encode(['data' => $data]);
        exit;
    }

    if ($action === 'download_excel') {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Cash_Flow.xls");

        $rows = getCashFlowRows();

        echo "<table border='1'>";
        echo "<thead>
            <tr style='font-weight:bold; background:#f0f0f0;'>
                <th>Invoice #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Movement Type</th>
                <th>Cash Flow Type</th>
                <th>Amount</th>
            </tr>
        </thead><tbody>";

        foreach ($rows as $r) {
            echo "<tr>
                <td align='center'>{$r['orderid']}</td>
                <td>{$r['customer_name']}</td>
                <td align='center'>{$r['date_display']}</td>
                <td align='center'>{$r['movement_type']}</td>
                <td align='center'>{$r['cash_flow_type']}</td>
                <td align='right'>$" . number_format($r['amount'], 2) . "</td>
            </tr>";
        }

        echo "</tbody></table>";
        exit;
    }

    if ($action === 'download_pdf') {
        require '../includes/fpdf/fpdf.php';

        $rows = getCashFlowRows();

        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Transactions Report', 0, 1);

        $pdf->SetFont('Arial', 'B', 9);
        $widths = [25, 45, 25, 35, 35, 25];

        $headers = ['Invoice #','Customer','Date','Movement Type','Cash Flow Type','Amount'];
        foreach ($headers as $i => $h) {
            $pdf->Cell($widths[$i], 7, $h, 1, 0, 'C');
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);

        foreach ($rows as $r) {
            $pdf->Cell($widths[0], 6, $r['orderid'], 1, 0, 'C');
            $pdf->Cell($widths[1], 6, $r['customer_name'], 1);
            $pdf->Cell($widths[2], 6, $r['date_display'], 1, 0, 'C');
            $pdf->Cell($widths[3], 6, $r['movement_type'], 1, 0, 'C');
            $pdf->Cell($widths[4], 6, $r['cash_flow_type'], 1, 0, 'C');
            $pdf->Cell($widths[5], 6, '$' . number_format($r['amount'], 2), 1, 0, 'R');
            $pdf->Ln();
        }

        $pdf->Output('I', 'cash_flow.pdf');
        exit;
    }

    if ($action === 'print_result') {
        $rows = getCashFlowRows();
        ?>
        <html>
        <head>
            <title>Transactions Print</title>
            <style>
                table { width:100%; border-collapse:collapse; }
                th, td { border:1px solid #000; padding:5px; }
                th { background:#f0f0f0; }
            </style>
        </head>
        <body onload="window.print()">
            <h3>Transactions Report</h3>
            <table>
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Movement Type</th>
                        <th>Cash Flow Type</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td align="center"><?= $r['orderid'] ?></td>
                        <td><?= $r['customer_name'] ?></td>
                        <td align="center"><?= $r['date_display'] ?></td>
                        <td align="center"><?= $r['movement_type'] ?></td>
                        <td align="center"><?= $r['cash_flow_type'] ?></td>
                        <td align="right">$<?= number_format($r['amount'],2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
        exit;
    }


    mysqli_close($conn);
}
?>
