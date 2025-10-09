<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'product_type';
$test_table = 'product_type_excel';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action === 'fetch_table') {
        $permission = $_SESSION['permission'] ?? '';
        $query = "SELECT * FROM product_abr ORDER BY date_added DESC";
        $result = mysqli_query($conn, $query);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $id             = $row['id'];
            $product_id     = $row['product_id'];
            $category       = $row['category'];
            $profile        = $row['profile'];
            $grade          = $row['grade'];
            $gauge          = $row['gauge'];
            $type           = $row['type'];
            $color          = $row['color'];
            $length         = $row['length'];

            $data[] = [
                'id'                => $id,
                'product_id'        => $product_id,
                'category'          => getProductCategoryName($category),
                'profile'           => getProfileTypeName($profile),
                'grade'             => getGradeName($grade),
                'gauge'             => getGaugeName($gauge),
                'type'              => getProductTypeName($type),
                'color'             => getColorName($color),
                'length'            => getDimensionName($length),
                'category_id'          => $category,
                'profile_id'           => $profile,
                'grade_id'             => $grade,
                'gauge_id'             => $gauge,
                'type_id'              => $type,
                'color_id'             => $color,
                'length_id'            => $length
            ];
        }

        echo json_encode(['data' => $data]);
        exit;
    }

    if ($action === 'fetch_product_id') {
        $where = [];

        $fields = ['category', 'profile', 'grade', 'gauge', 'type', 'color', 'length'];
        foreach ($fields as $f) {
            if (!empty($_POST[$f])) {
                $value = mysqli_real_escape_string($conn, $_POST[$f]);
                $where[] = "$f = '$value'";
            }
        }

        if (!empty($where)) {
            $query = "
                SELECT product_id 
                FROM product_abr 
                WHERE " . implode(' AND ', $where) . "
                ORDER BY date_added DESC 
                LIMIT 1
            ";

            $result = mysqli_query($conn, $query);
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                echo $row['product_id'];
            } else {
                echo 'Not Found';
            }
        } else {
            echo '';
        }
    }



    mysqli_close($conn);
}
?>
