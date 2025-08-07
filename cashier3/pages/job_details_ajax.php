<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

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

                            if($quantity > 0){
                            ?>
                            <tr>
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
                        <td class="text-end" colspan="5">Total Qty</td>
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


