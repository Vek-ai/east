<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

$emailSender = new EmailTemplates();

if (isset($_POST['search_returns'])) {
    $response = [
        'orders' => [],
        'total_count' => 0,
        'total_amount' => 0,
        'error' => null
    ];

    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name'] ?? '');
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from'] ?? '');
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to'] ?? '');
    $months = array_map('intval', $_POST['months'] ?? []);
    $years = array_map('intval', $_POST['years'] ?? []);

    $query = "
        SELECT *
        FROM coil_transaction
        WHERE 1=1
    ";

    if (!empty($date_from) && !empty($date_to)) {
        $date_to .= ' 23:59:59';
        $query .= " AND date BETWEEN '$date_from' AND '$date_to' ";
    } elseif (!empty($date_from)) {
        $query .= " AND date >= '$date_from' ";
    } elseif (!empty($date_to)) {
        $date_to .= ' 23:59:59';
        $query .= " AND date <= '$date_to' ";
    } else {
        $today_start = date('Y-m-d 00:00:00');
        $today_end   = date('Y-m-d 23:59:59');
        $query .= " AND date BETWEEN '$today_start' AND '$today_end' ";
    }

    if (!empty($months)) {
        $months_in = implode(',', $months);
        $query .= " AND MONTH(o.order_date) IN ($months_in) ";
    }

    if (!empty($years)) {
        $years_in = implode(',', $years);
        $query .= " AND YEAR(o.order_date) IN ($years_in) ";
    }

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['id'];
            $coilid = $row['coilid'];
            $coil = getCoilProductDetails($coilid);
            $entry_no = $coil['entry_no'];
            $date = date("F d, Y", strtotime($row['date']));
            
            $remaining_feet = floatval($row['remaining_length']);
            $length_before_use = floatval($row['length_before_use']);

            $used_feet = $length_before_use - $remaining_feet;
            if ($used_feet < 0) {
                $used_feet = 0;
            }

            $used_in_workorders = $row['used_in_workorders'];

            $response['coils'][] = [
                'id' => $id,
                'coilid' => $coilid,
                'entry_no' => $entry_no,
                'date' => $date,
                'used_feet' => $used_feet,
                'remaining_feet' => $remaining_feet,
                'used_in_workorders' => $used_in_workorders,
            ];
        }

    } else {
        $response['error'] = 'No coil usage found';
    }

    echo json_encode($response);
}

