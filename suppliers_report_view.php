<?php
include("../config.php");

// STATUS DATA
$statusQ = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM suppliers GROUP BY status");
$statuses = []; $statusCounts = [];
while($r = mysqli_fetch_assoc($statusQ)){
    $statuses[]      = ucfirst($r['status']);
    $statusCounts[]  = (int)$r['total'];
}

// CATEGORY DATA
$catQ = mysqli_query($conn, "SELECT category, COUNT(*) as total FROM suppliers GROUP BY category");
$categories = []; $catCounts = [];
while($r = mysqli_fetch_assoc($catQ)){
    $categories[] = $r['category'];
    $catCounts[]  = (int)$r['total'];
}

// SUPPLIERS PER CATEGORY (for second bar chart — reuse catQ above)
// Active vs Inactive per category
$activeQ = mysqli_query($conn, "SELECT category, SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN status='inactive' THEN 1 ELSE 0 END) as inactive_count FROM suppliers GROUP BY category");
$activeLabels = []; $activeData = []; $inactiveData = [];
while($r = mysqli_fetch_assoc($activeQ)){
    $activeLabels[]  = $r['category'];
    $activeData[]    = (int)$r['active_count'];
    $inactiveData[]  = (int)$r['inactive_count'];
}

// KPI SUMMARY
$total    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM suppliers"))[0];
$active   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM suppliers WHERE status='active'"))[0];
$inactive = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM suppliers WHERE status='inactive'"))[0];
$catTotal = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(DISTINCT category) FROM suppliers"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Suppliers Report — Hapana Fireworks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* =============================================
   DESIGN SYSTEM — CSS VARIABLES
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

.brand-block { display: flex; align-items: center; gap: 16px; }

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

.header-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

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

.kpi-card.c-total::after    { background: var(--accent); }
.kpi-card.c-active::after   { background: var(--success); }
.kpi-card.c-inactive::after { background: var(--danger); }
.kpi-card.c-cat::after      { background: var(--purple); }

.kpi-card:hover { transform: translateY(-2px); border-color: rgba(255,255,255,0.1); }

.kpi-icon { font-size: 22px; margin-bottom: 4px; }
.kpi-card.c-total    .kpi-icon { color: var(--accent); }
.kpi-card.c-active   .kpi-icon { color: var(--success); }
.kpi-card.c-inactive .kpi-icon { color: var(--danger); }
.kpi-card.c-cat      .kpi-icon { color: var(--purple); }

.kpi-value {
    font-family: var(--font-display);
    font-size: 28px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
}

.kpi-label { font-size: 12px; color: var(--text-secondary); font-weight: 500; }

/* =============================================
   DIVIDER
   ============================================= */
.divider { height: 1px; background: rgba(255,255,255,0.05); margin: 28px 0; }

/* =============================================
   CHARTS GRID
   ============================================= */
.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 28px;
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

.chart-wrap { position: relative; height: 220px; }

/* =============================================
   SUPPLIER TABLE
   ============================================= */
.table-card {
    background: var(--bg-card);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.data-table thead tr {
    background: var(--bg-elevated);
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.data-table thead th {
    padding: 13px 16px;
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 11px;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--accent);
    white-space: nowrap;
    text-align: left;
}

.data-table tbody tr {
    border-bottom: 1px solid rgba(255,255,255,0.04);
    transition: var(--transition);
}
.data-table tbody tr:last-child { border-bottom: none; }
.data-table tbody tr:hover { background: rgba(245,166,35,0.04); }

.data-table td {
    padding: 12px 16px;
    color: var(--text-secondary);
    vertical-align: middle;
    white-space: nowrap;
}
.data-table td strong { color: var(--text-primary); font-weight: 600; }

.supplier-id {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 12px;
    color: var(--accent);
    background: var(--accent-glow);
    border: 1px solid var(--accent-border);
    padding: 2px 8px;
    border-radius: 20px;
}

.badge-category {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    background: var(--bg-elevated);
    border: 1px solid rgba(255,255,255,0.08);
    color: var(--text-secondary);
}

.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}

.badge-status.active {
    background: rgba(62,207,142,0.12);
    border: 1px solid rgba(62,207,142,0.30);
    color: var(--success);
}

.badge-status.inactive {
    background: rgba(224,82,82,0.12);
    border: 1px solid rgba(224,82,82,0.30);
    color: var(--danger);
}

.badge-status.active::before  { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--success); }
.badge-status.inactive::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--danger); }

.empty-state { text-align: center; padding: 40px 24px; color: var(--text-muted); }
.empty-state i { font-size: 32px; display: block; margin-bottom: 10px; }

/* =============================================
   RESPONSIVE
   ============================================= */
@media (max-width: 900px) {
    .kpi-grid    { grid-template-columns: repeat(2, 1fr); }
    .charts-grid { grid-template-columns: 1fr; }
}

