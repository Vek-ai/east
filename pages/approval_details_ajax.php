<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_POST['fetch_available'])){
    $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
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
                        <th>Coil No</th>
                        <th class="text-center">Date</th>
                        <th class="text-center">Color</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Coating</th>
                        <th class="text-right">Rem. Feet</th>
                        <th class="text-right">Price Per Inch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $query = "SELECT * FROM coil_product WHERE color_sold_as='$color_id' AND grade='$grade'";
                    $result = mysqli_query($conn, $query);
                    $totalprice = 0;
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $color_details = getColorDetails($row['color_sold_as']);
                            ?>
                            <tr data-id="<?= $product_id ?>">
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
                        <td class="text-end" colspan="6">Average Price</td>
                        <td class="text-end">$ <?= number_format($average_price,2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
       
    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip(); 

            if ($.fn.DataTable.isDataTable('#coil_dtls_tbl')) {
                $('#coil_dtls_tbl').DataTable().draw();
            } else {
                $('#coil_dtls_tbl').DataTable({
                    language: {
                        emptyTable: "No Available Coils with the selected color"
                    },
                    autoWidth: false,
                    responsive: true
                });
            }

            $('#view_available_modal').on('shown.bs.modal', function () {
                $('#coil_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
            });
        });
    </script>
    <?php
}


