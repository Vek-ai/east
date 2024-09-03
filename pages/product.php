<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$generate_rend_upc = generateRandomUPC();
$picture_path = "images/product/product.jpg";
?>
<style>
    .select2-container {
        z-index: 9999 !important; 
    }
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
            <button type="button" id="addProductModalLabel" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addProductModal">
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
                <form id="add_product" class="form-horizontal">
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-body">
                                <input type="hidden" id="product_id" name="product_id" class="form-control"  />

                                <div class="row">
                                    <div class="card-body p-0">
                                        <h4 class="card-title text-center">Product Image</h4>
                                        <p action="#" id="myDropzone" class="dropzone">
                                            <div class="fallback">
                                            <input type="file" id="picture_path_add" name="picture_path[]" class="form-control" style="display: none" multiple/>
                                            </div>
                                        </p>
                                    </div>
                                </div>
                                

                                <div class="row pt-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Product Name</label>
                                            <input type="text" id="product_item" name="product_item" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Product SKU</label>
                                            <input type="text" id="product_sku" name="product_sku" class="form-control" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                    <label class="form-label">Product Category</label>
                                    <select id="product_category" class="form-control" name="product_category">
                                        <option value="/" >Select One...</option>
                                        <?php
                                        $query_roles = "SELECT * FROM product_category WHERE hidden = '0'";
                                        $result_roles = mysqli_query($conn, $query_roles);            
                                        while ($row_product_category = mysqli_fetch_array($result_roles)) {
                                        ?>
                                            <option value="<?= $row_product_category['product_category_id'] ?>" ><?= $row_product_category['product_category'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                    <label class="form-label">Product Line</label>
                                    <select id="product_line" class="form-control" name="product_line">
                                        <option value="/" >Select One...</option>
                                        <?php
                                        $query_roles = "SELECT * FROM product_line WHERE hidden = '0'";
                                        $result_roles = mysqli_query($conn, $query_roles);            
                                        while ($row_product_line = mysqli_fetch_array($result_roles)) {
                                        ?>
                                            <option value="<?= $row_product_line['product_line_id'] ?>" ><?= $row_product_line['product_line'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                    <label class="form-label">Product Type</label>
                                    <select id="product_type" class="form-control" name="product_type">
                                        <option value="/" >Select One...</option>
                                        <?php
                                        $query_roles = "SELECT * FROM product_type WHERE hidden = '0'";
                                        $result_roles = mysqli_query($conn, $query_roles);            
                                        while ($row_product_type = mysqli_fetch_array($result_roles)) {
                                        ?>
                                            <option value="<?= $row_product_type['product_type_id'] ?>" ><?= $row_product_type['product_type'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                </div>


                                <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-12">
                                    <label class="form-label">Correlated products</label>
                                    <select id="correlatedProducts" name="correlatedProducts[]" class="select2-add form-control" multiple="multiple">
                                        <optgroup label="Select Correlated Products">
                                            <?php
                                            $query_products = "SELECT * FROM product";
                                            $result_products = mysqli_query($conn, $query_products);            
                                            while ($row_products = mysqli_fetch_array($result_products)) {
                                            ?>
                                                <option value="<?= $row_products['product_id'] ?>" ><?= $row_products['product_item'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                    </div>
                                </div>        


                                <div class="row pt-3">
                                <div class="col-md-6 opt_field" data-id="1">
                                    <div class="mb-3">
                                    <label class="form-label">Stock Type</label>
                                    <select id="stock_type" class="form-control" name="stock_type">
                                        <option value="/" >Select Stock Type...</option>
                                        <?php
                                        $query_stock_type = "SELECT * FROM stock_type WHERE hidden = '0'";
                                        $result_stock_type = mysqli_query($conn, $query_stock_type);            
                                        while ($row_stock_type = mysqli_fetch_array($result_stock_type)) {
                                        ?>
                                            <option value="<?= $row_stock_type['stock_type_id'] ?>" ><?= $row_stock_type['stock_type'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-6 opt_field" data-id="2">
                                    <div class="mb-3">
                                    <label class="form-label">Material</label>
                                    <input type="text" id="material" name="material" class="form-control"  />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6 opt_field" data-id="3">
                                    <div class="mb-3">
                                    <label class="form-label">Dimensions</label>
                                    <input type="text" id="dimensions" name="dimensions" class="form-control"  />
                                    </div>
                                </div>
                                <div class="col-md-6 opt_field" data-id="4">
                                    <div class="mb-3">
                                    <label class="form-label">Thickness</label>
                                    <input type="text" id="thickness" name="thickness" class="form-control"  />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6 opt_field" data-id="5">
                                    <div class="mb-3">
                                    <label class="form-label">Gauge</label>
                                    <select id="gauge" class="form-control" name="gauge">
                                        <option value="/" >Select Gauge...</option>
                                        <?php
                                        $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0'";
                                        $result_gauge = mysqli_query($conn, $query_gauge);            
                                        while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                        ?>
                                            <option value="<?= $row_gauge['product_gauge_id'] ?>" ><?= $row_gauge['product_gauge'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-6 opt_field" data-id="6">
                                    <div class="mb-3">
                                    <label class="form-label">Grade</label>
                                    <select id="grade" class="form-control" name="grade">
                                        <option value="/" >Select Grade...</option>
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
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-4 opt_field" data-id="7">
                                    <div class="mb-3">
                                    <label class="form-label">Color</label>
                                    <select id="color" class="form-control" name="color">
                                        <option value="/" >Select Color...</option>
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
                                <div class="col-md-4 opt_field" data-id="8">
                                    <div class="mb-3">
                                    <label class="form-label">Paint Provider</label>
                                    <select id="paintProvider" class="form-control" name="paintProvider">
                                        <option value="/" >Select Color...</option>
                                        <?php
                                        $query_paint_providers = "SELECT * FROM paint_providers WHERE hidden = '0'";
                                        $result_paint_providers = mysqli_query($conn, $query_paint_providers);            
                                        while ($row_paint_providers = mysqli_fetch_array($result_paint_providers)) {
                                        ?>
                                            <option value="<?= $row_paint_providers['provider_id'] ?>" ><?= $row_paint_providers['provider_name'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-4 opt_field" data-id="17">
                                    <div class="mb-3">
                                    <label class="form-label">Coating</label>
                                    <input type="text" id="coating" name="coating" class="form-control"  />
                                    </div>
                                </div>
                                </div>


                                <div class="row pt-3">
                                <div class="col-md-6 opt_field" data-id="9">
                                    <div class="mb-3">
                                    <label class="form-label">Warranty Type</label>
                                    <select id="warrantyType" class="form-control" name="warrantyType">
                                        <option value="/" >Select Warranty Type...</option>
                                        <?php
                                        $query_product_warranty_type = "SELECT * FROM product_warranty_type WHERE hidden = '0'";
                                        $result_product_warranty_type = mysqli_query($conn, $query_product_warranty_type);            
                                        while ($row_product_warranty_type = mysqli_fetch_array($result_product_warranty_type)) {
                                        ?>
                                            <option value="<?= $row_product_warranty_type['product_warranty_type_id'] ?>" ><?= $row_product_warranty_type['product_warranty_type'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-6 opt_field" data-id="10">
                                    <div class="mb-3">
                                    <label class="form-label">Profile</label>
                                    <select id="profile" class="form-control" name="profile">
                                        <option value="/" >Select Profile...</option>
                                        <?php
                                        $query_profile_type = "SELECT * FROM profile_type WHERE hidden = '0'";
                                        $result_profile_type = mysqli_query($conn, $query_profile_type);            
                                        while ($row_profile_type = mysqli_fetch_array($result_profile_type)) {
                                        ?>
                                            <option value="<?= $row_profile_type['profile_type_id'] ?>" ><?= $row_profile_type['profile_type'] ?></option>
                                        <?php   
                                        }
                                        ?>
                                    </select>
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6 opt_field" data-id="11">
                                    <div class="mb-3">
                                    <label class="form-label">Width</label>
                                    <input type="text" id="width" name="width" class="form-control"  />
                                    </div>
                                </div>
                                <div class="col-md-6 opt_field" data-id="12">
                                    <div class="mb-3">
                                    <label class="form-label">Length</label>
                                    <input type="text" id="length" name="length" class="form-control"  />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-6 opt_field" data-id="13">
                                    <div class="mb-3">
                                    <label class="form-label">Weight</label>
                                    <input type="text" id="weight" name="weight" class="form-control"  />
                                    </div>
                                </div>
                                <div class="col-md-6 opt_field" data-id="14">
                                    <div class="mb-3">
                                    <label class="form-label">Unit of Measure</label>
                                    <input type="text" id="unitofMeasure" name="unitofMeasure" class="form-control"  />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                    <label class="form-label">Unit Price</label>
                                    <input type="text" id="unitPrice" name="unitPrice" class="form-control"  />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                    <label class="form-label">Unit Cost</label>
                                    <input type="text" id="unitCost" name="unitCost" class="form-control"  />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                    <label class="form-label">Unit Gross Margin</label>
                                    <input type="text" id="unitGrossMargin" name="unitGrossMargin" class="form-control"  />
                                    </div>
                                </div>
                                </div>

                                <div class="row pt-3">
                                
                                <div class="col-md-6 opt_field" data-id="15">
                                    <div class="mb-3">
                                    <label class="form-label">Usage</label>
                                    <input type="text" id="product_usage" name="product_usage" class="form-control"  />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                    <label class="form-label">UPC</label>
                                    <input type="text" id="upc" name="upc" class="form-control" value="<?= $generate_rend_upc ?>" readonly/>
                                    </div>
                                </div>
                                </div>

                                <div class="mb-3 opt_field" data-id="16">
                                <label class="form-label">Comment</label>
                                <textarea class="form-control" id="comment" name="comment" rows="5"></textarea>
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
        <div class="table-responsive">
        <h3 class="card-title d-flex justify-content-between align-items-center">
            Products List 
            <div class="px-3"> 
                <input type="checkbox" id="toggleActive" checked> Show Active Only
            </div>
        </h3>
        <table id="productList" class="table search-table align-middle text-nowrap">
            <thead class="header-item">
            <th>Product Name</th>
            <th>SKU</th>
            <th>Product Category</th>
            <th>Product Line</th>
            <th>Product Type</th>
            <th>Status</th>
            <th>Action</th>
            </thead>
            <tbody>
            <?php
                $no = 1;
                $query_product = "SELECT * FROM product";
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
                    <tr class="search-items">
                        <td>
                            <a href="/?page=product_details&product_id='<?= $row_product['product_id'] ?>">
                                <div class="d-flex align-items-center">
                                    <img src="<?= $picture_path ?>" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                    <div class="ms-3">
                                        <h6 class="fw-semibold mb-0 fs-4"><?= $row_product['product_item'] ?></h6>
                                    </div>
                                </div>
                            </a>
                        </td>
                        <td><?= $row_product['product_sku'] ?></td>
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
                                url: 'pages/product_ajax.php',
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
                            url: 'pages/product_ajax.php',
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

        var table = $('#productList').DataTable({
            "order": [[1, "asc"]]
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
            placeholder: "Select Correlated Products",
            allowClear: true
        });

        $('#product_category').on('change', function() {
            var product_category_id = $(this).val();
            $.ajax({
                url: 'pages/product_ajax.php',
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
        });

        

        // Show the View Product modal and log the product ID
        $(document).on('click', '#view_product_btn', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/product_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_view_modal"
                    },
                    success: function(response) {
                        $('#updateProductModal').html(response);
                        $(".select2-update").select2({
                            width: '100%',
                            placeholder: "Select Correlated Products",
                            allowClear: true
                        });
                        $('#updateProductModal').modal('show');

                        $('#updateProductModal').on('hide.bs.modal', function () {
                            $(".select2-add").select2({
                                width: '100%',
                                placeholder: "Select Correlated Products",
                                allowClear: true
                            });
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });


        // Show the Edit Product modal and log the product ID
        $(document).on('click', '#edit_product_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/product_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_edit_modal"
                    },
                    success: function(response) {
                        $('#updateProductModal').html(response);
                        $(".select2-update").select2({
                            width: '100%',
                            placeholder: "Select Correlated Products",
                            allowClear: true
                        });
                        $('#updateProductModal').modal('show');

                        $('#updateProductModal').on('hide.bs.modal', function () {
                            $(".select2-add").select2({
                                width: '100%',
                                placeholder: "Select Correlated Products",
                                allowClear: true
                            });
                        });
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
                url: 'pages/product_ajax.php',
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
                url: 'pages/product_ajax.php',
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


    });
</script>



