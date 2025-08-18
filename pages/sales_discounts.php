<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Sales & Discounts";

$permission = $_SESSION['permission'];

$staff_id = intval($_SESSION['userid']);
$profileSql = "SELECT access_profile_id FROM staff WHERE staff_id = $staff_id";
$profileRes = mysqli_query($conn, $profileSql);
$profile_id = 0;
if ($profileRes && mysqli_num_rows($profileRes) > 0) {
    $profile_id = intval(mysqli_fetch_assoc($profileRes)['access_profile_id']);
}
$page_id = getPageIdFromUrl($_GET['page'] ?? '');

$visibleColumns = getVisibleColumns($page_id, $profile_id);
function showCol($name) {
    global $visibleColumns;
    return !empty($visibleColumns[$name]);
}

?>
<style>
    #productList_filter {
        display: none !important;
    }

    .readonly {
        pointer-events: none;
        background-color: #f8f9fa;
        color: #6c757d;
        border: 0;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .readonly select,
    .readonly option {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
    }

    .readonly input {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
    }
</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"> <?= $page_title ?></h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Contact</li>
            </ol>
            </nav>
        </div>
        </div>
    </div>
    </div>

    <div class="widget-content searchable-container list">
    <?php                                                    
    if ($permission === 'edit') {
    ?>

    <div class="card card-body">
        <div class="row">
        <div class="col-md-12 col-xl-12 text-end d-flex justify-content-end mt-3 mt-md-0 gap-3">
            <button type="button" id="downloadProductModalBtn" class="btn btn-primary d-flex align-items-center">
                <i class="ti ti-download text-white me-1 fs-5"></i> Export <?= $page_title ?>
            </button>
            <button type="button" id="addDiscountModalBtn" class="btn btn-success d-flex align-items-center" data-id="">
                <i class="ti ti-plus text-white me-1 fs-5"></i> Bulk Discount
            </button>
        </div>
        </div>
    </div>

    <?php
    }
    ?>

    <div class="modal fade" id="saleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title">Add Product Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="text-center p-3">
                <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>

            </div>
        </div>
    </div>

    <!-- Bulk Discount Modal -->
    <div class="modal fade" id="bulkDiscountModal" tabindex="-1" aria-labelledby="bulkDiscountLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
            <form id="bulkDiscountForm">
                <div class="modal-header">
                <h5 class="modal-title" id="bulkDiscountLabel">Apply Bulk Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                <input type="hidden" name="action" value="apply_discount">
                <input type="hidden" name="apply_category" value="1">

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php
                        $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY product_category ASC";
                        $result_category = mysqli_query($conn, $query_category);
                        while ($row_category = mysqli_fetch_array($result_category)) {
                            echo "<option value='{$row_category['product_category_id']}'>{$row_category['product_category']}</option>";
                        }
                    ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Discount Type</label>
                    <input type="text" class="form-control" value="Percent" readonly>
                    <input type="hidden" name="discount_type" value="percent">
                </div>

                <div class="mb-3">
                    <label class="form-label">Discount Value (%)</label>
                    <input type="number" step="0.01" class="form-control" name="discount_value" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Date Start</label>
                    <input type="date" class="form-control" name="start_date" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Date End</label>
                    <input type="date" class="form-control" name="end_date" required>
                </div>
                </div>

                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Apply Discount</button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="response-modal" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div id="responseHeaderContainer" class="modal-header align-items-center modal-colored-header">
                <h4 id="responseHeader" class="m-0"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <p id="responseMsg"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
                Close
                </button>
            </div>
            </div>
        </div>
    </div>

    
    <div class="card card-body">
        <div class="row">
            <div class="col-3">
                <h3 class="card-title align-items-center mb-2 px-1">
                    Filter Products 
                </h3>
                <div class="position-relative w-100 px-1 mr-0 mb-2">
                    <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search Product">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center">
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control py-0 ps-5 select2 filter-selection" id="select-category" data-filter="category" data-filter-name="Product Category">
                            <option value="" data-category="">All Categories</option>
                            <optgroup label="Category">
                                <?php
                                $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                $result_category = mysqli_query($conn, $query_category);
                                while ($row_category = mysqli_fetch_array($result_category)) {
                                ?>
                                    <option value="<?= $row_category['product_category'] ?>" data-category="<?= $row_category['product_category'] ?>" <?= $selected ?>><?= $row_category['product_category'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-line" data-filter="line" data-filter-name="Product Line">
                            <option value="" data-category="">All Product Lines</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_line = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                                $result_line = mysqli_query($conn, $query_line);
                                while ($row_line = mysqli_fetch_array($result_line)) {
                                ?>
                                    <option value="<?= $row_line['product_line'] ?>" data-category="<?= $row_line['product_category'] ?>" <?= $selected ?>><?= $row_line['product_line'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-type" data-filter="type" data-filter-name="Product Type">
                            <option value="" data-category="">All Product Types</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                                $result_type = mysqli_query($conn, $query_type);
                                while ($row_type = mysqli_fetch_array($result_type)) {
                                ?>
                                    <option value="<?= $row_type['product_type'] ?>" data-category="<?= $row_type['product_category'] ?>" <?= $selected ?>><?= $row_type['product_type'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-status" data-filter="status" data-filter-name="Discount Status">
                            <option value="" data-category="">All Discount Statuses</option>
                            <optgroup label="Discount Status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="px-3 mb-2"> 
                    <input type="checkbox" id="toggleActive" checked> Show Active Only
                </div>
                <div class="px-3 mb-2">
                    <input type="checkbox" id="onlyInStock" <?= $onlyInStock ? 'checked' : '' ?>> Show only In Stock
                </div>
                <div class="px-1">
                    <button type="button" class="btn btn-primary reset_filters w-100 rounded-1">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
            <div class="col-9">
                <div id="selected-tags" class="mb-2"></div>
                <div class="datatables">
                    <div class="table-responsive">
                        <table id="productList" class="table search-table align-middle text-wrap">
                            <thead class="header-item">
                                <tr>
                                    <?php if (showCol('product_name')): ?>
                                        <th>Product Name</th>
                                    <?php endif; ?>

                                    <?php if (showCol('product_category')): ?>
                                        <th>Category</th>
                                    <?php endif; ?>

                                    <?php if (showCol('current_price')): ?>
                                        <th>Current Price</th>
                                    <?php endif; ?>

                                    <?php if (showCol('discount')): ?>
                                        <th>Discount</th>
                                    <?php endif; ?>

                                    <?php if (showCol('sale_price')): ?>
                                        <th>Sale Price</th>
                                    <?php endif; ?>

                                    <?php if (showCol('status')): ?>
                                        <th>Status</th>
                                    <?php endif; ?>

                                    <?php if (showCol('action')): ?>
                                        <th>Action</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    </div>
</div>

<script src="includes/pricing_data.js"></script>

<script>
    $(document).ready(function() {
        var selectedCategory = '';

        var table = $('#productList').DataTable({
            order: [],
            pageLength: 25,
            ajax: {
                url: 'pages/sales_discounts_ajax.php',
                type: 'POST',
                data: { action: 'fetch_products' },
                error: function(xhr, error, thrown) {
                    console.error('DataTables AJAX error:', error, thrown);
                    console.log('Response:', xhr.responseText);
                }
            },
            columns: [
                <?php if (showCol('product_name')): ?>
                    { data: 'product_name_html' },
                <?php endif; ?>

                <?php if (showCol('product_category')): ?>
                    { data: 'product_category' },
                <?php endif; ?>

                <?php if (showCol('current_price')): ?>
                    { data: 'current_price' },
                <?php endif; ?>

                <?php if (showCol('discount')): ?>
                    { data: 'discount' },
                <?php endif; ?>

                <?php if (showCol('sale_price')): ?>
                    { data: 'sale_price' },
                <?php endif; ?>

                <?php if (showCol('status')): ?>
                    { data: 'status_html' },
                <?php endif; ?>

                <?php if (showCol('action')): ?>
                    { data: 'action_html' }
                <?php endif; ?>
            ],
            createdRow: function (row, data, dataIndex) {
                $(row).attr('data-category', data.product_category);
                $(row).attr('data-line', data.product_line);
                $(row).attr('data-type', data.product_type);
                $(row).attr('data-status', data.status);
                $(row).attr('data-active', data.active);
                $(row).attr('data-instock', data.instock);
            },
            "dom": 'lftp'
        });

        $('#productList_filter').hide();

        $('#toggleActive').trigger('change');

        $(".select2").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            });
        });

        $(document).on('click', '#add_sale_btn', function(e) {
            e.preventDefault();

            let id = $(this).data('id');

            $('#saleModal').modal('show');
            $('#saleModal .modal-body').html(
                '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"></div></div>'
            );

            $.ajax({
                url: "pages/sales_discounts_ajax.php",
                type: "POST",
                data: { id: id, action: "fetch_view_modal" },
                success: function(response) {
                    $('#saleModal .modal-body').html(response);
                },
                error: function(xhr, status, error) {
                    $('#saleModal .modal-body').html(
                        '<div class="alert alert-danger">Failed to load data.</div>'
                    );
                }
            });
        });

        $(document).on("click", "#addDiscountModalBtn", function() {
            $("#bulkDiscountModal").modal("show");
        });

        $(document).on('click', '#applyDiscountBtn', function(e) {
            e.preventDefault();

            let data = {
                action: 'apply_discount',
                product_id: $('#unit_price').data('product-id'),
                category_id: $('#unit_price').data('category-id'),
                discount_type: $('select[name="discount_type"]').val(),
                discount_value: $('input[name="discount_value"]').val(),
                new_price: $('input[name="new_price"]').val(),
                start_date: $('input[name="start_date"]').val(),
                end_date: $('input[name="end_date"]').val(),
                apply_category: $('#apply_category').is(':checked') ? 1 : 0
            };

            $.ajax({
                url: "pages/sales_discounts_ajax.php",
                type: "POST",
                data: data,
                success: function(response) {
                    try {
                        let res = JSON.parse(response);
                        if (res.success) {
                            alert("Discount applied successfully!");
                            $('#saleModal').modal('hide');
                        } else {
                            alert("Error: " + res.message);
                        }
                        table.ajax.reload(null, false);
                    } catch(e) {
                        console.log(response);
                        alert("Unexpected response");
                    }
                },
                error: function(xhr) {
                    alert("Request failed");
                }
            });
        });

        $(document).on("submit", "#bulkDiscountForm", function(e) {
            e.preventDefault();

            let data = {
                action: 'apply_discount',
                product_id: 0,
                category_id: $('select[name="category_id"]').val(),
                discount_type: 'percent',
                discount_value: $('input[name="discount_value"]').val(),
                new_price: '',
                start_date: $('input[name="start_date"]').val(),
                end_date: $('input[name="end_date"]').val(),
                apply_category: 1
            };

            $.ajax({
                url: "pages/sales_discounts_ajax.php",
                type: "POST",
                data: data,
                success: function(response) {
                    try {
                        let res = JSON.parse(response);
                        if (res.success) {
                            alert("Bulk discount applied successfully!");
                            $("#bulkDiscountModal").modal("hide");
                        } else {
                            alert("Error: " + (response));
                        }
                        table.ajax.reload(null, false);
                    } catch (e) {
                        console.log(response);
                        alert("Unexpected response");
                    }
                },
                error: function() {
                    alert("Request failed");
                }
            });
        });


        $(document).on('change', 'select[name="discount_type"]', function() {
            $('input[name="discount_value"]').val('');
            $('input[name="new_price"]').val('');
        });

        $(document).on('input', 'input[name="discount_value"]', function() {
            let unitPrice = parseFloat($('#unit_price').val()) || 0;
            let discountType = $('select[name="discount_type"]').val();
            let discountValue = parseFloat($(this).val()) || 0;
            let newPrice = 0;

            if (discountType === 'fixed') {
                newPrice = discountValue;
            } else if (discountType === 'percent') {
                newPrice = unitPrice - (unitPrice * (discountValue / 100));
            }
            $('input[name="new_price"]').val(newPrice.toFixed(2));
        });
        
        $(document).on('mousedown', '.readonly', function() {
            e.preventDefault();
        });

        function filterTable() {
            var textSearch = $('#text-srh').val().toLowerCase();
            var isActive = $('#toggleActive').is(':checked');

            $.fn.dataTable.ext.search = [];

            if (textSearch) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
                });
            }

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var row = $(table.row(dataIndex).node());
                var match = true;

                $('.filter-selection').each(function() {
                    const normalize = str => str?.toString().trim().replace(/\s+/g, ' ').toLowerCase() || '';

                    var filterValue = normalize($(this).val());
                    var rowValue = normalize(row.data($(this).data('filter')));

                    if (filterValue && filterValue !== '/') {
                        if (!rowValue.includes(filterValue)) {
                            match = false;
                            return false;
                        }
                    }
                });

                if (isActive) {
                    var rowActive = parseInt(row.attr('data-active'), 10);
                    if (rowActive !== 1) {
                        match = false;
                    }
                }

                return match;
            });

            table.draw();
            updateSelectedTags();
        }

        function updateSelectedTags() {
            var displayDiv = $('#selected-tags');
            displayDiv.empty();

            $('.filter-selection').each(function() {
                var selectedOption = $(this).find('option:selected');
                var selectedText = selectedOption.text();
                var filterName = $(this).data('filter-name');

                if ($(this).val()) {
                    displayDiv.append(`
                        <div class="d-inline-block p-1 m-1 border rounded bg-light">
                            <span class="text-dark">${filterName}: ${selectedText}</span>
                            <button type="button" 
                                class="btn-close btn-sm ms-1 remove-tag" 
                                style="width: 0.75rem; height: 0.75rem;" 
                                aria-label="Close" 
                                data-select="#${$(this).attr('id')}">
                            </button>
                        </div>
                    `);
                }
            });

            $('.remove-tag').on('click', function() {
                $($(this).data('select')).val('').trigger('change');
                $(this).parent().remove();
            });
        }

        $(document).on('input change', '#text-srh, #toggleActive, #onlyInStock, .filter-selection', filterTable);

        $(document).on('click', '.reset_filters', function () {
            $('.filter-selection').each(function () {
                $(this).val(null).trigger('change.select2');
            });

            $('#text-srh').val('');

            filterTable();
        });
        filterTable();
    });
</script>



