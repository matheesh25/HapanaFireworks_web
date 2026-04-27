<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])){
    die("Order not found");
}

$order_id = intval($_GET['id']);
$user_id  = $_SESSION['user_id'];

$checkOrder = mysqli_query($conn,"SELECT * FROM orders WHERE id='$order_id' AND customer_id='$user_id'");
$order = mysqli_fetch_assoc($checkOrder);

if(!$order){
    die("Invalid order");
}

$deliveryRes = mysqli_query($conn,"SELECT * FROM delivery WHERE order_id='$order_id'");
$delivery = mysqli_fetch_assoc($deliveryRes);

if(!$delivery){
    die("Delivery details not found");
}

$progress = intval($delivery['progress']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hapana Fireworks – Track Order #ORD<?= $order_id ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ===========================
   CSS VARIABLES
=========================== */
:root {
  --gold: #ffc107;
  --gold-dark: #ff9800;
  --gold-dim: rgba(255,193,7,0.13);
  --gold-border: rgba(255,193,7,0.18);
  --bg-black: #000;
  --bg-card: rgba(255,255,255,0.04);
  --text-main: #fff;
  --text-muted: #c8c8c8;
  --radius-card: 20px;
  --radius-pill: 50px;
  --transition: 0.3s ease;
  --green: #4ade80;
  --green-dim: rgba(74,222,128,0.12);
  --green-border: rgba(74,222,128,0.25);
}

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior:smooth; }

body {
  background: var(--bg-black);
  color: var(--text-main);
  font-family: 'Poppins', sans-serif;
  overflow-x: hidden;
  line-height: 1.65;
  min-height: 100vh;
}

a { text-decoration:none; color:inherit; }
img { max-width:100%; display:block; }

/* ===========================
   FIREWORKS CANVAS
=========================== */
#fireworksCanvas {
  position: fixed;
  inset: 0;
  width: 100%; height: 100%;
  z-index: -1;
  pointer-events: none;
}

/* ===========================
   NAVBAR
=========================== */
.navbar {
  background: rgba(0,0,0,0.85);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  padding: 10px 0;
  border-bottom: 1px solid var(--gold-border);
}
.navbar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--gold) !important;
  letter-spacing: 0.3px;
  flex-shrink: 0;
}
.logo {
  width: 40px; height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid rgba(255,193,7,0.7);
  box-shadow: 0 0 12px rgba(255,193,7,0.3);
  flex-shrink: 0;
}
.navbar .nav-item { margin: 0 3px; }
.navbar .nav-link {
  color: #fff !important;
  font-weight: 500;
  font-size: 0.92rem;
  padding: 7px 14px !important;
  border-radius: var(--radius-pill);
  transition: all var(--transition);
}
.navbar .nav-link:hover { color: var(--gold) !important; background: var(--gold-dim); }
.navbar .profile-icon i {
  color: var(--gold);
  filter: drop-shadow(0 0 6px rgba(255,193,7,0.55));
}
.navbar-toggler { border: 1px solid rgba(255,193,7,0.4); padding: 5px 9px; }
.navbar-toggler:focus { box-shadow: none; }
.navbar-toggler-icon { filter: brightness(10); }

@media (max-width:991.98px) {
  .navbar-collapse {
    background: rgba(5,5,5,0.97);
    margin-top: 12px;
    padding: 16px;
    border-radius: 16px;
    border: 1px solid var(--gold-border);
  }
  .navbar .nav-item { margin: 4px 0; }
  .navbar .nav-link { display: block; }
}

/* ===========================
   PAGE LAYOUT
=========================== */
.track-page {
  min-height: 100vh;
  padding: 110px 0 70px;
}

/* Page header */
.page-header {
  text-align: center;
  margin-bottom: 36px;
}
.page-label {
  color: #ffd86b;
  font-size: 0.8rem;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  font-weight: 600;
  margin-bottom: 8px;
}
.page-header h1 {
  font-size: clamp(1.5rem, 4vw, 2.2rem);
  font-weight: 800;
  color: #fff;
  text-shadow: 0 0 18px rgba(255,193,7,0.2);
}

/* ===========================
   GLASS CARD
=========================== */
.glass-card {
  background: var(--bg-card);
  border: 1px solid var(--gold-border);
  border-radius: var(--radius-card);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.35);
  padding: 32px 28px;
  margin-bottom: 20px;
}

.section-title {
  font-size: 0.73rem;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--gold);
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.gold-divider {
  border: none;
  border-top: 1px solid var(--gold-border);
  margin: 20px 0;
}

