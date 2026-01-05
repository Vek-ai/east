<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$product_category_id = 0;
$customer_pricing_id = 0;
$product_items = '';
$percentage = 0;

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

if(!empty($_REQUEST['id'])){
  $id = $_REQUEST['id'];
  $query = "SELECT * FROM pricing_category WHERE id = '$id'";
  $result = mysqli_query($conn, $query);            
  while ($row = mysqli_fetch_array($result)) {
      $id = $row['id'];
      $product_category_id = $row['product_category_id'];
      $customer_pricing_id = $row['customer_pricing_id'];
      $product_items = $row['product_items'];
      $percentage = $row['percentage'];
  }
  $saveBtnTxt = "Update";
  $addHeaderTxt = "Update";
}

$permission = $_SESSION['permission'];
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
        width: 100%;
        height: 50px;
        font-size: 16px;
        padding: 10px;
        border-radius: 5px;
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
        <h4 class="font-weight-medium fs-14 mb-0">Categories</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Product Properties
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Categories</li>
          </ol>
        </nav>
      </div>
      <div>
        <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
          
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card card-body">
    <div class="row">
        <div class="col-3">
            <h3 class="card-title align-items-center mb-2">
                Filter Pricing Category 
            </h3>
            <div class="position-relative w-100 px-0 mr-0 mb-2">
                <input type="text" class="form-control py-2 ps-5" data-filter-name="Customer Name" id="text-srh" placeholder="Search Pricing">
                <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </div>
            <div class="align-items-center">
                <div class="position-relative w-100 px-1 mb-2">
                    <select class="form-control py-0 ps-5 select2 filter-selection" data-filter="category" data-filter-name="Product Category" id="select-category">
                        <option value="">All Product Category</option>
                        <optgroup label="Product Category">
                          <?php
                            $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                            $result_category = mysqli_query($conn, $query_category);            
                            while ($row_product_category = mysqli_fetch_array($result_category)) {
                            ?>
                                <option value="<?= $row_product_category['product_category_id'] ?>" 
                                        data-category="<?= $row_product_category['product_category'] ?>"
                                >
                                            <?= $row_product_category['product_category'] ?>
                                </option>
                            <?php   
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="px-3 mb-2"> 
                <input type="checkbox" id="toggleActive" checked> Show Active Only
            </div>
        </div>
        <div class="col-9">
            <div id="selected-tags" class="mb-2"></div>
            <div class="datatables">
              <div class="card">
                <div class="card-body">
                    <h4 class="card-title d-flex justify-content-between align-items-center">Pricing Category List  &nbsp;&nbsp; 
                    <?php                                                    
                    if ($permission === 'edit') {
                    ?>
                    <button type="button" id="addModalBtn" class="btn btn-primary d-flex align-items-center" data-id="" data-type="add">
                        <i class="ti ti-plus text-white me-1 fs-5"></i> Add Pricing Category
                    </button>
                    <?php                                                    
                    }
                    ?>

                    </h4>
                  
                  <div class="table-responsive">
                
                    <table id="display_pricing_category" class="table table-striped table-bordered text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>Product Category</th>
                                <th>Product Items</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;

                            $query_pricing_category = "
                                SELECT 
                                    pc.id,
                                    pc.product_category_id,
                                    pc.product_items,
                                    pcat.product_category,
                                    pc.percentage,
                                    pc.status
                                FROM pricing_category pc
                                LEFT JOIN product_category pcat 
                                    ON pc.product_category_id = pcat.product_category_id
                                WHERE pc.hidden = 0
                                GROUP BY pc.product_category_id, pc.product_items
                                ORDER BY pcat.product_category ASC, pc.product_items ASC
                            ";

                            $result_pricing_category = mysqli_query($conn, $query_pricing_category);

                            $current_category_id = null;

                            while ($row = mysqli_fetch_array($result_pricing_category, MYSQLI_ASSOC)) {
                                $id = $row['id'];
                                $category_id = $row['product_category_id'];
                                $percentage = $row['percentage'];
                                $status_val = $row['status'];

                                $status = ($status_val == 1)
                                    ? "<a href='#' class='changeStatus' data-no='$no' data-id='$id' data-status='$status_val'>
                                            <div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius:5%' role='alert'>Active</div>
                                    </a>"
                                    : "<a href='#' class='changeStatus' data-no='$no' data-id='$id' data-status='$status_val'>
                                            <div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius:5%' role='alert'>Inactive</div>
                                    </a>";

                                $items = array_filter(array_map('trim', explode(',', $row['product_items'])));

                                $names = [];
                                foreach ($items as $product_id) {
                                    $names[] = getProductName((int)$product_id);
                                }

                                $item_names = implode(', ', $names);

                                if (mb_strlen($item_names) > 50) {
                                    $item_names = mb_substr($item_names, 0, 50);
                                    $item_names = preg_replace('/\s+\S*$/u', '', $item_names) . '...';
                                }
                                
                                ?>
                                <tr id="product-row-<?= $no ?>" data-category="<?= $category_id ?>">
                                    <td><span class="<?php if ($status_val == 0) echo 'emphasize-strike'; ?>"><?= $row['product_category'] ?></span></td>
                                    <td><span class="<?php if ($status_val == 0) echo 'emphasize-strike'; ?>"><?= $item_names ?></span></td>
                                    <td><?= $status ?></td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center">
                                        <?php if ($permission === 'edit') { 
                                            if ($status_val == 0) { ?>
                                                <a href="#" title="Archive" class="py-1 text-dark hidePricingCategory text-decoration-none" data-id="<?= $id ?>" data-row="<?= $no ?>">
                                                    <i class="ti ti-trash text-danger fs-7"></i>
                                                </a>
                                            <?php } else { ?>
                                                <a href="#" title="Edit" 
                                                id="addModalBtn" 
                                                class="d-flex align-items-center justify-content-center text-decoration-none" 
                                                data-id="<?= $id ?>" 
                                                data-type="edit">
                                                    <i class="ti ti-pencil fs-7"></i>
                                                </a>
                                            <?php } 
                                        } ?>
                                        </div>
                                    </td>
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

<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="add-header">
                    Add
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pricingCategoryForm" class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                          <div id="add-fields" class=""></div>
                          <div class="form-actions">
                              <div class="border-top">
                                  <div class="row mt-2">
                                      <div class="col-6 text-start"></div>
                                      <div class="col-6 text-end ">
                                          <button type="submit" class="btn btn-primary" style="border-radius: 10%;">Save</button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
  $(document).ready(function() {
    document.title = "Pricing Category";

    var table = $('#display_pricing_category').DataTable({
        pageLength: 100
    });

    $('#display_pricing_category_filter').hide();

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

    $(document).on('click', '.changeStatus', function(event) {
        event.preventDefault(); 
        var id = $(this).data('id');
        var status = $(this).data('status');
        var no = $(this).data('no');
        $.ajax({
            url: 'pages/pricing_category_ajax.php',
            type: 'POST',
            data: {
                id: id,
                status: status,
                action: 'change_status'
            },
            success: function(response) {
                if (response == 'success') {
                    if (status == 1) {
                        $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                        $(".changeStatus[data-no='" + no + "']").data('status', "0");
                        $('.product' + no).addClass('emphasize-strike');
                        $('#action-button-' + no).html('<a href="#" title="Archive" class="btn btn-light py-1 text-dark hidePricingCategory" data-id="' + id + '" data-row="' + no + '" style="border-radius: 10%;">Archive</a>');
                        $('#toggleActive').trigger('change');
                        } else {
                        $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                        $(".changeStatus[data-no='" + no + "']").data('status', "1");
                        $('.product' + no).removeClass('emphasize-strike');
                        $('#action-button-' + no).html('<a href="?page=pricing_category&id=' + id + '" title="Edit" class="btn btn-primary py-1" style="border-radius: 10%;">Edit</a>');
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

    $(document).on('click', '.hidePricingCategory', function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        var rowId = $(this).data('row');
        $.ajax({
            url: 'pages/pricing_category_ajax.php',
            type: 'POST',
            data: {
                id: id,
                action: 'hide_pricing_category'
            },
            success: function(response) {
                if (response == 'success') {
                    $('#product-row-' + rowId).remove();
                } else {
                    alert('Failed to hide category.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

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

    $('.select2').select2();

    $("#product_category").select2({
        width: '100%',
        placeholder: "Select Product Category",
        allowClear: true
    });

    $("#customer_pricing").select2({
        width: '100%',
        placeholder: "Select Customer Pricing",
        allowClear: true
    });

    $('#pricingCategoryForm').on('submit', function(event) {
        event.preventDefault(); 

        var userid = getCookie('userid');

        var formData = new FormData(this);
        formData.append('action', 'add_update');
        formData.append('userid', userid);

        var appendResult = "";

        $.ajax({
            url: 'pages/pricing_category_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              $('.modal').modal("hide");
              if (response.trim() === "success") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text('Pricing category successfully saved.');
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

    function filterTable() {
        var textSearch = $('#text-srh').val().toLowerCase();
        var isActive = $('#toggleActive').is(':checked');

        $.fn.dataTable.ext.search = [];

        if (textSearch) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
            });
        }

        if (isActive) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                return $(table.row(dataIndex).node()).find('a .alert').text().trim() === 'Active';
            });
        }

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var row = $(table.row(dataIndex).node());
            var match = true;

            $('.filter-selection').each(function() {
                var filterValue = $(this).val()?.toString() || '';
                var rowValue = row.data($(this).data('filter'))?.toString() || '';

                if (filterValue && filterValue !== '/' && rowValue !== filterValue) {
                    match = false;
                    return false; // Exit loop early if mismatch is found
                }
            });

            return match;
        });

        table.draw();
        updateSelectedTags();
    }

    $(document).on('change', '.filter-selection', filterTable);

    $(document).on('input', '#text-srh', filterTable);

    $(document).on('change', '#toggleActive', filterTable);

    function updateSelectedTags() {
        var displayDiv = $('#selected-tags');
        displayDiv.empty();

        $('.filter-selection').each(function() {
            var selectedOption = $(this).find('option:selected');
            var selectedText = selectedOption.text().trim();
            var filterName = $(this).data('filter-name'); // Custom attribute for display

            if ($(this).val()) {
                displayDiv.append(`
                    <div class="d-inline-block p-1 m-1 border rounded bg-light">
                        <span class="text-dark">${filterName}: ${selectedText}</span>
                        <button type="button" 
                            class="btn-close btn-sm ms-1 remove-tag" 
                            style="width: 0.75rem; height: 0.75rem;" 
                            aria-label="Close" 
                            data-select="#${$(this).attr('id')}">
                        </button>
                    </div>
                `);
            }
        });

        $('.remove-tag').on('click', function() {
            $($(this).data('select')).val('').trigger('change');
            $(this).parent().remove();
        });
    }

    $(document).on('click', '#addModalBtn', function(event) {
        event.preventDefault();
        var id = $(this).data('id') || '';
        var type = $(this).data('type') || '';

        if(type == 'edit'){
          $('#add-header').html('Update Pricing Category');
        }else{
          $('#add-header').html('Add Pricing Category');
        }

        $.ajax({
            url: 'pages/pricing_category_ajax.php',
            type: 'POST',
            data: {
              id : id,
              action: 'fetch_modal_content'
            },
            success: function (response) {
                $('#add-fields').html(response);
                $('#addModal').modal('show');
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
    
});
</script>