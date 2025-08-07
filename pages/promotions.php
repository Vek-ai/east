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

$page_title = "Promotions/Discounts"
?>
<style>
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
</style>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
        <div class="card-body px-0">
            <div class="d-flex justify-content-between align-items-center">
            <div><br>
                <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title ?></h4>
                <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                    <a class="text-muted text-decoration-none" href="">Home
                    </a>
                    </li>
                    <li class="breadcrumb-item text-muted" aria-current="page"><?= $page_title ?></li>
                </ol>
                </nav>
            </div>
            </div>
        </div>
    </div>

    <div class="card card-body">
        <div class="row">
            <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
                <button type="button" id="addModalBtn" class="btn btn-primary d-flex align-items-center" data-id="" data-type="add">
                    <i class="ti ti-plus text-white me-1 fs-5"></i> Add <?= $page_title ?>
                </button>
            </div>
        </div>
    </div>

    <div class="widget-content searchable-container list">
        <div class="card card-body">
            <div class="row">
                <div class="col-3">
                    <h3 class="card-title align-items-center mb-2">
                        Filter Products 
                    </h3>
                    <div class="position-relative w-100 px-1 mr-0 mb-2">
                        <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search Product">
                        <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                    </div>
                    <div class="align-items-center">
                        <div class="position-relative w-100 px-1 mb-2">
                            <select class="form-control py-0 ps-5 select2 filter-selection" id="select-category" id="filter-category" data-filter="category" data-filter-name="Product Category">
                                <option value="" data-category="">All Categories</option>
                                <optgroup label="Category">
                                    <?php
                                    $query_category = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                    $result_category = mysqli_query($conn, $query_category);
                                    while ($row_category = mysqli_fetch_array($result_category)) {
                                        $selected = ($category_id == $row_category['product_category_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_category['product_category_id'] ?>" data-category="<?= $row_category['product_category'] ?>" <?= $selected ?>><?= $row_category['product_category'] ?></option>
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
                                    $query_profile = "SELECT * FROM profile_type WHERE hidden = '0'";
                                    $result_profile = mysqli_query($conn, $query_profile);
                                    while ($row_profile = mysqli_fetch_array($result_profile)) {
                                        $selected = ($profile_id == $row_profile['profile_type_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_profile['profile_type_id'] ?>" data-category="<?= $v['product_category'] ?>" <?= $selected ?>><?= $row_profile['profile_type'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="position-relative w-100 px-1 mb-2">
                            <select class="form-control search-chat py-0 ps-5 select2 filter-selection" id="select-color" data-filter="color" data-filter-name="Paint Color">
                                <option value="" data-category="">All Colors</option>
                                <optgroup label="Product Colors">
                                    <?php
                                    $query_color = "SELECT * FROM paint_colors WHERE hidden = '0' AND color_status = '1' ORDER BY `color_name` ASC";
                                    $result_color = mysqli_query($conn, $query_color);
                                    while ($row_color = mysqli_fetch_array($result_color)) {
                                        $selected = ($color_id == $row_color['color_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_color['color_id'] ?>" data-category="category" <?= $selected ?>><?= $row_color['color_name'] ?></option>
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
                                        $selected = ($gauge_id == $row_gauge['product_gauge_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_gauge['product_gauge_id'] ?>" data-category="gauge" <?= $selected ?>><?= $row_gauge['product_gauge'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="px-3 mb-2">
                        <input type="checkbox" class="filter-selection" id="onlyPromotions" value="true" data-filter="promotion" data-filter-name="On Promotions"> Show Items on Promotion Only
                    </div>
                    <div class="px-3 mb-2">
                        <input type="checkbox" class="filter-selection" id="onlyOnSale" value="true" data-filter="on-sale" data-filter-name="On Sale"> Show Items on Sale Only
                    </div>
                    
                </div>
                <div class="col-9">
                    <h3 class="card-title mb-2">
                        Promotions/Discounts List 
                    </h3>
                    <div id="selected-tags" class="mb-2"></div>
                    <div class="datatables">
                        <div class="table-responsive">
                            <table id="productList" class="table search-table align-middle text-wrap text-center">
                                <thead class="header-item">
                                    <th class="align-middle text-start">Product Name</th>
                                    <th class="align-middle text-center">On Promotions</th>
                                    <th class="align-middle text-center">On Sale</th>
                                    <th class="align-middle text-center">Reason</th>
                                    <th class="align-middle text-center">Action</th>
                                </thead>
                                <tbody>
                                <?php
                                    $no = 1;
                                    $query_product = "
                                        SELECT 
                                            *
                                        FROM 
                                            product
                                        WHERE 
                                            hidden = '0' AND status = '1' AND ( on_sale = '1' || on_promotion = '1' )
                                    ";

                                    $result_product = mysqli_query($conn, $query_product);            
                                    while ($row_product = mysqli_fetch_array($result_product)) {
                                        $product_id = $row_product['product_id'];
                                        $db_status = $row_product['status'];

                                        if ($db_status == '0') {
                                            $status_icon = "text-danger ti ti-trash";
                                            $status = "<a href='#'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                                        } else {
                                            $status_icon = "text-warning ti ti-reload";
                                            $status = "<a href='#'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                                        }

                                        if(!empty($row_product['main_image'])){
                                            $picture_path = $row_product['main_image'];
                                        }else{
                                            $picture_path = "images/product/product.jpg";
                                        }
                    
                                    ?>
                                        <!-- start row -->
                                        <tr class="search-items" 
                                            data-category="<?= $row_product['product_category'] ?>"
                                            data-profile="<?= $row_product['profile'] ?>"
                                            data-color="<?= $row_product['color'] ?>"
                                            data-gauge="<?= $row_product['gauge'] ?>"
                                            data-on-sale="<?= $row_product['on_sale'] ?? '' == 1 ? 'true' : 'false' ?>"
                                            data-promotion="<?= $row_product['on_promotion'] ?? '' == 1 ? 'true' : 'false' ?>"
                                            >
                                            <td class="align-middle">
                                                <a href="/?page=product_details&product_id=<?= $row_product['product_id'] ?>">
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= $picture_path ?>" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                                        <div class="ms-3">
                                                            <h6 class="fw-semibold mb-0 fs-4"><?= $row_product['product_item'] ?></h6>
                                                        </div>
                                                    </div>
                                                </a>
                                            </td>
                                            <td class="align-middle"><?= $row_product['on_sale'] ?? '' == 1 ? '<i class="fas fa-check"></i>' : '' ?></td>
                                            <td class="align-middle"><?= $row_product['on_promotion'] ?? '' == 1 ? '<i class="fas fa-check"></i>' : '' ?></td>
                                            <td class="align-middle"><?= $row_product['reason'] ?></td>
                                            <td class="align-middle">
                                                <div class="action-btn text-center d-flex justify-content-center gap-2">
                                                    <a href="#" id="view_product_btn" class="edit d-flex align-items-center" data-id="<?= $row_product['product_id'] ?>" title="View">
                                                        <i class="text-primary ti ti-eye fs-7"></i>
                                                    </a>
                                                    <a href="#" id="addModalBtn" title="Edit" class="edit d-flex align-items-center" data-id="<?= $row_product['product_id'] ?>" data-type="edit">
                                                        <i class="text-warning ti ti-pencil fs-7"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php 
                                    $no++;
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

        <div class="modal fade" id="updateProductModal" tabindex="-1" role="dialog" aria-labelledby="updateProductModal" aria-hidden="true">  
        </div>

        <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header d-flex align-items-center">
                        <h4 class="modal-title" id="add-header">
                            Add <?= $page_title ?>
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="promotionDiscountsForm" class="form-horizontal">
                        <div class="modal-body">
                            <div class="card">
                                <div class="card-body">
                                <div id="add-fields" class=""></div>
                                <div class="form-actions">
                                    <div class="border-top">
                                        <div class="row mt-2">
                                            <div class="col-6 text-start"></div>
                                            <div class="col-6 text-end ">
                                                <button type="submit" class="btn btn-primary" style="border-radius: 10%;">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </form>
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
    </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#productList').DataTable({
            "order": [[1, "asc"]],
            "pageLength": 100,
            "lengthMenu": [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            "dom": 'lftp',
        });

        $('#select-system, #select-line, #select-profile, #select-color, #select-grade, #select-gauge, #select-category, #select-type, #onlyInStock').on('change', filterTable);

        $('#text-srh').on('keyup', filterTable);

        $(document).on('click', '#view_product_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            $.ajax({
                    url: 'pages/promotions_ajax.php',
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

        $(".select2").each(function() {
            $(this).select2({
                width: '100%',
                dropdownParent: $(this).parent()
            });
        });

        $('#promotionDiscountsForm').on('submit', function(event) {
            event.preventDefault(); 
            var formData = new FormData(this);
            formData.append('action', 'add_update');
            $.ajax({
                url: 'pages/promotions_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                $('.modal').modal("hide");
                if (response === "update_success") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text("<?= $page_title ?> updated successfully.");
                    $('#responseHeaderContainer').removeClass("bg-danger");
                    $('#responseHeaderContainer').addClass("bg-success");
                    $('#response-modal').modal("show");
                    $('#response-modal').on('hide.bs.modal', function () {
                        location.reload();
                    });
                } else if (response === "add_update") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text("New <?= $page_title ?> added successfully.");
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

        $(document).on('click', '#addModalBtn', function(event) {
            event.preventDefault();
            var id = $(this).data('id') || '';
            var type = $(this).data('type') || '';

            if(type == 'edit'){
                $('#add-header').html('Update <?= $page_title ?>');
            }else{
                $('#add-header').html('Add <?= $page_title ?>');
            }

            $.ajax({
                url: 'pages/promotions_ajax.php',
                type: 'POST',
                data: {
                    id : id,
                    action: 'fetch_modal_content'
                },
                success: function (response) {
                    $('#add-fields').html(response);

                    $("#product_select").each(function () {
                        $(this).select2({
                            width: '100%',
                            dropdownParent: $(this).parent(),
                            placeholder: "Select Product/s"
                        });
                    });

                    $('#addModal').modal('show');
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

        function filterTable() {
            var textSearch = $('#text-srh').val().toLowerCase();

            $.fn.dataTable.ext.search = [];

            if (textSearch) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    return $(table.row(dataIndex).node()).text().toLowerCase().includes(textSearch);
                });
            }

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var row = $(table.row(dataIndex).node());
                var match = true;

                $('.filter-selection').each(function() {
                    var $filter = $(this);
                    var filterType = $filter.attr('type');
                    var dataKey = $filter.data('filter');
                    var filterVal = '';

                    if (filterType === 'checkbox') {
                        if ($filter.is(':checked')) {
                            let rowVal = row.data(dataKey);
                            match = rowVal === true || rowVal === 'true';
                        }
                    } else {
                        filterVal = $filter.val()?.toString().trim().toLowerCase() || '';
                        let rowVal = row.data(dataKey)?.toString().toLowerCase() || '';

                        if (filterVal && filterVal !== '/' && !rowVal.includes(filterVal)) {
                            match = false;
                        }
                    }

                    if (!match) return false;
                });

                return match;
            });

            table.draw();
            updateSelectedTags();
        }


        function updateSelectedTags() {
            var displayDiv = $('#selected-tags');
            displayDiv.empty();

            $('.filter-selection').each(function () {
                var $filter = $(this);
                var filterName = $filter.data('filter-name');
                var filterId = $filter.attr('id');
                var value = $filter.val();

                if ($filter.attr('type') === 'checkbox') {
                    if ($filter.is(':checked')) {
                        displayDiv.append(`
                            <div class="d-inline-block p-1 m-1 border rounded bg-light">
                                <span class="text-dark">${filterName}</span>
                                <button type="button"
                                    class="btn-close btn-sm ms-1 remove-tag"
                                    style="width: 0.75rem; height: 0.75rem;"
                                    aria-label="Close"
                                    data-select="#${filterId}">
                                </button>
                            </div>
                        `);
                    }
                } else {
                    if (value) {
                        var selectedOption = $filter.find('option:selected');
                        var selectedText = selectedOption.text().trim();

                        displayDiv.append(`
                            <div class="d-inline-block p-1 m-1 border rounded bg-light">
                                <span class="text-dark">${filterName}: ${selectedText}</span>
                                <button type="button"
                                    class="btn-close btn-sm ms-1 remove-tag"
                                    style="width: 0.75rem; height: 0.75rem;"
                                    aria-label="Close"
                                    data-select="#${filterId}">
                                </button>
                            </div>
                        `);
                    }
                }
            });

            $('.remove-tag').on('click', function () {
                var $target = $($(this).data('select'));
                if ($target.attr('type') === 'checkbox') {
                    $target.prop('checked', false).trigger('change');
                } else {
                    $target.val('').trigger('change');
                }
                $(this).parent().remove();
            });
        }


        $(document).on('input change', '#text-srh, .filter-selection', filterTable);

        filterTable();

    });
</script>



