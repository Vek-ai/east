<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
$permission = $_SESSION['permission'];

if (isset($_POST['search_product'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search_product']);

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

if (isset($_POST['fetch_available'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $details = getWorkOrderDetails($id);

    $color_id = $details['custom_color'];
    $grade = $details['custom_grade'];
    $width = floatval($details['custom_width']);
    $lengthFeet = floatval($details['custom_length'] ?? 0);
    $lengthInch = floatval($details['custom_length2'] ?? 0);
    $quantity = floatval($details['quantity'] ?? 1);

    $total_length = ($lengthFeet + ($lengthInch / 12)) * $quantity ?: 1;

    $total_length_reached = 0;
    $weighted_sum = 0;
    $total_weight = 0;
    ?>
    <style>
        .tooltip-inner { background-color: white !important; color: black !important; font-size: calc(0.875rem + 2px) !important; }
    </style>

    <div class="card card-body datatables">
        <div class="product-details table-responsive text-wrap">
            <h4>Coils List</h4>
            <table id="coil_dtls_tbl" class="table table-hover mb-0 text-md-nowrap text-center">
                <thead>
                    <tr>
                        <th></th>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>Coil No</th>
                        <th class="text-center">Date</th>
                        <th class="text-left">Color</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Thickness</th>
                        <th class="text-right">Width</th>
                        <th class="text-right">Rem. Feet</th>
                        <th class="text-right">Price/In</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $coils = getAvailableCoils($color_id, $grade, $width);
                    foreach ($coils as $row) {
                        $color_details = getColorDetails($row['color_sold_as']);

                        $weighted_sum += $row['price'] * $row['remaining_feet'];
                        $total_weight += $row['remaining_feet'];

                        $is_checked = 0;
                        if ($total_length_reached < $total_length) {
                            $total_length_reached += $row['remaining_feet'];
                            $is_checked = 1;
                        }
                    ?>
                        <tr data-id="<?= $row['coil_id'] ?>" data-length="<?= $total_length ?>">
                            <td><?= $is_checked ?></td>
                            <td class="text-start">
                                <input type="checkbox" class="row-select" data-id="<?= $row['coil_id'] ?>" <?= $is_checked ? 'checked' : '' ?>>
                            </td>
                            <td><?= $row['entry_no'] ?></td>
                            <td><?= date("M d, Y", strtotime($row['date'])) ?></td>
                            <td class="text-left">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <span class="rounded-circle d-block" style="background-color:<?= $color_details['color_code'] ?>; width: 20px; height: 20px;"></span>
                                    <?= $color_details['color_name'] ?>
                                </div>
                            </td>
                            <td><?= getGradeName($row['grade']) ?></td>
                            <td><?= $row['thickness'] ?></td>
                            <td class="text-right"><?= $row['width'] ?></td>
                            <td class="text-right"><?= $row['remaining_feet'] ?></td>
                            <td class="text-right"><?= number_format($row['price'], 2) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="9" class="text-right"><strong>Weighted Average Price:</strong></td>
                        <td class="text-right"><strong>$<?= number_format($total_weight > 0 ? $weighted_sum / $total_weight : 0, 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script>
        $(function () {
            let selectedCoils = [];

            $('#coil_dtls_tbl').off('change', '.row-select').on('change', '.row-select', function () {
                const id = $(this).data('id');
                if (this.checked) selectedCoils.push(id);
                else selectedCoils = selectedCoils.filter(i => i !== id);
            });

            $('#selectAll').off('change').on('change', function () {
                const checked = this.checked;
                $('#coil_dtls_tbl .row-select').prop('checked', checked).trigger('change');
            });

            $('#saveSelection').off('click').on('click', function () {
                const id = <?= $id ?? 0 ?>;
                const table = $('#coil_dtls_tbl').DataTable();
                const coils = $('input.row-select:checked', table.rows().nodes()).map(function () {
                    return $(this).data('id');
                }).get();

                $.ajax({
                    url: 'pages/work_order_list_ajax.php',
                    method: 'POST',
                    data: {
                        id: id,
                        selected_coils: JSON.stringify(coils),
                        assign_coil: 'assign_coil'
                    },
                    success: function (res) {
                        if (res.trim() === 'success') {
                            alert('Successfully Saved!');
                            location.reload();
                        } else {
                            alert('Failed to Update!');
                            console.log(res);
                        }
                    },
                    error: function (xhr, status, error) {
                        alert('AJAX error occurred: ' + error);
                        console.error('AJAX Error:', xhr.responseText);
                    }
                });
            });

            $.fn.dataTable.ext.type.order['custom-date-pre'] = function (d) {
                const parts = d.split(' ');
                return new Date(parts[2], ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'].indexOf(parts[0]), parseInt(parts[1])).getTime();
            };

            if (!$.fn.DataTable.isDataTable('#coil_dtls_tbl')) {
                $('#coil_dtls_tbl').DataTable({
                    language: { emptyTable: "No Available Coils with the selected color" },
                    autoWidth: false,
                    responsive: true,
                    columnDefs: [
                        { targets: 0, visible: false },
                        { targets: 1, width: "5%" },
                        { targets: 3, type: 'custom-date' }
                    ],
                    order: [[0, 'desc'], [3, 'asc']]
                });
            }

            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
<?php }

if (isset($_POST['assign_coil'])) {
    $id = intval($_POST['id']);
    $wrk_ordr = getWorkOrderDetails($id);
    $userid = $_SESSION['userid'] ?? 0;

    $orderid = $wrk_ordr['work_order_id'];

    $selected_coils = json_decode($_POST['selected_coils'], true);
    $coils_json = json_encode($selected_coils);

    $sql = "UPDATE work_order SET status = '1', assigned_coils = '$coils_json' WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "success";

        $actorId = $userid;
        $actor_name = get_staff_name($actorId);
        $actionType = 'new_work_order';
        $targetId = $id;
        $targetType = 'New Work Order';
        $message = "New Work Order SO-$orderid added by $actor_name.";
        $url = '?page=';
        $recipientRole = 'work_order';

        createNotification($actorId, $actionType, $targetId, $targetType, $message, $recipientRole, $url);
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

if (isset($_POST['search_work_order'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_search']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);

    $query = "
        SELECT wo.*, p.product_item
        FROM work_order AS wo
        LEFT JOIN product AS p ON p.product_id = wo.productid
        WHERE wo.status = '0'
    ";

    if (!empty($product_name) && $product_name != 'All Products') {
        $query .= " AND p.product_item LIKE '%$product_name%' ";
    }

    if (!empty($date_from) && !empty($date_to)) {
        $date_to .= ' 23:59:59';
        $query .= " AND (wo.submitted_date >= '$date_from' AND wo.submitted_date <= '$date_to') ";
    }else{
        $query .= " AND (wo.submitted_date >= DATE_SUB(curdate(), INTERVAL 2 WEEK) AND wo.submitted_date <= NOW()) ";
    }

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $total_amount = 0;
        $total_count = 0;

        ?>
        <table id="sales_table" class="table table-hover mb-0 text-md-nowrap">
            <thead>
                <tr>
                    <th class="w-20">Description</th>
                    <th>Color</th>
                    <th>Grade</th>
                    <th>Profile</th>
                    <th>Width</th>
                    <th>Length</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-center">Details</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Customer Price</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>     
            <?php
            $images_directory = "images/drawing/";

            $default_image = 'images/product/product.jpg';

            while ($row = mysqli_fetch_assoc($result)) {
                $color_details = getColorDetails($row['custom_color']);
                $product_id = $row['productid'];
                $width = $row['custom_width'];
                $bend = $row['custom_bend'];
                $hem = $row['custom_hem'];
                $length = $row['custom_length'];
                $inch = $row['custom_length2'];

                $picture_path = !empty($row['custom_img_src']) ? $images_directory.$row["custom_img_src"] : $default_image;
                ?>
                <tr data-id="<?= $product_id?>">
                    <td class="align-middle text-wrap w-20"> 
                        <a href="javascript:void(0);" class="d-inline-flex align-items-center justify-content-start">
                                <img src="<?= $picture_path ?>" style="background-color: #fff; width: 56px; height: 56px;" class="rounded-circle img-thumbnail preview-image" width="56" height="56" style="background-color: #fff;">
                            <div class="mt-1 ms-2"><?= getProductName($product_id) ?></div>
                        </a>
                    </td>
                    <td>
                    <div class="d-inline-flex align-items-center gap-2">
                        <a 
                            href="javascript:void(0)" 
                            id="viewAvailableBtn" 
                            data-app-prod-id="<?= $row['id'] ?>" 
                            class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                <span class="rounded-circle d-block" style="background-color:<?= $color_details['color_code'] ?>; width: 20px; height: 20px;"></span>
                                <?= $color_details['color_name'] ?>
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
                    <td class="text-center">
                        <?php 
                            $status = $row['status'];
                            switch ($status) {
                                case 0:
                                    $statusText = 'New';
                                    $statusColor = 'bg-primary';
                                    break;
                                case 1:
                                    $statusText = 'Pending Work Order';
                                    $statusColor = 'bg-warning';
                                    break;
                                case 2:
                                    $statusText = 'Processing';
                                    $statusColor = 'bg-info';
                                    break;
                                case 3:
                                    $statusText = 'Done';
                                    $statusColor = 'bg-success';
                                    break;
                                default:
                                    $statusText = 'Unknown';
                                    $statusColor = 'bg-secondary';
                                    break;
                            }
                        ?>
                        <span class="badge <?= $statusColor; ?>"><?= $statusText; ?></span>
                    </td>
                    <td class="text-end">$<?= number_format($row['actual_price'],2) ?></td>
                    <td class="text-end">
                        <span id='price_<?= $row['id'] ?>'>
                            $<?= number_format($row['discounted_price'],2) ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btn text-center">
                            <?php                                                    
                            if ($permission === 'edit') {
                            ?>

                            <a href="javascript:void(0)" class="text-decoration-none" id="viewAvailableBtn" data-app-prod-id="<?= $row['id'] ?>">
                                <i class="fa fa-arrow-right-to-bracket"></i>
                            </a>

                            <?php
                            }
                            ?>

                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
    } else {
        echo "<h4 class='text-center'>No Requests found</h4>";
    }
}


