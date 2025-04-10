<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$supplier_colors = array();

$page_title = "Supplier";

?>
<style>
    td.last-edit{
        white-space: normal;
        word-wrap: break-word;
    }
    .select2-container .select2-selection {
        height: auto !important;
        padding: 2px !important;
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-selection--multiple {
        min-height: auto;
        line-height: 1.5;
    }

    .select2-results__option {
        padding: 4px 3px !important;
    }

    .select2-selection__choice {
        padding: 2px 8px !important;
        margin: 2px 4px !important;
        line-height: 1.5 !important;
        display: inline-flex;
        align-items: center;
    }

    .select2-selection__choice__display {
        padding: 0px !important;
        margin: 0 !important;
        line-height: inherit !important;
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
            
            </div>
        </div>
        </div>
    </div>
    </div>

    <div class="widget-content searchable-container list">

    <div class="card card-body">
        <div class="row">
        <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
            <button type="button" id="addModalBtn" class="btn btn-primary d-flex align-items-center" data-id="" data-type="add">
                <i class="ti ti-plus text-white me-1 fs-5"></i> Add <?= $page_title ?>
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
            <div class="position-relative w-100 px-1 mr-0 mb-2">
                <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search">
                <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </div>
            <div class="align-items-center">
                <div class="position-relative w-100 px-1 mb-2">
                    <select class="form-control py-0 ps-5 select2 filter-selection" id="filter-category" data-filter="type" data-filter-name="Supplier Type">
                        <option value="">All Supplier Types</option>
                        <optgroup label="Category">
                            <?php
                            $query_roles = "SELECT * FROM supplier_type WHERE hidden = '0' ORDER BY `supplier_type` ASC";
                            $result_roles = mysqli_query($conn, $query_roles);            
                            while ($row_supplier = mysqli_fetch_array($result_roles)) {
                                $selected = ($row_supplier['supplier_type_id'] == $row['supplier_type']) ? 'selected' : '';
                            ?>
                                <option value="<?= $row_supplier['supplier_type_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_type'] ?></option>
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
        </div>
        <div class="col-9">
            <div id="selected-tags" class="mb-2"></div>
            <div class="datatables">
                <h4 class="card-title d-flex justify-content-between align-items-center">Supplier List</h4>
                <div class="table-responsive">
                    <table id="supplierList" class="table search-table align-middle text-nowrap">
                        <thead class="header-item">
                        <th>Supplier Name</th>
                        <th>Supplier Type</th>
                        <th>Contact Name</th>
                        <th>Contact Email</th>
                        <th>Contact Phone</th>
                        <th>Status</th>
                        <th>Action</th>
                        </thead>
                        <tbody>
                        <?php
                            $no = 1;
                            $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
                            $result_supplier = mysqli_query($conn, $query_supplier);            
                            while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                $supplier_id = $row_supplier['supplier_id'];
                                $db_status = $row_supplier['status'];

                                if ($row_supplier['status'] == '0') {
                                    $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$supplier_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                                } else {
                                    $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$supplier_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
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
                                <tr id="product-row-<?= $no ?>"
                                    data-type="<?=$row_supplier['supplier_type']?>"
                                >
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
                                    <td><?= $status ?></td>
                                    <td>
                                        <div class="action-btn d-flex justify-content-center align-items-center gap-2">
                                            <a href="#" id="addModalBtn" class="d-flex align-items-center justify-content-center text-decoration-none" data-toggle="tooltip" data-placement="top" title="View" data-id="<?= $supplier_id ?>" data-type="view">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="#" id="addModalBtn" class="d-flex align-items-center justify-content-center text-decoration-none" data-toggle="tooltip" data-placement="top" title="Edit" data-id="<?= $supplier_id ?>" data-type="edit">
                                                <i class="fa fa-pencil text-warning"></i>
                                            </a>
                                            <a href="?page=supplier_dashboard&id=<?= $supplier_id ?>" class="py-1 pe-1" data-toggle="tooltip" data-placement="top" title="Dashboard">
                                                <i class="fa fa-chart-bar text-info"></i>
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
    </div>
    </div>
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

<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="add-header">
                    Add <?= $page_title ?>
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="supplierForm" class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                        <div id="add-fields" class=""></div>
                        <div class="form-actions toggleElements">
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
                                <option value="supplier_type">Supplier Type</option>
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
                        <select class="form-select select2" id="select-download-category" name="supplier_type">
                            <option value="">All Supplier Types</option>
                            <optgroup label="Suppliers">
                                <?php
                                $query_supplier_type = "SELECT * FROM supplier_type WHERE status = 1 ORDER BY `supplier_type` ASC";
                                $result_supplier_type = mysqli_query($conn, $query_supplier_type);            
                                while ($row_supplier_type = mysqli_fetch_array($result_supplier_type)) {
                                ?>
                                    <option value="<?= $row_supplier_type['supplier_type_id'] ?>"><?= $row_supplier_type['supplier_type'] ?></option>
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
    function formatOption(state) {
        if (!state.id) {
            return state.text;
        }
        var color = $(state.element).data('color');
        var $state = $(
            '<span class="d-flex align-items-center" style="padding: 0; padding-left:10px; margin: 0; line-height: 1;">' +
                '<span class="rounded-circle d-block" ' +
                    'style="background-color:' + color + '; width: 16px; height: 16px; border: 1px solid #ccc; margin-right: 8px;"></span>' +
                '<span>' + state.text + '</span>' +
            '</span>'
        );
        return $state;
    }

    function formatSelected(state) {
        if (!state.id) {
            return state.text;
        }
        var color = $(state.element).data('color');
        var $state = $(
            '<span class="d-flex align-items-center justify-content-center">' +
                '<span class="rounded-circle d-block" ' +
                    'style="background-color:' + color + '; width: 20px; height: 20px; margin-left: 15px; border: 1px solid #ccc;"></span>' +
            '</span>'
        );
        return $state;
    }

    function toggleFormEditable(formId, enable) {
        console.log(`Toggling form '${formId}' to ${enable ? "editable" : "readonly"}`);

        let form = document.getElementById(formId);
        if (!form) {
            console.log(`Form with ID '${formId}' not found.`);
            return;
        }

        let hideBorders = !enable;
        let hideControls = !enable;
        console.log(`hideBorders: ${hideBorders}, hideControls: ${hideControls}`);

        form.querySelectorAll("input, select, textarea").forEach(element => {
            console.log(`Processing element: ${element.tagName}, Name: ${element.name}`);

            if (enable) {
                element.removeAttribute("readonly");
                element.removeAttribute("disabled");
                element.style.border = hideBorders ? "none" : "";
                element.style.backgroundColor = "";
                if (element.tagName === "SELECT") {
                    element.classList.remove("hide-dropdown");
                }
            } else {
                element.setAttribute("readonly", "true");
                element.setAttribute("disabled", "true");
                element.style.border = hideBorders ? "none" : "1px solid #ccc";
                element.style.backgroundColor = "#f8f9fa";
                if (element.tagName === "SELECT") {
                    element.classList.add("hide-dropdown");
                }
            }
        });

        document.querySelectorAll(".toggleElements").forEach(element => {
            console.log(`Toggling visibility for: ${element.className}`);
            element.classList.toggle("d-none", !enable);
        });

        console.log(`Form '${formId}' toggled successfully.`);
    }


    $(document).ready(function() {
        document.title = "<?= $page_title ?>";

        var table = $('#supplierList').DataTable({
            "order": [[0, "asc"]],
            pageLength: 100
        });

        $('#supplierList_filter').hide();

        $(".select2").each(function () {
            $(this).select2({
                width: '100%',
                dropdownParent: $(this).parent()
            });
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

        $('.supplier_color').select2({
            placeholder: 'Select Supplier Color...',
            width: '100%',
            dropdownParent: $('#color_add'),
            templateResult: formatOption,
            templateSelection: formatOption
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
                        $('.supplier_color').select2({
                            placeholder: 'Select Supplier Color...',
                            width: '100%',
                            dropdownParent: $('#color_upd'),
                            templateResult: formatOption,
                            templateSelection: formatOption
                        });
                        $('#updateContactModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $('#updateContactModal').on('hidden.bs.modal', function() {
            $('.supplier_color').select2('destroy');
            $('.supplier_color').select2({
                placeholder: 'Select Supplier Color...',
                width: '100%',
                dropdownParent: $('#color_add'),
                templateResult: formatOption,
                templateSelection: formatOption
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

        $(document).on('submit', '#supplierForm', function(event) {
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
                    $('.modal').modal('hide');
                    if (response === "success_update") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text('Supplier updated successfully.');
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
                            window.location.href = "?page=product_supplier";
                        });
                    }else if(response === "success_add"){
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text('New Supplier added successfully.');
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

        $(document).on('click', '#addModalBtn', function(event) {
            event.preventDefault();
            var id = $(this).data('id') || '';
            var type = $(this).data('type') || '';
            
            $.ajax({
                url: 'pages/supplier_ajax.php',
                type: 'POST',
                data: {
                id : id,
                action: 'fetch_modal_content'
                },
                success: function (response) {
                    $('#add-fields').html(response);
                    if(type == 'edit'){
                        $('#add-header').html('Update <?= $page_title ?>');
                        toggleFormEditable("supplierForm", true);
                    }else if(type == 'view'){ 
                        $('#add-header').html('View <?= $page_title ?>');
                        toggleFormEditable("supplierForm", false);
                    }else{
                        $('#add-header').html('Add <?= $page_title ?>');
                        toggleFormEditable("supplierForm", true);
                    }

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

        $("#download_excel_form").submit(function (e) {
            e.preventDefault();
            window.location.href = "pages/supplier_ajax.php?action=download_excel&category=" + encodeURIComponent($("#select-download-category").val());
        });

        $("#download_class_form").submit(function (e) {
            e.preventDefault();
            window.location.href = "pages/supplier_ajax.php?action=download_classifications&class=" + encodeURIComponent($("#select-download-class").val());
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
                url: 'pages/supplier_ajax.php',
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
                url: 'pages/supplier_ajax.php',
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
                url: 'pages/supplier_ajax.php',
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
                    url: "pages/supplier_ajax.php",
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
                        return false; // Exit loop early if mismatch is found
                    }
                });

                return match;
            });

            table.draw();
            updateSelectedTags();
        }

        $(document).on('change', '.filter-selection', filterTable);

        $(document).on('input', '#text-srh', filterTable);

        $(document).on('change', '#toggleActive', filterTable);

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

    });
</script>



