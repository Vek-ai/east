<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_modal'])){
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_special_trim_id']);
    ?>
        <div class="card-body datatables">
            <div class="product-details table-responsive text-nowrap">
                <table id="special_trim_tbl" class="table table-hover mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Special Trim Description</th>
                            <th>Customer Trim #</th>
                            <th>Last Order Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 

                        $query = "
                            SELECT 
                                * 
                            FROM special_trim 
                            WHERE 
                                customer_id = '$customer_id' AND
                                hidden = 0 AND 
                                status = 1
                        ";

                        $query .= " ORDER BY last_order DESC";
                        $result = mysqli_query($conn, $query);
                        if ($result && mysqli_num_rows($result) > 0) {
                            $response = array();
                            while ($row = mysqli_fetch_assoc($result)) {
                                $customer_name = get_customer_name($customer_id);
                                $description = $row['special_trim_desc'];
                                $trim_no = $row['special_trim_no'];
                                $last_order = $row['last_order'];
                            ?>
                            <tr>
                                <td>
                                    <?= $customer_name ?>
                                </td>
                                <td >
                                    <?= $description ?>
                                </td>
                                <td >
                                    <?= $trim_no ?>
                                </td>
                                <td>
                                    <?= $last_order ?>
                                </td>
                                <td>
                                    <a href="javascript:void(0);" 
                                        class="select_special_trim_btn" 
                                        data-id="<?= $row["special_trim_id"]; ?>">
                                            <i class="fa fa-check"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <script>
        $(document).ready(function() {
            $('#special_trim_tbl').DataTable({
                language: {
                    emptyTable: "No products available for return"
                },
                autoWidth: false,
                responsive: true,
                order: []
            });
        });
    </script>
    <?php
}