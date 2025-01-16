<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$id = 0;
$supplierid = 0;
$color = '';
$color_code = 0;

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['id'])){
  $id = $_REQUEST['id'];
  $query = "SELECT * FROM supplier_color WHERE id = '$id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $id = $row['id'];
      $supplierid = $row['supplierid'];
      $color = $row['color'];
      $color_code = $row['color_code'];
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
        <h4 class="font-weight-medium fs-14 mb-0">Supplier Color</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Supplier
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Supplier Color</li>
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
<div class="col-12">
  <!-- start Default Form Elements -->
  <div class="card card-body">
    <div class="row">
      <div class="col-3">
        <h4 class="card-title"><?= $addHeaderTxt ?> Supplier Color</h4>
      </div>
    </div>
    <form id="supplierColorForm" class="form-horizontal">
      <div class="row pt-3">
        <div class="col-md-6">
        <label class="form-label">Supplier</label>
          <div class="mb-3">
            <select id="supplierid" class="form-control" name="supplierid">
                <option value="" >Select Supplier...</option>
                <?php
                $query_supplier = "SELECT * FROM supplier";
                $result_supplier = mysqli_query($conn, $query_supplier);            
                while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                    $selected = ($supplierid == $row_supplier['supplier_id']) ? 'selected' : '';
                ?>
                    <option value="<?= $row_supplier['supplier_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_name'] ?></option>
                <?php   
                }
                ?>
            </select>
          </div>
        </div>
        <div class="col-md-6"></div>
      </div>

      <div class="row pt-3">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Color</label>
            <input type="text" id="color" name="color" class="form-control" placeholder="ex. Red" value="<?= $color ?>" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Color Code</label>
            <input type="color" id="color_code" name="color_code" class="form-control form-control-color" value="<?= $color_code ?>" />
          </div>
        </div>
      </div>

      <div class="form-actions">
        <div class="card-body border-top ">
          <input type="hidden" id="id" name="id" class="form-control"  value="<?= $id ?>"/>
          <div class="row">
            
            <div class="col-6 text-start">
            
            </div>
            <div class="col-6 text-end">
              <button type="submit" class="btn btn-primary" style="border-radius: 10%;"><?= $saveBtnTxt ?></button>
            </div>
          </div>
          
        </div>
      </div>

    </form>
  </div>
  <!-- end Default Form Elements -->
</div>
<div class="col-12">
  <div class="datatables">
    <div class="card">
      <div class="card-body">
          <h4 class="card-title d-flex justify-content-between align-items-center">Supplier Color List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['id'])){ ?>
            <a href="/?page=supplier_color" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
            <?php } ?> <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
          </h4>
        
        <div class="table-responsive">
       
          <table id="display_supplier_color" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th>Supplier</th>
                <th>Color</th>
                <th>Color Code</th>
                <th>Details</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
              <?php
              $no = 1;
              $query_supplier_color = "SELECT * FROM supplier_color WHERE hidden=0";
              $result_supplier_color = mysqli_query($conn, $query_supplier_color);            
              while ($row_supplier_color = mysqli_fetch_array($result_supplier_color)) {
                  $id = $row_supplier_color['id'];
                  $supplierid = $row_supplier_color['supplierid'];
                  $db_status = $row_supplier_color['status'];
                  $color = $row_supplier_color['color'];
                  $color_code = $row_supplier_color['color_code'];
                // $last_edit = $row_supplier_color['last_edit'];
                  $date = new DateTime($row_supplier_color['last_edit']);
                  $last_edit = $date->format('m-d-Y');

                  $added_by = $row_supplier_color['added_by'];
                  $edited_by = $row_supplier_color['edited_by'];

                  
                  if($edited_by != "0"){
                    $last_user_name = get_name($edited_by);
                  }else if($added_by != "0"){
                    $last_user_name = get_name($added_by);
                  }else{
                    $last_user_name = "";
                  }

                  if ($row_supplier_color['status'] == '0') {
                      $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                  } else {
                      $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                  }
              ?>
              <tr id="product-row-<?= $no ?>">
                  <td><span class="product<?= $no ?> <?php if ($row_supplier_color['status'] == '0') { echo 'emphasize-strike'; } ?>"><?= getSupplierName($supplierid) ?></span></td>
                  <td><?= ucwords($color) ?></td>
                  <td><?= ucwords($color_code) ?></td>
                  <td class="last-edit" style="width:30%;">Last Edited <?= $last_edit ?> by  <?= $last_user_name ?></td>
                  <td><?= $status ?></td>
                  <td class="text-center" id="action-button-<?= $no ?>">
                      <?php if ($row_supplier_color['status'] == '0') { ?>
                          <a href="#" class="btn btn-light py-1 text-dark hideSupplierColor" data-id="<?= $id ?>" data-row="<?= $no ?>" style='border-radius: 10%;'>Archive</a>
                      <?php } else { ?>
                          <a href="/?page=supplier_color&id=<?= $id ?>" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a>
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
                          url: 'pages/supplier_color_ajax.php',
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
                                      $('#action-button-' + no).html('<a href="#" class="btn btn-light py-1 text-dark hideSupplierColor" data-id="' + id + '" data-row="' + no + '" style="border-radius: 10%;">Archive</a>');
                                      $('#toggleActive').trigger('change');
                                    } else {
                                      $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                      $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                      $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                                      $('#action-button-' + no).html('<a href="/?page=supplier_color&id=' + id + '" class="btn btn-primary py-1" style="border-radius: 10%;">Edit</a>');
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

                  $(document).on('click', '.hideSupplierColor', function(event) {
                      event.preventDefault();
                      var id = $(this).data('id');
                      var rowId = $(this).data('row');
                      $.ajax({
                          url: 'pages/supplier_color_ajax.php',
                          type: 'POST',
                          data: {
                              id: id,
                              action: 'hide_supplier_color'
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
    var table = $('#display_supplier_color').DataTable();

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

    $("#supplierid").select2({
        width: '100%',
        placeholder: "Select Supplier",
        allowClear: true
    });

    $('#supplierColorForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/supplier_color_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              
              if (response.trim() === "success_update") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text('Supplier Color updated successfully.');
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    window.location.href = "?page=supplier_color";
                  });
              } else if (response.trim() === "success_add") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text('New Supplier Color added successfully.');
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
    
});
</script>