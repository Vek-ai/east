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

if(isset($_POST['fetch_available'])){
    $id = mysqli_real_escape_string($conn, $_POST['id']);

    $checkQuery = "SELECT * FROM inventory WHERE Product_id = '$id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        $product = getProductDetails($id);
        $default_image = '../images/product/product.jpg';
        $picture_path = !empty($product['main_image'])
        ? "../" .$product['main_image']
        : $default_image;
        ?>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Available Colors</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center justify-content-start fw-bold mb-3">
                        <div class="d-flex align-items-center w-100">
                            <img src="<?= $picture_path ?>" class="rounded-circle me-2" alt="product-image" width="56" height="56">
                            <span><?= getProductName($id) ?></span>
                        </div>
                    </div>
                    <table id="productTable" class="table align-middle text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Color</th>
                                <th scope="col">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                while($row = mysqli_fetch_assoc($result)){
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex mb-0 gap-8">
                                            <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromColorID($row['color_id']) ?>"></a>
                                            <?= getColorName($row['color_id']) ?>
                                        </div>
                                    </td>
                                    <td><?= $row['quantity_ttl'] ?></td>
                                </tr>
                                <?php
                                }                    
                            ?>
                        </tbody>
                    </table>
                    <div class="fs-5 fw-bold mt-3">
                        <h5>If desired Quantity is not shown, then there may be a manufacture lead time of 2-3 days.</h5>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
<?php
    }
}