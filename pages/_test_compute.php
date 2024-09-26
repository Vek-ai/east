<?php
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
        <h4 class="font-weight-medium fs-14 mb-0">Coils</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Product
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Coils</li>
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
        <h4 class="card-title">Computation</h4>
      </div>
    </div>

    <div class="row pt-3">
      <div class="col-md-6">
          <label class="form-label">Coil</label>
              <div class="mb-3">
              <select id="coil" class="form-control select2-add" name="coil">
                  <option value="">Select Coil...</option>
                  <optgroup label="Coil">
                      <?php
                      $query_coil = "SELECT * FROM coil WHERE hidden = '0'";
                      $result_coil = mysqli_query($conn, $query_coil);
                      while ($row_coil = mysqli_fetch_array($result_coil)) {
                      ?>
                          <option value="<?= $row_coil['coil_id'] ?>" data-length="<?= $row_coil['length'] ?>"><?= $row_coil['coil'] ?></option>
                      <?php } ?>
                  </optgroup>
              </select>
          </div>
      </div>
      <div class="col-md-6">
          <label class="form-label">Panel / Trim</label>
          <div class="mb-3">
              <select id="coil" class="form-control select2-add" name="coil">
                  <option value="">Select Panel...</option>
                  <optgroup label="Panel">
                      <?php
                      $query_panel = "SELECT * FROM product WHERE product_category = '$panel_id' AND (length > 0 OR length IS NOT NULL)";
                      $result_panel = mysqli_query($conn, $query_panel);
                      while ($row_panel = mysqli_fetch_array($result_panel)) {
                      ?>
                          <option value="<?= $row_panel['product_id'] ?>" data-length="<?= $row_panel['length'] ?>"><?= $row_panel['product_item'] ?></option>
                      <?php } ?>
                  </optgroup>
                  <optgroup label="Trim">
                      <?php
                      $query_trim = "SELECT * FROM product WHERE product_category = '$trim_id' AND (length > 0 OR length IS NOT NULL)";
                      $result_trim = mysqli_query($conn, $query_trim);
                      while ($row_trim = mysqli_fetch_array($result_trim)) {
                      ?>
                          <option value="<?= $row_trim['product_id'] ?>" data-length="<?= $row_trim['length'] ?>"><?= $row_trim['product_item'] ?></option>
                      <?php } ?>
                  </optgroup>
              </select>

          </div>
      </div>
      <div class="col-md-12">
          <label class="form-label">Computed Length</label>
          <div class="mb-3">
            <textarea class="form-control w-100" rows="3"></textarea>
          </div>
      </div>
  </div>
    

  </div>
</div>


<script>
  $(document).ready(function() {
    
    
  });
</script>