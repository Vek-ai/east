<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$generate_rend_upc = generateRandomUPC();
$picture_path = "images/product/product.jpg";

if(!empty($_REQUEST['customer_id'])){
    $customer_id = $_REQUEST['customer_id'];
    $customer_details = getCustomerDetails($customer_id);
}

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

    .table-fixed {
        width: 100%;
    }

    .table-fixed th,
    .table-fixed td {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
        word-wrap: break-word;
    }

    .table-fixed th:nth-child(1),
    .table-fixed td:nth-child(1) { width: 30% !important; }
    .table-fixed th:nth-child(2),
    .table-fixed td:nth-child(2) { width: 10% !important; }
    .table-fixed th:nth-child(3),
    .table-fixed td:nth-child(3) { width: 10% !important; }
    .table-fixed th:nth-child(4),
    .table-fixed td:nth-child(4) { width: 10% !important; }
    .table-fixed th:nth-child(5),
    .table-fixed td:nth-child(5) { width: 15% !important; }
    .table-fixed th:nth-child(6),
    .table-fixed td:nth-child(6) { width: 15% !important; }
    .table-fixed th:nth-child(7),
    .table-fixed td:nth-child(7) { width: 10% !important; }
</style>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"><?php
            if(isset($customer_details)){
                echo "Customer " .$customer_details['customer_first_name'] .' ' .$customer_details['customer_last_name'];
            }
            ?> Loyalty Program List</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Loyalty Program
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Loyalty Program List</li>
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

    <div class="widget-content searchable-container list">

    <div class="modal fade" id="viewCustomersModal" tabindex="-1" aria-labelledby="viewCustomersModalLabel" aria-hidden="true"></div>

    <div class="modal fade" id="response-modal" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div id="responseHeaderContainer" class="modal-header align-items-center modal-colored-header">
                <h4 id="responseHeader" class="m-0"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <p id="responseMsg"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
                Close
                </button>
            </div>
            </div>
        </div>
    </div>

    
    <div class="card card-body">
        <div class="card-body datatables">
            <div class="product-details table-responsive text-nowrap">
                <table id="loyalty_tbl" class="table table-hover mb-0 text-md-nowrap">
                    <thead>
                        <tr>
                            <th>Loyalty Program</th>
                            <th>Order Amount Required</th>
                            <th>Discount (%)</th>
                            <th>No. of Customers</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $query = "SELECT * FROM loyalty_program WHERE status = '1'";
                        $result = mysqli_query($conn, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $query_orders = "
                                    SELECT COUNT(DISTINCT customerid) AS customer_count
                                    FROM (
                                        SELECT customerid
                                        FROM orders
                                        GROUP BY customerid
                                        HAVING SUM(discounted_price) >= " . intval($row['accumulated_total_orders']) . "
                                    ) AS customer_filter;
                                ";

                                $result_orders = mysqli_query($conn, $query_orders);
                                $row_orders = mysqli_fetch_assoc($result_orders);
                                $customer_count = isset($row_orders['customer_count']) ? $row_orders['customer_count'] : 0;
                                ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($row['loyalty_program_name'])  ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['accumulated_total_orders']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['discount']) ?>
                                    </td>
                                    <td>
                                        <?= $customer_count ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger-gradient btn-sm p-0 me-1" id="view_customers_btn" title="View" type="button" data-id="<?= htmlspecialchars($row['loyalty_id']); ?>">
                                            <i class="fa fa-eye fs-5"></i>
                                        </button>
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
    </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#loyalty_tbl').DataTable();

        $(document).on('click', '#view_customers_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/customer_loyalty_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_view_modal"
                    },
                    success: function(response) {
                        $('#viewCustomersModal').html(response);
                        $('#viewCustomersModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });
    });
</script>