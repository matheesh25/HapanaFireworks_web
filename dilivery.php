<?php
session_start();
include("config.php");

if(!isset($_SESSION["role"]) || $_SESSION["role"] != "admin"){
    header("Location: ../login.php");
    exit();
}

$res = mysqli_query($conn,"
    SELECT delivery.*, orders.customer_name, orders.product
    FROM delivery
    JOIN orders ON delivery.order_id = orders.id
    ORDER BY delivery.id DESC
");

// NOTIFICATIONS
$lowStock  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM products WHERE stock < 10"))['t'];
$newOrders = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM orders WHERE status='pending'"))['total'];
$newUsers  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM users WHERE created_at >= NOW() - INTERVAL 1 DAY"))['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Delivery — Hapana Fireworks Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
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
    --warning:        #f5a623;
    --radius-sm:      8px;
    --radius-md:      14px;
    --radius-lg:      20px;
    --sidebar-w:      240px;
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
    overflow-x: hidden;
}

/* =============================================
   LAYOUT
   ============================================= */
.app-shell { display: flex; min-height: 100vh; }

/* =============================================
   SIDEBAR
   ============================================= */
.sidebar {
    width: var(--sidebar-w);
    background: var(--bg-surface);
    border-right: 1px solid rgba(255,255,255,0.05);
    display: flex;
    flex-direction: column;
    padding: 20px 16px 28px;
    position: fixed;
    top: 0; left: 0;
    height: 100vh;
    z-index: 200;
    transition: var(--transition);
}

/* Brand block — logo + name (same as dashboard) */
.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 0 4px 22px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    margin-bottom: 16px;
}

.brand-logo {
    width: 40px; height: 40px;
    border-radius: var(--radius-sm);
    object-fit: cover;
    border: 2px solid var(--accent-border);
    flex-shrink: 0;
}

.brand-logo-fallback {
    width: 40px; height: 40px;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--accent), var(--accent-warm));
    display: none;
    align-items: center; justify-content: center;
    font-size: 18px;
    border: 2px solid var(--accent-border);
    flex-shrink: 0;
}

.brand-text { display: flex; flex-direction: column; }

.brand-name {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: 13px;
    color: var(--text-primary);
    line-height: 1.2;
    letter-spacing: 0.2px;
}
.brand-name span { color: var(--accent); }

.brand-sub {
    font-size: 10px;
    color: var(--text-muted);
    font-weight: 500;
    margin-top: 1px;
    letter-spacing: 0.2px;
}

.nav-label {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--text-muted);
    padding: 4px 12px 8px;
    margin-top: 8px;
}

.nav-link-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: var(--radius-sm);
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 500;
    font-size: 13.5px;
    transition: var(--transition);
    position: relative;
    margin-bottom: 2px;
    border: 1px solid transparent;
}

.nav-link-item i { font-size: 16px; flex-shrink: 0; width: 20px; text-align: center; }
.nav-link-item:hover { background: var(--bg-elevated); color: var(--text-primary); }

.nav-link-item.active {
    background: var(--accent-glow);
    color: var(--accent);
    border-color: var(--accent-border);
}

.nav-link-item.active::before {
    content: '';
    position: absolute;
    left: 0; top: 50%;
    transform: translateY(-50%);
    width: 3px; height: 60%;
    background: var(--accent);
    border-radius: 0 4px 4px 0;
}

.nav-spacer { flex: 1; }

.nav-link-item.logout {
    color: var(--danger);
    border-top: 1px solid rgba(255,255,255,0.05) !important;
    margin-top: 8px;
    padding-top: 14px;
    border-radius: 0;
    border-color: transparent;
}
.nav-link-item.logout:hover { background: rgba(224,82,82,0.1); border-radius: var(--radius-sm); }

/* =============================================
   MOBILE SIDEBAR
   ============================================= */
.sidebar-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 199;
    backdrop-filter: blur(2px);
}

.sidebar-toggle {
    display: none;
    background: var(--bg-surface);
    border: 1px solid rgba(255,255,255,0.08);
    color: var(--text-primary);
    width: 40px; height: 40px;
    border-radius: var(--radius-sm);
    align-items: center; justify-content: center;
    font-size: 18px;
    cursor: pointer;
    transition: var(--transition);
}
.sidebar-toggle:hover { background: var(--bg-elevated); color: var(--accent); }

/* =============================================
   MAIN CONTENT
   ============================================= */
