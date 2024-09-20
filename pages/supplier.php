<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

if(!empty($_REQUEST['category_id'])){
  $category_id = $_REQUEST['category_id'];
  $query = "SELECT * FROM product_category WHERE product_category_id = '$category_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
  }
}

?>
<style>
    td.last-edit{
        white-space: normal;
        word-wrap: break-word;
    }
</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"> Supplier</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Contact</li>
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
            <button type="button" id="addSupplierModalLabel" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                <i class="ti ti-users text-white me-1 fs-5"></i> Add Supplier
            </button>
        </div>
        </div>
    </div>

    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Add Supplier
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add_supplier" class="form-horizontal">
                    <div class="modal-body">
                        <input type="hidden" id="supplier_id" name="supplier_id" class="form-control"  />

                        <div class="row">
                            <div class="card-body p-0">
                                <h4 class="card-title text-center">Logo Picture</h4>
                                <div class="text-center">
                                    <?php 
                                        $logo_path = "images/supplier/logo.jpg";
                                    ?>
                                    <img src="<?= $logo_path ?>" id="logo_img_add" alt="logo-picture" class="img-fluid rounded-circle" width="120" height="120">
                                    <div class="d-flex align-items-center justify-content-center my-4 gap-6">
                                    <button id="upload_logo_add" type="button" class="btn btn-primary">Upload</button>
                                    <button id="reset_logo_add" type="button" class="btn bg-danger-subtle text-danger">Reset</button>
                                    </div>
                                    <input type="file" id="logo_path_add" name="logo_path" class="form-control" style="display: none;"/>
                                </div>
                            </div>
                        </div>
                    
                        <div class="row pt-3">
                        <div class="col-md-12">
                            <div class="mb-3">
                            <label class="form-label">Supplier Name</label>
                            <input type="text" id="supplier_name" name="supplier_name" class="form-control"  />
                            </div>
                        </div>
                        </div>

                        <div class="row pt-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                            <label class="form-label">Supplier Website</label>
                            <input type="text" id="supplier_website" name="supplier_website" class="form-control"  />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                            <label class="form-label">Supplier Type</label>
                            <select id="supplier_type" class="form-control" name="supplier_type">
                                <option value="/" >Select One...</option>
                                <?php
                                $query_roles = "SELECT * FROM supplier_type WHERE hidden = '0'";
                                $result_roles = mysqli_query($conn, $query_roles);            
                                while ($row_staff = mysqli_fetch_array($result_roles)) {
                                ?>
                                    <option value="<?= $row_staff['supplier_type_id'] ?>" ><?= $row_staff['supplier_type'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                            </div>
                        </div>
                        </div>

                        

                        <div class="row pt-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                            <label class="form-label">Contact Name</label>
                            <input type="text" id="contact_name" name="contact_name" class="form-control"  />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                            <label class="form-label">Contact Email</label>
                            <input type="text" id="contact_email" name="contact_email" class="form-control"  />
                            </div>
                        </div>
                        </div>

                        <div class="row pt-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                            <label class="form-label">Contact Phone</label>
                            <input type="phone" id="contact_phone" name="contact_phone" class="form-control phone-inputmask"  />
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                            <label class="form-label">Contact Fax</label>
                            <input type="phone" id="contact_fax" name="contact_fax" class="form-control phone-inputmask"  />
                            </div>
                        </div>
                        </div>


                        <div class="mb-3">
                        <label class="form-label">Secondary Name</label>
                        <input type="text" id="secondary_name" name="secondary_name" class="form-control"  />
                        </div>

                        <div class="row pt-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                            <label class="form-label">Secondary Phone</label>
                            <input type="phone" id="secondary_phone" name="secondary_phone" class="form-control phone-inputmask"  />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                            <label class="form-label">Secondary Email</label>
                            <input type="text" id="secondary_email" name="secondary_email" class="form-control"  />
                            </div>
                        </div>
                        </div>

                        <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" id="address" name="address" class="form-control"  />
                        </div>

                        <div class="row pt-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                            <label class="form-label">Last Ordered Date</label>
                            <input type="date" id="last_ordered_date" name="last_ordered_date" class="form-control"  />
                            </div>
                        </div>
                        </div>

                        <div class="row pt-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                            <label class="form-label">FreightRate</label>
                            <input type="text" id="freight_rate" name="freight_rate" class="form-control"  />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                            <label class="form-label">PaymentTerms</label>
                            <input type="text" id="payment_terms" name="payment_terms" class="form-control"  />
                            </div>
                        </div>
                        </div>

                        <div class="mb-3">
                        <label class="form-label">Comment</label>
                        <textarea class="form-control" id="comment" name="comment" rows="5"></textarea>
                        </div>

                        

                        
                    </div>
                    <div class="modal-footer">
                        <div class="form-actions">
                            <div class="card-body">
                                <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
    <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="viewProductModal" tabindex="-1" role="dialog" aria-labelledby="viewProductModal" aria-hidden="true"></div>

    <div class="modal fade" id="updateContactModal" tabindex="-1" role="dialog" aria-labelledby="updateContactModal" aria-hidden="true"></div>

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
        <table id="supplierList" class="table search-table align-middle text-nowrap">
            <thead class="header-item">
            <th>Supplier Name</th>
            <th>Supplier Type</th>
            <th>Contact Name</th>
            <th>Contact Email</th>
            <th>Contact Phone</th>
            <th>Details</th>
            <th>Status</th>
            <th>Action</th>
            </thead>
            <tbody>
            <?php
                $no = 1;
                $query_supplier = "SELECT * FROM supplier";
                $result_supplier = mysqli_query($conn, $query_supplier);            
                while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                    $product_category_id = $row_supplier['supplier_id'];
                    $db_status = $row_supplier['status'];

                    if ($row_supplier['status'] == '0') {
                        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$product_category_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                    } else {
                        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$product_category_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                    }

                    if(!empty($row_supplier['logo_path'])){
                        $logo_path = $row_supplier['logo_path'];
                    }else{
                        $logo_path = "images/supplier/logo.jpg";
                    }

                    $date = new DateTime($row_supplier['last_edit']);
                    $last_edit = $date->format('m-d-Y');
                    $edited_by = $row_supplier['edited_by'];

                    if($edited_by != "0"){
                        $last_user_name = get_staff_name($edited_by);
                    }
                ?>
                    <!-- start row -->
                    <tr class="search-items">
                        <td>
                        <!-- <a href="?page=supplier_products"> -->
                        <a href="#" id="view_product_btn" data-id="<?= $row_supplier['supplier_id'] ?>">
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= $logo_path ?>" alt="user4" width="60" height="60" class="rounded-circle">
                                <div>
                                    <?= $row_supplier['supplier_name'] ?>
                                </div>
                            </div>
                        </a>
                        </td>
                        <td><?= !empty($row_supplier['supplier_type']) ? getSupplierType($row_supplier['supplier_type']) : '' ?></td>
                        <td><?= $row_supplier['contact_name'] ?></td>
                        <td><?= $row_supplier['contact_email'] ?></td>
                        <td><?= $row_supplier['contact_phone'] ?></td>
                        <td class="last-edit">Last Edited <?= $last_edit ?> by  <?= $last_user_name ?></td>
                        <td><?= $status ?></td>
                        <td>
                            <div class="action-btn">
                                <a href="#" id="view_supplier_btn" class="text-primary edit" data-id="<?= $row_supplier['supplier_id'] ?>">
                                    <i class="ti ti-eye fs-5"></i>
                                </a>
                                <!-- <a href="javascript:void(0)" class="text-dark delete ms-2" data-id="<?= $row_supplier['supplier_id'] ?>">
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

        $('#supplierList').DataTable({
            "order": [[1, "asc"]] // Column index is 0-based, so column 2 is index 1
        });

        $(document).on('click', '#upload_logo_add', function(event) {
            event.preventDefault(); 
            $('#logo_path_add').click();
        });

        $(document).on('change', '#logo_path_add', function(event) {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    $('#logo_img_add').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        $(document).on('click', '#reset_logo_add', function(event) {
            event.preventDefault();
            var defaultLogoPath = "images/supplier/logo.jpg";
            $('#logo_img_add').attr('src', defaultLogoPath);
            $('#logo_path_add').val('');
        });

        $(document).on('click', '#upload_logo', function(event) {
            event.preventDefault(); 
            $('#logo_path').click();
        });

        $(document).on('change', '#logo_path', function(event) {
            console.log(123);
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    $('#logo_img').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        $(document).on('click', '#reset_logo', function(event) {
            event.preventDefault();
            var defaultLogoPath = "images/supplier/logo.jpg";
            $('#logo_img').attr('src', defaultLogoPath);
            $('#logo_path').val('');
        });

        $(document).on('click', '.changeStatus', function(event) {
            event.preventDefault(); 
            var supplier_id = $(this).data('id');
            var status = $(this).data('status');
            var no = $(this).data('no');
            $.ajax({
                url: 'pages/supplier_ajax.php',
                type: 'POST',
                data: {
                    supplier_id: supplier_id,
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

        // Show the View Supplier modal and log the supplier ID
        $(document).on('click', '#view_product_btn', function(event) {
            event.preventDefault(); 
            var supplier_id = $(this).data('id');
            $.ajax({
                    url: 'pages/supplier_ajax.php',
                    type: 'POST',
                    data: {
                        supplier_id: supplier_id,
                        action: "fetch_product"
                    },
                    success: function(response) {
                        $('#viewProductModal').html(response);
                        $('#viewProductModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        // Show the View Supplier modal and log the supplier ID
        $(document).on('click', '#view_supplier_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/supplier_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_modal"
                    },
                    success: function(response) {
                        $('#updateContactModal').html(response);
                        $('#updateContactModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

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

        $(document).on('submit', '#update_supplier', function(event) {
            event.preventDefault(); 

            var userid = getCookie('userid');

            var formData = new FormData(this);
            formData.append('action', 'add_update');
            formData.append('userid', userid);

            $.ajax({
                url: 'pages/supplier_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#updateContactModal').modal('hide');
                    if (response === "Supplier updated successfully.") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            window.location.href = "?page=product_supplier";
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

        $(document).on('submit', '#add_supplier', function(event) {
            event.preventDefault(); 

            var userid = getCookie('userid');

            var formData = new FormData(this);
            formData.append('action', 'add_update');
            formData.append('userid', userid);

            $.ajax({
                url: 'pages/supplier_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addSupplierModal').modal('hide');
                    if (response === "New supplier added successfully.") {
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



