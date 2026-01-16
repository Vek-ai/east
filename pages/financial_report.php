<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Financial Report";

$permission = $_SESSION['permission'];
?>
<style>
td.notes,  td.last-edit{
    white-space: normal;
    word-wrap: break-word;
}
.emphasize-strike {
    text-decoration: strike-through;
    font-weight: bold;
    color: #9a841c;
  }
.dataTables_filter input {
    width: 100%;
    height: 50px;
    font-size: 16px;
    padding: 10px;
    border-radius: 5px;
}
.dataTables_filter {  width: 100%;}
#toggleActive {
    margin-bottom: 10px;
}

.inactive-row {
    display: none;
}

.datepicker table tr td,
.datepicker table tr th {
    color: #ffffffff !important;
}

.datepicker table tr td.active,
.datepicker table tr td.active:hover {
    background-color: #0d6efd !important;
    color: #fff !important;
}

.datepicker-dropdown {
    border: 1px solid #ccc !important; 
}

.select2-container--default .select2-results>.select2-results__options{
    max-height: 400px !important;
}
    </style>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div><br>
        <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title ?></h4>
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
      <div>
        <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
          
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card card-body">
  <div class="row">
      <div class="col-3" id="filterPanel">
        <h3 class="card-title align-items-center mb-2">
            Filter <?= $page_title ?>
        </h3>
        <div class="position-relative w-100 px-0 mr-0 mb-2">
            <input type="text" class="form-control py-2 ps-5" id="text-srh" placeholder="Search">
            <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
        </div>

        <div class="align-items-center filter_container">
            <div class="position-relative w-100 px-0 mb-2">
                <select id="month_select" name="month[]" multiple class="form-select select2-month filter-selection" data-filter="month" data-filter-name="Month">
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

            <div class="position-relative w-100 px-0 mb-2">
                <select id="year_select" name="year[]" multiple class="form-select select2-year filter-selection select2" data-filter="year" data-filter-name="Year"></select>
            </div>

            <div class="position-relative w-100 px-0 mb-2">
                <select id="day_select" name="day[]" multiple class="form-select select2-day filter-selection select2" data-filter="day" data-filter-name="Day"></select>
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
          <div class="card">
            <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?></h4>
                <div class="datatables">
                    <div class="table-responsive">
                        <table id="cashFlowDailyTable" class="table table-striped table-bordered w-100 text-center align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day of Week</th>
                                    <th>Total Transactions</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="view_modal" tabindex="-1" aria-labelledby="view_modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cashFlowModalLabel">View Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="view_modal_body">
                <div class="text-center py-3 text-muted">
                <i class="fa fa-spinner fa-spin me-2"></i> Loading...
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cash_flow_modal" tabindex="-1" aria-labelledby="cash_flow_modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cashFlowModalLabel">Cash Flow Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cash_flow_modal_body">
                <div class="text-center py-3 text-muted">
                <i class="fa fa-spinner fa-spin me-2"></i> Loading...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="daily_sales_modal" tabindex="-1" aria-labelledby="daily_sales_modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cashFlowModalLabel">Daily Sales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="daily_sales_modal_body">
                <div class="text-center py-3 text-muted">
                <i class="fa fa-spinner fa-spin me-2"></i> Loading...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="receivable_modal" tabindex="-1" aria-labelledby="receivableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receivableModalLabel">Accounts Receivable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="receivable_modal_body">
                <div class="text-center py-3 text-muted">
                <i class="fa fa-spinner fa-spin me-2"></i> Loading...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dateModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select Date Range</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="modalDateFrom" class="form-label">From:</label>
          <input type="date" id="modalDateFrom" class="form-control">
        </div>
        <div class="mb-3">
          <label for="modalDateTo" class="form-label">To:</label>
          <input type="date" id="modalDateTo" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button id="searchPdfBtn" class="btn btn-primary">Search</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade custom-size" id="pdfModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Print/View Outputs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <ul class="nav nav-tabs mb-3" id="pdfTabs">
          <li class="nav-item">
            <a class="nav-link" href="#" data-tab="dailySales">Daily Sales</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="#" data-tab="cashFlow">Cash Flow</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" data-tab="accountsReceivable">Accounts Receivable</a>
          </li>
        </ul>

        <iframe id="pdfFrame" src="" style="height: 60vh; width: 100%;" class="mb-3 border rounded"></iframe>

        <div class="container mt-3 border rounded p-3">
          <div class="text-end">
            <button id="printBtn" class="btn btn-success me-2">Print</button>
            <button id="downloadBtn" class="btn btn-primary me-2">Download</button>
            <button class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>

      </div>
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

