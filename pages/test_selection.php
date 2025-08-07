<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';
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
        <h4 class="card-title">Price Selection</h4>
      </div>
    </div>

    <div class="d-flex justify-content-center align-items-center flex-wrap">
        <div class="col-md-2 mb-3">
            <label class="form-label">Product System</label>
            <div>
                <select class="form-control search-chat py-0 ps-5 select2-init" id="select-system" data-category="">
                    <option value="" data-category="">All Systems</option>
                    <optgroup label="Product Systems">
                        <?php
                        $query_systems = "SELECT * FROM product_system WHERE hidden = '0' AND status = '1' ORDER BY `product_system` ASC";
                        $result_systems = mysqli_query($conn, $query_systems);
                        while ($row_systems = mysqli_fetch_array($result_systems)) {
                        ?>
                            <option value="<?= $row_systems['product_system_id'] ?>" data-category="systems" data-multiplier="<?= $row_systems['multiplier'] ?>"><?= $row_systems['product_system'] ?></option>
                        <?php
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Product Category</label>
            <div>
                <select class="form-control search-chat py-0 ps-5 select2-init" id="select-category" data-category="">
                    <option value="" data-category="">All Categories</option>
                    <optgroup label="Product Category">
                        <?php
                        $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                        $result_category = mysqli_query($conn, $query_category);
                        while ($row_category = mysqli_fetch_array($result_category)) {
                        ?>
                            <option value="<?= $row_category['product_category_id'] ?>" data-category="category" data-multiplier="<?= $row_category['multiplier'] ?>"><?= $row_category['product_category'] ?></option>
                        <?php
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Product Line</label>
            <div>
                <select class="form-control search-chat py-0 ps-5 select2-init" id="select-line" data-category="">
                    <option value="" data-category="">All Lines</option>
                    <optgroup label="Product Lines">
                        <?php
                        $query_line = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                        $result_line = mysqli_query($conn, $query_line);
                        while ($row_line = mysqli_fetch_array($result_line)) {
                        ?>
                            <option value="<?= $row_line['product_line_id'] ?>" data-category="line" data-multiplier="<?= $row_line['multiplier'] ?>"><?= $row_line['product_line'] ?></option>
                        <?php
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <p class="form-label">Product Type</p>
            <div>
                <select class="form-control search-chat py-0 ps-5 select2-init" id="select-type" data-category="">
                    <option value="" data-category="">All Types</option>
                    <optgroup label="Product Types">
                        <?php
                        $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                        $result_type = mysqli_query($conn, $query_type);
                        while ($row_type = mysqli_fetch_array($result_type)) {
                        ?>
                            <option value="<?= $row_type['product_type_id'] ?>" data-category="type" data-multiplier="<?= $row_type['multiplier'] ?>" data-special="<?= $row_type['special'] ?>"><?= $row_type['product_type'] ?></option>
                        <?php
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <p class="form-label">Trim Multiplier</p>
            <div>
                <select class="form-control search-chat py-0 ps-5 select2-init" id="select-trim-color" data-category="">
                    <option value="" data-category="">All Trim Multipliers</option>
                    <optgroup label="Trim Multipliers">
                        <?php
                        $query_trim_color = "SELECT * FROM trim_color WHERE hidden = '0'";
                        $result_trim_color = mysqli_query($conn, $query_trim_color);
                        while ($row_trim_color = mysqli_fetch_array($result_trim_color)) {
                        ?>
                            <option value="<?= $row_trim_color['trim_color_id'] ?>" data-category="trim_color" data-multiplier="<?= $row_trim_color['multiplier'] ?>"><?= $row_trim_color['trim_color'] ?></option>
                        <?php
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="col-md-12 mb-3">
            <label class="form-label">Product Item</label>
            <div>
                <select class="form-control search-chat py-0 ps-5 select2-init" id="select-item" data-category="">
                    <option value="" data-category="">All Items</option>
                    <optgroup label="Product Items">
                        <?php
                        $query_item = "SELECT * FROM product_base WHERE hidden = '0'";
                        $result_item = mysqli_query($conn, $query_item);
                        while ($row_item = mysqli_fetch_array($result_item)) {
                        ?>
                            <option value="<?= $row_item['id'] ?>" data-category="item" data-price="<?= $row_item['base_price'] ?>"><?= $row_item['product_name'] ?></option>
                        <?php
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="mb-2 col-md-12">
            <label class="fs-5 fw-bold" for="quantity_product">Quantity</label>
            <input id="quantity_product" name="quantity_product" class="form-control" placeholder="Enter Quantity" list="quantity-product-list" autocomplete="off">
        </div>
        <div class="mb-2 col-md-12">
            <fieldset class="p-0 position-relative">
                <legend class="fs-5 fw-bold">Length</legend>
                <div class="input-group d-flex align-items-center">
                    <input class="form-control mr-1" type="number" id="length_feet" name="length_feet"  list="length_feet_datalist" placeholder="FT" size="5" style="color:#ffffff;">
                    <input class="form-control" type="number" id="length_inch" name="length_inch" list="length_inch_datalist" placeholder="IN" size="5" style="color:#ffffff;">
                </div>
            </fieldset>
            <fieldset class="p-0 position-relative mt-2">
                <legend class="fs-5 fw-bold">Width</legend>
                <div class="input-group d-flex align-items-center">
                    <input class="form-control mr-1" type="number" id="width_feet" name="width_feet"  placeholder="FT" size="5" style="color:#ffffff;">
                    <input class="form-control" type="number" id="width_inch" name="width_inch" placeholder="IN" size="5" style="color:#ffffff;">
                </div>
            </fieldset>
        </div>
            
        <div class="mb-2 row col-md-12 panel-group d-none w-100">
            <label class="form-label">Select Panel Type</label>
            <div class="d-flex gap-3">
                <div class="form-check">
                    <input type="radio" id="solid_panel" name="panel_type" value="solid" checked class="form-check-input">
                    <label for="solid_panel" class="form-check-label">Solid</label>
                </div>
                <div class="form-check position-relative">
                    <input type="radio" id="vented_panel" name="panel_type" value="vented" class="form-check-input">
                    <label for="vented_panel" class="form-check-label">Vented</label>
                    <div id="tooltip" class="tooltip-custom" style="display: none;">Double-click to select Vented</div>
                </div>
                <div class="form-check">
                    <input type="radio" id="drip_stop_panel" name="panel_type" value="drip_stop" class="form-check-input" checked>
                    <label for="drip_stop_panel" class="form-check-label">Drip Stop</label>
                </div>
            </div>
        </div>

        <div class="special-group col-md-12 d-flex align-items-center justify-content-between flex-wrap w-100 mb-3 d-none">
            <div class="me-2 flex-grow-1">
                <label class="form-label" for="bend">Bends</label>
                <input type="number" id="bend" class="form-control" placeholder="Enter Bends">
            </div>
            <div class="me-2 flex-grow-1">
                <label class="form-label" for="hem">Hems</label>
                <input type="number" id="hem" class="form-control" placeholder="Enter Hems">
            </div>
        </div> 
        



    </div>

    <div class="form-actions">
        <div class="card-body border-top ">
            <div class="row">
                <div class="col-6 text-start"></div>
                <div class="col-6 text-end">
                    <button type="button" class="btn btn-primary" id="compute-button" style="border-radius: 10%;">Compute</button>
                </div>
            </div>
        </div>
    </div>

  </div>
  <!-- end Default Form Elements -->
</div>
<div class="col-12">
  <div class="datatables">
    <div class="card">
      <div class="card-body">
        <div id="selected-info" class="mt-3" style="display: none;"></div>
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
        $('.select2-init').select2({
            width: '100%',
            placeholder: 'Select an option',
            allowClear: true
        });

        $('#select-type').on('change', function() {
            var specialData = $('#select-type option:selected').data('special');

            if (specialData == '1') {
                $('.special-group').removeClass('d-none').addClass('d-flex');
            } else {
                $('.special-group').removeClass('d-flex').addClass('d-none');
            }
        });

        $('#select-category').on('change', function() {
            var categoryData = $('#select-category option:selected').val();
            if (categoryData == '3') {
                $('.panel-group').removeClass('d-none').addClass('d-flex');
            } else {
                $('.panel-group').removeClass('d-flex').addClass('d-none');
            }
        });


        $('#compute-button').on('click', function() {
            var systemName = $('#select-system option:selected').text();
            var systemMultiplier = parseFloat($('#select-system option:selected').data('multiplier')) || 1;

            var categoryName = $('#select-category option:selected').text();
            var categoryMultiplier = parseFloat($('#select-category option:selected').data('multiplier')) || 1;

            var lineName = $('#select-line option:selected').text();
            var lineMultiplier = parseFloat($('#select-line option:selected').data('multiplier')) || 1;

            var typeName = $('#select-type option:selected').text();
            var typeMultiplier = parseFloat($('#select-type option:selected').data('multiplier')) || 1;

            var trimColorName = $('#select-trim-color option:selected').text();
            var trimColorMultiplier = parseFloat($('#select-trim-color option:selected').data('multiplier')) || 1;

            var itemName = $('#select-item option:selected').text();
            var itemPrice = parseFloat($('#select-item option:selected').data('price')) || 0;

            var basePrice = itemPrice * systemMultiplier * categoryMultiplier * lineMultiplier * typeMultiplier * trimColorMultiplier;

            var bends = parseFloat($('#bend').val()) || 0;
            var hems = parseFloat($('#hem').val()) || 0;

            var quantity = parseFloat($('#quantity_product').val()) || 0;
            var lengthFeet = parseFloat($('#length_feet').val()) || 0;
            var lengthInch = parseFloat($('#length_inch').val()) || 0;
            var widthFeet = parseFloat($('#width_feet').val()) || 0;
            var widthInch = parseFloat($('#width_inch').val()) || 0;
            var panelType = $('input[name="panel_type"]:checked').val();

            var pricePerBend = <?= number_format(getPaymentSetting('price_per_bend'),2) ?>;
            var pricePerHem = <?= number_format(getPaymentSetting('price_per_hem'),2) ?>;
            var extraCostPerFoot = 0;

            if (panelType === 'vented') {
                extraCostPerFoot = parseFloat(<?= number_format(getPaymentSetting('vented'),2) ?>) || 0;
            } else if (panelType === 'drip_stop') {
                extraCostPerFoot = parseFloat(<?= number_format(getPaymentSetting('drip_stop'),2) ?>) || 0;
            }

            var totalLength = lengthFeet + (lengthInch / 12);
            var totalWidth = widthFeet + (widthInch / 12);

            var bendCost = bends * pricePerBend;
            var hemCost = hems * pricePerHem;

            var soldByFeet = 1;
            var finalPrice = (basePrice * (totalLength * totalWidth) * quantity) + bendCost + hemCost + extraCostPerFoot;
            
            var resultHtml = `
            <div class="d-flex justify-content-center gap-3 text-center mb-2">
                <div class="mb-2">
                    <div><strong>Product Item: ${itemName}</strong></div>
                    <div>Base Price: $${itemPrice}</div>
                </div>
            </div>

            <div class="d-flex justify-content-between gap-3 text-center mt-3 mb-2">
                <div class="mb-2">
                    <div><strong>Product System: ${systemName}</strong></div>
                    <div>Multiplier: ${systemMultiplier}</div>
                </div>
                <div class="mb-2">
                    <div><strong>Product Category: ${categoryName}</strong></div>
                    <div>Multiplier: ${categoryMultiplier}</div>
                </div>
                <div class="mb-2">
                    <div><strong>Product Line: ${lineName}</strong></div>
                    <div>Multiplier: ${lineMultiplier}</div>
                </div>
                <div class="mb-2">
                    <div><strong>Product Type: ${typeName}</strong></div>
                    <div>Multiplier: ${typeMultiplier}</div>
                </div>
                <div class="mb-2">
                    <div><strong>Product Type: ${trimColorName}</strong></div>
                    <div>Multiplier: ${trimColorMultiplier}</div>
                </div>
            </div>  

            <h5 class="text-center pt-3 fs-5 fw-bold mb-3">Initial Product Cost: $<span id="product-cost">${basePrice}</span></h5>
            `;

            if (bends > 0 || hems > 0) {
                resultHtml += `
                <div class="row text-center mb-2">
                    <div class="mb-2 col-6">
                        <div><strong>Bends:</strong></div>
                        <div>${bends}</div>
                    </div>
                    <div class="mb-2 col-6">
                        <div><strong>Hems:</strong></div>
                        <div>${hems}</div>
                    </div>
                </div>
                <div class="row text-center mb-1">
                    <div class="mb-2 col-6">
                        <div><strong>Price Per Bend:</strong></div>
                        <div>$${pricePerBend}</div>
                    </div>
                    <div class="mb-2 col-6">
                        <div><strong>Price Per Hem:</strong></div>
                        <div>$${pricePerHem}</div>
                    </div>
                </div>
                `;
            }

            resultHtml += `
                <h5 class="text-center pt-3 fs-5 fw-bold">Final Product Cost: $<span id="multiplied-product-cost">${finalPrice}</span></h5>
            `;

            $('#selected-info').html(resultHtml).show();
        });


    });

</script>