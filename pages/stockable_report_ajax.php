<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

$emailSender = new EmailTemplates();

if (isset($_POST['search_stockable'])) {
    $response = [
        'products' => [],
        'total_count' => 0,
        'total_amount' => 0,
        'error' => null
    ];

    $date_from = mysqli_real_escape_string($conn, $_POST['date_from'] ?? '');
    $date_to   = mysqli_real_escape_string($conn, $_POST['date_to'] ?? '');
    $months    = array_map('intval', $_POST['months'] ?? []);
    $years     = array_map('intval', $_POST['years'] ?? []);

    $query = "
        SELECT *
        FROM stockable_report s
        WHERE 1=1
    ";

    if (!empty($date_from) && !empty($date_to)) {
        $date_to .= ' 23:59:59';
        $query .= " AND s.date BETWEEN '$date_from' AND '$date_to' ";
    } elseif (!empty($date_from)) {
        $query .= " AND s.date >= '$date_from' ";
    } elseif (!empty($date_to)) {
        $date_to .= ' 23:59:59';
        $query .= " AND s.date <= '$date_to' ";
    } else {
        $today_start = date('Y-m-d 00:00:00');
        $today_end   = date('Y-m-d 23:59:59');
        $query .= " AND s.date BETWEEN '$today_start' AND '$today_end' ";
    }

    if (!empty($months)) {
        $months_in = implode(',', $months);
        $query .= " AND MONTH(s.date) IN ($months_in) ";
    }

    if (!empty($years)) {
        $years_in = implode(',', $years);
        $query .= " AND YEAR(s.date) IN ($years_in) ";
    }

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['id'];
            $product_id = $row['product_id'];
            $product = getProductDetails($product_id);

            $date = date("F d, Y", strtotime($row['date']));
            $remaining_inventory = floatval($row['remaining_inventory']);
            $quantity_ordered    = floatval($row['quantity_ordered']);

            $response['stockables'][] = [
                'id' => $id,
                'product_id' => $product_id,
                'product_name' => $product['product_item'] ?? 'Unknown',
                'quantity' => round($quantity_ordered, 2),
                'date' => $date,
                'remaining' => round($remaining_inventory, 2),
                'order_id' => $row['order_id'],
                'order_product_id' => $row['order_product_id'],
            ];

            $response['total_count'] += $quantity_ordered;
            $response['total_amount'] += ($product['unit_price'] ?? 0) * $quantity_ordered;
        }
    } else {
        $response['error'] = 'No stockable usage found';
    }

    echo json_encode($response);
}

