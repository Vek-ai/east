<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

?>
<style>
    .min-height-500px{
        min-height: 45vh;
    }
</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Staff</h4>
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

    <div class="card overflow-hidden chat-application">
    <div class="d-flex align-items-center justify-content-between gap-6 m-3 d-lg-none">
        <button class="btn btn-primary d-flex" type="button" data-bs-toggle="offcanvas" data-bs-target="#chat-sidebar" aria-controls="chat-sidebar">
        <i class="ti ti-menu-2 fs-5"></i>
        </button>
        <form class="position-relative w-100">
        <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Contact">
        <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
        </form>
    </div>
    <div class="d-flex w-100">
        <div class="d-flex w-100">
        <div class="min-width-340">
            <div class="border-end user-chat-box h-100">
            <div class="px-4 pt-9 pb-0 d-none d-lg-block">
                <button id="addStaffModalLabel" class="btn btn-primary fw-semibold py-8 w-100" data-bs-toggle="modal" data-bs-target="#addStaffModal"><i class="ti ti-users text-white me-1 fs-5"></i> Add New Staff</button>
            </div>
                
            <div class="px-4 pt-9 pb-6 d-none d-lg-block">
                <form class="position-relative">
                <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search" />
                <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </form>
            </div>
            <div class="app-chat">
                <ul class="chat-users mh-n100" data-simplebar>
                <?php 
                $query_staff = "SELECT * FROM staff";
                $result_staff = mysqli_query($conn, $query_staff);            
                while ($row_staff = mysqli_fetch_array($result_staff)) {
                    if(!empty($row_staff['profile_path'])){
                        $profile_path = $row_staff['profile_path'];
                    }else{
                        $profile_path = "../assets/images/profile/user-3.jpg";
                    }
                ?>
                <li>
                    <a href="javascript:void(0)" class="px-4 py-3 bg-hover-light-black d-flex align-items-center chat-user bg-light-subtle" id="view_staff_btn" data-id="<?= $row_staff['staff_id'] ?>">
                    <span class="position-relative">
                        <img src="<?= $profile_path ?>" alt="user" width="40" height="40" class="rounded-circle">
                    </span>
                    <div class="ms-6 d-inline-block w-75">
                        <h6 class="mb-1 fw-semibold chat-title" data-username="<?= $row_staff['staff_fname'] ." " .$row_staff['staff_lname'] ?>"><?= $row_staff['staff_fname'] ." " .$row_staff['staff_lname'] ?>
                        </h6>
                        <span class="fs-2 text-body-color d-block"><p class="mb-0"><?= get_role_name($row_staff['role']) ?></p></span>
                    </div>
                    </a>
                </li>
                <?php } ?>
                </ul>
            </div>
            </div>
        </div>
        <div class="w-100 min-height-500px" >
            <div class="chat-container h-100 w-100">
            <div class="chat-box-inner-part h-100">
                <div class="chatting-box app-email-chatting-box">
                <div class="p-9 py-3 border-bottom chat-meta-user d-flex align-items-center justify-content-between">
                    <h5 class="text-dark mb-0 fs-5">Staff Details</h5>
                    <ul class="list-unstyled mb-0 d-flex align-items-center">
                    <li class="d-lg-none d-block">
                        <a class="text-dark back-btn px-2 fs-5 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)">
                        <i class="ti ti-arrow-left"></i>
                        </a>
                    </li>
                    <li class="position-relative" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="important">
                        <a class="text-dark px-2 fs-5 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)">
                        <i class="ti ti-star"></i>
                        </a>
                    </li>
                    <li class="position-relative" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit">
                        <a class="d-block text-dark px-2 fs-5 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#updateStaffModal">
                        <i class="ti ti-pencil"></i>
                        </a>
                    </li>
                    <li class="position-relative" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete">
                        <a class="text-dark px-2 fs-5 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)">
                        <i class="ti ti-trash"></i>
                        </a>
                    </li>
                    </ul>
                </div>
                <div class="position-relative overflow-hidden">
                    <div class="position-relative">
                    <div class="chat-box email-box mh-n100 p-9" data-simplebar="init">

                        <div id="staff_details" class="chat-list chat-active" data-user-id="1">
                            <div class="h-100 w-100 text-center">
                                <h6>To view employee details, please click one of the profiles on the left.</h6>
                            </div>
                        </div>
                        

                    </div>
                    </div>
                </div>
                </div>
            </div>
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

        <div class="modal fade" id="updateStaffModal" tabindex="-1" aria-labelledby="updateStaffModalLabel" aria-hidden="true">

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

    </div>
    </div>
</div>

<script>
    function getUrlParameter(name) {
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);
        return params.get(name);
    }

    $(document).ready(function() {
        if(getUrlParameter('staff_id')){
            var staff_id = getUrlParameter('staff_id');
            $.ajax({
                    url: 'pages/staff_ajax.php',
                    type: 'POST',
                    data: {
                        staff_id: staff_id,
                        action: "fetch_info"
                    },
                    success: function(response) {
                        $('#staff_details').html(response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });

            $.ajax({
                    url: 'pages/staff_ajax.php',
                    type: 'POST',
                    data: {
                        staff_id: staff_id,
                        action: "fetch_modal"
                    },
                    success: function(response) {
                        $('#updateStaffModal').html(response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        }

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
            var defaultProfilePath = "../assets/images/profile/user-1.jpg";
            $('#profile_img_add').attr('src', defaultProfilePath);
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
            var defaultProfilePath = "../assets/images/profile/user-1.jpg";
            $('#profile_img').attr('src', defaultProfilePath);
            $('#profile_path').val('');
        });

        $(document).on('click', '#view_staff_btn', function(event) {
            event.preventDefault(); 
            var staff_id = $(this).data('id');
            $.ajax({
                    url: 'pages/staff_ajax.php',
                    type: 'POST',
                    data: {
                        staff_id: staff_id,
                        action: "fetch_info"
                    },
                    success: function(response) {
                        $('#staff_details').html(response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });

            $.ajax({
                    url: 'pages/staff_ajax.php',
                    type: 'POST',
                    data: {
                        staff_id: staff_id,
                        action: "fetch_modal"
                    },
                    success: function(response) {
                        $('#updateStaffModal').html(response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('submit', '#update_staff', function(event) {
            event.preventDefault(); 

            var staffId = $(this).find('input[name="staff_id"]').val();

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
                    if (response.trim() === "Staff updated successfully.") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            window.location.href = "?page=staff&staff_id=" +staffId;
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
                    if (response.trim() === "New staff added successfully.") {
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