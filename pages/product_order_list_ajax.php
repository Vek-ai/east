<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['search_customer'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search_customer']);

    $query = "
        SELECT 
            product_id AS value, 
            product_item AS label
        FROM 
            product
        WHERE 
            product_item LIKE '%$search%' 
            AND status = '1'
        LIMIT 15
    ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        $response = array();

        $response[] = array(
            'value' => 'All Products',
            'label' => 'All Products'
        );

        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }

        echo json_encode($response);
    } else {
        echo json_encode(array('error' => 'Query failed'));
    }
}

if (isset($_POST['search_product_orders'])) {
    $product_search = mysqli_real_escape_string($conn, $_POST['product_search']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);

    $query = "
        SELECT p.product_id, 
            p.product_item, 
            COALESCE(SUM(op.quantity), 0) AS total_quantity_sold
        FROM product p
        LEFT JOIN order_product op ON p.product_id = op.productid
        LEFT JOIN orders o ON op.orderid = o.orderid
        WHERE 1=1
    ";

    if (!empty($product_search) && $product_search != 'All Products') {
        $query .= " AND p.product_item LIKE '%$product_search%'";
    }

    if (!empty($date_from)) {
        $query .= " AND o.order_date >= '$date_from 00:00:00'";
    }

    if (!empty($date_to)) {
        $query .= " AND o.order_date <= '$date_to 23:59:59'";
    }

    $query .= "
        GROUP BY p.product_id
        ORDER BY total_quantity_sold DESC;
    ";

    echo "<script>console.log('$query')</script>";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $total_amount = 0;
        $total_count = 0;

        ?>
        <table id="sales_table" class="table table-hover mb-0 text-md-nowrap">
            <thead>
                <tr>
                    <th>Product Item</th>
                    <th>Quantity Sold</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>     
            <?php

            while ($row = mysqli_fetch_assoc($result)) {

                $product_item = $row['product_item'];
                $total_quantity_sold = $row['total_quantity_sold'];
                ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($product_item) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($total_quantity_sold) ?>
                    </td>
                    <td>
                        <a href="javascript:void(0)" title="View" data-id="<?= $row['product_id'] ?>" id="view_details">
                            <i class="fa fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
    } else {
        echo "<h4 class='text-center'>No orders found</h4>";
    }
}

if (isset($_POST['fetch_product_orders'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['id']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);
    ?>
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="mb-2">
                <h5 class="fw-bold mb-1">Product Item: <?= getProductName($product_id) ?></h5>
            </div>
        </div>
    </div>
    <div class="datatables">
        <div class="table-responsive">
            <table id="product_order_dtls_tbl" class="table table-hover mb-0 text-wrap w-100">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Color</th>
                        <th>Grade</th>
                        <th>Profile</th>
                        <th>Quantity</th>
                        <th class="text-center">Dimensions</th>
                        <th class="text-center">Details</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Customer Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $query = "
                        SELECT 
                            op.*, 
                            o.originalcustomerid as customer_id,
                            o.order_date
                        FROM order_product as op
                        LEFT JOIN orders as o ON o.orderid = op.orderid
                        WHERE op.productid = '$product_id'";

                    if (!empty($date_from)) {
                        $query .= " AND order_date >= '$date_from 00:00:00'";
                    }
                
                    if (!empty($date_to)) {
                        $query .= " AND order_date <= '$date_to 23:59:59'";
                    }

                    $result = mysqli_query($conn, $query);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        $totalquantity = $total_actual_price = $total_disc_price = 0;
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $product_details = getProductDetails($row['productid ']);
                            ?> 
                            <tr> 
                                <td>
                                    <?= get_customer_name($row['customer_id']) ?>
                                </td>
                                <td>
                                <div class="d-flex mb-0 gap-8">
                                    <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($row['custom_color'])?>"></a>
                                    <?= getColorFromID($row['custom_color']); ?>
                                </div>
                                </td>
                                <td>
                                    <?php echo getGradeName($row['custom_grade']); ?>
                                </td>
                                <td>
                                    <?php echo getProfileTypeName($product_details['profile']); ?>
                                </td>
                                <td><?= $row['quantity'] ?></td>
                                <td>
                                    <?php 
                                        $width = $row['custom_width'];
                                        $height = $row['custom_height'];
                                        $feet = $row['custom_length'];
                                        $inch = $row['custom_length2'];

                                        if (!empty($width) && !empty($height)) {
                                            echo htmlspecialchars($width) . " X " . htmlspecialchars($height);
                                        } elseif (!empty($width)) {
                                            echo "Width: " . htmlspecialchars($width);
                                        } elseif (!empty($height)) {
                                            echo "Height: " . htmlspecialchars($height);
                                        }

                                        if (!empty($feet) && !empty($inch)) {
                                            echo " " . htmlspecialchars($feet) . " ft " . htmlspecialchars($inch) . " in";
                                        } elseif (!empty($feet)) {
                                            echo " " . htmlspecialchars($feet) . " ft";
                                        } elseif (!empty($inch)) {
                                            echo " " . htmlspecialchars($inch) . " in";
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        $stiff_stand_seam = $row['stiff_stand_seam'];
                                        $stiff_board_batten = $row['stiff_board_batten'];
                                        $panel_type = $row['panel_type'];

                                        if (!empty($stiff_stand_seam)) {
                                            echo "Standing Seam Style: ";
                                            switch ($stiff_stand_seam) {
                                                case 1:
                                                    echo "Striated<br>";
                                                    break;
                                                case 2:
                                                    echo "Flat<br>";
                                                    break;
                                                case 3:
                                                    echo "Minor Rib<br>";
                                                    break;
                                                default:
                                                    echo "";
                                                    break;
                                            }
                                        }

                                        if (!empty($stiff_board_batten)) {
                                            echo "Board and Batten Style: ";
                                            switch ($stiff_board_batten) {
                                                case 1:
                                                    echo "Flat<br>";
                                                    break;
                                                case 2:
                                                    echo "Minor Rib<br>";
                                                    break;
                                                default:
                                                    echo "";
                                                    break;
                                            }
                                        }

                                        if (!empty($panel_type)) {
                                            echo "Panel Type: ";
                                            switch ($panel_type) {
                                                case 1:
                                                    echo "Solid<br>";
                                                    break;
                                                case 2:
                                                    echo "Vented<br>";
                                                    break;
                                                case 3:
                                                    echo "Drip Stop<br>";
                                                    break;
                                                default:
                                                    echo "";
                                                    break;
                                            }
                                        }
                                        ?>
                                </td>
                                <td class="text-end">$ <?= number_format($row['actual_price'],2) ?></td>
                                <td class="text-end">$ <?= number_format($row['discounted_price'],2) ?></td>
                            </tr>
                            <?php
                            $totalquantity += $row['quantity'] ;
                            $total_actual_price += $row['actual_price'];
                            $total_disc_price += $row['discounted_price'];
                        }
                    }
                    ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <p class="m-1">Total Quantity: <?= $totalquantity ?></p>
                </div>
                <div class="text-center">
                    <p class="m-1">Actual Price: $<?= number_format($total_actual_price,2) ?></p>
                </div>
                <div class="text-right">
                    <p class="m-1">Discounted Price: $<?= number_format($total_disc_price,2) ?></p>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            var table = $('#product_order_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Product Order not found"
                },
                responsive: true,
                lengthChange: false,
                paging: false,
                info: false
            });

            $('#view_product_order_details_modal').on('shown.bs.modal', function () {
                table.columns.adjust().responsive.recalc();
            });
        });
    </script>
    <?php
}