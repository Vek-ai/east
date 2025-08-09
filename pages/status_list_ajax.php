<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

$permission = $_SESSION['permission'];

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
                        <th></th>
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
                            $is_show_checkbox = false;
                            if($type == 'approval'){
                                $data_id = $row['id'];
                                $product_id = $row['productid'];
                                $status = $row['status'];
                                $custom_color = $row['custom_color'];
                                $custom_grade = $row['custom_grade'];
                                $quantity = $row['quantity'];
                                $width = $row['custom_width'];
                                $bend = $row['custom_bend'];
                                $hem = $row['custom_hem'];
                                $length = $row['custom_length'];
                                $inch = $row['custom_length2'];
                                $actual_price = $row['actual_price'];
                                $discounted_price = $row['discounted_price'];

                                $product_details = getProductDetails($product_id);
                                if($product_details['product_origin'] == '1'){
                                    $is_show_checkbox = true;
                                }else{
                                    $query_work_orders = "SELECT * FROM work_order_product WHERE type='3' AND work_order_id='$id' AND productid='$product_id'";
                                    $result_work_orders = mysqli_query($conn, $query_work_orders);
                                    if (mysqli_num_rows($result_work_orders) > 0) {
                                        $is_show_checkbox = true;
                                    } 
                                }
                            }else if($type == 'estimate'){
                                $data_id = $row['id'];
                                $product_id = $row['product_id'];
                                $status = $row['status'];
                                $custom_color = $row['custom_color'];
                                $custom_grade = $row['custom_grade'];
                                $quantity = $row['quantity'];
                                $width = $row['custom_width'];
                                $bend = $row['custom_bend'];
                                $hem = $row['custom_hem'];
                                $length = $row['custom_length'];
                                $inch = $row['custom_length2'];
                                $actual_price = $row['actual_price'];
                                $discounted_price = $row['discounted_price'];

                                $product_details = getProductDetails($product_id);
                                if($product_details['product_origin'] == '1'){
                                    $is_show_checkbox = true;
                                }else{
                                    $query_work_orders = "SELECT * FROM work_order_product WHERE type='1' AND work_order_id='$id' AND productid='$product_id'";
                                    $result_work_orders = mysqli_query($conn, $query_work_orders);
                                    if (mysqli_num_rows($result_work_orders) > 0) {
                                        $is_show_checkbox = true;
                                    } 
                                }
                            }else if($type == 'order'){
                                $data_id = $row['id'];
                                $product_id = $row['productid'];
                                $status = $row['status'];
                                $custom_color = $row['custom_color'];
                                $custom_grade = $row['custom_grade'];
                                $quantity = $row['quantity'];
                                $width = $row['custom_width'];
                                $bend = $row['custom_bend'];
                                $hem = $row['custom_hem'];
                                $length = $row['custom_length'];
                                $inch = $row['custom_length2'];
                                $actual_price = $row['actual_price'];
                                $discounted_price = $row['discounted_price'];

                                $product_details = getProductDetails($product_id);
                                if($product_details['product_origin'] == '1'){
                                    $is_show_checkbox = true;
                                }else{
                                    $query_work_orders = "SELECT * FROM work_order_product WHERE type='2' AND work_order_id='$id' AND productid='$product_id'";
                                    $result_work_orders = mysqli_query($conn, $query_work_orders);
                                    if (mysqli_num_rows($result_work_orders) > 0) {
                                        $is_show_checkbox = true;
                                    } 
                                }
                            }
                            
                            if($quantity > 0){
                            ?>
                            <tr>
                                <td class="text-start">
                                    <?php
                                    if($is_show_checkbox){
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
                                        style="background-color: <?= getColorHexFromColorID($custom_color) ?>; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center;">
                                    </span>
                                    <span>
                                        <?= getColorName($custom_color); ?>
                                    </span>
                                </div>
                                </td>
                                <td>
                                    <?php echo getGradeName($custom_grade); ?>
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
                                <td>
                                    <?php                                                    
                                    if ($permission === 'edit') {
                                    ?>
                                    <a href="javascript:void(0);" class="py-1 pe-1 fs-5" id="edit_status_details" data-id="<?= $data_id ?>" data-type="<?= $type ?>" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fa fa-pencil"></i></a>
                                    <?php
                                    }
                                    ?>

                                    <a href="javascript:void(0);" class="py-1 pe-1 fs-5" id="edit_history_details" data-id="<?= $data_id ?>" data-type="<?= $type ?>" data-toggle="tooltip" data-placement="top" title="History"><i class="fa fa-clock-rotate-left"></i></i></a>
                                </td>
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
                        <td></td>
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
                            window.open("print_order_delivery.php?id=<?= $id ?? 0 ?>&type=<?= $type ?? 0 ?>", "_blank");
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

if(isset($_POST['fetch_edit_details'])){
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
        <div class="product-details text-wrap">
            <form id="formEditProduct">
            <?php 
                $no = 0;
                $totalquantity = $total_actual_price = $total_disc_price = 0;

                if($type == 'approval'){
                    $query = "SELECT * FROM approval_product WHERE id='$id'";
                    $result = mysqli_query($conn, $query);
                }else if($type == 'estimate'){
                    $query = "SELECT * FROM estimate_prod WHERE id='$id'";
                    $result = mysqli_query($conn, $query);
                }else if($type == 'order'){
                    $query = "SELECT * FROM order_product WHERE id='$id'";
                    $result = mysqli_query($conn, $query);
                }
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $response = array();
                    while ($row = mysqli_fetch_assoc($result)) {
                        $is_show_checkbox = false;
                        if($type == 'approval'){
                            $data_id = $row['id'];
                            $product_id = $row['productid'];
                            $status = $row['status'];
                            $custom_color = $row['custom_color'];
                            $custom_grade = $row['custom_grade'];
                            $quantity = $row['quantity'];
                            $width = $row['custom_width'];
                            $bend = $row['custom_bend'];
                            $hem = $row['custom_hem'];
                            $feet = $row['custom_length'];
                            $inch = $row['custom_length2'];
                        }else if($type == 'estimate'){
                            $data_id = $row['id'];
                            $product_id = $row['product_id'];
                            $status = $row['status'];
                            $custom_color = $row['custom_color'];
                            $custom_grade = $row['custom_grade'];
                            $quantity = $row['quantity'];
                            $width = $row['custom_width'];
                            $bend = $row['custom_bend'];
                            $hem = $row['custom_hem'];
                            $feet = $row['custom_length'];
                            $inch = $row['custom_length2'];
                        }else if($type == 'order'){
                            $data_id = $row['id'];
                            $product_id = $row['productid'];
                            $status = $row['status'];
                            $custom_color = $row['custom_color'];
                            $custom_grade = $row['custom_grade'];
                            $quantity = $row['quantity'];
                            $width = $row['custom_width'];
                            $bend = $row['custom_bend'];
                            $hem = $row['custom_hem'];
                            $feet = $row['custom_length'];
                            $inch = $row['custom_length2'];
                        }

                        $product_details = getProductDetails($product_id);
                        ?>
                        <input type="hidden" id="id" name="id" value="<?=$data_id?>">
                        <input type="hidden" id="type" name="type" value="<?=$type?>">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <h4><?=$product_details['product_item']?></h4>
                                    <h5>Grade: <?= getGradeName($row['custom_grade']) ?></h5>
                                    <h5>
                                        <?php
                                        if (!empty($bend)) {
                                            echo "Bend: " . htmlspecialchars($bend) . "<br>";
                                        }
                                        
                                        if (!empty($hem)) {
                                            echo "Hem: " . htmlspecialchars($hem) . "<br>";
                                        }
                                        ?>
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Color</label>
                                <div class="mb-3">
                                    <select id="color" class="form-control select2" name="color">
                                        <option value="" >Select Color...</option>
                                        <?php
                                        $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                        $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                        while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                            $selected = ($custom_color == $row_paint_colors['color_id']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?>><?= $row_paint_colors['color_name'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Width</label>
                                    <input type="text" id="width" name="width" class="form-control" value="<?= $width?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Length Feet</label>
                                    <input type="text" id="length_feet" name="length_feet" class="form-control" value="<?= $feet ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Length Inch</label>
                                    <input type="text" id="length_inch" name="length_inch" class="form-control" value="<?= $inch ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn ripple btn-success" type="submit">Save</button>
                            <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                        </div>
                        <?php
                    }
                }
                ?>
            </form>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip(); 

            $(".select2").select2({
                width: '100%',
                placeholder: "Select Correlated Products",
                allowClear: true,
                dropdownParent: $('#edit_details_modal')
            });
        });
    </script>
    <?php
}


if (isset($_POST['edit_product'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $width = mysqli_real_escape_string($conn, $_POST['width']);
    $length_feet = mysqli_real_escape_string($conn, $_POST['length_feet']);
    $length_inch = mysqli_real_escape_string($conn, $_POST['length_inch']);

    $table = '';
    $prod_details = array();
    $main_id = 0;
    $product_id = 0;
    if ($type == 'approval') {
        $table = 'approval_product';
        $prod_details = getApprovalProductDetails($id);
        $main_id = $prod_details['approval_id'];
        $product_id = $prod_details['productid'];
    } elseif ($type == 'estimate') {
        $table = 'estimate_prod';
        $prod_details = getEstimateProdDetails($id);
        $main_id = $prod_details['estimateid'];
        $product_id = $prod_details['product_id'];
    } elseif ($type == 'order') {
        $table = 'order_product';
        $prod_details = getOrderProdDetails($id);
        $main_id = $prod_details['orderid'];
        $product_id = $prod_details['productid'];
    }

    if (!empty($table) && !empty($id)) {
        $sql_color = "UPDATE $table SET custom_color = '$color' WHERE id = '$id'";
        if ($conn->query($sql_color) === TRUE) {
            $color_name = getColorName($color);
            if ($type == 'approval') {
                log_approval_changes($main_id, $product_id, "Updated Color to $color_name", $id);
            } elseif ($type == 'estimate') {
                log_estimate_changes($main_id, $product_id, "Updated Color to $color_name", $id);
            } elseif ($type == 'order') {
                log_order_changes($main_id, $product_id, "Updated Color to $color_name", $id);
            }
            
        } else {
            echo "Error updating Color: " . $conn->error;
        }

        $sql_width = "UPDATE $table SET custom_width = '$width' WHERE id = '$id'";
        if ($conn->query($sql_width) === TRUE) {
            if ($type == 'approval') {
                log_approval_changes($main_id, $product_id, "Updated Width to $width" , $id);
            } elseif ($type == 'estimate') {
                log_estimate_changes($main_id, $product_id, "Updated Width to $width" , $id);
            } elseif ($type == 'order') {
                log_order_changes($main_id, $product_id, "Updated Width to $width" , $id);
            }
            
        } else {
            echo "Error updating Width: " . $conn->error;
        }

        $length = $length_feet . "ft " . $length_inch . "in";
        $sql_length = "UPDATE $table SET custom_length = '$length_feet', custom_length2 = '$length_inch'  WHERE id = '$id'";
        if ($conn->query($sql_length) === TRUE) {
            if ($type == 'approval') {
                log_approval_changes($main_id, $product_id, "Updated Length to $length" , $id);
            } elseif ($type == 'estimate') {
                log_estimate_changes($main_id, $product_id, "Updated Length to $length" , $id);
            } elseif ($type == 'order') {
                log_order_changes($main_id, $product_id, "Updated Length to $length" , $id);
            }
            
        } else {
            echo "Error updating Length: " . $conn->error;
        }

        echo "success";
    } else {
        echo "Invalid type or missing ID. table: $table, Type: $type";
    }
}

if (isset($_POST['fetch_changes_modal'])) {
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
    <div class="card">
        <div class="card-body datatables">
            <div class="table-responsive text-nowrap">
                <table id="changes_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Action</th>
                            <th>User</th>
                            <th>Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $table_id = mysqli_real_escape_string($conn, $_POST['id']);
                        $type = mysqli_real_escape_string($conn, $_POST['type']);

                        if($type == 'approval'){
                            $query = "SELECT * FROM approval_changes WHERE approval_product_id = '$table_id'";
                        }else if($type == 'estimate'){
                            $query = "SELECT * FROM estimate_changes WHERE estimate_prod_id = '$table_id'";
                        }else if($type == 'order'){
                            $query = "SELECT * FROM order_changes WHERE order_product_id = '$table_id'";
                        }

                        
                        $result = mysqli_query($conn, $query);
                        
                        if ($result && mysqli_num_rows($result) > 0) {
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                                $table_id = $row['orderid'];
                                $product_details = getProductDetails($row['product_id']);
                            ?> 
                                <tr> 
                                    <td>
                                        <?php echo getProductName($row['product_id']) ?>
                                    </td>
                                    <td>
                                        <?php echo $row['action'] ?>
                                    </td>
                                    <td>
                                        <?php echo get_staff_name($row['user']) ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if (isset($row["date_changed"]) && !empty($row["date_changed"]) && $row["date_changed"] !== '0000-00-00 00:00:00') {
                                                echo date("m/d/Y", strtotime($row["date_changed"]));
                                            } else {
                                                echo '';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if (isset($row["date_changed"]) && !empty($row["date_changed"]) && $row["date_changed"] !== '0000-00-00 00:00:00') {
                                                echo date("h:i A", strtotime($row["date_changed"]));
                                            } else {
                                                echo '';
                                            }
                                        ?>
                                    </td>
                                    
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#changes_tbl').DataTable({
                language: {
                    emptyTable: "No Changes Recorded"
                },
                autoWidth: false,
                responsive: true,
                lengthChange: false
            });
        });
    </script>
    <?php
} 



