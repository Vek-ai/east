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

    $status_labels = [
        'pickup'   => ['label' => 'Pay at Pick-up'],
        'delivery' => ['label' => 'Pay at Delivery'],
        'cash'     => ['label' => 'Cash'],
        'check'    => ['label' => 'Check'],
        'card'     => ['label' => 'Credit/Debit Card'],
        'net30'    => ['label' => 'Charge Net 30'],
    ];

    $query = "
        SELECT o.*, CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name, customer_pricing
        FROM orders AS o
        LEFT JOIN customer AS c ON c.customer_id = o.originalcustomerid
        WHERE o.status != 6
    ";

    if (!empty($customer_name) && $customer_name != 'All Customers') {
        $query .= " AND CONCAT(c.customer_first_name, ' ', c.customer_last_name) LIKE '%$customer_name%' ";
    }

    if (!empty($date_from) && !empty($date_to)) {
        $date_to .= ' 23:59:59';
        $query .= " AND (o.order_date >= '$date_from' AND o.order_date <= '$date_to') ";
    } elseif (!empty($date_from)) {
        $query .= " AND o.order_date >= '$date_from' ";
    } elseif (!empty($date_to)) {
        $date_to .= ' 23:59:59';
        $query .= " AND o.order_date <= '$date_to' ";
    }

    if (!empty($months)) {
        $months_in = implode(',', $months);
        $query .= " AND MONTH(o.order_date) IN ($months_in)";
    }

    if (!empty($years)) {
        $years_in = implode(',', $years);
        $query .= " AND YEAR(o.order_date) IN ($years_in)";
    }

    if (!empty($staff)) {
        $query .= " AND cashier = '$staff'";
    }

    if (!empty($tax_status)) {
        $query .= " AND c.tax_status = '$tax_status'";
    }

    $query .= " ORDER BY o.order_date DESC";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orderid = $row['orderid'];
            $pay_type = strtolower(trim($row['pay_type']));
            $label = $status_labels[$pay_type]['label'] ?? ucfirst($pay_type);

            $total_paid = getOrderTotalPayments($orderid);
            $expected_amount = floatval($row['discounted_price']);

            if ($total_paid <= 0) {
                $payment_status = 'not_paid';
                $color = 'red';
            } elseif ($total_paid < $expected_amount) {
                $payment_status = 'paid_in_part';
                $color = 'orange';
            } else {
                $payment_status = 'paid_in_full';
                $color = 'green';
            }

            if (!empty($paid_status) && $payment_status !== $paid_status) {
                continue;
            }
            

            $status_html = '<span class="badge" style="background-color: ' . $color . ';">' . htmlspecialchars($label) . '</span>';

            $response['orders'][] = [
                'orderid' => $row['orderid'],
                'order_date' => $row['order_date'],
                'formatted_date' => date("F d, Y", strtotime($row['order_date'])),
                'formatted_time' => date("h:i A", strtotime($row['order_date'])),
                'cashier' => get_staff_name($row['cashier']),
                'station' => getStationName($row["station"]),
                'customer_name' => $row['customer_name'],
                'customer_pricing' => $row['customer_pricing'],
                'amount' => number_format($row['discounted_price'], 2),
                'status' => $status_html,
                'payment_status'  => $payment_status,
            ];
            $response['total_amount'] += $row['discounted_price'];
            $response['total_count']++;
        }
    }
 else {
        $response['error'] = 'No orders found';
    }

    echo json_encode($response);
}

