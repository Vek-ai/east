<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';
$trim_id = 4;
$panel_id = 3;
$lumber_id = 1;
$truss_id = 2;
$acc_id = 6;
$fastener_id = 5;
if(isset($_POST['fetch_prompt_quantity'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    
    $product_details = getProductDetails($id);
        ?>
        <div class="modal-dialog" role="document">
            <form id="quantity_form" class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Select Quantity</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php
                    if (!empty($product_details)) {
                        $category_id = $product_details['product_category'];
                        $sold_by_feet = $product_details["sold_by_feet"];
                    ?>
                    <input type="hidden" id="product_id" name="product_id" value="<?= $id ?>" />
                    <div class="quantity_input">
                        <div class="mb-2">
                            <label class="fs-5 fw-bold" for="quantity-product">Quantity</label>
                            <input id="quantity-product" name="quantity_product" class="form-control" placeholder="Enter Quantity" list="quantity-product-list" autocomplete="off">
                            <datalist id="quantity-product-list">
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                                <option value="2000">2000</option>
                                <option value="5000">5000</option>
                            </datalist>
                        </div>
                    </div>
                    <div class="length_input">
                        <div class="mb-2 <?= empty($sold_by_feet) ? 'd-none' : '';?>">
                            <fieldset class="p-0 position-relative">
                                <legend class="fs-5 fw-bold">Length</legend>
                                <div class="input-group d-flex align-items-center">
                                    <input class="form-control mr-1" type="number" id="length_feet" name="length_feet"  list="length_feet_datalist" value="<?= $values["estimate_length"] ?>" placeholder="FT" size="5" style="color:#ffffff;">
                                    <datalist id="length_feet_datalist">
                                        <option value="0">0'</option>
                                        <option value="1">1'</option>
                                        <option value="5">5'</option>
                                        <option value="10">10'</option>
                                        <option value="25">25'</option>
                                        <option value="50">50'</option>
                                        <option value="100">100'</option>
                                        <option value="500">500'</option>
                                        <option value="1000">1000'</option>
                                        <option value="2000">2000'</option>
                                        <option value="5000">5000'</option>
                                    </datalist>
                                    <input class="form-control" type="number" id="length_inch" name="length_inch" list="length_inch_datalist" value="<?= $values["estimate_length_inch"]; ?>" placeholder="IN" size="5" style="color:#ffffff;">
                                    <datalist id="length_inch_datalist">
                                        <option value="0">0"</option>
                                        <option value="1">1"</option>
                                        <option value="2">2"</option>
                                        <option value="3">3"</option>
                                        <option value="4">4"</option>
                                        <option value="5">5"</option>
                                        <option value="6">6"</option>
                                        <option value="7">7"</option>
                                        <option value="8">8"</option>
                                        <option value="9">9"</option>
                                        <option value="10">10"</option>
                                        <option value="11">11"</option>
                                    </datalist>
                                </div>
                            </fieldset>
                        </div>
                        <div class="mb-2 <?= (($category_id == $fastener_id) || $id == 21) ? '' : 'd-none';?>">
                            <label class="fs-5 fw-bold" for="quantity-product">Select Case</label>
                            <div class="input-group d-flex align-items-center">
                                <select class="form-control mr-1" id="case_type" name="case_type" style="color:#ffffff;">
                                    <option>100</option>
                                    <option>250</option>
                                    <option>500</option>
                                    <option>1000</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="panel_options">
                        <div class="mb-2 <?= ($category_id == $panel_id)  ? '' : 'd-none';?>">
                            <label class="fs-5 fw-bold" for="quantity-product">Select Panel Type</label>
                            <div class="input-group d-flex align-items-center">
                                <div class="form-control mr-1">
                                    <input type="radio" id="solid_panel" name="panel_type" value="solid" checked> Solid
                                </div>
                                <div class="form-control mr-1">
                                    <input type="radio" id="vented_panel" name="panel_type" value="vented"> Vented
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="product_cost_display">
                        <h5 class="text-center pt-3 fs-5 fw-bold">Product Cost: $<span id="product-cost">0.00</span></h5>
                    </div>
                    <script>
                        $(document).ready(function () {
                            function calculateProductCost() {
                                const quantity = parseInt($('#quantity-product').val()) || 0;
                                const lengthFeet = parseInt($('#length_feet').val()) || 0;
                                const lengthInch = parseInt($('#length_inch').val()) || 0;
                                const totalLength = lengthFeet + lengthInch / 12;

                                const panelType = $('input[name="panel_type"]:checked').val();
                                
                                const soldByFeet = <?= $sold_by_feet; ?>;
                                const unitPrice = <?= $product_details["unit_price"]; ?>;

                                let productPrice = 0;
                                const extraCostPerFoot = panelType === "vented" ? 0.50 : 0;

                                if (soldByFeet == 1) {
                                    productPrice = quantity * totalLength * (unitPrice + extraCostPerFoot);
                                } else {
                                    console.log(extraCostPerFoot)
                                    productPrice = quantity * unitPrice + (extraCostPerFoot);
                                }

                                $('#product-cost').text(productPrice.toFixed(2));
                            }
                            $('#quantity-product, #length_feet, #length_inch').on('input', calculateProductCost);
                            $('input[name="panel_type"]').on('change', calculateProductCost);
                        });
                    </script>
                    <?php
                    }else{
                    ?>
                    <h5 class="text-center">Product Not Found! ID: <?= $id; ?>  </h5>
                    <?php
                    }
                    ?>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success ripple btn-secondary" data-bs-dismiss="modal" type="submit">Add to Cart</button>
                    <button class="btn btn-danger ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </form>
            </div>
        </div>
<?php
    
}