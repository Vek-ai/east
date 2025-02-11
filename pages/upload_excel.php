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

<div class="card p-3 shadow w-50 mx-auto">
    <h3 class="text-center mb-3">Upload TRIM Excel File</h3>
    <form id="uploadForm" action="#" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <input type="file" class="form-control" name="excel_file" accept=".xls,.xlsx" required>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Upload & Read</button>
        </div>
    </form>
</div>

<?php
$sql = "SELECT * FROM test";
$result = $conn->query($sql);

if ($result->num_rows > 0) { ?>
    <div class="card p-3 shadow">
        <h3 class="text-center mb-3">Uploaded Data</h3>

        <form id="tableForm">
            <div style="overflow-x: auto; overflow-y: auto; max-height: 800px; max-width: 100%;">
                <table class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>SKU</th>
                            <th>Coil Part No</th>
                            <th>Price 1</th>
                            <th>Price 2</th>
                            <th>Price 3</th>
                            <th>Price 4</th>
                            <th>Price 5</th>
                            <th>Price 6</th>
                            <th>Price 7</th>
                            <th>Category</th>
                            <th>Line</th>
                            <th>Type</th>
                            <th>System</th>
                            <th>Item</th>
                            <th>Stock Type</th>
                            <th>Desc.</th>
                            <th>Material</th>
                            <th>Dimensions</th>
                            <th>Thickness</th>
                            <th>Gauge</th>
                            <th>Grade</th>
                            <th>Color</th>
                            <th>Color Code</th>
                            <th>Paint Provider</th>
                            <th>Color Group</th>
                            <th>Warranty Type</th>
                            <th>Coating</th>
                            <th>Profile</th>
                            <th>Width</th>
                            <th>Bends</th>
                            <th>Hems</th>
                            <th>Hemming Machine</th>
                            <th>Trim Rollformer</th>
                            <th>$ per Hem</th>
                            <th>$ per Bend</th>
                            <th>$ per Sq. In.</th>
                            <th>Coil Width</th>
                            <th>Length</th>
                            <th>Weight</th>
                            <th>Qty Stock</th>
                            <th>Qty Quoted</th>
                            <th>Qty Committed</th>
                            <th>Qty Available</th>
                            <th>Qty In Transit</th>
                            <th>Unit Price</th>
                            <th>Date Added</th>
                            <th>Date Modified</th>
                            <th>Last Ordered Date</th>
                            <th>Last Sold Date</th>
                            <th>Supplier ID</th>
                            <th>Supplier SKU</th>
                            <th>UPC</th>
                            <th>Unit of Measure</th>
                            <th>Coil ID</th>
                            <th>Coil Qty</th>
                            <th>Unit Gross Margin</th>
                            <th>Unit Cost</th>
                            <th>Comment</th>
                            <th>Product Usage</th>
                            <th>Sold By Feet</th>
                            <th>Standing Seam</th>
                            <th>Board Batten</th>
                            <th>Correlated Product ID</th>
                            <th>SmartBuild ID</th>
                            <th>Status</th>
                            <th>Hidden</th>
                            <th>Main Image</th>
                            <th>Product Origin</th>
                            <th>Product Base</th>
                            <th>Product Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            foreach ($row as $key => $value) {
                              ?>
                              <td contenteditable='true' data-column='<?= $key ?>' style='border: 1px solid #ddd; text-align: center; padding: 2px'>
                                <?php
                                if($key == 'product_category'){
                                  ?>
                                  <select id="product_category" class="form-control" name="product_category">
                                      <option value="" >Select One...</option>
                                      <?php
                                      $query_roles = "SELECT * FROM product_category WHERE hidden = '0'";
                                      $result_roles = mysqli_query($conn, $query_roles);            
                                      while ($row_product_category = mysqli_fetch_array($result_roles)) {
                                          $selected = ($value == $row_product_category['product_category_id']) ? 'selected' : '';
                                      ?>
                                          <option value="<?= $row_product_category['product_category_id'] ?>" <?= $selected ?>><?= $row_product_category['product_category'] ?></option>
                                      <?php   
                                      }
                                      ?>
                                  </select>
                                  <?php
                                }else if($key == 'product_system'){
                                  ?>
                                  <select id="product_system" class="form-control" name="product_system">
                                      <option value="" >Select One...</option>
                                      <?php
                                      $query_roles = "SELECT * FROM product_system WHERE hidden = '0'";
                                      $result_roles = mysqli_query($conn, $query_roles);            
                                      while ($row_product_system = mysqli_fetch_array($result_roles)) {
                                          $selected = ($value == $row_product_system['product_system_id']) ? 'selected' : '';
                                      ?>
                                          <option value="<?= $row_product_system['product_system_id'] ?>" <?= $selected ?>><?= $row_product_system['product_system'] ?></option>
                                      <?php   
                                      }
                                      ?>
                                  </select>
                                  <?php
                                }else if($key == 'product_type'){
                                  ?>
                                  <select id="product_type" class="form-control" name="product_type">
                                      <option value="" >Select One...</option>
                                      <?php
                                      $query_roles = "SELECT * FROM product_type WHERE hidden = '0'";
                                      $result_roles = mysqli_query($conn, $query_roles);            
                                      while ($row_product_type = mysqli_fetch_array($result_roles)) {
                                          $selected = ($value == $row_product_type['product_type_id']) ? 'selected' : '';
                                      ?>
                                          <option value="<?= $row_product_type['product_type_id'] ?>" <?= $selected ?>><?= $row_product_type['product_type'] ?></option>
                                      <?php   
                                      }
                                      ?>
                                  </select>
                                  <?php
                                }else if($key == 'product_line'){
                                  ?>
                                  <select id="product_line" class="form-control" name="product_line">
                                      <option value="" >Select One...</option>
                                      <?php
                                      $query_roles = "SELECT * FROM product_line WHERE hidden = '0'";
                                      $result_roles = mysqli_query($conn, $query_roles);            
                                      while ($row_product_line = mysqli_fetch_array($result_roles)) {
                                          $selected = ($value == $row_product_line['product_line_id']) ? 'selected' : '';
                                      ?>
                                          <option value="<?= $row_product_line['product_line_id'] ?>" <?= $selected ?>><?= $row_product_line['product_line'] ?></option>
                                      <?php   
                                      }
                                      ?>
                                  </select>
                                  <?php
                                }else if($key == 'color'){

                                  ?>
                                  <select id="color" class="form-control" name="color">
                                      <option value="" >Select One...</option>
                                      <?php
                                      $query_color = "SELECT * FROM product_color";
                                      $result_color = mysqli_query($conn, $query_color);            
                                      while ($row_color = mysqli_fetch_array($result_color)) {
                                          $selected = ($value == $row_color['id']) ? 'selected' : '';
                                      ?>
                                          <option value="<?= $row_color['id'] ?>" <?= $selected ?>><?= $row_color['color_name'] ?></option>
                                      <?php   
                                      }
                                      ?>
                                  </select>
                                  <?php
                                }else{
                                  echo $value;
                                }
                                ?>
                              </td>
                              <?php
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end">
                <button type="button" id="saveTable" class="btn btn-primary mt-3">Save</button>
            </div>
        </form>
    </div>
<?php } ?>


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
        $('#uploadForm').on('submit', function (e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            formData.append('action', 'upload_excel');

            $.ajax({
                url: 'pages/upload_excel_ajax.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    response = response.trim();
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Data Uploaded successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }  
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    console.error('Response:', jqXHR.responseText);

                    $('#responseHeader').text("Error");
                    $('#responseMsg').text("An error occurred while processing your request.");
                    $('#responseHeaderContainer').removeClass("bg-success").addClass("bg-danger");
                    $('#response-modal').modal("show");
                }
            });
        });

        $("#saveTable").click(function () {
            if (confirm("Are you sure you want to save this Excel data to the products?")) {
                var formData = new FormData();
                formData.append("action", "save_table");

                $.ajax({
                    url: "pages/upload_excel_ajax.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        response = response.trim();
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                        $('#response-modal').modal("show");
                    }
                });
            }
        });

    });
</script>