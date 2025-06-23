<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/send_email.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_balance_modal") {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $customer_details = getCustomerDetails($customer_id);
        $query = "SELECT * FROM orders WHERE credit_amt > 0 AND customerid = '$customer_id'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $response = array();
            ?>
            <div class="card">
                <div class="card-body datatables">
                    <h5>Balance Due for <?= get_customer_name($customer_id) ?></h5>
                    <div class="estimate-details table-responsive text-nowrap">
                        <table id="balance_due_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Cashier</th>
                                    <th>Order Date</th>
                                    <th class="text-end">Balance Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $ttl_amount_due = 0;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $orderid = $row['orderid'];
                                        $amount_due = $row['credit_amt'];
                                        $ttl_amount_due += $amount_due;
                                ?> 
                                <tr> 
                                    <td><?= $orderid ?></td>
                                    <td><?= get_staff_name($row['cashier']); ?></td>
                                    <td><?= date("m d, Y", strtotime($row['order_date'])) ?></td>
                                    <td class="text-end">$ <?= number_format($amount_due,2) ?></td>
                                </tr>
                                <?php
                                    }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end">Total</td>
                                    <td class="text-end">$ <?= number_format($ttl_amount_due,2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <?php
        }
    } 
    
    mysqli_close($conn);
}
?>
