<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_est_details'])){
    $estimateid = mysqli_real_escape_string($conn, $_POST['estimateid']);
    ?>
    <div class="card-body">
        <div class="product-details table-responsive text-nowrap">
            <table id="productTable" class="table table-hover mb-0 text-md-nowrap">
                <thead>
                    <tr>
                        <th width="20%">Description</th>
                        <th width="13%" class="text-center">Quantity</th>
                        <th width="13%" class="text-center">Actual Price</th>
                        <th width="13%" class="text-center">Discounted Price</th>
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
                                <td><?= $row['quantity'] ?></td>
                                <td><?= $row['actual_price'] ?></td>
                                <td><?= $row['discounted_price'] ?></td>
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
                        <td colspan="3"></td>
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
    <?php
}