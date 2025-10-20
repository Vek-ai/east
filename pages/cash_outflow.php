<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Cash Outflow";

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

<?php                                                    
if ($permission === 'edit') {
?>
<div class="card card-body">
    <div class="row">
      <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
          <button type="button" id="addModalBtn" class="btn btn-primary d-flex align-items-center" data-id="" data-type="add">
              <i class="ti ti-plus text-white me-1 fs-5"></i> Add <?= $page_title ?>
          </button>
      </div>
    </div>
</div>
<?php
}
?>

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

        <hr class="my-3 border-dark opacity-75">

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
                            <th>Description</th>
                            <th>Note</th>
                            <th>Date Added</th>
                        </tr>
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

<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="add-header">
                    Add
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cashOutflowForm" class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                          <div id="add-fields" class=""></div>
                          <div class="form-actions">
                              <div class="border-top">
                                  <div class="row mt-2">
                                      <div class="col-6 text-start"></div>
                                      <div class="col-6 text-end ">
                                          <button type="submit" class="btn btn-primary" style="border-radius: 10%;">Save</button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                        </div>
                    </div>
                </div>
            </form>
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
        url: 'pages/cash_outflow_ajax.php',
        type: 'POST',
        data: { action: 'fetch_table' }
    },
    columns: [
        { data: 'description' },
        { data: 'notes' },
        { data: 'date_display' }
    ],
    createdRow: function(row, data, dataIndex) {
        $(row).attr('data-date', data.date);
    }
});
    
    $('#display_cash_flow_filter').hide();

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            dropdownParent: $(this).parent()
        });
    });

    $(document).on('click', '#addModalBtn', function(event) {
        event.preventDefault();
        var id = $(this).data('id') || '';
        var type = $(this).data('type') || '';

        if(type == 'edit'){
          $('#add-header').html('Update <?= $page_title ?>');
        }else{
          $('#add-header').html('Add <?= $page_title ?>');
        }

        $.ajax({
            url: 'pages/cash_outflow_ajax.php',
            type: 'POST',
            data: {
              id : id,
              action: 'fetch_modal_content'
            },
            success: function (response) {
                $('#add-fields').html(response);
                $('#addModal').modal('show');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.error('Response:', jqXHR.responseText);

                $('#responseHeader').text("Error");
                $('#responseMsg').text("An error occurred while processing your request.");
                $('#responseHeaderContainer').removeClass("bg-success").addClass("bg-danger");
                $('#response-modal').modal("show");
            }
        });
    });

    $('#cashOutflowForm').on('submit', function(event) {
        event.preventDefault(); 
        var formData = new FormData(this);
        formData.append('action', 'add_update');
        $.ajax({
            url: 'pages/cash_outflow_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              $('.modal').modal("hide");
              if (response.trim() === "update-success") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("<?= $page_title ?> updated successfully.");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");
                  table.ajax.reload(null, false);
              } else if (response.trim() === "add-success") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("New <?= $page_title ?> added successfully.");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");
                  table.ajax.reload(null, false);
              } else {
                  $('#responseHeader').text("Failed");
                  $('#responseMsg').text(response);
                  $('#responseHeaderContainer').removeClass("bg-success");
                  $('#responseHeaderContainer').addClass("bg-danger");
                  $('#response-modal').modal("show");
              }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

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
                var rowDate = new Date(rowDateStr);
                if (!isNaN(rowDate.getTime())) {
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

    filterTable();
});
</script>