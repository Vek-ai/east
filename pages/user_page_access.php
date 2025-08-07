<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$query = "SELECT id, page_name FROM pages";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $originalName = $row['page_name'];

    $formatted = ucwords(str_replace('_', ' ', strtolower($originalName)));

    $update = "UPDATE pages SET page_name = '" . mysqli_real_escape_string($conn, $formatted) . "' WHERE id = $id";
    mysqli_query($conn, $update);
}

$page_title = "User Permissions Management";
?>
<style>
    .dataTables_filter input {
        width: 100%;
        height: 50px;
        font-size: 16px;
        padding: 10px;
        border-radius: 5px;
    }
    .dataTables_filter { width: 100%;}
    #toggleActive {
        margin-bottom: 10px;
    }

    .form-check-input.access-toggle {
        border-radius: 50%;
        width: 1.2em;
        height: 1.2em;
        border: 2px solid #6c757d;
    }

    .form-check-input.access-toggle:checked {
        background-color: #198754; /* green */
        border-color: #198754;
    }

    #pages_table td:not(:first-child),
    #pages_table th:not(:first-child) {
        text-align: center;
        vertical-align: middle;
    }
</style>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div><br>
        <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title ?></h4>
      </div>
    </div>
  </div>
</div>

<div class="card card-body">
  <div class="row align-items-end">
    <div class="col-12 mb-3">
      <h4 class="mb-0 d-flex align-items-center">
        <span class="me-2" style="font-size: 1.4rem;">
          <i class="fas fa-user"></i>
        </span>
        Select User
      </h4>
    </div>

    <div class="col-md-4">
      <label for="staff_filter" class="form-label">Choose user to manage</label>
      <div class="mb-3 mb-md-0">
        <select id="staff_filter" class="form-select select2">
          <option value="" hidden selected>Select User</option>
          <optgroup label="Staff List">
            <?php
              $staffQuery = "SELECT staff_id, email FROM staff ORDER BY staff_fname ASC";
              $staffResult = mysqli_query($conn, $staffQuery);
              if ($staffResult && mysqli_num_rows($staffResult) > 0) {
                  while ($staffRow = mysqli_fetch_assoc($staffResult)) {
                      $staff_id = $staffRow['staff_id'];
                      $email = $staffRow['email'];
                      $staff_name_only = get_staff_name($staff_id);
                      $display_name = $staff_name_only;
                      if (!empty($email)) {
                          $display_name .= " ({$email})";
                      }
                      echo "<option value='{$staff_id}' data-name='" . htmlspecialchars($staff_name_only, ENT_QUOTES) . "'>{$display_name}</option>";
                  }
              }
            ?>
          </optgroup>
        </select>
      </div>
    </div>

    <div class="col-md-5 d-flex justify-content-center align-items-end">
      <div class="d-flex justify-content-center gap-3 flex-wrap">
        <div class="d-flex align-items-center">
          <i class="fas fa-circle text-success me-1"></i> Can Edit
        </div>
        <div class="d-flex align-items-center">
          <i class="fas fa-circle text-primary me-1"></i> View Only
        </div>
        <div class="d-flex align-items-center">
          <i class="fas fa-circle me-1" style="color: #6c757d;"></i> No Access
        </div>
      </div>
    </div>

    <div class="col-md-3 d-flex justify-content-md-end justify-content-center align-items-end mt-3 mt-md-0">
      <button type="submit" id="load_permissions" class="btn btn-primary d-flex align-items-center" data-id="" data-type="add">
        <i class="ti ti-plus text-white me-1 fs-5"></i> Load Permissions
      </button>
    </div>
  </div>
</div>

<div id="pages_section" class="card card-body d-none">
  <div class="card-header d-flex justify-content-between align-items-center px-2 py-3">
    <h3 class="card-title mb-0">
        <i class="fas fa-cog"></i> Page Permission (<span id="selected-staff-name">Staff Name</span>)
    </h3>
    <div class="btn-group">
      <button type="button" class="btn btn-outline-success" id="grant_all_btn">
        <i class="fas fa-check me-1"></i> Grant All
      </button>
      <button type="button" class="btn btn-outline-danger" id="revoke_all_btn">
        <i class="fas fa-times me-1"></i> Revoke All
      </button>
      <button type="button" class="btn btn-primary" id="save_changes_btn">
        <i class="fas fa-save me-1"></i> Save Changes
      </button>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="datatables">
        <div class="card">
          <div class="card-body">
            <div class="table-responsive">
              <table id="pages_table" class="table table-striped table-bordered align-middle w-100">
                <thead>
                  <tr>
                    <th>Page/URL</th>
                    <th>Category</th>
                    <th>Access</th>
                    <th>Permission Level</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>

                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" id="pages_summary" class="fw-semibold text-start text-primary"></td>
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

<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="add-header">
                    Add <?= $page_title ?>
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pageForm" class="form-horizontal">
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

