<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

?>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"> Orders</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Orders</li>
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
        <div class="row">
        <div class="col-md-4 col-xl-3">
            <!-- <form class="position-relative">
            <input type="text" class="form-control product-search ps-5" id="input-search" placeholder="Search Contacts..." />
            <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </form> -->
        </div>
        <div class="col-md-8 col-xl-9 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0">
        <!-- Select Category -->
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
                <th>Coil Name</th>
                <th>Length</th>
                <th>Tag Number</th>
                <th>Added Date</th>
            </tr>
        </thead>
        <tbody id="coilList">
            <!-- Coils data will be loaded here via JavaScript -->
        </tbody>
    </table>
    <div id="calculationResult"></div> <!-- Add a place to show the result of the calculation -->
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


    
    <div class="card card-body">
    <div class="table-responsive">
        <table id="productList" class="table search-table align-middle text-nowrap">
            <thead class="header-item">
            <th>Product Name</th>
            <th>Quantity</th>
            <th> Width</th>
            <th> Bend</th>
            <th> Hem</th>
            <th> Length</th>
            <th>Color </th>
            
            <th>Coils</th>
            <th>Flat Stock</th>
            </thead>
            <tbody>
            <?php
                $prodcat = $_GET['prodcat'];
                $query_products = "
                    SELECT op.productid, p.product_item, SUM(op.quantity) as total_quantity, op.custom_width, op.custom_bend, op.custom_hem, op.custom_length, pc.color_name, pc.color_code,pc.color_id 
                    FROM order_product op 
                    JOIN product p ON op.productid = p.product_id 
                    JOIN paint_colors pc ON p.color = pc.color_id
                    WHERE op.status = 1 AND op.product_category = '$prodcat'
                    GROUP BY op.productid, op.custom_width, op.custom_length
                ";
                $result_products = mysqli_query($conn, $query_products);
                $no = 1;

                while ($row_product = mysqli_fetch_array($result_products)) {
                    ?>
                    <tr class="search-items">
                        <td><?= $row_product['product_item'] ?></td>
                        <td><?= $row_product['total_quantity'] ?></td>
                        <td><?= !empty($row_product['custom_width']) ? $row_product['custom_width'] : 'N/A' ?></td>
                        <td><?= !empty($row_product['custom_bend']) ? $row_product['custom_bend'] : 'N/A' ?></td>
                        <td><?= !empty($row_product['custom_hem']) ? $row_product['custom_hem'] : 'N/A' ?></td>
                        <td><?= !empty($row_product['custom_length']) ? $row_product['custom_length'] : 'N/A' ?></td>
                        <td><?= !empty($row_product['color_name']) ? $row_product['color_name'] : 'N/A' ?>
                        <div style="width: 30px; height: 30px; background-color: <?= $row_product['color_code'] ?>; border: 1px solid #000;"></div></td>
                        <td>
    <div class="action-btn">
        <a href="#" id="view_product_btn" class="text-primary edit" data-id="<?= $row_product['productid'] ?>" data-color="<?= $row_product['color_id'] ?>" data-quantity="<?= $row_product['total_quantity'] ?>" data-custom_length="<?= $row_product['custom_length'] ?>" onclick="openCoilModal(this)">
            <i class="ti ti-eye fs-5"></i>
        </a>
    </div>
</td>
<td>
    <div class="action-btn">
        <a href="#" id="view_product_btn" class="text-primary edit" data-id="<?= $row_product['productid'] ?>" data-color="<?= $row_product['color_id'] ?>" onclick="openFlatModal(this)">
            <i class="ti ti-eye fs-5"></i>
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
            // Update the modal's content
            $('#coilList').html(response);
            // Show the modal
            $('#coilModal').modal('show');
        },
        error: function() {
            alert('Error fetching coil data.');
        }
    });
}
function openFlatModal(element) {   $('#StockModal').modal('show');}

$(document).on('change', '.coil-checkbox', function() {
    var totalProducts = 0;

    // Loop through each checked checkbox
    $('.coil-checkbox:checked').each(function() {
        var coilLength = $(this).data('coil-length');
        var quantity = $(this).data('quantity');
        var customLength = $(this).data('custom-length');

        // Calculate how many products this coil can make
        var productsMade = coilLength / (customLength);

        // Accumulate the total number of products
        totalProducts += productsMade;
    });

    // Display the result
    $('#calculationResult').html('Total Products Made: ' + totalProducts.toFixed(2));
});


</script>



