<?php
require 'includes/dbconn.php';
require 'includes/functions.php';
?>
<style>
    .emphasize-strike {
        text-decoration: line-through;
        font-weight: bold;
        color: #9a841c;
    }

    .dataTables_filter input {
        width: 100%;
        height: 30px;
        font-size: 16px;
        padding: 10px;
        border-radius: 5px;
    }

    .dataTables_filter {  
        width: 100%;
    }

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
        <h4 class="font-weight-medium fs-14 mb-0">Paint Colors</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Product Properties
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Paint Colors</li>
          </ol>
        </nav>
      </div>
      <div>
        <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
          <div class="d-flex gap-2">
            <div class="">
              <small>This Month</small>
              <h4 class="text-primary mb-0 ">$58,256</h4>
            </div>
            <div class="">
              <div class="breadbar"></div>
            </div>
          </div>
          <div class="d-flex gap-2">
            <div class="">
              <small>Last Month</small>
              <h4 class="text-secondary mb-0 ">$58,256</h4>
            </div>
            <div class="">
              <div class="breadbar2"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card card-body">
    <div class="row">
      <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
          <button type="button" id="addModalBtn" class="btn btn-primary d-flex align-items-center" data-id="" data-type="add">
              <i class="ti ti-plus text-white me-1 fs-5"></i> Add Paint Color
          </button>
          <button type="button" id="downloadClassModalBtn" class="btn btn-primary d-flex align-items-center">
              <i class="ti ti-download text-white me-1 fs-5"></i> Download Classifications
          </button>
          <button type="button" id="downloadModalBtn" class="btn btn-primary d-flex align-items-center">
              <i class="ti ti-download text-white me-1 fs-5"></i> Download Colors
          </button>
          <button type="button" id="uploadModalBtn" class="btn btn-primary d-flex align-items-center">
              <i class="ti ti-upload text-white me-1 fs-5"></i> Upload Colors
          </button>
      </div>
    </div>
</div>

<div class="card card-body">
  <div class="row">
      <div class="col-3">
          <h3 class="card-title align-items-center mb-2">
              Filter Paint Colors
          </h3>
          <div class="position-relative w-100 px-0 mr-0 mb-2">
              <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search">
              <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
          </div>
          <div class="align-items-center">
              <div class="position-relative w-100 px-1 mb-2">
                  <select class="form-control py-0 ps-5 select2 filter-selection" id="filter-category" data-filter="category" data-filter-name="Product Category">
                      <option value="">All Categories</option>
                      <optgroup label="Category">
                          <?php
                          $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                          $result_category = mysqli_query($conn, $query_category);
                          while ($row_category = mysqli_fetch_array($result_category)) {
                              $selected = ($category_id == $row_category['product_category_id']) ? 'selected' : '';
                          ?>
                              <option value="<?= $row_category['product_category'] ?>" data-category="<?= $row_category['product_category'] ?>" <?= $selected ?>><?= $row_category['product_category'] ?></option>
                          <?php
                          }
                          ?>
                      </optgroup>
                  </select>
              </div>
              <div class="position-relative w-100 px-1 mb-2">
                  <select class="form-control py-0 ps-5 select2 filter-selection" id="filter-color-group" data-filter="color-group" data-filter-name="Color Group">
                      <option value="">All Color Groups</option>
                      <optgroup label="Color Groups">
                          <?php
                              $query_color_group = "
                                  SELECT DISTINCT cgn.color_group_name_id, cgn.color_group_name 
                                  FROM color_group_name cgn
                                  INNER JOIN product_color pc ON cgn.color_group_name_id = pc.color
                                  WHERE cgn.hidden = '0'
                                  ORDER BY cgn.color_group_name
                              ";

                              $result_color_group = mysqli_query($conn, $query_color_group);
                              while ($row_color_group = mysqli_fetch_array($result_color_group)) {
                                  $selected = (($color_details['color_group'] ?? '') == $row_color_group['color_group_name_id']) ? 'selected' : '';
                              ?>
                                  <option value="<?= $row_color_group['color_group_name'] ?>" data-color-group="<?= $row_category['color_group_name'] ?>" <?= $selected ?>>
                                      <?= $row_color_group['color_group_name'] ?>
                                  </option>
                              <?php
                              }
                              ?>
                      </optgroup>
                  </select>
              </div>
              <div class="position-relative w-100 px-1 mb-2">
                  <select class="form-control py-0 ps-5 select2 filter-selection" id="filter-provider" data-filter="provider" data-filter-name="Provider">
                      <option value="">All Providers</option>
                      <optgroup label="Provider">
                          <?php
                          $query_rows = "SELECT * FROM paint_providers";
                          $result_rows = mysqli_query($conn, $query_rows);            
                          while ($row_rows = mysqli_fetch_array($result_rows)) {
                          $selected = ($row_rows['provider_id'] == ($color_details['provider_id'] ?? '')) ? 'selected' : '';
                          ?>
                              <option value="<?= $row_rows['provider_name'] ?>" data-provider="<?= $row_rows['provider_name'] ?>" <?= $selected ?>><?= $row_rows['provider_name'] ?></option>
                          <?php   
                          }
                          ?>
                      </optgroup>
                  </select>
              </div>
              <div class="position-relative w-100 px-1 mb-2">
                  <select class="form-control py-0 ps-5 select2 filter-selection" id="filter-availability" data-filter="availability" data-filter-name="Product Availability">
                      <option value="">All Availabilities</option>
                      <optgroup label="Availability">
                          <?php
                          $query_availability = "SELECT * FROM product_availability";
                          $result_availability = mysqli_query($conn, $query_availability);            
                          while ($row_availability = mysqli_fetch_array($result_availability)) {
                          $selected = ($row_availability['product_availability_id'] == ($color_details['stock_availability'] ?? '')) ? 'selected' : '';
                          ?>
                              <option value="<?= $row_availability['product_availability'] ?>" data-availability="<?= $row_availability['product_availability'] ?>" <?= $selected ?> ><?= $row_availability['product_availability'] ?></option>
                          <?php   
                          }
                          ?>
                      </optgroup>
                  </select>
              </div>
          </div>
          <div class="px-3 mb-2"> 
              <input type="checkbox" id="toggleActive" checked> Show Active Only
          </div>
      </div>
      <div class="col-9">
        <div id="selected-tags" class="mb-2"></div>
          <div class="datatables">
            <div class="card">
              <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                    <h4 class="d-flex justify-content-between align-items-center">Paint color List</h4>
                  </div>
                <div class="table-responsive">
                  <table id="display_paint_colors" class="table table-striped table-bordered text-wrap align-middle">
                    <thead>
                      <tr>
                        <th>EKM Color Name</th>
                        <th>Hex Color Code</th>
                        <th>Color Group</th>
                        <th>Provider</th>
                        <th>Category</th>
                        <th>Availability</th>
                        <th>Details</th>
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

