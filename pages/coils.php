<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

$trim_id = 43;
$panel_id = 46;

if(!empty($_REQUEST['coil_id'])){
  $coil_id = $_REQUEST['coil_id'];
  $query = "SELECT * FROM coil WHERE coil_id = '$coil_id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $coil_id = $row['coil_id'];
      $coil = $row['coil'];
      $grade = $row['grade'];
      $color = $row['color'];
      $width = $row['width'];
      $length = $row['length'];
      $thickness = $row['thickness'];
      $material_grade = $row['material_grade'];
      $steel_coating = $row['steel_coating'];
      $gauge = $row['gauge'];
      $backer_color = $row['backer_color'];
      $weight = $row['weight'];

      $supplier = $row['supplier'];
      $entry_number = $row['entry_number'];
      $coil_number = $row['coil_number'];
      $tag_number = $row['tag_number'];
      $entry_date = $row['entry_date'];
      $invoice = $row['invoice'];
      $original_feet = $row['original_feet'];
      $original_weight = $row['original_weight'];
      $remaining_feet = $row['remaining_feet'];
      $remaining_weight = $row['remaining_weight'];
      $price_per_foot = $row['price_per_foot'];
      $price_per_cwt = $row['price_per_cwt'];
      $pounds_per_foot = $row['pounds_per_foot'];
      $color_code = $row['color_code'];
      $actual_width = $row['actual_width'];
      $rounded_width = $row['rounded_width'];
      $original_price = $row['original_price'];
      $current_price = $row['current_price'];
      $category = $row['category'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}

$message = "";
if(!empty($_REQUEST['result'])){
  if($_REQUEST['result'] == '1'){
    $message = "New coil added successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '2'){
    $message = "Coils updated successfully.";
    $textColor = "text-success";
  }else if($_REQUEST['result'] == '0'){
    $message = "Failed to Perform Operation";
    $textColor = "text-danger";
  }
  
}

?>
    <style>
      td.notes,  td.last-edit{
          white-space: normal;
          word-wrap: break-word;
      }
      .emphasize-strike {
          text-decoration: line-through;
          font-weight: bold;
          color: #9a841c;
      }
      .dataTables_filter input {
          width: 100%; /* Adjust the width as needed */
          height: 50px; /* Adjust the height as needed */
          font-size: 16px; /* Adjust the font size as needed */
          padding: 10px; /* Adjust the padding as needed */
          border-radius: 5px; /* Adjust the border-radius as needed */
      }
      .dataTables_filter {  width: 100%;}
      #toggleActive {
          margin-bottom: 10px;
      }

      .inactive-row {
          display: none;
      }
    </style>
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
  <!-- start Default Form Elements -->
  <div class="card card-body">
    <div class="row">
      <div class="col-3">
        <h4 class="card-title"><?= $addHeaderTxt ?> Coils</h4>
      </div>
      <div class="col-9">
        <h4 class="card-title <?= $textColor ?>"><?= $message ?></h4>
      </div>
    </div>
    

    <form id="coilForm" class="form-horizontal">
    <div class="row pt-3">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Coil Name</label>
                <input type="text" id="coil" name="coil" class="form-control" value="<?= $coil ?>"/>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Supplier</label>
            <div class="mb-3">
                <select id="supplier_id" class="form-control select2-add" name="supplier_id">
                    <option value="">Select Supplier...</option>
                    <optgroup label="Supplier">
                        <?php
                        $query_supplier = "SELECT * FROM supplier";
                        $result_supplier = mysqli_query($conn, $query_supplier);
                        while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                        ?>
                            <option value="<?= $row_supplier['supplier_id'] ?>"><?= $row_supplier['supplier_name'] ?></option>
                        <?php } ?>
                    </optgroup>
                </select>
            </div>
        </div>
    </div>

    <div class="row pt-3">
        <div class="col-md-3 opt_field_update" data-id="7">
            <div class="mb-3">
                <label class="form-label">Color</label>
                <select id="color" class="form-control" name="color">
                    <option value="">Select Color...</option>
                    <?php
                    $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0'";
                    $result_paint_colors = mysqli_query($conn, $query_paint_colors);
                    while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                        $selected = ($color == $row_paint_colors['color_id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?>><?= $row_paint_colors['color_name'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="col-md-3 opt_field_update" data-id="5">
            <div class="mb-3">
                <label class="form-label">Gauge</label>
                <select id="gauge" class="form-control" name="gauge">
                    <option value="">Select Gauge...</option>
                    <?php
                    $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0'";
                    $result_gauge = mysqli_query($conn, $query_gauge);
                    while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                        $selected = ($gauge == $row_gauge['product_gauge_id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $row_gauge['product_gauge_id'] ?>" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="col-md-3 opt_field_update" data-id="6">
            <div class="mb-3">
                <label class="form-label">Grade</label>
                <select id="grade" class="form-control" name="grade">
                    <option value="">Select Grade...</option>
                    <?php
                    $query_grade = "SELECT * FROM product_grade WHERE hidden = '0'";
                    $result_grade = mysqli_query($conn, $query_grade);
                    while ($row_grade = mysqli_fetch_array($result_grade)) {
                        $selected = ($grade == $row_grade['product_grade_id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $row_grade['product_grade_id'] ?>" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="col-md-3 opt_field_update" data-id="6">
            <div class="mb-3">
                <label class="form-label">Product Category</label>
                <select id="product_category_update" class="form-control" name="category">
                    <option value="">Select One...</option>
                    <option value="46" <?= isset($category) && $category == $trim_id ? 'selected' : '' ?>>Trim</option>
                    <option value="43" <?= isset($category) && $category == $panel_id ? 'selected' : '' ?>>Panel</option>
                </select>
            </div>
        </div>
        
    </div>

    <div class="row pt-3">
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">Entry #</label>
                <input type="text" id="entry_number" name="entry_number" class="form-control" value="<?= $entry_number ?>"/>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">Coil #</label>
                <input type="text" id="coil_number" name="coil_number" class="form-control" value="<?= $coil_number ?>"/>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">Tag #</label>
                <input type="text" id="tag_number" name="tag_number" class="form-control" value="<?= $tag_number ?>"/>
            </div>
        </div>
    </div>

    <div class="row pt-3">
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label">Original Feet</label>
                <input type="text" id="original_feet" name="original_feet" class="form-control" value="<?= $original_feet ?>"/>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label">Original Weight</label>
                <input type="text" id="original_weight" name="original_weight" class="form-control" value="<?= $original_weight ?>"/>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label">Remaining Feet</label>
                <input type="text" id="remaining_feet" name="remaining_feet" class="form-control" value="<?= $remaining_feet ?>"/>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label">Remaining Weight</label>
                <input type="text" id="remaining_weight" name="remaining_weight" class="form-control" value="<?= $remaining_weight ?>"/>
            </div>
        </div>
    </div>

    <div class="row pt-3">
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label">Price per Foot</label>
                <input type="text" id="price_per_foot" name="price_per_foot" class="form-control" value="<?= $price_per_foot ?>"/>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label">Price per CWT</label>
                <input type="text" id="price_per_cwt" name="price_per_cwt" class="form-control" value="<?= $price_per_cwt ?>"/>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label">Pounds per Foot</label>
                <input type="text" id="pounds_per_foot" name="pounds_per_foot" class="form-control" value="<?= $pounds_per_foot ?>"/>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label">Color Code</label>
                <input type="text" id="color_code" name="color_code" class="form-control" value="<?= $color_code ?>"/>
            </div>
        </div>
    </div>

    <div class="row pt-3">
      <div class="col-md-3">
        <div class="mb-3">
          <label class="form-label">Width</label>
          <input type="text" id="width" name="width" class="form-control"  value="<?= $width ?>"/>
        </div>
      </div>
      <div class="col-md-3">
        <div class="mb-3">
          <label class="form-label">Length</label>
          <input type="text" id="length" name="length" class="form-control"  value="<?= $length ?>"/>
        </div>
      </div>
      <div class="col-md-3">
        <div class="mb-3">
          <label class="form-label">Thickness</label>
          <input type="text" id="thickness" name="thickness" class="form-control"  value="<?= $thickness ?>"/>
        </div>
      </div>
      <div class="col-md-3">
        <div class="mb-3">
          <label class="form-label">Weight</label>
          <input type="text" id="weight" name="weight" class="form-control"  value="<?= $weight ?>"/>
        </div>
      </div>
    </div>

    <div class="row pt-3">
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label">Actual Width</label>
                <input type="text" id="actual_width" name="actual_width" class="form-control" value="<?= $actual_width ?>"/>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label">Rounded Width</label>
                <input type="text" id="rounded_width" name="rounded_width" class="form-control" value="<?= $rounded_width ?>"/>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label">Original Price</label>
                <input type="text" id="original_price" name="original_price" class="form-control" value="<?= $original_price ?>"/>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label">Current Price</label>
                <input type="text" id="current_price" name="current_price" class="form-control" value="<?= $current_price ?>"/>
            </div>
        </div>
    </div>

    <div class="row pt-3">
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label">Material Grade</label>
          <input type="text" id="material_grade" name="material_grade" class="form-control"  value="<?= $material_grade ?>"/>
        </div>
      </div>
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label">Steel Coating</label>
          <input type="text" id="steel_coating" name="steel_coating" class="form-control"  value="<?= $steel_coating ?>"/>
        </div>
      </div>
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label">Backer Color</label>
          <input type="text" id="backer_color" name="backer_color" class="form-control"  value="<?= $backer_color ?>"/>
        </div>
      </div>
    </div>

    <div class="row pt-3">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Entry Date</label>
                <input type="date" id="entry_date" name="entry_date" class="form-control" value="<?= $entry_date ?>"/>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Invoice</label>
                <input type="text" id="invoice" name="invoice" class="form-control" value="<?= $invoice ?>"/>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <div class="card-body border-top">
            <input type="hidden" id="coil_id" name="coil_id" class="form-control" value="<?= $coil_id ?>"/>
            <div class="row">
                <div class="col-6 text-start"></div>
                <div class="col-6 text-end">
                    <button type="submit" class="btn btn-primary" style="border-radius: 10%;"><?= $saveBtnTxt ?></button>
                </div>
            </div>
        </div>
    </div>
</form>

  </div>
  <!-- end Default Form Elements -->
</div>
<div class="col-12">
  <div class="datatables">
    <div class="card">
      <div class="card-body">
          <h4 class="card-title d-flex justify-content-between align-items-center">Coils List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['coil_id'])){ ?>
            <a href="/?page=coils" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
            <?php } ?> <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
          </h4>
        
        <div class="table-responsive">
       
          <table id="display_coil" class="table table-striped table-bordered text-nowrap align-middle">
            <thead>
              <!-- start row -->
              <tr>
                <th>Coils</th>
                <th>Grade</th>
                <th>Color</th>
                <th>Gauge</th>
                <th>Width</th>
                <th>Details</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
              <!-- end row -->
            </thead>
            <tbody>
<?php
$no = 1;
$query_coil = "SELECT * FROM coil WHERE hidden=0";
$result_coil = mysqli_query($conn, $query_coil);            
while ($row_coil = mysqli_fetch_array($result_coil)) {
    $coil_id = $row_coil['coil_id'];
    $coil = $row_coil['coil'];
    $grade = $row_coil['grade'];
    $color = $row_coil['color'];
    $gauge = $row_coil['gauge'];
    $width = $row_coil['width'];
    $db_status = $row_coil['status'];
   // $last_edit = $row_coil['last_edit'];
    $date = new DateTime($row_coil['last_edit']);
    $last_edit = $date->format('m-d-Y');

    $added_by = $row_coil['added_by'];
    $edited_by = $row_coil['edited_by'];

    
    if($edited_by != "0"){
      $last_user_name = get_name($edited_by);
    }else if($added_by != "0"){
      $last_user_name = get_name($added_by);
    }else{
      $last_user_name = "";
    }

    if ($row_coil['status'] == '0') {
        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$coil_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
    } else {
        $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$coil_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
    }
?>
<tr id="product-row-<?= $no ?>">
    <td><span class="product<?= $no ?> <?php if ($row_coil['status'] == '0') { echo 'emphasize-strike'; } ?>"><?= $coil ?></span></td>
    <td><?= getGradeName($grade) ?></td>
    <td><?= getColorName($color) ?></td>
    <td><?= getGaugeName($gauge) ?></td>
    <td><?= $width ?></td>
    <td class="last-edit" style="width:30%;">Last Edited <?= $last_edit ?> by  <?= $last_user_name ?></td>
    <td><?= $status ?></td>
    <td class="text-center" id="action-button-<?= $no ?>">
        <?php if ($row_coil['status'] == '0') { ?>
            <a href="#" class="btn btn-light py-1 text-dark hideCoil" data-id="<?= $coil_id ?>" data-row="<?= $no ?>" style='border-radius: 10%;'>Archive</a>
        <?php } else { ?>
            <a href="/?page=coils&coil_id=<?= $coil_id ?>" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a>
        <?php } ?>
    </td>
</tr>
<?php
$no++;
}
?>
</tbody>
<script>
$(document).ready(function() {
    // Use event delegation for dynamically generated elements
    $(document).on('click', '.changeStatus', function(event) {
        event.preventDefault(); 
        var coil_id = $(this).data('id');
        var status = $(this).data('status');
        var no = $(this).data('no');

        console.log("id: " +coil_id +"status: " +status +"no: " +no)
        $.ajax({
            url: 'pages/coils_ajax.php',
            type: 'POST',
            data: {
                coil_id: coil_id,
                status: status,
                action: 'change_status'
            },
            success: function(response) {
                if (response == 'success') {
                    if (status == 1) {
                        $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                        $(".changeStatus[data-no='" + no + "']").data('status', "0");
                        $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                        $('#action-button-' + no).html('<a href="#" class="btn btn-light py-1 text-dark hideCoil" data-id="' + coil_id + '" data-row="' + no + '" style="border-radius: 10%;">Archive</a>');
                        $('#toggleActive').trigger('change');
                      } else {
                        $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                        $(".changeStatus[data-no='" + no + "']").data('status', "1");
                        $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                        $('#action-button-' + no).html('<a href="/?page=coils&coil_id=' + coil_id + '" class="btn btn-primary py-1" style="border-radius: 10%;">Edit</a>');
                        $('#toggleActive').trigger('change');
                      }
                } else {
                    alert('Failed to change status.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $(document).on('click', '.hideCoil', function(event) {
        event.preventDefault();
        var coil_id = $(this).data('id');
        var rowId = $(this).data('row');
        $.ajax({
            url: 'pages/coils_ajax.php',
            type: 'POST',
            data: {
                coil_id: coil_id,
                action: 'hide_coil'
            },
            success: function(response) {
                if (response == 'success') {
                    $('#product-row-' + rowId).remove(); // Remove the row from the DOM
                } else {
                    alert('Failed to hide coil.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });
});
</script>




            
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
    var table = $('#display_coil').DataTable();

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var status = $(table.row(dataIndex).node()).find('a .alert').text().trim();
        var isActive = $('#toggleActive').is(':checked');

        if (!isActive || status === 'Active') {
            return true;
        }
        return false;
    });

    $('#toggleActive').on('change', function() {
        table.draw();
    });

    $('#toggleActive').trigger('change');

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    $('#coilForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/coils_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              
              if (response.trim() === "success_update") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("Coils updated successfully.");
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                      location.reload();
                  });
              } else if (response.trim() === "success_add") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text("New coil added successfully.");
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
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $(document).on('click', '#btnOrderCoil', function(event) {
        event.preventDefault();
        var coil_id = $(this).data('id');
        $.ajax({
            url: 'pages/coils_ajax.php',
            type: 'POST',
            data: {
                coil_id: coil_id,
                action: 'order_coil'
            },
            success: function(response) {
                if (response == 'success') {
                  window.location.href = "?page=order_coil";
                } else {
                  console.log(response)
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });
    
    
});
</script>