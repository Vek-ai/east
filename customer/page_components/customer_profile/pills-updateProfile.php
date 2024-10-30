<div class="row">
  <div class="col-lg-12 mt-4">
    <div class="card shadow-none">
      <div class="card-body">
        <form id="lineForm" class="form-horizontal">
            <div class="row pt-3">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">First Name</label>
                  <input type="text" id="customer_first_name" name="customer_first_name" class="form-control"
                    value="<?= htmlspecialchars($currentUser['customer_first_name']) ?>" />
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Last Name</label>
                  <input type="text" id="customer_last_name" name="customer_last_name" class="form-control"
                    value="<?= htmlspecialchars($currentUser['customer_last_name']) ?>" />
                </div>
              </div>
            </div>

            <div class="row pt-3">
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Customer Email</label>
                  <input type="text" id="contact_email" name="contact_email" class="form-control"
                    value="<?= htmlspecialchars($currentUser['contact_email']) ?>" />
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Customer Phone</label>
                  <input type="text" id="contact_phone" name="contact_phone" class="form-control"
                    value="<?= htmlspecialchars($currentUser['contact_phone']) ?>" />
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Customer Fax</label>
                  <input type="text" id="contact_fax" name="contact_fax" class="form-control"
                    value="<?= htmlspecialchars($currentUser['contact_fax']) ?>" />
                </div>
              </div>
            </div>

            <div class="row pt-3">
              <div class="col-md-12">
                <div class="mb-3">
                  <label class="form-label">Customer Business Name</label>
                  <input type="text" id="customer_business_name" name="customer_business_name" class="form-control"
                    value="<?= htmlspecialchars($currentUser['customer_business_name']) ?>" />
                </div>
              </div>
            </div>

            <!-- CustomerTypeID -->

            <div class="row pt-3">
              <div class="col-md-12">
                <div class="mb-3">
                  <label class="form-label">Address</label>
                  <input type="text" id="address" name="address" class="form-control"
                    value="<?= htmlspecialchars($currentUser['address']) ?>" />
                </div>
              </div>
            </div>

            <div class="row pt-3">
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">City</label>
                  <input type="text" id="city" name="city" class="form-control"
                    value="<?= htmlspecialchars($currentUser['city']) ?>" />
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">State</label>
                  <input type="text" id="state" name="state" class="form-control"
                    value="<?= htmlspecialchars($currentUser['state']) ?>" />
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Zip</label>
                  <input type="text" id="zip" name="zip" class="form-control"
                    value="<?= htmlspecialchars($currentUser['zip']) ?>" />
                </div>
              </div>
            </div>

            <div class="row pt-3">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Secondary Contact Name</label>
                  <input type="text" id="secondary_contact_name" name="secondary_contact_name" class="form-control"
                    value="<?= htmlspecialchars($currentUser['secondary_contact_name']) ?>" />
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Secondary Contact Phone</label>
                  <input type="text" id="secondary_contact_phone" name="secondary_contact_phone" class="form-control"
                    value="<?= htmlspecialchars($currentUser['secondary_contact_phone']) ?>" />
                </div>
              </div>
            </div>

            <div class="row pt-3">
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">AP Contact Name</label>
                  <input type="text" id="ap_contact_name" name="ap_contact_name" class="form-control"
                    value="<?= htmlspecialchars($currentUser['ap_contact_name']) ?>" />
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">AP Contact Email</label>
                  <input type="text" id="ap_contact_email" name="ap_contact_email" class="form-control"
                    value="<?= htmlspecialchars($currentUser['ap_contact_email']) ?>" />
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">AP Contact Phone</label>
                  <input type="text" id="ap_contact_phone" name="ap_contact_phone" class="form-control"
                    value="<?= htmlspecialchars($currentUser['ap_contact_phone']) ?>" />
                </div>
              </div>
            </div>

            <!-- LastOrderDate -->
            <!-- LastQuoteDate -->

            <div class="row pt-3">
              <div class="col-md-6 opt_field_update">
                <div class="mb-3">
                  <label class="form-label">Tax Status</label>
                  <select id="tax_status" class="form-select form-control" name="tax_status">
                    <option value="">Select Tax Status...</option>
                    <?php
                    $query_tax_status = "SELECT * FROM customer_tax";
                    $result_tax_status = mysqli_query($conn, $query_tax_status);
                    while ($row_tax_status = mysqli_fetch_array($result_tax_status)) {
                      $selected = ($tax_status == $row_tax_status['taxid']) ? 'selected' : '';
                      ?>
                      <option value="<?= $row_tax_status['taxid'] ?>" <?= $selected ?>>
                        (<?= $row_tax_status['percentage'] ?>%) <?= $row_tax_status['tax_status_desc'] ?></option>
                      <?php
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Tax Exempt Number</label>
                  <input type="text" id="tax_exempt_number" name="tax_exempt_number" class="form-control"
                    value="<?= htmlspecialchars($currentUser['tax_exempt_number']) ?>" />
                </div>
              </div>

            </div>

            <div class="row pt-3">
              <div class="col-md-6">
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
                
              </div>

            </div>

            <div class="mb-3">
              <label class="form-label">Customer Notes</label>
              <textarea class="form-control" id="customer_notes" name="customer_notes"
                rows="5"><?= htmlspecialchars($currentUser['customer_notes']) ?></textarea>
            </div>

            <div class="row mb-3">
              <div class="col">
                <label class="form-label">Customer Call Status</label>
                <input type="checkbox" id="call_status" name="call_status" <?= $currentUser['call_status'] ? 'checked' : '' ?>>
              </div>
            </div>

            <div class="form-actions">
              <div class="card-body border-top ">
                <input type="hidden" id="customer_id" name="customer_id" class="form-control"
                  value="<?= htmlspecialchars($currentUser['customer_id']) ?>" />
                <div class="row">

                  <div class="col-6 text-start">

                  </div>
                  <div class="col-6 text-end">
                    <button type="submit" class="btn btn-primary" style="border-radius: 10%;">Update Profile</button>
                  </div>
                </div>

              </div>
            </div>

          </form>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function () {
    $('#lineForm').on('submit', function (e) {
      e.preventDefault();

      $.ajax({
        url: 'crud/update_customer.php',  // PHP file to handle the update request
        type: 'POST',
        data: $(this).serialize(),
        success: function (response) {
          // Handle success response
          if (response.success) {
            alert('Customer updated successfully!');
            // Close modal, refresh data, or update UI as needed
            $('#updateCustomerModal').modal('hide');
            location.reload(); // Optional: Refresh page to show updated data
          } else {
            alert('Update failed: ' + response.message);
          }
        },
        error: function (xhr, status, error) {
          // Handle error response
          alert('An error occurred: ' + error);
        }
      });
    });
  });
</script>
