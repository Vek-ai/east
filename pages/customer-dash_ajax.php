<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

$is_points_enabled = getSetting('is_points_enabled');

$permission = $_SESSION['permission'];

if (isset($_POST['search_orders'])) {
    $customerid = mysqli_real_escape_string($conn, $_POST['customerid']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);

    $status_labels = [
        1 => ['label' => 'New Order', 'class' => 'badge bg-primary'],
        2 => ['label' => 'Processing', 'class' => 'badge bg-warning'],
        3 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
        4 => ['label' => 'Sent to Customer', 'class' => 'badge bg-success'],
        5 => ['label' => 'On Hold', 'class' => 'badge bg-danger'],
        6 => ['label' => 'Archived/Returned', 'class' => 'badge bg-secondary'],
    ];
    ?>
    <div class="month-table">
        <div class="datatables">
            <div class="table-responsive mt-3">
                <table id="orders-tbl" class="table align-middle mb-0 no-wrap text-center">
                    <thead>
                    <tr>
                        <th class="border-0 ps-0">Sales Person</th>
                        <th class="border-0">Date</th>
                        <th class="border-0 text-end">Total Amount</th>
                        <th class="border-0 text-end">Discount</th>
                        <?php if($is_points_enabled == '1'){
                        ?>
                            <th class="border-0 text-end">Points</th>
                        <?php
                        }
                        ?>
                        <th class="border-0">Status</th>
                        <th class="border-0"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                        $query = "SELECT * FROM orders WHERE customerid = '$customerid'";

                        if (!empty($date_from) && !empty($date_to)) {
                            $date_to .= ' 23:59:59';
                            $query .= " AND (order_date >= '$date_from' AND order_date <= '$date_to')";
                        }
                        $query .= " ORDER BY order_date DESC";
                        if (empty($date_from) || empty($date_to)) {
                            $query .= " LIMIT 10";
                        }

                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $status_code = $row['status'];
                                $status = isset($status_labels[$status_code]) ? $status_labels[$status_code] : ['label' => 'Unknown', 'class' => 'badge bg-dark'];
                                ?>
                                <tr>
                                    <td class="ps-0">
                                        <div class="hstack gap-3">
                                            <span class="round-48 rounded-circle overflow-hidden flex-shrink-0 hstack justify-content-center">
                                                <img src="assets/images/profile/user-2.jpg" alt class="img-fluid">
                                            </span>
                                            <div>
                                                <h5 class="mb-1"><?= get_staff_name($row['cashier']) ?></h5>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="mb-0"><?= date("F d, Y", strtotime($row['order_date'])) ?></p>
                                    </td>
                                    <td class="text-end">
                                        <p class="mb-0">$<?= getOrderTotals($row['orderid']) ?></p>
                                    </td>
                                    <td class="text-end">
                                        <p class="mb-0">$<?= getOrderTotalsDiscounted($row['orderid']) ?></p>
                                    </td>
                                    <?php if($is_points_enabled == '1'){
                                    ?>
                                        <td class="text-end">
                                            <p class="mb-0"><?= getOrderPoints($row['orderid']) ?></p>
                                        </td>
                                    <?php
                                    }
                                    ?>
                                    
                                    <td>
                                        <span class="<?= $status['class']; ?> fw-bold"><?= $status['label']; ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_order_btn" type="button" data-id="<?php echo $row["orderid"]; ?>"><i class="text-primary fa fa-eye fs-5"></i></button>
                                        <a href="/print_order_product.php?id=<?= $row["orderid"]; ?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>"><i class="text-success fa fa-print fs-5"></i></a>
                                        <a href="/print_order_total.php?id=<?= $row["orderid"]; ?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>"><i class="text-white fa fa-file-lines fs-5"></i></a>
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
<?php
}

if (isset($_POST['search_estimates'])) {
    $customerid = mysqli_real_escape_string($conn, string: $_POST['customerid']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);
    ?>
    <div class="month-table">
        <div class="datatables">
            <div class="table-responsive mt-3">
                <table id="estimates-tbl" class="table align-middle  mb-0 no-wrap text-center">
                    <thead>
                    <tr>
                        <th class="border-0 ps-0">Estimate ID</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">No. of changes</th>
                        <th class="border-0 text-end">Total Amount</th>
                        <th class="border-0"></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM estimates WHERE customerid = '$customerid'";

                        if (!empty($date_from) && !empty($date_to)) {
                            $date_to .= ' 23:59:59';
                            $query .= " AND (estimated_date >= '$date_from' AND estimated_date <= '$date_to')";
                        }
                        $query .= " ORDER BY estimated_date DESC";
                        if (empty($date_from) || empty($date_to)) {
                            $query .= " LIMIT 10";
                        }

                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $estimate_id = $row['estimateid'];
                                $total_changes = 0;
                                $query_est_changes = "SELECT count(*) as total_changes FROM estimate_changes WHERE estimate_id = '$estimate_id'";
                                $result_est_changes = mysqli_query($conn, $query_est_changes);
                                if ($result_est_changes && mysqli_num_rows($result_est_changes) > 0) {
                                    $row_est_changes = mysqli_fetch_assoc($result_est_changes);
                                    $total_changes = $row_est_changes['total_changes'];
                                }

                                $status_html = "";
                                if(intval($row['status']) == 1){
                                    $status_html = '<span class="badge bg-primary text-light">Not Ordered</span>';
                                }else if(intval($row['estimateid']) == 2){
                                    $status_html = '<span class="badge bg-success text-light">Ordered</span>';
                                }
                            ?>
                            <tr>
                                <td class="ps-0">
                                    <h5 class="mb-1 text-center"><?= $row['estimateid'] ?></h5>
                                </td>
                                <td>
                                    <?= $status_html ?>
                                </td>
                                <td>
                                    <p class="mb-0"><?= $total_changes ?></p>
                                </td>
                                <td class="text-end">
                                    <p class="mb-0 fs-3">$<?= getEstimateTotalsDiscounted($row['estimateid']) ?></p>
                                </td>
                                <td>
                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_estimate_btn" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-primary fa fa-eye fs-5"></i></button>
                                    <a href="/print_estimate_product.php?id=<?= $row["estimateid"]; ?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-success fa fa-print fs-5"></i></a>
                                    <a href="/print_estimate_total.php?id=<?= $row["estimateid"]; ?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-white fa fa-file-lines fs-5"></i></a>
                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_changes_btn" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-info fa fa-clock-rotate-left fs-5"></i></button>
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
<?php
}

