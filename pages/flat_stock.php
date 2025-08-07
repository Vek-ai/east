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
        <h4 class="font-weight-medium fs-14 mb-0">Flat Sheet</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Coils
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Flat Sheet</li>
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
        <h4 class="card-title">Flat Stock</h4>
      </div>
    </div>

    <div class="row pt-3">
      <div class="col-md-3">
          <label class="form-label">Color</label>
          <div class="mb-3">
            <select id="color" class="form-control select2-add" name="color">
                <option value="" >Select Color...</option>
                <?php
                $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                ?>
                    <option value="<?= $row_paint_colors['color_id'] ?>" ><?= $row_paint_colors['color_name'] ?></option>
                <?php   
                }
                ?>
            </select>
          </div>
      </div>
      <div class="col-md-3">
          <label class="form-label">Width</label>
          <div class="mb-3">
            <input type="text" id="width" name="width" class="form-control" />
          </div>
      </div>
      <div class="col-md-3">
          <label class="form-label">Length</label>
          <div class="mb-3">
            <input type="text" id="length" name="length" class="form-control" />
          </div>
      </div>
      <div class="col-md-3">
          <label class="form-label">Quantity</label>
          <div class="mb-3">
            <input type="text" id="quantity" name="quantity" class="form-control" />
          </div>
      </div>
      <div class="col-md-12">
          <label class="form-label">Notes</label>
          <div class="mb-3">
              <textarea class="form-control w-100" id="notes" name="notes" rows="3"></textarea>
          </div>
      </div>
      <div class="col-md-12">
          <button class="btn ripple btn-primary" type="button" id="saveFlatStock">
              <i class="fe fe-hard-drive"></i> Save
          </button>
      </div>
  </div>
  </div>
</div>


<script>
    $(document).ready(function() {
        $(".select2-add").select2({
            width: '100%',
            placeholder: "Select Correlated Products",
            allowClear: true
        });

        $('#saveFlatStock').click(function() {
            var color = Number($('#color').val());
            var width = Number($('#width').val());
            var length = Number($('#length').val());
            var quantity = Number($('#quantity').val());
            var notes = $('#notes').val();

            $.ajax({
                url: "pages/flat_stock_ajax.php",
                type: "POST",
                data: {
                    color: color,
                    width: width,
                    length: length,
                    quantity: quantity,
                    notes: notes,
                    save_flat_stock: 'save_flat_stock'
                },
                success: function(data) {
                if(data.trim() == 'success'){
                    alert('Successfully saved!');
                    location.reload();
                }else{
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
           
        });
    });


</script>