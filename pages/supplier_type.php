<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Supplier Type";

$supplier_type = "";
$description = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['supplier_type_id'])){
  $supplier_type_id = $_REQUEST['supplier_type_id'];
  $query = "SELECT * FROM supplier_type WHERE supplier_type_id = '$supplier_type_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $supplier_type_id = $row['supplier_type_id'];
      $supplier_type = $row['supplier_type'];
      $description = $row['description'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}
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
        <h4 class="font-weight-medium fs-14 mb-0">Supplier Types</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">s
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Product Properties
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Categories</li>
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
      </div>
      <div class="col-9">
        <div id="selected-tags" class="mb-2"></div>
        <div class="datatables">
          <div class="card">
            <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center">Supplier Types List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['supplier_type_id'])){ ?>
                  <a href="/?page=supplier_type" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
                  <?php } ?> <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
                </h4>
              
              <div class="table-responsive">
            
                <table id="display_supplier_type" class="table table-striped table-bordered text-nowrap align-middle">
                  <thead>
                    <!-- start row -->
                    <tr>
                      <th>Supplier Type</th>
                      <th>Role Description</th>
                      <th>Details</th>
                      <th>Status</th>
                    
                      <th>Action</th>
                    </tr>
                    <!-- end row -->
                  </thead>
                  <tbody>
                  <?php
                  $no = 1;
                  $query_supplier_type = "SELECT * FROM supplier_type WHERE hidden = 0";
                  $result_supplier_type = mysqli_query($conn, $query_supplier_type);            
                  while ($row_supplier_type = mysqli_fetch_array($result_supplier_type)) {
                      $supplier_type_id = $row_supplier_type['supplier_type_id'];
                      $supplier_type = $row_supplier_type['supplier_type'];
                      $description = $row_supplier_type['description'];
                      $db_status = $row_supplier_type['status'];

                      $date = new DateTime($row_supplier_type['last_edit']);
                      $last_edit = $date->format('m-d-Y');

                      $added_by = $row_supplier_type['added_by'];
                      $edited_by = $row_supplier_type['edited_by'];

                      
                      if($edited_by != "0"){
                        $last_user_name = get_name($edited_by);
                      }else if($added_by != "0"){
                        $last_user_name = get_name($added_by);
                      }else{
                        $last_user_name = "";
                      }

                      if ($row_supplier_type['status'] == '0') {
                          $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$supplier_type_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                      } else {
                          $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$supplier_type_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                      }
                  ?>
                  <tr id="product-row-<?= $no ?>">
                      <td><span class="product<?= $no ?> <?php if ($row_supplier_type['status'] == '0') { echo 'emphasize-strike'; } ?>"><?= $supplier_type ?></span></td>
                      <td class="notes" style="width:30%;"><?= $description ?></td>
                      <td class="last-edit" style="width:30%;">Last Edited <?= $last_edit ?> by  <?= $last_user_name ?></td>
                      <td><?= $status ?></td>
                      <td class="text-center" id="action-button-<?= $no ?>">
                          <?php if ($row_supplier_type['status'] == '0') { ?>
                              <a href="#" class="btn btn-light py-1 text-dark hideSupplierType" data-id="<?= $supplier_type_id ?>" data-row="<?= $no ?>" style='border-radius: 10%;'>Archive</a>
                          <?php } else { ?>
                              <a href="/?page=supplier_type&supplier_type_id=<?= $supplier_type_id ?>" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a>
                          <?php } ?>
                      </td>
                  </tr>
                  <?php
                  $no++;
                  }
                  ?>
                  </tbody>
                  <script>
                  $(document).ready(function() {
                      // Use event delegation for dynamically generated elements
                      $(document).on('click', '.changeStatus', function(event) {
                          event.preventDefault(); 
                          var supplier_type_id = $(this).data('id');
                          var status = $(this).data('status');
                          var no = $(this).data('no');
                          $.ajax({
                              url: 'pages/supplier_type_ajax.php',
                              type: 'POST',
                              data: {
                                  supplier_type_id: supplier_type_id,
                                  status: status,
                                  action: 'change_status'
                              },
                              success: function(response) {
                                  if (response == 'success') {
                                      if (status == 1) {
                                          $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                                          $(".changeStatus[data-no='" + no + "']").data('status', "0");
                                          $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                                          $('#action-button-' + no).html('<a href="#" class="btn btn-light py-1 text-dark hideSupplierType" data-id="' + supplier_type_id + '" data-row="' + no + '" style="border-radius: 10%;">Archive</a>');
                                          $('#toggleActive').trigger('change');
                                        } else {
                                          $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                          $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                          $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                                          $('#action-button-' + no).html('<a href="/?page=supplier_type&supplier_type_id=' + supplier_type_id + '" class="btn btn-primary py-1" style="border-radius: 10%;">Edit</a>');
                                          $('#toggleActive').trigger('change');
                                        }
                                  } else {
                                      alert('Failed to change status.');
                                  }
                              },
                              error: function(jqXHR, textStatus, errorThrown) {
                                  alert('Error: ' + textStatus + ' - ' + errorThrown);
                              }
                          });
                      });

                      $(document).on('click', '.hideSupplierType', function(event) {
                          event.preventDefault();
                          var supplier_type_id = $(this).data('id');
                          var rowId = $(this).data('row');
                          $.ajax({
                              url: 'pages/supplier_type_ajax.php',
                              type: 'POST',
                              data: {
                                  supplier_type_id: supplier_type_id,
                                  action: 'hide_supplier_type'
                              },
                              success: function(response) {
                                  if (response == 'success') {
                                      $('#product-row-' + rowId).remove(); // Remove the row from the DOM
                                  } else {
                                      alert('Failed to hide supplier type.');
                                  }
                              },
                              error: function(jqXHR, textStatus, errorThrown) {
                                  alert('Error: ' + textStatus + ' - ' + errorThrown);
                              }
                          });
                      });
                  });
                  </script>
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
            <form id="supplierTypeForm" class="form-horizontal">
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

