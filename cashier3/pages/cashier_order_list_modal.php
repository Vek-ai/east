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

if(isset($_POST['fetch_order_list'])){
    $customerid = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $orderid = mysqli_real_escape_string($conn, $_POST['orderid']);
    ?>
        <div class="card-body datatables">
            <div class="product-details table-responsive text-nowrap">
                <table id="order_list_tbl" class="table table-hover mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th class="d-none">Product Name (Hidden)</th>
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

                        $query = "
                            SELECT o.*, CONCAT(c.customer_first_name, ' ', c.customer_last_name) AS customer_name
                            FROM orders AS o
                            LEFT JOIN customer AS c ON c.customer_id = o.originalcustomerid
                            WHERE 1 = 1 AND o.status != 6
                        ";

                        if (!empty($customerid) && $customer_name != 'All Customers') {
                            $query .= " AND o.customerid = '$customerid' ";
                        }

                        if (!empty($orderid)) {
                            $query .= " AND orderid = '$orderid' ";
                        }

                        $query .= " ORDER BY o.order_date DESC";

                        $result = mysqli_query($conn, $query);
                    
                        if ($result && mysqli_num_rows($result) > 0) {
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                                $orderid = $row['orderid'];

                                $orderProducts = getReturnableProducts($orderid);
                                $productNames = array_map(function($prod) {
                                    return getProductName($prod['productid']);
                                }, $orderProducts);

                                $hiddenProductNames = implode(', ', $productNames);

                            ?>
                            <tr>
                                <td class="d-none">
                                    <?php echo htmlspecialchars($hiddenProductNames); ?>
                                </td>
                                <td>
                                    <?php echo get_customer_name($row["customerid"]) ?>
                                </td>
                                <td >
                                    $ <?php echo number_format(floatval(getOrderTotals($row["orderid"])),2) ?>
                                </td>
                                <td >
                                    $ <?php echo number_format(floatval(getOrderTotalsDiscounted($row["orderid"])),2) ?>
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
                    emptyTable: "No products available for return"
                },
                columnDefs: [
                    { targets: 0, visible: false },
                    { width: "20%", targets: 1 },
                    { width: "15%", targets: [2, 3, 4] },
                    { width: "10%", targets: [5, 6] }
                ],
                autoWidth: false,
                responsive: true,
                order: []
            });
        });
    </script>
    <?php
}