<?php
$job_name = $_REQUEST['job_name'] ?? '';
$customer_id = $_REQUEST['customer_id'] ?? 0;
?>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Job Name: <?= ucwords($job_name) ?></h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="/">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Job Name: <?= ucwords($job_name) ?></li>
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

    <div class="card card-body">
        <div class="card-body">
            <div class="row">
                <div class="datatables">
                    <div id="tbl-job-details" class="product-details table-responsive text-nowrap">
                        <?php
                        $query_approval = "
                            SELECT 
                                'approval' AS source_table, 
                                a.*
                            FROM approval AS a
                            WHERE job_name = '$job_name' AND originalcustomerid = '$customer_id'
                        ";
                    
                        $result = $conn->query($query_approval);
                        if ($result && mysqli_num_rows($result) > 0) {
                            $total_amount = 0;
                            $total_count = 0;
                    
                            ?>
                            <div class="card card-body datatables">
                                <div class="table-responsive">
                                    <h4>Approvals List</h4>
                                    <table id="approval_table" class="table table-hover mb-0 text-md-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Invoice Number</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Amount</th>
                                                <th> </th>
                                            </tr>
                                        </thead>
                                        <tbody>     
                                        <?php
                            
                                        while ($row_approval = mysqli_fetch_assoc($result)) {
                                            $total_amount += $row_approval['discounted_price'];
                                            $total_count += 1;
                                            ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($row_approval['approval_id']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars(date("F d, Y", strtotime($row_approval['submitted_date']))) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars(date("h:i A", strtotime($row_approval['submitted_date']))) ?>
                                                </td>
                                                <td>
                                                    $ <?= htmlspecialchars($row_approval['discounted_price']) ?>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0);" class="py-1 pe-1 fs-5" id="view_status_details" data-id="<?php echo $row_approval["approval_id"]; ?>" data-type="<?= $row_approval['source_table'] ?>" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                        <tfoot>
                                            <td colspan="3" class="text-end">Total: </td>
                                            <td>$ <?= number_format($total_amount,2) ?></td>
                                            <td></td>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            
                            <?php
                        }
                
                        $query_estimate = "
                            SELECT 
                                'estimate' AS source_table, 
                                e.* 
                            FROM estimates AS e
                            WHERE job_name = '$job_name' AND originalcustomerid = '$customer_id'
                        ";
                    
                        $result = $conn->query($query_estimate);
                        if ($result && mysqli_num_rows($result) > 0) {
                            $total_amount = 0;
                            $total_count = 0;
                    
                            ?>
                            <div class="card card-body datatables">
                                <div class="table-responsive">
                                    <h4>Estimates List</h4>
                                    <table id="estimate_table" class="table table-hover mb-0 text-md-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Invoice Number</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Amount</th>
                                                <th> </th>
                                            </tr>
                                        </thead>
                                        <tbody>     
                                        <?php
                            
                                        while ($row_estimate = mysqli_fetch_assoc($result)) {
                                            $total_amount += $row_estimate['discounted_price'];
                                            $total_count += 1;
                                            ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($row_estimate['estimateid']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars(date("F d, Y", strtotime($row_estimate['estimated_date']))) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars(date("h:i A", strtotime($row_estimate['estimated_date']))) ?>
                                                </td>
                                                <td>
                                                    $ <?= htmlspecialchars($row_estimate['discounted_price']) ?>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0);" class="py-1 pe-1 fs-5" id="view_status_details" data-id="<?php echo $row_estimate["estimateid"]; ?>" data-type="<?= $row_estimate['source_table'] ?>" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                        <tfoot>
                                            <td colspan="3" class="text-end">Total: </td>
                                            <td>$ <?= number_format($total_amount,2) ?></td>
                                            <td></td>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            
                            <?php
                        }
                    
                        $query_order = "
                            SELECT 
                                'order' AS source_table, 
                                o.*
                            FROM orders AS o
                            WHERE job_name = '$job_name' AND originalcustomerid = '$customer_id'
                        ";
                    
                        $result = $conn->query($query_order);
                    
                        if ($result && mysqli_num_rows($result) > 0) {
                            $total_amount = 0;
                            $total_count = 0;
                    
                            ?>
                            <div class="card card-body datatables">
                                <div class="table-responsive">
                                    <h4>Orders List</h4>
                                    <table id="order_table" class="table table-hover mb-0 text-md-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Invoice Number</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Amount</th>
                                                <th> </th>
                                            </tr>
                                        </thead>
                                        <tbody>     
                                        <?php
                            
                                        while ($row_order = mysqli_fetch_assoc($result)) {
                                            $total_amount += $row_order['discounted_price'];
                                            $total_count += 1;
                                        
                                            ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($row_order['orderid']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars(date("F d, Y", strtotime($row_order['order_date']))); ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars(date("h:i A", strtotime($row_order['order_date']))); ?>
                                                </td>
                                                <td>
                                                    $ <?= htmlspecialchars($row_order['discounted_price']) ?>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0);" class="py-1 pe-1 fs-5" id="view_status_details" data-id="<?php echo $row_order["orderid"]; ?>" data-type="<?= $row_order['source_table'] ?>" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                        <tfoot>
                                            <td colspan="3" class="text-end">Total: </td>
                                            <td>$ <?= number_format($total_amount,2) ?></td>
                                            <td></td>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal" id="view_status_details_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 90vw;">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">View Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="status-details">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    var id = '';
    var type = '';

    function loadStatusDetails(){
        $.ajax({
            url: 'pages/job_details_ajax.php',
            type: 'POST',
            data: {
                id: id,
                type: type,
                fetch_status_details: "fetch_status_details"
            },
            success: function(response) {
                $('#status-details').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    

    $(document).ready(function() {
        
        $('[data-toggle="tooltip"]').tooltip();

        $('#approval_table').DataTable({
            language: {
                emptyTable: "No Approvals Found"
            },
            autoWidth: false,
            responsive: true
        });

        $('#estimate_table').DataTable({
            language: {
                emptyTable: "No Estimates Found"
            },
            autoWidth: false,
            responsive: true
        });

        $('#order_table').DataTable({
            language: {
                emptyTable: "No Orders Found"
            },
            autoWidth: false,
            responsive: true
        });

        $(document).on('click', '#view_status_details', function(event) {
            id = $(this).data('id');
            type = $(this).data('type');
            loadStatusDetails();
            $('#view_status_details_modal').modal('toggle');
        });

    });
</script>