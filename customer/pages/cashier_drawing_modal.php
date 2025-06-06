<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_POST['fetch_drawing'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $drawing_data = $_POST['drawing_data'];
    if (is_array($drawing_data)) {
        $drawing_data = json_encode($drawing_data, JSON_UNESCAPED_UNICODE);
    }
    ?>
        <input type="hidden" id="initial_drawing_data" value='<?= htmlspecialchars($drawing_data, ENT_QUOTES, "UTF-8") ?>'>
        
        <div class="card-body">
            <div class="product-details table-responsive text-nowrap">
                <form id="drawingForm">
                    <div class="container ">
                        <h4>Trim Draw Box</h4>
                        <div class="d-flex justify-content-center">
                            <div class="position-relative d-inline-block border rounded shadow-sm p-2 bg-light">
                                <div class="position-absolute top-0 end-0 m-2 d-flex gap-2 align-items-center z-3">
                                    <a href="javascript:void(0)" id="undoBtn" class="btn btn-warning btn-sm p-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Undo">
                                        <i class="fas fa-undo-alt"></i>
                                    </a>

                                    <a href="javascript:void(0)" id="redoBtn" class="btn btn-info btn-sm p-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Redo">
                                        <i class="fas fa-redo-alt"></i>
                                    </a>

                                    <a href="javascript:void(0)" id="resetBtn" class="btn btn-danger btn-sm p-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Clear">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>

                                <canvas id="drawingCanvas" width="1000" height="700" class="border rounded bg-white"></canvas>
                                <div class="row mt-0">
                                    <div class="col-md-6 d-flex align-items-center gap-2">
                                        <!--
                                        <button type="button" class="btn p-0 border-0 bg-transparent insert-img" title="Insert Flat Hem">
                                            <img src="../images/flat-hem.svg" alt="Hem 1" style="width: 60px; height: auto;">
                                        </button>
                                        <button type="button" class="btn p-0 border-0 bg-transparent insert-img" title="Insert Open Hem">
                                            <img src="../images/open-hem.svg" alt="Hem 2" style="width: 60px; height: auto;">
                                        </button>
                                        <button type="button" class="btn p-0 border-0 bg-transparent insert-img" title="Insert Arrow">
                                            <img src="../images/arrow.png" alt="Arrow" style="width: 30px; height: auto;">
                                        </button>
                                        -->

                                        <button type="button" class="btn p-0 border-0 bg-transparent insert-arrow-line" title="Click and Drag to Draw Arrow" style="cursor: pointer;">
                                            <i class="fas fa-arrow-right" style="font-size: 30px; color: lightgreen; transform: rotate(315deg);"></i>
                                        </button>

                                        <button type="button" id="triggerUpload" class="btn p-0 border-0 bg-transparent" title="Insert Image" style="cursor: pointer;">
                                            <i class="fas fa-upload" style="font-size: 24px;"></i>
                                        </button>
                                        <input type="file" id="uploadImage" accept="image/*" style="display: none;">
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-end align-items-center gap-2">
                                        <button type="button" id="btn-pencil" class="btn btn-success px-2 py-1" style="display: none;">
                                            Start Drawing
                                        </button>
                                        <button type="button" id="btn-stop" class="btn btn-danger px-2 py-1" style="display: none;">
                                            Stop Drawing
                                        </button>
                                    </div>
                                </div>
                                <div id="lineEditorContainer" class="mt-3 p-2 border rounded">
                                    <div class="row mt-0 mb-2">
                                        <div class="col-md-6 d-flex justify-content-start align-items-center gap-2">
                                            <h5>Edit Lines</h5>
                                        </div>
                                        <div class="col-md-6 d-flex justify-content-end align-items-center gap-2">
                                            <div class="col-md-6 d-flex justify-content-end align-items-center gap-2">
                                                <button type="button" id="btn-show-angles" class="btn btn-success px-2 py-1" style="display: none;">
                                                    Show Angles
                                                </button>
                                                <button type="button" id="btn-hide-angles" class="btn btn-danger px-2 py-1" style="display: none;">
                                                    Hide Angles
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="lineEditorList"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php
}