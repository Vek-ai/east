<?php
require 'includes/dbconn.php';

$product_category = "";
$category_abreviations = "";
$notes = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['category_id'])){
  $category_id = $_REQUEST['category_id'];
  $query = "SELECT * FROM product_category WHERE product_category_id = '$category_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $product_category_id = $row['product_category_id'];
      $product_category = $row['product_category'];
      $category_abreviations = $row['category_abreviations'];
      $notes = $row['notes'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}

$message = "";
if(!empty($_REQUEST['result'])){
  if($_REQUEST['result'] == '1'){
    $message = "New category added successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '2'){
    $message = "Category updated successfully.";
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
<div class="col-12">
  <!-- start Default Form Elements -->
  <div class="card card-body">
    <div class="row">
      <div class="col-3">
        <h4 class="card-title"><?= $addHeaderTxt ?> Category</h4>
      </div>
      <div class="col-9">
        <h4 class="card-title <?= $textColor ?>"><?= $message ?></h4>
      </div>
    </div>
    

    <form id="categoryForm" class="form-horizontal">
      <div class="row pt-3">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Product Category</label>
            <input type="text" id="product_category" name="product_category" class="form-control"  value="<?= $product_category ?>"/>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Category Abreviations</label>
            <input type="text" id="category_abreviations" name="category_abreviations" class="form-control" value="<?= $category_abreviations ?>" />
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="5"><?= $notes ?></textarea>
      </div>

      <div class="form-actions">
        <div class="card-body border-top ">
          <input type="hidden" id="product_category_id" name="product_category_id" class="form-control"  value="<?= $product_category_id ?>"/>
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
          <h4 class="card-title d-flex justify-content-between align-items-center">Category List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['category_id'])){ ?>
            <a href="/?page=category" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
            <?php } ?> <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
          </h4>
        
        <div class="table-responsive">
       
          <table id="display_category" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th >Product Category</th>
                <th>Category Abreviations</th>
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
$query_product_category = "SELECT * FROM product_category WHERE hidden=0";
$result_product_category = mysqli_query($conn, $query_product_category);            
while ($row_product_category = mysqli_fetch_array($result_product_category)) {
    $product_category_id = $row_product_category['product_category_id'];
    $product_category = $row_product_category['product_category'];
    $category_abreviations = $row_product_category['category_abreviations'];
    $db_status = $row_product_category['status'];
    $notes = $row_product_category['notes'];
   // $last_edit = $row_product_category['last_edit'];
    $date = new DateTime($row_product_category['last_edit']);
    $last_edit = $date->format('m-d-Y');
    if ($row_product_category['status'] == '0') {
        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$product_category_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
    } else {
        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$product_category_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
    }
?>
<tr id="product-row-<?= $no ?>">
    <td><span class="product<?= $no ?> <?php if ($row_product_category['status'] == '0') { echo 'emphasize-strike'; } ?>"><?= $product_category ?></span></td>
    <td><?= $category_abreviations ?></td>
    <td class="notes" style="width:30%;"><?= $notes ?></td>
    <td>Last Edited <?= $last_edit ?> by </td>
    <td><?= $status ?></td>
    <td class="text-center" id="action-button-<?= $no ?>">
        <?php if ($row_product_category['status'] == '0') { ?>
            <a href="#" class="btn btn-light py-1 text-dark hideCategory" data-id="<?= $product_category_id ?>" data-row="<?= $no ?>" style='border-radius: 10%;'>Archive</a>
        <?php } else { ?>
            <a href="/?page=category&category_id=<?= $product_category_id ?>" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a>
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
        var product_category_id = $(this).data('id');
        var status = $(this).data('status');
        var no = $(this).data('no');
        $.ajax({
            url: 'pages/category_ajax.php',
            type: 'POST',
            data: {
                product_category_id: product_category_id,
                status: status,
                action: 'change_status'
            },
            success: function(response) {
                if (response == 'success') {
                    if (status == 1) {
                        $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                        $(".changeStatus[data-no='" + no + "']").data('status', "0");
                        $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                        $('#action-button-' + no).html('<a href="#" class="btn btn-light py-1 text-dark hideCategory" data-id="' + product_category_id + '" data-row="' + no + '" style="border-radius: 10%;">Archive</a>');
                    } else {
                        $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                        $(".changeStatus[data-no='" + no + "']").data('status', "1");
                        $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                        $('#action-button-' + no).html('<a href="/?page=category&category_id=' + product_category_id + '" class="btn btn-primary py-1" style="border-radius: 10%;">Edit</a>');
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

    $(document).on('click', '.hideCategory', function(event) {
        event.preventDefault();
        var product_category_id = $(this).data('id');
        var rowId = $(this).data('row');
        $.ajax({
            url: 'pages/category_ajax.php',
            type: 'POST',
            data: {
                product_category_id: product_category_id,
                action: 'hide_category'
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
 document.addEventListener("DOMContentLoaded", function() {
            var table = $('#display_category').DataTable();
 // Add event listener for the checkbox
 $('#toggleActive').on('change', function() {
        if ($(this).is(':checked')) {
            // Show only active items
            table.rows().every(function() {
                var data = this.data();
                if ($(data[4]).text().trim() === 'Inactive') {
                    $(this.node()).hide();
                } else {
                    $(this.node()).show();
                }
            });
        } else {
            // Show all items
            table.rows().every(function() {
                $(this.node()).show();
            });
        }
    });

    // Initial filter on page load
    $('#toggleActive').trigger('change');
        });
  $(document).ready(function() {
    $('#categoryForm').on('submit', function(event) {
        event.preventDefault(); 

        var formData = new FormData(this);
        formData.append('action', 'add_update');

        var appendResult = "";

        $.ajax({
            url: 'pages/category_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              
              if (response === "Category updated successfully.") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text(response);
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    window.location.href = "?page=category";
                  });
              } else if (response === "New category added successfully.") {
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

    
});
</script>