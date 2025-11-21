<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = 'Screw Type';

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
    td.notes,  td.last-edit{
        white-space: normal;
        word-wrap: break-word;
    }
    .emphasize-strike {
        text-decoration: type-through;
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
          <button type="button" id="downloadBtn" class="btn btn-primary d-flex align-items-center">
              <i class="ti ti-download text-white me-1 fs-5"></i> Download <?= $page_title ?>
          </button>
          <button type="button" id="uploadBtn" class="btn btn-primary d-flex align-items-center">
              <i class="ti ti-upload text-white me-1 fs-5"></i> Upload <?= $page_title ?>
          </button>
      </div>
    </div>
</div>

<?php
}
?>

<div class="card card-body">
  <div class="row">
      <div class="col-3">
          <h3 class="card-title align-items-center mb-2">
              Filter <?= $page_title ?>
          </h3>
          <div class="position-relative w-100 px-0 mr-0 mb-2">
              <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search">
              <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
          </div>
          <div class="px-3 mb-2"> 
              <input type="checkbox" id="toggleActive" checked> Show Active Only
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
                  <table id="display_screw_type" class="table table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th><?= $page_title ?></th>
                            <th>Abbreviation</th>
                            <th>Notes</th>
                            <th>Last Edit By</th>
                            <th>Last Time Edited</th>
                            <th>Status</th>
                            <th>Action</th>
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
                    Add
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="screwTypeForm" class="form-horizontal">
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
                  Uploaded <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div id="uploaded_excel" class="modal-body"></div>
      </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    document.title = "<?= $page_title ?>";

    var columns = [];
    columns.push({ data: 'product_screw_type' });
    columns.push({ data: 'type_abreviations' });
    columns.push({ data: 'notes' });
    columns.push({ data: 'last_edit_by' });
    columns.push({ data: 'last_edit' });
    columns.push({ data: 'status_html' });
    columns.push({ data: 'action_html' });

    var table = $('#display_screw_type').DataTable({
        pageLength: 100,
        ajax: {
            url: 'pages/product_screw_type_ajax.php',
            type: 'POST',
            data: { action: 'fetch_table' },
            error: function (xhr, status, error) {
                console.log("Response Text:", xhr.responseText);
            }
        },
        columns: columns
    });

    $('#display_screw_type_filter').hide();

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            dropdownParent: $(this).parent()
        });
    });

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var status = $(table.row(dataIndex).node()).find('a .alert').text().trim();
        var isActive = $('#toggleActive').is(':checked');

        if (!isActive || status === 'Active') {
            return true;
        }
        return false;
    });

    $('#toggleActive').on('change', function() {
        table.draw();
    });

    $('#toggleActive').trigger('change');

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

    $(document).on('click', '.changeStatus', function(event) {
        event.preventDefault(); 
        var product_type_id = $(this).data('id');
        var status = $(this).data('status');
        var no = $(this).data('no');
        $.ajax({
            url: 'pages/product_screw_type_ajax.php',
            type: 'POST',
            data: {
                product_type_id: product_type_id,
                status: status,
                action: 'change_status'
            },
            success: function(response) {
                if (response == 'success') {
                    table.ajax.reload(null, false);
                } else {
                    alert('Failed to change status.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $(document).on('click', '.hideType', function(event) {
        event.preventDefault();
        var product_type_id = $(this).data('id');
        var rowId = $(this).data('row');
        $.ajax({
            url: 'pages/product_screw_type_ajax.php',
            type: 'POST',
            data: {
                product_type_id: product_type_id,
                action: 'hide_product_type'
            },
            success: function(response) {
                if (response == 'success') {
                    table.ajax.reload(null, false);
                } else {
                    console.log(response);
                    alert('Failed to hide Screw Type.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $('#screwTypeForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/product_screw_type_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              $('.modal').modal("hide");
              if (response.trim() === "update-success") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("Screw Type updated successfully.");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");
                  table.ajax.reload(null, false);
              } else if (response === "add-success") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("New Screw Type added successfully.");
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

    $(document).on('click', '#downloadBtn', function(event) {
        window.location.href = "pages/product_screw_type_ajax.php?action=download_excel";
    });

    $(document).on('click', '#uploadBtn', function(event) {
        $('#uploadModal').modal('show');
    });

    $(document).on('click', '#downloadClassModalBtn', function(event) {
        $('#downloadClassModal').modal('show');
    });

    $(document).on('click', '#readUploadBtn', function(event) {
        $.ajax({
            url: 'pages/product_screw_type_ajax.php',
            type: 'POST',
            data: {
                action: "fetch_uploaded_modal"
            },
            success: function(response) {
                $('#uploaded_excel').html(response);
                $('#readUploadModal').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $('#upload_excel_form').on('submit', function (e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'upload_excel');

        $.ajax({
            url: 'pages/product_screw_type_ajax.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $('.modal').modal('hide');
                response = response.trim();
                if (response.trim() === "success") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text("Data Uploaded successfully.");
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

    $(document).on('blur', '.table_data', function() {
        let newValue;
        let updatedData = {};
        
        if ($(this)[0].tagName.toLowerCase() === 'select') {
            const selectedValue = $(this).val();
            const selectedText = $(this).find('option:selected').text();
            newValue = selectedValue ? selectedValue : selectedText;
        } 
        else if ($(this).is('td')) {
            newValue = $(this).text();
        }
        
        const headerName = $(this).data('header-name');
        const id = $(this).data('id');

        updatedData = {
            action: 'update_test_data',
            id: id,
            header_name: headerName,
            new_value: newValue,
        };

        $.ajax({
            url: 'pages/product_screw_type_ajax.php',
            type: 'POST',
            data: updatedData,
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, status, error) {
                console.log('Error: ' + error);
                alert('Error updating data');
            }
        });
    });

    $(document).on('click', '#saveTable', function(event) {
        if (confirm("Are you sure you want to save this Excel data to the product lines data?")) {
            var formData = new FormData();
            formData.append("action", "save_table");

            $.ajax({
                url: "pages/product_screw_type_ajax.php",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('.modal').modal('hide');
                    response = response.trim();
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text(response);
                    $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                    $('#response-modal').modal("show");
                }
            });
        }
    });

    $(document).on('click', '#addModalBtn', function(event) {
        event.preventDefault();
        var id = $(this).data('id') || '';
        var type = $(this).data('type') || '';

        if(type == 'edit'){
          $('#add-header').html('Update Screw Type');
        }else{
          $('#add-header').html('Add Screw Type');
        }

        $.ajax({
            url: 'pages/product_screw_type_ajax.php',
            type: 'POST',
            data: {
              id : id,
              action: 'fetch_modal_content'
            },
            success: function (response) {
                $('#add-fields').html(response);

                $(".select2_modal").each(function () {
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
                return $(table.row(dataIndex).node()).find('a .alert').text().trim() === 'Active';
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
            var filterName = $(this).data('filter-name'); // Custom attribute for display

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