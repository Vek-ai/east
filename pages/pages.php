<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Pages";

$permission = $_SESSION['permission'];
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
    .dataTables_filter { width: 100%;}
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
    <div class="row align-items-center">
        <div class="col-md-6">
            <label for="category_filter" class="form-label">Select Page Category</label>
            <div class="mb-3 mb-md-0">
                <select id="category_filter" class="form-select select2">
                    <option value="" hidden disabled selected>Select Page Category</option>
                    <option value="all">All Categories</option>
                    <optgroup label="Categories">
                        <?php
                        $catQuery = "SELECT * FROM page_categories ORDER BY category_name ASC";
                        $catResult = mysqli_query($conn, $catQuery);
                        if ($catResult && mysqli_num_rows($catResult) > 0) {
                            while ($catRow = mysqli_fetch_assoc($catResult)) {
                                $category_name = ucwords(str_replace('_', ' ', $catRow['category_name']));
                                echo "<option value='{$catRow['id']}'>{$category_name}</option>";
                            }
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
        </div>

        <?php                                                    
        if ($permission === 'edit') {
        ?>

        <div class="col-md-6 d-flex justify-content-md-end justify-content-center">
            <button type="button" id="addModalBtn" class="btn btn-primary d-flex align-items-center" data-id="" data-type="add">
                <i class="ti ti-plus text-white me-1 fs-5"></i> Add <?= $page_title ?>
            </button>
        </div>

        <?php
        }
        ?>
    </div>
</div>

<div id="pages_section" class="card card-body d-none">
  <div class="row">
      <div class="col-3">
            <h3 class="card-title align-items-center mb-2">
                Filter <?= $page_title ?>
            </h3>
            <div class="position-relative w-100 px-1 mr-0 mb-2">
                <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search">
                <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
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
                <table id="pages_table" class="table table-striped table-bordered align-middle w-100">
                  <thead>
                    <tr>
                      <th>Page Name</th>
                      <th>File Name</th>
                      <th>URL</th>
                      <th>Category</th>
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
                url: 'pages/pages_ajax.php',
                type: 'POST',
                data: function(d) {
                    d.action = 'fetch_table';
                    d.category_id = $('#category_filter').val();
                }
            },
            order: [],
            columns: [
                { data: 'page_name' },
                { data: 'file_name' },
                { data: 'url' },
                { data: 'category' },
                { data: 'action_html' }
            ]
        });
    }

    $(document).on('change', '#category_filter', function () {
        const selected = $(this).val();

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

    $(document).on('change', '#category_filter', function () {
        const selected = $(this).val();

        if (selected !== '') {
            $('#pages_section').removeClass('d-none');
        } else {
            $('#pages_section').addClass('d-none');
        }

        table.ajax.reload(null, false);

        setTimeout(function () {
            table.columns.adjust().draw();
        }, 200);
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
            url: 'pages/pages_ajax.php',
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
              } else if (response === "duplicate_category") {
                  $('#responseHeader').text("Failed");
                  $('#responseMsg').text("Category name already exist");
                  $('#responseHeaderContainer').removeClass("bg-success");
                  $('#responseHeaderContainer').addClass("bg-danger");
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
            url: 'pages/pages_ajax.php',
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