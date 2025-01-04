<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_order_list'])){
    ?>
        <div class="card-body datatables">
            <div class="product-details table-responsive text-nowrap">
                <table id="order_list_tbl" class="table table-hover mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Total Price</th>
                            <th>Discounted Price</th>
                            <th>Order Date</th>
                            <th>Cashier</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 

                        $query = "SELECT * FROM orders WHERE status = '1'";
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
                                    $ <?php echo number_format(getOrderTotals($row["orderid"]),2) ?>
                                </td>
                                <td >
                                    $ <?php echo number_format(getOrderTotalsDiscounted($row["orderid"]),2) ?>
                                </td>
                                <td>
                                    <?php echo date("F d, Y", strtotime($row["order_date"])); ?>
                                </td>
                                <td>
                                    <?= get_staff_name($row["cashier"]) ?>
                                </td>
                                <td>
                                    <a href="javascript:void(0);" class="py-1 pe-1 fs-5" id="view_order_details" data-id="<?php echo $row["orderid"]; ?>" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                            <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="6" class="text-center">No Orders found.</td>
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
            $('#order_list_tbl').DataTable({
                language: {
                    emptyTable: "Orders List not found"
                },
                autoWidth: false,
                responsive: true
            });

            $('#view_order_list_modal').on('shown.bs.modal', function () {
                $('#order_list_tbl').DataTable().columns.adjust().responsive.recalc();
            });
        });
    </script>
    <?php
}