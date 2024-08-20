<?php
require 'includes/dbconn.php';
require 'includes/functions.php';
?>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="font-weight-medium fs-14 mb-0">Shop list</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Shop list</li>
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

<div class="product-list">
    <div class="card">
        <div class="card-body p-3">
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
        var type_id = $('#select-type').find('option:selected').val();
        var line_id = $('#select-line').find('option:selected').val();
        var category_id = $('#select-category').find('option:selected').val();
        $.ajax({
            url: 'pages/product_view_ajax.php',
            type: 'POST',
            data: {
                query: query,
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

    $('#rowsPerPage').change(function() {
        rowsPerPage = parseInt($(this).val());
        currentPage = 1;
        updateTable();
    });

    $('#select-type').select2();
    $('#select-line').select2();
    $('#select-category').select2();

    $(document).on('input change', '#text-srh, #select-category, #select-type, #select-line', function() {
        performSearch($('#text-srh').val());
    });

    performSearch('');
    updateTable(); // Initial table update
});




</script>