<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Panel Multipliers";
?>
<style>
td.notes,  td.last-edit{
    white-space: normal;
    word-wrap: break-word;
}
.emphasize-strike {
    text-decoration: line-through;
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

.display_multiplier th, #display_multiplier td {
    text-align: center;
    vertical-align: middle;
}

#display_multiplier td {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

#display_multiplier td:hover {
    background-color:rgb(41, 63, 82);
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
              <a class="text-muted text-decoration-none" href="">Product Properties
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page"><?= $page_title ?></li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- <div class="card card-body">
    <div class="row">
      <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
          <button type="button" id="addModalBtn" class="btn btn-primary d-flex align-items-center" data-id="" data-type="add">
              <i class="ti ti-plus text-white me-1 fs-5"></i> Add <?= $page_title ?>
          </button>
      </div>
    </div>
</div> -->

<div class="card card-body">
  <div class="row">
      <div class="col-3" id="filter-panel" style="display: none;">
          <h3 class="card-title align-items-center mb-2">
              Filter <?= htmlspecialchars($page_title) ?>
          </h3>
          <div class="position-relative w-100 px-0 mr-0 mb-2">
              <input type="text" class="form-control py-2 ps-5" id="text-srh" placeholder="Search">
              <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
          </div>
      </div>
      <div class="col-12" id="table-panel">
        <button class="btn btn-outline-secondary mb-2" id="toggle-filter">
            <i class="ti ti-filter"></i> Toggle Filter
        </button>
        <div id="selected-tags" class="mb-2"></div>
        <div class="">
          <div class="card">
            <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= htmlspecialchars($page_title) ?> List</h4>
              <div class="table-responsive">
                <table id="display_multiplier" class="table display_multiplier table-bordered align-middle">
                  <thead>
                    <tr>
                        <th class="text-center">GAUGE</th>
                        <th class="text-center" colspan="3">29</th>
                        <th class="text-center" colspan="7">26</th>
                        <th class="text-center" colspan="4">24</th>
                        <th class="text-center" colspan="4">22</th>
                    </tr>
                    <tr>
                      <th>PROFILE</th>
                      <th>Corrugated</th>
                      <th>5V</th>
                      <th>Low-Rib</th>
                      <th>Board and Batten</th>
                      <th>Standing Seam 15</th>
                      <th>Standing Seam 175</th>
                      <th>Standing Seam 2</th>
                      <th>Board and Batten</th>
                      <th>R-panel</th>
                      <th>Hi-Rib/PBR</th>
                      <th>Standing Seam 15</th>
                      <th>Standing Seam 175</th>
                      <th>Standing Seam 2</th>
                      <th>Board and Batten</th>
                      <th>Standing Seam 15</th>
                      <th>Standing Seam 175</th>
                      <th>Standing Seam 2</th>
                      <th>Board and Batten</th>
                    </tr>
                    <tr>
                      <th>WIDTH</th>
                      <th colspan="2">28</th>
                      <th colspan="1">41</th>
                      <th>13.625</th>
                      <th colspan="4">20.5</th>
                      <th>41.625</th>
                      <th>43</th>
                      <th colspan="4">20</th>
                      <th colspan="4">20</th>
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

  <div class="row">
      <div class="col-3" id="filter-per-inch" style="display: none;">
          <h3 class="card-title align-items-center mb-2">
              Filter <?= htmlspecialchars($page_title) ?> SQ IN
          </h3>
          <div class="position-relative w-100 px-0 mr-0 mb-2">
              <input type="text" class="form-control py-2 ps-5" id="text-srh" placeholder="Search">
              <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
          </div>
      </div>
      <div class="col-12" id="table-per-inch">
        <button class="btn btn-outline-secondary mb-2" id="toggle-per-inch">
            <i class="ti ti-filter"></i> Toggle Filter
        </button>
        <div id="selected-tags" class="mb-2"></div>
        <div class="">
          <div class="card">
            <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= htmlspecialchars($page_title) ?> SQ IN</h4>
              <div class="table-responsive">
                <table id="display_multiplier_per_inch" class="table table-bordered table-striped align-middle display_multiplier">
                  <thead>
                    <tr>
                        <th class="text-center">GAUGE</th>
                        <th class="text-center" colspan="3">29</th>
                        <th class="text-center" colspan="7">26</th>
                        <th class="text-center" colspan="4">24</th>
                        <th class="text-center" colspan="4">22</th>
                    </tr>
                    <tr>
                      <th>PROFILE</th>
                      <th>Corrugated</th>
                      <th>5V</th>
                      <th>Low-Rib</th>
                      <th>Board and Batten</th>
                      <th>Standing Seam 15</th>
                      <th>Standing Seam 175</th>
                      <th>Standing Seam 2</th>
                      <th>Board and Batten</th>
                      <th>R-panel</th>
                      <th>Hi-Rib/PBR</th>
                      <th>Standing Seam 15</th>
                      <th>Standing Seam 175</th>
                      <th>Standing Seam 2</th>
                      <th>Board and Batten</th>
                      <th>Standing Seam 15</th>
                      <th>Standing Seam 175</th>
                      <th>Standing Seam 2</th>
                      <th>Board and Batten</th>
                    </tr>
                    <tr>
                      <th>WIDTH</th>
                      <th colspan="2">28</th>
                      <th colspan="1">41</th>
                      <th>13.625</th>
                      <th colspan="4">20.5</th>
                      <th>41.625</th>
                      <th>43</th>
                      <th colspan="4">20</th>
                      <th colspan="4">20</th>
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

  <div class="row">
      <div class="col-3" id="filter-markup" style="display: none;">
          <h3 class="card-title align-items-center mb-2">
              Filter <?= htmlspecialchars($page_title) ?> Markup
          </h3>
          <div class="position-relative w-100 px-0 mr-0 mb-2">
              <input type="text" class="form-control py-2 ps-5" id="text-srh" placeholder="Search">
              <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
          </div>
      </div>
      <div class="col-12" id="table-markup">
        <button class="btn btn-outline-secondary mb-2" id="toggle-markup">
            <i class="ti ti-filter"></i> Toggle Filter
        </button>
        <div id="selected-tags" class="mb-2"></div>
        <div class="">
          <div class="card">
            <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= htmlspecialchars($page_title) ?> Markup</h4>
              <div class="table-responsive">
                <table id="display_multiplier_markup" class="table table-bordered table-striped align-middle display_multiplier">
                  <thead>
                    <tr>
                        <th class="text-center">GAUGE</th>
                        <th class="text-center" colspan="3">29</th>
                        <th class="text-center" colspan="7">26</th>
                        <th class="text-center" colspan="4">24</th>
                        <th class="text-center" colspan="4">22</th>
                    </tr>
                    <tr>
                      <th>PROFILE</th>
                      <th>Corrugated</th>
                      <th>5V</th>
                      <th>Low-Rib</th>
                      <th>Board and Batten</th>
                      <th>Standing Seam 15</th>
                      <th>Standing Seam 175</th>
                      <th>Standing Seam 2</th>
                      <th>Board and Batten</th>
                      <th>R-panel</th>
                      <th>Hi-Rib/PBR</th>
                      <th>Standing Seam 15</th>
                      <th>Standing Seam 175</th>
                      <th>Standing Seam 2</th>
                      <th>Board and Batten</th>
                      <th>Standing Seam 15</th>
                      <th>Standing Seam 175</th>
                      <th>Standing Seam 2</th>
                      <th>Board and Batten</th>
                    </tr>
                    <tr>
                      <th>WIDTH</th>
                      <th colspan="2">28</th>
                      <th colspan="1">41</th>
                      <th>13.625</th>
                      <th colspan="4">20.5</th>
                      <th>41.625</th>
                      <th>43</th>
                      <th colspan="4">20</th>
                      <th colspan="4">20</th>
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

