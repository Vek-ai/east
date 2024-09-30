<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

function findCartKey($cart, $product_id, $line) {
    foreach ($cart as $key => $item) {
        if ($item['product_id'] == $product_id && $item['line'] == $line) {
            return $key;
        }
    }
    return false;
}

if(isset($_POST['fetch_drawing'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    ?>
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                font-family: Arial, sans-serif;
            }

            .container {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            #drawingCanvas {
                border: 1px solid black;
                cursor: crosshair;
            }

            .controls {
                margin-top: 10px;
            }

            label {
                margin-right: 10px;
            }

            input {
                width: 80px;
                margin-right: 10px;
            }

            select {
                margin-right: 10px;
            }

            .length-angle-pair {
                display: flex;
                align-items: center;
                margin-top: 5px;
            }

            #totalLength,
            #totalCost {
                font-weight: bold;
                margin-bottom: 10px;
            }

            .button-container {
                display: flex;
                justify-content: space-between;
                width: 100%;
                margin-top: 10px;
            }

            .button-container button {
                padding: 5px 10px;
                font-size: 16px;
                cursor: pointer;
            }
        </style>
        <div class="card-body">
            <div class="product-details table-responsive text-nowrap">
                <form id="drawingForm">
                    <div class="container">
                        <p>Sample code for Line drawing that we can integrate, the colors would be the colors of the selected product.</p>
                        <p>Its not yet optimized for touchscreen, please use Desktop to test.</p>
                        <canvas id="drawingCanvas" width="800" height="600"></canvas>
                        
                        <input type="hidden" id="custom_trim_id" value="<?= $id ?>">
                        <input type="hidden" id="custom_trim_line" value="<?= $line ?>">
                        <div class="controls" id="controls">
                            <div id="totalLength"></div>
                            <div id="totalCost"></div>
                            <div id="lengthAnglePairs"></div>
                        </div>
                    </div>
                </form>
                
            </div>
        </div>
    <?php
}