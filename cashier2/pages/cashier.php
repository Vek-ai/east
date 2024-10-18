<?php
session_start();
require '../includes/dbconn.php';
require '../includes/functions.php';
?>
<style>
    #custom_trim_draw_modal {
        z-index: 1060;
    }

    #custom_trim_draw_modal ~ .modal-backdrop.show {
        z-index: 1055;
    }

    #viewOutOfStockmodal {
        z-index: 11060;
    }

    #viewOutOfStockmodal ~ .modal-backdrop.show {
        z-index: 11055;
    }

    #viewInStockmodal {
        z-index: 11060;
    }

    #viewInStockmodal ~ .modal-backdrop.show {
        z-index: 11055;
    }
</style>
<div class="product-list pt-4">
    <div class="row row-xs pr-3">
        <div class="col-md-8"></div>
            <?php if(isset($_SESSION["grandtotal"])){?>
                <div class="col-md-4 bg-primary" id="thegrandtotal" style="text-align:center;font-size:48px;display: flex;align-items: center;justify-content: center;text-align: center;" >$<?php echo number_format($_SESSION["grandtotal"],2);?> </div>
            <?php }else{ ?>
                <div class="col-md-4 bg-primary" id="thegrandtotal" style="text-align:center;font-size:48px;display: flex;align-items: center;justify-content: center;text-align: center;" >$0.00 </div>
            <?php } ?>
    </div>
    <div class="card">
        <div class="card-body text-right p-3">
            
            <div class="p-2">
                <input type="checkbox" id="toggleActive" checked> Show only In Stock
            </div>
            <div class="d-flex justify-content-between align-items-center  mb-9">
                <div class="position-relative w-100 col-4">
                    <input type="text" class="form-control search-chat py-2 ps-5 " id="text-srh" placeholder="Search Product">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-color" data-category="">
                        <option value="" data-category="">All Colors</option>
                        <optgroup label="Product Colors">
                            <?php
                            $query_color = "SELECT * FROM paint_colors WHERE hidden = '0'";
                            $result_color = mysqli_query($conn, $query_color);
                            while ($row_color = mysqli_fetch_array($result_color)) {
                            ?>
                                <option value="<?= $row_color['color_id'] ?>" data-category="category"><?= $row_color['color_name'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-category" data-category="">
                        <option value="" data-category="">All Categories</option>
                        <optgroup label="Category">
                            <?php
                            $query_category = "SELECT * FROM product_category WHERE hidden = '0'";
                            $result_category = mysqli_query($conn, $query_category);
                            while ($row_category = mysqli_fetch_array($result_category)) {
                            ?>
                                <option value="<?= $row_category['product_category_id'] ?>" data-category="category"><?= $row_category['product_category'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-line" data-category="">
                        <option value="" data-category="">All Product Lines</option>
                        <optgroup label="Product Line">
                            <?php
                            $query_line = "SELECT * FROM product_line WHERE hidden = '0'";
                            $result_line = mysqli_query($conn, $query_line);
                            while ($row_line = mysqli_fetch_array($result_line)) {
                            ?>
                                <option value="<?= $row_line['product_line_id'] ?>" data-category="line"><?= $row_line['product_line'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-type" data-category="">
                        <option value="" data-category="">All Product Types</option>
                        <optgroup label="Product Type">
                            <?php
                            $query_type = "SELECT * FROM product_type WHERE hidden = '0'";
                            $result_type = mysqli_query($conn, $query_type);
                            while ($row_type = mysqli_fetch_array($result_type)) {
                            ?>
                                <option value="<?= $row_type['product_type_id'] ?>" data-category="type"><?= $row_type['product_type'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="table-responsive border rounded">
                <table id="productTable" class="table align-middle text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Products</th>
                            <th scope="col">Color</th>
                            <th scope="col">Type</th>
                            <th scope="col">Line</th>
                            <th scope="col">Category</th>
                            <th scope="col">Status</th>
                            <th scope="col">Price</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody"></tbody>
                </table>
                    
                <div class="d-flex align-items-center justify-content-end py-1">
                    <p class="mb-0 fs-2">Rows per page:</p>
                    <select id="rowsPerPage" class="form-select w-auto ms-0 ms-sm-2 me-8 me-sm-4 py-1 pe-7 ps-2 border-0" aria-label="Rows per page">
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                    </select>
                    <p id="paginationInfo" class="mb-0 fs-2"></p>
                    <nav aria-label="...">
                        <ul id="paginationControls" class="pagination justify-content-center mb-0 ms-8 ms-sm-9">
                            <!-- Pagination buttons will be inserted here by JS -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="d-flex justify-content-start">
                    <button class="btn btn-primary mb-2 me-2" type="button" id="view_est_list">
                        <i class="fa fa-save fs-4 me-2"></i>
                        View Estimates
                    </button>
                </div>
            </div>
            <div class="col-6">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary mb-2 me-2" type="button" id="view_cart">
                        <i class="fa fa-shopping-cart fs-4 me-2"></i>
                        Cart
                    </button>
                    <button type="button" class="btn btn-primary d-flex align-items-center mb-2 me-2" id="view_estimate">
                        <i class="fa fa-save fs-4 me-2"></i>
                        Estimate
                    </button>
                    <button type="button" class="btn btn-success d-flex align-items-center mb-2 me-2" id="view_order">
                        <i class="fa fa-shopping-cart fs-4 me-2"></i>
                        Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="custom_trim_draw_modal">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Draw Custom Trim</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="drawing-body">

                </div>
            </div>
            <div class="modal-footer">
                <button id="saveDrawing" class="btn ripple btn-success" type="button">Save</button>
                <button id="clearButton" class="btn ripple btn-warning" type="button">Reset</button>
                <button class="btn ripple btn-danger" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_cart_modal">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Cart Contents</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="cart-tbl">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_est_list_modal">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Estimates List</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="estimates-tbl">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_est_details_modal">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Estimate Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="estimates-details">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="view_estimate_modal">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Save Estimate</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group col-4">
                    <div id="customer_est_section">
                    <?php 
                        if(!empty($_SESSION["customer_id"])){
                        ?>
                        <div class="form-group">
                            <label>Customer Name: <?= get_customer_name($_SESSION["customer_id"]);?></label>
                            <button class="btn ripple btn-primary" type="button" id="customer_change_estimate" ><i class="fe fe-reload"></i> Change</button>                                       
                        </div>
                        <?php } else {?>
                        <label>Customer Name</label>
                        <div class="input-group">
                            <input class="form-control" placeholder="Search Customer" type="text" id="customer_select_estimate">
                            <a class="input-group-text rounded-right m-0 p-0" href="/cashier/?page=customer" target="_blank">
                                <span class="input-group-text"> + </span>
                            </a>
                        </div>
                    <?php } ?>
                    </div>
                    <input type='hidden' id='customer_id_estimate' name="customer_id"/>
                </div>
                <div id="estimate-tbl"></div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary d-flex align-items-center mb-2 me-2" id="save_estimate">
                    <i class="fa fa-save fs-4 me-2"></i>
                    Save
                </button>
                <a href="#" class="btn ripple btn-success d-none" type="button" id="print_estimate_category" target="_blank">
                    <i class="fe fe-print"></i> Print Details
                </a>
                <a href="#" class="btn ripple btn-warning d-none" type="button" id="print_estimate" target="_blank">
                    <i class="fe fe-print"></i> Print Total
                </a>
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="viewDetailsModal"></div>

<div class="modal" id="viewInStockmodal"></div>

<div class="modal" id="viewOutOfStockmodal"></div>

<div class="modal" id="viewAvailablemodal"></div>

<div class="modal" id="cashmodal">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Save Order</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group col-6">
                    <div id="customer_cash_section">
                        <?php 
                        if(!empty($_SESSION["customer_id"])){
                        ?>
                        <div class="form-group">
                            <label>Customer Name: <?= get_customer_name($_SESSION["customer_id"]);?></label>
                            <button class="btn ripple btn-primary" type="button" id="customer_change_cash" ><i class="fe fe-reload"></i> Change</button>                                       
                        </div>
                        <?php } else {?>
                        <label>Customer Name</label>
                        <div class="input-group">
                            <input class="form-control" placeholder="Search Customer" type="text" id="customer_select_cash">
                            <a class="input-group-text rounded-right m-0 p-0" href="/cashier/?page=customer" target="_blank">
                                <span class="input-group-text"> + </span>
                            </a>
                        </div>
                    <?php } ?>
                    </div>
                    <input type='hidden' id='customer_id_cash' name="customer_id"/>
                </div>
                <div id="order-tbl">
                    
                </div>
                
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="save_order">
                    <i class="fe fe-hard-drive"></i> Save
                </button>
                <a href="#" class="btn ripple btn-success d-none" type="button" id="print_order_category" target="_blank">
                    <i class="fe fe-print"></i> Print Details
                </a>
                <a href="#" class="btn ripple btn-warning d-none" type="button" id="print_order" target="_blank">
                    <i class="fe fe-print"></i> Print Total
                </a>
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function updateEstimateBend(element){
        var bend = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                bend: bend,
                id: id,
                line: line,
                set_estimate_bend: "set_estimate_bend"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateHem(element){
        var hem = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                hem: hem,
                id: id,
                line: line,
                set_estimate_hem: "set_estimate_hem"
            },
            success: function(response) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateLength(element){
        var length = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                length: length,
                id: id,
                line: line,
                set_estimate_length: "set_estimate_length"
            },
            success: function(response) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateLengthInch(element){
        var length_inch = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                length_inch: length_inch,
                id: id,
                line: line,
                set_estimate_length_inch: "set_estimate_length_inch"
            },
            success: function(response) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateHeight(element){
        var height = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                height: height,
                id: id,
                line: line,
                set_estimate_height: "set_estimate_height"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateEstimateWidth(element){
        var width = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');
        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                width: width,
                id: id,
                line: line,
                set_estimate_width: "set_estimate_width"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updateUsage(element){
        var usage = $(element).val();
        var id = $(element).data('id');
        var line = $(element).data('line');
        $.ajax({
            url: 'pages/cashier_ajax.php',
            type: 'POST',
            data: {
                usage: usage,
                id: id,
                line: line,
                set_usage: "set_usage"
            },
            success: function(response) {
                console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadDrawingModal(element){
        var id = $(element).data('id');
        var line = $(element).data('line');
        $.ajax({
            url: 'pages/cashier_drawing_modal.php',
            type: 'POST',
            data: {
                id: id,
                line: line,
                fetch_drawing: "fetch_drawing"
            },
            success: function(response) {
                $('#drawing-body').html(response);

                initializeDrawingApp();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadEstimatesList(){
        $.ajax({
            url: 'pages/cashier_est_list_modal.php',
            type: 'POST',
            data: {
                fetch_est_list: "fetch_est_list"
            },
            success: function(response) {
                $('#estimates-tbl').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadEstimatesDetails(estimate_id){
        $.ajax({
            url: 'pages/cashier_est_details_modal.php',
            type: 'POST',
            data: {
                estimateid: estimate_id,
                fetch_est_details: "fetch_est_details"
            },
            success: function(response) {
                $('#estimates-details').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadCart(){      
        $.ajax({
            url: 'pages/cashier_cart_modal.php',
            type: 'POST',
            data: {
                fetch_cart: "fetch_cart"
            },
            success: function(response) {
                $('#cart-tbl').html(''); 
                $('#cart-tbl').html(response); 
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadOrderContents(){
        $.ajax({
            url: 'pages/cashier_order_modal.php',
            type: 'POST',
            data: {
                fetch_order: "fetch_order"
            },
            success: function(response) {
                $('#order-tbl').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadEstimateContents(){
        $.ajax({
            url: 'pages/cashier_estimate_modal.php',
            type: 'POST',
            data: {
                fetch_estimate: "fetch_estimate"
            },
            success: function(response) {
                $('#estimate-tbl').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }
    
    function addtocart(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: "pages/cashier_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                modifyquantity: 'modifyquantity',
                addquantity: 'addquantity'
            },
            success: function(data) {
                console.log(data);
                loadCart();
                loadOrderContents();
                loadEstimateContents();
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function updatequantity(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');
        var qty = $(element).val();
        $.ajax({
            url: "pages/cashier_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                qty: qty,
                modifyquantity: 'modifyquantity',
                setquantity: 'setquantity'
            },
            success: function(data) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function addquantity(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var quantity = Number(input_quantity.val());
        $.ajax({
            url: "pages/cashier_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                quantity: quantity,
                modifyquantity: 'modifyquantity',
                addquantity: 'addquantity'
            },
            success: function(data) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
                
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function deductquantity(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var quantity = Number(input_quantity.val());
        $.ajax({
            url: "pages/cashier_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                quantity: quantity,
                modifyquantity: 'modifyquantity',
                deductquantity: 'deductquantity'
            },
            success: function(data) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function delete_item(element) {
        var id = $(element).data('id');
        var line = $(element).data('line');
        $.ajax({
            url: "pages/cashier_ajax.php",
            data: {
                product_id_del: id,
                line: line,
                deleteitem: 'deleteitem'
            },
            type: "POST",
            success: function(data) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
            },
            error: function() {}
        });
    }

    function duplicate_item(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');
        $.ajax({
            url: "pages/cashier_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                modifyquantity: 'modifyquantity',
                duplicate_product: 'duplicate_product'
            },
            success: function(data) {
                loadCart();
                loadOrderContents();
                loadEstimateContents();
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function initializeDrawingApp() {
        const canvas = document.getElementById('drawingCanvas');
        const ctx = canvas.getContext('2d');
        const totalLengthDiv = document.getElementById('totalLength');
        const totalCostDiv = document.getElementById('totalCost');
        const lengthAnglePairs = document.getElementById('lengthAnglePairs');
        const clearButton = document.getElementById('clearButton');
        const saveDrawing = document.getElementById('saveDrawing');

        let points = [];
        let lengths = [];
        let angles = [];
        let colors = [];
        let currentStartPoint = null;
        const pixelsPerInch = 96;

        const colorPrices = {
            black: 1.9,
            red: 2.0,
            green: 2.1,
            blue: 2.3,
            yellow: 2.5
        };

        function drawPlaceholderText() {
            ctx.font = "30px Arial";
            ctx.fillStyle = "lightgray";
            ctx.textAlign = "center";
            ctx.fillText("Draw here", canvas.width / 2, canvas.height / 2);
        }

        function drawLine(point1, point2, color) {
            ctx.beginPath();
            ctx.moveTo(point1.x, point1.y);
            ctx.lineTo(point2.x, point2.y);
            ctx.strokeStyle = color;
            ctx.stroke();
        }

        function drawTemporaryLine(point1, point2) {
            ctx.beginPath();
            ctx.moveTo(point1.x, point1.y);
            ctx.lineTo(point2.x, point2.y);
            ctx.strokeStyle = 'gray';
            ctx.stroke();
        }

        function calculateDistance(point1, point2) {
            const distanceInPixels = Math.sqrt(Math.pow(point2.x - point1.x, 2) + Math.pow(point2.y - point1.y, 2));
            return (distanceInPixels / pixelsPerInch).toFixed(2);
        }

        function calculateInteriorAngle(p1, p2, p3) {
            const angle = Math.atan2(p3.y - p2.y, p3.x - p2.x) - Math.atan2(p1.y - p2.y, p1.x - p2.x);
            let degrees = (angle * 180 / Math.PI) % 360;
            if (degrees < 0) {
                degrees += 360;
            }
            if (degrees > 180) {
                degrees = 360 - degrees;
            }
            return degrees;
        }

        function drawAngleArc(p1, p2, p3, angle) {
            const radius = 30;
            const startAngle = Math.atan2(p1.y - p2.y, p1.x - p2.x);
            const endAngle = Math.atan2(p3.y - p2.y, p3.x - p2.x);

            ctx.beginPath();
            ctx.arc(p2.x, p2.y, radius, startAngle, endAngle, endAngle < startAngle);
            ctx.strokeStyle = 'red';
            ctx.stroke();
        }

        function updateLengthAnglePairs() {
            lengthAnglePairs.innerHTML = '';
            let totalLength = 0;
            let totalCost = 0;

            lengths.forEach((length, index) => {
                const pair = document.createElement('div');
                pair.classList.add('length-angle-pair');

                const lengthDiv = document.createElement('div');
                lengthDiv.textContent = `Line ${index + 1}: ${length} inches`;
                totalLength += parseFloat(length);

                pair.appendChild(lengthDiv);

                if (index < angles.length) {
                    const angleInput = document.createElement('input');
                    angleInput.type = 'number';
                    angleInput.value = angles[index].toFixed(2);
                    angleInput.addEventListener('change', (e) => {
                        const newAngle = parseFloat(e.target.value);
                        angles[index] = newAngle;
                        const newPoint = calculateNewPoint(points[index - 1], points[index], lengths[index], newAngle);
                        points[index + 1] = newPoint;
                        redrawCanvas();
                    });

                    const angleLabel = document.createElement('label');
                    angleLabel.textContent = 'Angle (°): ';
                    pair.appendChild(angleLabel);
                    pair.appendChild(angleInput);
                }

                const colorSelect = document.createElement('select');
                const colorsOptions = Object.keys(colorPrices);
                colorsOptions.forEach((color) => {
                    const option = document.createElement('option');
                    option.value = color;
                    option.textContent = color.charAt(0).toUpperCase() + color.slice(1);
                    colorSelect.appendChild(option);
                });
                colorSelect.value = colors[index];
                colorSelect.addEventListener('change', (e) => {
                    colors[index] = e.target.value;
                    updateLengthAnglePairs();
                    redrawCanvas();
                });

                pair.appendChild(colorSelect);

                const price = colorPrices[colors[index]];
                const lineTotal = parseFloat(length) * price;
                totalCost += lineTotal;

                const priceDiv = document.createElement('div');
                priceDiv.textContent = `Price: $${price.toFixed(2)}`;
                pair.appendChild(priceDiv);

                const totalDiv = document.createElement('div');
                totalDiv.textContent = `Total: $${lineTotal.toFixed(2)}`;
                pair.appendChild(totalDiv);

                lengthAnglePairs.appendChild(pair);
            });

            totalLengthDiv.textContent = `Total Length: ${totalLength.toFixed(2)} inches`;
            totalCostDiv.textContent = `Total Cost: $${totalCost.toFixed(2)}`;
        }

        function calculateNewPoint(p1, p2, length, angle) {
            const radians = (angle * Math.PI) / 180;
            const dx = length * Math.cos(radians);
            const dy = length * Math.sin(radians);
            return { x: p2.x + dx, y: p2.y + dy };
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        function redrawCanvas() {
            clearCanvas();
            for (let i = 1; i < points.length; i++) {
                drawLine(points[i - 1], points[i], colors[i - 1]);
            }
            for (let i = 2; i < points.length; i++) {
                drawAngleArc(points[i - 2], points[i - 1], points[i], angles[i - 2]);
            }
            if (points.length === 0) {
                drawPlaceholderText();
            }
        }

        canvas.addEventListener('click', (e) => {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            let selectedPoint = { x, y };

            for (let point of points) {
                if (Math.hypot(point.x - x, point.y - y) < 5) {
                    selectedPoint = point;
                    break;
                }
            }

            if (currentStartPoint) {
                points.push(selectedPoint);
                colors.push('black');
                drawLine(currentStartPoint, selectedPoint, 'black');
                const length = calculateDistance(currentStartPoint, selectedPoint);
                lengths.push(length);
                if (points.length > 2) {
                    const angle = calculateInteriorAngle(points[points.length - 3], points[points.length - 2], points[points.length - 1]);
                    angles.push(angle);
                    drawAngleArc(points[points.length - 3], points[points.length - 2], points[points.length - 1], angle);
                }
                updateLengthAnglePairs();
                currentStartPoint = null;
            } else {
                currentStartPoint = selectedPoint;
                if (!points.includes(selectedPoint)) {
                    points.push(selectedPoint);
                }
            }
        });

        canvas.addEventListener('mousemove', (e) => {
            if (currentStartPoint) {
                const rect = canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                redrawCanvas();
                drawTemporaryLine(currentStartPoint, { x, y });
            }
        });

        clearButton.addEventListener('click', () => {
            clearCanvas();
            points = [];
            lengths = [];
            angles = [];
            colors = [];
            currentStartPoint = null;
            updateLengthAnglePairs();
            drawPlaceholderText();
        });

        saveDrawing.addEventListener('click', () => {
            var isSave = confirm("Are you sure you want to finalize your custom trim?");
            
            if (isSave) {
                const canvasDrawn = $('#drawingCanvas')[0];
                const image_data = canvasDrawn.toDataURL('image/png');

                const id = $('#custom_trim_id').val();
                const line = $('#custom_trim_line').val();

                $.ajax({
                    url: 'pages/cashier_ajax.php',
                    type: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    data: JSON.stringify({
                        image_data: image_data,
                        save_drawing: 'save_drawing',
                        id: id,
                        line: line
                    }),
                    success: function(response) {
                        if (response.filename) {
                            loadCart();
                            loadOrderContents();
                            loadEstimateContents();
                            $('#custom_trim_draw_modal').modal('hide');
                        } else {
                            console.log("Error: " + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("Error: " + xhr.responseText);
                    }
                });
            }
        });

        drawPlaceholderText();
    }

    $("#customer_select_estimate").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/cashier_ajax.php",
                type: 'post',
                dataType: "json",
                data: {
                    search_customer: request.term
                },
                success: function(data) {
                    response(data);
                },
                error: function(xhr, status, error) {
                    console.log("Error: " + xhr.responseText);
                }
            });
        },
        select: function(event, ui) {
            $('#customer_select_estimate').val(ui.item.label);
            $('#customer_id_estimate').val(ui.item.value);
            return false;
        },
        focus: function(event, ui) {
            $('#customer_select_estimate').val(ui.item.label);
            return false;
        },
        appendTo: "#view_estimate_modal", 
        open: function() {
            $(".ui-autocomplete").css("z-index", 1050);
        }
    });

    $("#customer_select_cash").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/cashier_ajax.php",
                type: 'post',
                dataType: "json",
                data: {
                    search_customer: request.term
                },
                success: function(data) {
                    response(data);
                },
                error: function(xhr, status, error) {
                    console.log("Error: " + xhr.responseText);
                }
            });
        },
        select: function(event, ui) {
            $('#customer_select_cash').val(ui.item.label);
            $('#customer_id_cash').val(ui.item.value);
            return false;
        },
        focus: function(event, ui) {
            $('#customer_select_cash').val(ui.item.label);
            return false;
        },
        appendTo: "#cashmodal", 
        open: function() {
            $(".ui-autocomplete").css("z-index", 1050);
        }
    });

    $(document).ready(function() {
        var currentPage = 1,
            rowsPerPage = parseInt($('#rowsPerPage').val()),
            totalRows = 0,
            totalPages = 0,
            maxPageButtons = 5,
            stepSize = 5;

        function updateTable() {
            var $rows = $('#productTableBody tr');
            totalRows = $rows.length;
            totalPages = Math.ceil(totalRows / rowsPerPage);

            var start = (currentPage - 1) * rowsPerPage,
                end = Math.min(currentPage * rowsPerPage, totalRows);

            $rows.hide().slice(start, end).show();

            $('#paginationControls').html(generatePagination());
            $('#paginationInfo').text(`${start + 1}–${end} of ${totalRows}`);

            $('#paginationControls').find('a').click(function(e) {
                e.preventDefault();
                if ($(this).hasClass('page-link-next')) {
                    currentPage = Math.min(currentPage + stepSize, totalPages);
                } else if ($(this).hasClass('page-link-prev')) {
                    currentPage = Math.max(currentPage - stepSize, 1);
                } else {
                    currentPage = parseInt($(this).text());
                }
                updateTable();
            });
        }

        function generatePagination() {
            var pagination = '';
            var startPage = Math.max(1, currentPage - Math.floor(maxPageButtons / 2));
            var endPage = Math.min(totalPages, startPage + maxPageButtons - 1);

            if (currentPage > 1) {
                pagination += `<li class="page-item p-1"><a class="page-link border-0 rounded-circle text-dark fs-6 round-32 d-flex align-items-center justify-content-center page-link-prev" href="#">‹</a></li>`;
            }

            for (var i = startPage; i <= endPage; i++) {
                pagination += `<li class="page-item p-1 ${i === currentPage ? 'active' : ''}"><a class="page-link border-0 rounded-circle text-dark fs-6 round-32 d-flex align-items-center justify-content-center" href="#">${i}</a></li>`;
            }

            if (currentPage < totalPages) {
                pagination += `<li class="page-item p-1"><a class="page-link border-0 rounded-circle text-dark fs-6 round-32 d-flex align-items-center justify-content-center page-link-next" href="#">›</a></li>`;
            }

            return pagination;
        }

        function performSearch(query) {
            var color_id = $('#select-color').find('option:selected').val();
            var type_id = $('#select-type').find('option:selected').val();
            var line_id = $('#select-line').find('option:selected').val();
            var category_id = $('#select-category').find('option:selected').val();
            var onlyInStock = $('#toggleActive').prop('checked');
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    query: query,
                    color_id: color_id,
                    type_id: type_id,
                    line_id: line_id,
                    category_id: category_id,
                    onlyInStock: onlyInStock
                },
                success: function(response) {
                    $('#productTableBody').html(response);
                    currentPage = 1;
                    updateTable();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }

        $(document).on('click', '#save_estimate', function(event) {
            var discount = $('#est_discount').val();
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    discount: discount,
                    save_estimate: 'save_estimate'
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        alert("Estimate successfully saved.");
                        $('#print_estimate_category').attr('href', '/print_estimate_product.php?id=' + response.estimate_id);
                        $('#print_estimate_category').removeClass('d-none');
                        $('#print_estimate').attr('href', '/print_estimate_total.php?id=' + response.estimate_id);
                        $('#print_estimate').removeClass('d-none');
                    } else if (response.error) {
                        alert("Error: " + response.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#load_estimate', function(event) {
            var id = $(this).data('id');
            console.log(id);
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    load_estimate: 'load_estimate'
                },
                success: function(response) {
                    if (response.success) {
                        loadOrderContents();
                        $('#cashmodal').modal('show');
                    } else if (response.error) {
                        alert("Error: " + response.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#save_order', function(event) {
            var discount = $('#order_discount').val();
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    discount: discount,
                    save_order: 'save_order'
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        alert("Order successfully saved.");
                        $('#print_order_category').attr('href', '/print_order_product.php?id=' + response.order_id);
                        $('#print_order').attr('href', '/print_order_total.php?id=' + response.order_id);
                        $('#print_order_category').removeClass('d-none');
                        $('#print_order').removeClass('d-none');
                    } else if (response.error) {
                        alert("Error: " + response.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Response Text: ' + jqXHR.responseText);
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });


        $(document).on('click', '#view_product_details', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                url: 'pages/cashier_prod_details_modal.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_details_modal: "fetch_details_modal"
                },
                success: function(response) {
                    $('#viewDetailsModal').html(response);
                    $('#viewDetailsModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('change', '#customer_select_estimate', function(event) {
            var customer_id = $('#customer_id_estimate').val();
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    customer_id: customer_id,
                    change_customer: "change_customer"
                },
                success: function(response) {
                    if (response.trim() == 'success') {
                        $('#customer_est_section').load(location.href + " #customer_est_section");
                        loadOrderContents();
                        loadEstimateContents();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('change', '#customer_select_cash', function(event) {
            var customer_id = $('#customer_id_cash').val();
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    customer_id: customer_id,
                    change_customer: "change_customer"
                },
                success: function(response) {
                    if (response.trim() == 'success') {
                        $('#customer_cash_section').load(location.href + " #customer_cash_section");
                        loadOrderContents();
                        loadEstimateContents();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#customer_change_cash', function(event) {
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    unset_customer: "unset_customer"
                },
                success: function(response) {
                    $('#customer_cash_section').load(location.href + " #customer_cash_section", function() {
                        $.getScript("https://code.jquery.com/ui/1.12.1/jquery-ui.min.js", function() {
                            $("#customer_select_cash").autocomplete({
                                source: function(request, response) {
                                    $.ajax({
                                        url: "pages/cashier_ajax.php",
                                        type: 'post',
                                        dataType: "json",
                                        data: {
                                            search_customer: request.term
                                        },
                                        success: function(data) {
                                            response(data);
                                        },
                                        error: function(xhr, status, error) {
                                            console.log("Error: " + xhr.responseText);
                                        }
                                    });
                                },
                                select: function(event, ui) {
                                    $('#customer_select_cash').val(ui.item.label);
                                    $('#customer_id_cash').val(ui.item.value);
                                    return false;
                                },
                                focus: function(event, ui) {
                                    $('#customer_select_cash').val(ui.item.label);
                                    return false;
                                },
                                appendTo: "#cashmodal",
                                open: function() {
                                    $(".ui-autocomplete").css("z-index", 1050);
                                }
                            });
                        });
                    });
                    loadOrderContents();
                    loadEstimateContents();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#customer_change_estimate', function(event) {
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    unset_customer: "unset_customer"
                },
                success: function(response) { 
                    $('#customer_est_section').load(location.href + " #customer_est_section", function() {
                        $.getScript("https://code.jquery.com/ui/1.12.1/jquery-ui.min.js", function() {
                            $("#customer_select_estimate").autocomplete({
                                source: function(request, response) {
                                    $.ajax({
                                        url: "pages/cashier_ajax.php",
                                        type: 'post',
                                        dataType: "json",
                                        data: {
                                            search_customer: request.term
                                        },
                                        success: function(data) {
                                            response(data);
                                        },
                                        error: function(xhr, status, error) {
                                            console.log("Error: " + xhr.responseText);
                                        }
                                    });
                                },
                                select: function(event, ui) {
                                    $('#customer_select_estimate').val(ui.item.label);
                                    $('#customer_id_estimate').val(ui.item.value);
                                    return false;
                                },
                                focus: function(event, ui) {
                                    $('#customer_select_estimate').val(ui.item.label);
                                    return false;
                                },
                                appendTo: "#view_estimate_modal", 
                                open: function() {
                                    $(".ui-autocomplete").css("z-index", 1050);
                                }
                            });
                        });
                        loadOrderContents();
                        loadEstimateContents();
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });


        $(document).on('click', '#custom_trim_draw', function(event) {
            loadDrawingModal(this);
            $('#custom_trim_draw_modal').modal('show');
        });

        $(document).on('click', '#view_cart', function(event) {
            loadCart();
            $('#view_cart_modal').modal('show');
        });

        $(document).on('click', '#view_est_list', function(event) {
            loadEstimatesList();
            $('#view_est_list_modal').modal('show');
        });

        $(document).on('click', '#view_est_details', function(event) {
            var estimate_id = $(this).data('id');
            loadEstimatesDetails(estimate_id);
            $('#view_est_details_modal').modal('show');
        });

        $(document).on('click', '#view_estimate', function(event) {
            loadEstimateContents();
            $('#view_estimate_modal').modal('show');
        });

        $(document).on('click', '#view_order', function(event) {
            loadOrderContents();
            $('#cashmodal').modal('show');
        });

        $(document).on('click', '#view_in_stock', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/cashier_in_stock_modal.php',
                    type: 'POST',
                    data: {
                        id: id,
                        fetch_in_stock_modal: "fetch_in_stock_modal"
                    },
                    success: function(response) {
                        $('#viewInStockmodal').html(response);
                        $('#viewInStockmodal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('click', '#view_out_of_stock', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/cashier_out_of_stock_modal.php',
                    type: 'POST',
                    data: {
                        id: id,
                        fetch_out_of_stock_modal: "fetch_out_of_stock_modal"
                    },
                    success: function(response) {
                        $('#viewOutOfStockmodal').html(response);
                        $('#viewOutOfStockmodal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('click', '#view_available', function(event) {
            event.preventDefault();
            var color = $(this).data('color');
            var width = $(this).data('width');
            console.log("Color: " +color +" Width: " +width);
            $.ajax({
                    url: 'pages/cashier_available_modal.php',
                    type: 'POST',
                    data: {
                        color: color,
                        width: width,
                        fetch_available: "fetch_available"
                    },
                    success: function(response) {
                        $('#viewAvailablemodal').html(response);
                        $('#viewAvailablemodal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $('#rowsPerPage').change(function() {
            rowsPerPage = parseInt($(this).val());
            currentPage = 1;
            updateTable();
        });


        $(document).on('input change', '#text-srh, #select-category, #select-type, #select-line', function() {
            performSearch($('#text-srh').val());
        });

        $('#select-color').select2();
        $('#select-type').select2();
        $('#select-line').select2();
        $('#select-category').select2();

        $(document).on('input change', '#text-srh, #select-color, #select-category, #select-type, #select-line, #toggleActive', function() {
            performSearch($('#text-srh').val());
        });

        performSearch('');
        updateTable(); // Initial table update
    });
</script>
