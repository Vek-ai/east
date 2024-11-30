<?php
require 'includes/dbconn.php';
require 'includes/functions.php';
?>

<style>
    #product_order_dtls_tbl td, #product_order_dtls_tbl th {
            white-space: normal !important;
            word-wrap: break-word;
    }

    .modal-xxl {
        width: 90% !important;
        max-width: 90% !important;
    }

    .datepicker-autoclose {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        padding-right: 0;
    }

    .datepicker-autoclose::-webkit-calendar-picker-indicator {
        display: none;
    }
</style>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Product Orders</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Sales
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Product Orders</li>
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
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="product_search" class="form-label">Product Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input class="form-control" placeholder="All Products" type="text" id="product_search">
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group date">
                        <label for="date_from" class="form-label">Start Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input class="form-control datepicker-autoclose" type="date" id="date_from">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group date">
                        <label for="date_to" class="form-label">End Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input class="form-control datepicker-autoclose" type="date" id="date_to">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn-primary" id="btn-view-product-orders">
                        View Product Orders
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="datatables">
                    <div id="tbl-product-orders" class="product-details table-responsive text-nowrap"></div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" id="view_product_order_details_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myLargeModalLabel">
                    View Product Order Details
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div id="product_orders_details" class="card-body">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $("#product_search").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/product_order_list_ajax.php",
                type: 'post',
                dataType: "json",
                data: {
                    search_customer: request.term
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
            $('#product_search').val(ui.item.label);
            return false;
        },
        focus: function(event, ui) {
            $('#product_search').val(ui.item.label);
            return false;
        },
        minLength: 0
    });

    $(document).ready(function() {
        $(".datepicker-autoclose").datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "yyyy-mm-dd",
            container: "body",
            orientation: "bottom left"
        });

        $('[data-toggle="tooltip"]').tooltip();
        
        function performSearch() {
            var product_search = $('#product_search').val();
            var date_from = $('#date_from').val();
            var date_to = $('#date_to').val();

            $.ajax({
                url: 'pages/product_order_list_ajax.php',
                type: 'POST',
                data: {
                    product_search: product_search,
                    date_from: date_from,
                    date_to: date_to,
                    search_product_orders: 'search_product_orders'
                },
                success: function(response) {
                    $('#tbl-product-orders').html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }

        $('#btn-view-product-orders').on('click', function() {
            performSearch();
        });

        $(document).on('click', '#view_details', function(event) {
            var id = $(this).data('id');
            var date_from = $('#date_from').val();
            var date_to = $('#date_to').val();
            
            $.ajax({
                url: 'pages/product_order_list_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    date_from: date_from,
                    date_to: date_to,
                    fetch_product_orders: 'fetch_product_orders'
                },
                success: function(response) {
                    $('#product_orders_details').html(response);
                    $('#view_product_order_details_modal').modal('toggle');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
            
        });
    });
</script>