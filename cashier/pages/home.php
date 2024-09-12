<?php
require '../includes/dbconn.php';
require '../includes/functions.php';
?>
<style>
    .ui-autocomplete {
        background-color: #333;
        color: #fff;
        border: 1px solid #666;
    }

    .ui-autocomplete .ui-menu-item {
        padding: 8px;
    }

    .ui-autocomplete .ui-state-highlight {
        background-color: #555;
        color: #fff;
    }

    .ui-autocomplete {
        position: absolute;
        z-index: 1051 !important;
    }
</style>
<div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7 mt-3">
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
                        <?php if(isset($_SESSION["grandtotal"])){?>
                            <div class="col-md-4 bg-primary" id="thegrandtotal" style="text-align:center;font-size:48px;display: flex;align-items: center;justify-content: center;text-align: center;" >$<?php echo number_format($_SESSION["grandtotal"],2);?> </div>
                        <?php }else{ ?>
                            <div class="col-md-4 bg-primary" id="thegrandtotal" style="text-align:center;font-size:48px;display: flex;align-items: center;justify-content: center;text-align: center;" >$0.00 </div>
                        <?php } ?>
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
                                <input class="form-control form-control-lg" placeholder="Type Barcode/Product (Shift+S)" type="text" id="product_item" onchange="add_product()">
                                <input type='hidden' id='product_id' name="product_id"/>
                            </div>
                        </div>
                    </div>
                    <div class="row row-xs">
                        <div class="position-relative px-1 col-4">
                            <select class="form-control search-chat py-0 ps-5" id="select-color" data-category="">
                                <option value="" data-category="">All Colors</option>
                                <optgroup label="Product Colors">
                                    <?php
                                    $query_color = "SELECT * FROM paint_colors WHERE hidden = '0'";
                                    $result_color = mysqli_query($conn, $query_color);
                                    while ($row_color = mysqli_fetch_array($result_color)) {
                                    ?>
                                        <option value="<?= $row_color['color_id'] ?>" data-category="category"><?= $row_color['color_name'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="position-relative px-1 col-4">
                            <select class="form-control search-chat py-0 ps-5" id="select-grade" data-category="">
                                <option value="" data-category="">All Grades</option>
                                <optgroup label="Product Grades">
                                    <?php
                                    $query_grade = "SELECT * FROM product_grade WHERE hidden = '0'";
                                    $result_grade = mysqli_query($conn, $query_grade);
                                    while ($row_grade = mysqli_fetch_array($result_grade)) {
                                    ?>
                                        <option value="<?= $row_grade['product_grade_id'] ?>" data-category="line"><?= $row_grade['product_grade'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="position-relative px-1 col-4">
                            <select class="form-control search-chat py-0 ps-5" id="select-profile" data-category="">
                                <option value="" data-category="">All Profiles</option>
                                <optgroup label="Product Profiles">
                                    <?php
                                    $query_profile = "SELECT * FROM profile_type WHERE hidden = '0'";
                                    $result_profile = mysqli_query($conn, $query_profile);
                                    while ($row_profile = mysqli_fetch_array($result_profile)) {
                                    ?>
                                        <option value="<?= $row_profile['profile_type_id'] ?>" data-category="type"><?= $row_profile['profile_type'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div id="demo">
                    <div class="card-body">
                        <div class="product-details table-responsive text-nowrap">
                            <table id="productTable" class="table table-hover mb-0 text-md-nowrap">
                                <thead>
                                    <tr>
                                        <th width="20%">Description</th>
                                        <th width="13%" class="text-center">Color</th>
                                        <th width="13%" class="text-center">Grade</th>
                                        <th width="13%" class="text-center">Profile</th>
                                        <th width="20%" class="text-center pl-3">Quantity</th>
                                        <th width="5%" class="text-center">Stock</th>
                                        <th width="10%" class="text-center">Price</i></th>
                                        <th width="6%" class="text-center">Action</i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total = 0;
                                    $totalquantity = 0;
                                    if (!empty($_SESSION["cart"])) {
                                        foreach ($_SESSION["cart"] as $keys => $values) {
                                            $data_id = $values["product_id"];

                                            $totalstockquantity = $values["quantity_ttl"] + $values["quantity_in_stock"];

                                            if ($totalstockquantity > 0) {
                                                $stock_text = '
                                                    <a href="javascript:void(0);" id="view_product_details" data-id="' . htmlspecialchars($data_id, ENT_QUOTES, 'UTF-8') . '" class="d-flex align-items-center">
                                                        <span class="text-bg-success p-1 rounded-circle"></span>
                                                        <span class="ms-2">In Stock</span>
                                                    </a>';
                                            } else {
                                                $stock_text = '
                                                    <div class="d-flex align-items-center">
                                                        <span class="text-bg-danger p-1 rounded-circle"></span>
                                                        <span class="ms-2">Out of Stock</span>
                                                    </div>';
                                            } 
                                    ?>
                                            <tr>
                                                <td>
                                                    <?php echo $values["product_item"]; ?>
                                                </td>
                                                <td>
                                                    <?php echo getColorFromID($data_id); ?>
                                                    
                                                </td>
                                                <td>
                                                    <?php echo getGradeFromID($data_id); ?>
                                                    
                                                </td>
                                                <td>
                                                    <?php echo getProfileFromID($data_id); ?>
                                                    
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-primary btn-icon p-1 mr-1" type="button" data-id="<?php echo $data_id; ?>" onClick="deductquantity(this)">
                                                                <i class="fa fa-minus"></i>
                                                            </button>
                                                        </span> 
                                                        <input class="form-control" type="text" size="5" value="<?php echo $values["quantity_cart"]; ?>" style="color:#ffffff;" onchange="updatequantity(this)" data-id="<?php echo $data_id; ?>" id="item_quantity<?php echo $data_id;?>">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-primary btn-icon p-1 ml-1" type="button" data-id="<?php echo $data_id; ?>" onClick="addquantity(this)">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td><?= $stock_text ?></td>
                                                <td class="text-end pl-3">$
                                                    <?php
                                                    $subtotal = ($values["quantity_cart"] * $values["unit_price"]);
                                                    echo number_format($subtotal, 2);
                                                    ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-danger-gradient btn-sm" type="button" data-id="<?php echo $data_id; ?>" onClick="delete_item(this)"><i class="fa fa-trash"></i></button>
                                                    <input type="hidden" class="form-control" data-id="<?php echo $data_id; ?>" id="item_id<?php echo $data_id; ?>" value="<?php echo $values["product_id"]; ?>">
                                                    <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_ttl"];?>" id="warehouse_stock<?php echo $data_id;?>">
                                                    <input class="form-control" type="hidden" size="5" value="<?php echo $values["quantity_in_stock"];?>" id="store_stock<?php echo $data_id;?>">
                                                </td>
                                            </tr>
                                    <?php
                                            $totalquantity += $values["quantity_cart"];
                                            $total += $subtotal;
                                        }
                                        
                                    }
                                    $_SESSION["grandtotal"] = $total;
                                    ?>
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="1" class="text-end">Total Quantity:</td>
                                        <td colspan="1" class=""><span id="qty_ttl"><?= $totalquantity ?></span></td>
                                        <td colspan="1" class="text-end">Amount Due:</td>
                                        <td colspan="1" class="text-end"><span id="ammount_due"><?= $total ?> $</span></td>
                                        <td colspan="1"></td>
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

<!-- Product Details modal -->
<div class="modal" id="viewDetailsmodal"></div>

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
                <div id="checkout">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card box-shadow-0">
                                <div class="card-body">
                                    <form>
                                        <div class="form-group">
                                            <label>Customer Name</label>
                                            <div class="input-group">
                                                <input class="form-control" placeholder="Search Customer" type="text" id="customer_select_cash">
                                                    <a class="input-group-text rounded-right m-0 p-0" href="/cashier/?page=customer" target="_blank">
                                                        <span class="input-group-text"> + </span>
                                                    </a>
                                                <input type='hidden' id='customer_id_cash' name="customer_id"/>
                                            </div>
                                        </div>
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

<script>
    function add_product() {
        var product_id = $("#product_id").val();
        var qty = $("#qty").val();
        jQuery.ajax({
        url: "pages/cashier_ajax.php",
        data:"product_id="+product_id+"&qty="+qty,
        type: "POST",
        success:function(data){
            $("#barcode").focus();
            $("#qty").val('');
            $("#demo").load(location.href + " #demo");
            $("#thegrandtotal").load(location.href + " #thegrandtotal");
            $("#checkout").load(location.href + " #checkout");
            $("#barcode").focus();
            $('#product_item').val('');
            },
            error:function (){}
        });
        
    }
    function readbarcode() {
        var barcode = $("#barcode").val();
        var qty = $("#qty").val();
        jQuery.ajax({
            url: "pages/cashier_ajax.php",
            data:"barcode="+barcode+"&qty="+qty,
            type: "POST",
            success:function(data){
                if(data=='0'){
                    if(!alert('QUANTITY IS HIGHER THAN STORE STOCK')){location.reload(true);}
                } else if (data=='wrong') {
                    if(!alert('Incorrect Barcode')){location.reload(true);}
                }else {
                    $("#barcode").val("");
                    $("#qty").val('');
                    $("#demo").load(location.href + " #demo");
                    $("#thegrandtotal").load(location.href + " #thegrandtotal");
                    $("#checkout").load(location.href + " #checkout");
                    $("#barcode").val("");
                    $("#barcode").focus();
                }
            },
            error:function (){}
        });
    }

    function delete_item(element) {
        var id = $(element).data('id');
        $.ajax({
            url: "pages/cashier_ajax.php",
            data: {
                product_id_del: id,
                deleteitem: 'deleteitem'
            },
            type: "POST",
            success: function(data) {
                location.reload(true);
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
                $("#checkout").load(location.href + " #checkout");
            },
            error: function() {}
        });
    }

    function updatequantity(element) {
        var id = $(element).data('id');
        var item = Number($("#item_quantity" + id).val());
        var warehouse = Number($("#warehouse_stock" + id).val());
        var store = Number($("#store_stock" + id).val());
        var product_id_update = $("#item_id" + id).val();
        if (item > (store + warehouse)) {
            $("#item_quantity" + id).css({ "border-color": "red" });
            alert("QUANTITY IS HIGHER THAN STORE AND WAREHOUSE STOCK");
            $("#demo").load(location.href + " #demo");
        }else{
            $.ajax({
                url: "pages/cashier_ajax.php",
                data: {
                    product_id_update: product_id_update,
                    item_quantity: item,
                    update_qty: 'update_qty'
                },
                type: "POST",
                success: function(data) {
                    $("#demo").load(location.href + " #demo");
                    $("#thegrandtotal").load(location.href + " #thegrandtotal");
                    $("#checkout").load(location.href + " #checkout");
                },
                error: function() {}
            });
        }
    }

    function addquantity(element) {
        var id = $(element).data('id');
        var item = Number($("#item_quantity" + id).val());
        var product_id_update = $("#item_id" + id).val();
        $.ajax({
            url: "pages/cashier_ajax.php",
            data: {
                product_id_update: product_id_update,
                addquantity: 'addquantity',
                update_qty: 'update_qty',
                item_quantity: item
            },
            type: "POST",
            success: function(data) {
                if (data == "greater") {
                    $("#item_quantity" + id).css({ "border-color": "red" });
                    alert("QUANTITY IS HIGHER THAN STORE STOCK");
                } else {
                    $("#demo").load(location.href + " #demo");
                    $("#thegrandtotal").load(location.href + " #thegrandtotal");
                    $("#checkout").load(location.href + " #checkout");
                }
            },
            error: function() {}
        });
    }


    function deductquantity(element) {
        var id = $(element).data('id');
        var item = Number($("#item_quantity" + id).val());
        var product_id_update = $("#item_id" + id).val();
        $.ajax({
            url: "pages/cashier_ajax.php",
            data: {
                product_id_update: product_id_update,
                deductquantity: 'deductquantity',
                update_qty: 'update_qty'
            },
            type: "POST",
            success: function() {
                $("#demo").load(location.href + " #demo");
                $("#thegrandtotal").load(location.href + " #thegrandtotal");
                $("#checkout").load(location.href + " #checkout");
            },
            error: function() {}
        });
    }

    // Show the View Product modal and log the product ID
    $(document).on('click', '#view_product_details', function(event) {
        event.preventDefault();
        var id = $(this).data('id');
        $.ajax({
                url: 'pages/cashier_ajax.php',
                type: 'POST',
                data: {
                    id: id,
                    fetch_view_modal: "fetch_view_modal"
                },
                success: function(response) {
                    $('#viewDetailsmodal').html(response);
                    $('#viewDetailsmodal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus + ' - ' + errorThrown);
                }
        });
    });

    $("#product_item").autocomplete({ 
        source: function(request, response) {
            var color_id = $('#select-color').find('option:selected').val();
            var grade_id = $('#select-grade').find('option:selected').val();
            var profile_id = $('#select-profile').find('option:selected').val();
            $.ajax({
                url: "pages/cashier_ajax.php",
                type: 'post',
                dataType: "json",
                data: {
                    search: request.term,
                    color_id: color_id,
                    grade_id: grade_id,
                    profile_id, profile_id
                },
                success: function(data) {
                    response(data);
                },
                error: function(xhr, status, error) {
                    console.log("AJAX Error:", status, error);
                }
            });
        },
        select: function(event, ui) {
            $('#product_item').val(ui.item.label);
            $('#product_id').val(ui.item.value);
            return false;
        }
    });

    $("#customer_select_cash").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/cashier_ajax.php",
                type: 'post',
                dataType: "json",
                data: {
                    search_customer: request.term
                },
                success: function(data) {
                    response(data);
                },
                error: function(xhr, status, error) {
                    console.log("Error: " + xhr.responseText);
                }
            });
        },
        select: function(event, ui) {
            $('#customer_select_cash').val(ui.item.label);
            $('#customer_id_cash').val(ui.item.value);
            return false;
        },
        focus: function(event, ui) {
            $('#customer_select_cash').val(ui.item.label);
            return false;
        },
        appendTo: "#cashmodal", 
        open: function() {
            $(".ui-autocomplete").css("z-index", 1050);
        }
    });

    $("#customer_select_credit").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "pages/cashier_ajax.php",
                type: 'post',
                dataType: "json",
                data: {
                    search_customer: request.term
                },
                success: function(data) {
                    response(data);
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        },
        select: function(event, ui) {
            $('#customer_select_credit').val(ui.item.label);
            $('#customer_id_credit').val(ui.item.value);
            return false;
        },
        focus: function(event, ui) {
            $('#customer_select_credit').val(ui.item.label);
            return false;
        },
        appendTo: "#creditmodal", 
        open: function() {
            $(".ui-autocomplete").css("z-index", 1050);
        }
    });

    $(document).ready(function() {
        $('#select-color').select2({
            placeholder: 'Select a color',
            allowClear: true
        });
        $('#select-grade').select2({
            placeholder: 'Select a Grade',
            allowClear: true
        });
        $('#select-profile').select2({
            placeholder: 'Select a Profile',
            allowClear: true
        });
    });
</script>



