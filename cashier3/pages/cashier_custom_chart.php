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

if (isset($_POST['fetch_modal'])) {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $category_details = getCategoryDetails($category);
    $image = $category_details['custom_chart_image'] ?? '';

    $relativePath = '../images/default_trim.png';
    $checkPath = '../../images/default_trim.png';

    if ($image && file_exists($checkPath)) {
        ?>
        <img id="chartImage" src="<?= $relativePath ?>" alt="Custom Chart Image"
             class="img-fluid w-100" style="height: 60vw;">
        <?php
    } else {
        ?>
        <div class="text-muted fst-italic">No chart image available.</div>
        <?php
    }
}
?>
