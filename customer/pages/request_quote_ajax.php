<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../../includes/dbconn.php';
require '../../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "save_building_form") {
        $id                = (int)($_POST['id'] ?? 0);
        $width             = mysqli_real_escape_string($conn, $_POST['width'] ?? '');
        $length            = mysqli_real_escape_string($conn, $_POST['length'] ?? '');
        $wall_height       = mysqli_real_escape_string($conn, $_POST['wall_height'] ?? '');
        $wall_framing      = mysqli_real_escape_string($conn, $_POST['wall_framing'] ?? '');
        $roof_pitch        = mysqli_real_escape_string($conn, $_POST['roof_pitch'] ?? '');
        $foundation        = mysqli_real_escape_string($conn, $_POST['foundation'] ?? '');
        $truss_wood        = mysqli_real_escape_string($conn, $_POST['truss_wood'] ?? '');
        $truss_steel       = mysqli_real_escape_string($conn, $_POST['truss_steel'] ?? '');
        $overhang          = mysqli_real_escape_string($conn, $_POST['overhang'] ?? '');
        $spacing           = mysqli_real_escape_string($conn, $_POST['spacing'] ?? '');

        $interior_walls    = mysqli_real_escape_string($conn, $_POST['interior_walls'] ?? '');
        $slider_doors      = mysqli_real_escape_string($conn, $_POST['slider_doors'] ?? '');
        $slider_details    = mysqli_real_escape_string($conn, $_POST['slider_details'] ?? '');

        $grade             = mysqli_real_escape_string($conn, $_POST['grade'] ?? '');
        $roof_color        = mysqli_real_escape_string($conn, $_POST['roof_color'] ?? '');
        $wall_color        = mysqli_real_escape_string($conn, $_POST['wall_color'] ?? '');
        $roof_trim_color   = mysqli_real_escape_string($conn, $_POST['roof_trim_color'] ?? '');
        $wall_trim_color   = mysqli_real_escape_string($conn, $_POST['wall_trim_color'] ?? '');
        $wainscot          = mysqli_real_escape_string($conn, $_POST['wainscot'] ?? '');
        $wainscot_color    = mysqli_real_escape_string($conn, $_POST['wainscot_color'] ?? '');

        $customer_name     = mysqli_real_escape_string($conn, $_POST['customer_name'] ?? '');
        $customer_address  = mysqli_real_escape_string($conn, $_POST['customer_address'] ?? '');
        $customer_phone    = mysqli_real_escape_string($conn, $_POST['customer_phone'] ?? '');
        $customer_email    = mysqli_real_escape_string($conn, $_POST['customer_email'] ?? '');
        $contractor        = mysqli_real_escape_string($conn, $_POST['contractor'] ?? '');
        $contact_method    = mysqli_real_escape_string($conn, $_POST['contact_method'] ?? '');

        $garage_doors_no   = mysqli_real_escape_string($conn, $_POST['garage_doors_no'] ?? '');
        $garage_doors_size = mysqli_real_escape_string($conn, $_POST['garage_doors_size'] ?? '');
        $entry_doors_no    = mysqli_real_escape_string($conn, $_POST['entry_doors_no'] ?? '');
        $entry_doors_size  = mysqli_real_escape_string($conn, $_POST['entry_doors_size'] ?? '');
        $windows_no        = mysqli_real_escape_string($conn, $_POST['windows_no'] ?? '');
        $windows_size      = mysqli_real_escape_string($conn, $_POST['windows_size'] ?? '');

        $wall_insulation   = isset($_POST['wall_insulation']) ? mysqli_real_escape_string($conn, json_encode($_POST['wall_insulation'])) : null;
        $roof_insulation   = isset($_POST['roof_insulation']) ? mysqli_real_escape_string($conn, json_encode($_POST['roof_insulation'])) : null;
        $roof_selection    = isset($_POST['roof_selection']) ? mysqli_real_escape_string($conn, json_encode($_POST['roof_selection'])) : null;
        $wall_selection    = isset($_POST['wall_selection']) ? mysqli_real_escape_string($conn, json_encode($_POST['wall_selection'])) : null;
        $building_type     = isset($_POST['building_type']) ? mysqli_real_escape_string($conn, json_encode($_POST['building_type'])) : null;

        $created_by        = 1;
        $is_customer       = 0;
        $customer_id       = "NULL";

        if ($id > 0) {
            $sql = "
            UPDATE building_form SET
                width = '$width',
                length = '$length',
                wall_height = '$wall_height',
                wall_framing = '$wall_framing',
                roof_pitch = '$roof_pitch',
                foundation = '$foundation',
                truss_wood = '$truss_wood',
                truss_steel = '$truss_steel',
                overhang = '$overhang',
                spacing = '$spacing',
                interior_walls = '$interior_walls',
                slider_doors = '$slider_doors',
                slider_details = '$slider_details',
                grade = '$grade',
                roof_color = '$roof_color',
                wall_color = '$wall_color',
                roof_trim_color = '$roof_trim_color',
                wall_trim_color = '$wall_trim_color',
                wainscot = '$wainscot',
                wainscot_color = '$wainscot_color',
                customer_name = '$customer_name',
                customer_address = '$customer_address',
                customer_phone = '$customer_phone',
                customer_email = '$customer_email',
                contractor = '$contractor',
                contact_method = '$contact_method',
                garage_doors_no = '$garage_doors_no',
                garage_doors_size = '$garage_doors_size',
                entry_doors_no = '$entry_doors_no',
                entry_doors_size = '$entry_doors_size',
                windows_no = '$windows_no',
                windows_size = '$windows_size',
                wall_insulation = " . ($wall_insulation ? "'$wall_insulation'" : "NULL") . ",
                roof_insulation = " . ($roof_insulation ? "'$roof_insulation'" : "NULL") . ",
                roof_selection = " . ($roof_selection ? "'$roof_selection'" : "NULL") . ",
                wall_selection = " . ($wall_selection ? "'$wall_selection'" : "NULL") . ",
                building_type = " . ($building_type ? "'$building_type'" : "NULL") . "
            WHERE id = $id
            ";
        } else {
            $sql = "
            INSERT INTO building_form(
                width, length, wall_height, wall_framing, roof_pitch, foundation, truss_wood, truss_steel, 
                overhang, spacing, interior_walls, slider_doors, slider_details, grade, roof_color, wall_color, 
                roof_trim_color, wall_trim_color, wainscot, wainscot_color, customer_name, customer_address, 
                customer_phone, customer_email, contractor, contact_method, garage_doors_no, garage_doors_size, 
                entry_doors_no, entry_doors_size, windows_no, windows_size, wall_insulation, roof_insulation, 
                roof_selection, wall_selection, building_type, created_by, is_customer, customer_id
            ) VALUES (
                '$width','$length','$wall_height','$wall_framing','$roof_pitch','$foundation','$truss_wood','$truss_steel',
                '$overhang','$spacing','$interior_walls','$slider_doors','$slider_details','$grade','$roof_color',
                '$wall_color','$roof_trim_color','$wall_trim_color','$wainscot','$wainscot_color','$customer_name',
                '$customer_address','$customer_phone','$customer_email','$contractor','$contact_method',
                '$garage_doors_no','$garage_doors_size','$entry_doors_no','$entry_doors_size','$windows_no','$windows_size',
                " . ($wall_insulation ? "'$wall_insulation'" : "NULL") . ",
                " . ($roof_insulation ? "'$roof_insulation'" : "NULL") . ",
                " . ($roof_selection ? "'$roof_selection'" : "NULL") . ",
                " . ($wall_selection ? "'$wall_selection'" : "NULL") . ",
                " . ($building_type ? "'$building_type'" : "NULL") . ",
                $created_by, $is_customer, $customer_id
            )
            ";
        }

        if (mysqli_query($conn, $sql)) {
            $form_id = ($id > 0) ? $id : mysqli_insert_id($conn);

            echo json_encode([
                "success" => true,
                "form_id" => $form_id
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => mysqli_error($conn)
            ]);
        }

        if (!empty($_FILES['attachments']['name'][0])) {
            $targetDir = "../building_form_attachments/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            foreach ($_FILES['attachments']['tmp_name'] as $i => $tmp) {
                if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                    $filename   = basename($_FILES['attachments']['name'][$i]);
                    $safeName   = preg_replace("/[^a-zA-Z0-9._-]/", "_", $filename);
                    $uniqueName = time() . "_" . $safeName;
                    $targetPath = $targetDir . $uniqueName;

                    if (move_uploaded_file($tmp, $targetPath)) {
                        $attachment_type = (strpos($_FILES['attachments']['type'][$i], 'image') !== false) ? 'image' : 'file';

                        $form_id_esc   = (int)$form_id;
                        $file_url_esc  = mysqli_real_escape_string($conn, $uniqueName);
                        $type_esc      = mysqli_real_escape_string($conn, $attachment_type);

                        $sql = "
                            INSERT INTO building_form_attachments (building_form_id, file_url, attachment_type) 
                            VALUES ('$form_id_esc', '$file_url_esc', '$type_esc')
                        ";
                        mysqli_query($conn, $sql) or die("Attachment insert failed: " . mysqli_error($conn));
                    }
                }
            }
        }

    } 

    if ($action == 'delete_attachment') {
        $id = (int)$_POST['id'];

        $result = mysqli_query($conn, "SELECT file_url FROM building_form_attachments WHERE id = $id");
        $file   = mysqli_fetch_assoc($result);

        if ($file) {
            mysqli_query($conn, "DELETE FROM building_form_attachments WHERE id = $id");
            $filePath = __DIR__ . "/building_form_attachments/" . $file['file_url'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "File not found."]);
        }
        exit;
    }

    
    mysqli_close($conn);
}
?>
