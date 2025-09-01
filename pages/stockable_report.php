<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Stockable Inventory Report";

$permission = $_SESSION['permission'];
?>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title ?></h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Reports
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

    <div class="card card-body">
        <div class="row">
        <div class="col-3">
            <h3 class="card-title align-items-center mb-2">
                Search <?= $page_title ?>
            </h3>
            
            <div class="position-relative w-100 px-0 mr-0 mb-2">
                <input type="text" class="form-control py-2 ps-5 " id="text_search" placeholder="Search">
                <i class="ti ti-user position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </div>
            <div class="position-relative w-100 px-0 mr-0 mb-2">
                <div class="input-daterange input-group mb-3" id="datepicker">
                    <input type="text" class="form-control form-control-md px-1" id="date_from" name="start" placeholder="Start Date" autocomplete="off" />
                    <span class="input-group-text py-1 px-2 small">to</span>
                    <input type="text" class="form-control form-control-md px-1" id="date_to" name="end" placeholder="End Date" autocomplete="off" />
                </div>
            </div>

            <hr class="my-3 border-dark opacity-75">

            <div class="position-relative w-100 px-0">
                <div class="mb-2">
                    <select id="month_select" name="month[]" multiple class="form-select select2-month" style="width: 100%;">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
            </div>

            <div class="position-relative w-100 px-0 mb-2">
                <div class="mb-2">
                    <select id="year_select" name="year[]" multiple class="form-select select2-year" style="width: 100%;">
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
            <div id="selected-tags" class="mb-2"></div>
            <div class="datatables">
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?> List</h4>
                <div class="table-responsive text-nowrap">
                    <table id="stockable_table" class="table table-hover mb-0 text-md-nowrap">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Date</th>
                                <th>Remaining Inventory</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    </div>
</div>

<div class="modal" id="view_stockable_details_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 80%">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Stockable Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="stockable-details">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_product_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Product Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="product-details">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function loadProductDetails(product_id){
        $.ajax({
            url: 'pages/stockable_report_ajax.php',
            type: 'POST',
            data: {
                product_id: product_id,
                fetch_product_details: "fetch_product_details"
            },
            success: function(response) {
                $('#product-details').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadStockableDetails(id){
        $.ajax({
            url: 'pages/stockable_report_ajax.php',
            type: 'POST',
            data: {
                id: id,
                fetch_stockable_details: "fetch_stockable_details"
            },
            success: function(response) {
                $('#stockable-details').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    $(document).ready(function() {
        document.title = "<?= $page_title ?>";

        var isPrinting = false;

        const yearSelect = $('#year_select');
        const currentYear = new Date().getFullYear();
        const yearsBack = 15;
        for (let y = currentYear; y >= currentYear - yearsBack; y--) {
            yearSelect.append(new Option(y, y));
        }

        $(document).on('mouseenter focus', '.select2-selection__choice, .select2-selection__choice__remove', function () {
            $(this).removeAttr('title');
            
            if ($(this).data('bs.tooltip')) {
                $(this).tooltip('hide');
            }
        });

        $(document).on('click', '.select2-selection__choice__remove', function () {
            $(this).tooltip('hide');
        });

        $('.input-daterange').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            orientation: "bottom auto"
        });

        $(".select2").each(function () {
            $(this).select2({
                width: '100%',
                dropdownParent: $(this).parent()
            });
        });

        $("#month_select").select2({
            placeholder: "All Months",
            width: '100%',
            dropdownParent: $("#month_select").parent()
        });

        $("#year_select").select2({
            placeholder: "All Years",
            width: '100%',
            dropdownParent: $("#year_select").parent()
        });

        function updateSelectedTags() {
            var displayDiv = $('#selected-tags');
            displayDiv.empty();

            $('.filter-selection').each(function() {
                var selectedOption = $(this).find('option:selected');
                var selectedText = selectedOption.text().trim();
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
        
        function performSearch() {
            const date_from = $('#date_from').val();
            const date_to = $('#date_to').val();
            const month_select = $('#month_select').val() || [];
            const year_select = $('#year_select').val() || [];

            $.ajax({
                url: 'pages/stockable_report_ajax.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    date_from,
                    date_to,
                    month_select,
                    year_select,
                    search_stockable: 'search_stockable'
                },
                success: function (response) {
                    console.log(response);

                    if ($.fn.DataTable.isDataTable('#stockable_table')) {
                        $('#stockable_table').DataTable().clear().destroy();
                    }

                    const table = $('#stockable_table').DataTable({
                        pageLength: 100
                    });

                    $('#stockable_table_filter').hide();

                    table.clear();

                    $('#text_search').on('keyup', function() {
                        table.search(this.value).draw();
                    });

                    if (response.stockables.length > 0) {
                        response.stockables.forEach(stockable => {
                            let rowData = [];

                            rowData.push(stockable.product_id);
                            let stockable_details_btn = `
                                <a href="javascript:void(0)" class="text-primary" id="view_product_details" data-id="${stockable.product_id}">
                                    ${stockable.product_name}
                                </a>
                            `;
                            rowData.push(stockable_details_btn);
                            rowData.push(stockable.quantity);
                            rowData.push(stockable.date);
                            rowData.push(stockable.remaining);

                            let actionButtons = `
                                <a href="javascript:void(0)" class="text-primary" id="view_stockable" data-id="${stockable.order_id}" title="View Order">
                                    <i class="fa fa-eye"></i>
                                </a>
                            `;

                            rowData.push(actionButtons);

                            table.row.add(rowData);
                        });

                        table.draw();

                    }

                    updateSelectedTags();
                },
                error: function (xhr, status, error) {
                    alert('Error: ' + status + ' - ' + xhr.responseText);
                }
            });
        }


        $(document).on('change', '#customer_search, #date_from, #date_to, .filter-selection', function(event) {
            performSearch();
        });

        $(document).on('click', '#view_stockable', function(event) {
            var id = $(this).data('id');
            loadStockableDetails(id);
            $('#view_stockable_details_modal').modal('toggle');
        });

        $(document).on('click', '#view_product_details', function(event) {
            var product_id = $(this).data('id');
            loadProductDetails(product_id);
            $('#view_product_modal').modal('toggle');
        });

        performSearch();
    });
</script>