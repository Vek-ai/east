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

if (!empty($_REQUEST['id'])) {
    $ids = array_map('intval', explode(',', $_REQUEST['id']));
    if (empty($ids)) exit('Invalid ID input.');

    $id_list = implode(',', $ids);
    $query = "SELECT * FROM coil_defective WHERE coil_defective_id IN ($id_list) ORDER BY tagged_date DESC";
    $result = mysqli_query($conn, $query);

    class PDF extends FPDF {
        public $lineHeight = 6;
        public $colWidths = [
            'coil_no'        => 20,
            'color'          => 30,
            'grade'          => 25,
            'remaining_feet' => 25,
            'status'         => 30,
            'tagged_date'    => 30,
            'supplier'       => 30
        ];

        function Header() {
            $this->Image('assets/images/logo-bw.png', 10, 6, 50, 15);
            $this->SetXY(10, 22);
            $this->SetFont('Arial', '', 9);
            $this->Cell(0, 5, '977 E Hal Rogers Parkway', 0, 1);
            $this->Cell(0, 5, 'London, KY 40741', 0, 1);

            $this->SetFont('Arial', 'B', 9);
            $this->Cell(0, 5, 'Phone: (606) 877-1848 | Fax: (606) 864-4280', 0, 1);
            $this->Cell(0, 5, 'Email: Sales@Eastkentuckymetal.com', 0, 1);
            $this->Cell(0, 5, 'Website: Eastkentuckymetal.com', 0, 1);
            $this->Ln(5);
        }

        function CoilTableHeader() {
            $this->SetFont('Arial', 'B', 10);
            $this->SetFillColor(220, 220, 220);
            foreach ($this->colWidths as $col => $width) {
                $label = ucwords(str_replace('_', ' ', $col));
                if ($col === 'coil_no') $label = 'Coil #';
                elseif ($col === 'remaining_feet') $label = 'Rem. Ft.';
                elseif ($col === 'tagged_date') $label = 'Date';

                $this->Cell($width, $this->lineHeight, $label, 1, 0, 'C', true);
            }
            $this->Ln();
        }

        function NbLines($w, $txt) {
            $cw = &$this->CurrentFont['cw'];
            if ($w == 0)
                $w = $this->w - $this->rMargin - $this->x;
            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
            $s = str_replace("\r", '', $txt);
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
                } else
                    $i++;
            }
            return $nl;
        }

        function CoilRow($row) {
            global $conn;

            $this->lineHeight = 5;

            $this->SetFont('Arial', '', 9);
            $color_details = getColorDetails($row['color_sold_as']);
            $color = $color_details['color_name'] ?? '';
            $grade = getGradeName($row['grade']);
            $remaining_feet = $row['remaining_feet'] ?? 0;
            $entry_no = $row['entry_no'];
            $supplier_name = getSupplierName($row['supplier']);

            $tagged_date = '';
            if (!empty($row['tagged_date']) && strtotime($row['tagged_date'])) {
                $tagged_date = date('F j, Y', strtotime($row['tagged_date']));
            }

            $status = (int)$row['status'];
            switch ($status) {
                case 0: $status_text = 'New Defective'; break;
                case 1: $status_text = 'Under Review'; break;
                case 2: $status_text = "Quarantined Coil"; break;
                case 3: $status_text = "Return to Supplier"; break;
                case 4: $status_text = "Awaiting Approval"; break;
                case 5: $status_text = "Claim Submitted"; break;
                default: $status_text = 'Unknown'; break;
            }

            $data = [
                'coil_no'        => $entry_no,
                'color'          => $color,
                'grade'          => $grade,
                'remaining_feet' => $remaining_feet,
                'status'         => $status_text,
                'tagged_date'    => $tagged_date,
                'supplier'       => is_array($supplier_name) ? implode(', ', $supplier_name) : $supplier_name
            ];

            $maxLines = 1;
            foreach ($data as $key => $text) {
                $text = is_array($text) ? json_encode($text) : (string)$text;
                $lines = $this->NbLines($this->colWidths[$key], $text);
                $maxLines = max($maxLines, $lines);
            }

            $rowHeight = $this->lineHeight * $maxLines;

            if ($this->GetY() + $rowHeight > ($this->h - $this->bMargin)) {
                $this->AddPage();
                $this->CoilTableHeader();
                $this->SetFont('Arial', '', 9);
            }

            $x = $this->GetX();
            $y = $this->GetY();

            foreach ($data as $key => $text) {
                $w = $this->colWidths[$key];
                $text = (string)$text;
                $this->SetXY($x, $y);
                $this->Rect($x, $y, $w, $rowHeight);
                $this->MultiCell($w, $this->lineHeight, $text, 0, 'C');
                $x += $w;
            }

            $this->SetY($y + $rowHeight);
        }

        function Footer() {
            $marginLeft = 10;
            $this->SetY(-15);

            $colWidth = ($this->w - 2 * $marginLeft) / 3;

            $this->SetFont('Arial', '', 9);

            $this->SetX($marginLeft);
            $this->Cell($colWidth, 5, 'Phone: (606) 877-1848 | Fax: (606) 864-4280', 0, 0, 'L');

            $this->SetX($marginLeft + $colWidth + 10);
            $this->Cell($colWidth, 5, 'Sales@Eastkentuckymetal.com', 0, 0, 'C');

            $this->SetX($marginLeft + 2 * $colWidth);
            $this->Cell($colWidth, 5, 'Eastkentuckymetal.com', 0, 0, 'R');
        }
    }

    $pdf = new PDF('P', 'mm', 'A4');
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();
    $pdf->SetTitle('Defective Coil List');

    $pdf->SetFont('Arial', 'B', 11);
    $blueColor = [0, 51, 102];
    $whiteColor = [255, 255, 255];
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFillColor(...$blueColor);
    $pdf->Cell(0, 7, 'Defective Coils', 0, 1, 'C', true);

    $pdf->SetTextColor(0, 0, 0);

    $pdf->CoilTableHeader();

    while ($row = mysqli_fetch_assoc($result)) {
        $pdf->CoilRow($row);
    }

    $pdf->Output('coil_defective_list.pdf', 'I');
    exit;
}
?>


