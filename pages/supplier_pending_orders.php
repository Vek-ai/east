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

$page_title = "Supplier Orders";
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
        z-index: 9999; /* Ensure the remove button is on top of the image */
        cursor: pointer; /* Make sure it looks clickable */
    }

    #supplier_pending_orders_filter {
        display: none !important;
    }

    #productTable th:nth-child(1), 
    #productTable td:nth-child(1) {
        width: 25% !important; /* Products */
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
                <a class="text-muted text-decoration-none" href="?page=">Home
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

    <div class="modal fade" id="viewProductModal" tabindex="-1" role="dialog" aria-labelledby="viewProductModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        View Product Details
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="product_details" class="form-horizontal">
                    <div id="viewProductModalBody" class="modal-body">
                        
                    </div>
                </div>

            </div>
            <!-- /.modal-content -->
        </div>
    </div>

    
    <div class="card card-body">
        <div class="row">
            <div class="col-3">
                <h3 class="card-title align-items-center mb-2">
                    Filter <?= $page_title ?> 
                </h3>
                <div class="position-relative w-100 px-0 mr-0 mb-2">
                    <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search <?= $page_title ?>">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center">
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control py-0 ps-5 select2 filter-selection" data-filter="cashier" data-filter-name="Salesperson" id="select-cashier">
                            <option value="">All Salespersons</option>
                            <?php
                            $query_staff = "SELECT staff_id, staff_fname, staff_lname FROM staff WHERE status = 1 ORDER BY staff_fname ASC";
                            $result_staff = mysqli_query($conn, $query_staff);
                            while ($row_staff = mysqli_fetch_assoc($result_staff)) {
                                ?>
                                <option value="<?= $row_staff['staff_id'] ?>">
                                    <?= htmlspecialchars($row_staff['staff_fname'] . ' ' . $row_staff['staff_lname']) ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control py-0 ps-5 select2 filter-selection" data-filter="supplier" data-filter-name="Supplier" id="select-supplier">
                            <option value="">All Suppliers</option>
                            <?php
                            $query_staff = "SELECT supplier_id FROM supplier WHERE status = 1 ORDER BY supplier_name ASC";
                            $result_staff = mysqli_query($conn, $query_staff);
                            while ($row_staff = mysqli_fetch_assoc($result_staff)) {
                                ?>
                                <option value="<?= $row_staff['supplier_id'] ?>">
                                    <?= getSupplierName($row_staff['supplier_id']) ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="month" data-filter-name="Month" id="select-month">
                            <option value="">All Months</option>
                            <option value="01">January</option>
                            <option value="02">February</option>
                            <option value="03">March</option>
                            <option value="04">April</option>
                            <option value="05">May</option>
                            <option value="06">June</option>
                            <option value="07">July</option>
                            <option value="08">August</option>
                            <option value="09">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>

                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="year" data-filter-name="Year" id="select-year">
                            <option value="">All Years</option>
                            <?php
                                $currentYear = date("Y");
                                for ($year = $currentYear; $year >= $currentYear - 20; $year--) {
                                    echo "<option value=\"$year\">$year</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end py-2">
                    <button type="button" class="btn btn-outline-primary reset_filters">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
            <div class="col-9">
                <h3 class="card-title mb-2">
                    <?= $page_title ?> List 
                </h3>
                <div id="selected-tags" class="mb-2"></div>
                <div class="datatables">
                    <div class="table-responsive">
                        <table id="supplier_pending_orders" class="table search-table align-middle text-wrap">
                            <thead class="header-item">
                                <th>Supplier Name</th>
                                <th>Cashier</th>
                                <th>Price</th>
                                <th>Date</th>
                                <th>Action</th>
                            </thead>
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
        var supplier_id = '';
        
        function loadViewModal() {
            $.ajax({
                url: 'pages/supplier_pending_orders_ajax.php',
                type: 'POST',
                data: {
                    supplier_id: supplier_id,
                    action: "fetch_view_modal"
                },
                success: function(response) {
                    $('#viewProductModalBody').html(response);

                    if ($.fn.DataTable.isDataTable('#supplier_order_products')) {
                        $('#supplier_order_products').DataTable().destroy();
                    }

                    $('#supplier_order_products').DataTable({
                        paging: false,
                        searching: false,
                        info: false,
                        ordering: true
                    });

                    $('#viewProductModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }
        var selectedCategory = '';

        var table = $('#supplier_pending_orders').DataTable({
            order: [],
            pageLength: 100,
            ajax: {
                url: 'pages/supplier_pending_orders_ajax.php',
                type: 'POST',
                data: { action: 'fetch_suppliers_w_order' }
            },
            columns: [
                { data: 'supplier_name' },
                { data: 'cashier_name' },
                { data: 'total_price' },
                { data: 'order_date' },
                { data: 'action_html' }
            ],
            createdRow: function (row, data, dataIndex) {
                $(row).attr('data-supplier', data.supplier_id);
                $(row).attr('data-cashier', data.cashier_id);
                $(row).attr('data-month', data.month);
                $(row).attr('data-year', data.year);
            },
            dom: 'lftp'
        });



        $('#supplier_pending_orders_filter').hide();

        $(".select2").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            });
        });

        $(document).on('click', '.view_order_btn', function(event) {
            event.preventDefault();
            supplier_id = $(this).data('supplier');
            loadViewModal();
        });

        $(document).on('click', '#approve_supplier_order', function () {
            let supplierId = $(this).data('supplier-id');

            $.ajax({
                url: 'pages/supplier_pending_orders_ajax.php',
                type: 'POST',
                data: {
                    supplier_id: supplierId,
                    action: 'approve_supplier_order'
                },
                success: function (response) {
                    alert('Orders approved successfully.');
                    $('.modal').modal('hide');
                    table.ajax.reload(null, false);
                },
                error: function () {
                    alert('Failed to approve orders.');
                }
            });
        });

        $(document).on('click', '#remove_supplier_order', function () {
            let supplierId = $(this).data('supplier-id');

            if (confirm('Are you sure you want to remove all orders for this supplier?')) {
                $.ajax({
                    url: 'pages/supplier_pending_orders_ajax.php',
                    type: 'POST',
                    data: {
                        supplier_id: supplierId,
                        action: 'reject_supplier_order'
                    },
                    success: function (response) {
                        alert('Orders rejected successfully.');
                        $('.modal').modal('hide');
                        table.ajax.reload(null, false);
                    },
                    error: function () {
                        alert('Failed to reject orders.');
                    }
                });
            }
        });

        $(document).on('click', '.delete-product-btn', function () {
            const prodOrderId = $(this).data('id');

            if (!confirm("Are you sure you want to delete this product from the order?")) {
                return;
            }

            $.ajax({
                url: 'pages/supplier_pending_orders_ajax.php',
                type: 'POST',
                data: {
                    action: 'delete_product',
                    prod_order_id: prodOrderId
                },
                success: function (response) {
                    if(response.trim() == 'success'){
                        alert("Successfully deleted product from order");
                    }else{
                        alert('Failed');
                    }
                    loadViewModal();
                },
                error: function (xhr, status, error) {
                    console.log("Error:", xhr.responseText);
                }
            });
        });

        
        $(document).on('mousedown', '.readonly', function() {
            e.preventDefault();
        });

        function filterTable() {
            var textSearch = $('#text-srh').val().toLowerCase();

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

    });
</script>