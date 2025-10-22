<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['search_customer'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search_customer']);

    $query = "
        SELECT 
            customer_id AS value, 
            CONCAT(customer_first_name, ' ', customer_last_name) AS label
        FROM 
            customer
        WHERE 
            (customer_first_name LIKE '%$search%' 
            OR 
            customer_last_name LIKE '%$search%')
            AND status != '3'
        LIMIT 15
    ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $response = array();

        $response[] = array(
            'value' => 'all_customers',
            'label' => 'All Customers'
        );

        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }

        echo json_encode($response);
    } else {
        echo json_encode(array('error' => 'Query failed'));
    }
}

if (isset($_POST['search_orders'])) {
    $response = [
        'orders' => [],
        'total_count' => 0,
        'total_amount' => 0,
        'total_paid' => 0,
        'total_balance' => 0,
        'error' => null
    ];

    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);
    $months = array_map('intval', $_POST['month_select'] ?? []);
    $years = array_map('intval', $_POST['year_select'] ?? []);
    $staff = mysqli_real_escape_string($conn, $_POST['staff']);
    $tax_status = mysqli_real_escape_string($conn, $_POST['tax_status']);
    $paid_status = mysqli_real_escape_string($conn, $_POST['paid_status']);

    $query = "
        SELECT 
            o.*, 
            CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name, 
            c.customer_pricing, 
            c.tax_status
        FROM orders AS o
        LEFT JOIN customer AS c ON c.customer_id = o.originalcustomerid
        WHERE o.status != 6
    ";

    if (!empty($customer_name) && $customer_name != 'All Customers') {
        $query .= " AND CONCAT(c.customer_first_name, ' ', c.customer_last_name) LIKE '%$customer_name%' ";
    }

    if (!empty($date_from) && !empty($date_to)) {
        $query .= " AND (o.order_date BETWEEN '$date_from' AND '$date_to 23:59:59')";
    } elseif (!empty($date_from)) {
        $query .= " AND o.order_date >= '$date_from'";
    } elseif (!empty($date_to)) {
        $query .= " AND o.order_date <= '$date_to 23:59:59'";
    }

    if (!empty($months)) {
        $query .= " AND MONTH(o.order_date) IN (" . implode(',', $months) . ")";
    }

    if (!empty($years)) {
        $query .= " AND YEAR(o.order_date) IN (" . implode(',', $years) . ")";
    }

    if (!empty($staff)) {
        $query .= " AND o.cashier = '$staff'";
    }

    if (!empty($tax_status)) {
        $query .= " AND c.tax_status = '$tax_status'";
    }

    $query .= " ORDER BY o.order_date DESC";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orderid = $row['orderid'];
            $payment_method = ucfirst($row['pay_type']);
            $expected_amount = floatval($row['discounted_price']);
            $total_paid = floatval(getOrderTotalPayments($orderid));
            $balance = max(0, $expected_amount - $total_paid);

            $payment_status_str = '';
            if ($total_paid <= 0) {
                $payment_status = 'not_paid';
                $payment_status_str = 'Unpaid';
                $color = 'red';
            } elseif ($total_paid < $expected_amount) {
                $payment_status = 'paid_in_part';
                $payment_status_str = 'Partially Paid';
                $color = 'orange';
            } else {
                $payment_status = 'paid_in_full';
                $payment_status_str = 'Fully Paid';
                $color = 'green';
            }

            if (!empty($paid_status) && $payment_status !== $paid_status) {
                continue;
            }

            $status_html = '<span class="badge" style="background-color:' . $color . ';">' . htmlspecialchars($payment_status_str) . '</span>';

            $response['orders'][] = [
                'orderid' => $orderid,
                'order_date' => $row['order_date'],
                'formatted_date' => date("m/d/Y", strtotime($row['order_date'])),
                'cashier' => get_staff_name($row['cashier']),
                'station' => getStationName($row['station']),
                'customer_name' => $row['customer_name'],
                'customer_pricing' => $row['customer_pricing'],
                'amount' => number_format($expected_amount, 2),
                'paid' => number_format($total_paid, 2),
                'balance' => number_format($balance, 2),
                'status' => $status_html,
                'payment_method' => $payment_method
            ];

            $response['total_amount'] += $expected_amount;
            $response['total_paid'] += $total_paid;
            $response['total_balance'] += $balance;
            $response['total_count']++;
        }
    } else {
        $response['error'] = 'No orders found';
    }

    echo json_encode($response);
    exit;
}

