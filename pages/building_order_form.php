<?php
if (!defined('APP_SECURE')) {
    header("Location: /not_authorized.php");
    exit;
}
require 'includes/dbconn.php';
require 'includes/functions.php';

$page_title = "Building Package Order Form";
$permission = $_SESSION['permission'];
?>

<style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: #f8f9fa;
    }
    .order-form-card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        padding: 25px 30px;
        margin-bottom: 30px;
    }
    .order-header {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0.75rem;
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 20px;
        padding-bottom: 10px;
    }
    .order-header h1 {
        font-size: 45px;
        margin: 0;
        color: #333;
    }
    .section {
        margin-bottom: 25px;
    }
    .section h2 {
        font-size: 18px;
        font-weight: 600;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 15px;
        padding-bottom: 5px;
        color: #444;
    }
    .form-check-label {
        margin-left: 5px;
    }
    .footer {
        font-size: 12px;
        text-align: center;
        color: #666;
        margin-top: 30px;
        line-height: 1.5;
    }
</style>

<div class="container my-5">
    <div class="order-form-card">

        <form method="post" action="save_building_package.php">
            <div class="order-header">
                <img src="assets/images/logo.png" alt="EKM Logo" style="height:80px;">
                <h1 class="fw-bold">Building Package Order Form</h1>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6 section">
                    <h2>Desired Building Size</h2>
                    <div class="mb-3">
                        <label class="form-label">Dimensions</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control" name="width" placeholder="Width">
                            <input type="text" class="form-control" name="length" placeholder="Length">
                            <input type="text" class="form-control" name="wall_height" placeholder="Wall Height">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Wall Framing</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="wall_framing[]" value="Post">
                                    <label class="form-check-label">Post</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="wall_framing[]" value="Stud">
                                    <label class="form-check-label">Stud</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Roof Pitch</label><br>
                                <input type="text" class="form-control w-50 d-inline-block" name="roof_pitch"> /12
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Foundation</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="foundation[]" value="Post Hole">
                                    <label class="form-check-label">Post Hole</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="foundation[]" value="Slab">
                                    <label class="form-check-label">Slab</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    

                    <div class="mb-3">
                        <label class="form-label">Truss Type & Style</label><br>

                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Wood</label><br>
                                <input type="text" class="form-control d-inline-block" name="truss_wood">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Steel</label><br>
                                <input type="text" class="form-control d-inline-block" name="truss_steel">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Overhang</label><br>
                                <input type="text" class="form-control d-inline-block" name="overhang">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Truss Spacing</label><br>
                            <input type="text" class="form-control d-inline-block" name="spacing">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Interior Walls?</label><br>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input" name="interior_walls" value="Yes"> Yes
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input" name="interior_walls" value="No"> No
                                </div>
                                <p class="mt-2 text-muted"><em>If Yes, Floor Plan Required</em></p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Slider Doors?</label><br>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input" name="slider_doors" value="Yes"> Yes
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input" name="slider_doors" value="No"> No
                                </div>
                                <div class="mt-2">
                                    <label class="form-label">If Yes, Details:</label>
                                    <input type="text" class="form-control" name="slider_details">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Wall Insulation</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="wall-insulation-bubble" name="wall_insulation[]" value="Bubble">
                            <label class="form-check-label" for="wall-insulation-bubble">Bubble</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="wall-insulation-underlayment" name="wall_insulation[]" value="Underlayment">
                            <label class="form-check-label" for="wall-insulation-underlayment">Underlayment</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="wall-insulation-fiberglass" name="wall_insulation[]" value="Fiberglass">
                            <label class="form-check-label" for="wall-insulation-fiberglass">Fiberglass</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="wall-insulation-none" name="wall_insulation[]" value="None">
                            <label class="form-check-label" for="wall-insulation-none">None</label>
                        </div>
                    </div>


                    <div class="mb-3">
                        <label class="form-label">Roof Insulation</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="roof_insulation[]" value="Bubble">
                            <label class="form-check-label">Bubble</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="roof_insulation[]" value="Underlayment">
                            <label class="form-check-label">Underlayment</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="roof_insulation[]" value="Fiberglass">
                            <label class="form-check-label">Fiberglass</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="roof_insulation[]" value="None">
                            <label class="form-check-label">None</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Roof Selection</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roof_selection[]" value="Low-Rib 29ga">
                                    <label class="form-check-label">Low-Rib 29ga</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roof_selection[]" value="Hi-Rib 26ga">
                                    <label class="form-check-label">Hi-Rib 26ga</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roof_selection[]" value="Corrugated">
                                    <label class="form-check-label">Corrugated (Galv. Only)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roof_selection[]" value="5V">
                                    <label class="form-check-label">5-V Crimp (Galv. Only)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roof_selection[]" value="Standing Seam 26ga">
                                    <label class="form-check-label">Standing Seam 26ga</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Wall Selection</label>
                                    <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="wall_selection[]" value="Low-Rib 29ga">
                                <label class="form-check-label">Low-Rib 29ga</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="wall_selection[]" value="Hi-Rib 26ga">
                                    <label class="form-check-label">Hi-Rib 26ga</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="wall_selection[]" value="Corrugated">
                                    <label class="form-check-label">Corrugated (Galv. Only)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="wall_selection[]" value="5V">
                                    <label class="form-check-label">5-V Crimp (Galv. Only)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="wall_selection[]" value="Board & Batten 26ga Narrow">
                                    <label class="form-check-label">Board & Batten 26ga Narrow</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="wall_selection[]" value="Board & Batten 26ga Wide">
                                    <label class="form-check-label">Board & Batten 26ga Wide</label>
                                </div>
                            </div>
                        </div>
                    </div>

                   <div class="mb-3">
                        <label class="form-label">#1 (Premier) or #2 (Economy) Metal?</label>
                        <input type="text" class="form-control" name="grade">
                        <p class="form-text fst-italic mt-1 small">
                            The Sherwin-Williams Company offers a limited paint warranty on #1 Metal coated with its WeatherXL Siliconized Polyester. 
                            EKM has made no warranty, explicit or implied, with respect to the WeatherXL paint or any product sold by EKM.
                        </p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Roof Color</label>
                            <input type="text" class="form-control" name="roof_color">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Wall Color</label>
                            <input type="text" class="form-control" name="wall_color">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Roof Trim Color</label>
                            <input type="text" class="form-control" name="roof_trim_color">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Wall Trim Color</label>
                            <input type="text" class="form-control" name="wall_trim_color">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label d-block">Wainscot</label>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" name="wainscot" value="Yes">
                                <label class="form-check-label">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" name="wainscot" value="No">
                                <label class="form-check-label">No</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Wainscot Color</label>
                            <input type="text" class="form-control" name="wainscot_color">
                        </div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="col-md-6 section">
                    <h2>Customer Information</h2>
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" class="form-control" name="customer_name"></div>
                    <div class="mb-3"><label class="form-label">Address</label><input type="text" class="form-control" name="customer_address"></div>
                    <div class="mb-3"><label class="form-label">Phone No.</label><input type="text" class="form-control" name="customer_phone"></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="text" class="form-control" name="customer_email"></div>
                    <div class="mb-3"><label class="form-label">Contractor Name & No.</label><input type="text" class="form-control" name="contractor"></div>

                    <div class="mb-3">
                        <label class="form-label">Preferred Contact</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="contact_method[]" value="Email">
                            <label class="form-check-label">Email</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="contact_method[]" value="Call">
                            <label class="form-check-label">Call</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="contact_method[]" value="Text">
                            <label class="form-check-label">Text</label>
                        </div>
                    </div>

                    <h2>Building Type</h2>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="building_type[]" value="Barndominium">
                                <label class="form-check-label">Barndominium</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="building_type[]" value="Barn">
                                <label class="form-check-label">Barn</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="building_type[]" value="Carport">
                                <label class="form-check-label">Carport</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="building_type[]" value="Garage/Shop">
                                <label class="form-check-label">Garage/Shop</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="building_type[]" value="Pavilion">
                                <label class="form-check-label">Pavilion</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="building_type[]" value="Metal Only">
                                <label class="form-check-label">Metal Only</label>
                            </div>
                        </div>
                    </div>

                    <h2 class="mt-4">Openings</h2>
                    <div class="mb-3">
                        <label class="form-label">Garage Doors</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control w-25" name="garage_doors_no" placeholder="No.">
                            <input type="text" class="form-control" name="garage_doors_size" placeholder="Size(s)">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Entry Doors</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control w-25" name="entry_doors_no" placeholder="No.">
                            <input type="text" class="form-control" name="entry_doors_size" placeholder="Size(s)">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Windows</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control w-25" name="windows_no" placeholder="No.">
                            <input type="text" class="form-control" name="windows_size" placeholder="Size(s)">
                        </div>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>Additional Details</h2>
                <textarea class="form-control" name="additional_details" rows="4"></textarea>
            </div>

            <div class="footer">
                MADE TO ORDER | METAL ROOFING & SIDING | FACTORY DIRECT PRICING<br>
                977 E. Hal Rogers Pkwy, London, KY | Mon-Fri 8am – 4:30pm | eastkentuckymetal.com<br>
                (606) 877-1848 | (606) 864-4280 (Fax) | sales@eastkentuckymetal.com<br>
                Homeowner 2025
            </div>

            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-primary px-4 py-2">Submit Order</button>
            </div>
        </form>
    </div>
</div>
