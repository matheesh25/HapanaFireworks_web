<?php
require('../fpdf/fpdf.php');
include("../config.php");

// ── Save chart images from POST ──────────────────────────────────────────────
$charts = ['pieChart', 'barChart', 'courierChart', 'lineChart'];
$files  = [];
foreach ($charts as $c) {
    if (!empty($_POST[$c])) {
        $path = __DIR__ . "/../temp/$c.png";
        file_put_contents($path, base64_decode(explode(',', $_POST[$c])[1]));
        $files[$c] = $path;
    }
}

// ── KPIs ─────────────────────────────────────────────────────────────────────
$total      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM delivery"))[0];
$completed  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM delivery WHERE delivery_status='completed'"))[0];
$pending    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM delivery WHERE delivery_status='pending'"))[0];
$processing = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM delivery WHERE delivery_status='processing'"))[0];

// ── Extend FPDF ───────────────────────────────────────────────────────────────
class DeliveryPDF extends FPDF {

    // ── Colours ──────────────────────────────────────────────────────────────
    const GOLD_R    = 245; const GOLD_G    = 166; const GOLD_B    = 35;
    const DARK_R    = 15;  const DARK_G    = 17;  const DARK_B    = 24;
    const WHITE_R   = 240; const WHITE_G   = 237; const WHITE_B   = 232;
    const MUTED_R   = 90;  const MUTED_G   = 95;  const MUTED_B   = 110;
    const ROW_ALT_R = 245; const ROW_ALT_G = 247; const ROW_ALT_B = 250;
    const SUCCESS_R = 39;  const SUCCESS_G = 174; const SUCCESS_B = 96;
    const WARN_R    = 230; const WARN_G    = 126; const WARN_B    = 34;
    const INFO_R    = 52;  const INFO_G    = 152; const INFO_B    = 219;
    const DANGER_R  = 192; const DANGER_G  = 57;  const DANGER_B  = 43;

    // ── Header ────────────────────────────────────────────────────────────────
    function Header() {
        // Dark header band
        $this->SetFillColor(self::DARK_R, self::DARK_G, self::DARK_B);
        $this->Rect(0, 0, 210, 38, 'F');

        // Gold accent stripe
        $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->Rect(0, 35, 210, 3, 'F');

        // Logo (if it exists)
        $logo = __DIR__ . '/../images/logo.jpg';
        if (file_exists($logo)) {
            $this->Image($logo, 8, 7, 22);
        }

        // Company name
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->SetXY(0, 9);
        $this->Cell(200, 8, 'HAPANA FIREWORKS', 0, 1, 'R');

        // Subtitle
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(self::WHITE_R, self::WHITE_G, self::WHITE_B);
        $this->SetXY(0, 19);
        $this->Cell(200, 5, 'Delivery Analytics Dashboard', 0, 1, 'R');

        // Date
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(self::MUTED_R, self::MUTED_G, self::MUTED_B);
        $this->SetXY(0, 26);
        $this->Cell(200, 5, 'Generated: ' . date("d M Y, H:i:s"), 0, 1, 'R');

        $this->SetY(44);
    }

