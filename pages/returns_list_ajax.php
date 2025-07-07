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
            customer_id, 
            customer_first_name, 
            customer_last_name, 
            customer_business_name
        FROM 
            customer
        WHERE 
            (
                customer_first_name LIKE '%$search%' 
                OR customer_last_name LIKE '%$search%'
                OR customer_business_name LIKE '%$search%'
            )
            AND status NOT IN ('0', '3')
        LIMIT 15
    ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $response = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $fullName = $row['customer_first_name'] . ' ' . $row['customer_last_name'];
            $label = !empty($row['customer_business_name']) 
                ? $fullName . ' (' . $row['customer_business_name'] . ')' 
                : $fullName;

            $response[] = [
                'value' => $row['customer_id'],
                'label' => $label
            ];
        }
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Query failed']);
    }
}

if (isset($_POST['search_returns'])) {
    $response = [
        'orders' => [],
        'total_count' => 0,
        'total_amount' => 0,
        'error' => null
    ];

    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name'] ?? '');
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from'] ?? '');
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to'] ?? '');
    $months = array_map('intval', $_POST['months'] ?? []);
    $years = array_map('intval', $_POST['years'] ?? []);
    $staff = mysqli_real_escape_string($conn, $_POST['staff'] ?? '');
    $tax_status = mysqli_real_escape_string($conn, $_POST['tax_status'] ?? '');

    $query = "
        SELECT pr.*, 
               CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name,
               c.customer_id
        FROM product_returns AS pr
        LEFT JOIN orders AS o ON o.orderid = pr.orderid
        LEFT JOIN customer AS c ON c.customer_id = o.originalcustomerid
        WHERE 1 = 1
    ";

    if (!empty($customer_name) && $customer_name !== 'All Customers') {
        $query .= " AND CONCAT(c.customer_first_name, ' ', c.customer_last_name) LIKE '%$customer_name%' ";
    }

    if (!empty($date_from) && !empty($date_to)) {
        $date_to .= ' 23:59:59';
        $query .= " AND o.order_date BETWEEN '$date_from' AND '$date_to' ";
    } elseif (!empty($date_from)) {
        $query .= " AND o.order_date >= '$date_from' ";
    } elseif (!empty($date_to)) {
        $date_to .= ' 23:59:59';
        $query .= " AND o.order_date <= '$date_to' ";
    }

    if (!empty($months)) {
        $months_in = implode(',', $months);
        $query .= " AND MONTH(o.order_date) IN ($months_in) ";
    }

    if (!empty($years)) {
        $years_in = implode(',', $years);
        $query .= " AND YEAR(o.order_date) IN ($years_in) ";
    }

    if (!empty($staff)) {
        $query .= " AND o.cashier = '$staff' ";
    }

    if (!empty($tax_status)) {
        $query .= " AND c.tax_status = '$tax_status' ";
    }

    $query .= " GROUP BY o.orderid ";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $order_id = $row['orderid'];
            $amount = getReturnTotals($order_id);
            $order_details = getOrderDetails($order_id);

            $response['orders'][] = [
                'orderid' => $order_id,
                'order_date' => $row['order_date'],
                'formatted_date' => date("F d, Y", strtotime($order_details['order_date'])),
                'formatted_time' => date("h:i A", strtotime($order_details['order_date'])),
                'cashier' => get_staff_name($order_details['cashier']),
                'customer_name' => $row['customer_name'],
                'customer_id' => $order_details['customer_id'] ?? null,
                'amount' => $amount,
                'sql' => $query
            ];

            $response['total_amount'] += $amount;
            $response['total_count']++;
        }
    } else {
        $response['error'] = 'No orders found';
    }

    echo json_encode($response);
}


if(isset($_POST['fetch_order_details'])){
    $orderid = mysqli_real_escape_string($conn, $_POST['orderid']);
    ?>
    <style>
        .tooltip-inner {
            background-color: white !important;
            color: black !important;
            font-size: calc(0.875rem + 2px) !important;
        }
    </style>
    <div class="card card-body datatables">
        <?php 
            $query = "SELECT * FROM product_returns WHERE orderid='$orderid'";
            $result = mysqli_query($conn, $query);
            $totalquantity = $total_actual_price = $total_disc_price = $total_amount_returned = $total_stock_fee = 0;
            if ($result && mysqli_num_rows($result) > 0) {
            ?>
            <div class="return-details table-responsive text-wrap mt-5">
                <h4>Return/Refund Details</h4>
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
                            <th class="text-center">Stocking Fee</th>
                            <th class="text-center">Amount Returned</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                                $product_id = $row['productid'];
                                $price = $row['discounted_price'] * $row['quantity'];
                                $stock_fee = floatval($row['stock_fee']) * $price;
                                $amount_returned = $price - $stock_fee;
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
                                    <td class="text-end">$ <?= number_format($price,2) ?></td>
                                    <td class="text-end">$ <?= number_format($stock_fee,2) ?></td>
                                    <td class="text-end">$ <?= number_format($amount_returned,2) ?></td>
                                </tr>
                        <?php
                                $totalquantity += $row['quantity'] ;
                                $total_disc_price += $price;
                                $total_stock_fee += $stock_fee;
                                $total_amount_returned += $amount_returned;
                                }
                            
                        }
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="4">Total</td>
                            <td><?= $totalquantity ?></td>
                            <td></td>
                            <td class="text-end">$ <?= number_format($total_disc_price,2) ?></td>
                            <td class="text-end">$ <?= number_format($total_stock_fee,2) ?></td>
                            <td class="text-end">$ <?= number_format($total_amount_returned,2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php 
        } 
        ?>
    </div>   
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

if (isset($_POST['send_order'])) {
    $orderid = mysqli_real_escape_string($conn, $_POST['send_order_id']);
    $customer_id = mysqli_real_escape_string($conn, $_POST['send_customer_id']);
    $customer_details= getCustomerDetails($customer_id);
    $customer_name = get_customer_name($customer_id);
    $customer_email = $customer_details['contact_email'];
    $customer_phone = $customer_details['contact_phone'];

    $send_option = mysqli_real_escape_string($conn, $_POST['send_option']);

    $return_url = "https://metal.ilearnwebtech.com/print_return_product.php?id=" . urlencode($orderid);

    $subject = "Product Returns";

    $sms_message = "Hi $customer_name,\n\nYour product orders has been successfully returned.\nClick this link for details:\n$return_url";
    
    $html_message = "
    <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f4f4f4;
                    padding: 30px;
                }
                .container {
                    padding: 20px;
                    border: 1px solid #e0e0e0;
                    background-color: #ffffff;
                    width: 80%;
                    margin: 0 auto;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
                }
                h2 {
                    color: #0056b3;
                    margin-bottom: 15px;
                }
                .link {
                    display: inline-block;
                    margin-top: 10px;
                    padding: 10px 15px;
                    background-color: #007bff;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                }
                .link:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Order Invoice</h2>
                <p>Hi $customer_name,</p>
                <p>Your product orders has been successfully returned. Click the button below for more details.</p>
                <a href='$return_url' class='link' target='_blank'>View Details</a>
            </div>
        </body>
    </html>";

    if ($send_option === 'email' || $send_option === 'both') {
        $results['email'] = sendEmail($customer_email, $customer_name, $subject, $html_message);
    }

    if ($send_option === 'sms' || $send_option === 'both') {
        $results['sms'] = sendPhoneMessage($customer_phone, $customer_name, $subject, $sms_message);
    }

    $response = [
        'success' => true,
        'message' => "Successfully sent to Customer",
        'results' => $results
    ];

    echo json_encode($response);
}
