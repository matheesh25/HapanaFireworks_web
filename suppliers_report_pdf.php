<?php
require('../fpdf/fpdf.php');
include("../config.php");

// ── Save chart images from POST ──────────────────────────────────────────────
$charts = ['pieChart', 'barChart'];
$files  = [];
foreach ($charts as $c) {
    if (!empty($_POST[$c])) {
        $path = __DIR__ . "/../temp/$c.png";
        file_put_contents($path, base64_decode(explode(',', $_POST[$c])[1]));
        $files[$c] = $path;
    }
}

// ── KPIs ─────────────────────────────────────────────────────────────────────
$total      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM suppliers"))[0];
$active     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM suppliers WHERE status='active'"))[0];
$inactive   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM suppliers WHERE status='inactive'"))[0];

// ── Extended PDF class ────────────────────────────────────────────────────────
class SuppliersPDF extends FPDF {

    const GOLD_R  = 245; const GOLD_G  = 166; const GOLD_B  = 35;
    const DARK_R  = 15;  const DARK_G  = 17;  const DARK_B  = 24;
    const WHITE_R = 240; const WHITE_G = 237; const WHITE_B = 232;
    const MUTED_R = 90;  const MUTED_G = 95;  const MUTED_B = 110;

    const SUCCESS_R = 39;  const SUCCESS_G = 174; const SUCCESS_B = 96;
    const DANGER_R  = 192; const DANGER_G  = 57;  const DANGER_B  = 43;
    const INFO_R    = 52;  const INFO_G    = 152; const INFO_B    = 219;

    // ── Header ────────────────────────────────────────────────────────────────
    function Header() {
        $this->SetFillColor(self::DARK_R, self::DARK_G, self::DARK_B);
        $this->Rect(0, 0, 210, 38, 'F');

        $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->Rect(0, 35, 210, 3, 'F');

        $logo = __DIR__ . '/../images/logo.jpg';
        if (file_exists($logo)) $this->Image($logo, 8, 7, 22);

        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->SetXY(0, 9);
        $this->Cell(200, 8, 'HAPANA FIREWORKS', 0, 1, 'R');

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(self::WHITE_R, self::WHITE_G, self::WHITE_B);
        $this->SetXY(0, 19);
        $this->Cell(200, 5, 'Suppliers Analytics Dashboard', 0, 1, 'R');

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(self::MUTED_R, self::MUTED_G, self::MUTED_B);
        $this->SetXY(0, 26);
        $this->Cell(200, 5, 'Generated: ' . date("d M Y, H:i:s"), 0, 1, 'R');

        $this->SetY(44);
    }

