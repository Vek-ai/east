<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$id = 0;
$actual_width = 0;
$decimal_conversion = 0;
$rounded_width = 0;
$rounded_conversion = 0;
$classification = '';
$main_profile = '';
$second_profile = '';
$third_profile = '';
$stock = '';
$gauge_systems = array();

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['id'])){
  $id = $_REQUEST['id'];
  $query = "SELECT * FROM coil_width WHERE id = '$id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
        $id = $row['id'];
        $actual_width = $row['actual_width'];
        $decimal_conversion = $row['decimal_conversion'];
        $rounded_width = $row['rounded_width'];
        $rounded_conversion = $row['rounded_conversion'];
        $classification = $row['classification'];
        $main_profile = $row['main_profile'];
        $second_profile = $row['second_profile'];
        $third_profile = $row['third_profile'];
        $stock = $row['stock'];
        $gauge_systems = isset($row['gauge_systems']) ? explode(';', $row['gauge_systems']) : [];
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
        <h4 class="font-weight-medium fs-14 mb-0">Coil Width</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="?page=coil_product">Coils
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Coil Width</li>
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
        <h4 class="card-title"><?= $addHeaderTxt ?> Coil Width</h4>
      </div>
    </div>
    <form id="supplierPackForm" class="form-horizontal">
      <div class="row pt-3">
        <div class="col-md-3">
            <div class="mb-3">
              <label class="form-label">Actual Width</label>
              <input type="text" id="actual_width" name="actual_width" class="form-control" placeholder="ex. 1.00" value="<?= $actual_width ?>" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
              <label class="form-label">Decimal Conversion</label>
              <input type="text" id="decimal_conversion" name="decimal_conversion" class="form-control" placeholder="ex. 1.00" value="<?= $decimal_conversion ?>" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
              <label class="form-label">Rounded Width</label>
              <input type="text" id="rounded_width" name="rounded_width" class="form-control" placeholder="ex. 1.00" value="<?= $rounded_width ?>" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
              <label class="form-label">Rounded Conversion</label>
              <input type="text" id="rounded_conversion" name="rounded_conversion" class="form-control" placeholder="ex. 1.00" value="<?= $rounded_conversion ?>" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Classification</label>
              <input type="text" id="classification" name="classification" class="form-control" placeholder="ex. SN" value="<?= $classification ?>" />
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Main Profile</label>
            <div class="mb-3">
                <select id="main_profile" name="main_profile" class="form-control select2">
                    <option value="">Select Main Profile...</option>
                    <?php 
                    $main_profiles = ["5V", "Board and Batten", "Corrugated", "Flat Stock", "Hi-Rib", "Low-Rib", "Rpanel", "Standing Seam"];
                    foreach ($main_profiles as $profile) {
                        $selected = ($main_profile == $profile) ? "selected" : "";
                        echo "<option value='$profile' $selected>$profile</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label">Second Profile</label>
            <div class="mb-3">
                <select id="second_profile" name="second_profile" class="form-control select2">
                    <option value="">Select Second Profile...</option>
                    <?php 
                    $second_profiles = ["Board and Batten", "Corrugated", "Low-Rib", "Standing Seam"];
                    foreach ($second_profiles as $profile) {
                        $selected = ($second_profile == $profile) ? "selected" : "";
                        echo "<option value='$profile' $selected>$profile</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">Third Profile</label>
            <div class="mb-3">
                <select id="third_profile" name="third_profile" class="form-control select2">
                    <option value="">Select Third Profile...</option>
                    <?php 
                    $third_profiles = ["Plank65", "Corrugated"];
                    foreach ($third_profiles as $profile) {
                        $selected = ($third_profile == $profile) ? "selected" : "";
                        echo "<option value='$profile' $selected>$profile</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">Stock</label>
            <div class="mb-3">
                <select id="stock" name="stock" class="form-control select2">
                    <option value="">Select Stock...</option>
                    <?php 
                    $stock_options = ["Non-Stock", "Stock", "Partial"];
                    foreach ($stock_options as $stock_value) {
                        $selected = ($stock == $stock_value) ? "selected" : "";
                        echo "<option value='$stock_value' $selected>$stock_value</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="col-md-12">
            <label class="form-label">Gauge Systems</label>
            <div class="mb-3">
                <select id="gauge_systems" class="form-control select2" name="gauge_systems[]" multiple>
                    <option value="">Select Gauge...</option>
                    <?php
                    $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY `product_gauge` ASC";
                    $result_gauge = mysqli_query($conn, $query_gauge);
                    
                    $selected_gauges = [];
                    
                    while ($row_gauge = mysqli_fetch_assoc($result_gauge)) {
                        if (!in_array($row_gauge['product_gauge'], $selected_gauges)) {
                            $selected = in_array($row_gauge['product_gauge'], $gauge_systems) ? "selected" : "";
                            echo "<option value='{$row_gauge['product_gauge']}' $selected>{$row_gauge['product_gauge']}</option>";
                            $selected_gauges[] = $row_gauge['product_gauge'];
                        }
                    }
                    ?>
                </select>
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
          <h4 class="card-title d-flex justify-content-between align-items-center">Coil Widths List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['id'])){ ?>
            <a href="?page=coil_width" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
            <?php } ?> <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
          </h4>
        
        <div class="table-responsive">
       
          <table id="display_coil_width" class="table table-striped table-bordered text-nowrap align-middle text-center">
            <thead>
              <!-- start row -->
              <tr>
                <th>Actual Width</th>
                <th>Classification</th>
                <th>Main Profile</th>
                <th>Second Profile</th>
                <th>Third Profile</th>
                <th>Gauge Systems</th>
                <th>Details</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
              <?php
              $no = 1;
              $query_coil_width = "SELECT * FROM coil_width WHERE hidden=0";
              $result_coil_width = mysqli_query($conn, $query_coil_width);            
              while ($row_coil_width = mysqli_fetch_array($result_coil_width)) {
                  $id = $row_coil_width['id'];
                  $actual_width = $row_coil_width['actual_width'];
                  $classification = $row_coil_width['classification'];
                  $main_profile = $row_coil_width['main_profile'];
                  $second_profile = $row_coil_width['second_profile'];
                  $third_profile = $row_coil_width['third_profile'];
                  $gauge_systems = !empty($row_coil_width['gauge_systems']) ? explode(';', $row_coil_width['gauge_systems']) : [];
                  $db_status = $row_coil_width['status'];
                  // $last_edit = $row_coil_width['last_edit'];
                  $date = new DateTime($row_coil_width['last_edit'] ?? '');
                  $last_edit = $date->format('m-d-Y');

                  $added_by = $row_coil_width['added_by'];
                  $edited_by = $row_coil_width['edited_by'];

                  
                  if($edited_by != "0"){
                    $last_user_name = get_name($edited_by);
                  }else if($added_by != "0"){
                    $last_user_name = get_name($added_by);
                  }else{
                    $last_user_name = "";
                  }

                  if ($row_coil_width['status'] == '0') {
                      $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                  } else {
                      $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                  }
              ?>
              <tr id="product-row-<?= $no ?>">
                  <td><span class="product<?= $no ?> <?php if ($row_coil_width['status'] == '0') { echo 'emphasize-strike'; } ?>"><?= $actual_width ?></span></td>
                  <td><?= ucwords($classification) ?></td>
                  <td><?= ucwords($main_profile) ?></td>
                  <td><?= ucwords($second_profile) ?></td>
                  <td><?= ucwords($third_profile) ?></td>
                  <td>
                    <?php 
                    foreach($gauge_systems as $gauge){
                        echo "<span class='badge text-bg-primary m-1'>".$gauge."</span>";
                    } 
                  ?></td>
                  <td class="last-edit" style="width:30%;">Last Edited <?= $last_edit ?> by  <?= $last_user_name ?></td>
                  <td><?= $status ?></td>
                  <td class="text-center" id="action-button-<?= $no ?>">
                      <?php if ($row_coil_width['status'] == '0') { ?>
                          <a href="#" title="Archive" class="btn btn-light py-1 text-dark hideSupplierPack" data-id="<?= $id ?>" data-row="<?= $no ?>" style='border-radius: 10%;'>Archive</a>
                      <?php } else { ?>
                          <a href="?page=coil_width&id=<?= $id ?>" title="Edit" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a>
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
                          url: 'pages/coil_width_ajax.php',
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
                                      $('#action-button-' + no).html('<a href="#" title="Archive" class="btn btn-light py-1 text-dark hideSupplierPack" data-id="' + id + '" data-row="' + no + '" style="border-radius: 10%;">Archive</a>');
                                      $('#toggleActive').trigger('change');
                                    } else {
                                      $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                      $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                      $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                                      $('#action-button-' + no).html('<a href="?page=coil_width&id=' + id + '" title="Edit" class="btn btn-primary py-1" style="border-radius: 10%;">Edit</a>');
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

                  $(document).on('click', '.hideSupplierPack', function(event) {
                      event.preventDefault();
                      var id = $(this).data('id');
                      var rowId = $(this).data('row');
                      $.ajax({
                          url: 'pages/coil_width_ajax.php',
                          type: 'POST',
                          data: {
                              id: id,
                              action: 'hide_coil_width'
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
    var table = $('#display_coil_width').DataTable();

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

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            allowClear: true,
            dropdownParent: $(this).parent()
        });
    });

    $('#supplierPackForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        $('#supplierid').prop('disabled', false);

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/coil_width_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#supplierid').prop('disabled', true);
                if (response.trim() === "success_update") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text('Supplier Pack updated successfully.');
                    $('#responseHeaderContainer').removeClass("bg-danger");
                    $('#responseHeaderContainer').addClass("bg-success");
                    $('#response-modal').modal("show");

                    $('#response-modal').on('hide.bs.modal', function () {
                        window.location.href = "?page=coil_width";
                    });
                } else if (response.trim() === "success_add") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text('New Supplier Pack added successfully.');
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