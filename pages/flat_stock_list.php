<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

?>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Flat Stock List</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Flat Stock
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Flat Stock List</li>
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

    <div class="modal fade" id="coilModal" tabindex="-1" aria-labelledby="coilModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="coilModalLabel">Available Coils</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
    <table class="table">
        <thead>
            <tr>
                <th>Select</th> <!-- Add header for the checkbox -->
                <th>Name</th>
                <th>Length X Width</th>
                <th>Tag Number</th>
                <th>Manufactured Date</th>
            </tr>
        </thead>
        <tbody id="flatStockList" class='text-center'>
            <!-- Coils data will be loaded here via JavaScript -->
        </tbody>
    </table>
</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="StockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="StockModalLabel">Available Flat Stocks</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
    <table class="table">
        <thead>
            <tr>
                <th>Select</th> <!-- Add header for the checkbox -->
                <th>Name</th>
                <th>Length X Width</th>
                <th>Tag Number</th>
                <th>Manufactured Date</th>
            </tr>
        </thead>
        <tbody>
            <!-- Coils data will be loaded here via JavaScript -->
        </tbody>
    </table>
    <!-- Add a place to show the result of the calculation -->
</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


    
    <div class="card card-body datatables">
    <div class="table-responsive">
        <table id="flatStockTbl" class="table search-table align-middle text-nowrap">
            <thead class="header-item">
                <th></th>
                <th>Color</th>
                <th>Width</th>
                <th>Length</th>
                <th>Quantity</th>
                <th>Notes</th>
                <th>Added Date</th>
                <th></th>
            </thead>
            <tbody>
            <?php
                $no = 1;
                $query_flat_stock = "SELECT * FROM flat_stock";
                $result_flat_stock = mysqli_query($conn, $query_flat_stock);
                while ($row_flat_stock = mysqli_fetch_array($result_flat_stock)) {
                    ?>
                    <tr class="search-items">
                        <td><?= $no ?></td>
                        <td>
                            <?= !empty($row_flat_stock['color']) ? getColorName($row_flat_stock['color']) : 'N/A' ?>
                            <div style="width: 30px; height: 30px; background-color: <?= getColorHexFromColorID($row_flat_stock['color']) ?>; border: 1px solid #000;">
                            </div>
                        </td>
                        <td><?= $row_flat_stock['width'] ?></td>
                        <td><?= $row_flat_stock['length'] ?></td>
                        <td><?= $row_flat_stock['quantity'] ?></td>
                        <td><?= $row_flat_stock['notes'] ?></td>
                        <td><?= date("F d, Y", strtotime($row_flat_stock['date_added'])) ?></td>
                        <td>
                            <div class="action-btn">
                                <a href="#" id="view_product_btn" class="text-primary edit" 
                                    data-id="<?= $row_flat_stock['id'] ?>" 
                                    data-color="<?= $row_flat_stock['color'] ?>" 
                                    data-quantity="<?= $row_flat_stock['quantity'] ?>" 
                                    data-custom_length="<?= $row_flat_stock['length'] ?>"  
                                    onclick="openCoilModal(this)">
                                        <i class="ti ti-eye fs-5"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php 
                $no++;
                } 
                ?>
            </tbody>
        </table>
    </div>
</div>



    
</div>

</div>

    </div>
</div>

<script>
 function openCoilModal(element) {
    var colorCode = $(element).data('color');
    var quantity = $(element).data('quantity');
    var customLength = $(element).data('custom_length');


    // Make an AJAX request to fetch coils with the same color
    $.ajax({
        url: 'pages/fetch_coils.php', // Your PHP script to fetch coils
        method: 'POST',
        data: { 
            color_code: colorCode,
            quantity: quantity,
            custom_length: customLength 
        },
        success: function(response) {
            
    console.log(colorCode);
    console.log(quantity);
    console.log(customLength);
            // Update the modal's content
            $('#flatStockList').html(response);
            // Show the modal
            $('#coilModal').modal('show');
        },
        error: function() {
            alert('Error fetching coil data.');
        }
    });
}
function openFlatModal(element) {   $('#StockModal').modal('show');}

$(document).ready(function() {
    $('#flatStockTbl').DataTable();
});
</script>



