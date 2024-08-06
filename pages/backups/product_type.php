<?php
require 'includes/dbconn.php';

$product_type = "";
$type_abreviations = "";
$notes = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['product_type_id'])){
  $product_type_id = $_REQUEST['product_type_id'];
  $query = "SELECT * FROM product_type WHERE product_type_id = '$product_type_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $product_type_id = $row['product_type_id'];
      $product_type = $row['product_type'];
      $type_abreviations = $row['type_abreviations'];
      $notes = $row['notes'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}

$message = "";
if(!empty($_REQUEST['result'])){
  if($_REQUEST['result'] == '1'){
    $message = "New product type added successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '2'){
    $message = "Product type updated successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '0'){
    $message = "Failed to Perform Operation";
    $textColor = "text-danger";
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
        <h4 class="card-title"><?= $addHeaderTxt ?> Product Type</h4>
      </div>
      <div class="col-9">
        <h4 class="card-title <?= $textColor ?>"><?= $message ?></h4>
      </div>
    </div>
    

    <form id="typeForm" class="form-horizontal">
      <div class="row pt-3">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Product Product Type</label>
            <input type="text" id="product_type" name="product_type" class="form-control"  value="<?= $product_type ?>"/>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Product Type Abreviations</label>
            <input type="text" id="type_abreviations" name="type_abreviations" class="form-control" value="<?= $type_abreviations ?>" />
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="5"><?= $notes ?></textarea>
      </div>

      <div class="form-actions">
        <div class="card-body border-top ">
          <input type="hidden" id="product_type_id" name="product_type_id" class="form-control"  value="<?= $product_type_id ?>"/>
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
          <h4 class="card-title">Product Type List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['product_type_id'])){ ?>
            <a href="/?page=product_type" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
            <?php } ?>
          </h4>
        
        <div class="table-responsive">
          <table id="display_type" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th >Product Product Type</th>
                <th>Product Type Abreviations</th>
                <th>Notes</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
              <?php
                $no = 1;
                $query_product_type = "SELECT * FROM product_type";
                $result_product_type = mysqli_query($conn, $query_product_type);            
                while ($row_product_type = mysqli_fetch_array($result_product_type)) {
                    $product_type_id = $row_product_type['product_type_id'];
                    $product_type = $row_product_type['product_type'];
                    $type_abreviations = $row_product_type['type_abreviations'];
                    $db_status = $row_product_type['status'];
                    $notes = $row_product_type['notes'];
                    if ($row_product_type['status'] == '0') {
                        $status = "<a href='#' class='changeStatus$no' data-id='$product_type_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                    } else {
                        $status = "<a href='#' class='changeStatus$no' data-id='$product_type_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                    }
                
                ?>
                <tr>
                  <td><?= $product_type ?></td>
                  <td><?= $type_abreviations ?></td>
                  <td class="notes" style="width:30%;"><?= $notes ?></td>
                  <td><?= $status ?></td>
                  <td class="text-center">
                    <a href="/?page=product_type&product_type_id=<?= $product_type_id ?>" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a> 
                  </td>
                </tr>
                <?php 
                
                ?>
                <script>
                    $('.changeStatus<?= $no ?>').on('click', function(event) {
                        event.preventDefault(); 
                        var product_type_id = $(this).data('id');
                        var status = $(this).data('status');
                        $.ajax({
                            url: 'pages/product_type_ajax.php',
                            type: 'POST',
                            data: {
                                product_type_id: product_type_id,
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
            var table = $('#display_type').DataTable();
        });
  $(document).ready(function() {
    $('#typeForm').on('submit', function(event) {
        event.preventDefault(); 

        var formData = new FormData(this);
        formData.append('action', 'add_update');

        var appendResult = "";

        $.ajax({
            url: 'pages/product_type_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              if(response == "Product type updated successfully.") {
                  appendResult = "2";
              }else if(response == "New product type added successfully.") {
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