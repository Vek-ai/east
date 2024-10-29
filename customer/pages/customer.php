<?php
session_start();
include 'includes/dbconn.php';
include 'includes/functions.php';

$customer_id = $_SESSION['userid'];
$customer_first_name = "";
$customer_last_name = "";
$customer_business_name = "";
$old_customer_type_id = "";
$contact_email = "";
$contact_phone = "";
$contact_fax = "";
$address = "";
$city = "";
$state = "";
$zip = "";
$secondary_contact_name = "";
$secondary_contact_phone = "";
$ap_contact_name = "";
$ap_contact_email = "";
$ap_contact_phone = "";
$tax_status = "";
$tax_exempt_number = "";
$customer_notes = "";
$call_status = "";

$customer_name = $customer_first_name . " " . $customer_last_name;

$saveBtnTxt = "Add";
$addHeaderTxt = "Add New";

  if(!empty($_REQUEST['customer_id'])){
    $product_line_id = $_REQUEST['customer_id'];
    $query = "SELECT * FROM customer WHERE customer_id = '$product_line_id'";
    $result = mysqli_query($conn, $query);            
    while ($row = mysqli_fetch_array($result)) {
        $customer_id = $row['customer_id'];
        $customer_first_name = $row['customer_first_name'];
        $customer_last_name = $row['customer_last_name'];
        $customer_business_name = $row['customer_business_name'];
        $old_customer_type_id = $row['customer_type_id'];
        $contact_email = $row['contact_email'];
        $contact_phone = $row['contact_phone'];
        $contact_fax = $row['contact_fax'];
        $address = $row['address'];
        $city = $row['city'];
        $state = $row['state'];
        $zip = $row['zip'];
        $secondary_contact_name = $row['secondary_contact_name'];
        $secondary_contact_phone = $row['secondary_contact_phone'];
        $ap_contact_name = $row['ap_contact_name'];
        $ap_contact_email = $row['ap_contact_email'];
        $ap_contact_phone = $row['ap_contact_phone'];
        $tax_status = $row['tax_status'];
        $tax_exempt_number = $row['tax_exempt_number'];
        $customer_notes = $row['customer_notes'];
        $call_status = $row['call_status'];
    }
    $saveBtnTxt = "Update";
    $addHeaderTxt = "Update";

    echo '<script>
              $(document).ready(function() {
                  $("#customerModal").modal("show");
              });
          </script>';
  }

  $message = "";
  if(!empty($_REQUEST['result'])){
    if($_REQUEST['result'] == '1'){
      $message = "New customer added successfully.";
      $textColor = "text-success";
    }else if($_REQUEST['result'] == '2'){
      $message = "Customer updated successfully.";
      $textColor = "text-success";
    }else if($_REQUEST['result'] == '0'){
      $message = "Failed to Perform Operation";
      $textColor = "text-danger";
    }
  
}
?>

