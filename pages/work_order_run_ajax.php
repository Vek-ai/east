<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
$permission = $_SESSION['permission'];

if(isset($_POST['fetch_available'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $work_order_details = getWorkOrderDetails($id);
    $assigned_coils = $work_order_details['assigned_coils'];
    $decoded_coils = json_decode($assigned_coils, true);
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
            <h5>Coils List</h5>
            <table id="coil_dtls_tbl" class="table table-hover mb-0 text-md-nowrap text-center">
                <thead>
                    <tr>
                        <th class="text-center">Coil</th>
                        <th class="text-center">Date</th>
                        <th class="text-left">Color</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Thickness</th>
                        <th class="text-right">Width</th>
                        <th class="text-right">Rem. Feet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (is_array($decoded_coils)) {
                        $coils_string = implode(',', $decoded_coils);

                        $query = "SELECT * FROM coil_product WHERE coil_id IN ($coils_string) ORDER BY date ASC";
                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $color_details = getColorDetails($row['color_sold_as']);
                                ?>
                                <tr data-id="<?= $row['coil_id'] ?>">
                                    <td class="text-wrap">
                                        <a href="javascript:void(0)" 
                                        class="coil-entry" 
                                        data-entry="<?= htmlspecialchars($row['entry_no']) ?>" 
                                        data-warehouse="<?= $row['warehouse'] ?>">
                                            <?= htmlspecialchars($row['entry_no']) ?>
                                        </a>
                                    </td>
                                    <td class="text-wrap"> 
                                        <?= date("M d, Y", strtotime($row['date'])) ?>
                                    </td>
                                    <td class="text-left">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <span class="rounded-circle d-block" style="background-color:<?= $color_details['color_code'] ?>; width: 20px; height: 20px;"></span>
                                            <?= $color_details['color_name'] ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?= getGradeName($row['grade']); ?>
                                    </td>
                                    <td>
                                        <?= $row['thickness']; ?>
                                    </td>
                                    <td class="text-right">
                                        <?= $row['width']; ?>
                                    </td>
                                    <td class="text-right">
                                        <?= $row['remaining_feet']; ?>
                                    </td>
                                </tr>
                                <?php
                                $no++;
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php                                                    
    if ($permission === 'edit') {
    ?>
    <div class="modal-footer">
        <?php if (!empty($work_order_details) && $work_order_details['status'] == 2): ?>
            <button id="finish_work_order" class="btn ripple btn-primary" type="button">Finish Work Order</button>
        <?php endif; ?>
    </div>
    <?php                                                    
    }
    ?>


    <div class="modal fade" id="coilWarehouseModal" tabindex="-1" aria-labelledby="coilWarehouseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="coilWarehouseModalLabel">Coil Warehouse Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h6>Coil Name:</h6>
                <p id="modalEntryNo" class="fw-bold fs-5"></p>
                <h6>Warehouse Location:</h6>
                <p id="modalWarehouse" class="fw-bold text-primary fs-5"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
    </div>
       
    <script>
        $(document).ready(function() {
            $('#finish_work_order').off('click').on('click', function () {
                const id = <?= $id ?? 0 ?>;
                if (!id) return;

                $.ajax({
                    url: 'pages/work_order_run_ajax.php',
                    type: 'POST',
                    data: {
                        selected_ids: [id],
                        finish_work_order: true
                    },
                    success: function (res) {
                        if (res.trim() === 'success') {
                            alert('Work order marked as completed!');
                            location.reload();
                        } else {
                            alert('Failed to complete work order.');
                            console.log(res);
                        }
                    }
                });
            });

            $('.coil-entry').off('click').on('click', function () {
                const entryNo = $(this).data('entry');
                const warehouse = $(this).data('warehouse');

                $('#modalEntryNo').text(entryNo);
                $('#modalWarehouse').text(warehouse);

                const coilModal = new bootstrap.Modal(document.getElementById('coilWarehouseModal'));
                coilModal.show();
            });

            $('[data-toggle="tooltip"]').tooltip(); 

            if ($.fn.DataTable.isDataTable('#coil_dtls_tbl')) {
                $('#coil_dtls_tbl').DataTable().order([[0, 'desc'], [3, 'asc']]).draw();
            } else {
                $('#coil_dtls_tbl').DataTable({
                    language: {
                        emptyTable: "No Assigned Coils"
                    },
                    autoWidth: false,
                    responsive: true,
                    order: [
                        [0, 'desc'],
                        [3, 'asc']
                    ]
                });
            }
        });
    </script>
    <?php
}

if(isset($_POST['fetch_view'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $work_order_details = getWorkOrderDetails($id);
    $assigned_coils = $work_order_details['assigned_coils'];
    $decoded_coils = json_decode($assigned_coils, true);
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
                        wo.work_order_id = '$id' AND wo.status = 2
                ";

                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    $total_amount = 0;
                    $total_count = 0;

                    ?>
                    <table id="work_order_table_dtls" class="table table-hover mb-0 text-md-nowrap">
                        <thead>
                            <tr>
                                <th class="text-center align-middle">
                                    <input type="checkbox" id="selectAll">
                                </th>
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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>     
                        <?php
                        $images_directory = "../images/drawing/";
                        $no = 1;

                        $default_image = '../images/product/product.jpg';
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
                                <td class="text-center align-middle">
                                    <input type="checkbox" class="row-check" value="<?= $row['id'] ?>">
                                </td>
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
                                <td>
                                    <div class="action-btn text-center">
                                        <a href="javascript:void(0)" class="text-decoration-none" id="viewAvailableBtn" title="Finish Work Order" data-app-prod-id="<?= $row['id'] ?>">
                                            <i class="fa fa-arrow-right-to-bracket"></i>
                                        </a>
                                    </div>
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
                    echo "<h4 class='text-center'>No Requests found</h4>";
                }
                ?>
        </div>
    </div>
    <div class="d-flex justify-content-end mt-3">
        <?php                                                    
        if ($permission === 'edit') {
        ?>
        <button type="button" class="btn btn-success" id="finishSelectedBtn">
            <i class="fa fa-play me-1"></i> Finish
        </button>
        <?php                                                    
        }
        ?>

    </div>

    <div class="modal fade" id="coilWarehouseModal" tabindex="-1" aria-labelledby="coilWarehouseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="coilWarehouseModalLabel">Coil Warehouse Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h6>Coil Name:</h6>
                <p id="modalEntryNo" class="fw-bold fs-5"></p>
                <h6>Warehouse Location:</h6>
                <p id="modalWarehouse" class="fw-bold text-primary fs-5"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
    </div>
       
    <script>
        $(document).ready(function() {

            $('.coil-entry').off('click').on('click', function () {
                const entryNo = $(this).data('entry');
                const warehouse = $(this).data('warehouse');

                $('#modalEntryNo').text(entryNo);
                $('#modalWarehouse').text(warehouse);

                const coilModal = new bootstrap.Modal(document.getElementById('coilWarehouseModal'));
                coilModal.show();
            });

            $(document).on('change', '#selectAll', function () {
                $('.row-check').prop('checked', this.checked);
            });

            $(document).on('click', '#finishSelectedBtn', function () {
                const selectedIds = $('.row-check:checked').map(function () {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) {
                    alert('Please select at least one item to run.');
                    return;
                }

                const id = <?= $id ?? 0 ?>;

                $.ajax({
                    url: 'pages/work_order_run_ajax.php',
                    method: 'POST',
                    data: {
                        id: id,
                        selected_ids: selectedIds,
                        finish_work_order: 'finish_work_order'
                    },
                    success: function (res) {
                        if (res.trim() === 'success') {
                            alert('Work Order Marked as Done and is now pending for Release.');
                            location.reload();
                        } else {
                            alert('Failed to update coil lengths.');
                            console.log(res);
                        }
                    },
                    error: function (xhr) {
                        alert('An error occurred: ' + xhr.statusText);
                        console.error(xhr.responseText);
                    }
                });
            });

            if ($.fn.DataTable.isDataTable('#coil_dtls_tbl')) {
                $('#coil_dtls_tbl').DataTable().order([[0, 'desc'], [3, 'asc']]).draw();
            } else {
                $('#coil_dtls_tbl').DataTable({
                    language: {
                        emptyTable: "No Assigned Coils"
                    },
                    autoWidth: false,
                    responsive: true,
                    order: [
                        [0, 'desc'],
                        [3, 'asc']
                    ]
                });
            }
        });
    </script>
    <?php
}

if (isset($_POST['finish_work_order'])) {
    $ids = $_POST['selected_ids'] ?? [];

    if (!empty($ids)) {
        $sanitized_ids = array_map(function($id) use ($conn) {
            return "'" . mysqli_real_escape_string($conn, $id) . "'";
        }, $ids);

        $id_list = implode(',', $sanitized_ids);

        $update = "UPDATE work_order SET status = 3 WHERE id IN ($id_list)";
        if (mysqli_query($conn, $update)) {
            echo "success";
        } else {
            echo "error: " . mysqli_error($conn);
        }
    } else {
        echo "error: No IDs selected.";
    }
    exit;
}



