<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Rejected Return/Refund";
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

    <div class="card card-body">
        <div class="row">
        <div class="col-3">
            <h3 class="card-title align-items-center mb-2">
                Search <?= $page_title ?>
            </h3>
            
            <div class="position-relative w-100 px-0 mr-0 mb-2">
                <input type="text" class="form-control py-2 ps-5 " id="customer_search" placeholder="All Customers">
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

            <div class="align-items-center">
                <div class="position-relative w-100 px-0 mb-2">
                    <select class="form-control py-0 ps-5 select2 filter-selection" id="filter-staff" data-filter="staff" data-filter-name="Created by">
                        <option value="">All Salesperson</option>
                        <optgroup label="Salesperson">
                            <?php
                            $query_staff = "SELECT * FROM staff ORDER BY `staff_fname` ASC";
                            $result_staff = mysqli_query($conn, $query_staff);            
                            while ($row_staff = mysqli_fetch_array($result_staff)) {
                            ?>
                                <option value="<?= $row_staff['staff_id'] ?>"><?= $row_staff['staff_fname'] .' ' .$row_staff['staff_lname'] ?></option>
                            <?php   
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-0 mb-2">
                    <select class="form-control py-0 ps-5 select2 filter-selection" id="filter-tax" data-filter="tax" data-filter-name="Tax">
                        <option value="">All Tax Status</option>
                        <optgroup label="Tax Status">
                          <?php
                          $query_tax_status = "SELECT * FROM customer_tax";
                          $result_tax_status = mysqli_query($conn, $query_tax_status);
                          while ($row_tax_status = mysqli_fetch_array($result_tax_status)) {
                            ?>
                            <option value="<?= $row_tax_status['taxid'] ?>">
                              (<?= $row_tax_status['percentage'] ?>%) <?= $row_tax_status['tax_status_desc'] ?></option>
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
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?> List</h4>
                <div class="table-responsive text-nowrap">
                    <table id="returns_table" class="table table-hover mb-0 text-md-nowrap">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Product Amount</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Salesperson</th>
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

<div class="modal" id="view_order_details_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Return/Refund Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="order-details">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $("#customer_search").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/returns_list_rejected_ajax.php",
                type: 'post',
                dataType: "json",
                data: {
                    search_customer: request.term
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
            $('#customer_search').val(ui.item.label);
            return false;
        },
        focus: function(event, ui) {
            $('#customer_search').val(ui.item.label);
            return false;
        },
        minLength: 0
    });

    function loadOrderDetails(orderid){
        $.ajax({
            url: 'pages/returns_list_rejected_ajax.php',
            type: 'POST',
            data: {
                orderid: orderid,
                fetch_order_details: "fetch_order_details"
            },
            success: function(response) {
                $('#order-details').html(response);
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
            todayHighlight: true
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
            const customer_name = $('#customer_search').val();
            const date_from = $('#date_from').val();
            const date_to = $('#date_to').val();
            const month_select = $('#month_select').val() || [];
            const year_select = $('#year_select').val() || [];
            const staff = $('#filter-staff').val();
            const tax_status = $('#filter-tax').val();

            $.ajax({
                url: 'pages/returns_list_rejected_ajax.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    customer_name,
                    date_from,
                    date_to,
                    month_select,
                    year_select,
                    tax_status,
                    staff,
                    search_returns: 'search_returns'
                },
                success: function (response) {
                    console.log(response);

                    if ($.fn.DataTable.isDataTable('#returns_table')) {
                        $('#returns_table').DataTable().clear().destroy();
                    }

                    const table = $('#returns_table').DataTable({
                        pageLength: 100
                    });

                    $('#returns_table_filter').hide();
                    table.clear();

                    if (response.orders.length > 0) {
                        response.orders.forEach(order => {

                            table.row.add([
                                order.orderid,
                                order.customer_name,
                                `$ ${parseFloat(order.amount).toFixed(2)}`,
                                order.formatted_date,
                                order.formatted_time,
                                order.cashier,
                                `
                                <a href="javascript:void(0)" class="text-primary" id="view_order_details" data-id="${order.orderid}" title="View Details">
                                        <i class="fa fa-eye"></i>
                                </a>
                                `
                            ]);
                        });

                        table.draw();

                        $('#returns_table tfoot').html(`
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total Orders:</td>
                                <td>${response.total_count}</td>
                                <td colspan="2" class="text-end fw-bold">Total Amount:</td>
                                <td class="text-end fw-bold">$ ${parseFloat(response.total_amount).toFixed(2)}</td>
                                <td></td>
                            </tr>
                        `);
                    } else {
                        $('#returns_table tfoot').html(`
                            <tr>
                                <td colspan="7" class="text-center">No orders found.</td>
                            </tr>
                        `);
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

        $(document).on('click', '#view_order_details', function(event) {
            var orderid = $(this).data('id');
            loadOrderDetails(orderid);
            $('#view_order_details_modal').modal('toggle');
        });

        $(document).on('click', '.btn-show-pdf', function(e) {
            e.preventDefault();
            pdfUrl = $(this).attr('href');
            document.getElementById('pdfFrame').src = pdfUrl;
            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();
        });

        performSearch();
    });
</script>