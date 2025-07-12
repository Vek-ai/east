<!-- --- -->
<div class="modal" id="view_cart_modal">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Cart Contents</h6>
                <button aria-label="Close" class="close text-light" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="cart-tbl"></div>
            </div>
            <div class="">
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex flex-wrap justify-content-center">
                            <button type="button" class="btn mb-2 me-2 flex-fill" id="clear_cart" style="background-color: #dc3545; color: white;">
                                <i class="fa fa-trash fs-4 me-2"></i>
                                Clear Cart
                            </button>
                            <button type="button" class="btn mb-2 me-2 flex-fill" id="btnGradeModal" style="background-color: #6c757d; color: white;">
                                <i class="fa fa-chart-line fs-4 me-2"></i>
                                Change Grade
                            </button>
                            <button type="button" class="btn mb-2 me-2 flex-fill" id="btnColorModal" style="background-color: #17a2b8; color: white;">
                                <i class="fa fa-palette fs-4 me-2"></i>
                                Change Color
                            </button>
                            <button type="button" class="btn mb-2 me-2 flex-fill" id="btnApprovalModal" style="background-color: #800080; color: white;">
                                <i class="fa fa-check-circle fs-4 me-2"></i>
                                Save to My Projects
                            </button>
                            <button type="button" class="btn mb-2 me-2 flex-fill" id="view_order" style="background-color: #28a745; color: white;">
                                <i class="fa fa-shopping-cart fs-4 me-2"></i>
                                Next
                            </button>
                            <div class="col-auto mb-2">
                                <button type="button" class="btn btn-danger px-3" data-bs-dismiss="modal">
                                    <i class="fa fa-times fs-5 me-2"></i> Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="view_estimate_modal">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Save Estimate</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="estimate-tbl"></div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple btn-primary next" type="button" id="next_page_est">
                    <i class="fe fe-hard-drive"></i> Next
                </button>
                <button class="btn ripple btn-primary previous d-none" type="button" id="prev_page_est">
                    <i class="fe fe-hard-drive"></i> Previous
                </button>
                <button class="btn ripple btn-success d-none" type="button" id="save_estimate">
                    <i class="fe fe-hard-drive"></i> Save
                </button>
                <a href="#" class="btn ripple btn-light text-dark d-none" type="button" id="print_estimate_category" target="_blank">
                    <i class="fe fe-print"></i> Print Details
                </a>
                <a href="#" class="btn ripple btn-warning text-dark d-none" type="button" id="print_estimate" target="_blank">
                    <i class="fe fe-print"></i> Print Total
                </a>
                <button class="btn ripple btn-danger" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="cashmodal">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Checkout</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="order-tbl"></div>
            </div>
            <div class="modal-footer">
                <button class="btn ripple px-3" type="button" id="btnApprovalModal" style="background-color: #800080; color: white;">
                    Submit Approval
                </button>
                <button class="btn ripple btn-warning next" type="button" id="save_estimate">
                    Save Estimate
                </button>
                <button class="btn ripple btn-success" type="button" id="save_order_alt">
                    Place Order
                </button>
                <button class="btn ripple btn-primary previous d-none" type="button" id="prev_page_order">
                    <i class="fe fe-hard-drive"></i> Previous
                </button>
                <button class="btn ripple btn-success d-none" type="button" id="save_order">
                    <i class="fe fe-hard-drive"></i> Save
                </button>
                <a href="#" class="btn ripple btn-light text-dark d-none" type="button" id="print_order_category" target="_blank">
                    <i class="fe fe-print"></i> Print Details
                </a>
                <a href="#" class="btn ripple btn-warning text-dark d-none" type="button" id="print_order" target="_blank">
                    <i class="fe fe-print"></i> Print Total
                </a>
                <a href="#" class="btn ripple btn-info d-none" type="button" id="print_deliver" target="_blank">
                    <i class="fe fe-print"></i> Print Delivery
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="viewDetailsModal"></div>

<div class="modal" id="viewInStockmodal"></div>

<div class="modal" id="viewOutOfStockmodal"></div>

<div class="modal" id="viewAvailablemodal"></div>

<div class="modal" id="viewAvailableColormodal"></div>

