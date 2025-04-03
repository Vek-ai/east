<?php
$supplier_name = '';
if(!empty($_REQUEST['id']) && !empty($_REQUEST['key'])){
  $supplier_order_id = $_REQUEST['id'];
  $key = $_REQUEST['key'];

  $query = "SELECT * FROM supplier_orders WHERE supplier_order_id = '$supplier_order_id' AND order_key = '$key'";
  $result = mysqli_query($conn, $query);      
  if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
  }    
  $supplier_id = $row['supplier_id'];
  $supplier_details = getSupplierDetails($supplier_id);
  $supplier_name = $supplier_details['supplier_name'];
  $status_code = $row['status'];
  $is_edited = $row['is_edited'];

  $status_labels = [
      1 => ['label' => 'New Order', 'class' => 'badge bg-primary'],
      2 => ['label' => 'Pending EKM Approval', 'class' => 'badge bg-warning text-dark'],
      3 => ['label' => 'Pending Supplier Approval', 'class' => 'badge bg-warning text-dark'],
      4 => ['label' => 'Approved, Waiting to Process', 'class' => 'badge bg-secondary'],
      5 => ['label' => 'Processing', 'class' => 'badge bg-success'],
      6 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
      7 => ['label' => 'Delivered', 'class' => 'badge bg-success']
  ];

  $status = $status_labels[$status_code];
}
?>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7 mt-4">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h4 class="font-weight-medium fs-14 mb-0"><?= $supplier_name ?></h4>
      </div>
    </div>
  </div>
</div>

