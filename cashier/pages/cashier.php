<!-- -------------------------------------------------------------- -->
<!-- Breadcrumb -->
<!-- -------------------------------------------------------------- -->
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
  <div class="card-body px-0">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h4 class="font-weight-medium fs-14 mb-0">Cashier</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a class="text-muted text-decoration-none" href="">Home
              </a>
            </li>
            <li class="breadcrumb-item text-muted" aria-current="page">Cashier</li>
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

<div class="main-container container-fluid">
    <!-- row -->
    <div class="row row-sm">
        <div class="col-xl-12 col-lg-12 col-md-12 mb-12 mb-md-0">
            <div class="card">
                <div class="card-body">
                    <div class="row row-xs">
                        <div class="col-md-8"></div>
                        <div class="col-md-4 bg-primary" id="thegrandtotal" style="text-align:center;font-size:48px;display: flex;align-items: center;justify-content: center;text-align: center;">Grand Total</div>
                    </div>

                    <div class="row row-xs">
                        <div class="col-md-2">
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon2"><i class="ti ti-shopping-cart"></i></span>
                                <input class="form-control form-control-lg numberonly" placeholder="QTY" type="text" size="1" id="qty">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="ti ti-layout-column4-alt"></i></span>
                                <input class="form-control form-control-lg" placeholder="Scan Barcode" type="text" id="barcode" onChange="readbarcode()" autofocus>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon3">
                                    <i class="ti ti-layout-column4-alt"></i>
                                </span>
                                <input class="form-control form-control-lg" placeholder="Type Barcode/Product(Shift+S)" type="text" id="autocomplete2" Onchange="addnote2()">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="demo">
                    <div class="card-body">
                        <div class="product-details table-responsive text-nowrap">
                            <table id="productTable" class="table table-hover mb-0 text-md-nowrap">
                                <thead>
                                    <tr>
                                        <th width="35%">Item Name</th>
                                        <th width="10%">Stock</th>
                                        <th width="20%">Quantity</th>
                                        <th width="10%">Price</th>
                                        <th width="10%">Subtotal</th>
                                        <th width="5%"><i class="ti ti-trash"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="4">
                                            <input type="text" class="form-control numberonly" value="">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5"></td>
                                        <td colspan="3">
                                            <div class="form-group mb-0 justify-content-end">
                                                <div class="checkbox">
                                                    <div class="form-checkbox custom-control">
                                                        <input type="checkbox" class="custom-control-input" value="">
                                                        <label for="vatexempt" class="custom-control-label mt-1">VAT Exempt</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5"></td>
                                        <td colspan="1">Amount Due:</td>
                                        <td colspan="1"><input type="text" class="form-control" value=""></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#cashmodal">Cash Payment</button>
                        <button class="btn btn-secondary" type="button" data-bs-toggle="modal" data-bs-target="#creditmodal">Credit Payment</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cash modal -->
<div class="modal" id="cashmodal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Cash Payment</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="cash_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card box-shadow-0">
                                <div class="card-body">
                                    <form>
                                        <div class="form-group">
                                            <label>Amount</label>
                                            <input type="text" class="form-control" id="cash_amount" onchange="update_cash()" value="">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body pricing">
                                    <ul class="list-unstyled leading-loose">
                                        <li><strong>Total Items: </strong>0</li>
                                        <li><strong>Total: </strong> 0.00</li>
                                        <li><strong>Discount(-): </strong> 0.00</li>
                                        <li><strong>Total Payable: </strong> 0.00</li>
                                        <li><strong>Balance: </strong> 0.00</li>
                                        <li class="list-group-item border-bottom-0 bg-primary" style="font-size:30px;">
                                            <strong>Change: </strong> 0.00
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="savecash" onclick="savecash()">
                    <i class="fe fe-hard-drive"></i> Save
                </button>
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Credit modal -->
<div class="modal" id="creditmodal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Credit Payment</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="cash_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card box-shadow-0">
                                <div class="card-body">
                                    <form>
                                        <div class="form-group">
                                            <label>Amount</label>
                                            <input type="text" class="form-control" id="credit_amount" onchange="update_credit()" value="">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body pricing">
                                    <ul class="list-unstyled leading-loose">
                                        <li><strong>Total Items: </strong>0</li>
                                        <li><strong>Total: </strong> 0.00</li>
                                        <li><strong>Discount(-): </strong> 0.00</li>
                                        <li><strong>Total Payable: </strong> 0.00</li>
                                        <li><strong>Balance: </strong> 0.00</li>
                                        <li class="list-group-item border-bottom-0 bg-primary" style="font-size:30px;">
                                            <strong>Change: </strong> 0.00
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="savecash" onclick="savecredit()">
                    <i class="fe fe-hard-drive"></i> Save
                </button>
                <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
  function showcash(){
    $("#cashmodal").modal('show');
    $("#cash_amount").focus();	
  }
  function showcredit(){
    $("#creditmodal").modal('show');
    $("#credit_amount").focus();	
  } 
  function readbarcode() {												
    var barcode = $("#barcode").val();
    $.ajax({
        url: 'pages/cashier_ajax.php',
        type: 'POST',
        data: {
            barcode: barcode
        },
        dataType: 'json',
        success: function(response) {
            if(response.status === 'success') {
                var tableBody = $('#productTable tbody');
                tableBody.empty();

                var item = response.data;
                var row = '<tr>' +
                    '<td>' + item.item_name + '</td>' +
                    '<td>' + item.quantity + '</td>' +
                    '<td>' +
                        '<div class="input-group">' +
                            '<span class="input-group-btn">' +
                                '<button class="btn btn-primary btn-icon m-1 py-1" type="button"><i class="ti ti-minus"></i></button>' +
                            '</span>' +
                            '<input class="form-control" type="text" size="5" value="' + item.quantity + '" style="color:#000000;">' +
                            '<span class="input-group-btn">' +
                                '<button class="btn btn-primary btn-icon m-1 py-1" type="button"><i class="ti ti-plus"></i></button>' +
                            '</span>' +
                        '</div>' +
                    '</td>' +
                    '<td>' + item.price + '</td>' +
                    '<td>' + 'Discount' + '</td>' +
                    '<td>' + (item.quantity * item.price).toFixed(2) + '</td>' +
                    '<td>' +
                        '<button class="btn btn-danger-gradient btn-sm" type="button"><i class="ti ti-trash"></i></button>' +
                        '<button class="btn btn-warning-gradient btn-sm" type="button"><i class="ti ti-reload"></i></button>' +
                    '</td>' +
                '</tr>';

                tableBody.prepend(row);
            } else {
                console.log('Error:', response.message);
            }
        },
        error: function(xhr, status, error) {
            //console.log('AJAX Error:', status, error);
            console.log(xhr.responseText)
        }
    }); 
  }
</script>



