<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../includes/dbconn.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $WarehouseID = mysqli_real_escape_string($conn, $_POST['WarehouseID']);
        $WarehouseName = mysqli_real_escape_string($conn, $_POST['WarehouseName']);
        $Location = mysqli_real_escape_string($conn, $_POST['Location']);
        $corresponding_user = mysqli_real_escape_string($conn, $_POST['corresponding_user']);
        $contact_person = mysqli_real_escape_string($conn, $_POST['contact_person']);
        $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone']);
        $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
    
        $checkQuery = "SELECT * FROM warehouses WHERE WarehouseID = '$WarehouseID'";
        $result = mysqli_query($conn, $checkQuery);
    
        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "
                UPDATE warehouses 
                SET 
                    WarehouseName = '$WarehouseName', 
                    Location = '$Location', 
                    corresponding_user = '$corresponding_user', 
                    contact_person = '$contact_person', 
                    contact_phone = '$contact_phone', 
                    contact_email = '$contact_email'
                WHERE WarehouseID = '$WarehouseID'
            ";
    
            if (mysqli_query($conn, $updateQuery)) {
                echo "success";
            } else {
                echo "Error updating warehouse: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "
                INSERT INTO warehouses (
                    WarehouseID,
                    WarehouseName, 
                    Location, 
                    corresponding_user, 
                    contact_person, 
                    contact_phone, 
                    contact_email
                ) VALUES (
                    '$WarehouseID', 
                    '$WarehouseName', 
                    '$Location', 
                    '$corresponding_user', 
                    '$contact_person', 
                    '$contact_phone', 
                    '$contact_email'
                )
            ";
    
            if (mysqli_query($conn, $insertQuery)) {
                echo "success";
            } else {
                echo "Error adding warehouse: " . mysqli_error($conn);
            }
        }
    }

    if ($action == "change_status") {
        $warehouse_id = mysqli_real_escape_string($conn, $_POST['warehouse_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE warehouses SET status = '$new_status' WHERE WarehouseID = '$warehouse_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    

    if ($action == "fetch_modal") {
        $warehouse_id = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM warehouses WHERE WarehouseID  = '$warehouse_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">Update Warehouse</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="update_warehouse" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">
                                    <input type="hidden" id="WarehouseID" name="WarehouseID" class="form-control" value="<?= $row['WarehouseID'] ?>"/>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Warehouse Name</label>
                                                <input type="text" id="WarehouseName" name="WarehouseName" class="form-control" value="<?= $row['WarehouseName'] ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Staff In-charge</label>
                                            <div class="mb-3">
                                                <select id="corresponding_user" class="select2-update form-control" name="corresponding_user">
                                                    <option value="" >Select Staff...</option>
                                                    <?php
                                                    $query_staff = "SELECT * FROM staff WHERE status = '1'";
                                                    $result_staff = mysqli_query($conn, $query_staff);            
                                                    while ($row_staff = mysqli_fetch_array($result_staff)) {
                                                        $selected = ($row['corresponding_user'] == $row_staff['staff_id']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_staff['staff_id'] ?>" <?= $selected ?>><?= $row_staff['staff_fname'] ." " .$row_staff['staff_lname'] ?></option>
                                                    <?php   
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Location</label>
                                                <input type="text" id="Location" name="Location" class="form-control" value="<?= $row['Location'] ?>"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-4 mb-12">
                                            <label class="form-label">Contact Person</label>
                                            <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?= $row['contact_person'] ?>" />
                                        </div>
                                        <div class="col-4 mb-6">
                                            <label class="form-label">Contact Phone</label>
                                            <input type="text" id="contact_phone" name="contact_phone" class="form-control phone-inputmask" value="<?= $row['contact_phone'] ?>" />
                                        </div>
                                        <div class="col-4 mb-6">
                                            <label class="form-label">Contact Email</label>
                                            <input type="text" id="contact_email" name="contact_email" class="form-control" value="<?= $row['contact_email'] ?>" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <div class="form-actions">
                                <div class="card-body">
                                    <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                    <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <script>
                $(".phone-inputmask").inputmask("(999) 999-9999");

                $(".select2-update").select2({
                    dropdownParent: $('#updateWarehouseModal .modal-content'),
                    placeholder: "Select One...",
                    allowClear: true
                });
            </script>
            <?php
        }
    } 

    if ($action === "fetch_warehouse_qr") {
        $warehouse_id = mysqli_real_escape_string($conn, $_POST['id'] ?? '');
        include_once('../delivery/qrlib.php');

        $warehouse_name = getWarehouseName($warehouse_id);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $domain   = $protocol . $_SERVER['HTTP_HOST'];

        $website = $domain . "/?page=warehouse_details&warehouse_id=" . urlencode($warehouse_id);

        $dir = "../images/warehouseqr";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $final_path = "$dir/warehouse{$warehouse_id}.png";
        $tmp_qr     = "$dir/tmp_qr_{$warehouse_id}.png";

        if (!file_exists($final_path)) {
            QRcode::png($website, $tmp_qr, QR_ECLEVEL_L, 10, 1);

            $qr_img = imagecreatefrompng($tmp_qr);
            $qr_w = imagesx($qr_img);
            $qr_h = imagesy($qr_img);

            $font_file = __DIR__ . '/../assets/fonts/roboto/Roboto-Bold.ttf'; 
            $font_size = 15;

            $lines = [];
            $words = explode(" ", $warehouse_name);
            $current = "";
            foreach ($words as $word) {
                $test = $current ? "$current $word" : $word;
                $box  = imagettfbbox($font_size, 0, $font_file, $test);
                $w = $box[2] - $box[0];
                if ($w < $qr_w - 20) {
                    $current = $test;
                } else {
                    $lines[] = $current;
                    $current = $word;
                }
            }
            if ($current) $lines[] = $current;

            $line_spacing = 4;
            $line_height = $font_size + $line_spacing;
            $text_height = count($lines) * $line_height;

            $padding = 5;
            $canvas_w = $qr_w + ($padding * 2);
            $canvas_h = $text_height + $qr_h + ($padding * 2);

            $canvas = imagecreatetruecolor($canvas_w, $canvas_h);
            $white  = imagecolorallocate($canvas, 255, 255, 255);
            $black  = imagecolorallocate($canvas, 0, 0, 0);
            imagefill($canvas, 0, 0, $white);

            $y = $padding;
            foreach ($lines as $line) {
                $box = imagettfbbox($font_size, 0, $font_file, $line);
                $text_w = $box[2] - $box[0];
                $x = ($canvas_w - $text_w) / 2;
                imagettftext($canvas, $font_size, 0, $x, $y + $font_size, $black, $font_file, $line);
                $y += $line_height;
            }

            imagecopy($canvas, $qr_img, $padding, $y, 0, 0, $qr_w, $qr_h);

            imagepng($canvas, $final_path);
            imagedestroy($qr_img);
            imagedestroy($canvas);
            unlink($tmp_qr);
        }

        echo ltrim($final_path, '../');
        exit;
    }
    
    mysqli_close($conn);
}
?>
