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

$table = 'test';
//4 = TRIM
$trim_id = 4;
$category_id = 4;

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action']; 
    
    if ($action == 'fetch_products') {
        $permission = $_SESSION['permission'];
        $data = [];

        $saleItems = getSaleItems();
        $saleMap = [];
        foreach ($saleItems as $s) {
            $saleMap[$s['product_id']] = $s;
        }

        $query = "SELECT product_id FROM product WHERE hidden = 0";
        $result = mysqli_query($conn, $query);
        $no = 1;

        while ($row = mysqli_fetch_assoc($result)) {
            $product_id = $row['product_id'];
            $details    = getProductDetails($product_id);

            $current_price = floatval($details['price']);
            $discount      = 0;
            $sale_price    = "";
            
            if (isset($saleMap[$product_id])) {
                $sale_price = $saleMap[$product_id]['sale_price'];
                if ($current_price > 0) {
                    $discount = round((1 - ($sale_price / $current_price)) * 100);
                } else {
                    $discount = 0;
                }
            }

            $picture_path = !empty($details['main_image']) ? $details['main_image'] : "images/product/product.jpg";
            $product_name_html = "
                <a href='?page=product_details&product_id={$product_id}'>
                    <div class='d-flex align-items-center'>
                        <img src='{$picture_path}' class='rounded-circle' width='56' height='56'>
                        <div class='ms-3'>
                            <h6 class='fw-semibold mb-0 fs-4'>{$details['product_item']}</h6>";
                            
            if (!empty($details['product_sku'])) {
                $product_name_html .= "
                            <div class='text-muted fs-3'>{$details['product_sku']}</div>";
            }

            $product_name_html .= "
                        </div>
                    </div>
                </a>";

            $status_html = '';
            $active = 0;

            if ($details['status'] == 0) {
                $status_html = "<div id='status-alert$no' class='changeStatus alert alert-danger text-white text-center py-1 px-2 my-0' data-no='$no' data-id='$product_id' data-status='0'>Inactive</div>";
                $active = 0;
            } else if (isset($saleMap[$product_id])) {
                $now = new DateTime();
                $start = ($saleMap[$product_id]['date_started'] !== "0000-00-00 00:00:00") 
                        ? new DateTime($saleMap[$product_id]['date_started']) 
                        : null;
                $end   = ($saleMap[$product_id]['date_finished'] !== "0000-00-00 00:00:00") 
                        ? new DateTime($saleMap[$product_id]['date_finished']) 
                        : null;
                if (is_null($start) || is_null($end)) {
                    $status_html = "<div id='status-alert$no' 
                                    class='changeStatus alert alert-success text-white text-center py-1 px-2 my-0' 
                                    data-no='$no' data-id='$product_id' data-status='1'>On Sale</div>";
                    $active = 1;
                } else {
                    if ($now >= $start && $now <= $end) {
                        $status_html = "<div id='status-alert$no' 
                                        class='changeStatus alert alert-success text-white text-center py-1 px-2 my-0' 
                                        data-no='$no' data-id='$product_id' data-status='1'>On Sale</div>";
                        $active = 1;
                    } else {
                        $status_html = "<div id='status-alert$no' 
                                        class='changeStatus alert alert-warning text-white text-center py-1 px-2 my-0' 
                                        data-no='$no' data-id='$product_id' data-status='0'>Sale Ended</div>";
                        $active = 1;
                    }
                }
            } else {
                $status_html = "<div id='status-alert$no' class='changeStatus alert alert-info text-white text-center py-1 px-2 my-0' data-no='$no' data-id='$product_id' data-status='1'>Active</div>";
                $active = 1;
            }

            $action_html = "<div class='action-btn text-center'>"; 
            $action_html .= "<a href='javascript:void(0)' id='view_product_btn' title='View Sale' class='text-primary' data-id='{$product_id}'><i class='ti ti-eye fs-7'></i></a>"; 
            if ($permission === 'edit') {
                $action_html .= "<a href='javascript:void(0)' id='add_sale_btn' title='Add Sale' class='text-warning' data-id='{$product_id}'><i class='ti ti-plus fs-7'></i></a>";
            }
            $action_html .= "</div>"; 

            $data[] = [
                'product_name_html' => $product_name_html,
                'product_category'  => getProductCategoryName($details['product_category']),
                'product_line'      => getProductLineName($details['product_line']),
                'product_type'      => getProductTypeName($details['product_type']),
                'current_price'     => number_format($current_price, 2),
                'discount'          => $discount > 0 ? $discount . "%" : "",
                'sale_price'        => $sale_price !== "" ? number_format($sale_price, 2) : "",
                'instock'           => $details['quantity_ttl'] > 1 ? 1 : 0,
                'status'            => $status,
                'active'            => $active,
                'status_html'       => $status_html,
                'action_html'       => $action_html
            ];

            $no++;
        }

        echo json_encode(['data' => $data]);
    }

    if ($action == 'fetch_view_modal') {
        $id = intval($_POST['id']);
        
        $query = "SELECT * FROM product WHERE product_id = $id";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);

        $discount_sql = "SELECT * FROM sales_discounts WHERE product_id = $id LIMIT 1";
        $discount_res = mysqli_query($conn, $discount_sql);
        $discount_row = mysqli_fetch_assoc($discount_res);

        $start_date = $discount_row['date_started'] ?? '';
        $end_date   = $discount_row['date_finished'] ?? '';
        $existing_price = $discount_row ? getSalePrice($id) : '';

        if ($row) {
            $picture_path = !empty($row['main_image']) ? $row['main_image'] : "images/product/product.jpg";
            ?>
            <a href='javascript:void(0)'>
                <div class='d-flex align-items-center px-4 mb-3'>
                    <img src='<?= $picture_path ?>' class='rounded-circle' width='56' height='56'>
                    <div class='ms-3'>
                        <h6 class='fw-semibold mb-0 fs-4'><?= $row['product_item'] ?></h6>
                        <div class='text-muted fs-3'>Current Price: <?= $row['unit_price'] ?></div> 
                        <input type="hidden" id="unit_price" 
                            value="<?= $row['unit_price'] ?>" 
                            data-product-id="<?= $row['product_id'] ?>" 
                            data-category-id="<?= $row['product_category'] ?>"> 
                    </div>
                </div>
            </a>
            
            <div class="mb-3">
                <label class="form-label">Discount Type</label>
                <select class="form-select" name="discount_type">
                    <option value="fixed">Set Fixed Price</option>
                    <option value="percent">Percentage Off</option>
                </select>
            </div>
            
            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label">Discount Value</label>
                    <div class="input-group">
                        <input type="number" step="0.01" class="form-control" name="discount_value">
                        <span class="input-group-text">$</span>
                    </div>
                </div>
                <div class="col-6">
                    <label class="form-label">New Price</label>
                    <input type="text" class="form-control" name="new_price" readonly value="<?= $existing_price ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label">Start Date</label>
                    <input type="datetime-local" class="form-control" name="start_date" value="<?= $start_date ?>">
                </div>
                <div class="col-6">
                    <label class="form-label">End Date</label>
                    <input type="datetime-local" class="form-control" name="end_date" value="<?= $end_date ?>">
                </div>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" name="apply_category" id="apply_category">
                <label class="form-check-label" for="apply_category">Apply to entire category</label>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="applyDiscountBtn" class="btn btn-success">Apply Discount</button>
            </div>
            <?php
        } else {
            echo "<div class='alert alert-warning'>Product not found.</div>";
        }
    }

    if ($action == 'apply_discount') {
        $product_id     = intval($_POST['product_id']);
        $category_id    = intval($_POST['category_id']);
        $discount_value = floatval($_POST['discount_value']);
        $discount_type  = $_POST['discount_type'] ?? 'flat';
        $start_date     = mysqli_real_escape_string($conn, $_POST['start_date']);
        $end_date       = mysqli_real_escape_string($conn, $_POST['end_date']);
        $added_by       = $_SESSION['userid'] ?? 0;
        $apply_category = intval($_POST['apply_category']);

        if ($apply_category == 1) {
            $products_sql = "SELECT product_id, price FROM product WHERE product_category = '$category_id'";
            $products_res = mysqli_query($conn, $products_sql);

            while ($p = mysqli_fetch_assoc($products_res)) {
                $pid = intval($p['product_id']);
                $orig_price = floatval($p['price']);
                $discounted_price = $orig_price - ($orig_price * ($discount_value / 100));
                if ($discounted_price < 0) $discounted_price = 0;

                $check = mysqli_query($conn, "SELECT saleid FROM sales_discounts WHERE product_id = '$pid' LIMIT 1");
                if (mysqli_num_rows($check) > 0) {
                    $sql = "UPDATE sales_discounts 
                            SET category_id='$category_id',
                                date_started='$start_date',
                                date_finished='$end_date',
                                added_by='$added_by'
                            WHERE product_id = '$pid'";
                } else {
                    $sql = "INSERT INTO sales_discounts 
                            (category_id, product_id, date_started, date_finished, added_by)
                            VALUES 
                            ('$category_id', '$pid', '$start_date', '$end_date', '$added_by')";
                }
                mysqli_query($conn, $sql);

                mysqli_query($conn, "UPDATE inventory 
                                    SET on_sale = 1, sale_price = '$discounted_price' 
                                    WHERE product_id = '$pid'");
            }
            echo json_encode(['success' => true, 'bulk' => true]);
        } else {
            $res = mysqli_query($conn, "SELECT price FROM product WHERE product_id = '$product_id' LIMIT 1");
            $row = mysqli_fetch_assoc($res);
            $orig_price = floatval($row['price']);

            if ($discount_type === 'percent') {
                $discounted_price = $orig_price - ($orig_price * ($discount_value / 100));
            } else {
                $discounted_price = $discount_value;
            }
            if ($discounted_price < 0) $discounted_price = 0;

            $check = mysqli_query($conn, "SELECT saleid FROM sales_discounts WHERE product_id = '$product_id' LIMIT 1");
            if (mysqli_num_rows($check) > 0) {
                $sql = "UPDATE sales_discounts 
                        SET category_id='$category_id',
                            date_started='$start_date',
                            date_finished='$end_date',
                            added_by='$added_by'
                        WHERE product_id = '$product_id'";
            } else {
                $sql = "INSERT INTO sales_discounts 
                        (category_id, product_id, date_started, date_finished, added_by)
                        VALUES 
                        ('$category_id', '$product_id', '$start_date', '$end_date', '$added_by')";
            }

            if (mysqli_query($conn, $sql)) {
                mysqli_query($conn, "UPDATE inventory 
                                    SET on_sale = 1, sale_price = '$discounted_price' 
                                    WHERE product_id = '$product_id'");
                echo json_encode(['success' => true, 'bulk' => false]);
            } else {
                echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
            }
        }

        exit;
    }

    mysqli_close($conn);
}
?>
