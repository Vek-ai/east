<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_REQUEST['query'])) {
    $searchQuery = isset($_REQUEST['query']) ? mysqli_real_escape_string($conn, $_REQUEST['query']) : '';
    $supplier_id = mysqli_real_escape_string($conn, $_REQUEST['supplier_id']);

    $query_product = "
        SELECT
            *
        FROM
            inventory AS i
        LEFT JOIN product AS p
        ON
            i.Product_id = p.product_id
        WHERE
            i.supplier_id = '$supplier_id'
    ";

    if (!empty($searchQuery)) {
        $query_product .= " AND (p.product_item LIKE '%$searchQuery%' OR p.description LIKE '%$searchQuery%')";
    }

    $query_product .= " ORDER BY i.Date DESC";
    $result_product = mysqli_query($conn, $query_product);
    $tableHTML = "";

    if (mysqli_num_rows($result_product) > 0) {
        while ($row_product = mysqli_fetch_array($result_product)) {

            $default_image = 'images/product/product.jpg';

            $picture_path = (!empty($row_product['main_image']) && file_exists($row_product['main_image'])) 
            ? $row_product['main_image'] 
            : $default_image;

            $tableHTML .= '
                <tr>
                    <td>
                        <a href="javascript:void(0);" id="view_product_details" data-id="' . htmlspecialchars($row_product['product_id']) . '" class="d-flex align-items-center">
                            <div class="d-flex align-items-center">
                                <img src="'. htmlspecialchars($picture_path) .'" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                <div class="ms-3 text-wrap">
                                    <h6 class="fw-semibold mb-0 fs-4">'. htmlspecialchars($row_product['product_item']) .' ' . htmlspecialchars($dimensions) .'</h6>
                                </div>
                            </div>
                        </a>
                    </td>
                    <td>
                        <div class="d-flex mb-0 gap-8">
                            <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color: ' . htmlspecialchars(getColorHexFromColorID($row_product['i.color_id'])) .'"></a> '
                            . htmlspecialchars(getColorName($row_product['i.color_id'])) .'
                        </div>
                    </td>
                    <td><h6 class="mb-0 fs-4">' . getProductCategoryName(htmlspecialchars($row_product['product_category'])) . '</h6></td>
                    <td><h6 class="mb-0 fs-4">' . getWarehouseName(htmlspecialchars($row_product['Warehouse_id'])) . '</h6></td>
                    <td><h6 class="mb-0 fs-4">' . htmlspecialchars($row_product['quantity_ttl']) . '</h6></td>
                </tr>';
        }
    } else {
        $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    
    //echo $tableHTML;
    echo $tableHTML;
}