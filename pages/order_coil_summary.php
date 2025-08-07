<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
session_start();
require 'includes/dbconn.php';
require 'includes/functions.php';

$trim_id = 43;
$panel_id = 46;
?>
<div class="product-list pt-4">
    <div class="card">
        <div class="card-body text-left p-3">
            <div class="row mb-9">
                <div class="col-12 text-left">
                    <h3 class="modal-title">Order Summary</h3>
                </div>
                <div class="col-6">
                    <div id="select_supplier_section">
                        <?php 
                            if (!empty($_SESSION["supplier_id"])) {
                        ?>
                            <div class="form-group">
                                <label>Supplier: <?= getSupplierName($_SESSION["supplier_id"]); ?></label>
                                <button class="btn ripple btn-primary py-0" type="button" id="supplier_change"><i class="fe fe-reload"></i> Change</button>                                       
                            </div>
                        <?php } else { ?>
                            <div class="input-group">
                                <input class="form-control" placeholder="Search Supplier" type="text" id="supplier_select">
                                <a class="input-group-text rounded-right m-0 p-0" href="/?page=product_supplier" target="_blank">
                                    <span class="input-group-text"> + </span>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                    <input type='hidden' id='supplier_select_id' name="supplier_select_id"/>
                </div>
                <div class="col-6">
                    <div class="form-group text-right">
                        <a href="?page=order_coil">
                            <button class="btn ripple btn-primary py-1" type="button">Add</button>
                        </a>
                        <a href="print_order_coil.php" target="_blank">  
                            <button class="btn ripple btn-secondary py-1" type="button">Print</button>                                    
                        </a>
                    </div>
                </div>
                <div class="pt-0" id="order-tbl"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function loadOrderContents(){
        $.ajax({
            url: 'pages/order_coil_summary_ajax.php',
            type: 'POST',
            data: {
                fetch_orders: "fetch_orders"
            },
            success: function(response) {
                $('#order-tbl').html(response);

                var table = $('#orderTable').DataTable({
                    language: {
                        emptyTable: "No products added to orders"
                    },
                    paging: false,
                    searching: false,
                    info: false,
                    ordering: false,
                    responsive: true
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function addToOrders(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');

        $.ajax({
            url: "pages/order_coil_summary_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
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

    function addToOrdersCoil(element) {
        var coil_id = $(element).data('id');

        $.ajax({
            url: "pages/order_coil_summary_ajax.php",
            type: "POST",
            data: {
                coil_id: coil_id,
                add_order_coil: 'add_order_coil'
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

    function updatequantity(element) {
        var product_id = $(element).data('id');
        var line = $(element).data('line');
        var type = $(element).data('type');
        var qty = $(element).val();
        $.ajax({
            url: "pages/order_coil_summary_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                type: type,
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
        var line = $(element).data('line');
        var type = $(element).data('type');
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var quantity = Number(input_quantity.val());
        $.ajax({
            url: "pages/order_coil_summary_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                type: type,
                quantity: quantity,
                modifyquantity: 'modifyquantity',
                addquantity: 'addquantity'
            },
            success: function(data) {
                console.log(data);
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
        var line = $(element).data('line');
        var type = $(element).data('type');
        var input_quantity = $('input[data-id="' + product_id + '"]');
        var quantity = Number(input_quantity.val());
        $.ajax({
            url: "pages/order_coil_summary_ajax.php",
            type: "POST",
            data: {
                product_id: product_id,
                line: line,
                type: type,
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
        var id = $(element).data('id');
        var line = $(element).data('line');
        var type = $(element).data('type');
        $.ajax({
            url: "pages/order_coil_summary_ajax.php",
            data: {
                product_id_del: id,
                line: line,
                type: type,
                deleteitem: 'deleteitem'
            },
            type: "POST",
            success: function(data) {
                loadOrderContents();
            },
            error: function() {}
        });
    }

    $("#supplier_select").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/order_coil_summary_ajax.php",
                type: 'post',
                dataType: "json",
                data: {
                    search_supplier: request.term
                },
                success: function(data) {
                    response(data);
                },
                error: function(xhr, status, error) {
                    console.log("Error: " + xhr.responseText);
                }
            });
        },
        select: function(event, ui) {
            $('#supplier_select').val(ui.item.label);
            $('#supplier_select_id').val(ui.item.value);
            return false;
        },
        focus: function(event, ui) {
            $('#supplier_select').val(ui.item.label);
            return false;
        }
    }); 

    $(document).ready(function() {
        loadOrderContents();
        $(document).on('change', '#supplier_select', function(event) {
            var supplier_id = $('#supplier_select_id').val();
            console.log(supplier_id)
            $.ajax({
                url: 'pages/order_coil_summary_ajax.php',
                type: 'POST',
                data: {
                    supplier_id: supplier_id,
                    change_supplier: "change_supplier"
                },
                success: function(response) {
                    if (response.trim() == 'success') {
                        location.reload();
                    }
                    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#supplier_change', function(event) {
            $.ajax({
                url: 'pages/order_coil_summary_ajax.php',
                type: 'POST',
                data: {
                    unset_supplier: "unset_supplier"
                },
                success: function(response) {
                    location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });
    });
</script>
