<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Staging Bin";
?>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Staging Bin</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="?page=">Home
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Staging Bin</li>
            </ol>
            </nav>
        </div>
        </div>
    </div>
    </div>

    <div class="widget-content searchable-container list">

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
                <button type="button" class="btn btn-primary waves-effect text-start" data-bs-dismiss="modal">
                Close
                </button>
            </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="transfer-modal" tabindex="-1" aria-labelledby="vertical-center-modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <form id="inventoryTransferForm">
                    <div class="card mb-0">
                        <div class="card-body pb-2">
                            <div class="modal-header align-items-center modal-colored-header">
                                <h4 class="m-0">Transfer to Inventory</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="transfer_product_id" name="id" value="">

                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label">Warehouse</label>
                                        <div class="mb-3">
                                            <select id="inventory_warehouse" class="select2-update form-control" name="Warehouse_id">
                                                <option value="">Select Warehouse...</option>
                                                <?php
                                                $query_warehouse = "SELECT * FROM warehouses WHERE status = '1'";
                                                $result_warehouse = mysqli_query($conn, $query_warehouse);            
                                                while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                                                ?>
                                                    <option value="<?= $row_warehouse['WarehouseID'] ?>">
                                                        <?= $row_warehouse['WarehouseName'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Shelf</label>
                                        <div class="mb-3">
                                            <select id="inventory_shelf" class="form-control select2-update" name="Shelves_id">
                                                <option value="">Select Shelf...</option>
                                                <?php
                                                $query_shelf = "SELECT * FROM shelves";
                                                $result_shelf = mysqli_query($conn, $query_shelf);            
                                                while ($row_shelf = mysqli_fetch_array($result_shelf)) {
                                                ?>
                                                    <option value="<?= $row_shelf['ShelfID'] ?>">
                                                        <?= $row_shelf['ShelfCode'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Bin</label>
                                        <div class="mb-3">
                                            <select id="inventory_bin" class="form-control select2-update" name="Bin_id">
                                                <option value="">Select Bin...</option>
                                                <?php
                                                $query_bin = "SELECT * FROM bins";
                                                $result_bin = mysqli_query($conn, $query_bin);            
                                                while ($row_bin = mysqli_fetch_array($result_bin)) {
                                                ?>
                                                    <option value="<?= $row_bin['BinID'] ?>">
                                                        <?= $row_bin['BinCode'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Row</label>
                                        <div class="mb-3">
                                            <select id="inventory_row" class="form-control select2-update" name="Row_id">
                                                <option value="">Select Row...</option>
                                                <?php
                                                $query_rows = "SELECT * FROM warehouse_rows";
                                                $result_rows = mysqli_query($conn, $query_rows);            
                                                while ($row_rows = mysqli_fetch_array($result_rows)) {
                                                ?>
                                                    <option value="<?= $row_rows['WarehouseRowID'] ?>">
                                                        <?= $row_rows['WarehouseRowID'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row pt-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Date</label>
                                        <input type="date" id="date" name="Date" class="form-control" value="<?= date('Y-m-d') ?>" />
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-sign-in me-1"></i> Transfer 
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="card card-body">
        <div class="row">
            <div class="col-3">
                <h3 class="card-title align-items-center mb-2">
                    Filter <?= $page_title ?>
                </h3>
                <div class="position-relative w-100 px-0 mr-0 mb-2">
                    <input type="text" class="form-control py-2 ps-5 " id="text-srh" placeholder="Search">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                </div>
                <div class="px-3 mb-2"> 
                    <input type="checkbox" id="toggleActive" checked> Show Active Only
                </div>
            </div>
            <div class="col-9">
                <div id="selected-tags" class="mb-2"></div>
                <div class="datatables">
                    <div class="product-details table-responsive text-nowrap">
                        <table id="staging_bin_tbl" class="table table-hover mb-0 text-md-nowrap">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $no = 1;
                                    $query_product = "
                                        SELECT
                                            *,
                                            sb.status as staging_status
                                        FROM
                                            staging_bin AS sb
                                        LEFT JOIN product AS p
                                        ON
                                            sb.product_id = p.product_id
                                        WHERE
                                            1
                                    ";

                                    $result_product = mysqli_query($conn, $query_product);            
                                    while ($row_product = mysqli_fetch_array($result_product)) {
                                        $product_id = $row_product['product_id'];

                                        $quantity = $row_product['quantity'];
                                        $date = date('F j, Y \a\t g:i A', strtotime($row_product['date']));

                                        if(!empty($row_product['main_image'])){
                                            $picture_path = $row_product['main_image'];
                                        }else{
                                            $picture_path = "images/product/product.jpg";
                                        }

                                        if ($row_product['staging_status'] == '1') {
                                            $status = "<a href='#'><div class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Transferred</div></a>";
                                        } else {
                                            $status = "<a href='#'><div class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>New</div></a>";
                                        }
                    
                                    ?>
                                        <tr class="search-items" 
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
                                            <td class="text-center align-middle">
                                                <?= $quantity ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <?= $date ?>
                                            </td>
                                            <td><?= $status ?></td>
                                            <td>
                                                <div class="action-btn text-center">
                                                    <a href="javascript:void(0);" 
                                                        class="transfer_to_inventory text-primary me-2" 
                                                        data-id="<?= $row_product['id'] ?? '' ?>" 
                                                        title="Transfer to Inventory">
                                                            <i class="fas fa-warehouse"></i>
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
        document.title = "<?= $page_title ?>";

        var table = $('#staging_bin_tbl').DataTable({
            pageLength: 100
        });

        $('#display_supplier_type_filter').hide();

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var status = $(table.row(dataIndex).node()).find('a .alert').text().trim();
            var isActive = $('#toggleActive').is(':checked');

            if (!isActive || status === 'New') {
                return true;
            }
            return false;
        });

        $('#toggleActive').on('change', function() {
            table.draw();
        });

        $('#toggleActive').trigger('change');

        $(document).on('click', '.transfer_to_inventory', function () {
            var productId = $(this).data('id');
            $('#transfer_product_id').val(productId);

            $('#transfer-modal').modal('show');
        });

        $('#date').on('focus', function () {
            this.showPicker && this.showPicker();
        });

        $(document).on('submit', '#inventoryTransferForm', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            formData.append('action', 'add_inventory');

            $.ajax({
                url: 'pages/staging_bin_ajax.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('.modal').modal('hide');
                    if (response.trim() === "success") {
                        $('#responseHeader').text("Success");
                        $('#responseMsg').text("Successfully trasferred to inventory.");
                        $('#responseHeaderContainer').removeClass("bg-danger");
                        $('#responseHeaderContainer').addClass("bg-success");
                        $('#response-modal').modal("show");
                        $('#response-modal').on('hide.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        $('#responseHeader').text("Failed!");
                        $('#responseMsg').text("Failed to trasferred inventory.");
                        $('#responseHeaderContainer').removeClass("bg-success");
                        $('#responseHeaderContainer').addClass("bg-danger");
                        $('#response-modal').modal("show");

                        console.log(response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
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
                    return $(table.row(dataIndex).node()).find('a .alert').text().trim() === 'New';
                });
            }

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var row = $(table.row(dataIndex).node());
                var match = true;

                $('.filter-selection').each(function() {
                    var filterValue = $(this).val()?.toString() || '';
                    var rowValue = row.data($(this).data('filter'))?.toString() || '';

                    if (filterValue && filterValue !== '/' && rowValue !== filterValue) {
                        match = false;
                        return false;
                    }
                });

                return match;
            });

            table.draw();
            updateSelectedTags();
        }

        function updateSearchCategory() {
            let selectedCategory = $('#select-category option:selected').data('category');
            let hasCategory = !!selectedCategory;

            $('.search-category').each(function () {
                let $select2Element = $(this);

                if (!$select2Element.data('all-options')) {
                    $select2Element.data('all-options', $select2Element.find('option').clone(true));
                }

                let allOptions = $select2Element.data('all-options');

                $select2Element.empty();

                if (hasCategory) {
                    allOptions.each(function () {
                        let optionCategory = $(this).data('category');
                        if (String(optionCategory) === String(selectedCategory)) {
                            $select2Element.append($(this).clone(true));
                        }
                    });
                } else {
                    allOptions.each(function () {
                        $select2Element.append($(this).clone(true));
                    });
                }

                $select2Element.select2('destroy');

                let parentContainer = $select2Element.parent();
                $select2Element.select2({
                    width: '100%',
                    dropdownParent: parentContainer
                });
            });

            $('.category_selection').toggleClass('d-none', !hasCategory);
        }

        function updateSelectedTags() {
            var displayDiv = $('#selected-tags');
            displayDiv.empty();

            $('.filter-selection').each(function() {
                var selectedOption = $(this).find('option:selected');
                var selectedText = selectedOption.text().trim();
                var filterName = $(this).data('filter-name'); // Custom attribute for display

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

        $(document).on('input change', '#text-srh, #toggleActive, .filter-selection', filterTable);
        
        filterTable();

    });
</script>