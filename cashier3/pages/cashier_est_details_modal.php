<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_est_details'])){
    $estimateid = mysqli_real_escape_string($conn, $_POST['estimateid']);
    ?>
    <div class="card-body datatables">
        <div class="product-details table-responsive text-nowrap">
            <table id="est_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Color</th>
                        <th>Grade</th>
                        <th>Profile</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Dimensions</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Customer Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $query = "SELECT * FROM estimate_prod WHERE estimateid='$estimateid'";
                    $result = mysqli_query($conn, $query);
                    $totalquantity = $total_actual_price = $total_disc_price = 0;
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $product_id = $row['product_id'];
                        ?>
                            <tr>
                                <td>
                                    <?php echo getProductName($product_id) ?>
                                </td>
                                <td>
                                <div class="d-flex mb-0 gap-8">
                                    <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_id)?>"></a>
                                    <?= getColorFromID($product_id); ?>
                                </div>
                                </td>
                                <td>
                                    <?php echo getGradeFromID($product_id); ?>
                                </td>
                                <td>
                                    <?php echo getProfileFromID($product_id); ?>
                                </td>
                                <td><?= $row['quantity'] ?></td>
                                <td>
                                    <?php 
                                    $width = $row['custom_width'];
                                    $height = $row['custom_height'];
                                    
                                    if (!empty($width) && !empty($height)) {
                                        echo htmlspecialchars($width) . " X " . htmlspecialchars($height);
                                    } elseif (!empty($width)) {
                                        echo "Width: " . htmlspecialchars($width);
                                    } elseif (!empty($height)) {
                                        echo "Height: " . htmlspecialchars($height);
                                    }
                                    ?>
                                </td>
                                <td class="text-end">$ <?= number_format($row['actual_price'],2) ?></td>
                                <td class="text-end">$ <?= number_format($row['discounted_price'],2) ?></td>
                            </tr>
                    <?php
                            $totalquantity += $row['quantity'] ;
                            $total_actual_price += $row['actual_price'];
                            $total_disc_price += $row['discounted_price'];
                        }
                    }
                    ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="7"></td>
                        <td class="text-end">
                            <p>Total Quantity: <?= $totalquantity ?></p>
                            <p>Actual Price: <?= $total_actual_price ?></p>
                            <p>Discounted Price: <?= $total_disc_price ?></p>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>   
    <script>
        $(document).ready(function() {
            $('#est_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Estimate Details not found"
                },
                columnDefs: [
                    { width: "20%", targets: 0 },
                    { width: "15%", targets: [1, 2, 3] },
                    { width: "10%", targets: [4, 5] },
                    { width: "15%", targets: 6 }
                ],
                autoWidth: false,
                responsive: true
            });

            $('#view_est_details_modal').on('shown.bs.modal', function () {
                $('#est_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
            });
        });
    </script>
    <?php
}