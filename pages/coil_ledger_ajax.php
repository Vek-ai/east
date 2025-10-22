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
        'coils' => [],
        'total_count' => 0,
        'total_amount' => 0,
        'error' => null
    ];

    $coilid = mysqli_real_escape_string($conn, $_POST['coilid'] ?? '');
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id'] ?? '');
    $months = array_map('intval', $_POST['months'] ?? []);
    $supplier_id = mysqli_real_escape_string($conn, $_POST['supplier_id'] ?? '');

    $months_in = !empty($months) ? implode(',', $months) : '';

    $query = "
        SELECT 
            ct.id,
            ct.coilid,
            ct.date,
            ct.remaining_length,
            ct.length_before_use,
            ct.used_in_workorders,
            cp.supplier AS supplier_id,
            cp.entry_no,
            cp.date_inventory AS coil_date,
            GROUP_CONCAT(DISTINCT o.customerid) AS customerids,
            GROUP_CONCAT(DISTINCT o.orderid) AS orderids
        FROM coil_transaction ct
        LEFT JOIN coil_product cp 
            ON ct.coilid = cp.coil_id
        LEFT JOIN work_order wo 
            ON FIND_IN_SET(wo.id, ct.used_in_workorders)
        LEFT JOIN orders o 
            ON wo.work_order_id = o.orderid
        WHERE 1 = 1
    ";

    if (!empty($customer_id)) {
        $query .= " AND o.customerid = '$customer_id' ";
    }

    if (!empty($months_in)) {
        $query .= " AND MONTH(o.order_date) IN ($months_in) ";
    }

    if (!empty($supplier_id)) {
        $query .= " AND cp.supplier = '$supplier_id' ";
    }

    if (!empty($coilid)) {
        $query .= " AND ct.coilid = '$coilid' ";
    }

    $query .= "
        GROUP BY 
            ct.id, ct.coilid, ct.date, ct.remaining_length, 
            ct.length_before_use, cp.supplier, cp.entry_no, cp.date_inventory
        ORDER BY ct.date DESC
    ";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        $response['error'] = 'SQL Error: ' . mysqli_error($conn);
        echo json_encode($response);
        exit;
    }

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $remaining_feet = floatval($row['remaining_length'] ?? 0);
            $length_before_use = floatval($row['length_before_use'] ?? 0);
            $used_feet = max(0, $length_before_use - $remaining_feet);

            $coil_date = !empty($row['coil_date']) ? date('m/d/Y', strtotime($row['coil_date'])) : '-';
            $trans_date = !empty($row['date']) ? date('m/d/Y', strtotime($row['date'])) : '-';

            $order_list = !empty($row['orderids']) ? explode(',', $row['orderids']) : [];
            $customer_list = !empty($row['customerids']) ? explode(',', $row['customerids']) : [];

            $order_display = !empty($order_list)
                ? implode(', ', array_map(fn($id) => 'INV#' . trim($id), $order_list))
                : '-';

            if (!empty($customer_list)) {
                $customer_names = [];
                foreach ($customer_list as $cid) {
                    $name = get_customer_name(trim($cid));
                    if ($name) {
                        $customer_names[] = $name;
                    }
                }
                $customer_name = implode(', ', $customer_names);
            } else {
                $customer_name = '-';
            }

            $response['coils'][] = [
                'id' => $row['id'],
                'coilid' => $row['coilid'] ?? '-',
                'entry_no' => $row['entry_no'] ?? '-',
                'supplier_id' => $row['supplier_id'] ?? '-',
                'orderid' => $first_orderid,
                'customerid' => $first_customerid,
                'customer' => $customer_name,
                'used_in_workorders' => $row['used_in_workorders'] ?? '',
                'length_before_use' => $length_before_use,
                'remaining_length' => number_format($remaining_feet,2),
                'used_feet' => number_format($used_feet,2),
                'coil_date' => $coil_date,
                'transaction_date' => $trans_date
            ];

            $response['total_count']++;
            $response['total_amount'] += $used_feet;
        }
    } else {
        $response['error'] = 'No coil usage found.';
    }

    echo json_encode($response);
}

