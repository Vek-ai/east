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

    $query = "
        SELECT 
            c.customer_id,
            CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name,
            o.job_po AS job_po,
            o.job_name AS job_name,
            oe.type AS source_type,
            o.orderid AS id
        FROM order_estimate AS oe
        LEFT JOIN orders AS o ON oe.order_estimate_id = o.orderid AND oe.type = 2
        LEFT JOIN customer AS c ON o.originalcustomerid = c.customer_id
        WHERE oe.type = 2
    ";

    $query .= " UNION ALL ";

    $query .= "
        SELECT 
            c.customer_id,
            CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name,
            e.job_po AS job_po,
            e.job_name AS job_name,
            oe.type AS source_type,
            e.estimateid AS id
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

    if (!empty($filters)) {
        $filter_condition = implode(" AND ", $filters);
        $query = "
            SELECT * FROM (
                ($query)
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
                        <div class="card-body datatables">
                            <div class="order-details table-responsive text-nowrap">
                                <table id="est_dtls_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
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
                                        $orderid = mysqli_real_escape_string($conn, $_POST['id']);
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
                                            <td colspan="6"></td>
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
    <script>
        $(document).ready(function() {
            $('#est_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Order Details not found"
                },
                autoWidth: false,
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
                            <div class="card-body datatables">
                                <div class="estimate-details table-responsive text-nowrap">
                                    <table id="est_dtls_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
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
                                            $estimateid = mysqli_real_escape_string($conn, $_POST['id']);
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
                                                <td colspan="6"></td>
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
        <script>
            $(document).ready(function() {
                $('#est_dtls_tbl').DataTable({
                    language: {
                        emptyTable: "Estimate Details not found"
                    },
                    autoWidth: false,
                    responsive: true,
                    lengthChange: false
                });

                $('#viewEstimateModal').on('shown.bs.modal', function () {
                    $('#est_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
                });
            });
        </script>

        <?php
    
} 