<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_POST['merge'])) {
    $product_original = intval($_POST['product_original'] ?? 0);
    $product_merge = intval($_POST['product_merge'] ?? 0);

    if ($product_original > 0 && $product_merge > 0) {
        $updateProduct = mysqli_query(
            $conn,
            "UPDATE product 
             SET status = 0 
             WHERE product_id = $product_merge"
        );

        $updateAbr = mysqli_query(
            $conn,
            "UPDATE product_abr 
             SET product_id_from_table = $product_original 
             WHERE product_id_from_table = $product_merge"
        );

        if ($updateProduct && $updateAbr) {
            echo "success";
        } else {
            echo "error";
        }
    } else {
        echo "invalid";
    }
}

if (isset($_POST['fetch_data'])) {
    $product_id = (int)($_POST['product_id'] ?? 0);

    if ($product_id > 0) {
        $productIDs = fetchProductIDs($product_id);
        $productArray = array_filter(explode(',', $productIDs));
        ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Product IDs</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($productArray)) { ?>
                    <ul class="mb-0">
                        <?php foreach ($productArray as $id) { ?>
                            <li><?= htmlspecialchars(trim($id)) ?></li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p class="text-muted mb-0">No product IDs found.</p>
                <?php } ?>
            </div>
        </div>
        <?php
    }
}


?>
