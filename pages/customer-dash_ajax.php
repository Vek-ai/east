<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['search_orders'])) {
    $customerid = mysqli_real_escape_string($conn, string: $_POST['customerid']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);
?>
    <div class="month-table">
        <div class="table-responsive mt-3">
            <table class="table align-middle  mb-0 no-wrap text-center">
                <thead>
                <tr>
                    <th class="border-0 ps-0">Sales Person</th>
                    <th class="border-0">Date</th>
                    <th class="border-0 text-end">Total Amount</th>
                    <th class="border-0 text-end">Discount</th>
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
                                <td>
                                    <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_order_btn" type="button" data-id="<?php echo $row["orderid"]; ?>"><i class="text-primary fa fa-eye fs-5"></i></button>
                                    <a href="/print_order_product.php?id=<?= $row["orderid"]; ?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>"><i class="text-success fa fa-print fs-5"></i></a>
                                    <a href="/print_order_total.php?id=<?= $row["orderid"]; ?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["orderid"]; ?>"><i class="text-white fa fa-file-lines fs-5"></i></a>
                                </td>
                            </tr>
                            <?php
                        }
                    }else{
                    ?>
                        <tr>
                            <td colspan="4">No orders found</td>
                        </tr>
                    <?php  
                    }
                    
                    ?>
                </tbody>
            </table>
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
        <div class="table-responsive mt-3">
            <table class="table align-middle  mb-0 no-wrap text-center">
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
                    }else{
                    ?>
                        <tr>
                            <td colspan="4">No estimates found</td>
                        </tr>
                    <?php  
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
}

if (isset($_POST['search_jobs'])) {
    $customerid = mysqli_real_escape_string($conn, string: $_POST['customerid']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);
    ?>
    <div class="month-table">
        <div class="table-responsive mt-3">
            <table class="table align-middle  mb-0 no-wrap text-center">
                <thead>
                <tr>
                    <th class="border-0 ps-0">Job PO #</th>
                    <th class="border-0">Job Name</th>
                    <th class="border-0 text-right">Deposited Amount</th>
                    <th class="border-0 text-right">Materials Purchased</th>
                    <th class="border-0"></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $conditions = [];

                if (!empty($customerid)) {
                    $conditions[] = "j.customer_id = '$customerid'";
                }

                if (!empty($date_from) && !empty($date_to)) {
                    $conditions[] = "DATE(jl.created_at) BETWEEN '$date_from' AND '$date_to'";
                }

                $where_clause = implode(" AND ", $conditions);

                $query_jobs = "
                    SELECT 
                        jl.ledger_id, 
                        j.job_id, 
                        jl.amount, 
                        jl.entry_type, 
                        jl.created_at, 
                        jl.reference_no AS orderid 
                    FROM jobs j
                    LEFT JOIN job_ledger jl 
                        ON jl.job_id = j.job_id 
                        AND jl.entry_type IN ('deposit', 'usage')
                    " . (!empty($where_clause) ? "WHERE $where_clause" : "") . "
                    ORDER BY jl.created_at DESC
                ";
                $result_jobs = mysqli_query($conn, $query_jobs);

                if ($result_jobs && mysqli_num_rows($result_jobs) > 0) {
                    while ($row_jobs = mysqli_fetch_assoc($result_jobs)) {
                        $job_id = $row_jobs['job_id'];
                        $job_details = getJobDetails($job_id);
                        $customer_id = $job_details['customer_id'];
                        $customer_name = get_customer_name($customer_id);
                        $job_name = $job_details['job_name'];

                        $type = $row_jobs['entry_type'];
                        $amount = floatval($row_jobs['amount']);
                        
                        $order_id = $row_jobs['orderid'];
                        $order_details = getOrderDetails($order_id);

                        $job_po = $order_details['job_po'];

                        $deposit_amount = 0;
                        $usage_amount = 0;

                        switch ($type) {
                            case 'deposit':
                                $deposit_amount = abs($amount);
                                break;

                            case 'usage':
                                $usage_amount = abs($amount);
                                break;
                        }

                        $ledger_id = $row_jobs['ledger_id'];

                        ?>
                        <tr>
                            <td class="ps-0">
                                <h5 class="mb-1 text-center"><?= htmlspecialchars($job_po) ?></h5>
                            </td>
                            <td>
                                <h5 class="mb-1 text-center"><?= htmlspecialchars($job_name) ?></h5>
                            </td>
                            <td>
                                <?php if ($deposit_amount > 0): ?>
                                    <h5 class="mb-1 text-right" style="color: green !important;">
                                        + $<?= number_format($deposit_amount, 2); ?>
                                    </h5>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($usage_amount > 0): ?>
                                    <h5 class="mb-1 text-right" style="color: red !important;">
                                        - $<?= number_format($usage_amount, 2); ?>
                                    </h5>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="?page=job_details&customer_id=<?= $customerid ?>&job_name=<?= urlencode($job_name) ?>"
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
                                    data-customer-id="<?= $customerid ?>"
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
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="4" class="text-center">No jobs found</td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
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
    $deposited_by = trim($_POST['deposited_by'] ?? '');
    $reference_no = trim($_POST['reference_no'] ?? '');
    $payment_method = $_POST['type'] ?? 'cash';
    $check_no = $_POST['check_no'] ?? null;
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? 'Job deposit');

    $check_query = "SELECT * FROM jobs WHERE job_id = '$job_id'";
    $check_result = mysqli_query($conn, $check_query);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $check_no_sql = $payment_method === 'check' ? "'" . mysqli_real_escape_string($conn, $check_no) . "'" : "NULL";

        $insert = "
            INSERT INTO job_ledger (job_id, entry_type, amount, payment_method, check_number, reference_no, description, created_by)
            VALUES (
                '$job_id',
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


