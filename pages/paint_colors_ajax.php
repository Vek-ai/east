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

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "add_update") {
        $color_id            = mysqli_real_escape_string($conn, $_POST['color_id']);
        $color_name          = mysqli_real_escape_string($conn, $_POST['color_name'] ?? '');
        $color_code          = mysqli_real_escape_string($conn, $_POST['color_code'] ?? '');
        $color_group         = mysqli_real_escape_string($conn, $_POST['color_group'] ?? '');
        $provider_id         = mysqli_real_escape_string($conn, $_POST['provider'] ?? '');
        $ekm_color_code      = mysqli_real_escape_string($conn, $_POST['ekm_color_code'] ?? '');
        $ekm_color_no        = mysqli_real_escape_string($conn, $_POST['ekm_color_no'] ?? '');
        $ekm_paint_code      = mysqli_real_escape_string($conn, $_POST['ekm_paint_code'] ?? '');
        $ekm_color_name      = mysqli_real_escape_string($conn, $_POST['ekm_color_name'] ?? '');
        $color_abbreviation  = mysqli_real_escape_string($conn, $_POST['color_abbreviation'] ?? '');
        $stock_availability  = mysqli_real_escape_string($conn, $_POST['stock_availability'] ?? '');
        $multiplier_category = mysqli_real_escape_string($conn, $_POST['multiplier_category'] ?? '');
        $ranking             = mysqli_real_escape_string($conn, $_POST['ranking'] ?? '');
        $notes               = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
        $userid              = mysqli_real_escape_string($conn, $_POST['userid']);
        $use_ekm_color_name  = isset($_POST['use_ekm_color_name']) ? 1 : 0;

        $product_category = mysqli_real_escape_string(
            $conn, json_encode(array_map('intval', $_POST['product_category'] ?? []))
        );
        $grade = mysqli_real_escape_string(
            $conn, json_encode(array_map('intval', $_POST['grade'] ?? []))
        );
        $gauge = mysqli_real_escape_string(
            $conn, json_encode(array_map('intval', $_POST['gauge'] ?? []))
        );
        $profile = mysqli_real_escape_string(
            $conn, json_encode(array_map('intval', $_POST['profile'] ?? []))
        );

        $checkQuery = "SELECT color_abbreviation FROM paint_colors WHERE color_id = '$color_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $old_abbr = $row['color_abbreviation'];

            $updateQuery = "UPDATE paint_colors SET 
                color_name = '$color_name', 
                color_code = '$color_code', 
                ekm_color_no = '$ekm_color_no', 
                ekm_paint_code = '$ekm_paint_code', 
                ekm_color_name = '$ekm_color_name',
                color_group = '$color_group', 
                product_category = '$product_category', 
                grade = '$grade',
                gauge = '$gauge',
                profile = '$profile',
                provider_id = '$provider_id', 
                last_edit = NOW(), 
                edited_by = '$userid', 
                ekm_color_code = '$ekm_color_code', 
                color_abbreviation = '$color_abbreviation', 
                stock_availability = '$stock_availability', 
                multiplier_category = '$multiplier_category', 
                ranking = '$ranking',
                notes = '$notes',
                use_ekm_color_name = '$use_ekm_color_name'
                WHERE color_id = '$color_id'";

            if (mysqli_query($conn, $updateQuery)) {
                echo "update-success";
                if ($old_abbr !== $color_abbreviation) {
                    regenerateABR('paint_colors', $color_id);
                }
            } else {
                echo "Error updating paint color: " . mysqli_error($conn);
            }
        } else {
            $insertQuery = "INSERT INTO paint_colors 
                (color_name, color_code, ekm_color_no, ekm_paint_code, ekm_color_name, color_group, product_category, grade, gauge, profile, provider_id, added_date, added_by, ekm_color_code, color_abbreviation, stock_availability, multiplier_category, ranking, notes, use_ekm_color_name) 
                VALUES 
                ('$color_name', '$color_code', '$ekm_color_no', '$ekm_paint_code', '$ekm_color_name', '$color_group', '$product_category', '$grade', '$gauge', '$profile', '$provider_id', NOW(), '$userid', '$ekm_color_code', '$color_abbreviation', '$stock_availability', '$multiplier_category', '$ranking', '$notes', '$use_ekm_color_name')";

            if (mysqli_query($conn, $insertQuery)) {
                echo "add-success";
            } else {
                echo "Error adding paint color: " . mysqli_error($conn);
            }
        }
    }

   

    if ($action == "fetch_update_modal") {
        $color_id = mysqli_real_escape_string($conn, $_POST['id']);
        $color_details = getColorDetails($color_id);
        ?>
        <input type="hidden" id="color_id" name="color_id" class="form-control" value="<?= $color_id ?>"/>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Paint Color Identifiers</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Paint Color Name</label>
                            <input type="text" id="color_name" name="color_name" class="form-control"  value="<?= $color_details['color_name'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Hex Color Code</label>
                            <input type="color" id="color_code" name="color_code" class="form-control" value="<?= $color_details['color_code'] ?? '' ?>" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Provider</label>
                        <div class="mb-3">
                            <select id="provider" class="form-control select2-edit" name="provider">
                                <option value="" >Select One...</option>
                                <?php
                                $query_rows = "SELECT * FROM paint_providers";
                                $result_rows = mysqli_query($conn, $query_rows);            
                                while ($row_rows = mysqli_fetch_array($result_rows)) {
                                $selected = ($row_rows['provider_id'] == ($color_details['provider_id'] ?? '')) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_rows['provider_id'] ?>" <?= $selected ?> ><?= $row_rows['provider_name'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Color Group</label>
                            </div>
                            <select id="color_group" class="form-control select2-edit" name="color_group">
                                <option value="">Select Color Group...</option>
                                <?php
                                $query_color_group = "
                                    SELECT * FROM product_color ORDER BY color_name ASC
                                ";

                                $result_color_group = mysqli_query($conn, $query_color_group);
                                while ($row_color_group = mysqli_fetch_array($result_color_group)) {
                                    $selected = (($color_details['color_group'] ?? '') == $row_color_group['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_color_group['id'] ?>" <?= $selected ?>>
                                        <?= $row_color_group['color_name'] ?>
                                    </option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Color Abbreviation</label>
                            <input type="text" id="color_abbreviation" name="color_abbreviation" class="form-control"  value="<?= $color_details['color_abbreviation'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-4"></div>

                    <div class="col-md-4 text-center">
                        <label for="use_ekm_color_name" class="form-label d-block">Use EKM Color Name</label>
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            id="use_ekm_color_name" 
                            name="use_ekm_color_name"
                            value="1"
                            <?= !empty($color_details['use_ekm_color_name']) && $color_details['use_ekm_color_name'] == 1 ? 'checked' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">EKM Color Name</label>
                            <input type="text" id="ekm_color_name" name="ekm_color_name" class="form-control"  value="<?= $color_details['ekm_color_name'] ?? '' ?>"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">EKM Color No</label>
                            <input type="number" id="ekm_color_no" name="ekm_color_no" class="form-control" value="<?= $color_details['ekm_color_no'] ?? '' ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $selected_categories = !empty($color_details['product_category']) ? json_decode($color_details['product_category'], true) : [];
        $selected_grades     = !empty($color_details['grade']) ? json_decode($color_details['grade'], true) : [];
        $selected_gauges     = !empty($color_details['gauge']) ? json_decode($color_details['gauge'], true) : [];
        $selected_profiles   = !empty($color_details['profile']) ? json_decode($color_details['profile'], true) : [];

        $selected_categories = is_array($selected_categories) ? $selected_categories : [];
        $selected_grades     = is_array($selected_grades) ? $selected_grades : [];
        $selected_gauges     = is_array($selected_gauges) ? $selected_gauges : [];
        $selected_profiles   = is_array($selected_profiles) ? $selected_profiles : [];
        ?>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Paint Color Availablity</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Availability</label>
                        <div class="mb-3">
                            <select id="stock_availability_add" class="form-control select2-edit" name="stock_availability">
                                <option value="" >Select Availability...</option>
                                <?php
                                $query_availability = "SELECT * FROM product_availability";
                                $result_availability = mysqli_query($conn, $query_availability);            
                                while ($row_availability = mysqli_fetch_array($result_availability)) {
                                $selected = ($row_availability['product_availability_id'] == ($color_details['stock_availability'] ?? '')) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row_availability['product_availability_id'] ?>" <?= $selected ?> ><?= $row_availability['product_availability'] ?></option>
                                <?php   
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Product Category</label>
                        <div class="mb-3">
                            <select id="product_category" class="form-control select2-edit" name="product_category[]" multiple>
                                <?php
                                $query_roles = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                $result_roles = mysqli_query($conn, $query_roles);            
                                while ($row_product_category = mysqli_fetch_array($result_roles)) {
                                    $selected = in_array($row_product_category['product_category_id'], $selected_categories) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $row_product_category['product_category_id'] ?>"
                                            data-category="<?= $row_product_category['product_category'] ?>"
                                            data-filename="<?= $row_product_category['color_group_filename'] ?>"
                                            <?= $selected ?>>
                                        <?= $row_product_category['product_category'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Product Grade</label>
                            <a href="?page=product_grade" target="_blank" class="text-decoration-none">Edit</a>
                        </div>
                        <div class="mb-3">
                            <select id="product_grade" class="form-control select2-edit add-category" name="grade[]" multiple>
                                <?php
                                $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY product_grade";
                                $result_grade = mysqli_query($conn, $query_grade);
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                    $selected = in_array($row_grade['product_grade_id'], $selected_grades) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $row_grade['product_grade_id'] ?>" 
                                            data-category="<?= $row_grade['product_category'] ?>" 
                                            <?= $selected ?>>
                                        <?= $row_grade['product_grade'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Product Gauge</label>
                                <a href="?page=product_gauge" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <select id="gauge" class="form-control select2-edit" name="gauge[]" multiple>
                                <?php
                                $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1'";
                                $result_gauge = mysqli_query($conn, $query_gauge);

                                $existing_gauges = [];
                                while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                    if (!in_array($row_gauge['product_gauge'], $existing_gauges)) {
                                        $existing_gauges[] = $row_gauge['product_gauge'];
                                        $selected = in_array($row_gauge['product_gauge_id'], $selected_gauges) ? 'selected' : '';
                                        ?>
                                        <option value="<?= htmlspecialchars($row_gauge['product_gauge_id']) ?>" 
                                                data-multiplier="<?= htmlspecialchars($row_gauge['multiplier']) ?>" 
                                                <?= $selected ?>>
                                            <?= htmlspecialchars($row_gauge['product_gauge']) ?>
                                        </option>
                                    <?php }
                                } ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Product Profile</label>
                                <a href="?page=profile_type" target="_blank" class="text-decoration-none">Edit</a>
                            </div>
                            <select id="profile" class="form-control select2-edit add-category" name="profile[]" multiple>
                                <?php
                                $query_profile_type = "SELECT * FROM profile_type WHERE hidden = '0' AND status = '1'";
                                $result_profile_type = mysqli_query($conn, $query_profile_type);            
                                while ($row_profile_type = mysqli_fetch_array($result_profile_type)) {
                                    $selected = in_array($row_profile_type['profile_type_id'], $selected_profiles) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $row_profile_type['profile_type_id'] ?>" 
                                            data-category="<?= $row_profile_type['product_category'] ?>"  
                                            <?= $selected ?>>
                                        <?= $row_profile_type['profile_type'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 mb-3">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0 fw-bold">Color Notes</h5>
            </div>
            <div class="card-body border rounded p-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <textarea 
                                id="notes" 
                                name="notes" 
                                class="form-control" 
                                rows="5"
                            ><?= htmlspecialchars($color_details['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php
    }
    
    if ($action == "change_status") {
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE paint_colors SET color_status = '$new_status' WHERE color_id = '$color_id'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }

    if ($action == 'hide_paint_color') {
        $color_id = mysqli_real_escape_string($conn, $_POST['color_id']);
        $query = "UPDATE paint_colors SET hidden='1' WHERE color_id='$color_id'";
        if (mysqli_query($conn, $query)) {
            echo 'success';
        } else {
            echo 'error';
        }
    }

    if ($action == "fetch_uploaded_modal") {
        $table = "paint_colors_excel";
        
        $sql = "SELECT * FROM $table";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $columns = array_keys($row);
            $result->data_seek(0);

            $includedColumns = [ 
                'color_id',
                'color_name',
                'color_code',
                'ekm_color_code',
                'ekm_color_no',
                'product_category',
                'color_group',
                'provider_id',
                'color_abbreviation',
                'stock_availability'
            ];

            $columns = array_filter($columns, fn($col) => in_array($col, $includedColumns));

            

            $columnsWithData = [];
            while ($row = $result->fetch_assoc()) {
                foreach ($columns as $column) {
                    if (isset($row[$column]) && trim($row[$column]) !== '') {
                        $columnsWithData[$column] = true;
                    }
                }
            }

            $result->data_seek(0);
            ?>
            
            <div class="card card-body shadow">
                <form id="tableForm">
                    <div style="overflow-x: auto; overflow-y: auto; max-height: 80vh; max-width: 100%;">
                        <table class="table table-bordered table-striped text-center">
                            <thead>
                                <tr>
                                    <?php
                                    foreach ($columns as $column) {
                                        if ($column === 'color_code') {
                                            echo "<th class='fs-4'>Hex Color Code</th>";
                                        } elseif (isset($columnsWithData[$column])) {
                                            $formattedColumn = ucwords(str_replace('_', ' ', $column));
                                            echo "<th class='fs-4'>" . $formattedColumn . "</th>";
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                while ($row = $result->fetch_assoc()) {
                                    $color_id = $row['color_id'];
                                    echo '<tr>';
                                    foreach ($columns as $column) {
                                        if (isset($columnsWithData[$column])) {
                                            $value = $row[$column] ?? '';
                                            echo "<td contenteditable='true' class='table_data' data-header-name='".$column."' data-id='".$color_id."'>$value</td>";
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
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $table_test = 'paint_colors_excel';

            if ($fileExtension != "xlsx" && $fileExtension != "xls") {
                echo "Please upload a valid Excel file.";
                exit;
            }

            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $columns = $rows[0];
            $dbColumns = [];
            $columnMapping = [];

            foreach ($columns as $col) {
                if($col == 'Hex Color Code'){
                    $dbColumn = 'color_code';
                }else{
                    $dbColumn = strtolower(str_replace(' ', '_', $col));
                }
                $dbColumns[] = $dbColumn;
                $columnMapping[$dbColumn] = $col;
            }

            $truncateSql = "TRUNCATE TABLE $table_test";
            $truncateResult = $conn->query($truncateSql);

            if (!$truncateResult) {
                echo "Error truncating table: " . $conn->error;
                exit;
            }

            foreach ($rows as $index => $row) {
                if ($index == 0) {
                    continue;
                }

                $data = array_combine($dbColumns, $row);

                $columnNames = implode(", ", array_keys($data));
                $columnValues = implode("', '", array_map(function($value) { return $value ?? ''; }, array_values($data)));

                $sql = "INSERT INTO $table_test ($columnNames) VALUES ('$columnValues')";
                $result = $conn->query($sql);

                if (!$result) {
                    echo "Error inserting data: " . $conn->error;
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
        $table = "paint_colors";
        
        $selectSql = "SELECT * FROM paint_colors_excel";
        $result = $conn->query($selectSql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $color_id = trim($row['color_id'] ?? ''); 

                unset($row['id']);

                if (!empty($color_id)) {
                    $checkSql = "SELECT COUNT(*) as count FROM $table WHERE color_id = '$color_id'";
                    $checkResult = $conn->query($checkSql);
                    $exists = $checkResult->fetch_assoc()['count'] > 0;

                    if ($exists) {
                        $updateFields = [];
                        foreach ($row as $column => $value) {
                            if ($column !== 'color_id') {
                                $updateFields[] = "$column = '$value'";
                            }
                        }
                        $updateSql = "UPDATE $table SET " . implode(", ", $updateFields) . " WHERE color_id = '$color_id'";
                        $conn->query($updateSql);
                        continue;
                    }
                }

                $columns = implode(", ", array_keys($row));
                $values = implode("', '", array_values($row));
                $insertSql = "INSERT INTO $table ($columns) VALUES ('$values')";
                $conn->query($insertSql);
            }

            echo "Data has been successfully saved";

            $truncateSql = "TRUNCATE TABLE paint_colors_excel";
            if ($conn->query($truncateSql) !== TRUE) {
                echo " but failed to clear test color table: " . $conn->error;
            }
        } else {
            echo "No data found in test color table.";
        }
    }

    if ($action == "download_excel") {
        $product_category = mysqli_real_escape_string($conn, $_REQUEST['category'] ?? '');
        $category_name = strtoupper(getProductCategoryName($product_category));

        $includedColumns = array();
        $column_txt = '*';

        $includedColumns = [ 
            'color_id',
            'color_name',
            'color_code',
            'ekm_color_code',
            'ekm_color_no',
            'ranking',
            'product_category',
            'color_group',
            'provider_id',
            'color_abbreviation',
            'stock_availability'
        ];

        $column_txt = implode(', ', $includedColumns);

        $sql = "SELECT " . $column_txt . " FROM paint_colors WHERE hidden = '0' AND color_status = '1'";
        if (!empty($product_category)) {
            $sql .= " AND product_category = '$product_category'";
        }
        $result = $conn->query($sql);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [];
        $row = 1;
        
        foreach ($includedColumns as $index => $column) {
            if ($column === 'color_code') {
                $header = 'Hex Color Code';
            } else {
                $header = ucwords(str_replace('_', ' ', $column));
            }
        
            if ($index >= 26) {
                $columnLetter = indexToColumnLetter($index);
            } else {
                $columnLetter = chr(65 + $index);
            }
        
            $sheet->setCellValue($columnLetter . $row, $header);
        }        

        $row = 2;
        while ($data = $result->fetch_assoc()) {
            foreach ($includedColumns as $index => $column) {
                if ($index >= 26) {
                    $columnLetter = indexToColumnLetter($index);
                } else {
                    $columnLetter = chr(65 + $index);
                }
                $sheet->setCellValue($columnLetter . $row, $data[$column] ?? '');
            }
            $row++;
        }

        $filename = "$category_name COLORS.xlsx";
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
            'color_group' => [
                'columns' => ['color_group_name_id', 'color_group_name'],
                'table' => 'color_group_name',
                'where' => "status = '1'"
            ],
            'paint_providers' => [
                'columns' => ['provider_id', 'provider_name'],
                'table' => 'paint_providers',
                'where' => "provider_status = '1'"
            ],
            'availability' => [
                'columns' => ['product_availability_id', 'product_availability'],
                'table' => 'product_availability',
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
                        
                    $sheet->setCellValue($columnLetter . $row, $value);
                }
                $row++;
            }
        }

        if(empty($classification)){
            $classification = 'Color Classifications';
        }else{
            $classification = ucwords($classification);
        }

        $filename = "$classification Color Classifications.xlsx";
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

    if ($action == "update_color_data") {
        $column_name = $_POST['header_name'];
        $new_value = $_POST['new_value'];
        $color_id = $_POST['id'];
        
        if (empty($column_name) || empty($color_id)) {
            exit;
        }
        
        $column_name = mysqli_real_escape_string($conn, $column_name);
        $new_value = mysqli_real_escape_string($conn, $new_value);
        $color_id = mysqli_real_escape_string($conn, $color_id);
        
        $sql = "UPDATE paint_colors_excel SET `$column_name` = '$new_value' WHERE color_id = '$color_id'";

        if ($conn->query($sql) === TRUE) {
            echo 'Success';
        } else {
            echo 'Error updating record: ' . $conn->error;
        }
    }

    if ($action === 'fetch_table') {
        $permission = $_SESSION['permission'];
        $query = "SELECT * FROM paint_colors WHERE hidden = 0";
        $result = mysqli_query($conn, $query);

        $assignedColors = [];
        $res = mysqli_query($conn, "SELECT DISTINCT color_id FROM product_color_assign");
        while($r = mysqli_fetch_assoc($res)) {
            $assignedColors[$r['color_id']] = true;
        }
    
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $no = $row['color_id'];

            $isAssigned = isset($assignedColors[$no]);

            
            $color_abbreviation = $row['color_abbreviation'];
            $color_name = $row['color_name'];
            $color_code = $row['color_code'];
            $color_group = getColorGroupName($row['color_group']);
            $provider = getPaintProviderName($row['provider_id']);

            //$product_category = getProductCategoryName($row['product_category']);
            $category_ids = json_decode($row['product_category'], true);
            $category_ids = is_array($category_ids) ? $category_ids : [];

            $product_category = implode(', ', array_unique(array_map('getProductCategoryName', $category_ids)));

            $availability_details = getAvailabilityDetails($row['stock_availability']);
            $availability = $availability_details['product_availability'] ?? '';
    
            $added_by = $row['added_by'];
            $edited_by = $row['edited_by'];
    
            if ($edited_by != "0") {
                $last_user_name = get_customer_name($edited_by);
            } elseif ($added_by != "0") {
                $last_user_name = get_customer_name($added_by);
            } else {
                $last_user_name = "";
            }

            $last_edit = '';
            if (!empty($row['last_edit'])) {
                $dt = new DateTime($row['last_edit']);
                $last_edit = $dt->format('m/d/Y');
            }

            $notes = !empty($row['notes']) 
            ? (strlen($row['notes']) > 30 ? substr($row['notes'], 0, 30) . '...' : $row['notes']) 
            : '';
    
            if ($isAssigned) {
                $status_html = "<div class='alert alert-success border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;'>Assigned</div>";
                $status_assigned = 1;
            } else {
                $status_html = "<div class='alert alert-warning border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;'>Pending</div>";
                $status_assigned = 0;
            }

    
            $action_html = '';
            if ($permission === 'edit') {
                $action_html .= "
                    <a href='javascript:void(0)' id='addModalBtn' title='View' class='d-flex align-items-center justify-content-center text-decoration-none' data-id='$no' data-type='view'>
                            <i class='ti ti-eye fs-7'></i>
                    </a>
                    <a href='javascript:void(0)' id='addModalBtn' title='Edit' class='d-flex align-items-center justify-content-center text-decoration-none' data-id='$no' data-type='edit'>
                            <i class='ti ti-pencil fs-7'></i>
                    </a>
                    <a href='javascript:void(0)' title='Copy' class='copy_color_btn ms-2' data-id='$no'>
                        <i class='ti ti-copy fs-7'></i>
                    </a>
                    <a href='javascript:void(0)' title='Delete' class='delete_color_btn ms-2' data-id='$no'>
                        <i class='ti ti-trash fs-7'></i>
                    </a>";
            }

            $product_category_names = getColumnFromTable("product_category", "product_category", $row['product_category']);
            $product_profile_names  = getColumnFromTable("profile_type", "profile_type", $row['profile']);
            $product_grade_names    = getColumnFromTable("product_grade", "product_grade", $row['grade']);
            $product_gauge_names    = getColumnFromTable("product_gauge", "product_gauge", $row['gauge']);

            $product_category_names = (strpos($product_category_names, ',') !== false) ? 'Multiple Categories' : $product_category_names;
            $product_profile_names  = (strpos($product_profile_names, ',') !== false)  ? 'Multiple Profiles'  : $product_profile_names;
            $product_grade_names    = (strpos($product_grade_names, ',') !== false)    ? 'Multiple Grades'    : $product_grade_names;
            $product_gauge_names    = (strpos($product_gauge_names, ',') !== false)    ? 'Multiple Gauges'    : $product_gauge_names;

    
            $data[] = [
                'color_id' => $no,
                'color_abbreviation' => $color_abbreviation,
                'color_name' => $color_name,
                'color_code' => $color_code,
                'ekm_color_name' => $row['ekm_color_name'],
                'color_group' => $color_group,
                'ekm_color_no' => $row['ekm_color_no'],
                'provider' => $provider,
                'availability' => $availability,
                'product_category_names' => $product_category_names,
                'product_profile_names' => $product_profile_names,
                'product_grade_names' => $product_grade_names,
                'product_gauge_names' => $product_gauge_names,
                'notes' => $notes,
                'last_edit_by' => $last_user_name,
                'last_edit' => $last_edit,
                'status' => $status_html,
                'status_html' => $status_html,
                'action_html' => $action_html,
                'product_category' => $row['product_category'],
                'grade' => $row['grade'],
                'gauge' => $row['gauge'],
                'profile' => $row['profile'],
                'status_assigned' => $status_assigned,
            ];
        }
    
        echo json_encode(['data' => $data]);
        exit;
    }

    if ($action == "assign_color") {
        $assignedBy = $_SESSION['userid']; 

        $selectedCategories = $_POST['selectedCategories'] ?? [];
        $selectedDate = $_POST['select_date'] ?? '';
        $selectedTime = $_POST['select_time'] ?? '';
        $selectedColors = json_decode($_POST['selectedIds'], true);

        if (empty($selectedCategories)) {
            echo 'Please select categories first';
            exit;
        }

        if (empty($selectedColors)) {
            echo 'Please select colors first';
            exit;
        }

        if (!empty($selectedCategories) && !empty($selectedColors)) {
            $categoryIds = is_array($selectedCategories) ? $selectedCategories : [$selectedCategories];
            $escapedIds = array_map('intval', $categoryIds);
            $idsList = implode(',', $escapedIds);

            $query = "SELECT product_id FROM product 
                    WHERE product_category IN ($idsList)
                    AND hidden = 0 AND status = 1";

            $result = mysqli_query($conn, $query);

            $productIds = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $productIds[] = intval($row['product_id']);
            }

            if (empty($productIds)) {
                echo 'No products found for the selected category/categories';
                exit;
            }

            foreach ($productIds as $productId) {
                foreach ($selectedColors as $colorId) {
                    $colorId = intval($colorId);
                    $queryInsert = "INSERT INTO product_color_assign 
                        (product_id, color_id, `date`, `time`, assigned_by) 
                        VALUES ($productId, $colorId, '$selectedDate', '$selectedTime', $assignedBy)";
                    
                    mysqli_query($conn, $queryInsert);
                }
            }

            echo 'success';
        } else {
            echo 'No categories or colors selected';
        }
    }



    mysqli_close($conn);
}
?>