    // ── Footer ────────────────────────────────────────────────────────────────
    function Footer() {
        // Gold footer stripe
        $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->Rect(0, 290, 210, 2, 'F');

        $this->SetY(-10);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(self::MUTED_R, self::MUTED_G, self::MUTED_B);
        $this->Cell(100, 6, 'Hapana Fireworks — Confidential', 0, 0, 'L');
        $this->Cell(100, 6, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    // ── Section heading ───────────────────────────────────────────────────────
    function SectionTitle($title) {
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(self::DARK_R, self::DARK_G, self::DARK_B);
        $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);

        // Gold left bar (drawn as a narrow filled rect)
        $x = $this->GetX(); $y = $this->GetY();
        $this->Rect($x, $y, 3, 7, 'F');
        $this->SetXY($x + 5, $y);
        $this->Cell(0, 7, $title, 0, 1, 'L');
        $this->Ln(3);
    }

    // ── KPI box ───────────────────────────────────────────────────────────────
    function KPIBox($label, $value, $x, $y, $w = 42, $fillR = null, $fillG = null, $fillB = null) {
        $fillR = $fillR ?? self::DARK_R;
        $fillG = $fillG ?? self::DARK_G;
        $fillB = $fillB ?? self::DARK_B;

        // Box background
        $this->SetFillColor($fillR, $fillG, $fillB);
        $this->RoundedRect($x, $y, $w, 22, 3, 'F');

        // Gold top accent line
        $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->Rect($x, $y, $w, 2, 'F');

        // Value
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->SetXY($x, $y + 3);
        $this->Cell($w, 10, $value, 0, 1, 'C');

        // Label
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(200, 200, 210);
        $this->SetXY($x, $y + 13);
        $this->Cell($w, 7, strtoupper($label), 0, 1, 'C');

        $this->SetTextColor(0, 0, 0);
    }

    // ── Rounded rectangle (FPDF doesn't have one natively) ───────────────────
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k  = $this->k;
        $hp = $this->h;
        if ($style === 'F') $op = 'f';
        elseif ($style === 'FD' || $style === 'DF') $op = 'B';
        else $op = 'S';
        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        $xc = $x + $w - $r; $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
        $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
        $xc = $x + $w - $r; $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x + $r; $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
        $xc = $x + $r; $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1 * $this->k, ($h - $y1) * $this->k,
            $x2 * $this->k, ($h - $y2) * $this->k,
            $x3 * $this->k, ($h - $y3) * $this->k));
    }

    // ── Status badge ─────────────────────────────────────────────────────────
    function StatusBadge($status, $x, $y, $w = 38) {
        $s = strtolower(trim($status));
        if ($s === 'completed')          { $r = self::SUCCESS_R; $g = self::SUCCESS_G; $b = self::SUCCESS_B; }
        elseif ($s === 'processing')     { $r = self::INFO_R;    $g = self::INFO_G;    $b = self::INFO_B; }
        elseif (str_contains($s,'out'))  { $r = self::WARN_R;    $g = self::WARN_G;    $b = self::WARN_B; }
        else                             { $r = self::DANGER_R;  $g = self::DANGER_G;  $b = self::DANGER_B; }

        // Tinted background
        $this->SetFillColor((int)($r + (255-$r)*0.82), (int)($g + (255-$g)*0.82), (int)($b + (255-$b)*0.82));
        $this->RoundedRect($x + 1, $y + 1.5, $w - 2, 5, 2, 'F');

        // Text
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor($r, $g, $b);
        $this->SetXY($x, $y + 1);
        $this->Cell($w, 7, strtoupper($status), 0, 0, 'C');
        $this->SetTextColor(0, 0, 0);
    }

    // ── Progress bar ─────────────────────────────────────────────────────────
    function ProgressBar($pct, $x, $y, $w = 28) {
        $pct = (int)$pct;
        // Track
        $this->SetFillColor(220, 220, 228);
        $this->RoundedRect($x, $y + 2.5, $w, 3.5, 1.5, 'F');
        // Fill
        if ($pct > 0) {
            $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
            $fillW = max(3, $w * $pct / 100);
            $this->RoundedRect($x, $y + 2.5, $fillW, 3.5, 1.5, 'F');
        }
        // Label
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor(50, 50, 60);
        $this->SetXY($x + $w + 1, $y + 1);
        $this->Cell(10, 7, $pct . '%', 0, 0, 'L');
        $this->SetTextColor(0, 0, 0);
    }
}

// ── Build PDF ─────────────────────────────────────────────────────────────────
$pdf = new DeliveryPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 18);

// ── Page title ────────────────────────────────────────────────────────────────
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(15, 17, 24);
$pdf->Cell(0, 8, 'Delivery Report', 0, 1, 'C');
$pdf->Ln(4);

// ── KPI Row ───────────────────────────────────────────────────────────────────
$pdf->SectionTitle('Key Performance Indicators');
$startX = 10; $y = $pdf->GetY();
$pdf->KPIBox('Total Deliveries', $total,      $startX,      $y);
$pdf->KPIBox('Completed',        $completed,  $startX + 46, $y);
$pdf->KPIBox('Processing',       $processing, $startX + 92, $y);
$pdf->KPIBox('Pending',          $pending,    $startX + 138, $y);
$pdf->SetY($y + 28);

// ── Charts ────────────────────────────────────────────────────────────────────
if (!empty($files)) {
    $pdf->SectionTitle('Analytics Charts');
    $chartY = $pdf->GetY();
    if (isset($files['pieChart']))     $pdf->Image($files['pieChart'],     10,  $chartY, 88, 58);
    if (isset($files['barChart']))     $pdf->Image($files['barChart'],     112, $chartY, 88, 58);
    $pdf->SetY($chartY + 62);
    $chartY2 = $pdf->GetY();
    if (isset($files['courierChart'])) $pdf->Image($files['courierChart'], 10,  $chartY2, 88, 58);
    if (isset($files['lineChart']))    $pdf->Image($files['lineChart'],    112, $chartY2, 88, 58);
    $pdf->SetY($chartY2 + 66);
}

