<?php
require('../fpdf/fpdf.php');
include("../config.php");

// ── Save chart images from POST ──────────────────────────────────────────────
$charts = ['pieChart', 'barChart', 'lineChart', 'payChart'];
$files  = [];
foreach ($charts as $c) {
    if (!empty($_POST[$c])) {
        $path = __DIR__ . "/../temp/$c.png";
        file_put_contents($path, base64_decode(explode(',', $_POST[$c])[1]));
        $files[$c] = $path;
    }
}

// ── KPIs ─────────────────────────────────────────────────────────────────────
$totalOrders   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders"))[0];
$totalRevenue  = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(total) FROM orders"))[0];
$totalPaid     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders WHERE payment='paid'"))[0];
$totalPending  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders WHERE status='pending'"))[0];

// ── Extend FPDF ───────────────────────────────────────────────────────────────
class OrdersPDF extends FPDF {

    const GOLD_R  = 245; const GOLD_G  = 166; const GOLD_B  = 35;
    const DARK_R  = 15;  const DARK_G  = 17;  const DARK_B  = 24;
    const WHITE_R = 240; const WHITE_G = 237; const WHITE_B = 232;
    const MUTED_R = 90;  const MUTED_G = 95;  const MUTED_B = 110;

    const SUCCESS_R = 39;  const SUCCESS_G = 174; const SUCCESS_B = 96;
    const WARN_R    = 230; const WARN_G    = 126; const WARN_B    = 34;
    const INFO_R    = 52;  const INFO_G    = 152; const INFO_B    = 219;
    const DANGER_R  = 192; const DANGER_G  = 57;  const DANGER_B  = 43;

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
        $this->Cell(200, 5, 'Orders Analytics Dashboard', 0, 1, 'R');

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
        $op = ($style === 'F') ? 'f' : (($style === 'FD' || $style === 'DF') ? 'B' : 'S');
        $k = $this->k; $hp = $this->h;
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

    // ── KPI card ──────────────────────────────────────────────────────────────
    function KPIBox($label, $value, $x, $y, $w = 42) {
        $this->SetFillColor(self::DARK_R, self::DARK_G, self::DARK_B);
        $this->RoundedRect($x, $y, $w, 22, 3, 'F');
        $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->Rect($x, $y, $w, 2, 'F');

        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->SetXY($x, $y + 3);
        $this->Cell($w, 10, $value, 0, 1, 'C');

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(200, 200, 210);
        $this->SetXY($x, $y + 13);
        $this->Cell($w, 7, strtoupper($label), 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
    }

    // ── Status badge ─────────────────────────────────────────────────────────
    function StatusBadge($status, $x, $y, $w) {
        $s = strtolower(trim($status));
        if ($s === 'completed' || $s === 'paid')         { $r = self::SUCCESS_R; $g = self::SUCCESS_G; $b = self::SUCCESS_B; }
        elseif ($s === 'processing' || $s === 'shipped') { $r = self::INFO_R;    $g = self::INFO_G;    $b = self::INFO_B; }
        elseif ($s === 'pending')                        { $r = self::WARN_R;    $g = self::WARN_G;    $b = self::WARN_B; }
        else                                             { $r = self::DANGER_R;  $g = self::DANGER_G;  $b = self::DANGER_B; }

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

    // ── Table header row ─────────────────────────────────────────────────────
    function TableHeader($cols) {
        $this->SetFillColor(self::DARK_R, self::DARK_G, self::DARK_B);
        $this->SetTextColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->SetFont('Arial', 'B', 8);
        foreach ($cols as [$label, $w]) {
            $this->Cell($w, 9, $label, 0, 0, 'C', true);
        }
        $this->Ln();
        $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->Rect(10, $this->GetY(), 190, 0.8, 'F');
        $this->Ln(1);
    }
}

// ── Build PDF ─────────────────────────────────────────────────────────────────
$pdf = new OrdersPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 18);

// Page title
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(15, 17, 24);
$pdf->Cell(0, 8, 'Orders Report', 0, 1, 'C');
$pdf->Ln(4);

// ── KPI Row ───────────────────────────────────────────────────────────────────
$pdf->SectionTitle('Key Performance Indicators');
$y = $pdf->GetY();
$pdf->KPIBox('Total Orders',   $totalOrders,                          10,  $y);
$pdf->KPIBox('Total Revenue',  'LKR ' . number_format($totalRevenue, 2), 56,  $y, 58);
$pdf->KPIBox('Paid Orders',    $totalPaid,                            118, $y);
$pdf->KPIBox('Pending Orders', $totalPending,                         164, $y);
$pdf->SetY($y + 28);

// ── Charts ────────────────────────────────────────────────────────────────────
if (!empty($files)) {
    $pdf->SectionTitle('Analytics Charts');
    $cy = $pdf->GetY();
    if (isset($files['pieChart']))  $pdf->Image($files['pieChart'],  10,  $cy, 88, 58);
    if (isset($files['barChart']))  $pdf->Image($files['barChart'],  112, $cy, 88, 58);
    $pdf->SetY($cy + 62);
    $cy2 = $pdf->GetY();
    if (isset($files['lineChart'])) $pdf->Image($files['lineChart'], 10,  $cy2, 88, 58);
    if (isset($files['payChart']))  $pdf->Image($files['payChart'],  112, $cy2, 88, 58);
    $pdf->SetY($cy2 + 66);
}

// ── Orders Table ──────────────────────────────────────────────────────────────
$pdf->SectionTitle('Order Records');

// Columns: [label, width]
$cols = [
    ['ID',       12],
    ['Customer', 42],
    ['Product',  42],
    ['Total',    30],
    ['Payment',  28],
    ['Status',   36],
];

$pdf->TableHeader($cols);

$res = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC");
$rowNum = 0;

while ($r = mysqli_fetch_assoc($res)) {
    $rowNum++;

    // Page-break guard with header repeat
    if ($pdf->GetY() > 268) {
        $pdf->AddPage();
        $pdf->TableHeader($cols);
        $rowNum = 1;
    }

    $rowY = $pdf->GetY();

    // Alternating row background
    if ($rowNum % 2 === 0) {
        $pdf->SetFillColor(245, 247, 250);
        $pdf->Rect(10, $rowY, 190, 9, 'F');
    }

    // ID
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(180, 120, 10);
    $pdf->Cell($cols[0][1], 9, '#' . $r['id'], 0, 0, 'C');

    // Customer
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(30, 35, 50);
    $pdf->Cell($cols[1][1], 9, $r['customer_name'], 0, 0, 'L');

    // Product
    $pdf->Cell($cols[2][1], 9, $r['product'], 0, 0, 'L');

    // Total
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(15, 17, 24);
    $pdf->Cell($cols[3][1], 9, 'LKR ' . number_format($r['total'], 2), 0, 0, 'R');

    // Payment badge
    $px = $pdf->GetX(); $py = $pdf->GetY();
    $pdf->Cell($cols[4][1], 9, '', 0, 0);
    $pdf->StatusBadge($r['payment'], $px, $py, $cols[4][1]);

    // Status badge
    $sx = $pdf->GetX(); $sy = $pdf->GetY();
    $pdf->Cell($cols[5][1], 9, '', 0, 0);
    $pdf->StatusBadge($r['status'], $sx, $sy, $cols[5][1]);

    // Row separator
    $pdf->Ln();
    $pdf->SetDrawColor(220, 222, 228);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
}

// ── Output ────────────────────────────────────────────────────────────────────
$pdf->Output('Orders_Report.pdf', 'D');