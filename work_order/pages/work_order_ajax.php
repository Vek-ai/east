<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

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
                        <th class="text-center">Entry No</th>
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

                /* $.ajax({
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
                        } else {
                            alert('Failed to Update!');
                            console.log(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Response Text:', xhr.responseText);
                    }
                }); */
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

    $query = "
        SELECT wo.*, p.product_item
        FROM work_order AS wo
        LEFT JOIN product AS p ON p.product_id = wo.productid
        WHERE 1 = 1
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
                    <th>Requested by</th>
                    <th>Color</th>
                    <th>Grade</th>
                    <th>Profile</th>
                    <th>Width</th>
                    <th>Length</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-center">Details</th>
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
                        <?= get_name($row['user_id']); ?>
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