<script>
  $(document).ready(function() {
    document.title = "<?= $page_title ?>";

    var table = $('#cashFlowDailyTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        order: [[0, 'desc']],
        ajax: {
            url: 'pages/financial_report_ajax.php',
            type: 'POST',
            data: function(d) {
                d.action = 'fetch_table';
                d.month = $('#month_select').val() || [];
                d.year = $('#year_select').val() || [];
                d.day = $('#day_select').val() || [];
                d.business_status = $('#business_status_select').val() || '';
                d.daily_status = $('#daily_status_select').val() || '';
            }
        },
        columns: [
            { data: 'formatted_date' },
            { data: 'day_of_week' },
            { data: 'total_transactions' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });


    $('#cashFlowDailyTable_filter').hide();

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            dropdownParent: $(this).parent()
        });
    });

    $(".select2-month").each(function() {
        $(this).select2({
            placeholder: "All Months",
            width: "100%",
            dropdownParent: $(this).parent()
        });
    });

    $(".select2-year").each(function() {
        $(this).select2({
            placeholder: "All Years",
            width: "100%",
            dropdownParent: $(this).parent()
        });
    });

    $(".select2-day").each(function() {
        $(this).select2({
            placeholder: "Day of Week",
            width: "100%",
            dropdownParent: $(this).parent()
        });
    });

    const currentYear = new Date().getFullYear();
    const yearSelect = $('#year_select');
    const daySelect = $('#day_select');

    for (let y = currentYear; y >= currentYear - 10; y--) {
        yearSelect.append(`<option value="${y}">${y}</option>`);
    }

    for (let d = 1; d <= 31; d++) {
        daySelect.append(`<option value="${d}">${d}</option>`);
    }

    $('#year_select').on('change', function() {
        $('#month_select').trigger('change');
    });

    $(document).on('click', '.view_report', function (e) {
        e.preventDefault();
        const date = $(this).data('date');

        $.ajax({
            type: 'POST',
            url: 'pages/financial_report_ajax.php',
            data: {
                date: date,
                action: 'fetch_view'
            },
            success: function (response) {
                $('#view_modal_body').html(response);
                $('#view_modal').modal('show');
            },
            error: function () {
                $('#responseHeader').text("Error");
                $('#responseMsg').text("Something went wrong. Please try again.");
                $('#responseHeaderContainer')
                    .removeClass("bg-success")
                    .addClass("bg-danger");
                $('#response-modal').modal("show");
            }
        });
    });

    $(document).on('click', '#close-btn', function () {
        const date = $(this).data('date');

        if (!date) {
            alert('Invalid date.');
            return;
        }

        if (!confirm(`Are you sure you want to close the station for ${date}?`)) {
            return;
        }

        $.ajax({
            url: 'pages/financial_report_ajax.php',
            type: 'POST',
            data: {
                action: 'close_station',
                date: date
            },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert('Station closed successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + (res.message || 'Could not close station.'));
                }
            },
            error: function (xhr, status, error) {
                console.log('XHR Response:', xhr.responseText);
                alert('AJAX error. Check console.');
            }
        });
    });

    $(document).on('click', '.view_cash_flow', function (e) {
        e.preventDefault();
        const date = $(this).data('date');

        $.ajax({
            type: 'POST',
            url: 'pages/financial_report_ajax.php',
            data: {
                date: date,
                action: 'fetch_cash_flow'
            },
            success: function (response) {
                $('#cash_flow_modal_body').html(response);
                $('#cash_flow_modal').modal('show');
            },
            error: function () {
                $('#responseHeader').text("Error");
                $('#responseMsg').text("Something went wrong. Please try again.");
                $('#responseHeaderContainer')
                    .removeClass("bg-success")
                    .addClass("bg-danger");
                $('#response-modal').modal("show");
            }
        });
    });

    $(document).on('click', '.view_daily_sales', function (e) {
        e.preventDefault();
        const date = $(this).data('date');

        $.ajax({
            type: 'POST',
            url: 'pages/financial_report_ajax.php',
            data: {
                date: date,
                action: 'fetch_daily_sales'
            },
            success: function (response) {
                $('#daily_sales_modal_body').html(response);
                $('#daily_sales_modal').modal('show');
            },
            error: function () {
                $('#responseHeader').text("Error");
                $('#responseMsg').text("Something went wrong. Please try again.");
                $('#responseHeaderContainer')
                    .removeClass("bg-success")
                    .addClass("bg-danger");
                $('#response-modal').modal("show");
            }
        });
    });

    $(document).on('click', '.view_receivable', function (e) {
        e.preventDefault();
        const date = $(this).data('date');

        $.ajax({
            type: 'POST',
            url: 'pages/financial_report_ajax.php',
            data: {
                date: date,
                action: 'fetch_receivable'
            },
            success: function (response) {
                $('#receivable_modal_body').html(response);
                $('#receivable_modal').modal('show');
            },
            error: function () {
                $('#responseHeader').text("Error");
                $('#responseMsg').text("Something went wrong. Please try again.");
                $('#responseHeaderContainer')
                    .removeClass("bg-success")
                    .addClass("bg-danger");
                $('#response-modal').modal("show");
            }
        });
    });

    function filterTable() {
        table.ajax.reload(null, false);
        updateSelectedTags();
    }

    function updateSelectedTags() {
        var displayDiv = $('#selected-tags');
        displayDiv.empty();

        $('.filter-selection').each(function() {
            var selectedOptions = $(this).find('option:selected');
            var filterName = $(this).data('filter-name');
            var selectedTexts = [];

            selectedOptions.each(function() {
                if ($(this).val()) selectedTexts.push($(this).text().trim());
            });

            if (selectedTexts.length) {
                displayDiv.append(`
                    <div class="d-inline-block p-1 m-1 border rounded bg-light">
                        <span class="text-dark">${filterName}: ${selectedTexts.join(', ')}</span>
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
            var selector = $(this).data('select');
            $(selector).val('').trigger('change');
            $(this).parent().remove();
        });
    }

    $(document).on('input change', '#text-srh, .filter-selection, .select2-day, .select2-month, .select2-year', filterTable);

    $(document).on('click', '.reset_filters', function () {
        $('.filter-selection').each(function () {
            $(this).val(null).trigger('change.select2');
        });

        $('#text-srh').val('');
        $('#filter_date').val('');

        filterTable();
    });

    let selectedPdfTab = 'cashFlow';
    let selectedDateRange = { from: '', to: '' };
    let pdfUrl = '';
    let isPrinting = false;

    $(document).on('click', '.view_print', function(e) {
        e.preventDefault();
        console.log(123);

        const date = $(this).data('id');

        $('#modalDateFrom').val(date);
        $('#modalDateTo').val(date);

        const modal = new bootstrap.Modal(document.getElementById('dateModal'));
        modal.show();
    });

    $('#searchPdfBtn').on('click', function() {
        selectedDateRange.from = $('#modalDateFrom').val();
        selectedDateRange.to = $('#modalDateTo').val();

        if (!selectedDateRange.from || !selectedDateRange.to) {
            alert('Please select both From and To dates.');
            return;
        }

        const smallModal = bootstrap.Modal.getInstance(document.getElementById('dateModal'));
        smallModal.hide();

        pdfUrl = getPdfUrl(selectedPdfTab, selectedDateRange.from, selectedDateRange.to);
        $('#pdfFrame').attr('src', pdfUrl);

        const bigModal = new bootstrap.Modal(document.getElementById('pdfModal'));
        bigModal.show();
    });

    $('#pdfTabs').on('click', 'a.nav-link', function(e) {
        e.preventDefault();
        $('#pdfTabs a.nav-link').removeClass('active bg-warning text-dark');
        $(this).addClass('active bg-warning text-dark');

        selectedPdfTab = $(this).data('tab');

        pdfUrl = getPdfUrl(selectedPdfTab, selectedDateRange.from, selectedDateRange.to);
        $('#pdfFrame').attr('src', pdfUrl);
    });

    function getPdfUrl(tab, from, to) {
        switch(tab) {
            case 'dailySales':
                return `/print_financial_daily_sales.php?from=${from}&to=${to}`;
            case 'cashFlow':
                return `/print_financial_cash_flow.php?from=${from}&to=${to}`;
            case 'accountsReceivable':
                return `/print_financial_statement_account.php?from=${from}&to=${to}`;
            default:
                return '';
        }
    }

    $('#printBtn').on('click', function () {
        if (isPrinting) return;
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
    });

    $('#downloadBtn').on('click', function () {
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    var today = new Date().toISOString().split('T')[0];
    $('#filter_date').val(today);
    filterTable();
});
</script>