<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);

$customer_name = '';
if(!empty($_REQUEST['id']) && !empty($_REQUEST['key'])){
    $orderid = $_REQUEST['id'];
    $key = $_REQUEST['key'];

    $query = "SELECT * FROM orders WHERE orderid = '$orderid' AND order_key = '$key'";
    $result = mysqli_query($conn, $query);      
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
    }    
    $customerid = $row['customerid'];
    $customer_details = getCustomerDetails($customerid);
    $customer_name = $customer_details['customer_first_name'] . ' ' .$customer_details['customer_last_name'];
    $status_code = $row['status'];
    $is_edited = $row['is_edited'];
    $tracking_number = $row['tracking_number'];
    $shipping_comp_details = getShippingCompanyDetails($row['shipping_company']);
    $shipping_company = $shipping_comp_details['shipping_company'];

    $status_labels = [
        1 => ['label' => 'New Order', 'class' => 'badge bg-primary'],
        2 => ['label' => 'Processing', 'class' => 'badge bg-warning'],
        3 => ['label' => 'In Transit', 'class' => 'badge bg-info'],
        4 => ['label' => 'Delivered', 'class' => 'badge bg-success']
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
if(!empty($orderid) && !empty($_REQUEST['key'])){
?>
<div class="product-list">
  <div class="card">
    <div class="card-body p-3">
        <div class="row align-items-center g-3 mb-4">
            <div class="col-12 col-md-4 col-lg-4">
                <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Product">
            </div>

            <div class="col-12 col-md-4 col-lg-4 text-md-start" id="shipping-info">
                <?php if (!empty($shipping_company)) : ?>
                <div>
                    <strong>Shipping Company:</strong>
                    <span id="shipping-company"><?= htmlspecialchars($shipping_company) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($tracking_number)) : ?>
                <div>
                    <strong>Tracking #:</strong>
                    <span id="tracking-number"><?= htmlspecialchars($tracking_number) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-12 col-md-4 col-lg-4 text-md-end">
                <h4 class="mb-0">
                STATUS:
                <span class="<?= $status['class']; ?> fw-bold fs-5"><?= $status['label']; ?></span>
                </h4>
            </div>
        </div>
      <div class="table-responsive border rounded">
          <table class="table align-middle text-nowrap mb-0" id="orderTable">
              <thead>
                  <tr>
                      <th scope="col">Products</th>
                      <th scope="col">Color</th>
                      <th scope="col">Quantity</th>
                      <th scope="col">Status</th>
                      <th scope="col" class="text-right">Disc Price</th>
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
    function loadOrder(page = 1, limit = 5) {
        var search = $("#text-srh").val();
        $.ajax({
            url: 'pages/order_ajax.php',
            type: 'GET',
            data: {
                orderid: '<?= $orderid ?>',
                search: search,
                page: page,
                limit: limit,
                action: 'load_order_prod'
            },
            dataType: 'json',
            success: function (response) {
                let tbody = $("#orderTable tbody");
                let tfoot = $("#orderTable tfoot");
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
                                <td class="text-right"><h6 class="mb-0 fs-4">$ ${parseFloat(prodDiscTtlAmt).toFixed(2)}</h6></td>
                            </tr>
                        `);
                    });

                    $("#orderTable").append(`
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td colspan="2" class="text-end">Total:</td>
                                <td><h6 class="mb-0 fs-4">${totalQuantity}</h6></td>
                                <td></td>
                                <td class="text-right"><h6 class="mb-0 fs-4">$ ${totalDiscAmount.toFixed(2)}</h6></td>
                            </tr>
                        </tfoot>
                    `);

                    updatePagination(response.current_page, response.total_pages);
                } else {
                    tbody.append('<tr><td colspan="5" class="text-center">No products found.</td></tr>');
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
        loadOrder();
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
            loadOrder(page);
        }
    });

    $(document).on("change", "#rowsPerPage", function () {
        loadOrder(1, $(this).val());
    });

    loadOrder();
});
</script>

<?php
}
?>