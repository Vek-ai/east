<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_view_modal") {
        $orderid = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM order_product WHERE orderid = '$orderid'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            ?>
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            Order Details
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div id="order_details" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card card-body datatables">
                                <div class="table-responsive">
                                <h3 class="card-title d-flex justify-content-between align-items-center">
                                    Orders List
                                </h3>
                                <table id="ordersDetails" class="table search-table align-middle text-nowrap">
                                    <thead class="header-item">
                                    <th style="width: 20%;">Product</th>
                                    <th>Quantity</th>
                                    <th>Width</th>
                                    <th>Height</th>
                                    <th>Bend</th>
                                    <th>Hem</th>
                                    <th>Length</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actual Price</th>
                                    <th>Disc. Price</th>
                                    </thead>
                                    <tbody>
                                    <?php         
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $orderid = $row['orderid'];
                                            $product_id = $row['productid'];
                                            $product_details = getProductDetails($product_id);
                                            $product_name = $product_details['product_item'];
                                            $quantity = $row['quantity'];
                                            $custom_width = $row['custom_width'];

                                            if($custom_width == 0){
                                                $custom_width = $product_details['width'];
                                            }

                                            $custom_height = $row['custom_height'];
                                            $custom_bend = $row['custom_bend'];
                                            $custom_hem = $row['custom_hem'];
                                            $custom_length = $row['custom_length'];
                                            

                                            $stock = getProductStockTotal($product_id);
                                            if($stock > 0){
                                                $stock_text = "<span class='text-bg-success p-1 rounded-circle'></span><p class='mb-0 ms-2'>$stock</p>";
                                            }else{
                                                $stock_text = '<span class="text-bg-danger p-1 rounded-circle"></span><p class="mb-0 ms-2">OutOfStock</p>';
                                            }

                                            $status = $row['status'];
                                            if ($status == 1) {
                                                $status_icon = "text-primary ti ti-trash";
                                                $status_text = "<a href='#'><div id='status-alert$no' class='alert alert-primary bg-primary text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Ordered</div></a>";
                                            }else if ($status == 2) {
                                                $status_icon = "text-warning ti ti-trash";
                                                $status_text = "<a href='#'><div id='status-alert$no' class='alert alert-warning bg-warning text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Manufacturing</div></a>";
                                            } else if ($status == 3) {
                                                $status_icon = "text-success ti ti-trash";
                                                $status_text = "<a href='#'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Dispatched</div></a>";
                                            }

                                            $actual_price = $row['actual_price'];
                                            $discounted_price = $row['discounted_price'];
                                        ?>
                                            <!-- start row -->
                                            <tr class="search-items">
                                                <td style="width: 20%;"><h6 class="fw-semibold mb-0 fs-4"><?= $product_name ?></h6></td>
                                                <td><?= $quantity ?></td>
                                                <td><?= $custom_width ?></td>
                                                <td><?= $custom_height ?></td>
                                                <td><?= $custom_bend ?></td>
                                                <td><?= $custom_hem ?></td>
                                                <td><?= $custom_length ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?= $stock_text ?>
                                                    </div>
                                                </td>
                                                <td><?= $status_text ?></td>
                                                <td><?= $actual_price ?></td>
                                                <td><?= $discounted_price ?></td>
                                            </tr>
                                        <?php 
                                        $no++;
                                        } ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.modal-content -->
            </div>
            <script>
                $(document).ready(function() {
                    var table = $('#ordersDetails').DataTable({
                        "order": [[1, "asc"]]
                    });
                });

            </script>

            <?php
        }
    } 
    
    mysqli_close($conn);
}
?>
