<?php
include("../config.php");

// STATUS DATA
$statusQ = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM users GROUP BY status");
$statuses = []; $statusCounts = [];
while($r = mysqli_fetch_assoc($statusQ)){
    $statuses[]      = ucfirst($r['status']);
    $statusCounts[]  = (int)$r['total'];
}

// ROLE DATA
$roleQ = mysqli_query($conn, "SELECT role, COUNT(*) as total FROM users GROUP BY role");
$roles = []; $roleCounts = [];
while($r = mysqli_fetch_assoc($roleQ)){
    $roles[]      = ucfirst($r['role']);
    $roleCounts[] = (int)$r['total'];
}

// REGISTRATIONS OVER TIME (monthly, last 6 months if created_at exists)
// Active vs Blocked per role
$roleStatusQ = mysqli_query($conn, "SELECT role, SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN status='blocked' THEN 1 ELSE 0 END) as blocked_count FROM users GROUP BY role");
$roleStatusLabels = []; $roleActiveData = []; $roleBlockedData = [];
while($r = mysqli_fetch_assoc($roleStatusQ)){
    $roleStatusLabels[] = ucfirst($r['role']);
    $roleActiveData[]   = (int)$r['active_count'];
    $roleBlockedData[]  = (int)$r['blocked_count'];
}

// KPI
$total   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0];
$active  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE status='active'"))[0];
$blocked = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE status='blocked'"))[0];
$admins  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='admin'"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users Report — Hapana Fireworks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
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

/* HEADER */
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

.section-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 14px;
}

/* KPI CARDS */
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
.kpi-card:hover { transform: translateY(-2px); border-color: rgba(255,255,255,0.1); }

.kpi-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
}
.kpi-card.c-total::after   { background: var(--accent); }
.kpi-card.c-active::after  { background: var(--success); }
.kpi-card.c-blocked::after { background: var(--danger); }
.kpi-card.c-admin::after   { background: var(--purple); }

.kpi-icon { font-size: 22px; margin-bottom: 4px; }
.kpi-card.c-total   .kpi-icon { color: var(--accent); }
.kpi-card.c-active  .kpi-icon { color: var(--success); }
.kpi-card.c-blocked .kpi-icon { color: var(--danger); }
.kpi-card.c-admin   .kpi-icon { color: var(--purple); }

.kpi-value {
    font-family: var(--font-display);
    font-size: 28px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
}
.kpi-label { font-size: 12px; color: var(--text-secondary); font-weight: 500; }

.divider { height: 1px; background: rgba(255,255,255,0.05); margin: 28px 0; }

/* CHARTS */
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

/* TABLE */
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

.user-id {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 12px;
    color: var(--accent);
    background: var(--accent-glow);
    border: 1px solid var(--accent-border);
    padding: 2px 8px;
    border-radius: 20px;
}

.user-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: var(--bg-elevated);
    border: 1px solid rgba(255,255,255,0.10);
    display: inline-flex;
    align-items: center; justify-content: center;
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 12px;
    color: var(--accent);
    text-transform: uppercase;
    flex-shrink: 0;
}

.user-cell { display: flex; align-items: center; gap: 10px; }

.badge-role {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
}
.badge-role.admin {
    background: rgba(192,132,252,0.12);
    border: 1px solid rgba(192,132,252,0.30);
    color: var(--purple);
}
.badge-role.user {
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
.badge-status.blocked {
    background: rgba(224,82,82,0.12);
    border: 1px solid rgba(224,82,82,0.30);
    color: var(--danger);
}
.badge-status.active::before  { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--success); }
.badge-status.blocked::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--danger); }

.empty-state { text-align: center; padding: 40px 24px; color: var(--text-muted); }
.empty-state i { font-size: 32px; display: block; margin-bottom: 10px; }

/* RESPONSIVE */
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