if (isset($_POST['fetch_usage_details'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);

    $sql = "
        SELECT
            wo.id AS wo_id,
            wo.work_order_id AS invoice_no,
            wo.quantity AS wo_quantity,
            wo.custom_length AS wo_length_ft,
            wo.custom_length2 AS wo_length_in,
            op.id AS op_id,
            op.productid AS product_id,
            op.product_item AS product_item,
            op.product_id_abbrev AS product_id_abbrev,
            op.custom_color AS op_custom_color,
            op.custom_grade AS op_custom_grade,
            op.custom_gauge AS op_custom_gauge,
            op.custom_profile AS op_custom_profile
        FROM work_order wo
        LEFT JOIN order_product op ON op.id = wo.work_order_product_id
        WHERE FIND_IN_SET(wo.id, '$id')
        ORDER BY op.productid, wo.id
    ";

    $res = mysqli_query($conn, $sql);
    if (!$res) {
        echo '<div class="alert alert-danger">SQL error: ' . mysqli_error($conn) . '</div>';
        exit;
    }

    $grouped = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $pid = $r['product_id'] ?: 'unknown_product';
        $grouped[$pid][] = $r;
    }

    $line_counter = 0;
    $grand_total = 0;
    ?>

    <div class="card card-body">
        <h5 class="fw-bold mb-3">View Coil Use History</h5>

        <?php if (empty($grouped)): ?>
            <div class="text-center text-muted">No coil usage found.</div>
        <?php else: ?>

            <?php foreach ($grouped as $product_id => $rows): ?>
                <?php
                $first = $rows[0];
                $product_item = $first['product_item'] ?? 'Unknown';
                $product_abbrev = $first['product_id_abbrev'] ?? ('-');

                $product_subtotal = 0;
                ?>
                <div class="mb-3">
                    <h6 class="fw-bold text-primary mb-2">
                        Product: <?= htmlspecialchars($product_item) ?> (<?= htmlspecialchars($product_abbrev) ?>)
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0 text-center">
                            <thead class="text-center">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Line Item ID #</th>
                                    <th>Product ID #</th>
                                    <th>Description</th>
                                    <th>Color</th>
                                    <th>Grade</th>
                                    <th>Gauge</th>
                                    <th>Profile</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Total Length Ft</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row): 
                                    $line_counter++;
                                    $line_id = 'L' . $line_counter;

                                    $invoice_no = "INV#" . $row['invoice_no'];

                                    $qty = intval($row['wo_quantity']);
                                    $length_ft = floatval($row['wo_length_ft']);
                                    $line_total = $qty * $length_ft;

                                    $product_id_display = $row['product_id_abbrev'] ?? $product_abbrev;
                                    $description = $row['product_item'] ?? '-';

                                    $color = function_exists('getColorName') ? getColorName($row['op_custom_color']) : ($row['op_custom_color'] ?? '-');
                                    $grade = function_exists('getGradeName') ? getGradeName($row['op_custom_grade']) : ($row['op_custom_grade'] ?? '-');
                                    $gauge = function_exists('getGaugeName') ? getGaugeName($row['op_custom_gauge']) : ($row['op_custom_gauge'] ?? '-');
                                    $profile = function_exists('getProfileTypeName') ? getProfileTypeName($row['op_custom_profile']) : ($row['op_custom_profile'] ?? '-');

                                    $product_subtotal += $line_total;
                                    $grand_total += $line_total;
                                ?>
                                    <tr>
                                        <td>
                                            <a href="javascript:void(0)" class="view_invoice_details" style="color: #4da3ff !important;" data-orderid="<?= $row['invoice_no'] ?>">
                                                <?= htmlspecialchars($invoice_no) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($line_id) ?></td>
                                        <td><?= htmlspecialchars($product_id_display) ?></td>
                                        <td><?= htmlspecialchars($description) ?></td>
                                        <td><?= htmlspecialchars($color) ?></td>
                                        <td><?= htmlspecialchars($grade) ?></td>
                                        <td><?= htmlspecialchars($gauge) ?></td>
                                        <td><?= htmlspecialchars($profile) ?></td>
                                        <td><?= number_format($qty) ?></td>
                                        <td><?= number_format($line_total, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <button class="btn btn-success btn-sm" id="btnPrint"><i class="fa fa-print me-1"></i> Print</button>
            <button class="btn btn-primary btn-sm" id="btnDownload"><i class="fa fa-download me-1"></i> Download</button>
            <button class="btn btn-danger btn-sm" id="btnClose"><i class="fa fa-times me-1"></i> Close</button>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#btnClose').on('click', function() {
                $('#view_coil_usage_modal').modal('hide');
            });
        });
    </script>

    <?php
    // end POST handler
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
                        <small class="text-muted d-block">Coil #</small>
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

