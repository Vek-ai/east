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

$supplier_id = $_REQUEST['supplier_id'] ?? 0;
$supplier_id = intval($supplier_id);
$supplier_name = getSupplierName($supplier_id);

$page_title = "$supplier_name Orders";
$permission = $_SESSION['permission'];
if (!empty($supplier_id)) {
    loadSupplierOrders($supplier_id);
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
            <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title ?></h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=supplier_pending_orders">Supplier Orders
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page"><?= $page_title ?></li>
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
    <?php                                                    
    if ($permission === 'edit') {
    ?> 
    <div class="card card-body">
        <div class="row">
            <div class="col-md-12 mt-3 mt-md-0 d-flex align-items-center justify-content-end gap-4">
                <a href="#" id="view_order" class="cart-icon text-decoration-none position-relative d-inline-flex">
                    <iconify-icon icon="ic:round-shopping-cart" class="cart-icon fs-8"></iconify-icon>
                    <span id="cartCounter" class="cart-badge">0</span>
                </a>
            </div>
        </div>
    </div>
    <?php                                                    
    }
    ?> 

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
                <div class="modal-footer d-flex justify-content-end">
                    <div>
                        <button class="btn ripple fw-bold text-white me-2" type="button" id="save_edit_order" 
                            style="background-color: #28A745; border-color: #218838;" data-supplier-id="<?= $supplier_id ?>">
                            <i class="fas fa-shopping-cart" style="color: #D4EDDA;"></i> Save Edited Order
                        </button>
                        <button class="btn ripple fw-bold text-white" data-bs-dismiss="modal" type="button" 
                            style="background-color: #DC3545; border-color: #C82333;">
                            <i class="fas fa-times" style="color: #F8D7DA;"></i> Close
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal" id="view_order_list_modal">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Saved Orders List</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="orders-saved-tbl">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="view_order_details_modal" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Order Details</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="order-details">
                    </div>
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
                        <select class="form-control py-0 ps-5 select2 filter-selection" id="select-category" data-filter="category" data-filter-name="Product Category">
                            <option value="" data-category="">All Categories</option>
                            <optgroup label="Category">
                                <?php
                                $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                $result_category = mysqli_query($conn, $query_category);
                                while ($row_category = mysqli_fetch_array($result_category)) {
                                ?>
                                    <option value="<?= $row_category['product_category'] ?>" data-category="<?= $row_category['product_category'] ?>" <?= $selected ?>><?= $row_category['product_category'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-system" data-filter="system" data-filter-name="Product System">
                            <option value="" data-category="">All Product Systems</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_system = "SELECT * FROM product_system WHERE hidden = '0' AND status = '1' ORDER BY `product_system` ASC";
                                $result_system = mysqli_query($conn, $query_system);
                                while ($row_system = mysqli_fetch_array($result_system)) {
                                ?>
                                    <option value="<?= $row_system['product_system'] ?>" data-category="<?= $row_system['product_category'] ?>" <?= $selected ?>><?= $row_system['product_system'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-line" data-filter="line" data-filter-name="Product Line">
                            <option value="" data-category="">All Product Lines</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_line = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                                $result_line = mysqli_query($conn, $query_line);
                                while ($row_line = mysqli_fetch_array($result_line)) {
                                ?>
                                    <option value="<?= $row_line['product_line'] ?>" data-category="<?= $row_line['product_category'] ?>" <?= $selected ?>><?= $row_line['product_line'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-type" data-filter="type" data-filter-name="Product Type">
                            <option value="" data-category="">All Product Types</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                                $result_type = mysqli_query($conn, $query_type);
                                while ($row_type = mysqli_fetch_array($result_type)) {
                                ?>
                                    <option value="<?= $row_type['product_type'] ?>" data-category="<?= $row_type['product_category'] ?>" <?= $selected ?>><?= $row_type['product_type'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-profile" data-filter="profile" data-filter-name="Product Profile">
                            <option value="" data-category="">All Profile Types</option>
                            <optgroup label="Product Line">
                                <?php
                                $query_profile = "SELECT * FROM profile_type WHERE hidden = '0' AND status = '1' ORDER BY `profile_type` ASC";
                                $result_profile = mysqli_query($conn, $query_profile);
                                while ($row_profile = mysqli_fetch_array($result_profile)) {
                                ?>
                                    <option value="<?= $row_profile['profile_type'] ?>" data-category="<?= $v['product_category'] ?>" <?= $selected ?>><?= $row_profile['profile_type'] ?></option>
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
                                    <option value="<?= $row_color['color_name'] ?>" data-category="category" <?= $selected ?>><?= $row_color['color_name'] ?></option>
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
                                    <option value="<?= $row_grade['product_grade'] ?>" data-category="grade" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
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
                                    <option value="<?= $row_gauge['product_gauge'] ?>" data-category="gauge" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
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
                                        $picture_path = '../'.$row_product['main_image'];
                                    }else{
                                        $picture_path = "../images/product/product.jpg";
                                    }
                
                                ?>
                                    <!-- start row -->
                                    <tr class="search-items" 
                                        data-system="<?= getProductSystemName($row_product['product_system']) ?>"
                                        data-line="<?= getProductLineName($row_product['product_line']) ?>"
                                        data-profile="<?= getProfileTypeName($row_product['profile']) ?>"
                                        data-color="<?= getColorName($row_product['color']) ?>"
                                        data-grade="<?= getGradeName($row_product['grade']) ?>"
                                        data-gauge="<?= getGaugeName($row_product['gauge']) ?>"
                                        data-category="<?= getProductCategoryName($row_product['product_category']) ?>"
                                        data-type="<?= getProductTypeName($row_product['product_type']) ?>"
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
                                            <select class="form-control search-chat py-0 ps-5 select2_color" id="select_color_<?= $row_product['product_id'] ?>" data-id="<?= $row_product['product_id'] ?>">
                                                <option value="" data-category="">All Colors</option>
                                                <optgroup label="Product Colors">
                                                    <?php
                                                    $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                                    $result_color = mysqli_query($conn, $query_color);
                                                    while ($row_color = mysqli_fetch_array($result_color)) {
                                                        $selected = ($color_id == $row_color['color_id']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_color['color_id'] ?>" data-color="<?= $row_color['color_code'] ?>" <?= $selected ?>><?= $row_color['color_name'] ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </optgroup>
                                            </select>
                                        </td>
                                        <td>
                                            <?php                                                    
                                            if ($permission === 'edit') {
                                            ?> 
                                            <div class="input-group input-group-sm">
                                                <button class="btn btn-outline-primary btn-minus" type="button" data-id="<?= $row_product['product_id'] ?>">-</button>
                                                <input class="form-control p-1 text-center" type="number" id="qty<?= $row_product['product_id'] ?>" value="1" min="1">
                                                <button class="btn btn-outline-primary btn-plus" type="button" data-id="<?= $row_product['product_id'] ?>">+</button>
                                            </div>
                                            <?php                                                    
                                            }
                                            ?> 
                                        </td>
                                        <td>
                                            <?php                                                    
                                            if ($permission === 'edit') {
                                            ?> 
                                            <button class="btn btn-sm btn-primary btn-add-to-cart" type="button" data-id="<?= $row_product['product_id'] ?>" id="add-to-cart-btn">Add to Order</button>
                                            <?php                                                    
                                            }
                                            ?> 
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
            url: 'pages/supplier_edit_orders_ajax.php',
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
            url: 'pages/supplier_edit_orders_ajax.php',
            type: 'POST',
            data: {
                fetch_order: "fetch_order"
            },
            success: function(response) {
                $('#order-tbl').html('');
                $('#order-tbl').html(response);

                updateCartCounter();
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
            url: "pages/supplier_edit_orders_ajax.php",
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
            url: "pages/supplier_edit_orders_ajax.php",
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
            url: "pages/supplier_edit_orders_ajax.php",
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
            url: "pages/supplier_edit_orders_ajax.php",
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
            url: 'pages/supplier_edit_orders_ajax.php',
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

    function loadOrderList(){
        $.ajax({
            url: 'pages/supplier_edit_orders_ajax.php',
            type: 'POST',
            data: {
                fetch_order_saved: "fetch_order_saved"
            },
            success: function(response) {
                $('#orders-saved-tbl').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadOrderDetails(orderid){
        $.ajax({
            url: 'pages/supplier_edit_orders_ajax.php',
            type: 'POST',
            data: {
                orderid: orderid,
                fetch_order_details: "fetch_order_details"
            },
            success: function(response) {
                $('#order-details').html(response);
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

        $(".select2").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            });
        });

        $(".select2_color").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent(),
                templateResult: formatOption,
                templateSelection: formatOption,
                escapeMarkup: function(markup) { return markup; }
            });
        });

        $(document).on('change', '#product_category', function() {
            updateSearchCategory();
        });

        $(document).on('click', '#view_order', function(event) {
            $('.modal').modal('hide');
            loadOrderContents();
            $('#order_modal').modal('show');
        });

        $(document).on('click', '#save_edit_order', function (e) {
            e.preventDefault();

            let supplierId = $(this).data('supplier-id');

            $.ajax({
                url: 'pages/supplier_edit_orders_ajax.php',
                type: 'POST',
                data: {
                    save_edit_order: 'save_edit_order',
                    supplier_id: supplierId
                },
                success: function (response) {
                    console.log("Raw response:", response);
                    let res = JSON.parse(response);
                    if (res.success) {
                        alert('Order updated successfully!');
                        location.reload();
                    } else {
                        alert('Update failed: ' + (res.error || 'Unknown error'));
                    }
                },
                error: function () {
                    alert('AJAX request failed.');
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
                url: "pages/supplier_edit_orders_ajax.php",
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
                    var filterValue = $(this).val()?.toString().toLowerCase() || '';
                    var rowValue = row.data($(this).data('filter'))?.toString().toLowerCase() || '';

                    if (filterValue && filterValue !== '/') {
                        if (!rowValue.includes(filterValue)) {
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

        $(document).on('click', '.reset_filters', function () {
            $('.filter-selection').each(function () {
                $(this).val(null).trigger('change.select2');
            });

            $('#text-srh').val('');

            filterTable();
        });

        updateCartCounter();
    });
</script>

<?php
} else {
    echo "<h4 class='mt-3 text-center'>No orders found for supplier</h4>";
}
?>



