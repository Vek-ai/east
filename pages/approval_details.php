<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$approval_id = mysqli_real_escape_string($conn, $_REQUEST['id']);
?>
<style>
    .tooltip-inner {
        background-color: white !important;
        color: black !important;
        font-size: calc(0.875rem + 2px) !important;
    }
</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Approval Details</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=approval_list">Approval List
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Approval Details</li>
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
    <?php
    if(!empty($approval_id)){
        $approval_details = getApprovalDetails($approval_id);
        $customer_id = $approval_details['originalcustomerid'];
        $customer_details = getCustomerDetails($customer_id);
    ?>
    <div class="widget-content searchable-container list">
        <div class="card card-body">
            <div class="card-body">
                <div style="display: flex;" class="pb-2">
                    <div style="flex: 1; padding-right: 20px;">
                        <h4 class="pb-1 mb-1"><?= $customer_details['customer_first_name'] .' ' .$customer_details['customer_last_name'] ?></h4>
                        <h5 class="pb-1 mb-1">Address: <?= getCustomerAddress($customer_id) ?></h5>
                    </div>
                    <div style="flex: 1;">
                        <h5 class="pb-1 mb-1">Contact Number: <?= $customer_details['contact_phone'] ?></h5>
                        <h5 class="pb-1 mb-1">Contact Email: <?= $customer_details['contact_email'] ?></h5>
                    </div>
                </div>
                <div class="row">
                    <div class="datatables">
                        <div id="tbl-approval" class="product-details table-responsive text-nowrap">
                        <div class="card card-body datatables">
                            <div class="product-details table-responsive text-wrap">
                                <h4>Approval Items List</h4>
                                <table id="approval_dtls_tbl" class="table table-hover mb-0 text-md-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Color</th>
                                            <th>Grade</th>
                                            <th>Profile</th>
                                            <th>Width</th>
                                            <th>Length</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Details</th>
                                            <th class="text-center">Price</th>
                                            <th class="text-center">Customer Price</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 0;
                                        $query = "SELECT * FROM approval_product WHERE approval_id='$approval_id'";
                                        $result = mysqli_query($conn, $query);
                                        $totalquantity = $total_actual_price = $total_disc_price = 0;
                                        if ($result && mysqli_num_rows($result) > 0) {
                                            $response = array();
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $color_details = getColorDetails($row['custom_color']);
                                                $product_id = $row['productid'];
                                                $width = $row['custom_width'];
                                                $bend = $row['custom_bend'];
                                                $hem = $row['custom_hem'];
                                                $length = $row['custom_length'];
                                                $inch = $row['custom_length2'];
                                                ?>
                                                <tr data-id="<?= $product_id ?>">
                                                    <td class="text-wrap"> 
                                                        <?php echo getProductName($product_id) ?>
                                                    </td>
                                                    <td>
                                                    <div class="d-inline-flex align-items-center gap-2">
                                                        <a 
                                                            href="javascript:void(0)" 
                                                            id="viewAvailableBtn" 
                                                            data-app-prod-id="<?= $row['id'] ?>" 
                                                            class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                                                <span class="rounded-circle d-block" style="background-color:<?= $color_details['color_code'] ?>; width: 20px; height: 20px;"></span>
                                                                <?= $color_details['color_name'] ?>
                                                        </a>
                                                    </div>
                                                    </td>
                                                    <td>
                                                        <?php echo getGradeName($row['custom_grade']); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo getProfileFromID($product_id); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php 
                                                        if (!empty($width)) {
                                                            echo htmlspecialchars($width);
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php 
                                                        if (!empty($length)) {
                                                            echo htmlspecialchars($length) . " ft";
                                                            
                                                            if (!empty($inch)) {
                                                                echo " " . htmlspecialchars($inch) . " in";
                                                            }
                                                        } elseif (!empty($inch)) {
                                                            echo htmlspecialchars($inch) . " in";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php echo $row['quantity']; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php 
                                                        if (!empty($bend)) {
                                                            echo "Bend: " . htmlspecialchars($bend) . "<br>";
                                                        }
                                                        
                                                        if (!empty($hem)) {
                                                            echo "Hem: " . htmlspecialchars($hem) . "<br>";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="text-end">$<?= number_format($row['actual_price'],2) ?></td>
                                                    <td class="text-end">
                                                        <a 
                                                            href="javascript:void(0)" 
                                                            id="chngPriceAn" 
                                                            data-app-prod-id="<?= $row['id'] ?>" 
                                                            class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                                                <span id='price_<?= $row['id'] ?>'>
                                                                    $<?= number_format($row['discounted_price'],2) ?>
                                                                </span>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="action-btn text-center">
                                                            <a href="#" class="text-decoration-none" id="viewAvailableBtn" data-app-prod-id="<?= $row['id'] ?>">
                                                                <i class="text-white ti ti-eye fs-7"></i>
                                                            </a>
                                                        </div>
                                                    </td>
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
                                            <td class="text-end" colspan="6">Total</td>
                                            <td class="text-center"><?= $totalquantity ?></td>
                                            <td></td>
                                            <td class="text-end">$<?= number_format($total_actual_price,2) ?></td>
                                            <td class="text-end">$<?= number_format($total_disc_price,2) ?></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="d-flex align-items-center justify-content-end gap-3 pt-4">
                                <button class="chng-status btn btn-success btn-md" data-id="<?= $approval_id ?>" data-status="2" type="button">
                                    <span aria-hidden="true">Approve</span>
                                </button>
                                <button class="chng-status btn btn-danger btn-md" data-id="<?= $approval_id ?>" data-status="3" type="button">
                                    <span aria-hidden="true">Reject</span>
                                </button>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }else{
    ?>
    <div class="text-center">
        <h4 class="fw-bold">Invalid Request</h4>
    </div>
    <?php
    }
    ?>

<div class="modal" id="view_available_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Available Coils</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="available-details"></div>
            </div>
            <div class="modal-footer">
                <button id="saveSelection" class="btn ripple btn-success" type="button">Save</button>
                <button class="btn ripple btn-danger" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="chng_price_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content p-2">
            <div class="modal-header pb-1">
                <h6 class="modal-title mb-0">Change Price</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="price-details">
                    <form id="chngPriceForm" autocomplete="false">
                        <input type="hidden" id="approval_product_id" name="approval_product_id" value="">
                        <div class="form-group">
                            <input class="form-control" type="text" id="inpt_price" name="inpt_price" placeholder="Enter new price">
                        </div>
                        <div class="text-center d-flex align-items-center justify-content-center gap-3">
                            <button type="submit" class="btn ripple btn-success btn-secondary">Save</button>
                            <button type="button" class="btn ripple btn-danger btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        $(document).on('click', '#viewAvailableBtn', function(event) {
            var id = $(this).data('app-prod-id');

            $.ajax({
                url: 'pages/approval_details_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_available: 'fetch_available'
                },
                success: function(response) {
                    $('#available-details').html(response);
                    $('#view_available_modal').modal('toggle');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#chngPriceAn', function(event) {
            var id = $(this).data('app-prod-id');
            var price = $(this).text().trim().replace(/[^0-9.]/g, '');
            $('#approval_product_id').val(id);
            $('#inpt_price').val(price);
            $('#chng_price_modal').modal('show');
        });

        $(document).on('click', '.chng-status', function(event) {
            var id = $(this).data('id');
            var status = $(this).data('status');

            $.ajax({
                url: 'pages/approval_details_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    status: status,
                    chng_status: 'chng_status'
                },
                success: function(response) {
                    if (response.trim() == 'success') {
                        if(status == 2){
                            alert('Submission Successfully Approved');
                        }else if(status == 3){
                            alert('Submission Successfully Declined');
                        }else{
                            alert('Operation Failed');
                            console.log("Status: " +status)
                        }
                    } else {
                        alert('Failed to Update!');
                        console.log(response);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('submit', '#chngPriceForm', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            formData.append('chng_price', 'chng_price');
            $.ajax({
                url: 'pages/approval_details_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.trim() == 'success') {
                        alert('Price changed successfully');
                        $('#approval_dtls_tbl').load(location.href + " #approval_dtls_tbl");
                        $('#chng_price_modal').modal('hide');
                    } else {
                        alert('Failed to Update!');
                        console.log(response);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + jqXHR.responseText);
                }
            });
        });


    });
</script>