<div class="modal fade" id="editMultiplierModal" tabindex="-1" aria-labelledby="editMultiplierLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editMultiplierForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editMultiplierLabel">Edit Multiplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="modalColorGroup">
        <input type="hidden" id="modalGauge">
        <input type="hidden" id="modalProfile">
        <input type="hidden" id="modalWidth">
        <div class="mb-3">
          <label for="modalMultiplier" class="form-label">Multiplier</label>
          <input type="number" step="0.01" class="form-control" id="modalMultiplier" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
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
    const columns = [
        [29, 'Corrugated', 28],
        [29, '5V', 28],
        [29, 'Low-Rib', 41],
        [26, 'Board and Batten', 13.625],
        [26, 'Standing Seam 15', 20.5],
        [26, 'Standing Seam 175', 20.5],
        [26, 'Standing Seam 2', 20.5],
        [26, 'Board and Batten', 20.5],
        [26, 'R-panel', 41.625],
        [26, 'Hi-Rib/PBR', 43],
        [24, 'Standing Seam 15', 20],
        [24, 'Standing Seam 175', 20],
        [24, 'Standing Seam 2', 20],
        [24, 'Board and Batten', 20],
        [22, 'Standing Seam 15', 20],
        [22, 'Standing Seam 175', 20],
        [22, 'Standing Seam 2', 20],
        [22, 'Board and Batten', 20],
    ];
    let table;
    let selectedCell = null;

    function loadAndRenderMultiplierTables() {
        $.ajax({
            url: 'pages/panel_multiplier_ajax.php',
            data: { action: 'fetch_data' },
            dataType: 'json',
            success: function(data) {
                const colorGroups = [...new Set(data.map(item => item.color_group))];
                const dataMap = {};
                const basePerInchMap = {};

                data.forEach(({ color_group, gauge, profile, width, multiplier }) => {
                    dataMap[color_group] ??= {};
                    dataMap[color_group][gauge] ??= {};
                    dataMap[color_group][gauge][profile] ??= {};
                    dataMap[color_group][gauge][profile][width] = multiplier;
                });

                function destroyDataTables() {
                    ['#display_multiplier', '#display_multiplier_per_inch', '#display_multiplier_markup'].forEach(id => {
                        if ($.fn.DataTable.isDataTable(id)) {
                            $(id).DataTable().destroy();
                        }
                    });
                }

                function buildTableBody($tbody, valueCallback) {
                    $tbody.empty();
                    colorGroups.forEach(cg => {
                        const $tr = $('<tr>').append($('<td>').text(cg));
                        columns.forEach(([g, p, w]) => {
                            const val = valueCallback(cg, g, p, w);
                            $tr.append($('<td>').text(val).attr({
                                'data-cg': cg, 'data-g': g, 'data-p': p, 'data-w': w
                            }));
                        });
                        $tbody.append($tr);
                    });
                }

                // Destroy tables before updating DOM
                destroyDataTables();

                buildTableBody($('#display_multiplier tbody'), (cg, g, p, w) => {
                    return dataMap[cg]?.[g]?.[p]?.[w] ?? '-';
                });

                buildTableBody($('#display_multiplier_per_inch tbody'), (cg, g, p, w) => {
                    const val = dataMap[cg]?.[g]?.[p]?.[w];
                    return (val && w) ? (val / w).toFixed(4) : '-';
                });

                buildTableBody($('#display_multiplier_markup tbody'), (cg, g, p, w) => {
                    const current = dataMap[cg]?.[g]?.[p]?.[w];
                    if (!current || !w) return '-';

                    const currentPerInch = current / w;
                    let basePerInch = null;

                    if (p === 'Low-Rib') {
                        const base = dataMap['Standard']?.[g]?.[p]?.[w];
                        if (base) basePerInch = base / w;
                    } else {
                        const base = dataMap[cg]?.[g]?.['Low-Rib']?.[w];
                        if (base) basePerInch = base / w;
                    }

                    console.log(basePerInch);

                    if (basePerInch === null || basePerInch === 0) return '-';

                    const diff = (currentPerInch - basePerInch) / basePerInch;
                    if (!isFinite(diff)) return '-';

                    return (1 + diff).toFixed(4);
                });

                function setupDataTables() {
                    ['#display_multiplier', '#display_multiplier_per_inch', '#display_multiplier_markup'].forEach(id => {
                        $(id).DataTable({
                            pageLength: 100,
                            ordering: false
                        });
                        $(`${id}_filter`).hide();
                    });
                }

                // Reinitialize tables
                setupDataTables();
            },
            error: function(xhr) {
                console.warn('Response:', xhr.responseText);
                alert('Failed to load multiplier data.');
            }
        });
    }


    $(document).on('click', '#display_multiplier tbody td:not(:first-child)', function () {
        selectedCell = $(this);

        const cg = selectedCell.data('cg');
        const g = selectedCell.data('g');
        const p = selectedCell.data('p');
        const w = selectedCell.data('w');
        const val = selectedCell.text();

        $('#modalColorGroup').val(cg);
        $('#modalGauge').val(g);
        $('#modalProfile').val(p);
        $('#modalWidth').val(w);
        $('#modalMultiplier').val(val === '-' ? '' : val);

        const modal = new bootstrap.Modal(document.getElementById('editMultiplierModal'));
        modal.show();
    });

    $('#editMultiplierForm').on('submit', function (e) {
        e.preventDefault();

        const cg = $('#modalColorGroup').val();
        const g = $('#modalGauge').val();
        const p = $('#modalProfile').val();
        const w = $('#modalWidth').val();
        const m = $('#modalMultiplier').val();

        $.ajax({
            url: 'pages/panel_multiplier_ajax.php',
            method: 'POST',
            data: {
                action: 'save_multiplier',
                color_group: cg,
                gauge: g,
                profile: p,
                width: w,
                multiplier: m
            },
            success: function (res) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editMultiplierModal'));
                modal.hide();
                loadAndRenderMultiplierTables();
                alert(res);
            },
            error: function (xhr) {
                alert('Save failed: ' + xhr.responseText);
            }
        });
    });

    document.title = "<?= $page_title ?>";

    $('#multiplierForm').on('submit', function(event) {
        event.preventDefault(); 
        var userid = getCookie('userid');
        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);
        var appendResult = "";
        $.ajax({
            url: 'pages/panel_multiplier_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              $('.modal').modal("hide");
              if (response === "Multiplier updated successfully.") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text(response);
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    window.location.href = "?page=supplier_type";
                  });
              } else if (response === "New Multiplier added successfully.") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text(response);
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                      location.reload();
                  });
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

    $(document).on('click', '#toggle-filter', function(event) {
      $('#filter-panel').toggle();

      if ($('#filter-panel').is(':visible')) {
        $('#table-panel').removeClass('col-12').addClass('col-9');
      } else {
        $('#table-panel').removeClass('col-9').addClass('col-12');
      }
    });

    $(document).on('click', '#toggle-per-inch', function(event) {
      $('#filter-per-inch').toggle();

      if ($('#filter-per-inch').is(':visible')) {
        $('#table-per-inch').removeClass('col-12').addClass('col-9');
      } else {
        $('#table-per-inch').removeClass('col-9').addClass('col-12');
      }
    });

    $(document).on('click', '#toggle-markup', function(event) {
      $('#filter-markup').toggle();

      if ($('#filter-markup').is(':visible')) {
        $('#table-markup').removeClass('col-12').addClass('col-9');
      } else {
        $('#table-markup').removeClass('col-9').addClass('col-12');
      }
    });

    function filterTable() {
        var textSearch = $('#text-srh').val().toLowerCase();
        var isActive = $('#toggleActive').is(':checked');

        $.fn.dataTable.ext.search = [];

        if (textSearch) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
            });
        }

        table.draw();
        updateSelectedTags();
    }

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

    $(document).on('input change', '#text-srh, #toggleActive, .filter-selection', filterTable);

    loadAndRenderMultiplierTables();
});
</script>