<?php
include("../config.php");

// STATUS
$statusQ = mysqli_query($conn,"SELECT status, COUNT(*) total FROM orders GROUP BY status");
$statuses=[]; $statusCounts=[];
while($r=mysqli_fetch_assoc($statusQ)){
    $statuses[]     = ucfirst($r['status']);
    $statusCounts[] = (int)$r['total'];
}

// MONTHLY SALES
$monthQ = mysqli_query($conn,"
    SELECT DATE_FORMAT(created_at,'%b') as month, SUM(total) as total
    FROM orders GROUP BY month
");
$months=[]; $sales=[];
while($r=mysqli_fetch_assoc($monthQ)){
    $months[] = $r['month'];
    $sales[]  = (float)$r['total'];
}

// PAYMENT
$payQ = mysqli_query($conn,"SELECT payment, COUNT(*) total FROM orders GROUP BY payment");
$payments=[]; $payCounts=[];
while($r=mysqli_fetch_assoc($payQ)){
    $payments[] = strtoupper($r['payment']);
    $payCounts[] = (int)$r['total'];
}

// KPI
$totalOrders  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM orders"))[0];
$totalRevenue = mysqli_fetch_row(mysqli_query($conn,"SELECT COALESCE(SUM(total),0) FROM orders"))[0];
$pending      = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM orders WHERE status='pending'"))[0];
$completed    = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM orders WHERE status='completed'"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Report — Hapana Fireworks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* =============================================
   DESIGN SYSTEM — CSS VARIABLES (shared)
   ============================================= */
:root {
    --bg-base:        #0a0a0a;
    --bg-surface:     #111318;
    --bg-elevated:    #181c24;
    --bg-card:        #1a1f2b;
    --accent:         #f5a623;
    --accent-warm:    #ff7b2e;
    --accent-glow:    rgba(245,166,35,0.18);
    --accent-border:  rgba(245,166,35,0.30);
    --text-primary:   #f0ede8;
    --text-secondary: #8a8fa8;
    --text-muted:     #4a4f62;
    --danger:         #e05252;
    --success:        #3ecf8e;
    --info:           #5b8def;
    --purple:         #c084fc;
    --radius-sm:      8px;
    --radius-md:      14px;
    --radius-lg:      20px;
    --transition:     all 0.25s cubic-bezier(0.4,0,0.2,1);
    --font-display:   'Syne', sans-serif;
    --font-body:      'DM Sans', sans-serif;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }

body {
    background: var(--bg-base);
    color: var(--text-primary);
    font-family: var(--font-body);
    font-size: 14px;
    line-height: 1.6;
    min-height: 100vh;
}

/* =============================================
   PAGE WRAPPER
   ============================================= */
.report-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 32px 24px 60px;
    animation: pageIn 0.4s ease-out;
}

@keyframes pageIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* =============================================
   HEADER
   ============================================= */
.report-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 36px;
    flex-wrap: wrap;
}

.brand-block {
    display: flex;
    align-items: center;
    gap: 16px;
}

.brand-logo {
    width: 52px; height: 52px;
    border-radius: var(--radius-md);
    object-fit: cover;
    border: 2px solid var(--accent-border);
}

.brand-logo-fallback {
    width: 52px; height: 52px;
    border-radius: var(--radius-md);
    background: linear-gradient(135deg, var(--accent), var(--accent-warm));
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    border: 2px solid var(--accent-border);
}

.brand-name {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: 20px;
    color: var(--text-primary);
    line-height: 1.2;
}

.brand-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 2px;
    letter-spacing: 0.3px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--bg-elevated);
    border: 1px solid rgba(255,255,255,0.08);
    color: var(--text-secondary);
    padding: 9px 16px;
    border-radius: var(--radius-sm);
    font-family: var(--font-body);
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    transition: var(--transition);
}
.back-btn:hover { color: var(--text-primary); border-color: rgba(255,255,255,0.15); }

.pdf-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--accent);
    border: none;
    color: #000;
    padding: 9px 18px;
    border-radius: var(--radius-sm);
    font-family: var(--font-body);
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: var(--transition);
}
.pdf-btn:hover { background: var(--accent-warm); }

/* Section label */
.section-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 14px;
}

/* =============================================
   KPI CARDS
   ============================================= */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

.kpi-card {
    background: var(--bg-card);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: var(--radius-lg);
    padding: 22px 20px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    position: relative;
    overflow: hidden;
    transition: var(--transition);
}

.kpi-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
}

.kpi-card.c-total::after   { background: var(--accent); }
.kpi-card.c-revenue::after { background: var(--success); }
.kpi-card.c-pending::after { background: var(--danger); }
.kpi-card.c-done::after    { background: var(--info); }

.kpi-card:hover {
    transform: translateY(-2px);
    border-color: rgba(255,255,255,0.1);
}

.kpi-icon { font-size: 22px; margin-bottom: 4px; }
.kpi-card.c-total   .kpi-icon { color: var(--accent); }
.kpi-card.c-revenue .kpi-icon { color: var(--success); }
.kpi-card.c-pending .kpi-icon { color: var(--danger); }
.kpi-card.c-done    .kpi-icon { color: var(--info); }

.kpi-value {
    font-family: var(--font-display);
    font-size: 28px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
}

.kpi-label {
    font-size: 12px;
    color: var(--text-secondary);
    font-weight: 500;
}

/* =============================================
   DIVIDER
   ============================================= */
.divider {
    height: 1px;
    background: rgba(255,255,255,0.05);
    margin: 28px 0;
}

/* =============================================
   CHARTS GRID
   ============================================= */
.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.chart-card {
    background: var(--bg-card);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: var(--radius-lg);
    padding: 22px;
    transition: var(--transition);
}
.chart-card:hover { border-color: rgba(255,255,255,0.1); }

