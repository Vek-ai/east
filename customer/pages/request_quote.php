<?php
$page_title = "Request Quote";

$formData = null;
$attachments = [];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$wallInsulation = [];
$roofInsulation = [];
$roofSelection  = [];
$wallSelection  = [];
$buildingType   = [];

if ($id > 0) {
    $sql = "SELECT * FROM building_form WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $formData = mysqli_fetch_assoc($result);
    }

    $res = mysqli_query($conn, "SELECT * FROM building_form_attachments WHERE building_form_id = $id");
    while ($row = mysqli_fetch_assoc($res)) {
        $attachments[] = $row;
    }

    $formData = $formData ?? null;
    $attachments = $attachments ?? [];

    $wallInsulation = $formData ? json_decode($formData['wall_insulation'] ?? '[]', true) : [];
    $roofInsulation = $formData ? json_decode($formData['roof_insulation'] ?? '[]', true) : [];
    $roofSelection  = $formData ? json_decode($formData['roof_selection'] ?? '[]', true) : [];
    $wallSelection  = $formData ? json_decode($formData['wall_selection'] ?? '[]', true) : [];
    $buildingType   = $formData ? json_decode($formData['building_type'] ?? '[]', true) : [];

    $primary_contact = null;

    if (!empty($formData['contact_method'])) {
        $primary_contact = $formData['contact_method'];

    } else {
        $customer_id = null;
        if (!empty($formData['customer_id'])) {
            $customer_id = (int)$formData['customer_id'];
        } elseif (!empty($_SESSION['customer_id'])) {
            $customer_id = (int)$_SESSION['customer_id'];
        }

        if ($customer_id) {
            $sql_customer = "SELECT * FROM customer WHERE customer_id = $customer_id AND hidden = 0 LIMIT 1";
            $res_customer = mysqli_query($conn, $sql_customer);

            if ($res_customer && mysqli_num_rows($res_customer) > 0) {
                $customerData = mysqli_fetch_assoc($res_customer);

                if ($customerData['primary_contact'] == 0) {
                    $primary_contact = 'email';
                } elseif ($customerData['primary_contact'] == 1) {
                    $primary_contact = 'text';
                }
            }
        }
    }

    if (!$primary_contact) {
        $primary_contact = 'email';
    }

    $formData['customer_name']   = $formData['customer_name']   ?? trim(($customerData['customer_business_name'] ?: $customerData['customer_first_name'].' '.$customerData['customer_last_name']));
    $formData['customer_address']= $formData['customer_address']?? ($customerData['address'] ?? '');
    $formData['customer_phone']  = $formData['customer_phone']  ?? ($customerData['contact_phone'] ?? '');
    $formData['customer_email']  = $formData['customer_email']  ?? ($customerData['contact_email'] ?? '');
    $formData['contractor']      = $formData['contractor']      ?? ($customerData['secondary_contact_name'].' '.$customerData['secondary_contact_phone']);
}
?>