// ── Delivery Table ────────────────────────────────────────────────────────────
$pdf->SectionTitle('Delivery Records');

// Column config: [label, width, align]
$cols = [
    ['Order',    16, 'C'],
    ['Customer', 36, 'L'],
    ['Product',  36, 'L'],
    ['Status',   38, 'C'],
    ['Progress', 40, 'C'],
    ['ETA',      26, 'C'],
    ['Courier',  0,  'L'],   // 0 = fill remaining width
];
// Resolve the flex column
$fixed = array_sum(array_column(array_filter($cols, fn($c) => $c[1] > 0), 1));
$remaining = 190 - $fixed;
foreach ($cols as &$col) { if ($col[1] === 0) $col[1] = $remaining; }
unset($col);

// Table header
$pdf->SetFillColor(15, 17, 24);
$pdf->SetTextColor(245, 166, 35);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetLineWidth(0);
foreach ($cols as [$label, $w, $align]) {
    $pdf->Cell($w, 9, $label, 0, 0, 'C', true);
}
$pdf->Ln();

// Gold underline
$pdf->SetFillColor(245, 166, 35);
$pdf->Rect(10, $pdf->GetY(), 190, 0.8, 'F');
$pdf->Ln(1);

// Table rows
$res = mysqli_query($conn, "
    SELECT delivery.*, orders.customer_name, orders.product
    FROM delivery
    JOIN orders ON delivery.order_id = orders.id
    ORDER BY delivery.id DESC
");

$rowNum = 0;
while ($r = mysqli_fetch_assoc($res)) {
    $rowNum++;

    // Page-break guard
    if ($pdf->GetY() > 268) {
        $pdf->AddPage();
        $pdf->SetFillColor(15, 17, 24);
        $pdf->SetTextColor(245, 166, 35);
        $pdf->SetFont('Arial', 'B', 8);
        foreach ($cols as [$label, $w]) {
            $pdf->Cell($w, 9, $label, 0, 0, 'C', true);
        }
        $pdf->Ln();
        $pdf->SetFillColor(245, 166, 35);
        $pdf->Rect(10, $pdf->GetY(), 190, 0.8, 'F');
        $pdf->Ln(1);
        $rowNum = 1;
    }

    // Alternating row bg
    $rowY = $pdf->GetY();
    if ($rowNum % 2 === 0) {
        $pdf->SetFillColor(245, 247, 250);
        $pdf->Rect(10, $rowY, 190, 9, 'F');
    }

    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(30, 35, 50);

    // Order ID
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(180, 120, 10);
    $pdf->Cell($cols[0][1], 9, '#' . $r['order_id'], 0, 0, 'C');

    // Customer
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(30, 35, 50);
    $pdf->Cell($cols[1][1], 9, $r['customer_name'], 0, 0, 'L');

    // Product
    $pdf->Cell($cols[2][1], 9, $r['product'], 0, 0, 'L');

    // Status badge
    $sx = $pdf->GetX(); $sy = $pdf->GetY();
    $pdf->Cell($cols[3][1], 9, '', 0, 0, 'C');
    $pdf->StatusBadge($r['delivery_status'], $sx, $sy, $cols[3][1]);

    // Progress bar
    $px = $pdf->GetX(); $py = $pdf->GetY();
    $pdf->Cell($cols[4][1], 9, '', 0, 0, 'C');
    $pdf->ProgressBar($r['progress'], $px + 2, $py, $cols[4][1] - 16);

    // ETA
    $pdf->SetFont('Arial', '', 7.5);
    $pdf->SetTextColor(80, 85, 100);
    $pdf->Cell($cols[5][1], 9, $r['estimated_time'], 0, 0, 'C');

    // Courier
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(30, 35, 50);
    $pdf->Cell($cols[6][1], 9, $r['courier_service'], 0, 0, 'L');

    // Subtle row separator
    $pdf->Ln();
    $pdf->SetDrawColor(220, 222, 228);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
}

// ── Output ────────────────────────────────────────────────────────────────────
$pdf->Output('Delivery_Report.pdf', 'D');