<div class="modal fade" id="addColorModal" tabindex="-1" aria-labelledby="addColorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="add-header">
                  Add
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="card-body">
              <form id="color_form" class="form-horizontal">
                <div id="color_form_body">
                </div>
                <div class="form-actions modal-footer">
                  <div class="card-body border-top ">
                      <div class="row pt-2">
                        <div class="col-6 text-start"></div>
                        <div class="col-6 text-end">
                          <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                      </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="downloadColorModal" tabindex="-1" aria-labelledby="downloadColorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Download Color
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form id="download_color_form" class="form-horizontal">
                  <label for="select-category" class="form-label fw-semibold">Select Category</label>
                  <div class="mb-3">
                      <select class="form-select select2" id="select-download-category" name="category">
                          <option value="">All Categories</option>
                          <optgroup label="Category">
                              <?php
                              $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                              $result_category = mysqli_query($conn, $query_category);
                              while ($row_category = mysqli_fetch_array($result_category)) {
                              ?>
                                  <option value="<?= $row_category['product_category_id'] ?>"><?= $row_category['product_category'] ?></option>
                              <?php
                              }
                              ?>
                          </optgroup>
                      </select>
                  </div>

                  <div class="d-grid">
                      <button type="submit" class="btn btn-primary fw-semibold">
                          <i class="fas fa-download me-2"></i> Download Excel
                      </button>
                  </div>
              </form>
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="downloadClassModal" tabindex="-1" aria-labelledby="downloadClassModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Download Classification
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form id="download_class_form" class="form-horizontal">
                  <label for="select-category" class="form-label fw-semibold">Select Classification</label>
                  <div class="mb-3">
                      <select class="form-select select2" id="select-download-class" name="category">
                          <option value="">All Classifications</option>
                          <optgroup label="Classifications">
                              <option value="category">Category</option>
                              <option value="color_group">Color Group</option>
                              <option value="paint_providers">Paint Provider</option> 
                              <option value="availability">Product Availability</option> 
                          </optgroup>
                      </select>
                  </div>

                  <div class="d-grid">
                      <button type="submit" class="btn btn-primary fw-semibold">
                          <i class="fas fa-download me-2"></i> Download Classification
                      </button>
                  </div>
              </form>
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="uploadColorModal" tabindex="-1" aria-labelledby="uploadColorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Upload Color
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="card">
                  <div class="card-body">
                      <form id="upload_color_form" action="#" method="post" enctype="multipart/form-data">
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
                      <button type="button" id="readUploadColorBtn" class="btn btn-primary fw-semibold">
                          <i class="fas fa-eye me-2"></i> View Uploaded File
                      </button>
                  </div>
              </div>    
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="readUploadColorModal" tabindex="-1" aria-labelledby="readUploadColorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Uploaded Excel Color
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div id="uploaded_excel" class="modal-body">
          
          </div>
      </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    document.title = "Paint Colors";

    var table = $('#display_paint_colors').DataTable({
        pageLength: 100,
        ajax: {
            url: 'pages/paint_colors_ajax.php',
            type: 'POST',
            data: { action: 'fetch_table' }
        },
        columns: [
            { data: 'color_name' },
            { data: 'color_code' },
            { data: 'color_group' },
            { data: 'provider' },
            { data: 'product_category_name' },
            { data: 'availability' },
            { data: 'last_edit' },
            { data: 'status_html' },
            { data: 'action_html' }
        ],
        createdRow: function (row, data, dataIndex) {
            $(row).attr('data-category', data.product_category_name);
            $(row).attr('data-color-group', data.color_group);
            $(row).attr('data-provider', data.provider);
            $(row).attr('data-availability', data.availability);
        }
    });

    $('#display_paint_colors_filter').hide();

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

    $(document).on('click', '#addModalBtn', function(event) {
        event.preventDefault(); 
        var id = $(this).data('id') || '';
        var type = $(this).data('type') || '';

        if(type == 'edit'){
          $('#add-header').html('Update Paint Color');
        }else{
          $('#add-header').html('Add Paint Color');
        }

        $.ajax({
          url: 'pages/paint_colors_ajax.php',
          type: 'POST',
          data: {
              id: id,
              action: "fetch_update_modal"
          },
          success: function(response) {
              $('#color_form_body').html(response);
              $('#addColorModal').modal('show');
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

    $(document).on('click', '.changeStatus', function(event) {
        event.preventDefault(); 
        var color_id = $(this).data('id');
        var status = $(this).data('status');
        var no = $(this).data('no');
        $.ajax({
            url: 'pages/paint_colors_ajax.php',
            type: 'POST',
            data: {
                color_id: color_id,
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

    $(document).on('click', '.hidePaintColor', function(event) {
        event.preventDefault();
        var color_id = $(this).data('id');
        var rowId = $(this).data('row');
        $.ajax({
            url: 'pages/paint_colors_ajax.php',
            type: 'POST',
            data: {
                color_id: color_id,
                action: 'hide_paint_color'
            },
            success: function(response) {
                if (response == 'success') {
                    table.ajax.reload(null, false);
                } else {
                    alert('Failed to hide product system.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $('#color_form').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        $.ajax({
            url: 'pages/paint_colors_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              $('.modal').modal('hide');
              if (response.trim() === "Paint color updated successfully.") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("Paint color updated successfully.");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");
                  table.ajax.reload(null, false);
              } else if (response.trim() === "New paint color added successfully.") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text(response);
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

    $(document).on('click', '#downloadModalBtn', function(event) {
        $('#downloadColorModal').modal('show');
    });

    $(document).on('click', '#downloadClassModalBtn', function(event) {
        $('#downloadClassModal').modal('show');
    });

    $(document).on('click', '#uploadModalBtn', function(event) {
        $('#uploadColorModal').modal('show');
    });

    $(document).on('click', '#readUploadColorBtn', function(event) {
        $.ajax({
            url: 'pages/paint_colors_ajax.php',
            type: 'POST',
            data: {
                action: "fetch_uploaded_modal"
            },
            success: function(response) {
                $('#uploaded_excel').html(response);
                $('#readUploadColorModal').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $("#download_color_form").submit(function (e) {
        e.preventDefault();
        window.location.href = "pages/paint_colors_ajax.php?action=download_excel&category=" + encodeURIComponent($("#select-download-category").val());
    });

    $("#download_class_form").submit(function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        formData.append("action", "download_classifications");

        $.ajax({
            url: "pages/paint_colors_ajax.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                window.location.href = "pages/paint_colors_ajax.php?action=download_classifications&class=" + encodeURIComponent($("#select-download-class").val());
            },
            error: function (xhr, status, error) {
                alert("Error downloading file: " + error);
            }
        });
    });

    $('#upload_color_form').on('submit', function (e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'upload_excel');

        $.ajax({
            url: 'pages/paint_colors_ajax.php',
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
            action: 'update_color_data',
            id: id,
            header_name: headerName,
            new_value: newValue,
        };

        $.ajax({
            url: 'pages/paint_colors_ajax.php',
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
        if (confirm("Are you sure you want to save this Excel data to the colors?")) {
            var formData = new FormData();
            formData.append("action", "save_table");

            $.ajax({
                url: "pages/paint_colors_ajax.php",
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
});
</script>