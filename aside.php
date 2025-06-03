  <aside class="left-sidebar with-vertical">
      <div><!-- ---------------------------------- -->
        <!-- Start Vertical Layout Sidebar -->
        <!-- ---------------------------------- -->
        <!-- Sidebar scroll-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
          <!-----------Profile------------------>
          <div class="user-profile position-relative" style="background: url(assets/images/backgrounds/user-info.jpg) no-repeat;">
            <!-- User profile image -->
            <div class="profile-img">
              <img src="assets/images/profile/user-1.jpg" alt="user" class="w-100 rounded-circle overflow-hidden" />
            </div>
            <!-- User profile text-->
            <div class="profile-text hide-menu pt-1 dropdown">
              <a href="javascript:void(0)" class="dropdown-toggle u-dropdown w-100 text-white
                  d-block
                  position-relative
                " id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">Markarn Doe</a>
              <div class="dropdown-menu animated flipInY" aria-labelledby="dropdownMenuLink">
               
               
               
                <div class="dropdown-divider"></div>
                <a class="dropdown-item d-flex gap-2" href="../dark/page-account-settings.html">
                  <i data-feather="settings" class="feather-sm text-warning "></i>
                  Account Setting
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item d-flex gap-2" href="logout.php">
                  <i data-feather="log-out" class="feather-sm text-danger "></i>
                  Logout
                </a>
                <div class="dropdown-divider"></div>
                <div class="px-3 py-2">
                  <a href="../dark/page-user-profile.html" class="btn d-block w-100 btn-info rounded-pill">View Profile</a>
                </div>
              </div>
            </div>
          </div>
          <!-----------Profile End------------------>

          <ul id="sidebarnav">
        <!-- ---------------------------------- -->
        <!-- Sales -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Sales</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="/cashier2/">
            <iconify-icon icon="solar:pen-new-round-linear" class="aside-icon"></iconify-icon>
            <span class="hide-menu">New Sale</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=estimate_list">
            <iconify-icon icon="solar:document-linear" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Estimates</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=order_list">
            <iconify-icon icon="solar:wallet-money-linear" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Orders</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="estimate_list.php">
            <iconify-icon icon="solar:undo-left-round-linear" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Returns/Refunds</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=sales_list">
            <iconify-icon icon="solar:history-linear" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Sales History</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="invoices.php">
            <iconify-icon icon="solar:folder-with-files-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Invoices</span>
          </a>
        </li>

        <!-- ---------------------------------- -->
        <!-- Products -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Products</span>
        </li>
       <!-- <li class="sidebar-item">
          <a class="sidebar-link" href="add_product.php">
            <iconify-icon icon="solar:add-circle-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Add New Product</span>
          </a>
        </li>-->
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=product4">
            <iconify-icon icon="solar:clipboard-list-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Product List</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=inventory">
            <iconify-icon icon="solar:smartphone-update-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Inventory</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="discounts_promotions.php">
            <iconify-icon icon="solar:flag-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Discounts/Promotions</span>
          </a>
        </li>
       <!--   <li class="sidebar-item">
          <a class="sidebar-link" href="remove_product.php">
            <iconify-icon icon="solar:trash-bin-minimalistic-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Remove Product</span>
          </a>
        </li>-->
        
        <li class="sidebar-item">
          <a class="sidebar-link" href="add_barcode.php">
            <iconify-icon icon="solar:camera-add-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Print Barcode</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="add_barcode.php">
            <iconify-icon icon="solar:camera-add-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Print QR</span>
          </a>
        </li>
        <!-- Products -->
        <!-- ---------------------------------- -->
        
       <!-- ---------------------------------- -->
        <!-- Customers -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:add-square-outline" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Customers</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=customer">
            <iconify-icon icon="solar:add-square-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Customer</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=merge_customer">
            <iconify-icon icon="solar:checklist-minimalistic-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Merge Customer</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=loyalty_program">
            <iconify-icon icon="solar:graph-new-up-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Loyalty Program</span>
          </a>
        </li>

        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">EKM Tools</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#contactInfoModal">
            <iconify-icon icon="mdi:store-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Store Information</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" target="_blank" href="https://eastweb.ilearnwebtech.com/">
            <iconify-icon icon="mdi:web" class="aside-icon"></iconify-icon>
            <span class="hide-menu">EKM Website</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#color_chart_modal">
            <iconify-icon icon="mdi:palette-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">EKM Color Charts</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" target="_blank" href="https://postframesolver.azurewebsites.net/Account/Login?ReturnUrl=%2F">
            <iconify-icon icon="mdi:tools" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Smart Build</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" target="_blank" href="https://roofingpassport.b2clogin.com/roofingpassport.onmicrosoft.com/b2c_1a_signin_signup_i[%E2%80%A6]wtxRIH&state=a72d1f53944a0220a2328528d4b611eff9Hpe7qwE">
            <iconify-icon icon="mdi:home-roof" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Roofing Passport</span>
          </a>
        </li>

        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Supplier</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=product_supplier">
            <iconify-icon icon="solar:pen-new-round-linear" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Suppliers</span>
          </a>
        </li>

        <!-- ---------------------------------- -->
        <!-- Reports -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:presentation-graph-outline" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Reports</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="sales_reports.php">
            <iconify-icon icon="solar:presentation-graph-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Sales Reports</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="inventory_reports.php">
            <iconify-icon icon="solar:clipboard-list-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Inventory Reports</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="customer_reports.php">
            <iconify-icon icon="solar:user-hands-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Customer Reports</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="financial_reports.php">
            <iconify-icon icon="solar:chart-2-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Financial Reports</span>
          </a>
        </li>

        <!-- ---------------------------------- -->
        <!-- Employees -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Employees</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=staff">
            <iconify-icon icon="solar:user-hand-up-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Employees</span>
          </a>
        </li>
       <!-- <li class="sidebar-item">
          <a class="sidebar-link" href="employee_list.php">
            <iconify-icon icon="solar:notebook-minimalistic-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Employee List</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="update_employee.php">
            <iconify-icon icon="solar:restart-circle-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Update Employee</span>
          </a>
        </li>-->
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=employee_roles">
            <iconify-icon icon="solar:user-circle-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Employee Roles</span>
          </a>
        </li>
