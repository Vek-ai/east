<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

$picture_path = "images/product/product.jpg";

$page_title = "Stockable Order Release";

$price_per_hem = getPaymentSetting('price_per_hem');
$price_per_bend = getPaymentSetting('price_per_bend');

?>
<style>
    .dz-preview {
        position: relative;
    }

    .dz-remove {
        position: absolute;
        top: 10px;
        right: 10px;
        background: red;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        font-weight: bold;
        font-size: 12px;
        z-index: 9999;
        cursor: pointer;
    }

    #productList_filter {
        display: none !important;
    }

    #productTable th:nth-child(1), 
    #productTable td:nth-child(1) {
        width: 25% !important;
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
            <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title ?></h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page"><?= $page_title ?></li>
            </ol>
            </nav>
        </div>
        
        </div>
    </div>
    </div>

    <div class="widget-content searchable-container list">

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
                <h3 class="card-title align-items-center mb-2">
                    Filter <?= $page_title ?> 
                </h3>
                <div class="position-relative w-100 px-0 mr-0 mb-2">
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
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-system" data-filter="system" data-filter-name="Product System">
                            <option value="" data-category="">All Product Systems</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_system = "SELECT * FROM product_system WHERE hidden = '0' AND status = '1' ORDER BY `product_system` ASC";
                                $result_system = mysqli_query($conn, $query_system);
                                while ($row_system = mysqli_fetch_array($result_system)) {
                                ?>
                                    <option value="<?= $row_system['product_system'] ?>" data-category="<?= $row_system['product_category'] ?>" <?= $selected ?>><?= $row_system['product_system'] ?></option>
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
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-profile" data-filter="profile" data-filter-name="Product Profile">
                            <option value="" data-category="">All Profile Types</option>
                            <optgroup label="Product Line">
                                <?php
                                $query_profile = "SELECT * FROM profile_type WHERE hidden = '0' AND status = '1' ORDER BY `profile_type` ASC";
                                $result_profile = mysqli_query($conn, $query_profile);
                                while ($row_profile = mysqli_fetch_array($result_profile)) {
                                ?>
                                    <option value="<?= $row_profile['profile_type'] ?>" data-category="<?= $v['product_category'] ?>" <?= $selected ?>><?= $row_profile['profile_type'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-color" data-filter="color" data-filter-name="Product Color">
                            <option value="" data-category="">All Colors</option>
                            <optgroup label="Product Colors">
                                <?php
                                $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                $result_color = mysqli_query($conn, $query_color);
                                while ($row_color = mysqli_fetch_array($result_color)) {
                                ?>
                                    <option value="<?= $row_color['color_name'] ?>" data-category="category" <?= $selected ?>><?= $row_color['color_name'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-grade" data-filter="grade" data-filter-name="Product Grade">
                            <option value="" data-category="">All Grades</option>
                            <optgroup label="Product Grades">
                                <?php
                                $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                $result_grade = mysqli_query($conn, $query_grade);
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                ?>
                                    <option value="<?= $row_grade['product_grade'] ?>" data-category="grade" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-gauge" data-filter="gauge" data-filter-name="Product Gauge">
                            <option value="" data-category="">All Gauges</option>
                            <optgroup label="Product Gauges">
                                <?php
                                $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY `product_gauge` ASC";
                                $result_gauge = mysqli_query($conn, $query_gauge);
                                while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                ?>
                                    <option value="<?= $row_gauge['product_gauge'] ?>" data-category="gauge" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
                                <?php
                                }
                                ?>
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
                <div class="d-flex justify-content-end py-2">
                    <button type="button" class="btn btn-outline-primary reset_filters">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
            <div class="col-9">
                <div id="selected-tags" class="mb-2"></div>
                <div class="datatables">
                    <div class="table-responsive">
                        <table id="productList" class="table search-table align-middle text-wrap text-center">
                            <thead class="header-item">
                            <th>Invoice #</th>
                            <th>Product Name</th>
                            <th>Product Category</th>
                            <th>Product Line</th>
                            <th>Product Type</th>
                            <th>Warehouse</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Action</th>
                            </thead>
                            <tbody>
                            
                            </tbody>
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
            order: [[1, "asc"]],
            pageLength: 100,
            ajax: {
                url: 'pages/stockable_release_ajax.php',
                type: 'POST',
                data: { action: 'fetch_products' }
            },
            columns: [
                { data: 'orderid' },
                { data: 'product_name_html' },
                { data: 'product_category' },
                { data: 'product_line' },
                { data: 'product_type' },
                { data: 'warehouse' },
                { data: 'order_qty' },
                { data: 'status_html' },
                { data: 'action_html' }
            ],
            columnDefs: [
                { targets: 1, width: "20%" }
            ],
            createdRow: function (row, data, dataIndex) {
                $(row).attr('data-category', data.product_category);
                $(row).attr('data-system', data.product_system);
                $(row).attr('data-line', data.product_line);
                $(row).attr('data-type', data.product_type);
                $(row).attr('data-profile', data.profile);
                $(row).attr('data-color', data.color);
                $(row).attr('data-grade', data.grade);
                $(row).attr('data-gauge', data.gauge);
                $(row).attr('data-warehouse', data.warehouse);
                $(row).attr('data-active', data.active);
                $(row).attr('data-instock', data.instock);
                $(row).attr('data-active', data.status);
            },
            "dom": 'lftp'
        });

        $('#productList_filter').hide();

        $(".select2").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            });
        });

        $(document).on('click', '#release_product', function () {
            const productId = $(this).data('id');

            if (!confirm('Are you sure you want to release this product?')) {
                return;
            }

            $.ajax({
                url: 'pages/stockable_release_ajax.php',
                type: 'POST',
                data: {
                    action: 'release_product',
                    product_id: productId,
                    status: 2
                },
                success: function (response) {
                    console.log(response);
                    alert('Product successfully released');
                    table.ajax?.reload(null, false);
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        });

        function filterTable() {
            var textSearch = $('#text-srh').val().toLowerCase();
            var isActive = $('#toggleActive').is(':checked');
            var onlyInStock = $('#onlyInStock').is(':checked');

            $.fn.dataTable.ext.search = [];

            if (textSearch) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
                });
            }

            if (isActive) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    return $(table.row(dataIndex).node()).data('active') === 1;
                });
            }

            if (onlyInStock) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    return $(table.row(dataIndex).node()).data('instock') === 1;
                });
            }

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var row = $(table.row(dataIndex).node());
                var match = true;

                $('.filter-selection').each(function() {
                    var filterValue = $(this).val()?.toString().toLowerCase() || '';
                    var rowValue = row.data($(this).data('filter'))?.toString().toLowerCase() || '';

                    if (filterValue && filterValue !== '/') {
                        if (!rowValue.includes(filterValue)) {
                            match = false;
                            return false;
                        }
                    }
                });

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



