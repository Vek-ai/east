<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$color_group_name = "";
$group_abbreviations = "";
$notes = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['color_group_name_id'])){
  $color_group_name_id = $_REQUEST['color_group_name_id'];
  $query = "SELECT * FROM color_group_name WHERE color_group_name_id = '$color_group_name_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $color_group_name_id = $row['color_group_name_id'];
      $color_group_name = $row['color_group_name'];
      $group_abbreviations = $row['group_abbreviations'];
      $notes = $row['notes'];
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
        text-decoration: grade-through;
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
                <h4 class="font-weight-medium fs-14 mb-0">Color Group Names</h4>
                <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                    <a class="text-muted text-decoration-none" href="">Product Properties
                    </a>
                    </li>
                    <li class="breadcrumb-item text-muted" aria-current="page">Color Group Name</li>
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
        <div class="col-12">
            <h4 class="card-title"><?= $addHeaderTxt ?> Color Group Name</h4>
        </div>
        </div>
        

        <form id="groupNameForm" class="form-horizontal">
        <div class="row pt-3">
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Color Group Name</label>
                <input type="text" id="color_group_name" name="color_group_name" class="form-control"  value="<?= $color_group_name ?>"/>
            </div>
            </div>
            <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Group Name Abreviations</label>
                <input type="text" id="group_abbreviations" name="group_abbreviations" class="form-control" value="<?= $group_abbreviations ?>" />
            </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="5"><?= $notes ?></textarea>
        </div>

        <div class="form-actions">
            <div class="card-body border-top ">
            <input type="hidden" id="color_group_name_id" name="color_group_name_id" class="form-control"  value="<?= $color_group_name_id ?>"/>
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
            <h4 class="card-title d-flex justify-content-between align-items-center">Color Group Name List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['color_group_name_id'])){ ?>
                <a href="?page=color_group_name" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
                <?php } ?> <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
            </h4>
            
            <div class="table-responsive">
        
            <table id="display_color_group_name" class="table table-striped table-bordered text-nowrap align-middle">
                <thead>
                <!-- start row -->
                <tr>
                    <th>Color Group Name</th>
                    <th>Abreviations</th>
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
                    $query_color_group_name = "SELECT * FROM color_group_name WHERE hidden=0";
                    $result_color_group_name = mysqli_query($conn, $query_color_group_name);            
                    while ($row_color_group_name = mysqli_fetch_array($result_color_group_name)) {
                        $color_group_name_id = $row_color_group_name['color_group_name_id'];
                        $color_group_name = $row_color_group_name['color_group_name'];
                        $group_abbreviations = $row_color_group_name['group_abbreviations'];
                        $product_category = $row_color_group_name['product_category'];
                        $multiplier = $row_color_group_name['multiplier'];
                        $db_status = $row_color_group_name['status'];
                        $notes = $row_color_group_name['notes'];
                        // $last_edit = $row_color_group_name['last_edit'];
                        $date = new DateTime($row_color_group_name['last_edit']);
                        $last_edit = $date->format('m-d-Y');

                        $added_by = $row_color_group_name['added_by'];
                        $edited_by = $row_color_group_name['edited_by'];

                        
                        if($edited_by != "0"){
                        $last_user_name = get_name($edited_by);
                        }else if($added_by != "0"){
                        $last_user_name = get_name($added_by);
                        }else{
                        $last_user_name = "";
                        }

                        if ($row_color_group_name['status'] == '0') {
                            $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$color_group_name_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                        } else {
                            $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$color_group_name_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                        }
                    ?>
                    <tr id="product-row-<?= $no ?>">
                        <td><span class="product<?= $no ?> <?php if ($row_color_group_name['status'] == '0') { echo 'emphasize-strike'; } ?>"><?= $color_group_name ?></span></td>
                        <td><?= $group_abbreviations ?></td>
                        <td class="notes" style="width:30%;"><?= $notes ?></td>
                        <td class="last-edit" style="width:30%;">Last Edited <?= $last_edit ?> by  <?= $last_user_name ?></td>
                        <td><?= $status ?></td>
                        <td class="text-center" id="action-button-<?= $no ?>">
                            <?php if ($row_color_group_name['status'] == '0') { ?>
                                <a href="#" class="py-1 text-dark hideColorGroupName text-decoration-none" data-id="<?= $color_group_name_id ?>" data-row="<?= $no ?>">
                                    <i class="text-danger ti ti-trash fs-7"></i>
                                </a>
                            <?php } else { ?>
                                <a href="?page=color_group_name&color_group_name_id=<?= $color_group_name_id ?>" class="py-1 text-decoration-none">
                                    <i class="text-warning ti ti-pencil fs-7"></i>
                                </a>
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
                        var color_group_name_id = $(this).data('id');
                        var status = $(this).data('status');
                        var no = $(this).data('no');
                        $.ajax({
                            url: 'pages/color_group_names_ajax.php',
                            type: 'POST',
                            data: {
                                color_group_name_id: color_group_name_id,
                                status: status,
                                action: 'change_status'
                            },
                            success: function(response) {
                                if (response == 'success') {
                                    if (status == 1) {
                                        $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                                        $(".changeStatus[data-no='" + no + "']").data('status', "0");
                                        $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                                        $('#action-button-' + no).html('<a href="#" class="text-decoration-none py-1 text-dark hideColorGroupName" data-id="' + color_group_name_id + '" data-row="' + no + '"><i class="text-danger ti ti-trash fs-7"></i></a>');
                                        $('#toggleActive').trigger('change');
                                    } else {
                                        $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                        $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                        $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                                        $('#action-button-' + no).html('<a href="?page=color_group_name&color_group_name_id=' + color_group_name_id + '" class="text-decoration-none py-1"><i class="text-warning ti ti-pencil fs-7"></i></a>');
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

                    $(document).on('click', '.hideColorGroupName', function(event) {
                        event.preventDefault();
                        var color_group_name_id = $(this).data('id');
                        var rowId = $(this).data('row');
                        $.ajax({
                            url: 'pages/color_group_names_ajax.php',
                            type: 'POST',
                            data: {
                                color_group_name_id: color_group_name_id,
                                action: 'hide_color_group_name'
                            },
                            success: function(response) {
                                if (response == 'success') {
                                    $('#product-row-' + rowId).remove(); // Remove the row from the DOM
                                } else {
                                    alert('Failed to hide product Color Group Name.');
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
    var table = $('#display_color_group_name').DataTable();
    
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

    $('#groupNameForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/color_group_names_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              
              if (response === "update-success") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("Color Group Name updated successfully.");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    window.location.href = "?page=color_group_name";
                  });
              } else if (response === "add-success") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("New Color Group Name added successfully.");
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