if(isset($_POST['fetch_stockable_details'])){
    $orderid = mysqli_real_escape_string($conn, $_POST['id']);
    $query = "SELECT * FROM order_product WHERE orderid = '$orderid'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $order_details = getOrderDetails($orderid);
        $totalquantity = $total_actual_price = $total_disc_price = 0;
        $status_code = $order_details['status'];

        $tracking_number = $order_details['tracking_number'];
        $shipping_comp_details = getShippingCompanyDetails($order_details['shipping_company']);
        $shipping_company = $shipping_comp_details['shipping_company'];

        $deliver_method = $order_details['deliver_method'];

        $response = array();
        ?>
        <div class="card">
            <div class="card-body datatables">
                <h4 class="modal-title" id="myLargeModalLabel">
                    View Order Details
                </h4>
                <div class="order-details table-responsive">
                    <table id="order_dtls_tbl" class="table table-hover mb-0 w-100">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select_all"></th>
                                <th>Description</th>
                                <th>Color</th>
                                <th>Grade</th>
                                <th>Profile</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Payment Status</th>
                                <th class="text-center">Details</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Customer Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $is_pickup = false;
                                $is_paid = 1;
                                $is_ready = false;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $orderid = $row['orderid'];
                                    $product_details = getProductDetails($row['productid']);

                                    $is_stockable = $product_details['product_origin'] == 1;

                                    $status_prod_db = (int)$row['status'];
                                    $payment_db = (int)$row['paid_status'];

                                    $price = $row['discounted_price'];

                                    $product_name = '';
                                    if(!empty($row['product_item'])){
                                        $product_name = $row['product_item'];
                                    }else{
                                        $product_name = getProductName($row['product_id']);
                                    }

                                    if($status_prod_db == '2'){
                                        $is_ready = true;
                                    }

                                    if($payment_db == '0'){
                                        $is_paid = 0;
                                    }

                                    $payment_labels = [
                                        0 => ['label' => 'Unpaid', 'class' => 'badge bg-danger'],
                                        1 => ['label' => 'Paid', 'class' => 'badge bg-success']
                                    ];
                                    $payment_prod = $payment_labels[$payment_db];

                                    $status_prod_labels = [
                                        0 => ['label' => 'New', 'class' => 'badge bg-primary'],
                                        1 => ['label' => 'Processing', 'class' => 'badge bg-success'],
                                        2 => ['label' => 'Waiting for Dispatch', 'class' => 'badge bg-warning'],
                                        3 => ['label' => 'In Transit', 'class' => 'badge bg-secondary'],
                                        4 => ['label' => 'Delivered', 'class' => 'badge bg-success'],
                                        5 => ['label' => 'On Hold', 'class' => 'badge bg-danger'],
                                        6 => ['label' => 'Returned', 'class' => 'badge bg-danger']
                                    ];

                                    if($deliver_method == 'pickup'){
                                        $is_pickup = true;

                                        $status_prod_labels[2]['label'] = 'Ready for Pick-up';
                                        $status_prod_labels[4]['label'] = 'Picked Up';
                                    }

                                    $status_prod = $status_prod_labels[$status_prod_db];
                                ?> 
                                    <tr> 
                                        <td class="text-center">
                                            <?= $is_pickup && $is_ready ? "<input type='checkbox' class='row-checkbox' value='{$row['id']}' data-amount='$price' data-paid='$payment_db'>" : "" ?>
                                        </td>
                                        <td>
                                            <?= $product_name ?>
                                        </td>
                                        <td>
                                        <div class="d-flex mb-0 gap-8">
                                            <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromColorID($row['custom_color'])?>"></a>
                                            <?= getColorFromID($row['custom_color']); ?>
                                        </div>
                                        </td>
                                        <td>
                                            <?php echo getGradeName($product_details['grade']); ?>
                                        </td>
                                        <td>
                                            <?php echo getProfileTypeName($product_details['profile']); ?>
                                        </td>
                                        <td><?= $row['quantity'] ?></td>
                                        <td>
                                            <span class="<?= $status_prod['class']; ?> fw-bond"><?= $status_prod['label']; ?></span>
                                        </td>
                                        <td>
                                            <span class="<?= $payment_prod['class']; ?> fw-bond"><?= $payment_prod['label']; ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            if($product_details['product_category'] == $screw_id){
                                                $pack_count = $row['custom_length'];
                                                echo htmlspecialchars($pack_count) . ' pcs';
                                            }else{
                                                $width = $row['custom_width'];
                                                $height = $row['custom_height'];
                                                
                                                if (!empty($width) && !empty($height)) {
                                                    echo htmlspecialchars($width) . " X " . htmlspecialchars($height);
                                                } elseif (!empty($width)) {
                                                    echo "Width: " . htmlspecialchars($width);
                                                } elseif (!empty($height)) {
                                                    echo "Height: " . htmlspecialchars($height);
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td class="text-end">$ <?= number_format($row['actual_price'],2) ?></td>
                                        <td class="text-end">$ <?= number_format($row['discounted_price'],2) ?></td>
                                    </tr>
                            <?php
                                    $totalquantity += $row['quantity'] ;
                                    $total_actual_price += $row['actual_price'];
                                    $total_disc_price += $row['discounted_price'];
                                    $total_amount += floatval($row['discounted_price']);
                                }
                            
                            ?>
                        </tbody>

                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-end">Total</td>
                                <td><?= $totalquantity ?></td>
                                <td></td>
                                <td></td>
                                <td class="text-end">$ <?= number_format($total_amount,2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {

                if ($.fn.DataTable.isDataTable('#order_dtls_tbl')) {
                    $('#order_dtls_tbl').DataTable().clear().destroy();
                }

                $('#order_dtls_tbl').DataTable({
                    language: {
                        emptyTable: "Order Details not found"
                    },
                    autoWidth: false,
                    responsive: true,
                    lengthChange: false
                });
            });
        </script>
        

        <?php
    }
}

if (isset($_POST['fetch_product_details'])) {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);

    $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        ?>
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

                        <div class="mb-3">
                            <label class="form-label">Description:</label>
                            <p><?= $row['description'] ?></p>
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

