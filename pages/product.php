<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

?>
<style>
    .select2-container {
        z-index: 9999 !important; 
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
                                <select id="correlatedProducts" name="correlatedProducts" class="select2 form-control" multiple="multiple">
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
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Stock Type</label>
                                <select class="form-select" id="stock_type" name="stock_type">
                                    <option selected>Choose...</option>
                                    <option value="1">One</option>
                                    <option value="2">Two</option>
                                    <option value="3">Three</option>
                                </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Material</label>
                                <input type="text" id="material" name="material" class="form-control"  />
                                </div>
                            </div>
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Dimensions</label>
                                <input type="text" id="dimensions" name="dimensions" class="form-control"  />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Thickness</label>
                                <input type="text" id="thickness" name="thickness" class="form-control"  />
                                </div>
                            </div>
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Gauge</label>
                                <input type="text" id="gauge" name="gauge" class="form-control"  />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Grade</label>
                                <input type="text" id="grade" name="grade" class="form-control"  />
                                </div>
                            </div>
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Color</label>
                                <input type="text" id="color" name="color" class="form-control"  />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Color Code</label>
                                <input type="text" id="colorCode" name="colorCode" class="form-control"  />
                                </div>
                            </div>
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-4">
                                <div class="mb-3">
                                <label class="form-label">Paint Provider</label>
                                <input type="text" id="paintProvider" name="paintProvider" class="form-control"  />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                <label class="form-label">Color Group</label>
                                <input type="text" id="colorGroup" name="colorGroup" class="form-control"  />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                <label class="form-label">Coating</label>
                                <input type="text" id="coating" name="coating" class="form-control"  />
                                </div>
                            </div>
                            </div>


                            <div class="row pt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Warranty Type</label>
                                <input type="text" id="warrantyType" name="warrantyType" class="form-control"  />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Profile</label>
                                <input type="text" id="profile" name="profile" class="form-control"  />
                                </div>
                            </div>
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Width</label>
                                <input type="text" id="width" name="width" class="form-control"  />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Length</label>
                                <input type="text" id="length" name="length" class="form-control"  />
                                </div>
                            </div>
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Weight</label>
                                <input type="text" id="weight" name="weight" class="form-control"  />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Unit of Measure</label>
                                <input type="text" id="unitofMeasure" name="unitofMeasure" class="form-control"  />
                                </div>
                            </div>
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">UnitPrice</label>
                                <input type="text" id="unitPrice" name="unitPrice" class="form-control"  />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">UPC</label>
                                <input type="text" id="upc" name="upc" class="form-control"  />
                                </div>
                            </div>
                            </div>

                          

                            <div class="row pt-3">
                           
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Unit Cost</label>
                                <input type="text" id="unitCost" name="unitCost" class="form-control"  />
                                </div>
                            </div>
                            </div>

                            <div class="row pt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Unit Gross Margin</label>
                                <input type="text" id="unitGrossMargin" name="unitGrossMargin" class="form-control"  />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label class="form-label">Usage</label>
                                <input type="text" id="product_usage" name="product_usage" class="form-control"  />
                                </div>
                            </div>
                            </div>

                            <div class="mb-3">
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
        <table id="productList" class="table search-table align-middle text-nowrap">
            <thead class="header-item">
            <th>Product Name</th>
            <th>SKU</th>
            <th>Product Category</th>
            <th>Product Line</th>
            <th>Product Type</th>
            <th>Action</th>
            </thead>
            <tbody>
            <?php
                $no = 1;
                $query_product = "SELECT * FROM product";
                $result_product = mysqli_query($conn, $query_product);            
                while ($row_product = mysqli_fetch_array($result_product)) {
                    $product_id = $row_product['product_id'];
   
                ?>
                    <!-- start row -->
                    <tr class="search-items">
                        <td><?= $row_product['product_item'] ?></td>
                        <td><?= $row_product['product_sku'] ?></td>
                        <td><?= getProductCategoryName($row_product['product_category']) ?></td>
                        <td><?= getProductLineName($row_product['product_line']) ?></td>
                        <td><?= getProductTypeName($row_product['product_type']) ?></td>
                        <td>
                            <div class="action-btn">
                                <a href="#" id="view_product_btn" class="text-primary edit" data-id="<?= $row_product['product_id'] ?>">
                                    <i class="ti ti-eye fs-5"></i>
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
        </table>
        </div>
    </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('#productList').DataTable({
            "order": [[1, "asc"]] // Column index is 0-based, so column 2 is index 1
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
                        action: "fetch_modal"
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
                            window.location.href = "?page=product_product";
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


<script>
$(document).ready(function() {
    $(".select2").select2({});

    $('#addProductModal').on('shown.bs.modal', function () {
        $('.select2').select2({
            width: '100%',
            placeholder: "Select Correlated Products",
            allowClear: true
        });
    });

    $('#correlatedProducts').on('change', function () {
        console.log($(this).val());
    });
});
</script>



