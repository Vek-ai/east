<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_est_list'])){
    ?>
        <div class="card-body">
            <div class="product-details table-responsive text-nowrap">
                <table id="productTable" class="table table-hover mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Total Price</th>
                            <th>Discounted Price</th>
                            <th>Estimate Date</th>
                            <th>Order Date</th>
                            <th>Action</i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 

                        $query = "SELECT * FROM estimates WHERE status = '1'";
                        $result = mysqli_query($conn, $query);
                    
                        if ($result && mysqli_num_rows($result) > 0) {
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td>
                                    <?php echo get_customer_name($row["customerid"]) ?>
                                </td>
                                <td >
                                    <?php echo number_format($row["total_price"],2) ?>
                                </td>
                                <td >
                                    <?php echo number_format($row["discounted_price"],2) ?>
                                </td>
                                <td>
                                    <?php echo $row["estimated_date"] ?>
                                </td>
                                <td>
                                    <?php echo $row["order_date"] ?>
                                </td>
                                <td>
                                    <button class="btn btn-danger-gradient btn-sm" id="view_est_details" type="button" data-id="<?php echo $row["estimateid"]; ?>"><i class="fa fa-eye"></i></button>
                                </td>
                            </tr>
                            <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="6" class="text-center">No Estimates found.</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
}