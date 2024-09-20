<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if (isset($_REQUEST['query'])) {
    $searchQuery = $_REQUEST['query'];
    $type_id = $_REQUEST['type_id'];
    $line_id = $_REQUEST['line_id'];
    $category_id = $_REQUEST['category_id'];

    $query_product = "SELECT * FROM product WHERE hidden = '0'";
    if (!empty($searchQuery)) {
        $query_product .= " AND (product_item LIKE '%$searchQuery%' OR description LIKE '%$searchQuery%')";
    }

    if (!empty($type_id)) {
        $query_product .= " AND product_type = '$type_id'";
    }

    if (!empty($line_id)) {
        $query_product .= " AND product_line = '$line_id'";
    }

    if (!empty($category_id)) {
        $query_product .= " AND product_category = '$category_id'";
    }

    $result_product = mysqli_query($conn, $query_product);

    $tableHTML = "";

    if (mysqli_num_rows($result_product) > 0) {
        while ($row_product = mysqli_fetch_array($result_product)) {

            if(!empty($row_product['main_image'])){
                $picture_path = $row_product['main_image'];
            }else{
                $picture_path = "images/product/product.jpg";
            }

            $tableHTML .= '
            <tr>
                <td>
                    <a href="/?page=product_details&product_id='.$row_product['product_id'].'">
                        <div class="d-flex align-items-center">
                            <img src="'.$picture_path.'" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                            <div class="ms-3">
                                <h6 class="fw-semibold mb-0 fs-4">'. $row_product['product_item'] .'</h6>
                            </div>
                        </div>
                    </a>
                </td>
                <td><p class="mb-0">'. getProductTypeName($row_product['product_type']) .'</p></td>
                <td><p class="mb-0">'. getProductLineName($row_product['product_line']) .'</p></td>
                <td><p class="mb-0">'. getProductCategoryName($row_product['product_category']) .'</p></td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="text-bg-success p-1 rounded-circle"></span>
                        <p class="mb-0 ms-2">InStock</p>
                    </div>
                </td>
                <td><h6 class="mb-0 fs-4">$'. $row_product['unit_cost'] .'</h6></td>
                <td>
                    <a class="fs-6 text-muted" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Edit">
                        <i class="ti ti-dots-vertical"></i>
                    </a>
                </td>
            </tr>';
        }
    } else {
        $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    
    //echo $tableHTML;
    echo $tableHTML;
}



