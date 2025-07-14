<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_available'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $work_order_details = getSubmitWorkOrderDetails($id);
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
                        <th class="text-right">Price/In</th>
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
                                    <td class="text-right">
                                        <?= number_format($row['price'], 2); ?>
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
    <div class="modal-footer">
        <?php if (!empty($work_order_details) && $work_order_details['status'] == 1): ?>
            <button id="run_work_order" class="btn ripple btn-success" type="button">Run Work Order</button>
        <?php elseif (!empty($work_order_details) && $work_order_details['status'] == 2): ?>
            <button id="finish_work_order" class="btn ripple btn-primary" type="button">Finish Work Order</button>
        <?php endif; ?>
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
            $('#run_work_order').off('click').on('click', function () {
                const id = <?= $id ?? 0 ?>;

                $.ajax({
                    url: 'pages/work_order_ajax.php',
                    method: 'POST',
                    data: {
                        id: id,
                        run_work_order: 'run_work_order'
                    },
                    success: function (res) {
                        if (res.trim() === 'success') {
                            alert('Work Order Completed. Coil lengths updated.');
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

            $('#finish_work_order').off('click').on('click', function () {
                const id = <?= $id ?? 0 ?>;
                if (!id) return;

                $.ajax({
                    url: 'pages/work_order_ajax.php',
                    type: 'POST',
                    data: {
                        id,
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
    $work_order_details = getSubmitWorkOrderDetails($id);
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
                    SELECT wo.*, p.product_item, wop.type as order_type, wop.work_order_id
                    FROM work_order AS wo
                    LEFT JOIN work_order_product AS wop ON wo.work_order_product_id = wop.id
                    LEFT JOIN product AS p ON p.product_id = wo.productid
                    WHERE wop.work_order_id = '$id'
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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>     
                        <?php
                        $images_directory = "../images/drawing/";

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
                            $order_type = $row['order_type'];

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

                            if($order_type == 1){
                                $order_no = 'ES-'  .$order_no;
                            }else{
                                $order_no = 'SO-'  .$order_no;
                            }


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
                                <td>
                                    <div class="action-btn text-center">
                                        <a href="javascript:void(0)" class="text-decoration-none" id="viewAvailableBtn" title="Run Work Order" data-app-prod-id="<?= $row['id'] ?>">
                                            <i class="fa fa-arrow-right-to-bracket"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="text-decoration-none" id="viewAssignedBtn" title="View" data-id="<?= $row['id'] ?>">
                                            <i class="fa fa-eye"></i>
                                        </a>
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
                ?>
        </div>
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

if(isset($_POST['fetch_assigned'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $work_order_details = getSubmitWorkOrderDetails($id);
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
            <table id="coils_selected_tbl" class="table table-hover mb-0 text-md-nowrap text-center">
                <thead>
                    <tr>
                        <th class="text-center">Coil</th>
                        <th class="text-center">Date</th>
                        <th class="text-left">Color</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Thickness</th>
                        <th class="text-right">Width</th>
                        <th class="text-right">Rem. Feet</th>
                        <th class="text-right"></th>
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
                                    <td class="text-right">
                                        <a href="javascript:void(0)" class="text-decoration-none" id="viewCoilsBtn" title="Change" data-id="<?= $id ?>">
                                            <i class="fa fa-edit text-warning"></i>
                                        </a>
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


            if ($.fn.DataTable.isDataTable('#coils_selected_tbl')) {
                $('#coils_selected_tbl').DataTable().order([[0, 'desc'], [3, 'asc']]).draw();
            } else {
                $('#coils_selected_tbl').DataTable({
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


if (isset($_POST['fetch_coils'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $details = getSubmitWorkOrderDetails($id);

    $assigned_coils = json_decode($details['assigned_coils'] ?? '[]', true) ?? [];

    $color_id = $details['custom_color'];
    $grade = $details['custom_grade'];
    $width = floatval($details['custom_width']);
    $lengthFeet = floatval($details['custom_length'] ?? 0);
    $lengthInch = floatval($details['custom_length2'] ?? 0);
    $quantity = floatval($details['quantity'] ?? 1);

    $total_length = ($lengthFeet + ($lengthInch / 12)) * $quantity ?: 1;

    $where = "WHERE 1=1";
    if (!empty($color_id)) $where .= " AND color_sold_as = '" . mysqli_real_escape_string($conn, $color_id) . "'";
    if (!empty($grade)) $where .= " AND grade = '" . mysqli_real_escape_string($conn, $grade) . "'";
    if (!empty($width)) $where .= " AND width >= $width";

    $query = "SELECT * FROM coil_product $where ORDER BY date ASC";
    $result = mysqli_query($conn, $query);

    $total_length_reached = 0;
    $weighted_sum = 0;
    $total_weight = 0;
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
            <h4>Coils List</h4>
            <table id="coils_tbl" class="table table-hover mb-0 text-md-nowrap text-center">
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
                    <?php while ($row = mysqli_fetch_assoc($result)):
                        $coil_id = $row['coil_id'];
                        $color_details = getColorDetails($row['color_sold_as']);

                        $weighted_sum += $row['price'] * $row['remaining_feet'];
                        $total_weight += $row['remaining_feet'];

                        $is_checked = in_array($coil_id, $assigned_coils);
                    ?>
                        <tr data-id="<?= $coil_id ?>" data-length="<?= $total_length ?>">
                            <td><?= $is_checked ? 1 : 0 ?></td>
                            <td class="text-start">
                                <input type="checkbox" class="row-select" data-id="<?= $coil_id ?>" <?= $is_checked ? 'checked' : '' ?>>
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
                    <?php endwhile; ?>
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

    <div class="modal-footer d-flex justify-content-end">
        <button id="save_selected_coils" class="btn ripple btn-success me-2" type="button">Change</button>
        <button class="btn ripple btn-danger" type="button" data-bs-dismiss="modal">Close</button>
    </div>

    <div class="modal fade" id="confirmChangeModal" tabindex="-1" aria-labelledby="confirmChangeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content p-3">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Coil Change</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to change the coils?</p>
                    <div class="form-check">
                        <input class="form-check-input reason-radio" type="radio" name="change_reason" id="reason_defective" value="defective" required>
                        <label class="form-check-label" for="reason_defective">Defective</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input reason-radio" type="radio" name="change_reason" id="reason_others" value="others">
                        <label class="form-check-label" for="reason_others">Others</label>
                    </div>
                    <div class="mt-3">
                        <label for="change_notes" class="form-label">Notes (optional):</label>
                        <textarea class="form-control" id="change_notes" rows="3" placeholder="Enter any notes..."></textarea>
                    </div>
                    <div class="text-danger mt-2 d-none" id="reason_error">Please select a reason to proceed.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirm_change_btn" class="btn btn-success">Confirm</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        $(function () {
            let selectedCoils = [];

            $('#coils_tbl').off('change', '.row-select').on('change', '.row-select', function () {
                const id = $(this).data('id');
                if (this.checked) selectedCoils.push(id);
                else selectedCoils = selectedCoils.filter(i => i !== id);
            });

            $('#selectAll').off('change').on('change', function () {
                const checked = this.checked;
                $('#coils_tbl .row-select').prop('checked', checked).trigger('change');
            });

            $('#save_selected_coils').off('click').on('click', function () {
                $('#reason_error').addClass('d-none');
                $('#confirmChangeModal').modal('show');
            });

            $('#confirm_change_btn').off('click').on('click', function () {
                const selectedReason = $('input[name="change_reason"]:checked').val();
                const notes = $('#change_notes').val().trim();
                const id = <?= (int) $id ?>;
                const coils = $('#coils_tbl .row-select:checked').map((_, el) => $(el).data('id')).get();

                if (!selectedReason) {
                    $('#reason_error').removeClass('d-none');
                    return;
                }

                $.ajax({
                    url: 'pages/work_order_ajax.php',
                    method: 'POST',
                    data: {
                        id: id,
                        selected_coils: JSON.stringify(coils),
                        assign_coil: 'assign_coil',
                        change_reason: selectedReason,
                        change_notes: notes
                    },
                    success: function (res) {
                        if (res.trim() === 'success') {
                            $('#confirmChangeModal').modal('hide');
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

            if (!$.fn.DataTable.isDataTable('#coils_tbl')) {
                $('#coils_tbl').DataTable({
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


if(isset($_POST['assign_coil'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $wrk_ordr = getWorkOrderDetails($id);

    $change_reason = $_POST['change_reason'] ?? null;
    $change_notes = $_POST['change_notes'] ?? null;

    $userid = $_SESSION['userid'];

    $selected_coils = json_decode($_POST['selected_coils'], true);
    $coils_json = json_encode($selected_coils);

    $sql = "UPDATE work_order SET assigned_coils = '$coils_json' WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo "success";
    } else {
        echo "Error updating records: " . $conn->error;
    }
}

if (isset($_POST['search_work_order'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_search']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);

    
}

if (isset($_POST['run_work_order'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);

    $work_order_details = getSubmitWorkOrderDetails($id);
    $assigned_coils = json_decode($work_order_details['assigned_coils'], true);

    $length_ft = floatval($work_order_details['custom_length'] ?? 0);
    $length_in = floatval($work_order_details['custom_length2'] ?? 0);

    $total_length_ft = $length_ft + ($length_in / 12);

    if (is_array($assigned_coils)) {
        foreach ($assigned_coils as $coil_id) {
            $coil_id = intval($coil_id);
            $update = "UPDATE coil_product 
                       SET remaining_feet = GREATEST(remaining_feet - $total_length_ft, 0) 
                       WHERE coil_id = $coil_id";
            mysqli_query($conn, $update);
        }

        $status_update = "UPDATE work_order SET status = 2 WHERE id = $id";
        mysqli_query($conn, $status_update);

        echo 'success';
    } else {
        echo 'invalid';
    }

    exit;
}

if (isset($_POST['finish_work_order'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);

    $update = "UPDATE work_order SET status = 3 WHERE id = '$id'";
    if (mysqli_query($conn, $update)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($conn);
    }
    exit;
}



