<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

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
    <div class="modal-footer">
        <?php if (!empty($work_order_details) && $work_order_details['status'] == 1): ?>
            <button id="run_work_order" class="btn ripple btn-success" type="button">Run Work Order</button>
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
                    url: 'pages/work_order_new_ajax.php',
                    method: 'POST',
                    data: {
                        selected_ids: [id],
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

if(isset($_POST['fetch_assigned'])){
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
    $ids = json_decode($_POST['id'], true) ?? [];

    if (!is_array($ids)) $ids = [$ids];
    if (empty($ids)) {
        echo "No valid work order IDs provided.";
        exit;
    }

    $all_colors = [];
    $total_length_needed = 0;

    foreach ($ids as $id) {
        $id = mysqli_real_escape_string($conn, $id);
        $details = getWorkOrderDetails($id);

        $color = $details['custom_color'];
        if (!empty($color)) {
            $all_colors[] = intval($color);
        }

        $lengthFeet = floatval($details['custom_length'] ?? 0);
        $lengthInch = floatval($details['custom_length2'] ?? 0);
        $quantity = floatval($details['quantity'] ?? 1);
        $total_length_needed += ($lengthFeet + ($lengthInch / 12)) * $quantity;
    }

    if($total_length_needed == 0){
        $total_length_needed = 1;
    }

    $color_list = implode(",", array_map('intval', $all_colors));
    $where = "WHERE 1=1";

    if (!empty($color_list)) {
        $where .= " AND color_sold_as IN ($color_list)";
    }

    $query = "SELECT * FROM coil_product $where ORDER BY date ASC";
    $result = mysqli_query($conn, $query);

    $total_selected_length = 0;
    ?>
    <div class="card card-body datatables">
        <div class="product-details table-responsive text-wrap">
            <h4>Coils List</h4>
            <table id="coils_tbl" class="table table-hover mb-0 text-md-nowrap text-center">
                <thead>
                    <tr>
                        <th></th>
                        <th><input type="checkbox" id="selectAllCoils"></th>
                        <th>Coil No</th>
                        <th class="text-center">Date</th>
                        <th class="text-left">Color</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Thickness</th>
                        <th class="text-right">Width</th>
                        <th class="text-right">Rem. Feet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)):
                        $coil_id = $row['coil_id'];
                        $color_details = getColorDetails($row['color_sold_as']);
                        $remaining = floatval($row['remaining_feet']);

                        $select = false;
                        if ($total_selected_length < $total_length_needed) {
                            $select = true;
                            $total_selected_length += $remaining;
                        }
                    ?>
                    <tr data-id="<?= $coil_id ?>" data-length="<?= $remaining ?>">
                        <td><?= $select ? 1 : 0 ?></td>
                        <td class="text-start">
                            <input type="checkbox" class="row-select" data-id="<?= $coil_id ?>" <?= $select ? 'checked' : '' ?>>
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
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    $rf_query = "SELECT roll_former_id, roll_former FROM roll_former WHERE status = 1 AND (hidden IS NULL OR hidden = 0)";
    $rf_result = mysqli_query($conn, $rf_query);
    ?>
    <div class="mt-3 col-6">
        <label for="rollformer_select" class="form-label fw-bold">Select Roll Former</label>
        <select id="rollformer_select" class="form-select">
            <option value="">-- Select Roll Former --</option>
            <?php while ($rf = mysqli_fetch_assoc($rf_result)): ?>
                <option value="<?= $rf['roll_former_id'] ?>"><?= htmlspecialchars($rf['roll_former']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="modal-footer d-flex justify-content-end">
        <button id="save_selected_coils" class="btn ripple btn-success me-2" type="button">Run</button>
    </div>

    <script>
        $(function () {
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
    <?php
}

if (isset($_POST['run_work_order'])) {
    $ids = $_POST['selected_ids'] ?? [];
    $roll_former_id = intval($_POST['roll_former_id'] ?? 0);
    $selected_coils = json_decode($_POST['selected_coils'] ?? '[]', true);

    if (!is_array($ids) || empty($ids) || !is_array($selected_coils) || empty($selected_coils)) {
        echo 'no_selection';
        exit;
    }

    $coils_json = json_encode($selected_coils);

    foreach ($ids as $id) {
        $id = mysqli_real_escape_string($conn, $id);

        // Save assigned coils first
        $update_sql = "UPDATE work_order SET assigned_coils = '$coils_json', roll_former_id = '$roll_former_id' WHERE id = $id";
        $conn->query($update_sql); // Optional: handle error if needed

        $work_order_details = getWorkOrderDetails($id);
        if (!$work_order_details) {
            continue;
        }

        $length_ft = floatval($work_order_details['custom_length'] ?? 0);
        $length_in = floatval($work_order_details['custom_length2'] ?? 0);
        $total_length_ft = $length_ft + ($length_in / 12);

        foreach ($selected_coils as $coil_id) {
            $coil_id = intval($coil_id);

            $update = "UPDATE coil_product 
                       SET remaining_feet = GREATEST(remaining_feet - $total_length_ft, 0) 
                       WHERE coil_id = $coil_id";
            mysqli_query($conn, $update);
        }

        $status_update = "UPDATE work_order SET status = 2 WHERE id = $id";
        mysqli_query($conn, $status_update);
    }

    echo 'success';
    exit;
}




