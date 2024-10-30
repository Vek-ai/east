<div class="row">
  <div class="col-lg-12 mt-4">
    <div class="card shadow-none">
      <div class="card-body">
        <h4>Address</h4>
        <div class="table-responsive border rounded">
          <table id="productTable" class="table align-middle text-nowrap mb-0 table-fixed text-center">
            <thead>
              <tr>
                <th scope="col">Address</th>
                <th scope="col">City</th>
                <th scope="col">State</th>
                <th scope="col">Zip</th>
              </tr>
            </thead>
            <tbody id="productTableBody">
              <?php if ($currentUser): ?>
                <tr>
                  <td><?= htmlspecialchars($currentUser['address']) ?></td>
                  <td><?= htmlspecialchars($currentUser['city']) ?></td>
                  <td><?= htmlspecialchars($currentUser['state']) ?></td>
                  <td><?= htmlspecialchars($currentUser['zip']) ?></td>
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