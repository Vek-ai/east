<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

$generate_rend_upc = generateRandomUPC();
$picture_path = "images/product/product.jpg";

$permission = $_SESSION['permission'];

$staff_id = intval($_SESSION['userid']);
$profileSql = "SELECT access_profile_id FROM staff WHERE staff_id = $staff_id";
$profileRes = mysqli_query($conn, $profileSql);
$profile_id = 0;
if ($profileRes && mysqli_num_rows($profileRes) > 0) {
    $profile_id = intval(mysqli_fetch_assoc($profileRes)['access_profile_id']);
}
$page_id = getPageIdFromUrl($_GET['page'] ?? '');
$page_title = "Special Trim";
?>
<style>
    .dz-preview {
        position: relative;
    }

    .dz-remove {
        position: absolute;
        top: 10px;
        right: 10px;
        background: red;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        font-weight: bold;
        font-size: 12px;
        z-index: 9999; /* Ensure the remove button is on top of the image */
        cursor: pointer; /* Make sure it looks clickable */
    }

    #productList_filter {
        display: none !important;
    }

    #productTable th:nth-child(1), 
    #productTable td:nth-child(1) {
        width: 25% !important; /* Products */
    }

    .readonly {
        pointer-events: none;
        background-color: #f8f9fa;
        color: #6c757d;
        border: 0;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .readonly select,
    .readonly option {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
    }

    .readonly input {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
    }

    #standing_seam[type="checkbox"],
    #board_batten[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        width: 18px;
        height: 18px;
        border: 2px solid #666;
        border-radius: 50%;
        outline: none;
        cursor: pointer;
        position: relative;
    }

    #standing_seam[type="checkbox"]:checked,
    #board_batten[type="checkbox"]:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    #standing_seam[type="checkbox"]:checked::after,
    #board_batten[type="checkbox"]:checked::after {
        content: "";
        width: 10px;
        height: 10px;
        background: white;
        border-radius: 50%;
        position: absolute;
        top: 3px;
        left: 3px;
    }

    .select2-container--default .select2-results__option[aria-disabled=true] {
        display: none;
    }
