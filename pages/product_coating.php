<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$product_coating = "";
$coating_abbreviations = "";
$product_category = '';
$notes = "";
$multiplier = 0;

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['product_coating_id'])){
  $product_coating_id = $_REQUEST['product_coating_id'];
  $query = "SELECT * FROM product_coating WHERE product_coating_id = '$product_coating_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $product_coating_id = $row['product_coating_id'];
      $product_coating = $row['product_coating'];
      $product_category = $row['product_category'];
      $coating_abbreviations = $row['coating_abbreviations'];
      $notes = $row['notes'];
      $multiplier = $row['multiplier'];
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
        <h4 class="font-weight-medium fs-14 mb-0">Product Systems</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Product Properties
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Product Systems</li>
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
  <div class="card card-body">
    <div class="row">
      <div class="col-3">
        <h4 class="card-title"><?= $addHeaderTxt ?> Product System</h4>
      </div>
    </div>
    
    <form id="productSystemForm" class="form-horizontal">
      <div class="row pt-3">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Product System</label>
            <input type="text" id="product_coating" name="product_coating" class="form-control"  value="<?= $product_coating ?>"/>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">System Abreviations</label>
            <input type="text" id="coating_abbreviations" name="coating_abbreviations" class="form-control" value="<?= $coating_abbreviations ?>" />
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="5"><?= $notes ?></textarea>
      </div>

      <div class="form-actions">
        <div class="card-body border-top ">
          <input type="hidden" id="product_coating_id" name="product_coating_id" class="form-control"  value="<?= $product_coating_id ?>"/>
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
          <h4 class="card-title d-flex justify-content-between align-items-center">Product System List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['product_coating_id'])){ ?>
            <a href="?page=product_coating" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
            <?php } ?> <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
          </h4>
        
        <div class="table-responsive">
       
          <table id="display_system" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th>Product System</th>
                <th>Abreviation</th>
                <th>Notes</th>
                <th>Details</th>
                <th>Status</th>
              
                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
              <?php
              $no = 1;
              $query_product_coating = "SELECT * FROM product_coating WHERE hidden=0";
              $result_product_coating = mysqli_query($conn, $query_product_coating);            
              while ($row_product_coating = mysqli_fetch_array($result_product_coating)) {
                  $product_coating_id = $row_product_coating['product_coating_id'];
                  $product_coating = $row_product_coating['product_coating'];
                  $coating_abbreviations = $row_product_coating['coating_abbreviations'];
                  $db_status = $row_product_coating['status'];
                  $notes = $row_product_coating['notes'];
                // $last_edit = $row_product_coating['last_edit'];
                  $date = new DateTime($row_product_coating['last_edit']);
                  $last_edit = $date->format('m-d-Y');

                  $added_by = $row_product_coating['added_by'];
                  $edited_by = $row_product_coating['edited_by'];

                  
                  if($edited_by != "0"){
                    $last_user_name = get_name($edited_by);
                  }else if($added_by != "0"){
                    $last_user_name = get_name($added_by);
                  }else{
                    $last_user_name = "";
                  }

                  if ($row_product_coating['status'] == '0') {
                      $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$product_coating_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                  } else {
                      $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$product_coating_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                  }
              ?>
              <tr id="product-row-<?= $no ?>">
                  <td><span class="product<?= $no ?> <?php if ($row_product_coating['status'] == '0') { echo 'emphasize-strike'; } ?>"><?= $product_coating ?></span></td>
                  <td><?= $coating_abbreviations ?></td>
                  <td class="notes" style="width:30%;"><?= $notes ?></td>
                  <td class="last-edit" style="width:30%;">Last Edited <?= $last_edit ?> by  <?= $last_user_name ?></td>
                  <td><?= $status ?></td>
                  <td class="text-center" id="action-button-<?= $no ?>">
                      <?php if ($row_product_coating['status'] == '0') { ?>
                          <a href="#" title="Archive" class="text-decoration-none py-1 text-dark hideSystem" data-id="<?= $product_coating_id ?>" data-row="<?= $no ?>">
                            <i class="text-danger ti ti-trash fs-7"></i>
                          </a>
                      <?php } else { ?>
                          <a href="?page=product_coating&product_coating_id=<?= $product_coating_id ?>" title="Edit" class="text-decoration-none py-1">
                            <i class="text-warning ti ti-pencil fs-7"></i>
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
                    var product_coating_id = $(this).data('id');
                    var status = $(this).data('status');
                    var no = $(this).data('no');
                    $.ajax({
                        url: 'pages/product_coating_ajax.php',
                        type: 'POST',
                        data: {
                            product_coating_id: product_coating_id,
                            status: status,
                            action: 'change_status'
                        },
                        success: function(response) {
                            if (response == 'success') {
                                if (status == 1) {
                                    $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                                    $(".changeStatus[data-no='" + no + "']").data('status', "0");
                                    $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                                    $('#action-button-' + no).html('<a href="#" title="Archive" class="text-decoration-none py-1 text-dark hideSystem" data-id="' + product_coating_id + '" data-row="' + no + '" style="border-radius: 10%;"><i class="text-danger ti ti-trash fs-7"></i></a>');
                                    $('#toggleActive').trigger('change');
                                  } else {
                                    $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                    $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                    $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                                    $('#action-button-' + no).html('<a href="?page=product_coating&product_coating_id=' + product_coating_id + '" title="Edit" class="text-decoration-none py-1" style="border-radius: 10%;"><i class="text-warning ti ti-pencil fs-7"></i></a>');
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

                $(document).on('click', '.hideSystem', function(event) {
                    event.preventDefault();
                    var product_coating_id = $(this).data('id');
                    var rowId = $(this).data('row');
                    $.ajax({
                        url: 'pages/product_coating_ajax.php',
                        type: 'POST',
                        data: {
                            product_coating_id: product_coating_id,
                            action: 'hide_system'
                        },
                        success: function(response) {
                            if (response == 'success') {
                                $('#product-row-' + rowId).remove(); // Remove the row from the DOM
                            } else {
                                alert('Failed to hide product system.');
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
    var table = $('#display_system').DataTable();

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

    $('#productSystemForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/product_coating_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              
              if (response.trim() === "success_update") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("Product System updated successfully.");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    location.reload();
                  });
              } else if (response.trim() === "success_add") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("New Product System added successfully.");
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