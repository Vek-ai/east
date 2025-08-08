<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$permission = $_SESSION['permission'];

?>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"> Warehouse</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Warehouses</li>
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

    <?php                                                    
    if ($permission === 'edit') {
    ?>
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
            <button type="button" id="addWarehouseModalLabel" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
                <i class="fas fa-warehouse text-white me-1 fs-5"></i> Add Warehouse
            </button>
        </div>
        </div>
    </div>
    <?php
    }
    ?>

    <div class="modal fade" id="addWarehouseModal" tabindex="-1" aria-labelledby="addWarehouseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">Add Warehouse</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add_warehouse" class="form-horizontal">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body">
                                <input type="hidden" id="WarehouseID" name="WarehouseID" class="form-control" />

                                <div class="row pt-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Warehouse Name</label>
                                            <input type="text" id="WarehouseName" name="WarehouseName" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Staff In-charge</label>
                                        <div class="mb-3">
                                            <select id="corresponding_user" class="select2-add form-control" name="corresponding_user">
                                                <option value="" >Select Staff...</option>
                                                <?php
                                                $query_staff = "SELECT * FROM staff WHERE status = '1'";
                                                $result_staff = mysqli_query($conn, $query_staff);            
                                                while ($row_staff = mysqli_fetch_array($result_staff)) {
                                                ?>
                                                    <option value="<?= $row_staff['staff_id'] ?>" ><?= $row_staff['staff_fname'] ." " .$row_staff['staff_lname'] ?></option>
                                                <?php   
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Location</label>
                                            <input type="text" id="Location" name="Location" class="form-control" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4 mb-7">
                                        <label class="form-label">Contact Person</label>
                                        <input type="text" id="contact_person" name="contact_person" class="form-control" />
                                    </div>
                                    <div class="col-4 mb-7">
                                        <label class="form-label">Contact Phone</label>
                                        <input type="text" id="contact_phone" name="contact_phone" class="form-control phone-inputmask" />
                                    </div>
                                    <div class="col-4 mb-9">
                                        <label class="form-label">Contact Email</label>
                                        <input type="text" id="contact_email" name="contact_email" class="form-control" />
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

    <div class="modal fade" id="updateWarehouseModal" tabindex="-1" role="dialog" aria-labelledby="updateWarehouseModal" aria-hidden="true">
        
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
        <table id="warehouseList" class="table search-table align-middle text-nowrap">
            <thead class="header-item">
            <th>Warehouse Name</th>
            <th>Location</th>
            <th>Contact Name</th>
            <th>Contact Phone</th>
            <th>Details</th>
            <th>Action</th>
            </thead>
            <tbody>
            <?php
                $no = 1;
                $query_warehouse = "SELECT * FROM warehouses";
                $result_warehouse = mysqli_query($conn, $query_warehouse);            
                while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                    $WarehouseID = $row_warehouse['WarehouseID'];
                    $db_status = $row_warehouse['status'];

                    if ($row_warehouse['status'] == '0') {
                        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$WarehouseID' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                    } else {
                        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$WarehouseID' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                    }
                ?>
                    <!-- start row -->
                    <tr class="search-items">
                        <td>
                            <?= $row_warehouse['WarehouseName'] ?>
                        </td>
                        <td><?= $row_warehouse['Location'] ?></td>
                        <td><?= $row_warehouse['contact_person'] ?></td>
                        <td><?= $row_warehouse['contact_phone'] ?></td>
                        <td><?= $status ?></td>
                        <td>
                            <?php
                            $action_html = '';
                                if ($permission === 'edit') {
                                ?>
                                <div class="action-btn d-flex justify-content-center gap-2">
                                    <a href="#" id="view_warehouse_btn" class="text-primary edit" data-id="<?= $row_warehouse['WarehouseID'] ?>" title="Archive">
                                        <i class="fa fa-eye fs-6"></i>
                                    </a>
                                    <a href="?page=warehouse_details&warehouse_id=<?= $row_warehouse['WarehouseID'] ?>" title="Edit">
                                        <i class="fa fa-pencil text-warning fs-6"></i>
                                    </a>
                                    <!-- <a href="javascript:void(0)" class="text-dark delete ms-2" data-id="<?= $row_warehouse['WarehouseID'] ?>">
                                        <i class="ti ti-trash fs-5"></i>
                                    </a> -->
                                </div>
                                <?php
                                }
                            ?>
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

        $('#warehouseList').DataTable({
            "order": [[0, "asc"]] // Column index is 0-based, so column 2 is index 1
        });

        $(".select2-add").select2({
            dropdownParent: $('#addWarehouseModal .modal-content'),
            placeholder: "Select One...",
            allowClear: true
        });

        $(document).on('click', '.changeStatus', function(event) {
            event.preventDefault(); 
            var warehouse_id = $(this).data('id');
            var status = $(this).data('status');
            var no = $(this).data('no');
            $.ajax({
                url: 'pages/warehouse_ajax.php',
                type: 'POST',
                data: {
                    warehouse_id: warehouse_id,
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

        // Show the View Warehouse modal and log the warehouse ID
        $(document).on('click', '#view_warehouse_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            console.log(id)
            $.ajax({
                    url: 'pages/warehouse_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_modal"
                    },
                    success: function(response) {
                        
                        $('#updateWarehouseModal').html(response);
                        $('#updateWarehouseModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
            
        });

        $(document).on('submit', '#update_warehouse', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/warehouse_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#updateWarehouseModal').modal('hide');
                    if (response === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Warehouse updated successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            window.location.href = "?page=warehouses";
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

        $(document).on('submit', '#add_warehouse', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/warehouse_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addWarehouseModal').modal('hide');
                    if (response === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New warehouse added successfully.");
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



