<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';


if (isset($_REQUEST['query'])) {
    $searchQuery = $_REQUEST['query'];
    if (empty($searchQuery)) {
        $query_product = "SELECT * FROM product WHERE hidden = '0'";
    } else {
        $query_product = "SELECT * FROM product WHERE (product_item LIKE '%$searchQuery%' OR description LIKE '%$searchQuery%') AND hidden = '0'";
    }
    $result_product = mysqli_query($conn, $query_product);
    
    $tableRows = '';
    if (mysqli_num_rows($result_product) > 0) {
        while ($row_product = mysqli_fetch_array($result_product)) {
            $tableRows .= '
            <tr>
                <td>
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault1">
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="../assets/images/products/s1.jpg" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                        <div class="ms-3">
                            <h6 class="fw-semibold mb-0 fs-4">'. $row_product['product_item'] .'</h6>
                        </div>
                    </div>
                </td>
                <td><p class="mb-0">'. $row_product['product_type'] .'</p></td>
                <td><p class="mb-0">'. $row_product['product_line'] .'</p></td>
                <td><p class="mb-0">'. $row_product['product_category'] .'</p></td>
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
        $tableRows = '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    echo $tableRows;
}


