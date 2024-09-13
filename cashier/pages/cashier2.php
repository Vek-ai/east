<?php
require '../includes/dbconn.php';
require '../includes/functions.php';
?>

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
                <input type="checkbox" id="toggleActive" checked> Exclude Out of Stock</div>
            </div>
            <div class="d-flex justify-content-between align-items-center  mb-9">
                <div class="position-relative w-100 col-6">
                    <input type="text" class="form-control search-chat py-2 ps-5 " id="text-srh" placeholder="Search Product">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
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
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary me-2" type="button" id="view_cart">Cart</button>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#checkoutModal">Checkout</button>
        </div>
    </div>
</div>

<div class="modal" id="view_cart_modal"></div>

<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkoutModalLabel">Checkout Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-primary d-flex align-items-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#estimateModal">
                        <i class="fa fa-save fs-4 me-2"></i>
                        Estimate
                    </button>
                    <span class="align-self-center px-4">OR</span>
                    <button type="button" class="btn btn-success d-flex align-items-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#cashmodal">
                        <i class="fa fa-shopping-cart fs-4 me-2"></i>
                        Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="cashmodal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Cash Payment</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="checkout">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card box-shadow-0">
                                <div class="card-body">
                                    <form>
                                        <div class="form-group">
                                            <label>Customer Name</label>
                                            <div class="input-group">
                                                <input class="form-control" placeholder="Search Customer" type="text" id="customer_select_cash">
                                                    <a class="input-group-text rounded-right m-0 p-0" href="/cashier/?page=customer" target="_blank">
                                                        <span class="input-group-text"> + </span>
                                                    </a>
                                                <input type='hidden' id='customer_id_cash' name="customer_id"/>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Amount</label>
                                            <input type="text" class="form-control" id="cash_amount" onchange="update_cash()" value="<?= $_SESSION["grandtotal"] ?>">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body pricing">
                                    <ul class="list-unstyled leading-loose">
                                        <li><strong>Total Items: </strong><span id="total_items"><?= $_SESSION["total_quantity"] ?? '0' ?></span></li>
                                        <li><strong>Total: </strong>$ <span id="total_amt"> <?= $_SESSION["grandtotal"] ?? '0.00' ?></span></li>
                                        <li><strong>Discount(-): </strong>$ <span id="total_discount">0.00</span></li>
                                        <li><strong>Total Payable: </strong>$ <span id="total_payable"> <?= $_SESSION["grandtotal"] ?> </span></li>
                                        <li class="list-group-item border-bottom-0 bg-primary" style="font-size:30px;">
                                            <strong>Change: </strong>$ 0.00
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="savecash" onclick="savecash()">
                    <i class="fe fe-hard-drive"></i> Save
                </button>
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function addtocart(element) {
        var product_id = $(element).data('id');

        $.ajax({
            url: "pages/cashier2_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                modifyquantity: 'modifyquantity',
                addquantity: 'addquantity'
            },
            success: function(data) {
                loadCart();
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
                $("#checkout").load(location.href + " #checkout");
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

    function loadCart(){
        $.ajax({
            url: 'pages/cashier2_ajax.php',
            type: 'POST',
            data: {
                fetch_cart: "fetch_cart"
            },
            success: function(response) {
                $('#view_cart_modal').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function updatequantity(element) {
        var product_id = $(element).data('id');
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var qty = Number(input_quantity.val());

        $.ajax({
            url: "pages/cashier2_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                qty: qty,
                modifyquantity: 'modifyquantity',
                setquantity: 'setquantity'
            },
            success: function(data) {
                var updatedQuantity = Number(data);
                input_quantity.val(updatedQuantity);
                $("#view_cart").click();
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
                $("#checkout").load(location.href + " #checkout");
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
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var quantity = Number(input_quantity.val());

        $.ajax({
            url: "pages/cashier2_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                quantity: quantity,
                modifyquantity: 'modifyquantity',
                addquantity: 'addquantity'
            },
            success: function(data) {
                var updatedQuantity = Number(data);
                input_quantity.val(updatedQuantity);
                $("#view_cart").click();
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
                $("#checkout").load(location.href + " #checkout");
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
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var quantity = Number(input_quantity.val());

        $.ajax({
            url: "pages/cashier2_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                quantity: quantity,
                modifyquantity: 'modifyquantity',
                deductquantity: 'deductquantity'
            },
            success: function(data) {
                var updatedQuantity = Number(data);
                input_quantity.val(updatedQuantity);
                $("#view_cart").click();
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
                $("#checkout").load(location.href + " #checkout");
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
        $.ajax({
            url: "pages/cashier_ajax.php",
            data: {
                product_id_del: id,
                deleteitem: 'deleteitem'
            },
            type: "POST",
            success: function(data) {
                $("#view_cart").click();
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
                $("#checkout").load(location.href + " #checkout");
            },
            error: function() {}
        });
    }

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
            var type_id = $('#select-type').find('option:selected').val();
            var line_id = $('#select-line').find('option:selected').val();
            var category_id = $('#select-category').find('option:selected').val();
            var onlyInStock = $('#toggleActive').prop('checked');
            $.ajax({
                url: 'pages/cashier2_ajax.php',
                type: 'POST',
                data: {
                    query: query,
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

        $(document).on('click', '#view_cart', function(event) {
            event.preventDefault();
            loadCart();
            $('#view_cart_modal').modal('show');
        });

        $('#rowsPerPage').change(function() {
            rowsPerPage = parseInt($(this).val());
            currentPage = 1;
            updateTable();
        });


        $(document).on('input change', '#text-srh, #select-category, #select-type, #select-line', function() {
            performSearch($('#text-srh').val());
        });

        $('#select-type').select2();
        $('#select-line').select2();
        $('#select-category').select2();

        $(document).on('input change', '#text-srh, #select-category, #select-type, #select-line, #toggleActive', function() {
            performSearch($('#text-srh').val());
        });

        performSearch('');
        updateTable(); // Initial table update
    });

    
</script>