<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$payment_setting_name = "";
$value = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['id'])){
  $id = $_REQUEST['id'];
  $query = "SELECT * FROM payment_settings WHERE payment_setting_id = '$id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $id = $row['payment_setting_id'];
      $payment_setting_name = $row['payment_setting_name'];
      $value = $row['value'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}
$no = 1;
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
        <h4 class="font-weight-medium fs-14 mb-0">Payment Settings</h4>
        <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
            <a class="text-muted text-decoration-none" href="">Settings
            </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Payment Settings</li>
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
            <h4 class="card-title"><?= $addHeaderTxt ?> Settings</h4>
        </div>
        </div>
        

        <form id="paymentSettingForm" class="form-horizontal">
        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Setting Name</label>
                <input type="text" id="payment_setting_name" name="payment_setting_name" class="form-control"  value="<?= $payment_setting_name ?>" <?= !empty($_REQUEST['id']) ? 'readonly' : '' ?>/>
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Value</label>
                <input type="text" id="value" name="value" class="form-control" value="<?= $value ?>" />
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
</div>
<div class="col-12">
  <div class="datatables">
    <div class="card">
      <div class="card-body">
          <h4 class="card-title d-flex justify-content-between align-items-center">Payment Settings List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['id'])){ ?>
            <a href="/?page=settings" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
             <?php } ?> <!-- <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div> -->
          </h4>
        
        <div class="table-responsive">
       
          <table id="display_settings" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th>Payment Setting</th>
                <th>Value</th>
              
                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
              <?php
              $query_setting_name = "SELECT * FROM payment_settings";
              $result_setting_name = mysqli_query($conn, $query_setting_name);            
              while ($row_setting = mysqli_fetch_array($result_setting_name)) {
                  $payment_setting_id = $row_setting['payment_setting_id'];
                  $payment_setting_name = $row_setting['payment_setting_name'];
                  $value = $row_setting['value'];
              ?>
              <tr id="settings-row-<?= $no ?>">
                  <td><?= $payment_setting_name ?></td>
                  <td><?= $value ?></td>
                  <td class="text-center" id="action-button-<?= $no ?>">
                          <a href="/?page=payment_settings&id=<?= $payment_setting_id ?>" title="Edit" class="py-1"><i class="ti ti-pencil fs-7"></i></a>
                          <a class="py-1 text-light deleteSettings" title="Archive" data-id="<?= $payment_setting_id ?>" data-row="<?= $no ?>" ><i class="text-danger ti ti-trash fs-7"></i></a>

                  </td>
              </tr>
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
    var table = $('#display_settings').DataTable();
    
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

    $('#paymentSettingForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/payment_settings_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              
              if (response.trim() === "success_update") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text('Payment setting updated successfully.');
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                      location.reload();
                  });
              } else if (response === "success_add") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text('New payment setting added successfully.');
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
                console.error('Error: ', textStatus, errorThrown, jqXHR.responseText);
                alert('Error: Failed to Update');
            }
        });
    });

    $(document).on('click', '.deleteSettings', function(event) {
      event.preventDefault();
      
      var id = $(this).data('id');
      var row = $(this).data('row');
      
      var userConfirmation = confirm("Are you sure you want to delete this setting?");
      if (!userConfirmation) {
          return;
      }
      
      $.ajax({
          url: 'pages/payment_settings_ajax.php',
          type: 'POST',
          data: {
              id: id,
              action: 'delete'
          },
          success: function(response) {
              if (response.trim() == "success_delete") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text('Payment Setting deleted successfully.');
                  $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                  $('#response-modal').modal("show");
                  
                  $('#response-modal').on('hide.bs.modal', function () {
                      window.location.href = "?page=payment_settings";
                  });
              } else {
                  alert('Failed to delete the payment setting.');
              }
          },
          error: function(jqXHR, textStatus, errorThrown) {
              console.error('Error: ', textStatus, errorThrown, jqXHR.responseText);
              alert('Error: Failed to Update');
          }
      });
  });

});
</script>