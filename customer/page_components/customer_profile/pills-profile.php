<div class="row">
  <div class="col-lg-12 mt-4">
    <div class="card shadow-none">
      <div class="card-body">

        <div class="row pt-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">First Name:</label>
              <span id="customer_first_name"><?= !empty($currentUser['customer_first_name']) ? htmlspecialchars($currentUser['customer_first_name']) : 'No data to show.' ?></span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Last Name:</label>
              <span id="customer_last_name"><?= !empty($currentUser['customer_last_name']) ? htmlspecialchars($currentUser['customer_last_name']) : 'No data to show.' ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Customer Email:</label>
              <span id="contact_email"><?= !empty($currentUser['contact_email']) ? htmlspecialchars($currentUser['contact_email']) : 'No data to show.' ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Customer Phone:</label>
              <span id="contact_phone"><?= !empty($currentUser['contact_phone']) ? htmlspecialchars($currentUser['contact_phone']) : 'No data to show.' ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Customer Fax:</label>
              <span id="contact_fax"><?= !empty($currentUser['contact_fax']) ? htmlspecialchars($currentUser['contact_fax']) : 'No data to show.' ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-12">
            <div class="mb-3">
              <label class="form-label">Customer Business Name:</label>
              <span id="customer_business_name"><?= !empty($currentUser['customer_business_name']) ? htmlspecialchars($currentUser['customer_business_name']) : 'No data to show.' ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-12">
            <div class="mb-3">
              <label class="form-label">Address:</label>
              <span id="address"><?= !empty($currentUser['address']) ? htmlspecialchars($currentUser['address']) : 'No data to show.' ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">City:</label>
              <span id="city"><?= !empty($currentUser['city']) ? htmlspecialchars($currentUser['city']) : 'No data to show.' ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">State:</label>
              <span id="state"><?= !empty($currentUser['state']) ? htmlspecialchars($currentUser['state']) : 'No data to show.' ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Zip:</label>
              <span id="zip"><?= !empty($currentUser['zip']) ? htmlspecialchars($currentUser['zip']) : 'No data to show.' ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Secondary Contact Name:</label>
              <span id="secondary_contact_name"><?= !empty($currentUser['secondary_contact_name']) ? htmlspecialchars($currentUser['secondary_contact_name']) : 'No data to show.' ?></span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Secondary Contact Phone:</label>
              <span id="secondary_contact_phone"><?= !empty($currentUser['secondary_contact_phone']) ? htmlspecialchars($currentUser['secondary_contact_phone']) : 'No data to show.' ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">AP Contact Name:</label>
              <span id="ap_contact_name"><?= !empty($currentUser['ap_contact_name']) ? htmlspecialchars($currentUser['ap_contact_name']) : 'No data to show.' ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">AP Contact Email:</label>
              <span id="ap_contact_email"><?= !empty($currentUser['ap_contact_email']) ? htmlspecialchars($currentUser['ap_contact_email']) : 'No data to show.' ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">AP Contact Phone:</label>
              <span id="ap_contact_phone"><?= !empty($currentUser['ap_contact_phone']) ? htmlspecialchars($currentUser['ap_contact_phone']) : 'No data to show.' ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Tax Exempt Number:</label>
              <span id="tax_exempt_number"><?= !empty($currentUser['tax_exempt_number']) ? htmlspecialchars($currentUser['tax_exempt_number']) : 'No data to show.' ?></span>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Customer Notes:</label>
          <span id="customer_notes"><?= !empty($currentUser['customer_notes']) ? nl2br(htmlspecialchars($currentUser['customer_notes'])) : 'No data to show.' ?></span>
        </div>

        <div class="row mb-3">
          <div class="col">
            <label class="form-label">Customer Call Status:</label>
            <span id="call_status"><?= $currentUser['call_status'] ? 'Yes' : 'No' ?></span>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