<?php
if(!empty($supplier_order_id)){
?>
<div class="product-list">
  <div class="card">
    <div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center gap-3 mb-9">
        <div class="d-flex align-items-center">
          <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Product">
        </div>
        <h4 class="mb-0">
          STATUS: <span class="<?= $status['class']; ?> fw-bond fs-5"><?= $status['label']; ?></span>
        </h4>
      </div>
      <div class="table-responsive border rounded">
          <table class="table align-middle text-nowrap mb-0" id="productTable">
              <thead>
                  <tr>
                      <th scope="col">Products</th>
                      <th scope="col">Color</th>
                      <th scope="col">Quantity</th>
                      <th scope="col" class="text-right">Unit Price</th>
                      <th scope="col" class="text-right">Total Price</th>
                      <th scope="col" class="text-center">Actions</th>
                  </tr>
              </thead>
              <tbody></tbody>
          </table>
          <div class="d-flex align-items-center justify-content-end py-1">
              <p class="mb-0 fs-2">Rows per page:</p>
              <select id="rowsPerPage" class="form-select w-auto ms-2 me-4 py-1 pe-7 ps-2 border-0">
                  <option value="5" selected>5</option>
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
              </select>
              <nav>
                  <ul class="pagination justify-content-center mb-0 ms-4"></ul>
              </nav>
          </div>
      </div>
      <div class="d-flex justify-content-end align-items-center gap-3 p-3">
          <?php if ($status_code == 1): ?>
              <button type="button" id="resendBtn" class="btn btn-warning <?= $is_edited != 1 ? 'd-none' : '' ?>" data-id="<?=$supplier_order_id?>" data-action="submit_for_approval">Submit for Approval</button>
              <button type="button" id="AcceptBtn" class="btn btn-success" data-id="<?=$supplier_order_id?>" data-action="accept_order">Accept</button>
          <?php elseif ($status_code == 2): ?>
              
          <?php elseif ($status_code == 3): ?>
              <button type="button" id="resendBtn" class="btn btn-warning <?= $is_edited != 1 ? 'd-none' : '' ?>" data-id="<?=$supplier_order_id?>" data-action="submit_for_approval">Submit for Approval</button>
              <button type="button" id="AcceptBtn" class="btn btn-success" data-id="<?=$supplier_order_id?>" data-action="accept_order">Accept</button>
          <?php elseif ($status_code == 4): ?>
              <button type="button" id="processOrderBtn" class="btn btn-info" data-id="<?=$supplier_order_id?>" data-action="process_order">Process Order</button>
          <?php elseif ($status_code == 5): ?>
              <button type="button" id="shipOrderBtn" class="btn btn-primary" data-id="<?=$supplier_order_id?>" data-action="ship_order">Ship Order</button>
          <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProductForm">
                <div class="modal-body">
                    <input type="hidden" id="editId" name="id">

                    <h4 class="fw-bold" id="editProductName"></h4>

                    <div class="mb-3">
                        <label for="editProductQuantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="editProductQuantity" name="quantity" required>
                    </div>

                    <div class="mb-3">
                        <label for="editProductPrice" class="form-label">Unit Price</label>
                        <input type="text" class="form-control" id="editProductPrice" name="price" required>
                    </div>

                    <div>
                      <label for="editProductColor" class="form-label">Color</label>
                      <div class="mb-3">
                          <select class="form-select select2" id="editProductColor" name="color">
                              <option value="">Select Color...</option>
                              <?php
                              $query_roles = "SELECT * FROM supplier_color WHERE status = '1' AND hidden = '0' AND supplierid = '$supplier_id' ORDER BY `color` ASC";
                              $result_roles = mysqli_query($conn, $query_roles);            
                              while ($row_color = mysqli_fetch_array($result_roles)) {
                              ?>
                                  <option value="<?= $row_color['color_id'] ?>" data-color="<?= $row_color['color_hex'] ?>"><?= $row_color['color'] ?></option>
                              <?php   
                              }
                              ?>
                          </select>
                      </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
function formatOption(state) {
  if (!state.id) {
      return state.text;
  }
  var color = $(state.element).data('color');
  var $state = $(
      '<span class="d-flex align-items-center small">' +
          '<span class="rounded-circle d-block p-1 me-2" style="background-color:' + color + '; width: 16px; height: 16px;"></span>' +
          state.text + 
      '</span>'
  );
  return $state;
}
$(document).ready(function () {
    function loadProducts(page = 1, limit = 5) {
        var search = $("#text-srh").val();
        $.ajax({
            url: 'pages/home_ajax.php',
            type: 'GET',
            data: {
                supplier_order_id: '<?= $supplier_order_id ?>',
                search: search,
                page: page,
                limit: limit,
                action: 'load_order_prod'
            },
            dataType: 'json',
            success: function (response) {
                console.log(response);
                let tbody = $("#productTable tbody");
                let tfoot = $("#productTable tfoot");
                tbody.empty();
                tfoot.remove();

                if (response.data.length > 0) {
                    let totalQuantity = 0;
                    let totalAmount = 0.00;

                    $.each(response.data, function (index, item) {
                        totalQuantity += parseInt(item.quantity);
                        totalAmount += parseFloat(item.total_price.replace(/,/g, ''));

                        tbody.append(`
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center ps-2">
                                        <img src="${item.image}" class="rounded-circle" alt="product-img" width="56" height="56">
                                        <div class="ms-3">
                                            <h6 class="fw-semibold mb-0 fs-4">${item.product_name}</h6>
                                            <p class="mb-0">${item.category}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-flex align-items-center small">
                                        <span class="rounded-circle d-block p-1 me-2" 
                                              style="background-color: ${item.color_hex}; width: 25px; height: 25px;">
                                        </span>
                                        ${item.color_name}
                                    </span>
                                </td>
                                <td><h6 class="mb-0 fs-4">${item.quantity}</h6></td>
                                <td class="text-right"><h6 class="mb-0 fs-4">${parseFloat(item.price).toFixed(2)}</h6></td>
                                <td class="text-right"><h6 class="mb-0 fs-4">${item.total_price}</h6></td>
                                <td class="text-center">
                                    <a class="fs-6 text-muted btn-edit" href="javascript:void(0)" 
                                      data-id="${item.id}" 
                                      data-name="${item.product_name}" 
                                      data-quantity="${item.quantity}"
                                      data-price="${item.price}" 
                                      data-color="${item.color}">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    });

                    $("#productTable").append(`
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td colspan="2" class="text-end">Total:</td>
                                <td><h6 class="mb-0 fs-4">${totalQuantity}</h6></td>
                                <td></td>
                                <td class="text-right"><h6 class="mb-0 fs-4">${totalAmount.toFixed(2)}</h6></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    `);

                    updatePagination(response.current_page, response.total_pages);
                } else {
                    tbody.append('<tr><td colspan="6" class="text-center">No products found.</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                console.log("XHR Object:", xhr);
                console.log("Response Text:", xhr.responseText);
                alert("An error occurred while fetching products.");
            }
        });
    }

    function updatePagination(currentPage, totalPages) {
        let pagination = $(".pagination");
        pagination.empty();

        if (totalPages > 1) {
            pagination.append(`
                <li class="page-item p-1 ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" data-page="${currentPage - 1}">
                        <i class="ti ti-chevron-left"></i>
                    </a>
                </li>
            `);

            for (let i = 1; i <= totalPages; i++) {
                pagination.append(`
                    <li class="page-item p-1 ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0)" data-page="${i}">${i}</a>
                    </li>
                `);
            }

            pagination.append(`
                <li class="page-item p-1 ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" data-page="${currentPage + 1}">
                        <i class="ti ti-chevron-right"></i>
                    </a>
                </li>
            `);
        }
    }

    $(document).on("input", "#text-srh", function () {
        loadProducts();
    });

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            dropdownParent: $(this).parent(),
            templateResult: formatOption,
            templateSelection: formatOption
        });
    });

    $(document).on("click", ".pagination .page-link", function () {
        let page = $(this).data("page");
        if (page) {
            loadProducts(page);
        }
    });

    $(document).on("change", "#rowsPerPage", function () {
        loadProducts(1, $(this).val());
    });

    loadProducts();

    $(document).on("click", ".btn-edit", function () {
        let Id = $(this).data("id");
        let productName = $(this).data("name");
        let productQuantity = $(this).data("quantity");
        let productPrice = $(this).data("price");
        let productColor = $(this).data("color");

        $("#editId").val(Id);
        $("#editProductName").val(productName);
        $("#editProductQuantity").val(productQuantity);
        $("#editProductPrice").val(productPrice);

        $("#editProductColor").val(productColor).trigger("change");

        $("#editProductModal").modal("show");
    });

    $("#editProductForm").on("submit", function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        formData.append("action", "update_product");

        $.ajax({
            url: 'pages/home_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
              console.log(response);
                if (response.success) {
                    alert("Product updated successfully!");
                    $("#editProductModal").modal("hide");
                    loadProducts();
                } else {
                    alert("Error updating product.");
                }
            },
            error: function(xhr, status, error) {
                console.log("AJAX Error:", xhr.responseText);
            }
        });
    });

    $(document).on("click", "#resendBtn, #AcceptBtn, #processOrderBtn, #shipOrderBtn", function () {
        var dataId = $(this).data("id");
        var action = $(this).data("action");

        var confirmMessage = action.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });

        if (confirm("Are you sure you want to " + confirmMessage + "?")) {
            $.ajax({
                url: 'pages/home_ajax.php',
                type: 'POST',
                data: {
                    id: dataId,
                    method: action,
                    action: 'update_status'
                },
                success: function (response) {
                    console.log(response);
                    try {
                        var jsonResponse = JSON.parse(response);  
                    } catch (e) {
                        var jsonResponse = response;
                    }

                    if (jsonResponse.success) {
                        alert("Status updated successfully!");
                        location.reload();
                    } else {
                        alert("Update Success, but email failed to send");
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    console.log("AJAX Error:", xhr.responseText);
                }
            });
        }
    });

});
</script>

<?php
}
?>