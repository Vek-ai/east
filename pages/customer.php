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

$page_title = "Customer";

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
  td.notes,
  td.last-edit {
    white-space: normal;
    word-wrap: break-word;
  }

  .emphasize-strike {
    text-decoration: line-through;
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

  .dataTables_filter {
    width: 100%;
  }

  #toggleActive {
    margin-bottom: 10px;
  }

  .inactive-row {
    display: none;
  }

  .tooltip-inner {
    background-color: white !important;
    color: black !important;
    font-size: calc(0.875rem + 2px) !important;
  }
</style>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div><br>
        <h4 class="font-weight-medium fs-14 mb-0">Customer</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Customers
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Customer</li>
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

<?php                                                    
if ($permission === 'edit') {
?>
<div class="card card-body">
    <div class="row">
      <div class="col-md-12 col-xl-12 text-end d-flex justify-content-md-end justify-content-center mt-3 mt-md-0 gap-3">
          <button type="button" class="btn btn-primary d-flex align-items-center addCustomerBtn" data-id="" data-type="e">
              <i class="ti ti-plus text-white me-1 fs-5"></i> Add <?= $page_title ?>
          </button>
          <button type="button" id="downloadClassModalBtn" class="btn btn-primary d-flex align-items-center">
              <i class="ti ti-download text-white me-1 fs-5"></i> Download Classifications
          </button>
          <button type="button" id="downloadBtn" class="btn btn-primary d-flex align-items-center">
              <i class="ti ti-download text-white me-1 fs-5"></i> Download <?= $page_title ?>
          </button>
          <button type="button" id="uploadBtn" class="btn btn-primary d-flex align-items-center">
              <i class="ti ti-upload text-white me-1 fs-5"></i> Upload <?= $page_title ?>
          </button>
      </div>
    </div>
</div>
<?php
}
?>


