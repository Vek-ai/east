<?php
require '../includes/dbconn.php';
require '../includes/functions.php';
?>

<div class="product-list">
    <div class="card">
        <div class="card-body text-right p-3">
            <div class="p-2">
                <input type="checkbox" id="toggleActive" checked> Exclude Out of Stock</div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-9 p-2">
                <div class="position-relative w-100 col-6">
                    <input type="text" class="form-control search-chat py-2 ps-5 " id="text-srh" placeholder="Search Product">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-profile" data-category="">
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
                    <select class="form-control search-chat py-0 ps-5" id="select-grade" data-category="">
                        <option value="" data-category="">All Grades</option>
                        <optgroup label="Product Grades">
                            <?php
                            $query_grade = "SELECT * FROM product_grade WHERE hidden = '0'";
                            $result_grade = mysqli_query($conn, $query_grade);
                            while ($row_grade = mysqli_fetch_array($result_grade)) {
                            ?>
                                <option value="<?= $row_grade['product_grade_id'] ?>" data-category="line"><?= $row_grade['product_grade'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 col-2">
                    <select class="form-control search-chat py-0 ps-5" id="select-color" data-category="">
                        <option value="" data-category="">All Profiles</option>
                        <optgroup label="Product Profiles">
                            <?php
                            $query_profile = "SELECT * FROM profile_type WHERE hidden = '0'";
                            $result_profile = mysqli_query($conn, $query_profile);
                            while ($row_profile = mysqli_fetch_array($result_profile)) {
                            ?>
                                <option value="<?= $row_profile['profile_type_id'] ?>" data-category="type"><?= $row_profile['profile_type'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="table-responsive border rounded ">
                <table id="productTable" class="table align-middle text-nowrap mb-0 p-2">
                    <thead>
                        <tr>
                            <th scope="col">Description</th>
                            <th scope="col">Color</th>
                            <th scope="col">Grade</th>
                            <th scope="col">Profile</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Stock</th>
                            <th scope="col">Price</th>
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

<div class="modal" id="viewDetailsmodal"></div>

<script>
    function updatequantity(element) {
        var product_id = $(element).data('id');
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var qty = Number(input_quantity.val());

        $.ajax({
            url: "pages/cashier_ajax.php",
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
            url: "pages/cashier_ajax.php",
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
            url: "pages/cashier_ajax.php",
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
            var grade_id = $('#select-grade').find('option:selected').val();
            var profile_id = $('#select-profile').find('option:selected').val();
            var onlyInStock = $('#toggleActive').prop('checked');
            $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    query: query,
                    color_id: color_id,
                    grade_id: grade_id,
                    profile_id: profile_id,
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

        $(document).on('click', '#view_product_details', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/cashier_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        fetch_view_modal: "fetch_view_modal"
                    },
                    success: function(response) {
                        $('#viewDetailsmodal').html(response);
                        $('#viewDetailsmodal').modal('show');
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


        $(document).on('input change', '#text-srh, #select-profile, #select-color, #select-grade', function() {
            performSearch($('#text-srh').val());
        });

        $('#select-color').select2();
        $('#select-grade').select2();
        $('#select-profile').select2();

        $(document).on('input change', '#text-srh, #select-profile, #select-color, #select-grade, #toggleActive', function() {
            performSearch($('#text-srh').val());
        });

        performSearch('');
        updateTable();
    });
</script>