    // ── Footer ────────────────────────────────────────────────────────────────
    function Footer() {
        $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->Rect(0, 290, 210, 2, 'F');

        $this->SetY(-10);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(self::MUTED_R, self::MUTED_G, self::MUTED_B);
        $this->Cell(100, 6, 'Hapana Fireworks — Confidential', 0, 0, 'L');
        $this->Cell(100, 6, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    // ── Section title ─────────────────────────────────────────────────────────
    function SectionTitle($title) {
        $x = $this->GetX(); $y = $this->GetY();
        $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->Rect($x, $y, 3, 7, 'F');
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(self::DARK_R, self::DARK_G, self::DARK_B);
        $this->SetXY($x + 5, $y);
        $this->Cell(0, 7, $title, 0, 1, 'L');
        $this->Ln(3);
    }

    // ── Rounded rectangle ────────────────────────────────────────────────────
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $op  = ($style === 'F') ? 'f' : (($style === 'FD' || $style === 'DF') ? 'B' : 'S');
        $k   = $this->k; $hp = $this->h;
        $arc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        $xc = $x + $w - $r; $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
        $this->_Arc($xc + $r * $arc, $yc - $r, $xc + $r, $yc - $r * $arc, $xc + $r, $yc);
        $xc = $x + $w - $r; $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc + $r, $yc + $r * $arc, $xc + $r * $arc, $yc + $r, $xc, $yc + $r);
        $xc = $x + $r; $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($xc - $r * $arc, $yc + $r, $xc - $r, $yc + $r * $arc, $xc - $r, $yc);
        $xc = $x + $r; $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc - $r, $yc - $r * $arc, $xc - $r * $arc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1 * $this->k, ($h - $y1) * $this->k,
            $x2 * $this->k, ($h - $y2) * $this->k,
            $x3 * $this->k, ($h - $y3) * $this->k));
    }

    // ── KPI card ─────────────────────────────────────────────────────────────
    function KPIBox($label, $value, $x, $y, $w = 58) {
        $this->SetFillColor(self::DARK_R, self::DARK_G, self::DARK_B);
        $this->RoundedRect($x, $y, $w, 22, 3, 'F');
        $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->Rect($x, $y, $w, 2, 'F');

        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->SetXY($x, $y + 3);
        $this->Cell($w, 10, $value, 0, 1, 'C');

        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(200, 200, 210);
        $this->SetXY($x, $y + 13);
        $this->Cell($w, 7, strtoupper($label), 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
    }

    // ── Status badge ─────────────────────────────────────────────────────────
    function StatusBadge($status, $x, $y, $w) {
        $s = strtolower(trim($status));
        if ($s === 'active')       { $r = self::SUCCESS_R; $g = self::SUCCESS_G; $b = self::SUCCESS_B; }
        elseif ($s === 'inactive') { $r = self::DANGER_R;  $g = self::DANGER_G;  $b = self::DANGER_B; }
        else                       { $r = self::INFO_R;    $g = self::INFO_G;    $b = self::INFO_B; }

        $this->SetFillColor(
            (int)($r + (255 - $r) * 0.82),
            (int)($g + (255 - $g) * 0.82),
            (int)($b + (255 - $b) * 0.82)
        );
        $this->RoundedRect($x + 1, $y + 1.5, $w - 2, 5, 2, 'F');
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor($r, $g, $b);
        $this->SetXY($x, $y + 1);
        $this->Cell($w, 7, strtoupper($status), 0, 0, 'C');
        $this->SetTextColor(0, 0, 0);
    }

    // ── Category pill ─────────────────────────────────────────────────────────
    function CategoryPill($cat, $x, $y, $w) {
        $this->SetFillColor(30, 35, 55);
        $this->RoundedRect($x + 2, $y + 1.5, $w - 4, 5.5, 2, 'F');
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor(180, 185, 210);
        $this->SetXY($x, $y + 1);
        $this->Cell($w, 7, strtoupper($cat), 0, 0, 'C');
        $this->SetTextColor(0, 0, 0);
    }

    // ── Table header ─────────────────────────────────────────────────────────
    function TableHeader($cols) {
        $this->SetFillColor(self::DARK_R, self::DARK_G, self::DARK_B);
        $this->SetTextColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->SetFont('Arial', 'B', 8);
        foreach ($cols as [$label, $w, $align]) {
            $this->Cell($w, 9, $label, 0, 0, $align, true);
        }
        $this->Ln();
        $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->Rect(10, $this->GetY(), 190, 0.8, 'F');
        $this->Ln(1);
    }
}

// ── Build PDF ─────────────────────────────────────────────────────────────────
$pdf = new SuppliersPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 18);

// Page title
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(15, 17, 24);
$pdf->Cell(0, 8, 'Suppliers Report', 0, 1, 'C');
$pdf->Ln(4);

// ── KPI Row ───────────────────────────────────────────────────────────────────
$pdf->SectionTitle('Key Performance Indicators');
$y = $pdf->GetY();
$pdf->KPIBox('Total Suppliers', $total,    10,  $y);
$pdf->KPIBox('Active',          $active,   72,  $y);
$pdf->KPIBox('Inactive',        $inactive, 134, $y);
$pdf->SetY($y + 28);

// ── Charts ────────────────────────────────────────────────────────────────────
$pdf->SectionTitle('Analytics Charts');
$chartY = $pdf->GetY();

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(15, 17, 24);
$pdf->SetXY(10, $chartY);
$pdf->Cell(88, 7, 'Suppliers by Category', 0, 0, 'C');
$pdf->Cell(88, 7, 'Category Comparison', 0, 1, 'C');
$chartY += 7;

