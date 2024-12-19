<?php
require 'includes/dbconn.php';
require 'includes/functions.php';
?>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Status List</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="/">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Status List</li>
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
                        <label for="date_from" class="form-label">Date From</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="date_from">
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="date_to" class="form-label">Date To</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="date_to">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12 text-end">
                    <button type="button" class="btn btn-primary" id="btn-view-status">
                        View Status List
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="datatables">
                    <div id="tbl-status" class="product-details table-responsive text-nowrap">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal" id="view_status_details_modal" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 90vw;">
        <div class="modal-content p-2">
            <div class="modal-header">
                <h6 class="modal-title">View Details</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="status-details">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    var id = '';
    var type = '';

    function loadStatusDetails(){
        $.ajax({
            url: 'pages/status_list_ajax.php',
            type: 'POST',
            data: {
                id: id,
                type: type,
                fetch_status_details: "fetch_status_details"
            },
            success: function(response) {
                $('#status-details').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    $("#customer_search").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/status_list_ajax.php",
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
        $('[data-toggle="tooltip"]').tooltip();
        
        function performSearch(query) {
            var customer_name = $('#customer_search').val();
            var date_from = $('#date_from').val();
            var date_to = $('#date_to').val();

            $.ajax({
                url: 'pages/status_list_ajax.php',
                type: 'POST',
                data: {
                    customer_name: customer_name,
                    date_from: date_from,
                    date_to: date_to,
                    search_status: 'search_status'
                },
                success: function(response) {
                    $('#tbl-status').html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }

        $('#btn-view-status').on('click', function() {
            performSearch();
        });

        $(document).on('click', '#view_status_details', function(event) {
            id = $(this).data('id');
            type = $(this).data('type');
            loadStatusDetails();
            $('#view_status_details_modal').modal('toggle');
        });

        performSearch();
    });
</script>