if(isset($_POST['fetch_order_details'])){
    $orderid = mysqli_real_escape_string($conn, $_POST['orderid']);
    ?>
    <style>
        .tooltip-inner {
            font-size: calc(0.875rem + 2px) !important;
        }
    </style>
    <div class="card card-body datatables">
        <div class="product-details table-responsive text-wrap">
            <h4>Products Ordered</h4>
            <table id="order_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
                <thead>
                    <tr>
                        <th>Line ID</th>
                        <th>Description</th>
                        <th>Color</th>
                        <th>Grade</th>
                        <th>Profile</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Dimensions</th>
                        <th class="text-center">Unit Price</th>
                        <th class="text-center">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 0;
                    $query = "SELECT * FROM order_product WHERE orderid='$orderid' ";
                    $result = mysqli_query($conn, $query);
                    $totalquantity = $total_actual_price = $total_disc_price = 0;
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $product_id = $row['productid'];
                            if($row['quantity'] > 0){
                            ?>
                            <tr>
                                <td class="text-wrap"> 
                                    <?php echo "L" .$row['id'] ?>
                                </td>
                                <td class="text-wrap"> 
                                    <?php echo getProductName($product_id) ?>
                                </td>
                                <td>
                                    <div class="d-flex mb-0 gap-8">
                                        <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromColorID($row['custom_color'])?>"></a>
                                        <?= getColorName($row['custom_color']); ?>
                                    </div>
                                </td>
                                <td>
                                    <?= getGradeName($row['custom_grade']); ?>
                                </td>
                                <td>
                                    <?= getProfileTypeName($row['custom_profile']); ?>
                                </td>
                                <td>
                                    <?php echo $row['quantity']; ?>
                                </td>
                                <td>
                                    <?php 
                                    $width = $row['custom_width'];
                                    $bend = $row['custom_bend'];
                                    $hem = $row['custom_hem'];
                                    $length = $row['custom_length'];
                                    $inch = $row['custom_length2'];
                                    
                                    if (!empty($width)) {
                                        echo "Width: " . htmlspecialchars($width) . "<br>";
                                    }
                                    
                                    if (!empty($bend)) {
                                        echo "Bend: " . htmlspecialchars($bend) . "<br>";
                                    }
                                    
                                    if (!empty($hem)) {
                                        echo "Hem: " . htmlspecialchars($hem) . "<br>";
                                    }
                                    
                                    if (!empty($length)) {
                                        echo "Length: " . htmlspecialchars($length) . " ft";
                                        
                                        if (!empty($inch)) {
                                            echo " " . htmlspecialchars($inch) . " in";
                                        }
                                        echo "<br>";
                                    } elseif (!empty($inch)) {
                                        echo "Length: " . htmlspecialchars($inch) . " in<br>";
                                    }
                                    ?>
                                </td>
                                <td class="text-end">$ <?= number_format($row['discounted_price'],2) ?></td>
                                <td class="text-end">$ <?= number_format($row['discounted_price'] * $row['quantity'],2) ?></td>
                            </tr>
                    <?php
                            $totalquantity += $row['quantity'] ;
                            $total_actual_price += $row['discounted_price'];
                            $total_disc_price += $row['discounted_price'] * $row['quantity'];
                            }
                        }
                    }
                    ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="5">Total</td>
                        <td><?= $totalquantity ?></td>
                        <td></td>
                        <td class="text-end">$ <?= number_format($total_actual_price,2) ?></td>
                        <td class="text-end">$ <?= number_format($total_disc_price,2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php 
        $query = "SELECT * FROM product_returns WHERE orderid='$orderid'";
        $result = mysqli_query($conn, $query);
        $totalquantity = $total_actual_price = $total_disc_price = 0;
        if ($result && mysqli_num_rows($result) > 0) {
        ?>
        <div class="card card-body datatables">
            <div class="return-details table-responsive text-wrap mt-5">
                <h4>Returned Products</h4>
                <table id="return_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Color</th>
                            <th>Grade</th>
                            <th>Profile</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Dimensions</th>
                            <th class="text-center">Price</th>
                            <th class="text-center">Customer Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                                $product_id = $row['productid'];
                                if($row['quantity'] > 0){
                                ?>
                                <tr>
                                    <td class="text-wrap"> 
                                        <?php echo getProductName($product_id) ?>
                                    </td>
                                    <td>
                                    <div class="d-flex mb-0 gap-8">
                                        <span class="rounded-circle d-block p-3" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_id)?>; width: 20px; height: 20px;"></span>
                                        <?= getColorFromID($product_id); ?>
                                    </div>
                                    </td>
                                    <td>
                                        <?php echo getGradeFromID($product_id); ?>
                                    </td>
                                    <td>
                                        <?php echo getProfileFromID($product_id); ?>
                                    </td>
                                    <td>
                                        <?php echo $row['quantity']; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $width = $row['custom_width'];
                                        $bend = $row['custom_bend'];
                                        $hem = $row['custom_hem'];
                                        $length = $row['custom_length'];
                                        $inch = $row['custom_length2'];
                                        
                                        if (!empty($width)) {
                                            echo "Width: " . htmlspecialchars($width) . "<br>";
                                        }
                                        
                                        if (!empty($bend)) {
                                            echo "Bend: " . htmlspecialchars($bend) . "<br>";
                                        }
                                        
                                        if (!empty($hem)) {
                                            echo "Hem: " . htmlspecialchars($hem) . "<br>";
                                        }
                                        
                                        if (!empty($length)) {
                                            echo "Length: " . htmlspecialchars($length) . " ft";
                                            
                                            if (!empty($inch)) {
                                                echo " " . htmlspecialchars($inch) . " in";
                                            }
                                            echo "<br>";
                                        } elseif (!empty($inch)) {
                                            echo "Length: " . htmlspecialchars($inch) . " in<br>";
                                        }
                                        ?>
                                    </td>
                                    <td class="text-end">$ <?= number_format($row['actual_price'],2) ?></td>
                                    <td class="text-end">$ <?= number_format($row['discounted_price'],2) ?></td>
                                </tr>
                        <?php
                                $totalquantity += $row['quantity'] ;
                                $total_actual_price += $row['actual_price'];
                                $total_disc_price += $row['discounted_price'];
                                }
                            
                        }
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="4">Total</td>
                            <td><?= $totalquantity ?></td>
                            <td></td>
                            <td class="text-end">$ <?= number_format($total_actual_price,2) ?></td>
                            <td class="text-end">$ <?= number_format($total_disc_price,2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
    <?php 
    } 
    ?>
       
    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip(); 

            $('#order_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Order Details not found"
                },
                autoWidth: false,
                responsive: true
            });

            $('#view_order_details_modal').on('shown.bs.modal', function () {
                $('#order_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
            });

            $('#return_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Order Details not found"
                },
                autoWidth: false,
                responsive: true
            });

            $('#view_order_details_modal').on('shown.bs.modal', function () {
                $('#return_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
            });
        });
    </script>
    <?php
}

