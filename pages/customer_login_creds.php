<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$generate_rend_upc = generateRandomUPC();
$picture_path = "images/product/product.jpg";

if($_REQUEST['id']){
    $customer_id = $_REQUEST['id'];
    $customer_details = getCustomerDetails($customer_id);
}
?>


<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"><?php
            if(isset($customer_details)){
                echo "Customer " .$customer_details['customer_first_name'] .' ' .$customer_details['customer_last_name'];
            }
            ?> Login Credentials</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=customer">Customer
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Login Credentials</li>
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
        <form id="credentialsForm" class="form-horizontal">
            <input type="hidden" name="customer_id" value="<?= $customer_id ?>" />
            <h5 class="card-header">Account Credentials</h5>
            <div class="card-body pt-2">
                <div class="row">
                <div class="mb-3 col-md-6 form-password-toggle">
                    <label class="form-label" for="username">Username</label>
                    <input
                        class="form-control"
                        type="text"
                        name="username"
                        id="username"
                        placeholder="Enter Username" 
                        value="<?= $customer_details['username']  ?>"
                        />
                </div>
                <div class="mb-3 col-md-6 form-password-toggle">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group input-group-merge">
                    <input
                        class="form-control"
                        type="password"
                        name="password"
                        id="password"
                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                    <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                    </div>
                </div>
                </div>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary" style="border-radius: 10%;">Save</button>
            </div>
        </form>
    </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.input-group-text').on('click', function () {
            const $input = $(this).siblings('input');
            const $icon = $(this).find('i');
            
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $icon.removeClass('ti-eye-off').addClass('ti-eye');
            } else {
                $input.attr('type', 'password');
                $icon.removeClass('ti-eye').addClass('ti-eye-off');
            }
        });

        $('#credentialsForm').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('action', 'update_credentials');
            $.ajax({
                url: 'pages/customer_login_creds_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    try {
                        var jsonResponse = JSON.parse(response);
                        if (jsonResponse.success) {
                            $('#responseHeader').text("Success");
                            $('#responseMsg').text(jsonResponse.message);
                            $('#responseHeaderContainer').removeClass("bg-danger");
                            $('#responseHeaderContainer').addClass("bg-success");
                            $('#response-modal').modal("show");
                            $('#response-modal').on('hide.bs.modal', function () {
                                location.reload();
                            });
                        } else {
                            $('#responseHeader').text("Failed");
                            $('#responseMsg').text('Update failed: ' + jsonResponse.message);
                            $('#responseHeaderContainer').removeClass("bg-success");
                            $('#responseHeaderContainer').addClass("bg-danger");
                            $('#response-modal').modal("show");
                        }
                    } catch (e) {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text('An error occurred while processing the response: ' + e.message);
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }
                },
                error: function (xhr, status, error) {
                    alert('An error occurred: ' + xhr.responseText);
                }
            });
        });
    });
</script>