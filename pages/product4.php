<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';

$generate_rend_upc = generateRandomUPC();
$picture_path = "images/product/product.jpg";

$color_id = isset($_REQUEST['color_id']) ? $_REQUEST['color_id'] : '';
$grade_id = isset($_REQUEST['grade_id']) ? $_REQUEST['grade_id'] : '';
$gauge_id = isset($_REQUEST['gauge_id']) ? $_REQUEST['gauge_id'] : '';
$category_id = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : '';
$profile_id = isset($_REQUEST['profile_id']) ? $_REQUEST['profile_id'] : '';
$type_id = isset($_REQUEST['type_id']) ? $_REQUEST['type_id'] : '';
$onlyInStock = isset($_REQUEST['onlyInStock']) ? filter_var($_REQUEST['onlyInStock'], FILTER_VALIDATE_BOOLEAN) : false;

$price_per_hem = getPaymentSetting('price_per_hem');
$price_per_bend = getPaymentSetting('price_per_bend');

$permission = $_SESSION['permission'];

$staff_id = intval($_SESSION['userid']);
$profileSql = "SELECT access_profile_id FROM staff WHERE staff_id = $staff_id";
$profileRes = mysqli_query($conn, $profileSql);
$profile_id = 0;
if ($profileRes && mysqli_num_rows($profileRes) > 0) {
    $profile_id = intval(mysqli_fetch_assoc($profileRes)['access_profile_id']);
}
$page_id = getPageIdFromUrl($_GET['page'] ?? '');

$visibleColumns = getVisibleColumns($page_id, $profile_id);
function showCol($name) {
    global $visibleColumns;
    return !empty($visibleColumns[$name]);
}

?>
<style>
    /* .select2-container {
        z-index: 9999 !important; 
    } */
    .dz-preview {
        position: relative;
    }

    .dz-remove {
        position: absolute;
        top: 10px;
        right: 10px;
        background: red;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        font-weight: bold;
        font-size: 12px;
        z-index: 9999; /* Ensure the remove button is on top of the image */
        cursor: pointer; /* Make sure it looks clickable */
    }

    #productList_filter {
        display: none !important;
    }

    #productTable th:nth-child(1), 
    #productTable td:nth-child(1) {
        width: 25% !important; /* Products */
    }

    .readonly {
        pointer-events: none;
        background-color: #f8f9fa;
        color: #6c757d;
        border: 0;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .readonly select,
    .readonly option {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
    }

    .readonly input {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
    }

    #standing_seam[type="checkbox"],
    #board_batten[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        width: 18px;
        height: 18px;
        border: 2px solid #666;
        border-radius: 50%;
        outline: none;
        cursor: pointer;
        position: relative;
    }

    #standing_seam[type="checkbox"]:checked,
    #board_batten[type="checkbox"]:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    #standing_seam[type="checkbox"]:checked::after,
    #board_batten[type="checkbox"]:checked::after {
        content: "";
        width: 10px;
        height: 10px;
        background: white;
        border-radius: 50%;
        position: absolute;
        top: 3px;
        left: 3px;
    }
