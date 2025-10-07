<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Return/Refund List";

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
                <li class="breadcrumb-item text-muted" aria-current="page">Return/Refund List</li>
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
                                <?php if (showCol('invoice_no')): ?>
                                    <th>Invoice #</th>
                                <?php endif; ?>

                                <?php if (showCol('customer')): ?>
                                    <th>Customer</th>
                                <?php endif; ?>

                                <?php if (showCol('product_amount')): ?>
                                    <th>Product Amount</th>
                                <?php endif; ?>

                                <?php if (showCol('sale_date')): ?>
                                    <th>Date</th>
                                <?php endif; ?>

                                <?php if (showCol('sale_time')): ?>
                                    <th>Time</th>
                                <?php endif; ?>

                                <?php if (showCol('salesperson')): ?>
                                    <th>Salesperson</th>
                                <?php endif; ?>

                                <?php if (showCol('action')): ?>
                                    <th>Action</th>
                                <?php endif; ?>
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

<div class="modal fade custom-size" id="pdfModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Print/View Outputs</h5>
        <button type="button" class="close" data-bs-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <iframe id="pdfFrame" src="" style="height: 70vh; width: 100%;" class="mb-3 border rounded"></iframe>

        <div class="container mt-3 border rounded p-3" style="width: 100%;">
        
        <div class="mt-3 text-end">
            <button id="printBtn" class="btn btn-success me-2">Print</button>
            <button id="downloadBtn" class="btn btn-primary me-2">Download</button>
            <button class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
        </div>
        </div>

      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="sendOrderModal" tabindex="-1" aria-labelledby="sendOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendOrderModalLabel">Send Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h6 class="mb-3">How would you like to send the order to the customer?</h6>

                <form class="send_order_form d-flex flex-column flex-md-row align-items-center justify-content-center gap-2" method="post">
                    <input id="send_order_id" type="hidden" name="send_order_id" value="">
                    <input id="send_customer_id" type="hidden" name="send_customer_id" value="">

                    <select name="send_option" class="form-select form-select-sm w-auto">
                        <option value="email">Email</option>
                        <option value="sms">Text Message</option>
                        <option value="both">Both</option>
                    </select>

                    <button type="submit" class="btn btn-sm btn-primary">Send</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $("#customer_search").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/returns_list_ajax.php",
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
            url: 'pages/returns_list_ajax.php',
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

    function loadPendingDetails(orderid){
        $.ajax({
            url: 'pages/returns_list_ajax.php',
            type: 'POST',
            data: {
                orderid: orderid,
                fetch_pending_details: "fetch_pending_details"
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
                url: 'pages/returns_list_ajax.php',
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
                            let rowData = [];

                            <?php if (showCol('invoice_no')): ?>
                                rowData.push(order.orderid);
                            <?php endif; ?>

                            <?php if (showCol('customer')): ?>
                                rowData.push(order.customer_name);
                            <?php endif; ?>

                            <?php if (showCol('product_amount')): ?>
                                rowData.push(`$ ${parseFloat(order.amount).toFixed(2)}`);
                            <?php endif; ?>

                            <?php if (showCol('sale_date')): ?>
                                rowData.push(order.formatted_date);
                            <?php endif; ?>

                            <?php if (showCol('sale_time')): ?>
                                rowData.push(order.formatted_time);
                            <?php endif; ?>

                            <?php if (showCol('salesperson')): ?>
                                rowData.push(order.cashier);
                            <?php endif; ?>

                            <?php if (showCol('action')): ?>
                                let actionButtons = `
                                    <a href="javascript:void(0)" class="text-primary" id="view_order_details" data-id="${order.orderid}" title="View Details">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="print_return_product.php?id=${order.orderid}" 
                                        class="btn-show-pdf btn btn-danger-gradient btn-sm p-0 me-1" 
                                        data-id="${order.orderid}" data-bs-toggle="tooltip" 
                                        title="Print/Download">
                                            <i class="text-success fa fa-print fs-5"></i>
                                    </a>
                                `;
                                <?php if ($permission === 'edit'): ?>
                                    actionButtons += `
                                        <a href="javascript:void(0)" 
                                            class="me-1 email_order_btn" 
                                            data-customer="${order.customer_id}" 
                                            data-id="${order.orderid}" 
                                            title="Send to Customer">
                                                <iconify-icon icon="solar:plain-linear" class="fs-5 text-info"></iconify-icon>
                                        </a>
                                    `;
                                <?php endif; ?>
                                rowData.push(actionButtons);
                            <?php endif; ?>

                            table.row.add(rowData);
                        });

                        table.draw();

                        let footerCols = [];

                        <?php if (showCol('invoice_no') || showCol('customer') || showCol('product_amount')): ?>
                            footerCols.push(`<td colspan="2" class="text-end fw-bold">Total Orders:</td>`);
                        <?php else: ?>
                            footerCols.push(`<td colspan="0"></td>`);
                        <?php endif; ?>

                        <?php if (showCol('product_amount')): ?>
                            footerCols.push(`<td class="fw-bold">$ ${parseFloat(response.total_amount).toFixed(2)}</td>`);
                        <?php else: ?>
                            footerCols.push(`<td></td>`);
                        <?php endif; ?>

                        <?php if (showCol('sale_date') || showCol('sale_time')): ?>
                            footerCols.push(`<td colspan="2" class="text-end fw-bold">Total</td>`);
                        <?php endif; ?>

                        <?php if (showCol('salesperson')): ?>
                            footerCols.push(`<td class="text-end">${response.total_count}</td>`);
                        <?php endif; ?>

                        <?php if (showCol('action')): ?>
                            footerCols.push(`<td></td>`);
                        <?php endif; ?>

                        $('#returns_table tfoot').html(`<tr>${footerCols.join('')}</tr>`);

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

        $(document).on('click', '.email_order_btn', function () {
            const orderId = $(this).data('id');
            const customerId = $(this).data('customer');

            $('#send_order_id').val(orderId);
            $('#send_customer_id').val(customerId);

            $('#sendOrderModal').modal('show');
        });

        $(document).on('submit', '.send_order_form', function (e) {
            e.preventDefault();

            const $form = $(this);
            const formData = new FormData(this);
            formData.append('send_order', 'send_order');

            $.ajax({
                url: 'pages/returns_list_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    $form.find('button').prop('disabled', true).text('Sending...');
                },
                success: function (response) {
                    console.log(response);
                    let res = {};
                    try {
                        res = JSON.parse(response);
                    } catch (e) {
                        alert('Invalid response from server.');
                        return;
                    }

                    let msg = '';
                    if (res.results) {
                        if (res.results.email) {
                            msg += 'Email: ' + res.results.email.message + '\n';
                        }
                        if (res.results.sms) {
                            msg += 'SMS: ' + res.results.sms.message + '\n';
                        }
                    } else {
                        msg = res.message || 'Operation complete.';
                    }

                    alert(msg);
                },
                error: function () {
                    alert('Failed to send message.');
                },
                complete: function () {
                    $('.modal').modal('hide');
                }
            });
        });

        performSearch();
    });
</script>