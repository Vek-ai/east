<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

$trim_id = 43;
$panel_id = 46;

if (isset($_REQUEST['query'])) {
    $searchQuery = isset($_REQUEST['query']) ? mysqli_real_escape_string($conn, $_REQUEST['query']) : '';
    $type_id = isset($_REQUEST['type_id']) ? mysqli_real_escape_string($conn, $_REQUEST['type_id']) : '';
    $line_id = isset($_REQUEST['line_id']) ? mysqli_real_escape_string($conn, $_REQUEST['line_id']) : '';
    $category_id = isset($_REQUEST['category_id']) ? mysqli_real_escape_string($conn, $_REQUEST['category_id']) : '';
    $onlyInStock = isset($_REQUEST['onlyInStock']) ? filter_var($_REQUEST['onlyInStock'], FILTER_VALIDATE_BOOLEAN) : false;

    $query_coil = "SELECT * FROM coil_transaction as ct left join coil as c on ct.coilid = c.coil_id";

    if (!empty($searchQuery)) {
        $query_coil .= " AND (ct.coil LIKE '%$searchQuery%')";
    }

    if (!empty($category_id)) {
        $query_coil .= " AND c.category = '$category_id'";
    }else{
        $query_coil .= " AND (c.category = '$trim_id' OR c.category = '$panel_id')";
    }

    $result_coil = mysqli_query($conn, $query_coil);

    $tableHTML = "";

    if (mysqli_num_rows($result_coil) > 0) {
        while ($row_coil = mysqli_fetch_array($result_coil)) {

            if(!empty($product_arr['main_image'])){
                $picture_path = $product_arr['main_image'];
            }else{
                $picture_path = "images/product/product.jpg";
            }

            $date = strtotime($row_coil['date']);

            $tableHTML .= '
            <tr>
                <td class="text-center">
                    <a href="#">
                        <div class="d-flex align-items-center">
                            <img src="'.$picture_path.'" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                            <div class="ms-3">
                                <h6 class="fw-semibold mb-0 fs-4">'. $row_coil['coil'] .'</h6>
                            </div>
                        </div>
                    </a>
                </td>
                <td class="text-center"><h6 class="mb-0 fs-4">'. date('Y-m-d', $date) .'</h6></td>
                <td class="text-center"><h6 class="mb-0 fs-4">'. $row_coil['remaining_length'] .'</h6></td>
                <td>
                    <a class="fs-6 text-muted" href="#" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                        <button class="btn btn-primary">
                            <i class="fa fa-exchange"></i> Add to Warehouse
                        </button> 
                    </a>
                </td>
            </tr>';
        }
    } else {
        $tableHTML .= '<tr><td colspan="8" class="text-center">No products found</td></tr>';
    }
    
    
    echo $tableHTML;
}



