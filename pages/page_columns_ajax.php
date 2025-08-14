<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'truss_type';
$test_table = 'truss_type_excel';
$main_primary_key = getPrimaryKey($table);

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];    

    if ($action === 'load_page_columns') {
        $page_id    = intval($_POST['page_id'] ?? 0);
        $profile_id = intval($_POST['profile_id'] ?? 0);

        if (!$page_id || !$profile_id) {
            echo '<tr><td colspan="5">Invalid parameters.</td></tr>';
            exit;
        }

        $hiddenCols = [];
        $result = $conn->query("
            SELECT page_column_id 
            FROM hidden_page_column_roles
            WHERE page_id = $page_id
            AND profile_id = $profile_id
        ");
        while ($row = $result->fetch_assoc()) {
            $hiddenCols[] = $row['page_column_id'];
        }

        $result = $conn->query("
            SELECT id, column_name, display_name, data_type, sort_order, default_visible
            FROM page_columns
            WHERE page_id = $page_id
            ORDER BY sort_order ASC
        ");

        if ($result->num_rows > 0) {
            while ($col = $result->fetch_assoc()) {
                $isVisible = !in_array($col['id'], $hiddenCols);

                echo '<tr>';
                echo '<td>' . htmlspecialchars($col['column_name']) . '</td>';
                echo '<td>' . htmlspecialchars($col['display_name']) . '</td>';
                echo '<td class="text-center">
                        <input type="checkbox" 
                                class="toggle-visible" 
                                data-id="' . $col['id'] . '" 
                                data-pageid="' . $page_id . '" 
                                data-profileid="' . $profile_id . '" 
                            ' . ($isVisible ? 'checked' : '') . '>
                    </td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="5">No columns found for this page.</td></tr>';
        }
    }

    if ($action === 'toggle_column') {
        $page_id    = intval($_POST['page_id'] ?? 0);
        $profile_id = intval($_POST['profile_id'] ?? 0);
        $col_id     = intval($_POST['column_id'] ?? 0);
        $visible    = intval($_POST['visible'] ?? 1);

        if (!$page_id || !$profile_id || !$col_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
            exit;
        }

        if ($visible === 0) {
            $conn->query("
                INSERT IGNORE INTO hidden_page_column_roles (page_id, page_column_id, profile_id)
                VALUES ($page_id, $col_id, $profile_id)
            ");
        } else {
            $conn->query("
                DELETE FROM hidden_page_column_roles
                WHERE page_id = $page_id AND page_column_id = $col_id AND profile_id = $profile_id
            ");
        }

        echo json_encode(['status' => 'success']);
        exit;
    }

    mysqli_close($conn);
}
?>
