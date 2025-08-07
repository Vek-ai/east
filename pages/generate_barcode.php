<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
?>
<style>
    /* Ensure everything is visible in print, but hide what you don't need */
    @media print {
        body * {
            visibility: hidden;
        }
        /* Only display the card with the barcode */
        .card {
            visibility: visible;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            box-shadow: none;
        }
        /* Hide the print button during printing */
        .card button {
            display: none;
        }
        /* Ensure the barcode is centered and visible */
        #barcode img {
            visibility: visible;
            width: 100%; 
            height: auto; 
            max-width: 100%; 
        }
        /* Ensure the UPC code is visible */
        #upc-code {
            visibility: visible;
        }
    }

    #barcode img {
        visibility: visible;
        width: 100%; 
        height: auto; 
        max-width: 100%; 
    }
</style>


<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h4 class="font-weight-medium fs-14 mb-0">Generate Barcode</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="#">Product Properties</a>
            </li>
            <li class="breadcrumb-item text-muted active" aria-current="page">Generate Barcode</li>
          </ol>
        </nav>
      </div>
      <div class="d-none d-sm-flex gap-3 no-block justify-content-end align-items-center">
        <div class="d-flex gap-2">
          <div>
            <small>This Month</small>
            <h4 class="text-primary mb-0">$58,256</h4>
          </div>
          <div class="breadbar"></div>
        </div>
        <div class="d-flex gap-2">
          <div>
            <small>Last Month</small>
            <h4 class="text-secondary mb-0">$58,256</h4>
          </div>
          <div class="breadbar2"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card card-body">
  
  
  <div id="barcode" class="text-center">
    <!-- Barcode image here -->
  </div>
  <h3 id="upc-code" class="text-center mt-3 font-weight-bold"></h3>

  <div class="row">
    <div class="col">
      
    </div>
    <div class="col text-end">
      <button id="generate-btn" class="btn btn-primary mt-3">Generate Barcode</button>
      <button id="print-btn" class="btn btn-secondary mt-3" style="display:none;" onclick="window.print()">Print Barcode</button>
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
    $(document).ready(function(){
        $('#generate-btn').on('click', function(){
            $.ajax({
                url: 'pages/generate_barcode_ajax.php',
                type: 'POST',
                success: function(response) {
                    var result = JSON.parse(response);
                    $('#barcode').html('<img src="' + result.file_path + '" alt="UPC Barcode" class="img-fluid">');
                    $('#upc-code').text('UPC Code: ' + result.upc_code);
                    $('#print-btn').show();
                }
            });
        });
    });
</script>