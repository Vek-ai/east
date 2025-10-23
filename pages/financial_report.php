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

            <div class="position-relative w-100 px-0 mb-2">
                <select id="business_status_select" name="business_status" class="form-select select2-business-status filter-selection select2" data-filter="business-status" data-filter-name="Business Day Status">
                    <option value="">All Business Day Status</option>
                    <option value="Open">Open</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>

            <div class="position-relative w-100 px-0 mb-2">
                <select id="daily_status_select" name="daily_status" class="form-select select2-daily-status filter-selection select2" data-filter="daily-status" data-filter-name="Daily Status">
                    <option value="">All Business Day Status</option>
                    <option value="Completed">Completed</option>
                    <option value="In Operation">In Operation</option>
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
          <div class="card">
            <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?></h4>
                <div class="datatables">
                    <div class="table-responsive">
                        <table id="order_list_tbl" class="table table-hover mb-0 text-center">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day of Week</th>
                                    <th>Business Day Status</th>
                                    <th>Total Transactions</th>
                                    <th>Daily Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $query = "
                                    SELECT 
                                        DATE(`date`) AS transaction_date,
                                        COUNT(*) AS total_transactions
                                    FROM cash_flow
                                    GROUP BY DATE(`date`)
                                    ORDER BY transaction_date DESC
                                ";
                                $result = mysqli_query($conn, $query);

                                if ($result && mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $date = $row['transaction_date'];
                                        $total_transactions = $row['total_transactions'];

                                        $formatted_date = date('M. jS, Y', strtotime($date));

                                        $day_of_week = date('l', strtotime($date));

                                        $day_num = date('N', strtotime($date));
                                        $business_status = ($day_num >= 1 && $day_num <= 5) ? 'Open' : 'Closed';

                                        $check_summary = "
                                            SELECT 1 FROM cash_flow_summary 
                                            WHERE closing_date = '$date'
                                            LIMIT 1
                                        ";
                                        $summary_result = mysqli_query($conn, $check_summary);
                                        $exists_in_summary = mysqli_num_rows($summary_result) > 0;

                                        if ($exists_in_summary) {
                                            $daily_status = 'Completed';
                                        } elseif ($date == date('Y-m-d')) {
                                            $daily_status = 'Operational';
                                        } else {
                                            $daily_status = 'Pending Completion';
                                        }
                                        ?>
                                        <tr 
                                            data-month="<?= date('m', strtotime($date)) ?>"
                                            data-year="<?= date('Y', strtotime($date)) ?>"
                                            data-day="<?= date('d', strtotime($date)) ?>"
                                            data-business-status="<?= $business_status ?>"
                                            data-daily-status="<?= $daily_status ?>"
                                        >
                                            <td><?= htmlspecialchars($formatted_date) ?></td>
                                            <td><?= htmlspecialchars($day_of_week) ?></td>
                                            <td><?= htmlspecialchars($business_status) ?></td>
                                            <td><?= htmlspecialchars($total_transactions) ?></td>
                                            <td><?= htmlspecialchars($daily_status) ?></td>
                                            <td class="text-center">
                                                <a href="javascript:void(0)" class="text-decoration-none p-0 me-1" id="view_report" data-date="<?= $date ?>" title="View">
                                                    <iconify-icon icon="solar:eye-outline" width="20"></iconify-icon>
                                                </a>

                                                <a href="javascript:void(0)" class="text-decoration-none p-0 me-1" id="view_cash_flow" data-date="<?= $date ?>" title="Cash Flow">
                                                    <iconify-icon icon="solar:wad-of-money-outline" width="20" class="text-warning"></iconify-icon>
                                                </a>

                                                <a href="javascript:void(0)" class="text-decoration-none p-0 me-1" id="view_daily_sales" data-date="<?= $date ?>" title="Daily Sales">
                                                    <iconify-icon icon="solar:chart-outline" width="20" class="text-info"></iconify-icon>
                                                </a>

                                                <a href="javascript:void(0)" class="text-decoration-none p-0 me-1" id="view_receivable" data-date="<?= $date ?>" title="Accounts Receivable">
                                                    <iconify-icon icon="solar:clipboard-outline" width="20" class="text-primary"></iconify-icon>
                                                </a>

                                                <a href="javascript:void(0)" class="text-decoration-none p-0 me-1" id="view_print" data-id="<?= $date ?>" title="Print/Download">
                                                    <iconify-icon icon="solar:printer-outline" width="20" class="text-success"></iconify-icon>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="6" class="text-center text-muted">No cash flow records found.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="view_modal" tabindex="-1" aria-labelledby="view_modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cash_flow_modal" tabindex="-1" aria-labelledby="cash_flow_modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
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
    <div class="modal-dialog modal-dialog-centered modal-xl">
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
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receivableModalLabel">Daily Sales</h5>
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

    var table = $('#order_list_tbl').DataTable({
        "order": [],
        "pageLength": 100
    });

    $('#order_list_tbl_filter').hide();

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
            placeholder: "All Days",
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

    $('#month_select').on('change', function() {
        const selectedMonths = $(this).val();
        daySelect.empty();

        if (!selectedMonths || selectedMonths.length === 0) {
            daySelect.append(`<option value="">All Days</option>`);
            daySelect.trigger('change');
            return;
        }

        if (selectedMonths.length > 1) {
            for (let d = 1; d <= 31; d++) {
                daySelect.append(`<option value="${d}">${d}</option>`);
            }
            daySelect.trigger('change');
            return;
        }

        const month = parseInt(selectedMonths[0]);
        const year = $('#year_select').val()?.[0]
            ? parseInt($('#year_select').val()[0])
            : currentYear;

        const daysInMonth = new Date(year, month, 0).getDate();
        for (let d = 1; d <= daysInMonth; d++) {
            daySelect.append(`<option value="${d}">${d}</option>`);
        }

        daySelect.trigger('change');
    });

    $('#year_select').on('change', function() {
        $('#month_select').trigger('change');
    });

    $(document).on('click', '#view_report', function (e) {
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

    $(document).on('click', '#view_cash_flow', function (e) {
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

    $(document).on('click', '#view_daily_sales', function (e) {
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

    $(document).on('click', '#view_receivable', function (e) {
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
                var filterValue = $(this).val()?.toString() || '';
                var rowValue = row.data($(this).data('filter'))?.toString() || '';

                if (filterValue && filterValue !== '/' && rowValue !== filterValue) {
                    match = false;
                    return false;
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

    var today = new Date().toISOString().split('T')[0];
    $('#filter_date').val(today);
    filterTable();
});
</script>