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
            <h4 class="font-weight-medium fs-14 mb-0"> Color Groups</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Color Groups</li>
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
            <button type="button" id="addProductModalLabel" class="btn btn-primary d-flex align-items-center view_color_btn" data-title="Add Color Group Multiplier" data-category="" data-id="0">
                <i class="ti ti-users text-white me-1 fs-5"></i> Add Color Group
            </button>
        </div>
        </div>
    </div>

    <div class="modal fade" id="colorGroupModal" tabindex="-1" role="dialog" aria-labelledby="colorGroupModal" aria-hidden="true">
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
                        <div class="card">
                            <div class="card-body">
                                <input type="hidden" id="form_id" name="id" class="form-control"/>

                                <div class="row pt-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Product Category</label>
                                        <div class="mb-3">
                                            <select id="product_category" class="form-control" name="product_category">
                                                <option value="">Select Category...</option>
                                                <?php
                                                $query_roles = "SELECT * FROM product_category WHERE hidden = '0' AND status = '1' ORDER BY `product_category` ASC";
                                                $result_roles = mysqli_query($conn, $query_roles);            
                                                while ($row_product_category = mysqli_fetch_array($result_roles)) {
                                                ?>
                                                    <option value="<?= $row_product_category['product_category_id'] ?>"
                                                            data-category="<?= $row_product_category['product_category'] ?>"
                                                            data-filename="<?= $row_product_category['color_group_filename'] ?>"
                                                    ><?= $row_product_category['product_category'] ?></option>
                                                <?php   
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div id="add-fields" class="row pt-3">
                                    
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
                    Filter Color Groups
                </h3>
                <div class="position-relative w-100 px-0 mr-0 mb-2">
                    <input type="text" class="form-control search-chat py-2 ps-5 fs-3 " id="text-srh" placeholder="Search Color Group">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="align-items-center filter_container">
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2-filter" id="select-category" title="Categories">
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
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2-filter" id="select-color-multiplier" title="Color Multipliers">
                            <option value="">All Color Multipliers</option>
                            <optgroup label="Product Color Multipliers">
                                <?php
                                $query_color_mult = "SELECT * FROM color_multiplier WHERE hidden = '0' AND status = '1'";
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
                        <select class="form-control search-chat py-0 ps-5 select2-filter" id="select-grade" title="Grade">
                            <option value="">All Grades</option>
                            <optgroup label="Product Grades">
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="position-relative w-100 px-1 mb-2">
                        <select class="form-control search-chat py-0 ps-5 select2-filter" id="select-gauge" title="Gauges">
                            <option value="">All Gauges</option>
                            <optgroup label="Product Gauges">
                                <?php
                                $query_gauge = "SELECT * FROM product_gauge WHERE hidden = '0' AND status = '1' ORDER BY `product_gauge` ASC";
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
                    Color Group List 
                </h3>
                <div id="selected-tags" class="mb-2"></div>
                <div class="datatables">
                    <div class="table-responsive">
                        <table id="colorList" class="table search-table align-middle text-wrap">
                            <thead class="header-item">
                            <th>Color</th>
                            <th>Product Category</th>
                            <th>System</th>
                            <th>Multiplier</th>
                            <th>Grade</th>
                            <th>Gauge</th>
                            <th>Action</th>
                            </thead>
                            <tbody>
                            <?php
                                $no = 1;
                                $query_prod_color = "SELECT pc.*, cgn.color_group_name AS color_name FROM product_color AS pc LEFT JOIN color_group_name AS cgn ON pc.color = cgn.color_group_name_id ORDER BY cgn.color_group_name ASC";
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
                                            <?= getColorGroupName($row_prod_color['color']) ?>
                                        </td>
                                        <td><?= getProductCategoryName($row_prod_color['product_category']) ?></td>
                                        <td><?= getProductSystemName($row_prod_color['product_system']) ?></td>
                                        <td><?= $row_prod_color['multiplier'] ?></td>
                                        <td><?= $row_prod_color['grade'] ?></td>
                                        <td><?= $row_prod_color['gauge'] ?></td>
                                        <td>
                                            <div class="action-btn text-center">
                                                <a href="#" class="text-primary view_color_btn" data-title="Update Color Group Multiplier" data-category="<?= $row_prod_color['product_category'] ?>" data-id="<?= $row_prod_color['id'] ?>">
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

<script src="includes/pricing_data.js"></script>

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
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        $(document).on('click', '.view_color_btn', function(event) {
            event.preventDefault();
            var title = $(this).data('title');
            $('#modal_title').html(title +" Color");

            var id = $(this).data('id') || '';
            $('#form_id').val(id);

            console.log($('#form_id').val())

            var product_category = $(this).data('category') || '';
            $('#product_category').val(product_category);

            updateSearchCategory();

            $('#colorGroupModal').modal('show');
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
                    $('#colorGroupModal').modal('hide');
                    if (response.trim() === "success_update") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Color Group updated successfully.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    }else if (response.trim() === "success_add") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("New Color Group added successfully.");
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

        $(document).on("change", ".calculate", function () {
            let selectedCategory = $('#product_category').val();
            // Get the selected text for each field
            let product_system = $('#product_system').find(":selected").text() || 0;
            let width = $('#width').find(":selected").text() || 0;
            let color = $('#color').find(":selected").text() || 0;
            let gauge = $('#gauge').find(":selected").text() || 0;

            console.log(color)

            // selectedCategory is declared globally
            if (String(selectedCategory) == '3') { // category 3 = Panel

                let multiplier = 0;
                for (let i = 0; i < pricing_data.length; i++) {
                    if (
                        pricing_data[i].color === color &&
                        pricing_data[i].gauge == gauge &&
                        pricing_data[i].system === product_system
                    ) {
                        multiplier = pricing_data[i].multiplier;
                        break;
                    }
                }

                if (gauge == "29") {
                    const colorOptions = ["Standard", "Premium", "Textured", "Metallic", "Woodgrain", "Embossed"];
                    if (colorOptions.includes(color)) {
                        let selectedPricingData = null;
                        for (let i = 0; i < pricing_data.length; i++) {
                            if (
                                pricing_data[i].color === color &&
                                pricing_data[i].gauge == gauge &&
                                pricing_data[i].system === product_system
                            ) {
                                selectedPricingData = pricing_data[i];
                                break;
                            }
                        }

                        if (selectedPricingData) {
                            let basePrice = selectedPricingData.multiplier;

                            let lowRibPrice = 0;
                            let acrylicPrice = 0;

                            for (let i = 0; i < pricing_data.length; i++) {
                                if (
                                    pricing_data[i].color === "Low-Rib" &&
                                    pricing_data[i].gauge == gauge &&
                                    pricing_data[i].system === product_system
                                ) {
                                    lowRibPrice = pricing_data[i].multiplier;
                                }

                                if (
                                    pricing_data[i].color === "Acrylic" &&
                                    pricing_data[i].gauge == gauge &&
                                    pricing_data[i].system === product_system
                                ) {
                                    acrylicPrice = pricing_data[i].multiplier;
                                }
                            }

                            if (lowRibPrice && acrylicPrice) {
                                multiplier = (1 + ((basePrice - lowRibPrice) / acrylicPrice)) * G7;
                                console.log("Calculated Multiplier: " + multiplier);
                            } else {
                                console.log("Required pricing data not found.");
                            }
                        } else {
                            console.log("Pricing data not found for selected options.");
                        }
                    }
                }


                $("#multiplier").val(multiplier.toFixed(3));
            }
        });

        $(document).on('change', '#product_category', function() {
            updateSearchCategory();
        });

        function updateSearchCategory() {
            var product_category = $('#product_category').val() || '';
            var id = $('#form_id').val() || '';
            var filename = $('#product_category option:selected').data('filename') || '';

            if(filename != ''){
                
                $.ajax({
                    url: 'pages/' +filename,
                    type: 'POST',
                    data: {
                        id: id,
                        action: "fetch_edit_modal"
                    },
                    success: function(response) {
                        $('#add-fields').html(response);

                        let selectedCategory = $('#product_category').val() || '';
                        //this hides select options that are not the selected category
                        $('.add-category option').each(function() {
                            let match = String($(this).data('category')) === String(product_category);
                            $(this).toggle(match);
                        });
                        
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('Error: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            }else{
                $('#add-fields').html('');
            }
        }

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