/* PRINT */
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
            <div class="brand-logo-fallback" style="display:none">👤</div>
            <div>
                <div class="brand-name">Hapana Fireworks</div>
                <div class="brand-sub">Users Analytics Report</div>
            </div>
        </div>
        <div class="header-right">
            <a href="users.php" class="back-btn">
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
            <div class="kpi-label">Total Users</div>
        </div>
        <div class="kpi-card c-active">
            <i class="bi bi-person-check kpi-icon"></i>
            <div class="kpi-value"><?= $active ?></div>
            <div class="kpi-label">Active Users</div>
        </div>
        <div class="kpi-card c-blocked">
            <i class="bi bi-person-x kpi-icon"></i>
            <div class="kpi-value"><?= $blocked ?></div>
            <div class="kpi-label">Blocked Users</div>
        </div>
        <div class="kpi-card c-admin">
            <i class="bi bi-shield-check kpi-icon"></i>
            <div class="kpi-value"><?= $admins ?></div>
            <div class="kpi-label">Admins</div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- pie chart -->
    <div class="section-label">Analytics</div>
    <div class="charts-grid">

        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Users by Status</div>
                <span class="chart-type-badge">Pie</span>
            </div>
            <div class="chart-wrap">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
<!--bar chart-->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Users by Role</div>
                <span class="chart-type-badge">Bar</span>
            </div>
            <div class="chart-wrap">
                <canvas id="barChart"></canvas>
            </div>
        </div>
<!--donut chart-->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Status Breakdown</div>
                <span class="chart-type-badge">Donut</span>
            </div>
            <div class="chart-wrap">
                <canvas id="statusDonut"></canvas>
            </div>
        </div>
<!--stack bar-->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Active vs Blocked by Role</div>
                <span class="chart-type-badge">Stacked</span>
            </div>
            <div class="chart-wrap">
                <canvas id="stackedBar"></canvas>
            </div>
        </div>

    </div>

    <div class="divider"></div>

    <!-- TABLE -->
    <div class="section-label">User Details</div>
    <div class="table-card">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
                $rowCount = 0;
                while($r = mysqli_fetch_assoc($res)){
                    $rowCount++;
                    $statusClass = strtolower($r['status']) === 'active' ? 'active' : 'blocked';
                    $roleClass   = strtolower($r['role'])   === 'admin'  ? 'admin'  : 'user';
                    $initial     = strtoupper(substr($r['name'], 0, 1));
                ?>
                <tr>
                    <td><span class="user-id">#<?= $r['id'] ?></span></td>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar"><?= $initial ?></div>
                            <strong><?= htmlspecialchars($r['name']) ?></strong>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($r['email']) ?></td>
                    <td>
                        <span class="badge-role <?= $roleClass ?>">
                            <?= ucfirst(htmlspecialchars($r['role'])) ?>
                        </span>
                    </td>
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
                            No users found.
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
Chart.defaults.color       = '#8a8fa8';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
Chart.defaults.font.family = "'DM Sans', sans-serif";

const legendOpts = {
    position: 'bottom',
    labels: { padding: 14, usePointStyle: true, pointStyleWidth: 8, color: '#8a8fa8' }
};

const statusLabels      = <?= json_encode($statuses) ?>;
const statusCounts      = <?= json_encode($statusCounts) ?>;
const roleLabels        = <?= json_encode($roles) ?>;
const roleCounts        = <?= json_encode($roleCounts) ?>;
const roleStatusLabels  = <?= json_encode($roleStatusLabels) ?>;
const roleActiveData    = <?= json_encode($roleActiveData) ?>;
const roleBlockedData   = <?= json_encode($roleBlockedData) ?>;

/* STATUS PIE */
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: statusLabels,
        datasets: [{ data: statusCounts, backgroundColor: ['#3ecf8e','#e05252'], borderWidth: 0, hoverOffset: 8 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: legendOpts } }
});

/* ROLE BAR */
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: roleLabels,
        datasets: [{
            label: 'Users',
            data: roleCounts,
            backgroundColor: ['#c084fc','#5b8def'],
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

/* STATUS DONUT */
new Chart(document.getElementById('statusDonut'), {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{ data: statusCounts, backgroundColor: ['#3ecf8e','#e05252'], borderWidth: 0, hoverOffset: 6 }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: legendOpts } }
});

/* STACKED BAR — active vs blocked per role */
new Chart(document.getElementById('stackedBar'), {
    type: 'bar',
    data: {
        labels: roleStatusLabels,
        datasets: [
            { label: 'Active',  data: roleActiveData,  backgroundColor: '#3ecf8e', borderRadius: 6, borderSkipped: false },
            { label: 'Blocked', data: roleBlockedData, backgroundColor: '#e05252', borderRadius: 6, borderSkipped: false }
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

/*download */
function downloadPDF(){
    setTimeout(() => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'users_report_pdf.php';
        ['pieChart','barChart','statusDonut','stackedBar'].forEach(id => {
            const input = document.createElement('input');
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