</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"> Product</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Contact</li>
            </ol>
            </nav>
        </div>
        <div>
            <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
            <div class="d-flex gap-2">
                <div class="">
                <small>This Month</small>
                <h4 class="text-primary mb-0 ">$58,256</h4>
                </div>
                <div class="">
                <div class="breadbar"></div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="">
                <small>Last Month</small>
                <h4 class="text-secondary mb-0 ">$58,256</h4>
                </div>
                <div class="">
                <div class="breadbar2"></div>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
    </div>

    <div class="widget-content searchable-container list">
    <?php                                                    
    if ($permission === 'edit') {
    ?>

    <div class="card card-body">
        <div class="row">
        <div class="col-md-4 col-xl-3">
            <!-- <form class="position-relative">
            <input type="text" class="form-control product-search ps-5" id="input-search" placeholder="Search Contacts..." />
            <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </form> -->
        </div>
        <div class="col-md-8 col-xl-9 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
            <button type="button" id="downloadClassModalBtn" class="btn btn-primary d-flex align-items-center">
                <i class="ti ti-download text-white me-1 fs-5"></i> Download Classifications
            </button>
            <button type="button" id="addProductModalBtn" class="btn btn-primary d-flex align-items-center" data-id="">
                <i class="ti ti-plus text-white me-1 fs-5"></i> Add Product
            </button>
            <button type="button" id="downloadProductModalBtn" class="btn btn-primary d-flex align-items-center">
                <i class="ti ti-download text-white me-1 fs-5"></i> Download Products
            </button>
            <button type="button" id="uploadProductModalBtn" class="btn btn-primary d-flex align-items-center">
                <i class="ti ti-upload text-white me-1 fs-5"></i> Upload Products
            </button>
        </div>
        </div>
    </div>

    <?php
    }
    ?>

    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Add Product
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="product_form" class="form-horizontal">
                    <input type="hidden" id="product_id" name="product_id" class="form-control" />
                    <div class="modal-body">
                        <div class="card">
                            <div class="card shadow-sm rounded-3 mb-3">
                                <div class="card-header bg-light border-bottom">
                                    <h5 class="mb-0 fw-bold">Product Identifier</h5>
                                </div>
                                <div class="card-body border rounded p-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Product Category</label>
                                            <div class="mb-3">
                                            <select id="product_category" class="form-control" name="product_category">
                                                <option value="" >Select One...</option>
                                                <?php
                                                $query_roles = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                                $result_roles = mysqli_query($conn, $query_roles);            
                                                while ($row_product_category = mysqli_fetch_array($result_roles)) {
                                                ?>
                                                    <option value="<?= $row_product_category['product_category_id'] ?>" 
                                                            data-category="<?= $row_product_category['product_category'] ?>"
                                                            data-filename="<?= $row_product_category['product_filename'] ?>"
                                                    >
                                                                <?= $row_product_category['product_category'] ?>
                                                    </option>
                                                <?php   
                                                }
                                                ?>
                                            </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 hidden-field d-none">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="form-label">Product Line</label>
                                                <a href="?page=product_line" target="_blank" class="text-decoration-none">Edit</a>
                                            </div>
                                            <div class="mb-3">
                                            <select id="product_line" class="form-control add-category calculate" name="product_line">
                                                <option value="" >Select Line...</option>
                                                <?php
                                                $query_roles = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                                                $result_roles = mysqli_query($conn, $query_roles);            
                                                while ($row_product_line = mysqli_fetch_array($result_roles)) {
                                                    $selected = (($row['product_line'] ?? '') == $row_product_line['product_line_id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $row_product_line['product_line_id'] ?>" data-category="<?= $row_product_line['product_category'] ?>" <?= $selected ?>><?= $row_product_line['product_line'] ?></option>
                                                <?php   
                                                }
                                                ?>
                                            </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4 hidden-field d-none">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="form-label">Product Type</label>
                                                <a href="?page=product_type" target="_blank" class="text-decoration-none">Edit</a>
                                            </div>
                                            <div class="mb-3">
                                            <select id="product_type" class="form-control add-category calculate" name="product_type">
                                                <option value="" >Select Type...</option>
                                                <?php
                                                $query_roles = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                                                $result_roles = mysqli_query($conn, $query_roles);            
                                                while ($row_product_type = mysqli_fetch_array($result_roles)) {
                                                    $selected = (($row['product_type'] ?? '') == $row_product_type['product_type_id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $row_product_type['product_type_id'] ?>" data-category="<?= $row_product_type['product_category'] ?>" <?= $selected ?>><?= $row_product_type['product_type'] ?></option>
                                                <?php   
                                                }
                                                ?>
                                            </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4 hidden-field d-none">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="form-label">Product System</label>
                                                <a href="?page=product_system" target="_blank" class="text-decoration-none">Edit</a>
                                            </div>
                                            <div class="mb-3">
                                            <select id="product_system" class="form-control add-category calculate" name="product_system">
                                                <option value="" >Select System...</option>
                                                <?php
                                                $query_system = "SELECT * FROM product_system WHERE hidden = '0' AND status = '1' ORDER BY `product_system` ASC";
                                                $result_system = mysqli_query($conn, $query_system);
                                                while ($row_system = mysqli_fetch_array($result_system)) {
                                                    $selected = (($row['product_system'] ?? '') == $row_system['product_system_id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $row_system['product_system_id'] ?>" data-category='<?= $row_system['product_category'] ?>' <?= $selected ?>><?= $row_system['product_system'] ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4 hidden-field d-none">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <label class="form-label">Product Grade</label>
                                                    <a href="?page=product_grade" target="_blank" class="text-decoration-none">Edit</a>
                                                </div>
                                                <select id="grade" class="form-control calculate add-category" name="grade">
                                                    <option value="" >Select Grade...</option>
                                                    <?php
                                                    $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                                    $result_grade = mysqli_query($conn, $query_grade);            
                                                    while ($row_grade = mysqli_fetch_array($result_grade)) {
                                                        $selected = (($row['grade'] ?? '') == $row_grade['product_grade']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $row_grade['product_grade'] ?>" data-category="<?= $row_grade['product_category'] ?>" data-multiplier="<?= $row_grade['multiplier'] ?>" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                                    <?php   
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4 hidden-field d-none">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <label class="form-label">Product Gauge</label>
                                                    <a href="?page=product_gauge" target="_blank" class="text-decoration-none">Edit</a>
                                                </div>
                                                <select id="gauge" class="form-control calculate" name="gauge">
                                                    <option value="" >Select Gauge...</option>
                                                    <?php
                                                    $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY `product_gauge` ASC";
                                                    $result_gauge = mysqli_query($conn, $query_gauge);

                                                    $unique_gauges = [];

                                                    while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                                        if (in_array($row_gauge['product_gauge_id'], $unique_gauges)) {
                                                            continue;
                                                        }

                                                        $unique_gauges[] = $row_gauge['product_gauge'];
                                                        
                                                        $selected = (($row['gauge'] ?? '') == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                                        ?>
                                                        <option value="<?= $row_gauge['product_gauge_id'] ?>" data-multiplier="<?= $row_gauge['multiplier'] ?>" <?= $selected ?>>
                                                            <?= $row_gauge['product_gauge'] ?>
                                                        </option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4 hidden-field d-none">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <label class="form-label">Product Profile</label>
                                                    <a href="?page=profile_type" target="_blank" class="text-decoration-none">Edit</a>
                                                </div>
                                                <select id="profile" class="form-control add-category" name="profile">
                                                    <option value="" >Select Profile...</option>
                                                    <?php
                                                    $query_profile_type = "SELECT * FROM profile_type WHERE hidden = '0' AND status = '1'";
                                                    $result_profile_type = mysqli_query($conn, $query_profile_type);            
                                                    while ($row_profile_type = mysqli_fetch_array($result_profile_type)) {
                                                        $selected = ($row['profile'] == $row_profile_type['profile_type_id']) ? 'selected' : '';
                                                                    ?>
                                                        <option value="<?= $row_profile_type['profile_type_id'] ?>" data-category="<?= $row_profile_type['product_category'] ?>"  <?= $selected ?>><?= $row_profile_type['profile_type'] ?></option>
                                                    <?php   
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="add-fields" class=""></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="downloadProductModal" tabindex="-1" aria-labelledby="downloadProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Download Product
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="download_product_form" class="form-horizontal">
                        <label for="select-category" class="form-label fw-semibold">Select Category</label>
                        <div class="mb-3">
                            <select class="form-select select2" id="select-download-category" name="category">
                                <option value="">All Categories</option>
                                <optgroup label="Category">
                                    <?php
                                    $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                    $result_category = mysqli_query($conn, $query_category);
                                    while ($row_category = mysqli_fetch_array($result_category)) {
                                    ?>
                                        <option value="<?= $row_category['product_category_id'] ?>"><?= $row_category['product_category'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="fas fa-download me-2"></i> Download Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="downloadClassModal" tabindex="-1" aria-labelledby="downloadClassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Download Classification
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="download_class_form" class="form-horizontal">
                        <label for="select-category" class="form-label fw-semibold">Select Classification</label>
                        <div class="mb-3">
                            <select class="form-select select2" id="select-download-class" name="category">
                                <option value="">All Classifications</option>
                                <optgroup label="Classifications">
                                    <option value="category">Category</option>
                                    <option value="system">Product System</option>
                                    <option value="line">Product Line</option> 
                                    <option value="type">Product Type</option> 
                                    <option value="grade">Product Grade</option> 
                                    <option value="color">Color</option> 
                                    <option value="profile">Profile</option> 
                                    <option value="flat_sheet_width">Flat Sheet Width</option> 
                                </optgroup>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="fas fa-download me-2"></i> Download Classification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadProductModal" tabindex="-1" aria-labelledby="uploadProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Upload Product
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                            <form id="upload_product_form" action="#" method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="excel_file" class="form-label fw-semibold">Select Excel File</label>
                                    <input type="file" class="form-control" name="excel_file" accept=".xls,.xlsx" required>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Upload & Read</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card mb-0 mt-2">
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <button type="button" id="readUploadProductBtn" class="btn btn-primary fw-semibold">
                                <i class="fas fa-eye me-2"></i> View Uploaded File
                            </button>
                        </div>
                    </div>    
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="readUploadProductModal" tabindex="-1" aria-labelledby="readUploadProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Uploaded Excel Product
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="uploaded_excel" class="modal-body">
                
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateProductModal" tabindex="-1" role="dialog" aria-labelledby="updateProductModal" aria-hidden="true"></div>

    <div class="modal fade" id="addInventoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header align-items-center modal-colored-header">
                    <h4 class="m-0">Add Product to Inventory</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="inventory-body">
                    
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="viewDetailsModal">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Product Details</h6>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                <div class="card">
                    <div class="card-body mb-0" id="viewDetailsModalBody">
                    
                    </div>
                </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-danger" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="response-modal" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div id="responseHeaderContainer" class="modal-header align-items-center modal-colored-header">
                <h4 id="responseHeader" class="m-0"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <p id="responseMsg"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
                Close
                </button>
            </div>
            </div>
        </div>
    </div>

    
    <div class="card card-body">
        <div class="row">
            <div class="col-3">
                <h3 class="card-title align-items-center mb-2">
                    Filter Products 
                </h3>
                <div class="position-relative w-100 px-0 mr-0 mb-2">
                    <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search Product">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center">
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control py-0 ps-5 select2 filter-selection" id="select-category" data-filter="category" data-filter-name="Product Category">
                            <option value="" data-category="">All Categories</option>
                            <optgroup label="Category">
                                <?php
                                $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                $result_category = mysqli_query($conn, $query_category);
                                while ($row_category = mysqli_fetch_array($result_category)) {
                                ?>
                                    <option value="<?= $row_category['product_category'] ?>" data-category="<?= $row_category['product_category'] ?>" <?= $selected ?>><?= $row_category['product_category'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-system" data-filter="system" data-filter-name="Product System">
                            <option value="" data-category="">All Product Systems</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_system = "SELECT * FROM product_system WHERE hidden = '0' AND status = '1' ORDER BY `product_system` ASC";
                                $result_system = mysqli_query($conn, $query_system);
                                while ($row_system = mysqli_fetch_array($result_system)) {
                                ?>
                                    <option value="<?= $row_system['product_system'] ?>" data-category="<?= trim(preg_replace('/\s+/', '', $row_system['product_category'])) ?>" <?= $selected ?>><?= $row_system['product_system'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-line" data-filter="line" data-filter-name="Product Line">
                            <option value="" data-category="">All Product Lines</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_line = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                                $result_line = mysqli_query($conn, $query_line);
                                while ($row_line = mysqli_fetch_array($result_line)) {
                                ?>
                                    <option value="<?= $row_line['product_line'] ?>" data-category="<?= $row_line['product_category'] ?>" <?= $selected ?>><?= $row_line['product_line'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-type" data-filter="type" data-filter-name="Product Type">
                            <option value="" data-category="">All Product Types</option>
                            <optgroup label="Product Type">
                                <?php
                                $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                                $result_type = mysqli_query($conn, $query_type);
                                while ($row_type = mysqli_fetch_array($result_type)) {
                                ?>
                                    <option value="<?= $row_type['product_type'] ?>" data-category="<?= $row_type['product_category'] ?>" <?= $selected ?>><?= $row_type['product_type'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-category py-0 ps-5 select2 filter-selection" id="select-profile" data-filter="profile" data-filter-name="Product Profile">
                            <option value="" data-category="">All Profile Types</option>
                            <optgroup label="Product Line">
                                <?php
                                $query_profile = "SELECT * FROM profile_type WHERE hidden = '0' AND status = '1' ORDER BY `profile_type` ASC";
                                $result_profile = mysqli_query($conn, $query_profile);
                                while ($row_profile = mysqli_fetch_array($result_profile)) {
                                ?>
                                    <option value="<?= $row_profile['profile_type'] ?>" data-category="<?= $v['product_category'] ?>" <?= $selected ?>><?= $row_profile['profile_type'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-color" data-filter="color" data-filter-name="Product Color">
                            <option value="" data-category="">All Colors</option>
                            <optgroup label="Product Colors">
                                <?php
                                $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                $result_color = mysqli_query($conn, $query_color);
                                while ($row_color = mysqli_fetch_array($result_color)) {
                                ?>
                                    <option value="<?= $row_color['color_name'] ?>" data-category="category" <?= $selected ?>><?= $row_color['color_name'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-grade" data-filter="grade" data-filter-name="Product Grade">
                            <option value="" data-category="">All Grades</option>
                            <optgroup label="Product Grades">
                                <?php
                                $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                $result_grade = mysqli_query($conn, $query_grade);
                                while ($row_grade = mysqli_fetch_array($result_grade)) {
                                ?>
                                    <option value="<?= $row_grade['product_grade'] ?>" data-category="grade" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-gauge" data-filter="gauge" data-filter-name="Product Gauge">
                            <option value="" data-category="">All Gauges</option>
                            <optgroup label="Product Gauges">
                                <?php
                                $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY `product_gauge` ASC";
                                $result_gauge = mysqli_query($conn, $query_gauge);
                                while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                ?>
                                    <option value="<?= $row_gauge['product_gauge'] ?>" data-category="gauge" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="px-3 mb-2"> 
                    <input type="checkbox" id="toggleActive" checked> Show Active Only
                </div>
                <div class="px-3 mb-2">
                    <input type="checkbox" id="onlyInStock" <?= $onlyInStock ? 'checked' : '' ?>> Show only In Stock
                </div>
                <div class="d-flex justify-content-end py-2">
                    <button type="button" class="btn btn-outline-primary reset_filters">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
            <div class="col-9">
                <h3 class="card-title mb-2">
                    Products List 
                </h3>
                <div id="selected-tags" class="mb-2"></div>
                <div class="datatables">
                    <div class="table-responsive">
                        <table id="productList" class="table search-table align-middle text-wrap">
                            <thead class="header-item">
                                <tr>
                                    <?php if (showCol('product_name')): ?>
                                        <th>Product Name</th>
                                    <?php endif; ?>

                                    <?php if (showCol('product_category')): ?>
                                        <th>Product Category</th>
                                    <?php endif; ?>

                                    <?php if (showCol('product_line')): ?>
                                        <th>Product Line</th>
                                    <?php endif; ?>

                                    <?php if (showCol('product_type')): ?>
                                        <th>Product Type</th>
                                    <?php endif; ?>

                                    <?php if (showCol('status')): ?>
                                        <th>Status</th>
                                    <?php endif; ?>

                                    <?php if (showCol('action')): ?>
                                        <th>Action</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    </div>
</div>

<script src="includes/pricing_data.js"></script>

<script>
    $(document).ready(function() {
        var selectedCategory = '';

        var table = $('#productList').DataTable({
            order: [[1, "asc"]],
            pageLength: 100,
            ajax: {
                url: 'pages/product4_ajax.php',
                type: 'POST',
                data: { action: 'fetch_products' },
            },
            columns: [
                <?php if (showCol('product_name')): ?>
                    { data: 'product_name_html' },
                <?php endif; ?>

                <?php if (showCol('product_category')): ?>
                    { data: 'product_category' },
                <?php endif; ?>

                <?php if (showCol('product_line')): ?>
                    { data: 'product_line' },
                <?php endif; ?>

                <?php if (showCol('product_type')): ?>
                    { data: 'product_type' },
                <?php endif; ?>

                <?php if (showCol('status')): ?>
                    { data: 'status_html' },
                <?php endif; ?>

                <?php if (showCol('action')): ?>
                    { data: 'action_html' }
                <?php endif; ?>
            ],
            createdRow: function (row, data, dataIndex) {
                $(row).attr('data-category', data.product_category);
                $(row).attr('data-system', data.product_system);
                $(row).attr('data-line', data.product_line);
                $(row).attr('data-type', data.product_type);
                $(row).attr('data-profile', data.profile);
                $(row).attr('data-color', data.color);
                $(row).attr('data-grade', data.grade);
                $(row).attr('data-gauge', data.gauge);
                $(row).attr('data-active', data.active);
                $(row).attr('data-instock', data.instock);
            },
            "dom": 'lftp'
        });


        $('#productList_filter').hide();

        $(document).on('click', '.remove-image-btn', function(event) {
            event.preventDefault();
            let imageId = $(this).data('image-id');

            if (confirm("Are you sure you want to remove this image?")) {
                $.ajax({
                    url: 'pages/product_ajax.php',
                    type: 'POST',
                    data: { 
                        image_id: imageId,
                        action: "remove_image"
                    },
                    success: function(response) {
                        if(response.trim() == 'success') {
                            $('button[data-image-id="' + imageId + '"]').closest('.col-md-2').remove();
                        } else {
                            alert('Failed to remove image.');
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            }
        });

        $('#toggleActive').trigger('change');

        $(".select2").each(function() {
            $(this).select2({
                dropdownParent: $(this).parent()
            });
        });

        $(document).on('change', '#product_category', function() {
            updateSearchCategory();
        });
       
        function updateSearchCategory() {
            var product_category = $('#product_category').val() || '';
            var product_id = $('#product_id').val() || '';
            var filename = $('#product_category option:selected').data('filename') || '';

            if (filename != '') {
                $.ajax({
                    url: 'pages/' + filename,
                    type: 'POST',
                    data: {
                        id: product_id,
                        action: "fetch_product_modal"
                    },
                    success: function(response) {
                        $('#add-fields').html(response);

                        $('.hidden-field').removeClass('d-none');

                        let selectedCategory = $('#product_category').val() || '';

                        $('.add-category option').each(function () {
                            let categoryAttr = String($(this).data('category') || '');
                            let match = categoryAttr.includes(selectedCategory);
                            $(this).toggle(match);
                        });

                        $(".select2").each(function() {
                            let $this = $(this);

                            if ($this.hasClass("select2-hidden-accessible")) {
                                $this.select2('destroy');
                                $this.removeAttr('data-select2-id');
                                $this.next('.select2-container').remove();
                            }

                            $this.select2({
                                width: '100%',
                                dropdownParent: $this.parent()
                            });
                        });
                    }
                });
            } else {
                $('#add-fields').html('');
                $('.hidden-field').addClass('d-none');
            }
        }

        $(document).on('click', '#addProductModalBtn', function(event) {
            event.preventDefault();
            $('#product_id').val($(this).data('id') || '');
            $('#product_category').val($(this).data('category') || '');

            $('.hidden-field').addClass('d-none');

            updateSearchCategory();
            $('#product_form')[0].reset();
            $('#product_id').val("");
            
            $('#addProductModal').modal('show');
        });

        $(document).on('click', '#edit_product_btn', function(event) {
            event.preventDefault();

            $('#product_id').val($(this).data('id') || '');
            $('#product_category').val($(this).data('category') || '');
            $('#product_line').val($(this).data('line') || '');
            $('#product_type').val($(this).data('type') || '');
            $('#product_system').val($(this).data('system') || '');
            $('#grade').val($(this).data('grade') || '');
            $('#gauge').val($(this).data('gauge') || '');
            $('#profile').val($(this).data('profile') || '');
            $('#color').val($(this).data('color') || '');

            $('.hidden-field').removeClass('d-none');
            updateSearchCategory();
            $('#addProductModal').modal('show');
        });

        $(document).on('click', '#duplicate_product_btn', function(event) {
            event.preventDefault();
            if (!confirm("Are you sure you want to duplicate this product?")) {
                return;
            }
            var id = $(this).data('id') || '';
            $.ajax({
                url: 'pages/product4_ajax.php',
                type: 'POST',
                data: {
                    product_id: id,
                    action: "duplicate_product"
                },
                success: function(response) {
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Product Duplicated successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);

                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '#downloadProductModalBtn', function(event) {
            $('#downloadProductModal').modal('show');
        });

        $(document).on('click', '#downloadClassModalBtn', function(event) {
            $('#downloadClassModal').modal('show');
        });

        $(document).on('click', '#uploadProductModalBtn', function(event) {
            $('#uploadProductModal').modal('show');
        });

        $(document).on('click', '#readUploadProductBtn', function(event) {
            $.ajax({
                url: 'pages/product4_ajax.php',
                type: 'POST',
                data: {
                    action: "fetch_uploaded_modal"
                },
                success: function(response) {
                    $('#uploaded_excel').html(response);
                    $('#readUploadProductModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $("#download_product_form").submit(function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            formData.append("action", "download_excel");

            $.ajax({
                url: "pages/product4_ajax.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    window.location.href = "pages/product4_ajax.php?action=download_excel&category=" + encodeURIComponent($("#select-download-category").val());
                },
                error: function (xhr, status, error) {
                    alert("Error downloading file: " + error);
                }
            });
        });

        $("#download_class_form").submit(function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            formData.append("action", "download_classifications");

            $.ajax({
                url: "pages/product4_ajax.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    window.location.href = "pages/product4_ajax.php?action=download_classifications&class=" + encodeURIComponent($("#select-download-class").val());
                },
                error: function (xhr, status, error) {
                    alert("Error downloading file: " + error);
                }
            });
        });

        $('#upload_product_form').on('submit', function (e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            formData.append('action', 'upload_excel');

            $.ajax({
                url: 'pages/product4_ajax.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    response = response.trim();
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Data Uploaded successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }  
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    console.error('Response:', jqXHR.responseText);

                    $('#responseHeader').text("Error");
                    $('#responseMsg').text("An error occurred while processing your request.");
                    $('#responseHeaderContainer').removeClass("bg-success").addClass("bg-danger");
                    $('#response-modal').modal("show");
                }
            });
        });

        $(document).on('click', '#saveTable', function(event) {
            if (confirm("Are you sure you want to save this Excel data to the products?")) {
                var formData = new FormData();
                formData.append("action", "save_table");

                $.ajax({
                    url: "pages/product4_ajax.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        response = response.trim();
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text(response);
                        $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                        $('#response-modal').modal("show");
                    }
                });
            }
        });

        $(document).on('click', '#view_product_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/product4_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_view_modal"
                    },
                    success: function(response) {
                        $('#updateProductModal').html(response);
                        $('#updateProductModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('click', '#view_product_details', function(event) {
            event.preventDefault();
            var id = $(this).data('id');
            $.ajax({
                url: 'pages/product4_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    action: "fetch_details_modal"
                },
                success: function(response) {
                    $('#viewDetailsModalBody').html(response);
                    $('#viewDetailsModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('change', '.inventory_supplier', function () {
            let supplier_id = $(this).val();

            if (supplier_id) {
                $.ajax({
                    url: 'pages/inventory_ajax.php',
                    type: 'POST',
                    data: { 
                    supplier_id: supplier_id,
                    action: 'fetch_supplier_packs'
                    },
                    dataType: 'json',
                    success: function (response) {
                        let packDropdown = $('.pack_select');
                        packDropdown.empty();
                        packDropdown.append('<option value="">Select Pack...</option>');

                        if (response.length > 0) {
                            $.each(response, function (index, pack) {
                            packDropdown.append('<option value="' + pack.id + '" data-count="' + pack.pack_count + '">' + pack.pack + ' (' + pack.pack_count + ')</option>');
                            });
                        } else {
                            packDropdown.append('<option value="">No Packs Available</option>');
                        }
                        
                        packDropdown.trigger('change');
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error: ", status, error);
                    }
                });

            } else {
            $('.pack_select').empty().append('<option value="0">Select Pack...</option>').trigger('change');
            }
        });

        $(document).on('click', '.changeStatus', function(event) {
            event.preventDefault(); 
            var product_id = $(this).data('id');
            var status = $(this).data('status');
            var no = $(this).data('no');
            $.ajax({
                url: 'pages/product4_ajax.php',
                type: 'POST',
                data: {
                    product_id: product_id,
                    status: status,
                    action: 'change_status'
                },
                success: function(response) {
                    console.log(response);
                    if (response == 'success') {
                        table.ajax.reload(null, false);
                    } else {
                        alert('Failed to change status.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '.hideProduct', function(event) {
            event.preventDefault();

            var product_id = $(this).data('id');
            var rowId = $(this).data('row');

            if (!confirm("Are you sure you want to archive this product?")) {
                return;
            }

            $.ajax({
                url: 'pages/product4_ajax.php',
                type: 'POST',
                data: {
                    product_id: product_id,
                    action: 'hide_product'
                },
                success: function(response) {
                    if (response == 'success') {
                        table.ajax.reload(null, false);
                    } else {
                        alert('Failed to hide product system.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('submit', '#product_form', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/product4_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addProductModal').modal('hide');
                    if (response.trim() === "success_update") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Product successfully updated.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        table.ajax.reload(null, false);
                    } else if (response.trim() === "success_add") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New product added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        table.ajax.reload(null, false);
                    } else {
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);

                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }
                    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('submit', '#add_inventory', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');
              
            $.ajax({
                url: 'pages/inventory_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addInventoryModal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New inventory added successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");

                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        $('#responseHeader').text("Failed");
                        $('#responseMsg').text(response);

                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response-modal').modal("show");
                    }

                    
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        function updateColorMult() {
            let colorGroup = ($('#color').val() || '').trim();

            if (colorGroup) {
                let productSystem = ($('#product_system').val() || '').trim();
                let grade         = ($('#grade').val() || '').trim();
                let gauge         = ($('#gauge').val() || '').trim();
                let category      = ($('#product_category').val() || '').trim();

                $.ajax({
                    url: 'pages/product4_ajax.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        color_group: colorGroup,
                        product_system: productSystem,
                        grade: grade,
                        gauge: gauge,
                        product_category: category,
                        action: "fetch_color_multiplier"
                    },
                    success: function(response) {
                        console.log(response);
                        if (response && !response.error && !isNaN(response.multiplier)) {
                            $('#color_multiplier').val(parseFloat(response.multiplier).toFixed(3));
                        } else {
                            $('#color_multiplier').val("");
                            console.warn(response.error || 'No valid data found');
                            console.log(colorGroup);
                            console.log(colorPaint);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        $('#color_multiplier').val("0");
                    }
                });
            } else {
                $('#color_multiplier').val("1.000");
            }
        }

        $(document).on('click', '#add_inventory_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/product4_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_add_inventory"
                    },
                    success: function(response) {
                        $('#inventory-body').html(response);
                        $(".select2-inventory").each(function () {
                            $(this).select2({
                                width: '100%',
                                allowClear: true,
                                placeholder: "Select an option",
                                dropdownParent: $(this).parent()
                            });
                        });

                        $('#addInventoryModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on("change", "#gauge, #grade", function () {
            $('#color').val(null).trigger('change');
        });

        $(document).on("change", ".calculate", function () {
            let selectedCategory = $('#product_category').val();
            let selectedSystem = $('#product_system').val();
            let selectedLine = $('#product_line').val();
            let selectedType = $('#product_type').val();
            let selectedGrade = $('#grade').val();
            let selectedGauge = $('#gauge').val();

            let $flatSheetWidth = $('#flat_sheet_width');

            let matchedFSheet = $flatSheetWidth.find('option').filter(function () {
                let $option = $(this);
                if ($option.val() === "") {
                    return false;
                }
                let categoryMatch = $option.data('category') == selectedCategory || !$option.data('category');
                let systemMatch = $option.data('system') == selectedSystem || !$option.data('system');
                let lineMatch = $option.data('line') == selectedLine || !$option.data('line');
                let typeMatch = $option.data('type') == selectedType || !$option.data('type');
                return categoryMatch && systemMatch && lineMatch && typeMatch;
            }).first();

            if (matchedFSheet.length) {
                $flatSheetWidth.val(matchedFSheet.val());
            } else {
                $flatSheetWidth.val('');
            }
            
            updateColorMult();

            let color_multi = parseFloat($("#color_multiplier").val()) || 1;
            let grade_multi = parseFloat($("#grade option:selected").attr("data-multiplier")) || 1;
            let color_multiplier = grade_multi * color_multi;
            $("#color_multiplier").val(color_multiplier.toFixed(3));

            let stock_multi = parseFloat($("#color_paint option:selected").attr("data-stock-multiplier")) || 1;
            let cost = color_multiplier * stock_multi;
            $("#cost").val(cost.toFixed(3));

            let descriptionParts = [];

            if (selectedSystem) descriptionParts.push($("#product_system option:selected").text().trim());
            if (selectedGauge) descriptionParts.push(selectedGauge);

            $("#description").val(descriptionParts.join(" - "));
            $("#product_item").val(descriptionParts.join(" - "));

        });
        
        $(document).on('mousedown', '.readonly', function() {
            e.preventDefault();
        });

        function filterTable() {
            var textSearch = $('#text-srh').val().toLowerCase();
            var isActive = $('#toggleActive').is(':checked');

            $.fn.dataTable.ext.search = [];

            if (textSearch) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
                });
            }

            if (isActive) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    return $(table.row(dataIndex).node()).find('a .alert').text() === 'Active';
                });
            }

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var row = $(table.row(dataIndex).node());
                var match = true;

                $('.filter-selection').each(function() {
                    const normalize = str => str
                        ?.toString()
                        .trim()
                        .replace(/\s+/g, ' ')
                        .toLowerCase() || '';

                    var filterValue = normalize($(this).val());
                    var rowValue = normalize(row.data($(this).data('filter')));

                    if (filterValue && filterValue !== '/') {
                        if (!rowValue.includes(filterValue)) {
                            match = false;
                            return false;
                        }
                    }
                });

                return match;
            });


            table.draw();
            updateSelectedTags();
        }

        function updateSelectedTags() {
            var displayDiv = $('#selected-tags');
            displayDiv.empty();

            $('.filter-selection').each(function() {
                var selectedOption = $(this).find('option:selected');
                var selectedText = selectedOption.text();
                var filterName = $(this).data('filter-name');

                if ($(this).val()) {
                    displayDiv.append(`
                        <div class="d-inline-block p-1 m-1 border rounded bg-light">
                            <span class="text-dark">${filterName}: ${selectedText}</span>
                            <button type="button" 
                                class="btn-close btn-sm ms-1 remove-tag" 
                                style="width: 0.75rem; height: 0.75rem;" 
                                aria-label="Close" 
                                data-select="#${$(this).attr('id')}">
                            </button>
                        </div>
                    `);
                }
            });

            $('.remove-tag').on('click', function() {
                $($(this).data('select')).val('').trigger('change');
                $(this).parent().remove();
            });
        }

        $(document).on('input change', '#text-srh, #toggleActive, #onlyInStock, .filter-selection', filterTable);

        $(document).on('blur', '.table_data', function() {
            let newValue;
            let updatedData = {};
            
            // Determine the new value based on the element type (select or td)
            if ($(this)[0].tagName.toLowerCase() === 'select') {
                const selectedValue = $(this).val();
                const selectedText = $(this).find('option:selected').text();
                newValue = selectedValue ? selectedValue : selectedText;
            } 
            else if ($(this).is('td')) {
                newValue = $(this).text();
            }
            
            const headerName = $(this).data('header-name');
            const id = $(this).data('id');

            updatedData = {
                action: 'update_product_data',
                id: id,
                header_name: headerName,
                new_value: newValue,
            };

            $.ajax({
                url: 'pages/product4_ajax.php',
                type: 'POST',
                data: updatedData,
                success: function(response) {
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                    alert('Error updating data');
                }
            });
        });

        $(document).on('click', '.reset_filters', function () {
            $('.filter-selection').each(function () {
                $(this).val(null).trigger('change.select2');
            });

            $('#text-srh').val('');

            filterTable();
        });

        $(document).on("change", "#standing_seam, #board_batten", function () {
            if ($(this).is("#standing_seam")) {
                $("#board_batten").prop("checked", false);
            } else if ($(this).is("#board_batten")) {
                $("#standing_seam").prop("checked", false);
            }
        });

    });
</script>



