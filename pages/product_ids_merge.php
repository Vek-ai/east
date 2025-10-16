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
        <h4 class="font-weight-medium fs-14 mb-0">Merge Products</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="?page=product_ids">Product IDs
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Merge Products</li>
          </ol>
        </nav>
      </div>
      <div>
      </div>
    </div>
  </div>
</div>
<div class="col-12">
  <div class="card card-body">
    <div class="row">
      <div class="col-3">
        <h4 class="card-title">Merge Products</h4>
      </div>
    </div>

    <div class="row pt-3">
        <div class="col-md-5">
            <label class="form-label">Product to be merged</label>
            <div class="mb-3">
                <select id="product_merge" class="form-control select2-merge" name="product_merge">
                    <option value=""></option>
                    <?php
                    $query_products = "SELECT * FROM product WHERE status != '0' AND hidden='0'";
                    $result_products = mysqli_query($conn, $query_products);            
                    while ($row_products = mysqli_fetch_array($result_products)) {
                    ?>
                        <option value="<?= $row_products['product_id'] ?>" ><?= $row_products['product_item'] ?></option>
                    <?php   
                    }
                    ?>
                </select>
            </div>
            <div id="merge_product_details" class="mt-3"></div>
        </div>
        <div class="col-md-5">
            <label class="form-label">Product to keep</label>
            <div class="mb-3">
                <select id="product_original" class="form-control select2-original" name="product_original">
                    <option value=""></option>
                    <?php
                    $query_products = "SELECT * FROM product WHERE status != '0' AND hidden='0'";
                    $result_products = mysqli_query($conn, $query_products);            
                    while ($row_products = mysqli_fetch_array($result_products)) {
                    ?>
                        <option value="<?= $row_products['product_id'] ?>" ><?= $row_products['product_item'] ?></option>
                    <?php   
                    }
                    ?>
                </select>
            </div>
            <div id="original_product_details" class="mt-3"></div>
        </div>
        
        <div class="col-md-2 d-flex align-items-start justify-content-center">
            <button class="btn btn-primary w-100 mt-4" type="button" id="mergeCustomers">
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
            placeholder: "Select product to keep...",
            allowClear: true
        });

        $(".select2-merge").select2({
            width: '100%',
            placeholder: "Select product to be merged...",
            allowClear: true
        });

        $('#product_merge').on('change', function() {
            var product_id = $(this).val();
            if (product_id) {
                $.ajax({
                    url: "pages/product_ids_merge_ajax.php",
                    type: "POST",
                    data: { 
                      product_id: product_id,
                      fetch_data: 'fetch_data'
                    },
                    success: function(data) {
                        $("#merge_product_details").html(data);
                    }
                });
            } else {
                $("#merge_product_details").html('');
            }
        });

        $('#product_original').on('change', function() {
            var product_id = $(this).val();
            if (product_id) {
                $.ajax({
                    url: "pages/product_ids_merge_ajax.php",
                    type: "POST",
                    data: { 
                      product_id: product_id,
                      fetch_data: 'fetch_data'
                    },
                    success: function(data) {
                        $("#original_product_details").html(data);
                    }
                });
            } else {
                $("#original_product_details").html('');
            }
        });

        $('#mergeCustomers').click(function() {
            var product_original = $('#product_original').val();
            var product_merge = $('#product_merge').val();
            
            if (confirm("Are you sure you want to merge these products?")) {
                $.ajax({
                    url: "pages/product_ids_merge_ajax.php",
                    type: "POST",
                    data: {
                        product_original: product_original,
                        product_merge: product_merge,
                        merge: 'merge'
                    },
                    success: function(data) {
                        if (data.trim() === 'success') {
                            alert('Successfully merged!');
                            location.reload();
                        } else {
                            alert(data);
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