.main-content {
    margin-left: var(--sidebar-w);
    flex: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* =============================================
   TOPBAR
   ============================================= */
.topbar {
    background: var(--bg-surface);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    padding: 14px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky; top: 0; z-index: 100;
    gap: 12px;
}

.topbar-left { display: flex; align-items: center; gap: 14px; }

.page-title {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 18px;
    color: var(--text-primary);
}
.page-title span { color: var(--accent); }

.topbar-right { display: flex; align-items: center; gap: 10px; }

/* Notification bell */
.notif-btn {
    position: relative;
    background: var(--bg-elevated);
    border: 1px solid rgba(255,255,255,0.06);
    color: var(--text-secondary);
    width: 40px; height: 40px;
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    font-size: 16px;
    transition: var(--transition);
}
.notif-btn:hover { color: var(--accent); border-color: var(--accent-border); }

.notif-badge {
    position: absolute;
    top: -5px; right: -5px;
    background: var(--danger);
    color: white;
    font-size: 10px;
    font-weight: 700;
    min-width: 18px; height: 18px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 4px;
    border: 2px solid var(--bg-surface);
}

.dropdown-menu {
    background: var(--bg-elevated) !important;
    border: 1px solid rgba(255,255,255,0.08) !important;
    border-radius: var(--radius-md) !important;
    padding: 12px !important;
    min-width: 220px;
    box-shadow: 0 16px 40px rgba(0,0,0,0.5);
}

.notif-header {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 13px;
    color: var(--accent);
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    margin-bottom: 8px;
}

.notif-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 6px;
    font-size: 13px;
    color: var(--text-secondary);
    border-radius: var(--radius-sm);
    transition: var(--transition);
    list-style: none;
}
.notif-item:hover { background: rgba(255,255,255,0.04); color: var(--text-primary); }
.notif-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

/* Topbar brand identity — same as dashboard */
.topbar-brand {
    display: flex;
    align-items: center;
    gap: 9px;
}

.topbar-logo {
    width: 34px; height: 34px;
    border-radius: var(--radius-sm);
    object-fit: cover;
    border: 1px solid var(--accent-border);
}

.topbar-logo-fallback {
    width: 34px; height: 34px;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--accent), var(--accent-warm));
    display: none;
    align-items: center; justify-content: center;
    font-size: 14px;
}

.topbar-name {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 13px;
    color: var(--text-primary);
    line-height: 1.2;
}

.topbar-role { font-size: 10px; color: var(--text-muted); }

/* =============================================
   PAGE BODY
   ============================================= */
.page-body {
    padding: 28px;
    flex: 1;
    animation: pageIn 0.4s ease-out;
}

@keyframes pageIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* =============================================
   TOOLBAR
   ============================================= */
.toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.search-wrap {
    position: relative;
    flex: 1;
    min-width: 180px;
    max-width: 280px;
}

.search-wrap i {
    position: absolute;
    left: 12px; top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 14px;
    pointer-events: none;
}

.search-input {
    width: 100%;
    background: var(--bg-elevated);
    border: 1px solid rgba(255,255,255,0.08);
    color: var(--text-primary);
    border-radius: var(--radius-sm);
    padding: 9px 12px 9px 36px;
    font-family: var(--font-body);
    font-size: 13px;
    transition: var(--transition);
    outline: none;
}
.search-input::placeholder { color: var(--text-muted); }
.search-input:focus { border-color: var(--accent-border); box-shadow: 0 0 0 3px var(--accent-glow); }

.filter-select {
    background: var(--bg-elevated);
    border: 1px solid rgba(255,255,255,0.08);
    color: var(--text-primary);
    border-radius: var(--radius-sm);
    padding: 9px 32px 9px 12px;
    font-family: var(--font-body);
    font-size: 13px;
    cursor: pointer;
    transition: var(--transition);
    outline: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a8fa8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
}
.filter-select:focus { border-color: var(--accent-border); box-shadow: 0 0 0 3px var(--accent-glow); }
.filter-select option { background: var(--bg-elevated); }

