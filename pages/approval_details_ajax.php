<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_POST['fetch_available'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $app_prod_arr = getApprovalProductDetails($id);
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
                        <th>
                            <input type="checkbox" id="selectAll" >
                        </th>
                        <th>Coil No</th>
                        <th class="text-center">Date</th>
                        <th class="text-center">Color</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Thickness</th>
                        <th class="text-right">Width</th>
                        <th class="text-right">Rem. Feet</th>
                        <th class="text-right">Price Per Inch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $query = "SELECT * FROM coil_product WHERE color_sold_as='$color_id' AND grade='$grade' AND width >='$width'";
                    $result = mysqli_query($conn, $query);
                    $totalprice = 0;
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $color_details = getColorDetails($row['color_sold_as']);
                            ?>
                            <tr data-id="<?= $product_id ?>">
                                <td class="text-start">
                                    <input type="checkbox" class="row-select" data-id="<?= $row['coil_id'] ?>">
                                </td>
                                <td class="text-wrap"> 
                                    <?= $row['entry_no'] ?>
                                </td>
                                <td class="text-wrap"> 
                                    <?= date("M d, Y", strtotime($row['date'])) ?>
                                </td>
                                <td>
                                <div class="d-inline-flex align-items-center gap-2">
                                    <span class="rounded-circle d-block" style="background-color:<?= $color_details['color_code'] ?>; width: 20px; height: 20px;"></span>
                                    <?= $color_details['color_name'] ?>
                                </div>
                                </td>
                                <td>
                                    <?php echo getGradeName($row['grade']); ?>
                                </td>
                                <td>
                                    <?php echo $row['thickness']; ?>
                                </td>
                                <td class="text-right">
                                    <?php echo $row['width']; ?>
                                </td>
                                <td class="text-right">
                                    <?php echo $row['remaining_feet']; ?>
                                </td>
                                <td class="text-right">
                                    $<?php echo $row['price']; ?>
                                </td>
                            </tr>
                            <?php
                            $totalprice += $row['price'] ;
                            $no++;
                        }

                        $average_price = $totalprice / $no;
                    }
                    ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td class="text-end" colspan="8">Average Price</td>
                        <td class="text-end">$ <?= number_format($average_price,2) ?></td>
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
                    url: 'pages/approval_details_ajax.php',
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
                });
            });

            $('[data-toggle="tooltip"]').tooltip(); 

            if ($.fn.DataTable.isDataTable('#coil_dtls_tbl')) {
                $('#coil_dtls_tbl').DataTable().draw();
            } else {
                $('#coil_dtls_tbl').DataTable({
                    language: {
                        emptyTable: "No Available Coils with the selected color"
                    },
                    autoWidth: false,
                    responsive: true,
                    columnDefs: [
                        { targets: 0, width: "5%" }
                    ]
                });
            }

            $('#view_available_modal').on('shown.bs.modal', function () {
                $('#coil_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
            });
        });
    </script>
    <?php
}

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

if(isset($_POST['chng_status'])){
    $approval_id = mysqli_real_escape_string($conn, $_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    if (!empty($approval_id) && !empty($status)) {
        $sql = "UPDATE approval SET status = $status WHERE approval_id = $approval_id";
        if ($conn->query($sql) === TRUE) {
            echo "success";
        } else {
            echo "Error updating price: " . $conn->error;
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