/* ===========================
   HERO TRUCK BANNER
=========================== */
.truck-banner {
  text-align: center;
  padding: 10px 0 24px;
}

.truck-icon-wrap {
  position: relative;
  display: inline-block;
  margin-bottom: 16px;
}

.truck-icon-wrap i {
  font-size: 3.5rem;
  color: var(--gold);
  filter: drop-shadow(0 0 18px rgba(255,193,7,0.55));
  display: block;
}

.truck-pulse {
  position: absolute;
  inset: -10px;
  border-radius: 50%;
  border: 2px solid rgba(255,193,7,0.25);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.6; }
  50%       { transform: scale(1.25); opacity: 0; }
}

.truck-banner h3 {
  font-size: clamp(1.1rem, 3vw, 1.4rem);
  font-weight: 700;
  color: var(--gold);
  margin-bottom: 6px;
}

.truck-banner p {
  font-size: 0.88rem;
  color: var(--text-muted);
  max-width: 400px;
  margin: 0 auto;
}

/* ===========================
   PROGRESS BAR
=========================== */
.progress-wrap {
  margin: 24px 0 8px;
}

.progress-labels {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.progress-label-text {
  font-size: 0.75rem;
  color: var(--text-muted);
  font-weight: 500;
}

.progress-pct {
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--gold);
}

.progress-track {
  height: 10px;
  background: rgba(255,255,255,0.07);
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid rgba(255,255,255,0.05);
}

.progress-fill {
  height: 100%;
  border-radius: 10px;
  background: linear-gradient(90deg, var(--gold-dark), var(--gold));
  box-shadow: 0 0 10px rgba(255,193,7,0.4);
  transition: width 1.2s cubic-bezier(0.22, 1, 0.36, 1);
  position: relative;
  overflow: hidden;
}

.progress-fill::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent 60%, rgba(255,255,255,0.25));
  animation: shimmer 1.8s ease-in-out infinite;
}

@keyframes shimmer {
  0%   { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

/* ===========================
   VERTICAL TIMELINE
=========================== */
.v-timeline {
  display: flex;
  flex-direction: column;
  gap: 0;
  padding: 4px 0;
}

.v-step {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  position: relative;
}

.v-step:not(:last-child) .v-connector {
  position: absolute;
  left: 17px;
  top: 36px;
  width: 2px;
  height: calc(100% - 4px);
  background: rgba(255,255,255,0.07);
  z-index: 0;
}

.v-step.done .v-connector { background: linear-gradient(to bottom, var(--gold), rgba(255,193,7,0.2)); }

.v-dot-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex-shrink: 0;
  position: relative;
  z-index: 1;
  padding-bottom: 28px;
}

.v-step:last-child .v-dot-col { padding-bottom: 0; }

.v-dot {
  width: 36px; height: 36px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.9rem;
  border: 2px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.04);
  color: #444;
  flex-shrink: 0;
  transition: all var(--transition);
}

.v-dot.done {
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  border-color: var(--gold);
  color: #000;
  box-shadow: 0 0 14px rgba(255,193,7,0.45);
}

.v-dot.active {
  border-color: var(--gold);
  color: var(--gold);
  background: var(--gold-dim);
  box-shadow: 0 0 14px rgba(255,193,7,0.25);
  animation: activePulse 1.8s ease-in-out infinite;
}

@keyframes activePulse {
  0%, 100% { box-shadow: 0 0 14px rgba(255,193,7,0.25); }
  50%       { box-shadow: 0 0 24px rgba(255,193,7,0.55); }
}

.v-content {
  flex: 1;
  padding-bottom: 28px;
}

.v-step:last-child .v-content { padding-bottom: 0; }

.v-step-title {
  font-size: 0.92rem;
  font-weight: 600;
  color: #e0e0e0;
  margin-bottom: 3px;
  line-height: 1.3;
}

.v-step.done .v-step-title { color: var(--gold); }
.v-step.active .v-step-title { color: var(--gold); }
.v-step.inactive .v-step-title { color: #555; }

.v-step-sub {
  font-size: 0.76rem;
  color: #555;
}

.v-step.done .v-step-sub { color: #888; }
.v-step.active .v-step-sub { color: var(--text-muted); }

/* ===========================
   INFO GRID
=========================== */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px 20px;
}
.info-item {}
.info-lbl {
  font-size: 0.7rem;
  color: var(--text-muted);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}
.info-val {
  font-size: 0.9rem;
  color: #f0f0f0;
  font-weight: 500;
  word-break: break-word;
}

/* ===========================
   BADGE CHIPS
=========================== */
.chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 13px;
  border-radius: var(--radius-pill);
  font-size: 0.76rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}
