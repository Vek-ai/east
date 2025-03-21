<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';
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

    .cart-icon {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    .cart-badge {
        position: absolute;
        top: -16px;
        right: -16px; /* Slightly outside the icon */
        background-color: red;
        color: white;
        font-size: 14px;
        font-weight: bold;
        min-width: 20px;
        min-height: 20px;
        line-height: 20px;
        text-align: center;
        border-radius: 50%;
        padding: 2px 6px;
        white-space: nowrap;
        display: none;
    }

    /* Adjust width dynamically based on number size */
    .cart-badge[data-count="10"],
    .cart-badge[data-count="99"],
    .cart-badge[data-count="100+"] {
        min-width: auto;
        padding: 2px 8px;
    }
    
    /* Show badge only when count is greater than 0 */
    .cart-badge:not(:empty):not(:contains("0")) {
        display: inline-block;
    }


</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Order Products</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=product4">Product
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Order Products</li>
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
        
        <div class="col-md-12 col-xl-4 mt-3 text-start mt-md-0 gap-3">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label">Supplier</label>
                <a href="?page=product_supplier" target="_blank" class="text-decoration-none d-none">Edit</a>
            </div>
            <div class="mb-3">
                <select id="supplier_id" class="form-control select2" name="supplier_id">
                    <option value="" >Select Supplier...</option>
                    <optgroup label="Supplier">
                        <?php
                        $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
                        $result_supplier = mysqli_query($conn, $query_supplier);            
                        while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                            $selected = (($row['supplier_id'] ?? '') == $row_supplier['supplier_id']) ? 'selected' : '';
                        ?>
                            <option value="<?= $row_supplier['supplier_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_name'] ?></option>
                        <?php   
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="col-md-12 col-xl-8 mt-3 text-end text-start mt-md-0 d-flex align-items-center justify-content-end gap-3">
            <a href="#" id="view_order" class="cart-icon text-decoration-none position-relative d-inline-flex">
                <iconify-icon icon="ic:round-shopping-cart" class="cart-icon fs-8"></iconify-icon>
                <span id="cartCounter" class="cart-badge">0</span>
            </a>
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

    <div class="modal" id="order_modal">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Save Order</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="order-tbl"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple fw-bold text-white" type="button" id="save_order" 
                        style="background-color: #17A2B8; border-color: #138496;">
                        <i class="fas fa-save" style="color: #E3F2FD;"></i> Save Order
                    </button>
                    <button class="btn ripple fw-bold text-white" type="button" id="order_products" 
                        style="background-color: #28A745; border-color: #218838;">
                        <i class="fas fa-shopping-cart" style="color: #D4EDDA;"></i> Place Order
                    </button>
                    <button class="btn ripple fw-bold text-white" data-bs-dismiss="modal" type="button" 
                        style="background-color: #DC3545; border-color: #C82333;">
                        <i class="fas fa-times" style="color: #F8D7DA;"></i> Close
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
                                $query_profile = "SELECT * FROM profile_type WHERE hidden = '0' AND status = '1' ORDER BY `profile_type` ASC";
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
                            <th>Description</th>
                            <th>Category</th>
                            <th>Color</th>
                            <th>Quantity</th>
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
                                        product AS p
                                    LEFT JOIN 
                                        inventory AS i ON p.product_id = i.product_id
                                    WHERE 
                                        p.hidden = '0' AND p.product_origin = '1'
                                    GROUP BY p.product_id
                                ";
                                $result_product = mysqli_query($conn, $query_product);            
                                while ($row_product = mysqli_fetch_array($result_product)) {
                                    $product_id = $row_product['product_id'];
                                    $db_status = $row_product['status'];

                                    if ($db_status == '0') {
                                        $status_icon = "text-danger ti ti-trash";
                                        $status = "<a href='#'><div id='status-alert$no' class='changeStatus alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' data-no='$no' data-id='$product_id' data-status='$db_status' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                                    } else {
                                        $status_icon = "text-warning ti ti-reload";
                                        $status = "<a href='#'><div id='status-alert$no' class='changeStatus alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' data-no='$no' data-id='$product_id' data-status='$db_status' style='border-radius: 5%;' role='alert'>Active</div></a>";
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
                                        <td>
                                            <select class="form-control search-chat py-0 ps-5 select2" id="select_color_<?= $row_product['product_id'] ?>" data-id="<?= $row_product['product_id'] ?>">
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
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <button class="btn btn-outline-primary btn-minus" type="button" data-id="<?= $row_product['product_id'] ?>">-</button>
                                                <input class="form-control p-1 text-center" type="number" id="qty<?= $row_product['product_id'] ?>" value="1" min="1">
                                                <button class="btn btn-outline-primary btn-plus" type="button" data-id="<?= $row_product['product_id'] ?>">+</button>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary btn-add-to-cart" type="button" data-id="<?= $row_product['product_id'] ?>" id="add-to-cart-btn">Add to Order</button>
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

<script src="includes/pricing_data.js"></script>

<script>
    function updateCartCounter() {
        $.ajax({
            url: 'pages/supplier_order_ajax.php',
            type: 'POST',
            data: {
                fetch_cart_count: 'fetch_cart_count'
            },
            success: function(response) {
                console.log(response);
                let count = parseInt(response, 10) || 0;
                let $counter = $('#cartCounter');
                if (count > 0) {
                    $counter.text(count).show();
                } else {
                    $counter.hide();
                }
            }
        });
    }

    function loadOrderContents(){
        $.ajax({
            url: 'pages/supplier_order_ajax.php',
            type: 'POST',
            data: {
                fetch_order: "fetch_order"
            },
            success: function(response) {
                $('#order-tbl').html('');
                $('#order-tbl').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function formatOption(state) {
        if (!state.id) {
            return state.text;
        }
        var color = $(state.element).data('color');
        var $state = $(
            '<span class="d-flex align-items-center small">' +
                '<span class="rounded-circle d-block p-1 me-2" style="background-color:' + color + '; width: 16px; height: 16px;"></span>' +
                state.text + 
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
                '<span class="rounded-circle d-block p-1" style="background-color:' + color + '; width: 25px; height: 25px;"></span>' +
                '&nbsp;' +
            '</span>'
        );
        return $state;
    }

    function updatequantity(element) {
        var product_id = $(element).data('id');
        var key = $(element).data('key');
        var qty = $(element).val();
        $.ajax({
            url: "pages/supplier_order_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                key: key,
                qty: qty,
                modifyquantity: 'modifyquantity',
                setquantity: 'setquantity'
            },
            success: function(data) {
                loadOrderContents();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function addquantity(element) {
        var product_id = $(element).data('id');
        var key = $(element).data('key');
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var quantity = Number(input_quantity.val());
        $.ajax({
            url: "pages/supplier_order_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                key: key,
                quantity: quantity,
                modifyquantity: 'modifyquantity',
                addquantity: 'addquantity'
            },
            success: function(data) {
                loadOrderContents();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function deductquantity(element) {
        var product_id = $(element).data('id');
        var key = $(element).data('key');
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var quantity = Number(input_quantity.val());
        $.ajax({
            url: "pages/supplier_order_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                key: key,
                quantity: quantity,
                modifyquantity: 'modifyquantity',
                deductquantity: 'deductquantity'
            },
            success: function(data) {
                loadOrderContents();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    function delete_item(element) {
        var key = $(element).data('key');
        $.ajax({
            url: "pages/supplier_order_ajax.php",
            data: {
                key: key,
                deleteitem: 'deleteitem'
            },
            type: "POST",
            success: function(data) {
                loadOrderContents();
            },
            error: function() {}
        });
    }

    function updateColor(element){
        var color = $(element).val();
        var id = $(element).data('id');
        var key = $(element).data('key');
        $.ajax({
            url: 'pages/supplier_order_ajax.php',
            type: 'POST',
            data: {
                color_id: color,
                key: key,
                id: id,
                set_color: "set_color"
            },
            success: function(response) {
                loadOrderContents();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    $(document).ready(function() {
        

        var selectedCategory = '';

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

        $(".select2").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            });
        });

        $(document).on('change', '#product_category', function() {
            updateSearchCategory();
        });

        $(document).on('change', '#supplier_id, #order_supplier_id', function() {
            var supplier_id = $(this).val();
            $.ajax({
                url: 'pages/supplier_order_ajax.php',
                type: 'POST',
                data: {
                    supplier_id: supplier_id,
                    change_supplier: "change_supplier"
                },
                success: function(response) {

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#view_order', function(event) {
            $('.modal').modal('hide');
            loadOrderContents();
            $('#order_modal').modal('show');
        });

        $(document).on('click', '#save_order', function(event) {
            if (!confirm("Save this Order for future use?")) {
                return;
            }
            $.ajax({
                url: 'pages/supplier_order_ajax.php',
                type: 'POST',
                data: {
                    save_order: 'save_order'
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        alert("Order successfully saved.");
                    } else if (response.error) {
                        alert("Error: " + response.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Response Text: ' + jqXHR.responseText);
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#order_products', function(event) {
            if (!confirm("Order the products in cart?")) {
                return;
            }
            $.ajax({
                url: 'pages/supplier_order_ajax.php',
                type: 'POST',
                data: {
                    order_supplier_products: 'order_supplier_products'
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        alert("Order to Supplier successfully submitted.");
                    } else if (response.error) {
                        alert("Error: " + response.error);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('Response Text: ' + jqXHR.responseText);
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '.btn-minus', function () {
            var product_id = $(this).data('id');
            var input = $('#qty' + product_id);
            var currentValue = parseInt(input.val(), 10) || 0;
            var minValue = parseInt(input.attr('min')) || 1;
            if (currentValue > minValue) {
                input.val(currentValue - 1).trigger('change');
            }
        });

        $(document).on('click', '.btn-plus', function () {
            var product_id = $(this).data('id');
            var input = $('#qty' + product_id);
            var currentValue = parseInt(input.val(), 10) || 0;
            input.val(currentValue + 1).trigger('change');
        });

        $(document).on('click', '#add-to-cart-btn', function() {
            var product_id = $(this).data('id');
            var qty = parseInt($('#qty' + product_id).val(), 10) || 0;
            var color = parseInt($('#select_color_' + product_id).val(), 10) || 0;

            $.ajax({
                url: "pages/supplier_order_ajax.php",
                type: "POST",
                data: {
                    product_id: product_id,
                    qty: qty,
                    color: color,
                    addquantity: 'addquantity',
                    modifyquantity: 'modifyquantity'
                },
                success: function(data) {
                    $('#qty' + product_id).val(1);

                    if ($('#alert-container').length === 0) {
                        $('body').append(`
                            <div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050; max-width: 300px;">
                            </div>
                        `);
                    }

                    var alertId = 'alert-' + Date.now();
                    var alertHtml = `
                        <div id="${alertId}" class="alert alert-success alert-dismissible fade show small mb-2" role="alert">
                            <strong>Success!</strong> Item added to cart.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
                    $('#alert-container').append(alertHtml);

                    setTimeout(function() {
                        $('#' + alertId).alert('close');
                    }, 5000);

                    updateCartCounter();
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                }
            });
        });
  
        function updateSearchCategory() {
            var product_category = $('#product_category').val() || '';
            var product_id = $('#product_id').val() || '';
            var filename = $('#product_category option:selected').data('filename') || '';

            if(filename != ''){
                $.ajax({
                    url: 'pages/' +filename,
                    type: 'POST',
                    data: {
                        id: product_id,
                        action: "fetch_product_modal"
                    },
                    success: function(response) {
                        $('#add-fields').html(response);

                        let selectedCategory = $('#product_category').val() || '';
                        //this hides select options that are not the selected category
                        $('.add-category option').each(function() {
                            let match = String($(this).data('category')) === String(product_category);
                            $(this).toggle(match);
                        });
                        
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
                        
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('Error: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            }else{
                $('#add-fields').html('');
            }
            
        }
        
        $(document).on('mousedown', '.readonly', function() {
            e.preventDefault();
        });

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

        updateCartCounter();
    });
</script>



