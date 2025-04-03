<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

$supplier_id = '';

if(!empty($_REQUEST['id'])){
    $supplier_id = $_REQUEST['id'];
    $supplier_details = getSupplierDetails($supplier_id);
}

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
            <h4 class="font-weight-medium fs-14 mb-0"><?= getSupplierName($supplier_id) ?> Orders</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page"><?= getSupplierName($supplier_id) ?> Orders</li>
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

    <div class="widget-content searchable-container list" id="pageBody">

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

    <?php
    $sql_products = "SELECT * FROM supplier_temp_prod_orders WHERE supplier_id = '$supplier_id'";
    $result_products = $conn->query($sql_products);
    if ($result_products->num_rows > 0) {
    ?>
            <div class="card card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="datatables">
                            <div class="table-responsive">
                                <table id="productList" class="table table-sm search-table align-middle text-wrap">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Color</th>
                                            <th>Quantity</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row_product = $result_products->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?= getProductName($row_product['product_id']); ?></td>
                                                <td>
                                                    <span class="d-flex align-items-center small">
                                                        <span class="rounded-circle d-block p-1 me-2" 
                                                            style="background-color: <?= getColorHexFromColorID($row_product['color']); ?>; 
                                                                    width: 25px; height: 25px;">
                                                        </span>
                                                        <?= !empty($row_product['color']) ? getColorName($row_product['color']) : ''; ?>
                                                    </span>
                                                </td>
                                                <td><?= $row_product['quantity']; ?></td>
                                                <td class="text-right">$<?= number_format($row_product['price'], 2); ?></td>
                                                <td class="text-right">$<?= number_format($row_product['price'] * $row_product['quantity'], 2); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="#" class="btn btn-sm" id="place_order_products" style="background-color: #28a745; color: #fff; border: none;">
                                <i class="fas fa-shopping-cart me-1"></i> Order
                            </a>
                            <a href="#" class="btn btn-sm" id="editOrderBtn" style="background-color: #ffc107; color: #fff; border: none;" data-id="<?= $supplier_temp_order_id ?>">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
    <?php
    } else {
        echo "<p>No orders found for this supplier.</p>";
    }
    ?>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="add-header">
                    Edit Order
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="productSystemForm" class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                          <div id="edit-fields" class=""></div>
                          <div class="form-actions">
                              <div class="border-top">
                                  <div class="row mt-2">
                                      <div class="col-6 text-start"></div>
                                      <div class="col-6 text-end "></div>
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

<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true" data-backdrop="true" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="add-header">Add Product</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="datatables">
                            <div class="table-responsive">
                                <table id="productListAdd" class="table search-table align-middle text-wrap">
                                    <thead class="header-item">
                                        <tr>
                                            <th>Description</th>
                                            <th>Category</th>
                                            <th>Color</th>
                                            <th>Quantity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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

