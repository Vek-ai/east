<!-- -------------------------------------------------------------- -->
<!-- Breadcrumb -->
<!-- -------------------------------------------------------------- -->
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h4 class="font-weight-medium fs-14 mb-0">Dashboard</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Home
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Dashboard</li>
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

<!-- -------------------------------------------------------------- -->
<!-- Breadcrumb End -->
<!-- -------------------------------------------------------------- -->
<div class="row">
  <!-- text-cards -->
  <div class="col-lg-3 col-md-6">
    <div class="card">
      <div class="card-body p-9">
        <div class="hstack gap-9">
          <div class="round-56 rounded-circle text-white d-flex align-items-center justify-content-center text-bg-primary">
            <i class="ti ti-credit-card fs-6"></i>
          </div>
          <div class="align-self-center">
            <h3 class="mb-1 fs-6">$3249</h3>
            <span class="text-muted">Total Revenue</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card">
      <div class="card-body p-9">
        <div class="hstack gap-9">
          <div class="round-56 rounded-circle text-white d-flex align-items-center justify-content-center text-bg-secondary">
            <i class="ti ti-users fs-6"></i>
          </div>
          <div class="align-self-center">
            <h3 class="mb-1 fs-6">$2376</h3>
            <span class="text-muted">Online Revenue</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card">
      <div class="card-body p-9">
        <div class="hstack gap-9">
          <div class="round-56 rounded-circle text-white d-flex align-items-center justify-content-center text-bg-danger">
            <i class="ti ti-calendar fs-6"></i>
          </div>
          <div class="align-self-center">
            <h3 class="mb-1 fs-6">$1795</h3>
            <span class="text-muted">Offline Products</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card">
      <div class="card-body p-9">
        <div class="hstack gap-9">
          <div class="round-56 rounded-circle text-white d-flex align-items-center justify-content-center text-bg-warning">
            <i class="ti ti-settings fs-6"></i>
          </div>
          <div class="align-self-center">
            <h3 class="mb-1 fs-6">$687</h3>
            <span class="text-muted">Ad. Expense</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bandwidth cards -->
  <div class="col-lg-4">
    <div class="card overflow-hidden">
      <div class="card-body bg-purple">
        <div class="hstack gap-6 mb-7">
          <div class="bg-black bg-opacity-10 round-48 rounded-circle d-flex align-items-center justify-content-center">
            <iconify-icon icon="solar:server-square-linear" class="fs-7 icon-center text-white"></iconify-icon>
          </div>
          <div>
            <h4 class="card-title text-white">Bandwidth usage</h4>
            <p class="card-subtitle text-white opacity-70">March
              2024</p>
          </div>
        </div>
        <div class="row align-items-center">
          <div class="col-6">
            <h2 class="mb-0 text-white text-nowrap">50 GB</h2>
          </div>
          <div class="col-6">
            <div id="bandwidth-usage"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="card  overflow-hidden">
      <div class="card-body bg-secondary">
        <div class="hstack gap-6 mb-7">
          <div class="bg-white bg-opacity-20 round-48 rounded-circle d-flex align-items-center justify-content-center">
            <iconify-icon icon="solar:chart-2-linear" class="fs-7 icon-center text-white"></iconify-icon>
          </div>
          <div>
            <h4 class="card-title text-white">Download count</h4>
            <p class="card-subtitle text-white opacity-70">March
              2024</p>
          </div>
        </div>
        <div class="row align-items-center">
          <div class="col-5">
            <h2 class="mb-0 text-white text-nowrap">35487</h2>
          </div>
          <div class="col-7">
            <div id="download-count"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Our Visitors -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body pb-2">
        <h4 class="card-title">Our Visitors</h4>
        <p class="card-subtitle">Different Devices Used to
          Visit</p>
        <div id="our-visitors" class="mt-6"></div>
      </div>
      <div class="card-body pt-4 d-flex align-items-center justify-content-center border-top">
        <ul class="list-inline mb-0 hstack justify-content-center">
          <li class="list-inline-item px-2 me-0">
            <div class="text-primary d-flex align-items-center gap-2 fs-3">
              <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Mobile
            </div>
          </li>
          <li class="list-inline-item px-2 me-0">
            <div class="text-secondary d-flex align-items-center gap-2 fs-3">
              <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Tablet
            </div>
          </li>
          <li class="list-inline-item px-2 me-0">
            <div class="text-purple d-flex align-items-center gap-2 fs-3">
              <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Desktop
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <!-- Current Visitors -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Current Visitors</h4>
        <p class="card-subtitle">Different Devices Used to Visit</p>
        <div id="usa" class="h-280"></div>
        <div class="text-center">
          <ul class="list-inline mb-0 hstack justify-content-center">
            <li class="list-inline-item px-2 me-0">
              <div class="text-secondary d-flex align-items-center gap-2 fs-3">
                <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Valley
              </div>
            </li>
            <li class="list-inline-item px-2 me-0">
              <div class="text-primary d-flex align-items-center gap-2 fs-3">
                <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>New York
              </div>
            </li>
            <li class="list-inline-item px-2 me-0">
              <div class="text-danger d-flex align-items-center gap-2 fs-3">
                <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Kansas
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Blog Card -->
  <div class="col-lg-4">
    <div class="card blog-widget w-100">
      <div class="card-body p-2">
        <div class="blog-image">
          <img src="assets/images/backgrounds/blog-bg.jpg" height="273" alt="img" class="w-100 rounded" />
        </div>
        <div class="p-9">
          <div class="
            badge badge-pill
            bg-primary-subtle
            text-primary
            mb-6
          ">Technology</div>
          <h4 class="card-title">Business development new rules for 2023</h4>

          <p class="mb-6 truncate-2 text-muted">
            Lorem ipsum dolor sit amet, this is a consectetur
            adipisicing elit, sed do eiusmod tempor incididunt ut
          </p>
          <div class="d-flex justify-content-between align-items-center">
            <button class="
            btn btn-primary
            ">
              Read more
            </button>
            <div class="ms-auto">
              <a href="javascript:void(0)" class="link" data-bs-toggle="tooltip" title="Share"><iconify-icon icon="solar:share-linear" class="fs-7"></iconify-icon></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Newsletter Campaign -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center flex-wrap mb-9">
          <div>
            <h4 class="card-title">Newsletter Campaign</h4>
            <p class="card-subtitle">
              Overview of Newsletter Campaign
            </p>
          </div>
          <div class="ms-auto align-self-center">
            <ul class="d-flex align-items-center gap-3 mb-0">
              <li class="d-flex">
                <div class="text-primary d-flex align-items-center gap-2 fs-3">
                  <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Open Rate
                </div>
              </li>
              <li class="d-flex ">
                <div class="text-secondary d-flex align-items-center gap-2 fs-3">
                  <iconify-icon icon="ri:circle-fill" class="fs-2"></iconify-icon>Recurring
                  Payments
                </div>
              </li>
            </ul>

          </div>
        </div>
        <div class="me-n4 me-rtl-n4">
          <div id="newsletter-campaign"></div>
        </div>
        <div class="row text-center">
          <div class="col-lg-4 col-md-4 mt-2">
            <h2 class="mb-0">5098</h2>
            <small class="fs-3 text-muted">Total Sent</small>
          </div>
          <div class="col-lg-4 col-md-4 mt-2">
            <h2 class="mb-0">4156</h2>
            <small class="fs-3 text-muted">Mail Open Rate</small>
          </div>
          <div class="col-lg-4 col-md-4 mt-2">
            <h2 class="mb-0">1369</h2>
            <small class="fs-3 text-muted">Click Rate</small>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Projects of the Month -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body pb-3">
        <div class="d-md-flex no-block">
          <h4 class="card-title">Projects of the Month</h4>
          <div class="ms-auto">
            <select class="form-select">
              <option selected>January</option>
              <option value="1">February</option>
              <option value="2">March</option>
              <option value="3">April</option>
            </select>
          </div>
        </div>
        <div class="month-table">
          <div class="table-responsive mt-3">
            <table class="table align-middle  mb-0 no-wrap">
              <thead>
                <tr>
                  <th class="border-0 ps-0">
                    Client
                  </th>
                  <th class="border-0">Name</th>
                  <th class="border-0">
                    Priority
                  </th>
                  <th class="border-0 text-end">
                    Budget
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="ps-0">
                    <div class="hstack gap-3">
                      <span class="round-48 rounded-circle overflow-hidden flex-shrink-0 hstack justify-content-center">
                        <img src="assets/images/profile/user-2.jpg" alt class="img-fluid">
                      </span>
                      <div>
                        <h5 class="mb-1">Sunil Joshi</h5>
                        <p class="mb-0 fs-3">Web Designer</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="mb-0">Digital Agency</p>
                  </td>
                  <td>
                    <span class="badge bg-primary-subtle text-primary">Low</span>
                  </td>
                  <td class="text-end">
                    <p class="mb-0 fs-3">$3.9K</p>
                  </td>
                </tr>
                <tr>
                  <td class="ps-0">
                    <div class="hstack gap-3">
                      <span class="round-48 rounded-circle overflow-hidden flex-shrink-0 hstack justify-content-center">
                        <img src="assets/images/profile/user-4.jpg" alt class="img-fluid">
                      </span>
                      <div>
                        <h5 class="mb-1">Andrew Liock</h5>
                        <p class="mb-0 fs-3">Project Manager</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="mb-0">Real Homes</p>
                  </td>
                  <td>
                    <span class="badge bg-info-subtle text-info">Medium</span>
                  </td>
                  <td class="text-end">
                    <p class="mb-0 fs-3">$23.9K</p>
                  </td>
                </tr>
                <tr>
                  <td class="ps-0">
                    <div class="hstack gap-3">
                      <span class="round-48 rounded-circle overflow-hidden flex-shrink-0 hstack justify-content-center">
                        <img src="assets/images/profile/user-5.jpg" alt class="img-fluid">
                      </span>
                      <div>
                        <h5 class="mb-1">Biaca George</h5>
                        <p class="mb-0 fs-3">Developer</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="mb-0">MedicalPro Theme</p>
                  </td>
                  <td>
                    <span class="badge bg-secondary-subtle text-secondary">High</span>
                  </td>
                  <td class="text-end">
                    <p class="mb-0 fs-3">$12.9K</p>
                  </td>
                </tr>
                <tr>
                  <td class="border-bottom-0 ps-0">
                    <div class="hstack gap-3">
                      <span class="round-48 rounded-circle overflow-hidden flex-shrink-0 hstack justify-content-center">
                        <img src="assets/images/profile/user-6.jpg" alt class="img-fluid">
                      </span>
                      <div>
                        <h5 class="mb-1">Nirav Joshi</h5>
                        <p class="mb-0 fs-3">Frontend Eng</p>
                      </div>
                    </div>
                  </td>
                  <td class="border-bottom-0">
                    <p class="mb-0">Elite Admin</p>
                  </td>
                  <td class="border-bottom-0">
                    <span class="badge bg-danger-subtle text-danger">Very
                      High</span>
                  </td>
                  <td class="text-end border-bottom-0">
                    <p class="mb-0 fs-3">$2.6K</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Profile card -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body p-2">
        <img class="card-img-top w-100 profile-bg-height rounded overflow-hidden" src="assets/images/backgrounds/profile-bg.jpg" height="111" alt="Card image cap" />
        <div class="card-body little-profile text-center p-9">
          <div class="pro-img mb-3">
            <img src="assets/images/profile/user-2.jpg" alt="user" class="rounded-circle shadow-sm" width="112" />
          </div>
          <h3 class="mb-1 fs-14">Angelo Dominic</h3>
          <p class="fs-3 mb-4">Web Designer &amp; Developer</p>
          <a href="javascript:void(0)" class="
              btn btn-primary btn-md btn-rounded mb-7
            ">Follow</a>
          <div class="row gx-lg-4 text-center pt-4 justify-content-center border-top">
            <div class="col-4">
              <h3 class="mb-0 fs-14">1099</h3>
              <small class="text-muted fs-3">Articles</small>
            </div>
            <div class="col-4">
              <h3 class="mb-0 fs-14">23,469</h3>
              <small class="text-muted fs-3">Followers</small>
            </div>
            <div class="col-4">
              <h3 class="mb-0 fs-14">6035</h3>
              <small class="text-muted fs-3">Following</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Recent Comments -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <div class="d-md-flex align-items-center justify-content-between mb-4">
          <div class="mb-3 mb-md-0">
            <h4 class="card-title">Recent Comments</h4>
            <p class="card-subtitle">
              Latest Comments on users from Material
            </p>
          </div>
          <a href="javascript:void(0)" class="btn btn-primary">View
            All</a>
        </div>
        <!-- ============================================================== -->
        <!-- Comment widgets -->
        <!-- ============================================================== -->
        <div class="comment-widgets widgets">
          <!-- Comment Row -->
          <div class="comment-row hstack align-items-start gap-6 pb-9 border-bottom">
            <span class="round flex-shrink-0">
              <img src="assets/images/profile/user-10.jpg" class="rounded-circle" alt="user" width="44" height="44">
            </span>
            <div class="comment-text w-100">
              <h5 class="text-nowrap">
                James Anderson
              </h5>
              <p class="fs-3 mb-8">
                Lorem Ipsum is simply dummy text of the printing and
                type setting industry.
              </p>
              <div class="comment-footer d-md-flex align-items-center justify-content-between">
                <div class="hstack gap-6 mb-2 mb-md-0">
                  <span class="
                    badge
                    bg-warning-subtle
                    text-warning
                  ">Pending</span>
                  <ul class="action-icons list-unstyled mb-0 hstack gap-6">
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:pen-new-square-linear"></iconify-icon></a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:check-circle-linear"></iconify-icon></a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:heart-linear"></iconify-icon></a>
                    </li>
                  </ul>
                </div>
                <div class="ms-auto">
                  <span class="fs-3">April 14, 2024</span>
                </div>
              </div>
            </div>
          </div>
          <!-- Comment Row -->
          <div class="comment-row hstack align-items-start gap-6 py-9 border-bottom">
            <span class="round flex-shrink-0">
              <img src="assets/images/profile/user-6.jpg" class="rounded-circle" alt="user" width="44" height="44">
            </span>
            <div class="comment-text w-100">
              <h5 class="text-nowrap">
                Michael Jorden
              </h5>
              <p class="fs-3 mb-8">
                Lorem Ipsum is simply dummy text of the printing and
                type setting industry.
              </p>
              <div class="comment-footer d-md-flex align-items-center justify-content-between">
                <div class="hstack gap-6 mb-2 mb-md-0">
                  <span class="
                  badge
                  bg-secondary-subtle
                  text-secondary
                ">Approved</span>
                  <ul class="action-icons list-unstyled mb-0 hstack gap-6">
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:pen-new-square-linear"></iconify-icon></a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:check-circle-linear"></iconify-icon></a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:heart-linear"></iconify-icon></a>
                    </li>
                  </ul>
                </div>
                <div class="ms-auto">
                  <span class="fs-3">April 14, 2024</span>
                </div>
              </div>
            </div>
          </div>
          <!-- Comment Row -->
          <div class="comment-row hstack align-items-start gap-6 pt-9">
            <span class="round flex-shrink-0">
              <img src="assets/images/profile/user-12.jpg" class="rounded-circle" alt="user" width="44" height="44">
            </span>
            <div class="comment-text w-100">
              <h5 class="text-nowrap">
                Johnathan Doeting
              </h5>
              <p class="fs-3 mb-8">
                Lorem Ipsum is simply dummy text of the printing and
                type setting industry.
              </p>
              <div class="comment-footer d-md-flex align-items-center justify-content-between">
                <div class="hstack gap-6 mb-2 mb-md-0">
                  <span class="
                  badge
                  bg-danger-subtle
                  text-danger
                ">Rejected</span>
                  <ul class="action-icons list-unstyled mb-0 hstack gap-6">
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:pen-new-square-linear"></iconify-icon></a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:check-circle-linear"></iconify-icon></a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="d-flex fs-5"><iconify-icon icon="solar:heart-linear"></iconify-icon></a>
                    </li>
                  </ul>
                </div>
                <div class="ms-auto">
                  <span class="fs-3">April 14, 2024</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- To Do list -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body pb-4">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h4 class="card-title">To Do list</h4>
            <p class="card-subtitle">
              List of your next task to complete
            </p>
          </div>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
            Add Task
          </button>
          <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header d-flex">
                  <h4 class="modal-title">Add Task</h4>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form>
                    <div class="mb-3">
                      <label>Task name</label>
                      <input type="text" class="form-control" placeholder="Enter Task Name" />
                    </div>
                    <div class="mb-3">
                      <label>Assign to</label>
                      <select class="form-select">
                        <option selected>Sachin</option>
                        <option value="1">Sehwag</option>
                      </select>
                    </div>
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                  </button>
                  <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                    Submit
                  </button>
                </div>
              </div>
              <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
          </div>
        </div>

        <!-- -------------------------------------------------------------- -->
        <!-- To do list widgets -->
        <!-- -------------------------------------------------------------- -->
        <div class="to-do-widget common-widget">
          <!-- .modal for add task -->
          <!-- /.modal -->
          <ul class="list-task todo-list list-group mb-0" data-role="tasklist">
            <li class="list-group-item py-4 px-0 border-0 border-bottom" data-role="task">
              <div class="form-check form-check-inline w-100 me-0 mb-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <input type="checkbox" class="form-check-input primary check-light-primary" id="inputSchedule" name="inputCheckboxesSchedule" />
                  <div class="ms-3">
                    <label for="inputSchedule" class="form-check-label hstack gap-1">
                      <h5 class="mb-0 todo-desc">Schedule meeting
                        with</h5>
                      <span class="badge bg-primary-subtle text-primary lh-base">Today</span>
                    </label>
                    <p class="mb-0 fs-3 text-body mt-1">Phasellus
                      quis rutrum leo quis vulputate tortor...</p>
                  </div>
                </div>
                <div class="round-56 rounded-1 overflow-hidden">
                  <img src="assets/images/blog/blog-img2.jpg" alt="user" class="img-fluid h-100">
                </div>
              </div>
            </li>
            <li class="list-group-item py-4 px-0 border-0 border-bottom" data-role="task">
              <div class="form-check form-check-inline w-100 me-0 mb-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <input type="checkbox" class="form-check-input primary check-light-primary" id="inputSchedule2" name="inputCheckboxesSchedule" checked />
                  <div class="ms-3">
                    <label for="inputSchedule2" class="form-check-label hstack gap-1">
                      <h5 class="mb-0 todo-desc text-decoration-line-through">Forward
                        all tasks</h5>
                      <span class="badge bg-secondary-subtle text-secondary lh-base">Yesterday</span>
                    </label>
                    <p class="mb-0 fs-3 text-body mt-1">Mauris
                      cursus scelerisque felis et ultricies...</p>
                  </div>
                </div>
                <div class="round-56 rounded-1 overflow-hidden">
                  <img src="assets/images/blog/blog-img4.jpg" alt="user" class="img-fluid h-100">
                </div>
              </div>
            </li>
            <li class="list-group-item py-4 px-0 border-0 border-bottom" data-role="task">
              <div class="form-check form-check-inline w-100 me-0 mb-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <input type="checkbox" class="form-check-input primary check-light-primary" id="inputSchedule3" name="inputCheckboxesSchedule" />
                  <div class="ms-3">
                    <label for="inputSchedule3" class="form-check-label hstack gap-1">
                      <h5 class="mb-0 todo-desc">Give Purchase
                        report to</h5>
                      <span class="badge bg-danger-subtle text-danger lh-base">Tomorrow</span>
                    </label>
                    <p class="mb-0 fs-3 text-body mt-1">Vestibulum
                      non aliquet mi vitae mollis lorem...</p>
                  </div>
                </div>
                <div class="round-56 rounded-1 overflow-hidden">
                  <img src="assets/images/blog/blog-img3.jpg" alt="user" class="img-fluid h-100">
                </div>
              </div>
            </li>
            <li class="list-group-item py-4 px-0 border-0 border-bottom" data-role="task">
              <div class="form-check form-check-inline w-100 me-0 mb-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <input type="checkbox" class="form-check-input primary check-light-primary" id="inputSchedule4" name="inputCheckboxesSchedule" />
                  <div class="ms-3">
                    <label for="inputSchedule4" class="form-check-label hstack gap-1">
                      <h5 class="mb-0 todo-desc">Book flight for
                        holiday</h5>
                      <span class="badge bg-warning-subtle text-warning lh-base">1
                        Week</span>
                    </label>
                    <p class="mb-0 fs-3 text-body mt-1">Aenean
                      interdum auctor massa ut scelerisque...</p>
                  </div>
                </div>
                <div class="round-56 rounded-1 overflow-hidden">
                  <img src="assets/images/blog/blog-img1.jpg" alt="user" class="img-fluid h-100">
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="assets/js/breadcrumb/breadcrumbChart.js"></script>
<script src="assets/js/theme/sidebarmenu.js"></script>
<script src="assets/js/dashboards/dashboard2.js"></script>