if (isset($_REQUEST['download_excel'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Sales_List.xls");

    $pay_labels = [
        'pickup'   => 'Pay at Pick-up',
        'delivery' => 'Pay at Delivery',
        'cash'     => 'Cash',
        'check'    => 'Check',
        'card'     => 'Credit/Debit Card',
        'net30'    => 'Charge Net 30'
    ];

    echo "<table border='1'>";
    echo "<thead>
            <tr style='font-weight: bold; background-color: #f0f0f0;'>
                <th>Invoice #</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Status</th>
                <th>Payment Method</th>
                <th>Station</th>
                <th>Cashier</th>
            </tr>
        </thead><tbody>";

    $query = "SELECT * FROM orders ORDER BY order_date DESC";
    $result = mysqli_query($conn, $query);

    $total_amount = $total_paid = $total_balance = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $orderid = $row['orderid'];
        $customer_name = get_customer_name($row["customerid"]);
        $station = getStationName($row['station']);
        $cashier = get_staff_name($row['cashier']);
        $expected_amount = floatval($row['discounted_price']);
        $total_paid_amt = floatval(getOrderTotalPayments($orderid));
        $balance = $expected_amount - $total_paid_amt;

        $date = '';
        if (!empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') {
            $date = date("F d, Y", strtotime($row["order_date"]));
        }

        $pay_type = strtolower(trim($row['pay_type']));
        $payment_method = $pay_labels[$pay_type] ?? ucfirst($pay_type);

        // Status colors
        if ($total_paid_amt <= 0) {
            $status = 'Not Paid';
            $bg_color = '#ff4d4d';
        } elseif ($total_paid_amt < $expected_amount) {
            $status = 'Partially Paid';
            $bg_color = '#ffa500';
        } else {
            $status = 'Fully Paid';
            $bg_color = '#4CAF50';
        }

        echo "<tr>
                <td style='text-align: center;'>SO-{$orderid}</td>
                <td style='text-align: center;'>{$date}</td>
                <td style='text-align: center;'>{$customer_name}</td>
                <td style='text-align: right;'>" . number_format($expected_amount, 2) . "</td>
                <td style='text-align: right;'>" . number_format($total_paid_amt, 2) . "</td>
                <td style='text-align: right;'>" . number_format($balance, 2) . "</td>
                <td style='text-align: center; background-color: {$bg_color}; color: #fff;'>{$status}</td>
                <td style='text-align: center;'>{$payment_method}</td>
                <td style='text-align: center;'>{$station}</td>
                <td style='text-align: center;'>{$cashier}</td>
            </tr>";

        $total_amount += $expected_amount;
        $total_paid += $total_paid_amt;
        $total_balance += $balance;
    }

    echo "<tr style='font-weight:bold; background-color:#e6e6e6;'>
            <td colspan='3' style='text-align:right;'>TOTALS</td>
            <td style='text-align:right;'>" . number_format($total_amount, 2) . "</td>
            <td style='text-align:right;'>" . number_format($total_paid, 2) . "</td>
            <td style='text-align:right;'>" . number_format($total_balance, 2) . "</td>
            <td colspan='4'></td>
        </tr>";

    echo "</tbody></table>";
    exit;
}

if (isset($_REQUEST['download_pdf'])) {
    require '../includes/fpdf/fpdf.php';

    $pay_labels = [
        'pickup'   => 'Pay at Pick-up',
        'delivery' => 'Pay at Delivery',
        'cash'     => 'Cash',
        'check'    => 'Check',
        'card'     => 'Credit/Debit Card',
        'net30'    => 'Charge Net 30'
    ];

    $pdf = new FPDF('L', 'mm', 'Letter');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Sales Report', 0, 1, 'C');

    $usableWidth = $pdf->GetPageWidth() - 20;
    $colWidths = [
        'Invoice'     => $usableWidth * 0.08,
        'Date'        => $usableWidth * 0.12,
        'Customer'    => $usableWidth * 0.15,
        'Amount'      => $usableWidth * 0.10,
        'Paid'        => $usableWidth * 0.10,
        'Balance'     => $usableWidth * 0.10,
        'Status'      => $usableWidth * 0.10,
        'Method'      => $usableWidth * 0.10,
        'Station'     => $usableWidth * 0.08,
        'Cashier'     => $usableWidth * 0.07,
    ];

    $pdf->SetFont('Arial', 'B', 10);
    foreach ($colWidths as $label => $width) {
        $pdf->Cell($width, 7, $label, 1, 0, 'C');
    }
    $pdf->Ln();

    $query = "SELECT * FROM orders ORDER BY order_date DESC";
    $result = mysqli_query($conn, $query);
    $pdf->SetFont('Arial', '', 9);

    $total_amount = $total_paid = $total_balance = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $orderid = $row['orderid'];
        $customer_name = get_customer_name($row["customerid"]);
        $station = getStationName($row['station']);
        $cashier = get_staff_name($row['cashier']);
        $expected_amount = floatval($row['discounted_price']);
        $paid_amt = floatval(getOrderTotalPayments($orderid));
        $balance = $expected_amount - $paid_amt;
        $date = date("M d, Y", strtotime($row["order_date"]));

        $pay_type = strtolower(trim($row['pay_type']));
        $method = $pay_labels[$pay_type] ?? ucfirst($pay_type);

        if ($paid_amt <= 0) {
            $status = 'Not Paid';
            $pdf->SetFillColor(255, 77, 77);
        } elseif ($paid_amt < $expected_amount) {
            $status = 'Partially Paid';
            $pdf->SetFillColor(255, 165, 0);
        } else {
            $status = 'Fully Paid';
            $pdf->SetFillColor(76, 175, 80);
        }

        $pdf->Cell($colWidths['Invoice'], 6, 'SO-' . $orderid, 1, 0, 'C');
        $pdf->Cell($colWidths['Date'], 6, $date, 1, 0, 'C');
        $pdf->Cell($colWidths['Customer'], 6, $customer_name, 1, 0, 'C');
        $pdf->Cell($colWidths['Amount'], 6, number_format($expected_amount, 2), 1, 0, 'R');
        $pdf->Cell($colWidths['Paid'], 6, number_format($paid_amt, 2), 1, 0, 'R');
        $pdf->Cell($colWidths['Balance'], 6, number_format($balance, 2), 1, 0, 'R');
        $pdf->Cell($colWidths['Status'], 6, $status, 1, 0, 'C', true);
        $pdf->Cell($colWidths['Method'], 6, $method, 1, 0, 'C');
        $pdf->Cell($colWidths['Station'], 6, $station, 1, 0, 'C');
        $pdf->Cell($colWidths['Cashier'], 6, $cashier, 1, 0, 'C');
        $pdf->Ln();

        $total_amount += $expected_amount;
        $total_paid += $paid_amt;
        $total_balance += $balance;
    }

    // Totals row
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($colWidths['Invoice'] + $colWidths['Date'] + $colWidths['Customer'], 7, 'TOTALS', 1, 0, 'R');
    $pdf->Cell($colWidths['Amount'], 7, number_format($total_amount, 2), 1, 0, 'R');
    $pdf->Cell($colWidths['Paid'], 7, number_format($total_paid, 2), 1, 0, 'R');
    $pdf->Cell($colWidths['Balance'], 7, number_format($total_balance, 2), 1, 0, 'R');
    $pdf->Cell($colWidths['Status'] + $colWidths['Method'] + $colWidths['Station'] + $colWidths['Cashier'], 7, '', 1, 0);

    $pdf->Output('I', 'Sales_List.pdf');
    exit;
}