<!-- Products -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Warehouse</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=warehouses">
            <iconify-icon icon="la:warehouse" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Warehouses</span>
          </a>
        </li>

        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Products Properties</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=category">
            <iconify-icon icon="solar:server-minimalistic-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Categories</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=product_line">
            <iconify-icon icon="solar:server-linear" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Product Line</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="?page=product_type">
            <iconify-icon icon="octicon:dash-16" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Product Type</span>
          </a>
        </li>
    
        <!-- ---------------------------------- -->
        <!-- Settings -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Settings</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="general_settings.php">
            <iconify-icon icon="solar:settings-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">General Settings</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="tax_settings.php">
            <iconify-icon icon="solar:calculator-minimalistic-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Tax Settings</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="payment_methods.php">
            <iconify-icon icon="solar:money-bag-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Payment Methods</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="receipt_templates.php">
            <iconify-icon icon="solar:bill-check-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Receipt Templates</span>
          </a>
        </li>

        <!-- ---------------------------------- -->
        <!-- Help & Support -->
        <!-- ---------------------------------- -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Help & Support</span>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="user_manual.php">
            <iconify-icon icon="solar:book-2-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">User Manual</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="contact_support.php">
            <iconify-icon icon="solar:headphones-round-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">Contact Support</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="faqs.php">
            <iconify-icon icon="solar:question-square-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">FAQs</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="system_updates.php">
            <iconify-icon icon="solar:cloud-upload-outline" class="aside-icon"></iconify-icon>
            <span class="hide-menu">System Updates</span>
          </a>
        </li>
      </ul>
        </nav>

        <!-- End Sidebar scroll-->
      </div>
    </aside>