if (isset($_POST['search_jobs'])) {
    $customerid = mysqli_real_escape_string($conn, string: $_POST['customerid']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);
    ?>
    <div class="datatables">
        <div class="table-responsive mt-3">
            <table id="jobs-tbl" class="table align-middle  mb-0 no-wrap text-center">
                <thead>
                <tr>
                    <th class="border-0 ps-0">Job PO #</th>
                    <th class="border-0">Job Name</th>
                    <th class="border-0 text-right">Deposited Amount</th>
                    <th class="border-0 text-right">Materials Purchased</th>
                    <th class="border-0"></th>
                    <th class="border-0" style="display:none;"></th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    $job_conditions = [];

                    if (!empty($customerid)) {
                        $job_conditions[] = "customer_id = '$customerid'";
                    }

                    $job_where = !empty($job_conditions) ? "WHERE " . implode(" AND ", $job_conditions) : "";

                    $job_query = "SELECT * FROM jobs $job_where ORDER BY created_at DESC";
                    $job_result = mysqli_query($conn, $job_query);

                    if ($job_result && mysqli_num_rows($job_result) > 0) {
                        while ($job = mysqli_fetch_assoc($job_result)) {
                            $job_id = $job['job_id'];
                            $job_name = $job['job_name'];
                            $customer_id = $job['customer_id'];

                            $deposit_query = "
                                SELECT * FROM job_deposits 
                                WHERE job_id = '$job_id' AND deposit_status = '1'
                                " . (!empty($date_from) && !empty($date_to) ? "AND DATE(created_at) BETWEEN '$date_from' AND '$date_to'" : "") . "
                                ORDER BY created_at ASC
                            ";
                            $deposit_result = mysqli_query($conn, $deposit_query);

                            while ($deposit = mysqli_fetch_assoc($deposit_result)) {
                                $order_id = $deposit['reference_no'];
                                $order_details = getOrderDetails($order_id);
                                $po = $order_details['job_po'] ?? '';

                                ?>
                                <tr>
                                    <td class="ps-0 text-center">
                                        <h5 class="mb-1"><?= htmlspecialchars($po) ?></h5>
                                    </td>
                                    <td class="text-center">
                                        <h5 class="mb-1"><?= htmlspecialchars($job_name) ?></h5>
                                    </td>
                                    <td>
                                        <h5 class="mb-1 text-right text-success">
                                            + $<?= number_format($deposit['deposit_amount'], 2) ?>
                                        </h5>
                                    </td>
                                    <td></td>
                                    <td class="text-center">
                                        <a href="?page=job_details&customer_id=<?= $customer_id ?>&job_name=<?= urlencode($job_name) ?>"
                                            target="_blank"
                                            title="View Job Details"
                                            class="btn btn-sm p-0 me-1 text-decoration-none">
                                            <i class="fa fa-eye text-primary fs-5"></i>
                                        </a>

                                        <?php                                                    
                                        if ($permission === 'edit') {
                                        ?>

                                        <a href="#"
                                            id="addModalBtn"
                                            title="Edit Job"
                                            class="btn btn-sm p-0 text-decoration-none"
                                            data-job-id="<?= $job_id ?>"
                                            data-customer-id="<?= $customer_id ?>"
                                            data-type="edit">
                                            <i class="ti ti-pencil fs-6"></i>
                                        </a>

                                        <a href="#"
                                            id="depositModalBtn"
                                            title="Deposit"
                                            class="btn btn-sm p-0 text-decoration-none"
                                            data-job="<?= $job_id ?>">
                                            <i class="ti ti-plus text-success fs-6"></i>
                                        </a>

                                        <?php
                                        }
                                        ?>
                                    </td>
                                    <td style="display:none;" class="created-at"><?= $deposit['created_at'] ?></td>
                                </tr>
                                <?php
                            }

                            $usage_query = "
                                SELECT * FROM job_ledger 
                                WHERE job_id = '$job_id' AND entry_type = 'usage'
                                " . (!empty($date_from) && !empty($date_to) ? "AND DATE(created_at) BETWEEN '$date_from' AND '$date_to'" : "") . "
                                ORDER BY created_at ASC
                            ";
                            $usage_result = mysqli_query($conn, $usage_query);

                            while ($usage = mysqli_fetch_assoc($usage_result)) {
                                $order_id = $usage['reference_no'];
                                $order_details = getOrderDetails($order_id);
                                $po = $order_details['job_po'] ?? '';

                                ?>
                                <tr>
                                    <td class="ps-0 text-center">
                                        <h5 class="mb-1"><?= htmlspecialchars($po) ?></h5>
                                    </td>
                                    <td class="text-center">
                                        <h5 class="mb-1"><?= htmlspecialchars($job_name) ?></h5>
                                    </td>
                                    <td></td>
                                    <td>
                                        <h5 class="mb-1 text-right text-danger">
                                            - $<?= number_format($usage['amount'], 2) ?>
                                        </h5>
                                    </td>
                                    <td class="text-center">
                                        <a href="?page=job_details&customer_id=<?= $customer_id ?>&job_name=<?= urlencode($job_name) ?>"
                                            target="_blank"
                                            title="View Job Details"
                                            class="btn btn-sm p-0 me-1 text-decoration-none">
                                            <i class="fa fa-eye text-primary fs-5"></i>
                                        </a>

                                        <?php                                                    
                                        if ($permission === 'edit') {
                                        ?>
                                        <a href="#"
                                            id="addModalBtn"
                                            title="Edit Job"
                                            class="btn btn-sm p-0 text-decoration-none"
                                            data-job-id="<?= $job_id ?>"
                                            data-customer-id="<?= $customer_id ?>"
                                            data-type="edit">
                                            <i class="ti ti-pencil fs-6"></i>
                                        </a>

                                        <a href="#"
                                            id="depositModalBtn"
                                            title="Deposit"
                                            class="btn btn-sm p-0 text-decoration-none"
                                            data-job="<?= $job_id ?>">
                                            <i class="ti ti-plus text-success fs-6"></i>
                                        </a>
                                        <?php 
                                        }
                                        ?>
                                    </td>
                                    <td style="display:none;" class="created-at"><?= $usage['created_at'] ?></td>
                                </tr>
                                <?php
                            }

                            if (
                                mysqli_num_rows($deposit_result) === 0 &&
                                mysqli_num_rows($usage_result) === 0
                            ) {
                                ?>
                                <tr>
                                    <td class="ps-0 text-center">
                                        <h5 class="mb-1">-</h5>
                                    </td>
                                    <td class="text-center">
                                        <h5 class="mb-1"><?= htmlspecialchars($job_name) ?></h5>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-center">
                                        <a href="?page=job_details&customer_id=<?= $customer_id ?>&job_name=<?= urlencode($job_name) ?>"
                                            target="_blank"
                                            title="View Job Details"
                                            class="btn btn-sm p-0 me-1 text-decoration-none">
                                            <i class="fa fa-eye text-primary fs-5"></i>
                                        </a>

                                        <a href="#"
                                            id="addModalBtn"
                                            title="Edit Job"
                                            class="btn btn-sm p-0 text-decoration-none"
                                            data-job-id="<?= $job_id ?>"
                                            data-customer-id="<?= $customer_id ?>"
                                            data-type="edit">
                                            <i class="ti ti-pencil fs-6"></i>
                                        </a>

                                        <a href="#"
                                            id="depositModalBtn"
                                            title="Deposit"
                                            class="btn btn-sm p-0 text-decoration-none"
                                            data-job="<?= $job_id ?>">
                                            <i class="ti ti-plus text-success fs-6"></i>
                                        </a>
                                    </td>
                                    <td style="display:none;" class="created-at"><?= $job['created_at'] ?></td>
                                </tr>

                                <?php
                            }
                        }
                    }
                    ?>
                    </tbody>

            </table>
        </div>
    </div>
<?php
}

