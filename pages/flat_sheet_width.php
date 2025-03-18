<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$id = 0;
$product_category = 0;
$product_system = 0;
$product_line = 0;
$product_type = 0;
$width = 0;

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['id'])){
  $id = $_REQUEST['id'];
  $query = "SELECT * FROM flat_sheet_width WHERE id = '$id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
        $id = $row['id'];
        $product_category = $row['product_category'];
        $product_system = $row['product_system'];
        $product_line = $row['product_line'];
        $product_type = $row['product_type'];
        $width = $row['width'];
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
        <h4 class="font-weight-medium fs-14 mb-0">Flat Sheet Width</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="?page=coil_product">Coils
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Flat Sheet Width</li>
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
      <div class="col-3">
          <h3 class="card-title align-items-center mb-2">
              Filter Widths 
          </h3>
          <div class="position-relative w-100 px-0 mr-0 mb-2">
              <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search Widths">
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
                              <option value="<?= $row_category['product_category_id'] ?>" data-category="<?= $row_category['product_category'] ?>" <?= $selected ?>><?= $row_category['product_category'] ?></option>
                          <?php
                          }
                          ?>
                      </optgroup>
                  </select>
              </div>
              <div class="position-relative w-100 px-1 mb-2">
                  <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="filter-system" data-filter="system" data-filter-name="Product System">
                      <option value="">All Product Systems</option>
                      <optgroup label="Product Type">
                          <?php
                          $query_system = "SELECT * FROM product_system WHERE hidden = '0' AND status = '1' ORDER BY `product_system` ASC";
                          $result_system = mysqli_query($conn, $query_system);
                          while ($row_system = mysqli_fetch_array($result_system)) {
                              $selected = ($product_system == $row_system['product_system_id']) ? 'selected' : '';
                          ?>
                              <option value="<?= $row_system['product_system_id'] ?>" data-category="<?= $row_system['product_category'] ?>" <?= $selected ?>><?= $row_system['product_system'] ?></option>
                          <?php
                          }
                          ?>
                      </optgroup>
                  </select>
              </div>
              <div class="position-relative w-100 px-1 mb-2">
                  <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="filter-line" data-filter="line" data-filter-name="Product Line">
                      <option value="">All Product Lines</option>
                      <optgroup label="Product Type">
                          <?php
                          $query_line = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                          $result_line = mysqli_query($conn, $query_line);
                          while ($row_line = mysqli_fetch_array($result_line)) {
                              $selected = ($type_id == $row_line['product_line_id']) ? 'selected' : '';
                          ?>
                              <option value="<?= $row_line['product_line_id'] ?>" data-category="<?= $row_line['product_category'] ?>" <?= $selected ?>><?= $row_line['product_line'] ?></option>
                          <?php
                          }
                          ?>
                      </optgroup>
                  </select>
              </div>
              <div class="position-relative w-100 px-1 mb-2">
                  <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="filter-type" data-filter="type" data-filter-name="Product Type">
                      <option value="">All Product Types</option>
                      <optgroup label="Product Type">
                          <?php
                          $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                          $result_type = mysqli_query($conn, $query_type);
                          while ($row_type = mysqli_fetch_array($result_type)) {
                              $selected = ($type_id == $row_type['product_type_id']) ? 'selected' : '';
                          ?>
                              <option value="<?= $row_type['product_type_id'] ?>" data-category="<?= $row_type['product_category'] ?>" <?= $selected ?>><?= $row_type['product_type'] ?></option>
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
                <h4 class="card-title d-flex justify-content-between align-items-center">Flat Sheet Widths List  &nbsp;&nbsp; 
                <button type="button" id="addModalBtn" class="btn btn-primary d-flex align-items-center" data-id="" data-type="add">
                    <i class="ti ti-plus text-white me-1 fs-5"></i> Add Product Type
                </button>
                </h4>
                
                <div class="table-responsive">
              
                  <table id="display_flat_sheet_width" class="table table-striped table-bordered text-nowrap align-middle text-center">
                    <thead>
                      <!-- start row -->
                      <tr>
                        <th>Width</th>
                        <th>Product System</th>
                        <th>Product Category</th>
                        <th>Product Line</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                      <!-- end row -->
                    </thead>
                    <tbody>
                      <?php
                      $no = 1;
                      $query_fs_width = "SELECT * FROM flat_sheet_width WHERE hidden=0";
                      $result_fs_width = mysqli_query($conn, $query_fs_width);            
                      while ($row_fs_width = mysqli_fetch_array($result_fs_width)) {
                          $id = $row_fs_width['id'];
                          $product_category = $row_fs_width['product_category'];
                          $product_system = $row_fs_width['product_system'];
                          $product_line = $row_fs_width['product_line'];
                          $product_type = $row_fs_width['product_type'];
                          $width = $row_fs_width['width'];
                          $db_status = $row_fs_width['status'];
                          // $last_edit = $row_fs_width['last_edit'];
                          $date = new DateTime($row_fs_width['last_edit'] ?? '');
                          $last_edit = $date->format('m-d-Y');

                          $added_by = $row_fs_width['added_by'];
                          $edited_by = $row_fs_width['edited_by'];

                          
                          if($edited_by != "0"){
                            $last_user_name = get_name($edited_by);
                          }else if($added_by != "0"){
                            $last_user_name = get_name($added_by);
                          }else{
                            $last_user_name = "";
                          }

                          if ($row_fs_width['status'] == '0') {
                              $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                          } else {
                              $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                          }
                      ?>
                        <tr id="product-row-<?= $no ?>"
                            data-category="<?=$row_fs_width['product_category']?>"
                            data-system="<?=$row_fs_width['product_system']?>"
                            data-line="<?=$row_fs_width['product_line']?>"
                            data-type="<?=$row_fs_width['product_type']?>"
                        >
                            <td><?= number_format(floatval($width),2) ?></td>
                            <td><?= getProductSystemName($product_system) ?></td>
                            <td><?= getProductCategoryName($product_category) ?></td>
                            <td><?= getProductLineName($product_line) ?></td>
                            <td>
                                <a href="#" id="viewModalBtn" class="d-flex align-items-center justify-content-center text-decoration-none" data-id="<?= $id ?>" data-type="<?= $product_type ?>">
                                    <?= getProductTypeName($product_type) ?>
                                </a>
                            </td>
                            <td><?= $status ?></td>
                            <td class="text-center" id="action-button-<?= $no ?>">
                            <?php if ($row_fs_width['status'] == '0') { ?>
                                <a href="#" class="text-decoration-none py-1 text-dark hideFSWidth" data-id="<?= $id ?>" data-row="<?= $no ?>">
                                <i class="text-danger ti ti-trash fs-7"></i>
                                </a>
                            <?php } else { ?>
                                <a href="#" id="addModalBtn" class="d-flex align-items-center justify-content-center text-decoration-none" data-id="<?= $id ?>" data-type="edit">
                                    <i class="ti ti-pencil fs-7"></i>
                                </a>
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
                              var id = $(this).data('id');
                              var status = $(this).data('status');
                              var no = $(this).data('no');
                              $.ajax({
                                  url: 'pages/flat_sheet_width_ajax.php',
                                  type: 'POST',
                                  data: {
                                      id: id,
                                      status: status,
                                      action: 'change_status'
                                  },
                                  success: function(response) {
                                      if (response == 'success') {
                                          if (status == 1) {
                                              $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                                              $(".changeStatus[data-no='" + no + "']").data('status', "0");
                                              $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                                              $('#action-button-' + no).html('<a href="#" class="text-decoration-none py-1 text-dark hideFSWidth" data-id="' + id + '" data-row="' + no + '" style="border-radius: 10%;"><i class="text-danger ti ti-trash fs-7"></i></a>');
                                              $('#toggleActive').trigger('change');
                                            } else {
                                              $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                              $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                              $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                                              $('#action-button-' + no).html('<a href="?page=flat_sheet_width&id=' + id + '" class="text-decoration-none py-1" style="border-radius: 10%;"><i class="text-warning ti ti-pencil fs-7"></i></a>');
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

                          $(document).on('click', '.hideFSWidth', function(event) {
                              event.preventDefault();
                              var id = $(this).data('id');
                              var rowId = $(this).data('row');
                              $.ajax({
                                  url: 'pages/flat_sheet_width_ajax.php',
                                  type: 'POST',
                                  data: {
                                      id: id,
                                      action: 'hide_fs_width'
                                  },
                                  success: function(response) {
                                      if (response == 'success') {
                                          $('#product-row-' + rowId).remove(); // Remove the row from the DOM
                                      } else {
                                          alert('Failed to hide category.');
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
                    Add
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="flatSheetWidthForm" class="form-horizontal">
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

<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="add-header">
                    Trim Profiles
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div id="view-fields" class=""></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

  $(document).ready(function() {
    document.title = "Flat Sheet Width";

    var table = $('#display_flat_sheet_width').DataTable({
        pageLength: 100
    });

    $('#display_flat_sheet_width_filter').hide();

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

    $(document).on('change', '#select-category', function() {
        updateSearchCategory();
    });

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            dropdownParent: $(this).parent()
        });
    });

    $('#flatSheetWidthForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');
        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        $.ajax({
            url: 'pages/flat_sheet_width_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#supplierid').prop('disabled', true);
                $('.modal').modal("hide");
                if (response.trim() === "success_update") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text('Flat Sheet Width updated successfully.');
                    $('#responseHeaderContainer').removeClass("bg-danger");
                    $('#responseHeaderContainer').addClass("bg-success");
                    $('#response-modal').modal("show");

                    $('#response-modal').on('hide.bs.modal', function () {
                        location.reload();
                    });
                } else if (response.trim() === "success_add") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text('New Flat Sheet Width added successfully.');
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
                    return false; // Exit loop early if mismatch is found
                }
            });

            return match;
        });

        table.draw();
        updateSelectedTags();
    }

    $(document).on('change', '.filter-selection', filterTable);

    $(document).on('input', '#text-srh', filterTable);

    $(document).on('change', '#toggleActive', filterTable);

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

    $(document).on('click', '#addModalBtn', function(event) {
        event.preventDefault();
        var id = $(this).data('id') || '';
        var type = $(this).data('type') || '';

        if(type == 'edit'){
          $('#add-header').html('Update Flat Sheet Width');
        }else{
          $('#add-header').html('Add Flat Sheet Width');
        }

        $.ajax({
            url: 'pages/flat_sheet_width_ajax.php',
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

    $(document).on('click', '#viewModalBtn', function(event) {
        event.preventDefault();
        var id = $(this).data('id') || '';
        var type = $(this).data('type') || '';

        $.ajax({
            url: 'pages/flat_sheet_width_ajax.php',
            type: 'POST',
            data: {
              id : id,
              type: type,
              action: 'fetch_view_content'
            },
            success: function (response) {
                $('#view-fields').html(response);
                $('#viewModal').modal('show');
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
    
    
});
</script>