<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

$generate_rend_upc = generateRandomUPC();
$picture_path = "images/product/product.jpg";

$color_id = isset($_REQUEST['color_id']) ? $_REQUEST['color_id'] : '';
$grade_id = isset($_REQUEST['grade_id']) ? $_REQUEST['grade_id'] : '';
$gauge_id = isset($_REQUEST['gauge_id']) ? $_REQUEST['gauge_id'] : '';
$category_id = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : '';
$profile_id = isset($_REQUEST['profile_id']) ? $_REQUEST['profile_id'] : '';
$type_id = isset($_REQUEST['type_id']) ? $_REQUEST['type_id'] : '';
$onlyInStock = isset($_REQUEST['onlyInStock']) ? filter_var($_REQUEST['onlyInStock'], FILTER_VALIDATE_BOOLEAN) : false;

$price_per_hem = getPaymentSetting('price_per_hem');
$price_per_bend = getPaymentSetting('price_per_bend');

?>
<style>
    /* .select2-container {
        z-index: 9999 !important; 
    } */
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
</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"> Product</h4>
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
            <button type="button" id="addProductModalBtn" class="btn btn-primary d-flex align-items-center" data-id="">
                <i class="ti ti-users text-white me-1 fs-5"></i> Add Product
            </button>
        </div>
        </div>
    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Add Product
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="product_form" class="form-horizontal">
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

    <div class="modal fade" id="updateProductModal" tabindex="-1" role="dialog" aria-labelledby="updateProductModal" aria-hidden="true">
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
                    Filter Products 
                </h3>
                <div class="position-relative w-100 px-0 mr-0 mb-2">
                    <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search Product">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center">
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control py-0 ps-5 select2" id="select-category" data-category="">
                            <option value="" data-category="">All Categories</option>
                            <optgroup label="Category">
                                <?php
                                $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                $result_category = mysqli_query($conn, $query_category);
                                while ($row_category = mysqli_fetch_array($result_category)) {
                                    $selected = ($category_id == $row_category['product_category_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_category['product_category_id'] ?>" data-category="<?= $row_category['product_category'] ?>" <?= $selected ?>><?= $row_category['product_category'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2" id="select-system" data-category="">
                            <option value="" data-category="">All Product Systems</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_system = "SELECT * FROM product_system WHERE hidden = '0' AND status = '1' ORDER BY `product_system` ASC";
                                $result_system = mysqli_query($conn, $query_system);
                                while ($row_system = mysqli_fetch_array($result_system)) {
                                    $selected = ($product_system == $row_system['product_system_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_system['product_system_id'] ?>" data-category="<?= $row_system['product_category'] ?>" <?= $selected ?>><?= $row_system['product_system'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2" id="select-line" data-category="">
                            <option value="" data-category="">All Product Lines</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_line = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                                $result_line = mysqli_query($conn, $query_line);
                                while ($row_line = mysqli_fetch_array($result_line)) {
                                    $selected = ($type_id == $row_line['product_line_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_line['product_line_id'] ?>" data-category="<?= $row_line['product_category'] ?>" <?= $selected ?>><?= $row_line['product_line'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2" id="select-type" data-category="">
                            <option value="" data-category="">All Product Types</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                                $result_type = mysqli_query($conn, $query_type);
                                while ($row_type = mysqli_fetch_array($result_type)) {
                                    $selected = ($type_id == $row_type['product_type_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_type['product_type_id'] ?>" data-category="<?= $row_type['product_category'] ?>" <?= $selected ?>><?= $row_type['product_type'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2" id="select-profile" data-category="">
                            <option value="" data-category="">All Profile Types</option>
                            <optgroup label="Product Line">
                                <?php
                                $query_profile = "SELECT * FROM profile_type WHERE hidden = '0'";
                                $result_profile = mysqli_query($conn, $query_profile);
                                while ($row_profile = mysqli_fetch_array($result_profile)) {
                                    $selected = ($profile_id == $row_profile['profile_type_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_profile['profile_type_id'] ?>" data-category="<?= $v['product_category'] ?>" <?= $selected ?>><?= $row_profile['profile_type'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2" id="select-color" data-category="">
                            <option value="" data-category="">All Colors</option>
                            <optgroup label="Product Colors">
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
                        <select class="form-control search-chat py-0 ps-5 select2" id="select-grade" data-category="">
                            <option value="" data-category="">All Grades</option>
                            <optgroup label="Product Grades">
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
                        <select class="form-control search-chat py-0 ps-5 select2" id="select-gauge" data-category="">
                            <option value="" data-category="">All Gauges</option>
                            <optgroup label="Product Gauges">
                                <?php
                                $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY `product_gauge` ASC";
                                $result_gauge = mysqli_query($conn, $query_gauge);
                                while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                    $selected = ($gauge_id == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_gauge['product_gauge_id'] ?>" data-category="gauge" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
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
                <div class="px-3 mb-2">
                    <input type="checkbox" id="onlyInStock" <?= $onlyInStock ? 'checked' : '' ?>> Show only In Stock
                </div>
            </div>
            <div class="col-9">
                <h3 class="card-title mb-2">
                    Products List 
                </h3>
                <div id="selected-tags" class="mb-2"></div>
                <div class="datatables">
                    <div class="table-responsive">
                        <table id="productList" class="table search-table align-middle text-wrap">
                            <thead class="header-item">
                            <th>Product Name</th>
                            <th>Product Category</th>
                            <th>Product Line</th>
                            <th>Product Type</th>
                            <th>Status</th>
                            <th>Action</th>
                            </thead>
                            <tbody>
                            <?php
                                $no = 1;
                                $query_product = "
                                    SELECT 
                                        p.*,
                                        COALESCE(SUM(i.quantity_ttl), 0) AS total_quantity
                                    FROM 
                                        product_duplicate AS p
                                    LEFT JOIN 
                                        inventory AS i ON p.product_id = i.product_id
                                    WHERE 
                                        p.hidden = '0'
                                ";

                                if (!empty($color_id)) {
                                    $query_product .= " AND p.color = '$color_id'";
                                }

                                if (!empty($grade_id)) {
                                    $query_product .= " AND p.grade = '$grade_id'";
                                }

                                if (!empty($gauge_id)) {
                                    $query_product .= " AND p.gauge = '$gauge_id'";
                                }

                                if (!empty($type_id)) {
                                    $query_product .= " AND p.product_type = '$type_id'";
                                }

                                if (!empty($profile_id)) {
                                    $query_product .= " AND p.profile = '$profile_id'";
                                }

                                if (!empty($category_id)) {
                                    $query_product .= " AND p.product_category = '$category_id'";
                                }

                                $query_product .= " GROUP BY p.product_id";

                                if ($onlyInStock) {
                                    $query_product .= " HAVING total_quantity > 1";
                                }

                                $result_product = mysqli_query($conn, $query_product);            
                                while ($row_product = mysqli_fetch_array($result_product)) {
                                    $product_id = $row_product['product_id'];
                                    $db_status = $row_product['status'];

                                    if ($db_status == '0') {
                                        $status_icon = "text-danger ti ti-trash";
                                        $status = "<a href='#'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                                    } else {
                                        $status_icon = "text-warning ti ti-reload";
                                        $status = "<a href='#'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                                    }

                                    if(!empty($row_product['main_image'])){
                                        $picture_path = $row_product['main_image'];
                                    }else{
                                        $picture_path = "images/product/product.jpg";
                                    }
                
                                ?>
                                    <!-- start row -->
                                    <tr class="search-items" 
                                        data-system="<?= $row_product['product_system'] ?>"
                                        data-line="<?= $row_product['product_line'] ?>"
                                        data-profile="<?= $row_product['profile'] ?>"
                                        data-profile="<?= $row_product['profile'] ?>"
                                        data-color="<?= $row_product['color'] ?>"
                                        data-grade="<?= $row_product['grade'] ?>"
                                        data-gauge="<?= $row_product['gauge'] ?>"
                                        data-category="<?= $row_product['product_category'] ?>"
                                        data-type="<?= $row_product['product_type'] ?>"
                                        data-active="<?= $row_product['p.status'] = 1 ? 1 : 0 ?>"
                                        data-instock="<?= $row_product['total_quantity'] > 1 ? 1 : 0 ?>"
                                        >
                                        <td>
                                            <a href="/?page=product_details&product_id=<?= $row_product['product_id'] ?>">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= $picture_path ?>" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                                    <div class="ms-3">
                                                        <h6 class="fw-semibold mb-0 fs-4"><?= $row_product['product_item'] ?></h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td><?= getProductCategoryName($row_product['product_category']) ?></td>
                                        <td><?= getProductLineName($row_product['product_line']) ?></td>
                                        <td><?= getProductTypeName($row_product['product_type']) ?></td>
                                        <td><?= $status ?></td>
                                        <td>
                                            <div class="action-btn text-center">
                                                <a href="#" id="view_product_btn" class="text-primary edit" data-id="<?= $row_product['product_id'] ?>">
                                                    <i class="text-primary ti ti-eye fs-7"></i>
                                                </a>
                                                <a href="#" id="edit_product_btn" class="text-warning edit" data-id="<?= $row_product['product_id'] ?>">
                                                    <i class="text-warning ti ti-pencil fs-7"></i>
                                                </a>
                                                <a href="#" id="delete_product_btn" class="text-danger edit changeStatus" data-no="<?= $no ?>" data-id="<?= $product_id ?>" data-status='<?= $db_status ?>'>
                                                    
                                                    <i class="text-danger ti ti-trash fs-7"></i>
                                                </a>
                                                
                                                
                                                <!-- <a href="javascript:void(0)" class="text-dark delete ms-2" data-id="<?= $row_product['product_id'] ?>">
                                                    <i class="ti ti-trash fs-5"></i>
                                                </a> -->
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
                                        var confirmed = confirm("Are you sure you want to change the status of this Product?");
                                        
                                        if (confirmed) {
                                            var product_id = $(this).data('id');
                                            var status = $(this).data('status');
                                            var no = $(this).data('no');
                                            
                                            $.ajax({
                                                url: 'pages/product3_ajax.php',
                                                type: 'POST',
                                                data: {
                                                    product_id: product_id,
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
                                                        
                                                        $('#action-button-' + no).html('<a href="#" class="btn ' + (newStatus == 1 ? 'btn-light' : 'btn-primary') + ' py-1" data-id="' + product_id + '" data-row="' + no + '" style="border-radius: 10%;">' + newButtonText + '</a>');
                                                        
                                                        $('#delete_product_btn').find('i').removeClass().addClass(newIconClass + ' fs-7');
                                                        
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
                                        var product_id = $(this).data('id');
                                        var rowId = $(this).data('row');
                                        $.ajax({
                                            url: 'pages/product3_ajax.php',
                                            type: 'POST',
                                            data: {
                                                product_id: product_id,
                                                action: 'hide_product'
                                            },
                                            success: function(response) {
                                                if (response == 'success') {
                                                    $('#product-row-' + rowId).remove();
                                                } else {
                                                    alert('Failed to hide product.');
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
        var basePrice = 0;
        var selectedCategory = '';
        let uploadedFiles = []; // Array to hold the files

        $('.dropzone').dropzone({
            addRemoveLinks: true,
            dictRemoveFile: "X",
            init: function() {
                this.on("addedfile", function(file) {
                    uploadedFiles.push(file);
                    updateFileInput();
                    displayFileNames()
                });

                this.on("removedfile", function(file) {
                    uploadedFiles = uploadedFiles.filter(f => f.name !== file.name);
                    updateFileInput();
                    displayFileNames()
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
                    url: 'pages/product3_ajax.php',
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

        $('#select-system, #select-line, #select-profile, #select-color, #select-grade, #select-gauge, #select-category, #select-type, #onlyInStock').on('change', filterTable);

        $('#text-srh').on('keyup', filterTable);

        $('#toggleActive').on('change', filterTable);

        $('#toggleActive').trigger('change');

        $(".select2-add").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            });
        });

        /* $('#product_category').on('change', function() {
            var product_category_id = $(this).val();
            $.ajax({
                url: 'pages/product3_ajax.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    product_category_id: product_category_id,
                    action: "fetch_product_fields"
                },
                success: function(response) {
                    if (response.length > 0) {
                        $('.opt_field').hide();

                        response.forEach(function(field) {
                            var fieldParts = field.fields.split(',');
                            fieldParts.forEach(function(part) {
                                $('.opt_field[data-id="' + part + '"]').show();
                            });
                        });
                    } else {
                        $('.opt_field').show();
                        console.log('No fields found for this category.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }); */

        $(document).on('click', '#addProductModalBtn, #edit_product_btn', function(event) {
            event.preventDefault();
            var id = $(this).data('id') || '';

            console.log(id);
            $.ajax({
                url: 'pages/product3_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    action: "fetch_product_modal"
                },
                success: function(response) {
                    $('#product_form').html(response);
                    $(".select2").each(function() {
                        let $this = $(this);

                        if ($this.hasClass("select2-hidden-accessible")) {
                            $this.select2('destroy');
                            $this.removeAttr('data-select2-id');
                            $this.next('.select2-container').remove();
                        }

                        $this.select2({
                            width: '100%',
                            dropdownParent: $this.parent()
                        });
                    });
                    $('#addProductModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('change', '.inventory_supplier', function () {
            let supplier_id = $(this).val();

            if (supplier_id) {
                $.ajax({
                    url: 'pages/inventory_ajax.php',
                    type: 'POST',
                    data: { 
                    supplier_id: supplier_id,
                    action: 'fetch_supplier_packs'
                    },
                    dataType: 'json',
                    success: function (response) {
                        let packDropdown = $('.pack_select');
                        packDropdown.empty();
                        packDropdown.append('<option value="">Select Pack...</option>');

                        if (response.length > 0) {
                            $.each(response, function (index, pack) {
                            packDropdown.append('<option value="' + pack.id + '" data-count="' + pack.pack_count + '">' + pack.pack + ' (' + pack.pack_count + ')</option>');
                            });
                        } else {
                            packDropdown.append('<option value="">No Packs Available</option>');
                        }
                        
                        packDropdown.trigger('change');
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error: ", status, error);
                    }
                });

                $.ajax({
                    url: 'pages/inventory_ajax.php',
                    type: 'POST',
                    data: { 
                        supplier_id: supplier_id,
                        action: 'fetch_supplier_cases'
                    },
                    dataType: 'json',
                    success: function (response) {
                        let caseDropdown = $('.case_select');
                        caseDropdown.empty();
                        caseDropdown.append('<option value="">Select Case...</option>');

                        if (response.length > 0) {
                            $.each(response, function (index, caseItem) {
                                caseDropdown.append('<option value="' + caseItem.id + '" data-count="' + caseItem.case_count + '">' + caseItem.case + ' (' + caseItem.case_count + ')</option>');
                            });
                        } else {
                            caseDropdown.append('<option value="">No Cases Available</option>');
                        }
                        
                        caseDropdown.trigger('change');
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error: ", status, error);
                    }
                });

            } else {
            $('.case_select').empty().append('<option value="">Select Case...</option>').trigger('change');
            }
        });

        $(document).on('click', '#view_product_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/product3_ajax.php',
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
            });
        });

        $(document).on('submit', '#update_product', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/product3_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#updateProductModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Product updated successfully.");
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

        $(document).on('submit', '#add_product', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/product3_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addProductModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New product added successfully.");
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
                width: '100%',
                dropdownParent: $(this).parent()
            });
        });

        $(document).on("change", "#base_product_add, #color_add, #gauge_add, #grade_add, #bends, #hems, #price_per_bend, #price_per_hem, #coil_width_add, #width, #pack_add, #case_add", function () {
            basePrice = parseFloat($("#base_product_add").find(":selected").data("base-price")) || 0;

            let colorMultiplier = parseFloat($("#color_add").find(":selected").data("multiplier")) || 1;
            let gaugeMultiplier = parseFloat($("#gauge_add").find(":selected").data("multiplier")) || 1;
            let gradeMultiplier = parseFloat($("#grade_add").find(":selected").data("multiplier")) || 1;

            //selectedCategory is declared global
            if (String(selectedCategory) == '4') { //category 4 = TRIM
                basePrice = parseFloat($("#color_add").find(":selected").data("price")) || 0;
                var num_bends = parseFloat($("#bends").val()) || 0;
                var num_hems = parseFloat($("#hems").val()) || 0;
                var price_per_bend = parseFloat($("#price_per_bend").val()) || 0;
                var price_per_hem = parseFloat($("#price_per_hem").val()) || 0;
                let coil_width = parseFloat($("#coil_width_add option:selected").data("width")) || 1; 
                var width = parseFloat($("#width").val()) || 0;

                var unitPrice = basePrice;

                if (num_bends > 0) {
                    unitPrice += num_bends * price_per_bend;
                }

                if (num_hems > 0) {
                    unitPrice += num_hems * price_per_hem;
                }

                if (coil_width > 0) {
                    cost_per_sq_in = (width / coil_width) * unitPrice;
                }
                
                $("#cost_per_sq_in").val(cost_per_sq_in.toFixed(2));
                var cost_per_sq_ft = cost_per_sq_in * 144;
                $("#cost_per_sq_ft").val(cost_per_sq_ft.toFixed(2));
                var cost_per_linear_ft = width / cost_per_sq_ft;
                $("#cost_per_linear_ft").val(cost_per_linear_ft.toFixed(2));

            }else if (String(selectedCategory) == '16') {  //category 16 = SCREWS
                var packs = parseFloat($("#pack_add").find(":selected").data("count")) || 1;
                var caseCount = parseFloat($("#case_add").find(":selected").data("count")) || 1;

                var unitPrice = basePrice  * packs * caseCount;

            }if (String(selectedCategory) == '3') { //category 3 = panels
                let coil_width = parseFloat($("#coil_width_add option:selected").data("width")) || 1; 
                var width = parseFloat($("#width").val()) || 0;
                var color_price = parseFloat($("#color_add").find(":selected").data("price")) || 0;

                if (coil_width > 0) {
                    cost_per_sq_in = (width / coil_width) * color_price;
                }
                
                $("#cost_per_sq_in").val(cost_per_sq_in.toFixed(2));
                var cost_per_sq_ft = cost_per_sq_in * 144;
                $("#cost_per_sq_ft").val(cost_per_sq_ft.toFixed(2));
                var cost_per_linear_ft = width / cost_per_sq_ft;
                $("#cost_per_linear_ft").val(cost_per_linear_ft.toFixed(2));

                var unitPrice = basePrice * colorMultiplier * gaugeMultiplier * gradeMultiplier;
            }else{
                var unitPrice = basePrice * colorMultiplier * gaugeMultiplier * gradeMultiplier;
            }

            $("#unit_price_add").val(unitPrice.toFixed(2));
        });

        function updateSearchCategory() {
            selectedCategory = $('#product_category_add').val();
            let hasCategory = !!selectedCategory; 

            $('.add-category option').each(function() {
                let match = String($(this).data('category')) === String(selectedCategory);
                $(this).toggle(match);
            });

            $("#bends").val('').trigger('change');
            $("#hems").val('').trigger('change');

            if (hasCategory) {
                $('#add-fields').removeClass('d-none');
            } else {
                $('#add-fields').addClass('d-none');
            }

            $('.panel-fields, .trim-fields, .screw-fields').addClass('d-none');
            $('#base_product_div').removeClass('d-none');
            $('#unit_price_div').removeClass('d-none');

            if (selectedCategory == 3) { // PANELS
                $('.panel-fields').removeClass('d-none');
            }

            if (selectedCategory == 4) { // TRIM
                $('.trim-fields').removeClass('d-none');
                $('#unit_price_div').addClass('d-none');
                $('#base_product_div').addClass('d-none');
            }

            if (selectedCategory == 16) { // SCREW
                $('.screw-fields').removeClass('d-none');
            }

            $(".select2").each(function() {
                let $this = $(this);

                if ($this.hasClass("select2-hidden-accessible")) {
                    $this.select2('destroy');
                    $this.removeAttr('data-select2-id');
                    $this.next('.select2-container').remove();
                }

                $this.select2({
                    width: '100%',
                    dropdownParent: $this.parent()
                });
            });

        }

        function filterTable() {
            var system = $('#select-system').val()?.toString() || '';
            var line = $('#select-line').val()?.toString() || '';
            var profile = $('#select-profile').val()?.toString() || '';
            var profile = $('#select-profile').val()?.toString() || '';
            var color = $('#select-color').val()?.toString() || '';
            var grade = $('#select-grade').val()?.toString() || '';
            var gauge = $('#select-gauge').val()?.toString() || '';
            var category = $('#select-category').val()?.toString() || '';
            var type = $('#select-type').val()?.toString() || '';
            var onlyInStock = $('#onlyInStock').prop('checked') ? 1 : 0;
            var textSearch = $('#text-srh').val().toLowerCase();
            var isActive = $('#toggleActive').is(':checked');

            $.fn.dataTable.ext.search = [];

            if (textSearch) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var rowText = $(table.row(dataIndex).node()).text().toLowerCase();
                    return rowText.includes(textSearch);
                });
            }

            if (isActive) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var status = $(table.row(dataIndex).node()).find('a .alert').text().trim();
                    return status === 'Active';
                });
            }

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var row = $(table.row(dataIndex).node());
                if (system && system !== '/' && row.data('system').toString() !== system) return false;
                if (line && line !== '/' && row.data('line').toString() !== line) return false;
                if (profile && profile !== '/' && row.data('profile').toString() !== profile) return false;
                if (color && color !== '/' && row.data('color').toString() !== color) return false;
                if (grade && grade !== '/' && row.data('grade').toString() !== grade) return false;
                if (gauge && gauge !== '/' && row.data('gauge').toString() !== gauge) return false;
                if (category && category !== '/' && row.data('category').toString() !== category) return false;
                if (type && type !== '/' && row.data('type').toString() !== type) return false;
                if (onlyInStock && row.data('instock') != onlyInStock) return false;

                return true;
            });

            table.draw();
            updateSelectedTags();
        }

        $(document).on('change', '#product_category_add', function() {
            updateSearchCategory();
        });

        function updateSelectedTags() {
            const sections = [
                { id: '#select-color', title: 'Color' },
                { id: '#select-grade', title: 'Grade' },
                { id: '#select-gauge', title: 'Gauge' },
                { id: '#select-category', title: 'Category' },
                { id: '#select-profile', title: 'Profile' },
                { id: '#select-system', title: 'System' },
                { id: '#select-line', title: 'Line' },
                { id: '#select-profile', title: 'Profile' },
                { id: '#select-type', title: 'Type' },
            ];

            const displayDiv = $('#selected-tags');
            displayDiv.empty();

            sections.forEach((section) => {
                const selectedOption = $(`${section.id} option:selected`);
                const selectedText = selectedOption.text().trim();

                if (selectedOption.val()) {
                    displayDiv.append(`
                        <div class="d-inline-block p-1 m-1 border rounded bg-light">
                            <span class="text-dark">${section.title}: ${selectedText}</span>
                            <button type="button" 
                                class="btn-close btn-sm ms-1 remove-tag" 
                                style="width: 0.75rem; height: 0.75rem;" 
                                aria-label="Close" 
                                data-tag="${selectedText}" 
                                data-select="${section.id}">
                            </button>
                        </div>
                    `);
                }
            });

            $('.remove-tag').on('click', function() {
                const selectId = $(this).data('select');
                $(selectId).val('').trigger('change');

                $(this).parent().remove();
            });
        }

    });
</script>



