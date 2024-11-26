<?php
require 'includes/dbconn.php';
require 'includes/functions.php';
?>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Order/Estimate List</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Sales
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Order/Estimate List</li>
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
                    <div class="form-group">
                        <label for="job_po_search" class="form-label">Job PO #</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                            <input class="form-control" placeholder="All Job PO" type="text" id="job_po_search">
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="job_order_search" class="form-label">Job Order Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                            <input class="form-control" placeholder="All Job Orders" type="text" id="job_order_search">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group date">
                        <label for="date_from" class="form-label">Start Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input class="form-control datepicker-autoclose" type="date" id="date_from">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
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
                        View Orders/Estimates
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="datatables">
                    <div id="tbl-orders-estimates" class="product-details table-responsive text-nowrap"></div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" id="view_order_estimate_details_modal" style="background-color: rgba(0, 0, 0, 0.5);"></div>

<div class="modal fade" id="view_deliver_img_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myLargeModalLabel">
                    View Delivery Image
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="update_product" class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div id="deliver_img" class="card-body">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $("#customer_search").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/order_estimate_list_ajax.php",
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
        });

        $('[data-toggle="tooltip"]').tooltip();
        
        function performSearch() {
            var customer_name = $('#customer_search').val();
            var job_po = $('#job_po_search').val();
            var job_order = $('#job_order_search').val();
            var date_from = $('#date_from').val();
            var date_to = $('#date_to').val();

            $.ajax({
                url: 'pages/order_estimate_list_ajax.php',
                type: 'POST',
                data: {
                    customer_name: customer_name,
                    job_po: job_po,
                    job_order: job_order,
                    date_from: date_from,
                    date_to: date_to,
                    search_order_estimate: 'search_order_estimate'
                },
                success: function(response) {
                    $('#tbl-orders-estimates').html(response);
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
                url: 'pages/order_estimate_list_ajax.php',
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

        $(document).on('click', '#view_deliver_img_btn', function(event) {
            var id = $(this).data('id');
            $.ajax({
                url: 'pages/order_estimate_list_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_delivery_image: 'fetch_delivery_image'
                },
                success: function(response) {
                    $('#deliver_img').html(response);
                    $('#view_deliver_img_modal').modal('toggle');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
            
        });
    });
</script>