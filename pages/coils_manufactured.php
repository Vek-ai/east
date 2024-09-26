<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$trim_id = 43;
$panel_id = 46;

$allowed_category = array();
$allowed_category[] = $trim_id;
$allowed_category[] = $panel_id;

?>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="font-weight-medium fs-14 mb-0">Coils Manufactured</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Coils
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Coils Manufactured</li>
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

<div class="modal fade" id="addInventoryModal" tabindex="-1" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myLargeModalLabel">
                    Add Inventory
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="add_inventory" class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                        <input type="hidden" id="Inventory_id" name="Inventory_id" class="form-control"  />

                        <div class="row pt-3">
                        <div class="col-md-12">
                            <label class="form-label">Product</label>
                            <div class="mb-3">
                            <select id="Product_id" class="form-control select2-add" name="Product_id">
                                <option value="" hidden>Select Product...</option>
                                <optgroup label="Product">
                                    <?php
                                    $query_product = "SELECT * FROM product WHERE hidden = '0'";
                                    $result_product = mysqli_query($conn, $query_product);            
                                    while ($row_product = mysqli_fetch_array($result_product)) {
                                    ?>
                                        <option value="<?= $row_product['product_id'] ?>" ><?= $row_product['product_item'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>

                        </div>
                        <div class="row pt-3">
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <div class="mb-3">
                            <select id="supplier_id" class="form-control select2-add" name="supplier_id">
                                <option value="" >Select Supplier...</option>
                                <optgroup label="Supplier">
                                    <?php
                                    $query_supplier = "SELECT * FROM supplier";
                                    $result_supplier = mysqli_query($conn, $query_supplier);            
                                    while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                    ?>
                                        <option value="<?= $row_supplier['supplier_id'] ?>" ><?= $row_supplier['supplier_name'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                                
                            </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Warehouse</label>
                            <div class="mb-3">
                            <select id="Warehouse_id" class="form-control select2-add" name="Warehouse_id">
                                <option value="" >Select Warehouse...</option>
                                <optgroup label="Warehouse">
                                    <?php
                                    $query_warehouse = "SELECT * FROM warehouses WHERE status = '1'";
                                    $result_warehouse = mysqli_query($conn, $query_warehouse);            
                                    while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                                    ?>
                                        <option value="<?= $row_warehouse['WarehouseID'] ?>" ><?= $row_warehouse['WarehouseName'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                                
                            </select>
                            </div>
                        </div>
                        </div>

                        <div class="row pt-3">
                        <div class="col-md-4">
                            <label class="form-label">Shelf</label>
                            <div class="mb-3">
                            <select id="Shelves_id" class="form-control select2-add" name="Shelves_id">
                                <option value="" >Select Shelf...</option>
                                <optgroup label="Shelf">
                                    <?php
                                    $query_shelf = "SELECT * FROM shelves";
                                    $result_shelf = mysqli_query($conn, $query_shelf);            
                                    while ($row_shelf = mysqli_fetch_array($result_shelf)) {
                                    ?>
                                        <option value="<?= $row_shelf['ShelfID'] ?>" ><?= $row_shelf['ShelfCode'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bin</label>
                            <div class="mb-3">
                            <select id="Bin_id" class="form-control select2-add" name="Bin_id">
                                <option value="" >Select Bin...</option>
                                <optgroup label="Bin">
                                    <?php
                                    $query_bin = "SELECT * FROM bins";
                                    $result_bin = mysqli_query($conn, $query_bin);            
                                    while ($row_bin = mysqli_fetch_array($result_bin)) {
                                    ?>
                                        <option value="<?= $row_bin['BinID'] ?>" ><?= $row_bin['BinCode'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Row</label>
                            <div class="mb-3">
                            <select id="Row_id" class="form-control select2-add" name="Row_id">
                                <option value="" >Select Row...</option>
                                <optgroup label="Row">
                                    <?php
                                    $query_rows = "SELECT * FROM warehouse_rows";
                                    $result_rows = mysqli_query($conn, $query_rows);            
                                    while ($row_rows = mysqli_fetch_array($result_rows)) {
                                    ?>
                                        <option value="<?= $row_rows['WarehouseRowID'] ?>" ><?= $row_rows['WarehouseRowID'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        </div>
                        <div class="row pt-3">
                            <div class="col-md-4">
                                <label class="form-label">Quantity</label>
                                <input type="text" id="quantity_add" name="quantity" class="form-control"  />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pack</label>
                                <div class="mb-3">
                                <select id="pack_add" class="form-control select2-add" name="pack">
                                    <option value="" >Select Pack...</option>
                                    <optgroup label="Pack">
                                        <?php
                                        $query_pack = "SELECT * FROM product_pack WHERE hidden = '0'";
                                        $result_pack = mysqli_query($conn, $query_pack);            
                                        while ($row_pack = mysqli_fetch_array($result_pack)) {
                                        ?>
                                            <option value="<?= $row_pack['id'] ?>" data-count="<?= $row_pack['pieces_count'] ?>" ><?= $row_pack['pack_name'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </optgroup>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Quantity</label>
                                <input type="text" id="quantity_ttl_add" name="quantity_ttl" class="form-control"  />
                            </div>
                        </div>  
                        <div class="row pt-3">
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" id="Date" name="Date" class="form-control"  />
                            </div>
                        </div>      
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-actions">
                        <div class="card-body">
                            <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                            <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
<!-- /.modal-dialog -->
</div>

<div class="product-list">
    <div class="card">
        <div class="card-body text-center p-3">
            <div class="d-flex justify-content-between align-items-center  mb-9">
                <div class="position-relative w-100 col-8">
                    <input type="text" class="form-control search-chat py-2 ps-5 " id="text-srh" placeholder="Search Product">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="position-relative w-100 px-1 col-4 ">
                    <select class="form-control search-chat py-0 ps-5" id="select-category" data-category="">
                        <option value="" data-category="">All Categories</option>
                        <optgroup label="Category">
                            <option value="46" data-category="category">Trim</option>
                            <option value="43" data-category="category">Panel</option>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="table-responsive border rounded">
                <table id="productTable" class="table align-middle text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Coil Name</th>
                            <th scope="col">Color</th>
                            <th scope="col">Quantity</th>
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
        var category_id = $('#select-category').find('option:selected').val();
        $.ajax({
            url: 'pages/coils_manufactured_ajax.php',
            type: 'POST',
            data: {
                query: query,
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

    $('#rowsPerPage').change(function() {
        rowsPerPage = parseInt($(this).val());
        currentPage = 1;
        updateTable();
    });


    $(document).on('input change', '#text-srh, #select-category', function() {
        performSearch($('#text-srh').val());
    });

    $('#select-category').select2();

    $(".select2-add").select2({
        dropdownParent: $('#addInventoryModal .modal-content'),
        placeholder: "Select One...",
        allowClear: true
    });

    $(document).on('change', '#quantity_add, #pack_add', function(event) {
        var qty = parseFloat($('#quantity_add').val());
        var selectedOption = $('#pack_add').find('option:selected');
        var pack = selectedOption.length ? parseFloat(selectedOption.data('count')) : 1;

        pack = isNaN(pack) ? 1 : pack;

        if (!isNaN(qty) && qty > 0) {
            $('#quantity_ttl_add').val(qty * pack);
        } else {
            $('#quantity_ttl_add').val('');
        }
    });

    $(document).on('submit', '#add_inventory', function(event) {
        event.preventDefault(); 

        var formData = new FormData(this);
        formData.append('action', 'add_update');
            
        $.ajax({
            url: 'pages/inventory_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#addInventoryModal').modal('hide');
                if (response.trim() === "success") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text("New inventory added successfully.");
                    $('#responseHeaderContainer').removeClass("bg-danger");
                    $('#responseHeaderContainer').addClass("bg-success");
                    $('#response-modal').modal("show");

                    $('#response-modal').on('hide.bs.modal', function () {
                        location.reload();
                    });
                } else {
                    $('#responseHeader').text("Failed");
                    $('#responseMsg').text(response);

                    $('#responseHeaderContainer').removeClass("bg-success");
                    $('#responseHeaderContainer').addClass("bg-danger");
                    $('#response-modal').modal("show");
                }

                
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $(document).on('input change', '#text-srh, #select-category', function() {
        performSearch($('#text-srh').val());
    });

    performSearch('');
    updateTable(); // Initial table update
});




</script>