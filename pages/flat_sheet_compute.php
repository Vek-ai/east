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
        <h4 class="card-title">Flat Sheet Computation</h4>
      </div>
    </div>

    <div class="row pt-3">
      <div class="col-md-6">
          <label class="form-label">Coil</label>
          <div class="mb-3">
              <select id="coilSelect" class="form-control select2-add" name="coil">
                  <option value="">Select Coil...</option>
                  <optgroup label="Coil">
                      <?php
                      $query_coil = "SELECT * FROM coil WHERE hidden = '0'";
                      $result_coil = mysqli_query($conn, $query_coil);
                      while ($row_coil = mysqli_fetch_array($result_coil)) {
                      ?>
                        <option 
                            value="<?= $row_coil['coil_id'] ?>" 
                            data-length="<?= $row_coil['length'] ?>" 
                            data-width="<?= $row_coil['width'] ?>" 
                            data-color="<?= getColorName($row_coil['color']) ?>" 
                            data-hexcolor="<?= addslashes(getColorHexFromColorID($row_coil['color'])) ?>">
                                <?= $row_coil['coil'] ?>
                        </option>
                      <?php } ?>
                  </optgroup>
              </select>
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
          <label class="form-label">Remaining Length</label>
          <div class="mb-3">
              <textarea class="form-control w-100" id="remainingLength" rows="3" readonly></textarea>
          </div>
      </div>
      <div class="col-md-12">
          <button class="btn ripple btn-primary" type="button" id="saveComputation">
              <i class="fe fe-hard-drive"></i> Save
          </button>
      </div>
  </div>
  </div>
</div>


<script>
    $(document).ready(function() {
        function computeLength() {
            var coilLength = Number($('#coilSelect option:selected').data('length'));
            var length = Number($('#length').val());
            var quantity = Number($('#quantity').val());

            if (!isNaN(coilLength) && coilLength > 0 && length > 0 && quantity > 0) {
                var computedLength = coilLength - (length * quantity);
                
                if (computedLength < 0) {
                    $('#remainingLength').val('Formula results to a negative remaining length!');
                } else {
                    var wholeNumberLength = Math.floor(computedLength);
                    var decimalPart = (computedLength - wholeNumberLength).toFixed(2);

                    $('#remainingLength').val('Remaining Length: ' + computedLength);
                }
            }
        }

        function formatOption(state) {
            if (!state.id) {
                return state.text;
            }

            var hexcolor = $(state.element).data('hexcolor');
            var color = $(state.element).data('color');
            var width = $(state.element).data('width');

            var $state = $(`
                <span class="d-flex align-items-center">
                    ${state.text} - 
                    <span class="rounded-circle d-block p-1 me-2" style="background-color: ${hexcolor}; width: 16px; height: 16px;"></span> ${color} - 
                    ${width} 
                </span>
            `);

            return $state;
        }


        $('#coilSelect').select2({
            placeholder: "Select One",
            templateResult: formatOption,
            templateSelection: formatOption
        });

        $('#saveComputation').click(function() {
            var coil_id = $('#coilSelect option:selected').val();
            var length = Number($('#length').val());
            var quantity = Number($('#quantity').val());

            if(coil_id && length > 0 && quantity > 0){
              $.ajax({
                  url: "pages/flat_sheet_compute_ajax.php",
                  type: "POST",
                  data: {
                      coil_id: coil_id,
                      length: length,
                      quantity: quantity,
                      save_computation: 'save_computation'
                  },
                  success: function(data) {
                    if(data.trim() == 'success'){
                        alert('Successfully saved!');
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
            }else{
              alert("Please provide coil, length and quantity.");
            }
        });

        $('#coilSelect, #length, #quantity').change(function() {
            computeLength();
        });
    });


</script>