<script>
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
    var key = $(element).data('key');
    var quantity = $(element).val();
    $.ajax({
        url: "pages/supplier_order_list_ajax.php",
        type: "POST",
        data: {
            key: key,
            quantity: quantity,
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
    var key = $(element).data('key');
    $.ajax({
        url: "pages/supplier_order_list_ajax.php",
        type: "POST",
        data: {
            key: key,
            modifyquantity: 'modifyquantity',
            addquantity: 'addquantity'
        },
        success: function(data) {
            console.log(data)
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
    var key = $(element).data('key');
    $.ajax({
        url: "pages/supplier_order_list_ajax.php",
        type: "POST",
        data: {
            key: key,
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
        url: "pages/supplier_order_list_ajax.php",
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
    var key = $(element).data('key');
    $.ajax({
        url: 'pages/supplier_order_list_ajax.php',
        type: 'POST',
        data: {
            color_id: color,
            key: key,
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

function loadOrderContents(){
    var id = $('#editOrderBtn').data('id') || '';
    var supplier_id = <?= $supplier_id ?? '' ?>;

    $.ajax({
        url: 'pages/supplier_order_list_ajax.php',
        type: 'POST',
        data: {
            id : id,
            supplier_id: supplier_id,
            fetch_edit_modal: 'fetch_edit_modal'
        },
        success: function (response) {
            $('#edit-fields').html(response);
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
}

function showResponseModal(title, message, type) {
    let headerClass = "";
    let footerButtons = "";

    if (type === "success") {
        headerClass = "bg-success-subtle text-success";
        footerButtons = `<button type="button" class="btn bg-success text-white" data-bs-dismiss="modal">OK</button>`;
    } 
    else if (type === "error") {
        headerClass = "bg-danger-subtle text-danger";
        footerButtons = `<button type="button" class="btn bg-danger text-white" data-bs-dismiss="modal">Close</button>`;
    } 
    else if (type === "warning") {
        headerClass = "bg-warning-subtle text-warning";
        footerButtons = `<button type="button" class="btn bg-warning text-white" data-bs-dismiss="modal">Understood</button>`;
    } 
    else if (type === "confirm") {
        headerClass = "bg-primary-subtle text-primary";
        footerButtons = `
            <button type="button" class="btn bg-secondary-subtle text-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="confirm-action" class="btn bg-primary text-white">Yes, Confirm</button>
        `;
    } 
    else {
        headerClass = "bg-secondary-subtle text-secondary";
        footerButtons = `<button type="button" class="btn bg-secondary text-white" data-bs-dismiss="modal">Close</button>`;
    }

    $("#responseHeader").text(title);
    $("#responseMsg").text(message);
    $("#responseHeaderContainer").removeClass().addClass("modal-header align-items-center modal-colored-header " + headerClass);
    $("#modalFooter").html(footerButtons);
    
    $("#response-modal").modal("show");
}

$(document).ready(function() {
    document.title = "<?= getSupplierName($supplier_id) ?> Orders";
    var activeOrderEditing = '';
    var supplier_id = <?= $supplier_id ?? '' ?>;

    $(document).on('click', '#editOrderBtn', function(event) {
        activeOrderEditing = $(this).data('id') || '';
        event.preventDefault();
        loadOrderContents();
        $('#editModal').modal('show');
    });

    $(document).on('click', '#addProductModalBtn', function(event) {
        event.preventDefault();

        $.ajax({
            url: "pages/supplier_order_list_ajax.php",
            type: "POST",
            data: {
                supplier_id: supplier_id,
                fetch_products: 'fetch_products'
            },
            dataType: "json",
            success: function (response) {
                let tableBody = $("#productListAdd tbody");
                tableBody.empty();
                
                response.forEach((product, index) => {
                    let statusClass = product.status == "0" ? "alert-danger bg-danger" : "alert-success bg-success";
                    let statusText = product.status == "0" ? "Inactive" : "Active";
                    
                    let row = `
                        <tr class="search-items" 
                            data-system="${product.product_system}" 
                            data-line="${product.product_line}" 
                            data-profile="${product.profile}" 
                            data-color="${product.color}" 
                            data-grade="${product.grade}" 
                            data-gauge="${product.gauge}" 
                            data-category="${product.product_category}" 
                            data-type="${product.product_type}" 
                            data-active="${product.status == 1 ? 1 : 0}" 
                            data-instock="${product.total_quantity > 1 ? 1 : 0}">
                            
                            <td>
                                <a href="/?page=product_details&product_id=${product.product_id}">
                                    <div class="d-flex align-items-center">
                                        <img src="${product.main_image}" class="rounded-circle" width="56" height="56">
                                        <div class="ms-3">
                                            <h6 class="fw-semibold mb-0 fs-4">${product.product_item}</h6>
                                        </div>
                                    </div>
                                </a>
                            </td>
                            <td>${product.product_category}</td>
                            <td>
                                <select class="form-control search-chat py-0 ps-5 select2_color" id="select_color_${product.product_id}" data-id="${product.product_id}">
                                    <option value="" data-category="">All Colors</option>
                                </select>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-primary btn-minus" type="button" data-id="${product.product_id}">-</button>
                                    <input class="form-control p-1 text-center" type="number" id="qty${product.product_id}" value="1" min="1">
                                    <button class="btn btn-outline-primary btn-plus" type="button" data-id="${product.product_id}">+</button>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary btn-add-to-cart" type="button" data-id="${product.product_id}" id="add-to-cart-btn">Add to Order</button>
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                });
            },
            error: function (xhr, status, error) {
                console.error("Error loading products:", error);
                console.log("Response Text:", xhr.responseText);
            }
        });

        $('#addProductModal').modal('show');
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
        var quantity = parseInt($('#qty' + product_id).val(), 10) || 0;
        var color = parseInt($('#select_color_' + product_id).val(), 10) || 0;

        $.ajax({
            url: "pages/supplier_order_list_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                quantity: quantity,
                color: color,
                addToCart: 'addToCart'
            },
            success: function(data) {
                if ($('#alert-container').length === 0) {
                    $('body').append(`
                        <div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 2000; max-width: 300px;">
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
                }, 2000);

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
    });

    $(document).on('click', '#place_order_products', function(event) {
        if (!confirm("Are you sure you want to place this order to supplier?")) {
            return;
        }
        $.ajax({
            url: 'pages/supplier_order_list_ajax.php',
            type: 'POST',
            data: {
                supplier_id: supplier_id,
                order_supplier_products: 'order_supplier_products'
            },
            success: function(response) {
                if (response.success) {
                    let orderLink = `supplier/index.php?id=${response.supplier_order_id}&key=${response.key}`;
                    let message = `${response.message}.<br> 
                                LINK: <a href="${orderLink}" target="_blank">CLICK HERE TO ACCESS THE SUPPLIER LINK SENT TO EMAIL</a>`;

                    $("#responseHeader").text("Success");
                    $("#responseMsg").html(message);
                    $("#response-modal").modal("show");

                    $("#response-modal").off("hidden.bs.modal").on("hidden.bs.modal", function() {
                        window.open(`print_supplier_order.php?id=${response.supplier_order_id}`, "_blank");
                        location.reload();
                    });
                } else if (response.error) {
                    showResponseModal("Error", '', "error");
                    console.log("Error: " + response.error);
                    location.reload();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('Response Text: ' + jqXHR.responseText);
                showResponseModal("Error", '', "bg-danger-subtle text-danger");
                console.log("Error: " + textStatus + " - " + errorThrown);
            }
        });
    });

    $('#editModal').on('hidden.bs.modal', function () {
        $("#pageBody").load(location.href + " #pageBody > *");
    });
});
    
</script>