.btn-report {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--accent-glow);
    border: 1px solid var(--accent-border);
    color: var(--accent);
    padding: 9px 16px;
    border-radius: var(--radius-sm);
    font-family: var(--font-body);
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    transition: var(--transition);
}
.btn-report:hover { background: var(--accent); color: #000; border-color: var(--accent); }

/* =============================================
   TABLE
   ============================================= */
.table-card {
    background: var(--bg-card);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.table-responsive { overflow-x: auto; }

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

.order-id {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 12px;
    color: var(--accent);
    background: var(--accent-glow);
    border: 1px solid var(--accent-border);
    padding: 2px 8px;
    border-radius: 20px;
}

/* Status badges */
.status-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    white-space: nowrap;
}
.status-pending    { background: rgba(245,166,35,0.15); border: 1px solid rgba(245,166,35,0.3); color: #f5a623; }
.status-processing { background: rgba(91,141,239,0.15); border: 1px solid rgba(91,141,239,0.3); color: #5b8def; }
.status-out        { background: rgba(62,207,142,0.15); border: 1px solid rgba(62,207,142,0.3); color: #3ecf8e; }
.status-completed  { background: rgba(62,207,142,0.22); border: 1px solid rgba(62,207,142,0.45); color: #3ecf8e; }

/* Progress bar */
.progress-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 110px;
}

.progress-bar-track {
    flex: 1;
    height: 6px;
    background: var(--bg-elevated);
    border-radius: 99px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, var(--accent), var(--accent-warm));
    transition: width 0.4s ease;
}

.progress-label {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 11px;
    color: var(--accent);
    min-width: 30px;
    text-align: right;
}

.courier-pill {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    background: var(--bg-elevated);
    border: 1px solid rgba(255,255,255,0.08);
    color: var(--text-secondary);
}

.action-group { display: flex; gap: 6px; align-items: center; }

.btn-tbl {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 11px;
    border-radius: var(--radius-sm);
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
    border: 1px solid transparent;
    white-space: nowrap;
    font-family: var(--font-body);
    cursor: pointer;
    background: none;
}

.btn-tbl-edit {
    background: var(--bg-elevated);
    border-color: rgba(255,255,255,0.08);
    color: var(--text-secondary);
}
.btn-tbl-edit:hover { background: var(--accent-glow); border-color: var(--accent-border); color: var(--accent); }

.eta-cell { font-size: 12px; color: var(--text-secondary); }

.empty-state {
    text-align: center;
    padding: 48px 24px;
    color: var(--text-muted);
}
.empty-state i { font-size: 36px; margin-bottom: 12px; display: block; }

/* =============================================
   RESPONSIVE
   ============================================= */
@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); box-shadow: 4px 0 30px rgba(0,0,0,0.5); }
    .sidebar.open { transform: translateX(0); }
    .sidebar-overlay.show { display: block; }
    .sidebar-toggle { display: flex; }
    .main-content { margin-left: 0; }
    .topbar { padding: 12px 16px; }
    .page-body { padding: 16px; }
    .page-title { font-size: 15px; }
    .toolbar { flex-direction: column; align-items: stretch; }
    .search-wrap { max-width: 100%; }
    .topbar-name, .topbar-role { display: none; }
}
</style>
</head>
<body>
<div class="app-shell">

<!-- ========= SIDEBAR ========= -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <!-- Logo — same as dashboard -->
        <img src="../images/PHOTO-2026-02-12-17-21-53.jpg" class="brand-logo" alt="Hapana Fireworks"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="brand-logo-fallback">🎆</div>
        <div class="brand-text">
            <div class="brand-name">Hapana <span>Fireworks</span></div>
            <div class="brand-sub">Admin Panel</div>
        </div>
    </div>

    <div class="nav-label">Navigation</div>

    <a href="dashboard.php" class="nav-link-item">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="suppliers.php" class="nav-link-item">
        <i class="bi bi-people"></i> Suppliers
    </a>
    <a href="products.php" class="nav-link-item">
        <i class="bi bi-box-seam"></i> Products
    </a>
    <a href="orders.php" class="nav-link-item">
        <i class="bi bi-cart3"></i> Orders
    </a>
    <a href="users.php" class="nav-link-item">
        <i class="bi bi-person-badge"></i> Users
    </a>
    <a href="dilivery.php" class="nav-link-item active">
        <i class="bi bi-truck"></i> Delivery
    </a>

    <div class="nav-spacer"></div>

    <a href="../logout.php" class="nav-link-item logout">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</aside>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ========= MAIN ========= -->
