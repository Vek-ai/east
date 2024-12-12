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
    ?>
    <div class="widget-content searchable-container list">
        <div class="card card-body">
            <div class="card-body">
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
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Dimensions</th>
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
                                                        <?php echo $row['quantity']; ?>
                                                    </td>
                                                    <td class="text-center">
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
                                                    <td class="text-end">$ <?= number_format($row['actual_price'],2) ?></td>
                                                    <td class="text-end">$ <?= number_format($row['discounted_price'],2) ?></td>
                                                    <td>
                                                        <div class="action-btn text-center">
                                                            <a href="#" class="text-decoration-none" id="viewAvailableBtn" data-color="<?= $row['custom_color'] ?>" data-grade="<?= $row['custom_grade'] ?>" data-product="<?= $row['productid'] ?>">
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
                                            <td class="text-end" colspan="4">Total</td>
                                            <td class="text-center"><?= $totalquantity ?></td>
                                            <td></td>
                                            <td class="text-end">$ <?= number_format($total_actual_price,2) ?></td>
                                            <td class="text-end">$ <?= number_format($total_disc_price,2) ?></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
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
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
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
    });
</script>