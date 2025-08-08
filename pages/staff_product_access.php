<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
require 'includes/dbconn.php';
require 'includes/functions.php';

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";
$product_ids = array();

if(!empty($_REQUEST['id'])){
  $staff_product_access_id = $_REQUEST['id'];
  $query = "SELECT * FROM staff_product_access WHERE staff_product_access_id = '$staff_product_access_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $staff_product_access_id = $row['staff_product_access_id'];
      $staff_id = $row['staff_id'];
      $product_id = $row['product_id'];
  }

  $product_ids = explode(",", $product_id);

  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}
?>
<style>
    /* Ensure that the text within the notes column wraps properly */
    td.last-edit, td.products{
        white-space: normal;
        word-wrap: break-word;
    }
    .emphasize-strike {
        text-decoration: line-through;
        font-weight: bold;
        color: #9a841c; /* You can choose any color you like for emphasis */
    }
    .dataTables_filter input {
        width: 100%; /* Adjust the width as needed */
        height: 50px; /* Adjust the height as needed */
        font-size: 16px; /* Adjust the font size as needed */
        padding: 10px; /* Adjust the padding as needed */
        border-radius: 5px; /* Adjust the border-radius as needed */
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
                  <h4 class="font-weight-medium fs-14 mb-0">Staff Product Access</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="">Staff
                        </a>
                      </li>
                      <li class="breadcrumb-item text-muted" aria-current="page">Staff Product Access</li>
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
    <div class="container">
        <div class="row">
            <div class="col">
                <h4 class="card-title"><?= $addHeaderTxt ?> Staff Product Access</h4>
            </div>
            <form id="staffProductAccessForm" class="form-horizontal">
                <div class="row pt-3">
                    <label class="form-label">Staff Name</label>
                    <div class="col-md-6">
                    
                        
                        <select id="select2-staff" class="form-control" name="staff_id">
                            <option value="" ></option>
                            <?php
                            $query_staff = "SELECT * FROM staff WHERE status = '1'";
                            $result_staff = mysqli_query($conn, $query_staff);            
                            while ($row_staff = mysqli_fetch_array($result_staff)) {
                                $selected = ($staff_id == $row_staff['staff_id']) ? 'selected' : '';
                            ?>
                                <option value="<?= $row_staff['staff_id'] ?>" <?= $selected ?>><?= $row_staff['staff_fname'] . " " .$row_staff['staff_lname'] ?></option>
                            <?php   
                            }
                            ?>
                        </select>
                    
                    </div>
                </div>
                
                <div class="row pt-3">
                    <div class="col-md-12">
                    <label class="form-label">Accessible Products</label>
                    <div class="mb-3">
                        <select id="select2-products" class="form-control" name="product_ids[]" multiple>
                            <option value="" ></option>
                            <?php
                            $query_product = "SELECT * FROM product WHERE hidden = '0'";
                            $result_product = mysqli_query($conn, $query_product);            
                            while ($row_product = mysqli_fetch_array($result_product)) {
                                $selected = in_array($row_product['product_id'], $product_ids) ? 'selected' : '';
                            ?>
                                <option value="<?= $row_product['product_id'] ?>" <?= $selected ?>><?= $row_product['product_item'] ?></option>
                            <?php   
                            }
                            ?>
                        </select>
                    </div>
                    </div>
                </div>


                <div class="form-actions">
                    <div class="border-top ">
                    <input type="hidden" id="staff_product_access_id" name="staff_product_access_id" class="form-control"  value="<?= $staff_product_access_id ?>"/>
                    <div class="row mt-2">
                        
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
    </div>
    
    

    
  </div>
  <!-- end Default Form Elements -->
</div>
<div class="col-12">
  <div class="datatables">
    <div class="card">
      <div class="card-body">
          <h4 class="card-title d-flex justify-content-between align-items-center">Staff Product Access List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['staff_product_access_id'])){ ?>
            <a href="/?page=staff_product_access" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
            <?php } ?> <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
          </h4>
        
        <div class="table-responsive">
       
          <table id="display_staff_product_access" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th>Staff Name</th>
                <th>Accessible Products</th>
                <th>Details</th>
                <th>Status</th>
              
                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
            <?php
            $no = 1;
            $query_staff_product_access = "SELECT * FROM staff_product_access WHERE hidden = 0";
            $result_staff_product_access = mysqli_query($conn, $query_staff_product_access);            
            while ($row_staff_product_access = mysqli_fetch_array($result_staff_product_access)) {
                $staff_product_access_id = $row_staff_product_access['staff_product_access_id'];
                $staff_id = $row_staff_product_access['staff_id'];
                $product_id = $row_staff_product_access['product_id'];
                $product_ids = explode(",", $product_id);
                $db_status = $row_staff_product_access['status'];
                $notes = $row_staff_product_access['notes'];

                $date = new DateTime($row_staff_product_access['last_edit']);
                $last_edit = $date->format('m-d-Y');
                $added_by = $row_staff_product_access['added_by'];
                $edited_by = $row_staff_product_access['edited_by'];
                
                if($edited_by != "0"){
                $last_user_name = get_name($edited_by);
                }else if($added_by != "0"){
                $last_user_name = get_name($added_by);
                }else{
                $last_user_name = "";
                }

                if ($row_staff_product_access['status'] == '0') {
                    $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$staff_product_access_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                } else {
                    $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$staff_product_access_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                }
            ?>
            <tr id="product-row-<?= $no ?>">
                <td><span class="product<?= $no ?> <?php if ($row_staff_product_access['status'] == '0') { echo 'emphasize-strike'; } ?>"><?= get_staff_name($staff_id) ?></span></td>
                <td class="products" style="width:30%;">
                    <?php foreach($product_ids as $product_id){ ?>
                        <span class="badge text-bg-primary m-1"><?= getProductName($product_id) ?></span>
                    <?php } ?>
                </td>
                <td class="last-edit" style="width:30%;">Last Edited <?= $last_edit ?> by  <?= $last_user_name ?></td>
                <td><?= $status ?></td>
                <td class="text-center" id="action-button-<?= $no ?>">
                    <?php if ($row_staff_product_access['status'] == '0') { ?>
                        <a href="#" title="Archive" class="btn btn-light py-1 text-dark hideProductLine" data-id="<?= $staff_product_access_id ?>" data-row="<?= $no ?>" style='border-radius: 10%;'>Archive</a>
                    <?php } else { ?>
                        <a href="/?page=staff_product_access&id=<?= $staff_product_access_id ?>" title="Edit" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a>
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
                $("#select2-staff").select2({
                    width: '100%',
                    placeholder: "Select Staff",
                    allowClear: true
                });

                $("#select2-products").select2({
                    multiple: true,
                    width: '100%',
                    placeholder: "Select Products",
                    allowClear: true
                });
                // Use event delegation for dynamically generated elements
                $(document).on('click', '.changeStatus', function(event) {
                    event.preventDefault(); 
                    var staff_product_access_id = $(this).data('id');
                    var status = $(this).data('status');
                    var no = $(this).data('no');
                    $.ajax({
                        url: 'pages/staff_product_access_ajax.php',
                        type: 'POST',
                        data: {
                            staff_product_access_id: staff_product_access_id,
                            status: status,
                            action: 'change_status'
                        },
                        success: function(response) {
                            if (response == 'success') {
                                if (status == 1) {
                                    $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                                    $(".changeStatus[data-no='" + no + "']").data('status', "0");
                                    $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                                    $('#action-button-' + no).html('<a href="#" title="Archive" class="btn btn-light py-1 text-dark hideProductLine" data-id="' + staff_product_access_id + '" data-row="' + no + '" style="border-radius: 10%;">Archive</a>');
                                    $('#toggleActive').trigger('change');
                                } else {
                                    $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                    $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                    $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                                    $('#action-button-' + no).html('<a href="/?page=staff_product_access&id=' + staff_product_access_id + '" title="Edit" class="btn btn-primary py-1" style="border-radius: 10%;">Edit</a>');
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

                $(document).on('click', '.hideProductLine', function(event) {
                    event.preventDefault();
                    var staff_product_access_id = $(this).data('id');
                    var rowId = $(this).data('row');
                    $.ajax({
                        url: 'pages/staff_product_access_ajax.php',
                        type: 'POST',
                        data: {
                            staff_product_access_id: staff_product_access_id,
                            action: 'hide_staff_product_access'
                        },
                        success: function(response) {
                            if (response == 'success') {
                                $('#product-row-' + rowId).remove(); // Remove the row from the DOM
                            } else {
                                alert('Failed to hide staff product access.');
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
    var table = $('#display_staff_product_access').DataTable();
    
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

    $('#staffProductAccessForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/staff_product_access_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              
              if (response === "success") {
                  $('#responseHeader').text("success-update");
                  $('#responseMsg').text('Staff product access updated successfully.');
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    window.location.href = "?page=staff_product_access";
                  });
              } else if (response.trim() === "success-add") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text('New staff product access added successfully.');
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