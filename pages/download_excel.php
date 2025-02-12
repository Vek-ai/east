<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div><br>
        <h4 class="font-weight-medium fs-14 mb-0">Upload Excel</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="?page=">Home
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Upload Excel</li>
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

<div class="card shadow-lg p-4 mx-auto" style="max-width: 500px; border-radius: 12px;">
    <h3 class="text-center mb-4 text-primary">Download Product Excel</h3>
    
    <form id="uploadForm" method="post">
        <label for="select-category" class="form-label fw-semibold">Select Category</label>
        <div class="mb-3">
            <select class="form-select select2" id="select-category" name="category">
                <option value="">All Categories</option>
                <optgroup label="Category">
                    <?php
                    $query_category = "SELECT * FROM product_category WHERE hidden = '0'";
                    $result_category = mysqli_query($conn, $query_category);
                    while ($row_category = mysqli_fetch_array($result_category)) {
                    ?>
                        <option value="<?= $row_category['product_category_id'] ?>"><?= $row_category['product_category'] ?></option>
                    <?php
                    }
                    ?>
                </optgroup>
            </select>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary fw-semibold">
                <i class="fas fa-download me-2"></i> Download Excel
            </button>
        </div>
    </form>
</div>

<div class="modal fade" id="response-modal" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div id="responseHeaderContainer" class="modal-header align-items-center modal-colored-header">
        <h4 id="responseHeader" class="m-0"></h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="responseMsg"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<script>
    $(document).ready(function () {
        $('.select2').select2({
            placeholder: "Choose a category",
            allowClear: true,
            width: '100%'
        });

        $("#uploadForm").submit(function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            formData.append("action", "download_excel");

            $.ajax({
                url: "pages/download_excel_ajax.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    window.location.href = "pages/download_excel_ajax.php?action=download_excel&category=" + encodeURIComponent($("#select-category").val());
                },
                error: function (xhr, status, error) {
                    alert("Error downloading file: " + error);
                }
            });
        });
    });
</script>

