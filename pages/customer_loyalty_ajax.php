<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_view_modal") {
        $loyalty_id = mysqli_real_escape_string($conn, $_POST['id']);
        $query="SELECT * FROM loyalty_program WHERE loyalty_id = '$loyalty_id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            ?>
            <style>
                #customer_loyalty_tbl {
                    width: 100% !important;
                }

                #customer_loyalty_tbl td, #customer_loyalty_tbl th {
                    white-space: normal !important;
                    word-wrap: break-word;
                }
            </style>
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            View Customers in <?= $row['loyalty_program_name'] ?> Category
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div id="update_product" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body datatables">
                                    <div class="estimate-details table-responsive text-nowrap">
                                        <table id="customer_loyalty_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Customer Name</th>
                                                    <th>Contact Number</th>
                                                    <th>Contact Email</th>
                                                    <th>Accumulated Orders</th>
                                                    <th>Last Order Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $query_orders = "
                                                    SELECT customerid, SUM(discounted_price) AS total_orders, MAX(order_date) AS last_order_date
                                                    FROM orders
                                                    GROUP BY customerid
                                                    HAVING SUM(discounted_price) >= '" .$row['accumulated_total_orders'] ."'
                                                ";
                                                $result_orders = mysqli_query($conn, $query_orders);
                                                while ($row_orders = mysqli_fetch_assoc($result_orders)) {
                                                    $customerid = $row_orders['customerid'];
                                                    $customer_details = getCustomerDetails($customerid);
                                                    ?> 
                                                    <tr> 
                                                        <td>
                                                            <?= $customer_details['customer_first_name'] .' ' .$customer_details['customer_last_name'] ?>
                                                        </td>
                                                        <td>
                                                            <?= $customer_details['contact_phone']?>
                                                        </td>
                                                        <td>
                                                            <?= $customer_details['contact_email']?>
                                                        </td>
                                                        <td class="text-end">
                                                            $ <?= number_format($row_orders['total_orders'],2) ?>
                                                        </td>
                                                        <td>
                                                            <?= date("F d, Y", strtotime($row_orders['last_order_date'])) ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
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
                    $('#customer_loyalty_tbl').DataTable({
                        language: {
                            emptyTable: "No customer found on this loyalty category"
                        },
                        autoWidth: false,
                        responsive: true,
                        lengthChange: false
                    });
                });
            </script>

            <?php
        }
    } 
    
    mysqli_close($conn);
}
?>
