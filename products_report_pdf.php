<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('../fpdf/fpdf.php');
include("../config.php");

// ── Save chart images from POST ──────────────────────────────────────────────
$pieFile = __DIR__ . '/../temp/temp_pie.png';
$barFile = __DIR__ . '/../temp/temp_bar.png';

$pieImage = $_POST['pie'] ?? '';
$barImage = $_POST['bar'] ?? '';

if (!empty($pieImage) && strpos($pieImage, 'base64,') !== false)
    file_put_contents($pieFile, base64_decode(explode('base64,', $pieImage)[1]));

if (!empty($barImage) && strpos($barImage, 'base64,') !== false)
    file_put_contents($barFile, base64_decode(explode('base64,', $barImage)[1]));

// ── KPIs ─────────────────────────────────────────────────────────────────────
$totalProducts = 0;
$totalStock    = 0;
$totalPrice    = 0;
$lowStock      = 0;

$productQuery = mysqli_query($conn, "SELECT * FROM products");
while ($row = mysqli_fetch_assoc($productQuery)) {
    $totalProducts++;
    $totalStock += (int)$row['stock'];
    $totalPrice += (float)$row['price'];
    if ((int)$row['stock'] < 10) $lowStock++;
}
$avgPrice = $totalProducts > 0 ? $totalPrice / $totalProducts : 0;

$tableQuery = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");

// ── Extended PDF class ────────────────────────────────────────────────────────
class ProductsPDF extends FPDF {

    const GOLD_R  = 245; const GOLD_G  = 166; const GOLD_B  = 35;
    const DARK_R  = 15;  const DARK_G  = 17;  const DARK_B  = 24;
    const WHITE_R = 240; const WHITE_G = 237; const WHITE_B = 232;
    const MUTED_R = 90;  const MUTED_G = 95;  const MUTED_B = 110;

