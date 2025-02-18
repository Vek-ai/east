<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$picture_path = "images/product/product.jpg";
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

    #colorList_filter {
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
            <h4 class="font-weight-medium fs-14 mb-0"> Product Colors</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Product Colors</li>
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
    <div class="card card-body">
        <div class="row">
        <div class="col-md-4 col-xl-3">
            <!-- <form class="position-relative">
            <input type="text" class="form-control product-search ps-5" id="input-search" placeholder="Search Contacts..." />
            <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </form> -->
        </div>
        <div class="col-md-8 col-xl-9 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0">
            <div class="action-btn show-btn">
            <a href="javascript:void(0)" class="delete-multiple bg-danger-subtle btn me-2 text-danger d-flex align-items-center ">
                <i class="ti ti-trash me-1 fs-5"></i> Delete All Row
            </a>
            </div>
            <button type="button" id="addProductModalLabel" class="btn btn-primary d-flex align-items-center view_color_btn" data-title="Add" data-id="0">
                <i class="ti ti-users text-white me-1 fs-5"></i> Add Product Color
            </button>
        </div>
        </div>
    </div>

    <div class="modal fade" id="updateProductModal" tabindex="-1" role="dialog" aria-labelledby="updateProductModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="modal_title">
                        Color
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="colorForm" method="POST" class="form-horizontal" enctype="multipart/form-data">
                    <div id="color_details_sec" class="modal-body">
                        
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
            <!-- /.modal-content -->
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
                    Filter Product Colors
                </h3>
                <div class="position-relative w-100 px-0 mr-0 mb-2">
                    <input type="text" class="form-control search-chat py-2 ps-5 " id="text-srh" placeholder="Search Product Color">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center filter_container">
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2-filter" id="select-category" title="Categories">
                            <option value="">All Categories</option>
                            <optgroup label="Category">
                                <?php
                                $query_category = "SELECT * FROM product_category WHERE hidden = '0'";
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
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2-filter" id="select-color-multiplier" title="Color Multipliers">
                            <option value="">All Color Multipliers</option>
                            <optgroup label="Product Color Multipliers">
                                <?php
                                $query_color_mult = "SELECT * FROM color_multiplier WHERE hidden = '0'";
                                $result_color_mult = mysqli_query($conn, $query_color_mult);
                                while ($row_color_mult = mysqli_fetch_array($result_color_mult)) {
                                ?>
                                    <option value="<?= $row_color_mult['id'] ?>"><?= $row_color_mult['color'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2-filter" id="select-grade" title="Color Multipliers">
                            <option value="">All Grades</option>
                            <optgroup label="Product Grades">
                                <option value="1">1</option>
                                <option value="1">1</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2-filter" id="select-gauge" title="Gauges">
                            <option value="">All Gauges</option>
                            <optgroup label="Product Gauges">
                                <?php
                                $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0'";
                                $result_gauge = mysqli_query($conn, $query_gauge);
                                while ($row_gauge = mysqli_fetch_array($result_gauge)) {
                                ?>
                                    <option value="<?= $row_gauge['product_gauge_id'] ?>"><?= $row_gauge['product_gauge'] ?></option>
                                <?php
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2-filter" id="select-coating" title="Coating">
                            <option value="">All Coatings</option>
                            <optgroup label="Category">
                                <option value="bare">Bare</option>
                                <option value="painted">Painted</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2-filter" id="select-surface" title="Surface">
                            <option value="">All Surfaces</option>
                            <optgroup label="Surfaces">
                                <option value="textured">Textured</option>
                                <option value="smooth">Smooth</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="px-3 mb-2 d-none"> 
                    <input type="checkbox" id="toggleActive" checked> Show Active Only
                </div>
            </div>
            <div class="col-9">
                <h3 class="card-title mb-2">
                    Products Colors List 
                </h3>
                <div id="selected-tags" class="mb-2"></div>
                <div class="datatables">
                    <div class="table-responsive">
                        <table id="colorList" class="table search-table align-middle text-wrap">
                            <thead class="header-item">
                            <th>Color</th>
                            <th>Product Category</th>
                            <th>Color Mult.</th>
                            <th>Availability</th>
                            <th>Grade</th>
                            <th>Gauge</th>
                            <th>Action</th>
                            </thead>
                            <tbody>
                            <?php
                                $no = 1;
                                $query_prod_color = "SELECT * FROM product_color";
                                $result_prod_color = mysqli_query($conn, $query_prod_color);            
                                while ($row_prod_color = mysqli_fetch_array($result_prod_color)) {
                                ?>
                                    <tr class="search-items" 
                                        data-category="<?= $row_prod_color['product_category'] ?>"
                                        data-multiplier="<?= $row_prod_color['color_mult_id'] ?>"
                                        data-availability="<?= $row_prod_color['availability'] ?>"
                                        data-coating="<?= strtolower($row_prod_color['coating']) ?>"
                                        data-surface="<?= strtolower($row_prod_color['surface']) ?>"
                                        data-grade="<?= $row_prod_color['grade'] ?>"
                                        data-gauge="<?= $row_prod_color['gauge'] ?>"
                                        >
                                        <td>
                                            <?= $row_prod_color['color_name'] ?>
                                        </td>
                                        <td><?= getProductCategoryName($row_prod_color['product_category']) ?></td>
                                        <td><?= getProductColorMultName($row_prod_color['color_mult_id']) ?></td>
                                        <td><?= $row_prod_color['availability'] ?></td>
                                        <td><?= $row_prod_color['grade'] ?></td>
                                        <td><?= getGaugeName($row_prod_color['gauge']) ?></td>
                                        <td>
                                            <div class="action-btn text-center">
                                                <a href="#" class="text-primary view_color_btn" data-title="Update" data-id="<?= $row_prod_color['id'] ?>">
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
    </div>
</div>

<script>
    
    $(document).ready(function() {
        var table = $('#colorList').DataTable({
            "order": [[1, "asc"]],
            "pageLength": 100,
            "lengthMenu": [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            "dom": 'lftp',
        });

        $('#select-profile, #select-color, #select-color-multiplier, #select-grade, #select-gauge, #select-category, #select-surface, #select-coating').on('change', filterTable);

        $('#text-srh').on('keyup', filterTable);

        $('#toggleActive').on('change', filterTable);

        $('#toggleActive').trigger('change');

        $(".select2-filter").each(function () {
            $(this).select2({
                width: '100%',
                dropdownParent: $(this).parent()
            });
        });

        $('#product_category').on('change', function() {
            var product_category_id = $(this).val();
            $.ajax({
                url: 'pages/product_ajax.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    product_category_id: product_category_id,
                    action: "fetch_product_fields"
                },
                success: function(response) {
                    if (response.length > 0) {
                        $('.opt_field').hide();

                        response.forEach(function(field) {
                            var fieldParts = field.fields.split(',');
                            fieldParts.forEach(function(part) {
                                $('.opt_field[data-id="' + part + '"]').show();
                            });
                        });
                    } else {
                        $('.opt_field').show();
                        console.log('No fields found for this category.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '.view_color_btn', function(event) {
            event.preventDefault(); 
            var id = $(this).data('id');
            var title = $(this).data('title');
            $('#modal_title').html(title +" Color");
            $.ajax({
                    url: 'pages/product_color_ajax.php',
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_edit_modal"
                    },
                    success: function(response) {
                        console.log(response);
                        $('#color_details_sec').html(response);
                        $(".select2-edit").each(function () {
                            $(this).select2({
                                width: '100%',
                                dropdownParent: $(this).parent()
                            });
                        });

                        $('#updateProductModal').modal('show');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert('Error: ' + textStatus + ' - ' + errorThrown);
                    }
            });
        });

        $(document).on('submit', '#colorForm', function(event) {
            event.preventDefault(); 

            var formData = new FormData(this);
            formData.append('action', 'add_update');

            $.ajax({
                url: 'pages/product_color_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#updateProductModal').modal('hide');
                    if (response.trim() === "success_update") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Product Color updated successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }else if (response.trim() === "success_add") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New Product Color added successfully.");
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

        function filterTable() {
            var category = $('#select-category').val()?.toString() || '';
            var multiplier = $('#select-color-multiplier').val()?.toString() || '';
            var grade = $('#select-grade').val()?.toString() || '';
            var gauge = $('#select-gauge').val()?.toString() || '';
            
            var surface = $('#select-surface').val()?.toString() || '';
            var coating = $('#select-coating').val()?.toString() || '';
            var textSearch = $('#text-srh').val().toLowerCase();
            var isActive = $('#toggleActive').is(':checked');

            console.log(surface)

            $.fn.dataTable.ext.search = [];

            if (textSearch) {
                $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                    var rowText = $(table.row(dataIndex).node()).text().toLowerCase();
                    return rowText.includes(textSearch);
                });
            }

            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                var row = $(table.row(dataIndex).node());
                if (category && category !== '' && category !== '/' && row.data('category').toString() !== category) {
                    return false;
                }
                if (multiplier && multiplier !== '' && multiplier !== '/' && row.data('multiplier').toString() !== multiplier) {
                    return false;
                }
                if (grade && grade !== '' && grade !== '/' && row.data('grade').toString() !== grade) {
                    return false;
                }
                if (gauge && gauge !== '' && gauge !== '/' && row.data('gauge').toString() !== gauge) {
                    return false;
                }
                if (surface && surface !== '' && surface !== '/' && row.data('surface').toString() !== surface) {
                    return false;
                }
                if (coating && coating !== '' && coating !== '/' && row.data('coating').toString() !== coating) {
                    return false;
                }

                return true;
            });

            table.draw();

            updateSelectedTags();
        }

        function updateSelectedTags() {
            const containerDiv = $('.filter_container');
            const sections = [];

            containerDiv.find('select').each(function () {
                const selectElement = $(this);
                const id = '#' + selectElement.attr('id');
                const title = selectElement.attr('title');

                if (id && title) {
                    sections.push({ id: id, title: title });
                }
            });

            const displayDiv = $('#selected-tags');
            displayDiv.empty();

            sections.forEach(function (section) {
                const selectedOption = $(section.id + ' option:selected');
                const selectedText = selectedOption.text().trim();

                if (selectedOption.val()) {
                    displayDiv.append(
                        '<div class="d-inline-block p-1 m-1 border rounded bg-light">' +
                            '<span class="text-dark">' + section.title + ': ' + selectedText + '</span>' +
                            '<button type="button" ' +
                                'class="btn-close btn-sm ms-1 remove-tag" ' +
                                'style="width: 0.75rem; height: 0.75rem;" ' +
                                'aria-label="Close" ' +
                                'data-tag="' + selectedText + '" ' +
                                'data-select="' + section.id + '">' +
                            '</button>' +
                        '</div>'
                    );
                }
            });

            $('.remove-tag').on('click', function () {
                const selectId = $(this).data('select');
                $(selectId).val('').trigger('change');
                $(this).parent().remove();
            });
        }

    });
</script>



