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

if (isset($_POST['search_order_estimate'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $job_po = mysqli_real_escape_string($conn, $_POST['job_po']);
    $job_order = mysqli_real_escape_string($conn, $_POST['job_order']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);

    $query = "
        SELECT 
            c.customer_id,
            CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name,
            o.job_po AS job_po,
            o.job_name AS job_name,
            oe.status AS status,
            oe.type AS source_type,
            o.orderid AS id,
            o.order_date AS record_date
        FROM order_estimate AS oe
        LEFT JOIN orders AS o ON oe.order_estimate_id = o.orderid AND oe.type = 2
        LEFT JOIN customer AS c ON o.originalcustomerid = c.customer_id
        WHERE oe.type = 2

        UNION ALL

        SELECT 
            c.customer_id,
            CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name,
            e.job_po AS job_po,
            e.job_name AS job_name,
            oe.status AS status,
            oe.type AS source_type,
            e.estimateid AS id,
            e.estimated_date AS record_date
        FROM order_estimate AS oe
        LEFT JOIN estimates AS e ON oe.order_estimate_id = e.estimateid AND oe.type = 1
        LEFT JOIN customer AS c ON e.originalcustomerid = c.customer_id
        WHERE oe.type = 1
    ";

    $filters = [];
    if (!empty($customer_name) && $customer_name != 'All Customers') {
        $filters[] = "customer_name LIKE '%$customer_name%'";
    }

    if (!empty($job_po) && $job_po != 'All Customers') {
        $filters[] = "(job_po LIKE '%$job_po%')";
    }

    if (!empty($job_order) && $job_order != 'All Customers') {
        $filters[] = "(job_name LIKE '%$job_order%')";
    }

    if (!empty($date_from)) {
        $filters[] = "record_date >= '$date_from'";
    }

    if (!empty($date_to)) {
        $filters[] = "record_date <= '$date_to'";
    }

    if (!empty($filters)) {
        $filter_condition = implode(" AND ", $filters);
        $query = "
            SELECT * FROM (
                $query
            ) AS combined_result
            WHERE $filter_condition
        ";
    }

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $total_amount = 0;
        $total_count = 0;

        ?>
        <table id="sales_table" class="table table-hover mb-0 text-md-nowrap">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Job PO #</th>
                    <th>Job Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>     
            <?php

            while ($row = mysqli_fetch_assoc($result)) {

                $customer_name = $row['customer_name'];
                $job_po = $row['job_po'];
                $job_name = $row['job_name'];
                $type = $row['source_type'];
                if($type == 1){
                    $type_text = 'Estimate';
                }else{
                    $type_text = 'Order';
                }
                $status = $row['status'];
                $status_badges = [
                    0 => ['class' => 'bg-primary', 'text' => 'New'],
                    1 => ['class' => 'bg-warning', 'text' => 'Pick Up'],
                    2 => ['class' => 'bg-info', 'text' => 'Dispatched'],
                    3 => ['class' => 'bg-success', 'text' => 'Delivered'],
                ];
                ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($customer_name) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($job_po) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($job_name) ?>
                    </td>
                    <td>
                        <?= $type_text ?>
                    </td>
                    <td>
                        <?php
                            if (isset($status_badges[$status])) {
                                echo '<span class="d-flex align-items-center gap-2">';
                                echo '<span class="badge ' . $status_badges[$status]['class'] . '">&nbsp;</span>';
                                echo $status_badges[$status]['text'];
                                echo '</span>';
                            } 
                        ?>
                    </td>
                    <td>
                        <a href="javascript:void(0)" data-id="<?= $row['id'] ?>" data-type="<?= $type ?>" id="view_details">
                            <i class="fa fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
    } else {
        echo "<h4 class='text-center'>No orders found</h4>";
    }
}

if($_POST['fetchType'] == 'fetch_order_details'){
    $orderid = mysqli_real_escape_string($conn, $_POST['id']);
    $order_details = getOrderDetails($orderid);
    $fullAddress = trim(implode(', ', array_filter([
        $order_details['deliver_address'] ?? null,
        $order_details['deliver_city'] ?? null,
        $order_details['deliver_state'] ?? null,
        $order_details['deliver_zip'] ?? null,
    ])));
    ?>
    <style>
        #est_dtls_tbl {
            width: 100% !important;
        }

        #est_dtls_tbl td, #est_dtls_tbl th {
            white-space: normal !important;
            word-wrap: break-word;
        }
    </style>
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myLargeModalLabel">
                    View Order
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="update_product" class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="mb-2">
                                        <h5 class="fw-bold mb-1">Recipient Name:</h5>
                                        <div class="ms-3 d-flex gap-1">
                                            <h5 class="recipient-fname mb-0"><?= $order_details['deliver_fname'] ?></h5>
                                            <h5 class="recipient-lname mb-0"><?= $order_details['deliver_lname'] ?></h5>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1">Recipient Address:</h5>
                                        <div class="ms-3">
                                            <h5 class="recipient-fname mb-0"><?= $fullAddress ?></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="mb-2">
                                        <h5 class="fw-bold mb-1">Job PO #:</h5>
                                        <div class="ms-3">
                                            <h5 class="recipient-fname mb-0"><?= $order_details['job_po'] ?></h5>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1">Job Name:</h5>
                                        <div class="ms-3">
                                            <h5 class="recipient-fname mb-0"><?= $order_details['job_name'] ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="datatables">
                                <div class="order-details table-responsive text-nowrap">
                                    <table id="est_dtls_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th>Color</th>
                                                <th>Grade</th>
                                                <th>Profile</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-center">In Stock</th>
                                                <th class="text-center">To Manufacture</th>
                                                <th class="text-center">Dimensions</th>
                                                <th class="text-center">Price</th>
                                                <th class="text-center">Customer Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $query = "SELECT * FROM order_product WHERE orderid = '$orderid'";
                                            $result = mysqli_query($conn, $query);
                                            
                                            if ($result && mysqli_num_rows($result) > 0) {
                                                $totalquantity = $total_actual_price = $total_disc_price = 0;
                                                $response = array();
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $orderid = $row['orderid'];
                                                    $product_details = getProductDetails($row['productid']);
                                                    ?> 
                                                    <tr> 
                                                        <td>
                                                            <?php echo getProductName($row['productid']) ?>
                                                        </td>
                                                        <td>
                                                        <div class="d-flex mb-0 gap-8">
                                                            <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_details['color'])?>"></a>
                                                            <?= getColorFromID($product_details['color']); ?>
                                                        </div>
                                                        </td>
                                                        <td>
                                                            <?php echo getGradeName($product_details['grade']); ?>
                                                        </td>
                                                        <td>
                                                            <?php echo getProfileTypeName($product_details['profile']); ?>
                                                        </td>
                                                        <td><?= $row['quantity'] ?></td>
                                                        <td><?= getProductStockTotal($row['productid']) ?></td>
                                                        <td><?= max(0, $row['quantity'] - getProductStockTotal($row['productid']))?></td>
                                                        <td>
                                                            <?php 
                                                            $width = $row['custom_width'];
                                                            $height = $row['custom_height'];
                                                            
                                                            if (!empty($width) && !empty($height)) {
                                                                echo htmlspecialchars($width) . " X " . htmlspecialchars($height);
                                                            } elseif (!empty($width)) {
                                                                echo "Width: " . htmlspecialchars($width);
                                                            } elseif (!empty($height)) {
                                                                echo "Height: " . htmlspecialchars($height);
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
                                                <td colspan="8"></td>
                                                <td colspan="2" class="text-end">
                                                    <p class="m-1">Total Quantity: <?= $totalquantity ?></p>
                                                    <p class="m-1">Actual Price: <?= $total_actual_price ?></p>
                                                    <p class="m-1">Discounted Price: <?= $total_disc_price ?></p>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#est_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Order Details not found"
                },
                autoWidth: true,
                responsive: true,
                lengthChange: false
            });

            $('#viewOrderModal').on('shown.bs.modal', function () {
                $('#est_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
            });
        });
    </script>
    <?php
} 

