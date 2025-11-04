<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['fetch_available'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $details = getApprovalProductDetails($id);

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
        .tooltip-inner { 
            font-size: calc(0.875rem + 2px) !important; 
        }
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

                        $weighted_sum += floatval($row['price']) * floatval($row['remaining_feet']);
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
                            <td class="text-right"><?= number_format(floatval($row['price']), 2) ?></td>
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

if(isset($_POST['chng_price'])){
    $id = mysqli_real_escape_string($conn, $_POST['approval_product_id']);
    $inpt_price = mysqli_real_escape_string($conn, $_POST['inpt_price']);

    if (!empty($inpt_price) && !empty($id)) {
        $sql = "UPDATE approval_product SET discounted_price = $inpt_price WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            echo "success";
        } else {
            echo "Error updating price: " . $conn->error;
        }
    } else {
        echo "Invalid input.";
    }
}

if (isset($_POST['chng_status'])) {
    $approval_id = mysqli_real_escape_string($conn, $_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    if (!empty($approval_id) && !empty($status)) {
        $update_sql = "UPDATE approval SET status = $status WHERE approval_id = $approval_id";

        if ($conn->query($update_sql) === TRUE) {
            if ($status == 2) {
                $approval_sql = "SELECT * FROM approval WHERE approval_id = $approval_id";
                $approval_result = $conn->query($approval_sql);
                $approval_data = $approval_result->fetch_assoc();

                if ($approval_data) {
                    $pay_type = $conn->real_escape_string($approval_data['pay_type'] ?? '');

                    $fields = [
                        'status' => 1,
                        'estimateid' => 0,
                        'cashier' => $approval_data['cashier'] ?? 'NULL',
                        'total_price' => $approval_data['total_price'],
                        'discounted_price' => $approval_data['discounted_price'],
                        'discount_percent' => $approval_data['discount_percent'],
                        'cash_amt' => $approval_data['cash_amt'],
                        'credit_amt' => 0,
                        'order_date' => 'NOW()',
                        'scheduled_date' => 'NULL',
                        'delivered_date' => 'NULL',
                        'customerid' => $approval_data['customerid'],
                        'originalcustomerid' => $approval_data['originalcustomerid'] ?? 'NULL',
                        'job_name' => "'" . $conn->real_escape_string($approval_data['job_name']) . "'",
                        'job_po' => "'" . $conn->real_escape_string($approval_data['job_po']) . "'",
                        'deliver_address' => "'" . $conn->real_escape_string($approval_data['deliver_address']) . "'",
                        'deliver_city' => "'" . $conn->real_escape_string($approval_data['deliver_city']) . "'",
                        'deliver_state' => "'" . $conn->real_escape_string($approval_data['deliver_state']) . "'",
                        'deliver_zip' => "'" . $conn->real_escape_string($approval_data['deliver_zip']) . "'",
                        'delivery_amt' => "'" . $conn->real_escape_string($approval_data['delivery_amt']) . "'",
                        'deliver_fname' => "'" . $conn->real_escape_string($approval_data['deliver_fname']) . "'",
                        'deliver_lname' => "'" . $conn->real_escape_string($approval_data['deliver_lname']) . "'",
                        'deliver_method' => "'pickup'",
                        'is_edited' => 0,
                        'order_from' => 1,
                        'shipping_company' => 0,
                        'tracking_number' => 'NULL',
                        'pickup_name' => 'NULL',
                        'pay_type' => "'$pay_type'"
                    ];

                    $columns = implode(', ', array_keys($fields));
                    $values = implode(', ', array_values($fields));

                    $order_sql = "INSERT INTO orders ($columns) VALUES ($values)";
                    if ($conn->query($order_sql)) {
                        $new_orderid = $conn->insert_id;

                        $prod_sql = "SELECT * FROM approval_product WHERE approval_id = $approval_id";
                        $prod_result = $conn->query($prod_sql);

                        while ($prod = $prod_result->fetch_assoc()) {
                            $order_prod_sql = "
                                INSERT INTO order_product (
                                    orderid, productid, product_item, status, paid_status, quantity,
                                    custom_color, custom_grade, custom_width, custom_height,
                                    custom_bend, custom_hem, custom_length, custom_length2,
                                    actual_price, discounted_price, product_category,
                                    usageid, current_customer_discount, current_loyalty_discount,
                                    used_discount, stiff_stand_seam, stiff_board_batten,
                                    panel_type, custom_img_src
                                ) VALUES (
                                    '$new_orderid',
                                    '{$prod['productid']}',
                                    '" . $conn->real_escape_string($prod['product_item']) . "',
                                    0,
                                    0,
                                    '{$prod['quantity']}',
                                    " . ($prod['custom_color'] ?? 'NULL') . ",
                                    " . ($prod['custom_grade'] ?? 'NULL') . ",
                                    '" . $conn->real_escape_string($prod['custom_width']) . "',
                                    " . ($prod['custom_height'] ? "'" . $conn->real_escape_string($prod['custom_height']) . "'" : 'NULL') . ",
                                    " . ($prod['custom_bend'] ? "'" . $conn->real_escape_string($prod['custom_bend']) . "'" : 'NULL') . ",
                                    " . ($prod['custom_hem'] ? "'" . $conn->real_escape_string($prod['custom_hem']) . "'" : 'NULL') . ",
                                    " . ($prod['custom_length'] ? "'" . $conn->real_escape_string($prod['custom_length']) . "'" : 'NULL') . ",
                                    " . ($prod['custom_length2'] ? "'" . $conn->real_escape_string($prod['custom_length2']) . "'" : 'NULL') . ",
                                    '{$prod['actual_price']}',
                                    '{$prod['discounted_price']}',
                                    '{$prod['product_category']}',
                                    '{$prod['usageid']}',
                                    '{$prod['current_customer_discount']}',
                                    '{$prod['current_loyalty_discount']}',
                                    '{$prod['used_discount']}',
                                    '{$prod['stiff_stand_seam']}',
                                    '{$prod['stiff_board_batten']}',
                                    '{$prod['panel_type']}',
                                    NULL
                                )
                            ";
                            $conn->query($order_prod_sql);
                        }

                        $cashierid = intval($_SESSION['userid']);
                        $actorId = $cashierid;
                        $actor_name = get_staff_name($actorId);
                        createNotification(
                            $actorId,
                            'approval_granted',
                            $approval_id,
                            'Request for Approval Granted',
                            "Approval #$approval_id requested by $actor_name",
                            'cashier',
                            '?page=approved_list'
                        );

                        $job_id = getJobID($approval_data['job_name'], $approval_data['customerid']);
                        $amount = floatval($approval_data['discounted_price']);
                        $job_id_value = $job_id ? "'$job_id'" : '0';
                        $po_number = $conn->real_escape_string($approval_data['job_po']);
                        $customer_id = intval($approval_data['customerid']);

                        $ledger_sql = "
                            INSERT INTO job_ledger (
                                job_id, customer_id, entry_type, amount, po_number, reference_no, description,
                                check_number, created_by, created_at, payment_method
                            ) VALUES (
                                $job_id_value,
                                $customer_id,
                                'credit',
                                $amount,
                                '$po_number',
                                '',
                                'Approval converted to order',
                                NULL,
                                0,
                                NOW(),
                                '$pay_type'
                            )
                        ";

                        mysqli_query($conn, $ledger_sql);

                        echo "success";
                    } else {
                        echo "Error inserting order: " . $conn->error;
                    }
                } else {
                    echo "Approval not found.";
                }
            } else {
                echo "success";
            }
        } else {
            echo "Error updating status: " . $conn->error;
        }
    } else {
        echo "Invalid input. ID: $approval_id, Status: $status";
    }
}

if(isset($_POST['assign_coil'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $selected_coils = json_decode($_POST['selected_coils'], true);

    $coils_json = json_encode($selected_coils);

    $sql = "UPDATE approval_product 
        SET assigned_coils = '$coils_json' 
        WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "success";
    } else {
        echo "Error updating records: " . $conn->error;
    }
}





