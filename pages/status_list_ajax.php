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

if (isset($_POST['search_status'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);

    $base_query_approval = "
        SELECT 
            'approval' AS source_table, 
            a.approval_id AS id,
            a.status AS status,
            a.total_price AS total_price,
            a.discounted_price AS discounted_price,
            a.discount_percent AS discount_percent,
            a.submitted_date AS submit_date_filter,  
            a.originalcustomerid AS originalcustomerid,
            CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name
        FROM benguetf_eastkentucky.approval AS a
        LEFT JOIN customer AS c ON a.originalcustomerid = c.customer_id
        WHERE 1=1
    ";

    $conditions_approval = "";

    if (!empty($customer_name) && $customer_name != 'All Customers') {
        $customer_condition = "AND CONCAT(c.customer_first_name, ' ', c.customer_last_name) LIKE '%$customer_name%'";
        $conditions_approval .= $customer_condition;
    }

    if (!empty($date_from) && !empty($date_to)) {
        $date_to .= ' 23:59:59';
        $date_condition = "AND (a.submitted_date >= '$date_from' AND a.submitted_date <= '$date_to')";
        $conditions_approval .= $date_condition;
    } else {
        $default_date_from = date('Y-m-d', strtotime('-2 weeks'));
        $date_condition = "AND (a.submitted_date >= '$default_date_from' AND a.submitted_date <= CURDATE())";
        $conditions_approval .= $date_condition;
    }

    $query_approval = $base_query_approval . $conditions_approval;

    $base_query_estimate = "
        SELECT 
            'estimate' AS source_table, 
            e.estimateid AS id,
            e.status AS status,
            e.total_price AS total_price,
            e.discounted_price AS discounted_price,
            e.discount_percent AS discount_percent,
            e.estimated_date AS submit_date_filter,  
            e.originalcustomerid AS originalcustomerid,
            CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name
        FROM benguetf_eastkentucky.estimates AS e
        LEFT JOIN customer AS c ON e.originalcustomerid = c.customer_id
        WHERE 1=1
    ";

    $conditions_estimate = "";

    if (!empty($customer_name) && $customer_name != 'All Customers') {
        $customer_condition = "AND CONCAT(c.customer_first_name, ' ', c.customer_last_name) LIKE '%$customer_name%'";
        $conditions_estimate .= $customer_condition;
    }

    if (!empty($date_from) && !empty($date_to)) {
        $date_to .= ' 23:59:59';
        $date_condition = "AND (e.estimated_date >= '$date_from' AND e.estimated_date <= '$date_to')";
        $conditions_estimate .= $date_condition;
    } else {
        $default_date_from = date('Y-m-d', strtotime('-2 weeks'));
        $date_condition = "AND (e.estimated_date >= '$default_date_from' AND e.estimated_date <= CURDATE())";
        $conditions_estimate .= $date_condition;
    }

    $query_estimate = $base_query_estimate . $conditions_estimate;

    $base_query_order = "
        SELECT 
            'order' AS source_table, 
            o.orderid AS id,
            o.status AS status,
            o.total_price AS total_price,
            o.discounted_price AS discounted_price,
            o.discount_percent AS discount_percent,
            o.order_date AS submit_date_filter,  
            o.originalcustomerid AS originalcustomerid,
            CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name
        FROM benguetf_eastkentucky.orders AS o
        LEFT JOIN customer AS c ON o.originalcustomerid = c.customer_id
        WHERE 1=1
    ";

    $conditions_order = "";

    if (!empty($customer_name) && $customer_name != 'All Customers') {
        $customer_condition = "AND CONCAT(c.customer_first_name, ' ', c.customer_last_name) LIKE '%$customer_name%'";
        $conditions_order .= $customer_condition;
    }

    if (!empty($date_from) && !empty($date_to)) {
        $date_to .= ' 23:59:59';
        $date_condition = "AND (o.order_date >= '$date_from' AND o.order_date <= '$date_to')";
        $conditions_order .= $date_condition;
    }else {
        $default_date_from = date('Y-m-d', strtotime('-2 weeks'));
        $date_condition = "AND (o.order_date >= '$default_date_from' AND o.order_date <= CURDATE())";
        $conditions_order .= $date_condition;
    }

    $query_order = $base_query_order . $conditions_order;

    $full_query = $query_approval . " UNION ALL " . $query_estimate . " UNION ALL " . $query_order;

    $result = $conn->query($full_query);

    if ($result && mysqli_num_rows($result) > 0) {
        $total_amount = 0;
        $total_count = 0;

        ?>
        <table id="sales_table" class="table table-hover mb-0 text-md-nowrap">
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th> </th>
                </tr>
            </thead>
            <tbody>     
            <?php

            while ($row = mysqli_fetch_assoc($result)) {
                $total_amount += $row['discounted_price'];
                $total_count += 1;

                $submit_date_filter = $row['submit_date_filter'];
                $customer_name = $row['customer_name'];
            
                ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($row['id']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars(date("F d, Y", strtotime($submit_date_filter))) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars(date("h:i A", strtotime($submit_date_filter))) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($customer_name) ?>
                    </td>
                    <td>
                        <?= ucfirst($row['source_table']) ?>
                    </td>
                    <td>
                        <a href="javascript:void(0);" class="py-1 pe-1 fs-5" id="view_status_details" data-id="<?php echo $row["id"]; ?>" data-type="<?= $row['source_table'] ?>" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
            <tfoot>
                <td colspan="4" class="text-end">Total: </td>
                <td><?= $total_count ?></td>
                <td></td>
            </tfoot>
        </table>
        <?php
    } else {
        echo "<h4 class='text-center'>No orders found</h4>";
    }
}

if(isset($_POST['fetch_status_details'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    ?>
    <style>
        .tooltip-inner {
            background-color: white !important;
            color: black !important;
            font-size: calc(0.875rem + 2px) !important;
        }
    </style>
    <div class="card card-body datatables">
        <div class="product-details table-responsive text-wrap">
            <h4>Products List</h4>
            <table id="dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll" >
                        </th>
                        <th class="w-20">Description</th>
                        <th>Color</th>
                        <th>Grade</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Width</th>
                        <th class="text-center">Length</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Customer Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 0;
                    $totalquantity = $total_actual_price = $total_disc_price = 0;

                    if($type == 'approval'){
                        $query = "SELECT * FROM approval_product WHERE approval_id='$id'";
                        $result = mysqli_query($conn, $query);
                    }else if($type == 'estimate'){
                        $query = "SELECT * FROM estimate_prod WHERE estimateid='$id'";
                        $result = mysqli_query($conn, $query);
                    }else if($type == 'order'){
                        $query = "SELECT * FROM order_product WHERE orderid='$id'";
                        $result = mysqli_query($conn, $query);
                    }
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            
                            if($type == 'approval'){
                                $data_id = $row['id'];
                                $product_id = $row['productid'];
                                $status = $row['status'];
                                $quantity = $row['quantity'];
                                $width = $row['custom_width'];
                                $bend = $row['custom_bend'];
                                $hem = $row['custom_hem'];
                                $length = $row['custom_length'];
                                $inch = $row['custom_length2'];
                                $actual_price = $row['actual_price'];
                                $discounted_price = $row['discounted_price'];
                            }else if($type == 'estimate'){
                                $data_id = $row['id'];
                                $product_id = $row['product_id'];
                                $status = $row['status'];
                                $quantity = $row['quantity'];
                                $width = $row['custom_width'];
                                $bend = $row['custom_bend'];
                                $hem = $row['custom_hem'];
                                $length = $row['custom_length'];
                                $inch = $row['custom_length2'];
                                $actual_price = $row['actual_price'];
                                $discounted_price = $row['discounted_price'];
                            }else if($type == 'order'){
                                $data_id = $row['id'];
                                $product_id = $row['productid'];
                                $status = $row['status'];
                                $quantity = $row['quantity'];
                                $width = $row['custom_width'];
                                $bend = $row['custom_bend'];
                                $hem = $row['custom_hem'];
                                $length = $row['custom_length'];
                                $inch = $row['custom_length2'];
                                $actual_price = $row['actual_price'];
                                $discounted_price = $row['discounted_price'];
                            }

                            $product_details = getProductDetails($product_id);

                            if($quantity > 0){
                            ?>
                            <tr>
                                <td class="text-start">
                                    <?php
                                    if($product_details['product_origin'] == '1'){
                                        ?>
                                        <input type="checkbox" class="row-select" data-id="<?= $data_id ?>" data-type="<?=$type?>">
                                        <?php
                                    }
                                    ?>
                                    
                                </td>
                                <td class="text-wrap w-20" > 
                                    <?php echo getProductName($product_id) ?>
                                </td>
                                <td>
                                <div class="d-flex mb-0 gap-8 align-items-center">
                                    <span class="rounded-circle d-block p-3" 
                                        style="background-color: <?= getColorHexFromProdID($product_id) ?>; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center;">
                                    </span>
                                    <span>
                                        <?= getColorFromID($product_id); ?>
                                    </span>
                                </div>
                                </td>
                                <td>
                                    <?php echo getGradeFromID($product_id); ?>
                                </td>
                                <td>
                                    <?php echo $quantity; ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    if (!empty($width)) {
                                        echo htmlspecialchars($width);
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    if (!empty($length)) {
                                        echo htmlspecialchars($length) . " ft";
                                        
                                        if (!empty($inch)) {
                                            echo " " . htmlspecialchars($inch) . " in";
                                        }
                                    } elseif (!empty($inch)) {
                                        echo htmlspecialchars($inch) . " in";
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        switch ($status) {
                                            case 0:
                                                $statusText = 'Pending';
                                                $statusColor = '#007bff';
                                                break;
                                            case 1:
                                                $statusText = 'Manufacturing';
                                                $statusColor = '#ffc107';
                                                break;
                                            case 2:
                                                $statusText = 'Waiting For Dispatch';
                                                $statusColor = '#28a745';
                                                break;
                                            case 3:
                                                $statusText = 'Dispatched';
                                                $statusColor = '#65c466';
                                                break;
                                            case 4:
                                                $statusText = 'Delivered';
                                                $statusColor = '#28a745';
                                                break;
                                            default:
                                                $statusText = 'Unknown';
                                                $statusColor = '#6c757d';
                                                break;
                                        }
                                    ?>
                                    <span class="badge" style="background-color: <?= $statusColor; ?>"><?= $statusText; ?></span>
                                </td>
                                <td class="text-end">$ <?= number_format($actual_price,2) ?></td>
                                <td class="text-end">$ <?= number_format($discounted_price,2) ?></td>
                            </tr>
                    <?php
                            $totalquantity += $quantity ;
                            $total_actual_price += $actual_price;
                            $total_disc_price += $discounted_price;
                            }
                        }
                    }
                    ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td class="text-end" colspan="6">Total Qty</td>
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
            let selectedProduct = [];
            var type = '<?= $type ?? '' ?>';

            $(document).off('change', '.row-select').on('change', '.row-select', function () {
                const prodId = $(this).data('id');

                if ($(this).is(':checked')) {
                    if (!selectedProduct.includes(prodId)) {
                        selectedProduct.push(prodId);
                    }
                } else {
                    selectedProduct = selectedProduct.filter(id => id !== prodId);
                }
            });

            $('#selectAll').off('change').on('change', function () {
                const isChecked = $(this).is(':checked');
                const table = $('#dtls_tbl').DataTable();
                const allRows = table.rows().nodes();

                $(allRows).find('.row-select').prop('checked', isChecked).trigger('change');
            });

            $('#saveSelection').off('click').on('click', function () {
                const table = $('#dtls_tbl').DataTable();
                const allRows = table.rows().nodes();

                const id = <?= $id ?? 0 ?>;
                
                selectedProduct = [];

                $(allRows).find('.row-select:checked').each(function () {
                    selectedProduct.push($(this).data('id'));
                });

                const selectedProductJson = JSON.stringify(selectedProduct);

                $.ajax({
                    type: 'POST',
                    url: 'pages/status_list_ajax.php',
                    data: { 
                        id: id,
                        type: type,
                        selected_products: selectedProductJson,
                        assign_dispatch: 'assign_dispatch'
                    },
                    success: function(response) {
                        if (response.trim() == 'success') {
                            alert('Successfully Saved!');
                            location.reload();
                        } else {
                            alert('Failed to Update!' +response);
                            console.log(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Response Text:', xhr.responseText);
                    }
                });
            });

            $('[data-toggle="tooltip"]').tooltip(); 

            $('#dtls_tbl').DataTable({
                language: {
                    emptyTable: "Products not found"
                },
                autoWidth: false,
                responsive: true
            });

            $('#view_status_details_modal').on('shown.bs.modal', function () {
                $('#dtls_tbl').DataTable().columns.adjust().responsive.recalc();
            });
        });
    </script>
    <?php
}

if (isset($_POST['assign_dispatch'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $selected_products = json_decode($_POST['selected_products'], true);

    if (is_array($selected_products) && !empty($selected_products)) {
        $table = '';
        if ($type == 'approval') {
            $table = 'approval_product';
        } elseif ($type == 'estimate') {
            $table = 'estimate_prod';
        } elseif ($type == 'order') {
            $table = 'order_product';
        }

        if ($table) {
            $ids = implode(',', array_map('intval', $selected_products)); // Safely process the IDs
            $sql = "UPDATE $table SET status = '3' WHERE id IN ($ids)";

            if ($conn->query($sql) === TRUE) {
                echo "success";
            } else {
                echo "Error updating records: " . $conn->error;
            }
        } else {
            echo "Invalid type specified.";
        }
    } else {
        echo "No products selected for dispatch.";
    }
}