.chip-gold    { background: var(--gold-dim); color: var(--gold); border: 1px solid rgba(255,193,7,0.3); }
.chip-green   { background: var(--green-dim); color: var(--green); border: 1px solid var(--green-border); }
.chip-blue    { background: rgba(96,165,250,0.12); color: #60a5fa; border: 1px solid rgba(96,165,250,0.3); }
.chip-red     { background: rgba(248,113,113,0.12); color: #f87171; border: 1px solid rgba(248,113,113,0.3); }

/* ===========================
   BACK BUTTON
=========================== */
.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: var(--gold-dim);
  border: 1px solid var(--gold-border);
  border-radius: var(--radius-pill);
  padding: 8px 18px;
  color: var(--gold);
  font-size: 0.86rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition);
  margin-bottom: 28px;
}
.btn-back:hover {
  background: rgba(255,193,7,0.2);
  border-color: rgba(255,193,7,0.4);
  transform: translateX(-3px);
  color: var(--gold);
}

/* ===========================
   ACTION BUTTONS
=========================== */
.action-row {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  justify-content: center;
  margin-top: 8px;
}
.btn-primary-gold {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(45deg, var(--gold-dark), var(--gold));
  color: #000;
  font-weight: 700;
  font-size: 0.88rem;
  padding: 11px 26px;
  border-radius: var(--radius-pill);
  border: none;
  cursor: pointer;
  transition: all var(--transition);
  box-shadow: 0 0 16px rgba(255,193,7,0.22);
}
.btn-primary-gold:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 24px rgba(255,193,7,0.4);
  color: #000;
}
.btn-outline-gold {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  color: var(--gold);
  font-weight: 600;
  font-size: 0.88rem;
  padding: 10px 26px;
  border-radius: var(--radius-pill);
  border: 1px solid rgba(255,193,7,0.35);
  cursor: pointer;
  transition: all var(--transition);
}
.btn-outline-gold:hover {
  background: var(--gold-dim);
  border-color: rgba(255,193,7,0.6);
  transform: translateY(-2px);
  color: var(--gold);
}

