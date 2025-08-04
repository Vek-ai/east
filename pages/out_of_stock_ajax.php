<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_view_modal") {
        $product_id = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM product WHERE product_id = '$product_id'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
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
    
    if ($action == 'fetch_products') {
        $data = [];
        $query = "
            SELECT 
                p.*, 
                COALESCE(SUM(i.quantity_ttl), 0) AS total_quantity 
            FROM product AS p 
            LEFT JOIN inventory AS i ON p.product_id = i.product_id 
            WHERE p.hidden = 0 
            GROUP BY p.product_id
            HAVING total_quantity < 1
        ";
        $result = mysqli_query($conn, $query);
        $no = 1;
    
        while ($row = mysqli_fetch_assoc($result)) {
            $product_id = $row['product_id'];
            $status = $row['status'];
            $category_id = $row['product_category'];
    
            $picture_path = !empty($row['main_image']) ? $row['main_image'] : "images/product/product.jpg";
    
            $product_name_html = "
                <a href='?page=product_details&product_id={$product_id}'>
                    <div class='d-flex align-items-center'>
                        <img src='{$picture_path}' class='rounded-circle' width='56' height='56'>
                        <div class='ms-3'>
                            <h6 class='fw-semibold mb-0 fs-4'>{$row['product_item']}</h6>
                        </div>
                    </div>
                </a>";
    
            $action_html = "
                <div class='action-btn text-center'>
                    <a href='javascript:void(0)' id='view_product_btn' title='View' class='text-primary edit' data-id='{$product_id}' data-category='{$category_id}'><i class='ti ti-eye fs-7'></i></a>
                </div>";
    
            $data[] = [
                'product_name_html'   => $product_name_html,
                'product_category'    => getProductCategoryName($row['product_category']),
                'product_system'      => getProductSystemName($row['product_system']),
                'product_line'        => getProductLineName($row['product_line']),
                'product_type'        => getProductTypeName($row['product_type']),
                'profile'             => getProfileTypeName($row['profile']),
                'color'               => getColorName($row['color']),
                'grade'               => getGradeName($row['grade']),
                'gauge'               => getGaugeName($row['gauge']),
                'active'              => $status,
                'action_html'         => $action_html
            ];
    
            $no++;
        }
    
        echo json_encode(['data' => $data]);
    }
    
    mysqli_close($conn);
}
?>
