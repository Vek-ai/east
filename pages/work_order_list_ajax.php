<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

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

if(isset($_POST['fetch_available'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $app_prod_arr = getWorkOrderDetails($id);
    $color_id = $app_prod_arr['custom_color'];
    $grade = $app_prod_arr['custom_grade'];
    $width = floatval($app_prod_arr['custom_width']);
    $lengthFeet = !empty($app_prod_arr['custom_length']) ? floatval($app_prod_arr['custom_length']) : 0;
    $lengthInch = !empty($app_prod_arr['custom_length2']) ? floatval($app_prod_arr['custom_length2']) : 0;
    $quantity = !empty($app_prod_arr['quantity']) ? floatval($app_prod_arr['quantity']) : 1;
    $total_ln_in_ft = $lengthFeet + ($lengthInch / 12);
    $total_ln_in_ft = !empty($total_ln_in_ft) ? $total_ln_in_ft : 1;
    $total_length = $total_ln_in_ft * $quantity;
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
            <table id="coil_dtls_tbl" class="table table-hover mb-0 text-md-nowrap text-center">
                <thead>
                    <tr>
                        <th></th>
                        <th>
                            <input type="checkbox" id="selectAll" >
                        </th>
                        <th>Coil No</th>
                        <th class="text-center">Date</th>
                        <th class="text-left">Color</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Thickness</th>
                        <th class="text-right">Width</th>
                        <th class="text-right">Rem. Feet</th>
                        <th class="text-right">Price/In</th>
                        <th class="text-right">Avg Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $query = "SELECT * FROM coil_product ORDER BY date ASC";
                    $result = mysqli_query($conn, $query);

                    $grouped_data = [];
                    $all_rows = [];

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $all_rows[] = $row;

                            $group_key = $row['color_sold_as'] . '-' . $row['gauge'] . '-' . $row['year'] . '-' . $row['grade_no'];
                            if (!isset($grouped_data[$group_key])) {
                                $grouped_data[$group_key] = [
                                    'total_price' => 0,
                                    'count' => 0
                                ];
                            }
                            
                            $grouped_data[$group_key]['total_price'] += $row['price'];
                            $grouped_data[$group_key]['count'] += 1;
                        }
                    }

                    foreach ($grouped_data as $key => $data) {
                        $grouped_data[$key]['average_price'] = $data['total_price'] / $data['count'];
                    }

                    $totalprice = 0;
                    $no = 0;
                    $group_count = count($grouped_data);
                    $weighted_sum = 0;
                    $total_weight = 0;

                    $is_checked = 1;
                    $total_length_reached = 0;

                    foreach ($all_rows as $row) {
                        if( $row['color_sold_as'] == $color_id &&
                            $row['grade'] == $grade &&
                            $row['width'] >= $width ){
                        
                            $color_details = getColorDetails($row['color_sold_as']);
                            $group_key = $row['color_sold_as'] . '-' . $row['gauge'] . '-' . $row['year'] . '-' . $row['grade_no'];
                            $average_price = $grouped_data[$group_key]['average_price'];

                            $weighted_sum += $row['price'] * $row['remaining_feet'];
                            $total_weight += $row['remaining_feet'];

                            if ($total_weight > 0) {
                                $weighted_average = $weighted_sum / $total_weight;
                            }

                            if ($total_length_reached < $total_length) {
                                $total_length_reached += $row['remaining_feet'];
                            } else {
                                $is_checked = 0;
                            }

                            ?>
                            <tr data-id="<?= $row['coil_id'] ?>" data-length="<?= $total_length ?>">
                                <td><?= $is_checked; ?></td>
                                <td class="text-start">
                                    <input type="checkbox" class="row-select" data-id="<?= $row['coil_id'] ?>" <?= !empty($is_checked) ? 'checked' : '' ?>>
                                </td>
                                <td class="text-wrap"> 
                                    <?= $row['entry_no'] ?>
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
                                <td class="text-right">
                                    $<?= number_format($average_price, 2); ?>
                                </td>
                            </tr>
                            <?php
                            $totalprice += $average_price;
                            $no++;
                        }

                        if ($total_weight > 0) {
                            $weighted_average = $weighted_sum / $total_weight;
                        }
                    }
                    ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="10" class="text-right"><strong>Weighted Average Price:</strong></td>
                        <td class="text-right"><strong>$<?= number_format($weighted_average, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
       
    <script>
        $(document).ready(function() {
            let selectedCoils = [];

            $(document).off('change', '.row-select').on('change', '.row-select', function () {
                const coilId = $(this).data('id');

                if ($(this).is(':checked')) {
                    if (!selectedCoils.includes(coilId)) {
                        selectedCoils.push(coilId);
                    }
                } else {
                    selectedCoils = selectedCoils.filter(id => id !== coilId);
                }
            });

            $('#selectAll').off('change').on('change', function () {
                const isChecked = $(this).is(':checked');
                const table = $('#coil_dtls_tbl').DataTable();
                const allRows = table.rows().nodes();

                $(allRows).find('.row-select').prop('checked', isChecked).trigger('change');
            });

            $('#saveSelection').off('click').on('click', function () {
                const table = $('#coil_dtls_tbl').DataTable();
                const allRows = table.rows().nodes();

                const id = <?= $id ?? 0 ?>;
                
                selectedCoils = [];

                $(allRows).find('.row-select:checked').each(function () {
                    selectedCoils.push($(this).data('id'));
                });

                const selectedCoilsJson = JSON.stringify(selectedCoils);

                $.ajax({
                    type: 'POST',
                    url: 'pages/work_order_list_ajax.php',
                    data: { 
                        id: id,
                        selected_coils: selectedCoilsJson,
                        assign_coil: 'assign_coil'
                    },
                    success: function(response) {
                        if (response.trim() == 'success') {
                            alert('Successfully Saved!');
                            location.reload();
                        } else {
                            alert('Failed to Update!');
                            console.log(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Response Text:', xhr.responseText);
                    }
                });
            });

            $('[data-toggle="tooltip"]').tooltip(); 

            $.fn.dataTable.ext.type.order['custom-date-pre'] = function (date) {
                const months = {
                    Jan: 0, Feb: 1, Mar: 2, Apr: 3, May: 4, Jun: 5,
                    Jul: 6, Aug: 7, Sep: 8, Oct: 9, Nov: 10, Dec: 11
                };
                const parts = date.split(' ');
                const month = months[parts[0]];
                const day = parseInt(parts[1].replace(',', ''), 10);
                const year = parseInt(parts[2], 10);
                var date_return = new Date(year, month, day).getTime();
                return date_return;
            };

            if ($.fn.DataTable.isDataTable('#coil_dtls_tbl')) {
                $('#coil_dtls_tbl').DataTable().order([[0, 'desc'], [3, 'asc']]).draw();
            } else {
                $('#coil_dtls_tbl').DataTable({
                    language: {
                        emptyTable: "No Available Coils with the selected color"
                    },
                    autoWidth: false,
                    responsive: true,
                    columnDefs: [
                        { targets: 0, visible: false },
                        { targets: 1, width: "5%" },
                        { targets: 3, type: 'custom-date' }
                    ],
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

if(isset($_POST['assign_coil'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $wrk_ordr = getWorkOrderDetails($id);

    $userid = $_SESSION['userid'];

    $selected_coils = json_decode($_POST['selected_coils'], true);
    $coils_json = json_encode($selected_coils);

    $sql = "
        INSERT INTO work_order(
            work_order_product_id,
            productid,
            status,
            quantity,
            custom_color,
            custom_grade,
            custom_width,
            custom_height,
            custom_bend,
            custom_hem,
            custom_length,
            custom_length2,
            actual_price,
            discounted_price,
            product_category,
            usageid,
            current_customer_discount,
            current_loyalty_discount,
            used_discount,
            stiff_stand_seam,
            stiff_board_batten,
            panel_type,
            submitted_date,
            assigned_coils,
            user_id
        )
        VALUES(
            '".$id."',
            '".$wrk_ordr['productid']."',
            '1',
            '".$wrk_ordr['quantity']."',
            '".$wrk_ordr['custom_color']."',
            '".$wrk_ordr['custom_grade']."',
            '".$wrk_ordr['custom_width']."',
            '".$wrk_ordr['custom_height']."',
            '".$wrk_ordr['custom_bend']."',
            '".$wrk_ordr['custom_hem']."',
            '".$wrk_ordr['custom_length']."',
            '".$wrk_ordr['custom_length2']."',
            '".$wrk_ordr['actual_price']."',
            '".$wrk_ordr['discounted_price']."',
            '".$wrk_ordr['product_category']."',
            '".$wrk_ordr['usageid']."',
            '".$wrk_ordr['current_customer_discount']."',
            '".$wrk_ordr['current_loyalty_discount']."',
            '".$wrk_ordr['used_discount']."',
            '".$wrk_ordr['stiff_stand_seam']."',
            '".$wrk_ordr['stiff_board_batten']."',
            '".$wrk_ordr['panel_type']."',
            CURRENT_TIMESTAMP(), 
            '$coils_json',
            '$userid'
        )
    ";

    if ($conn->query($sql) === TRUE) {
        $sql = "UPDATE work_order_product SET status = '2' WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            echo "success";
        } else {
            echo "Error updating records: " . $conn->error;
        }
    } else {
        echo "Error updating records: " . $conn->error;
    }
}

if (isset($_POST['search_work_order'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_search']);
    $date_from = mysqli_real_escape_string($conn, $_POST['date_from']);
    $date_to = mysqli_real_escape_string($conn, $_POST['date_to']);

    $query = "
        SELECT wop.*, p.product_item
        FROM work_order_product AS wop
        LEFT JOIN product AS p ON p.product_id = wop.productid
        WHERE 1 = 1
    ";

    if (!empty($product_name) && $product_name != 'All Products') {
        $query .= " AND p.product_item LIKE '%$product_name%' ";
    }

    if (!empty($date_from) && !empty($date_to)) {
        $date_to .= ' 23:59:59';
        $query .= " AND (wop.submitted_date >= '$date_from' AND wop.submitted_date <= '$date_to') ";
    }else{
        $query .= " AND (wop.submitted_date >= DATE_SUB(curdate(), INTERVAL 2 WEEK) AND wop.submitted_date <= NOW()) ";
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

            while ($row = mysqli_fetch_assoc($result)) {
                $color_details = getColorDetails($row['custom_color']);
                $product_id = $row['productid'];
                $width = $row['custom_width'];
                $bend = $row['custom_bend'];
                $hem = $row['custom_hem'];
                $length = $row['custom_length'];
                $inch = $row['custom_length2'];
                ?>
                <tr data-id="<?= $product_id ?>">
                    <td class="text-wrap w-20"> 
                        <?php echo getProductName($product_id) ?>
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
                                case 1:
                                    $statusText = 'New';
                                    $statusColor = 'bg-primary';
                                    break;
                                case 2:
                                    $statusText = 'Processing';
                                    $statusColor = 'bg-warning';
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
                            <a href="javascript:void(0)" class="text-decoration-none" id="viewAvailableBtn" data-app-prod-id="<?= $row['id'] ?>">
                                <i class="fa fa-arrow-right-to-bracket"></i>
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
}