if(isset($_POST['fetch_close_details'])){
    $orderid = mysqli_real_escape_string($conn, $_POST['orderid']);
    ?>
    <style>
        .tooltip-inner {
            font-size: calc(0.875rem + 2px) !important;
        }
    </style>
    <div class="card card-body datatables">
        <div class="product-details table-responsive text-wrap">
            <h4>Products Ordered</h4>
            <table id="order_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Color</th>
                        <th>Grade</th>
                        <th>Profile</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Dimensions</th>
                        <th class="text-center">Unit Price</th>
                        <th class="text-center">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 0;
                    $query = "SELECT * FROM order_product WHERE orderid='$orderid' ";
                    $result = mysqli_query($conn, $query);
                    $totalquantity = $total_actual_price = $total_disc_price = 0;
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $product_id = $row['productid'];
                            if($row['quantity'] > 0){
                            ?>
                            <tr>
                                <td class="text-wrap"> 
                                    <?php echo getProductName($product_id) ?>
                                </td>
                                <td>
                                <div class="d-flex mb-0 gap-8">
                                    <span class="rounded-circle d-block p-3" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_id)?>; width: 20px; height: 20px;"></span>
                                    <?= getColorFromID($product_id); ?>
                                </div>
                                </td>
                                <td>
                                    <?php echo getGradeFromID($product_id); ?>
                                </td>
                                <td>
                                    <?php echo getProfileFromID($product_id); ?>
                                </td>
                                <td>
                                    <?php echo $row['quantity']; ?>
                                </td>
                                <td>
                                    <?php 
                                    $width = $row['custom_width'];
                                    $bend = $row['custom_bend'];
                                    $hem = $row['custom_hem'];
                                    $length = $row['custom_length'];
                                    $inch = $row['custom_length2'];
                                    
                                    if (!empty($width)) {
                                        echo "Width: " . htmlspecialchars($width) . "<br>";
                                    }
                                    
                                    if (!empty($bend)) {
                                        echo "Bend: " . htmlspecialchars($bend) . "<br>";
                                    }
                                    
                                    if (!empty($hem)) {
                                        echo "Hem: " . htmlspecialchars($hem) . "<br>";
                                    }
                                    
                                    if (!empty($length)) {
                                        echo "Length: " . htmlspecialchars($length) . " ft";
                                        
                                        if (!empty($inch)) {
                                            echo " " . htmlspecialchars($inch) . " in";
                                        }
                                        echo "<br>";
                                    } elseif (!empty($inch)) {
                                        echo "Length: " . htmlspecialchars($inch) . " in<br>";
                                    }
                                    ?>
                                </td>
                                <td class="text-end">$ <?= number_format($row['discounted_price'],2) ?></td>
                                <td class="text-end">$ <?= number_format($row['discounted_price'] * $row['quantity'],2) ?></td>
                            </tr>
                    <?php
                            $totalquantity += $row['quantity'] ;
                            $total_actual_price += $row['discounted_price'];
                            $total_disc_price += $row['discounted_price'] * $row['quantity'];
                            }
                        }
                    }
                    ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="4">Total</td>
                        <td><?= $totalquantity ?></td>
                        <td></td>
                        <td class="text-end">$ <?= number_format($total_actual_price,2) ?></td>
                        <td class="text-end">$ <?= number_format($total_disc_price,2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip(); 

            $('#close_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Order Details not found"
                },
                autoWidth: false,
                responsive: true
            });
        });
    </script>
    <?php
}

