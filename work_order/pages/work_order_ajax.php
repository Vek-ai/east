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

if(isset($_POST['fetch_details'])){
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



