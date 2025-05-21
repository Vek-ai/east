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
                <style>
                    #display_multiplier th, #display_multiplier td {
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
                <table id="display_multiplier" class="table table-bordered align-middle">
                  <thead>
                    <tr>
                        <th class="text-center">COLOR GROUP</th>
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

<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="add-header">
                    Add <?= $page_title ?>
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="multiplierForm" class="form-horizontal">
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

<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Upload <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="card">
                  <div class="card-body">
                      <form id="upload_excel_form" action="#" method="post" enctype="multipart/form-data">
                          <div class="mb-3">
                              <label for="excel_file" class="form-label fw-semibold">Select Excel File</label>
                              <input type="file" class="form-control" name="excel_file" accept=".xls,.xlsx" required>
                          </div>
                          <div class="text-center">
                              <button type="submit" class="btn btn-primary">Upload & Read</button>
                          </div>
                      </form>
                  </div>
              </div>
              <div class="card mb-0 mt-2">
                  <div class="card-body d-flex justify-content-center align-items-center">
                      <button type="button" id="readUploadBtn" class="btn btn-primary fw-semibold">
                          <i class="fas fa-eye me-2"></i> View Uploaded File
                      </button>
                  </div>
              </div>    
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="readUploadModal" tabindex="-1" aria-labelledby="readUploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Uploaded Excel <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div id="uploaded_excel" class="modal-body"></div>
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

    $.ajax({
        url: 'pages/panel_multiplier_ajax.php',
        data: { action: 'fetch_data' },
        dataType: 'json',
        success: function(data) {
            const colorGroups = [...new Set(data.map(item => item.color_group))];
            const dataMap = {};

            $.each(data, function(_, item) {
                if (!dataMap[item.color_group]) dataMap[item.color_group] = {};
                if (!dataMap[item.color_group][item.gauge]) dataMap[item.color_group][item.gauge] = {};
                if (!dataMap[item.color_group][item.gauge][item.profile]) dataMap[item.color_group][item.gauge][item.profile] = {};
                dataMap[item.color_group][item.gauge][item.profile][item.width] = item.multiplier;
            });

            const $tbody = $('#display_multiplier tbody').empty();

            $.each(colorGroups, function(_, cg) {
                const $tr = $('<tr>');
                $tr.append($('<td>').text(cg));
                $.each(columns, function(index, col) {
                    const [g, p, w] = col;
                    let val = dataMap[cg]?.[g]?.[p]?.[w] ?? '-';
                    const $td = $('<td>').text(val).attr({
                        'data-cg': cg,
                        'data-g': g,
                        'data-p': p,
                        'data-w': w
                    });
                    $tr.append($td);
                });
                $tbody.append($tr);
            });

            if ($.fn.DataTable.isDataTable('#display_multiplier')) {
                table.destroy();
            }

            table = $('#display_multiplier').DataTable({
                pageLength: 100,
                ordering: false
            });

            $('#display_multiplier_filter').hide();
        },
        error: function(xhr) {
            console.warn('Response:', xhr.responseText);
            alert('Failed to load multiplier data.');
        }
    });

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
                if (selectedCell) {
                    selectedCell.text(parseFloat(m).toFixed(2)); // update the cell visually
                }
                alert(res);
            },
            error: function (xhr) {
                alert('Save failed: ' + xhr.responseText);
            }
        });
    });


    document.title = "<?= $page_title ?>";

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

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
            url: 'pages/panel_multiplier_ajax.php',
            type: 'POST',
            data: {
              id : id,
              action: 'fetch_modal_content'
            },
            success: function (response) {
                $('#add-fields').html(response);

                $(".select2-form").each(function () {
                    $(this).select2({
                        width: '100%',
                        dropdownParent: $(this).parent(),
                        templateResult: formatOption,
                        templateSelection: formatOption
                    });
                });

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

    $(document).on('click', '#toggle-filter', function(event) {
      $('#filter-panel').toggle();

      if ($('#filter-panel').is(':visible')) {
        $('#table-panel').removeClass('col-12').addClass('col-9');
      } else {
        $('#table-panel').removeClass('col-9').addClass('col-12');
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

    function updateSearchCategory() {
        let selectedCategory = $('#select-category option:selected').data('category');
        let hasCategory = !!selectedCategory;

        $('.search-category').each(function () {
            let $select2Element = $(this);

            if (!$select2Element.data('all-options')) {
                $select2Element.data('all-options', $select2Element.find('option').clone(true));
            }

            let allOptions = $select2Element.data('all-options');

            $select2Element.empty();

            if (hasCategory) {
                allOptions.each(function () {
                    let optionCategory = $(this).data('category');
                    if (String(optionCategory) === String(selectedCategory)) {
                        $select2Element.append($(this).clone(true));
                    }
                });
            } else {
                allOptions.each(function () {
                    $select2Element.append($(this).clone(true));
                });
            }

            $select2Element.select2('destroy');

            let parentContainer = $select2Element.parent();
            $select2Element.select2({
                width: '100%',
                dropdownParent: parentContainer
            });
        });

        $('.category_selection').toggleClass('d-none', !hasCategory);
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
    
    //filterTable();
});
</script>