<div class="main-content">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <div class="page-title">Delivery <span>Management</span></div>
        </div>

        <div class="topbar-right">
            <!-- Notification bell — same as dashboard -->
            <div class="dropdown">
                <button class="notif-btn" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <span class="notif-badge"><?= $newOrders + $lowStock + $newUsers ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <div class="notif-header"><i class="bi bi-bell-fill me-1"></i> Notifications</div>
                    <li class="notif-item">
                        <span class="notif-dot" style="background:var(--accent)"></span>
                        New Orders
                        <strong style="color:var(--text-primary);margin-left:auto"><?= $newOrders ?></strong>
                    </li>
                    <li class="notif-item">
                        <span class="notif-dot" style="background:var(--danger)"></span>
                        Low Stock
                        <strong style="color:var(--text-primary);margin-left:auto"><?= $lowStock ?></strong>
                    </li>
                    <li class="notif-item">
                        <span class="notif-dot" style="background:var(--info)"></span>
                        New Users
                        <strong style="color:var(--text-primary);margin-left:auto"><?= $newUsers ?></strong>
                    </li>
                </ul>
            </div>

            <!-- Brand identity — same as dashboard -->
            <div class="topbar-brand">
                <img src="../images/PHOTO-2026-02-12-17-21-53.jpg" class="topbar-logo" alt="Logo"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="topbar-logo-fallback">🎆</div>
                <div>
                    <div class="topbar-name">Hapana Fireworks</div>
                    <div class="topbar-role">Administrator</div>
                </div>
            </div>
        </div>
    </header>

    <!-- PAGE BODY -->
    <main class="page-body">

        <!-- ===== TOOLBAR ===== -->
        <div class="toolbar">
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="search" class="search-input" placeholder="Search customer or product…">
            </div>

            <select id="statusFilter" class="filter-select">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="out for delivery">Out for Delivery</option>
                <option value="completed">Completed</option>
            </select>

            <select id="progressFilter" class="filter-select">
                <option value="all">All Progress</option>
                <option value="0">0%</option>
                <option value="25">25%</option>
                <option value="50">50%</option>
                <option value="75">75%</option>
                <option value="100">100%</option>
            </select>

            <a href="delivery_report_view.php" class="btn-report" style="margin-left:auto;">
                <i class="bi bi-bar-chart-line"></i> Delivery Report
            </a>
        </div>

        <!-- ===== TABLE ===== -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="data-table" id="deliveryTable">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Phone</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>ETA</th>
                            <th>Courier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $count = 0;
                    while($row = mysqli_fetch_assoc($res)){
                        $count++;
                        $status = strtolower($row['delivery_status']);
                        if($status === 'pending')           $badgeClass = 'status-pending';
                        elseif($status === 'processing')    $badgeClass = 'status-processing';
                        elseif(str_contains($status,'out')) $badgeClass = 'status-out';
                        elseif($status === 'completed')     $badgeClass = 'status-completed';
                        else                                $badgeClass = 'status-pending';
                        $progress = (int)$row['progress'];
                    ?>
                    <tr>
                        <td><span class="order-id">#<?= $row['order_id'] ?></span></td>
                        <td><strong><?= htmlspecialchars($row['customer_name']) ?></strong></td>
                        <td><?= htmlspecialchars($row['product']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td>
                            <div class="progress-wrap">
                                <div class="progress-bar-track">
                                    <div class="progress-bar-fill" style="width:<?= $progress ?>%"></div>
                                </div>
                                <span class="progress-label"><?= $progress ?>%</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?= $badgeClass ?>">
                                <?= htmlspecialchars($row['delivery_status']) ?>
                            </span>
                        </td>
                        <td class="eta-cell"><?= htmlspecialchars($row['estimated_time']) ?></td>
                        <td><span class="courier-pill"><?= htmlspecialchars($row['courier_service']) ?></span></td>
                        <td>
                            <div class="action-group">
                                <a href="edit_delivery.php?id=<?= $row['id'] ?>" class="btn-tbl btn-tbl-edit">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>

                    <?php if($count === 0): ?>
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="bi bi-truck"></i>
                                No delivery records found.
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* SIDEBAR MOBILE */
const sidebar   = document.getElementById('sidebar');
const overlay   = document.getElementById('sidebarOverlay');
const toggleBtn = document.getElementById('sidebarToggle');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
});
overlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
});

/* FILTER */
const search         = document.getElementById("search");
const statusFilter   = document.getElementById("statusFilter");
const progressFilter = document.getElementById("progressFilter");

function filterTable(){
    const searchVal   = search.value.toLowerCase();
    const statusVal   = statusFilter.value.toLowerCase();
    const progressVal = progressFilter.value;

    document.querySelectorAll("#deliveryTable tbody tr").forEach(row => {
        const text       = row.innerText.toLowerCase();
        const statusEl   = row.querySelector('.status-badge');
        const progressEl = row.querySelector('.progress-label');
        if(!statusEl) return;
        const rowStatus   = statusEl.innerText.toLowerCase();
        const rowProgress = progressEl.innerText.replace('%','').trim();

        const matchSearch   = text.includes(searchVal);
        const matchStatus   = statusVal === 'all' || rowStatus.includes(statusVal);
        const matchProgress = progressVal === 'all' || rowProgress === progressVal;

        row.style.display = (matchSearch && matchStatus && matchProgress) ? '' : 'none';
    });
}

search.addEventListener("keyup", filterTable);
statusFilter.addEventListener("change", filterTable);
progressFilter.addEventListener("change", filterTable);
</script>
</body>
</html>