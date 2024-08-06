<?php
require 'includes/dbconn.php';

$warehouse_id = "";
$warehouse_name = "";
$location = "";
$warehouse_capacity = "";
$warehouse_rows = "";
$shelf = "";
$bin = "";
$bin_capacity = "";
$count_date = "";
$contact_person = "";
$contact_phone = "";
$contact_email = "";
$status = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['warehouse_id'])){
  $warehouse_id = $_REQUEST['warehouse_id'];
  $query = "SELECT * FROM warehouse WHERE warehouse_id = '$warehouse_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $warehouse_id = $row['warehouse_id'];
      $warehouse_name = $row['warehouse_name'];
      $location = $row['location'];
      $warehouse_capacity = $row['warehouse_capacity'];
      $warehouse_rows = $row['warehouse_rows'];
      $shelf = $row['shelf'];
      $bin = $row['bin'];
      $bin_capacity = $row['bin_capacity'];
      $count_date = $row['count_date'];
      $contact_person = $row['contact_person'];
      $contact_phone = $row['contact_phone'];
      $contact_email = $row['contact_email'];
      $status = $row['status'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}


if (isset($_REQUEST['result'])) {
    switch ($_REQUEST['result']) {
        case '0':
            $message = "Failed to Perform Operation.";
            $textColor = "text-danger";
            break;
        case '1':
            $message = "New warehouse added successfully.";
            $textColor = "text-success";
            break;
        case '2':
            $message = "Warehouse updated successfully.";
            $textColor = "text-success";
            break;
        default:
            $message = "Unknown operation.";
            $textColor = "text-warning";
            break;
    }
}



?>
<style>
    /* Ensure that the text within the notes column wraps properly */
    td.notes {
        white-space: normal;
        word-wrap: break-word;
    }
</style>
<div class="col-12">
  <!-- start Default Form Elements -->
  <div class="card card-body">
    <div class="row">
      <div class="col-3">
        <h4 class="card-title"><?= $addHeaderTxt ?> Warehouse</h4>
      </div>
      <div class="col-9">
        <h4 class="card-title <?= $textColor ?>"><?= $message ?></h4>
      </div>
    </div>
    

    <form id="warehouseForm" class="form-horizontal">
        <div class="mb-3">
            <label class="form-label">Warehouse Name</label>
            <input type="text" id="warehouse_name" name="warehouse_name" class="form-control"  value="<?= $warehouse_name ?>"/>
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" id="location" name="location" class="form-control" value="<?= $location ?>" />
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Warehouse Capacity</label>
                <input type="text" id="warehouse_capacity" name="warehouse_capacity" class="form-control" value="<?= $warehouse_capacity ?>" />
            </div>
            </div>
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Rows</label>
                <input type="text" id="warehouse_rows" name="warehouse_rows" class="form-control" value="<?= $warehouse_rows ?>" />
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Shelf</label>
                <input type="text" id="shelf" name="shelf" class="form-control" value="<?= $shelf ?>" />
            </div>
            </div>
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Bin</label>
                <input type="text" id="bin" name="bin" class="form-control" value="<?= $bin ?>" />
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Bin Capacity</label>
                <input type="text" id="bin_capacity" name="bin_capacity" class="form-control" value="<?= $bin_capacity ?>" />
            </div>
            </div>
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Contact Person</label>
                <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?= $contact_person ?>" />
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Count Date</label>
                <input type="date" id="count_date" name="count_date" class="form-control" value="<?= $count_date ?>" />
            </div>
            </div>
        </div>

        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Contact Phone</label>
                <input type="text" id="contact_phone" name="contact_phone" class="form-control" value="<?= $contact_phone ?>" />
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Contact Email</label>
                <input type="text" id="contact_email" name="contact_email" class="form-control" value="<?= $contact_email ?>" />
            </div>
            </div>
        </div>

      <div class="form-actions">
        <div class="card-body border-top ">
          <input type="hidden" id="warehouse_id" name="warehouse_id" class="form-control"  value="<?= $warehouse_id ?>"/>
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
  <div >
    <div class="card">
      <div class="card-body">
          <h4 class="card-title">Warehouse List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['warehouse_id'])){ ?>
            <a href="/?page=warehouse" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
            <?php } ?>
          </h4>
        
        <div class="table-responsive">
          <table id="display_warehouse" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th>Warehouse Name</th>
                <th>Location</th>
                <th>Warehouse Capacity</th>
                <th>Rows</th>
                <th>Shelf</th>
                <th>Bin</th>
                <th>Bin Capacity</th>
                <th>Count Date</th>
                <th>Contact Person</th>
                <th>Contact Phone</th>
                <th>Contact Email</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
              <!-- end row -->
            </thead>
            <tbody>
              <?php
                $no = 1;
                $query_warehouse = "SELECT * FROM warehouse";
                $result_warehouse = mysqli_query($conn, $query_warehouse);            
                while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                    $warehouse_id = $row_warehouse['warehouse_id'];
                    $warehouse_name = $row_warehouse['warehouse_name'];
                    $location = $row_warehouse['location'];
                    $warehouse_capacity = $row_warehouse['warehouse_capacity'];
                    $warehouse_rows = $row_warehouse['warehouse_rows'];
                    $shelf = $row_warehouse['shelf'];
                    $bin = $row_warehouse['bin'];
                    $bin_capacity = $row_warehouse['bin_capacity'];
                    $count_date = $row_warehouse['count_date'];
                    $contact_person = $row_warehouse['contact_person'];
                    $contact_phone = $row_warehouse['contact_phone'];
                    $contact_email = $row_warehouse['contact_email'];
                    $status = $row_warehouse['status'];
                    if ($row_warehouse['status'] == '0') {
                        $status = "<a href='#' class='changeStatus$no' data-id='$warehouse_id' data-status='$status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                    } else {
                        $status = "<a href='#' class='changeStatus$no' data-id='$warehouse_id' data-status='$status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                    }
                
                ?>
                <tr>
                  <td><?= $warehouse_name ?></td>
                  <td><?= $location ?></td>
                  <td><?= $warehouse_capacity ?></td>
                  <td><?= $warehouse_rows ?></td>
                  <td><?= $shelf ?></td>
                  <td><?= $bin ?></td>
                  <td><?= $bin_capacity ?></td>
                  <td><?= $count_date ?></td>
                  <td><?= $contact_person ?></td>
                  <td><?= $contact_phone ?></td>
                  <td><?= $contact_email ?></td>
                  <td><?= $status ?></td>
                  <td class="text-center">
                    <a href="/?page=warehouse&warehouse_id=<?= $warehouse_id ?>" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a> 
                  </td>
                </tr>
                <?php 
                
                ?>
                <script>
                    $('.changeStatus<?= $no ?>').on('click', function(event) {
                        event.preventDefault(); 
                        var warehouse_id = $(this).data('id');
                        var status = $(this).data('status');
                        $.ajax({
                            url: 'pages/warehouse_ajax.php',
                            type: 'POST',
                            data: {
                                warehouse_id: warehouse_id,
                                status: status,
                                action: 'change_status'
                            },
                            success: function(response) {
                              if(response == 'success'){
                                if (status == 1) {
                                    $('#status-alert<?= $no ?>').removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                                    $(".changeStatus<?= $no ?>").data('status', "0");
                                } else {
                                    $('#status-alert<?= $no ?>').removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                    $(".changeStatus<?= $no ?>").data('status', "1");
                                }
                              }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                alert('Error: ' + textStatus + ' - ' + errorThrown);
                            }
                        });
                    });
                </script>
                <?php
                $no++;
                } 
                ?>
            </tbody>
            
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="response-modal" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header d-flex align-items-center">
        
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h4 id="responseHeader"></h4>
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
    document.addEventListener("DOMContentLoaded", function() {
        var table = $('#display_warehouse').DataTable();
    });
    $(document).ready(function() {
        $('#warehouseForm').on('submit', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            var appendResult = "";

            $.ajax({
                url: 'pages/warehouse_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                if(response == "Warehouse updated successfully.") {
                    appendResult = "2";
                }else if(response == "New warehouse added successfully.") {
                    appendResult = "1";
                } else {
                    appendResult = "0";
                }

                var currentUrl = new URL(window.location.href);

                currentUrl.searchParams.set('result', appendResult);

                window.location.href = currentUrl.toString();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });
    });
</script>