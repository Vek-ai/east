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

$trim_id = 43;
$panel_id = 46;
?>


<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div><br>
        <h4 class="font-weight-medium fs-14 mb-0">Merge Customers</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Customer
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Merge Customers</li>
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
<div class="col-12">
  <div class="card card-body">
    <div class="row">
      <div class="col-3">
        <h4 class="card-title">Merge Customers</h4>
      </div>
    </div>

    <div class="row pt-3 text-center">
        <div class="col-md-5">
            <label class="form-label">Customer account to be removed</label>
            <div class="mb-3">
                <select id="customer_merge" class="form-control select2-merge" name="customer_merge">
                    <option value=""></option>
                    <?php
                    $query_merge_customers = "SELECT * FROM customer WHERE status != '3' AND status != '0' AND hidden='0'";
                    $result_merge_customers = mysqli_query($conn, $query_merge_customers);            
                    while ($row_merge_customers = mysqli_fetch_array($result_merge_customers)) {
                    ?>
                        <option value="<?= $row_merge_customers['customer_id'] ?>" ><?= get_customer_name($row_merge_customers['customer_id']) ?></option>
                    <?php   
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-5">
            <label class="form-label">Customer account to keep</label>
            <div class="mb-3">
                <select id="customer_original" class="form-control select2-original" name="customer_original">
                    <option value=""></option>
                    <?php
                    $query_original_customers = "SELECT * FROM customer WHERE status != '3' AND status != '0' AND hidden='0'";
                    $result_original_customers = mysqli_query($conn, $query_original_customers);            
                    while ($row_original_customers = mysqli_fetch_array($result_original_customers)) {
                    ?>
                        <option value="<?= $row_original_customers['customer_id'] ?>" ><?= get_customer_name($row_original_customers['customer_id']) ?></option>
                    <?php   
                    }
                    ?>
                </select>
            </div>
        </div>
        
        <div class="col-md-2 d-flex align-items-center justify-content-center">
            <button class="btn btn-primary w-100" type="button" id="mergeCustomers">
                <i class="fe fe-hard-drive"></i> Merge
            </button>
        </div>
    </div>
  </div>
</div>


<script>
    $(document).ready(function() {
        $(".select2-original").select2({
            width: '100%',
            placeholder: "Select account to keep...",
            allowClear: true
        });

        $(".select2-merge").select2({
            width: '100%',
            placeholder: "Select account to be removed...",
            allowClear: true
        });

        $('#mergeCustomers').click(function() {
            var customer_original = $('#customer_original').val();
            var customer_merge = $('#customer_merge').val();
            
            if (confirm("Are you sure you want to merge these customer details?")) {
                $.ajax({
                    url: "pages/merge_customer_ajax.php",
                    type: "POST",
                    data: {
                        customer_original: customer_original,
                        customer_merge: customer_merge,
                        merge: 'merge'
                    },
                    success: function(data) {
                        if (data.trim() === 'success') {
                            alert('Successfully merged!');
                            location.reload();
                        } else {
                            console.log(data);
                        }
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
        });
    });


</script>