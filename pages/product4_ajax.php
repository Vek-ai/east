<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';
require '../includes/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$product_excel = 'product_excel';
//4 = TRIM
$trim_id = 4;
$category_id = 4;

$includedColumns = [
    'product_id'        => 'Product ID',
    'product_category'  => 'Product Category',
    'product_type'      => 'Product Type',
    'profile'           => 'Product Profile',
    'grade'             => 'Grade',
    'gauge'             => 'Gauge',
    'color_group'       => 'Color Group',
    'color_paint'       => 'Color',
    'product_item'      => 'Description',
    'warranty_type'     => 'Warranty Type',
    'product_origin'    => 'Manufactured or Purchased',
    'unit_of_measure'   => 'Unit of Measure',
    'weight'            => 'Weight',
    'sold_by_feet'      => 'Sold by Linear Feet',
    'panel_type'        => 'Panel Type',
    'panel_style'       => 'Panel Style',
    'standing_seam'     => 'Standing Seam',
    'board_batten'      => 'Board & Batten',
    'is_custom_length'  => 'Sold with custom length?',
    'available_lengths' => 'Available Lengths',
    'unit_price'        => 'Retail Price',
    'inv_id'            => 'Inventory ID',
    'coil_part_no'      => 'Coil Part #',
    'product_sku'       => 'SKU',
    'upc'               => 'UPC',
    'reorder_level'     => 'Reorder Level',
    'supplier_id'       => 'Supplier',
    'comment'           => 'Notes'
];

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);

        $product_category = intval($_POST['product_category'] ?? 0);
        $product_type      = isset($_POST['product_type']) ? array_filter(array_map('intval', $_POST['product_type'])) : [];
        $profile           = isset($_POST['profile']) ? array_filter(array_map('intval', $_POST['profile'])) : [];
        $grade             = isset($_POST['grade']) ? array_filter(array_map('intval', $_POST['grade'])) : [];
        $gauge             = isset($_POST['gauge']) ? array_filter(array_map('intval', $_POST['gauge'])) : [];
        $color_paint       = isset($_POST['color_paint']) ? array_filter(array_map('intval', $_POST['color_paint'])) : [];
        $available_lengths = isset($_POST['available_lengths']) ? array_filter(array_map('intval', $_POST['available_lengths'])) : [];

        $fields = [];
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $allNumeric = array_reduce($value, fn($carry, $item) => $carry && is_numeric($item), true);
                if ($allNumeric) $value = array_map('intval', $value);
                $value = json_encode($value);
            }
            $escapedValue = mysqli_real_escape_string($conn, $value);
            if ($key != 'product_id') $fields[$key] = $escapedValue;
            if ($key == 'color_paint') $fields['color'] = $escapedValue;
        }

        $has_color = isset($_POST['has_color']) ? 1 : 0;
        $is_special_trim = isset($_POST['is_special_trim']) ? 1 : 0;
        $standing_seam = ($_POST['panel_type'] ?? '') === 'standing_seam' ? 1 : 0;
        $board_batten  = ($_POST['panel_type'] ?? '') === 'board_batten' ? 1 : 0;
        $fields['has_color'] = $has_color;
        $fields['is_special_trim'] = $is_special_trim;
        $fields['standing_seam'] = $standing_seam;
        $fields['board_batten']  = $board_batten;

        $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $updateQuery = "UPDATE product SET ";
            foreach ($fields as $column => $value) {
                $columnExists = mysqli_query($conn, "SHOW COLUMNS FROM product LIKE '$column'");
                if (mysqli_num_rows($columnExists) > 0) {
                    $updateQuery .= "$column = '$value', ";
                }
            }
            $updateQuery = rtrim($updateQuery, ", ") . " WHERE product_id = '$product_id'";
            if (!mysqli_query($conn, $updateQuery)) {
                echo "Error updating product: " . mysqli_error($conn);
                exit;
            }
            echo "success_update";
        } else {
            $columns = $values = [];
            foreach ($fields as $column => $value) {
                $columnExists = mysqli_query($conn, "SHOW COLUMNS FROM product LIKE '$column'");
                if (mysqli_num_rows($columnExists) > 0) {
                    $columns[] = $column;
                    $values[] = "'$value'";
                }
            }
            $columnsStr = implode(", ", $columns);
            $valuesStr = implode(", ", $values);
            $insertQuery = "INSERT INTO product (product_id, $columnsStr) VALUES ('$product_id', $valuesStr)";
            if (!mysqli_query($conn, $insertQuery)) {
                echo "Error adding product: " . mysqli_error($conn);
                exit;
            }
            $product_id = $conn->insert_id;
            $conn->query("UPDATE product SET main_image='images/product/product.jpg' WHERE product_id='$product_id'");
            echo "success_add";
        }

        if (!empty($color_paint)) {
            $assignedBy = $_SESSION['userid'];
            $date = date('Y-m-d');
            $time = date('H:i:s');

            $existingColors = [];
            $res = mysqli_query($conn, "SELECT color_id FROM product_color_assign WHERE product_id = '$product_id'");
            while ($row = mysqli_fetch_assoc($res)) $existingColors[] = intval($row['color_id']);

            $toAdd = array_diff($color_paint, $existingColors);
            $toDelete = array_diff($existingColors, $color_paint);

            if (!empty($toDelete)) {
                mysqli_query($conn, "DELETE FROM product_color_assign WHERE product_id = '$product_id' AND color_id IN (".implode(',', $toDelete).")");
            }

            foreach ($toAdd as $colorId) {
                mysqli_query($conn, "INSERT INTO product_color_assign (product_id, color_id, `date`, `time`, assigned_by) 
                                    VALUES ($product_id, $colorId, '$date', '$time', $assignedBy)");
            }

            $allColors = array_unique(array_merge($color_paint, $existingColors));
            mysqli_query($conn, "UPDATE product SET color = '".json_encode($allColors)."' WHERE product_id = '$product_id'");
        }

        if (!empty($_FILES['picture_path']['name'][0])) {
            $uploadFileDir = '../images/product/';
            for ($i = 0; $i < count($_FILES['picture_path']['name']); $i++) {
                $fileTmpPath = $_FILES['picture_path']['tmp_name'][$i];
                $fileName = $_FILES['picture_path']['name'][$i];
                if (empty($fileName)) continue;
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    if ($i == 0) $conn->query("UPDATE product SET main_image='images/product/$newFileName' WHERE product_id='$product_id'");
                    $conn->query("INSERT INTO product_images (productid, image_url) VALUES ('$product_id', 'images/product/$newFileName')");
                }
            }
        }

        if (!empty($product_type) || !empty($profile) || !empty($grade) || !empty($gauge) || !empty($color_paint) || !empty($available_lengths)) {
            generateProductAbr([$product_category], $profile, $grade, $gauge, $product_type, $color_paint, $available_lengths, $product_id);
        }

        $dimension_ids = $_POST['dimension_ids'] ?? [];
        $unit_prices   = $_POST['unit_price'] ?? [];
        $floor_prices  = $_POST['floor_price'] ?? [];
        $bulk_prices   = $_POST['bulk_price'] ?? [];

        foreach ($dimension_ids as $i => $dim_id) {
            $dim_id = intval($dim_id);
            if ($dim_id <= 0) continue;

            $unit_price  = isset($unit_prices[$i]) ? floatval($unit_prices[$i]) : 0;
            $floor_price = isset($floor_prices[$i]) ? floatval($floor_prices[$i]) : 0;
            $bulk_price  = isset($bulk_prices[$i]) ? floatval($bulk_prices[$i]) : 0;

            $res = mysqli_query($conn, "SELECT id FROM product_screw_lengths WHERE product_id = '$product_id' AND dimension_id = '$dim_id'");
            if (mysqli_num_rows($res) > 0) {
                mysqli_query($conn, "UPDATE product_screw_lengths SET 
                    unit_price = '$unit_price', 
                    floor_price = '$floor_price', 
                    bulk_price = '$bulk_price'
                    WHERE product_id = '$product_id' AND dimension_id = '$dim_id'");
            } else {
                mysqli_query($conn, "INSERT INTO product_screw_lengths 
                    (product_id, dimension_id, unit_price, floor_price, bulk_price) 
                    VALUES ('$product_id', '$dim_id', '$unit_price', '$floor_price', '$bulk_price')");
            }
        }

        $lines   = $_POST['profile'] ?? [];
        $types   = $_POST['product_type'] ?? [];
        $grades  = $_POST['grade'] ?? [];
        $gauges  = $_POST['gauge'] ?? [];
        $lengths = $_POST['available_lengths'] ?? [];
        $colors  = $_POST['color_paint'] ?? [];

        $combinations = array_combinations([
            'product_line' => $lines,
            'product_type' => $types,
            'grade'        => $grades,
            'gauge'        => $gauges,
            'dimension_id' => $lengths,
            'color_id'     => $colors
        ]);

        if (!empty($combinations)) {
            $insertValues = [];

            foreach ($combinations as $combo) {
                $line   = intval($combo['product_line']);
                $type   = intval($combo['product_type']);
                $grade  = intval($combo['grade']);
                $gauge  = intval($combo['gauge']);
                $dim    = intval($combo['dimension_id']);
                $color  = intval($combo['color_id']);

                $insertValues[] = "('$product_id', $line, $type, $grade, $gauge, $dim, $color, 0)";
            }

            if (!empty($insertValues)) {
                $insertSql = "
                    INSERT IGNORE INTO inventory
                    (Product_id, product_line, product_type, grade, gauge, dimension_id, color_id, quantity_ttl)
                    VALUES " . implode(',', $insertValues);

                mysqli_query($conn, $insertSql);
            }
        }
    }

    if ($action == "get_product_abr") {
        $category_ids = isset($_POST['category_ids']) ? array_map('intval', $_POST['category_ids']) : [];
        $type_ids     = isset($_POST['type_ids']) ? array_map('intval', $_POST['type_ids']) : [];
        $profile_ids  = isset($_POST['profile_ids']) ? array_map('intval', $_POST['profile_ids']) : [];
        $grade_ids    = isset($_POST['grade_ids']) ? array_map('intval', $_POST['grade_ids']) : [];
        $gauge_ids    = isset($_POST['gauge_ids']) ? array_map('intval', $_POST['gauge_ids']) : [];
        $color_ids    = isset($_POST['color_ids']) ? array_map('intval', $_POST['color_ids']) : [];
        $length       = isset($_POST['length']) ? floatval($_POST['length']) : 0;
        $product_id   = isset($_POST['product_id']) ? $_POST['product_id'] : '';

        $panel_id = 3;
        $trim_id  = 4;

        $product_ids_string = generateProductAbrString(
                                $category_ids,
                                $profile_ids,
                                $grade_ids,
                                $gauge_ids,
                                $type_ids,
                                $color_ids,
                                '',
                                $product_id = null
                            );

        echo $product_ids_string;
    }


    if ($action == "fetch_view_modal") {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            Add Product
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div id="update_product" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">

                                    <div class="card card-body">
                                        <h4 class="card-title text-center">Product Image</h4>
                                        <div class="row pt-3">
                                            <?php
                                            $query_img = "SELECT * FROM product_images WHERE productid = '$product_id'";
                                            $result_img = mysqli_query($conn, $query_img); 
                                            if(mysqli_num_rows($result_img) > 0){
                                                while ($row_img = mysqli_fetch_array($result_img)) {
                                                ?>
                                                <div class="col-md">
                                                    <img src="<?= $row_img['image_url'] ?>" class="img-fluid" alt="Product Image" />
                                                </div>
                                                <?php
                                                }
                                            }else{
                                            ?>
                                            <p class="mb-0 fs-3 text-center">No image found.</p>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Product Name:</label>
                                                <p><?= $row['product_item'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Product SKU:</label>
                                                <p><?= $row['product_sku'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Product Category:</label>
                                                <p><?= getProductCategoryName($row['product_category']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Product Line:</label>
                                                <p><?= getProductLineName($row['product_line']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Product Type:</label>
                                                <p><?= getProductTypeName($row['product_type']) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Correlated Products:</label>
                                            <ul>
                                                <?php
                                                $correlated_product_ids = [];
                                                $product_id = mysqli_real_escape_string($conn, $row['product_id']);
                                                $query_correlated = "SELECT correlated_id FROM correlated_product WHERE main_correlated_product_id = '$product_id'";
                                                $result_correlated = mysqli_query($conn, $query_correlated);
                                                
                                                while ($row_correlated = mysqli_fetch_assoc($result_correlated)) {
                                                    $correlated_product_ids[] = $row_correlated['correlated_id'];
                                                }
                                                foreach ($correlated_product_ids as $correlated_id) {
                                                    // Assuming you fetch the correlated product name
                                                    echo "<li>" .getProductName($correlated_id) ."</li>";
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Stock Type:</label>
                                                <p><?= getStockTypeName($row['stock_type']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Material:</label>
                                                <p><?= $row['material'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Dimensions:</label>
                                                <p><?= $row['dimensions'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Thickness:</label>
                                                <p><?= $row['thickness'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Gauge:</label>
                                                <p><?= getGaugeName($row['gauge']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Grade:</label>
                                                <p><?= getGradeName($row['grade']) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Color:</label>
                                                <p><?= getColorName($row['color']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Usage:</label>
                                                <p><?= getUsageName($row['product_usage']) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Warranty Type:</label>
                                                <p><?= getWarrantyTypeName($row['warranty_type']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Profile:</label>
                                                <p><?= getProfileTypeName($row['profile']) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Width:</label>
                                                <p><?= $row['width'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Length:</label>
                                                <p><?= $row['length'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Weight:</label>
                                                <p><?= $row['weight'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Unit of Measure:</label>
                                                <p><?= $row['unit_of_measure'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Retail Price:</label>
                                                <p><?= $row['unit_price'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Cost:</label>
                                                <p><?= $row['cost'] ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">UPC:</label>
                                                <p><?= $row['upc'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Reorder Level:</label>
                                                <p><?= $row['reorder_level'] ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-12 d-flex align-items-center justify-content-between">
                                            <div class="mb-1">
                                                <label class="form-label">Sold By Feet:</label>
                                                <p><?= $row['sold_by_feet'] == 1 ? 'Yes' : 'No' ?></p>
                                            </div>
                                            <div class="mb-1">
                                                <label class="form-label">Standing Seam Panel:</label>
                                                <p><?= $row['standing_seam'] == 1 ? 'Yes' : 'No' ?></p>
                                            </div>
                                            <div class="mb-1">
                                                <label class="form-label">Board & Batten Panel:</label>
                                                <p><?= $row['board_batten'] == 1 ? 'Yes' : 'No' ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Comment:</label>
                                        <p><?= $row['comment'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.modal-content -->
            </div>
            <script>
                $(document).ready(function() {
                    let uploadedUpdateFiles = [];

                    $('#myUpdateDropzone').dropzone({
                        addRemoveLinks: true,
                        dictRemoveFile: "X",
                        init: function() {
                            this.on("addedfile", function(file) {
                                uploadedUpdateFiles.push(file);
                                updateFileInput2();
                                displayFileNames2()
                            });

                            this.on("removedfile", function(file) {
                                uploadedUpdateFiles = uploadedUpdateFiles.filter(f => f.name !== file.name);
                                updateFileInput2();
                                displayFileNames2()
                            });
                        }
                    });

                    function updateFileInput2() {
                        const fileInput = document.getElementById('picture_path_update');
                        const dataTransfer = new DataTransfer();

                        uploadedUpdateFiles.forEach(file => {
                            const fileBlob = new Blob([file], { type: file.type });
                            dataTransfer.items.add(new File([fileBlob], file.name, { type: file.type }));
                        });

                        fileInput.files = dataTransfer.files;
                    }

                    function displayFileNames2() {
                        let files = document.getElementById('picture_path_update').files;
                        let fileNames = '';

                        if (files.length > 0) {
                            for (let i = 0; i < files.length; i++) {
                                let file = files[i];
                                fileNames += `<p>${file.name}</p>`;
                            }
                        } else {
                            fileNames = '<p>No files selected</p>';
                        }

                        console.log(fileNames);
                    }
                });

            </script>

            <?php
        }
    } 

    if ($action == "fetch_pricing_section") {
        $screw_type = mysqli_real_escape_string($conn, $_POST['screw_type']);
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $product_details = getProductDetails($product_id);

        $bulk_starts_at = $product_details['bulk_starts_at'] ?? 0;

        $screw_type_det = getProductScrewType($screw_type);

        $dimension_arr = json_decode($screw_type_det['dimensions'] ?? '[]', true);
        if (!is_array($dimension_arr)) $dimension_arr = [];

        $lengths = [];
        $lengthQuery = "SELECT * FROM dimensions WHERE dimension_category = 16 ORDER BY dimension ASC";
        $lengthRes = mysqli_query($conn, $lengthQuery);
        while ($l = mysqli_fetch_assoc($lengthRes)) {
            $lengths[$l['dimension_id']] = $l;
        }

        $product_lengths = [];
        $res = mysqli_query($conn, "SELECT * FROM product_screw_lengths WHERE product_id = '$product_id'");
        while ($r = mysqli_fetch_assoc($res)) {
            $product_lengths[$r['dimension_id']] = $r;
        }

        foreach ($dimension_arr as $dim_id):
            $dim = $lengths[$dim_id] ?? null;
            if (!$dim) continue;

            $unit_price = $product_lengths[$dim_id]['unit_price'] ?? '';
            $floor_price = $product_lengths[$dim_id]['floor_price'] ?? '';
            $bulk_price = $product_lengths[$dim_id]['bulk_price'] ?? '';
            ?>
            <div class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Length</label>
                    <select name="dimensions[]" class="form-control select2_modal">
                        <option value="<?= $dim['dimension_id'] ?>" selected>
                            <?= $dim['dimension'] . ' ' . ($dim['dimension_unit'] ?? '') ?>
                        </option>
                    </select>
                    <input type="hidden" name="dimension_ids[]" value="<?= $dim['dimension_id'] ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Retail Price</label>
                    <input type="number" step="0.001" class="form-control" name="unit_price[]" value="<?= $unit_price ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Floor Price</label>
                    <input type="number" step="0.001" class="form-control" name="floor_price[]" value="<?= $floor_price ?>">
                </div>

                <div class="col-md-3">
                    <div id="bulk_pricing_fields" class="row align-items-end <?= ($bulk_price > 0) ? '' : 'd-none' ?> bulk_pricing_fields">
                        <label class="form-label">Bulk Price</label>
                        <input type="number" step="0.001" class="form-control" name="bulk_price[]" value="<?= $bulk_price ?>">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-md-4 mb-3">
            <div id="bulk_pricing_fields" class="row align-items-end <?= ($bulk_price > 0) ? '' : 'd-none' ?> bulk_pricing_fields">
                <label class="form-label fw-semibold mb-1">Bulk Pricing Starts At</label>
                <input type="number" class="form-control" id="bulk_starts_at" name="bulk_starts_at" placeholder="Enter quantity threshold" value="<?= $bulk_starts_at ?>">
            </div>
        </div>

        <?php 
        $bulk_starts_at = floatval($row['bulk_starts_at'] ?? 0);
        ?>
        <div class="col-12 mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="enable_bulk_pricing" <?= ($bulk_price > 0 || $bulk_starts_at > 0) ? 'checked' : '' ?>>
                <label class="form-check-label fw-bold" for="enable_bulk_pricing">
                    Bulk Pricing
                </label>
            </div>
        </div>
    <?php
    }

    if ($action == 'fetch_color_multiplier') {
        $colorGroup    = intval($_POST['color_group'] ?? 0);
        $productSystem = trim($_POST['product_system'] ?? '');
        $grade         = trim($_POST['grade'] ?? '');
        $gauge         = intval($_POST['gauge'] ?? 0);
        $category      = trim($_POST['product_category'] ?? '');

        $query = "SELECT * FROM product_color WHERE color = $colorGroup";

        if ($productSystem !== '') {
            $query .= " AND product_system = '" . mysqli_real_escape_string($conn, $productSystem) . "'";
        }
        if ($grade !== '') {
            $query .= " AND grade = '" . mysqli_real_escape_string($conn, $grade) . "'";
        }
        if ($gauge > 0) {
            $query .= " AND gauge = $gauge";
        }
        if ($category !== '') {
            $query .= " AND product_category = '" . mysqli_real_escape_string($conn, $category) . "'";
        }

        $query .= " LIMIT 1";

        $result = mysqli_query($conn, $query);

        if ($result && $row = mysqli_fetch_assoc($result)) {
            echo json_encode([
                'multiplier'  => $row['multiplier'] ?? 1,
                'price'       => $row['price'] ?? 0,
                'color_name'  => $row['color_name'] ?? '',
                'availability'=> $row['availability'] ?? ''
            ]);
        } else {
            echo json_encode([
                'multiplier' => 1,
                'price' => 0,
                'error' => 'No matching record found'
            ]);
        }
    }

    if ($action == "remove_image") {
        $image_id = $_POST['image_id'];
    
        $delete_query = "DELETE FROM product_images WHERE prodimgid = '$image_id'";
        if (mysqli_query($conn, $delete_query)) {
            /* if (file_exists($image_url)) {
                unlink($image_url);
            } */
            echo 'success';
        } else {
            echo "Error removing image: " . mysqli_error($conn);
        }
    }

    if ($action == "change_status") {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE product SET status = '$new_status' WHERE product_id = '$product_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == 'hide_product') {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $query = "UPDATE product SET hidden='1' WHERE product_id='$product_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == 'fetch_product_fields') {
        $product_category_id = mysqli_real_escape_string($conn, $_POST['product_category_id']);
        $query = "SELECT * FROM product_fields WHERE product_category_id='$product_category_id'";
        $result = mysqli_query($conn, $query);
    
        if ($result) {
            $fields = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $fields[] = $row;
            }
            echo json_encode($fields);
        } else {
            echo 'error';
        }
    }

    if ($action == "fetch_uploaded_modal") {
        $sql = "SELECT * FROM `$product_excel`";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $columnsWithData = [];
            while ($row = $result->fetch_assoc()) {
                foreach (array_keys($includedColumns) as $col) {
                    if (!empty(trim($row[$col] ?? ''))) {
                        $columnsWithData[$col] = true;
                    }
                }
            }

            $result->data_seek(0);
            ?>
            
            <div class="card card-body shadow">
                <form id="tableForm">
                    <div style="overflow-x: auto; overflow-y: auto; max-height: 90vh; max-width: 100%;">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <?php
                                    foreach ($includedColumns as $dbCol => $label) {
                                        if (isset($columnsWithData[$dbCol])) {
                                            echo "<th class='fs-4'>" . htmlspecialchars($label) . "</th>";
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                $product_id = htmlspecialchars($row['product_id'] ?? '');
                                echo '<tr>';
                                foreach ($includedColumns as $dbCol => $label) {
                                    if (isset($columnsWithData[$dbCol])) {
                                        $value = htmlspecialchars($row[$dbCol] ?? '');
                                        echo "<td contenteditable='true' class='table_data' data-header-name='" . htmlspecialchars($dbCol) . "' data-id='" . $product_id . "'>$value</td>";
                                    }
                                }
                                echo '</tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" id="saveTable" class="btn btn-primary mt-3">Save</button>
                    </div>
                </form>
            </div>
            <?php
        } else {
            echo "<p>No data found in the table.</p>";
        }
    }

    if ($action == "upload_excel") {
        if (isset($_FILES['excel_file'])) {
            $fileTmpPath = $_FILES['excel_file']['tmp_name'];
            $fileName = $_FILES['excel_file']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, ["xlsx", "xls"])) {
                echo "Please upload a valid Excel file.";
                exit;
            }

            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $dbColumns = array_keys($includedColumns);
            $numColumns = count($dbColumns);

            if (!$conn->query("TRUNCATE TABLE `$product_excel`")) {
                echo "Error truncating table: " . $conn->error;
                exit;
            }

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                $row = array_pad($row, $numColumns, '');
                $row = array_slice($row, 0, $numColumns);

                $data = array_combine($dbColumns, $row);

                $escapedValues = array_map(fn($v) => mysqli_real_escape_string($conn, $v ?? ''), array_values($data));
                $columnNames = implode(", ", array_map(fn($col) => "`$col`", array_keys($data)));
                $columnValues = implode("', '", $escapedValues);

                $sql = "INSERT INTO `$product_excel` ($columnNames) VALUES ('$columnValues')";
                if (!$conn->query($sql)) {
                    echo "Error inserting row {$i}: " . $conn->error;
                    exit;
                }
            }

            echo "success";
        } else {
            echo "No file uploaded.";
            exit;
        }
    }

    
    
    if ($action == "save_table") {
        $table = "product";
    
        $selectSql = "SELECT * FROM $product_excel";
        $result = $conn->query($selectSql);
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                unset($row['id']);
    
                $product_id = trim($row['product_id'] ?? '');
    
                $conditions = [];
                foreach ($row as $column => $value) {
                    $conditions[] = "$column = '" . $conn->real_escape_string($value) . "'";
                }
    
                $checkSql = "SELECT COUNT(*) as count FROM $table WHERE " . implode(" AND ", $conditions);
                $checkResult = $conn->query($checkSql);
                $exists = $checkResult->fetch_assoc()['count'] > 0;
    
                if ($exists) {
                    continue;
                }
    
                if (!empty($product_id)) {
                    $idCheckSql = "SELECT COUNT(*) as count FROM $table WHERE product_id = '$product_id'";
                    $idCheckResult = $conn->query($idCheckSql);
                    $idExists = $idCheckResult->fetch_assoc()['count'] > 0;
    
                    if ($idExists) {
                        $updateFields = [];
                        foreach ($row as $column => $value) {
                            if ($column !== 'product_id') {
                                $updateFields[] = "$column = '" . $conn->real_escape_string($value) . "'";
                            }
                        }
                        $updateSql = "UPDATE $table SET " . implode(", ", $updateFields) . " WHERE product_id = '$product_id'";
                        $conn->query($updateSql);
                        continue;
                    }
                }
    
                $columns = implode(", ", array_keys($row));
                $values = "'" . implode("', '", array_map([$conn, 'real_escape_string'], array_values($row))) . "'";
                $insertSql = "INSERT INTO $table ($columns) VALUES ($values)";
                $conn->query($insertSql);
            }
    
            echo "Data has been successfully saved";
    
            $truncateSql = "TRUNCATE TABLE $product_excel";
            if ($conn->query($truncateSql) !== TRUE) {
                echo " but failed to clear $product_excel table: " . $conn->error;
            }
        } else {
            echo "No data found in $product_excel table.";
        }
    }    

    if ($action == "download_excel") {
        $product_category = mysqli_real_escape_string($conn, $_REQUEST['category'] ?? '');
        $category_name = strtoupper(getProductCategoryName($product_category));

        $columnNames = array_keys($includedColumns);
        $column_txt = implode(', ', $columnNames);

        $sql = "SELECT $column_txt FROM product WHERE hidden = '0' AND status = '1'";
        if (!empty($product_category)) {
            $sql .= " AND product_category = '$product_category'";
        }
        $result = $conn->query($sql);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;

        foreach ($includedColumns as $column => $header) {
            $index = array_search($column, $columnNames);
            $columnLetter = indexToColumnLetter($index);
            $sheet->setCellValue($columnLetter . $row, $header);
        }

        $row = 2;
        while ($data = $result->fetch_assoc()) {
            foreach ($includedColumns as $column => $header) {
                $index = array_search($column, $columnNames);
                $columnLetter = indexToColumnLetter($index);
                $sheet->setCellValue($columnLetter . $row, $data[$column] ?? '');
            }
            $row++;
        }

        if(empty($category_name)){
            $category_name = "PRODUCTS";
        }
        $filename = "$category_name.xlsx";
        $filePath = $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=0');

        readfile($filePath);
        unlink($filePath);
        exit;
    }

    if ($action == "download_classifications") {
        $classification = mysqli_real_escape_string($conn, $_REQUEST['class'] ?? '');
    
        $classifications = [
            'category' => [
                'columns' => ['product_category_id', 'product_category'],
                'table' => 'product_category',
                'where' => "status = '1'"
            ],
            'type' => [
                'columns' => ['product_type_id', 'product_type'],
                'table' => 'product_type',
                'where' => "status = '1'"
            ],
            'grade' => [
                'columns' => ['product_grade_id', 'product_grade'],
                'table' => 'product_grade',
                'where' => "status = '1'"
            ],
            'color' => [
                'columns' => ['color_id', 'product_category', 'color_name'],
                'table' => 'paint_colors',
                'where' => "color_status = '1'"
            ],
            'profile' => [
                'columns' => ['profile_type_id', 'profile_type'],
                'table' => 'profile_type',
                'where' => "status = '1'"
            ],
            'flat_sheet_width' => [
                'columns' => ['id', 'product_system', 'product_category', 'product_line', 'product_type', 'width'],
                'table' => 'flat_sheet_width',
                'where' => "status = '1'"
            ]
        ];
    
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $selectedClassifications = empty($classification) ? array_keys($classifications) : [$classification];
    
        foreach ($selectedClassifications as $class) {
            if (!isset($classifications[$class])) {
                continue;
            }
    
            $includedColumns = $classifications[$class]['columns'];
            $table = $classifications[$class]['table'];
            $where = $classifications[$class]['where'];
            $column_txt = implode(', ', $includedColumns);
            $sql = "SELECT $column_txt FROM $table WHERE $where";
            $result = $conn->query($sql);
    
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(ucwords($class));
    
            $row = 1;
            foreach ($includedColumns as $index => $column) {
                $header = ucwords(str_replace('_', ' ', $column));
                $columnLetter = chr(65 + $index);
                $sheet->setCellValue($columnLetter . $row, $header);
            }
    
            $row = 2;
            while ($data = $result->fetch_assoc()) {
                foreach ($includedColumns as $index => $column) {
                    $columnLetter = chr(65 + $index);

                    $value = $data[$column] ?? '';
    
                    if ($column == 'product_category' && $class == 'color') {
                        $value = getProductCategoryName($data[$column] ?? '');
                    }

                    if ($column == 'product_system' && $class == 'flat_sheet_width') {
                        $value = getProductSystemName($data[$column] ?? '');
                    }

                    if ($column == 'product_category' && $class == 'flat_sheet_width') {
                        $value = getProductCategoryName($data[$column] ?? '');
                    }

                    if ($column == 'product_line' && $class == 'flat_sheet_width') {
                        $value = getProductLineName($data[$column] ?? '');
                    }

                    if ($column == 'product_type' && $class == 'flat_sheet_width') {
                        $value = getProductTypeName($data[$column] ?? '');
                    }
                        
                    $sheet->setCellValue($columnLetter . $row, $value);
                }
                $row++;
            }
        }

        if(empty($classification)){
            $classification = 'Classifications';
        }else{
            $classification = ucwords($classification);
        }
    
        $filename = "$classification.xlsx";
        $filePath = $filename;
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=0');
    
        readfile($filePath);
        unlink($filePath);
        exit;
    }    

    if ($action == "update_product_data") {
        $column_name = $_POST['header_name'];
        $new_value = $_POST['new_value'];
        $product_id = $_POST['id'];
        
        if (empty($column_name) || empty($product_id)) {
            exit;
        }
        
        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $product_id = mysqli_real_escape_string($conn, $product_id);
        
        $sql = "UPDATE $product_excel SET `$column_name` = '$new_value' WHERE product_id = '$product_id'";

        if ($conn->query($sql) === TRUE) {
            echo 'Success';
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
    }

    if ($action == "fetch_add_inventory") {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);
        $prouct_details = getProductDetails($product_id);
        $supplier_id = $prouct_details['supplier_id'];
        ?>
        
        <form id="add_inventory" class="form-horizontal" action="#">
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="fw-bold"><?= getProductName($product_id)?></h4>
                    <input type="hidden" id="product_id_filter" class="form-control select2-add" name="Product_id" value="<?= $product_id ?>" />
                    <input type="hidden" id="operation" name="operation" value="add" />
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <div class="mb-3">
                                <select id="color<?= $no ?>" class="form-control color-cart select2-inventory" name="color_id">
                                    <option value="" >Select Color...</option>
                                    <?php
                                    $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                    $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                    while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                    ?>
                                        <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?> data-color="<?= getColorHexFromColorID($row_paint_colors['color_id']) ?>"><?= $row_paint_colors['color_name'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <div class="mb-3">
                                <p><?= !empty($supplier_id) ? getSupplierName($supplier_id) : 'No Supplier Set for Product' ?></p>
                                <input type="hidden" id="supplier_id_update" name="supplier_id" value="<?= $supplier_id ?>" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Warehouse</label>
                            <div class="mb-3">
                            <select id="Warehouse_id" class="form-control select2-inventory" name="Warehouse_id">
                                <option value="" >Select Warehouse...</option>
                                <optgroup label="Warehouse">
                                    <?php
                                    $query_warehouse = "SELECT * FROM warehouses WHERE status = '1'";
                                    $result_warehouse = mysqli_query($conn, $query_warehouse);            
                                    while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                                    ?>
                                        <option value="<?= $row_warehouse['WarehouseID'] ?>" ><?= $row_warehouse['WarehouseName'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                                
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Shelf</label>
                            <div class="mb-3">
                            <select id="Shelves_id" class="form-control select2-inventory" name="Shelves_id">
                                <option value="" >Select Shelf...</option>
                                <optgroup label="Shelf">
                                    <?php
                                    $query_shelf = "SELECT * FROM shelves";
                                    $result_shelf = mysqli_query($conn, $query_shelf);            
                                    while ($row_shelf = mysqli_fetch_array($result_shelf)) {
                                    ?>
                                        <option value="<?= $row_shelf['ShelfID'] ?>" ><?= $row_shelf['ShelfCode'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bin</label>
                            <div class="mb-3">
                            <select id="Bin_id" class="form-control select2-inventory" name="Bin_id">
                                <option value="" >Select Bin...</option>
                                <optgroup label="Bin">
                                    <?php
                                    $query_bin = "SELECT * FROM bins";
                                    $result_bin = mysqli_query($conn, $query_bin);            
                                    while ($row_bin = mysqli_fetch_array($result_bin)) {
                                    ?>
                                        <option value="<?= $row_bin['BinID'] ?>" ><?= $row_bin['BinCode'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Row</label>
                            <div class="mb-3">
                            <select id="Row_id" class="form-control select2-inventory" name="Row_id">
                                <option value="" >Select Row...</option>
                                <optgroup label="Row">
                                    <?php
                                    $query_rows = "SELECT * FROM warehouse_rows";
                                    $result_rows = mysqli_query($conn, $query_rows);            
                                    while ($row_rows = mysqli_fetch_array($result_rows)) {
                                    ?>
                                        <option value="<?= $row_rows['WarehouseRowID'] ?>" ><?= $row_rows['WarehouseRowID'] ?></option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <input type="text" id="quantity_add" name="quantity" class="form-control"  />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pack</label>
                            <div class="mb-3">
                            <select id="pack_add" class="form-control select2-inventory pack_select" name="pack">
                                <option value="" >Select Pack...</option>
                                <optgroup label="Supplier Packs">
                                    <?php
                                    $query_packs = "SELECT * FROM supplier_pack WHERE supplierid = '$supplier_id'";
                                    $result_packs = mysqli_query($conn, $query_packs);            
                                    while ($row_packs = mysqli_fetch_array($result_packs)) {
                                    ?>
                                        <option value="<?= $row_packs['id'] ?>" data-count="<?= $row_packs['pack_count'] ?>" ><?= $row_packs['pack'] ?> ( <?= $row_packs['pack_count'] ?> )</option>
                                    <?php   
                                    }
                                    ?>
                                </optgroup>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Quantity</label>
                            <input type="text" id="quantity_ttl_add" name="quantity_ttl" class="form-control"  />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" id="Date" name="Date" class="form-control"  />
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="form-buttons text-right">
                <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
            </div>
        </form>
        <?php
    }

    if ($action == "duplicate_product") {
        $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $result = $conn->query("SELECT * FROM product WHERE product_id = '$product_id'");

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $new_product_name = "Copy - " . $row['product_item'];

            $columns = [];
            $columns_sql = $conn->query("SHOW COLUMNS FROM product");
            while ($col = $columns_sql->fetch_assoc()) {
                if ($col['Field'] !== 'product_id') $columns[] = $col['Field'];
            }
            $columns_list = implode(", ", $columns);
            $columns_select = implode(", ", array_map(function($col) use ($row, $conn, $new_product_name) {
                return $col === 'product_item'
                    ? "'" . mysqli_real_escape_string($conn, $new_product_name) . "'"
                    : "product.$col";
            }, $columns));

            $conn->query("INSERT INTO product ($columns_list) SELECT $columns_select FROM product WHERE product_id = '$product_id'");
            $new_product_id = $conn->insert_id;

            $conn->query("
                INSERT INTO product_color_assign (product_id, color_id, date, time, assigned_by)
                SELECT '$new_product_id', color_id, date, time, assigned_by
                FROM product_color_assign
                WHERE product_id = '$product_id'
            ");

            $lines   = json_decode($row['product_line'], true) ?: [$row['product_line']];
            $types   = json_decode($row['product_type'], true) ?: [$row['product_type']];
            $grades  = json_decode($row['grade'], true) ?: [$row['grade']];
            $gauges  = json_decode($row['gauge'], true) ?: [$row['gauge']];
            $lengths = json_decode($row['available_lengths'], true) ?: [$row['available_lengths']];
            $colors  = json_decode($row['color'], true) ?: [$row['color']];

            $combinations = array_combinations([
                'product_line' => $lines,
                'product_type' => $types,
                'grade'        => $grades,
                'gauge'        => $gauges,
                'dimension_id' => $lengths,
                'color_id'     => $colors
            ]);

            if (!empty($combinations)) {
                $values = [];
                foreach ($combinations as $c) {
                    $values[] = "(
                        '$new_product_id',
                        '".implode("','", array_map('intval', $c))."',
                        0
                    )";
                }
                $conn->query("INSERT IGNORE INTO inventory (Product_id, product_line, product_type, grade, gauge, dimension_id, color_id, quantity_ttl) VALUES ".implode(',', $values));
            }

            echo "success";
        } else {
            echo "Product not found.";
        }
    }
    
    if ($action == 'fetch_products') {
        $permission = $_SESSION['permission'];
        $data = [];
        $query = "
            SELECT 
                p.*, 
                COALESCE(SUM(i.quantity_ttl), 0) AS total_quantity 
            FROM product AS p 
            LEFT JOIN inventory AS i ON p.product_id = i.product_id 
            WHERE p.hidden = 0 
            GROUP BY p.product_id
        ";
        $result = mysqli_query($conn, $query);
        $no = 1;
    
        while ($row = mysqli_fetch_assoc($result)) {
            $product_id = $row['product_id'];
            $status = $row['status'];
            $instock = $row['total_quantity'] > 1 ? 1 : 0;
            $category_id = $row['product_category'];
    
            $status_html = $status == 1
                ? "<a href='#'><div id='status-alert$no' class='changeStatus alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' data-no='$no' data-id='$product_id' data-status='1' style='border-radius: 5%;'>Active</div></a>"
                : "<a href='#'><div id='status-alert$no' class='changeStatus alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' data-no='$no' data-id='$product_id' data-status='0' style='border-radius: 5%;'>Inactive</div></a>";
    
            $picture_path = !empty($row['main_image']) ? $row['main_image'] : "images/product/product.jpg";
    
            $product_name_html = "
                <a href='javascrip:void(0)' id='view_product_details' data-id='{$product_id}'>
                    <div class='d-flex align-items-center'>
                        <img src='{$picture_path}' class='rounded-circle' width='56' height='56'>
                        <div class='ms-3'>
                            <h6 class='fw-semibold mb-0 fs-4'>{$row['product_item']}</h6>
                        </div>
                    </div>
                </a>";
    
            $action_html = '';
            if ($permission === 'edit') {
                $action_html = "
                    <div class='action-btn text-center'>
                        <a href='javascript:void(0)' 
                        id='view_product_btn' 
                        title='View' 
                        class='text-primary edit' 
                        data-id='{$product_id}' 
                        data-category='{$row['product_category']}'>
                        <i class='ti ti-eye fs-7'></i>
                        </a>
                        <a href='javascript:void(0)' 
                        id='edit_product_btn' 
                        title='Edit' 
                        class='text-warning edit'
                        data-id='{$product_id}' 
                        data-category='{$row['product_category']}'
                        data-line='{$row['product_line']}'
                        data-type='{$row['product_type']}'
                        data-system='{$row['product_system']}'
                        data-grade='{$row['grade']}'
                        data-gauge='{$row['gauge']}'
                        data-profile='{$row['profile']}'
                        data-color='{$row['color']}'>
                        <i class='ti ti-pencil fs-7'></i>
                        </a>
                        <a href='javascript:void(0)' id='duplicate_product_btn' title='Duplicate' class='text-info edit' data-id='{$product_id}' data-category='{$category_id}'><i class='ti ti-copy fs-7'></i></a>
                        <a href='javascript:void(0)' id='add_inventory_btn' title='Add Inventory' class='text-secondary edit' data-id='{$product_id}' data-category='{$category_id}'><i class='ti ti-plus fs-7'></i></a>
                        <a href='javascript:void(0)' id='delete_product_btn' title='Archive' class='text-danger edit hideProduct' data-no='{$no}' data-id='{$product_id}' data-status='{$status}'><i class='ti ti-trash fs-7'></i></a>
                    </div>";

            }
    
            $data[] = [
                'product_name_html'   => $product_name_html,
                'product_category'    => getProductCategoryName($row['product_category']),
                'product_system'      => getColumnFromTable("product_system", "product_system", $row['product_system']),
                'product_gauge'       => getColumnFromTable("product_gauge", "product_gauge", $row['gauge']),
                'product_line'       => getColumnFromTable("product_line", "product_line", $row['product_line']),
                'product_type'        => getColumnFromTable("product_type", "product_type", $row['product_type']),
                'profile'             => getColumnFromTable("profile_type", "profile_type", $row['profile']),
                'color'               => getColorName($row['color']),
                'grade'               => $row['grade'],
                'gauge'               => $row['gauge'],
                'type'                => $row['product_type'],
                'line'                => $row['product_line'],
                'active'              => $status,
                'instock'             => $instock,
                'status_html'         => $status_html,
                'action_html'         => $action_html
            ];
    
            $no++;
        }
    
        echo json_encode(['data' => $data]);
    }

    if ($action == 'fetch_details_modal') {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);

        $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <style>
            #sync1 .item img {
                width: 100%;
                height: 400px;
                object-fit: contain;
                object-position: center;
                border-radius: 6px;
                display: block;
            }
            #sync2 .item img {
                width: 100%;
                height: 50px;
                object-fit: contain;
                object-position: center;
                border-radius: 4px;
                display: block;
            }
            </style>
            <div class="row">
                <div class="col-lg-6">
                    <div id="sync1" class="owl-carousel owl-theme">
                        <?php
                            $query_prod_img = "SELECT * FROM product_images WHERE productid = '$product_id'";
                            $result_prod_img = mysqli_query($conn, $query_prod_img);  

                            if ($result_prod_img && mysqli_num_rows($result_prod_img) > 0) {
                                while ($row_prod_img = mysqli_fetch_array($result_prod_img)) {
                                    $image_url = !empty($row_prod_img['image_url'])
                                        ? $row_prod_img['image_url']
                                        : "images/product/product.jpg";
                                    ?>
                                    <div class="item rounded overflow-hidden">
                                        <img src="<?=$image_url?>" alt="materialpro-img" class="img-fluid">
                                    </div>
                                    <?php 
                                }
                            } else {
                                ?>
                                <div class="item rounded overflow-hidden">
                                    <img src="images/product/product.jpg" alt="materialpro-img" class="img-fluid">
                                </div>
                                <?php
                            } 
                        ?> 
                    </div>

                    <div id="sync2" class="owl-carousel owl-theme">
                        <?php
                            if ($result_prod_img && mysqli_num_rows($result_prod_img) > 0) {
                                mysqli_data_seek($result_prod_img, 0);
                                while ($row_prod_img = mysqli_fetch_array($result_prod_img)) {
                                    $image_url = !empty($row_prod_img['image_url'])
                                        ? $row_prod_img['image_url']
                                        : "images/product/product.jpg";
                                    ?>
                                    <div class="item rounded overflow-hidden">
                                        <img src="<?=$image_url?>" alt="materialpro-img" class="img-fluid">
                                    </div>
                                    <?php 
                                }
                            } else {
                                ?>
                                <div class="item rounded overflow-hidden">
                                    <img src="images/product/product.jpg" alt="materialpro-img" class="img-fluid">
                                </div>
                                <?php
                            } 
                        ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="shop-content">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <?php
                            $totalQuantity = getProductStockTotal($row['product_id']);
                            if ($totalQuantity > 0) {
                                ?>
                                <span class="badge text-bg-success fs-2 fw-semibold">In Stock</span>
                                <?php
                            } else {
                                ?>
                                <span class="badge text-bg-danger fs-2 fw-semibold">Out of Stock</span>
                                <?php
                            }
                            ?>

                            <span class="fs-2"><?= getProductCategoryName($row['product_category']) ?></span>
                        </div>

                        <h4><?= $row['product_item'] ?></h4>

                        <?php
                        $lumber_id = 1;
                        $inventoryList = getAvailableInventory($row['product_id']);
                        $product = getProductDetails($row['product_id']);
                        $category_id = $product['product_category'];

                        if (!empty($inventoryList)) {
                            if ($category_id == $lumber_id) {
                                usort($inventoryList, function($a, $b) {
                                    return strcasecmp($a['lumber_type'], $b['lumber_type']);
                                });
                            }
                            ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>
                                                <?= ($category_id == $lumber_id) ? "Lumber Type" : "Color" ?>
                                            </th>
                                            <th>Dimensions</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inventoryList as $inv) { ?>
                                            <tr>
                                                <td>
                                                    <?php if ($category_id == $lumber_id) { ?>
                                                        <?= htmlspecialchars(ucwords($inv['lumber_type'] ?? 'None')) ?>
                                                    <?php } else { ?>
                                                        <?php if (!empty($inv['color_id'])) { ?>
                                                            <span class="d-inline-block rounded-circle me-2" 
                                                                style="width:20px; height:20px; background-color:<?= getColorHexFromColorID($inv['color_id']) ?>;">
                                                            </span>
                                                        <?php } else { ?>
                                                            None
                                                        <?php } ?>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($inv['dimension'] . ' ' . $inv['dimension_unit']) ?>
                                                </td>
                                                <td>$<?= number_format($inv['price'], 2) ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php
                        }
                        ?>

                    </div>
                </div>

                <div class="col-lg-12">
                    <?php
                        $statusMessages = [];
                        if (!empty($row['on_sale']) && $row['on_sale'] == 1) {
                            $statusMessages[] = "<span class='badge bg-success me-1'>On Sale</span>";
                        }
                        if (!empty($row['on_promotion']) && $row['on_promotion'] == 1) {
                            $statusMessages[] = "<span class='badge bg-warning text-dark'>On Promotion</span>";
                        }
                        
                        if (!empty($statusMessages)) {
                            echo "<h5>Status: " . implode(" & ", $statusMessages) . "</h5>";

                            if (!empty($row['reason'])) {
                                echo "<p class='mb-0'><em>Reason:</em> " . htmlspecialchars($row['reason']) . "</p>";
                            }
                        }
                    ?>
                </div>
            </div>
            <script>
                $(function () {
                    // product detail

                    var sync1 = $("#sync1");
                    var sync2 = $("#sync2");
                    var slidesPerPage = 4;
                    var syncedSecondary = true;

                    sync1
                        .owlCarousel({
                        items: 1,
                        slideSpeed: 2000,
                        nav: false,
                        autoplay: false,
                        dots: true,
                        loop: true,
                        rtl: true,
                        responsiveRefreshRate: 200,
                        navText: [
                            '<svg width="12" height="12" height="100%" viewBox="0 0 11 20"><path style="fill:none;stroke-width: 3px;stroke: #fff;" d="M9.554,1.001l-8.607,8.607l8.607,8.606"/></svg>',
                            '<svg width="12" height="12" viewBox="0 0 11 20" version="1.1"><path style="fill:none;stroke-width: 3px;stroke: #fff;" d="M1.054,18.214l8.606,-8.606l-8.606,-8.607"/></svg>',
                        ],
                        })
                        .on("changed.owl.carousel", syncPosition);

                    sync2
                        .on("initialized.owl.carousel", function () {
                        sync2.find(".owl-item").eq(0).addClass("current");
                        })
                        .owlCarousel({
                        items: slidesPerPage,
                        items: 6,
                        margin: 16,
                        dots: true,
                        nav: false,
                        rtl: true,
                        smartSpeed: 200,
                        slideSpeed: 500,
                        slideBy: slidesPerPage,
                        responsiveRefreshRate: 100,
                        })
                        .on("changed.owl.carousel", syncPosition2);

                    function syncPosition(el) {
                        var count = el.item.count - 1;
                        var current = Math.round(el.item.index - el.item.count / 2 - 0.5);

                        if (current < 0) {
                        current = count;
                        }
                        if (current > count) {
                        current = 0;
                        }

                        sync2
                        .find(".owl-item")
                        .removeClass("current")
                        .eq(current)
                        .addClass("current");
                        var onscreen = sync2.find(".owl-item.active").length - 1;
                        var start = sync2.find(".owl-item.active").first().index();
                        var end = sync2.find(".owl-item.active").last().index();

                        if (current > end) {
                        sync2.data("owl.carousel").to(current, 100, true);
                        }
                        if (current < start) {
                        sync2.data("owl.carousel").to(current - onscreen, 100, true);
                        }
                    }

                    function syncPosition2(el) {
                        if (syncedSecondary) {
                        var number = el.item.index;
                        sync1.data("owl.carousel").to(number, 100, true);
                        }
                    }

                    sync2.on("click", ".owl-item", function (e) {
                        e.preventDefault();
                        var number = $(this).index();
                        sync1.data("owl.carousel").to(number, 300, true);
                    });
                    });
            </script>
            
    <?php
        }
    }
    
    mysqli_close($conn);
}
?>