<div class="card card-body">
    <div class="row">
        <div class="col-3">
            <h3 class="card-title align-items-center mb-2">
                Filter Customers 
            </h3>
            <div class="position-relative w-100 px-0 mr-0 mb-2">
                <input type="text" class="form-control py-2 ps-5" data-filter-name="Customer Name" id="text-srh" placeholder="Search">
                <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
            </div>
            <div class="align-items-center">
                <div class="position-relative w-100 px-1 mb-2">
                    <select class="form-control py-0 ps-5 select2 filter-selection" data-filter="tax" data-filter-name="Tax" id="select-tax">
                        <option value="">All Tax Status</option>
                        <optgroup label="Tax Status">
                          <?php
                          $query_tax_status = "SELECT * FROM customer_tax";
                          $result_tax_status = mysqli_query($conn, $query_tax_status);
                          while ($row_tax_status = mysqli_fetch_array($result_tax_status)) {
                            ?>
                            <option value="<?= $row_tax_status['taxid'] ?>">
                              (<?= $row_tax_status['percentage'] ?>%) <?= $row_tax_status['tax_status_desc'] ?></option>
                          <?php
                          }
                          ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 mb-2">
                    <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="loyalty" data-filter-name="Loyalty" id="select-loyalty">
                        <option value="">All Loyalty Options</option>
                        <option value="1">ON</option>
                        <option value="0">OFF</option>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 mb-2">
                    <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="pricing" data-filter-name="Pricing Category" id="select-pricing">
                        <option value="" data-category="">All Pricing Category</option>
                        <optgroup label="Pricing Category">
                            <?php
                            $query_pricing = "SELECT * FROM customer_pricing WHERE hidden = '0' AND status = '1'";
                            $result_pricing = mysqli_query($conn, $query_pricing);            
                            while ($row_pricing = mysqli_fetch_array($result_pricing)) {
                            ?>
                                <option value="<?= $row_pricing['id'] ?>"><?= $row_pricing['pricing_name'] ?></option>
                            <?php   
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="position-relative w-100 px-1 mb-2">
                    <select class="form-control search-category py-0 ps-5 select2 filter-selection" data-filter="city" data-filter-name="City" id="select-city">
                        <option value="">All Cities</option>
                        <optgroup label="Cities">
                            <?php
                            $query_city = "SELECT DISTINCT LOWER(city) AS city_lower 
                                              FROM customer 
                                              WHERE city IS NOT NULL AND city <> '' AND status = '1' and hidden = '0'
                                              ORDER BY city_lower;";
                            $result_city = mysqli_query($conn, $query_city);            
                            while ($row_city = mysqli_fetch_array($result_city)) {
                            ?>
                                <option value="<?= $row_city['city_lower'] ?>"><?= ucwords($row_city['city_lower']) ?></option>
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
            <div class="d-flex justify-content-end py-2">
                <button type="button" class="btn btn-outline-primary reset_filters">
                    <i class="fas fa-sync-alt me-1"></i> Reset Filters
                </button>
            </div>
        </div>
        <div class="col-9">
            <div id="selected-tags" class="mb-2"></div>
            <div class="datatables">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title d-flex justify-content-between align-items-center">Customer List</h4>
                  <div class="table-responsive">
                    <table id="display_customer" class="table table-bordered align-middle table-hover mb-0 text-md-nowrap">
                      <thead>
                        <tr>
                            <?php if (showCol('name')): ?>
                                <th>Name</th>
                            <?php endif; ?>

                            <?php if (showCol('business_name')): ?>
                                <th>Business Name</th>
                            <?php endif; ?>

                            <?php if (showCol('phone_number')): ?>
                                <th>Phone Number</th>
                            <?php endif; ?>

                            <?php if (showCol('status')): ?>
                                <th>Status</th>
                            <?php endif; ?>

                            <?php if (showCol('action')): ?>
                                <th>Action</th>
                            <?php endif; ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $no = 1;
                        $query_customer = "
                          SELECT c.*, ct.customer_type_name, 
                          (SELECT COUNT(o.customerid) FROM orders o WHERE o.customerid = c.customer_id) AS order_count,
                          lp.accumulated_total_orders, lp.loyalty_program_name
                          FROM customer c
                          LEFT JOIN customer_types ct ON c.customer_type_id = ct.customer_type_id 
                          LEFT JOIN loyalty_program lp ON c.loyalty = 1 AND 
                          (SELECT COUNT(o.customerid) FROM orders o WHERE o.customerid = c.customer_id) >= lp.accumulated_total_orders
                          WHERE c.hidden = 0";

                        $result_customer = mysqli_query($conn, $query_customer);
                        while ($row_customer = mysqli_fetch_array($result_customer)) {
                          $customer_id = $row_customer['customer_id'];
                          $name = $row_customer['customer_first_name'] . " " . $row_customer['customer_last_name'];
                          $business_name = $row_customer['customer_business_name'];
                          $email = $row_customer['contact_email'];
                          $phone = $row_customer['contact_phone'];
                          $fax = $row_customer['contact_fax'];
                          $address = $row_customer['address'];
                          $customer_type_name = $row_customer['customer_type_name'];
                          $type = $row_customer['customer_type_id'];
                          $db_status = $row_customer['status'];
                          $order_count = $row_customer['order_count'];
                        
                          if ($row_customer['loyalty'] == '1') {
                            if ($order_count >= $row_customer['accumulated_total_orders']) {
                              $loyalty = $row_customer['loyalty_program_name'];
                            } else {
                              $loyalty = "No Loyalty Level";
                            }
                          } else {
                            $loyalty = "Off";
                          }

                          if ($row_customer['status'] == '0' || $row_customer['status'] == '3') {
                            $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$customer_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                          } else {
                            $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$customer_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                          }
                          ?>
                          <tr id="product-row-<?= $no ?>"
                              data-tax="<?= $row_customer['tax_status'] ?>"
                              data-loyalty="<?= $row_customer['loyalty'] ?>"
                              data-city="<?= strtolower($row_customer['city']) ?>"
                              data-pricing="<?= $row_customer['customer_pricing'] ?>"
                          >
                            <?php if (showCol('name')): ?>
                                <td>
                                  <a href="javascript:void(0)" class="text-decoration-none">
                                    <span class="customer<?= $no ?><?php if ($row_customer['status'] == '0' || $row_customer['status'] == '3') {echo 'emphasize-strike';} ?>">
                                      <?php 
                                      if($row_customer['status'] == '3'){
                                        $merge_details = getCustomerDetails($customer_id);
                                        $merge_id = $merge_details['merge_from'];
                                        $current_details = getCustomerDetails($merge_id);
                                        echo "$name - Merge to " .$current_details['customer_first_name'] . " " . $current_details['customer_last_name'];
                                      }else{
                                        echo $name;
                                      }
                                      ?>
                                    </span>
                                  </a>
                                </td>
                            <?php endif; ?>

                            <?php if (showCol('business_name')): ?>
                                <td><?= $business_name ?></td>
                            <?php endif; ?>

                            <?php if (showCol('phone_number')): ?>
                                <td><?= $phone ?></td>
                            <?php endif; ?>

                            <?php if (showCol('status')): ?>
                                <td><?= $status ?></td>
                            <?php endif; ?>

                            <?php if (showCol('action')): ?>
                                <td class="text-center fs-5" id="action-button-<?= $no ?>">
                                  <?php if ($row_customer['status'] == '0') { ?>
                                    <a href="?page=customer&customer_id=<?= $customer_id ?>&t=e" class="py-1 text-dark hideCustomer" data-id="<?= $customer_id ?>" data-row="<?= $no ?>"
                                      style='border-radius: 10%;' data-toggle="tooltip" data-placement="top" title="Archive"><i
                                        class="fa fa-box-archive text-danger"></i></a>
                                  <?php } else { ?>
                                    <a href="javascript:void(0)" data-id="<?= $customer_id ?>" data-type="v" class="py-1 pe-1 viewCustomerBtn"
                                      title="View">
                                      <i class="fa fa-eye text-light"></i>
                                    </a>
                                    <?php                                                    
                                    if ($permission === 'edit') {
                                    ?>
                                    <a href="javascript:void(0)" data-id="<?= $customer_id ?>" data-type="<?= $type ?>" data-type="e" class="py-1 pe-1 editCustomerBtn"
                                      data-toggle="tooltip" data-placement="top" title="Edit">
                                      <i class="fa fa-pencil text-warning"></i>
                                    </a>
                                    <?php
                                    }
                                    ?>
                                    <a href="?page=customer-dashboard&id=<?= $customer_id ?>" class="py-1 pe-1" style='border-radius: 10%;'
                                      data-toggle="tooltip" data-placement="top" title="Dashboard"><i
                                        class="fa fa-chart-bar text-primary"></i>
                                    </a>
                                    <a href="?page=estimate_list&customer_id=<?= $customer_id ?>" class="py-1 pe-1"
                                      style='border-radius: 10%;' data-toggle="tooltip" data-placement="top" title="Estimates"><i
                                        class="fa fa-calculator text-secondary"></i>
                                    </a>
                                    <a href="?page=order_list&customer_id=<?= $customer_id ?>" class="py-1 pe-1"
                                      style='border-radius: 10%;' data-toggle="tooltip" data-placement="top" title="Orders"><i
                                        class="fa fa-cart-shopping text-success"></i>
                                    </a>
                                  <?php } ?>
                                </td>
                            <?php endif; ?>
                          </tr>
                          <?php
                          $no++;
                        }
                        ?>
                      </tbody>


                      <script>
                        $(document).ready(function () {
                          $('[data-toggle="tooltip"]').tooltip();
                          // Use event delegation for dynamically generated elements
                          $(document).on('click', '.changeStatus', function (event) {
                            event.preventDefault();
                            var customer_id = $(this).data('id');
                            var status = $(this).data('status');
                            var no = $(this).data('no');
                            $.ajax({
                              url: 'pages/customer_ajax.php',
                              type: 'POST',
                              data: {
                                customer_id: customer_id,
                                status: status,
                                action: 'change_status'
                              },
                              success: function (response) {
                                if (response == 'success') {
                                  if (status == 1) {
                                    $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                                    $(".changeStatus[data-no='" + no + "']").data('status', "0");
                                    $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                                    $('#action-button-' + no).html('<a href="javascript:void(0)" class="py-1 text-dark hideCustomer" data-id="' + customer_id + '" data-row="' + no + '"><i class="fa fa-trash text-light"></i></a>');
                                    $('#toggleActive').trigger('change');
                                  } else {
                                    $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                    $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                    $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                                    $('#action-button-' + no).html('<a href="javascript:void(0)" data-id=' + customer_id + ' data-type="e" class="py-1"><i class="fa fa-pencil text-light"></i></a>');
                                    $('#toggleActive').trigger('change');
                                  }
                                } else {
                                  alert('Failed to change status.');
                                }
                              },
                              error: function (jqXHR, textStatus, errorThrown) {
                                alert('Error: ' + textStatus + ' - ' + errorThrown);
                              }
                            });
                          });

                          $(document).on('click', '.hideCustomer', function (event) {
                            event.preventDefault();
                            var customer_id = $(this).data('id');
                            var rowId = $(this).data('row');
                            $.ajax({
                              url: 'pages/customer_ajax.php',
                              type: 'POST',
                              data: {
                                customer_id: customer_id,
                                action: 'hide_customer'
                              },
                              success: function (response) {
                                if (response == 'success') {
                                  $('#product-row-' + rowId).remove(); // Remove the row from the DOM
                                } else {
                                  alert('Failed to hide customer.');
                                }
                              },
                              error: function (jqXHR, textStatus, errorThrown) {
                                alert('Error: ' + textStatus + ' - ' + errorThrown);
                              }
                            });
                          });
                        });
                      </script>

                    </table>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

          <div class="modal-header">
              <h5 class="modal-title">Add New Customer</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
              <div class="mb-3">
                <label for="customerType" class="form-label">Customer Type</label>
                <select id="customerType" class="form-select">
                    <option value="" selected disabled>Select Customer Type</option>
                    <option value="1">Personal</option>
                    <option value="2">Business</option>
                    <option value="3">Farm</option>
                    <option value="4">Exempt - (Church/School/Municipal)</option>
                </select>
              </div>
          </div>

          <div class="modal-footer">
              <button type="button" class="btn btn-success" id="saveCustomerTypeBtn">Next</button>
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

<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="customerModalLabel">Customer Details</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="form_section">
          <form id="lineForm" class="form-horizontal">
              <div class="form_body"></div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Upload <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="card">
                  <div class="card-body">
                      <form id="upload_excel_form" action="#" method="post" enctype="multipart/form-data">
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
                      <button type="button" id="readUploadBtn" class="btn btn-primary fw-semibold">
                          <i class="fas fa-eye me-2"></i> View Uploaded File
                      </button>
                  </div>
              </div>    
          </div>
      </div>
  </div>
</div>

<div class="modal fade" id="readUploadModal" tabindex="-1" aria-labelledby="readUploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Uploaded Excel <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div id="uploaded_excel" class="modal-body"></div>
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
                              <option value="tax_status">Tax Status</option>
                              <option value="customer_pricing">Customer Pricing</option>
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

<div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
      <div class="modal-content">
          <div class="modal-header d-flex align-items-center">
              <h4 class="modal-title" id="myLargeModalLabel">
                  Download <?= $page_title ?>
              </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form id="download_excel_form" class="form-horizontal">
                  <label for="select-category" class="form-label fw-semibold">Select Supplier</label>
                  <div class="mb-3">
                      <select class="form-select select2" id="select-download-category" name="category">
                          <option value="">All Suppliers</option>
                          <optgroup label="Suppliers">
                              <?php
                              $query_supplier = "SELECT * FROM supplier WHERE status = 1 ORDER BY `supplier_name` ASC";
                              $result_supplier = mysqli_query($conn, $query_supplier);            
                              while ($row_supplier = mysqli_fetch_array($result_supplier)) {
                                  $selected = (!empty($supplierid) && $supplierid == $row_supplier['supplier_id']) ? 'selected' : '';
                                  if(!empty($_REQUEST['supplier_id'])){
                                    $selected = (!empty($supplier_id) && $supplier_id == $row_supplier['supplier_id']) ? 'selected' : '';
                                  }
                              ?>
                                  <option value="<?= $row_supplier['supplier_id'] ?>" <?= $selected ?>><?= $row_supplier['supplier_name'] ?></option>
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

<script>
  function toggleFormEditable(formId, enable = true, hideBorders = false, hideControls = false) {
    const $form = $("#" + formId);
    if ($form.length === 0) return;

    $form.find("input, select, textarea").each(function () {
      const $element = $(this);
      if (enable) {
        $element.removeAttr("readonly").removeAttr("disabled");
        $element.css("border", hideBorders ? "none" : "");
        $element.css("background-color", "");
        if ($element.is("select")) {
          $element.removeClass("hide-dropdown");
        }
      } else {
        $element.attr("readonly", true).attr("disabled", true);
        $element.css("border", hideBorders ? "none" : "1px solid #ccc");
        $element.css("background-color", "#f8f9fa");
        if ($element.is("select")) {
          $element.addClass("hide-dropdown");
        }
      }
    });

    $(".toggleElements").each(function () {
      $(this).toggleClass("d-none", !enable);
    });
  }

  $(document).ready(function () {
    document.title = "<?= $page_title ?>";

    var table = $('#display_customer').DataTable({
        pageLength: 100
    });

    $('#display_customer_filter').hide();

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            dropdownParent: $(this).parent()
        });
    });

    $(document).on("click", ".addCustomerBtn", function() {
        $("#addCustomerModal").modal("show");
    });

    $(document).on("click", "#saveCustomerTypeBtn", function() {
        let type = $("#customerType").val();
        if (!type) {
            alert("Please select a customer type.");
            return;
        }

        if(type == '1'){
            action = 'customer_personal_modal';
        }else if(type == '2'){
            action = 'customer_business_modal';
        }else if(type == '3'){
            action = 'customer_farm_modal';
        }else if(type == '4'){
            action = 'customer_exempt_modal';
        }else{
            action = 'customer_personal_modal';
        }

        $.ajax({
            url: "pages/customer_ajax_modal.php",
            type: "POST",
            data: {
              id: "",
              action: action
            },
            success: function (response) {
                $(".form_body").html(response);
                toggleFormEditable("lineForm", true, false);
                $("#addCustomerModal").modal("hide");

                $('#customerModal').modal('show');

                $("#customer_type_id").val(type);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert("Error: " + textStatus + " - " + errorThrown);
            }
        });
    });

    $(document).on("click", ".viewCustomerBtn", function() {
        let type = $(this).data('type');
        let customer_id = $(this).data('id');

        if(type == '1'){
            action = 'customer_personal_modal';
        }else if(type == '2'){
            action = 'customer_business_modal';
        }else if(type == '3'){
            action = 'customer_farm_modal';
        }else if(type == '4'){
            action = 'customer_exempt_modal';
        }else{
            action = 'customer_personal_modal';
        }
        
        $.ajax({
            url: "pages/customer_ajax_modal.php",
            type: "POST",
            data: {
              id: customer_id,
              action: action
            },
            success: function (response) {
                $(".form_body").html(response);
                toggleFormEditable("lineForm", false, true);
                $("#addCustomerModal").modal("hide");

                $('#customerModal').modal('show');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert("Error: " + textStatus + " - " + errorThrown);
            }
        });
    });

    $(document).on("click", ".editCustomerBtn", function() {
        let type = $(this).data('type');
        let customer_id = $(this).data('id');

        if(type == '1'){
            action = 'customer_personal_modal';
        }else if(type == '2'){
            action = 'customer_business_modal';
        }else if(type == '3'){
            action = 'customer_farm_modal';
        }else if(type == '4'){
            action = 'customer_exempt_modal';
        }else{
            action = 'customer_personal_modal';
        }
        
        $.ajax({
            url: "pages/customer_ajax_modal.php",
            type: "POST",
            data: {
              id: customer_id,
              action: action
            },
            success: function (response) {
                $(".form_body").html(response);
                toggleFormEditable("lineForm", true, false);
                $("#addCustomerModal").modal("hide");

                $('#customerModal').modal('show');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert("Error: " + textStatus + " - " + errorThrown);
            }
        });
    });

    $('.addModalBtn').on('click', function () {
        const id = $(this).data('id');
        const type = $(this).data('type');

        $.ajax({
          url: 'pages/customer_ajax_modal.php',
          type: 'POST',
          data: {
            id: id,
            action: 'change_act_cust_id'
          },
          success: function (response) {
            $('.form_body').html(response);

            if (type === "e") {
              toggleFormEditable("lineForm", true, false);
            } else {
              toggleFormEditable("lineForm", false, true);
            }

            $('#customerModal').modal('show');
          },
          error: function (jqXHR, textStatus, errorThrown) {
            alert('Error: ' + textStatus + ' - ' + errorThrown);
          }
        });
    });

    $('#customerTypeFilter').on('change', function () {
      var selectedValue = $(this).val();
      table.search(selectedValue).draw();
    });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
      var status = $(table.row(dataIndex).node()).find('a .alert').text().trim();
      var isActive = $('#toggleActive').is(':checked');

      if (!isActive || status === 'Active') {
        return true;
      }
      return false;
    });

    $('#toggleActive').on('change', function () {
      table.draw();
    });

    $('#toggleActive').trigger('change');

    function getCookie(name) {
      var nameEQ = name + "=";
      var ca = document.cookie.split(';');
      for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
      }
      return null;
    }

    $(document).on('submit', '#lineForm', function (event) {
      event.preventDefault();

      var userid = getCookie('userid');

      var formData = new FormData(this);
      formData.append('action', 'add_update');
      formData.append('userid', userid);

      var appendResult = "";

      $.ajax({
        url: 'pages/customer_ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          $('.modal').modal("hide");
          if (response === "Customer updated successfully.") {
            $('#responseHeader').text("Success");
            $('#responseMsg').text(response);
            $('#responseHeaderContainer').removeClass("bg-danger");
            $('#responseHeaderContainer').addClass("bg-success");
            $('#response-modal').modal("show");

            $('#response-modal').on('hide.bs.modal', function () {
              window.location.href = "?page=customer";
            });
          } else if (response === "New customer added successfully.") {
            $('#responseHeader').text("Success");
            $('#responseMsg').text(response);
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
          alert('Error: ' + textStatus + ' - ' + errorThrown);
        }
      });
    });

    $(document).on('click', '#uploadBtn', function(event) {
        $('#uploadModal').modal('show');
    });

    $(document).on('click', '#downloadBtn', function(event) {
        window.location.href = "pages/customer_ajax.php?action=download_excel";
    });

    $("#download_class_form").submit(function (e) {
        e.preventDefault();
        window.location.href = "pages/customer_ajax.php?action=download_classifications&class=" + encodeURIComponent($("#select-download-class").val());
    });

    $(document).on('click', '#downloadClassModalBtn', function(event) {
        $('#downloadClassModal').modal('show');
    });

    $(document).on('click', '#readUploadBtn', function(event) {
        $.ajax({
            url: 'pages/customer_ajax.php',
            type: 'POST',
            data: {
                action: "fetch_uploaded_modal"
            },
            success: function(response) {
                $('#uploaded_excel').html(response);
                $('#readUploadModal').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $('#upload_excel_form').on('submit', function (e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'upload_excel');

        $.ajax({
            url: 'pages/customer_ajax.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $('.modal').modal('hide');
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

    $(document).on('blur', '.table_data', function() {
        let newValue;
        let updatedData = {};
        
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
            action: 'update_test_data',
            id: id,
            header_name: headerName,
            new_value: newValue,
        };

        $.ajax({
            url: 'pages/customer_ajax.php',
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

    $(document).on('click', '#saveTable', function(event) {
        if (confirm("Are you sure you want to save this Excel data to the product lines data?")) {
            var formData = new FormData();
            formData.append("action", "save_table");

            $.ajax({
                url: "pages/customer_ajax.php",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('.modal').modal('hide');
                    response = response.trim();
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text(response);
                    $('#responseHeaderContainer').removeClass("bg-danger").addClass("bg-success");
                    $('#response-modal').modal("show");
                }
            });
        }
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
                return $(table.row(dataIndex).node()).find('a .alert').text().trim() === 'Active';
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
                    return false; // Exit loop early if mismatch is found
                }
            });

            return match;
        });

        table.draw();
        updateSelectedTags();
    }

    $(document).on('change', '.filter-selection', filterTable);

    $(document).on('input', '#text-srh', filterTable);

    $(document).on('change', '#toggleActive', filterTable);

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

    $(document).on('click', '.reset_filters', function () {
        $('.filter-selection').each(function () {
            $(this).val(null).trigger('change.select2');
        });

        $('#text-srh').val('');

        filterTable();
    });

    $(document).on('click', '.remove-image-btn', function(event) {
        event.preventDefault();
        let imageId = $(this).data('image-id');

        if (confirm("Are you sure you want to remove this image?")) {
            $.ajax({
                url: 'pages/customer_ajax.php',
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
  });
</script>