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

if (isset($_POST['search_approval'])) {
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);

    $query = "
        SELECT a.*, CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name
        FROM approval AS a
        LEFT JOIN customer AS c ON c.customer_id = a.originalcustomerid
        WHERE a.status = '1'
    ";

    if (!empty($customer_name) && $customer_name != 'All Customers') {
        $query .= " AND CONCAT(c.customer_first_name, ' ', c.customer_last_name) LIKE '%$customer_name%' ";
    }

    if (!empty($date_from) && !empty($date_to)) {
        $date_to .= ' 23:59:59';
        $query .= " AND (a.submitted_date >= '$date_from' AND a.submitted_date <= '$date_to') ";
    }else{
        $query .= " AND (a.submitted_date >= DATE_SUB(curdate(), INTERVAL 2 WEEK) AND a.submitted_date <= NOW()) ";
    }

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $total_amount = 0;
        $total_count = 0;

        ?>
        <table id="sales_table" class="table table-hover mb-0 text-md-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Submission Date</th>
                    <th>Submission Time</th>
                    <th>Cashier</th>
                    <th>Customer</th>
                    <th class="text-center">Type of Approval</th>
                    <th class="text-end">Amount</th>
                    <th> </th>
                </tr>
            </thead>
            <tbody>     
            <?php

            while ($row = mysqli_fetch_assoc($result)) {
                $total_amount += $row['discounted_price'];
                $total_count += 1;
                $type_approval = $row['type_approval'];
                $submitted_date = $row['submitted_date'];
                $customer_name = $row['customer_name'];
            
                switch ($type_approval) {
                    case 1:
                        $badge_html = '<span class="badge bg-warning text-dark">Discount</span>';
                        break;
                    case 2:
                        $badge_html = '<span class="badge bg-danger">Net 30 Exceeded</span>';
                        break;
                    case 3:
                        $badge_html = '<span class="badge bg-info text-dark">Customer Order</span>';
                        break;
                    default:
                        $badge_html = '<span class="badge bg-secondary">Unknown</span>';
                        break;
                }
                ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($row['approval_id']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars(date("F d, Y", strtotime($submitted_date))) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars(date("h:i A", strtotime($submitted_date))) ?>
                    </td>
                    <td>
                        <?= get_staff_name($row['cashier']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($customer_name) ?>
                    </td>
                    <td class="text-center">
                        <?= $badge_html ?>
                    </td>
                    <td class="text-end">
                        $ <?= number_format($row['discounted_price'], 2) ?>
                    </td>
                    <td>
                        <a href="?page=approval_details&id=<?= $row["approval_id"] ?>" title="View" target="_blank" class="py-1 pe-1 fs-5" data-id="<?php echo $row["approval_id"]; ?>" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
            <tfoot>
                <td colspan="3" class="text-end">Total Submissions: </td>
                <td><?= $total_count ?></td>
                <td colspan="2" class="text-end">Total Amount: </td>
                <td class="text-end">$ <?= $total_amount ?></td>
                <td></td>
            </tfoot>
        </table>
        <?php
    } else {
        echo "<h4 class='text-center'>No Requests found</h4>";
    }
}

if(isset($_POST['fetch_available'])){
    $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
    ?>
    <style>
        .tooltip-inner {
            font-size: calc(0.875rem + 2px) !important;
        }
    </style>
    <div class="card card-body datatables">
        <div class="product-details table-responsive text-wrap">
            <h4>Coils List</h4>
            <table id="coil_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
                <thead>
                    <tr>
                        <th>Coil No</th>
                        <th class="text-center">Warehouse</th>
                        <th class="text-center">Color</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Gauge</th>
                        <th class="text-center">Coating</th>
                        <th class="text-right">Rem. Feet</th>
                        <th class="text-right">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $query = "SELECT * FROM coil_product WHERE color_sold_as='$color_id'";
                    $result = mysqli_query($conn, $query);
                    $totalprice = 0;
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $color_details = getColorDetails($row['color_sold_as']);
                            ?>
                            <tr data-id="<?= $product_id ?>">
                                <td class="text-wrap"> 
                                    <?= $row['coil_no'] ?>
                                </td>
                                <td class="text-wrap"> 
                                    <?= getWarehouseName($row['coil_no']) ?>
                                </td>
                                <td>
                                <div class="d-inline-flex align-items-center gap-2">
                                    <span class="rounded-circle d-block" style="background-color:<?= $color_details['color_code'] ?>; width: 20px; height: 20px;"></span>
                                    <?= $color_details['color_name'] ?>
                                </div>
                                </td>
                                <td>
                                    <?php echo getGradeFromID($product_id); ?>
                                </td>
                                <td>
                                    <?php echo getGaugeName($product_id); ?>
                                </td>
                                <td>
                                    <?php echo $row['coating']; ?>
                                </td>
                                <td class="text-right">
                                    <?php echo $row['remaining_feet']; ?>
                                </td>
                                <td class="text-right">
                                    $<?php echo $row['price']; ?>
                                </td>
                            </tr>
                            <?php
                            $totalprice += $row['price'] ;
                            $no++;
                        }

                        $average_price = $totalprice / $no;
                    }
                    ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="7">Average Price</td>
                        <td class="text-end">$ <?= number_format($average_price,2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
       
    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip(); 

            if ($.fn.DataTable.isDataTable('#coil_dtls_tbl')) {
                $('#coil_dtls_tbl').DataTable().draw();
            } else {
                $('#coil_dtls_tbl').DataTable({
                    language: {
                        emptyTable: "No Available Coils with the selected color"
                    },
                    autoWidth: false,
                    responsive: true
                });
            }

            $('#view_available_modal').on('shown.bs.modal', function () {
                $('#coil_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
            });
        });
    </script>
    <?php
}

if(isset($_POST['fetch_order_details'])){
    $approval_id = mysqli_real_escape_string($conn, $_POST['approval_id']);
    ?>
    <style>
        .tooltip-inner {
            font-size: calc(0.875rem + 2px) !important;
        }
    </style>
    <div class="card card-body datatables">
        <div class="product-details table-responsive text-wrap">
            <h4>Items List</h4>
            <table id="approval_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
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
                    $no = 0;
                    $query = "SELECT * FROM approval_product WHERE approval_id='$approval_id'";
                    $result = mysqli_query($conn, $query);
                    $totalquantity = $total_actual_price = $total_disc_price = 0;
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $color_details = getColorDetails($row['custom_color']);
                            if($row['quantity'] > 0){
                            ?>
                            <tr data-id="<?= $product_id ?>">
                                <td class="text-wrap"> 
                                    <?php echo getProductName($product_id) ?>
                                </td>
                                <td>
                                <div class="d-inline-flex align-items-center gap-2">
                                    <a href="javascript:void(0)" id="viewAvailableBtn" data-color="<?= $row['custom_color'] ?>" data-product="<?= $row['productid'] ?>" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                        <span class="rounded-circle d-block" style="background-color:<?= $color_details['color_code'] ?>; width: 20px; height: 20px;"></span>
                                        <?= $color_details['color_name'] ?>
                                    </a>
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

            $('#approval_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Submission Details not found"
                },
                autoWidth: false,
                responsive: true
            });

            $('#view_approval_details_modal').on('shown.bs.modal', function () {
                $('#approval_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
            });
        });
    </script>
    <?php
}


