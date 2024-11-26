<?php
require 'includes/dbconn.php';
require 'includes/functions.php';
?>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Custom Discounted Product List</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Sales
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Custom Discounted Product List</li>
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
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="customer_search" class="form-label">Customer Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input class="form-control" placeholder="All Customers" type="text" id="customer_search">
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group date">
                        <label for="date_from" class="form-label">Start Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input class="form-control datepicker-autoclose" type="date" id="date_from">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
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
                    <button type="button" class="btn btn-primary" id="btn-view-order_estimate">
                        View Products List
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="datatables">
                    <div id="tbl-products" class="product-details table-responsive text-nowrap"></div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" id="view_order_estimate_details_modal" style="background-color: rgba(0, 0, 0, 0.5);"></div>

<script>
    $("#customer_search").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/custom_discount_list_ajax.php",
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
            $('#customer_search').val(ui.item.label);
            return false;
        },
        focus: function(event, ui) {
            $('#customer_search').val(ui.item.label);
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
            var customer_name = $('#customer_search').val();
            var date_from = $('#date_from').val();
            var date_to = $('#date_to').val();

            $.ajax({
                url: 'pages/custom_discount_list_ajax.php',
                type: 'POST',
                data: {
                    customer_name: customer_name,
                    date_from: date_from,
                    date_to: date_to,
                    search_custom_discounted: 'search_custom_discounted'
                },
                success: function(response) {
                    $('#tbl-products').html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }

        $('#btn-view-order_estimate').on('click', function() {
            performSearch();
        });

        $(document).on('click', '#view_details', function(event) {
            var id = $(this).data('id');
            var type = $(this).data('type');
            var fetchType = '';

            if(type === 1){
                fetchType = 'fetch_estimate_details';
            }else if(type === 2){
                fetchType = 'fetch_order_details';
            }

            $.ajax({
                url: 'pages/custom_discount_list_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    fetchType: fetchType
                },
                success: function(response) {
                    $('#view_order_estimate_details_modal').html(response);
                    $('#view_order_estimate_details_modal').modal('toggle');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
            
        });
    });
</script>