.chart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.chart-title {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 14px;
    color: var(--text-primary);
}

.chart-type-badge {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--text-muted);
    background: var(--bg-elevated);
    padding: 3px 8px;
    border-radius: 20px;
}

.chart-wrap {
    position: relative;
    height: 220px;
}

/* =============================================
   RESPONSIVE
   ============================================= */
@media (max-width: 900px) {
    .kpi-grid    { grid-template-columns: repeat(2, 1fr); }
    .charts-grid { grid-template-columns: 1fr; }
}

@media (max-width: 520px) {
    .report-page  { padding: 20px 16px 40px; }
    .kpi-grid     { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .kpi-value    { font-size: 22px; }
    .report-header { flex-direction: column; align-items: flex-start; }
    .header-right  { width: 100%; }
    .pdf-btn, .back-btn { flex: 1; justify-content: center; }
}

/* =============================================
   PRINT / PDF OVERRIDES
   ============================================= */
@media print {
    body { background: #fff !important; color: #000 !important; }
    .kpi-card, .chart-card { background: #f9f9f9 !important; border: 1px solid #ddd !important; }
    .chart-title, .kpi-value, .brand-name { color: #000 !important; }
    .kpi-label, .brand-sub, .section-label { color: #555 !important; }
    .header-right { display: none !important; }
}
</style>
</head>
<body>
<div class="report-page">

    <!-- HEADER -->
    <div class="report-header">
        <div class="brand-block">
            <img src="../images/logo.jpg" class="brand-logo"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="brand-logo-fallback" style="display:none">🎆</div>
            <div>
                <div class="brand-name">Hapana Fireworks</div>
                <div class="brand-sub">Orders Analytics Report</div>
            </div>
        </div>
        <div class="header-right">
            <a href="orders.php" class="back-btn">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button onclick="downloadPDF()" class="pdf-btn">
                <i class="bi bi-download"></i> Download PDF
            </button>
        </div>
    </div>

    <!-- KPI -->
    <div class="section-label">Key Metrics</div>
    <div class="kpi-grid">
        <div class="kpi-card c-total">
            <i class="bi bi-cart3 kpi-icon"></i>
            <div class="kpi-value"><?= $totalOrders ?></div>
            <div class="kpi-label">Total Orders</div>
        </div>
        <div class="kpi-card c-revenue">
            <i class="bi bi-currency-dollar kpi-icon"></i>
            <div class="kpi-value">Rs. <?= number_format($totalRevenue, 0) ?></div>
            <div class="kpi-label">Total Revenue</div>
        </div>
        <div class="kpi-card c-pending">
            <i class="bi bi-clock-history kpi-icon"></i>
            <div class="kpi-value"><?= $pending ?></div>
            <div class="kpi-label">Pending Orders</div>
        </div>
        <div class="kpi-card c-done">
            <i class="bi bi-bag-check kpi-icon"></i>
            <div class="kpi-value"><?= $completed ?></div>
            <div class="kpi-label">Completed</div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- CHARTS -->
    <div class="section-label">Analytics</div>
    <div class="charts-grid">

        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Status Distribution</div>
                <span class="chart-type-badge">Pie</span>
            </div>
            <div class="chart-wrap">
                <canvas id="pieChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Status Comparison</div>
                <span class="chart-type-badge">Bar</span>
            </div>
            <div class="chart-wrap">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Monthly Revenue</div>
                <span class="chart-type-badge">Line</span>
            </div>
            <div class="chart-wrap">
                <canvas id="lineChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Payment Methods</div>
                <span class="chart-type-badge">Donut</span>
            </div>
            <div class="chart-wrap">
                <canvas id="payChart"></canvas>
            </div>
        </div>

    </div>
</div><!-- /report-page -->

<script>
/* ===== CHART GLOBAL DEFAULTS ===== */
Chart.defaults.color       = '#8a8fa8';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
Chart.defaults.font.family = "'DM Sans', sans-serif";

const palette    = ['#f5a623','#3ecf8e','#5b8def','#e05252','#c084fc','#ff7b2e'];
const legendOpts = {
    position: 'bottom',
    labels: { padding: 14, usePointStyle: true, pointStyleWidth: 8, color: '#8a8fa8' }
};

/* STATUS PIE */
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($statuses) ?>,
        datasets: [{
            data: <?= json_encode($statusCounts) ?>,
            backgroundColor: palette,
            borderWidth: 0,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: legendOpts }
    }
});

/* STATUS BAR */
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($statuses) ?>,
        datasets: [{
            label: 'Orders',
            data: <?= json_encode($statusCounts) ?>,
            backgroundColor: palette,
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: 'rgba(255,255,255,0.04)' } }
        }
    }
});

/* MONTHLY REVENUE LINE */
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'Revenue (Rs.)',
            data: <?= json_encode($sales) ?>,
            borderColor: '#f5a623',
            backgroundColor: 'rgba(245,166,35,0.10)',
            borderWidth: 2.5,
            pointBackgroundColor: '#f5a623',
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: 'rgba(255,255,255,0.04)' } }
        }
    }
});

/* PAYMENT DONUT */
new Chart(document.getElementById('payChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($payments) ?>,
        datasets: [{
            data: <?= json_encode($payCounts) ?>,
            backgroundColor: palette,
            borderWidth: 0,
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        cutout: '65%',
        plugins: { legend: legendOpts }
    }
});

/* PDF DOWNLOAD */
function downloadPDF(){
    setTimeout(() => {
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = 'orders_report_pdf.php';
        ['pieChart','barChart','lineChart','payChart'].forEach(id => {
            let input   = document.createElement('input');
            input.name  = id;
            input.value = document.getElementById(id).toDataURL();
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    }, 500);
}
</script>
</body>
</html>