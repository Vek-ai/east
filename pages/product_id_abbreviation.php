<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$permission = $_SESSION['permission'];

$page_title = "Product IDs";
?>
<style>
    td.notes,  td.last-edit{
        white-space: normal;
        word-wrap: break-word;
    }
    .emphasize-strike {
        text-decoration: type-through;
        font-weight: bold;
        color: #9a841c;
    }
    .dataTables_filter input {
        width: 100%;
        height: 50px;
        font-size: 16px;
        padding: 10px;
        border-radius: 5px;
    }
    .dataTables_filter {  width: 100%;}
    #toggleActive {
        margin-bottom: 10px;
    }

    .inactive-row {
        display: none;
    }
</style>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div><br>
        <h4 class="font-weight-medium fs-14 mb-0"><?= $page_title ?></h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Product Properties
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page"><?= $page_title ?></li>
          </ol>
        </nav>
      </div>
      <div>
        <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
          
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card card-body">
    <div class="row">
      <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
            <button class="btn btn-primary open-product-filter">
                Search Product ID
            </button>
      </div>
    </div>
</div>

<div class="modal fade" id="productFilterModal" tabindex="-1" aria-labelledby="productFilterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productFilterModalLabel">Product ID Abbreviation Builder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">

          <!-- Category -->
          <div class="col-md-6 mb-3">
            <label class="form-label">Product Category</label>
            <div class="">
                <select class="form-control select2" id="filter-category">
                    <option value="">All Categories</option>
                    <optgroup label="Category">
                        <?php
                        $query = "SELECT * FROM product_category WHERE hidden = 0 AND status = 1 ORDER BY product_category ASC";
                        $result = mysqli_query($conn, $query);
                        while ($r = mysqli_fetch_assoc($result)) {
                            echo "<option value='{$r['product_category_id']}'>{$r['product_category']}</option>";
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
            
          </div>

          <!-- Profile -->
          <div class="col-md-6 mb-3">
            <label class="form-label">Product Profile</label>
            <div class="">
                <select class="form-control select2" id="filter-profile">
                    <option value="">All Profile Types</option>
                    <optgroup label="Profile">
                        <?php
                        $query = "SELECT * FROM profile_type WHERE hidden = 0 AND status = 1 ORDER BY profile_type ASC";
                        $result = mysqli_query($conn, $query);
                        while ($r = mysqli_fetch_assoc($result)) {
                            echo "<option value='{$r['profile_type_id']}'>{$r['profile_type']}</option>";
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
          </div>

          <!-- Grade -->
          <div class="col-md-6 mb-3">
            <label class="form-label">Product Grade</label>
            <div class="">
                <select class="form-control select2" id="filter-grade">
                    <option value="">All Grades</option>
                    <optgroup label="Grade">
                        <?php
                        $query = "SELECT * FROM product_grade WHERE hidden = 0 AND status = 1 ORDER BY product_grade ASC";
                        $result = mysqli_query($conn, $query);
                        while ($r = mysqli_fetch_assoc($result)) {
                            echo "<option value='{$r['product_grade_id']}'>{$r['product_grade']}</option>";
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
          </div>

          <!-- Gauge -->
          <div class="col-md-6 mb-3">
            <label class="form-label">Product Gauge</label>
            <div class="">
                <select class="form-control select2" id="filter-gauge">
                    <option value="">All Gauges</option>
                    <optgroup label="Gauge">
                        <?php
                        $query = "SELECT * FROM product_gauge WHERE hidden = 0 AND status = 1 ORDER BY product_gauge ASC";
                        $result = mysqli_query($conn, $query);
                        while ($r = mysqli_fetch_assoc($result)) {
                            echo "<option value='{$r['product_gauge_id']}'>{$r['product_gauge']}</option>";
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
          </div>

          <!-- Type -->
          <div class="col-md-6 mb-3">
            <label class="form-label">Product Type</label>
            <div class="">
                <select class="form-control select2" id="filter-type">
                    <option value="">All Types</option>
                    <optgroup label="Type">
                        <?php
                        $query = "SELECT * FROM product_type WHERE hidden = 0 AND status = 1 ORDER BY product_type ASC";
                        $result = mysqli_query($conn, $query);
                        while ($r = mysqli_fetch_assoc($result)) {
                            echo "<option value='{$r['product_type_id']}'>{$r['product_type']}</option>";
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
          </div>

          <!-- Length -->
          <div class="col-md-6 mb-3">
            <label class="form-label">Length</label>
            <div class="">
                <select class="form-control select2" id="filter-length">
                    <option value="">All Lengths</option>
                    <optgroup label="Dimensions">
                        <?php
                        $query = "SELECT * FROM dimensions ORDER BY dimension ASC";
                        $result = mysqli_query($conn, $query);
                        while ($r = mysqli_fetch_assoc($result)) {
                            $label = $r['dimension'] . ' ' . $r['dimension_unit'];
                            echo "<option value='{$r['dimension_id']}'>{$label}</option>";
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
          </div>

          <!-- Color -->
          <div class="col-md-12 mb-3">
            <label class="form-label">Color</label>
            <div class="">
                <select class="form-control select2" id="filter-color">
                    <option value="">All Colors</option>
                    <optgroup label="Colors">
                        <?php
                        $query = "SELECT * FROM paint_colors WHERE hidden = 0 AND color_status = 1 ORDER BY color_name ASC";
                        $result = mysqli_query($conn, $query);
                        while ($r = mysqli_fetch_assoc($result)) {
                            echo "<option value='{$r['color_id']}'>{$r['color_name']}</option>";
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
          </div>
        </div>

        <div class="text-center mt-3">
          <h4 id="abbr-display" class="fw-bold text-primary"></h4>
        </div>

        <div id="product-results" class="mt-4"></div>
      </div>
    </div>
  </div>
</div>

<div class="card card-body">
  <div class="row">
      <div class="col-3">
            <div class="d-flex justify-content-start align-items-center mb-2">
                <h3 class="card-title mb-0">Filter <?= $page_title ?></h3>
            </div>
            <div class="position-relative w-100 px-0 mr-0 mb-2">
                <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search">
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
                                <option value="<?= $row_category['product_category_id'] ?>" data-category="<?= $row_category['product_category'] ?>"><?= $row_category['product_category'] ?></option>
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
                                <option value="<?= $row_profile['profile_type_id'] ?>" data-category="<?= $v['product_category'] ?>"><?= $row_profile['profile_type'] ?></option>
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
                                <option value="<?= $row_grade['product_grade_id'] ?>" data-category="grade"><?= $row_grade['product_grade'] ?></option>
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
                                <option value="<?= $row_gauge['product_gauge_id'] ?>" data-category="gauge"><?= $row_gauge['product_gauge'] ?></option>
                            <?php
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
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
                            <option value="<?= $row_type['product_type_id'] ?>" data-category="<?= $row_type['product_category'] ?>"><?= $row_type['product_type'] ?></option>
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
                            <option value="<?= $row_color['color_id'] ?>" data-category="category"><?= $row_color['color_name'] ?></option>
                        <?php
                        }
                        ?>
                    </optgroup>
                </select>
            </div>
            <div class="px-3 mb-2"> 
                <input type="checkbox" id="show_history"> Show History
            </div>
            <div class="d-flex justify-content-end px-3 mb-2">
                <div class="py-2">
                    <button type="button" class="btn btn-outline-primary reset_filters">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </button>
                </div>
            </div>
      </div>
      <div class="col-9">
        <div id="selected-tags" class="mb-2"></div>
          <div class="datatables">
            <div class="card">
              <div class="card-body">
                <h4 class="card-title d-flex justify-content-between align-items-center"><?= $page_title ?> List</h4>
                <div class="table-responsive">
                  <table id="display_product_id" class="table table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Category</th>
                            <th>Profile</th>
                            <th>Grade</th>
                            <th>Gauge</th>
                            <th>Type</th>
                            <th>Color</th>
                            <th>Length</th>
                        </tr>
                    </thead>

                    <tbody>
                      
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
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

<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="add-header">
                    Add
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="dimensionForm" class="form-horizontal">
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

<script>
  $(document).ready(function() {
    document.title = "Product Type";

    $.fn.dataTable.ext.errMode = 'none';

    var table = $('#display_product_id').DataTable({
        pageLength: 10,
        order: [],
        ajax: {
            url: 'pages/product_id_abbreviation_ajax.php',
            type: 'POST',
            data: { action: 'fetch_table' },
            error: function(xhr, status, error) {
                alert('Failed');
                console.log('DataTables AJAX error:', status, error);
                console.log('Response text:', xhr.responseText);
            }
        },
        columns: [
            { data: 'product_id', title: 'Product ID' },
            { data: 'category', title: 'Category' },
            { data: 'profile', title: 'Profile' },
            { data: 'grade', title: 'Grade' },
            { data: 'gauge', title: 'Gauge' },
            { data: 'type', title: 'Type' },
            { data: 'color', title: 'Color' },
            { data: 'length', title: 'Length' }
        ],
        createdRow: function (row, data, dataIndex) {
            $(row).attr('data-category', data.category_id);
            $(row).attr('data-profile', data.profile_id);
            $(row).attr('data-grade', data.grade_id);
            $(row).attr('data-gauge', data.gauge_id);
            $(row).attr('data-type', data.type_id);
            $(row).attr('data-color', data.color_id);
            $(row).attr('data-length', data.length_id);
        }
    });

    $('#display_product_id_filter').hide();

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            dropdownParent: $(this).parent()
        });
    });

    $(document).on('click', '.open-product-filter', function (e) {
        e.preventDefault();

        $('#productFilterModal select').val('').trigger('change');
        $('#abbr-display').text('');

        $('#productFilterModal').modal('show');
    });

    $('#filter-category, #filter-profile, #filter-grade, #filter-gauge, #filter-type, #filter-length, #filter-color').on('change', function() {
        const category = $('#filter-category').val();
        const profile  = $('#filter-profile').val();
        const grade    = $('#filter-grade').val();
        const gauge    = $('#filter-gauge').val();
        const type     = $('#filter-type').val();
        const length   = $('#filter-length').val();
        const color    = $('#filter-color').val();

        $.ajax({
            url: 'pages/product_id_abbreviation_ajax.php',
            method: 'POST',
            data: {
                action: 'fetch_product_id',
                category: category,
                profile: profile,
                grade: grade,
                gauge: gauge,
                type: type,
                length: length,
                color: color
            },
            success: function(response) {
                $('#abbr-display').text(response);
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                $('#abbr-display').text('Error: ' + xhr.responseText);
            }
        });
    });

    function filterTable() {
        var textSearch = $('#text-srh').val().toLowerCase();
        var showHistory = $('#show_history').is(':checked');

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
                var filterValue = $(this).val()?.toString().toLowerCase() || '';
                var rowValue = row.data($(this).data('filter'))?.toString().toLowerCase() || '';

                if (filterValue && filterValue !== '/') {
                    if (!rowValue.includes(filterValue)) {
                        match = false;
                        return false;
                    }
                }
            });

            return match;
        });

        if (!showHistory) {
            var seenCombos = new Set();

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var row = $(table.row(dataIndex).node());

                var comboKey = [
                    row.data('profile'),
                    row.data('grade'),
                    row.data('gauge'),
                    row.data('type'),
                    row.data('color'),
                    row.data('length')
                ].join('-');

                if (seenCombos.has(comboKey)) {
                    return false;
                } else {
                    seenCombos.add(comboKey);
                    return true;
                }
            });
        }

        table.draw();
        updateSelectedTags();
    }


    function updateSelectedTags() {
        var displayDiv = $('#selected-tags');
        displayDiv.empty();

        $('.filter-selection').each(function() {
            var selectedOption = $(this).find('option:selected');
            var selectedText = selectedOption.text().trim();
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

    $(document).on('input change', '#text-srh, #show_history, .filter-selection', filterTable);

    $(document).on('click', '.reset_filters', function () {
        $('.filter-selection').each(function () {
            $(this).val(null).trigger('change.select2');
        });

        $('#text-srh').val('');

        filterTable();
    });

    filterTable();
    
});
</script>