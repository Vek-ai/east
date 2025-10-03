<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_order_details'])){
    $orderid = mysqli_real_escape_string($conn, $_POST['orderid']);
    ?>
    <style>
        .tooltip-inner {
            font-size: calc(0.875rem + 2px) !important;
        }
    </style>
    <div class="card-body datatables">
        <div class="product-details table-responsive text-nowrap">
            <table id="order_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Color</th>
                        <th>Grade</th>
                        <th>Profile</th>
                        <th class="text-center">Quantity to Return</th>
                        <th class="text-center">Dimensions</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Customer Price</th>
                        <th> </th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 0;
                    $query = "SELECT * FROM order_product WHERE orderid='$orderid' AND status IN (2, 3, 4)";
                    $result = mysqli_query($conn, $query);
                    $totalquantity = $total_actual_price = $total_disc_price = 0;
                    if ($result && mysqli_num_rows($result) > 0) {
                        $response = array();
                        while ($row = mysqli_fetch_assoc($result)) {
                            $product_id = $row['productid'];
                            if($row['quantity'] > 0){
                                $actual_price = number_format(floatval($row['actual_price']),2);
                                $discounted_price = number_format(floatval($row['discounted_price']),2);

                                $order_date = getOrderDateByProductId($row['id']);

                                $order_date = getOrderDateByProductId($row['id'], $conn);
                                $store_credited = 0;

                                if ($order_date) {
                                    $orderDateObj = new DateTime($order_date);
                                    $currentDateObj = new DateTime();

                                    $interval = $orderDateObj->diff($currentDateObj);
                                    $daysPassed = $interval->days;

                                    if ($daysPassed >= 90) {
                                        $store_credited = 1;
                                    }
                                }
                            ?>
                            <tr>
                                <td class="text-wrap"> 
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
                                <td>
                                    <input class="form-control" type="text" size="5" value="<?php echo $row['quantity']; ?>" style="color:#ffffff;" data-id="<?= $row["id"] ?>" id="return_quantity<?= $row["id"] ?>">
                                </td>
                                <td>
                                    <?php 
                                    $width = $row['custom_width'];
                                    $bend = $row['custom_bend'];
                                    $hem = $row['custom_hem'];
                                    $length = $row['custom_length'];
                                    $inch = $row['custom_length2'];
                                    
                                    if (!empty($width)) {
                                        echo "Width: " . htmlspecialchars($width) . "<br>";
                                    }
                                    
                                    if (!empty($bend)) {
                                        echo "Bend: " . htmlspecialchars($bend) . "<br>";
                                    }
                                    
                                    if (!empty($hem)) {
                                        echo "Hem: " . htmlspecialchars($hem) . "<br>";
                                    }
                                    
                                    if (!empty($length)) {
                                        echo "Length: " . htmlspecialchars($length) . " ft";
                                        
                                        if (!empty($inch)) {
                                            echo " " . htmlspecialchars($inch) . " in";
                                        }
                                        echo "<br>";
                                    } elseif (!empty($inch)) {
                                        echo "Length: " . htmlspecialchars($inch) . " in<br>";
                                    }
                                    ?>
                                </td>
                                <td class="text-end">$ <?= $actual_price ?></td>
                                <td class="text-end">$ <span id="return_price<?= $row["id"] ?>"><?= $discounted_price ?></span></td>
                                <td>
                                    <a href="javascript:void(0);" class="py-1 pe-1 fs-5" id="return_product" data-id="<?php echo $row["id"]; ?>" data-store-credited="<?= $store_credited ?>"><i class="fa fa-rotate-left text-success"></i></i></a>
                                </td>
                            </tr>
                    <?php
                            $totalquantity += $row['quantity'] ;
                            $total_actual_price += $actual_price;
                            $total_disc_price += $discounted_price;
                            }
                        }
                    }
                    ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="4">Total</td>
                        <td><?= $totalquantity ?></td>
                        <td></td>
                        <td class="text-end">$ <?= number_format($total_actual_price,2) ?></td>
                        <td class="text-end">$ <?= number_format($total_disc_price,2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>   
    <script>
        $(document).ready(function() { 
            $('#order_dtls_tbl').DataTable({
                language: {
                    emptyTable: "Order Details not found"
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
        });
    </script>
    <?php
}