<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'includes/dbconn.php';
require 'includes/functions.php';

if(!empty($_REQUEST['staff_id'])){
    $staff_id = $_REQUEST['staff_id'];
    $query = "SELECT * FROM staff WHERE staff_id = '$staff_id'";
    $result = mysqli_query($conn, $query);            
    while ($row = mysqli_fetch_array($result)) {
    }
  }

?>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"> Staff</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Staff</li>
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

    <div class="widget-content searchable-container list">
    <div class="card card-body">
        <div class="row">
        <div class="col-md-4 col-xl-3">
            <!-- <form class="position-relative">
            <input type="text" class="form-control product-search ps-5" id="input-search" placeholder="Search Contacts..." />
            <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </form> -->
        </div>
        <div class="col-md-8 col-xl-9 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0">
            <div class="action-btn show-btn">
            <a href="javascript:void(0)" class="delete-multiple bg-danger-subtle btn me-2 text-danger d-flex align-items-center ">
                <i class="ti ti-trash me-1 fs-5"></i> Delete All Row
            </a>
            </div>
            <button type="button" id="addStaffModalLabel" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                <i class="ti ti-users text-white me-1 fs-5"></i> Add Staff
            </button>
        </div>
        </div>
    </div>

    <div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Staff</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add_staff" class="form-horizontal">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body">
                                <input type="hidden" id="staff_id" name="staff_id" class="form-control" />

                                <div class="row">
                                    <div class="card-body p-0">
                                        <h4 class="card-title text-center">Profile Picture</h4>
                                        <div class="text-center">
                                            <?php 
                                                $profile_path = "../assets/images/profile/user-3.jpg";
                                            ?>
                                            <img src="<?= $profile_path ?>" id="profile_img_add" alt="profile-picture" class="img-fluid rounded-circle" width="120" height="120">
                                            <div class="d-flex align-items-center justify-content-center my-4 gap-6">
                                            <button id="upload_profile_add" type="button" class="btn btn-primary">Upload</button>
                                            <button id="reset_profile_add" type="button" class="btn bg-danger-subtle text-danger">Reset</button>
                                            </div>
                                            <input type="file" id="profile_path_add" name="profile_path" class="form-control" style="display: none;"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Staff First Name</label>
                                            <input type="text" id="staff_fname" name="staff_fname" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Staff Last Name</label>
                                            <input type="text" id="staff_lname" name="staff_lname" class="form-control" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Role</label>
                                            <select id="role" class="form-control" name="role">
                                                <option value="/" >Select One...</option>
                                                <?php
                                                $query_roles = "SELECT * FROM staff_roles WHERE hidden = '0'";
                                                $result_roles = mysqli_query($conn, $query_roles);            
                                                while ($row_staff = mysqli_fetch_array($result_roles)) {
                                                ?>
                                                    <option value="<?= $row_staff['emp_role_id'] ?>" ><?= $row_staff['emp_role'] ?></option>
                                                <?php   
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-4 mb-7">
                                        <label class="form-label">Phone number</label>
                                        <input type="text" id="phone" name="phone" class="form-control phone-inputmask" />
                                    </div>
                                    <div class="col-4 mb-7">
                                        <label class="form-label">Email address</label>
                                        <input type="email" id="email" name="email" class="form-control" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 mb-9">
                                        <label class="form-label">Address</label>
                                        <input type="text" id="address" name="address" class="form-control" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4 mb-7">
                                        <label class="form-label">City</label>
                                        <input type="text" id="city" name="city" class="form-control" />
                                    </div>
                                    <div class="col-4 mb-7">
                                        <label class="form-label">State</label>
                                        <input type="text" id="state" name="state" class="form-control" />
                                    </div>
                                    <div class="col-4 mb-7">
                                        <label class="form-label">Zip</label>
                                        <input type="text" id="zip" name="zip" class="form-control" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-7">
                                        <label class="form-label">Emergency Contact Name</label>
                                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" />
                                    </div>
                                    <div class="col-6 mb-7">
                                        <label class="form-label">Emergency Contact Phone</label>
                                        <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control phone-inputmask" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4 mb-7">
                                        <label class="form-label">Driver Medical Certificate</label>
                                        <input type="text" id="driver_med_cert" name="driver_med_cert" class="form-control" />
                                    </div>
                                    <div class="col-4 mb-7">
                                        <label class="form-label">Driver Class</label>
                                        <input type="text" id="driver_class" name="driver_class" class="form-control" />
                                    </div>
                                    <div class="col-4 mb-7">
                                        <label class="form-label">License Renewal Date</label>
                                        <input type="date" id="license_renewal_date" name="license_renewal_date" class="form-control" />
                                    </div>
                                </div>
                            
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="form-actions">
                            <div class="card-body">
                                <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="updateStaffModal" tabindex="-1" role="dialog" aria-labelledby="updateStaffModal" aria-hidden="true">
        
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

    
    <div class="card card-body">
        <div class="table-responsive">
        <table id="staffList" class="table search-table align-middle text-nowrap">
            <thead class="header-item">
            <th>Staff Name</th>
            <th>Role</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Details</th>
            <th>Action</th>
            </thead>
            <tbody>
            <?php
                $no = 1;
                $query_staff = "SELECT * FROM staff";
                $result_staff = mysqli_query($conn, $query_staff);            
                while ($row_staff = mysqli_fetch_array($result_staff)) {
                    $staff_id = $row_staff['staff_id'];
                    $db_status = $row_staff['status'];

                    if ($row_staff['status'] == '0') {
                        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$staff_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                    } else {
                        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$staff_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                    }

                    if(!empty($row_staff['profile_path'])){
                        $profile_path = $row_staff['profile_path'];
                    }else{
                        $profile_path = "images/staff/user.jpg";
                    }
                ?>
                    <!-- start row -->
                    <tr class="search-items">
                        <td>
                        <a href="#">
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= $profile_path ?>" alt="user4" width="60" height="60" class="rounded-circle">
                                <div>
                                    <?= $row_staff['staff_fname'] ." " .$row_staff['staff_lname'] ?>
                                </div>
                            </div>
                        </a>
                        </td>
                        <td><?= get_role_name($row_staff['role']) ?></td>
                        <td><?= $row_staff['email'] ?></td>
                        <td><?= $row_staff['phone'] ?></td>
                        <td><?= $status ?></td>
                        <td>
                            <div class="action-btn">
                                <a href="#" id="view_staff_btn" class="text-primary edit" data-id="<?= $row_staff['staff_id'] ?>">
                                    <i class="ti ti-eye fs-5"></i>
                                </a>
                                <!-- <a href="javascript:void(0)" class="text-dark delete ms-2" data-id="<?= $row_staff['staff_id'] ?>">
                                    <i class="ti ti-trash fs-5"></i>
                                </a> -->
                            </div>
                        </td>
                    </tr>
                <?php 
                $no++;
                } ?>
            </tbody>
        </table>
        </div>
    </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('#staffList').DataTable({
            "order": [[0, "asc"]] // Column index is 0-based, so column 2 is index 1
        });

        $(document).on('click', '#upload_profile_add', function(event) {
            event.preventDefault(); 
            $('#profile_path_add').click();
        });

        $(document).on('change', '#profile_path_add', function(event) {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    $('#profile_img_add').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        $(document).on('click', '#reset_profile_add', function(event) {
            event.preventDefault();
            var defaultLogoPath = "images/staff/user.jpg";
            $('#profile_img_add').attr('src', defaultLogoPath);
            $('#profile_path_add').val('');
        });

        $(document).on('click', '#upload_profile', function(event) {
            event.preventDefault(); 
            $('#profile_path').click();
        });

        $(document).on('change', '#profile_path', function(event) {
            console.log(123);
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    $('#profile_img').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        $(document).on('click', '#reset_profile', function(event) {
            event.preventDefault();
            var defaultLogoPath = "images/staff/user.jpg";
            $('#profile_img').attr('src', defaultLogoPath);
            $('#profile_path').val('');
        });

        $(document).on('click', '.changeStatus', function(event) {
            event.preventDefault(); 
            var staff_id = $(this).data('id');
            var status = $(this).data('status');
            var no = $(this).data('no');
            $.ajax({
                url: 'pages/staff_ajax.php',
                type: 'POST',
                data: {
                    staff_id: staff_id,
                    status: status,
                    action: 'change_status'
                },
                success: function(response) {
                    
                    if (response == 'success') {
                        if (status == 1) {
                            $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                            $(".changeStatus[data-no='" + no + "']").data('status', "0");
                        } else {
                            $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                            $(".changeStatus[data-no='" + no + "']").data('status', "1");
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

        // Show the View Staff modal and log the staff ID
        $(document).on('click', '#view_staff_btn', function(event) {
            event.preventDefault(); 
            
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/staff_ajax.php',
                    type: 'POST',
                    data: {
                        staff_id: id,
                        action: "fetch_modal"
                    },
                    success: function(response) {
                        $('#updateStaffModal').html(response);
                        $('#updateStaffModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
            
        });

        $(document).on('submit', '#update_staff', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/staff_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#updateStaffModal').modal('hide');
                    if (response === "Staff updated successfully.") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            window.location.href = "?page=staff";
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

        $(document).on('submit', '#add_staff', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/staff_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addStaffModal').modal('hide');
                    if (response === "New staff added successfully.") {
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