if (isset($_REQUEST['print_result'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Sales Report</title>
        <style>
            table { border-collapse: collapse; width: 100%; font-size: 12px; }
            th, td { border: 1px solid #000; padding: 4px; }
            th { background-color: #f0f0f0; }
            .not_paid { background-color: #ff4d4d; color: #fff; }
            .part_paid { background-color: #ffa500; color: #fff; }
            .paid_full { background-color: #4CAF50; color: #fff; }
            @media print {
                .not_paid, .part_paid, .paid_full { -webkit-print-color-adjust: exact; }
            }
        </style>
    </head>
    <body onload="window.print()">
        <h3>Sales Report</h3>
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Payment Method</th>
                    <th>Station</th>
                    <th>Cashier</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM orders ORDER BY order_date DESC";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $orderid = $row['orderid'];
                    $customer_name = get_customer_name($row["customerid"]);
                    $station = getStationName($row['station']);
                    $cashier = get_staff_name($row['cashier']);
                    $expected_amount = floatval($row['discounted_price']);
                    $paid_amt = floatval(getOrderTotalPayments($orderid));
                    $balance = $expected_amount - $paid_amt;
                    $date = date("M d, Y", strtotime($row["order_date"]));
                    $pay_type = strtolower(trim($row['pay_type']));
                    $method = ucfirst($pay_type);

                    if ($paid_amt <= 0) {
                        $status = 'Not Paid';
                        $class = 'not_paid';
                    } elseif ($paid_amt < $expected_amount) {
                        $status = 'Partially Paid';
                        $class = 'part_paid';
                    } else {
                        $status = 'Fully Paid';
                        $class = 'paid_full';
                    }

                    echo "<tr>
                        <td>SO-{$orderid}</td>
                        <td>{$date}</td>
                        <td>{$customer_name}</td>
                        <td style='text-align:right;'>" . number_format($expected_amount, 2) . "</td>
                        <td style='text-align:right;'>" . number_format($paid_amt, 2) . "</td>
                        <td style='text-align:right;'>" . number_format($balance, 2) . "</td>
                        <td class='{$class}'>{$status}</td>
                        <td>{$method}</td>
                        <td>{$station}</td>
                        <td>{$cashier}</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}



