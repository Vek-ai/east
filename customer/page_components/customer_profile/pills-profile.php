<div class="row">
  <div class="col-lg-12 mt-4">
    <div class="card shadow-none">
      <div class="card-body">

        <div class="row pt-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">First Name:</label>
              <span id="customer_first_name"><?= htmlspecialchars($currentUser['customer_first_name']) ?></span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Last Name:</label>
              <span id="customer_last_name"><?= htmlspecialchars($currentUser['customer_last_name']) ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Customer Email:</label>
              <span id="contact_email"><?= htmlspecialchars($currentUser['contact_email']) ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Customer Phone:</label>
              <span id="contact_phone"><?= htmlspecialchars($currentUser['contact_phone']) ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Customer Fax:</label>
              <span id="contact_fax"><?= htmlspecialchars($currentUser['contact_fax']) ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-12">
            <div class="mb-3">
              <label class="form-label">Customer Business Name:</label>
              <span id="customer_business_name"><?= htmlspecialchars($currentUser['customer_business_name']) ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-12">
            <div class="mb-3">
              <label class="form-label">Address:</label>
              <span id="address"><?= htmlspecialchars($currentUser['address']) ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">City:</label>
              <span id="city"><?= htmlspecialchars($currentUser['city']) ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">State:</label>
              <span id="state"><?= htmlspecialchars($currentUser['state']) ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Zip:</label>
              <span id="zip"><?= htmlspecialchars($currentUser['zip']) ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Secondary Contact Name:</label>
              <span id="secondary_contact_name"><?= htmlspecialchars($currentUser['secondary_contact_name']) ?></span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Secondary Contact Phone:</label>
              <span id="secondary_contact_phone"><?= htmlspecialchars($currentUser['secondary_contact_phone']) ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">AP Contact Name:</label>
              <span id="ap_contact_name"><?= htmlspecialchars($currentUser['ap_contact_name']) ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">AP Contact Email:</label>
              <span id="ap_contact_email"><?= htmlspecialchars($currentUser['ap_contact_email']) ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">AP Contact Phone:</label>
              <span id="ap_contact_phone"><?= htmlspecialchars($currentUser['ap_contact_phone']) ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Tax Exempt Number:</label>
              <span id="tax_exempt_number"><?= htmlspecialchars($currentUser['tax_exempt_number']) ?></span>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Customer Notes:</label>
          <span id="customer_notes"><?= nl2br(htmlspecialchars($currentUser['customer_notes'])) ?></span>
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
