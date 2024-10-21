<?php
require 'includes/dbconn.php';
require 'includes/functions.php';
?>

<div class="container-fluid">
    <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
    <div class="card-body px-0">
        <div class="d-flex justify-content-between align-items-center">
        <div><br>
            <h4 class="font-weight-medium fs-14 mb-0">Sales List</h4>
            <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                <a class="text-muted text-decoration-none" href="">Sales
                </a>
                </li>
                <li class="breadcrumb-item text-muted" aria-current="page">Sales List</li>
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

    <div class="card card-body">
        <div class="card-body">
            <div class="input-group mb-3">
                <label for="customer_search" class="form-label mr-2">Customer Name</label>
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input class="form-control" placeholder="Search Customer" type="text" id="customer_search">
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="input-group mb-3">
                        <label for="date_from" class="form-label mr-2">Date From</label>
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" class="form-control" id="date_from">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-3">
                        <label for="date_to" class="form-label mr-2">Date To</label>
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" class="form-control" id="date_to">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="datatables">
                    <div class="product-details table-responsive text-nowrap">
                        <table id="sales_table" class="table table-hover mb-0 text-md-nowrap">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Purchase Date</th>
                                    <th>Time</th>
                                    <th>Terminal</th>
                                    <th>Cashier</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>INV1001</td>
                                    <td>2024-10-21</td>
                                    <td>14:35</td>
                                    <td>Terminal 1</td>
                                    <td>John</td>
                                    <td>Maria Garcia</td>
                                    <td>$150.00</td>
                                </tr>
                                <tr>
                                    <td>INV1002</td>
                                    <td>2024-10-21</td>
                                    <td>15:20</td>
                                    <td>Terminal 2</td>
                                    <td>Anna</td>
                                    <td>Carlos Ramirez</td>
                                    <td>$45.00</td>
                                </tr>
                                <tr>
                                    <td>INV1003</td>
                                    <td>2024-10-20</td>
                                    <td>16:00</td>
                                    <td>Terminal 3</td>
                                    <td>Mark</td>
                                    <td>John Smith</td>
                                    <td>$200.50</td>
                                </tr>
                                <tr>
                                    <td>INV1004</td>
                                    <td>2024-10-20</td>
                                    <td>17:15</td>
                                    <td>Terminal 4</td>
                                    <td>Sarah</td>
                                    <td>Linda Lee</td>
                                    <td>$75.75</td>
                                </tr>
                                <tr>
                                    <td>INV1005</td>
                                    <td>2024-10-19</td>
                                    <td>10:05</td>
                                    <td>Terminal 1</td>
                                    <td>John</td>
                                    <td>Alex White</td>
                                    <td>$120.00</td>
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