@media (max-width: 520px) {
    .report-page   { padding: 20px 16px 40px; }
    .kpi-grid      { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .kpi-value     { font-size: 22px; }
    .report-header { flex-direction: column; align-items: flex-start; }
    .header-right  { width: 100%; }
    .pdf-btn, .back-btn { flex: 1; justify-content: center; }
}

/* =============================================
   PRINT / PDF
   ============================================= */
@media print {
    body { background: #fff !important; color: #000 !important; }
    .kpi-card, .chart-card, .table-card { background: #f9f9f9 !important; border: 1px solid #ddd !important; }
    .chart-title, .kpi-value, .brand-name, .data-table td strong { color: #000 !important; }
    .kpi-label, .brand-sub, .section-label, .data-table td { color: #555 !important; }
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
            <div class="brand-logo-fallback" style="display:none">🏭</div>
            <div>
                <div class="brand-name">Hapana Fireworks</div>
                <div class="brand-sub">Suppliers Analytics Report</div>
            </div>
        </div>
        <div class="header-right">
            <a href="suppliers.php" class="back-btn">
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
            <i class="bi bi-people kpi-icon"></i>
            <div class="kpi-value"><?= $total ?></div>
            <div class="kpi-label">Total Suppliers</div>
        </div>
        <div class="kpi-card c-active">
            <i class="bi bi-check-circle kpi-icon"></i>
            <div class="kpi-value"><?= $active ?></div>
            <div class="kpi-label">Active Suppliers</div>
        </div>
        <div class="kpi-card c-inactive">
            <i class="bi bi-x-circle kpi-icon"></i>
            <div class="kpi-value"><?= $inactive ?></div>
            <div class="kpi-label">Inactive Suppliers</div>
        </div>
        <div class="kpi-card c-cat">
            <i class="bi bi-tags kpi-icon"></i>
            <div class="kpi-value"><?= $catTotal ?></div>
            <div class="kpi-label">Categories</div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- CHARTS -->
    <div class="section-label">Analytics</div>
    <div class="charts-grid">

        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Suppliers by Status</div>
                <span class="chart-type-badge">Pie</span>
            </div>
            <div class="chart-wrap">
                <canvas id="pieChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Suppliers by Category</div>
                <span class="chart-type-badge">Bar</span>
            </div>
            <div class="chart-wrap">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Active vs Inactive by Category</div>
                <span class="chart-type-badge">Stacked</span>
            </div>
            <div class="chart-wrap">
                <canvas id="stackedBar"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Status Breakdown</div>
                <span class="chart-type-badge">Donut</span>
            </div>
            <div class="chart-wrap">
                <canvas id="statusDonut"></canvas>
            </div>
        </div>

    </div>

    <div class="divider"></div>

    <!-- SUPPLIER TABLE -->
    <div class="section-label">Supplier Details</div>
    <div class="table-card">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Category</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY id DESC");
                $rowCount = 0;
                while($r = mysqli_fetch_assoc($res)){
                    $rowCount++;
                    $statusClass = strtolower($r['status']) === 'active' ? 'active' : 'inactive';
                ?>
                <tr>
                    <td><span class="supplier-id">#<?= $r['id'] ?></span></td>
                    <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                    <td><?= htmlspecialchars($r['company_name']) ?></td>
                    <td><span class="badge-category"><?= htmlspecialchars($r['category']) ?></span></td>
                    <td>
                        <span class="badge-status <?= $statusClass ?>">
                            <?= ucfirst(htmlspecialchars($r['status'])) ?>
                        </span>
                    </td>
                </tr>
                <?php } ?>

                <?php if($rowCount === 0): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="bi bi-people"></i>
                            No suppliers found.
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
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

const statusLabels   = <?= json_encode($statuses) ?>;
const statusCounts   = <?= json_encode($statusCounts) ?>;
const catLabels      = <?= json_encode($categories) ?>;
const catCounts      = <?= json_encode($catCounts) ?>;
const activeLabels   = <?= json_encode($activeLabels) ?>;
const activeData     = <?= json_encode($activeData) ?>;
const inactiveData   = <?= json_encode($inactiveData) ?>;

/* STATUS PIE */
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: statusLabels,
        datasets: [{ data: statusCounts, backgroundColor: ['#3ecf8e','#e05252'], borderWidth: 0, hoverOffset: 8 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: legendOpts } }
});

/* CATEGORY BAR */
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: catLabels,
        datasets: [{
            label: 'Suppliers',
            data: catCounts,
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
            y: { grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true }
        }
    }
});

/* STACKED BAR — active vs inactive per category */
new Chart(document.getElementById('stackedBar'), {
    type: 'bar',
    data: {
        labels: activeLabels,
        datasets: [
            {
                label: 'Active',
                data: activeData,
                backgroundColor: '#3ecf8e',
                borderRadius: 6,
                borderSkipped: false
            },
            {
                label: 'Inactive',
                data: inactiveData,
                backgroundColor: '#e05252',
                borderRadius: 6,
                borderSkipped: false
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: legendOpts },
        scales: {
            x: { stacked: true, grid: { display: false } },
            y: { stacked: true, grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true }
        }
    }
});

/* STATUS DONUT */
new Chart(document.getElementById('statusDonut'), {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{ data: statusCounts, backgroundColor: ['#3ecf8e','#e05252'], borderWidth: 0, hoverOffset: 6 }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: legendOpts } }
});

/* PDF DOWNLOAD */
function downloadPDF(){
    setTimeout(() => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'suppliers_report_pdf.php';
        ['pieChart','barChart','stackedBar','statusDonut'].forEach(id => {
            const input   = document.createElement('input');
            input.name    = id;
            input.value   = document.getElementById(id).toDataURL();
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    }, 500);
}
</script>
</body>
</html>