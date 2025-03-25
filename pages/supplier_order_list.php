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

    <div class="widget-content searchable-container list">

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
    $sql_orders = "SELECT * FROM supplier_temp_orders WHERE supplier_id = '$supplier_id'";
    $result_orders = $conn->query($sql_orders);

    if ($result_orders->num_rows > 0) {
        while ($row_order = $result_orders->fetch_assoc()) {
            $supplier_temp_order_id = $row_order['supplier_temp_order_id'];
    ?>
            <div class="card card-body">
                <div class="row">
                    <div class="col-12">
                        <h3 class="card-title mb-2">
                            Saved Order #<?= $supplier_temp_order_id ?> - Total: $<?= number_format($row_order['total_price'], 2) ?>
                        </h3>

                        <?php
                        $sql_products = "SELECT * FROM supplier_temp_prod_orders WHERE supplier_temp_order_id = '$supplier_temp_order_id'";
                        $result_products = $conn->query($sql_products);
                        ?>

                        <div class="datatables">
                            <div class="table-responsive">
                                <table id="productList" class="table table-sm search-table align-middle text-wrap">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Color</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
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
                                                <td>$<?= number_format($row_product['price'], 2); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <?php
        }
    } else {
        echo "<p>No orders found for this supplier.</p>";
    }
    ?>

    </div>
</div>

<script src="includes/pricing_data.js"></script>

<script>
    function updateCartCounter() {
        $.ajax({
            url: 'pages/supplier_order_list_ajax.php',
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
            url: 'pages/supplier_order_list_ajax.php',
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
            url: "pages/supplier_order_list_ajax.php",
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
            url: "pages/supplier_order_list_ajax.php",
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
            url: "pages/supplier_order_list_ajax.php",
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
        var id = $(element).data('id');
        var key = $(element).data('key');
        $.ajax({
            url: 'pages/supplier_order_list_ajax.php',
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
            url: 'pages/supplier_order_list_ajax.php',
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
            url: 'pages/supplier_order_list_ajax.php',
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

        $('#toggleActive').on('change', filterTable);

        $('#toggleActive').trigger('change');

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

        $(document).on('change', '#supplier_id, #order_supplier_id', function() {
            var supplier_id = $(this).val();
            $.ajax({
                url: 'pages/supplier_order_list_ajax.php',
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
                url: 'pages/supplier_order_list_ajax.php',
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

        $(document).on('click', '#edit_saved_order', function(event) {
            if (!confirm("Load and edit this saved order?")) {
                return;
            }

            var orderid = $(this).data('id');
            $.ajax({
                url: 'pages/supplier_order_list_ajax.php',
                type: 'POST',
                data: {
                    orderid: orderid,
                    load_saved_order: 'load_saved_order'
                },
                success: function(response) {
                    if (response.success) {
                        alert("Order successfully loaded.");
                        $('.modal').modal('hide');
                        updateCartCounter();
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
                url: 'pages/supplier_order_list_ajax.php',
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

        $(document).on('click', '#view_order_list', function(event) {
            loadOrderList();
            $('#view_order_list_modal').modal('show');
        });

        $(document).on('click', '#view_order_details', function(event) {
            let orderId = $(this).data('id');
            loadOrderDetails(orderId);
            $('#view_order_details_modal').modal('show');
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
                url: "pages/supplier_order_list_ajax.php",
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



