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

if(isset($_POST['fetch_est_list'])){
    ?>
        <style>
            .tooltip-inner {
                background-color: white !important;
                color: black !important;
                font-size: calc(0.875rem + 2px) !important;
            }
        </style>
        <div class="card-body datatables">
            <div class="product-details table-responsive text-nowrap">
                <table id="est_list_tbl" class="table table-hover mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Total Price</th>
                            <th>Discounted Price</th>
                            <th>Estimate Date</th>
                            <th>Order Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 

                        $query = "SELECT * FROM estimates WHERE status = '1'";
                        $result = mysqli_query($conn, $query);
                    
                        if ($result && mysqli_num_rows($result) > 0) {
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td>
                                    <?php echo get_customer_name($row["customerid"]) ?>
                                </td>
                                <td >
                                    $ <?php echo number_format($row["total_price"],2) ?>
                                </td>
                                <td >
                                    $ <?php echo number_format($row["discounted_price"],2) ?>
                                </td>
                                <td>
                                    <?php echo date("F d, Y", strtotime($row["estimated_date"])); ?>
                                </td>
                                <td>
                                    <?php 
                                        if (isset($row["order_date"]) && !empty($row["order_date"]) && $row["order_date"] !== '0000-00-00 00:00:00') {
                                            echo date("F d, Y", strtotime($row["order_date"]));
                                        } else {
                                            echo '';
                                        }
                                    ?>
                                </td>
                                <td>
                                    <a href="javascript:void(0);" class="py-1 pe-1 fs-5" id="view_est_details" data-id="<?php echo $row["estimateid"]; ?>" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a>
                                    <a href="javascript:void(0);" class="py-1 pe-1 fs-5" id="load_estimate" data-id="<?php echo $row["estimateid"]; ?>" data-toggle="tooltip" data-placement="top" title="Order"><i class="fa fa-cart-arrow-down text-success"></i></i></a>
                                </td>
                            </tr>
                            <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="6" class="text-center">No Estimates found.</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip(); 

            $('#est_list_tbl').DataTable({
                language: {
                    emptyTable: "Estimate List not found"
                },
                autoWidth: false,
                responsive: true
            });

            $('#view_est_details_modal').on('shown.bs.modal', function () {
                $('#est_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
            });
        });
    </script>
    <?php
}