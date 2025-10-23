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

        <div class="align-items-center filter_container">
            <div class="position-relative w-100 px-0 mb-2 d-none">
                <select class="form-control py-0 ps-5 select2 filter-selection" id="filter_station" data-filter="station" data-filter-name="Station">
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
            <div class="position-relative w-100 px-0 mb-2">
                <label for="date_select" class="form-label">Select Date</label>
                <input type="date" id="filter_date" name="date" class="form-control py-0 ps-5 filter-selection" data-filter="date" data-filter-name="Date">
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
                <div class="cash_flow_summary_tbl"></div>
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

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            dropdownParent: $(this).parent()
        });
    });

    function filterTable() {
        var date = $('#filter_date').val();
        var station = $('#filter_station').val();

        $.ajax({
            url: 'pages/cash_flow_summary_ajax.php',
            type: 'POST',
            data: {
                action: 'fetch_table',
                date: date,
                station: station
            },
            success: function(response) {
                try {
                    var res = JSON.parse(response);
                    $('.cash_flow_summary_tbl').html(res.data);
                } catch(e) {
                    console.error(response);
                    alert('Failed to parse response');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert('Failed to fetch table');
            }
        });

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

    $(document).on('input change', '#text-srh, .filter-selection', filterTable);

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