    const SUCCESS_R = 39;  const SUCCESS_G = 174; const SUCCESS_B = 96;
    const WARN_R    = 230; const WARN_G    = 126; const WARN_B    = 34;
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
        $this->Cell(200, 5, 'Products Analytics Dashboard', 0, 1, 'R');

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
    function KPIBox($label, $value, $x, $y, $w = 44) {
        $this->SetFillColor(self::DARK_R, self::DARK_G, self::DARK_B);
        $this->RoundedRect($x, $y, $w, 22, 3, 'F');
        $this->SetFillColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->Rect($x, $y, $w, 2, 'F');

        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(self::GOLD_R, self::GOLD_G, self::GOLD_B);
        $this->SetXY($x, $y + 3);
        $this->Cell($w, 10, $value, 0, 1, 'C');

        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(200, 200, 210);
        $this->SetXY($x, $y + 13);
        $this->Cell($w, 7, strtoupper($label), 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
    }

    // ── Stock badge ───────────────────────────────────────────────────────────
    function StockBadge($stock, $x, $y, $w) {
        $stock = (int)$stock;
        if ($stock <= 0)       { $r = self::DANGER_R;  $g = self::DANGER_G;  $b = self::DANGER_B;  $label = 'OUT OF STOCK'; }
        elseif ($stock < 10)   { $r = self::WARN_R;    $g = self::WARN_G;    $b = self::WARN_B;    $label = 'LOW'; }
        else                   { $r = self::SUCCESS_R; $g = self::SUCCESS_G; $b = self::SUCCESS_B; $label = 'OK'; }

        $this->SetFillColor(
            (int)($r + (255 - $r) * 0.82),
            (int)($g + (255 - $g) * 0.82),
            (int)($b + (255 - $b) * 0.82)
        );
        $this->RoundedRect($x + 1, $y + 1.5, $w - 2, 5, 2, 'F');
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor($r, $g, $b);
        $this->SetXY($x, $y + 1);
        $this->Cell($w, 7, $stock . '  ' . $label, 0, 0, 'C');
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

    // ── Table header ──────────────────────────────────────────────────────────
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
$pdf = new ProductsPDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 18);

// Page title
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(15, 17, 24);
$pdf->Cell(0, 8, 'Products Report', 0, 1, 'C');
$pdf->Ln(4);

// ── KPI Row ───────────────────────────────────────────────────────────────────
$pdf->SectionTitle('Key Performance Indicators');
$y = $pdf->GetY();
$pdf->KPIBox('Total Products',  $totalProducts,                        10,  $y);
$pdf->KPIBox('Total Stock',     $totalStock,                           58,  $y);
$pdf->KPIBox('Low Stock Items', $lowStock,                             106, $y);
$pdf->KPIBox('Average Price',   'LKR ' . number_format($avgPrice, 2), 154, $y, 46);
$pdf->SetY($y + 28);

// ── Charts ────────────────────────────────────────────────────────────────────
$pdf->SectionTitle('Analytics Charts');
$chartY = $pdf->GetY();

// Chart labels
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(15, 17, 24);
$pdf->SetXY(10, $chartY);
$pdf->Cell(88, 7, 'Products by Category', 0, 0, 'C');
$pdf->Cell(88, 7, 'Category Comparison', 0, 1, 'C');
$chartY += 7;

// Chart images
if (file_exists($pieFile)) {
    $pdf->Image($pieFile, 10, $chartY, 88, 62);
} else {
    $pdf->SetFillColor(245, 247, 250);
    $pdf->RoundedRect(10, $chartY, 88, 62, 4, 'F');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(150, 155, 170);
    $pdf->SetXY(10, $chartY + 26);
    $pdf->Cell(88, 10, 'Chart not available', 0, 0, 'C');
}

if (file_exists($barFile)) {
    $pdf->Image($barFile, 112, $chartY, 88, 62);
} else {
    $pdf->SetFillColor(245, 247, 250);
    $pdf->RoundedRect(112, $chartY, 88, 62, 4, 'F');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(150, 155, 170);
    $pdf->SetXY(112, $chartY + 26);
    $pdf->Cell(88, 10, 'Chart not available', 0, 0, 'C');
}

$pdf->SetY($chartY + 68);

// ── Products Table ────────────────────────────────────────────────────────────
$pdf->SectionTitle('Product Details');

$cols = [
    ['ID',       14, 'C'],
    ['Name',     58, 'L'],
    ['Category', 38, 'C'],
    ['Price',    38, 'R'],
    ['Stock',    42, 'C'],
];

$pdf->TableHeader($cols);

$rowNum = 0;
if (mysqli_num_rows($tableQuery) > 0) {
    while ($row = mysqli_fetch_assoc($tableQuery)) {
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
        $pdf->Cell($cols[0][1], 9, '#' . $row['id'], 0, 0, 'C');

        // Name
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(30, 35, 50);
        $name = mb_strimwidth($row['name'], 0, 34, '...');
        $pdf->Cell($cols[1][1], 9, $name, 0, 0, 'L');

        // Category pill
        $cx = $pdf->GetX(); $cy = $pdf->GetY();
        $pdf->Cell($cols[2][1], 9, '', 0, 0);
        $pdf->CategoryPill($row['category'], $cx, $cy, $cols[2][1]);

        // Price
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(15, 17, 24);
        $pdf->Cell($cols[3][1], 9, 'LKR ' . number_format($row['price'], 2), 0, 0, 'R');

        // Stock badge
        $sx = $pdf->GetX(); $sy = $pdf->GetY();
        $pdf->Cell($cols[4][1], 9, '', 0, 0);
        $pdf->StockBadge($row['stock'], $sx, $sy, $cols[4][1]);

        // Row separator
        $pdf->Ln();
        $pdf->SetDrawColor(220, 222, 228);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    }
} else {
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(150, 155, 170);
    $pdf->Cell(190, 12, 'No products found.', 0, 1, 'C');
}

// ── Signature ─────────────────────────────────────────────────────────────────
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

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Products_Report.pdf"');

$pdf->Output('Products_Report.pdf', 'D');

// Cleanup temp files
if (file_exists($pieFile)) unlink($pieFile);
if (file_exists($barFile)) unlink($barFile);