if(isset($_POST['fetch_usage_details'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $work_order_details = getWorkOrderDetails($id);
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
            <?php
                $query = "
                    SELECT 
                        wo.*, 
                        p.product_item, 
                        wo.work_order_id
                    FROM 
                        work_order AS wo
                    LEFT JOIN 
                        product AS p ON 
                            p.product_id = wo.productid
                    WHERE 
                        wo.id = '$id'
                ";

                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    $total_amount = 0;
                    $total_count = 0;

                    ?>
                    <table id="work_order_table_dtls" class="table table-hover mb-0 text-md-nowrap">
                        <thead>
                            <tr>
                                <th class="align-middle">Order #</th>
                                <th class="w-20 align-middle">Description</th>
                                <th class="text-center align-middle">Cashier</th>
                                <th class="text-center align-middle">Color</th>
                                <th class="text-center align-middle">Grade</th>
                                <th class="text-center align-middle">Profile</th>
                                <th class="text-center align-middle">Width</th>
                                <th class="text-center align-middle">Length</th>
                                <th class="text-center align-middle">Status</th>
                                <th class="text-center align-middle">Quantity</th>
                                <th class="text-center align-middle">Details</th>
                            </tr>
                        </thead>
                        <tbody>     
                        <?php
                        $images_directory = "images/drawing/";
                        $no = 1;

                        $default_image = 'images/product/product.jpg';
                        while ($row = mysqli_fetch_assoc($result)) {
                            $color_details = getColorDetails($row['custom_color']);
                            $product_id = $row['productid'];
                            $product_details = getProductDetails($product_id);
                            $width = $row['custom_width'];
                            $bend = $row['custom_bend'];
                            $hem = $row['custom_hem'];
                            $length = $row['custom_length'];
                            $inch = $row['custom_length2'];
                            $inventory_type = '';
                            $status = $row['status'];

                            $status = (int)$row['status'];
                            $statusText = '';

                            switch ($status) {
                                case 1:
                                    $statusText = 'New';
                                    break;
                                case 2:
                                    $statusText = 'Processing';
                                    break;
                                case 3:
                                    $statusText = 'Done';
                                    break;
                                default:
                                    $statusText = 'Unknown';
                            }

                            $order_no = $row['work_order_id'];

                            $order_no = 'SO-' .$order_no ."-$no";

                            $picture_path = !empty($row['custom_img_src']) ? $images_directory.$row["custom_img_src"] : $default_image;
                            ?>
                            <tr data-id="<?= $product_id ?>"
                                data-category="<?= getProductCategoryName($row['product_category']) ?>"
                                data-type="<?= getProductTypeName($product_details['product_type']) ?>"
                                data-inventory="<?= $inventory_type ?>"
                                data-width="<?= $width ?>"
                                data-grade="<?= getGradeName($row['custom_grade']) ?>"
                                data-gauge="<?= getGaugeName($product_details['gauge']) ?>"
                                data-color="<?= getColorName($row['custom_color']) ?>"
                                data-profile="<?= getProfileTypeName($product_details['profile']) ?>"
                                data-status="<?= $statusText ?>"
                                data-order="<?= $order_type ?>"

                            >
                                <td class="align-middle">
                                    <?= $order_no ?>
                                </td>
                                <td class="align-middle text-wrap w-20"> 
                                    <a href="javascript:void(0);" class="d-inline-flex align-items-center justify-content-start">
                                            <img src="<?= $picture_path ?>" style="background-color: #fff; width: 56px; height: 56px;" class="rounded-circle img-thumbnail preview-image" width="56" height="56" style="background-color: #fff;">
                                        <div class="mt-1 ms-2"><?= getProductName($product_id) ?></div>
                                    </a>
                                </td>
                                <td>
                                    <?= get_name($row['user_id']); ?>
                                </td>
                                <td>
                                <div class="d-inline-flex align-items-center gap-2">
                                    <a 
                                        href="javascript:void(0)" 
                                        id="viewAvailableBtn" 
                                        data-app-prod-id="<?= $row['id'] ?>" 
                                        class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                            <span class="rounded-circle d-block" style="background-color:<?= $color_details['color_code'] ?? '' ?>; width: 20px; height: 20px;"></span>
                                            <?= $color_details['color_name'] ?? '' ?>
                                    </a>
                                </div>
                                </td>
                                <td>
                                    <?php echo getGradeName($row['custom_grade']); ?>
                                </td>
                                <td>
                                    <?php echo getProfileFromID($product_id); ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    if (!empty($width)) {
                                        echo htmlspecialchars($width);
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    if (!empty($length)) {
                                        echo htmlspecialchars($length) . " ft";
                                        
                                        if (!empty($inch)) {
                                            echo " " . htmlspecialchars($inch) . " in";
                                        }
                                    } elseif (!empty($inch)) {
                                        echo htmlspecialchars($inch) . " in";
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $status = (int)$row['status'];
                                    $statusText = '';
                                    $statusClass = '';

                                    switch ($status) {
                                        case 1:
                                            $statusText = 'New';
                                            $statusClass = 'badge bg-primary';
                                            break;
                                        case 2:
                                            $statusText = 'Processing';
                                            $statusClass = 'badge bg-warning text-dark';
                                            break;
                                        case 3:
                                            $statusText = 'Done';
                                            $statusClass = 'badge bg-success';
                                            break;
                                        default:
                                            $statusText = 'Unknown';
                                            $statusClass = 'badge bg-secondary';
                                    }
                                    ?>
                                    <span class="<?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td class="text-center">
                                    <?php echo $row['quantity']; ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    if (!empty($bend)) {
                                        echo "Bend: " . htmlspecialchars($bend) . "<br>";
                                    }
                                    
                                    if (!empty($hem)) {
                                        echo "Hem: " . htmlspecialchars($hem) . "<br>";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                            $no++;
                        }
                        ?>
                        </tbody>
                    </table>
                    <?php
                } else {
                    echo "<h4 class='text-center'>No Requests found $id</h4>";
                }
                ?>
        </div>
    </div>

    
    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#work_order_table_dtls')) {
                $('#work_order_table_dtls').DataTable().clear().destroy();
            }

            var table = $('#work_order_table_dtls').DataTable({
                pageLength: 100
            });
        });
    </script>
    <?php
}

if (isset($_POST['fetch_coil_details'])) {
    $coil_id = mysqli_real_escape_string($conn, $_POST['coil_id']);
    $coil = getCoilProductDetails($coil_id); 
    ?>
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-1">
                        <small class="text-muted d-block">Entry No</small>
                        <span class="fw-bold fs-6"><?= $coil['entry_no'] ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-1">
                        <small class="text-muted d-block">Color</small>
                        <span class="fw-bold fs-6"><?= getColorName($coil['color_sold_as']) ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-1">
                        <small class="text-muted d-block">Grade</small>
                        <span class="fw-bold fs-6"><?= getGradeName($coil['grade']) ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-1">
                        <small class="text-muted d-block">Gauge</small>
                        <span class="fw-bold fs-6"><?= getGaugeName($coil['gauge']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

