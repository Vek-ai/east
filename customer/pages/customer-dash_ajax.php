<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

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
                    <th class="border-0">Status</th>
                    <th class="border-0 text-end">Price</th>
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

                            $status_labels = [
                                1 => ['label' => 'New Order', 'class' => 'badge bg-primary'],
                                2 => ['label' => 'Processing', 'class' => 'badge bg-warning'],
                                3 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
                                4 => ['label' => 'Delivered', 'class' => 'badge bg-success']
                            ];

                            $status = $status_labels[$status_code];
                            $status_html = '<span class="' . $status['class'] . ' ">' . $status['label'] . '</span>';

                            ?>
                            <tr>
                                <td class="ps-0">
                                    <div class="hstack gap-3">
                                        <span class="round-48 rounded-circle overflow-hidden flex-shrink-0 hstack justify-content-center">
                                            <img src="../assets/images/profile/user-2.jpg" alt class="img-fluid">
                                        </span>
                                        <div>
                                            <h5 class="mb-1"><?= get_staff_name($row['cashier']) ?></h5>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="mb-0"><?= date("F d, Y", strtotime($row['order_date'])) ?></p>
                                </td>
                                <td>
                                    <?= $status_html ?>
                                </td>
                                <td class="text-end">
                                    <p class="mb-0">$<?= number_format(getOrderTotalsDiscounted($row['orderid']),2) ?></p>
                                </td>
                                <td class="text-end">
                                    <?php
                                    if($status_code != null){
                                    ?>
                                    <a href="index.php?page=order&id=<?=$row["orderid"]?>&key=<?=$row["order_key"]?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button"><i class="text-warning fa fa-sign-in-alt fs-5"></i></a>
                                    <?php 
                                    }
                                    ?>
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
                            <td colspan="5">No orders found</td>
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
                    <th class="border-0 text-end">Price</th>
                    <th class="border-0 text-end"></th>
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

                            $status_code = $row['status'];

                            $status_labels = [
                                1 => ['label' => 'New Estimate', 'class' => 'badge bg-primary'],
                                2 => ['label' => 'Sent to Customer', 'class' => 'badge bg-success text-dark'],
                                3 => ['label' => 'Modified by Customer', 'class' => 'badge bg-warning text-dark'],
                                4 => ['label' => 'Approved', 'class' => 'badge bg-secondary'],
                                5 => ['label' => 'Processing', 'class' => 'badge bg-success'],
                                6 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
                                7 => ['label' => 'Delivered', 'class' => 'badge bg-success']
                            ];

                            $status = $status_labels[$status_code];
                            $status_html = '<span class="' . $status['class'] . ' ">' . $status['label'] . '</span>';
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
                                <p class="mb-0 fs-3">$<?= number_format(getEstimateTotalsDiscounted($row['estimateid']),2) ?></p>
                            </td>
                            <td class="text-end">
                                <?php
                                if($status_code != 1){
                                   ?>
                                   <a href="index.php?page=estimate&id=<?=$row["estimateid"]?>&key=<?=$row["est_key"]?>" target="_blank" class="btn btn-danger-gradient btn-sm p-0 me-1" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="text-warning fa fa-sign-in-alt fs-5"></i></a>
                                   <?php 
                                }
                                ?>
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
                            <td colspan="5">No estimates found</td>
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

            $default_image = '../images/product/product.jpg';

            $picture_path = !empty($row_product['main_image'])
            ? '../' .$row_product['main_image']
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
                                <td class="text-end">$ <?= $discounted_price ?></td>
                            </tr>
                    <?php
                            $totalquantity += $row['quantity'] ;
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                                $product_id = $row['productid'];
                                $actual_price = $discounted_price = 0;
                                if($row['quantity'] > 0){
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
                                    <td class="text-end">$ <?= $discounted_price ?></td>
                                </tr>
                        <?php
                                $totalquantity += $row['quantity'] ;
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    $estimateid = $row['estimateid'];
                                                    $product_details = getProductDetails($row['product_id']);
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
                                                        <td class="text-end">$ <?= $discounted_price ?></td>
                                                    </tr>
                                            <?php
                                                    $totalquantity += $row['quantity'] ;
                                                    $total_disc_price += $discounted_price;
                                                }
                                            
                                            ?>
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <td colspan="4">Total</td>
                                                <td class="text-start"><?= $totalquantity ?></td>
                                                <td></td>
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