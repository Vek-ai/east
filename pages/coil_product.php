<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Coils";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

$generate_rend_upc = generateRandomUPC();
$picture_path = "images/coils/product.jpg";

$color_id = isset($_REQUEST['color_id']) ? $_REQUEST['color_id'] : '';
$grade_id = isset($_REQUEST['grade_id']) ? $_REQUEST['grade_id'] : '';
$gauge_id = isset($_REQUEST['gauge_id']) ? $_REQUEST['gauge_id'] : '';
$category_id = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : '';
$profile_id = isset($_REQUEST['profile_id']) ? $_REQUEST['profile_id'] : '';
$type_id = isset($_REQUEST['type_id']) ? $_REQUEST['type_id'] : '';
$onlyInStock = isset($_REQUEST['onlyInStock']) ? filter_var($_REQUEST['onlyInStock'], FILTER_VALIDATE_BOOLEAN) : false;

$permission = $_SESSION['permission'];
?>
<style>
    @media (max-width: 992px) {
        .d-flex {
            flex-direction: column;
        }
        .flex-shrink-0 {
            width: 100% !important;
            margin-bottom: 1rem;
        }
    }

    .flex-grow-1 {
        min-width: 0;
        overflow-x: auto;
        overflow-y: hidden;
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
        </div>
    </div>
    </div>

    <div class="widget-content searchable-container list">

    <?php                                                    
    if ($permission === 'edit') {
    ?>
    <div class="card card-body">
        <div class="row">
            <div class="col-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0">
                <button type="button" class="btn btn-primary d-flex align-items-center coil_btn" data-id="" data-type="add">
                    <i class="ti ti-users text-white me-1 fs-5"></i> Add <?= $page_title ?>
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
    <?php
    }
    ?>

    <div class="modal fade" id="addCoilModal" tabindex="-1" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title">
                        Add <?= $page_title ?>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="coil_form" class="form-horizontal" autocomplete="off">
                    <div class="modal-body" id="addCoilBody">
                        
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
                        Uploaded <?= $page_title ?>
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="uploaded_excel" class="modal-body"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateProductModal" tabindex="-1" role="dialog" aria-labelledby="updateProductModal" aria-hidden="true"></div>

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
        <div class="d-flex">
            <div class="flex-shrink-0" style="width: 250px;">
                <h3 class="card-title align-items-center mb-2">
                    Filter <?= $page_title ?>
                </h3>
                <div class="position-relative w-100 px-1 mr-0 mb-2">
                    <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center">
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-color" data-filter="color" data-filter-name="Color">
                            <option value="" data-category="">All Colors</option>
                            <optgroup label="Coil Colors">
                                <?php
                                $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                $result_color = mysqli_query($conn, $query_color);
                                while ($row_color = mysqli_fetch_array($result_color)) {
                                    $selected = ($color_id == $row_color['color_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_color['color_id'] ?>" data-category="category" <?= $selected ?>><?= $row_color['color_name'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-grade" data-filter="grade" data-filter-name="Grade">
                            <option value="" data-category="">All Grades</option>
                            <optgroup label="Coil Grades">
                                <?php
                                $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                $result_grade = mysqli_query($conn, $query_grade);
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                    $selected = ($grade_id == $row_grade['product_grade_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_grade['product_grade_id'] ?>" data-category="grade" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-supplier" data-filter="supplier" data-filter-name="Supplier">
                            <option value="">All Suppliers</option>
                            <optgroup label="Suppliers">
                                <?php
                                $query_grade = "SELECT * FROM supplier WHERE status = '1' ORDER BY `supplier_name` ASC";
                                $result_grade = mysqli_query($conn, $query_grade);
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                ?>
                                    <option value="<?= $row_grade['supplier_id'] ?>"><?= $row_grade['supplier_name'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="px-3 mb-2"> 
                    <input type="checkbox" id="toggleDefective"> Show Defective
                </div>
                <div class="px-3 mb-2"> 
                    <input type="checkbox" id="toggleClaim"> Show Submitted for Claim
                </div>
                <div class="d-flex justify-content-end py-2">
                    <button type="button" class="btn btn-outline-primary reset_filters">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
            <div class="flex-grow-1 ms-3">
                <div id="selected-tags" class="mb-2"></div>
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?> List</h4>
                <div class="datatables">
                    <div class="table-responsive">
                        <table id="productList" class="table search-table align-middle text-wrap">
                            <thead class="header-item">
                            <th>Coil #</th>
                            <th>Color Sold As</th>
                            <th>Grade</th>
                            <th>Supplier</th>
                            <th>Remaining Ft</th>
                            <th>Notes</th>
                            <th>Last Edit By</th>
                            <th>Last Time Edited</th>
                            <th>Status</th>
                            <th>Action</th>
                            </thead>
                            <tbody>
                            <?php
                                $no = 1;
                                $query_coil = "
                                    SELECT 
                                        *
                                    FROM 
                                        coil_product
                                    WHERE 
                                        hidden = '0'
                                ";
                                $result_coil = mysqli_query($conn, $query_coil);            
                                while ($row_coil = mysqli_fetch_array($result_coil)) {
                                    $color = $row_coil['color_sold_as'];
                                    $grade = $row_coil['grade'];
                                    $supplier = $row_coil['supplier'];
                                    $remaining_feet = $row_coil['remaining_feet'] ?? 0;
                                    $notes = isset($row_coil['notes']) ? substr($row_coil['notes'], 0, 30) : '';
                                    $coil_id = $row_coil['coil_id'];
                                    $db_status = $row_coil['status'];
                                    
                                    $instock = $remaining_feet > 0 ? '1' : '0';

                                    switch ($db_status) {
                                        case '0': // Available
                                            $status_icon = "text-success ti ti-check-circle";
                                            $status = "<a href='#'>
                                                <div id='status-alert$no' style='background-color: #28a745; color: white; text-align: center; padding: 4px 8px; border-radius: 5%; font-weight: bold;'>
                                                    Available
                                                </div>
                                            </a>";
                                            break;

                                        case '1': // Used
                                            $status_icon = "text-primary ti ti-package";
                                            $status = "<a href='#'>
                                                <div id='status-alert$no' style='background-color: #007bff; color: white; text-align: center; padding: 4px 8px; border-radius: 5%; font-weight: bold;'>
                                                    Used
                                                </div>
                                            </a>";
                                            break;

                                        case '2': // Rework
                                            $status_icon = "text-danger ti ti-alert-circle";
                                            $status = "<a href='#'>
                                                <div id='status-alert$no' style='background-color: #c6223aff; color: #ffffffff; text-align: center; padding: 4px 8px; border-radius: 5%; font-weight: bold;'>
                                                    Rework
                                                </div>
                                            </a>";
                                            break;

                                        case '3': // Defective
                                            $status_icon = "text-warning ti ti-alert-circle";
                                            $status = "<a href='#'>
                                                <div id='status-alert$no' style='background-color: #ffc107; color: #212529; text-align: center; padding: 4px 8px; border-radius: 5%; font-weight: bold;'>
                                                    Defective
                                                </div>
                                            </a>";
                                            break;

                                        case '4': // Archived
                                            $status_icon = "text-muted ti ti-archive";
                                            $status = "<a href='#'>
                                                <div id='status-alert$no' style='background-color: #6c757d; color: white; text-align: center; padding: 4px 8px; border-radius: 5%; font-weight: bold;'>
                                                    Archived
                                                </div>
                                            </a>";
                                            break;
                                        
                                        case '5': // Submit Claim
                                            $status_icon = "text-dark ti ti-file";
                                            $status = "<a href='#'>
                                                <div id='status-alert$no' style='background-color: #ffffffff; color: black; text-align: center; padding: 4px 8px; border-radius: 5%; font-weight: bold;'>
                                                    Submitted for Claim
                                                </div>
                                            </a>";
                                            break;

                                        default: // Unknown
                                            $status_icon = "text-dark ti ti-question-mark";
                                            $status = "<a href='#'>
                                                <div id='status-alert$no' style='background-color: #343a40; color: white; text-align: center; padding: 4px 8px; border-radius: 5%; font-weight: bold;'>
                                                    Unknown
                                                </div>
                                            </a>";
                                            break;
                                    }

                                    if(!empty($row_coil['main_image'])){
                                        $picture_path = $row_coil['main_image'];
                                    }else{
                                        $picture_path = "images/coils/product.jpg";
                                    }

                                    $color_details = getColorDetails($row_coil['color_sold_as']);

                                    $last_edit = '';
                                    if (!empty($row_coil['last_edit'])) {
                                        $date = new DateTime($row_coil['last_edit']);
                                        $last_edit = $date->format('m-d-Y');
                                    }
                            
                                    $user_id = $row_coil['edited_by'] != 0 ? $row_coil['edited_by'] : $row_coil['added_by'];
                                    $last_edit_by = $user_id ? get_name($user_id) : '';
                
                                ?>
                                    <tr class="search-items"
                                        data-no="<?= $no ?>"
                                        data-color="<?= $color ?>"
                                        data-grade="<?= $grade ?>"
                                        data-instock="<?= $instock ?>"
                                        data-supplier="<?= $supplier ?>"
                                        data-status="<?= $db_status ?>"
                                    >
                                        <td>
                                            <a href="?page=coil_product_ledger&coil=<?= $row_coil['coil_id'] ?>" target="_blank" class="coil_history_btn" data-id="<?= $row_coil['coil_id'] ?>">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= $picture_path ?>" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                                    <div class="ms-3">
                                                        <h6 class="fw-semibold mb-0 fs-4"><?= $row_coil['entry_no'] ?></h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="d-inline-flex align-items-center gap-2">
                                                <a href="javascript:void(0)" id="viewAvailableBtn" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                                                    <span class="rounded-circle d-block" style="background-color:<?= $color_details['color_code'] ?>; width: 30px; height: 30px;"></span>
                                                    <?= $color_details['color_name'] ?>
                                                </a>
                                            </div>
                                        </td>
                                        <td><?= getGradeName($row_coil['grade']) ?></td>
                                        <td><?= getSupplierName($row_coil['supplier']) ?></td>
                                        <td><?= number_format(floatval($remaining_feet),2) ?></td>
                                        <td><?= $notes ?></td>
                                        <td><?= $last_edit_by ?></td>
                                        <td><?= $last_edit ?></td>
                                        <td><?= $status ?></td>
                                        <td>
                                            <div class="action-btn text-center">
                                                <a href="#" title="View" class="coil_btn" data-id="<?= $row_coil['coil_id'] ?>" data-type="view">
                                                    <i class="ti ti-eye fs-7"></i>
                                                </a>
                                                <?php                                                    
                                                if ($permission === 'edit') {
                                                ?>
                                                <a href="?page=coil_product_ledger&coil=<?= $row_coil['coil_id'] ?>" target="_blank" title="Coil Product Ledger" class="edit coil_history_btn" data-id="<?= $row_coil['coil_id'] ?>">
                                                    <i class="ti ti-history text-info fs-7"></i>
                                                </a>
                                                <a href="#" title="Edit" class="edit coil_btn" data-id="<?= $row_coil['coil_id'] ?>" data-type="add">
                                                    <i class="ti ti-pencil text-warning fs-7"></i>
                                                </a>
                                                <a href="#" title="Remove From Stock" id="delete_product_btn" class="text-danger edit changeStatus" data-no="<?= $no ?>" data-id="<?= $coil_id ?>" data-status='<?= $db_status ?>'>
                                                    <i class="text-danger ti ti-trash fs-7"></i>
                                                </a>
                                                <?php
                                                }
                                                ?>

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

<script>
    function toggleFormEditable(formId, enable = true, hideBorders = false) {
        const $form = $("#" + formId);
        if ($form.length === 0) return;

        $form.find("input, select, textarea").each(function () {
            const $element = $(this);
            if (enable) {
                $element.removeAttr("readonly").removeAttr("disabled");
                $element.css("border", hideBorders ? "none" : "");
                $element.css("background-color", "");
                if ($element.is("select")) {
                    $element.removeClass("hide-dropdown");
                }
            } else {
                $element.attr("readonly", true).attr("disabled", true);
                $element.css("border", hideBorders ? "none" : "1px solid #ccc");
                $element.css("background-color", "#f8f9fa");
                if ($element.is("select")) {
                    $element.addClass("hide-dropdown");
                }
            }
        });

        $form.find("button[type='submit'], input[type='submit']").toggle(enable);

        $(".toggleElements").toggleClass("d-none", !enable);
    }

    $(document).ready(function() {
        let uploadedFiles = [];

        $('.dropzone').dropzone({
            addRemoveLinks: true,
            dictRemoveFile: "X",
            maxFiles: 1,
            init: function() {
                this.on("addedfile", function(file) {
                    if (this.files.length > 1) {
                        this.removeFile(this.files[0]);
                    }
                    uploadedFiles = [file];
                    updateFileInput();
                });

                this.on("removedfile", function(file) {
                    uploadedFiles = uploadedFiles.filter(f => f.name !== file.name);
                    updateFileInput();
                });
            }
        });

        function updateFileInput() {
            const fileInput = document.getElementById('picture_path_add');
            const dataTransfer = new DataTransfer();

            uploadedFiles.forEach(file => {
                const fileBlob = new Blob([file], { type: file.type });
                dataTransfer.items.add(new File([fileBlob], file.name, { type: file.type }));
            });

            fileInput.files = dataTransfer.files;
        }

        $(document).on("change", ".width-select", function () {
            var width_id = $(this).val();
            var type = $(this).data("type");
            if (width_id) {
                $.ajax({
                    url: "pages/coil_product_ajax.php",
                    type: "POST",
                    data: { 
                        width_id: width_id,
                        action: 'fetch_width_details'
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            $("#coil_class_" + type).val(response.classification);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("XHR Response:", xhr.responseText);
                        console.error("Status:", status);
                        console.error("Error:", error);
                        alert("Error fetching color details.");
                    }
                });
            }
        });

        $(document).on('click', '.remove-image-btn', function(event) {
            event.preventDefault();
            let imageId = $(this).data('image-id');

            if (confirm("Are you sure you want to remove this image?")) {
                $.ajax({
                    url: 'pages/coil_product_ajax.php',
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

        var table = $('#productList').DataTable({
            responsive: true,
            autoWidth: false,
            order: [[1, "asc"]],
            pageLength: 100,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            dom: 'lftp',
        });

        $('.dataTables_scrollBody').css('overflow-y', 'hidden');

        setTimeout(() => table.columns.adjust().responsive.recalc(), 500);

        $('#productList_filter').hide();

        $(window).on('resize', function() {
            table.columns.adjust().responsive.recalc();
        });

        $(".select2-add").each(function () {
            $(this).select2({
                width: '100%',
                placeholder: "Select One...",
                allowClear: true,
                dropdownParent: $(this).parent()
            });
        });

        $(document).on('click', '.changeStatus', function(event) {
            var confirmed = confirm("Are you sure you want to Archive this Coil?");
            
            if (confirmed) {
                var coil_id = $(this).data('id');
                var status = $(this).data('status');
                var no = $(this).data('no');

                $.ajax({
                    url: 'pages/coil_product_ajax.php',
                    type: 'POST',
                    data: {
                        coil_id: coil_id,
                        action: 'change_status'
                    },
                    success: function(response) {
                        if (response == 'success') {
                            table.row($('tr[data-no="' + no + '"]')).remove().draw();
                        } else {
                            alert('Failed to archive.');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            }
        });

        function getUploadedFiles() {
            const dz = Dropzone.forElement('#myUpdateDropzone');
            return dz.getAcceptedFiles();
        }

        $(document).on('click', '.coil_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            var type = $(this).data('type') || '';

            if(type == 'edit'){
                $('.modal-title').html('Update <?= $page_title ?>');
            }else if(type == 'view'){
                $('.modal-title').html('View <?= $page_title ?>');
            }else{
                $('.modal-title').html('Add <?= $page_title ?>');
            }
            $.ajax({
                    url: 'pages/coil_product_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_modal"
                    },
                    success: function(response) {
                        $('#addCoilBody').html(response);
                        if(type == 'view'){
                            toggleFormEditable("coil_form", false, true);
                        }else{
                            toggleFormEditable("coil_form", true, false);
                        }
                        $('#addCoilModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('submit', '#coil_form', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            getUploadedFiles().forEach(file => {
                formData.append('picture_path[]', file);
            });

            $.ajax({
                url: 'pages/coil_product_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('.modal').modal('hide');
                    if (response.trim() === "success_add") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New coil added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }else if (response.trim() === "success_update") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Coil updated successfully.");
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

        $(".select2").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            });
        });

        $(document).on('click', '#downloadBtn', function(event) {
            window.location.href = "pages/coil_product_ajax.php?action=download_excel";
        });

        $(document).on('click', '#uploadBtn', function(event) {
            $('#uploadModal').modal('show');
        });

        $(document).on('click', '#readUploadBtn', function(event) {
            $.ajax({
                url: 'pages/coil_product_ajax.php',
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
                url: 'pages/coil_product_ajax.php',
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
                url: 'pages/coil_product_ajax.php',
                type: 'POST',
                data: updatedData,
                success: function(response) {

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
                    url: "pages/coil_product_ajax.php",
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
            var showDefective = $('#toggleDefective').is(':checked');
            var showClaim = $('#toggleClaim').is(':checked');

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
                    var filterValue = $(this).val()?.toString() || '';
                    var rowValue = row.data($(this).data('filter'))?.toString() || '';

                    if (filterValue && filterValue !== '/' && rowValue !== filterValue) {
                        match = false;
                        return false;
                    }
                });

                if (match) {
                    var status = row.data('status')?.toString() || '';

                    if (showDefective && status === '3') return true;
                    if (showClaim && status === '5') return true;
                    if (!showDefective && status === '3') return false;
                    if (!showClaim && status === '5') return false;
                }

                return match;
            });

            table.draw();
            updateSelectedTags();
        }

        $('#toggleDefective, #toggleClaim').on('change', filterTable);


        function updateSelectedTags() {
            var displayDiv = $('#selected-tags');
            displayDiv.empty();

            $('.filter-selection').each(function() {
                var selectedOption = $(this).find('option:selected');
                var selectedText = selectedOption.text().trim();
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

        $(document).on('input change', '#text-srh, #toggleActive, .filter-selection', filterTable);

        $(document).on('click', '.reset_filters', function () {
            $('.filter-selection').each(function () {
                $(this).val(null).trigger('change.select2');
            });

            $('#text-srh').val('');

            filterTable();
        });
        
        filterTable();

    });
</script>



