<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Cash Flow";

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
            <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search">
            <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
        </div>
        <div class="position-relative w-100 px-0 mr-0 mb-2">
            <div class="input-daterange input-group mb-3" id="datepicker">
                <input type="text" class="form-control form-control-md px-1" id="date_from" name="start" placeholder="Start Date" autocomplete="off" />
                <span class="input-group-text py-1 px-2 small">to</span>
                <input type="text" class="form-control form-control-md px-1" id="date_to" name="end" placeholder="End Date" autocomplete="off" />
            </div>
        </div>

        <hr class="my-3 border-dark opacity-75">

        <div class="align-items-center filter_container">
            <!-- <div class="position-relative w-100 px-0 mb-2">
                <select class="form-control py-0 ps-5 select2 filter-selection" id="select-station" data-filter="station" data-filter-name="Station">
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
            </div> -->
            <div class="position-relative w-100 px-0 mb-2">
                <select id="month_select" name="month[]" multiple class="form-control py-0 ps-5 select2 filter-selection" style="width: 100%;" data-filter="month" data-filter-name="Month">
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
                <div class="mb-2">
                    <select id="year_select" name="year[]" multiple class="form-control py-0 ps-5 select2 filter-selection" style="width: 100%;" data-filter="year" data-filter-name="Year">
                    </select>
                </div>
            </div>

            <div class="position-relative w-100 px-0 mb-2">
                <select id="movement_type" class="form-control py-0 ps-5 select2 filter-selection" style="width: 100%;" data-filter="movement" data-filter-name="Movement Type">
                    <option value="">All Movement Types</option>
                    <option value="Cash Inflow">Cash Inflow</option>
                    <option value="Cash Outflow">Cash Outflow</option>
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
          <div class="card">
            <div class="card-body">
                    <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?> List</h4>
                    <div class="table-responsive">
                    <table id="display_cash_flow" class="table table-striped table-bordered align-middle text-center">
                    <thead>
                        <tr>
                        <th>Cashier</th>
                        <th>Payment Method</th>
                        <th>Movement Type</th>
                        <th>Cash Flow Type</th>
                        <th>Date</th>
                        <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" style="text-align:right">Total:</th>
                            <th style="text-align:center"></th>
                        </tr>
                    </tfoot>
                </table>
              </div>
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

    var table = $('#display_cash_flow').DataTable({
        pageLength: 100,
        ajax: {
            url: 'pages/cash_flow_ajax.php',
            type: 'POST',
            data: { action: 'fetch_table' }
        },
        columns: [
            { data: 'cashier' },
            { data: 'payment_method' },
            { data: 'movement_type' },
            { data: 'cash_flow_type' },
            { data: 'date_display' },
            { data: 'amount_display' },
        ],
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-station', data.station_id);
            $(row).attr('data-date', data.date);
            $(row).attr('data-month', data.month);
            $(row).attr('data-year', data.year);
            $(row).attr('data-amount', data.amount);
            $(row).attr('data-movement', data.movement_type);
        },
        drawCallback: function(settings) {
            let total = 0;

            $('#display_cash_flow tbody tr').each(function() {
                const amt = parseFloat($(this).attr('data-amount')) || 0;
                total += amt;
            });

            $('#display_cash_flow tfoot th:last').html(
                '$' + total.toLocaleString()
            );
        }
    });
    
    $('#display_cash_flow_filter').hide();

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

    const yearSelect = $('#year_select');
    const currentYear = new Date().getFullYear();
    const yearsBack = 15;
    for (let y = currentYear; y >= currentYear - yearsBack; y--) {
        yearSelect.append(new Option(y, y));
    }

    function filterTable() {
        var textSearch = $('#text-srh').val().toLowerCase();
        var isActive = $('#toggleActive').is(':checked');

        var dateFrom = $('#date_from').val() ? new Date($('#date_from').val()) : null;
        var dateTo = $('#date_to').val() ? new Date($('#date_to').val()) : null;

        var selectedMonths = $('#month_select').val() || [];
        var selectedYears = $('#year_select').val() || [];

        $.fn.dataTable.ext.search = [];

        if (textSearch) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
            });
        }

        if (isActive) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                return $(table.row(dataIndex).node()).find('a .alert').text().trim() === 'Active';
            });
        }

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var row = $(table.row(dataIndex).node());
            var match = true;

            $('.filter-selection').each(function() {
                var filterValue = $(this).val() || [];
                if (!Array.isArray(filterValue)) filterValue = [filterValue];
                var rowValue = row.data($(this).data('filter'))?.toString() || '';

                if (filterValue.length && !filterValue.includes('') && !filterValue.some(f => rowValue.includes(f))) {
                    match = false;
                    return false;
                }
            });

            var rowDateStr = row.data('date');
            if (rowDateStr) {
                var parts = rowDateStr.split('-');
                var rowDate = new Date(parts[0], parts[1] - 1, parts[2]);

                if (!isNaN(rowDate.getTime())) {
                    rowDate.setHours(0, 0, 0, 0);
                    if (dateFrom) dateFrom.setHours(0, 0, 0, 0);
                    if (dateTo) dateTo.setHours(0, 0, 0, 0);

                    if (dateFrom && rowDate < dateFrom) match = false;
                    if (dateTo && rowDate > dateTo) match = false;
                    if (selectedMonths.length && !selectedMonths.includes((rowDate.getMonth() + 1).toString())) match = false;
                    if (selectedYears.length && !selectedYears.includes(rowDate.getFullYear().toString())) match = false;
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

        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            var parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            return parts[1] + '-' + parts[2] + '-' + parts[0];
        }

        if (dateFrom || dateTo) {
            var dateText = formatDate(dateFrom) + ' to ' + formatDate(dateTo);
            displayDiv.append(`
                <div class="d-inline-block p-1 m-1 border rounded bg-light">
                    <span class="text-dark">Date Range: ${dateText}</span>
                    <button type="button" 
                        class="btn-close btn-sm ms-1 remove-tag" 
                        style="width: 0.75rem; height: 0.75rem;" 
                        aria-label="Close" 
                        data-select="#date_from,#date_to">
                    </button>
                </div>
            `);
        }

        $('.remove-tag').on('click', function() {
            var selectors = $(this).data('select').split(',');
            selectors.forEach(function(sel) {
                $(sel).val('').trigger('change');
            });
            $(this).parent().remove();
        });
    }

    $(document).on('input change', '#text-srh, #toggleActive, .filter-selection, #date_from, #date_to', filterTable);

    $(document).on('click', '.reset_filters', function () {
        $('.filter-selection').each(function () {
            $(this).val(null).trigger('change.select2');
        });

        $('#month_select, #year_select').val(null).trigger('change.select2');

        $('#text-srh, #date_from, #date_to').val('');

        filterTable();
    });

    const today = new Date().toISOString().split('T')[0];
    $('.input-daterange input[name="start"]').val(today);
    $('.input-daterange input[name="end"]').val(today);

    filterTable();
});
</script>