<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Staff Members";

if(!empty($_REQUEST['staff_id'])){
    $staff_id = $_REQUEST['staff_id'];
    $query = "SELECT * FROM staff WHERE staff_id = '$staff_id'";
    $result = mysqli_query($conn, $query);            
    while ($row = mysqli_fetch_array($result)) {
    }
}

?>

<style>
.tw {
    color: #ffffff !important;
}
</style>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
        <div class="card-body px-0">
            <div class="d-flex justify-content-between align-items-center">
            <div><br>
                <h4 class="font-weight-medium fs-14 mb-0"> <?= $page_title ?></h4>
                <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                    <a class="text-muted text-decoration-none" href="">Home
                    </a>
                    </li>
                    <li class="breadcrumb-item text-muted" aria-current="page"><?= $page_title ?></li>
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

    <div class="widget-content searchable-container list">
        <div class="card card-body">
            <div class="row">
            <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-end mt-3 mt-md-0 gap-3">
                <button type="button" id="addStaffModalLabel" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                    <i class="ti ti-users text-white me-1 fs-5"></i> Add <?= $page_title ?>
                </button>
                <button type="button" id="downloadClassModalBtn" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-download text-white me-1 fs-5"></i> Download Classifications
                </button>
                <button type="button" id="downloadBtn" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-download text-white me-1 fs-5"></i> Download <?= $page_title ?>
                </button>
                <button type="button" id="uploadBtn" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-upload text-white me-1 fs-5"></i> Upload <?= $page_title ?>
                </button>
            </div>
            </div>
        </div>

        <div class="card card-body">
            <div class="row">
                <div class="col-3">
                    <h3 class="card-title align-items-center mb-2">
                        Filter <?= $page_title ?>
                    </h3>
                    <div class="position-relative w-100 px-0 mr-0 mb-2">
                        <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search">
                        <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                    </div>
                    <div class="align-items-center">
                        <div class="position-relative w-100 px-1 mb-2">
                            <select class="form-control py-0 ps-5 select2 filter-selection" id="filter-role" data-filter="role" data-filter-name="Role">
                                <option value="">All Roles</option>
                                <optgroup label="Roles">
                                    <?php
                                    $query_roles = "SELECT * FROM staff_roles WHERE hidden = '0' AND status = '1' ORDER BY `emp_role` ASC";
                                    $result_roles = mysqli_query($conn, $query_roles);            
                                    while ($row_staff = mysqli_fetch_array($result_roles)) {
                                    ?>
                                        <option value="<?= $row_staff['emp_role_id'] ?>" 
                                                data-role="<?= $row_supplier['emp_role_id'] ?>">
                                                <?= $row_staff['emp_role'] ?>
                                        </option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="position-relative w-100 px-1 mb-2">
                            <select class="form-control py-0 ps-5 select2 filter-selection" id="filter-warehouse" data-filter="warehouse" data-filter-name="Warehouse">
                                <option value="">All Warehouse Assigned</option>
                                <optgroup label="Warehouses">
                                    <?php
                                    $query_warehouses = "SELECT * FROM warehouses WHERE status = '1'";
                                    $result_warehouses = mysqli_query($conn, $query_warehouses);            
                                    while ($row_warehouses = mysqli_fetch_array($result_warehouses)) {
                                    ?>
                                        <option value="<?= $row_warehouses['WarehouseID'] ?>" 
                                                data-role="<?= $row_warehouses['WarehouseID'] ?>">
                                                <?= $row_warehouses['WarehouseName'] ?>
                                        </option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="px-3 mb-2"> 
                        <input type="checkbox" id="toggleActive" checked> Show Active Only
                    </div>
                    <div class="d-flex justify-content-end py-2">
                        <button type="button" class="btn btn-outline-primary reset_filters">
                            <i class="fas fa-sync-alt me-1"></i> Reset Filters
                        </button>
                    </div>
                </div>
                <div class="col-9">
                    <div id="selected-tags" class="mb-2"></div>
                    <div class="datatables">
                    <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?> List</h4>
                        <div class="table-responsive">
                        <table id="staffList" class="table search-table align-middle text-nowrap">
                            <thead class="header-item">
                            <th style="color: #ffffff !important">Staff Name</th>
                            <th style="color: #ffffff !important">Role</th>
                            <th style="color: #ffffff !important">Email</th>
                            <th style="color: #ffffff !important">Phone</th>
                            <th style="color: #ffffff !important">Details</th>
                            <th style="color: #ffffff !important">Action</th>
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

                                    $warehouse_assigned = getStaffAssignedWarehouse($staff_id);
                                    $warehouse_assigned_id = $warehouse_assigned['WarehouseID'] ?? '';
                                ?>
                                    <!-- start row -->
                                    <tr class="search-items"
                                        data-role="<?=$row_staff['role']?>"
                                        data-warehouse="<?=$warehouse_assigned_id?>"
                                    >
                                        <td style="color: #ffffff !important">
                                        <a href="#" id="view_details_btn" data-id="<?= $row_staff['staff_id'] ?>">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= $profile_path ?>" alt="user4" width="60" height="60" class="rounded-circle">
                                                <div>
                                                    <?= $row_staff['staff_fname'] ." " .$row_staff['staff_lname'] ?>
                                                </div>
                                            </div>
                                        </a>
                                        </td>
                                        <td style="color: #ffffff !important"><?= get_role_name($row_staff['role']) ?></td>
                                        <td style="color: #ffffff !important"><?= $row_staff['email'] ?></td>
                                        <td style="color: #ffffff !important"><?= $row_staff['phone'] ?></td>
                                        <td style="color: #ffffff !important"><?= $status ?></td>
                                        <td>
                                            <div class="action-btn text-center">
                                                <a href="#" id="view_staff_btn" class="text-primary edit" data-id="<?= $row_staff['staff_id'] ?>">
                                                    <i class="ti ti-pencil fs-5"></i>
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
        </div>
    </div>
</div>


<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myLargeModalLabel">Add <?= $page_title ?></h4>
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
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label class="form-label">Role</label>
                                            <a href="?page=employee_roles" target="_blank" class="text-decoration-none">Edit</a>
                                        </div>
                                        <select id="role" class="form-control" name="role">
                                            <option value="" >Select One...</option>
                                            <?php
                                            $query_roles = "SELECT * FROM staff_roles WHERE hidden = '0' AND status = '1' ORDER BY `emp_role` ASC";
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
                        
                            <div class="col-12 mb-9">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label">Warehouses</label>
                                    <a href="?page=warehouses" target="_blank" class="text-decoration-none">Edit</a>
                                </div>
                                <select id="warehouse" class="form-control" name="warehouse">
                                    <option value="" >Select One...</option>
                                    <?php
                                    $query_warehouses = "SELECT * FROM warehouses WHERE status = '1'";
                                    $result_warehouses = mysqli_query($conn, $query_warehouses);            
                                    while ($row_warehouses = mysqli_fetch_array($result_warehouses)) {
                                    ?>
                                        <option value="<?= $row_warehouses['WarehouseID'] ?>" ><?= $row_warehouses['WarehouseName'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </select>
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

<div class="modal fade" id="viewStaffModal" tabindex="-1" role="dialog" aria-labelledby="viewStaffModal" aria-hidden="true"></div>

<div class="modal fade" id="updateStaffModal" tabindex="-1" role="dialog" aria-labelledby="updateStaffModal" aria-hidden="true"></div>

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

<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myLargeModalLabel">
                    Add Product
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="staff_form" class="form-horizontal">
                <input type="hidden" id="staff_id" name="staff_id" class="form-control" />
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">

                            <div id="staff-fields" class=""></div>
                            
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Upload <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="card">
                  <div class="card-body">
                      <form id="upload_excel_form" action="#" method="post" enctype="multipart/form-data">
                          <div class="mb-3">
                              <label for="excel_file" class="form-label fw-semibold">Select Excel File</label>
                              <input type="file" class="form-control" name="excel_file" accept=".xls,.xlsx" required>
                          </div>
                          <div class="text-center">
                              <button type="submit" class="btn btn-primary">Upload & Read</button>
                          </div>
                      </form>
                  </div>
              </div>
              <div class="card mb-0 mt-2">
                  <div class="card-body d-flex justify-content-center align-items-center">
                      <button type="button" id="readUploadBtn" class="btn btn-primary fw-semibold">
                          <i class="fas fa-eye me-2"></i> View Uploaded File
                      </button>
                  </div>
              </div>    
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="readUploadModal" tabindex="-1" aria-labelledby="readUploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Uploaded Excel <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div id="uploaded_excel" class="modal-body"></div>
      </div>
  </div>
</div>

<div class="modal fade" id="downloadClassModal" tabindex="-1" aria-labelledby="downloadClassModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Download Classification
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form id="download_class_form" class="form-horizontal">
                  <label for="select-category" class="form-label fw-semibold">Select Classification</label>
                  <div class="mb-3">
                      <select class="form-select select2" id="select-download-class" name="category">
                          <option value="">All Classifications</option>
                          <optgroup label="Classifications">
                              <option value="role">Roles</option>
                          </optgroup>
                      </select>
                  </div>

                  <div class="d-grid">
                      <button type="submit" class="btn btn-primary fw-semibold">
                          <i class="fas fa-download me-2"></i> Download Classification
                      </button>
                  </div>
              </form>
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Download <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form id="download_excel_form" class="form-horizontal">
                  <label for="select-category" class="form-label fw-semibold">Select Supplier</label>
                  <div class="mb-3">
                      <select class="form-select select2" id="select-download-category" name="role">
                          <option value="">All Roles</option>
                          <optgroup label="Roles">
                                <?php
                                $query_roles = "SELECT * FROM staff_roles WHERE hidden = '0' AND status = '1' ORDER BY `emp_role` ASC";
                                $result_roles = mysqli_query($conn, $query_roles);            
                                while ($row_staff = mysqli_fetch_array($result_roles)) {
                                ?>
                                    <option value="<?= $row_staff['emp_role_id'] ?>" ><?= $row_staff['emp_role'] ?></option>
                                <?php   
                                }
                                ?>
                          </optgroup>
                      </select>
                  </div>

                  <div class="d-grid">
                      <button type="submit" class="btn btn-primary fw-semibold">
                          <i class="fas fa-download me-2"></i> Download Excel
                      </button>
                  </div>
              </form>
          </div>
      </div>
  </div>
</div>

<script>
$(document).ready(function() {
    document.title = "<?= $page_title ?>";

    var table = $('#staffList').DataTable({
        "order": [[0, "asc"]],
        pageLength: 100
    });

    $('#staffList_filter').hide();

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            dropdownParent: $(this).parent()
        });
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
    $(document).on('click', '#view_details_btn', function(event) {
        event.preventDefault(); 
        
        var id = $(this).data('id');
        $.ajax({
                url: 'pages/staff_ajax.php',
                type: 'POST',
                data: {
                    staff_id: id,
                    action: "fetch_modal_view"
                },
                success: function(response) {
                    $('#viewStaffModal').html(response);
                    $('#viewStaffModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
        });
        
    });

    // Show the Edit Staff modal and log the staff ID
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
                $('.modal').modal('hide');
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

    $("#download_excel_form").submit(function (e) {
        e.preventDefault();
        window.location.href = "pages/staff_ajax.php?action=download_excel&role=" + encodeURIComponent($("#select-download-category").val());
    });

    $("#download_class_form").submit(function (e) {
        e.preventDefault();
        window.location.href = "pages/staff_ajax.php?action=download_classifications&class=" + encodeURIComponent($("#select-download-class").val());
    });

    $(document).on('click', '#uploadBtn', function(event) {
        $('#uploadModal').modal('show');
    });

    $(document).on('click', '#downloadClassModalBtn', function(event) {
        $('#downloadClassModal').modal('show');
    });

    $(document).on('click', '#downloadBtn', function(event) {
        $('#downloadModal').modal('show');
    });

    $(document).on('click', '#readUploadBtn', function(event) {
        $.ajax({
            url: 'pages/staff_ajax.php',
            type: 'POST',
            data: {
                action: "fetch_uploaded_modal"
            },
            success: function(response) {
                $('#uploaded_excel').html(response);
                $('#readUploadModal').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $('#upload_excel_form').on('submit', function (e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'upload_excel');

        $.ajax({
            url: 'pages/staff_ajax.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $('.modal').modal('hide');
                response = response.trim();
                if (response.trim() === "success") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text("Data Uploaded successfully.");
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
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.error('Response:', jqXHR.responseText);

                $('#responseHeader').text("Error");
                $('#responseMsg').text("An error occurred while processing your request.");
                $('#responseHeaderContainer').removeClass("bg-success").addClass("bg-danger");
                $('#response-modal').modal("show");
            }
        });
    });

    $(document).on('blur', '.table_data', function() {
        let newValue;
        let updatedData = {};
        
        if ($(this)[0].tagName.toLowerCase() === 'select') {
            const selectedValue = $(this).val();
            const selectedText = $(this).find('option:selected').text();
            newValue = selectedValue ? selectedValue : selectedText;
        } 
        else if ($(this).is('td')) {
            newValue = $(this).text();
        }
        
        const headerName = $(this).data('header-name');
        const id = $(this).data('id');

        updatedData = {
            action: 'update_test_data',
            id: id,
            header_name: headerName,
            new_value: newValue,
        };

        $.ajax({
            url: 'pages/staff_ajax.php',
            type: 'POST',
            data: updatedData,
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, status, error) {
                console.log('Error: ' + error);
                alert('Error updating data');
            }
        });
    });

    $(document).on('click', '#saveTable', function(event) {
        if (confirm("Are you sure you want to save this Excel data to the product lines data?")) {
            var formData = new FormData();
            formData.append("action", "save_table");

            $.ajax({
                url: "pages/staff_ajax.php",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('.modal').modal('hide');
                    response = response.trim();
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text(response);
                    $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                    $('#response-modal').modal("show");
                }
            });
        }
    });

    $(document).on('click', '#addModalBtn', function(event) {
        event.preventDefault();
        var id = $(this).data('id') || '';
        var type = $(this).data('type') || '';

        if(type == 'edit'){
          $('#add-header').html('Update <?= $page_title ?>');
        }else{
          $('#add-header').html('Add <?= $page_title ?>');
        }

        $.ajax({
            url: 'pages/staff_ajax.php',
            type: 'POST',
            data: {
              id : id,
              action: 'fetch_modal_content'
            },
            success: function (response) {
                $('#add-fields').html(response);

                $(".select2-form").each(function () {
                    $(this).select2({
                        width: '100%',
                        dropdownParent: $(this).parent(),
                        templateResult: formatOption,
                        templateSelection: formatOption
                    });
                });

                $('#addModal').modal('show');
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
    });

    function filterTable() {
        var textSearch = $('#text-srh').val().toLowerCase();
        var isActive = $('#toggleActive').is(':checked');

        $.fn.dataTable.ext.search = [];

        if (textSearch) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
            });
        }

        if (isActive) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                return $(table.row(dataIndex).node()).find('a .alert').text().trim() === 'Active';
            });
        }

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var row = $(table.row(dataIndex).node());
            var match = true;

            $('.filter-selection').each(function() {
                var filterValue = $(this).val()?.toString() || '';
                var rowValue = row.data($(this).data('filter'))?.toString() || '';

                if (filterValue && filterValue !== '/' && rowValue !== filterValue) {
                    match = false;
                    return false;
                }
            });

            return match;
        });

        table.draw();
        updateSelectedTags();
    }

    function updateSearchCategory() {
        let selectedCategory = $('#select-category option:selected').data('category');
        let hasCategory = !!selectedCategory;

        $('.search-category').each(function () {
            let $select2Element = $(this);

            if (!$select2Element.data('all-options')) {
                $select2Element.data('all-options', $select2Element.find('option').clone(true));
            }

            let allOptions = $select2Element.data('all-options');

            $select2Element.empty();

            if (hasCategory) {
                allOptions.each(function () {
                    let optionCategory = $(this).data('category');
                    if (String(optionCategory) === String(selectedCategory)) {
                        $select2Element.append($(this).clone(true));
                    }
                });
            } else {
                allOptions.each(function () {
                    $select2Element.append($(this).clone(true));
                });
            }

            $select2Element.select2('destroy');

            let parentContainer = $select2Element.parent();
            $select2Element.select2({
                width: '100%',
                dropdownParent: parentContainer
            });
        });

        $('.category_selection').toggleClass('d-none', !hasCategory);
    }

    function updateSelectedTags() {
        var displayDiv = $('#selected-tags');
        displayDiv.empty();

        $('.filter-selection').each(function() {
            var selectedOption = $(this).find('option:selected');
            var selectedText = selectedOption.text().trim();
            var filterName = $(this).data('filter-name'); // Custom attribute for display

            if ($(this).val()) {
                displayDiv.append(`
                    <div class="d-inline-block p-1 m-1 border rounded bg-light">
                        <span class="text-dark">${filterName}: ${selectedText}</span>
                        <button type="button" 
                            class="btn-close btn-sm ms-1 remove-tag" 
                            style="width: 0.75rem; height: 0.75rem;" 
                            aria-label="Close" 
                            data-select="#${$(this).attr('id')}">
                        </button>
                    </div>
                `);
            }
        });

        $('.remove-tag').on('click', function() {
            $($(this).data('select')).val('').trigger('change');
            $(this).parent().remove();
        });
    }

    $(document).on('input change', '#text-srh, #toggleActive, .filter-selection', filterTable);
    
    filterTable();

    $(document).on('click', '.reset_filters', function () {
        $('.filter-selection').each(function () {
            $(this).val(null).trigger('change.select2');
        });

        $('#text-srh').val('');

        filterTable();
    });

    });
</script>



