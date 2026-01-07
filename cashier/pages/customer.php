<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING);
require '../includes/dbconn.php';
require '../includes/functions.php';

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
    font-size: calc(0.875rem + 2px) !important;
  }

  .select2-container--default .select2-results__option[aria-disabled=true] {
      display: none;
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
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Business/ Customer Name</th>
                            <th>Farm Name</th>
                            <th>Phone</th>
                            <th>Tax Status</th>
                            <th>Status</th>
                            <th>Action</th>
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

<div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h5 class="modal-title">Add Job Deposit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="depositForm" class="form-horizontal">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                            <input type="hidden" id="job_id" name="job_id">
                            <input type="hidden" id="deposited_by" name="deposited_by">

                            <div class="mb-3">
                                <label for="type" class="form-label">Deposit Type</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                    <option value="card">Credit/Debit Card</option>
                                    <option value="wire">Wire Transfer</option>
                                </select>
                            </div>

                            <div id="deposit_details_group" class="d-none">
                                <div class="mb-3">
                                    <label for="deposit_amount" class="form-label">Deposit Amount</label>
                                    <input type="number" step="0.01" class="form-control" id="deposit_amount" name="deposit_amount" >
                                </div>

                                <div class="mb-3">
                                    <label for="reference_no" class="form-label">Reference No</label>
                                    <input type="text" class="form-control" id="reference_no" name="reference_no" required>
                                </div>

                                <div class="mb-3 d-none" id="check_no_group">
                                    <label for="check_no" class="form-label">Check No</label>
                                    <input type="text" class="form-control" id="check_no" name="check_no">
                                </div>

                                <div class="mb-3 d-none" id="card_group">
                                    <label for="auth_no" class="form-label">Authorization #</label>
                                    <input type="text" class="form-control" id="auth_no" name="auth_no">
                                </div>

                                <div class="mb-3">
                                    <h6 class="mb-0">Job Name</h6>
                                    <div id="order_checkout">
                                        <select id="job_name" class="form-control" name="job_id">
                                            <option value="">Select Job Name...</option>
                                            <?php
                                            $query_job_name = "SELECT * FROM jobs";
                                            $result_job_name = mysqli_query($conn, $query_job_name);
                                            while ($row_job_name = mysqli_fetch_array($result_job_name)) {
                                                $job_id = $row_job_name['job_id'];
                                                $customer_id_option = $row_job_name['customer_id'];
                                            ?>
                                                <option value="<?= $job_id; ?>" 
                                                        data-customer-id="<?= $customer_id_option; ?>"
                                                        data-constructor="<?= htmlspecialchars($row_job_name['constructor_name']); ?>" 
                                                        data-constructor-contact="<?= htmlspecialchars($row_job_name['constructor_contact']); ?>"
                                                        data-credit="<?= htmlspecialchars(getJobBalance($job_id)); ?>"
                                                        data-job-id="<?= $job_id ?>">
                                                    <?= htmlspecialchars($row_job_name['job_name']); ?>
                                                </option>
                                            <?php } ?>
                                            <option value="add_new_job_name">Add new Job Name</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <h6 class="mb-0">Job PO #</h6>
                                    <input type="text" id="job_po" name="job_po" class="form-control" placeholder="Enter Job PO #">
                                </div>

                                <div class="mt-3">
                                    <h6 class="mb-0">Salesperson</h6>
                                    <div id="salesperson_div">
                                        <select id="salesperson" class="form-control select2" name="salesperson">
                                            <option value="">Select Salesperson...</option>
                                            <?php
                                            $query_staff = "SELECT staff_id, staff_fname, staff_lname FROM staff WHERE status = 1 ORDER BY staff_fname ASC";
                                            $result_staff = mysqli_query($conn, $query_staff);
                                            while ($row_staff = mysqli_fetch_assoc($result_staff)) {
                                                $selected = ($cashier_id == $row_staff['staff_id']) ? 'selected' : '';
                                                ?>
                                                <option value="<?= $row_staff['staff_id'] ?>" <?= $selected ?>>
                                                    <?= htmlspecialchars($row_staff['staff_fname'] . ' ' . $row_staff['staff_lname']) ?>
                                                </option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" style="border-radius: 10%;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="prompt_job_name_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog" role="document">
        <form id="job_name_form" class="modal-content modal-content-demo">
            <input type='hidden' id="selected_customer_id" name="customer_id" value="<?= $customer_id ?>">
            <div class="modal-header">
                <h6 class="modal-title">New Job Name</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="job_name_prompt_container">
                    <div class="job_name_input">
                        <div class="mb-2">
                            <label class="fs-5 fw-bold" for="job_name">Job Name</label>
                            <input id="new_job_name" name="job_name" class="form-control" placeholder="Enter Job Name" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success ripple btn-secondary" data-bs-dismiss="modal" type="submit">Save</button>
                <button class="btn btn-danger ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
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

<script>
  class BaseMap {
      constructor(inputSelector, listSelector, modalSelector, mapContainerSelector, latField, lngField, title, type) {
          this.inputSelector = inputSelector;
          this.listSelector = listSelector;
          this.modalSelector = modalSelector;
          this.mapContainerSelector = mapContainerSelector;
          this.latField = latField;
          this.lngField = lngField;
          this.title = title;
          this.type = type;

          this.lat = this.safeFloat($(latField).val(), 37.8393);
          this.lng = this.safeFloat($(lngField).val(), -84.2700);

          this.map = null;
          this.marker = null;

          this.debounce = (func, wait) => {
              let timeout;
              return (...args) => {
                  clearTimeout(timeout);
                  timeout = setTimeout(() => func.apply(this, args), wait);
              };
          };

          this.initUI();
      }

      safeFloat(val, fallback) {
          if (val === undefined || val === null || val === "") return fallback;
          const num = parseFloat(val);
          return isNaN(num) ? fallback : num;
      }

      initUI() {
          $(this.inputSelector).on('input', this.debounce(() => this.updateSuggestions(), 400));
          $(this.inputSelector).on('change', () => this.onAddressChange());

          $(this.modalSelector).on('shown.bs.modal', () => {
              if (!this.map) this.initMap();
          });

          $(this.modalSelector).on('hidden.bs.modal', () => $('#customerModal').modal('show'));
      }

      updateSuggestions() {
          let query = $(this.inputSelector).val();
          if (query.length < 2) return;

          $.ajax({
              url: 'pages/supplier_ajax.php',
              method: 'POST',
              data: { action: 'search_address', query },
              dataType: 'json',
              success: (data) => {
                  let datalist = $(this.listSelector).empty();
                  data.forEach(item => {
                      $('<option>')
                          .attr('value', item.display_name)
                          .data('lat', item.lat)
                          .data('lon', item.lon)
                          .appendTo(datalist);
                  });
              },
              error: (xhr, status, err) => console.error("Suggestion error:", status, err, xhr.responseText)
          });
      }

      onAddressChange() {
          let selectedOption = $(`${this.listSelector} option[value="${$(this.inputSelector).val()}"]`);
          if (!selectedOption.length) return;

          let lat = parseFloat(selectedOption.data('lat'));
          let lng = parseFloat(selectedOption.data('lon'));

          this.lat = lat;
          this.lng = lng;

          this.marker = this.updateMarker(this.lat, this.lng, this.title);
          this.getPlaceName();
      }

      updateMarker(lat, lng, title) {
          if (!this.map) return this.marker;
          if (this.marker) this.marker.setMap(null);
          const pos = new google.maps.LatLng(lat, lng);
          const marker = new google.maps.Marker({ position: pos, map: this.map, title });
          this.map.setCenter(pos);
          return marker;
      }

      initMap() {
          const container = document.querySelector(this.mapContainerSelector);
          if (!container) return console.error("Map container not found:", this.mapContainerSelector);

          this.map = new google.maps.Map(container, {
              center: { lat: this.lat, lng: this.lng },
              zoom: 13
          });

          this.marker = this.updateMarker(this.lat, this.lng, this.title);

          google.maps.event.addListener(this.map, 'click', e => {
              this.lat = e.latLng.lat();
              this.lng = e.latLng.lng();
              this.marker = this.updateMarker(this.lat, this.lng, this.title);
              this.getPlaceName();
          });
      }

      getPlaceName() {
          $.ajax({
              url: 'pages/supplier_ajax.php',
              method: 'POST',
              data: { action: 'get_place_name', lat: this.lat, lng: this.lng, type: this.type },
              dataType: 'json',
              success: data => {
                  if (!data || !data.display_name) return;

                  if (this.type === 'main') {
                      $('#searchBox1').val(data.display_name);
                      $('#address').val(data.address.road || data.address.suburb || '');
                      $('#city').val(data.address.city || data.address.town || '');
                      $('#state').val(data.address.state || data.address.region || '');
                      $('#zip').val(data.address.postcode || '');
                      $('#lat').val(this.lat);
                      $('#lng').val(this.lng);
                  } else if (this.type === 'ship') {
                      $('#searchBox2').val(data.display_name);
                      $('#ship_address').val(data.address.road || data.address.suburb || '');
                      $('#ship_city').val(data.address.city || data.address.town || '');
                      $('#ship_state').val(data.address.state || data.address.region || '');
                      $('#ship_zip').val(data.address.postcode || '');
                      $('#ship_lat').val(this.lat);
                      $('#ship_lng').val(this.lng);
                  } else if (this.type === 'corpo') {
                      $('#searchBox3').val(data.display_name);
                      $('#corpo_address').val(data.address.road || data.address.suburb || '');
                      $('#corpo_city').val(data.address.city || data.address.town || '');
                      $('#corpo_state').val(data.address.state || data.address.region || '');
                      $('#corpo_zip').val(data.address.postcode || '');
                      $('#corpo_lat').val(this.lat);
                      $('#corpo_lng').val(this.lng);
                  }
              },
              error: () => console.error("Error retrieving place name")
          });
      }
  }

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
        serverSide: true,
        processing: true,
        ajax: {
            url: 'pages/customer_ajax.php',
            type: 'POST',
            data: function(d) {
                d.action = 'fetch_table';
                d.textSearch = $('#text-srh').val();
                d.isActive = $('#toggleActive').is(':checked') ? 1 : 0;
                // manual filters
                d.tax = $('#select-tax').val();
                d.loyalty = $('#select-loyalty').val();
                d.pricing = $('#select-pricing').val();
                d.city = $('#select-city').val();
            },
            dataSrc: function (json) {
                console.log(json);
                return json.data;
            },
            error: function (xhr, error, thrown) {
                console.log('AJAX Error: ', xhr.responseText);
            }
        },
        pageLength: 100,
        lengthMenu: [10, 25, 50, 100],
        columns: [
            { data: 'fname' },
            { data: 'lname' },
            { data: 'business_name' },
            { data: 'farm_name' },
            { data: 'phone_number' },
            { data: 'tax_status' },
            { data: 'status', orderable: false },
            { data: 'action', orderable: false },
        ]
    });

    $('#display_customer_filter').hide();

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
                filterTable();
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
                filterTable();
            } else {
                alert('Failed to hide customer.');
            }
            },
            error: function (jqXHR, textStatus, errorThrown) {
            alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    $(".select2").each(function () {
        $(this).select2({
            width: '100%',
            dropdownParent: $(this).parent()
        });
    });

    /* $(document).on("click", ".addCustomerBtn", function() {
        $("#addCustomerModal").modal("show");
    }); */

    $(document).on("click", ".addCustomerBtn", function() {
        //let type = $("#customerType").val();

        /* if (!type) {
            alert("Please select a customer type.");
            return;
        } */
        let type = 0;
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
                console.log(response);
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
          if (response.trim() === "success_update") {
            $('#responseHeader').text("Success");
            $('#responseMsg').text("Customer updated successfully.");
            $('#responseHeaderContainer').removeClass("bg-danger");
            $('#responseHeaderContainer').addClass("bg-success");
            $('#response-modal').modal("show");

            $('#response-modal').on('hide.bs.modal', function () {
              window.location.href = "?page=customer";
            });
          } else if (response.trim() === "success_add") {
            $('#responseHeader').text("Success");
            $('#responseMsg').text("New customer added successfully.");
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
        table.ajax.reload();
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

    $(document).on('click', '.reset_filters', function () {
        $('.filter-selection').each(function () {
            $(this).val(null);
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

    $(document).on('change', '#type', function () {
        const type = $(this).val();

        $('#check_no_group').addClass('d-none');
        $('#card_group').addClass('d-none');

        if (type === 'cash') {
            $('#deposit_details_group').removeClass('d-none');
        } else if (type === 'wire') {
            $('#deposit_details_group').removeClass('d-none');
        } else if (type === 'check') {
            $('#deposit_details_group').removeClass('d-none');
            $('#check_no_group').removeClass('d-none');
            $('#check_no').attr('required', true);
        } else if (type === 'card') {
            $('#deposit_details_group').removeClass('d-none');
            $('#card_group').removeClass('d-none');
        } else {
            $('#deposit_details_group').addClass('d-none');
        }
    });

    $(document).on('click', '#depositModalBtn', function () {
        const customer_id = $(this).data('id');

        $('#deposited_by').val(customer_id);
        $('#selected_customer_id').val(customer_id);

        $('#job_name option').each(function() {
            const optionCustomerId = $(this).data('customer-id');
            if (optionCustomerId !== undefined) {
                $(this).prop('disabled', optionCustomerId != customer_id);
            } else {
                $(this).prop('disabled', false);
            }
        });

        $('#job_name').select2('destroy').select2({
            width: '100%',
            placeholder: "Select Job Name...",
            dropdownParent: $('#depositModal')
        });

        $('#job_name').val('').trigger('change');

        $('#depositModal').modal('show');
    });
    
    $('#job_name').select2({
        width: '100%',
        placeholder: "Select Job Name...",
        dropdownAutoWidth: true,
        dropdownParent: $('#order_checkout'),
        templateResult: function (data) {
            if (data.id === 'add_new_job_name') {
                return $(
                    '<div style="border-top: 1px solid #ddd; margin-top: 0px; padding-top: 10px;">' +
                    '<span style="font-style: italic; color: #ff6b6b;">' + data.text + '</span>' +
                    '</div>'
                );
            }
            return data.text;
        },
        templateSelection: function (data) {
            return data.text;
        },
        matcher: function (params, data) {
            if (data.id === 'add_new_job_name') {
                return data;
            }
            return $.fn.select2.defaults.defaults.matcher(params, data);
        }
    });

    $('#job_name').on('select2:select', function (e) {
        const selectedValue = e.params.data.id;

        if (selectedValue === 'add_new_job_name') {
            $('#prompt_job_name_modal').modal('show');
            $('#job_name').val(null).trigger('change');
        } 
    });

    $(document).on('submit', '#job_name_form', function (event) {
        event.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'add_job_name');
        const newJobName = $('#new_job_name').val().trim();

        $.ajax({
            url: 'pages/customer_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                const isSuccess = response.success === true;

                $('#prompt_job_name_modal').modal('hide');

                $('#responseHeader').text(isSuccess ? 'Success' : 'Failed');
                $('#responseMsg').text(
                    isSuccess
                        ? 'Successfully added Job Name.'
                        : (response.message || 'Something went wrong')
                );
                $('#responseHeaderContainer')
                    .toggleClass('bg-success', isSuccess)
                    .toggleClass('bg-danger', !isSuccess);

                $('#response-modal').modal('show');

                if (isSuccess && newJobName !== '') {
                    const jobSelect = $('#job_name');
                    const addNewOption = jobSelect.find('option[value="add_new_job_name"]').detach();

                    const newOption = new Option(
                        response.job_name,
                        response.job_id,
                        true,
                        true
                    );
                    jobSelect.append(newOption);
                    jobSelect.append(addNewOption);
                    jobSelect.trigger('change');
                }
            },
            error: function (xhr) {
                console.error('Error:', xhr.responseText);
            }
        });
    });


    $('#depositForm').on('submit', function(event) {
        event.preventDefault(); 
        var formData = new FormData(this);
        formData.append('action', 'deposit_job');
        $.ajax({
            url: 'pages/customer_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('.modal').modal("hide");
                if (response == "success") {
                    $('#responseHeader').text("Success");
                    $('#responseMsg').text("Amount Deposited successfully!");
                    $('#responseHeaderContainer').removeClass("bg-danger");
                    $('#responseHeaderContainer').addClass("bg-success");
                    $('#response-modal').modal("show");

                    $('#response-modal').on('hide.bs.modal', function () {
                      location.reload();
                    });
                } else {
                    $('#responseHeader').text("Failed");
                    $('#responseMsg').text("Process Failed");
                    console.log("Response: "+response);
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
  });
</script>