<div class="modal fade" id="map1Modal" tabindex="-1" role="dialog" aria-labelledby="mapsModalLabel" aria-hidden="true" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapsModalLabel">Search Address</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="mapForm" class="form-horizontal">
              <div class="modal-body">
                  <div class="mb-2">
                      <input id="searchBox1" class="form-control" placeholder="<?= $addressDetails ?>" list="address1-list" autocomplete="off">
                      <datalist id="address1-list"></datalist>
                  </div>
                  <div id="map1" class="map-container" style="height: 60vh; width: 100%;"></div>
              </div>
              <div class="modal-footer">
                  <div class="form-actions">
                      <div class="card-body">
                          <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                      </div>
                  </div>
              </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div id="confirmHeaderContainer" class="modal-header align-items-center modal-colored-header">
                <h4 id="confirmHeader" class="text-center m-0 p-2 pb-0">Are you Sure?</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="text-muted px-2">Add the product to cart with <span class="text-warning">VENTED</span> Panel Type?</h5>
            </div>
            <div class="text-center mb-2">
                <button type="button" id="confirm_yes_btn" class="btn bg-success-subtle text-success waves-effect text-start">
                    Yes
                </button>
                <button type="button" class="btn bg-danger-subtle text-danger waves-effect text-start" data-bs-dismiss="modal">
                    No
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="approval_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header align-items-center modal-colored-header pb-0">
                <h4 id="responseHeader" class="m-0"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="text-center pt-0"> Are you sure you want to submit these items for approval?</h5>
            </div>
            <div class="text-center p-3">
                <button type="button" id="submitApprovalBtn" class="btn bg-success-subtle waves-effect text-start">
                    Yes
                </button>
                <button type="button" class="btn bg-danger-subtle waves-effect text-start" data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="response_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div id="responseHeaderContainer" class="modal-header align-items-center modal-colored-header">
            <h4 id="responseHeader" class="m-0"></h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-center" id="responseMsg"></p>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn bg-danger-subtle text-danger  waves-effect text-start" data-bs-dismiss="modal">
                Close
            </button>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" id="chng_color_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Color</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="change_color_container"></div>
            </div>
            <div class="modal-footer">
                <div class="form-actions">
                    <div class="card-body">
                        <button type="button" id="save_color_change" class="btn bg-success-subtle text-light waves-effect text-start">Save</button>
                        <button type="button" class="btn bg-danger-subtle text-light waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="chng_price_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Price Group</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="change_price_container"></div>
            </div>
            <div class="modal-footer">
                <div class="form-actions">
                    <div class="card-body">
                        <button type="button" id="save_price_change" class="btn bg-success-subtle text-light waves-effect text-start">Save</button>
                        <button type="button" class="btn bg-danger-subtle text-light waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="chng_grade_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Grade</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="change_grade_container"></div>
            </div>
                <div class="modal-footer">
                    <div class="form-actions">
                        <div class="card-body">
                            <button type="button" id="save_grade_change" class="btn bg-success-subtle text-light waves-effect text-start">Save</button>
                            <button type="button" class="btn bg-danger-subtle text-light waves-effect text-start" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            
        </div>
    </div>
</div>

<div class="modal fade" id="prompt_quantity_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form id="quantity_form" class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Metal Panel Configuration</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="qty_prompt_container"></div>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="trim_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form id="trim_form" class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Trim Configuration</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="trim_container"></div>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="custom_length_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form id="custom_length_form" class="modal-content modal-content-demo">
            <div class="modal-header">
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="custom_length_container"></div>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="custom_truss_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <form id="custom_truss_form" class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Custom Truss</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="custom_truss_container"></div>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="prompt_job_name_modal" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog" role="document">
        <form id="job_name_form" class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">New Job Name</h6>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="job_name_prompt_container">
                    <div class="job_name_input">
                        <div class="mb-2">
                            <label class="fs-5 fw-bold" for="job_name">Job Name</label>
                            <input id="job_name" name="job_name" class="form-control" placeholder="Enter Job Name" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success ripple btn-secondary" data-bs-dismiss="modal" type="submit">Save</button>
                <button class="btn btn-danger ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </form>
        </div>
    </div>
</div>

<script src="cart/cart_script.php"></script>
<!-- --- -->