<div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Download <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form id="download_excel_form" class="form-horizontal">
                  <label for="select-category" class="form-label fw-semibold">Select Supplier</label>
                  <div class="mb-3">
                      <select class="form-select select2" id="select-download-category" name="category">
                          <option value="">All Suppliers</option>
                          <optgroup label="Suppliers">
                              <?php
                              $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
                              $result_supplier = mysqli_query($conn, $query_supplier);            
                              while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                  $selected = (!empty($supplierid) && $supplierid == $row_supplier['supplier_id']) ? 'selected' : '';
                                  if(!empty($_REQUEST['supplier_id'])){
                                    $selected = (!empty($supplier_id) && $supplier_id == $row_supplier['supplier_id']) ? 'selected' : '';
                                  }
                              ?>
                                  <option value="<?= $row_supplier['supplier_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_name'] ?></option>
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

<script>
  $(document).ready(function() {
    var table = $('#display_supplier_type').DataTable();

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

    $('#supplierTypeForm').on('submit', function(event) {
        event.preventDefault(); 
        var userid = getCookie('userid');
        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);
        var appendResult = "";
        $.ajax({
            url: 'pages/supplier_type_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              $('.modal').modal("hide");
              if (response === "Supplier type updated successfully.") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text(response);
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    window.location.href = "?page=supplier_type";
                  });
              } else if (response === "New supplier type added successfully.") {
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

    $(document).on('click', '#uploadBtn', function(event) {
        $('#uploadModal').modal('show');
    });

    $(document).on('click', '#downloadBtn', function(event) {
        window.location.href = "pages/supplier_type_ajax.php?action=download_excel";
    });

    $(document).on('click', '#readUploadBtn', function(event) {
        $.ajax({
            url: 'pages/supplier_type_ajax.php',
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
            url: 'pages/supplier_type_ajax.php',
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
            url: 'pages/supplier_type_ajax.php',
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
                url: "pages/supplier_type_ajax.php",
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
          $('#add-header').html('Update <?= $page_title ?>');
        }else{
          $('#add-header').html('Add <?= $page_title ?>');
        }

        $.ajax({
            url: 'pages/supplier_type_ajax.php',
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
    
    filterTable();
});
</script>