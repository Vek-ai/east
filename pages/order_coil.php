<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
session_start();
require 'includes/dbconn.php';
require 'includes/functions.php';

$trim_id = 43;
$panel_id = 46;
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
    <div class="card">
        <div class="card-body text-right p-3">
            <div class="row mb-9">
                <div class="col-12 text-left">
                    <h3 class="modal-title">Order Cart</h3>
                </div>
                <div class="col-6 text-left">
                    <div id="select_supplier_section">
                        <?php 
                            if (!empty($_SESSION["supplier_id"])) {
                        ?>
                            <div class="form-group">
                                <label>Supplier: <?= getSupplierName($_SESSION["supplier_id"]); ?></label>
                                <button class="btn ripple btn-primary" type="button" id="supplier_change"><i class="fe fe-reload"></i> Change</button>                                       
                            </div>
                        <?php } else { ?>
                            <div class="input-group">
                                <input class="form-control" placeholder="Search Supplier" type="text" id="supplier_select">
                                <a class="input-group-text rounded-right m-0 p-0" href="/?page=product_supplier" target="_blank">
                                    <span class="input-group-text"> + </span>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                    <input type='hidden' id='supplier_select_id' name="supplier_select_id"/>
                </div>
                <div class="pt-0" id="order-tbl"></div>
            </div>
            <div class="row">
                <div class="col-6">
                </div>
                <div class="col-6">
                    <div class="d-flex justify-content-end position-sticky" style="bottom: 0;">
                        <button type="button" id="btn_save" class="btn btn-primary d-flex align-items-center my-2 ms-1">
                            <i class="fa fa-shopping-cart fs-4 me-2"></i>
                            Save
                        </button>
                        <a href="?page=order_coil_summary">
                            <button type="button" id="btn_order" class="btn btn-success d-flex align-items-center my-2 ms-1">
                                <i class="fa fa-shopping-cart fs-4 me-2"></i>
                                Order
                            </button>
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-right p-3">
            <div class="row mb-9">
                <div class="col-12 text-left">
                    <h3 class="modal-title">Order Products</h3>
                </div>
                
            </div>
            <div class="d-flex justify-content-between align-items-center text-left mb-9">
                <div class="position-relative w-100 col-4 pl-0">
                    <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Product">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-color" data-category="">
                        <option value="" data-category="">All Colors</option>
                        <optgroup label="Product Colors">
                            <?php
                            $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
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
                            $excluded_ids = [];
                            $excluded_ids[] = $trim_id;
                            $excluded_ids[] = $panel_id;
                            $excluded_ids_str = implode(',', $excluded_ids);
                            $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND product_category_id NOT IN ($excluded_ids_str) ORDER BY `product_category` ASC";
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
                            $query_line = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
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
                            $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
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
                <style>
                    .table-fixed-orders {
                        table-layout: fixed;
                        width: 100%;
                    }

                    .table-fixed-orders th,
                    .table-fixed-orders td {
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: normal;
                    }

                    .table-fixed-orders th:nth-child(1),
                    .table-fixed-orders td:nth-child(1) {
                         width: 25%; 
                         word-wrap: break-word;
                    }
                    .table-fixed-orders th:nth-child(2),
                    .table-fixed-orders td:nth-child(2) { width: 6%; }
                </style>
                <table id="productTable" class="table align-middle text-nowrap mb-0 table-fixed-orders text-center">
                    <thead>
                        <tr>
                            <th scope="col">Product</th>
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
        
    </div>
    
</div>

<script>
    function loadOrderContents(){
        $.ajax({
            url: 'pages/order_coil_ajax.php',
            type: 'POST',
            data: {
                fetch_orders: "fetch_orders"
            },
            success: function(response) {
                $('#order-tbl').html(response);

                var table = $('#orderTable').DataTable({
                    language: {
                        emptyTable: "No products added to orders"
                    },
                    paging: false,
                    searching: false,
                    info: false,
                    ordering: false,
                    responsive: true
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function addToOrders(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: "pages/order_coil_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                modifyquantity: 'modifyquantity',
                addquantity: 'addquantity'
            },
            success: function(data) {
                loadOrderContents();
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

    function addToOrdersCoil(element) {
        var coil_id = $(element).data('id');

        $.ajax({
            url: "pages/order_coil_ajax.php",
            type: "POST",
            data: {
                coil_id: coil_id,
                add_order_coil: 'add_order_coil'
            },
            success: function(data) {
                loadOrderContents();
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
        var type = $(element).data('type');
        var qty = $(element).val();
        $.ajax({
            url: "pages/order_coil_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                type: type,
                qty: qty,
                modifyquantity: 'modifyquantity',
                setquantity: 'setquantity'
            },
            success: function(data) {
                loadOrderContents();
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
        var type = $(element).data('type');
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var quantity = Number(input_quantity.val());
        $.ajax({
            url: "pages/order_coil_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                type: type,
                quantity: quantity,
                modifyquantity: 'modifyquantity',
                addquantity: 'addquantity'
            },
            success: function(data) {
                console.log(data);
                loadOrderContents();
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
        var type = $(element).data('type');
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var quantity = Number(input_quantity.val());
        $.ajax({
            url: "pages/order_coil_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                type: type,
                quantity: quantity,
                modifyquantity: 'modifyquantity',
                deductquantity: 'deductquantity'
            },
            success: function(data) {
                loadOrderContents();
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
        var type = $(element).data('type');
        $.ajax({
            url: "pages/order_coil_ajax.php",
            data: {
                product_id_del: id,
                line: line,
                type: type,
                deleteitem: 'deleteitem'
            },
            type: "POST",
            success: function(data) {
                loadOrderContents();
            },
            error: function() {}
        });
    }

    $("#supplier_select").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/order_coil_ajax.php",
                type: 'post',
                dataType: "json",
                data: {
                    search_supplier: request.term
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
            $('#supplier_select').val(ui.item.label);
            $('#supplier_select_id').val(ui.item.value);
            return false;
        },
        focus: function(event, ui) {
            $('#supplier_select').val(ui.item.label);
            return false;
        }
    }); 

    $(document).ready(function() {
        loadOrderContents();
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
            $.ajax({
                url: 'pages/order_coil_ajax.php',
                type: 'POST',
                data: {
                    query: query,
                    color_id: color_id,
                    type_id: type_id,
                    line_id: line_id,
                    category_id: category_id
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

        $(document).on('click', '#btn_save', function(event) {
            $.ajax({
                url: 'pages/order_coil_ajax.php',
                type: 'POST',
                data: {
                    save_order: 'save_order'
                },
                success: function(response) {
                    
                    if(response.trim() == 'success'){
                        alert("Order successfully saved.");
                        $('#cashmodal').modal('hide');
                    }
                    alert(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('change', '#supplier_select', function(event) {
            var supplier_id = $('#supplier_select_id').val();
            console.log(supplier_id)
            $.ajax({
                url: 'pages/order_coil_ajax.php',
                type: 'POST',
                data: {
                    supplier_id: supplier_id,
                    change_supplier: "change_supplier"
                },
                success: function(response) {
                    if (response.trim() == 'success') {
                        location.reload();
                    }
                    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#supplier_change', function(event) {
            $.ajax({
                url: 'pages/order_coil_ajax.php',
                type: 'POST',
                data: {
                    unset_supplier: "unset_supplier"
                },
                success: function(response) {
                    location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#view_order', function(event) {
            loadOrderContents();
            $('#view_orders').modal('show');
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

        $(document).on('input change', '#text-srh, #select-color, #select-category, #select-type, #select-line', function() {
            performSearch($('#text-srh').val());
        });

        performSearch('');
        updateTable(); // Initial table update
    });
</script>
