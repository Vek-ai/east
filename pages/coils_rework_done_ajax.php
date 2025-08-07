<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])){
   $action = $_REQUEST['action'];

    if ($action == 'coil_tag_release') {
        $coil_defective_id = intval($_POST['coil_defective_id']);

        if ($coil_defective_id <= 0) {
            echo "Invalid coil ID";
            exit;
        }

        $check = mysqli_query($conn, "SELECT * FROM coil_defective WHERE coil_defective_id = $coil_defective_id");
        if (mysqli_num_rows($check) > 0) {
            $coil = mysqli_fetch_assoc($check);

            $update = "
                UPDATE coil_defective 
                SET status = 3 
                WHERE coil_defective_id = $coil_defective_id
            ";

            if (mysqli_query($conn, $update)) {

                $cols = [
                    'entry_no', 'warehouse', 'color_family', 'color_abbreviation', 'paint_supplier',
                    'paint_code', 'stock_availability', 'multiplier_category', 'actual_color', 'color_close',
                    'coil_no', 'date', 'supplier', 'supplier_name', 'color_sold_as', 'color_sold_name',
                    'product_id', 'og_length', 'weight', 'thickness', 'width', 'grade', 'coating', 'tag_no',
                    'invoice_no', 'remaining_feet', 'last_inventory_count', 'coil_class', 'gauge', 'grade_no',
                    'year', 'month', 'extracting_price', 'price', 'avg_by_color', 'total', 'current_weight',
                    'lb_per_ft', 'contract_ppf', 'contract_ppcwg', 'invoice_price', 'round_width',
                    'hidden', 'main_image', 'supplier_tag'
                ];

                $columns = implode(", ", $cols) . ", tagged_defective, tagged_date, tagged_note, status";
                $values = [];

                foreach ($cols as $col) {
                    $val = $coil[$col] ?? null;
                    $values[] = is_null($val) ? "NULL" : "'" . mysqli_real_escape_string($conn, $val) . "'";
                }

                $values[] = 0;      // tagged_defective
                $values[] = "NULL"; // tagged_date
                $values[] = "NULL"; // tagged_note
                $values[] = 0;      // status

                $values_str = implode(", ", $values);

                $insert_sql = "
                    INSERT INTO coil_product ($columns)
                    VALUES ($values_str)
                ";

                if (mysqli_query($conn, $insert_sql)) {
                    echo "success";
                } else {
                    echo "Error inserting into coil_product: " . mysqli_error($conn);
                }

            } else {
                echo "Error updating status: " . mysqli_error($conn);
            }
        } else {
            echo "Coil not found in defective list.";
        }

        exit;
    }

   

}





