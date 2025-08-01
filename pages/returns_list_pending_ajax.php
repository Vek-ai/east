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
        WHERE pr.status = 0
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

            $status = intval($row['status']);
            $badge = '';

            switch ($status) {
                case 0:
                    $badge = '<span class="badge bg-warning text-dark">Pending</span>';
                    break;
                case 1:
                    $badge = '<span class="badge bg-success">Returned</span>';
                    break;
                case 2:
                    $badge = '<span class="badge bg-danger">Rejected</span>';
                    break;
            }

            $response['orders'][] = [
                'orderid' => $order_id,
                'order_date' => $row['order_date'],
                'formatted_date' => date("F d, Y", strtotime($order_details['order_date'])),
                'formatted_time' => date("h:i A", strtotime($order_details['order_date'])),
                'cashier' => get_staff_name($order_details['cashier']),
                'customer_name' => $row['customer_name'],
                'customer_id' => $order_details['customer_id'] ?? null,
                'amount' => $amount,
                'status' => $status,
                'status_badge' => $badge
            ];

            $response['total_amount'] += $amount;
            $response['total_count']++;
        }
    } else {
        $response['error'] = 'No orders found';
    }

    echo json_encode($response);
}

if(isset($_POST['fetch_pending_details'])){
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
            <div class="mt-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-success btn-approve-return" data-id="<?= $orderid ?>">
                    <i class="fa fa-check me-1"></i> Approve Return
                </button>
                <button type="button" class="btn btn-danger btn-reject-return" data-id="<?= $orderid ?>">
                    <i class="fa fa-times me-1"></i> Reject Return
                </button>
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

if(isset($_POST['approve_return'])){
    header('Content-Type: application/json');
    $orderid = mysqli_real_escape_string($conn, $_POST['orderid']);

    $update = "UPDATE product_returns SET status = 1 WHERE orderid = '$orderid' AND status = 0";
    if (!mysqli_query($conn, $update)) {
        echo json_encode([
            'status' => 'failed',
            'message' => 'Failed to approve returned items.'
        ]);
        exit;
    }

    $query_returns = "
        SELECT pr.*, o.order_date, o.customerid
        FROM product_returns pr
        JOIN orders o ON pr.orderid = o.orderid
        WHERE pr.orderid = '$orderid' AND pr.status = 1
    ";
    $result_returns = mysqli_query($conn, $query_returns);
    if (!$result_returns || mysqli_num_rows($result_returns) === 0) {
        echo json_encode([
            'status' => 'failed',
            'message' => 'No returned items found to approve.'
        ]);
        exit;
    }

    $row = mysqli_fetch_assoc($result_returns);
    $customer_id = $row['customerid'];

    while ($row = mysqli_fetch_assoc($result_returns)) {
        $quantity = $row['quantity'];
        $discounted_price = floatval($row['discounted_price']);
        $stock_fee_percent = floatval($row['stock_fee']);
        $productid = $row['productid'];
        $return_id = $row['id'];
        $order_date = $row['order_date'];
        $customer_id = $row['originalcustomerid'];

        $purchase_date = new DateTime($order_date);
        $today = new DateTime();
        $interval = $purchase_date->diff($today)->days;

        if ($interval > 90) {
            $amount = $quantity * $discounted_price;
            $stock_fee = $amount * $stock_fee_percent;
            $amount_returned = $amount - $stock_fee;

            $credit_update = "
                UPDATE customer 
                SET store_credit = store_credit + $amount_returned
                WHERE customer_id = '$customer_id'
            ";
            if (!mysqli_query($conn, $credit_update)) {
                continue;
            }

            $credit_history = "
                INSERT INTO customer_store_credit_history (
                    customer_id,
                    credit_amount,
                    credit_type,
                    reference_type,
                    reference_id,
                    description,
                    created_at
                ) VALUES (
                    '$customer_id',
                    $amount_returned,
                    'add',
                    'product_return',
                    $return_id,
                    'Refund (return over 90 days, less stock fee)',
                    NOW()
                )
            ";
            if (!mysqli_query($conn, $credit_history)) {
            }
        }
    }

    setOrderTotals($orderid);

    $actorId = $_SESSION['userid'];
    $actor_name = get_staff_name($actorId);
    $actionType = 'return_approved';
    $targetId = $orderid;
    $targetType = 'Returns';
    $message = "Approved Return Request for Invoice #$orderid";
    $url = '#';
    createCustomerNotification($actorId, $actionType, $targetId, $targetType, $message, [$customer_id], $url);

    echo json_encode([
        'status' => 'success',
        'message' => 'Return approved successfully.'
    ]);
    exit;
}

if(isset($_POST['reject_return'])){
    header('Content-Type: application/json');
    $orderid = mysqli_real_escape_string($conn, $_POST['orderid']);
    $order_details = getOrderDetails($orderid);
    $customer_id = $order_details['customerid'];

    $update = "UPDATE product_returns SET status = 2 WHERE orderid = '$orderid'";
    if (mysqli_query($conn, $update)) {
        $actorId = $_SESSION['userid'];
        $actor_name = get_staff_name($actorId);
        $actionType = 'return_rejected';
        $targetId = $orderid;
        $targetType = 'Returns';
        $message = "Rejected Return Request for Invoice #$orderid";
        $url = '#';
        createCustomerNotification($actorId, $actionType, $targetId, $targetType, $message, [$customer_id], $url);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Return has been rejected successfully.',
            'customerid' => $customer_id
        ]);
    } else {
        echo json_encode([
            'status' => 'failed',
            'message' => 'Failed to reject the return. Please try again.'
        ]);
    }
    exit;
}
