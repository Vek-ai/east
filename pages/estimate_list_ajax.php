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

                    $('#viewEstimateModal').on('shown.bs.modal', function () {
                        $('#est_dtls_tbl').DataTable().columns.adjust().responsive.recalc();
                    });
                });
            </script>

            <?php
        }
    } 

    if ($action == "fetch_add_modal") {
        ?>
            <style>
                #add_est_form {
                    width: 100% !important;
                }

                #add_est_form td, #add_est_form th {
                    white-space: normal !important;
                    word-wrap: break-word;
                }
            </style>
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            Add Estimate
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="add_est_form" method="POST" class="form-horizontal" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="card datatables">
                                <div class="card-body table-responsive">
                                    <table id="est_add_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
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
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-body">
                                            <tr>
                                                <td>
                                                    <select id="product" class="productAdd form-control" name="product[]">
                                                        <option value="" >Select Product...</option>
                                                        <?php
                                                        $query_product = "SELECT * FROM product WHERE hidden = '0'";
                                                        $result_product = mysqli_query($conn, $query_product);            
                                                        while ($row_product = mysqli_fetch_array($result_product)) {
                                                        ?>
                                                            <option value="<?= $row_product['product_id'] ?>" <?= $selected ?>><?= $row_product['product_item'] ?></option>
                                                        <?php   
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select id="color" class="colorAdd form-control" name="color[]">
                                                        <option value="" >Select Color...</option>
                                                        <?php
                                                        $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0'";
                                                        $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                                        while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                                            $selected = ($row['color'] == $row_paint_colors['color_id']) ? 'selected' : '';
                                                        ?>
                                                            <option value="<?= $row_paint_colors['color_id'] ?>" data-color="<?= getColorHexFromColorID($row_paint_colors['color_id']) ?>" <?= $selected ?>><?= $row_paint_colors['color_name'] ?></option>
                                                        <?php   
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select id="grade" class="gradeAdd form-control" name="grade[]">
                                                        <option value="" >Select Grade...</option>
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
                                                </td>
                                                <td>
                                                    <select id="profile" class="profileAdd form-control" name="profile[]">
                                                        <option value="" >Select Profile...</option>
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
                                                </td>
                                                <td><input type="number" name="quantity[]" class="form-control"></td>
                                                <td>
                                                    <div class="d-flex flex-column align-items-center">
                                                        <input class="form-control text-center mb-1" type="text" value="" placeholder="Width" size="5" style="color:#ffffff; ">
                                                        <span class="mx-1 text-center mb-1">X</span>
                                                        <input class="form-control text-center mb-1" type="text" value="" placeholder="Bend" size="5" style="color:#ffffff;">
                                                        <span class="mx-1 text-center mb-1">X</span>
                                                        <input class="form-control text-center mb-1" type="text" value="" placeholder="Hem" size="5" style="color:#ffffff;">
                                                        <span class="mx-1 text-center mb-1">X</span>
                                                        <input class="form-control text-center" type="text" value="" placeholder="Length" size="5" style="color:#ffffff;">
                                                    </div>
                                                </td>
                                                <td><input type="text" name="actual_price[]" class="form-control"></td>
                                                <td><input type="text" name="discounted_price[]" class="form-control"></td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <button type="button" class="btn add-row btn-sm p-1 fs-5 me-1">
                                                            <i class="text-success ti ti-plus fs-7"></i>
                                                        </button>
                                                        <button type="button" class="btn minus-row btn-sm p-1 fs-5">
                                                            <i class="text-danger ti ti-minus fs-7"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
            <script>
                function formatOption(state) {
                    if (!state.id) {
                        return state.text;
                    }
                    var color = $(state.element).data('color');
                    var $state = $(
                        '<span class="d-flex align-items-center">' +
                        '<span class="rounded-circle d-block p-1 me-2" style="background-color:' + color + '; width: 16px; height: 16px;"></span>' +
                        state.text + '</span>'
                    );
                    return $state;
                }

                $('#est_add_tbl').DataTable({
                    language: {
                        emptyTable: "Estimate List not found"
                    },
                    autoWidth: false,
                    responsive: true,
                    lengthChange: false,
                    paging: false,
                    searching: false,
                    ordering: false,
                    info: false
                });

                $(document).on('click', '.add-row', function() {
                    var row = $('#table-body tr:last').clone();
                    row.find('input').val('');
                    row.appendTo('#table-body');
                });

                $('.colorAdd').select2({
                    placeholder: "Select Color",
                    templateResult: formatOption,
                    templateSelection: formatOption,
                    width: '300px'
                });

                $('.productAdd, .gradeAdd, .profileAdd').select2({
                    placeholder: "Select One",
                    width: '300px'
                });

                $(document).on('click', '.minus-row', function() {
                    var row = $(this).closest('tr');
                    if (confirm("Are you sure you want to remove this row?")) {
                        if ($('#table-body tr').length > 1) {
                            row.remove();
                        } else {
                            row.find('input').val('');
                        }
                    }
                });
            </script>
    <?php
    } 

    if ($action == "fetch_edit_modal") {
        ?>
            <style>
                #add_est_form {
                    width: 100% !important;
                }

                #add_est_form td, #add_est_form th {
                    white-space: normal !important;
                    word-wrap: break-word;
                }
            </style>
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="myLargeModalLabel">
                            Add Estimate
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="add_est_form" method="POST" class="form-horizontal" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="card datatables">
                                <div class="card-body table-responsive">
                                    <table id="est_edit_tbl" class="table table-hover mb-0 text-md-nowrap w-100">
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
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-body">
                                            <tr>
                                                <td>
                                                    <select id="product" class="productSelect form-control" name="product[]">
                                                        <option value="" >Select Product...</option>
                                                        <?php
                                                        $query_product = "SELECT * FROM product WHERE hidden = '0'";
                                                        $result_product = mysqli_query($conn, $query_product);            
                                                        while ($row_product = mysqli_fetch_array($result_product)) {
                                                        ?>
                                                            <option value="<?= $row_product['product_id'] ?>" <?= $selected ?>><?= $row_product['product_item'] ?></option>
                                                        <?php   
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select id="color" class="colorSelect form-control" name="color[]">
                                                        <option value="" >Select Color...</option>
                                                        <?php
                                                        $query_paint_colors = "SELECT * FROM paint_colors WHERE hidden = '0'";
                                                        $result_paint_colors = mysqli_query($conn, $query_paint_colors);            
                                                        while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                                            $selected = ($row['color'] == $row_paint_colors['color_id']) ? 'selected' : '';
                                                        ?>
                                                            <option value="<?= $row_paint_colors['color_id'] ?>" data-color="<?= getColorHexFromColorID($row_paint_colors['color_id']) ?>" <?= $selected ?>><?= $row_paint_colors['color_name'] ?></option>
                                                        <?php   
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select id="grade" class="gradeSelect form-control" name="grade[]">
                                                        <option value="" >Select Grade...</option>
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
                                                </td>
                                                <td>
                                                    <select id="profile" class="profileSelect form-control" name="profile[]">
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
                                                </td>
                                                <td><input type="number" name="quantity[]" class="form-control"></td>
                                                <td>
                                                    <div class="d-flex flex-column align-items-center">
                                                        <input class="form-control text-center mb-1" type="text" value="" placeholder="Width" size="5" style="color:#ffffff; ">
                                                        <span class="mx-1 text-center mb-1">X</span>
                                                        <input class="form-control text-center mb-1" type="text" value="" placeholder="Bend" size="5" style="color:#ffffff;">
                                                        <span class="mx-1 text-center mb-1">X</span>
                                                        <input class="form-control text-center mb-1" type="text" value="" placeholder="Hem" size="5" style="color:#ffffff;">
                                                        <span class="mx-1 text-center mb-1">X</span>
                                                        <input class="form-control text-center" type="text" value="" placeholder="Length" size="5" style="color:#ffffff;">
                                                    </div>
                                                </td>
                                                <td><input type="text" name="actual_price[]" class="form-control"></td>
                                                <td><input type="text" name="discounted_price[]" class="form-control"></td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <button type="button" class="btn add-row btn-sm p-1 fs-5 me-1">
                                                            <i class="text-success ti ti-plus fs-7"></i>
                                                        </button>
                                                        <button type="button" class="btn minus-row btn-sm p-1 fs-5">
                                                            <i class="text-danger ti ti-minus fs-7"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
            <script>
                function formatOption(state) {
                    if (!state.id) {
                        return state.text;
                    }
                    var color = $(state.element).data('color');
                    var $state = $(
                        '<span class="d-flex align-items-center">' +
                        '<span class="rounded-circle d-block p-1 me-2" style="background-color:' + color + '; width: 16px; height: 16px;"></span>' +
                        state.text + '</span>'
                    );
                    return $state;
                }

                $('#est_edit_tbl').DataTable({
                    language: {
                        emptyTable: "Estimate List not found"
                    },
                    autoWidth: false,
                    responsive: true,
                    lengthChange: false,
                    paging: false,
                    searching: false,
                    ordering: false,
                    info: false
                });

                $(document).on('click', '.add-row', function() {
                    var row = $('#table-body tr:last').clone();
                    row.find('input').val('');
                    row.appendTo('#table-body');
                });

                $('.colorSelect').select2({
                    placeholder: "Select Color",
                    templateResult: formatOption,
                    templateSelection: formatOption,
                    width: '300px'
                });

                $('.productSelect, .gradeSelect, .profileSelect').select2({
                    placeholder: "Select One",
                    width: '300px'
                });

                $(document).on('click', '.minus-row', function() {
                    var row = $(this).closest('tr');
                    if (confirm("Are you sure you want to remove this row?")) {
                        if ($('#table-body tr').length > 1) {
                            row.remove();
                        } else {
                            row.find('input').val('');
                        }
                    }
                });
            </script>
    <?php
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