if(isset($_REQUEST['download_excel'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Sales_List.xls");

    $pay_labels = [
        'pickup'   => ['label' => 'Pay at Pick-up'],
        'delivery' => ['label' => 'Pay at Delivery'],
        'cash'     => ['label' => 'Cash'],
        'check'    => ['label' => 'Check'],
        'card'     => ['label' => 'Credit/Debit Card'],
        'net30'    => ['label' => 'Charge Net 30'],
    ];

    echo "<table border='1'>";
    echo "<thead>
            <tr style='font-weight: bold; background-color: #f0f0f0;'>
                <th>Invoice #</th>
                <th>Customer</th>
                <th>Total Price</th>
                <th>Order Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Salesperson</th>
            </tr>
        </thead><tbody>";

    $query = "SELECT * FROM orders ORDER BY order_date DESC";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $orderid = $row['orderid'];
        $customer_name = get_customer_name($row["customerid"]);
        $total_price = number_format($row['discounted_price'], 2);

        $date = '';
        $time = '';
        if (!empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') {
            $date = date("F d, Y", strtotime($row["order_date"]));
            $time = date("h:i A", strtotime($row["order_date"]));
        }

        $pay_type_key = strtolower(trim($row['pay_type']));
        $pay_type = $pay_labels[$pay_type_key]['label'] ?? ucfirst($pay_type_key);

        $total_paid = getOrderTotalPayments($orderid);
        $expected_amount = floatval($row['discounted_price']);

        if ($total_paid <= 0) {
            $payment_status = 'Not Paid';
            $bg_color = '#ff4d4d';
        } elseif ($total_paid < $expected_amount) {
            $payment_status = 'Partially Paid';
            $bg_color = '#ffa500';
        } else {
            $payment_status = 'Fully Paid';
            $bg_color = '#4CAF50';
        }

        $cashier = get_staff_name($row['cashier']);

        echo "<tr>
                <td style='text-align: center'>" . htmlspecialchars($orderid) . "</td>
                <td style='text-align: center'>" . htmlspecialchars($customer_name) . "</td>
                <td style='text-align: right'>" . $total_price . "</td>
                <td style='text-align: center'>" . $date . "</td>
                <td style='text-align: center'>" . $time . "</td>
                <td style='text-align: center; background-color: {$bg_color}; color: #fff;'>" . $pay_type . "</td>
                <td style='text-align: center'>" . htmlspecialchars($cashier) . "</td>
            </tr>";
    }

    echo "</tbody></table>";
    exit;
}

if (isset($_REQUEST['download_pdf'])) {
    require '../includes/fpdf/fpdf.php';

    $pay_labels = [
        'pickup'   => ['label' => 'Pay at Pick-up'],
        'delivery' => ['label' => 'Pay at Delivery'],
        'cash'     => ['label' => 'Cash'],
        'check'    => ['label' => 'Check'],
        'card'     => ['label' => 'Credit/Debit Card'],
        'net30'    => ['label' => 'Charge Net 30'],
    ];

    $pdf = new FPDF('P', 'mm', 'Letter');
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Sales List', 0, 1, 'L');

    $usableWidth = $pdf->GetPageWidth() - 20;
    $colWidths = [
        'Invoice'     => $usableWidth * 0.12,
        'Customer'    => $usableWidth * 0.17,
        'Amount'      => $usableWidth * 0.13,
        'Date'        => $usableWidth * 0.17,
        'Time'        => $usableWidth * 0.13,
        'Status'      => $usableWidth * 0.15,
        'Salesperson' => $usableWidth * 0.13
    ];

    $cellheight = 6;

    $pdf->SetFont('Arial', 'B', 10);
    foreach ($colWidths as $label => $width) {
        $pdf->Cell($width, $cellheight, $label, 1, 0, 'C');
    }
    $pdf->Ln();

    $query = "SELECT * FROM orders ORDER BY order_date DESC";
    $result = mysqli_query($conn, $query);
    $pdf->SetFont('Arial', '', 10);

    while ($row = mysqli_fetch_assoc($result)) {
        $orderid = $row['orderid'];
        $customer_name = get_customer_name($row["customerid"]);
        $amount = number_format(floatval($row['discounted_price']), 2);
        $date = $time = '';
        if (!empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') {
            $date = date("F d, Y", strtotime($row["order_date"]));
            $time = date("h:i A", strtotime($row['order_date']));
        }

        $pay_type = strtolower(trim($row['pay_type']));
        $label = $pay_labels[$pay_type]['label'] ?? ucfirst($pay_type);

        $total_paid = getOrderTotalPayments($orderid);
        $expected = floatval($row['discounted_price']);

        if ($total_paid <= 0) {
            $status = 'Not Paid';
            $pdf->SetFillColor(255, 77, 77);
        } elseif ($total_paid < $expected) {
            $status = 'Partially Paid';
            $pdf->SetFillColor(255, 165, 0);
        } else {
            $status = 'Fully Paid';
            $pdf->SetFillColor(76, 175, 80);
        }

        $cashier = get_staff_name($row['cashier']);

        $pdf->Cell($colWidths['Invoice'], $cellheight, $orderid, 1, 0, 'C');
        $pdf->Cell($colWidths['Customer'], $cellheight, $customer_name, 1, 0, 'C');
        $pdf->Cell($colWidths['Amount'], $cellheight, '$' . $amount, 1, 0, 'R');
        $pdf->Cell($colWidths['Date'], $cellheight, $date, 1, 0, 'C');
        $pdf->Cell($colWidths['Time'], $cellheight, $time, 1, 0, 'C');
        $pdf->Cell($colWidths['Status'], $cellheight, $label, 1, 0, 'C', true);
        $pdf->Cell($colWidths['Salesperson'], $cellheight, $cashier, 1, 0, 'C');
        $pdf->Ln();
    }

    $pdf->Output('I', 'sales_list.pdf');
    exit;
}

if(isset($_REQUEST['print_result'])) {
    $status_labels = [
        'pickup'   => ['label' => 'Pay at Pick-up'],
        'delivery' => ['label' => 'Pay at Delivery'],
        'cash'     => ['label' => 'Cash'],
        'check'    => ['label' => 'Check'],
        'card'     => ['label' => 'Credit/Debit Card'],
        'net30'    => ['label' => 'Charge Net 30'],
    ];

    $query = "SELECT * FROM orders ORDER BY order_date DESC";
    $result = mysqli_query($conn, $query);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Print Orders</title>
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
            }
            th, td {
                border: 1px solid #000;
                padding: 2px;
                padding-left: 8px;
                
            }
            th {
                background-color: #f0f0f0;
            }
            .badge {
                color: #fff;
                padding: 2px 6px;
                border-radius: 999px; 
                display: inline-block;
            }
            .badge-red {
                background-color: red !important;
            }
            .badge-orange {
                background-color: orange !important;
            }
            .badge-green {
                background-color: green !important;
            }

            @media print {
                .badge {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }
        </style>
    </head>
    <body onload="window.print()">
        <h3>Sales List</h3>
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th style="text-align: right">Amount</th>
                    <th style="text-align: center">Date</th>
                    <th style="text-align: center">Time</th>
                    <th style="text-align: center">Status</th>
                    <th style="text-align: center">Salesperson</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    while ($row = mysqli_fetch_assoc($result)){ 
                        $orderid = $row['orderid'];
                        $customer_name = get_customer_name($row["customerid"]);
                        $total_price = '$' .number_format(floatval($row['discounted_price']), 2);

                        $date = '';
                        if (!empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') {
                            $date = date("F d, Y", strtotime($row["order_date"]));
                            $time = date("h:i A", strtotime($row['order_date']));
                        }

                        $orderid = $row['orderid'];
                        $pay_type = strtolower(trim($row['pay_type']));
                        $label = $status_labels[$pay_type]['label'] ?? ucfirst($pay_type);

                        $total_paid = getOrderTotalPayments($orderid);
                        $expected_amount = floatval($row['discounted_price']);

                        if ($total_paid <= 0) {
                            $payment_status = 'not_paid';
                            $color = 'red';
                        } elseif ($total_paid < $expected_amount) {
                            $payment_status = 'paid_in_part';
                            $color = 'orange';
                        } else {
                            $payment_status = 'paid_in_full';
                            $color = 'green';
                        }

                        if (!empty($paid_status) && $payment_status !== $paid_status) {
                            continue;
                        }

                        $status_html = '<span class="badge badge-' . $color . '">' . htmlspecialchars($label) . '</span>';

                        $cashier = get_staff_name($row['cashier']);
                    ?>
                    <tr>
                        <td style="text-align: center"><?= $orderid ?></td>
                        <td style="text-align: center"><?= $customer_name ?></td>
                        <td style="text-align: right"><?= $total_price ?></td>
                        <td style="text-align: center"><?= $date ?></td>
                        <td style="text-align: center"><?= $time ?></td>
                        <td style="text-align: center"><?= $status_html ?></td>
                        <td style="text-align: center"><?= $cashier ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_REQUEST['close_out_order'])) {
    $orderid = intval($_POST['orderid']);

    $sql = "SELECT id, productid, custom_color, quantity 
            FROM order_product 
            WHERE orderid = $orderid";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $productid = intval($row['productid']);
        $color_id = is_null($row['custom_color']) ? 'IS NULL' : '= ' . intval($row['custom_color']);
        $quantity = intval($row['quantity']);
        $order_product_id = intval($row['id']);

        $inv_sql = "
            SELECT Inventory_id, quantity_ttl 
            FROM inventory 
            WHERE Product_id = $productid AND color_id $color_id 
            LIMIT 1";
        $inv_result = mysqli_query($conn, $inv_sql);

        if ($inv_row = mysqli_fetch_assoc($inv_result)) {
            $inventory_id = intval($inv_row['Inventory_id']);
            $new_qty = intval($inv_row['quantity_ttl']) + $quantity;

            mysqli_query($conn, "
                UPDATE inventory 
                SET quantity_ttl = $new_qty 
                WHERE Inventory_id = $inventory_id
            ");
        }

        mysqli_query($conn, "
            UPDATE order_product 
            SET actual_price = 0,
                discounted_price = 0,
                quantity = 0,
                status = 6
            WHERE id = $order_product_id
        ");
    }

    mysqli_query($conn, "UPDATE orders SET status = 6 WHERE orderid = $orderid");

    echo 'success';
}

if (isset($_REQUEST['fetch_edit_sales'])) {
    $orderid = intval($_POST['sale_id']);
    $password = trim($_POST['password']);

    $sales_edit_password = getSetting('sales_edit_password');

    if ($password !== $sales_edit_password) {
        echo '<div class="alert alert-danger text-center mb-0">Invalid password.</div>';
        exit;
    }

    $query = "SELECT orderid, pay_type, deliver_method FROM orders WHERE orderid = '$orderid' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        echo '<div class="alert alert-warning text-center mb-0">Order not found.</div>';
        exit;
    }

    $order = mysqli_fetch_assoc($result);
    $payType = $order['pay_type'];
    $deliverMethod = $order['deliver_method'];
?>
    <form id="editSalesForm">
        <input type="hidden" name="orderid" value="<?= $orderid ?>">

        <div class="mb-3">
            <label class="form-label fw-bold">Order ID: <?= $orderid ?></label>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Payment Method</label>
            <select class="form-select" name="payMethod" id="payMethod" required>
                <option value="" hidden>-- Select Payment Method --</option>
                <option value="pickup" <?= $payType === 'pickup' ? 'selected' : '' ?>>Pay at Pick-Up</option>
                <option value="delivery" <?= $payType === 'delivery' ? 'selected' : '' ?>>Pay at Delivery</option>
                <option value="cash" <?= $payType === 'cash' ? 'selected' : '' ?>>Cash</option>
                <option value="check" <?= $payType === 'check' ? 'selected' : '' ?>>Check</option>
                <option value="card" <?= $payType === 'card' ? 'selected' : '' ?>>Credit/Debit Card</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Delivery Method</label>
            <select name="deliveryMethod" id="deliveryMethod" class="form-select" required>
                <option value="" hidden>-- Select Delivery Method --</option>
                <option value="pickup" <?= $deliverMethod === 'pickup' ? 'selected' : '' ?>>Pick-Up</option>
                <option value="deliver" <?= $deliverMethod === 'deliver' ? 'selected' : '' ?>>Delivery</option>
            </select>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
        </div>
    </form>
<?php
}

if (isset($_POST['edit_sales'])) {
    $orderid = intval($_POST['orderid']);
    $payMethod = mysqli_real_escape_string($conn, $_POST['payMethod']);
    $deliveryMethod = mysqli_real_escape_string($conn, $_POST['deliveryMethod']);

    $query = "
        UPDATE orders 
        SET pay_type = '$payMethod', deliver_method = '$deliveryMethod', is_edited = 1 
        WHERE orderid = '$orderid'
    ";

    if (mysqli_query($conn, $query)) {
        echo '<div class="alert alert-success text-center mb-0">Sales record updated successfully!</div>';
    } else {
        echo '<div class="alert alert-danger text-center mb-0">Error updating record</div>';
    }
}