if (isset($_POST['search_contractor_jobs'])) {
    $customerid = mysqli_real_escape_string($conn, $_POST['customerid']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);

    $status_labels = [
        1 => ['label' => 'New Order', 'class' => 'badge bg-primary'],
        2 => ['label' => 'Processing', 'class' => 'badge bg-warning'],
        3 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
        4 => ['label' => 'Sent to Customer', 'class' => 'badge bg-success'],
        5 => ['label' => 'On Hold', 'class' => 'badge bg-danger'],
        6 => ['label' => 'Archived/Returned', 'class' => 'badge bg-secondary'],
    ];
    ?>
    <div class="month-table">
        <div class="datatables">
            <div class="table-responsive mt-3">
                <table id="contractor-jobs-tbl" class="table align-middle mb-0 no-wrap text-center">
                    <thead>
                    <tr>
                        <th class="border-0 ps-0">Customer</th>
                        <th class="border-0">Date</th>
                        <?php if($is_points_enabled == '1'){
                        ?>
                            <th class="border-0 text-end">Points Gained</th>
                        <?php
                        }
                        ?>
                        <th class="border-0">Status</th>
                        <th class="border-0"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                        $query = "SELECT * FROM orders WHERE contractor_id = '$customerid'";

                        if (!empty($date_from) && !empty($date_to)) {
                            $date_to .= ' 23:59:59';
                            $query .= " AND (order_date >= '$date_from' AND order_date <= '$date_to')";
                        }
                        $query .= " ORDER BY order_date DESC";
                        if (empty($date_from) || empty($date_to)) {
                            $query .= " LIMIT 10";
                        }

                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $status_code = $row['status'];
                                $status = isset($status_labels[$status_code]) ? $status_labels[$status_code] : ['label' => 'Unknown', 'class' => 'badge bg-dark'];

                                $points = getContractorPointsFromOrder($row['orderid']);
                                $orderid = (int) $row['orderid'];
                                $check_sql = "SELECT COUNT(*) AS cnt FROM customer_points WHERE order_id = $orderid AND type = 1";
                                $check_res = mysqli_query($conn, $check_sql);
                                $check_row = mysqli_fetch_assoc($check_res);
                                $already_converted = ($check_row['cnt'] > 0);
                                ?>
                                <tr>
                                    <td class="ps-0">
                                        <div class="hstack gap-3">
                                            <span class="round-48 rounded-circle overflow-hidden flex-shrink-0 hstack justify-content-center">
                                                <img src="assets/images/profile/user-2.jpg" alt class="img-fluid">
                                            </span>
                                            <div>
                                                <h5 class="mb-1"><?= get_customer_name($row['customerid']) ?></h5>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="mb-0"><?= date("F d, Y", strtotime($row['order_date'])) ?></p>
                                    </td>
                                    <?php if($is_points_enabled == '1'){ ?>
                                        <td class="text-end">
                                            <p class="mb-0"><?= $points ?></p>
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <span class="<?= $status['class']; ?> fw-bold"><?= $status['label']; ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger-gradient btn-sm p-0 me-1" 
                                                id="view_contractor_order_btn" 
                                                title="View"
                                                type="button" 
                                                data-id="<?php echo $row["orderid"]; ?>">
                                            <i class="text-primary fa fa-eye fs-5"></i>
                                        </button>

                                        <?php if ($points > 0 && !$already_converted) { ?>
                                            <a href="javascript:void(0);" 
                                            class="convert-points-btn text-primary text-decoration-none" 
                                            title="Convert"
                                            data-orderid="<?= $row['orderid'] ?>" 
                                            data-points="<?= $points ?>">
                                                <i class="fa fa-refresh text-success fs-5"></i>
                                            </a>
                                        <?php } ?>
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
<?php
}

if (isset($_POST['convert_points'])) {
    $customerid = (int) $_POST['customerid'];
    $orderid    = (int) $_POST['orderid'];

    $is_points_enabled = getSetting('is_points_enabled');
    if ($is_points_enabled != '1') {
        echo json_encode(['status' => 'error', 'message' => 'Points system disabled']);
        exit;
    }

    $check_sql = "SELECT COUNT(*) AS cnt FROM customer_points WHERE order_id = $orderid";
    $check_res = mysqli_query($conn, $check_sql);
    $check_row = mysqli_fetch_assoc($check_res);

    if ($check_row['cnt'] > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Points already converted for this order']);
        exit;
    }

    $points = getContractorPointsFromOrder($orderid);

    if ($points <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'No points to convert']);
        exit;
    }

    $order_total = getOrderTotalsDiscounted($orderid);

    $insert_sql = "
        INSERT INTO customer_points (customer_id, order_id, total_order_amount, points_earned, type, date)
        VALUES ($customerid, $orderid, $order_total, $points, 1, NOW())
    ";

    if (mysqli_query($conn, $insert_sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Points successfully converted']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database insert failed: ' . mysqli_error($conn)]);
    }

    exit;
}

