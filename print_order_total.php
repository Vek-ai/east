<?php
session_start();
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

if (!isset($_SESSION['userid'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect_url");
    exit();
}

require 'includes/fpdf/fpdf.php';
require 'includes/dbconn.php';
require 'includes/functions.php';

function NbLines($pdf, $w, $txt) {
    $txt = str_replace("\r", '', (string)$txt);
    $nb = strlen($txt);

    if ($nb > 0 && $txt[$nb - 1] == "\n") {
        $nb--;
    }

    $lines = 1;
    $lineWidth = 0;
    $maxWidth = $w;
    $spaceWidth = $pdf->GetStringWidth(' ');

    for ($i = 0; $i < $nb; $i++) {
        $c = $txt[$i];
        if ($c == "\n") {
            $lines++;
            $lineWidth = 0;
            continue;
        }

        $charWidth = $pdf->GetStringWidth($c);
        if ($lineWidth + $charWidth > $maxWidth) {
            $lines++;
            $lineWidth = $charWidth;
        } else {
            $lineWidth += $charWidth;
        }
    }
    return $lines;
}

function renderTableRows($rows, $textColor = [0, 0, 0], $bold = false, $fontSize = 10) {
    global $pdf, $widths;

    $pdf->SetFont('Arial', $bold ? 'B' : '', $fontSize);
    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);

    foreach ($rows as $row) {
        $y_initial = $pdf->GetY();

        foreach ($widths as $i => $w) {
            $x = $pdf->GetX();
            $y = $pdf->GetY();

            $align = ($i === array_key_last($widths)) ? 'R' : 'L';
            $text = $row[$i];

            if ($i === 0) {
                // Dotted leader for first column (prevent wrapping)
                $textWidth = $pdf->GetStringWidth($text);
                $available = $w - $textWidth - 3;
                $dotWidth = $pdf->GetStringWidth('.');
                $numDots = max(0, floor($available / $dotWidth));
                $dots = str_repeat('.', $numDots);
                $lineText = $text . ' ' . $dots;

                // Draw single-line cell (no wrapping)
                $pdf->Cell($w, 5, $lineText, 'TLR', 0, 'L');
            } else {
                // Use MultiCell for other columns (wrapping okay)
                $pdf->MultiCell($w, 5, $text, 'TLR', $align);
                $pdf->SetXY($x + $w, $y);
            }
        }

        $pdf->Ln(5);
        $y_bottom = $pdf->GetY();
        $pdf->Line(10, $y_bottom, 200, $y_bottom);
    }
}


class PDF extends FPDF {
    public $orderid;
    public $order_date;
    public $delivery_method;
    public $scheduled_date;
    public $salesperson;

    function Header() {
        $this->SetFont('Arial', '', 10);
        $this->Image('assets/images/logo-bw.png', 10, 6, 60, 20);

        $col2_x = 140;

        $this->SetXY($col2_x - 10, 6);
        $this->MultiCell(95, 5, "Invoice #: " . getInvoiceNumName($this->orderid), 0, 'L');

        $this->SetXY($col2_x - 10, $this->GetY());
        $this->Cell(95, 5, "Order Date: " . $this->order_date, 0, 1, 'L');

        $this->SetXY($col2_x - 10, $this->GetY());
        $labelWidth = 33;
        $this->SetFont('Arial', '', 10);
        $this->Cell($labelWidth, 5, "Pick-up or Delivery:", 0, 0, 'L');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(95 - $labelWidth, 5, $this->delivery_method, 0, 1, 'L');

        $this->SetXY($col2_x - 10, $this->GetY());
        $this->SetFont('Arial', '', 10);
        $this->Cell(95, 5, "Scheduled Date: " . $this->scheduled_date, 0, 1, 'L');

        $this->SetXY($col2_x - 10, $this->GetY());
        $this->Cell(95, 5, "Salesperson: " . $this->salesperson, 0, 0, 'L');

        $this->Ln(5);
    }

    function Footer() {
        $marginLeft = 10;
        $marginLeft = 10;
        $colWidthLeft  = 110;
        $colWidthRight = 70;
        $this->SetY(-40);

        $colWidth = ($this->w - 2 * $marginLeft) / 3;

        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 51, 153);
        $this->Cell($this->w - 2 * $marginLeft, 5, 'We appreciate your continued business with East Kentucky Metal!', 0, 1, 'C');

        $this->SetTextColor(0, 0, 0);
        $this->SetY($this->GetY() + 1);
        $gpsIcon = 'assets/images/gps.png';
        $text = '977 E. Hal Rogers Parkway';
        $iconWidth = 5;
        $spacing = 2;

        $totalWidth = $iconWidth + $spacing + $this->GetStringWidth($text);
        $x = ($this->w - $totalWidth) / 2;

        $this->Image($gpsIcon, $x, $this->GetY(), $iconWidth, 5);
        $this->SetXY($x + $iconWidth + $spacing, $this->GetY());
        $this->Cell($this->GetStringWidth($text), 5, $text, 0, 1, 'L');

        $this->SetY(-20);
        $this->SetFont('Arial', '', 9);

        $phoneIcon = 'assets/images/phone.png';
        $this->Image($phoneIcon, $marginLeft, $this->GetY(), 5, 5);
        $this->SetXY($marginLeft + 7, $this->GetY());
        $this->Cell($colWidth, 5, '(606) 877-1848 | Fax: (606) 864-4280', 0, 0, 'L');

        $emailIcon = 'assets/images/email.png';
        $this->Image($emailIcon, $marginLeft + $colWidth + 10, $this->GetY(), 5, 5);
        $this->SetXY($marginLeft + $colWidth + 17, $this->GetY());
        $this->Cell($colWidth, 5, 'Sales@EastKentuckyMetal.com', 0, 0, 'L');

        $webIcon = 'assets/images/web.png';
        $this->Image($webIcon, $marginLeft + 2 * $colWidth + 10, $this->GetY(), 5, 5);
        $this->SetXY($marginLeft + 2 * $colWidth + 17, $this->GetY());
        $this->Cell($colWidth, 5, 'EastKentuckyMetal.com', 0, 0, 'L');
    }

    public function GetMultiCellHeight($w, $h, $txt)
    {
        // Calculate number of lines
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c] ?? 0;
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl * $h;
    }
}

