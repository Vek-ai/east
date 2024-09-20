<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'includes/phpmailer/vendor/autoload.php';
use Picqer\Barcode\BarcodeGeneratorPNG;

$file_path = "";
$barcode = "";

?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="dark" data-color-theme="Blue_Theme" data-layout="vertical">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 

  <title>Generate Barcode</title>

</head>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
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
        .card button {
            display: none;
        }
        #barcode img {
            visibility: visible;
            width: 100%; 
            height: auto; 
            max-width: 100%; 
        }
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
                <h3>Generate Barcode</h3>
              </div>
            </div>

            <form id="contactForm" class="form-horizontal" method="post" action="generatebarcode.php">
                <div id="barcode" class="text-center">
                    <?php
                    if (isset($_POST['btn-submit'])) {
                        function generateRandomUPC() {
                            $upc_code = '';
                    
                            for ($i = 0; $i < 11; $i++) {
                                $upc_code .= mt_rand(0, 9);
                            }
                    
                            $odd_sum = 0;
                            $even_sum = 0;
                    
                            for ($i = 0; $i < 11; $i++) {
                                if ($i % 2 === 0) {
                                    $odd_sum += $upc_code[$i];
                                } else {
                                    $even_sum += $upc_code[$i];
                                }
                            }
                    
                            $total_sum = (3 * $odd_sum) + $even_sum;
                            $check_digit = (10 - ($total_sum % 10)) % 10;
                    
                            $upc_code .= $check_digit;
                    
                            return $upc_code;
                        }
                    
                        $generator = new BarcodeGeneratorPNG();
                        $upc_code = generateRandomUPC();
                    
                        // Generate the barcode
                        $barcode = $generator->getBarcode($upc_code, $generator::TYPE_UPC_A);
                    
                        // Encode the barcode image data into a base64 string
                        $barcode_base64 = base64_encode($barcode);
                    
                        // Generate the image source for HTML
                        $img_src = 'data:image/png;base64,' . $barcode_base64;
                    
                        // You can then display the barcode in your HTML like this
                        echo '<img src="' . $img_src . '" alt="UPC Barcode">';

                        echo "<h3 id='upc-code' class='text-center mt-3 font-weight-bold'>UPC Code: $upc_code</h3>";
                        ?>
                        <div class="row">
                            <div class="col-6 text-start"></div>
                            <div class="col-6 text-end">
                                <button id="btn-submit" type="submit" name="btn-submit" class="btn btn-primary" style="border-radius: 10%;">Generate Barcode</button>
                                <button id="print-btn" class="btn btn-secondary" onclick="window.print()">Print Barcode</button>
                            </div>
                        </div>
                        
                    <?php
                    }else{
                    ?>
                        <div class="row">
                            <div class="col-6 text-start"></div>
                            <div class="col-6 text-end">
                                <button id="btn-submit" type="submit" name="btn-submit" class="btn btn-primary" style="border-radius: 10%;">Generate Barcode</button>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

</html>