if (isset($_REQUEST['query'])) {
    $searchQuery = isset($_REQUEST['query']) ? mysqli_real_escape_string($conn, $_REQUEST['query']) : '';
    $customerid = mysqli_real_escape_string($conn, $_REQUEST['customerid']);

    $query_product = "
        SELECT o.*, op.*, p.product_item, p.main_image, p.color
        FROM 
            orders as o
        LEFT JOIN order_product as op ON o.orderid = op.orderid
        LEFT JOIN product as p ON p.product_id = op.productid
        WHERE 
            hidden = '0' AND o.customerid = '$customerid'
    ";

    if (!empty($searchQuery)) {
        $query_product .= " AND (p.product_item LIKE '%$searchQuery%' OR p.description LIKE '%$searchQuery%')";
    }

    $query_product .= " ORDER BY o.order_date DESC";

    $result_product = mysqli_query($conn, $query_product);

    $tableHTML = "";

    if (mysqli_num_rows($result_product) > 0) {
        while ($row_product = mysqli_fetch_array($result_product)) {

            $product_length = $row_product['length'];
            $product_width = $row_product['width'];
            $product_color = $row_product['color'];

            $dimensions = "";

            if (!empty($product_length) || !empty($product_width)) {
                $dimensions = '';
            
                if (!empty($product_length)) {
                    $dimensions .= $product_length;
                }
            
                if (!empty($product_width)) {
                    if (!empty($dimensions)) {
                        $dimensions .= " X ";
                    }
                    $dimensions .= $product_width;
                }
            
                if (!empty($dimensions)) {
                    $dimensions = " - " . $dimensions;
                }
            }

            $default_image = 'images/product/product.jpg';

            $picture_path = !empty($row_product['main_image'])
            ? $row_product['main_image']
            : $default_image;

            $tableHTML .= '
                <tr>
                    <td>
                        <a href="javascript:void(0);" id="view_product_details" data-id="' . htmlspecialchars($row_product['productid']) . '" class="d-flex align-items-center">
                            <div class="d-flex align-items-center">
                                <img src="'. htmlspecialchars($picture_path) .'" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                <div class="ms-3 text-wrap">
                                    <h6 class="fw-semibold mb-0 fs-4">'. htmlspecialchars($row_product['product_item']) .' ' . htmlspecialchars($dimensions) .'</h6>
                                </div>
                            </div>
                        </a>
                    </td>
                    <td>
                        <div class="d-flex mb-0 gap-8">
                            <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color: ' . htmlspecialchars(getColorHexFromColorID($row_product['color'])) .'"></a> '
                            . htmlspecialchars(getColorName($row_product['color'])) .'
                        </div>
                    </td>
                    <td>';

                    $width = htmlspecialchars($row_product['custom_width']);
                    $bend = htmlspecialchars($row_product['custom_bend']);
                    $hem = htmlspecialchars($row_product['custom_hem']);
                    $length = htmlspecialchars($row_product['custom_length']);
                    $inch = htmlspecialchars($row_product['custom_length2']);
                    
                    if (!empty($width)) {
                        $tableHTML .= "Width: " . number_format($width,2) . "<br>";
                    }

                    if (!empty($bend)) {
                        $tableHTML .= "Bend: " . number_format($bend,2) . "<br>";
                    }
                    
                    if (!empty($hem)) {
                        $tableHTML .= "Hem: " . number_format($hem,2) . "<br>";
                    }
                    
                    if (!empty($length)) {
                        $tableHTML .= "Length: " . number_format($length,2) . " ft";
                        if (!empty($inch)) {
                            $tableHTML .= " " . number_format($inch,2) . " in";
                        }
                        $tableHTML .= "<br>";
                    } elseif (!empty($inch)) {
                        $tableHTML .= "Length: " . number_format($inch,2) . " in<br>";
                    }

                    $tableHTML .= '</td>
                    <td><h6 class="mb-0 fs-4">' . htmlspecialchars(getUsageName($row_product['usageid'])) . '</h6></td>
                    <td><h6 class="mb-0 fs-4">' . htmlspecialchars($row_product['job_name']) . '</h6></td>
                </tr>';

        }
    } else {
        $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    
    //echo $tableHTML;
    echo $tableHTML;
}

if(isset($_POST['fetch_order_details'])){
    $orderid = mysqli_real_escape_string($conn, $_POST['orderid']);
    $status_prod_labels = [
        0 => ['label' => 'New', 'class' => 'badge bg-primary'],
        1 => ['label' => 'Processing', 'class' => 'badge bg-success'],
        2 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-warning'],
        3 => ['label' => 'In Transit', 'class' => 'badge bg-secondary'],
        4 => ['label' => 'Delivered', 'class' => 'badge bg-success'],
        5 => ['label' => 'On Hold', 'class' => 'badge bg-danger'],
        6 => ['label' => 'Returned', 'class' => 'badge bg-danger']
    ];
    ?>
    <style>
        .tooltip-inner {
            font-size: calc(0.875rem + 2px) !important;
        }
    </style>
    <div class="card card-body datatables">
        <div class="product-details table-responsive text-wrap">
            <h4>Products Ordered</h4>
            <table id="order_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Color</th>
                        <th>Grade</th>
                        <th>Profile</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Dimensions</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Customer Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 0;
                    $query = "SELECT * FROM order_product WHERE orderid='$orderid'";
                    $result = mysqli_query($conn, $query);
                    $totalquantity = $total_actual_price = $total_disc_price = 0;
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $product_id = $row['productid'];
                            $actual_price = $discounted_price = 0;
                            if($row['quantity'] > 0){
                                $actual_price = number_format(floatval($row['actual_price'] * $row['quantity']),2);
                                $discounted_price = number_format(floatval($row['discounted_price'] * $row['quantity']),2);
                                $status_id = $row['status'] ?? 0; 
                                $label_data = $status_prod_labels[$status_id] ?? ['label' => 'Unknown', 'class' => 'badge bg-dark'];
                            ?>
                            <tr>
                                <td class="text-wrap"> 
                                    <?php echo getProductName($product_id) ?>
                                </td>
                                <td>
                                <div class="d-flex mb-0 gap-8">
                                    <span class="rounded-circle d-block p-3" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_id)?>; width: 20px; height: 20px;"></span>
                                    <?= getColorFromID($product_id); ?>
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
                                <td class="text-center">
                                    <span class="<?= $label_data['class'] ?>">
                                        <?= htmlspecialchars($label_data['label']) ?>
                                    </span>
                                </td>
                                <td class="text-end">$ <?= $actual_price ?></td>
                                <td class="text-end">$ <?= $discounted_price ?></td>
                            </tr>
                    <?php
                            $totalquantity += $row['quantity'] ;
                            $total_actual_price += $actual_price;
                            $total_disc_price += $discounted_price;
                            }
                        }
                    }
                    ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="5">Total</td>
                        <td><?= $totalquantity ?></td>
                        <td></td>
                        <td class="text-end">$ <?= number_format($total_actual_price,2) ?></td>
                        <td class="text-end">$ <?= number_format($total_disc_price,2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php 
        $query = "SELECT * FROM product_returns WHERE orderid='$orderid'";
        $result = mysqli_query($conn, $query);
        $totalquantity = $total_actual_price = $total_disc_price = 0;
        if ($result && mysqli_num_rows($result) > 0) {
        ?>
        <div class="card card-body datatables">
            <div class="return-details table-responsive text-wrap mt-5">
                <h4>Returned Products</h4>
                <table id="return_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
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
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                                $product_id = $row['productid'];
                                $actual_price = $discounted_price = 0;
                                if($row['quantity'] > 0){
                                    $actual_price = number_format(floatval($row['actual_price'] * $row['quantity']),2);
                                    $discounted_price = number_format(floatval($row['discounted_price'] * $row['quantity']),2);
                                    ?>
                                <tr>
                                    <td class="text-wrap"> 
                                        <?php echo getProductName($product_id) ?>
                                    </td>
                                    <td>
                                    <div class="d-flex mb-0 gap-8">
                                        <span class="rounded-circle d-block p-3" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_id)?>; width: 20px; height: 20px;"></span>
                                        <?= getColorFromID($product_id); ?>
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
                                    <td class="text-end">$ <?= $actual_price ?></td>
                                    <td class="text-end">$ <?= $discounted_price ?></td>
                                </tr>
                        <?php
                                $totalquantity += $row['quantity'] ;
                                $total_actual_price += $actual_price;
                                $total_disc_price += $discounted_price;
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
        
    <?php 
    } 
    ?>
       
    <script>
        $(document).ready(function() {

            $('#order_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Order Details not found"
                },
                autoWidth: false,
                responsive: true
            });

            $('#return_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Order Details not found"
                },
                autoWidth: false,
                responsive: true
            });
        });
    </script>
    <?php
}