</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title  ?></h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page"><?= $page_title  ?></li>
            </ol>
            </nav>
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
            <div class="col d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
                <button type="button" id="downloadClassModalBtn" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-download text-white me-1 fs-5"></i> Download Classifications
                </button>
                <button type="button" id="addProductModalBtn" class="btn btn-primary d-flex align-items-center" data-id="">
                    <i class="ti ti-plus text-white me-1 fs-5"></i> Add <?= $page_title  ?>
                </button>
                <button type="button" id="downloadProductModalBtn" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-download text-white me-1 fs-5"></i> Download <?= $page_title  ?>
                </button>
                <button type="button" id="uploadProductModalBtn" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-upload text-white me-1 fs-5"></i> Upload <?= $page_title  ?>
                </button>
            </div>
        </div>
    </div>
    <?php
    }
    ?>

    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Special Trim
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="product_form" class="form-horizontal">
                    <input type="hidden" id="product_id" name="product_id" class="form-control" />
                    <div class="modal-body">
                        <div class="card">
                            <div id="add-fields" class=""></div>
                        </div>
                    </div>
                </form>
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
                                    <option value="category">Category</option>
                                    <option value="type">Product Type</option> 
                                    <option value="grade">Product Grade</option> 
                                    <option value="profile">Product Profile</option>  
                                    <option value="color">Color</option> 
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

    <div class="modal fade" id="uploadProductModal" tabindex="-1" aria-labelledby="uploadProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Upload Product
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                            <form id="upload_product_form" action="#" method="post" enctype="multipart/form-data">
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
                            <button type="button" id="readUploadProductBtn" class="btn btn-primary fw-semibold">
                                <i class="fas fa-eye me-2"></i> View Uploaded File
                            </button>
                        </div>
                    </div>    
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="readUploadProductModal" tabindex="-1" aria-labelledby="readUploadProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Uploaded Excel Product
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="uploaded_excel" class="modal-body">
                
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateProductModal" tabindex="-1" role="dialog" aria-labelledby="updateProductModal" aria-hidden="true"></div>

    <div class="modal" id="viewDetailsModal">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title"><?= $page_title  ?> Details</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                <div class="card">
                    <div class="card-body mb-0" id="viewDetailsModalBody">
                    
                    </div>
                </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-danger" data-bs-dismiss="modal" type="button">Close</button>
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

    
    <div class="card card-body">
        <div class="row">
            <div class="col-3">
                <h3 class="card-title align-items-center mb-2">
                    Filter <?= $page_title  ?> 
                </h3>
                <div class="position-relative w-100 px-0 mr-0 mb-2">
                    <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search Product">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center">
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control py-0 ps-5 select2 filter-selection" id="select-customer" data-filter="customer" data-filter-name="Customer">
                            <option value="" data-category="">All Customers</option>
                            <optgroup label="Customers">
                                <?php
                                $query_customer = "SELECT customer_id FROM customer WHERE hidden = '0' AND status = '1' ORDER BY `customer_first_name` ASC";
                                $result_customer = mysqli_query($conn, $query_customer);
                                while ($row_customer = mysqli_fetch_array($result_customer)) {
                                    $name = get_customer_name($row_customer['customer_id']);
                                ?>
                                    <option value="<?= $row_customer['customer_id'] ?>"><?= $name ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-color" data-filter="color" data-filter-name="Product Color">
                            <option value="" data-category="">All Colors</option>
                            <optgroup label="Product Colors">
                                <?php
                                $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                $result_color = mysqli_query($conn, $query_color);
                                while ($row_color = mysqli_fetch_array($result_color)) {
                                ?>
                                    <option value="<?= $row_color['color_id'] ?>" data-category="category" <?= $selected ?>><?= $row_color['color_name'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-grade" data-filter="grade" data-filter-name="Product Grade">
                            <option value="" data-category="">All Grades</option>
                            <optgroup label="Product Grades">
                                <?php
                                $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                $result_grade = mysqli_query($conn, $query_grade);
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                ?>
                                    <option value="<?= $row_grade['product_grade_id'] ?>" data-category="grade" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-gauge" data-filter="gauge" data-filter-name="Product Gauge">
                            <option value="" data-category="">All Gauges</option>
                            <optgroup label="Product Gauges">
                                <?php
                                $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY `product_gauge` ASC";
                                $result_gauge = mysqli_query($conn, $query_gauge);
                                while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                ?>
                                    <option value="<?= $row_gauge['product_gauge_id'] ?>" data-category="gauge" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end py-2">
                    <button type="button" class="btn btn-outline-primary reset_filters">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
            <div class="col-9">
                <h3 class="card-title mb-2">
                    <?= $page_title  ?> List 
                </h3>
                <div id="selected-tags" class="mb-2"></div>
                <div class="datatables">
                    <div class="table-responsive">
                        <table id="productList" class="table search-table align-middle text-wrap text-center">
                            <thead class="header-item">
                                <tr>
                                    <th>Customer</th>
                                    <th>Special Trim Description</th>
                                    <th>Special Trim #</th>
                                    <th>Last Order Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    </div>
</div>

<script src="includes/pricing_data.js"></script>

<script>
    $(document).ready(function() {
        var selectedCategory = '';
        document.title = "<?= $page_title ?>";

        var table = $('#productList').DataTable({
            order: [[1, "asc"]],
            pageLength: 100,
            ajax: {
                url: 'pages/customer_special_trim_ajax.php',
                type: 'POST',
                data: { action: 'fetch_products' },
            },
            columns: [
                { data: 'customer' },
                { data: 'description' },
                { data: 'trim_no' },
                { data: 'last_order' },
                { data: 'action_html' }
            ],
            createdRow: function (row, data, dataIndex) {
                $(row).attr('data-customer', data.customer_id);
                $(row).attr('data-color', data.color);
                $(row).attr('data-grade', data.grade);
                $(row).attr('data-gauge', data.gauge);
            },
            "dom": 'lftp'
        });

        $('#productList_filter').hide();

        $(document).on('click', '.remove-image-btn', function(event) {
            event.preventDefault();
            let imageId = $(this).data('image-id');

            if (confirm("Are you sure you want to remove this image?")) {
                $.ajax({
                    url: 'pages/product_ajax.php',
                    type: 'POST',
                    data: { 
                        image_id: imageId,
                        action: "remove_image"
                    },
                    success: function(response) {
                        if(response.trim() == 'success') {
                            $('button[data-image-id="' + imageId + '"]').closest('.col-md-2').remove();
                        } else {
                            alert('Failed to remove image.');
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            }
        });

        $('#toggleActive').trigger('change');

        $(".select2").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            });
        });

        $(document).on('click', '#addProductModalBtn', function(event) {
            event.preventDefault();

            var id = $(this).data('id') || '';

            $.ajax({
                url: 'pages/customer_special_trim_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    action: "fetch_modal"
                },
                success: function (response) {
                    $('#add-fields').html(response);

                    $(".select2").each(function () {
                        $(this).select2({
                            width: '100%',
                            dropdownParent: $(this).parent()
                        });
                    });

                    $('#addProductModal').modal('show');
                }
            });
        });

        $(document).on('click', '#downloadProductModalBtn', function(event) {
            event.preventDefault();
            window.location.href = "pages/customer_special_trim_ajax.php?action=download_excel";
        });

        $(document).on('click', '#downloadClassModalBtn', function(event) {
            $('#downloadClassModal').modal('show');
        });

        $(document).on('click', '#uploadProductModalBtn', function(event) {
            $('#uploadProductModal').modal('show');
        });

        $(document).on('click', '#readUploadProductBtn', function(event) {
            $.ajax({
                url: 'pages/customer_special_trim_ajax.php',
                type: 'POST',
                data: {
                    action: "fetch_uploaded_modal"
                },
                success: function(response) {
                    $('#uploaded_excel').html(response);
                    $('#readUploadProductModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $("#download_class_form").submit(function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            formData.append("action", "download_classifications");

            $.ajax({
                url: "pages/customer_special_trim_ajax.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    window.location.href = "pages/customer_special_trim_ajax.php?action=download_classifications&class=" + encodeURIComponent($("#select-download-class").val());
                },
                error: function (xhr, status, error) {
                    alert("Error downloading file: " + error);
                }
            });
        });

        $('#upload_product_form').on('submit', function (e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            formData.append('action', 'upload_excel');

            $.ajax({
                url: 'pages/customer_special_trim_ajax.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
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

        $(document).on('click', '#saveTable', function(event) {
            if (confirm("Are you sure you want to save this Excel data to the products?")) {
                var formData = new FormData();
                formData.append("action", "save_table");

                $.ajax({
                    url: "pages/customer_special_trim_ajax.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        response = response.trim();
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                        $('#response-modal').modal("show");
                    }
                });
            }
        });

        $(document).on('click', '#view_product_btn', function(event) {
            event.preventDefault(); 
            /* var id = $(this).data('id');
            $.ajax({
                    url: 'pages/customer_special_trim_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_view_modal"
                    },
                    success: function(response) {
                        $('#updateProductModal').html(response);
                        $('#updateProductModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            }); */
        });

        $(document).on('click', '.changeStatus', function(event) {
            event.preventDefault(); 
            var product_id = $(this).data('id');
            var status = $(this).data('status');
            var no = $(this).data('no');
            $.ajax({
                url: 'pages/customer_special_trim_ajax.php',
                type: 'POST',
                data: {
                    product_id: product_id,
                    status: status,
                    action: 'change_status'
                },
                success: function(response) {
                    if (response == 'success') {
                        table.ajax.reload(null, false);
                    } else {
                        alert('Failed to change status.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '.hideProduct', function(event) {
            event.preventDefault();

            var product_id = $(this).data('id');
            var rowId = $(this).data('row');

            if (!confirm("Are you sure you want to archive this product?")) {
                return;
            }

            $.ajax({
                url: 'pages/customer_special_trim_ajax.php',
                type: 'POST',
                data: {
                    product_id: product_id,
                    action: 'hide_product'
                },
                success: function(response) {
                    if (response == 'success') {
                        table.ajax.reload(null, false);
                    } else {
                        alert('Failed to hide product system.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('submit', '#product_form', function(event) {
            event.preventDefault(); 

            $('#color_paint option:disabled').prop('disabled', false);

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/customer_special_trim_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addProductModal').modal('hide');
                    if (response.trim() === "success_update") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Product successfully updated.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        table.ajax.reload(null, false);
                    } else if (response.trim() === "success_add") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New product added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        table.ajax.reload(null, false);
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
        
        $(document).on('mousedown', '.readonly', function() {
            e.preventDefault();
        });

        function filterTable() {
            var textSearch = $('#text-srh').val().toLowerCase();

            $.fn.dataTable.ext.search = [];

            if (textSearch) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
                });
            }

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var row = $(table.row(dataIndex).node());
                var match = true;

                $('.filter-selection').each(function() {
                    const normalize = str => str
                        ?.toString()
                        .trim()
                        .replace(/\s+/g, ' ')
                        .toLowerCase() || '';

                    var filterKey   = $(this).data('filter');
                    var filterValue = $(this).val();

                    var rowNode = row[0];
                    var rawAttr = $(rowNode).data(filterKey);

                    var rowValue = rawAttr;

                    if (typeof rawAttr === 'string' && rawAttr.startsWith('[')) {
                        try {
                            rowValue = JSON.parse(rawAttr);
                        } catch (e) {
                            rowValue = rawAttr;
                        }
                    }

                    if (Array.isArray(rowValue)) {
                        if (filterValue && !rowValue.includes(parseInt(filterValue))) {
                            match = false;
                            return false;
                        }
                    } else {
                        var normalizedFilter = normalize(filterValue);
                        var normalizedRow    = normalize(rowValue);
                        if (normalizedFilter && normalizedFilter !== '/' && !normalizedRow.includes(normalizedFilter)) {
                            match = false;
                            return false;
                        }
                    }
                });

                return match;
            });


            table.draw();
            updateSelectedTags();
        }

        function updateSelectedTags() {
            var displayDiv = $('#selected-tags');
            displayDiv.empty();

            $('.filter-selection').each(function() {
                var selectedOption = $(this).find('option:selected');
                var selectedText = selectedOption.text();
                var filterName = $(this).data('filter-name');

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

        $(document).on('input change', '#text-srh, #toggleActive, #onlyInStock, .filter-selection', filterTable);

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
                action: 'update_product_data',
                id: id,
                header_name: headerName,
                new_value: newValue,
            };

            $.ajax({
                url: 'pages/customer_special_trim_ajax.php',
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

        $(document).on('click', '.reset_filters', function () {
            $('.filter-selection').each(function () {
                $(this).val(null).trigger('change.select2');
            });

            $('#text-srh').val('');

            filterTable();
        });

        function fetchProductABR() {
            let category_ids = toIntArray($('#product_category').val());
            let type_ids     = toIntArray($('#product_type').val());
            let profile_ids  = toIntArray($('#profile').val());
            let grade_ids    = toIntArray($('#grade').val());
            let gauge_ids    = toIntArray($('#gauge').val());
            let color_ids    = toIntArray($('#color_paint').val());
            let length_ids   = toIntArray($('#available_lengths').val());

            $.ajax({
                url: 'pages/customer_special_trim_ajax.php',
                type: 'POST',
                data: {
                    category_ids,
                    type_ids,
                    profile_ids,
                    grade_ids,
                    gauge_ids,
                    color_ids,
                    length_ids,
                    action: 'get_product_abr'
                },
                success: function(response) {
                    let container = $('#product_ids_abbrev');
                    container.empty();

                    if (response.trim() !== '') {
                        let items = response.split(',').map(item => item.trim());

                        let ul = $('<ul></ul>').css({
                            display: 'grid',
                            'grid-template-columns': 'repeat(3, 1fr)',
                            gap: '5px',
                            padding: '0 20px',
                            'list-style-position': 'inside'
                        });

                        items.forEach(function(id) {
                            ul.append('<li>' + id + '</li>');
                        });

                        container.append(ul);
                    } else {
                        container.text('No product IDs generated.');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', error);
                    console.log('Response Text:', xhr.responseText);
                }
            });
        }

        $(document).on('click', '#btn_fetch_prod_id', fetchProductABR);
    });
</script>



