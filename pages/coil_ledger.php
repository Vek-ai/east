<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Coil Use History";

$permission = $_SESSION['permission'];

$coilid = $_REQUEST['coil'] ?? '';
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

            <hr class="my-3 border-dark opacity-75">

            <div class="align-items-center">
                <div class="position-relative w-100 px-1 mb-2">
                    <select id="customer_id" class="form-control select2-filter filter-selection select2" name="customer" data-filter="customer" data-filter-name="Customer">
                        <option value="" >All Customers...</option>
                        <optgroup label="Customers">
                            <?php
                            $query_customer = "SELECT * FROM customer WHERE status = 1 ORDER BY `customer_first_name` ASC";
                            $result_customer = mysqli_query($conn, $query_customer);            
                            while ($row_customer = mysqli_fetch_array($result_customer)) {
                            ?>
                                <option value="<?= $row_customer['customer_id'] ?>"><?= get_customer_name($row_customer['customer_id']) ?></option>
                            <?php   
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 mb-2">
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
                <div class="position-relative w-100 px-1 mb-2">
                    <select id="supplier_id" class="form-control select2-filter filter-selection select2" name="supplier_id" data-filter="supplier" data-filter-name="Supplier">
                        <option value="" >All Suppliers...</option>
                        <optgroup label="Supplier">
                            <?php
                            $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
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
            
            <div class="d-flex justify-content-end py-2">
                <button type="button" class="btn btn-outline-primary reset_filters">
                    <i class="fas fa-sync-alt me-1"></i> Reset Filters
                </button>
            </div>
        </div>
        <div class="col-9">
            <div id="selected-tags" class="mb-2"></div>
            <div class="datatables">
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?></h4>
                <div class="table-responsive text-nowrap">
                    <table id="coil_usage_table" class="table table-hover mb-0 text-md-nowrap">
                        <thead>
                            <tr>
                                <th>Coil ID #</th>
                                <th>Customer</th>
                                <th>Used Ft</th>
                                <th>Remaining Ft</th>
                                <th>Date</th>
                                <th>Action</th>
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

<div class="modal" id="view_coil_details_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Coil Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="coil-details">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_coil_usage_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document" style="max-width: 80%;">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Work Order</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="usage-details">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_invoice_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document" style="max-width: 80%;">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Invoice Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="invoice-details">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function loadCoilDetails(coil_id){
        $.ajax({
            url: 'pages/coil_ledger_ajax.php',
            type: 'POST',
            data: {
                coil_id: coil_id,
                fetch_coil_details: "fetch_coil_details"
            },
            success: function(response) {
                $('#coil-details').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadUsageDetails(id){
        $.ajax({
            url: 'pages/coil_ledger_ajax.php',
            type: 'POST',
            data: {
                id: id,
                fetch_usage_details: "fetch_usage_details"
            },
            success: function(response) {
                console.log(response);
                $('#usage-details').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    $(document).ready(function() {
        document.title = "<?= $page_title ?>";

        var isPrinting = false;
        var coilid = '<?= $coilid ?>';

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
            const customer_id = $('#customer_id').val();
            const month_select = $('#month_select').val() || [];
            const supplier_id = $('#supplier_id').val() || [];

            $.ajax({
                url: 'pages/coil_ledger_ajax.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    coilid,
                    customer_id,
                    month_select,
                    supplier_id,
                    search_returns: 'search_returns'
                },
                success: function (response) {
                    console.log(response);

                    if ($.fn.DataTable.isDataTable('#coil_usage_table')) {
                        $('#coil_usage_table').DataTable().clear().destroy();
                    }

                    const table = $('#coil_usage_table').DataTable({
                        pageLength: 100,
                        ordering: false,
                        searching: true
                    });

                    $('#coil_usage_table_filter').hide();

                    table.clear();

                    $('#text_search').off('keyup').on('keyup', function() {
                        table.search(this.value).draw();
                    });

                    if (response.coils && response.coils.length > 0) {
                        response.coils.forEach(coil => {
                            const entry_no = coil.entry_no || '-';
                            const orderid = coil.orderid || '-';
                            const customer = coil.customer || '-';
                            const used_feet = coil.used_feet || '-';
                            const remaining_length = coil.remaining_length || '-';
                            const date = coil.transaction_date || '-';

                            const actionButtons = `
                                <a href="javascript:void(0)" 
                                class="text-primary" 
                                id="view_coil_usage" 
                                data-id="${coil.used_in_workorders}" 
                                title="View Coil Usage">
                                    <i class="fa fa-eye"></i>
                                </a>
                            `;

                            table.row.add([
                                entry_no,        
                                customer,
                                used_feet,
                                remaining_length,
                                date,
                                actionButtons
                            ]);
                        });

                        table.draw();
                    }

                    updateSelectedTags();
                },
                error: function (xhr, status, error) {
                    console.error('Error:', status, xhr.responseText);
                    alert('Error fetching coil usage data.');
                }
            });
        }



        $(document).on('change', '#customer_search, #date_from, #date_to, .filter-selection', function(event) {
            performSearch();
        });

        $(document).on('click', '#view_coil_details', function(event) {
            var coil_id = $(this).data('id');
            loadCoilDetails(coil_id);
            $('#view_coil_details_modal').modal('toggle');
        });

        $(document).on('click', '#view_coil_usage', function(event) {
            var id = $(this).data('id');
            loadUsageDetails(id);
            $('#view_coil_usage_modal').modal('toggle');
        });

        $(document).on('click', '.view_invoice_details', function(event) {
            var orderid = $(this).data('orderid');
            $.ajax({
                url: 'pages/sales_list_ajax.php',
                type: 'POST',
                data: {
                    orderid: orderid,
                    fetch_order_details: "fetch_order_details"
                },
                success: function(response) {
                    console.log(response);
                    $('#invoice-details').html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Failed!');
                    console.log(jqXHR.responseText)
                }
            });
            $('#view_invoice_modal').modal('toggle');
        });

        performSearch();
    });
</script>