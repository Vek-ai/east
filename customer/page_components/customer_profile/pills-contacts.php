<div class="row">
  <div class="col-lg-12 mt-2">
    <div class="card shadow-none">
      <div class="card-body">
        <h4>Contacts</h4>
        <div class="table-responsive border rounded">
          <table id="productTable" class="table align-middle text-nowrap mb-0 table-fixed text-center">
            <thead>
              <tr>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Fax</th>
              </tr>
            </thead>
            <tbody id="productTableBody">
              <?php if ($currentUser): ?>
                <tr>
                  <td><?= htmlspecialchars($currentUser['contact_email']) ?></td>
                  <td><?= htmlspecialchars($currentUser['contact_phone']) ?></td>
                  <td><?= htmlspecialchars($currentUser['contact_fax']) ?></td>
                </tr>
              <?php else: ?>
                <tr>
                  <td colspan="4">No address data available.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-12">
    <div class="card shadow-none">
      <div class="card-body">
        <h4>Secondary Contacts</h4>
        <div class="table-responsive border rounded">
          <table id="productTable" class="table align-middle text-nowrap mb-0 table-fixed text-center">
            <thead>
              <tr>
                <th scope="col">Secondary Contacts Name</th>
                <th scope="col">Secondary Contacts Phone</th>
              </tr>
            </thead>
            <tbody id="productTableBody">
              <?php if ($currentUser): ?>
                <tr>
                  <td><?= htmlspecialchars($currentUser['secondary_contact_name']) ?></td>
                  <td><?= htmlspecialchars($currentUser['secondary_contact_phone']) ?></td>
                </tr>
              <?php else: ?>
                <tr>
                  <td colspan="4">No address data available.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-12">
    <div class="card shadow-none">
      <div class="card-body">
        <h4>AP Contacts</h4>
        <div class="table-responsive border rounded">
          <table id="productTable" class="table align-middle text-nowrap mb-0 table-fixed text-center">
            <thead>
              <tr>
                <th scope="col">AP Contacts Name</th>
                <th scope="col">AP Contacts Phone</th>
              </tr>
            </thead>
            <tbody id="productTableBody">
              <?php if ($currentUser): ?>
                <tr>
                  <td><?= htmlspecialchars($currentUser['ap_contact_name']) ?></td>
                  <td><?= htmlspecialchars($currentUser['ap_contact_phone']) ?></td>
                </tr>
              <?php else: ?>
                <tr>
                  <td colspan="4">No address data available.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>