/* ===========================
   ETA HIGHLIGHT BOX
=========================== */
.eta-box {
  background: rgba(255,193,7,0.06);
  border: 1px solid rgba(255,193,7,0.22);
  border-radius: 14px;
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 16px;
}
.eta-icon {
  width: 44px; height: 44px;
  background: var(--gold-dim);
  border: 1px solid var(--gold-border);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.eta-icon i { color: var(--gold); font-size: 1.2rem; }
.eta-lbl { font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; margin-bottom: 3px; }
.eta-val { font-size: 1rem; font-weight: 700; color: var(--gold); }

/* ===========================
   FOOTER
=========================== */
.footer-section {
  background: #000;
  padding: 26px 0;
  border-top: 1px solid var(--gold-border);
  text-align: center;
}
.footer-copy { font-size: 0.78rem; color: #444; margin:0; }
.footer-sub  { font-size: 0.74rem; color: #333; margin:4px 0 0; }

/* ===========================
   ANIMATIONS
=========================== */
.fade-in    { animation: fadeIn 0.85s ease both; }
.fade-in-d1 { animation: fadeIn 0.85s 0.1s ease both; }
.fade-in-d2 { animation: fadeIn 0.85s 0.18s ease both; }
.fade-in-d3 { animation: fadeIn 0.85s 0.26s ease both; }
.fade-in-d4 { animation: fadeIn 0.85s 0.34s ease both; }

@keyframes fadeIn {
  from { opacity:0; transform:translateY(18px); }
  to   { opacity:1; transform:translateY(0); }
}

/* ===========================
   MOBILE
=========================== */
@media (max-width:767.98px) {
  .track-page { padding: 100px 0 60px; }
  .glass-card  { padding: 22px 16px; }
  .info-grid   { grid-template-columns: 1fr 1fr; }
  .action-row  { flex-direction: column; align-items: stretch; }
  .btn-primary-gold, .btn-outline-gold { justify-content: center; }
  .eta-box { flex-direction: column; text-align: center; gap: 10px; }
}
@media (max-width:480px) {
  .info-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- FIREWORKS CANVAS -->
<canvas id="fireworksCanvas"></canvas>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container">
    <a class="navbar-brand" href="Index.php">
      <img src="images/PHOTO-2026-02-12-17-21-53.jpg" class="logo" alt="Hapana Fireworks Logo">
      Hapana Fireworks
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="Index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="Products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
        <li class="nav-item"><a class="nav-link" href="Login.html">Login</a></li>
        <li class="nav-item ms-lg-2">
          <a class="nav-link profile-icon" href="profile.php" aria-label="Profile">
            <i class="bi bi-person-circle fs-5"></i>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- TRACK PAGE -->
<main class="track-page">
  <div class="container" style="max-width:720px;">

    <!-- Page Header -->
    <div class="page-header fade-in">
      <p class="page-label">Live Tracking</p>
      <h1>Track Order <span style="color:var(--gold);">#ORD<?= $order_id ?></span></h1>
    </div>

    <!-- Back -->
    <div class="fade-in">
      <a href="orderDetail.php?id=<?= $order_id ?>" class="btn-back">
        <i class="bi bi-arrow-left"></i> Back to Order Details
      </a>
    </div>

    <!-- TRUCK HERO + PROGRESS -->
    <div class="glass-card fade-in-d1">
      <div class="truck-banner">
        <div class="truck-icon-wrap">
          <div class="truck-pulse"></div>
          <i class="bi bi-truck"></i>
        </div>
        <h3>
          <?php if($progress == 100): ?>🎉 Delivered!
          <?php elseif($progress >= 75): ?>Out for Delivery
          <?php elseif($progress >= 50): ?>On the Way
          <?php elseif($progress >= 25): ?>Processing
          <?php else: ?>Order Placed
          <?php endif; ?>
        </h3>
        <p><?= htmlspecialchars($delivery['top_message']) ?></p>
      </div>

      <div class="progress-wrap">
        <div class="progress-labels">
          <span class="progress-label-text">Delivery Progress</span>
          <span class="progress-pct"><?= $progress ?>%</span>
        </div>
        <div class="progress-track">
          <div class="progress-fill" style="width:<?= $progress ?>%"></div>
        </div>
      </div>
    </div>

    <!-- ETA + COURIER -->
    <div class="glass-card fade-in-d2">
      <div class="section-title"><i class="bi bi-clock-history"></i> Estimated Arrival</div>
      <div class="eta-box">
        <div class="eta-icon"><i class="bi bi-calendar-check"></i></div>
        <div>
          <p class="eta-lbl">Expected Delivery</p>
          <p class="eta-val"><?= htmlspecialchars($delivery['estimated_time']) ?></p>
        </div>
      </div>

      <hr class="gold-divider">

      <div class="info-grid">
        <div class="info-item">
          <p class="info-lbl">Order ID</p>
          <p class="info-val">#ORD<?= $delivery['order_id'] ?></p>
        </div>
        <div class="info-item">
          <p class="info-lbl">Courier Service</p>
          <p class="info-val"><?= htmlspecialchars($delivery['courier_service']) ?></p>
        </div>
        <div class="info-item">
          <p class="info-lbl">Delivery Status</p>
          <p class="info-val">
            <?php
              $ds = $delivery['delivery_status'];
              if($ds === 'Delivered')         echo '<span class="chip chip-green"><i class="bi bi-check-circle-fill"></i> Delivered</span>';
              elseif($ds === 'Out for Delivery') echo '<span class="chip chip-gold"><i class="bi bi-truck"></i> Out for Delivery</span>';
              elseif($ds === 'Cancelled')     echo '<span class="chip chip-red"><i class="bi bi-x-circle-fill"></i> Cancelled</span>';
              else                             echo '<span class="chip chip-blue"><i class="bi bi-arrow-repeat"></i> ' . htmlspecialchars($ds) . '</span>';
            ?>
          </p>
        </div>
        <div class="info-item">
          <p class="info-lbl">Phone</p>
          <p class="info-val"><?= htmlspecialchars($delivery['phone']) ?></p>
        </div>
        <div class="info-item" style="grid-column:1 / -1;">
          <p class="info-lbl">Shipping Address</p>
          <p class="info-val"><?= nl2br(htmlspecialchars($delivery['address'])) ?></p>
        </div>
      </div>
    </div>

    <!-- TIMELINE -->
    <div class="glass-card fade-in-d3">
      <div class="section-title"><i class="bi bi-signpost-2"></i> Delivery Progress</div>
      <?php
        $steps = [
          ['label' => 'Order Placed',      'sub' => 'Your order has been received.',         'threshold' => 25,  'icon' => 'receipt'],
          ['label' => 'Processing',         'sub' => 'Order is being prepared for dispatch.', 'threshold' => 50,  'icon' => 'gear'],
          ['label' => 'Dispatched',         'sub' => 'Package handed to courier.',            'threshold' => 75,  'icon' => 'box-seam'],
          ['label' => 'Out for Delivery',   'sub' => 'Courier is on the way to you.',         'threshold' => 90,  'icon' => 'truck'],
          ['label' => 'Delivery Completed', 'sub' => 'Package successfully delivered.',       'threshold' => 100, 'icon' => 'house-check'],
        ];
      ?>
      <div class="v-timeline">
        <?php foreach($steps as $i => $step):
          $done   = ($progress >= $step['threshold']);
          $prevThreshold = ($i === 0) ? 0 : $steps[$i-1]['threshold'];
          $active = (!$done && $progress >= $prevThreshold && $progress < $step['threshold']);
          $cls    = $done ? 'done' : ($active ? 'active' : 'inactive');
        ?>
        <div class="v-step <?= $cls ?>">
          <div class="v-connector"></div>
          <div class="v-dot-col">
            <div class="v-dot <?= $done ? 'done' : ($active ? 'active' : '') ?>">
              <i class="bi bi-<?= $done ? 'check-lg' : $step['icon'] ?>"></i>
            </div>
          </div>
          <div class="v-content">
            <p class="v-step-title"><?= $step['label'] ?></p>
            <p class="v-step-sub"><?= $step['sub'] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="action-row fade-in-d4">
      <a href="orderDetail.php?id=<?= $order_id ?>" class="btn-primary-gold">
        <i class="bi bi-receipt"></i> View Order Details
      </a>
      <a href="myOrders.php" class="btn-outline-gold">
        <i class="bi bi-list-ul"></i> My Orders
      </a>
    </div>

  </div>
</main>

<!-- FOOTER -->
<footer class="footer-section">
  <div class="container">
    <p class="footer-copy">© 2026 Hapana Fireworks. All rights reserved.</p>
    <p class="footer-sub">Negombo, Sri Lanka</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* FIREWORKS */
const canvas = document.getElementById("fireworksCanvas");
const ctx    = canvas.getContext("2d");
function resize(){ canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
resize();
window.addEventListener("resize", resize);
const fireworks = [], particles = [];

class Firework {
  constructor(){
    this.x       = Math.random() * canvas.width;
    this.y       = canvas.height;
    this.targetY = Math.random() * canvas.height * 0.5;
    this.color   = `hsl(${Math.random()*360},100%,60%)`;
    this.speed   = 5;
    this.exploded = false;
  }
  update(){
    if(!this.exploded){
      this.y -= this.speed;
      if(this.y <= this.targetY){
        this.exploded = true;
        for(let i=0;i<50;i++) particles.push(new Particle(this.x,this.y,this.color));
      }
    }
  }
  draw(){
    if(!this.exploded){ ctx.fillStyle=this.color; ctx.fillRect(this.x,this.y,3,8); }
  }
}

class Particle {
  constructor(x,y,color){
    this.x=x; this.y=y; this.color=color; this.radius=2;
    this.speedX=(Math.random()-0.5)*6;
    this.speedY=(Math.random()-0.5)*6;
    this.gravity=0.05; this.alpha=1;
  }
  update(){ this.x+=this.speedX; this.y+=this.speedY; this.speedY+=this.gravity; this.alpha-=0.01; }
  draw(){
    ctx.globalAlpha=this.alpha;
    ctx.fillStyle=this.color;
    ctx.beginPath();
    ctx.arc(this.x,this.y,this.radius,0,Math.PI*2);
    ctx.fill();
    ctx.globalAlpha=1;
  }
}

function animate(){
  ctx.fillStyle="rgba(0,0,0,0.2)";
  ctx.fillRect(0,0,canvas.width,canvas.height);
  if(Math.random()<0.04) fireworks.push(new Firework());
  for(let i=fireworks.length-1;i>=0;i--){ fireworks[i].update(); fireworks[i].draw(); if(fireworks[i].exploded) fireworks.splice(i,1); }
  for(let i=particles.length-1;i>=0;i--){ particles[i].update(); particles[i].draw(); if(particles[i].alpha<=0) particles.splice(i,1); }
  requestAnimationFrame(animate);
}
animate();
</script>
</body>
</html>