<?php
require 'includes/dbconn.php';
require 'includes/functions.php';

?>
<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0"> Warehouse Details</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a class="text-muted text-decoration-none" href="/">
                        Home
                    </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">
                    <a class="text-muted text-decoration-none" href="#">
                        Warehouses
                    </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Warehouse Details</li>
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

    <div class="widget-content searchable-container list">
    <!-- layout here -->
        <div class="col-12 ">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="card-body">
                                <div class="app-chat">
                                    <?php 
                                        $query_warehouse = "SELECT * FROM warehouses";
                                        $result_warehouse = mysqli_query($conn, $query_warehouse);            
                                        while ($row_warehouse = mysqli_fetch_array($result_warehouse)) {
                                        ?>
                                        <li>
                                            <a href="javascript:void(0)" class="px-4 py-3 bg-hover-light-black d-flex align-items-center chat-user bg-light-subtle" id="view_warehouse_btn" data-id="<?= $row_warehouse['WarehouseID'] ?>">
                                            
                                            <div class="ms-6 d-inline-block w-75">
                                                <h6 class="mb-1 fw-semibold chat-title" data-username="<?= $row_warehouse['WarehouseName'] ?>"><?= $row_warehouse['WarehouseName'] ?>
                                                </h6>
                                                <span class="fs-2 text-body-color d-block"><?= $row_warehouse['Location'] ?></span>
                                            </div>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card-body">
                                <!-- Warehouse Details -->
                                <div class="row">
                                    <div class="col">
                                        <div class="card">
                                            <div class="card-body">
                                                
                                                <h6 class="fw-semibold fs-4 mb-0">Warehouse Name</h6>
                                                <span class="mb-0">Warehouse Location</span>

                                                <div class="row mt-5">
                                                    <div class="col-4 mb-7">
                                                        <label class="form-label">Contact Person</label>
                                                        <input type="text" id="contact_person" name="contact_person" class="form-control" />
                                                    </div>
                                                    <div class="col-4 mb-7">
                                                        <label class="form-label">Contact Phone</label>
                                                        <input type="text" id="contact_phone" name="contact_phone" class="form-control" />
                                                    </div>
                                                    <div class="col-4 mb-7">
                                                        <label class="form-label">Contact Email</label>
                                                        <input type="text" id="contact_email" name="contact_email" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tables -->
                                <div class="row">
                                    <div class="datatables col">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4>List of Bins</h4>

                                                <div class="table-responsive">
                                                    <table>
                                                        <thead>
                                                            <tr>
                                                                <th>This is the Table Head</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>This is the Body</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="datatables col">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4>List of Rows</h4>

                                                <div class="table-responsive">
                                                    <table>
                                                        <thead>
                                                            <tr>
                                                                <th>This is the Table Head</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>This is the Body</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="datatables col">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4>List of Shelves</h4>

                                                <div class="table-responsive">
                                                    <table>
                                                        <thead>
                                                            <tr>
                                                                <th>This is the Table Head</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>This is the Body</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </div>
    </div>
</div>

<script>
    function getUrlParameter(name) {
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);
        return params.get(name);
    }

    $(document).ready(function() {
        var warehouse_id = "";

        $(document).on('click', '#view_warehouse_btn', function(event){
            event.preventDefault();
            warehouse_id = $(this).data('id'):
            $.ajax({
                url: 'pages/warehouse_ajax_details.php',
                type: 'POST',
                data: {
                    warehouse_id:warehouse_id,
                    action: "fetch_info"
                }
            })
        })
    });
</script>