if(isset($_POST['fetch_contractor_order_details'])){
    $orderid = mysqli_real_escape_string($conn, $_POST['orderid']);
    $status_prod_labels = [
        0 => ['label' => 'New', 'class' => 'badge bg-primary'],
        1 => ['label' => 'Processing', 'class' => 'badge bg-success'],
        2 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-warning'],
        3 => ['label' => 'In Transit', 'class' => 'badge bg-secondary'],
        4 => ['label' => 'Delivered', 'class' => 'badge bg-success'],
        5 => ['label' => 'On Hold', 'class' => 'badge bg-danger'],
        6 => ['label' => 'Returned', 'class' => 'badge bg-danger']
    ];
    ?>
    <style>
        .tooltip-inner {
            font-size: calc(0.875rem + 2px) !important;
        }
    </style>
    <div class="card card-body datatables">
        <div class="product-details table-responsive text-wrap">
            <h4>Products Ordered</h4>
            <table id="order_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Color</th>
                        <th>Grade</th>
                        <th>Profile</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Dimensions</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 0;
                    $query = "SELECT * FROM order_product WHERE orderid='$orderid'";
                    $result = mysqli_query($conn, $query);
                    $totalquantity = $total_actual_price = $total_disc_price = 0;
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $product_id = $row['productid'];
                            $actual_price = $discounted_price = 0;
                            if($row['quantity'] > 0){
                                $actual_price = number_format(floatval($row['actual_price'] * $row['quantity']),2);
                                $discounted_price = number_format(floatval($row['discounted_price'] * $row['quantity']),2);
                                $status_id = $row['status'] ?? 0; 
                                $label_data = $status_prod_labels[$status_id] ?? ['label' => 'Unknown', 'class' => 'badge bg-dark'];
                            ?>
                            <tr>
                                <td class="text-wrap"> 
                                    <?php echo getProductName($product_id) ?>
                                </td>
                                <td>
                                <div class="d-flex mb-0 gap-8">
                                    <span class="rounded-circle d-block p-3" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_id)?>; width: 20px; height: 20px;"></span>
                                    <?= getColorFromID($product_id); ?>
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
                                <td class="text-center">
                                    <span class="<?= $label_data['class'] ?>">
                                        <?= htmlspecialchars($label_data['label']) ?>
                                    </span>
                                </td>
                            </tr>
                    <?php
                            $totalquantity += $row['quantity'] ;
                            $total_actual_price += $actual_price;
                            $total_disc_price += $discounted_price;
                            }
                        }
                    }
                    ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="5">Total</td>
                        <td><?= $totalquantity ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php 
        $query = "SELECT * FROM product_returns WHERE orderid='$orderid'";
        $result = mysqli_query($conn, $query);
        $totalquantity = $total_actual_price = $total_disc_price = 0;
        if ($result && mysqli_num_rows($result) > 0) {
        ?>
        <div class="card card-body datatables">
            <div class="return-details table-responsive text-wrap mt-5">
                <h4>Returned Products</h4>
                <table id="return_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
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
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                                $product_id = $row['productid'];
                                $actual_price = $discounted_price = 0;
                                if($row['quantity'] > 0){
                                    $actual_price = number_format(floatval($row['actual_price'] * $row['quantity']),2);
                                    $discounted_price = number_format(floatval($row['discounted_price'] * $row['quantity']),2);
                                    ?>
                                <tr>
                                    <td class="text-wrap"> 
                                        <?php echo getProductName($product_id) ?>
                                    </td>
                                    <td>
                                    <div class="d-flex mb-0 gap-8">
                                        <span class="rounded-circle d-block p-3" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_id)?>; width: 20px; height: 20px;"></span>
                                        <?= getColorFromID($product_id); ?>
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
                                    <td class="text-end">$ <?= $actual_price ?></td>
                                    <td class="text-end">$ <?= $discounted_price ?></td>
                                </tr>
                        <?php
                                $totalquantity += $row['quantity'] ;
                                $total_actual_price += $actual_price;
                                $total_disc_price += $discounted_price;
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
        
    <?php 
    } 
    ?>
       
    <script>
        $(document).ready(function() {

            $('#order_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Order Details not found"
                },
                autoWidth: false,
                responsive: true
            });

            $('#return_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Order Details not found"
                },
                autoWidth: false,
                responsive: true
            });
        });
    </script>
    <?php
}

