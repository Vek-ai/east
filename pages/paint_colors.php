<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$color_name = "";
$color_code = "";
$color_group = "";
$provider_id = "";
$ekm_color_code = "";
$ekm_color_no = "";
$ekm_paint_code = "";
$stock_availability = '';
$multiplier_category = '';
$color_abbreviation = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['color_id'])){
  $color_id = $_REQUEST['color_id'];
  $query = "SELECT * FROM paint_colors WHERE color_id = '$color_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $color_id = $row['color_id'];
      $color_name = $row['color_name'];
      $color_code = $row['color_code'];
      $color_group = $row['color_group'];
      $provider_id = $row['provider_id'];
      $availability = $row['stock_availability'];
      $multiplier_category = $row['multiplier_category'];
      $ekm_color_code = $row['ekm_color_code'];
      $ekm_color_no = $row['ekm_color_no'];
      $ekm_paint_code = $row['ekm_paint_code'];
      $color_abbreviation = $row['color_abbreviation'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}

$message = "";
if(!empty($_REQUEST['result'])){
  if($_REQUEST['result'] == '1'){
    $message = "New paint color added successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '2'){
    $message = "Paint color updated successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '0'){
    $message = "Failed to Perform Operation";
    $textColor = "text-danger";
  }
  
}

?>
<style>
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
                  <h4 class="font-weight-medium fs-14 mb-0">Paint Colors</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="">Product Properties
                        </a>
                      </li>
                      <li class="breadcrumb-item text-muted" aria-current="page">Paint Colors</li>
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
        <h4 class="card-title"><?= $addHeaderTxt ?> Paint color</h4>
      </div>
      <div class="col-9">
        <h4 class="card-title <?= $textColor ?>"><?= $message ?></h4>
      </div>
    </div>
    

    <form id="paintColorForm" class="form-horizontal">
      <div class="row pt-0">
        <div class="col-md-12">
          <div class="mb-3">
            <label class="form-label">Color Name</label>
            <input type="text" id="color_name" name="color_name" class="form-control"  value="<?= $color_name ?>"/>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Hex Color</label>
            <input type="color" id="color_code" name="color_code" class="form-control" value="<?= $color_code ?>" />
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">EKM Color Code</label>
            <input type="text" id="ekm_color_code" name="ekm_color_code" class="form-control" value="<?= $ekm_color_code ?>" />
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">EKM Color No</label>
            <input type="text" id="ekm_color_no" name="ekm_color_no" class="form-control" value="<?= $ekm_color_no ?>" />
          </div>
        </div>
        <div class="col-md-3 trim-field screw-fields panel-fields">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label">Color Group</label>
                </div>
                <select id="color_group" class="form-control" name="color_group">
                    <option value="" >Select Color Group...</option>
                    <?php
                    $query_color_group = "SELECT * FROM color_group_name WHERE hidden = '0' ORDER BY color_group_name";
                    $result_color_group = mysqli_query($conn, $query_color_group);
                    while ($row_color_group = mysqli_fetch_array($result_color_group)) {
                        $selected = ($color_group == $row_color_group['color_group_name_id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $row_color_group['color_group_name_id'] ?>" <?= $selected ?>><?= $row_color_group['color_group_name'] ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="col-md-3">
          <div class="mb-3">
            <label class="form-label">Provider</label>
            <select id="provider" class="form-control" name="provider" required>
                <option value="" >Select One...</option>
                <?php
                $query_rows = "SELECT * FROM paint_providers";
                $result_rows = mysqli_query($conn, $query_rows);            
                while ($row_rows = mysqli_fetch_array($result_rows)) {
                  $selected = ($row_rows['provider_id'] == $provider_id) ? 'selected' : '';
                ?>
                    <option value="<?= $row_rows['provider_id'] ?>" <?= $selected ?> ><?= $row_rows['provider_name'] ?></option>
                <?php   
                }
                ?>
            </select>
          </div>
        </div>

        <div class="col-md-3">
          <div class="mb-3">
            <label class="form-label">Color Abbreviation</label>
            <input type="text" id="color_abbreviation" name="color_abbreviation" class="form-control" value="<?= $color_abbreviation ?>" />
          </div>
        </div>
        <div class="col-md-3 opt_field" data-id="5">
            <label class="form-label">Availability</label>
            <div class="mb-3">
                <select id="stock_availability_add" class="form-control select2-add" name="stock_availability">
                    <option value="" >Select Availability...</option>
                    <?php
                    $query_availability = "SELECT * FROM product_availability";
                    $result_availability = mysqli_query($conn, $query_availability);            
                    while ($row_availability = mysqli_fetch_array($result_availability)) {
                      $selected = ($row_availability['product_availability_id'] == $availability) ? 'selected' : '';
                    ?>
                        <option value="<?= $row_availability['product_availability_id'] ?>" <?= $selected ?> ><?= $row_availability['product_availability'] ?></option>
                    <?php   
                    }
                    ?>
                </select>
            </div>
        </div>
      </div>

      <div class="form-actions">
        <div class="card-body border-top ">
          <input type="hidden" id="color_id" name="color_id" class="form-control"  value="<?= $color_id ?>"/>
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
          <h4 class="card-title d-flex justify-content-between align-items-center">Paint color List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['color_id'])){ ?>
            <a href="?page=paint_colors" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
            <?php } ?> <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
          </h4>
        
        <div class="table-responsive">
       
          <table id="display_paint_colors" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th>Color Name</th>
                <th>Hex Color</th>
                <th>Color Group</th>
                <th>Provider</th>
                <th>Details</th>
                <th>Status</th>
              
                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
<?php
$no = 1;
$query_paint_color = "SELECT * FROM paint_colors WHERE hidden=0";
$result_paint_color = mysqli_query($conn, $query_paint_color);            
while ($row_paint_color = mysqli_fetch_array($result_paint_color)) {
    $color_id = $row_paint_color['color_id'];
    $color_name = $row_paint_color['color_name'];
    $color_code = $row_paint_color['color_code'];
    $color_group = getColorGroupName($row_paint_color['color_group']);
    $provider_id = $row_paint_color['provider_id'];
    $db_status = $row_paint_color['color_status'];
   // $last_edit = $row_paint_color['last_edit'];
    $date = new DateTime($row_paint_color['last_edit']);
    $last_edit = $date->format('m-d-Y');

    $added_by = $row_paint_color['added_by'];
    $edited_by = $row_paint_color['edited_by'];

    
    if($edited_by != "0"){
      $last_user_name = get_name($edited_by);
    }else if($added_by != "0"){
      $last_user_name = get_name($added_by);
    }else{
      $last_user_name = "";
    }

    if ($row_paint_color['color_status'] == '0') {
        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$color_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
    } else {
        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$color_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
    }
?>
<tr id="product-row-<?= $no ?>">
    <td><span class="product<?= $no ?> <?php if ($row_paint_color['color_status'] == '0') { echo 'emphasize-strike'; } ?>"><?= $color_name ?></span></td>
    <td><?= $color_code ?></td>
    <td><?= $color_group ?></td>
    <td><?= getPaintProviderName($provider_id) ?></td>
    <td class="last-edit" style="width:30%;">Last Edited <?= $last_edit ?> by  <?= $last_user_name ?></td>
    <td><?= $status ?></td>
    <td class="text-center" id="action-button-<?= $no ?>">
        <?php if ($row_paint_color['color_status'] == '0') { ?>
            <a href="#" class="btn btn-light py-1 text-dark hideProductLine" data-id="<?= $color_id ?>" data-row="<?= $no ?>" style='border-radius: 10%;'>Archive</a>
        <?php } else { ?>
            <a href="?page=paint_colors&color_id=<?= $color_id ?>" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a>
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
        var color_id = $(this).data('id');
        var status = $(this).data('status');
        var no = $(this).data('no');
        $.ajax({
            url: 'pages/paint_colors_ajax.php',
            type: 'POST',
            data: {
                color_id: color_id,
                status: status,
                action: 'change_status'
            },
            success: function(response) {
                if (response == 'success') {
                    if (status == 1) {
                        $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                        $(".changeStatus[data-no='" + no + "']").data('status', "0");
                        $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                        $('#action-button-' + no).html('<a href="#" class="btn btn-light py-1 text-dark hideProductLine" data-id="' + color_id + '" data-row="' + no + '" style="border-radius: 10%;">Archive</a>');
                        $('#toggleActive').trigger('change');
                      } else {
                        $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                        $(".changeStatus[data-no='" + no + "']").data('status', "1");
                        $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                        $('#action-button-' + no).html('<a href="?page=paint_colors&color_id=' + color_id + '" class="btn btn-primary py-1" style="border-radius: 10%;">Edit</a>');
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
        var color_id = $(this).data('id');
        var rowId = $(this).data('row');
        $.ajax({
            url: 'pages/paint_colors_ajax.php',
            type: 'POST',
            data: {
                color_id: color_id,
                action: 'hide_paint_color'
            },
            success: function(response) {
                if (response == 'success') {
                    $('#product-row-' + rowId).remove(); // Remove the row from the DOM
                } else {
                    alert('Failed to hide paint color.');
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
    var table = $('#display_paint_colors').DataTable();
    
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

    $('#paintColorForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/paint_colors_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              
              if (response.trim() === "Paint color updated successfully.") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("Paint color updated successfully.");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    window.location.href = "?page=paint_colors";
                  });
              } else if (response.trim() === "New paint color added successfully.") {
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