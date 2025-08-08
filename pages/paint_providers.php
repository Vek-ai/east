<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$provider_name = "";
$contact_person = "";
$contact_email = "";
$contact_phone = "";
$address = "";
$website = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['provider_id'])){
  $provider_id = $_REQUEST['provider_id'];
  $query = "SELECT * FROM paint_providers WHERE provider_id = '$provider_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $provider_id = $row['provider_id'];
      $provider_name = $row['provider_name'];
      $contact_person = $row['contact_person'];
      $contact_email = $row['contact_email'];
      $contact_phone = $row['contact_phone'];
      $address = $row['address'];
      $website = $row['website'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}

$message = "";
if(!empty($_REQUEST['result'])){
  if($_REQUEST['result'] == '1'){
    $message = "New paint provider added successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '2'){
    $message = "Paint provider updated successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '0'){
    $message = "Failed to Perform Operation";
    $textColor = "text-danger";
  }
  
}

?>
<style>
        /* Ensure that the text within the notes column wraps properly */
        td.notes,  td.last-edit{
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
                  <h4 class="font-weight-medium fs-14 mb-0">Paint provider</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="">Paint Properties
                        </a>
                      </li>
                      <li class="breadcrumb-item text-muted" aria-current="page">Paint Provider</li>
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
        <h4 class="card-title"><?= $addHeaderTxt ?> Paint provider</h4>
      </div>
      <div class="col-9">
        <h4 class="card-title <?= $textColor ?>"><?= $message ?></h4>
      </div>
    </div>
    

    <form id="paintProviderForm" class="form-horizontal">
      <div class="row pt-3">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Paint provider</label>
            <input type="text" id="provider_name" name="provider_name" class="form-control"  value="<?= $provider_name ?>"/>
          </div>
        </div>
        
      </div>

      <div class="row pt-3">
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Contact Person</label>
            <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?= $contact_person ?>" />
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" id="contact_email" name="contact_email" class="form-control"  value="<?= $contact_email ?>"/>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" id="contact_phone" name="contact_phone" class="form-control phone-inputmask" value="<?= $contact_phone ?>" />
          </div>
        </div>
      </div>

      <div class="row pt-3">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" id="address" name="address" class="form-control"  value="<?= $address ?>"/>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">Website</label>
            <input type="text" id="website" name="website" class="form-control" value="<?= $website ?>" />
          </div>
        </div>
      </div>

      <div class="form-actions">
        <div class="card-body border-top ">
          <input type="hidden" id="provider_id" name="provider_id" class="form-control"  value="<?= $provider_id ?>"/>
          <div class="row">
            <div class="col-6 text-end"></div>
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
          <h4 class="card-title d-flex justify-content-between align-items-center">Paint provider List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['provider_id'])){ ?>
            <a href="/?page=paint_providers" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
            <?php } ?> <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
          </h4>
        
        <div class="table-responsive">
       
          <table id="display_paint_provider" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th>Paint provider</th>
                <th>Contact Person</th>
                <th>Contact Email</th>
                <th>Contact Phone</th>
                <th>Status</th>
              
                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
<?php
$no = 1;
$query_paint_provider = "SELECT * FROM paint_providers WHERE hidden = '0'";
$result_paint_provider = mysqli_query($conn, $query_paint_provider);            
while ($row_paint_provider = mysqli_fetch_array($result_paint_provider)) {
    $provider_id = $row_paint_provider['provider_id'];
    $paint_provider = $row_paint_provider['provider_name'];
    $contact_person = $row_paint_provider['contact_person'];
    $contact_email = $row_paint_provider['contact_email'];
    $contact_phone = $row_paint_provider['contact_phone'];
    $db_status = $row_paint_provider['provider_status'];
    // if($edited_by != "0"){
    //   $last_user_name = get_name($edited_by);
    // }else if($added_by != "0"){
    //   $last_user_name = get_name($added_by);
    // }else{
    //   $last_user_name = "";
    // }

    if ($row_paint_provider['provider_status'] == '0') {
        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$provider_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
    } else {
        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$provider_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
    }
?>
<tr id="product-row-<?= $no ?>">
    <td><span class="product<?= $no ?> "><?= $paint_provider ?></span></td>
    <td><?= $contact_person ?></td>
    <td><?= $contact_email ?></td>
    <td><?= $contact_phone ?></td>
    <!-- <td class="last-edit" style="width:30%;">Last Edited <?= $last_edit ?> by  <?= $last_user_name ?></td> -->
    <td><?= $status ?></td>
    <td class="text-center" id="action-button-<?= $no ?>">
        <?php if ($row_paint_provider['provider_status'] == '0') { ?>
            <a href="#" title="Archive" class="py-1 text-dark hidePaintProvider" data-id="<?= $provider_id ?>" data-row="<?= $no ?>"><i class="text-danger ti ti-trash fs-7"></i></a>
        <?php } else { ?>
            <a href="/?page=paint_providers&provider_id=<?= $provider_id ?>" title="Edit" class="py-1"><i class="ti ti-pencil fs-7"></i></a>
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
        var provider_id = $(this).data('id');
        var status = $(this).data('status');
        var no = $(this).data('no');
        $.ajax({
            url: 'pages/paint_providers_ajax.php',
            type: 'POST',
            data: {
                provider_id: provider_id,
                status: status,
                action: 'change_status'
            },
            success: function(response) {
                if (response == 'success') {
                  
                    if (status == 1) {
                        $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                        $(".changeStatus[data-no='" + no + "']").data('status', "0");
                        $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                        $('#action-button-' + no).html('<a href="#" title="Archive" class="py-1 text-dark hidePaintProvider" data-id="' + provider_id + '" data-row="' + no + '" ><i class="text-danger ti ti-trash fs-7"></i></a>');
                        $('#toggleActive').trigger('change');
                      } else {
                        $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                        $(".changeStatus[data-no='" + no + "']").data('status', "1");
                        $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                        $('#action-button-' + no).html('<a href="/?page=paint_providers&provider_id=' + provider_id + '" title="Edit" class="py-1" ><i class="ti ti-pencil fs-7"></i></a>');
                        $('#toggleActive').trigger('change');
                      }
                } else {
                    alert('Failed to change status.');
                    console.log(response)
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $(document).on('click', '.hidePaintProvider', function(event) {
        event.preventDefault();
        var provider_id = $(this).data('id');
        var rowId = $(this).data('row');
        $.ajax({
            url: 'pages/paint_providers_ajax.php',
            type: 'POST',
            data: {
                provider_id: provider_id,
                action: 'hide_paint_provider'
            },
            success: function(response) {
                if (response == 'success') {
                    $('#product-row-' + rowId).remove(); // Remove the row from the DOM
                } else {
                    alert('Failed to hide paint provider.');
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
    var table = $('#display_paint_provider').DataTable();
    
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

    $('#paintProviderForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');  
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/paint_providers_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              
              if (response.trim() === "update-success") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("Paint provider updated successfully.");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    window.location.href = "?page=paint_providers";
                  });
              } else if (response.trim() === "add-success") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("New paint provider added successfully.");
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