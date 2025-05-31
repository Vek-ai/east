<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

require 'includes/dbconn.php';
require 'includes/functions.php';
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
                <h4 class="font-weight-medium fs-14 mb-0">Promotions/Discounts</h4>
                <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                    <a class="text-muted text-decoration-none" href="">Home
                    </a>
                    </li>
                    <li class="breadcrumb-item text-muted" aria-current="page">Promotions/Discounts</li>
                </ol>
                </nav>
            </div>
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
                    <div class="position-relative w-100 px-0 mr-0 mb-2">
                        <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search Product">
                        <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                    </div>
                    <div class="align-items-center">
                        <div class="position-relative w-100 px-1 mb-2">
                            <select class="form-control py-0 ps-5 select2" id="select-category" data-category="">
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
                            <select class="form-control search-category py-0 ps-5 select2" id="select-system" data-category="">
                                <option value="" data-category="">All Product Systems</option>
                                <optgroup label="Product Type">
                                    <?php
                                    $query_system = "SELECT * FROM product_system WHERE hidden = '0' AND status = '1' ORDER BY `product_system` ASC";
                                    $result_system = mysqli_query($conn, $query_system);
                                    while ($row_system = mysqli_fetch_array($result_system)) {
                                        $selected = ($product_system == $row_system['product_system_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_system['product_system_id'] ?>" data-category="<?= $row_system['product_category'] ?>" <?= $selected ?>><?= $row_system['product_system'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="position-relative w-100 px-1 mb-2">
                            <select class="form-control search-category py-0 ps-5 select2" id="select-line" data-category="">
                                <option value="" data-category="">All Product Lines</option>
                                <optgroup label="Product Type">
                                    <?php
                                    $query_line = "SELECT * FROM product_line WHERE hidden = '0' AND status = '1' ORDER BY `product_line` ASC";
                                    $result_line = mysqli_query($conn, $query_line);
                                    while ($row_line = mysqli_fetch_array($result_line)) {
                                        $selected = ($type_id == $row_line['product_line_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_line['product_line_id'] ?>" data-category="<?= $row_line['product_category'] ?>" <?= $selected ?>><?= $row_line['product_line'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="position-relative w-100 px-1 mb-2">
                            <select class="form-control search-category py-0 ps-5 select2" id="select-type" data-category="">
                                <option value="" data-category="">All Product Types</option>
                                <optgroup label="Product Type">
                                    <?php
                                    $query_type = "SELECT * FROM product_type WHERE hidden = '0' AND status = '1' ORDER BY `product_type` ASC";
                                    $result_type = mysqli_query($conn, $query_type);
                                    while ($row_type = mysqli_fetch_array($result_type)) {
                                        $selected = ($type_id == $row_type['product_type_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_type['product_type_id'] ?>" data-category="<?= $row_type['product_category'] ?>" <?= $selected ?>><?= $row_type['product_type'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="position-relative w-100 px-1 mb-2">
                            <select class="form-control search-category py-0 ps-5 select2" id="select-profile" data-category="">
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
                            <select class="form-control search-chat py-0 ps-5 select2" id="select-color" data-category="">
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
                            <select class="form-control search-chat py-0 ps-5 select2" id="select-grade" data-category="">
                                <option value="" data-category="">All Grades</option>
                                <optgroup label="Product Grades">
                                    <?php
                                    $query_grade = "SELECT * FROM product_grade WHERE hidden = '0' AND status = '1' ORDER BY `product_grade` ASC";
                                    $result_grade = mysqli_query($conn, $query_grade);
                                    while ($row_grade = mysqli_fetch_array($result_grade)) {
                                        $selected = ($grade_id == $row_grade['product_grade_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $row_grade['product_grade_id'] ?>" data-category="grade" <?= $selected ?>><?= $row_grade['product_grade'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="position-relative w-100 px-1 mb-2">
                            <select class="form-control search-chat py-0 ps-5 select2" id="select-gauge" data-category="">
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
                        <input type="checkbox" id="onlyInStock" <?= $onlyInStock ? 'checked' : '' ?>> Show only In Stock
                    </div>
                </div>
                <div class="col-9">
                    <h3 class="card-title mb-2">
                        Promotions/Discounts List 
                    </h3>
                    <div id="selected-tags" class="mb-2"></div>
                    <div class="datatables">
                        <div class="table-responsive">
                            <table id="productList" class="table search-table align-middle text-wrap">
                                <thead class="header-item">
                                <th>Product Name</th>
                                <th>Promotions</th>
                                <th>Discounts</th>
                                <th>Action</th>
                                </thead>
                                <tbody>
                                <?php
                                    $no = 1;
                                    $query_product = "
                                        SELECT 
                                            p.*,
                                            COALESCE(SUM(i.quantity_ttl), 0) AS total_quantity
                                        FROM 
                                            product_duplicate AS p
                                        LEFT JOIN 
                                            inventory AS i ON p.product_id = i.product_id
                                        WHERE 
                                            p.hidden = '0' AND p.status = '1'
                                        GROUP BY p.product_id
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
                                            data-system="<?= $row_product['product_system'] ?>"
                                            data-line="<?= $row_product['product_line'] ?>"
                                            data-profile="<?= $row_product['profile'] ?>"
                                            data-profile="<?= $row_product['profile'] ?>"
                                            data-color="<?= $row_product['color'] ?>"
                                            data-grade="<?= $row_product['grade'] ?>"
                                            data-gauge="<?= $row_product['gauge'] ?>"
                                            data-category="<?= $row_product['product_category'] ?>"
                                            data-type="<?= $row_product['product_type'] ?>"
                                            data-active="<?= $row_product['p.status'] = 1 ? 1 : 0 ?>"
                                            data-instock="<?= $row_product['total_quantity'] > 1 ? 1 : 0 ?>"
                                            >
                                            <td>
                                                <a href="/?page=product_details&product_id=<?= $row_product['product_id'] ?>">
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= $picture_path ?>" class="rounded-circle" alt="materialpro-img" width="56" height="56">
                                                        <div class="ms-3">
                                                            <h6 class="fw-semibold mb-0 fs-4"><?= $row_product['product_item'] ?></h6>
                                                        </div>
                                                    </div>
                                                </a>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td>
                                                <div class="action-btn text-center">
                                                    <a href="#" id="view_product_btn" class="text-primary edit" data-id="<?= $row_product['product_id'] ?>">
                                                        <i class="text-primary ti ti-eye fs-7"></i>
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

        function filterTable() {
            var system = $('#select-system').val()?.toString() || '';
            var line = $('#select-line').val()?.toString() || '';
            var profile = $('#select-profile').val()?.toString() || '';
            var profile = $('#select-profile').val()?.toString() || '';
            var color = $('#select-color').val()?.toString() || '';
            var grade = $('#select-grade').val()?.toString() || '';
            var gauge = $('#select-gauge').val()?.toString() || '';
            var category = $('#select-category').val()?.toString() || '';
            var type = $('#select-type').val()?.toString() || '';
            var onlyInStock = $('#onlyInStock').prop('checked') ? 1 : 0;
            var textSearch = $('#text-srh').val().toLowerCase();

            $.fn.dataTable.ext.search = [];

            if (textSearch) {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var rowText = $(table.row(dataIndex).node()).text().toLowerCase();
                    return rowText.includes(textSearch);
                });
            }

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var row = $(table.row(dataIndex).node());
                if (system && system !== '/' && row.data('system').toString() !== system) return false;
                if (line && line !== '/' && row.data('line').toString() !== line) return false;
                if (profile && profile !== '/' && row.data('profile').toString() !== profile) return false;
                if (color && color !== '/' && row.data('color').toString() !== color) return false;
                if (grade && grade !== '/' && row.data('grade').toString() !== grade) return false;
                if (gauge && gauge !== '/' && row.data('gauge').toString() !== gauge) return false;
                if (category && category !== '/' && row.data('category').toString() !== category) return false;
                if (type && type !== '/' && row.data('type').toString() !== type) return false;
                if (onlyInStock && row.data('instock') != onlyInStock) return false;

                return true;
            });

            table.draw();
        }

        function updateSelectedTags() {
            const sections = [
                { id: '#select-color', title: 'Color' },
                { id: '#select-grade', title: 'Grade' },
                { id: '#select-gauge', title: 'Gauge' },
                { id: '#select-category', title: 'Category' },
                { id: '#select-profile', title: 'Profile' },
                { id: '#select-system', title: 'System' },
                { id: '#select-line', title: 'Line' },
                { id: '#select-profile', title: 'Profile' },
                { id: '#select-type', title: 'Type' },
            ];

            const displayDiv = $('#selected-tags');
            displayDiv.empty();

            sections.forEach((section) => {
                const selectedOption = $(`${section.id} option:selected`);
                const selectedText = selectedOption.text().trim();

                if (selectedOption.val()) {
                    displayDiv.append(`
                        <div class="d-inline-block p-1 m-1 border rounded bg-light">
                            <span class="text-dark">${section.title}: ${selectedText}</span>
                            <button type="button" 
                                class="btn-close btn-sm ms-1 remove-tag" 
                                style="width: 0.75rem; height: 0.75rem;" 
                                aria-label="Close" 
                                data-tag="${selectedText}" 
                                data-select="${section.id}">
                            </button>
                        </div>
                    `);
                }
            });

            $('.remove-tag').on('click', function() {
                const selectId = $(this).data('select');
                $(selectId).val('').trigger('change');

                $(this).parent().remove();
            });
        }

    });
</script>



