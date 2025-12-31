<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_out_of_stock_modal'])){
    ?>
    <div class="card">
        <div class="card-body">
            <h5 class="mb-3 fs-6 fw-semibold text-center">Out of Stock</h5>
        </div>
    </div>
<?php
    
}