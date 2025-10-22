<?php
session_start();
$permission = $_SESSION['permission'];
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

if (isset($_POST['fetch_coils'])) {
    $ids = json_decode($_POST['id'], true) ?? [];

    if (!is_array($ids)) $ids = [$ids];
    if (empty($ids)) {
        echo "No valid work order IDs provided.";
        exit;
    }

    $profiles = array();
    $all_colors = [];
    $total_length_needed = 0;

    foreach ($ids as $id) {
        $id = mysqli_real_escape_string($conn, $id);
        $details = getWorkOrderDetails($id);

        $product_id = $details['productid'];
        $product_details = getProductDetails($product_id);

        $color = $details['custom_color'];
        if (!empty($color)) {
            $all_colors[] = intval($color);
        }

        $lengthFeet = floatval($details['custom_length'] ?? 0);
        $lengthInch = floatval($details['custom_length2'] ?? 0);
        $quantity = floatval($details['quantity'] ?? 1);
        $total_length_needed += ($lengthFeet + ($lengthInch / 12)) * $quantity;

        $profiles[] = $details['custom_profile'];
    }

    $profiles = array_unique($profiles);
    $profiles = array_filter($profiles, fn($val) => $val !== 0 && $val !== '0');

    if($total_length_needed == 0){
        $total_length_needed = 1;
    }

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
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $coils = getAvailableCoils($all_colors);
                    foreach ($coils as $row) {
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
                        <td class="text-center">
                            <?php                                                    
                            if ($permission === 'edit') {
                            ?>
                            <a href="javascript:void(0)" 
                                class="text-decoration-none" 
                                id="tagDefectiveBtn" 
                                title="Tag as Defective" 
                                data-id='<?= htmlspecialchars(json_encode($ids), ENT_QUOTES, 'UTF-8') ?>'
                                data-coil-id="<?=$row['coil_id']?>">
                                <iconify-icon class="fs-7 text-danger" icon="mdi:tools"></iconify-icon>
                            </a>
                            <?php                                                    
                            }
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
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

    <?php                                                    
    if ($permission === 'edit') {
    ?>
    <div class="modal-footer">
        <button id="openSingleRunWorkOrderModal" class="btn ripple btn-success" data-id="<?= $id ?>" type="button">Run Work Order</button>
    </div>
    <?php                                                    
    }
    ?>

    <script>
        $(function () {
            $('#indvl_rollformer_select').on('change', function () {
                const selectedVal = $(this).val();
                $('#indvl_rollformer').val(selectedVal);
            });

            $(document).on('click', '#confirmRunSingleBtn', function () {
                const selectedRollFormer = $('#indvl_rollformer').val();
                const coils = $('#coils_tbl .row-select:checked').map((_, el) => $(el).data('id')).get();

                if (!selectedRollFormer) {
                    alert('Please select a Roll Former.');
                    return;
                }

                const id = <?= json_encode($ids) ?>;

                $.ajax({
                    url: 'pages/work_order_new_ajax.php',
                    method: 'POST',
                    data: {
                        selected_ids: id,
                        selected_coils: JSON.stringify(coils),
                        roll_former_id: selectedRollFormer,
                        run_work_order: 'run_work_order'
                    },
                    success: function (res) {
                        console.log(res);
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

if (isset($_POST['tag_coil_defective'])) {
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
    createNotification($actorId, $actionType, $targetId, $targetType, $message, $recipientIds, $url);

    if ($notificationId === false) {
        die("Error: Failed to create notification.");
    }

    $ids = json_decode($_POST['id'], true);
    $change_reason = 'defective';
    $userid = $_SESSION['userid'];
    $defective_json = json_encode([$selected_coil]);

    if (!is_array($ids) || count($ids) === 0) {
        die("Invalid work order IDs");
    }

    foreach ($ids as $id) {
        $id = intval($id);
        $wrk_ordr = getWorkOrderDetails($id);

        if (!$wrk_ordr) continue;

        $orderid = $wrk_ordr['work_order_id'];
        $order_product_id = $wrk_ordr['work_order_product_id'];

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

        if (!$conn->query($log_sql)) {
            echo "Error logging change for ID $id: " . $conn->error;
            exit;
        }
    }
}

if (isset($_POST['run_work_order'])) {
    $ids = $_POST['selected_ids'] ?? [];
    $roll_former_id = intval($_POST['roll_former_id'] ?? 0);
    $selected_coils = json_decode($_POST['selected_coils'] ?? '[]', true);

    if (empty($ids) || empty($selected_coils)) {
        echo 'no_selection';
        exit;
    }

    $coils_json = json_encode($selected_coils);

    $materialData = [];
    $coilData = [];
    $jobBatchData = [];
    $barcodes = [];
    $coil_usage = [];

    $no = 1;

    foreach ($ids as $id) {
        $id = intval($id);

        $update_sql = "
            UPDATE work_order 
            SET assigned_coils = '$coils_json', roll_former_id = '$roll_former_id'
            WHERE id = $id
        ";
        $conn->query($update_sql);

        $work_order_details = getWorkOrderDetails($id);
        if (!$work_order_details) continue;

        $productid = $work_order_details['productid'];
        $product_name = getProductName($productid);
        $product_details = getProductDetails($productid);
        $color_id = $work_order_details['custom_color'];

        $materialData[] = [
            'L', $product_name, $product_details['gauge'], $work_order_details['custom_grade'],
            $product_details['thickness'], $work_order_details['width'], getColorName($color_id), '0', ''
        ];

        $profile_type_id = $work_order_details['custom_profile'];
        $profile_details = getProfileTypeDetails($profile_type_id);
        $profile = $profile_details['profile_type'];

        $rollformer_details = getRollFormerDetails($roll_former_id);
        $rollformer_name = $rollformer_details['roll_former'];

        $length_ft = floatval($work_order_details['custom_length'] ?? 0);
        $length_in = floatval($work_order_details['custom_length2'] ?? 0);
        $total_length_ft = $length_ft + ($length_in / 12);

        foreach ($selected_coils as $coil_id) {
            $coil_id = intval($coil_id);
            if (!isset($coil_usage[$coil_id])) {
                $coil_usage[$coil_id] = [
                    'total_used' => 0,
                    'work_orders' => []
                ];
            }
            $coil_usage[$coil_id]['total_used'] += $total_length_ft;
            $coil_usage[$coil_id]['work_orders'][] = $id;
        }

        $generated_upc = getOrderProductBarcode($work_order_details['work_order_product_id']);
        mysqli_query($conn, "UPDATE work_order SET status = 2, upc = '$generated_upc' WHERE id = $id");

        $orderid = $work_order_details['work_order_id'];
        $order_details = getOrderDetails($orderid);
        $job_name = $order_details['job_name'];
        $part_no = $product_details['coil_part_no'];
        $quantity = $work_order_details['quantity'];
        $custom_length = $work_order_details['custom_length'];
        $custom_length2 = $work_order_details['custom_length2'];
        $decimal_length = floatval($custom_length) + (floatval($custom_length2) / 12);
        $barcode = $generated_upc;
        $barcodes[] = $barcode;
        $usageid = $work_order_details['usageid'];
        $usage_name = getUsageName($usageid);

        $jobBatchData[] = [
            'B', $no, $quantity, $decimal_length, $part_no, $barcode, $usage_name, 'UDF3', 'UDF4', 'UDF5'
        ];
        $no++;
    }

    foreach ($coil_usage as $coil_id => $data) {
        $coil_before = getCoilProductDetails($coil_id);
        $length_before_use = floatval($coil_before['remaining_feet']);
        $deduct_used = floatval($data['total_used']);

        mysqli_query($conn, "
            UPDATE coil_product 
            SET remaining_feet = GREATEST(remaining_feet - $deduct_used, 0)
            WHERE coil_id = $coil_id
        ");

        $coil_after = getCoilProductDetails($coil_id);
        $remaining_length = floatval($coil_after['remaining_feet']);
        $work_order_ids = implode(',', array_unique($data['work_orders']));

        $check = mysqli_query($conn, "
            SELECT id, used_in_workorders
            FROM coil_transaction
            WHERE coilid = $coil_id
            ORDER BY id DESC
            LIMIT 1
        ");

        if ($check && mysqli_num_rows($check) > 0) {
            $row = mysqli_fetch_assoc($check);
            $existing_ids = array_filter(explode(',', $row['used_in_workorders']));
            $merged_ids = array_unique(array_merge($existing_ids, $data['work_orders']));
            $merged_ids_str = implode(',', $merged_ids);

            mysqli_query($conn, "
                UPDATE coil_transaction
                SET remaining_length = '$remaining_length',
                    length_before_use = '$length_before_use',
                    used_in_workorders = '$merged_ids_str',
                    date = NOW()
                WHERE id = {$row['id']}
            ");
        } else {
            mysqli_query($conn, "
                INSERT INTO coil_transaction (coilid, date, remaining_length, length_before_use, used_in_workorders)
                VALUES ($coil_id, NOW(), '$remaining_length', '$length_before_use', '$work_order_ids')
            ");
        }

        $coilData[] = [
            'C', $coil_after['entry_no'], getWarehouseName($coil_after['warehouse']), '',
            date('m/d/Y', strtotime($coil_after['date'] ?? date('Y-m-d'))),
            match ($coil_after['status']) {
                0 => 'AVAILABLE',
                1 => 'USED',
                2 => 'REWORK',
                3 => 'DEFECTIVE',
                4 => 'ARCHIVED',
                default => 'UNKNOWN'
            },
            getSupplierName($coil_after['supplier']),
            floatval($coil_after['weight']),
            floatval($coil_after['price']),
            $remaining_length,
            getGradeName($coil_after['grade']),
            $coil_after['notes'] ?? ''
        ];
    }

    $materialRows = [['#L','MATERIAL','GAUGE','GRADE','THICKNESS','WIDTH','COLOR','DENSITY','DESCRIPTION'], ...$materialData];
    $profileRows  = [['#F','PROFILE','DESCRIPTION'],['#H','MACHINE'],['F',$profile,$profile_details['notes']],['H',$rollformer_name]];
    $coilRows     = [['#C','COIL','LOCATION','MATERIAL','RECEIVED','STATUS','VENDOR','WEIGHT','COST','LENGTH','GRADE','NOTES'], ...$coilData];
    $jobBatchRows = [
        ['#J','JOB','MACHINE','PROFILE','MATERIAL','USER 1','USER 2','USER 3','USER 4','USER 5'],
        ['#B','BATCH','QUANTITY','LENGTH','PART','USER 1','USER 2','USER 3','USER 4','USER 5'],
        ['J',$job_name,$rollformer_name,$profile,$product_name,'UDF1','UDF2','UDF3','UDF4','UDF5'],
        ...$jobBatchData
    ];

    $csvData = array_merge($materialRows, [[]], $profileRows, [[]], $coilRows, [[]], $jobBatchRows);

    $folderPath = __DIR__ . "/temp_exports";
    if (!file_exists($folderPath)) mkdir($folderPath, 0777, true);
    $filename = "Work_Order_SO_{$orderid}.csv";
    $filepath = "$folderPath/$filename";

    $fp = fopen($filepath, 'w');
    foreach ($csvData as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);

    $baseUrl = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
    $downloadUrl = $baseUrl . "/temp_exports/$filename";
    $barcodeList = implode(',', array_filter($barcodes));

    echo json_encode([
        'status' => 'success',
        'url' => $downloadUrl,
        'barcodes' => $barcodeList
    ]);
    exit;
}







