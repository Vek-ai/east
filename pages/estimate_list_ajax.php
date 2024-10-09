<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require '../includes/dbconn.php';
require '../includes/functions.php';

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    if ($action == "fetch_view_modal") {
        $estimateid = mysqli_real_escape_string($conn, $_POST['id']);
        $query = "SELECT * FROM estimate_prod WHERE estimateid = '$estimateid'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $totalquantity = $total_actual_price = $total_disc_price = 0;
            $response = array();
            ?>
            <style>
                #est_dtls_tbl {
                    width: 100% !important;
                }

                #est_dtls_tbl td, #est_dtls_tbl th {
                    white-space: normal !important;
                    word-wrap: break-word;
                }
            </style>
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            View Estimate
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div id="update_product" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body datatables">
                                    <div class="estimate-details table-responsive text-nowrap">
                                        <table id="est_dtls_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Description</th>
                                                    <th>Color</th>
                                                    <th>Grade</th>
                                                    <th>Profile</th>
                                                    <th class="text-center">Quantity</th>
                                                    <th class="text-center">Dimensions</th>
                                                    <th class="text-center">Price</th>
                                                    <th class="text-center">Customer Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        $estimateid = $row['estimateid'];
                                                        $product_details = getProductDetails($row['product_id']);
                                                    ?> 
                                                        <tr> 
                                                            <td>
                                                                <?php echo getProductName($row['product_id']) ?>
                                                            </td>
                                                            <td>
                                                            <div class="d-flex mb-0 gap-8">
                                                                <a class="rounded-circle d-block p-6" href="javascript:void(0)" style="background-color:<?= getColorHexFromProdID($product_details['color'])?>"></a>
                                                                <?= getColorFromID($product_details['color']); ?>
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
                                                                <?php 
                                                                $width = $row['custom_width'];
                                                                $height = $row['custom_height'];
                                                                
                                                                if (!empty($width) && !empty($height)) {
                                                                    echo htmlspecialchars($width) . " X " . htmlspecialchars($height);
                                                                } elseif (!empty($width)) {
                                                                    echo "Width: " . htmlspecialchars($width);
                                                                } elseif (!empty($height)) {
                                                                    echo "Height: " . htmlspecialchars($height);
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
                                                    }
                                                
                                                ?>
                                            </tbody>

                                            <tfoot>
                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td colspan="2" class="text-end">
                                                        <p class="m-1">Total Quantity: <?= $totalquantity ?></p>
                                                        <p class="m-1">Actual Price: <?= $total_actual_price ?></p>
                                                        <p class="m-1">Discounted Price: <?= $total_disc_price ?></p>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                $(document).ready(function() {
                    $('#est_dtls_tbl').DataTable({
                        language: {
                            emptyTable: "Estimate Details not found"
                        },
                        autoWidth: false,
                        responsive: true,
                        lengthChange: false
                    });

                    $('#view_est_details_modal').on('shown.bs.modal', function () {
                        $('#est_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
                    });
                });
            </script>

            <?php
        }
    } 

    if ($action == "fetch_edit_modal") {
        $estimateid = mysqli_real_escape_string($conn, $_POST['id']);

        // SQL query to check if the record exists
        $checkQuery = "SELECT * FROM estimate WHERE estimateid = '$estimateid'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Record exists, fetch current values
            $row = mysqli_fetch_assoc($result);
            
            ?>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            Add Estimate
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="update_product" method="POST" class="form-horizontal" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">
                                    <input type="hidden" id="estimateid" name="estimateid" class="form-control"  value="<?= $row['estimateid']?>"/>

                                    <div class="row">
                                        <div class="card-body p-0">
                                            <h4 class="card-title text-center">Estimate Image</h4>
                                                <p action="#" id="myUpdateDropzone" class="dropzone">
                                                    <div class="fallback">
                                                    <input type="file" id="picture_path_update" name="picture_path[]" class="form-control" style="display: none" multiple/>
                                                    </div>
                                                </p>
                                        </div>
                                    </div>
                                    
                                    <div class="card card-body m-2">
                                        <h5>Current Images</h5>
                                        <div class="row pt-3">
                                            <?php
                                            $query_img = "SELECT * FROM product_images WHERE productid = '$estimateid'";
                                            $result_img = mysqli_query($conn, $query_img);            
                                            while ($row_img = mysqli_fetch_array($result_img)) {
                                                $image_id = $row_img['prodimgid'];
                                            ?>
                                            <div class="col-md-2 position-relative">
                                                <div class="mb-3">
                                                    <img src="<?= $row_img['image_url'] ?>" class="img-fluid" alt="Estimate Image" />
                                                    <button class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn" data-image-id="<?= $image_id ?>">X</button>
                                                </div>
                                            </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Estimate Name</label>
                                                <input type="text" id="product_item" name="product_item" class="form-control" value="<?= $row['product_item']?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Estimate SKU</label>
                                                <input type="text" id="product_sku" name="product_sku" class="form-control" value="<?= $row['product_sku']?>" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Estimate Category</label>
                                                <select id="product_category_update" class="form-control" name="product_category">
                                                    <option value="/">Select One...</option>
                                                    <?php
                                                    $query_roles = "SELECT * FROM product_category WHERE hidden = '0'";
                                                    $result_roles = mysqli_query($conn, $query_roles);            
                                                    while ($row_product_category = mysqli_fetch_array($result_roles)) {
                                                        $selected = ($row['product_category'] == $row_product_category['estimateid']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_product_category['product_category_id'] ?>" <?= $selected ?>><?= $row_product_category['product_category'] ?></option>
                                                    <?php   
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Estimate Line</label>
                                                <select id="product_line" class="form-control" name="product_line">
                                                    <option value="/">Select One...</option>
                                                    <?php
                                                    $query_roles = "SELECT * FROM product_line WHERE hidden = '0'";
                                                    $result_roles = mysqli_query($conn, $query_roles);            
                                                    while ($row_product_line = mysqli_fetch_array($result_roles)) {
                                                        $selected = ($row['product_line'] == $row_product_line['product_line_id']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_product_line['product_line_id'] ?>" <?= $selected ?>><?= $row_product_line['product_line'] ?></option>
                                                    <?php   
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Estimate Type</label>
                                                <select id="product_type" class="form-control" name="product_type">
                                                    <option value="/">Select One...</option>
                                                    <?php
                                                    $query_roles = "SELECT * FROM product_type WHERE hidden = '0'";
                                                    $result_roles = mysqli_query($conn, $query_roles);            
                                                    while ($row_product_type = mysqli_fetch_array($result_roles)) {
                                                        $selected = ($row['product_type'] == $row_product_type['product_type_id']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_product_type['product_type_id'] ?>" <?= $selected ?>><?= $row_product_type['product_type'] ?></option>
                                                    <?php   
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"><?= $row['product_item']?></textarea>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-12">
                                        <label class="form-label">Correlated products</label>
                                        <select id="correlatedProducts" name="correlatedProducts[]" class="select2-update form-control" multiple="multiple">
                                            <optgroup label="Select Correlated Products">
                                                <?php
                                                $correlated_product_ids = [];
                                                $estimateid = mysqli_real_escape_string($conn, $row['estimateid']);
                                                $query_correlated = "SELECT correlated_id FROM correlated_product WHERE main_correlated_product_id = '$estimateid'";
                                                $result_correlated = mysqli_query($conn, $query_correlated);
                                                
                                                while ($row_correlated = mysqli_fetch_assoc($result_correlated)) {
                                                    $correlated_product_ids[] = $row_correlated['correlated_id'];
                                                }
                                                
                                                $query_products = "SELECT * FROM estimate";
                                                $result_products = mysqli_query($conn, $query_products);            
                                                while ($row_products = mysqli_fetch_array($result_products)) {
                                                    $selected = in_array($row_products['estimateid'], $correlated_product_ids) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $row_products['estimateid'] ?>" <?= $selected ?> ><?= $row_products['product_item'] ?></option>
                                                <?php   
                                                }
                                                ?>
                                            </optgroup>
                                        </select>
                                        </div>
                                    </div>  

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="1">
                                        <div class="mb-3">
                                        <label class="form-label">Stock Type</label>
                                        <select id="stock_type" class="form-control" name="stock_type">
                                            <option value="/" >Select Stock Type...</option>
                                            <?php
                                            $query_stock_type = "SELECT * FROM stock_type WHERE hidden = '0'";
                                            $result_stock_type = mysqli_query($conn, $query_stock_type);            
                                            while ($row_stock_type = mysqli_fetch_array($result_stock_type)) {
                                                $selected = ($row['stock_type'] == $row_stock_type['stock_type_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_stock_type['stock_type_id'] ?>" <?= $selected ?>><?= $row_stock_type['stock_type'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="2">
                                        <div class="mb-3">
                                        <label class="form-label">Material</label>
                                        <input type="text" id="material" name="material" class="form-control" value="<?= $row['material']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="3">
                                        <div class="mb-3">
                                        <label class="form-label">Dimensions</label>
                                        <input type="text" id="dimensions" name="dimensions" class="form-control" value="<?= $row['dimensions']?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="4">
                                        <div class="mb-3">
                                        <label class="form-label">Thickness</label>
                                        <input type="text" id="thickness" name="thickness" class="form-control" value="<?= $row['thickness']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="5">
                                        <div class="mb-3">
                                        <label class="form-label">Gauge</label>
                                        <select id="gauge" class="form-control" name="gauge">
                                            <option value="/" >Select Gauge...</option>
                                            <?php
                                            $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0'";
                                            $result_gauge = mysqli_query($conn, $query_gauge);            
                                            while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                                $selected = ($row['gauge'] == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_gauge['product_gauge_id'] ?>" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="6">
                                        <div class="mb-3">
                                        <label class="form-label">Grade</label>
                                        <select id="grade" class="form-control" name="grade">
                                            <option value="/" >Select Grade...</option>
                                            <?php
                                            $query_grade = "SELECT * FROM product_grade WHERE hidden = '0'";
                                            $result_grade = mysqli_query($conn, $query_grade);            
                                            while ($row_grade = mysqli_fetch_array($result_grade)) {
                                                $selected = ($row['grade'] == $row_grade['product_grade_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_grade['product_grade_id'] ?>" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-4 opt_field_update" data-id="7">
                                        <div class="mb-3">
                                        <label class="form-label">Color</label>
                                        <select id="color" class="form-control" name="color">
                                            <option value="/" >Select Color...</option>
                                            <?php
                                            $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0'";
                                            $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                            while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                                $selected = ($row['color'] == $row_paint_colors['color_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_paint_colors['color_id'] ?>" <?= $selected ?>><?= $row_paint_colors['color_name'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 opt_field_update" data-id="8">
                                        <div class="mb-3">
                                        <label class="form-label">Paint Provider</label>
                                        <select id="paintProvider" class="form-control" name="paintProvider">
                                            <option value="/" >Select Color...</option>
                                            <?php
                                            $query_paint_providers = "SELECT * FROM paint_providers WHERE hidden = '0'";
                                            $result_paint_providers = mysqli_query($conn, $query_paint_providers);            
                                            while ($row_paint_providers = mysqli_fetch_array($result_paint_providers)) {
                                                $selected = ($row['paint_provider'] == $row_paint_providers['provider_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_paint_providers['provider_id'] ?>" <?= $selected ?>><?= $row_paint_providers['provider_name'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 opt_field_update" data-id="17">
                                        <div class="mb-3">
                                        <label class="form-label">Coating</label>
                                        <input type="text" id="coating" name="coating" class="form-control" value="<?= $row['coating']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="9">
                                        <div class="mb-3">
                                        <label class="form-label">Warranty Type</label>
                                        <select id="warrantyType" class="form-control" name="warrantyType">
                                            <option value="/" >Select Warranty Type...</option>
                                            <?php
                                            $query_product_warranty_type = "SELECT * FROM product_warranty_type WHERE hidden = '0'";
                                            $result_product_warranty_type = mysqli_query($conn, $query_product_warranty_type);            
                                            while ($row_product_warranty_type = mysqli_fetch_array($result_product_warranty_type)) {
                                                $selected = ($row['warranty_type'] == $row_product_warranty_type['product_warranty_type_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_product_warranty_type['product_warranty_type_id'] ?>" <?= $selected ?>><?= $row_product_warranty_type['product_warranty_type'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="10">
                                        <div class="mb-3">
                                        <label class="form-label">Profile</label>
                                        <select id="profile" class="form-control" name="profile">
                                            <option value="/" >Select Profile...</option>
                                            <?php
                                            $query_profile_type = "SELECT * FROM profile_type WHERE hidden = '0'";
                                            $result_profile_type = mysqli_query($conn, $query_profile_type);            
                                            while ($row_profile_type = mysqli_fetch_array($result_profile_type)) {
                                                $selected = ($row['profile'] == $row_profile_type['profile_type_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $row_profile_type['profile_type_id'] ?>" <?= $selected ?>><?= $row_profile_type['profile_type'] ?></option>
                                            <?php   
                                            }
                                            ?>
                                        </select>
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="11">
                                        <div class="mb-3">
                                        <label class="form-label">Width</label>
                                        <input type="text" id="width" name="width" class="form-control" value="<?= $row['width']?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="12">
                                        <div class="mb-3">
                                        <label class="form-label">Length</label>
                                        <input type="text" id="length" name="length" class="form-control" value="<?= $row['length']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="13">
                                        <div class="mb-3">
                                        <label class="form-label">Weight</label>
                                        <input type="text" id="weight" name="weight" class="form-control" value="<?= $row['weight']?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6 opt_field_update" data-id="14">
                                        <div class="mb-3">
                                        <label class="form-label">Unit of Measure</label>
                                        <input type="text" id="unitofMeasure" name="unitofMeasure" class="form-control" value="<?= $row['unit_of_measure']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                        <label class="form-label">UnitPrice</label>
                                        <input type="text" id="unitPrice" name="unitPrice" class="form-control" value="<?= $row['unit_price']?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                        <label class="form-label">Unit Cost</label>
                                        <input type="text" id="unitCost" name="unitCost" class="form-control" value="<?= $row['unit_cost']?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                        <label class="form-label">Unit Gross Margin</label>
                                        <input type="text" id="unitGrossMargin" name="unitGrossMargin" class="form-control" value="<?= $row['unit_gross_margin']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="row pt-3">
                                    <div class="col-md-6 opt_field_update" data-id="15">
                                        <div class="mb-3">
                                        <label class="form-label">Usage</label>
                                        <input type="text" id="product_usage" name="product_usage" class="form-control" value="<?= $row['product_usage']?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                        <label class="form-label">UPC</label>
                                        <input type="text" id="upc" name="upc" class="form-control" value="<?= $row['upc']?>" />
                                        </div>
                                    </div>
                                    </div>

                                    <div class="mb-3 opt_field_update" data-id="16">
                                    <label class="form-label">Comment</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="5"><?= $row['comment']?></textarea>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="form-actions">
                                <div class="card-body">
                                    <button type="submit" class="btn bg-success-subtle waves-effect text-start">Save</button>
                                    <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php
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
        $estimateid = mysqli_real_escape_string($conn, $_POST['estimateid']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $new_status = ($status == '0') ? '1' : '0';

        $statusQuery = "UPDATE estimate SET status = '$new_status' WHERE estimateid = '$estimateid'";
        if (mysqli_query($conn, $statusQuery)) {
            echo "success";
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    }
    if ($action == 'hide_category') {
        $estimateid = mysqli_real_escape_string($conn, $_POST['estimateid']);
        $query = "UPDATE estimate SET hidden='1' WHERE estimateid='$estimateid'";
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
    
    mysqli_close($conn);
}
?>