if (isset($files['pieChart'])) {
    $pdf->Image($files['pieChart'], 10, $chartY, 88, 62);
} else {
    $pdf->SetFillColor(245, 247, 250);
    $pdf->RoundedRect(10, $chartY, 88, 62, 4, 'F');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(150, 155, 170);
    $pdf->SetXY(10, $chartY + 26);
    $pdf->Cell(88, 10, 'Chart not available', 0, 0, 'C');
}

if (isset($files['barChart'])) {
    $pdf->Image($files['barChart'], 112, $chartY, 88, 62);
} else {
    $pdf->SetFillColor(245, 247, 250);
    $pdf->RoundedRect(112, $chartY, 88, 62, 4, 'F');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(150, 155, 170);
    $pdf->SetXY(112, $chartY + 26);
    $pdf->Cell(88, 10, 'Chart not available', 0, 0, 'C');
}

$pdf->SetY($chartY + 68);

// ── Suppliers Table ───────────────────────────────────────────────────────────
$pdf->SectionTitle('Supplier Records');

$cols = [
    ['ID',       13, 'C'],
    ['Name',     40, 'L'],
    ['Company',  45, 'L'],
    ['Email',    50, 'L'],
    ['Category', 30, 'C'],
    ['Status',   22, 'C'],  // 13+40+45+50+30+22 = 200 — but table starts at x=10, so usable = 190
];
// Adjust total to exactly 190mm
// 13+40+45+50+30+12 = 190 — let's recalculate
$cols = [
    ['ID',       12, 'C'],
    ['Name',     38, 'L'],
    ['Company',  42, 'L'],
    ['Email',    48, 'L'],
    ['Category', 28, 'C'],
    ['Status',   22, 'C'],
];
// 12+38+42+48+28+22 = 190 ✓

$pdf->TableHeader($cols);

$res     = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY id DESC");
$rowNum  = 0;

while ($r = mysqli_fetch_assoc($res)) {
    $rowNum++;

    // Page-break guard
    if ($pdf->GetY() > 268) {
        $pdf->AddPage();
        $pdf->TableHeader($cols);
        $rowNum = 1;
    }

    $rowY = $pdf->GetY();

    // Alternating row bg
    if ($rowNum % 2 === 0) {
        $pdf->SetFillColor(245, 247, 250);
        $pdf->Rect(10, $rowY, 190, 9, 'F');
    }

    // ID
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(180, 120, 10);
    $pdf->Cell($cols[0][1], 9, '#' . $r['id'], 0, 0, 'C');

    // Name
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(30, 35, 50);
    $pdf->Cell($cols[1][1], 9, mb_strimwidth($r['name'], 0, 20, '..'), 0, 0, 'L');

    // Company
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(60, 65, 80);
    $pdf->Cell($cols[2][1], 9, mb_strimwidth($r['company_name'], 0, 24, '..'), 0, 0, 'L');

    // Email
    $pdf->SetFont('Arial', '', 7.5);
    $pdf->SetTextColor(80, 85, 100);
    $pdf->Cell($cols[3][1], 9, mb_strimwidth($r['email'] ?? '', 0, 28, '..'), 0, 0, 'L');

    // Category pill
    $cx = $pdf->GetX(); $cy = $pdf->GetY();
    $pdf->Cell($cols[4][1], 9, '', 0, 0);
    $pdf->CategoryPill($r['category'], $cx, $cy, $cols[4][1]);

    // Status badge
    $sx = $pdf->GetX(); $sy = $pdf->GetY();
    $pdf->Cell($cols[5][1], 9, '', 0, 0);
    $pdf->StatusBadge($r['status'], $sx, $sy, $cols[5][1]);

    // Row separator
    $pdf->Ln();
    $pdf->SetDrawColor(220, 222, 228);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
}

// ── Signature ────────────────────────────────────────────────────────────────
$pdf->Ln(12);
$pdf->SetDrawColor(180, 185, 200);
$pdf->Line(130, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(90, 95, 110);
$pdf->Cell(130, 6, '', 0, 0);
$pdf->Cell(70, 6, 'Authorized Signature', 0, 1, 'C');

// ── Output ────────────────────────────────────────────────────────────────────
if (ob_get_length()) ob_end_clean();

$pdf->Output('Suppliers_Report.pdf', 'D');