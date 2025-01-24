<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$color_name = "";
$color_code = "";
$color_group = "";
$provider_id = "";
$ekm_color_code = "";
$ekm_color_no = "";
$ekm_paint_code = "";
$stock_availability = '';
$multiplier_category = '';
$color_abbreviation = "";

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['color_id'])){
  $color_id = $_REQUEST['color_id'];
  $query = "SELECT * FROM paint_colors WHERE color_id = '$color_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $color_id = $row['color_id'];
      $color_name = $row['color_name'];
      $color_code = $row['color_code'];
      $color_group = $row['color_group'];
      $provider_id = $row['provider_id'];
      $stock_availability = $row['stock_availability'];
      $multiplier_category = $row['multiplier_category'];
      $ekm_color_code = $row['ekm_color_code'];
      $ekm_color_no = $row['ekm_color_no'];
      $ekm_paint_code = $row['ekm_paint_code'];
      $color_abbreviation = $row['color_abbreviation'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}

$message = "";
if(!empty($_REQUEST['result'])){
  if($_REQUEST['result'] == '1'){
    $message = "New paint color added successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '2'){
    $message = "Paint color updated successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '0'){
    $message = "Failed to Perform Operation";
    $textColor = "text-danger";
  }
  
}

?>
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
        <div class="card-body px-0">
            <div class="d-flex justify-content-between align-items-center">
            <div><br>
                <h4 class="font-weight-medium fs-14 mb-0">Gauge</h4>
                <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                    <a class="text-muted text-decoration-none" href="">Product Properties
                    </a>
                    </li>
                    <li class="breadcrumb-item text-muted" aria-current="page">Gauge</li>
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
    <div class="datatables">
        <div class="card">
        <div class="card-body">
            <h4 class="card-title d-flex justify-content-between align-items-center">Gauge List</h4>
            
            <div class="table-responsive">
        
            <table id="display_gauge" class="table table-striped table-bordered text-nowrap align-middle">
                <thead>
                <!-- start row -->
                <tr>
                    <th>Gauge</th>
                    <th>Thickness</th>
                    <th>#/SQFT</th>
                    <th>#/SQIN</th>
                </tr>
                <!-- end row -->
                </thead>
                <tbody>
                <?php
                $no = 1;
                $query_gauge = "SELECT * FROM table_gauge";
                $result_gauge = mysqli_query($conn, $query_gauge);            
                while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                    $gauge = $row_gauge['gauge'];
                    $thickness = $row_gauge['thickness'];
                    $no_per_sqft = $row_gauge['no_per_sqft'];
                    $no_per_sqin = $row_gauge['no_per_sqin'];
                ?>
                <tr>
                    <td><?= $gauge ?></td>
                    <td><?= $thickness ?></td>
                    <td><?= $no_per_sqft ?></td>
                    <td><?= $no_per_sqin ?></td>
                </tr>
                <?php
                $no++;
                }
                ?>
                </tbody>
                
            </table>
            </div>
        </div>
        </div>
    </div>
    </div>

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

<script>
  $(document).ready(function() {
    var table = $('#display_gauge').DataTable();
});
</script>