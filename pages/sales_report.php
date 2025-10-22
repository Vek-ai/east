<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Sales Report";

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
    .modal.custom-size .modal-dialog {
        width: 80%;
        max-width: none;
        margin: 0 auto;
        height: 100vh;
    }

    .modal.custom-size .modal-content {
        height: 100%;
        border-radius: 0;
    }

    .modal.custom-size .modal-body {
        height: calc(100% - 56px);
        overflow: hidden;
    }

    .modal.custom-size iframe {
        width: 100%;
        height: 80%;
        border: none;
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
                <a class="text-muted text-decoration-none" href="">Sales
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
            <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
                <button type="button" id="downloadExcelBtn" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-file-spreadsheet text-white me-1 fs-5"></i> Excel Download
                </button>
                <button type="button" id="downloadPDFBtn" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-file-text text-white me-1 fs-5"></i> PDF Download
                </button>
                <button type="button" id="PrintBtn" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-printer text-white me-1 fs-5"></i> Print
                </button>
            </div>
        </div>
    </div>

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
                    <select id="month_select" name="month[]" multiple class="form-select select2-month filter-selection" style="width: 100%;" data-filter="month" data-filter-name="Month">
                        <option value="">All Months</option>
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
                    <select id="year_select" name="year[]" multiple class="form-select select2-year filter-selection" style="width: 100%;" data-filter="year" data-filter-name="Year">
                        <option value="">All Years</option>
                    </select>
                </div>
            </div>

            <div class="align-items-center">
                <div class="position-relative w-100 px-0 mb-2">
                    <select class="form-control py-0 ps-5 select2 filter-selection" id="filter-staff" data-filter="staff" data-filter-name="Created by">
                        <option value="">All Salespeople</option>
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
                <div class="position-relative w-100 px-0">
                    <div class="mb-2">
                        <select id="paid_status_select" name="paid_status" class="form-select select2 filter-selection" style="width: 100%;" data-filter="paid-status" data-filter-name="Paid Status">
                            <option value="">All Paid Status</option>
                            <option value="not_paid">Not Paid</option>
                            <option value="paid_in_part">Paid in Part</option>
                            <option value="paid_in_full">Paid in Full</option>
                        </select>
                    </div>
                </div>
                <select class="form-control py-0 ps-5 select2 filter-selection" id="filter-station" data-filter="station" data-filter-name="Station">
                    <option value="">All Stations</option>
                    <optgroup label="Stations">
                        <?php
                        $query_station = "SELECT * FROM station ORDER BY `station_name` ASC";
                        $result_station = mysqli_query($conn, $query_station);
                        while ($row_station = mysqli_fetch_array($result_station)) {
                        ?>
                            <option value="<?= $row_station['station_id'] ?>"><?= $row_station['station_name'] ?></option>
                        <?php
                        }
                        ?>
                    </optgroup>
                </select>
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
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?></h4>
                        <div class="table-responsive">
                            <table id="sales_table" class="table table-hover mb-0 text-md-wrap text-center">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Payment Method</th>
                                        <th>Station</th>
                                        <th>Cashier</th>
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
</div>

<div class="modal" id="view_order_details_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">Order Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="order-details">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $("#customer_search").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/sales_report_ajax.php",
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
            url: 'pages/sales_report_ajax.php',
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
        var pdfUrl = '';
        var isPrinting = false;
        var print_order_id = '';

        document.title = "<?= $page_title ?>";

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

        $(document).on('click', '.btn-show-pdf', function (e) {
            e.preventDefault();

            print_order_id = $(this).data('id');

            pdfUrl = $(this).attr('href');
            document.getElementById('pdfFrame').src = pdfUrl;
            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();

            const type = $(this).data('type');
            $('.pricing-btn').addClass('d-none');
            $('.pricing-btn[data-id="1"]').removeClass('d-none');

            if (type && type != 1) {
                $(`.pricing-btn[data-id="${type}"]`).removeClass('d-none');
            }
        });

        $(document).on('click', '#view_customer_pricing', function(e) {
            e.preventDefault();

            const pricing_id = $(this).data('id');
            const $iframe = $('#pdfFrame');

            const baseUrl = 'print_order_product.php';
            const params = new URLSearchParams();
            params.set('id', print_order_id);
            params.set('pricing_id', pricing_id);

            const newSrc = baseUrl + '?' + params.toString();
            $iframe.attr('src', newSrc);
        });

        $(document).on('click', '#downloadExcelBtn', function () {
            window.open('pages/sales_report_ajax.php?download_excel=1', '_blank');
        });

        $(document).on('click', '#downloadPDFBtn', function () {
            window.open('pages/sales_report_ajax.php?download_pdf=1', '_blank');
        });

        $(document).on('click', '#PrintBtn', function () {
            window.open('pages/sales_report_ajax.php?print_result=1', '_blank');
        });

        $('#printBtn').on('click', function () {
            if (isPrinting) {
                return;
            }

            isPrinting = true;
            const $iframe = $('#pdfFrame');

            $iframe.off('load').one('load', function () {
                try {
                    this.contentWindow.focus();
                    this.contentWindow.print();
                } catch (e) {
                    alert("Failed to print PDF.");
                }
                isPrinting = false;
            });

            $iframe.attr('src', pdfUrl);

            const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();
        });

        $('#downloadBtn').on('click', function () {
            const link = document.createElement('a');
            link.href = pdfUrl;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        function updateSelectedTags() {
            const displayDiv = $('#selected-tags');
            displayDiv.empty();

            $('.filter-selection').each(function () {
                const $select = $(this);
                const selectedOptions = $select.find('option:selected');
                const filterName = $select.data('filter-name');
                const isMultiple = $select.prop('multiple');

                selectedOptions.each(function () {
                    const selectedText = $(this).text().trim();
                    const selectedValue = $(this).val();

                    if (selectedValue) {
                        const tagId = `${$select.attr('id')}-${selectedValue}`;

                        displayDiv.append(`
                            <div class="d-inline-block p-1 m-1 border rounded bg-light tag-item" id="${tagId}">
                                <span class="text-dark">${filterName}: ${selectedText}</span>
                                <button type="button" 
                                    class="btn-close btn-sm ms-1 remove-tag" 
                                    style="width: 0.75rem; height: 0.75rem;" 
                                    aria-label="Close" 
                                    data-select="#${$select.attr('id')}" 
                                    data-value="${selectedValue}">
                                </button>
                            </div>
                        `);
                    }
                });
            });

            $('.remove-tag').on('click', function () {
                const selectId = $(this).data('select');
                const valueToRemove = $(this).data('value');
                const $select = $(selectId);

                if ($select.prop('multiple')) {
                    $select.find(`option[value="${valueToRemove}"]`).prop('selected', false);
                } else {
                    $select.val('');
                }

                $select.trigger('change');
                $(this).closest('.tag-item').remove();
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
            const paid_status = $('#paid_status_select').val();
            const station = $('#filter-station').val();

            $.ajax({
                url: 'pages/sales_report_ajax.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    customer_name,
                    date_from,
                    date_to,
                    month_select,
                    year_select,
                    staff,
                    tax_status,
                    paid_status,
                    station,
                    search_orders: 'search_orders'
                },
                success: function (response) {
                    if ($.fn.DataTable.isDataTable('#sales_table')) {
                        $('#sales_table').DataTable().clear().destroy();
                    }

                    const table = $('#sales_table').DataTable({
                        pageLength: 100,
                        order: []
                    });

                    $('#sales_table_filter').hide();
                    table.clear();

                    if (response.orders && response.orders.length > 0) {
                        response.orders.forEach(order => {
                            table.row.add([
                                `SO-${order.orderid}`,
                                order.formatted_date,
                                order.customer_name,
                                `$ ${parseFloat(order.amount).toFixed(2)}`,
                                `$ ${parseFloat(order.paid).toFixed(2)}`,
                                `$ ${parseFloat(order.balance).toFixed(2)}`,
                                order.status,
                                order.payment_method,
                                order.station,
                                order.cashier
                            ]);
                        });

                        table.draw();

                        const totalAmount = parseFloat(response.total_amount || 0).toFixed(2);
                        const totalPaid = parseFloat(response.total_paid || 0).toFixed(2);
                        const totalBalance = parseFloat(response.total_balance || 0).toFixed(2);

                        $('#sales_table tfoot').html(`
                            <tr class="fw-bold text-end">
                                <td colspan="3">TOTALS</td>
                                <td>$ ${totalAmount}</td>
                                <td>$ ${totalPaid}</td>
                                <td>$ ${totalBalance}</td>
                                <td colspan="4"></td>
                            </tr>
                        `);
                    } else {
                        $('#sales_table tfoot').html('');
                    }
                },
                error: function (xhr, status, error) {
                    alert('Error: ' + status + ' - ' + xhr.responseText);
                }
            });

            updateSelectedTags();
        }

        $(document).on('change', '#customer_search, #date_from, #date_to, .filter-selection', function(event) {
            performSearch();
        });

        $(document).on('click', '#view_order_details', function(event) {
            var orderid = $(this).data('id');
            loadOrderDetails(orderid);
            $('#view_order_details_modal').modal('toggle');
        });

        performSearch();
    });
</script>