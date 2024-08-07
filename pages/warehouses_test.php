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
        <div>
            <h4 class="font-weight-medium fs-14 mb-0">Warehouse</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Warehouse</li>
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
                <button id="addWarehouseModalLabel" class="btn btn-primary fw-semibold py-8 w-100" data-bs-toggle="modal" data-bs-target="#addWarehouseModal"><i class="ti ti-users text-white me-1 fs-5"></i> Add New Warehouse</button>
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
                $query_warehouse = "SELECT * FROM warehouses";
                $result_warehouse = mysqli_query($conn, $query_warehouse);            
                while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                ?>
                <li>
                    <a href="javascript:void(0)" class="px-4 py-3 bg-hover-light-black d-flex align-items-center chat-user bg-light-subtle" id="view_warehouse_btn" data-id="<?= $row_warehouse['WarehouseID'] ?>">
                    
                    <div class="ms-6 d-inline-block w-75">
                        <h6 class="mb-1 fw-semibold chat-title" data-username="<?= $row_warehouse['WarehouseName'] ?>"><?= $row_warehouse['WarehouseName'] ?>
                        </h6>
                        <span class="fs-2 text-body-color d-block"><?= $row_warehouse['Location'] ?></span>
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
                    <h5 class="text-dark mb-0 fs-5">Warehouse Details</h5>
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
                        <a class="d-block text-dark px-2 fs-5 bg-hover-primary nav-icon-hover position-relative z-index-5" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#updateWarehouseModal">
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

                        <div id="warehouse_details" class="chat-list chat-active" data-user-id="1">
                            <div class="h-100 w-100 text-center">
                                <h6>To view warehouse details, please click one of the profiles on the left.</h6>
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
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Warehouse Name</label>
                                                <input type="text" id="WarehouseName" name="WarehouseName" class="form-control" />
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
                                        <div class="col-12 mb-7">
                                            <label class="form-label">Contact Person</label>
                                            <input type="text" id="contact_person" name="contact_person" class="form-control" />
                                        </div>
                                        <div class="col-6 mb-7">
                                            <label class="form-label">Contact Phone</label>
                                            <input type="text" id="contact_phone" name="contact_phone" class="form-control" />
                                        </div>
                                        <div class="col-6 mb-9">
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

        <div class="modal fade" id="updateWarehouseModal" tabindex="-1" aria-labelledby="updateWarehouseModalLabel" aria-hidden="true"></div>

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
        var warehouse_id = "";

        if(getUrlParameter('warehouse_id')){
            warehouse_id = getUrlParameter('warehouse_id');

            $.ajax({
                    url: 'pages/warehouses_ajax_test.php',
                    type: 'POST',
                    data: {
                        warehouse_id: warehouse_id,
                        action: "fetch_info"
                    },
                    success: function(response) {
                        $('#warehouse_details').html(response);

                        $('#row_wh_bins').DataTable();
                        $('#row_wh_rows').DataTable();
                        $('#row_wh_shelves').DataTable();
                        
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });

            $.ajax({
                    url: 'pages/warehouses_ajax_test.php',
                    type: 'POST',
                    data: {
                        warehouse_id: warehouse_id,
                        action: "fetch_modal"
                    },
                    success: function(response) {
                        $('#updateWarehouseModal').html(response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });

        }

        $(document).on('click', '#view_warehouse_btn', function(event) {
            event.preventDefault(); 
            warehouse_id = $(this).data('id');
            $.ajax({
                    url: 'pages/warehouses_ajax_test.php',
                    type: 'POST',
                    data: {
                        warehouse_id: warehouse_id,
                        action: "fetch_info"
                    },
                    success: function(response) {
                        $('#warehouse_details').html(response);

                        $('#row_wh_bins').DataTable();
                        $('#row_wh_rows').DataTable();
                        $('#row_wh_shelves').DataTable();
                        
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });

            $.ajax({
                    url: 'pages/warehouses_ajax_test.php',
                    type: 'POST',
                    data: {
                        warehouse_id: warehouse_id,
                        action: "fetch_modal"
                    },
                    success: function(response) {
                        $('#updateWarehouseModal').html(response);
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
                url: 'pages/warehouses_ajax_test.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#updateWarehouseModal').modal('hide');
                    console.log(response);
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Warehouse updated successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            window.location.href = "?page=warehouses&warehouse_id=" +warehouse_id;
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
                url: 'pages/warehouses_ajax_test.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addWarehouseModal').modal('hide');
                    if (response.trim() === "success") {
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

        $(document).on('submit', '#add_bin', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update_bin');

            $.ajax({
                url: 'pages/warehouses_ajax_test.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addBinModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New bin added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            window.location.href = "?page=warehouses&warehouse_id=" +warehouse_id;
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

        $(document).on('submit', '#add_row', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update_row');

            $.ajax({
                url: 'pages/warehouses_ajax_test.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addBinModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New row added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            window.location.href = "?page=warehouses&warehouse_id=" +warehouse_id;
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

        $(document).on('submit', '#add_shelf', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update_shelf');

            $.ajax({
                url: 'pages/warehouses_ajax_test.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addBinModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New shelf added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            window.location.href = "?page=warehouses&warehouse_id=" +warehouse_id;
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