<style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: #f8f9fa;
    }
    .order-form-card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        padding: 20px;
        margin-bottom: 30px;
    }
    .order-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 20px;
        padding-bottom: 10px;
    }
    .order-header h1 {
        font-size: 2rem;
        margin: 0;
        color: #333;
        flex: 1 1 auto;
    }
    .section h2 {
        font-size: 1.25rem;
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

    @media (max-width: 767px) {
        .order-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .order-header img {
            height: 60px;
        }
        .order-header h1 {
            font-size: 1.5rem;
        }
        .d-flex.gap-2 {
            flex-direction: column !important;
        }
        .form-control.w-25 {
            width: 100% !important;
        }
        .row > [class*="col-"] {
            margin-bottom: 15px;
        }
    }
</style>

<div class="container my-5">
    <div class="order-form-card">

        <form class="sharp_form">
            <div class="order-header">
                <h1 class="fw-bold">Building Package Order Form</h1>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6 section">
                    <h2>Desired Building Size</h2>
                    <div class="mb-3">
                        <label class="form-label">Dimensions</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control" name="width" placeholder="Width"
                                value="<?= htmlspecialchars($formData['width'] ?? '') ?>">
                            <input type="text" class="form-control" name="length" placeholder="Length"
                                value="<?= htmlspecialchars($formData['length'] ?? '') ?>">
                            <input type="text" class="form-control" name="wall_height" placeholder="Wall Height"
                                value="<?= htmlspecialchars($formData['wall_height'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Wall Framing</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="wall_framing" value="Post"
                                        <?= ($formData['wall_framing'] ?? '') === 'Post' ? 'checked' : '' ?>>
                                    <label class="form-check-label">Post</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="wall_framing" value="Stud"
                                        <?= ($formData['wall_framing'] ?? '') === 'Stud' ? 'checked' : '' ?>>
                                    <label class="form-check-label">Stud</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Roof Pitch</label><br>
                                <input type="text" class="form-control w-50 d-inline-block" name="roof_pitch"
                                    value="<?= htmlspecialchars($formData['roof_pitch'] ?? '') ?>"> /12
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Foundation</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="foundation" value="Post Hole"
                                        <?= ($formData['foundation'] ?? '') === 'Post Hole' ? 'checked' : '' ?>>
                                    <label class="form-check-label">Post Hole</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="foundation" value="Slab"
                                        <?= ($formData['foundation'] ?? '') === 'Slab' ? 'checked' : '' ?>>
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
                                <input type="text" class="form-control d-inline-block" name="truss_wood"
                                    value="<?= htmlspecialchars($formData['truss_wood'] ?? '') ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Steel</label><br>
                                <input type="text" class="form-control d-inline-block" name="truss_steel"
                                    value="<?= htmlspecialchars($formData['truss_steel'] ?? '') ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Overhang</label><br>
                                <input type="text" class="form-control d-inline-block" name="overhang"
                                    value="<?= htmlspecialchars($formData['overhang'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Truss Spacing</label><br>
                            <input type="text" class="form-control d-inline-block" name="spacing"
                                value="<?= htmlspecialchars($formData['spacing'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Interior Walls?</label><br>
                                <div class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input" name="interior_walls" value="Yes"
                                        <?= ($formData['interior_walls'] ?? '') === 'Yes' ? 'checked' : '' ?>> Yes
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input" name="interior_walls" value="No"
                                        <?= ($formData['interior_walls'] ?? '') === 'No' ? 'checked' : '' ?>> No
                                </div>
                                <p class="mt-2 text-muted"><em>If Yes, Floor Plan Required</em></p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Slider Doors?</label><br>
                                <div class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input" name="slider_doors" value="Yes"
                                        <?= ($formData['slider_doors'] ?? '') === 'Yes' ? 'checked' : '' ?>> Yes
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input" name="slider_doors" value="No"
                                        <?= ($formData['slider_doors'] ?? '') === 'No' ? 'checked' : '' ?>> No
                                </div>
                                <div class="mt-2">
                                    <label class="form-label">If Yes, Details:</label>
                                    <input type="text" class="form-control" name="slider_details"
                                        value="<?= htmlspecialchars($formData['slider_details'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Wall Insulation -->
                    <div class="mb-3">
                        <label class="form-label d-block">Wall Insulation</label>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <?php
                            $wallOptions = ['Bubble','Underlayment','Fiberglass','None'];
                            foreach ($wallOptions as $opt): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        id="wall-insulation-<?= strtolower($opt) ?>"
                                        name="wall_insulation[]" value="<?= $opt ?>"
                                        <?= in_array($opt, $wallInsulation ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="wall-insulation-<?= strtolower($opt) ?>"><?= $opt ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Roof Insulation -->
                    <div class="mb-3">
                        <label class="form-label d-block">Roof Insulation</label>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <?php
                            $roofOptions = ['Bubble','Underlayment','Fiberglass','None'];
                            foreach ($roofOptions as $opt): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        id="roof-insulation-<?= strtolower($opt) ?>"
                                        name="roof_insulation[]" value="<?= $opt ?>"
                                        <?= in_array($opt, $roofInsulation ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="roof-insulation-<?= strtolower($opt) ?>"><?= $opt ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Roof Selection -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Roof Selection</label>
                                <?php
                                $roofChoices = ['Low-Rib 29ga','Hi-Rib 26ga','Corrugated','5V','Standing Seam 26ga'];
                                foreach ($roofChoices as $opt): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roof_selection[]" value="<?= $opt ?>"
                                            <?= in_array($opt, $roofSelection ?? []) ? 'checked' : '' ?>>
                                        <label class="form-check-label"><?= $opt ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Wall Selection -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Wall Selection</label>
                                <?php
                                $wallChoices = [
                                    'Low-Rib 29ga','Hi-Rib 26ga','Corrugated','5V',
                                    'Board & Batten 26ga Narrow','Board & Batten 26ga Wide'
                                ];
                                foreach ($wallChoices as $opt): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="wall_selection[]" value="<?= $opt ?>"
                                            <?= in_array($opt, $wallSelection ?? []) ? 'checked' : '' ?>>
                                        <label class="form-check-label"><?= $opt ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    
                </div>

                <!-- Right Column -->
                <div class="col-md-6 section">
                    <!-- Other fields -->
                    <div class="mb-3">
                        <label class="form-label">#1 (Premier) or #2 (Economy) Metal?</label>
                        <input type="text" class="form-control" name="grade"
                            value="<?= htmlspecialchars($formData['grade'] ?? '') ?>">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Roof Color</label>
                            <input type="text" class="form-control" name="roof_color"
                                value="<?= htmlspecialchars($formData['roof_color'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Wall Color</label>
                            <input type="text" class="form-control" name="wall_color"
                                value="<?= htmlspecialchars($formData['wall_color'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Roof Trim Color</label>
                            <input type="text" class="form-control" name="roof_trim_color"
                                value="<?= htmlspecialchars($formData['roof_trim_color'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Wall Trim Color</label>
                            <input type="text" class="form-control" name="wall_trim_color"
                                value="<?= htmlspecialchars($formData['wall_trim_color'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label d-block">Wainscot</label>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" name="wainscot" value="Yes"
                                    <?= ($formData['wainscot'] ?? '') === 'Yes' ? 'checked' : '' ?>>
                                <label class="form-check-label">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input" name="wainscot" value="No"
                                    <?= ($formData['wainscot'] ?? '') === 'No' ? 'checked' : '' ?>>
                                <label class="form-check-label">No</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Wainscot Color</label>
                            <input type="text" class="form-control" name="wainscot_color"
                                value="<?= htmlspecialchars($formData['wainscot_color'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="d-none">
                        <h2>Customer Information</h2>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="customer_name"
                                value="<?= htmlspecialchars($formData['customer_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="customer_address"
                                value="<?= htmlspecialchars($formData['customer_address'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone No.</label>
                            <input type="text" class="form-control" name="customer_phone"
                                value="<?= htmlspecialchars($formData['customer_phone'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="text" class="form-control" name="customer_email"
                                value="<?= htmlspecialchars($formData['customer_email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contractor Name & No.</label>
                            <input type="text" class="form-control" name="contractor"
                                value="<?= htmlspecialchars($formData['contractor'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preferred Contact</label><br>
                            <?php $contact = $formData['contact_method'] ?? ''; ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="contact_method" value="email"
                                    <?= $contact === 'email' ? 'checked' : '' ?>>
                                <label class="form-check-label">Email</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="contact_method" value="call"
                                    <?= $contact === 'call' ? 'checked' : '' ?>>
                                <label class="form-check-label">Call</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="contact_method" value="text"
                                    <?= $contact === 'text' ? 'checked' : '' ?>>
                                <label class="form-check-label">Text</label>
                            </div>
                        </div>
                    </div>

                    <h2>Building Type</h2>
                    <div class="row">
                        <?php 
                        $types = ["Barndominium","Barn","Carport","Garage/Shop","Pavilion","Metal Only"];
                        foreach ($types as $t): ?>
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="building_type[]" value="<?= $t ?>"
                                        <?= in_array($t, $buildingType) ? 'checked' : '' ?>>
                                    <label class="form-check-label"><?= $t ?></label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <h2 class="mt-4">Openings</h2>
                    <div class="mb-3">
                        <label class="form-label">Garage Doors</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control w-25" name="garage_doors_no" placeholder="No."
                                value="<?= htmlspecialchars($formData['garage_doors_no'] ?? '') ?>">
                            <input type="text" class="form-control" name="garage_doors_size" placeholder="Size(s)"
                                value="<?= htmlspecialchars($formData['garage_doors_size'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Entry Doors</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control w-25" name="entry_doors_no" placeholder="No."
                                value="<?= htmlspecialchars($formData['entry_doors_no'] ?? '') ?>">
                            <input type="text" class="form-control" name="entry_doors_size" placeholder="Size(s)"
                                value="<?= htmlspecialchars($formData['entry_doors_size'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Windows</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control w-25" name="windows_no" placeholder="No."
                                value="<?= htmlspecialchars($formData['windows_no'] ?? '') ?>">
                            <input type="text" class="form-control" name="windows_size" placeholder="Size(s)"
                                value="<?= htmlspecialchars($formData['windows_size'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="section mb-3">
                        <h2>Additional Details</h2>

                        <input type="file" id="file-input" multiple hidden>
                        <button type="button" class="btn btn-outline-primary mb-3" id="upload-btn">Upload Files</button>

                        <div id="file-preview" class="d-flex flex-wrap gap-3">
                            <?php foreach ($attachments as $file): ?>
                                <div class="card position-relative p-2 text-center" style="width:120px;height:120px;overflow:hidden;display:flex;flex-direction:column;align-items:center;justify-content:center">
                                    <button type="button" 
                                            class="btn-close position-absolute top-0 end-0 m-1 p-1 remove-existing" 
                                            data-id="<?= $file['id'] ?>" 
                                            aria-label="Remove"></button>

                                    <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file['file_url'])): ?>
                                        <img src="building_form_attachments/<?= htmlspecialchars($file['file_url']) ?>" 
                                            alt="<?= htmlspecialchars($file['file_url']) ?>" 
                                            style="width:100%;height:auto;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="text-muted small">
                                            <?= (strlen($file['file_url']) > 12) 
                                                ? substr($file['file_url'],0,12).'...'.pathinfo($file['file_url'], PATHINFO_EXTENSION) 
                                                : $file['file_url']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>


                </div>

            </div>

            <div class="footer">
                MADE TO ORDER | METAL ROOFING & SIDING | FACTORY DIRECT PRICING<br>
                977 E. Hal Rogers Pkwy, London, KY | Mon-Fri 8am – 4:30pm | eastkentuckymetal.com<br>
                (606) 877-1848 | (606) 864-4280 (Fax) | sales@eastkentuckymetal.com<br>
                Homeowner 2025
            </div>

            <?php
            if (empty($id)) {
            ?>
            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-primary px-4 py-2">Submit Request</button>
            </div>
            <?php
            }
            ?>
        </form>
    </div>
</div>

<script>
    $(document).ready(function(){
        const maxFilenameLength = 12;
        let uploadedFiles = [];

        $('#upload-btn').click(function(){
            $('#file-input').click();
        });

        $('#file-input').on('change', function(){
            const newFiles = Array.from(this.files);
            uploadedFiles = uploadedFiles.concat(newFiles);
            renderFiles();
            $(this).val('');
        });

        function renderFiles(){
            $('#file-preview').empty();
            uploadedFiles.forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = function(e){
                    const $card = $('<div class="card position-relative p-2 text-center"></div>').css({
                        width: '120px',
                        height: '120px',
                        overflow: 'hidden',
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        justifyContent: 'center'
                    });

                    const $remove = $('<button type="button" class="btn-close position-absolute top-0 end-0 m-1 p-1" aria-label="Remove"></button>');
                    $remove.click(function(){
                        uploadedFiles.splice(index, 1);
                        renderFiles();
                    });
                    $card.append($remove);

                    if(file.type.startsWith('image/')){
                        const $img = $('<img>').attr('src', e.target.result).css({
                            width: '100%',
                            height: 'auto',
                            objectFit: 'cover'
                        });
                        $card.append($img);
                    } else {
                        let displayName = file.name;
                        if(displayName.length > maxFilenameLength){
                            const ext = displayName.includes('.') ? displayName.split('.').pop() : '';
                            displayName = displayName.substring(0, maxFilenameLength) + (ext ? `...${ext}` : '...');
                        }
                        const $icon = $('<div class="text-muted small"></div>').text(displayName);
                        $card.append($icon);
                    }

                    $('#file-preview').append($card);
                };

                reader.readAsDataURL(file);
            });
        }

        $(document).on('submit', '.sharp_form', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            uploadedFiles.forEach((file, i) => {
                formData.append('attachments[]', file);
            });

            formData.append('action', 'save_building_form');

            $.ajax({
                url: 'pages/request_quote_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        let res = JSON.parse(response);
                        if (res.success) {
                            alert("Building Form successfully saved!");
                            location.reload();
                        } else {
                            alert("Failed");
                            console.log(response);
                        }
                    } catch (e) {
                        console.log("Invalid JSON:", response);
                        alert("Failed");
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                    alert("Request failed!");
                }
            });
        });

        $(document).on('click', '.remove-existing', function () {
            let btn = $(this);
            let attachmentId = btn.data('id');

            if (!confirm("Remove this file?")) return;

            $.ajax({
                url: "pages/request_quote_ajax.php",
                type: "POST",
                data: {
                    action: "delete_attachment",
                    id: attachmentId
                },
                success: function (response) {
                    try {
                        let res = JSON.parse(response);
                        if (res.success) {
                            btn.closest('.card').remove();
                        } else {
                            alert(res.message || "Failed to remove file.");
                        }
                    } catch (e) {
                        console.error("Invalid JSON response:", response);
                        alert("Server error.");
                    }
                },
                error: function () {
                    alert("AJAX error.");
                }
            });
        });


    });
</script>