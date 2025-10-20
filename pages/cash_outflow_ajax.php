<?php
session_start();
require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = 'cash_flow';
$test_table = 'cash_flow_excel';

$permission = $_SESSION['permission'];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
        $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');

        $checkQuery = "SELECT 1 FROM cash_outflows WHERE id = '$id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            $updateQuery = "UPDATE cash_outflows 
                            SET description = '$description', 
                                notes = '$notes'
                            WHERE id = '$id'";

            if (mysqli_query($conn, $updateQuery)) {
                echo "update-success";
            } else {
                echo "Error updating cash outflows: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO cash_outflows 
                            (description, notes) 
                            VALUES ('$description', '$notes')";
            if (mysqli_query($conn, $insertQuery)) {
                echo "add-success";
            } else {
                echo "Error adding cash outflows: " . mysqli_error($conn);
            }
        }
    }

    if ($action == 'fetch_table') {
        $query = "SELECT * FROM cash_outflows ORDER BY date_added DESC";
        $result = mysqli_query($conn, $query);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $dateObj = new DateTime($row['date_added']);
            $data[] = [
                'id'             => $row['id'],
                'description'    => $row['description'],
                'notes'          => $row['notes'],
                'date_display'   => $dateObj->format('m/d/Y'),
                'date'           => $dateObj->format('Y-m-d'),
                'month'          => $dateObj->format('n'),
                'year'           => $dateObj->format('Y')
            ];
        }

        echo json_encode(['data' => $data]);
        exit;
    }

    if ($action == 'fetch_modal_content') {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM cash_outflows WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
        }
        ?>
            <div class="row pt-3">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" id="description" name="description" class="form-control"  value="<?= $row['description'] ?? '' ?>"/>
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4"></div>

                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="5"><?= $row['notes'] ?? '' ?></textarea>
                    </div>
                </div>
            </div>

            <input type="hidden" id="id" name="id" class="form-control"  value="<?= $id ?>"/>
        <?php
    }

    mysqli_close($conn);
}
?>
