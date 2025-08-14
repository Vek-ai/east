<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Page Columns";
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
              <a class="text-muted text-decoration-none" href="?page=">Home
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page"><?= $page_title ?></li>
          </ol>
        </nav>
      </div>
      <div>
        
      </div>
    </div>
  </div>
</div>

<div class="card card-body">
    <div class="row">
        <div class="col-3">
            <h3 class="card-title align-items-center mb-2">
                Filter <?= $page_title ?>
            </h3>
            <div class="position-relative w-100 px-0 mr-0 mb-2">
                <input type="text" class="form-control py-2 ps-5" id="text-srh" placeholder="Search">
                <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </div>
            <div class="d-flex justify-content-end py-2">
                <button type="button" class="btn btn-outline-primary reset_filters">
                    <i class="fas fa-sync-alt me-1"></i> Reset Filters
                </button>
            </div>
        </div>
        <div class="col-9">
            <h3 class="card-title mb-3">Column Visibility Control</h3>
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div class="col-5">
                    <label for="profileSelect" class="form-label">Select Profile</label>
                    <div class="mb-3">
                        <select id="profileSelect" class="form-select select2">
                            <option value="">-- All Profiles --</option>
                            <?php
                            $query_profile = "SELECT access_profile_id, access_profile FROM access_profile WHERE hidden = 0";
                            $result_profile = mysqli_query($conn, $query_profile);            
                            while ($row_profile = mysqli_fetch_array($result_profile)) {
                                echo '<option value="'.$row_profile['access_profile_id'].'">'.htmlspecialchars($row_profile['access_profile']).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-5">
                    <label for="pageSelect" class="form-label">Select Page</label>
                    <div id="pageSelectWrapper" class="mb-3">
                        <select id="pageSelect" class="form-select select2">
                            <option value="">-- All Pages --</option>
                            <?php
                            $query_pages = "SELECT id, page_name FROM pages";
                            $result_pages = mysqli_query($conn, $query_pages);            
                            while ($row_pages = mysqli_fetch_array($result_pages)) {
                                echo '<option value="'.$row_pages['id'].'">'.htmlspecialchars($row_pages['page_name']).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-primary" id="search_page_columns">
                        Search
                    </button>
                </div>
            </div>
            

            

            

            <div id="columnsTableWrapper" style="display:none;">
                <form id="columnVisibilityForm">
                    <input type="hidden" name="profile_id" id="formProfileId">
                    <input type="hidden" name="page_id" id="formPageId">
                    <div class="datatables">
                        <div class="table-responsive">
                            <table id="columnsTable" class="table table-striped table-bordered text-nowrap align-middle">
                                <thead>
                                    <tr>
                                        <th>Column Name</th>
                                        <th>Display Name</th>
                                        <th style="width:120px;text-align:center;">Visible</th>
                                    </tr>
                                </thead>
                                <tbody id="columnsTableBody">
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                
                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
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
            <form id="pageColumnsForm" class="form-horizontal">
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

    let table = $('#columnsTable').DataTable({
        order: [],
        pageLength: 100,
        searching: false,
        paging: false,
        info: false
    });

    function loadPageColumns() {
        let pageId = $('#pageSelect').val();
        let profileId = $('#profileSelect').val();

        $.ajax({
            url: 'pages/page_columns_ajax.php',
            type: 'POST',
            data: { 
                page_id: pageId, 
                profile_id: profileId,
                action: 'load_page_columns'
            },
            success: function(html) {
                $('#columnsTableBody').html(html);
                $('#formProfileId').val(profileId);
                $('#formPageId').val(pageId);
                $('#columnsTableWrapper').show();
            }
        });
    }

    $(document).on('click', '#search_page_columns', function(event) {
        loadPageColumns();
    });

    $(document).on('change', '.toggle-visible', function () {
        let $checkbox  = $(this);
        let colId      = $checkbox.data('id');
        let pageId     = $checkbox.data('pageid');
        let profileId  = $checkbox.data('profileid');
        let isVisible  = $checkbox.is(':checked') ? 1 : 0;

        $checkbox.prop('disabled', true);

        $.ajax({
            url: 'pages/page_columns_ajax.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'toggle_column',
                page_id: pageId,
                profile_id: profileId,
                column_id: colId,
                visible: isVisible
            },
            success: function (res) {
                console.log(res);
                if (res.status !== 'success') {
                    alert(res.message || 'Update failed');
                    $checkbox.prop('checked', !isVisible);
                }
            },
            error: function () {
                alert('AJAX request failed');
                $checkbox.prop('checked', !isVisible);
            },
            complete: function () {
                $checkbox.prop('disabled', false);
            }
        });
    });


    $(".select2").each(function() {
        $(this).select2({
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
            url: 'pages/page_columns_ajax.php',
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
    
    filterTable();
});
</script>