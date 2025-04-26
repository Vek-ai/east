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

            .controls {
                margin-top: 10px;
                display: flex;
                gap: 10px;
                align-items: center;
            }
            #colorCircle {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                background-color: #ffffff;
                cursor: pointer;
            }
        </style>
        <div class="card-body">
            <div class="product-details table-responsive text-nowrap">
                <form id="drawingForm">
                    <div class="container">
                        <h4>Trim Draw Box</h4>

                        <!-- Canvas Wrapper with Relative Position -->
                        <div class="position-relative d-inline-block rounded">
                            <!-- Action Icons -->
                            <div class="position-absolute top-0 end-0 mt-2 me-2 d-flex gap-2 z-3 align-items-center">
                                <div id="colorCircle"
                                    class="rounded-circle"
                                    style="width: 24px; height: 24px; border: 2px solid #ccc; box-shadow: 0 0 5px rgba(0,0,0,0.2); background-color: #000000; cursor: pointer;"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="bottom"
                                    title="Select line color">
                                </div>

                                <input type="color" id="lineColorPicker" value="#ffffff" style="display:none;" title="Choose line color">

                                <a href="javascript:void(0)" id="undoBtn" class="fs-6" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Undo">
                                    <i class="fas fa-undo-alt text-warning"></i>
                                </a>

                                <a href="javascript:void(0)" id="redoBtn" class="fs-6" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Redo">
                                    <i class="fas fa-redo-alt text-info"></i>
                                </a>

                                <a href="javascript:void(0)" id="resetBtn" class="fs-6" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Clear">
                                    <i class="fas fa-trash-alt text-danger"></i>
                                </a>
                            </div>

                            <!-- Fixed-Size Canvas -->
                            <canvas id="drawingCanvas" width="700" height="500"></canvas>
                        </div>

                        <input type="hidden" id="custom_trim_id" value="<?= $id ?>">
                        <input type="hidden" id="custom_trim_line" value="<?= $line ?>">

                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label" for="trim_quantity">Quantity</label>
                                    <input type="number" value="1" id="trim_quantity" name="quantity" class="form-control mb-1" placeholder="Enter Quantity">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label" for="trim_length">Length</label>
                                    <select id="trim_length" name="length" class="form-select mb-1">
                                        <!-- Default 10ft -->
                                        <option value="10">10 ft</option>
                                        <option value="12">12 ft</option>
                                        <option value="14">14 ft</option>
                                        <option value="16">16 ft</option>
                                        <option value="18">18 ft</option>
                                        <option value="20">20 ft</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label" for="truss_price">Price</label>
                                    <input type="text" id="truss_price" name="price" class="form-control mb-1" placeholder="Enter Price">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php
}