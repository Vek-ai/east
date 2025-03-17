<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$emp_role = "";
$role_desc = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['emp_role_id'])){
  $emp_role_id = $_REQUEST['emp_role_id'];
  $query = "SELECT * FROM staff_roles WHERE emp_role_id = '$emp_role_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $emp_role_id = $row['emp_role_id'];
      $emp_role = $row['emp_role'];
      $role_desc = $row['role_desc'];
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
            <h4 class="font-weight-medium fs-14 mb-0">Employee Roles</h4>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">s
                <li class="breadcrumb-item">
                  <a class="text-muted text-decoration-none" href="">Product Properties
                  </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Categories</li>
              </ol>
            </nav>
          </div>
          <div>
            <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
              
            </div>
          </div>
        </div>
      </div>
    </div>

<div class="col-12">
  <div class="datatables">
    <div class="card">
      <div class="card-body">
          <h4 class="card-title d-flex justify-content-between align-items-center">Employee Roles List  &nbsp;&nbsp; 
          <button type="button" id="addRoleModalBtn" class="btn btn-primary d-flex align-items-center" data-id="" data-type="add">
              <i class="ti ti-plus text-white me-1 fs-5"></i> Add Role
          </button>
          <div> 
            <input type="checkbox" id="toggleActive" checked> Show Active Only
          </div>
          </h4>
        
        <div class="table-responsive">
       
          <table id="display_employee_roles" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <tr>
                <th>Employee Role</th>
                <th>Role Description</th>
                <th>Details</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              $query_employee_roles = "SELECT * FROM staff_roles WHERE hidden = 0";
              $result_employee_roles = mysqli_query($conn, $query_employee_roles);            
              while ($row_employee_roles = mysqli_fetch_array($result_employee_roles)) {
                  $emp_role_id = $row_employee_roles['emp_role_id'];
                  $emp_role = $row_employee_roles['emp_role'];
                  $role_desc = $row_employee_roles['role_desc'];
                  $db_status = $row_employee_roles['status'];

                  $date = new DateTime($row_employee_roles['last_edit']);
                  $last_edit = $date->format('m-d-Y');

                  $added_by = $row_employee_roles['added_by'];
                  $edited_by = $row_employee_roles['edited_by'];

                  
                  if($edited_by != "0"){
                    $last_user_name = get_name($edited_by);
                  }else if($added_by != "0"){
                    $last_user_name = get_name($added_by);
                  }else{
                    $last_user_name = "";
                  }

                  if ($row_employee_roles['status'] == '0') {
                      $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$emp_role_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                  } else {
                      $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$emp_role_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                  }
              ?>
              <tr id="product-row-<?= $no ?>">
                  <td>
                      <a href="#" id="view_emp_list" data-id="<?= $emp_role_id ?>">
                        <span class="product<?= $no ?> <?php if ($row_employee_roles['status'] == '0') { echo 'emphasize-strike'; } ?>"><?= $emp_role ?></span>
                      </a>
                  </td>
                  <td class="notes" style="width:30%;"><?= $role_desc ?></td>
                  <td class="last-edit" style="width:30%;">Last Edited <?= $last_edit ?> by  <?= $last_user_name ?></td>
                  <td><?= $status ?></td>
                  <td class="text-center" id="action-button-<?= $no ?>">
                      <?php if ($row_employee_roles['status'] == '0') { ?>
                          <a href="#" class="btn btn-light py-1 text-dark hideCategory" data-id="<?= $emp_role_id ?>" data-row="<?= $no ?>" style='border-radius: 10%;'>Archive</a>
                      <?php } else { ?>
                          <a href="#" id="addRoleModalBtn" class="d-flex align-items-center justify-content-center text-decoration-none" data-id="<?= $emp_role_id ?>" data-type="edit">
                            <i class="ti ti-pencil fs-7"></i>
                          </button>
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
                    var emp_role_id = $(this).data('id');
                    var status = $(this).data('status');
                    var no = $(this).data('no');
                    $.ajax({
                        url: 'pages/staff_role_ajax.php',
                        type: 'POST',
                        data: {
                            emp_role_id: emp_role_id,
                            status: status,
                            action: 'change_status'
                        },
                        success: function(response) {
                            if (response == 'success') {
                                if (status == 1) {
                                    $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                                    $(".changeStatus[data-no='" + no + "']").data('status', "0");
                                    $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                                    $('#action-button-' + no).html('<a href="#" class="btn btn-light py-1 text-dark hideCategory" data-id="' + emp_role_id + '" data-row="' + no + '" style="border-radius: 10%;">Archive</a>');
                                    $('#toggleActive').trigger('change');
                                  } else {
                                    $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                    $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                    $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                                    $('#action-button-' + no).html('<a href="?page=employee_roles&emp_role_id=' + emp_role_id + '" class="btn btn-primary py-1" style="border-radius: 10%;">Edit</a>');
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

                $(document).on('click', '.hideCategory', function(event) {
                    event.preventDefault();
                    var emp_role_id = $(this).data('id');
                    var rowId = $(this).data('row');
                    $.ajax({
                        url: 'pages/staff_role_ajax.php',
                        type: 'POST',
                        data: {
                            emp_role_id: emp_role_id,
                            action: 'hide_employee_role'
                        },
                        success: function(response) {
                            if (response == 'success') {
                                $('#product-row-' + rowId).remove(); // Remove the row from the DOM
                            } else {
                                alert('Failed to hide employee role.');
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

<div class="modal fade" id="employee_list_modal" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header align-items-center modal-colored-header">
        <h4 id="employeeListHeader" class="m-0">Employee List</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="employeeList" class="row align-items-center"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
          Close
        </button>
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
        <div id="responseMsg"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="add-role-header">
                    Add
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="employeeRoleForm" class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                          <div id="add-fields" class=""></div>
                          <div class="form-actions">
                              <div class="border-top">
                                  <div class="row mt-2">
                                      <div class="col-6 text-start"></div>
                                      <div class="col-6 text-end ">
                                          <button type="submit" class="btn btn-primary" style="border-radius: 10%;">Save</button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                        </div>
                        
                    </div>
                </div>
                
            </form>
        </div>
    </div>
</div>

<script>
  $(document).ready(function() {
    var table = $('#display_employee_roles').DataTable({
        pageLength: 100
    });

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

    $(document).on('click', '#view_emp_list', function(event) {
        event.preventDefault();
        var emp_role_id = $(this).data('id');
        console.log(emp_role_id)
        $.ajax({
                url: 'pages/staff_role_ajax.php',
                type: 'POST',
                data: {
                    emp_role_id: emp_role_id,
                    action: "fetch_emp_list"
                },
                success: function(response) {
                    $('#employeeList').html(response);
                    $('#employee_list_modal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
        });
    });

    $(document).on('click', '#addRoleModalBtn', function(event) {
        event.preventDefault();
        var id = $(this).data('id') || '';
        var type = $(this).data('type') || '';
        $('#role_id').val(id);

        if(type == 'edit'){
          $('#add-role-header').html('Update Employee Role');
        }else{
          $('#add-role-header').html('Add Employee Role');
        }

        $.ajax({
            url: 'pages/staff_role_ajax.php',
            type: 'POST',
            data: {
              id : id,
              action: 'fetch_modal_content'
            },
            success: function (response) {
                $('#add-fields').html(response);
                $('#addRoleModal').modal('show');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.error('Response:', jqXHR.responseText);

                $('#responseHeader').text("Error");
                $('#responseMsg').text("An error occurred while processing your request.");
                $('#responseHeaderContainer').removeClass("bg-success").addClass("bg-danger");
                $('#response-modal').modal("show");
            }
        });

        $('#addRoleModal').modal('show');
    });

    $('#employeeRoleForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/staff_role_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              $('.modal').modal("hide");
              if (response === "Employee role updated successfully.") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text(response);
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    window.location.href = "?page=employee_roles";
                  });
              } else if (response === "New employee role added successfully.") {
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