if($_POST['fetchType'] == 'fetch_estimate_details'){
    $estimateid = mysqli_real_escape_string($conn, $_POST['id']);
    $estimate_details = getEstimateDetails($estimateid);
    $fullAddress = trim(implode(', ', array_filter([
        $estimate_details['deliver_address'] ?? null,
        $estimate_details['deliver_city'] ?? null,
        $estimate_details['deliver_state'] ?? null,
        $estimate_details['deliver_zip'] ?? null,
    ])));
    ?>
        <style>
            #est_dtls_tbl {
                width: 100% !important;
            }

            #est_dtls_tbl td, #est_dtls_tbl th {
                white-space: normal !important;
                word-wrap: break-word;
            }
        </style>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        View Estimate
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="update_product" class="form-horizontal">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="mb-2">
                                            <h5 class="fw-bold mb-1">Recipient Name:</h5>
                                            <div class="ms-3 d-flex gap-1">
                                                <h5 class="recipient-fname mb-0"><?= $estimate_details['deliver_fname'] ?></h5>
                                                <h5 class="recipient-lname mb-0"><?= $estimate_details['deliver_lname'] ?></h5>
                                            </div>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1">Recipient Address:</h5>
                                            <div class="ms-3">
                                                <h5 class="recipient-fname mb-0"><?= $fullAddress ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="mb-2">
                                            <h5 class="fw-bold mb-1">Job PO #:</h5>
                                            <div class="ms-3">
                                                <h5 class="recipient-fname mb-0"><?= $estimate_details['job_po'] ?></h5>
                                            </div>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1">Job Name:</h5>
                                            <div class="ms-3">
                                                <h5 class="recipient-fname mb-0"><?= $estimate_details['job_name'] ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="datatables">
                                    <div class="estimate-details table-responsive text-nowrap">
                                        <table id="est_dtls_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Description</th>
                                                    <th>Color</th>
                                                    <th>Grade</th>
                                                    <th>Profile</th>
                                                    <th class="text-center">Quantity</th>
                                                    <th class="text-center">In Stock</th>
                                                    <th class="text-center">To Manufacture</th>
                                                    <th class="text-center">Dimensions</th>
                                                    <th class="text-center">Price</th>
                                                    <th class="text-center">Customer Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $query = "SELECT * FROM estimate_prod WHERE estimateid = '$estimateid'";
                                                $result = mysqli_query($conn, $query);
                                                if ($result && mysqli_num_rows($result) > 0) {
                                                    $totalquantity = $total_actual_price = $total_disc_price = 0;
                                                    $response = array();
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        $estimateid = $row['estimateid'];
                                                        $product_details = getProductDetails($row['product_id']);
                                                        ?> 
                                                        <tr> 
                                                            <td>
                                                                <?php echo getProductName($row['product_id']) ?>
                                                            </td>
                                                            <td>
                                                            <div class="d-flex mb-0 gap-8">
                                                                <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_details['color'])?>"></a>
                                                                <?= getColorFromID($product_details['color']); ?>
                                                            </div>
                                                            </td>
                                                            <td>
                                                                <?php echo getGradeName($product_details['grade']); ?>
                                                            </td>
                                                            <td>
                                                                <?php echo getProfileTypeName($product_details['profile']); ?>
                                                            </td>
                                                            <td><?= $row['quantity'] ?></td>
                                                            <td><?= getProductStockTotal($row['product_id']) ?></td>
                                                            <td><?= max(0, $row['quantity'] - getProductStockTotal($row['product_id']))?></td>
                                                            <td>
                                                                <?php 
                                                                $width = $row['custom_width'];
                                                                $height = $row['custom_height'];
                                                                
                                                                if (!empty($width) && !empty($height)) {
                                                                    echo htmlspecialchars($width) . " X " . htmlspecialchars($height);
                                                                } elseif (!empty($width)) {
                                                                    echo "Width: " . htmlspecialchars($width);
                                                                } elseif (!empty($height)) {
                                                                    echo "Height: " . htmlspecialchars($height);
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
                                                    <td colspan="8"></td>
                                                    <td colspan="2" class="text-end">
                                                        <p class="m-1">Total Quantity: <?= $totalquantity ?></p>
                                                        <p class="m-1">Actual Price: <?= $total_actual_price ?></p>
                                                        <p class="m-1">Discounted Price: <?= $total_disc_price ?></p>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $('#est_dtls_tbl').DataTable({
                    language: {
                        emptyTable: "Estimate Details not found"
                    },
                    autoWidth: true,
                    responsive: true,
                    lengthChange: false
                });
            });
        </script>

        <?php
    
} 