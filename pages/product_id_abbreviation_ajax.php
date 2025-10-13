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

        $grouped = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $key = implode('-', [
                $row['category'],
                $row['profile'],
                $row['grade'],
                $row['gauge'],
                $row['type'],
                $row['color'],
                $row['length']
            ]);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'category'     => getProductCategoryName($row['category']),
                    'profile'      => getProfileTypeName($row['profile']),
                    'grade'        => getGradeName($row['grade']),
                    'gauge'        => getGaugeName($row['gauge']),
                    'type'         => getProductTypeName($row['type']),
                    'color'        => getColorName($row['color']),
                    'length'       => getDimensionName($row['length']),
                    'category_id'  => $row['category'],
                    'profile_id'   => $row['profile'],
                    'grade_id'     => $row['grade'],
                    'gauge_id'     => $row['gauge'],
                    'type_id'      => $row['type'],
                    'color_id'     => $row['color'],
                    'length_id'    => $row['length'],
                    'latest_id'    => $row['product_id']
                ];
            }
        }

        $data = [];
        foreach ($grouped as $group) {
            $attrs = sprintf(
                'class="show_id_history" data-category="%s" data-profile="%s" data-grade="%s" data-gauge="%s" data-type="%s" data-color="%s" data-length="%s"',
                $group['category_id'],
                $group['profile_id'],
                $group['grade_id'],
                $group['gauge_id'],
                $group['type_id'],
                $group['color_id'],
                $group['length_id']
            );

            $link = '<a href="#" ' . $attrs . '>' . htmlspecialchars($group['latest_id']) . '</a>';

            $data[] = array_merge($group, [
                'product_ids_html' => $link
            ]);
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

    if ($action === 'fetch_history') {
        $category = !empty($_POST['category']) ? intval($_POST['category']) : null;
        $profile  = !empty($_POST['profile']) ? intval($_POST['profile']) : null;
        $grade    = !empty($_POST['grade']) ? intval($_POST['grade']) : null;
        $gauge    = !empty($_POST['gauge']) ? intval($_POST['gauge']) : null;
        $type     = !empty($_POST['type']) ? intval($_POST['type']) : null;
        $color    = !empty($_POST['color']) ? intval($_POST['color']) : null;
        $length   = !empty($_POST['length']) ? intval($_POST['length']) : null;

        $conditions = [];
        if (!is_null($category)) $conditions[] = "category = $category";
        if (!is_null($profile))  $conditions[] = "profile = $profile";
        if (!is_null($grade))    $conditions[] = "grade = $grade";
        if (!is_null($gauge))    $conditions[] = "gauge = $gauge";
        if (!is_null($type))     $conditions[] = "type = $type";
        if (!is_null($color))    $conditions[] = "color = $color";
        if (!is_null($length))   $conditions[] = "length = $length";

        $where = $conditions ? implode(' AND ', $conditions) : '1=1';

        $query = "
            SELECT * FROM product_abr
            WHERE $where
            ORDER BY date_added DESC
        ";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) === 0) {
            ?>
            <div class="text-center text-muted py-3">No history found for this combination.</div>
            <?php
            exit;
        }

        $category_name = getProductCategoryName($category);
        $profile_name  = getProfileTypeName($profile);
        $grade_name    = getGradeName($grade);
        $gauge_name    = getGaugeName($gauge);
        $type_name     = getProductTypeName($type);
        $color_name    = getColorName($color);
        $length_name   = getDimensionName($length);
        ?>

        <div class="mb-3">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0 text-center align-middle small">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Profile</th>
                            <th>Grade</th>
                            <th>Gauge</th>
                            <th>Type</th>
                            <th>Color</th>
                            <th>Length</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($category_name) ?></td>
                            <td><?= htmlspecialchars($profile_name) ?></td>
                            <td><?= htmlspecialchars($grade_name) ?></td>
                            <td><?= htmlspecialchars($gauge_name) ?></td>
                            <td><?= htmlspecialchars($type_name) ?></td>
                            <td><?= htmlspecialchars($color_name) ?></td>
                            <td><?= htmlspecialchars($length_name) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>




        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm align-middle">
                <thead class="">
                    <tr>
                        <th class="text-start ps-2">Product ID</th>
                        <th>Date Added</th>
                        <th>Time Added</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        $date = !empty($row['date_added']) ? date('M d, Y', strtotime($row['date_added'])) : '';
                        $time = !empty($row['date_added']) ? date('h:i A', strtotime($row['date_added'])) : '';
                    ?>
                        <tr>
                            <td class="text-start ps-2"><?= htmlspecialchars($row['product_id']) ?></td>
                            <td><?= htmlspecialchars($date) ?></td>
                            <td><?= htmlspecialchars($time) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php
        exit;
    }

    mysqli_close($conn);
}
?>