if (isset($_POST['fetch_estimate_details'])) {
    $estimateid = mysqli_real_escape_string($conn, $_POST['id']);
    $query = "SELECT * FROM estimate_prod WHERE estimateid = '$estimateid'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $totalquantity = $total_actual_price = $total_disc_price = 0;
        $response = array();
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
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        View Estimate
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="update_product" class="form-horizontal">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body datatables">
                                <div class="estimate-details table-responsive text-nowrap">
                                    <table id="est_dtls_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
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
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $estimateid = $row['estimateid'];
                                                    $product_details = getProductDetails($row['product_id']);
                                                    $actual_price = number_format(floatval($row['actual_price'] * $row['quantity']),2);
                                                    $discounted_price = number_format(floatval($row['discounted_price'] * $row['quantity']),2);
                                                ?> 
                                                    <tr> 
                                                        <td>
                                                            <?php echo getProductName($row['product_id']) ?>
                                                        </td>
                                                        <td>
                                                        <div class="d-flex mb-0 gap-8">
                                                            <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_details['color'])?>"></a>
                                                            <?= getColorFromID($product_details['color']); ?>
                                                        </div>
                                                        </td>
                                                        <td>
                                                            <?php echo getGradeName($product_details['grade']); ?>
                                                        </td>
                                                        <td>
                                                            <?php echo getProfileTypeName($product_details['profile']); ?>
                                                        </td>
                                                        <td><?= $row['quantity'] ?></td>
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
                                                        <td class="text-end">$ <?= $actual_price ?></td>
                                                        <td class="text-end">$ <?= $discounted_price ?></td>
                                                    </tr>
                                            <?php
                                                    $totalquantity += $row['quantity'] ;
                                                    $total_actual_price += $actual_price;
                                                    $total_disc_price += $discounted_price;
                                                }
                                            
                                            ?>
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <td colspan="4">Total</td>
                                                <td class="text-start"><?= $totalquantity ?></td>
                                                <td></td>
                                                <td class="text-end">$ <?= $total_actual_price ?></td>
                                                <td class="text-end">$ <?= $total_disc_price ?></td>
                                            </tr>
                                        </tfoot>
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
                $('#est_dtls_tbl').DataTable({
                    language: {
                        emptyTable: "Estimate Details not found"
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

if(isset($_POST['fetch_job_details'])){
    $customerid = mysqli_real_escape_string($conn, $_POST['customerid']);
    $job_name = mysqli_real_escape_string($conn, $_POST['job_name']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);
    ?>
    <div class="card card-body datatables">
        <div class="product-details table-responsive text-wrap">
            <h4>Job Name: <?= ucwords($job_name) ?></h4>
            <table id="order_dtls_tbl" class="table table-hover mb-0 text-md-nowrap text-center">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                    $query_orders = "SELECT * FROM orders WHERE customerid = '$customerid'";
                    if ($job_name !== '') {
                        $query_orders .= " AND job_name LIKE '%$job_name%'";
                    } else {
                        $query_orders .= " AND (job_name IS NULL OR job_name = '')";
                    }

                    if (!empty($date_from) && !empty($date_to)) {
                        $query_orders .= " AND (order_date >= '$date_from' AND order_date <= '$date_to')";
                    }

                    $query_orders .= " ORDER BY order_date DESC";

                    $result_orders = mysqli_query($conn, $query_orders);

                    if ($result_orders && mysqli_num_rows($result_orders) > 0) {
                        $total_amt = 0;
                        while ($row_orders = mysqli_fetch_assoc($result_orders)) {
                            $total_amt += $row_orders['discounted_price'];
                            ?>
                            <tr>
                                <td class="ps-0">
                                    <h5 class="mb-1 text-center"><?= $row_orders['orderid'] ?></h5>
                                </td>
                                <td>
                                    <h5 class="mb-1 text-center"><?= date("F d, Y", strtotime($row_orders['order_date'])) ?></h5>
                                </td>
                                <td>
                                    <h5 class="mb-1 text-right">$ <?= number_format($row_orders['discounted_price'], 2) ?></h5>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>

                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="2" class="text-right me-3">Total</td>
                        <td class="text-right">
                            <h5>$ <?= number_format($total_amt,2) ?></h5>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myLargeModalLabel">
                    View Estimate Changes
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="update_product" class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body datatables">
                            <div class="estimate-details table-responsive text-nowrap">
                                <table id="est_changes_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
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
                                        $estimateid = mysqli_real_escape_string($conn, $_POST['id']);
                                        $query = "SELECT * FROM estimate_changes WHERE estimate_id = '$estimateid'";
                                        $result = mysqli_query($conn, $query);
                                        
                                        if ($result && mysqli_num_rows($result) > 0) {
                                            $response = array();
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $estimateid = $row['estimateid'];
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
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#est_changes_tbl').DataTable({
                language: {
                    emptyTable: "Estimate details unchanged"
                },
                autoWidth: false,
                responsive: true,
                lengthChange: false
            });

            $('#viewChangesModal').on('shown.bs.modal', function () {
                $('#est_changes_tbl').DataTable().columns.adjust().responsive.recalc();
            });
        });
    </script>

    <?php

} 

if (isset($_POST['fetch_job_modal'])) {
    $job_id = intval($_POST['job_id']);
    $customer_id = intval($_POST['customer_id']);
    $query = "SELECT * FROM jobs WHERE job_id = '$job_id'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
    }
    ?>
        <div class="row">
            <!-- Job Name -->
            <div class="col-md-6 mb-3">
                <label for="job_name" class="form-label">Job Name</label>
                <input type="text" class="form-control" id="job_name" name="job_name" placeholder="Enter job name"
                    value="<?= $row['job_name'] ?? '' ?>" required>
            </div>

            <!-- Status -->
            <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="">Select Status...</option>
                    <option value="active" <?= (isset($row['status']) && $row['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="completed" <?= (isset($row['status']) && $row['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= (isset($row['status']) && $row['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>

            <!-- Location -->
            <div class="col-12 mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" placeholder="Enter job location" value="<?= $row['location'] ?? '' ?>" required>
            </div>

            <!-- Constructor Name -->
            <div class="col-md-6 mb-3">
                <label for="constructor_name" class="form-label">Constructor Name</label>
                <input type="text" class="form-control" id="constructor_name" name="constructor_name"
                    value="<?= $row['constructor_name'] ?? '' ?>" placeholder="Enter constructor name">
            </div>

            <!-- Constructor Contact -->
            <div class="col-md-6 mb-3">
                <label for="constructor_contact" class="form-label">Constructor Contact</label>
                <input type="text" class="form-control" id="constructor_contact" name="constructor_contact"
                    value="<?= $row['constructor_contact'] ?? '' ?>" placeholder="Enter contact number or email">
            </div>
        </div>

        <input type="hidden" id="job_id" name="job_id" class="form-control"  value="<?= $row['job_id'] ?>"/>
        <input type="hidden" id="customer_id" name="customer_id" class="form-control"  value="<?= $customer_id ?>"/>
    <?php
}

if (isset($_POST['save_job'])) {
    $job_id = intval($_POST['job_id'] ?? 0);
    $customer_id = intval($_POST['customer_id'] ?? 0);
    $job_name = mysqli_real_escape_string($conn, $_POST['job_name'] ?? '');
    $location = mysqli_real_escape_string($conn, $_POST['location'] ?? '');
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? '');
    $constructor_name = mysqli_real_escape_string($conn, $_POST['constructor_name'] ?? '');
    $constructor_contact = mysqli_real_escape_string($conn, $_POST['constructor_contact'] ?? '');

    $check_query = "SELECT * FROM jobs WHERE job_id = '$job_id' LIMIT 1";
    $check_result = mysqli_query($conn, $check_query);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $update = "
            UPDATE jobs 
            SET job_name = '$job_name',
                location = '$location',
                status = '$status',
                constructor_name = '$constructor_name',
                constructor_contact = '$constructor_contact'
            WHERE job_id = '$job_id'
        ";
        $result = mysqli_query($conn, $update);

        echo $result ? 'success_update' : 'error_update';
    } else {
        $insert = "
            INSERT INTO jobs (customer_id, job_name, location, status, constructor_name, constructor_contact)
            VALUES ('$customer_id', '$job_name', '$location', '$status', '$constructor_name', '$constructor_contact')
        ";
        $result = mysqli_query($conn, $insert);

        echo $result ? 'success_add' : 'error_insert';
    }
}

if (isset($_POST['deposit_job'])) {
    $job_id = intval($_POST['job_id'] ?? 0);
    $deposit_amount = floatval($_POST['deposit_amount'] ?? 0);
    $deposited_by = $_SESSION['userid'];
    $reference_no = trim($_POST['reference_no'] ?? '');
    $payment_method = $_POST['type'] ?? 'cash';
    $check_no = $_POST['check_no'] ?? null;
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? 'Job deposit');

    $check_query = "SELECT * FROM jobs WHERE job_id = '$job_id'";
    $check_result = mysqli_query($conn, $check_query);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $check_no_sql = $payment_method === 'check' ? "'" . mysqli_real_escape_string($conn, $check_no) . "'" : "NULL";

        $deposit_status = 1;
        if ($payment_method === 'cash' && $deposit_amount > 10000) {
            $deposit_status = 0;
        }

        $insert = "
            INSERT INTO job_ledger (job_id, customer_id, entry_type, amount, payment_method, check_number, reference_no, description, created_by)
            VALUES (
                '$job_id',
                '" . mysqli_real_escape_string($conn, $deposited_by) . "',
                'deposit',
                '$deposit_amount',
                '$payment_method',
                $check_no_sql,
                '" . mysqli_real_escape_string($conn, $reference_no) . "',
                '$description',
                '" . mysqli_real_escape_string($conn, $deposited_by) . "'
            )
        ";

        if (mysqli_query($conn, $insert)) {
            $insert_deposit = "
                INSERT INTO job_deposits (
                    job_id,
                    deposit_amount,
                    deposit_remaining,
                    deposit_status,
                    deposited_by,
                    reference_no,
                    type,
                    check_no
                ) VALUES (
                    '$job_id',
                    '$deposit_amount',
                    '$deposit_amount',
                    '$deposit_status',
                    '" . mysqli_real_escape_string($conn, $deposited_by) . "',
                    '" . mysqli_real_escape_string($conn, $reference_no) . "',
                    '$payment_method',
                    $check_no_sql
                )
            ";
            mysqli_query($conn, $insert_deposit);

            if ($payment_method === 'cash' && $deposit_amount > 10000) {
                $deposit_id = mysqli_insert_id($conn);

                $actorId = $_SESSION['userid'];
                $actor_name = get_staff_name($actorId);
                $actionType = 'deposit_approval';
                $targetId = $deposit_id;
                $targetType = 'Cash Deposit Approval';
                $message = "$actor_name has requested cash deposit approval";
                $url = '?page=job_deposit_approval';
                $recipientIds = getAdminIDs();
                createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);
            }

            $update = "
                UPDATE jobs 
                SET deposit_amount = deposit_amount + $deposit_amount
                WHERE job_id = '$job_id'
            ";
            $update_result = mysqli_query($conn, $update);

            echo $update_result ? 'success' : 'error_update';
        } else {
            echo 'error_insert';
        }
    } else {
        echo 'job_not_found';
    }
}



