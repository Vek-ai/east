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
                    <th class="border-0 ps-0">
                    Sales Person
                    </th>
                    <th class="border-0">Date</th>
                    <th class="border-0 text-end">
                    Total Amount
                    </th>
                    <th class="border-0 text-end">
                    Discount
                    </th>
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
                                    <p class="mb-0">$<?= number_format($row['total_price'],2) ?></p>
                                </td>
                                <td class="text-end">
                                    <p class="mb-0">$<?= number_format($row['discounted_price'],2) ?></p>
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
                    <th class="border-0 ps-0">
                    Estimate ID
                    </th>
                    <th class="border-0">Status</th>
                    <th class="border-0">
                    No. of changes
                    </th>
                    <th class="border-0 text-end">
                    Total Amount
                    </th>
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
                                <p class="mb-0 fs-3">$<?= number_format($row['discounted_price'],2) ?></p>
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
                </tr>';

        }
    } else {
        $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    
    //echo $tableHTML;
    echo $tableHTML;
}