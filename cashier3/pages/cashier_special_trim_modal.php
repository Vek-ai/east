<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
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

if(isset($_POST['fetch_modal'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $line = mysqli_real_escape_string($conn, $_POST['line']);
    $product_details = getProductDetails($id);
    $category_id = $product_details['product_category'];

    $key = findCartKey($_SESSION["cart"], $id, $line);

    if ($key !== false) {
        $cart_item = $_SESSION["cart"][$key];
    
        $price = $cart_item['unit_price'];
        $quantity = $cart_item['quantity_cart'];
        $length = $cart_item['estimate_length'];
        $img = $cart_item['custom_trim_src'];
        $drawing_data = htmlspecialchars($cart_item['drawing_data'], ENT_QUOTES, 'UTF-8');

    }
    
    $hasImage = !empty($img);
    $images_directory = "../images/drawing/";

    ?>
        <div class="card-body">
            <div class="product-details table-responsive text-nowrap">
                <form id="specialTrimForm">
                    <div class="container">
                        <h4 class="text-center">Trim Draw Box</h4>

                        <input type="hidden" id="custom_trim_id" name="id" value="<?= $id ?>">
                        <input type="hidden" id="custom_trim_line" name="line" value="<?= $line ?>">
                        <input type="hidden" id="drawing_data" name="drawing_data" value="<?= $drawing_data ?>">
                    
                        <div class="d-flex justify-content-center flex-column align-items-center mb-3">
                            <div class="mb-2 text-center text-secondary" style="font-size: 14px; font-weight: 500;">
                                Click the image to start drawing
                            </div>
                            <!-- Canvas Wrapper with Relative Position -->
                            <div id="drawingContainer" data-id="<?= $id ?>" data-line="<?= $line ?>" data-drawing="<?= $drawing_data ?>" class="drawingContainer d-flex justify-content-center align-items-center border border-dashed" style="width: 700px; height: 500px; position: relative; cursor: pointer;">
                                <img id="drawingImage"
                                    src="<?php echo $hasImage ? htmlspecialchars($images_directory . $img) : ''; ?>"
                                    alt="Drawing"
                                    class="img-fluid h-100 w-auto"
                                    style="<?php echo $hasImage ? '' : 'display: none;'; ?>">

                                <?php if (!$hasImage): ?>
                                    <span id="placeholderText" class="position-absolute text-muted" style="font-size: 18px;">Click here to draw</span>
                                <?php endif; ?>

                                <input type="hidden" name="img_src" id="img_src" value="<?php echo $hasImage ? htmlspecialchars($img) : ''; ?>">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label" for="trim_quantity">Quantity</label>
                                    <input type="number" id="custom_trim_qty" name="quantity" class="form-control mb-1" value="<?= $quantity ?>" placeholder="Enter Quantity">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Length</label>
                                    <select id="custom_trim_length" class="form-control mb-1">
                                        <option value="0">Select length</option>
                                        <?php
                                        $lengths = getInventoryLengths($id);

                                        foreach ($lengths as $entry) {
                                            $length = htmlspecialchars($entry['length']);
                                            $feet = htmlspecialchars($entry['feet']);
                                            $selected = ($feet == 1) ? 'selected' : '';
                                            echo "<option value=\"$feet\" $selected>$length</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label" for="truss_price">Price</label>
                                    <input type="text" id="custom_trim_price" name="price" class="form-control mb-1" value="<?= $price ?>" placeholder="Enter Price">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end align-items-center px-0">
                        <div class="d-flex justify-content-end">
                            <button id="saveDrawing" class="btn btn-success" type="submit">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php
}