<?php
require 'includes/dbconn.php';

$product_line = "";
$line_abreviations = "";
$notes = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['product_line_id'])){
  $product_line_id = $_REQUEST['product_line_id'];
  $query = "SELECT * FROM product_line WHERE product_line_id = '$product_line_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $product_line_id = $row['product_line_id'];
      $product_line = $row['product_line'];
      $line_abreviations = $row['line_abreviations'];
      $notes = $row['notes'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}

$message = "";
if(!empty($_REQUEST['result'])){
  if($_REQUEST['result'] == '1'){
    $message = "New product line added successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '2'){
    $message = "Product line updated successfully.";
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
        <h4 class="card-title"><?= $addHeaderTxt ?> Product Line</h4>
      </div>
      <div class="col-9">
        <h4 class="card-title <?= $textColor ?>"><?= $message ?></h4>
      </div>
    </div>
    

    <form id="lineForm" class="form-horizontal">
      <div class="row pt-3">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Product Product Line</label>
            <input type="text" id="product_line" name="product_line" class="form-control"  value="<?= $product_line ?>"/>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Product Line Abreviations</label>
            <input type="text" id="line_abreviations" name="line_abreviations" class="form-control" value="<?= $line_abreviations ?>" />
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="5"><?= $notes ?></textarea>
      </div>

      <div class="form-actions">
        <div class="card-body border-top ">
          <input type="hidden" id="product_line_id" name="product_line_id" class="form-control"  value="<?= $product_line_id ?>"/>
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
          <h4 class="card-title">Product Line List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['product_line_id'])){ ?>
            <a href="/?page=product_line" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
            <?php } ?>
          </h4>
        
        <div class="table-responsive">
          <table id="display_line" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th >Product Product Line</th>
                <th>Product Line Abreviations</th>
                <th>Notes</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
              <?php
                $no = 1;
                $query_product_line = "SELECT * FROM product_line";
                $result_product_line = mysqli_query($conn, $query_product_line);            
                while ($row_product_line = mysqli_fetch_array($result_product_line)) {
                    $product_line_id = $row_product_line['product_line_id'];
                    $product_line = $row_product_line['product_line'];
                    $line_abreviations = $row_product_line['line_abreviations'];
                    $db_status = $row_product_line['status'];
                    $notes = $row_product_line['notes'];
                    if ($row_product_line['status'] == '0') {
                        $status = "<a href='#' class='changeStatus$no' data-id='$product_line_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                    } else {
                        $status = "<a href='#' class='changeStatus$no' data-id='$product_line_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                    }
                
                ?>
                <tr>
                  <td><?= $product_line ?></td>
                  <td><?= $line_abreviations ?></td>
                  <td class="notes" style="width:30%;"><?= $notes ?></td>
                  <td><?= $status ?></td>
                  <td class="text-center">
                    <a href="/?page=product_line&product_line_id=<?= $product_line_id ?>" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a> 
                  </td>
                </tr>
                <?php 
                
                ?>
                <script>
                    $('.changeStatus<?= $no ?>').on('click', function(event) {
                        event.preventDefault(); 
                        var product_line_id = $(this).data('id');
                        var status = $(this).data('status');
                        $.ajax({
                            url: 'pages/product_line_ajax.php',
                            type: 'POST',
                            data: {
                                product_line_id: product_line_id,
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
            var table = $('#display_line').DataTable();
        });
  $(document).ready(function() {
    $('#lineForm').on('submit', function(event) {
        event.preventDefault(); 

        var formData = new FormData(this);
        formData.append('action', 'add_update');

        var appendResult = "";

        $.ajax({
            url: 'pages/product_line_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              if(response == "Product line updated successfully.") {
                  appendResult = "2";
              }else if(response == "New product line added successfully.") {
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