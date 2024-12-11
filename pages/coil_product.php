<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

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
        z-index: 9999;
        cursor: pointer;
    }

    #productList_filter {
        display: none !important;
    }
</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"> Coils</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Coils</li>
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
            <button type="button" id="addCoilModalLabel" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addCoilModal">
                <i class="ti ti-users text-white me-1 fs-5"></i> Add Coil
            </button>
        </div>
        </div>
    </div>

    <div class="modal fade" id="addCoilModal" tabindex="-1" aria-labelledby="addCoilModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Add Coil
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add_coil" class="form-horizontal" autocomplete="off">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body">
                                <input type="hidden" id="coil_id_add" name="coil_id" class="form-control" />

                                <div class="row">
                                    <div class="card-body p-0">
                                        <h4 class="card-title text-center">Coil Image</h4>
                                        <p action="#" id="myDropzone" class="dropzone">
                                            <div class="fallback">
                                            <input type="file" id="picture_path_add" name="picture_path" class="form-control" style="display: none"/>
                                            </div>
                                        </p>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Entry #</label>
                                            <input type="text" id="entry_no_add" name="entry_no" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Warehouse</label>
                                        <div class="mb-3">
                                            <select id="warehouse_add" class="form-control select2-add" name="warehouse">
                                                <option value="" >Select One...</option>
                                                <?php
                                                $query_warehouses = "SELECT * FROM warehouses";
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

                                <div class="row pt-3">
                                <div class="col-md-6 opt_field">
                                    <label class="form-label">Color Family</label>
                                    <div class="mb-3">
                                        <select id="color_family_add" class="form-control select2-add" name="color_family">
                                            <option value="" >Select Color...</option>
                                            <?php
                                            $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0'";
                                            $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                            while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                            ?>
                                                <option value="<?= $row_paint_colors['color_id'] ?>" ><?= $row_paint_colors['color_name'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 opt_field">
                                    <label class="form-label">Close EKM Color</label>
                                    <div class="mb-3">
                                        <select id="color_close_add" class="form-control select2-add" name="color_close">
                                            <option value="" >Select Color...</option>
                                            <?php
                                            $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0'";
                                            $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                            while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                            ?>
                                                <option value="<?= $row_paint_colors['color_id'] ?>" ><?= $row_paint_colors['color_name'] ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Coil #</label>
                                            <input type="text" id="coil_no_add" name="coil_no" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" id="date_add" name="date" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Supplier</label>
                                        <div class="mb-3">
                                            <select id="supplier_add" class="form-control select2-add" name="supplier">
                                                <option value="">Select Supplier...</option>
                                                <?php
                                                $query_supplier = "SELECT * FROM supplier WHERE status = '1'";
                                                $result_supplier = mysqli_query($conn, $query_supplier);            
                                                while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                                ?>
                                                    <option value="<?= $row_supplier['supplier_id'] ?>" ><?= $row_supplier['supplier_name'] ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Sold As</label>
                                        <div class="mb-3">
                                            <select id="color_sold_as_add" class="form-control select2-add" name="color_sold_as">
                                                <option value="" >Select Color...</option>
                                                <?php
                                                $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0'";
                                                $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                                while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                                ?>
                                                    <option value="<?= $row_paint_colors['color_id'] ?>" ><?= $row_paint_colors['color_name'] ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Product ID</label>
                                            <input type="text" id="product_id_add" name="product_id" class="form-control" />
                                        </div>
                                    </div>
                                </div>   
                                
                                <div class="row pt-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Original Length</label>
                                            <input type="text" id="og_length_add" name="og_length" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Remaining Length</label>
                                            <input type="text" id="remaining_feet_add" name="remaining_feet" class="form-control" />
                                        </div>
                                    </div>
                                </div>


                                <div class="row pt-3">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Weight</label>
                                            <input type="text" id="weight_add" name="weight" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Thickness</label>
                                            <input type="text" id="thickness_add" name="thickness" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Width</label>
                                            <input type="text" id="width_add" name="width" class="form-control" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Grade</label>
                                        <div class="mb-3">
                                            <select id="grade_add" class="form-control select2-add" name="grade">
                                                <option value="" >Select Grade...</option>
                                                <?php
                                                $query_grade = "SELECT * FROM product_grade WHERE hidden = '0'";
                                                $result_grade = mysqli_query($conn, $query_grade);            
                                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                                ?>
                                                    <option value="<?= $row_grade['product_grade_id'] ?>" ><?= $row_grade['product_grade'] ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Coating</label>
                                            <input type="text" id="coating_add" name="coating" class="form-control" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tag #</label>
                                            <input type="text" id="tag_no_add" name="tag_no" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Invoice #</label>
                                            <input type="text" id="invoice_no_add" name="invoice_no" class="form-control" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-4 opt_field" data-id="5">
                                        <label class="form-label">Grade No.</label>
                                        <div class="mb-3">
                                            <select id="grade_no_add" class="form-control select2-add" name="grade_no">
                                                <option value="" >Select Grade No...</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Price ($)</label>
                                            <input type="text" id="price_add" name="price" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Contract PPF</label>
                                            <input type="text" id="contract_ppf_add" name="contract_ppf" class="form-control" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Contract PPCWG</label>
                                            <input type="text" id="contract_ppcwg_add" name="contract_ppcwg" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Invoice Price ($)</label>
                                            <input type="text" id="invoice_price_add" name="invoice_price" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Round Width</label>
                                            <input type="text" id="round_width_add" name="round_width" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                            </div>
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
        <div class="table-responsive">
        <h3 class="card-title d-flex justify-content-between align-items-center">
            Coils List
            <div class="px-3"> 
                <input type="checkbox" id="toggleActive" checked> Show Active Only
            </div>
            <div class="p-2 text-right">
                <input type="checkbox" id="onlyInStock" <?= $onlyInStock ? 'checked' : '' ?>> Show only In Stock
            </div>
        </h3>
        
        <div class="d-flex justify-content-between align-items-center mb-9">
            <div class="position-relative w-100 col-6 px-0 mr-0">
                <input type="text" class="form-control search-chat py-2 ps-5 " id="text-srh" placeholder="Search Coil #">
                <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </div>
            <div class="col-6 d-flex justify-content-between align-items-center">
                <div class="position-relative w-100 px-1 col-6">
                    <select class="form-control search-chat py-0 ps-5" id="select-color" data-category="">
                        <option value="" data-category="">All Colors</option>
                        <optgroup label="Coil Colors">
                            <?php
                            $query_color = "SELECT * FROM paint_colors WHERE hidden = '0'";
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
                <div class="position-relative w-100 px-1 col-6">
                    <select class="form-control search-chat py-0 ps-5" id="select-grade" data-category="">
                        <option value="" data-category="">All Grades</option>
                        <optgroup label="Coil Grades">
                            <?php
                            $query_grade = "SELECT * FROM product_grade WHERE hidden = '0'";
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
            </div>
        </div>
        <div class="datatables">
            <div class="table-responsive">
                <table id="productList" class="table search-table align-middle text-nowrap">
                    <thead class="header-item">
                    <th>Entry #</th>
                    <th>Color</th>
                    <th>Grade</th>
                    <th>Remaining Feet</th>
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

                        if (!empty($color_id)) {
                            $query_coil .= " AND color_family = '$color_id'";
                        }

                        if (!empty($grade_id)) {
                            $query_coil .= " AND grade = '$grade_id'";
                        }

                        if ($onlyInStock) {
                            $query_coil .= " AND remaining_feet > 0";
                        }

                        $result_coil = mysqli_query($conn, $query_coil);            
                        while ($row_coil = mysqli_fetch_array($result_coil)) {
                            $coil_id = $row_coil['coil_id'];
                            $db_status = $row_coil['status'];
                            $remaining_feet = $row_coil['remaining_feet'] ?? 0;

                            if ($db_status == '0') {
                                $status_icon = "text-danger ti ti-trash";
                                $status = "<a href='#'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                            } else {
                                $status_icon = "text-warning ti ti-reload";
                                $status = "<a href='#'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                            }

                            if(!empty($row_coil['main_image'])){
                                $picture_path = $row_coil['main_image'];
                            }else{
                                $picture_path = "images/coils/product.jpg";
                            }

                            $color_details = getColorDetails($row_coil['color_sold_as']);
        
                        ?>
                            <!-- start row -->
                            <tr class="search-items">
                                <td>
                                    <a href="javascript:void(0)">
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
                                <td><?= $remaining_feet ?></td>
                                <td><?= $status ?></td>
                                <td>
                                    <div class="action-btn text-center">
                                        <a href="#" id="edit_product_btn" class="text-warning edit" data-id="<?= $row_coil['coil_id'] ?>">
                                            <i class="text-warning ti ti-pencil fs-7"></i>
                                        </a>
                                        <a href="#" id="delete_product_btn" class="text-danger edit changeStatus" data-no="<?= $no ?>" data-id="<?= $coil_id ?>" data-status='<?= $db_status ?>'>
                                            <i class="ti <?= $status_icon ?> fs-7"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                        $no++;
                        } ?>
                    </tbody>
                    <script>
                        $(document).ready(function() {
                            $(document).on('click', '.changeStatus', function(event) {
                                var confirmed = confirm("Are you sure you want to change the status of this Coil?");
                                
                                if (confirmed) {
                                    var coil_id = $(this).data('id');
                                    var status = $(this).data('status');
                                    var no = $(this).data('no');
                                    
                                    $.ajax({
                                        url: 'pages/coil_product_ajax.php',
                                        type: 'POST',
                                        data: {
                                            coil_id: coil_id,
                                            status: status,
                                            action: 'change_status'
                                        },
                                        success: function(response) {
                                            if (response == 'success') {
                                                var newStatus = (status == 1) ? 0 : 1;
                                                var newStatusText = (status == 1) ? 'Inactive' : 'Active';
                                                var newStatusClass = (status == 1) ? 'alert-danger bg-danger' : 'alert-success bg-success';
                                                var newIconClass = (status == 1) ? 'text-danger ti ti-reload' : 'text-danger ti ti-trash';
                                                var newButtonText = (status == 1) ? 'Archive' : 'Edit';
                                                
                                                $('#status-alert' + no)
                                                    .removeClass()
                                                    .addClass('alert ' + newStatusClass + ' text-white border-0 text-center py-1 px-2 my-0')
                                                    .text(newStatusText);
                                                
                                                $(".changeStatus[data-no='" + no + "']").data('status', newStatus);
                                                $('.product' + no).toggleClass('emphasize-strike', newStatus == 0);
                                                
                                                $('#action-button-' + no).html('<a href="#" class="btn ' + (newStatus == 1 ? 'btn-light' : 'btn-primary') + ' py-1" data-id="' + coil_id + '" data-row="' + no + '" style="border-radius: 10%;">' + newButtonText + '</a>');
                                                
                                                $('#delete_product_btn_' + no).find('i').removeClass().addClass(newIconClass + ' fs-7');

                                                $('#toggleActive').trigger('change');
                                            } else {
                                                alert('Failed to change status.');
                                            }
                                        },
                                        error: function(jqXHR, textStatus, errorThrown) {
                                            alert('Error: ' + textStatus + ' - ' + errorThrown);
                                        }
                                    });
                                }
                            });



                            $(document).on('click', '.hideProduct', function(event) {
                                event.preventDefault();
                                var coil_id = $(this).data('id');
                                var rowId = $(this).data('row');
                                $.ajax({
                                    url: 'pages/coil_product_ajax.php',
                                    type: 'POST',
                                    data: {
                                        coil_id: coil_id,
                                        action: 'hide_product'
                                    },
                                    success: function(response) {
                                        if (response == 'success') {
                                            $('#product-row-' + rowId).remove();
                                        } else {
                                            alert('Failed to hide coil.');
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
</div>

<script>
    function displayFileNames() {
        let files = document.getElementById('picture_path_add').files;
        let fileNames = '';

        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                let file = files[i];
                fileNames += `<p>${file.name}</p>`;
            }
        } else {
            fileNames = '<p>No files selected</p>';
        }

        console.log(fileNames);
    }
    $(document).ready(function() {
        displayFileNames()
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
                    displayFileNames();
                });

                this.on("removedfile", function(file) {
                    uploadedFiles = uploadedFiles.filter(f => f.name !== file.name);
                    updateFileInput();
                    displayFileNames();
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
            "order": [[1, "asc"]],
            "pageLength": 100,
            "lengthMenu": [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            "dom": 'lftp',
        });

        $('#text-srh').on('keyup', function () {
            table.search(this.value).draw();
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

        $(".select2-add").select2({
            width: '100%',
            placeholder: "Select One...",
            allowClear: true,
            dropdownParent: $('#addCoilModal')
        });

        // Show the Edit Product modal and log the product ID
        $(document).on('click', '#edit_product_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/coil_product_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_edit_modal"
                    },
                    success: function(response) {
                        $('#updateProductModal').html(response);
                        $('#updateProductModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('submit', '#edit_coil', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/coil_product_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#updateProductModal').modal('hide');
                    if (response.trim() === "success") {
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

        $(document).on('submit', '#add_coil', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/coil_product_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addCoilModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New coil added successfully.");
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

        $('#select-color').select2();
        $('#select-grade').select2();
        

        $('#select-color').on('change', function() {
            updateURLParam('color_id', $(this).val());
        });

        $('#select-grade').on('change', function() {
            updateURLParam('grade_id', $(this).val());
        });

        $('#onlyInStock').on('change', function() {
            var checked = $(this).prop('checked') ? 1 : 0;
            var url = new URL(window.location.href);
            url.searchParams.set('onlyInStock', checked);
            window.history.replaceState({}, '', url.toString());
            window.location.reload();
        });

        function updateURLParam(param, value) {
            var url = new URL(window.location.href);
            if (value === 0 || value === '') {
                url.searchParams.delete(param);
            } else {
                url.searchParams.set(param, value);
            }
            window.history.replaceState({}, '', url.toString());
            window.location.reload();
        }

    });
</script>



