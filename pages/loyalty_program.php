<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

$trim_id = 43;
$panel_id = 46;

if(!empty($_REQUEST['id'])){
  $loyalty_id = $_REQUEST['id'];
  $query = "SELECT * FROM loyalty_program WHERE loyalty_id = '$loyalty_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $loyalty_id = $row['loyalty_id'];
      $loyalty_program_name = $row['loyalty_program_name'];
      $accumulated_total_orders = $row['accumulated_total_orders'];
      $discount = $row['discount'];
      $date_from = $row['date_from'];
      $date_to = $row['date_to'];
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
            <h4 class="font-weight-medium fs-14 mb-0">Loyalty Programs</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <a class="text-muted text-decoration-none" href="/">Home
                  </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Loyalty Programs</li>
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
          <div class="col-6">
            <h4 class="card-title"><?= $addHeaderTxt ?> Loyalty Program</h4>
          </div>
        </div>
        
        <form id="loyaltyForm" class="form-horizontal">
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Loyalty Program</label>
                        <input type="text" id="loyalty_program_name" name="loyalty_program_name" class="form-control" value="<?= $loyalty_program_name ?>"/>
                    </div>
                </div>
            </div>
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Accumulated Total Orders</label>
                        <input type="number" id="accumulated_total_orders" name="accumulated_total_orders" class="form-control" value="<?= $accumulated_total_orders ?>"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Discount (%)</label>
                        <input type="number" id="discount" name="discount" class="form-control" value="<?= $discount ?>"/>
                    </div>
                </div> 
            </div>
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Validity Date Start</label>
                        <input type="date" id="date_from" name="date_from" class="form-control" value="<?= $date_from ?>"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Validity Date End</label>
                        <input type="date" id="date_to" name="date_to" class="form-control" value="<?= $date_to ?>"/>
                    </div>
                </div> 
            </div>
            <div class="form-actions">
                <div class="card-body border-top pb-0">
                    <input type="hidden" id="loyalty_id" name="loyalty_id" class="form-control" value="<?= $loyalty_id ?>"/>
                    <div class="row">
                        <div class="col-6 text-start"></div>
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
            <h4 class="card-title d-flex justify-content-between align-items-center">Loyalty Programs List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['id'])){ ?>
                <a href="?page=loyalty_program" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
                <?php } ?> <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
            </h4>
            <div class="table-responsive">
          
              <table id="display_loyalty_program" class="table table-striped table-bordered text-nowrap align-middle text-wrap">
                <thead>
                  <tr>
                    <th>Loyalty Programs</th>
                    <th class="text-wrap">Accumulated Total Orders</th>
                    <th>Discount (%)</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $no = 1;
                  $query_loyalty_program = "SELECT * FROM loyalty_program WHERE hidden = 0";
                  $result_loyalty_program = mysqli_query($conn, $query_loyalty_program);            
                  while ($row_loyalty_program = mysqli_fetch_array($result_loyalty_program)) {
                      $loyalty_id = $row_loyalty_program['loyalty_id'];
                      $loyalty_program_name = $row_loyalty_program['loyalty_program_name'];
                      $accumulated_total_orders = $row_loyalty_program['accumulated_total_orders'];
                      $discount = $row_loyalty_program['discount'];
                      $date_from = $row_loyalty_program['date_from'];
                      $date_to = $row_loyalty_program['date_to'];

                      $db_status = $row_loyalty_program['status'];
                      $date = new DateTime($row_loyalty_program['last_edit']);
                      $last_edit = $date->format('m-d-Y');
                      $added_by = $row_loyalty_program['added_by'];
                      $edited_by = $row_loyalty_program['edited_by'];
                      
                      if($edited_by != "0"){
                        $last_user_name = get_name($edited_by);
                      }else if($added_by != "0"){
                        $last_user_name = get_name($added_by);
                      }else{
                        $last_user_name = "";
                      }

                      if ($row_loyalty_program['status'] == '0') {
                          $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$loyalty_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                      } else {
                          $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$loyalty_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                      }
                  ?>
                  <tr id="product-row-<?= $no ?>">
                      <td><span class="product<?= $no ?> <?php if ($row_loyalty_program['status'] == '0') { echo 'emphasize-strike'; } ?>"><?= $loyalty_program_name ?></span></td>
                      <td><?= $accumulated_total_orders ?></td>
                      <td><?= $discount ?></td>
                      <td><?= date("F d, Y", strtotime($date_from)) ?></td>
                      <td><?= date("F d, Y", strtotime($date_to)) ?></td>
                      <td class="last-edit" style="width:30%;">Last Edited <?= $last_edit ?> by  <?= $last_user_name ?></td>
                      <td><?= $status ?></td>
                      <td class="text-center" id="action-button-<?= $no ?>">
                          <?php if ($row_loyalty_program['status'] == '0') { ?>
                              <a href="#" class="btn btn-light py-1 text-dark hideLoyaltyProgram" data-id="<?= $loyalty_id ?>" data-row="<?= $no ?>" style='border-radius: 10%;'>Archive</a>
                          <?php } else { ?>
                              <a href="?page=loyalty_program&id=<?= $loyalty_id ?>" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a>
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
                      $(document).on('click', '.changeStatus', function(event) {
                          event.preventDefault(); 
                          var loyalty_id = $(this).data('id');
                          var status = $(this).data('status');
                          var no = $(this).data('no');
                          $.ajax({
                              url: 'pages/loyalty_program_ajax.php',
                              type: 'POST',
                              data: {
                                  loyalty_id: loyalty_id,
                                  status: status,
                                  action: 'change_status'
                              },
                              success: function(response) {
                                  if (response == 'success') {
                                      if (status == 1) {
                                          $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                                          $(".changeStatus[data-no='" + no + "']").data('status', "0");
                                          $('.product' + no).addClass('emphasize-strike');
                                          $('#action-button-' + no).html('<a href="#" class="btn btn-light py-1 text-dark hideLoyaltyProgram" data-id="' + loyalty_id + '" data-row="' + no + '" style="border-radius: 10%;">Archive</a>');
                                          $('#toggleActive').trigger('change');
                                        } else {
                                          $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                          $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                          $('.product' + no).removeClass('emphasize-strike');
                                          $('#action-button-' + no).html('<a href="?page=loyalty_program&id=' + loyalty_id + '" class="btn btn-primary py-1" style="border-radius: 10%;">Edit</a>');
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

                      $(document).on('click', '.hideLoyaltyProgram', function(event) {
                          event.preventDefault();
                          var loyalty_id = $(this).data('id');
                          var rowId = $(this).data('row');
                          $.ajax({
                              url: 'pages/loyalty_program_ajax.php',
                              type: 'POST',
                              data: {
                                  loyalty_id: loyalty_id,
                                  action: 'hide_loyalty_program'
                              },
                              success: function(response) {
                                  if (response == 'success') {
                                      $('#product-row-' + rowId).remove();
                                  } else {
                                      alert('Failed to hide loyalty program.');
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
        var table = $('#display_loyalty_program').DataTable();

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

        $('#loyaltyForm').on('submit', function(event) {
            event.preventDefault(); 
            var userid = getCookie('userid');
            var formData = new FormData(this);
            formData.append('action', 'add_update');
            formData.append('userid', userid);

            var appendResult = "";

            $.ajax({
                url: 'pages/loyalty_program_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                  
                  if (response.trim() === "success_update") {
                      $('#responseHeader').text("Success");
                      $('#responseMsg').text("Loyalty Program updated successfully.");
                      $('#responseHeaderContainer').removeClass("bg-danger");
                      $('#responseHeaderContainer').addClass("bg-success");
                      $('#response-modal').modal("show");

                      $('#response-modal').on('hide.bs.modal', function () {
                          location.reload();
                      });
                  } else if (response.trim() === "success_add") {
                      $('#responseHeader').text("Success");
                      $('#responseMsg').text("New loyalty program added successfully.");
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