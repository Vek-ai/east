<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h4 class="font-weight-medium fs-14 mb-0">Scan Barcode</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="#">Product Properties</a>
            </li>
            <li class="breadcrumb-item text-muted active" aria-current="page">Scan Barcode</li>
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
    <h3 id="barcode-upc"></h3>
  </div>
  <h3 id="upc-code" class="text-center mt-3 font-weight-bold"></h3>
  <input type="text" id="upc-input" />
  <div class="row">
    <div class="col">
      
    </div>
    <div class="col text-end">
      <button id="scan-btn" class="btn btn-primary mt-3">Scan Barcode</button>
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
        $('#scan-btn').on('click', function(){
            $('#barcode-upc').text("UPC: "+$("#upc-input").val());
        });
    });
</script>