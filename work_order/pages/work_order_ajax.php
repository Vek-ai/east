<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';
require '../../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function addSheet($spreadsheet, $sheetName, $data, $isFirst = false) {
    $sheet = $isFirst ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
    $sheet->setTitle(substr($sheetName, 0, 31));

    foreach ($data as $r => $row) {
        foreach ($row as $c => $value) {
            $colLetter = Coordinate::stringFromColumnIndex($c + 1);
            $cell = $colLetter . ($r + 1);
            $sheet->setCellValue($cell, $value);
        }
    }
}

if(isset($_POST['fetch_available'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $work_order_details = getWorkOrderDetails($id);
    $assigned_coils = $work_order_details['assigned_coils'];
    $decoded_coils = json_decode($assigned_coils, true);
    $profiles = $work_order_details['custom_profile'];
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
            <h5>Assigned Coils</h5>
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

                        $is_reworked = false;
                        $coil_count = 0;
                        $query = "SELECT * FROM coil_product WHERE coil_id IN ($coils_string) AND status = '0' ORDER BY date ASC";
                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            $coil_count = mysqli_num_rows($result);
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
                        }else {
                        $rework_query = "SELECT * FROM coil_product WHERE coil_id IN ($coils_string) AND status = '2' ORDER BY date ASC";
                        $rework_result = mysqli_query($conn, $rework_query);

                        if ($rework_result && mysqli_num_rows($rework_result) > 0) {
                            $is_reworked = true;
                        }
                    }

                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    $rf_query = "
        SELECT roll_former_id, roll_former 
        FROM roll_former 
        WHERE status = 1 AND hidden = 0
    ";

    if (!empty($profiles)) {
        if (!is_array($profiles)) {
            $profiles = [$profiles];
        }

        $conditions = [];
        foreach ($profiles as $p) {
            $p = mysqli_real_escape_string($conn, $p);
            $conditions[] = "JSON_CONTAINS(profile, '\"$p\"')";
        }

        $rf_query .= " AND (" . implode(' OR ', $conditions) . ")";
    }

    $rf_result = mysqli_query($conn, $rf_query);
    $roll_formers = [];
    while ($rf = mysqli_fetch_assoc($rf_result)) {
        $roll_formers[] = $rf;
    }
    ?>

    <div class="mt-3 col-6">
        <label class="form-label fw-bold">Assigned Roll Former</label>

        <input type="hidden" id="indvl_rollformer" name="rollformer_selected_final"
            value="<?= count($roll_formers) === 1 ? $roll_formers[0]['roll_former_id'] : '' ?>">

        <div id="rollformer_text_display" class="<?= count($roll_formers) === 1 ? '' : 'd-none' ?> fw-bold ms-3">
            <?= count($roll_formers) === 1 ? htmlspecialchars($roll_formers[0]['roll_former']) : '' ?>
        </div>
    </div>

    <div class="modal fade" id="runSingleWorkOrderModal" tabindex="-1" aria-labelledby="runWorkOrderModalLabel" aria-hidden="true" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-dialog-centered ?>">
            <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="runWorkOrderModalLabel">Confirm Run</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p>Are you sure you want to run this work order?</p>
                <?php if (count($roll_formers) > 1): ?>
                <div class="mb-2">
                    <label for="indvl_rollformer_select" class="form-label fw-bold">Select Roll Former</label>
                    <select id="indvl_rollformer_select"
                            class="form-select <?= count($roll_formers) > 1 ? '' : 'd-none' ?>">
                        <option value="">-- Select Roll Former --</option>
                        <?php foreach ($roll_formers as $rf): ?>
                            <option value="<?= $rf['roll_former_id'] ?>">
                                <?= htmlspecialchars($rf['roll_former']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="confirmRunSingleBtn" type="button" class="btn btn-success">Run</button>
            </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <?php if (!empty($work_order_details) && $work_order_details['status'] == 1 && $coil_count > 0): ?>
            <button id="openSingleRunWorkOrderModal" class="btn ripple btn-success" data-id="<?= $id ?>" type="button">Run Work Order</button>
            <?php endif; ?>
        <?php if ($is_reworked): ?>
            <button class="btn ripple btn-warning change_assigned_coils" data-id="<?= $id ?>" type="button">Change Coils</button>
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

            $('#indvl_rollformer_select').on('change', function () {
                const selectedVal = $(this).val();
                $('#indvl_rollformer').val(selectedVal);
            });

            $(document).on('click', '#confirmRunSingleBtn', function () {
                const selectedRollFormer = $('#indvl_rollformer').val();

                if (!selectedRollFormer) {
                    alert('Please select a Roll Former.');
                    return;
                }

                const id = <?= json_encode($id) ?>;

                $.ajax({
                    url: 'pages/work_order_ajax.php',
                    method: 'POST',
                    data: {
                        selected_ids: [id],
                        roll_former_id: selectedRollFormer,
                        run_work_order: 'run_work_order'
                    },
                    success: function (res) {
                        try {
                            const response = JSON.parse(res);
                            if (response.status === 'success' && response.url) {
                                alert('Work Order Run Started.');
                                window.open(response.url, '_blank');
                                location.reload();
                            } else {
                                alert('Failed');
                                console.log(res);
                            }
                        } catch (e) {
                            alert('Invalid server response.');
                            console.error(res);
                        }
                    },
                    error: function (xhr) {
                        alert('An error occurred: ' + xhr.statusText);
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('click', '#openSingleRunWorkOrderModal', function () {
                $('#runSingleWorkOrderModal').modal('toggle');
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
                        emptyTable: '<div style="font-size: 1.1rem; font-weight: bold; color: #ff9800;">Waiting for Admin Approval</div>'
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
    $profiles = array();
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
                        wo.work_order_id = '$id' AND wo.status = 1
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

                            $profiles[] = $row['custom_profile'];

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
                                    <?= getProfileTypeName($row['custom_profile']) ?>
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
                                    <div class="action-btn d-flex align-items-center gap-2 text-center">
                                        <a href="javascript:void(0)" class="text-decoration-none" id="viewAssignedBtn" title="View" data-id="<?= $row['id'] ?>">
                                            <i class="fa fa-eye fs-6"></i>
                                        </a>
                                        <a href="javascript:void(0)" class="text-decoration-none" id="viewAvailableBtn" title="Run Work Order" data-id="<?= $row['id'] ?>">
                                            <i class="fa fa-arrow-right-to-bracket fs-6"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                            $no++;
                        }

                        $profiles = array_unique($profiles);
                        $profiles = array_filter($profiles, fn($val) => $val !== 0 && $val !== '0');
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
    <?php
    $rf_query = "
        SELECT roll_former_id, roll_former 
        FROM roll_former 
        WHERE status = 1 AND hidden = 0
    ";

    if (!empty($profiles)) {
        $conditions = [];
        foreach ($profiles as $p) {
            $p = mysqli_real_escape_string($conn, $p);
            $conditions[] = "JSON_CONTAINS(profile, '\"$p\"')";
        }

        $rf_query .= " AND (" . implode(' OR ', $conditions) . ")";
    }

    $rf_result = mysqli_query($conn, $rf_query);
    $roll_formers = [];
    while ($rf = mysqli_fetch_assoc($rf_result)) {
        $roll_formers[] = $rf;
    }
    ?>

    <?php if (count($roll_formers) === 1) { ?>
        <div class="mt-3 col-6">
            <label class="form-label fw-bold">Assigned Roll Former</label>
            <input type="hidden" name="rollformer_select_batch" value="<?= $roll_formers[0]['roll_former_id'] ?>">
            <div class="fw-bold ms-3">
                <?= htmlspecialchars($roll_formers[0]['roll_former']) ?>
            </div>
        </div>
    <?php } ?>

    <div class="modal-footer">
        <button id="openRunWorkOrderModal" class="btn ripple btn-success" type="button">Run Work Order</button>
    </div>

    <div class="modal fade" id="runWorkOrderModal" tabindex="-1" aria-labelledby="runWorkOrderModalLabel" aria-hidden="true" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-dialog-centered ?>">
            <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="runWorkOrderModalLabel">Confirm Run</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p>Are you sure you want to run this work order?</p>
                <?php if (count($roll_formers) > 1): ?>
                <div class="mb-2">
                    <label for="modal_rollformer_select" class="form-label fw-bold">Select Roll Former</label>
                    <select id="modal_rollformer_select" class="form-select">
                    <option value="">-- Select --</option>
                    <?php foreach ($roll_formers as $rf): ?>
                        <option value="<?= $rf['roll_former_id'] ?>"><?= htmlspecialchars($rf['roll_former']) ?></option>
                    <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="confirmRunWorkOrderBtn" type="button" class="btn btn-success">Run</button>
            </div>
            </div>
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

            $(document).on('change', '#selectAll', function () {
                $('.row-check').prop('checked', this.checked);
            });

            $(document).on('click', '#openRunWorkOrderModal', function () {
                const selectedIds = $('.row-check:checked').map(function () {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) {
                    alert('Please select at least one item to run.');
                    return;
                }

                $('#runWorkOrderModal').modal('toggle');
            });

            $(document).on('click', '#confirmRunWorkOrderBtn', function () {
                const selectedIds = $('.row-check:checked').map(function () {
                    return $(this).val();
                }).get();

                const selectedRollFormer = <?= count($roll_formers) === 1 ? json_encode($roll_formers[0]['roll_former_id']) : "$('#modal_rollformer_select').val()" ?>;

                if (!selectedRollFormer) {
                    alert('Please select a Roll Former.');
                    return;
                }

                const id = <?= json_encode($id) ?>;

                $.ajax({
                    url: 'pages/work_order_ajax.php',
                    method: 'POST',
                    data: {
                        id: id,
                        selected_ids: selectedIds,
                        roll_former_id: selectedRollFormer,
                        run_work_order: 'run_work_order'
                    },
                    success: function (res) {
                        try {
                            const response = JSON.parse(res);
                            if (response.status === 'success' && response.url) {
                                alert('Work Order Run Started.');
                                window.open(response.url, '_blank');
                                location.reload();
                            } else {
                                alert('Failed');
                                console.log(res);
                            }
                        } catch (e) {
                            alert('Invalid server response.');
                            console.error(res);
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
                        emptyTable: '<div style="font-size: 1.1rem; font-weight: bold; color: #ff9800;"> Waiting for Admin Approval</div>'
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

                        $is_reworked = false;
                        $query = "SELECT * FROM coil_product WHERE coil_id IN ($coils_string) AND status = '0' ORDER BY date ASC";
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
                                            <iconify-icon class="fs-7 text-warning" icon="mdi:pencil"></iconify-icon>
                                        </a>
                                        <a href="javascript:void(0)" class="text-decoration-none" id="tagDefectiveBtn" title="Tag as Defective" data-id="<?= $id ?>" data-coil-id="<?=$row['coil_id']?>">
                                            <iconify-icon class="fs-7 text-danger" icon="mdi:tools"></iconify-icon>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                $no++;
                            }
                        }else {
                            $rework_query = "SELECT * FROM coil_product WHERE coil_id IN ($coils_string) AND status = '2' ORDER BY date ASC";
                            $rework_result = mysqli_query($conn, $rework_query);

                            if ($rework_result && mysqli_num_rows($rework_result) > 0) {
                                $is_reworked = true;
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-footer">
        <?php if ($is_reworked): ?>
            <button class="btn ripple btn-warning change_assigned_coils" data-id="<?= $id ?>" type="button">Change Coils</button>
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
                        emptyTable: '<div style="font-size: 1.1rem; font-weight: bold; color: #ff9800;">Waiting for Admin Approval</div>'
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
    $is_reworked = isset($_POST['is_reworked']) ? intval($_POST['is_reworked']) : 0;
    $details = getWorkOrderDetails($id);

    $assigned_coils = json_decode($details['assigned_coils'] ?? '[]', true) ?? [];

    $color_id = $details['custom_color'];
    $grade = $details['custom_grade'];
    $width = floatval($details['custom_width']);
    $lengthFeet = floatval($details['custom_length'] ?? 0);
    $lengthInch = floatval($details['custom_length2'] ?? 0);
    $quantity = floatval($details['quantity'] ?? 1);

    $total_length = ($lengthFeet + ($lengthInch / 12)) * $quantity ?: 1;

    $where = "WHERE status = '0'";
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
            <h4>Coils List: <?= $id ?></h4>
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
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-footer d-flex justify-content-end">
        <button id="save_selected_coils" class="btn ripple btn-success me-2" type="button">Change</button>
        <button class="btn ripple btn-danger" type="button" data-bs-dismiss="modal">Close</button>
    </div>

    <div class="modal fade" id="confirmChangeModal" tabindex="-1" aria-labelledby="confirmChangeModalLabel" aria-hidden="true" style="background-color: rgba(0, 0, 0, 0.5);">
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
            var isReworked = <?= $is_reworked ? 'true' : 'false' ?>;
            console.log(isReworked)
            let selectedCoils = [];

            if (isReworked) {
                $('#reason_others').prop('checked', true);
                $('.reason-radio').closest('.form-check').hide();
                $('#change_notes').closest('.mt-3').hide();
                $('#change_notes').val('').prop('disabled', true);
                $('#reason_error').addClass('d-none');
            } else {
                $('.reason-radio').closest('.form-check').show();
                $('#change_notes').closest('.mt-3').show();
                $('#change_notes').prop('disabled', false);
            }

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
                        console.log(res);
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

if (isset($_POST['assign_coil'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $wrk_ordr = getWorkOrderDetails($id);

    $change_reason = mysqli_real_escape_string($conn, $_POST['change_reason'] ?? '');
    $change_notes = mysqli_real_escape_string($conn, $_POST['change_notes'] ?? '');
    $userid = $_SESSION['userid'];

    $selected_coils = json_decode($_POST['selected_coils'], true);
    $coils_json = json_encode($selected_coils);

    $previous_coils = json_decode($wrk_ordr['assigned_coils'] ?? '[]', true);
    if (!is_array($previous_coils)) $previous_coils = [];

    $defective_coils = array_values(array_diff($previous_coils, $selected_coils));

    $update_sql = "UPDATE work_order SET assigned_coils = '$coils_json' WHERE id = $id";
    if ($conn->query($update_sql) === TRUE) {

        if (strtolower($change_reason) === 'defective' && !empty($defective_coils)) {
            foreach ($defective_coils as $coil_id) {
                $coil_id = intval($coil_id);
                $tag_note = $change_notes !== '' ? "'" . mysqli_real_escape_string($conn, $change_notes) . "'" : "NULL";

                $defect_sql = "
                    UPDATE coil_product
                    SET 
                        status = 3,
                        tagged_defective = 1,
                        tagged_date = NOW(),
                        tagged_note = $tag_note
                    WHERE coil_id = $coil_id
                ";
                $conn->query($defect_sql);

                $coil_res = mysqli_query($conn, "SELECT * FROM coil_product WHERE coil_id = $coil_id");
                if ($coil_res && mysqli_num_rows($coil_res) > 0) {
                    $coil_data = mysqli_fetch_assoc($coil_res);

                    $cols = [
                        'coil_id', 'entry_no', 'warehouse', 'color_family', 'color_abbreviation', 'paint_supplier',
                        'paint_code', 'stock_availability', 'multiplier_category', 'actual_color', 'color_close',
                        'coil_no', 'date', 'supplier', 'supplier_name', 'color_sold_as', 'color_sold_name',
                        'product_id', 'og_length', 'weight', 'thickness', 'width', 'grade', 'coating', 'tag_no',
                        'invoice_no', 'remaining_feet', 'last_inventory_count', 'coil_class', 'gauge', 'grade_no',
                        'year', 'month', 'extracting_price', 'price', 'avg_by_color', 'total', 'current_weight',
                        'lb_per_ft', 'contract_ppf', 'contract_ppcwg', 'invoice_price', 'round_width',
                        'status', 'hidden', 'main_image', 'supplier_tag', 'tagged_defective', 'tagged_date', 'tagged_note'
                    ];

                    $columns = implode(", ", $cols);
                    $values = [];

                    foreach ($cols as $col) {
                        if ($col === 'status') {
                            $values[] = 0;
                        } elseif ($col === 'tagged_defective') {
                            $values[] = 1;
                        } elseif ($col === 'tagged_date') {
                            $values[] = "NOW()";
                        } elseif ($col === 'tagged_note') {
                            $values[] = $tag_note;
                        } else {
                            $val = $coil_data[$col] ?? null;
                            $values[] = is_null($val) ? "NULL" : "'" . mysqli_real_escape_string($conn, $val) . "'";
                        }
                    }

                    $values_str = implode(", ", $values);

                    $insert_sql = "
                        INSERT INTO coil_defective ($columns)
                        VALUES ($values_str)
                    ";
                    $conn->query($insert_sql);
                    $inserted_id = mysqli_insert_id($conn);
                    logCoilDefectiveChange($inserted_id, 'add', "Coil tagged as defective");
                }

                $actorId = $_SESSION['work_order_user_id'];
                $actor_name = get_staff_name($actorId);
                $actionType = 'coil_defective';
                $coil_details = getCoilProductDetails($coil_id);
                $targetId = $coil_details['entry_no'];
                $targetType = 'Coil';
                $message = "$actor_name tagged Coil #$targetId as defective.";
                $url = '?page=coils_defective';
                $recipientIds = getAdminIDs();
                createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);
            }
        }

        $orderid = $wrk_ordr['work_order_id'];
        $order_product_id = $wrk_ordr['work_order_product_id'];
        $defective_json = json_encode($defective_coils);

        $log_sql = "
            INSERT INTO work_order_changes (
                orderid, order_product_id, reason, notes, change_date, changed_by, defective_coils
            ) VALUES (
                '$orderid',
                '$order_product_id',
                '$change_reason',
                " . ($change_notes !== '' ? "'$change_notes'" : "NULL") . ",
                NOW(),
                '$userid',
                '$defective_json'
            )
        ";

        if ($conn->query($log_sql) === TRUE) {
            echo "success";
        } else {
            echo "Error logging change: " . $conn->error;
        }

    } else {
        echo "Error updating records: " . $conn->error;
    }
}

if (isset($_POST['tag_coil_defective'])) {
    $id = intval($_POST['id']);
    $wrk_ordr = getWorkOrderDetails($id);

    $userid = $_SESSION['userid'];
    $selected_coil = intval($_POST['coil_id']);
    $change_reason = 'defective';
    $tagged_defective_value = 2;

    $defect_sql = "
        UPDATE coil_product
        SET 
            status = 3,
            tagged_defective = $tagged_defective_value,
            tagged_date = NOW(),
            tagged_note = NULL
        WHERE coil_id = $selected_coil
    ";
    $conn->query($defect_sql);

    $coil_res = mysqli_query($conn, "SELECT * FROM coil_product WHERE coil_id = $selected_coil");
    if ($coil_res && mysqli_num_rows($coil_res) > 0) {
        $coil_data = mysqli_fetch_assoc($coil_res);

        $cols = [
            'coil_id', 'entry_no', 'warehouse', 'color_family', 'color_abbreviation', 'paint_supplier',
            'paint_code', 'stock_availability', 'multiplier_category', 'actual_color', 'color_close',
            'coil_no', 'date', 'supplier', 'supplier_name', 'color_sold_as', 'color_sold_name',
            'product_id', 'og_length', 'weight', 'thickness', 'width', 'grade', 'coating', 'tag_no',
            'invoice_no', 'remaining_feet', 'last_inventory_count', 'coil_class', 'gauge', 'grade_no',
            'year', 'month', 'extracting_price', 'price', 'avg_by_color', 'total', 'current_weight',
            'lb_per_ft', 'contract_ppf', 'contract_ppcwg', 'invoice_price', 'round_width',
            'status', 'hidden', 'main_image', 'supplier_tag', 'tagged_defective', 'tagged_date', 'tagged_note'
        ];

        $columns = implode(", ", $cols);
        $values = [];

        foreach ($cols as $col) {
            if ($col === 'status') {
                $values[] = 0;
            } elseif ($col === 'tagged_defective') {
                $values[] = $tagged_defective_value;
            } elseif ($col === 'tagged_date') {
                $values[] = "NOW()";
            } elseif ($col === 'tagged_note') {
                $values[] = "NULL";
            } else {
                $val = $coil_data[$col] ?? null;
                $values[] = is_null($val) ? "NULL" : "'" . mysqli_real_escape_string($conn, $val) . "'";
            }
        }

        $values_str = implode(", ", $values);

        $insert_sql = "
            INSERT INTO coil_defective ($columns)
            VALUES ($values_str)
        ";
        $conn->query($insert_sql);
        $inserted_id = mysqli_insert_id($conn);
        logCoilDefectiveChange($inserted_id, 'add', "Coil tagged as defective");
    }

    $actorId = $_SESSION['work_order_user_id'];
    $actor_name = get_staff_name($actorId);
    $actionType = 'coil_defective';
    $coil_details = getCoilProductDetails($selected_coil);
    $targetId = $coil_details['entry_no'];
    $targetType = 'Coil';
    $message = "$actor_name tagged Coil #$targetId as defective.";
    $url = '?page=coils_defective';
    $recipientIds = getAdminIDs();
    createNotification($actorId, $actionType, $targetId, $targetType, $message, 'admin', $url);

    if ($notificationId === false) {
        die("Error: Failed to create notification.");
    }

    $orderid = $wrk_ordr['work_order_id'];
    $order_product_id = $wrk_ordr['work_order_product_id'];
    $defective_json = json_encode([$selected_coil]);

    $log_sql = "
        INSERT INTO work_order_changes (
            orderid, order_product_id, reason, notes, change_date, changed_by, defective_coils
        ) VALUES (
            '$orderid',
            '$order_product_id',
            '$change_reason',
            NULL,
            NOW(),
            '$userid',
            '$defective_json'
        )
    ";

    if ($conn->query($log_sql) === TRUE) {
        echo "success";
    } else {
        echo "Error logging change: " . $conn->error;
    }
}

if (isset($_POST['run_work_order'])) {
    $ids = $_POST['selected_ids'] ?? [];
    $roll_former_id = intval($_POST['roll_former_id'] ?? 0);

    if (!is_array($ids) || empty($ids)) {
        echo 'no_selection';
        exit;
    }

    foreach ($ids as $id) {
        $id = mysqli_real_escape_string($conn, $id);

        $work_order_details = getWorkOrderDetails($id);
        if (!$work_order_details) {
            continue;
        }

        $materialRows = [
            ['#L', 'MATERIAL', 'GAUGE', 'GRADE', 'THICKNESS', 'WIDTH', 'COLOR', 'DENSITY', 'DESCRIPTION'],
        ];

        $productid = $work_order_details['productid'];
        $product_name = getProductName($productid);
        $product_details = getProductDetails($productid);
        $color_id = $work_order_details['custom_color'];
        $materialRows[] = ['L', $product_name, $product_details['gauge'], $work_order_details['custom_grade'], $product_details['thickness'], $work_order_details['custom_width'], getColorName($color_id), '0', ''];

        $profileRows = [
            ['#F', 'PROFILE', 'DESCRIPTION'],
            ['#H', 'MACHINE']
        ];

        $profile_type_id = $work_order_details['custom_profile'];
        $profile_details = getProfileTypeDetails($profile_type_id);
        $profile = $profile_details['profile_type'];
        $profileRows[] = ['F', $profile, $profile_details['notes'], '', '', '', '', ''];

        $rollformer_details = getRollFormerDetails($roll_former_id);
        $rollformer_name = $rollformer_details['roll_former'];
        $profileRows[] = ['H', $rollformer_details['roll_former']];
        
        
        $assigned_coils = json_decode($work_order_details['assigned_coils'], true);
        $length_ft = floatval($work_order_details['custom_length'] ?? 0);
        $length_in = floatval($work_order_details['custom_length2'] ?? 0);

        $total_length_ft = $length_ft + ($length_in / 12);

        if (is_array($assigned_coils)) {
            $coilRows = [
                ['#C', 'COIL', 'LOCATION', 'MATERIAL', 'RECEIVED', 'STATUS', 'VENDOR', 'WEIGHT', 'COST', 'LENGTH', 'GRADE', 'NOTES']
            ];
            foreach ($assigned_coils as $coil_id) {
                $coil_id = intval($coil_id);

                $update = "UPDATE coil_product 
                           SET remaining_feet = GREATEST(remaining_feet - $total_length_ft, 0) 
                           WHERE coil_id = $coil_id";
                mysqli_query($conn, $update);

                $coil_details = getCoilProductDetails($coil_id);
                $entry_no = $coil_details['entry_no'];
                $warehouse_id = $coil_details['warehouse'];
                $warehouse_name = getWarehouseName($warehouse_id);
                $material = '';
                $received_date = date('m/d/Y', strtotime($coil_details['date']));
                $status_id = $coil_details['status'];
                $status_label = '';
                switch ($coil_details['status']) {
                    case 0:
                        $status_label = 'AVAILABLE';
                        break;
                    case 1:
                        $status_label = 'USED';
                        break;
                    case 2:
                        $status_label = 'REWORK';
                        break;
                    case 3:
                        $status_label = 'DEFECTIVE';
                        break;
                    case 4:
                        $status_label = 'ARCHIVED';
                        break;
                    default:
                        $status_label = 'UNKNOWN';
                        break;
                }
                $supplier_id = $coil_details['supplier'];
                $supplier_name = getSupplierName($supplier_id);
                $weight = floatval($coil_details['weight']);
                $cost = floatval($coil_details['price']);
                $length = floatval($coil_details['remaining_feet']);
                $grade_id = $coil_details['grade'];
                $grade_name = getGradeName($grade_id);

                $coilRows[] = ['C', $entry_no, $warehouse_name, $material, $received_date, $status_label, $supplier_name, $weight, $cost, $length, $grade_name, 'NotesHere'];
            }

            $status_update = "UPDATE work_order SET status = 2, roll_former_id = '$roll_former_id' WHERE id = $id";
            mysqli_query($conn, $status_update);
        }
    }

    $partOpRows = [
        ['#P', 'PART', 'DESCRIPTION'],
        ['#O', 'OPERATION', 'POSITION', 'REFERENCE', 'YPOS'],
        ['P', 'TEST PART', 'TEST PART'],
        ['O', 'TEST OPERATION', '1', 'LEADING EDGE', '2'],
        ['O', 'TEST OPERATION 2', '2', 'LEADING EDGE', '-2'],
        ['O', 'TEST OPERATION 3', '4', 'LEADING EDGE', '2'],
        ['O', 'TEST OPERATION 4', '8', 'LEADING EDGE', '-2'],
    ];

    $jobBatchRows = [
        ['#J', 'JOB', 'MACHINE', 'PROFILE', 'MATERIAL', 'USER 1', 'USER 2', 'USER 3', 'USER 4', 'USER 5'],
        ['#B', 'BATCH', 'QUANTITY', 'LENGTH', 'PART', 'USER 1', 'USER 2', 'USER 3', 'USER 4', 'USER 5']
    ];

    $orderid = $work_order_details['work_order_id'];
    $order_details = getOrderDetails($orderid);
    $job_name = $order_details['job_name'];
    $jobBatchRows[] =
        ['J', $job_name, $rollformer_name, $profile, $product_name, 'UDF1', 'UDF2', 'UDF3', 'UDF4', 'UDF5']
    ;

    $jobBatchRows[] =
        ['B', '1', '10', '120', 'SHEAR ONLY', 'UDF1', 'UDF2', 'UDF3', 'UDF4', 'UDF5']
    ;

    $folderRows = [
        ['#FOLDER_PART', 'NAME', 'DESCRIPTION', 'CLAMP_PRESSURE', 'OVERBEND', 'MATERIAL_THICKNESS', 'PAINT_DIRECTION'],
        ['#FOLDER_OPERATION', 'STEP', 'BACKGAUGE2', 'BACKGAUGE', 'CLAMP_PRESSURE', 'BEND_ANGLE', 'OVERBEND', 'UPPER_JAW', 'BUMP_BEND_ANGLE', 'BUMP_BEND_RADIUS', 'BUMP_BEND_ITERATIONS', 'ROTARY_SHEAR', 'FLIP', 'HELI_ROTATE', 'PROP ROTATE', 'BG ADJUST'],
        ['FOLDER_PART', 'CANOPY-PANEL', 'TEST PART', '1500', '8', '0.03998', 'UP'],
        ['FOLDER_OPERATION', '0', '18.99994', '18.99994', '0', '0', '0', '0.7', '0', '0', '0', '200', 'NO', 'NO', 'NO', '0'],
        ['FOLDER_OPERATION', '1', '18.24994', '18.24994', '0', '90', '0', '0.7', '0', '0', '0', '0', 'NO', 'NO', 'NO', '0'],
        ['FOLDER_OPERATION', '2', '17.19994', '17.19994', '0', '90', '0', '0.7', '0', '0', '0', '0', 'NO', 'NO', 'NO', '0'],
        ['FOLDER_OPERATION', '3', '14.36995', '14.36995', '0', '95', '0', '0.7', '0', '0', '0', '0', 'NO', 'NO', 'YES', '0'],
        ['FOLDER_OPERATION', '4', '1.54999', '1.54999', '0', '90', '0', '2', '0', '0', '0', '0', 'NO', 'NO', 'NO', '0'],
        ['FOLDER_OPERATION', '5', '0.59998', '0.59998', '0', '85', '0', '2', '0', '0', '0', '0', 'NO', 'NO', 'NO', '0'],
        ['FOLDER_OPERATION', '6', '2.81998', '2.81998', '0', '90', '0', '2', '0', '0', '0', '0', 'NO', 'NO', 'NO', '0'],
    ];

    $spreadsheet = new Spreadsheet();

    addSheet($spreadsheet, 'Material', $materialRows, true);
    addSheet($spreadsheet, 'Profile', $profileRows);
    addSheet($spreadsheet, 'Coil', $coilRows);
    addSheet($spreadsheet, 'Part_Operation', $partOpRows);
    addSheet($spreadsheet, 'Job_Batch', $jobBatchRows);
    addSheet($spreadsheet, 'FolderOps', $folderRows);

    $timestamp = time();
    $filename = "Wrok Order_SO_$orderid.xlsx";
    $filepath = __DIR__ . "/temp_exports/$filename";

    if (!file_exists(__DIR__ . "/temp_exports")) {
        mkdir(__DIR__ . "/temp_exports", 0777, true);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($filepath);

    $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    $downloadUrl = $baseUrl . "/temp_exports/$filename";

    echo json_encode([
        'status' => 'success',
        'url' => $downloadUrl
    ]);

    exit;
}