<style>
        /* Ensure that the text within the notes column wraps properly */
        td.notes,  td.last-edit{
            white-space: normal;
            word-wrap: break-word;
        }
        .emphasize-strike {
            text-decoration: line-through;
            font-weight: bold;
            color: #9a841c; /* You can choose any color you like for emphasis */
        }
      .dataTables_filter input {
    width: 100%; /* Adjust the width as needed */
    height: 50px; /* Adjust the height as needed */
    font-size: 16px; /* Adjust the font size as needed */
    padding: 10px; /* Adjust the padding as needed */
    border-radius: 5px; /* Adjust the border-radius as needed */
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
                  
          <div class="row pt-3">
            <div class="col-md-12 text-end">
              <!-- Trigger Modal Button -->
              <button type="button" class="btn btn-primary me-10 mb-3 px-5" data-bs-toggle="modal" data-bs-target="#customerModal">
                Add
              </button>
            </div>
          </div>

        <!-- Modal Structure -->
        <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel"><?= $addHeaderTxt ?> Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <!-- The form -->
                <form id="lineForm" class="form-horizontal">
              <div class="row pt-3">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" id="customer_first_name" name="customer_first_name" class="form-control"  value="<?= $customer_first_name ?>"/>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" id="customer_last_name" name="customer_last_name" class="form-control" value="<?= $customer_last_name ?>" />
                  </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Customer Email</label>
                    <input type="text" id="contact_email" name="contact_email" class="form-control"  value="<?= $contact_email ?>"/>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Customer Phone</label>
                    <input type="text" id="contact_phone" name="contact_phone" class="form-control" value="<?= $contact_phone ?>" />
                  </div>
                </div>        
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Customer Fax</label>
                    <input type="text" id="contact_fax" name="contact_fax" class="form-control" value="<?= $contact_fax ?>" />
                  </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-12">
                  <div class="mb-3">
                    <label class="form-label">Customer Business Name</label>
                    <input type="text" id="customer_business_name" name="customer_business_name" class="form-control" value="<?= $customer_business_name ?>" />
                  </div>
                </div>
              </div>

              <!-- CustomerTypeID -->

              <div class="row pt-3">
                <div class="col-md-12">
                  <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" id="address" name="address" class="form-control" value="<?= $address ?>" />
                  </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">City</label>
                    <input type="text" id="city" name="city" class="form-control"  value="<?= $city ?>"/>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">State</label>
                    <input type="text" id="state" name="state" class="form-control" value="<?= $state ?>" />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Zip</label>
                    <input type="text" id="zip" name="zip" class="form-control" value="<?= $zip ?>" />
                  </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Secondary Contact Name</label>
                    <input type="text" id="secondary_contact_name" name="secondary_contact_name" class="form-control"  value="<?= $secondary_contact_name ?>"/>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Secondary Contact Phone</label>
                    <input type="text" id="secondary_contact_phone" name="secondary_contact_phone" class="form-control" value="<?= $secondary_contact_phone ?>" />
                  </div>
                </div>
              </div>

              <div class="row pt-3">
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">AP Contact Name</label>
                    <input type="text" id="ap_contact_name" name="ap_contact_name" class="form-control"  value="<?= $ap_contact_name ?>"/>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">AP Contact Email</label>
                    <input type="text" id="ap_contact_email" name="ap_contact_email" class="form-control" value="<?= $ap_contact_email ?>" />
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">AP Contact Phone</label>
                    <input type="text" id="ap_contact_phone" name="ap_contact_phone" class="form-control" value="<?= $ap_contact_phone ?>" />
                  </div>
                </div>
              </div>
              
              <!-- LastOrderDate -->
              <!-- LastQuoteDate -->

              <div class="row pt-3">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Tax Status</label>
                    <input type="text" id="tax_status" name="tax_status" class="form-control"  value="<?= $tax_status ?>"/>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Tax Exempt Number</label>
                    <input type="text" id="tax_exempt_number" name="tax_exempt_number" class="form-control" value="<?= $tax_exempt_number ?>" />
                  </div>
                </div>
                <div class="col-md-4">
                    <?php
                    // Fetch all customer types
                    $query = "SELECT * FROM customer_types";
                    $result = mysqli_query($conn, $query);
                    
                    // Fetch the name for the old customer type ID
                    $default_customer_type_name = '';
                    if ($old_customer_type_id > 0) {
                        $default_query = "SELECT customer_type_name FROM customer_types WHERE customer_type_id = $old_customer_type_id";
                        $default_result = mysqli_query($conn, $default_query);
                        if ($default_row = mysqli_fetch_assoc($default_result)) {
                            $default_customer_type_name = htmlspecialchars($default_row['customer_type_name']);
                        }
                    }
                    ?>
                    <div class="mb-3">
                        <label class="form-label">Customer Type</label>
                        <select class="form-select" id="customer_type" name="customer_type">
                            <option value=""><?php echo $default_customer_type_name ? $default_customer_type_name : 'Choose...'; ?></option>
                            <?php
                            // Generate options for the dropdown
                            while ($row = mysqli_fetch_assoc($result)) {
                                $selected = ($old_customer_type_id == $row['customer_type_id']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($row['customer_type_id']) . '" ' . $selected . '>' . htmlspecialchars($row['customer_type_name']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Customer Notes</label>
                <textarea class="form-control" id="customer_notes" name="customer_notes" rows="5"><?= $customer_notes ?></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Customer Call Status</label>
                <input type="checkbox" id="call_status" name="call_status" <?= $call_status ? 'checked' : '' ?>>
              </div>

              <div class="form-actions">
                <div class="card-body border-top ">
                  <input type="hidden" id="customer_id" name="customer_id" class="form-control"  value="<?= $customer_id ?>"/>
                  <div class="row">
                    
                    <div class="col-6 text-start">
                    
                    </div>
                    <div class="col-6 text-end">
                      <button type="submit" class="btn btn-primary" style="border-radius: 10%;"><?= $saveBtnTxt ?></button>
                    </div>
                  </div>
                  
                </div>
              </div>

            </form>
              </div>
            </div>
          </div>
        </div>

  <!-- Table -->
  <div class="col-12">
    <div class="datatables">
      <div class="card">
        <div class="card-body">
            <h4 class="card-title d-flex justify-content-between align-items-center">Customer List  &nbsp;&nbsp; <?php if(!empty($_REQUEST['product_line_id'])){ ?>
              <a href="/cashier/?page=customer" class="btn btn-primary" style="border-radius: 10%;">Add New</a>
              <?php } ?> <div> <input type="checkbox" id="toggleActive" checked> Show Active Only</div>
            </h4>
          
          <div class="table-responsive">
            <table id="display_customer" class="table table-striped table-bordered text-nowrap align-middle">
              <thead>
                <!-- start row -->
                <tr>
                  <th>Name</th>
                  <th>Business Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Fax</th>
                  <th>Address</th>
                  <th>
                    <div class="">
                      <!-- Add a Dropdown for Filtering -->
                      <?php
                        $query = "SELECT * FROM customer_types";
                        $result = mysqli_query($conn, $query);
                      ?>

                      <!-- Add a Dropdown for Filtering -->
                      <select id="customerTypeFilter">
                          <option value="">Category</option>
                          <?php
                          // Loop through the results and create dropdown options
                          while ($row = mysqli_fetch_assoc($result)) {
                          ?>
                              <option value='<?= $row['customer_type_name'] ?>'><?= $row['customer_type_name'] ?></option>
                          <?php
                          }
                          ?>
                      </select>
                    </div>
                  </th>
                  <th>Status</th>
                
                  <th>Action</th>
                </tr>
                <!-- end row -->
              </thead>
              <tbody>
                <?php
                $no = 1;
                $query_customer = "
                  SELECT c.*, ct.customer_type_name 
                  FROM customer c 
                  LEFT JOIN customer_types ct ON c.customer_type_id = ct.customer_type_id 
                  WHERE c.hidden = 0";
                $result_customer = mysqli_query($conn, $query_customer);            
                while ($row_customer = mysqli_fetch_array($result_customer)) {
                    $customer_id = $row_customer['customer_id'];
                    $name = $row_customer['customer_first_name'] . "" . $row_customer['customer_last_name'];
                    $business_name = $row_customer['customer_business_name'];
                    $email = $row_customer['contact_email'];
                    $phone = $row_customer['contact_phone'];
                    $fax = $row_customer['contact_fax'];
                    $address = $row_customer['address'];
                    $customer_type_name = $row_customer['customer_type_name'];
                    $db_status = $row_customer['status'];

                      if ($row_customer['status'] == '0') {
                          $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$customer_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Inactive</div></a>";
                      } else {
                          $status = "<a href='#' class='changeStatus' data-no='$no' data-id='$customer_id' data-status='$db_status'><div id='status-alert$no' class='alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0' style='border-radius: 5%;' role='alert'>Active</div></a>";
                      }
                  ?>
                  <tr  id="product-row-<?= $no ?>">
                      <td><span class="customer<?= $no ?> <?php if ($row_customer['status'] == '0') { echo 'emphasize-strike'; } ?>"><?= $name ?></span></td>
                      <td><?= $business_name ?></td>
                      <td><?= $email ?></td>
                      <td><?= $phone ?></td>
                      <td><?= $fax ?></td>
                      <td><?= $address ?></td>
                      <td><?= $customer_type_name ?></td>
                      <td><?= $status ?></td>
                      <td class="text-center" id="action-button-<?= $no ?>">
                          <?php if ($row_customer['status'] == '0') { ?>
                              <a href="#" class="btn btn-light py-1 text-dark hideCustomer" data-id="<?= $customer_id ?>" data-row="<?= $no ?>" style='border-radius: 10%;'>Archive</a>
                          <?php } else { ?>
                              <a href="/cashier/?page=customer&customer_id=<?= $customer_id ?>" class="btn btn-primary py-1" style='border-radius: 10%;'>Edit</a>
                          <?php } ?>
                      </td>
                  </tr>
                  <?php
                $no++;
                }
                ?>
              </tbody>
                <script>
                  $(document).ready(function() {
                      // Use event delegation for dynamically generated elements
                      $(document).on('click', '.changeStatus', function(event) {
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
                              success: function(response) {
                                  if (response == 'success') {
                                      if (status == 1) {
                                          $('#status-alert' + no).removeClass().addClass('alert alert-danger bg-danger text-white border-0 text-center py-1 px-2 my-0').text('Inactive');
                                          $(".changeStatus[data-no='" + no + "']").data('status', "0");
                                          $('.product' + no).addClass('emphasize-strike'); // Add emphasize-strike class
                                          $('#action-button-' + no).html('<a href="#" class="btn btn-light py-1 text-dark hideCustomer" data-id="' + customer_id + '" data-row="' + no + '" style="border-radius: 10%;">Archive</a>');
                                          $('#toggleActive').trigger('change');
                                        } else {
                                          $('#status-alert' + no).removeClass().addClass('alert alert-success bg-success text-white border-0 text-center py-1 px-2 my-0').text('Active');
                                          $(".changeStatus[data-no='" + no + "']").data('status', "1");
                                          $('.product' + no).removeClass('emphasize-strike'); // Remove emphasize-strike class
                                          $('#action-button-' + no).html('<a href="/cashier/?page=customer&customer_id=' + customer_id + '" class="btn btn-primary py-1" style="border-radius: 10%;">Edit</a>');
                                          $('#toggleActive').trigger('change');
                                        }
                                  } else {
                                      alert('Failed to change status.');
                                  }
                              },
                              error: function(jqXHR, textStatus, errorThrown) {
                                  alert('Error: ' + textStatus + ' - ' + errorThrown);
                              }
                          });
                      });

                      $(document).on('click', '.hideCustomer', function(event) {
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
                              success: function(response) {
                                  if (response == 'success') {
                                      $('#product-row-' + rowId).remove(); // Remove the row from the DOM
                                  } else {
                                      alert('Failed to hide customer.');
                                  }
                              },
                              error: function(jqXHR, textStatus, errorThrown) {
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
  // for 
  $(document).ready(function() {
    var table = $('#display_customer').DataTable({
      columnDefs: [
        { orderable: false, targets: 6 }  // Disable sorting for the "Customer Type" column (index 6)
      ]
    });
    
        // Filter based on dropdown selection
        $('#customerTypeFilter').on('change', function() {
    var selectedValue = $(this).val();
    table.search(selectedValue).draw();  // Apply global search based on dropdown value
  });
    
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var status = $(table.row(dataIndex).node()).find('a .alert').text().trim();
        var isActive = $('#toggleActive').is(':checked');

        if (!isActive || status === 'Active') {
            return true;
        }
        return false;
    });

    $('#toggleActive').on('change', function() {
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

    $('#lineForm').on('submit', function(event) {
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
            success: function(response) {
              
              if (response === "Customer updated successfully.") {
                  $('#responseHeader').text("Success");
                  $('#responseMsg').text(response);
                  $('#responseHeaderContainer').removeClass("bg-danger");
                  $('#responseHeaderContainer').addClass("bg-success");
                  $('#response-modal').modal("show");

                  $('#response-modal').on('hide.bs.modal', function () {
                    window.location.href = "cashier/?page=customer";
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
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error: ' + textStatus + ' - ' + errorThrown);
            }
        });
    });

    
});
</script>