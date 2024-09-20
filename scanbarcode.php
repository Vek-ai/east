<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="dark" data-color-theme="Blue_Theme" data-layout="vertical">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 

  <title>Scan Barcode</title>

</head>


<body>
<div id="main-wrapper">
  <div class="page-wrapper">
    <div class="body-wrapper">
      <div class="container-fluid max-width-1000">
        <div class="col-12 card card-body">
          <div class="card card-body">
          
            <div class="row">
              <div class="col-12">
                
              </div>
            </div>

            <div class="row pt-3">
              <div class="col-12">
                <h3>Scan Barcode</h3>
              </div>
            </div>

            <div class="card card-body">
                <form id="scanBarcode" action="scanbarcode.php" method="post" class="p-4 shadow rounded">
                    <div id="barcode" class="text-center mb-3">
                        <h3 id="barcode-upc">UPC: </h3>
                    </div>
                    <div class="form-group">
                        <label for="upc-input" class="font-weight-bold">Enter UPC Code:</label>
                        <input type="text" id="upc-input" name="upc_code" class="form-control" placeholder="Enter or scan UPC code" required />
                    </div>
                    <h3 id="upc-code" class="text-center mt-3 font-weight-bold"></h3>
                    <div class="row">
                        <div class="col text-end">
                            <button type="submit" id="scan-btn" class="btn btn-primary mt-3 w-100">Scan Barcode</button>
                        </div>
                    </div>
                </form>
                
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function(){
        $('#scanBarcode').on('submit', function(event){
            event.preventDefault();
            $('#barcode-upc').text("UPC: " + $("#upc-input").val());
        });
    });
</script>
</html>
