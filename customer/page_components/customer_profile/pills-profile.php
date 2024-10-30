<div class="row">
  <div class="col-lg-12 mt-4">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="row pt-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">First Name</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['customer_first_name']) ?></span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Last Name</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['customer_last_name']) ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Customer Email</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['contact_email']) ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Customer Phone</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['contact_phone']) ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Customer Fax</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['contact_fax']) ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-12">
            <div class="mb-3">
              <label class="form-label">Customer Business Name</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['customer_business_name']) ?></span>
            </div>
          </div>
        </div>

        <!-- Tax Status and Address Information -->
        <div class="row pt-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Tax Exempt Number</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['tax_exempt_number']) ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-12">
            <div class="mb-3">
              <label class="form-label">Address</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['address']) ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">City</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['city']) ?></span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">State</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['state']) ?></span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Zip</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['zip']) ?></span>
            </div>
          </div>
        </div>

        <div class="row pt-3">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Secondary Contact Name</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['secondary_contact_name']) ?></span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Secondary Contact Phone</label>
              <span class="form-control"><?= htmlspecialchars($currentUser['secondary_contact_phone']) ?></span>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Customer Notes</label>
          <p class="form-control"><?= nl2br(htmlspecialchars($currentUser['customer_notes'])) ?></p>
        </div>

        <div class="row mb-3">
          <div class="col">
            <label class="form-label">Customer Call Status</label>
            <input type="checkbox" disabled <?= $currentUser['call_status'] ? 'checked' : '' ?>>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>