$pdf = new PDF();
$pdf->SetAutoPageBreak(true, 40);

$col1_x = 10;
$col2_x = 140;
$lumber_id = 1;
$screw_id = 16;
$panel_id = 3;
$trim_id = 4;

$orderid = $_REQUEST['id'];
$pricing_id = $_REQUEST['pricing_id'] ?? '';
$current_user_id = $_SESSION['userid'];

$query = "SELECT * FROM orders WHERE orderid = '$orderid'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while($row_orders = mysqli_fetch_assoc($result)){
        $delivery_price = floatval($row_orders['delivery_amt']);
        $discount = floatval($row_orders['discount_percent']) / 100;
        $orderid = $row_orders['orderid'];
        $customer_id = $row_orders['customerid'];
        $customerDetails = getCustomerDetails($customer_id);
        $tax = floatval(getCustomerTax($customer_id)) / 100;
        $delivery_method = 'Deliver';
        $order_date = '';
        if (!empty($row_orders['order_date']) && $row_orders['order_date'] !== '0000-00-00 00:00:00') {
            $order_date = date("(l) m/d/Y || g:i A", strtotime($row_orders['order_date']));
        }

        $scheduled_date = '';
        if (!empty($row_orders["scheduled_date"]) && $row_orders["scheduled_date"] !== '0000-00-00 00:00:00') {
            $scheduled_date = date("(l) m/d/Y || g:i A", strtotime($row_orders["scheduled_date"]));
        }

        if($delivery_price == 0){
            $delivery_method = 'Pickup';
        }
        
        $pdf->orderid = $orderid;
        $pdf->order_date = $order_date;
        $pdf->delivery_method = $delivery_method;
        $pdf->scheduled_date = $scheduled_date;
        $pdf->salesperson = get_staff_name($current_user_id);

        $pdf->AddPage();

        $col1_x = 10;
        $col2_x -= 30;
        $col3_x = 140;
        $pageWidth = $pdf->GetPageWidth();
        $marginLeft = 10;
        $marginRight = 10;
        $usableWidth = $pageWidth - $marginLeft - $marginRight;
        $mailToWidth = $usableWidth / 2;

        

        $currentY = $pdf->GetY();

        $pdf->SetFillColor(211, 211, 211);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($col1_x, $currentY);
        $pdf->Cell($mailToWidth+10, 7, 'Bill to:', 0, 0, 'L', true);

        $pdf->SetXY($col2_x, $currentY);
        $pdf->Cell($mailToWidth-10, 7, 'Ship to:', 0, 0, 'L', true);

        $pdf->Ln(7);
        $def_y = $pdf->GetY();

        $leftX = $col1_x;
        $leftY = $def_y;
        $pdf->SetXY($leftX, $leftY);

        $leftText = get_customer_name($row_orders['customerid']) . "\n";
        $addressParts = [];
        $address = $customerDetails['address'];
        $city = $customerDetails['city'];
        $state = $customerDetails['state'];
        $zip = $customerDetails['zip'];
        if(!empty($customerDetails['different_ship_address'])){
            $address = $customerDetails['ship_address'];
            $city = $customerDetails['ship_city'];
            $state = $customerDetails['ship_state'];
            $zip = $customerDetails['ship_zip'];
        }
        
        if (!empty($address)) $addressParts[] = $address;
        if (!empty($city)) $addressParts[] = $city;
        if (!empty($state)) $addressParts[] = $state;
        if (!empty($zip)) $addressParts[] = $zip;
        if (!empty($addressParts)) $leftText .= implode(', ', $addressParts) . "\n";
        if (!empty($customerDetails['tax_exempt_number'])) $leftText .= 'Tax Exempt #: ' . $customerDetails['tax_exempt_number'] . "\n";
        if (!empty($customerDetails['contact_phone'])) $leftText .= $customerDetails['contact_phone'] . "\n";

        $pdf->SetFont('Arial', '', 10);
        $leftStartY = $pdf->GetY();
        $pdf->MultiCell($mailToWidth, 5, $leftText, 0, 'L');
        $leftHeight = $pdf->GetY() - $leftStartY;

        $rightX = $col2_x;
        $rightY = $def_y;
        $pdf->SetXY($rightX, $rightY);

        $rightText = trim($row_orders['deliver_fname'].' '.$row_orders['deliver_lname'])."\n";
        $shipAddressParts = [];
        if (!empty($row_orders['deliver_address'])) $shipAddressParts[] = $row_orders['deliver_address'];
        if (!empty($row_orders['deliver_city'])) $shipAddressParts[] = $row_orders['deliver_city'];
        if (!empty($row_orders['deliver_state'])) $shipAddressParts[] = $row_orders['deliver_state'];
        if (!empty($row_orders['deliver_zip'])) $shipAddressParts[] = $row_orders['deliver_zip'];
        if (!empty($shipAddressParts)) $rightText .= implode(', ', $shipAddressParts)."\n";
        $rightText .= 'Job Name: '.$row_orders['job_name'];

        $rightStartY = $pdf->GetY();
        $pdf->MultiCell($mailToWidth, 5, $rightText, 0, 'L');
        $rightHeight = $pdf->GetY() - $rightStartY;

        $blockHeight = max($leftHeight, $rightHeight);
        $pdf->SetY($def_y + $blockHeight + 2);


        $total_price = 0;
        $total_qty = 0;
        $screw_id = 16;

        $total_price = 0;
        $total_qty = 0;
        $total_price_undisc = 0;

        $query_category = "SELECT * FROM product_category WHERE hidden = 0";
        $result_category = mysqli_query($conn, $query_category);

        if (mysqli_num_rows($result_category) > 0) {
            $pdf->SetFont('Arial', 'B', 9);
            $widths = [138, 53];
            $headers = ['PRODUCT CATEGORY', 'PRICE'];

            for ($i = 0; $i < count($headers); $i++) {
                $pdf->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C');
            }
            $pdf->Ln();

            $total_price = 0;
            $total_qty = 0;

            while ($row_category = mysqli_fetch_assoc($result_category)) {
                $product_category_id = $row_category['product_category_id'];

                $total_per_component = 0.0;

                $query_product = "
                    SELECT p.product_category, op.*
                    FROM `order_product` AS op
                    LEFT JOIN product AS p ON p.product_id = op.`productid`
                    WHERE op.orderid = '" . mysqli_real_escape_string($conn, $orderid) . "'
                    AND p.product_category = '" . mysqli_real_escape_string($conn, $product_category_id) . "'";

                $result_product = mysqli_query($conn, $query_product);
                if (mysqli_num_rows($result_product) > 0) {
                    while ($row_product = mysqli_fetch_assoc($result_product)) {
                        $quantity = is_numeric($row_product['quantity']) ? floatval($row_product['quantity']) : 0.0;

                        $price_undisc = floatval($row_product['discounted_price']);

                        $total_price += $price_undisc;
                        $total_per_component += $price_undisc;
                        $total_qty += $quantity;
                    }

                    $data = [
                        [getProductCategoryName($product_category_id), '$ ' . number_format($total_per_component, 2)]
                    ];
                    $pdf->SetFont('Arial', '', 8);
                    renderTableRows($data);
                }
            }

            $materials_total = $total_price;
            $discount_value = 0;
            if (!empty($row_orders['discount_percent']) && $row_orders['discount_percent'] > 0) {
                $discount_value = $materials_total * ($row_orders['discount_percent'] / 100);
            } elseif (!empty($row_orders['discount_amount']) && $row_orders['discount_amount'] > 0) {
                $discount_value = min($row_orders['discount_amount'], $materials_total);
            }

            $subtotal = max(0, $materials_total - $discount_value);
            $delivery_price = isset($delivery_price) ? floatval($delivery_price) : 0.0;
            $taxable_total = $subtotal + $delivery_price;

            $tax = isset($tax) ? floatval($tax) : 0.0;
            $sales_tax = $taxable_total * $tax;

            $grand_total = $taxable_total + $sales_tax;

            $total_saved = $discount_value;

            $pdf->Ln(5);

            $miscRows = [
                ['Job Site Fees', '$ 0.00'],
                ['Delivery Charge', '$ ' . number_format($delivery_price, 2)],
                ['Savings', '$ ' . number_format($total_saved, 2)],
            ];
            renderTableRows($miscRows);
            $pdf->Ln(5);

            $subtotalRows = [
                ['Subtotal (after discount)', '$ ' . number_format($subtotal, 2)],
                ['Sales Tax', '$ ' . number_format($sales_tax, 2)],
            ];
            renderTableRows($subtotalRows);
            $pdf->Ln(5);

            $grandTotalRows = [
                ['Total Price (includes Sales Tax)', '$ ' . number_format($grand_total, 2)],
            ];
            renderTableRows($grandTotalRows, [0, 0, 139], true, 11);

            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(0, 0, 0);

        } else {
            echo "No key components found";
        }


        $pdf->Ln(5);

        $col1_x = 10;
        $col2_x = 140;
        $col_y = $pdf->GetY();

        $pdf->SetFont('Arial', '', 10);

        $disclaimer = "Customer is solely responsible for accuracy of order and for verifying accuracy of materials before leaving EKMS or at time of delivery. If an agent orders or takes materials on customer's behalf, EKMS is entitled to rely upon the agent as if s/he has full authority to act on customer's behalf. No returns on metal panels or special trim. All other materials returned undamaged within 60 days of invoice date are subject to a restocking fee equal to 25% of current retail price.";

        $savings_note = "*Customer Savings represent your savings on this Order by being an EKM Member.*";

        $disclaimerHeight = $pdf->GetMultiCellHeight(120, 4, $disclaimer);
        $qrBlockHeight   = 30;
        $blockHeight     = max($disclaimerHeight, $qrBlockHeight);

        if ($pdf->GetY() + $blockHeight > $pdf->GetPageHeight() - 20) {
            $pdf->AddPage();
            $col_y = $pdf->GetY();
        } else {
            $col_y = $pdf->GetY();
        }

        $pdf->SetXY($col1_x, $col_y);
        $pdf->MultiCell(120, 4, $disclaimer, 0, 'L');

        $yStart = $col_y; 
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY($col2_x, $yStart);
        $pdf->MultiCell(60, 5, "Scan me for a Digtal copy of this Summary Cost breakdown", 0, 'C');

        $qrX = $col2_x + 15;
        $qrY = $pdf->GetY();
        $pdf->Image('assets/images/qr_rickroll.png', $qrX, $qrY, 25, 25);

        $pdf->SetFont('Arial', '', 9);
        $pdf->Ln($blockHeight - $disclaimerHeight + 5);

        $pdf->SetTitle('Receipt');
        $pdf->Output('Receipt.pdf', 'I');
        
    }
}else{
    echo "ID not Found!";
}

?>
