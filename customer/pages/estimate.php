<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

$customer_name = '';
if(!empty($_REQUEST['id']) && !empty($_REQUEST['key'])){
  $estimateid = $_REQUEST['id'];
  $key = $_REQUEST['key'];

  $query = "SELECT * FROM estimates WHERE estimateid = '$estimateid' AND est_key = '$key'";
  $result = mysqli_query($conn, $query);      
  if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
  }    
  $customerid = $row['customerid'];
  $customer_details = getCustomerDetails($customerid);
  $customer_name = $customer_details['customer_first_name'] . ' ' .$customer_details['customer_last_name'];
  $status_code = $row['status'];
  $is_edited = $row['is_edited'];

  $status_labels = [
      1 => ['label' => 'New Estimate', 'class' => 'badge bg-primary'],
      2 => ['label' => 'Sent to Customer', 'class' => 'badge bg-success text-dark'],
      3 => ['label' => 'Modified by Customer', 'class' => 'badge bg-warning text-dark'],
      4 => ['label' => 'Approved', 'class' => 'badge bg-secondary'],
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
        <h4 class="font-weight-medium fs-14 mb-0"><?= $customer_name ?></h4>
      </div>
    </div>
  </div>
</div>

<?php
if(!empty($estimateid) && !empty($_REQUEST['key'])){
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
          <table class="table align-middle text-nowrap mb-0" id="estTable">
              <thead>
                  <tr>
                      <th scope="col">Products</th>
                      <th scope="col">Color</th>
                      <th scope="col">Quantity</th>
                      <th scope="col">Status</th>
                      <th scope="col" class="text-right">Unit Price</th>
                      <th scope="col" class="text-right">Disc Price</th>
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
              
          <?php elseif ($status_code == 2): ?>
            <button type="button" id="resendBtn" class="btn btn-warning <?= $is_edited != 1 ? 'd-none' : '' ?>" data-id="<?=$estimateid?>" data-action="submit_for_approval">Submit for Approval</button>
            <button type="button" id="AcceptBtn" class="btn btn-success" data-id="<?=$estimateid?>" data-action="accept_estimate">Accept</button>
          <?php elseif ($status_code == 3): ?>
              
          <?php elseif ($status_code == 4): ?>
              
          <?php elseif ($status_code == 5): ?>
              
          <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editEstimateModal" tabindex="-1" aria-labelledby="editEstimateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEstimateModalLabel">Edit Estimate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEstimateForm">
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
                                $query_paint_colors = "SELECT * FROM paint_colors 
                                                    WHERE hidden = '0' AND color_status = '1' 
                                                    GROUP BY color_name 
                                                    ORDER BY color_name ASC";

                                $result_paint_colors = mysqli_query($conn, $query_paint_colors);            

                                while ($row_paint_colors = mysqli_fetch_array($result_paint_colors)) {
                                ?>
                                    <option value="<?= $row_paint_colors['color_id'] ?>" 
                                            data-color="<?= getColorHexFromColorID($row_paint_colors['color_id']) ?>">
                                        <?= $row_paint_colors['color_name'] ?>
                                    </option>
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
    function loadEstimates(page = 1, limit = 5) {
        var search = $("#text-srh").val();
        $.ajax({
            url: 'pages/estimate_ajax.php',
            type: 'GET',
            data: {
                estimateid: '<?= $estimateid ?>',
                search: search,
                page: page,
                limit: limit,
                action: 'load_est_prod'
            },
            dataType: 'json',
            success: function (response) {
                let tbody = $("#estTable tbody");
                let tfoot = $("#estTable tfoot");
                tbody.empty();
                tfoot.remove();

                if (response.data.length > 0) {
                    let totalQuantity = 0;
                    let totalAmount = 0.00;
                    let totalDiscAmount = 0.00;

                    $.each(response.data, function (index, item) {
                        var quantity = parseInt(item.quantity, 10);
                        var totalDiscPrice = parseFloat(item.total_disc_price.replace(/,/g, ''));
                        var discountedPrice = parseFloat(item.discounted_price.replace(/,/g, ''));

                        totalQuantity += quantity;
                        totalDiscAmount += totalDiscPrice;

                        var prodDiscTtlAmt = quantity * discountedPrice;

                        const statusProdLabels = {
                            0: { label: 'New', class: 'badge bg-primary' },
                            1: { label: 'Processing', class: 'badge bg-success' },
                            2: { label: 'Waiting for Dispatch', class: 'badge bg-warning' },
                            3: { label: 'In Transit', class: 'badge bg-secondary' },
                            4: { label: 'Delivered', class: 'badge bg-success' }
                        };

                        const status = statusProdLabels[item.status] || { label: 'Unknown', class: 'badge bg-dark' };

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
                                <td><span class="${status.class} fw-bold">${status.label}</span></td>
                                <td class="text-right"><h6 class="mb-0 fs-4">$ ${parseFloat(item.discounted_price).toFixed(2)}</h6></td>
                                <td class="text-right"><h6 class="mb-0 fs-4">$ ${parseFloat(prodDiscTtlAmt).toFixed(2)}</h6></td>
                                <td class="text-center">
                                    <a class="fs-6 text-muted btn-edit" href="javascript:void(0)" 
                                      data-id="${item.id}" 
                                      data-name="${item.product_name}" 
                                      data-quantity="${item.quantity}"
                                      data-price="${item.discounted_price}" 
                                      data-color="${item.color}">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    });

                    $("#estTable").append(`
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td colspan="2" class="text-end">Total:</td>
                                <td><h6 class="mb-0 fs-4">${totalQuantity}</h6></td>
                                <td></td>
                                <td class="text-right"><h6 class="mb-0 fs-4">$ ${totalDiscAmount.toFixed(2)}</h6></td>
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
        loadEstimates();
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
            loadEstimates(page);
        }
    });

    $(document).on("change", "#rowsPerPage", function () {
        loadEstimates(1, $(this).val());
    });

    loadEstimates();

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

        $("#editEstimateModal").modal("show");
    });

    $("#editEstimateForm").on("submit", function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        formData.append("action", "update_estimate");

        $.ajax({
            url: 'pages/estimate_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
              console.log(response);
                if (response.success) {
                    alert("Estimate updated successfully!");
                    $("#editEstimateModal").modal("hide");
                    loadEstimates();
                    location.reload();
                } else {
                    alert("Error updating product.");
                    location.reload();
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
                url: 'pages/estimate_ajax.php',
                type: 'POST',
                data: {
                    id: dataId,
                    method: action,
                    action: 'update_status'
                },
                success: function (response) {
                    console.log("Raw Response:", response);
                    try {
                        var jsonResponse = (typeof response === "string") ? JSON.parse(response) : response;
                    } catch (e) {
                        var jsonResponse = { success: false, message: "Invalid JSON response" };
                    }

                    if (jsonResponse?.success === true) {
                        alert("Status updated successfully!");
                        location.reload();
                    } else {
                        alert(jsonResponse?.message || "An unknown error occurred.");
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