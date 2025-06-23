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

    if ($action == "fetch_credit_modal") {
        $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
        $customer_details = getCustomerDetails($customer_id);

        $query = "
            SELECT 
                j.job_id,
                j.job_name,
                j.location,
                j.constructor_name,
                j.constructor_contact,
                d.deposit_amount,
                d.deposited_by,
                d.reference_no,
                d.type,
                d.check_no,
                d.created_at
            FROM jobs j
            INNER JOIN job_deposits d ON d.job_id = j.job_id
            WHERE j.customer_id = '$customer_id'
            ORDER BY j.job_id, d.created_at DESC
        ";
        
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $response = array();

            $jobs = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $job_id = $row['job_id'];
                $jobs[$job_id]['info'] = [
                    'job_name' => $row['job_name'],
                    'location' => $row['location'],
                    'constructor_name' => $row['constructor_name'],
                    'constructor_contact' => $row['constructor_contact']
                ];
                $jobs[$job_id]['deposits'][] = $row;
            }
            ?>

            <div class="card">
                <div class="card-body datatables">
                    <h5>Credit Available for <?= get_customer_name($customer_id) ?></h5>

                    <?php 
                    $grand_total = 0;
                    foreach ($jobs as $job_id => $job_data): ?>
                        <div class="mb-4 border p-3 rounded">
                            <h5>
                                <?= $job_data['info']['job_name'] ?>
                            </h5>
                            <h6 class="mb-2">
                                <strong>Contractor:</strong> <?= $job_data['info']['constructor_name'] ?> <br>
                                <strong>Contact:</strong> <?= $job_data['info']['constructor_contact'] ?>
                            </h6>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0 w-100">
                                    <thead>
                                        <tr>
                                            <th>Reference No</th>
                                            <th>Type</th>
                                            <th>Check No</th>
                                            <th>Date</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $job_total = 0;
                                        foreach ($job_data['deposits'] as $deposit):
                                            $job_total += floatval($deposit['deposit_amount']);
                                            $grand_total += floatval($deposit['deposit_amount']);
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($deposit['reference_no']) ?></td>
                                                <td><?= ucfirst($deposit['type']) ?></td>
                                                <td><?= $deposit['type'] === 'check' ? $deposit['check_no'] : '-' ?></td>
                                                <td><?= date('M d, Y', strtotime($deposit['created_at'])) ?></td>
                                                <td class="text-end">$ <?= number_format($deposit['deposit_amount'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                            <td class="text-end"><strong>$ <?= number_format($job_total, 2) ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($grand_total > 0): ?>
                        <div class="mt-4 text-end border-top pt-3">
                            <h5 class="text-success">Grand Total Credit: $ <?= number_format($grand_total, 2) ?></h5>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }

    
    mysqli_close($conn);
}
?>