<script>
  $(document).ready(function() {
    document.title = "<?= $page_title ?>";

    let tableInitialized = false;
    let table;

    function initializePagesTable() {
        table = $('#pages_table').DataTable({
            pageLength: 100,
            ajax: {
                url: 'pages/user_page_access_ajax.php',
                type: 'POST',
                data: function(d) {
                    d.action = 'fetch_table';
                    d.staff_id = $('#staff_filter').val();
                },
                dataSrc: function (json) {
                    const counts = json.counts || {};
                    const summary = `
                        <span class="text-dark">${counts.total || 0}</span> Pages &nbsp;&nbsp;
                        <span class="text-success">${counts.with_access || 0}</span> With Access &nbsp;&nbsp;
                        <span class="text-primary">${counts.view_only || 0}</span> View Only &nbsp;&nbsp;
                        <span class="text-success">${counts.can_edit || 0}</span> Can Edit
                    `;
                    $('#pages_summary').html(summary);

                    return json.data;
                }
            },
            order: [],
            columns: [
                { data: 'page' },
                { data: 'category' },
                { data: 'access' },
                { data: 'permission' },
                { data: 'status' }
            ]
        });

        $('#pages_table').on('error.dt', function (e, settings, techNote, message) {
            alert('Failed to load data.');
            console.error('DataTables error:', message);
        });
    }

    $(document).on('change', '.access-toggle', function () {
        const hasAccess = $(this).is(':checked');
        const pageId = $(this).data('page-id');
        const radios = $(`input[name='permission_${pageId}']`);

        if (hasAccess) {
            radios.prop('disabled', false);
            if (!radios.is(':checked')) {
                radios.filter('[value="view"]').prop('checked', true);
            }
        } else {
            radios.prop('disabled', true).prop('checked', false);
        }
    });

    $(document).on('click', '#grant_all_btn', function () {
        table.rows().every(function () {
            const row = this.node();
            const $toggle = $(row).find('.access-toggle');
            const pageId = $toggle.data('page-id');
            const $radios = $(`input[name='permission_${pageId}']`, row);

            $toggle.prop('checked', true);
            $radios.prop('disabled', false);
            if (!$radios.is(':checked')) {
                $radios.filter('[value="view"]').prop('checked', true);
            }
        });
    });

    $(document).on('click', '#revoke_all_btn', function () {
        table.rows().every(function () {
            const row = this.node();
            const $toggle = $(row).find('.access-toggle');
            const pageId = $toggle.data('page-id');
            const $radios = $(`input[name='permission_${pageId}']`, row);

            $toggle.prop('checked', false);
            $radios.prop('disabled', true).prop('checked', false);
        });
    });

    $(document).on('click', '#save_changes_btn', function () {
        const updates = [];

        table.rows().every(function () {
            const row = this.node();
            const $accessToggle = $(row).find('.access-toggle');
            const pageId = $accessToggle.data('page-id');
            const hasAccess = $accessToggle.is(':checked');
            let permission = null;

            if (hasAccess) {
                permission = $(row).find(`input[name='permission_${pageId}']:checked`).val() || 'view';
            }

            updates.push({
                page_id: pageId,
                has_access: hasAccess,
                permission: permission
            });
        });

        const staffId = $('#staff_filter').val();

        $.ajax({
            url: 'pages/user_page_access_ajax.php',
            method: 'POST',
            data: {
                action: 'save_changes',
                staff_id: staffId,
                updates: JSON.stringify(updates)
            },
            success: function (response) {
                alert('Changes saved successfully.');
                table.ajax.reload(null, false);
            },
            error: function (xhr, status, error) {
                console.error('Save failed:', error);
                alert('Failed to save changes.');
            }
        });
    });

    $(document).on('change', '#staff_filter', function () {
        const selected = $(this).val();
        const selectedName = $(this).find('option:selected').data('name');
        $('#selected-staff-name').text(selectedName);

        if (selected !== '') {
            $('#pages_section').removeClass('d-none');

            if (!tableInitialized) {
                initializePagesTable();
                $('#pages_table_filter').hide();
                tableInitialized = true;
            } else {
                table.ajax.reload(null, false);
            }

            setTimeout(() => {
                table.columns.adjust().draw();
            }, 200);
        } else {
            $('#pages_section').addClass('d-none');
        }
    });

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            dropdownParent: $(this).parent()
        });
    });

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

    $('#pageForm').on('submit', function(event) {
        event.preventDefault(); 
        var userid = getCookie('userid');
        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);
        $.ajax({
            url: 'pages/user_page_access_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              $('.modal').modal("hide");
              if (response === "success_update") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("<?= $page_title ?> updated successfully.");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");
                  table.ajax.reload(null, false);
              } else if (response === "success_add") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("New <?= $page_title ?> added successfully.");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");
                  table.ajax.reload(null, false);
              } else {
                  $('#responseHeader').text("Failed");
                  $('#responseMsg').text("Failed to modify page category.");
                  $('#responseHeaderContainer').removeClass("bg-success");
                  $('#responseHeaderContainer').addClass("bg-danger");
                  $('#response-modal').modal("show");
                  console.log(response);
                  table.ajax.reload(null, false);
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
            url: 'pages/user_page_access_ajax.php',
            type: 'POST',
            data: {
              id : id,
              action: 'fetch_modal_content'
            },
            success: function (response) {
                $('#add-fields').html(response);
                $(".select2").each(function () {
                    $(this).select2({
                        width: '100%',
                        dropdownParent: $(this).parent()
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
    
    $(document).on('change', '#form_category_id', function () {
        const pageDetails = $('#page_details');
        if ($(this).val() !== '') {
            pageDetails.removeClass('d-none');
        } else {
            pageDetails.addClass('d-none');
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

        if (isActive) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                return $(table.row(dataIndex).node()).find('a .alert').text() === 'Active';
            });
        }

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var row = $(table.row(dataIndex).node());
            var match = true;

            $('.filter-selection').each(function() {
                var filterValue = $(this).val()?.toString().toLowerCase() || '';
                var rowValue = row.data($(this).data('filter'))?.toString().toLowerCase() || '';

                if (filterValue && filterValue !== '/') {
                    if (!rowValue.includes(filterValue)) {
                        match = false;
                        return false;
                    }
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
            var selectedOption = $(this).find('option:selected');
            var selectedText = selectedOption.text();
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

    $(document).on('click', '.reset_filters', function () {
        $('.filter-selection').each(function () {
            $(this).val(null).trigger('change.select2');
        });

        $('#text-srh').val('');

        filterTable();
    });
});
</script>