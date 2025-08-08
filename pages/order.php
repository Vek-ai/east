<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$generate_rend_upc = generateRandomUPC();
$picture_path = "images/product/product.jpg";
?>
<style>
    .select2-container {
        z-index: 9999 !important; 
    }
    .dz-preview {
        position: relative;
    }

    .dz-remove {
        position: absolute;
        top: 10px;
        right: 10px;
        background: red;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        font-weight: bold;
        font-size: 12px;
        z-index: 9999; /* Ensure the remove button is on top of the image */
        cursor: pointer; /* Make sure it looks clickable */
    }
</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"> Orders List</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Orders
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Orders List</li>
            </ol>
            </nav>
        </div>
        <div>
            <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
            <div class="d-flex gap-2">
                <div class="">
                <small>This Month</small>
                <h4 class="text-primary mb-0 ">$58,256</h4>
                </div>
                <div class="">
                <div class="breadbar"></div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="">
                <small>Last Month</small>
                <h4 class="text-secondary mb-0 ">$58,256</h4>
                </div>
                <div class="">
                <div class="breadbar2"></div>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="view_order_details" tabindex="-1" role="dialog" aria-labelledby="view_order_details" aria-hidden="true">
        
    </div>

    <div class="widget-content searchable-container list">
    <div class="card card-body datatables">
        <div class="table-responsive">
        <h3 class="card-title d-flex justify-content-between align-items-center">
            Orders List
        </h3>
        <table id="ordersList" class="table search-table align-middle text-nowrap">
            <thead class="header-item">
            <th>Customer Name</th>
            <th>Total Price</th>
            <th>Discounted Price</th>
            <th>Order Date</th>
            <th>Status</th>
            <th>Action</th>
            </thead>
            <tbody>
            <?php
                $no = 1;
                $query_orders = "SELECT * FROM orders";
                $result_orders = mysqli_query($conn, $query_orders);            
                while ($row_orders = mysqli_fetch_array($result_orders)) {
                    $orderid = $row_orders['orderid'];
                    $customerid = $row_orders['customerid'];
                    $total_price = $row_orders['total_price'];
                    $discounted_price = $row_orders['discounted_price'];
                    $order_date = date('Y-m-d', strtotime($row_orders['order_date']));
                    $status = $row_orders['status'];

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
   
                ?>
                    <!-- start row -->
                    <tr class="search-items">
                        <td><h6 class="fw-semibold mb-0 fs-4"><?= get_customer_name($customerid) ?></h6></td>
                        <td><?= $total_price ?></td>
                        <td><?= $discounted_price ?></td>
                        <td><?= $order_date ?></td>
                        <td><?= $status_text ?></td>
                        <td>
                            <div class="action-btn text-center">
                                <a href="#" id="view_orders_btn" class="text-primary edit" data-id="<?= $orderid ?>">
                                    <i class="text-primary ti ti-eye fs-7"></i>
                                </a>
                            </div>
                        </td>
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

<script>
    $(document).ready(function() {
        var table = $('#ordersList').DataTable({
            "order": [[4, "desc"]]
        });

        // Show the View Product modal and log the product ID
        $(document).on('click', '#view_orders_btn', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/order_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_view_modal"
                    },
                    success: function(response) {
                        $('#view_order_details').html(response);
                        $('#view_order_details').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });
    });
</script>



